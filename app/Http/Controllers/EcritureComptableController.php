<?php

namespace App\Http\Controllers;

use App\Models\PlanTiers;
use App\Models\User;
use Illuminate\Http\Request;
use App\Models\EcritureComptable;
use App\Models\ExerciceComptable;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use App\Models\AxeAnalytique;
use App\Models\SectionAnalytique;
use App\Models\VentilationAnalytique;
use App\Services\TaxValidationService;
use App\Models\PlanComptable;
use Carbon\Carbon;
use App\Models\CodeJournal;
use App\Models\CompteTresorerie;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Approval;
use App\Models\TreasuryCategory;
use App\Traits\HandlesTreasuryPosts;
use App\Services\NumerotationSaisie;

class EcritureComptableController extends Controller
{
    use HandlesTreasuryPosts;

    protected $taxService;

    public function __construct(TaxValidationService $taxService)
    {
        $this->taxService = $taxService;
    }
    public function index(Request $request)
    {
        $user = Auth::user();
        $activeCompanyId = session('current_company_id', $user->company_id);
        $data = $request->all();

        $exercicesCount = ExerciceComptable::where('company_id', $activeCompanyId)->count();
        if ($exercicesCount == 0) {
            return redirect()->route('exercice_comptable')->with('info', 'Veuillez créer un exercice comptable.');
        }

        $data['annee'] = $data['annee'] ?? date('Y');
        $data['mois'] = $data['mois'] ?? date('n');

        // ─────────────────────────────────────────────
        // RÉSOLUTION DE L'EXERCICE ACTIF (objet complet, toujours verrouillé sur le contexte de session)
        // Même ordre de priorité que EcritureComptableController::list() pour rester cohérent :
        // 1) exercice sélectionné en session (contexte utilisateur, sidebar)
        // 2) exercice actif par défaut
        // 3) dernier exercice non clôturé
        // ─────────────────────────────────────────────
        $exerciceContextId = session('current_exercice_id');
        $exerciceActif = null;

        if ($exerciceContextId) {
            $exerciceActif = ExerciceComptable::where('id', $exerciceContextId)
                ->where('company_id', $activeCompanyId)
                ->first();
        }

        if (!$exerciceActif) {
            $exerciceActif = ExerciceComptable::where('company_id', $activeCompanyId)
                ->where('is_active', 1)
                ->first();
        }

        if (!$exerciceActif) {
            $exerciceActif = ExerciceComptable::where('company_id', $activeCompanyId)
                ->where('cloturer', 0)
                ->orderBy('date_debut', 'desc')
                ->first();
        }

        if ($exerciceActif) {
            $data['id_exercice'] = $exerciceActif->id;
            $data['annee'] = date('Y', strtotime($exerciceActif->date_debut));
        }

        // LISTER UNIQUEMENT L'EXERCICE ACTIF (ou tous si aucun actif n'est défini pour permettre la sélection)
        $exercicesVisibles = ExerciceComptable::where('company_id', $activeCompanyId);
        if (ExerciceComptable::where('company_id', $activeCompanyId)->where('is_active', 1)->exists()) {
            $exercicesVisibles->where('is_active', 1);
        }
        $exercicesVisibles = $exercicesVisibles->get();

        $plansComptables = PlanComptable::where('company_id', $activeCompanyId)->select('id', 'numero_de_compte', 'intitule', 'numero_original')->orderBy('numero_de_compte')->get();
        $plansTiers = PlanTiers::where('company_id', $activeCompanyId)->select('id', 'numero_de_tiers', 'intitule', 'compte_general', 'numero_original')->with('compte')->get();
        $comptesTresorerie = CompteTresorerie::where('company_id', $activeCompanyId)->with('category')->orderBy('name')->get();
        $categories = TreasuryCategory::where('company_id', $activeCompanyId)
            ->whereIn('name', [
            'I. Flux de trésorerie des activités opérationnelles',
            'II. Flux de trésorerie des activités d\'investissement',
            'III. Flux de trésorerie des activités de financement',
        ])
            ->orderBy('name')
            ->get();

        // Journaux de la company, nécessaires à la grille de saisie combinée (sélecteur Journal + résolution contrepartie)
        $codeJournaux = CodeJournal::where('company_id', $activeCompanyId)->orderBy('code_journal')->get();

        // Modèles de saisie réutilisables (voir EcritureModele) — support serveur pour "Appeler un modèle"
        $modelesSaisie = class_exists(\App\Models\EcritureModele::class)
            ? \App\Models\EcritureModele::where('company_id', $activeCompanyId)->orderBy('nom')->get()
            : collect();

        // Numéro utilisateur du jour, au format CPT-XX-JJMMAA-000001.
        $nextSaisieNumber = NumerotationSaisie::utilisateur($activeCompanyId, $user);

        $query = EcritureComptable::where('company_id', $activeCompanyId);

        // Filtrer par exercice si présent (toujours résolu ci-dessus depuis le contexte verrouillé)
        if (!empty($data['id_exercice'])) {
            $query->where('exercices_comptables_id', $data['id_exercice']);
        }

        // Un import ecrit ses milliers de lignes dans la meme seconde : triees
        // sur created_at seul, les lignes d'une meme piece revenaient dans un
        // ordre quelconque et le tableau paraissait melange — au point de
        // faire croire que les filtres ne s'appliquaient pas. On trie donc par
        // date, puis par numero de saisie, puis par identifiant : chaque piece
        // reste d'un seul bloc.
        $ecritures = $query->with(['planComptable', 'planTiers', 'codeJournal', 'compteTresorerie', 'posteTresorerie'])
            ->orderBy('date')
            ->orderBy('n_saisie')
            ->orderBy('id')
            ->get();

        // Gestion du mode édition depuis l'approbation
        $approvalEditingData = null;
        if ($request->has('approval_edit')) {
            try {
                $approval = Approval::findOrFail($request->approval_edit);
                $nSaisie = $approval->data['n_saisie'] ?? null;

                if ($nSaisie) {
                    $lines = EcritureComptable::where('company_id', $activeCompanyId)
                        ->where('n_saisie', $nSaisie)
                        ->get();

                    if ($lines->isNotEmpty()) {
                        $first = $lines->first();
                        $approvalEditingData = [
                            'approval_id' => $approval->id,
                            'n_saisie' => $nSaisie,
                            'date' => $first->date,
                            'description' => $first->description_operation,
                            'reference' => $first->reference_piece,
                            'code_journal_id' => $first->code_journal_id,
                            'compte_tresorerie_id' => $first->compte_tresorerie_id,
                            'lines' => $lines
                        ];
                    }
                }
            }
            catch (\Exception $e) {
            // Ignorer ou logger l'erreur
            }
        }

        $axes = AxeAnalytique::where('company_id', $activeCompanyId)->get();
        $sections = SectionAnalytique::where('company_id', $activeCompanyId)->get();

        return view('accounting_entry_real', compact(
            'plansComptables', 'plansTiers', 'data', 'ecritures',
            'nextSaisieNumber', 'comptesTresorerie', 'exercicesVisibles',
            'approvalEditingData', 'categories', 'axes', 'sections',
            'codeJournaux', 'exerciceActif', 'modelesSaisie'
        ));
    }

    public function scanIndex(Request $request)
    {
        $user = Auth::user();
        $data = $request->all();

        $activeCompanyId = session('current_company_id', $user->company_id);

        // ─────────────────────────────────────────────
        // VERROU D'EXERCICE : un scan de facture doit toujours rester dans l'exercice
        // sélectionné dans le contexte de session (sidebar), jamais en dehors.
        // Même règle que JournauxSaisisController::find().
        // ─────────────────────────────────────────────
        $exerciceContextId = session('current_exercice_id');
        if ($exerciceContextId && $request->filled('id_exercice') && $request->input('id_exercice') != $exerciceContextId) {
            abort(403, "Vous ne pouvez pas scanner une facture pour un exercice différent de celui sélectionné dans le contexte.");
        }

        // Si aucun id_exercice n'est fourni (ou pour fiabiliser la valeur), on la résout nous-mêmes
        // depuis le contexte verrouillé plutôt que de faire confiance uniquement à la requête.
        $exerciceActifScan = null;
        if ($exerciceContextId) {
            $exerciceActifScan = ExerciceComptable::where('id', $exerciceContextId)
                ->where('company_id', $activeCompanyId)
                ->first();
        }
        if (!$exerciceActifScan) {
            $exerciceActifScan = ExerciceComptable::where('company_id', $activeCompanyId)
                ->where('is_active', 1)
                ->first();
        }
        if (!$exerciceActifScan) {
            $exerciceActifScan = ExerciceComptable::where('company_id', $activeCompanyId)
                ->where('cloturer', 0)
                ->orderBy('date_debut', 'desc')
                ->first();
        }
        if ($exerciceActifScan) {
            $data['id_exercice'] = $exerciceActifScan->id;
            $data['annee'] = date('Y', strtotime($exerciceActifScan->date_debut));
        }

        // Cloisonnement multi-entreprise identique au scan groupé.
        $plansComptables = PlanComptable::select('id', 'numero_de_compte', 'intitule')
            ->where('company_id', $activeCompanyId)
            ->orderBy('numero_de_compte')->get();
        $plansTiers = PlanTiers::select('id', 'numero_de_tiers', 'intitule', 'compte_general')
            ->where('company_id', $activeCompanyId)
            ->with('compte')->get();

        $nextSaisieNumber = NumerotationSaisie::utilisateur($activeCompanyId, $user);

        Log::debug("Génération N° Saisie (scanIndex) - Numéro: $nextSaisieNumber, Company: $activeCompanyId");

        $comptesTresorerie = CompteTresorerie::with('category')->orderBy('name')->get();

        return view('accounting.scan', compact('plansComptables', 'plansTiers', 'data', 'nextSaisieNumber', 'comptesTresorerie'));
    }

    public function bulkScanIndex(Request $request)
    {
        $user = Auth::user();
        $activeCompanyId = session('current_company_id', $user->company_id);

        $plansComptables = PlanComptable::select('id', 'numero_de_compte', 'intitule')
            ->where('company_id', $activeCompanyId)
            ->orderBy('numero_de_compte')
            ->get();

        $plansTiers = PlanTiers::select('id', 'numero_de_tiers', 'intitule', 'compte_general')
            ->where('company_id', $activeCompanyId)
            ->with('compte')
            ->get();

        $codeJournaux = CodeJournal::where('company_id', $activeCompanyId)->get();

        // ─────────────────────────────────────────────
        // VERROU D'EXERCICE : le Centre de Scan doit lui aussi rester dans l'exercice
        // sélectionné dans le contexte de session (même règle que scanIndex()).
        // ─────────────────────────────────────────────
        $exerciceContextId = session('current_exercice_id');
        $exerciceActif = null;

        if ($exerciceContextId) {
            $exerciceActif = ExerciceComptable::where('id', $exerciceContextId)
                ->where('company_id', $activeCompanyId)
                ->first();
        }

        if (!$exerciceActif) {
            $exerciceActif = ExerciceComptable::where('company_id', $activeCompanyId)
                ->where('is_active', 1)
                ->first();
        }

        if (!$exerciceActif) {
            $exerciceActif = ExerciceComptable::where('company_id', $activeCompanyId)
                ->where('cloturer', 0)
                ->orderBy('date_debut', 'desc')
                ->first();
        }

        $comptesTresorerie = CompteTresorerie::with('category')->orderBy('name')->get();
        $axes = AxeAnalytique::where('company_id', $activeCompanyId)->with('sections')->get();

        $nextSaisieNumber = NumerotationSaisie::utilisateur($activeCompanyId, $user);

        return view('accounting.bulk_scan', compact(
            'plansComptables',
            'plansTiers',
            'codeJournaux',
            'exerciceActif',
            'comptesTresorerie',
            'nextSaisieNumber',
            'axes'
        ));
    }

    private function determineFluxClasse($numeroCompte)
    {
        $classe = substr($numeroCompte, 0, 1);
        if (in_array($classe, ['6', '7']))
            return 'Operationnelles';
        if ($classe == '2')
            return 'Investissement';
        if ($classe == '1')
            return 'Financement';
        return null;
    }

    public function show($id)
    {
        try {
            $user = Auth::user();
            $primaryEcriture = EcritureComptable::where('company_id', $user->company_id)->findOrFail($id);
            $ecritures = EcritureComptable::with(['planComptable', 'planTiers', 'compteTresorerie', 'posteTresorerie', 'codeJournal'])
                ->where('company_id', $user->company_id)
                ->where('n_saisie', $primaryEcriture->n_saisie)
                ->orderBy('id', 'asc')
                ->get();

            return view('ecriture_show', compact('ecritures', 'primaryEcriture'));
        }
        catch (\Exception $e) {
            return redirect()->route('accounting_entry_list')->with('error', 'Écriture non trouvée : ' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        try {
            $user = Auth::user();
            $ecriture = EcritureComptable::with(['planComptable', 'planTiers', 'compteTresorerie', 'posteTresorerie', 'codeJournal'])
                ->where('company_id', $user->company_id)
                ->findOrFail($id);

            return response()->json([
                'success' => true,
                'ecriture' => $ecriture
            ]);
        }
        catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du chargement des données : ' . $e->getMessage()
            ], 500);
        }
    }

    public function loadBySaisie($n_saisie)
    {
        try {
            $user = Auth::user();
            $activeCompanyId = session('current_company_id', $user->company_id);

            $exerciceId = session('current_exercice_id');
            if (!$exerciceId) {
                $exerciceActif = ExerciceComptable::where('company_id', $activeCompanyId)->where('is_active', 1)->first();
                $exerciceId = $exerciceActif ? $exerciceActif->id : null;
            }

            // Récupérer toutes les lignes d'écriture pour ce n_saisie
            $query = EcritureComptable::with(['planComptable', 'planTiers', 'compteTresorerie', 'posteTresorerie.category', 'codeJournal'])
                ->where('company_id', $activeCompanyId)
                ->where('n_saisie', $n_saisie);

            if ($exerciceId) {
                $query->where('exercices_comptables_id', $exerciceId);
            }

            $ecritures = $query->orderBy('id')->get();

            if ($ecritures->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Aucune écriture trouvée pour ce numéro de saisie.'
                ], 404);
            }

            // Préparer les données dans le même format que les brouillons
            $first = $ecritures->first();

            $summary = [
                'date' => $first->date,
                'description' => $first->description_operation,
                'reference' => $first->reference_piece,
                'code_journal_id' => $first->code_journal_id,
                'journal_code' => $first->codeJournal ? $first->codeJournal->code_journal : '',
                'n_saisie' => $first->n_saisie,
                'compte_tresorerie_id' => $first->compte_tresorerie_id,
                'piece_justificatif' => $first->piece_justificatif
            ];

            return response()->json([
                'success' => true,
                'summary' => $summary,
                'brouillons' => $ecritures
            ]);
        }
        catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du chargement des données : ' . $e->getMessage()
            ], 500);
        }
    }

    public function deleteBySaisie($n_saisie)
    {
        try {
            $user = Auth::user();
            $activeCompanyId = session('current_company_id', $user->company_id);
            if (!$user) {
                return response()->json(['success' => false, 'message' => 'Utilisateur non authentifié.'], 401);
            }

            $exerciceId = session('current_exercice_id');
            if (!$exerciceId) {
                $exerciceActif = ExerciceComptable::where('company_id', $activeCompanyId)->where('is_active', 1)->first();
                $exerciceId = $exerciceActif ? $exerciceActif->id : null;
            }

            $query = EcritureComptable::where('company_id', $activeCompanyId)
                ->where('n_saisie', $n_saisie);

            if ($exerciceId) {
                $query->where('exercices_comptables_id', $exerciceId);
            }

            // Suppression tracee : chaque ligne part a l'archive des
            // suppressions et au journal d'audit avant de disparaître.
            $deleted = \App\Services\SuppressionTracee::supprimer($query);

            if ($deleted > 0) {
                return response()->json(['success' => true, 'message' => "$deleted lignes supprimées."]);
            }
            return response()->json(['success' => false, 'message' => "Aucune écriture trouvée."]);
        }
        catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => "Erreur : " . $e->getMessage()], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $user = Auth::user();
            $activeCompanyId = session('current_company_id', $user->company_id);

            // Récupérer l'exercice comptable actif
            $exerciceActif = ExerciceComptable::where('company_id', $activeCompanyId)
                ->where('is_active', 1)
                ->first();

            if (!$exerciceActif) {
                $exerciceActif = ExerciceComptable::where('company_id', $activeCompanyId)
                    ->where('cloturer', 0)
                    ->orderBy('date_debut', 'desc')
                    ->first();
            }

            if (!$exerciceActif || $exerciceActif->cloturer) {
                return response()->json([
                    'success' => false,
                    'message' => 'Impossible d\'enregistrer : L\'exercice comptable est clôturé ou inexistant.'
                ], 422);
            }

            // Valider les données (Note: assurez-vous que compte_tresorerie_id est bien envoyé par le JS)
            $data = $request->validate([
                'date' => [
                    'required',
                    'date',
                    function ($attribute, $value, $fail) use ($exerciceActif) {
                $dateSaisie = \Carbon\Carbon::parse($value);
                $dateDebut = \Carbon\Carbon::parse($exerciceActif->date_debut);
                $dateFin = \Carbon\Carbon::parse($exerciceActif->date_fin);

                if ($dateSaisie->lt($dateDebut) || $dateSaisie->gt($dateFin)) {
                    $fail("La date doit être comprise entre " . $dateDebut->format('d/m/Y') . " et " . $dateFin->format('d/m/Y'));
                }
            }
                ],
                'n_saisie' => 'required|string|max:50',
                'code_journal' => [
                    'required',
                    // La table s'appelle code_journals : « code_journaux » faisait
                    // échouer la validation sur une table inexistante.
                    \Illuminate\Validation\Rule::exists(CodeJournal::class, 'id')->where('company_id', $activeCompanyId)
                ],
                'description_operation' => 'required|string|max:255',
                'reference_piece' => 'nullable|string|max:50',
                'plan_comptable_id' => [
                    'required',
                    \Illuminate\Validation\Rule::exists('plan_comptables', 'id')->where('company_id', $activeCompanyId)
                ],
                'plan_tiers_id' => [
                    'nullable',
                    \Illuminate\Validation\Rule::exists('plan_tiers', 'id')->where('company_id', $activeCompanyId)
                ],
                'debit' => 'nullable|numeric|min:0',
                'credit' => 'nullable|numeric|min:0',
                'plan_analytique' => 'nullable|boolean',
                'piece_justificatif' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
                'compte_tresorerie_id' => 'nullable|integer',
                'poste_tresorerie_id' => 'nullable|integer',
            ]);

            $numeroCompteSaisi = PlanComptable::where('company_id', $activeCompanyId)
                ->whereKey($data['plan_comptable_id'])
                ->value('numero_de_compte');
            $estResultat = \App\Services\ComptesDeResultat::estCompteDeResultat($numeroCompteSaisi);

            if ($estResultat) {
                $debitResultat = (float) ($data['debit'] ?? 0);
                $creditResultat = (float) ($data['credit'] ?? 0);
                $sensResultatCorrect = str_starts_with((string) $numeroCompteSaisi, '1301')
                    ? ($creditResultat > 0 && $debitResultat <= 0)
                    : ($debitResultat > 0 && $creditResultat <= 0);
                if (!$sensResultatCorrect) {
                    return response()->json(['success' => false, 'message' => 'Une écriture sur 1301 ou 1309 doit comporter un seul montant, au débit ou au crédit, sur une seule ligne.'], 422);
                }
                if ($this->resultatDejaSaisi($activeCompanyId, $exerciceActif->id)) {
                    return response()->json(['success' => false, 'message' => 'L’écriture de résultat a déjà été passée pour cet exercice. Une seule écriture 1301/1309 est autorisée par exercice.'], 422);
                }
            }

            if ($request->plan_analytique || $request->input('plan_analytique')) {
                $ventilations = $request->input('ventilations', []);
                $validationError = $this->validateVentilations($ventilations, $activeCompanyId);
                if ($validationError) {
                    return response()->json([
                        'success' => false,
                        'message' => $validationError
                    ], 422);
                }
            }

            // Gestion du fichier
            if ($request->hasFile('piece_justificatif')) {
                $file = $request->file('piece_justificatif');
                $filename = time() . '_' . $file->getClientOriginalName();
                $file->move(public_path('justificatifs'), $filename);
                $data['piece_justificatif'] = $filename;
            }

            // Déterminer automatiquement le type de flux en fonction des montants
            $debit = $data['debit'] ?? 0;
            $credit = $data['credit'] ?? 0;
            $typeFlux = null;
            if ($debit > 0) {
                $typeFlux = 'encaissement'; // entrée d’argent
            }
            elseif ($credit > 0) {
                $typeFlux = 'decaissement'; // sortie d’argent
            }
            else {
                $typeFlux = null;
            }

            // Déterminer le statut initial
            $hasApprovalPower = $user->isSuperAdmin() || $user->hasPermission('admin.approvals');
            $status = $hasApprovalPower ? 'approved' : 'pending';

            $date = \Carbon\Carbon::parse($data['date']);
            $journalSaisi = \App\Models\JournalSaisi::firstOrCreate([
                'annee' => $date->year,
                'mois' => $date->month,
                'exercices_comptables_id' => $exerciceActif->id,
                'code_journals_id' => $data['code_journal'],
                'company_id' => $activeCompanyId,
            ], ['user_id' => $user->id]);

            // Générer le numéro approprié selon le statut
            $nSaisie = null;
            $nSaisieUser = $request->n_saisie; // Numéro utilisateur fourni par le formulaire

            if ($status === 'approved') {
                // Pour les écritures approuvées: générer le numéro global ECR_
                $nSaisie = $this->generateGlobalSaisieNumber($activeCompanyId, $exerciceActif->id);
            }
            else {
                // Pour les écritures en attente: utiliser le numéro utilisateur
                $nSaisie = $nSaisieUser;
            }

            // Préparer les données pour la création
            $ecritureData = [
                'company_id' => $activeCompanyId,
                'user_id' => $user->id,
                'n_saisie' => $nSaisie,
                'n_saisie_user' => $nSaisieUser,
                'code_journal_id' => $data['code_journal'],
                'exercices_comptables_id' => $exerciceActif->id,
                'date' => $data['date'],
                'description_operation' => $data['description_operation'],
                'reference_piece' => $data['reference_piece'] ?? null,
                'plan_comptable_id' => $data['plan_comptable_id'],
                'plan_tiers_id' => $data['plan_tiers_id'] ?? null,
                'debit' => $debit,
                'credit' => $credit,
                'type_flux' => $typeFlux,
                'plan_analytique' => $data['plan_analytique'] ?? false,
                'compte_tresorerie_id' => $request->compte_tresorerie_id ?? $data['compte_tresorerie_id'] ?? null,
                'poste_tresorerie_id' => $this->resolveTreasuryPost($activeCompanyId, $data['plan_comptable_id']) ?? ($request->poste_tresorerie_id ?? $data['poste_tresorerie_id'] ?? null),
                'piece_justificatif' => $data['piece_justificatif'] ?? null,
                'statut' => $status,
                'journaux_saisis_id' => $journalSaisi->id,
            ];

            $ecriture = EcritureComptable::create($ecritureData);

            if ($estResultat && $status === 'approved') {
                $exerciceActif->update(['resultat_passe' => true, 'resultat_passe_le' => now()]);
            }

            // Sauvegarder les ventilations si présentes
            if ($request->has('ventilations')) {
                $vnts = $request->input('ventilations');
                if (is_string($vnts)) {
                    $vnts = json_decode($vnts, true) ?? [];
                }
                if (is_array($vnts) && !empty($vnts)) {
                    foreach ($vnts as $v) {
                        $ecriture->ventilations()->create([
                            'section_id' => $v['section_id'],
                            'montant' => $v['montant'],
                            'pourcentage' => $v['pourcentage'],
                        ]);
                    }
                }
            }

            // Créer une demande d'approbation SI ce n'est pas déjà approuvé
            if ($status === 'pending') {
                Approval::create([
                    'approvable_type' => EcritureComptable::class ,
                    'approvable_id' => $ecriture->id,
                    'type' => 'accounting_entry',
                    'status' => 'pending',
                    'requested_by' => $user->id,
                    'data' => ['n_saisie' => $ecriture->n_saisie] // Stocker le numéro utilisateur
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => $status === 'approved' ? 'Écriture validée avec succès' : 'Écriture enregistrée (en attente d\'approbation)',
                'id' => $ecriture->id,
                'statut' => $status
            ]);

        }
        catch (\Exception $e) {
            Log::error('Échec enregistrement écriture', ['company_id' => $activeCompanyId ?? null, 'exception' => $e]);
            return response()->json(['success' => false, 'message' => 'Une erreur est survenue pendant l’enregistrement. Veuillez réessayer ou contacter le support.'], 500);
        }
    }

    public function storeMultiple(Request $request)
    {
        \Illuminate\Support\Facades\Log::info('EcritureComptableController@storeMultiple called', [
            'user_id' => auth()->id()
        ]);
        try {
            $user = Auth::user();
            if (!$user) {
                return response()->json(['success' => false, 'error' => 'Utilisateur non authentifié'], 401);
            }

            $activeCompanyId = session('current_company_id', $user->company_id);

            $ecrituresRaw = $request->input('ecritures') ?? $request->input('lignes');
            if (is_string($ecrituresRaw)) {
                $ecrituresRaw = json_decode($ecrituresRaw, true);
            }

            if (empty($ecrituresRaw) || !is_array($ecrituresRaw)) {
                return response()->json(['success' => false, 'error' => 'Aucune écriture à enregistrer.'], 400);
            }

            $firstEcriture = reset($ecrituresRaw);
            $exerciceIdFromInput = $firstEcriture['exercices_comptables_id'] 
                ?? $firstEcriture['exercice_id'] 
                ?? $firstEcriture['id_exercice'] 
                ?? null;

            // Le contexte de la barre latérale est la source de vérité serveur.
            // Une page de scan restée ouverte peut encore porter l'ancien ID :
            // on bascule alors sur l'exercice sélectionné en session sans demander
            // à l'utilisateur de recharger la page.
            $exerciseContextId = session('current_exercice_id');
            if ($exerciseContextId) {
                $exerciceActif = ExerciceComptable::where('company_id', $activeCompanyId)
                    ->whereKey($exerciseContextId)->first();
            } elseif ($exerciceIdFromInput) {
                $exerciceActif = ExerciceComptable::where('company_id', $activeCompanyId)->find($exerciceIdFromInput);
            } else {
                $exerciceActif = ExerciceComptable::where('company_id', $activeCompanyId)->where('is_active', 1)->first()
                    ?? ExerciceComptable::where('company_id', $activeCompanyId)->where('cloturer', 0)->orderBy('date_debut', 'desc')->first();
            }

            if (!$exerciceActif || $exerciceActif->cloturer) {
                return response()->json(['success' => false, 'error' => 'Impossible d\'enregistrer : L\'exercice est clôturé ou inexistant.'], 422);
            }

            // L'exercice est déjà résolu à partir du contexte de session ci-dessus.
            // Les dates et le company_id de chaque ligne restent vérifiés ci-dessous.

            foreach ($ecrituresRaw as $index => $line) {
                $accountId = $line['plan_comptable_id'] ?? $line['compte_general'] ?? null;
                $journalId = $line['code_journal_id'] ?? $line['journal_id'] ?? null;
                $tierId = $line['plan_tiers_id'] ?? $line['compte_tiers'] ?? null;
                $lineExerciseId = $line['exercices_comptables_id'] ?? $line['exercice_id'] ?? $line['id_exercice'] ?? $exerciceActif->id;

                if (!PlanComptable::where('company_id', $activeCompanyId)->whereKey($accountId)->exists()) {
                    return response()->json(['success' => false, 'error' => 'Un compte général de la pièce est invalide ou ne dépend pas de cette entreprise.'], 422);
                }
                if (!CodeJournal::where('company_id', $activeCompanyId)->whereKey($journalId)->exists()) {
                    return response()->json(['success' => false, 'error' => 'Un journal de la pièce est invalide ou ne dépend pas de cette entreprise.'], 422);
                }
                if ($tierId && !PlanTiers::where('company_id', $activeCompanyId)->whereKey($tierId)->exists()) {
                    return response()->json(['success' => false, 'error' => 'Un tiers de la pièce est invalide ou ne dépend pas de cette entreprise.'], 422);
                }
                // Si le formulaire est resté ouvert pendant un changement d'exercice,
                // son identifiant peut être obsolète. Le contexte session fait foi ;
                // sans contexte explicite, on continue à refuser les IDs arbitraires.
                if (!$exerciseContextId && (int) $lineExerciseId !== (int) $exerciceActif->id) {
                    return response()->json(['success' => false, 'error' => 'Toutes les lignes du scan doivent appartenir à l’exercice sélectionné.'], 422);
                }

                try {
                    $lineDate = \Carbon\Carbon::parse($line['date'] ?? now()->format('Y-m-d'));
                } catch (\Throwable $e) {
                    return response()->json(['success' => false, 'error' => 'Une date de facture est invalide.'], 422);
                }
                if ($lineDate->lt(\Carbon\Carbon::parse($exerciceActif->date_debut)) || $lineDate->gt(\Carbon\Carbon::parse($exerciceActif->date_fin))) {
                    return response()->json(['success' => false, 'error_type' => 'exercise_mismatch', 'error' => 'Cette facture est datée hors de l’exercice sélectionné. Aucune écriture n’a été enregistrée. Changez l’exercice comptable dans le sélecteur, puis relancez le scan.'], 422);
                }
            }

            // --- GROUP BY N_SAISIE ---
            $groups = [];
            foreach ($ecrituresRaw as $e) {
                $ns = $e['n_saisie'] ?? $e['numero_saisie'] ?? 'DEFAULT';
                $groups[$ns][] = $e;
            }

            // Les numeros des comptes utilises : l'ecriture de resultat s'y
            // reconnait, quelle que soit la longueur du plan du dossier.
            $idsComptes = collect($ecrituresRaw)->map(fn ($e) => $e['plan_comptable_id'] ?? $e['compte_general'] ?? null)->filter()->unique();
            $numerosDesComptes = PlanComptable::where('company_id', $activeCompanyId)
                ->whereIn('id', $idsComptes)
                ->pluck('numero_de_compte', 'id');

            // Un seul mouvement de résultat par lot, sur 1301 ou 1309.
            $lignesResultatLot = collect($ecrituresRaw)->filter(function ($e) use ($numerosDesComptes) {
                $idCompte = $e['plan_comptable_id'] ?? $e['compte_general'] ?? null;
                return \App\Services\ComptesDeResultat::estCompteDeResultat($numerosDesComptes[$idCompte] ?? null);
            });

            if ($lignesResultatLot->count() > 1) {
                return response()->json(['success' => false, 'error' => 'Le résultat doit être saisi sur une seule ligne, sans contrepartie.'], 422);
            }
            if ($lignesResultatLot->isNotEmpty()) {
                $ligneResultat = $lignesResultatLot->first();
                $debitResultat = (float) ($ligneResultat['debit'] ?? 0);
                $creditResultat = (float) ($ligneResultat['credit'] ?? 0);
                $numeroCompteResultat = $numerosDesComptes[$ligneResultat['plan_comptable_id'] ?? $ligneResultat['compte_general'] ?? null] ?? '';
                $sensResultatCorrect = str_starts_with((string) $numeroCompteResultat, '1301')
                    ? ($creditResultat > 0 && $debitResultat <= 0)
                    : ($debitResultat > 0 && $creditResultat <= 0);
                if (!$sensResultatCorrect) {
                    return response()->json(['success' => false, 'error' => 'Le compte 1301 reçoit un bénéfice au crédit ; le compte 1309 reçoit une perte au débit. Une seule ligne est autorisée.'], 422);
                }
                if ($this->resultatDejaSaisi($activeCompanyId, $exerciceActif->id)) {
                    return response()->json(['success' => false, 'error' => 'L’écriture de résultat a déjà été passée pour cet exercice.'], 422);
                }
            }

            // --- VALIDATE EACH GROUP BALANCE ---
            foreach ($groups as $ns => $groupEcritures) {
                $totalDebit = 0;
                $totalCredit = 0;
                $groupePorteResultat = false;
                foreach ($groupEcritures as $e) {
                    $totalDebit += floatval($e['debit'] ?? 0);
                    $totalCredit += floatval($e['credit'] ?? 0);
                    $idCompte = $e['plan_comptable_id'] ?? $e['compte_general'] ?? null;
                    $groupePorteResultat = $groupePorteResultat || \App\Services\ComptesDeResultat::estCompteDeResultat($numerosDesComptes[$idCompte] ?? null);
                }

                if ($groupePorteResultat) {
                    if (count($groupEcritures) !== 1) {
                        return response()->json(['success' => false, 'error' => "L'opération $ns utilise 1301/1309 : elle doit contenir une seule ligne, sans contrepartie."], 422);
                    }
                    continue;
                }

                $diff = abs($totalDebit - $totalCredit);
                if ($diff > 0.1) {
                    return response()->json([
                        'success' => false,
                        'error' => "Opération $ns déséquilibrée (D: $totalDebit != C: $totalCredit). Écart: $diff"
                    ], 422);
                }
            }

            // Validations analytiques avant de commencer la transaction
            foreach ($groups as $ns => $groupEcritures) {
                foreach ($groupEcritures as $e) {
                    $planAnalytique = (isset($e['plan_analytique']) && ($e['plan_analytique'] == 1 || $e['plan_analytique'] == true));
                    if ($planAnalytique) {
                        $ventilations = $e['ventilations'] ?? [];
                        if (is_string($ventilations)) {
                            $ventilations = json_decode($ventilations, true) ?? [];
                        }
                        $validationError = $this->validateVentilations($ventilations, $activeCompanyId);
                        if ($validationError) {
                            return response()->json([
                                'success' => false,
                                'error' => "Erreur de ventilation pour la pièce {$ns} : " . $validationError
                            ], 422);
                        }
                    }
                }
            }

            $hasApprovalPower = $user->isSuperAdmin() || $user->hasPermission('admin.approvals');
            $status = $hasApprovalPower ? 'approved' : 'pending';

            $pieceFilename = null;
            $file = $request->file('piece_justificatif');
            if ($file) {
                $pieceFilename = time() . '_' . preg_replace('/[^A-Za-z0-9._-]/', '_', $file->getClientOriginalName());
                $file->move(public_path('justificatifs'), $pieceFilename);
            }

            DB::beginTransaction();
            $globalNSaisieMap = []; // Cache for global sequence numbers (status=approved)
            $createdIds = [];

            $rootUserNS = $request->input('n_saisie') ?? 'DEFAULT';

            foreach ($groups as $userNS => $groupEcritures) {
                $resolvedUserNS = ($userNS === 'DEFAULT' || empty($userNS)) ? $rootUserNS : $userNS;
                $currentGlobalNS = null;
                if ($status === 'approved') {
                    $firstData = reset($groupEcritures);
                    $exerciceId = $firstData['exercices_comptables_id'] ?? $firstData['exercice_id'] ?? $firstData['id_exercice'] ?? ($exerciceActif ? $exerciceActif->id : null);
                    $currentGlobalNS = $this->generateGlobalSaisieNumber($activeCompanyId, $exerciceId);
                }

                $firstInGroup = null;

                foreach ($groupEcritures as $data) {
                    $planComptableId = !empty($data['plan_comptable_id']) ? $data['plan_comptable_id'] : ($data['compte_general'] ?? null);
                    if (!$planComptableId) throw new \Exception("Un compte général est requis pour l'opération $resolvedUserNS.");

                    $codeJournalId = !empty($data['code_journal_id']) ? $data['code_journal_id'] : ($data['journal_id'] ?? null);
                    if (!$codeJournalId) throw new \Exception("Un code journal est requis pour l'opération $resolvedUserNS.");

                    $dateString = !empty($data['date']) ? $data['date'] : now()->format('Y-m-d');
                    $date = \Carbon\Carbon::parse($dateString);
                    $exerciceId = $data['exercices_comptables_id'] ?? $data['exercice_id'] ?? $data['id_exercice'] ?? ($exerciceActif ? $exerciceActif->id : null);

                    $journalSaisi = \App\Models\JournalSaisi::firstOrCreate([
                        'annee' => $date->year,
                        'mois' => $date->month,
                        'exercices_comptables_id' => $exerciceId,
                        'code_journals_id' => $codeJournalId,
                        'company_id' => $activeCompanyId,
                    ], ['user_id' => $user->id]);

                    $ecriture = new EcritureComptable();
                    $ecriture->date = $dateString;
                    $ecriture->n_saisie = ($status === 'approved') ? $currentGlobalNS : $resolvedUserNS;
                    $ecriture->n_saisie_user = $resolvedUserNS;
                    $ecriture->description_operation = $data['description_operation'] ?? $data['libelle'] ?? '';
                    $ecriture->reference_piece = $data['reference_piece'] ?? $data['reference'] ?? null;
                    $ecriture->plan_comptable_id = $planComptableId;
                    $ecriture->plan_tiers_id = !empty($data['plan_tiers_id']) ? $data['plan_tiers_id'] : ($data['compte_tiers'] ?? null);
                    $ecriture->debit = $data['debit'] ?? 0;
                    $ecriture->credit = $data['credit'] ?? 0;
                    $ecriture->plan_analytique = (isset($data['plan_analytique']) && $data['plan_analytique'] == 1) ? 1 : 0;
                    $ecriture->code_journal_id = $codeJournalId;
                    $ecriture->company_id = $activeCompanyId;
                    $ecriture->user_id = $user->id;
                    $ecriture->piece_justificatif = $pieceFilename;
                    $ecriture->exercices_comptables_id = $exerciceId;
                    $ecriture->journaux_saisis_id = $journalSaisi->id;
                    $ecriture->statut = $status;
                    $ecriture->poste_tresorerie_id = $this->resolveTreasuryPost($activeCompanyId, $planComptableId) ?? ($data['poste_tresorerie_id'] ?? null);
                    $ecriture->save();

                    if (\App\Services\ComptesDeResultat::estCompteDeResultat($numerosDesComptes[$planComptableId] ?? null)
                        && $status === 'approved') {
                        ExerciceComptable::where('company_id', $activeCompanyId)->whereKey($exerciceId)
                            ->update(['resultat_passe' => true, 'resultat_passe_le' => now()]);
                    }

                    $createdIds[] = $ecriture->id;
                    if (!$firstInGroup) $firstInGroup = $ecriture;

                    if ($ecriture->plan_analytique && !empty($data['ventilations'])) {
                        $vnts = $data['ventilations'];
                        if (is_string($vnts)) {
                            $vnts = json_decode($vnts, true) ?? [];
                        }
                        foreach ($vnts as $v) {
                            $ecriture->ventilations()->create([
                                'section_id' => $v['section_id'],
                                'montant' => $v['montant'],
                                'pourcentage' => $v['pourcentage'],
                            ]);
                        }
                    }
                }

                if ($status === 'pending' && $firstInGroup) {
                    Approval::create([
                        'approvable_type' => EcritureComptable::class,
                        'approvable_id' => $firstInGroup->id,
                        'type' => 'accounting_entry',
                        'status' => 'pending',
                        'requested_by' => $user->id,
                        'data' => ['n_saisie' => $userNS]
                    ]);
                }
            }

            DB::commit();

            $batchId = $request->input('batch_id');
            if ($batchId) {
                \App\Models\Brouillon::where('batch_id', $batchId)->where('company_id', $activeCompanyId)->delete();
            }

            $createdEcritures = EcritureComptable::with(['codeJournal', 'planComptable', 'planTiers', 'posteTresorerie'])
                ->whereIn('id', $createdIds)
                ->get()
                ->map(function ($e) {
                    return [
                        'id' => $e->id,
                        'date' => $e->date,
                        'n_saisie' => $e->n_saisie,
                        'n_saisie_user' => $e->n_saisie_user,
                        'statut' => $e->statut,
                        'code_journal_id' => $e->code_journal_id,
                        'code_journal' => $e->codeJournal->code_journal ?? '',
                        'code_journal_original' => $e->codeJournal->numero_original ?? null,
                        'description_operation' => $e->description_operation,
                        'reference_piece' => $e->reference_piece,
                        'compte_general' => $e->planComptable->numero_de_compte ?? '',
                        'compte_general_intitule' => $e->planComptable->intitule ?? '',
                        'compte_general_original' => $e->planComptable->numero_original ?? null,
                        'compte_tiers' => $e->planTiers->numero_de_tiers ?? '',
                        'compte_tiers_intitule' => $e->planTiers->intitule ?? '',
                        'compte_tiers_original' => $e->planTiers->numero_original ?? null,
                        'analytique' => (bool) $e->plan_analytique,
                        'debit' => $e->debit,
                        'credit' => $e->credit,
                        'poste_tresorerie' => $e->posteTresorerie->name ?? '',
                        'poste_tresorerie_id' => $e->poste_tresorerie_id ?? null,
                        'piece' => (bool) $e->piece_justificatif,
                        'piece_url' => $e->piece_justificatif ? (str_starts_with($e->piece_justificatif, 'private/justificatifs/') ? route('ecriture.justificatif', $e->id) : asset('justificatifs/' . $e->piece_justificatif)) : null,
                    ];
                });

            return response()->json([
                'success' => true, 
                'message' => $status === 'approved' ? 'Opérations validées avec succès.' : 'Opérations Duo enregistrées (en attente).',
                'ecritures' => $createdEcritures
            ]);
        }
        catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Échec enregistrement du lot de scan', ['company_id' => $activeCompanyId ?? null, 'exception' => $e]);
            return response()->json(['success' => false, 'error' => 'Une erreur est survenue pendant la création des écritures. Aucune information technique ne peut être affichée.'], 500);
        }
    }

    // ... autres méthodes

    public function list(Request $request)
    {
        $user = Auth::user();
        $data = $request->all();
        $activeCompanyId = session('current_company_id', $user->company_id);

        // PRIORITÉ 1 : Exercice sélectionné en session (contexte utilisateur)
        $exerciceContextId = session('current_exercice_id');
        $exerciceActif = null;

        if ($exerciceContextId) {
            $exerciceActif = ExerciceComptable::where('id', $exerciceContextId)
                ->where('company_id', $activeCompanyId)
                ->first();
        }

        // PRIORITÉ 2 : Exercice actif par défaut
        if (!$exerciceActif) {
            $exerciceActif = ExerciceComptable::where('company_id', $activeCompanyId)
                ->where('is_active', 1)
                ->first();
        }

        // PRIORITÉ 3 : Dernier exercice non clôturé
        if (!$exerciceActif) {
            $exerciceActif = ExerciceComptable::where('company_id', $activeCompanyId)
                ->where('cloturer', 0)
                ->orderBy('date_debut', 'desc')
                ->first();
        }

        $baseQuery = EcritureComptable::where('ecriture_comptables.company_id', $activeCompanyId);

        // FILTRE EXERCICE STRICT
        if ($exerciceActif) {
            $baseQuery->where('ecriture_comptables.exercices_comptables_id', $exerciceActif->id);
        }
        else {
            // Si vraiment aucun exercice, on ne retourne rien (sécurité)
            $baseQuery->whereRaw('1 = 0');
        }

        // Logique de filtrage par rôle/permission
        if (!$user->isSuperAdmin() && !$user->hasPermission('admin.approvals')) {
            // Un collaborateur ne voit que ses propres écritures (tous statuts)
            $baseQuery->where('ecriture_comptables.user_id', $user->id);
        }
        else {
            // Un admin voit tout ce qui est validé + ses propres écritures en attente (s'il y en a)
            $baseQuery->where(function ($q) use ($user) {
                $q->where('ecriture_comptables.statut', 'approved')
                    ->orWhere('ecriture_comptables.user_id', $user->id);
            });
        }

        if (!empty($data['numero_saisie']))
            $baseQuery->where('ecriture_comptables.n_saisie', 'like', '%' . $data['numero_saisie'] . '%');
        if (!empty($data['code_journal']))
            $baseQuery->whereHas('codeJournal', function ($q) use ($data) {
                $q->where('code_journal', 'like', '%' . $data['code_journal'] . '%');
            });
        if (!empty($data['recherche'])) {
            $search = $data['recherche'];
            $baseQuery->where(function ($q) use ($search) {
                $q->where('ecriture_comptables.description_operation', 'like', '%' . $search . '%')
                    ->orWhereHas('planComptable', function ($sq) use ($search) {
                    $sq->where('numero_de_compte', 'like', '%' . $search . '%');
                }
                )
                    ->orWhereHas('planTiers', function ($sq) use ($search) {
                    $sq->where('numero_de_tiers', 'like', '%' . $search . '%');
                }
                );
            });
        }
        if (!empty($data['mois']))
            $baseQuery->whereMonth('ecriture_comptables.date', $data['mois']);
        if (!empty($data['statut']))
            $baseQuery->where('ecriture_comptables.statut', $data['statut']);

        if (!empty($data['etat_poste']) && $data['etat_poste'] !== '') {
            $baseQuery->whereHas('planComptable', function ($sq) {
                $sq->where('numero_de_compte', 'like', '5%');
            });

            if ($data['etat_poste'] === 'defini') {
                $baseQuery->whereNotNull('ecriture_comptables.poste_tresorerie_id');
            }
            elseif ($data['etat_poste'] === 'non_defini') {
                $baseQuery->whereNull('ecriture_comptables.poste_tresorerie_id');
            }
        }

        // NOUVEAUX FILTRES
        if (!empty($data['journal_id'])) {
            $baseQuery->where('ecriture_comptables.code_journal_id', $data['journal_id']);
        }
        if (!empty($data['journal_type'])) {
            $baseQuery->whereHas('codeJournal', function ($q) use ($data) {
                $q->where('type', 'like', $data['journal_type'] . '%');
            });
        }
        if (!empty($data['plan_tiers_id'])) {
            $baseQuery->where('ecriture_comptables.plan_tiers_id', $data['plan_tiers_id']);
        }
        if (!empty($data['tier_prefix'])) {
            $baseQuery->whereHas('planTiers', function ($q) use ($data) {
                $q->where('numero_de_tiers', 'like', $data['tier_prefix'] . '%');
            });
        }
        if (!empty($data['tier_type'])) {
            $baseQuery->whereHas('planTiers', function ($q) use ($data) {
                $q->where('type_de_tiers', 'like', $data['tier_type'] . '%');
            });
        }

        // CALCUL DES TOTAUX (sur toute la sélection filtrée)
        $totals = (clone $baseQuery)->select(
            DB::raw('SUM(ecriture_comptables.debit) as total_debit'),
            DB::raw('SUM(ecriture_comptables.credit) as total_credit')
        )->first();

        $totalDebit = $totals->total_debit ?? 0;
        $totalCredit = $totals->total_credit ?? 0;
        $balance = $totalDebit - $totalCredit;

        // Tri et pagination par Date d'écriture (récente en premier) et Numéro de saisie (récent en premier)
        $paginatedSaisies = (clone $baseQuery)
            ->select('ecriture_comptables.n_saisie', DB::raw('MAX(ecriture_comptables.date) as max_date'))
            ->groupBy('ecriture_comptables.n_saisie')
            ->orderBy('max_date', 'desc')
            ->orderBy('ecriture_comptables.n_saisie', 'desc')
            // Sans withQueryString, chaque lien de page repart sans les
            // filtres : le journal, le mois ou le libelle choisis etaient
            // perdus des la page 2.
            ->paginate(10)
            ->withQueryString();

        $saisieList = $paginatedSaisies->pluck('n_saisie')->toArray();

        // Les lignes affichées repartent de la requête filtrée, et non d'une
        // requête neuve : sinon les lignes écartées par les filtres reviennent
        // dès que plusieurs pièces partagent un même numéro de saisie, et le
        // mois, le journal ou le libellé demandés semblent ignorés.
        $ecritures = (clone $baseQuery)
            ->with(['planComptable', 'planTiers', 'compteTresorerie', 'posteTresorerie.category', 'codeJournal', 'ventilations.section.axe'])
            ->whereIn('ecriture_comptables.n_saisie', $saisieList)
            ->select('ecriture_comptables.*')
            ->orderBy('ecriture_comptables.date', 'desc')
            ->orderBy('ecriture_comptables.n_saisie', 'desc')
            ->orderBy('ecriture_comptables.id', 'asc')
            ->get();

        $code_journaux = CodeJournal::where('company_id', $activeCompanyId)->get();
        $treasury_categories = \App\Models\TreasuryCategory::where('company_id', $activeCompanyId)->get();
        $plansTiers = PlanTiers::where('company_id', $activeCompanyId)->orderBy('numero_de_tiers')->get();

        $selectedJournal = !empty($data['journal_id']) ? CodeJournal::find($data['journal_id']) : null;

        return view('accounting_entry_list', [
            'ecritures' => $ecritures,
            'exerciceActif' => $exerciceActif,
            'code_journaux' => $code_journaux,
            'selectedJournal' => $selectedJournal,
            'plansTiers' => $plansTiers,
            'treasury_categories' => $treasury_categories,
            'pagination' => $paginatedSaisies,
            'totalEntries' => $paginatedSaisies->total(),
            'totalDebit' => $totalDebit,
            'totalCredit' => $totalCredit,
            'balance' => $balance,
            'data' => $data,
            'exercices' => $exerciceActif ? collect([$exerciceActif]) : \App\Models\ExerciceComptable::where('company_id', $activeCompanyId)->get()
        ]);
    }

    public function deleteAll(Request $request)
    {
        try {
            $user = Auth::user();
            $activeCompanyId = session('current_company_id', $user->company_id);
            $exerciceId = session('current_exercice_id');

            if (!$exerciceId) {
                $exerciceActif = ExerciceComptable::where('company_id', $activeCompanyId)->where('is_active', 1)->first();
                $exerciceId = $exerciceActif ? $exerciceActif->id : null;
            }

            if (!$exerciceId) {
                return response()->json(['success' => false, 'message' => "Aucun exercice sélectionné."], 400);
            }

            $deleted = \App\Services\SuppressionTracee::supprimer(
                EcritureComptable::where('company_id', $activeCompanyId)
                    ->where('exercices_comptables_id', $exerciceId)
            );

            return response()->json([
                'success' => true,
                'message' => "$deleted écritures ont été supprimées totalement."
            ]);
        }
        catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => "Erreur : " . $e->getMessage()], 500);
        }
    }

    public function journalHub()
    {
        $user = Auth::user();
        $activeCompanyId = session('current_company_id', $user->company_id);
        $exerciceId = session('current_exercice_id');

        if (!$exerciceId) {
            $exerciceActif = ExerciceComptable::where('company_id', $activeCompanyId)->where('is_active', 1)->first();
            $exerciceId = $exerciceActif ? $exerciceActif->id : null;
        }

        $exerciceActif = ExerciceComptable::find($exerciceId);

        $journals = CodeJournal::where('company_id', $activeCompanyId)->get();

        // Get counts for the current exercice
        $counts = EcritureComptable::where('company_id', $activeCompanyId)
            ->where('exercices_comptables_id', $exerciceId)
            ->select('code_journal_id', DB::raw('count(distinct n_saisie) as total_entries'))
            ->groupBy('code_journal_id')
            ->get()
            ->pluck('total_entries', 'code_journal_id');

        // Dynamic grouping by type
        $groupedJournals = $journals->groupBy(function ($item) {
            return !empty($item->type) ? $item->type : 'Autres';
        });

        return view('ecriture_journal_hub', [
            'groupedJournals' => $groupedJournals,
            'exerciceActif' => $exerciceActif,
            'counts' => $counts
        ]);
    }

    public function getNextSaisieNumber(Request $request)
    {
        try {
            $user = Auth::user();
            if (!$user)
                return response()->json(['error' => 'Non authentifié'], 401);

            $activeCompanyId = session('current_company_id', $user->company_id);
            $prefix = NumerotationSaisie::prefixeUtilisateur($user);
            $formattedNumber = NumerotationSaisie::utilisateur($activeCompanyId, $user);

            Log::debug("API - Saisie calculée: $formattedNumber");

            return response()->json([
                'success' => true,
                'numero' => $formattedNumber,
                // Certains écrans lisent « nextSaisieNumber » : on sert les deux clés.
                'nextSaisieNumber' => $formattedNumber,
                'prefix' => $prefix
            ]);
        }
        catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Génère un numéro de saisie global au format ECR-JJMMAA-000001.
     */
    private function generateGlobalSaisieNumber($companyId, $exerciceId = null)
    {
        return NumerotationSaisie::global($companyId, $exerciceId);
    }

    public function getCompteParJournal(Request $request)
    {
        $journalId = $request->query('journal_id');
        // On cherche le compte de trésorerie lié au journal
        $journal = CodeJournal::with('compteTresorerie')->find($journalId);

        if ($journal && $journal->compteTresorerie) {
            return response()->json([
                'success' => true,
                'compte' => $journal->compteTresorerie
            ]);
        }
        return response()->json(['success' => false, 'message' => 'Aucun compte associé']);
    }
    public function rejectedList()
    {
        $user = Auth::user();
        if (!$user)
            return redirect()->route('login');

        $activeCompanyId = session('current_company_id', $user->company_id);

        // PRIORITÉ 1 : Exercice sélectionné en session (contexte utilisateur)
        $exerciceContextId = session('current_exercice_id');
        $exerciceActif = null;

        if ($exerciceContextId) {
            $exerciceActif = ExerciceComptable::where('id', $exerciceContextId)
                ->where('company_id', $activeCompanyId)
                ->first();
        }

        // PRIORITÉ 2 : Exercice actif par défaut
        if (!$exerciceActif) {
            $exerciceActif = ExerciceComptable::where('company_id', $activeCompanyId)
                ->where('is_active', 1)
                ->first();
        }

        // PRIORITÉ 3 : Dernier exercice non clôturé
        if (!$exerciceActif) {
            $exerciceActif = ExerciceComptable::where('company_id', $activeCompanyId)
                ->where('cloturer', 0)
                ->orderBy('date_debut', 'desc')
                ->first();
        }

        $query = EcritureComptable::with(['planComptable', 'planTiers', 'codeJournal', 'compteTresorerie'])
            ->where('company_id', $activeCompanyId)
            ->where('statut', 'rejected');

        // FILTRER par exercice actif
        if ($exerciceActif) {
            $query->where('exercices_comptables_id', $exerciceActif->id);
        }

        if (!$user->isSuperAdmin() && !$user->hasPermission('admin.approvals')) {
            $query->where('user_id', $user->id);
        }

        $ecritures = $query->orderBy('created_at', 'desc')->get();

        return view('accounting.rejected', compact('ecritures', 'exerciceActif'));
    }
    public function updateFromApproval(Request $request)
    {
        $user = Auth::user();
        if (!$user->isSuperAdmin() && !$user->hasPermission('admin.approvals')) {
            return response()->json(['success' => false, 'message' => 'Non autorisé'], 403);
        }

        try {
            DB::beginTransaction();

            $approvalId = $request->input('approval_id');
            $approval = Approval::findOrFail($approvalId);
            $oldNSaisie = $approval->data['n_saisie'];
            $activeCompanyId = session('current_company_id', $user->company_id);

            // 1. Supprimer les anciennes lignes (Pending)
            \App\Services\SuppressionTracee::supprimer(
                EcritureComptable::where('company_id', $activeCompanyId)
                    ->where('n_saisie', $oldNSaisie)
            );

            // 3. Créer les nouvelles lignes
            $ecritures = $request->input('ecritures');
            if (is_string($ecritures))
                $ecritures = json_decode($ecritures, true);

            $firstEcriture = is_array($ecritures) ? reset($ecritures) : null;
            $exerciceId = $firstEcriture ? ($firstEcriture['exercices_comptables_id'] ?? $firstEcriture['exercice_id'] ?? $firstEcriture['id_exercice'] ?? null) : null;

            // 2. Générer le NOUVEAU numéro global (Séquentiel)
            $newGlobalNSaisie = $this->generateGlobalSaisieNumber($activeCompanyId, $exerciceId);

            // Fichier
            $pieceFilename = null;
            $file = $request->file('piece_justificatif') ?? $request->file('ecritures.0.piece_justificatif');
            if ($file) {
                $pieceFilename = time() . '_' . preg_replace('/[^A-Za-z0-9._-]/', '_', $file->getClientOriginalName());
                $file->move(public_path('justificatifs'), $pieceFilename);
            }
            else {
            // Tenter de récupérer l'ancien fichier s'il n'y en a pas de nouveau ?
            // Idéalement il faudrait le passer dans le formulaire si on veut le garder.
            // Pour simplifier ici, on suppose qu'il faut le re-uploader ou gérer un champ hidden 'existing_file'
            }

            foreach ($ecritures as $data) {
                EcritureComptable::create([
                    'company_id' => $activeCompanyId,
                    'user_id' => $approval->requested_by, // Garder l'user original ? Ou mettre l'admin ? Mieux vaut garder l'original.
                    'n_saisie' => $newGlobalNSaisie, // Numéro GLOBAL
                    'n_saisie_user' => $oldNSaisie, // On garde le préfixe utilisateur original
                    'code_journal_id' => $data['code_journal_id'] ?? $data['journal_id'],
                    'exercices_comptables_id' => $data['exercices_comptables_id'] ?? $data['exercice_id'],
                    'date' => $data['date'],
                    'description_operation' => $data['description_operation'],
                    'reference_piece' => $data['reference_piece'] ?? null,
                    'plan_comptable_id' => $data['plan_comptable_id'] ?? $data['compte_general'],
                    'plan_tiers_id' => $data['plan_tiers_id'] ?? $data['compte_tiers'] ?? null,
                    'debit' => $data['debit'] ?? 0,
                    'credit' => $data['credit'] ?? 0,
                    'plan_analytique' => $data['plan_analytique'] ?? 0,
                    'compte_tresorerie_id' => $data['compte_tresorerie_id'] ?? null,
                    'piece_justificatif' => $pieceFilename, // Ou ancien fichier
                    'statut' => 'approved',
                    'admin_modified' => true,
                ]);
            }

            // 4. Mettre à jour l'approbation
            $approval->update([
                'status' => 'approved',
                'handled_by' => $user->id,
            ]);

            DB::commit();

            return response()->json(['success' => true, 'message' => 'Écriture modifiée et validée avec succès.', 'redirect' => route('approvals')]);

        }
        catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Soldes affichés dans l'en-tête de la saisie.
     *
     * Le journal choisi sert à DÉSIGNER les comptes de trésorerie à suivre —
     * son compte réglé, et ceux réellement mouvementés dans ce journal. Mais
     * les montants, eux, se lisent sur TOUS LES JOURNAUX, comme dans la
     * balance : le solde d'une caisse, c'est la caisse entière, et non la part
     * qui est passée par un journal.
     *
     * Auparavant tout était limité au journal. Une caisse partagée entre le
     * journal de caisse et les opérations diverses n'affichait donc qu'une
     * tranche, sous un libellé — « Solde août » — qui annonce un solde de
     * compte. Le cadre ne pouvait pas tomber d'accord avec la balance, et le
     * nouveau solde pouvait paraître créditeur alors que la caisse était
     * pleine.
     *
     * Chaque compte a sa ligne, pour que le chiffre soit vérifiable un par un
     * sur la balance. Un journal sans compte de trésorerie (achats, ventes)
     * garde une lecture par journal, sur toutes ses écritures.
     *
     * Les écritures rejetées ne comptent pas.
     */
    public function soldesJournal(Request $request)
    {
        $user = Auth::user();
        $companyId = session('current_company_id', $user->company_id);

        $journal = CodeJournal::where('company_id', $companyId)->find($request->integer('journal_id'));
        if (!$journal) {
            return response()->json(['success' => false, 'message' => 'Choisissez un journal.'], 422);
        }

        $exercice = ExerciceComptable::where('company_id', $companyId)
            ->find($request->integer('exercice_id') ?: session('current_exercice_id'));
        if (!$exercice) {
            return response()->json(['success' => false, 'message' => 'Aucun exercice ouvert.'], 422);
        }

        $debutExercice = Carbon::parse($exercice->date_debut)->startOfDay();
        $finExercice = Carbon::parse($exercice->date_fin)->endOfDay();

        // Le mois choisi, replacé dans l'exercice (un exercice peut chevaucher deux années).
        $mois = (int) $request->input('mois');
        $debutPeriode = $debutExercice->copy();
        $finPeriode = $finExercice->copy();
        if ($mois >= 1 && $mois <= 12) {
            $curseur = $debutExercice->copy()->startOfMonth();
            while ($curseur->lte($finExercice) && $curseur->month !== $mois) {
                $curseur->addMonth();
            }
            if ($curseur->lte($finExercice)) {
                $debutPeriode = $curseur->copy()->max($debutExercice);
                $finPeriode = $curseur->copy()->endOfMonth()->min($finExercice);
            }
        }

        // Compte de trésorerie du journal : l'identifiant du plan, sinon son numéro.
        $compteId = $journal->compte_de_tresorerie;
        if (!$compteId && $journal->compte_de_contrepartie) {
            $compteId = PlanComptable::where('company_id', $companyId)
                ->where('numero_de_compte', $journal->compte_de_contrepartie)
                ->value('id');
        }
        $compte = $compteId ? PlanComptable::where('company_id', $companyId)->find($compteId) : null;

        $retenues = fn ($q) => $q->whereNull('statut')->orWhere('statut', '!=', 'rejected');

        $duJournal = EcritureComptable::where('company_id', $companyId)
            ->where('exercices_comptables_id', $exercice->id)
            ->where('code_journal_id', $journal->id)
            ->where($retenues);

        // Les comptes suivis. Le compte réglé sur le journal ne correspond pas
        // toujours à celui des écritures (plan importé, compte renuméroté :
        // journal réglé sur 55200000, écritures sur 55200100) ; tout restait
        // alors à zéro. On prend donc le compte réglé ET les comptes de
        // trésorerie réellement mouvementés dans ce journal : classe 5, hors
        // virements internes (58), qui sont des contreparties.
        $estTresorerie = $compte || in_array($journal->type, ['Banque', 'Caisse', 'Trésorerie', 'Tresorerie'], true);
        $comptesLus = collect();
        if ($estTresorerie) {
            $comptesLus = PlanComptable::where('company_id', $companyId)
                ->where(function ($q) use ($duJournal, $compte) {
                    $q->where(function ($r) use ($duJournal) {
                        $r->whereIn('id', (clone $duJournal)->select('plan_comptable_id')->distinct())
                            ->where('numero_de_compte', 'like', '5%')
                            ->where('numero_de_compte', 'not like', '58%');
                    });
                    if ($compte) {
                        $q->orWhere('id', $compte->id);
                    }
                })
                ->orderBy('numero_de_compte')
                ->get(['id', 'numero_de_compte', 'intitule']);
        }

        // « Solde février » quand on saisit mars : le solde porte le nom du mois qui le clôt.
        $nomsMois = [1 => 'janvier', 'février', 'mars', 'avril', 'mai', 'juin', 'juillet', 'août', 'septembre', 'octobre', 'novembre', 'décembre'];
        $libelleAncien = $debutPeriode->gt($debutExercice) || ($mois >= 1 && $mois <= 12)
            ? 'Solde ' . $nomsMois[$debutPeriode->copy()->subMonthNoOverflow()->month]
            : "Solde d'ouverture";

        $veille = $debutPeriode->toDateString();
        $jusqua = $finPeriode->toDateString();

        // Les montants se lisent sur tous les journaux — c'est ce que fait la
        // balance, et c'est la seule lecture qu'un comptable puisse recouper.
        $surLesComptes = fn () => EcritureComptable::where('company_id', $companyId)
            ->where('exercices_comptables_id', $exercice->id)
            ->where($retenues);

        $lignes = [];
        $totalAncien = $totalDebit = $totalCredit = 0.0;
        $totalDebitIci = $totalCreditIci = $totalDebitAilleurs = $totalCreditAilleurs = 0.0;

        if ($comptesLus->isNotEmpty()) {
            $ids = $comptesLus->pluck('id');

            $avant = $surLesComptes()
                ->whereIn('plan_comptable_id', $ids)
                ->whereDate('date', '<', $veille)
                ->groupBy('plan_comptable_id')
                ->selectRaw('plan_comptable_id, COALESCE(SUM(debit), 0) AS debit, COALESCE(SUM(credit), 0) AS credit')
                ->get()->keyBy('plan_comptable_id');

            // Les mouvements du mois, separes en deux : ceux passes par le
            // journal ouvert, et ceux passes ailleurs.
            //
            // Le cadre annoncait un journal et montrait des montants tous
            // journaux confondus, sans le dire. Le solde, lui, DOIT rester
            // celui du compte entier — sinon il ne se recoupe plus avec la
            // balance. Mais on montre desormais d'ou vient chaque mouvement.
            $mouvements = function (bool $duJournalOuvert) use ($surLesComptes, $ids, $veille, $jusqua, $journal) {
                return $surLesComptes()
                    ->whereIn('plan_comptable_id', $ids)
                    ->whereDate('date', '>=', $veille)
                    ->whereDate('date', '<=', $jusqua)
                    ->where(fn ($q) => $duJournalOuvert
                        ? $q->where('code_journal_id', $journal->id)
                        : $q->where('code_journal_id', '!=', $journal->id))
                    ->groupBy('plan_comptable_id')
                    ->selectRaw('plan_comptable_id, COALESCE(SUM(debit), 0) AS debit, COALESCE(SUM(credit), 0) AS credit')
                    ->get()->keyBy('plan_comptable_id');
            };

            $ici = $mouvements(true);
            $ailleurs = $mouvements(false);

            foreach ($comptesLus as $c) {
                $a = $avant->get($c->id);
                $i = $ici->get($c->id);
                $ail = $ailleurs->get($c->id);

                $ancien = round((float) ($a->debit ?? 0) - (float) ($a->credit ?? 0), 2);

                $debitIci = round((float) ($i->debit ?? 0), 2);
                $creditIci = round((float) ($i->credit ?? 0), 2);
                $debitAilleurs = round((float) ($ail->debit ?? 0), 2);
                $creditAilleurs = round((float) ($ail->credit ?? 0), 2);

                $debit = round($debitIci + $debitAilleurs, 2);
                $credit = round($creditIci + $creditAilleurs, 2);

                $nouveau = round($ancien + $debit - $credit, 2);

                // Le compte regle sur le journal est suivi meme s'il ne bouge
                // pas — mais une ligne entierement a zero n'apprend rien et
                // laisse croire a un compte « qui a des montants sans
                // ecritures ». On ne l'affiche pas.
                if (abs($ancien) < 0.01 && abs($debit) < 0.01 && abs($credit) < 0.01) {
                    continue;
                }

                $lignes[] = [
                    'numero' => $c->numero_de_compte,
                    'intitule' => trim((string) $c->intitule),
                    'ancien' => $ancien,
                    'debit' => $debit,
                    'credit' => $credit,
                    'debit_ici' => $debitIci,
                    'credit_ici' => $creditIci,
                    'debit_ailleurs' => $debitAilleurs,
                    'credit_ailleurs' => $creditAilleurs,
                    'nouveau' => $nouveau,
                ];

                $totalAncien += $ancien;
                $totalDebit += $debit;
                $totalCredit += $credit;
                $totalDebitIci += $debitIci;
                $totalCreditIci += $creditIci;
                $totalDebitAilleurs += $debitAilleurs;
                $totalCreditAilleurs += $creditAilleurs;
            }
        } else {
            // Journal sans compte de trésorerie : on lit le journal lui-même.
            $avant = (clone $duJournal)->whereDate('date', '<', $veille)
                ->selectRaw('COALESCE(SUM(debit), 0) AS debit, COALESCE(SUM(credit), 0) AS credit')->first();
            $pendant = (clone $duJournal)->whereDate('date', '>=', $veille)->whereDate('date', '<=', $jusqua)
                ->selectRaw('COALESCE(SUM(debit), 0) AS debit, COALESCE(SUM(credit), 0) AS credit')->first();

            $totalAncien = (float) $avant->debit - (float) $avant->credit;
            $totalDebit = $totalDebitIci = (float) $pendant->debit;
            $totalCredit = $totalCreditIci = (float) $pendant->credit;
        }

        $totalAncien = round($totalAncien, 2);
        $totalDebit = round($totalDebit, 2);
        $totalCredit = round($totalCredit, 2);

        return response()->json([
            'success' => true,
            'journal' => $journal->code_journal,
            'compte' => $comptesLus->pluck('numero_de_compte')->implode(', ') ?: null,
            'compte_regle' => $compte?->numero_de_compte,
            'tous_journaux' => $comptesLus->isNotEmpty(),
            'periode' => [$debutPeriode->format('d/m/Y'), $finPeriode->format('d/m/Y')],
            'libelle_ancien' => $libelleAncien,
            'comptes' => $lignes,
            // Conservés pour les appels qui ne lisent que le total.
            'ancien_solde' => $totalAncien,
            'mouvements' => [
                'debit' => $totalDebit,
                'credit' => $totalCredit,
                'debit_ici' => round($totalDebitIci, 2),
                'credit_ici' => round($totalCreditIci, 2),
                'debit_ailleurs' => round($totalDebitAilleurs, 2),
                'credit_ailleurs' => round($totalCreditAilleurs, 2),
            ],
            'nouveau_solde' => round($totalAncien + $totalDebit - $totalCredit, 2),
        ]);
    }

    public function checkReference(Request $request)
    {
        try {
            $user = Auth::user();
            if (!$user) {
                return response()->json(['error' => 'Non autorisé'], 401);
            }

            $reference = $request->query('reference');
            if (empty($reference)) {
                return response()->json(['is_duplicate' => false]);
            }

            $activeCompanyId = session('current_company_id', $user->company_id);

            // Get exercise context: either passed as id_exercice, from session, or default active/latest
            $exerciceId = $request->query('id_exercice') 
                ?? session('current_exercice_id');

            if (!$exerciceId) {
                $exerciceActif = ExerciceComptable::where('company_id', $activeCompanyId)
                    ->where('is_active', 1)
                    ->first() 
                    ?? ExerciceComptable::where('company_id', $activeCompanyId)
                    ->where('cloturer', 0)
                    ->orderBy('date_debut', 'desc')
                    ->first();
                $exerciceId = $exerciceActif ? $exerciceActif->id : null;
            }

            if (!$exerciceId) {
                return response()->json(['is_duplicate' => false]);
            }

            $exists = EcritureComptable::where('company_id', $activeCompanyId)
                ->where('exercices_comptables_id', $exerciceId)
                ->where('reference_piece', $reference)
                ->exists();

            return response()->json(['is_duplicate' => $exists]);
        }
        catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    private function validateVentilations($ventilations, $companyId)
    {
        if (empty($ventilations)) {
            return "L'écriture analytique doit comporter au moins une ventilation.";
        }

        if (!$companyId) {
            return "Entreprise active introuvable pour valider les ventilations.";
        }

        $sectionIds = collect($ventilations)->pluck('section_id')->filter()->unique()->values()->all();
        $sections = \App\Models\SectionAnalytique::where('company_id', $companyId)
            ->whereIn('id', $sectionIds)
            ->get()
            ->keyBy('id');

        // Group ventilations by axis
        $groupedByAxe = [];
        foreach ($ventilations as $v) {
            $secId = $v['section_id'] ?? null;
            if (!$secId || !$sections->has($secId)) {
                return "Section analytique invalide ou introuvable.";
            }
            $axeId = $sections->get($secId)->axe_id;
            $groupedByAxe[$axeId][] = floatval($v['pourcentage']);
        }

        if (empty($groupedByAxe)) {
            return "Aucun axe analytique valide n'est défini.";
        }

        foreach ($groupedByAxe as $axeId => $percentages) {
            $sum = array_sum($percentages);
            if (abs($sum - 100) > 0.05) {
                $axe = \App\Models\AxeAnalytique::where('company_id', $companyId)->find($axeId);
                $axeName = $axe ? $axe->libelle : "inconnu";
                return "Le total des pourcentages pour l'axe '{$axeName}' doit être égal à 100%. Actuellement : " . round($sum, 2) . "%.";
            }
        }

        return null;
    }
    /** Une écriture 1301/1309 existe déjà dans l'exercice. */
    private function resultatDejaSaisi(int $companyId, int $exerciceId): bool
    {
        $exercice = ExerciceComptable::where('company_id', $companyId)->find($exerciceId);
        if ($exercice && (bool) $exercice->resultat_passe) {
            return true;
        }

        return EcritureComptable::where('company_id', $companyId)
            ->where('exercices_comptables_id', $exerciceId)
            ->where(function ($q) {
                $q->whereNull('statut')->orWhereNotIn('statut', ['rejected', 'refused']);
            })
            ->where(function ($q) {
                $q->where('is_ran', false)->orWhereNull('is_ran');
            })
            ->whereHas('planComptable', function ($q) {
                $q->where('numero_de_compte', 'like', '1301%')
                    ->orWhere('numero_de_compte', 'like', '1309%');
            })
            ->exists();
    }
}
