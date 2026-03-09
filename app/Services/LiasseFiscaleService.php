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
        $map['XA'] = $sig['ventes_marchandises'] ?? 0;
        $map['XB'] = $sig['achats_marchandises'] ?? 0;
        $map['XC'] = $sig['marge_commerciale'] ?? 0;
        $map['XF'] = $sig['chiffre_affaires'] ?? 0;
        $map['XK'] = $sig['valeur_ajoutee'] ?? 0;
        $map['XO'] = $sig['ebe'] ?? 0;
        $map['XR'] = $sig['resultat_exploitation'] ?? 0;
        $map['XW'] = $sig['resultat_financier'] ?? 0;
        $map['XA_TOTAL'] = $sig['resultat_activites_ordinaires'] ?? 0;
        $map['XM'] = $sig['resultat_net'] ?? 0;
        $map['XG_TOTAL'] = $sig['resultat_net'] ?? 0;
        
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
     * Génère le fichier XML au format e-SINTAX.
     */
    public function generateXml($exerciceId, $companyId)
    {
        $exercice = ExerciceComptable::find($exerciceId);
        $company = \App\Models\Company::find($companyId);
        
        // Récupérer TOUTES les données de la liasse (Auto + Manuelles)
        $allData = [];
        $pageCodes = [
            'BILAN_ACTIF', 'BILAN_PASSIF', 'RESULTAT', 'TFT',
            'NOTE_1', 'NOTE_2', 'NOTE_3' // ... etc
        ];

        foreach ($pageCodes as $code) {
            $allData = array_merge($allData, $this->getPageData($exerciceId, $code));
        }

        // Création du XML
        $xml = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><liasse_fiscale/>');
        
        $entete = $xml->addChild('entete');
        $entete->addChild('ifu', $company->ifu ?? 'N/A');
        $entete->addChild('societe', $company->name);
        $entete->addChild('exercice', $exercice->intitule);
        
        $corps = $xml->addChild('donnees');
        foreach ($allData as $code => $value) {
            $corps->addChild('f_' . $code, $value);
        }

        return $xml->asXML();
    }
}
