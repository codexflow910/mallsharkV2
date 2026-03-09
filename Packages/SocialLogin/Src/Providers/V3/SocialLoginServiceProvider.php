<?php

namespace Packages\SocialLogin\Providers\V3;

use Illuminate\Support\ServiceProvider;
use Packages\SocialLogin\Repositories\Eloquent\V3\SocialLoginRepository;
use Packages\SocialLogin\Services\V3\SocialLoginService;

class SocialLoginServiceProvider extends ServiceProvider
{
    public function register()
    {
        // Register services
        $this->app->singleton(SocialLoginService::class, function ($app) {
            return new SocialLoginService($app->make(SocialLoginRepository::class));
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