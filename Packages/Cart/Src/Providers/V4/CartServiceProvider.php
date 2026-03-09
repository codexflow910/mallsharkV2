<?php

namespace Packages\Cart\Providers\V4;

use Illuminate\Support\ServiceProvider;
use Packages\Cart\Repositories\Eloquent\V4\CartRepository;
use Packages\Cart\Services\V4\CartService;

class CartServiceProvider extends ServiceProvider
{
    public function register()
    {
        // Register services
        $this->app->singleton(CartService::class, function ($app) {
            return new CartService($app->make(CartRepository::class));
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
            __DIR__.'/../../../Config/config.php' => config_path('cart.php'),
        ], 'cart-config');
    }
}