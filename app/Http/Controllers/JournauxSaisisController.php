<?php

namespace App\Http\Controllers;

use App\Models\JournalSaisi;
use App\Models\ExerciceComptable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class JournauxSaisisController extends Controller
{
    /**
     * Affiche la liste des journaux saisis pour un exercice donné.
     */
    public function index(Request $request)
    {
        // Validation minimale pour éviter les erreurs 500 si id_exercice manque
        $request->validate([
            'id_exercice' => 'required|exists:exercices_comptables,id'
        ]);

        // $companyId = Auth::user()->company_id;
        $companyId = session('current_company_id', Auth::user()->company_id);
        $data = $request->all();

        // Récupérer l'exercice sélectionné
        $exercice = ExerciceComptable::findOrFail($request->id_exercice);
        
        // Ajouter les informations de l'exercice aux données
        $data['date_debut'] = \Carbon\Carbon::parse($exercice->date_debut)->format('d/m/Y');
        $data['date_fin'] = \Carbon\Carbon::parse($exercice->date_fin)->format('d/m/Y');
        $data['intitule'] = $exercice->intitule;

        // Récupérer les journaux saisis avec relation
        $journaux = JournalSaisi::with('codeJournal')
            ->where('exercices_comptables_id', $request->id_exercice)
            ->where('company_id', $companyId)
            ->get();

        return view('journaux_saisis', compact('data', 'journaux'));
    }

    /**
     * Trouve un journal spécifique via AJAX.
     */
    public function find(Request $request)
    {
        // Sécurité : Validation des entrées
        if (!$request->has(['annee', 'mois', 'exercice_id', 'code_journal_id'])) {
            return response()->json([
                'success' => false,
                'message' => 'Paramètres manquants'
            ], 400);
        }

        $journal = JournalSaisi::where([
                ['annee', '=', $request->annee],
                ['mois', '=', $request->mois],
                ['exercices_comptables_id', '=', $request->exercice_id],
                ['code_journals_id', '=', $request->code_journal_id],
                // ['company_id', '=', Auth::user()->company_id]
                ['company_id', '=', session('current_company_id', Auth::user()->company_id)]
            ])
            ->select('id') // On ne récupère que l'ID pour optimiser la mémoire
            ->first();

        if ($journal) {
            return response()->json([
                'success' => true,
                'id' => $journal->id
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Aucun journal trouvé pour cette période'
        ], 404);
    }
}
