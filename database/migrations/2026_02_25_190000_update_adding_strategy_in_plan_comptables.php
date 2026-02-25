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
        // Utilisation de DB::statement car modifier un ENUM via Blueprint nécessite doctrine/dbal
        // qui n'est pas installé dans le projet (vu dans composer.json).
        DB::statement("ALTER TABLE plan_comptables MODIFY COLUMN adding_strategy ENUM('auto', 'manuel', 'imported') NOT NULL DEFAULT 'manuel'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // En cas de rollback, on revient aux valeurs d'origine. 
        // /!\ Attention : s'il y a des données 'imported', SQL pourrait générer une erreur ou tronquer.
        DB::statement("ALTER TABLE plan_comptables MODIFY COLUMN adding_strategy ENUM('auto', 'manuel') NOT NULL DEFAULT 'manuel'");
    }
};
