<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use DB;
use App\Http\Controllers\ElasticSearchController;
use App\Models\Campaign;
use Carbon\Carbon;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

class RunCampaign extends Command {

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'campaign:run';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Campaign active and inctive';

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
        $camparray = array();
        $startDate = date('Y-m-d');
        $currentTimeDate = date('Y-m-d H:i:s');
        $constants = config('constants.shopzit_gamification');
        $pledgePrice = $constants['ONE_PLEDGE_PRICE'];
        $targetAmount = $constants['FLASH_SALE_TARGET'];
        $campaignRunDurantion = $constants['CAMPAIGN_RUN_DURATION'];
        $campendDate = date('Y-m-d H:i:s', strtotime("+$campaignRunDurantion days", strtotime($currentTimeDate)));
        $campQuery = DB::table('store_campaigns')->where('status', 'support')
                        //->where('camp_end','<=', date('Y-m-d') . ' 00:00:00')->get();
                        ->where('camp_end', '<=', $currentTimeDate)->get();
        foreach ($campQuery as $cmq) {
            $totalAmount = $cmq->pledges * $pledgePrice;
            // target achieve
            if ($totalAmount >= $targetAmount) {
                // store campaign active and set campaign end date
                DB::table('store_campaigns')->where('store_campaign_id', $cmq->store_campaign_id)
                        //->update(['status' => 'active', 'camp_run_end' => $campendDate, 'flash_sale_start' => $currentTimeDate]);
                        ->update(['status' => 'active', 'camp_run_end' => $campendDate]);
                // product update for flash sale 
                DB::table('products')->where('store_id', $cmq->store_id)->update(['flashsale_status' => 'standard']);
                //flash sale product
                //DB::table('products')->where('product_id', $cmq->product_id)->update(['flashsale_status' => 'choosen']);
                $esearch = new ElasticSearchController();
                echo 'capaign' . $cmq->store_campaign_id . "has been active";
                $camparray['active_campaign_ids'] = $cmq->store_campaign_id;
            }
            //target not achive
            else {
                DB::table('store_campaigns')->where('store_campaign_id', $cmq->store_campaign_id)
                        ->update(['status' => 'deactive']);
                $camparray['dactive_campaign_ids'] = $cmq->store_campaign_id;
                echo 'capaign' . $cmq->store_campaign_id . "has been deactive";
            }
            // next campaing start 
            $fornextCampaign = DB::table('store_campaigns')->select('store_campaign_id')
                            ->where('status', '=', 'pending')->where('store_id', $cmq->store_id)->first();
            DB::table('store_campaigns')->where('store_campaign_id', $fornextCampaign->store_campaign_id)
                    ->update(['status' => 'support']);
            $this->addNextCampaign($cmq->store_id);
        }
        // for campaign complete
        $activeCampaigns = DB::table('store_campaigns')->select('store_campaign_id', 'store_id', 'camp_run_end')
                        ->where('status', 'active')
                        ->where('camp_run_end', '<=', $currentTimeDate)->get();
        if (!empty($activeCampaigns)) {
            foreach ($activeCampaigns as $ac) {
                DB::table('store_campaigns')->where('store_campaign_id', $ac->store_campaign_id)
                        ->update(['status' => 'completed']);
                DB::table('products')->where('store_id', $ac->store_id)->update(['flashsale_status' => 'all']);
                echo 'capaign' . $ac->store_campaign_id . "has been completed";
                echo '<br>';
                $camparray['campaign_completed_ids'] = $ac->store_campaign_id;
                $esearch = new ElasticSearchController();
            }
        }
        //if cashback is end
        $cashBackquery = DB::table('store_campaigns')->select('toal_gave_back', 'store_campaign_id', 'store_id')->where('status', 'active')->get();
        if (!empty($cashBackquery)) {
            foreach ($cashBackquery as $cb) {
                if ($cb->toal_gave_back > $targetAmount) {
                    DB::table('store_campaigns')->where('store_campaign_id', $cb->store_campaign_id)->update(['status' => 'completed']);
                    DB::table('products')->where('store_id', $cb->store_id)->update(['flashsale_status' => 'all']);
                    echo 'capaign' . $cb->store_campaign_id . "has been completed";
                    echo '<br>';
                }
            }
        }
        $orderLog = new Logger('');
        $orderLog->pushHandler(new StreamHandler(storage_path('logs/runcampaign.log')), Logger::INFO);
        $orderLog->info('Campiagn run', $camparray);
    }

    public function addNextCampaign($storeid) {
        $constants = config('constants.shopzit_gamification');
        $campaignSupportDuration = $constants['CAMPAIGN_SUPPORT_DURATION'];
        $storeCampaign = DB::table('store_campaigns')
                        ->select('store_campaign_id', 'product_id', 'store_id', 'camp_end')
                        ->where('store_id', $storeid)->get();
        if (!empty($storeCampaign)) {
            $numItems = count($storeCampaign);
            $i = 0;
            foreach ($storeCampaign as $sc) {
                if (++$i === $numItems) {
                    $startDate = date('Y-m-d H:i:s', strtotime("+1 days", strtotime($sc->camp_end)));
                    $endDate = date('Y-m-d H:i:s', strtotime("+$campaignSupportDuration days", strtotime($startDate)));
                    $campaign = new Campaign();
                    $campaign->store_id = $storeid;
                    $campaign->product_id = $sc->product_id;
                    $campaign->camp_start = $startDate;
                    $campaign->camp_end = $endDate;
                    $campaign->status = "pending";
                    $campaign->save();
                }
            }
        }
    }
}
