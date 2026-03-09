<?php
 
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Repositories\Buyer\Buyeraccess;
use App\Repositories\Location\LocationAccess;
use App\Repositories\Product\Productaccess;
use App\Repositories\Orders\StoreOrdersAccess;
use App\Repositories\Campaign\Campaignaccess;
use App\Repositories\Transactions\WalletAccess;
use App\Repositories\Herd\Herdaccess;
use Redirect;
use File;
use Session;
use DB;

class BuyerController extends Controller {

    private $_buyerobj;
    private $_locationobj;
    private $_request;
    private $productObj;
    private $access_obj;

    public function __construct(Request $request) {
        $this->_request = $request;
        $this->_buyerobj = new Buyeraccess();
        $this->_locationobj = new LocationAccess();
        $this->productObj = new Productaccess();
        $this->access_obj = new StoreOrdersAccess();
    }

    public function index() {
        $userData = $this->_buyerobj->getProfileData();
        $shippingInfo = $this->_buyerobj->getcurrentShippingAddress();
        // $userData['statename'] = $this->_locationobj->getStateByStateCode($userData['user_state_id'], $userData['user_country_id']);
        $favorites = $this->_buyerobj->getCountFavorites();
         $categories = DB::table('categories')->select('categoryTitle', 'id')->where('parentCategoryID', '=', 0)->get();
        // if ($this->isMobileDevice()) {
        //     return view('buyer/mobile/index', ['userData' => $userData, 'shippingInfo' => $shippingInfo, 'favorites' => $favorites,'categories' => $categories]);
        // } else {
            return view('buyer/index', ['userData' => $userData, 'shippingInfo' => $shippingInfo, 'favorites' => $favorites,'categories' => $categories]);
        // }
    }

    public function userInfo() {
        if ($this->_request->isMethod('post')) {
//            $this->_request->validate([
//                'txtname' => 'required',
//                'email' => 'unique:users,email',
//                'password' => 'required|confirmed',
//            ]);
            $postedData = $this->_request->input();
            $result = $this->_buyerobj->UpdateProfileData($postedData);
            if ($result == 1) {
                $this->_request->session()->flash('updatesuccessmessage', 'User information has been updated successfully');
                return redirect()->back();
            } else if ($result == 0) {

                $this->_request->session()->flash('updateerrormessage', 'Please enter correct current password');
                return redirect()->back();
            } else if ($result == 2) {

                $this->_request->session()->flash('updateerrormessage', 'Please enter current password');
                return redirect()->back();
            }
        }
        $userData = $this->_buyerobj->getProfileData();
        // if ($this->isMobileDevice()) {
        //     return view('buyer/mobile/useraccount', ['userData' => $userData]);
        // } else {
            return view('buyer/useraccount', ['userData' => $userData]);
        // }
    }

    public function getCampaigns() {
        return view('buyer/campaigns');
    }

    public function validateEmail() {
        if ($this->_request->ajax()) {
            $email = $this->_request->input('email');
            $result = $this->_buyerobj->checkEmail($email);
            if ($result > 0) {
                echo "false";
            } else {
                echo "true";
            }
        }
    }

    public function userOrders(Request $req) {
        //  dd($req->input());
        $mesage = '';
        $active = '#home';
        $order_status = $req->input('status');
       
        $input = $req->all();
        if (isset($input['status']) && $input['status'] != '' && isset($input['order_id']) && $input['order_id'] != '') {
            $active = '#' . $input['active_tab'];
            // echo '$input: <pre>' .print_r($input,true). '</pre>'; die;
            // if ($this->access_obj->update_order_status($input)) {
            if ($this->access_obj->approval_order_status($input)) {
                $mesage = 'Status updated successfully';
            } else {
                $mesage = 'Status is not changed please contact to super admin';
            }
        }
        if ($order_status == '') {
            $all = $this->_buyerobj->getallOrders();
        } else {
            $all = $this->_buyerobj->getallOrdersStatus($order_status);
           
        }
        // if ($this->isMobileDevice()) {
        //     return View('buyer.mobile.buyer_orders', array(
        //         'orders_all' => $all,
        //         'mesage' => $mesage,
        //         'active' => $active,
        //     ));
        // } else {
            return View('buyer.buyer_orders', array(
                'orders_all' => $all, 'status' => $order_status,
                /*
                  'orders_completed' => $completed,
                  'orders_new' => $new,
                  'orders_processing' => $processiong,
                  'orders_shipped' => $shipped,
                  'orders_cancelled' => $cancell,
                 * 
                 */
                'mesage' => $mesage,
                'active' => $active,
            ));
        // }
    }
    // New order page
    public function userOrdersNew(Request $req) {
        $mesage = '';
        $active = '#home';
        $order_status = $req->input('status');
       
        $input = $req->all();
        if (isset($input['status']) && $input['status'] != '' && isset($input['order_id']) && $input['order_id'] != '') {
            $active = '#' . $input['active_tab'];
            // echo '$input: <pre>' .print_r($input,true). '</pre>'; die;
            // if ($this->access_obj->update_order_status($input)) {
            if ($this->access_obj->approval_order_status($input)) {
                $mesage = 'Status updated successfully';
            } else {
                $mesage = 'Status is not changed please contact to super admin';
            }
        }
        if ($order_status == '') {
            $all = $this->_buyerobj->getallOrdersNew();
        } else {
            $all = $this->_buyerobj->getallOrdersStatus($order_status);
           
        }
        // echo 'Data: <pre>' .print_r($all,true). '</pre>'; die();
        $status_list = [
            "All",
            "Pending",
            "Shipped",
            "Refunded",
            "Cancelled",
            "Disputed",
            "Completed",
        ];
        $data = [1, 2, 3, 4];
            return View('buyer.new_buyer_orders', array(
                'orders_all' => $all, 'status' => $order_status,
                'mesage' => $mesage,
                'active' => $active,
                "status_list" => $status_list, 
                "data" => $data,
            ));
    }
     // New order page Details
     public function userOrderDetail($order_id) {
        $order = getStoreOrder($order_id);
        if($order){

            $orderStatus = $order->order_status;
            
            $userId = getBuyerIdbyOrderId($order_id);
            $currentid = Session::get('userData')['user_id'];
            // echo '$userId: <pre>' .print_r($userId,true). '</pre>'; 
            // echo 'Data: <pre>' .print_r($currentid,true). '</pre>'; die();
            
            $all = $this->_buyerobj->getOrderDetailsNew($order_id);
            // dd($all);
            if ($all) {
                
                if($currentid == $userId['buyer_id']){
                    return view('buyer.buyer_order_detail', array(
                    'orders_all' => $all, 'status' => $orderStatus
                ));
            }else{
                return view('error.404page');
            }
        }else{
            
            return view('error.404page');
        } 
    }else{
        return view('error.404page');

    }
    }

    public function buyerOrderdetailsModal(Request $request)
    {
        try {
            $order_id = $request->input('order_id');
            $orderDetails = $this->access_obj->getOrderDetailByOrderId($order_id);
            
            
            if ($orderDetails) {
                return view('buyer.buyer_order_popup_modal_ajax', ['orderDetails' => $orderDetails]);
            }
        } catch (\Throwable $th) {

            return response()->json([
                'error' => $th->getMessage(),
            ], 500);
        }
    }
    public function buyerOrderPostModal(Request $request)
    {
        try {
            $product_id = $request->input('productId');
            $orderId = $request->input('orderId');
            return view('buyer.buyer_post_popup_modal_ajax', ['product_id' => $product_id , "orderId" => $orderId]);
            
        } catch (\Throwable $th) {

            return response()->json([
                'error' => $th->getMessage(),
            ], 500);
        }
    }

    public function userAccount() {

        if ($this->_request->isMethod('post')) {  
            $this->_request->validate([
                'user_name' => 'required',
                'user_country' => 'required',
                'user_state' => 'required',
                'user_city_id' => 'required',
                'user_zip_code' => 'required',
                'email' => 'required',
                
            ]);
            $postedData = $this->_request->input();
            $result = $this->_buyerobj->updateAccount($postedData);
            if ($result == true) {
                $this->_request->session()->flash('updatesuccessmessage', 'User information has been updated successfully.');
                // return redirect('/user-dashboard');
            }
        }
        $userData = $this->_buyerobj->getProfileData();
       // dd($userData);
        if (!empty($userData['user_country_id'])) {
            // $countryCode = $userData['user_country_id'];
            // $states = $this->_locationobj->getStateByCountryCode($countryCode);
        } else {
            $states = '';
        }
        $countries = array();
        //$states=array();
        // if ($this->isMobileDevice()) {
        //     return view('buyer/mobile/userbasic', ['userData' => $userData]);
        // } else {
            return view('buyer/userbasic', ['userData' => $userData]);
        // }
    }

    public function uploadUserImg() {
        
        $data = array();
        $img = str_replace('data:image/png;base64,', '', $this->_request->input('ImageData'));
        $img = str_replace(' ', '+', $img);
        $data = base64_decode($img);
      
        $fileName = $this->productObj->generateRandomString(15) . '.png';
        $fileName = 'cropImage--_--' . $this->productObj->generateRandomString(15) . '.png';
        $destinationPath = public_path() . '/uploads/temp';
        if (!File::exists($destinationPath)) {
            File::makeDirectory($destinationPath, 0775, true);
        }
        $file = $destinationPath . '/' . $fileName;
        file_put_contents($file, $data);
        $attachmentId = $this->productObj->getAttachmentId($fileName, 'image', true, false);
        
        $arr['id'] = $attachmentId;
        $arr['image'] = getAttachmentUrlById($attachmentId, 'media', '');
        echo json_encode($arr);
    //     echo '$arr: <pre>' .print_r($arr,true). '</pre>'; die;
    //     return response()->json([
    //         'id'=>$attachmentId,
    //         'image'=> getAttachmentUrlById($attachmentId, 'media', ''),
    // ]);
        // echo '$arr: <pre>' .print_r($arr,true). '</pre>'; die;
        // return $arr;
    }

    public function uploadTempImg() {
        $data = array();
        $img = str_replace('data:image/png;base64,', '', $this->_request->input('ImageData'));
        $img = str_replace(' ', '+', $img);
        $data = base64_decode($img);
        //////   print_rr($data);die;
        $fileName = $this->productObj->generateRandomString(15) . '.png';
        $fileName = 'cropImage--_--' . $this->productObj->generateRandomString(15) . '.png';
        $destinationPath = public_path() . '/uploads/temp';
        if (!File::exists($destinationPath)) {
            File::makeDirectory($destinationPath, 0775, true);
        }
        $file = $destinationPath . '/' . $fileName;
        file_put_contents($file, $data);
        return $fileName;
    }

    public function getStatesofCountry() {

        if ($this->_request->ajax()) {
            $countryId = $this->_request->input('country_id');
            $states = getStatesOfCountry($countryId);
            // print_rr($states); exit;
            $stateHtml = $this->builtHtmlforStates($states);
            return $stateHtml;
        }
    }

    public function builtHtmlforStates($data) {
        $html = "";
        if (count($data) > 0) {
            foreach ($data as $d) {
                $html .= '<option value="' . $d['state_id'] . '">' . ucfirst($d['state_name']) . '</option>';
            }
        }
  
        return $html;
    }

    public function editShippingAddress() {
        if ($this->_request->isMethod('post')) {
            $this->_request->validate([
                'full_name' => 'required',
                'user_country' => 'required',
                'user_state' => 'required',
                'post_code' => 'required',
                // 'flat_other' => 'required',
                'street' => 'required',
            ]);
            $postedData = $this->_request->input();
            $this->_buyerobj->updateShippingAddress($postedData);
            $this->_request->session()->flash('shippAddressmessage', 'Shipping address information has been updated successfully');
            return redirect()->back();
        }
        //$countries = $this->_locationobj->getAllCountries();
        $buyerShippingData = $this->_buyerobj->getShippingAddress();
        $states = getStatesOfCountry($buyerShippingData['country']);
        $countries = array();
        // $states=array();
        // if ($this->isMobileDevice()) {
        //     return view('buyer.mobile.usershippingaddress', ['countries' => $countries, 'buyerShippingData' => $buyerShippingData, 'states' => $states]);
        // } else {
            return view('buyer.usershippingaddress', ['countries' => $countries, 'buyerShippingData' => $buyerShippingData, 'states' => $states]);
        // }
    }

    public function reviews() {
        $all = $this->_buyerobj->getallOrders();
        return view('buyer.reviews', array('orders' => $all));
    }

    public function userAbout() {
        return view('buyer.userabout');
    }

    public function userKingdom() {
        $userId = Session::get('userData')['user_id'];
        $herdaccess = new Herdaccess();
        $userHerdsCount = $herdaccess->getUserHerdsCount($userId);
        $userHerds = $herdaccess->getUserHerds($userId);
        $userType = 'current';
        if ($userHerds) {
            foreach ($userHerds as $userHerd) {
                $userHerd->herdInfo = $herdaccess->getHerdInfo($userHerd, $userType);
                $campaignObj = new Campaignaccess();
                $userHerd->campaign = $campaignObj->getSupportingCampaign($userHerd->herdInfo['storeId']);
            }
        }
        $userData = getUserById($userId);
        return view('buyer.userkingdom', ['userData' => $userData, 'userType' => $userType, 'userHerds' => $userHerds, 'userHerdsCount' => $userHerdsCount]);
    }

    public function userWallet(Request $request) {
        $data = $request->all();
        $constants = config('constants.shopzit_gamification');
        if (!Session::get('userData')['user_id']) {
            $userType = 'other';
            $transactions = '';
            $currentVolum = '';
        } else {
            $userId = Session::get('userData')['user_id'];
            if(empty($userId)){
              $userId = Request::segment(2);  
            }
            $userType = 'current';
            $WalletAccess = new WalletAccess();
            $transactions = $WalletAccess->getTransactionHistory($constants['PAGINATION_LIMIT'], $userId, $data);
            $currentVolum = $WalletAccess->getWalletAmount($userId);
        }
        // if ($this->isMobileDevice()) {
        // return view('buyer.mobile.userWellet', ['transactions' => $transactions, 'currentVolum' => $currentVolum, 'userType' => $userType]);
        // }else{
        return view('buyer.userwallet', ['transactions' => $transactions, 'currentVolum' => $currentVolum, 'userType' => $userType]);
    // }
}

    public function changeAlertsStatus() {
        if ($this->_request->ajax()) {
            $postedData = $this->_request->input();
            $this->_buyerobj->changeAlerts($postedData);
        }
    }
    public function updateUserIntrest(){
        if ($this->_request->ajax()) {
            $postedData = $this->_request->input();
            $this->_buyerobj->updateIntrest($postedData);
        } 
    }

}
