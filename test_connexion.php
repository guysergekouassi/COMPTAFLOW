<?php
// Test de connexion à l'API Gemini
echo "=== TEST CONNEXION RÉSEAU ===\n";

// Test DNS
$domain = "generativelanguage.googleapis.com";
$ip = gethostbyname($domain);
echo "DNS: $domain → $ip\n";

if ($ip === $domain) {
    echo "❌ Erreur DNS: Impossible de résoudre $domain\n";
} else {
    echo "✅ DNS OK\n";
}

// Test ping simple
$timeout = 5;
$socket = @fsockopen($domain, 443, $errno, $errstr, $timeout);
if ($socket) {
    echo "✅ Connexion TCP OK (port 443)\n";
    fclose($socket);
} else {
    echo "❌ Erreur connexion TCP: $errno - $errstr\n";
}

// Test HTTP simple
$context = stream_context_create([
    'http' => [
        'timeout' => 10,
        'method' => 'GET'
    ],
    'ssl' => [
        'verify_peer' => false,
        'verify_peer_name' => false
    ]
]);

$url = "https://generativelanguage.googleapis.com";
$start = microtime(true);
$response = @file_get_contents($url, false, $context);
$end = microtime(true);

if ($response !== false) {
    echo "✅ HTTP OK (" . round(($end - $start) * 1000) . "ms)\n";
} else {
    echo "❌ Erreur HTTP\n";
    $error = error_get_last();
    if ($error) {
        echo "Détail: " . $error['message'] . "\n";
    }
}

// Test API simple
$api_key = "AIzaSyDuwMm9cdo_vTqBe9j3degykq4rL-kOKVU";
$model = "gemini-flash-latest";
$api_url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$api_key}";

$payload = [
    "contents" => [
        [
            "parts" => [
                ["text" => "Test simple. Réponds 'OK'."]
            ]
        ]
    ]
];

$context = stream_context_create([
    'http' => [
        'timeout' => 15,
        'method' => 'POST',
        'header' => 'Content-Type: application/json',
        'content' => json_encode($payload)
    ],
    'ssl' => [
        'verify_peer' => false,
        'verify_peer_name' => false
    ]
]);

echo "\n=== TEST API ===\n";
$start = microtime(true);
$response = @file_get_contents($api_url, false, $context);
$end = microtime(true);

if ($response !== false) {
    echo "✅ API OK (" . round(($end - $start) * 1000) . "ms)\n";
    $result = json_decode($response, true);
    if (isset($result['candidates'][0]['content']['parts'][0]['text'])) {
        echo "Réponse: " . $result['candidates'][0]['content']['parts'][0]['text'] . "\n";
    }
} else {
    echo "❌ Erreur API\n";
    $error = error_get_last();
    if ($error) {
        echo "Détail: " . $error['message'] . "\n";
    }
}
?>
