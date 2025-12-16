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


class PlanTiersEcritureGroupesController extends Controller
{
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
        if (!empty($data['id_plan_tiers'])) {
            $query->where('plan_tiers_id', $data['id_plan_tiers']);
        }

        // Cloner la requête pour la somme
        $queryForSum = clone $query;

        $ecritures = $query->with(['planComptable', 'planTiers', 'codeJournal', 'JournauxSaisis'])->get();

        $totalDebit = $queryForSum->sum('debit');
        $totalCredit = $queryForSum->sum('credit');

        return view('plan_tiers_ecritures_groupes', compact(
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
        $lignes = $request->input('lignes');

        foreach ($lignes as $ligne) {
            $ecriture = EcritureComptable::find($ligne['id']);

            if ($ecriture) {
                $ecriture->date = $ligne['date'];
                $ecriture->n_saisie = $ligne['n_saisie'];
                $ecriture->reference_piece = $ligne['reference'];
                $ecriture->description_operation = $ligne['description'];

                // On suppose que "journal_saisie" est l'ID de JournauxSaisis
                $ecriture->journaux_saisis_id = $ligne['journal_saisie'];

                // Si tu veux récupérer aussi le code_journal_id via la relation
                $journalSaisi = JournalSaisi::find($ligne['journal_saisie']);

                $ecriture->code_journal_id = $journalSaisi ? $journalSaisi->codeJournal->id : null;

                $ecriture->plan_comptable_id = $ligne['compte_general'];
                $ecriture->plan_tiers_id = $ligne['compte_tiers'] ?: null;
                $ecriture->plan_analytique = $ligne['plan_analytique'];
                $ecriture->debit = $ligne['debit'];
                $ecriture->credit = $ligne['credit'];

                $ecriture->save();
            }
        }

        return response()->json(['message' => 'Mise à jour réussie']);
    }
}
