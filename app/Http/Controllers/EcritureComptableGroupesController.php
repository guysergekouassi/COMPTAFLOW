<?php

namespace App\Http\Controllers;

use App\Models\PlanTiers;
use App\Models\User;
use Illuminate\Http\Request;
use App\Models\EcritureComptable;
use App\Models\ExerciceComptable;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use App\Models\PlanComptable;
use App\Models\CompteTresorerie;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class EcritureComptableGroupesController extends Controller
{
    public function index(Request $request)
    {
        try {
            $user = Auth::user();
// RÉCUPÉRER L'ID DE LA COMPAGNIE ACTIVE
        $activeCompanyId = session('current_company_id', $user->company_id);

            $data = $request->all();
            
            $plansComptables = PlanComptable::where('company_id', $activeCompanyId)
                ->select('id', 'numero_de_compte', 'intitule')
                ->get();

            $plansTiers = PlanTiers::where('company_id', $activeCompanyId)
                ->select('id', 'numero_de_tiers', 'intitule')
                ->get();

            $comptesTresorerie = CompteTresorerie::where('company_id', $activeCompanyId) // Assurez-vous d'avoir une colonne 'company_id' si vous filtrez par entreprise
                ->select('id', 'name', 'type')
                ->get();

            $query = EcritureComptable::where('company_id', $activeCompanyId)
                ->orderBy('created_at', 'desc');

            if (!empty($data['n_saisie'])) {
                $query->where('n_saisie', $data['n_saisie']);
            }

            if (!empty($data['id_journal'])) {
                $query->where('journaux_saisis_id', $data['id_journal']);
            }

            $queryForSum = clone $query;

            // $ecritures = $query->with(['planComptable', 'planTiers', 'codeJournal'])->get();
            $ecritures = $query->with(['planComptable', 'planTiers', 'codeJournal', 'compteTresorerie'])->get();
            $totalDebit = $queryForSum->sum('debit');
            $totalCredit = $queryForSum->sum('credit');

            $lastSaisie = EcritureComptable::where('company_id', $user->company_id)
                ->max('n_saisie');

            $nextSaisieNumber = $lastSaisie ? str_pad((int) $lastSaisie + 1, 12, '0', STR_PAD_LEFT) : '000000000001';

            $exercice = ExerciceComptable::findOrFail($data['id_exercice']);

            return view('accounting_entry_real_goupes', compact(
                'plansComptables',
                'plansTiers',
                'data',
                'ecritures',
                'totalDebit',
                'totalCredit',
                'nextSaisieNumber',
                'exercice',
                'comptesTresorerie'
            ));
        } catch (\Throwable $e) {
            Log::error('Erreur dans index (EcritureComptableGroupesController) : ' . $e->getMessage());
            return redirect()->back()->with('error', 'Erreur lors du chargement des écritures comptables.');
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $entry = EcritureComptable::findOrFail($id);

            $validated = $request->validate([
                'date' => 'required|date',
                'description_operation' => 'required|string|max:255',
                'reference_piece' => 'nullable|string|max:255',
                'compte_general' => 'required|integer|exists:plan_comptables,id',
                'plan_tiers_id' => 'nullable|integer|exists:plan_tiers,id',
                'plan_analytique' => 'required|boolean',
                'debit' => 'nullable|numeric|min:0',
                'credit' => 'nullable|numeric|min:0',
                'piece_justificatif' => 'nullable|file|mimes:pdf,jpg,jpeg,png',
            ]);

            $entry->fill([
                'date' => $validated['date'],
                'description_operation' => $validated['description_operation'],
                'reference_piece' => $validated['reference_piece'] ?? null,
                'plan_comptable_id' => $validated['compte_general'],
                'plan_tiers_id' => $validated['plan_tiers_id'] ?? null,
                'plan_analytique' => $validated['plan_analytique'],
                'debit' => $validated['debit'] ?? 0,
                'credit' => $validated['credit'] ?? 0,
            ]);

            if ($request->hasFile('piece_justificatif')) {
                $file = $request->file('piece_justificatif');
                if ($entry->piece_justificatif) {
                    $ancienFichierPath = public_path('justificatifs/' . $entry->piece_justificatif);
                    if (file_exists($ancienFichierPath)) {
                        unlink($ancienFichierPath);
                    }
                }
                $pieceJustificatifName = time() . '_' . $file->getClientOriginalName();
                $file->move(public_path('justificatifs'), $pieceJustificatifName);
                $entry->piece_justificatif = $pieceJustificatifName;
            }

            $entry->save();

            return redirect()->back()->with('success', 'Écriture mise à jour avec succès');
        } catch (\Throwable $e) {
            Log::error('Erreur lors de la mise à jour d’une écriture : ' . $e->getMessage());
            return redirect()->back()->with('error', 'Une erreur est survenue lors de la mise à jour.');
        }
    }

    public function miseAJourMassive(Request $request)
    {
        try {
            $user = Auth::user();
            $activeCompanyId = session('current_company_id', $user->company_id);
            $lignes = $request->input('lignes', []);

            if (empty($lignes) || !is_array($lignes)) {
                return response()->json(['success' => false, 'message' => 'Aucune ligne à mettre à jour.'], 400);
            }

            // ── Sécurité 1 : toutes les écritures doivent appartenir à la company courante ──
            $ids = array_column($lignes, 'id');
            $ecrituresExistantes = EcritureComptable::where('company_id', $activeCompanyId)
                ->whereIn('id', $ids)
                ->get()
                ->keyBy('id');

            if ($ecrituresExistantes->count() !== count(array_unique($ids))) {
                return response()->json([
                    'success' => false,
                    'message' => 'Une ou plusieurs écritures sont introuvables ou n\'appartiennent pas à votre société.'
                ], 403);
            }

            // ── Sécurité 2 : l'exercice des écritures ne doit pas être clôturé ──
            $premiere = $ecrituresExistantes->first();
            $exercice = ExerciceComptable::find($premiere->exercices_comptables_id);
            if (!$exercice || $exercice->cloturer) {
                return response()->json([
                    'success' => false,
                    'message' => 'Impossible de modifier : l\'exercice comptable est clôturé ou introuvable.'
                ], 422);
            }

            // ── Sécurité 3 : le groupe doit rester équilibré (même règle que storeMultiple) ──
            $totalDebit = 0;
            $totalCredit = 0;
            foreach ($lignes as $ligne) {
                $totalDebit += floatval($ligne['debit'] ?? 0);
                $totalCredit += floatval($ligne['credit'] ?? 0);
            }
            $diff = abs($totalDebit - $totalCredit);
            if ($diff > 0.1) {
                return response()->json([
                    'success' => false,
                    'message' => "Écriture déséquilibrée (Débit: $totalDebit / Crédit: $totalCredit). Écart: $diff"
                ], 422);
            }

            DB::beginTransaction();
            foreach ($lignes as $ligne) {
                $ecrituresExistantes[$ligne['id']]->update([
                    'date' => $ligne['date'],
                    'n_saisie' => $ligne['n_saisie'],
                    'reference_piece' => $ligne['reference'],
                    'description_operation' => $ligne['description'],
                    'plan_comptable_id' => $ligne['compte_general'],
                    'plan_tiers_id' => $ligne['compte_tiers'] ?: null,
                    'poste_tresorerie_id' => $ligne['poste_tresorerie_id'] ?? $ecrituresExistantes[$ligne['id']]->poste_tresorerie_id,
                    'plan_analytique' => $ligne['plan_analytique'],
                    'debit' => $ligne['debit'],
                    'credit' => $ligne['credit'],
                ]);
            }
            DB::commit();

            return response()->json(['success' => true, 'message' => 'Mise à jour réussie']);
        } catch (\Throwable $e) {
            if (DB::transactionLevel() > 0) {
                DB::rollBack();
            }
            Log::error('Erreur dans miseAJourMassive : ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Erreur lors de la mise à jour massive.'], 500);
        }
    }

    public function supprimerGroupe($nSaisie)
    {
        try {
            $user = Auth::user();
            $companyId = session('current_company_id', $user->company_id);

            $deletedCount = EcritureComptable::where('company_id', $companyId)
                ->where('n_saisie', $nSaisie)
                ->delete();

            if ($deletedCount > 0) {
                return response()->json(['success' => true, 'message' => "Écriture $nSaisie supprimée avec succès."]);
            }

            return response()->json(['success' => false, 'message' => 'Aucune écriture trouvée pour ce numéro.'], 404);
        } catch (\Throwable $e) {
            Log::error("Erreur lors de la suppression de l'écriture groupe $nSaisie: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Erreur serveur lors de la suppression.'], 500);
        }
    }
}
