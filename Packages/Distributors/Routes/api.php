<?php

namespace Packages\Distributors\Routes;

use Illuminate\Support\Facades\Route;
use Packages\Distributors\Http\Controllers\V2\DistributorController as DistributorControllerV2;
use Packages\Distributors\Http\Controllers\V3\DistributorController as DistributorControllerV3;

Route::prefix('api/v2')->group(function () {

    Route::prefix(('distributor'))->group(function () {
        Route::middleware('auth:api')->group(function () {
            Route::get('/list', [DistributorControllerV2::class, 'list']);
            Route::post('/create', [DistributorControllerV2::class, 'create']);
            Route::post('/update/{id}', [DistributorControllerV2::class, 'update']);
            Route::delete('/delete', [DistributorControllerV2::class, 'delete']);
            Route::get('/list-all', [DistributorControllerV2::class, 'getAllDistributors']);
            Route::post('register-brand', [DistributorControllerV2::class, 'registerBrand']);
            Route::get('/detail/{id}', [DistributorControllerV2::class, 'getDistributorDetail']);
            Route::get('/brands-list', [DistributorControllerV2::class, 'getBrandDistributors']);
            Route::post('assign-brand', [DistributorControllerV2::class, 'assignBrandToDistributor']);
            Route::post('change-status', [DistributorControllerV2::class, 'changeDistributorStatus']);
            Route::post('document/change-status', [DistributorControllerV2::class, 'changeDocumentStatus']);
        });
        Route::get('/brands', [DistributorControllerV2::class, 'getBrands']);
    });
});

Route::prefix('api/v3')->group(function () {

    Route::prefix(('distributor'))->group(function () {
        Route::middleware('auth:api')->group(function () {
            Route::get('/list', [DistributorControllerV3::class, 'list']);
            Route::post('/create', [DistributorControllerV3::class, 'create']);
            Route::post('/update/{id}', [DistributorControllerV3::class, 'update']);
            Route::delete('/delete', [DistributorControllerV3::class, 'delete']);
            Route::get('/list-all', [DistributorControllerV3::class, 'getAllDistributors']);
            Route::post('register-brand', [DistributorControllerV3::class, 'registerBrand']);
            Route::get('/detail/{id}', [DistributorControllerV3::class, 'getDistributorDetail']);
            Route::get('/brands-list', [DistributorControllerV3::class, 'getBrandDistributors']);
            Route::post('assign-brand', [DistributorControllerV3::class, 'assignBrandToDistributor']);
            Route::post('change-status', [DistributorControllerV3::class, 'changeDistributorStatus']);
            Route::post('document/change-status', [DistributorControllerV3::class, 'changeDocumentStatus']);
        });
        Route::get('/brands', [DistributorControllerV3::class, 'getBrands']);
    });
});

// V4 Routes
use Packages\Distributors\Http\Controllers\V4\DistributorController as DistributorControllerV4;

Route::prefix('api/v4')->group(function () {

    Route::prefix(('distributor'))->group(function () {
        Route::middleware('auth:api')->group(function () {
            Route::get('/list', [DistributorControllerV4::class, 'list']);
            Route::post('/create', [DistributorControllerV4::class, 'create']);
            Route::post('/update/{id}', [DistributorControllerV4::class, 'update']);
            Route::delete('/delete', [DistributorControllerV4::class, 'delete']);
            Route::get('/list-all', [DistributorControllerV4::class, 'getAllDistributors']);
            Route::post('register-brand', [DistributorControllerV4::class, 'registerBrand']);
            Route::get('/detail/{id}', [DistributorControllerV4::class, 'getDistributorDetail']);
            Route::get('/brands-list', [DistributorControllerV4::class, 'getBrandDistributors']);
            Route::post('assign-brand', [DistributorControllerV4::class, 'assignBrandToDistributor']);
            Route::post('change-status', [DistributorControllerV4::class, 'changeDistributorStatus']);
            Route::post('document/change-status', [DistributorControllerV4::class, 'changeDocumentStatus']);
        });
        Route::get('/brands', [DistributorControllerV4::class, 'getBrands']);
    });
});
