<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Company;
use App\Models\ExerciceComptable;

$companies = Company::all();
echo "--- RECENSEMENT DES SOCIÉTÉS ---\n";
foreach ($companies as $c) {
    echo "ID: {$c->id}, Name: {$c->company_name}\n";
    $active = ExerciceComptable::where('company_id', $c->id)->where('is_active', 1)->first();
    echo "  -> Exercice Actif: " . ($active ? $active->intitule : "NONE") . "\n";
    $count = ExerciceComptable::where('company_id', $c->id)->count();
    echo "  -> Total Exercices: $count\n\n";
}
