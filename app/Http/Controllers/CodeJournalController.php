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

    // La requête est maintenant automatiquement filtrée par TenantScope (Session current_company_id ou User company_id)
    $query = CodeJournal::orderByDesc('created_at');

    // FILTRAGE PAR RÔLE
    if ($user->role !== 'admin' && $user->role !== 'super_admin') {
        $query->where('user_id', $user->id);
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
    $code_journaux->map(function ($journal) use ($tresoreriesData, $planComptableAccounts) {
        $codeTresorerie = null;

        if ($tresoreriesData->has($journal->code_journal)) {
            $codeTresorerie = $tresoreriesData[$journal->code_journal]->compte_de_contrepartie;
        } elseif ($journal->compte_de_tresorerie && $planComptableAccounts->has($journal->compte_de_tresorerie)) {
            $compte = $planComptableAccounts[$journal->compte_de_tresorerie];
            $codeTresorerie = $compte->numero_de_compte ?? null;
        }

        $journal->code_tresorerie_display = $codeTresorerie;
        return $journal;
    });

    // 5. Calculs statistiques
    $allJournauxForStats = CodeJournal::get();
    $totalJournauxCompany = $allJournauxForStats->count();

    $userCreatedJournaux = CodeJournal::where('user_id', $user->id)
        ->count();

    // 6. Comptes de Trésorerie
    $comptesTresorerie = PlanComptable::where('numero_de_compte', 'like', '5%')
        ->get();


    // 7. On passe la variable ENRICHIE ($code_journaux) à la vue.
    // 🔑 FIX : Utilisez $code_journaux dans compact()
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
                ->first();

            if ($existing) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Ce code journal existe déjà pour votre entreprise.');
            }

            $intitule_formate = ucfirst(strtolower($request->intitule));
            $user = Auth::user();
            $currentCompanyId = session('current_company_id', $user->company_id);

            CodeJournal::create([
                'code_journal' => strtoupper($request->code_journal),
                'intitule' => $intitule_formate,
                'traitement_analytique' => $request->traitement_analytique === 'oui' ? 1 : 0,
                'type' => $request->type,
                'compte_de_contrepartie' => $request->compte_de_contrepartie,
                'compte_de_tresorerie' => $request->compte_de_tresorerie,
                'rapprochement_sur' => $request->rapprochement_sur,
                'user_id' => $user->id,
                'company_id' => $currentCompanyId,
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
