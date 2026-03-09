<?php

return [
    /*
    |--------------------------------------------------------------------------
    | PayPal Mode
    |--------------------------------------------------------------------------
    |
    | This option controls the PayPal environment mode.
    | Supported: "sandbox", "live"
    |
    */
    'mode' => env('PAYPAL_MODE', 'sandbox'),

    /*
    |--------------------------------------------------------------------------
    | PayPal Credentials
    |--------------------------------------------------------------------------
    |
    | Your PayPal REST API credentials. Get these from:
    | https://developer.paypal.com/dashboard/applications
    |
    */
    'client_id' => env('PAYPAL_CLIENT_ID'),
    'client_secret' => env('PAYPAL_CLIENT_SECRET'),

    /*
    |--------------------------------------------------------------------------
    | PayPal Webhook ID
    |--------------------------------------------------------------------------
    |
    | The webhook ID for verifying PayPal webhook signatures.
    | Create webhooks at: https://developer.paypal.com/dashboard/webhooks
    |
    */
    'webhook_id' => env('PAYPAL_WEBHOOK_ID'),

    /*
    |--------------------------------------------------------------------------
    | PayPal API URLs
    |--------------------------------------------------------------------------
    |
    | The base URLs for PayPal API endpoints.
    |
    */
    'base_url' => [
        'sandbox' => 'https://api-m.sandbox.paypal.com',
        'live' => 'https://api-m.paypal.com',
    ],

    /*
    |--------------------------------------------------------------------------
    | PayPal Return URLs
    |--------------------------------------------------------------------------
    |
    | URLs for PayPal to redirect after payment approval or cancellation.
    |
    */
    'return_url' => env('PAYPAL_RETURN_URL', env('APP_URL') . '/checkout/paypal/success'),
    'cancel_url' => env('PAYPAL_CANCEL_URL', env('APP_URL') . '/checkout/paypal/cancel'),

    /*
    |--------------------------------------------------------------------------
    | Currency
    |--------------------------------------------------------------------------
    |
    | Default currency for PayPal transactions.
    |
    */
    'currency' => env('PAYPAL_CURRENCY', 'USD'),

    /*
    |--------------------------------------------------------------------------
    | Platform Fee Percentage
    |--------------------------------------------------------------------------
    |
    | Platform fee percentage to charge on each order (as decimal).
    | Example: 0.045 = 4.5%
    |
    */
    'platform_fee_percentage' => env('PAYPAL_PLATFORM_FEE', 0.045),

    /*
    |--------------------------------------------------------------------------
    | PayPal Fee Calculation
    |--------------------------------------------------------------------------
    |
    | PayPal's transaction fee structure for calculations.
    |
    */
    'transaction_fee' => [
        'percentage' => 0.0349, // 3.49% for standard PayPal
        'fixed' => 0.49,        // $0.49 fixed fee
    ],

    /*
    |--------------------------------------------------------------------------
    | PayPal Partner/Seller Onboarding Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for PayPal Commerce Platform (Partner Referrals API)
    | to onboard sellers and enable split payments.
    |
    */
    'partner_id' => env('PAYPAL_PARTNER_ID'),

    'partner_attribution_id' => env('PAYPAL_BN_CODE'),

    // Seller onboarding return URLs
    'seller_return_url' => env('ADMIN_URL') . '/paypal/return',
    'seller_cancel_url' => env('PAYPAL_SELLER_CANCEL_URL', env('APP_URL') . '/seller/paypal/cancel'),

    // Required features/scopes for sellers
    'seller_features' => [
        'PAYMENT',
        'REFUND',
        'PARTNER_FEE',
        'DELAY_FUNDS_DISBURSEMENT',
    ],

    // Required products for seller accounts
    'seller_products' => [
        'EXPRESS_CHECKOUT',
    ],

    // Disbursement mode for split payments: INSTANT or DELAYED
    'disbursement_mode' => env('PAYPAL_DISBURSEMENT_MODE', 'DELAYED'),
];
