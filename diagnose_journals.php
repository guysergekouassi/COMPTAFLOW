<?php
/**
 * Script de diagnostic pour tracer la création des journaux
 * À exécuter via: php diagnose_journals.php
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\CodeJournal;
use App\Models\ImportStaging;
use Illuminate\Support\Facades\DB;

echo "=== DIAGNOSTIC DES JOURNAUX - COMPANY 33 ===\n\n";

// 1. État actuel des journaux
echo "1. JOURNAUX ACTUELS :\n";
$journals = CodeJournal::where('company_id', 33)
    ->select('id', 'code_journal', 'intitule', 'created_at', 'user_id')
    ->orderBy('created_at', 'desc')
    ->get();

if ($journals->isEmpty()) {
    echo "   ✓ Aucun journal existant (base propre)\n";
} else {
    echo "   ⚠ {$journals->count()} journal(aux) trouvé(s) :\n";
    foreach ($journals as $j) {
        echo "      - {$j->code_journal} | {$j->intitule} | Créé le: {$j->created_at} | User: {$j->user_id}\n";
    }
}

echo "\n2. IMPORTS EN COURS :\n";
$imports = ImportStaging::where('company_id', 33)
    ->where('type', 'journals')
    ->select('id', 'file_name', 'status', 'created_at')
    ->orderBy('created_at', 'desc')
    ->limit(5)
    ->get();

if ($imports->isEmpty()) {
    echo "   Aucun import en cours\n";
} else {
    foreach ($imports as $imp) {
        echo "   - Import #{$imp->id} | {$imp->file_name} | Status: {$imp->status} | {$imp->created_at}\n";
    }
}

echo "\n3. HISTORIQUE DES CRÉATIONS (dernières 24h) :\n";
$recent = DB::table('code_journals')
    ->where('company_id', 33)
    ->where('created_at', '>=', now()->subDay())
    ->select('code_journal', 'created_at', 'user_id')
    ->orderBy('created_at', 'desc')
    ->get();

if ($recent->isEmpty()) {
    echo "   Aucune création récente\n";
} else {
    foreach ($recent as $r) {
        echo "   - {$r->code_journal} créé à {$r->created_at} par user {$r->user_id}\n";
    }
}

echo "\n4. RECOMMANDATIONS :\n";
if ($journals->isNotEmpty()) {
    echo "   ⚠ Vous avez des journaux existants. Avant d'importer :\n";
    echo "      1. Vérifiez si vous avez cliqué sur 'Charger par défaut' par erreur\n";
    echo "      2. Supprimez-les avec: php cleanup_journals.php\n";
    echo "      3. Puis importez votre fichier\n";
} else {
    echo "   ✓ Base propre ! Vous pouvez importer votre fichier maintenant.\n";
}

echo "\n";
