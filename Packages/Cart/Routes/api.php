<?php

namespace Packages\Cart\Routes;

use Illuminate\Support\Facades\Route;
use Packages\Cart\Http\Controllers\CartController;
use Packages\Cart\Http\Controllers\V2\CartController as CartControllerV2;
use Packages\Cart\Http\Controllers\V3\CartController as CartControllerV3;


Route::prefix('api/v2')->group(function () {

    Route::prefix(('cart'))->group(function () {
        // Route::middleware('auth:api')->group(function () {
        Route::controller(CartControllerV2::class)->group(function () {
            Route::Post('/', 'getCart');
            Route::Post('/v3', 'getCartV3');
            Route::Post('/add-v3', 'addToCartV3');
            Route::Post('/check-quantity', 'checkQuantity');
            Route::Post('/increase-quantity-v3', 'increaseQuantityV3');
            Route::Post('/decrease-quantity-v3', 'decreaseQuantityV3');
            Route::Post('/delete-v3', 'deleteCartV3');
            Route::Post('/show_hide', 'toggleCart');
        });
        // });
    });
});

Route::prefix('api/v3')->group(function () {

    Route::prefix(('cart'))->group(function () {
        // Route::middleware('auth:api')->group(function () {
        Route::controller(CartControllerV3::class)->group(function () {
            Route::Post('/', 'getCart');
            Route::Post('/v3', 'getCartV3');
            Route::Post('/add', 'addToCart');
            Route::Post('/add-v3', 'addToCartV3');
            Route::Post('/check-quantity', 'checkQuantity');
            Route::Post('/increase-quantity', 'increaseQuantity');
            Route::Post('/increase-quantity-v3', 'increaseQuantityV3');
            Route::Post('/decrease-quantity', 'decreaseQuantity');
            Route::Post('/decrease-quantity-v3', 'decreaseQuantityV3');
            Route::Post('/delete', 'deleteCart');
            Route::Post('/delete-v3', 'deleteCartV3');
            Route::Post('/show_hide', 'toggleCart');
        });
        // });
    });
});

// V4 Routes
use Packages\Cart\Http\Controllers\V4\CartController as CartControllerV4;

Route::prefix('api/v4')->group(function () {

    Route::prefix(('cart'))->group(function () {
        // Route::middleware('auth:api')->group(function () {
        Route::controller(CartControllerV4::class)->group(function () {
            Route::Post('/', 'getCart');
            Route::Post('/v3', 'getCartV3');
            Route::Post('/add', 'addToCart');
            Route::Post('/add-v3', 'addToCartV3');
            Route::Post('/check-quantity', 'checkQuantity');
            Route::Post('/increase-quantity', 'increaseQuantity');
            Route::Post('/increase-quantity-v3', 'increaseQuantityV3');
            Route::Post('/decrease-quantity', 'decreaseQuantity');
            Route::Post('/decrease-quantity-v3', 'decreaseQuantityV3');
            Route::Post('/delete', 'deleteCart');
            Route::Post('/delete-v3', 'deleteCartV3');
            Route::Post('/show_hide', 'toggleCart');
        });
        // });
    });
});
