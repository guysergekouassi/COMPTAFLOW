<?php

namespace App\Services;

use App\Models\LiasseData;
use App\Models\LiasseMapping;
use App\Models\ExerciceComptable;
use App\Models\EcritureComptable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Artisan;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;

class LiasseFiscaleService
{
    protected $reportingService;

    public function __construct(\App\Services\AccountingReportingService $reportingService)
    {
        $this->reportingService = $reportingService;
    }

    /**
     * Résumé des indicateurs clés (Total Actif, Passif, Résultat).
     */
    public function getSummaryData($exerciceId, $companyId)
    {
        $balances = $this->getAccountBalances($exerciceId, $companyId);
        $totalActif = $this->calculateValueForRangeFast('2,3,4,5', $balances)['net'];
        $totalPassif = $this->calculateValueForRangeFast('1,4,5', $balances)['net'];
        $produits = $this->calculateValueForRangeFast('7', $balances)['net'];
        $charges = $this->calculateValueForRangeFast('6', $balances)['net'];
        
        return [
            'total_actif' => $totalActif,
            'total_passif' => $totalPassif,
            'resultat_net' => $produits - $charges,
        ];
    }

    public function getAccountBalances($exerciceId, $companyId)
    {
        return DB::table('ecriture_comptables')
            ->join('plan_comptables', 'ecriture_comptables.plan_comptable_id', '=', 'plan_comptables.id')
            ->where('ecriture_comptables.exercices_comptables_id', $exerciceId)
            ->where('ecriture_comptables.company_id', $companyId)
            ->selectRaw('plan_comptables.numero_de_compte, plan_comptables.intitule, SUM(debit) as total_debit, SUM(credit) as total_credit')
            ->groupBy('plan_comptables.id', 'plan_comptables.numero_de_compte', 'plan_comptables.intitule')
            ->get();
    }

    public function calculateValueForRangeFast($range, $balances)
    {
        $prefixes = explode(',', $range);
        $totalDebit = 0;
        $totalCredit = 0;
        $amort = 0;
        $details = [];

        foreach ($balances as $b) {
            $n = trim($b->numero_de_compte);
            $d = $b->total_debit;
            $c = $b->total_credit;
            
            $matchPrefix = false;
            foreach ($prefixes as $p) {
                $p = trim($p);
                if ($p !== '' && str_starts_with($n, $p)) {
                    $matchPrefix = true;
                    break;
                }
            }

            if ($matchPrefix) {
                $totalDebit += $d;
                $totalCredit += $c;
                // Détails : compte de passif = Crédit-Débit, compte d'actif = Débit-Crédit
                $firstDigit = substr(trim($range), 0, 1);
                $solde = in_array($firstDigit, ['1', '4', '7']) ? ($c - $d) : ($d - $c);
                if (abs($solde) > 0.01) {
                    $details[] = [
                        'numero' => $n,
                        'intitule' => $b->intitule,
                        'solde' => $solde
                    ];
                }
            }
        }
        
        $firstDigit = substr(trim($range), 0, 1);
        if (in_array($firstDigit, ['1', '4', '7'])) {
            $brut = $totalCredit - $totalDebit;
        } else {
            $brut = $totalDebit - $totalCredit;
        }

        if ($firstDigit === '2') {
            foreach ($balances as $b) {
                $n = trim($b->numero_de_compte);
                foreach ($prefixes as $p) {
                    $cleanPrefix = trim($p);
                    if (strlen($cleanPrefix) >= 1) {
                        $p28 = '28' . substr($cleanPrefix, 1);
                        $p29 = '29' . substr($cleanPrefix, 1);
                        if (str_starts_with($n, $p28) || str_starts_with($n, $p29)) {
                            // Amortissements : Crédit - Débit
                            $amortMontant = $b->total_credit - $b->total_debit;
                            $amort += $amortMontant;
                            if (abs($amortMontant) > 0.01) {
                                $details[] = [
                                    'numero' => $n,
                                    'intitule' => $b->intitule,
                                    'solde' => -$amortMontant // Négatif car soustrait de l'Actif dans le détail
                                ];
                            }
                        }
                    }
                }
            }
        }

        usort($details, function($a, $b) { return strcmp($a['numero'], $b['numero']); });

        return [
            'brut' => $brut,
            'amort' => $amort,
            'net' => $brut - $amort,
            'details' => $details
        ];
    }

    public function getPageData($exerciceId, $pageCode)
    {
        if ($pageCode === 'BALANCE') {
            $companyId = ExerciceComptable::find($exerciceId)->company_id ?? 1;
            return $this->reportingService->getBalanceData($exerciceId, $companyId);
        }
        
        if ($pageCode === 'GRAND_LIVRE') {
            $companyId = ExerciceComptable::find($exerciceId)->company_id ?? 1;
            return $this->reportingService->getLedgerData($exerciceId, $companyId);
        }

        $exercice = ExerciceComptable::find($exerciceId);
        if (!$exercice) return [];

        $companyId = $exercice->company_id;

        $prevExercice = ExerciceComptable::where('company_id', $companyId)
            ->where('date_fin', '<', $exercice->date_debut)
            ->orderBy('date_fin', 'desc')
            ->first();

        $mappings = LiasseMapping::where('code_tableau', $pageCode)->get();
        if ($mappings->isEmpty()) {
            if ($pageCode === 'FICHE_R1') {
                return [
                    'ZA1' => $exercice->date_debut->format('d/m/Y'),
                    'ZA2' => $exercice->date_fin->format('d/m/Y'),
                    'ZA3' => 12,
                ];
            }
            return [];
        }

        $balancesN = $this->getAccountBalances($exerciceId, $companyId);
        $balancesN1 = $prevExercice ? $this->getAccountBalances($prevExercice->id, $companyId) : [];

        $result = [];

        foreach ($mappings as $mapping) {
            $fieldCode = $mapping->code_champ_dgi;
            $parts = explode('_', $fieldCode);
            $shortCode = $parts[count($parts)-2] ?? null;
            if (!$shortCode) continue;

            if ($mapping->account_range) {
                $valuesN = $this->calculateValueForRangeFast($mapping->account_range, $balancesN);
                $valuesN1 = $prevExercice ? $this->calculateValueForRangeFast($mapping->account_range, $balancesN1) : ['net' => 0, 'details' => []];

                if ($pageCode === 'BILAN_ACTIF') {
                    $result[$shortCode . '_brut'] = $valuesN['brut'];
                    $result[$shortCode . '_amort'] = $valuesN['amort'];
                    $result[$shortCode . '_net'] = $valuesN['net'];
                    $result[$shortCode . '_details'] = $valuesN['details'];
                    $result[$shortCode . '_net_N1'] = $valuesN1['net'];
                } else {
                    $result[$shortCode] = $valuesN['net'];
                    $result[$shortCode . '_details'] = $valuesN['details'];
                    $result[$shortCode . '_N1'] = $valuesN1['net'];
                }
            }
        }

        // Pour TFT et Résultat, si on veut utiliser reportingService au lieu de mapping
        if ($pageCode === 'TFT') {
            $result['detailed_tft'] = $this->reportingService->getTFTData($exerciceId, $companyId, null, true);
        }
        if ($pageCode === 'RESULTAT') {
            $result['detailed_sig'] = $this->reportingService->getSIGData($exerciceId, $companyId, null, true);
        }

        $manualData = LiasseData::where('exercice_id', $exerciceId)
            ->where('company_id', $companyId)
            ->where('page_code', $pageCode)
            ->pluck('value', 'field_code')
            ->toArray();

        return array_merge($result, $manualData);
    }

    /**
     * Enregistre les données manuelles.
     */
    public function saveManualData($exerciceId, $companyId, $pageCode, $data)
    {
        foreach ($data as $fieldCode => $value) {
            LiasseData::updateOrCreate(
                [
                    'company_id' => $companyId,
                    'exercice_id' => $exerciceId,
                    'page_code' => $pageCode,
                    'field_code' => $fieldCode,
                ],
                ['value' => $value]
            );
        }
        return true;
    }

    /**
     * Génère le fichier XML au format e-SINTAX.
     */
    public function generateXml($exerciceId, $companyId)
    {
        $exercice = ExerciceComptable::find($exerciceId);
        $company = \App\Models\Company::find($companyId);
        if (!$exercice || !$company) throw new \Exception("Données manquantes.");

        $xml = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><EDI/>');
        $info = $xml->addChild('informations');
        $info->addChild('type', 'NO'); 
        $info->addChild('ncc', $company->ncc ?? '0000000X');
        $info->addChild('exercice', $exercice->date_debut->format('Y'));
        
        $fixesNode = $xml->addChild('champsTableauxFixes');
        $varsNode = $xml->addChild('champsTableauxVariables');

        $mappings = LiasseMapping::all();
        $pageDataCache = [];

        foreach ($mappings as $mapping) {
            $pageCode = $mapping->code_tableau;
            if (!isset($pageDataCache[$pageCode])) {
                $pageDataCache[$pageCode] = $this->getPageData($exerciceId, $pageCode);
            }
            
            $data = $pageDataCache[$pageCode];
            $fieldCode = $mapping->code_champ_dgi;
            $parts = explode('_', $fieldCode);
            $shortCode = $parts[count($parts)-2] ?? null;
            $colNum = $parts[count($parts)-1] ?? '1';

            if ($shortCode) {
                $suffix = "";
                if (in_array($mapping->code_tableau, ['ACTIF', 'PASSIF', 'RESULTAT', 'TFT'])) {
                    if ($colNum == '1') $suffix = ($mapping->code_tableau === 'ACTIF') ? '_brut' : '';
                    if ($colNum == '2') $suffix = ($mapping->code_tableau === 'ACTIF') ? '_amort' : '_N1';
                    if ($colNum == '3') $suffix = ($mapping->code_tableau === 'ACTIF') ? '_net' : '';
                    if ($colNum == '4') $suffix = '_N1';
                }
                $value = $data[$shortCode . $suffix] ?? $data[$shortCode] ?? "";
            }

            if ($mapping->pos_ligne === null || $mapping->pos_ligne == 0) {
                $field = $fixesNode->addChild('champTableauFixe');
                $field->addChild('code', $fieldCode);
                $field->addChild('valeur', $this->formatXmlValue($value));
            } else {
                if (!empty($value) && $value != 0) {
                    $field = $varsNode->addChild('champTableauVariable');
                    $field->addChild('colonne', $fieldCode);
                    $field->addChild('ligne', $mapping->pos_ligne);
                    $field->addChild('valeur', $this->formatXmlValue($value));
                }
            }
        }

        $dom = new \DOMDocument("1.0");
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = true;
        $dom->loadXML($xml->asXML());
        return $dom->saveXML();
    }

    private function formatXmlValue($value)
    {
        if (is_numeric($value)) return (string)round((float)$value);
        return (string)$value;
    }

    /**
     * Génère un PDF.
     */
    public function generatePdf($exerciceId, $companyId, $pageCode = null, $allPages = [])
    {
        $exercice = ExerciceComptable::find($exerciceId);
        $company = \App\Models\Company::find($companyId);
        
        View::share('isExport', true);
        View::share('isPdf', true);

        $pagesToRender = [];
        if ($pageCode) {
            $data = $this->getPageData($exerciceId, $pageCode);
            $viewName = 'reporting.liasse.pages.' . strtolower($pageCode);
            $html = View::exists($viewName) ? view($viewName, compact('data'))->render() : "Contenu indisponible.";
            $pagesToRender[] = ['title' => $pageCode, 'html' => $html];
        } else {
            foreach($allPages as $p) {
                if (in_array($p['code'], ['BALANCE', 'GRAND_LIVRE'])) continue;
                $data = $this->getPageData($exerciceId, $p['code']);
                $viewName = 'reporting.liasse.pages.' . strtolower($p['code']);
                if (View::exists($viewName)) {
                    $pagesToRender[] = ['title' => $p['title'], 'html' => view($viewName, compact('data'))->render()];
                }
            }
        }

        $pdf = Pdf::loadView('reporting.liasse.pdf_layout', [
            'pages' => $pagesToRender,
            'company' => $company,
            'exercice' => $exercice,
            'title' => count($pagesToRender) === 1 ? $pagesToRender[0]['title'] : "Liasse Fiscale Complète"
        ]);
        
        $pdf->setPaper('a4', 'portrait');
        $pdf->setOptions(['isHtml5ParserEnabled' => true, 'isRemoteEnabled' => true]);
        return $pdf->output();
    }

    /**
     * Génère un Excel.
     */
    public function generateExcel($exerciceId, $companyId, $pageCode = null, $allPages = [])
    {
        $filename = ($pageCode ?: 'liasse_fiscale_complete') . "_" . date('Ymd') . ".xlsx";
        if ($pageCode) {
            $data = $this->getPageData($exerciceId, $pageCode);
            return Excel::download(new class($data, 'reporting.liasse.pages.' . strtolower($pageCode)) implements \Maatwebsite\Excel\Concerns\FromView {
                private $data; private $view;
                public function __construct($data, $view) { $this->data = $data; $this->view = $view; }
                public function view(): \Illuminate\Contracts\View\View { return view($this->view, ['data' => $this->data, 'isExcel' => true]); }
            }, $filename);
        }
        // ... (Logique multi-sheets identique ...)
        return response()->json(['message' => 'Export complet non implémenté pour cet exemple.']);
    }
}
