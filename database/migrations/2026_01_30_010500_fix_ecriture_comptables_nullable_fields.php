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
        Schema::table('ecriture_comptables', function (Blueprint $table) {
            // Rendre plan_tiers_id nullable
            $table->foreignId('plan_tiers_id')->nullable()->change();
            
            // Rendre ces champs également nullable pour compatibilité
            $table->foreignId('exercices_comptables_id')->nullable()->change();
            $table->foreignId('journaux_saisis_id')->nullable()->change();

            // Corriger type_flux pour accepter les valeurs utilisées dans le code et être nullable
            $table->string('type_flux')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ecriture_comptables', function (Blueprint $table) {
            $table->foreignId('plan_tiers_id')->nullable(false)->change();
            $table->enum('type_flux', ['debit', 'credit'])->nullable(false)->change();
        });
    }
};
