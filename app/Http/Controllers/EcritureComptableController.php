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

        if (empty($data['id_exercice'])) {
            $exerciceActif = ExerciceComptable::where('company_id', $activeCompanyId)
                ->where('is_active', 1)
                ->first();

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

        // Générer le numéro utilisateur au format CPT-XX_000000000001
        $initials = $user->initiales;
        $prefix = "CPT-" . $initials . "_";
        $lastUserSaisie = EcritureComptable::where('company_id', $activeCompanyId)
            ->where('n_saisie_user', 'like', $prefix . '%')
            ->max('id');

        $nextSequence = ($lastUserSaisie ? $lastUserSaisie + 1 : 1);
        // Note: Pour être plus précis on devrait compter les n_saisie_user distincts mais count(distinct) ou max sur la sequence suffixe est mieux.
        // On va utiliser une approche simple pour l'init :
        $nextSaisieNumber = $prefix . str_pad($nextSequence, 12, '0', STR_PAD_LEFT);

        $query = EcritureComptable::where('company_id', $activeCompanyId);

        // Filtrer par exercice si présent
        if (!empty($data['id_exercice'])) {
            $query->where('exercices_comptables_id', $data['id_exercice']);
        }

        $ecritures = $query->with(['planComptable', 'planTiers', 'compteTresorerie', 'posteTresorerie'])
            ->orderBy('created_at', 'desc')
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
            'approvalEditingData', 'categories', 'axes', 'sections'
        ));
    }

    public function scanIndex(Request $request)
    {
        $user = Auth::user();
        $data = $request->all();

        $plansComptables = PlanComptable::select('id', 'numero_de_compte', 'intitule')->orderBy('numero_de_compte')->get();
        $plansTiers = PlanTiers::select('id', 'numero_de_tiers', 'intitule', 'compte_general')->with('compte')->get();

        $activeCompanyId = session('current_company_id', $user->company_id);
        $initials = $user->initiales;
        $prefix = "CPT-" . $initials . "_";

        Log::debug("Génération N° Saisie (scanIndex) - Prefix: $prefix, Company: $activeCompanyId");

        // Utiliser le nombre d'écritures distinctes + 1 pour suivre une suite logique 1, 2, 3...
        $nextSequence = EcritureComptable::where('company_id', $activeCompanyId)
            ->where('n_saisie_user', 'like', $prefix . '%')
            ->distinct('n_saisie_user')
            ->count('n_saisie_user') + 1;

        // Boucle de sécurité pour trouver le premier numéro réellement disponible
        do {
            $nextSaisieNumber = $prefix . str_pad($nextSequence, 12, '0', STR_PAD_LEFT);
            $existe = EcritureComptable::where('company_id', $activeCompanyId)
                ->where('n_saisie_user', $nextSaisieNumber)
                ->exists();
            if ($existe)
                $nextSequence++;
        } while ($existe);

        Log::debug("Saisie calculée: $nextSaisieNumber (Séquence: $nextSequence)");

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

        $exerciceActif = ExerciceComptable::where('company_id', $activeCompanyId)
            ->where('is_active', 1)
            ->first();

        if (!$exerciceActif) {
            $exerciceActif = ExerciceComptable::where('company_id', $activeCompanyId)
                ->where('cloturer', 0)
                ->orderBy('date_debut', 'desc')
                ->first();
        }

        $comptesTresorerie = CompteTresorerie::with('category')->orderBy('name')->get();
        $axes = AxeAnalytique::where('company_id', $activeCompanyId)->with('sections')->get();

        $initials = $user->initiales;
        $prefix = "CPT-" . $initials . "_";
        $nextSequence = EcritureComptable::where('company_id', $activeCompanyId)
            ->where('n_saisie_user', 'like', $prefix . '%')
            ->distinct('n_saisie_user')
            ->count('n_saisie_user') + 1;

        $nextSaisieNumber = $prefix . str_pad($nextSequence, 12, '0', STR_PAD_LEFT);

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

            // Récupérer toutes les lignes d'écriture pour ce n_saisie
            $ecritures = EcritureComptable::with(['planComptable', 'planTiers', 'compteTresorerie', 'posteTresorerie.category', 'codeJournal'])
                ->where('company_id', $activeCompanyId)
                ->where('n_saisie', $n_saisie)
                ->orderBy('id')
                ->get();

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

            $deleted = EcritureComptable::where('company_id', $activeCompanyId)
                ->where('n_saisie', $n_saisie)
                ->delete();

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
                    \Illuminate\Validation\Rule::exists('code_journaux', 'id')->where('company_id', $activeCompanyId)
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
            $hasApprovalPower = $user->isAdmin() || $user->hasPermission('admin.approvals');
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

            // Sauvegarder les ventilations si présentes
            if ($request->has('ventilations') && is_array($request->ventilations)) {
                foreach ($request->ventilations as $v) {
                    $ecriture->ventilations()->create([
                        'section_id' => $v['section_id'],
                        'montant' => $v['montant'],
                        'pourcentage' => $v['pourcentage'],
                    ]);
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
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function storeMultiple(Request $request)
    {
        \Illuminate\Support\Facades\Log::info('EcritureComptableController@storeMultiple called', [
            'request' => $request->all(),
            'user_id' => auth()->id()
        ]);
        try {
            $user = Auth::user();
            if (!$user) {
                return response()->json(['success' => false, 'error' => 'Utilisateur non authentifié'], 401);
            }

            $activeCompanyId = session('current_company_id', $user->company_id);

            $ecrituresRaw = $request->input('ecritures');
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

            if ($exerciceIdFromInput) {
                $exerciceActif = ExerciceComptable::where('company_id', $activeCompanyId)->find($exerciceIdFromInput);
            } else {
                $exerciceActif = ExerciceComptable::where('company_id', $activeCompanyId)->where('is_active', 1)->first() 
                                ?? ExerciceComptable::where('company_id', $activeCompanyId)->where('cloturer', 0)->orderBy('date_debut', 'desc')->first();
            }

            if (!$exerciceActif || $exerciceActif->cloturer) {
                return response()->json(['success' => false, 'error' => 'Impossible d\'enregistrer : L\'exercice est clôturé ou inexistant.'], 422);
            }

            // --- GROUP BY N_SAISIE ---
            $groups = [];
            foreach ($ecrituresRaw as $e) {
                $ns = $e['n_saisie'] ?? $e['numero_saisie'] ?? 'DEFAULT';
                $groups[$ns][] = $e;
            }

            // --- VALIDATE EACH GROUP BALANCE ---
            foreach ($groups as $ns => $groupEcritures) {
                $totalDebit = 0;
                $totalCredit = 0;
                foreach ($groupEcritures as $e) {
                    $totalDebit += floatval($e['debit'] ?? 0);
                    $totalCredit += floatval($e['credit'] ?? 0);
                }
                $diff = abs($totalDebit - $totalCredit);
                if ($diff > 0.1) {
                    return response()->json([
                        'success' => false, 
                        'error' => "Opération $ns déséquilibrée (D: $totalDebit != C: $totalCredit). Écart: $diff"
                    ], 422);
                }
            }

            $hasApprovalPower = $user->isAdmin() || $user->hasPermission('admin.approvals');
            $status = $hasApprovalPower ? 'approved' : 'pending';

            $pieceFilename = null;
            $file = $request->file('piece_justificatif');
            if ($file) {
                $pieceFilename = time() . '_' . preg_replace('/[^A-Za-z0-9._-]/', '_', $file->getClientOriginalName());
                $file->move(public_path('justificatifs'), $pieceFilename);
            }

            DB::beginTransaction();
            $globalNSaisieMap = []; // Cache for global sequence numbers (status=approved)

            foreach ($groups as $userNS => $groupEcritures) {
                $currentGlobalNS = null;
                if ($status === 'approved') {
                    $firstData = reset($groupEcritures);
                    $exerciceId = $firstData['exercices_comptables_id'] ?? $firstData['exercice_id'] ?? $firstData['id_exercice'] ?? ($exerciceActif ? $exerciceActif->id : null);
                    $currentGlobalNS = $this->generateGlobalSaisieNumber($activeCompanyId, $exerciceId);
                }

                $firstInGroup = null;

                foreach ($groupEcritures as $data) {
                    $planComptableId = !empty($data['plan_comptable_id']) ? $data['plan_comptable_id'] : ($data['compte_general'] ?? null);
                    if (!$planComptableId) throw new \Exception("Un compte général est requis pour l'opération $userNS.");

                    $codeJournalId = !empty($data['code_journal_id']) ? $data['code_journal_id'] : ($data['journal_id'] ?? null);
                    if (!$codeJournalId) throw new \Exception("Un code journal est requis pour l'opération $userNS.");

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
                    $ecriture->n_saisie = ($status === 'approved') ? $currentGlobalNS : $userNS;
                    $ecriture->n_saisie_user = $userNS;
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

                    if (!$firstInGroup) $firstInGroup = $ecriture;

                    if ($ecriture->plan_analytique && !empty($data['ventilations'])) {
                        foreach ($data['ventilations'] as $v) {
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

            return response()->json(['success' => true, 'message' => $status === 'approved' ? 'Opérations validées avec succès.' : 'Opérations Duo enregistrées (en attente).']);
        }
        catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
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
        if (!$user->isAdmin() && !$user->hasPermission('admin.approvals')) {
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

        // Tri et pagination par Journal, Référence et Date de création
        $paginatedSaisies = (clone $baseQuery)
            ->select('ecriture_comptables.n_saisie', DB::raw('MAX(ecriture_comptables.created_at) as latest_created_at'), DB::raw('MAX(code_journals.code_journal) as journal_code'), DB::raw('MAX(ecriture_comptables.reference_piece) as ref_piece'))
            ->leftJoin('code_journals', 'ecriture_comptables.code_journal_id', '=', 'code_journals.id')
            ->groupBy('ecriture_comptables.n_saisie')
            ->orderBy('journal_code', 'asc')
            ->orderBy('ref_piece', 'asc')
            ->orderBy('latest_created_at', 'desc')
            ->paginate(10);

        $saisieList = $paginatedSaisies->pluck('n_saisie')->toArray();
        
        $ecritures = EcritureComptable::with(['planComptable', 'planTiers', 'compteTresorerie', 'posteTresorerie.category', 'codeJournal', 'ventilations.section.axe'])
            ->where('ecriture_comptables.company_id', $activeCompanyId)
            ->whereIn('ecriture_comptables.n_saisie', $saisieList)
            ->leftJoin('code_journals', 'ecriture_comptables.code_journal_id', '=', 'code_journals.id')
            ->select('ecriture_comptables.*')
            ->orderBy('code_journals.code_journal', 'asc')
            ->orderBy('ecriture_comptables.reference_piece', 'asc')
            ->orderBy('ecriture_comptables.created_at', 'desc')
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

            $deleted = EcritureComptable::where('company_id', $activeCompanyId)
                ->where('exercices_comptables_id', $exerciceId)
                ->delete();

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
            $initials = $user->initiales;
            $prefix = "CPT-" . $initials . "_";

            Log::debug("Génération N° Saisie (API) - Prefix: $prefix, Company: $activeCompanyId");

            // Même logique robuste: count distinct + 1
            $nextNumber = EcritureComptable::where('company_id', $activeCompanyId)
                ->where('n_saisie_user', 'like', $prefix . '%')
                ->distinct('n_saisie_user')
                ->count('n_saisie_user') + 1;

            do {
                $formattedNumber = $prefix . str_pad($nextNumber, 12, '0', STR_PAD_LEFT);
                $existe = EcritureComptable::where('company_id', $activeCompanyId)
                    ->where('n_saisie_user', $formattedNumber)
                    ->exists();
                if ($existe)
                    $nextNumber++;
            } while ($existe);

            Log::debug("API - Saisie calculée: $formattedNumber");

            return response()->json([
                'success' => true,
                'numero' => $formattedNumber,
                'prefix' => $prefix
            ]);
        }
        catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Génère un numéro de saisie global séquentiel au format ECR_000000000001
     */
    private function generateGlobalSaisieNumber($companyId, $exerciceId = null)
    {
        // On cherche le max de n_saisie qui commence par ECR_
        $query = EcritureComptable::where('company_id', $companyId)
            ->where('n_saisie', 'like', 'ECR_%');

        if ($exerciceId) {
            $query->where('exercices_comptables_id', $exerciceId);
        }

        $lastEntry = $query->orderBy('n_saisie', 'desc')
            ->first();

        $nextNumber = 1;
        if ($lastEntry) {
            $lastNSaisie = $lastEntry->n_saisie;
            $numberPart = str_replace('ECR_', '', $lastNSaisie);
            $nextNumber = (int)$numberPart + 1;
        }

        return 'ECR_' . str_pad($nextNumber, 12, '0', STR_PAD_LEFT);
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

        if (!$user->isAdmin() && !$user->hasPermission('admin.approvals')) {
            $query->where('user_id', $user->id);
        }

        $ecritures = $query->orderBy('created_at', 'desc')->get();

        return view('accounting.rejected', compact('ecritures', 'exerciceActif'));
    }
    public function updateFromApproval(Request $request)
    {
        $user = Auth::user();
        if (!$user->isAdmin() && !$user->hasPermission('admin.approvals')) {
            return response()->json(['success' => false, 'message' => 'Non autorisé'], 403);
        }

        try {
            DB::beginTransaction();

            $approvalId = $request->input('approval_id');
            $approval = Approval::findOrFail($approvalId);
            $oldNSaisie = $approval->data['n_saisie'];
            $activeCompanyId = session('current_company_id', $user->company_id);

            // 1. Supprimer les anciennes lignes (Pending)
            EcritureComptable::where('company_id', $activeCompanyId)
                ->where('n_saisie', $oldNSaisie)
                ->delete();

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
}