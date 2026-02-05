<?php
// Vérifier la relation planComptable des écritures
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\EcritureComptable;
use App\Models\PlanComptable;

header('Content-Type: text/html; charset=utf-8');

echo "<!DOCTYPE html>\n";
echo "<html><head><meta charset='UTF-8'><title>Check Relations</title>";
echo "<style>body{font-family:monospace;padding:20px;background:#f5f5f5;}table{border-collapse:collapse;background:white;}th,td{border:1px solid #ddd;padding:8px;}</style>";
echo "</head><body>\n";

echo "<h1>🔍 Vérification des Relations PlanComptable</h1>\n";

$companyId = 1;
$exerciceId = 5;

// Prendre quelques écritures de l'exercice
$ecritures = EcritureComptable::where('company_id', $companyId)
    ->where('exercices_comptables_id', $exerciceId)
    ->limit(20)
    ->get();

echo "<h2>Écritures de l'exercice 5 (20 premières)</h2>\n";
echo "<table><tr><th>ID</th><th>Date</th><th>plan_comptable_id</th><th>Relation OK?</th><th>Compte trouvé?</th><th>Numéro</th></tr>\n";

foreach ($ecritures as $ec) {
    $hasRelation = $ec->planComptable !== null;
    
    // Vérifier si le compte existe dans la table
    $compteExists = PlanComptable::withoutGlobalScopes()
        ->where('id', $ec->plan_comptable_id)
        ->exists();
    
    $numero = $hasRelation ? $ec->planComptable->numero_de_compte : 'N/A';
    
    $style = $hasRelation ? ' style="background:#ccffcc;"' : ' style="background:#ffcccc;"';
    
    echo "<tr{$style}>\n";
    echo "<td>{$ec->id}</td>\n";
    echo "<td>{$ec->date}</td>\n";
    echo "<td>{$ec->plan_comptable_id}</td>\n";
    echo "<td>" . ($hasRelation ? '✅ OUI' : '❌ NON') . "</td>\n";
    echo "<td>" . ($compteExists ? '✅ OUI' : '❌ NON') . "</td>\n";
    echo "<td>{$numero}</td>\n";
    echo "</tr>\n";
}
echo "</table>\n";

// Compter les écritures sans relation valide
$totalEcritures = EcritureComptable::where('company_id', $companyId)
    ->where('exercices_comptables_id', $exerciceId)
    ->count();

$ecrituresAvecCompte = EcritureComptable::where('company_id', $companyId)
    ->where('exercices_comptables_id', $exerciceId)
    ->whereHas('planComptable')
    ->count();

echo "<h2>Statistiques</h2>\n";
echo "<ul>\n";
echo "<li><strong>Total écritures exercice 5:</strong> {$totalEcritures}</li>\n";
echo "<li><strong>Écritures avec relation planComptable valide:</strong> {$ecrituresAvecCompte}</li>\n";
echo "<li><strong>Écritures SANS relation valide:</strong> " . ($totalEcritures - $ecrituresAvecCompte) . "</li>\n";
echo "</ul>\n";

if ($ecrituresAvecCompte == 0) {
    echo "<div style='background:#ffcccc;padding:15px;border-left:5px solid red;'>\n";
    echo "<h3>⚠️ PROBLÈME CRITIQUE</h3>\n";
    echo "<p>AUCUNE écriture n'a de relation valide avec planComptable !</p>\n";
    echo "<p>Cela explique pourquoi whereHas() ne retourne rien.</p>\n";
    echo "<p><strong>Solution:</strong> Il faut retirer complètement le filtre par comptes ou utiliser une jointure manuelle.</p>\n";
    echo "</div>\n";
}

echo "</body></html>";
