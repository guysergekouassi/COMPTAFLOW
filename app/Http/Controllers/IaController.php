<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class IaController extends Controller
{
    public function traiterFacture(Request $request)
    {
        try {
            // Vérifier si une image est envoyée
            if (!$request->hasFile('facture')) {
                return response()->json(['error' => 'Aucune image reçue.'], 400);
            }

            // Créer un hash unique pour cette image
            $image = $request->file('facture');
            $image_hash = md5_file($image->getPathname()) . '_' . $image->getSize();
            $cache_key = "ia_analysis_" . $image_hash;

            // Vérifier si on a déjà analysé cette image récemment
            if (Cache::has($cache_key)) {
                return response()->json([
                    'message' => 'Résultat récupéré du cache',
                    'data' => Cache::get($cache_key)
                ]);
            }

            // --- CONFIGURATION ---
            $api_key = env('GEMINI_API_KEY');
            if (!$api_key) {
                return response()->json(['error' => 'Clé API Gemini manquante dans le fichier .env'], 500);
            }
            $model = "gemini-flash-latest"; 
            $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$api_key}";

            // 1. Préparation de l'image
            $image_data = base64_encode(file_get_contents($image->getPathname()));
            $mime_type = $image->getMimeType();

            // 2. Prompt Expert SYSCOHADA Côte d'Ivoire
            $prompt = <<<PROMPT
Tu es un expert-comptable SYSCOHADA Côte d'Ivoire. Analyse cette facture.

FORMAT JSON EXIGÉ :
{
  "type_document": "Facture",
  "tiers": "Nom du fournisseur",
  "date": "AAAA-MM-JJ",
  "reference": "Numéro pièce",
  "montant_ht": 0,
  "montant_tva": 0,
  "montant_ttc": 0,
  "ecriture": [
    {"compte": "601000", "intitule": "Achats marchandises", "debit": 10000, "credit": 0},
    {"compte": "445100", "intitule": "TVA déductible", "debit": 1800, "credit": 0},
    {"compte": "401000", "intitule": "Fournisseurs", "debit": 0, "credit": 11800}
  ]
}
PROMPT;

            // 3. Payload pour Gemini
            $payload = [
                "contents" => [
                    [
                        "parts" => [
                            ["text" => $prompt],
                            [
                                "inline_data" => [
                                    "mime_type" => $mime_type,
                                    "data" => $image_data
                                ]
                            ]
                        ]
                    ]
                ],
                "generationConfig" => [
                    "temperature" => 0.2,
                    "maxOutputTokens" => 2000,
                    "response_mime_type" => "application/json"
                ]
            ];

            // 4. Appel API avec retry intelligent anti-429
            $max_retries = 5;
            $retry_count = 0;
            $base_delay = 2; // 2 secondes de base

            while ($retry_count < $max_retries) {
                try {
                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, $url);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_POST, true);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
                    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
                    curl_setopt($ch, CURLOPT_TIMEOUT, 120); // Timeout 2 minutes
                    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30); // Timeout connexion 30s
                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

                    $response = curl_exec($ch);
                    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                    curl_close($ch);

                    // Gestion spécifique du quota 429
                    if ($http_code == 429) {
                        $retry_count++;
                        if ($retry_count >= $max_retries) {
                            return response()->json([
                                'error' => 'Quota Gemini dépassé. Réessayez dans quelques minutes.',
                                'retry_count' => $retry_count,
                                'suggestion' => 'Vérifiez votre quota sur https://aistudio.google.com/app/apikey',
                                'alternatives' => [
                                    '1. Attendre 10-15 minutes',
                                    '2. Utiliser une autre clé API',
                                    '3. Passer au plan payant Gemini'
                                ]
                            ], 429);
                        }
                        
                        // Backoff exponentiel avec jitter : 2s, 6s, 18s, 54s, 162s
                        $delay = $base_delay * pow(3, $retry_count - 1) + rand(1, 3);
                        sleep($delay);
                        continue;
                    }

                    // Succès, sortir de la boucle
                    break;

                } catch (\Exception $e) {
                    $retry_count++;
                    if ($retry_count >= $max_retries) {
                        return response()->json(['error' => $e->getMessage()], 500);
                    }
                    sleep($base_delay * $retry_count);
                }
            }

            if ($http_code === 200) {
                $result = json_decode($response, true);
                if (isset($result['candidates'][0]['content']['parts'][0]['text'])) {
                    $json_comptable = $result['candidates'][0]['content']['parts'][0]['text'];
                    
                    // Nettoyage du JSON
                    $json_comptable = preg_replace('/```json\s*/', '', $json_comptable);
                    $json_comptable = preg_replace('/```\s*$/', '', $json_comptable);
                    $json_comptable = trim($json_comptable);
                    
                    // Parser et valider le JSON
                    $data = json_decode($json_comptable, true);
                    if (json_last_error() === JSON_ERROR_NONE) {
                        // Mettre en cache pour 1 heure
                        Cache::put($cache_key, $data, 3600);
                        
                        return response($json_comptable, 200, ['Content-Type' => 'application/json']);
                    } else {
                        return response()->json([
                            'error' => 'JSON invalide généré par l\'IA',
                            'raw_response' => $json_comptable,
                            'json_error' => json_last_error_msg()
                        ], 500);
                    }
                }
            }

            return response()->json([
                'error' => "Erreur API ($http_code)",
                'details' => json_decode($response, true)
            ], 500);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
