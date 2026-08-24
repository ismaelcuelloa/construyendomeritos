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

    'payu' => [
        'api_key' => env('PAYU_API_KEY'),
        'api_login' => env('PAYU_API_LOGIN'),
        'merchant_id' => env('PAYU_MERCHANT_ID'),
        'account_id' => env('PAYU_ACCOUNT_ID'),
        'mode' => env('PAYU_MODE', 'sandbox'), // sandbox o production
        'base_url' => env('PAYU_BASE_URL', 'https://sandbox.api.payulatam.com'),
        'response_url' => env('PAYU_RESPONSE_URL'), // URL a donde PayU redirige después del pago
        'confirmation_url' => env('PAYU_CONFIRMATION_URL'), // URL webhook para IPN
    ],

    'epayco' => [
        'client_id' => env('EPAYCO_CLIENT_ID'),
        'client_secret' => env('EPAYCO_CLIENT_SECRET'),
        'public_key' => env('EPAYCO_PUBLIC_KEY'),
        'private_key' => env('EPAYCO_PRIVATE_KEY'),
        'test' => env('EPAYCO_TEST', true), // true en desarrollo
        'url' => env('EPAYCO_CHECKOUT_URL', 'https://secure.epayco.co/checkout.php'),
    ],

    'wompi' => [
        'public_key' => env('WOMPI_PUBLIC_KEY'),
        'private_key' => env('WOMPI_PRIVATE_KEY'),
        'events_secret' => env('WOMPI_EVENTS_SECRET'),
        'integrity_secret' => env('WOMPI_INTEGRITY_SECRET'),
        'events_url' => env('WOMPI_EVENTS_URL'), // URL de webhook para eventos
    ],

    'meta' => [
        'pixel_id' => env('META_PIXEL_ID'),
        'access_token' => env('META_ACCESS_TOKEN'), // Opcional para Conversions API
    ],

];
