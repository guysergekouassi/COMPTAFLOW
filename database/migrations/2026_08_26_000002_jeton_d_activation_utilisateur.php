<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Un jeton d'activation, pour créer un compte sans transporter de mot de passe.
 *
 * `POST /api/external/register-enterprise` exigeait `admin_password` : le
 * superadministrateur Selflow choisissait le mot de passe du compte d'un
 * client, et cette valeur traversait la passerelle **en clair** dans le corps
 * de la requête — journalisée au passage par tout ce qui trace les appels
 * sortants, et connue d'une personne qui n'avait aucune raison de la connaître.
 *
 * `companies/provision`, qui la remplace, ne transmet aucun mot de passe. Le
 * compte est créé avec un secret aléatoire que personne ne détient, et son
 * titulaire choisit le sien depuis un lien envoyé à son adresse.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'activation_token')) {
                // Le jeton est stocké haché : la table `users` est lue par bien
                // plus de code que ce lot, et un jeton en clair vaut un mot de
                // passe tant qu'il n'a pas servi.
                $table->string('activation_token', 64)->nullable()->unique();
            }
            if (!Schema::hasColumn('users', 'activation_token_expires_at')) {
                $table->timestamp('activation_token_expires_at')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['activation_token', 'activation_token_expires_at']);
        });
    }
};
