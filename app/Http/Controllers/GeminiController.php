<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class GeminiController extends Controller
{
    public function generateText(Request $request)
    {
        $text = $request->input('prompt', 'Bonjour Gemini !');

        // 🔑 Vérifie que la clé API est définie
        $apiKey = env('GEMINI_API_KEY');
        if (!$apiKey) {
            return response()->json(['error' => 'Clé API Gemini manquante'], 500);
        }

        // ❌ Ici tu dois remplacer le modèle par un modèle disponible pour ta clé
        $model = 'gemini-1.5'; // par exemple, au lieu de 'gemini-1.5-pro-latest'

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type' => 'application/json',
            ])->post("https://api.gemini.com/v1/models/$model/complete", [
                'prompt' => $text,
                'max_tokens' => 500,
            ]);

            return response()->json($response->json());
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erreur API Gemini : ' . $e->getMessage()
            ], 500);
        }
    }
}
