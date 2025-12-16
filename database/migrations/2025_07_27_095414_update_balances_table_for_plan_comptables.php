<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateBalancesTableForPlanComptables extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
         if (!Schema::hasTable('balances')) {
        Schema::table('balances', function (Blueprint $table) {
            // Supprimer l'ancienne clé étrangère
            $table->dropForeign(['code_journals_id']);
            $table->dropColumn('code_journals_id');

            // Ajouter les nouvelles clés
            $table->foreignId('plan_comptable_id_1')->after('date_fin')->constrained('plan_comptables')->onDelete('cascade');
            $table->foreignId('plan_comptable_id_2')->after('plan_comptable_id_1')->constrained('plan_comptables')->onDelete('cascade');
        });
    }
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('balances', function (Blueprint $table) {
            // Supprimer les nouveaux champs
            $table->dropForeign(['plan_comptable_id_1']);
            $table->dropForeign(['plan_comptable_id_2']);
            $table->dropColumn(['plan_comptable_id_1', 'plan_comptable_id_2']);

            // Restaurer l'ancien champ
            $table->foreignId('code_journals_id')->constrained('code_journals')->onDelete('cascade')->after('date_fin');
        });
    }
}
