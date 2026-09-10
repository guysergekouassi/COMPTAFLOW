<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Offre souscrite.
 *
 *  - « entreprise » : une seule comptabilité, celle créée à l'inscription.
 *    Pas d'espace cabinet, ni création d'une autre société, ni fusion.
 *  - « cabinet » : espace multi-dossiers, sans limite de sociétés.
 *
 * Les comptes existants sont tous des cabinets : c'est la valeur par défaut,
 * elle laisse leur fonctionnement inchangé.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'pack')) {
                $table->string('pack', 20)->default('cabinet')->after('role');
            }
        });

        Schema::table('companies', function (Blueprint $table) {
            if (!Schema::hasColumn('companies', 'pack')) {
                $table->string('pack', 20)->default('cabinet')->after('company_code');
            }
        });

        DB::table('users')->whereNull('pack')->update(['pack' => 'cabinet']);
        DB::table('companies')->whereNull('pack')->update(['pack' => 'cabinet']);
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'pack')) {
                $table->dropColumn('pack');
            }
        });

        Schema::table('companies', function (Blueprint $table) {
            if (Schema::hasColumn('companies', 'pack')) {
                $table->dropColumn('pack');
            }
        });
    }
};
