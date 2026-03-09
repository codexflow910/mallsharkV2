<?php

namespace Packages\Admin\Routes;

use Illuminate\Support\Facades\Route;
use Packages\Admin\Http\Controllers\V2\AdminController as AdminControllerV2;
use Packages\Admin\Http\Controllers\V2\CategoryTagController as CategoryTagControllerV2;
use Packages\Admin\Http\Controllers\V3\AdminController as AdminControllerV3;
use Packages\Admin\Http\Controllers\V3\CategoryTagController as CategoryTagControllerV3;

Route::prefix('api/v2')->group(function () {

    Route::prefix(('admin'))->group(function () {
        Route::middleware('auth:api')->group(function () {
            
            Route::controller(AdminControllerV2::class)->group(function () {

                Route::post('/store', 'store')->name('storeProduct');
                Route::get('/store-deduction/{storeId}', 'getByStoreId')->name('getByStoreId');
                Route::post('/store-deduction', 'createOrUpdate')->name('createOrUpdate');
            });

            Route::controller(CategoryTagControllerV2::class)->group(function () {
                /**category tags */
                Route::prefix(('category'))->group(function () {
                    Route::get('/{id}/tags', 'getCategoryTags')->name('category-tags'); 
                    Route::post('/create-tag', 'createTag')->name('create-tag');
                    Route::post('/delete-tag/{id}', 'deleteTag')->name('delete-tag');
                });
            });
        });
    });
});

Route::prefix('api/v3')->group(function () {

    Route::prefix(('admin'))->group(function () {
        Route::middleware('auth:api')->group(function () {

            Route::controller(AdminControllerV3::class)->group(function () {

                Route::post('/store', 'store')->name('v3.storeProduct');
                Route::get('/store-deduction/{storeId}', 'getByStoreId')->name('v3.getByStoreId');
                Route::post('/store-deduction', 'createOrUpdate')->name('v3.createOrUpdate');
            });

            Route::controller(CategoryTagControllerV3::class)->group(function () {
                /**category tags */
                Route::prefix(('category'))->group(function () {
                    Route::get('/{id}/tags', 'getCategoryTags')->name('v3.category-tags');
                    Route::post('/create-tag', 'createTag')->name('v3.create-tag');
                    Route::post('/delete-tag/{id}', 'deleteTag')->name('v3.delete-tag');
                });
            });
        });
    });
});

// V4 Routes
use Packages\Admin\Http\Controllers\V4\AdminController as AdminControllerV4;
use Packages\Admin\Http\Controllers\V4\CategoryTagController as CategoryTagControllerV4;

Route::prefix('api/v4')->group(function () {

    Route::prefix(('admin'))->group(function () {
        Route::middleware('auth:api')->group(function () {

            Route::controller(AdminControllerV4::class)->group(function () {

                Route::post('/store', 'store')->name('v4.storeProduct');
                Route::get('/store-deduction/{storeId}', 'getByStoreId')->name('v4.getByStoreId');
                Route::post('/store-deduction', 'createOrUpdate')->name('v4.createOrUpdate');
            });

            Route::controller(CategoryTagControllerV4::class)->group(function () {
                /**category tags */
                Route::prefix(('category'))->group(function () {
                    Route::get('/{id}/tags', 'getCategoryTags')->name('v4.category-tags');
                    Route::post('/create-tag', 'createTag')->name('v4.create-tag');
                    Route::post('/delete-tag/{id}', 'deleteTag')->name('v4.delete-tag');
                });
            });
        });
    });
});
