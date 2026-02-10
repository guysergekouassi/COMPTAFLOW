<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\TreasuryCategory;
use App\Models\CompteTresorerie;

$categories = TreasuryCategory::all();
echo "--- ANALYSE DES CATEGORIES ---\n";
foreach ($categories as $cat) {
    $count = CompteTresorerie::where('category_id', $cat->id)->count();
    echo "ID: {$cat->id} | Nom: \"{$cat->name}\" | CoID: " . ($cat->company_id ?? 'GLOBAL') . " | Postes liés: {$count}\n";
}

echo "\n--- DOUBLONS POTENTIELS (Mêmes noms par compagnie) ---\n";
$duplicates = TreasuryCategory::select('name', 'company_id', \Illuminate\Support\Facades\DB::raw('count(*) as total'))
    ->groupBy('name', 'company_id')
    ->having('total', '>', 1)
    ->get();

foreach ($duplicates as $dup) {
    echo "Nom: \"{$dup->name}\" | CoID: " . ($dup->company_id ?? 'GLOBAL') . " | Fois: {$dup->total}\n";
}
