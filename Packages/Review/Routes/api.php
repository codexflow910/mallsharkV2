<?php

namespace Packages\Review\Routes;

use Illuminate\Support\Facades\Route;
use Packages\Review\Http\Controllers\ReviewController;
use Packages\Review\Http\Controllers\V2\ReviewController as ReviewControllerV2;
use Packages\Review\Http\Controllers\V3\ReviewController as ReviewControllerV3;

Route::prefix('api/v2')->group(function () {

    Route::prefix(('review'))->group(function () {
        Route::middleware('auth:api')->group(function () {
            Route::controller(ReviewControllerV2::class)->group(function () {

                Route::post('/add', 'addReview')->name('add-review');
                Route::post('/admin-add', 'addSuperReview')->name('add-super-review');
                Route::post('/admin-update/{id}', 'updateSuperReview')->name('update-super-review');
                Route::delete('/admin-delete/{id}', 'deleteSuperReview')->name('delete-super-review');
                Route::get('/get-all-reviews', 'getReviews');
                Route::post('/update-review/{id}', 'updateReview');
                Route::delete('/delete-review/{id}', 'deleteReview');
            });
        });
        Route::controller(ReviewControllerV2::class)->group(function () {
                
            Route::get('/all/sorting', 'getSortedReviews')->name('getSortedReviews');
            Route::get('/top-reviews', 'getTopReviews')->name('getTopReviews');
            Route::get('/review_request/{id}', 'reviewRequest');
            Route::post('/add-v3', 'addReviewV3')->name('add-review-v3');
        });
    });
});

Route::prefix('api/v3')->group(function () {

    Route::prefix(('review'))->group(function () {
        Route::middleware('auth:api')->group(function () {
            Route::controller(ReviewControllerV3::class)->group(function () {

                Route::post('/add', 'addReview')->name('v3.add-review');
                Route::post('/admin-add', 'addSuperReview')->name('v3.add-super-review');
                Route::post('/admin-update/{id}', 'updateSuperReview')->name('v3.update-super-review');
                Route::delete('/admin-delete/{id}', 'deleteSuperReview')->name('v3.delete-super-review');
            });
        });
        Route::controller(ReviewControllerV3::class)->group(function () {

            Route::get('/all/sorting', 'getSortedReviews')->name('v3.getSortedReviews');
            Route::get('/review_request/{id}', 'reviewRequest');
            Route::post('/add-v3', 'addReviewV3')->name('v3.add-review-v3');
        });
    });
});

// V4 Routes
use Packages\Review\Http\Controllers\V4\ReviewController as ReviewControllerV4;

Route::prefix('api/v4')->group(function () {

    Route::prefix(('review'))->group(function () {
        Route::middleware('auth:api')->group(function () {
            Route::controller(ReviewControllerV4::class)->group(function () {

                Route::post('/add', 'addReview')->name('v4.add-review');
                Route::post('/admin-add', 'addSuperReview')->name('v4.add-super-review');
                Route::post('/admin-update/{id}', 'updateSuperReview')->name('v4.update-super-review');
                Route::delete('/admin-delete/{id}', 'deleteSuperReview')->name('v4.delete-super-review');
            });
        });
        Route::controller(ReviewControllerV4::class)->group(function () {

            Route::get('/all/sorting', 'getSortedReviews')->name('v4.getSortedReviews');
            Route::get('/review_request/{id}', 'reviewRequest');
            Route::post('/add-v3', 'addReviewV3')->name('v4.add-review-v3');
        });
    });
});
