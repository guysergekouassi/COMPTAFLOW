<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';

$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\EcritureComptable;
use App\Models\PlanTiers;
use App\Models\User;
use App\Models\Company;
use Illuminate\Support\Facades\DB;

echo "Laravel bootstrapping complete.\n";

// 1. List Companies
$companies = Company::all();
echo "Found " . $companies->count() . " companies:\n";
foreach ($companies as $c) {
    echo " - ID: " . $c->id . " | Name: " . $c->company_name . "\n";
}

if ($companies->count() == 0) {
    die("No companies found!\n");
}

// Select the first company to test
$testCompany = $companies->first();
$companyId = $testCompany->id;
echo "\nTesting with Company ID: $companyId (" . $testCompany->company_name . ")\n";

// 2. Check total count
$totalCount = EcritureComptable::where('company_id', $companyId)->count();
echo "Total EcritureComptable count: $totalCount\n";

// 3. Check count with plan_tiers_id not null
$tiersCount = EcritureComptable::where('company_id', $companyId)->whereNotNull('plan_tiers_id')->count();
echo "EcritureComptable with valid plan_tiers_id: $tiersCount\n";

if ($tiersCount == 0) {
    echo "WARNING: No EcritureComptable found with plan_tiers_id set for this company!\n";
    
    // Check if there are ANY ecritures with plan_tiers_id in the entire DB?
    $globalTiersCount = EcritureComptable::whereNotNull('plan_tiers_id')->count();
    echo "Global EcritureComptable with valid plan_tiers_id (all companies): $globalTiersCount\n";
    
    // Maybe they are using 'auxiliaire' field or something else?
    // Let's inspect columns of EcritureComptable
    // $columns = \Schema::getColumnListing('ecriture_comptable');
    // print_r($columns);
    
} else {
    // 4. Dump a few examples
    $examples = EcritureComptable::where('company_id', $companyId)
        ->whereNotNull('plan_tiers_id')
        ->limit(5)
        ->get();

    foreach ($examples as $ex) {
        echo "ID: " . $ex->id . " | Date: " . $ex->date . " | Tiers ID: " . $ex->plan_tiers_id . " | Debit: " . $ex->debit . " | Credit: " . $ex->credit . "\n";
    }
}

// 5. Check PlanTiers
$tiersCount = PlanTiers::where('company_id', $companyId)->count();
echo "\nPlanTiers count for company: $tiersCount\n";

if ($tiersCount > 0) {
    $first = PlanTiers::where('company_id', $companyId)->orderBy('numero_de_tiers')->first();
    $last = PlanTiers::where('company_id', $companyId)->orderBy('numero_de_tiers', 'desc')->first();
    echo "Range: " . $first->numero_de_tiers . " - " . $last->numero_de_tiers . "\n";
}
