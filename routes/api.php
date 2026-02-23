<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\AccountingController;
use App\Http\Controllers\Api\EntryController;
use App\Http\Controllers\Api\ReportController;

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    
    Route::get('/dashboard', [DashboardController::class, 'index']);
    
    // Plan Comptable
    Route::get('/accounting/plan-comptable', [AccountingController::class, 'planComptableIndex']);
    Route::post('/accounting/plan-comptable', [AccountingController::class, 'planComptableStore']);
    
    // Plan Tiers
    Route::get('/accounting/plan-tiers', [AccountingController::class, 'planTiersIndex']);
    Route::post('/accounting/plan-tiers', [AccountingController::class, 'planTiersStore']);
    
    // Codes Journaux
    Route::get('/accounting/journals', [AccountingController::class, 'journalsIndex']);
    Route::post('/accounting/journals', [AccountingController::class, 'journalsStore']);
    
    // Écritures et Scan
    Route::get('/entries', [EntryController::class, 'index']);
    Route::post('/entries', [EntryController::class, 'store']);
    Route::get('/entries/{n_saisie}', [EntryController::class, 'loadBySaisie']);
    Route::delete('/entries/{n_saisie}', [EntryController::class, 'destroy']);
    Route::post('/scan', [EntryController::class, 'scan']);
    
    // Rapports Financials
    Route::get('/reports/balance', [ReportController::class, 'balance']);
    Route::get('/reports/grand-livre', [ReportController::class, 'grandLivre']);
    Route::get('/reports/bilan', [ReportController::class, 'bilan']);
    Route::get('/reports/resultat', [ReportController::class, 'resultat']);
    Route::get('/reports/tft', [ReportController::class, 'tft']);
    
    // Suppression générique
    Route::delete('/accounting/{type}/{id}', [AccountingController::class, 'destroy']);

    Route::post('/logout', [AuthController::class, 'logout']);
});

// Routes pour l'application mobile
Route::prefix('v1')->middleware('auth:sanctum')->group(function () {
    // Ajoutez vos routes ici
});
