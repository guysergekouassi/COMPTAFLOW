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
        Schema::table('companies', function (Blueprint $table) {
            // Configuration pour la numérotation des tiers
            if (!Schema::hasColumn('companies', 'tier_digits')) {
                $table->integer('tier_digits')->default(6)->after('journal_code_type');
            }
            
            if (!Schema::hasColumn('companies', 'tier_id_type')) {
                $table->enum('tier_id_type', ['numeric', 'alphanumeric'])->default('numeric')->after('tier_digits');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            if (Schema::hasColumn('companies', 'tier_id_type')) {
                $table->dropColumn('tier_id_type');
            }
            
            if (Schema::hasColumn('companies', 'tier_digits')) {
                $table->dropColumn('tier_digits');
            }
        });
    }
};
