<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migration
     */
    public function up(): void
    {
        Schema::create('compte_tresoreries', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type')->default('banque');
            $table->decimal('solde_initial',15 ,2)->nullable();;
            $table->decimal('solde_actuel',15,2)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('compte_tresoreries');
    }
};
