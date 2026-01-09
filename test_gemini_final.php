<?php
// Test simple de l'API Gemini avec le bon modèle
$api_key = "AIzaSyDuwMm9cdo_vTqBe9j3degykq4rL-kOKVU";
$model = "gemini-1.5-flash"; 
$url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$api_key}";

echo "Test de connexion à l'API Gemini avec modèle: $model\n";

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

// Utilisation de file_get_contents
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

echo "Réponse brute: " . $response . "\n";

if ($response !== false) {
    $result = json_decode($response, true);
    if (isset($result['candidates'][0]['content']['parts'][0]['text'])) {
        echo "✅ Succès: " . $result['candidates'][0]['content']['parts'][0]['text'] . "\n";
    } else {
        echo "❌ Réponse inattendue\n";
        print_r($result);
    }
} else {
    echo "❌ Erreur de connexion\n";
}
?>
