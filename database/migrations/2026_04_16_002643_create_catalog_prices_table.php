<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('catalog_prices', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['app', 'service']); // 'app' ou 'service'
            $table->string('app_name')->nullable();    // RHFLOW, COMPTAFLOW, etc.
            $table->string('pack_name');               // Basic, Pro, etc.
            $table->decimal('prix_mensuel', 15, 2)->nullable(); // null = sur mesure
            $table->boolean('sur_mesure')->default(false);
            $table->boolean('actif')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['type', 'app_name', 'pack_name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('catalog_prices');
    }
};
