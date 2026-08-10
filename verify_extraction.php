<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "🔍 VÉRIFICATION DÉTAILLÉE - Extraction SYSCOHADA\n";
echo str_repeat("=", 80) . "\n\n";

try {
    $filePath = __DIR__ . '/syscohada.txt';
    
    if (!file_exists($filePath)) {
        die("❌ Fichier syscohada.txt introuvable!\n");
    }

    $content = file_get_contents($filePath);
    $lines = explode("\n", $content);
    
    echo "✓ Fichier chargé: " . count($lines) . " lignes\n\n";
    
    $accounts = [];
    $skippedLines = [];
    $lineNumber = 0;
    
    foreach ($lines as $line) {
        $lineNumber++;
        $originalLine = $line;
        $line = trim($line);
        
        // Ignorer les lignes vides
        if (empty($line)) {
            continue;
        }
        
        // Ignorer les en-têtes et métadonnées
        if (stripos($line, 'www.Droit-Afrique') !== false ||
            stripos($line, 'OHADA') === 0 ||
            stripos($line, 'Plan comptable') !== false ||
            stripos($line, 'Suivant l\'acte') !== false ||
            stripos($line, 'Classe ') === 0 ||
            preg_match('/^\d+\/\d+$/', $line) ||  // Format "1/31", "2/31", etc.
            $line === '\f' ||
            strlen($line) < 2) {
            continue;
        }
        
        $matched = false;
        
        // Pattern universel : chercher un numéro de 1 à 4 chiffres précédé d'éventuels caractères non-lettres/non-chiffres et suivi d'un libellé
        if (preg_match('/^[^\p{L}\d]*(\d{1,4})\s+(.+)$/u', $line, $matches)) {
            $numero = $matches[1];
            $libelle = trim($matches[2]);
            $accounts[$numero] = mb_strtoupper($libelle);
            $matched = true;
        }
        
        // Si aucun pattern ne correspond et que la ligne contient un chiffre, la marquer comme sautée
        if (!$matched && preg_match('/\d/', $line)) {
            $skippedLines[] = "L$lineNumber: [$line]";
        }
    }
    
    echo "✓ Comptes extraits: " . count($accounts) . "\n";
    echo "⚠️  Lignes potentiellement sautées: " . count($skippedLines) . "\n\n";
    
    if (count($skippedLines) > 0) {
        echo "📋 Lignes sautées contenant des chiffres (50 premières):\n";
        echo str_repeat("-", 80) . "\n";
        foreach (array_slice($skippedLines, 0, 50) as $skipped) {
            echo "   $skipped\n";
        }
        echo str_repeat("-", 80) . "\n\n";
    }
    
    // Trier par numéro de compte
    ksort($accounts, SORT_NATURAL);
    
    // Afficher un échantillon
    echo "📊 Échantillon des comptes extraits (premiers 30):\n";
    echo str_repeat("-", 80) . "\n";
    $sample = array_slice($accounts, 0, 30, true);
    foreach ($sample as $num => $lib) {
        printf("   %-6s => %s\n", $num, substr($lib, 0, 60));
    }
    echo str_repeat("-", 80) . "\n\n";
    
    // Vérifier les classes
    echo "📈 Vérification par classe:\n";
    for ($classe = 1; $classe <= 9; $classe++) {
        $comptesDansClasse = array_filter($accounts, function($key) use ($classe) {
            return substr($key, 0, 1) == $classe;
        }, ARRAY_FILTER_USE_KEY);
        echo "   Classe $classe: " . count($comptesDansClasse) . " comptes\n";
    }
    
} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}
