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
            $table->integer('journal_code_digits')->nullable()->default(3);
            $table->enum('journal_code_type', ['alphabetical', 'alphanumeric', 'numeric'])->default('alphabetical');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn(['journal_code_digits', 'journal_code_type']);
        });
    }
};
