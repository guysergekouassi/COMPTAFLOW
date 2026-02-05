<?php
// Script pour débugger la requête de balance exacte
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\EcritureComptable;
use App\Models\ExerciceComptable;
use App\Models\PlanComptable;

header('Content-Type: text/html; charset=utf-8');

echo "<!DOCTYPE html>\n";
echo "<html><head><meta charset='UTF-8'><title>Debug Balance Query</title>";
echo "<style>body{font-family:Arial;padding:20px;background:#f5f5f5;}table{border-collapse:collapse;width:100%;background:white;margin:10px 0;}th,td{border:1px solid #ddd;padding:8px;text-align:left;}th{background:#4CAF50;color:white;}.warning{background:#ffcccc;padding:15px;border-left:5px solid red;margin:20px 0;}.info{background:#cce5ff;padding:15px;border-left:5px solid blue;margin:20px 0;}</style>";
echo "</head><body>\n";

echo "<h1>🔍 Debug Balance Query</h1>\n";

$companyId = 1;
$exerciceId = 5; // Exercice 2025

$exercice = ExerciceComptable::find($exerciceId);

echo "<div class='info'>\n";
echo "<h3>Exercice Sélectionné</h3>\n";
echo "<p><strong>ID:</strong> {$exercice->id}</p>\n";
echo "<p><strong>Intitulé:</strong> {$exercice->intitule}</p>\n";
echo "<p><strong>Période:</strong> {$exercice->date_debut} → {$exercice->date_fin}</p>\n";
echo "</div>\n";

// Récupérer tous les comptes
$allComptes = PlanComptable::withoutGlobalScopes()
    ->where('company_id', $companyId)
    ->orderBy('numero_de_compte')
    ->get();

echo "<h2>Plan Comptable ({$allComptes->count()} comptes)</h2>\n";
echo "<table><tr><th>ID</th><th>Numéro</th><th>Intitulé</th><th>Nb Écritures (exercice 2025)</th></tr>\n";

$totalEcrituresParCompte = 0;
foreach ($allComptes->take(20) as $compte) {
    $nbEcritures = EcritureComptable::where('company_id', $companyId)
        ->where('exercices_comptables_id', $exerciceId)
        ->where('plan_comptable_id', $compte->id)
        ->count();
    
    $totalEcrituresParCompte += $nbEcritures;
    
    $style = $nbEcritures > 0 ? ' style="background:#ccffcc;"' : '';
    echo "<tr{$style}>\n";
    echo "<td>{$compte->id}</td>\n";
    echo "<td><strong>{$compte->numero_de_compte}</strong></td>\n";
    echo "<td>{$compte->intitule}</td>\n";
    echo "<td><strong>{$nbEcritures}</strong></td>\n";
    echo "</tr>\n";
}
echo "</table>\n";
echo "<p><em>Affichage des 20 premiers comptes seulement...</em></p>\n";

// Test avec TOUS les comptes
$tousComptesIds = $allComptes->pluck('id');

echo "<h2>Test Requête Balance (TOUS LES COMPTES)</h2>\n";

$query = EcritureComptable::where('company_id', $companyId)
    ->whereIn('plan_comptable_id', $tousComptesIds)
    ->whereBetween('date', [$exercice->date_debut, $exercice->date_fin])
    ->where('exercices_comptables_id', $exerciceId);

$count = $query->count();

echo "<div class='info'>\n";
echo "<h3>Résultat</h3>\n";
echo "<p><strong>Nombre d'écritures trouvées:</strong> <span style='font-size:24px;color:" . ($count > 0 ? 'green' : 'red') . ";'>{$count}</span></p>\n";
echo "</div>\n";

if ($count > 0) {
    echo "<h3>Exemples d'écritures trouvées (10 premières)</h3>\n";
    $exemples = $query->with('planComptable')->limit(10)->get();
    
    echo "<table><tr><th>ID</th><th>Date</th><th>Compte</th><th>Libellé</th><th>Débit</th><th>Crédit</th></tr>\n";
    foreach ($exemples as $ec) {
        echo "<tr>\n";
        echo "<td>{$ec->id}</td>\n";
        echo "<td>{$ec->date}</td>\n";
        echo "<td>" . ($ec->planComptable ? $ec->planComptable->numero_de_compte : 'N/A') . "</td>\n";
        echo "<td>" . substr($ec->libelle ?? '', 0, 50) . "</td>\n";
        echo "<td>" . number_format($ec->debit, 0, ',', ' ') . "</td>\n";
        echo "<td>" . number_format($ec->credit, 0, ',', ' ') . "</td>\n";
        echo "</tr>\n";
    }
    echo "</table>\n";
} else {
    echo "<div class='warning'>\n";
    echo "<h3>⚠️ Aucune écriture trouvée !</h3>\n";
    echo "<p>Vérification des critères:</p>\n";
    echo "<ul>\n";
    
    // Test sans filtre exercice
    $sansExercice = EcritureComptable::where('company_id', $companyId)
        ->whereIn('plan_comptable_id', $tousComptesIds)
        ->whereBetween('date', [$exercice->date_debut, $exercice->date_fin])
        ->count();
    echo "<li>Sans filtre exercice: <strong>{$sansExercice}</strong> écritures</li>\n";
    
    // Test sans filtre date
    $sansDate = EcritureComptable::where('company_id', $companyId)
        ->whereIn('plan_comptable_id', $tousComptesIds)
        ->where('exercices_comptables_id', $exerciceId)
        ->count();
    echo "<li>Sans filtre date: <strong>{$sansDate}</strong> écritures</li>\n";
    
    // Test sans filtre comptes
    $sansComptes = EcritureComptable::where('company_id', $companyId)
        ->whereBetween('date', [$exercice->date_debut, $exercice->date_fin])
        ->where('exercices_comptables_id', $exerciceId)
        ->count();
    echo "<li>Sans filtre comptes: <strong>{$sansComptes}</strong> écritures</li>\n";
    
    echo "</ul>\n";
    echo "</div>\n";
}

// Vérifier les dates des écritures
echo "<h2>Distribution des Écritures par Date</h2>\n";
$ecrituresParDate = EcritureComptable::where('company_id', $companyId)
    ->where('exercices_comptables_id', $exerciceId)
    ->selectRaw('DATE(date) as date_only, COUNT(*) as count')
    ->groupBy('date_only')
    ->orderBy('date_only')
    ->limit(20)
    ->get();

echo "<table><tr><th>Date</th><th>Nombre d'écritures</th></tr>\n";
foreach ($ecrituresParDate as $row) {
    echo "<tr><td>{$row->date_only}</td><td><strong>{$row->count}</strong></td></tr>\n";
}
echo "</table>\n";

echo "</body></html>";
