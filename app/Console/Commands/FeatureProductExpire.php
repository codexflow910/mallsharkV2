<?php

namespace App\Console\Commands;

use App\Models\FeatureProducts;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\ElasticSearchController;

class FeatureProductExpire extends Command
{
    private $_esearch;

  
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'featureproduct:expire';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Feature Product Expire after one month';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->_esearch = new ElasticSearchController();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $Products = Product::get();
        foreach($Products as $product){
           
           if (Carbon::now() > $product->feature_enddate &&  $product->feature_enddate !=  '0000-00-00' )  {
               DB::table('products')->where('product_id',$product->product_id)->update(['is_feature' => "0" , 'feature_startdate' => 'NULL' , 'feature_enddate' => 'NULL']);
          }
        }
    }
}
