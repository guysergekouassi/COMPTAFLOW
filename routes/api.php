<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\AccountingController;
use App\Http\Controllers\Api\EntryController;
use App\Http\Controllers\Api\ReportController;

use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\TaskController;
use App\Http\Controllers\Api\AnalytiqueController;
use App\Http\Controllers\Api\ImmoController;
use App\Http\Controllers\Api\LettrageController;
use App\Http\Controllers\Api\ExerciceController;
use App\Http\Controllers\Api\ApprovalController;
use App\Http\Controllers\Api\ScanController;


Route::prefix('v1')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/exercices/by-email/{email}', [ExerciceController::class, 'getByEmail']);

    // Route spéciale pour l'Agent IA (Python)
    Route::post('/agent/execute', [\App\Http\Controllers\AiAgentController::class, 'executeAction']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/user', function (Request $request) {
            return $request->user();
        });
        
        Route::get('/dashboard', [DashboardController::class, 'index']);
        Route::get('/dashboard/chat-history/{contactId}', [DashboardController::class, 'chatHistory']);
        
        // Approbations / Validation
        Route::get('/approvals', [ApprovalController::class, 'index']);
        Route::post('/approvals/{id}/handle', [ApprovalController::class, 'handle']);

        // Notifications
        Route::get('/notifications', [NotificationController::class, 'index']);
        Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount']);
        Route::post('/notifications/{id}/mark-as-read', [NotificationController::class, 'markAsRead']);
        Route::post('/notifications', [NotificationController::class, 'store']);

        // Tâches
        Route::get('/tasks', [TaskController::class, 'index']);
        Route::get('/tasks/daily', [TaskController::class, 'dailyTasks']);
        Route::post('/tasks', [TaskController::class, 'store']);
        Route::post('/tasks/{id}/complete', [TaskController::class, 'markAsCompleted']);
        Route::delete('/tasks/{id}', [TaskController::class, 'destroy']);

        // Exercices
        Route::get('/exercices', [ExerciceController::class, 'index']);
        Route::get('/exercices/active', [ExerciceController::class, 'showActive']);

        // Analytique
        Route::get('/analytique/axes', [AnalytiqueController::class, 'axeIndex']);
        Route::post('/analytique/axes', [AnalytiqueController::class, 'axeStore']);
        Route::get('/analytique/sections', [AnalytiqueController::class, 'sectionIndex']);
        Route::post('/analytique/sections', [AnalytiqueController::class, 'sectionStore']);
        Route::get('/analytique/ventilations', [AnalytiqueController::class, 'ventilationIndex']);

        // Immobilisations
        Route::get('/immobilisations', [ImmoController::class, 'index']);
        Route::get('/immobilisations/{id}', [ImmoController::class, 'show']);
        Route::get('/immobilisations/{id}/amortissements', [ImmoController::class, 'amortissementIndex']);

        // Lettrage
        Route::get('/lettrage', [LettrageController::class, 'index']);
        Route::post('/lettrage', [LettrageController::class, 'store']);

        // Plan Comptable
        Route::get('/accounting/plan-comptable', [AccountingController::class, 'planComptableIndex']);
        Route::post('/accounting/plan-comptable', [AccountingController::class, 'planComptableStore']);
        
        // Plan Tiers
        Route::get('/accounting/plan-tiers', [AccountingController::class, 'planTiersIndex']);
        Route::post('/accounting/plan-tiers', [AccountingController::class, 'planTiersStore']);
        
        // Codes Journaux
        Route::get('/accounting/journals', [AccountingController::class, 'journalsIndex']);
        Route::post('/accounting/journals', [AccountingController::class, 'journalsStore']);

        // Postes de Trésorerie
        Route::get('/accounting/treasury-categories', [AccountingController::class, 'treasuryCategoriesIndex']);
        Route::post('/accounting/treasury-categories', [AccountingController::class, 'treasuryCategoriesStore']);
        Route::get('/accounting/treasury-posts', [AccountingController::class, 'treasuryPostsIndex']);
        Route::post('/accounting/treasury-posts', [AccountingController::class, 'treasuryPostsStore']);
        
        // Écritures et Scan
        Route::get('/entries/rejected', [EntryController::class, 'indexRejetes']);
        Route::get('/entries/drafts', [EntryController::class, 'indexBrouillons']);
        Route::post('/entries/multiple', [EntryController::class, 'storeMultiple']);
        Route::get('/entries', [EntryController::class, 'index']);
        Route::post('/entries', [EntryController::class, 'store']);
        Route::get('/entries/{n_saisie}', [EntryController::class, 'loadBySaisie']);
        Route::delete('/entries/{n_saisie}', [EntryController::class, 'destroy']);
        Route::post('/scan', [EntryController::class, 'scan']);
        
        // Nouveau Scan par lot Mobile
        Route::get('/scan/context', [ScanController::class, 'getContext']);
        Route::post('/scan/upload', [ScanController::class, 'upload']);
        Route::post('/scan/batch-store', [ScanController::class, 'storeBatch']);

        
        // Rapports financiers
        Route::get('/reports/balance', [ReportController::class, 'balance']);
        Route::get('/reports/grand-livre', [ReportController::class, 'grandLivre']);
        Route::get('/reports/bilan', [ReportController::class, 'bilan']);
        Route::get('/reports/resultat', [ReportController::class, 'resultat']);
        Route::get('/reports/resultat/monthly', [ReportController::class, 'monthlyResultat']);
        Route::get('/reports/tft', [ReportController::class, 'tft']);
        Route::get('/reports/tft/monthly', [ReportController::class, 'monthlyTft']);
        Route::get('/reports/tft/personalized', [ReportController::class, 'personalizedTft']);
        Route::get('/reports/analytique/balance', [ReportController::class, 'balanceAnalytique']);
        Route::get('/reports/analytique/grand-livre', [ReportController::class, 'grandLivreAnalytique']);
        Route::get('/reports/analytique/resultat', [ReportController::class, 'resultatAnalytique']);
        
        // Analytique - Paramétrage
        Route::get('/analytique/rules', [AnalytiqueController::class, 'ruleIndex']);
        Route::post('/analytique/rules', [AnalytiqueController::class, 'ruleStore']);
        
        // Suppression générique
        Route::delete('/accounting/{type}/{id}', [AccountingController::class, 'destroy']);

        // ── Honoraires & Abonnements (Super Admin Mobile) ──────────────────────
        Route::get('/honoraires', [\App\Http\Controllers\Super\HonorairesController::class, 'apiIndex']);
        Route::get('/honoraires/{companyId}', [\App\Http\Controllers\Super\HonorairesController::class, 'apiShow']);

        Route::post('/logout', [AuthController::class, 'logout']);
    });
});

// ═══════════════════════════════════════════════════════════════════════════
// Routes de synchronisation externe (Selflow ↔ COMPTAFLOW)
//
// Le secret serveur `EXTERNAL_SYNC_SECRET` dit que l'appel vient de Selflow ;
// il ne dit pas **quelle entreprise** appelle. C'était le corps de la requête
// qui l'annonçait, et Comptaflow le croyait sur parole : quiconque détenait le
// secret pouvait écrire dans les livres de n'importe quelle entreprise en
// changeant un entier dans un JSON.
//
// Chaque dossier porte désormais sa propre clé, présentée en en-tête
// `X-Company-Key` et vérifiée par `cle.entreprise`, qui refuse une clé
// désignant un autre dossier que celui annoncé dans le corps.
// ═══════════════════════════════════════════════════════════════════════════
Route::prefix('external')->group(function () {
    // ── Le cycle de vie de la liaison ──
    //
    // `provision` est le seul appel sans `cle.entreprise` : il n'y a pas encore
    // de clé à présenter, puisque c'est lui qui la génère. D'où la limitation
    // de débit serrée — c'est aussi le seul qui crée des dossiers.
    Route::post('/companies/provision', [\App\Http\Controllers\Api\ExternalCompanyController::class, 'provision'])
        ->middleware('throttle:6,1')
        ->name('api.external.companies.provision');
    Route::post('/companies/revoke', [\App\Http\Controllers\Api\ExternalCompanyController::class, 'revoke'])
        ->middleware(['cle.entreprise', 'throttle:20,1'])
        ->name('api.external.companies.revoke');
    Route::post('/companies/verify', [\App\Http\Controllers\Api\ExternalCompanyController::class, 'verify'])
        ->middleware(['cle.entreprise', 'throttle:60,1'])
        ->name('api.external.companies.verify');

    // ── Les deux déversements ──
    //
    // C'est ici que la clé sert vraiment : sans `cle.entreprise` sur ces deux
    // routes, tout le reste ne sert à rien.
    Route::post('/ecritures/deverser', [\App\Http\Controllers\Api\ExternalSyncController::class, 'deverserEcritures'])
        ->middleware('cle.entreprise')
        ->name('api.external.ecritures.deverser');
    // Selflow déverse son référentiel — plan comptable, journaux, tiers — dans
    // l'entreprise Comptaflow qui lui est liée. Sens unique : rien ne repart.
    Route::post('/referentiel/deverser', [\App\Http\Controllers\Api\ExternalSyncController::class, 'deverserReferentiel'])
        ->middleware('cle.entreprise')
        ->name('api.external.referentiel.deverser');

    // ── Le reste de la passerelle ──
    //
    // `register-enterprise` a été retirée : `companies/provision` la remplace,
    // plus rien ne l'appelait depuis Selflow dans ce sens, et elle exigeait un
    // `admin_password` transporté en clair dans le corps de la requête.
    //
    // ⚠️ Ne pas confondre avec la route **homonyme de Selflow** : Comptaflow
    // l'appelle toujours pour créer une entreprise *chez Selflow*
    // (SuperAdminCompanyController, SuperAdminLiaisonController). Les deux
    // portent le même chemin de chaque côté de la passerelle ; celle-là reste.
    Route::get('/status', [\App\Http\Controllers\Api\ExternalSyncController::class, 'syncStatus'])
        ->name('api.external.sync-status');
    Route::post('/link-company', [\App\Http\Controllers\Api\ExternalSyncController::class, 'linkCompany'])
        ->name('api.external.link-company');
    Route::post('/list-companies', [\App\Http\Controllers\Api\ExternalSyncController::class, 'listCompanies'])
        ->name('api.external.list-companies');
    Route::post('/company-info', [\App\Http\Controllers\Api\ExternalSyncController::class, 'companyInfo'])
        ->name('api.external.company-info');
});

// --- Ajouté automatiquement pour le FlowHub ---
use App\Http\Controllers\Api\DashboardApiController;

Route::middleware(['api', 'verify.hub.token'])->prefix('dashboard')->group(function () {
    Route::get('/kpis', [DashboardApiController::class, 'kpis'])->name('api.dashboard.kpis');
});

Route::middleware(['api', 'verify.hub.token'])->group(function () {
    Route::get('/companies', [DashboardApiController::class, 'companies'])->name('api.companies');
});

Route::get('/ping', function () {
    return response()->json(['status' => 'ok', 'service' => 'ComptaFlow API']);
})->name('api.ping');
// ----------------------------------------------




require __DIR__ . '/api_hub.php';
