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
        Schema::table('users', function (Blueprint $table) {
            // Type de Super Admin : 'primary' (principal, non supprimable) ou 'secondary' (limité à certaines entreprises)
            $table->enum('super_admin_type', ['primary', 'secondary'])->nullable()->after('role');
            
            // Liste des IDs d'entreprises supervisées (pour les SA secondaires uniquement)
            // Format JSON: [1, 5, 12] pour superviser les entreprises 1, 5 et 12
            $table->json('supervised_companies')->nullable()->after('super_admin_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['super_admin_type', 'supervised_companies']);
        });
    }
};
