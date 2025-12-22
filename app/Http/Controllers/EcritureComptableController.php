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

        // Vérifier si des exercices existent pour cette entreprise
        $exercicesCount = ExerciceComptable::where('company_id', $user->company_id)->count();

        // Si aucun exercice n'existe, rediriger vers la page de création
        if ($exercicesCount == 0) {
            return redirect()->route('exercice_comptable')->with('info', 'Veuillez créer un exercice comptable avant de pouvoir gérer les écritures.');
        }

        // Valeurs par défaut si non fournies
        $data['annee'] = $data['annee'] ?? date('Y');
        $data['mois'] = $data['mois'] ?? date('n'); // mois sans leading zero
        $data['id_exercice'] = $data['id_exercice'] ?? null;

        $plansComptables = PlanComptable::where('company_id', $user->company_id)
            ->select('id', 'numero_de_compte', 'intitule')
            ->orderByRaw("LEFT(numero_de_compte, 1) ASC")  // trie par classe
            ->orderBy('numero_de_compte', 'asc')           // trie par compte
            ->get();


        $plansTiers = PlanTiers::where('company_id', $user->company_id)
            ->select('id', 'numero_de_tiers', 'intitule', 'compte_general')
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

        $exercice = $data['id_exercice'] ? ExerciceComptable::findOrFail($data['id_exercice']) : null;

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
        $user = Auth::user();

        $exercices = ExerciceComptable::where('company_id', $user->company_id)
            ->orderBy('date_debut', 'desc')
            ->get();

        // Récupérer l'exercice actif (non clôturé) pour pré-sélection
        $exerciceActif = ExerciceComptable::where('company_id', $user->company_id)
            ->where('cloturer', 0)
            ->orderBy('date_debut', 'desc')
            ->first();

        $code_journaux = CodeJournal::where('company_id', $user->company_id)
            ->orderBy('code_journal', 'asc')
            ->get();

        return view('components.modal_saisie_direct', [
            'exercices' => $exercices,
            'code_journaux' => $code_journaux,
            'exerciceActif' => $exerciceActif,
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



public function getComptesParFlux(Request $request) {
    $user = Auth::user();
    $typeFlux = $request->query('type');

    Log::info("AJAX getComptesParFlux called. TypeFlux received: '" . $typeFlux . "'");

    $query = PlanComptable::where('company_id', $user->company_id)
        ->select('id', 'numero_de_compte', 'intitule');

    // Filtrage selon la logique comptable des flux de trésorerie
    if ($typeFlux && stripos($typeFlux, 'Operationnelles') !== false) {
         Log::info("Matched: Operationnelles - Classes 4, 5, 6, 7");
        $query->where(function($q) {
            // ✅ AJOUT : Classes 4 et 5
            $q->where('numero_de_compte', 'like', '4%')
              ->orWhere('numero_de_compte', 'like', '5%')
              ->orWhere('numero_de_compte', 'like', '6%')
              ->orWhere('numero_de_compte', 'like', '7%');
        });
    } elseif ($typeFlux && stripos($typeFlux, 'Investissement') !== false) {
         Log::info("Matched: Investissement - Classes 2, 4, 5");
        $query->where(function($q) {
            // ✅ AJOUT : Classes 4 et 5
            $q->where('numero_de_compte', 'like', '2%')
              ->orWhere('numero_de_compte', 'like', '4%')
              ->orWhere('numero_de_compte', 'like', '5%');
        });
    } elseif ($typeFlux && stripos($typeFlux, 'Financement') !== false) {
         Log::info("Matched: Financement - Classes 1, 4, 5");
        $query->where(function($q) {
            // ✅ AJOUT : Classes 4 et 5
            $q->where('numero_de_compte', 'like', '1%')
              ->orWhere('numero_de_compte', 'like', '4%')
              ->orWhere('numero_de_compte', 'like', '5%');
        });
    }
    else {
         Log::info("No match found. Returning default limit 500.");
         $query->limit(500);
    }

    $comptes = $query->orderBy('numero_de_compte', 'asc')->get();
    Log::info("Returning " . $comptes->count() . " accounts.");

    return response()->json($comptes);
}





    public function list(Request $request)
    {
        $user = Auth::user();
        $data = $request->all();

        // Récupérer les données de base
        $exercices = ExerciceComptable::where('company_id', $user->company_id)->get();
        $code_journaux = CodeJournal::where('company_id', $user->company_id)->get();

        // Construire la requête pour les écritures
        $query = EcritureComptable::where('company_id', $user->company_id);

        // Appliquer les filtres
        if (!empty($data['exercice_id'])) {
            $query->where('exercice_id', $data['exercice_id']);
        }

        if (!empty($data['mois'])) {
            $query->whereMonth('date', $data['mois']);
        }

        if (!empty($data['journal_id'])) {
            $query->where('code_journal_id', $data['journal_id']);
        }

        // Récupérer les écritures
        $ecritures = $query->orderBy('date', 'desc')->orderBy('n_saisie', 'desc')->get();

        // Calculer les totaux
        $totalDebit = $ecritures->sum('debit');
        $totalCredit = $ecritures->sum('credit');

        // Récupérer les données pour les formulaires
        $plansComptables = PlanComptable::where('company_id', $user->company_id)
            ->select('id', 'numero_de_compte', 'intitule')
            ->orderByRaw("LEFT(numero_de_compte, 1) ASC")
            ->orderBy('numero_de_compte', 'asc')
            ->get();

        $tiers = PlanTiers::where('company_id', $user->company_id)
            ->select('id', 'numero_de_tiers', 'intitule')
            ->orderByRaw("LEFT(numero_de_tiers, 1) ASC")
            ->orderBy('numero_de_tiers', 'asc')
            ->get();

        $postesTresorerie = CompteTresorerie::where('company_id', $user->company_id)
            ->orderBy('name', 'asc')
            ->get();

        return view('accounting_entry_list', compact(
            'exercices',
            'code_journaux',
            'ecritures',
            'totalDebit',
            'totalCredit',
            'plansComptables',
            'tiers',
            'postesTresorerie',
            'data'
        ));
    }

}
