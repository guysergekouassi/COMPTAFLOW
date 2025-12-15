<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use App\Models\User;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot(): void
    {
        $this->registerPolicies();

        // Le Super Admin a tous les droits (Bypass Gate)
        // Ceci est une convention Laravel pour les super-utilisateurs.
        Gate::before(function (User $user, string $ability) {
            if ($user->isSuperAdmin()) {
                return true;
            }
        });

        // 1. Gate 'manage-users' : Permet aux Admins de voir et lister les utilisateurs.
        // Ce Gate permet l'accès à l'index et aux statistiques.
        Gate::define('manage-users', function (User $user) {
            // isAdmin() dans le modèle User inclut déjà isSuperAdmin()
            return $user->isAdmin();
        });

        // 2. Gate 'edit-users' : Permet UNIQUEMENT au Super Admin de créer/modifier/supprimer.
        // Cela empêche l'Admin de manipuler les utilisateurs, en forçant le rôle spécifique.
        Gate::define('edit-users', function (User $user) {
            // Note: Nous utilisons isSuperAdmin() directement (qui vérifie 'super_admin')
            return $user->isSuperAdmin();
        });
    }
}
