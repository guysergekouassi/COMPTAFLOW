<?php

namespace App\Services;

use App\Models\EcritureComptable;
use Illuminate\Support\Facades\DB;

class AccountingReportingService
{
    /**
     * Récupère les écritures filtrées par exercice et mois optionnel.
     */
    private function getFilteredEcritures($exerciceId, $companyId, $month = null)
    {
        $query = EcritureComptable::where('exercices_comptables_id', $exerciceId)
            ->where('company_id', $companyId)
            ->with(['planComptable' => function($q) {
                // Optimisation: ne charger que le numéro et l'intitulé
                $q->select('id', 'numero_de_compte', 'intitule');
            }]);

        if ($month && $month != 'all') {
            $query->whereMonth('date', $month);
        }

        return $query->get();
    }

    /**
     * Helper pour accumuler les détails des comptes.
     */
    private function addDetail(&$detailsArray, $compte, $montant)
    {
        if (abs($montant) < 0.01) return; // Ignorer les montants nuls
        
        $num = $compte->numero_de_compte;
        if (!isset($detailsArray[$num])) {
            $detailsArray[$num] = [
                'numero' => $num,
                'intitule' => $compte->intitule,
                'solde' => 0
            ];
        }
        $detailsArray[$num]['solde'] += $montant;
    }

    /**
     * Calcule les données pour le Bilan (Classes 1 à 5).
     */
    public function getBilanData($exerciceId, $companyId, $month = null, $detailed = false)
    {
        $ecritures = $this->getFilteredEcritures($exerciceId, $companyId, $month);

        $data = [
            'actif' => [
                'immobilise' => ['total' => 0, 'details' => []],
                'circulant' => ['total' => 0, 'details' => []],
                'tresorerie' => ['total' => 0, 'details' => []],
                'total' => 0,
            ],
            'passif' => [
                'capitaux' => ['total' => 0, 'details' => []],
                'dettes' => ['total' => 0, 'details' => []],
                'tresorerie' => ['total' => 0, 'details' => []],
                'total' => 0,
            ],
            'equilibre' => true,
            'difference' => 0,
        ];

        foreach ($ecritures as $ecriture) {
            $compte = $ecriture->planComptable;
            if (!$compte) continue;

            $numero = $compte->numero_de_compte;
            $solde = $ecriture->debit - $ecriture->credit;

            // Logique Bilan
            if (str_starts_with($numero, '2')) {
                // Actif Immobilisé
                $data['actif']['immobilise']['total'] += $solde;
                if ($detailed) $this->addDetail($data['actif']['immobilise']['details'], $compte, $solde);
            } elseif (str_starts_with($numero, '3')) {
                // Actif Circulant (Stocks)
                $data['actif']['circulant']['total'] += $solde;
                if ($detailed) $this->addDetail($data['actif']['circulant']['details'], $compte, $solde);
            } elseif (str_starts_with($numero, '4')) {
                // Tiers (Clients/Actif ou Fournisseurs/Passif)
                // Simplification: Solde débiteur = Actif, Créditeur = Passif
                if ($solde > 0) {
                    $data['actif']['circulant']['total'] += $solde;
                    if ($detailed) $this->addDetail($data['actif']['circulant']['details'], $compte, $solde);
                } else {
                    $data['passif']['dettes']['total'] += abs($solde);
                    if ($detailed) $this->addDetail($data['passif']['dettes']['details'], $compte, abs($solde));
                }
            } elseif (str_starts_with($numero, '5')) {
                // Trésorerie
                if ($solde >= 0) {
                    $data['actif']['tresorerie']['total'] += $solde;
                    if ($detailed) $this->addDetail($data['actif']['tresorerie']['details'], $compte, $solde);
                } else {
                    $data['passif']['tresorerie']['total'] += abs($solde);
                    if ($detailed) $this->addDetail($data['passif']['tresorerie']['details'], $compte, abs($solde));
                }
            } elseif (str_starts_with($numero, '1')) {
                // Capitaux (Passif)
                if(str_starts_with($numero, '16')) { // Emprunts = Dettes financières
                     $data['passif']['dettes']['total'] += abs($solde);
                     if ($detailed) $this->addDetail($data['passif']['dettes']['details'], $compte, abs($solde));
                } else {
                     $data['passif']['capitaux']['total'] += abs($solde);
                     if ($detailed) $this->addDetail($data['passif']['capitaux']['details'], $compte, abs($solde));
                }
            }
        }

        $data['actif']['total'] = $data['actif']['immobilise']['total'] + $data['actif']['circulant']['total'] + $data['actif']['tresorerie']['total'];
        $data['passif']['total'] = $data['passif']['capitaux']['total'] + $data['passif']['dettes']['total'] + $data['passif']['tresorerie']['total'];
        
        $data['difference'] = $data['actif']['total'] - $data['passif']['total'];
        $data['equilibre'] = abs($data['difference']) < 1.0; // Tolérance 1 FCFA

        return $data;
    }

    /**
     * Calcule les SIG (Soldes Intermédiaires de Gestion) selon SYSCOHADA.
     * Remplace getResultatData pour plus de précision.
     */
    public function getSIGData($exerciceId, $companyId, $month = null, $detailed = false)
    {
        $ecritures = $this->getFilteredEcritures($exerciceId, $companyId, $month);

        // Initialisation de la structure SIG
        $sig = [
            'ventes_marchandises' => 0,     // 701
            'achats_marchandises' => 0,     // 601
            'var_stock_march' => 0,         // 6031
            'marge_commerciale' => 0,       // SOLDE 1

            'prod_vendue' => 0,             // 70 (sauf 701)
            'prod_stockee' => 0,            // 73
            'prod_immobilisee' => 0,        // 72
            'production_exercice' => 0,     // Somme PROD

            'achats_matieres' => 0,         // 602
            'var_stock_mat' => 0,           // 6032
            'autres_achats' => 0,           // 604, 605, 608
            'transports' => 0,              // 61
            'services_ext' => 0,            // 62, 63
            'consommation_exercice' => 0,   // Somme CONSOS
            
            'valeur_ajoutee' => 0,          // SOLDE 2 (MC + PROD - CONSO)

            'subventions_expl' => 0,        // 71
            'impots_taxes' => 0,            // 64
            'charges_personnel' => 0,       // 66
            'ebe' => 0,                     // SOLDE 3 (VA + SUBV - IMPOTS - PERSO)

            'reprises_amort_prov' => 0,     // 791, 798, 75
            'transfert_charges' => 0,       // 781
            'dotations_amort_prov' => 0,    // 681, 691, 65
            'resultat_exploitation' => 0,   // SOLDE 4 (EBE + REP + TRANS - DOT)

            'revenus_financiers' => 0,      // 77
            'reprises_fin' => 0,            // 797
            'transfert_fin' => 0,           // 787
            'frais_financiers' => 0,        // 67
            'dotations_fin' => 0,           // 687, 697
            'resultat_financier' => 0,      // SOLDE 5 (PROD FIN - CHARGES FIN)

            'resultat_activites_ordinaires' => 0, // SOLDE 6 (REX + RFIN)

            'produits_hao' => 0,            // 82, 84, 86, 88
            'charges_hao' => 0,             // 81, 83, 85
            'resultat_hao' => 0,            // SOLDE 7

            'impots_resultat' => 0,         // 89
            'resultat_net' => 0,            // SOLDE 8 (RAO + RHAO - IMPOTS)

            'details' => []                 // Pour stocker les comptes individuels si $detailed = true
        ];

        foreach ($ecritures as $ecriture) {
            $compte = $ecriture->planComptable;
            if (!$compte) continue;

            $num = $compte->numero_de_compte;
            $solde = $ecriture->credit - $ecriture->debit; // Pour le résultat, Crédit = +, Débit = - en général (Produits - Charges)
            
            // Inversion pour les charges (car solde débiteur est négatif dans la formule Prod - Charges, mais ici on veut sommer les valeurs absolues parfois)
            // On va travailler avec le solde algébrique (Crédit - Débit). 
            // Charges = Solde Négatif. Produits = Solde Positif.
            
            // --- MARGE COMMERCIALE ---
            if (str_starts_with($num, '701')) { $sig['ventes_marchandises'] += $solde; }
            elseif (str_starts_with($num, '601')) { $sig['achats_marchandises'] += -$solde; } // On veut la valeur positive de la charge
            elseif (str_starts_with($num, '6031')) { $sig['var_stock_march'] += -$solde; }

            // --- PRODUCTION ---
            elseif (str_starts_with($num, '70') && !str_starts_with($num, '701')) { $sig['prod_vendue'] += $solde; }
            elseif (str_starts_with($num, '72')) { $sig['prod_immobilisee'] += $solde; }
            elseif (str_starts_with($num, '73')) { $sig['prod_stockee'] += $solde; }

            // --- CONSOMMATION ---
            elseif (str_starts_with($num, '602')) { $sig['achats_matieres'] += -$solde; }
            elseif (str_starts_with($num, '6032')) { $sig['var_stock_mat'] += -$solde; }
            elseif (str_starts_with($num, '604') || str_starts_with($num, '605') || str_starts_with($num, '608')) { 
                $sig['autres_achats'] += -$solde; 
            }
            elseif (str_starts_with($num, '61')) { $sig['transports'] += -$solde; }
            elseif (str_starts_with($num, '62') || str_starts_with($num, '63')) { $sig['services_ext'] += -$solde; }

            // --- VALEUR AJOUTEE ---
            // (Calculé à la fin)

            // --- EBE ---
            elseif (str_starts_with($num, '71')) { $sig['subventions_expl'] += $solde; }
            elseif (str_starts_with($num, '64')) { $sig['impots_taxes'] += -$solde; }
            elseif (str_starts_with($num, '66')) { $sig['charges_personnel'] += -$solde; }

            // --- REX ---
            elseif (str_starts_with($num, '791') || str_starts_with($num, '798') || str_starts_with($num, '75')) { 
                $sig['reprises_amort_prov'] += $solde; 
            }
            elseif (str_starts_with($num, '781')) { $sig['transfert_charges'] += $solde; }
            elseif (str_starts_with($num, '681') || str_starts_with($num, '691') || str_starts_with($num, '65')) { 
                $sig['dotations_amort_prov'] += -$solde; 
            }

            // --- RESULTAT FINANCIER ---
            elseif (str_starts_with($num, '77')) { $sig['revenus_financiers'] += $solde; }
            elseif (str_starts_with($num, '797')) { $sig['reprises_fin'] += $solde; }
            elseif (str_starts_with($num, '787')) { $sig['transfert_fin'] += $solde; }
            elseif (str_starts_with($num, '67')) { $sig['frais_financiers'] += -$solde; }
            elseif (str_starts_with($num, '687') || str_starts_with($num, '697')) { 
                $sig['dotations_fin'] += -$solde; 
            }

            // --- RESULTAT HAO ---
            elseif (str_starts_with($num, '82') || str_starts_with($num, '84') || str_starts_with($num, '86') || str_starts_with($num, '88')) {
                $sig['produits_hao'] += $solde;
            }
            elseif (str_starts_with($num, '81') || str_starts_with($num, '83') || str_starts_with($num, '85')) {
                $sig['charges_hao'] += -$solde;
            }

            // --- IMPÔTS ---
            elseif (str_starts_with($num, '89')) { $sig['impots_resultat'] += -$solde; }

            // --- COLLECTION DES DÉTAILS ---
            if ($detailed) {
                // Catégorisation pour l'affichage détail
                $category = 'Autres';
                if(str_starts_with($num, '6')) $category = 'Charges';
                if(str_starts_with($num, '7')) $category = 'Produits';
                if(str_starts_with($num, '8')) $category = 'HAO';
                
                if (!isset($sig['details'][$category])) $sig['details'][$category] = [];
                $this->addDetail($sig['details'][$category], $compte, $solde); // Attention ici solde est algébrique (Credits - Debits)
            }
        }

        // --- CALCULS DES SOLDES ---
        $sig['marge_commerciale'] = $sig['ventes_marchandises'] - $sig['achats_marchandises'] - $sig['var_stock_march'];
        
        $sig['production_exercice'] = $sig['prod_vendue'] + $sig['prod_stockee'] + $sig['prod_immobilisee'];
        
        $sig['consommation_exercice'] = $sig['achats_matieres'] + $sig['var_stock_mat'] + $sig['autres_achats'] + $sig['transports'] + $sig['services_ext'];
        
        $sig['valeur_ajoutee'] = $sig['marge_commerciale'] + $sig['production_exercice'] - $sig['consommation_exercice'];
        
        $sig['ebe'] = $sig['valeur_ajoutee'] + $sig['subventions_expl'] - $sig['impots_taxes'] - $sig['charges_personnel'];
        
        $sig['resultat_exploitation'] = $sig['ebe'] + $sig['reprises_amort_prov'] + $sig['transfert_charges'] - $sig['dotations_amort_prov'];
        
        $sig['resultat_financier'] = ($sig['revenus_financiers'] + $sig['reprises_fin'] + $sig['transfert_fin']) - ($sig['frais_financiers'] + $sig['dotations_fin']);
        
        $sig['resultat_activites_ordinaires'] = $sig['resultat_exploitation'] + $sig['resultat_financier'];
        
        $sig['resultat_hao'] = $sig['produits_hao'] - $sig['charges_hao'];
        
        $sig['resultat_net'] = $sig['resultat_activites_ordinaires'] + $sig['resultat_hao'] - $sig['impots_resultat'];

        return $sig;
    }

    /**
     * Calcule les données pour le Tableau des Flux de Trésorerie (TFT).
     */
    public function getTFTData($exerciceId, $companyId, $month = null, $detailed = false)
    {
        $ecritures = $this->getFilteredEcritures($exerciceId, $companyId, $month);

        $data = [
            'operationnel' => [
                'caf' => 0,
                'variation_bfr' => 0,
                'total' => 0,
                'details' => []
            ],
            'investissement' => [
                'acquisitions' => 0,
                'cessions' => 0,
                'total' => 0,
                'details' => []
            ],
            'financement' => [
                'capital' => 0,
                'emprunts' => 0,
                'dividendes' => 0,
                'total' => 0,
                'details' => []
            ],
            'tresorerie' => [
                'initiale' => 0,
                'finale' => 0,
                'variation_nette' => 0
            ]
        ];

        // Pour la CAF, on part du Résultat Net et on retire les éléments non encaissables/décaissables
        // Mais ici, on va utiliser la méthode directe approximative basée sur les flux
        
        // 1. Récupérer le résultat (avec les mêmes filtres)
        $sigData = $this->getSIGData($exerciceId, $companyId, $month);
        $data['operationnel']['caf'] = $sigData['resultat_net'];

        foreach ($ecritures as $ec) {
            $compte = $ec->planComptable;
            if (!$compte) continue;

            $num = $compte->numero_de_compte;
            $flux = $ec->debit - $ec->credit; // Flux de trésorerie conventionnel : Débit = Emploi, Crédit = Ressource ?
            // Simplification : On regarde l'impact sur la trésorerie.
            
            // Note: Pour CAF méthode additive : Résultat + Dotations - Reprises
            if (str_starts_with($num, '68') || str_starts_with($num, '69')) {
                $data['operationnel']['caf'] += $flux; // Charges calculées (Débit > 0), on les rajoute car non décaissées
                if($detailed) $this->addDetail($data['operationnel']['details'], $compte, $flux);
            }
            if (str_starts_with($num, '78') || str_starts_with($num, '79')) {
               $data['operationnel']['caf'] += $flux; // Produits calculés (Crédit > 0 -> Flux Négatif), on les retire (donc on ajoute le flux négatif)
               if($detailed) $this->addDetail($data['operationnel']['details'], $compte, $flux);
            }

            // BFR
            if (str_starts_with($num, '3') || str_starts_with($num, '4')) {
                // Pour actifs (3, 41): Augmentation (Debit) = Besoin (-)
                // Pour passifs (40, 42): Augmentation (Credit) = Ressource (+)
                if(str_starts_with($num, '40') || str_starts_with($num, '42') || str_starts_with($num, '43') || str_starts_with($num, '44')) {
                    $data['operationnel']['variation_bfr'] -= $flux; // Flux négatif (Credit) = Augmentation Dette = + Tréso. Donc -(-x) = +x
                } else {
                    $data['operationnel']['variation_bfr'] -= $flux; // Flux positif (Debit) = Augmentation Créance = - Tréso. Donc -(x) = -x
                }
                if($detailed) $this->addDetail($data['operationnel']['details'], $compte, -$flux);
            }

            // INVESTISSEMENT
            if (str_starts_with($num, '2') && !str_starts_with($num, '28') && !str_starts_with($num, '29')) {
                if ($flux > 0) { // Acquisition
                    $data['investissement']['acquisitions'] += $flux;
                } else { // Cession
                    $data['investissement']['cessions'] += abs($flux);
                }
                if($detailed) $this->addDetail($data['investissement']['details'], $compte, $flux);
            }

            // FINANCEMENT
            if (str_starts_with($num, '16') || str_starts_with($num, '10')) {
                 $data['financement']['total'] -= $flux; // Crédit = Ressource (+)
                 if($detailed) $this->addDetail($data['financement']['details'], $compte, -$flux);
            }

            // TRESORERIE
            if (str_starts_with($num, '5')) {
                 if ($ec->is_ran) { // Report à nouveau
                     $data['tresorerie']['initiale'] += $flux;
                 } else {
                     $data['tresorerie']['variation_nette'] += $flux;
                 }
            }
        }

        $data['operationnel']['total'] = $data['operationnel']['caf'] + $data['operationnel']['variation_bfr'];
        $data['investissement']['total'] = $data['investissement']['cessions'] - $data['investissement']['acquisitions'];
        
        $data['tresorerie']['finale'] = $data['tresorerie']['initiale'] + $data['tresorerie']['variation_nette'];

        return $data;
    }
}
