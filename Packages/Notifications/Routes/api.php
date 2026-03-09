<?php

namespace Packages\Notifications\Routes;

use Illuminate\Support\Facades\Route;
use Packages\Notifications\Http\Controllers\NotificationsController;
use Packages\Notifications\Http\Controllers\V2\NotificationsController as NotificationsControllerV2;
use Packages\Notifications\Http\Controllers\V3\NotificationsController as NotificationsControllerV3;


Route::prefix('api/v2')->group(function () {

    Route::prefix(('notification'))->group(function () {
        Route::middleware('auth:api')->group(function () {
            Route::controller(NotificationsControllerV2::class)->group(function () {

                Route::get('/all', 'allNotifications')->name('all-notifications');
                Route::get('/seller/all', 'sellerAllNotifications')->name('seller-all-notifications');
                Route::delete('/delete/{id}', 'deleteNotification')->name('delete-notification');
                Route::get('/count-unread', 'unreadCountNotification')->name('count-unread-notification');
                Route::get('/mark-read', 'markReadNotification')->name('mark-read-notification');
                Route::post('/check-notification', 'userNotificationChangeStatus')->name('check-notification');
            });
        });
    });
});

Route::prefix('api/v3')->group(function () {

    Route::prefix(('notification'))->group(function () {
        Route::middleware('auth:api')->group(function () {
            Route::controller(NotificationsControllerV3::class)->group(function () {

                Route::get('/all', 'allNotifications')->name('v3.all-notifications');
                Route::get('/seller/all', 'sellerAllNotifications')->name('v3.seller-all-notifications');
                Route::delete('/delete/{id}', 'deleteNotification')->name('v3.delete-notification');
                Route::get('/count-unread', 'unreadCountNotification')->name('v3.count-unread-notification');
                Route::get('/mark-read', 'markReadNotification')->name('v3.mark-read-notification');
                Route::post('/check-notification', 'userNotificationChangeStatus')->name('v3.check-notification');
            });
        });
    });
});

// V4 Routes
use Packages\Notifications\Http\Controllers\V4\NotificationsController as NotificationsControllerV4;

Route::prefix('api/v4')->group(function () {

    Route::prefix(('notification'))->group(function () {
        Route::middleware('auth:api')->group(function () {
            Route::controller(NotificationsControllerV4::class)->group(function () {

                Route::get('/all', 'allNotifications')->name('v4.all-notifications');
                Route::get('/seller/all', 'sellerAllNotifications')->name('v4.seller-all-notifications');
                Route::delete('/delete/{id}', 'deleteNotification')->name('v4.delete-notification');
                Route::get('/count-unread', 'unreadCountNotification')->name('v4.count-unread-notification');
                Route::get('/mark-read', 'markReadNotification')->name('v4.mark-read-notification');
                Route::post('/check-notification', 'userNotificationChangeStatus')->name('v4.check-notification');
            });
        });
    });
});
