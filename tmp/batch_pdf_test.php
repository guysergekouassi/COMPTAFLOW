<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\VertexAiService;
use App\Models\IaLog;

$service = new VertexAiService();

// Trouver 10 PDFs différents dans les logs
$pdfs = IaLog::where('image_nom', 'LIKE', '%.pdf')
    ->where('status', 'rejected') // On prend ceux qui ont échoué avant pour prouver que ça marche maintenant
    ->latest()
    ->limit(10)
    ->get();

if ($pdfs->isEmpty()) {
    // Fallback si pas de rejets récents, prendre n'importe quel PDF
    $pdfs = IaLog::where('image_nom', 'LIKE', '%.pdf')->latest()->limit(10)->get();
}

if ($pdfs->isEmpty()) {
    die("Aucun fichier PDF trouvé dans les logs pour le test.\n");
}

echo "Démarrage du test sur " . $pdfs->count() . " fichiers PDF...\n";
echo "Région: " . config('services.vertex_ai.location') . "\n";
echo "--------------------------------------------------\n";

$successCount = 0;
foreach ($pdfs as $index => $log) {
    $num = $index + 1;
    $source = storage_path('app/public/ia_images/' . $log->image_nom);
    
    echo "[$num/10] Fichier: {$log->image_nom}... ";
    
    if (!file_exists($source)) {
        echo "SKIP (Fichier non trouvé sur le disque)\n";
        continue;
    }

    try {
        $imgData = base64_encode(file_get_contents($source));
        $data = $service->analyzeInvoice($imgData, 'application/pdf', 'Analyse cette facture.');
        
        if (isset($data['data']['numero_facture'])) {
            echo "SUCCESS (Facture N° " . $data['data']['numero_facture'] . ")\n";
            $successCount++;
        } else {
            $err = $data['error'] ?? 'Rejet métier / Format JSON invalide';
            echo "FAILED ($err)\n";
        }
    } catch (\Exception $e) {
        echo "ERROR: " . $e->getMessage() . "\n";
    }
}

echo "--------------------------------------------------\n";
echo "BILAN : $successCount succès sur " . $pdfs->count() . " tentatives.\n";
