<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBalancesTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('balances')) {
        Schema::create('balances', function (Blueprint $table) {
            $table->id();

            $table->date('date_debut');
            $table->date('date_fin');

            $table->foreignId('code_journals_id')->constrained('code_journals')->onDelete('cascade');
            $table->string('balance'); // nom du fichier PDF


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
        Schema::dropIfExists('balances');
    }
}
