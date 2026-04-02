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

    // 2. Brute-force des combinaisons
    echo "[TEST 2] Recherche d'un modèle (Brute-force augmenté) :\n";
    foreach ($regions as $reg) {
        foreach ($versions as $ver) {
            foreach ($models as $mod) {
                // Variante 1: Standard (publishers/google/models)
                $url1 = "https://{$reg}-aiplatform.googleapis.com/{$ver}/projects/{$projectId}/locations/{$reg}/publishers/google/models/{$mod}:generateContent";
                
                // Variante 2: Direct (locations/models - fréquent sur v1beta1)
                $url2 = "https://{$reg}-aiplatform.googleapis.com/{$ver}/projects/{$projectId}/locations/{$reg}/models/{$mod}:generateContent";
                
                // Variante 3: GenerativeModels (Nouveau format)
                $url3 = "https://{$reg}-aiplatform.googleapis.com/{$ver}/projects/{$projectId}/locations/{$reg}/generativeModels/{$mod}:generateContent";

                $urls = [
                    'Standard' => $url1,
                    'Direct'   => $url2,
                    'GenModel' => $url3
                ];

                foreach ($urls as $label => $u) {
                    $res = Http::withToken($token)
                        ->withHeaders(['X-Goog-User-Project' => $projectId])
                        ->timeout(3)
                        ->post($u, ['contents' => [['parts' => [['text' => 'test']]]]]);

                    $status = $res->status();
                    if ($status !== 404) {
                        echo sprintf("Region: %-13s | Ver: %-7s | Format: %-9s | Model: %-18s | Status: %d\n", $reg, $ver, $label, $mod, $status);
                        if ($status === 200 || $status === 400) {
                            echo ">>> TROUVÉ ! Cet endpoint répond. <<<\n";
                            echo "URL: $u\n\n";
                            return; // On s'arrête dès qu'on en a un qui marche
                        }
                    }
                }
            }
        }
        echo "Check $reg fini...\n";
    }
}

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

diagForce();
