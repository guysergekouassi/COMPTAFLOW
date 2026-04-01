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
 */
class VertexAiService
{
    private string $projectId;
    private string $projectNumber;
    private string $location;
    private string $model;
    private string $apiVersion;
    private $accessToken;
    private string $endpoint;

    public function __construct()
    {
        $this->projectId = (string) (config('services.vertex_ai.project_id') ?? 'scan1-comptaflow');
        $this->projectNumber = (string) (config('services.vertex_ai.project_number') ?? '288805151479');
        $this->location = (string) (config('services.vertex_ai.location') ?? 'europe-west2');
        $this->model = (string) (config('services.vertex_ai.model') ?? 'gemini-1.5-flash');
        $this->apiVersion = (string) (config('services.vertex_ai.api_version') ?? env('VERTEX_AI_API_VERSION', 'v1'));

        
        // S'assurer que GOOGLE_APPLICATION_CREDENTIALS est visible pour getenv()
        $creds = env('GOOGLE_APPLICATION_CREDENTIALS');
        
        // Fallback automatique : si non défini, chercher 'credentials.json' à la racine
        if (!$creds && file_exists(base_path('credentials.json'))) {
            $creds = 'credentials.json';
        }

        if ($creds) {
            if (!preg_match('/^([a-zA-Z]:|\/)/', $creds)) {
                $creds = base_path($creds);
            }
            putenv("GOOGLE_APPLICATION_CREDENTIALS=$creds");
        }


        // Fix SSL for Laragon / Windows
        $caPath = 'C:/laragon/etc/ssl/cacert.pem';
        if (file_exists($caPath)) {
            ini_set('curl.cainfo', $caPath);
            ini_set('openssl.cafile', $caPath);
            putenv("CURL_CA_BUNDLE=$caPath");
            putenv("SSL_CERT_FILE=$caPath");
        }

        // URL Vertex AI endpoint (v1 ou v1beta1)
        $this->endpoint = "https://{$this->location}-aiplatform.googleapis.com/{$this->apiVersion}/projects/{$this->projectNumber}/locations/{$this->location}/publishers/google/models/{$this->model}:generateContent";
    }

    /**
     * Obtient le token d'accès via ADC (Application Default Credentials)
     */
    private function getAccessToken(): string
    {
        try {
            // Utilise les scopes nécessaires pour Vertex AI
            $scopes = ['https://www.googleapis.com/auth/cloud-platform'];
            $credentials = ApplicationDefaultCredentials::getCredentials($scopes);
            
            // Récupérer le token (gère le rafraîchissement si nécessaire via le cache interne de Google Auth)
            $token = $credentials->fetchAuthToken();
            
            return $token['access_token'];
        } catch (\Exception $e) {
            Log::error('Vertex AI ADC Error: ' . $e->getMessage());
            throw new \Exception('Impossible d\'obtenir le token ADC. Vérifiez la configuration GCP ou exécutez gcloud auth application-default login.');
        }
    }

    /**
     * Analyse une facture avec Vertex AI Gemini Vision
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
                                ['text' => $prompt],
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
                        'temperature' => floatval(config('services.vertex_ai.temperature', 0.2)),
                        'maxOutputTokens' => intval(config('services.vertex_ai.max_tokens', 4096)),
                        'topP' => 0.95,
                        'topK' => 40
                    ]
                ];

                Log::info('Vertex AI Request', [
                    'endpoint' => $this->endpoint,
                    'model' => $this->model,
                    'mime' => $mimeType
                ]);

                // Appel Vertex AI via cURL
                $response = $this->callVertexApi($payload);

                if (isset($response['error'])) {
                    $statusCode = $response['http_code'] ?? 0;
                    
                    if (in_array($statusCode, [429, 503]) && $retryCount < $maxRetries - 1) {
                        $retryCount++;
                        $delay = $baseDelay * pow(2, $retryCount - 1);
                        sleep($delay);
                        continue;
                    }

                    return [
                        'error' => $response['error'],
                        'http_code' => $statusCode,
                        'details' => $response['details'] ?? null,
                        'raw_response' => json_encode($response['data'] ?? [])
                    ];
                }

                $parsed = $this->parseVertexResponse($response);
                if (isset($parsed['error']) && isset($response['data']['candidates'][0]['content']['parts'][0]['text'])) {
                    $parsed['raw_response'] = $response['data']['candidates'][0]['content']['parts'][0]['text'];
                }
                return $parsed;

            } catch (\Exception $e) {
                Log::error('Vertex AI Exception: ' . $e->getMessage());
                if ($retryCount < $maxRetries - 1) {
                    $retryCount++;
                    sleep($baseDelay * $retryCount);
                    continue;
                }
                return ['error' => 'Erreur Vertex AI: ' . $e->getMessage()];
            }
        }

        return ['error' => 'Vertex AI: Nombre maximum de tentatives atteint'];
    }

    private function callVertexApi(array $payload): array
    {
        // Renouveler le token pour chaque appel (fetchAuthToken est rapide car il utilise le cache si valide)
        $token = $this->getAccessToken();

        Log::info('Vertex AI - Initiation de l\'appel API', [
            'endpoint' => $this->endpoint,
            'model' => $this->model,
            'payload_size' => strlen(json_encode($payload))
        ]);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->endpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $token,
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, intval(env('VERTEX_AI_TIMEOUT_SECONDS', 120)));

        // SSL Handling - Forcer la vǸrification en prod si possible
        $caPath = 'C:/laragon/etc/ssl/cacert.pem';
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN' && file_exists($caPath)) {
             curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
             curl_setopt($ch, CURLOPT_CAINFO, $caPath);
        } else {
             // En ligne (Linux), on laisse le systǸme gǸrer ou on dǸsactive si persistant
             curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, env('VERTEX_AI_SSL_VERIFY', true));
        }

        $response = curl_exec($ch);
        $errno = curl_errno($ch);
        $error = curl_error($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);


        Log::info('Vertex AI - Résultat cURL', [
            'http_code' => $httpCode,
            'errno' => $errno,
            'error' => $error,
            'response_preview' => substr($response, 0, 200)
        ]);

        curl_close($ch);

        if ($httpCode !== 200) {
            Log::error('Vertex AI - Erreur API détectée', [
                'http_code' => $httpCode,
                'response' => $response,
                'curl_error' => $error
            ]);
            return [
                'error' => "HTTP $httpCode",
                'http_code' => $httpCode,
                'details' => $response
            ];
        }

        return ['success' => true, 'data' => json_decode($response, true)];
    }

    private function parseVertexResponse(array $response): array
    {
        if (!isset($response['data']['candidates'][0]['content']['parts'][0]['text'])) {
            return ['error' => 'Format de réponse Vertex AI invalide'];
        }

        $text = $response['data']['candidates'][0]['content']['parts'][0]['text'];
        
        // 1. Nettoyage Markdown
        $text = preg_replace('/```(?:json)?\s*(.*?)\s*```/s', '$1', $text);
        
        // 2. Recherche du bloc JSON le plus large { ... }
        $firstBracket = strpos($text, '{');
        $lastBracket = strrpos($text, '}');

        if ($firstBracket !== false && $lastBracket !== false) {
            $jsonText = substr($text, $firstBracket, $lastBracket - $firstBracket + 1);
            
            // 3. Nettoyage des caractères de contrôle et UTF8 invisibles
            $jsonText = preg_replace('/[\x00-\x1F\x7F]/', '', $jsonText);
            
            // 4. Tentatives de décodage
            $data = json_decode($jsonText, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return ['data' => $data];
            }
            
            // 5. Correction des erreurs JSON courantes (virgules traînantes)
            $jsonTextFixed = preg_replace('/,\s*([\]\}])/', '$1', $jsonText);
            $data = json_decode($jsonTextFixed, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return ['data' => $data];
            }
        }

        return [
            'error' => 'Échec de l\'extraction JSON. Réponse brute: ' . substr($text, 0, 100) . '...',
            'raw_debug' => $text
        ];
    }

    public static function testConnection(): array
    {
        try {
            $service = new self();
            $token = $service->getAccessToken();
            return [
                'status' => 'ok',
                'message' => 'Token ADC récupéré avec succès',
                'token_preview' => substr($token, 0, 10) . '...',
                'project_id' => $service->projectId
            ];
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    public static function getConfig(): array
    {
        return [
            'project_id' => config('services.vertex_ai.project_id'),
            'location' => config('services.vertex_ai.location'),
            'model' => config('services.vertex_ai.model'),
            'api_version' => config('services.vertex_ai.api_version'),
        ];
    }

}
