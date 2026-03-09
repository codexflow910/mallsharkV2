<?php

namespace Packages\Import\Providers\V2;

use Illuminate\Support\ServiceProvider;
use Packages\Import\Services\V2\CsvService;
use Packages\Import\Repositories\Eloquent\V2\CsvRepository;
use Packages\Import\Repositories\Eloquent\V2\ShopifyRepository;
use Packages\Import\Contracts\V2\ShopifyRepositoryInterface;

class ShopifyServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(CsvService::class, function ($app) {
            return new CsvService($app->make(CsvRepository::class));
        });

        // Bind ShopifyRepository
        $this->app->bind(ShopifyRepositoryInterface::class, ShopifyRepository::class);
        $this->app->singleton(ShopifyRepository::class);

        // Merge config
        $this->mergeConfigFrom(__DIR__.'/../../../Config/config.php', 'shopify');

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
            __DIR__.'/../../../Config/config.php' => config_path('shopify.php'),
        ], 'shopify-config');
    }
}