<?php
// Test avec le modèle correct
$api_key = "AIzaSyDuwMm9cdo_vTqBe9j3degykq4rL-kOKVU";
$model = "gemini-1.5-flash"; 
$url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$api_key}";

echo "Test avec modèle: $model\n";

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

$data = json_encode($payload);
$options = [
    'http' => [
        'header' => "Content-Type: application/json\r\n",
        'method' => 'POST',
        'content' => $data,
        'timeout' => 30
    ]
];

$context = stream_context_create($options);
$response = file_get_contents($url, false, $context);

if ($response !== false) {
    $result = json_decode($response, true);
    if (isset($result['candidates'][0]['content']['parts'][0]['text'])) {
        echo "✅ Succès: " . $result['candidates'][0]['content']['parts'][0]['text'] . "\n";
    } else {
        echo "❌ Réponse inattendue\n";
        echo "Erreur: " . json_last_error_msg() . "\n";
        if (isset($result['error'])) {
            echo "Détail erreur: " . json_encode($result['error']) . "\n";
        }
    }
} else {
    echo "❌ Erreur de connexion\n";
    echo "HTTP Error: " . $http_response_header[0] ?? "Unknown" . "\n";
}
?>
