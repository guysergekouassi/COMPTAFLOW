<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "📊 EXTRACTION ULTRA-COMPLÈTE - Plan Comptable SYSCOHADA\n";
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
        $line = rtrim($line); // Garder les espaces de début mais enlever ceux de fin
        
        // Ignorer les lignes vides
        if (trim($line) === '') {
            continue;
        }
        
        // Ignorer les en-têtes et métadonnées spécifiques
        $trimmed = trim($line);
        if (stripos($trimmed, 'www.Droit-Afrique') !== false ||
            $trimmed === 'OHADA' ||
            stripos($trimmed, 'Plan comptable OHADA') !== false ||
            stripos($trimmed, 'Suivant l\'acte uniforme') !== false ||
            stripos($trimmed, 'des comptabilités') !== false ||
            stripos($trimmed, 'le 22 février') !== false ||
            preg_match('/^Classe\s+\d+\s*[-‐–—]\s*/u', $trimmed) ||  // "Classe 1 - ..."
            preg_match('/^\d+\/\d+$/', $trimmed) ||  // "1/31", "2/31"
            $trimmed === '\f') {
            continue;
        }
        
        // EXTRACTION ULTRA-ROBUSTE
        // Pattern universel : chercher un numéro de 1 à 4 chiffres précédé d'éventuels caractères non-lettres/non-chiffres et suivi d'un libellé
        if (preg_match('/^[^\p{L}\d]*(\d{1,4})\s+(.+)$/u', $line, $matches)) {
            $numero = $matches[1];
            $libelle = trim($matches[2]);
            
            // Nettoyer le libellé (enlever les caractères spéciaux en fin)
            $libelle = preg_replace('/\s+$/', '', $libelle);
            
            // Ignorer si le libellé est vide ou trop court
            if (strlen($libelle) < 2) {
                continue;
            }
            
            // Ignorer les faux positifs (lignes de pagination, etc.)
            if (preg_match('/^\d+$/', $libelle)) {  // Juste un chiffre
                continue;
            }
            
            $accounts[$numero] = mb_strtoupper($libelle);
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
    $phpCode .= " * PLAN COMPTABLE SYSCOHADA COMPLET - EXTRACTION ULTRA-ROBUSTE\n";
    $phpCode .= " * Extrait de syscohada.txt (Source: www.Droit-Afrique.com)\n";
    $phpCode .= " * Total: " . count($accounts) . " comptes\n";
    $phpCode .= " * Date d'extraction: " . date('Y-m-d H:i:s') . "\n";
    $phpCode .= " * TOUS les comptes, sous-comptes et sous-sous-comptes inclus\n";
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
    
    echo "✅ EXTRACTION ULTRA-COMPLÈTE RÉUSSIE!\n";
    echo "📁 Fichier: $outputFile\n";
    echo "📊 Total: " . count($accounts) . " comptes\n\n";
    
    // Statistiques détaillées par classe
    echo "📈 Répartition détaillée par classe:\n";
    echo str_repeat("-", 80) . "\n";
    for ($classe = 1; $classe <= 9; $classe++) {
        $comptesDansClasse = array_filter($accounts, function($key) use ($classe) {
            return substr($key, 0, 1) == $classe;
        }, ARRAY_FILTER_USE_KEY);
        
        $count = count($comptesDansClasse);
        echo sprintf("   Classe %d: %4d comptes", $classe, $count);
        
        // Afficher quelques exemples
        $exemples = array_slice($comptesDansClasse, 0, 3, true);
        if (!empty($exemples)) {
            $exList = [];
            foreach ($exemples as $num => $lib) {
                $exList[] = $num;
            }
            echo " (ex: " . implode(", ", $exList) . "...)";
        }
        echo "\n";
    }
    echo str_repeat("-", 80) . "\n\n";
    
    // Afficher un aperçu complet
    echo "📋 Aperçu complet (50 premiers comptes):\n";
    echo str_repeat("-", 80) . "\n";
    $preview = array_slice($accounts, 0, 50, true);
    foreach ($preview as $num => $lib) {
        printf("   %-6s => %s\n", $num, substr($lib, 0, 65));
    }
    echo str_repeat("-", 80) . "\n";
    
    echo "\n✅ Fichier prêt! TOUS les comptes SYSCOHADA ont été extraits.\n";
    
} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}
