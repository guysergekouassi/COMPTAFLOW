<?php

namespace App\Services;

use Google\Auth\Credentials\ServiceAccountCredentials;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class VertexAiService
{
    private ?string $apiKey;
    private string $projectId;
    private string $location;
    private string $apiVersion;
    private string $model;

    public function __construct()
    {
        $this->apiKey = env('GEMINI_API_KEY');
        $this->projectId = env('GOOGLE_CLOUD_PROJECT_ID', 'scan1-comptaflow');
        $this->location = env('GOOGLE_CLOUD_LOCATION', 'us-central1');
        $this->apiVersion = 'v1'; // On reste sur v1 stable
        $this->model = 'gemini-1.5-flash';

        if (!$this->apiKey) {
            $creds = base_path('credentials.json');
            if (file_exists($creds)) {
                putenv("GOOGLE_APPLICATION_CREDENTIALS=$creds");
            }
        }
    }

    private function getAccessToken(): ?string
    {
        if ($this->apiKey) return null;

        try {
            $creds = getenv('GOOGLE_APPLICATION_CREDENTIALS');
            if (!$creds || !file_exists($creds)) return null;

            $jsonKey = json_decode(file_get_contents($creds), true);
            $scopes = ['https://www.googleapis.com/auth/cloud-platform'];
            $credentials = new ServiceAccountCredentials($scopes, $jsonKey);
            $tokenData = $credentials->fetchAuthToken();
            
            return $tokenData['access_token'] ?? null;
        } catch (\Exception $e) {
            Log::error('Vertex AI Auth Error: ' . $e->getMessage());
            return null;
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
                            'inlineData' => [
                                'mimeType' => $mimeType,
                                'data' => $base64Data
                            ]
                        ]
                    ]
                ]
            ],
            'generationConfig' => [
                'temperature' => 0.1
                // On retire response_mime_type qui cause des erreurs 400 sur certaines versions d'API
            ]
        ];

        return $this->callVertexApi($payload);
    }

    public function callVertexApi(array $payload)
    {
        try {
            $isToken = $this->apiKey && str_starts_with($this->apiKey, 'AQ.');
            $isApiKey = $this->apiKey && !$isToken;

            // MÉTHODE A : Clé API Standard (AI Studio)
            if ($isApiKey) {
                Log::info("IA Request via Standard API KEY (v1)");
                $url = "https://generativelanguage.googleapis.com/v1/models/{$this->model}:generateContent?key={$this->apiKey}";
                
                // Format snake_case pour API Studio
                $p = $payload;
                $p['contents'][0]['parts'][1] = [
                    'inline_data' => [
                        'mime_type' => $payload['contents'][0]['parts'][1]['inlineData']['mimeType'],
                        'data' => $payload['contents'][0]['parts'][1]['inlineData']['data']
                    ]
                ];
                
                $response = Http::timeout(300)->post($url, $p);
            } 
            // MÉTHODE B : Token OAuth (AQ...) ou Service Account -> Tunnel Vertex AI (Pro)
            else {
                $token = $this->apiKey ?: $this->getAccessToken();
                if (!$token) throw new \Exception("Aucune méthode d'authentification disponible.");

                Log::info("IA Request via Pro Tunnel (Vertex AI) - [{$this->projectId}]");
                $url = "https://{$this->location}-aiplatform.googleapis.com/{$this->apiVersion}/projects/{$this->projectId}/locations/{$this->location}/publishers/google/models/{$this->model}:generateContent";
                
                $response = Http::withToken($token)
                    ->withHeaders(['X-Goog-User-Project' => $this->projectId])
                    ->timeout(300)
                    ->post($url, $payload);
            }

            if ($response->failed()) {
                Log::error('IA Scan API Error', ['status' => $response->status(), 'msg' => $response->body()]);
                return ['has_error' => true, 'error_message' => $response->body(), 'http_code' => $response->status()];
            }

            $json = $response->json();
            $aiText = $json['candidates'][0]['content']['parts'][0]['text'] ?? '';
            $aiText = preg_replace('/^```json\s*|\s*```$/', '', trim($aiText));
            
            return ['has_error' => false, 'data' => json_decode($aiText, true)];

        } catch (\Exception $e) {
            Log::error('IA Scan Exception: ' . $e->getMessage());
            return ['has_error' => true, 'error_message' => $e->getMessage()];
        }
    }
}
