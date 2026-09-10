<?php

use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;

/**
 * Le titulaire d'un Pack Entreprise est le gérant de sa comptabilité.
 *
 * Il la crée en souscrivant : il en est le premier responsable, au même titre
 * qu'un gérant de cabinet l'est des siennes. Ce qui sépare les deux offres
 * n'est pas le rôle, mais l'espace cabinet, absent du Pack Entreprise.
 *
 * Périmètre : compte inscrit par lui-même, encore comptable, et créateur d'au
 * moins une comptabilité. Un collaborateur invité a toujours un créateur et ne
 * crée pas de comptabilité : il n'est pas concerné.
 */
return new class extends Migration
{
    public function up(): void
    {
        $titulaires = User::where('role', 'comptable')
            ->whereNull('created_by_id')
            ->whereExists(function ($q) {
                $q->selectRaw('1')->from('companies')->whereColumn('companies.user_id', 'users.id');
            })
            ->get();

        foreach ($titulaires as $titulaire) {
            $titulaire->role = 'admin';
            $titulaire->save();

            // Sans habilitations, le gérant perdrait des pages entières.
            $titulaire->accorderToutesLesHabilitationsMetier();

            // Le rattachement de responsable lui ouvre l'exercice comptable.
            foreach (Company::where('user_id', $titulaire->id)->get() as $company) {
                $company->associatedUsers()->syncWithoutDetaching([$titulaire->id => ['role' => 'admin']]);
            }
        }

        if ($titulaires->isNotEmpty()) {
            echo '  Titulaires promus gerants : ' . $titulaires->count() . PHP_EOL;
        }
    }

    public function down(): void
    {
        // Rétrograder ces gérants les priverait de leur propre comptabilité.
    }
};
