<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\Http\Controllers\HomeController;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Illuminate\Support\Carbon;

class dynamicSiteMap extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'site:map';
    private $homeController;

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command hits a function that will update sitemap dynamically';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(HomeController $homeController)
    {
        parent::__construct();
        $this->homeController = $homeController;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        try{

        $this->homeController->siteMap();
        $paymentLog = new Logger('');
        $paymentLog->pushHandler(new StreamHandler(storage_path('logs/sitemap.log')), Logger::INFO);
        $paymentLog->info('SiteMap Generated', ['created_at' => Carbon::now()]);
        }catch(\Exception $e){
        
        $paymentLog = new Logger('');
        $paymentLog->pushHandler(new StreamHandler(storage_path('logs/sitemap.log')), Logger::INFO);
        $paymentLog->info('SiteMap Failed', ['created_at' => Carbon::now()]);
        }
        // $this->info('Function hit successfully');
      
    }
}
