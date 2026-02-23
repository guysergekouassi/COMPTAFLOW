<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    Illuminate\Http\Request::capture()
);

use App\Models\IaLog;

$logs = IaLog::orderBy('created_at', 'desc')->limit(5)->get();

ob_start();
foreach ($logs as $log) {
    echo "ID: " . $log->id . " | Time: " . $log->created_at . "\n";
    echo "Status: " . $log->status . "\n";
    echo "Erreur: " . $log->erreur_message . "\n";
    echo "JSON Brut:\n" . $log->json_brut . "\n";
    echo "-------------------\n";
}
file_put_contents('ia_debug.txt', ob_get_clean());
echo "Logs écrits dans ia_debug.txt\n";
