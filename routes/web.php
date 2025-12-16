<?php

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
use App\Http\Controllers\Tresoreriecontro\TresorerieController;
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





















// **********************************************
// ROUTES PUBLIQUES / AUTHENTIFICATION
// **********************************************

Route::get('/login', function () {
    return view('login');
})->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');

// DEBUG ROUTE - TO DELETE LATER
Route::get('/debug-comptes', function () {
    $user = auth()->user();
    if (!$user) return 'Not logged in';
    
    $counts = \App\Models\PlanComptable::where('company_id', $user->company_id)
        ->selectRaw('LEFT(numero_de_compte, 1) as classe, count(*) as count')
        ->groupBy('classe')
        ->orderBy('classe')
        ->get();
        
    return response()->json($counts);
});

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
})->name('dashboard');

// require __DIR__ . '/super_admin.php';
// **********************************************
// ROUTES PROTÉGÉES (MIDDLEWARE 'auth')
// **********************************************

Route::middleware('auth')->group(function () {
    // Déconnexion
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Pages principales
    Route::get('/', function () { return view('index'); })->name('index');
    Route::get('/index', function () { return view('index'); })->name('index_page');
    Route::get('/accounting_balance', function () { return view('accounting_balance'); })->name('accounting_balance');
    Route::get('/accounting_entry', function () { return view('accounting_entry'); })->name('accounting_entry');
    Route::get('/file_management', function () { return view('file_management'); })->name('file_management');
    Route::get('/financial_statements', function () { return view('financial_statements'); })->name('financial_statements');

    // *****************ROUTE GESTION DE COMPANY
    Route::get('/compagny_information', [CompanyController::class, 'index'])->name('compagny_information');
    Route::put('/compagny_information/{company}', [CompanyController::class, 'update'])->name('compagny_information.update');

    // *****************ROUTE GESTION DES USERS
    Route::post('/users', [UserController::class, 'store'])->name('users.store');
    Route::put('/users/{id}', [UserController::class, 'update'])->name('users.update');
    Route::delete('/users/{id}', [UserController::class, 'destroy'])->name('users.destroy');
    Route::get('/user_management', [UserController::class, 'stat_online'])->name('user_management');
    Route::get('/users', [UserController::class, 'index'])->name('users.index');






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
    Route::get('/plan_tiers/{racine}', [PlanTiersController::class, 'getDernierNumero'])->name('getDernierNumero');
    Route::post('/plan_tiers', [PlanTiersController::class, 'store'])->name('plan_tiers.store');
    Route::put('/plan_tiers/{id}', [PlanTiersController::class, 'update'])->name('plan_tiers.update');
    Route::delete('/plan_tiers/{id}', [PlanTiersController::class, 'destroy'])->name('plan_tiers.destroy');

    // *****************ROUTE GESTION DES CODE JOURNAL
    Route::get('/accounting_journals', [CodeJournalController::class, 'index'])->name('accounting_journals');
    Route::post('/accounting_journals', [CodeJournalController::class, 'store'])->name('accounting_journals.store');
    Route::put('/accounting_journals/{id}', [CodeJournalController::class, 'update'])->name('accounting_journals.update');
    Route::delete('/accounting_journals/{id}', [CodeJournalController::class, 'destroy'])->name('accounting_journals.destroy');

    // *****************ROUTE GESTION DES ECRITURES COMPTABLE
    Route::get('/accounting_entry_real', [EcritureComptableController::class, 'index'])->name('accounting_entry_real');
    Route::get('/api/comptes-par-flux', [EcritureComptableController::class, 'getComptesParFlux'])->name('api.comptes_par_flux'); // NEW AJAX ROUTE
    Route::post('/accounting_entry_real', [EcritureComptableController::class, 'storeMultiple'])->name('storeMultiple.storeMultiple');
    Route::get('/saisie-directe-modal', [EcritureComptableController::class, 'showSaisieModal'])->name('modal_saisie_direct');

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
    Route::post('/exercice_comptable', [ExerciceComptableController::class, 'store'])->name('exercice_comptable.store');
    Route::delete('/exercice_comptable/{id}', [ExerciceComptableController::class, 'destroy'])->name('exercice_comptable.destroy');
    Route::patch('/exercice_comptable/{id}', [ExerciceComptableController::class, 'cloturer'])->name('exercice_comptable.cloturer');

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
    Route::put('/{id},compta-accounts', [ComptaAccountController::class,'update'])->name('compta_accounts.update');
    Route::delete('/{id},compta-accounts', [ComptaAccountController::class, 'destroy'])->name('compta_accounts.destroy');
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

    // *****************ROUTES PROFIL ET PARAMÈTRES UTILISATEUR
    Route::get('/profile', [UserController::class, 'showProfile'])->name('profile');
    Route::get('/settings', [UserController::class, 'settings'])->name('settings');
    Route::put('/settings/account', [UserController::class, 'updateAccount'])->name('settings.account');
    Route::put('/settings/password', [UserController::class, 'updatePassword'])->name('settings.password');
    Route::post('/settings/avatar', [UserController::class, 'updateAvatar'])->name('settings.avatar');
    Route::get('/my-dashboard', [UserController::class, 'personalDashboard'])->name('personal.dashboard');

    // Dashboard Admin
    Route::get('/admin/dashboards', [UserController::class, 'dashboardStats'])->name('admin.dashboard');

    // Dashboard Comptable
    Route::get('/comptable/dashboard', function () {
        return view('comptable.comptdashboard');
    })->name('comptable.comptdashboard');


    Route::get('/comptable/dashboard', [ComptaDashboardController::class, 'index'])->name('comptable.comptdashboard');




// Route pour afficher la vue de sélection des packs
        Route::get('/packs/subscription', [SubscriptionController::class, 'showPricing'])->name('pricing.show');


        // Routes Tresorerie (ressource complète)
// 1. Routes Statiques Spécifiques (ex: /tresorerie/plan, /tresorerie/create, /tresorerie/store, /journal-tresorerie/defaut)

Route::get('/tresorerie/plan', [TresorerieController::class, 'generateCashFlowPlan'])->name('generate_cash_flow_plan');
Route::get('/tresorerie/previsualisation', [TresorerieController::class, 'previewCashFlowPdf'])->name('preview_cash_flow_pdf');
Route::get('/tresorerie/pdf', [TresorerieController::class, 'generatePdf'])
    ->name('generate_cash_flow_pdf');
Route::get('/tresorerie/create', [TresorerieController::class, 'create'])->name('createtresorerie');
Route::post('/tresorerie/store', [TresorerieController::class, 'store'])->name('storetresorerie');
Route::post('/journal-tresorerie/defaut', [TresorerieController::class, 'loadDefaultTresorerie'])->name('journal_tresorerie.defaut');
Route::get('/tresorerie/plan', [TresorerieController::class, 'generateCashFlowPlan'])
    ->name('generate_cash_flow_plan');





// La route pour générer le plan doit être maintenue car elle gère l'affichage initial du tableau HTML

// Garder la route d'export existante
Route::get('/tresorerie/plan/export-csv', [TresorerieController::class, 'exportCashFlowCsv'])->name('export_cash_flow_csv');




// 2. Route Index (Racine)

Route::get('/tresorerie', [TresorerieController::class, 'index'])->name('indextresorerie');

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



});

    // Route compte
    Route::get('/comptes/creer', [compteController::class, 'index'])->name('creer_compte.index');
    Route::post('/comptes/store', [compteController::class, 'store'])->name('creer_compte.store');


    // Assurez-vous que cette ligne existe si vous utilisez 'companies.store'
    Route::get('/companies', [CompanyController::class, 'index'])->name('companies');
    // Route::post('/companies/store', [CompanyController::class, 'store'])->name('companies.store');


Route::middleware(['auth'])->group(function () {
    // Laissez votre route d'impersonation ici (avec son nom existant)
    Route::get('/impersonate/{user}', [UserController::class, 'impersonate'])->name('admin.impersonate');
    Route::get('/impersonate/leave', [UserController::class, 'leaveImpersonation'])->name('admin.leave_impersonation');
});

// **********************************************
// ROUTES SUPER ADMIN (MIDDLEWARE 'superadmin')
// **********************************************

Route::middleware(['auth',authSuperAdminMiddleware::class])->group(function () {

    // Tableau de Bord Super Admin
    Route::get('/dashboard', [SuperAdminDashboardController::class, 'index'])->name('superadmin.dashboard');


     Route::put('/{company}/toggle', [SuperAdminCompanyController::class, 'toggleStatus'])->name('toggle');


    // Route::resource('companies', SuperAdminCompanyController::class)->except(['index', 'show']);
    Route::resource('companies', SuperAdminCompanyController::class);
    // Page de configuration avancée
    Route::get('/advanced-settings', [SuperAdminSetupController::class, 'index'])->name('settings');

    // Route pour la création de sous-compagnies par l'Admin
    Route::post('/admin/companies/store', [CompanyController::class, 'adminStoreCompany'])->name('admin.store_sub_company');
    Route::get('/switch-company/{company_id}', [UserController::class, 'switchCompany'])->name('switch_company');
});


