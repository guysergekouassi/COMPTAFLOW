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
            $table->unsignedBigInteger('poste_tresorerie_id')->nullable()->after('compte_tresorerie_id');
            $table->foreign('poste_tresorerie_id')->references('id')->on('compte_tresoreries')->onDelete('set null');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ecriture_comptables', function (Blueprint $table) {
            $table->dropForeign(['poste_tresorerie_id']);
            $table->dropColumn('poste_tresorerie_id');

        });
    }
};
