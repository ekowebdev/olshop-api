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

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme' => 'https',
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => env('GOOGLE_REDIRECT_URI'),
    ],

    'recaptcha' => [
        'site_key' => env('GOOGLE_RECAPTCHA_SITE_KEY'),
        'secret_key' => env('GOOGLE_RECAPTCHA_SECRET_KEY'),
        'development_key' => env('GOOGLE_RECAPTCHA_DEVELOPMENT_KEY'),
    ],

    'rajaongkir' => [
        'key' => env('RAJAONGKIR_API_KEY')
    ],

    'midtrans' => [
        'server_key' => env('MIDTRANS_SERVER_KEY'),
        'production' => env('MIDTRANS_PRODUCTION'),
        '3ds' => env('MIDTRANS_3DS'),
    ],

    'binderbyte' => [
        'key' => env('BINDERBYTE_API_KEY')
    ],

    'meilisearch' => [
        'host' => env('MEILISEARCH_HOST', 'http://127.0.0.1:7700'),
        'key' => env('MEILISEARCH_KEY', null),
    ],

    'cloudinary' => [
        'key' => env('CLOUDINARY_API_KEY'),
        'secret' => env('CLOUDINARY_API_SECRET'),
        'url' => env('CLOUDINARY_URL'),
        'path_url' => env('CLOUDINARY_PATH_URL'),
        'folder' => env('CLOUDINARY_FOLDER'),
    ],

    'xendit' => [
        'secret_key' => env('XENDIT_SECRET_KEY'),
        'public_key' => env('XENDIT_PUBLIC_KEY'),
        'webhook_verification_token' => env('XENDIT_WEBHOOK_VERIFICATION_TOKEN'),
    ],
];
