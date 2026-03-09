<?php

namespace Packages\Auth\Providers;

use Illuminate\Support\ServiceProvider;
use Packages\Auth\Repositories\Eloquent\AuthRepository;
use Packages\Auth\Services\AuthService;

class AuthServiceProvider extends ServiceProvider
{
    public function register()
    {
        // Register services
        $this->app->singleton(AuthService::class, function ($app) {
            return new AuthService($app->make(AuthRepository::class));
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
            __DIR__.'/../../Config/config.php' => config_path('login.php'),
        ], 'login-config');
    }
}