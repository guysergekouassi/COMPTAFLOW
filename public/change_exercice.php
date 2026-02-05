<?php
// Script pour changer l'exercice actif
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\ExerciceComptable;

header('Content-Type: text/html; charset=utf-8');

echo "<!DOCTYPE html>\n";
echo "<html><head><meta charset='UTF-8'><title>Changer Exercice Actif</title>";
echo "<style>body{font-family:Arial;padding:20px;background:#f5f5f5;}.btn{padding:10px 20px;margin:10px;border:none;border-radius:5px;cursor:pointer;font-weight:bold;}.btn-primary{background:#4CAF50;color:white;}.btn-danger{background:#f44336;color:white;}.success{background:#ccffcc;padding:15px;border-left:5px solid green;margin:20px 0;}</style>";
echo "</head><body>\n";

echo "<h1>🔄 Changer l'Exercice Actif</h1>\n";

$companyId = 1;

if (isset($_POST['action']) && $_POST['action'] === 'change') {
    // Désactiver tous les exercices
    ExerciceComptable::where('company_id', $companyId)
        ->update(['is_active' => 0]);
    
    // Activer l'exercice 2025
    ExerciceComptable::where('company_id', $companyId)
        ->where('id', 5)
        ->update(['is_active' => 1]);
    
    echo "<div class='success'>\n";
    echo "<h2>✅ Succès !</h2>\n";
    echo "<p>L'exercice 2025 est maintenant actif.</p>\n";
    echo "<p><strong>Action requise :</strong> Allez dans votre application et actualisez la page (F5).</p>\n";
    echo "<p>Ensuite, essayez de générer la balance à nouveau.</p>\n";
    echo "</div>\n";
}

$exercices = ExerciceComptable::where('company_id', $companyId)->get();

echo "<h2>Exercices Disponibles</h2>\n";
echo "<table border='1' cellpadding='10' style='border-collapse:collapse;background:white;'>\n";
echo "<tr><th>ID</th><th>Intitulé</th><th>Période</th><th>Actif</th><th>Nb Écritures</th></tr>\n";

foreach ($exercices as $ex) {
    $count = \App\Models\EcritureComptable::where('company_id', $companyId)
        ->where('exercices_comptables_id', $ex->id)
        ->count();
    
    $style = $ex->is_active ? ' style="background:#90EE90;"' : '';
    echo "<tr{$style}>\n";
    echo "<td>{$ex->id}</td>\n";
    echo "<td><strong>{$ex->intitule}</strong></td>\n";
    echo "<td>{$ex->date_debut} → {$ex->date_fin}</td>\n";
    echo "<td>" . ($ex->is_active ? '✅ OUI' : 'NON') . "</td>\n";
    echo "<td><strong style='font-size:18px;color:" . ($count > 0 ? 'green' : 'red') . ";'>{$count}</strong></td>\n";
    echo "</tr>\n";
}
echo "</table>\n";

echo "<h2>Action Recommandée</h2>\n";
echo "<p>L'exercice 2026 est actuellement actif mais n'a <strong>aucune écriture</strong>.</p>\n";
echo "<p>L'exercice 2025 a <strong>364 écritures</strong>.</p>\n";

echo "<form method='POST' style='margin:20px 0;'>\n";
echo "<input type='hidden' name='action' value='change'>\n";
echo "<button type='submit' class='btn btn-primary' onclick='return confirm(\"Êtes-vous sûr de vouloir activer l\\'exercice 2025 ?\")'>🔄 Activer l'Exercice 2025</button>\n";
echo "</form>\n";

echo "<p><em>Note: Vous pouvez aussi simplement sélectionner l'exercice 2025 dans la sidebar de l'application.</em></p>\n";

echo "</body></html>";
