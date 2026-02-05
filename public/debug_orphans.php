<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';

$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\EcritureComptable;
use App\Models\PlanTiers;
use App\Models\Company;

echo "Laravel bootstrapping complete.\n";

$company = Company::find(1);
$companyId = $company->id;
echo "Checking Orphans for Company: " . $company->company_name . "\n";

$ecritures = EcritureComptable::where('company_id', $companyId)
    ->whereNotNull('plan_tiers_id')
    ->get();

$orphans = 0;
$valid = 0;

foreach ($ecritures as $ecriture) {
    if (!$ecriture->planTiers) {
        $orphans++;
        // echo "Orphan found: Ecriture ID " . $ecriture->id . " -> Tiers ID " . $ecriture->plan_tiers_id . "\n";
    } else {
        $valid++;
    }
}

echo "Total Ecritures with plan_tiers_id: " . $ecritures->count() . "\n";
echo "Valid Relationships: $valid\n";
echo "Orphan Relationships: $orphans\n";

if ($orphans > 0) {
    echo "WARNING: There are orphan records! These will show up as empty headers in the report.\n";
}
