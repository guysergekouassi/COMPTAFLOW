<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Structure de la table plan_tiers :\n";
$columns = Illuminate\Support\Facades\DB::select('DESCRIBE plan_tiers');
foreach ($columns as $column) {
    echo "{$column->Field} | {$column->Type} | {$column->Null} | {$column->Default}\n";
}

echo "\n10 premiers tiers :\n";
$data = App\Models\PlanTiers::take(10)->get();
foreach ($data as $d) {
    echo "ID: {$d->id} | Num: {$d->numero_de_tiers} | Orig: '{$d->numero_original}' | Account: {$d->compte_general}\n";
}
