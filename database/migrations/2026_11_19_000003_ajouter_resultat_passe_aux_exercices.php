<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('exercices_comptables')) {
            return;
        }

        Schema::table('exercices_comptables', function (Blueprint $table) {
            if (!Schema::hasColumn('exercices_comptables', 'resultat_passe')) {
                $table->boolean('resultat_passe')->default(false);
            }
            if (!Schema::hasColumn('exercices_comptables', 'resultat_passe_le')) {
                $table->timestamp('resultat_passe_le')->nullable();
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('exercices_comptables')) {
            return;
        }

        Schema::table('exercices_comptables', function (Blueprint $table) {
            if (Schema::hasColumn('exercices_comptables', 'resultat_passe_le')) {
                $table->dropColumn('resultat_passe_le');
            }
            if (Schema::hasColumn('exercices_comptables', 'resultat_passe')) {
                $table->dropColumn('resultat_passe');
            }
        });
    }
};
