<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Correction du code_tableau car le CSV disait "ACTIF" mais l'application cherche "BILAN_ACTIF"
        DB::table('liasse_mappings')
            ->where('code_tableau', 'ACTIF')
            ->update(['code_tableau' => 'BILAN_ACTIF']);
            
        DB::table('liasse_mappings')
            ->where('code_tableau', 'PASSIF')
            ->update(['code_tableau' => 'BILAN_PASSIF']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('liasse_mappings')
            ->where('code_tableau', 'BILAN_ACTIF')
            ->update(['code_tableau' => 'ACTIF']);
            
        DB::table('liasse_mappings')
            ->where('code_tableau', 'BILAN_PASSIF')
            ->update(['code_tableau' => 'PASSIF']);
    }
};
