<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class VertexAiService
{
    private string $model;
    private string $endpoint;

    public function __construct()
    {
        // Passage sur l'API Google AI (Generative Language) qui est plus stable sur les régions africaines/européennes
        // Elle accepte les mêmes credentials que Vertex AI
        $this->model = 'gemini-1.5-flash';
        $this->endpoint = "https://generativelanguage.googleapis.com/v1/models/{$this->model}:generateContent";

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
            $scopes = ['https://www.googleapis.com/auth/cloud-platform', 'https://www.googleapis.com/auth/generative-language'];
            
            $credentials = new \Google\Auth\Credentials\ServiceAccountCredentials($scopes, $jsonKey);
            $token = $credentials->fetchAuthToken();
            
            return $token['access_token'];
        } catch (\Exception $e) {
            Log::error('Gemini Auth Error: ' . $e->getMessage());
            throw $e;
        }
    }

    public function analyzeInvoice(string $base64Data, string $mimeType, string $prompt)
    {
        $payload = [
            'contents' => [
                [
                    'parts' => [
                        ['text' => $prompt],
                        [
                            'inline_data' => [
                                'mime_type' => $mimeType,
                                'data' => $base64Data
                            ]
                        ]
                    ]
                ]
            ],
            'generationConfig' => [
                'temperature' => 0.1,
                'response_mime_type' => 'application/json'
            ]
        ];

        return $this->callVertexApi($payload);
    }

    public function callVertexApi(array $payload)
    {
        try {
            $token = $this->getAccessToken();
            
            Log::info("Gemini AI - Envoi de la requête à l'API Standard...");

            $response = Http::withToken($token)
                ->timeout(60)
                ->post($this->endpoint, $payload);

            if ($response->failed()) {
                Log::error('Gemini API Error', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
                return ['has_error' => true, 'error_message' => $response->body()];
            }

            // Transformation du format Gemini vers le format attendu par le contrôleur
            $json = $response->json();
            
            if (!isset($json['candidates'][0]['content']['parts'][0]['text'])) {
                Log::error('Gemini Invalid Response Structure', ['response' => $json]);
                return ['has_error' => true, 'error_message' => 'Structure invalide'];
            }

            $aiText = $json['candidates'][0]['content']['parts'][0]['text'];
            $aiText = preg_replace('/^```json\s*|\s*```$/', '', trim($aiText));
            
            $data = json_decode($aiText, true);

            return ['data' => $data];
        } catch (\Exception $e) {
            Log::error('Gemini Exception: ' . $e->getMessage());
            return ['has_error' => true, 'error_message' => $e->getMessage()];
        }
    }
}
