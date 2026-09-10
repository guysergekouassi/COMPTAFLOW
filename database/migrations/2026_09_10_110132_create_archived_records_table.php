<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Archive des suppressions : toute donnée supprimée (individuellement ou en lot)
 * y est conservée 30 jours, avec son contenu complet, avant purge définitive.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('archived_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->nullable()->index();
            $table->foreignId('user_id')->nullable()->index();   // qui a supprimé
            $table->string('model_type');                        // classe du modèle supprimé
            $table->unsignedBigInteger('model_id')->nullable();  // son identifiant d'origine
            $table->string('label')->nullable();                 // description lisible
            $table->json('data')->nullable();                    // contenu complet avant suppression
            $table->uuid('batch_id')->nullable()->index();       // regroupe une suppression en lot
            $table->unsignedInteger('batch_size')->default(1);
            $table->string('ip_address')->nullable();
            $table->timestamp('deleted_at')->nullable()->index();
            $table->timestamp('expires_at')->nullable()->index(); // deleted_at + 30 jours
            $table->timestamps();

            $table->index(['company_id', 'model_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('archived_records');
    }
};
