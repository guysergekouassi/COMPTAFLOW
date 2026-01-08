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

            // Récupérer le dernier numéro de saisie et incrémenter
        $lastSaisie = EcritureComptable::max('id');
        $nextSaisieNumber = str_pad(($lastSaisie ? $lastSaisie + 1 : 1), 12, '0', STR_PAD_LEFT);

        $activeCompanyId = session('switched_company_id', $user->company_id);
$query = EcritureComptable::where('company_id', $activeCompanyId)->orderBy('created_at', 'desc');
        // $query = EcritureComptable::where('company_id', $user->company_id)->orderBy('created_at', 'desc');
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
        
        // Récupérer le dernier numéro de saisie
        $lastSaisie = EcritureComptable::max('id');
        $nextSaisieNumber = str_pad(($lastSaisie ? $lastSaisie + 1 : 1), 12, '0', STR_PAD_LEFT);

        // Récupérer la clé API Gemini
        $apiKey = env('GEMINI_API_KEY');

        return view('accounting.scan', compact('plansComptables', 'plansTiers', 'data', 'nextSaisieNumber', 'apiKey'));
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
            
            // Récupérer la ligne spécifique pour obtenir le n_saisie
            $primaryEcriture = EcritureComptable::where('company_id', $user->company_id)->findOrFail($id);
            
            // Récupérer TOUTES les lignes de cette écriture (même n_saisie)
            $ecritures = EcritureComptable::with(['planComptable', 'planTiers', 'compteTresorerie', 'codeJournal'])
                ->where('company_id', $user->company_id)
                ->where('n_saisie', $primaryEcriture->n_saisie)
                ->orderBy('id', 'asc')
                ->get();
                
            return view('ecriture_show', compact('ecritures', 'primaryEcriture'));
            
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
            
           
            
            return view('accounting_entry_edit', compact('ecriture', 'plansComptables', 'plansTiers', 'comptesTresorerie', 'codeJournaux'));
            
        } catch (\Exception $e) {
           
            return redirect()->route('accounting_entry_list')
                ->with('error', 'Erreur lors de l\'ouverture du formulaire d\'édition : ' . $e->getMessage());
        }
    }

    /**
     * Enregistre une nouvelle écriture comptable.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function deleteBySaisie($n_saisie)
    {
        try {
            $user = Auth::user();
            $activeCompanyId = session('switched_company_id', $user->company_id); // AJOUTER CECI
            if (!$user) {
                return response()->json(['success' => false, 'message' => 'Utilisateur non authentifié.'], 401);
            }

            // Using Auth::user()->company_id for consistency with the rest of the controller
           $deleted = EcritureComptable::where('company_id', $activeCompanyId) // UTILISER CECI
        ->where('n_saisie', $n_saisie)
        ->delete();
            if ($deleted > 0) {
                return response()->json([
                    'success' => true,
                    'message' => "$deleted lignes supprimées avec succès."
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => "Aucune écriture trouvée pour le numéro $n_saisie (Entreprise ID: $user->company_id)."
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => "Erreur serveur : " . $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $user = Auth::user();
            $activeCompanyId = session('switched_company_id', $user->company_id);
           

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

            // Handle file upload
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

            $ecriture = EcritureComptable::create($data);

            return response()->json([
                'success' => true,
                'message' => 'Écriture ajoutée avec succès',
                'id' => $ecriture->id
            ]);

        } catch (\Exception $e) {
           
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'ajout : ' . $e->getMessage()
            ], 500);
        }
    }

    public function storeMultiple(Request $request)
{
    try {
        $user = Auth::user();

        // On récupère l'ID de la compagnie active (switchée ou par défaut)
        $activeCompanyId = session('switched_company_id', $user->company_id);
        
        $ecritures = $request->input('ecritures');
        
        if (empty($ecritures) || !is_array($ecritures)) {
            return response()->json(['success' => false, 'message' => 'Aucune écriture à enregistrer.'], 400);
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
                
                // CORRECTION ICI : Utiliser la variable $activeCompanyId
                'company_id' => $activeCompanyId, 
                
                'user_id' => $user->id,
                'piece_justificatif' => $data['piece_justificatif'] ?? null,
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
                $q->where('numero_de_compte', 'like', '6%')
                  ->orWhere('numero_de_compte', 'like', '7%');
            });
        } elseif ($flux === 'Investissement') {
            $query->where('numero_de_compte', 'like', '2%');
        } elseif ($flux === 'Financement') {
            $query->where('numero_de_compte', 'like', '1%');
        }

        $comptes = $query->orderBy('numero_de_compte')->get();
        return response()->json($comptes);
    }

    public function getNextSaisieNumber(Request $request)
    {
        try {
            $user = Auth::user();
            if (!$user) {
                return response()->json(['success' => false, 'message' => 'Non authentifié'], 401);
            }
          $activeCompanyId = session('switched_company_id', $user->company_id);  
           $lastSaisie = EcritureComptable::where('company_id', $activeCompanyId)
                ->select(DB::raw('MAX(CAST(n_saisie AS UNSIGNED)) as max_saisie'))
                ->first();

            $nextNumber = 1;
            if ($lastSaisie && $lastSaisie->max_saisie) {
                $nextNumber = (int)$lastSaisie->max_saisie + 1;
            }

            $formattedNextSaisie = str_pad($nextNumber, 12, '0', STR_PAD_LEFT);

            return response()->json([
                'success' => true, 
                'nextSaisieNumber' => $formattedNextSaisie
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false, 
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function list(Request $request)
{
    $user = Auth::user();
    $data = $request->all();
    
    // 1. Définir l'ID de la compagnie une bonne fois pour toutes
    $activeCompanyId = session('switched_company_id', $user->company_id);
    
    // Récupérer l'exercice actif pour la compagnie SWITCHÉE
    $exerciceActif = ExerciceComptable::where('company_id', $activeCompanyId) // CORRIGÉ
        ->where('cloturer', 0)
        ->orderBy('date_debut', 'desc')
        ->first();
        
    // Construire la base de la requête avec filtres sur la compagnie SWITCHÉE
    $baseQuery = EcritureComptable::where('company_id', $activeCompanyId); // DÉJÀ BON DANS VOTRE CODE
    
    if (isset($data['numero_saisie']) && $data['numero_saisie']) {
        $baseQuery->where('n_saisie', 'like', '%' . $data['numero_saisie'] . '%');
    }
    if (isset($data['code_journal']) && $data['code_journal']) {
        $baseQuery->whereHas('codeJournal', function($q) use ($data) {
            $q->where('code_journal', 'like', '%' . $data['code_journal'] . '%');
        });
    }
    if (isset($data['mois']) && $data['mois']) {
        $baseQuery->whereMonth('date', $data['mois']);
    }

    // 2. Paginer sur les NUMÉROS DE SAISIE UNIQUES
    $paginatedSaisies = (clone $baseQuery)
        ->select('n_saisie', DB::raw('MAX(created_at) as latest_created_at'))
        ->groupBy('n_saisie')
        ->orderBy('latest_created_at', 'desc')
        ->paginate(5);
        
    // 3. Récupérer TOUTES les lignes (C'est ici que ça bloquait)
    $saisieList = $paginatedSaisies->pluck('n_saisie')->toArray();
    $ecritures = EcritureComptable::with(['planComptable', 'planTiers', 'compteTresorerie', 'codeJournal'])
        ->where('company_id', $activeCompanyId) // CORRIGÉ : était $user->company_id
        ->whereIn('n_saisie', $saisieList)
        ->orderBy('created_at', 'desc')
        ->orderBy('id', 'asc')
        ->get();
        
    // 4. Débogage interne si vide
    if ($ecritures->isEmpty()) {
        $ecrituresCompany = \App\Models\EcritureComptable::where('company_id', $activeCompanyId)->count(); // CORRIGÉ
    }
    
    // Récupérer les journaux pour les filtres de la compagnie SWITCHÉE
    $code_journaux = CodeJournal::where('company_id', $activeCompanyId)->get(); // CORRIGÉ

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