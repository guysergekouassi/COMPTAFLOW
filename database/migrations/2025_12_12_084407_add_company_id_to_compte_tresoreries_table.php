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
            // Ajoute la colonne company_id (unsignedBigInteger est le type standard pour les clés étrangères/IDs)
            $table->unsignedBigInteger('company_id')->after('id');

        });
    }

    public function down(): void
    {
        Schema::table('compte_tresoreries', function (Blueprint $table) {

            $table->dropColumn('company_id');
        });
    }
};
