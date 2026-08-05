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
        try {
            DB::statement("ALTER TABLE `companies` MODIFY `social_capital` DECIMAL(15, 2) NULL DEFAULT NULL");
        } catch (\Throwable $e) {
            // Ignorer si déjà nullable
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        try {
            DB::statement("ALTER TABLE `companies` MODIFY `social_capital` DECIMAL(15, 2) NOT NULL DEFAULT 0.00");
        } catch (\Throwable $e) {
            // Ignorer
        }
    }
};
