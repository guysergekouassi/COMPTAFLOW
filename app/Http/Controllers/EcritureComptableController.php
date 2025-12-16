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



class EcritureComptableController extends Controller
{
    //
    public function index(Request $request)
    {
        $user = Auth::user();
        $data = $request->all();

        // Initialisation des valeurs par défaut si manquantes
        if (!isset($data['annee'])) {
            $data['annee'] = Carbon::now()->year;
        }

        if (!isset($data['mois'])) {
            $data['mois'] = Carbon::now()->month;
        }

        if (!isset($data['id_exercice'])) {
            $dernierExercice = ExerciceComptable::where('company_id', $user->company_id)
                ->orderBy('date_debut', 'desc')
                ->first();
            
            if ($dernierExercice) {
                $data['id_exercice'] = $dernierExercice->id;
            }
        }

        if (!isset($data['id_journal'])) {
            $data['id_journal'] = null;
        }

        if (!isset($data['id_code'])) {
            $data['id_code'] = null;
        }

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

            // Récupération des postes de trésorerie (TOUS les postes, pas filtrés par company)
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

        // --- FIX ANTI-GRAVITY: Remplissage automatique des données manquantes (N/A) ---
        // 1. Essayer de récupérer les infos depuis le JournalSaisi (Si filtré par journal)
        if (!empty($data['id_journal'])) {
             $journalSaisi = \App\Models\JournalSaisi::with('codeJournal')->find($data['id_journal']);
             if ($journalSaisi && $journalSaisi->codeJournal) {
                 if (empty($data['code'])) $data['code'] = $journalSaisi->codeJournal->code_journal;
                 if (empty($data['type'])) $data['type'] = $journalSaisi->codeJournal->type;
                 if (empty($data['intitule'])) $data['intitule'] = $journalSaisi->codeJournal->intitule;
             }
        }

        // 2. Fallback sur la première écriture (si pas de filtre journal explicite mais qu'on a des données)
        if ($ecritures->isNotEmpty()) {
            $first = $ecritures->first();
            
            // Récupération via la relation CodeJournal (si disponible sur le modèle EcritureComptable)
            if ($first->codeJournal) {
                if (empty($data['code'])) $data['code'] = $first->codeJournal->code_journal;
                if (empty($data['type'])) $data['type'] = $first->codeJournal->type;
                if (empty($data['intitule'])) $data['intitule'] = $first->codeJournal->intitule;
            }

            // Récupération de la date si l'année/mois par défaut semblent incorrects ou manquants pour l'affichage
            if ($first->date) {
                try {
                    $dateObj = \Carbon\Carbon::parse($first->date);
                    if (empty($data['annee'])) $data['annee'] = $dateObj->year;
                } catch (\Exception $e) { }
            }
        }
        // -----------------------------------------------------------------------------

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
            'nextSaisieNumber', // on l’envoie à la vue
            'exercice',
            'dateDebut',
            'dateFin',
            'comptesTresorerie'
        ));
    }

    public function showSaisieModal()
    {

        $exercices = ExerciceComptable::orderBy('date_debut', 'desc')->get();


        // Journaux de Saisie : tous SAUF ceux de type Trésorerie (incluant les NULL)
        $journaux_saisie = CodeJournal::where('company_id', Auth::user()->company_id)
            ->where(function($query) {
                $query->where(function($q) {
                    $q->where('type', '!=', 'Trésorerie')
                      ->where('type', '!=', 'tresorerie');
                })->orWhereNull('type');
            })
            ->orderBy('code_journal', 'asc')
            ->get();
        
        // Récupérer les journaux de trésorerie (CodeJournal uniquement)
        $journaux_tresorerie = CodeJournal::where('company_id', Auth::user()->company_id)
            ->where(function($query) {
                $query->where('type', 'Trésorerie')
                      ->orWhere('type', 'tresorerie');
            })
            ->orderBy('code_journal', 'asc')
            ->get();


        return view('components.modal_saisie_direct', [
            'exercices' => $exercices,
            'journaux_saisie' => $journaux_saisie,
            'journaux_tresorerie' => $journaux_tresorerie,
        ]);
    }

    public function storeMultiple(Request $request)
    {

        // dd($request->ecritures);
        try {
            foreach ($request->ecritures as $index => $ecriture) {
                $pieceJustificatifName = null;

                if ($request->hasFile("ecritures.$index.piece_justificatif")) {
                    $file = $request->file("ecritures.$index.piece_justificatif");
                    $pieceJustificatifName = time() . '_' . $file->getClientOriginalName();
                    $file->move(public_path('justificatifs'), $pieceJustificatifName);
                }

                 $compteTresorerieId = $ecriture['tresorerieFields'] ?? null;

                 if($compteTresorerieId == ""){
                    $compteTresorerieId = null;
                 }

                 $typeFlux = $ecriture['typeFlux'] ?? null;

                 if($typeFlux == ""){
                    $typeFlux = null;
                 }

                EcritureComptable::create([
                    'date' => $ecriture['date'],
                    'n_saisie' => $ecriture['n_saisie'],
                    'description_operation' => ucfirst(strtolower($ecriture['description'])),
                    'reference_piece' => strtoupper($ecriture['reference']),
                    'plan_comptable_id' => $ecriture['compte_general'],
                    'plan_tiers_id' => $ecriture['compte_tiers'] ?? null,

                    // AJOUT CRUCIAL: Mappage vers la colonne de la DB
                    'compte_tresorerie_id' => $compteTresorerieId,
                    'type_flux' => $typeFlux,

                    'plan_analytique' => $ecriture['analytique'] === 'Oui' ? 1 : 0,
                    'code_journal_id' => $ecriture['journal'] ?? null,
                    'exercices_comptables_id' => $ecriture['exercices_comptables_id'] ?? null,
                    'journaux_saisis_id' => $ecriture['journaux_saisis_id'] ?? null,
                    'debit' => $ecriture['debit'],
                    'credit' => $ecriture['credit'],
                    'piece_justificatif' => $pieceJustificatifName,
                    'user_id' => Auth::id(),
                    'company_id' => Auth::user()->company_id,
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
                // Flux opérationnels : Tiers (4), Trésorerie (5), Charges (6), Produits (7)
                $q->where('numero_de_compte', 'like', '4%')
                  ->orWhere('numero_de_compte', 'like', '5%')
                  ->orWhere('numero_de_compte', 'like', '6%')
                  ->orWhere('numero_de_compte', 'like', '7%');
            });
        } elseif ($typeFlux && stripos($typeFlux, 'Investissement') !== false) {
             Log::info("Matched: Investissement - Classes 2, 4, 5");
             // Flux d'investissement : Immobilisations (2), Tiers (4), Trésorerie (5)
            $query->where(function($q) {
                $q->where('numero_de_compte', 'like', '2%')
                  ->orWhere('numero_de_compte', 'like', '4%')
                  ->orWhere('numero_de_compte', 'like', '5%');
            });
        } elseif ($typeFlux && stripos($typeFlux, 'Financement') !== false) {
             Log::info("Matched: Financement - Classes 1, 4, 5");
             // Flux de financement : Capitaux (1), Tiers (4), Trésorerie (5)
            $query->where(function($q) {
                $q->where('numero_de_compte', 'like', '1%')
                  ->orWhere('numero_de_compte', 'like', '4%')
                  ->orWhere('numero_de_compte', 'like', '5%');
            });
        }
        // Cas par défaut : si aucun flux reconnu, on limite pour la performance
        else {
             Log::info("No match found. Returning default limit 500.");
             $query->limit(500); 
        }

        $comptes = $query->orderBy('numero_de_compte', 'asc')->get();
        Log::info("Returning " . $comptes->count() . " accounts.");

        return response()->json($comptes);
    }

}
