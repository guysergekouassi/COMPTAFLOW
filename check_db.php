<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$columns = Illuminate\Support\Facades\DB::select('DESCRIBE ecriture_comptables');
foreach ($columns as $column) {
    echo "{$column->Field} | {$column->Type} | {$column->Null} | {$column->Default}\n";
}
