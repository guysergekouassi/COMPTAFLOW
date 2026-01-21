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
        Schema::create('approvals', function (Blueprint $table) {
            $table->id();
            $table->string('approvable_type')->nullable(); // Model class
            $table->unsignedBigInteger('approvable_id')->nullable(); // Model ID
            $table->string('type'); // e.g., 'new_entry', 'user_access', 'company_creation'
            $table->json('data')->nullable(); // Changes to be applied if approved
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->unsignedBigInteger('requested_by')->nullable();
            $table->unsignedBigInteger('handled_by')->nullable();
            $table->text('comment')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('approvals');
    }
};
