<?php
// Test direct de l'API Gemini
$api_key = "AIzaSyDuwMm9cdo_vTqBe9j3degykq4rL-kOKVU";
$model = "gemini-1.5-flash"; 
$url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$api_key}";

// Test simple sans image
$payload = [
    "contents" => [
        [
            "parts" => [
                ["text" => "Test simple. Réponds 'OK' si tu fonctionnes."]
            ]
        ]
    ],
    "generationConfig" => [
        "temperature" => 0.2,
        "maxOutputTokens" => 50
    ]
];

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "=== TEST API GEMINI ===\n";
echo "HTTP Code: $http_code\n";
echo "Response: " . $response . "\n";

if ($http_code === 200) {
    $result = json_decode($response, true);
    if (isset($result['candidates'][0]['content']['parts'][0]['text'])) {
        echo "✅ Succès: " . $result['candidates'][0]['content']['parts'][0]['text'] . "\n";
    } else {
        echo "❌ Réponse inattendue\n";
    }
} else {
    echo "❌ Erreur API\n";
}
?>
