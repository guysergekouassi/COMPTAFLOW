<?php

namespace App\Services;

use Mpdf\Mpdf;
use Mpdf\Config\ConfigVariables;
use Mpdf\Config\FontVariables;

/**
 * GrandLivrePdfService
 *
 * Génère le Grand Livre en PDF via mPDF en écrivant les cellules directement
 * (sans parsing HTML), ce qui est 10 à 50x plus rapide que DOMPDF.
 *
 * Architecture identique à Sage : on écrit les primitives PDF ligne par ligne.
 */
class GrandLivrePdfService
{
    // ── Dimensions A4 paysage ─────────────────────────────────────────────
    const PAGE_W      = 297;   // mm
    const PAGE_H      = 210;   // mm
    const MARGIN_LEFT = 8;
    const MARGIN_RIGHT = 8;
    const MARGIN_TOP  = 12;
    const MARGIN_BOTTOM = 12;

    // Hauteur d'une ligne standard (mm)
    const ROW_H = 5;

    // Largeurs colonnes (A4 paysage, total utilisable ≈ 281mm)
    // Date | Journal | N°Saisie | Pièce | Compte | Tiers | Libellé | Lettr | Débit | Crédit | Solde
    const COLS = [
        'date'    => 18,
        'journal' => 10,
        'saisie'  => 18,
        'piece'   => 18,
        'compte'  => 14,
        'tiers'   => 14,
        'libelle' => 80,
        'lettr'   => 8,
        'debit'   => 26,
        'credit'  => 26,
        'solde'   => 26,
    ];

    private Mpdf $mpdf;
    private float $pageWidth;
    private string $companyName;
    private string $titre;
    private string $dateDebut;
    private string $dateFin;
    private string $compteMin;
    private string $compteMax;
    private float $currentY;
    private int $pageNum  = 0;
    private float $runningDebit  = 0;
    private float $runningCredit = 0;

    public function generate(
        $ecritures,
        array $soldesInitiaux,
        string $titre,
        string $displayMode,
        string $companyName,
        string $dateDebut,
        string $dateFin,
        string $compteMin,
        string $compteMax,
        string $outputPath
    ): void {
        $this->titre       = $titre;
        $this->companyName = $companyName;
        $this->dateDebut   = $dateDebut;
        $this->dateFin     = $dateFin;
        $this->compteMin   = $compteMin;
        $this->compteMax   = $compteMax;

        // ── Init mPDF ──────────────────────────────────────────────────────
        $this->mpdf = new Mpdf([
            'mode'            => 'utf-8',
            'format'          => 'A4-L',       // A4 paysage
            'margin_left'     => self::MARGIN_LEFT,
            'margin_right'    => self::MARGIN_RIGHT,
            'margin_top'      => self::MARGIN_TOP,
            'margin_bottom'   => self::MARGIN_BOTTOM,
            'margin_header'   => 0,
            'margin_footer'   => 0,
            'orientation'     => 'L',
            'tempDir'         => storage_path('app/mpdf_tmp'),
        ]);

        $this->mpdf->SetAutoPageBreak(false); // On gère manuellement les sauts de page
        $this->mpdf->SetTitle($titre . ' — ' . $companyName);
        $this->pageWidth = self::PAGE_W - self::MARGIN_LEFT - self::MARGIN_RIGHT;

        // ── Première page ─────────────────────────────────────────────────
        $this->mpdf->AddPage('L');
        $this->pageNum = 1;
        $this->printPageHeader();

        // ── Regrouper par compte ──────────────────────────────────────────
        $isTiers = str_contains($titre, 'Tiers');
        $grouped = $isTiers
            ? $ecritures->groupBy('plan_tiers_id')
            : $ecritures->groupBy('plan_comptable_id');

        $grandTotalDebit  = 0.0;
        $grandTotalCredit = 0.0;

        foreach ($grouped as $groupId => $ops) {
            $this->printAccount(
                $groupId, $ops, $soldesInitiaux, $isTiers, $displayMode,
                $grandTotalDebit, $grandTotalCredit
            );
        }

        // ── Total général ─────────────────────────────────────────────────
        $this->ensureSpace(8);
        $this->printGrandTotal($grandTotalDebit, $grandTotalCredit);

        // ── Sauvegarde ───────────────────────────────────────────────────
        if (!is_dir(dirname($outputPath))) {
            mkdir(dirname($outputPath), 0777, true);
        }
        $this->mpdf->Output($outputPath, 'F');
    }

    // ─────────────────────────────────────────────────────────────────────
    //  Rendu d'un compte
    // ─────────────────────────────────────────────────────────────────────
    private function printAccount(
        $groupId, $ops, array $soldesInitiaux, bool $isTiers, string $displayMode,
        float &$grandTotalDebit, float &$grandTotalCredit
    ): void {
        $first = $ops->first();
        if ($isTiers) {
            $model   = $first->planTiers;
            $numero  = $model?->numero_de_tiers ?? '-';
            $numOrig = $model?->numero_original ?? '';
            $intitule = $model?->intitule ?? 'Intitulé inconnu';
        } else {
            $model   = $first->planComptable;
            $numero  = $model?->numero_de_compte ?? '-';
            $numOrig = $model?->numero_original ?? '';
            $intitule = $model?->intitule ?? 'Intitulé inconnu';
        }

        $numeroAffiche = $this->resolveNum($numero, $numOrig, $displayMode);

        // Solde initial
        $si = $soldesInitiaux[$groupId] ?? ['debit' => 0, 'credit' => 0, 'solde' => 0];
        $currentSolde  = (float) $si['solde'];
        $accountDebit  = (float) $si['debit'];
        $accountCredit = (float) $si['credit'];

        // Vérifier espace suffisant pour le header compte (au moins 3 lignes)
        $this->ensureSpace(3 * self::ROW_H);

        // En-tête compte
        $this->printAccountHeader($numeroAffiche, $intitule);

        // En-tête colonnes
        $this->printColumnHeader();

        // Solde initial (si non nul)
        if ($si['debit'] != 0 || $si['credit'] != 0) {
            $this->runningDebit  += (float) $si['debit'];
            $this->runningCredit += (float) $si['credit'];
            $this->printInitialBalance($si['debit'], $si['credit'], $si['solde']);
        }

        // Écritures groupées par n_saisie
        $byEntry = $ops->groupBy('n_saisie');
        foreach ($byEntry as $nSaisie => $lines) {
            $entryDebit  = 0.0;
            $entryCredit = 0.0;

            foreach ($lines as $e) {
                $d = (float)($e->debit ?? 0);
                $c = (float)($e->credit ?? 0);
                $currentSolde  += ($d - $c);
                $entryDebit    += $d;
                $entryCredit   += $c;
                $accountDebit  += $d;
                $accountCredit += $c;
                $this->runningDebit  += $d;
                $this->runningCredit += $c;

                $this->ensureSpace(self::ROW_H);
                $this->printEntryLine($e, $displayMode, $currentSolde);
            }

            // Sous-total saisie
            $this->ensureSpace(self::ROW_H);
            $this->printSubtotal($nSaisie, $entryDebit, $entryCredit, $currentSolde);
        }

        // Total compte
        $this->ensureSpace(self::ROW_H + 1);
        $this->printAccountTotal($numeroAffiche, $accountDebit, $accountCredit, $currentSolde);

        $grandTotalDebit  += $accountDebit;
        $grandTotalCredit += $accountCredit;
    }

    // ─────────────────────────────────────────────────────────────────────
    //  Gestion des pages
    // ─────────────────────────────────────────────────────────────────────
    private function ensureSpace(float $neededMm): void
    {
        $maxY = self::PAGE_H - self::MARGIN_BOTTOM - self::MARGIN_TOP - 14; // footer zone
        if ($this->currentY + $neededMm > $maxY) {
            $this->printPageFooter();
            $this->mpdf->AddPage('L');
            $this->pageNum++;
            $this->printPageHeader();
            $this->printReportLine();
        }
    }

    private function printPageHeader(): void
    {
        $y = self::MARGIN_TOP;
        $this->mpdf->SetFont('dejavusans', 'B', 8);
        $this->mpdf->SetXY(self::MARGIN_LEFT, $y);
        $this->mpdf->Cell($this->pageWidth / 2, 4, $this->companyName, 0, 0, 'L');
        $this->mpdf->Cell($this->pageWidth / 2, 4, 'Page ' . $this->pageNum, 0, 1, 'R');

        $this->mpdf->SetFont('dejavusans', 'B', 9);
        $this->mpdf->SetX(self::MARGIN_LEFT);
        $this->mpdf->Cell($this->pageWidth, 5, $this->titre, 0, 1, 'C');

        $this->mpdf->SetFont('dejavusans', '', 7);
        $this->mpdf->SetX(self::MARGIN_LEFT);
        $period = 'Période du ' . $this->formatDate($this->dateDebut) . ' au ' . $this->formatDate($this->dateFin);
        $range  = 'Comptes : ' . $this->compteMin . ' → ' . $this->compteMax;
        $this->mpdf->Cell($this->pageWidth / 2, 4, $period, 0, 0, 'L');
        $this->mpdf->Cell($this->pageWidth / 2, 4, $range, 0, 1, 'R');

        // Ligne séparatrice
        $this->mpdf->SetDrawColor(100, 100, 100);
        $this->mpdf->Line(self::MARGIN_LEFT, self::MARGIN_TOP + 13, self::MARGIN_LEFT + $this->pageWidth, self::MARGIN_TOP + 13);

        $this->currentY = self::MARGIN_TOP + 15;
        $this->mpdf->SetY($this->currentY);
    }

    private function printPageFooter(): void
    {
        $footerY = self::PAGE_H - self::MARGIN_BOTTOM - 6;
        $this->mpdf->SetFont('dejavusans', 'I', 7);
        $this->mpdf->SetXY(self::MARGIN_LEFT, $footerY);
        $this->mpdf->SetFillColor(235, 235, 235);
        $cols = self::COLS;
        $w1 = $this->pageWidth - $cols['debit'] - $cols['credit'];
        $this->mpdf->Cell($w1, 5, 'À REPORTER', 1, 0, 'R', true);
        $this->mpdf->SetFont('dejavusans', 'B', 7);
        $this->mpdf->Cell($cols['debit'],  5, $this->fmt($this->runningDebit),  1, 0, 'R', true);
        $this->mpdf->Cell($cols['credit'], 5, $this->fmt($this->runningCredit), 1, 1, 'R', true);
    }

    private function printReportLine(): void
    {
        $this->mpdf->SetFont('dejavusans', 'I', 7);
        $this->mpdf->SetXY(self::MARGIN_LEFT, $this->currentY);
        $this->mpdf->SetFillColor(235, 235, 235);
        $cols = self::COLS;
        $w1 = $this->pageWidth - $cols['debit'] - $cols['credit'];
        $this->mpdf->Cell($w1, self::ROW_H, 'REPORT', 1, 0, 'R', true);
        $this->mpdf->SetFont('dejavusans', 'B', 7);
        $this->mpdf->Cell($cols['debit'],  self::ROW_H, $this->fmt($this->runningDebit),  1, 0, 'R', true);
        $this->mpdf->Cell($cols['credit'], self::ROW_H, $this->fmt($this->runningCredit), 1, 1, 'R', true);
        $this->currentY += self::ROW_H + 1;
        $this->mpdf->SetY($this->currentY);
    }

    // ─────────────────────────────────────────────────────────────────────
    //  Lignes de contenu
    // ─────────────────────────────────────────────────────────────────────
    private function printAccountHeader(string $numero, string $intitule): void
    {
        $this->mpdf->SetXY(self::MARGIN_LEFT, $this->currentY);
        $this->mpdf->SetFillColor(30, 60, 120);
        $this->mpdf->SetTextColor(255, 255, 255);
        $this->mpdf->SetFont('dejavusans', 'B', 8);
        $this->mpdf->Cell($this->pageWidth, 6, '  ' . $numero . '  —  ' . $intitule, 0, 1, 'L', true);
        $this->mpdf->SetTextColor(0, 0, 0);
        $this->currentY += 7;
        $this->mpdf->SetY($this->currentY);
    }

    private function printColumnHeader(): void
    {
        $this->mpdf->SetFont('dejavusans', 'B', 6.5);
        $this->mpdf->SetFillColor(220, 230, 245);
        $this->mpdf->SetXY(self::MARGIN_LEFT, $this->currentY);
        $cols = self::COLS;
        $this->mpdf->Cell($cols['date'],    self::ROW_H, 'Date',       1, 0, 'C', true);
        $this->mpdf->Cell($cols['journal'], self::ROW_H, 'Jnl',        1, 0, 'C', true);
        $this->mpdf->Cell($cols['saisie'],  self::ROW_H, 'N° Saisie',  1, 0, 'C', true);
        $this->mpdf->Cell($cols['piece'],   self::ROW_H, 'Pièce',      1, 0, 'C', true);
        $this->mpdf->Cell($cols['compte'],  self::ROW_H, 'Compte',     1, 0, 'C', true);
        $this->mpdf->Cell($cols['tiers'],   self::ROW_H, 'Tiers',      1, 0, 'C', true);
        $this->mpdf->Cell($cols['libelle'], self::ROW_H, 'Libellé',    1, 0, 'C', true);
        $this->mpdf->Cell($cols['lettr'],   self::ROW_H, 'Ltr',        1, 0, 'C', true);
        $this->mpdf->Cell($cols['debit'],   self::ROW_H, 'Débit',      1, 0, 'R', true);
        $this->mpdf->Cell($cols['credit'],  self::ROW_H, 'Crédit',     1, 0, 'R', true);
        $this->mpdf->Cell($cols['solde'],   self::ROW_H, 'Solde',      1, 1, 'R', true);
        $this->currentY += self::ROW_H;
        $this->mpdf->SetY($this->currentY);
    }

    private function printInitialBalance(float $debit, float $credit, float $solde): void
    {
        $cols = self::COLS;
        $this->mpdf->SetFont('dejavusans', 'I', 6.5);
        $this->mpdf->SetFillColor(248, 248, 220);
        $this->mpdf->SetXY(self::MARGIN_LEFT, $this->currentY);
        $labelWidth = $cols['date'] + $cols['journal'] + $cols['saisie'] + $cols['piece'] + $cols['compte'] + $cols['tiers'] + $cols['libelle'] + $cols['lettr'];
        $this->mpdf->Cell($labelWidth, self::ROW_H, 'SOLDE INITIAL', 1, 0, 'C', true);
        $this->mpdf->SetFont('dejavusans', 'B', 6.5);
        $this->mpdf->Cell($cols['debit'],  self::ROW_H, $debit  != 0 ? $this->fmt($debit)  : '', 1, 0, 'R', true);
        $this->mpdf->Cell($cols['credit'], self::ROW_H, $credit != 0 ? $this->fmt($credit) : '', 1, 0, 'R', true);
        $this->mpdf->Cell($cols['solde'],  self::ROW_H, $this->fmt($solde), 1, 1, 'R', true);
        $this->currentY += self::ROW_H;
        $this->mpdf->SetY($this->currentY);
    }

    private function printEntryLine($e, string $displayMode, float $solde): void
    {
        $cols = self::COLS;

        $jl  = $this->resolveDisplay(
            $e->codeJournal?->code_journal    ?? '-',
            $e->codeJournal?->numero_original ?? '',
            $displayMode
        );
        $ns  = $this->resolveDisplay($e->n_saisie ?? '-', $e->n_saisie_user ?? '', $displayMode);
        $cpt = $this->resolveDisplay(
            $e->planComptable?->numero_de_compte ?? '',
            $e->planComptable?->numero_original  ?? '',
            $displayMode
        );
        $tiers = $this->resolveDisplay(
            $e->planTiers?->numero_de_tiers ?? '',
            $e->planTiers?->numero_original ?? '',
            $displayMode
        );

        $d = (float)($e->debit  ?? 0);
        $c = (float)($e->credit ?? 0);

        $this->mpdf->SetFont('dejavusans', '', 6.5);
        $this->mpdf->SetFillColor(255, 255, 255);
        $this->mpdf->SetXY(self::MARGIN_LEFT, $this->currentY);

        $this->mpdf->Cell($cols['date'],    self::ROW_H, $this->formatDate($e->date ?? ''), 1, 0, 'C');
        $this->mpdf->Cell($cols['journal'], self::ROW_H, mb_substr($jl, 0, 6),       1, 0, 'C');
        $this->mpdf->Cell($cols['saisie'],  self::ROW_H, mb_substr($ns, 0, 15),      1, 0, 'C');
        $this->mpdf->Cell($cols['piece'],   self::ROW_H, mb_substr($e->reference_piece ?? '-', 0, 15), 1, 0, 'C');
        $this->mpdf->Cell($cols['compte'],  self::ROW_H, mb_substr($cpt, 0, 10),     1, 0, 'C');
        $this->mpdf->Cell($cols['tiers'],   self::ROW_H, mb_substr($tiers, 0, 10),   1, 0, 'C');
        $this->mpdf->Cell($cols['libelle'], self::ROW_H, mb_substr($e->description_operation ?? '', 0, 55), 1, 0, 'L');
        $this->mpdf->Cell($cols['lettr'],   self::ROW_H, mb_substr($e->lettrage ?? '', 0, 4), 1, 0, 'C');
        $this->mpdf->Cell($cols['debit'],   self::ROW_H, $d > 0 ? $this->fmt($d) : '', 1, 0, 'R');
        $this->mpdf->Cell($cols['credit'],  self::ROW_H, $c > 0 ? $this->fmt($c) : '', 1, 0, 'R');
        $this->mpdf->Cell($cols['solde'],   self::ROW_H, $this->fmtSolde($solde), 1, 1, 'R');

        $this->currentY += self::ROW_H;
        $this->mpdf->SetY($this->currentY);
    }

    private function printSubtotal(string $nSaisie, float $debit, float $credit, float $solde): void
    {
        $cols = self::COLS;
        $this->mpdf->SetFont('dejavusans', 'B', 6.5);
        $this->mpdf->SetFillColor(240, 240, 250);
        $this->mpdf->SetXY(self::MARGIN_LEFT, $this->currentY);
        $labelWidth = $cols['date'] + $cols['journal'] + $cols['saisie'] + $cols['piece'] + $cols['compte'] + $cols['tiers'] + $cols['libelle'] + $cols['lettr'];
        $this->mpdf->Cell($labelWidth, self::ROW_H, 'Sous-total saisie ' . $nSaisie, 1, 0, 'R', true);
        $this->mpdf->Cell($cols['debit'],  self::ROW_H, $this->fmt($debit),  1, 0, 'R', true);
        $this->mpdf->Cell($cols['credit'], self::ROW_H, $this->fmt($credit), 1, 0, 'R', true);
        $this->mpdf->Cell($cols['solde'],  self::ROW_H, $this->fmtSolde($solde), 1, 1, 'R', true);
        $this->currentY += self::ROW_H;
        $this->mpdf->SetY($this->currentY);
    }

    private function printAccountTotal(string $numero, float $debit, float $credit, float $solde): void
    {
        $cols = self::COLS;
        $this->mpdf->SetFont('dejavusans', 'B', 7);
        $this->mpdf->SetFillColor(200, 215, 240);
        $this->mpdf->SetXY(self::MARGIN_LEFT, $this->currentY);
        $labelWidth = $cols['date'] + $cols['journal'] + $cols['saisie'] + $cols['piece'] + $cols['compte'] + $cols['tiers'] + $cols['libelle'] + $cols['lettr'];
        $this->mpdf->Cell($labelWidth, self::ROW_H + 1, 'TOTAL  ' . $numero, 1, 0, 'R', true);
        $this->mpdf->Cell($cols['debit'],  self::ROW_H + 1, $this->fmt($debit),  1, 0, 'R', true);
        $this->mpdf->Cell($cols['credit'], self::ROW_H + 1, $this->fmt($credit), 1, 0, 'R', true);
        $this->mpdf->Cell($cols['solde'],  self::ROW_H + 1, $this->fmtSolde($solde), 1, 1, 'R', true);
        $this->currentY += self::ROW_H + 3;
        $this->mpdf->SetY($this->currentY);
    }

    private function printGrandTotal(float $debit, float $credit): void
    {
        $cols = self::COLS;
        $this->mpdf->SetFont('dejavusans', 'B', 8);
        $this->mpdf->SetFillColor(30, 60, 120);
        $this->mpdf->SetTextColor(255, 255, 255);
        $this->mpdf->SetXY(self::MARGIN_LEFT, $this->currentY);
        $labelWidth = $cols['date'] + $cols['journal'] + $cols['saisie'] + $cols['piece'] + $cols['compte'] + $cols['tiers'] + $cols['libelle'] + $cols['lettr'];
        $this->mpdf->Cell($labelWidth, 7, 'TOTAL GÉNÉRAL', 1, 0, 'R', true);
        $this->mpdf->Cell($cols['debit'],  7, $this->fmt($debit),  1, 0, 'R', true);
        $this->mpdf->Cell($cols['credit'], 7, $this->fmt($credit), 1, 0, 'R', true);
        $this->mpdf->Cell($cols['solde'],  7, $this->fmtSolde($debit - $credit), 1, 1, 'R', true);
        $this->mpdf->SetTextColor(0, 0, 0);
    }

    // ─────────────────────────────────────────────────────────────────────
    //  Utilitaires
    // ─────────────────────────────────────────────────────────────────────
    private function fmt(float $v): string
    {
        return number_format($v, 2, ',', ' ');
    }

    private function fmtSolde(float $v): string
    {
        return ($v < 0 ? '(' : '') . number_format(abs($v), 2, ',', ' ') . ($v < 0 ? ')' : '');
    }

    private function formatDate(string $date): string
    {
        try {
            return \Carbon\Carbon::parse($date)->format('d/m/Y');
        } catch (\Throwable) {
            return $date;
        }
    }

    private function resolveNum(string $num, string $numOrig, string $displayMode): string
    {
        return match ($displayMode) {
            'origine' => !empty($numOrig) ? $numOrig : $num,
            'both'    => !empty($numOrig) && $numOrig !== $num ? $num . ' (' . $numOrig . ')' : $num,
            default   => $num,
        };
    }

    private function resolveDisplay(string $sys, string $orig, string $displayMode): string
    {
        return match ($displayMode) {
            'origine' => !empty($orig) ? $orig : $sys,
            'both'    => !empty($orig) && $orig !== $sys ? $sys . '(' . $orig . ')' : $sys,
            default   => $sys,
        };
    }
}
