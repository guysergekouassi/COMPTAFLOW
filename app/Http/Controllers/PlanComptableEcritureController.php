<?php

namespace App\Http\Controllers;

use App\Models\PlanTiers;
use Illuminate\Http\Request;
use App\Models\EcritureComptable;
use App\Models\ExerciceComptable;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use App\Models\PlanComptable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;




class PlanComptableEcritureController extends Controller
{
    //
    public function index(Request $request)
    {
        // dd($request->all());
        $user = Auth::user();
        $data = $request->all();

        // Année en cours
        $anneeEnCours = date('Y');
        $debutExercice = $request->input('date_debut') ?? date('Y') . '-01-01';
        $finExercice = $request->input('date_fin') ?? date('Y') . '-12-31';



        $query = EcritureComptable::where('company_id', $user->company_id)
            ->whereBetween('date', [$debutExercice, $finExercice]) // <-- Filtre sur l'année
            ->orderBy('created_at', 'desc');


        // Ajouter le filtre sur plan_comptable_id si fourni
        if (!empty($data['id_plan_comptable'])) {
            $query->where('plan_comptable_id', $data['id_plan_comptable']);
        }

        // Cloner la requête pour la somme
        $queryForSum = clone $query;

        $ecritures = $query->with(['planComptable', 'planTiers', 'codeJournal'])->get();

        $totalDebit = $queryForSum->sum('debit');
        $totalCredit = $queryForSum->sum('credit');

        $solde = $totalDebit - $totalCredit;

        return view('plan_comptable_ecritures', compact(
            'data',
            'ecritures',
            'totalDebit',
            'totalCredit',
            'debutExercice',
            'finExercice',
            'solde' 
        ));
    }


    public function update(Request $request, $id)
    {
        // dd($request->all());

        try {

            $ecriture = EcritureComptable::findOrFail($id);

            $validated = $request->validate([
                'description_operation' => 'required|string|max:255',
                'reference_piece' => 'nullable|string|max:255',
                'compte_general' => 'required|exists:plan_comptables,id',
                'plan_tiers_id' => 'nullable|exists:plan_tiers,id',
                'plan_analytique' => 'required|in:0,1',
                'debit' => 'nullable|numeric|min:0',
                'credit' => 'nullable|numeric|min:0',
                'piece_justificatif' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
            ]);

            $ecriture->description_operation = $request->description_operation;
            $ecriture->reference_piece = $request->reference_piece;
            $ecriture->plan_comptable_id = $request->compte_general;
            $ecriture->plan_tiers_id = $request->plan_tiers_id;
            $ecriture->plan_analytique = $request->plan_analytique;
            $ecriture->debit = $request->debit;
            $ecriture->credit = $request->credit;

            $message = 'Écriture mise à jour avec succès.';

            if ($request->hasFile('piece_justificatif')) {
                $file = $request->file('piece_justificatif');
                $originalName = $file->getClientOriginalName();

                $existingFileName = $ecriture->piece_justificatif
                    ? basename($ecriture->piece_justificatif)
                    : null;

                if ($existingFileName !== $originalName) {
                    $pieceJustificatifName = time() . '_' . $originalName;

                    // Vérifie si le fichier existe déjà dans le dossier pour éviter d'écraser
                    if (File::exists(public_path('justificatifs/' . $pieceJustificatifName))) {
                        $message = 'Le fichier existe déjà sur le disque. Veuillez renommer le fichier.';
                    } else {
                        $file->move(public_path('justificatifs'), $pieceJustificatifName);
                        $ecriture->piece_justificatif = 'justificatifs/' . $pieceJustificatifName;
                        $message = 'Écriture mise à jour avec une nouvelle pièce justificative.';
                    }
                } else {
                    $message = 'Le fichier est identique à celui déjà enregistré. Aucun changement effectué.';
                }
            }

            $ecriture->save();

            return redirect()->back()->with('success', $message);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la mise à jour de l\'écriture : ' . $e->getMessage());

            return redirect()->back()->with('error', 'Une erreur est survenue : ' . $e->getMessage());
        }
    }







}
