<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';

$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\PlanTiers;
use App\Models\Company;

echo "Laravel bootstrapping complete.\n";

$company = Company::find(1);
$companyId = $company->id;
echo "Checking numero_original for Company: " . $company->company_name . "\n";

$tiersWithOriginal = PlanTiers::where('company_id', $companyId)
    ->whereNotNull('numero_original')
    ->where('numero_original', '!=', '')
    ->count();

$totalTiers = PlanTiers::where('company_id', $companyId)->count();

echo "Total Tiers: $totalTiers\n";
echo "Tiers with numero_original: $tiersWithOriginal\n";

$examples = PlanTiers::where('company_id', $companyId)
    ->whereNotNull('numero_original')
    ->limit(5)
    ->get();

echo "\nExamples:\n";
foreach ($examples as $t) {
    echo "ID: " . $t->id . " | Num: " . $t->numero_de_tiers . " | Orig: " . $t->numero_original . "\n";
}
