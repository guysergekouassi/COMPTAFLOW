<?php

use PhpOffice\PhpSpreadsheet\IOFactory;

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "📊 EXTRACTION FINALE - Plan Comptable SYSCOHADA\n";
echo str_repeat("=", 80) . "\n\n";

try {
    $filePath = __DIR__ . '/syscohada.xlsx';
    
    if (!file_exists($filePath)) {
        die("❌ Fichier introuvable!\n");
    }

    $spreadsheet = IOFactory::load($filePath);
    $worksheet = $spreadsheet->getActiveSheet();
    $highestRow = $worksheet->getHighestRow();
    
    echo "✓ Fichier chargé: $highestRow lignes\n\n";
    
    $accounts = [];
    $currentAccount = null;
    
    for ($row = 1; $row <= $highestRow; $row++) {
        $cellA = trim($worksheet->getCell('A' . $row)->getValue());
        $cellB = trim($worksheet->getCell('B' . $row)->getValue());
        
        // Ignorer les lignes vides et les en-têtes
        if (empty($cellA) || 
            stripos($cellA, 'Plan comptable') !== false ||
            stripos($cellA, 'OHADA') !== false ||
            stripos($cellA, 'Suivant') !== false ||
            stripos($cellA, 'uniformes') !== false) {
            continue;
        }
        
        // Pattern 1: "Numéro Description" dans la colonne A
        // Ex: "10 Capital", "101 Capital social", "1011 Capital souscrit"
        if (preg_match('/^(\d{1,4})\s+(.+)$/u', $cellA, $matches)) {
            $numero = $matches[1];
            $libelle = trim($matches[2]);
            $accounts[$numero] = mb_strtoupper($libelle);
            continue;
        }
        
        // Pattern 2: Numéro seul dans A, description dans B
        if (preg_match('/^\d{1,4}$/u', $cellA) && !empty($cellB)) {
            $accounts[$cellA] = mb_strtoupper($cellB);
            continue;
        }
        
        // Pattern 3: Description qui continue (sous-comptes avec indentation)
        // Ex: "  - Créances rattachées à des participations"
        if (preg_match('/^[\s-]+(.+)$/u', $cellA, $matches)) {
            // C'est probablement une continuation ou un sous-élément
            continue;
        }
    }
    
    echo "✓ Comptes extraits: " . count($accounts) . "\n\n";
    
    if (count($accounts) == 0) {
        die("❌ Aucun compte extrait. Vérifiez le format du fichier.\n");
    }
    
    // Trier par numéro de compte
    ksort($accounts, SORT_NATURAL);
    
    // Générer le fichier PHP
    $phpCode = "<?php\n\n";
    $phpCode .= "/**\n";
    $phpCode .= " * PLAN COMPTABLE SYSCOHADA COMPLET\n";
    $phpCode .= " * Extrait automatiquement de syscohada.xlsx\n";
    $phpCode .= " * Total: " . count($accounts) . " comptes\n";
    $phpCode .= " * Date d'extraction: " . date('Y-m-d H:i:s') . "\n";
    $phpCode .= " */\n\n";
    $phpCode .= "return [\n";
    
    foreach ($accounts as $numero => $libelle) {
        $libelle = str_replace("'", "\\'", $libelle);
        $phpCode .= "    '$numero' => '$libelle',\n";
    }
    
    $phpCode .= "];\n";
    
    // Sauvegarder
    $outputFile = __DIR__ . '/config/syscohada_complet.php';
    file_put_contents($outputFile, $phpCode);
    
    echo "✅ EXTRACTION RÉUSSIE!\n";
    echo "📁 Fichier: $outputFile\n";
    echo "📊 Total: " . count($accounts) . " comptes\n\n";
    
    // Statistiques par classe
    echo "📈 Répartition par classe:\n";
    $classes = [];
    foreach ($accounts as $numero => $libelle) {
        $classe = substr($numero, 0, 1);
        if (!isset($classes[$classe])) {
            $classes[$classe] = 0;
        }
        $classes[$classe]++;
    }
    
    ksort($classes);
    foreach ($classes as $classe => $count) {
        echo "   Classe $classe: $count comptes\n";
    }
    
    echo "\n📋 Aperçu (20 premiers comptes):\n";
    echo str_repeat("-", 80) . "\n";
    $preview = array_slice($accounts, 0, 20, true);
    foreach ($preview as $num => $lib) {
        printf("   %-6s => %s\n", $num, substr($lib, 0, 60));
    }
    echo str_repeat("-", 80) . "\n";
    
    echo "\n✅ Fichier prêt à être utilisé dans AdminConfigController!\n";
    
} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}
