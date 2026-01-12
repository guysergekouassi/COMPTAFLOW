<?php
// Test de l'API Gemini
$api_key = "AIzaSyDuwMm9cdo_vTqBe9j3degykq4rL-kOKVU";
$model = "gemini-flash-latest"; 
$url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$api_key}";

// Test simple
$payload = [
    "contents" => [
        [
            "parts" => [
                ["text" => "Réponds simplement 'API fonctionne'"]
            ]
        ]
    ],
    "generationConfig" => [
        "temperature" => 0.2,
        "maxOutputTokens" => 100
    ]
];

echo "Test de l'API Gemini...\n";

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
$error = curl_error($ch);
curl_close($ch);

echo "HTTP Code: $http_code\n";
echo "Curl Error: " . ($error ?: 'Aucune') . "\n";
echo "Response: " . substr($response, 0, 500) . "...\n";

if ($http_code === 200) {
    echo "\n✅ API Gemini est ACTIVE !\n";
} else {
    echo "\n❌ API Gemini est INACTIVE ou erreur !\n";
}
?>
