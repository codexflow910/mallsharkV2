<?php

namespace Packages\Dashboard\Routes;

use Illuminate\Support\Facades\Route;
use Packages\Dashboard\Http\Controllers\DashboardController;
use Packages\Dashboard\Http\Controllers\V2\DashboardController as DashboardControllerV2;
use Packages\Dashboard\Http\Controllers\V3\DashboardController as DashboardControllerV3;

Route::prefix('api/v2')->group(function () {

    Route::prefix(('dashboard'))->group(function () {
        Route::middleware('auth:api')->group(function () {
            Route::controller(DashboardControllerV2::class)->group(function () {
                
                Route::get('/store', 'storeInfo')->name('store');
                Route::get('/orders', 'ordersStats')->name('order-stats');
                Route::get('/products', 'productStats')->name('product-stats');
                Route::get('/orders/percentages', 'orderPercentages')->name('order-percentages');
                Route::get('/discount-live', 'discountLive')->name('discount-live');
            });
        });
    });
});

Route::prefix('api/v3')->group(function () {

    Route::prefix(('dashboard'))->group(function () {
        Route::middleware('auth:api')->group(function () {
            Route::controller(DashboardControllerV3::class)->group(function () {

                Route::get('/store', 'storeInfo')->name('v3.store');
                Route::get('/orders', 'ordersStats')->name('v3.order-stats');
                Route::get('/products', 'productStats')->name('v3.product-stats');
                Route::get('/orders/percentages', 'orderPercentages')->name('v3.order-percentages');
                Route::get('/discount-live', 'discountLive')->name('v3.discount-live');
            });
        });
    });
});

// V4 Routes
use Packages\Dashboard\Http\Controllers\V4\DashboardController as DashboardControllerV4;

Route::prefix('api/v4')->group(function () {

    Route::prefix(('dashboard'))->group(function () {
        Route::middleware('auth:api')->group(function () {
            Route::controller(DashboardControllerV4::class)->group(function () {

                Route::get('/store', 'storeInfo')->name('v4.store');
                Route::get('/orders', 'ordersStats')->name('v4.order-stats');
                Route::get('/products', 'productStats')->name('v4.product-stats');
                Route::get('/orders/percentages', 'orderPercentages')->name('v4.order-percentages');
                Route::get('/discount-live', 'discountLive')->name('v4.discount-live');
            });
        });
    });
});
