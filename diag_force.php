<?php

// Script de diagnostic "FORCE" pour trouver l'endpoint Vertex AI qui répond
require __DIR__ . '/vendor/autoload.php';

use Google\Auth\Credentials\ServiceAccountCredentials;
use Illuminate\Support\Facades\Http;

function diagForce() {
    $projectId = 'scan1-comptaflow';
    $credsPath = __DIR__ . '/credentials.json';
    
    $regions = ['us-central1', 'europe-west1', 'europe-west9', 'europe-west2', 'us-east4'];
    $versions = ['v1', 'v1beta1'];
    $models = ['gemini-1.5-flash', 'gemini-1.5-flash-001', 'gemini-1.0-pro-vision'];

    echo "--- DIAGNOSTIC VERTEX AI FORCE ---\n";
    
    if (!file_exists($credsPath)) {
        echo "ERREUR: credentials.json introuvable.\n";
        return;
    }

    $jsonKey = json_decode(file_get_contents($credsPath), true);
    $scopes = ['https://www.googleapis.com/auth/cloud-platform'];
    $credentials = new ServiceAccountCredentials($scopes, $jsonKey);
    $tokenData = $credentials->fetchAuthToken();
    $token = $tokenData['access_token'];

    echo "Token OK. Test des endpoints...\n\n";

    // 1. Tester la liste des locations autorisées
    echo "[TEST 1] Liste des locations autorisées :\n";
    $locUrl = "https://us-central1-aiplatform.googleapis.com/v1/projects/{$projectId}/locations";
    $res = Http::withToken($token)->withHeaders(['X-Goog-User-Project' => $projectId])->get($locUrl);
    echo "Status: " . $res->status() . "\n";
    if ($res->successful()) {
        $locs = $res->json();
        foreach ($locs['locations'] ?? [] as $l) echo "- " . $l['locationId'] . "\n";
    } else {
        echo "Echec liste locations: " . substr($res->body(), 0, 100) . "\n";
    }
    echo "\n";

    // 2. Lister les modèles disponibles
    echo "[TEST 2] Liste des modèles disponibles (Listing réel) :\n";
    $testRegions = ['us-central1', 'europe-west1'];
    foreach ($testRegions as $reg) {
        echo "Check $reg :\n";
        $listUrl = "https://{$reg}-aiplatform.googleapis.com/v1/projects/{$projectId}/locations/{$reg}/publishers/google/models";
        $res = Http::withToken($token)->withHeaders(['X-Goog-User-Project' => $projectId])->get($listUrl);
        
        echo "  Standard URL Status: " . $res->status() . "\n";
        if ($res->successful()) {
            $data = $res->json();
            foreach ($data['models'] ?? [] as $m) echo "  - " . ($m['name'] ?? 'Inconnu') . "\n";
            if (empty($data['models'])) echo "  - (Liste vide)\n";
        } else {
            // Test sans publishers/google
            $listUrl2 = "https://{$reg}-aiplatform.googleapis.com/v1/projects/{$projectId}/locations/{$reg}/models";
            $res2 = Http::withToken($token)->withHeaders(['X-Goog-User-Project' => $projectId])->get($listUrl2);
            echo "  Direct URL Status: " . $res2->status() . "\n";
            if ($res2->successful()) {
                $data = $res2->json();
                foreach ($data['models'] ?? [] as $m) echo "  - " . ($m['name'] ?? 'Inconnu') . "\n";
                if (empty($data['models'])) echo "  - (Liste vide)\n";
            } else {
                echo "  Erreur Listing: " . substr($res2->body(), 0, 100) . "\n";
            }
        }
    }
}

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

diagForce();
