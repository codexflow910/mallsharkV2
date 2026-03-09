<?php

namespace Packages\Stripe\Providers\V2;

use Illuminate\Support\ServiceProvider;
use Packages\Stripe\Repositories\Eloquent\V2\StripeRepository;
use Packages\Stripe\Services\V2\StripeService;

class StripeServiceProvider extends ServiceProvider
{
    public function register()
    {
        // Register services
        $this->app->singleton(StripeService::class, function ($app) {
            return new StripeService($app->make(StripeRepository::class));
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
            __DIR__.'/../../../Config/config.php' => config_path('stripe.php'),
        ], 'stripe-config');
    }
}