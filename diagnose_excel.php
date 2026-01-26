<?php

use PhpOffice\PhpSpreadsheet\IOFactory;

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "🔍 DIAGNOSTIC COMPLET du fichier syscohada.xlsx\n";
echo str_repeat("=", 100) . "\n\n";

try {
    $filePath = __DIR__ . '/syscohada.xlsx';
    
    if (!file_exists($filePath)) {
        die("❌ Fichier introuvable!\n");
    }

    $spreadsheet = IOFactory::load($filePath);
    $worksheet = $spreadsheet->getActiveSheet();
    $highestRow = $worksheet->getHighestRow();
    $highestColumn = $worksheet->getHighestColumn();
    
    echo "📊 Informations générales:\n";
    echo "   - Lignes: $highestRow\n";
    echo "   - Colonnes: A à $highestColumn\n\n";
    
    echo "📋 TOUTES LES LIGNES (50 premières):\n";
    echo str_repeat("-", 100) . "\n";
    
    for ($row = 1; $row <= min(50, $highestRow); $row++) {
        $rowData = [];
        
        // Lire toutes les colonnes
        for ($col = 'A'; $col <= 'E'; $col++) {  // Limiter à 5 colonnes pour la lisibilité
            $value = $worksheet->getCell($col . $row)->getValue();
            if ($value !== null && $value !== '') {
                $rowData[$col] = substr(trim($value), 0, 40);
            }
        }
        
        if (!empty($rowData)) {
            echo sprintf("L%-3d: ", $row);
            foreach ($rowData as $col => $val) {
                echo "[$col: $val] ";
            }
            echo "\n";
        }
    }
    
    echo str_repeat("-", 100) . "\n\n";
    
    // Essayer de détecter les patterns
    echo "🔎 Détection de patterns (lignes contenant des chiffres):\n";
    $accountLines = [];
    for ($row = 1; $row <= $highestRow; $row++) {
        $cellA = trim($worksheet->getCell('A' . $row)->getValue());
        $cellB = trim($worksheet->getCell('B' . $row)->getValue());
        
        // Chercher les lignes qui commencent par un chiffre
        if (preg_match('/^\d/', $cellA) || preg_match('/^\d/', $cellB)) {
            $accountLines[] = "L$row: A=[$cellA] B=[$cellB]";
            if (count($accountLines) >= 20) break;  // Limiter l'affichage
        }
    }
    
    foreach ($accountLines as $line) {
        echo "   $line\n";
    }
    
} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "\n";
}
