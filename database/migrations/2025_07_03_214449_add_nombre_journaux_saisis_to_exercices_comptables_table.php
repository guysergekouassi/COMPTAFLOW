<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        /*
        Schema::table('exercices_comptables', function (Blueprint $table) {
            $table->unsignedInteger('nombre_journaux_saisis')->default(0)->after('intitule');
        });
        */
    }

    public function down(): void
    {
        Schema::table('exercices_comptables', function (Blueprint $table) {
            $table->dropColumn('nombre_journaux_saisis');
        });
    }
};
