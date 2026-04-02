<?php

// Script de diagnostic pour lister les modèles Vertex AI disponibles
require __DIR__ . '/vendor/autoload.php';

use Google\Auth\Credentials\ServiceAccountCredentials;
use Illuminate\Support\Facades\Http;

function diagVertex() {
    $projectId = 'scan1-comptaflow';
    $location = 'us-central1';
    $credsPath = __DIR__ . '/credentials.json';

    echo "--- DIAGNOSTIC VERTEX AI ---\n";
    echo "Projet: $projectId\n";
    echo "Région: $location\n";
    echo "Fichier de clé: " . (file_exists($credsPath) ? "OK" : "MANQUANT") . "\n";

    if (!file_exists($credsPath)) return;

    try {
        $jsonKey = json_decode(file_get_contents($credsPath), true);
        $scopes = ['https://www.googleapis.com/auth/cloud-platform'];
        $credentials = new ServiceAccountCredentials($scopes, $jsonKey);
        $tokenData = $credentials->fetchAuthToken();
        $token = $tokenData['access_token'];

        echo "Token obtenu: " . substr($token, 0, 20) . "...\n\n";

        // Lister les modèles disponibles pour l'éditeur 'google' dans cette région
        // Endpoint: https://{location}-aiplatform.googleapis.com/v1/projects/{project}/locations/{location}/publishers/google/models
        $url = "https://{$location}-aiplatform.googleapis.com/v1/projects/{$projectId}/locations/{$location}/publishers/google/models";

        echo "Appel de l'API Model Garden...\n";
        $response = \Illuminate\Support\Facades\Http::withToken($token)->get($url);

        if ($response->failed()) {
            echo "ERREUR API Global:\n";
            echo $response->body() . "\n";
            return;
        }

        $data = $response->json();
        echo "Modèles trouvés (" . count($data['models'] ?? []) . "):\n";
        
        if (isset($data['models'])) {
            foreach ($data['models'] as $m) {
                // On affiche le nom court pour aider l'utilisateur
                $name = $m['name'];
                echo "- $name\n";
            }
        } else {
            echo "Aucun modèle listé.\n";
            print_r($data);
        }

    } catch (\Exception $e) {
        echo "EXCEPTION: " . $e->getMessage() . "\n";
    }
}

// Pour faire tourner ça en standalone dans Laravel context
// On charge l'app
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

diagVertex();
