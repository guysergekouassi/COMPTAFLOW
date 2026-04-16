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
        if (Schema::hasTable('admin_tasks')) {
            Schema::table('admin_tasks', function (Blueprint $table) {
                if (!Schema::hasColumn('admin_tasks', 'file_path')) {
                    $table->string('file_path')->nullable()->after('description');
                }
                // $table->unsignedBigInteger('assigned_to')->nullable()->change();
            });
        }

        if (!Schema::hasTable('admin_task_user')) {
            Schema::create('admin_task_user', function (Blueprint $table) {
                $table->id();
                $table->foreignId('admin_task_id')->constrained('admin_tasks')->onDelete('cascade');
                $table->unsignedBigInteger('user_id'); 
                $table->enum('status', ['pending', 'in_progress', 'completed'])->default('pending');
                $table->timestamp('read_at')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admin_task_user');
        Schema::table('admin_tasks', function (Blueprint $table) {
            $table->dropColumn('file_path');
        });
    }
};
