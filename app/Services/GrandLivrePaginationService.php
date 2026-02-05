<?php

namespace App\Services;

use Carbon\Carbon;

class GrandLivrePaginationService
{
    protected $linesPerPage = 32; // Ajustable selon la densité souhaitée
    protected $minAccountSpace = 6; // Espace minimum (lignes) pour commencer un compte sur une page

    /**
     * Transforme une collection d'écritures en une structure paginée avec reports.
     *
     * @param \Illuminate\Support\Collection $ecritures
     * @param array $soldesInitiaux
     * @param string $titre
     * @param string $displayMode
     * @return array
     */
    public function paginate($ecritures, $soldesInitiaux, $titre, $displayMode = 'comptaflow')
    {
        $isTiersReport = ($titre !== 'Grand-livre des comptes' && $titre !== 'Prévisualisation Grand-livre des comptes');

        // 1. Groupage initial (Par Compte OU Tiers)
        if ($isTiersReport) {
            $grouped = $ecritures->groupBy('plan_tiers_id');
        } else {
            $grouped = $ecritures->groupBy('plan_comptable_id');
        }

        $pages = [];
        $currentPageRows = [];
        $linesOnCurrentPage = 0;

        // Totaux Généraux du rapport
        $grandTotalDebit = 0;
        $grandTotalCredit = 0;
        $grandTotalSolde = 0; // Solde progressif global (incluant SI)

        // Boucle sur chaque COMPTE
        foreach ($grouped as $groupId => $accountOperations) {
            
            // --- A. Préparation des données du compte ---
            
            // Récupération infos compte
            if (!$isTiersReport) {
                $compteModel = $accountOperations->first()->planComptable;
                $numero = $compteModel->numero_de_compte ?? '-';
                $numero_orig = $compteModel->numero_original;
                $intitule = $compteModel->intitule ?? 'Intitulé inconnu';
            } else {
                $compteModel = $accountOperations->first()->planTiers;
                $numero = $compteModel->numero_de_tiers ?? '-';
                $numero_orig = $compteModel->numero_original;
                $intitule = $compteModel->intitule ?? 'Intitulé inconnu';
            }

            // Logique d'affichage du numéro (Header)
            $numeroAffiche = $numero;
            $numeroSecondaire = null;
            if ($displayMode === 'origine' && !empty($numero_orig)) {
                $numeroAffiche = $numero_orig;
            } elseif ($displayMode === 'both' && !empty($numero_orig) && $numero_orig !== $numero) {
                $numeroSecondaire = $numero_orig;
            }

            // Solde Initial
            $si = $soldesInitiaux[$groupId] ?? ['debit' => 0, 'credit' => 0, 'solde' => 0];
            $currentSolde = (float)$si['solde'];
            
            // Totaux du compte
            $accountTotalDebit = (float)$si['debit']; // Inclut SI
            $accountTotalCredit = (float)$si['credit']; // Inclut SI

            // --- B. Génération des Lignes (Rows) pour ce compte ---
            $accountRows = [];

            // 1. Ligne En-tête Compte
            $accountRows[] = [
                'type' => 'account_header',
                'numero' => $numeroAffiche,
                'intitule' => $intitule,
                'numero_secondaire' => $numeroSecondaire
            ];

            // 2. Ligne Report Solde Initial (si non nul)
            if ($si['debit'] != 0 || $si['credit'] != 0) {
                $accountRows[] = [
                    'type' => 'initial_balance',
                    'debit' => $si['debit'],
                    'credit' => $si['credit'],
                    'solde' => $si['solde']
                ];
            }

            // 3. Traitement des écritures (Groupées par N° Saisie)
            $operationsByEntry = $accountOperations->groupBy('n_saisie');

            foreach ($operationsByEntry as $nSaisie => $entryLines) {
                $entryDebit = 0;
                $entryCredit = 0;

                // Lignes d'écriture
                foreach ($entryLines as $ecriture) {
                    $debit = $ecriture->debit ?? 0;
                    $credit = $ecriture->credit ?? 0;
                    $currentSolde += ($debit - $credit);
                    
                    $entryDebit += $debit;
                    $entryCredit += $credit;
                    
                    $accountTotalDebit += $debit;
                    $accountTotalCredit += $credit;

                    // Formatage des données pour l'affichage (similaire à la vue actuelle)
                    $formattedData = $this->formatEntryData($ecriture, $displayMode);
                    $formattedData['solde_progressif'] = $currentSolde;

                    $accountRows[] = [
                        'type' => 'entry_line',
                        'data' => $formattedData
                    ];
                }

                // Ligne Sous-total Saisie
                $accountRows[] = [
                    'type' => 'entry_subtotal',
                    'n_saisie' => $nSaisie,
                    'debit' => $entryDebit,
                    'credit' => $entryCredit,
                    'solde' => $currentSolde
                ];
            }

            // 4. Ligne Total Compte
            $accountRows[] = [
                'type' => 'account_total',
                'numero' => $numeroAffiche,
                'debit' => $accountTotalDebit, // Total incluant SI + Mouvements
                'credit' => $accountTotalCredit,
                'solde' => $currentSolde
            ];
            
            // Ajout aux totaux généraux
            $grandTotalDebit += $accountTotalDebit;
            $grandTotalCredit += $accountTotalCredit;
            $grandTotalSolde += $currentSolde; // Non utilisé directement mais pour vérif


            // --- C. Pagination des lignes du compte ---
            
            foreach ($accountRows as $row) {
                // Estimer la hauteur de la ligne (1 = ligne standard)
                // En-tête compte = 2 lignes visuelles
                $weight = ($row['type'] === 'account_header') ? 2 : 1;

                if ($linesOnCurrentPage + $weight > $this->linesPerPage) {
                    // PAGE PLEINE -> AJOUTER "A REPORTER"
                    
                    // On recule pour trouver le solde progressif de la dernière ligne "entry_line" ou "initial_balance" ajoutée
                    // Pour afficher les totaux courants en bas de page
                    
                    // NOTE IMPORTANTE: Le "A Reporter" d'un Grand Livre montre généralement
                    // le Cumul Débit et Cumul Crédit (incluant SI) à ce stade.
                    // Nous devons donc suivre le cumul courant pendant l'itération des lignes.
                    // C'est complexe car $accountRows contient déjà tout calculé.
                    
                    // CORRECTION STRATÉGIE:
                    // Au lieu de pré-calculer tout $accountRows, nous devons calculer les totaux *pendant* la pagination.
                    // Mais pour le groupage saisie, c'est dur de couper au milieu.
                    
                    // SIMPLIFICATION SAGE:
                    // Sage coupe parfois au milieu d'une saisie.
                    // Ici, nous allons simplement insérer la ligne report.
                    // Le montant "A Reporter" sera le cumul débit/crédit DU COMPTE EN COURS jusqu'à cette ligne.
                    
                    // On ne peut pas facilement recalculer ici sans re-itérer.
                    // Pour faire simple et robuste : On va stocker les cumuls progressifs DANS $accountRows lors de la création ci-dessus ?
                    // Non, trop lourd.
                    
                    // On va ajouter le report avec des valeurs 0 temporaires, 
                    // et on aura besoin d'un "Running Total" lors de l'ajout dans $pages.
                }
            }
        } // Fin foreach Account

        // RE-PLANIFICATION DE LA BOUCLE DE PAGINATION
        // Pour gérer les reports corrects, on doit paginer FLUX CONTINU.
        
        return $this->processPagination($grouped, $soldesInitiaux, $isTiersReport, $displayMode);
    }

    private function processPagination($grouped, $soldesInitiaux, $isTiersReport, $displayMode)
    {
        $pages = [];
        $currentPage = [];
        $lineCount = 0;
        
        $grandTotalDebit = 0;
        $grandTotalCredit = 0;
        
        // Cumuls globaux pour le rapport entier (si nécessaire) ? 
        // Non, le "A reporter" est généralement par COMPTE ou Général.
        // Sur Sage: Le "Report" en bas de page est le total général cumulé de TOUT le grand livre jusqu'à cette ligne.
        
        $runningDebit = 0;
        $runningCredit = 0;

        $isFirstAccountInReport = true;

        foreach ($grouped as $groupId => $accountOperations) {
            
            // 0. Vérification d'espace pour le nouvel ensemble (Header + Spacer + TableHeader + au moins 1 ligne)
            // Poids estimé : spacer(1) + header(2) + header_spacer(1) + table_header(1) + 1 ligne(1) = 6
            if ($lineCount + $this->minAccountSpace > $this->linesPerPage && $lineCount > 0) {
                $this->forcePageBreak($pages, $currentPage, $lineCount, $runningDebit, $runningCredit);
            }

            // Ajouter un spacer entre les comptes (sauf pour le tout premier compte DU RAPPORT)
            if (!$isFirstAccountInReport) {
                $this->addPageRow($pages, $currentPage, $lineCount, [
                    'type' => 'account_spacer'
                ], $runningDebit, $runningCredit, 1);
            }
            $isFirstAccountInReport = false;

             // ... Récupération infos compte (idem plus haut) ...
             // ... Récupération infos compte (idem plus haut) ...
             if (!$isTiersReport) {
                $compteModel = $accountOperations->first()->planComptable;
                $numero = $compteModel->numero_de_compte ?? '-';
                $numero_orig = $compteModel->numero_original;
                $intitule = $compteModel->intitule ?? 'Intitulé inconnu';
            } else {
                $compteModel = $accountOperations->first()->planTiers;
                $numero = $compteModel->numero_de_tiers ?? '-';
                $numero_orig = $compteModel->numero_original;
                $intitule = $compteModel->intitule ?? 'Intitulé inconnu';
            }

            $numeroAffiche = $numero;
            $numeroSecondaire = null;
            if ($displayMode === 'origine' && !empty($numero_orig)) {
                $numeroAffiche = $numero_orig;
            } elseif ($displayMode === 'both' && !empty($numero_orig) && $numero_orig !== $numero) {
                $numeroSecondaire = $numero_orig;
            }

            // Solde Initial
            $si = $soldesInitiaux[$groupId] ?? ['debit' => 0, 'credit' => 0, 'solde' => 0];
            $currentSolde = (float)$si['solde'];
            
            // Ajouter aux cumuls courants (SI fait partie du total général progressif)
            $runningDebit += (float)$si['debit'];
            $runningCredit += (float)$si['credit'];
            
            // Totaux spécifiques au compte (pour la ligne "Total Compte" à la fin)
            $accountDebit = (float)$si['debit'];
            $accountCredit = (float)$si['credit'];

            // 1. Ajouter Header Compte
            $this->addPageRow($pages, $currentPage, $lineCount, [
                'type' => 'account_header',
                'numero' => $numeroAffiche,
                'intitule' => $intitule,
                'numero_secondaire' => $numeroSecondaire
            ], $runningDebit, $runningCredit, 2);

            // 2. Petit espace sous l'en-tête (Image-style)
            $this->addPageRow($pages, $currentPage, $lineCount, [
                'type' => 'header_spacer'
            ], $runningDebit, $runningCredit, 1);

            // 3. Répéter l'en-tête de colonnes (Repeated per account)
            $this->addPageRow($pages, $currentPage, $lineCount, [
                'type' => 'table_header'
            ], $runningDebit, $runningCredit, 1);

            // 4. Ajouter Report SI
            if ($si['debit'] != 0 || $si['credit'] != 0) {
                $this->addPageRow($pages, $currentPage, $lineCount, [
                    'type' => 'initial_balance',
                    'debit' => $si['debit'],
                    'credit' => $si['credit'],
                    'solde' => $si['solde']
                ], $runningDebit, $runningCredit);
            }

            // 3. Écritures
            $operationsByEntry = $accountOperations->groupBy('n_saisie');

            foreach ($operationsByEntry as $nSaisie => $entryLines) {
                $entryDebit = 0;
                $entryCredit = 0;

                foreach ($entryLines as $ecriture) {
                    $debit = $ecriture->debit ?? 0;
                    $credit = $ecriture->credit ?? 0;
                    
                    $currentSolde += ($debit - $credit);
                    
                    $entryDebit += $debit;
                    $entryCredit += $credit;
                    
                    $accountDebit += $debit;
                    $accountCredit += $credit;
                    
                    $runningDebit += $debit;
                    $runningCredit += $credit;

                    $formattedData = $this->formatEntryData($ecriture, $displayMode);
                    $formattedData['solde_progressif'] = $currentSolde;

                    $this->addPageRow($pages, $currentPage, $lineCount, [
                        'type' => 'entry_line',
                        'data' => $formattedData
                    ], $runningDebit, $runningCredit);
                }

                // Sous-total Saisie
                $this->addPageRow($pages, $currentPage, $lineCount, [
                    'type' => 'entry_subtotal',
                    'n_saisie' => $nSaisie,
                    'debit' => $entryDebit,
                    'credit' => $entryCredit,
                    'solde' => $currentSolde
                ], $runningDebit, $runningCredit);
            }

            // 4. Total Compte
            $this->addPageRow($pages, $currentPage, $lineCount, [
                'type' => 'account_total',
                'numero' => $numeroAffiche,
                'debit' => $accountDebit,
                'credit' => $accountCredit,
                'solde' => $currentSolde
            ], $runningDebit, $runningCredit);

        } // End Foreach Account

        // Flush last page
        if (!empty($currentPage)) {
            // Ajouter Total Général Fin de Rapport sur la dernière page
            // (ou une ligne spéciale 'grand_total')
            
            // Calcul des mvt période (Running Total - Sum(SI)) ? 
            // On peut passer les totaux globaux séparément à la vue.
            $pages[] = $currentPage;
        }

        // Retourner structure complète
        return [
            'pages' => $pages,
            'grand_total_debit' => $runningDebit,
            'grand_total_credit' => $runningCredit
        ];
    }

    private function addPageRow(&$pages, &$currentPage, &$lineCount, $row, $runningDebit, $runningCredit, $weight = 1)
    {
        // Vérifier si page pleine
        if ($lineCount + $weight > $this->linesPerPage) {
            $this->forcePageBreak($pages, $currentPage, $lineCount, $runningDebit, $runningCredit);
        }

        // Si on vient d'ajouter un spacer mais qu'il tombe en haut d'une nouvelle page, on l'ignore
        if (isset($row['type']) && $row['type'] === 'account_spacer' && $lineCount === 0) {
            return;
        }

        // Ajouter la ligne
        $currentPage[] = $row;
        $lineCount += $weight;
    }

    private function forcePageBreak(&$pages, &$currentPage, &$lineCount, $runningDebit, $runningCredit)
    {
        if (empty($currentPage)) return;

        // AJOUTER "A REPORTER"
        $currentPage[] = [
            'type' => 'to_report',
            'debit' => $runningDebit,
            'credit' => $runningCredit
        ];
        
        // SAUVEGARDER PAGE
        $pages[] = $currentPage;
        
        // NOUVELLE PAGE
        $currentPage = [];
        $lineCount = 0;
        
        // AJOUTER "REPORT"
        $currentPage[] = [
            'type' => 'reported',
            'debit' => $runningDebit,
            'credit' => $runningCredit
        ];
        $lineCount += 1; // La ligne report compte
    }

    private function formatEntryData($ecriture, $displayMode)
    {
        // Logique de formatage identique à la vue précédente
        $jl_sys = $ecriture->codeJournal->code_journal ?? '-';
        $jl_orig = $ecriture->codeJournal->numero_original ?? ''; 
        $aff_jl = match ($displayMode) {
            'origine' => !empty($jl_orig) ? $jl_orig : $jl_sys,
            'both' => ($jl_sys . (!empty($jl_orig) && $jl_orig !== $jl_sys ? '<br><span style="color:#555;font-size:8px">(' . $jl_orig . ')</span>' : '')),
            default => $jl_sys,
        };

        $n_sys = $ecriture->n_saisie ?? '-';
        $n_orig = $ecriture->n_saisie_user ?? ''; 
        $aff_n_saisie = match ($displayMode) {
            'origine' => !empty($n_orig) ? $n_orig : $n_sys,
            'both' => ($n_sys . (!empty($n_orig) && $n_orig !== $n_sys ? '<br><span style="color:#555;font-size:8px">(' . $n_orig . ')</span>' : '')),
            default => $n_sys,
        };

        $cpt_sys = $ecriture->planComptable->numero_de_compte ?? '';
        $cpt_orig = $ecriture->planComptable->numero_original ?? '';
        $aff_compte = match ($displayMode) {
            'origine' => !empty($cpt_orig) ? $cpt_orig : $cpt_sys,
            'both' => ($cpt_sys . (!empty($cpt_orig) && $cpt_orig !== $cpt_sys ? '<br><span style="color:#555;font-size:8px">(' . $cpt_orig . ')</span>' : '')),
            default => $cpt_sys,
        };

        $tiers_sys = $ecriture->planTiers->numero_de_tiers ?? '';
        $tiers_orig = $ecriture->planTiers->numero_original ?? '';
        $aff_tiers = match ($displayMode) {
            'origine' => !empty($tiers_orig) ? $tiers_orig : $tiers_sys,
            'both' => ($tiers_sys . (!empty($tiers_orig) && $tiers_orig !== $tiers_sys ? '<br><span style="color:#555;font-size:8px">(' . $tiers_orig . ')</span>' : '')),
            default => $tiers_sys,
        };

        return [
            'date' => $ecriture->date,
            'aff_jl' => $aff_jl,
            'aff_n_saisie' => $aff_n_saisie,
            'n_piece' => $ecriture->reference_piece ?? '-',
            'aff_compte' => $aff_compte,
            'aff_tiers' => $aff_tiers,
            'libelle' => $ecriture->description_operation,
            'lettrage' => $ecriture->lettrage ?? '',
            'debit' => $ecriture->debit,
            'credit' => $ecriture->credit,
        ];
    }
}
