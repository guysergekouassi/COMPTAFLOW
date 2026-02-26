<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
$staging = \App\Models\ImportStaging::find(47);
if ($staging) {
    echo "ID: " . $staging->id . "\n";
    echo "Type: " . $staging->type . "\n";
    echo "Status: " . $staging->status . "\n";
    echo "Mapping: " . json_encode($staging->mapping, JSON_PRETTY_PRINT) . "\n";
    echo "Raw Data Sample (first 2 rows):\n";
    echo json_encode(array_slice($staging->raw_data, 0, 2), JSON_PRETTY_PRINT) . "\n";
} else {
    echo "ImportStaging 47 not found";
}
