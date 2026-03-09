<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Creditaccess
 *
 * @author Asif
 */

namespace App\Repositories\Credit;

use App\Models\Credits;
use App\Models\FeaturePrices;
use App\Models\Product;
use App\Models\User;
use App\BaseAccess;
use Session;
use App\Http\Controllers\ElasticSearchController;

class Creditaccess extends BaseAccess {

    private $_credits;
    private $_user;
    private $_esearch;

    public function __construct() {
        $this->_credits = new Credits();
        $this->_prodcut = new Product();
        $this->_user = new User();
        $this->_esearch = new ElasticSearchController();
    }

    public function getCreditPackages() {
        $query = $this->_credits->get();
        if (!empty($query)) {
            return $query->toArray();
        } else {
            return false;
        }
    }

    public function getCreditPackageById($planId) {
        $query = $this->_credits->where('credit_plan_id', '=', $planId)->first();
        if (!empty($query)) {
            return $query->toArray();
        } else {
            return false;
        }
    }

    public function getFeaturePricing() {
        $featureObj = new FeaturePrices();
        $query = $featureObj->where('feature_price_id', '=', 1)->first();
        if (!empty($query)) {
            return $query->toArray();
        } else {
            return false;
        }
    }

    public function makeProductFeature($productId, $paystripe = false) {
        $product = new Product();
        $featureenddate = date('Y-m-d', strtotime('+1 month'));
        $featurestartdate = date('Y-m-d');
        $feature = $this->getFeaturePricing();
        $featurePrice = $feature['price'];
        $product->where('product_id', '=', $productId)->update([
            'feature_startdate' => $featurestartdate,
            'feature_enddate' => $featureenddate,
            'is_feature' => "1",
            'feature_price' => $featurePrice,
        ]);
        if (Session::has('userData')) {
            $userObj = new User();
            $userid = Session::get('userData')['user_id'];
            $userData = getUserById($userid);
            $userAvailableCredits = $userData->user_credits;
            if ($paystripe == true) {
                $userAvailableCredits = Session::get('purchased_plan')['plan_points'];
            }
            
            
            $remainCredits = (int) $userAvailableCredits - (int) $featurePrice;
            $userObj->where('user_id', '=', $userid)->update(['user_credits' => $remainCredits]);
            
        }
    }
    public function updateUserCredit() {
        if (Session::has('userData')) {
            $userObj = new User();
            $userid = Session::get('userData')['user_id'];
            $userData = getUserById($userid);
            $userAvailableCredits = $userData->user_credits;
            $prechasedCredit = Session::get('purchased_user_plan')['plan_points'];
            $totalCredit = (int) $userAvailableCredits + (int) $prechasedCredit;
            $userObj->where('user_id', '=', $userid)->update(['user_credits' => $totalCredit]);
        }
    }

}
