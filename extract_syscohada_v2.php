<?php

use PhpOffice\PhpSpreadsheet\IOFactory;

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "📊 Extraction COMPLÈTE du plan comptable SYSCOHADA...\n\n";

try {
    $filePath = __DIR__ . '/syscohada.xlsx';
    
    if (!file_exists($filePath)) {
        die("❌ Fichier syscohada.xlsx introuvable!\n");
    }

    $spreadsheet = IOFactory::load($filePath);
    $worksheet = $spreadsheet->getActiveSheet();
    $highestRow = $worksheet->getHighestRow();
    
    echo "✓ Fichier chargé: $highestRow lignes\n";
    
    $accounts = [];
    $skipped = 0;
    
    // Parcourir toutes les lignes
    for ($row = 1; $row <= $highestRow; $row++) {
        $cellValue = trim($worksheet->getCell('A' . $row)->getValue());
        
        // Ignorer les lignes vides ou les en-têtes
        if (empty($cellValue) || 
            stripos($cellValue, 'Plan comptable') !== false ||
            stripos($cellValue, 'OHADA') !== false ||
            stripos($cellValue, 'Suivant') !== false) {
            $skipped++;
            continue;
        }
        
        // Essayer de parser le format "Numéro Libellé"
        // Ex: "10 Capital" ou "101 Capital social"
        if (preg_match('/^(\d+)\s+(.+)$/u', $cellValue, $matches)) {
            $numero = $matches[1];
            $libelle = trim($matches[2]);
            $accounts[$numero] = $libelle;
        } else {
            // Si le format ne correspond pas, essayer de détecter si c'est juste un numéro ou un libellé
            if (preg_match('/^\d+$/', $cellValue)) {
                // C'est juste un numéro, chercher le libellé dans la cellule suivante
                $nextCell = trim($worksheet->getCell('B' . $row)->getValue());
                if (!empty($nextCell)) {
                    $accounts[$cellValue] = $nextCell;
                }
            }
        }
    }
    
    echo "✓ Comptes extraits: " . count($accounts) . "\n";
    echo "✓ Lignes ignorées: $skipped\n\n";
    
    if (count($accounts) == 0) {
        echo "⚠️  Aucun compte extrait. Affichage des 20 premières lignes pour diagnostic:\n\n";
        for ($row = 1; $row <= min(20, $highestRow); $row++) {
            $value = $worksheet->getCell('A' . $row)->getValue();
            echo "Ligne $row: [$value]\n";
        }
        exit(1);
    }
    
    // Générer le fichier PHP
    $phpCode = "<?php\n\n";
    $phpCode .= "// PLAN COMPTABLE SYSCOHADA COMPLET\n";
    $phpCode .= "// Extrait automatiquement de syscohada.xlsx\n";
    $phpCode .= "// Total: " . count($accounts) . " comptes\n";
    $phpCode .= "// Date: " . date('Y-m-d H:i:s') . "\n\n";
    $phpCode .= "return [\n";
    
    foreach ($accounts as $numero => $libelle) {
        $libelle = str_replace("'", "\\'", $libelle);
        $phpCode .= "    '$numero' => '$libelle',\n";
    }
    
    $phpCode .= "];\n";
    
    // Sauvegarder
    $outputFile = __DIR__ . '/config/syscohada_complet.php';
    file_put_contents($outputFile, $phpCode);
    
    echo "✅ Extraction terminée avec succès!\n";
    echo "📁 Fichier généré: $outputFile\n";
    echo "📊 Total de comptes: " . count($accounts) . "\n\n";
    
    // Afficher un aperçu
    echo "📋 Aperçu des 15 premiers comptes:\n";
    echo str_repeat("-", 80) . "\n";
    $preview = array_slice($accounts, 0, 15, true);
    foreach ($preview as $num => $lib) {
        printf("   %-10s => %s\n", $num, substr($lib, 0, 60));
    }
    echo str_repeat("-", 80) . "\n";
    
} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}
