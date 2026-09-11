<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Voir `2026_02_25_190000` : `MODIFY` ne se lit que sur MySQL.
        if (DB::getDriverName() === 'mysql') {
            try {
                DB::statement("ALTER TABLE `companies` MODIFY `social_capital` DECIMAL(15, 2) NULL DEFAULT NULL");
            } catch (\Throwable $e) {
                // Ignorer si déjà nullable
            }

            return;
        }

        Schema::table('companies', function (Blueprint $table) {
            $table->decimal('social_capital', 15, 2)->nullable()->default(null)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            try {
                DB::statement("ALTER TABLE `companies` MODIFY `social_capital` DECIMAL(15, 2) NOT NULL DEFAULT 0.00");
            } catch (\Throwable $e) {
                // Ignorer
            }

            return;
        }

        Schema::table('companies', function (Blueprint $table) {
            $table->decimal('social_capital', 15, 2)->default(0)->change();
        });
    }
};
