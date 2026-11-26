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
        // Sans scopes globaux : un exercice N-1 clôturé reste introuvable via find()
        // (UserIsolationScope impose `cloturer = 0` aux utilisateurs non-admin).
        $exercice = \App\Models\ExerciceComptable::withoutGlobalScopes()->find($exerciceId);
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
     * Liste publique des mois d'un exercice (alimente les filtres de période).
     * Chaque entrée : ['id' => n° de mois, 'name' => 'janv.-25', 'year' => 2025]
     */
    public function getMonthsForExercice($exerciceId)
    {
        return $this->getMonthsForExercise($exerciceId);
    }

    /**
     * Retourne l'exercice précédent (N-1) d'un exercice donné, pour la même entreprise.
     *
     * On ignore volontairement les scopes globaux : l'exercice N-1 est presque
     * toujours clôturé, or UserIsolationScope filtre sur `cloturer = 0` pour les
     * utilisateurs non-admin — il resterait donc introuvable.
     *
     * @param  \App\Models\ExerciceComptable  $exercice
     * @return \App\Models\ExerciceComptable|null
     */
    public function getExercicePrecedent($exercice)
    {
        if (!$exercice) return null;

        return \App\Models\ExerciceComptable::withoutGlobalScopes()
            ->where('company_id', $exercice->company_id)
            ->where('id', '!=', $exercice->id)
            ->whereDate('date_fin', '<', $exercice->date_debut)
            ->orderByDesc('date_fin')
            ->first();
    }

    /**
     * Liste des exercices sélectionnables pour une entreprise (du plus récent au plus ancien).
     * Sert à alimenter le filtre « Exercice » des boîtes de téléchargement.
     */
    public function getExercicesSelectionnables($companyId)
    {
        return \App\Models\ExerciceComptable::withoutGlobalScopes()
            ->where('company_id', $companyId)
            ->orderByDesc('date_debut')
            ->get();
    }

    /**
     * Détermine la période réellement couverte par un rapport en fonction
     * du mois sélectionné et des bornes de l'exercice.
     *
     * Le filtre des écritures utilise whereMonth(), il faut donc retrouver
     * l'année du mois choisi à l'intérieur de l'exercice (un exercice peut
     * débuter en cours d'année, ex: 01/10/N au 30/09/N+1).
     *
     * @return array{debut: \Carbon\Carbon, fin: \Carbon\Carbon, label: string, is_month: bool}
     */
    public function resolvePeriode($exercice, $month = null)
    {
        $debutExercice = \Carbon\Carbon::parse($exercice->date_debut);
        $finExercice = \Carbon\Carbon::parse($exercice->date_fin);

        if ($month === null || $month === '' || $month === 'all' || !is_numeric($month)) {
            return [
                'debut' => $debutExercice,
                'fin' => $finExercice,
                'label' => 'Tout l\'exercice',
                'is_month' => false,
            ];
        }

        $month = (int) $month;
        if ($month < 1 || $month > 12) {
            return [
                'debut' => $debutExercice,
                'fin' => $finExercice,
                'label' => 'Tout l\'exercice',
                'is_month' => false,
            ];
        }

        // Recherche du mois demandé à l'intérieur des bornes de l'exercice.
        $annee = null;
        $curseur = $debutExercice->copy()->startOfMonth();
        $finMois = $finExercice->copy()->startOfMonth();
        while ($curseur->lte($finMois)) {
            if ($curseur->month === $month) {
                $annee = $curseur->year;
                break;
            }
            $curseur->addMonth();
        }

        // Mois hors exercice : on retombe sur l'année de début d'exercice.
        if ($annee === null) {
            $annee = $month >= $debutExercice->month ? $debutExercice->year : $finExercice->year;
        }

        $debut = \Carbon\Carbon::create($annee, $month, 1)->startOfDay();
        $fin = $debut->copy()->endOfMonth()->startOfDay();

        // On ne déborde jamais des bornes de l'exercice.
        if ($debut->lt($debutExercice)) {
            $debut = $debutExercice->copy();
        }
        if ($fin->gt($finExercice)) {
            $fin = $finExercice->copy();
        }

        return [
            'debut' => $debut,
            'fin' => $fin,
            'label' => ucfirst($debut->copy()->locale('fr')->isoFormat('MMMM YYYY')),
            'is_month' => true,
        ];
    }

    public function getBalanceData($exerciceId, $companyId, $month = null)
    {
        $ecritures = $this->getFilteredEcritures($exerciceId, $companyId, $month);
        
        $grouped = $ecritures->groupBy('plan_comptable_id');
        
        $balance = [];
        foreach ($grouped as $compteId => $operations) {
            $compte = $operations->first()->planComptable;
            if (!$compte) continue;
            
            $totalDebit = $operations->sum('debit');
            $totalCredit = $operations->sum('credit');
            
            $solde = $totalDebit - $totalCredit;
            
            $balance[] = [
                'compte' => $compte,
                'numero' => $compte->numero_de_compte,
                'intitule' => $compte->intitule,
                'debit' => $totalDebit,
                'credit' => $totalCredit,
                'solde_debiteur' => $solde > 0 ? $solde : 0,
                'solde_crediteur' => $solde < 0 ? abs($solde) : 0,
            ];
        }
        
        usort($balance, function($a, $b) {
            return strcmp($a['numero'], $b['numero']);
        });
        
        return $balance;
    }

    public function getLedgerData($exerciceId, $companyId, $month = null)
    {
        $ecritures = $this->getFilteredEcritures($exerciceId, $companyId, $month);
        
        $grouped = $ecritures->sortBy('date')->groupBy('plan_comptable_id');
        
        $ledger = [];
        foreach ($grouped as $compteId => $operations) {
            $compte = $operations->first()->planComptable;
            if (!$compte) continue;
            
            $ledger[] = [
                'compte' => $compte,
                'operations' => $operations
            ];
        }
        
        usort($ledger, function($a, $b) {
            return strcmp($a['compte']->numero_de_compte, $b['compte']->numero_de_compte);
        });
        
        return $ledger;
    }

    /**
     * Récupère les écritures filtrées par exercice et mois optionnel.
     */
    private function getFilteredEcritures($exerciceId, $companyId, $month = null)
    {
        $query = EcritureComptable::where('exercices_comptables_id', $exerciceId)
            ->where('company_id', $companyId)
            ->with(['codeJournal', 'planComptable' => function($q) {
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
                'total_brut' => 0,
                'total_amort' => 0,
                'total_net' => 0,
                'subcategories' => [
                    'charges_immo' => ['label' => 'Charges immobilisées', 'brut' => 0, 'amort' => 0, 'net' => 0, 'details' => []], // 20
                    'immo_incorp' => ['label' => 'Immobilisations incorporelles', 'brut' => 0, 'amort' => 0, 'net' => 0, 'details' => []], // 21
                    'immo_corp' => ['label' => 'Immobilisations corporelles', 'brut' => 0, 'amort' => 0, 'net' => 0, 'details' => []], // 22, 23, 24
                    'immo_fin' => ['label' => 'Immobilisations financières', 'brut' => 0, 'amort' => 0, 'net' => 0, 'details' => []], // 25, 26, 27
                ]
            ],
            'circulant' => [
                'total_brut' => 0,
                'total_amort' => 0,
                'total_net' => 0,
                'subcategories' => [
                    'stocks' => ['label' => 'Stocks', 'brut' => 0, 'amort' => 0, 'net' => 0, 'details' => []], // 3
                    'creances' => ['label' => 'Créances', 'brut' => 0, 'amort' => 0, 'net' => 0, 'details' => []], // 4 (Débiteur)
                ]
            ],
            'tresorerie' => [
                'total_brut' => 0,
                'total_amort' => 0,
                'total_net' => 0,
                'subcategories' => [
                    'titres' => ['label' => 'Titres de placement', 'brut' => 0, 'amort' => 0, 'net' => 0, 'details' => []], // 50
                    'banque' => ['label' => 'Banque', 'brut' => 0, 'amort' => 0, 'net' => 0, 'details' => []], // 52, 53... (Débiteur)
                    'caisse' => ['label' => 'Caisse', 'brut' => 0, 'amort' => 0, 'net' => 0, 'details' => []], // 57, 58 (Débiteur)
                ]
            ],
            'total_brut' => 0,
            'total_amort' => 0,
            'total_net' => 0
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

            // --- ACTIF ---
            // CLASSE 2 : IMMOBILISATIONS
            if (str_starts_with($n, '2')) {
                // Determine category
                if (str_starts_with($n, '20')) $cat = 'charges_immo';
                elseif (str_starts_with($n, '21')) $cat = 'immo_incorp';
                elseif (str_starts_with($n, '22') || str_starts_with($n, '23') || str_starts_with($n, '24')) $cat = 'immo_corp';
                elseif (str_starts_with($n, '28') || str_starts_with($n, '29')) {
                    if(str_starts_with($n, '280') || str_starts_with($n, '290')) $cat = 'charges_immo';
                    elseif(str_starts_with($n, '281') || str_starts_with($n, '291')) $cat = 'immo_incorp';
                    else $cat = 'immo_corp';
                }
                else $cat = 'immo_fin';

                $target = &$actif['immobilise']['subcategories'][$cat];
                
                if (str_starts_with($n, '28') || str_starts_with($n, '29')) {
                    $target['amort'] += abs($solde); // Amortissements/Depreciations are credits so solde is negative, we take positive value
                } else {
                    $target['brut'] += $solde;
                }
                
                $target['net'] = $target['brut'] - $target['amort'];
                if ($detailed) $this->addDetail($target['details'], $compte, $solde);
            }

            // CLASSE 3 : STOCKS
            elseif (str_starts_with($n, '3')) {
                $target = &$actif['circulant']['subcategories']['stocks'];
                if (str_starts_with($n, '39')) {
                    $target['amort'] += abs($solde);
                } else {
                    $target['brut'] += $solde;
                }
                $target['net'] = $target['brut'] - $target['amort'];
                if ($detailed) $this->addDetail($target['details'], $compte, $solde);
            }

            // CLASSE 4 : TIERS
            elseif (str_starts_with($n, '4')) {
                if ($solde > 0) {
                    $target = &$actif['circulant']['subcategories']['creances'];
                    if (str_starts_with($n, '49')) {
                        $target['amort'] += abs($solde);
                    } else {
                        $target['brut'] += $solde;
                    }
                    $target['net'] = $target['brut'] - $target['amort'];
                    if ($detailed) $this->addDetail($target['details'], $compte, $solde);
                } else {
                    // PASSIF : DETTES
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
                    if (str_starts_with($n, '50')) $cat = 'titres';
                    elseif (str_starts_with($n, '57') || str_starts_with($n, '58')) $cat = 'caisse';
                    else $cat = 'banque';
                    
                    $target = &$actif['tresorerie']['subcategories'][$cat];
                    if (str_starts_with($n, '59')) {
                        $target['amort'] += abs($solde);
                    } else {
                        $target['brut'] += $solde;
                    }
                    $target['net'] = $target['brut'] - $target['amort'];
                    if ($detailed) $this->addDetail($target['details'], $compte, $solde);
                } else {
                    $target = &$passif['tresorerie']['subcategories']['decouverts'];
                    $target['total'] += abs($solde);
                    if ($detailed) $this->addDetail($target['details'], $compte, abs($solde));
                }
            }

            // CLASSE 1 : CAPITAUX
            elseif (str_starts_with($n, '1')) {
                if (str_starts_with($n, '16')) {
                    $target = &$passif['dettes_fin']['subcategories']['emprunts'];
                    $target['total'] += abs($solde);
                } elseif (str_starts_with($n, '13')) {
                    $target = &$passif['capitaux']['subcategories']['resultat'];
                    $target['total'] += (-$solde);
                } else {
                    if (str_starts_with($n, '10')) $target = &$passif['capitaux']['subcategories']['capital'];
                    elseif (str_starts_with($n, '11')) $target = &$passif['capitaux']['subcategories']['reserves'];
                    elseif (str_starts_with($n, '12')) $target = &$passif['capitaux']['subcategories']['report'];
                    elseif (str_starts_with($n, '14')) $target = &$passif['capitaux']['subcategories']['subventions'];
                    elseif (str_starts_with($n, '15')) $target = &$passif['capitaux']['subcategories']['provisions'];
                    else $target = &$passif['capitaux']['subcategories']['reserves'];

                    $target['total'] += abs($solde);
                }
                if ($detailed) $this->addDetail($target['details'], $compte, -$solde);
            }
        }

        // Calculs Totaux Sections Actif
        foreach (['immobilise', 'circulant', 'tresorerie'] as $section) {
            $actif[$section]['total_brut'] = array_sum(array_column($actif[$section]['subcategories'], 'brut'));
            $actif[$section]['total_amort'] = array_sum(array_column($actif[$section]['subcategories'], 'amort'));
            $actif[$section]['total_net'] = array_sum(array_column($actif[$section]['subcategories'], 'net'));
        }
        $actif['total_brut'] = $actif['immobilise']['total_brut'] + $actif['circulant']['total_brut'] + $actif['tresorerie']['total_brut'];
        $actif['total_amort'] = $actif['immobilise']['total_amort'] + $actif['circulant']['total_amort'] + $actif['tresorerie']['total_amort'];
        $actif['total_net'] = $actif['immobilise']['total_net'] + $actif['circulant']['total_net'] + $actif['tresorerie']['total_net'];

        // Calculs Totaux Sections Passif
        $passif['capitaux']['total'] = array_sum(array_column($passif['capitaux']['subcategories'], 'total'));
        $passif['dettes_fin']['total'] = array_sum(array_column($passif['dettes_fin']['subcategories'], 'total'));
        $passif['passif_circ']['total'] = array_sum(array_column($passif['passif_circ']['subcategories'], 'total'));
        $passif['tresorerie']['total'] = array_sum(array_column($passif['tresorerie']['subcategories'], 'total'));
        
        $totalPassifProvisoire = $passif['capitaux']['total'] + $passif['dettes_fin']['total'] + $passif['passif_circ']['total'] + $passif['tresorerie']['total'];

        // Calcul automatique du résultat si déséquilibre
        $difference = $actif['total_net'] - $totalPassifProvisoire;
        
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
            'equilibre' => abs($actif['total_net'] - $passif['total']) < 0.01,
            'difference' => $actif['total_net'] - $passif['total']
        ];
    }

    /**
     * Calcule les SIG (Soldes Intermédiaires de Gestion) selon SYSCOHADA.
     * Remplace getResultatData pour plus de précision.
     */
    public function getSIGData($exerciceId, $companyId, $month = null, $detailed = false, $excludeSystemResult = false)
    {
        $ecritures = $this->getFilteredEcritures($exerciceId, $companyId, $month);
        if ($excludeSystemResult) {
            $ecritures = $ecritures->reject(fn ($ecriture) => (bool) $ecriture->is_resultat_systeme);
        }

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
            $montant = $ec->debit - $ec->credit; // Solde Algébrique (Debit +, Credit -)
            $flux = $montant; // On garde flux pour la suite

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
            // Les comptes qui portent un investissement ou un financement
            // (481/482/485 immobilisations, 461/465 associés) relèvent des
            // sections II et III : les compter ici les compterait deux fois.
            if ((str_starts_with($num, '3') || str_starts_with($num, '4'))
                && ClassificationFlux::section($num) === ClassificationFlux::OPERATIONNELLE
                && !ClassificationFlux::estNonMonetaire($num)) {
                if(str_starts_with($num, '40') || str_starts_with($num, '42') || str_starts_with($num, '43') || str_starts_with($num, '44')) {
                    $data['operationnel']['variation_bfr'] -= $flux; 
                } else {
                    $data['operationnel']['variation_bfr'] -= $flux; 
                }
                if($detailed) $this->addDetail($data['operationnel']['details'], $compte, -$flux);
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

        // Investissement et financement : uniquement ce qui a bougé en banque
        // ou en caisse.
        //
        // Le calcul précédent lisait les mouvements des comptes 2, 10 et 16
        // eux-mêmes : une immobilisation acquise à crédit y figurait en
        // décaissement alors qu'aucun franc n'était sorti. Il classait de plus
        // selon la catégorie du poste de trésorerie, si bien qu'une banque
        // rangée en « investissement » y envoyait toutes ses opérations.
        foreach (AnalyseFluxTresorerie::mouvements($ecritures->filter(fn($e) => !$e->is_ran)) as $mouvement) {
            $signe = $mouvement->sens === 'encaissements' ? 1 : -1;
            $compte = (object) [
                'numero_de_compte' => $mouvement->compte,
                'intitule' => trim(str_replace($mouvement->compte . ' -', '', $mouvement->libelle)),
            ];

            if ($mouvement->section === ClassificationFlux::INVESTISSEMENT) {
                $rubrique = $signe > 0 ? 'cessions' : 'acquisitions';
                $data['investissement'][$rubrique] += $mouvement->montant;
                if ($detailed) $this->addDetail($data['investissement']['details'], $compte, $signe * $mouvement->montant);
            } elseif ($mouvement->section === ClassificationFlux::FINANCEMENT) {
                // Les trois lignes SYSCOHADA se lisent sur la contrepartie.
                if (str_starts_with($mouvement->compte, '10')) {
                    $data['financement']['capital'] += $signe * $mouvement->montant;
                } elseif (str_starts_with($mouvement->compte, '465')) {
                    $data['financement']['dividendes'] += $mouvement->montant;
                } else {
                    $data['financement']['emprunts'] += $signe * $mouvement->montant;
                }

                $data['financement']['total'] += $signe * $mouvement->montant;
                if ($detailed) $this->addDetail($data['financement']['details'], $compte, $signe * $mouvement->montant);
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
            //
            // Seule l'exploitation courante entre ici. Les comptes qui portent
            // un investissement ou un financement (481/482/485 fournisseurs et
            // créances d'immobilisations, 461/465 associés) relèvent des
            // sections II et III : les compter deux fois ferait dire au
            // tableau l'inverse de ce qui s'est passé en banque.
            if (ClassificationFlux::section($num) !== ClassificationFlux::OPERATIONNELLE
                || ClassificationFlux::estNonMonetaire($num)) {
                continue;
            }

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

        }

        // 5. INVESTISSEMENT ET FINANCEMENT : de l'argent réellement encaissé
        //    ou décaissé, et rien d'autre.
        //
        // L'ancien calcul lisait les mouvements des comptes d'immobilisations
        // et d'emprunts eux-mêmes. Une machine achetée à crédit (241 au débit,
        // 481 au crédit) apparaissait donc en décaissement d'investissement
        // alors qu'aucun franc n'était sorti ; seul le besoin en fonds de
        // roulement compensait l'écart, et le tableau ne disait plus la
        // trésorerie. Il classait par ailleurs selon la catégorie du poste,
        // si bien qu'une banque rangée en « investissement » y envoyait ses
        // salaires.
        //
        // On part désormais des lignes de trésorerie, la contrepartie de la
        // pièce donnant la section. Voir AnalyseFluxTresorerie.
        foreach (AnalyseFluxTresorerie::mouvements($ecritures->filter(fn($e) => !$e->is_ran)) as $mouvement) {
            if ($mouvement->section === ClassificationFlux::OPERATIONNELLE) {
                continue;   // déjà porté par la CAF et la variation du BFR
            }

            $date = \Carbon\Carbon::parse($mouvement->date);
            $monthIndex = -1;
            foreach ($months as $index => $m) {
                if ($m['id'] == $date->month && $m['year'] == $date->year) {
                    $monthIndex = $index;
                    break;
                }
            }
            if ($monthIndex === -1) continue;

            $compte = (object) ['numero_de_compte' => $mouvement->compte, 'intitule' => trim(str_replace($mouvement->compte . ' -', '', $mouvement->libelle))];

            if ($mouvement->section === ClassificationFlux::INVESTISSEMENT) {
                $rubrique = $mouvement->sens === 'encaissements' ? 'cessions' : 'acquisitions';
                $matrix['flux']['investissement'][$rubrique][$monthIndex] += $mouvement->montant;
                if ($detailed) $this->addDetailMatrix($matrix['flux']['investissement']['details'][$rubrique], $compte, $mouvement->montant, $monthIndex);
            } else {
                $val = $mouvement->sens === 'encaissements' ? $mouvement->montant : -$mouvement->montant;
                $matrix['flux']['financement']['net'][$monthIndex] += $val;
                if ($detailed) $this->addDetailMatrix($matrix['flux']['financement']['details']['net'], $compte, $val, $monthIndex);
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
                'vente_marchandises' => ['label' => 'Ventes (70)', 'data' => array_fill(0, count($months), 0), 'details' => []],
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
                    'label' => 'ACTIVITÉS OPÉRATIONNELLES (I)',
                    'encaissements' => ['total' => array_fill(0, $monthCount, 0), 'categories' => []],
                    'decaissements' => ['total' => array_fill(0, $monthCount, 0), 'categories' => []],
                    'net' => array_fill(0, $monthCount, 0)
                ],
                'investissement' => [
                    'label' => 'ACTIVITÉS D\'INVESTISSEMENT (II)',
                    'encaissements' => ['total' => array_fill(0, $monthCount, 0), 'categories' => []],
                    'decaissements' => ['total' => array_fill(0, $monthCount, 0), 'categories' => []],
                    'net' => array_fill(0, $monthCount, 0)
                ],
                'financement' => [
                    'label' => 'ACTIVITÉS DE FINANCEMENT (III)',
                    'encaissements' => ['total' => array_fill(0, $monthCount, 0), 'categories' => []],
                    'decaissements' => ['total' => array_fill(0, $monthCount, 0), 'categories' => []],
                    'net' => array_fill(0, $monthCount, 0)
                ]
            ],
            'global_net' => array_fill(0, $monthCount, 0),
            'cumule' => array_fill(0, $monthCount, 0)
        ];

        // 1. Récupérer TOUTES les écritures pour analyse complète (Saisie par Saisie)
        $allEcritures = EcritureComptable::where('exercices_comptables_id', $exerciceId)
            ->where('company_id', $companyId)
            ->with(['planComptable', 'posteTresorerie.category'])
            ->get();

        // 2. Calcul de la Trésorerie Initiale (RAN classe 5)
        $treso_initiale = 0;
        foreach ($allEcritures as $ec) {
            if ($ec->is_ran && $ec->planComptable && str_starts_with($ec->planComptable->numero_de_compte, '5')) {
                $treso_initiale += ($ec->debit - $ec->credit);
            }
        }
        $data['treso_initiale'] = $treso_initiale;

        // 3. Décomposition en mouvements de trésorerie.
        //
        // Le montant et le sens de chaque mouvement se lisent sur la ligne
        // elle-même, comme dans la balance ; la pièce, reconstituée sur
        // l'équilibre, ne sert plus qu'à désigner la contrepartie, donc la
        // section et le libellé. Un numéro de saisie partagé par plusieurs
        // pièces ne peut donc plus compenser encaissements et décaissements.
        // Voir AnalyseFluxTresorerie.
        $mouvements = AnalyseFluxTresorerie::mouvements(
            $allEcritures->filter(fn($e) => !$e->is_ran)
        );

        foreach ($mouvements as $mouvement) {
            $date = \Carbon\Carbon::parse($mouvement->date);

            $monthIndex = -1;
            foreach ($months as $idx => $m) {
                if ($m['id'] == $date->month && $m['year'] == $date->year) {
                    $monthIndex = $idx;
                    break;
                }
            }
            if ($monthIndex === -1) continue;

            $activityKey = $mouvement->section;
            $sensKey = $mouvement->sens;

            $data['activities'][$activityKey][$sensKey]['total'][$monthIndex] += $mouvement->montant;
            $this->addManualCategoryDetail(
                $data['activities'][$activityKey][$sensKey]['categories'],
                $mouvement->libelle, $mouvement->montant, $monthIndex, $monthCount
            );
        }


        // 4. Calculs Finaux (Nets et Cumuls)
        $currentCumul = $treso_initiale;

        for ($i = 0; $i < $monthCount; $i++) {
            $globalNetMonth = 0;

            foreach (['operationnelle', 'investissement', 'financement'] as $key) {
                $enc = $data['activities'][$key]['encaissements']['total'][$i];
                $dec = $data['activities'][$key]['decaissements']['total'][$i];
                
                $net = $enc - $dec;
                
                $data['activities'][$key]['net'][$i] = $net;
                $globalNetMonth += $net;
            }

            $data['global_net'][$i] = $globalNetMonth;
            
            $currentCumul += $globalNetMonth;
            $data['cumule'][$i] = $currentCumul;
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

    /**
     * Rapport : Balance Analytique.
     * Somme les ventilations par section pour un axe donné.
     */
    public function getBalanceAnalytiqueData($exerciceId, $companyId, $axeId)
    {
        return \App\Models\VentilationAnalytique::query()
            ->join('ecriture_comptables', 'ventilations_analytiques.ecriture_id', '=', 'ecriture_comptables.id')
            ->join('sections_analytiques', 'ventilations_analytiques.section_id', '=', 'sections_analytiques.id')
            ->where('ecriture_comptables.exercices_comptables_id', $exerciceId)
            ->where('ecriture_comptables.company_id', $companyId)
            ->where('sections_analytiques.axe_id', $axeId)
            ->select(
                'sections_analytiques.code',
                'sections_analytiques.libelle',
                DB::raw('SUM(ventilations_analytiques.montant) as total_montant')
            )
            ->groupBy('sections_analytiques.code', 'sections_analytiques.libelle')
            ->get();
    }

    /**
     * Rapport : Grand Livre Analytique.
     * Liste chronologique des écritures ventilées.
     */
    public function getGrandLivreAnalytiqueData($exerciceId, $companyId, $axeId, $sectionId = null)
    {
        $query = \App\Models\VentilationAnalytique::query()
            ->with(['ecriture.planComptable', 'section'])
            ->join('ecriture_comptables', 'ventilations_analytiques.ecriture_id', '=', 'ecriture_comptables.id')
            ->join('sections_analytiques', 'ventilations_analytiques.section_id', '=', 'sections_analytiques.id')
            ->where('ecriture_comptables.exercices_comptables_id', $exerciceId)
            ->where('ecriture_comptables.company_id', $companyId)
            ->where('sections_analytiques.axe_id', $axeId);

        if ($sectionId) {
            $query->where('ventilations_analytiques.section_id', $sectionId);
        }

        return $query->orderBy('ecriture_comptables.date')->get();
    }

    /**
     * Rapport : Résultat Analytique.
     * Calcul (Produits - Charges) ventilé par axe/section.
     */
    public function getResultatAnalytiqueData($exerciceId, $companyId, $axeId)
    {
        $ventilations = \App\Models\VentilationAnalytique::query()
            ->join('ecriture_comptables', 'ventilations_analytiques.ecriture_id', '=', 'ecriture_comptables.id')
            ->join('plan_comptables', 'ecriture_comptables.plan_comptable_id', '=', 'plan_comptables.id')
            ->join('sections_analytiques', 'ventilations_analytiques.section_id', '=', 'sections_analytiques.id')
            ->where('ecriture_comptables.exercices_comptables_id', $exerciceId)
            ->where('ecriture_comptables.company_id', $companyId)
            ->where('sections_analytiques.axe_id', $axeId)
            ->where(function($q) {
                $q->where('plan_comptables.numero_de_compte', 'LIKE', '6%')
                  ->orWhere('plan_comptables.numero_de_compte', 'LIKE', '7%');
            })
            ->select(
                'sections_analytiques.code',
                'sections_analytiques.libelle',
                DB::raw('SUM(CASE WHEN plan_comptables.numero_de_compte LIKE "7%" THEN ventilations_analytiques.montant ELSE -ventilations_analytiques.montant END) as resultat')
            )
            ->groupBy('sections_analytiques.code', 'sections_analytiques.libelle')
            ->get();

        return $ventilations;
    }
}
