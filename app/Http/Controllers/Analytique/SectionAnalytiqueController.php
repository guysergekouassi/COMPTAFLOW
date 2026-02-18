<?php

namespace App\Http\Controllers\Analytique;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SectionAnalytique;
use App\Models\AxeAnalytique;
use Illuminate\Support\Facades\Session;

class SectionAnalytiqueController extends Controller
{
    public function index(Request $request)
    {
        $companyId = Session::get('current_company_id') ?? auth()->user()->company_id;
        
        $query = SectionAnalytique::where('company_id', $companyId)->with('axe');
        
        if ($request->has('axe_id') && $request->axe_id != '') {
            $query->where('axe_id', $request->axe_id);
        }
        
        $sections = $query->get();
        $axes = AxeAnalytique::where('company_id', $companyId)->get();
        
        $totalSections = $sections->count();

        return view('analytique.sections.index', compact('sections', 'axes', 'totalSections'));
    }

    public function store(Request $request)
    {
        $companyId = Session::get('current_company_id') ?? auth()->user()->company_id;

        $request->validate([
            'axe_id' => 'required|exists:axes_analytiques,id',
            'code' => 'required|string|max:20',
            'libelle' => 'required|string|max:255'
        ]);

        // Check uniqueness within the axe for this company
        $exists = SectionAnalytique::where('company_id', $companyId)
            ->where('axe_id', $request->axe_id)
            ->where('code', $request->code)
            ->exists();

        if ($exists) {
            return redirect()->back()->with('error', 'Une section avec ce code existe déjà dans cet axe.');
        }

        SectionAnalytique::create([
            'axe_id' => $request->axe_id,
            'code' => $request->code,
            'libelle' => $request->libelle,
            'company_id' => $companyId
        ]);

        return redirect()->route('analytique.sections.index')->with('success', 'Section analytique créée avec succès.');
    }

    public function update(Request $request, $id)
    {
        $section = SectionAnalytique::findOrFail($id);
        $companyId = Session::get('current_company_id') ?? auth()->user()->company_id;

        $request->validate([
            'axe_id' => 'required|exists:axes_analytiques,id',
            'code' => 'required|string|max:20',
            'libelle' => 'required|string|max:255'
        ]);

        // Check uniqueness excluding current
        $exists = SectionAnalytique::where('company_id', $companyId)
            ->where('axe_id', $request->axe_id)
            ->where('code', $request->code)
            ->where('id', '!=', $id)
            ->exists();

        if ($exists) {
            return redirect()->back()->with('error', 'Une section avec ce code existe déjà dans cet axe.');
        }

        $section->update([
            'axe_id' => $request->axe_id,
            'code' => $request->code,
            'libelle' => $request->libelle
        ]);

        return redirect()->route('analytique.sections.index')->with('success', 'Section analytique mise à jour.');
    }

    public function destroy($id)
    {
        $section = SectionAnalytique::findOrFail($id);
        $section->delete();

        return redirect()->route('analytique.sections.index')->with('success', 'Section analytique supprimée.');
    }
}
