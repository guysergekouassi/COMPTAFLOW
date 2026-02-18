<?php

namespace App\Http\Controllers\Analytique;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PlanComptable;
use App\Models\SectionAnalytique;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;

class VentilationController extends Controller
{
    public function rulesIndex()
    {
        $companyId = Session::get('current_company_id') ?? auth()->user()->company_id;
        
        // Get Class 6 & 7 accounts
        $accounts = PlanComptable::where('company_id', $companyId)
            ->where(function($q) {
                $q->where('numero_de_compte', 'like', '6%')
                  ->orWhere('numero_de_compte', 'like', '7%');
            })
            ->orderBy('numero_de_compte')
            ->get();

        $sections = SectionAnalytique::where('company_id', $companyId)->with('axe')->get();

        return view('analytique.regles.index', compact('accounts', 'sections'));
    }

    public function storeRule(Request $request)
    {
        $companyId = Session::get('current_company_id') ?? auth()->user()->company_id;

        $request->validate([
            'plan_comptable_id' => 'required|exists:plan_comptables,id',
            'ventilations' => 'required|array',
            'ventilations.*.section_id' => 'required|exists:sections_analytiques,id',
            'ventilations.*.pourcentage' => 'required|numeric|min:0.01|max:100',
        ]);

        // Validate total percentage = 100
        $total = collect($request->ventilations)->sum('pourcentage');
        if (abs($total - 100) > 0.01) {
            return back()->with('error', 'Le total des pourcentages doit être égal à 100%. Actuellement : ' . $total . '%');
        }

        DB::beginTransaction();
        try {
            // Delete existing rules for this account
            RegleVentilation::where('company_id', $companyId)
                ->where('plan_comptable_id', $request->plan_comptable_id)
                ->delete();

            // Create new rules
            foreach ($request->ventilations as $v) {
                RegleVentilation::create([
                    'company_id' => $companyId,
                    'plan_comptable_id' => $request->plan_comptable_id,
                    'section_id' => $v['section_id'],
                    'pourcentage_defaut' => $v['pourcentage']
                ]);
            }

            DB::commit();
            return back()->with('success', 'Règles de ventilation enregistrées avec succès.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erreur lors de l\'enregistrement : ' . $e->getMessage());
        }
    }
    
    public function getRules($accountId)
    {
        $companyId = Session::get('current_company_id') ?? auth()->user()->company_id;
        
        $rules = RegleVentilation::where('company_id', $companyId)
            ->where('plan_comptable_id', $accountId)
            ->with(['section.axe'])
            ->get();
            
        return response()->json($rules);
    }

    public function balanceIndex()
    {
        return view('analytique.rapports.balance');
    }

    public function ledgerIndex()
    {
        return view('analytique.rapports.grand_livre');
    }

    public function resultIndex()
    {
        return view('analytique.rapports.resultat');
    }
}
