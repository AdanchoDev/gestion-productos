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

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'banxico' => [
        'token' => env('BANXICO_TOKEN'),
        'url' => env('BANXICO_URL', 'https://www.banxico.org.mx/SieAPIRest/service/v1'),
        // SF43718 es la serie del tipo de cambio FIX (pesos por dólar)
        'serie' => env('BANXICO_SERIE', 'SF43718'),
    ],

    'tipo_cambio' => [
        // API de respaldo, sin credenciales, para cuando no hay token de Banxico o Banxico no responde
        'url' => env('TIPO_CAMBIO_URL', 'https://api.frankfurter.dev/v1/latest'),
        'timeout' => (int) env('TIPO_CAMBIO_TIMEOUT', 5),
        'cache_store' => env('TIPO_CAMBIO_CACHE_STORE', 'redis'),
        'cache_ttl' => (int) env('TIPO_CAMBIO_CACHE_TTL', 3600),
    ],

];
