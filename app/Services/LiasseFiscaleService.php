<?php

namespace App\Services;

use App\Models\LiasseData;
use App\Models\ExerciceComptable;
use Illuminate\Support\Facades\DB;

class LiasseFiscaleService
{
    protected $reportingService;

    public function __construct(\App\Services\AccountingReportingService $reportingService)
    {
        $this->reportingService = $reportingService;
    }

    public function getSummaryData($exerciceId, $companyId)
    {
        $bilan = $this->reportingService->getBilanData($exerciceId, $companyId);
        $sig = $this->reportingService->getSIGData($exerciceId, $companyId);

        return [
            'total_actif' => $bilan['total_net'] ?? 0,
            'total_passif' => $bilan['passif']['total_n'] ?? 0,
            'resultat_net' => $sig['résultat_net']['n'] ?? 0,
        ];
    }

    /**
     * Récupère les données pour une page spécifique de la liasse (N et N-1).
     */
    public function getPageData($exerciceId, $pageCode)
    {
        $exercice = ExerciceComptable::find($exerciceId);
        if (!$exercice) return [];

        // 1. Récupérer l'exercice N-1
        $prevExercice = ExerciceComptable::where('company_id', $exercice->company_id)
            ->where('date_fin', '<', $exercice->date_debut)
            ->orderBy('date_fin', 'desc')
            ->first();

        // 2. Récupérer les données automatisées N
        $automatedN = $this->calculateAutomatedData($exercice, $pageCode);
        
        // 3. Récupérer les données automatisées N-1
        $automatedN1 = $prevExercice ? $this->calculateAutomatedData($prevExercice, $pageCode) : [];

        // 4. Récupérer les données manuelles N
        $manualN = LiasseData::where('exercice_id', $exerciceId)
            ->where('page_code', $pageCode)
            ->pluck('value', 'field_code')
            ->toArray();

        // 5. Fusionner tout ça
        // Pour les clés N-1, on ajoute un suffixe _N1
        $result = $automatedN;
        foreach ($automatedN1 as $code => $value) {
            $result[$code . '_N1'] = $value;
        }

        return array_merge($result, $manualN);
    }

    /**
     * Calcule les données automatisées à partir de la balance via ReportingService.
     */
    private function calculateAutomatedData($exercice, $pageCode)
    {
        $companyId = $exercice->company_id;
        $exerciceId = $exercice->id;

        if ($pageCode === 'BALANCE') {
            return $this->reportingService->getBalanceData($exerciceId, $companyId);
        }

        if ($pageCode === 'GRAND_LIVRE') {
            return $this->reportingService->getLedgerData($exerciceId, $companyId);
        }

        if ($pageCode === 'BILAN_ACTIF') {
            $bilan = $this->reportingService->getBilanData($exerciceId, $companyId);
            return $this->mapActifToLiasse($bilan['actif']);
        }

        if ($pageCode === 'BILAN_PASSIF') {
            $bilan = $this->reportingService->getBilanData($exerciceId, $companyId);
            return $this->mapPassifToLiasse($bilan['passif']);
        }

        if ($pageCode === 'RESULTAT') {
            $sig = $this->reportingService->getSIGData($exerciceId, $companyId);
            return $this->mapSIGToLiasse($sig);
        }

        if ($pageCode === 'FICHE_R1') {
            return [
                'ZA1' => $exercice->date_debut->format('d/m/Y'),
                'ZA2' => $exercice->date_fin->format('d/m/Y'),
                'ZA3' => 12, // Par défaut
            ];
        }

        if ($pageCode === 'TFT') {
            $tft = $this->reportingService->getTFTData($exerciceId, $companyId);
            return $this->mapTFTToLiasse($tft);
        }

        return [];
    }

    private function mapActifToLiasse($actif)
    {
        $map = [];
        $immo = $actif['immobilise']['subcategories'] ?? [];
        
        // Helper to safely get triplet values
        $getTriplet = function($data) {
            return [
                'brut' => $data['brut'] ?? 0,
                'amort' => $data['amort'] ?? 0,
                'net' => $data['net'] ?? 0
            ];
        };

        // AD - Immobilisations incorporelles
        $v = $getTriplet($immo['immo_incorp'] ?? []);
        $map['AD_brut'] = $v['brut'];
        $map['AD_amort'] = $v['amort'];
        $map['AD_net'] = $v['net'];

        // AI - Immobilisations corporelles
        $v = $getTriplet($immo['immo_corp'] ?? []);
        $map['AI_brut'] = $v['brut'];
        $map['AI_amort'] = $v['amort'];
        $map['AI_net'] = $v['net'];

        // AQ - Immobilisations financières
        $v = $getTriplet($immo['immo_fin'] ?? []);
        $map['AQ_brut'] = $v['brut'];
        $map['AQ_amort'] = $v['amort'];
        $map['AQ_net'] = $v['net'];

        // AZ - TOTAL ACTIF IMMOBILISE
        $map['AZ_brut'] = $actif['immobilise']['total_brut'] ?? 0;
        $map['AZ_amort'] = $actif['immobilise']['total_amort'] ?? 0;
        $map['AZ_net'] = $actif['immobilise']['total_net'] ?? 0;

        // BB - STOCKS
        $v = $getTriplet($actif['circulant']['subcategories']['stocks'] ?? []);
        $map['BB_brut'] = $v['brut'];
        $map['BB_amort'] = $v['amort'];
        $map['BB_net'] = $v['net'];

        // BK - TOTAL ACTIF CIRCULANT
        $map['BK_brut'] = $actif['circulant']['total_brut'] ?? 0;
        $map['BK_amort'] = $actif['circulant']['total_amort'] ?? 0;
        $map['BK_net'] = $actif['circulant']['total_net'] ?? 0;

        // BT - TOTAL TRESORERIE ACTIF
        $map['BT_brut'] = $actif['tresorerie']['total_brut'] ?? 0;
        $map['BT_amort'] = $actif['tresorerie']['total_amort'] ?? 0;
        $map['BT_net'] = $actif['tresorerie']['total_net'] ?? 0;

        // BZ - TOTAL GENERAL
        $map['BZ_brut'] = $actif['total_brut'] ?? 0;
        $map['BZ_amort'] = $actif['total_amort'] ?? 0;
        $map['BZ_net'] = $actif['total_net'] ?? 0;
        
        return $map;
    }

    private function mapPassifToLiasse($passif)
    {
        $map = [];
        $capitaux = $passif['capitaux']['subcategories'] ?? [];
        
        // CA - Capital
        $map['CA'] = $capitaux['capital']['total'] ?? 0;
        
        // CF - Réserves (Libres + Indisponibles)
        $map['CF'] = $capitaux['reserves']['total'] ?? 0;
        
        // CG - Report à nouveau
        $map['CG'] = $capitaux['report']['total'] ?? 0;
        
        // CJ - Résultat net
        $map['CJ'] = $capitaux['resultat']['total'] ?? 0;
        
        // CP - TOTAL CAPITAUX PROPRES
        $map['CP'] = $passif['capitaux']['total'] ?? 0;
        
        // DF - Emprunts et dettes financières
        $map['DF'] = $passif['dettes_fin']['total'] ?? 0;
        
        // DP - TOTAL DETTES FINANCIERES
        $map['DP'] = $passif['dettes_fin']['total'] ?? 0;
        
        // FB - Fournisseurs d'exploitation
        $map['FB'] = $passif['passif_circ']['subcategories']['fournisseurs']['total'] ?? 0;
        
        // FG - TOTAL PASSIF CIRCULANT
        $map['FG'] = $passif['passif_circ']['total'] ?? 0;
        
        // GZ - TOTAL GENERAL PASSIF
        $map['GZ'] = $passif['total'] ?? 0;
        
        return $map;
    }

    private function mapSIGToLiasse($sig)
    {
        $map = [];
        $map['XA'] = $sig['ventes_marchandises']['n'] ?? 0;
        $map['XB'] = $sig['achats_marchandises']['n'] ?? 0;
        $map['XC'] = $sig['marge_commerciale']['n'] ?? 0;
        $map['XD'] = $sig['production_valorisée']['n'] ?? 0;
        $map['XE'] = $sig['consommation_exercice']['n'] ?? 0;
        $map['XF'] = $sig['valeur_ajoutée']['n'] ?? 0;
        $map['XG'] = $sig['subventions_exploitation']['n'] ?? 0;
        $map['XH'] = $sig['charges_personnel']['n'] ?? 0;
        $map['XI'] = $sig['ebe']['n'] ?? 0;
        $map['XJ'] = $sig['reprises_amortissements']['n'] ?? 0;
        $map['XK'] = $sig['dotations_amortissements']['n'] ?? 0;
        $map['XL'] = $sig['resultat_exploitation']['n'] ?? 0;
        $map['XM'] = $sig['revenus_financiers']['n'] ?? 0;
        $map['XN'] = $sig['charges_financieres']['n'] ?? 0;
        $map['XO'] = $sig['resultat_financier']['n'] ?? 0;
        $map['XP'] = $sig['resultat_activites_ordinaires']['n'] ?? 0;
        $map['XQ'] = $sig['produits_hao']['n'] ?? 0;
        $map['XR'] = $sig['charges_hao']['n'] ?? 0;
        $map['XS'] = $sig['resultat_hao']['n'] ?? 0;
        $map['XT'] = $sig['participation_travailleurs']['n'] ?? 0;
        $map['XU'] = $sig['impots_resultat']['n'] ?? 0;
        $map['XV'] = $sig['résultat_net']['n'] ?? 0;
        
        return $map;
    }

    private function mapTFTToLiasse($tft)
    {
        $map = [];
        $op = $tft['operationnel'] ?? [];
        $inv = $tft['investissement'] ?? [];
        $fin = $tft['financement'] ?? [];
        $tre = $tft['tresorerie'] ?? [];

        // Flux d'exploitation
        $map['ZA'] = $op['caf'] ?? 0;
        $map['ZB'] = $op['variation_bfr'] ?? 0;
        $map['ZC'] = $op['total'] ?? 0;
        
        // Flux d'investissement
        $map['ZD'] = $inv['acquisitions'] ?? 0;
        $map['ZE'] = $inv['cessions'] ?? 0;
        $map['ZF'] = $inv['total'] ?? 0;
        
        // Flux de financement
        $map['ZG'] = $fin['capital'] ?? 0;
        $map['ZH'] = $fin['emprunts'] ?? 0;
        $map['ZI'] = $fin['dividendes'] ?? 0;
        $map['ZJ'] = $fin['total'] ?? 0;
        
        // Trésorerie
        $map['ZK'] = $tre['variation_nette'] ?? 0;
        $map['ZL'] = $tre['initiale'] ?? 0;
        $map['ZM'] = $tre['finale'] ?? 0;
        
        return $map;
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
     * Incrémente un code alphabétique (AA -> AB, AZ -> BA).
     */
    private function incrementCode($code)
    {
        $len = strlen($code);
        $lastChar = substr($code, -1);
        $prefix = substr($code, 0, -1);
        
        if ($lastChar === 'Z') {
            if ($len === 1) return 'AA';
            return $this->incrementCode($prefix) . 'A';
        }
        
        return $prefix . chr(ord($lastChar) + 1);
    }

    /**
     * Génère le fichier XML au format e-SINTAX (DGI Côte d'Ivoire).
     */
    public function generateXml($exerciceId, $companyId)
    {
        $exercice = \App\Models\ExerciceComptable::find($exerciceId);
        $company = \App\Models\Company::find($companyId);
        
        if (!$exercice || !$company) {
            throw new \Exception("Exercice ou Entreprise introuvable.");
        }

        $xml = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><EDI/>');
        $info = $xml->addChild('informations');
        $info->addChild('type', 'NO'); 
        $info->addChild('ncc', $company->ncc ?? '0000000X');
        $info->addChild('exercice', $exercice->date_debut->format('Y'));
        
        $fixesNode = $xml->addChild('champsTableauxFixes');
        $varsNode = $xml->addChild('champsTableauxVariables');

        // Récupérer tous les codes de mappage
        $mappings = \App\Models\LiasseMapping::all();
        
        // Groupes de pages pour optimiser le chargement des data
        $pageDataCache = [];

        foreach ($mappings as $mapping) {
            $pageCode = $mapping->code_tableau;
            
            // On charge les données de la page si pas déjà en cache
            if (!isset($pageDataCache[$pageCode])) {
                $pageDataCache[$pageCode] = $this->getPageData($exerciceId, $pageCode);
            }
            
            $data = $pageDataCache[$pageCode];
            $value = "";

            // Résolution de la valeur à partir du mapping
            // Le mapping contient souvent des codes comme 'ZA1', 'XB', etc.
            // On vérifie si on a une correspondance dans $data
            $fieldCode = $mapping->code_champ_dgi; // Ex: NO_FR1_ZA1_1
            
            // Extraire le code court (ex: ZA1) du code long DGI
            // Pattern: NO_{TABLEAU}_{CODE}_{COL}
            $parts = explode('_', $fieldCode);
            $shortCode = $parts[count($parts)-2] ?? null;
            $colNum = $parts[count($parts)-1] ?? '1';

            if ($shortCode) {
                // Suffixes pour colonnes
                $suffix = "";
                if ($mapping->type === 'Tableau' && in_array($mapping->code_tableau, ['ACTIF', 'PASSIF', 'RESULTAT', 'TFT'])) {
                    if ($colNum == '1') $suffix = '_brut'; // Pour Actif
                    if ($colNum == '2') $suffix = '_amort'; // Pour Actif
                    if ($colNum == '3') $suffix = '_net'; // Pour Actif / N pour Passif
                    if ($colNum == '4') $suffix = '_N1'; // N-1
                }
                
                // Cas spécifique pour Passif/Resultat qui n'ont pas de brut/amort
                if (in_array($mapping->code_tableau, ['PASSIF', 'RESULTAT', 'TFT'])) {
                   if ($colNum == '1') $suffix = ''; // Valeur N
                   if ($colNum == '2') $suffix = '_N1'; // Valeur N-1
                }

                $value = $data[$shortCode . $suffix] ?? $data[$shortCode] ?? "";
            }

            if ($mapping->pos_ligne === null || $mapping->pos_ligne == 0) {
                // Champ FIXE
                $field = $fixesNode->addChild('champTableauFixe');
                $field->addChild('code', $fieldCode);
                $field->addChild('valeur', $this->formatXmlValue($value));
            } else {
                // Champ VARIABLE (si valeur non vide)
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
        @$dom->loadXML($xml->asXML());
        
        return $dom->saveXML();
    }

    private function formatXmlValue($value)
    {
        if (is_numeric($value)) {
            return (string)round((float)$value);
        }
        return (string)$value;
    }

    /**
     * Génère un PDF (une seule page ou toute la liasse).
     */
    public function generatePdf($exerciceId, $companyId, $pageCode = null, $allPages = [])
    {
        $exercice = \App\Models\ExerciceComptable::find($exerciceId);
        $company = \App\Models\Company::find($companyId);
        
        // Partager le flag d'export pour les vues Blade
        \Illuminate\Support\Facades\View::share('isExport', true);
        \Illuminate\Support\Facades\View::share('isPdf', true);

        $pagesToRender = [];
        // ... (suite identique ...)
        
        if ($pageCode) {
            // Une seule page
            $title = "Rapport";
            foreach($allPages as $p) {
                if ($p['code'] === $pageCode) {
                    $title = $p['title'];
                    break;
                }
            }
            
            $data = $this->getPageData($exerciceId, $pageCode);
            $viewName = 'reporting.liasse.pages.' . strtolower($pageCode);
            
            if (\Illuminate\Support\Facades\View::exists($viewName)) {
                $html = view($viewName, compact('data'))->render();
            } else {
                $html = "<p>Contenu non disponible pour " . $pageCode . "</p>";
            }
            
            $pagesToRender[] = ['title' => $title, 'html' => $html];
        } else {
            // Toute la liasse (Sauf Balance et Grand Livre peut-être, car trop volumineux)
            foreach($allPages as $p) {
                if (in_array($p['code'], ['BALANCE', 'GRAND_LIVRE'])) continue;
                
                $data = $this->getPageData($exerciceId, $p['code']);
                $viewName = 'reporting.liasse.pages.' . strtolower($p['code']);
                
                if (\Illuminate\Support\Facades\View::exists($viewName)) {
                    $html = view($viewName, compact('data'))->render();
                    $pagesToRender[] = ['title' => $p['title'], 'html' => $html];
                }
            }
        }

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('reporting.liasse.pdf_layout', [
            'pages' => $pagesToRender,
            'company' => $company,
            'exercice' => $exercice,
            'title' => count($pagesToRender) === 1 ? $pagesToRender[0]['title'] : "Liasse Fiscale Complète"
        ]);
        
        // Configuration DomPDF pour support HTML5, caractères spéciaux, etc.
        $pdf->setPaper('a4', 'portrait');
        $pdf->setOptions(['isHtml5ParserEnabled' => true, 'isRemoteEnabled' => true]);

        return $pdf->output();
    }

    /**
     * Génère un fichier Excel.
     */
    public function generateExcel($exerciceId, $companyId, $pageCode = null, $allPages = [])
    {
        $filename = ($pageCode ?: 'liasse_fiscale_complete') . "_" . date('Ymd') . ".xlsx";

        if ($pageCode) {
            // Une seule feuille
            $data = $this->getPageData($exerciceId, $pageCode);
            $viewName = 'reporting.liasse.pages.' . strtolower($pageCode);
            
            return \Maatwebsite\Excel\Facades\Excel::download(new class($data, $viewName) implements \Maatwebsite\Excel\Concerns\FromView {
                private $data;
                private $view;
                public function __construct($data, $view) { $this->data = $data; $this->view = $view; }
                public function view(): \Illuminate\Contracts\View\View {
                    return view($this->view, ['data' => $this->data, 'isExcel' => true]);
                }
            }, $filename);
        } else {
            // Multi-feuilles
            $sheets = [];
            foreach ($allPages as $p) {
                // Exclure Balance et Grand Livre (trop lourds pour Excel par feuilles multiples ici)
                if (in_array($p['code'], ['BALANCE', 'GRAND_LIVRE'])) continue;

                $data = $this->getPageData($exerciceId, $p['code']);
                $viewName = 'reporting.liasse.pages.' . strtolower($p['code']);
                
                if (\Illuminate\Support\Facades\View::exists($viewName)) {
                    $sheets[] = new class($data, $viewName, $p['title']) implements \Maatwebsite\Excel\Concerns\FromView, \Maatwebsite\Excel\Concerns\WithTitle {
                        private $data;
                        private $view;
                        private $title;
                        public function __construct($data, $view, $title) { 
                            $this->data = $data; 
                            $this->view = $view; 
                            // Limiter le titre à 31 caractères (limite Excel)
                            $this->title = substr(str_replace([':', '/', '\\', '?', '*', '[', ']'], ' ', $title), 0, 31);
                        }
                        public function view(): \Illuminate\Contracts\View\View {
                            return view($this->view, ['data' => $this->data, 'isExcel' => true]);
                        }
                        public function title(): string { return $this->title; }
                    };
                }
            }
            
            return \Maatwebsite\Excel\Facades\Excel::download(new class($sheets) implements \Maatwebsite\Excel\Concerns\WithMultipleSheets {
                private $sheets;
                public function __construct($sheets) { $this->sheets = $sheets; }
                public function sheets(): array { return $this->sheets; }
            }, $filename);
        }
    }
}
