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
    /*
    | Le secret partagé avec Selflow.
    |
    | **Aucune valeur de repli.** Elle valait « selflow-comptaflow-secret-2026 »,
    | en clair dans un dépôt : quiconque l'a lu pouvait déverser des écritures
    | dans la comptabilité de n'importe quelle entreprise liée, ou lire la liste
    | de toutes les entreprises de la plateforme. Sans la variable
    | d'environnement, les points d'entrée externes refusent désormais tout —
    | ce qui est le bon comportement : mieux vaut une synchronisation qui ne
    | démarre pas qu'une porte ouverte.
    */
    'external_sync_secret' => env('EXTERNAL_SYNC_SECRET'),

    /*
    |--------------------------------------------------------------------------
    | Timeout des requêtes HTTP sortantes (secondes)
    |--------------------------------------------------------------------------
    */
    'api_timeout' => env('EXTERNAL_API_TIMEOUT', 15),

];
