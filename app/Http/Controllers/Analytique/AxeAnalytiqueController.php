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
        
        // Mock data for KPIs if needed, or real stats
        $totalAxes = $axes->count();
        $totalSections = \App\Models\SectionAnalytique::where('company_id', $companyId)->count();

        return view('analytique.axes.index', compact('axes', 'totalAxes', 'totalSections'));
    }

    public function store(Request $request)
    {
        $companyId = Session::get('current_company_id') ?? auth()->user()->company_id;

        $request->validate([
            'code' => 'required|string|max:20',
            'libelle' => 'required|string|max:255',
            'type' => 'nullable|string'
        ]);

        // Check uniqueness for this company
        $exists = AxeAnalytique::where('company_id', $companyId)
            ->where('code', $request->code)
            ->exists();

        if ($exists) {
            return redirect()->back()->with('error', 'Un axe avec ce code existe déjà.');
        }

        AxeAnalytique::create([
            'code' => $request->code,
            'libelle' => $request->libelle,
            'type' => $request->type ?? 'divers',
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
            'type' => 'nullable|string'
        ]);

        // Check uniqueness excluding current
        $exists = AxeAnalytique::where('company_id', $companyId)
            ->where('code', $request->code)
            ->where('id', '!=', $id)
            ->exists();

        if ($exists) {
            return redirect()->back()->with('error', 'Un axe avec ce code existe déjà.');
        }

        $axe->update([
            'code' => $request->code,
            'libelle' => $request->libelle,
            'type' => $request->type ?? 'divers'
        ]);

        return redirect()->route('analytique.axes.index')->with('success', 'Axe analytique mis à jour.');
    }

    public function destroy($id)
    {
        $axe = AxeAnalytique::findOrFail($id);
        $axe->delete();

        return redirect()->route('analytique.axes.index')->with('success', 'Axe analytique supprimé.');
    }
}
