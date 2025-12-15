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

class EcritureComptableGroupesController extends Controller
{
    public function index(Request $request)
    {
        try {
            $user = Auth::user();
            $data = $request->all();

            // Initialisation des valeurs par défaut pour éviter les erreurs "Undefined array key"
            if (!isset($data['annee'])) $data['annee'] = \Carbon\Carbon::now()->year;
            if (!isset($data['mois'])) $data['mois'] = \Carbon\Carbon::now()->month;
            if (!isset($data['n_saisie'])) $data['n_saisie'] = null;
            if (!isset($data['id_journal'])) $data['id_journal'] = null;
            if (!isset($data['code'])) $data['code'] = null;
            if (!isset($data['type'])) $data['type'] = null;
            if (!isset($data['intitule'])) $data['intitule'] = null;

            if (!isset($data['id_exercice'])) {
                $dernierExercice = ExerciceComptable::where('company_id', $user->company_id)
                    ->orderBy('date_debut', 'desc')
                    ->first();
                
                if ($dernierExercice) {
                    $data['id_exercice'] = $dernierExercice->id;
                }
            }

            $plansComptables = PlanComptable::where('company_id', $user->company_id)
                ->select('id', 'numero_de_compte', 'intitule')
                ->get();

            $plansTiers = PlanTiers::where('company_id', $user->company_id)
                ->select('id', 'numero_de_tiers', 'intitule')
                ->get();

            $comptesTresorerie = CompteTresorerie::where('company_id', $user->company_id) // Assurez-vous d'avoir une colonne 'company_id' si vous filtrez par entreprise
                ->select('id', 'name', 'type')
                ->get();

            $query = EcritureComptable::where('company_id', $user->company_id)
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

            // --- FIX ANTI-GRAVITY: Remplissage automatique des données manquantes (N/A) ---
            // 1. Essayer de récupérer les infos depuis le JournalSaisi (Si filtré par journal)
            if (!empty($data['id_journal'])) {
                 $journalSaisi = \App\Models\JournalSaisi::with('codeJournal')->find($data['id_journal']);
                 if ($journalSaisi && $journalSaisi->codeJournal) {
                     if (empty($data['code'])) $data['code'] = $journalSaisi->codeJournal->code_journal;
                     if (empty($data['type'])) $data['type'] = $journalSaisi->codeJournal->type;
                     if (empty($data['intitule'])) $data['intitule'] = $journalSaisi->codeJournal->intitule;
                 }
            }

            if ($ecritures->isNotEmpty()) {
                $first = $ecritures->first();
                
                // Récupération via la relation CodeJournal
                if ($first->codeJournal) {
                    if (empty($data['code'])) $data['code'] = $first->codeJournal->code_journal;
                    if (empty($data['type'])) $data['type'] = $first->codeJournal->type;
                    if (empty($data['intitule'])) $data['intitule'] = $first->codeJournal->intitule;
                }

                // Récupération de la date si l'année/mois par défaut ne correspondent pas (ou pour être plus précis)
                if ($first->date) {
                    try {
                        $dateObj = \Carbon\Carbon::parse($first->date);
                        // On écrase les valeurs par défaut (now()) si elles n'étaient pas dans la requête explicite
                        // Astuce: on verifie si 'annee' est present dans $request->all() original, sinon on prend celui de l'écriture
                        if (!$request->has('annee')) $data['annee'] = $dateObj->year;
                        if (!$request->has('mois')) $data['mois'] = $dateObj->month;
                    } catch (\Exception $e) { 
                        // silent fail 
                    }
                }
            }
            // -----------------------------------------------------------------------------

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
            $lignes = $request->input('lignes');

            foreach ($lignes as $ligne) {
                $ecriture = EcritureComptable::find($ligne['id']);
                if ($ecriture) {
                    $ecriture->update([
                        'date' => $ligne['date'],
                        'n_saisie' => $ligne['n_saisie'],
                        'reference_piece' => $ligne['reference'],
                        'description_operation' => $ligne['description'],
                        'plan_comptable_id' => $ligne['compte_general'],
                        'plan_tiers_id' => $ligne['compte_tiers'] ?: null,
                        'plan_analytique' => $ligne['plan_analytique'],
                        'debit' => $ligne['debit'],
                        'credit' => $ligne['credit'],
                    ]);
                }
            }

            return response()->json(['message' => 'Mise à jour réussie']);
        } catch (\Throwable $e) {
            Log::error('Erreur dans miseAJourMassive : ' . $e->getMessage());
            return response()->json(['message' => 'Erreur lors de la mise à jour massive.'], 500);
        }
    }
}
