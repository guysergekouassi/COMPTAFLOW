<?php
/**
 * Script de traitement IA pour COMPTAFLOW - Expert SYSCOHADA Côte d'Ivoire
 * Analyse intelligente des factures avec comptabilité ivoirienne
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
$prompt = <<<PROMPT
Tu es un expert-comptable SYSCOHADA Côte d'Ivoire de très haut niveau. Analyse cette pièce comptable et applique les normes ivoiriennes.

CONTEXTE SYSCOHADA CI :
- Classe 1 : Comptes de capitaux (10-19)
- Classe 2 : Comptes d'immobilisations (20-29) 
- Classe 3 : Comptes de stocks (30-39)
- Classe 4 : Comptes de tiers (40-49)
- Classe 5 : Comptes de trésorerie (50-59)
- Classe 6 : Comptes de charges (60-69)
- Classe 7 : Comptes de produits (70-79)

RÈGLES SPÉCIFIQUES CI :
401000 - Fournisseurs d'exploitation
402000 - Fournisseurs d'immobilisations  
411000 - Clients
421000 - Personnel
431000 - Sécurité sociale (CNPS)
442000 - Impôts et taxes
445000 - TVA
445100 - TVA déductible
445200 - TVA collectée
501000 - Caisse
521000 - Banques
571000 - Caisse principale
601000 - Achats de marchandises
603000 - Achats de matières premières
604000 - Achats d'études et prestations
605000 - Achats de matériel
607000 - Achats non stockés
611000 - Transports
613000 - Locations
614000 - Entretien et réparations
616000 - Primes d'assurance
622000 - Rémunérations d'intermédiaires
623000 - Publicité
624000 - Transports de personnel
625000 - Déplacements
626000 - Frais postaux
627000 - Services bancaires
631000 - Impôts et taxes
641000 - Charges de personnel
661000 - Charges d'intérêts
701000 - Ventes de marchandises
702000 - Ventes de produits finis
706000 - Services vendus
707000 - Produits accessoires

ANALYSE COMPTABLE EXIGÉE :
1. Identifier le type de document (facture, reçu, note de frais)
2. Extraire le nom du tiers (fournisseur/client)
3. Déterminer la nature de l'opération
4. Appliquer les comptes SYSCOHADA CI appropriés
5. Calculer les montants HT, TVA (18%), TTC
6. Générer l'écriture comptable équilibrée

FORMAT JSON EXIGÉ (UNIQUEMENT) :
{
  "type_document": "Facture/Reçu/Note",
  "tiers": "Nom exact du tiers",
  "date": "AAAA-MM-JJ",
  "reference": "Numéro pièce",
  "montant_ht": 0,
  "montant_tva": 0,
  "montant_ttc": 0,
  "devise": "XOF",
  "ecriture": [
    {
      "compte": "code_complet_6_chiffres",
      "intitule": "Libellé comptable précis",
      "debit": 0,
      "credit": 0
    }
  ],
  "analyse": "Brève explication du choix des comptes"
}

CONTRAINTES :
- Utiliser OBLIGATOIREMENT les comptes à 6 chiffres
- Total débit = Total crédit
- Si document payé en espèces : utiliser 571000
- Si document payé par banque : utiliser 521000
- Si facture non payée : utiliser 401000
- TVA 18% si mentionnée : utiliser 445100 au débit
PROMPT;

// 3. Construction du Payload pour Gemini
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
        "topP" => 0.8,
        "topK" => 40,
        "maxOutputTokens" => 2000,
        "response_mime_type" => "application/json"
    ]
];

// 4. Appel de l'API avec retry intelligent
$max_retries = 3;
$retry_count = 0;

while ($retry_count < $max_retries) {
    try {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);

        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if (curl_errno($ch)) {
            throw new Exception('Erreur cURL: ' . curl_error($ch));
        }

        curl_close($ch);

        // Gestion du quota 429
        if ($http_code == 429) {
            $retry_count++;
            if ($retry_count >= $max_retries) {
                echo json_encode([
                    'error' => 'Quota dépassé. Réessayez plus tard.',
                    'retry_count' => $retry_count
                ]);
                exit;
            }
            
            // Attente progressive : 2s, 4s, 8s
            sleep(pow(2, $retry_count));
            continue;
        }

        break; // Succès, sortir de la boucle

    } catch (Exception $e) {
        $retry_count++;
        if ($retry_count >= $max_retries) {
            echo json_encode(['error' => $e->getMessage()]);
            exit;
        }
        sleep(2);
    }
}

// 5. Traitement de la réponse
if ($http_code === 200) {
    $result = json_decode($response, true);
    
    if (isset($result['candidates'][0]['content']['parts'][0]['text'])) {
        $json_comptable = $result['candidates'][0]['content']['parts'][0]['text'];
        
        // Nettoyage du JSON
        $json_comptable = preg_replace('/```json\s*/', '', $json_comptable);
        $json_comptable = preg_replace('/```\s*$/', '', $json_comptable);
        $json_comptable = trim($json_comptable);
        
        // Validation du JSON
        $data = json_decode($json_comptable, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            echo $json_comptable;
        } else {
            echo json_encode([
                'error' => 'JSON invalide généré par l\'IA',
                'raw_response' => $json_comptable
            ]);
        }
    } else {
        echo json_encode([
            'error' => 'Réponse vide de l\'IA',
            'response' => $result
        ]);
    }
} else {
    echo json_encode([
        'error' => "Erreur API ($http_code)",
        'details' => json_decode($response, true)
    ]);
}
?>
