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
// `X-Company-Key` et vérifiée par `integration.auth`, qui refuse une clé
// désignant un autre dossier que celui annoncé dans le corps.
// ═══════════════════════════════════════════════════════════════════════════
// Demande de création de dossier dédiée par une application externe.
Route::post('/integration/external/company-requests/{requestId}/status', [\App\Http\Controllers\Api\IntegrationCompanyRequestController::class, 'show'])
    ->middleware('throttle:20,1')
    ->name('api.integration.external.company_requests.show');
Route::post('/integration/external/company-requests', [\App\Http\Controllers\Api\IntegrationCompanyRequestController::class, 'store'])
    ->middleware('throttle:5,1')
    ->name('api.integration.external.company_requests.store');

// Échange du code de liaison copié depuis le Super Admin (application maison).
Route::post('/integration/pairing/redeem', [\App\Http\Controllers\Api\IntegrationPairingController::class, 'redeem'])
    ->middleware('throttle:5,1')
    ->name('api.integration.pairing.redeem');

// Authentification centralisée des applications COMPTA-APK.
Route::post('/integration/token', [\App\Http\Controllers\Api\IntegrationTokenController::class, 'issue'])
    ->middleware('throttle:10,1')
    ->name('api.integration.token');

Route::prefix('external')->group(function () {
    // Liaison indépendante GESTION-DE-SITES : elle ne réutilise aucun identifiant Selflow.
    Route::post('/gestionsites/companies/provision', [\App\Http\Controllers\Api\ExternalGestionSitesController::class, 'provision'])
        ->middleware('throttle:6,1')
        ->name('api.external.gestionsites.provision');
    Route::post('/gestionsites/companies/verify', [\App\Http\Controllers\Api\ExternalGestionSitesController::class, 'verify'])
        ->middleware(['integration.auth', 'throttle:60,1'])
        ->name('api.external.gestionsites.verify');
    Route::post('/gestionsites/tiers/lister', [\App\Http\Controllers\Api\ExternalGestionSitesController::class, 'listerTiers'])
        ->middleware(['integration.auth', 'throttle:60,1'])
        ->name('api.external.gestionsites.tiers.lister');

    // ── Le cycle de vie de la liaison ──
    //
    // `provision` est le seul appel sans `integration.auth` : il n'y a pas encore
    // de clé à présenter, puisque c'est lui qui la génère. D'où la limitation
    // de débit serrée — c'est aussi le seul qui crée des dossiers.
    Route::post('/companies/provision', [\App\Http\Controllers\Api\ExternalCompanyController::class, 'provision'])
        ->middleware('throttle:6,1')
        ->name('api.external.companies.provision');
    Route::post('/companies/revoke', [\App\Http\Controllers\Api\ExternalCompanyController::class, 'revoke'])
        ->middleware(['integration.auth', 'throttle:20,1'])
        ->name('api.external.companies.revoke');
    // La clé ne doit pas être éternelle : `rotate-key` la remplace, et la clé
    // actuelle authentifie sa propre relève. Débit serré comme `revoke` — un
    // dossier ne se renouvelle qu'une fois par mois, plus un bouton manuel.
    Route::post('/companies/rotate-key', [\App\Http\Controllers\Api\ExternalCompanyController::class, 'rotateKey'])
        ->middleware(['integration.auth', 'throttle:20,1'])
        ->name('api.external.companies.rotate-key');
    Route::post('/companies/verify', [\App\Http\Controllers\Api\ExternalCompanyController::class, 'verify'])
        ->middleware(['integration.auth', 'throttle:60,1'])
        ->name('api.external.companies.verify');

    // ── Les deux déversements ──
    //
    // C'est ici que la clé sert vraiment : sans `integration.auth` sur ces deux
    // routes, tout le reste ne sert à rien.
    Route::post('/ecritures/deverser', [\App\Http\Controllers\Api\ExternalSyncController::class, 'deverserEcritures'])
        ->middleware(['integration.auth', 'throttle:60,1'])
        ->name('api.external.ecritures.deverser');
    // Rejeu : les opérations parties avant le 06/10/2026 sont arrivées une
    // ligne par pièce. Ce point d'entrée les regroupe sous un seul numéro,
    // sans rien recréer — rejouer le déversement n'aurait rien changé, chaque
    // ligne y étant déjà reconnue à sa clé.
    Route::post('/ecritures/regrouper', [\App\Http\Controllers\Api\ExternalSyncController::class, 'regrouperEcritures'])
        ->middleware(['integration.auth', 'throttle:60,1'])
        ->name('api.external.ecritures.regrouper');
    Route::post('/ecritures/comptes/propager', [\App\Http\Controllers\Api\ExternalSyncController::class, 'propagerComptes'])
        ->middleware(['integration.auth', 'throttle:30,1'])
        ->name('api.external.ecritures.comptes.propager');
    // Selflow déverse son référentiel — plan comptable, journaux, tiers — dans
    // l'entreprise Comptaflow qui lui est liée. Sens unique : rien ne repart.
    Route::post('/referentiel/deverser', [\App\Http\Controllers\Api\ExternalSyncController::class, 'deverserReferentiel'])
        ->middleware(['integration.auth', 'throttle:60,1'])
        ->name('api.external.referentiel.deverser');

    // Pièce PDF rattachée à toutes les lignes d'une opération source.
    // Le dossier destinataire provient exclusivement de X-Company-Key.
    Route::post('/pieces-justificatives/deverser', [\App\Http\Controllers\Api\ExternalJustificatifController::class, 'deverser'])
        ->middleware(['integration.auth', 'throttle:20,1'])
        ->name('api.external.pieces-justificatives.deverser');
    Route::get('/pieces-justificatives/{ecritureId}', [\App\Http\Controllers\Api\ExternalJustificatifController::class, 'telechargerPourIntegration'])
        ->middleware(['integration.auth', 'throttle:60,1'])
        ->name('api.external.pieces-justificatives.show');
    Route::post('/gestionsites/referentiel/deverser', [\App\Http\Controllers\Api\ExternalSyncController::class, 'deverserReferentiel'])
        ->middleware(['integration.auth', 'throttle:60,1'])
        ->name('api.external.gestionsites.referentiel.deverser');

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
        ->middleware(['integration.auth', 'throttle:30,1'])
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
