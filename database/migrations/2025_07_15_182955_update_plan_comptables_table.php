<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('plan_comptables', function (Blueprint $table) {
            // Supprimer les anciennes colonnes
            $table->dropColumn([
                'type_de_compte',
                'poste',
                'extrait_du_compte',
                'traitement_analytique',
                'classe',
            ]);

            // Ajouter la colonne enum
            $table->enum('adding_strategy', ['auto', 'manuel'])->default('manuel')->after('intitule');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('plan_comptables', function (Blueprint $table) {
            // Restaurer les anciennes colonnes
            $table->string('type_de_compte')->nullable();
            $table->string('poste')->nullable();
            $table->boolean('extrait_du_compte')->default(false);
            $table->boolean('traitement_analytique')->default(false);
            $table->integer('classe')->nullable();

            // Supprimer la colonne enum
            $table->dropColumn('adding_strategy');
        });
    }
};
