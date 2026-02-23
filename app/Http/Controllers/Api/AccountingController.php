<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PlanComptable;
use App\Models\PlanTiers;
use App\Models\CodeJournal;
use App\Models\EcritureComptable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

/**
 * AccountingController gère le Plan Comptable, le Plan Tiers et les Codes Journaux.
 */
class AccountingController extends Controller
{
    // --- PLAN COMPTABLE ---

    public function planComptableIndex(Request $request)
    {
        $companyId = $request->header('X-Company-Id', $request->user()->company_id);
        
        $query = PlanComptable::where('company_id', $companyId);

        if ($request->has('search')) {
            $query->where(function($q) use ($request) {
                $q->where('numero_de_compte', 'like', $request->search . '%')
                  ->orWhere('intitule', 'like', '%' . $request->search . '%');
            });
        }

        return response()->json($query->orderBy('numero_de_compte')->get());
    }

    public function planComptableStore(Request $request)
    {
        $companyId = $request->header('X-Company-Id', $request->user()->company_id);

        $request->validate([
            'numero_de_compte' => 'required|string',
            'intitule' => 'required|string',
        ]);

        $numero = str_pad($request->numero_de_compte, 8, '0', STR_PAD_RIGHT);

        if (PlanComptable::where('company_id', $companyId)->where('numero_de_compte', $numero)->exists()) {
            return response()->json(['message' => 'Ce numéro de compte existe déjà.'], 422);
        }

        $plan = PlanComptable::create([
            'numero_de_compte' => $numero,
            'intitule' => ucfirst(strtolower($request->intitule)),
            'adding_strategy' => 'manuel',
            'user_id' => $request->user()->id,
            'company_id' => $companyId,
            'classe' => substr($numero, 0, 1),
        ]);

        return response()->json($plan, 201);
    }

    // --- PLAN TIERS ---

    public function planTiersIndex(Request $request)
    {
        $companyId = $request->header('X-Company-Id', $request->user()->company_id);
        
        $query = PlanTiers::with('compte')->where('company_id', $companyId);

        if ($request->has('search')) {
            $query->where(function($q) use ($request) {
                $q->where('numero_de_tiers', 'like', $request->search . '%')
                  ->orWhere('intitule', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->has('type')) {
            $query->where('type_de_tiers', $request->type);
        }

        return response()->json($query->orderBy('numero_de_tiers')->get());
    }

    public function planTiersStore(Request $request)
    {
        $companyId = $request->header('X-Company-Id', $request->user()->company_id);

        $request->validate([
            'numero_de_tiers' => 'required|string',
            'compte_general' => 'required|exists:plan_comptables,id',
            'intitule' => 'required|string',
            'type_de_tiers' => 'required',
        ]);

        if (PlanTiers::where('company_id', $companyId)->where('numero_de_tiers', $request->numero_de_tiers)->exists()) {
            return response()->json(['message' => 'Ce numéro de tiers existe déjà.'], 422);
        }

        $tiers = PlanTiers::create([
            'numero_de_tiers' => $request->numero_de_tiers,
            'compte_general' => $request->compte_general,
            'intitule' => ucfirst(strtolower($request->intitule)),
            'type_de_tiers' => $request->type_de_tiers,
            'user_id' => $request->user()->id,
            'company_id' => $companyId,
        ]);

        return response()->json($tiers, 201);
    }

    // --- CODES JOURNAUX ---

    public function journalsIndex(Request $request)
    {
        $companyId = $request->header('X-Company-Id', $request->user()->company_id);
        
        $query = CodeJournal::where('company_id', $companyId);

        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        return response()->json($query->orderBy('code_journal')->get());
    }

    public function journalsStore(Request $request)
    {
        $companyId = $request->header('X-Company-Id', $request->user()->company_id);
        $digits = $request->user()->company->journal_code_digits ?? 3;

        $request->validate([
            'code_journal' => ['required', 'string', 'max:'.$digits],
            'intitule' => 'required|string',
            'type' => 'required|string',
            'compte_de_contrepartie' => 'nullable|string',
        ]);

        if (CodeJournal::where('company_id', $companyId)->where('code_journal', strtoupper($request->code_journal))->exists()) {
            return response()->json(['message' => 'Ce code journal existe déjà.'], 422);
        }

        $journal = CodeJournal::create([
            'code_journal' => strtoupper($request->code_journal),
            'intitule' => ucfirst(strtolower($request->intitule)),
            'type' => $request->type,
            'compte_de_contrepartie' => $request->compte_de_contrepartie,
            'traitement_analytique' => $request->traitement_analytique === 'oui' ? 1 : 0,
            'user_id' => $request->user()->id,
            'company_id' => $companyId,
        ]);

        return response()->json($journal, 201);
    }

    // --- SUPPRESSION (Générique pour l'exemple) ---

    public function destroy(Request $request, $type, $id)
    {
        $model = match($type) {
            'plan-comptable' => PlanComptable::class,
            'plan-tiers' => PlanTiers::class,
            'journal' => CodeJournal::class,
            default => null,
        };

        if (!$model) return response()->json(['message' => 'Type invalide'], 400);

        $item = $model::findOrFail($id);

        // Vérification si utilisé dans des écritures
        $count = match($type) {
            'plan-comptable' => EcritureComptable::where('plan_comptable_id', $id)->count(),
            'plan-tiers' => EcritureComptable::where('plan_tiers_id', $id)->count(),
            'journal' => EcritureComptable::where('code_journal_id', $id)->count(),
        };

        if ($count > 0) {
            return response()->json(['message' => 'Impossible de supprimer un élément utilisé dans des écritures.'], 423);
        }

        $item->delete();
        return response()->json(['message' => 'Supprimé avec succès']);
    }
}
