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
        Schema::table('ecriture_comptables', function (Blueprint $table) {
            $table->unsignedBigInteger('lettrage_id')->nullable()->after('statut');
            $table->foreign('lettrage_id')->references('id')->on('lettrages')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ecriture_comptables', function (Blueprint $table) {
            $table->dropForeign(['lettrage_id']);
            $table->dropColumn('lettrage_id');
        });
    }
};
