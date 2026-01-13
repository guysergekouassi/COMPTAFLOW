<?php
/**
 * Script de traitement IA pour COMPTAFLOW - Expert SYSCOHADA Côte d'Ivoire
 * Version autonome sans Laravel
 */

header('Content-Type: application/json');

// --- CONFIGURATION ---
$api_key = "AIzaSyDuwMm9cdo_vTqBe9j3degykq4rL-kOKVU";
$model = "gemini-flash-latest"; 
$url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$api_key}";

// Vérifier si une image est envoyée
if (!isset($_FILES['facture'])) {
    echo json_encode(['error' => 'Aucune image reçue.']);
    exit;
}

// 1. Préparation de l'image
$image_path = $_FILES['facture']['tmp_name'];
$image_data = base64_encode(file_get_contents($image_path));
$mime_type = $_FILES['facture']['type'];

// 2. Prompt Expert SYSCOHADA Côte d'Ivoire
$default_prompt = <<<PROMPT
Tu es un expert-comptable SYSCOHADA Côte d'Ivoire. Analyse cette facture.

FORMAT JSON EXIGÉ :
{
  "hasVAT": false,
  "type_document": "Facture",
  "tiers": "Nom du fournisseur",
  "date": "AAAA-MM-JJ",
  "reference": "Numéro pièce",
  "montant_ht": 0,
  "montant_tva": 0,
  "montant_ttc": 0,
  "ecriture": [
    {"compte": "601000", "type": "CHARGE", "intitule": "Achats marchandises", "debit": 10000, "credit": 0},
    {"compte": "445100", "type": "TVA", "intitule": "TVA déductible", "debit": 1800, "credit": 0},
    {"compte": "401000", "type": "FOURNISSEUR", "intitule": "Fournisseurs", "debit": 0, "credit": 11800}
  ]
}
PROMPT;

$prompt = isset($_POST['prompt']) ? $_POST['prompt'] : $default_prompt;

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
        "temperature" => 0.1,
        "maxOutputTokens" => 4000,
        "response_mime_type" => "application/json"
    ]
];

// 4. Appel API avec retry intelligent anti-429
$max_retries = 5;
$retry_count = 0;
$base_delay = 5; 

while ($retry_count < $max_retries) {
    try {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($http_code == 429) {
            $retry_count++;
            if ($retry_count >= $max_retries) {
                echo json_encode([
                    'error' => 'Quota Gemini dépassé (429). Réessayez dans quelques minutes.',
                    'retry_count' => $retry_count
                ]);
                exit;
            }
            $delay = $base_delay * pow(2, $retry_count);
            sleep($delay);
            continue;
        }

        if ($http_code === 200) {
            $result = json_decode($response, true);
            if (isset($result['candidates'][0]['content']['parts'][0]['text'])) {
                $json_comptable = $result['candidates'][0]['content']['parts'][0]['text'];
                
                // Nettoyage du JSON
                $json_comptable = preg_replace('/```json\s*/', '', $json_comptable);
                $json_comptable = preg_replace('/```\s*$/', '', $json_comptable);
                $json_comptable = trim($json_comptable);
                
                // Validation et retour
                $data = json_decode($json_comptable, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    echo $json_comptable;
                } else {
                    echo json_encode([
                        'error' => 'JSON invalide généré par l\'IA',
                        'raw_response' => $json_comptable,
                        'json_error' => json_last_error_msg()
                    ]);
                }
                exit;
            }
        }

        // Erreur HTTP
        $details = json_decode($response, true);
        echo json_encode([
            'error' => "Erreur API Google ($http_code)",
            'details' => $details,
            'curl_error' => $error
        ]);
        exit;

    } catch (Exception $e) {
        $retry_count++;
        if ($retry_count >= $max_retries) {
            echo json_encode(['error' => $e->getMessage()]);
            exit;
        }
        sleep($base_delay * $retry_count);
    }
}
?>
