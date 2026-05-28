<?php

namespace App\Services;

use Mpdf\Mpdf;

/**
 * GrandLivrePdfService — Style identique à la Balance ComptaFlow
 *
 * En-tête identique à la balance :
 *   - Ligne 1 : Société (gauche) | Titre (centre, gras) | Période (droite)
 *   - Ligne 2 : © ComptaFlow … (gauche) | Date de tirage (centre) | Page X (droite)
 *   - Séparateur
 * Filigrane : « ComptaFlow » en diagonale (identique à la balance)
 * Solde progressif : signe − pour les négatifs (pas de parenthèses)
 */
class GrandLivrePdfService
{
    // ── Mise en page A4 paysage ───────────────────────────────────────────
    const ML = 8;    // margin left
    const MR = 8;    // margin right
    const MT = 8;    // margin top
    const MB = 10;   // margin bottom
    const RH = 5;    // row height (mm)

    // Largeurs colonnes (A4 paysage : 297 - 16 = 281 mm utiles)
    // Date | Jnl | N°Saisie | Pièce | Compte | Tiers | Libellé | Ltr | Débit | Crédit | Solde
    const C = [
        'date'    => 18,
        'journal' => 10,
        'saisie'  => 20,
        'piece'   => 18,
        'compte'  => 14,
        'tiers'   => 14,
        'libelle' => 78,
        'lettr'   => 8,
        'debit'   => 27,
        'credit'  => 27,
        'solde'   => 27,
    ];

    // Couleurs
    const NAVY   = [27,  62, 110];   // Bleu marine — en-têtes colonnes + total général
    const BLUE_M = [200, 215, 235];  // Bleu moyen  — total compte
    const GREY_H = [210, 210, 210];  // Gris moyen  — en-tête compte (style balance)
    const GREY_L = [242, 242, 248];  // Gris clair  — sous-totaux saisie
    const YELLOW = [255, 252, 225];  // Jaune doux  — solde initial
    const WHITE  = [255, 255, 255];
    const ORANGE = [220,  80,   0];  // Orange      — "Impression définitive" (style balance)

    private Mpdf   $mpdf;
    private float  $pw;          // page width utile
    private float  $maxY;        // Y max avant footer
    private float  $Y;           // curseur Y courant
    private int    $pageNum = 0;
    private float  $runD = 0.0;  // cumul débit (à reporter)
    private float  $runC = 0.0;  // cumul crédit (à reporter)

    // Métadonnées rapport
    private string $companyName;
    private string $titre;
    private string $dateDebut;
    private string $dateFin;
    private string $compteMin;
    private string $compteMax;

    // ─────────────────────────────────────────────────────────────────────
    public function generate(
        $ecritures,
        array  $soldesInitiaux,
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
        $this->pw          = 297 - self::ML - self::MR;
        $this->maxY        = 210 - self::MB - 10; // zone footer réservée

        // ── Création mPDF ─────────────────────────────────────────────────
        $tmpDir = storage_path('app/mpdf_tmp');
        if (!is_dir($tmpDir)) mkdir($tmpDir, 0777, true);

        $this->mpdf = new Mpdf([
            'mode'          => 'utf-8',
            'format'        => 'A4-L',
            'margin_left'   => self::ML,
            'margin_right'  => self::MR,
            'margin_top'    => self::MT,
            'margin_bottom' => self::MB,
            'margin_header' => 0,
            'margin_footer' => 0,
            'tempDir'       => $tmpDir,
        ]);

        // Filigrane identique à la balance
        $this->mpdf->SetWatermarkText('ComptaFlow');
        $this->mpdf->watermarkTextAlpha = 0.07;
        $this->mpdf->showWatermarkText  = true;

        $this->mpdf->SetAutoPageBreak(false);
        $this->mpdf->SetTitle($titre . ' — ' . $companyName);

        // ── Première page ─────────────────────────────────────────────────
        $this->addPage();

        // ── Données ──────────────────────────────────────────────────────
        $isTiers = str_contains($titre, 'Tiers');
        $grouped = $isTiers
            ? $ecritures->groupBy('plan_tiers_id')
            : $ecritures->groupBy('plan_comptable_id');

        $grandTotalD = 0.0;
        $grandTotalC = 0.0;

        foreach ($grouped as $groupId => $ops) {
            $this->renderAccount(
                $groupId, $ops, $soldesInitiaux, $isTiers,
                $displayMode, $grandTotalD, $grandTotalC
            );
        }

        // ── Total général ─────────────────────────────────────────────────
        $this->need(8);
        $this->renderGrandTotal($grandTotalD, $grandTotalC);

        // ── Pied de la dernière page ──────────────────────────────────────
        $this->renderPageFooter();

        // ── Sauvegarde ───────────────────────────────────────────────────
        if (!is_dir(dirname($outputPath))) mkdir(dirname($outputPath), 0777, true);
        $this->mpdf->Output($outputPath, 'F');
    }

    // ═════════════════════════════════════════════════════════════════════
    //  GESTION DES PAGES
    // ═════════════════════════════════════════════════════════════════════

    private function addPage(): void
    {
        if ($this->pageNum > 0) {
            $this->renderPageFooter();
        }
        $this->mpdf->AddPage('L');
        $this->pageNum++;
        $this->renderPageHeader();
    }

    /** S'assure qu'il reste $mm mm sur la page, sinon saute de page. */
    private function need(float $mm): void
    {
        if ($this->Y + $mm > $this->maxY) {
            $this->addPage();
            // Ligne « Report » en haut de nouvelle page
            $this->renderReportLine();
        }
    }

    // ─────────────────────────────────────────────────────────────────────
    //  EN-TÊTE DE PAGE (identique au style Balance)
    // ─────────────────────────────────────────────────────────────────────
    private function renderPageHeader(): void
    {
        $y  = self::MT;
        $pw = $this->pw;
        $ml = self::ML;

        // ── Ligne 1 : Société (gras) | Titre (gras, grand) | Période ────
        $this->mpdf->SetXY($ml, $y);
        $this->mpdf->SetFont('dejavusans', 'B', 9);
        $this->mpdf->SetTextColor(0, 0, 0);
        $this->mpdf->Cell($pw / 3, 5, $this->companyName, 0, 0, 'L');

        $this->mpdf->SetFont('dejavusans', 'B', 12);
        $this->mpdf->Cell($pw / 3, 5, $this->titre, 0, 0, 'C');

        $this->mpdf->SetFont('dejavusans', '', 8);
        $this->mpdf->Cell($pw / 3, 5, 'Période du ' . $this->fmtDate($this->dateDebut), 0, 1, 'R');

        // ── Ligne 2 : « Impression définitive » (orange italic) | plage | fin période
        $this->mpdf->SetXY($ml, $y + 5);
        // Orange italic — identique au style balance
        $this->mpdf->SetFont('dejavusans', 'I', 7.5);
        $this->mpdf->SetTextColor(...self::ORANGE);
        $this->mpdf->Cell($pw / 3, 4, 'Impression définitive', 0, 0, 'L');
        // Reset couleur
        $this->mpdf->SetTextColor(0, 0, 0);
        $this->mpdf->SetFont('dejavusans', '', 7.5);
        $this->mpdf->Cell($pw / 3, 4, 'Comptes : ' . $this->compteMin . ' → ' . $this->compteMax, 0, 0, 'C');
        $this->mpdf->Cell($pw / 3, 4, 'au ' . $this->fmtDate($this->dateFin), 0, 1, 'R');

        // ── Séparateur 1 ─────────────────────────────────────────────────
        $this->mpdf->SetDrawColor(150, 150, 150);
        $this->mpdf->Line($ml, $y + 10, $ml + $pw, $y + 10);

        // ── Ligne 3 : © ComptaFlow | Date de tirage | Page X ─────────────
        $this->mpdf->SetXY($ml, $y + 11);
        $this->mpdf->SetFont('dejavusans', '', 7);
        $this->mpdf->SetTextColor(0, 0, 0);
        $this->mpdf->Cell($pw / 3, 4, '© ComptaFlow - Logiciel de comptabilité', 0, 0, 'L');
        $dateStr = 'Date de tirage : ' . now()->format('d/m/Y') . ' à ' . now()->format('H:i:s');
        $this->mpdf->Cell($pw / 3, 4, $dateStr, 0, 0, 'C');
        $this->mpdf->Cell($pw / 3, 4, 'Page : ' . $this->pageNum, 0, 1, 'R');

        // ── Séparateur 2 ─────────────────────────────────────────────────
        $this->mpdf->Line($ml, $y + 16, $ml + $pw, $y + 16);

        $this->Y = $y + 18;
        $this->mpdf->SetY($this->Y);
    }

    // ─────────────────────────────────────────────────────────────────────
    //  PIED DE PAGE
    // ─────────────────────────────────────────────────────────────────────
    private function renderPageFooter(): void
    {
        $footY = 210 - self::MB - 6;
        $this->mpdf->SetFont('dejavusans', 'I', 7);
        $this->mpdf->SetFillColor(...self::GREY_L);
        $this->mpdf->SetXY(self::ML, $footY);
        $labelW = $this->pw - self::C['debit'] - self::C['credit'];
        $this->mpdf->Cell($labelW,         self::RH, 'À REPORTER', 1, 0, 'R', true);
        $this->mpdf->SetFont('dejavusans', 'B', 7);
        $this->mpdf->Cell(self::C['debit'],  self::RH, $this->fmt($this->runD), 1, 0, 'R', true);
        $this->mpdf->Cell(self::C['credit'], self::RH, $this->fmt($this->runC), 1, 1, 'R', true);
    }

    private function renderReportLine(): void
    {
        $this->mpdf->SetFont('dejavusans', 'I', 7);
        $this->mpdf->SetFillColor(...self::GREY_L);
        $this->mpdf->SetXY(self::ML, $this->Y);
        $labelW = $this->pw - self::C['debit'] - self::C['credit'];
        $this->mpdf->Cell($labelW,         self::RH, 'REPORT', 1, 0, 'R', true);
        $this->mpdf->SetFont('dejavusans', 'B', 7);
        $this->mpdf->Cell(self::C['debit'],  self::RH, $this->fmt($this->runD), 1, 0, 'R', true);
        $this->mpdf->Cell(self::C['credit'], self::RH, $this->fmt($this->runC), 1, 1, 'R', true);
        $this->Y += self::RH + 1;
        $this->mpdf->SetY($this->Y);
    }

    // ═════════════════════════════════════════════════════════════════════
    //  RENDU D'UN COMPTE
    // ═════════════════════════════════════════════════════════════════════

    private function renderAccount(
        $groupId, $ops, array $soldesInitiaux, bool $isTiers,
        string $displayMode, float &$gtD, float &$gtC
    ): void {
        // ── Infos compte ──────────────────────────────────────────────────
        $first = $ops->first();
        if (!$isTiers) {
            $model   = $first->planComptable;
            $numero  = $model?->numero_de_compte ?? '-';
            $numOrig = $model?->numero_original  ?? '';
            $label   = $model?->intitule          ?? 'Intitulé inconnu';
        } else {
            $model   = $first->planTiers;
            $numero  = $model?->numero_de_tiers   ?? '-';
            $numOrig = $model?->numero_original   ?? '';
            $label   = $model?->intitule           ?? 'Intitulé inconnu';
        }
        $numAff = $this->resolveNum($numero, $numOrig, $displayMode);

        // Solde initial
        $si  = $soldesInitiaux[$groupId] ?? ['debit' => 0, 'credit' => 0, 'solde' => 0];
        $sol = (float) $si['solde'];
        $aD  = (float) $si['debit'];
        $aC  = (float) $si['credit'];
        $this->runD += $aD;
        $this->runC += $aC;

        // ── Vérification espace (header + colonnes + 1 ligne) ─────────────
        $this->need(3 * self::RH + 4);

        // ── En-tête compte (gris clair + texte noir gras, style balance) ────
        $this->mpdf->SetXY(self::ML, $this->Y);
        $this->mpdf->SetFillColor(...self::GREY_H);
        $this->mpdf->SetTextColor(0, 0, 0);
        $this->mpdf->SetFont('dejavusans', 'B', 8);
        $this->mpdf->Cell($this->pw, 6,
            '  ' . $numAff . '  —  ' . mb_strtoupper($label),
            1, 1, 'L', true);
        $this->Y += 7;

        // ── En-tête colonnes ──────────────────────────────────────────────
        $this->renderColumnHeader();

        // ── Solde initial ─────────────────────────────────────────────────
        if ($si['debit'] != 0 || $si['credit'] != 0) {
            $this->renderInitialBalance($si['debit'], $si['credit'], $si['solde']);
        }

        // ── Écritures ─────────────────────────────────────────────────────
        $byEntry = $ops->groupBy('n_saisie');
        foreach ($byEntry as $nSaisie => $lines) {
            $eD = 0.0;
            $eC = 0.0;
            foreach ($lines as $e) {
                $d   = (float)($e->debit  ?? 0);
                $c   = (float)($e->credit ?? 0);
                $sol += $d - $c;
                $eD  += $d; $eC  += $c;
                $aD  += $d; $aC  += $c;
                $this->runD += $d;
                $this->runC += $c;
                $this->need(self::RH);
                $this->renderEntry($e, $displayMode, $sol);
            }
            $this->need(self::RH);
            $this->renderSubtotal($nSaisie, $eD, $eC, $sol);
        }

        // ── Total compte ──────────────────────────────────────────────────
        $this->need(self::RH + 2);
        $this->renderAccountTotal($numAff, $aD, $aC, $sol);

        $gtD += $aD;
        $gtC += $aC;
    }

    // ─────────────────────────────────────────────────────────────────────
    //  LIGNES DE CONTENU
    // ─────────────────────────────────────────────────────────────────────

    private function renderColumnHeader(): void
    {
        $c = self::C;
        // Identique au style balance : fond bleu marine foncé + texte blanc
        $this->mpdf->SetFont('dejavusans', 'B', 7);
        $this->mpdf->SetFillColor(...self::NAVY);
        $this->mpdf->SetTextColor(255, 255, 255);
        $this->mpdf->SetDrawColor(...self::NAVY);
        $this->mpdf->SetXY(self::ML, $this->Y);
        $this->mpdf->Cell($c['date'],    self::RH, 'Date',      1, 0, 'C', true);
        $this->mpdf->Cell($c['journal'], self::RH, 'Jnl',       1, 0, 'C', true);
        $this->mpdf->Cell($c['saisie'],  self::RH, 'N° Saisie', 1, 0, 'C', true);
        $this->mpdf->Cell($c['piece'],   self::RH, 'Pièce',     1, 0, 'C', true);
        $this->mpdf->Cell($c['compte'],  self::RH, 'Compte',    1, 0, 'C', true);
        $this->mpdf->Cell($c['tiers'],   self::RH, 'Tiers',     1, 0, 'C', true);
        $this->mpdf->Cell($c['libelle'], self::RH, 'Libellé',   1, 0, 'C', true);
        $this->mpdf->Cell($c['lettr'],   self::RH, 'Ltr',       1, 0, 'C', true);
        $this->mpdf->Cell($c['debit'],   self::RH, 'Débit',     1, 0, 'R', true);
        $this->mpdf->Cell($c['credit'],  self::RH, 'Crédit',    1, 0, 'R', true);
        $this->mpdf->Cell($c['solde'],   self::RH, 'Solde',     1, 1, 'R', true);
        // Reset couleur texte
        $this->mpdf->SetTextColor(0, 0, 0);
        $this->mpdf->SetDrawColor(160, 160, 160);
        $this->Y += self::RH;
        $this->mpdf->SetY($this->Y);
    }

    private function renderInitialBalance(float $d, float $c, float $sol): void
    {
        $cols = self::C;
        $labelW = $cols['date'] + $cols['journal'] + $cols['saisie'] + $cols['piece']
                + $cols['compte'] + $cols['tiers'] + $cols['libelle'] + $cols['lettr'];
        $this->mpdf->SetFont('dejavusans', 'I', 7);
        $this->mpdf->SetFillColor(...self::YELLOW);
        $this->mpdf->SetXY(self::ML, $this->Y);
        $this->mpdf->Cell($labelW,         self::RH, 'SOLDE INITIAL', 1, 0, 'C', true);
        $this->mpdf->SetFont('dejavusans', 'B', 7);
        $this->mpdf->Cell($cols['debit'],  self::RH, $d != 0 ? $this->fmt($d) : '', 1, 0, 'R', true);
        $this->mpdf->Cell($cols['credit'], self::RH, $c != 0 ? $this->fmt($c) : '', 1, 0, 'R', true);
        $this->mpdf->Cell($cols['solde'],  self::RH, $this->fmtSolde($sol),          1, 1, 'R', true);
        $this->Y += self::RH;
        $this->mpdf->SetY($this->Y);
    }

    private function renderEntry($e, string $dm, float $sol): void
    {
        $c   = self::C;
        $jl  = $this->disp($e->codeJournal?->code_journal ?? '-',   $e->codeJournal?->numero_original ?? '', $dm);
        $ns  = $this->disp($e->n_saisie ?? '-',                     $e->n_saisie_user ?? '',                  $dm);
        $cpt = $this->disp($e->planComptable?->numero_de_compte ?? '',$e->planComptable?->numero_original ?? '',$dm);
        $ti  = $this->disp($e->planTiers?->numero_de_tiers ?? '',   $e->planTiers?->numero_original ?? '',    $dm);
        $d   = (float)($e->debit  ?? 0);
        $cv  = (float)($e->credit ?? 0);

        $this->mpdf->SetFont('dejavusans', '', 7);
        $this->mpdf->SetFillColor(...self::WHITE);
        $this->mpdf->SetXY(self::ML, $this->Y);
        $this->mpdf->Cell($c['date'],    self::RH, $this->fmtDate($e->date ?? ''),               1, 0, 'C');
        $this->mpdf->Cell($c['journal'], self::RH, mb_substr($jl,  0, 6),                        1, 0, 'C');
        $this->mpdf->Cell($c['saisie'],  self::RH, mb_substr($ns,  0, 16),                       1, 0, 'C');
        $this->mpdf->Cell($c['piece'],   self::RH, mb_substr($e->reference_piece ?? '-', 0, 14), 1, 0, 'C');
        $this->mpdf->Cell($c['compte'],  self::RH, mb_substr($cpt, 0, 10),                       1, 0, 'C');
        $this->mpdf->Cell($c['tiers'],   self::RH, mb_substr($ti,  0, 10),                       1, 0, 'C');
        $this->mpdf->Cell($c['libelle'], self::RH, mb_substr($e->description_operation ?? '', 0, 54), 1, 0, 'L');
        $this->mpdf->Cell($c['lettr'],   self::RH, mb_substr($e->lettrage ?? '', 0, 4),          1, 0, 'C');
        $this->mpdf->Cell($c['debit'],   self::RH, $d  > 0 ? $this->fmt($d)  : '',              1, 0, 'R');
        $this->mpdf->Cell($c['credit'],  self::RH, $cv > 0 ? $this->fmt($cv) : '',              1, 0, 'R');
        $this->mpdf->Cell($c['solde'],   self::RH, $this->fmtSolde($sol),                        1, 1, 'R');
        $this->Y += self::RH;
        $this->mpdf->SetY($this->Y);
    }

    private function renderSubtotal(string $nSaisie, float $d, float $c, float $sol): void
    {
        $cols   = self::C;
        $labelW = $cols['date'] + $cols['journal'] + $cols['saisie'] + $cols['piece']
                + $cols['compte'] + $cols['tiers'] + $cols['libelle'] + $cols['lettr'];
        $this->mpdf->SetFont('dejavusans', 'B', 7);
        $this->mpdf->SetFillColor(...self::GREY_L);
        $this->mpdf->SetXY(self::ML, $this->Y);
        $this->mpdf->Cell($labelW,         self::RH, 'Sous-total saisie ' . $nSaisie, 1, 0, 'R', true);
        $this->mpdf->Cell($cols['debit'],  self::RH, $this->fmt($d),         1, 0, 'R', true);
        $this->mpdf->Cell($cols['credit'], self::RH, $this->fmt($c),         1, 0, 'R', true);
        $this->mpdf->Cell($cols['solde'],  self::RH, $this->fmtSolde($sol),  1, 1, 'R', true);
        $this->Y += self::RH;
        $this->mpdf->SetY($this->Y);
    }

    private function renderAccountTotal(string $num, float $d, float $c, float $sol): void
    {
        $cols   = self::C;
        $labelW = $cols['date'] + $cols['journal'] + $cols['saisie'] + $cols['piece']
                + $cols['compte'] + $cols['tiers'] + $cols['libelle'] + $cols['lettr'];
        $this->mpdf->SetFont('dejavusans', 'B', 7.5);
        $this->mpdf->SetFillColor(...self::BLUE_M);
        $this->mpdf->SetXY(self::ML, $this->Y);
        $this->mpdf->Cell($labelW,         self::RH + 1, 'TOTAL  ' . $num, 1, 0, 'R', true);
        $this->mpdf->Cell($cols['debit'],  self::RH + 1, $this->fmt($d),        1, 0, 'R', true);
        $this->mpdf->Cell($cols['credit'], self::RH + 1, $this->fmt($c),        1, 0, 'R', true);
        $this->mpdf->Cell($cols['solde'],  self::RH + 1, $this->fmtSolde($sol), 1, 1, 'R', true);
        $this->Y += self::RH + 4;
        $this->mpdf->SetY($this->Y);
    }

    private function renderGrandTotal(float $d, float $c): void
    {
        $cols   = self::C;
        $labelW = $cols['date'] + $cols['journal'] + $cols['saisie'] + $cols['piece']
                + $cols['compte'] + $cols['tiers'] + $cols['libelle'] + $cols['lettr'];
        $this->mpdf->SetFont('dejavusans', 'B', 8.5);
        $this->mpdf->SetFillColor(...self::NAVY);
        $this->mpdf->SetTextColor(255, 255, 255);
        $this->mpdf->SetXY(self::ML, $this->Y);
        $this->mpdf->Cell($labelW,        7, 'TOTAL GÉNÉRAL', 1, 0, 'R', true);
        $this->mpdf->Cell($cols['debit'], 7, $this->fmt($d),         1, 0, 'R', true);
        $this->mpdf->Cell($cols['credit'],7, $this->fmt($c),         1, 0, 'R', true);
        $this->mpdf->Cell($cols['solde'], 7, $this->fmtSolde($d-$c), 1, 1, 'R', true);
        $this->mpdf->SetTextColor(0, 0, 0);
    }

    // ═════════════════════════════════════════════════════════════════════
    //  UTILITAIRES
    // ═════════════════════════════════════════════════════════════════════

    /** Formate un montant (toujours positif, séparateur espace) */
    private function fmt(float $v): string
    {
        return number_format(abs($v), 2, ',', ' ');
    }

    /**
     * Solde progressif : − devant si négatif, sans parenthèses.
     * Ex : -5 000 000,00   ou   125 936 224,00
     */
    private function fmtSolde(float $v): string
    {
        $abs = number_format(abs($v), 2, ',', ' ');
        return $v < 0 ? '-' . $abs : $abs;
    }

    private function fmtDate(string $date): string
    {
        try { return \Carbon\Carbon::parse($date)->format('d/m/Y'); }
        catch (\Throwable) { return $date; }
    }

    private function resolveNum(string $num, string $orig, string $dm): string
    {
        return match ($dm) {
            'origine' => !empty($orig) ? $orig : $num,
            'both'    => !empty($orig) && $orig !== $num ? $num . ' (' . $orig . ')' : $num,
            default   => $num,
        };
    }

    private function disp(string $sys, string $orig, string $dm): string
    {
        return match ($dm) {
            'origine' => !empty($orig) ? $orig : $sys,
            'both'    => !empty($orig) && $orig !== $sys ? $sys . '(' . $orig . ')' : $sys,
            default   => $sys,
        };
    }
}
