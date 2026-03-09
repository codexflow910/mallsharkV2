<?php

namespace Packages\Dashboard\Providers;

use Illuminate\Support\ServiceProvider;
use Packages\Dashboard\Repositories\Eloquent\DashboardRepository;
use Packages\Dashboard\Services\DashboardService;

class DashboardServiceProvider extends ServiceProvider
{
    public function register()
    {
        // Register services
        $this->app->singleton(DashboardService::class, function ($app) {
            return new DashboardService($app->make(DashboardRepository::class));
        });   

        // Merge config
        $this->mergeConfigFrom(__DIR__.'/../../Config/config.php', '');

        // Load constants
        require_once __DIR__.'/../../Config/constants.php';
    }

    public function boot()
    {
        // Load routes
        $this->loadRoutesFrom(__DIR__.'/../../Routes/api.php');

        // Publish config
        $this->publishes([
            __DIR__.'/../../Config/config.php' => config_path('dashboard.php'),
        ], 'dashboard-config');
    }
}