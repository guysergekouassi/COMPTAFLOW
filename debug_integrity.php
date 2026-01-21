<?php
include 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$companyId = 33;
$accounts = App\Models\PlanComptable::withoutGlobalScopes()
    ->where('company_id', $companyId)
    ->get();

$badFormat = [];
$total = 0;
foreach($accounts as $acc) {
    if(strlen($acc->numero_de_compte) !== strlen(trim($acc->numero_de_compte))) {
        $badFormat[] = "'".$acc->numero_de_compte."'";
    }
    $total++;
}

echo "Total analyses : $total\n";
echo "Comptes avec espaces de tête/queue : " . count($badFormat) . "\n";
print_r(array_slice($badFormat, 0, 10));

$classes = [];
foreach($accounts as $acc) {
    $c = substr(trim($acc->numero_de_compte), 0, 1);
    $classes[$c] = ($classes[$c] ?? 0) + 1;
}
echo "\nStats par classe (trim) :\n";
ksort($classes);
print_r($classes);

// Chercher des doublons exacts
$duplicates = App\Models\PlanComptable::withoutGlobalScopes()
    ->where('company_id', $companyId)
    ->selectRaw('numero_de_compte, count(*) as count')
    ->groupBy('numero_de_compte')
    ->having('count', '>', 1)
    ->get()
    ->toArray();

echo "\nDoublons trouvés : " . count($duplicates) . "\n";
print_r(array_slice($duplicates, 0, 10));
