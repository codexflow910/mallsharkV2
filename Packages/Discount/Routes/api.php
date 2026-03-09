<?php

namespace Packages\Discount\Routes;

use Illuminate\Support\Facades\Route;
use Packages\Discount\Http\Controllers\DiscountController;
use Packages\Discount\Http\Controllers\V2\DiscountController as DiscountControllerV2;
use Packages\Discount\Http\Controllers\V3\DiscountController as DiscountControllerV3;


Route::prefix('api/v2')->group(function () {

    Route::prefix(('discount'))->group(function () {
        Route::post('/apply-coupon', [DiscountControllerV2::class, 'applyCoupon'])->name('applyCoupon');
        Route::post('/get-coupons', [DiscountControllerV2::class, 'getCoupons']);
        Route::get('/coupons/{id}/products', [DiscountControllerV2::class, 'couponsProducts']);
        
        Route::middleware('auth:api')->group(function () {
            Route::controller(DiscountControllerV2::class)->group(function () {
                Route::post('/customer-coupens','customerCoupons')->name('customer-coupons');
                Route::get('/customer-coupon-dashboard','customerCouponsDashboard')->name('customer-coupons');
            });
            Route::middleware('userPermission:manage-discounts-store')->group(function () {
                Route::controller(DiscountControllerV2::class)->group(function () {
                    Route::post('/store', 'store')->name('storeDiscount');
                    Route::post('/update/{id}', 'update')->name('updateDiscount');
                    Route::get('/list', 'getAll')->name('discountList');
                    Route::get('/archive-list', 'getArchives')->name('discountArchiveList');
                    Route::get('/{id}', 'get')->name('getDiscount');
                    Route::put('/{id}/update-status', 'updateStatus')->name('updateStatus'); 
                    Route::put('/{id}/confirm-update-status', 'confirmUpdateStatus')->name('confirmUpdateStatus'); 
                    Route::post('/{id}/archive','archive')->name('discountArchive');
                    Route::post('/{id}/unarchive','unArchive')->name('discountUnarchive');
                });
            });
        });

    });
});

Route::prefix('api/v3')->group(function () {

    Route::prefix(('discount'))->group(function () {
        Route::post('/apply-coupon', [DiscountControllerV3::class, 'applyCoupon'])->name('v3.applyCoupon');
        Route::post('/get-coupons', [DiscountControllerV3::class, 'getCoupons']);
        Route::get('/coupons/{id}/products', [DiscountControllerV3::class, 'couponsProducts']);

        Route::middleware('auth:api')->group(function () {
            Route::controller(DiscountControllerV3::class)->group(function () {
                Route::post('/customer-coupens','customerCoupons')->name('v3.customer-coupons');
                Route::get('/customer-coupon-dashboard','customerCouponsDashboard')->name('v3.customer-coupons-dashboard');
            });
            Route::middleware('userPermission:manage-discounts-store')->group(function () {
                Route::controller(DiscountControllerV3::class)->group(function () {
                    Route::post('/store', 'store')->name('v3.storeDiscount');
                    Route::post('/update/{id}', 'update')->name('v3.updateDiscount');
                    Route::get('/list', 'getAll')->name('v3.discountList');
                    Route::get('/archive-list', 'getArchives')->name('v3.discountArchiveList');
                    Route::get('/{id}', 'get')->name('v3.getDiscount');
                    Route::put('/{id}/update-status', 'updateStatus')->name('v3.updateStatus');
                    Route::put('/{id}/confirm-update-status', 'confirmUpdateStatus')->name('v3.confirmUpdateStatus');
                    Route::post('/{id}/archive','archive')->name('v3.discountArchive');
                    Route::post('/{id}/unarchive','unArchive')->name('v3.discountUnarchive');
                });
            });
        });

    });
});

// V4 Routes
use Packages\Discount\Http\Controllers\V4\DiscountController as DiscountControllerV4;

Route::prefix('api/v4')->group(function () {

    Route::prefix(('discount'))->group(function () {
        Route::post('/apply-coupon', [DiscountControllerV4::class, 'applyCoupon'])->name('v4.applyCoupon');
        Route::post('/get-coupons', [DiscountControllerV4::class, 'getCoupons']);
        Route::get('/coupons/{id}/products', [DiscountControllerV4::class, 'couponsProducts']);

        Route::middleware('auth:api')->group(function () {
            Route::controller(DiscountControllerV4::class)->group(function () {
                Route::post('/customer-coupens','customerCoupons')->name('v4.customer-coupons');
                Route::get('/customer-coupon-dashboard','customerCouponsDashboard')->name('v4.customer-coupons-dashboard');
            });
            Route::middleware('userPermission:manage-discounts-store')->group(function () {
                Route::controller(DiscountControllerV4::class)->group(function () {
                    Route::post('/store', 'store')->name('v4.storeDiscount');
                    Route::post('/update/{id}', 'update')->name('v4.updateDiscount');
                    Route::get('/list', 'getAll')->name('v4.discountList');
                    Route::get('/archive-list', 'getArchives')->name('v4.discountArchiveList');
                    Route::get('/{id}', 'get')->name('v4.getDiscount');
                    Route::put('/{id}/update-status', 'updateStatus')->name('v4.updateStatus');
                    Route::put('/{id}/confirm-update-status', 'confirmUpdateStatus')->name('v4.confirmUpdateStatus');
                    Route::post('/{id}/archive','archive')->name('v4.discountArchive');
                    Route::post('/{id}/unarchive','unArchive')->name('v4.discountUnarchive');
                });
            });
        });

    });
});
