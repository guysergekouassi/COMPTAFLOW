<?php
// Script de diagnostic simplifié pour la balance
require __DIR__.'/../vendor/autoload.php';

$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\EcritureComptable;
use App\Models\ExerciceComptable;
use App\Models\PlanComptable;
use App\Models\Company;

echo "<!DOCTYPE html>\n";
echo "<html><head><meta charset='UTF-8'><title>Diagnostic Balance</title>";
echo "<style>body{font-family:Arial;padding:20px;background:#f5f5f5;}table{border-collapse:collapse;width:100%;background:white;}th,td{border:1px solid #ddd;padding:8px;text-align:left;}th{background:#4CAF50;color:white;}.highlight{background:#90EE90;}.warning{background:#ffcccc;padding:15px;border-left:5px solid red;margin:20px 0;}.success{background:#ccffcc;padding:15px;border-left:5px solid green;margin:20px 0;}</style>";
echo "</head><body>\n";
echo "<h1>🔍 Diagnostic Balance - Données Vides</h1>\n";

// Récupérer toutes les compagnies
$companies = Company::all();
echo "<h2>1. Compagnies Disponibles</h2>\n";
echo "<table><tr><th>ID</th><th>Nom</th><th>Nb Exercices</th><th>Nb Écritures</th></tr>\n";
foreach ($companies as $company) {
    $nbExercices = ExerciceComptable::where('company_id', $company->id)->count();
    $nbEcritures = EcritureComptable::where('company_id', $company->id)->count();
    echo "<tr><td>{$company->id}</td><td>{$company->company_name}</td><td>{$nbExercices}</td><td><strong>{$nbEcritures}</strong></td></tr>\n";
}
echo "</table>\n";

// Analyser chaque compagnie
foreach ($companies as $company) {
    $companyId = $company->id;
    $companyName = $company->company_name;
    
    echo "<hr style='margin:30px 0;'>\n";
    echo "<h2>📊 Analyse pour: {$companyName} (ID: {$companyId})</h2>\n";
    
    // Exercices
    $exercices = ExerciceComptable::where('company_id', $companyId)->get();
    if ($exercices->isEmpty()) {
        echo "<div class='warning'><strong>⚠️ Aucun exercice trouvé pour cette compagnie</strong></div>\n";
        continue;
    }
    
    echo "<h3>Exercices Disponibles</h3>\n";
    echo "<table><tr><th>ID</th><th>Intitulé</th><th>Début</th><th>Fin</th><th>Actif</th><th>Nb Écritures</th><th>Nb Écritures (dates)</th></tr>\n";
    
    foreach ($exercices as $ex) {
        $countById = EcritureComptable::where('company_id', $companyId)
            ->where('exercices_comptables_id', $ex->id)
            ->count();
        
        $countByDate = EcritureComptable::where('company_id', $companyId)
            ->whereBetween('date', [$ex->date_debut, $ex->date_fin])
            ->count();
        
        $highlight = $ex->is_active ? ' class="highlight"' : '';
        echo "<tr{$highlight}>\n";
        echo "<td>{$ex->id}</td>\n";
        echo "<td>{$ex->intitule}</td>\n";
        echo "<td>{$ex->date_debut}</td>\n";
        echo "<td>{$ex->date_fin}</td>\n";
        echo "<td>" . ($ex->is_active ? '✅ OUI' : 'NON') . "</td>\n";
        echo "<td><strong>{$countById}</strong></td>\n";
        echo "<td><strong>{$countByDate}</strong></td>\n";
        echo "</tr>\n";
    }
    echo "</table>\n";
    
    // Statistiques détaillées
    $totalEcritures = EcritureComptable::where('company_id', $companyId)->count();
    $ecrituresAvecExercice = EcritureComptable::where('company_id', $companyId)
        ->whereNotNull('exercices_comptables_id')
        ->count();
    $ecrituresSansExercice = EcritureComptable::where('company_id', $companyId)
        ->whereNull('exercices_comptables_id')
        ->count();
    
    echo "<h3>Statistiques Écritures</h3>\n";
    echo "<ul>\n";
    echo "<li><strong>Total écritures:</strong> {$totalEcritures}</li>\n";
    echo "<li><strong>Écritures AVEC exercice_id:</strong> {$ecrituresAvecExercice}</li>\n";
    echo "<li><strong>Écritures SANS exercice_id:</strong> <span style='color:" . ($ecrituresSansExercice > 0 ? 'red' : 'green') . ";font-weight:bold;'>{$ecrituresSansExercice}</span></li>\n";
    echo "</ul>\n";
    
    if ($ecrituresSansExercice > 0) {
        echo "<div class='warning'>\n";
        echo "<h4>⚠️ PROBLÈME DÉTECTÉ</h4>\n";
        echo "<p><strong>{$ecrituresSansExercice} écritures n'ont pas de lien avec un exercice</strong> (champ <code>exercices_comptables_id</code> est NULL).</p>\n";
        echo "<p>C'est pourquoi la balance est vide quand vous filtrez par exercice !</p>\n";
        echo "<h5>Solutions:</h5>\n";
        echo "<ol>\n";
        echo "<li><strong>Option 1 (Recommandée):</strong> Associer ces écritures à un exercice en mettant à jour le champ <code>exercices_comptables_id</code></li>\n";
        echo "<li><strong>Option 2 (Temporaire):</strong> Retirer le filtre par exercice dans le contrôleur Balance</li>\n";
        echo "</ol>\n";
        echo "</div>\n";
        
        // Afficher quelques exemples
        echo "<h4>Exemples d'écritures sans exercice:</h4>\n";
        $exemples = EcritureComptable::where('company_id', $companyId)
            ->whereNull('exercices_comptables_id')
            ->with('planComptable')
            ->limit(10)
            ->get();
        
        echo "<table><tr><th>ID</th><th>Date</th><th>Compte</th><th>Exercice ID</th><th>Débit</th><th>Crédit</th></tr>\n";
        foreach ($exemples as $ec) {
            echo "<tr>\n";
            echo "<td>{$ec->id}</td>\n";
            echo "<td>{$ec->date}</td>\n";
            echo "<td>" . ($ec->planComptable ? $ec->planComptable->numero_de_compte : 'N/A') . "</td>\n";
            echo "<td style='color:red;font-weight:bold;'>" . ($ec->exercices_comptables_id ?? 'NULL') . "</td>\n";
            echo "<td>" . number_format($ec->debit, 0, ',', ' ') . "</td>\n";
            echo "<td>" . number_format($ec->credit, 0, ',', ' ') . "</td>\n";
            echo "</tr>\n";
        }
        echo "</table>\n";
    } else {
        echo "<div class='success'>\n";
        echo "<h4>✅ TOUT EST OK</h4>\n";
        echo "<p>Toutes les écritures sont bien liées à un exercice.</p>\n";
        echo "</div>\n";
    }
}

echo "</body></html>";
