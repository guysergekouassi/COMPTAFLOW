<?php

namespace App\Http\Controllers;

use App\Models\CodeJournal;
use App\Models\PlanComptable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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

        // 1. DÉTERMINATION DES IDs DE COMPAGNIE À VISUALISER
        if ($user->role === 'admin') {
            // L'Admin voit toutes les compagnies gérées (mère + enfants)
            $companyIdsToView = $this->getManagedCompanyIds();
        } else {
            // Un comptable/utilisateur voit les données de sa propre compagnie
            $companyIdsToView = [$currentCompanyId];
        }

        // 2. Requête de base pour la collection principale de la vue
        $query = CodeJournal::whereIn('company_id', $companyIdsToView) // 🔑 CORRECTION : Utilisation de whereIn
            ->orderByDesc('created_at');

        // 3. FILTRAGE PAR RÔLE (Ajuster si vous voulez que les comptables voient TOUS les journaux de leur compagnie)
        if ($user->role !== 'admin' && $user->role !== 'super_admin') {
            // Si l'utilisateur n'est ni admin ni super_admin, il ne voit que ses créations
            // Si votre comptable doit voir tous les journaux de sa compagnie, supprimez cette ligne.
            $query->where('user_id', $user->id);
        }

        // 4. Exécution de la requête pour la vue principale (la liste)
        $codeJournaux = $query->get();

        // 5. Calculs statistiques
        // Pour les stats, nous comptons sur toute la portée de visibilité.
        $allJournauxForStats = CodeJournal::whereIn('company_id', $companyIdsToView)->get();

        $totalJournauxCompany = $allJournauxForStats->count();

        // Le nombre de journaux créés par ce user (compter uniquement dans sa compagnie actuelle est plus logique)
        $userCreatedJournaux = CodeJournal::where('company_id', $currentCompanyId)
                                            ->where('user_id', $user->id)
                                            ->count();

        // 6. Comptes de Trésorerie
        // Cette requête doit se baser sur la compagnie actuelle (celle de l'utilisateur)
        $comptesTresorerie = PlanComptable::where('company_id', $currentCompanyId)
            ->where('numero_de_compte', 'like', '5%')
            ->get();


        // On passe $codeJournaux pour que la boucle @foreach fonctionne
        return view('accounting_journals', compact('codeJournaux', 'totalJournauxCompany', 'userCreatedJournaux', 'comptesTresorerie'));
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
