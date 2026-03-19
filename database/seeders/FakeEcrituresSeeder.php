<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\PlanComptable;
use App\Models\ExerciceComptable;
use App\Models\CodeJournal;
use App\Models\JournalSaisi;
use App\Models\EcritureComptable;
use Carbon\Carbon;

class FakeEcrituresSeeder extends Seeder
{
    public function run(): void
    {
        $companyId = 1;
        $userId = 2; // admin orange

        // Trouver l'exercice 2024 actif
        $exercice = ExerciceComptable::where('company_id', $companyId)
            ->whereYear('date_fin', 2024)
            ->first();

        if (!$exercice) {
            $exercice = ExerciceComptable::where('company_id', $companyId)
                ->orderBy('date_debut', 'desc')
                ->first();
        }

        echo "Exercice: " . $exercice->id . " (" . $exercice->date_debut . " -> " . $exercice->date_fin . ")\n";

        // Trouver le journal
        $journal = CodeJournal::where('company_id', $companyId)->first();
        if (!$journal) {
            $this->command->error('Aucun journal trouvé !');
            return;
        }
        echo "Journal: " . $journal->id . " (" . $journal->code_journal . ")\n";

        // Trouver les comptes
        $acc521 = PlanComptable::where('company_id', $companyId)->where('numero_de_compte', 'like', '521%')->first();
        $acc101 = PlanComptable::where('company_id', $companyId)->where('numero_de_compte', 'like', '101%')->first();
        $acc211 = PlanComptable::where('company_id', $companyId)->where('numero_de_compte', 'like', '211%')->first();
        $acc701 = PlanComptable::where('company_id', $companyId)->where('numero_de_compte', 'like', '701%')->first();
        $acc411 = PlanComptable::where('company_id', $companyId)->where('numero_de_compte', 'like', '411%')->first();
        $acc601 = PlanComptable::where('company_id', $companyId)->where('numero_de_compte', 'like', '601%')->first();
        $acc401 = PlanComptable::where('company_id', $companyId)->where('numero_de_compte', 'like', '401%')->first();

        // Supprimer les anciennes écritures fictives
        DB::table('ecriture_comptables')->where('company_id', $companyId)->delete();

        // Helper pour créer une entrée
        $makeEntry = function ($date, $accId, $tierId, $debit, $credit, $libelle, $nsaisie) 
            use ($companyId, $exercice, $userId, $journal) {
            $d = Carbon::parse($date);
            $js = JournalSaisi::firstOrCreate([
                'annee' => $d->year,
                'mois' => $d->month,
                'exercices_comptables_id' => $exercice->id,
                'code_journals_id' => $journal->id,
                'company_id' => $companyId,
            ], ['user_id' => $userId]);

            return EcritureComptable::create([
                'company_id' => $companyId,
                'exercices_comptables_id' => $exercice->id,
                'user_id' => $userId,
                'journaux_saisis_id' => $js->id,
                'code_journal_id' => $journal->id,
                'plan_comptable_id' => $accId,
                'plan_tiers_id' => $tierId,
                'date' => $date,
                'debit' => $debit,
                'credit' => $credit,
                'description_operation' => $libelle,
                'n_saisie' => $nsaisie,
                'n_saisie_user' => $nsaisie,
                'statut' => 'approved',
            ]);
        };

        // 1. Apport Capital 50M
        $makeEntry('2024-01-02', $acc521->id, null, 50000000, 0, 'Apport en capital', 'ECR_0001');
        $makeEntry('2024-01-02', $acc101->id, null, 0, 50000000, 'Apport en capital', 'ECR_0001');

        // 2. Achat Terrain 10M
        $makeEntry('2024-02-15', $acc211->id, null, 10000000, 0, 'Achat terrain bureau', 'ECR_0002');
        $makeEntry('2024-02-15', $acc521->id, null, 0, 10000000, 'Achat terrain bureau', 'ECR_0002');

        // 3. Vente 25M
        $makeEntry('2024-06-10', $acc411->id, null, 25000000, 0, 'Vente de marchandises', 'ECR_0003');
        $makeEntry('2024-06-10', $acc701->id, null, 0, 25000000, 'Vente de marchandises', 'ECR_0003');

        // 4. Achat marchandises 15M
        $makeEntry('2024-08-20', $acc601->id, null, 15000000, 0, 'Achat matieres premieres', 'ECR_0004');
        $makeEntry('2024-08-20', $acc401->id, null, 0, 15000000, 'Achat matieres premieres', 'ECR_0004');

        $total = DB::table('ecriture_comptables')->where('company_id', $companyId)->count();
        echo "Total ecritures: " . $total . "\n";
    }
}
