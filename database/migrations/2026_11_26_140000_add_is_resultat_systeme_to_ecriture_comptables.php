<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('ecriture_comptables', 'is_resultat_systeme')) {
            Schema::table('ecriture_comptables', function (Blueprint $table) {
                $table->boolean('is_resultat_systeme')->default(false)->after('is_ran');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('ecriture_comptables', 'is_resultat_systeme')) {
            Schema::table('ecriture_comptables', function (Blueprint $table) {
                $table->dropColumn('is_resultat_systeme');
            });
        }
    }
};
