<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // `ALTER TABLE ... MODIFY` est propre a MySQL : sur SQLite, ou tourne la
        // suite d'epreuves, il s'arrete sur `syntax error near "MODIFY"` et tout
        // ce qui suit reste rouge. La voie MySQL est conservee telle quelle pour
        // la production ; les autres moteurs passent par le Blueprint, qui sait
        // changer une colonne sans doctrine/dbal depuis Laravel 11.
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE plan_comptables MODIFY COLUMN adding_strategy ENUM('auto', 'manuel', 'imported') NOT NULL DEFAULT 'manuel'");

            return;
        }

        Schema::table('plan_comptables', function (Blueprint $table) {
            // SQLite n'a pas d'ENUM : il pose une contrainte de controle a la
            // creation. La remplacer par une chaine libre suffit ici — ce que la
            // migration veut, c'est que « imported » soit accepte.
            $table->string('adding_strategy')->default('manuel')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // En cas de rollback, on revient aux valeurs d'origine.
        // /!\ Attention : s'il y a des données 'imported', SQL pourrait générer une erreur ou tronquer.
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE plan_comptables MODIFY COLUMN adding_strategy ENUM('auto', 'manuel') NOT NULL DEFAULT 'manuel'");
        }
    }
};
