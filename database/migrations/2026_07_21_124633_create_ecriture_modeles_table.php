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
        Schema::create('ecriture_modeles', function (Blueprint $table) {
            $table->id();
            $table->string('nom');
            $table->foreignId('code_journal_id')->nullable()->constrained('code_journals')->onDelete('set null');
            $table->json('lignes'); // [{plan_comptable_id, plan_tiers_id, debit, credit, poste_tresorerie_id}, ...]
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ecriture_modeles');
    }
};
