<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Company;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class GenerateCompanyCodesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'compta:generate-codes {--force : Régénérer les codes même si déjà existants}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Génère les codes uniques sécurisés pour toutes les entreprises qui n\'en ont pas encore.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $force = $this->option('force');

        $query = Company::query();
        if (!$force) {
            $query->whereNull('company_code');
        }

        $companies = $query->get();

        if ($companies->isEmpty()) {
            $this->info('Toutes les entreprises ont déjà un code. Utilisez --force pour régénérer.');
            return Command::SUCCESS;
        }

        $this->info("Génération de codes pour {$companies->count()} entreprise(s)...");
        $bar = $this->output->createProgressBar($companies->count());
        $bar->start();

        $generated = 0;
        foreach ($companies as $company) {
            $cleanName = preg_replace('/[^A-Za-z]/', '', $company->company_name);
            $prefix = strtoupper(substr($cleanName, 0, 3));
            if (strlen($prefix) < 3) {
                $prefix = str_pad($prefix, 3, 'X');
            }

            $attempts = 0;
            do {
                $code = $prefix . '-' . strtoupper(Str::random(6));
                $exists = Company::where('company_code', $code)->where('id', '!=', $company->id)->exists();
                $attempts++;
            } while ($exists && $attempts < 20);

            if (!$exists) {
                $company->update(['company_code' => $code]);
                $generated++;
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("✅ {$generated} codes générés avec succès !");

        // Afficher un tableau récapitulatif
        $this->table(
            ['Entreprise', 'Code généré'],
            Company::whereNotNull('company_code')->get()->map(fn($c) => [$c->company_name, $c->company_code])->toArray()
        );

        return Command::SUCCESS;
    }
}
