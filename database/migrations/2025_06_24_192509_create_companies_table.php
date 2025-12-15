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
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->string('company_name');
            $table->string('activity');
            $table->string('juridique_form');
            $table->decimal('social_capital', 15, 2);
            $table->string('adresse');
            $table->string('code_postal');
            $table->string('city');
            $table->string('country');
            $table->string('phone_number');
            $table->string('email_adresse', 191)->unique();
            $table->string('identification_TVA')->nullable();
            $table->boolean('is_blocked')->default(false);
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};
