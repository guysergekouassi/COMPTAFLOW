<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
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
            // Préparer la structure de base
            $payload = [
                'contents' => [
                    [
                        'role' => 'user',
                        'parts' => [
                            ['text' => $prompt]
                        ]
                    ]
                ],
                'generationConfig' => [
                    'temperature' => 0.2,
                    'topP' => 0.8,
                    'topK' => 40,
                    'maxOutputTokens' => 2000,
                ]
            ];

            // Si une image est fournie, l'ajouter aux parties
            if ($image) {
                $payload['contents'][0]['parts'][] = [
                    'inlineData' => [
                        'mimeType' => 'image/jpeg',
                        'data' => $image
                    ]
                ];
                // Utiliser le modèle qui supporte les images
                $model = 'gemini-1.5-pro';
            } else {
                // Modèle pour le texte uniquement
                $model = 'gemini-1.5-pro';
            }

            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->post("https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}", $payload);

            $responseData = $response->json();
            
            if (isset($responseData['error'])) {
                return response()->json([
                    'error' => 'Erreur de l\'API Gemini: ' . ($responseData['error']['message'] ?? 'Erreur inconnue')
                ], 500);
            }

            if (empty($responseData['candidates'][0]['content']['parts'][0]['text'])) {
                return response()->json([
                    'error' => 'Réponse inattendue de l\'API Gemini',
                    'raw_response' => $responseData
                ], 500);
            }

            return response()->json([
                'text' => $responseData['candidates'][0]['content']['parts'][0]['text']
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erreur lors de l\'appel à l\'API Gemini: ' . $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 500);
        }
    }
}
