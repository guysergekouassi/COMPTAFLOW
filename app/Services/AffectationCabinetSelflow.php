<?php

namespace App\Services;

use App\Models\Cabinet;
use App\Models\Company;
use Illuminate\Support\Facades\DB;

/**
 * Affecter un dossier ouvert depuis Selflow à un cabinet (24/11/2026).
 *
 * Deux portées, au choix du superadministrateur de Selflow :
 *
 *   - **le cabinet seul** : `companies.cabinet_id`. Le dossier entre dans le
 *     portefeuille du cabinet, et son gérant le voit dans son espace
 *     (`Rattachements::comptabilitesDe`) ;
 *   - **un membre précis du cabinet** : le dossier est rangé dans le cabinet
 *     (son groupe) ET confié à ce membre (`company_user`, rôle admin), qui le
 *     voit dans son propre espace.
 *
 * Rien n'est retiré : un rattachement existant reste en place.
 */
class AffectationCabinetSelflow
{
    /**
     * @return array{success: bool, message: string}
     */
    public static function appliquer(Company $company, ?int $cabinetId, ?int $userId = null): array
    {
        if (!$cabinetId) {
            return ['success' => true, 'message' => 'Aucun cabinet choisi.'];
        }

        $cabinet = Cabinet::where('id', $cabinetId)->where('is_active', true)->first();
        if (!$cabinet) {
            return ['success' => false, 'message' => 'Cabinet introuvable ou inactif.'];
        }

        if ($userId) {
            $membre = (int) $cabinet->user_id === $userId
                || DB::table('cabinet_user')->where('cabinet_id', $cabinet->id)->where('user_id', $userId)->exists();
            if (!$membre) {
                return ['success' => false, 'message' => "Cet utilisateur n'est pas membre du cabinet « {$cabinet->nom} »."];
            }
        }

        DB::transaction(function () use ($company, $cabinet, $userId) {
            $company->forceFill(['cabinet_id' => $cabinet->id])->save();

            if ($userId && (int) $company->user_id !== $userId) {
                DB::table('company_user')->updateOrInsert(
                    ['company_id' => $company->id, 'user_id' => $userId],
                    ['role' => 'admin', 'updated_at' => now(), 'created_at' => now()]
                );
            }
        });

        return [
            'success' => true,
            'message' => $userId
                ? "Dossier affecté au cabinet « {$cabinet->nom} » et confié au membre choisi."
                : "Dossier affecté au cabinet « {$cabinet->nom} ».",
        ];
    }
}
