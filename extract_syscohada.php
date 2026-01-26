<?php

use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "📊 Extraction du plan comptable SYSCOHADA depuis Excel...\n\n";

try {
    $filePath = __DIR__ . '/syscohada.xlsx';
    
    if (!file_exists($filePath)) {
        die("❌ Fichier syscohada.xlsx introuvable!\n");
    }

    echo "✓ Fichier trouvé: $filePath\n";
    echo "✓ Chargement du fichier Excel...\n";
    
    $spreadsheet = IOFactory::load($filePath);
    $worksheet = $spreadsheet->getActiveSheet();
    $highestRow = $worksheet->getHighestRow();
    
    echo "✓ Nombre de lignes détectées: $highestRow\n\n";
    
    $accounts = [];
    $skipped = 0;
    
    // Parcourir toutes les lignes (en supposant que la première ligne est l'en-tête)
    for ($row = 2; $row <= $highestRow; $row++) {
        $numero = trim($worksheet->getCell('A' . $row)->getValue());
        $libelle = trim($worksheet->getCell('B' . $row)->getValue());
        
        // Ignorer les lignes vides
        if (empty($numero) || empty($libelle)) {
            $skipped++;
            continue;
        }
        
        $accounts[$numero] = $libelle;
    }
    
    echo "✓ Comptes extraits: " . count($accounts) . "\n";
    echo "✓ Lignes ignorées (vides): $skipped\n\n";
    
    // Générer le code PHP pour le contrôleur
    echo "📝 Génération du code PHP...\n\n";
    
    $phpCode = "// PLAN COMPTABLE SYSCOHADA COMPLET - Extrait de syscohada.xlsx\n";
    $phpCode .= "// Total: " . count($accounts) . " comptes\n";
    $phpCode .= "\$syscohadaComplet = [\n";
    
    foreach ($accounts as $numero => $libelle) {
        // Échapper les apostrophes
        $libelle = str_replace("'", "\\'", $libelle);
        $phpCode .= "    '$numero' => '$libelle',\n";
    }
    
    $phpCode .= "];\n";
    
    // Sauvegarder dans un fichier
    $outputFile = __DIR__ . '/syscohada_extracted.php';
    file_put_contents($outputFile, "<?php\n\n" . $phpCode);
    
    echo "✅ Extraction terminée!\n";
    echo "📁 Fichier généré: $outputFile\n";
    echo "📊 Total de comptes: " . count($accounts) . "\n\n";
    
    // Afficher un aperçu
    echo "📋 Aperçu des premiers comptes:\n";
    $preview = array_slice($accounts, 0, 10, true);
    foreach ($preview as $num => $lib) {
        echo "   $num => $lib\n";
    }
    
} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
