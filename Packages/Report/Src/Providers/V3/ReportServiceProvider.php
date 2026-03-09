<?php

namespace Packages\Report\Providers\V3;

use Illuminate\Support\ServiceProvider;
use Packages\Report\Repositories\Eloquent\V3\ReportRepository;
use Packages\Report\Services\V3\ReportService;


class  ReportServiceProvider extends ServiceProvider
{
    public function register()
    {

        // Register services
        $this->app->singleton(ReportService::class, function ($app) {
            return new ReportService($app->make(ReportRepository::class));
        });

        // Merge config
        $this->mergeConfigFrom(__DIR__.'/../../../Config/config.php', 'report');

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
            __DIR__.'/../../../Config/config.php' => config_path('config.php'),
        ], 'report-config');
    }
}