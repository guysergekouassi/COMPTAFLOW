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
            $table->integer('journal_code_digits')->default(4)->change();
        });

        // Mettre à jour toutes les entreprises existantes qui sont à 3 vers 4
        \DB::table('companies')->where('journal_code_digits', 3)->update(['journal_code_digits' => 4]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->integer('journal_code_digits')->default(3)->change();
        });
        
        // Note: On ne revient pas en arrière sur les données existantes pour éviter de tronquer des codes
    }
};
