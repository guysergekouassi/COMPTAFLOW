<?php
include 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$companyId = 33;
$stats = [];
for($i=1; $i<=9; $i++) {
    $stats[$i] = App\Models\PlanComptable::withoutGlobalScopes()
        ->where('company_id', $companyId)
        ->where('numero_de_compte', 'like', $i.'%')
        ->count();
}

$total = App\Models\PlanComptable::withoutGlobalScopes()
    ->where('company_id', $companyId)
    ->count();

echo "Total pour société $companyId : $total\n";
print_r($stats);

// Vérifier les 10 derniers comptes après le tri
$lastAccounts = App\Models\PlanComptable::withoutGlobalScopes()
    ->where('company_id', $companyId)
    ->orderBy('numero_de_compte', 'asc')
    ->get()
    ->slice(-10)
    ->pluck('numero_de_compte')
    ->toArray();

echo "\n10 derniers comptes :\n";
print_r($lastAccounts);
