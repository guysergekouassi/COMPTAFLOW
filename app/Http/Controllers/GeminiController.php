<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class GeminiController extends Controller
{
    public function generateText(Request $request)
    {
        $prompt = $request->input('prompt', 'Bonjour Gemini !');
        $image = $request->input('image');
        $apiKey = env('GEMINI_API_KEY');
        
        if (!$apiKey) {
            return response()->json([
                'error' => 'Clé API Gemini manquante dans le fichier .env (GEMINI_API_KEY)'
            ], 500);
        }

        try {
            // Déterminer le modèle à utiliser
            $model = $image ? 'gemini-1.5-flash' : 'gemini-1.5-flash';

            // Préparer les parties du contenu
            $parts = [
                ['text' => $prompt]
            ];

            if ($image) {
                $parts[] = [
                    'inline_data' => [
                        'mime_type' => 'image/jpeg',
                        'data' => str_replace(['data:image/jpeg;base64,', ' '], ['', '+'], $image)
                    ]
                ];
            }

            $payload = [
                'contents' => [
                    [
                        'role' => 'user',
                        'parts' => $parts
                    ]
                ],
                'generationConfig' => [
                    'temperature' => 0.2,
                    'topP' => 0.8,
                    'topK' => 40,
                    'maxOutputTokens' => 2000,
                ]
            ];

            // Appel à l'API Gemini
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->withoutVerifying()
              ->timeout(60)
              ->post("https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}", $payload);

            $responseData = $response->json();
            $httpCode = $response->status();
            $responseBody = $response->body();
            
            Log::info('API Gemini - Code HTTP:', ['code' => $httpCode]);
            Log::info('API Gemini - Réponse brute (autour position 293):', ['snippet' => substr($responseBody, 280, 30)]);
            Log::info('API Gemini - Réponse complète:', ['body' => $responseBody]);
            
            if ($httpCode !== 200) {
                Log::error('API Gemini - Erreur HTTP:', ['code' => $httpCode, 'body' => $responseBody]);
                return response()->json([
                    'error' => "Erreur HTTP {$httpCode} de l'API Gemini",
                    'response' => $responseBody
                ], 500);
            }

            if (isset($responseData['error'])) {
                return response()->json([
                    'error' => 'Erreur de l\'API Gemini: ' . ($responseData['error']['message'] ?? 'Erreur inconnue'),
                    'details' => $responseData['error'] ?? null
                ], 500);
            }

            if (empty($responseData['candidates']) || empty($responseData['candidates'][0]['content']['parts'][0]['text'])) {
                Log::error('Structure réponse API:', ['keys' => array_keys($responseData), 'candidates' => isset($responseData['candidates']) ? 'exists' : 'missing']);
                if (isset($responseData['candidates'])) {
                    Log::error('Candidates structure:', ['count' => count($responseData['candidates']), 'first_keys' => $responseData['candidates'][0] ? array_keys($responseData['candidates'][0]) : 'empty']);
                }
                return response()->json([
                    'error' => 'Réponse inattendue de l\'API Gemini',
                    'raw_response' => $responseData
                ], 500);
            }

            Log::info('Succès API Gemini - Texte généré:', ['text_length' => strlen($responseData['candidates'][0]['content']['parts'][0]['text'])]);

            $generatedText = $responseData['candidates'][0]['content']['parts'][0]['text'];
            
            return response()->json([
                'text' => $generatedText
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erreur lors de l\'appel à l\'API Gemini: ' . $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 500);
        }
    }
}
