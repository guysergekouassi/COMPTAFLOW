<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\DashboardApiController;

/*
|--------------------------------------------------------------------------
| API Routes – ComptaFlow
|--------------------------------------------------------------------------
|
| Ajouter ces routes dans routes/api.php du projet ComptaFlow.
|
| Toutes les routes exposées au FlowHub sont protégées par :
|   - Middleware 'verify.hub.token' : X-Hub-Token header
|   - Middleware 'api'             : réponses JSON, sans session
|
*/

// Groupe routes Hub – accès via proxy FlowHub
Route::middleware(['api', 'verify.hub.token'])->prefix('dashboard')->group(function () {

    /**
     * GET /api/dashboard/kpis?company_id={id}
     *
     * Retourne les KPIs financiers consolidés pour le Hub.
     * Paramètres :
     *   - company_id (int, optionnel, défaut = 1) : ID de la société
     */
    Route::get('/kpis', [DashboardApiController::class, 'kpis'])
         ->name('api.dashboard.kpis');
         
    Route::get('/exercices', [DashboardApiController::class, 'getExercices'])
         ->name('api.dashboard.exercices');

});

/**
 * GET /api/companies
 * 
 * Retourne la liste simplifiée des entreprises pour le Hub
 */
Route::middleware(['api', 'verify.hub.token'])->group(function () {
    Route::get('/companies', [DashboardApiController::class, 'companies'])
         ->name('api.companies');
});

// Route de santé (ping) – sans authentification pour le monitoring
Route::get('/ping', function () {
    return response()->json([
        'status'    => 'ok',
        'service'   => 'ComptaFlow API',
        'version'   => config('app.version', '1.0.0'),
        'timestamp' => now()->toIso8601String(),
    ]);
})->name('api.ping');
