<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\ExerciceComptable;

$exercices = ExerciceComptable::where('nombre_journaux_saisis', 0)->get();
echo "Recherche d'exercices avec 0 journaux...\n";

foreach ($exercices as $ex) {
    echo "Régularisation de l'exercice: {$ex->intitule} (ID: {$ex->id})\n";
    if (method_exists($ex, 'syncJournaux')) {
        $ex->syncJournaux();
        echo " - Synchronisation effectuée. Nouveau compteur: {$ex->nombre_journaux_saisis}\n";
    } else {
        echo " - Erreur: méthode syncJournaux introuvable sur le modèle.\n";
    }
}
echo "Terminé.\n";
