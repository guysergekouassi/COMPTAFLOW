<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Google\Auth\ApplicationDefaultCredentials;
use Google\Auth\CredentialsLoader;

/**
 * VertexAiService
 *
 * Remplace l'API Gemini directe par Vertex AI + Gemini.
 * Architecture : ADC (Application Default Credentials) + Google Cloud
 *
 * Avantages :
 * - Authentification via ADC (plus sûr, pas de clé en dur)
 * - Support RAG (Retrieval-Augmented Generation) pour contexte métier
 * - Meilleur monitoring & logging
 * - Access Control via IAM GCP
 */
class VertexAiService
{
    private string $projectId;
    private string $location;
    private string $model;
    private string $endpoint;
    private $accessToken;

    public function __construct()
    {
        $this->projectId = env('GOOGLE_CLOUD_PROJECT_ID', 'scan1-comptaflow');
        $this->location = env('VERTEX_AI_LOCATION', 'europe-west2');
        $this->model = env('VERTEX_AI_MODEL', 'gemini-2.5-flash');
        
        // URL Vertex AI endpoint
        $this->endpoint = "https://{$this->location}-aiplatform.googleapis.com/v1/projects/" 
            . env('GOOGLE_CLOUD_PROJECT_NUMBER', '288805151479') 
            . "/locations/{$this->location}/publishers/google/models/{$this->model}:generateContent";
        
        // Obtenir le token ADC
        $this->accessToken = $this->getAccessToken();
    }

    /**
     * Obtient le token d'accès via ADC (Application Default Credentials)
     */
    private function getAccessToken(): string
    {
        try {
            $credentials = ApplicationDefaultCredentials::getCredentials();
            
            // Si le token a expiré, le renouveler
            if ($credentials->hasExpired()) {
                $credentials->refreshTokenRequest(new \GuzzleHttp\Client());
            }
            
            return $credentials->getAccessToken();
        } catch (\Exception $e) {
            Log::error('Vertex AI ADC Error: ' . $e->getMessage());
            throw new \Exception('Impossible d\'obtenir le token ADC. Vérifiez la configuration GCP.');
        }
    }

    /**
     * Analyse une facture avec Vertex AI Gemini Vision
     * 
     * @param string $imageBase64 Données image en base64
     * @param string $mimeType Type MIME (image/jpeg, image/png, application/pdf)
     * @param string $prompt Master prompt avec contexte métier
     * @return array JSON structuré {est_facture, montant_ttc, ecriture[], confiance, ...}
     */
    public function analyzeInvoice(string $imageBase64, string $mimeType, string $prompt): array
    {
        $maxRetries = 5;
        $retryCount = 0;
        $baseDelay = 2;

        while ($retryCount < $maxRetries) {
            try {
                // Construire le payload Vertex AI
                $payload = [
                    'contents' => [
                        [
                            'role' => 'user',
                            'parts' => [
                                [
                                    'text' => $prompt
                                ],
                                [
                                    'inlineData' => [
                                        'mimeType' => $mimeType,
                                        'data' => $imageBase64
                                    ]
                                ]
                            ]
                        ]
                    ],
                    'generationConfig' => [
                        'temperature' => floatval(env('VERTEX_AI_TEMPERATURE', 0.2)),
                        'maxOutputTokens' => intval(env('VERTEX_AI_MAX_TOKENS', 4096)),
                        'topP' => 0.95,
                        'topK' => 40
                    ]
                ];

                Log::info('Vertex AI Request', [
                    'endpoint' => $this->endpoint,
                    'model' => $this->model,
                    'image_size' => strlen($imageBase64),
                    'prompt_length' => strlen($prompt)
                ]);

                // Appel Vertex AI via cURL
                $response = $this->callVertexApi($payload);

                if (isset($response['error'])) {
                    $statusCode = $response['http_code'] ?? 0;
                    
                    // Retry sur 429 (Quota exceeded) ou 503 (Service unavailable)
                    if (in_array($statusCode, [429, 503]) && $retryCount < $maxRetries - 1) {
                        $retryCount++;
                        $delay = $baseDelay * pow(2, $retryCount - 1) + rand(1, 3);
                        Log::warning("Vertex AI Retry ($statusCode). Waiting {$delay}s... (Attempt $retryCount/$maxRetries)");
                        sleep($delay);
                        continue;
                    }

                    // Erreur définitive
                    return [
                        'error' => $response['error'],
                        'http_code' => $statusCode,
                        'details' => $response['details'] ?? null
                    ];
                }

                // Succès : parser et valider la réponse
                return $this->parseVertexResponse($response);

            } catch (\Exception $e) {
                Log::error('Vertex AI Exception: ' . $e->getMessage());
                
                if ($retryCount < $maxRetries - 1) {
                    $retryCount++;
                    $delay = $baseDelay * pow(2, $retryCount - 1);
                    sleep($delay);
                    continue;
                }

                return [
                    'error' => 'Erreur Vertex AI: ' . $e->getMessage(),
                    'exception' => get_class($e)
                ];
            }
        }

        return ['error' => 'Vertex AI: Nombre maximum de tentatives atteint'];
    }

    /**
     * Appelle l'API Vertex AI via cURL
     */
    private function callVertexApi(array $payload): array
    {
        // Renouveler le token si nécessaire
        $this->accessToken = $this->getAccessToken();

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->endpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->accessToken,
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, intval(env('VERTEX_AI_TIMEOUT_SECONDS', 120)));
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($curlError) {
            return ['error' => "cURL Error: $curlError", 'http_code' => $httpCode];
        }

        if ($httpCode !== 200) {
            Log::error("Vertex AI HTTP $httpCode", [
                'response' => substr($response, 0, 500),
                'endpoint' => $this->endpoint
            ]);

            $decoded = json_decode($response, true);
            return [
                'error' => "HTTP $httpCode",
                'http_code' => $httpCode,
                'details' => $decoded
            ];
        }

        $decoded = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return [
                'error' => 'JSON Parse Error: ' . json_last_error_msg(),
                'http_code' => 200,
                'details' => ['raw' => substr($response, 0, 200)]
            ];
        }

        return ['success' => true, 'data' => $decoded];
    }

    /**
     * Parse la réponse Vertex AI et extrait le JSON de la facture
     */
    private function parseVertexResponse(array $response): array
    {
        if (!isset($response['data']['candidates'][0]['content']['parts'][0]['text'])) {
            return [
                'error' => 'Format de réponse Vertex AI invalide',
                'details' => $response['data'] ?? null
            ];
        }

        $text = $response['data']['candidates'][0]['content']['parts'][0]['text'];
        $finishReason = $response['data']['candidates'][0]['finishReason'] ?? 'UNKNOWN';

        Log::info('Vertex AI Response', [
            'finish_reason' => $finishReason,
            'text_length' => strlen($text)
        ]);

        // Nettoyer le texte (enlever backticks Markdown si présentes)
        $text = preg_replace('/```(?:json)?\s*(.*?)\s*```/s', '$1', $text);
        $text = trim($text);

        // Extraire le bloc JSON { ... }
        $start = strpos($text, '{');
        $end = strrpos($text, '}');

        if ($start === false || $end === false) {
            return [
                'error' => 'JSON non trouvé dans la réponse Vertex AI',
                'details' => ['text' => substr($text, 0, 300)]
            ];
        }

        $jsonText = substr($text, $start, $end - $start + 1);
        
        // Supprimer les caractères invisibles
        $jsonText = preg_replace('/[\x00-\x1F\x7F]/', '', $jsonText);

        $data = json_decode($jsonText, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return [
                'error' => 'JSON invalide: ' . json_last_error_msg(),
                'details' => ['json_text' => substr($jsonText, 0, 300)]
            ];
        }

        return ['data' => $data];
    }

    /**
     * Test simple pour vérifier la connectivité Vertex AI
     */
    public static function testConnection(): array
    {
        try {
            $service = new self();
            
            $testPayload = [
                'contents' => [
                    [
                        'role' => 'user',
                        'parts' => [
                            [
                                'text' => 'Réponds UNIQUEMENT avec : {"status":"ok"}'
                            ]
                        ]
                    ]
                ],
                'generationConfig' => [
                    'maxOutputTokens' => 50,
                    'temperature' => 0.1
                ]
            ];

            $response = $service->callVertexApi($testPayload);

            if (isset($response['error'])) {
                return [
                    'status' => 'error',
                    'message' => $response['error'],
                    'http_code' => $response['http_code'] ?? null
                ];
            }

            return [
                'status' => 'ok',
                'message' => 'Connexion Vertex AI réussie',
                'project_id' => $service->projectId,
                'location' => $service->location,
                'model' => $service->model
            ];

        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Obtient les informations de configuration (pour debug)
     */
    public static function getConfig(): array
    {
        return [
            'project_id' => env('GOOGLE_CLOUD_PROJECT_ID'),
            'project_number' => env('GOOGLE_CLOUD_PROJECT_NUMBER'),
            'location' => env('VERTEX_AI_LOCATION'),
            'model' => env('VERTEX_AI_MODEL'),
            'temperature' => env('VERTEX_AI_TEMPERATURE'),
            'max_tokens' => env('VERTEX_AI_MAX_TOKENS'),
            'timeout' => env('VERTEX_AI_TIMEOUT_SECONDS'),
        ];
    }
}
