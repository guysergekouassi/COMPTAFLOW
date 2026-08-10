<?php
/**
 * ============================================================
 * SCRIPT DE RATTACHEMENT D'ENTREPRISES À UN GÉRANT
 * ============================================================
 * Ce script PHP à exécuter UNE SEULE FOIS permet de :
 *
 * 1. Trouver toutes les entreprises créées avec un ancien email
 *    (avant la centralisation des comptes)
 * 2. Les rattacher au gérant principal (mail du gérant)
 * 3. Écraser l'ancien email de l'entreprise par le mail du gérant
 * 4. Insérer les liaisons dans la table pivot company_user
 *
 * USAGE :
 *   php artisan tinker < database/scripts/rattacher_entreprises.php
 *
 * OU depuis le terminal Laravel :
 *   php artisan compta:link-companies
 *
 * ============================================================
 * CONFIGURATION – À MODIFIER AVANT EXÉCUTION
 * ============================================================
 */

use App\Models\User;
use App\Models\Company;
use Illuminate\Support\Facades\DB;

// ─────────────────────────────────────────────
// 🔧 PARAMÈTRES À REMPLIR
// ─────────────────────────────────────────────

// Email du gérant PRINCIPAL (le compte qui doit tout gérer)
$gerантEmail = 'votre.email@gmail.com'; // ← REMPLACER PAR LE VRAI MAIL

// Liste des anciens emails utilisés pour créer les entreprises
// Chaque entrée = ['email_ancien' => 'email@exemple.com', 'role_dans_entreprise' => 'admin']
$anciensMails = [
    ['email' => 'entreprise1@gmail.com', 'role' => 'admin'],
    ['email' => 'entreprise2@gmail.com', 'role' => 'admin'],
    // ← Ajouter autant que nécessaire
];

// Remplacer également l'email dans la table companies ? (true = oui)
$updateCompanyEmail = true;

// ─────────────────────────────────────────────
// SCRIPT (NE PAS MODIFIER EN DESSOUS)
// ─────────────────────────────────────────────

echo "\n========================================\n";
echo "  RATTACHEMENT D'ENTREPRISES AU GÉRANT\n";
echo "========================================\n\n";

// 1. Trouver le gérant principal
$gerant = User::where('email_adresse', $gerantEmail)->first();

if (!$gerant) {
    echo "❌ ERREUR: Gérant introuvable pour l'email: {$gerantEmail}\n";
    echo "   Vérifiez l'email et réessayez.\n";
    exit(1);
}

echo "✅ Gérant principal: {$gerant->name} {$gerant->last_name} (ID: {$gerant->id})\n\n";

$totalRattachees = 0;
$totalCreesUser = 0;

foreach ($anciensMails as $entry) {
    $oldEmail = $entry['email'];
    $role = $entry['role'] ?? 'admin';

    echo "📌 Traitement de l'email: {$oldEmail}\n";

    // Trouver les entreprises liées à cet ancien email
    // Cherche dans companies.email_adresse ET dans users.email_adresse (ancien compte)
    $companies = Company::where('email_adresse', $oldEmail)->get();
    $oldUser = User::where('email_adresse', $oldEmail)->first();

    if ($companies->isEmpty() && !$oldUser) {
        echo "   ⚠️  Aucune entreprise ni utilisateur trouvé pour cet email.\n\n";
        continue;
    }

    // Rattacher les entreprises trouvées par email d'entreprise
    foreach ($companies as $company) {
        echo "   🏢 Entreprise trouvée: {$company->company_name} (ID: {$company->id})\n";

        DB::beginTransaction();
        try {
            // Mettre à jour le propriétaire de l'entreprise
            Company::where('id', $company->id)->update([
                'user_id' => $gerant->id,
            ]);

            // Insérer dans la table pivot company_user
            DB::table('company_user')->updateOrInsert(
                ['company_id' => $company->id, 'user_id' => $gerant->id],
                ['role' => $role, 'created_at' => now(), 'updated_at' => now()]
            );

            // Générer un code unique pour l'entreprise si elle n'en a pas
            if (!$company->company_code) {
                $cleanName = preg_replace('/[^A-Za-z]/', '', $company->company_name);
                $prefix = strtoupper(substr($cleanName, 0, 3));
                $prefix = str_pad($prefix, 3, 'X');

                $attempts = 0;
                do {
                    $code = $prefix . '-' . strtoupper(\Illuminate\Support\Str::random(6));
                    $exists = Company::where('company_code', $code)->exists();
                    $attempts++;
                } while ($exists && $attempts < 30);

                Company::where('id', $company->id)->update(['company_code' => $code]);
                echo "   🔑 Code unique généré: {$code}\n";
            }

            DB::commit();
            $totalRattachees++;
            echo "   ✅ Rattachée avec succès au gérant.\n";

        } catch (Exception $e) {
            DB::rollBack();
            echo "   ❌ ERREUR: " . $e->getMessage() . "\n";
        }
    }

    // Si l'ancien email appartient à un USER différent du gérant
    if ($oldUser && $oldUser->id !== $gerant->id) {
        echo "   👤 Ancien utilisateur trouvé: {$oldUser->name} {$oldUser->last_name} (ID: {$oldUser->id})\n";

        // Rattacher également ses entreprises (company_id = son ancien id)
        $userCompanies = Company::where('user_id', $oldUser->id)->get();

        foreach ($userCompanies as $uc) {
            echo "   🏢 Entreprise de cet utilisateur: {$uc->company_name} (ID: {$uc->id})\n";

            DB::beginTransaction();
            try {
                Company::where('id', $uc->id)->update(['user_id' => $gerant->id]);

                DB::table('company_user')->updateOrInsert(
                    ['company_id' => $uc->id, 'user_id' => $gerant->id],
                    ['role' => $role, 'created_at' => now(), 'updated_at' => now()]
                );

                // Générer un code si absent
                if (!$uc->company_code) {
                    $cleanName = preg_replace('/[^A-Za-z]/', '', $uc->company_name);
                    $prefix = strtoupper(str_pad(substr($cleanName, 0, 3), 3, 'X'));
                    $attempts = 0;
                    do {
                        $code = $prefix . '-' . strtoupper(\Illuminate\Support\Str::random(6));
                        $exists = Company::where('company_code', $code)->exists();
                    } while ($exists && ++$attempts < 30);

                    Company::where('id', $uc->id)->update(['company_code' => $code]);
                    echo "   🔑 Code unique généré: {$code}\n";
                }

                // Transférer les écritures de l'ancien user vers le gérant
                $tables = ['ecriture_comptables', 'plan_comptables', 'plan_tiers', 'code_journals'];
                foreach ($tables as $table) {
                    if (\Illuminate\Support\Facades\Schema::hasTable($table)) {
                        DB::table($table)->where('company_id', $uc->id)->where('user_id', $oldUser->id)
                            ->update(['user_id' => $gerant->id]);
                    }
                }

                DB::commit();
                $totalRattachees++;
                echo "   ✅ Entreprise et écritures rattachées au gérant.\n";

            } catch (Exception $e) {
                DB::rollBack();
                echo "   ❌ ERREUR: " . $e->getMessage() . "\n";
            }
        }
    }

    echo "\n";
}

// ─────────────────────────────────────────────
// RÉSUMÉ FINAL
// ─────────────────────────────────────────────
echo "========================================\n";
echo "  RÉSUMÉ\n";
echo "========================================\n";
echo "  Entreprises rattachées : {$totalRattachees}\n";
echo "\n  Récapitulatif du portefeuille de {$gerant->name} :\n";

$allCompanies = Company::where('user_id', $gerant->id)->get();
foreach ($allCompanies as $c) {
    echo "  • {$c->company_name} – Code: " . ($c->company_code ?? '⚠️ Non généré') . "\n";
}

echo "\n✅ Script terminé avec succès !\n";
echo "   Connectez-vous à Mon Espace pour vérifier : /mon-espace\n\n";
