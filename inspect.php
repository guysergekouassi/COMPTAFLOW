<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$imports = App\Models\ImportStaging::all();
foreach ($imports as $import) {
    if (!$import->raw_data) continue;
    foreach ($import->raw_data as $idx => $r) {
        $row_str = implode(' | ', $r);
        if (strpos($row_str, '343') !== false || strpos($row_str, '437') !== false) {
            echo "Import ID: " . $import->id . " | Row $idx: " . $row_str . "\n";
        }
    }
}
