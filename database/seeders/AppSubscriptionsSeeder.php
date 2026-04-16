<?php

namespace Database\Seeders;

use App\Models\AppSubscription;
use App\Models\Company;
use App\Models\ServiceHonoraire;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class AppSubscriptionsSeeder extends Seeder
{
    public function run(): void
    {
        $companies = Company::take(5)->get();

        if ($companies->isEmpty()) {
            $this->command->warn('Aucune entreprise trouvée. Créez d\'abord des entreprises.');
            return;
        }

        $apps = ['RHFLOW', 'COMPTAFLOW', 'TASKFLOW', 'SELFLOW', 'LEGALFLOW'];
        $statusPool = ['paid', 'paid', 'pending', 'overdue'];

        // ── Abonnements Apps ───────────────────────────────────────────────────
        foreach ($companies as $i => $company) {
            // Chaque entreprise souscrit à 1-3 apps
            $selectedApps = array_slice($apps, 0, rand(1, 3));
            foreach ($selectedApps as $app) {
                $packs = AppSubscription::prixParApp($app);
                // On exclut les packs sans prix défini (sur mesure) pour le seeder
                $packsAvecPrix = array_filter($packs, fn($p) => !is_null($p));
                if (empty($packsAvecPrix)) continue;
                $packKeys = array_keys($packsAvecPrix);
                $packName = $packKeys[array_rand($packKeys)];
                $prixPack = $packsAvecPrix[$packName];

                AppSubscription::create([
                    'company_id'      => $company->id,
                    'app_name'        => $app,
                    'pack_name'       => $packName,
                    'prix_mensuel'    => $prixPack ?? 25_000,
                    'date_debut'      => Carbon::now()->subMonths(rand(2, 8)),
                    'date_fin'        => null,
                    'statut_paiement' => $statusPool[array_rand($statusPool)],
                ]);
            }
        }

        // ── Abonnements Services ──────────────────────────────────────────────
        $services = [
            ['COMPTABILITE', null,   []],                    // prix à définir
            ['FISCALITE',    null,   ['TE']],                // prix à définir
            ['SOCIAL_RH',   50_000, ['CNPS','FNE','CMU','TE']],
            ['JURIDIQUE',   null,   []],                    // prix à définir
        ];

        foreach ($companies->take(4) as $i => $company) {
            [$srv, $prix, $decl] = $services[$i % count($services)];
            ServiceHonoraire::create([
                'company_id'      => $company->id,
                'service_name'    => $srv,
                'description'     => ServiceHonoraire::catalogue()[$srv]['description'] ?? '',
                'prix_mensuel'    => $prix,
                'declarations'    => $decl,
                'date_debut'      => Carbon::now()->subMonths(rand(1, 6)),
                'date_fin'        => null,
                'statut_paiement' => $statusPool[array_rand($statusPool)],
            ]);
        }

        $this->command->info('✓ Abonnements et services de test créés avec succès.');
    }
}
