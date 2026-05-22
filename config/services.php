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
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
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

    'meudanfe' => [
        'base_url' => env('MEUDANFE_BASE_URL'),
        'api_key' => env('MEUDANFE_API_KEY'),
    ],

    'mercadopago' => [
        'access_token' => env('MERCADO_PAGO_ACCESS_TOKEN'),
        'mode' => env('MERCADO_PAGO_MODE', 'production'),
        'public_key' => env('MERCADO_PAGO_PUBLIC_KEY'),
        'client_id' => env('MERCADO_PAGO_CLIENT_ID'),
        'client_secret' => env('MERCADO_PAGO_CLIENT_SECRET'),
        'redirect_uri' => env('MERCADO_PAGO_REDIRECT_URI'),
        'oauth_scopes' => env('MERCADO_PAGO_OAUTH_SCOPES'),
        'card_checkout_enabled' => filter_var(env('MERCADO_PAGO_CARD_CHECKOUT_ENABLED', false), FILTER_VALIDATE_BOOLEAN),
        'webhook_url' => env('MERCADO_PAGO_WEBHOOK_URL'),
        'webhook_secret' => env('MERCADO_PAGO_WEBHOOK_SECRET'),
    ],

    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        // Support either GOOGLE_REDIRECT or GOOGLE_REDIRECT_URI in .env
        'redirect' => env('GOOGLE_REDIRECT', env('GOOGLE_REDIRECT_URI')),
    ],

];
