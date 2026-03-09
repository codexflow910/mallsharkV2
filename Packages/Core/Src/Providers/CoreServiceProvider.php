<?php

namespace Packages\Core\Providers;

use Packages\Core\Contracts\CoreRepositoryInterface;
use Illuminate\Support\ServiceProvider;
use Packages\Core\Repositories\Eloquent\CoreRepository as CoreRepository;
use Packages\Core\Services\CoreService;
use Packages\Import\Http\Controllers\ShopifyController;
class CoreServiceProvider extends ServiceProvider
{
    public function register()
    {



        
        // Register services
        $this->app->singleton(CoreService::class, function ($app) {
            return new CoreService($app->make(CoreRepository::class),$app->make(ShopifyController::class));
        });


        // Merge config
        $this->mergeConfigFrom(__DIR__ . '/../../Config/config.php', 'core');

        // Load constants
        require_once __DIR__ . '/../../Config/constants.php';
    }

    public function boot()
    {
        // Load routes
        $this->loadRoutesFrom(__DIR__ . '/../../Routes/api.php');
        // $this->loadRoutesFrom(__DIR__.'/../../Routes/api_v2.php');

        // Publish config
        $this->publishes([
            __DIR__ . '/../../Config/config.php' => config_path('core.php'),
        ], 'core-config');
    }
}
