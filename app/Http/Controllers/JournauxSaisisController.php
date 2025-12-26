<?php

namespace App\Http\Controllers;

use App\Models\JournalSaisi;
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

        $companyId = Auth::user()->company_id;
        $data = $request->all();

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
                ['company_id', '=', Auth::user()->company_id]
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
