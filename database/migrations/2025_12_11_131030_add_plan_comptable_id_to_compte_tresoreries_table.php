<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('compte_tresoreries', function (Blueprint $table) {
          if (!Schema::hasColumn('compte_tresoreries', 'plan_comptable_id')) {
                $table->foreignId('plan_comptable_id')->nullable()->constrained()->after('type');
                
            }
        });
    }

    public function down(): void
    {
        Schema::table('compte_tresoreries', function (Blueprint $table) {
            $table->dropForeign(['plan_comptable_id']);
            $table->dropColumn('plan_comptable_id');
        });
    }
};
