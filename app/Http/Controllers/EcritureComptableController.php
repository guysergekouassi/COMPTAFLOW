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
<<<<<<< HEAD
use App\Models\tresoreries\Tresoreries;
=======
>>>>>>> e75dd97871a7b3f1790f1751c44c99b0e43a5fb9
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

<<<<<<< HEAD
    public function showSaisieModal()
    {
        $user = Auth::user();
        
        // Récupérer l'ID de la société active (gestion du switch admin)
        $companyId = session('current_company_id', $user->company_id);
        
        // Récupérer les exercices uniques par intitulé, triés par date de début décroissante
        $exercices = ExerciceComptable::where('company_id', $companyId)
            ->orderBy('date_debut', 'desc')
            ->get()
            ->unique('intitule')
            ->values();

        // Récupérer l'exercice actif (non clôturé) ou le premier disponible
        $exerciceActif = $exercices->firstWhere('cloturer', 0) ?? $exercices->first();

        // Récupérer les journaux pour la société active
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
    // Initialiser une liste d'erreurs pour le retour
    $errors = [];
    
    // Récupérer le numéro de saisie de la session ou en générer un nouveau
    if (!session()->has('current_saisie_number')) {
        $lastSaisie = EcritureComptable::max('n_saisie');
        $nextSaisieNumber = $lastSaisie ? str_pad((int) $lastSaisie + 1, 12, '0', STR_PAD_LEFT) : '000000000001';
        session(['current_saisie_number' => $nextSaisieNumber]);
    } else {
        $nextSaisieNumber = session('current_saisie_number');
    }

    try {
        // Utiliser le numéro de saisie de la session
        $nextSaisieNumber = session('current_saisie_number');
        
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
                    'n_saisie' => $nextSaisieNumber, // Utilisation du même numéro pour toutes les écritures
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
                    // 'user_id' et 'company_id' sont maintenant gérés par BelongsToTenant
                ]);
            } catch (\Illuminate\Database\QueryException $e) {
                // CAPTURE DE L'ERREUR SPÉCIFIQUE À LA LIGNE
                $errorMessage = "Erreur à la ligne " . ($index + 1) . " (Compte " . $ecriture['compte_general'] . "): " . $e->getMessage();
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

public function getComptesParFlux(Request $request)
{
    $user = Auth::user();
    $typeFlux = $request->query('type');

    $query = PlanComptable::select('id', 'numero_de_compte', 'intitule');

    // Filtrage selon la logique comptable des flux de trésorerie
    if ($typeFlux && stripos($typeFlux, 'Operationnelles') !== false) {
        $query->where(function($q) {
            // ✅ AJOUT : Classes 4 et 5
            $q->where('numero_de_compte', 'like', '4%')
              ->orWhere('numero_de_compte', 'like', '5%')
              ->orWhere('numero_de_compte', 'like', '6%')
              ->orWhere('numero_de_compte', 'like', '7%');
        });
    } elseif ($typeFlux && stripos($typeFlux, 'Investissement') !== false) {
        $query->where(function($q) {
            // ✅ AJOUT : Classes 4 et 5
            $q->where('numero_de_compte', 'like', '2%')
              ->orWhere('numero_de_compte', 'like', '4%')
              ->orWhere('numero_de_compte', 'like', '5%');
        });
    } elseif ($typeFlux && stripos($typeFlux, 'Financement') !== false) {
        $query->where(function($q) {
            // ✅ AJOUT : Classes 4 et 5
            $q->where('numero_de_compte', 'like', '1%')
              ->orWhere('numero_de_compte', 'like', '4%')
              ->orWhere('numero_de_compte', 'like', '5%');
        });
    }
    else {
         $query->limit(500);
    }

    $comptes = $query->orderBy('numero_de_compte', 'asc')->get();

    return response()->json($comptes);
}





    public function list(Request $request)
    {
        $user = Auth::user();
        $data = $request->all();

        // Récupérer les données de base
        $exercices = ExerciceComptable::get()->unique('intitule');
        $code_journaux = CodeJournal::get()->unique('code_journal');

        // Construire la requête pour les écritures
        $query = EcritureComptable::query();

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

        $journal = null;
        if (!empty($data['journal_id'])) {
            $journal = CodeJournal::find($data['journal_id']);
        }
        if ($journal === null) {
            $journal = CodeJournal::orderBy('code_journal')->first();
        }

        $exercice = null;
        if (!empty($data['exercice_id'])) {
            $exercice = ExerciceComptable::find($data['exercice_id']);
        }
        if ($exercice === null) {
            $exercice = ExerciceComptable::orderBy('date_debut', 'desc')->first();
        }

        // Récupérer les écritures
        $ecritures = $query->orderBy('date', 'desc')->orderBy('n_saisie', 'desc')->get();

        // Calculer les totaux
        $totalDebit = $ecritures->sum('debit');
        $totalCredit = $ecritures->sum('credit');

        // Récupérer les données pour les formulaires
        $plansComptables = PlanComptable::select('id', 'numero_de_compte', 'intitule')
            ->orderByRaw("LEFT(numero_de_compte, 1) ASC")
            ->orderBy('numero_de_compte', 'asc')
            ->get();

        $tiers = PlanTiers::select('id', 'numero_de_tiers', 'intitule')
            ->orderByRaw("LEFT(numero_de_tiers, 1) ASC")
            ->orderBy('numero_de_tiers', 'asc')
            ->get();

        $postesTresorerie = CompteTresorerie::orderBy('name', 'asc')
            ->get();

        $entries = $ecritures;

        // Génération automatique du n° de saisie (12 chiffres, unique)
    $lastSaisie = EcritureComptable::max('n_saisie');
    $nextSaisieNumber = $lastSaisie ? str_pad((int) $lastSaisie + 1, 12, '0', STR_PAD_LEFT) : '000000000001';

    return view('accounting_entry_list', compact(
        'exercices',
        'code_journaux',
        'ecritures',
        'entries',
        'journal',
        'exercice',
        'totalDebit',
        'totalCredit',
        'plansComptables',
        'tiers',
        'postesTresorerie',
        'nextSaisieNumber',
        'data'
    ));
    }

=======
>>>>>>> e75dd97871a7b3f1790f1751c44c99b0e43a5fb9
    /**
     * Détermine le flux selon la classe du compte (Indispensable pour éviter l'erreur 500)
     */
<<<<<<< HEAD
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
=======
    private function determineFluxClasse($numeroCompte) {
        $classe = substr($numeroCompte, 0, 1);
        if (in_array($classe, ['6', '7'])) return 'Operationnelles';
        if ($classe == '2') return 'Investissement';
        if ($classe == '1') return 'Financement';
        return null;
>>>>>>> e75dd97871a7b3f1790f1751c44c99b0e43a5fb9
    }

    /**
     * Renommé en "store" pour correspondre à l'appel API du front-end
     */
    public function store(Request $request)
    {
<<<<<<< HEAD
        // Récupérer les données JSON brutes
        $data = $request->all();
        
        // Journalisation pour le débogage
        
        // Valider les données
        $validated = $request->validate([
            'ecritures' => 'required|array|min:1',
            'ecritures.*.date' => 'required|date',
            'ecritures.*.n_saisie' => 'required|string|max:12',
            'ecritures.*.code_journal_id' => 'required|exists:code_journals,id',
            'ecritures.*.plan_comptable_id' => 'required|exists:plan_comptables,id',
            'ecritures.*.debit' => 'required|numeric|min:0',
            'ecritures.*.credit' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();
        
=======
>>>>>>> e75dd97871a7b3f1790f1751c44c99b0e43a5fb9
        try {
            DB::beginTransaction();
            
            // Valider que des écritures sont présentes
            if (empty($request->ecritures) || !is_array($request->ecritures)) {
                throw new \Exception('Aucune écriture à enregistrer.');
            }
            
            // Génération du numéro de saisie unique pour ce batch
            $lastSaisie = EcritureComptable::max('n_saisie');
            $nextSaisieNumber = $lastSaisie ? str_pad((int) $lastSaisie + 1, 12, '0', STR_PAD_LEFT) : '000000000001';
            $user = auth()->user();

            foreach ($request->ecritures as $index => $ecriture) {
                // Vérifier que les champs obligatoires sont présents
                if (empty($ecriture['plan_comptable_id']) && empty($ecriture['compte_general'])) {
                    throw new \Exception("Le compte général est obligatoire pour l'écriture #" . ($index + 1));
                }
                
                // Utiliser plan_comptable_id ou compte_general selon ce qui est disponible
                $planComptableId = $ecriture['plan_comptable_id'] ?? $ecriture['compte_general'] ?? null;
                
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

                if (is_null($typeFlux) && !empty($planComptableId)) {
                    $planComptable = PlanComptable::find($planComptableId);
                    if ($planComptable) {
                        $typeFlux = $this->determineFluxClasse($planComptable->numero_de_compte);
                    }
                }

                $ecritureData = [
                    'date' => $ecriture['date'] ?? now()->format('Y-m-d'),
                    'n_saisie' => $ecriture['n_saisie'] ?? $nextSaisieNumber,
                    'description_operation' => ucfirst(strtolower($ecriture['description_operation'] ?? $ecriture['description'] ?? '')),
                    'reference_piece' => strtoupper($ecriture['reference_piece'] ?? $ecriture['reference'] ?? ''),
                    'plan_comptable_id' => $planComptableId,
                    'plan_tiers_id' => $ecriture['plan_tiers_id'] ?? $ecriture['compte_tiers'] ?? null,
                    'compte_tresorerie_id' => $compteTresorerieId,
                    'type_flux' => $typeFlux,
                    'debit' => $debit,
                    'credit' => $credit,
                    'plan_analytique' => (isset($ecriture['plan_analytique']) && $ecriture['plan_analytique'] === 'Oui') || ($ecriture['analytique'] ?? 'Non') === 'Oui' ? 1 : 0,
                    'code_journal_id' => $ecriture['code_journal_id'] ?? $ecriture['journal'] ?? null,
                    'exercices_comptables_id' => $ecriture['exercices_comptables_id'] ?? $ecriture['exercice_id'] ?? null,
                    'journaux_saisis_id' => $ecriture['journaux_saisis_id'] ?? $ecriture['journal_id'] ?? null,
                    'piece_justificatif' => $pieceJustificatifName,
                    'user_id' => $user ? $user->id : null,
                    'company_id' => $user ? $user->company_id : null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                // Nettoyer les valeurs nulles
                $ecritureData = array_filter($ecritureData, function($value) {
                    return $value !== null && $value !== '';
                });

                EcritureComptable::create($ecritureData);
            }
            
            DB::commit();
            return response()->json(['success' => true, 'message' => 'Enregistré avec succès.']);
            
        } catch (\Exception $e) {
            DB::rollBack();
<<<<<<< HEAD
            
            return response()->json([
                'success' => false,
                'message' => 'Une erreur est survenue lors de l\'enregistrement des écritures',
                'error' => $e->getMessage()
            ], 500);
=======
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
>>>>>>> e75dd97871a7b3f1790f1751c44c99b0e43a5fb9
        }
    }

    public function storeMultiple(Request $request)
    {
        try {
            $ecritures = $request->input('ecritures');
            if (empty($ecritures) || !is_array($ecritures)) {
                return response()->json(['success' => false, 'message' => 'Aucune écriture à enregistrer.'], 400);
            }

            foreach ($ecritures as $ecriture) {
                EcritureComptable::create($ecriture);
            }

            return response()->json(['success' => true, 'message' => 'Écritures enregistrées avec succès.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
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
        $ecritures = EcritureComptable::with(['planComptable', 'planTiers', 'compteTresorerie'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('accounting_entry_list', compact('ecritures'));
    }
}