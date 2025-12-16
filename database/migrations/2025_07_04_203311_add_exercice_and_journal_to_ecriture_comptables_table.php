<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('ecriture_comptables', function (Blueprint $table) {
            $table->foreignId('exercices_comptables_id')
                  ->after('code_journal_id')
                  ->constrained('exercices_comptables')
                  ->onDelete('cascade');

            $table->foreignId('journaux_saisis_id')
                  ->after('exercices_comptables_id')
                  ->constrained('journaux_saisis')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('ecriture_comptables', function (Blueprint $table) {
            $table->dropForeign(['exercices_comptables_id']);
            $table->dropColumn('exercices_comptables_id');

            $table->dropForeign(['journaux_saisis_id']);
            $table->dropColumn('journaux_saisis_id');
        });
    }
};
