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
use App\Models\tresoreries\Tresoreries;
use Illuminate\Support\Facades\DB;

class EcritureComptableController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $data = $request->all();

        $exercicesCount = ExerciceComptable::count();

        if ($exercicesCount == 0) {
            return redirect()->route('exercice_comptable')->with('info', 'Veuillez créer un exercice comptable avant de pouvoir gérer les écritures.');
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

        $plansComptables = PlanComptable::select('id', 'numero_de_compte', 'intitule')
            ->orderByRaw("LEFT(numero_de_compte, 1) ASC")
            ->orderBy('numero_de_compte', 'asc')
            ->get();

        $plansTiers = PlanTiers::select('id', 'numero_de_tiers', 'intitule', 'compte_general')
            ->with('compte')
            ->orderByRaw("LEFT(numero_de_tiers, 1) ASC")
            ->orderBy('numero_de_tiers', 'asc')
            ->get();

        $comptesTresorerie = CompteTresorerie::select('id', 'name', 'type')
            ->orderBy('name', 'asc')
            ->get();

        $query = EcritureComptable::where('company_id', $user->company_id)
            ->orderBy('created_at', 'desc')
            ->orderBy('n_saisie');

        if (!empty($data['id_journal'])) {
            $query->where('journaux_saisis_id', $data['id_journal']);
        }

        $dateDebut = Carbon::create($data['annee'], $data['mois'], 1)->startOfMonth()->toDateString();
        $dateFin = Carbon::create($data['annee'], $data['mois'], 1)->endOfMonth()->toDateString();

        $queryForSum = clone $query;
        $ecritures = $query->with(['planComptable', 'planTiers','compteTresorerie'])->get();
        $totalDebit = $queryForSum->sum('debit');
        $totalCredit = $queryForSum->sum('credit');

        if (!session()->has('current_saisie_number')) {
            $lastSaisie = EcritureComptable::max('n_saisie');
            $nextSaisieNumber = $lastSaisie ? str_pad((int) $lastSaisie + 1, 12, '0', STR_PAD_LEFT) : '000000000001';
            session(['current_saisie_number' => $nextSaisieNumber]);
        } else {
            $nextSaisieNumber = session('current_saisie_number');
        }

        $exercice = null;
        if (!empty($data['id_exercice'])) {
            $exercice = ExerciceComptable::find($data['id_exercice']);
        }
        
        if (!$exercice) {
            $exercice = ExerciceComptable::where('company_id', $user->company_id)
                ->orderBy('date_debut', 'desc')
                ->first();
                
            if ($exercice) {
                $data['id_exercice'] = $exercice->id;
                $data['annee'] = date('Y', strtotime($exercice->date_debut));
            }
        }

        $id_code = $request->id_code; 
        $id_exercice = $request->id_exercice;

        return view('accounting_entry_real', compact(
            'id_code', 'id_exercice', 'plansComptables', 'plansTiers', 'data', 
            'ecritures', 'totalDebit', 'totalCredit', 'nextSaisieNumber', 
            'exercice', 'dateDebut', 'dateFin', 'comptesTresorerie'
        ));
    }

    public function showSaisieModal()
    {
        $user = Auth::user();
        $companyId = session('current_company_id', $user->company_id);
        
        $exercices = ExerciceComptable::where('company_id', $companyId)
            ->orderBy('date_debut', 'desc')
            ->get()
            ->unique('intitule')
            ->values();

        $exerciceActif = $exercices->firstWhere('cloturer', 0) ?? $exercices->first();

        $code_journaux = CodeJournal::where('company_id', $companyId)
            ->orderBy('code_journal', 'asc')
            ->get()
            ->unique('code_journal');

        return view('components.modal_saisie_direct', [
            'exercices' => $exercices,
            'code_journaux' => $code_journaux,
            'exerciceActif' => $exerciceActif,
            'companyId' => $companyId
        ]);
    }

    public function storeMultiple(Request $request)
    {
        if (!session()->has('current_saisie_number')) {
            $lastSaisie = EcritureComptable::max('n_saisie');
            $nextSaisieNumber = $lastSaisie ? str_pad((int) $lastSaisie + 1, 12, '0', STR_PAD_LEFT) : '000000000001';
            session(['current_saisie_number' => $nextSaisieNumber]);
        } else {
            $nextSaisieNumber = session('current_saisie_number');
        }

        try {
            foreach ($request->ecritures as $index => $ecriture) {
                $pieceJustificatifName = null;

                if ($request->hasFile("ecritures.$index.piece_justificatif")) {
                    $file = $request->file("ecritures.$index.piece_justificatif");
                    $pieceJustificatifName = time() . '_' . $file->getClientOriginalName();
                    $file->move(public_path('justificatifs'), $pieceJustificatifName);
                }

                $debit = (float)($ecriture['debit'] ?? 0);
                $credit = (float)($ecriture['credit'] ?? 0);
                $compteTresorerieId = $ecriture['tresorerieFields'] ?? null;
                $typeFlux = $ecriture['typeFlux'] ?? null;

                if (!is_null($compteTresorerieId) && is_null($typeFlux)) {
                    $typeFlux = ($debit > 0) ? 'encaissement' : (($credit > 0) ? 'decaissement' : null);
                }

                $compteGeneralId = $ecriture['compte_general'] ?? null;
                if (is_null($typeFlux) && $compteGeneralId) {
                    $planComptable = PlanComptable::find($compteGeneralId);
                    if ($planComptable) {
                        $classeFluxDeterminee = $this->determineFluxClasse($planComptable->numero_de_compte);
                        if (is_null($compteTresorerieId)) {
                            $typeFlux = $classeFluxDeterminee;
                        }
                    }
                }

                EcritureComptable::create([
                    'date' => $ecriture['date'],
                    'n_saisie' => $nextSaisieNumber,
                    'description_operation' => ucfirst(strtolower($ecriture['description'])),
                    'reference_piece' => strtoupper($ecriture['reference']),
                    'plan_comptable_id' => $ecriture['compte_general'],
                    'plan_tiers_id' => $ecriture['compte_tiers'] ?? null,
                    'compte_tresorerie_id' => $compteTresorerieId,
                    'type_flux' => $typeFlux,
                    'debit' => $debit,
                    'credit' => $credit,
                    'plan_analytique' => $ecriture['analytique'] === 'Oui' ? 1 : 0,
                    'code_journal_id' => $ecriture['journal'] ?? null,
                    'exercices_comptables_id' => $ecriture['exercices_comptables_id'] ?? null,
                    'journaux_saisis_id' => $ecriture['journaux_saisis_id'] ?? null,
                    'piece_justificatif' => $pieceJustificatifName,
                ]);
            }

            return response()->json(['message' => 'Toutes les écritures ont été enregistrées avec succès.']);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Une erreur est survenue lors de l\'enregistrement.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    public function getComptesParFlux(Request $request)
    {
        $user = Auth::user();
        $typeFlux = $request->query('type');
        $query = PlanComptable::select('id', 'numero_de_compte', 'intitule');

        if ($typeFlux && stripos($typeFlux, 'Operationnelles') !== false) {
            $query->where(function($q) {
                $q->where('numero_de_compte', 'like', '4%')
                  ->orWhere('numero_de_compte', 'like', '5%')
                  ->orWhere('numero_de_compte', 'like', '6%')
                  ->orWhere('numero_de_compte', 'like', '7%');
            });
        } elseif ($typeFlux && stripos($typeFlux, 'Investissement') !== false) {
            $query->where(function($q) {
                $q->where('numero_de_compte', 'like', '2%')
                  ->orWhere('numero_de_compte', 'like', '4%')
                  ->orWhere('numero_de_compte', 'like', '5%');
            });
        } elseif ($typeFlux && stripos($typeFlux, 'Financement') !== false) {
            $query->where(function($q) {
                $q->where('numero_de_compte', 'like', '1%')
                  ->orWhere('numero_de_compte', 'like', '4%')
                  ->orWhere('numero_de_compte', 'like', '5%');
            });
        } else {
             $query->limit(500);
        }

        return response()->json($query->orderBy('numero_de_compte', 'asc')->get());
    }

    public function list(Request $request)
    {
        $user = Auth::user();
        $data = $request->all();

        $exercices = ExerciceComptable::get()->unique('intitule');
        $code_journaux = CodeJournal::get()->unique('code_journal');

        $query = EcritureComptable::query();

        if (!empty($data['exercice_id'])) {
            $query->where('exercice_id', $data['exercice_id']);
        }
        if (!empty($data['mois'])) {
            $query->whereMonth('date', $data['mois']);
        }
        if (!empty($data['journal_id'])) {
            $query->where('code_journal_id', $data['journal_id']);
        }

        $journal = !empty($data['journal_id']) ? CodeJournal::find($data['journal_id']) : CodeJournal::orderBy('code_journal')->first();
        $exercice = !empty($data['exercice_id']) ? ExerciceComptable::find($data['exercice_id']) : ExerciceComptable::orderBy('date_debut', 'desc')->first();

        $ecritures = $query->orderBy('date', 'desc')->orderBy('n_saisie', 'desc')->get();
        $totalDebit = $ecritures->sum('debit');
        $totalCredit = $ecritures->sum('credit');

        $plansComptables = PlanComptable::select('id', 'numero_de_compte', 'intitule')
            ->orderByRaw("LEFT(numero_de_compte, 1) ASC")
            ->orderBy('numero_de_compte', 'asc')
            ->get();

        $tiers = PlanTiers::select('id', 'numero_de_tiers', 'intitule')
            ->orderByRaw("LEFT(numero_de_tiers, 1) ASC")
            ->orderBy('numero_de_tiers', 'asc')
            ->get();

        $postesTresorerie = CompteTresorerie::orderBy('name', 'asc')->get();
        $entries = $ecritures;

        $lastSaisie = EcritureComptable::max('n_saisie');
        $nextSaisieNumber = $lastSaisie ? str_pad((int) $lastSaisie + 1, 12, '0', STR_PAD_LEFT) : '000000000001';

        return view('accounting_entry_list', compact(
            'exercices', 'code_journaux', 'ecritures', 'entries', 'journal', 
            'exercice', 'totalDebit', 'totalCredit', 'plansComptables', 
            'tiers', 'postesTresorerie', 'nextSaisieNumber', 'data'
        ));
    }

    public function getNextSaisieNumber(Request $request)
    {
        try {
            if (!session()->has('current_saisie_number')) {
                $lastSaisie = EcritureComptable::max('n_saisie');
                $nextSaisieNumber = $lastSaisie ? str_pad((int) $lastSaisie + 1, 12, '0', STR_PAD_LEFT) : '000000000001';
                session(['current_saisie_number' => $nextSaisieNumber]);
            } else {
                $nextSaisieNumber = session('current_saisie_number');
            }

            return response()->json([
                'success' => true,
                'nextSaisieNumber' => $nextSaisieNumber
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération du numéro de saisie'
            ], 500);
        }
    }

    public function store(Request $request)
    {
        $data = $request->all();
        
        $request->validate([
            'ecritures' => 'required|array|min:1',
            'ecritures.*.date' => 'required|date',
            'ecritures.*.n_saisie' => 'required|string|max:12',
            'ecritures.*.code_journal_id' => 'required|exists:code_journals,id',
            'ecritures.*.plan_comptable_id' => 'required|exists:plan_comptables,id',
            'ecritures.*.debit' => 'required|numeric|min:0',
            'ecritures.*.credit' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();
        
        try {
            $savedEcritures = [];
            $user = auth()->user();
            
            foreach ($request->ecritures as $ecriture) {
                $savedEcriture = EcritureComptable::create([
                    'date' => $ecriture['date'],
                    'n_saisie' => $ecriture['n_saisie'],
                    'description_operation' => $ecriture['description_operation'] ?? null,
                    'reference_piece' => $ecriture['reference_piece'] ?? null,
                    'plan_comptable_id' => $ecriture['plan_comptable_id'],
                    'plan_tiers_id' => $ecriture['plan_tiers_id'] ?? null,
                    'code_journal_id' => $ecriture['code_journal_id'],
                    'debit' => $ecriture['debit'],
                    'credit' => $ecriture['credit'],
                    'piece_justificatif' => $ecriture['piece_justificatif'] ?? null,
                    'plan_analytique' => $ecriture['plan_analytique'] ?? false,
                    'user_id' => $user->id,
                    'company_id' => $user->company_id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                
                $savedEcritures[] = $savedEcriture;
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Écritures enregistrées avec succès',
                'data' => $savedEcritures
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Une erreur est survenue lors de l\'enregistrement',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}