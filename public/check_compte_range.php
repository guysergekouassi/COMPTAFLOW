<?php
// Vérifier la plage de comptes disponible
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\PlanComptable;
use App\Models\EcritureComptable;

header('Content-Type: text/html; charset=utf-8');

echo "<!DOCTYPE html>\n";
echo "<html><head><meta charset='UTF-8'><title>Plage de Comptes</title>";
echo "<style>body{font-family:Arial;padding:20px;background:#f5f5f5;}.info{background:#cce5ff;padding:15px;border-left:5px solid blue;margin:20px 0;}.warning{background:#fff3cd;padding:15px;border-left:5px solid orange;margin:20px 0;}table{border-collapse:collapse;background:white;width:100%;}th,td{border:1px solid #ddd;padding:8px;text-align:left;}</style>";
echo "</head><body>\n";

echo "<h1>📊 Analyse de la Plage de Comptes</h1>\n";

$companyId = 1;
$exerciceId = 5;

// Récupérer tous les comptes du plan comptable
$allComptes = PlanComptable::withoutGlobalScopes()
    ->where('company_id', $companyId)
    ->orderBy('numero_de_compte')
    ->get();

$premier = $allComptes->first();
$dernier = $allComptes->last();

echo "<div class='info'>\n";
echo "<h2>Plan Comptable</h2>\n";
echo "<ul>\n";
echo "<li><strong>Nombre total de comptes:</strong> {$allComptes->count()}</li>\n";
echo "<li><strong>Premier compte:</strong> {$premier->numero_de_compte} - {$premier->intitule} (ID: {$premier->id})</li>\n";
echo "<li><strong>Dernier compte:</strong> {$dernier->numero_de_compte} - {$dernier->intitule} (ID: {$dernier->id})</li>\n";
echo "</ul>\n";
echo "</div>\n";

// Récupérer les comptes utilisés dans les écritures
$comptesUtilises = EcritureComptable::where('company_id', $companyId)
    ->where('exercices_comptables_id', $exerciceId)
    ->with('planComptable')
    ->get()
    ->pluck('planComptable.numero_de_compte')
    ->unique()
    ->sort()
    ->values();

echo "<div class='info'>\n";
echo "<h2>Comptes Utilisés dans l'Exercice 5</h2>\n";
echo "<ul>\n";
echo "<li><strong>Nombre de comptes distincts:</strong> {$comptesUtilises->count()}</li>\n";
if ($comptesUtilises->count() > 0) {
    echo "<li><strong>Premier compte utilisé:</strong> {$comptesUtilises->first()}</li>\n";
    echo "<li><strong>Dernier compte utilisé:</strong> {$comptesUtilises->last()}</li>\n";
}
echo "</ul>\n";
echo "</div>\n";

// Vérifier si les comptes utilisés sont dans le plan comptable
$comptesUtilisesIds = EcritureComptable::where('company_id', $companyId)
    ->where('exercices_comptables_id', $exerciceId)
    ->pluck('plan_comptable_id')
    ->unique();

$comptesExistants = PlanComptable::withoutGlobalScopes()
    ->where('company_id', $companyId)
    ->whereIn('id', $comptesUtilisesIds)
    ->count();

echo "<div class='info'>\n";
echo "<h2>Vérification</h2>\n";
echo "<ul>\n";
echo "<li><strong>Comptes utilisés (IDs uniques):</strong> {$comptesUtilisesIds->count()}</li>\n";
echo "<li><strong>Comptes existants dans le plan:</strong> {$comptesExistants}</li>\n";
echo "</ul>\n";
echo "</div>\n";

// Test avec whereHas
$testWhereHas = EcritureComptable::where('company_id', $companyId)
    ->where('exercices_comptables_id', $exerciceId)
    ->whereHas('planComptable', function($q) use ($premier, $dernier) {
        $q->where('numero_de_compte', '>=', $premier->numero_de_compte)
          ->where('numero_de_compte', '<=', $dernier->numero_de_compte);
    })
    ->count();

echo "<div class='warning'>\n";
echo "<h2>🧪 Test whereHas avec TOUTE la plage</h2>\n";
echo "<p><strong>Requête:</strong></p>\n";
echo "<pre>whereHas('planComptable', function(\$q) {\n";
echo "    \$q->where('numero_de_compte', '>=', '{$premier->numero_de_compte}')\n";
echo "      ->where('numero_de_compte', '<=', '{$dernier->numero_de_compte}');\n";
echo "})</pre>\n";
echo "<p><strong>Résultat:</strong> <span style='font-size:24px;color:" . ($testWhereHas > 0 ? 'green' : 'red') . ";'>{$testWhereHas} écritures</span></p>\n";
echo "</div>\n";

// Afficher les comptes utilisés
echo "<h2>Liste des Comptes Utilisés</h2>\n";
echo "<table><tr><th>Numéro</th><th>Intitulé</th><th>Nb Écritures</th></tr>\n";

$comptesDetails = EcritureComptable::where('company_id', $companyId)
    ->where('exercices_comptables_id', $exerciceId)
    ->with('planComptable')
    ->get()
    ->groupBy('plan_comptable_id');

foreach ($comptesDetails as $compteId => $ecritures) {
    $compte = $ecritures->first()->planComptable;
    if ($compte) {
        echo "<tr>\n";
        echo "<td><strong>{$compte->numero_de_compte}</strong></td>\n";
        echo "<td>{$compte->intitule}</td>\n";
        echo "<td>{$ecritures->count()}</td>\n";
        echo "</tr>\n";
    }
}
echo "</table>\n";

echo "</body></html>";
