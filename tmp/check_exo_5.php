<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$exo = App\Models\ExerciceComptable::find(5);
if ($exo) {
    echo "Exercice ID: 5 | Company ID: " . $exo->company_id . " | Start: " . $exo->debut . " | End: " . $exo->fin . "\n";
} else {
    echo "Exercice ID 5 not found.\n";
}

$planTiers = App\Models\PlanTiers::where('company_id', 1)->where('intitule', 'LIKE', '%AMA%')->get();
echo "Search result for AMA in Company 1: " . $planTiers->count() . "\n";
foreach ($planTiers as $pt) {
    echo "- " . $pt->intitule . " (" . $pt->numero_de_tiers . ")\n";
}
