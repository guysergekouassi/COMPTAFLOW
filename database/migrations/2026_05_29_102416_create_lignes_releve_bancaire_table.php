<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lignes_releve_bancaire', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rapprochement_id')
                  ->constrained('rapprochements_bancaires')
                  ->onDelete('cascade');
            $table->date('date_operation');
            $table->date('date_valeur')->nullable();
            $table->string('libelle', 500)->nullable();
            $table->string('reference', 100)->nullable();
            // Montants tels qu'ils apparaissent dans le relevé
            $table->decimal('debit',  18, 2)->default(0);   // sortie d'argent
            $table->decimal('credit', 18, 2)->default(0);   // entrée d'argent
            $table->decimal('solde',  18, 2)->nullable();    // solde progressif si présent
            // Statut de pointage
            $table->enum('statut', ['non_pointe', 'pointe', 'ecart'])->default('non_pointe');
            $table->integer('ordre')->default(0); // ordre d'apparition dans le fichier
            $table->timestamps();

            $table->index(['rapprochement_id', 'statut']);
            $table->index(['rapprochement_id', 'date_operation']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lignes_releve_bancaire');
    }
};
