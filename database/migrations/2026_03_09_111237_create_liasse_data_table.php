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
        Schema::create('liasse_data', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('exercice_id');
            $table->string('page_code'); // ex: BILAN_ACTIF, NOTE_1, ESS
            $table->string('field_code'); // ex: BA_TOTAL, N1_STOCK_MAT
            $table->longText('value')->nullable(); // JSON ou Valeur brute
            $table->timestamps();

            $table->index(['company_id', 'exercice_id', 'page_code']);
            $table->unique(['company_id', 'exercice_id', 'page_code', 'field_code'], 'liasse_unique_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('liasse_data');
    }
};
