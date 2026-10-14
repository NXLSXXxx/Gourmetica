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

    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => env('GOOGLE_REDIRECT_URL'),
    ],

    'sunat' => [
        'production' => env('SUNAT_PRODUCTION', false),
        'endpoint_production' => env('SUNAT_ENDPOINT_PRODUCTION', 'https://e-factura.sunat.gob.pe/ol-ti-itcpfegem/billService'),
        'endpoint_demo' => env('SUNAT_ENDPOINT_DEMO', 'https://demo-ose.nubefact.com/ol-ti-itcpe/billService'),
        'ruc' => env('SUNAT_RUC', '10737725648'),
        'user' => env('SUNAT_USER', 'MODDATOS'),
        'pass' => env('SUNAT_PASS', 'MODDATOS'),
        'certificate_path' => env('SUNAT_CERTIFICATE_PATH', storage_path('app/sunat/certificate.pem')),
    ],
];
