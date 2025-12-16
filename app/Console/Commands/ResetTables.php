<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ResetTables extends Command
{
    protected $signature = 'app:reset-tables';
    protected $description = 'Réinitialise certaines tables de la base de données pour le développement/test.';

    public function handle()
    {
        if (!$this->confirm('Es-tu sûr de vouloir TRUNCATE ces tables ?')) {
            $this->info('Opération annulée.');
            return;
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        DB::table('ecriture_comptables')->truncate();
        DB::table('journaux_saisis')->truncate();
        DB::table('exercices_comptables')->truncate();
        DB::table('code_journals')->truncate();
        DB::table('plan_tiers')->truncate();
        DB::table('plan_comptables')->truncate();
        DB::table('grand_livres')->truncate();
        DB::table('balances')->truncate();

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $this->info('Tables réinitialisées avec succès.');
    }
}
