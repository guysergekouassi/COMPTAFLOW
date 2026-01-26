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
use Illuminate\Support\Facades\Log;
use App\Models\Approval;

class EcritureComptableController extends Controller
{
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

        $plansComptables = PlanComptable::select('id', 'numero_de_compte', 'intitule')->orderBy('numero_de_compte')->get();
        $plansTiers = PlanTiers::select('id', 'numero_de_tiers', 'intitule', 'compte_general')->with('compte')->get();
        $comptesTresorerie = CompteTresorerie::select('id', 'name', 'type')->orderBy('name')->get();

        $lastSaisie = EcritureComptable::max('id');
        $nextSaisieNumber = str_pad(($lastSaisie ? $lastSaisie + 1 : 1), 12, '0', STR_PAD_LEFT);
        $activeCompanyId = session('current_company_id', $user->company_id);

        $query = EcritureComptable::where('company_id', $user->company_id)->orderBy('created_at', 'desc');
        $ecritures = $query->with(['planComptable', 'planTiers','compteTresorerie'])->get();

        return view('accounting_entry_real', compact(
            'plansComptables', 'plansTiers', 'data', 'ecritures', 
            'nextSaisieNumber', 'comptesTresorerie', 'exercicesVisibles'
        ));
    }

    public function scanIndex(Request $request)
    {
        $user = Auth::user();
        $data = $request->all();

        $plansComptables = PlanComptable::select('id', 'numero_de_compte', 'intitule')->orderBy('numero_de_compte')->get();
        $plansTiers = PlanTiers::select('id', 'numero_de_tiers', 'intitule', 'compte_general')->with('compte')->get();
        
        $lastSaisie = EcritureComptable::max('id');
        $nextSaisieNumber = str_pad(($lastSaisie ? $lastSaisie + 1 : 1), 12, '0', STR_PAD_LEFT);

        return view('accounting.scan', compact('plansComptables', 'plansTiers', 'data', 'nextSaisieNumber'));
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
            $ecritures = EcritureComptable::with(['planComptable', 'planTiers', 'compteTresorerie', 'codeJournal'])
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
            $ecriture = EcritureComptable::with(['planComptable', 'planTiers', 'compteTresorerie', 'codeJournal'])
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
            $ecritures = EcritureComptable::with(['planComptable', 'planTiers', 'compteTresorerie', 'codeJournal'])
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

            if (!$exerciceActif) {
                return response()->json([
                    'success' => false,
                    'message' => 'Aucun exercice comptable actif trouvé.'
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
                'n_saisie' => 'required|string|max:12',
                'code_journal' => 'required|exists:code_journaux,id',
                'description_operation' => 'required|string|max:255',
                'reference_piece' => 'nullable|string|max:50',
                'plan_comptable_id' => 'required|exists:plan_comptables,id',
                'plan_tiers_id' => 'nullable|exists:plan_tiers,id',
                'debit' => 'nullable|numeric|min:0',
                'credit' => 'nullable|numeric|min:0',
                'plan_analytique' => 'nullable|boolean',
                'piece_justificatif' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
                'compte_tresorerie_id' => 'nullable|integer',
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

            // Préparer les données pour la création
            $ecritureData = [
                'company_id' => $activeCompanyId,
                'user_id' => $user->id,
                'n_saisie' => $request->n_saisie,
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
                'piece_justificatif' => $data['piece_justificatif'] ?? null,
                'statut' => $status,
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
                    'data' => ['n_saisie' => $ecriture->n_saisie]
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

            if (!$exerciceActif) {
                return response()->json(['success' => false, 'error' => 'Aucun exercice actif trouvé.'], 422);
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
                $ecriture->n_saisie = $data['n_saisie'] ?? $data['numero_saisie'] ?? null;
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
                    'data' => ['n_saisie' => $firstEcriture->n_saisie]
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
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    // ... autres méthodes

    public function list(Request $request)
    {
        $user = Auth::user();
        $data = $request->all();
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
            
        $baseQuery = EcritureComptable::where('company_id', $activeCompanyId);
        
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
        if (!empty($data['mois'])) $baseQuery->whereMonth('date', $data['mois']);
        if (!empty($data['statut'])) $baseQuery->where('statut', $data['statut']);

        $paginatedSaisies = (clone $baseQuery)
            ->select('n_saisie', DB::raw('MAX(created_at) as latest_created_at'))
            ->groupBy('n_saisie')
            ->orderBy('latest_created_at', 'desc')
            ->paginate(10);
            
        $saisieList = $paginatedSaisies->pluck('n_saisie')->toArray();
        $ecritures = EcritureComptable::with(['planComptable', 'planTiers', 'compteTresorerie', 'codeJournal'])
            ->where('company_id', $activeCompanyId)
            ->whereIn('n_saisie', $saisieList)
            ->orderBy('created_at', 'desc')
            ->orderBy('id', 'asc')
            ->get();
            
        $code_journaux = CodeJournal::where('company_id', $activeCompanyId)->get();

        return view('accounting_entry_list', [
            'ecritures' => $ecritures,
            'exerciceActif' => $exerciceActif,
            'code_journaux' => $code_journaux,
            'pagination' => $paginatedSaisies,
            'totalEntries' => $paginatedSaisies->total(),
            'data' => $data,
            'exercices' => $exerciceActif ? collect([$exerciceActif]) : \App\Models\ExerciceComptable::where('company_id', $activeCompanyId)->get()
        ]);
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

        $query = EcritureComptable::with(['planComptable', 'planTiers', 'codeJournal'])
            ->where('company_id', $activeCompanyId)
            ->where('statut', 'rejected');

        if (!$user->hasPermission('admin.approvals')) {
            $query->where('user_id', $user->id);
        }

        $ecritures = $query->orderBy('created_at', 'desc')->get();

        return view('accounting.rejected', compact('ecritures'));
    }
}