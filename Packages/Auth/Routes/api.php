<?php

namespace Packages\Auth\Routes;

use Illuminate\Support\Facades\Route;
use Packages\Auth\Http\Controllers\AuthController;
use Packages\Auth\Http\Controllers\V2\AuthController as AuthControllerV2;
use Packages\Auth\Http\Controllers\V3\AuthController as AuthControllerV3;

Route::prefix('api/v2')->group(function () {

    Route::prefix(('user'))->group(function () {
        Route::controller(AuthControllerV2::class)->group(function () {

            Route::middleware('auth:api')->group(function () {
                Route::post('/change-password', 'changePassword')->name('change-password');
                Route::post('/logout', 'logout')->name('logout-user');
                Route::get('/profile', 'userProfile')->name('user-profile');
                Route::post('/update-profile', 'updateUserProfile')->name('update-user-profile');
                Route::post('/update-shipping-address', 'updateShippingAddress');
                Route::get('/analytics', 'analytics')->name('analytics');
                Route::post('/request-account-deletion', 'requestDeletion');
                Route::post('assign-to-store', 'assignUserToStore')->middleware('userPermission:assign-store');
                Route::post('assign-permissions', 'assignPermissionsToUser')->middleware('userPermission:assign-permissions');
                Route::get('store-accessors', 'getStoreAccessors')->middleware('userPermission:assign-permissions');
                Route::get('moderator-permissions', 'getModeratorPermissions');
                Route::delete('remove-store-accessor/{userId}', 'removeStoreAccessor')->middleware('userPermission:assign-permissions');
                Route::post('/customer-wallet-address','customerWalletAddress');
                Route::get('/get-wallet-address','getCustomerWalletAddress');
            });
            Route::post('/login', 'login')->name('login-user');
            Route::post('/register', 'register')->name('register-user');
            Route::get('/email-exist', 'checkEmailExist')->name('email-exist');
            Route::post('/email-verification', 'verifyEmail')->name('email-verification');
            Route::post('/forgot-password', 'forgotPassword')->name('forgot-password');
            Route::post('/match-password-otp', 'matchPasswordOtp')->name('match-password-otp');
            Route::post('/create-new-password', 'createNewPassword')->name('new-password');
            Route::post('/seller-login', 'sellerLogin')->name('login-seller');
            Route::post('/request-account-deletion', 'requestDeletion');
            Route::get('/seller-side-permissions', 'getSellerSidePermissions');
        });
    });
});

Route::prefix('api/v3')->group(function () {

    Route::prefix(('user'))->group(function () {
        Route::controller(AuthControllerV3::class)->group(function () {

            Route::middleware('auth:api')->group(function () {
                Route::post('/change-password', 'changePassword')->name('v3.change-password');
                Route::post('/logout', 'logout')->name('v3.logout-user');
                Route::get('/profile', 'userProfile')->name('v3.user-profile');
                Route::post('/update-profile', 'updateUserProfile')->name('v3.update-user-profile');
                Route::post('/update-shipping-address', 'updateShippingAddress');
                Route::get('/analytics', 'analytics')->name('v3.analytics');
                Route::post('/request-account-deletion', 'requestDeletion');
                Route::delete('/delete-account', 'deleteAccount')->name('v3.delete-account');
                Route::post('assign-to-store', 'assignUserToStore')->middleware('userPermission:assign-store');
                Route::post('assign-permissions', 'assignPermissionsToUser')->middleware('userPermission:assign-permissions');
                Route::get('store-accessors', 'getStoreAccessors')->middleware('userPermission:assign-permissions');
                Route::get('moderator-permissions', 'getModeratorPermissions');
                Route::delete('remove-store-accessor/{userId}', 'removeStoreAccessor')->middleware('userPermission:assign-permissions');
                Route::post('/customer-wallet-address','customerWalletAddress');
                Route::get('/get-wallet-address','getCustomerWalletAddress');
            });
            Route::post('/resend-otp', 'resendOtp')->name('v3.resend-otp');
            Route::post('/login', 'login')->name('v3.login-user');
            Route::post('/register', 'register')->name('v3.register-user');
            Route::get('/email-exist', 'checkEmailExist')->name('v3.email-exist');
            Route::post('/email-verification', 'verifyEmail')->name('v3.email-verification');
            Route::post('/forgot-password', 'forgotPassword')->name('v3.forgot-password');
            Route::post('/match-password-otp', 'matchPasswordOtp')->name('v3.match-password-otp');
            Route::post('/create-new-password', 'createNewPassword')->name('v3.new-password');
            Route::post('/seller-login', 'sellerLogin')->name('v3.login-seller');
            Route::post('/request-account-deletion', 'requestDeletion');
            Route::get('/seller-side-permissions', 'getSellerSidePermissions');
        });
    });
});

// V4 Routes
use Packages\Auth\Http\Controllers\V4\AuthController as AuthControllerV4;

Route::prefix('api/v4')->group(function () {

    Route::prefix(('user'))->group(function () {
        Route::controller(AuthControllerV4::class)->group(function () {

            Route::middleware('auth:api')->group(function () {
                Route::post('/change-password', 'changePassword')->name('v4.change-password');
                Route::post('/logout', 'logout')->name('v4.logout-user');
                Route::get('/profile', 'userProfile')->name('v4.user-profile');
                Route::post('/update-profile', 'updateUserProfile')->name('v4.update-user-profile');
                Route::post('/update-shipping-address', 'updateShippingAddress');
                Route::get('/analytics', 'analytics')->name('v4.analytics');
                Route::post('/request-account-deletion', 'requestDeletion');
                Route::delete('/delete-account', 'deleteAccount')->name('v4.delete-account');
                Route::post('assign-to-store', 'assignUserToStore')->middleware('userPermission:assign-store');
                Route::post('assign-permissions', 'assignPermissionsToUser')->middleware('userPermission:assign-permissions');
                Route::get('store-accessors', 'getStoreAccessors')->middleware('userPermission:assign-permissions');
                Route::get('moderator-permissions', 'getModeratorPermissions');
                Route::delete('remove-store-accessor/{userId}', 'removeStoreAccessor')->middleware('userPermission:assign-permissions');
                Route::post('/customer-wallet-address','customerWalletAddress');
                Route::get('/get-wallet-address','getCustomerWalletAddress');
            });
            Route::post('/login', 'login')->name('v4.login-user');
            Route::post('/register', 'register')->name('v4.register-user');
            Route::get('/email-exist', 'checkEmailExist')->name('v4.email-exist');
            Route::post('/email-verification', 'verifyEmail')->name('v4.email-verification');
            Route::post('/forgot-password', 'forgotPassword')->name('v4.forgot-password');
            Route::post('/match-password-otp', 'matchPasswordOtp')->name('v4.match-password-otp');
            Route::post('/create-new-password', 'createNewPassword')->name('v4.new-password');
            Route::post('/seller-login', 'sellerLogin')->name('v4.login-seller');
            Route::post('/request-account-deletion', 'requestDeletion');
            Route::get('/seller-side-permissions', 'getSellerSidePermissions');
        });
    });
});
