<?php

namespace Packages\Product\Routes;

use Illuminate\Support\Facades\Route;
use Packages\Product\Http\Controllers\ProductController;
use Packages\Product\Http\Controllers\V2\ProductController as V2ProductController;
use Packages\Product\Http\Controllers\V3\ProductController as V3ProductController;

// ==========================
// API V2 Routes
// ==========================
Route::prefix('api/v2')->group(function () {
    Route::prefix('product')->group(function () {
        Route::middleware('auth:api')->group(function () {
            Route::controller(V2ProductController::class)->group(function () {
                Route::get('/{id}', 'show')->name('v2.getProduct')->middleware('userPermission:manage-products-store');
                Route::post('/update/{id}', 'update')->name('v2.updateProduct')->middleware('userPermission:manage-products-store');
                Route::post('/store', 'store')->name('v2.storeProduct')->middleware('userPermission:manage-products-store');
                Route::post('/upload-desc-image', 'uploadDescImage')->name('v2.uploadDescImage');
                Route::post('/change-status', 'changestatus')->name('v2.changestatus')->middleware('userPermission:manage-products-store');
                Route::post('/add-favorite', 'addToFavorite')->name('v2.add-to-favorite');
                Route::get('/get/favorite', 'getBuyerFavouriteProducts')->name('v2.get-favourite-products');
                Route::get('/no/paginate', 'getProducts')->middleware('userPermission:manage-products-store');
                Route::post('/upload-product-image', 'uploadProductImage')->name('v2.upload-product-image');
                Route::post('/delete-product-image', [V2ProductController::class, 'deleteProductImage']);
                Route::post('/update-shipping', 'updateProductShipping');
                Route::get('/disassociate-from-shopify/{productId}', 'disassociateFromShopify')->name('v2.disassociate-from-shopify');
                Route::post('/ai/rephrase', 'rephrase');
                Route::post('/tiers', 'storeProductTiers')->name('v2.storeProductTiers');
                Route::post('/variation-tiers', 'syncVariationTiers');
                Route::get('/preview/{slug}', 'productPreview')->name('v2.productPreview');
            });
        });

        Route::controller(V2ProductController::class)->group(function () {
            Route::get('/love/products', 'getLoveProducts');
            Route::get('/seo-heading/{slug}', 'productHeadingSeo');
            Route::get('/detail/{slug}', 'productDetail')->name('v2.productDetail');
            Route::get('/detail-seller/{slug}', 'productDetailSeller')->name('v2.productDetailSeller');
            Route::post('get-variations-info', 'getVariationsInfo');
            Route::get('/all/products-slug', 'getProductsSlug');
            Route::get('update/slug/{id}', 'updateProductSlug')->name('v2.updateProductSlug');
            Route::post('/assign-labels', 'assignLabels');
            Route::post('/assign-labels-to-store-products', 'assignLabelsToStoreProducts');
            Route::get('/all/products-title' , 'getProductsTitle');
            Route::get('/tiers/{productId}', 'getProductTiers');
            Route::post('/tiers/variation', 'syncVariationTiers');
            Route::post('/upload-image', 'uploadImage')->name('v2.uploadImages');
        });
    });
});

// ==========================
// API V3 Routes
// ==========================
Route::prefix('api/v3')->group(function () {
    Route::prefix('product')->group(function () {
        Route::middleware('auth:api')->group(function () {
            Route::controller(V3ProductController::class)->group(function () {
                Route::get('/{id}', 'show')->name('v3.getProduct')->middleware('userPermission:manage-products-store');
                Route::post('/update/{id}', 'update')->name('v3.updateProduct')->middleware('userPermission:manage-products-store');
                Route::post('/store', 'store')->name('v3.storeProduct')->middleware('userPermission:manage-products-store');
                Route::post('/upload-desc-image', 'uploadDescImage')->name('v3.uploadDescImage');
                Route::post('/change-status', 'changestatus')->name('v3.changestatus')->middleware('userPermission:manage-products-store');
                Route::post('/add-favorite', 'addToFavorite')->name('v3.add-to-favorite');
                Route::get('/get/favorite', 'getBuyerFavouriteProducts')->name('v3.get-favourite-products');
                Route::get('/no/paginate', 'getProducts')->middleware('userPermission:manage-products-store');
                Route::post('/upload-product-image', 'uploadProductImage')->name('v3.upload-product-image');
                Route::post('/delete-product-image', [V3ProductController::class, 'deleteProductImage']);
                Route::post('/update-shipping', 'updateProductShipping');
                Route::get('/disassociate-from-shopify/{productId}', 'disassociateFromShopify')->name('v3.disassociate-from-shopify');
                Route::post('/ai/rephrase', 'rephrase');
                Route::post('/tiers', 'storeProductTiers')->name('v3.storeProductTiers');
                Route::get('/preview/{slug}', 'productPreview')->name('v3.productPreview');
            });
        });

        Route::controller(V3ProductController::class)->group(function () {
            Route::get('/love/products', 'getLoveProducts');
            Route::get('/seo-heading/{slug}', 'productHeadingSeo');
            Route::get('/detail/{slug}', 'productDetail')->name('v3.productDetail');
            Route::get('/detail-seller/{slug}', 'productDetailSeller')->name('v3.productDetailSeller');
            Route::post('get-variations-info', 'getVariationsInfo');
            Route::get('/all/products-slug', 'getProductsSlug');
            Route::get('update/slug/{id}', 'updateProductSlug')->name('v3.updateProductSlug');
            Route::get('/tiers/{productId}', 'getProductTiers');
            Route::post('/tiers/variation', 'syncVariationTiers');
            Route::post('/assign-labels', 'assignLabels');
            Route::post('/assign-labels-to-store-products', 'assignLabelsToStoreProducts');
            Route::get('/all/products-title' , 'getProductsTitle');
            Route::post('/upload-image', 'uploadImage')->name('v3.uploadImages');
        });
    });
});

// V4 Routes
use Packages\Product\Http\Controllers\V4\ProductController as V4ProductController;

Route::prefix('api/v4')->group(function () {
    Route::prefix('product')->group(function () {
        Route::middleware('auth:api')->group(function () {
            Route::controller(V4ProductController::class)->group(function () {
                Route::get('/{id}', 'show')->name('v4.getProduct')->middleware('userPermission:manage-products-store');
                Route::post('/update/{id}', 'update')->name('v4.updateProduct')->middleware('userPermission:manage-products-store');
                Route::post('/store', 'store')->name('v4.storeProduct')->middleware('userPermission:manage-products-store');
                Route::post('/upload-desc-image', 'uploadDescImage')->name('v4.uploadDescImage');
                Route::post('/change-status', 'changestatus')->name('v4.changestatus')->middleware('userPermission:manage-products-store');
                Route::post('/add-favorite', 'addToFavorite')->name('v4.add-to-favorite');
                Route::get('/get/favorite', 'getBuyerFavouriteProducts')->name('v4.get-favourite-products');
                Route::get('/no/paginate', 'getProducts')->middleware('userPermission:manage-products-store');
                Route::post('/upload-product-image', 'uploadProductImage')->name('v4.upload-product-image');
                Route::post('/delete-product-image', [V4ProductController::class, 'deleteProductImage']);
                Route::post('/update-shipping', 'updateProductShipping');
                Route::get('/disassociate-from-shopify/{productId}', 'disassociateFromShopify')->name('v4.disassociate-from-shopify');
                Route::post('/ai/rephrase', 'rephrase');
                Route::post('/tiers', 'storeProductTiers')->name('v4.storeProductTiers');
                Route::get('/preview/{slug}', 'productPreview')->name('v4.productPreview');
            });
        });

        Route::controller(V4ProductController::class)->group(function () {
            Route::get('/love/products', 'getLoveProducts');
            Route::get('/seo-heading/{slug}', 'productHeadingSeo');
            Route::get('/detail/{slug}', 'productDetail')->name('v4.productDetail');
            Route::get('/detail-seller/{slug}', 'productDetailSeller')->name('v4.productDetailSeller');
            Route::post('get-variations-info', 'getVariationsInfo');
            Route::get('/all/products-slug', 'getProductsSlug');
            Route::get('update/slug/{id}', 'updateProductSlug')->name('v4.updateProductSlug');
            Route::get('/tiers/{productId}', 'getProductTiers');
            Route::post('/tiers/variation', 'syncVariationTiers');
            Route::post('/assign-labels', 'assignLabels');
            Route::post('/assign-labels-to-store-products', 'assignLabelsToStoreProducts');
            Route::get('/all/products-title' , 'getProductsTitle');
            Route::post('/upload-image', 'uploadImage')->name('v4.uploadImages');
        });
    });
});
