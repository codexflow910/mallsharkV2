<?php

namespace Packages\Product\Providers;

use Illuminate\Support\ServiceProvider;
use Packages\Product\Services\ProductService;
use Packages\Product\Contracts\ProductRepositoryInterface;

use Packages\Product\Repositories\Eloquent\ProductRepository;
use Packages\Product\Services\ProductTypes\SimpleProductService;
use Packages\Product\Services\ProductTypes\VariableProductService;
use Packages\Product\Repositories\Eloquent\SimpleProductRepository;
use Packages\Product\Repositories\Eloquent\VariableProductRepository;


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
            return new SimpleProductService($app->make(SimpleProductRepository::class));
        });

        $this->app->singleton(VariableProductService::class, function ($app) {
            return new VariableProductService($app->make(VariableProductRepository::class));
        });
        $this->app->singleton(ProductService::class, function ($app) {
            return new ProductService($app->make(ProductRepository::class));
        });
   

        // Merge config
        $this->mergeConfigFrom(__DIR__.'/../../Config/config.php', 'product');

        // Load constants
        require_once __DIR__.'/../../Config/constants.php';
    }

    public function boot()
    {
        // Load routes
        $this->loadRoutesFrom(__DIR__.'/../../Routes/api.php');
        // $this->loadRoutesFrom(__DIR__.'/../../Routes/api_.php');

        // Publish config
        $this->publishes([
            __DIR__.'/../../Config/config.php' => config_path('product.php'),
        ], 'product-config');
    }
}