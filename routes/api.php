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

Route::prefix('v1')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/exercices/by-email/{email}', [ExerciceController::class, 'getByEmail']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/user', function (Request $request) {
            return $request->user();
        });
        
        Route::get('/dashboard', [DashboardController::class, 'index']);
        
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

        Route::post('/logout', [AuthController::class, 'logout']);
    });
});
