<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('compte_tresoreries', function (Blueprint $table) {
            // Modifie la colonne existante pour accepter NULL
            $table->foreignId('company_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('compte_tresoreries', function (Blueprint $table) {
            // Optionnel : Revenir à NOT NULL (si nécessaire pour le rollback)
            // Attention : Si la colonne contient des NULL, cela échouera.
            $table->foreignId('company_id')->nullable(false)->change();
        });
    }
};
