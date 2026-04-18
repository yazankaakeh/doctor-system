<?php

return [
    'name' => 'Payment',

    /*
    |--------------------------------------------------------------------------
    | PayPal Configuration
    |--------------------------------------------------------------------------
    */
    'paypal' => [
        'sandbox' => env('PAYPAL_SANDBOX', true),
        'client_id' => env('PAYPAL_CLIENT_ID'),
        'client_secret' => env('PAYPAL_CLIENT_SECRET'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Payment Settings
    |--------------------------------------------------------------------------
    */
    'currency' => env('PAYMENT_CURRENCY', 'USD'),
    'offline_payment_enabled' => env('OFFLINE_PAYMENT_ENABLED', true),
];
