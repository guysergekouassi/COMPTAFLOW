<?php
// Script de diagnostic pour la balance
require __DIR__.'/../vendor/autoload.php';

$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\EcritureComptable;
use App\Models\ExerciceComptable;
use App\Models\PlanComptable;

// Démarrer la session Laravel
$app->make('Illuminate\Session\Middleware\StartSession')->handle(
    Illuminate\Http\Request::capture(),
    function ($request) { return response('OK'); }
);

echo "\u003c!DOCTYPE html\u003e\n";
echo "\u003chtml\u003e\u003chead\u003e\u003cmeta charset='UTF-8'\u003e\u003ctitle\u003eDiagnostic Balance\u003c/title\u003e\u003c/head\u003e\u003cbody style='font-family: monospace; padding: 20px;'\u003e\n";
echo "\u003ch1\u003eDiagnostic Balance - Données Vides\u003c/h1\u003e\n";

// Récupérer les informations de session
$companyId = session('current_company_id');
$exerciceId = session('current_exercice_id');
$userId = auth()->id();

echo "\u003ch2\u003e1. Informations de Session\u003c/h2\u003e\n";
echo "\u003cul\u003e\n";
echo "\u003cli\u003e\u003cstrong\u003eCompany ID:\u003c/strong\u003e " . ($companyId ?? 'NON DÉFINI') . "\u003c/li\u003e\n";
echo "\u003cli\u003e\u003cstrong\u003eExercice ID (Session):\u003c/strong\u003e " . ($exerciceId ?? 'NON DÉFINI') . "\u003c/li\u003e\n";
echo "\u003cli\u003e\u003cstrong\u003eUser ID:\u003c/strong\u003e " . ($userId ?? 'NON CONNECTÉ') . "\u003c/li\u003e\n";
echo "\u003c/ul\u003e\n";

if (!$companyId) {
    echo "\u003cp style='color: red; font-weight: bold;'\u003eERREUR: Aucune compagnie sélectionnée !\u003c/p\u003e\n";
    echo "\u003c/body\u003e\u003c/html\u003e";
    exit;
}

// Récupérer l'exercice actif si pas dans la session
if (!$exerciceId) {
    $exercice = ExerciceComptable::where('company_id', $companyId)
        ->where('is_active', 1)
        ->first();
    $exerciceId = $exercice ? $exercice->id : null;
}

echo "\u003ch2\u003e2. Exercices Disponibles\u003c/h2\u003e\n";
$exercices = ExerciceComptable::where('company_id', $companyId)->get();
echo "\u003ctable border='1' cellpadding='5' style='border-collapse: collapse;'\u003e\n";
echo "\u003ctr\u003e\u003cth\u003eID\u003c/th\u003e\u003cth\u003eIntitulé\u003c/th\u003e\u003cth\u003eDébut\u003c/th\u003e\u003cth\u003eFin\u003c/th\u003e\u003cth\u003eActif\u003c/th\u003e\u003cth\u003eNb Écritures\u003c/th\u003e\u003c/tr\u003e\n";
foreach ($exercices as $ex) {
    $count = EcritureComptable::where('company_id', $companyId)
        ->where('exercices_comptables_id', $ex->id)
        ->count();
    $isActive = $ex->id == $exerciceId ? ' style="background-color: #90EE90;"' : '';
    echo "\u003ctr{$isActive}\u003e\n";
    echo "\u003ctd\u003e{$ex->id}\u003c/td\u003e\n";
    echo "\u003ctd\u003e{$ex->intitule}\u003c/td\u003e\n";
    echo "\u003ctd\u003e{$ex->date_debut}\u003c/td\u003e\n";
    echo "\u003ctd\u003e{$ex->date_fin}\u003c/td\u003e\n";
    echo "\u003ctd\u003e" . ($ex->is_active ? 'OUI' : 'NON') . "\u003c/td\u003e\n";
    echo "\u003ctd\u003e\u003cstrong\u003e{$count}\u003c/strong\u003e\u003c/td\u003e\n";
    echo "\u003c/tr\u003e\n";
}
echo "\u003c/table\u003e\n";

echo "\u003ch2\u003e3. Statistiques Écritures\u003c/h2\u003e\n";
$totalEcritures = EcritureComptable::where('company_id', $companyId)->count();
echo "\u003cul\u003e\n";
echo "\u003cli\u003e\u003cstrong\u003eTotal écritures (toutes):\u003c/strong\u003e {$totalEcritures}\u003c/li\u003e\n";

if ($exerciceId) {
    $ecrituresExercice = EcritureComptable::where('company_id', $companyId)
        ->where('exercices_comptables_id', $exerciceId)
        ->count();
    echo "\u003cli\u003e\u003cstrong\u003eÉcritures pour exercice sélectionné ({$exerciceId}):\u003c/strong\u003e {$ecrituresExercice}\u003c/li\u003e\n";
}

$ecrituresSansExercice = EcritureComptable::where('company_id', $companyId)
    ->whereNull('exercices_comptables_id')
    ->count();
echo "\u003cli\u003e\u003cstrong\u003eÉcritures SANS exercice:\u003c/strong\u003e {$ecrituresSansExercice}\u003c/li\u003e\n";
echo "\u003c/ul\u003e\n";

echo "\u003ch2\u003e4. Test de Requête Balance\u003c/h2\u003e\n";
$compte1 = PlanComptable::withoutGlobalScopes()
    ->where('company_id', $companyId)
    ->orderBy('numero_de_compte')
    ->first();

$compte2 = PlanComptable::withoutGlobalScopes()
    ->where('company_id', $companyId)
    ->orderBy('numero_de_compte', 'desc')
    ->first();

if ($compte1 && $compte2 && $exerciceId) {
    $exercice = ExerciceComptable::find($exerciceId);
    
    echo "\u003cp\u003e\u003cstrong\u003ePlage testée:\u003c/strong\u003e {$compte1->numero_de_compte} à {$compte2->numero_de_compte}\u003c/p\u003e\n";
    echo "\u003cp\u003e\u003cstrong\u003ePériode testée:\u003c/strong\u003e {$exercice->date_debut} à {$exercice->date_fin}\u003c/p\u003e\n";
    
    $min = $compte1->numero_de_compte < $compte2->numero_de_compte ? $compte1->numero_de_compte : $compte2->numero_de_compte;
    $max = $compte1->numero_de_compte > $compte2->numero_de_compte ? $compte1->numero_de_compte : $compte2->numero_de_compte;
    
    $comptesIds = PlanComptable::withoutGlobalScopes()
        ->where('company_id', $companyId)
        ->where('numero_de_compte', '>=', $min)
        ->where('numero_de_compte', '<=', $max)
        ->pluck('id');
    
    echo "\u003cp\u003e\u003cstrong\u003eNombre de comptes dans la plage:\u003c/strong\u003e " . $comptesIds->count() . "\u003c/p\u003e\n";
    
    // SANS filtre exercice
    $ecrituresSansFiltre = EcritureComptable::where('company_id', $companyId)
        ->whereIn('plan_comptable_id', $comptesIds)
        ->whereBetween('date', [$exercice->date_debut, $exercice->date_fin])
        ->count();
    echo "\u003cp\u003e\u003cstrong\u003eÉcritures trouvées (SANS filtre exercice):\u003c/strong\u003e \u003cspan style='color: blue; font-size: 20px;'\u003e{$ecrituresSansFiltre}\u003c/span\u003e\u003c/p\u003e\n";
    
    // AVEC filtre exercice
    $ecrituresAvecFiltre = EcritureComptable::where('company_id', $companyId)
        ->whereIn('plan_comptable_id', $comptesIds)
        ->whereBetween('date', [$exercice->date_debut, $exercice->date_fin])
        ->where('exercices_comptables_id', $exerciceId)
        ->count();
    echo "\u003cp\u003e\u003cstrong\u003eÉcritures trouvées (AVEC filtre exercice {$exerciceId}):\u003c/strong\u003e \u003cspan style='color: " . ($ecrituresAvecFiltre > 0 ? 'green' : 'red') . "; font-size: 20px; font-weight: bold;'\u003e{$ecrituresAvecFiltre}\u003c/span\u003e\u003c/p\u003e\n";
    
    if ($ecrituresSansFiltre > 0 && $ecrituresAvecFiltre == 0) {
        echo "\u003cdiv style='background-color: #ffcccc; padding: 15px; border-left: 5px solid red; margin: 20px 0;'\u003e\n";
        echo "\u003ch3 style='margin-top: 0; color: red;'\u003e⚠️ PROBLÈME IDENTIFIÉ\u003c/h3\u003e\n";
        echo "\u003cp\u003eIl y a {$ecrituresSansFiltre} écritures dans la plage de dates, mais AUCUNE n'est liée à l'exercice sélectionné (ID: {$exerciceId}).\u003c/p\u003e\n";
        echo "\u003cp\u003e\u003cstrong\u003eSolutions possibles:\u003c/strong\u003e\u003c/p\u003e\n";
        echo "\u003col\u003e\n";
        echo "\u003cli\u003eVérifier que les écritures ont bien le champ \u003ccode\u003eexercices_comptables_id\u003c/code\u003e rempli\u003c/li\u003e\n";
        echo "\u003cli\u003eRetirer temporairement le filtre par exercice pour tester\u003c/li\u003e\n";
        echo "\u003cli\u003eSélectionner un autre exercice dans la sidebar\u003c/li\u003e\n";
        echo "\u003c/ol\u003e\n";
        echo "\u003c/div\u003e\n";
        
        // Afficher quelques écritures pour diagnostic
        echo "\u003ch3\u003eExemple d'écritures trouvées (sans filtre exercice):\u003c/h3\u003e\n";
        $exemples = EcritureComptable::where('company_id', $companyId)
            ->whereIn('plan_comptable_id', $comptesIds)
            ->whereBetween('date', [$exercice->date_debut, $exercice->date_fin])
            ->with('planComptable')
            ->limit(10)
            ->get();
        
        echo "\u003ctable border='1' cellpadding='5' style='border-collapse: collapse;'\u003e\n";
        echo "\u003ctr\u003e\u003cth\u003eID\u003c/th\u003e\u003cth\u003eDate\u003c/th\u003e\u003cth\u003eCompte\u003c/th\u003e\u003cth\u003eExercice ID\u003c/th\u003e\u003cth\u003eDébit\u003c/th\u003e\u003cth\u003eCrédit\u003c/th\u003e\u003c/tr\u003e\n";
        foreach ($exemples as $ec) {
            echo "\u003ctr\u003e\n";
            echo "\u003ctd\u003e{$ec->id}\u003c/td\u003e\n";
            echo "\u003ctd\u003e{$ec->date}\u003c/td\u003e\n";
            echo "\u003ctd\u003e" . ($ec->planComptable ? $ec->planComptable->numero_de_compte : 'N/A') . "\u003c/td\u003e\n";
            echo "\u003ctd\u003e\u003cstrong\u003e" . ($ec->exercices_comptables_id ?? 'NULL') . "\u003c/strong\u003e\u003c/td\u003e\n";
            echo "\u003ctd\u003e" . number_format($ec->debit, 0, ',', ' ') . "\u003c/td\u003e\n";
            echo "\u003ctd\u003e" . number_format($ec->credit, 0, ',', ' ') . "\u003c/td\u003e\n";
            echo "\u003c/tr\u003e\n";
        }
        echo "\u003c/table\u003e\n";
    }
}

echo "\u003c/body\u003e\u003c/html\u003e";
