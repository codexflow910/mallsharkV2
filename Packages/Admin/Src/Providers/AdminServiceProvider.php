<?php

namespace Packages\Admin\Providers;

use Illuminate\Support\ServiceProvider;
use Packages\Admin\Repositories\Eloquent\AdminRepository;
use Packages\Admin\Services\AdminService;

class AdminServiceProvider extends ServiceProvider
{
    public function register()
    {
        // Register services

        $this->app->singleton(AdminService::class, function ($app) {
            return new AdminService($app->make(AdminRepository::class));
        });   


        // Merge config
        $this->mergeConfigFrom(__DIR__.'/../../Config/config.php', 'admin');

        // Load constants
        require_once __DIR__.'/../../Config/constants.php';
    }

    public function boot()
    {
        // Load routes
        $this->loadRoutesFrom(__DIR__.'/../../Routes/api.php');
        // $this->loadRoutesFrom(__DIR__.'/../../Routes/api_v2.php');

        // Publish config
        $this->publishes([
            __DIR__.'/../../Config/config.php' => config_path('admin.php'),
        ], 'admin-config');
    }
}