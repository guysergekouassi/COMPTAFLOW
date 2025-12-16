<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB; // <-- AJOUTER CETTE LIGNE

return new class extends Migration
{
    public function up(): void
    {
        // 1. Nettoyer les données EXISTANTES : Remplacer tous les NULL par 0.00
        // Ceci est la cause de l'erreur Data truncated
        DB::statement("UPDATE ecriture_comptables SET debit = 0.00 WHERE debit IS NULL");
        DB::statement("UPDATE ecriture_comptables SET credit = 0.00 WHERE credit IS NULL");

        // 2. Modifier la structure de la colonne
        // (Assurez-vous d'avoir bien installé doctrine/dbal)
        Schema::table('ecriture_comptables', function (Blueprint $table) {

            // On retire le nullable() pour garantir que la valeur est toujours un nombre
            $table->decimal('debit', 15, 2)->default(0.00)->nullable(false)->change();
            $table->decimal('credit', 15, 2)->default(0.00)->nullable(false)->change();
        });
    }

    public function down(): void
    {
        Schema::table('ecriture_comptables', function (Blueprint $table) {
            // Revenir à l'état initial (nullable)
            $table->decimal('debit', 15, 2)->nullable()->default(null)->change();
            $table->decimal('credit', 15, 2)->nullable()->default(null)->change();
        });
    }
};
