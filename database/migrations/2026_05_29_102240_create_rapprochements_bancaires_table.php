<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rapprochements_bancaires', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->foreignId('compte_tresorerie_id')->constrained('compte_tresoreries')->onDelete('cascade');
            $table->foreignId('exercice_id')->constrained('exercices_comptables')->onDelete('cascade');
            $table->foreignId('code_journal_id')->nullable()->constrained('code_journals')->onDelete('set null');
            $table->date('date_debut');
            $table->date('date_fin');
            // Soldes de référence saisis par l'utilisateur
            $table->decimal('solde_initial_banque', 18, 2)->default(0);
            $table->decimal('solde_final_banque',   18, 2)->default(0);
            $table->decimal('solde_initial_compta', 18, 2)->default(0);
            // Nom du fichier importé (pour affichage)
            $table->string('nom_fichier_releve')->nullable();
            // Statut de la session
            $table->enum('statut', ['en_cours', 'valide', 'cloture'])->default('en_cours');
            $table->text('note')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();

            $table->index(['company_id', 'compte_tresorerie_id', 'statut'], 'rb_company_compte_statut_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rapprochements_bancaires');
    }
};
