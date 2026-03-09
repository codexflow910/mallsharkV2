<?php

namespace Packages\Distributors\Providers\V2;

use Illuminate\Support\ServiceProvider;

use Packages\Distributors\Services\V2\DistributorService;
use Packages\Distributors\Repositories\Eloquent\V2\DistributorRepository;
class DistributorServiceProvider extends ServiceProvider
{
    public function register()
    {
        
        // Register services
        $this->app->singleton(DistributorService::class, function ($app) {
            return new DistributorService($app->make(DistributorRepository::class));
        });

        
   

        // Merge config
        $this->mergeConfigFrom(__DIR__.'/../../../Config/config.php', 'distributors');
        require_once __DIR__ . '/../../../Config/constants.php';

    }

    public function boot()
    {
        // Load routes
        $this->loadRoutesFrom(__DIR__.'/../../../Routes/api.php');
        // $this->loadRoutesFrom(__DIR__.'/../../Routes/api_v2.php');

        // Publish config
        $this->publishes([
            __DIR__.'/../../../Config/config.php' => config_path('distributors.php'),
        ], 'distributors-config');
    }
}