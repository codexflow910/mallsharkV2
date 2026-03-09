<?php

namespace Packages\Home\Routes;

use Illuminate\Support\Facades\Route;
use Packages\Home\Http\Controllers\HomeController;
use Packages\Home\Http\Controllers\V2\HomeController as HomeControllerV2;
use Packages\Home\Http\Controllers\V3\HomeController as HomeControllerV3;


Route::prefix('api/v2')->group(function () {

    Route::prefix('home')->group(function () {
        Route::controller(HomeControllerV2::class)->group(function () {
            Route::get('/banners', 'getHomeBanners');
            Route::get('/categories', 'getHomeCategories');
            Route::get('/sidebar-categories', 'getSideBarCategories');
            Route::get('seo-heading/{slug}','categoryHeadingSeo');
            Route::post('cookies','storeCookies');
            Route::post('recomended_products','recommendedProducts');
            Route::get('tiktok-videos','gettiktokVideos');

            Route::middleware('auth:api')->group(function () {
                Route::get('get-categories', 'getCategories');
            });
        });
    });
});

Route::prefix('api/v3')->group(function () {

    Route::prefix('home')->group(function () {
        Route::controller(HomeControllerV3::class)->group(function () {
            Route::get('/banners', 'getHomeBanners');
            Route::get('/categories', 'getHomeCategories');
            Route::get('/sidebar-categories', 'getSideBarCategories');
            Route::get('seo-heading/{slug}','categoryHeadingSeo');
            Route::post('cookies','storeCookies');
            Route::post('recomended_products','recommendedProducts');


            Route::middleware('auth:api')->group(function () {
                Route::get('get-categories', 'getCategories');
            });
        });
    });
});

// V4 Routes
use Packages\Home\Http\Controllers\V4\HomeController as HomeControllerV4;

Route::prefix('api/v4')->group(function () {

    Route::prefix('home')->group(function () {
        Route::controller(HomeControllerV4::class)->group(function () {
            Route::get('/banners', 'getHomeBanners');
            Route::get('/categories', 'getHomeCategories');
            Route::get('/sidebar-categories', 'getSideBarCategories');
            Route::get('seo-heading/{slug}','categoryHeadingSeo');
            Route::post('cookies','storeCookies');
            Route::post('recomended_products','recommendedProducts');


            Route::middleware('auth:api')->group(function () {
                Route::get('get-categories', 'getCategories');
            });
        });
    });
});
