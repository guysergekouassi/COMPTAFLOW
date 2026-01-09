<?php
// Test API Gemini avec gemini-flash-latest
$api_key = "AIzaSyDuwMm9cdo_vTqBe9j3degykq4rL-kOKVU";
$model = "gemini-flash-latest";
$url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$api_key}";

echo "=== TEST API GEMINI ===\n";
echo "Modèle: $model\n";
echo "URL: $url\n\n";

$payload = [
    "contents" => [
        [
            "parts" => [
                ["text" => "Dis simplement 'OK' pour tester."]
            ]
        ]
    ],
    "generationConfig" => [
        "temperature" => 0.2,
        "maxOutputTokens" => 10
    ]
];

$data_string = json_encode($payload);
echo "Payload: " . $data_string . "\n\n";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

echo "HTTP Code: $http_code\n";
if ($error) {
    echo "cURL Error: $error\n";
}
echo "Response: " . $response . "\n";

if ($http_code === 200) {
    $result = json_decode($response, true);
    if (isset($result['candidates'][0]['content']['parts'][0]['text'])) {
        echo "✅ SUCCÈS: " . $result['candidates'][0]['content']['parts'][0]['text'] . "\n";
    } else {
        echo "❌ Format de réponse inattendu\n";
    }
} else {
    echo "❌ Erreur HTTP $http_code\n";
}
?>
