<?php
/**
 * Script de nettoyage des journaux pour la company_id 33
 * À exécuter via: php cleanup_journals.php
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\CodeJournal;
use App\Models\JournalSaisi;

echo "=== NETTOYAGE DES JOURNAUX POUR COMPANY 33 ===\n\n";

// Afficher les journaux existants
$existingJournals = CodeJournal::where('company_id', 33)->get();
echo "Journaux actuels :\n";
foreach ($existingJournals as $journal) {
    echo "  - ID: {$journal->id}, Code: {$journal->code_journal}, Intitulé: {$journal->intitule}\n";
}
echo "\n";

if ($existingJournals->isEmpty()) {
    echo "Aucun journal à supprimer.\n";
    exit(0);
}

echo "Voulez-vous supprimer ces journaux ? (oui/non) : ";
$handle = fopen("php://stdin", "r");
$line = trim(fgets($handle));

if (strtolower($line) !== 'oui') {
    echo "Opération annulée.\n";
    exit(0);
}

echo "\nSuppression en cours...\n";

// Supprimer d'abord les JournalSaisi liés
$journalIds = $existingJournals->pluck('id')->toArray();
$deletedSaisis = JournalSaisi::whereIn('code_journals_id', $journalIds)->delete();
echo "  - {$deletedSaisis} journaux saisis supprimés\n";

// Supprimer les CodeJournal
$deletedJournals = CodeJournal::where('company_id', 33)->delete();
echo "  - {$deletedJournals} codes journaux supprimés\n";

echo "\n✓ Nettoyage terminé avec succès !\n";
echo "Vous pouvez maintenant relancer votre importation.\n";
