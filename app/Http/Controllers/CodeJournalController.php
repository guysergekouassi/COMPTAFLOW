<?php

namespace App\Http\Controllers;

use App\Models\CodeJournal;
use App\Models\PlanComptable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\tresoreries\Tresoreries;
use App\Traits\ManagesCompany;
class CodeJournalController extends Controller
{
    //
use ManagesCompany;

// Dans votre CodeJournalController ou similaire...

// Dans votre CodeJournalController ou similaire...

public function index()
{
    $user = Auth::user();
    $currentCompanyId = $user->company_id;

     // --- BLOC DE TEST : Ceci doit arrêter l'exécution et afficher des données ---
    // Récupérer une seule entrée de trésorerie pour le test
    $tresorerieTest = \App\Models\tresoreries\Tresoreries::where('company_id', $currentCompanyId)->first();

    // Si $tresorerieTest est null, c'est que la compagnie n'a pas de données de trésorerie.
    // Sinon, dd() affichera l'objet Tresoreries.

    // 🛑 VÉRIFIEZ LE RÉSULTAT DE CE DD() 🛑
    dd($tresorerieTest ? $tresorerieTest->toArray() : 'AUCUNE ENTREE DE TRESORERIE TROUVÉE POUR CETTE COMPAGNIE');

    // --- FIN DU BLOC DE TEST : Le code ci-dessous doit être ignoré ---
    // 1. DÉTERMINATION DES IDs DE COMPAGNIE À VISUALISER
    if ($user->role === 'admin') {
        $companyIdsToView = $this->getManagedCompanyIds();
    } else {
        $companyIdsToView = [$currentCompanyId];
    }

    // 2. Requête de base pour la collection principale
    $query = CodeJournal::whereIn('company_id', $companyIdsToView)
        ->orderByDesc('created_at');

    // 3. FILTRAGE PAR RÔLE
    if ($user->role !== 'admin' && $user->role !== 'super_admin') {
        $query->where('user_id', $user->id);
    }

    // 4. Exécution de la requête pour obtenir la collection de journaux
    $code_journaux = $query->get();


    // =============================================================
    // LOGIQUE POUR ENRICHIR LES CODES JOURNAUX AVEC LA TRÉSORERIE
    // =============================================================

    // A. Récupérer les données de Trésorerie
    $tresoreriesData = Tresoreries::whereIn('company_id', $companyIdsToView)
        ->get()
        // 🔑 CORRECTION CRUCIALE : Mettre la clé en MAJUSCULES
        ->keyBy(function ($item) {
            return strtoupper($item->code_journal);
        });

    // B. Récupérer les comptes du Plan Comptable
    $planComptableIds = $code_journaux->pluck('compte_de_tresorerie')->filter()->unique();
    $planComptableAccounts = PlanComptable::whereIn('id', $planComptableIds)
        ->get()
        ->keyBy('id');

    // C. Parcourir et enrichir la collection $code_journaux
    $code_journaux->map(function ($journal) use ($tresoreriesData, $planComptableAccounts) {
        $codeTresorerie = null;

        // Mettre le code journal en MAJUSCULES pour la comparaison
        $journalCodeUpper = strtoupper($journal->code_journal);

        // PRIORITÉ 1 : Le journal est un journal de Trésorerie (lien direct par code_journal)
        // 🔑 CORRECTION CRUCIALE : Utiliser le code en MAJUSCULES pour vérifier l'existence
        if ($tresoreriesData->has($journalCodeUpper)) {
            $codeTresorerie = $tresoreriesData[$journalCodeUpper]->compte_de_contrepartie;
        }

        // PRIORITÉ 2 : Le journal est lié via l'ID de Plan Comptable
        elseif ($journal->compte_de_tresorerie && $planComptableAccounts->has($journal->compte_de_tresorerie)) {
            $compte = $planComptableAccounts[$journal->compte_de_tresorerie];
            $codeTresorerie = $compte->numero_de_compte ?? null;
        }

        $journal->code_tresorerie_display = $codeTresorerie;
        return $journal;
    });

    // 5. Calculs statistiques (le reste de votre code)
    $allJournauxForStats = CodeJournal::whereIn('company_id', $companyIdsToView)->get();
    $totalJournauxCompany = $allJournauxForStats->count();

    $userCreatedJournaux = CodeJournal::where('company_id', $currentCompanyId)
        ->where('user_id', $user->id)
        ->count();

    // 6. Comptes de Trésorerie
    $comptesTresorerie = PlanComptable::where('company_id', $currentCompanyId)
        ->where('numero_de_compte', 'like', '5%')
        ->get();


    // 7. On passe la variable ENRICHIE ($code_journaux) à la vue.
    return view('accounting_journals', compact('code_journaux', 'totalJournauxCompany', 'userCreatedJournaux', 'comptesTresorerie'));
}











    public function store(Request $request)
    {
        $request->validate([
            'code_journal' => 'required|string|max:50',
            'intitule' => 'required|string|max:255',
            'traitement_analytique' => 'required|in:oui,non',
            'type' => 'nullable|string',
            'compte_de_contrepartie' => 'nullable|string',
            'compte_de_tresorerie' => 'nullable|exists:plan_comptables,id',
            'rapprochement_sur' => 'nullable|string|in:Contrepartie,tresorerie',
        ]);

        try {
            $existing = CodeJournal::where('code_journal', strtoupper($request->code_journal))
                ->where('company_id', Auth::user()->company_id)
                ->first();

            if ($existing) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Ce code journal existe déjà pour votre entreprise.');
            }

            $intitule_formate = ucfirst(strtolower($request->intitule));

            CodeJournal::create([
                'code_journal' => strtoupper($request->code_journal),
                'intitule' => $intitule_formate,
                'traitement_analytique' => $request->traitement_analytique === 'oui' ? 1 : 0,
                'type' => $request->type,
                'compte_de_contrepartie' => $request->compte_de_contrepartie,
                'compte_de_tresorerie' => $request->compte_de_tresorerie,
                'rapprochement_sur' => $request->rapprochement_sur,
                'user_id' => Auth::id(),
                'company_id' => Auth::user()->company_id,
            ]);

            return redirect()->back()->with('success', 'Code journal créé avec succès.');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Une erreur s\'est produite : ' . $e->getMessage());
        }
    }



    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'code_journal' => 'required|string|max:50',
            'intitule' => 'required|string|max:255',
            'traitement_analytique' => 'nullable|in:0,1',
            'type' => 'nullable|string',
            'compte_de_contrepartie' => 'nullable|string',
            'compte_de_tresorerie' => 'nullable|exists:plan_comptables,id',
            'rapprochement_sur' => 'nullable|string|in:Contrepartie,tresorerie',
        ]);

        try {
            $validated['intitule'] = ucfirst(strtolower($validated['intitule']));

            $journal = CodeJournal::findOrFail($id);
            $journal->update($validated);

            return redirect()->back()->with('success', 'Code journal mis à jour avec succès.');
        } catch (\Exception $e) {
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
