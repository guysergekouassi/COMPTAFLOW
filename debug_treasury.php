<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\TreasuryCategory;
use App\Models\CompteTresorerie;

echo "--- CATEGORIES ---\n";
foreach (TreasuryCategory::all() as $c) {
    echo "ID: {$c->id} | Name: {$c->name} | CoID: {$c->company_id}\n";
}

echo "\n--- POSTES (CompteTresorerie) ---\n";
foreach (CompteTresorerie::all() as $p) {
    $catName = $p->category ? $p->category->name : 'NONE';
    echo "ID: {$p->id} | Name: {$p->name} | Type: {$p->type} | Cat: {$catName} | CoID: {$p->company_id}\n";
}
