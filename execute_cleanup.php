<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\TreasuryCategory;
use App\Models\CompteTresorerie;
use Illuminate\Support\Facades\DB;

DB::beginTransaction();
try {
    echo "--- DEBUT DU NETTOYAGE ---\n";

    // 1. Identifier les catégories à supprimer (Banques, Caisses, etc.)
    $obsoleteNames = ['Banques', 'Comptes Courants', 'Comptes d\'Épargne', 'Comptes de Dépôt', "flux d'investissement", 'Caisses', 'Autres'];
    $obsoleteCategories = TreasuryCategory::whereIn('name', $obsoleteNames)->get();
    
    foreach ($obsoleteCategories as $oldCat) {
        // Trouver une catégorie de remplacement pour la même compagnie (le flux I par défaut)
        $replacement = TreasuryCategory::where('company_id', $oldCat->company_id)
            ->where('name', 'like', 'I.%')
            ->first();
            
        if (!$replacement) {
            // Si pas de flux I pour cette compagnie, en créer un ou prendre le premier disponible
            $replacement = TreasuryCategory::where('company_id', $oldCat->company_id)->first();
        }

        if ($replacement) {
            $affected = CompteTresorerie::where('category_id', $oldCat->id)
                ->update(['category_id' => $replacement->id]);
            echo "Réassignation de {$affected} postes de la catégorie \"{$oldCat->name}\" (ID: {$oldCat->id}) vers \"{$replacement->name}\" (ID: {$replacement->id})\n";
        }
        
        $oldCat->delete();
        echo "Suppression de la catégorie : \"{$oldCat->name}\" (ID: {$oldCat->id})\n";
    }

    echo "\n--- VERIFICATION FINALE ---\n";
    $remaining = TreasuryCategory::all();
    foreach ($remaining as $r) {
        $count = CompteTresorerie::where('category_id', $r->id)->count();
        echo "Flux conservé: \"{$r->name}\" (ID: {$r->id}) | Company: {$r->company_id} | Postes: {$count}\n";
    }

    DB::commit();
    echo "\nNETTOYAGE TERMINE AVEC SUCCES.\n";
} catch (\Exception $e) {
    DB::rollBack();
    echo "ERREUR DURANT LE NETTOYAGE: " . $e->getMessage() . "\n";
}
