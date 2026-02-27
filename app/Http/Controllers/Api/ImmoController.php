<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Immobilisation;
use App\Models\Amortissement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ImmoController extends Controller
{
    public function index(Request $request)
    {
        $companyId = $request->header('X-Company-Id', Auth::user()->company_id);
        $exerciceId = $request->header('X-Exercice-Id');

        $query = Immobilisation::where('company_id', $companyId);

        if ($exerciceId) {
            $query->where('exercice_id', $exerciceId);
        }

        return response()->json($query->with(['compteImmo', 'compteAmort'])->orderBy('date_acquisition', 'desc')->get());
    }

    public function show($id)
    {
        $companyId = request()->header('X-Company-Id', Auth::user()->company_id);
        $immo = Immobilisation::where('company_id', $companyId)->with(['compteImmo', 'compteAmort'])->findOrFail($id);
        return response()->json($immo);
    }

    /**
     * Retourne le plan d'amortissement pour une immobilisation.
     */
    public function amortissementIndex(Request $request, $id)
    {
        $companyId = $request->header('X-Company-Id', Auth::user()->company_id);
        
        // Sécurité : Vérifier que l'immo appartient à la compagnie
        $immo = Immobilisation::where('company_id', $companyId)->findOrFail($id);
        
        $amortissements = Amortissement::where('immobilisation_id', $immo->id)
            ->orderBy('annee', 'asc')
            ->get();
            
        return response()->json($amortissements);
    }
}
