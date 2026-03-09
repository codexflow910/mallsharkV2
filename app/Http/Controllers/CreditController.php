<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Repositories\Credit\Creditaccess;
use Redirect;
use Session;
use DB;

class CreditController extends Controller {

    private $_request;
    private $_creditobj;

    public function __construct(Request $request) {
        $this->_request = $request;
        $this->_creditobj = new Creditaccess();
        //$this->_favobj = new Favouritesaccess();
    }

    public function index($id) {
        $userAvailableCredits = '';
        $this->_request->session()->put('featur_product_id', $id);
        if (Session::has('userData')) {
            $userid = Session::get('userData')['user_id'];
            $userData = getUserById($userid);
            $userAvailableCredits = $userData->user_credits;
            $featurdata = $this->_creditobj->getFeaturePricing();

            $featurprice = $featurdata['price'];
            if ($userAvailableCredits >= $featurprice) {
                $this->_creditobj->makeProductFeature($id, $paystripe = false);

                $this->_request->session()->flash('SuccessMessage', 'Product has been featured successfully.');
                return redirect()->back();
            } else {
                $credits = $this->_creditobj->getCreditPackages();
                return view('credits.buycredit', ['credits' => $credits, 'userAvailableCredits' => $userAvailableCredits]);
            }
        }
    }

    public function PostCredit($id) {
        $userAvailableCredits = '';
        $this->_request->session()->put('featur_post_id', $id);
        if (Session::has('userData')) {
            $userid = Session::get('userData')['user_id'];
            $userData = getUserById($userid);
            $userAvailableCredits = $userData[0]->user_credits;
            $featurdata = $this->_creditobj->getFeaturePricing();
            $featurprice = $featurdata['price'];
            if ($userAvailableCredits >= $featurprice) {
                $this->_creditobj->makePostFeature($id, $paystripe = false);
                $this->_request->session()->flash('SuccessMessage', 'Post has been featured successfully.');
                return redirect()->back();
            } else {
                $credits = $this->_creditobj->getCreditPackages();
                return view('credits.buypostcredit', ['credits' => $credits, 'userAvailableCredits' => $userAvailableCredits]);
            }
        }
    }

    public function buyCredits($id) {
        $creditPlan = $this->_creditobj->getCreditPackageById($id);
        $this->_request->session()->put('purchased_plan', $creditPlan);
        return view('credits.paywithstripe', ['creditPlan' => $creditPlan]);
    }

    public function purchasePostCredits($id) {
        $creditPlan = $this->_creditobj->getCreditPackageById($id);
        $this->_request->session()->put('purchased_post_plan', $creditPlan);
        return view('credits.postwithstripe', ['creditPlan' => $creditPlan]);
    }

    public function purchaseCredits() {
        $userAvailableCredits = "";
        $credits = $this->_creditobj->getCreditPackages();
        if (Session::has('userData')) {
            $userid = Session::get('userData')['user_id'];
            $userData = getUserById($userid);
            $userAvailableCredits = $userData->user_credits;
            $pointHistory = DB::table('points_history')->where('user_id', $userid)->get()->toArray();
        }
        if ($this->isMobileDevice()) {
            return view('credits/mobile/store-usercredit', ['credits' => $credits, 'userAvailableCredits' => $userAvailableCredits, 'pointHistory' => $pointHistory]);
        } else {
            return view('credits.store-usercredit', ['credits' => $credits, 'userAvailableCredits' => $userAvailableCredits, 'pointHistory' => $pointHistory]);
        }
    }

    public function userPurchaseCredits() {
        $userAvailableCredits = "";
        $credits = $this->_creditobj->getCreditPackages();
        if (Session::has('userData')) {
            $userid = Session::get('userData')['user_id'];
            $userData = getUserById($userid);
            $userAvailableCredits = $userData->user_credits;
            $pointHistory = DB::table('points_history')->where('user_id', $userid)->get()->toArray();
        }
        return view('credits.usercredit', ['credits' => $credits, 'userAvailableCredits' => $userAvailableCredits, 'pointHistory' => $pointHistory]);
    }

    public function purchaseUserCredits($id) {
        $creditPlan = $this->_creditobj->getCreditPackageById($id);
        $this->_request->session()->put('purchased_user_plan', $creditPlan);
        return view('credits.usercreditpay', ['creditPlan' => $creditPlan]);
    }

    public function purchaseStoreUserCredits($id) {
        $creditPlan = $this->_creditobj->getCreditPackageById($id);
        $this->_request->session()->put('purchased_user_plan', $creditPlan);
        return view('credits.storeusercreditpay', ['creditPlan' => $creditPlan]);
    }

    public function storesubscription() {
        return view('credits.store-subscription');
    }

}
