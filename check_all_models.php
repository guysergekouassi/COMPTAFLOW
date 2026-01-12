<?php
// Vérification des modèles disponibles et quotas Gemini

echo "=== VÉRIFICATION COMPLÈTE DES MODÈLES GEMINI ===\n\n";

$api_key = "AIzaSyDuwMm9cdo_vTqBe9j3degykq4rL-kOKVU";

// Liste de tous les modèles Gemini possibles
$all_models = [
    "gemini-flash-latest",
    "gemini-2.5-flash", 
    "gemini-2.0-flash",
    "gemini-1.5-flash",
    "gemini-1.5-pro",
    "gemini-pro",
    "gemini-1.0-pro",
    "gemini-1.0-flash"
];

echo "Test de tous les modèles Gemini disponibles:\n";
echo "==========================================\n\n";

$working_models = [];
$quota_exceeded = [];
$not_found = [];
$errors = [];

foreach ($all_models as $model) {
    $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$api_key}";
    
    // Payload très simple pour tester
    $payload = [
        "contents" => [
            [
                "parts" => [
                    ["text" => "Test"]
                ]
            ]
        ],
        "generationConfig" => [
            "temperature" => 0.1,
            "maxOutputTokens" => 5
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
    
    if ($http_code === 200) {
        echo "✅ DISPONIBLE\n";
        $working_models[] = $model;
    } elseif ($http_code == 429) {
        echo "❌ QUOTA DÉPASSÉ\n";
        $quota_exceeded[] = $model;
        
        // Analyser la réponse pour voir les détails du quota
        $result = json_decode($response, true);
        if (isset($result['error']['details'])) {
            foreach ($result['error']['details'] as $detail) {
                if (isset($detail['violations'])) {
                    foreach ($detail['violations'] as $violation) {
                        echo "   → Limite: " . ($violation['quotaValue'] ?? 'N/A') . "\n";
                        echo "   → Métrique: " . ($violation['quotaMetric'] ?? 'N/A') . "\n";
                    }
                }
                if (isset($detail['retryDelay'])) {
                    echo "   → Retry: " . $detail['retryDelay'] . "\n";
                }
            }
        }
    } elseif ($http_code == 404) {
        echo "❌ MODÈLE NON TROUVÉ\n";
        $not_found[] = $model;
    } else {
        echo "❌ ERREUR: $error\n";
        $errors[] = $model;
    }
    echo "\n";
}

echo "=== RÉSUMÉ ===\n";
echo "Modèles fonctionnels: " . count($working_models) . "\n";
if (!empty($working_models)) {
    echo "→ " . implode(', ', $working_models) . "\n";
}
echo "\n";

echo "Modèles quota dépassé: " . count($quota_exceeded) . "\n";
if (!empty($quota_exceeded)) {
    echo "→ " . implode(', ', $quota_exceeded) . "\n";
}
echo "\n";

echo "Modèles non trouvés: " . count($not_found) . "\n";
if (!empty($not_found)) {
    echo "→ " . implode(', ', $not_found) . "\n";
}
echo "\n";

echo "Autres erreurs: " . count($errors) . "\n";
if (!empty($errors)) {
    echo "→ " . implode(', ', $errors) . "\n";
}
echo "\n";

echo "=== RECOMMANDATIONS ===\n";
if (!empty($working_models)) {
    echo "✅ Utilisez ces modèles dans votre système de fallback:\n";
    echo "   " . implode(', ', $working_models) . "\n";
} else {
    echo "❌ Aucun modèle disponible actuellement\n";
    echo "   → Vérifiez votre clé API\n";
    echo "   → Attendez le reset des quotas\n";
    echo "   → Utilisez le fallback local\n";
}
?>
