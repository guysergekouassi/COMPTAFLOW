<?php

use App\Models\Company;
use App\Models\PlanComptable;
use App\Models\CodeJournal;

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$name = 'AGNIMEL2';
$company = Company::where('company_name', 'LIKE', "%$name%")->first();

if (!$company) {
    echo "Company '$name' NOT FOUND.\n";
    exit;
}

echo "Company Found: {$company->company_name} (ID: {$company->id})\n";
echo "Parent ID (Raw): " . var_export($company->parent_company_id, true) . "\n";
echo "Active: " . ($company->is_active ? 'YES' : 'NO') . "\n";

if ($company->parent_company_id) {
    $parent = Company::find($company->parent_company_id);
    echo "Parent Name: " . ($parent ? $parent->company_name : 'Unknown') . "\n";
}

$accounts = PlanComptable::where('company_id', $company->id)->count();
$journals = CodeJournal::where('company_id', $company->id)->count();

echo "Plan Comptable Count: $accounts\n";
echo "Journals Count: $journals\n";
