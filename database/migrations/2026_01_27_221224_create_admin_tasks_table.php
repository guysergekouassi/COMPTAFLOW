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
        if (!Schema::hasTable('admin_tasks')) {
        Schema::create('admin_tasks', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            
            // Assignation
            $table->foreignId('assigned_to')->nullable()->constrained('users')->onDelete('cascade');
            $table->foreignId('assigned_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('company_id')->nullable()->constrained('companies')->onDelete('cascade');
            
            // Statut et priorité
            $table->enum('status', ['pending', 'in_progress', 'completed', 'cancelled'])->default('pending');
            $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium');
            
            // Dates
            $table->dateTime('due_date')->nullable();
            $table->dateTime('completed_at')->nullable();
            
            $table->timestamps();
            
            // Index pour les recherches fréquentes
            $table->index(['status', 'priority']);
            $table->index('assigned_to');
            $table->index('company_id');
        });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admin_tasks');
    }
};
