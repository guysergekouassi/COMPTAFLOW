<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Les champs qu'une entreprise n'est pas tenue de renseigner.
     *
     * @var array<int, string>
     */
    private const FACULTATIFS = ['adresse', 'code_postal', 'city', 'country', 'phone_number'];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('companies')) {
            return;
        }

        // Voir `2026_02_25_190000` : `MODIFY` ne se lit que sur MySQL, et la
        // suite d'epreuves tourne sur SQLite.
        if (DB::getDriverName() === 'mysql') {
            DB::statement(
                'ALTER TABLE `companies`
                    MODIFY `adresse` VARCHAR(255) NULL,
                    MODIFY `code_postal` VARCHAR(255) NULL,
                    MODIFY `city` VARCHAR(255) NULL,
                    MODIFY `country` VARCHAR(255) NULL,
                    MODIFY `phone_number` VARCHAR(255) NULL'
            );

            return;
        }

        Schema::table('companies', function (Blueprint $table) {
            foreach (self::FACULTATIFS as $colonne) {
                if (Schema::hasColumn('companies', $colonne)) {
                    $table->string($colonne, 255)->nullable()->change();
                }
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('companies')) {
            return;
        }

        if (DB::getDriverName() === 'mysql') {
            DB::statement(
                'ALTER TABLE `companies`
                    MODIFY `adresse` VARCHAR(255) NOT NULL,
                    MODIFY `code_postal` VARCHAR(255) NOT NULL,
                    MODIFY `city` VARCHAR(255) NOT NULL,
                    MODIFY `country` VARCHAR(255) NOT NULL,
                    MODIFY `phone_number` VARCHAR(255) NOT NULL'
            );

            return;
        }

        Schema::table('companies', function (Blueprint $table) {
            foreach (self::FACULTATIFS as $colonne) {
                if (Schema::hasColumn('companies', $colonne)) {
                    $table->string($colonne, 255)->nullable(false)->change();
                }
            }
        });
    }
};
