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
    echo "[TEST 2] Recherche d'un modèle (Brute-force) :\n";
    foreach ($regions as $reg) {
        foreach ($versions as $ver) {
            foreach ($models as $mod) {
                $testUrl = "https://{$reg}-aiplatform.googleapis.com/{$ver}/projects/{$projectId}/locations/{$reg}/publishers/google/models/{$mod}:generateContent";
                
                // On fait un petit test (requête vide ou invalide juste pour voir le status)
                $res = Http::withToken($token)
                    ->withHeaders(['X-Goog-User-Project' => $projectId])
                    ->timeout(5)
                    ->post($testUrl, ['contents' => [['parts' => [['text' => 'test']]]]]);

                $status = $res->status();
                echo sprintf("Region: %-13s | Ver: %-7s | Model: %-18s | Status: %d\n", $reg, $ver, $mod, $status);

                // Si on a un 200 (Succès) ou un 400 (Bad Request - car payload incomplet), c'est gagné !
                // Un 404 signifie que l'endpoint n'existe pas.
                if ($status !== 404 && $status !== 403) {
                    echo ">>> TROUVÉ ! Cet endpoint répond. <<<\n";
                    echo "URL: $testUrl\n\n";
                }
            }
        }
    }
}

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

diagForce();
