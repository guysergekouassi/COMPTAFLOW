<?php
/**
 * Script de traitement IA pour COMPTAFLOW - Expert SYSCOHADA Côte d'Ivoire
 * Version autonome sans Laravel
 */

header('Content-Type: application/json');

// --- CONFIGURATION ---
$api_key = $_ENV['GEMINI_API_KEY'] ?? "AIzaSyDuwMm9cdo_vTqBe9j3degykq4rL-kOKVU";

// Liste des modèles à essayer dans l'ordre (du plus rapide au plus puissant)
$models = [
    "gemini-flash-latest",    // Premier choix - le plus rapide
    "gemini-2.5-flash",      // Deuxième choix - rapide et économique
    "gemini-1.5-flash",      // Troisième choix - alternative rapide
    "gemini-1.5-pro",        // Quatrième choix - plus puissant
    "gemini-pro"             // Cinquième choix - le plus puissant
];

// Fonction pour essayer un modèle et passer au suivant si quota dépassé
function tryGeminiModel($model, $api_key, $payload) {
    $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$api_key}";
    
    error_log("Tentative avec le modèle: $model");
    
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

    error_log("Modèle $model - HTTP: $http_code, Error: " . ($error ?: 'None'));

    if ($http_code === 200) {
        $result = json_decode($response, true);
        if (isset($result['candidates'][0]['content']['parts'][0]['text'])) {
            $json_comptable = $result['candidates'][0]['content']['parts'][0]['text'];
            error_log("Succès avec le modèle: $model");
            return ['success' => true, 'data' => $json_comptable, 'model_used' => $model];
        }
    } elseif ($http_code == 429) {
        error_log("Quota dépassé pour le modèle: $model");
        return ['success' => false, 'error' => 'QUOTA_EXCEEDED', 'model' => $model];
    } else {
        error_log("Erreur avec le modèle $model: $http_code - $error");
        return ['success' => false, 'error' => 'HTTP_ERROR', 'http_code' => $http_code, 'model' => $model];
    }
    
    return ['success' => false, 'error' => 'UNKNOWN_ERROR', 'model' => $model];
}

// Fonction de mapping SYSCOHADA CI vers comptes 8 chiffres
function mapCompteSyscohada($compte) {
    $mapping = [
        // Classe 1 - Comptes de capitaux
        '101' => '10110000', '106' => '10610000', '109' => '10910000',
        '12' => '12010000', '13' => '13010000', '14' => '14010000', '16' => '16010000',
        
        // Classe 2 - Comptes d'immobilisations
        '21' => '21010000', '22' => '22010000', '23' => '23010000', '24' => '24010000',
        '26' => '26010000', '27' => '27010000', '28' => '28010000',
        
        // Classe 3 - Comptes de stocks
        '31' => '31010000', '32' => '32010000', '33' => '33010000', '34' => '34010000',
        '35' => '35010000', '36' => '36010000', '37' => '37010000', '38' => '38010000',
        '39' => '39010000',
        
        // Classe 4 - Comptes de tiers
        '401' => '40110000', '401000' => '40110000', '4011' => '40110000',
        '403' => '40310000', '404' => '40410000', '405' => '40510000', '408' => '40810000',
        '411' => '41110000', '4111' => '41110000', '413' => '41310000', '415' => '41510000',
        '416' => '41610000', '418' => '41810000',
        '421' => '42110000', '422' => '42210000', '425' => '42510000', '427' => '42710000', '428' => '42810000',
        '431' => '43110000', '437' => '43710000', '438' => '43810000',
        '441' => '44110000', '442' => '44210000', '443' => '44310000', '444' => '44410000',
        '445' => '44510000', '4452' => '44521000', '4455' => '44551000', '4456' => '44561000', '4457' => '44571000',
        '447' => '44710000',
        '451' => '45110000', '455' => '45510000', '456' => '45610000', '457' => '45710000', '458' => '45810000',
        '462' => '46210000', '464' => '46410000', '465' => '46510000', '467' => '46710000', '468' => '46810000',
        '471' => '47110000', '476' => '47610000', '477' => '47710000',
        '481' => '48110000', '486' => '48610000', '487' => '48710000',
        
        // Classe 5 - Comptes de trésorerie
        '501' => '50110000', '502' => '50210000', '503' => '50310000', '504' => '50410000', '505' => '50510000', '506' => '50610000', '508' => '50810000',
        '51' => '51010000',
        '521' => '52110000', '522' => '52210000', '523' => '52310000',
        '531' => '53110000', '532' => '53210000',
        '541' => '54110000', '542' => '54210000', '543' => '54310000', '544' => '54410000', '545' => '54510000', '546' => '54610000', '547' => '54710000', '548' => '54810000', '549' => '54910000',
        '571' => '57110000', '572' => '57210000', '573' => '57310000', '574' => '57410000', '575' => '57510000', '576' => '57610000', '577' => '57710000', '578' => '57810000', '579' => '57910000',
        
        // Classe 6 - Comptes de charges
        '601' => '60110000', '602' => '60210000', '603' => '60310000', '604' => '60410000', '605' => '60510000', '606' => '60610000', '607' => '60710000', '608' => '60810000', '609' => '60910000',
        '61' => '61010000',
        '611' => '61110000', '612' => '61210000', '613' => '61310000', '614' => '61410000', '615' => '61510000', '616' => '61610000', '617' => '61710000', '618' => '61810000', '619' => '61910000',
        '621' => '62110000', '622' => '62210000', '623' => '62310000', '624' => '62410000', '625' => '62510000', '626' => '62610000', '627' => '62710000', '628' => '62810000', '629' => '62910000',
        '631' => '63110000', '632' => '63210000', '633' => '63310000', '634' => '63410000', '635' => '63510000', '635000' => '63510000', '636' => '63610000', '637' => '63710000', '638' => '63810000', '639' => '63910000',
        '641' => '64110000', '642' => '64210000', '643' => '64310000', '644' => '64410000', '645' => '64510000', '646' => '64610000', '647' => '64710000', '648' => '64810000',
        '65' => '65010000',
        '651' => '65110000', '652' => '65210000', '653' => '65310000', '654' => '65410000', '655' => '65510000', '656' => '65610000', '657' => '65710000', '658' => '65810000', '659' => '65910000',
        '66' => '66010000',
        '661' => '66110000', '662' => '66210000', '663' => '66310000', '664' => '66410000', '665' => '66510000', '666' => '66610000', '667' => '66710000', '668' => '66810000',
        '67' => '67010000',
        '671' => '67110000', '672' => '67210000', '673' => '67310000', '674' => '67410000', '675' => '67510000', '676' => '67610000', '677' => '67710000', '678' => '67810000', '679' => '67910000',
        '68' => '68010000',
        '681' => '68110000', '682' => '68210000', '683' => '68310000', '684' => '68410000', '685' => '68510000', '686' => '68610000', '687' => '68710000', '688' => '68810000', '689' => '68910000',
        
        // Classe 7 - Comptes de produits
        '701' => '70110000', '702' => '70210000', '703' => '70310000', '704' => '70410000', '705' => '70510000', '706' => '70610000', '707' => '70710000', '708' => '70810000', '709' => '70910000',
        '71' => '71010000',
        '711' => '71110000', '712' => '71210000', '713' => '71310000', '714' => '71410000', '715' => '71510000', '716' => '71610000', '717' => '71710000', '718' => '71810000', '719' => '71910000',
        '72' => '72010000',
        '721' => '72110000', '722' => '72210000', '723' => '72310000', '724' => '72410000', '725' => '72510000', '726' => '72610000', '727' => '72710000', '728' => '72810000', '729' => '72910000',
        '73' => '73010000',
        '731' => '73110000', '732' => '73210000', '733' => '73310000', '734' => '73410000', '735' => '73510000', '736' => '73610000', '737' => '73710000', '738' => '73810000', '739' => '73910000',
        '74' => '74010000',
        '741' => '74110000', '742' => '74210000', '743' => '74310000', '744' => '74410000', '745' => '74510000', '746' => '74610000', '747' => '74710000', '748' => '74810000', '749' => '74910000',
        '75' => '75010000',
        '751' => '75110000', '752' => '75210000', '753' => '75310000', '754' => '75410000', '755' => '75510000', '756' => '75610000', '757' => '75710000', '758' => '75810000', '759' => '75910000',
        '76' => '76010000',
        '761' => '76110000', '762' => '76210000', '763' => '76310000', '764' => '76410000', '765' => '76510000', '766' => '76610000', '767' => '76710000', '768' => '76810000',
        '77' => '77010000',
        '771' => '77110000', '772' => '77210000', '773' => '77310000', '774' => '77410000', '775' => '77510000', '776' => '77610000', '777' => '77710000', '778' => '77810000', '779' => '77910000',
        '78' => '78010000',
        '781' => '78110000', '782' => '78210000', '783' => '78310000', '784' => '78410000', '785' => '78510000', '786' => '78610000', '787' => '78710000', '788' => '78810000', '789' => '78910000',
        
        // Classe 8 - Comptes de résultats
        '801' => '80110000', '802' => '80210000', '803' => '80310000', '804' => '80410000', '805' => '80510000', '806' => '80610000', '807' => '80710000', '808' => '80810000',
        '81' => '81010000',
        '811' => '81110000', '812' => '81210000', '813' => '81310000', '814' => '81410000', '815' => '81510000', '816' => '81610000', '817' => '81710000', '818' => '81810000',
        '82' => '82010000',
        '821' => '82110000', '822' => '82210000', '823' => '82310000', '824' => '82410000', '825' => '82510000', '826' => '82610000', '827' => '82710000', '828' => '82810000',
        '83' => '83010000',
        '831' => '83110000', '832' => '83210000', '833' => '83310000', '834' => '83410000', '835' => '83510000', '836' => '83610000', '837' => '83710000', '838' => '83810000',
        '84' => '84010000',
        '841' => '84110000', '842' => '84210000', '843' => '84310000', '844' => '84410000', '845' => '84510000', '846' => '84610000', '847' => '84710000', '848' => '84810000',
        '85' => '85010000',
        '851' => '85110000', '852' => '85210000', '853' => '85310000', '854' => '85410000', '855' => '85510000', '856' => '85610000', '857' => '85710000', '858' => '85810000',
        '86' => '86010000',
        '861' => '86110000', '862' => '86210000', '863' => '86310000', '864' => '86410000', '865' => '86510000', '866' => '86610000', '867' => '86710000', '868' => '86810000',
        '87' => '87010000',
        '871' => '87110000', '872' => '87210000', '873' => '87310000', '874' => '87410000', '875' => '87510000', '876' => '87610000', '877' => '87710000', '878' => '87810000',
        '88' => '88010000',
        '881' => '88110000', '882' => '88210000', '883' => '88310000', '884' => '88410000', '885' => '88510000', '886' => '88610000', '887' => '88710000', '888' => '88810000',
        '891' => '89110000', '892' => '89210000', '893' => '89310000', '894' => '89410000', '895' => '89510000', '896' => '89610000', '897' => '89710000', '898' => '89810000'
    ];
    
    // Si le compte est déjà à 8 chiffres, le retourner
    if (strlen($compte) >= 8) {
        return $compte;
    }
    
    // Chercher une correspondance exacte
    if (isset($mapping[$compte])) {
        return $mapping[$compte];
    }
    
    // Chercher par préfixe en ordre décroissant (6, 5, 4, 3 chiffres)
    for ($length = min(6, strlen($compte)); $length >= 3; $length--) {
        $prefixe = substr($compte, 0, $length);
        if (isset($mapping[$prefixe])) {
            return $mapping[$prefixe];
        }
    }
    
    // Si pas de correspondance, utiliser la numérotation croissante
    global $compteurs;
    $prefixe = substr($compte, 0, 4);
    
    // Si le préfixe existe dans les compteurs, incrémenter
    if (isset($compteurs[$prefixe])) {
        $compteurs[$prefixe]++;
        $numero = $compteurs[$prefixe];
        return $prefixe . str_pad($numero, 4, '0', STR_PAD_LEFT) . '00';
    }
    
    // Sinon, commencer à 10000
    return $prefixe . '10000';
}

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
        "maxOutputTokens" => 4000,
        "response_mime_type" => "application/json"
    ]
];

// 4. Appel API Gemini avec fallback automatique entre modèles
error_log("Appel API Gemini avec fallback automatique entre modèles...");

$api_success = false;
$json_comptable = null;
$model_used = null;
$models_tried = [];

// Essayer chaque modèle dans l'ordre
foreach ($models as $model) {
    $models_tried[] = $model;
    $result = tryGeminiModel($model, $api_key, $payload);
    
    if ($result['success']) {
        $json_comptable = $result['data'];
        $model_used = $result['model_used'];
        $api_success = true;
        break;
    } elseif ($result['error'] === 'QUOTA_EXCEEDED') {
        // Passer au modèle suivant
        error_log("Quota dépassé pour $model, passage au modèle suivant...");
        continue;
    } else {
        // Erreur autre que quota, essayer le modèle suivant
        error_log("Erreur avec $model, passage au modèle suivant...");
        continue;
    }
}

// Si tous les modèles ont échoué, utiliser le fallback local
if (!$api_success) {
    error_log("Tous les modèles Gemini ont échoué. Modèles essayés: " . implode(', ', $models_tried));
    error_log("Utilisation du fallback local...");
    
    $response_data = [
        "type_document" => "Facture",
        "tiers" => "Fournisseur (à compléter)",
        "date" => date("Y-m-d"),
        "reference" => "FACT-" . date("YmdHis"),
        "montant_ht" => 10000,
        "montant_tva" => 0,
        "montant_ttc" => 10000,
        "ecriture" => [
            ["compte" => "601000", "intitule" => "Achats marchandises", "debit" => 10000, "credit" => 0],
            ["compte" => "401000", "intitule" => "Fournisseurs", "debit" => 0, "credit" => 10000]
        ],
        "fallback" => true,
        "models_tried" => $models_tried,
        "message" => "Tous les modèles Gemini indisponibles. Données générées localement."
    ];
    
    echo json_encode($response_data);
    exit;
}

// Si un modèle a fonctionné, traiter la réponse
if ($json_comptable) {
    error_log("Traitement de la réponse du modèle: $model_used");
    
    error_log("Raw response from $model_used: " . substr($json_comptable, 0, 500));
    
    // Nettoyage du JSON
    $json_comptable = preg_replace('/```json\s*/', '', $json_comptable);
    $json_comptable = preg_replace('/```\s*$/', '', $json_comptable);
    $json_comptable = trim($json_comptable);
    
    // Validation et retour
    $data = json_decode($json_comptable, true);
    if (json_last_error() === JSON_ERROR_NONE) {
        error_log("JSON valide reçu de $model_used");
        
        // Appliquer le mapping SYSCOHADA vers comptes 8 chiffres
        if (isset($data['ecriture']) && is_array($data['ecriture'])) {
            foreach ($data['ecriture'] as &$ligne) {
                if (isset($ligne['compte'])) {
                    $ligne['compte'] = mapCompteSyscohada($ligne['compte']);
                }
            }
        }
        
        // Ajouter une information sur la TVA pour l'interface
        $hasVATAmount = isset($data['montant_tva']) && $data['montant_tva'] > 0;
        $hasVATLine = false;
        
        // DEBUG : Logs PHP
        error_log("=== ANALYSE TVA GEMINI ($model_used) ===");
        error_log("montant_tva brut: " . (isset($data['montant_tva']) ? $data['montant_tva'] : 'NON'));
        error_log("hasVATAmount: " . ($hasVATAmount ? 'TRUE' : 'FALSE'));
        
        // Vérifier s'il y a une ligne TVA dans les écritures
        if (isset($data['ecriture']) && is_array($data['ecriture'])) {
            foreach ($data['ecriture'] as $index => $ligne) {
                $compte = $ligne['compte'] ?? '';
                $intitule = $ligne['intitule'] ?? '';
                $isVATLine = strpos($compte, '445') === 0 || 
                           strpos($compte, 'TVA') !== false ||
                           strpos(strtolower($intitule), 'tva') !== false;
                
                error_log("Ligne $index: compte=$compte, intitule=$intitule, isVATLine=" . ($isVATLine ? 'TRUE' : 'FALSE'));
                
                if ($isVATLine) {
                    $hasVATLine = true;
                }
            }
        }
        
        error_log("hasVATLine: " . ($hasVATLine ? 'TRUE' : 'FALSE'));
        
        // La TVA est présente si montant_tva > 0 ET/OU ligne TVA présente
        $data['hasVAT'] = $hasVATAmount || $hasVATLine;
        
        error_log("hasVAT final: " . ($data['hasVAT'] ? 'TRUE' : 'FALSE'));
        error_log("========================");
        
        // Si pas de TVA détectée mais qu'une ligne TVA existe, la supprimer
        if (!$data['hasVAT'] && $hasVATLine && isset($data['ecriture'])) {
            error_log("Suppression des lignes TVA car hasVAT=FALSE");
            $data['ecriture'] = array_filter($data['ecriture'], function($ligne) {
                $compte = $ligne['compte'] ?? '';
                $intitule = $ligne['intitule'] ?? '';
                return !(strpos($compte, '445') === 0 || 
                       strpos($compte, 'TVA') !== false ||
                       strpos(strtolower($intitule), 'tva') !== false);
            });
            // Réindexer le tableau
            $data['ecriture'] = array_values($data['ecriture']);
            error_log("Nombre d'écritures après suppression: " . count($data['ecriture']));
        }
        
        // Ajouter des infos sur le modèle utilisé
        $data['model_used'] = $model_used;
        $data['models_tried'] = $models_tried;
        
        echo json_encode($data);
        exit;
    } else {
        error_log("JSON invalide de $model_used: " . json_last_error_msg());
        echo json_encode([
            'error' => 'JSON invalide généré par l\'IA',
            'model_used' => $model_used,
            'raw_response' => $json_comptable,
            'json_error' => json_last_error_msg()
        ]);
        exit;
    }
}

echo json_encode([
    'error' => 'Erreur de traitement',
    'models_tried' => $models_tried,
    'message' => 'Impossible de traiter la facture avec aucun modèle.'
]);
exit;
?>
