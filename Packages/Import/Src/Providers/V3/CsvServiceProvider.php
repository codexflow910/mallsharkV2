<?php

namespace Packages\Import\Providers\V3;

use Illuminate\Support\ServiceProvider;
use Packages\Import\Services\V3\CsvService;
use Packages\Import\Repositories\Eloquent\V3\CsvRepository;

class CsvServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(CsvService::class, function ($app) {
            return new CsvService($app->make(CsvRepository::class));
        });

        // Merge config
        $this->mergeConfigFrom(__DIR__.'/../../../Config/config.php', 'csv');

        // Load constants
        require_once __DIR__.'/../../../Config/constants.php';
    }

    public function boot()
    {
        // Load routes
        $this->loadRoutesFrom(__DIR__.'/../../../Routes/api.php');
        // $this->loadRoutesFrom(__DIR__.'/../../Routes/api_v2.php');

        // Publish config
        $this->publishes([
            __DIR__.'/../../../Config/config.php' => config_path('csv.php'),
        ], 'csv-config');
    }
}