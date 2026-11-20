<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Retire la fiche RH-FLOW « pending » que l'écran Liaison COMPTA-APK créait
 * d'office à chaque affichage.
 *
 * Seule la fiche restée intacte est retirée : code `rh-flow`, statut pending,
 * mode planned, sans URL, sans code de liaison, sans dossier lié, sans jeton
 * ni demande. Une fiche que le Super Admin a complétée n'est pas touchée.
 * Les événements d'audit sont conservés (détachés), comme à la suppression
 * depuis l'écran.
 */
return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('integration_applications')) {
            return;
        }

        $requete = DB::table('integration_applications')
            ->where('code', 'rh-flow')->where('type', 'maison')
            ->where('status', 'pending')->where('auth_mode', 'planned')
            ->whereNull('client_id');
        foreach (['app_url', 'pairing_code_hash'] as $colonne) {
            if (Schema::hasColumn('integration_applications', $colonne)) {
                $requete->whereNull($colonne);
            }
        }
        $fiche = $requete->first();
        if (!$fiche) {
            return;
        }

        foreach (['integration_application_companies', 'integration_api_tokens', 'integration_company_requests'] as $table) {
            if (Schema::hasTable($table) && DB::table($table)->where('integration_application_id', $fiche->id)->exists()) {
                return;
            }
        }

        DB::transaction(function () use ($fiche) {
            if (Schema::hasTable('integration_api_events')) {
                DB::table('integration_api_events')->where('integration_application_id', $fiche->id)
                    ->update(['integration_application_id' => null]);
            }
            DB::table('integration_applications')->where('id', $fiche->id)->delete();
        });
    }

    public function down(): void
    {
        // Rien à rétablir : la fiche était vide et n'était qu'un pense-bête.
    }
};
