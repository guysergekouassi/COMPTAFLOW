<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Import\UniversalParser;
use App\Services\Import\HeuristicAnalyzer;
use App\Models\ImportStaging;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

class UniversalImportController extends Controller
{
    protected $parser;
    protected $analyzer;

    public function __construct(UniversalParser $parser, HeuristicAnalyzer $analyzer)
    {
        $this->parser = $parser;
        $this->analyzer = $analyzer;
    }

    /**
     * Upload Step: Reads file -> Staging -> Redirect to Mapping
     */
    public function upload(Request $request)
    {
        $request->validate([
            'file' => 'required|file|max:20480', // 20MB limit
            'type' => 'required', // accounts, journals, tiers, courant (entries)
            'source' => 'nullable' // excel, sage, etc. (Just for info)
        ]);

        try {
            $file = $request->file('file');
            $filePath = $file->getRealPath();
            $clientExtension = $file->getClientOriginalExtension();
            $fileType = $request->input('type');
            
            // Map legacy type names to internal types
            $typeMap = [
                'courant' => 'entries',
                'initial' => 'accounts',
            ];
            $internalType = $typeMap[$fileType] ?? $fileType;

            // 1. Parse File
            $parsed = $this->parser->parse($filePath, $clientExtension);
            $headers = $parsed['headers'];
            $rows = $parsed['rows'];

            // 2. Analyze Mapping
            $proposal = $this->analyzer->analyze($headers, array_slice($rows, 0, 5));

            // 3. Staging & Pre-Validation
            $batchId = (string) Str::uuid();
            $companyId = session('current_company_id', Auth::user()->company_id);
            $userId = Auth::id();

            // Metadata Row
            ImportStaging::create([
                'batch_id' => $batchId,
                'company_id' => $companyId,
                'user_id' => $userId,
                'exercice_id' => $request->input('exercice'),
                'file_name' => $file->getClientOriginalName(),
                'type' => 'metadata',
                'raw_data' => json_encode([
                    'headers' => $headers, 
                    'proposal' => $proposal,
                    'exercice_id' => $request->input('exercice') // On garde aussi ici pour compatibilité si besoin
                ]),
                'status' => 'pending'
            ]);

            // Data Rows - Perform Validation Immediately
            $chunks = array_chunk($rows, 500);
            $headerIndexMap = array_flip($headers);
            
            foreach ($chunks as $chunk) {
                $insertData = [];
                foreach ($chunk as $index => $row) {
                    if (empty(array_filter($row))) continue;
                    
                    // Validate Row
                    $validationResult = $this->validateRow($row, $proposal, $internalType, $headerIndexMap);
                    
                    $insertData[] = [
                        'batch_id' => $batchId,
                        'company_id' => $companyId,
                        'user_id' => $userId,
                        'file_name' => $file->getClientOriginalName(),
                        'type' => $internalType,
                        'raw_data' => json_encode($row),
                        'status' => $validationResult['status'],
                        'error_log' => $validationResult['error'],
                        'created_at' => now(),
                        'updated_at' => now()
                    ];
                }
                if (!empty($insertData)) ImportStaging::insert($insertData);
            }

            // Calculations for View
            $totalRows = count($rows);
            // Retrieve actual stats from DB
            $validCount = ImportStaging::where('batch_id', $batchId)->where('type', '!=', 'metadata')->where('status', 'valid')->count();
            $errorCount = ImportStaging::where('batch_id', $batchId)->where('type', '!=', 'metadata')->where('status', 'error')->count();
            
            // Retrieve ALL rows for display (User request: "tous les comptes")
            // Limit to 5000 to prevent crash, warn if more? 
            // The user asked for ALL. We will pass a query builder or collection. 
            // For blade rendering, 5000 is okay.
            $stagingRows = ImportStaging::where('batch_id', $batchId)->where('type', '!=', 'metadata')->limit(5000)->get();

            return view('admin.import.mapping_step', [
                'batch_id' => $batchId,
                'headers' => $headers,
                'proposal' => $proposal,
                'staging_rows' => $stagingRows, // Passing full objects with status
                'valid_count' => $validCount,
                'error_count' => $errorCount,
                'type' => $internalType
            ]);

        } catch (\Exception $e) {
            Log::error('UniversalImport Upload Error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->back()
                ->withInput()
                ->with('error', "Erreur lors de l'analyse du fichier : " . $e->getMessage());
        }
    }

    /**
     * Helper to validate a single row based on proposed mapping
     */
    protected function validateRow($row, $mapping, $type, $headerMap) 
    {
        $status = 'valid';
        $error = null;
        $payload = [];

        // Build Payload
        foreach ($mapping as $dbField => $headerName) {
            if (empty($headerName)) continue;
            if (isset($headerMap[$headerName])) {
                $payload[$dbField] = trim((string)($row[$headerMap[$headerName]] ?? ''));
            }
        }

        // Rules
        if ($type === 'accounts') {
            if (empty($payload['numero_de_compte'])) { $status = 'error'; $error = 'Numéro de compte manquant'; }
            elseif (empty($payload['intitule'])) { $status = 'error'; $error = 'Intitulé manquant'; }
        } elseif ($type === 'tiers') {
            if (empty($payload['numero_de_tiers'])) { $status = 'error'; $error = 'Numéro Tiers manquant'; }
            elseif (empty($payload['intitule'])) { $status = 'error'; $error = 'Nom manquant'; }
        } elseif ($type === 'journals') {
            if (empty($payload['code_journal'])) { $status = 'error'; $error = 'Code Journal manquant'; }
        } elseif ($type === 'entries') {
             if (empty($payload['date_ecriture'])) { $status = 'error'; $error = 'Date manquante'; }
             else {
                 $d = $this->parseDateRobust($payload['date_ecriture']);
                 if (!$d) { $status = 'error'; $error = 'Format de date invalide : ' . $payload['date_ecriture']; }
             }
        }

        return ['status' => $status, 'error' => $error];
    }

    /**
     * Process Step: Validates Mapping -> Validates Logic -> Inserts
     */
    public function process(Request $request)
    {
        $batchId = $request->batch_id;
        $mapping = $request->mapping; // ['db_col' => 'file_header']
        $internalType = $request->type;
        $companyId = session('current_company_id', Auth::user()->company_id);
        $userId = Auth::id();
        
        $metadataReq = ImportStaging::where('batch_id', $batchId)->where('type', 'metadata')->first();
        if (!$metadataReq) return redirect()->route('admin.config.external_import')->with('error', "Métadonnées d'importation introuvables.");

        $metadata = json_decode($metadataReq->raw_data, true);
        $headers = $metadata['headers'];
        $targetExerciceId = $metadataReq->exercice_id ?? $metadata['exercice_id'] ?? null;
        $headerIndexMap = array_flip($headers); // HeaderName => Index

        // Retrieve Data
        $stagingRows = ImportStaging::where('batch_id', $batchId)->where('type', '!=', 'metadata')->get();
        if ($stagingRows->isEmpty()) return redirect()->route('admin.config.external_import')->with('error', "Aucune donnée à importer.");

        DB::beginTransaction();
        try {
            $count = 0;
            $errors = [];
            $validPayloads = [];
            $importBatchMax = [];

            // PHASE 1: Validation & Construction
            foreach ($stagingRows as $index => $rowRecord) {
                $row = json_decode($rowRecord->raw_data, true);
                $lineNumber = $index + 2; // +1 for header, +1 for 0-index

                $payload = [];
                // Apply Mapping
                foreach ($mapping as $dbField => $headerName) {
                    if (empty($headerName)) continue;
                    if (isset($headerIndexMap[$headerName])) {
                        $val = $row[$headerIndexMap[$headerName]] ?? null;
                        $payload[$dbField] = trim((string)$val);
                    }
                }

                // Required Fields Check
                if ($internalType === 'accounts') {
                    if (empty($payload['numero_de_compte']) || empty($payload['intitule'])) {
                        $errors[] = "Ligne $lineNumber : Compte ou Intitulé manquant.";
                        continue;
                    }
                } elseif ($internalType === 'tiers') {
                // LOGIQUE DE GÉNÉRATION AUTOMATIQUE ANTIGRAVITY (Replacement)
                $importedNum = $payload['numero_de_tiers'] ?? '';
                $payload['numero_original'] = $importedNum; // Sauvegarde du numéro original
                $intitule = $payload['intitule'] ?? '';
                $compteGeneral = $payload['compte_general'] ?? '';
                $category = $payload['type_de_tiers'] ?? '';

                // 1. Détection de catégorie par préfixe si le numéro importé est présent
                if (!empty($importedNum)) {
                    $prefix = substr($importedNum, 0, 2);
                    $categoryMap = [
                        '40' => 'Fournisseur', '41' => 'Client', '42' => 'Personnel',
                        '43' => 'Organisme sociaux / CNPS', '44' => 'Impôt',
                        '45' => 'Organisme international', '46' => 'Associé', '47' => 'Divers Tiers'
                    ];
                    if (isset($categoryMap[$prefix])) {
                        $category = $categoryMap[$prefix];
                        $payload['type_de_tiers'] = $category;
                        if (empty($compteGeneral)) {
                            $compteGeneral = $prefix . str_pad('0', (8 - 2), '0', STR_PAD_RIGHT); // Fallback standard digits
                            $payload['compte_general'] = $compteGeneral;
                        }
                    }
                }

                // 2. Génération du nouveau numéro séquentiel
                if (!empty($compteGeneral)) {
                    $prefix = substr($compteGeneral, 0, 2);
                    $company = \App\Models\Company::find($companyId);
                    $digits = (int)($company->tier_digits ?? 8);
                    $base = $prefix;
                    $seqLength = max(1, $digits - strlen($base));

                    // Cache du max pour éviter O(N^2) et les collisions
                    if (!isset($importBatchMax[$base])) {
                        $maxSeq = 0;
                        $existingTiers = \App\Models\PlanTiers::where('company_id', $companyId)
                            ->where('numero_de_tiers', 'like', $base . '%')
                            ->get();

                        foreach ($existingTiers as $tier) {
                            $suffix = substr($tier->numero_de_tiers, strlen($base));
                            if (is_numeric($suffix)) {
                                $maxSeq = max($maxSeq, (int)$suffix);
                            }
                        }
                        $importBatchMax[$base] = $maxSeq;
                    }

                    $importBatchMax[$base]++;
                    $nextId = $base . str_pad($importBatchMax[$base], $seqLength, '0', STR_PAD_LEFT);
                    
                    if (strlen($nextId) > $digits) {
                        // En cas de dépassement, on utilise le max brut sans padding forcé si nécessaire, ou on tronque intelligemment
                         $nextId = substr($nextId, 0, $digits);
                    }
                    
                    $payload['numero_de_tiers'] = $nextId;
                }

                if (empty($payload['numero_de_tiers']) || empty($payload['intitule'])) {
                     $errors[] = "Ligne $lineNumber : Numéro Tiers impossible à générer ou Nom manquant.";
                     continue;
                }
            } elseif ($internalType === 'journals') {
                    if (empty($payload['code_journal'])) {
                        $errors[] = "Ligne $lineNumber : Code journal manquant.";
                        continue;
                    }
                } elseif ($internalType === 'entries') {
                     // Check balance later? No, usually per line checks are format based. E.g. date.
                     if (empty($payload['date_ecriture'])) $errors[] = "Ligne $lineNumber : Date manquante.";
                     if (empty($payload['n_saisie'])) $payload['n_saisie'] = $payload['piece_ref'] ?? 'IMPORT';
                     
                     // Convert 'debit'/'credit' to float
                     if (isset($payload['debit'])) $payload['debit'] = (float)str_replace([' ', ','], ['', '.'], $payload['debit']);
                     if (isset($payload['credit'])) $payload['credit'] = (float)str_replace([' ', ','], ['', '.'], $payload['credit']);
                }

                $validPayloads[] = $payload;
            }

            if (count($errors) > 0) {
                // Fail immediately "Infallible"
                DB::rollBack();
                $msg = "Erreurs de validation détectées :<br>" . implode('<br>', array_slice($errors, 0, 10));
                if (count($errors) > 10) $msg .= "<br>... et " . (count($errors) - 10) . " autres.";
                return redirect()->route('admin.config.external_import')->with('error', $msg);
            }

            // PHASE 2: Insertion
            foreach ($validPayloads as $p) {
                // Add System Fields
                $p['company_id'] = $companyId;
                
                if ($internalType === 'accounts') {
                     \App\Models\PlanComptable::firstOrCreate(
                        ['company_id' => $companyId, 'numero_de_compte' => $p['numero_de_compte']],
                        array_merge($p, ['user_id' => $userId, 'classe' => substr($p['numero_de_compte'], 0, 1)])
                     );
                } elseif ($internalType === 'tiers') {
                     \App\Models\PlanTiers::firstOrCreate(
                        ['company_id' => $companyId, 'numero_de_tiers' => $p['numero_de_tiers']],
                        array_merge($p, ['user_id' => $userId])
                     );
                } elseif ($internalType === 'journals') {
                     \App\Models\CodeJournal::firstOrCreate(
                        ['company_id' => $companyId, 'code_journal' => $p['code_journal']],
                        $p
                     );
                } elseif ($internalType === 'entries') {
                     // Need 'exercice_comptable_id'
                     $p['user_id'] = $userId;
                     $p['exercices_comptables_id'] = $targetExerciceId ?? session('current_exercice_id') ?? 1;
                     $p['statut'] = 'approved'; 
                     // Date for JournalSaisi
                     $dateObj = $this->parseDateRobust($p['date_ecriture']);
                     if (!$dateObj) {
                         $errors[] = "Ligne $lineNumber : Date invalide '" . $p['date_ecriture'] . "'.";
                         continue;
                     }
                     $p['date'] = $dateObj->format('Y-m-d');
                     
                     // LOGIQUE GLOBAL NUMBERING
                     $origNSaisie = $p['n_saisie'] ?? 'IMPORT';
                     $dateKey = $p['date']; // Ensurer uniqueness per date + original piece
                     $mapKey = $dateKey . '_' . $origNSaisie;

                     if (!isset($importBatchMax['ECR'])) {
                         $importBatchMax['ECR'] = []; // Use this for ECR global mapping
                     }
                     
                     if (!isset($importBatchMax['ECR'][$mapKey])) {
                         $importBatchMax['ECR'][$mapKey] = $this->generateGlobalSaisieNumber($companyId);
                     }
                     
                     $p['n_saisie'] = $importBatchMax['ECR'][$mapKey];
                     $p['n_saisie_user'] = $origNSaisie;

                     // Map other fields
                     $p['description_operation'] = $p['libelle'] ?? 'IMPORT';
                     $p['reference_piece'] = $p['piece_ref'] ?? null;
                     
                     // Retrieve IDs
                     $p['code_journal_id'] = \App\Models\CodeJournal::where('company_id', $companyId)
                        ->where('code_journal', $p['code_journal'] ?? '')
                        ->value('id');
                     $p['plan_comptable_id'] = \App\Models\PlanComptable::where('company_id', $companyId)
                        ->where('numero_de_compte', $p['numero_compte'] ?? '')
                        ->value('id');
                     
                     $p['plan_tiers_id'] = \App\Models\PlanTiers::where('company_id', $companyId)
                        ->where('numero_de_tiers', $p['compte_tiers'] ?? '')
                        ->value('id');

                     if (!$p['code_journal_id']) {
                         Log::warning("Import Entry: Journal not found", ['code' => $p['code_journal']]);
                     }
                     if (!$p['plan_comptable_id']) {
                         Log::warning("Import Entry: Account not found", ['numero' => $p['numero_compte']]);
                     }
                     
                     // Generate or get JournalSaisi
                     if ($p['code_journal_id']) {
                         $journalSaisi = \App\Models\JournalSaisi::firstOrCreate([
                             'annee' => $dateObj->year,
                             'mois' => $dateObj->month,
                             'exercices_comptables_id' => $p['exercices_comptables_id'],
                             'code_journals_id' => $p['code_journal_id'],
                             'company_id' => $companyId,
                         ], ['user_id' => $userId]);
                         $p['journaux_saisis_id'] = $journalSaisi->id;
                     }

                     \App\Models\EcritureComptable::create($p);
                }
                $count++;
            }

            // Clean Staging
            ImportStaging::where('batch_id', $batchId)->delete();
            DB::commit();

            return redirect()->route('admin.config.external_import')->with('success', "Import effectué avec succès ($count enregistrements).");

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('admin.config.external_import')->with('error', "Erreur Système : " . $e->getMessage());
        }
    }

    private function generateGlobalSaisieNumber($companyId)
    {
        // On cherche le dernier numéro dans la table réelle
        $lastRealSaisie = \App\Models\EcritureComptable::where('company_id', $companyId)
            ->where('n_saisie', 'like', 'ECR_%')
            ->orderBy('n_saisie', 'desc')
            ->first();

        $nextNumber = 1;
        if ($lastRealSaisie) {
            $lastNSaisie = $lastRealSaisie->n_saisie;
            $numberPart = str_replace('ECR_', '', $lastNSaisie);
            $nextNumber = (int)$numberPart + 1;
        }

        return 'ECR_' . str_pad($nextNumber, 12, '0', STR_PAD_LEFT);
    }

    /**
     * Parse date string robustly, handling Excel serial numbers and EU formats.
     */
    private function parseDateRobust($dateStr)
    {
        if (empty($dateStr)) return null;
        
        // Excel Serial Number detection
        // Excel serials for 2000-2040 are between 36526 and 51137.
        // We only treat it as a serial if it's numeric, doesn't start with 0 (unless it's just a few digits),
        // and is within a reasonable range for accounting (usually >= 30000).
        if (is_numeric($dateStr)) {
            $num = (float)$dateStr;
            if ($num >= 30000 && $num <= 60000 && !preg_match('/^0/', $dateStr)) {
                try {
                    return Carbon::instance(ExcelDate::excelToDateTimeObject($num));
                } catch (\Exception $e) {}
            }
        }
        
        $dateStr = trim((string)$dateStr);
        // Replace common separators with /
        $normalizedDate = str_replace(['\\', '.', '-', ' '], '/', $dateStr);
        
        $formats = [
            'd/m/Y', 'j/n/Y', 'd/n/Y', 'j/m/Y', // EU with /
            'Y-m-d', 'd-m-Y', 'Y/m/d',          // ISO or other
            'd/m/y',                            // 2-digit year with /
            'dmY', 'dmy',                       // Sage/No separator formats
        ];

        foreach ($formats as $fmt) {
            try {
                // If format has no separators, use the original string
                $strToParse = (strpbrk($fmt, '/- ') === false) ? $dateStr : $normalizedDate;
                $d = Carbon::createFromFormat($fmt, $strToParse);
                
                if ($d && $d->format($fmt) == $strToParse) {
                    // Safety check: ensure year is reasonable (not 0024 instead of 2024)
                    if ($d->year < 100) $d->year += 2000;
                    return $d;
                }
            } catch (\Exception $e) {}
        }
        
        // Final fallback (souple)
        try {
            return Carbon::parse($dateStr);
        } catch (\Exception $e) {
            return null;
        }
    }
}
