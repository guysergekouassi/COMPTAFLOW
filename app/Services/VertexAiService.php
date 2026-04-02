<?php

namespace App\Services;

use Google\Auth\Credentials\ServiceAccountCredentials;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class VertexAiService
{
    private string $projectId;
    private string $location;
    private string $apiVersion;
    private string $model;
    private string $endpoint;

    public function __construct()
    {
        // Détection dynamique des réglages via .env ou valeurs par défaut pour LWS
        $this->projectId = env('GOOGLE_CLOUD_PROJECT_ID', 'scan1-comptaflow');
        $this->location = env('GOOGLE_CLOUD_LOCATION', 'us-central1');
        $this->apiVersion = 'v1';
        $this->model = 'gemini-1.5-flash';
        
        // Construction de l'URL standard Vertex AI
        $this->endpoint = "https://{$this->location}-aiplatform.googleapis.com/{$this->apiVersion}/projects/{$this->projectId}/locations/{$this->location}/publishers/google/models/{$this->model}:generateContent";

        // Détection du chemin des credentials (Local ou Production)
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
                throw new \Exception("Fichier credentials introuvable à la racine du projet.");
            }

            $jsonKey = json_decode(file_get_contents($creds), true);
            $scopes = ['https://www.googleapis.com/auth/cloud-platform'];
            
            $credentials = new ServiceAccountCredentials($scopes, $jsonKey);
            $tokenData = $credentials->fetchAuthToken();
            
            if (!isset($tokenData['access_token'])) {
                throw new \Exception("Impossible d'obtenir le token d'accès Google.");
            }

            return $tokenData['access_token'];
        } catch (\Exception $e) {
            Log::error('Vertex AI Auth Error: ' . $e->getMessage());
            throw $e;
        }
    }

    public function analyzeInvoice(string $base64Data, string $mimeType, string $prompt)
    {
        $payload = [
            'contents' => [
                [
                    'role' => 'user',
                    'parts' => [
                        ['text' => $prompt],
                        [
                            'inlineData' => [
                                'mimeType' => $mimeType,
                                'data' => $base64Data
                            ]
                        ]
                    ]
                ]
            ],
            'generationConfig' => [
                'temperature' => 0.1,
            ]
        ];

        return $this->callVertexApi($payload);
    }

    public function callVertexApi(array $payload)
    {
        try {
            $token = $this->getAccessToken();
            
            Log::info("Vertex AI Request [{$this->projectId} / {$this->location}]");

            $response = Http::withToken($token)
                ->withHeaders(['X-Goog-User-Project' => $projectId ?? $this->projectId])
                ->timeout(60)
                ->post($this->endpoint, $payload);

            if ($response->failed()) {
                Log::error('Vertex AI API Error', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
                return ['has_error' => true, 'error_message' => $response->body()];
            }

            $json = $response->json();
            
            if (!isset($json['candidates'][0]['content']['parts'][0]['text'])) {
                return ['has_error' => true, 'error_message' => 'Structure de réponse invalide.'];
            }

            $aiText = $json['candidates'][0]['content']['parts'][0]['text'];
            $aiText = preg_replace('/^```json\s*|\s*```$/', '', trim($aiText));
            
            return ['data' => json_decode($aiText, true)];
        } catch (\Exception $e) {
            Log::error('Vertex AI Exception: ' . $e->getMessage());
            return ['has_error' => true, 'error_message' => $e->getMessage()];
        }
    }
}
