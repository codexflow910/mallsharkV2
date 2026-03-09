<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use DB;
use Carbon\Carbon;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use App\Http\Controllers\ElasticSearchController;

class RunFlashsale extends Command {

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'flashsale:run';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Flash sale run';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle() {
        $flashSaleArray = array();
        $currentTimeDate = date('Y-m-d H:i:s');
        $constants = config('constants.shopzit_gamification');
        $flashSaleDuration = $constants['CHOOSEN_CASHBACK_LIMIT'];
        $flashSale = DB::table('store_campaigns')->select('flash_sale_start', 'store_campaign_id', 'product_id')->where('status', 'active')->get();
        if (!empty($flashSale)) {
            foreach ($flashSale as $fs) {
                $flashEndate = date('Y-m-d H:i:s', strtotime("+$flashSaleDuration hours", strtotime($fs->flash_sale_start)));
                if ($flashEndate < $currentTimeDate) {
                    DB::table('products')->where('product_id', $fs->product_id)
                            ->update(['flashsale_status' => 'standard','home_flash_sale'=>0]);
                    $flashSaleArray['today'] = $currentTimeDate;
                    $flashSaleArray['removed_from_flash'] = $fs->product_id;
                } else {
                    echo $fs->store_campaign_id . 'remain for sale<br>';
                }
            }
            $orderLog = new Logger('');
            $orderLog->pushHandler(new StreamHandler(storage_path('logs/flashsale.log')), Logger::INFO);
            $orderLog->info('Flash sale', $flashSaleArray);
        }
    }

}
