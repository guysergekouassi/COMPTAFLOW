<?php
// Test pour vérifier les données du plan comptable
require_once 'vendor/autoload.php';

// Connexion à la base
$app = require_once 'bootstrap/app.php';

try {
    // Récupérer quelques plans comptables pour tester
    $plans = \App\Models\PlanComptable::limit(5)->get(['id', 'numero_de_compte', 'intitule', 'classe', 'adding_strategy']);
    
    echo "=== TEST DES DONNÉES PLAN COMPTABLE ===\n\n";
    
    foreach ($plans as $plan) {
        echo "ID: " . $plan->id . "\n";
        echo "Numéro: " . $plan->numero_de_compte . "\n";
        echo "Intitulé: " . $plan->intitule . "\n";
        echo "Classe: " . ($plan->classe ?? 'NULL') . "\n";
        echo "Strategy: " . ($plan->adding_strategy ?? 'NULL') . "\n";
        echo "--------------------------------\n";
    }
    
} catch (Exception $e) {
    echo "ERREUR: " . $e->getMessage() . "\n";
}
?>
