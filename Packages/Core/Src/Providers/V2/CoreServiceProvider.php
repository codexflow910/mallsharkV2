<?php

namespace Packages\Core\Providers\V2;

use Packages\Core\Contracts\V2\CoreRepositoryInterface;
use Illuminate\Support\ServiceProvider;
use Packages\Core\Repositories\Eloquent\V2\CoreRepository as CoreRepository;
use Packages\Core\Services\V2\CoreService;
use Packages\Core\Services\V2\CryptoService;
use Packages\Klavio\Services\V2\KlavioService;
use Packages\Import\Http\Controllers\V2\ShopifyController;

class CoreServiceProvider extends ServiceProvider
{
    public function register()
    {
        // Register CryptoService
        $this->app->singleton(CryptoService::class, function ($app) {
            return new CryptoService();
        });
        
        // Register services
        $this->app->singleton(CoreService::class, function ($app) {
            return new CoreService
            ($app->make(CoreRepository::class),
                $app->make(ShopifyController::class),
                $app->make(KlavioService::class)
            
            );
        });


        // Merge config
        $this->mergeConfigFrom(__DIR__ . '/../../../Config/config.php', 'core');

        // Load constants
        require_once __DIR__ . '/../../../Config/constants.php';
    }

    public function boot()
    {
        // Load routes
        $this->loadRoutesFrom(__DIR__ . '/../../../Routes/api.php');
        // $this->loadRoutesFrom(__DIR__.'/../../Routes/api_v2.php');

        // Publish config
        $this->publishes([
            __DIR__ . '/../../../Config/config.php' => config_path('core.php'),
        ], 'core-config');
    }
}
