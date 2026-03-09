<?php

namespace Packages\Auth\Providers\V4;

use Illuminate\Support\ServiceProvider;
use Packages\Auth\Repositories\Eloquent\V4\AuthRepository;
use Packages\Auth\Services\V4\AuthService;
use Packages\Klavio\Services\V4\KlavioService;



class AuthServiceProvider extends ServiceProvider
{
    public function register()
    {  
        $this->app->singleton(AuthService::class, function ($app) {
            return new AuthService
            ($app->make(AuthRepository::class),
            $app->make(KlavioService::class));
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
            __DIR__.'/../../../Config/config.php' => config_path('login.php'),
        ], 'login-config');
    }
}