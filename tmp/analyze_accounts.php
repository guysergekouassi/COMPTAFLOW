<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$companyId = 1;

echo "--- CLASS 6 ACCOUNTS (Charges) ---\n";
$comptes6 = App\Models\PlanComptable::where('company_id', $companyId)
    ->where('numero_de_compte', 'LIKE', '6%')
    ->get();
foreach ($comptes6 as $c) {
    echo "{$c->numero_de_compte} - {$c->intitule}\n";
}

echo "\n--- WHY 200000? ---\n";
$compte200 = App\Models\PlanComptable::where('company_id', $companyId)
    ->where('numero_de_compte', '200000')
    ->first();
if ($compte200) {
    echo "ID: {$compte200->id} | Numero: {$compte200->numero_de_compte} | Intitule: {$compte200->intitule}\n";
}

echo "\n--- OTHER ACCOUNTS STARTING WITH 2 (Immobilisations) ---\n";
$comptes2 = App\Models\PlanComptable::where('company_id', $companyId)
    ->where('numero_de_compte', 'LIKE', '2%')
    ->limit(10)
    ->get();
foreach ($comptes2 as $c) {
    echo "{$c->numero_de_compte} - {$c->intitule}\n";
}
