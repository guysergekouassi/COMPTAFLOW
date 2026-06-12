<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Models\PlanComptable;
use App\Models\Company;

$companyId = 2; // La company avec 30 comptes
$company = Company::find($companyId);
$accountDigits = $company->account_digits ?? 8;

echo "Company: {$company->name}" . PHP_EOL;
echo "account_digits: $accountDigits" . PHP_EOL . PHP_EOL;

// Simuler exactement ce que fait le code
$accountDetails = PlanComptable::where('company_id', $companyId)
    ->select('id', 'numero_de_compte', 'intitule', 'numero_original')
    ->get()
    ->keyBy(fn($item) => strtoupper(trim($item->numero_de_compte)));

$accountDetailsByOriginal = PlanComptable::where('company_id', $companyId)
    ->whereNotNull('numero_original')
    ->where('numero_original', '!=', '')
    ->select('id', 'numero_de_compte', 'intitule', 'numero_original')
    ->get()
    ->keyBy(fn($item) => strtoupper(trim($item->numero_original)));

$accountMapping = [];
PlanComptable::where('company_id', $companyId)
    ->whereNotNull('numero_original')
    ->where('numero_original', '!=', '')
    ->select('numero_de_compte', 'numero_original')
    ->chunk(100, function($accounts) use (&$accountMapping) {
        foreach($accounts as $acc) {
            $accountMapping[strtoupper(trim($acc->numero_original))] = trim($acc->numero_de_compte);
        }
    });

// Simuler le traitement de quelques comptes du fichier (simulés avec les numéros existants en base)
$testRows = [
    ['numero_de_compte' => '401000', 'intitule' => 'FOURNISSEUR'],
    ['numero_de_compte' => '411000', 'intitule' => 'CLIENTS '],
    ['numero_de_compte' => '521000', 'intitule' => 'BANQUE'],
    ['numero_de_compte' => '571000', 'intitule' => 'CAISSE'],
];

function standardizeAccountNumber(string $num, int $digits): string {
    $num = preg_replace('/[^0-9]/', '', $num);
    if (empty($num)) return '';
    if (strlen($num) < $digits) {
        $num = str_pad($num, $digits, '0', STR_PAD_RIGHT);
    } elseif (strlen($num) > $digits) {
        $num = substr($num, 0, $digits);
    }
    return strtoupper($num);
}

echo "=== SIMULATION LOOKUP ===" . PHP_EOL;
foreach ($testRows as $row) {
    $originalRawValue = trim($row['numero_de_compte']);
    $rowCompte = $originalRawValue;
    
    // Standardisation
    $newCompte = standardizeAccountNumber($rowCompte, $accountDigits);
    $rowCompte = $newCompte;
    
    $upperOrigRaw = strtoupper(trim($originalRawValue));
    $upperRowCompte = strtoupper(trim($rowCompte));
    
    echo PHP_EOL . "--- Fichier: [{$originalRawValue}] → Standardisé: [{$rowCompte}] | Libellé: [{$row['intitule']}] ---" . PHP_EOL;
    echo "  accountDetailsByOriginal['{$upperOrigRaw}']: " . (isset($accountDetailsByOriginal[$upperOrigRaw]) ? "TROUVÉ → " . $accountDetailsByOriginal[$upperOrigRaw]->intitule : "PAS TROUVÉ") . PHP_EOL;
    echo "  accountMapping['{$upperOrigRaw}']: " . (isset($accountMapping[$upperOrigRaw]) ? "TROUVÉ → " . $accountMapping[$upperOrigRaw] : "PAS TROUVÉ") . PHP_EOL;
    echo "  accountDetails['{$upperRowCompte}']: " . ($accountDetails->get($upperRowCompte) ? "TROUVÉ → " . $accountDetails->get($upperRowCompte)->intitule : "PAS TROUVÉ") . PHP_EOL;
    
    // Comparaison libellé
    $existing = $accountDetailsByOriginal[$upperOrigRaw] ?? $accountDetails->get($upperRowCompte);
    if ($existing) {
        $currentLabelUpper = trim(strtoupper($row['intitule']));
        $existingLabelUpper = trim(strtoupper($existing->intitule));
        $match = ($currentLabelUpper === $existingLabelUpper);
        echo "  LIBELLÉ fichier: [{$currentLabelUpper}] vs DB: [{$existingLabelUpper}] → " . ($match ? "MATCH = DOUBLON ✅" : "DIFFÉRENT → COLLISION (génère nouveau numéro) ❌") . PHP_EOL;
    }
    
    // Vérif erreur longueur
    $lenOk = strlen($rowCompte) == $accountDigits;
    echo "  Longueur [{$rowCompte}] = " . strlen($rowCompte) . " vs attendu $accountDigits → " . ($lenOk ? "OK" : "ERREUR → bloc doublon sauté !") . PHP_EOL;
}

echo PHP_EOL . "=== KEYS accountDetails (10 premières) ===" . PHP_EOL;
foreach ($accountDetails->keys()->take(10) as $k) {
    echo "  [$k]" . PHP_EOL;
}

echo PHP_EOL . "=== KEYS accountDetailsByOriginal (10 premières) ===" . PHP_EOL;
foreach ($accountDetailsByOriginal->keys()->take(10) as $k) {
    echo "  [$k]" . PHP_EOL;
}
