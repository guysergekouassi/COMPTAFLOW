<?php

namespace App\Http\Controllers;

use App\Models\JournalSaisi;
use App\Models\PlanTiers;
use Illuminate\Http\Request;
use App\Models\EcritureComptable;
use App\Models\ExerciceComptable;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use App\Models\PlanComptable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;




class PlanComptableEcritureGroupesController extends Controller
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

        $plansComptables = PlanComptable::where('company_id', $user->company_id)
            ->select('id', 'numero_de_compte', 'intitule')
            ->get();

        $plansTiers = PlanTiers::where('company_id', $user->company_id)
            ->select('id', 'numero_de_tiers', 'intitule')
            ->get();

        $JournauxSaisi = JournalSaisi::where('company_id', $user->company_id)
            ->select('id', 'annee', 'mois', 'code_journals_id')
            ->get();



        $query = EcritureComptable::where('company_id', $user->company_id)
            ->whereBetween('date', [$debutExercice, $finExercice]) // <-- Filtre sur l'année
            ->orderBy('created_at', 'desc');

        // ⚠️ Ajout du filtre n_saisie
        if (!empty($data['n_saisie'])) {
            $query->where('n_saisie', $data['n_saisie']);
        }


        // Ajouter le filtre sur plan_comptable_id si fourni
        if (!empty($data['id_plan_comptable'])) {
            $query->where('plan_comptable_id', $data['id_plan_comptable']);
        }

        // Cloner la requête pour la somme
        $queryForSum = clone $query;

        $ecritures = $query->with(['planComptable', 'planTiers', 'codeJournal', 'JournauxSaisis'])->get();

        $totalDebit = $queryForSum->sum('debit');
        $totalCredit = $queryForSum->sum('credit');

        return view('plan_comptable_ecritures_groupes', compact(
            'data',
            'ecritures',
            'totalDebit',
            'totalCredit',
            'debutExercice',
            'finExercice',
            'plansTiers',
            'plansComptables',
            'JournauxSaisi'
        ));
    }



    public function miseAJourMassive(Request $request)
    {
        try {
            $lignes = $request->input('lignes');

            foreach ($lignes as $ligne) {
                $ecriture = EcritureComptable::find($ligne['id']);

                if ($ecriture) {
                    $ecriture->date = $ligne['date'];
                    $ecriture->n_saisie = $ligne['n_saisie'];
                    $ecriture->reference_piece = $ligne['reference'];
                    $ecriture->description_operation = $ligne['description'];

                    $ecriture->journaux_saisis_id = $ligne['journal_saisie'];

                    $journalSaisi = JournalSaisi::find($ligne['journal_saisie']);
                    $ecriture->code_journal_id = $journalSaisi?->codeJournal?->id;

                    $ecriture->plan_comptable_id = $ligne['compte_general'];
                    $ecriture->plan_tiers_id = $ligne['compte_tiers'] ?? null;
                    $ecriture->plan_analytique = $ligne['plan_analytique'] ?? false;
                    $ecriture->debit = $ligne['debit'] ?? 0;
                    $ecriture->credit = $ligne['credit'] ?? 0;

                    $ecriture->save();
                }
            }

            return response()->json(['message' => 'Mise à jour réussie']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erreur: ' . $e->getMessage()], 500);
        }
    }





}
