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

            // Récupérer le dernier numéro de saisie et incrémenter
        $lastSaisie = EcritureComptable::max('id');
        $nextSaisieNumber = str_pad(($lastSaisie ? $lastSaisie + 1 : 1), 12, '0', STR_PAD_LEFT);

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
     * Affiche les détails d'une écriture comptable spécifique.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */

    public function show($id)
    {
        try {
            $user = Auth::user();
            
            // Récupérer l'écriture avec ses relations
            $ecriture = EcritureComptable::with(['planComptable', 'planTiers', 'compteTresorerie', 'codeJournal'])
                ->where('company_id', $user->company_id)
                ->findOrFail($id);
                
            return view('ecriture_show', compact('ecriture'));
            
        } catch (\Exception $e) {
            return redirect()->route('accounting_entry_list')
                ->with('error', 'Écriture non trouvée : ' . $e->getMessage());
        }
    }

    /**
     * Affiche le formulaire d'édition d'une écriture comptable spécifique.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */

    public function edit($id)
    {
        try {
            $user = Auth::user();
            
            // Récupérer l'écriture avec ses relations
            $ecriture = EcritureComptable::with(['planComptable', 'planTiers', 'compteTresorerie', 'codeJournal'])
                ->where('company_id', $user->company_id)
                ->findOrFail($id);
                
            $plansComptables = PlanComptable::select('id', 'numero_de_compte', 'intitule')
                ->orderBy('numero_de_compte')
                ->get();
                
            $plansTiers = PlanTiers::select('id', 'numero_de_tiers', 'intitule', 'compte_general')
                ->with('compte')
                ->get();
                
            $comptesTresorerie = CompteTresorerie::select('id', 'name', 'type')
                ->orderBy('name')
                ->get();
                
            $codeJournaux = CodeJournal::all();
            
            // Debug: Vérifier les données avant l'envoi à la vue
            \Log::info('Données envoyées à la vue d\'édition:', [
                'ecriture_id' => $ecriture->id,
                'compte_general' => $ecriture->compte_general,
                'tiers' => $ecriture->tiers,
                'libelle' => $ecriture->libelle,
                'mouvement' => $ecriture->mouvement,
                'date_ecriture' => $ecriture->date_ecriture,
                'piece' => $ecriture->piece,
                'n_saisie' => $ecriture->n_saisie,
            ]);
            
            return view('accounting_entry_edit', compact(
                'ecriture', 
                'plansComptables', 
                'plansTiers', 
                'comptesTresorerie',
                'codeJournaux'
            ));
            
        } catch (\Exception $e) {
           
            return redirect()->back()->with('error', 'Impossible de charger l\'écriture pour édition: ' . $e->getMessage());
        }
    }

    /**
     * Enregistre une nouvelle écriture comptable.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
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

                // Récupérer l'exercice comptable
                $exerciceActif = null;
                
                // 1. Vérifier si un exercice est spécifié dans la requête
                if (!empty($ecriture['exercices_comptables_id'])) {
                    $exerciceActif = ExerciceComptable::where('id', $ecriture['exercices_comptables_id'])
                        ->first();
                }
                
                // 2. Sinon, chercher l'exercice actif (non clôturé)
                if (!$exerciceActif) {
                    $exerciceActif = ExerciceComptable::where('cloturer', 0)
                        ->orderBy('date_debut', 'desc')
                        ->first();
                }
                
                // 3. Si toujours pas trouvé, prendre le dernier exercice créé
                if (!$exerciceActif) {
                    $exerciceActif = ExerciceComptable::orderBy('date_debut', 'desc')
                        ->first();
                }

                if (!$exerciceActif) {
                    throw new \Exception('Aucun exercice comptable trouvé. Veuillez d\'abord créer un exercice comptable.');
                }

                // Récupérer le journal saisi par défaut si non spécifié
                $journalSaisiId = $ecriture['journaux_saisis_id'] ?? $ecriture['journal_id'] ?? null;
                if (!$journalSaisiId && !empty($ecriture['code_journal_id'])) {
                    $journalSaisiId = $ecriture['code_journal_id'];
                }

                $ecritureData = [
                    'date' => $ecriture['date'] ?? now()->format('Y-m-d'),
                    'n_saisie' => $ecriture['n_saisie'] ?? $nextSaisieNumber,
                    'description_operation' => ucfirst(strtolower($ecriture['description_operation'] ?? $ecriture['description'] ?? '')),
                    'reference_piece' => strtoupper($ecriture['reference_piece'] ?? $ecriture['reference'] ?? ''),
                    'plan_comptable_id' => $planComptableId,
                    'plan_tiers_id' => $ecriture['plan_tiers_id'] ?? $ecriture['compte_tiers'] ?? null,
                    'compte_tresorerie_id' => $compteTresorerieId,
                    // 'type_flux' => $typeFlux ? strtolower($typeFlux) : null,
                    'debit' => $debit,
                    'credit' => $credit,
                    'plan_analytique' => (isset($ecriture['plan_analytique']) && $ecriture['plan_analytique'] === 'Oui') || ($ecriture['analytique'] ?? 'Non') === 'Oui' ? 1 : 0,
                    'code_journal_id' => $ecriture['code_journal_id'] ?? $ecriture['journal'] ?? null,
                    'exercices_comptables_id' => $exerciceActif->id,
                    'journaux_saisis_id' => $journalSaisiId,
                    'piece_justificatif' => $pieceJustificatifName,
                    'user_id' => $user ? $user->id : null,
                    // 'company_id' => $user ? $user->company_id : null,
                   'company_id' => session('current_company_id', $user->company_id),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                // Nettoyer les valeurs nulles
                $ecritureData = array_filter($ecritureData, function($value) {
    return !is_null($value) && $value !== '';
});
                EcritureComptable::create($ecritureData);
            }
            
            DB::commit();
            return response()->json(['success' => true, 'message' => 'Enregistré avec succès.']);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
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
            // Récupérer le dernier numéro de saisie
            $lastSaisie = EcritureComptable::orderBy('id', 'desc')->first();
            $nextNumber = 1;
            
            if ($lastSaisie && !empty($lastSaisie->n_saisie)) {
                // Extraire le numéro de la dernière saisie
                if (preg_match('/^(\d+)/', $lastSaisie->n_saisie, $matches)) {
                    $nextNumber = (int)$matches[1] + 1;
                } else {
                    $nextNumber = $lastSaisie->id + 1;
                }
            } elseif ($lastSaisie) {
                // Si n_saisie est vide mais qu'il y a des enregistrements
                $nextNumber = $lastSaisie->id + 1;
            }
            
            // Formater le numéro sur 12 chiffres
            $nextSaisieNumber = str_pad($nextNumber, 12, '0', STR_PAD_LEFT);
            
            // Ajouter la date actuelle au format JJ/MM/AAAA
            $formattedDate = now()->format('d/m/Y');
            
            return response()->json([
                'success' => true, 
                'nextSaisieNumber' => $nextSaisieNumber,
                'date' => $formattedDate
            ]);
            
        } catch (\Exception $e) {
            // En cas d'erreur, retourner une valeur par défaut
            return response()->json([
                'success' => true, 
                'nextSaisieNumber' => '000000000001',
                'date' => now()->format('d/m/Y')
            ]);
        }
    }

    public function list(Request $request)
    {
        $user = Auth::user();
        $data = $request->all();
        
        // Activation des logs de débogage
        \Log::info('=== DÉBUT DÉBOGAGE ===');
        \Log::info('Utilisateur connecté:', [
            'id' => $user->id,
            'company_id' => $user->company_id
        ]);
        \Log::info('Données de la requête:', $data);
        
        // Récupérer l'exercice actif
        $exerciceActif = ExerciceComptable::where('company_id', $user->company_id)
            ->where('cloturer', 0)
            ->orderBy('date_debut', 'desc')
            ->first();
            
        // Récupérer les écritures avec filtres
        $query = EcritureComptable::with(['planComptable', 'planTiers', 'compteTresorerie', 'codeJournal'])
            ->where('company_id', $user->company_id);
            
        // Appliquer les filtres si présents
        if (isset($data['numero_saisie']) && $data['numero_saisie']) {
            $query->where('n_saisie', 'like', '%' . $data['numero_saisie'] . '%');
        }
        if (isset($data['code_journal']) && $data['code_journal']) {
            $query->whereHas('codeJournal', function($q) use ($data) {
                $q->where('code_journal', 'like', '%' . $data['code_journal'] . '%');
            });
        }
        if (isset($data['mois']) && $data['mois']) {
            $query->whereMonth('date', $data['mois']);
        }
        
        $ecritures = $query->orderBy('created_at', 'desc')->get();
        
        // Log des résultats
        \Log::info('Résultats de la requête:', [
            'requete_sql' => $query->toSql(),
            'parametres' => $query->getBindings(),
            'nombre_ecritures' => $ecritures->count()
        ]);
        
        // Afficher directement les informations de débogage
        if ($ecritures->isEmpty()) {
            \Log::info('Aucune écriture trouvée pour les critères suivants:', [
                'company_id' => $user->company_id,
                'filtres' => $data
            ]);
            
            // Vérifier s'il y a des écritures dans la base de données
            $totalEcritures = \App\Models\EcritureComptable::count();
            $ecrituresCompany = \App\Models\EcritureComptable::where('company_id', $user->company_id)->count();
            
            \Log::info('Statistiques des écritures:', [
                'total_ecritures' => $totalEcritures,
                'ecritures_company' => $ecrituresCompany
            ]);
        }
        
        // Récupérer les journaux pour les filtres
        $code_journaux = CodeJournal::where('company_id', $user->company_id)->get();

        return view('accounting_entry_list', compact('ecritures', 'exerciceActif', 'code_journaux'));
    }
}