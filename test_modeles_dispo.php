<?php
// Test direct du fallback automatique entre modèles

echo "=== TEST FALLBACK AUTOMATIQUE GEMINI ===\n\n";

// Configuration
$api_key = "AIzaSyDuwMm9cdo_vTqBe9j3degykq4rL-kOKVU";

// Liste des modèles à essayer dans l'ordre
$models = [
    "gemini-flash-latest",    // Premier choix - le plus rapide
    "gemini-2.5-flash",      // Deuxième choix - rapide et économique
    "gemini-1.5-flash",      // Troisième choix - alternative rapide
    "gemini-1.5-pro",        // Quatrième choix - plus puissant
    "gemini-pro"             // Cinquième choix - le plus puissant
];

// Fonction de test simple pour chaque modèle
function testModel($model, $api_key) {
    $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$api_key}";
    
    // Payload simple pour tester
    $payload = [
        "contents" => [
            [
                "parts" => [
                    ["text" => "Test simple - réponds avec: OK"]
                ]
            ]
        ],
        "generationConfig" => [
            "temperature" => 0.1,
            "maxOutputTokens" => 10
        ]
    ];
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);

    echo "Modèle: $model\n";
    echo "HTTP: $http_code\n";
    echo "Error: " . ($error ?: 'None') . "\n";
    
    if ($http_code === 200) {
        echo "✅ SUCCÈS - Modèle disponible\n";
        return true;
    } elseif ($http_code == 429) {
        echo "❌ QUOTA DÉPASSÉ - Passage au modèle suivant\n";
        return false;
    } else {
        echo "❌ ERREUR - Passage au modèle suivant\n";
        return false;
    }
}

echo "Test de disponibilité des modèles:\n";
echo "=================================\n\n";

$working_model = null;
$models_tried = [];

foreach ($models as $model) {
    $models_tried[] = $model;
    if (testModel($model, $api_key)) {
        $working_model = $model;
        echo "\n🎉 MODÈLE FONCTIONNEL TROUVÉ: $model\n";
        break;
    }
    echo "\n";
}

if (!$working_model) {
    echo "❌ TOUS LES MODÈLES INDISPONIBLES\n";
    echo "Modèles essayés: " . implode(', ', $models_tried) . "\n";
    echo "→ Utilisation du fallback local\n";
} else {
    echo "✅ SYSTÈME DE FALLBACK FONCTIONNEL\n";
    echo "Modèle utilisé: $working_model\n";
    echo "Modèles essayés: " . implode(', ', $models_tried) . "\n";
}

echo "\n=== CONCLUSION ===\n";
echo "Le système de fallback automatique est maintenant configuré !\n";
echo "Si un modèle a son quota dépassé, le système passe automatiquement au suivant.\n";
echo "Si tous échouent, le fallback local garantit une réponse.\n\n";

echo "Testez maintenant avec facture3.jpg via:\n";
echo "http://127.0.1:8000/accounting/scan\n";
?>
