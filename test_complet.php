<?php
// Test complet du système IA SYSCOHADA CI
echo "=== TEST COMPLET SYSTÈME IA SYSCOHADA CI ===\n\n";

// Simulation de l'upload d'une facture
$_FILES['facture'] = [
    'tmp_name' => 'simple.jpg',
    'type' => 'image/jpeg'
];

// Inclure le traitement IA
require_once 'ia_traitement_test.php';

echo "\n✅ Test terminé !\n";
?>
