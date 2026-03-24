<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class GeminiController extends Controller
{
    private \App\Services\VertexAiService $vertexAiService;

    public function __construct()
    {
        $this->vertexAiService = new \App\Services\VertexAiService();
    }

    public function generateText(Request $request)
    {
        $prompt = $request->input('prompt', 'Bonjour Gemini !');
        $image = $request->input('image');
        
        try {
            $mimeType = 'image/jpeg';
            $imageData = null;

            if ($image) {
                // Nettoyage data URL
                $imageData = str_replace(['data:image/jpeg;base64,', 'data:image/png;base64,', ' '], ['', '', '+'], $image);
            }

            // Appel Vertex AI via le service
            // Si pas d'image, on passe une chaîne vide ou on adapte le service
            // Ici, on utilise analyzeInvoice qui attend une image. 
            // Si besoin de texte seul, on pourrait ajouter une méthode analyzeText au service.
            // Pour l'instant, on utilise l'existant.
            
            $result = $this->vertexAiService->analyzeInvoice($imageData ?? '', $mimeType, $prompt);

            if (isset($result['error'])) {
                return response()->json([
                    'error' => $result['error'],
                    'details' => $result['details'] ?? null
                ], 500);
            }

            // VertexAiService parse déjà le JSON si possible, mais ici on veut peut-être juste le texte brut
            // ou le résultat de l'analyse.
            $data = $result['data'];
            
            return response()->json([
                'text' => $data['text'] ?? ($data['analyse'] ?? json_encode($data)),
                'raw' => $data
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erreur Vertex AI : ' . $e->getMessage(),
            ], 500);
        }
    }
}
