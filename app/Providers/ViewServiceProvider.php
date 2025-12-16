<?php

namespace App\Providers;

use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Auth;

class ViewServiceProvider extends ServiceProvider
{
    /**
     * Enregistre les services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Démarre les services.
     */
    public function boot(): void
    {
        // 'layouts.sections.sidebar.company-menu' doit être le chemin de votre vue Blade de menu
        // Si votre menu est inclus dans 'layouts.app', utilisez 'layouts.*'
        View::composer('layouts.sections.sidebar.company-menu', function ($view) {
            $isComptaAccountActive = false;
            $habilitations = [];
            $user = Auth::user();

            // 1. Déterminer si un compte Comptabilité est actif en session
            $activeCompanyId = session('active_company_id');
            if ($activeCompanyId) {
                // S'assurer que l'utilisateur appartient bien à cette compagnie ou est Super Admin
                if ($user && ($user->isSuperAdmin() || $user->company_id === $activeCompanyId)) {
                    $isComptaAccountActive = true;
                }
            }

            // 2. Récupérer les habilitations si le compte est actif
            if ($isComptaAccountActive && $user) {
                $habilitations = $user->getHabilitations();
            }

            // 3. Partager les variables avec la vue
            $view->with([
                'isComptaAccountActive' => $isComptaAccountActive,
                'habilitations' => $habilitations,
            ]);
        });
    }
}
