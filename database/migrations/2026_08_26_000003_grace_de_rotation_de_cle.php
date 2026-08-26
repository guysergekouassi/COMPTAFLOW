<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * La clé de liaison cesse d'être éternelle : elle se renouvelle, avec une grâce.
 *
 * Une clé posée une fois et jamais changée ouvre le dossier comptable d'une
 * entreprise aussi longtemps que ce dossier existe. Un prestataire qui a vu
 * passer une requête, une sauvegarde égarée, un journal mal purgé : rien ne
 * referme derrière eux. La rotation ne rend pas une fuite impossible — elle
 * borne sa durée de vie à un mois.
 *
 * ── Pourquoi une clé *précédente*, et pas seulement une clé courante ──────────
 *
 * C'est le seul point délicat du lot, et la seule chose qui l'empêche de casser
 * quelque chose. Un déversement d'écritures **déjà parti** au moment précis du
 * renouvellement porte encore l'ancienne clé et arrivera après elle. Sans
 * période de grâce, cet appel-là échoue — rarement, une fois par mois au pire,
 * et sans que personne comprenne pourquoi. C'est exactement le genre de défaut
 * qu'on met six mois à diagnostiquer, parce qu'il ne se reproduit pas quand on
 * le cherche.
 *
 * Cinq minutes suffisent : le temps qu'une requête en vol se pose. Passé ce
 * délai l'ancienne clé ne vaut plus rien, et les deux colonnes sont vidées —
 * on ne garde pas un demi-secret « au cas où ».
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            if (!Schema::hasColumn('companies', 'selflow_sync_key_hash_precedente')) {
                // Un haché, comme la clé courante : la valeur d'origine n'est
                // déductible d'aucune colonne. Indexée mais **non unique** — une
                // clé qui vient d'être remplacée existe le temps de la grâce
                // dans cette colonne-ci alors qu'elle n'est plus dans l'autre,
                // et un index unique sur les deux ensemble n'existe pas.
                $table->string('selflow_sync_key_hash_precedente', 64)->nullable()
                    ->after('selflow_sync_key_hash')->index();
            }
            if (!Schema::hasColumn('companies', 'selflow_sync_key_precedente_expire_at')) {
                $table->timestamp('selflow_sync_key_precedente_expire_at')->nullable()
                    ->after('selflow_sync_key_hash_precedente');
            }
            if (!Schema::hasColumn('companies', 'selflow_sync_key_rotated_at')) {
                // Ce que la tâche planifiée de Selflow interroge pour savoir
                // quelles clés ont plus de trente jours. Sans elle, seule
                // `selflow_linked_at` daterait la clé — et elle, un
                // reprovisionnement la remet à zéro pour de tout autres raisons.
                $table->timestamp('selflow_sync_key_rotated_at')->nullable()
                    ->after('selflow_sync_key_precedente_expire_at');
            }
        });
    }

    public function down(): void
    {
        // L'index part **avant** sa colonne : SQLite refuse de retirer une
        // colonne encore indexée, et le retour en arrière échouerait à
        // mi-chemin — c'est-à-dire au pire moment, celui où l'on essaie
        // justement de revenir à un état connu.
        Schema::table('companies', function (Blueprint $table) {
            $table->dropIndex(['selflow_sync_key_hash_precedente']);
        });

        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn([
                'selflow_sync_key_hash_precedente',
                'selflow_sync_key_precedente_expire_at',
                'selflow_sync_key_rotated_at',
            ]);
        });
    }
};
