<?php
// Test avec la nouvelle facture facture1.jpg
echo "=== TEST AVEC FACTURE1.JPG ===\n\n";

// Simulation de l'upload avec facture1.jpg
$_FILES['facture'] = [
    'tmp_name' => 'facture1.jpg',
    'type' => 'image/jpeg',
    'size' => filesize('facture1.jpg')
];

echo "📄 Fichier : facture1.jpg\n";
echo "📏 Taille : " . number_format(filesize('facture1.jpg'), 0, ',', ' ') . " octets\n";
echo "🔍 Analyse en cours...\n\n";

require_once 'ia_traitement_standalone.php';
?>
