<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
         if (!Schema::hasTable('grand_livres')) {
        Schema::table('grand_livres', function (Blueprint $table) {
            // Supprimer l’ancienne colonne
            $table->dropForeign(['plan_comptable_id']);
            $table->dropColumn('plan_comptable_id');

            // Ajouter les deux nouvelles colonnes
            $table->foreignId('plan_comptable_id_1')->after('date_fin')->constrained('plan_comptables')->onDelete('cascade');
            $table->foreignId('plan_comptable_id_2')->after('plan_comptable_id_1')->constrained('plan_comptables')->onDelete('cascade');
        });
    }}

    public function down(): void
    {
        Schema::table('grand_livres', function (Blueprint $table) {
            // Supprimer les deux nouvelles colonnes
            $table->dropForeign(['plan_comptable_id_1']);
            $table->dropForeign(['plan_comptable_id_2']);
            $table->dropColumn(['plan_comptable_id_1', 'plan_comptable_id_2']);

            // Restaurer l’ancienne colonne
            $table->foreignId('plan_comptable_id')->constrained('plan_comptables')->onDelete('cascade');
        });
    }
};

