<?php

namespace Packages\Report\Routes;

use Illuminate\Support\Facades\Route;
use Packages\Report\Http\Controllers\ReportController;
use Packages\Report\Http\Controllers\V2\ReportController as ReportControllerV2;
use Packages\Report\Http\Controllers\V3\ReportController as ReportControllerV3;

Route::prefix('api/v2')->group(function () {
    Route::prefix(('report'))->group(function () {
        Route::middleware('auth:api')->group(function () {
            Route::get('/store-reports', [ReportControllerV2::class, 'getReports']);
            Route::get('/store-products', [ReportControllerV2::class, 'storeProducts']);
            Route::get('/product-reports', [ReportControllerV2::class, 'productReports']);
            Route::get('/categories', [ReportControllerV2::class, 'categoriesList']);
            Route::get('/category-reports', [ReportControllerV2::class, 'categoryReports']);
            Route::get('/stock-reports', [ReportControllerV2::class, 'stockReports']);
        });
    });
});

Route::prefix('api/v3')->group(function () {
    Route::prefix(('report'))->group(function () {
        Route::middleware('auth:api')->group(function () {
            Route::get('/store-reports', [ReportControllerV3::class, 'getReports']);
            Route::get('/store-products', [ReportControllerV3::class, 'storeProducts']);
            Route::get('/product-reports', [ReportControllerV3::class, 'productReports']);
            Route::get('/categories', [ReportControllerV3::class, 'categoriesList']);
            Route::get('/category-reports', [ReportControllerV3::class, 'categoryReports']);
            Route::get('/stock-reports', [ReportControllerV3::class, 'stockReports']);
            Route::get('/shipping-summary', [ReportControllerV3::class, 'getShippingSummary']);
            Route::get('/customer-summary', [ReportControllerV3::class, 'getCustomerSummary']);
            
            // Finance Reports
            Route::get('/finance/balance', [ReportControllerV3::class, 'getBalanceSummary']);
            Route::get('/finance/payouts', [ReportControllerV3::class, 'getPayouts']);
            Route::get('/finance/fees-commissions', [ReportControllerV3::class, 'getFeesCommissions']);
            Route::get('/finance/dashboard', [ReportControllerV3::class, 'getFinanceDashboard']);

            // Store Performance Reports
            Route::get('/store-performance', [ReportControllerV3::class, 'getStorePerformance']);
            Route::get('/store-performance/pdf', [ReportControllerV3::class, 'downloadStorePerformancePdf']);
            // Marketing & Promotion Reports
            Route::get('/marketing/campaigns', [ReportControllerV3::class, 'getMarketingCampaigns']);
            Route::get('/marketing/campaigns/{id}', [ReportControllerV3::class, 'getCampaignDetail']);
            Route::get('/marketing/campaigns/{id}/products', [ReportControllerV3::class, 'getCampaignProducts']);
            Route::get('/marketing/campaigns/{id}/chart', [ReportControllerV3::class, 'getCampaignChart']);
            Route::get('/marketing/summary', [ReportControllerV3::class, 'getMarketingSummary']);
        });
    });
});

// V4 Routes
use Packages\Report\Http\Controllers\V4\ReportController as ReportControllerV4;

Route::prefix('api/v4')->group(function () {
    Route::prefix(('report'))->group(function () {
        Route::middleware('auth:api')->group(function () {
            Route::get('/store-reports', [ReportControllerV4::class, 'getReports']);
            Route::get('/store-products', [ReportControllerV4::class, 'storeProducts']);
            Route::get('/product-reports', [ReportControllerV4::class, 'productReports']);
            Route::get('/categories', [ReportControllerV4::class, 'categoriesList']);
            Route::get('/category-reports', [ReportControllerV4::class, 'categoryReports']);
            Route::get('/stock-reports', [ReportControllerV4::class, 'stockReports']);
            Route::get('/shipping-summary', [ReportControllerV4::class, 'getShippingSummary']);
            Route::get('/customer-summary', [ReportControllerV4::class, 'getCustomerSummary']);
            
            // Finance Reports
            Route::get('/finance/balance', [ReportControllerV4::class, 'getBalanceSummary']);
            Route::get('/finance/payouts', [ReportControllerV4::class, 'getPayouts']);
            Route::get('/finance/fees-commissions', [ReportControllerV4::class, 'getFeesCommissions']);
            Route::get('/finance/dashboard', [ReportControllerV4::class, 'getFinanceDashboard']);

            // Store Performance Reports
            Route::get('/store-performance', [ReportControllerV4::class, 'getStorePerformance']);
            Route::get('/store-performance/pdf', [ReportControllerV4::class, 'downloadStorePerformancePdf']);
        });
    });
});
