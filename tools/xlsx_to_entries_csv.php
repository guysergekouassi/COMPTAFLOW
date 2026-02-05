<?php

require __DIR__ . '/../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

$in = $argv[1] ?? null;
$out = $argv[2] ?? null;

if (!$in || !$out) {
    fwrite(STDERR, "Usage: php tools/xlsx_to_entries_csv.php <in.xlsx> <out.csv>\n");
    exit(1);
}

$inPath = $in;
if (!is_file($inPath)) {
    $inPath = __DIR__ . '/../' . ltrim($in, '/\\');
}

if (!is_file($inPath)) {
    fwrite(STDERR, "File not found: {$in}\n");
    exit(1);
}

$spreadsheet = IOFactory::load($inPath);
$sheet = $spreadsheet->getActiveSheet();
$rows = $sheet->toArray(null, true, true, true);

$fp = fopen($out, 'wb');
if ($fp === false) {
    fwrite(STDERR, "Cannot write to: {$out}\n");
    exit(1);
}

// Expected mapping for 18.xlsx based on preview:
// A=type_ecriture, B=journal, C=reference_piece, D=compte, E=tiers(optional), F=libelle, G=debit, H=credit, I=date_ecriture
$headers = ['type_ecriture', 'journal', 'reference_piece', 'compte', 'tiers', 'libelle', 'debit', 'credit', 'date_ecriture'];
fputcsv($fp, $headers, ';');

foreach ($rows as $idx => $r) {
    // Skip empty
    if (empty(array_filter($r, fn($v) => $v !== null && trim((string)$v) !== ''))) {
        continue;
    }

    $type = trim((string)($r['A'] ?? ''));
    $journal = trim((string)($r['B'] ?? ''));
    $ref = trim((string)($r['C'] ?? ''));
    $compte = trim((string)($r['D'] ?? ''));
    $tiers = trim((string)($r['E'] ?? ''));
    $libelle = trim((string)($r['F'] ?? ''));
    $debit = trim((string)($r['G'] ?? ''));
    $credit = trim((string)($r['H'] ?? ''));
    $date = trim((string)($r['I'] ?? ''));

    // Basic normalization
    $debit = str_replace(',', '.', $debit);
    $credit = str_replace(',', '.', $credit);

    fputcsv($fp, [$type, $journal, $ref, $compte, $tiers, $libelle, $debit, $credit, $date], ';');
}

fclose($fp);

echo "OK: {$out}\n";
