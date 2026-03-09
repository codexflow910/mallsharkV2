<?php

namespace Packages\Store\Routes;

use Illuminate\Support\Facades\Route;
use Packages\Search\Http\Controllers\SearchController;
use Packages\Search\Http\Controllers\V2\SearchController as SearchControllerV2;
use Packages\Search\Http\Controllers\V3\SearchController as SearchControllerV3;


//v2 version
Route::prefix('api/v2')->group(function () {

    Route::prefix(('search'))->group(function () {
        Route::get('/search-suggestions', [SearchControllerV2::class, 'suggestions'])->name('suggestions');
        Route::post('related-products', [SearchControllerV2::class, 'getRelatedProducts']);
        Route::get('/trending-products', [SearchControllerV2::class, 'getTrendingProducts']);
        Route::get('/product-search',   [SearchControllerV2::class, 'getSeacrchProduct']);
        Route::post('/most-popular',   [SearchControllerV2::class, 'getMostPopularProduct']);

        Route::get('/popular-tags',[SearchControllerV2::class,'getPopularTags']);
        Route::post('click-event', [SearchControllerV2::class, 'storeClickEvent']);
        Route::post('conversion-event', [SearchControllerV2::class, 'storeConversionEvent']);
        Route::post('/search-image', [SearchControllerV2::class, 'searchImage']);
        Route::post('/generate-tags', [SearchControllerV2::class, 'generateTags']);
        Route::get('/generate-tags-for-all', [SearchControllerV2::class, 'generateTagsForAllProducts']);
        Route::post('/looking-similar-products', [SearchControllerV2::class, 'getLookingSimilarProducts']);
        
        //version 3 api's for website
        Route::post('/search-results-v3',[SearchControllerV2::class,'searchResults']);
        Route::post('/best-sellers-v3', [SearchControllerV2::class, 'bestSellers']);
        Route::post('/deals-v3', [SearchControllerV2::class, 'deals']);
        Route::post('/recomended-products-v3', [SearchControllerV2::class, 'recommendedProducts']);

        Route::post('explore-more',[SearchControllerV2::class,'exploreMore']);// not using anymore
        Route::middleware('auth:api')->group(function () {
            Route::get('/tags',[SearchControllerV2::class, 'getSearchTags']);
            Route::get('/product-search',   [SearchControllerV2::class, 'getSeacrchProduct']);
        });
    });
});

//v3 version
Route::prefix('api/v3')->group(function () {

    Route::prefix(('search'))->group(function () {
        Route::get('/search-suggestions', [SearchControllerV3::class, 'suggestions'])->name('v3.suggestions');
        Route::post('/search-results',[SearchControllerV3::class,'searchResults']);
        Route::get('/category-filters/{category_id}', [SearchControllerV3::class, 'getCategoryFilters']);
        Route::post('/best-sellers', [SearchControllerV3::class, 'bestSellers']);
        Route::post('/deals', [SearchControllerV3::class, 'deals']);
        Route::post('related-products', [SearchControllerV3::class, 'getRelatedProducts']);
        Route::get('/trending-products', [SearchControllerV3::class, 'getTrendingProducts']);
        Route::get('/product-search',   [SearchControllerV3::class, 'getSeacrchProduct']);
        Route::post('/most-popular',   [SearchControllerV3::class, 'getMostPopularProduct']);

        Route::get('/popular-tags',[SearchControllerV3::class,'getPopularTags']);
        Route::post('click-event', [SearchControllerV3::class, 'storeClickEvent']);
        Route::post('conversion-event', [SearchControllerV3::class, 'storeConversionEvent']);
        Route::post('/search-image', [SearchControllerV3::class, 'searchImage']);
        Route::post('/generate-tags', [SearchControllerV3::class, 'generateTags']);
        Route::get('/generate-tags-for-all', [SearchControllerV3::class, 'generateTagsForAllProducts']);
        Route::post('/recomended-products', [SearchControllerV3::class, 'recommendedProducts']);
        Route::post('/looking-similar-products', [SearchControllerV3::class, 'getLookingSimilarProducts']);
        Route::post('/search-by-categories', [SearchControllerV3::class, 'searchByCategories']);
        //version 3 api's for website
        Route::post('/search-results-v3',[SearchControllerV3::class,'searchResultsV3']);
        Route::post('/best-sellers-v3', [SearchControllerV3::class, 'bestSellersV3']);
        Route::post('/deals-v3', [SearchControllerV3::class, 'dealsV3']);
        Route::post('/recomended-products-v3', [SearchControllerV3::class, 'recommendedProductsV3']);

        Route::post('explore-more',[SearchControllerV3::class,'exploreMore']);// not using anymore
        Route::middleware('auth:api')->group(function () {
            Route::get('/tags',[SearchControllerV3::class, 'getSearchTags']);
            Route::get('/product-search',   [SearchControllerV3::class, 'getSeacrchProduct']);
        });
    });
});
// V4 Routes
use Packages\Search\Http\Controllers\V4\SearchController as SearchControllerV4;

//v4 version
Route::prefix('api/v4')->group(function () {

    Route::prefix(('search'))->group(function () {
        Route::get('/search-suggestions', [SearchControllerV4::class, 'suggestions'])->name('v4.suggestions');
        Route::post('/search-results',[SearchControllerV4::class,'searchResults']);
        Route::post('/best-sellers', [SearchControllerV4::class, 'bestSellers']);
        Route::post('/deals', [SearchControllerV4::class, 'deals']);
        Route::post('related-products', [SearchControllerV4::class, 'getRelatedProducts']);
        Route::get('/trending-products', [SearchControllerV4::class, 'getTrendingProducts']);
        Route::get('/product-search',   [SearchControllerV4::class, 'getSeacrchProduct']);
        Route::post('/most-popular',   [SearchControllerV4::class, 'getMostPopularProduct']);

        Route::get('/popular-tags',[SearchControllerV4::class,'getPopularTags']);
        Route::post('click-event', [SearchControllerV4::class, 'storeClickEvent']);
        Route::post('conversion-event', [SearchControllerV4::class, 'storeConversionEvent']);
        Route::post('/search-image', [SearchControllerV4::class, 'searchImage']);
        Route::post('/generate-tags', [SearchControllerV4::class, 'generateTags']);
        Route::get('/generate-tags-for-all', [SearchControllerV4::class, 'generateTagsForAllProducts']);
        Route::post('/recomended-products', [SearchControllerV4::class, 'recommendedProducts']);
        Route::post('/looking-similar-products', [SearchControllerV4::class, 'getLookingSimilarProducts']);
        Route::post('/search-by-categories', [SearchControllerV4::class, 'searchByCategories']);

        Route::post('/search-results-v4',[SearchControllerV4::class,'searchResultsV3']);
        Route::post('/best-sellers-v4', [SearchControllerV4::class, 'bestSellersV3']);
        Route::post('/deals-v4', [SearchControllerV4::class, 'dealsV3']);
        Route::post('/recomended-products-v4', [SearchControllerV4::class, 'recommendedProductsV3']);

        Route::middleware('auth:api')->group(function () {
            Route::get('/tags',[SearchControllerV4::class, 'getSearchTags']);
            Route::get('/product-search',   [SearchControllerV4::class, 'getSeacrchProduct']);
        });
    });
});
