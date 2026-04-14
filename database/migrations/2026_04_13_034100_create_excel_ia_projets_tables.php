<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Table des projets d'IA Comptable
        Schema::create('excel_ia_projets', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('user_id');
            $table->string('titre');
            $table->text('instructions')->nullable();
            $table->string('couleur', 20)->default('#6366f1');
            $table->timestamps();

            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });

        // 2. Table des fichiers de dépôt de données associés au projet
        Schema::create('excel_ia_projet_fichiers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('projet_id');
            $table->string('nom');
            $table->string('chemin');
            $table->string('mime');
            $table->integer('taille');
            $table->timestamps();

            $table->foreign('projet_id')->references('id')->on('excel_ia_projets')->onDelete('cascade');
        });

        // 3. Liaison avec l'historique d'analyses existant
        Schema::table('excel_ia_analyses', function (Blueprint $table) {
            $table->unsignedBigInteger('projet_id')->nullable()->after('company_id');
            $table->foreign('projet_id')->references('id')->on('excel_ia_projets')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('excel_ia_analyses', function (Blueprint $table) {
            $table->dropForeign(['projet_id']);
            $table->dropColumn('projet_id');
        });
        
        Schema::dropIfExists('excel_ia_projet_fichiers');
        Schema::dropIfExists('excel_ia_projets');
    }
};
