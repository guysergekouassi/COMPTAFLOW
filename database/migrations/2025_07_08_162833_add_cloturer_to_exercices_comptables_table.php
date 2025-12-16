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
        Schema::table('exercices_comptables', function (Blueprint $table) {
            $table->boolean('cloturer')->default(0)->after('nombre_journaux_saisis');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('exercices_comptables', function (Blueprint $table) {
            $table->dropColumn('cloturer');
        });
    }
};
