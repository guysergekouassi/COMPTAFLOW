<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\ExerciceComptable;
use App\Models\CodeJournal;
use App\Models\JournalSaisi;

$ex30 = ExerciceComptable::find(30); // 2026
$ex32 = ExerciceComptable::find(32); // 2027

echo "--- EXERCICE 30 (2026) ---\n";
if ($ex30) {
    echo "ID: {$ex30->id}, Intitule: {$ex30->intitule}, Company: {$ex30->company_id}\n";
    $jCount = JournalSaisi::where('exercices_comptables_id', 30)->count();
    echo "Journaux Saisis: $jCount\n";
    if ($jCount > 0) {
        $firstJ = JournalSaisi::where('exercices_comptables_id', 30)->first();
        echo "Exemple Journal: Annee {$firstJ->annee}, Mois {$firstJ->mois}, CodeJournalID {$firstJ->code_journals_id}\n";
        $cj = CodeJournal::find($firstJ->code_journals_id);
        if ($cj) {
            echo "Code Journal lié: {$cj->code_journal}, Company: {$cj->company_id}\n";
        } else {
            echo "ERREUR: Code Journal {$firstJ->code_journals_id} introuvable !\n";
        }
    }
}

echo "\n--- EXERCICE 32 (2027) ---\n";
if ($ex32) {
    echo "ID: {$ex32->id}, Intitule: {$ex32->intitule}, Company: {$ex32->company_id}\n";
    $codes = CodeJournal::where('company_id', $ex32->company_id)->get();
    echo "Codes Journaux pour cette société: " . $codes->count() . "\n";
    foreach ($codes as $c) {
        echo " - {$c->code_journal} (ID: {$c->id})\n";
    }
}

echo "\n--- TOUS LES CODES JOURNAUX --- \n";
$allCodes = CodeJournal::all();
foreach ($allCodes as $c) {
    echo "ID: {$c->id}, Code: {$c->code_journal}, Company: {$c->company_id}\n";
}
