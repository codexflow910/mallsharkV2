<?php

namespace Packages\Product\Providers\V3;

use Illuminate\Support\ServiceProvider;
use Packages\Product\Services\V3\ProductService;
use Packages\Product\Contracts\V3\ProductRepositoryInterface;

use Packages\Product\Repositories\V3\ProductRepository;
use Packages\Product\Services\V3\ProductTypes\SimpleProductService;
use Packages\Product\Services\V3\ProductTypes\VariableProductService;
use Packages\Product\Repositories\V3\SimpleProductRepository;
use Packages\Product\Repositories\V3\VariableProductRepository;
use Packages\Klavio\Services\V3\KlavioService;


class ProductServiceProvider extends ServiceProvider
{
    public function register() 
    {
        
        // Register repositories with interface
        // $this->app->bind(ProductRepository::class, ProductRepositoryInterface::class);
        // $this->app->bind(SimpleProductRepository::class, ProductRepositoryInterface::class);
        // $this->app->bind(VariableProductRepository::class, ProductRepositoryInterface::class);


        // Register services
        $this->app->singleton(SimpleProductService::class, function ($app) {
            return new SimpleProductService($app->make(SimpleProductRepository::class),$app->make(KlavioService::class));
        });

        $this->app->singleton(VariableProductService::class, function ($app) {
            return new VariableProductService($app->make(VariableProductRepository::class),$app->make(KlavioService::class));
        });
        
        $this->app->singleton(ProductService::class, function ($app) {
            return new ProductService($app->make(ProductRepository::class), $app->make(KlavioService::class));
        });
   

        // Merge config
        $this->mergeConfigFrom(__DIR__.'/../../../Config/config.php', 'product');

        // Load constants
        require_once __DIR__.'/../../../Config/constants.php';
        // Load v3 constants
        require_once __DIR__.'/../../../Config/V3/constants.php';
    }

    public function boot()
    {
        // Load routes
        $this->loadRoutesFrom(__DIR__.'/../../../Routes/api.php');
        // $this->loadRoutesFrom(__DIR__.'/../../Routes/api_v2.php');

        // Publish config
        $this->publishes([
            __DIR__.'/../../../Config/config.php' => config_path('product.php'),
        ], 'product-config');
    }
}