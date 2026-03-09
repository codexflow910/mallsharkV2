<?php

namespace Packages\Klavio\Providers;

use Packages\Klavio\Contracts\KlavioRepositoryInterface;
use Illuminate\Support\ServiceProvider;
use Packages\Klavio\Repositories\Eloquent\SimpleProductRepository;
use Packages\Klavio\Repositories\Eloquent\KlavioRepository;
use Packages\Klavio\Repositories\Eloquent\VariableProductRepository;
use Packages\Klavio\Services\KlavioService;
use Packages\Klavio\Services\KlavioTypes\SimpleProductService;
use Packages\Klavio\Services\KlavioTypes\VariableProductService;

class KlavioServiceProvider extends ServiceProvider
{
    public function register()
    {
        
        // Register repositories with interface
        // $this->app->bind(ProductRepository::class, ProductRepositoryInterface::class);
        // $this->app->bind(SimpleProductRepository::class, ProductRepositoryInterface::class);
        // $this->app->bind(VariableProductRepository::class, ProductRepositoryInterface::class);


        // Register services
        $this->app->singleton(KlavioService::class, function ($app) {
            return new KlavioService($app->make(KlavioRepository::class));
        });

        
   

        // Merge config
        $this->mergeConfigFrom(__DIR__.'/../../Config/config.php', 'Klavio');

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
            __DIR__.'/../../Config/config.php' => config_path('Klavio.php'),
        ], 'Klavio-config');
    }
}