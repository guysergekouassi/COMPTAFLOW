<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "📊 EXTRACTION COMPLÈTE - Plan Comptable SYSCOHADA depuis TXT\n";
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
    $lineNumber = 0;
    
    foreach ($lines as $line) {
        $lineNumber++;
        $line = trim($line);
        
        // Ignorer les lignes vides et les en-têtes
        if (empty($line) || 
            stripos($line, 'www.Droit-Afrique') !== false ||
            stripos($line, 'OHADA') !== false ||
            stripos($line, 'Plan comptable') !== false ||
            stripos($line, 'Classe ') !== false ||
            stripos($line, '/31') !== false ||
            $line === '\\f') {
            continue;
        }
        
        // Pattern 1: Ligne commençant par un numéro de compte (sans tiret)
        // Ex: "10 Capital", "101 Capital social", "1011 Capital souscrit, non appelé"
        if (preg_match('/^(\d{1,4})\s+(.+)$/u', $line, $matches)) {
            $numero = $matches[1];
            $libelle = trim($matches[2]);
            $accounts[$numero] = mb_strtoupper($libelle);
            continue;
        }
        
        // Pattern 2: Ligne avec tiret (sous-compte)
        // Ex: "- 1011 Capital souscrit, non appelé"
        if (preg_match('/^-\s+(\d{1,4})\s+(.+)$/u', $line, $matches)) {
            $numero = $matches[1];
            $libelle = trim($matches[2]);
            $accounts[$numero] = mb_strtoupper($libelle);
            continue;
        }
        
        // Pattern 3: Ligne avec espace puis numéro (autre format de sous-compte)
        // Ex: " 101 Capital social"
        if (preg_match('/^\s+(\d{1,4})\s+(.+)$/u', $line, $matches)) {
            $numero = $matches[1];
            $libelle = trim($matches[2]);
            $accounts[$numero] = mb_strtoupper($libelle);
            continue;
        }
    }
    
    echo "✓ Comptes extraits: " . count($accounts) . "\n\n";
    
    if (count($accounts) == 0) {
        die("❌ Aucun compte extrait!\n");
    }
    
    // Trier par numéro de compte
    ksort($accounts, SORT_NATURAL);
    
    // Générer le fichier PHP
    $phpCode = "<?php\n\n";
    $phpCode .= "/**\n";
    $phpCode .= " * PLAN COMPTABLE SYSCOHADA COMPLET\n";
    $phpCode .= " * Extrait de syscohada.txt (Source: www.Droit-Afrique.com)\n";
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
    echo "📁 Fichier généré: $outputFile\n";
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
    
    echo "\n📋 Aperçu (30 premiers comptes):\n";
    echo str_repeat("-", 80) . "\n";
    $preview = array_slice($accounts, 0, 30, true);
    foreach ($preview as $num => $lib) {
        printf("   %-6s => %s\n", $num, substr($lib, 0, 60));
    }
    echo str_repeat("-", 80) . "\n";
    
    echo "\n✅ Fichier prêt! Vous pouvez maintenant mettre à jour AdminConfigController.\n";
    
} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}
