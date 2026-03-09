<?php

namespace Packages\Review\Providers\V4;

use Illuminate\Support\ServiceProvider;
use Packages\Review\Repositories\Eloquent\V4\ReviewRepository;
use Packages\Review\Services\V4\ReviewService;

class ReviewServiceProvider extends ServiceProvider
{
    public function register()
    {
        // Register services
        $this->app->singleton(ReviewService::class, function ($app) {
            return new ReviewService($app->make(ReviewRepository::class));
        });   

        // Merge config
        $this->mergeConfigFrom(__DIR__.'/../../../Config/config.php', '');

        // Load constants
        require_once __DIR__.'/../../../Config/constants.php';
    }

    public function boot()
    {
        // Load routes
        $this->loadRoutesFrom(__DIR__.'/../../../Routes/api.php');

        // Publish config
        $this->publishes([
            __DIR__.'/../../../Config/config.php' => config_path('dashboard.php'),
        ], 'dashboard-config');
    }
}