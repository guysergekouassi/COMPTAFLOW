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
        Schema::table('tresorerie', function (Blueprint $table) {
            $table->string('categorie')->nullable()->after('poste_tresorerie');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tresorerie', function (Blueprint $table) {
            $table->dropColumn('categorie');
        });
    }
};
