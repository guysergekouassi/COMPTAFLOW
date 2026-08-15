<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * La clé d'idempotence du déversement Selflow.
 *
 * `n_saisie` recevait la référence de pièce, ou `SELF_ . time() . _ . $count`
 * à défaut. Ni l'une ni l'autre ne distingue un **renvoi** d'une écriture
 * **nouvelle** : rejouer une synchronisation — après une coupure réseau, après
 * un retry, ou simplement en relançant la commande — dupliquait tout, et la
 * balance doublait sans que rien ne le signale.
 *
 * La clé porte l'identité de l'écriture chez Selflow :
 * `SELFLOW-{entreprise}-{ecriture}`. Elle est unique par entreprise, et un
 * second déversement de la même écriture est reconnu et ignoré.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ecriture_comptables', function (Blueprint $table) {
            $table->string('cle_selflow', 64)->nullable()->after('lettrage_id');

            // Unique par entreprise : deux entreprises peuvent porter la même
            // écriture n° 12 chez Selflow, ce sont deux écritures distinctes.
            $table->unique(['company_id', 'cle_selflow'], 'ecr_cle_selflow_unique');
        });
    }

    public function down(): void
    {
        Schema::table('ecriture_comptables', function (Blueprint $table) {
            $table->dropUnique('ecr_cle_selflow_unique');
            $table->dropColumn('cle_selflow');
        });
    }
};
