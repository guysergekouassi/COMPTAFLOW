<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
   
   public function up(): void
{
    Schema::table('ecriture_comptables', function (Blueprint $table) {
        $table->foreignId('compte_tresorerie_id')
              ->nullable()
              ->after('plan_tiers_id') // Pour le placer logiquement
              ->constrained('compte_tresoreries')
              ->onDelete('set null');
    });
}

public function down(): void
{
    Schema::table('ecriture_comptables', function (Blueprint $table) {
        $table->dropForeign(['compte_tresorerie_id']);
        $table->dropColumn('compte_tresorerie_id');
    });
}
};
