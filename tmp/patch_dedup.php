<?php
$filePath = __DIR__ . '/../app/Http/Controllers/Admin/AdminConfigController.php';
$content = file_get_contents($filePath);

// Find the dedup block by unique markers
$oldSignatureBlock = '                         // 2.2 : Signature de déduplication (Fallback)
                          $normRef = trim($rowMapped[\'reference\'] ?? \'\');
                          $normLib = trim($rowMapped[\'libelle\'] ?? \'\');
                          $normDebit = str_replace([\',\', \' \'], [\'.\', \'.\'], $rowMapped[\'debit\'] ?? \'0\');
                          $normCredit = str_replace([\',\', \' \'], [\'.\', \'.\'], $rowMapped[\'credit\'] ?? \'0\');
                          // IMPORTANT: Le tiers doit être inclus dans la signature pour éviter de considérer
                          // comme doublons deux lignes identiques SAUF le tiers (ex: 401025 vs 401085)
                          $normTiers = strtoupper(trim($rowMapped[\'tiers\'] ?? \'\'));
                          
                          $sigParts = [
                              trim($rowMapped[\'jour\'] ?? \'\'),
                              trim($rowJournal),
                              trim($rowCompte),
                              $normTiers,
                              (string)(float)$normDebit, 
                              (string)(float)$normCredit
                          ];

                         // Logique stricte : Si Ref existe, on l\'utilise. Sinon Libellé.
                          if (!empty($normRef)) {
                              $sigParts[] = $normRef;
                          } elseif (!empty($normLib)) {
                              // Si pas de ref, inclure le libellé pour éviter de fusionner des lignes distinctes
                              $sigParts[] = $normLib;
                          }

                          $signature = md5(implode(\'|\', $sigParts));

                          if (isset($deduplicationBuffer[$signature])) {
                              $duplicateCount++;
                              $report[\'deduplicated\']++;
                              continue;
                          }
                          $deduplicationBuffer[$signature] = true;
                     }';

$newSignatureBlock = '                         // 2.2 : Signature de déduplication STRICTE (anti-faux-positifs)
                          // Pour être un vrai doublon : identique sur date, journal, compte, tiers,
                          // montants, référence ET libellé ET n° saisie.
                          $normRef    = trim($rowMapped[\'reference\'] ?? \'\');
                          $normLib    = trim($rowMapped[\'libelle\'] ?? \'\');
                          $normDebit  = str_replace([\',\', \' \'], [\'.\', \'.\'], $rowMapped[\'debit\'] ?? \'0\');
                          $normCredit = str_replace([\',\', \' \'], [\'.\', \'.\'], $rowMapped[\'credit\'] ?? \'0\');
                          $normTiers  = strtoupper(trim($rowMapped[\'tiers\'] ?? \'\'));
                          $normNSaisie = trim($rowMapped[\'n_saisie\'] ?? \'\');

                          $sigParts = [
                              trim($rowMapped[\'jour\'] ?? \'\'),
                              trim($rowJournal),
                              trim($rowCompte),
                              $normTiers,
                              (string)(float)$normDebit,
                              (string)(float)$normCredit,
                              $normRef,      // référence TOUJOURS incluse
                              $normLib,      // libellé TOUJOURS inclus
                              $normNSaisie,  // n° saisie d\'origine
                          ];

                          $signature = md5(implode(\'|\', $sigParts));

                          if (isset($deduplicationBuffer[$signature])) {
                              $duplicateCount++;
                              $report[\'deduplicated\']++;
                              continue;
                          }
                          $deduplicationBuffer[$signature] = true;
                     }';

// Also fix the hidden-A detection to only check unmapped columns
$oldHiddenA = '                          $isHiddenA = false;
                          foreach ($rowOrig as $cellVal) {
                              $v = strtoupper(trim($cellVal ?? \'\'));
                              if ($v === \'A\' || $v === \'ANALYTIQUE\') {
                                  $isHiddenA = true;
                                  break;
                              }
                          }';

$newHiddenA = '                          $isHiddenA = false;
                          $mappedColIndexes = array_filter(array_values($mapping), fn($v) => is_numeric($v));
                          foreach ($rowOrig as $colIdx => $cellVal) {
                              if (in_array($colIdx, $mappedColIndexes)) continue; // ignorer colonnes mappées
                              $v = strtoupper(trim($cellVal ?? \'\'));
                              if ($v === \'A\' || $v === \'ANALYTIQUE\') {
                                  $isHiddenA = true;
                                  break;
                              }
                          }';

$countA = 0;
$countSig = 0;

$newContent = str_replace($oldHiddenA, $newHiddenA, $content, $countA);
$newContent = str_replace($oldSignatureBlock, $newSignatureBlock, $newContent, $countSig);

echo "HiddenA replacements: $countA\n";
echo "Signature replacements: $countSig\n";

if ($countA > 0 || $countSig > 0) {
    file_put_contents($filePath, $newContent);
    echo "SUCCESS: File patched.\n";
} else {
    echo "ERROR: Pattern not found. Check manually.\n";
    // Debug: show context around line 3200
    $lines = explode("\n", $content);
    for ($i = 3195; $i <= 3255; $i++) {
        echo "$i: " . ($lines[$i] ?? '') . "\n";
    }
}
