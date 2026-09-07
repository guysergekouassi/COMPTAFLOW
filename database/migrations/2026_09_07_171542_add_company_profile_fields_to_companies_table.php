<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Champs de la fiche entreprise saisis à la création mais qui n'étaient
 * stockés nulle part : la page "Paramètres & Informations" les affichait
 * sans jamais pouvoir les enregistrer.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            if (!Schema::hasColumn('companies', 'commune')) {
                $table->string('commune', 120)->nullable()->after('city');
            }
            if (!Schema::hasColumn('companies', 'quartier')) {
                $table->string('quartier', 120)->nullable()->after('commune');
            }
            if (!Schema::hasColumn('companies', 'idu')) {
                $table->string('idu', 100)->nullable()->after('ncc');
            }
            if (!Schema::hasColumn('companies', 'proprietaire_local')) {
                $table->string('proprietaire_local', 255)->nullable()->after('rattachement_dgi');
            }
            if (!Schema::hasColumn('companies', 'reference_cadastrale')) {
                $table->string('reference_cadastrale', 100)->nullable()->after('proprietaire_local');
            }
            if (!Schema::hasColumn('companies', 'logo_path')) {
                $table->string('logo_path', 255)->nullable()->after('company_code');
            }
            if (!Schema::hasColumn('companies', 'sticker_solde_alerte')) {
                $table->unsignedInteger('sticker_solde_alerte')->default(5)->after('reference_cadastrale');
            }
        });
    }

    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            foreach ([
                'commune',
                'quartier',
                'idu',
                'proprietaire_local',
                'reference_cadastrale',
                'logo_path',
                'sticker_solde_alerte',
            ] as $column) {
                if (Schema::hasColumn('companies', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
