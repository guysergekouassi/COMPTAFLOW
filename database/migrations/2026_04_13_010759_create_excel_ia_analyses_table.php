<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('excel_ia_analyses', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('exercice_id')->nullable();

            // Fichiers analysés
            $table->json('fichiers_noms');         // Liste des noms de fichiers uploadés
            $table->string('mois_cible')->nullable(); // ex: "JANVIER" ou "TOUS"

            // Résultats IA bruts
            $table->longText('ecritures_json');    // Tableau JSON des écritures générées
            $table->longText('rapport_transparence')->nullable(); // Rapport final IA
            $table->text('notes_utilisateur')->nullable();

            // Statut
            $table->enum('statut', ['en_attente', 'analyse', 'valide', 'erreur'])->default('en_attente');
            $table->string('erreur_message', 500)->nullable();

            // Actions effectuées
            $table->boolean('injecte_bdd')->default(false);
            $table->boolean('txt_telecharge')->default(false);
            $table->timestamp('injecte_le')->nullable();

            // Méta
            $table->unsignedInteger('nb_ecritures')->default(0);
            $table->decimal('total_debit', 15, 2)->default(0);
            $table->decimal('total_credit', 15, 2)->default(0);

            $table->timestamps();

            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('excel_ia_analyses');
    }
};
