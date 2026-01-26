<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use App\Models\PlanComptable;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth; // <-- AJOUTER
use Illuminate\Support\Collection;   // <-- AJOUTER (pour la transformation)
use App\Models\Company;
use App\Models\ExerciceComptable;
use App\Models\CodeJournal;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }


    /**
     * Bootstrap any application services.
     */

    public function boot()
    {

        View::composer('components.sidebar', function ($view) {
            if (Auth::check()) {
                $user = Auth::user();
                $currentCompanyId = session('current_company_id', $user->company_id);
                $currentCompany = \App\Models\Company::find($currentCompanyId);
                
                $pendingApprovalsCount = 0;
                if ($user->isAdmin()) {
                    $pendingApprovalsCount = \App\Models\Approval::where('status', 'pending')->count();
                }

                // Logic moved from sidebar.blade.php
                $isComptaAccountActive = session('plan_comptable', true) && session('current_compta_account_id', true);
                
                $exercices = \App\Models\ExerciceComptable::where('company_id', $currentCompanyId)
                    ->orderBy('date_debut', 'desc')
                    ->get()
                    ->unique(function ($item) {
                        return trim($item->intitule);
                    });

                $code_journaux = \App\Models\CodeJournal::where('company_id', $currentCompanyId)
                    ->get()
                    ->unique('code_journal');

                $exerciceActif = $exercices->where('cloturer', 0)->first() ?? $exercices->first();

                // Companies for switch
                if ($user->role === 'super_admin') {
                    $companies_for_switch = \App\Models\Company::all();
                } elseif ($user->role === 'admin') {
                    $companies_for_switch = \App\Models\Company::where('id', $user->company_id)
                        ->orWhere('parent_company_id', $user->company_id)
                        ->get();
                } else {
                    $companies_for_switch = \App\Models\Company::where('id', $user->company_id)->get();
                }

                $view->with([
                    'company' => $currentCompany,
                    'currentCompany' => $currentCompany,
                    'pendingApprovalsCount' => $pendingApprovalsCount,
                    'isComptaAccountActive' => $isComptaAccountActive,
                    'exercices' => $exercices,
                    'code_journaux' => $code_journaux,
                    'exerciceActif' => $exerciceActif,
                    'companies' => $companies_for_switch,
                    'currentCompanyId' => $currentCompanyId
                ]);
            } else {
                $view->with([
                    'company' => null,
                    'currentCompany' => null,
                    'pendingApprovalsCount' => 0,
                    'isComptaAccountActive' => false,
                    'exercices' => collect(),
                    'code_journaux' => collect(),
                    'exerciceActif' => null,
                    'companies' => collect(),
                    'currentCompanyId' => null
                ]);
            }
        });

        // Appliquer à toutes les vues du dossier Tresor
        View::composer('Tresor.*', function ($view) {
            $comptesClasse5 = PlanComptable::where('numero_de_compte', 5)->get();
            $view->with('comptesClasse5', $comptesClasse5);
        });
        // ------------------------------------------------------------------
        // NOUVEAU VIEW COMPOSER POUR LES HABILITATIONS (APPLIQUÉ À TOUTES LES VUES)
        // ------------------------------------------------------------------
        // ------------------------------------------------------------------
        // NOUVEAU VIEW COMPOSER POUR LES HABILITATIONS (OPTIMISÉ)
        // ------------------------------------------------------------------
        static $cachedHabilitations = null;
        View::composer('*', function ($view) use (&$cachedHabilitations) {
            if (Auth::check()) {
                if ($cachedHabilitations === null) {
                    $userHabilitations = Auth::user()->habilitations ?? [];
                    $cachedHabilitations = collect($userHabilitations)
                        ->filter(function ($value) {
                            return $value === true || $value === "1"; 
                        })
                        ->keys()
                        ->toArray();
                }
                $view->with('habilitations', $cachedHabilitations);
            } else {
                $view->with('habilitations', []);
            }
        });


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
        //
      View::composer('components.modal_saisie_direct', function ($view) {
    if (Auth::check()) {
        $user = Auth::user();
        
        // CORRECTION : On utilise 'current_company_id' et on lui donne la PRIORITÉ
        $companyId = session('current_company_id', $user->company_id);

        // Récupère les données filtrées par la société active (switchée)
        $exercices = ExerciceComptable::where('company_id', $companyId)
            ->orderBy('date_debut', 'desc')
            ->get();
            // Retirez le unique('intitule') si vous voulez voir tous les exercices par année

        $code_journaux = CodeJournal::where('company_id', $companyId)->get();

        // Définition de l'exercice actif (le premier de la liste par défaut)
        $exerciceActif = $exercices->where('is_active', true)->first() ?? $exercices->first();

        // Partage les variables avec la vue du modal
        $view->with([
            'exercices' => $exercices,
            'code_journaux' => $code_journaux,
            'exerciceActif' => $exerciceActif
        ]);
    } else {
        $view->with([
            'exercices' => collect(),
            'code_journaux' => collect(),
            'exerciceActif' => null
        ]);
    }
});


      View::composer(['layouts.app', 'user_management', 'layouts.sections.sidebar.company-menu'], function ($view) {
            if (Auth::check()) {
                $user = Auth::user();

                // 1. Récupération des compagnies gérées (logique de getManagedCompanies)
                $mainCompanies = Company::where('user_id', $user->id)
                                        ->whereNull('parent_company_id')
                                        ->get();

                $subCompanies = Company::where('parent_company_id', $user->company_id)->get();
                $managedCompanies = $mainCompanies->merge($subCompanies)->unique('id');

                $contextCompany = Company::find($user->company_id);
                if ($contextCompany && !$managedCompanies->contains('id', $contextCompany->id)) {
                    $managedCompanies->push($contextCompany);
                }

                $managedCompanies = $managedCompanies->sortBy('company_name');

                // 2. Définition de la variable manquante $currentCompanyId
                $currentCompanyId = session('current_company_id', $user->company_id);

                $view->with([
                    'managedCompanies' => $managedCompanies,
                    'currentCompanyId' => $currentCompanyId,
                ]);
            } else {
                    // Variables par défaut si non connecté
                $view->with('managedCompanies', collect([]))
                     ->with('currentCompanyId', null);
            }
        });


}
}
