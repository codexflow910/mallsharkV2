<?php

namespace Packages\Home\Providers;

use Packages\Home\Contracts\HomeRepositoryInterface;
use Illuminate\Support\ServiceProvider;
use Packages\Store\Repositories\Eloquent\StoreRepository;
use Packages\Store\Services\StoreService;


class HomeServiceProvider extends ServiceProvider
{
    public function register()
    {
        
        // Register repositories with interface
        // $this->app->bind(ProductRepository::class, ProductRepositoryInterface::class);
        // $this->app->bind(SimpleProductRepository::class, ProductRepositoryInterface::class);
        // $this->app->bind(VariableProductRepository::class, ProductRepositoryInterface::class);


        // Register services
        $this->app->singleton(StoreService::class, function ($app) {
            return new StoreService($app->make(StoreRepository::class));
        });

        
   

        // Merge config
        $this->mergeConfigFrom(__DIR__.'/../../Config/config.php', 'store');

        // Load constants
        require_once __DIR__.'/../../Config/constants.php';
    }

    public function boot()
    {
        // Load routes
        $this->loadRoutesFrom(__DIR__.'/../../Routes/api.php');
        // $this->loadRoutesFrom(__DIR__.'/../../Routes/api_v2.php');

        // Publish config
        $this->publishes([
            __DIR__.'/../../Config/config.php' => config_path('store.php'),
        ], 'store-config');
    }
}