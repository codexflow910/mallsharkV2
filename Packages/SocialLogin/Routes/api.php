<?php

namespace Packages\SocialLogin\Routes;

use Illuminate\Support\Facades\Route;
use Packages\SocialLogin\Http\Controllers\SocialLoginController;
use Packages\SocialLogin\Http\Controllers\V2\SocialLoginController as SocialLoginControllerV2;
use Packages\SocialLogin\Http\Controllers\V3\SocialLoginController as SocialLoginControllerV3;

Route::prefix('api/v2')->group(function () {

    Route::prefix(('social/login'))->group(function () {
        Route::controller(SocialLoginControllerV2::class)->group(function () {

            Route::post('/facebook', 'facebookLogin')->name('facebook-login');
            Route::post('/google', 'googleLogin')->name('google-login');
            Route::post('/apple', 'appleLogin')->name('apple-login');
        });
    });
});

Route::prefix('api/v3')->group(function () {

    Route::prefix(('social/login'))->group(function () {
        Route::controller(SocialLoginControllerV3::class)->group(function () {

            Route::post('/facebook', 'facebookLogin')->name('v3.facebook-login');
            Route::post('/google', 'googleLogin')->name('v3.google-login');
            Route::post('/apple', 'appleLogin')->name('v3.apple-login');
        });
    });
});

// V4 Routes
use Packages\SocialLogin\Http\Controllers\V4\SocialLoginController as SocialLoginControllerV4;

Route::prefix('api/v4')->group(function () {

    Route::prefix(('social/login'))->group(function () {
        Route::controller(SocialLoginControllerV4::class)->group(function () {

            Route::post('/facebook', 'facebookLogin')->name('v4.facebook-login');
            Route::post('/google', 'googleLogin')->name('v4.google-login');
            Route::post('/apple', 'appleLogin')->name('v4.apple-login');
        });
    });
});
