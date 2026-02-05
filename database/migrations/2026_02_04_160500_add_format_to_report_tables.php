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
        if (Schema::hasTable('grand_livres')) {
            Schema::table('grand_livres', function (Blueprint $table) {
                if (!Schema::hasColumn('grand_livres', 'plan_comptable_id_1')) {
                    $table->foreignId('plan_comptable_id_1')->after('date_fin')->nullable()->constrained('plan_comptables')->onDelete('cascade');
                }
                if (!Schema::hasColumn('grand_livres', 'plan_comptable_id_2')) {
                    $table->foreignId('plan_comptable_id_2')->after('plan_comptable_id_1')->nullable()->constrained('plan_comptables')->onDelete('cascade');
                }
                if (!Schema::hasColumn('grand_livres', 'format')) {
                    $table->string('format')->after('plan_comptable_id_2')->nullable();
                }
            });
        }

        if (Schema::hasTable('balances')) {
            Schema::table('balances', function (Blueprint $table) {
                if (Schema::hasColumn('balances', 'code_journals_id')) {
                    $table->dropForeign(['code_journals_id']);
                    $table->dropColumn('code_journals_id');
                }
                if (!Schema::hasColumn('balances', 'plan_comptable_id_1')) {
                    $table->foreignId('plan_comptable_id_1')->after('date_fin')->nullable()->constrained('plan_comptables')->onDelete('cascade');
                }
                if (!Schema::hasColumn('balances', 'plan_comptable_id_2')) {
                    $table->foreignId('plan_comptable_id_2')->after('plan_comptable_id_1')->nullable()->constrained('plan_comptables')->onDelete('cascade');
                }
                if (!Schema::hasColumn('balances', 'format')) {
                    $table->string('format')->after('plan_comptable_id_2')->nullable();
                }
                if (!Schema::hasColumn('balances', 'type')) {
                    $table->string('type')->after('format')->nullable();
                }
            });
        }

        if (Schema::hasTable('grand_livres_tiers')) {
            Schema::table('grand_livres_tiers', function (Blueprint $table) {
                if (!Schema::hasColumn('grand_livres_tiers', 'plan_tiers_id_1')) {
                    $table->foreignId('plan_tiers_id_1')->after('date_fin')->nullable()->constrained('plan_tiers')->onDelete('cascade');
                }
                if (!Schema::hasColumn('grand_livres_tiers', 'plan_tiers_id_2')) {
                    $table->foreignId('plan_tiers_id_2')->after('plan_tiers_id_1')->nullable()->constrained('plan_tiers')->onDelete('cascade');
                }
                if (!Schema::hasColumn('grand_livres_tiers', 'format')) {
                    $table->string('format')->after('plan_tiers_id_2')->nullable();
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('grand_livres')) {
            Schema::table('grand_livres', function (Blueprint $table) {
                if (Schema::hasColumn('grand_livres', 'format')) {
                    $table->dropColumn('format');
                }
            });
        }

        if (Schema::hasTable('balances')) {
            Schema::table('balances', function (Blueprint $table) {
                if (Schema::hasColumn('balances', 'format')) {
                    $table->dropColumn('format');
                }
            });
        }

        if (Schema::hasTable('grand_livres_tiers')) {
            Schema::table('grand_livres_tiers', function (Blueprint $table) {
                if (Schema::hasColumn('grand_livres_tiers', 'format')) {
                    $table->dropColumn('format');
                }
            });
        }
    }
};
