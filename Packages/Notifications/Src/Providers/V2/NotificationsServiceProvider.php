<?php

namespace Packages\Notifications\Providers\V2;

use Illuminate\Support\ServiceProvider;
use Packages\Notifications\Repositories\Eloquent\V2\NotificationsRepository;
use Packages\Notifications\Services\V2\NotificationsService;

class NotificationsServiceProvider extends ServiceProvider
{
    public function register()
    {
        // Register services
        $this->app->singleton(NotificationsService::class, function ($app) {
            return new NotificationsService($app->make(NotificationsRepository::class));
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