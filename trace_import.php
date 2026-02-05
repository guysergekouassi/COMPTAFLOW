<?php
/**
 * Script pour tracer exactement où les journaux sont créés
 * À exécuter via: php trace_import.php
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\CodeJournal;
use App\Models\ImportStaging;
use Illuminate\Support\Facades\DB;

echo "=== TRAÇAGE DE L'IMPORTATION ===\n\n";

// Activer le query log
DB::enableQueryLog();

// Compter les journaux AVANT
$countBefore = CodeJournal::where('company_id', 33)->count();
echo "Journaux AVANT : $countBefore\n\n";

// Simuler l'accès à la page de staging (import #123)
echo "Simulation de l'accès à la page de staging...\n";

$import = ImportStaging::find(123);
if (!$import) {
    echo "Import #123 introuvable\n";
    exit(1);
}

echo "Import trouvé : {$import->file_name}\n";
echo "Status : {$import->status}\n";
echo "Mapping : " . json_encode($import->mapping) . "\n\n";

// Compter les journaux APRÈS
$countAfter = CodeJournal::where('company_id', 33)->count();
echo "\nJournaux APRÈS : $countAfter\n";

if ($countAfter > $countBefore) {
    echo "\n⚠ ALERTE : " . ($countAfter - $countBefore) . " journal(aux) créé(s) !\n\n";
    
    echo "Requêtes SQL exécutées :\n";
    foreach (DB::getQueryLog() as $query) {
        if (str_contains($query['query'], 'code_journals') && str_contains($query['query'], 'insert')) {
            echo "  - INSERT détecté : " . $query['query'] . "\n";
            echo "    Bindings : " . json_encode($query['bindings']) . "\n";
        }
    }
    
    echo "\nJournaux créés :\n";
    $newJournals = CodeJournal::where('company_id', 33)
        ->where('created_at', '>=', now()->subMinute())
        ->get();
    foreach ($newJournals as $j) {
        echo "  - {$j->code_journal} | {$j->intitule}\n";
    }
} else {
    echo "\n✓ Aucun journal créé pendant la simulation\n";
}
