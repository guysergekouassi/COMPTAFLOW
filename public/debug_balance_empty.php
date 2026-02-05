<?php
require __DIR__.'/../vendor/autoload.php';

$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\EcritureComptable;
use App\Models\ExerciceComptable;
use App\Models\PlanComptable;

echo "=== DEBUG BALANCE PREVIEW ===\n\n";

// Vérifier la session
session_start();
echo "Session ID: " . session_id() . "\n";
echo "Current Exercice ID (Session): " . ($_SESSION['current_exercice_id'] ?? 'NOT SET') . "\n";
echo "Current Company ID (Session): " . ($_SESSION['current_company_id'] ?? 'NOT SET') . "\n\n";

// Récupérer l'exercice actif
$companyId = $_SESSION['current_company_id'] ?? 1;
$exerciceId = $_SESSION['current_exercice_id'] ?? null;

if (!$exerciceId) {
    $exercice = ExerciceComptable::where('company_id', $companyId)
        ->where('is_active', 1)
        ->first();
    $exerciceId = $exercice ? $exercice->id : null;
}

echo "Using Exercice ID: " . ($exerciceId ?? 'NULL') . "\n";

if ($exerciceId) {
    $exercice = ExerciceComptable::find($exerciceId);
    echo "Exercice: " . $exercice->intitule . " (" . $exercice->date_debut . " - " . $exercice->date_fin . ")\n\n";
}

// Compter les écritures
echo "=== ECRITURES COUNT ===\n";
$totalEcritures = EcritureComptable::where('company_id', $companyId)->count();
echo "Total écritures (company): $totalEcritures\n";

if ($exerciceId) {
    $ecrituresExercice = EcritureComptable::where('company_id', $companyId)
        ->where('exercices_comptables_id', $exerciceId)
        ->count();
    echo "Écritures pour exercice $exerciceId: $ecrituresExercice\n";
}

// Vérifier les comptes
echo "\n=== PLAN COMPTABLE ===\n";
$comptes = PlanComptable::withoutGlobalScopes()
    ->where('company_id', $companyId)
    ->orderBy('numero_de_compte')
    ->limit(10)
    ->get(['id', 'numero_de_compte', 'intitule']);

foreach ($comptes as $compte) {
    echo "- {$compte->numero_de_compte} : {$compte->intitule} (ID: {$compte->id})\n";
}

// Tester une requête de balance
echo "\n=== TEST BALANCE QUERY ===\n";
$compte1 = PlanComptable::withoutGlobalScopes()
    ->where('company_id', $companyId)
    ->orderBy('numero_de_compte')
    ->first();

$compte2 = PlanComptable::withoutGlobalScopes()
    ->where('company_id', $companyId)
    ->orderBy('numero_de_compte', 'desc')
    ->first();

if ($compte1 && $compte2) {
    echo "Compte 1: {$compte1->numero_de_compte}\n";
    echo "Compte 2: {$compte2->numero_de_compte}\n";
    
    $min = $compte1->numero_de_compte < $compte2->numero_de_compte ? $compte1->numero_de_compte : $compte2->numero_de_compte;
    $max = $compte1->numero_de_compte > $compte2->numero_de_compte ? $compte1->numero_de_compte : $compte2->numero_de_compte;
    
    $comptesIds = PlanComptable::withoutGlobalScopes()
        ->where('company_id', $companyId)
        ->where('numero_de_compte', '>=', $min)
        ->where('numero_de_compte', '<=', $max)
        ->pluck('id');
    
    echo "Comptes IDs count: " . $comptesIds->count() . "\n";
    
    // Sans filtre exercice
    $ecrituresSansFiltre = EcritureComptable::where('company_id', $companyId)
        ->whereIn('plan_comptable_id', $comptesIds)
        ->count();
    echo "Écritures (sans filtre exercice): $ecrituresSansFiltre\n";
    
    // Avec filtre exercice
    if ($exerciceId) {
        $ecrituresAvecFiltre = EcritureComptable::where('company_id', $companyId)
            ->whereIn('plan_comptable_id', $comptesIds)
            ->where('exercices_comptables_id', $exerciceId)
            ->count();
        echo "Écritures (avec filtre exercice $exerciceId): $ecrituresAvecFiltre\n";
    }
}

// Vérifier les exercices disponibles
echo "\n=== EXERCICES DISPONIBLES ===\n";
$exercices = ExerciceComptable::where('company_id', $companyId)->get();
foreach ($exercices as $ex) {
    $count = EcritureComptable::where('company_id', $companyId)
        ->where('exercices_comptables_id', $ex->id)
        ->count();
    echo "- {$ex->intitule} (ID: {$ex->id}) : $count écritures\n";
}
