<?php

namespace Packages\Review\Providers;

use Illuminate\Support\ServiceProvider;
use Packages\Review\Repositories\Eloquent\ReviewRepository;
use Packages\Review\Services\ReviewService;

class ReviewServiceProvider extends ServiceProvider
{
    public function register()
    {
        // Register services
        $this->app->singleton(ReviewService::class, function ($app) {
            return new ReviewService($app->make(ReviewRepository::class));
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