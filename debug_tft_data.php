<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\EcritureComptable;

$exerciceId = 5; // FORCE 2025
echo "--- Analysing Exercise ID: $exerciceId with Correct Grouping (n_saisie) ---\n";

// 2. Simulate TFT Logic (Group by n_saisie)
echo "\n2. Simulating TFT Logic:\n";
$transactions = EcritureComptable::where('exercices_comptables_id', $exerciceId)
    ->with(['planComptable', 'posteTresorerie'])
    ->get()
    ->filter(fn($e) => !$e->is_ran)
    ->groupBy('n_saisie'); // CORRECT GROUPING

$opCount = 0; $invCount = 0; $finCount = 0;
$opAmount = 0; $invAmount = 0; $finAmount = 0;

foreach ($transactions as $saisie => $lignes) {
    // A. Analyze Treasury
    $lignesTreso = $lignes->filter(fn($l) => str_starts_with($l->planComptable->numero_de_compte ?? '', '5'));
    
    if ($lignesTreso->isEmpty()) continue;
    
    $fluxNet = $lignesTreso->sum(fn($l) => $l->debit - $l->credit);
    if (abs($fluxNet) < 0.01) continue;

    // B. Determine Section
    $activityKey = 'operationnelle';
    $source = 'DEFAULT';
    
    // Check Post
    $mainTreso = $lignesTreso->first(fn($l) => $l->posteTresorerie) ?? $lignesTreso->first();
    if ($mainTreso->posteTresorerie) {
        $posteName = $mainTreso->posteTresorerie->name;
        $catName = $mainTreso->posteTresorerie->category->name ?? '';
        $sysCode = $mainTreso->posteTresorerie->syscohada_line_id;
        
        if (str_starts_with($sysCode ?? '', 'INV_') || str_contains(strtolower($catName), 'investissement')) {
            $activityKey = 'investissement';
            $source = "POSTE ($posteName)";
        } elseif (str_starts_with($sysCode ?? '', 'FIN_') || str_contains(strtolower($catName), 'financement')) {
            $activityKey = 'financement';
            $source = "POSTE ($posteName)";
        }
    }

    // Check Fallback
    if ($activityKey === 'operationnelle') {
        $hasInvest = $lignes->contains(function($l) {
            $num = $l->planComptable->numero_de_compte ?? '';
            return str_starts_with($num, '2') && !str_starts_with($num, '28') && !str_starts_with($num, '29');
        });
        if ($hasInvest) {
            $activityKey = 'investissement';
            $source = "FALLBACK (Class 2)";
        } else {
             $hasFin = $lignes->contains(function($l) {
                $num = $l->planComptable->numero_de_compte ?? '';
                return str_starts_with($num, '1') || str_starts_with($num, '16');
            });
            if ($hasFin) {
                $activityKey = 'financement';
                $source = "FALLBACK (Class 1)";
            }
        }
    }

    // C. Aggregate
    $absFlux = abs($fluxNet);
    if ($activityKey == 'operationnelle') { $opCount++; $opAmount += $absFlux; }
    if ($activityKey == 'investissement') { $invCount++; $invAmount += $absFlux; }
    if ($activityKey == 'financement') { $finCount++; $finAmount += $absFlux; }

    echo "  Saisie $saisie: $activityKey flow: $absFlux (Source: $source)\n";
}

echo "\n--- Summary ---\n";
echo "Operational: $opCount transactions | Total: $opAmount\n";
echo "Investment : $invCount transactions | Total: $invAmount\n";
echo "Financing  : $finCount transactions | Total: $finAmount\n";
