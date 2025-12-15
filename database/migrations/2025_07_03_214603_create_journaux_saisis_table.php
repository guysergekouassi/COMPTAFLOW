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
        Schema::create('journaux_saisis', function (Blueprint $table) {
            $table->id();
            $table->integer('annee');
            $table->integer('mois');

            $table->foreignId('exercices_comptables_id')->constrained('exercices_comptables')->onDelete('cascade');
            $table->foreignId('code_journals_id')->constrained('code_journals')->onDelete('cascade');


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
        Schema::dropIfExists('journaux_saisis');
    }
};
