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
        
        // us-central1 est la région la plus stable pour Gemini 1.5 Flash
        $this->location = 'us-central1';
        $this->apiVersion = 'v1';
        // Utilisation de la version précise pour éviter le 404
        $this->model = 'gemini-1.5-flash-001';
        
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

    /**
     * Analyse la facture et renvoie un tableau formatté pour IaController
     */
    public function analyzeInvoice(string $base64Data, string $mimeType, string $prompt)
    {
        $payload = [
            'contents' => [
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
            ],
            'generationConfig' => [
                'temperature' => 0.1,
                'topP' => 0.95,
                'maxOutputTokens' => 2048,
                'responseMimeType' => 'application/json'
            ]
        ];

        $response = $this->callVertexApi($payload);

        if (isset($response['has_error']) && $response['has_error']) {
            return ['error' => $response['error_message'], 'http_code' => 500];
        }

        // Extraction du texte JSON de la réponse Gemini
        if (!isset($response['candidates'][0]['content']['parts'][0]['text'])) {
            Log::error('Vertex AI Invalid Response Structure', ['response' => $response]);
            return ['error' => 'Structure de réponse IA invalide', 'http_code' => 500];
        }

        $aiText = $response['candidates'][0]['content']['parts'][0]['text'];
        
        // Nettoyage si l'IA a mis des backticks markdown
        $aiText = preg_replace('/^```json\s*|\s*```$/', '', trim($aiText));
        
        $data = json_decode($aiText, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            Log::error('Vertex AI JSON Parse Error', ['text' => $aiText]);
            return ['error' => 'Erreur de lecture du JSON de l\'IA', 'http_code' => 500];
        }

        // On renvoie le format attendu par IaController
        return ['data' => $data];
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
