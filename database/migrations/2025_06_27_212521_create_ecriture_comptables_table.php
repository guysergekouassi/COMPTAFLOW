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
        Schema::create('ecriture_comptables', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->string('n_saisie');
            $table->string('description_operation');
            $table->string('reference_piece')->nullable();


            $table->foreignId('plan_comptable_id')->constrained('plan_comptables')->onDelete('cascade');
            $table->foreignId('plan_tiers_id')->constrained('plan_tiers')->onDelete('cascade');

            $table->boolean('plan_analytique');

            $table->foreignId('code_journal_id')->constrained('code_journals')->onDelete('cascade');


            // $table->foreignId('exercices_comptables_id')->constrained('exercices_comptables')->onDelete('cascade');
            
            // $table->foreignId('journaux_saisis_id')->constrained('journaux_saisis')->onDelete('cascade');


            $table->decimal('debit', 15, 2)->default(0.00);
            $table->decimal('credit', 15, 2)->default(0.00);
            $table->string('piece_justificatif')->nullable();
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
        Schema::dropIfExists('ecriture_comptables');
    }
};
