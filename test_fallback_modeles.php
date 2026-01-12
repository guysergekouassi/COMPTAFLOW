<?php
// Test du fallback automatique entre modèles Gemini

echo "=== TEST FALLBACK AUTOMATIQUE ENTRE MODÈLES ===\n\n";

// Inclure le fichier de traitement
require_once 'ia_traitement_standalone.php';

// Simuler l'upload de la facture3
$_FILES['facture'] = [
    'name' => 'facture3.jpg',
    'type' => 'image/jpeg',
    'tmp_name' => 'c:\laragon\www\COMPTAFLOW\facture3.jpg',
    'error' => 0,
    'size' => filesize('c:\laragon\www\COMPTAFLOW\facture3.jpg')
];

echo "Test avec facture3.jpg...\n";
echo "Fichier: " . $_FILES['facture']['tmp_name'] . "\n";
echo "Taille: " . $_FILES['facture']['size'] . " octets\n";
echo "==========================================\n\n";

echo "Ordre des modèles à essayer:\n";
echo "1. gemini-2.5-flash (plus rapide)\n";
echo "2. gemini-1.5-flash (alternative rapide)\n";
echo "3. gemini-1.5-pro (plus puissant)\n";
echo "4. gemini-pro (le plus puissant)\n\n";

echo "Résultat attendu:\n";
echo "- Si gemini-2.5-flash a quota dépassé → passage à gemini-1.5-flash\n";
echo "- Si tous les modèles échouent → fallback local\n";
echo "- Logs détaillés dans storage/logs/laravel.log\n\n";

echo "Pour tester via l'interface web:\n";
echo "1. Allez sur http://127.0.1:8000/accounting/scan\n";
echo "2. Uploadez facture3.jpg\n";
echo "3. Vérifiez les logs pour voir quel modèle est utilisé\n";
echo "4. La réponse JSON contiendra 'model_used' et 'models_tried'\n\n";

echo "Exemple de réponse attendue:\n";
echo "{\n";
echo "  \"model_used\": \"gemini-1.5-pro\",\n";
echo "  \"models_tried\": [\"gemini-2.5-flash\", \"gemini-1.5-flash\", \"gemini-1.5-pro\"],\n";
echo "  \"hasVAT\": false,\n";
echo "  ...\n";
echo "}\n\n";

echo "FIN DU TEST\n";
?>
