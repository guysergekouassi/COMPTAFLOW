<?php
// use Illuminate\Support\Facades\Artisan;
// Route::get('/config-clear', function() {
//     Artisan::call('config:clear');
//     Artisan::call('cache:clear');
//     return "Configuration videe !";
// });
use App\Http\Middleware\authSuperAdminMiddleware;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BalanceController;
use App\Http\Controllers\BalanceTiersController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\JournauxSaisisController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PlanComptableController;
use App\Http\Controllers\PlanComptableEcritureController;
use App\Http\Controllers\PlanTiersEcritureController;
use App\Http\Controllers\PlanTiersController;
use App\Http\Controllers\CodeJournalController;
use App\Http\Controllers\EcritureComptableController;
use App\Http\Controllers\EcritureComptableGroupesController;
use App\Http\Controllers\PlanComptableEcritureGroupesController;
use App\Http\Controllers\PlanTiersEcritureGroupesController;
use App\Http\Controllers\ExerciceComptableController;
use App\Http\Controllers\GrandLivreController;
use App\Http\Controllers\GrandLivreTiersController;
use App\Http\Controllers\GestionTresorerieController;
use App\Http\Controllers\FluxTresorerieController;
use Maatwebsite\Excel\Facades\Excel;
use App\Http\Controllers\TresorerieContro\TresorerieController;
use App\Http\Controllers\compteController;
// Contrôleurs Super Admin (l'import était correct, mais la duplication posait problème)
use App\Http\Controllers\Super\SuperAdminSetupController;
// use App\Http\Controllers\Super\SuperAdminCompanyController;
use App\Http\Controllers\Super\SuperAdminDashboardController;
 use App\Http\Controllers\SuperAdminCompanyController;
use App\Http\Controllers\ComptaAccountController;
use App\Http\Controllers\CompanyAccessController;
use App\Http\Controllers\ComptaDashboardController;
use App\Http\Controllers\AccountingSwitchController;
use App\Http\Controllers\Souscrire\SubscriptionController;
use App\Http\Controllers\Compte\PosteTresorController;

use App\Http\Controllers\GeminiController;
use App\Http\Controllers\IaController;
use Illuminate\Support\Facades\Http;

// Route pour le traitement IA SYSCOHADA CI
Route::post('/ia-traitement', [IaController::class, 'traiterFacture']);

// Route de test sans CSRF
Route::post('/ia-traitement-test', function() {
    require_once public_path('ia_traitement_test.php');
});

// Route pour le script standalone (sans Laravel)
Route::post('/ia_traitement_standalone.php', function() {
    require_once public_path('ia_traitement_standalone.php');
});


















// **********************************************
// ROUTES PUBLIQUES / AUTHENTIFICATION
// **********************************************

Route::get('/login', function () {
    return view('login');
})->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');

    // Redirection vers le dashboard selon rôle après login
    Route::get('/dashboard', function () {
        $user = auth()->user();
        if ($user->isSuperAdmin()) {
            return redirect()->route('superadmin.dashboard');
        } elseif ($user->isAdmin()) {
            return redirect()->route('admin.dashboard');
        } elseif ($user->isComptable()) {
            return redirect()->route('comptable.comptdashboard');
        }
        return redirect('/unauthorized');
    })->name('app.dashboard');

// **********************************************
// ROUTES PROTÉGÉES (MIDDLEWARE 'auth')
// **********************************************

Route::middleware('auth')->group(function () {
    // Déconnexion
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Pages principales
    Route::get('/', function () { return redirect()->route('app.dashboard'); })->name('index');
    Route::get('/index', function () { return redirect()->route('app.dashboard'); })->name('index_page');
    Route::get('/accounting_balance', function () { return view('accounting_balance'); })->name('accounting_balance');
    Route::get('/accounting_entry', function () { return view('accounting_entry'); })->name('accounting_entry');
    Route::get('/file_management', function () { return view('file_management'); })->name('file_management');
    Route::get('/financial_statements', function () { return view('financial_statements'); })->name('financial_statements');

    // ***************** ROUTES PROFIL & PARAMETRES *****************
    Route::get('/profile', [UserController::class, 'profile'])->name('user.profile');
    Route::get('/settings', [UserController::class, 'settings'])->name('user.settings');
    Route::put('/settings/account', [UserController::class, 'updateAccount'])->name('user.settings.account');
    Route::put('/settings/password', [UserController::class, 'updatePassword'])->name('user.settings.password');
    Route::post('/settings/avatar', [UserController::class, 'updateAvatar'])->name('user.settings.avatar');
    Route::post('/ui/toggle-sidebar', [App\Http\Controllers\UserSessionController::class, 'toggleSidebarSection'])->name('ui.toggle_sidebar');

    // ROUTES NOTIFICATIONS INTERNES
    Route::get('/notifications', [App\Http\Controllers\InternalNotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications', [App\Http\Controllers\InternalNotificationController::class, 'store'])->name('notifications.store');
    Route::post('/notifications/{id}/read', [App\Http\Controllers\InternalNotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::get('/api/notifications/unread-count', [App\Http\Controllers\InternalNotificationController::class, 'unreadCount'])->name('api.notifications.unread_count');

    // *****************ROUTE GESTION DE COMPANY
    Route::get('/compagny_information', [CompanyController::class, 'index'])->name('compagny_information');
    Route::put('/compagny_information/{company}', [CompanyController::class, 'update'])->name('compagny_information.update');

    // *****************ROUTE GESTION DES USERS
    Route::post('/users', [UserController::class, 'store'])->name('users.store');
    Route::put('/users/{id}', [UserController::class, 'update'])->name('users.update');
    Route::delete('/users/{id}', [UserController::class, 'destroy'])->name('users.destroy');
    Route::get('/user_management', [UserController::class, 'stat_online'])->name('user_management');
    Route::get('/users', [UserController::class, 'index'])->name('users.index');


    //api route gemini 
    Route::post('/gemini/generate', [GeminiController::class, 'generateText']);
    Route::get('/gemini/list-models', function () {
    $apiKey = env('GEMINI_API_KEY');

    $response = Http::withHeaders([
        'Content-Type' => 'application/json',
    ])->get("https://generativelanguage.googleapis.com/v1beta/models?key={$apiKey}");

    return $response->json();
});


    // *****************ROUTE GESTION DES PLAN COMPTABLES
    Route::get('/plan_comptable', [PlanComptableController::class, 'index'])->name('plan_comptable');
    Route::post('/plan_comptable', [PlanComptableController::class, 'store'])->name('plan_comptable.store');
    Route::post('/plan_comptable/use-default', [PlanComptableController::class, 'useDefault'])->name('plan_comptable.defaut');
    Route::post('/plan_comptable/verifierNumeroCompte', [PlanComptableController::class, 'verifierNumeroCompte'])->name('verifierNumeroCompte');
    Route::put('/plan_comptable/{id}', [PlanComptableController::class, 'update'])->name('plan_comptable.update');
    Route::delete('/plan_comptable/{id}', [PlanComptableController::class, 'destroy'])->name('plan_comptable.destroy');
    Route::get('/plan_comptable_ecritures', [PlanComptableEcritureController::class, 'index'])->name('plan_comptable_ecritures');
    Route::put('/plan_comptable_ecritures/{id}', [PlanComptableEcritureController::class, 'update'])->name('plan_comptable_ecritures.update');
    Route::get('/plan_tiers_ecritures', [PlanTiersEcritureController::class, 'index'])->name('plan_tiers_ecritures');
    Route::put('/plan_tiers_ecritures/{id}', [PlanTiersEcritureController::class, 'update'])->name('plan_tiers_ecritures.update');

    // *****************ROUTE GESTION DES PLAN TIERS
    Route::get('/plan_tiers', [PlanTiersController::class, 'index'])->name('plan_tiers');
    Route::get('/plan_tiers/view/{plan_tier}', [PlanTiersController::class, 'show'])->name('plan_tiers.show');
    Route::get('/plan_tiers/{racine}', [PlanTiersController::class, 'getDernierNumero'])->name('getDernierNumero');
    Route::post('/plan_tiers', [PlanTiersController::class, 'store'])->name('plan_tiers.store');
    Route::put('/plan_tiers/{id}', [PlanTiersController::class, 'update'])->name('plan_tiers.update');
    Route::delete('/plan_tiers/{id}', [PlanTiersController::class, 'destroy'])->name('plan_tiers.destroy');

    // *****************ROUTE GESTION DES CODE JOURNAL
    Route::get('/accounting_journals', [CodeJournalController::class, 'index'])->name('accounting_journals')->middleware(['auth', 'company.session']);
    Route::post('/accounting_journals', [CodeJournalController::class, 'store'])->name('accounting_journals.store')->middleware(['auth', 'company.session']);
    Route::put('/accounting_journals/{id}', [CodeJournalController::class, 'update'])->name('accounting_journals.update')->middleware(['auth', 'company.session']);
    Route::delete('/accounting_journals/{id}', [CodeJournalController::class, 'destroy'])->name('accounting_journals.destroy')->middleware(['auth', 'company.session']);

    // *****************ROUTE GESTION DES ECRITURES COMPTABLE
    Route::get('/accounting_entry_real', [EcritureComptableController::class, 'index'])->name('accounting_entry_real');
    Route::get('/ecriture/{id}', [EcritureComptableController::class, 'show'])->name('ecriture.show');
    Route::get('/accounting_entry_list', [EcritureComptableController::class, 'list'])->name('accounting_entry_list');
    Route::get('/ecritures/rejetees', [EcritureComptableController::class, 'rejectedList'])->name('ecriture.rejected');
    Route::delete('/ecritures/saisie/{n_saisie}', [EcritureComptableController::class, 'deleteBySaisie'])->name('ecriture.delete_saisie');

    // Brouillons
    Route::get('/brouillons', [App\Http\Controllers\BrouillonController::class, 'index'])->name('brouillons.index');
    Route::post('/api/brouillons', [App\Http\Controllers\BrouillonController::class, 'store'])->name('api.brouillons.store')->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);
    Route::get('/api/brouillons/{batchId}', [App\Http\Controllers\BrouillonController::class, 'load'])->name('api.brouillons.load');
    Route::delete('/brouillons/{batchId}', [App\Http\Controllers\BrouillonController::class, 'destroy'])->name('brouillons.destroy');
    
    Route::middleware(['auth'])->group(function () {
        Route::post('/accounting_entry_real', [EcritureComptableController::class, 'storeMultiple'])->name('storeMultiple.storeMultiple');
        Route::post('/superadmin/switch/return', [\App\Http\Controllers\Super\SuperAdminSwitchController::class, 'returnToSuperAdmin'])->name('superadmin.switch.return');
    });
    
    Route::post('/api/ecritures', [EcritureComptableController::class, 'store'])->name('api.ecriture.store')->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);
    Route::delete('/ecriture-delete-by-saisie/{n_saisie?}', [EcritureComptableController::class, 'deleteBySaisie'])->name('ecriture.delete-by-saisie');
    Route::post('/ecriture', [EcritureComptableController::class, 'store'])->name('ecriture.store');
    Route::get('/ecriture-scan', [EcritureComptableController::class, 'scanIndex'])->name('ecriture.scan');
    // Route::post('/ecriture/store/multiple', [EcritureComptableController::class, 'storeMultiple'])->name('ecriture.store.multiple');
   Route::post('/ecritures-comptables/store', [EcritureComptableController::class, 'storeMultiple'])
     ->name('ecritures-comptables.store-multiple');
    Route::get('/saisie-directe-modal', [EcritureComptableController::class, 'showSaisieModal'])->name('modal_saisie_direct');
    // Route de test pour vérifier que le contrôleur fonctionne
    Route::get('/ecriture/get-next-saisie', [EcritureComptableController::class, 'getNextSaisieNumber'])
        ->name('ecriture.get-next-saisie');
    
    // Alias pour l'API (utilisé par le frontend)
    Route::get('/api/next-saisie-number', [EcritureComptableController::class, 'getNextSaisieNumber'])
        ->name('api.next-saisie-number');

    Route::post('/api/ecritures/multiple', [EcritureComptableController::class, 'storeMultiple'])
        ->name('api.ecriture.storeMultiple')
        ->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);

// Route de test pour vérifier que le contrôleur fonctionne
Route::get('/test-saisie-number', function() {
    $controller = new \App\Http\Controllers\EcritureComptableController();
    return $controller->getNextSaisieNumber(request());
});
    Route::get('/ecriture/{ecriture}/edit', [EcritureComptableController::class, 'edit'])->name('ecriture.edit');
    Route::get('/api/ecriture/load-by-saisie/{n_saisie}', [EcritureComptableController::class, 'loadBySaisie'])->name('api.ecriture.load-by-saisie');
    Route::put('/ecriture/{ecriture}', [EcritureComptableController::class, 'update'])->name('ecriture.update');
    Route::delete('/ecriture/{ecriture}', [EcritureComptableController::class, 'destroy'])->name('ecriture.destroy');

    // *****************ROUTE GESTION DES ECRITURES COMPTABLE GROUPES
    Route::get('/accounting_entry_real_goupes', [EcritureComptableGroupesController::class, 'index'])->name('accounting_entry_real_goupes');
    Route::put('/accounting_entry_real_goupes/{id}', [EcritureComptableGroupesController::class, 'update'])->name('accounting_entry_real_goupes.update');
    Route::post('/accounting_entry_real_goupes', [EcritureComptableGroupesController::class, 'miseAJourMassive'])->name('accounting_entry_real_goupes.miseAJourMassive');

    // *****************ROUTE GESTION DES ECRITURES COMPTABLE GROUPES VIA PLAN COMPTABLES
    Route::get('/plan_comptable_ecritures_groupes', [PlanComptableEcritureGroupesController::class, 'index'])->name('plan_comptable_ecritures_groupes');
    Route::put('/plan_comptable_ecritures_groupes/{id}', [PlanComptableEcritureGroupesController::class, 'update'])->name('plan_comptable_ecritures_groupes.update');
    Route::post('/plan_comptable_ecritures_groupes', [PlanComptableEcritureGroupesController::class, 'miseAJourMassive'])->name('plan_comptable_ecritures_groupes.miseAJourMassive');

    // *****************ROUTE GESTION DES ECRITURES COMPTABLE GROUPES VIA PLAN TIERS
    Route::get('/plan_tiers_ecritures_groupes', [PlanTiersEcritureGroupesController::class, 'index'])->name('plan_tiers_ecritures_groupes');
    Route::put('/plan_tiers_ecritures_groupes/{id}', [PlanTiersEcritureGroupesController::class, 'update'])->name('plan_tiers_ecritures_groupes.update');
    Route::post('/plan_tiers_ecritures_groupes', [PlanTiersEcritureGroupesController::class, 'miseAJourMassive'])->name('plan_tiers_ecritures_groupes.miseAJourMassive');

    // *****************ROUTE GESTION DES EXERCICES COMPTABLE
    Route::get('/exercice_comptable', [ExerciceComptableController::class, 'index'])->name('exercice_comptable');
    Route::get('/exercice_comptable/data', [ExerciceComptableController::class, 'getData'])->name('exercice_comptable.data');
    Route::get('/exercice_comptable/{exercice_comptable}', [ExerciceComptableController::class, 'show'])->name('exercice_comptable.show');
    Route::get('/exercice_comptable/{exercice_comptable}/edit', [ExerciceComptableController::class, 'edit'])->name('exercice_comptable.edit');
    Route::post('/exercice_comptable', [ExerciceComptableController::class, 'store'])->name('exercice_comptable.store');
    Route::put('/exercice_comptable/{exercice_comptable}', [ExerciceComptableController::class, 'update'])->name('exercice_comptable.update');
    Route::delete('/exercice_comptable/{exercice_comptable}', [ExerciceComptableController::class, 'destroy'])->name('exercice_comptable.destroy');
    Route::patch('/exercice_comptable/{exercice_comptable}', [ExerciceComptableController::class, 'cloturer'])->name('exercice_comptable.cloturer');
    Route::post('/exercice_comptable/{exercice_comptable}/activate', [ExerciceComptableController::class, 'activate'])->name('exercice_comptable.activate');

    // *****************ROUTE GESTION DES JOURNAUX DE SAISIS
    Route::get('/journaux_saisis', [JournauxSaisisController::class, 'index'])->name('journaux_saisis');
    Route::get('/journaux_saisis/find', [JournauxSaisisController::class, 'find'])->name('journaux_saisis.find');

    // *****************ROUTE GESTION DES GRANDS LIVRES
    Route::get('/accounting_ledger', [GrandLivreController::class, 'index'])->name('accounting_ledger');
    Route::post('accounting_ledger', [GrandLivreController::class, 'generateGrandLivre'])->name('accounting_ledger.generateGrandLivre');
    Route::post('accounting_ledger/previsualisation', [GrandLivreController::class, 'previewGrandLivre'])->name('accounting_ledger.previewGrandLivre');
    Route::delete('/accounting_ledger/{id}', [GrandLivreController::class, 'destroy'])->name('accounting_ledger.destroy');

    // *****************ROUTE GESTION DES GRANDS LIVRES DES TIERS
    Route::get('/accounting_ledger_tiers', [GrandLivreTiersController::class, 'index'])->name('accounting_ledger_tiers');
    Route::post('accounting_ledger_tiers', [GrandLivreTiersController::class, 'generateGrandLivre'])->name('accounting_ledger_tiers.generateGrandLivre');
    Route::post('accounting_ledger_tiers/previsualisation', [GrandLivreTiersController::class, 'previewGrandLivreTiers'])->name('accounting_ledger_tiers.previewGrandLivreTiers');
    Route::delete('/accounting_ledger_tiers/{id}', [GrandLivreTiersController::class, 'destroy'])->name('accounting_ledger_tiers.destroy');

    // *****************ROUTE GESTION DE LA BALANCE
    Route::get('/accounting_balance', [BalanceController::class, 'index'])->name('accounting_balance');
    Route::post('accounting_balance', [BalanceController::class, 'generateBalance'])->name('accounting_balance.generateBalance');
    Route::post('accounting_balance/previsualisation', [BalanceController::class, 'previewBalance'])->name('accounting_balance.previewBalance');
    Route::delete('/accounting_balance/{id}', [BalanceController::class, 'destroy'])->name('accounting_balance.destroy');

    // *****************ROUTE GESTION DE LA BALANCE DES TIERS
    Route::get('/accounting_balance_tiers', [BalanceTiersController::class, 'index'])->name('accounting_balance_tiers');
    Route::post('accounting_balance_tiers', [BalanceTiersController::class, 'generateBalance'])->name('accounting_balance_tiers.generateBalance');
    Route::post('accounting_balance_tiers/previsualisation', [BalanceTiersController::class, 'previewBalanceTiers'])->name('accounting_balance_tiers.previewBalanceTiers');
    Route::delete('/accounting_balance_tiers/{id}', [BalanceTiersController::class, 'destroy'])->name('accounting_balance_tiers.destroy');

    // *****************ROUTE GESTION DE LA TRESORERIE
    Route::get('/gestion_tresorerie', [GestionTresorerieController::class, 'index'])->name('gestion_tresorerie');
    Route::post('/gestion_tresorerie', [GestionTresorerieController::class, 'store'])->name('gestion_tresorerie.store');
    Route::put('/gestion_tresorerie', [GestionTresorerieController::class, 'update'])->name('gestion_tresorerie.update');
    Route::delete('/gestion_tresorerie', [GestionTresorerieController::class, 'destroy'])->name('gestion_tresorerie.destroy');

    // *****************ROUTE COMPTE-COMPTA
    Route::get('/compta-accounts', [ComptaAccountController::class, 'index'])->name('compta_accounts.index');
    Route::post('/compta-accounts', [ComptaAccountController::class, 'store'])->name('compta_accounts.store');
    Route::put('/compta-accounts/{id}', [ComptaAccountController::class,'update'])->name('compta_accounts.update');
    Route::delete('/compta-accounts/{id}', [ComptaAccountController::class, 'destroy'])->name('compta_accounts.destroy');
    Route::get('/access/company/{companyId}', [CompanyAccessController::class, 'accessCompany'])
        ->name('compta_accounts.access');
    Route::post('/leave/company', [CompanyAccessController::class, 'leaveCompany'])
        ->name('compta_accounts.leave');
Route::get('/dashboard-compta', [ComptaDashboardController::class, 'index'])->name('compta.dashboard')->middleware(['auth']);



    Route::get('/switch-to-account/{comptaAccountId}', [AccountingSwitchController::class, 'switchToAccount'])
         ->name('compta.switch');

    // Route pour effacer l'état du compte comptable et revenir à l'admin dashboard
    Route::get('/clear-account', [AccountingSwitchController::class, 'clearAccount'])
         ->name('compta.clear');







    // *****************ROUTE FLUX DE TRESORERIE
    Route::get('/flux_tresorerie', [FluxTresorerieController::class, 'index'])->name('flux_tresorerie');

    // Dashboard Admin (Performance par défaut)
    Route::get('/admin/dashboard', [App\Http\Controllers\Admin\PerformanceController::class, 'index'])->name('admin.dashboard');

    // Administration Avancée
    Route::prefix('admin')->name('admin.')->group(function() {
        // Approbations
        Route::get('/approvals', [App\Http\Controllers\Admin\ApprovalController::class, 'index'])->name('approvals');
        Route::post('/approvals/{id}/approve', [App\Http\Controllers\Admin\ApprovalController::class, 'approve'])->name('approvals.approve');
        Route::post('/approvals/{id}/reject', [App\Http\Controllers\Admin\ApprovalController::class, 'reject'])->name('approvals.reject');

        // Audit & Suivi
        Route::get('/audit', [App\Http\Controllers\Admin\AuditController::class, 'index'])->name('audit');
    Route::get('/audit/export', [App\Http\Controllers\Admin\AuditController::class, 'export'])->name('audit.export');
        
        // Contrôle d'Accès
        Route::get('/access-control', [App\Http\Controllers\Admin\AccessController::class, 'index'])->name('access');
        Route::post('/access/toggle-user/{id}', [App\Http\Controllers\Admin\AccessController::class, 'toggleUser'])->name('access.toggle_user');

        // Performance (Tableau de bord Admin)
        Route::get('/performance', [App\Http\Controllers\Admin\PerformanceController::class, 'index'])->name('performance');

        // Assignation de Tâches
        Route::get('/tasks', [App\Http\Controllers\Admin\TaskController::class, 'index'])->name('tasks');
        Route::post('/tasks', [App\Http\Controllers\Admin\TaskController::class, 'store'])->name('tasks.store');

        // Switch Comptabilité (Nouvelle Route)
        Route::get('/switch', [App\Http\Controllers\Admin\SwitchController::class, 'index'])->name('switch');

        // Configuration Entreprise (Modèles & Hub)
        Route::prefix('config')->name('config.')->group(function() {
            Route::get('/hub', [App\Http\Controllers\Admin\AdminConfigController::class, 'hub'])->name('hub');
            Route::get('/plan-comptable', [App\Http\Controllers\Admin\AdminConfigController::class, 'planComptable'])->name('plan_comptable');
            Route::get('/plan-tiers', [App\Http\Controllers\Admin\AdminConfigController::class, 'planTiers'])->name('plan_tiers');
            Route::get('/journals', [App\Http\Controllers\Admin\AdminConfigController::class, 'journals'])->name('journals');
            Route::get('/external-import', [App\Http\Controllers\Admin\AdminConfigController::class, 'externalImport'])->name('external_import');
            Route::post('/charge-imports', [App\Http\Controllers\Admin\AdminConfigController::class, 'chargeImports'])->name('charge_imports');
            Route::post('/update-settings', [App\Http\Controllers\Admin\AdminConfigController::class, 'updateSettings'])->name('update_settings');
            Route::post('/load-syscohada', [App\Http\Controllers\Admin\AdminConfigController::class, 'loadSyscohadaPlan'])->name('load_syscohada');
            Route::post('/load-syscohada-4', [App\Http\Controllers\Admin\AdminConfigController::class, 'loadSyscohada4'])->name('load_syscohada4');
            Route::post('/load-syscohada-6', [App\Http\Controllers\Admin\AdminConfigController::class, 'loadSyscohada6'])->name('load_syscohada6');
            Route::post('/load-syscohada-8', [App\Http\Controllers\Admin\AdminConfigController::class, 'loadSyscohada8'])->name('load_syscohada8');
            Route::post('/generate-custom-plan', [App\Http\Controllers\Admin\AdminConfigController::class, 'generateCustomPlan'])->name('generate_custom');
            
            Route::post('/reset-plan', [App\Http\Controllers\Admin\AdminConfigController::class, 'resetPlanComptable'])->name('reset_plan');
            Route::post('/reset-tiers', [App\Http\Controllers\Admin\AdminConfigController::class, 'resetPlanTiers'])->name('reset_tiers');
            Route::post('/reset-journals', [App\Http\Controllers\Admin\AdminConfigController::class, 'resetJournals'])->name('master_reset_journals');

            Route::post('/store-account', [App\Http\Controllers\Admin\AdminConfigController::class, 'storeAccount'])->name('store_account');
            Route::post('/import-accounts', [App\Http\Controllers\Admin\AdminConfigController::class, 'importAccounts'])->name('import_accounts');
            Route::put('/update-account/{id}', [App\Http\Controllers\Admin\AdminConfigController::class, 'updateAccount'])->name('update_account');
            Route::delete('/delete-account/{id}', [App\Http\Controllers\Admin\AdminConfigController::class, 'deleteAccount'])->name('delete_account');
            
            Route::post('/load-standard-journals', [App\Http\Controllers\Admin\AdminConfigController::class, 'loadStandardJournals'])->name('master_load_journals');
            Route::post('/store-tier', [App\Http\Controllers\Admin\AdminConfigController::class, 'storeTier'])->name('store_tier');
            Route::put('/update-tier/{id}', [App\Http\Controllers\Admin\AdminConfigController::class, 'updateTier'])->name('update_tier');
            Route::delete('/delete-tier/{id}', [App\Http\Controllers\Admin\AdminConfigController::class, 'deleteTier'])->name('delete_tier');
            // Journaux Master
            Route::post('/store-journal', [App\Http\Controllers\Admin\AdminConfigController::class, 'storeJournal'])->name('master_store_journal');
            Route::put('/update-journal/{id}', [App\Http\Controllers\Admin\AdminConfigController::class, 'updateJournal'])->name('master_update_journal');
            Route::delete('/delete-journal/{id}', [App\Http\Controllers\Admin\AdminConfigController::class, 'deleteJournal'])->name('master_delete_journal');
            
            Route::get('/get-next-tier', [App\Http\Controllers\Admin\AdminConfigController::class, 'getNextTierNumber'])->name('get_next_tier');

        });

    // Route de secours pour compatibilité (Evite Erreur 500)
    Route::get('/admin/export/hub', [App\Http\Controllers\Admin\AdminConfigController::class, 'exportHub'])->name('export.hub')->middleware(['auth']);

        // --- MODULE EXPORTATION (DÉPLACÉ HORS CONFIG) ---
        Route::get('/export/hub', [App\Http\Controllers\Admin\AdminConfigController::class, 'exportHub'])->name('export.hub');
        Route::post('/export/process', [App\Http\Controllers\Admin\AdminConfigController::class, 'exportProcess'])->name('export.process');

        // --- NOUVEAUX TUNNEL D'IMPORTATION (DÉPLACÉ HORS CONFIG) ---
        Route::get('/import/hub', [App\Http\Controllers\Admin\AdminConfigController::class, 'importHub'])->name('import.hub');
        Route::post('/import/upload', [App\Http\Controllers\Admin\AdminConfigController::class, 'importUpload'])->name('import.upload');
        Route::get('/import/mapping/{id}', [App\Http\Controllers\Admin\AdminConfigController::class, 'importMapping'])->name('import.mapping');
        Route::post('/import/process-mapping/{id}', [App\Http\Controllers\Admin\AdminConfigController::class, 'processMapping'])->name('import.process_mapping');
        Route::get('/import/staging/{id}', [App\Http\Controllers\Admin\AdminConfigController::class, 'importStaging'])->name('import.staging');
        Route::post('/import/commit/{id}', [App\Http\Controllers\Admin\AdminConfigController::class, 'commitImport'])->name('import.commit');
        Route::delete('/import/cancel/{id}', [App\Http\Controllers\Admin\AdminConfigController::class, 'cancelImport'])->name('import.cancel');
        Route::post('/import/quick-account', [App\Http\Controllers\Admin\AdminConfigController::class, 'quickAccountCreate'])->name('import.quick_account');
        Route::post('/import/update-row/{id}/{index}', [App\Http\Controllers\Admin\AdminConfigController::class, 'updateRow'])->name('import.update_row');
        Route::delete('/import/delete-row/{id}/{index}', [App\Http\Controllers\Admin\AdminConfigController::class, 'deleteRow'])->name('import.delete_row');
    });

    // Dashboard Comptable
    Route::get('/comptable/dashboard', function () {
        return view('comptable.comptdashboard');
    })->name('comptable.comptdashboard');


    Route::get('/comptable/dashboard', [ComptaDashboardController::class, 'index'])->name('comptable.comptdashboard');






        // Routes Tresorerie (ressource complète)
// 1. Routes Statiques Spécifiques (ex: /tresorerie/plan, /tresorerie/create, /tresorerie/store, /journal-tresorerie/defaut)

Route::get('/plan/poste', [PosteTresorController::class, 'generateCashFlowPlan'])->name('generate_cash_flow_plan');
Route::get('/previsualisation/poste', [PosteTresorController::class, 'previewCashFlowPdf'])->name('preview_cash_flow_pdf');
Route::get('/pdf/poste', [PosteTresorController::class, 'generatePdf'])
    ->name('generate_cash_flow_pdf');
Route::get('/tresorerie/create', [TresorerieController::class, 'create'])->name('createtresorerie');
Route::post('/tresorerie/store', [TresorerieController::class, 'store'])->name('storetresorerie');
Route::post('/journal-tresorerie/defaut', [TresorerieController::class, 'loadDefaultTresorerie'])->name('journal_tresorerie.defaut');
Route::put('/postetresorerie/{id}', [PosteTresorController::class, 'update'])->name('postetresorerie.update.id');





// La route pour générer le plan doit être maintenue car elle gère l'affichage initial du tableau HTML

// Garder la route d'export existante
Route::get('/tresorerie/plan/export-csv', [TresorerieController::class, 'exportCashFlowCsv'])->name('export_cash_flow_csv');




// 2. Route Index (Racine)

// Rediriger l'ancienne page trésorerie vers la page journaux unifiée
Route::get('/tresorerie', function() {
    return redirect()->route('accounting_journals.index');
})->name('indextresorerie');

// 3. Routes Paramétrées (celles qui ont /{id}) - DOIVENT être à la fin
Route::get('/tresorerie/{id}', [TresorerieController::class, 'show'])->name('showtresorerie');
Route::get('/tresorerie/{id}/edit', [TresorerieController::class, 'edit'])->name('editresorerie');
Route::put('/tresorerie/{id}', [TresorerieController::class, 'update'])->name('update_tresorerie');
Route::delete('/tresorerie/{id}', [TresorerieController::class, 'destroy'])->name('destroy_tresorerie');


// ROUTE DE POSTE DE TRESORERIE
Route::get('/poste',[PosteTresorController::class, 'index'])->name('postetresorerie.index');

// Affiche le formulaire de création d'un NOUVEAU POSTE de trésorerie
Route::get('/poste/create', [PosteTresorController::class, 'create'])->name('postetresorerie.create');

// Affiche le détail des mouvements pour un compte spécifique (Journal)
Route::get('/poste/{compte}', [PosteTresorController::class, 'show'])->name('postetresorerie.show');

// Traite la soumission du formulaire et enregistre le NOUVEAU POSTE (CompteTresorerie)
Route::post('/poste/creer', [PosteTresorController::class, 'storeCompteTresorerie'])->name('postetresorerie.store_poste');

// Traite la soumission du formulaire et enregistre le NOUVEAU MOUVEMENT (MouvementTresorerie)
Route::post('/mouvement/store', [PosteTresorController::class, 'storeMouvement'])->name('mouvement.store');

// L'ancienne route 'Route::post('/poste', [PosteTresorController::class, 'store'])->name('postetresorerie.store');' est supprimée.
Route::put('/poste/{compte}', [PosteTresorController::class, 'update'])->name('postetresorerie.update');



Route::get('/plan-comptable/datatable', [PlanComptableController::class, 'datatable'])->name('plan_comptable.datatable');



    // Route compte
    Route::get('/comptes/creer', [compteController::class, 'index'])->name('creer_compte.index');
    Route::post('/comptes/store', [compteController::class, 'store'])->name('creer_compte.store');


    // Assurez-vous que cette ligne existe si vous utilisez 'companies.store'
    Route::get('/companies', [CompanyController::class, 'index'])->name('companies');
    // Route::post('/companies/store', [CompanyController::class, 'store'])->name('companies.store');
    Route::post('/admin/companies/store', [CompanyController::class, 'adminStoreCompany'])->name('admin.store_sub_company');
    Route::get('/switch-company/{company_id}', [UserController::class, 'switchCompany'])->name('switch_company');
});

Route::middleware(['auth'])->group(function () {
    // Tâches
    Route::prefix('admin')->name('admin.')->group(function () {
        // Tâches
        Route::get('/tasks/assign', [App\Http\Controllers\Admin\TaskController::class, 'index'])->name('tasks.index'); // Assigner Tâche
        Route::post('/tasks/store', [App\Http\Controllers\Admin\TaskController::class, 'store'])->name('tasks.store');
        Route::get('/tasks/daily', [App\Http\Controllers\Admin\TaskController::class, 'dailyTasks'])->name('tasks.daily'); // Tâche Quotidienne
        Route::patch('/tasks/{task}/complete', [App\Http\Controllers\Admin\TaskController::class, 'markAsCompleted'])->name('tasks.complete');

        // Habilitations (Gouvernance)
        Route::get('/habilitations', [App\Http\Controllers\Admin\HabilitationController::class, 'index'])->name('habilitations.index');
        Route::put('/habilitations/{user}', [App\Http\Controllers\Admin\HabilitationController::class, 'update'])->name('habilitations.update');
    });

    // Routes de context switching pour l'Admin
    Route::get('/admin/context/reset', [UserController::class, 'resetContext'])->name('admin.context.reset');

    // Laissez votre route d'impersonation ici (avec son nom existant)
    Route::get('/impersonate/leave', [UserController::class, 'leaveImpersonation'])->name('admin.leave_impersonation');
    Route::get('/impersonate/{user}', [UserController::class, 'impersonate'])->name('admin.impersonate');

    // Routes de création d'utilisateurs pour l'Admin
    Route::get('/admin/users/create', [UserController::class, 'create'])->name('admin.users.create');
    Route::post('/users', [UserController::class, 'store'])->name('users.store');

    // Routes de création d'administrateurs pour l'Admin (Style SuperAdmin)
    Route::get('/admin/admins/create', [UserController::class, 'createAdmin'])->name('admin.admins.create');
    Route::post('/admin/admins', [UserController::class, 'storeAdmin'])->name('admin.admins.store');

    Route::get('/admin/secondary-admins/create', [UserController::class, 'createSecondaryAdmin'])->name('admin.secondary_admins.create');
    Route::post('/admin/secondary-admins/store', [UserController::class, 'storeSecondaryAdmin'])->name('admin.secondary_admins.store');
    
    // Routes de création de sous-entreprises pour l'Admin
    Route::get('/admin/companies/create-entity', [CompanyController::class, 'adminCreateCompany'])->name('admin.companies.create');
    Route::post('/admin/companies/store-entity', [CompanyController::class, 'adminStoreCompany'])->name('admin.companies.store');

    // Routes de création de comptabilité (Style SuperAdmin) - Exercices
    Route::get('/admin/companies/create', [ComptaAccountController::class, 'create'])->name('compta.create');
    Route::post('/admin/companies', [ComptaAccountController::class, 'storeExercice'])->name('compta.store');
});

// **********************************************
// ROUTES SUPER ADMIN (MIDDLEWARE 'superadmin')
// **********************************************

Route::middleware(['auth',authSuperAdminMiddleware::class])->group(function () {

    // Tableau de Bord Super Admin (Statistiques Globales)
    Route::get('/superadmin/dashboard', [SuperAdminDashboardController::class, 'index'])->name('superadmin.dashboard');

    // Gestion des Entités (Ancien Dashboard)
    Route::get('/entities', [SuperAdminDashboardController::class, 'entities'])->name('superadmin.entities');

    // Gestion des Entreprises
    Route::get('/superadmin/companies/create', [\App\Http\Controllers\Super\SuperAdminCompanyController::class, 'create'])->name('superadmin.companies.create');
    Route::post('/superadmin/companies', [\App\Http\Controllers\Super\SuperAdminCompanyController::class, 'store'])->name('superadmin.companies.store');
    Route::get('/superadmin/companies/{id}/edit', [\App\Http\Controllers\Super\SuperAdminCompanyController::class, 'edit'])->name('superadmin.companies.edit');
    Route::put('/{company}/toggle', [SuperAdminCompanyController::class, 'toggleStatus'])->name('toggle');
    Route::put('/companies/{company}/update', [SuperAdminCompanyController::class, 'update'])->name('superadmin.companies.update');

    // Gestion des Comptabilités
    Route::get('/superadmin/accounting', [\App\Http\Controllers\Super\SuperAdminComptaController::class, 'index'])->name('superadmin.accounting.index');
    Route::get('/superadmin/accounting/create', [\App\Http\Controllers\Super\SuperAdminComptaController::class, 'create'])->name('superadmin.accounting.create');
    Route::post('/superadmin/accounting', [\App\Http\Controllers\Super\SuperAdminComptaController::class, 'store'])->name('superadmin.accounting.store');
    Route::get('/superadmin/accounting/{id}/edit', [\App\Http\Controllers\Super\SuperAdminComptaController::class, 'edit'])->name('superadmin.accounting.edit');
    Route::put('/superadmin/accounting/{id}', [\App\Http\Controllers\Super\SuperAdminComptaController::class, 'update'])->name('superadmin.accounting.update');
    Route::delete('/superadmin/accounting/{id}', [\App\Http\Controllers\Super\SuperAdminComptaController::class, 'destroy'])->name('superadmin.accounting.destroy');

    // Administration des Utilisateurs
    Route::get('/superadmin/users', [\App\Http\Controllers\Super\SuperAdminUserController::class, 'index'])->name('superadmin.users');
    Route::get('/superadmin/users/create', [\App\Http\Controllers\Super\SuperAdminUserController::class, 'create'])->name('superadmin.users.create');
    Route::post('/superadmin/users', [\App\Http\Controllers\Super\SuperAdminUserController::class, 'store'])->name('superadmin.users.store');
    Route::get('/superadmin/users/{id}/edit', [\App\Http\Controllers\Super\SuperAdminUserController::class, 'edit'])->name('superadmin.users.edit');
    Route::put('/superadmin/users/{id}', [\App\Http\Controllers\Super\SuperAdminUserController::class, 'update'])->name('superadmin.users.update');
    Route::delete('/superadmin/users/{id}', [\App\Http\Controllers\Super\SuperAdminUserController::class, 'destroy'])->name('superadmin.users.destroy');

    // Administration des Administrateurs (Dédié)
    Route::get('/superadmin/admins/create', [\App\Http\Controllers\Super\SuperAdminUserController::class, 'createAdmin'])->name('superadmin.admins.create');
    Route::post('/superadmin/admins', [\App\Http\Controllers\Super\SuperAdminUserController::class, 'storeAdmin'])->name('superadmin.admins.store');

    // Suivi des Activités
    Route::get('/superadmin/activities', [\App\Http\Controllers\Super\SuperAdminActivityController::class, 'index'])->name('superadmin.activities');

    // Rapports de Performance
    Route::get('/superadmin/reports', [\App\Http\Controllers\Super\SuperAdminReportController::class, 'index'])->name('superadmin.reports');

    // Switch Entreprise/Utilisateur
    Route::get('/superadmin/switch', [\App\Http\Controllers\Super\SuperAdminSwitchController::class, 'index'])->name('superadmin.switch');
    Route::post('/superadmin/switch/company/{id}', [\App\Http\Controllers\Super\SuperAdminSwitchController::class, 'switchToCompany'])->name('superadmin.switch.company');
    Route::post('/superadmin/switch/user/{id}', [\App\Http\Controllers\Super\SuperAdminSwitchController::class, 'switchToUser'])->name('superadmin.switch.user');

    // Contrôle d'Accès (Blocage/Déblocage)
    Route::get('/superadmin/access-control', [\App\Http\Controllers\Super\SuperAdminAccessController::class, 'index'])->name('superadmin.access');
    Route::post('/superadmin/access/block-company/{id}', [\App\Http\Controllers\Super\SuperAdminAccessController::class, 'blockCompany'])->name('superadmin.access.block.company');
    Route::post('/superadmin/access/unblock-company/{id}', [\App\Http\Controllers\Super\SuperAdminAccessController::class, 'unblockCompany'])->name('superadmin.access.unblock.company');
    Route::post('/superadmin/access/block-user/{id}', [\App\Http\Controllers\Super\SuperAdminAccessController::class, 'blockUser'])->name('superadmin.access.block.user');
    Route::post('/superadmin/access/unblock-user/{id}', [\App\Http\Controllers\Super\SuperAdminAccessController::class, 'unblockUser'])->name('superadmin.access.unblock.user');
    Route::delete('/superadmin/access/company/{id}', [\App\Http\Controllers\Super\SuperAdminAccessController::class, 'destroyCompany'])->name('superadmin.access.destroy.company');
    Route::delete('/superadmin/access/user/{id}', [\App\Http\Controllers\Super\SuperAdminAccessController::class, 'destroyUser'])->name('superadmin.access.destroy.user');

    // Gestion des Packs/Abonnements
    Route::get('/activation', [SubscriptionController::class, 'showPricing'])->name('pricing.show');

    // Route::resource('companies', SuperAdminCompanyController::class)->except(['index', 'show']);
    Route::resource('companies', SuperAdminCompanyController::class);
    // Page de configuration avancée*

    Route::get('/advanced-settings', [SuperAdminSetupController::class, 'index'])->name('settings');

    // Route pour la création de sous-compagnies par l'Admin
    Route::post('/admin/companies/store', [CompanyController::class, 'adminStoreCompany'])->name('admin.store_sub_company');
    Route::get('/switch-company/{company_id}', [UserController::class, 'switchCompany'])->name('switch_company');
});


