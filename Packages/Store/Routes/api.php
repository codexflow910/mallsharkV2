<?php

namespace Packages\Store\Routes;

use Illuminate\Support\Facades\Route;
use Packages\Store\Http\Controllers\StoreController;
use Packages\Store\Http\Controllers\V2\StoreController as StoreControllerV2;
use Packages\Store\Http\Controllers\V3\StoreController as StoreControllerV3;
use Packages\Store\Http\Controllers\V3\PayPalOnboardingController;

Route::prefix('api/v2')->group(function () {

    Route::prefix(('store'))->group(function () {
        Route::middleware('auth:api')->group(function () {

            Route::post('/store', [StoreControllerV2::class, 'store'])->name('storeProduct');
            Route::get('/products', [StoreControllerV2::class, 'getStoreProducts'])->name('store-product');
            Route::post('/create', [StoreControllerV2::class, 'addStore']);
            Route::get('/edit', [StoreControllerV2::class, 'editStore']);
            Route::post('/update', [StoreControllerV2::class, 'updateStore']);
            Route::post('/address', [StoreControllerV2::class, 'addStoreAddress']);
            Route::post('/status/change', [StoreControllerV2::class, 'StoreStatusChange']);
            Route::post('seller-account-setting', [StoreControllerV2::class, 'updateAccountSetting']);
            Route::get('/getallcountries', [StoreControllerV2::class, 'getAllCountries']);
            Route::post('/delete-banner', [StoreControllerV2::class, 'deleteBanner']);
            Route::get('/all-products', [StoreControllerV2::class, 'allProducts'])->name('store-products')->middleware('userPermission:manage-products-store');
            Route::post('store-about',[StoreControllerV2::class,'updateStoreAbout']);
            Route::get('store-about',[StoreControllerV2::class,'getStoreAbout']);
            Route::post('follow',[StoreControllerV2::class,'followStore']);
            Route::post('/unlock-next-day', [StoreControllerV2::class, 'unlockNextDay'])->name('unlock-next-day');
        });
        Route::post('/contact-us', [StoreControllerV2::class, 'contactStore'])->name('contact_us');
        Route::get('/home-v3', [StoreControllerV2::class, 'storeHomeV3']);
        Route::get('/info', [StoreControllerV2::class, 'storeinfo']);
        Route::get('/all', [StoreControllerV2::class, 'getAllBrands']);
        Route::get('/states/{countryId}', [StoreControllerV2::class, 'getStatesByCountryId']);
        Route::get('/top-sellers', [StoreControllerV2::class, 'getTopSellers']);
    });
});

Route::prefix('api/v3')->group(function () {

    Route::prefix(('store'))->group(function () {
        Route::middleware('auth:api')->group(function () {

            Route::post('/store', [StoreControllerV3::class, 'store'])->name('v3.storeProduct');
            Route::get('/products', [StoreControllerV3::class, 'getStoreProducts'])->name('v3.store-product');
            Route::post('/create', [StoreControllerV3::class, 'addStore']);
            Route::get('/edit', [StoreControllerV3::class, 'editStore']);
            Route::post('/update', [StoreControllerV3::class, 'updateStore']);
            Route::post('/address', [StoreControllerV3::class, 'addStoreAddress']);
            Route::post('/status/change', [StoreControllerV3::class, 'StoreStatusChange']);
            Route::post('seller-account-setting', [StoreControllerV3::class, 'updateAccountSetting']);
            Route::get('/getallcountries', [StoreControllerV3::class, 'getAllCountries']);
            Route::post('/delete-banner', [StoreControllerV3::class, 'deleteBanner']);
            Route::get('/all-products', [StoreControllerV3::class, 'allProducts'])->name('v3.store-products')->middleware('userPermission:manage-products-store');
            Route::post('store-about',[StoreControllerV3::class,'updateStoreAbout']);
            Route::get('store-about',[StoreControllerV3::class,'getStoreAbout']);
            Route::post('follow',[StoreControllerV3::class,'followStore']);
            Route::post('/unlock-next-day', [StoreControllerV3::class, 'unlockNextDay'])->name('v3.unlock-next-day');

            // PayPal Seller Onboarding Routes (authenticated)
            Route::prefix('paypal')->group(function () {
                Route::post('/onboard', [PayPalOnboardingController::class, 'initiateOnboarding']);
                Route::post('/refresh-status', [PayPalOnboardingController::class, 'refreshStatus']);
                Route::post('/disconnect', [PayPalOnboardingController::class, 'disconnect']);
                Route::post('/regenerate-link', [PayPalOnboardingController::class, 'regenerateLink']);
            });
            Route::post('wallet/status', [PayPalOnboardingController::class, 'checkWalletStatus']);
            Route::delete('/delete-store', [StoreControllerV3::class, 'deleteStore'])->name('v3.delete-store');
        });

        // PayPal return callback (no auth required - PayPal redirects here)
        Route::get('/paypal/return', [PayPalOnboardingController::class, 'onboardingReturn']);

        Route::post('/contact-us', [StoreControllerV3::class, 'contactStore'])->name('v3.contact_us');
        Route::get('/home', [StoreControllerV3::class, 'storeHome']);
        Route::get('/home-v3', [StoreControllerV3::class, 'storeHomeV3']);
        Route::get('/info', [StoreControllerV3::class, 'storeinfo']);
        Route::get('/all', [StoreControllerV3::class, 'getAllBrands']);
        Route::get('/states/{countryId}', [StoreControllerV3::class, 'getStatesByCountryId']);
    });
});

// V4 Routes
use Packages\Store\Http\Controllers\V4\StoreController as StoreControllerV4;

Route::prefix('api/v4')->group(function () {

    Route::prefix(('store'))->group(function () {
        Route::middleware('auth:api')->group(function () {

            Route::post('/store', [StoreControllerV4::class, 'store'])->name('v4.storeProduct');
            Route::get('/products', [StoreControllerV4::class, 'getStoreProducts'])->name('v4.store-product');
            Route::post('/create', [StoreControllerV4::class, 'addStore']);
            Route::get('/edit', [StoreControllerV4::class, 'editStore']);
            Route::post('/update', [StoreControllerV4::class, 'updateStore']);
            Route::post('/address', [StoreControllerV4::class, 'addStoreAddress']);
            Route::post('/status/change', [StoreControllerV4::class, 'StoreStatusChange']);
            Route::post('seller-account-setting', [StoreControllerV4::class, 'updateAccountSetting']);
            Route::get('/getallcountries', [StoreControllerV4::class, 'getAllCountries']);
            Route::post('/delete-banner', [StoreControllerV4::class, 'deleteBanner']);
            Route::get('/all-products', [StoreControllerV4::class, 'allProducts'])->name('v4.store-products')->middleware('userPermission:manage-products-store');
            Route::post('store-about',[StoreControllerV4::class,'updateStoreAbout']);
            Route::get('store-about',[StoreControllerV4::class,'getStoreAbout']);
            Route::post('follow',[StoreControllerV4::class,'followStore']);
            Route::post('/unlock-next-day', [StoreControllerV4::class, 'unlockNextDay'])->name('v4.unlock-next-day');
            Route::delete('/delete-store', [StoreControllerV4::class, 'deleteStore'])->name('v4.delete-store');
        });
        Route::post('/contact-us', [StoreControllerV4::class, 'contactStore'])->name('v4.contact_us');
        Route::get('/home', [StoreControllerV4::class, 'storeHome']);
        Route::get('/home-v3', [StoreControllerV4::class, 'storeHomeV3']);
        Route::get('/info', [StoreControllerV4::class, 'storeinfo']);
        Route::get('/all', [StoreControllerV4::class, 'getAllBrands']);
        Route::get('/states/{countryId}', [StoreControllerV4::class, 'getStatesByCountryId']);
    });
});
