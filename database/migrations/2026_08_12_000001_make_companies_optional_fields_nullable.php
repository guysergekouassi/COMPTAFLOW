<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('companies')) {
            DB::statement(
                'ALTER TABLE `companies`
                    MODIFY `adresse` VARCHAR(255) NULL,
                    MODIFY `code_postal` VARCHAR(255) NULL,
                    MODIFY `city` VARCHAR(255) NULL,
                    MODIFY `country` VARCHAR(255) NULL,
                    MODIFY `phone_number` VARCHAR(255) NULL'
            );
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('companies')) {
            DB::statement(
                'ALTER TABLE `companies`
                    MODIFY `adresse` VARCHAR(255) NOT NULL,
                    MODIFY `code_postal` VARCHAR(255) NOT NULL,
                    MODIFY `city` VARCHAR(255) NOT NULL,
                    MODIFY `country` VARCHAR(255) NOT NULL,
                    MODIFY `phone_number` VARCHAR(255) NOT NULL'
            );
        }
    }
};
