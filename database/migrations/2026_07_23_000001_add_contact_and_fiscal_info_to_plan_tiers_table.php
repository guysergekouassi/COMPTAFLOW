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
        Schema::table('plan_tiers', function (Blueprint $table) {
            if (!Schema::hasColumn('plan_tiers', 'ncc')) {
                $table->string('ncc')->nullable()->after('type_de_tiers');
            }
            if (!Schema::hasColumn('plan_tiers', 'rccm')) {
                $table->string('rccm')->nullable()->after('ncc');
            }
            if (!Schema::hasColumn('plan_tiers', 'compte_contribuable')) {
                $table->string('compte_contribuable')->nullable()->after('rccm');
            }
            if (!Schema::hasColumn('plan_tiers', 'regime')) {
                $table->string('regime')->nullable()->after('compte_contribuable');
            }
            if (!Schema::hasColumn('plan_tiers', 'email')) {
                $table->string('email')->nullable()->after('regime');
            }
            if (!Schema::hasColumn('plan_tiers', 'telephone')) {
                $table->string('telephone')->nullable()->after('email');
            }
            if (!Schema::hasColumn('plan_tiers', 'adresse')) {
                $table->string('adresse')->nullable()->after('telephone');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('plan_tiers', function (Blueprint $table) {
            $table->dropColumn(['ncc', 'rccm', 'compte_contribuable', 'regime', 'email', 'telephone', 'adresse']);
        });
    }
};
