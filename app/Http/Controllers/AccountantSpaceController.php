<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Company;
use App\Models\User;
use App\Models\PlanComptable;
use App\Models\PlanTiers;
use App\Models\CodeJournal;
use App\Models\TreasuryCategory;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AccountantSpaceController extends Controller
{
    /**
     * Espace Comptable centralisé
     */
    public function index()
    {
        $user = Auth::user();

        // 1. Récupérer toutes les entreprises gérées ou associées
        $myCompanyIds = Company::where('user_id', $user->id)->pluck('id')->toArray();
        $assignedCompanyIds = DB::table('company_user')->where('user_id', $user->id)->pluck('company_id')->toArray();
        $allCompanyIds = array_unique(array_merge($myCompanyIds, $assignedCompanyIds));

        $companies = Company::whereIn('id', $allCompanyIds)->get();

        // Charger les KPIs pour chaque entreprise
        $companiesData = $companies->map(function ($comp) use ($user) {
            $isOwner = $comp->user_id === $user->id;
            
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

            // Utilisateurs affectés à cette entreprise
            $assignedUsers = DB::table('company_user')
                ->join('users', 'company_user.user_id', '=', 'users.id')
                ->where('company_user.company_id', $comp->id)
                ->select('users.id', 'users.name', 'users.last_name', 'users.email_adresse', 'company_user.role')
                ->get();

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

        // 2. Collaborateurs gérés (créés par cet utilisateur)
        $collaborators = User::where('created_by_id', $user->id)->get();

        // 3. Utilisateurs pour le Chat
        // Tout utilisateur connecté à une de mes entreprises, ou mes collaborateurs, ou les créateurs des entreprises auxquelles je suis affecté
        $chatUserIds = DB::table('company_user')->whereIn('company_id', $allCompanyIds)->where('user_id', '!=', $user->id)->pluck('user_id')->toArray();
        $myCreatedUserIds = User::where('created_by_id', $user->id)->pluck('id')->toArray();
        $creatorsOfMyCompanies = Company::whereIn('id', $allCompanyIds)->pluck('user_id')->toArray();

        $allChatUserIds = array_unique(array_merge($chatUserIds, $myCreatedUserIds, $creatorsOfMyCompanies));
        $allChatUserIds = array_diff($allChatUserIds, [$user->id]);

        $chatUsers = User::whereIn('id', $allChatUserIds)->get();

        // 4. Statistiques globales
        $stats = [
            'total_companies' => count($allCompanyIds),
            'owned_companies' => count($myCompanyIds),
            'assigned_companies' => count($assignedCompanyIds),
            'total_collaborators' => $collaborators->count(),
            'total_entries' => DB::table('ecriture_comptables')->whereIn('company_id', $allCompanyIds)->count()
        ];

        return view('accountant.index', compact('companiesData', 'collaborators', 'chatUsers', 'stats'));
    }

    /**
     * Création d'une entreprise avec génération de code unique sécurisé
     */
    public function storeCompany(Request $request)
    {
        $request->validate([
            'company_name' => 'required|string|max:255|unique:companies,company_name',
            'activity' => 'required|string|max:255',
            'juridique_form' => 'required|string|max:255',
            'social_capital' => 'nullable|numeric|min:0',
            'adresse' => 'required|string|max:255',
            'code_postal' => 'required|string|max:20',
            'city' => 'required|string|max:50',
            'country' => 'required|string|max:255',
            'phone_number' => 'required|string|min:10',
            'email_adresse' => 'required|email|max:191|unique:companies,email_adresse',
            'identification_TVA' => 'nullable|string|max:50',
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
            ]);

            // Liaison dans la table pivot company_user
            DB::table('company_user')->insert([
                'company_id' => $company->id,
                'user_id' => $user->id,
                'role' => 'admin',
                'created_at' => now(),
                'updated_at' => now()
            ]);

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
        $request->validate([
            'name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email_adresse' => 'required|email|max:191|unique:users,email_adresse',
            'password' => 'required|string|min:6',
            'role' => 'required|in:admin,comptable',
        ]);

        User::create([
            'name' => $request->name,
            'last_name' => $request->last_name,
            'email_adresse' => $request->email_adresse,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'is_active' => true,
            'created_by_id' => Auth::id(),
        ]);

        return redirect()->route('accountant.space')->with('success', 'Collaborateur créé avec succès.');
    }

    /**
     * Associer un utilisateur à une entreprise
     */
    public function assignUser(Request $request)
    {
        $request->validate([
            'company_id' => 'required|exists:companies,id',
            'user_id' => 'required|exists:users,id',
            'role' => 'required|in:admin,comptable',
        ]);

        $user = Auth::user();
        
        // Vérifier que le gérant possède l'entreprise
        $company = Company::findOrFail($request->company_id);
        if ($company->user_id !== $user->id && !DB::table('company_user')->where('company_id', $company->id)->where('user_id', $user->id)->where('role', 'admin')->exists()) {
            return back()->with('error', 'Vous n’avez pas le droit d’affecter des collaborateurs à cette entreprise.');
        }

        DB::table('company_user')->updateOrInsert(
            ['company_id' => $request->company_id, 'user_id' => $request->user_id],
            ['role' => $request->role, 'updated_at' => now(), 'created_at' => now()]
        );

        // Envoyer une notification interne à la cloche
        \App\Models\InternalNotification::create([
            'sender_id' => $user->id,
            'receiver_id' => $request->user_id,
            'title' => 'Affectation à une entreprise',
            'content' => 'Vous avez été affecté à l\'entreprise ' . $company->company_name . ' en tant que ' . $request->role,
            'type' => 'info',
            'is_read' => false,
        ]);

        return redirect()->route('accountant.space')->with('success', 'Collaborateur affecté à l’entreprise avec succès.');
    }

    /**
     * Retirer un utilisateur d'une entreprise
     */
    public function removeUser(Request $request)
    {
        $request->validate([
            'company_id' => 'required|exists:companies,id',
            'user_id' => 'required|exists:users,id',
        ]);

        $user = Auth::user();
        $company = Company::findOrFail($request->company_id);
        if ($company->user_id !== $user->id) {
            return back()->with('error', 'Action non autorisée.');
        }

        // Ne pas se détacher soi-même si propriétaire
        if ($request->user_id == $user->id) {
            return back()->with('error', 'Vous ne pouvez pas vous détacher vous-même de votre entreprise.');
        }

        DB::table('company_user')
            ->where('company_id', $request->company_id)
            ->where('user_id', $request->user_id)
            ->delete();

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

        // Redirection vers le tableau de bord de l'entreprise
        if ($user->role === 'admin') {
            return redirect()->route('admin.dashboard');
        } else {
            return redirect()->route('comptable.comptdashboard');
        }
    }

    /**
     * Fusionner/Déverser des données d'une entreprise A vers une entreprise B
     */
    public function fusionData(Request $request)
    {
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
