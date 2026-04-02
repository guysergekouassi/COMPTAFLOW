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
        // Retour sur Vertex AI (Seul API confirmée activée par l'utilisateur)
        $this->projectId = 'scan1-comptaflow';
        $this->location = 'europe-west1'; // Région stable pour l'Europe/Afrique
        $this->apiVersion = 'v1';
        // Utilisation de la version précise pour éviter les erreurs d'alias
        $this->model = 'gemini-1.5-flash-001';
        
        $this->endpoint = "https://{$this->location}-aiplatform.googleapis.com/{$this->apiVersion}/projects/{$this->projectId}/locations/{$this->location}/publishers/google/models/{$this->model}:generateContent";

        // Chargement des credentials
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
                throw new \Exception("Fichier credentials introuvable.");
            }

            $jsonKey = json_decode(file_get_contents($creds), true);
            $scopes = ['https://www.googleapis.com/auth/cloud-platform'];
            
            // On spécifie le Quota Project ID (Crucial pour éviter le 404 dans certains environnements)
            $credentials = new ServiceAccountCredentials($scopes, $jsonKey, null, $this->projectId);
            $token = $credentials->fetchAuthToken();
            
            return $token['access_token'];
        } catch (\Exception $e) {
            Log::error('Vertex AI Auth Error: ' . $e->getMessage());
            throw $e;
        }
    }

    public function analyzeInvoice(string $base64Data, string $mimeType, string $prompt)
    {
        // Format Vertex AI (CamelCase)
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
            
            Log::info("Vertex AI - Appel Prediction API ({$this->location})...");

            $response = Http::withToken($token)
                ->timeout(60)
                ->post($this->endpoint, $payload);

            if ($response->failed()) {
                Log::error('Vertex AI API Error', [
                    'status' => $response->status(),
                    'endpoint' => $this->endpoint,
                    'body' => $response->body()
                ]);
                return ['has_error' => true, 'error_message' => $response->body()];
            }

            $json = $response->json();
            
            if (!isset($json['candidates'][0]['content']['parts'][0]['text'])) {
                Log::error('Vertex AI Invalid Response Structure', ['response' => $json]);
                return ['has_error' => true, 'error_message' => 'Structure de réponse invalide.'];
            }

            $aiText = $json['candidates'][0]['content']['parts'][0]['text'];
            $aiText = preg_replace('/^```json\s*|\s*```$/', '', trim($aiText));
            
            $data = json_decode($aiText, true);

            return ['data' => $data];
        } catch (\Exception $e) {
            Log::error('Vertex AI Exception: ' . $e->getMessage());
            return ['has_error' => true, 'error_message' => $e->getMessage()];
        }
    }
}
