<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('plan_tiers', function (Blueprint $table) {
            // 1. On supprime l'ancienne contrainte qui bloquait tout
        $table->dropUnique(['numero_de_tiers']);

        // 2. On crée la nouvelle : le numéro doit être unique PAR société
        $table->unique(['numero_de_tiers', 'company_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('plan_tiers', function (Blueprint $table) {
            $table->dropUnique(['numero_de_tiers', 'company_id']);
           $table->unique(['numero_de_tiers']);
        });
    }
};
