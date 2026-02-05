<?php
// debug_range.php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\PlanComptable;
use App\Models\EcritureComptable;

// ID trouvé dans le test précédent (Compte 622200)
$missingId = 9735; 

$pc = PlanComptable::withoutGlobalScopes()->find($missingId);

if (!$pc) {
    echo "Compte ID $missingId INTROUVABLE.\n";
} else {
    echo "Compte ID $missingId:\n";
    echo " - Numéro: " . $pc->numero_de_compte . "\n";
    echo " - Company ID: " . $pc->company_id . "\n";
    echo " - User ID: " . $pc->user_id . "\n";
}

$companyId = 1;
echo "\nTarget Company ID: $companyId\n";

if ($pc && $pc->company_id != $companyId) {
    echo "⚠️ MISMATCH: Le compte n'appartient pas à la compagnie active!\n";
    
    // Vérifier les écritures
    $count = EcritureComptable::where('company_id', $companyId)
        ->where('plan_comptable_id', $missingId)
        ->count();
        
    echo "Nombre d'écritures pour ce compte dans la compagnie $companyId: $count\n";
    if ($count > 0) {
        echo "C'est la cause du problème: des écritures utilisent un compte d'une autre compagnie (ou null).\n";
    }
}
