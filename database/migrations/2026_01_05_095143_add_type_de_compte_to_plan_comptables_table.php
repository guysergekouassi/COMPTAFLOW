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
        if (!Schema::hasColumn('plan_comptables', 'type_de_compte')) {
            Schema::table('plan_comptables', function (Blueprint $table) {
                $table->string('type_de_compte')->nullable()->after('intitule');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('plan_comptables', function (Blueprint $table) {
            $table->dropColumn('type_de_compte');
        });
    }
};
