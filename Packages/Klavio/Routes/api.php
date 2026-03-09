<?php

namespace Packages\Klavio\Routes;

use Illuminate\Support\Facades\Route;
use Packages\Klavio\Http\Controllers\KlavioController;
use Packages\Klavio\Http\Controllers\V2\KlavioController as KlavioControllerV2;
use Packages\Klavio\Http\Controllers\V3\KlavioController as KlavioControllerV3;


Route::prefix('api/v2')->group(function () {

    Route::prefix(('klavio'))->group(function () {

        Route::post('/subscribe', [KlavioControllerV2::class, 'subscribe'])->name('subscribe');
        Route::post('/create-profile', [KlavioControllerV2::class, 'createKlaviyoProfile'])->name('create-profile');
        Route::post('/add-to-klavio-list', [KlavioControllerV2::class, 'addToList'])->name('storeProduct');
        Route::post('/add-to-klavio-metrice', [KlavioControllerV2::class, 'addToMetrice'])->name('storeProduct');
        Route::get('/products', [KlavioControllerV2::class, 'getStoreProducts'])->name('store-product');
    });
});

Route::prefix('api/v3')->group(function () {

    Route::prefix(('klavio'))->group(function () {

        Route::post('/subscribe', [KlavioControllerV3::class, 'subscribe'])->name('v3.subscribe');
        Route::post('/create-profile', [KlavioControllerV3::class, 'createKlaviyoProfile'])->name('v3.create-profile');
        Route::post('/add-to-klavio-list', [KlavioControllerV3::class, 'addToList'])->name('v3.storeProduct');
        Route::post('/add-to-klavio-metrice', [KlavioControllerV3::class, 'addToMetrice'])->name('v3.storeProduct');
        Route::get('/products', [KlavioControllerV3::class, 'getStoreProducts'])->name('v3.store-product');
    });
});

// V4 Routes
use Packages\Klavio\Http\Controllers\V4\KlavioController as KlavioControllerV4;

Route::prefix('api/v4')->group(function () {

    Route::prefix(('klavio'))->group(function () {

        Route::post('/subscribe', [KlavioControllerV4::class, 'subscribe'])->name('v4.subscribe');
        Route::post('/create-profile', [KlavioControllerV4::class, 'createKlaviyoProfile'])->name('v4.create-profile');
        Route::post('/add-to-klavio-list', [KlavioControllerV4::class, 'addToList'])->name('v4.storeProduct');
        Route::post('/add-to-klavio-metrice', [KlavioControllerV4::class, 'addToMetrice'])->name('v4.storeProduct');
        Route::get('/products', [KlavioControllerV4::class, 'getStoreProducts'])->name('v4.store-product');
    });
});
