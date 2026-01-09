<?php
// Test avec image factice
$api_key = "AIzaSyDuwMm9cdo_vTqBe9j3degykq4rL-kOKVU";
$model = "gemini-flash-latest";
$url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$api_key}";

// Créer une petite image de test (1x1 pixel PNG)
$image_data = "iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNkYPhfDwAChwGA60e6kgAAAABJRU5ErkJggg==";
$mime_type = "image/png";

$prompt = "Tu es un expert-comptable SYSCOHADA CI. Analyse cette image de test et retourne un JSON avec les informations d'une facture simple.";

$payload = [
    "contents" => [
        [
            "parts" => [
                ["text" => $prompt],
                [
                    "inline_data" => [
                        "mime_type" => $mime_type,
                        "data" => $image_data
                    ]
                ]
            ]
        ]
    ],
    "generationConfig" => [
        "temperature" => 0.2,
        "maxOutputTokens" => 500,
        "response_mime_type" => "application/json"
    ]
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "=== TEST AVEC IMAGE ===\n";
echo "HTTP Code: $http_code\n";

if ($http_code === 200) {
    $result = json_decode($response, true);
    if (isset($result['candidates'][0]['content']['parts'][0]['text'])) {
        $json_response = $result['candidates'][0]['content']['parts'][0]['text'];
        echo "✅ SUCCÈS - Réponse IA:\n";
        echo $json_response . "\n";
        
        // Valider le JSON
        $data = json_decode($json_response, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            echo "✅ JSON valide\n";
        } else {
            echo "❌ JSON invalide: " . json_last_error_msg() . "\n";
        }
    } else {
        echo "❌ Format de réponse inattendu\n";
    }
} else {
    echo "❌ Erreur HTTP $http_code\n";
    echo "Response: " . $response . "\n";
}
?>
