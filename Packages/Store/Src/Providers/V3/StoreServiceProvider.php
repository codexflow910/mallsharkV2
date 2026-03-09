<?php

namespace Packages\Store\Providers\V3;

use Packages\Store\Contracts\V3\StoreRepositoryInterface;
use Illuminate\Support\ServiceProvider;
use Packages\Store\Repositories\Eloquent\V3\StoreRepository as ProductRepository;
use Packages\Store\Repositories\Eloquent\V3\SimpleProductRepository;
use Packages\Store\Repositories\Eloquent\V3\StoreRepository;
use Packages\Store\Repositories\Eloquent\V3\VariableProductRepository;
use Packages\Store\Services\V3\StoreService;
use Packages\Store\Services\V3\PayPalOnboardingService;

class StoreServiceProvider extends ServiceProvider
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

        // Register PayPal Onboarding Service
        $this->app->singleton(PayPalOnboardingService::class, function ($app) {
            return new PayPalOnboardingService();
        });

        
   

        // Merge config
        $this->mergeConfigFrom(__DIR__.'/../../../Config/config.php', 'store');

        // Load constants
        require_once __DIR__.'/../../../Config/constants.php';

        // Load V2 constants
        require_once __DIR__.'/../../../Config/V2/constants.php';
        
        // Load V3 constants
        require_once __DIR__.'/../../../Config/V3/constants.php';
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