<?php
$envFile = __DIR__ . '/.env';
$apiKey = '';

echo "Checking .env file at: $envFile\n";
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        if (strpos($line, '=') === false) continue;
        list($name, $value) = explode('=', $line, 2);
        if (trim($name) == 'GEMINI_API_KEY') {
            $apiKey = trim($value);
            echo "Found API Key (length: " . strlen($apiKey) . ")\n";
            // Check if key starts with quote and remove it
            if (substr($apiKey, 0, 1) === '"') {
                $apiKey = trim($apiKey, '"');
            }
             if (substr($apiKey, 0, 1) === "'") {
                $apiKey = trim($apiKey, "'");
            }
            break;
        }
    }
}

if (!$apiKey) {
    die("API Key NOT found in .env\n");
}

$url = "https://generativelanguage.googleapis.com/v1beta/models?key=$apiKey";
echo "Requesting URL: https://generativelanguage.googleapis.com/v1beta/models?key=HIDDEN\n";

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // For dev env issues
curl_setopt($ch, CURLOPT_VERBOSE, true);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

if ($response === false) {
    echo "Curl Error: " . curl_error($ch) . "\n";
} else {
    echo "HTTP Status: $httpCode\n";
    echo "Response: " . substr($response, 0, 1000) . "\n"; // Print first 1000 chars
}
curl_close($ch);
?>
