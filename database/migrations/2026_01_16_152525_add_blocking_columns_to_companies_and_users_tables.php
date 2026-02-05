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
        /*
        Schema::table('companies', function (Blueprint $table) {
            $table->boolean('is_blocked')->default(false)->after('is_active');
            $table->string('block_reason')->nullable()->after('is_blocked');
            $table->timestamp('blocked_at')->nullable()->after('block_reason');
            $table->unsignedBigInteger('blocked_by')->nullable()->after('blocked_at');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_blocked')->default(false)->after('role');
            $table->string('block_reason')->nullable()->after('is_blocked');
            $table->timestamp('blocked_at')->nullable()->after('block_reason');
            $table->unsignedBigInteger('blocked_by')->nullable()->after('blocked_at');
        });
        */
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
