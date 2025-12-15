<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('code_journals', function (Blueprint $table) {
            $table->id();
            // $table->year('annee');
            // $table->string('mois');
            $table->string('code_journal');
            $table->string('intitule');
            $table->boolean('traitement_analytique');
            $table->string('type')->nullable();
            $table->string('compte_de_contrepartie')->nullable();
            $table->string('compte_de_tresorerie')->nullable();
            $table->string('rapprochement_sur')->nullable();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->timestamps();
        });
    }

    


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('code_journals');
    }
};
