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
        // Utilisation du moteur de calcul pour les codes de synthèse
        // BZ = Total Actif, GZ = Total Passif, XS = Résultat Net (Fiche SIG)
        $actif = $this->calculateValueForRange('1,2,3,4,5', $exerciceId, $companyId); // Très large pour test
        
        // Pour être plus précis on devrait prendre les totaux officiels
        $totalActif = $this->calculateValueForRange('2,3,4,5', $exerciceId, $companyId)['net'];
        $totalPassif = $this->calculateValueForRange('1,4,5', $exerciceId, $companyId)['net']; // Simplifié
        
        // Résultat Net (Produits - Charges)
        $produits = $this->calculateValueForRange('7', $exerciceId, $companyId)['net'];
        $charges = $this->calculateValueForRange('6', $exerciceId, $companyId)['net'];
        $resultat = $produits - $charges;

        return [
            'total_actif' => $totalActif,
            'total_passif' => $totalPassif,
            'resultat_net' => $resultat,
        ];
    }

    /**
     * Récupère les données pour une page spécifique de la liasse (N et N-1).
     */
    public function getPageData($exerciceId, $pageCode)
    {
        $exercice = ExerciceComptable::find($exerciceId);
        if (!$exercice) return [];

        $companyId = $exercice->company_id;

        // 1. Récupérer l'exercice N-1
        $prevExercice = ExerciceComptable::where('company_id', $companyId)
            ->where('date_fin', '<', $exercice->date_debut)
            ->orderBy('date_fin', 'desc')
            ->first();

        // 2. Récupérer les mappings pour cette page
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

        $result = [];

        foreach ($mappings as $mapping) {
            $fieldCode = $mapping->code_champ_dgi;
            $parts = explode('_', $fieldCode);
            $shortCode = $parts[count($parts)-2] ?? null;
            if (!$shortCode) continue;

            if ($mapping->account_range) {
                // Valeurs N
                $valuesN = $this->calculateValueForRange($mapping->account_range, $exerciceId, $companyId);
                
                // Valeurs N-1
                $valuesN1 = $prevExercice ? $this->calculateValueForRange($mapping->account_range, $prevExercice->id, $companyId) : ['net' => 0];

                if ($pageCode === 'BILAN_ACTIF') {
                    $result[$shortCode . '_brut'] = $valuesN['brut'];
                    $result[$shortCode . '_amort'] = $valuesN['amort'];
                    $result[$shortCode . '_net'] = $valuesN['net'];
                    $result[$shortCode . '_net_N1'] = $valuesN1['net'];
                } else {
                    $result[$shortCode] = $valuesN['net'];
                    $result[$shortCode . '_N1'] = $valuesN1['net'];
                }
            }
        }

        // 3. Récupérer les données manuelles
        $manualData = LiasseData::where('exercice_id', $exerciceId)
            ->where('company_id', $companyId)
            ->where('page_code', $pageCode)
            ->pluck('value', 'field_code')
            ->toArray();

        return array_merge($result, $manualData);
    }

    /**
     * Moteur de calcul par plage de comptes.
     */
    public function calculateValueForRange($range, $exerciceId, $companyId)
    {
        $prefixes = explode(',', $range);
        
        $query = EcritureComptable::where('company_id', $companyId)
            ->where('exercices_comptables_id', $exerciceId)
            ->whereHas('planComptable', function($q) use ($prefixes) {
                $q->where(function($sq) use ($prefixes) {
                    foreach ($prefixes as $prefix) {
                        $sq->orWhere('numero_de_compte', 'like', trim($prefix) . '%');
                    }
                });
            });

        $data = $query->selectRaw('SUM(debit) as total_debit, SUM(credit) as total_credit')->first();
        
        $totalDebit = $data->total_debit ?? 0;
        $totalCredit = $data->total_credit ?? 0;
        
        // Convention SYSCOHADA :
        // Actif / Charges : Solde Débiteur (D - C)
        // Passif / Produits : Solde Créditeur (C - D)
        
        $firstDigit = substr(trim($range), 0, 1);
        if (in_array($firstDigit, ['1', '4', '7'])) { // Passif (1, 4-crédit) et Produits (7)
            $brut = $totalCredit - $totalDebit;
        } else {
            $brut = $totalDebit - $totalCredit;
        }

        $amort = 0;
        // Calcul des amortissements (28) et dépréciations (29) pour les immobilisations (2)
        if ($firstDigit === '2') {
             $amortQuery = EcritureComptable::where('company_id', $companyId)
                ->where('exercices_comptables_id', $exerciceId)
                ->whereHas('planComptable', function($q) use ($prefixes) {
                    $q->where(function($sq) use ($prefixes) {
                        foreach ($prefixes as $prefix) {
                            $cleanPrefix = trim($prefix);
                            if (strlen($cleanPrefix) >= 1) {
                                $sq->orWhere('numero_de_compte', 'like', '28' . substr($cleanPrefix, 1) . '%');
                                $sq->orWhere('numero_de_compte', 'like', '29' . substr($cleanPrefix, 1) . '%');
                            }
                        }
                    });
                });
             $amortData = $amortQuery->selectRaw('SUM(credit) - SUM(debit) as total_amort')->first();
             $amort = $amortData->total_amort ?? 0;
        }

        return [
            'brut' => $brut,
            'amort' => $amort,
            'net' => $brut - $amort
        ];
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
