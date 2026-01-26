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
        Schema::table('companies', function (Blueprint $table) {
            if (!Schema::hasColumn('companies', 'is_blocked')) {
                $table->boolean('is_blocked')->default(false)->after('is_active');
            }
            if (!Schema::hasColumn('companies', 'block_reason')) {
                $table->string('block_reason')->nullable()->after('is_blocked');
            }
            if (!Schema::hasColumn('companies', 'blocked_at')) {
                $table->timestamp('blocked_at')->nullable()->after('block_reason');
            }
            if (!Schema::hasColumn('companies', 'blocked_by')) {
                $table->unsignedBigInteger('blocked_by')->nullable()->after('blocked_at');
            }
        });

        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'is_blocked')) {
                $table->boolean('is_blocked')->default(false)->after('role');
            }
            if (!Schema::hasColumn('users', 'block_reason')) {
                $table->string('block_reason')->nullable()->after('is_blocked');
            }
            if (!Schema::hasColumn('users', 'blocked_at')) {
                $table->timestamp('blocked_at')->nullable()->after('block_reason');
            }
            if (!Schema::hasColumn('users', 'blocked_by')) {
                $table->unsignedBigInteger('blocked_by')->nullable()->after('blocked_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn(['is_blocked', 'block_reason', 'blocked_at', 'blocked_by']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['is_blocked', 'block_reason', 'blocked_at', 'blocked_by']);
        });
    }
};
