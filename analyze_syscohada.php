<?php

use PhpOffice\PhpSpreadsheet\IOFactory;

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "🔍 Analyse de la structure du fichier syscohada.xlsx...\n\n";

try {
    $filePath = __DIR__ . '/syscohada.xlsx';
    
    if (!file_exists($filePath)) {
        die("❌ Fichier syscohada.xlsx introuvable!\n");
    }

    $spreadsheet = IOFactory::load($filePath);
    $worksheet = $spreadsheet->getActiveSheet();
    $highestRow = $worksheet->getHighestRow();
    $highestColumn = $worksheet->getHighestColumn();
    
    echo "📊 Informations du fichier:\n";
    echo "   - Nombre de lignes: $highestRow\n";
    echo "   - Dernière colonne: $highestColumn\n\n";
    
    echo "📋 Aperçu des 10 premières lignes:\n";
    echo str_repeat("=", 100) . "\n";
    
    for ($row = 1; $row <= min(10, $highestRow); $row++) {
        echo "Ligne $row: ";
        $rowData = [];
        for ($col = 'A'; $col <= $highestColumn; $col++) {
            $value = $worksheet->getCell($col . $row)->getValue();
            if (!empty($value)) {
                $rowData[] = "$col: " . substr($value, 0, 50);
            }
        }
        echo implode(" | ", $rowData) . "\n";
    }
    
    echo str_repeat("=", 100) . "\n\n";
    
    // Essayer de détecter automatiquement les colonnes
    echo "🔎 Détection automatique des colonnes...\n";
    $headerRow = 1;
    $headers = [];
    for ($col = 'A'; $col <= $highestColumn; $col++) {
        $value = $worksheet->getCell($col . $headerRow)->getValue();
        if (!empty($value)) {
            $headers[$col] = $value;
        }
    }
    
    echo "En-têtes détectés:\n";
    foreach ($headers as $col => $header) {
        echo "   Colonne $col: $header\n";
    }
    
} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "\n";
}
