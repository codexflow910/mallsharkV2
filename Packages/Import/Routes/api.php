<?php

namespace Packages\Import\Routes;

use Illuminate\Support\Facades\Route;
use Packages\Import\Http\Controllers\CsvController;
use Packages\Import\Http\Controllers\ShopifyController;
use Packages\Import\Http\Controllers\V2\CsvController as CsvControllerV2;
use Packages\Import\Http\Controllers\V2\ShopifyController as ShopifyControllerV2;
use Packages\Import\Http\Controllers\V2\ExcelController;
use Packages\Import\Http\Controllers\V3\CsvController as CsvControllerV3;
use Packages\Import\Http\Controllers\V3\ShopifyController as ShopifyControllerV3;
use Packages\Import\Http\Controllers\V3\ExcelController as ExcelControllerV3;

Route::prefix('api/v2')->group(function () {
    Route::middleware('auth:api')->group(function () {
        Route::prefix(('csv/products'))->group(function () {
            Route::controller(CsvControllerV2::class)->group(function () {
                Route::post('/bulk-update', 'bulkUpdate')->name('bulk-update');
            });
        });
        Route::prefix('excel/products')->group(function () {
            Route::controller(ExcelController::class)->group(function () {
                Route::post('/download', 'download')->name('excel-download');
                    Route::post('/bulk-upload', 'bulkUpload')->name('excel-bulk-upload');
            });
        });
        Route::prefix(('/shopify'))->group(function () {
            Route::controller(ShopifyControllerV2::class)->group(function () {
                // Old apps (pre-Jan 2026) - direct access token
                Route::post('/connect', 'connectShopify')->name('connect-shopify');

                // New apps (post-Jan 2026) - OAuth Authorization Code flow
                Route::post('/oauth/init', 'oauthInit')->name('shopify-oauth-init');

                Route::post('/disconnect', 'disconnectShopify')->name('disconnect-shopify');
                Route::get('/details', 'getShopDetails')->name('get-shopify-details');
                Route::post('/validate-access-token', 'validateAccessToken')->name('validate-access-token');
                Route::post('/import-products', 'importAllProducts')->name('import-products');
                Route::post('/import-products/save', 'saveImportedProducts')->name('save-imported-products');
                Route::get('/webhooks-list', 'webhooksList')->name('webhooks-list');
                Route::post('/webhooks-delete/{id}', 'deleteWebhook')->name('delete');

                Route::post('/update-inventory-on-mallshark-order', 'orderPlacedInventorySyncToShopify')->name('sync-inventory-to-shopify');
                Route::post('/import-categories', 'importAllCategories')->name('import-categories');
            });
        });
    });
    Route::prefix(('/shopify'))->group(function () {
        Route::controller(ShopifyControllerV2::class)->group(function () {
            // OAuth callback - Shopify redirects here after user approves (no auth needed)
            Route::get('/oauth/callback', 'oauthCallback')->name('shopify-oauth-callback');

            // Webhook endpoints - called by Shopify (no auth needed)
            Route::post('/update-inventory-on-shopify-order', 'orderPlacedInventorySyncToMallshark')->name('sync-inventory-to-mallshark');

            Route::post('/restore-inventory-on-mallshark-order-cancel', 'restoreInventoryToShopifyOnOrderCancel')->name('restore-inventory-to-shopify');
            Route::post('/update-inventory-on-shopify-order-cancel', 'orderCancelledInventorySyncToMallshark')->name('sync-inventory-to-mallshark-cancel');
            Route::post('/update-product-on-shopify-update', 'updateImportedProducts')->name('update-product-on-shopify-update');
            Route::post('/update-product-on-shopify-delete', 'updateDeletedProducts')->name('update-product-on-shopify-delete');
        });
    });
});

Route::prefix('api/v3')->group(function () {
    Route::middleware('auth:api')->group(function () {
        Route::prefix(('csv/products'))->group(function () {
            Route::controller(CsvControllerV3::class)->group(function () {
                Route::post('/bulk-update', 'bulkUpdate')->name('v3.bulk-update');
            });
        });
        Route::prefix('excel/products')->group(function () {
            Route::controller(ExcelControllerV3::class)->group(function () {
                Route::post('/download', 'download')->name('v3.excel-download');
                    Route::post('/bulk-upload', 'bulkUpload')->name('v3.excel-bulk-upload');
            });
        });
        Route::prefix(('/shopify'))->group(function () {
            Route::controller(ShopifyControllerV3::class)->group(function () {
                // Old apps (pre-Jan 2026) - direct access token
                Route::post('/connect', 'connectShopify')->name('v3.connect-shopify');

                // New apps (post-Jan 2026) - OAuth Authorization Code flow
                Route::post('/oauth/init', 'oauthInit')->name('v3.shopify-oauth-init');

                Route::post('/disconnect', 'disconnectShopify')->name('v3.disconnect-shopify');
                Route::get('/details', 'getShopDetails')->name('v3.get-shopify-details');
                Route::post('/validate-access-token', 'validateAccessToken')->name('v3.validate-access-token');
                Route::post('/import-products', 'importAllProducts')->name('v3.import-products');
                Route::post('/import-products/save', 'saveImportedProducts')->name('v3.save-imported-products');
                Route::get('/webhooks-list', 'webhooksList')->name('v3.webhooks-list');
                Route::post('/webhooks-delete/{id}', 'deleteWebhook')->name('v3.delete');

                Route::post('/update-inventory-on-mallshark-order', 'orderPlacedInventorySyncToShopify')->name('v3.sync-inventory-to-shopify');
                Route::post('/import-categories', 'importAllCategories')->name('v3.import-categories');
            });
        });
    });
    Route::prefix(('/shopify'))->group(function () {
        Route::controller(ShopifyControllerV3::class)->group(function () {
            // OAuth callback - Shopify redirects here after user approves (no auth needed)
            Route::get('/oauth/callback', 'oauthCallback')->name('v3.shopify-oauth-callback');

            // Webhook endpoints - called by Shopify (no auth needed)
            Route::post('/update-inventory-on-shopify-order', 'orderPlacedInventorySyncToMallshark')->name('v3.sync-inventory-to-mallshark');

            Route::post('/restore-inventory-on-mallshark-order-cancel', 'restoreInventoryToShopifyOnOrderCancel')->name('v3.restore-inventory-to-shopify');
            Route::post('/update-inventory-on-shopify-order-cancel', 'orderCancelledInventorySyncToMallshark')->name('v3.sync-inventory-to-mallshark-cancel');
            Route::post('/update-product-on-shopify-update', 'updateImportedProducts')->name('v3.update-product-on-shopify-update');
            Route::post('/update-product-on-shopify-delete', 'updateDeletedProducts')->name('v3.update-product-on-shopify-delete');
        });
    });
});

// V4 Routes
use Packages\Import\Http\Controllers\V4\CsvController as CsvControllerV4;
use Packages\Import\Http\Controllers\V4\ShopifyController as ShopifyControllerV4;
use Packages\Import\Http\Controllers\V4\ExcelController as ExcelControllerV4;

Route::prefix('api/v4')->group(function () {
    Route::middleware('auth:api')->group(function () {
        Route::prefix(('csv/products'))->group(function () {
            Route::controller(CsvControllerV4::class)->group(function () {
                Route::post('/bulk-update', 'bulkUpdate')->name('v4.bulk-update');
            });
        });
        Route::prefix('excel/products')->group(function () {
            Route::controller(ExcelControllerV4::class)->group(function () {
                Route::post('/download', 'download')->name('v4.excel-download');
                    Route::post('/bulk-upload', 'bulkUpload')->name('v4.excel-bulk-upload');
            });
        });
        Route::prefix(('/shopify'))->group(function () {
            Route::controller(ShopifyControllerV4::class)->group(function () {
                // Old apps (pre-Jan 2026) - direct access token
                Route::post('/connect', 'connectShopify')->name('v4.connect-shopify');

                // New apps (post-Jan 2026) - OAuth Authorization Code flow
                Route::post('/oauth/init', 'oauthInit')->name('v4.shopify-oauth-init');

                Route::post('/disconnect', 'disconnectShopify')->name('v4.disconnect-shopify');
                Route::get('/details', 'getShopDetails')->name('v4.get-shopify-details');
                Route::post('/validate-access-token', 'validateAccessToken')->name('v4.validate-access-token');
                Route::post('/import-products', 'importAllProducts')->name('v4.import-products');
                Route::post('/import-products/save', 'saveImportedProducts')->name('v4.save-imported-products');
                Route::get('/webhooks-list', 'webhooksList')->name('v4.webhooks-list');
                Route::post('/webhooks-delete/{id}', 'deleteWebhook')->name('v4.delete');

                Route::post('/update-inventory-on-mallshark-order', 'orderPlacedInventorySyncToShopify')->name('v4.sync-inventory-to-shopify');
                Route::post('/import-categories', 'importAllCategories')->name('v4.import-categories');
            });
        });
    });
    Route::prefix(('/shopify'))->group(function () {
        Route::controller(ShopifyControllerV4::class)->group(function () {
            // OAuth callback - Shopify redirects here after user approves (no auth needed)
            Route::get('/oauth/callback', 'oauthCallback')->name('v4.shopify-oauth-callback');

            // Webhook endpoints - called by Shopify (no auth needed)
            Route::post('/update-inventory-on-shopify-order', 'orderPlacedInventorySyncToMallshark')->name('v4.sync-inventory-to-mallshark');

            Route::post('/restore-inventory-on-mallshark-order-cancel', 'restoreInventoryToShopifyOnOrderCancel')->name('v4.restore-inventory-to-shopify');
            Route::post('/update-inventory-on-shopify-order-cancel', 'orderCancelledInventorySyncToMallshark')->name('v4.sync-inventory-to-mallshark-cancel');
            Route::post('/update-product-on-shopify-update', 'updateImportedProducts')->name('v4.update-product-on-shopify-update');
            Route::post('/update-product-on-shopify-delete', 'updateDeletedProducts')->name('v4.update-product-on-shopify-delete');
        });
    });
});
