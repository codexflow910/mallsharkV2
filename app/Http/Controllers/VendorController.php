<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Repositories\Store\Storeaccess;
//use App\Repositories\VendorReviews\VendorReviewsaccess;
//use App\Repositories\Location\LocationAccess;
use App\Repositories\Product\Productaccess;
use App\Repositories\search\Searchaccess;
use App\Repositories\Orders\StoreOrdersAccess;
//use App\Http\Controllers\StripeController;
use Crypt;
use Redirect;
use Session;
use Cookie;
use File;
use App\Models\Attachments;
use App\Models\User;
use DB;

class VendorController extends Controller {

    private $_storeobj;
    private $_storereview;
    private $_request;
    private $_productobj;
    private $_stripeObj;

//    
    public function __construct(Request $request) {
        $this->_storeobj = new Storeaccess();
        //$this->_storereview = new VendorReviewsaccess();
        //$this->_locationobj = new LocationAccess();
        $this->_productobj = new Productaccess();
        $this->_stripeObj = new StripeController();
        $this->_request = $request;
    }

    public function index() {
        $userId = Session::get('userData')['user_id'];
        $storeData = getStoreDataByUserId($userId);
        if (!empty($storeData)) {
            $userData = getUserById($userId);
            $totalcompletedOrders = $this->_storeobj->countTotalStoreOrders($storeData->store_id, $status = "completed");
            $totalcanceledOrders = $this->_storeobj->countTotalStoreOrders($storeData->store_id, $status = "cancelled");
            $currentMonthComplete = $this->_storeobj->countTotalStoreOrders($storeData->store_id, $status = "completed", $current = "Month");
            $currentMonthPending = $this->_storeobj->countTotalStoreOrders($storeData->store_id, $status = "pending", $current = "Month");
            $totalProducts = $this->_storeobj->getStoreProductCount($storeData->store_id, "decode");
            //$storeCountry = $this->_locationobj->getAllCountryById($storeData->store_country);
            $stripeConnectedAccount = $this->_stripeObj->getConnectedAccountByUserId();
            $stripeSubscription = $this->_stripeObj->getSubscriptionsByUserId();
            $connectAccount = $this->_stripeObj->connectAccount();
            return view('vendor/store', ['stripeSub' => $stripeSubscription, 'storeData' => $storeData, 'userData' => $userData, 'totalProducts' => $totalProducts, 'currentMonthComplete' => $currentMonthComplete, 'totalcanceledOrders' => $totalcanceledOrders, 'totalcanceledOrders' => $totalcanceledOrders, 'currentMonthPending' => $currentMonthPending, "totalcompletedOrders" => $totalcompletedOrders, 'totalProducts' => $totalProducts, 'connectAccount' => $connectAccount, 'stripeConnectedAccount' => $stripeConnectedAccount]);
        } else {
            return '<h2>You dont have any store</h2>';
        }
    }
    public function Payments(){
         $userId = Session::get('userData')['user_id'];
        $storeData = getStoreDataByUserId($userId);
        if (!empty($storeData)) {
            $userData = getUserById($userId);
            $totalcompletedOrders = $this->_storeobj->countTotalStoreOrders($storeData->store_id, $status = "completed");
            $totalcanceledOrders = $this->_storeobj->countTotalStoreOrders($storeData->store_id, $status = "cancelled");
            $currentMonthComplete = $this->_storeobj->countTotalStoreOrders($storeData->store_id, $status = "completed", $current = "Month");
            $currentMonthPending = $this->_storeobj->countTotalStoreOrders($storeData->store_id, $status = "pending", $current = "Month");
            $totalProducts = $this->_storeobj->getStoreProductCount($storeData->store_id, "decode");
            //$storeCountry = $this->_locationobj->getAllCountryById($storeData->store_country);
            $stripeConnectedAccount = $this->_stripeObj->getConnectedAccountByUserId();
            $stripeSubscription = $this->_stripeObj->getSubscriptionsByUserId();
            $connectAccount = $this->_stripeObj->connectAccount();
            return view('vendor/payments', ['stripeConnectedAccount' => $stripeConnectedAccount,'connectAccount'=>$connectAccount]);
        } else {
            return '<h2>You dont have any store</h2>';
        }
        
    }

    public function addVendor() {

        if ($this->_request->post()) {

            $postedData = $this->_request->input();

            $this->_storeobj->saveStore($postedData);
            $this->_request->session()->flash('SuccessMessage', 'Company profile  has been created successfully.');
            return redirect('/store-dashboard');
        }

        if (Session::has('userData')) {
            $userId = Session::get('userData')['user_id'];
            $storeData = getStoreDataByUserId($userId);
            if (!empty($storeData)) {
                return redirect('/store/edit');
            } else {
                return view('vendor/userbasic', ['actionUrl' => 'store/create']);
            }
        }
    }

    public function editVendor() {
        if ($this->_request->post()) {

            $postedData = $this->_request->input();
            $this->_storeobj->updateStore($postedData);
            $this->_request->session()->flash('SuccessMessage', 'Company profile data  has been updated successfully.');
            return redirect()->back();
        }
        $userId = Session::get('userData')['user_id'];
        $storeData = getStoreDataByUserId($userId);
        $storeData = $this->objToArray($storeData);
        $states = $this->_locationobj->getStateByCountryCode($storeData['store_country']);
        return view('vendor/userbasic', ['actionUrl' => 'store/edit', 'storeData' => $storeData, 'states' => $states]);
    }

    public function storeProducts(Request $request) {
        $data = $request->all();
        $results = $this->_storeobj->getProductsBystoreID($data);
        $prodcutFeaturePrice = $this->_storeobj->getFeaturePricing();
        if (Session::has('userData')) {
            $userid = Session::get('userData')['user_id'];
            $userData = getUserById($userid);
            $userAvailableCredits = $userData[0]->user_credits;
        }
        return view('vendor/storeproducts', ['results' => $results, 'prodcutFeaturePrice' => $prodcutFeaturePrice, 'userAvailableCredits' => $userAvailableCredits]);
    }

    public function deleteProduct($productId) {
        $results = $this->_storeobj->delProductById($productId);
        $this->_request->session()->flash('ErrorMessage', 'Product has been deleted successfully.');
        return redirect()->back();
    }

    public function ChangeStatus($productId, $status) {
        $result = $this->_storeobj->ChangeStatusByid($productId, $status);
        if ($result) {
            $this->_request->session()->flash('SuccessMessage', 'Product status changed successfully.');
        } else {
            $this->_request->session()->flash('ErrorMessage', 'Product Status is not changed please contact to technical dept.');
        }
        return redirect()->back();
    }

    public function uploadBrandLogo() {
        $data = array();
        if ($this->_request->hasFile('file')) {
            $imageObj = $this->_request->file('file');
            if ($imageObj) {
                $fileName = $this->_productobj->uploadTempFiles($imageObj, 'file', '');
                $attachmentId = $this->_productobj->getAttachmentId($fileName['fileName'][0], 'image', true, false);
                $data['id'] = $attachmentId;
                $data['image'] = getAttachmentUrlById($attachmentId, 'media', '');
                return $data;
            }
        }
    }

    public function getStatesofCountry() {

        if ($this->_request->ajax()) {
            $countryId = $this->_request->input('country_id');
            $states = $this->_locationobj->getStateByCountryCode($countryId);

            $stateHtml = $this->builtHtmlforStates($states);
            return $stateHtml;
        }
    }

    public function builtHtmlforStates($data) {
        $html = "<option value=''>Please select</option>";
        if (count($data) > 0) {
            foreach ($data as $d) {
                $html .= '<option value="' . $d['state_id'] . '">' . $d['state_name'] . '</option>';
            }
        }
        return $html;
    }

    public function activateStore() {
        if ($this->_request->ajax()) {
            $posedData = $this->_request->input();
            $this->_storeobj->changeStoreStatus($posedData);
        }
    }

    public function storeDeactivate() {
        return view('vendor/store-deactivate');
    }

    public function chat() {
        $users = User::all();
        return view('messages/users', compact('users'));
    }

    public function storeTemplates(Request $req) {
        $default_view = $req->input('defaulttemplate');
        $alldata = $req->all();
        $attachmentId1 = '';
        $attachmentId2 = '';
        $attachmentId3 = '';

        if (isset($alldata['banner1_img'])) {
            $attachmentId1 = $alldata['banner1_img'];
        }
        if ($req->hasfile('banner1')) {

            $imageObj = array($req->file('banner1'));

            if ($imageObj) {
                $fileName = $this->_productobj->uploadTempFiles($imageObj, 'file', '');

                $attachmentId1 = $this->_productobj->getAttachmentId($fileName['fileName'][0], 'image', true, false);


//                }
            }
        }

        if (isset($alldata['banner2_img'])) {
            $attachmentId2 = $alldata['banner2_img'];
        }
        if ($req->hasfile('banner2')) {

            $imageObj = array($req->file('banner2'));

            if ($imageObj) {
                $fileName = $this->_productobj->uploadTempFiles($imageObj, 'file', '');

                $attachmentId2 = $this->_productobj->getAttachmentId($fileName['fileName'][0], 'image', true, false);


//                }
            }
        }

        if (isset($alldata['banner3_img'])) {
            $attachmentId3 = $alldata['banner3_img'];
        }
        if ($req->hasfile('banner3')) {

            $imageObj = array($req->file('banner3'));

            if ($imageObj) {
                $fileName = $this->_productobj->uploadTempFiles($imageObj, 'file', '');

                $attachmentId3 = $this->_productobj->getAttachmentId($fileName['fileName'][0], 'image', true, false);


//                }
            }
        }
        $message = '';
        if ($default_view != '') {
           
            $res = DB::table('stores')->where('user_id', getCurrentUserId())->update(['default_view' => $default_view, 'image_id1' => $attachmentId1, 'image_id2' => $attachmentId2, 'image_id3' => $attachmentId3]);
            $message = 'Update Successfully';

            //$selected=$selected[0]->
        }
        $results = DB::table('stores')->where('user_id', getCurrentUserId())->select('*')->get()->toArray();
        $selected = isset($results[0]->default_view) ? $results[0]->default_view : 1;
        return view('vendor/store-template')->with(array('message' => $message, 'selected' => $selected,'results'=>$results));
    }

    public function chatHistory($id) {
        $conversations = Talk::getMessagesByUserId($id, 0, 5);
        $user = '';
        $messages = [];
        if (!$conversations) {
            $user = User::find($id);
        } else {
            $user = $conversations->withUser;
            $messages = $conversations->messages;
        }

        return view('messages.conversations', compact('messages', 'user'));
    }

    public function checkShopAvailablity() {
        $postedData = $this->_request->input();
        $result = $this->_storeobj->getStoreByname($postedData);
        if ($result == true) {
            echo 'true';
        } else {
            echo 'false';
        }
    }

    public function objToArray($arr) {
        $arr = json_encode($arr);
        $res = json_decode($arr, true);
        return $res;
    }

    public function storeReports() {
        return view('vendor.reports');
    }
    public function storeMArketing() {
        return view('vendor.store-marketing');
    }

}
