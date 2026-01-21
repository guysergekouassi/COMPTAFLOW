<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
   public function up()
{
    Schema::table('companies', function (Blueprint $table) {
        if (!Schema::hasColumn('companies', 'is_blocked')) {
            $table->boolean('is_blocked')->default(false)->after('is_active');
        }
    });

    Schema::table('users', function (Blueprint $table) {
        if (!Schema::hasColumn('users', 'is_blocked')) {
            $table->boolean('is_blocked')->default(false)->after('is_active');
        }
    });
}

};
