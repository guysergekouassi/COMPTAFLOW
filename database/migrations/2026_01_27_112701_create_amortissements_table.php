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
        Schema::create('amortissements', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('immobilisation_id');
            $table->unsignedBigInteger('exercice_id');
            
            // Données de l'amortissement
            $table->integer('annee'); // Année comptable
            $table->decimal('base_amortissable', 15, 2);
            $table->decimal('dotation_annuelle', 15, 2);
            $table->decimal('cumul_amortissement', 15, 2);
            $table->decimal('valeur_nette_comptable', 15, 2);
            
            // Lien avec l'écriture comptable générée
            $table->unsignedBigInteger('ecriture_comptable_id')->nullable();
            
            // Statut
            $table->enum('statut', ['previsionnel', 'comptabilise'])->default('previsionnel');
            $table->date('date_comptabilisation')->nullable();
            
            $table->timestamps();
            
            // Indexes
            $table->index('immobilisation_id');
            $table->index('exercice_id');
            $table->index('annee');
            $table->index('statut');
            
            // Contrainte unique : une seule ligne par immobilisation et par année
            $table->unique(['immobilisation_id', 'annee']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('amortissements');
    }
};
