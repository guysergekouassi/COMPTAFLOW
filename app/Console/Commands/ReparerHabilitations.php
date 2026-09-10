<?php

namespace App\Console\Commands;

use App\Models\Company;
use App\Models\ExerciceComptable;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Répare les habilitations des comptes déjà existants.
 *
 * Les droits sont attribués à la création du compte : les utilisateurs créés
 * avant que le catalogue de permissions soit complet gardent donc des menus
 * incomplets (Hub des Tiers, Fiche Entreprise, Exercice comptable…), avec des
 * écarts d'un compte à l'autre selon l'écran par lequel ils ont été créés.
 * Cette commande remet tout le monde au même niveau, sans jamais retirer de droit.
 */
class ReparerHabilitations extends Command
{
    protected $signature = 'habilitations:reparer
                            {--dry-run : Affiche ce qui serait fait sans rien modifier}';

    protected $description = "Donne toutes les habilitations aux responsables d'une comptabilité et complète celles des autres utilisateurs";

    public function handle(): int
    {
        $simulation = (bool) $this->option('dry-run');

        if ($simulation) {
            $this->warn('Mode simulation : aucune modification ne sera enregistrée.');
        }

        $toutesLesCles = $this->catalogue();
        $this->info('Catalogue : ' . count($toutesLesCles) . ' habilitations (hors Super Admin).');

        // Responsables, au sens de Company::estResponsable() :
        //  - créateur d'une entreprise (companies.user_id)
        //  - affecté à une entreprise avec le rôle admin (pivot company_user)
        //  - admin dont c'est l'entreprise principale (users.company_id)
        $responsableIds = collect()
            ->merge(Company::whereNotNull('user_id')->pluck('user_id'))
            ->merge(DB::table('company_user')->where('role', 'admin')->pluck('user_id'))
            ->merge(
                User::where('role', 'admin')
                    ->whereIn('company_id', Company::pluck('id'))
                    ->pluck('id')
            )
            ->filter()
            ->unique()
            ->values();

        $this->line('');
        $this->info('Responsables de comptabilité détectés : ' . $responsableIds->count());

        $repares = 0;
        $deja = 0;

        foreach (User::whereIn('id', $responsableIds)->get() as $user) {
            if ($user->role === 'super_admin') {
                continue;
            }

            $habilitations = $user->habilitations ?? [];

            // Un compte sans habilitation explicite (admin principal) a déjà tout :
            // lui en écrire fige ses droits inutilement.
            if (empty($habilitations) && method_exists($user, 'isPrincipalAdmin') && $user->isPrincipalAdmin()) {
                $deja++;
                continue;
            }

            $manquantes = array_diff($toutesLesCles, array_keys(array_filter(
                $habilitations,
                fn ($v) => $v === '1' || $v === 1 || $v === true
            )));

            if (empty($manquantes)) {
                $deja++;
                continue;
            }

            $this->line(sprintf(
                '  %-28s %-10s +%d habilitation(s)',
                mb_substr(trim(($user->name ?? '') . ' ' . ($user->last_name ?? '')), 0, 27),
                $user->role,
                count($manquantes)
            ));

            if (!$simulation) {
                foreach ($toutesLesCles as $cle) {
                    $habilitations[$cle] = '1';
                }
                $user->habilitations = $habilitations;
                $user->save();
            }

            $repares++;
        }

        $this->line('');
        $this->info("Comptes réparés : {$repares} — déjà complets : {$deja}");

        // Diagnostic : comptabilités inutilisables faute d'exercice
        $sansExercice = Company::whereNotIn('id', ExerciceComptable::distinct()->pluck('company_id')->filter())
            ->orderBy('company_name')
            ->get(['id', 'company_name']);

        $this->line('');
        if ($sansExercice->isEmpty()) {
            $this->info('Toutes les comptabilités ont au moins un exercice.');
        } else {
            $this->warn('Comptabilités sans aucun exercice comptable : ' . $sansExercice->count());
            foreach ($sansExercice->take(30) as $c) {
                $this->line('  #' . $c->id . ' ' . $c->company_name);
            }
            if ($sansExercice->count() > 30) {
                $this->line('  … et ' . ($sansExercice->count() - 30) . ' autre(s)');
            }
            $this->line("  Leur responsable peut désormais en ouvrir un depuis Traitement > Exercice comptable.");
        }

        return self::SUCCESS;
    }

    /** Toutes les clés d'habilitation, hors sections Super Admin. */
    private function catalogue(): array
    {
        $cles = [];

        foreach (config('accounting_permissions.permissions', []) as $section => $permissions) {
            if (!is_array($permissions) || str_contains($section, 'Super Admin')) {
                continue;
            }
            $cles = array_merge($cles, array_keys($permissions));
        }

        return $cles;
    }
}
