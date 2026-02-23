<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AxeAnalytique;
use App\Models\SectionAnalytique;
use App\Models\VentilationAnalytique;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AnalytiqueController extends Controller
{
    public function axeIndex(Request $request)
    {
        $companyId = $request->header('X-Company-Id', Auth::user()->company_id);
        $axes = AxeAnalytique::where('company_id', $companyId)->get();
        return response()->json($axes);
    }

    public function axeStore(Request $request)
    {
        $companyId = $request->header('X-Company-Id', Auth::user()->company_id);

        $request->validate([
            'code' => 'required|string|max:20',
            'libelle' => 'required|string|max:255',
            'type' => 'nullable|string'
        ]);

        if (AxeAnalytique::where('company_id', $companyId)->where('code', $request->code)->exists()) {
            return response()->json(['message' => 'Code déjà utilisé'], 422);
        }

        $axe = AxeAnalytique::create([
            'code' => $request->code,
            'libelle' => $request->libelle,
            'type' => $request->type ?? 'divers',
            'company_id' => $companyId
        ]);

        return response()->json($axe);
    }

    public function sectionIndex(Request $request)
    {
        $companyId = $request->header('X-Company-Id', Auth::user()->company_id);
        $query = SectionAnalytique::where('company_id', $companyId)->with('axe');

        if ($request->has('axe_id')) {
            $query->where('axe_id', $request->axe_id);
        }

        return response()->json($query->get());
    }

    public function sectionStore(Request $request)
    {
        $companyId = $request->header('X-Company-Id', Auth::user()->company_id);

        $request->validate([
            'axe_id' => 'required|exists:axes_analytiques,id',
            'code' => 'required|string|max:20',
            'libelle' => 'required|string|max:255'
        ]);

        if (SectionAnalytique::where('company_id', $companyId)->where('axe_id', $request->axe_id)->where('code', $request->code)->exists()) {
            return response()->json(['message' => 'Code déjà utilisé pour cet axe'], 422);
        }

        $section = SectionAnalytique::create([
            'axe_id' => $request->axe_id,
            'code' => $request->code,
            'libelle' => $request->libelle,
            'company_id' => $companyId
        ]);

        return response()->json($section);
    }
    
    public function ventilationIndex(Request $request)
    {
        $request->validate(['ecriture_id' => 'required|exists:ecriture_comptables,id']);
        
        $ventilations = VentilationAnalytique::where('ecriture_id', $request->ecriture_id)
            ->with(['section.axe'])
            ->get();
            
        return response()->json($ventilations);
    }
}
