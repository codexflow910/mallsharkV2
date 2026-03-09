<?php

namespace Packages\Search\Providers\V4;

use Packages\Search\Contracts\V4\SearchRepositoryInterface;
use Illuminate\Support\ServiceProvider;
use Packages\Search\Repositories\V4\SearchRepository;
use Packages\Search\Services\V4\SearchService;
use Packages\Klavio\Services\V4\KlavioService;

class SearchServiceProvider extends ServiceProvider
{
    public function register()
    {
        
        // Register repositories with interface
        // $this->app->bind(ProductRepository::class, ProductRepositoryInterface::class);
        // $this->app->bind(SimpleProductRepository::class, ProductRepositoryInterface::class);
        // $this->app->bind(VariableProductRepository::class, ProductRepositoryInterface::class);


        // Register services
        $this->app->singleton(SearchService::class, function ($app) {
            return new SearchService($app->make(SearchRepository::class), $app->make(KlavioService::class));
        });


        
   

        // Merge config
        $this->mergeConfigFrom(__DIR__.'/../../../Config/config.php', 'store');

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
            __DIR__.'/../../../Config/config.php' => config_path('store.php'),
        ], 'store-config');
    }
}