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
        Schema::table('compte_tresoreries', function (Blueprint $table) {
            $table->string('syscohada_line_id')->nullable()->after('category_id')->comment('Code ligne SYSCOHADA (ex: INV_ACQ, FIN_EMP)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('compte_tresoreries', function (Blueprint $table) {
            $table->dropColumn('syscohada_line_id');
        });
    }
};
