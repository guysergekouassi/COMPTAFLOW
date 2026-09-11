<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cabinet;
use App\Models\Company;
use App\Models\User;
use App\Models\PlanComptable;
use App\Models\PlanTiers;
use App\Models\CodeJournal;
use App\Models\TreasuryCategory;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AccountantSpaceController extends Controller
{
    /**
     * Espace Comptable centralisé
     */
    /**
     * Le Pack Entreprise tient une seule comptabilité : son espace existe, mais
     * il n'y ouvre pas d'autres sociétés et ne fusionne pas de dossiers.
     */
    private function refuserSiPackEntreprise()
    {
        $user = Auth::user();

        if ($user && !$user->peutCreerDesSocietes()) {
            return redirect()->route('accountant.space', ['page' => 'companies'])
                ->with('error', "Le Pack Entreprise ne gère qu'une seule comptabilité : la création de sociétés et la fusion demandent une offre supérieure.");
        }

        return null;
    }

    public function index()
    {
        $user = Auth::user();

        // 1. Récupérer toutes les entreprises gérées ou associées
        $myCompanyIds = Company::where('user_id', $user->id)->pluck('id')->toArray();
        $assignedCompanyIds = DB::table('company_user')->where('user_id', $user->id)->pluck('company_id')->toArray();

        // Le gérant voit tout ce que porte son cabinet, y compris les dossiers
        // qu'un collaborateur a ouverts de son côté : ils reviennent au cabinet.
        $cabinetGere = Cabinet::where('user_id', $user->id)->first();
        if ($cabinetGere) {
            $assignedCompanyIds = array_merge(
                $assignedCompanyIds,
                Company::where('cabinet_id', $cabinetGere->id)->pluck('id')->toArray()
            );
        }

        // Rattachement historique porté par users.company_id : un utilisateur créé
        // depuis la gestion des utilisateurs n'a pas de ligne dans company_user.
        // Sans cette prise en compte, son espace restait désespérément vide.
        if ($user->company_id && !in_array($user->company_id, $assignedCompanyIds)) {
            $assignedCompanyIds[] = $user->company_id;
        }

        $allCompanyIds = array_values(array_unique(array_merge($myCompanyIds, $assignedCompanyIds)));

        $companies = Company::with('admin')->whereIn('id', $allCompanyIds)->get();

        // Charger les KPIs pour chaque entreprise
        $companiesData = $companies->map(function ($comp) use ($user) {
            // Comparaison typée : selon le driver PDO, user_id peut remonter en chaîne
            $isOwner = (int) $comp->user_id === (int) $user->id
                || ($user->role === 'admin' && (int) $user->company_id === (int) $comp->id);

            // Calcul des KPIs
            $entriesCount = DB::table('ecriture_comptables')->where('company_id', $comp->id)->count();
            $accountsCount = DB::table('plan_comptables')->where('company_id', $comp->id)->count();
            $tiersCount = DB::table('plan_tiers')->where('company_id', $comp->id)->count();
            $journalsCount = DB::table('code_journals')->where('company_id', $comp->id)->count();

            // Exercice comptable actif
            $exerciceActif = DB::table('exercices_comptables')
                ->where('company_id', $comp->id)
                ->where('cloturer', 0)
                ->orderBy('date_debut', 'desc')
                ->first();

            // Utilisateurs affectés à cette entreprise (pivot + rattachement historique)
            $assignedUsers = DB::table('company_user')
                ->join('users', 'company_user.user_id', '=', 'users.id')
                ->where('company_user.company_id', $comp->id)
                ->select('users.id', 'users.name', 'users.last_name', 'users.email_adresse', 'company_user.role')
                ->get();

            $legacyUsers = DB::table('users')
                ->where('company_id', $comp->id)
                ->whereNotIn('id', $assignedUsers->pluck('id')->all() ?: [0])
                ->select('users.id', 'users.name', 'users.last_name', 'users.email_adresse', 'users.role')
                ->get();

            $assignedUsers = $assignedUsers->concat($legacyUsers);

            // KPIs Financiers SYSCOHADA
            // numero_de_compte est dans plan_comptables (JOIN nécessaire)

            // CA = SUM(credit) comptes 7x
            $ca = DB::table('ecriture_comptables')
                ->join('plan_comptables', 'ecriture_comptables.plan_comptable_id', '=', 'plan_comptables.id')
                ->where('ecriture_comptables.company_id', $comp->id)
                ->where('plan_comptables.numero_de_compte', 'like', '7%')
                ->sum('ecriture_comptables.credit');

            // Trésorerie = SUM(debit) - SUM(credit) comptes 5x
            $tresoDebit = DB::table('ecriture_comptables')
                ->join('plan_comptables', 'ecriture_comptables.plan_comptable_id', '=', 'plan_comptables.id')
                ->where('ecriture_comptables.company_id', $comp->id)
                ->where('plan_comptables.numero_de_compte', 'like', '5%')
                ->sum('ecriture_comptables.debit');
            $tresoCredit = DB::table('ecriture_comptables')
                ->join('plan_comptables', 'ecriture_comptables.plan_comptable_id', '=', 'plan_comptables.id')
                ->where('ecriture_comptables.company_id', $comp->id)
                ->where('plan_comptables.numero_de_compte', 'like', '5%')
                ->sum('ecriture_comptables.credit');
            $tresorerie = $tresoDebit - $tresoCredit;

            // Résultat Net = SUM(credit 7x) - SUM(debit 6x)
            $charges = DB::table('ecriture_comptables')
                ->join('plan_comptables', 'ecriture_comptables.plan_comptable_id', '=', 'plan_comptables.id')
                ->where('ecriture_comptables.company_id', $comp->id)
                ->where('plan_comptables.numero_de_compte', 'like', '6%')
                ->sum('ecriture_comptables.debit');
            $resultatNet = $ca - $charges;

            // Nombre de pièces de vente (code_journal dans code_journals)
            $ventesCount = DB::table('ecriture_comptables')
                ->join('code_journals', 'ecriture_comptables.code_journal_id', '=', 'code_journals.id')
                ->where('ecriture_comptables.company_id', $comp->id)
                ->whereIn(DB::raw('UPPER(code_journals.code_journal)'), ['VT', 'JV', 'VNT', 'VENTE', 'FAC'])
                ->distinct('ecriture_comptables.n_saisie')
                ->count('ecriture_comptables.n_saisie');

            return [
                'model' => $comp,
                'is_owner' => $isOwner,
                'assigned_status' => $isOwner ? 'created' : 'assigned',
                'assigned_by_name' => $isOwner ? null : ($comp->admin?->name . ' ' . $comp->admin?->last_name),
                'entries_count' => $entriesCount,
                'accounts_count' => $accountsCount,
                'tiers_count' => $tiersCount,
                'journals_count' => $journalsCount,
                'exercice_actif' => $exerciceActif ? date('Y', strtotime($exerciceActif->date_debut)) : 'Aucun',
                'assigned_users' => $assignedUsers,
                'ca' => $ca,
                'tresorerie' => $tresorerie,
                'resultat_net' => $resultatNet,
                'ventes_count' => $ventesCount,
            ];
        });

        // 2. Collaborateurs assignés à mes entreprises (tableau)
        //    Deux rattachements possibles : la table pivot company_user, ou le
        //    champ historique users.company_id.
        $assignedCollaboratorIds = DB::table('company_user')
            ->whereIn('company_id', $allCompanyIds)
            ->pluck('user_id')
            ->toArray();
        $legacyCollaboratorIds = User::whereIn('company_id', $allCompanyIds)->pluck('id')->toArray();
        $assignedCollaboratorIds = array_unique(array_merge($assignedCollaboratorIds, $legacyCollaboratorIds));
        $assignedCollaboratorIds = array_values(array_diff($assignedCollaboratorIds, [$user->id]));
        $collaborators = User::with(['companies', 'creator:id,name,last_name'])
            ->whereIn('id', $assignedCollaboratorIds)
            ->get();

        // 2 bis. Pour chaque collaborateur, uniquement les sociétés que MOI je gère
        //        et auxquelles je l'ai lié. Les autres sociétés du collaborateur
        //        (celles d'un autre gérant) ne me regardent pas : ni affichées,
        //        ni proposées au détachement.
        $manageableCompanies = $companies->filter(fn ($comp) => $this->peutGererCollaborateurs($comp, $user))->values();
        $manageableCompanyIds = $manageableCompanies->pluck('id')->all();

        $linksByUser = DB::table('company_user')
            ->whereIn('company_id', $manageableCompanyIds ?: [0])
            ->get()
            ->groupBy('user_id');

        $attacherSocietesLiees = function ($collab) use ($manageableCompanies, $linksByUser) {
            $linkedIds = collect($linksByUser->get($collab->id, []))->pluck('company_id')->all();

            // Rattachement historique (users.company_id), sans ligne pivot
            if ($collab->company_id) {
                $linkedIds[] = $collab->company_id;
            }

            $collab->linked_companies = $manageableCompanies
                ->whereIn('id', $linkedIds)
                ->map(fn ($comp) => ['id' => $comp->id, 'name' => $comp->company_name])
                ->values();

            return $collab;
        };

        $collaborators->each($attacherSocietesLiees);

        // 3. Collaborateurs assignables ou créés par moi (liste déroulante)
        $createdCollaboratorIds = User::where('created_by_id', $user->id)->pluck('id')->toArray();
        $assignableCollaboratorIds = array_unique(array_merge($createdCollaboratorIds, $assignedCollaboratorIds));
        $assignableCollaboratorIds = array_values(array_diff($assignableCollaboratorIds, [$user->id]));
        $assignableCollaborators = User::with(['companies', 'creator:id,name,last_name'])
            ->whereIn('id', $assignableCollaboratorIds)
            ->get()
            ->each($attacherSocietesLiees);

        $selectedCollaboratorId = session('selected_user_id');
        if ($selectedCollaboratorId) {
            $selectedUser = User::with(['companies', 'creator:id,name,last_name'])->find($selectedCollaboratorId);
            if ($selectedUser) {
                $attacherSocietesLiees($selectedUser);
                if (!$assignableCollaborators->contains('id', $selectedCollaboratorId)) {
                    $assignableCollaborators->push($selectedUser);
                }
            }
        }

        // 4. Utilisateurs pour le Chat
        // Tout utilisateur connecté à une de mes entreprises, ou mes collaborateurs, ou les créateurs des entreprises auxquelles je suis affecté
        $chatUserIds = DB::table('company_user')->whereIn('company_id', $allCompanyIds)->where('user_id', '!=', $user->id)->pluck('user_id')->toArray();
        $myCreatedUserIds = User::where('created_by_id', $user->id)->pluck('id')->toArray();
        $creatorsOfMyCompanies = Company::whereIn('id', $allCompanyIds)->pluck('user_id')->filter()->toArray();

        $allChatUserIds = array_unique(array_merge($chatUserIds, $legacyCollaboratorIds, $myCreatedUserIds, $creatorsOfMyCompanies));
        $allChatUserIds = array_diff($allChatUserIds, [$user->id]);

        $chatUsers = User::whereIn('id', $allChatUserIds)->get();

        // 4. Statistiques globales
        $stats = [
            'total_companies' => count($allCompanyIds),
            'owned_companies' => count($myCompanyIds),
            'assigned_companies' => count(array_diff($allCompanyIds, $myCompanyIds)),
            'total_collaborators' => $collaborators->count(),
            'total_entries' => DB::table('ecriture_comptables')->whereIn('company_id', $allCompanyIds)->count()
        ];

        $informations = $this->informationsEspace($user, $cabinetGere);

        return view('accountant.index', compact('companiesData', 'collaborators', 'assignableCollaborators', 'chatUsers', 'stats', 'selectedCollaboratorId', 'cabinetGere', 'informations'));
    }

    /**
     * Création d'une entreprise avec génération de code unique sécurisé
     */
    public function storeCompany(Request $request)
    {
        if ($refus = $this->refuserSiPackEntreprise()) {
            return $refus;
        }

        $request->validate([
            'company_name'     => 'required|string|max:255|unique:companies,company_name',
            'activity'         => 'required|string|max:255',
            'juridique_form'   => 'required|string|max:255',
            'social_capital'   => 'nullable|numeric|min:0',
            'adresse'          => 'nullable|string|max:255',
            'code_postal'      => 'nullable|string|max:20',
            'city'             => 'nullable|string|max:50',
            'country'          => 'nullable|string|max:255',
            'phone_number'     => 'nullable|string|min:8|max:30',
            'email_adresse'    => 'required|email|max:191|unique:companies,email_adresse',
            'identification_TVA' => 'nullable|string|max:50',
        ], [
            'company_name.required' => 'Le nom de la société est obligatoire.',
            'company_name.unique'   => 'Une société porte déjà le nom « ' . $request->company_name .' ». Choisissez un autre nom.',
            'activity.required'     => 'L’activité est obligatoire.',
            'juridique_form.required' => 'La forme juridique est obligatoire.',
            'social_capital.numeric' => 'Le capital social doit être un nombre.',
            'phone_number.min'      => 'Le numéro de téléphone doit contenir au moins 8 caractères.',
            'email_adresse.required' => 'L’adresse email de la société est obligatoire.',
            'email_adresse.email'   => 'Cette adresse email n’est pas valide (exemple : contact@societe.com).',
            'email_adresse.unique'  => 'Adresse email existante. Veuillez saisir une adresse email différente.',
        ], [
            'company_name'  => 'nom de la société',
            'email_adresse' => 'adresse email',
            'juridique_form' => 'forme juridique',
        ]);

        // Génération du code unique sécurisé avec les 3 premières lettres
        $cleanName = preg_replace('/[^A-Za-z]/', '', $request->company_name);
        $prefix = strtoupper(substr($cleanName, 0, 3));
        if (strlen($prefix) < 3) {
            $prefix = str_pad($prefix, 3, 'X');
        }
        
        do {
            $code = $prefix . '-' . strtoupper(Str::random(6));
            $exists = Company::where('company_code', $code)->exists();
        } while ($exists);

        DB::beginTransaction();
        try {
            $user = Auth::user();

            $company = Company::create([
                'company_name' => $request->company_name,
                'company_code' => $code,
                'is_active' => true,
                'juridique_form' => $request->juridique_form,
                'activity' => $request->activity,
                'social_capital' => $request->social_capital ?? 0,
                'adresse' => $request->adresse,
                'code_postal' => $request->code_postal,
                'city' => $request->city,
                'country' => $request->country,
                'phone_number' => $request->phone_number,
                'email_adresse' => $request->email_adresse,
                'identification_TVA' => $request->identification_TVA,
                'user_id' => $user->id, // Propriétaire principal
                // Un collaborateur ouvre ses dossiers sans en référer au gérant,
                // mais le cabinet les porte et les voit.
                'cabinet_id' => optional($this->cabinetDe($user))->id,
            ]);

            // Liaison dans la table pivot company_user
            DB::table('company_user')->insert([
                'company_id' => $company->id,
                'user_id' => $user->id,
                'role' => 'admin',
                'created_at' => now(),
                'updated_at' => now()
            ]);

            // Celui qui cree la comptabilite en est le responsable : il doit
            // disposer de toutes les habilitations, sinon des pages entieres
            // (Hub des Tiers, etats, configuration...) lui restent invisibles.
            $this->accorderToutesLesHabilitations($user);

            // Création automatique des trois catégories de flux pour le TFT
            $tftCategories = [
                'I. Flux de trésorerie des activités opérationnelles',
                'II. Flux de trésorerie des activités d\'investissement',
                'III. Flux de trésorerie des activités de financement',
            ];
            foreach ($tftCategories as $catName) {
                TreasuryCategory::create([
                    'name' => $catName,
                    'company_id' => $company->id,
                ]);
            }

            DB::commit();

            return redirect()->route('accountant.space')
                ->with('success', 'L’entreprise "' . $company->company_name . '" a été créée avec succès ! Code entreprise généré : ' . $code);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erreur lors de la création de l’entreprise : ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Créer un collaborateur (Admin ou Comptable)
     */
    public function createMember(Request $request)
    {
        $mode = $request->input('mode', 'invite');
        $existing = User::where('email_adresse', $request->input('email_adresse'))->first();

        if ($mode === 'invite') {
            if ($existing) {
                return redirect()->route('accountant.space', ['page' => 'collaborators'])
                    ->with('selected_user_id', $existing->id)
                    ->with('info', 'Utilisateur existant : ' . $existing->name . ' ' . $existing->last_name . '. Vous pouvez l\'affecter depuis "Affecter un collaborateur".');
            }

            return back()->with('error', 'Aucun utilisateur trouvé pour cet email. Passez en mode Créer pour ajouter ce collaborateur.')->withInput();
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email_adresse' => 'required|email|max:191|unique:users,email_adresse',
            'password' => 'required|string|min:6',
        ], [
            'name.required' => 'Le prénom est obligatoire.',
            'last_name.required' => 'Le nom est obligatoire.',
            'email_adresse.required' => 'L’adresse email est obligatoire.',
            'email_adresse.email' => 'Cette adresse email n’est pas valide (exemple : jean@email.com).',
            'email_adresse.unique' => 'Un compte utilise déjà l’adresse email « ' . $request->email_adresse
                . ' ». Utilisez le mode « Inviter » pour l’affecter, ou saisissez une autre adresse.',
            'password.required' => 'Le mot de passe est obligatoire.',
            'password.min' => 'Le mot de passe doit contenir au moins 6 caractères.',
        ]);

        // On n'accorde plus un rôle opaque : soit l'accès total, soit les cases
        // cochées, pour qu'on sache toujours ce que le collaborateur peut faire.
        $accesTotal = $request->input('acces') === 'total';
        $habilitations = $accesTotal
            ? User::catalogueMetier()
            : $this->habilitationsCochees($request->input('habilitations', []));

        $collaborateur = User::create([
            'name' => $request->name,
            'last_name' => $request->last_name,
            'email_adresse' => $request->email_adresse,
            'password' => Hash::make($request->password),
            'role' => 'comptable',
            'habilitations' => $habilitations,
            'pack' => Auth::user()->pack ?? 'cabinet',
            'is_active' => true,
            'created_by_id' => Auth::id(),
        ]);

        // Il entre dans le cabinet de celui qui le crée
        $cabinet = $this->cabinetDe(Auth::user());
        if ($cabinet) {
            DB::table('cabinet_user')->updateOrInsert(
                ['cabinet_id' => $cabinet->id, 'user_id' => $collaborateur->id],
                ['role' => 'collaborateur', 'updated_at' => now(), 'created_at' => now()]
            );
        }

        return redirect()->route('accountant.space', ['page' => 'collaborators'])
            ->with('success', 'Collaborateur créé ' . ($accesTotal ? 'avec un accès total.' : 'avec ' . count($habilitations) . ' habilitation(s).'));
    }

    /**
     * Ce que la page Informations donne à lire, selon qui regarde.
     *
     * Le gérant voit tout le cabinet : son code, ses collaborateurs, ses
     * comptabilités et les droits de chacun. Un collaborateur ne voit que ce
     * qui le concerne : le ou les cabinets qui l'accueillent, les dossiers
     * qu'il a lui-même ouverts, et les personnes rattachées à ceux-là. Les
     * dossiers d'un confrère ne le regardent pas.
     */
    private function informationsEspace($user, ?Cabinet $cabinetGere): array
    {
        $estGerant = (bool) $cabinetGere;

        // Cabinets auxquels la personne appartient, le sien compris
        $idsCabinets = DB::table('cabinet_user')->where('user_id', $user->id)->pluck('cabinet_id')->toArray();
        if ($cabinetGere) {
            $idsCabinets[] = $cabinetGere->id;
        }
        $cabinets = Cabinet::with('gerant:id,name,last_name,email_adresse')
            ->whereIn('id', array_unique($idsCabinets) ?: [0])
            ->get()
            ->map(fn ($cab) => [
                'nom'        => $cab->nom,
                'code'       => $cab->code,
                'gerant'     => trim(($cab->gerant->name ?? '') . ' ' . ($cab->gerant->last_name ?? '')) ?: '—',
                'est_gerant' => $cab->estGerant($user),
                'cree_le'    => $cab->created_at ? $cab->created_at->format('d/m/Y') : '—',
            ])
            ->values()
            ->all();

        // Comptabilités du périmètre
        $requete = Company::query();
        if ($estGerant) {
            $requete->where(function ($q) use ($cabinetGere, $user) {
                $q->where('cabinet_id', $cabinetGere->id)->orWhere('user_id', $user->id);
            });
        } else {
            $requete->where('user_id', $user->id);
        }
        $societes = $requete->with('admin:id,name,last_name')->orderBy('company_name')->get();

        $idsSocietes = $societes->pluck('id')->all() ?: [0];
        $rattachements = DB::table('company_user')
            ->join('users', 'company_user.user_id', '=', 'users.id')
            ->whereIn('company_user.company_id', $idsSocietes)
            ->select(
                'company_user.company_id',
                'company_user.role',
                'company_user.habilitations',
                'users.id',
                'users.name',
                'users.last_name',
                'users.email_adresse',
                'users.created_by_id'
            )
            ->get()
            ->groupBy('company_id');

        $lireAcces = function ($ligne) {
            if (($ligne->role ?? null) === 'admin') {
                return 'Accès total';
            }
            $choisies = !empty($ligne->habilitations) ? json_decode($ligne->habilitations, true) : null;

            return is_array($choisies) && $choisies !== []
                ? count($choisies) . ' habilitation(s)'
                : 'Habilitations du compte';
        };

        $listeSocietes = $societes->map(function ($soc) use ($rattachements, $lireAcces, $user) {
            $membres = collect($rattachements->get($soc->id, []))->map(fn ($l) => [
                'nom'   => trim($l->name . ' ' . $l->last_name),
                'email' => $l->email_adresse,
                'acces' => $lireAcces($l),
            ])->values()->all();

            return [
                'nom'            => $soc->company_name,
                'code'           => $soc->company_code ?: '—',
                'createur'       => (int) $soc->user_id === (int) $user->id
                    ? 'Vous'
                    : (trim(($soc->admin->name ?? '') . ' ' . ($soc->admin->last_name ?? '')) ?: '—'),
                'cree_le'        => $soc->created_at ? $soc->created_at->format('d/m/Y') : '—',
                'collaborateurs' => $membres,
            ];
        })->all();

        // Collaborateurs du périmètre, avec leurs droits dossier par dossier
        $parPersonne = [];
        foreach ($societes as $soc) {
            foreach ($rattachements->get($soc->id, []) as $ligne) {
                if ((int) $ligne->id === (int) $user->id) {
                    continue;
                }
                $cle = (int) $ligne->id;
                $parPersonne[$cle] ??= [
                    'nom'        => trim($ligne->name . ' ' . $ligne->last_name),
                    'email'      => $ligne->email_adresse,
                    'cree_par_vous' => (int) ($ligne->created_by_id ?? 0) === (int) $user->id,
                    'dossiers'   => [],
                ];
                $parPersonne[$cle]['dossiers'][] = [
                    'societe' => $soc->company_name,
                    'acces'   => $lireAcces($ligne),
                ];
            }
        }

        return [
            'est_gerant'     => $estGerant,
            'cabinets'       => $cabinets,
            'societes'       => $listeSocietes,
            'collaborateurs' => array_values($parPersonne),
        ];
    }

    /**
     * Le cabinet d'une personne : celui qu'elle gère, sinon celui qui l'accueille.
     */
    private function cabinetDe($user): ?Cabinet
    {
        if (!$user) {
            return null;
        }

        $gere = Cabinet::where('user_id', $user->id)->first();
        if ($gere) {
            return $gere;
        }

        $id = DB::table('cabinet_user')->where('user_id', $user->id)->value('cabinet_id');

        return $id ? Cabinet::find($id) : null;
    }

    /** Cases cochées ramenées au catalogue : on n'accorde que ce qui existe. */
    private function habilitationsCochees(array $cles): array
    {
        $catalogue = User::catalogueMetier();
        $retenues = [];

        foreach ($cles as $cle) {
            if (array_key_exists($cle, $catalogue)) {
                $retenues[$cle] = "1";
            }
        }

        return $retenues;
    }

    /**
     * Recherche d'un utilisateur par email (AJAX)
     */
    public function findUserByEmail(Request $request)
    {
        $email = $request->query('email');
        if (! $email) {
            return response()->json(['found' => false]);
        }

        $user = User::where('email_adresse', $email)->select('id', 'name', 'last_name', 'email_adresse', 'role')->first();
        if ($user) {
            return response()->json(['found' => true, 'user' => $user]);
        }

        return response()->json(['found' => false]);
    }

    /**
     * L'utilisateur courant peut-il gérer les collaborateurs de cette entreprise ?
     * Trois titres possibles : propriétaire, admin rattaché à l'entreprise
     * (users.company_id), ou admin dans la table pivot.
     * Comparaisons typées : selon le driver PDO, les identifiants peuvent
     * remonter sous forme de chaîne.
     */
    private function peutGererCollaborateurs(Company $company, $user)
    {
        if ((int) $company->user_id === (int) $user->id) {
            return true;
        }

        if ($user->role === 'admin' && (int) $user->company_id === (int) $company->id) {
            return true;
        }

        // Le gérant du cabinet répond de tous les dossiers qui lui reviennent
        if ($company->cabinet_id && Cabinet::where('id', $company->cabinet_id)->where('user_id', $user->id)->exists()) {
            return true;
        }

        return DB::table('company_user')
            ->where('company_id', $company->id)
            ->where('user_id', $user->id)
            ->where('role', 'admin')
            ->exists();
    }

    /**
     * Associer un utilisateur à une entreprise
     */
    public function assignUser(Request $request)
    {
        $request->validate([
            'company_id'      => 'required|exists:companies,id',
            'user_id'         => 'required|exists:users,id',
            'acces'           => 'required|in:total,habilitations',
            'habilitations'   => 'required_if:acces,habilitations|array',
            'habilitations.*' => 'string',
        ], [
            'acces.required' => "Choisissez l'etendue de l'acces accorde sur cette comptabilite.",
            'habilitations.required_if' => 'Cochez au moins une habilitation, ou accordez un acces total.',
        ]);

        // Ce que l'affectation accorde ne vaut que pour cette comptabilité :
        // accès total, ou les seules cases cochées.
        $accesTotal = $request->input('acces') === 'total';
        $roleDossier = $accesTotal ? 'admin' : 'comptable';
        $habilitationsDossier = $accesTotal
            ? null
            : json_encode($this->habilitationsCochees($request->input('habilitations', [])));

        $user = Auth::user();

        $company = Company::findOrFail($request->company_id);

        if (!$this->peutGererCollaborateurs($company, $user)) {
            return back()->with('error', 'Vous n’avez pas le droit d’affecter des collaborateurs à cette entreprise.');
        }

        $collaborator = User::findOrFail($request->user_id);

        DB::beginTransaction();
        try {
            $existing = DB::table('company_user')
                ->where('company_id', $company->id)
                ->where('user_id', $collaborator->id)
                ->first();

            if ($existing) {
                DB::table('company_user')
                    ->where('id', $existing->id)
                    ->update([
                        'role' => $roleDossier,
                        'habilitations' => $habilitationsDossier,
                        'updated_at' => now(),
                    ]);
            } else {
                DB::table('company_user')->insert([
                    'company_id' => $company->id,
                    'user_id' => $collaborator->id,
                    'role' => $roleDossier,
                    'habilitations' => $habilitationsDossier,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // Le collaborateur rejoint le cabinet qui porte ce dossier
            if ($company->cabinet_id) {
                DB::table('cabinet_user')->updateOrInsert(
                    ['cabinet_id' => $company->cabinet_id, 'user_id' => $collaborator->id],
                    ['role' => 'collaborateur', 'updated_at' => now(), 'created_at' => now()]
                );
            }

            // Sans entreprise de rattachement, le collaborateur n'a aucun contexte
            // par défaut au moment de sa connexion : on lui donne celle-ci.
            if (!$collaborator->company_id) {
                $collaborator->company_id = $company->id;
                $collaborator->save();
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur affectation collaborateur : ' . $e->getMessage());

            return back()->with('error', 'L’affectation n’a pas pu être enregistrée : ' . $e->getMessage());
        }

        // Notification interne (la cloche) : ne doit jamais faire échouer l'affectation
        try {
            \App\Models\InternalNotification::create([
                'sender_id' => $user->id,
                'receiver_id' => $collaborator->id,
                'title' => 'Affectation à une entreprise',
                'message' => 'Vous avez été affecté à l\'entreprise ' . $company->company_name
                    . ($accesTotal ? ' avec un accès total.' : ' avec les habilitations qui vous ont été accordées.'),
                'type' => 'info',
                'company_id' => $company->id,
                'is_read' => false,
            ]);
        } catch (\Exception $e) {
            Log::warning('Notification d\'affectation non envoyée : ' . $e->getMessage());
        }

        $etendue = $accesTotal
            ? 'avec un accès total'
            : 'avec ' . count(json_decode($habilitationsDossier, true) ?: []) . ' habilitation(s)';

        return redirect()->route('accountant.space', ['page' => 'collaborators'])->with(
            'success',
            trim($collaborator->name . ' ' . $collaborator->last_name) . ' est rattaché à « '
                . $company->company_name . ' » ' . $etendue . '. L’entreprise apparaît dans son espace dès sa prochaine connexion.'
        );
    }

    /**
     * Retirer un utilisateur d'une entreprise
     */
    public function removeUser(Request $request)
    {
        $request->validate([
            'company_id' => 'required',
            'company_id.*' => 'sometimes|exists:companies,id',
            'user_id' => 'required|exists:users,id',
        ]);

        $user = Auth::user();
        $companyIds = is_array($request->company_id) ? $request->company_id : [$request->company_id];

        // Même règle que pour l'affectation : on doit gérer chacune des entreprises visées
        $targetCompanies = Company::whereIn('id', $companyIds)->get();

        if ($targetCompanies->count() !== count(array_unique($companyIds))
            || $targetCompanies->contains(fn ($company) => !$this->peutGererCollaborateurs($company, $user))) {
            return back()->with('error', 'Action non autorisée.');
        }

        // Ne pas se détacher soi-même si propriétaire
        if ($request->user_id == $user->id) {
            return back()->with('error', 'Vous ne pouvez pas vous détacher vous-même de votre entreprise.');
        }

        DB::table('company_user')
            ->whereIn('company_id', $companyIds)
            ->where('user_id', $request->user_id)
            ->delete();

        // Le rattachement historique doit suivre : sinon l'entreprise resterait
        // visible dans l'espace du collaborateur via users.company_id.
        $collaborator = User::find($request->user_id);
        if ($collaborator && in_array($collaborator->company_id, $companyIds)) {
            $collaborator->company_id = DB::table('company_user')
                ->where('user_id', $collaborator->id)
                ->value('company_id');
            $collaborator->save();
        }

        return redirect()->route('accountant.space')->with('success', 'Collaborateur retiré de l’entreprise avec succès.');
    }

    /**
     * Basculer de contexte d'entreprise
     */
    public function switchCompany($id)
    {
        $user = Auth::user();

        // Sécurité : Vérifier que l'utilisateur a accès à cette entreprise
        $hasAccess = Company::where('id', $id)->where('user_id', $user->id)->exists()
            || DB::table('company_user')->where('company_id', $id)->where('user_id', $user->id)->exists();

        if (!$hasAccess) {
            return redirect()->route('accountant.space')->with('error', 'Accès non autorisé à cette entreprise.');
        }

        // Stocker la compagnie en session
        session(['current_company_id' => $id]);

        // On memorise d'ou vient le switch : « Quitter le mode switch » doit
        // ramener a Mon Espace, et non a la passerelle administrative.
        session(['context_return_url' => route('accountant.space', ['page' => 'companies'])]);

        // Redirection vers le tableau de bord de l'entreprise
        if ($user->role === 'admin') {
            return redirect()->route('admin.dashboard');
        } else {
            return redirect()->route('comptable.comptdashboard');
        }
    }

    /**
     * Donne à un utilisateur l'ensemble des habilitations, hors section Super Admin.
     * Les droits déjà accordés sont conservés : on n'enlève jamais rien.
     */
    /** Point d'entrée public, pour les autres écrans de création d'entreprise. */
    public function accorderToutesLesHabilitationsA(User $user): void
    {
        $this->accorderToutesLesHabilitations($user);
    }

    private function accorderToutesLesHabilitations(User $user): void
    {
        $user->accorderToutesLesHabilitationsMetier();
    }

    /**
     * Supprimer une comptabilité depuis Mon Espace.
     *
     * Refusée si l'entreprise contient des écritures : on ne détruit pas une
     * comptabilité mouvementée. La suppression est archivée 30 jours.
     */
    public function destroyCompany(Request $request, $id)
    {
        $user = Auth::user();
        $company = Company::find($id);

        if (!$company) {
            return redirect()->route('accountant.space', ['page' => 'companies'])
                ->with('error', 'Entreprise introuvable.');
        }

        // Seul le propriétaire (celui qui l'a créée) peut la supprimer
        if ((int) $company->user_id !== (int) $user->id) {
            return redirect()->route('accountant.space', ['page' => 'companies'])
                ->with('error', "Seule la personne qui a créé « {$company->company_name} » peut la supprimer.");
        }

        // Condition bloquante : présence d'écritures
        $nbEcritures = \App\Models\EcritureComptable::where('company_id', $company->id)->count();
        if ($nbEcritures > 0) {
            return redirect()->route('accountant.space', ['page' => 'companies'])
                ->with('error', "Suppression impossible : « {$company->company_name} » contient {$nbEcritures} écriture(s) comptable(s).");
        }

        $nom = $company->company_name;

        DB::beginTransaction();
        try {
            $lot = \App\Models\ArchivedRecord::nouveauLot();
            app()->instance('archive.batch_id', $lot);

            // Données de paramétrage rattachées, archivées avec l'entreprise
            foreach (\App\Models\PlanTiers::where('company_id', $company->id)->get() as $ligne) { $ligne->delete(); }
            foreach (\App\Models\CodeJournal::where('company_id', $company->id)->get() as $ligne) { $ligne->delete(); }
            foreach (\App\Models\PlanComptable::where('company_id', $company->id)->get() as $ligne) { $ligne->delete(); }
            foreach (\App\Models\ExerciceComptable::where('company_id', $company->id)->get() as $ligne) { $ligne->delete(); }

            DB::table('company_user')->where('company_id', $company->id)->delete();
            DB::table('treasury_categories')->where('company_id', $company->id)->delete();

            // L'entreprise elle-même (tracée et archivée par le trait LogsActivity)
            $company->delete();

            if (session('current_company_id') == $id) {
                session()->forget('current_company_id');
            }

            DB::commit();
            app()->forgetInstance('archive.batch_id');

            return redirect()->route('accountant.space', ['page' => 'companies'])
                ->with('success', "L'entreprise « {$nom} » a été supprimée. Elle reste consultable 30 jours dans l'Archive des suppressions.");
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('accountant.space', ['page' => 'companies'])
                ->with('error', 'Suppression impossible : ' . $e->getMessage());
        }
    }

    /**
     * Fusionner/Déverser des données d'une entreprise A vers une entreprise B
     */
    public function fusionData(Request $request)
    {
        if ($refus = $this->refuserSiPackEntreprise()) {
            return $refus;
        }

        $request->validate([
            'source_company_id' => 'required|exists:companies,id',
            'target_company_id' => 'required|exists:companies,id',
            'scope' => 'required|array|min:1',
            'scope.*' => 'in:accounts,journals,tiers',
        ]);

        $user = Auth::user();

        // Sécurité : Vérifier que l'utilisateur a accès aux deux entreprises
        $sourceAccess = Company::where('id', $request->source_company_id)->where('user_id', $user->id)->exists()
            || DB::table('company_user')->where('company_id', $request->source_company_id)->where('user_id', $user->id)->exists();

        $targetAccess = Company::where('id', $request->target_company_id)->where('user_id', $user->id)->exists()
            || DB::table('company_user')->where('company_id', $request->target_company_id)->where('user_id', $user->id)->exists();

        if (!$sourceAccess || !$targetAccess) {
            return back()->with('error', 'Accès non autorisé à l’une des entreprises.');
        }

        if ($request->source_company_id === $request->target_company_id) {
            return back()->with('error', 'L’entreprise source doit être différente de l’entreprise cible.');
        }

        $sourceId = $request->source_company_id;
        $targetId = $request->target_company_id;
        $scope = $request->scope;

        DB::beginTransaction();
        try {
            $log = [];

            // 1. PLAN COMPTABLE
            if (in_array('accounts', $scope)) {
                $sourceAccounts = PlanComptable::where('company_id', $sourceId)->get();
                $count = 0;
                foreach ($sourceAccounts as $account) {
                    $exists = PlanComptable::where('company_id', $targetId)
                        ->where('numero_de_compte', $account->numero_de_compte)
                        ->exists();
                    
                    if (!$exists) {
                        $newAccount = $account->replicate(['id', 'created_at', 'updated_at']);
                        $newAccount->company_id = $targetId;
                        $newAccount->user_id = $user->id;
                        $newAccount->save();
                        $count++;
                    }
                }
                $log[] = "$count comptes comptables déversés.";
            }

            // 2. CODIFICATIONS JOURNAUX
            if (in_array('journals', $scope)) {
                $sourceJournals = CodeJournal::where('company_id', $sourceId)->get();
                $count = 0;
                foreach ($sourceJournals as $journal) {
                    $exists = CodeJournal::where('company_id', $targetId)
                        ->where('code_journal', $journal->code_journal)
                        ->exists();
                    
                    if (!$exists) {
                        $newJournal = $journal->replicate(['id', 'created_at', 'updated_at']);
                        $newJournal->company_id = $targetId;
                        $newJournal->save();
                        $count++;
                    }
                }
                $log[] = "$count codes journaux déversés.";
            }

            // 3. PLAN TIERS
            if (in_array('tiers', $scope)) {
                $sourceTiers = PlanTiers::where('company_id', $sourceId)->get();
                $count = 0;
                foreach ($sourceTiers as $tier) {
                    $exists = PlanTiers::where('company_id', $targetId)
                        ->where('numero_de_tiers', $tier->numero_de_tiers)
                        ->exists();
                    
                    if (!$exists) {
                        $newTier = $tier->replicate(['id', 'created_at', 'updated_at']);
                        $newTier->company_id = $targetId;
                        $newTier->save();
                        $count++;
                    }
                }
                $log[] = "$count tiers déversés.";
            }

            DB::commit();
            return back()->with('success', 'Déversement terminé avec succès : ' . implode(' ', $log));

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erreur lors de la fusion : ' . $e->getMessage());
        }
    }

    /**
     * Générer (ou régénérer) le code alphanumérique d'une entreprise.
     * L'ancien code est écrasé — toute session basée sur l'ancien code devient invalide.
     */
    public function generateCode($id)
    {
        $user = Auth::user();
        $company = Company::findOrFail($id);

        // Sécurité : seul le propriétaire ou un admin de l'entreprise peut régénérer
        $isOwner = $company->user_id === $user->id;
        $isAdmin = DB::table('company_user')
            ->where('company_id', $id)
            ->where('user_id', $user->id)
            ->where('role', 'admin')
            ->exists();

        if (!$isOwner && !$isAdmin) {
            return back()->with('error', 'Action non autorisée.');
        }

        // Génération alphanumérique : PREFIX-XXXXXX (ex: SPL-A3K8X2)
        $cleanName = preg_replace('/[^A-Za-z]/', '', $company->company_name);
        $prefix = strtoupper(substr($cleanName, 0, 3));
        if (strlen($prefix) < 3) {
            $prefix = str_pad($prefix, 3, 'X');
        }

        do {
            $code = $prefix . '-' . strtoupper(Str::random(6));
            $exists = Company::where('company_code', $code)->where('id', '!=', $id)->exists();
        } while ($exists);

        $company->company_code = $code;
        $company->save();

        return back()->with('success', 'Code généré avec succès : ' . $code);
    }

    /**
     * Générer automatiquement les codes pour toutes les entreprises qui n'en ont pas.
     */
    public function bulkGenerateCodes()
    {
        $user = Auth::user();

        // Récupérer les entreprises du gérant sans code
        $companies = Company::where('user_id', $user->id)
            ->whereNull('company_code')
            ->orWhere(function($q) use ($user) {
                $q->where('user_id', $user->id)->where('company_code', '');
            })
            ->get();

        $generated = 0;
        foreach ($companies as $company) {
            $cleanName = preg_replace('/[^A-Za-z]/', '', $company->company_name);
            $prefix = strtoupper(substr($cleanName, 0, 3));
            if (strlen($prefix) < 3) {
                $prefix = str_pad($prefix, 3, 'X');
            }
            do {
                $code = $prefix . '-' . strtoupper(Str::random(6));
                $exists = Company::where('company_code', $code)->where('id', '!=', $company->id)->exists();
            } while ($exists);

            $company->company_code = $code;
            $company->save();
            $generated++;
        }

        if ($generated === 0) {
            return back()->with('info', 'Toutes vos entreprises ont déjà un code d\'accès.');
        }

        return back()->with('success', $generated . ' code(s) généré(s) avec succès.');
    }
}
