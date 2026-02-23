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

Route::prefix('v1')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/user', function (Request $request) {
            return $request->user();
        });
        
        Route::get('/dashboard', [DashboardController::class, 'index']);
        
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

        // Immobilisations
        Route::get('/immobilisations', [ImmoController::class, 'index']);
        Route::get('/immobilisations/{id}', [ImmoController::class, 'show']);

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
        Route::get('/entries', [EntryController::class, 'index']);
        Route::get('/entries/rejected', [EntryController::class, 'indexRejetes']);
        Route::get('/entries/drafts', [EntryController::class, 'indexBrouillons']);
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
        
        // Suppression générique
        Route::delete('/accounting/{type}/{id}', [AccountingController::class, 'destroy']);

        Route::post('/logout', [AuthController::class, 'logout']);
    });

});
