<?php
// Load Key
$envFile = __DIR__ . '/.env';
$apiKey = '';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        list($name, $value) = explode('=', $line, 2);
        if (trim($name) == 'GEMINI_API_KEY') {
            $apiKey = trim($value);
            $apiKey = trim($apiKey, '"\'');
            break;
        }
    }
}

if (!$apiKey) die("No API Key\n");

echo "Testing Key: " . substr($apiKey, 0, 5) . "...\n";

// Payload
$data = [
    "contents" => [
        [
            "parts" => [
                ["text" => "Hello, are you working? Respond with 'YES'."]
            ]
        ]
    ]
];

$models = ['gemini-2.5-flash', 'gemini-1.5-flash', 'gemini-1.5-flash-latest', 'gemini-1.5-flash-001'];

foreach ($models as $model) {
    echo "\n-------------------------------------------------\n";
    echo "Testing Model: $model\n";
    $url = "https://generativelanguage.googleapis.com/v1beta/models/$model:generateContent?key=$apiKey";
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Fix for local dev
    // curl_setopt($ch, CURLOPT_VERBOSE, true); // Too noisy usually
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    echo "HTTP Code: $httpCode\n";
    if ($error) {
        echo "Curl Error: $error\n";
    }
    if ($httpCode == 200) {
        echo "SUCCESS! Response excerpt: " . substr($response, 0, 100) . "...\n";
        break; // Stop if one works
    } else {
        echo "FAILED. Response: $response\n";
    }
}
?>
