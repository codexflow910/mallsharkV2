<?php

namespace Packages\Discount\Providers\V2;

use Illuminate\Support\ServiceProvider;
use Packages\Discount\Services\V2\CouponService;
use Packages\Discount\Contracts\V2\DiscountRepositoryInterface;
use Packages\Discount\Repositories\Eloquent\V2\CouponRepository;
use Packages\Discount\Repositories\Eloquent\V2\DiscountRepository;
use Packages\Discount\Services\V2\DiscountService;

class DiscountServiceProvider extends ServiceProvider
{
    public function register()
    {
        
        // Register services
        $this->app->singleton(CouponService::class, function ($app) {
            return new CouponService($app->make(CouponRepository::class), $app->make(DiscountRepository::class));
        });
        $this->app->singleton(DiscountService::class, function ($app) {
            return new DiscountService($app->make(DiscountRepository::class));
        });   

        // Merge config
        $this->mergeConfigFrom(__DIR__.'/../../../Config/config.php', 'discount');

        // Load constants
        require_once __DIR__.'/../../../Config/constants.php';
    }

    public function boot()
    {
        // Load routes
        $this->loadRoutesFrom(__DIR__.'/../../../Routes/api.php');
        // $this->loadRoutesFrom(__DIR__.'/../../Routes/api_v2.php');

        // Publish config
        $this->publishes([
            __DIR__.'/../../../Config/config.php' => config_path('discount.php'),
        ], 'discount-config');
    }
}