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

        $lastSaisie = EcritureComptable::max('n_saisie');
        $nextSaisieNumber = $lastSaisie ? str_pad((int) $lastSaisie + 1, 12, '0', STR_PAD_LEFT) : '000000000001';

        $query = EcritureComptable::where('company_id', $user->company_id)->orderBy('created_at', 'desc');
        $ecritures = $query->with(['planComptable', 'planTiers','compteTresorerie'])->get();

        return view('accounting_entry_real', compact(
            'plansComptables', 'plansTiers', 'data', 'ecritures', 
            'nextSaisieNumber', 'comptesTresorerie'
        ));
    }

    /**
     * Détermine le flux selon la classe du compte (Indispensable pour éviter l'erreur 500)
     */
    private function determineFluxClasse($numeroCompte) {
        $classe = substr($numeroCompte, 0, 1);
        if (in_array($classe, ['6', '7'])) return 'Operationnelles';
        if ($classe == '2') return 'Investissement';
        if ($classe == '1') return 'Financement';
        return null;
    }

    /**
     * Renommé en "store" pour correspondre à l'appel API du front-end
     */
   public function store(Request $request)
{
    try {
        DB::beginTransaction();
        
        if (empty($request->ecritures) || !is_array($request->ecritures)) {
            throw new \Exception('Aucune écriture à enregistrer.');
        }
        
        $user = auth()->user();

        // 1. Récupération des IDs depuis les paramètres de l'URL ou du formulaire global
        // Ces variables serviront de secours si la ligne individuelle est vide
        $idExerciceGlobal = $request->id_exercice ?? $request->exercices_comptables_id;
        $idJournalSaisiGlobal = $request->id_journal ?? $request->journaux_saisis_id;
        $idCodeJournalGlobal = $request->id_code ?? $request->code_journal_id;

        // 2. Génération du numéro de saisie
        $lastSaisie = EcritureComptable::max('n_saisie');
        $nextSaisieNumber = $lastSaisie ? str_pad((int) $lastSaisie + 1, 12, '0', STR_PAD_LEFT) : '000000000001';

        foreach ($request->ecritures as $index => $ecriture) {
            $planComptableId = $ecriture['plan_comptable_id'] ?? $ecriture['compte_general'] ?? null;
            
            if (!$planComptableId) {
                throw new \Exception("Le compte général est obligatoire (ligne " . ($index + 1) . ")");
            }

            // Gestion des fichiers
            $pieceJustificatifName = null;
            if ($request->hasFile("ecritures.$index.piece_justificatif")) {
                $file = $request->file("ecritures.$index.piece_justificatif");
                $pieceJustificatifName = time() . '_' . $file->getClientOriginalName();
                $file->move(public_path('justificatifs'), $pieceJustificatifName);
            }

            // Déduction du flux
            $debit = (float)($ecriture['debit'] ?? 0);
            $credit = (float)($ecriture['credit'] ?? 0);
            $typeFlux = $ecriture['typeFlux'] ?? null;

            if (is_null($typeFlux) && $planComptableId) {
                $plan = PlanComptable::find($planComptableId);
                if ($plan) {
                    $typeFlux = $this->determineFluxClasse($plan->numero_de_compte);
                }
            }

            // 3. Création de l'écriture avec sécurités sur les IDs
            EcritureComptable::create([
                'date' => $ecriture['date'] ?? now()->format('Y-m-d'),
                'n_saisie' => $nextSaisieNumber,
                'description_operation' => ucfirst(strtolower($ecriture['description'] ?? '')),
                'reference_piece' => strtoupper($ecriture['reference'] ?? ''),
                'plan_comptable_id' => $planComptableId,
                'plan_tiers_id' => $ecriture['compte_tiers'] ?? null,
                'compte_tresorerie_id' => $ecriture['tresorerieFields'] ?? null,
                'type_flux' => $typeFlux,
                'debit' => $debit,
                'credit' => $credit,
                'plan_analytique' => (($ecriture['analytique'] ?? 'Non') === 'Oui') ? 1 : 0,
                
                // UTILISATION DES FALLBACKS POUR ÉVITER L'ERREUR SQL
                'code_journal_id' => $ecriture['journal'] ?? $idCodeJournalGlobal,
                'exercices_comptables_id' => $ecriture['exercices_comptables_id'] ?? $idExerciceGlobal,
                'journaux_saisis_id' => $ecriture['journaux_saisis_id'] ?? $idJournalSaisiGlobal,
                
                'user_id' => $user->id,
                'company_id' => $user->company_id,
                'piece_justificatif' => $pieceJustificatifName,
            ]);
        }
        
        DB::commit();
        return response()->json(['success' => true, 'message' => 'Enregistré avec succès.']);
        
    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
    }
}

    public function getComptesParFlux(Request $request)
    {
        $typeFlux = $request->query('type');
        $query = PlanComptable::select('id', 'numero_de_compte', 'intitule');

        if ($typeFlux && stripos($typeFlux, 'Operationnelles') !== false) {
            $query->where(function($q) {
                $q->where('numero_de_compte', 'like', '4%')->orWhere('numero_de_compte', 'like', '5%')
                  ->orWhere('numero_de_compte', 'like', '6%')->orWhere('numero_de_compte', 'like', '7%');
            });
        } elseif ($typeFlux && stripos($typeFlux, 'Investissement') !== false) {
            $query->where(function($q) {
                $q->where('numero_de_compte', 'like', '2%')->orWhere('numero_de_compte', 'like', '4%')->orWhere('numero_de_compte', 'like', '5%');
            });
        } elseif ($typeFlux && stripos($typeFlux, 'Financement') !== false) {
            $query->where(function($q) {
                $q->where('numero_de_compte', 'like', '1%')->orWhere('numero_de_compte', 'like', '4%')->orWhere('numero_de_compte', 'like', '5%');
            });
        } else {
            $query->limit(500);
        }

        return response()->json($query->orderBy('numero_de_compte', 'asc')->get());
    }

    public function getNextSaisieNumber(Request $request)
    {
        try {
            $lastSaisie = EcritureComptable::max('n_saisie');
            $nextSaisieNumber = $lastSaisie ? str_pad((int) $lastSaisie + 1, 12, '0', STR_PAD_LEFT) : '000000000001';
            return response()->json(['success' => true, 'nextSaisieNumber' => $nextSaisieNumber]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
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
}