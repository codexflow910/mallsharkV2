<?php

namespace Packages\Shipping\Providers\V3;

use Illuminate\Support\ServiceProvider;
use Packages\Shipping\Repositories\Eloquent\V3\ShippingRepository;
use Packages\Shipping\Services\V3\ShippingService;

class ShippingServiceProvider extends ServiceProvider
{
    public function register()
    {
        // Register services
        $this->app->singleton(ShippingService::class, function ($app) {
            return new ShippingService($app->make(ShippingRepository::class));
        });   

        // Merge config
        $this->mergeConfigFrom(__DIR__.'/../../../Config/config.php', '');

        // Load constants
        require_once __DIR__.'/../../../Config/constants.php';
    }

    public function boot()
    {
        // Load routes
        $this->loadRoutesFrom(__DIR__.'/../../../Routes/api.php');

        // Publish config
        $this->publishes([
            __DIR__.'/../../../Config/config.php' => config_path('shipping.php'),
        ], 'shipping-config');
    }
}