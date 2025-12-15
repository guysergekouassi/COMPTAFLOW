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
        Schema::table('code_journals', function (Blueprint $table) {
            $table->dropColumn(['annee', 'mois']);
        });
    }

    public function down(): void
    {
        Schema::table('code_journals', function (Blueprint $table) {
            $table->year('annee');
            $table->string('mois');
        });
    }

};
