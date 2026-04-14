<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('factures_produites', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('exercice_id')->nullable();

            // Référence
            $table->string('reference', 50);           // ex: SP-2025-001 (ou format ComptaFlow)
            $table->string('n_saisie', 50)->nullable(); // Lié au n_saisie ComptaFlow si injecté

            // Informations client
            $table->string('client_nom', 255)->nullable();
            $table->string('client_tiers_code', 20)->nullable(); // ex: 410002

            // Montant
            $table->decimal('montant', 15, 2)->default(0);
            $table->string('devise', 10)->default('XOF');

            // Dates
            $table->date('date_facture');
            $table->unsignedTinyInteger('mois');   // 1-12
            $table->unsignedSmallInteger('annee'); // ex: 2025

            // Fichier stocké
            $table->string('nom_fichier_original', 255);
            $table->string('chemin_fichier', 500);   // Relatif à storage/app/
            $table->string('type_fichier', 10);      // pdf, jpg, png, jpeg
            $table->unsignedInteger('taille_fichier')->nullable(); // en octets

            // Statut
            $table->enum('statut', ['brouillon', 'valide', 'annulee'])->default('brouillon');
            $table->text('notes')->nullable();
            $table->boolean('injectee_comptaflow')->default(false);

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            // Index utiles
            $table->index(['company_id', 'annee', 'mois']);
            $table->index(['company_id', 'client_tiers_code']);
            $table->unique(['company_id', 'reference']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('factures_produites');
    }
};
