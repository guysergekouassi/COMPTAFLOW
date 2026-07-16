<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rapports_grand_livre_analytiques', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('axe_analytique_id')->nullable();
            $table->boolean('tous_axes')->default(false);
            $table->unsignedBigInteger('section_de_id')->nullable();
            $table->unsignedBigInteger('section_a_id')->nullable();
            // Libellés dénormalisés pour affichage sans jointure
            $table->string('axe_libelle')->nullable();
            $table->string('section_de_libelle')->nullable();
            $table->string('section_a_libelle')->nullable();
            $table->boolean('toutes_sections')->default(false);
            $table->date('date_debut')->nullable();
            $table->date('date_fin')->nullable();
            $table->boolean('toute_periode')->default(false);
            $table->enum('format', ['pdf', 'excel'])->default('pdf');
            $table->string('fichier')->nullable();
            $table->unsignedInteger('nb_mouvements')->default(0);
            $table->timestamps();

            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->index(['company_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rapports_grand_livre_analytiques');
    }
};
