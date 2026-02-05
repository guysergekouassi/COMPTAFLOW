<?php
// debug_bounds.php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\PlanComptable;

$companyId = 1;

$all = PlanComptable::withoutGlobalScopes()
    ->where('company_id', $companyId)
    ->orderBy('numero_de_compte')
    ->get();

echo "Total comptes: " . $all->count() . "\n";

echo "\n--- 10 PREMIERS ---\n";
foreach($all->take(10) as $a) {
    echo "ID: {$a->id} | Num: '{$a->numero_de_compte}'\n";
}

echo "\n--- 10 DERNIERS ---\n";
foreach($all->take(-10) as $a) {
    echo "ID: {$a->id} | Num: '{$a->numero_de_compte}'\n";
}

$first = $all->first();
$last = $all->last();

if (!$first) die("Vide");

$v1 = (string)$first->numero_de_compte;
$v2 = (string)$last->numero_de_compte;

$min = $v1 < $v2 ? $v1 : $v2;
$max = $v1 < $v2 ? $v2 : $v1;

echo "\nCALCUL:\n";
echo "V1 (First): '$v1'\n";
echo "V2 (Last): '$v2'\n";
echo "MIN: '$min'\n";
echo "MAX: '$max'\n";

// Test 624200
$target = "624200";
echo "Target '$target' >= '$min'? " . ($target >= $min ? "YES" : "NO") . "\n";
echo "Target '$target' <= '$max'? " . ($target <= $max ? "YES" : "NO") . "\n";
