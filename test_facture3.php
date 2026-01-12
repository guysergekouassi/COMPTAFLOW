<?php
// Test de la facture3 avec l'API Gemini
require_once 'ia_traitement_standalone.php';

// Simuler l'upload de la facture3
$_FILES['facture'] = [
    'name' => 'facture3.jpg',
    'type' => 'image/jpeg',
    'tmp_name' => 'c:\laragon\www\COMPTAFLOW\facture3.jpg',
    'error' => 0,
    'size' => filesize('c:\laragon\www\COMPTAFLOW\facture3.jpg')
];

echo "=== TEST DE LA FACTURE3 AVEC API GEMINI ===\n";
echo "Fichier: " . $_FILES['facture']['tmp_name'] . "\n";
echo "Taille: " . $_FILES['facture']['size'] . " octets\n";
echo "Type: " . $_FILES['facture']['type'] . "\n";
echo "==========================================\n\n";

// Exécuter le traitement
echo "Résultat de l'analyse:\n";
echo "----------------------\n";

// Le script ia_traitement_standalone.php va s'exécuter automatiquement
?>
