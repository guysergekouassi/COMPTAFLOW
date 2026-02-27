<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Lettrage;
use App\Models\EcritureComptable;
use App\Models\PlanTiers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class LettrageController extends Controller
{
    public function index(Request $request)
    {
        $companyId = $request->header('X-Company-Id', Auth::user()->company_id);
        $tierId = $request->query('tier_id');

        if (!$tierId) {
            return response()->json(['message' => 'tier_id est requis'], 422);
        }

        $ecritures = EcritureComptable::where('company_id', $companyId)
            ->where('plan_tiers_id', $tierId)
            ->whereNull('lettrage_id')
            ->orderBy('date')
            ->get();

        return response()->json($ecritures);
    }

    public function store(Request $request)
    {
        $request->validate([
            'ecriture_ids' => 'required|array|min:2',
            'ecriture_ids.*' => 'exists:ecriture_comptables,id',
        ]);

        $companyId = $request->header('X-Company-Id', Auth::user()->company_id);

        try {
            DB::beginTransaction();

            $ecritures = EcritureComptable::whereIn('id', $request->ecriture_ids)
                ->where('company_id', $companyId)
                ->whereNull('lettrage_id')
                ->get();

            if ($ecritures->count() !== count($request->ecriture_ids)) {
                throw new \Exception("Certaines écritures ne sont pas éligibles.");
            }

            if (abs($ecritures->sum('debit') - $ecritures->sum('credit')) > 0.01) {
                throw new \Exception("Déséquilibre lors du lettrage.");
            }

            $lettrage = Lettrage::create([
                'code' => strtoupper(Str::random(5)),
                'date_lettrage' => now(),
                'user_id' => Auth::id(),
                'company_id' => $companyId,
            ]);

            EcritureComptable::whereIn('id', $request->ecriture_ids)->update([
                'lettrage_id' => $lettrage->id
            ]);

            DB::commit();
            return response()->json(['success' => true, 'lettrage' => $lettrage]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }
}
