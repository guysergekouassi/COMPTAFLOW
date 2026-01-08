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
                ->where('cloturer', 0)
                ->orderBy('date_debut', 'desc')
                ->first();
                
            if ($exerciceActif) {
                $data['id_exercice'] = $exerciceActif->id;
                $data['annee'] = date('Y', strtotime($exerciceActif->date_debut));
            }
        }

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
            'nextSaisieNumber', 'comptesTresorerie'
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
                
            $plansComptables = PlanComptable::select('id', 'numero_de_compte', 'intitule')->orderBy('numero_de_compte')->get();
            $plansTiers = PlanTiers::select('id', 'numero_de_tiers', 'intitule', 'compte_general')->with('compte')->get();
            $comptesTresorerie = CompteTresorerie::select('id', 'name', 'type')->orderBy('name')->get();
            $codeJournaux = CodeJournal::all();
            
            return view('accounting_entry_edit', compact('ecriture', 'plansComptables', 'plansTiers', 'comptesTresorerie', 'codeJournaux'));
        } catch (\Exception $e) {
            return redirect()->route('accounting_entry_list')->with('error', 'Erreur lors de l\'ouverture : ' . $e->getMessage());
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

            $data = $request->validate([
                'date' => 'required|date',
                'n_saisie' => 'required|string',
                'code_journal' => 'required',
                'description_operation' => 'required|string',
                'reference_piece' => 'nullable|string',
                'plan_comptable_id' => 'required|exists:plan_comptables,id',
                'plan_tiers_id' => 'nullable|exists:plan_tiers,id',
                'debit' => 'nullable|numeric|min:0',
                'credit' => 'nullable|numeric|min:0',
                'plan_analytique' => 'nullable|boolean',
                'piece_justificatif' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
                'compte_tresorerie_id' => 'nullable|exists:compte_tresoreries,id',
            ]);

            if ($request->hasFile('piece_justificatif')) {
                $file = $request->file('piece_justificatif');
                $filename = time() . '_' . $file->getClientOriginalName();
                $file->move(public_path('justificatifs'), $filename);
                $data['piece_justificatif'] = $filename;
            }

            $data['company_id'] = $activeCompanyId;
            $data['user_id'] = $user->id;
            $data['n_saisie'] = $request->numero_saisie;
            $data['code_journal_id'] = $request->code_journal;


            $exerciceActif = ExerciceComptable::where('company_id', $activeCompanyId)
    ->where('cloturer', 0)
    ->orderBy('date_debut', 'desc')
    ->first();

if (!$exerciceActif) {
    return response()->json([
        'success' => false,
        'message' => 'Aucun exercice comptable actif.'
    ], 422);
}

    $data['exercices_comptables_id'] = $exerciceActif->id;
            $ecriture = EcritureComptable::create($data);
            return response()->json(['success' => true, 'message' => 'Écriture ajoutée', 'id' => $ecriture->id]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function storeMultiple(Request $request)
    {
        try {
            $user = Auth::user();
            $activeCompanyId = session('current_company_id', $user->company_id);
            $ecritures = $request->input('ecritures');
            
            if (is_string($ecritures)) {
                $ecritures = json_decode($ecritures, true);
            }
            
            if (empty($ecritures) || !is_array($ecritures)) {
                return response()->json(['success' => false, 'message' => 'Aucune écriture à enregistrer.'], 400);
            }

            $pieceFilename = null;
            if ($request->hasFile('piece_justificatif')) {
                $file = $request->file('piece_justificatif');
                $pieceFilename = time() . '_' . $file->getClientOriginalName();
                $file->move(public_path('justificatifs'), $pieceFilename);
            }

            DB::beginTransaction();
            foreach ($ecritures as $data) {
                EcritureComptable::create([
                    'date' => $data['date'] ?? now()->format('Y-m-d'),
                    'n_saisie' => $data['n_saisie'] ?? $data['numero_saisie'] ?? null,
                    'description_operation' => $data['description_operation'] ?? $data['description'] ?? '',
                    'reference_piece' => $data['reference_piece'] ?? $data['reference'] ?? null,
                    'plan_comptable_id' => $data['plan_comptable_id'] ?? $data['compte_general'] ?? null,
                    'plan_tiers_id' => $data['plan_tiers_id'] ?? $data['compte_tiers'] ?? null,
                    'debit' => $data['debit'] ?? 0,
                    'credit' => $data['credit'] ?? 0,
                    'plan_analytique' => (isset($data['plan_analytique']) && $data['plan_analytique'] == 1) ? 1 : 0,
                    'code_journal_id' => $data['code_journal_id'] ?? $data['journal_id'] ?? null,
                    'company_id' => $activeCompanyId,
                    'user_id' => $user->id,
                    'piece_justificatif' => $pieceFilename,
                    'exercices_comptables_id' => $data['exercices_comptables_id'] ?? $data['exercice_id'] ?? null,
                    'journaux_saisis_id' => $data['journaux_saisis_id'] ?? $data['journal_saisi_id'] ?? null
                ]);
            }
            DB::commit();

            return response()->json(['success' => true, 'message' => 'Écritures enregistrées avec succès.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function getComptesParFlux(Request $request)
    {
        $flux = $request->input('flux');
        $query = PlanComptable::query();
        if ($flux === 'Operationnelles') {
            $query->where(function($q) {
                $q->where('numero_de_compte', 'like', '6%')->orWhere('numero_de_compte', 'like', '7%');
            });
        } elseif ($flux === 'Investissement') {
            $query->where('numero_de_compte', 'like', '2%');
        } elseif ($flux === 'Financement') {
            $query->where('numero_de_compte', 'like', '1%');
        }
        return response()->json($query->orderBy('numero_de_compte')->get());
    }

    public function getNextSaisieNumber(Request $request)
    {
        try {
            $user = Auth::user();
            $activeCompanyId = session('current_company_id', $user->company_id);
            $lastSaisie = EcritureComptable::where('company_id', $activeCompanyId)
                ->select(DB::raw('MAX(CAST(n_saisie AS UNSIGNED)) as max_saisie'))
                ->first();

            $nextNumber = ($lastSaisie && $lastSaisie->max_saisie) ? (int)$lastSaisie->max_saisie + 1 : 1;
            return response()->json(['success' => true, 'nextSaisieNumber' => str_pad($nextNumber, 12, '0', STR_PAD_LEFT)]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function list(Request $request)
    {
        $user = Auth::user();
        $data = $request->all();
        $activeCompanyId = session('current_company_id', $user->company_id);

        $exerciceActif = ExerciceComptable::where('company_id', $activeCompanyId)
            ->where('cloturer', 0)
            ->orderBy('date_debut', 'desc')
            ->first();
            
        $baseQuery = EcritureComptable::where('company_id', $activeCompanyId);
        
        if (!empty($data['numero_saisie'])) $baseQuery->where('n_saisie', 'like', '%' . $data['numero_saisie'] . '%');
        if (!empty($data['code_journal'])) $baseQuery->whereHas('codeJournal', function($q) use ($data) {
            $q->where('code_journal', 'like', '%' . $data['code_journal'] . '%');
        });
        if (!empty($data['mois'])) $baseQuery->whereMonth('date', $data['mois']);

        $paginatedSaisies = (clone $baseQuery)
            ->select('n_saisie', DB::raw('MAX(created_at) as latest_created_at'))
            ->groupBy('n_saisie')
            ->orderBy('latest_created_at', 'desc')
            ->paginate(5);
            
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
            'data' => $data
        ]);
    }
}