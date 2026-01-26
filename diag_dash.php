<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\ExerciceComptable;
use Illuminate\Support\Facades\DB;

$cid = 33;
echo "--- DIAGNOSTIC SOCIÉTÉ $cid ---\n";

$active = ExerciceComptable::where('company_id', $cid)->where('is_active', 1)->first();
echo "Recherche is_active=1: " . ($active ? $active->intitule : "NOT FOUND") . "\n";

$activeBool = ExerciceComptable::where('company_id', $cid)->where('is_active', true)->first();
echo "Recherche is_active=true: " . ($activeBool ? $activeBool->intitule : "NOT FOUND") . "\n";

$any = ExerciceComptable::where('company_id', $cid)->get();
echo "Nombre d'exercices trouvés: " . $any->count() . "\n";
foreach($any as $ex) {
    echo " - ID: {$ex->id}, Title: {$ex->intitule}, Active: " . (int)$ex->is_active . ", Cloturer: " . (int)$ex->cloturer . "\n";
}

$default = ExerciceComptable::where('company_id', $cid)
    ->where('cloturer', 0)
    ->orderBy('date_debut', 'desc')
    ->first();
echo "Exercice par défaut (cloturer=0): " . ($default ? $default->intitule : "NOT FOUND") . "\n";

$defaultWithOne = ExerciceComptable::where('company_id', $cid)
    ->where('cloturer', 1)
    ->orderBy('date_debut', 'desc')
    ->first();
echo "Exercice par défaut (cloturer=1): " . ($defaultWithOne ? $defaultWithOne->intitule : "NOT FOUND") . "\n";
