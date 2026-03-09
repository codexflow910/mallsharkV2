<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Repositories\Campaign\Campaignaccess;
use DB;
use App\Models\Campaign;
use App\Http\Controllers\ElasticSearchController;
use Artisan;
use Carbon\Carbon;

class CampaignController extends Controller {

    private $_esearch;
    private $campObj;
    private $_request;

    public function __construct(Request $request) {
        $this->_request = $request;
        $this->campObj = new Campaignaccess();
        $this->_esearch = new ElasticSearchController();
    }

    public function runCampaign() {
        $this->campObj->saveCampaign();
    }

    public function showCampaign() {
        $this->campObj->saveCampaign();
    }

    public function updateFlashProduct() {
        if ($this->_request->ajax()) {
            $postedData = $this->_request->input();
            $res = $this->campObj->updateSaleProduct($postedData);
            if ($res == true) {
                echo 'success';
            } else {
                echo 'error';
            }
        }
    }

    public function campaign() {
        $camparray = array();
        $startDate = date('Y-m-d');
        $currentTimeDate = date('Y-m-d H:i:s');
        $constants = config('constants.shopzit_gamification');
        $pledgePrice = $constants['ONE_PLEDGE_PRICE'];
        $targetAmount = $constants['FLASH_SALE_TARGET'];
        $campaignRunDurantion = $constants['CAMPAIGN_RUN_DURATION'];
        $campQuery = DB::table('store_campaigns')->where('status', 'support')
                        //->where('camp_end','<=', date('Y-m-d') . ' 00:00:00')->get();
                        ->where('camp_end', '<=', $currentTimeDate)->get();
        $campendDate = date($currentTimeDate, strtotime("+$campaignRunDurantion days", strtotime($startDate)));
        foreach ($campQuery as $cmq) {
            $totalAmount = $cmq->pledges * $pledgePrice;
            // target achieve
            if ($totalAmount >= $targetAmount) {
                // store campaign active and set campaign end date
                DB::table('store_campaigns')->where('store_campaign_id', $cmq->store_campaign_id)
                        ->update(['status' => 'active', 'camp_run_end' => $campendDate, 'flash_sale_start' => $currentTimeDate]);
                // product update for flash sale 
                DB::table('products')->where('store_id', $cmq->store_id)->update(['flashsale_status' => 'standard']);
                DB::table('products')->where('product_id', $cmq->product_id)->update(['flashsale_status' => 'choosen']);
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
                        ->where('camp_run_end', '<=', date('Y-m-d') . ' 00:00:00')->get();
        if (!empty($activeCampaigns)) {
            foreach ($activeCampaigns as $ac) {
                DB::table('store_campaigns')->where('store_campaign_id', $ac->store_campaign_id)
                        ->update(['status' => 'completed']);
                DB::table('products')->where('store_id', $ac->store_id)->update(['flashsale_status' => 'all']);
                echo 'capaign' . $ac->store_campaign_id . "has been completed";
                $camparray['campaign_completed_ids'] = $ac->store_campaign_id;            }
        }
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

    public function flashSaleEnd() {
        date_default_timezone_set("Asia/Karachi");
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
                            ->update(['flashsale_status' => 'standard']);
                } else {
                    echo $fs->store_campaign_id . 'remain';
                }
            }
        }
    }

    /*
     * get recent campagins and products
     */

    public function campEnd($campId) {
        $currentTimeDate = date('Y-m-d H:i:s');
    }

    public function getRecentCampaignsResults() {
        $results = $this->campObj->getRecentCampaignsResults();
        print_rr($results);
        die();
    }

    public function flasSaleOn() {
        if ($this->_request->ajax()) {
            $campaignId = $this->_request->input('campaign_id');
            $res = $this->campObj->flashsaleUpdate($campaignId);
            echo $res;
        }
    }

    public function checkSales() {
        ///Artisan::call("smsoffer:run");
        Artisan::call("offer:run");
    }

}
