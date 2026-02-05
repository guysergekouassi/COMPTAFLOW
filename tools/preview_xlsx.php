<?php

require __DIR__ . '/../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

$file = $argv[1] ?? null;
if (!$file) {
    fwrite(STDERR, "Usage: php tools/preview_xlsx.php <file.xlsx>\n");
    exit(1);
}

$path = $file;
if (!is_file($path)) {
    $path = __DIR__ . '/../' . ltrim($file, '/\\');
}

if (!is_file($path)) {
    fwrite(STDERR, "File not found: {$file}\n");
    exit(1);
}

$spreadsheet = IOFactory::load($path);
$sheet = $spreadsheet->getActiveSheet();
$rows = $sheet->toArray(null, true, true, true);

$max = min(12, count($rows));
for ($i = 1; $i <= $max; $i++) {
    $row = $rows[$i] ?? [];
    echo $i . "\t" . json_encode($row, JSON_UNESCAPED_UNICODE) . PHP_EOL;
}
