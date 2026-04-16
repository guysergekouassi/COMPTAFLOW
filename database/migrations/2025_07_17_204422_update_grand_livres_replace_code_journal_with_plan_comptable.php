<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateGrandLivresReplaceCodeJournalWithPlanComptable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('grand_livres')) {
        Schema::table('grand_livres', function (Blueprint $table) {
            // Supprimer la contrainte et la colonne code_journals_id uniquement s'ils existent
            if (Schema::hasColumn('grand_livres', 'code_journals_id')) {
                $table->dropForeign(['code_journals_id']);
                $table->dropColumn('code_journals_id');
            }

            // Ajouter la nouvelle colonne plan_comptable_id uniquement si elle n'existe pas
            if (!Schema::hasColumn('grand_livres', 'plan_comptable_id')) {
                $table->foreignId('plan_comptable_id')
                    ->after('date_fin') // ou 'grand_livre' selon l'ordre souhaité
                    ->constrained('plan_comptables')
                    ->onDelete('cascade');
            }
        });
    }}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('grand_livres', function (Blueprint $table) {
            // Supprimer la contrainte plan_comptable_id
            $table->dropForeign(['plan_comptable_id']);
            $table->dropColumn('plan_comptable_id');

            // Remettre code_journals_id
            $table->foreignId('code_journals_id')
                ->constrained('code_journals')
                ->onDelete('cascade');
        });
    }
}
