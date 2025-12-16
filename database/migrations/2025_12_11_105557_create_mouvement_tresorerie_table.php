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
        Schema::create('mouvement_tresorerie', function (Blueprint $table) {
            $table->id();
            //fk
            $table->foreignId('compte_tresorerie_id')->constrained()->onDelete('cascade');

            $table->date('date_mouvement');
            $table->string('reference_piece')->nullable();
            $table->text('libelle');

            // Pour distinguer l'Encaissement du Décaissement
           // Un seul des deux champs sera rempli par ligne

           $table->decimal('montant_debit',15,2)->nullable(); // Décaissement (Sortie)
           $table->decimal('montant_credit',15,2)->nullable(); //Encaissement (Entrée)

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mouvement_tresorerie');
    }
};
