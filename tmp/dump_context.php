<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$companyId = 1;

// Tiers
$tiers = App\Models\PlanTiers::where('company_id', $companyId)->get();
echo "--- TIERS (" . $tiers->count() . ") ---\n";
foreach ($tiers as $t) {
    echo "- {$t->intitule} ({$t->numero_de_tiers})\n";
}

// Mappings
$mappings = App\Models\IaMapping::where('company_id', $companyId)->get();
echo "\n--- MAPPINGS (" . $mappings->count() . ") ---\n";
foreach ($mappings as $m) {
    echo "- {$m->tiers_nom} -> {$m->compte_numero} ({$m->compte_libelle})\n";
}

// Plan Comptable (first 50)
$comptes = App\Models\PlanComptable::where('company_id', $companyId)->limit(50)->get();
echo "\n--- PLAN COMPTABLE (First 50) ---\n";
foreach ($comptes as $c) {
    echo "- {$c->numero_de_compte} - {$c->intitule}\n";
}

// Check for "LUKE INTERNATIONAL SCHOOL"
$luke = App\Models\PlanTiers::where('company_id', $companyId)->where('intitule', 'LIKE', '%LUKE%')->first();
if ($luke) {
    echo "\nFOUND LUKE: " . $luke->intitule . " (" . $luke->numero_de_tiers . ")\n";
}
