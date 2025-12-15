<?php

namespace App\Http\Controllers;
use App\Models\JournalSaisi;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class JournauxSaisisController extends Controller
{

    public function index(Request $request)
    {
        $user = Auth::user();

        $data = $request->all();

        $companyId = Auth::user()->company_id;
        $idExercice = $data['id_exercice'];

        // Récupérer les journaux saisis liés à l'exercice et à la compagnie
        $journaux = JournalSaisi::with('codeJournal')
        ->where('exercices_comptables_id', $request->id_exercice)
        ->where('company_id', Auth::user()->company_id)
        ->get();

        return view('journaux_saisis', compact('data', 'journaux'));
    }
//     public function index(Request $request)
// {
//     $user = Auth::user();
//     $companyId = $user->company_id;
//     $idExercice = $request->input('id_exercice');

//     // Démarre la requête avec les filtres de base
//     $query = JournalSaisi::with('codeJournal')
//         ->where('company_id', $companyId);

//     // 💡 Ajoutez la logique de filtrage par utilisateur/administrateur
//     $query->currentUser(); // Utilisez le scope défini précédemment

//     // Applique le filtre d'exercice UNIQUEMENT s'il est fourni
//     if ($idExercice) {
//         $query->where('exercices_comptables_id', $idExercice);
//     }

//     // Récupère les données
//     $journaux = $query->get();

//     return view('journaux_saisis', compact('journaux'));
// }

    public function find(Request $request)
    {
        $journal = JournalSaisi::where('annee', $request->annee)
            ->where('mois', $request->mois)
            ->where('exercices_comptables_id', $request->exercice_id)
            ->where('code_journals_id', $request->code_journal_id)
            ->where('company_id', auth()->user()->company_id)
            ->first();

        if ($journal) {
            return response()->json([
                'success' => true,
                'id' => $journal->id
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Aucun journal trouvé'
        ], 404);
    }
}
