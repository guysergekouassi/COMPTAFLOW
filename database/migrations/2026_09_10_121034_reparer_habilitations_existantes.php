<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Artisan;

/**
 * Réparation des comptes existants.
 *
 * Les habilitations sont figées à la création du compte : les utilisateurs
 * créés avant que le catalogue de permissions soit complet gardent des menus
 * incomplets (Hub des Tiers, Fiche Entreprise, Exercice comptable…), avec des
 * écarts d'un compte à l'autre selon l'écran de création utilisé.
 *
 * La commande est idempotente : elle n'ajoute que ce qui manque et ne retire
 * jamais un droit. Elle peut être relancée à la main :
 *     php artisan habilitations:reparer
 */
return new class extends Migration
{
    public function up(): void
    {
        Artisan::call('habilitations:reparer');
        echo Artisan::output();
    }

    public function down(): void
    {
        // Rien à défaire : on n'a jamais retiré de droit.
    }
};
