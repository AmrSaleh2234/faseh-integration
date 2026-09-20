<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Environment: sandbox | production
    |--------------------------------------------------------------------------
    */
    'env' => env('FASAHPAY_ENV', 'sandbox'),

    'timeout' => (int) env('FASAHPAY_TIMEOUT', 45),

    'sandbox' => [
        'token_url' => env(
            'FASAHPAY_SANDBOX_TOKEN_URL',
            'https://qapigw.tabadul.sa/tabadul/qa/oauth2/token/v1/jwt'
        ),
        'invoice_base_url' => env(
            'FASAHPAY_SANDBOX_INVOICE_BASE_URL',
            'https://qapigw.tabadul.sa/tabadul/qa/api/v1.1/FasahPay/invoices'
        ),
        'client_id' => env('FASAHPAY_SANDBOX_CLIENT_ID'),
        'client_secret' => env('FASAHPAY_SANDBOX_CLIENT_SECRET'),
        'username' => env('FASAHPAY_SANDBOX_USERNAME'),
        'password' => env('FASAHPAY_SANDBOX_PASSWORD'),
    ],

    'production' => [
        'token_url' => env(
            'FASAHPAY_PRODUCTION_TOKEN_URL',
            'https://apigw.tabadul.sa/tabadul/public/oauth2/token/v1/jwt'
        ),
        'invoice_base_url' => env(
            'FASAHPAY_PRODUCTION_INVOICE_BASE_URL',
            'https://apigw.tabadul.sa/tabadul/public/api/v1.1/FasahPay/invoices'
        ),
        'client_id' => env('FASAHPAY_PRODUCTION_CLIENT_ID'),
        'client_secret' => env('FASAHPAY_PRODUCTION_CLIENT_SECRET'),
        'username' => env('FASAHPAY_PRODUCTION_USERNAME'),
        'password' => env('FASAHPAY_PRODUCTION_PASSWORD'),
    ],

    /*
    | Your company VAT (15 digits) — sent as billerVATNumber.
    */
    'biller_vat_number' => env('FASAHPAY_BILLER_VAT_NUMBER'),

    /*
    | Sadad = real bills | View = demo only (no payment).
    */
    'payment_method' => env('FASAHPAY_PAYMENT_METHOD', 'Sadad'),

    /*
    | Default invoice category when Daftra custom field is empty.
    | Allowed: billoflading | declaration | custombroker | importer | shippingagent
    */
    'default_invoice_type' => env('FASAHPAY_DEFAULT_INVOICE_TYPE', 'billoflading'),

    /*
    | Callback URLs Fasah will POST to (must be public HTTPS + IP whitelisted).
    */
    'invoice_callback_url' => env('FASAHPAY_INVOICE_CALLBACK_URL'),
    'settlement_callback_url' => env('FASAHPAY_SETTLEMENT_CALLBACK_URL'),
    'provider_type' => env('FASAHPAY_PROVIDER_TYPE', 'GEN'),

    /*
    | JWT cache TTL in seconds (refresh a bit early).
    */
    'token_cache_ttl' => (int) env('FASAHPAY_TOKEN_CACHE_TTL', 3000),

    /*
    | Optional shared secret query/header check for callbacks (if Fasah allows).
    */
    'callback_secret' => env('FASAHPAY_CALLBACK_SECRET'),
];
