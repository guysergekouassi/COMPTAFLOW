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
        if (!Schema::hasTable('grand_livres_tiers')) {
            Schema::create('grand_livres_tiers', function (Blueprint $table) {
                $table->id();
                $table->date('date_debut');
                $table->date('date_fin');
                $table->foreignId('plan_tiers_id_1')->nullable()->constrained('plan_tiers')->onDelete('cascade');
                $table->foreignId('plan_tiers_id_2')->nullable()->constrained('plan_tiers')->onDelete('cascade');
                $table->string('format')->nullable();
                $table->string('grand_livre_tiers'); // nom du fichier
                $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
                $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('balances_tiers')) {
            Schema::create('balances_tiers', function (Blueprint $table) {
                $table->id();
                $table->date('date_debut');
                $table->date('date_fin');
                $table->foreignId('plan_tiers_id_1')->nullable()->constrained('plan_tiers')->onDelete('cascade');
                $table->foreignId('plan_tiers_id_2')->nullable()->constrained('plan_tiers')->onDelete('cascade');
                $table->string('format')->nullable();
                $table->string('balance_tiers'); // nom du fichier
                $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
                $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('balances_tiers');
        Schema::dropIfExists('grand_livres_tiers');
    }
};
