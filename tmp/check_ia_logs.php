<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$logs = App\Models\IaLog::where('status', 'error')->whereNotNull('json_brut')->orderBy('id', 'desc')->take(5)->get();

foreach($logs as $log) {
    echo "--- ID: " . $log->id . " ---\n";
    echo "Message: " . $log->erreur_message . "\n";
    echo "Length: " . strlen($log->json_brut) . "\n";
    echo "Content Start: " . substr($log->json_brut, 0, 100) . "...\n";
    echo "Content End: ..." . substr($log->json_brut, -100) . "\n";
    echo "\n";
}
