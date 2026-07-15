<?php

namespace App\Http\Controllers\Analytique;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AxeAnalytique;
use Illuminate\Support\Facades\Session;

class AxeAnalytiqueController extends Controller
{
    public function index()
    {
        $companyId = Session::get('current_company_id') ?? auth()->user()->company_id;
        $axes = AxeAnalytique::where('company_id', $companyId)->get();
        
        // Obtenir tous les types d'axes existants distincts pour cette entreprise
        $existingTypes = AxeAnalytique::where('company_id', $companyId)
            ->whereNotNull('type')
            ->distinct()
            ->pluck('type')
            ->toArray();
            
        $defaultTypes = ['Projet', 'Département', 'Agence', 'Divers'];
        
        // Uniformiser la casse (Première lettre en majuscule)
        $existingTypesClean = array_map(function($t) {
            return ucfirst(strtolower(trim($t)));
        }, $existingTypes);
        
        $types = array_values(array_unique(array_filter(array_merge($defaultTypes, $existingTypesClean))));
        
        $totalAxes = $axes->count();
        $totalSections = \App\Models\SectionAnalytique::where('company_id', $companyId)->count();

        return view('analytique.axes.index', compact('axes', 'totalAxes', 'totalSections', 'types'));
    }

    public function store(Request $request)
    {
        $companyId = Session::get('current_company_id') ?? auth()->user()->company_id;

        $request->validate([
            'code' => 'required|string|max:20',
            'libelle' => 'required|string|max:255',
            'type' => 'nullable|string',
            'custom_type' => 'nullable|string|max:100'
        ]);

        // Check uniqueness for this company
        $exists = AxeAnalytique::where('company_id', $companyId)
            ->where('code', $request->code)
            ->exists();

        if ($exists) {
            return redirect()->back()->with('error', 'Un axe avec ce code existe déjà.');
        }

        $type = $request->type;
        if ($type === 'custom' && $request->filled('custom_type')) {
            $type = $request->custom_type;
        }

        AxeAnalytique::create([
            'code' => $request->code,
            'libelle' => $request->libelle,
            'type' => $type ?? 'Divers',
            'company_id' => $companyId
        ]);

        return redirect()->route('analytique.axes.index')->with('success', 'Axe analytique créé avec succès.');
    }

    public function update(Request $request, $id)
    {
        $axe = AxeAnalytique::findOrFail($id);
        $companyId = Session::get('current_company_id') ?? auth()->user()->company_id;

        $request->validate([
            'code' => 'required|string|max:20',
            'libelle' => 'required|string|max:255',
            'type' => 'nullable|string',
            'custom_type' => 'nullable|string|max:100'
        ]);

        // Check uniqueness excluding current
        $exists = AxeAnalytique::where('company_id', $companyId)
            ->where('code', $request->code)
            ->where('id', '!=', $id)
            ->exists();

        if ($exists) {
            return redirect()->back()->with('error', 'Un axe avec ce code existe déjà.');
        }

        $type = $request->type;
        if ($type === 'custom' && $request->filled('custom_type')) {
            $type = $request->custom_type;
        }

        $axe->update([
            'code' => $request->code,
            'libelle' => $request->libelle,
            'type' => $type ?? 'Divers'
        ]);

        return redirect()->route('analytique.axes.index')->with('success', 'Axe analytique mis à jour.');
    }

    public function destroy($id)
    {
        $axe = AxeAnalytique::findOrFail($id);
        
        $hasSections = \App\Models\SectionAnalytique::where('axe_id', $id)->exists();
        if ($hasSections) {
            return redirect()->back()->with('error', 'Cet axe ne peut pas être supprimé car il contient des sections analytiques.');
        }

        $axe->delete();

        return redirect()->route('analytique.axes.index')->with('success', 'Axe analytique supprimé.');
    }

    public function getSectionsByAxe($id)
    {
        $sections = \App\Models\SectionAnalytique::where('axe_id', $id)
            ->orderBy('code')
            ->get(['id', 'code', 'libelle']);

        return response()->json($sections);
    }

    public function apiList()
    {
        $companyId = Session::get('current_company_id') ?? auth()->user()->company_id;
        $axes = AxeAnalytique::where('company_id', $companyId)
            ->with(['sections' => function($q) use ($companyId) {
                $q->where('company_id', $companyId);
            }])
            ->get()
            ->map(function($axe) {
                return [
                    'id' => $axe->id,
                    'libelle' => $axe->libelle,
                    'sections' => $axe->sections->map(function($s) {
                        return [
                            'id' => $s->id,
                            'code' => $s->code,
                            'libelle' => $s->libelle
                        ];
                    })
                ];
            });
            
        return response()->json($axes);
    }
}
