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
        Schema::table('ecriture_comptables', function (Blueprint $table) {
            $table->string('n_saisie_user', 50)->nullable()->after('n_saisie');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ecriture_comptables', function (Blueprint $table) {
            $table->dropColumn('n_saisie_user');
        });
    }
};
