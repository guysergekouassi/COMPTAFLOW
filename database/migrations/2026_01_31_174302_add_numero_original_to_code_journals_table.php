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
            if (!Schema::hasColumn('code_journals', 'numero_original')) {
                $table->string('numero_original')->nullable()->after('code_journal');
            }
            $table->index('numero_original');
            $table->index('code_journal');
        });

        Schema::table('plan_comptables', function (Blueprint $table) {
            $table->index('numero_original');
            $table->index('numero_de_compte');
        });

        Schema::table('plan_tiers', function (Blueprint $table) {
            $table->index('numero_original');
            $table->index('numero_de_tiers');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('plan_tiers', function (Blueprint $table) {
            $table->dropIndex(['numero_original']);
            $table->dropIndex(['numero_de_tiers']);
        });

        Schema::table('plan_comptables', function (Blueprint $table) {
            $table->dropIndex(['numero_original']);
            $table->dropIndex(['numero_de_compte']);
        });

        Schema::table('code_journals', function (Blueprint $table) {
            $table->dropIndex(['numero_original']);
            $table->dropIndex(['code_journal']);
            $table->dropColumn('numero_original');
        });
    }
};
