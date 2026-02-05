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
            $table->foreignId('category_id')->nullable()->after('type')->constrained('treasury_categories')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('compte_tresoreries', function (Blueprint $table) {
            $table->dropConstrainedForeignId('category_id');
        });
    }
};
