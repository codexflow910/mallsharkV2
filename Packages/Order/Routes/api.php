<?php

namespace Packages\Order\Routes;

use Illuminate\Support\Facades\Route;
use Packages\Order\Http\Controllers\OrderController;
use Packages\Order\Http\Controllers\V2\OrderController as OrderControllerV2;
use Packages\Order\Http\Controllers\V3\OrderController as OrderControllerV3;

Route::prefix('api/v2')->group(function () {

    Route::prefix(('orders'))->group(function () {
        Route::middleware('auth:api')->group(function () {
            Route::controller(OrderControllerV2::class)->group(function () {
                Route::get('buyer-orders', 'buyerOrders')->name('buyer-orders');
                Route::get('buyer-orders-paginate', 'buyerOrdersPaginate')->name('buyer-orders-paginate');
                Route::get('brand-orders/', 'getBrandOrdersPaginate')->name('brand-orders')->middleware('userPermission:manage-orders-store');
                Route::get('brand-order-details/{id}', 'brandOrderdetails')->name('brand-orders-details');
                Route::get('pdf/{id}', 'orderPdf')->name('order.pdf');
                Route::get('brand-order-details/{id}', 'brandOrderdetails')->name('brand-orders-details');
                Route::get('order-pdf',  'getOrderPdf')->name('order.pdf');
                Route::get('store-orders', 'getStoreOrders')->name('getStoreOrders');
                Route::post('brand-orders/', [OrderControllerV2::class, 'updateOrderStatus'])->middleware('userPermission:manage-orders-store');
                Route::post('/refund', [OrderControllerV2::class, 'refundOrder']);
                Route::post('reject_refund',[OrderControllerV2::class,'rejectRefund'])->middleware('userPermission:manage-orders-store');
                Route::post('cancel_buyer',[OrderControllerV2::class,'cancelBuyerOrder']);
                Route::get('brand-orders-no-paginate', [OrderControllerV2::class, 'getBrandOrders'])->middleware('userPermission:manage-orders-store');
                Route::get('export-orders/{store_id}', [OrderControllerV2::class, 'exportOrders']);
                Route::post('/edit-order-shipping-address', 'editOrderShippingAddress');
                Route::post('/create-address-ticket', 'createZendeskTicketForAddress');
                Route::post('usps-ship-order', 'shipUspsOrder')->middleware('userPermission:manage-orders-store');
            });
            Route::post('authenticate',[OrderControllerV2::class,'authenticate']);
            Route::post('/fetch-fedex-rates',[OrderControllerV2::class,'fetchFedExOrderRate']);
            Route::post('/ship-fedex-order',[OrderControllerV2::class,'shipFedExOrder']);
            Route::post('/print-fedex-label',[OrderControllerV2::class,'printLabel']);
            Route::post('/track-fedex-order',[OrderControllerV2::class,'trackFedExOrder']);
            Route::post('/edit-order-note', [OrderControllerV2::class, 'editOrderNote']);
            Route::post('/edit-item-tracking', [OrderControllerV2::class, 'editItemTracking'])->middleware('userPermission:manage-orders-store');
            Route::post('/send-wallet-address-email', [OrderControllerV2::class, 'sendWalletAddressEmail']);
            Route::post('/items-mark-refunded', [OrderControllerV2::class, 'markItemsAsRefunded']);
        });
        Route::post('update-order-status', [OrderControllerV2::class, 'updateOrdersStatus']);
    });
});

Route::prefix('api/v3')->group(function () {

    Route::prefix(('orders'))->group(function () {
        Route::middleware('auth:api')->group(function () {
            Route::controller(OrderControllerV3::class)->group(function () {
                Route::get('buyer-orders', 'buyerOrders')->name('v3.buyer-orders');
                Route::get('buyer-orders-paginate', 'buyerOrdersPaginate')->name('v3.buyer-orders-paginate');
                Route::get('brand-orders/', 'getBrandOrdersPaginate')->name('v3.brand-orders')->middleware('userPermission:manage-orders-store');
                Route::get('brand-order-details/{id}', 'brandOrderdetails')->name('v3.brand-orders-details');
                Route::get('pdf/{id}', 'orderPdf')->name('v3.order.pdf');
                Route::get('brand-order-details/{id}', 'brandOrderdetails')->name('v3.brand-orders-details');
                Route::get('order-pdf',  'getOrderPdf')->name('v3.order.pdf');
                Route::get('store-orders', 'getStoreOrders')->name('v3.getStoreOrders');
                Route::post('brand-orders/', [OrderControllerV3::class, 'updateOrderStatus'])->middleware('userPermission:manage-orders-store');
                Route::post('/refund', [OrderControllerV3::class, 'refundOrder']);
                Route::post('reject_refund',[OrderControllerV3::class,'rejectRefund'])->middleware('userPermission:manage-orders-store');
                Route::post('cancel_buyer',[OrderControllerV3::class,'cancelBuyerOrder']);
                Route::get('brand-orders-no-paginate', [OrderControllerV3::class, 'getBrandOrders'])->middleware('userPermission:manage-orders-store');
                Route::get('export-orders/{store_id}', [OrderControllerV3::class, 'exportOrders']);
                Route::post('/edit-order-shipping-address', 'editOrderShippingAddress');
                Route::post('/create-address-ticket', 'createZendeskTicketForAddress');
                Route::post('usps-ship-order', 'shipUspsOrder')->middleware('userPermission:manage-orders-store');
            });
            Route::post('authenticate',[OrderControllerV3::class,'authenticate']);
            Route::post('/fetch-fedex-rates',[OrderControllerV3::class,'fetchFedExOrderRate']);
            Route::post('/ship-fedex-order',[OrderControllerV3::class,'shipFedExOrder']);
            Route::post('/print-fedex-label',[OrderControllerV3::class,'printLabel']);
            Route::post('/track-fedex-order',[OrderControllerV3::class,'trackFedExOrder']);
            Route::post('/edit-order-note', [OrderControllerV3::class, 'editOrderNote']);
            Route::post('/edit-item-tracking', [OrderControllerV3::class, 'editItemTracking'])->middleware('userPermission:manage-orders-store');
            Route::post('/send-wallet-address-email', [OrderControllerV3::class, 'sendWalletAddressEmail']);
            Route::post('/items-mark-refunded', [OrderControllerV3::class, 'markItemsAsRefunded']);
        });
        Route::post('update-order-status', [OrderControllerV3::class, 'updateOrdersStatus']);
    });
});

// V4 Routes
use Packages\Order\Http\Controllers\V4\OrderController as OrderControllerV4;

Route::prefix('api/v4')->group(function () {

    Route::prefix(('orders'))->group(function () {
        Route::middleware('auth:api')->group(function () {
            Route::controller(OrderControllerV4::class)->group(function () {
                Route::get('buyer-orders', 'buyerOrders')->name('v4.buyer-orders');
                Route::get('buyer-orders-paginate', 'buyerOrdersPaginate')->name('v4.buyer-orders-paginate');
                Route::get('brand-orders/', 'getBrandOrdersPaginate')->name('v4.brand-orders')->middleware('userPermission:manage-orders-store');
                Route::get('brand-order-details/{id}', 'brandOrderdetails')->name('v4.brand-orders-details');
                Route::get('pdf/{id}', 'orderPdf')->name('v4.order.pdf');
                Route::get('brand-order-details/{id}', 'brandOrderdetails')->name('v4.brand-orders-details');
                Route::get('order-pdf',  'getOrderPdf')->name('v4.order.pdf');
                Route::get('store-orders', 'getStoreOrders')->name('v4.getStoreOrders');
                Route::post('brand-orders/', [OrderControllerV4::class, 'updateOrderStatus'])->middleware('userPermission:manage-orders-store');
                Route::post('/refund', [OrderControllerV4::class, 'refundOrder']);
                Route::post('reject_refund',[OrderControllerV4::class,'rejectRefund'])->middleware('userPermission:manage-orders-store');
                Route::post('cancel_buyer',[OrderControllerV4::class,'cancelBuyerOrder']);
                Route::get('brand-orders-no-paginate', [OrderControllerV4::class, 'getBrandOrders'])->middleware('userPermission:manage-orders-store');
                Route::get('export-orders/{store_id}', [OrderControllerV4::class, 'exportOrders']);
                Route::post('/edit-order-shipping-address', 'editOrderShippingAddress');
                Route::post('/create-address-ticket', 'createZendeskTicketForAddress');
                Route::post('usps-ship-order', 'shipUspsOrder')->middleware('userPermission:manage-orders-store');
            });
            Route::post('authenticate',[OrderControllerV4::class,'authenticate']);
            Route::post('/fetch-fedex-rates',[OrderControllerV4::class,'fetchFedExOrderRate']);
            Route::post('/ship-fedex-order',[OrderControllerV4::class,'shipFedExOrder']);
            Route::post('/print-fedex-label',[OrderControllerV4::class,'printLabel']);
            Route::post('/track-fedex-order',[OrderControllerV4::class,'trackFedExOrder']);
            Route::post('/edit-order-note', [OrderControllerV4::class, 'editOrderNote']);
            Route::post('/edit-item-tracking', [OrderControllerV4::class, 'editItemTracking'])->middleware('userPermission:manage-orders-store');
            Route::post('/send-wallet-address-email', [OrderControllerV4::class, 'sendWalletAddressEmail']);
            Route::post('/items-mark-refunded', [OrderControllerV4::class, 'markItemsAsRefunded']);
        });
        Route::post('update-order-status', [OrderControllerV4::class, 'updateOrdersStatus']);
    });
});
