<?php

/**
 * Configuration des liaisons externes COMPTAFLOW.
 * Ces valeurs sont surchargées par les variables d'environnement du fichier .env.
 */
return [

    /*
    |--------------------------------------------------------------------------
    | URL de l'API Selflow
    |--------------------------------------------------------------------------
    */
    'selflow_api_url' => env('SELFLOW_API_URL', 'http://127.0.0.1:8003'),

    /*
    |--------------------------------------------------------------------------
    | Secret partagé API
    |--------------------------------------------------------------------------
    | Clé secrète partagée entre Selflow et COMPTAFLOW.
    | Doit être identique dans les deux .env.
    */
    'external_sync_secret' => env('EXTERNAL_SYNC_SECRET', 'selflow-comptaflow-secret-2026'),

    /*
    |--------------------------------------------------------------------------
    | Timeout des requêtes HTTP sortantes (secondes)
    |--------------------------------------------------------------------------
    */
    'api_timeout' => env('EXTERNAL_API_TIMEOUT', 15),

];
