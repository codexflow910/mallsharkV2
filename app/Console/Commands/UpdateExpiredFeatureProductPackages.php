<?php

namespace App\Console\Commands;

use App\Http\Controllers\ElasticSearchController;
use App\Models\FeatureProductPackagePurchase;
use App\Models\Product;
use Illuminate\Console\Command;

class UpdateExpiredFeatureProductPackages extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'removefeatureadd:run';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {

        $featureAds = FeatureProductPackagePurchase::all();
        $currentDate = date('Y-m-d');
        foreach ($featureAds as $fads) {
            if ($fads->end_date <= $currentDate) {
                //echo $fads->product_id;                             
                Product::where('product_id', $fads->product_id)->update(['feature_status' => 0]);
            } else {
                echo 'no product found';
            }
        }
    }
}
