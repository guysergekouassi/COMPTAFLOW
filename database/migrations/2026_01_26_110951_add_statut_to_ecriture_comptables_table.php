<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('ecriture_comptables', 'statut')) {
            Schema::table('ecriture_comptables', function (Blueprint $table) {
                $table->string('statut')->default('draft'); // ou 'approved' si tu veux
            });
        }
    }

    public function down(): void
    {
        Schema::table('ecriture_comptables', function (Blueprint $table) {
            $table->dropColumn('statut');
        });
    }
};
