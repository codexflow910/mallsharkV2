<?php

namespace Packages\Stripe\Routes;

use Illuminate\Support\Facades\Route;
use Packages\Stripe\Http\Controllers\PayoutController;
use Packages\Stripe\Http\Controllers\V2\PayoutController as PayoutControllerV2;
use Packages\Stripe\Http\Controllers\V3\PayoutController as PayoutControllerV3;

Route::prefix('api/v2')->group(function () {

    Route::prefix(('stripe'))->group(function () {
        Route::middleware('auth:api')->group(function () {
            Route::controller(PayoutControllerV2::class)->group(function () {

                Route::get('/payouts', 'getAllPayouts')->name('stripe-all-payouts');
                Route::get('/payout/{id}', 'getPayout')->name('stripe-get-payout');
                Route::get('/date-range', 'getPayoutsByDateRange')->name('stripe-payout-date');
                Route::post('/transferAmount', 'activateStipe');
                Route::get('/editstripeuser', 'editStripeAccount');
                Route::post('/get-stripe-login-link', 'getStripeLoginLink');
                Route::post('/onload-stripe-connect', 'onLoadConnectStripeAccount');
            });
        });        
    });
});

Route::prefix('api/v3')->group(function () {

    Route::prefix(('stripe'))->group(function () {
        Route::middleware('auth:api')->group(function () {
            Route::controller(PayoutControllerV3::class)->group(function () {

                Route::get('/payouts', 'getAllPayouts')->name('v3.stripe-all-payouts');
                Route::get('/payout/{id}', 'getPayout')->name('v3.stripe-get-payout');
                Route::get('/date-range', 'getPayoutsByDateRange')->name('v3.stripe-payout-date');
                Route::post('/transferAmount', 'activateStipe');
                Route::get('/editstripeuser', 'editStripeAccount');
                Route::post('/get-stripe-login-link', 'getStripeLoginLink');
                Route::post('/onload-stripe-connect', 'onLoadConnectStripeAccount');
            });
        });
    });
});

// V4 Routes
use Packages\Stripe\Http\Controllers\V4\PayoutController as PayoutControllerV4;

Route::prefix('api/v4')->group(function () {

    Route::prefix(('stripe'))->group(function () {
        Route::middleware('auth:api')->group(function () {
            Route::controller(PayoutControllerV4::class)->group(function () {

                Route::get('/payouts', 'getAllPayouts')->name('v4.stripe-all-payouts');
                Route::get('/payout/{id}', 'getPayout')->name('v4.stripe-get-payout');
                Route::get('/date-range', 'getPayoutsByDateRange')->name('v4.stripe-payout-date');
                Route::post('/transferAmount', 'activateStipe');
                Route::get('/editstripeuser', 'editStripeAccount');
                Route::post('/get-stripe-login-link', 'getStripeLoginLink');
                Route::post('/onload-stripe-connect', 'onLoadConnectStripeAccount');
            });
        });
    });
});
