<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ecriture_comptables', function (Blueprint $table) {
            $table->boolean('plan_analytique')->default(false)->change();
        });
    }

    public function down(): void
    {
        Schema::table('ecriture_comptables', function (Blueprint $table) {
            $table->boolean('plan_analytique')->default(null)->change();
        });
    }
};
