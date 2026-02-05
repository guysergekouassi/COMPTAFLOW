<?php

namespace App\Http\Controllers;

use App\Models\Brouillon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class BrouillonController extends Controller
{
    public function index()
    {
        $companyId = Auth::user()->company_id;
        $drafts = Brouillon::where('company_id', $companyId)
            ->with(['planComptable', 'planTiers'])
            ->get()
            ->groupBy('batch_id');

        return view('accounting.brouillons', compact('drafts'));
    }

    public function store(Request $request)
    {
        try {
            DB::beginTransaction();

            $user = Auth::user();
            $batchId = (string) Str::uuid();
            $ecritures = json_decode($request->input('ecritures'), true);
            $source = $request->input('source', 'manuel');

            if (empty($ecritures)) {
                return response()->json(['error' => 'Aucune donnée à enregistrer.'], 400);
            }
            
            // SÉCURITÉ : Forcer l'exercice du contexte session
            $companyId = session('current_company_id', $user->company_id);
            $exerciceContextId = session('current_exercice_id');
            $exerciceActif = null;
            
            if ($exerciceContextId) {
                $exerciceActif = \App\Models\ExerciceComptable::where('id', $exerciceContextId)
                    ->where('company_id', $companyId)
                    ->first();
            }
            
            if (!$exerciceActif) {
                $exerciceActif = \App\Models\ExerciceComptable::where('company_id', $companyId)
                    ->where('is_active', 1)
                    ->first();
            }
            
            if (!$exerciceActif) {
                return response()->json(['error' => 'Aucun exercice comptable actif trouvé.'], 400);
            }

            $pieceFilename = null;
            if ($request->hasFile('piece_justificatif')) {
                $file = $request->file('piece_justificatif');
                $pieceFilename = time() . '_' . $file->getClientOriginalName();
                $file->move(public_path('justificatifs'), $pieceFilename);
            }

            foreach ($ecritures as $ecriture) {
                Brouillon::create([
                    'batch_id' => $batchId,
                    'source' => $source,
                    'date' => $ecriture['date'] ?? null,
                    'n_saisie' => $ecriture['n_saisie'] ?? null,
                    'description_operation' => $ecriture['description_operation'] ?? null,
                    'reference_piece' => $ecriture['reference_piece'] ?? null,
                    'plan_comptable_id' => $ecriture['plan_comptable_id'] ?? null,
                    'plan_tiers_id' => $ecriture['plan_tiers_id'] ?? null,
                    'compte_tresorerie_id' => $ecriture['compte_tresorerie_id'] ?? null,
                    'type_flux' => $ecriture['type_flux'] ?? null,
                    'plan_analytique' => $ecriture['plan_analytique'] ?? 0,
                    'code_journal_id' => $ecriture['code_journal_id'] ?? null,
                    // FORCER l'exercice du contexte session (ignorer celui du formulaire)
                    'exercices_comptables_id' => $exerciceActif->id,
                    'journaux_saisis_id' => $ecriture['journaux_saisis_id'] ?? null,
                    'debit' => $ecriture['debit'] ?? 0,
                    'credit' => $ecriture['credit'] ?? 0,
                    'piece_justificatif' => $pieceFilename ?? $ecriture['piece_justificatif'] ?? null,
                    'user_id' => $user->id,
                    'company_id' => $user->company_id,
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Brouillon enregistré avec succès.',
                'batch_id' => $batchId
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Erreur lors de l\'enregistrement du brouillon: ' . $e->getMessage()], 500);
        }
    }

    public function destroy($batchId)
    {
        try {
            $companyId = Auth::user()->company_id;
            Brouillon::where('batch_id', $batchId)->where('company_id', $companyId)->delete();
            return redirect()->back()->with('success', 'Brouillon supprimé avec succès.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Erreur lors de la suppression du brouillon.');
        }
    }

    public function load($batchId)
    {
        $brouillons = Brouillon::with(['planComptable', 'planTiers', 'codeJournal'])
            ->where('batch_id', $batchId)
            ->where('company_id', Auth::user()->company_id)
            ->get();

        if ($brouillons->isEmpty()) {
            return response()->json(['success' => false, 'message' => 'Brouillon non trouvé'], 404);
        }

        return response()->json([
            'success' => true,
            'brouillons' => $brouillons,
            'summary' => [
                'date' => $brouillons[0]->date,
                'description' => $brouillons[0]->description_operation,
                'reference' => $brouillons[0]->reference_piece,
                'source' => $brouillons[0]->source,
                'batch_id' => $batchId,
                'code_journal_id' => $brouillons[0]->code_journal_id,
                'journal_code' => $brouillons[0]->codeJournal ? $brouillons[0]->codeJournal->code_journal : 'N/A',
                'n_saisie' => $brouillons[0]->n_saisie,
                'compte_tresorerie_id' => $brouillons[0]->compte_tresorerie_id,
                'piece_justificatif' => $brouillons[0]->piece_justificatif
            ]
        ]);
    }
}
