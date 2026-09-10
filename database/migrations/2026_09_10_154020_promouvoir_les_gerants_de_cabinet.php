<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;

/**
 * Le titulaire d'un Pack Cabinet est le gérant de son espace.
 *
 * Les inscriptions passées lui donnaient le rôle « comptable » : il se
 * retrouvait second dans ses propres comptabilités, privé des pages réservées
 * à l'administrateur. On rétablit le rôle de gérant.
 *
 * Périmètre volontairement étroit, pour ne promouvoir personne par erreur :
 *  - compte inscrit par lui-même (aucun créateur),
 *  - sans rattachement à une entreprise unique (users.company_id vide), ce qui
 *    exclut le Pack Entreprise et les anciens comptes mono-société,
 *  - offre cabinet.
 * Les collaborateurs invités ont toujours un créateur : ils ne sont pas touchés.
 */
return new class extends Migration
{
    public function up(): void
    {
        $gerants = User::where('role', 'comptable')
            ->whereNull('created_by_id')
            ->whereNull('company_id')
            ->where(function ($q) {
                $q->where('pack', 'cabinet')->orWhereNull('pack');
            })
            ->get();

        foreach ($gerants as $gerant) {
            $gerant->role = 'admin';
            $gerant->pack = 'cabinet';
            $gerant->save();

            // Un gérant sans habilitations ne verrait pas la moitié de ses pages.
            $gerant->accorderToutesLesHabilitationsMetier();
        }

        if ($gerants->isNotEmpty()) {
            echo '  Gerants de cabinet retablis : ' . $gerants->count() . PHP_EOL;
        }
    }

    public function down(): void
    {
        // Rétrograder d'anciens gérants ferait plus de dégâts que la migration
        // n'en répare : on ne revient pas en arrière.
    }
};
