<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Repositories\Campaign;

use App\BaseAccess;
use App;
use DB;
use Session;
use App\Models\Campaign;
use App\Http\Controllers\ElasticSearchController;

/**
 * Description of Useraccess
 *
 * @author Asif
 */
class Campaignaccess extends BaseAccess {

    private $campaign;

    public function __construct() {

        $this->campaign = new Campaign();
    }

//    public function saveCampaign() {
//        $date = date('Y-m-d');
//        // $res = strtotime('+7 days', $timestamp);
//        for ($i = 1; $i < 6; $i++) {
//            
//            $startDate = date('Y-m-d', strtotime("+$i month", strtotime($date)));
//            $campaignDate = date('Y-m-d', strtotime("+$i day", strtotime($startDate)));
//            $campaing = new Campaign();
//            $campaing->camp_start=$campaignDate;
//            $campaing->camp_start=date('Y-m-d', strtotime("+$i day", strtotime($campaignDate)));
//           
//        }
//    }
    public function saveCampaign() {
        $constants = config('constants.shopzit_gamification');
        $campaignSupportDuration = $constants['CAMPAIGN_SUPPORT_DURATION'];
        $userId = getCurrentUserId();
        $StoreData = getStoreDataByUserId($userId);
        $storeId = $StoreData->store_id;
        $query = DB::table('products')->select('product_id')->where('store_id', $storeId)->first();
        $productId = $query->product_id;
        for ($i = 1; $i <= 6; $i++) {
            if ($i == 1) {
                $campStatus = "support";
                $startDate = date('Y-m-d H:i:s');
                $endDate = date('Y-m-d H:i:s', strtotime("+$campaignSupportDuration days", strtotime($startDate)));
            } else {
                $campStatus = "pending";
                $startDate = date('Y-m-d H:i:s', strtotime("+1 days", strtotime($endDate)));
                $endDate = date('Y-m-d H:i:s', strtotime("+$campaignSupportDuration days", strtotime($startDate)));
            }
            $campaign = new Campaign();
            $campaign->store_id = $storeId;
            $campaign->product_id = $productId;
            $campaign->camp_start = $startDate;
            $campaign->camp_end = $endDate;
            $campaign->status = $campStatus;
            $campaign->save();
        }
    }

    public function getCampaignStoreIconId($campaignId) {
        $store = DB::table('store_campaigns')->join('stores', 'store_campaigns.store_id', '=', 'stores.store_id')->where('store_campaigns.store_campaign_id', $campaignId)->select('stores.user_id as store_user_id', 'stores.store_name as store_name', 'stores.store_icon as store_icon')->first();
        if ($store)
            return $store;
        else
            return false;
    }

    public function getStoreCampaign($storeId) {
        $query = $this->campaign->where('store_id', $storeId)->where('status', '=', 'pending')
                ->select('store_campaign_id', 'store_id', 'product_id', 'camp_start', 'camp_end', 'status')
                ->get();
        if (!empty($query)) {
            return $query->toArray();
        } else {
            return false;
        }
    }

    public function updateSaleProduct($data) {
        $query = DB::table('products')->where('product_id', $data['product_id'])->where('status', '1')->first();
        if (!empty($query)) {
            $this->campaign->where('store_campaign_id', $data['store_campaign_id'])->update(['product_id' => $data['product_id']]);
            return true;
        } else {
            return false;
        }
    }

    public function getSupportingCampaign($storeId = 0) {
        if (!$storeId) {
            $userId = getCurrentUserId();
            $StoreData = getStoreDataByUserId($userId);
            $storeId = $StoreData->store_id;
        }
        $query = $this->campaign->where('store_id', $storeId)->where('status', 'support')->first();
        if (!empty($query)) {
            return $query;
        } else {
            return false;
        }
    }

    public function updatePledge($campaign_id, $pledge_amount) {
        $constants = config('constants.shopzit_gamification');
        $pledge_price = number_format($pledge_amount / $constants['ONE_PLEDGE_PRICE'], 2, '.', '');
        $pledges = $this->campaign->where('store_campaign_id', $campaign_id)->value('pledges');
        $pledge_price = $pledge_price + $pledges;
        $this->campaign->where('store_campaign_id', $campaign_id)->update(['pledges' => $pledge_price]);
    }

    public function avaiableForFlashSale($productId = '') {
//        date_default_timezone_set("Asia/Karachi");
        $flashsalequery = DB::table('products')->where('product_id', $productId)
                        ->where('flashsale_status', 'choosen')->first();
        if (!empty($flashsalequery)) {
            $currentTimeDate = date('Y-m-d H:i:s');
            $constants = config('constants.shopzit_gamification');
            $flashSaleDuration = $constants['CHOOSEN_CASHBACK_LIMIT'];
            $query = DB::table('store_campaigns')
                            ->select('flash_sale_start')->where('product_id', $productId)->where('status', 'active')->first();


            $flashEndate = date('Y-m-d H:i:s', strtotime("+$flashSaleDuration hours", strtotime($query->flash_sale_start)));


            if ($flashEndate < $currentTimeDate) {
                DB::table('products')->where('product_id', $productId)
                        ->update(['flashsale_status' => 'standard','home_flash_sale'=>0]);
            }
        }
    }

    /*
     * get recent campaigns and products associated with them
     */

    public function getRecentCampaignsResults() {

        return $results = DB::table('store_campaigns')
                ->Join('products', 'store_campaigns.product_id', 'products.product_id')
                ->Join('stores', 'products.store_id', 'stores.store_id')
                //->LeftJoin('campaign_product_stats','campaign_product_stats.product_id','products.product_id')
                ->where('products.status', '1')
                //->where('store_campaigns.status', 'support')
                //->sum('campaign_product_stats.sold_amount')
                ->select('products.*', 'stores.store_slug', 'store_campaigns.store_campaign_id')
                ->get();
    }

    public function checkproductInFlashSale($productId) {
        $query = DB::table('store_campaigns')->where('product_id', $productId)->where('status', '!=', 'completed')->count();
        if ($query <= 0) {
            return true;
        } else {
            return false;
        }
    }

    public function flashsaleUpdate($campaignId) {
        $response = "";
        $currentStock = "";
        $currentTimeDate = date('Y-m-d H:i:s');
        $query = $this->campaign->where('store_campaign_id', $campaignId)->select('product_id', 'camp_run_end', 'flash_sale_start')->first();

        if (!empty($query)) {
            $status = 'standard';
            if ($query->flash_sale_start == NULL) {
                DB::table('store_campaigns')->where('store_campaign_id', $campaignId)
                        ->update(['flash_sale_start' => $currentTimeDate]);
            }
            $queryflash = DB::table('products')->where('product_id', $query->product_id)->select('current_stock', 'product_id', 'flashsale_status', 'product_type')->first();
            if (!empty($queryflash) && $queryflash->flashsale_status == 'standard') {
                $status = 'choosen';
                $currentStock = $queryflash->current_stock;
                if ($queryflash->product_type == 'variant') {
                    $res = getvariableProduct($queryflash->product_id);
                    $currentStock = $res['current_stock'];
                }
                if ($currentStock > 0) {
                    DB::table('products')->where('product_id', $query->product_id)->update(['flashsale_status' => $status]);
                     $response = "success";
                } else {
                    $response = "outofstock";
                }
            } else {
                if ($queryflash->flashsale_status == 'standard') {
                    $status = 'choosen';
                }
                DB::table('products')->where('product_id', $query->product_id)->update(['flashsale_status' => $status]);
                $response = "success";
            }
        } else {
            $response = "error";
        }
        return $response;
    }

    public function getSupprtCampaign($storeId) {
        $query = $this->campaign->where('store_id', $storeId)->where('status', 'support')->first();
        if (!empty($query)) {
            return $query;
        } else {
            return false;
        }
    }

}
