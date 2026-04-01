<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'vertex_ai' => [
        'project_id' => env('GOOGLE_CLOUD_PROJECT_ID', 'scan1-comptaflow'),
        'project_number' => env('GOOGLE_CLOUD_PROJECT_NUMBER', '288805151479'),
        'location' => env('VERTEX_AI_LOCATION', 'us-central1'),

        'model' => env('VERTEX_AI_MODEL', 'gemini-1.5-flash'),
        'api_version' => env('VERTEX_AI_API_VERSION', 'v1'),
        'temperature' => env('VERTEX_AI_TEMPERATURE', 0.2),
        'max_tokens' => env('VERTEX_AI_MAX_TOKENS', 4096),
    ],


];
