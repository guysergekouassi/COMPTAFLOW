<?php
require 'vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$api_key = env('GEMINI_API_KEY');
if (!$api_key) {
    die("No API key found\n");
}

$url = "https://generativelanguage.googleapis.com/v1beta/models?key={$api_key}";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$response = curl_exec($ch);
curl_close($ch);

$data = json_decode($response, true);
if (isset($data['models'])) {
    foreach ($data['models'] as $m) {
        if (strpos($m['name'], 'flash') !== false) {
            echo "Found model: " . $m['name'] . "\n";
        }
    }
} else {
    echo "Error or no models:\n";
    print_r($data);
}
