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
        Schema::create('liasse_mappings', function (Blueprint $table) {
            $table->id();
            $table->string('type')->nullable(); // FIXE, VARIABLE
            $table->string('code_tableau')->nullable(); // FR1, ACTIF, etc.
            $table->string('titre_tableau')->nullable();
            $table->string('type_tableau')->nullable(); // Fixe, Variable
            $table->string('onglet_excel')->nullable();
            $table->string('code_ligne_dgi', 50)->nullable();
            $table->text('libelle_ligne')->nullable();
            $table->string('libelle_colonne')->nullable();
            $table->string('code_champ_dgi', 100)->unique();
            $table->string('cellule_excel', 20)->nullable();
            $table->integer('pos_ligne')->nullable();
            $table->integer('pos_col')->nullable();
            $table->timestamps();

            $table->index('code_tableau');
            $table->index('code_champ_dgi');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('liasse_mappings');
    }
};
