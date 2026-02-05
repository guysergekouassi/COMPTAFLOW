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
        Schema::table('plan_tiers', function (Blueprint $table) {
            $table->string('numero_original')->nullable()->after('numero_de_tiers');
        });

        Schema::table('plan_comptables', function (Blueprint $table) {
            $table->string('numero_original')->nullable()->after('numero_de_compte');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('plan_tiers', function (Blueprint $table) {
            $table->dropColumn('numero_original');
        });

        Schema::table('plan_comptables', function (Blueprint $table) {
            $table->dropColumn('numero_original');
        });
    }
};
