<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGrandLivresTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('grand_livres')) {
        Schema::create('grand_livres', function (Blueprint $table) {
            $table->id();

            $table->date('date_debut');
            $table->date('date_fin');

            $table->foreignId('plan_comptable_id_1')->constrained('plan_comptables')->onDelete('cascade');
            $table->foreignId('plan_comptable_id_2')->constrained('plan_comptables')->onDelete('cascade');
            $table->string('grand_livre'); // nom du fichier PDF


            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');

            $table->timestamps();

            // Clés étrangères

        });
    }
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('grand_livres');
    }
}
