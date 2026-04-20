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
                            $amortMontant = $b->total_credit - $b->total_debit;
                            $amort += $amortMontant;
                            if (abs($amortMontant) > 0.01) {
                                $details[] = [
                                    'numero' => $n,
                                    'intitule' => $b->intitule,
                                    'solde' => -$amortMontant
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
        // Si c'est un code SMT ou si le régime est SMT (via détection de code)
        if (str_starts_with($pageCode, 'SMT_') || in_array($pageCode, ['BILAN_ACTIF', 'BILAN_PASSIF', 'RESULTAT', 'TFT', 'CHARGES'])) {
             // Pour la liasse SMT, on redirige vers getSmtPageData si c'est un code SMT explicite
             // Mais attention, getPageData est aussi utilisé par le SN.
        }

        if ($pageCode === 'BALANCE') {
            $exercice = ExerciceComptable::find($exerciceId);
            $companyId = $exercice->company_id ?? 1;
            return $this->reportingService->getBalanceData($exerciceId, $companyId);
        }
        
        if ($pageCode === 'GRAND_LIVRE') {
            $exercice = ExerciceComptable::find($exerciceId);
            $companyId = $exercice->company_id ?? 1;
            return $this->reportingService->getLedgerData($exerciceId, $companyId);
        }

        $exercice = ExerciceComptable::find($exerciceId);
        if (!$exercice) return [];

        $companyId = $exercice->company_id;
        
        // Redirection vers SMT si le code est préfixé SMT_
        if (str_starts_with($pageCode, 'SMT_')) {
            return $this->getSmtPageData($exerciceId, $pageCode);
        }

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

        // ── Post-processing: calcul des totaux par page ──────────────────────────
        if ($pageCode === 'BILAN_ACTIF') {
            // Total Actif Immobilisé (AZ)
            $immoFields = ['AE','AF','AG','AH','AJ','AK','AL','AM','AN','AP','AR','AS'];
            $az_brut = $az_amort = $az_net = 0;
            foreach ($immoFields as $f) {
                $az_brut  += $result[$f.'_brut']  ?? 0;
                $az_amort += $result[$f.'_amort'] ?? 0;
                $az_net   += $result[$f.'_net']   ?? 0;
            }
            $result['AZ_brut']  = $az_brut;
            $result['AZ_amort'] = $az_amort;
            $result['AZ_net']   = $az_net;
            $result['AZ_net_N1'] = array_sum(array_map(fn($f) => $result[$f.'_net_N1'] ?? 0, $immoFields));
            // Total Actif Circulant (BK)
            $result['BK_brut']  = ($result['BB_brut'] ??0) + ($result['BG_brut'] ??0);
            $result['BK_amort'] = ($result['BB_amort']??0) + ($result['BG_amort']??0);
            $result['BK_net']   = ($result['BB_net']  ??0) + ($result['BG_net']  ??0);
            $result['BK_net_N1']= ($result['BB_net_N1']??0) + ($result['BG_net_N1']??0);
            // Total Trésorerie Actif (BT)
            $result['BT_brut']  = $result['BS_brut']  ?? 0;
            $result['BT_amort'] = $result['BS_amort'] ?? 0;
            $result['BT_net']   = $result['BS_net']   ?? 0;
            $result['BT_net_N1']= $result['BS_net_N1']?? 0;
            // Total Général Actif (BZ)
            $result['BZ_brut']  = $az_brut  + ($result['BK_brut'] ??0) + ($result['BT_brut'] ??0);
            $result['BZ_amort'] = $az_amort + ($result['BK_amort']??0) + ($result['BT_amort']??0);
            $result['BZ_net']   = $az_net   + ($result['BK_net']  ??0) + ($result['BT_net']  ??0);
            $result['BZ_net_N1']= ($result['AZ_net_N1']??0) + ($result['BK_net_N1']??0) + ($result['BT_net_N1']??0);
        }

        if ($pageCode === 'BILAN_PASSIF') {
            // Résultat net de l'exercice (CJ) = Produits - Charges
            $produits = $this->calculateValueForRangeFast('7', $balancesN)['net'];
            $charges  = $this->calculateValueForRangeFast('6', $balancesN)['net'];
            $result['CJ'] = $produits - $charges;
            $result['CJ_N1'] = $prevExercice
                ? ($this->calculateValueForRangeFast('7', $balancesN1)['net'] - $this->calculateValueForRangeFast('6', $balancesN1)['net'])
                : 0;
            // Total Capitaux Propres (CP)
            $result['CP']    = ($result['CA']??0) + ($result['CF']??0) + ($result['CG']??0) + ($result['CJ']??0);
            $result['CP_N1'] = ($result['CA_N1']??0) + ($result['CF_N1']??0) + ($result['CG_N1']??0) + ($result['CJ_N1']??0);
            // Total Dettes Financières (DP)
            $result['DP']    = $result['DA']    ?? 0;
            $result['DP_N1'] = $result['DA_N1'] ?? 0;
            // Total Passif Circulant (FG)
            $result['FG']    = $result['FB']    ?? 0;
            $result['FG_N1'] = $result['FB_N1'] ?? 0;
            // Total Trésorerie Passif (HP)
            $result['HP']    = $result['HA']    ?? 0;
            $result['HP_N1'] = $result['HA_N1'] ?? 0;
            // Total Général Passif (GZ)
            $result['GZ']    = ($result['CP']??0) + ($result['DP']??0) + ($result['FG']??0) + ($result['HP']??0);
            $result['GZ_N1'] = ($result['CP_N1']??0) + ($result['DP_N1']??0) + ($result['FG_N1']??0) + ($result['HP_N1']??0);
        }

        if ($pageCode === 'RESULTAT') {
            $result['detailed_sig'] = $this->reportingService->getSIGData($exerciceId, $companyId, null, true);
            // Marge commerciale (XC) = Ventes marchandises - Achats marchandises
            $result['XC'] = ($result['XA']??0) - ($result['XB']??0);
            $result['XC_N1'] = ($result['XA_N1']??0) - ($result['XB_N1']??0);
            // Valeur Ajoutée (XF) = XC + Production - Consommation
            $result['XF'] = ($result['XC']??0) + ($result['XD']??0) - ($result['XE']??0);
            $result['XF_N1'] = ($result['XC_N1']??0) + ($result['XD_N1']??0) - ($result['XE_N1']??0);
            // EBE (XI) = XF + Subventions - Charges personnel
            $result['XI'] = ($result['XF']??0) + ($result['XG']??0) - ($result['XH']??0);
            $result['XI_N1'] = ($result['XF_N1']??0) + ($result['XG_N1']??0) - ($result['XH_N1']??0);
            // Résultat Exploitation (XL) = XI + Reprises - Dotations
            $result['XL'] = ($result['XI']??0) + ($result['XJ']??0) - ($result['XK']??0);
            $result['XL_N1'] = ($result['XI_N1']??0) + ($result['XJ_N1']??0) - ($result['XK_N1']??0);
            // Résultat Financier (XO) = Revenus financiers - Frais financiers
            $result['XO'] = ($result['XM']??0) - ($result['XN']??0);
            $result['XO_N1'] = ($result['XM_N1']??0) - ($result['XN_N1']??0);
            // Résultat des AO (XP) = XL + XO
            $result['XP'] = ($result['XL']??0) + ($result['XO']??0);
            $result['XP_N1'] = ($result['XL_N1']??0) + ($result['XO_N1']??0);
            // Résultat HAO (XS) = Produits HAO - Charges HAO
            $result['XS'] = ($result['XQ']??0) - ($result['XR']??0);
            $result['XS_N1'] = ($result['XQ_N1']??0) - ($result['XR_N1']??0);
            // Résultat Net (XV) = XP + XS - Participation - IS
            $result['XV'] = ($result['XP']??0) + ($result['XS']??0) - ($result['XT']??0) - ($result['XU']??0);
            $result['XV_N1'] = ($result['XP_N1']??0) + ($result['XS_N1']??0) - ($result['XT_N1']??0) - ($result['XU_N1']??0);
        }

        if ($pageCode === 'TFT') {
            $result['detailed_tft'] = $this->reportingService->getTFTData($exerciceId, $companyId, null, true);
            // ZC = Flux opérationnels nets (ZA - ZB)
            $result['ZC'] = ($result['ZA']??0) + ($result['ZB']??0);
            // ZF = Flux investissements nets (ZE - ZD; décaiss négatif)
            $result['ZF'] = ($result['ZE']??0) - ($result['ZD']??0);
            // ZJ = Flux financement nets
            $result['ZJ'] = ($result['ZG']??0) + ($result['ZH']??0) - ($result['ZI']??0);
            // ZK = Variation nette trésorerie
            $result['ZK'] = ($result['ZC']??0) + ($result['ZF']??0) + ($result['ZJ']??0);
            // ZM = Trésorerie fin = Trésorerie début + variation
            $result['ZM'] = ($result['ZL']??0) + ($result['ZK']??0);
        }

        $manualData = LiasseData::where('exercice_id', $exerciceId)
            ->where('company_id', $companyId)
            ->where('page_code', $pageCode)
            ->pluck('value', 'field_code')
            ->toArray();

        return array_merge($result, $manualData);
    }

    public function getSmtPageData(int $exerciceId, string $pageCode): array
    {
        $exercice = ExerciceComptable::find($exerciceId);
        if (!$exercice) return [];
        $companyId  = $exercice->company_id;
        $balancesN  = $this->getAccountBalances($exerciceId, $companyId);
        $prevExercice = ExerciceComptable::where('company_id', $companyId)
            ->where('date_fin', '<', $exercice->date_debut)->orderBy('date_fin', 'desc')->first();
        $balancesN1 = $prevExercice ? $this->getAccountBalances($prevExercice->id, $companyId) : collect();

        $net   = fn($r) => $this->calculateValueForRangeFast($r, $balancesN)['net'];
        $netN1 = fn($r) => $prevExercice ? $this->calculateValueForRangeFast($r, $balancesN1)['net'] : 0;

        // Alias pour le mapping XML (compatibilité avec la table liasse_mappings)
        $xmlAliases = [
            'BILAN_ACTIF'  => 'SMT_ACTIF',
            'BILAN_PASSIF' => 'SMT_PASSIF',
            'RESULTAT'     => 'SMT_RESULTAT',
            'CHARGES'      => 'SMT_RESULTAT',
            'NOTE1A'       => 'SMT_NOTES',
            'NOTE1B'       => 'SMT_NOTES',
            'NOTE6'        => 'SMT_NOTES',
            'FR1'          => 'SMT_FICHE_IDENT',
            'FR2A'         => 'SMT_FICHE_IDENT',
            'FR2B'         => 'SMT_FICHE_IDENT',
            'FR2D'         => 'SMT_FICHE_IDENT',
        ];
        if (isset($xmlAliases[$pageCode])) {
            $pageCode = $xmlAliases[$pageCode];
        }

        // 1. SMT_FICHE_IDENT — Fusion R1 + R2 + R2D (Identification et Activités)
        if ($pageCode === 'SMT_FICHE_IDENT') {
            $company = \App\Models\Company::find($companyId);
            $manual = \App\Models\LiasseData::where('exercice_id',$exerciceId)->where('company_id',$companyId)->where('page_code',$pageCode)->pluck('value','field_code')->toArray();
            return array_merge([
                'MT_R1_A' => $company->name ?? '',
                'MT_R1_B' => $company->ncc ?? '',
                'MT_R1_C' => $company->address ?? '',
                'MT_R1_D' => $company->phone ?? '',
                'ZA1' => $exercice->date_debut->format('d/m/Y'),
                'ZA2' => $exercice->date_fin->format('d/m/Y'),
                'ZA3' => 12,
                'ZB'  => $exercice->date_fin->format('d/m/Y'),
                'ZC'  => $prevExercice ? $prevExercice->date_fin->format('d/m/Y') : '',
                'CA'  => $net('70'),
            ], $manual);
        }

        // 2. SMT_ACTIF — Bilan Actif (GB, GD, GF, GZ)
        if ($pageCode === 'SMT_ACTIF') {
            $immoData    = $this->calculateValueForRangeFast('2', $balancesN);
            $immoBrut    = $immoData['brut'];
            $immoAmort   = $immoData['amort'];
            $immoNet     = $immoBrut - $immoAmort;
            $stocks      = $net('3');
            // Créances = clients + autres débiteurs (409=avances fourn, 41, 42, 43, 44, 45, 46, 47, 48)
            $creances    = $net('409,41,42,43,44,45,46,47,48');
            // Trésorerie active: banques (52,53), caisse (57), virements (58), valeurs à encaisser (50)
            $treso_actif = $net('50,52,53,57,58');
            $totalActif  = $immoNet + $stocks + $creances + $treso_actif;
            $totalActifN1 = $prevExercice
                ? ($this->calculateValueForRangeFast('2', $balancesN1)['net'] + $netN1('3') + $netN1('409,41,42,43,44,45,46,47,48') + $netN1('50,52,53,57,58'))
                : 0;
            return compact('immoBrut','immoAmort','immoNet','stocks','creances','treso_actif','totalActif','totalActifN1');
        }

        // 3. SMT_PASSIF — Bilan Passif (HA, HB, HD, HZ)
        if ($pageCode === 'SMT_PASSIF') {
            $capital     = $net('101,102,103,104,105,106,107,108,109');
            $reserves    = $net('111,112,113,114,115,116,117,118,119');
            $report      = $net('121,122,129');
            $resultat    = $net('7') - $net('6');
            $capitauxPropres = $capital + $reserves + $report + $resultat;
            $dettes_fin  = $net('161,162,163,164,165,166,168');
            // Dettes exploitation: fournisseurs (40) + dettes fiscales/sociales (42,43,44,45) + autres créditeurs (46,47,48)
            $dettes_exp  = $net('401,402,403,404,405,408,409');
            $dettes_fisc = $net('421,422,423,424,425,426,427,428,431,432,433,434,435,436,437,438,441,442,443,444,445,446,447,448,462');
            $treso_passif = $net('521,522,523,524,525,526,527,528,529,561,562,563,564,565,566,567,568,569');
            $totalPassif = $capitauxPropres + $dettes_fin + $dettes_exp + $dettes_fisc + $treso_passif;
            $totalPassifN1 = $prevExercice
                ? ($netN1('101,102,103,104,105,106,107,108,109') + $netN1('111,112,113,114,115,116,117,118,119') + $netN1('121,122,129')
                   + ($netN1('7') - $netN1('6')) + $netN1('161,162,163,164,165,166,168') + $netN1('401,402,403,404,405,408,409'))
                : 0;
            return compact('capital','reserves','report','resultat','capitauxPropres','dettes_fin','dettes_exp','dettes_fisc','treso_passif','totalPassif','totalPassifN1');
        }

        // 4. SMT_RESULTAT — Compte de Résultat (KA→KZ)
        if ($pageCode === 'SMT_RESULTAT') {
            $total_produits  = $net('7');
            $total_charges   = $net('6');
            $achats          = $net('601,602,603,604,605,606,607,608,609');
            $services_ext    = $net('61,62');
            $charges_pers    = $net('641,642,643,644,645,646,647,648,649,651,652,653,654,655,656,657,658,659');
            $impots_taxes    = $net('631,632,633,634,635,636,637,638,639,646,647');
            $autres_charges  = $net('67,68,69');
            
            $ca = $net('701,702,703,704,705,706,707,708,709,71,72,73,74,75');
            $autres_produits = $net('76,77,78,79');

            return [
                'CA'              => $ca,
                'autres_produits' => $autres_produits,
                'total_produits'  => $total_produits,
                'achats'          => $achats,
                'services_ext'    => $services_ext,
                'charges_pers'    => $charges_pers,
                'impots_taxes'    => $impots_taxes,
                'autres_charges'  => $autres_charges,
                'total_charges'   => $total_charges,
                'resultat_net'    => $total_produits - $total_charges,
                'total_produits_N1' => $netN1('7'),
                'total_charges_N1'  => $netN1('6'),
                'resultat_exercice_N1' => $netN1('7') - $netN1('6'),
            ];
        }

        // 5. SMT_TRESO_ENC — Trésorerie (Encaissements)
        if ($pageCode === 'SMT_TRESO_ENC') {
            $manual = \App\Models\LiasseData::where('exercice_id',$exerciceId)->where('company_id',$companyId)->where('page_code',$pageCode)->pluck('value','field_code')->toArray();
            // Encaissements = toutes les ventes et produits reçus
            $enc_ventes = $net('701,702,703,704,705,706,707,708,709');
            $enc_divers = $net('71,72,73,74,75,76,77,78,79');
            $enc_total  = $enc_ventes + $enc_divers;
            return array_merge([
                'enc_ventes' => $enc_ventes,
                'enc_divers' => $enc_divers,
                'enc_total'  => $enc_total,
            ], $manual);
        }

        // 6. SMT_TRESO_DEC — Trésorerie (Décaissements)
        if ($pageCode === 'SMT_TRESO_DEC') {
            $manual = \App\Models\LiasseData::where('exercice_id',$exerciceId)->where('company_id',$companyId)->where('page_code',$pageCode)->pluck('value','field_code')->toArray();
            $dec_achats   = $net('601,602,603,604,605,606,607,608,609'); // tous achats
            $dec_services = $net('61,62');                                // transports + services ext
            $dec_pers     = $net('641,642,643,644,645,646,647,648,649,651,652,653,654,655,656');
            $dec_impots   = $net('631,632,633,634,635,636,637,638,639');
            $dec_autres   = $net('67,68,69');
            $dec_total    = $dec_achats + $dec_services + $dec_pers + $dec_impots + $dec_autres;
            return array_merge([
                'dec_achats'   => $dec_achats,
                'dec_services' => $dec_services,
                'dec_pers'     => $dec_pers,
                'dec_impots'   => $dec_impots,
                'dec_autres'   => $dec_autres,
                'dec_total'    => $dec_total,
            ], $manual);
        }

        // 7. SMT_NOTES — Notes Annexes Consolidées
        if ($pageCode === 'SMT_NOTES') {
            $immoData = $this->calculateValueForRangeFast('2', $balancesN);
            $manual = \App\Models\LiasseData::where('exercice_id',$exerciceId)->where('company_id',$companyId)->where('page_code',$pageCode)->pluck('value','field_code')->toArray();
            return array_merge([
                'immoBrut'     => $immoData['brut'],
                'immoAmort'    => $immoData['amort'],
                'charges_pers' => $net('64,65,66'),
            ], $manual);
        }

        // 8. SMT_FISCAL — Passage au Résultat Fiscal
        if ($pageCode === 'SMT_FISCAL') {
            $resultat_comptable = $net('7') - $net('6');
            $manual = \App\Models\LiasseData::where('exercice_id',$exerciceId)->where('company_id',$companyId)->where('page_code',$pageCode)->pluck('value','field_code')->toArray();
            return array_merge(compact('resultat_comptable'), $manual);
        }

        return [];
    }

    public function saveManualData($exerciceId, $companyId, $pageCode, $data)
    {
        if (empty($data) || !is_array($data)) return;

        foreach ($data as $fieldCode => $value) {
            \App\Models\LiasseData::updateOrCreate(
                [
                    'exercice_id' => $exerciceId,
                    'company_id'  => $companyId,
                    'page_code'   => $pageCode,
                    'field_code'  => $fieldCode,
                ],
                ['value' => $value]
            );
        }
    }

    public function formatXmlValue($value)
    {
        if (is_numeric($value)) return (string)round((float)$value);
        return (string)$value;
    }

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
            $html = View::exists($viewName) ? view($viewName, compact('data', 'company', 'exercice'))->render() : "Contenu indisponible.";
            $pagesToRender[] = ['title' => $pageCode, 'html' => $html];
        } else {
            foreach($allPages as $p) {
                if (in_array($p['code'], ['BALANCE', 'GRAND_LIVRE'])) continue;
                $data = $this->getPageData($exerciceId, $p['code']);
                $viewName = 'reporting.liasse.pages.' . strtolower($p['code']);
                if (View::exists($viewName)) {
                    $pagesToRender[] = ['title' => $p['title'], 'html' => view($viewName, compact('data', 'company', 'exercice'))->render()];
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

    public function generateExcel($exerciceId, $companyId, $pageCode = null, $allPages = [])
    {
        $filename = ($pageCode ?: 'liasse_fiscale_complete') . "_" . date('Ymd') . ".xlsx";
        $exercice = ExerciceComptable::find($exerciceId);
        $company = \App\Models\Company::find($companyId);

        // Export mono-feuille si un code de page est spécifié
        if ($pageCode) {
            $data = $this->getPageData($exerciceId, $pageCode);
            $view = 'reporting.liasse.pages.' . strtolower($pageCode);
            
            return Excel::download(new class($data, $view, $company, $exercice) implements \Maatwebsite\Excel\Concerns\FromView {
                private $data; private $view; private $company; private $exercice;
                public function __construct($data, $view, $company, $exercice) { 
                    $this->data = $data; $this->view = $view; 
                    $this->company = $company; $this->exercice = $exercice;
                }
                public function view(): \Illuminate\Contracts\View\View { 
                    try {
                        $html = view($this->view, [
                            'data' => $this->data, 
                            'company' => $this->company, 
                            'exercice' => $this->exercice, 
                            'isExcel' => true
                        ])->render();
                        $clean = preg_replace('/>\s+</', '><', str_replace(["\r", "\n"], '', trim($html)));
                        $safe = mb_convert_encoding($clean, 'HTML-ENTITIES', 'UTF-8');
                        file_put_contents(storage_path('logs/excel_render.log'), "Mono Sheet: " . $this->view . " (Length: " . strlen($safe) . ")\n", FILE_APPEND);
                        return view('reporting.liasse.utils.raw_html', ['html' => $safe]);
                    } catch (\Throwable $e) {
                        file_put_contents(storage_path('logs/excel_render.log'), "ERROR Mono " . $this->view . ": " . $e->getMessage() . "\n", FILE_APPEND);
                        throw $e;
                    }
                }
            }, $filename);
        }

        // Export complet multi-feuilles
        return Excel::download(new class($allPages, $exercice, $company, $this) implements \Maatwebsite\Excel\Concerns\WithMultipleSheets {
            private $pages; private $exercice; private $company; private $service;
            public function __construct($pages, $exercice, $company, $service) { 
                $this->pages = $pages; $this->exercice = $exercice; 
                $this->company = $company; $this->service = $service;
            }
            public function sheets(): array {
                $sheets = [];
                foreach ($this->pages as $p) {
                    if (in_array($p['code'], ['BALANCE', 'GRAND_LIVRE'])) continue;
                    $sheets[] = new class($this->service->getPageData($this->exercice->id, $p['code']), 'reporting.liasse.pages.' . strtolower($p['code']), $p['title'], $this->company, $this->exercice) implements \Maatwebsite\Excel\Concerns\FromView, \Maatwebsite\Excel\Concerns\WithTitle {
                        private $data; private $view; private $title; private $company; private $exercice;
                        public function __construct($data, $view, $title, $company, $exercice) { 
                            $this->data = $data; $this->view = $view; $this->title = $title;
                            $this->company = $company; $this->exercice = $exercice;
                        }
                        public function view(): \Illuminate\Contracts\View\View { 
                            try {
                                $html = view($this->view, ['data' => $this->data, 'company' => $this->company, 'exercice' => $this->exercice, 'isExcel' => true])->render();
                                $clean = preg_replace('/>\s+</', '><', str_replace(["\r", "\n"], '', trim($html)));
                                $safe = mb_convert_encoding($clean, 'HTML-ENTITIES', 'UTF-8');
                                file_put_contents(storage_path('logs/excel_render.log'), "Multi Sheet: " . $this->view . " (Length: " . strlen($safe) . ")\n", FILE_APPEND);
                                return view('reporting.liasse.utils.raw_html', ['html' => $safe]);
                            } catch (\Throwable $e) {
                                file_put_contents(storage_path('logs/excel_render.log'), "ERROR Multi " . $this->view . ": " . $e->getMessage() . "\n", FILE_APPEND);
                                throw $e;
                            }
                        }
                        public function title(): string { return substr($this->title, 0, 31); }
                    };
                }
                return $sheets;
            }
        }, $filename);
    }
    public function generateXml($exerciceId, $companyId, string $regime = 'sn')
    {
        $exercice = ExerciceComptable::find($exerciceId);
        $company  = \App\Models\Company::find($companyId);
        if (!$exercice || !$company) throw new \Exception("Données manquantes.");

        $xmlType = $regime === 'smt' ? 'MT' : 'NO';

        $xml  = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><EDI/>');
        $info = $xml->addChild('informations');
        $info->addChild('type', $xmlType);
        $info->addChild('ncc', $company->ncc ?? '0000000X');
        $info->addChild('exercice', $exercice->date_debut->format('Y'));

        $fixesNode = $xml->addChild('champsTableauxFixes');
        $varsNode  = $xml->addChild('champsTableauxVariables');

        $mappings      = LiasseMapping::all();
        $pageDataCache = [];

        foreach ($mappings as $mapping) {
            $pageCode = $mapping->code_tableau;
            if (!isset($pageDataCache[$pageCode])) {
                $pageDataCache[$pageCode] = $regime === 'smt' 
                    ? $this->getSmtPageData($exerciceId, $pageCode)
                    : $this->getPageData($exerciceId, $pageCode);
            }

            $data      = $pageDataCache[$pageCode];
            $fieldCode = $mapping->code_champ_dgi;
            $parts     = explode('_', $fieldCode);
            $shortCode = $parts[count($parts)-2] ?? null;
            $colNum    = $parts[count($parts)-1] ?? '1';

            $value = '';
            if ($shortCode) {
                $suffix = "";
                if (in_array($mapping->code_tableau, ['ACTIF', 'PASSIF', 'RESULTAT', 'TFT', 'BILAN_ACTIF', 'BILAN_PASSIF'])) {
                    if ($colNum == '1') $suffix = ($mapping->code_tableau === 'BILAN_ACTIF') ? '_brut' : '';
                    if ($colNum == '2') $suffix = ($mapping->code_tableau === 'BILAN_ACTIF') ? '_amort' : '_N1';
                    if ($colNum == '3') $suffix = ($mapping->code_tableau === 'BILAN_ACTIF') ? '_net' : '';
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
        $dom->formatOutput       = true;
        $dom->loadXML($xml->asXML());
        return $dom->saveXML();
    }
}
