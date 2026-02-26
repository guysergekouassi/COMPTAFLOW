<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$accounts = \App\Models\PlanComptable::where('numero_de_compte', '140100')->get();
if ($accounts->isEmpty()) {
    echo "No account 140100 found in DB!\n";
} else {
    foreach ($accounts as $a) {
        echo "Found: ID {$a->id}, Num: {$a->numero_de_compte}, Company: {$a->company_id}\n";
    }
}
