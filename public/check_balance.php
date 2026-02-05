<?php
// Vérification rapide des données
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\EcritureComptable;
use App\Models\ExerciceComptable;

header('Content-Type: text/plain; charset=utf-8');

echo "=== VÉRIFICATION RAPIDE ===\n\n";

// Prendre la première compagnie
$companyId = 1;

// Compter les écritures
$total = EcritureComptable::where('company_id', $companyId)->count();
echo "Total écritures (company {$companyId}): {$total}\n\n";

// Vérifier les exercices
$exercices = ExerciceComptable::where('company_id', $companyId)->get();
echo "Exercices trouvés: " . $exercices->count() . "\n";

foreach ($exercices as $ex) {
    $countById = EcritureComptable::where('company_id', $companyId)
        ->where('exercices_comptables_id', $ex->id)
        ->count();
    
    $countByDate = EcritureComptable::where('company_id', $companyId)
        ->whereBetween('date', [$ex->date_debut, $ex->date_fin])
        ->count();
    
    echo "\n";
    echo "Exercice: {$ex->intitule} (ID: {$ex->id})\n";
    echo "  - Période: {$ex->date_debut} à {$ex->date_fin}\n";
    echo "  - Actif: " . ($ex->is_active ? 'OUI' : 'NON') . "\n";
    echo "  - Écritures avec exercice_id={$ex->id}: {$countById}\n";
    echo "  - Écritures dans les dates: {$countByDate}\n";
}

// Vérifier les écritures sans exercice
$sansExercice = EcritureComptable::where('company_id', $companyId)
    ->whereNull('exercices_comptables_id')
    ->count();

echo "\n\nÉcritures SANS exercice_id: {$sansExercice}\n";

if ($sansExercice > 0) {
    echo "\n⚠️ PROBLÈME: {$sansExercice} écritures n'ont pas de lien avec un exercice!\n";
    echo "\nSOLUTION: Il faut associer ces écritures à un exercice.\n";
    
    // Proposer une requête SQL pour corriger
    $exerciceActif = ExerciceComptable::where('company_id', $companyId)
        ->where('is_active', 1)
        ->first();
    
    if ($exerciceActif) {
        echo "\nRequête SQL pour associer les écritures à l'exercice actif ({$exerciceActif->intitule}):\n\n";
        echo "UPDATE ecriture_comptables \n";
        echo "SET exercices_comptables_id = {$exerciceActif->id} \n";
        echo "WHERE company_id = {$companyId} \n";
        echo "  AND exercices_comptables_id IS NULL \n";
        echo "  AND date BETWEEN '{$exerciceActif->date_debut}' AND '{$exerciceActif->date_fin}';\n";
    }
}
