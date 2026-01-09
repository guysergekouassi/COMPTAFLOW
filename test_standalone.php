<?php
// Simulation de l'upload pour tester
$_FILES['facture'] = [
    'tmp_name' => 'simple.jpg',
    'type' => 'image/jpeg',
    'size' => filesize('simple.jpg')
];

require_once 'ia_traitement_standalone.php';
?>
