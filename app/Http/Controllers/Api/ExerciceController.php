<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ExerciceComptable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ExerciceController extends Controller
{
    public function index(Request $request)
    {
        $companyId = $request->header('X-Company-Id', Auth::user()->company_id);
        $exercices = ExerciceComptable::where('company_id', $companyId)
            ->orderBy('annee', 'desc')
            ->get();
        return response()->json($exercices);
    }

    public function showActive(Request $request)
    {
        $companyId = $request->header('X-Company-Id', Auth::user()->company_id);
        $exercice = ExerciceComptable::where('company_id', $companyId)
            ->where('is_active', 1)
            ->first();
        return response()->json($exercice);
    }

    public function getByEmail($email)
    {
        $user = \App\Models\User::where('email_adresse', $email)->first();

        if (!$user) {
            return response()->json(['message' => 'Utilisateur non trouvé'], 404);
        }

        $exercices = ExerciceComptable::where('company_id', $user->company_id)
            ->orderBy('date_debut', 'desc')
            ->get(['id', 'intitule', 'date_debut']);

        return response()->json($exercices);
    }
}
