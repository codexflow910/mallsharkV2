<?php

namespace Packages\Order\Providers\V4;

use Illuminate\Support\ServiceProvider;
use Packages\Order\Repositories\Eloquent\V4\OrderRepository;
use Packages\Order\Services\V4\OrderService;
class OrderServiceProvider extends ServiceProvider
{
    public function register()
    {
        // Register services
        $this->app->singleton(OrderService::class, function ($app) {
            return new OrderService($app->make(OrderRepository::class));
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
            __DIR__.'/../../../Config/config.php' => config_path('order.php'),
        ], 'order-config');
    }
}