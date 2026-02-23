<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Table de mémorisation des mappings fournisseur → compte
        Schema::create('ia_mappings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->string('tiers_nom'); // Nom du fournisseur/client détecté
            $table->string('tiers_nif')->nullable(); // NIF si disponible
            $table->unsignedBigInteger('plan_tiers_id')->nullable(); // Lien vers le tiers existant
            $table->string('compte_numero'); // Compte comptable associé
            $table->string('compte_libelle')->nullable();
            $table->integer('confiance')->default(1); // Score de confiance (1-10)
            $table->integer('utilisations')->default(1); // Nombre de fois utilisé
            $table->timestamps();

            $table->index(['company_id', 'tiers_nom']);
        });

        // Table de logs pour audit et statistiques IA
        Schema::create('ia_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('user_id');
            $table->string('image_hash')->nullable(); // Hash MD5 de l'image
            $table->string('image_nom')->nullable(); // Nom du fichier original
            $table->integer('prompt_tokens')->nullable();
            $table->integer('response_tokens')->nullable();
            $table->longText('json_brut')->nullable(); // Réponse brute de l'IA
            $table->longText('json_final')->nullable(); // JSON après corrections utilisateur
            $table->enum('status', ['success', 'error', 'corrected'])->default('success');
            $table->string('erreur_message')->nullable();
            $table->integer('taux_correction')->nullable(); // % de champs corrigés par l'utilisateur
            $table->timestamps();

            $table->index(['company_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ia_logs');
        Schema::dropIfExists('ia_mappings');
    }
};
