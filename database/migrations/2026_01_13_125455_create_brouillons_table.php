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
        if (!Schema::hasTable('brouillons')) {
            Schema::create('brouillons', function (Blueprint $table) {
                $table->id();
                $table->string('batch_id')->index();
                $table->string('source')->default('manuel'); // manuel, scan
                $table->date('date')->nullable();
                $table->string('n_saisie')->nullable();
                $table->string('description_operation')->nullable();
                $table->string('reference_piece')->nullable();
                $table->unsignedBigInteger('plan_comptable_id')->nullable();
                $table->unsignedBigInteger('plan_tiers_id')->nullable();
                $table->unsignedBigInteger('compte_tresorerie_id')->nullable();
                $table->enum('type_flux', ['debit', 'credit'])->nullable();
                $table->boolean('plan_analytique')->default(false);
                $table->unsignedBigInteger('code_journal_id')->nullable();
                $table->unsignedBigInteger('exercices_comptables_id')->nullable();
                $table->unsignedBigInteger('journaux_saisis_id')->nullable();
                $table->decimal('debit', 15, 2)->default(0);
                $table->decimal('credit', 15, 2)->default(0);
                $table->string('piece_justificatif')->nullable();
                
                // Note: Foreign keys are manually handled to avoid environment-specific errors
                $table->unsignedBigInteger('user_id')->index();
                $table->unsignedBigInteger('company_id')->index();
                
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('brouillons');
    }
};
