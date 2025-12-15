<?php

namespace App\Traits;

use Illuminate\Support\Facades\Auth;
use App\Models\Company;

trait ManagesCompany
{
    /**
     * Récupère la liste des IDs de toutes les compagnies gérées par l'utilisateur.
     * @return array
     */
    protected function getManagedCompanyIds(): array
    {
        $user = Auth::user();
        $userCompanyId = $user->company_id;

        // Si l'utilisateur est un Admin principal, récupère sa compagnie + toutes les sous-compagnies.
        $managedCompanyIds = Company::where('id', $userCompanyId)
                               ->orWhere('parent_company_id', $userCompanyId)
                               ->orWhere('user_id', $user->id)
                               ->pluck('id')
                               ->toArray();

        // Sécurité : s'assurer que l'ID de la compagnie actuelle est toujours inclus.
        if (!in_array($userCompanyId, $managedCompanyIds)) {
            $managedCompanyIds[] = $userCompanyId;
        }

        return $managedCompanyIds;
    }
}
