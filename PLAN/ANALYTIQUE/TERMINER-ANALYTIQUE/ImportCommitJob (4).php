<?php

namespace App\Jobs;

use App\Models\ImportStaging;
use App\Models\EcritureComptable;
use App\Models\PlanComptable;
use App\Models\PlanTiers;
use App\Models\CodeJournal;
use App\Models\Company;
use App\Models\ExerciceComptable;
use App\Models\JournalSaisi;
use App\Models\SectionAnalytique;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

class ImportCommitJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Le job peut tourner jusqu'à 2 heures (50 000+ lignes).
     */
    public int $timeout = 7200;
    public int $tries   = 1;

    public function __construct(
        public readonly int $importId,
        public readonly int $userId
    ) {}

    public function handle(): void
    {
        set_time_limit(0);
        ini_set('memory_limit', '2G');

        $import = ImportStaging::findOrFail($this->importId);
        $user   = User::findOrFail($this->userId);

        $targetCompanyId = $import->company_id ?: $user->company_id;

        // Force correct Tenant context in Queue/CLI environments
        \Illuminate\Support\Facades\Auth::setUser($user);
        \Illuminate\Support\Facades\Session::put('current_company_id', $targetCompanyId);
        $user->company_id = $targetCompanyId; // memory override fallback

        $targetCompany   = Company::find($targetCompanyId);

        $this->updateProgress($import, 0, 'Initialisation…');

        // ═══════════════════════════════════════════════════════
        // AIGUILLAGE PAR TYPE D'IMPORT
        // initial / tiers / journals → handleReferentialImport()
        // courant (écritures) → logique existante ci-dessous
        // ═══════════════════════════════════════════════════════
        if (in_array($import->type, ['initial', 'tiers', 'journals'])) {
            $this->handleReferentialImport($import, $user, $targetCompanyId, $targetCompany);
            return;
        }

        // ─── Suite : Écritures comptables (courant) uniquement ──────────────
        $mapping     = $import->mapping;
        $headerIndex = $mapping['_header_index'] ?? 0;

        // Filtrage des lignes vides
        $data = array_filter(
            array_slice($import->raw_data, $headerIndex + 1, null, true),
            function ($row) use ($mapping) {
                foreach ($mapping as $field => $index) {
                    if ($field !== '_header_index' && $index !== null && $index !== '' && !empty(trim($row[$index] ?? ''))) {
                        return true;
                    }
                }
                return false;
            }
        );

        $totalRows     = count($data);
        $exercice      = ExerciceComptable::find($import->exercice_id);
        $accountDigits = $targetCompany->account_digits ?? 8;
        $journalDigits = $targetCompany->journal_code_digits ?? 4;
        $tierDigits    = $targetCompany->tier_digits ?? 8;
        $journalLimit  = 10;
        $ranNumLength  = max(1, $journalDigits - 3);

        // ─────────────────────────────────────────────
        // CHARGEMENT EN MÉMOIRE (élimine les N+1 queries)
        // ─────────────────────────────────────────────
        $planComptableIds = PlanComptable::where('company_id', $targetCompanyId)
            ->pluck('id', 'numero_de_compte')->toArray();
        $planComptableIds = array_change_key_case($planComptableIds, CASE_UPPER);

        $planComptableOriginalIds = PlanComptable::where('company_id', $targetCompanyId)
            ->whereNotNull('numero_original')->where('numero_original', '!=', '')
            ->pluck('id', 'numero_original')->toArray();
        $planComptableOriginalIds = array_change_key_case($planComptableOriginalIds, CASE_UPPER);

        $planTiersIds = PlanTiers::where('company_id', $targetCompanyId)
            ->pluck('id', 'numero_de_tiers')->toArray();
        $planTiersIds = array_change_key_case($planTiersIds, CASE_UPPER);

        $planTiersOriginalIds = PlanTiers::where('company_id', $targetCompanyId)
            ->whereNotNull('numero_original')->where('numero_original', '!=', '')
            ->pluck('id', 'numero_original')->toArray();
        $planTiersOriginalIds = array_change_key_case($planTiersOriginalIds, CASE_UPPER);

        $existingJournals = CodeJournal::where('company_id', $targetCompanyId)
            ->pluck('id', 'code_journal')->toArray();
        $existingJournals = array_change_key_case($existingJournals, CASE_UPPER);

        $journalTypes = CodeJournal::where('company_id', $targetCompanyId)
            ->pluck('type', 'id')->toArray();

        $existingJournalsOriginal = CodeJournal::where('company_id', $targetCompanyId)
            ->whereNotNull('numero_original')->where('numero_original', '!=', '')
            ->pluck('id', 'numero_original')->toArray();
        $existingJournalsOriginal = array_change_key_case($existingJournalsOriginal, CASE_UPPER);

        $journalIdToCode = CodeJournal::where('company_id', $targetCompanyId)
            ->pluck('code_journal', 'id')->toArray();

        // Sections analytiques disponibles, indexées par code (majuscules)
        $sectionsByCode = SectionAnalytique::where('company_id', $targetCompanyId)
            ->pluck('id', 'code')->toArray();
        $sectionsByCode = array_change_key_case($sectionsByCode, CASE_UPPER);

        // ─────────────────────────────────────────────
        // OPTIMISATION CLÉ : Pré-calcul des compteurs ECR/RAN UNE SEULE FOIS
        // (élimine des milliers de requêtes en boucle)
        // ─────────────────────────────────────────────
        $baseEcrCounter = (int)(EcritureComptable::where('company_id', $targetCompanyId)
            ->where('exercices_comptables_id', $import->exercice_id)
            ->where('n_saisie', 'like', 'ECR_%')
            ->max(DB::raw('CAST(SUBSTRING(n_saisie, 5) AS UNSIGNED)')) ?? 0);

        $baseRanCounter = (int)(EcritureComptable::where('company_id', $targetCompanyId)
            ->where('exercices_comptables_id', $import->exercice_id)
            ->where('n_saisie', 'like', 'RAN%')
            ->max(DB::raw('CAST(SUBSTRING(n_saisie, 4) AS UNSIGNED)')) ?? 0);

        // ─────────────────────────────────────────────
        // VARIABLES DE TRAITEMENT
        // ─────────────────────────────────────────────
        $report = [
            'status'       => 'success',
            'processed_g'  => 0,
            'filtered_a'   => 0,
            'deduplicated' => 0,
            'total_debit'  => 0,
            'total_credit' => 0,
            'new_accounts' => 0,
            'new_tiers'    => 0,
            'errors'       => [],
        ];

        $importedCount  = 0;
        $skippedCount   = 0;
        $errors         = [];
        $groupBalances  = [];
        $ecrMapping     = [];
        $jsCache        = [];
        $batchEcritures = [];

        $parseAmount = function ($val) {
            if (empty($val)) return 0.0;
            $val = trim((string) $val);
            $val = str_replace([' ', "\xC2\xA0"], '', $val);
            $isNegative = str_starts_with($val, '-');
            $val = ltrim($val, '-');
            if (strpos($val, ',') !== false && strpos($val, '.') !== false) {
                $val = (strrpos($val, ',') > strrpos($val, '.'))
                    ? str_replace(['.', ','], ['', '.'], $val)
                    : str_replace(',', '', $val);
            } else {
                $val = str_replace(',', '.', $val);
            }
            $val = preg_replace('/[^0-9.]/', '', $val);
            return $isNegative ? -(float) $val : (float) $val;
        };

        $msg_type = 'écritures';

        // --- DÉTERMINATION GLOBALE DYNAMIQUE DE LA STRATÉGIE DE GROUPAGE ---
        $groupingKeyStrategy = 'n_saisie'; // default
        
        $nSaisieCol = $mapping['n_saisie'] ?? null;
        $referenceCol = $mapping['reference'] ?? null;
        $jourCol = $mapping['jour'] ?? null;
        $journalCol = $mapping['journal'] ?? null;
        $debitCol = $mapping['debit'] ?? null;
        $creditCol = $mapping['credit'] ?? null;
        
        $hasNSaisie = ($nSaisieCol !== null && $nSaisieCol !== '' && $nSaisieCol !== 'AUTO');
        $hasReference = ($referenceCol !== null && $referenceCol !== '' && $referenceCol !== 'AUTO');
        
        if ($hasNSaisie && $hasReference) {
            $nSaisieValues = [];
            $referenceValues = [];
            $groupsNSaisie = [];
            $groupsReference = [];
            
            $lastJourTemp = null;
            $lastJournalTemp = null;
            $lastNSaisieTemp = null;
            $lastReferenceTemp = null;

            foreach ($data as $rowOrigTemp) {
                // S'assurer que le mapping est reproduit de la même façon que dans la boucle principale
                $rowMappedTemp = [];
                foreach ($mapping as $fieldTemp => $colIndexTemp) {
                    if ($fieldTemp === '_header_index') continue;
                    if (isset($rowOrigTemp[$fieldTemp]) && $rowOrigTemp[$fieldTemp] !== null && $rowOrigTemp[$fieldTemp] !== '') {
                        $rowMappedTemp[$fieldTemp] = $rowOrigTemp[$fieldTemp];
                    } elseif (is_string($colIndexTemp) && str_starts_with($colIndexTemp, 'FIXED:')) {
                        $rowMappedTemp[$fieldTemp] = substr($colIndexTemp, 6);
                    } else {
                        $rowMappedTemp[$fieldTemp] = $rowOrigTemp[$colIndexTemp] ?? null;
                    }
                }
                
                // Carry-over logic
                if (empty($rowMappedTemp['jour']) && $lastJourTemp !== null) {
                    $rowMappedTemp['jour'] = $lastJourTemp;
                }
                if (empty($rowMappedTemp['journal']) && $lastJournalTemp !== null) {
                    $rowMappedTemp['journal'] = $lastJournalTemp;
                }
                if (empty($rowMappedTemp['n_saisie']) && $lastNSaisieTemp !== null) {
                    $rowMappedTemp['n_saisie'] = $lastNSaisieTemp;
                }
                if (empty($rowMappedTemp['reference']) && $lastReferenceTemp !== null) {
                    $rowMappedTemp['reference'] = $lastReferenceTemp;
                }

                if (!empty($rowMappedTemp['jour'])) $lastJourTemp = $rowMappedTemp['jour'];
                if (!empty($rowMappedTemp['journal'])) $lastJournalTemp = $rowMappedTemp['journal'];
                if (!empty($rowMappedTemp['n_saisie'])) $lastNSaisieTemp = $rowMappedTemp['n_saisie'];
                if (!empty($rowMappedTemp['reference'])) $lastReferenceTemp = $rowMappedTemp['reference'];
                
                $nsVal = trim((string)($rowMappedTemp['n_saisie'] ?? ''));
                $refVal = trim((string)($rowMappedTemp['reference'] ?? ''));
                $jour = trim((string)($rowMappedTemp['jour'] ?? ''));
                $journal = trim((string)($rowMappedTemp['journal'] ?? ''));
                
                $debit = $parseAmount($rowMappedTemp['debit'] ?? 0);
                $credit = $parseAmount($rowMappedTemp['credit'] ?? 0);
                
                if ($nsVal !== '') {
                    $nSaisieValues[] = $nsVal;
                    $keyNS = $jour . '|' . $journal . '|' . strtoupper($nsVal);
                    if (!isset($groupsNSaisie[$keyNS])) {
                        $groupsNSaisie[$keyNS] = ['d' => 0.0, 'c' => 0.0];
                    }
                    $groupsNSaisie[$keyNS]['d'] += $debit;
                    $groupsNSaisie[$keyNS]['c'] += $credit;
                }
                
                if ($refVal !== '') {
                    $referenceValues[] = $refVal;
                    $keyRef = $jour . '|' . $journal . '|' . strtoupper($refVal);
                    if (!isset($groupsReference[$keyRef])) {
                        $groupsReference[$keyRef] = ['d' => 0.0, 'c' => 0.0];
                    }
                    $groupsReference[$keyRef]['d'] += $debit;
                    $groupsReference[$keyRef]['c'] += $credit;
                }
            }
            
            $uniqueNSaisie = array_unique($nSaisieValues);
            $uniqueReference = array_unique($referenceValues);
            
            $totalRowsCount = count($data);
            if ($totalRowsCount > 0) {
                $nsRatio = count($uniqueNSaisie) / $totalRowsCount;
                $refRatio = count($uniqueReference) / $totalRowsCount;
                
                if (count($uniqueNSaisie) < 10 && count($uniqueReference) >= 10 && $nsRatio < 0.05) {
                    $groupingKeyStrategy = 'reference';
                } elseif (count($uniqueReference) < 10 && count($uniqueNSaisie) >= 10 && $refRatio < 0.05) {
                    $groupingKeyStrategy = 'n_saisie';
                } else {
                    $unbalancedNS = 0;
                    foreach ($groupsNSaisie as $g) {
                        if (abs($g['d'] - $g['c']) > 0.01) {
                            $unbalancedNS++;
                        }
                    }
                    
                    $unbalancedRef = 0;
                    foreach ($groupsReference as $g) {
                        if (abs($g['d'] - $g['c']) > 0.01) {
                            $unbalancedRef++;
                        }
                    }
                    
                    if ($unbalancedRef < $unbalancedNS) {
                        $groupingKeyStrategy = 'reference';
                    } else {
                        $groupingKeyStrategy = 'n_saisie';
                    }
                }
            }
        } elseif ($hasReference) {
            $groupingKeyStrategy = 'reference';
        } else {
            $groupingKeyStrategy = 'n_saisie';
        }
        
        Log::info("COMMIT JOB EXECUTING NEW IMPORT LOGIC FOR: " . $import->id);

        $lastJour = null;
        $lastJournal = null;
        $lastNSaisie = null;
        $lastReference = null;

        $mappedRows = [];
        $errors = [];
        $rowNum = 0;
        $cachedRanNumber = null;

        // PHASE 1 : Mappage, standardisation et carry-over séquentiel
        foreach ($data as $index => $rowOrig) {
            $rowNum++;

            // Progression jusqu'à 30% pendant la phase de parsing
            if ($rowNum % 500 === 0) {
                $pct = (int) round(($rowNum / $totalRows) * 30);
                $this->updateProgress($import, $pct, "Lecture et validation ligne {$rowNum}/{$totalRows}…");
            }

            // ── Mapping ──
            $rowMapped = [];
            foreach ($mapping as $field => $colIndex) {
                if ($field === '_header_index') continue;
                if (isset($rowOrig[$field]) && $rowOrig[$field] !== null && $rowOrig[$field] !== '') {
                    $rowMapped[$field] = $rowOrig[$field];
                } elseif (is_string($colIndex) && str_starts_with($colIndex, 'FIXED:')) {
                    $rowMapped[$field] = substr($colIndex, 6);
                } else {
                    $rowMapped[$field] = $rowOrig[$colIndex] ?? null;
                }
            }

            // Carry-over logic
            if (empty($rowMapped['jour']) && $lastJour !== null) {
                $rowMapped['jour'] = $lastJour;
            }
            if (empty($rowMapped['journal']) && $lastJournal !== null) {
                $rowMapped['journal'] = $lastJournal;
            }
            if (empty($rowMapped['n_saisie']) && $lastNSaisie !== null) {
                $rowMapped['n_saisie'] = $lastNSaisie;
            }
            if (empty($rowMapped['reference']) && $lastReference !== null) {
                $rowMapped['reference'] = $lastReference;
            }

            if (!empty($rowMapped['jour'])) $lastJour = $rowMapped['jour'];
            if (!empty($rowMapped['journal'])) $lastJournal = $rowMapped['journal'];
            if (!empty($rowMapped['n_saisie'])) $lastNSaisie = $rowMapped['n_saisie'];
            if (!empty($rowMapped['reference'])) $lastReference = $rowMapped['reference'];

            $rowCompte     = $this->standardizeAccountNumber(trim($rowMapped['compte'] ?? ''), $accountDigits);
            $rowJournalRaw = trim($rowMapped['journal'] ?? '');
            $rowJournal    = $this->standardizeJournalCode($rowJournalRaw, $journalDigits);

            // ── Validations basiques ──
            if (empty($rowCompte))  { $errors[] = "L{$index}: Compte manquant."; continue; }
            if (empty($rowJournal)) { $errors[] = "L{$index}: Journal manquant."; continue; }

            $compteId  = $planComptableIds[$rowCompte]
                      ?? $planComptableOriginalIds[strtoupper(trim($rowMapped['compte'] ?? ''))]
                      ?? null;
            $journalId = $existingJournals[strtoupper($rowJournal)]
                      ?? $existingJournalsOriginal[strtoupper($rowJournalRaw)]
                      ?? null;

            if (!$compteId)  { $errors[] = "L{$index}: Compte '$rowCompte' introuvable."; continue; }
            if (!$journalId) { $errors[] = "L{$index}: Journal '$rowJournal' introuvable."; continue; }

            // Ensure the journal code is standardized to the database value if resolved
            $rowJournal = $journalIdToCode[$journalId] ?? $rowJournal;

            $tiersNum = trim($rowMapped['tiers'] ?? '');
            $tiersNumUpper = strtoupper($tiersNum);
            if (is_numeric($tiersNumUpper) && strlen($tiersNumUpper) < $tierDigits) {
                $tiersNumUpper = str_pad($tiersNumUpper, $tierDigits, '0', STR_PAD_RIGHT);
            }
            $tiersId  = !empty($tiersNum)
                ? ($planTiersIds[$tiersNumUpper] ?? $planTiersOriginalIds[strtoupper($tiersNum)] ?? null)
                : null;

            $debit  = $parseAmount($rowMapped['debit']  ?? 0);
            $credit = $parseAmount($rowMapped['credit'] ?? 0);

            if (abs($debit) < 0.01 && abs($credit) < 0.01) {
                $errors[] = "L{$index}: Montant nul."; continue;
            }

            // ── Parsing de la date ──
            $dateStr      = trim($rowMapped['jour'] ?? '');
            $dateStrClean = preg_replace('/\s+/', '', $dateStr);
            $date         = null;

            if (is_numeric($dateStrClean) && strlen($dateStrClean) === 6) {
                $d2 = (int)substr($dateStrClean, 0, 2);
                $m2 = (int)substr($dateStrClean, 2, 2);
                $y2 = (int)substr($dateStrClean, 4, 2);
                $y4 = $y2 < 70 ? 2000 + $y2 : 1900 + $y2;
                try { $date = Carbon::create($y4, $m2, $d2, 0, 0, 0); }
                catch (\Exception $e) { $errors[] = "L{$index}: Date DDMMYY invalide."; continue; }
            } elseif (is_numeric($dateStrClean) && (float)$dateStrClean > 59) {
                try { $date = Carbon::instance(ExcelDate::excelToDateTimeObject((float)$dateStrClean)); }
                catch (\Exception $e) { $errors[] = "L{$index}: Date Excel invalide."; continue; }
            } else {
                $norm = str_replace(['\\', '.', ' '], '/', trim($dateStr));
                if (preg_match('/^(\d{1,2})[\\/\-](\d{1,2})[\\/\-](\d{2})$/', $norm, $m)) {
                    $y2  = (int)$m[3];
                    $norm = sprintf('%02d/%02d/%04d', $m[1], $m[2], $y2 < 70 ? 2000 + $y2 : 1900 + $y2);
                }
                foreach (['d/m/Y', 'd/m/y', 'j/n/Y', 'Y-m-d', 'd-m-Y'] as $fmt) {
                    try {
                        $d = Carbon::createFromFormat($fmt, $norm);
                        if ($d) { $date = $d; break; }
                    } catch (\Exception $e) {}
                }
                if (!$date) {
                    try { $date = Carbon::parse($norm); }
                    catch (\Exception $e) { $errors[] = "L{$index}: Date invalide '$dateStr'."; continue; }
                }
            }

            if ($exercice && !$date->between($exercice->date_debut->startOfDay(), $exercice->date_fin->endOfDay())) {
                $errors[] = "L{$index}: Date hors exercice (" . $date->format('d/m/Y') . ")."; continue;
            }

            // Override original_n_saisie and reference for RAN (opening balance) lines
            $refValue = strtoupper(trim($rowMapped['reference'] ?? ''));
            $nsValue  = trim($rowMapped['n_saisie'] ?? '');

            if ($this->isOpeningJournal($rowJournal, $rowJournalRaw, $journalId, $journalTypes)) {
                if ($cachedRanNumber === null) {
                    $lastRealSaisie = EcritureComptable::where('company_id', $targetCompanyId)
                        ->where('n_saisie', 'like', 'RAN%')
                        ->get(['n_saisie'])
                        ->map(fn($e) => (int) substr($e->n_saisie, 3))
                        ->max() ?? 0;
                    $next = $lastRealSaisie + 1;
                    $cachedRanNumber = 'RAN' . str_pad($next, $ranNumLength, '0', STR_PAD_LEFT);
                }
                $refValue = $cachedRanNumber;
                $nsValue  = $cachedRanNumber;
            }

            // Ventilation analytique portée par la ligne elle-même (format Sage réel).
            // Plusieurs axes peuvent être ventilés en parallèle sur la MÊME ligne, chacun indépendamment
            // à 100% du montant de la ligne (même logique que le modal de saisie manuelle).
            $sectionsIdsResolues = [];
            foreach (['', '_2', '_3'] as $suffixe) {
                $codeChamp = 'section_analytique' . $suffixe;
                $sectionCode = strtoupper(trim($rowMapped[$codeChamp] ?? ''));
                if ($sectionCode === '') continue;

                $sectionIdResolved = $sectionsByCode[$sectionCode] ?? null;
                if (!$sectionIdResolved) {
                    $errors[] = "L{$index}: Section analytique '{$sectionCode}' (axe" . ($suffixe ?: ' 1') . ") introuvable. Créez-la d'abord dans Analytique > Sections.";
                    continue 2; // ligne invalide, on passe à la suivante
                }
                $sectionsIdsResolues[] = $sectionIdResolved;
            }

            // Conserver la ligne mappée validée
            $mappedRows[] = [
                'index'             => $index,
                'date'              => $date,
                'date_formatted'    => $date->format('Y-m-d'),
                'journal_id'        => $journalId,
                'journal_code'      => $rowJournal,
                'journal_code_raw'  => $rowJournalRaw,
                'reference'         => $refValue,
                'original_n_saisie' => $nsValue,
                'compte_id'         => $compteId,
                'tiers_id'          => $tiersId,
                'debit'             => $debit,
                'credit'            => $credit,
                'libelle'           => strtoupper(trim($rowMapped['libelle'] ?? 'IMPORTATION EXTERNE')),
                'plan_analytique'   => (isset($rowMapped['plan_analytique']) && $rowMapped['plan_analytique'] == 1) ? 1 : 0,
                'section_ids'       => $sectionsIdsResolues, // 0, 1, 2 ou 3 sections (une par axe ventilé)
            ];
        }

        // Si des erreurs de base existent, on s'arrête
        if (!empty($errors)) {
            $this->failWithErrors($import, $errors, $report);
            return;
        }

        $this->updateProgress($import, 40, 'Vérification de l\'équilibre des journaux et pièces…');

        // PHASE 2 : Vérification de l'équilibre par Journal
        $journalBalances = [];
        foreach ($mappedRows as $row) {
            $jCode = $row['journal_code'];
            if (!isset($journalBalances[$jCode])) {
                $journalBalances[$jCode] = ['debit' => 0.0, 'credit' => 0.0];
            }
            $journalBalances[$jCode]['debit']  += round($row['debit'], 2);
            $journalBalances[$jCode]['credit'] += round($row['credit'], 2);
        }

        foreach ($journalBalances as $jCode => $totals) {
            if (abs($totals['debit'] - $totals['credit']) > 0.01) {
                $diff = number_format(abs($totals['debit'] - $totals['credit']), 2, ',', ' ');
                $errors[] = "DÉSÉQUILIBRE JOURNAL : Le journal '{$jCode}' est déséquilibré de {$diff} (Total Débit: "
                          . number_format($totals['debit'], 2, ',', ' ')
                          . " / Total Crédit: " . number_format($totals['credit'], 2, ',', ' ') . ").";
            }
        }

        // PHASE 3 : Groupement par Journal -> Référence (ou original N° Saisie si vide) -> Période (Mois/Année)
        $groups = [];
        foreach ($mappedRows as $row) {
            $jCode   = $row['journal_code'];
            $ref     = $row['reference'];
            $origNS  = $row['original_n_saisie'];
            $date    = $row['date']; // Carbon instance
            $monthYear = $date->format('Y-m');

            $normalizedRef = $this->normalizeReferenceForGrouping($ref);
            $normalizedNS = $this->normalizeReferenceForGrouping($origNS);

            // Définir la clé : par référence si renseignée, sinon par numéro de saisie original
            $key = ($normalizedRef !== '') ? 'REF_' . $normalizedRef : 'NS_' . ($normalizedNS !== '' ? $normalizedNS : 'IMPORT');

            $groups[$jCode][$key][$monthYear][] = $row;
        }

        // PHASE 4 : Vérification de l'équilibre individuel de chaque groupe (pièce)
        $groupBalances = [];
        foreach ($groups as $jCode => $refGroups) {
            foreach ($refGroups as $key => $monthYearGroups) {
                foreach ($monthYearGroups as $monthYear => $rows) {
                    $groupDebit  = 0.0;
                    $groupCredit = 0.0;
                    foreach ($rows as $r) {
                        $groupDebit  += round($r['debit'], 2);
                        $groupCredit += round($r['credit'], 2);
                    }

                    if (abs($groupDebit - $groupCredit) > 0.01) {
                        $diff = number_format(abs($groupDebit - $groupCredit), 2, ',', ' ');
                        $refLabel = str_replace(['REF_', 'NS_'], '', $key);
                        $errorDate = $rows[0]['date']->format('d/m/Y');
                        $errors[] = "DÉSÉQUILIBRE PIÈCE : La pièce '{$refLabel}' du {$errorDate}"
                                  . " (Journal {$jCode}) est déséquilibrée de {$diff} (Débit: "
                                  . number_format($groupDebit, 2, ',', ' ') . " / Crédit: "
                                  . number_format($groupCredit, 2, ',', ' ') . ").";
                    }

                    // On enregistre les totaux pour le rapport final
                    $groupKey = $monthYear . '_' . $jCode . '_' . $key;
                    $groupBalances[$groupKey] = [
                        'debit'  => $groupDebit,
                        'credit' => $groupCredit
                    ];
                }
            }
        }

        // Si des déséquilibres de journaux ou de pièces existent, on s'arrête
        if (!empty($errors)) {
            $this->failWithErrors($import, $errors, $report);
            return;
        }

        $this->updateProgress($import, 60, 'Génération des écritures et enregistrement…');

        // PHASE 5 : Insertion finale en base de données
        DB::beginTransaction();
        try {
            $importedCount  = 0;
            $batchEcritures = [];
            $batchSize      = 2000;
            $totalGroups    = 0;
            
            // Compter le nombre total de groupes pour la progression
            foreach ($groups as $jCode => $refGroups) {
                foreach ($refGroups as $key => $monthYearGroups) {
                    $totalGroups += count($monthYearGroups);
                }
            }

            $currentGroupIndex = 0;

            foreach ($groups as $jCode => $refGroups) {
                foreach ($refGroups as $key => $monthYearGroups) {
                    foreach ($monthYearGroups as $monthYear => $rows) {
                        $currentGroupIndex++;

                        if ($currentGroupIndex % 100 === 0) {
                            $pct = 60 + (int) round(($currentGroupIndex / $totalGroups) * 30); // 60-90%
                            $this->updateProgress($import, $pct, "Enregistrement pièce {$currentGroupIndex}/{$totalGroups}…");
                        }

                        $firstRow = $rows[0];
                        $date = $firstRow['date'];
                        $journalId = $firstRow['journal_id'];

                        // ── Numérotation global ECR/RAN ──
                        $origNS = $firstRow['original_n_saisie'];
                        if (strtoupper($jCode) === 'RAN' || strtoupper($firstRow['journal_code_raw']) === 'RAN') {
                            $globalNSaisie = 'RAN' . str_pad(++$baseRanCounter, $ranNumLength, '0', STR_PAD_LEFT);
                        } else {
                            $globalNSaisie = 'ECR_' . str_pad(++$baseEcrCounter, 12, '0', STR_PAD_LEFT);
                        }

                        // ── Détermination de n_saisie_user (commun & correct) ──
                        $firstNS = trim($firstRow['original_n_saisie']);
                        $isCommon = ($firstNS !== '' && strtoupper($firstNS) !== 'IMPORT');
                        if ($isCommon) {
                            foreach ($rows as $r) {
                                if (trim($r['original_n_saisie']) !== $firstNS) {
                                    $isCommon = false;
                                    break;
                                }
                            }
                        }
                        $nSaisieUser = $isCommon ? $firstNS : null;

                        // ── Résolution JournalSaisi ──
                        $journalSaisiId = null;
                        if ($journalId) {
                            $jsCacheKey = $date->year . '_' . $date->month . '_' . ($import->exercice_id ?? '') . '_' . $journalId;
                            if (isset($jsCache[$jsCacheKey])) {
                                $journalSaisiId = $jsCache[$jsCacheKey];
                            } else {
                                $js = JournalSaisi::firstOrCreate([
                                    'annee'                     => $date->year,
                                    'mois'                      => $date->month,
                                    'exercices_comptables_id'   => $import->exercice_id ?? null,
                                    'code_journals_id'          => $journalId,
                                    'company_id'                => $targetCompanyId,
                                ], ['user_id' => $user->id]);
                                $journalSaisiId        = $js->id;
                                $jsCache[$jsCacheKey]  = $js->id;
                            }
                        }

                        // ── Ce groupe contient-il au moins une ligne avec une section analytique ? ──
                        $groupeAAnalytique = false;
                        foreach ($rows as $r) {
                            if (!empty($r['section_ids'])) { $groupeAAnalytique = true; break; }
                        }

                        if (!$groupeAAnalytique) {
                            // ── Chemin rapide (inchangé) : insertion en masse ──
                            foreach ($rows as $r) {
                                $batchEcritures[] = [
                                    'date'                      => $r['date_formatted'],
                                    'n_saisie'                  => $globalNSaisie,
                                    'n_saisie_user'             => $nSaisieUser,
                                    'reference_piece'           => ($r['reference'] !== '') ? $r['reference'] : 'IMPORT',
                                    'plan_comptable_id'         => $r['compte_id'],
                                    'plan_tiers_id'             => $r['tiers_id'],
                                    'plan_analytique'           => $r['plan_analytique'],
                                    'code_journal_id'           => $r['journal_id'],
                                    'journaux_saisis_id'        => $journalSaisiId,
                                    'description_operation'     => $r['libelle'],
                                    'debit'                     => $r['debit'],
                                    'credit'                    => $r['credit'],
                                    'exercices_comptables_id'   => $import->exercice_id ?? null,
                                    'company_id'                => $targetCompanyId,
                                    'user_id'                   => $user->id,
                                    'statut'                    => 'approved',
                                    'created_at'                => now(),
                                    'updated_at'                => now(),
                                ];

                                if (count($batchEcritures) >= $batchSize) {
                                    EcritureComptable::insert($batchEcritures);
                                    $importedCount += count($batchEcritures);
                                    $batchEcritures = [];
                                }
                            }
                        } else {
                            // ── Chemin dédié : insertion unitaire pour récupérer l'ID et ventiler immédiatement ──
                            if (!empty($batchEcritures)) {
                                EcritureComptable::insert($batchEcritures);
                                $importedCount += count($batchEcritures);
                                $batchEcritures = [];
                            }

                            foreach ($rows as $r) {
                                $ecriture = EcritureComptable::create([
                                    'date'                      => $r['date_formatted'],
                                    'n_saisie'                  => $globalNSaisie,
                                    'n_saisie_user'             => $nSaisieUser,
                                    'reference_piece'           => ($r['reference'] !== '') ? $r['reference'] : 'IMPORT',
                                    'plan_comptable_id'         => $r['compte_id'],
                                    'plan_tiers_id'             => $r['tiers_id'],
                                    'plan_analytique'           => !empty($r['section_ids']) ? 1 : $r['plan_analytique'],
                                    'code_journal_id'           => $r['journal_id'],
                                    'journaux_saisis_id'        => $journalSaisiId,
                                    'description_operation'     => $r['libelle'],
                                    'debit'                     => $r['debit'],
                                    'credit'                    => $r['credit'],
                                    'exercices_comptables_id'   => $import->exercice_id ?? null,
                                    'company_id'                => $targetCompanyId,
                                    'user_id'                   => $user->id,
                                    'statut'                    => 'approved',
                                ]);
                                $importedCount++;

                                // La ligne porte directement ses sections : une ventilation par axe, chacune
                                // à 100% du montant de la ligne (même principe que le modal manuel : chaque
                                // axe est indépendant, pas de partage du montant entre les axes).
                                foreach (($r['section_ids'] ?? []) as $sectionIdA) {
                                    $ecriture->ventilations()->create([
                                        'section_id'  => $sectionIdA,
                                        'montant'     => $r['debit'] ?: $r['credit'],
                                        'pourcentage' => 100,
                                    ]);
                                }
                            }
                        }
                    }
                }
            }

            // Insérer le reste
            if (!empty($batchEcritures)) {
                EcritureComptable::insert($batchEcritures);
                $importedCount += count($batchEcritures);
            }

            // ── Rapport Final ──
            $report['processed_g']  = $importedCount;
            $report['deduplicated'] = 0;
            $report['total_debit']  = array_sum(array_column($groupBalances, 'debit'));
            $report['total_credit'] = array_sum(array_column($groupBalances, 'credit'));

            $import->update([
                'status'    => 'committed',
                'error_log' => "Importation réussie : {$importedCount} écritures créées.",
                'metadata'  => array_merge($import->metadata ?? [], [
                    'commit_status'   => 'done',
                    'commit_progress' => 100,
                    'commit_report'   => $report,
                ]),
            ]);

            DB::commit();

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error("IMPORT_JOB [{$this->importId}]: " . $e->getMessage(), ['exception' => $e]);
            $truncatedMsg = substr($e->getMessage(), 0, 1000);
            $import->update([
                'status'    => 'error',
                'error_log' => 'Erreur système : ' . $truncatedMsg,
                'metadata'  => array_merge($import->metadata ?? [], [
                    'commit_status'   => 'error',
                    'commit_progress' => 100,
                    'commit_report'   => ['status' => 'error', 'errors' => [$truncatedMsg]],
                ]),
            ]);
        }
    }

    private function failWithErrors(ImportStaging $import, array $errors, array $report): void
    {
        $report['status'] = 'error';
        // Limiter le nombre d'erreurs stockées en metadata JSON pour éviter le dépassement de taille de la colonne text (64 Ko)
        $limit = 50;
        if (count($errors) > $limit) {
            $truncatedErrors = array_slice($errors, 0, $limit);
            $truncatedErrors[] = "... et " . (count($errors) - $limit) . " autres erreurs.";
            $report['errors'] = $truncatedErrors;
        } else {
            $report['errors'] = $errors;
        }

        $import->update([
            'status'    => 'error',
            'error_log' => substr(implode("\n", array_slice($errors, 0, 20)), 0, 1000),
            'metadata'  => array_merge($import->metadata ?? [], [
                'commit_status'   => 'error',
                'commit_progress' => 100,
                'commit_report'   => $report,
            ]),
        ]);
    }

    // ─────────────────────────────────────────────
    // Helpers (copiés depuis AdminConfigController)
    // ─────────────────────────────────────────────

    private function updateProgress(ImportStaging $import, int $pct, string $msg): void
    {
        $import->update([
            'metadata' => array_merge($import->metadata ?? [], [
                'commit_status'   => 'processing',
                'commit_progress' => $pct,
                'commit_message'  => $msg,
            ]),
        ]);
    }

    private function standardizeAccountNumber(string $num, int $digits): string
    {
        $num = preg_replace('/[^0-9]/', '', $num);
        if (empty($num)) return '';
        if (strlen($num) < $digits) {
            $num = str_pad($num, $digits, '0', STR_PAD_RIGHT);
        } elseif (strlen($num) > $digits) {
            $num = substr($num, 0, $digits);
        }
        return strtoupper($num);
    }

    private function standardizeJournalCode(string $code, int $digits): string
    {
        $code = strtoupper(trim($code));
        if (empty($code) || $code === 'AUTO') return '';
        if (strlen($code) > $digits) {
            $code = substr($code, 0, $digits);
        }
        return $code;
    }

    private function normalizeReferenceForGrouping(string $ref): string
    {
        $ref = trim($ref);
        if ($ref === '' || strtoupper($ref) === 'IMPORT') {
            return '';
        }

        // Standardisation de base : minuscules, suppression des espaces et de la ponctuation
        $normalized = mb_strtolower($ref);
        $normalized = preg_replace('/[^a-z0-9]/', '', $normalized);

        // Harmonisation des écritures récurrentes (paies, licenciements, provisions)
        if (str_starts_with($normalized, 'constpaie')) {
            return 'constpaie';
        }
        if (str_starts_with($normalized, 'constlicenci')) {
            return 'constlicenci';
        }
        if (str_starts_with($normalized, 'constprov')) {
            return 'constprov';
        }
        if (str_starts_with($normalized, 'constamort')) {
            return 'constamort';
        }

        return $normalized;
    }

    private function isOpeningJournal(string $jCode, string $jCodeRaw, ?int $journalId, array $journalTypes): bool
    {
        $jCodeUpper = strtoupper($jCode);
        $jCodeRawUpper = strtoupper($jCodeRaw);
        
        if (in_array($jCodeUpper, ['RAN', 'AN', 'REPORT', 'REPORTS', 'BILA', 'BILAN_OUV', 'OUVERTURE'])
            || in_array($jCodeRawUpper, ['RAN', 'AN', 'REPORT', 'REPORTS', 'BILA', 'BILAN_OUV', 'OUVERTURE'])
            || str_starts_with($jCodeUpper, 'RAN')
            || str_starts_with($jCodeUpper, 'AN')
            || str_starts_with($jCodeRawUpper, 'RAN')
            || str_starts_with($jCodeRawUpper, 'AN')
        ) {
            return true;
        }
        
        if ($journalId && isset($journalTypes[$journalId])) {
            $type = strtoupper(trim($journalTypes[$journalId]));
            if ($type === 'REPORT A NOUVEAU' || $type === 'SITUATION' || $type === 'A NOUVEAU') {
                return true;
            }
        }
        
        return false;
    }

    // ──────────────────────────────────────────────────────────────
    // Importation Reférentielle (initial / tiers / journals)
    // Utilise la logique de staging du controller pour générer les codes
    // et ne tente PAS de traiter ces lignes comme des écritures.
    // ──────────────────────────────────────────────────────────────
    private function handleReferentialImport(
        ImportStaging $import,
        User $user,
        int $targetCompanyId,
        Company $targetCompany
    ): void {
        DB::beginTransaction();
        try {
            $accountDigits = $targetCompany->account_digits ?? 8;
            $journalDigits = $targetCompany->journal_code_digits ?? 4;

            // Simuler l'auth pour que AdminConfigController puisse appeler Auth::user()
            \Illuminate\Support\Facades\Auth::setUser($user);

            // Obtenir les lignes validées via la logique de staging
            // (génération de codes, déduplication, validation incluses)
            $ctrl   = app(\App\Http\Controllers\Admin\AdminConfigController::class);
            $staged = $ctrl->importStaging($import->id, true);

            $rowsWithStatus = $staged['rowsWithStatus'] ?? [];
            $importedCount  = 0;
            $errors         = [];

            $this->updateProgress($import, 15, 'Traitement des lignes…');

            $validRows  = array_filter($rowsWithStatus, fn($r) => ($r['status'] ?? '') === 'valid');
            $totalValid = count($validRows);
            $processed  = 0;

            foreach ($validRows as $r) {
                $row = $r['data'];
                $processed++;

                if ($processed % 100 === 0) {
                    $pct = 15 + (int) round(($processed / max(1, $totalValid)) * 75);
                    $this->updateProgress($import, $pct, "Insertion {$processed}/{$totalValid}…");
                }

                try {
                    if ($import->type === 'initial') {
                        // ── Plan Comptable ──
                        $num   = $this->standardizeAccountNumber(trim($row['numero_de_compte'] ?? ''), $accountDigits);
                        $label = mb_strtoupper(trim($row['intitule'] ?? ''));
                        if (empty($num) || empty($label)) continue;

                        $prefix = substr($num, 0, 1);
                        $classe = is_numeric($prefix) ? (int)$prefix : 0;
                        $type   = in_array($classe, [1, 2, 3, 4, 5, 9]) ? 'Bilan' : 'Compte de résultat';

                        PlanComptable::firstOrCreate(
                            ['company_id' => $targetCompanyId, 'numero_de_compte' => $num],
                            [
                                'intitule'        => $label,
                                'type_de_compte'  => $type,
                                'classe'          => $classe,
                                'adding_strategy' => 'imported',
                                'numero_original' => $row['numero_original'] ?? null,
                                'user_id'         => $user->id,
                            ]
                        );
                        $importedCount++;

                    } elseif ($import->type === 'tiers') {
                        // ── Plan Tiers ──
                        $num   = strtoupper(trim($row['numero_de_tiers'] ?? ''));
                        $label = strtoupper(trim($row['intitule'] ?? ''));
                        if (empty($num) || empty($label) || $num === 'NON GÉNÉRÉ') continue;

                        $compteGenNum = trim($row['compte_general'] ?? '');
                        $compteGenId = null;
                        if (!empty($compteGenNum)) {
                            // Find the account by its standardized account number
                            $pc = PlanComptable::where('company_id', $targetCompanyId)
                                ->where('numero_de_compte', $compteGenNum)
                                ->first();
                            if ($pc) {
                                $compteGenId = $pc->id;
                            } else {
                                // Fallback: check if it's already an ID
                                if (is_numeric($compteGenNum)) {
                                    $pcById = PlanComptable::where('company_id', $targetCompanyId)
                                        ->where('id', (int)$compteGenNum)
                                        ->first();
                                    if ($pcById) {
                                        $compteGenId = $pcById->id;
                                    }
                                }
                            }
                        }

                        if (!$compteGenId) {
                            $typeTiers = $row['type_de_tiers'] ?? 'Autre';
                            $fallbackAccountNum = in_array(strtolower($typeTiers), ['fournisseur', 'cnps', 'impots']) ? '40110000' : '41110000';
                            $fallbackAccountNum = substr($fallbackAccountNum, 0, 3) . str_pad('0', ($accountDigits - 3), '0', STR_PAD_RIGHT);

                            $pcFallback = PlanComptable::where('company_id', $targetCompanyId)
                                ->where('numero_de_compte', $fallbackAccountNum)
                                ->first();
                            if ($pcFallback) {
                                $compteGenId = $pcFallback->id;
                            } else {
                                // Last resort: any account starting with 4
                                $pcAny = PlanComptable::where('company_id', $targetCompanyId)
                                    ->where('numero_de_compte', 'LIKE', '4%')
                                    ->first();
                                if ($pcAny) {
                                    $compteGenId = $pcAny->id;
                                } else {
                                    throw new \Exception("Aucun compte collectif de classe 4 trouvé en base pour rattacher le tiers '$num'.");
                                }
                            }
                        }

                        PlanTiers::firstOrCreate(
                            ['company_id' => $targetCompanyId, 'numero_de_tiers' => $num],
                            [
                                'intitule'        => $label,
                                'type_de_tiers'   => $row['type_de_tiers'] ?? 'Autre',
                                'compte_general'  => $compteGenId,
                                'numero_original' => $row['numero_original'] ?? null,
                                'user_id'         => $user->id,
                            ]
                        );
                        $importedCount++;

                    } elseif ($import->type === 'journals') {
                        // ── Code Journal ──
                        $code  = strtoupper(trim($row['code_journal'] ?? ''));
                        $label = strtoupper(trim($row['intitule'] ?? ''));
                        if (empty($code) || empty($label)) continue;

                        // Résolution du compte de trésorerie (si présent)
                        $compteIdTreso = null;
                        $compteTresoNum = trim($row['compte_de_tresorerie'] ?? '');
                        if (!empty($compteTresoNum)) {
                            $planTresoObj = PlanComptable::where('company_id', $targetCompanyId)
                                ->where('numero_de_compte', $compteTresoNum)
                                ->first();
                            $compteIdTreso = $planTresoObj?->id;
                        }

                        // Résolution robuste de traitement_analytique (colonne booléenne NOT NULL)
                        $analytiqueVal = $row['traitement_analytique'] ?? null;
                        $analytiqueBool = 0;
                        if ($analytiqueVal !== null) {
                            $cleanAna = strtolower(trim((string)$analytiqueVal));
                            if (in_array($cleanAna, ['oui', 'yes', 'true', '1', 'o', 'y'])) {
                                $analytiqueBool = 1;
                            }
                        }

                        CodeJournal::firstOrCreate(
                            ['company_id' => $targetCompanyId, 'code_journal' => $code],
                            [
                                'intitule'              => $label,
                                'type'                  => $row['type'] ?? 'Standard',
                                'poste_tresorerie'      => $row['poste_tresorerie'] ?? null,
                                'compte_de_tresorerie'  => $compteIdTreso,
                                'traitement_analytique' => $analytiqueBool,
                                'rapprochement_sur'     => $row['rapprochement_sur'] ?? null,
                                'numero_original'       => $row['numero_original'] ?? null,
                                'user_id'               => $user->id,
                            ]
                        );
                        $importedCount++;
                    }

                } catch (\Throwable $e) {
                    $errors[] = "Ligne {$r['index']}: " . $e->getMessage();
                }
            }

            if (!empty($errors)) {
                DB::rollBack();
                $limit = 50;
                if (count($errors) > $limit) {
                    $truncatedErrors = array_slice($errors, 0, $limit);
                    $truncatedErrors[] = "... et " . (count($errors) - $limit) . " autres erreurs.";
                    $reportErrors = $truncatedErrors;
                } else {
                    $reportErrors = $errors;
                }

                $report = [
                    'status'       => 'error',
                    'processed_g'  => $importedCount,
                    'filtered_a'   => 0,
                    'deduplicated' => 0,
                    'total_debit'  => 0.0,
                    'total_credit' => 0.0,
                    'new_accounts' => 0,
                    'new_tiers'    => 0,
                    'errors'       => $reportErrors,
                    'warnings'     => [],
                ];
                $import->update([
                    'status'    => 'error',
                    'error_log' => substr(implode("\n", array_slice($errors, 0, 20)), 0, 1000),
                    'metadata'  => array_merge($import->metadata ?? [], [
                        'commit_status'   => 'error',
                        'commit_progress' => 100,
                        'commit_report'   => $report,
                    ]),
                ]);
                return;
            }

            $report = [
                'status'       => 'success',
                'processed_g'  => $importedCount,
                'filtered_a'   => 0,
                'deduplicated' => 0,
                'total_debit'  => 0.0,
                'total_credit' => 0.0,
                'new_accounts' => ($import->type === 'initial' ? $importedCount : 0),
                'new_tiers'    => ($import->type === 'tiers' ? $importedCount : 0),
                'errors'       => [],
                'warnings'     => [],
            ];

            $import->update([
                'status'    => 'committed',
                'error_log' => "Importation réussie : {$importedCount} enregistrement(s) créé(s).",
                'metadata'  => array_merge($import->metadata ?? [], [
                    'commit_status'   => 'done',
                    'commit_progress' => 100,
                    'commit_report'   => $report,
                ]),
            ]);

            DB::commit();

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error("IMPORT_JOB_REF [{$this->importId}]: " . $e->getMessage(), ['exception' => $e]);
            $truncatedMsg = substr($e->getMessage(), 0, 1000);
            $import->update([
                'status'    => 'error',
                'error_log' => 'Erreur système : ' . $truncatedMsg,
                'metadata'  => array_merge($import->metadata ?? [], [
                    'commit_status'   => 'error',
                    'commit_progress' => 100,
                    'commit_report'   => [
                        'status'       => 'error',
                        'processed_g'  => 0,
                        'filtered_a'   => 0,
                        'deduplicated' => 0,
                        'total_debit'  => 0.0,
                        'total_credit' => 0.0,
                        'new_accounts' => 0,
                        'new_tiers'    => 0,
                        'errors'       => [$truncatedMsg],
                        'warnings'     => [],
                    ],
                ]),
            ]);
        }
    }
}
