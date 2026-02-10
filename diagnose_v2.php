<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\TreasuryCategory;
use App\Models\CompteTresorerie;

$output = "--- ANALYSE DETAILLEE DES CATEGORIES DE TRESORERIE ---\n\n";

$categories = TreasuryCategory::all();
foreach ($categories as $cat) {
    $count = CompteTresorerie::where('category_id', $cat->id)->count();
    $output .= "[ID: {$cat->id}] \"{$cat->name}\" | CompID: " . ($cat->company_id ?? 'GLOBAL') . " | Postes liés: {$count}\n";
}

$output .= "\n--- REGROUPEMENT PAR NOM ---\n";
$grouped = $categories->groupBy(function($item) {
    return trim($item->name);
});

foreach ($grouped as $name => $cats) {
    if ($cats->count() > 1) {
        $output .= "Doublon potentiel pour \"{$name}\" (Trouvé {$cats->count()} fois):\n";
        foreach ($cats as $c) {
            $output .= "  - ID: {$c->id}, CompID: " . ($c->company_id ?? 'GLOBAL') . "\n";
        }
    }
}

file_put_contents('treasury_diagnostic.txt', $output);
echo "Diagnostic written to treasury_diagnostic.txt\n";
