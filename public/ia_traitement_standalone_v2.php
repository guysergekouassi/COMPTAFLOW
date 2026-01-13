<?php
/**
 * Script de traitement IA pour COMPTAFLOW - Expert SYSCOHADA Côte d'Ivoire
 * Version autonome sans Laravel - DEBUG & ROBUST VERSION
 */

header('Content-Type: application/json');

// --- CONFIGURATION ---
$api_key = trim("AIzaSyDuwMm9cdo_vTqBe9j3degykq4rL-kOKVU");

// Liste des modèles à tenter - Ordre basé sur les tests (2.0-flash-exp a renvoyé 429 = autorisé)
$models = [
    'gemini-2.0-flash-exp',
    'gemini-2.0-flash',
    'gemini-flash-latest'
];

$api_version = 'v1beta';

// Vérifier si une image est envoyée
if (!isset($_FILES['facture'])) {
    echo json_encode(['error' => 'Aucune image reçue.']);
    exit;
}

$image_path = $_FILES['facture']['tmp_name'];
$image_data = base64_encode(file_get_contents($image_path));
$mime_type = $_FILES['facture']['type'];

$default_prompt = "Analyse cette facture expert-comptable SYSCOHADA Côte d'Ivoire. Retourne un JSON.";
$prompt = isset($_POST['prompt']) ? $_POST['prompt'] : $default_prompt;

$payload = [
    "contents" => [
        [
            "parts" => [
                ["text" => $prompt],
                ["inline_data" => ["mime_type" => $mime_type, "data" => $image_data]]
            ]
        ]
    ],
    "generationConfig" => ["temperature" => 0.1, "response_mime_type" => "application/json"]
];

$payload_json = json_encode($payload);
$last_attempt = null;

foreach ($models as $model) {
    $url = "https://generativelanguage.googleapis.com/{$api_version}/models/{$model}:generateContent?key={$api_key}";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload_json);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_TIMEOUT, 60);

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curl_error = curl_error($ch);
    curl_close($ch);

    if ($http_code === 200) {
        $result = json_decode($response, true);
        if (isset($result['candidates'][0]['content']['parts'][0]['text'])) {
            echo $result['candidates'][0]['content']['parts'][0]['text'];
            exit;
        }
    }

    $last_attempt = [
        'model' => $model,
        'http_code' => $http_code,
        'curl_error' => $curl_error,
        'api_response' => json_decode($response, true)
    ];

    if ($http_code === 429) {
        echo json_encode([
            'error' => "Quota Google dépassé pour le modèle {$model}.",
            'details' => "Veuillez attendre 1 minute. Cette erreur confirme que la clé est valide mais saturée.",
            'api_message' => $last_attempt['api_response']['error']['message'] ?? ''
        ]);
        exit;
    }

    // On continue la boucle si 404 (modèle non trouvé) ou 403 (pas autorisé pour ce modèle spécifique)
}

echo json_encode([
    'error' => "Échec de l'analyse : Aucun modèle autorisé trouvé.",
    'diagnostic' => "Dernier test sur {$last_attempt['model']} : Code {$last_attempt['http_code']}",
    'debug' => $last_attempt
]);
?>
