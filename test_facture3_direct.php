<?php
// Test direct de la facture3 avec l'API Gemini

// Configuration
$api_key = "AIzaSyDuwMm9cdo_vTqBe9j3degykq4rL-kOKVU";
$model = "gemini-flash-latest"; 
$url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$api_key}";

// Préparation de l'image
$image_path = 'c:\laragon\www\COMPTAFLOW\facture3.jpg';
if (!file_exists($image_path)) {
    echo "ERREUR: Fichier facture3.jpg non trouvé !\n";
    exit;
}

$image_data = base64_encode(file_get_contents($image_path));
$mime_type = 'image/jpeg';

echo "=== TEST DE LA FACTURE3 AVEC API GEMINI ===\n";
echo "Fichier: $image_path\n";
echo "Taille: " . filesize($image_path) . " octets\n";
echo "==========================================\n\n";

// Prompt
$prompt = <<<PROMPT
Tu es un expert-comptable SYSCOHADA Côte d'Ivoire. Analyse cette facture ATTENTIVEMENT.

RÈGLES CRUCIALES :
1. DÉTECTE si la facture contient de la TVA :
   - Cherche les mots : "TVA", "Taxe", "HT", "TTC", "18%"
   - Vérifie s'il y a des montants séparés HT/TTC

2. SI TVA DÉTECTÉE :
   - Ajoute une ligne de TVA déductible (compte 445100)
   - Mets montant_tva > 0
   - Le montant TTC doit inclure la TVA

3. SI PAS DE TVA DÉTECTÉE :
   - N'ajoute PAS de ligne de TVA
   - Mets montant_tva = 0
   - Le montant HT = montant TTC

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
    {"compte": "401000", "intitule": "Fournisseurs", "debit": 0, "credit": 10000}
  ]
}

ATTENTION : N'ajoute la ligne 445100 (TVA) QUE SI la facture contient réellement de la TVA !
PROMPT;

// Payload pour Gemini
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
        "maxOutputTokens" => 4000,
        "response_mime_type" => "application/json"
    ]
];

echo "Envoi à l'API Gemini...\n";

// Appel API
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 15);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

echo "HTTP Code: $http_code\n";
echo "Error: " . ($error ?: 'None') . "\n\n";

if ($http_code === 200) {
    $result = json_decode($response, true);
    if (isset($result['candidates'][0]['content']['parts'][0]['text'])) {
        $json_comptable = $result['candidates'][0]['content']['parts'][0]['text'];
        
        echo "Réponse brute de Gemini:\n";
        echo "========================\n";
        echo $json_comptable . "\n\n";
        
        // Nettoyage du JSON
        $json_comptable = preg_replace('/```json\s*/', '', $json_comptable);
        $json_comptable = preg_replace('/```\s*$/', '', $json_comptable);
        $json_comptable = trim($json_comptable);
        
        // Validation
        $data = json_decode($json_comptable, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            echo "JSON valide reçu !\n";
            echo "==================\n";
            echo json_encode($data, JSON_PRETTY_PRINT) . "\n\n";
            
            // Analyse TVA
            $hasVAT = isset($data['montant_tva']) && $data['montant_tva'] > 0;
            echo "ANALYSE TVA:\n";
            echo "-----------\n";
            echo "Montant TVA: " . ($data['montant_tva'] ?? 0) . "\n";
            echo "TVA détectée: " . ($hasVAT ? 'OUI' : 'NON') . "\n";
            
            if (isset($data['ecriture'])) {
                echo "Nombre d'écritures: " . count($data['ecriture']) . "\n";
                foreach ($data['ecriture'] as $i => $ligne) {
                    echo "Ligne " . ($i+1) . ": " . $ligne['compte'] . " - " . $ligne['intitule'] . "\n";
                }
            }
        } else {
            echo "ERREUR JSON: " . json_last_error_msg() . "\n";
        }
    } else {
        echo "ERREUR: Pas de texte dans la réponse Gemini\n";
    }
} else {
    echo "ERREUR HTTP: $http_code\n";
    echo "Détails: " . $response . "\n";
}
?>
