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
        Schema::create('immobilisations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('exercice_id')->nullable();
            
            // Identification
            $table->string('code', 50)->unique();
            $table->string('libelle');
            $table->enum('categorie', ['incorporelle', 'corporelle', 'financiere']);
            $table->text('description')->nullable();
            
            // Comptes comptables
            $table->unsignedBigInteger('compte_immobilisation_id'); // Classe 2
            $table->unsignedBigInteger('compte_amortissement_id'); // Classe 28
            $table->unsignedBigInteger('compte_dotation_id'); // Classe 68
            
            // Acquisition
            $table->date('date_acquisition');
            $table->decimal('valeur_acquisition', 15, 2);
            $table->string('fournisseur')->nullable();
            $table->string('numero_facture')->nullable();
            
            // Amortissement
            $table->date('date_mise_en_service');
            $table->integer('duree_amortissement'); // En années
            $table->enum('methode_amortissement', ['lineaire', 'degressif']);
            $table->decimal('taux_amortissement', 5, 2); // Pourcentage
            $table->decimal('valeur_residuelle', 15, 2)->default(0);
            
            // Statut
            $table->enum('statut', ['en_cours', 'totalement_amorti', 'cede'])->default('en_cours');
            
            // Cession (si applicable)
            $table->date('date_cession')->nullable();
            $table->decimal('montant_cession', 15, 2)->nullable();
            $table->unsignedBigInteger('compte_cession_id')->nullable();
            $table->text('motif_cession')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index('company_id');
            $table->index('exercice_id');
            $table->index('categorie');
            $table->index('statut');
            $table->index('compte_immobilisation_id');
            $table->index('compte_amortissement_id');
            $table->index('compte_dotation_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('immobilisations');
    }
};
