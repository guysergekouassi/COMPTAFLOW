<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\VertexAiService;

$service = new VertexAiService();

// Tentative de trouver le dossier par plusieurs méthodes
$possiblePaths = [
    base_path('storage/app/public/ia_images'),
    'C:/laragon/www/COMPTAFLOW/storage/app/public/ia_images',
    __DIR__ . '/../storage/app/public/ia_images',
    storage_path('app/public/ia_images')
];

$dir = null;
foreach ($possiblePaths as $p) {
    if (is_dir($p)) {
        $dir = $p;
        break;
    }
}

if (!$dir) {
    echo "DÉBOGAGE: SCAN DU ROOT\n";
    $root = base_path();
    echo "Root: $root\n";
    print_r(scandir($root));
    die("ERREUR: Dossier ia_images introuvable.\n");
}

echo "Dossier trouvé: $dir\n";

$all = scandir($dir);
$pdfFiles = [];
foreach ($all as $f) {
    if (str_ends_with(strtolower($f), '.pdf')) {
        $pdfFiles[] = $dir . DIRECTORY_SEPARATOR . $f;
    }
}

// Trier par date
usort($pdfFiles, function($a, $b) {
    return filemtime($b) - filemtime($a);
});

$subset = array_slice($pdfFiles, 0, 60);

echo "Traitement de " . count($subset) . " PDFs...\n";
$results = [
    'success' => 0,
    'failed' => 0,
    'total_amount' => 0,
    'vendors' => []
];

foreach ($subset as $i => $path) {
    $num = $i + 1;
    echo "[$num/60] " . basename($path) . "... ";
    
    try {
        $imgData = base64_encode(file_get_contents($path));
        $res = $service->analyzeInvoice($imgData, 'application/pdf', 'Analyse cette facture.');
        
        if (isset($res['data']['numero_facture'])) {
            $results['success']++;
            $results['total_amount'] += floatval($res['data']['montant_ttc'] ?? 0);
            $vendor = $res['data']['fournisseur_nom'] ?? 'Inconnu';
            $results['vendors'][$vendor] = ($results['vendors'][$vendor] ?? 0) + 1;
            echo "OK ({$vendor})\n";
        } else {
            $results['failed']++;
            echo "REJET\n";
        }
    } catch (\Exception $e) {
        $results['failed']++;
        echo "ERROR: " . $e->getMessage() . "\n";
    }
    usleep(50000); 
}

echo "\n--- ANALYSE PDF (LOT DE 60) ---\n";
echo "Succès: " . $results['success'] . "\n";
echo "Échecs: " . $results['failed'] . "\n";
echo "Montant Total: " . number_format($results['total_amount'], 0, ',', ' ') . " FCFA\n";
arsort($results['vendors']);
echo "Top Fournisseurs:\n";
foreach (array_slice($results['vendors'], 0, 5) as $v => $c) {
    echo "- $v ($c)\n";
}
echo "-------------------------------\n";
