<?php

namespace App\Services;

use Google\Auth\ApplicationDefaultCredentials;
use Google\Auth\CredentialsLoader;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class VertexAiService
{
    private string $projectId;
    private string $projectNumber;
    private string $location;
    private string $apiVersion;
    private string $model;
    private string $endpoint;

    public function __construct()
    {
        // On utilise l'ID du projet scan1-comptaflow
        $this->projectId = 'scan1-comptaflow';
        $this->projectNumber = '288805151479';
        
        // On bascule sur us-central1 pour garantir la disponibilité de Gemini 1.5 Flash
        $this->location = 'us-central1';
        $this->apiVersion = 'v1';
        $this->model = 'gemini-1.5-flash';
        
        $this->endpoint = "https://{$this->location}-aiplatform.googleapis.com/{$this->apiVersion}/projects/{$this->projectNumber}/locations/{$this->location}/publishers/google/models/{$this->model}:generateContent";

        // Chargement automatique des credentials
        $creds = base_path('credentials.json');
        if (file_exists($creds)) {
            putenv("GOOGLE_APPLICATION_CREDENTIALS=$creds");
        }
    }

    private function getAccessToken(): string
    {
        try {
            $creds = getenv('GOOGLE_APPLICATION_CREDENTIALS');
            if (!$creds || !file_exists($creds)) {
                throw new \Exception("Fichier credentials introuvable à la racine.");
            }

            $jsonKey = json_decode(file_get_contents($creds), true);
            $scopes = ['https://www.googleapis.com/auth/cloud-platform'];
            
            $credentials = new \Google\Auth\Credentials\ServiceAccountCredentials($scopes, $jsonKey);
            $token = $credentials->fetchAuthToken();
            
            return $token['access_token'];
        } catch (\Exception $e) {
            Log::error('Vertex AI Auth Error: ' . $e->getMessage());
            throw $e;
        }
    }

    public function callVertexApi(array $payload)
    {
        try {
            $token = $this->getAccessToken();
            
            Log::info("Vertex AI - Envoi de la requête à {$this->location}...");

            $response = Http::withToken($token)
                ->timeout(60)
                ->withHeaders(['Content-Type' => 'application/json'])
                ->post($this->endpoint, $payload);

            if ($response->failed()) {
                Log::error('Vertex AI API Error', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
                return ['has_error' => true, 'error_message' => $response->body()];
            }

            return $response->json();
        } catch (\Exception $e) {
            Log::error('Vertex AI cURL Error: ' . $e->getMessage());
            return ['has_error' => true, 'error_message' => $e->getMessage()];
        }
    }
}
