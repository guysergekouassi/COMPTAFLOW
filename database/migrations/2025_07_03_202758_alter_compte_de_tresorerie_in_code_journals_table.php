<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('code_journals', function (Blueprint $table) {
            // Supprimer la colonne actuelle si elle n'est pas déjà une foreign key
            $table->dropColumn('compte_de_tresorerie');

            // Ajouter une nouvelle colonne foreign key
            $table->foreignId('compte_de_tresorerie')
                ->nullable()
                ->constrained('plan_comptables')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('code_journals', function (Blueprint $table) {
            // Supprimer la clé étrangère
            $table->dropForeign(['compte_de_tresorerie']);
            $table->dropColumn('compte_de_tresorerie');

            // Restaurer l'ancienne définition si besoin
            $table->string('compte_de_tresorerie')->nullable();
        });
    }
};
