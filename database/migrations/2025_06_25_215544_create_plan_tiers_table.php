<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('plan_tiers', function (Blueprint $table) {
            $table->id();

            $table->string('numero_de_tiers')->unique();
            $table->foreignId('compte_general')->constrained('plan_comptable')->onDelete('cascade'); // correspond à plan_comptable_id
            $table->string('intitule');
            $table->string('type_de_tiers');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->timestamps();

            
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plan_tiers');
    }
};
