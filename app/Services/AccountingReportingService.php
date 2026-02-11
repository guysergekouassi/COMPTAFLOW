<?php

namespace App\Services;

use App\Models\EcritureComptable;
use Illuminate\Support\Facades\DB;

class AccountingReportingService
{
    /**
     * Génère la liste des mois pour un exercice donné.
     */
    private function getMonthsForExercise($exerciceId)
    {
        $exercice = \App\Models\ExerciceComptable::find($exerciceId);
        if (!$exercice) return [];

        $start = \Carbon\Carbon::parse($exercice->date_debut);
        $end = \Carbon\Carbon::parse($exercice->date_fin);
        
        $months = [];
        $current = $start->copy();
        while ($current <= $end) {
            $months[] = [
                'id' => $current->month,
                'name' => $current->locale('fr')->isoFormat('MMM-YY'),
                'year' => $current->year
            ];
            $current->addMonth();
        }
        return $months;
    }

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
    /**
     * Calcule les données pour le Bilan (Classes 1 à 5) avec structure OHADA détaillée.
     */
    public function getBilanData($exerciceId, $companyId, $month = null, $detailed = false)
    {
        $ecritures = $this->getFilteredEcritures($exerciceId, $companyId, $month);

        // Structure détaillée Actif
        $actif = [
            'immobilise' => [
                'total' => 0,
                'subcategories' => [
                    'charges_immo' => ['label' => 'Charges immobilisées', 'total' => 0, 'details' => []], // 20
                    'immo_incorp' => ['label' => 'Immobilisations incorporelles', 'total' => 0, 'details' => []], // 21
                    'immo_corp' => ['label' => 'Immobilisations corporelles', 'total' => 0, 'details' => []], // 22, 23, 24
                    'immo_fin' => ['label' => 'Immobilisations financières', 'total' => 0, 'details' => []], // 25, 26, 27
                ]
            ],
            'circulant' => [
                'total' => 0,
                'subcategories' => [
                    'stocks' => ['label' => 'Stocks', 'total' => 0, 'details' => []], // 3
                    'creances' => ['label' => 'Créances', 'total' => 0, 'details' => []], // 4 (Débiteur)
                ]
            ],
            'tresorerie' => [
                'total' => 0,
                'subcategories' => [
                    'titres' => ['label' => 'Titres de placement', 'total' => 0, 'details' => []], // 50
                    'banque' => ['label' => 'Banque', 'total' => 0, 'details' => []], // 52, 53... (Débiteur)
                    'caisse' => ['label' => 'Caisse', 'total' => 0, 'details' => []], // 57, 58 (Débiteur)
                ]
            ],
            'total' => 0
        ];

        // Structure détaillée Passif
        $passif = [
            'capitaux' => [
                'total' => 0,
                'subcategories' => [
                    'capital' => ['label' => 'Capital', 'total' => 0, 'details' => []], // 10
                    'reserves' => ['label' => 'Réserves', 'total' => 0, 'details' => []], // 11
                    'report' => ['label' => 'Report à nouveau', 'total' => 0, 'details' => []], // 12
                    'resultat' => ['label' => 'Résultat net (instance)', 'total' => 0, 'details' => []], // 13 (Calculé)
                    'subventions' => ['label' => 'Subventions', 'total' => 0, 'details' => []], // 14
                    'provisions' => ['label' => 'Provisions réglementées', 'total' => 0, 'details' => []], // 15
                ]
            ],
            'dettes_fin' => [
                'total' => 0,
                'subcategories' => [
                    'emprunts' => ['label' => 'Emprunts', 'total' => 0, 'details' => []], // 16
                ]
            ],
            'passif_circ' => [
                'total' => 0,
                'subcategories' => [
                    'fournisseurs' => ['label' => 'Dettes Fournisseurs', 'total' => 0, 'details' => []], // 40
                    'fiscales' => ['label' => 'Dettes Fiscales', 'total' => 0, 'details' => []], // 44
                    'sociales' => ['label' => 'Dettes Sociales', 'total' => 0, 'details' => []], // 42, 43
                    'autres_dettes' => ['label' => 'Autres dettes', 'total' => 0, 'details' => []], // Autres 4
                ]
            ],
            'tresorerie' => [
                'total' => 0,
                'subcategories' => [
                    'decouverts' => ['label' => 'Découverts bancaires', 'total' => 0, 'details' => []], // 5 (Créditeur)
                ]
            ],
            'total' => 0
        ];

        foreach ($ecritures as $ecriture) {
            $compte = $ecriture->planComptable;
            if (!$compte) continue;

            $n = $compte->numero_de_compte;
            $solde = $ecriture->debit - $ecriture->credit;
            if (abs($solde) < 0.01) continue;

            // --- ACTIF (Solde Débiteur > 0 en général, sauf 28, 29 Amort/Prov qui sont Créditeurs mais classés en Actif en moins) ---
            // Pour simplifier selon l'image : on classe selon la racine.
            
            // CLASSE 2 : IMMOBILISATIONS
            if (str_starts_with($n, '2')) {
                // Gestion des amortissements/provisions (28, 29) -> Vont en diminution de l'actif
                // Mais ici on fait un bilan NET ou BRUT ? Image montre "Actif Immobilisé". Souvent NET.
                // Si on fait NET, on ajoute le solde (qui peut être négatif si Amortissement).
                
                if (str_starts_with($n, '20')) $target = &$actif['immobilise']['subcategories']['charges_immo'];
                elseif (str_starts_with($n, '21')) $target = &$actif['immobilise']['subcategories']['immo_incorp'];
                elseif (str_starts_with($n, '22') || str_starts_with($n, '23') || str_starts_with($n, '24')) $target = &$actif['immobilise']['subcategories']['immo_corp'];
                elseif (str_starts_with($n, '28') || str_starts_with($n, '29')) {
                    // Amortissements : on les met dans la catégorie de l'immo correspondante ou globale ?
                    // Simplifions : 280/290->charges, 281/291->incorp, reste->corp
                    if(str_starts_with($n, '280') || str_starts_with($n, '290')) $target = &$actif['immobilise']['subcategories']['charges_immo'];
                    elseif(str_starts_with($n, '281') || str_starts_with($n, '291')) $target = &$actif['immobilise']['subcategories']['immo_incorp'];
                    else $target = &$actif['immobilise']['subcategories']['immo_corp'];
                }
                else $target = &$actif['immobilise']['subcategories']['immo_fin']; // 25, 26, 27

                if ($target) {
                    $target['total'] += $solde;
                    if ($detailed) $this->addDetail($target['details'], $compte, $solde);
                }
            }

            // CLASSE 3 : STOCKS
            elseif (str_starts_with($n, '3')) {
                $target = &$actif['circulant']['subcategories']['stocks'];
                $target['total'] += $solde;
                if ($detailed) $this->addDetail($target['details'], $compte, $solde);
            }

            // CLASSE 4 : TIERS (Active ou Passive selon solde)
            elseif (str_starts_with($n, '4')) {
                if ($solde > 0) {
                    // ACTIF : CRÉANCES
                    $target = &$actif['circulant']['subcategories']['creances'];
                    $target['total'] += $solde;
                    if ($detailed) $this->addDetail($target['details'], $compte, $solde);
                } else {
                    // PASSIF : DETTES (Solde Credit < 0, on prend valeur absolue)
                    if (str_starts_with($n, '40')) $target = &$passif['passif_circ']['subcategories']['fournisseurs'];
                    elseif (str_starts_with($n, '44')) $target = &$passif['passif_circ']['subcategories']['fiscales'];
                    elseif (str_starts_with($n, '42') || str_starts_with($n, '43')) $target = &$passif['passif_circ']['subcategories']['sociales'];
                    else $target = &$passif['passif_circ']['subcategories']['autres_dettes'];

                    $target['total'] += abs($solde);
                    if ($detailed) $this->addDetail($target['details'], $compte, abs($solde));
                }
            }

            // CLASSE 5 : TRÉSORERIE
            elseif (str_starts_with($n, '5')) {
                if ($solde >= 0) {
                    // ACTIF
                    if (str_starts_with($n, '50')) $target = &$actif['tresorerie']['subcategories']['titres'];
                    elseif (str_starts_with($n, '57') || str_starts_with($n, '58')) $target = &$actif['tresorerie']['subcategories']['caisse'];
                    else $target = &$actif['tresorerie']['subcategories']['banque'];

                    $target['total'] += $solde;
                    if ($detailed) $this->addDetail($target['details'], $compte, $solde);
                } else {
                    // PASSIF
                    $target = &$passif['tresorerie']['subcategories']['decouverts'];
                    $target['total'] += abs($solde);
                    if ($detailed) $this->addDetail($target['details'], $compte, abs($solde));
                }
            }

            // CLASSE 1 : CAPITAUX & DETTES FIN
            elseif (str_starts_with($n, '1')) {
                if (str_starts_with($n, '16')) {
                    // Dettes financières
                    $target = &$passif['dettes_fin']['subcategories']['emprunts'];
                    $target['total'] += abs($solde); // Créditeur
                    if ($detailed) $this->addDetail($target['details'], $compte, abs($solde));
                } elseif (str_starts_with($n, '13')) {
                    // Résultat Net (si clos)
                    $target = &$passif['capitaux']['subcategories']['resultat'];
                    $target['total'] += (-$solde); // Compte 13 : Crediteur = Benefice (Positif dans capitaux), Debiteur = Perte (Negatif)
                    if ($detailed) $this->addDetail($target['details'], $compte, -$solde);
                } else {
                    // Capitaux
                    if (str_starts_with($n, '10')) $target = &$passif['capitaux']['subcategories']['capital'];
                    elseif (str_starts_with($n, '11')) $target = &$passif['capitaux']['subcategories']['reserves'];
                    elseif (str_starts_with($n, '12')) $target = &$passif['capitaux']['subcategories']['report'];
                    elseif (str_starts_with($n, '14')) $target = &$passif['capitaux']['subcategories']['subventions'];
                    elseif (str_starts_with($n, '15')) $target = &$passif['capitaux']['subcategories']['provisions'];
                    else {
                        // Autres capitaux, mis dans Reserves par defaut
                        $target = &$passif['capitaux']['subcategories']['reserves'];
                    }

                    $target['total'] += abs($solde);
                    if ($detailed) $this->addDetail($target['details'], $compte, abs($solde));
                }
            }
        }

        // Calculs Totaux Sections Actif
        $actif['immobilise']['total'] = array_sum(array_column($actif['immobilise']['subcategories'], 'total'));
        $actif['circulant']['total'] = array_sum(array_column($actif['circulant']['subcategories'], 'total'));
        $actif['tresorerie']['total'] = array_sum(array_column($actif['tresorerie']['subcategories'], 'total'));
        $actif['total'] = $actif['immobilise']['total'] + $actif['circulant']['total'] + $actif['tresorerie']['total'];

        // Calculs Totaux Sections Passif (Hors Résultat pour l'instant si pas de 13)
        $passif['capitaux']['total'] = array_sum(array_column($passif['capitaux']['subcategories'], 'total'));
        $passif['dettes_fin']['total'] = array_sum(array_column($passif['dettes_fin']['subcategories'], 'total'));
        $passif['passif_circ']['total'] = array_sum(array_column($passif['passif_circ']['subcategories'], 'total'));
        $passif['tresorerie']['total'] = array_sum(array_column($passif['tresorerie']['subcategories'], 'total'));
        
        $totalPassifProvisoire = $passif['capitaux']['total'] + $passif['dettes_fin']['total'] + $passif['passif_circ']['total'] + $passif['tresorerie']['total'];

        // Calcul automatique du résultat si déséquilibre (Comptes 6 et 7 non soldés)
        $difference = $actif['total'] - $totalPassifProvisoire;
        
        // Si la différence n'est pas nulle, c'est le résultat de la période
        // (Actif = Passif + Résultat). Donc Résultat = Actif - Passif.
        // Si Actif > Passif => Bénéfice (Positif dans Capitaux)
        // Si Actif < Passif => Perte (Négatif dans Capitaux)
        if (abs($difference) > 0.01) {
            $passif['capitaux']['subcategories']['resultat']['total'] += $difference;
            $passif['capitaux']['total'] += $difference;
            $totalPassifProvisoire += $difference;
            
            // On ajoute une ligne "fictive" pour le détail si demandé
            if ($detailed) {
                 $passif['capitaux']['subcategories']['resultat']['details'][] = [
                     'numero' => 'RES',
                     'intitule' => 'Résultat de la période (calculé)',
                     'solde' => $difference
                 ];
            }
        }

        $passif['total'] = $totalPassifProvisoire;
        
        return [
            'actif' => $actif,
            'passif' => $passif,
            'equilibre' => abs($actif['total'] - $passif['total']) < 0.01,
            'difference' => $actif['total'] - $passif['total']
        ];
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

        // Tracker les Saisies traitées via Poste pour éviter le double comptage si fallback
        $handledSaisiesInv = [];
        $handledSaisiesFin = [];

        foreach ($ecritures as $ec) {
            $compte = $ec->planComptable;
            if (!$compte) continue;

            $num = $compte->numero_de_compte;
            $montant = $ec->debit - $ec->credit; // Solde Algébrique (Debit +, Credit -)
            $flux = $montant; // On garde flux pour la suite

            // --- A. INVESTISSEMENT & FINANCEMENT (Priorité Méthode Directe via Postes) ---
            if (str_starts_with($num, '5') && $ec->posteTresorerie) {
                $poste = $ec->posteTresorerie;
                $syscohadaLine = $poste->syscohada_line_id;
                $categoryName = $poste->category ? strtolower($poste->category->name) : '';
                
                // Flux de trésorerie réel : Debit = Entrée (+), Credit = Sortie (-)
                $fluxTresorerie = $ec->debit - $ec->credit;

                if ($syscohadaLine) {
                    if (str_starts_with($syscohadaLine, 'INV_')) {
                        $handledSaisiesInv[$ec->numero_saisie] = true;
                        if ($syscohadaLine === 'INV_CES') {
                            $data['investissement']['cessions'] += $fluxTresorerie;
                            if($detailed) $this->addDetail($data['investissement']['details'], $compte, $fluxTresorerie);
                        } elseif ($syscohadaLine === 'INV_ACQ') {
                            $data['investissement']['acquisitions'] += abs($fluxTresorerie);
                            if($detailed) $this->addDetail($data['investissement']['details'], $compte, abs($fluxTresorerie));
                        }
                    } elseif (str_starts_with($syscohadaLine, 'FIN_')) {
                        $handledSaisiesFin[$ec->numero_saisie] = true;
                        if ($syscohadaLine === 'FIN_CAP') $data['financement']['capital'] += $fluxTresorerie;
                        elseif ($syscohadaLine === 'FIN_EMP') $data['financement']['emprunts'] += $fluxTresorerie;
                        elseif ($syscohadaLine === 'FIN_DIV') $data['financement']['dividendes'] += abs($fluxTresorerie);
                        
                        $data['financement']['total'] += $fluxTresorerie;
                        if($detailed) $this->addDetail($data['financement']['details'], $compte, $fluxTresorerie);
                    }
                } 
                elseif (str_contains($categoryName, 'investissement')) {
                    $handledSaisiesInv[$ec->numero_saisie] = true;
                    if ($fluxTresorerie > 0) {
                        $data['investissement']['cessions'] += $fluxTresorerie;
                    } else {
                        $data['investissement']['acquisitions'] += abs($fluxTresorerie);
                    }
                    if($detailed) $this->addDetail($data['investissement']['details'], $compte, $fluxTresorerie);
                }
                elseif (str_contains($categoryName, 'financement')) {
                    $handledSaisiesFin[$ec->numero_saisie] = true;
                    $data['financement']['total'] += $fluxTresorerie;
                    if($detailed) $this->addDetail($data['financement']['details'], $compte, $fluxTresorerie);
                }
            }

            // --- B. MÉTHODE INDIRECTE (CAF & BFR) ---
            
            // CAF
            if (str_starts_with($num, '68') || str_starts_with($num, '69')) {
                $data['operationnel']['caf'] += $flux; 
                if($detailed) $this->addDetail($data['operationnel']['details'], $compte, $flux);
            }
            if (str_starts_with($num, '78') || str_starts_with($num, '79')) {
               $data['operationnel']['caf'] += $flux; 
               if($detailed) $this->addDetail($data['operationnel']['details'], $compte, $flux);
            }

            // BFR
            if (str_starts_with($num, '3') || (str_starts_with($num, '4') && !in_array($ec->numero_saisie, array_keys(array_merge($handledSaisiesInv, $handledSaisiesFin))))) {
                if(str_starts_with($num, '40') || str_starts_with($num, '42') || str_starts_with($num, '43') || str_starts_with($num, '44')) {
                    $data['operationnel']['variation_bfr'] -= $flux; 
                } else {
                    $data['operationnel']['variation_bfr'] -= $flux; 
                }
                if($detailed) $this->addDetail($data['operationnel']['details'], $compte, -$flux);
            }

            // --- C. FALLBACKS (Si non traité par poste) ---
            
            // INVESTISSEMENT
            if (str_starts_with($num, '2') && !str_starts_with($num, '28') && !str_starts_with($num, '29')) {
                if (!isset($handledSaisiesInv[$ec->numero_saisie])) {
                    if ($flux > 0) { // Acquisition
                        $data['investissement']['acquisitions'] += $flux;
                    } else { // Cession
                        $data['investissement']['cessions'] += abs($flux);
                    }
                    if($detailed) $this->addDetail($data['investissement']['details'], $compte, $flux);
                }
            }

            // FINANCEMENT
            if (str_starts_with($num, '16') || str_starts_with($num, '10')) {
                 if (!isset($handledSaisiesFin[$ec->numero_saisie])) {
                    $data['financement']['total'] -= $flux; // Crédit = Ressource (+)
                    if($detailed) $this->addDetail($data['financement']['details'], $compte, -$flux);
                 }
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
    /**
     * Calcule les données TFT au format matriciel (Mois par Mois).
     */
    public function getTFTMatrixData($exerciceId, $companyId, $detailed = false)
    {
        // 1. Déterminer les mois de l'exercice
        $exercice = \App\Models\ExerciceComptable::find($exerciceId);
        $start = \Carbon\Carbon::parse($exercice->date_debut);
        $end = \Carbon\Carbon::parse($exercice->date_fin);
        
        $months = [];
        $current = $start->copy();
        while ($current <= $end) {
            $months[] = [
                'id' => $current->month,
                'name' => $current->locale('fr')->isoFormat('MMM-YY'),
                'year' => $current->year
            ];
            $current->addMonth();
        }

        // 2. Initialiser la structure de la matrice (Format DIRECT pour Inv/Fin, INDIRECT pour Opérationnel)
        // Note: On change 'encaissements'/'decaissements' par 'caf' et 'bfr' pour l'opérationnel
        $matrix = [
            'months' => $months,
            'flux' => [
                'operationnel' => [
                    'caf' => [
                        'produits_encaissables' => array_fill(0, count($months), 0),
                        'charges_decaissables' => array_fill(0, count($months), 0),
                        'total' => array_fill(0, count($months), 0),
                        'details' => ['produits' => [], 'charges' => []]
                    ],
                    'bfr' => [
                        'variation_stocks' => array_fill(0, count($months), 0),
                        'variation_creances' => array_fill(0, count($months), 0),
                        'variation_dettes' => array_fill(0, count($months), 0),
                        'total' => array_fill(0, count($months), 0), // Variation Totale BFR
                        'details' => ['stocks' => [], 'creances' => [], 'dettes' => []]
                    ],
                    'net' => array_fill(0, count($months), 0)
                ],
                'investissement' => [
                    'acquisitions' => array_fill(0, count($months), 0),
                    'cessions' => array_fill(0, count($months), 0),
                    'net' => array_fill(0, count($months), 0),
                    'details' => ['acquisitions' => [], 'cessions' => []]
                ],
                'financement' => [
                    'net' => array_fill(0, count($months), 0),
                    'details' => ['net' => []]
                ],
                'tresorerie' => [
                    'net' => array_fill(0, count($months), 0),
                    'variation' => array_fill(0, count($months), 0),
                    'solde_fin' => array_fill(0, count($months), 0)
                ]
            ]
        ];

        // 3. Récupérer toutes les écritures
        $ecritures = EcritureComptable::where('exercices_comptables_id', $exerciceId)
            ->where('company_id', $companyId)
            ->with(['planComptable', 'posteTresorerie.category']) // Charger les postes et catégories
            ->get();

        // Tracker les Saisies (Transactions) traitées via Poste de Tréso pour éviter le double comptage si fallback
        $handledSaisiesInv = [];
        $handledSaisiesFin = [];

        // 4. PREMIÈRE PASSE : Postes de Trésorerie (Priorité) et Flux Opérationnels (Indirect)
        foreach ($ecritures as $ecriture) {
            $compte = $ecriture->planComptable;
            if (!$compte) continue;

            $num = $compte->numero_de_compte;
            $montant = $ecriture->debit - $ecriture->credit; // Solde Algébrique (Debit +, Credit -)
            
            // Index Mois
            $ecritureDate = \Carbon\Carbon::parse($ecriture->date);
            $monthIndex = -1;
            foreach ($months as $index => $m) {
                if ($m['id'] == $ecritureDate->month && $m['year'] == $ecritureDate->year) {
                    $monthIndex = $index;
                    break;
                }
            }
            if ($monthIndex === -1) continue;

            // --- A. ACTIVITÉS OPÉRATIONNELLES (Méthode Indirecte) ---
            
            // 1. CAF (Produits Encaissables - Charges Décaissables)
            // Charges (6) sauf dotations (68, 69)
            if (str_starts_with($num, '6') && !str_starts_with($num, '68') && !str_starts_with($num, '69')) {
                // Charge = Débit (+). Pour CAF c'est une sortie (-).
                // On met en positif dans 'charges_decaissables' pour l'affichage, on soustraira au total
                $matrix['flux']['operationnel']['caf']['charges_decaissables'][$monthIndex] += $ecriture->debit; // Prendre Debit brut (Charge)
                if($detailed) $this->addDetailMatrix($matrix['flux']['operationnel']['caf']['details']['charges'], $compte, $ecriture->debit, $monthIndex);
            }
            // Produits (7) sauf reprises (78, 79)
            elseif (str_starts_with($num, '7') && !str_starts_with($num, '78') && !str_starts_with($num, '79')) {
                // Produit = Crédit. Pour CAF c'est une entrée (+).
                $matrix['flux']['operationnel']['caf']['produits_encaissables'][$monthIndex] += $ecriture->credit; // Prendre Credit brut
                if($detailed) $this->addDetailMatrix($matrix['flux']['operationnel']['caf']['details']['produits'], $compte, $ecriture->credit, $monthIndex);
            }

            // 2. VARIATION BFR (Actif Circulant + Passif Circulant)
            // Stocks (3) et Tiers (4)
            if (str_starts_with($num, '3')) {
                // Actif : Variation = Solde Final - Solde Initial.
                // Au niveau flux : Debit = Augmentation Stock = Besoin en fonds de roulement (Cash -)
                // Credit = Diminution Stock = Ressource (Cash +)
                // Donc Flux BFR = -(Debit - Credit) = Credit - Debit
                $fluxBFR = $ecriture->credit - $ecriture->debit; 
                $matrix['flux']['operationnel']['bfr']['variation_stocks'][$monthIndex] += $fluxBFR;
                if($detailed) $this->addDetailMatrix($matrix['flux']['operationnel']['bfr']['details']['stocks'], $compte, $fluxBFR, $monthIndex);
            }
            elseif (str_starts_with($num, '4')) {
                // Tiers
                // Passif (40, 42, 43, 44) : Credit = Augmentation Dette = Ressource (+). Debit = -
                // Actif (41) : Debit = Augmentation Créance = Besoin (-). Credit = +
                
                $isPassif = str_starts_with($num, '40') || str_starts_with($num, '42') || str_starts_with($num, '43') || str_starts_with($num, '44');
                
                if ($isPassif) {
                     // Dette : BFR s'améliore si Dette augmente (Credit).
                     $fluxBFR = $ecriture->credit - $ecriture->debit;
                     $matrix['flux']['operationnel']['bfr']['variation_dettes'][$monthIndex] += $fluxBFR;
                     if($detailed) $this->addDetailMatrix($matrix['flux']['operationnel']['bfr']['details']['dettes'], $compte, $fluxBFR, $monthIndex);
                } else {
                     // Créance : BFR empire si Créance augmente (Debit).
                     // Flux Cash = -(Debit - Credit) = Credit - Debit
                     $fluxBFR = $ecriture->credit - $ecriture->debit;
                     $matrix['flux']['operationnel']['bfr']['variation_creances'][$monthIndex] += $fluxBFR;
                     if($detailed) $this->addDetailMatrix($matrix['flux']['operationnel']['bfr']['details']['creances'], $compte, $fluxBFR, $monthIndex);
                }
            }

            // --- B & C. INVESTISSEMENT & FINANCEMENT (Méthode Directe via Postes) ---
            // --- B & C. INVESTISSEMENT & FINANCEMENT (Méthode Directe via Postes) ---
            // On regarde UNIQUEMENT les comptes de classe 5 qui ont un poste défini
            if (str_starts_with($num, '5') && $ecriture->posteTresorerie) {
                $poste = $ecriture->posteTresorerie;
                $syscohadaLine = $poste->syscohada_line_id;
                $categoryName = $poste->category ? strtolower($poste->category->name) : '';
                
                // Flux de trésorerie réel : Debit = Entrée (+), Credit = Sortie (-)
                $fluxTresorerie = $ecriture->debit - $ecriture->credit;

                // Priorité au mapping explicite SYSCOHADA s'il existe
                if ($syscohadaLine) {
                    if (str_starts_with($syscohadaLine, 'INV_')) { // Investissement
                        $handledSaisiesInv[$ecriture->numero_saisie] = true;
                        if ($syscohadaLine === 'INV_CES') {
                            // Cessions (Flux Positif attendu)
                            $matrix['flux']['investissement']['cessions'][$monthIndex] += $fluxTresorerie; // Si positif = encaissement
                            if($detailed) $this->addDetailMatrix($matrix['flux']['investissement']['details']['cessions'], $compte, $fluxTresorerie, $monthIndex);
                        } elseif ($syscohadaLine === 'INV_ACQ') {
                            // Acquisitions (Flux Négatif attendu)
                            $matrix['flux']['investissement']['acquisitions'][$monthIndex] += abs($fluxTresorerie); // On stocke en positif pour l'affichage (Acq = Sortie)
                             if($detailed) $this->addDetailMatrix($matrix['flux']['investissement']['details']['acquisitions'], $compte, abs($fluxTresorerie), $monthIndex);
                        }
                    } elseif (str_starts_with($syscohadaLine, 'FIN_')) { // Financement
                        $handledSaisiesFin[$ecriture->numero_saisie] = true;
                        $matrix['flux']['financement']['net'][$monthIndex] += $fluxTresorerie;
                        if($detailed) $this->addDetailMatrix($matrix['flux']['financement']['details']['net'], $compte, $fluxTresorerie, $monthIndex);
                    }
                } 
                // Fallback : Mapping basé sur la catégorie (Heuristique)
                elseif (str_contains($categoryName, 'investissement')) {
                    $handledSaisiesInv[$ecriture->numero_saisie] = true;
                    // Classification simple : Positif = Cession, Négatif = Acquisition
                    if ($fluxTresorerie > 0) {
                        $matrix['flux']['investissement']['cessions'][$monthIndex] += $fluxTresorerie;
                        if($detailed) $this->addDetailMatrix($matrix['flux']['investissement']['details']['cessions'], $compte, $fluxTresorerie, $monthIndex);
                    } else {
                         $matrix['flux']['investissement']['acquisitions'][$monthIndex] += abs($fluxTresorerie);
                         if($detailed) $this->addDetailMatrix($matrix['flux']['investissement']['details']['acquisitions'], $compte, abs($fluxTresorerie), $monthIndex);
                    }
                }
                elseif (str_contains($categoryName, 'financement')) {
                    $handledSaisiesFin[$ecriture->numero_saisie] = true;
                    $matrix['flux']['financement']['net'][$monthIndex] += $fluxTresorerie;
                    if($detailed) $this->addDetailMatrix($matrix['flux']['financement']['details']['net'], $compte, $fluxTresorerie, $monthIndex);
                }
            }
        }

        // 5. DEUXIÈME PASSE : Fallback pour Investissement/Financement (Ancienne Méthode)
        // On ne traite que si la saisie n'a PAS été traitée par un poste de trésorerie
        foreach ($ecritures as $ecriture) {
            $compte = $ecriture->planComptable;
            if (!$compte) continue;
            
            // On ignore si déjà traité
            if (isset($handledSaisiesInv[$ecriture->numero_saisie]) || isset($handledSaisiesFin[$ecriture->numero_saisie])) {
                continue; 
            }

            $num = $compte->numero_de_compte;
            $monthIndex = -1; // Récupérer index (optimisation possible mais code plus simple ainsi)
            $ecritureDate = \Carbon\Carbon::parse($ecriture->date);
            foreach ($months as $index => $m) {
                if ($m['id'] == $ecritureDate->month && $m['year'] == $ecritureDate->year) {
                    $monthIndex = $index;
                    break;
                }
            }
            if ($monthIndex === -1) continue;

            // Fallback Investissement (Classe 2)
            if (str_starts_with($num, '2') && !str_starts_with($num, '28') && !str_starts_with($num, '29')) {
                // Acquisition (Debit 2) = Sortie Cash (-). Cession (Credit 2) = Entrée Cash (+)
                if ($ecriture->debit > 0) {
                    $matrix['flux']['investissement']['acquisitions'][$monthIndex] += $ecriture->debit;
                    if($detailed) $this->addDetailMatrix($matrix['flux']['investissement']['details']['acquisitions'], $compte, $ecriture->debit, $monthIndex);
                }
                if ($ecriture->credit > 0) {
                    $matrix['flux']['investissement']['cessions'][$monthIndex] += $ecriture->credit;
                    if($detailed) $this->addDetailMatrix($matrix['flux']['investissement']['details']['cessions'], $compte, $ecriture->credit, $monthIndex);
                }
            }

            // Fallback Financement (10, 16)
            if ((str_starts_with($num, '16') || str_starts_with($num, '10')) && !str_starts_with($num, '169')) {
                // Credit = Encaissement (+). Debit = Remboursement (-)
                $val = $ecriture->credit - $ecriture->debit;
                $matrix['flux']['financement']['net'][$monthIndex] += $val;
                if($detailed) $this->addDetailMatrix($matrix['flux']['financement']['details']['net'], $compte, $val, $monthIndex);
            }
        }

        // 6. Calculs Finaux
        $tresorerie_initiale = 0; 

        for ($i = 0; $i < count($months); $i++) {
            // Opérationnel
            $caf = $matrix['flux']['operationnel']['caf']['produits_encaissables'][$i] - $matrix['flux']['operationnel']['caf']['charges_decaissables'][$i];
            $matrix['flux']['operationnel']['caf']['total'][$i] = $caf;

            $bfr = $matrix['flux']['operationnel']['bfr']['variation_stocks'][$i]
                 + $matrix['flux']['operationnel']['bfr']['variation_creances'][$i]
                 + $matrix['flux']['operationnel']['bfr']['variation_dettes'][$i];
            $matrix['flux']['operationnel']['bfr']['total'][$i] = $bfr;

            // Net Opérationnel = CAF + Variation BFR (Attention aux signes, ici BFR est déjà un flux: + = Ressource, - = Emploi)
            $matrix['flux']['operationnel']['net'][$i] = $caf + $bfr;

            // Investissement
            $matrix['flux']['investissement']['net'][$i] = $matrix['flux']['investissement']['cessions'][$i] - $matrix['flux']['investissement']['acquisitions'][$i];

            // Variation Totale
            $variation = $matrix['flux']['operationnel']['net'][$i] 
                       + $matrix['flux']['investissement']['net'][$i] 
                       + $matrix['flux']['financement']['net'][$i];
            
            $matrix['flux']['tresorerie']['variation'][$i] = $variation;
            
            $tresorerie_initiale += $variation;
            $matrix['flux']['tresorerie']['solde_fin'][$i] = $tresorerie_initiale;
        }

        return $matrix;
    }

    public function getMonthlyResultatData($exerciceId, $companyId, $detailed = false)
    {
        $months = $this->getMonthsForExercise($exerciceId);
        $monthIds = array_column($months, 'id');
        
        // Initialize structure
        $data = [
            'produits' => [
                'vente_marchandises' => ['label' => 'Vente de marchandises (70)', 'data' => array_fill(0, count($months), 0), 'details' => []],
                'production_vendue' => ['label' => 'Production vendue (71)', 'data' => array_fill(0, count($months), 0), 'details' => []],
                'production_stockee' => ['label' => 'Production stockée (72-73)', 'data' => array_fill(0, count($months), 0), 'details' => []],
                'autres_produits' => ['label' => 'Autres produits (75-78)', 'data' => array_fill(0, count($months), 0), 'details' => []],
                'total' => array_fill(0, count($months), 0)
            ],
            'charges' => [
                'achats_marchandises' => ['label' => 'Achats de marchandises (60)', 'data' => array_fill(0, count($months), 0), 'details' => []],
                'transports' => ['label' => 'Transports (61)', 'data' => array_fill(0, count($months), 0), 'details' => []],
                'services_exterieurs' => ['label' => 'Services Extérieurs (62-63)', 'data' => array_fill(0, count($months), 0), 'details' => []],
                'impots_taxes' => ['label' => 'Impôts et Taxes (64)', 'data' => array_fill(0, count($months), 0), 'details' => []],
                'charges_personnel' => ['label' => 'Charges de personnel (66)', 'data' => array_fill(0, count($months), 0), 'details' => []],
                'autres_charges' => ['label' => 'Autres charges (65, 68)', 'data' => array_fill(0, count($months), 0), 'details' => []],
                'total' => array_fill(0, count($months), 0)
            ],
            'resultat' => array_fill(0, count($months), 0)
        ];

        // Fetch entries
        $ecritures = \App\Models\EcritureComptable::with('planComptable')
            ->where('company_id', $companyId)
            ->where('exercices_comptables_id', $exerciceId)
            ->whereHas('planComptable', function($q) {
                $q->where('numero_de_compte', 'LIKE', '6%')
                  ->orWhere('numero_de_compte', 'LIKE', '7%');
            })
            ->get();

        foreach ($ecritures as $ecriture) {
            $compte = $ecriture->planComptable;
            $num = $compte->numero_de_compte;
            
            // Find month index
            $ecritureDate = \Carbon\Carbon::parse($ecriture->date);
            $monthIndex = -1;
            foreach ($months as $index => $m) {
                if ($m['id'] == $ecritureDate->month && $m['year'] == $ecritureDate->year) {
                    $monthIndex = $index;
                    break;
                }
            }
            
            if ($monthIndex === -1) continue;

            $montant = 0;
            if (str_starts_with($num, '7')) {
                $montant = $ecriture->credit - $ecriture->debit;
                
                // Categorization
                $key = 'autres_produits';
                if (str_starts_with($num, '70')) $key = 'vente_marchandises';
                elseif (str_starts_with($num, '71')) $key = 'production_vendue';
                elseif (str_starts_with($num, '72') || str_starts_with($num, '73')) $key = 'production_stockee';
                
                $data['produits'][$key]['data'][$monthIndex] += $montant;
                $data['produits']['total'][$monthIndex] += $montant;
                
                if ($detailed) {
                     $this->addDetailMatrix($data['produits'][$key]['details'], $compte, $montant, $monthIndex);
                }

            } elseif (str_starts_with($num, '6')) {
                $montant = $ecriture->debit - $ecriture->credit;

                // Categorization
                $key = 'autres_charges';
                if (str_starts_with($num, '60')) $key = 'achats_marchandises';
                elseif (str_starts_with($num, '61')) $key = 'transports';
                elseif (str_starts_with($num, '62') || str_starts_with($num, '63')) $key = 'services_exterieurs';
                elseif (str_starts_with($num, '64')) $key = 'impots_taxes';
                elseif (str_starts_with($num, '66')) $key = 'charges_personnel';
                
                $data['charges'][$key]['data'][$monthIndex] += $montant;
                $data['charges']['total'][$monthIndex] += $montant;

                if ($detailed) {
                     $this->addDetailMatrix($data['charges'][$key]['details'], $compte, $montant, $monthIndex);
                }
            }
        }

        // Finalize Resultat Net
        for ($i = 0; $i < count($months); $i++) {
            $data['resultat'][$i] = $data['produits']['total'][$i] - $data['charges']['total'][$i];
        }

        return ['data' => $data, 'months' => $months];
    }

    private function addDetailMatrix(&$detailsArray, $compte, $montant, $monthIndex)
    {
        $num = $compte->numero_de_compte;
        if (!isset($detailsArray[$num])) {
            $detailsArray[$num] = [
                'numero' => $num,
                'intitule' => $compte->intitule,
                'data' => [] 
            ];
        }
        if (!isset($detailsArray[$num]['data'][$monthIndex])) {
             $detailsArray[$num]['data'][$monthIndex] = 0;
        }
        $detailsArray[$num]['data'][$monthIndex] += $montant;
    }

    /**
     * Calcule le TFT Personnalisé (Encaissements / Décaissements / Flux Net).
     */
        /**
     * Calcule le TFT Personnalisé (Encaissements / Décaissements / Flux Net par Activité).
     */
    public function getPersonalizedTFTData($exerciceId, $companyId, $detailed = false)
    {
        $months = $this->getMonthsForExercise($exerciceId);
        $monthCount = count($months);
        
        $data = [
            'months' => $months,
            'treso_initiale' => 0,
            'activities' => [
                'operationnelle' => [
                    'label' => 'ACTIVITÉS OPÉRATIONNELLES',
                    'encaissements' => ['total' => array_fill(0, $monthCount, 0), 'categories' => []],
                    'decaissements' => ['total' => array_fill(0, $monthCount, 0), 'categories' => []],
                    'net' => array_fill(0, $monthCount, 0)
                ],
                'investissement' => [
                    'label' => 'ACTIVITÉS D\'INVESTISSEMENT',
                    'encaissements' => ['total' => array_fill(0, $monthCount, 0), 'categories' => []],
                    'decaissements' => ['total' => array_fill(0, $monthCount, 0), 'categories' => []],
                    'net' => array_fill(0, $monthCount, 0)
                ],
                'financement' => [
                    'label' => 'ACTIVITÉS DE FINANCEMENT',
                    'encaissements' => ['total' => array_fill(0, $monthCount, 0), 'categories' => []],
                    'decaissements' => ['total' => array_fill(0, $monthCount, 0), 'categories' => []],
                    'net' => array_fill(0, $monthCount, 0)
                ]
            ],
            'global_net' => array_fill(0, $monthCount, 0),
            'cumule' => array_fill(0, $monthCount, 0)
        ];

        // Récupérer toutes les écritures pour le calcul
        $ecritures = EcritureComptable::where('exercices_comptables_id', $exerciceId)
            ->where('company_id', $companyId)
            ->with(['planComptable', 'posteTresorerie.category'])
            ->get();

        // 1. Calcul de la Trésorerie Initiale (RAN classe 5)
        $treso_initiale = 0;
        foreach ($ecritures as $ec) {
            if ($ec->is_ran && $ec->planComptable && str_starts_with($ec->planComptable->numero_de_compte, '5')) {
                $treso_initiale += ($ec->debit - $ec->credit);
            }
        }
        $data['treso_initiale'] = $treso_initiale;

        // Tracker pour investissement/financement par saisie (comme dans le standard)
        $handledSaisiesInv = [];
        $handledSaisiesFin = [];

        foreach ($ecritures as $ec) {
            $compte = $ec->planComptable;
            if (!$compte || $ec->is_ran) continue;

            $num = $compte->numero_de_compte;
            $ecDate = \Carbon\Carbon::parse($ec->date);
            $monthIndex = -1;
            foreach ($months as $idx => $m) {
                if ($m['id'] == $ecDate->month && $m['year'] == $ecDate->year) {
                    $monthIndex = $idx;
                    break;
                }
            }
            if ($monthIndex === -1) continue;

            // --- A. OPÉRATIONNEL (Méthode Indirecte adaptée) ---
            
            // CAF - Produits (7 sauf 78/79)
            if (str_starts_with($num, '7') && !str_starts_with($num, '78') && !str_starts_with($num, '79')) {
                $val = $ec->credit - $ec->debit;
                if ($val > 0) {
                    $data['activities']['operationnelle']['encaissements']['total'][$monthIndex] += $val;
                    $this->addManualCategoryDetail($data['activities']['operationnelle']['encaissements']['categories'], 'Produits encaissables', $val, $monthIndex, $monthCount);
                } else {
                    $data['activities']['operationnelle']['decaissements']['total'][$monthIndex] += abs($val);
                    $this->addManualCategoryDetail($data['activities']['operationnelle']['decaissements']['categories'], 'Annulations de produits', abs($val), $monthIndex, $monthCount);
                }
            }
            // CAF - Charges (6 sauf 68/69)
            elseif (str_starts_with($num, '6') && !str_starts_with($num, '68') && !str_starts_with($num, '69')) {
                $val = $ec->debit - $ec->credit;
                if ($val > 0) {
                    $data['activities']['operationnelle']['decaissements']['total'][$monthIndex] += $val;
                    $this->addManualCategoryDetail($data['activities']['operationnelle']['decaissements']['categories'], 'Charges décaissables', $val, $monthIndex, $monthCount);
                } else {
                    $data['activities']['operationnelle']['encaissements']['total'][$monthIndex] += abs($val);
                    $this->addManualCategoryDetail($data['activities']['operationnelle']['encaissements']['categories'], 'Annulations de charges', abs($val), $monthIndex, $monthCount);
                }
            }
            // BFR - Stocks (3) et Tiers (4)
            elseif (str_starts_with($num, '3') || str_starts_with($num, '4')) {
                $isPassif = str_starts_with($num, '40') || str_starts_with($num, '42') || str_starts_with($num, '43') || str_starts_with($num, '44');
                $fluxBFR = $ec->credit - $ec->debit; // Flux de cash : + si dette augmente ou actif baisse

                $label = 'Variation BFR';
                if (str_starts_with($num, '3')) $label = 'Variation des Stocks';
                elseif ($isPassif) $label = 'Variation des Dettes d\'exploitation';
                else $label = 'Variation des Créances d\'exploitation';

                if ($fluxBFR > 0) {
                    $data['activities']['operationnelle']['encaissements']['total'][$monthIndex] += $fluxBFR;
                    $this->addManualCategoryDetail($data['activities']['operationnelle']['encaissements']['categories'], $label, $fluxBFR, $monthIndex, $monthCount);
                } elseif ($fluxBFR < 0) {
                    $data['activities']['operationnelle']['decaissements']['total'][$monthIndex] += abs($fluxBFR);
                    $this->addManualCategoryDetail($data['activities']['operationnelle']['decaissements']['categories'], $label, abs($fluxBFR), $monthIndex, $monthCount);
                }
            }

            // --- B & C. INVESTISSEMENT & FINANCEMENT (Méthode Directe via Postes prioritaires) ---
            if (str_starts_with($num, '5') && $ec->posteTresorerie) {
                $poste = $ec->posteTresorerie;
                $syscohadaLine = $poste->syscohada_line_id;
                $fluxTreso = $ec->debit - $ec->credit;

                if ($syscohadaLine && (str_starts_with($syscohadaLine, 'INV_') || str_starts_with($syscohadaLine, 'FIN_'))) {
                    $actKey = str_starts_with($syscohadaLine, 'INV_') ? 'investissement' : 'financement';
                    if ($actKey == 'investissement') $handledSaisiesInv[$ec->numero_saisie] = true;
                    else $handledSaisiesFin[$ec->numero_saisie] = true;

                    if ($fluxTreso > 0) {
                        $data['activities'][$actKey]['encaissements']['total'][$monthIndex] += $fluxTreso;
                        $this->addManualCategoryDetail($data['activities'][$actKey]['encaissements']['categories'], $poste->name, $fluxTreso, $monthIndex, $monthCount);
                    } else {
                        $data['activities'][$actKey]['decaissements']['total'][$monthIndex] += abs($fluxTreso);
                        $this->addManualCategoryDetail($data['activities'][$actKey]['decaissements']['categories'], $poste->name, abs($fluxTreso), $monthIndex, $monthCount);
                    }
                }
            }
        }

        // --- D. DEUXIÈME PASSE : Fallback Inv/Fin (Méthode Indirecte pour alignement total) ---
        foreach ($ecritures as $ec) {
            $compte = $ec->planComptable;
            if (!$compte || $ec->is_ran || isset($handledSaisiesInv[$ec->numero_saisie]) || isset($handledSaisiesFin[$ec->numero_saisie])) continue;

            $num = $compte->numero_de_compte;
            $ecDate = \Carbon\Carbon::parse($ec->date);
            $monthIndex = -1;
            foreach ($months as $idx => $m) {
                if ($m['id'] == $ecDate->month && $m['year'] == $ecDate->year) {
                    $monthIndex = $idx;
                    break;
                }
            }
            if ($monthIndex === -1) continue;

            // Fallback Investissement (Classe 2)
            if (str_starts_with($num, '2') && !str_starts_with($num, '28') && !str_starts_with($num, '29')) {
                // Acquisition (Debit 2) = Sortie Cash (-). Cession (Credit 2) = Entrée Cash (+)
                if ($ec->credit > 0) {
                    $data['activities']['investissement']['encaissements']['total'][$monthIndex] += $ec->credit;
                    $this->addManualCategoryDetail($data['activities']['investissement']['encaissements']['categories'], 'Cessions d\'immobilisations', $ec->credit, $monthIndex, $monthCount);
                }
                if ($ec->debit > 0) {
                    $data['activities']['investissement']['decaissements']['total'][$monthIndex] += $ec->debit;
                    $this->addManualCategoryDetail($data['activities']['investissement']['decaissements']['categories'], 'Acquisitions d\'immobilisations', $ec->debit, $monthIndex, $monthCount);
                }
            }
            // Fallback Financement (10, 16)
            elseif ((str_starts_with($num, '16') || str_starts_with($num, '10')) && !str_starts_with($num, '169')) {
                $val = $ec->credit - $ec->debit; // + si cash rentre (emprunt/capital), - si rembourse
                if ($val > 0) {
                    $data['activities']['financement']['encaissements']['total'][$monthIndex] += $val;
                    $this->addManualCategoryDetail($data['activities']['financement']['encaissements']['categories'], 'Ressources de financement', $val, $monthIndex, $monthCount);
                } elseif ($val < 0) {
                    $data['activities']['financement']['decaissements']['total'][$monthIndex] += abs($val);
                    $this->addManualCategoryDetail($data['activities']['financement']['decaissements']['categories'], 'Remboursements / Dividendes', abs($val), $monthIndex, $monthCount);
                }
            }
        }

        // Calculs finaux cumulés
        $current_cumule = $treso_initiale;
        for ($i = 0; $i < $monthCount; $i++) {
            $total_net_month = 0;
            foreach (['operationnelle', 'investissement', 'financement'] as $key) {
                $net_act = $data['activities'][$key]['encaissements']['total'][$i] - $data['activities'][$key]['decaissements']['total'][$i];
                $data['activities'][$key]['net'][$i] = $net_act;
                $total_net_month += $net_act;
            }
            $data['global_net'][$i] = $total_net_month;
            $current_cumule += $total_net_month;
            $data['cumule'][$i] = $current_cumule;
        }

        return $data;
    }

    private function addManualCategoryDetail(&$categories, $label, $montant, $monthIndex, $monthCount)
    {
        if ($montant == 0) return;
        if (!isset($categories[$label])) {
            $categories[$label] = [
                'label' => $label,
                'data' => array_fill(0, $monthCount, 0),
            ];
        }
        $categories[$label]['data'][$monthIndex] += $montant;
    }
}
