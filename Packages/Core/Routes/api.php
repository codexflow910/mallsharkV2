<?php

namespace Packages\Core\Routes;

use Illuminate\Support\Facades\Route;
use Packages\Core\Http\Controllers\CoreController;
use Packages\Core\Http\Controllers\CheckoutController;
use Packages\Core\Http\Controllers\V2\CoreController as CoreControllerV2;
use Packages\Core\Http\Controllers\V2\CheckoutController as CheckoutControllerV2;
use Packages\Core\Http\Controllers\V3\CoreController as CoreControllerV3;
use Packages\Core\Http\Controllers\V3\CheckoutController as CheckoutControllerV3;
use Packages\Core\Http\Controllers\V4\CoreController as CoreControllerV4;
use Packages\Core\Http\Controllers\V4\CheckoutController as CheckoutControllerV4;


Route::prefix('api/v2')->group(function () {

    Route::prefix(('core'))->group(function () {
        Route::get('/tax/calculate', [CoreControllerV2::class, 'calculateTax']);
        Route::middleware('auth:api')->group(function () {
            Route::controller(CoreControllerV2::class)->group(function () {
                Route::post('/store', 'store')->name('storeProduct');
                Route::post('/buy-package', 'getPackageCheckout');
            });

            Route::controller(CoreControllerV2::class)->group(function () {
                Route::get('/package-confirmation', 'PackageConfirmation');
            });


            Route::prefix('shipstation')->group(function () {
                Route::post('create-order', [CoreControllerV2::class, 'createShipstationOrder']);
                Route::post('sync-orders-statuses', [CoreControllerV2::class, 'syncShipstationOrdersStatuses']);
                Route::post('create-webhook', [CoreControllerV2::class, 'createShipStationWebhook']);
                Route::post('order-webhook', [CoreControllerV2::class, 'handleTrackEvent']);
            });
            Route::prefix('usps')->group(function () {
                Route::post('get-rates', [CoreControllerV2::class, 'getUspsRates']);
                Route::post('ship-order', [CoreControllerV2::class, 'shipUspsOrder']);
            });
        });
        Route::get('product-profiles', [CheckoutControllerV2::class, 'profiles']);
        Route::post('/checkout', [CheckoutControllerV2::class,'getCheckout']);
        Route::post('/payment-received', [CheckoutControllerV2::class, 'paymentReceived']);
        Route::post('/new-payment-received', [CheckoutControllerV2::class, 'paymentReceivedV2']);
        Route::post('/buy-now',[CheckoutControllerV2::class,'buyNow']);
        Route::get('order-confirmation', [CheckoutControllerV2::class, 'OrderConfirmation']);
        Route::post('crypto-checkout',[CheckoutControllerV2::class,'cryptoCheckout']);
        Route::post('new-checkout-v3',[CheckoutControllerV2::class,'newCheckout']);
        Route::post('/apply-coupon-v3',[CheckoutControllerV2::class,'applyCouponV3']);
        Route::get('low-stock-email/{orderId}', [CheckoutControllerV2::class, 'sendLowStockEmail']);
        Route::post('order-summary', [CheckoutControllerV2::class, 'getOrderSummary']);
        Route::post('/checkout-shipping-rates', [CheckoutControllerV2::class, 'getRatesOnCheckout']);

        Route::prefix('usps')->group(function () {
            Route::post('get-rates', [CoreControllerV2::class, 'getUspsRates']);
            Route::post('validate-address', [CoreControllerV2::class, 'uspsValidateAddress']);
        });
        Route::post('/crypto-webhook', [CheckoutControllerV2::class, 'cryptoWebhook']);
        Route::post('apply_collective_coupon',[CheckoutControllerV2::class,'applyCollectiveCoupon']);
    });

});

Route::prefix('api/v3')->group(function () {

    Route::prefix(('core'))->group(function () {
        Route::get('/tax/calculate', [CoreControllerV3::class, 'calculateTax']);
        Route::middleware('auth:api')->group(function () {
            Route::controller(CoreControllerV3::class)->group(function () {
                Route::post('/store', 'store')->name('storeProductV3');
                Route::post('/buy-package', 'getPackageCheckout');
            });

            Route::controller(CoreControllerV3::class)->group(function () {
                Route::get('/package-confirmation', 'PackageConfirmation');
            });


            Route::prefix('shipstation')->group(function () {
                Route::post('create-order', [CoreControllerV3::class, 'createShipstationOrder']);
                Route::post('sync-orders-statuses', [CoreControllerV3::class, 'syncShipstationOrdersStatuses']);
                Route::post('create-webhook', [CoreControllerV3::class, 'createShipStationWebhook']);
                Route::post('order-webhook', [CoreControllerV3::class, 'handleTrackEvent']);
            });
        });
        Route::get('product-profiles', [CheckoutControllerV3::class, 'profiles']);
        Route::post('/checkout', [CheckoutControllerV3::class,'getCheckout']);
        Route::post('/payment-received', [CheckoutControllerV3::class, 'paymentReceived']);
        Route::post('/new-payment-received', [CheckoutControllerV3::class, 'paymentReceivedV2']);
        Route::post('/buy-now',[CheckoutControllerV3::class,'buyNow']);
        Route::get('order-confirmation', [CheckoutControllerV3::class, 'OrderConfirmation']);
        Route::post('crypto-checkout',[CheckoutControllerV3::class,'cryptoCheckout']);
        Route::post('new-checkout-v3',[CheckoutControllerV3::class,'newCheckoutV3']);
        Route::post('/apply-coupon-v3',[CheckoutControllerV3::class,'applyCouponV3']);
        Route::get('low-stock-email/{orderId}', [CheckoutControllerV3::class, 'sendLowStockEmail']);
        Route::post('order-summary', [CheckoutControllerV3::class, 'getOrderSummary']);
        Route::prefix('shipstation')->group(function () {
            Route::post('create-order', [CoreControllerV3::class, 'createShipstationOrder']);
        });
        Route::post('/checkout-shipping-rates', [CheckoutControllerV3::class, 'getRatesOnCheckout']);
        Route::post('/apply-coupon',[CheckoutControllerV3::class,'applyCoupon']);
        Route::post('new-checkout',[CheckoutControllerV3::class,'newCheckout']);

        Route::prefix('usps')->group(function () {
            Route::post('get-rates', [CoreControllerV3::class, 'getUspsRates']);
            Route::post('validate-address', [CoreControllerV3::class, 'uspsValidateAddress']);
        });
        Route::post('/crypto-webhook', [CheckoutControllerV3::class, 'cryptoWebhook']);
        Route::post('apply_collective_coupon',[CheckoutControllerV3::class,'applyCollectiveCoupon']);

        // PayPal Checkout Routes (V3)
        Route::prefix('paypal')->group(function () {
            Route::get('/client-id', [CheckoutControllerV3::class, 'getPayPalClientId']);
            Route::post('/create-order', [CheckoutControllerV3::class, 'createPayPalOrder']);
            Route::post('/capture-order', [CheckoutControllerV3::class, 'capturePayPalOrder']);
            Route::post('/webhook', [CheckoutControllerV3::class, 'paypalWebhook']);
            Route::get('/order-details', [CheckoutControllerV3::class, 'getPayPalOrderDetails']);
            Route::get('/order-confirmation', [CheckoutControllerV3::class, 'paypalOrderConfirmation']);
        });
    });

});

// V4 Routes
Route::prefix('api/v4')->group(function () {

    Route::prefix(('core'))->group(function () {
        Route::get('/tax/calculate', [CoreControllerV4::class, 'calculateTax']);
        Route::middleware('auth:api')->group(function () {
            Route::controller(CoreControllerV4::class)->group(function () {
                Route::post('/store', 'store')->name('storeProductV4');
                Route::post('/buy-package', 'getPackageCheckout');
            });

            Route::controller(CoreControllerV4::class)->group(function () {
                Route::get('/package-confirmation', 'PackageConfirmation');
            });


            Route::prefix('shipstation')->group(function () {
                Route::post('create-order', [CoreControllerV4::class, 'createShipstationOrder']);
                Route::post('sync-orders-statuses', [CoreControllerV4::class, 'syncShipstationOrdersStatuses']);
                Route::post('create-webhook', [CoreControllerV4::class, 'createShipStationWebhook']);
                Route::post('order-webhook', [CoreControllerV4::class, 'handleTrackEvent']);
            });
        });
        Route::get('product-profiles', [CheckoutControllerV4::class, 'profiles']);
        Route::post('/checkout', [CheckoutControllerV4::class,'getCheckout']);
        Route::post('/payment-received', [CheckoutControllerV4::class, 'paymentReceived']);
        Route::post('/new-payment-received', [CheckoutControllerV4::class, 'paymentReceivedV2']);
        Route::post('/buy-now',[CheckoutControllerV4::class,'buyNow']);
        Route::get('order-confirmation', [CheckoutControllerV4::class, 'OrderConfirmation']);
        Route::post('crypto-checkout',[CheckoutControllerV4::class,'cryptoCheckout']);
        Route::post('new-checkout-v4',[CheckoutControllerV4::class,'newCheckoutV3']);
        Route::post('/apply-coupon-v4',[CheckoutControllerV4::class,'applyCouponV3']);
        Route::get('low-stock-email/{orderId}', [CheckoutControllerV4::class, 'sendLowStockEmail']);
        Route::post('order-summary', [CheckoutControllerV4::class, 'getOrderSummary']);
        Route::prefix('shipstation')->group(function () {
            Route::post('create-order', [CoreControllerV4::class, 'createShipstationOrder']);
        });
        Route::post('/checkout-shipping-rates', [CheckoutControllerV4::class, 'getRatesOnCheckout']);
        Route::post('/apply-coupon',[CheckoutControllerV4::class,'applyCoupon']);
        Route::post('new-checkout',[CheckoutControllerV4::class,'newCheckout']);

        Route::prefix('usps')->group(function () {
            Route::post('get-rates', [CoreControllerV4::class, 'getUspsRates']);
            Route::post('validate-address', [CoreControllerV4::class, 'uspsValidateAddress']);
        });
        Route::post('/crypto-webhook', [CheckoutControllerV4::class, 'cryptoWebhook']);
        Route::post('apply_collective_coupon',[CheckoutControllerV4::class,'applyCollectiveCoupon']);
    });

});
