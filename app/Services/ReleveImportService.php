<?php

namespace App\Services;

use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Http\UploadedFile;

/**
 * ReleveImportService
 *
 * Parse un relevé bancaire depuis :
 *   - CSV (délimiteur point-virgule)
 *   - Excel (.xlsx / .xls)
 *
 * Retourne un tableau de lignes structurées :
 * [
 *   ['date_operation' => '2025-01-15', 'libelle' => '...', 'debit' => 0, 'credit' => 150000, 'solde' => null, 'reference' => ''],
 *   ...
 * ]
 */
class ReleveImportService
{
    // Colonnes possibles dans le relevé (détection automatique)
    const COL_DATE    = ['date', 'date operation', 'date_operation', 'date op.', 'date valeur', 'dat'];
    const COL_LIBELLE = ['libelle', 'libellé', 'description', 'motif', 'intitulé', 'details', 'détails', 'operation'];
    const COL_DEBIT   = ['debit', 'débit', 'retrait', 'sortie', 'montant débit', 'montant debit'];
    const COL_CREDIT  = ['credit', 'crédit', 'versement', 'entree', 'entrée', 'montant crédit', 'montant credit'];
    const COL_SOLDE   = ['solde', 'balance', 'solde progressif', 'cumul'];
    const COL_REF     = ['reference', 'réference', 'ref', 'n° op', 'numero', 'numéro', 'n°'];

    /**
     * Point d'entrée principal.
     *
     * @param  UploadedFile $file
     * @return array  ['lignes' => [...], 'colonnes_detectees' => [...], 'erreurs' => [...]]
     */
    public function parse(UploadedFile $file): array
    {
        $ext = strtolower($file->getClientOriginalExtension());

        return match ($ext) {
            'csv'        => $this->parseCsv($file->getPathname()),
            'xlsx', 'xls', 'ods' => $this->parseExcel($file->getPathname()),
            default      => ['lignes' => [], 'colonnes_detectees' => [], 'erreurs' => ["Format non supporté : .$ext"]],
        };
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  CSV
    // ─────────────────────────────────────────────────────────────────────────

    private function parseCsv(string $path): array
    {
        $rows   = [];
        $handle = fopen($path, 'r');
        if (!$handle) {
            return ['lignes' => [], 'colonnes_detectees' => [], 'erreurs' => ['Impossible de lire le fichier CSV.']];
        }

        // Détection de l'encodage (UTF-8 ou Windows-1252)
        $raw = file_get_contents($path);
        if (!mb_check_encoding($raw, 'UTF-8')) {
            $raw = mb_convert_encoding($raw, 'UTF-8', 'Windows-1252');
            file_put_contents($path, $raw);
        }

        while (($row = fgetcsv($handle, 2000, ';')) !== false) {
            $rows[] = $row;
        }
        fclose($handle);

        return $this->processRows($rows);
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  EXCEL
    // ─────────────────────────────────────────────────────────────────────────

    private function parseExcel(string $path): array
    {
        try {
            $spreadsheet = IOFactory::load($path);
            $sheet       = $spreadsheet->getActiveSheet();
            $rows        = [];

            foreach ($sheet->getRowIterator() as $row) {
                $cellIterator = $row->getCellIterator();
                $cellIterator->setIterateOnlyExistingCells(false);
                $rowData = [];
                foreach ($cellIterator as $cell) {
                    // Formatage de la date Excel si c'est un numérique de date
                    $value = $cell->getValue();
                    if (\PhpOffice\PhpSpreadsheet\Shared\Date::isDateTime($cell) && is_numeric($value)) {
                        $value = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value)
                                    ->format('d/m/Y');
                    }
                    $rowData[] = is_null($value) ? '' : (string) $value;
                }
                // Ignorer les lignes entièrement vides
                if (array_filter($rowData, fn($v) => trim($v) !== '') !== []) {
                    $rows[] = $rowData;
                }
            }

            return $this->processRows($rows);
        } catch (\Throwable $e) {
            return ['lignes' => [], 'colonnes_detectees' => [], 'erreurs' => ['Erreur lecture Excel : ' . $e->getMessage()]];
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  TRAITEMENT COMMUN
    // ─────────────────────────────────────────────────────────────────────────

    private function processRows(array $rows): array
    {
        if (count($rows) < 2) {
            return ['lignes' => [], 'colonnes_detectees' => [], 'erreurs' => ['Fichier vide ou sans données.']];
        }

        // Trouver la ligne d'en-tête (première ligne non vide)
        $headerRow  = null;
        $dataStart  = 0;
        foreach ($rows as $i => $row) {
            $filled = array_filter($row, fn($v) => trim($v) !== '');
            if (count($filled) >= 2) {
                $headerRow = array_map(fn($v) => mb_strtolower(trim($v)), $row);
                $dataStart = $i + 1;
                break;
            }
        }

        if (!$headerRow) {
            return ['lignes' => [], 'colonnes_detectees' => [], 'erreurs' => ['En-tête de colonnes introuvable.']];
        }

        // Mapper les colonnes
        $map = $this->detectColumns($headerRow);

        if (!isset($map['date']) || (!isset($map['debit']) && !isset($map['credit']))) {
            return [
                'lignes' => [],
                'colonnes_detectees' => $map,
                'erreurs' => ['Colonnes requises introuvables. Colonnes trouvées : ' . implode(', ', $headerRow)],
            ];
        }

        // Parser les lignes de données
        $lignes  = [];
        $erreurs = [];
        $ordre   = 0;

        for ($i = $dataStart; $i < count($rows); $i++) {
            $row = $rows[$i];
            // Ignorer les lignes sans montant
            $debit  = $this->parseMontant($row[$map['debit']]  ?? '');
            $credit = $this->parseMontant($row[$map['credit']] ?? '');

            if ($debit == 0 && $credit == 0) {
                continue; // Ligne vide ou sous-total sans montant
            }

            $dateRaw = trim($row[$map['date']] ?? '');
            $date    = $this->parseDate($dateRaw);

            if (!$date) {
                $erreurs[] = "Ligne " . ($i + 1) . " : date invalide « $dateRaw »";
                continue;
            }

            $lignes[] = [
                'date_operation' => $date,
                'date_valeur'    => isset($map['date_valeur']) ? ($this->parseDate(trim($row[$map['date_valeur']] ?? '')) ?? $date) : $date,
                'libelle'        => trim($row[$map['libelle']] ?? ''),
                'reference'      => isset($map['reference']) ? trim($row[$map['reference']] ?? '') : '',
                'debit'          => $debit,
                'credit'         => $credit,
                'solde'          => isset($map['solde']) ? $this->parseMontant($row[$map['solde']] ?? '') : null,
                'ordre'          => $ordre++,
            ];
        }

        return [
            'lignes'              => $lignes,
            'colonnes_detectees'  => $map,
            'erreurs'             => $erreurs,
        ];
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  DÉTECTION AUTOMATIQUE DES COLONNES
    // ─────────────────────────────────────────────────────────────────────────

    private function detectColumns(array $header): array
    {
        $map = [];
        foreach ($header as $idx => $col) {
            $col = mb_strtolower(trim($col));
            if (!isset($map['date'])       && in_array($col, self::COL_DATE))    $map['date']       = $idx;
            if (!isset($map['libelle'])    && in_array($col, self::COL_LIBELLE)) $map['libelle']    = $idx;
            if (!isset($map['debit'])      && in_array($col, self::COL_DEBIT))   $map['debit']      = $idx;
            if (!isset($map['credit'])     && in_array($col, self::COL_CREDIT))  $map['credit']     = $idx;
            if (!isset($map['solde'])      && in_array($col, self::COL_SOLDE))   $map['solde']      = $idx;
            if (!isset($map['reference'])  && in_array($col, self::COL_REF))     $map['reference']  = $idx;
        }
        return $map;
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  HELPERS
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Convertit une chaîne montant en float (gère "1 250 000,50" et "1250000.50")
     */
    private function parseMontant(string $val): float
    {
        if (trim($val) === '' || trim($val) === '-') return 0.0;
        // Supprimer espaces, retirer le séparateur milliers, remplacer virgule par point
        $val = preg_replace('/\s+/', '', $val);
        $val = str_replace([' ', "\u{00A0}"], '', $val); // espace insécable
        // Format français : 1.250.000,50 → virer les points milliers, virgule → point
        if (strpos($val, ',') !== false) {
            $val = str_replace('.', '', $val);
            $val = str_replace(',', '.', $val);
        }
        return abs((float) $val);
    }

    /**
     * Parse une date depuis différents formats : d/m/Y, Y-m-d, d-m-Y, d.m.Y
     */
    private function parseDate(string $val): ?string
    {
        $val = trim($val);
        if ($val === '') return null;

        $formats = ['d/m/Y', 'Y-m-d', 'd-m-Y', 'd.m.Y', 'm/d/Y', 'd/m/y'];
        foreach ($formats as $fmt) {
            $dt = \DateTime::createFromFormat($fmt, $val);
            if ($dt && $dt->format($fmt) === $val) {
                return $dt->format('Y-m-d');
            }
        }
        // Tentative via strtotime
        $ts = strtotime($val);
        return $ts ? date('Y-m-d', $ts) : null;
    }
}
