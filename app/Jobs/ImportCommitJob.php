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

        $existingJournalsOriginal = CodeJournal::where('company_id', $targetCompanyId)
            ->whereNotNull('numero_original')->where('numero_original', '!=', '')
            ->pluck('id', 'numero_original')->toArray();
        $existingJournalsOriginal = array_change_key_case($existingJournalsOriginal, CASE_UPPER);

        // ─────────────────────────────────────────────
        // OPTIMISATION CLÉ : Pré-calcul des compteurs ECR/RAN UNE SEULE FOIS
        // (élimine des milliers de requêtes en boucle)
        // ─────────────────────────────────────────────
        $baseEcrCounter = (int)(EcritureComptable::where('company_id', $targetCompanyId)
            ->where('n_saisie', 'like', 'ECR_%')
            ->max(DB::raw('CAST(SUBSTRING(n_saisie, 5) AS UNSIGNED)')) ?? 0);

        $baseRanCounter = (int)(EcritureComptable::where('company_id', $targetCompanyId)
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

        DB::beginTransaction();
        try {
            $rowNum    = 0;
            $batchSize = 2000; // 2× l'ancienne taille

            foreach ($data as $index => $rowOrig) {
                $rowNum++;

                // Mise à jour de la progression toutes les 500 lignes
                if ($rowNum % 500 === 0) {
                    $pct = (int) round(($rowNum / $totalRows) * 90); // 0-90%
                    $this->updateProgress($import, $pct, "Traitement ligne {$rowNum}/{$totalRows}…");
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

                // ── Type A (Analytique) → ignorer ──
                if (isset($rowMapped['type_ecriture']) && strtoupper(trim($rowMapped['type_ecriture'])) === 'A') {
                    $report['filtered_a']++;
                    continue;
                }

                $rowCompte     = $this->standardizeAccountNumber(trim($rowMapped['compte'] ?? ''), $accountDigits);
                $rowJournalRaw = trim($rowMapped['journal'] ?? '');
                $rowJournal    = $this->standardizeJournalCode($rowJournalRaw, $journalDigits);

                // ── Détection des lignes analytiques cachées (colonne hors mapping) ──
                $isHiddenA        = false;
                $mappedColIndexes = array_filter(array_values($mapping), fn($v) => is_numeric($v));
                foreach ($rowOrig as $colIdx => $cellVal) {
                    if (in_array($colIdx, $mappedColIndexes)) continue;
                    $v = strtoupper(trim($cellVal ?? ''));
                    if ($v === 'A' || $v === 'ANALYTIQUE') { $isHiddenA = true; break; }
                }
                if ($isHiddenA) { $report['filtered_a']++; continue; }

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

                $tiersNum = trim($rowMapped['tiers'] ?? '');
                $tiersId  = !empty($tiersNum)
                    ? ($planTiersIds[strtoupper($tiersNum)] ?? $planTiersOriginalIds[strtoupper($tiersNum)] ?? null)
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

                // ── Groupement / Numérotation ECR (OPTIMISÉ : 0 requête DB ici) ──
                $nsVal = trim((string)($rowMapped['n_saisie'] ?? ''));
                $refVal = trim((string)($rowMapped['reference'] ?? ''));
                if ($nsVal === '' && $refVal !== '') {
                    $nsVal = $refVal;
                    $rowMapped['n_saisie'] = $refVal;
                }
                $origNSaisie = $nsVal !== '' ? $nsVal : 'IMPORT';
                if (strtoupper($rowJournal) === 'RAN' || strtoupper($rowJournalRaw) === 'RAN') {
                    $origNSaisie = 'RAN';
                }

                $groupKey   = $date->format('Y-m-d') . '_' . $journalId . '_' . strtoupper($origNSaisie);

                if (!isset($ecrMapping[$groupKey])) {
                    // ✅ INCRÉMENTATION LOCALE — 0 requête DB
                    if (str_starts_with(strtoupper($origNSaisie), 'RAN') || strtoupper($rowJournal) === 'RAN') {
                        $ecrMapping[$groupKey] = 'RAN' . str_pad(++$baseRanCounter, $ranNumLength, '0', STR_PAD_LEFT);
                    } else {
                        $ecrMapping[$groupKey] = 'ECR_' . str_pad(++$baseEcrCounter, 12, '0', STR_PAD_LEFT);
                    }
                }
                $globalNSaisie = $ecrMapping[$groupKey];

                // Suivi équilibre : même clé que le groupKey (date + journalId + n_saisie)
                // journalId est l'ID normalisé en DB — ACH et ACH1 résolvent au même ID
                // donc toutes les lignes d'une même pièce sont bien groupées ensemble
                if (!isset($groupBalances[$groupKey])) {
                    $groupBalances[$groupKey] = ['debit' => 0, 'credit' => 0, 'ref' => $origNSaisie, 'journal' => $rowJournal, 'date' => $date->format('d/m/Y')];
                }
                $groupBalances[$groupKey]['debit']  += round($debit, 2);
                $groupBalances[$groupKey]['credit'] += round($credit, 2);

                // ── JournalSaisi (avec cache en mémoire) ──
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

                // ── Accumulation du batch ──
                $batchEcritures[] = [
                    'date'                      => $date->format('Y-m-d'),
                    'n_saisie'                  => $globalNSaisie,
                    'n_saisie_user'             => $origNSaisie,
                    'reference_piece'           => strtoupper($rowMapped['reference'] ?? 'IMPORT'),
                    'plan_comptable_id'         => $compteId,
                    'plan_tiers_id'             => $tiersId,
                    'plan_analytique'           => 0,
                    'code_journal_id'           => $journalId,
                    'journaux_saisis_id'        => $journalSaisiId,
                    'description_operation'     => strtoupper($rowMapped['libelle'] ?? 'IMPORTATION EXTERNE'),
                    'debit'                     => $debit,
                    'credit'                    => $credit,
                    'exercices_comptables_id'   => $import->exercice_id ?? null,
                    'company_id'                => $targetCompanyId,
                    'user_id'                   => $user->id,
                    'statut'                    => 'approved',
                    'created_at'                => now(),
                    'updated_at'                => now(),
                ];

                // Insertion par lots de 2 000
                if (count($batchEcritures) >= $batchSize) {
                    EcritureComptable::insert($batchEcritures);
                    $importedCount += count($batchEcritures);
                    $batchEcritures = [];
                }
            } // fin foreach

            // Insertion du reste
            if (!empty($batchEcritures)) {
                EcritureComptable::insert($batchEcritures);
                $importedCount += count($batchEcritures);
            }

            // ── Vérification équilibre BLOQUANTE (par pièce = date + n_saisie) ──
            foreach ($groupBalances as $bk => $bal) {
                if (abs($bal['debit'] - $bal['credit']) > 0.01) {
                    $diff     = number_format(abs($bal['debit'] - $bal['credit']), 2, ',', ' ');
                    $errors[] = "DÉSÉQUILIBRE : Pièce '{$bal['ref']}' du {$bal['date']} (Journal {$bal['journal']}) — Débit : "
                              . number_format($bal['debit'], 2, ',', ' ')
                              . " / Crédit : " . number_format($bal['credit'], 2, ',', ' ')
                              . " / Écart : {$diff}.";
                }
            }

            if (!empty($errors)) {
                DB::rollBack();
                $report['status'] = 'error';
                $report['errors'] = $errors;
                $import->update([
                    'status'    => 'error',
                    'error_log' => implode("\n", array_slice($errors, 0, 20)),
                    'metadata'  => array_merge($import->metadata ?? [], [
                        'commit_status'   => 'error',
                        'commit_progress' => 100,
                        'commit_report'   => $report,
                    ]),
                ]);
                return;
            }

            // ── Commit ──
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
            $import->update([
                'status'    => 'error',
                'error_log' => 'Erreur système : ' . $e->getMessage(),
                'metadata'  => array_merge($import->metadata ?? [], [
                    'commit_status'   => 'error',
                    'commit_progress' => 100,
                    'commit_report'   => ['status' => 'error', 'errors' => [$e->getMessage()]],
                ]),
            ]);
        }
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
                                'poste_tresorerie'      => $row['poste_tresorerie'] ?? 'Autre',
                                'compte_de_tresorerie'  => $compteIdTreso,
                                'traitement_analytique' => $analytiqueBool,
                                'rapprochement_sur'     => $row['rapprochement_sur'] ?? 'Manuel',
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
                $report = [
                    'status'       => 'error',
                    'processed_g'  => $importedCount,
                    'filtered_a'   => 0,
                    'deduplicated' => 0,
                    'total_debit'  => 0.0,
                    'total_credit' => 0.0,
                    'new_accounts' => 0,
                    'new_tiers'    => 0,
                    'errors'       => $errors,
                    'warnings'     => [],
                ];
                $import->update([
                    'status'    => 'error',
                    'error_log' => implode("\n", array_slice($errors, 0, 20)),
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
            $import->update([
                'status'    => 'error',
                'error_log' => 'Erreur système : ' . $e->getMessage(),
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
                        'errors'       => [$e->getMessage()],
                        'warnings'     => [],
                    ],
                ]),
            ]);
        }
    }
}
