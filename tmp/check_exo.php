<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
$company = \App\Models\Company::where('company_name', 'ENTREPRISE TEST')->first();
if ($company) {
    echo "Company ID: " . $company->id . "\n";
    $exercices = \App\Models\ExerciceComptable::where('company_id', $company->id)->get();
    foreach ($exercices as $exo) {
        echo "Exo: " . $exo->intitule . " | Start: " . $exo->date_debut . " | End: " . $exo->date_fin . " | Active: " . ($exo->is_active ? 'YES' : 'NO') . "\n";
    }
} else {
    echo "Company 'ENTREPRISE TEST' not found\n";
}
