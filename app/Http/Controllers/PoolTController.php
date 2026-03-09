<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Redirect;
use File;
use DB;
use App\Repositories\Transactions\PoolTAccess;
use App\Repositories\Transactions\WalletAccess;
use App\Repositories\Pools\PoolsAccess;

class PoolTController extends Controller {

    private $_poolTAccess;
    private $_poolAccess;
    private $_walletAccess;

    public function __construct() {
        $this->_poolTAccess = new PoolTAccess();
         $this->_poolAccess = new PoolsAccess();
        $this->_walletAccess = new WalletAccess();
    }

    public function getPoolStats() {
        $data=$this->_poolAccess->getPoolStats();
         return view('admin/pools-stats')->with('data', $data);
    }

    public function clearCashbacksProduct($productId, $storeID, $userId, $orderId, $qty) {

        $campaign_id = getCampaignIdByStoreID($storeID);

        $constants = config('constants.shopzit_gamification');
        $CASHBACK_POOL_ID = $constants['CASHBACK_POOL_ID'];
        $SUPERSUPPORTER_POOL_ID = $constants['SUPERSUPPORTER_POOL_ID'];
        $BURN_POOL_ID = $constants['BURN_POOL_ID'];
        $OPERATION_POOL_ID = $constants['OPERATION_POOL_ID'];
        $TRANSACTION_POOL_ID = $constants['TRANSACTION_POOL_ID'];
        $query = DB::table('products')->select('price','title')->where('product_id', $productId)->first();
        $productPrice = $query->price;
        //if (isFlashSale($productId) && alreadyPurchasedInSameCampaign($productId,$userId)==0) {
        if (isFlashSale($productId)) {
            // debit cashbackpool

            $cashback = $constants['CHOOSEN_CASHBACK'];
            $cashback = ($cashback * $productPrice) / 100;
            $cashback = round($cashback, 3);
            $cashback = $cashback * $qty;
            $this->_poolTAccess->debitPool($cashback, 'debit', $CASHBACK_POOL_ID, $trans_id = 0, $orderId);

            //credit user wallet
            
            $wtransaction_info = 'USDC Back received on purchase of ' . $query->title;
            $this->_walletAccess->creditWalletWithZit($cashback, $userId, 'credit', $wtransaction_info, $trans_id = 0);
            //update campaign
            DB::table('store_campaigns')->where([
                ['store_id', '=', $storeID],
                ['status', '=', 'active'],
            ])->increment('pledges_used', $cashback);
            DB::table('store_campaigns')->where([
                ['store_id', '=', $storeID],
                ['status', '=', 'active'],
            ])->increment('toal_gave_back', $cashback);
            addFlashSaleLog($productId,'choosen',$productPrice);
        } elseif (isStoreActiveCampaign($storeID)) {
            // debit cashbackpool

            $cashback = $constants['STANDARD_CASHBACK'];

            $cashback = ($cashback * $productPrice) / 100;
            $cashback = round($cashback, 3);
            $cashback = $cashback * $qty;
            DB::table('store_campaigns')->where([
                ['store_id', '=', $storeID],
                ['status', '=', 'active'],
            ])->increment('toal_gave_back', $cashback);
            $this->_poolTAccess->debitPool($cashback, 'debit', $CASHBACK_POOL_ID, $trans_id = 0, $orderId);

            //credit user wallet
            $wtransaction_info = 'Zit Back received on purchase of ' . $query->title;
            $this->_walletAccess->creditWalletWithZit($cashback, $userId, 'credit', $wtransaction_info, $trans_id = 0);
            addFlashSaleLog($productId,'standard',$productPrice);
        } else {

            $cashback = $constants['DEFAULT_CASHBACK'];

            $cashback = ($cashback * $productPrice) / 100;
            $cashback = round($cashback, 3);
            $cashback = $cashback * $qty;
            $this->_poolTAccess->debitPool($cashback, 'debit', $CASHBACK_POOL_ID, $trans_id = 0, $orderId);

            //credit user wallet
            $wtransaction_info = 'Zit Back received on purchase of ' . $query->title;
            $this->_walletAccess->creditWalletWithZit($cashback, $userId, 'credit', $wtransaction_info, $trans_id = 0);
        }

        if (!$campaign_id) {
            // do nothing
        } else {
            $campaign_id = $campaign_id->store_campaign_id;
            $alreadySold = DB::table('campaign_product_stats')
                    ->where('product_id', $productId)
                    ->where('campaign_id', $campaign_id)
                    ->count();

            if ($alreadySold > 0) {
                // increment sold quantity 
                DB::table('campaign_product_stats')->where([
                    ['product_id', '=', $productId],
                    ['campaign_id', '=', $campaign_id],
                ])->increment('sold_qty', $qty);
                // increment sold amount cashback 
                DB::table('campaign_product_stats')->where([
                    ['product_id', '=', $productId],
                    ['campaign_id', '=', $campaign_id],
                ])->increment('sold_amount', $cashback);
            } else {
                DB::table('campaign_product_stats')->insert(['campaign_id' => $campaign_id, 'product_id' => $productId, 'sold_qty' => $qty, 'sold_amount' => $cashback]);
            }
        }
    }

}
