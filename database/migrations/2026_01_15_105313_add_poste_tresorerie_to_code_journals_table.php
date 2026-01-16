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
        Schema::table('code_journals', function (Blueprint $table) {
            $table->string('poste_tresorerie')->nullable()->after('compte_de_tresorerie');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('code_journals', function (Blueprint $table) {
            $table->dropColumn('poste_tresorerie');
        });
    }
};
