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
            
            // Génération du numéro de saisie unique pour ce batch
            $lastSaisie = EcritureComptable::max('n_saisie');
            $nextSaisieNumber = $lastSaisie ? str_pad((int) $lastSaisie + 1, 12, '0', STR_PAD_LEFT) : '000000000001';

            foreach ($request->ecritures as $index => $ecriture) {
                $pieceJustificatifName = null;
                if ($request->hasFile("ecritures.$index.piece_justificatif")) {
                    $file = $request->file("ecritures.$index.piece_justificatif");
                    $pieceJustificatifName = time() . '_' . $file->getClientOriginalName();
                    $file->move(public_path('justificatifs'), $pieceJustificatifName);
                }

                $debit = (float)($ecriture['debit'] ?? 0);
                $credit = (float)($ecriture['credit'] ?? 0);
                $compteTresorerieId = !empty($ecriture['tresorerieFields']) ? $ecriture['tresorerieFields'] : null;
                $typeFlux = !empty($ecriture['typeFlux']) ? $ecriture['typeFlux'] : null;

                // Logique de déduction automatique du flux
                if (!is_null($compteTresorerieId) && is_null($typeFlux)) {
                    $typeFlux = ($debit > 0) ? 'encaissement' : (($credit > 0) ? 'decaissement' : null);
                }

                if (is_null($typeFlux) && !empty($ecriture['compte_general'])) {
                    $planComptable = PlanComptable::find($ecriture['compte_general']);
                    if ($planComptable) {
                        $typeFlux = $this->determineFluxClasse($planComptable->numero_de_compte);
                    }
                }

                EcritureComptable::create([
                    'date' => $ecriture['date'],
                    'n_saisie' => $nextSaisieNumber,
                    'description_operation' => ucfirst(strtolower($ecriture['description'] ?? '')),
                    'reference_piece' => strtoupper($ecriture['reference'] ?? ''),
                    'plan_comptable_id' => $ecriture['compte_general'],
                    'plan_tiers_id' => $ecriture['compte_tiers'] ?? null,
                    'compte_tresorerie_id' => $compteTresorerieId,
                    'type_flux' => $typeFlux,
                    'debit' => $debit,
                    'credit' => $credit,
                    'plan_analytique' => ($ecriture['analytique'] ?? 'Non') === 'Oui' ? 1 : 0,
                    'code_journal_id' => $ecriture['journal'] ?? null,
                    'exercices_comptables_id' => $ecriture['exercices_comptables_id'] ?? null,
                    'journaux_saisis_id' => $ecriture['journaux_saisis_id'] ?? null,
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
}