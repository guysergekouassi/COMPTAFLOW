<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
   
    public function up()
{
    if (!Schema::hasColumn('plan_comptables', 'classe')) {
        Schema::table('plan_comptables', function (Blueprint $table) {
            $table->string('classe')->after('intitule')->nullable();
        });
    }
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('plan_comptables', function (Blueprint $table) {
            //
        });
    }
};
