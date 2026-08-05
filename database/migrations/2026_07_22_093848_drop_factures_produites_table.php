<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Suppression complète du module "Factures Produites" (décision actée : nettoyage total).
     */
    public function up(): void
    {
        Schema::dropIfExists('factures_produites');
    }

    /**
     * Reverse the migrations.
     * Recrée la table à l'identique de la migration d'origine, pour permettre un rollback propre.
     */
    public function down(): void
    {
        Schema::create('factures_produites', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('exercice_id')->nullable();

            $table->string('reference', 50);
            $table->string('n_saisie', 50)->nullable();

            $table->string('client_nom', 255)->nullable();
            $table->string('client_tiers_code', 20)->nullable();

            $table->decimal('montant', 15, 2)->default(0);
            $table->string('devise', 10)->default('XOF');

            $table->date('date_facture');
            $table->unsignedTinyInteger('mois');
            $table->unsignedSmallInteger('annee');

            $table->string('nom_fichier_original', 255);
            $table->string('chemin_fichier', 500);
            $table->string('type_fichier', 10);
            $table->unsignedInteger('taille_fichier')->nullable();

            $table->enum('statut', ['brouillon', 'valide', 'annulee'])->default('brouillon');
            $table->text('notes')->nullable();
            $table->boolean('injectee_comptaflow')->default(false);

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            $table->index(['company_id', 'annee', 'mois']);
            $table->index(['company_id', 'client_tiers_code']);
            $table->unique(['company_id', 'reference']);
        });
    }
};
