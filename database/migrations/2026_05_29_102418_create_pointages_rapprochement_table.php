<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pointages_rapprochement', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rapprochement_id')
                  ->constrained('rapprochements_bancaires')
                  ->onDelete('cascade');
            $table->foreignId('ligne_releve_id')
                  ->constrained('lignes_releve_bancaire')
                  ->onDelete('cascade');
            $table->foreignId('ecriture_comptable_id')
                  ->constrained('ecriture_comptables')
                  ->onDelete('cascade');
            // auto = pré-pointage algorithme, manuel = fait par l'utilisateur
            $table->enum('type_pointage', ['auto', 'manuel'])->default('auto');
            // Écart résiduel (0 si parfaitement équilibré)
            $table->decimal('ecart', 18, 2)->default(0);
            $table->text('note')->nullable();
            $table->foreignId('created_by')
                  ->nullable()
                  ->constrained('users')
                  ->onDelete('set null');
            $table->timestamps();

            $table->index(['rapprochement_id']);
            $table->index(['ligne_releve_id']);
            $table->index(['ecriture_comptable_id']);
            // Éviter le double-pointage d'une même écriture dans le même rapprochement
            $table->unique(['rapprochement_id', 'ecriture_comptable_id'], 'unique_ecriture_par_rapprochement');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pointages_rapprochement');
    }
};
