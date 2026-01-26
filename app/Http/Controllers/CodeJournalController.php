<?php

namespace App\Http\Controllers;

use App\Models\CodeJournal;
use App\Models\PlanComptable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\tresoreries\Tresoreries;
use App\Traits\ManagesCompany;
class CodeJournalController extends Controller
{
    use ManagesCompany;

public function index(Request $request)
{
    $user = Auth::user();

    // La requête est maintenant automatiquement filtrée par TenantScope (Session current_company_id ou User company_id)
    $query = CodeJournal::orderByDesc('created_at');

    // FILTRAGE PAR TYPE (depuis les cartes KPI)
    if ($request->has('type') && $request->type !== 'all') {
        if ($request->type === 'Ventes') {
            $query->whereIn('type', ['Achats', 'Ventes']);
        } else {
            $query->where('type', $request->type);
        }
    }

    // FILTRAGE PAR CODE JOURNAL
    if ($request->has('code') && !empty($request->code)) {
        $query->where('code_journal', 'LIKE', '%' . $request->code . '%');
    }

    // FILTRAGE PAR INTITULE
    if ($request->has('intitule') && !empty($request->intitule)) {
        $query->where('intitule', 'LIKE', '%' . $request->intitule . '%');
    }

    $code_journaux = $query->get();


    // =============================================================
    // LOGIQUE POUR ENRICHIR LES CODES JOURNAUX AVEC LA TRÉSORERIE
    // =============================================================

    // A. Récupérer les données de Trésorerie (filtrées par scope)
    $tresoreriesData = Tresoreries::get()
        ->keyBy('code_journal');

    // B. Récupérer les comptes du Plan Comptable
    // NOTE : On utilise la collection $code_journaux ici.
    $planComptableIds = $code_journaux->pluck('compte_de_tresorerie')->filter()->unique();
    $planComptableAccounts = PlanComptable::whereIn('id', $planComptableIds)
        ->get()
        ->keyBy('id');

    // C. Parcourir et enrichir la collection $code_journaux
    $code_journaux->transform(function ($journal) use ($tresoreriesData, $planComptableAccounts) {
        $codeTresorerie = null;
        $posteTresorerie = null;

        // Priorité 1: Vérifier si c'est un journal de trésorerie dans la table tresoreries
        if ($tresoreriesData->has($journal->code_journal)) {
            $tresorerieData = $tresoreriesData[$journal->code_journal];
            $codeTresorerie = $tresorerieData->compte_de_contrepartie;
            $posteTresorerie = $tresorerieData->poste_tresorerie;
        }
        // Priorité 2: Sinon, utiliser les données de la table code_journals
        else {
            if ($journal->compte_de_tresorerie && $planComptableAccounts->has($journal->compte_de_tresorerie)) {
                $compte = $planComptableAccounts[$journal->compte_de_tresorerie];
                $codeTresorerie = $compte->numero_de_compte ?? null;
            }
            // Utiliser le poste de trésorerie de la table code_journals
            $posteTresorerie = $journal->poste_tresorerie;
        }

        $journal->code_tresorerie_display = $codeTresorerie;
        $journal->poste_tresorerie_display = $posteTresorerie;
        
        return $journal;
    });

    // 5. Calculs statistiques pour les cartes (requêtes séparées pour éviter les conflits de pagination)
    $stats = [
        'total' => CodeJournal::where('company_id', $this->getCurrentCompanyId())->count(),
        'tresorerie' => CodeJournal::where('company_id', $this->getCurrentCompanyId())->where('type', 'Tresorerie')->count(),
        'achatsVentes' => CodeJournal::where('company_id', $this->getCurrentCompanyId())->whereIn('type', ['Achats', 'Ventes'])->count(),
    ];

    // 6. Regrouper les journaux par type pour affichage dynamique des cartes
    $journauxParType = CodeJournal::where('company_id', $this->getCurrentCompanyId())
        ->get()
        ->groupBy('type')
        ->map(fn($group) => $group->count());
    $allJournauxForStats = CodeJournal::get();
    $totalJournauxCompany = $allJournauxForStats->count();

    $userCreatedJournaux = CodeJournal::where('user_id', $user->id)
        ->count();

    // 6. Comptes de Trésorerie
    $comptesTresorerie = PlanComptable::where('numero_de_compte', 'like', '5%')
        ->get();


    // Récupérer les comptes commençant par 5 (comptes de trésorerie)
    $comptesCinq = PlanComptable::where('numero_de_compte', 'like', '5%')
        ->where('company_id', $this->getCurrentCompanyId())
        ->get();

    // 7. Récupérer les postes de trésorerie distincts (uniquement les postes créés)
    $postesTresorerieData = \App\Models\tresoreries\Tresoreries::distinct()
        ->whereNotNull('poste_tresorerie')
        ->where('poste_tresorerie', '!=', '')
        ->where('company_id', $this->getCurrentCompanyId())
        ->pluck('categorie', 'poste_tresorerie');

    // 8. On passe les variables à la vue
        return view('accounting_journals', compact(
            'code_journaux', 
            'totalJournauxCompany', 
            'userCreatedJournaux', 
            'comptesTresorerie',
            'comptesCinq',
            'postesTresorerieData',
            'stats',
            'journauxParType'
        ));
}

    public function store(Request $request)
    {
        $request->validate([
            'code_journal' => ['required', 'string', 'max:4', 'regex:/^[A-Z0-9]{1,4}$/'],
            'intitule' => 'required|string|max:255',
            'traitement_analytique' => 'required|in:oui,non',
            'type' => 'nullable|string',
            'compte_de_contrepartie' => 'nullable|string',
            'rapprochement_sur' => 'nullable|in:Manuel,Automatique',
            'poste_tresorerie' => 'nullable|string',
        ], [
            'code_journal.regex' => 'Le code journal doit contenir entre 1 et 4 caractères alphanumériques en majuscules',
            'code_journal.max' => 'Le code journal ne peut pas dépasser 4 caractères'
        ]);

        try {
            $existing = CodeJournal::where('code_journal', strtoupper($request->code_journal))
                ->first();

            if ($existing) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ce code journal existe déjà pour votre entreprise.'
                ], 422);
            }

            $intitule_formate = ucfirst(strtolower($request->intitule));
            $user = Auth::user();
            $currentCompanyId = session('current_company_id', $user->company_id);

            $compteId = null;
            if ($request->compte_de_contrepartie) {
                $compteId = PlanComptable::where('company_id', $currentCompanyId)
                    ->where('numero_de_compte', $request->compte_de_contrepartie)
                    ->value('id');
            }

            $journal = CodeJournal::create([
                'code_journal' => strtoupper($request->code_journal),
                'intitule' => $intitule_formate,
                'traitement_analytique' => $request->traitement_analytique === 'oui' ? 1 : 0,
                'type' => $request->type,
                'compte_de_tresorerie' => $compteId,
                'compte_de_contrepartie' => $request->compte_de_contrepartie,
                'rapprochement_sur' => $request->rapprochement_sur,
                'poste_tresorerie' => $request->poste_tresorerie,
                'user_id' => $user->id,
                'company_id' => $currentCompanyId,
            ]);

            // Si c'est un journal de trésorerie, créer une entrée dans la table tresoreries
            if ($request->type === 'Tresorerie') {
                $categorie = Tresoreries::where('poste_tresorerie', $request->poste_tresorerie)
                    ->whereNotNull('categorie')
                    ->value('categorie');

                Tresoreries::create([
                    'code_journal' => strtoupper($request->code_journal),
                    'intitule' => $intitule_formate,
                    'compte_de_contrepartie' => $request->compte_de_contrepartie,
                    'poste_tresorerie' => $request->poste_tresorerie,
                    'categorie' => $categorie,
                    'user_id' => $user->id,
                    'company_id' => $currentCompanyId,
                ]);
            }

            // Pour l'affichage AJAX sans rechargement
            $journal->code_tresorerie_display = $journal->compte_de_contrepartie; 

            return response()->json([
                'success' => true,
                'message' => 'Code journal enregistré avec succès',
                'journal' => $journal
            ]);
        } catch (\Exception $e) {
            \Log::error('Erreur store journal: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Une erreur est survenue : ' . $e->getMessage()
            ], 500);
        }
    }



    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'code_journal' => ['required', 'string', 'max:4', 'regex:/^[A-Z0-9]{1,4}$/'],
            'intitule' => 'required|string|max:255',
            'traitement_analytique' => 'nullable|in:0,1',
            'type' => 'nullable|string',
            'compte_de_contrepartie' => 'nullable|string',
            'compte_de_tresorerie' => 'nullable|exists:plan_comptables,id',
            'poste_tresorerie' => 'nullable|string',
            'rapprochement_sur' => 'nullable|in:Manuel,Automatique',
        ], [
            'code_journal.regex' => 'Le code journal doit contenir entre 1 et 4 caractères alphanumériques en majuscules',
            'code_journal.max' => 'Le code journal ne peut pas dépasser 4 caractères'
        ]);

        try {
            $validated['intitule'] = ucfirst(strtolower($validated['intitule']));

            $journal = CodeJournal::findOrFail($id);
            $journal->update($validated);

            // Si c'est un journal de trésorerie, créer/mettre à jour l'entrée dans la table tresoreries
            if ($journal->type === 'Tresorerie') {
                $tresorerie = Tresoreries::where('code_journal', $journal->code_journal)->first();
                
                $data = [
                    'code_journal' => $journal->code_journal,
                    'intitule' => $journal->intitule,
                    'compte_de_contrepartie' => $validated['compte_de_contrepartie'] ?? null,
                    'poste_tresorerie' => $validated['poste_tresorerie'] ?? null,
                    'company_id' => $this->getCurrentCompanyId(),
                ];
                
                if ($tresorerie) {
                    $tresorerie->update($data);
                } else {
                    Tresoreries::create($data);
                }
            }

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Code journal mis à jour avec succès.'
                ]);
            }

            return redirect()->back()->with('success', 'Code journal mis à jour avec succès.');
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Une erreur est survenue : ' . $e->getMessage()
                ], 500);
            }
            return redirect()->back()->withInput()->with('error', 'Une erreur est survenue : ' . $e->getMessage());
        }
    }





    public function destroy($id)
    {
        try {
            $journal = CodeJournal::findOrFail($id);

            $utilise = \App\Models\EcritureComptable::where('code_journal_id', $id)->exists();

            if ($utilise) {
                return redirect()->back()->with('error', 'Ce Code journal est utilisé dans des écritures comptables et ne peut pas être supprimé.');
            }

            $journal->delete();

            return redirect()->back()->with('success', 'Code journal supprimé avec succès.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Une erreur est survenue lors de la suppression : ' . $e->getMessage());
        }
    }


}
