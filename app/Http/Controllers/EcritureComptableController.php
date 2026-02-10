<?php

namespace App\Http\Controllers;

use App\Models\PlanTiers;
use App\Models\User;
use Illuminate\Http\Request;
use App\Models\EcritureComptable;
use App\Models\ExerciceComptable;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use App\Models\PlanComptable;
use Carbon\Carbon;
use App\Models\CodeJournal;
use App\Models\CompteTresorerie;
use Illuminate\Support\Facades\DB;
use App\Models\Approval;
use App\Models\TreasuryCategory;
use App\Traits\HandlesTreasuryPosts;

class EcritureComptableController extends Controller
{
    use HandlesTreasuryPosts;
    public function index(Request $request)
    {
        $user = Auth::user();
        $data = $request->all();

        $exercicesCount = ExerciceComptable::count();
        if ($exercicesCount == 0) {
            return redirect()->route('exercice_comptable')->with('info', 'Veuillez créer un exercice comptable.');
        }

        $data['annee'] = $data['annee'] ?? date('Y');
        $data['mois'] = $data['mois'] ?? date('n');
        
        if (empty($data['id_exercice'])) {
            $exerciceActif = ExerciceComptable::where('company_id', $user->company_id)
                ->where('is_active', 1)
                ->first();
                
            if (!$exerciceActif) {
                $exerciceActif = ExerciceComptable::where('company_id', $user->company_id)
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
        $exercicesVisibles = ExerciceComptable::where('company_id', $user->company_id);
        if (ExerciceComptable::where('company_id', $user->company_id)->where('is_active', 1)->exists()) {
            $exercicesVisibles->where('is_active', 1);
        }
        $exercicesVisibles = $exercicesVisibles->get();

        $plansComptables = PlanComptable::select('id', 'numero_de_compte', 'intitule', 'numero_original')->orderBy('numero_de_compte')->get();
        $plansTiers = PlanTiers::select('id', 'numero_de_tiers', 'intitule', 'compte_general', 'numero_original')->with('compte')->get();
        $comptesTresorerie = CompteTresorerie::with('category')->orderBy('name')->get();
            $categories = TreasuryCategory::where('company_id', $user->company_id)
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
        $lastUserSaisie = EcritureComptable::where('company_id', $user->company_id)
            ->where('n_saisie_user', 'like', $prefix . '%')
            ->max('id');
        
        $nextSequence = ($lastUserSaisie ? $lastUserSaisie + 1 : 1);
        // Note: Pour être plus précis on devrait compter les n_saisie_user distincts mais count(distinct) ou max sur la sequence suffixe est mieux.
        // On va utiliser une approche simple pour l'init :
        $nextSaisieNumber = $prefix . str_pad($nextSequence, 12, '0', STR_PAD_LEFT);

        $activeCompanyId = session('current_company_id', $user->company_id);

        $query = EcritureComptable::where('company_id', $user->company_id);
        
        // Filtrer par exercice si présent
        if (!empty($data['id_exercice'])) {
            $query->where('exercices_comptables_id', $data['id_exercice']);
        }

        $ecritures = $query->with(['planComptable', 'planTiers','compteTresorerie', 'posteTresorerie'])
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
            } catch (\Exception $e) {
                // Ignorer ou logger l'erreur
            }
        }

        return view('accounting_entry_real', compact(
            'plansComptables', 'plansTiers', 'data', 'ecritures', 
            'nextSaisieNumber', 'comptesTresorerie', 'exercicesVisibles',
            'approvalEditingData', 'categories'
        ));
    }

    public function scanIndex(Request $request)
    {
        $user = Auth::user();
        $data = $request->all();

        $plansComptables = PlanComptable::select('id', 'numero_de_compte', 'intitule')->orderBy('numero_de_compte')->get();
        $plansTiers = PlanTiers::select('id', 'numero_de_tiers', 'intitule', 'compte_general')->with('compte')->get();
        
        $initials = $user->initiales;
        $prefix = "CPT-" . $initials . "_";
        $lastUserSaisie = EcritureComptable::where('company_id', $user->company_id)
            ->where('n_saisie_user', 'like', $prefix . '%')
            ->max('id');
        $nextSequence = ($lastUserSaisie ? $lastUserSaisie + 1 : 1);
        $nextSaisieNumber = $prefix . str_pad($nextSequence, 12, '0', STR_PAD_LEFT);

        $comptesTresorerie = CompteTresorerie::with('category')->orderBy('name')->get();

        return view('accounting.scan', compact('plansComptables', 'plansTiers', 'data', 'nextSaisieNumber', 'comptesTresorerie'));
    }

    private function determineFluxClasse($numeroCompte) {
        $classe = substr($numeroCompte, 0, 1);
        if (in_array($classe, ['6', '7'])) return 'Operationnelles';
        if ($classe == '2') return 'Investissement';
        if ($classe == '1') return 'Financement';
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
        } catch (\Exception $e) {
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
        } catch (\Exception $e) {
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
            $ecritures = EcritureComptable::with(['planComptable', 'planTiers', 'compteTresorerie', 'posteTresorerie', 'codeJournal'])
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
        } catch (\Exception $e) {
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
        } catch (\Exception $e) {
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
                $typeFlux = 'encaissement';   // entrée d’argent
            } elseif ($credit > 0) {
                $typeFlux = 'decaissement';   // sortie d’argent
            } else {
                $typeFlux = null;
            }

            // Déterminer le statut initial
            $hasApprovalPower = $user->hasPermission('admin.approvals');
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
                $nSaisie = $this->generateGlobalSaisieNumber($activeCompanyId);
            } else {
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
            
            // Créer une demande d'approbation SI ce n'est pas déjà approuvé
            if ($status === 'pending') {
                Approval::create([
                    'approvable_type' => EcritureComptable::class,
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

        } catch (\Exception $e) {
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
                return response()->json(['success' => false, 'error' => 'Impossible d\'enregistrer : L\'exercice est clôturé ou inexistant.'], 422);
            }

            $ecritures = $request->input('ecritures');
            if (is_string($ecritures)) {
                $ecritures = json_decode($ecritures, true);
            }
            
            if (empty($ecritures) || !is_array($ecritures)) {
                return response()->json(['success' => false, 'error' => 'Aucune écriture à enregistrer.'], 400);
            }

            // Gestion du filtrage auto
            $hasApprovalPower = $user->hasPermission('admin.approvals');
            $status = $hasApprovalPower ? 'approved' : 'pending';

            // Gestion du fichier justificatif
            $pieceFilename = null;
            $file = $request->file('piece_justificatif') ?? $request->file('ecritures.0.piece_justificatif');
            
            if ($file) {
                $pieceFilename = time() . '_' . preg_replace('/[^A-Za-z0-9._-]/', '_', $file->getClientOriginalName());
                $file->move(public_path('justificatifs'), $pieceFilename);
            }

            DB::beginTransaction();
            $firstEcriture = null;
            $globalNSaisie = null;
            $userNSaisie = null;

            // Générer le numéro approprié selon le statut
            if ($status === 'approved') {
                // Pour les écritures approuvées: générer le numéro global ECR_
                $globalNSaisie = $this->generateGlobalSaisieNumber($activeCompanyId);
            } else {
                // Pour les écritures en attente: utiliser le numéro utilisateur CPT-{initiales}_
                $userNSaisie = $ecritures[0]['n_saisie'] ?? $ecritures[0]['numero_saisie'] ?? null;
                if (!$userNSaisie) {
                    throw new \Exception("Le numéro de saisie utilisateur est requis pour les écritures en attente.");
                }
            }

            foreach ($ecritures as $data) {
                // ... (existing logic)
                $planComptableId = !empty($data['plan_comptable_id']) ? $data['plan_comptable_id'] : ($data['compte_general'] ?? null);
                if (!$planComptableId) throw new \Exception("Un compte général est requis.");

                $codeJournalId = !empty($data['code_journal_id']) ? $data['code_journal_id'] : ($data['journal_id'] ?? null);
                if (!$codeJournalId) throw new \Exception("Un code journal est requis.");

                $dateString = !empty($data['date']) ? $data['date'] : now()->format('Y-m-d');
                $date = \Carbon\Carbon::parse($dateString);
                $exerciceId = $data['exercices_comptables_id'] ?? $data['exercice_id'] ?? $exerciceActif->id;

                $journalSaisi = \App\Models\JournalSaisi::firstOrCreate([
                    'annee' => $date->year,
                    'mois' => $date->month,
                    'exercices_comptables_id' => $exerciceId,
                    'code_journals_id' => $codeJournalId,
                    'company_id' => $activeCompanyId,
                ], ['user_id' => $user->id]);

                $ecriture = new EcritureComptable();
                $ecriture->date = $dateString;
                
                // Attribution du numéro selon le statut
                if ($status === 'approved') {
                    $ecriture->n_saisie = $globalNSaisie; // Numéro GLOBAL (ECR_)
                    $ecriture->n_saisie_user = $data['n_saisie'] ?? $data['numero_saisie'] ?? null; // Numéro d'origine (User)
                } else {
                    // Pour les écritures en attente: le numéro utilisateur dans les deux champs
                    $ecriture->n_saisie = $userNSaisie; // Numéro utilisateur (CPT-XXX_)
                    $ecriture->n_saisie_user = $userNSaisie; // Même numéro pour traçabilité
                }
                
                $ecriture->description_operation = $data['description_operation'] ?? $data['description'] ?? '';
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

                if (!$firstEcriture) $firstEcriture = $ecriture;
            }

            if ($status === 'pending' && $firstEcriture) {
                Approval::create([
                    'approvable_type' => EcritureComptable::class,
                    'approvable_id' => $firstEcriture->id,
                    'type' => 'accounting_entry',
                    'status' => 'pending',
                    'requested_by' => $user->id,
                    'data' => ['n_saisie' => $firstEcriture->n_saisie] // Stocker le numéro utilisateur
                ]);
            }

            DB::commit();

            // ... cleanup brouillon
            $batchId = $request->input('batch_id');
            if ($batchId) {
                \App\Models\Brouillon::where('batch_id', $batchId)
                    ->where('company_id', $activeCompanyId)
                    ->delete();
            }

            return response()->json(['success' => true, 'message' => $status === 'approved' ? 'Écritures validées avec succès.' : 'Écritures enregistrées (en attente d\'approbation).']);
        } catch (\Exception $e) {
            DB::rollBack();
            \Illuminate\Support\Facades\Log::error('Error in storeMultiple: ' . $e->getMessage(), [
                'exception' => $e,
                'trace' => $e->getTraceAsString()
            ]);
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
            
        $baseQuery = EcritureComptable::where('company_id', $activeCompanyId);
        
        // FILTRE EXERCICE STRICT
        if ($exerciceActif) {
            $baseQuery->where('exercices_comptables_id', $exerciceActif->id);
        } else {
            // Si vraiment aucun exercice, on ne retourne rien (sécurité)
            $baseQuery->whereRaw('1 = 0');
        }
        
        // Logique de filtrage par rôle/permission
        if (!$user->hasPermission('admin.approvals')) {
            // Un collaborateur ne voit que ses propres écritures (tous statuts)
            $baseQuery->where('user_id', $user->id);
        } else {
            // Un admin voit tout ce qui est validé + ses propres écritures en attente (s'il y en a)
            $baseQuery->where(function($q) use ($user) {
                $q->where('statut', 'approved')
                  ->orWhere('user_id', $user->id);
            });
        }

        if (!empty($data['numero_saisie'])) $baseQuery->where('n_saisie', 'like', '%' . $data['numero_saisie'] . '%');
        if (!empty($data['code_journal'])) $baseQuery->whereHas('codeJournal', function($q) use ($data) {
            $q->where('code_journal', 'like', '%' . $data['code_journal'] . '%');
        });
        if (!empty($data['recherche'])) {
            $search = $data['recherche'];
            $baseQuery->where(function($q) use ($search) {
                $q->where('description_operation', 'like', '%' . $search . '%')
                  ->orWhereHas('planComptable', function($sq) use ($search) {
                      $sq->where('numero_de_compte', 'like', '%' . $search . '%');
                  })
                  ->orWhereHas('planTiers', function($sq) use ($search) {
                      $sq->where('numero_de_tiers', 'like', '%' . $search . '%');
                  });
            });
        }
        if (!empty($data['mois'])) $baseQuery->whereMonth('date', $data['mois']);
        if (!empty($data['statut'])) $baseQuery->where('statut', $data['statut']);
        
        if (!empty($data['etat_poste']) && $data['etat_poste'] !== '') {
            $baseQuery->whereHas('planComptable', function($sq) {
                $sq->where('numero_de_compte', 'like', '5%');
            });

            if ($data['etat_poste'] === 'defini') {
                $baseQuery->whereNotNull('poste_tresorerie_id');
            } elseif ($data['etat_poste'] === 'non_defini') {
                $baseQuery->whereNull('poste_tresorerie_id');
            }
        }

        $paginatedSaisies = (clone $baseQuery)
            ->select('n_saisie', DB::raw('MAX(created_at) as latest_created_at'))
            ->groupBy('n_saisie')
            ->orderBy('latest_created_at', 'desc')
            ->paginate(10);
            
        $saisieList = $paginatedSaisies->pluck('n_saisie')->toArray();
        $ecritures = EcritureComptable::with(['planComptable', 'planTiers', 'compteTresorerie', 'posteTresorerie.category', 'codeJournal'])
            ->where('company_id', $activeCompanyId)
            ->whereIn('n_saisie', $saisieList)
            ->orderBy('created_at', 'desc')
            ->orderBy('id', 'asc')
            ->get();
            
        $code_journaux = CodeJournal::where('company_id', $activeCompanyId)->get();
        $treasury_categories = \App\Models\TreasuryCategory::where('company_id', $activeCompanyId)->get();

        return view('accounting_entry_list', [
            'ecritures' => $ecritures,
            'exerciceActif' => $exerciceActif,
            'code_journaux' => $code_journaux,
            'treasury_categories' => $treasury_categories,
            'pagination' => $paginatedSaisies,
            'totalEntries' => $paginatedSaisies->total(),
            'data' => $data,
            'exercices' => $exerciceActif ? collect([$exerciceActif]) : \App\Models\ExerciceComptable::where('company_id', $activeCompanyId)->get()
        ]);
    }

    public function getNextSaisieNumber(Request $request)
    {
        try {
            $user = Auth::user();
            if (!$user) return response()->json(['error' => 'Non authentifié'], 401);

            $activeCompanyId = session('current_company_id', $user->company_id);
            $initials = $user->initiales;
            $prefix = "CPT-" . $initials . "_";

            // Trouver le dernier numéro utilisateur pour cet utilisateur et cette entreprise
            $lastEntry = EcritureComptable::where('company_id', $activeCompanyId)
                ->where('n_saisie_user', 'like', $prefix . '%')
                ->orderBy('id', 'desc')
                ->first();

            $nextNumber = 1;
            if ($lastEntry && $lastEntry->n_saisie_user) {
                $parts = explode('_', $lastEntry->n_saisie_user);
                if (count($parts) >= 2) {
                    $lastSequence = (int) end($parts);
                    $nextNumber = $lastSequence + 1;
                }
            }

            $formattedNumber = $prefix . str_pad($nextNumber, 12, '0', STR_PAD_LEFT);

            return response()->json([
                'success' => true,
                'numero' => $formattedNumber,
                'prefix' => $prefix
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Génère un numéro de saisie global séquentiel au format ECR_000000000001
     */
    private function generateGlobalSaisieNumber($companyId)
    {
        // On cherche le max de n_saisie qui commence par ECR_
        $lastEntry = EcritureComptable::where('company_id', $companyId)
            ->where('n_saisie', 'like', 'ECR_%')
            ->orderBy('n_saisie', 'desc')
            ->first();

        $nextNumber = 1;
        if ($lastEntry) {
            $lastNSaisie = $lastEntry->n_saisie;
            $numberPart = str_replace('ECR_', '', $lastNSaisie);
            $nextNumber = (int)$numberPart + 1;
        }

        return 'ECR_' . str_pad($nextNumber, 12, '0', STR_PAD_LEFT);
    }

    public function getCompteParJournal(){
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
        if (!$user) return redirect()->route('login');

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

        if (!$user->hasPermission('admin.approvals')) {
            $query->where('user_id', $user->id);
        }

        $ecritures = $query->orderBy('created_at', 'desc')->get();

        return view('accounting.rejected', compact('ecritures', 'exerciceActif'));
    }
    public function updateFromApproval(Request $request)
    {
        $user = Auth::user();
        if (!$user->hasPermission('admin.approvals')) {
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

            // 2. Générer le NOUVEAU numéro global (Séquentiel)
            $newGlobalNSaisie = $this->generateGlobalSaisieNumber($activeCompanyId);

            // 3. Créer les nouvelles lignes
            $ecritures = $request->input('ecritures');
            if (is_string($ecritures)) $ecritures = json_decode($ecritures, true);

            // Fichier
            $pieceFilename = null;
            $file = $request->file('piece_justificatif') ?? $request->file('ecritures.0.piece_justificatif');
            if ($file) {
                $pieceFilename = time() . '_' . preg_replace('/[^A-Za-z0-9._-]/', '_', $file->getClientOriginalName());
                $file->move(public_path('justificatifs'), $pieceFilename);
            } else {
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

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}