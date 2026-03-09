<?php

namespace App\Repositories;

use App\BaseAccess;
use Illuminate\Http\Request;
use DB;

class Formulas extends BaseAccess {
    /*
     * formula properties
     */

    public $pledgesInOneCredit;
    public $onePledgePrice;
    public $sellerRevenueEndLimit;
    public $sellerRevenueStartLimit;
    public $cashbackPoolStartLimit;
    public $cashbackPoolEndLimit;
    public $supersupporterPoolStartLimit;
    public $supersupporterPoolEndLimit;
    public $shopzitPercentage;
    private $cashbackFromShopzitModelA;
    private $cashbackFromShopzitModelB;
    private $TransactionCharges;
    private $CashbackModelAStartLimit;
    private $CashbackModelAEndLimit;
    private $CashbackModelBStartLimit;
    private $CashbackModelBEndLimit;
    private $AIPincreasePercent;
    private $AIPincreaseConnetcedPercent;
    private $defaultCashback;
    private $choosen_cashback;

    public function __construct() {
        $constants = config('constants.shopzit_gamification');

        $this->sellerRevenueStartLimit = $constants['SELLER_REVENUE_START_PERCENTGE']; //50
        $this->sellerRevenueEndLimit = $constants['SELLER_REVENUE_END_PERCENTGE']; //70
        $this->cashbackPoolStartLimit = $constants['CASHBACK_POOL_SHARE_START_PECENTAGE']; //5
        $this->cashbackPoolEndLimit = $constants['CASHBACK_POOL_SHARE_END_PECENTAGE']; //15
        $this->supersupporterPoolStartLimit = $constants['SUPERSUPPORTER_POOL_SHARE_START_PECENTAGE']; //20
        $this->supersupporterPoolEndLimit = $constants['SUPERSUPPORTER_POOL_SHARE_END_PECENTAGE']; //30
        $this->shopzitPercentage = $constants['SHOPZIT_PERCENTAGE']; //5
        $this->TransactionCharges = $constants['SHOPZIT_PERCENTAGE'];
        /*
          $this->cashbackFromShopzitModelA = $constants['CASH_BACK_FROM_SHOPZIT_MODEL_A']; //10
          $this->cashbackFromShopzitModelB = $constants['CASH_BACK_FROM_SHOPZIT_MODEL_B']; //20
         * 
         */
        $this->AIPincreasePercent = $constants['AIP_PRICE_INCREASE_PERCENT']; //1
        $this->pledgeAmountCashbackpool = $constants['PLEDGE_AMOUNT_GO_TO_CASHBACK_POOL']; //97 percent
        $this->pledgeAmountBurnPool = $constants['TRANSACTION_CHARGES_PERCENTAGE_P_BP']; //1 percent
        /*
          $this->CashbackModelAStartLimit = $constants['CASHBACK_MODEL_A_PERCENTAGE_START_LIMIT']; //3
          $this->CashbackModelAEndLimit = $constants['CASHBACK_MODEL_A_PERCENTAGE_END_LIMIT']; //25
          $this->CashbackModelBStartLimit = $constants['CASHBACK_MODEL_B_PERCENTAGE_START_LIMIT']; //16
          $this->CashbackModelBEndLimit = $constants['CASHBACK_MODEL_B_PERCENTAGE_END_LIMIT']; //95
         * 
         */
        $this->AIPincreaseConnetcedPercent = $constants['AIP_PRICE_CONNECTED_INCREASE_PERCENT'];
        $this->defaultCashback = $constants['DEFAULT_CASHBACK_TO_BUYER']; //2
        $this->pledgesInOneCredit = $constants['PLEDGES_IN_ONE_CREDIT']; //1000
        $this->onePledgePrice = $constants['ONE_PLEDGE_PRICE']; //1 zit
        $this->choosen_cashback = $constants['CHOOSEN_CASHBACK'];
    }

    /*
     * method to get animal icon price AIP
     */

    public function getAIP($animalIconId, $userID) {
        $result = DB::table('herds')->get('icon_price')->where('icon_id', $animalIconId)->where('user_id', $userID)->first();
        return $result->icon_price;
    }

    /*
     * method to get user herd value UHV
     */

    public function getUHV($userHerdId) {
        // current AIP of all animal icons - purchase AIP of all icons
        $totalHerdPrice = DB::table('herds')->join('herd_users', 'herds.herd_id', '=', 'herd_users.seller_herd_id')->where('herd_users.user_herd_id', $userHerdId)->sum('herds.icon_price');
        $user_herd = DB::table('herds')->where('herd_id', $userHerdId)->get();
        $uhv = $user_herd->icon_price + $totalHerdPrice - $user_herd->init_price;
        return $uhv;
    }

    /*
     * method to get collective debt
     */

    public function getDebtAll($iconID) {
        $result = DB::table('herds')->where('icon_id', $iconID)->sum('icon_debt');
        return $result;
    }

    /*
     * metgod to get seller herd value
     */

    public function getSHV($iconId) {
        // All AIP of icons- All depts of icons
        $icon_price = DB::table('herds')->where('icon_id', $iconId)->sum('icon_price');
        $icon_debt = DB::table('herds')->where('icon_id', $iconId)->sum('icon_debt');
        $shv = $icon_price - $icon_debt;
        return $shv;
    }

    /*
     * get user wallet transection history
     */

    public function getTransectionHistoryWallet($walletId, $duration) {
        
    }

    /*
     * add zit to user wallet
     */

    public function creditToUserWallet($user_id, $zit_amount) {
        $user_wallet = DB::table('users')->get('user_wallet')->where('user_id', $user_id);
        $user_wallet = $user_wallet + $zit_amount;
    }

    /*
     * debt zit from user wallet
     */

    public function debitToUserWallet($user_id) {
        
    }

    /*
     * get user wallet total
     */

    public function getWalletTotal($user_id) {
        
    }

    /*
     * method to verify icon eligibility to go in super supporter pool
     */

    protected function isSuperSupporterPoolIcon($iconID, $poolId) {
        
    }

    /*
     * method to calculate number of credits required to run a campaign Aodel Not Applicable for now
     */
    /*
      protected function campaignCreditsForModelA($cashbackPoolPercentage, $numberOfDays, $cashbackToBuyer) {
      // get model a max limit of cashback
      $this->CashbackModelAEndLimit = $cashbackPoolPercentage + $this->cashbackFromShopzitModelA;
      if ($cashbackToBuyer > $this->CashbackModelAEndLimit) {
      return array('error' => 'you cannnot give cashback to buyer greater than' . $this->CashbackModelAEndLimit . '%');
      }
      // get total credits required for runing campaign
      $creditsRequired = $numberOfDays + ($cashbackToBuyer - $this->defaultCashback);
      // get percentage amount that shop zit will refund in from of cashback
      $shopzitWillFund = $cashbackToBuyer - $cashbackPoolPercentage;
      if ($shopzitWillFund < 0) {
      $shopzitWillFund = 0;
      }
      return array('credits_required' => $creditsRequired, 'shopzit_will_fund' => $shopzitWillFund);
      }
     */
    /*
     * method to calculate number of credits required to run a campaign Aodel B NotApplicable for now
     */
    /*
      protected function campaignCreditsForModelB($cashbackPoolPercentage, $numberOfHours, $cashbackToBuyer) {
      // check if cashback to buyer did not exceed model b maximum limit
      $errors = array();
      if ($cashbackToBuyer > $this->CashbackModelBEndLimit) {
      return array('error' => 'you cannnot give cashback to buyer greater than' . $this->CashbackModelBEndLimit . '%');
      }

      // total credits required to run this campaign

      $creditsRequired = $numberOfHours;
      // get percentage amount that shopzit will refund in from of cashback
      $shopzitWillFund = $cashbackToBuyer - $cashbackPoolPercentage;
      $refundFromRevenue = 0;
      if ($shopzitWillFund < 0) {
      $shopzitWillFund = 0;
      }

      if ($shopzitWillFund > $this->cashbackFromShopzitModelB) {
      // the amount in percentage that would be refunded from user revenue
      $refundFromRevenue = $shopzitWillFund - $this->cashbackFromShopzitModelB;
      $shopzitWillFund = $this->cashbackFromShopzitModelB;
      }


      return array('credits_required' => $creditsRequired, 'shopzit_will_fund' => $shopzitWillFund, 'refund_from_revenue' => $refundFromRevenue);
      }
     */
    /*
     * method to get percentage of super supporter pool while adding product
     */

    public function getsupersupporterPercentage($sellerShare) {
        $superSupporter = 100 - $this->shopzitPercentage - $sellerShare;
        //$superSupporter = ($this->supersupporterPoolEndLimit - ($sellerShare - $this->sellerRevenueStartLimit) / 2);
        if ($superSupporter > $this->supersupporterPoolEndLimit) {
            $superSupporter = $this->supersupporterPoolEndLimit;
        }
        return $superSupporter;
    }

    /*
     * method to get percentage of super supporter pool while adding product
     */

    public function getCashbackPercentage($sellerShare) {
        //$superSupporterShare = $this->getsupersupporterPercentage($sellerShare);
        //return $cashback = 100 - $this->shopzitPercentage - $sellerShare - $superSupporterShare;
        return $this->cashbackPoolEndLimit;
    }

    /*
     * method to calculate user share from product selling
     */

    public function getUserSharefromSelling($productID, $userId) {
        
    }

    /*
     * method to calculate share of super supporter pool from product selling
     */

    public function getSuperSupporterPoolSharefromSelling() {
        
    }

    /*
     * method to calculate share of foundation pool from product selling
     */

    public function getFoundationPoolSharefromSelling() {
        
    }

    /*
     * method to calculate share of cashback pool from  product selling
     */

    public function getCashbackPoolSharefromSelling() {
        
    }

    /*
     * method to calculate share of transaction pool from product selling
     */

    public function getTransactionPoolSharefromSelling() {
        
    }

    /*
     * method to calculate share of burn pool from product selling
     */

    public function getBurnPoolSharefromSelling() {
        
    }

    /*
     * method to icrease ainmal icon value
     */

    public function addAIP($iconId) {
        
    }

    /*
     * method to add debt to animal icon price
     */

    public function setDebtToAIP($iconID, $debtAmount) {
        
    }

    /*
     * method to check if AIP has a debt already
     */

    protected function getDebt($iconID) {
        
    }

    /*
     * clear debt from animal icon price
     */

    public function clearDebtToAIP($debtAmount, $iconId) {
        
    }

    /*
     * method to get overll debt of herd
     */

    public function getDebtOfHerd($herdId) {
        
    }

    public function getPercentageAmount($amount, $percent) {
        return $result = ($amount) * ($percent / 100);
    }

    public function maxYouCanSell($productPrice, $campaignTotal) {
        $cashback = $this->choosen_cashback;
        $cashbackAmount = ($productPrice * $cashback) / 100;
        $maxSell = $campaignTotal / $cashbackAmount;
        return $maxSell = round($maxSell);
    }

    public function getIconPriceIncrease($old_price, $new_price) {
        $increase = 100 - (($old_price / $new_price) * 100);
        return number_format($increase, 2, '.', '');
    }

}
