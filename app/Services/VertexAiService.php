<?php

namespace App\Services;

use Google\Auth\ApplicationDefaultCredentials;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class VertexAiService
{
    private ?string $apiKey;
    private string $projectId;
    private string $location;
    private string $apiVersion;
    private string $model;
    private string $mode;

    public function __construct()
    {
        $this->apiKey = env('GEMINI_API_KEY');
        $this->projectId = env('GOOGLE_CLOUD_PROJECT_ID', 'scan1-comptaflow');
        $this->location = env('GOOGLE_CLOUD_LOCATION', env('VERTEX_AI_LOCATION', 'europe-west2'));
        $this->apiVersion = env('VERTEX_AI_API_VERSION', 'v1');
        $this->model = env('VERTEX_AI_MODEL', 'gemini-1.5-flash'); 

        // Mode de fonctionnement (vertex_ai ou gemini)
        $this->mode = env('COMPTAFLOW_IA_MODE', 'gemini');

        // Si on est en mode Vertex, on ignore la clé API Gemini
        if ($this->mode === 'vertex_ai') {
            $this->apiKey = null;
        }

        if (!$this->apiKey) {
            $creds = base_path('credentials.json');
            if (file_exists($creds)) {
                putenv("GOOGLE_APPLICATION_CREDENTIALS=$creds");
            }
        }
    }

    private function getAccessToken(): ?string
    {
        // En mode Gemini API (Method A), pas besoin de token OAuth
        if ($this->apiKey) return null;

        try {
            $credsPath = env('GOOGLE_APPLICATION_CREDENTIALS');
            
            if ($credsPath && file_exists($credsPath)) {
                // Si un chemin est spécifié dans le .env, on l'utilise pour putenv
                // Cela aide google-auth à trouver le fichier dans certains environnements
                putenv("GOOGLE_APPLICATION_CREDENTIALS=$credsPath");
            }

            // Utilise les ADC (Application Default Credentials)
            $credentials = ApplicationDefaultCredentials::getCredentials();
            
            // On rafraîchit le token
            $token = $credentials->fetchAuthToken();
            
            return $token['access_token'] ?? null;
        } catch (\Exception $e) {
            Log::error('Vertex AI Auth Error (ADC): ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Analyse une facture avec une seule image (Compatibilité ascendante)
     */
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
            ]
        ];

        return $this->callVertexApi($payload);
    }

    /**
     * Méthode générique pour appeler Gemini (v1 ou Vertex)
     */
    public function generateContent(array $contents, array $generationConfig = [])
    {
        $payload = [
            'contents' => $contents,
            'generationConfig' => array_merge([
                'temperature' => 0.1,
                'maxOutputTokens' => 2048,
            ], $generationConfig),
        ];

        return $this->callVertexApi($payload);
    }

    public function callVertexApi(array $payload)
    {
        $maxRetries = 3;
        $attempt = 0;

        while ($attempt < $maxRetries) {
            try {
                $isToken = $this->apiKey && str_starts_with($this->apiKey, 'AQ.');
                $isApiKey = $this->apiKey && !$isToken;

                // MÉTHODE A : Clé API Standard (AI Studio)
                if ($isApiKey) {
                    $url = "https://generativelanguage.googleapis.com/v1beta/models/{$this->model}:generateContent?key={$this->apiKey}";
                    $p = $payload;
                    foreach ($p['contents'] as &$content) {
                        foreach ($content['parts'] as &$part) {
                            if (isset($part['inlineData'])) {
                                $part['inline_data'] = [
                                    'mime_type' => $part['inlineData']['mimeType'],
                                    'data' => $part['inlineData']['data']
                                ];
                                unset($part['inlineData']);
                            }
                        }
                    }
                    if (isset($p['generationConfig'])) {
                        $configMapping = ['maxOutputTokens' => 'max_output_tokens', 'topP' => 'top_p', 'topK' => 'top_k'];
                        foreach ($configMapping as $old => $new) {
                            if (isset($p['generationConfig'][$old])) {
                                $p['generationConfig'][$new] = $p['generationConfig'][$old];
                                unset($p['generationConfig'][$old]);
                            }
                        }
                    }
                    $response = Http::timeout(300)->post($url, $p);
                } 
                // MÉTHODE B : Pro Tunnel (Vertex AI)
                else {
                    $token = $this->apiKey ?: $this->getAccessToken();
                    $url = "https://{$this->location}-aiplatform.googleapis.com/{$this->apiVersion}/projects/{$this->projectId}/locations/{$this->location}/publishers/google/models/{$this->model}:generateContent";
                    
                    // Vertex AI impose la présence du champ 'role' (user/model)
                    $p = $payload;
                    foreach ($p['contents'] as &$content) {
                        if (!isset($content['role'])) {
                            $content['role'] = 'user';
                        }
                    }

                    $response = Http::withToken($token)->withHeaders(['X-Goog-User-Project' => $this->projectId])->timeout(300)->post($url, $p);
                }

                if ($response->failed()) {
                    // Retry on 503 (Overloaded) or 429 (Quota)
                    if (in_array($response->status(), [503, 429]) && $attempt < $maxRetries - 1) {
                        $attempt++;
                        $delay = pow(2, $attempt);
                        Log::warning("IA API Retry ({$response->status()}) after {$delay}s...");
                        sleep($delay);
                        continue;
                    }
                    Log::error('IA API Error', ['status' => $response->status(), 'msg' => $response->body()]);
                    return ['has_error' => true, 'error_message' => $response->body(), 'http_code' => $response->status()];
                }

                $json = $response->json();
                $rawText = $json['candidates'][0]['content']['parts'][0]['text'] ?? '';
                $aiText = preg_replace('/^```json\s*|\s*```$/', '', trim($rawText));
                $jsonData = json_decode($aiText, true);

                return [
                    'has_error' => false, 
                    'data' => $jsonData ?: $rawText,
                    'raw_text' => $rawText
                ];

            } catch (\Exception $e) {
                if ($attempt < $maxRetries - 1) {
                    $attempt++;
                    sleep(2);
                    continue;
                }
                Log::error('IA API Exception: ' . $e->getMessage());
                return ['has_error' => true, 'error_message' => $e->getMessage()];
            }
        }
    }

    /**
     * Test simple pour vérifier la connectivité Vertex AI Pro
     */
    public static function testConnection(): array
    {
        try {
            $service = new self();
            
            $testPayload = [
                'contents' => [['parts' => [['text' => 'Réponds UNIQUEMENT avec : {"status":"ok"}']]]],
                'generationConfig' => ['maxOutputTokens' => 50, 'temperature' => 0.1]
            ];

            return $service->callVertexApi($testPayload);
        } catch (\Exception $e) {
            return ['has_error' => true, 'error_message' => $e->getMessage()];
        }
    }
}
