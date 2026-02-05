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
        Schema::table('immobilisations', function (Blueprint $table) {
            if (!Schema::hasColumn('immobilisations', 'ecriture_id')) {
                $table->unsignedBigInteger('ecriture_id')->nullable()->after('company_id');
                $table->foreign('ecriture_id')->references('id')->on('ecriture_comptables')->onDelete('set null');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('immobilisations', function (Blueprint $table) {
            $table->dropForeign(['ecriture_id']);
            $table->dropColumn('ecriture_id');
        });
    }
};
