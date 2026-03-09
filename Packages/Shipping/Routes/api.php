<?php

namespace Packages\Shipping\Routes;

use Illuminate\Support\Facades\Route;
use Packages\Shipping\Http\Controllers\ShippingController;
use Packages\Shipping\Http\Controllers\V2\ShippingController as ShippingControllerV2;
use Packages\Shipping\Http\Controllers\V3\ShippingController as ShippingControllerV3;

Route::prefix('api/v2')->group(function () {
    Route::middleware('auth:api')->group(function () {
        Route::controller(ShippingControllerV2::class)->group(function () {
            Route::prefix(('shipping/profile'))->group(function () {
                Route::post('save-update-shipping-option',[ShippingControllerV2::class,'updateShippingProfile']);
                Route::get('get-shipping-option',[ShippingControllerV2::class,'getShippingOptions']);
                Route::get('delete', 'deleteShippingProfile')->name('delete-shpping-profile');
            });
            Route::prefix(('shipping'))->group(function () {
                Route::post('new-carrier', [ShippingControllerV2::class, 'storeNewCarrier']);
                Route::get('get-store-carrier', [ShippingControllerV2::class, 'getStoreCarrier'])->name('get-carrier');
                Route::post('assign-carrier', [ShippingControllerV2::class, 'assignCarrierToStore'])->name('assign-carrier');
                Route::get('carrier', [ShippingControllerV2::class, 'getCarriers']);
                Route::get('/profile', [ShippingControllerV2::class, 'getProfiles'])->middleware('userPermission:manage-products-store');
                Route::post('profile', [ShippingControllerV2::class, 'storeProfile'])->middleware('userPermission:manage-products-store');
                Route::post('profile-update/{id}', [ShippingControllerV2::class, 'updateProfile'])->middleware('userPermission:manage-products-store');
                Route::delete('/profile-delete/{id}', [ShippingControllerV2::class, 'destroy'])->middleware('userPermission:manage-products-store');
                Route::post('carrier-config', [ShippingControllerV2::class, 'storeCarrierConfig']);
                Route::post('delete-store-carrier', [ShippingControllerV2::class, 'deleteStoreCarrier'])->name('delete-store-carrier');
            });

        });        
    });
});

Route::prefix('api/v3')->group(function () {
    Route::middleware('auth:api')->group(function () {
        Route::controller(ShippingControllerV3::class)->group(function () {
            Route::prefix(('shipping/profile'))->group(function () {
                Route::post('save-update-shipping-option',[ShippingControllerV3::class,'updateShippingProfile']);
                Route::get('get-shipping-option',[ShippingControllerV3::class,'getShippingOptions']);
                Route::get('delete', 'deleteShippingProfile')->name('v3.delete-shpping-profile');
            });
            Route::prefix(('shipping'))->group(function () {
                Route::post('new-carrier', [ShippingControllerV3::class, 'storeNewCarrier']);
                Route::get('get-store-carrier', [ShippingControllerV3::class, 'getStoreCarrier'])->name('v3.get-carrier');
                Route::post('assign-carrier', [ShippingControllerV3::class, 'assignCarrierToStore'])->name('v3.assign-carrier');
                Route::get('carrier', [ShippingControllerV3::class, 'getCarriers']);
                Route::get('/profile', [ShippingControllerV3::class, 'getProfiles'])->middleware('userPermission:manage-products-store');
                Route::post('profile', [ShippingControllerV3::class, 'storeProfile'])->middleware('userPermission:manage-products-store');
                Route::post('profile-update/{id}', [ShippingControllerV3::class, 'updateProfile'])->middleware('userPermission:manage-products-store');
                Route::delete('/profile-delete/{id}', [ShippingControllerV3::class, 'destroy'])->middleware('userPermission:manage-products-store');
                Route::post('carrier-config', [ShippingControllerV3::class, 'storeCarrierConfig']);
                Route::post('delete-store-carrier', [ShippingControllerV3::class, 'deleteStoreCarrier'])->name('v3.delete-store-carrier');
            });

        });
    });
});

// V4 Routes
use Packages\Shipping\Http\Controllers\V4\ShippingController as ShippingControllerV4;

Route::prefix('api/v4')->group(function () {
    Route::middleware('auth:api')->group(function () {
        Route::controller(ShippingControllerV4::class)->group(function () {
            Route::prefix(('shipping/profile'))->group(function () {
                Route::post('save-update-shipping-option',[ShippingControllerV4::class,'updateShippingProfile']);
                Route::get('get-shipping-option',[ShippingControllerV4::class,'getShippingOptions']);
                Route::get('delete', 'deleteShippingProfile')->name('v4.delete-shpping-profile');
            });
            Route::prefix(('shipping'))->group(function () {
                Route::post('new-carrier', [ShippingControllerV4::class, 'storeNewCarrier']);
                Route::get('get-store-carrier', [ShippingControllerV4::class, 'getStoreCarrier'])->name('v4.get-carrier');
                Route::post('assign-carrier', [ShippingControllerV4::class, 'assignCarrierToStore'])->name('v4.assign-carrier');
                Route::get('carrier', [ShippingControllerV4::class, 'getCarriers']);
                Route::get('/profile', [ShippingControllerV4::class, 'getProfiles'])->middleware('userPermission:manage-products-store');
                Route::post('profile', [ShippingControllerV4::class, 'storeProfile'])->middleware('userPermission:manage-products-store');
                Route::post('profile-update/{id}', [ShippingControllerV4::class, 'updateProfile'])->middleware('userPermission:manage-products-store');
                Route::delete('/profile-delete/{id}', [ShippingControllerV4::class, 'destroy'])->middleware('userPermission:manage-products-store');
                Route::post('carrier-config', [ShippingControllerV4::class, 'storeCarrierConfig']);
                Route::post('delete-store-carrier', [ShippingControllerV4::class, 'deleteStoreCarrier'])->name('v4.delete-store-carrier');
            });

        });
    });
});
