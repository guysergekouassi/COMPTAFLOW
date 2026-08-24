<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * `companies.tier_digits` vaut 6 — la convention de la passerelle Selflow.
 *
 * Deux migrations se contredisaient : `2026_01_23_162047` pose la colonne à
 * **8**, `2026_01_27_223941` la voulait à **6** mais ne s'exécute que si la
 * colonne n'existe pas encore — elle ne s'est donc jamais appliquée.
 *
 * À huit chiffres, les numéros de tiers de Selflow — six caractères — ne
 * correspondent à rien : chaque écriture déversée retombe sur le compte
 * collectif, et le relevé d'un client donné redevient impossible à établir.
 *
 * Les entreprises dont le plan de tiers porte déjà des numéros plus longs que
 * six caractères ne sont **pas** touchées : leur numérotation est en place, la
 * changer sous elles ferait deux formats dans le même plan.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->integer('tier_digits')->default(6)->change();
        });

        $aLaisser = DB::table('plan_tiers')
            ->select('company_id')
            ->whereRaw('LENGTH(numero_de_tiers) > 6')
            ->distinct()
            ->pluck('company_id');

        DB::table('companies')
            ->whereNotIn('id', $aLaisser)
            ->where(function ($q) {
                $q->where('tier_digits', '!=', 6)->orWhereNull('tier_digits');
            })
            ->update(['tier_digits' => 6]);
    }

    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->integer('tier_digits')->default(8)->change();
        });
    }
};
