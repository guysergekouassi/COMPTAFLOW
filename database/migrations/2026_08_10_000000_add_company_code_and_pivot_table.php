<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Ajouter company_code à la table companies
        Schema::table('companies', function (Blueprint $table) {
            $table->string('company_code')->nullable()->unique()->after('company_name');
        });

        // 2. Créer la table pivot company_user
        Schema::create('company_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('role')->default('comptable'); // admin, comptable, collaborateur
            $table->timestamps();

            // Unicité de l'association
            $table->unique(['company_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('company_user');

        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn('company_code');
        });
    }
};
