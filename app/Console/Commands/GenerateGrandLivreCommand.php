<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\GrandLivre;
use App\Models\GrandLivreTiers;
use App\Models\EcritureComptable;
use App\Models\PlanComptable;
use App\Models\PlanTiers;
use App\Models\User;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class GenerateGrandLivreCommand extends Command
{
    protected $signature = 'grandlivre:generate {--type=} {--id=} {--exercice=}';
    protected $description = 'Generate a Grand Livre PDF in the background';

    public function handle()
    {
        $type = $this->option('type');
        $id = $this->option('id');
        $exerciceId = $this->option('exercice');

        // Disable time limit and set memory
        set_time_limit(0);
        ini_set('memory_limit', '2048M');

        try {
            if ($type === 'general') {
                $record = GrandLivre::findOrFail($id);
                $this->generateGeneral($record, $exerciceId);
            } else {
                $record = GrandLivreTiers::findOrFail($id);
                $this->generateTiers($record, $exerciceId);
            }
            $this->info('Grand Livre generated successfully.');
        } catch (\Exception $e) {
            Log::error('Background Grand Livre generation failed: ' . $e->getMessage());
            $this->error($e->getMessage());
        }
    }

    private function generateGeneral($record, $exerciceId)
    {
        $companyId = $record->company_id;
        $user = User::with('company')->findOrFail($record->user_id);

        $compte1 = PlanComptable::withoutGlobalScopes()->findOrFail($record->plan_comptable_id_1);
        $compte2 = PlanComptable::withoutGlobalScopes()->findOrFail($record->plan_comptable_id_2);

        $v1 = (string)$compte1->numero_de_compte;
        $v2 = (string)$compte2->numero_de_compte;
        $min = strcmp($v1, $v2) < 0 ? $v1 : $v2;
        $max = strcmp($v1, $v2) < 0 ? $v2 : $v1;

        $comptesIds = PlanComptable::withoutGlobalScopes()
            ->where('company_id', $companyId)
            ->where('numero_de_compte', '>=', $min)
            ->where('numero_de_compte', '<=', $max)
            ->pluck('id');

        $query = EcritureComptable::join('plan_comptables', 'ecriture_comptables.plan_comptable_id', '=', 'plan_comptables.id')
            ->select('ecriture_comptables.*')
            ->with(['planComptable', 'planTiers', 'codeJournal'])
            ->where('ecriture_comptables.company_id', $companyId)
            ->whereIn('ecriture_comptables.plan_comptable_id', $comptesIds)
            ->whereBetween('date', [$record->date_debut, $record->date_fin])
            ->orderBy('plan_comptables.numero_de_compte', 'asc')
            ->orderBy('date', 'asc')
            ->orderBy('n_saisie', 'asc');

        if ($exerciceId) {
            $query->where('exercices_comptables_id', $exerciceId);
        }

        $ecritures = $query->get();
        $ecritures = $ecritures->whereIn('plan_comptable_id', $comptesIds);

        // Solde initial
        $soldeQuery = EcritureComptable::where('company_id', $companyId)
            ->whereIn('plan_comptable_id', $comptesIds)
            ->where('date', '<', $record->date_debut)
            ->selectRaw('plan_comptable_id, SUM(debit) as si_debit, SUM(credit) as si_credit')
            ->groupBy('plan_comptable_id');

        if ($exerciceId) {
            $soldeQuery->where('exercices_comptables_id', $exerciceId);
        }

        $soldesInitiaux = $soldeQuery->get()
            ->keyBy('plan_comptable_id')
            ->map(function ($r) {
                $d = (float)$r->si_debit;
                $c = (float)$r->si_credit;
                return ['debit' => $d, 'credit' => $c, 'solde' => $d - $c];
            })
            ->toArray();

        $titre = "Grand-livre des comptes";
        $paginationService = new \App\Services\GrandLivrePaginationService();
        $paginatedData = $paginationService->paginate($ecritures, $soldesInitiaux, $titre, 'comptaflow');

        $pdf = app('dompdf.wrapper');
        $pdf->getDomPDF()->set_option('isPhpEnabled', true);
        $pdf->getDomPDF()->set_option('enable_font_subsetting', false); // Désactiver le subsetting pour un gain de temps massif
        $pdf->getDomPDF()->set_option('isHtml5ParserEnabled', true);
        $pdf->loadView('grand_livre', [
            'company_name' => $user->company->company_name ?? 'Non défini',
            'paginatedData' => $paginatedData,
            'date_debut' => $record->date_debut,
            'date_fin' => $record->date_fin,
            'compte' => $compte1->numero_de_compte,
            'compte_2' => $compte2->numero_de_compte,
            'user' => $user,
            'titre' => $titre,
            'display_mode' => 'comptaflow',
        ]);

        $grandLivresPath = public_path('grand_livres/');
        if (!file_exists($grandLivresPath)) {
            mkdir($grandLivresPath, 0777, true);
        }

        $tmpFile = $record->grand_livre . '.tmp';
        $pdf->save($grandLivresPath . $tmpFile);
        rename($grandLivresPath . $tmpFile, $grandLivresPath . $record->grand_livre);
    }

    private function generateTiers($record, $exerciceId)
    {
        $companyId = $record->company_id;
        $user = User::with('company')->findOrFail($record->user_id);

        $compte1 = PlanTiers::withoutGlobalScopes()->findOrFail($record->plan_tiers_id_1);
        $compte2 = PlanTiers::withoutGlobalScopes()->findOrFail($record->plan_tiers_id_2);

        $v1 = (string)$compte1->numero_de_tiers;
        $v2 = (string)$compte2->numero_de_tiers;
        $min = strcmp($v1, $v2) < 0 ? $v1 : $v2;
        $max = strcmp($v1, $v2) < 0 ? $v2 : $v1;

        $comptesIds = PlanTiers::withoutGlobalScopes()
            ->where('company_id', $companyId)
            ->where('numero_de_tiers', '>=', $min)
            ->where('numero_de_tiers', '<=', $max)
            ->pluck('id');

        $query = EcritureComptable::join('plan_tiers', 'ecriture_comptables.plan_tiers_id', '=', 'plan_tiers.id')
            ->select('ecriture_comptables.*')
            ->with(['planTiers', 'planComptable', 'codeJournal'])
            ->where('ecriture_comptables.company_id', $companyId)
            ->whereIn('ecriture_comptables.plan_tiers_id', $comptesIds)
            ->whereBetween('date', [$record->date_debut, $record->date_fin])
            ->orderBy('plan_tiers.numero_de_tiers', 'asc')
            ->orderBy('date', 'asc')
            ->orderBy('n_saisie', 'asc');

        if ($exerciceId) {
            $query->where('exercices_comptables_id', $exerciceId);
        }

        $ecritures = $query->get();
        $ecritures = $ecritures->whereIn('plan_tiers_id', $comptesIds);

        // Solde initial
        $soldeQuery = EcritureComptable::where('company_id', $companyId)
            ->whereIn('plan_tiers_id', $comptesIds)
            ->where('date', '<', $record->date_debut)
            ->selectRaw('plan_tiers_id, SUM(debit) as si_debit, SUM(credit) as si_credit')
            ->groupBy('plan_tiers_id');

        if ($exerciceId) {
            $soldeQuery->where('exercices_comptables_id', $exerciceId);
        }

        $soldesInitiaux = $soldeQuery->get()
            ->keyBy('plan_tiers_id')
            ->map(function ($r) {
                $d = (float)$r->si_debit;
                $c = (float)$r->si_credit;
                return ['debit' => $d, 'credit' => $c, 'solde' => $d - $c];
            })
            ->toArray();

        $titre = "Grand-livre des Tiers";
        $paginationService = new \App\Services\GrandLivrePaginationService();
        $paginatedData = $paginationService->paginate($ecritures, $soldesInitiaux, $titre, 'comptaflow');

        $pdf = app('dompdf.wrapper');
        $pdf->getDomPDF()->set_option('isPhpEnabled', true);
        $pdf->getDomPDF()->set_option('enable_font_subsetting', false); // Désactiver le subsetting
        $pdf->getDomPDF()->set_option('isHtml5ParserEnabled', true);
        $pdf->loadView('grand_livre', [
            'company_name' => $user->company->company_name ?? 'Non défini',
            'paginatedData' => $paginatedData,
            'date_debut' => $record->date_debut,
            'date_fin' => $record->date_fin,
            'compte' => $compte1->numero_de_tiers,
            'compte_2' => $compte2->numero_de_tiers,
            'user' => $user,
            'titre' => $titre,
            'display_mode' => 'comptaflow',
        ]);

        $grandLivresPath = public_path('grand_livres_tiers/');
        if (!file_exists($grandLivresPath)) {
            mkdir($grandLivresPath, 0777, true);
        }

        $tmpFile = $record->grand_livre_tiers . '.tmp';
        $pdf->save($grandLivresPath . $tmpFile);
        rename($grandLivresPath . $tmpFile, $grandLivresPath . $record->grand_livre_tiers);
    }
}
