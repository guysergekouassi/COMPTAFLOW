<?php

namespace App\Services;

/**
 * GrandLivrePaginationService - VERSION OPTIMISÉE
 *
 * Transforme une collection d'écritures (déjà chargées en mémoire depuis la BDD)
 * en une structure paginée prête pour le rendu DOMPDF.
 *
 * OPTIMISATIONS CLÉ :
 *  - Un seul parcours de données (suppression de la double boucle précédente)
 *  - Pas de reformatage redondant
 *  - Calcul progressif en un flux linéaire
 */
class GrandLivrePaginationService
{
    // Nombre de lignes par page A4 (22 = sécuritaire, évite les débordements DOMPDF)
    protected int $linesPerPage = 22;

    // Espace minimum (lignes) pour démarrer un compte sur la page courante
    // (pour éviter un header isolé en bas de page)
    protected int $minAccountSpace = 5;

    /**
     * Point d'entrée unique.
     * Transforme $ecritures en structure paginée.
     *
     * @param \Illuminate\Support\Collection $ecritures  Collection d'objets EcritureComptable (avec relations déjà chargées)
     * @param array                          $soldesInitiaux  Tableau indexé par plan_comptable_id (ou plan_tiers_id)
     * @param string                         $titre
     * @param string                         $displayMode  'comptaflow' | 'origine' | 'both'
     * @return array  ['pages' => [...], 'grand_total_debit' => float, 'grand_total_credit' => float]
     */
    public function paginate($ecritures, array $soldesInitiaux, string $titre, ?string $displayMode = 'comptaflow'): array
    {
        $displayMode = $displayMode ?? 'comptaflow';

        $isTiersReport = !in_array($titre, [
            'Grand-livre des comptes',
            'Prévisualisation Grand-livre des comptes',
        ]);

        // ── Grouper par compte (une seule fois) ─────────────────────────────
        $grouped = $isTiersReport
            ? $ecritures->groupBy('plan_tiers_id')
            : $ecritures->groupBy('plan_comptable_id');

        // ── État de la pagination ────────────────────────────────────────────
        $pages       = [];
        $currentPage = [];
        $lineCount   = 0;

        // Cumuls "A Reporter / Report" de bas de page
        $runningDebit  = 0.0;
        $runningCredit = 0.0;

        // Totaux généraux du rapport (retournés à la vue)
        $grandTotalDebit  = 0.0;
        $grandTotalCredit = 0.0;

        $isFirst = true;

        foreach ($grouped as $groupId => $accountOperations) {

            // ── Infos du compte ──────────────────────────────────────────────
            $firstRow = $accountOperations->first();

            if (!$isTiersReport) {
                $model      = $firstRow->planComptable;
                $numero     = $model?->numero_de_compte ?? '-';
                $numero_orig = $model?->numero_original ?? '';
                $intitule   = $model?->intitule ?? 'Intitulé inconnu';
            } else {
                $model      = $firstRow->planTiers;
                $numero     = $model?->numero_de_tiers ?? '-';
                $numero_orig = $model?->numero_original ?? '';
                $intitule   = $model?->intitule ?? 'Intitulé inconnu';
            }

            [$numeroAffiche, $numeroSecondaire] = $this->resolveDisplayNumber(
                $numero, $numero_orig, $displayMode
            );

            // ── Solde Initial ────────────────────────────────────────────────
            $si = $soldesInitiaux[$groupId] ?? ['debit' => 0, 'credit' => 0, 'solde' => 0];
            $currentSolde  = (float) $si['solde'];
            $accountDebit  = (float) $si['debit'];
            $accountCredit = (float) $si['credit'];

            $runningDebit  += $accountDebit;
            $runningCredit += $accountCredit;

            // ── Vérification espace page pour header compte ──────────────────
            // Si pas assez de place pour header + 1 ligne, on force un saut
            if (!$isFirst && $lineCount > 0 && ($lineCount + $this->minAccountSpace) > $this->linesPerPage) {
                $this->flushPage($pages, $currentPage, $lineCount, $runningDebit, $runningCredit);
            }

            // Spacer entre comptes (sauf tout premier)
            if (!$isFirst) {
                $this->addRow($pages, $currentPage, $lineCount, ['type' => 'account_spacer'], $runningDebit, $runningCredit);
            }
            $isFirst = false;

            // ── Header compte ────────────────────────────────────────────────
            $this->addRow($pages, $currentPage, $lineCount, [
                'type'             => 'account_header',
                'numero'           => $numeroAffiche,
                'intitule'         => $intitule,
                'numero_secondaire' => $numeroSecondaire,
            ], $runningDebit, $runningCredit, 2);

            // En-tête de colonnes
            $this->addRow($pages, $currentPage, $lineCount, ['type' => 'table_header'], $runningDebit, $runningCredit);

            // Ligne Solde Initial (si non nul)
            if ($si['debit'] != 0 || $si['credit'] != 0) {
                $this->addRow($pages, $currentPage, $lineCount, [
                    'type'   => 'initial_balance',
                    'debit'  => $si['debit'],
                    'credit' => $si['credit'],
                    'solde'  => $si['solde'],
                ], $runningDebit, $runningCredit);
            }

            // ── Écritures groupées par n_saisie ──────────────────────────────
            // groupBy() est rapide car tout est déjà en mémoire
            $byEntry = $accountOperations->groupBy('n_saisie');

            foreach ($byEntry as $nSaisie => $entryLines) {
                $entryDebit  = 0.0;
                $entryCredit = 0.0;

                foreach ($entryLines as $ecriture) {
                    $d = (float)($ecriture->debit  ?? 0);
                    $c = (float)($ecriture->credit ?? 0);

                    $currentSolde  += ($d - $c);
                    $entryDebit    += $d;
                    $entryCredit   += $c;
                    $accountDebit  += $d;
                    $accountCredit += $c;
                    $runningDebit  += $d;
                    $runningCredit += $c;

                    $this->addRow($pages, $currentPage, $lineCount, [
                        'type' => 'entry_line',
                        'data' => $this->formatEntry($ecriture, $displayMode, $currentSolde),
                    ], $runningDebit, $runningCredit);
                }

                // Sous-total saisie
                $this->addRow($pages, $currentPage, $lineCount, [
                    'type'    => 'entry_subtotal',
                    'n_saisie' => $nSaisie,
                    'debit'   => $entryDebit,
                    'credit'  => $entryCredit,
                    'solde'   => $currentSolde,
                ], $runningDebit, $runningCredit);
            }

            // ── Total compte ─────────────────────────────────────────────────
            $this->addRow($pages, $currentPage, $lineCount, [
                'type'   => 'account_total',
                'numero' => $numeroAffiche,
                'debit'  => $accountDebit,
                'credit' => $accountCredit,
                'solde'  => $currentSolde,
            ], $runningDebit, $runningCredit);

            $grandTotalDebit  += $accountDebit;
            $grandTotalCredit += $accountCredit;
        }

        // Dernière page
        if (!empty($currentPage)) {
            $pages[] = $currentPage;
        }

        return [
            'pages'              => $pages,
            'grand_total_debit'  => $grandTotalDebit,
            'grand_total_credit' => $grandTotalCredit,
        ];
    }

    // ────────────────────────────────────────────────────────────────────────
    //  Helpers privés
    // ────────────────────────────────────────────────────────────────────────

    private function addRow(array &$pages, array &$currentPage, int &$lineCount, array $row, float $runningDebit, float $runningCredit, int $weight = 1): void
    {
        // Spacer ignoré en haut de page
        if ($row['type'] === 'account_spacer' && $lineCount === 0) {
            return;
        }

        // Page pleine → flush
        if ($lineCount + $weight > $this->linesPerPage) {
            $this->flushPage($pages, $currentPage, $lineCount, $runningDebit, $runningCredit);
        }

        $currentPage[] = $row;
        $lineCount    += $weight;
    }

    private function flushPage(array &$pages, array &$currentPage, int &$lineCount, float $runningDebit, float $runningCredit): void
    {
        if (empty($currentPage)) {
            return;
        }

        // Ligne "À REPORTER" en bas de page
        $currentPage[] = [
            'type'   => 'to_report',
            'debit'  => $runningDebit,
            'credit' => $runningCredit,
        ];

        $pages[]     = $currentPage;
        $currentPage = [];
        $lineCount   = 0;

        // Ligne "REPORT" en haut de la nouvelle page
        $currentPage[] = [
            'type'   => 'reported',
            'debit'  => $runningDebit,
            'credit' => $runningCredit,
        ];
        $lineCount = 1;
    }

    /**
     * Résout le numéro à afficher selon le mode.
     * @return array [numeroAffiche, numeroSecondaire|null]
     */
    private function resolveDisplayNumber(string $numero, string $numero_orig, string $displayMode): array
    {
        if ($displayMode === 'origine' && !empty($numero_orig)) {
            return [$numero_orig, null];
        }
        if ($displayMode === 'both' && !empty($numero_orig) && $numero_orig !== $numero) {
            return [$numero, $numero_orig];
        }
        return [$numero, null];
    }

    /**
     * Formate les données d'une ligne d'écriture pour l'affichage.
     */
    private function formatEntry($ecriture, string $displayMode, float $soldeCourant): array
    {
        // Journal
        $jl_sys  = $ecriture->codeJournal?->code_journal ?? '-';
        $jl_orig = $ecriture->codeJournal?->numero_original ?? '';
        $aff_jl  = $this->resolveDisplay($jl_sys, $jl_orig, $displayMode);

        // N° saisie
        $n_sys  = $ecriture->n_saisie ?? '-';
        $n_orig = $ecriture->n_saisie_user ?? '';
        $aff_n  = $this->resolveDisplay($n_sys, $n_orig, $displayMode);

        // Compte
        $cpt_sys  = $ecriture->planComptable?->numero_de_compte ?? '';
        $cpt_orig = $ecriture->planComptable?->numero_original ?? '';
        $aff_cpt  = $this->resolveDisplay($cpt_sys, $cpt_orig, $displayMode);

        // Tiers
        $tiers_sys  = $ecriture->planTiers?->numero_de_tiers ?? '';
        $tiers_orig = $ecriture->planTiers?->numero_original ?? '';
        $aff_tiers  = $this->resolveDisplay($tiers_sys, $tiers_orig, $displayMode);

        return [
            'date'             => $ecriture->date,
            'aff_jl'           => $aff_jl,
            'aff_n_saisie'     => $aff_n,
            'n_piece'          => $ecriture->reference_piece ?? '-',
            'aff_compte'       => $aff_cpt,
            'aff_tiers'        => $aff_tiers,
            'libelle'          => $ecriture->description_operation,
            'lettrage'         => $ecriture->lettrage ?? '',
            'debit'            => $ecriture->debit,
            'credit'           => $ecriture->credit,
            'solde_progressif' => $soldeCourant,
        ];
    }

    /**
     * Résout la valeur à afficher (sys vs origine vs both) pour un champ simple.
     */
    private function resolveDisplay(string $sys, string $orig, string $displayMode): string
    {
        return match ($displayMode) {
            'origine' => !empty($orig) ? $orig : $sys,
            'both'    => $sys . (!empty($orig) && $orig !== $sys
                            ? '<br><span style="color:#555;font-size:8px">(' . $orig . ')</span>'
                            : ''),
            default   => $sys,
        };
    }
}
