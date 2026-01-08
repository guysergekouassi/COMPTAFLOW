<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class GeminiController extends Controller
{
    public function generateText(Request $request)
    {
        $prompt = $request->input('prompt', 'Bonjour Gemini !');
        $apiKey = config('services.gemini.key');
        
        if (!$apiKey) {
            return response()->json([
                'error' => 'Clé API Gemini manquante dans le fichier .env (GEMINI_API_KEY)'
            ], 500);
        }

        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->post("https://generativelanguage.googleapis.com/v1beta/models/gemini-pro:generateContent?key=" . $apiKey, [
                'contents' => [
                    'parts' => [
                        ['text' => $prompt]
                    ]
                ],
                'generationConfig' => [
                    'temperature' => 0.5,
                    'maxOutputTokens' => 2000,
                ]
            ]);

            $responseData = $response->json();
            
            if (isset($responseData['error'])) {
                return response()->json([
                    'error' => 'Erreur de l\'API Gemini: ' . ($responseData['error']['message'] ?? 'Erreur inconnue')
                ], 500);
            }

            return response()->json([
                'text' => $responseData['candidates'][0]['content']['parts'][0]['text'] ?? 'Aucune réponse générée'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erreur lors de l\'appel à l\'API Gemini: ' . $e->getMessage()
            ], 500);
        }
    }
}
