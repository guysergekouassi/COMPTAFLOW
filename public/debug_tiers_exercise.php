<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';

$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\EcritureComptable;
use App\Models\PlanTiers;
use App\Models\Company;
use App\Models\ExerciceComptable;

echo "Laravel bootstrapping complete.\n";

$company = Company::find(1); // Hardcoded to check the company with data
$companyId = $company->id;
echo "Checking EcritureComptable for Company: " . $company->company_name . "\n";

// 1. Check distinct exercices_comptables_id
$distinctExercices = EcritureComptable::where('company_id', $companyId)
    ->whereNotNull('plan_tiers_id')
    ->distinct()
    ->pluck('exercices_comptables_id');

echo "Distinct Exercice IDs found in Ecritures (with Tiers): " . $distinctExercices->implode(', ') . "\n";

// 2. Check available Exercices for the company
$availableExercices = ExerciceComptable::where('company_id', $companyId)->get();
echo "\nAvailable Exercices in DB:\n";
foreach ($availableExercices as $ex) {
    echo "ID: " . $ex->id . " | dates: " . $ex->date_debut . " to " . $ex->date_fin . " | Active: " . $ex->is_active . "\n";
}

// 3. Sample Data with Exercice ID
$examples = EcritureComptable::where('company_id', $companyId)
    ->whereNotNull('plan_tiers_id')
    ->limit(5)
    ->get();

echo "\nSample Data:\n";
foreach ($examples as $ex) {
    echo "ID: " . $ex->id . " | Date: " . $ex->date . " | ExID: " . $ex->exercices_comptables_id . "\n";
}
