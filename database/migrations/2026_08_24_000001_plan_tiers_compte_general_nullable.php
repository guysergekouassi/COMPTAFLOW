<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * `plan_tiers.compte_general` devient facultatif.
 *
 * La colonne était `NOT NULL` avec une clé étrangère
 * (`2025_06_25_215544_create_plan_tiers_table.php:18`), et deux chemins
 * d'écriture ne la renseignaient pas :
 *
 * - `MasterTiersImport::model()` ne la posait jamais : **toute importation de
 *   tiers violait la contrainte d'intégrité**, même avec un fichier correct ;
 * - `ExternalSyncController::linkCompany()` y écrit `$compteGeneralGlient?->id`,
 *   qui vaut `null` tant que l'entreprise n'a pas de compte collectif — la
 *   liaison échouait alors sur la première fiche.
 *
 * Le compte collectif est désormais déduit du préfixe du numéro de tiers
 * partout où c'est possible. Là où il ne l'est pas, un tiers sans compte
 * général vaut mieux qu'un import qui échoue : le comptable le rattache
 * ensuite depuis l'écran du plan de tiers.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('plan_tiers', function (Blueprint $table) {
            $table->foreignId('compte_general')->nullable()->change();
        });
    }

    public function down(): void
    {
        // On ne remet pas la contrainte : les lignes créées entre-temps sans
        // compte général la feraient échouer, et le rollback casserait la base
        // au lieu de la remettre en état.
    }
};
