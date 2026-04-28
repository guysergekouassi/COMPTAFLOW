<?php

namespace App\Http\Controllers;

use App\Models\EcritureComptable;
use App\Models\ExerciceComptable;
use App\Models\PlanComptable;
use App\Models\PlanTiers;
use App\Models\CodeJournal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AdjustmentController extends Controller
{
    /**
     * Détection de doublons (Écritures presque similaires)
     */
    public function duplicates(Request $request)
    {
        $user = Auth::user();
        $activeCompanyId = session('current_company_id', $user->company_id);
        $exerciceId = session('current_exercice_id');

        if (!$exerciceId) {
            $exerciceActif = ExerciceComptable::where('company_id', $activeCompanyId)->where('is_active', 1)->first();
            $exerciceId = $exerciceActif ? $exerciceActif->id : null;
        }

        if (!$exerciceId) {
            return redirect()->back()->with('error', 'Veuillez sélectionner un exercice comptable pour la détection de doublons.');
        }

        // Algorithme : Grouper par date, journal, débit, crédit
        // On cherche les groupes ayant plus d'une ligne
        $duplicates = EcritureComptable::where('company_id', $activeCompanyId)
            ->where('exercices_comptables_id', $exerciceId)
            ->where('statut', 'approved')
            ->select('date', 'code_journal_id', 'debit', 'credit', DB::raw('count(*) as count'))
            ->groupBy('date', 'code_journal_id', 'debit', 'credit')
            ->having('count', '>', 1)
            ->get();

        $groupedEntries = [];
        foreach ($duplicates as $dup) {
            $entries = EcritureComptable::with(['planComptable', 'planTiers', 'codeJournal'])
                ->where('company_id', $activeCompanyId)
                ->where('exercices_comptables_id', $exerciceId)
                ->where('date', $dup->date)
                ->where('code_journal_id', $dup->code_journal_id)
                ->where('debit', $dup->debit)
                ->where('credit', $dup->credit)
                ->orderBy('created_at')
                ->get();
            
            $groupedEntries[] = [
                'criteria' => $dup,
                'entries' => $entries
            ];
        }

        return view('adjustment.duplicates', compact('groupedEntries'));
    }

    /**
     * Page de modification par lot
     */
    public function bulkEdit(Request $request)
    {
        $user = Auth::user();
        $activeCompanyId = session('current_company_id', $user->company_id);
        $exerciceId = session('current_exercice_id');

        $query = EcritureComptable::with(['planComptable', 'planTiers', 'codeJournal'])
            ->where('company_id', $activeCompanyId);

        if ($exerciceId) {
            $query->where('exercices_comptables_id', $exerciceId);
        }

        // Filtres
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function($q) use ($s) {
                $q->where('description_operation', 'like', "%$s%")
                  ->orWhere('reference_piece', 'like', "%$s%");
            });
        }

        if ($request->filled('journal_id')) {
            $query->where('code_journal_id', $request->journal_id);
        }

        if ($request->filled('date_start')) {
            $query->where('date', '>=', $request->date_start);
        }
        if ($request->filled('date_end')) {
            $query->where('date', '<=', $request->date_end);
        }

        $entries = $query->orderBy('date', 'desc')->paginate(100);
        $journals = CodeJournal::where('company_id', $activeCompanyId)->get();

        return view('adjustment.bulk_edit', compact('entries', 'journals'));
    }

    /**
     * Traitement de la modification par lot
     */
    public function bulkUpdate(Request $request)
    {
        $ids = $request->input('ids', []);
        $field = $request->input('field');
        $newValue = $request->input('value');

        if (empty($ids) || !$field) {
            return response()->json(['success' => false, 'message' => 'Données invalides.']);
        }

        try {
            DB::beginTransaction();

            $updateData = [];
            if ($field === 'plan_comptable_id') {
                $updateData['plan_comptable_id'] = $newValue;
            } elseif ($field === 'plan_tiers_id') {
                $updateData['plan_tiers_id'] = $newValue === 'null' ? null : $newValue;
            } elseif ($field === 'description_operation') {
                $updateData['description_operation'] = $newValue;
            } elseif ($field === 'reference_piece') {
                $updateData['reference_piece'] = $newValue;
            }

            if (!empty($updateData)) {
                EcritureComptable::whereIn('id', $ids)->update($updateData);
            }

            DB::commit();
            return response()->json(['success' => true, 'message' => count($ids) . ' écritures mises à jour.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Erreur : ' . $e->getMessage()]);
        }
    }

    /**
     * Recherche AJAX pour autocomplétion
     */
    public function searchReferences(Request $request)
    {
        $type = $request->input('type'); // 'account' or 'tier'
        $q = $request->input('q');
        $companyId = session('current_company_id');

        if ($type === 'account') {
            $results = PlanComptable::where('company_id', $companyId)
                ->where(function($query) use ($q) {
                    $query->where('numero_de_compte', 'like', "$q%")
                          ->orWhere('intitule', 'like', "%$q%");
                })
                ->limit(20)
                ->get()
                ->map(fn($item) => ['id' => $item->id, 'text' => $item->numero_de_compte . ' - ' . $item->intitule]);
        } else {
            $results = PlanTiers::where('company_id', $companyId)
                ->where(function($query) use ($q) {
                    $query->where('numero_de_tiers', 'like', "$q%")
                          ->orWhere('intitule', 'like', "%$q%");
                })
                ->limit(20)
                ->get()
                ->map(fn($item) => ['id' => $item->id, 'text' => $item->numero_de_tiers . ' - ' . $item->intitule]);
        }

        return response()->json($results);
    }
}
