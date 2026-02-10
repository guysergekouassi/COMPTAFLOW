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
        Schema::create('lettrages', function (Blueprint $table) {
            $table->id();
            $table->string('code')->index(); // Code de lettrage (ex: A, B, AB, etc.)
            $table->date('date_lettrage');
            $table->unsignedBigInteger('user_id'); // Utilisateur ayant effectué le lettrage
            $table->unsignedBigInteger('company_id'); // Entreprise concernée
            $table->timestamps();

            // Clés étrangères
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');

            // Unicité du code par entreprise (optionnel, mais recommandé pour éviter les confusions)
            // On peut aussi ajouter l'année si on veut réinitialiser les codes chaque année.
            //$table->unique(['company_id', 'code']); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lettrages');
    }
};
