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
use Illuminate\Support\Facades\Log;
use App\Models\tresoreries\Tresoreries;


class EcritureComptableController extends Controller
{
    //
    public function index(Request $request)
    {
        $user = Auth::user();
        $data = $request->all();

        $plansComptables = PlanComptable::where('company_id', $user->company_id)
            ->select('id', 'numero_de_compte', 'intitule')
            ->orderByRaw("LEFT(numero_de_compte, 1) ASC")  // trie par classe
            ->orderBy('numero_de_compte', 'asc')           // trie par compte
            ->get();


        $plansTiers = PlanTiers::where('company_id', $user->company_id)
            ->select('id', 'numero_de_tiers', 'intitule')
            ->orderByRaw("LEFT(numero_de_tiers, 1) ASC")
            ->orderBy('numero_de_tiers', 'asc')
            ->get();

            // Récupération des postes de trésorerie
        //  $comptesTresorerie = CompteTresorerie::where('company_id', $user->company_id)
        // ->select('id', 'name', 'type')
        // ->get();
        $comptesTresorerie = CompteTresorerie::select('id', 'name', 'type')
        ->orderBy('name', 'asc')
        ->get();


        $query = EcritureComptable::where('company_id', $user->company_id)
            ->orderBy('created_at', 'desc')
            ->orderBy('n_saisie');

        if (!empty($data['id_journal'])) {
            $query->where('journaux_saisis_id', $data['id_journal']);
        }

        $dateDebut = Carbon::create($data['annee'], $data['mois'], 1)
            ->startOfMonth()
            ->toDateString(); // 'YYYY-MM-DD'

        $dateFin = Carbon::create($data['annee'], $data['mois'], 1)
            ->endOfMonth()
            ->toDateString(); // 'YYYY-MM-DD'




        // Cloner la requête pour la somme
        $queryForSum = clone $query;

        $ecritures = $query->with(['planComptable', 'planTiers','compteTresorerie'])->get();

        $totalDebit = $queryForSum->sum('debit');
        $totalCredit = $queryForSum->sum('credit');

        // Génération automatique du n° de saisie (12 chiffres, unique)
        $lastSaisie = EcritureComptable::where('company_id', $user->company_id)
            ->max('n_saisie');

        $nextSaisieNumber = $lastSaisie ? str_pad((int) $lastSaisie + 1, 12, '0', STR_PAD_LEFT) : '000000000001';

        $exercice = ExerciceComptable::findOrFail($data['id_exercice']);

        // dd($dateDebut . '' . $dateFin);

        return view('accounting_entry_real', compact(
            'plansComptables',
            'plansTiers',
            'data',
            'ecritures',
            'totalDebit',
            'totalCredit',
            'nextSaisieNumber',
            'exercice',
            'dateDebut',
            'dateFin',
            'comptesTresorerie'
        ));
    }

    public function showSaisieModal()
    {

        $exercices = ExerciceComptable::orderBy('date_debut', 'desc')->get();


        $code_journaux = CodeJournal::orderBy('code_journal', 'asc')->get();



        return view('components.modal_saisie_direct', [
            'exercices' => $exercices,
            'code_journaux' => $code_journaux,
        ]);
    }

 public function storeMultiple(Request $request)
{
    Log::info('Données d\'écritures reçues:', $request->ecritures);

    // Initialiser une liste d'erreurs pour le retour
    $errors = [];

    try {
        foreach ($request->ecritures as $index => $ecriture) {
            $pieceJustificatifName = null;

            // ... (GESTION FICHIER)

            // 1. Gestion du Fichier Justificatif
            if ($request->hasFile("ecritures.$index.piece_justificatif")) {
                $file = $request->file("ecritures.$index.piece_justificatif");
                $pieceJustificatifName = time() . '_' . $file->getClientOriginalName();
                $file->move(public_path('justificatifs'), $pieceJustificatifName);
            }


            // 2. Préparation et Sécurisation des variables (LE CODE QUE VOUS AVEZ DÉJÀ)
            $debit = (float)($ecriture['debit'] ?? 0);
            $credit = (float)($ecriture['credit'] ?? 0);

            $compteTresorerieId = $ecriture['tresorerieFields'] ?? null;
            if ($compteTresorerieId === "" || is_null($compteTresorerieId)) {
                $compteTresorerieId = null;
            }

            $typeFlux = $ecriture['typeFlux'] ?? null;
            if ($typeFlux === "" || is_null($typeFlux)) {
                $typeFlux = null;
            }

            // 3. LOGIQUE DE DÉDUCTION DU TYPE DE FLUX (inchangée)
            if (!is_null($compteTresorerieId) && is_null($typeFlux)) {

                if ($debit > 0) {
                    $typeFlux = 'encaissement';
                }
                elseif ($credit > 0) {
                    $typeFlux = 'decaissement';
                }
            }

            //NOUVELLE LOGIQUE
            $compteGeneralId = $ecriture['compte_general'] ?? null;

            if (is_null($typeFlux) && $compteGeneralId) {
                // Récupérer le numéro de compte général
                $planComptable = PlanComptable::find($compteGeneralId);

                if ($planComptable) {
                    $classeFluxDeterminee = $this->determineFluxClasse($planComptable->numero_de_compte);


                    if (is_null($compteTresorerieId)) {
                        $typeFlux = $classeFluxDeterminee;
                    }
                }
            }



            // 4. Insertion de l'écriture complète
            try {
                 EcritureComptable::create([
                    'date' => $ecriture['date'],
                    'n_saisie' => $ecriture['n_saisie'],
                    'description_operation' => ucfirst(strtolower($ecriture['description'])),
                    'reference_piece' => strtoupper($ecriture['reference']),
                    'plan_comptable_id' => $ecriture['compte_general'],
                    'plan_tiers_id' => $ecriture['compte_tiers'] ?? null,

                    // 'compte_tresorerie_id' => $compteTresorerieId,
                    'compte_tresorerie_id' => $compteTresorerieId,
                    'type_flux' => $typeFlux,

                    'debit' => $debit,
                    'credit' => $credit,

                    'plan_analytique' => $ecriture['analytique'] === 'Oui' ? 1 : 0,
                    'code_journal_id' => $ecriture['journal'] ?? null,
                    'exercices_comptables_id' => $ecriture['exercices_comptables_id'] ?? null,
                    'journaux_saisis_id' => $ecriture['journaux_saisis_id'] ?? null,
                    'piece_justificatif' => $pieceJustificatifName,
                    'user_id' => Auth::id(),
                    'company_id' => Auth::user()->company_id,
                ]);
            } catch (\Illuminate\Database\QueryException $e) {
                // CAPTURE DE L'ERREUR SPÉCIFIQUE À LA LIGNE
                $errorMessage = "Erreur à la ligne " . ($index + 1) . " (Compte " . $ecriture['compte_general'] . "): " . $e->getMessage();
                Log::error($errorMessage);
                // Si une ligne échoue, nous arrêtons et renvoyons l'erreur
                return response()->json([
                    'error' => 'Échec de l\'enregistrement de la saisie batch.',
                    'details' => $errorMessage
                ], 500);
            }
        }

        return response()->json(['message' => 'Toutes les écritures ont été enregistrées avec succès.']);
    } catch (\Exception $e) {
        // Erreur inattendue (non liée à la base de données)
        return response()->json([
            'error' => 'Une erreur générale est survenue lors de l\'enregistrement.',
            'details' => $e->getMessage()
        ], 500);
    }
}

public function determineFluxClasse(string $numeroCompte){
    //on prends le premier chiffre pour eterminer la classe
    $classe = substr($numeroCompte, 0, 1);

    switch($classe){
        case '1':
        case '3':
        case '4':
        case '6':
        case '7':
          return 'Operation';

        case '2':
            return 'investissement';

        case '1':
            if(strpos($numeroCompte, '16') === 0 || strpos($numeroCompte, '10') === 0){
                return 'financement';
            }
            return 'Exploitation'; //pour les 1x

            case '5':
                return null;

                default:
                 return null;


    }
}



}
