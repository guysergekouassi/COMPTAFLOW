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
        Schema::table('excel_ia_projet_fichiers', function (Blueprint $table) {
            $table->longText('contenu_extrait')->nullable()->after('taille');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('excel_ia_projet_fichiers', function (Blueprint $table) {
            $table->dropColumn('contenu_extrait');
        });
    }
};
