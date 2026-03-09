<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Repositories\Buyer;

use DB;
use App;
use Crypt;
use Cookie;
use Request;
use App\BaseAccess;
use App\Models\User;
use App\Models\Store;
use App\Models\Favourites;
use App\Models\StoreOrder;
use App\Models\Attachments;
use Illuminate\Support\Str;
use App\Models\UserIntrests;
use App\Models\ShippingAddress;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use App\Repositories\Product\Productaccess;
use App\Repositories\Location\LocationAccess;
use Illuminate\Support\Facades\File;
use App\Http\Controllers\ElasticSearchController;
use Exception;

/**
 * Description of StoreAccess
 *
 * @author Asif
 */
class Buyeraccess extends BaseAccess {

    private $_user;
    private $product_Obj;
    private $shipping_obj;
    private $location_obj;
    private $fav_obj;
    private $_esearch;
    private $storeObj; 

    public function __construct() {
        $this->_user = new User();
        $this->product_Obj = new Productaccess();
        $this->shipping_obj = new ShippingAddress();
        $this->location_obj = new LocationAccess();
        $this->fav_obj = new Favourites();
        $this->storeObj = new Store();
        $this->_esearch = new ElasticSearchController();
    }

    public function getProfileData($data = '') {   
        // dd($data);     
        if(isset($data['return_source']) && $data['return_source'] == 'true'){
            $userId = Auth::user()->user_id;
           
        }else{
            $userId = Session::get('userData')['user_id'];
        }
            if (empty($userId)) {
                $userId = Request::segment(2);
            }
            if ($userId) {
                if(isset($data['return_source']) && $data['return_source'] == 'true'){
                $userData = $this->_user->select('user_id', 'name', 'email', 'status', 'ip_address', 'user_zip_code', 'contact_no', 'physical_address', 'about' )->where('user_id', '=', $userId)->first()->toArray();
                }else{
                $userData = $this->_user->where('user_id', '=', $userId)->first()->toArray();

                }
                if (!empty($userData)) {
                    return $userData;
                } else {
                    return false;
                }
            }
    }
    public function getstoreData() {
        $userId = Session::get('userData')['user_id'];
        $storeData = getStoreDataByUserId($userId); 
        $storeID =  !empty($storeData->store_id) ? $storeData->store_id :'';
        if (empty($storeID)) {
            $storeID = Request::segment(2);
        }
        if ($storeID) {
            $userData = $this->storeObj->where('store_id', '=', $storeID)->first()->toArray();
            if (!empty($userData)) {
                return $userData;
            } else {
                return false;
            }
        }
        // $userId = Session::get('userData')['user_id'];
        // if (empty($userId)) {
        //     $userId = Request::segment(2);
        // }
        // if ($userId) {
        //     $bankData = $this->storeObj->where('user_id', '=', $userId)->first()->toArray();
        //     if (!empty($userData)) {
        //         return $userData;
        //     } else {
        //         return false;
        //     }
        // }
    }

    public function UpdateProfileData($data) {
 
        $updateData = array();
        if(isset($data['return_source']) && $data['return_source'] == 'true'){
            $userId = Auth::user()->user_id;
        }else{
            $userId = Session::get('userData')['user_id'];
        }
        if (isset($data['email'])) {
            $updateData['email'] = $data['email'];
        }
         if(isset($data['return_source']) && $data['return_source'] == 'true'){
            $currentPassword = isset($data['current_password']) ? $data['current_password'] : "";
            $user = Auth::user();
            
            if (md5($currentPassword) !== $user->password) {
                return 2;
            }
            if (isset($data['current_password'])) {
                $updateData['password'] = md5($data['password']);
            }
         }
        $query = $this->_user->select('register_source', 'password')->where('user_id', $userId)->first();
        // update password in case of user register with face book
        if (isset($query) && ($query->register_source == 'facebook' || $query->register_source == 'google') && $query->password == '') {
            if (isset($data['password'])) {
                $updateData['password'] = md5($data['password']);
            }
            $this->_user->where('user_id', '=', $userId)->update($updateData);
            return 1;
        } // update password in case of user register with site
        else if (isset($data['txtpassword']) && $data['txtpassword'] != "") {
            $users = $this->_user->where('user_id', '=', $userId)
            ->where('password', '=', md5($data['txtpassword']))
            ->count();
            // dd($users);

            if ($users <= 0) {
                return 0;
            }else{
                if (isset($data['password'])) {
                    $updateData['password'] = md5($data['password']);
                }
            }
        } else if (empty($data['txtpassword'])) {
            return 2;
        }
        $this->_user->where('user_id', '=', $userId)->update($updateData);
        return true;
    }
    public function UpdatePasswordApi($data) {
        try{
            $updateData = array();
            $currentPassword = isset($data['current_password']) ? $data['current_password'] : "";
            $user = Auth::user();
            if (md5($currentPassword) !== $user->password) {
                return 2;
            }
            if (isset($data['new_password'])) {
                $updateData['password'] = md5($data['new_password']);
            
                $user->password = $updateData['password'];
                $user->save();
            return 1;
        }
        }catch(Exception $e){
            return 0;
        }
       

    }
    public function UpdateWalletData($data) {
        $updateData = array();
        $userId = Session::get('userData')['user_id'];
        $storeData = getStoreDataByUserId($userId); 
        $storeID =  !empty($storeData->store_id) ? $storeData->store_id :'';
        // echo 'Data: <pre>' .print_r($storeData,true). '</pre>'; exit;
        if (isset($data['wallet_address'])) {
            $updateData['wallet_address'] = $data['wallet_address'];
            $this->storeObj->where('store_id', '=', $storeID)->update($updateData);
            return 1;
        }
      
      if (isset($data['wallet_address']) && $data['wallet_address'] != "") {
            $users = $this->storeObj->where('store_id', '=', $storeID)
                    ->where('wallet_address', '=', $data['wallet_address'])
                    ->count();
            if ($users <= 0) {
                return 0;
            }
        } else if (empty($data['wallet_address'])) {
            return 2;
        }
        $this->storeObj->where('store_id', '=', $storeID)->update($updateData);
        return true;
    }

    public function UpdateBankDetails($data) {
        $updateData = array();
        $userId = Session::get('userData')['user_id'];
        $storeData = getStoreDataByUserId($userId); 
        $storeID =  !empty($storeData->store_id) ? $storeData->store_id :'';
        // echo 'Data: <pre>' .print_r($storeData,true). '</pre>'; exit;
        if (isset($data['wallet_address'])) {
            $updateData['wallet_address'] = $data['wallet_address'];
            $this->storeObj->where('store_id', '=', $storeID)->update($updateData);
            return 1;
        }
      
      if (isset($data['wallet_address']) && $data['wallet_address'] != "") {
            $users = $this->storeObj->where('store_id', '=', $storeID)
                    ->where('wallet_address', '=', $data['wallet_address'])
                    ->count();
            if ($users <= 0) {
                return 0;
            }
        } else if (empty($data['wallet_address'])) {
            return 2;
        }
        $this->storeObj->where('store_id', '=', $storeID)->update($updateData);
        return true;
    }

    public function checkEmail($email) {
        $userId = Session::get('userData')['user_id'];
        $result = $this->_user->where('email', '=', $email)->where('user_id', '!=', $userId)->count();
        return $result;
    }
    public function checkEmailApi($email) {
        $userId = Auth::user()->user_id;
        $result = $this->_user->where('email', '=', $email)->where('user_id', '!=', $userId)->count();
        return $result;
    }

    public function getallOrders() {
        $userId = Session::get('userData')['user_id'];
        if (empty($userId)) {
            $userId = Request::segment(2);
        }
        $orders = DB::table('store_orders')
                ->where('store_orders.buyer_id', $userId)
                ->join('order_items', 'store_orders.order_id', '=', 'order_items.order_id')
                ->leftJoin('shipping_addresses', 'store_orders.shipping_address', '=', 'shipping_addresses.sh_id')
                ->leftJoin('reviews', function ($join) {
                    $userId = Session::get('userData')['user_id'];
                    if (empty($userId)) {
                        $userId = Request::segment(2);
                    }
                    $join->on('reviews.product_id', '=', 'order_items.item_id');
                    $join->where('reviews.user_id', '=', $userId);
                })
                ->orderBy('store_orders.created_at', 'DESC')
                ->select('store_orders.order_id','store_orders.store_id', 'store_orders.tracking_id','order_items.variation_id', 'store_orders.payment_status',
                 'store_orders.buyer_id', 'store_orders.order_status', 'store_orders.shipping_address', 'store_orders.payment_method', 'store_orders.total_amount',
                 'order_items.shipping_amount', 'order_items.total as order_total', 'store_orders.created_at', 'order_items.item_id', 'order_items.quantity', 'order_items.price', 'reviews.comment',
                  'reviews.rating', 'shipping_addresses.street', 'shipping_addresses.country', 'shipping_addresses.state')
                ->get()
                ->toArray();
              //  echo '$orders: <pre>' .print_r($orders,true). '</pre>';
        if (count($orders) > 0) {
            $order_items = array();
            $product_variation = array();
            foreach ($orders as $order) {
                $order_items[$order->order_id][]= $order; 
            }
            return $order_items;
        }
    }

    public function getallOrdersNew() {
        $userId = Session::get('userData')['user_id'];
        if (empty($userId)) {
            $userId = Request::segment(2);
        }
              $orders = StoreOrder::where('buyer_id', $userId)->where('charge_status','paid')->orderBy('created_at', 'DESC')->paginate(15);
        if (count($orders) > 0) {
            return $orders;
            }else{
                return false;
            }
    }
    public function getallOrdersNewAPI() {
        
        $userId = Auth::user()->user_id;
        if (empty($userId)) {
            $userId = Request::segment(2);
        }
              $orders = StoreOrder::where('buyer_id', $userId)->where('charge_status','paid')->orderBy('created_at', 'DESC')->get();
        if (count($orders) > 0) {
            return $orders;
            }else{
                return false;
            }
    }

    public function getOrderDetailsNew($order_id) {
        $userId = Session::get('userData')['user_id'];
        if (empty($userId)) {
            $userId = Request::segment(2);
        }
        $orders = DB::table('store_orders')
                ->where('store_orders.order_id', $order_id)
                ->where('charge_status','paid')
                ->join('order_items', 'store_orders.order_id', '=', 'order_items.order_id')
                ->leftJoin('shipping_addresses', 'store_orders.shipping_address', '=', 'shipping_addresses.sh_id')
                ->leftJoin('reviews', function ($join) use ($order_id) {
                    $userId = Session::get('userData')['user_id'];
                    if (empty($userId)) {
                        $userId = Request::segment(2);
                    }
                    $join->on('reviews.product_id', '=', 'order_items.item_id');
                    $join->where('reviews.user_id', '=', $userId);
                    $join->where('reviews.order_id', '=', $order_id);
                })
                ->leftJoin('product_variation', 'order_items.variation_id', '=', 'product_variation.variation_id')
                ->orderBy('store_orders.created_at', 'DESC')
                ->select('store_orders.order_id','store_orders.buyer_id','store_orders.store_id', 'store_orders.tracking_id','order_items.variation_id', 'store_orders.payment_status',
                 'store_orders.buyer_id', 'store_orders.order_status', 'store_orders.shipping_address', 'store_orders.payment_method', 'store_orders.total_amount',
                 'order_items.shipping_amount', 'order_items.total as order_total', 'store_orders.created_at', 'order_items.item_id', 'order_items.quantity', 'order_items.price', 'reviews.comment','order_items.sale_id','order_items.sold_price',
                  'reviews.rating', 'shipping_addresses.street','shipping_addresses.post_code', 'shipping_addresses.city','shipping_addresses.country', 'shipping_addresses.state', 'product_variation.price as variation_price',
                  'order_items.product_name', 'order_items.item_image', 'order_items.variation_info')
                ->get()
                ->toArray();
        if (count($orders) > 0) {
            $order_items = array();
            $product_variation = array();
            foreach ($orders as $order) {
                $order_items[$order->order_id][]= $order; 
            }
            return $order_items;
        }else{
            return false;
        }
    }

    public function getallOrdersApi() {
        $userId = Auth::user()->user_id;
        $orders = DB::table('store_orders')
                ->where('store_orders.buyer_id', $userId)
                ->join('order_items', 'store_orders.order_id', '=', 'order_items.order_id')
                ->leftJoin('shipping_addresses', 'store_orders.shipping_address', '=', 'shipping_addresses.sh_id')
                ->leftJoin('reviews', function ($join) {
                    $userId = Auth::user()->user_id;
                    $join->on('reviews.product_id', '=', 'order_items.item_id');
                    $join->where('reviews.user_id', '=', $userId);
                })
                ->orderBy('store_orders.created_at', 'DESC')
                ->select('store_orders.order_id','store_orders.store_id', 'store_orders.tracking_id','order_items.variation_id', 'store_orders.payment_status',
                 'store_orders.buyer_id', 'store_orders.order_status', 'store_orders.shipping_address', 'store_orders.payment_method', 'store_orders.total_amount',
                 'order_items.shipping_amount', 'order_items.total as order_total', 'store_orders.created_at', 'order_items.item_id', 'order_items.quantity', 'order_items.price', 'reviews.comment',
                  'reviews.rating', 'shipping_addresses.street', 'shipping_addresses.country', 'shipping_addresses.state')
                ->get()
                ->toArray();
              //  echo '$orders: <pre>' .print_r($orders,true). '</pre>';
        if (count($orders) > 0) {
            $order_items = array();
            $product_variation = array();
            foreach ($orders as $order) {
                $order_items[$order->order_id][]= $order;
            }
            return $order_items;
        }
    }

    public function getallOrdersStatus($status) {
        $userId = Session::get('userData')['user_id'];
        $orders = DB::table('store_orders')
                ->where('store_orders.buyer_id', $userId)
                ->where('store_orders.order_status', '=', $status)
                ->join('order_items', 'store_orders.order_id', '=', 'order_items.order_id')
                ->leftJoin('shipping_addresses', 'store_orders.shipping_address', '=', 'shipping_addresses.sh_id')
                ->leftJoin('reviews', function ($join) {
                    $join->on('reviews.product_id', '=', 'order_items.item_id');
                    $join->where('reviews.user_id', '=', getCurrentUserId());
                })
                ->orderBy('store_orders.created_at', 'DESC')
                //->get()
                ->select('store_orders.order_id','store_orders.store_id', 'order_items.variation_id', 'store_orders.tracking_id', 'store_orders.payment_status', 'store_orders.buyer_id', 'store_orders.order_status', 'store_orders.shipping_address', 'store_orders.payment_method', 'store_orders.total_amount', 'store_orders.created_at', 'order_items.item_id','order_items.shipping_amount', 'order_items.quantity', 'order_items.price', 'reviews.comment', 'reviews.rating', 'shipping_addresses.street', 'shipping_addresses.country', 'shipping_addresses.state')
                ->get()
                ->toArray();
        if (count($orders) > 0) {
            $order_items = array();
            foreach ($orders as $order) {
                $order_items[$order->order_id][] = $order;
            }
            return $order_items;
        }
    }
    public function getallOrdersStatusApi($status) {
        $userId = Auth::user()->user_id;
        $orders = DB::table('store_orders')
                ->where('store_orders.buyer_id', $userId)
                ->where('store_orders.order_status', '=', $status)
                ->join('order_items', 'store_orders.order_id', '=', 'order_items.order_id')
                ->leftJoin('shipping_addresses', 'store_orders.shipping_address', '=', 'shipping_addresses.sh_id')
                ->leftJoin('reviews', function ($join) {
                    $userId = Auth::user()->user_id;
                    $join->on('reviews.product_id', '=', 'order_items.item_id');
                    $join->where('reviews.user_id', '=', $userId);
                })
                ->orderBy('store_orders.created_at', 'DESC')
                //->get()
                ->select('store_orders.order_id','store_orders.store_id', 'order_items.variation_id', 'store_orders.tracking_id', 'store_orders.payment_status', 'store_orders.buyer_id', 'store_orders.order_status', 'store_orders.shipping_address', 'store_orders.payment_method', 'store_orders.total_amount', 'store_orders.created_at', 'order_items.item_id','order_items.shipping_amount', 'order_items.quantity', 'order_items.price', 'reviews.comment', 'reviews.rating', 'shipping_addresses.street', 'shipping_addresses.country', 'shipping_addresses.state')
                ->get()
                ->toArray();
        if (count($orders) > 0) {
            $order_items = array();
            foreach ($orders as $order) {
                $order_items[$order->order_id][] = $order;
            }
            return $order_items;
        }
    }

    public function updateAccount($data) {
        $updateData = array();
            $userId = Session::get('userData')['user_id'];
        $updateData['name'] = isset($data['user_name']) ? $data['user_name'] : null;
        $updateData['email'] = isset($data['email']) ? $data['email'] : null;
        $updateData['user_country_id'] = isset($data['user_country']) ? $data['user_country'] : null;
        $updateData['user_state_id'] = isset($data['user_state']) ? $data['user_state'] : null;
        $updateData['user_city_id'] = isset($data['user_city_id']) ? $data['user_city_id'] : null;
        $updateData['user_zip_code'] = isset($data['user_zip_code']) ? $data['user_zip_code'] : null;
        $updateData['contact_no'] = isset($data['contact_no']) ? $data['contact_no'] : null;
        $updateData['physical_address'] = isset($data['physical_address']) ? $data['physical_address'] : null;
        $updateData['about'] = isset($data['about']) ? $data['about'] : null;

        // $updateData['user_address'] = $data['user_address'];
        if (isset($data['avatar_image_hidden']) && $data['avatar_image_hidden'] != "") {
            $updateData['user_avatar'] = $data['avatar_image_hidden'];
        }
        
        $res = $this->_user->where('user_id', '=', $userId)->update($updateData);
        if (is_null($res)) {
            return false;
        } else {
            return true;
        }
    }

    public function updateUserProfileApi($data,$imageArr) {
        try{
            $updateData = array();
            $user = Auth::user();
            if(isset($imageArr) && !empty($imageArr)){
                $avatar = $user->user_avatar;
                if(isset($avatar) && !is_null($avatar)){
                    $attachment = Attachments::find($avatar);
                    if(!is_null($attachment->fileName)){
                        $destinationPath = public_path() . '/uploads/avatar/' . $attachment->fileName;
                        if (File::exists($destinationPath)) {
                            // dd($destinationPath);
                            unlink($destinationPath);
                        }
                        $attachment->delete();
                    }
                }
            }
            $updateData['name'] = isset($data['full_name']) ? $data['full_name'] : $user->name;
            $updateData['contact_no'] = isset($data['contact_no']) ? $data['contact_no'] : '';
            $updateData['email'] = isset($data['email']) ? $data['email'] : $user->email;
            $updateData['user_avatar'] = isset($imageArr['id']) ? $imageArr['id'] : $user->user_avatar;
            $res = $this->_user->where('user_id', '=', $user->user_id)->update($updateData);
                return true;
        }catch(Exception $e){
            return false;
        }
        
    }

    public function getcurrentShippingAddress($data = ''){
        $shipData = array();
        if(isset($data['return_source']) && $data['return_source'] == 'true'){
            $userId = Auth::user()->user_id;
        }else{
            $userId = Session::get('userData')['user_id'];
        }
        $result = $this->shipping_obj->where('user_id', '=', $userId)->first();
       if (!empty($result)) {
        $shipData = $result->toArray();
        $shipData['shipping_state'] = getStatebyId($shipData['state']);
        $shipData['shipping_country'] = getCountryById($shipData['country']);
        return $shipData;
       }
    }
 

    public function getShippingAddress() {
        $userData = array();
        $userId = Session::get('userData')['user_id'];
        $result = $this->shipping_obj->where('user_id', '=', $userId)->first();
        if (!empty($result)) {
            $userData = $result->toArray();
            $userData['shipping_state'] = getStatesOfCountry($userData['country']);
            $userData['shipping_country'] = getCountryById($userData['country']);
           
            return $userData;
        }
    }
    public function getShippingAddressApi() {
        $userData = array();
        $userId = Auth::user()->user_id;
        $result = $this->shipping_obj->where('user_id', '=', $userId)->first();
        if (!empty($result)) {
            $userData = $result->toArray();
            $userData['shipping_state'] = getStatesOfCountry($userData['country']);
            $userData['shipping_country'] = getCountryById($userData['country']);
           
            return $userData;
        }
    }

    public function updateShippingAddress($data) {
        if (isset($data['shipping_key']) && $data['shipping_key'] != "") {
            $shippingAddressId = Crypt::decryptString($data['shipping_key']);
            $updateData = array();
            $updateData['full_name'] = $data['full_name'];
            $updateData['country'] = $data['user_country'];
            $updateData['state'] = $data['user_state'];
            $updateData['street'] = $data['street'];
            $updateData['post_code'] = $data['post_code'];
            $updateData['city'] = $data['city'];
            $updateData['flat_other'] = $data['flat_other'];
            $this->shipping_obj->where('sh_id', '=', $shippingAddressId)->update($updateData);
        } else {
            $userId = Session::get('userData')['user_id'];
            $this->shipping_obj->full_name = $data['full_name'];
            $this->shipping_obj->user_id = $userId;
            $this->shipping_obj->country = $data['user_country'];
            $this->shipping_obj->state = $data['user_state'];
            $this->shipping_obj->street = $data['street'];
            $this->shipping_obj->post_code = $data['post_code'];
            $this->shipping_obj->flat_other = $data['flat_other'];
            $this->shipping_obj->city = $data['city'];
            $this->shipping_obj->save();
        }
    }

    public function updateShippingAddressApi($data) {
        try{
            $userId = isset($data['user_id']) && !empty($data['user_id'])? $data['user_id'] : null;
          
            $updateData = array();
            
            $updateData['user_country_id'] =  isset($data['user_country']) ? $data['user_country'] : $user->user_country;
            $updateData['user_state_id'] = isset($data['user_state']) ? $data['user_state'] : $user->user_state_id;
            $updateData['physical_address'] = isset($data['street']) ? $data['street'] : $user->physical_address;
            $updateData['user_zip_code'] = isset($data['zip_code']) ? $data['zip_code']: $user->user_zip_code;
            $updateData['user_city_id'] = isset($data['city']) ? $data['city'] : $user->user_city_id;
            $user = auth()->user();
            
            if($user){

                $user = $this->_user->where('user_id','=',$user->user_id)->update($updateData);
            }else if(isset($userId)){
                $user = $this->_user->where('user_id','=',$userId)->update($updateData);
                
            }
            
            return true;
        }catch(Exception $e){
            return false;
        }
            

        
    }

    public function getCountFavorites($data = '') {
        if(isset($data['return_source']) && $data['return_source'] == 'true'){
            $userId = Auth::user()->user_id;
        }else{
            $userId = Session::get('userData')['user_id'];
        }
        $res = $this->fav_obj->where('user_id', $userId)->count();
        if (!empty($res)) {
            return $res;
        } else {
            return 0;
        }
    }

    public function changeAlerts($data) {
        $userId = Session::get('userData')['user_id'];
        $status = $data['alert_status'];
        $updateStatus = 0;
        $msg = "Email notification has been deactivated";
        $smsMsg = "Sms notification has been deactivated";
        if ($status == "true") {
            $updateStatus = 1;
            $msg = "Email notification has been activated";
            $smsMsg = "Sms notification has been activated";
        }
        if ($data['type'] == 'email_alerts') {
            DB::table('users')->where('user_id', $userId)->update(['email_alerts' => $updateStatus]);
            echo $msg;
        } else if ($data['type'] == 'sms_alerts') {
            DB::table('users')->where('user_id', $userId)->update(['sms_alerts' => $updateStatus]);
            echo $smsMsg;
        }
    }

    public function changeAlertsApi($data) {
        $userId = Auth::user()->user_id;
        $status = $data['alert_status'];
        $updateStatus = 0;
        $msg = "Email notification has been deactivated";
        $smsMsg = "Sms notification has been deactivated";
        if ($status == "true") {
            $updateStatus = 1;
            $msg = "Email notification has been activated";
            $smsMsg = "Sms notification has been activated";
        }
        if ($data['type'] == 'email_alerts') {
            DB::table('users')->where('user_id', $userId)->update(['email_alerts' => $updateStatus]);
            return $msg;
        } else if ($data['type'] == 'sms_alerts') {
            DB::table('users')->where('user_id', $userId)->update(['sms_alerts' => $updateStatus]);
            return $smsMsg;
        }
    }

    public function updateIntrest($data) {
        if (isset($data['categories'])) {
            if (isset($data['return_source']) &&   $data['return_source'] == 'true') {
                $userId = Auth::user()->user_id;
            }else{
                $userId = Session::get('userData')['user_id'];
            }
            $deleteResult = DB::table('user_intrests')->where('user_id', $userId)->delete();
            $selectedCategories = $data['categories'];
            foreach ($selectedCategories as $sc) {
                $intestuser = new UserIntrests();
                $intestuser->user_id = $userId;
                $intestuser->category_id = $sc;
                $intestuser->save();
            }
           return true; 
          
        }
    }

}
