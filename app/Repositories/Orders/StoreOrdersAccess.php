<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Repositories\Orders;

use App;
use Request;
use Session;
use Exception;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use App\Constants;
use App\BaseAccess;
use App\Models\Cart;
use App\Models\User;
use Mailgun\Mailgun;
//use App\models\ShAddress;
use App\Models\Product;
use App\Models\Userrole;
use App\Models\OrderItems;
use App\Models\StoreOrder;
use App\Models\StoreEscrow;
use App\Services\FCMService;
use App\Models\DiscountUsage;
use App\Repositories\Formulas;
use Illuminate\Support\Carbon;
use App\Models\ShippingAddress;
use App\Services\DiscountsService;
use App\Services\SellerFcmService;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\MandrillT;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use function PHPUnit\Framework\isNull;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Redirect;
use App\Http\Controllers\PoolTController;
use App\Http\Controllers\StripeController;
use App\Http\Controllers\MailgunController;
use App\Http\Controllers\EasypostController;
use App\Models\{ProductVariation,DeviceToken};
use App\Repositories\Transactions\PoolTAccess;

use App\Repositories\Discounts\Discountsaccess;
use App\Repositories\Transactions\WalletAccess;
use App\Http\Controllers\ElasticSearchController;

/**
 * Description of StoreAccess
 *
 * @author Asif
 */
class StoreOrdersAccess extends BaseAccess
{

    private $store_order;
    private $order_items;
    private $shipping_address;
    private $mailgun;
    private $stripeObj;
    private $mandrill;
    private $walletTransactions;
    private $_esearch;
    private $storeEscrew;
    private $_userRole;
    private $_user;
    private $_cart;
    protected $discountsService;

    public function __construct()
    {
        $this->shipping_address = new ShippingAddress();
        $this->store_order = new StoreOrder;
        $this->order_items = new OrderItems;
        $this->stripeObj = new StripeController();
        $this->mandrill = new MandrillT();
        $this->PoolTAccess = new PoolTAccess();
        $this->walletTransactions = new WalletAccess();
        $this->mailgun = new MailgunController();
        $this->_esearch = new ElasticSearchController();
        $this->storeEscrew = new StoreEscrow();
        $this->_user = new User();
        $this->_userRole = new Userrole();
        $this->_cart = new Cart();
        $discountsAccess = new Discountsaccess();
        $this->discountsService = new DiscountsService($discountsAccess);
    }

    private function current_user_can_update_order($order_id)
    {
        $val = false;
        if ($order_id != '') {
            $current_user_id = getCurrentUserId();
            $store_id = DB::table('store_orders')
                ->where('order_id', '=', $order_id)
                ->select('store_id', 'buyer_id')
                ->get()
                ->toArray();


            if (count($store_id) > 0) {
                $store_idd = $store_id[0]->store_id;
                if ($store_idd != '') {
                    $user = DB::table('stores')
                        ->where('store_id', '=', $store_idd)
                        ->select('user_id')
                        ->get()
                        ->toArray();

                    if (count($user) > 0) {
                        $user_id = $user[0]->user_id;
                        $buyer_id = $store_id[0]->buyer_id;
                        if ($user_id == $current_user_id) {
                            $val = true;
                        }
                        if ($buyer_id == $current_user_id) {
                            $val = true;
                        }
                    }
                }
            }
        }
        return $val;
    }
    private function current_user_can_update_order_api($order_id)
    {
        $val = false;
        if ($order_id != '') {
            $current_user_id = Auth::user()->user_id;

            $store_id = DB::table('store_orders')
                ->where('order_id', '=', $order_id)
                ->select('store_id', 'buyer_id')
                ->get()
                ->toArray();

            if (count($store_id) > 0) {
                $store_idd = $store_id[0]->store_id;
                if ($store_idd != '') {
                    $user = DB::table('stores')
                        ->where('store_id', '=', $store_idd)
                        ->select('user_id')
                        ->get()
                        ->toArray();

                    if (count($user) > 0) {
                        $user_id = $user[0]->user_id;
                        $buyer_id = $store_id[0]->buyer_id;
                        if ($user_id == $current_user_id) {
                            $val = true;
                        }
                        if ($buyer_id == $current_user_id) {
                            $val = true;
                        }
                    }
                }
            }
        }
        return $val;
    }

    public function create_order($order_info = array())
    {
        DB::beginTransaction();
        try{

        
          
        $client = new \EasyPost\EasyPostClient(env('EASYPOST_API_KEY'));
        $sess = array();
        $insertdData = array();
        $shipping_address_id = '';
        $get_user_id = '';
        $item_price = '';
        $sale_price = '';

                $user_email = isset($order_info['useremail']) ? $order_info['useremail'] : "";

                $user = User::where('email', $user_email)->first();

                $login_key = str_random(25);
                // check if user doesn't exists create new one
                if ($user) {
                    $get_user_id = $user->user_id;
                    $this->shipping_address->user_id = $user->user_id;

                    $this->shipping_address->full_name = $order_info['full_name'];
                    $this->shipping_address->country = $order_info['s_country'];
                    $this->shipping_address->state = isset($order_info['states_countries']) ? $order_info['states_countries'] : "46";
                    $this->shipping_address->city = $order_info['city'];
                    $this->shipping_address->street = $order_info['s_street'];
                    $this->shipping_address->post_code = $order_info['s_post_code'];

                    $this->shipping_address->save();
                    $shipping_address_id = $this->shipping_address->sh_id;
                } else {
                    // User does not exist, insert new record
                    $this->_user->name = $order_info['full_name'];
                    $this->_user->email = $user_email;
                    $this->_user->password = md5('mallshark123456');
                    $now = Carbon::now();
                    $this->_user->forget_key_date = $now;
                    $this->_user->forget_key = $login_key;
                    $this->_user->register_source = 'MallShark';

                    $this->_user->contact_no = isset($order_info['phone_number']) ? $order_info['phone_number'] : "";

                    $this->_user->save();

                    $lastInsertid = $this->_user->user_id;
                    $get_user_id = $this->_user->user_id;

                    $this->shipping_address->full_name = $order_info['full_name'];
                    $this->shipping_address->country = $order_info['s_country'];
                    $this->shipping_address->state = $order_info['states_countries'];
                    $this->shipping_address->street = $order_info['s_street'];
                    $this->shipping_address->post_code = $order_info['s_post_code'];
                    $this->shipping_address->city = $order_info['city'];
                    $this->shipping_address->user_id = $lastInsertid;
                    $this->shipping_address->save();
                    $shipping_address_id = $this->shipping_address->sh_id;
                    $this->_userRole->user_id = $lastInsertid;
                    $this->_userRole->role_id = 4;
                    $this->_userRole->save();
                    $url = URL::to('/change-password/'.$login_key);
                    $html = view('mail.activateUser_checkout', ['activate_url' => $url]);
                    $subject = 'Welcome to MallShark - Get Ready for an Unforgettable Shopping Experience!';

                    $toEmail = $order_info['useremail'];
                    $toCompletename = $order_info['full_name'];
                    $params = [
                        'from'    => 'MallShark  ' . env('SUPPORT_EMAIL'),
                        'to'      => $toEmail,
                        'subject' => $subject,
                        'html'    => "$html",
                    ];
                    $results = $this->mailgun->SendMailgunMail($params);
                    $this->_esearch->userMapping($lastInsertid);
                }
                //  _____________________USER CHECK ENDS________________

                $counter_g = 0;

                $shippingPriceArray = array();
                $productPriceArray = array();
                foreach ($order_info['orderData'] as $key1 => $val) {
                    $totalProductsQuantity = 0;
                    $coupon_code = '';
                    $couponKey = array_key_last($val); 
                    if (isset($val[$couponKey]['coupon_code'])) {
                        $coupon_code = $val[$couponKey]['coupon_code'];
                        array_pop($val); 
                    }
                    $totalOrderPrice = 0;
                    foreach ($val as $pval) {
                        $totalOrderShimpentPrice = 0;
                        if ($key1 == $pval['store_id']) {
                            if ($pval['product_type'] == 'variant') {
                                $vaiablePrice =  $pval['on_sale'] == 1 ? $pval['product_variation_sale_price'] : $pval['variation_sale_price'];

                                if (is_numeric($vaiablePrice) && is_numeric($pval['product_quantity'])) {
                                    $totalOrderPrice = $totalOrderPrice + ($vaiablePrice * $pval['product_quantity']);
                                }
                            } else if ($pval['product_type'] == 'simple') {

                                $simplePrice = $pval['on_sale'] == 1 ? $pval['sale_price'] : $pval['price'];
                                $quantity = $pval['product_quantity'];
                                if (!is_numeric($quantity)) {
                                    $quantity = 1;
                                }
                                $totalOrderPrice = $totalOrderPrice + ($simplePrice * $quantity);

                            } else if ($pval['product_type'] == 'virtual') {
                                $virtualPrice = $pval['price'];
                                $totalOrderPrice = $totalOrderPrice +  $virtualPrice;
                            }
                            $shipping_price = isset($pval['shipping_price']) ? $pval['shipping_price'] : 0;
                            $totalOrderShimpentPrice = $totalOrderShimpentPrice + $shipping_price;

                    $str[$key1]['ship_price'] = $totalOrderShimpentPrice;
                    if (isset($shippingPriceArray[$key1])) {
                        $shippingPriceArray[$key1] = $totalOrderShimpentPrice + $shippingPriceArray[$key1];
                    } else {
                        $shippingPriceArray[$key1] = $totalOrderShimpentPrice;
                    }

                    // if (isset($productPriceArray[$key1])) {
                    //     $productPriceArray[$key1] = $totalOrderPrice + $productPriceArray[$key1];
                    // } else {
                    //     $productPriceArray[$key1] = $totalOrderPrice;
                    // }
                }
                $totalProductsQuantity += $pval['product_quantity'];
            }
                // if (isset($val[0]['coupon_code']) && !empty($val[0]['coupon_code'])) {
                if (isset($coupon_code) && !empty($coupon_code)) {
                    $result = $this->discountsService->applyCoupon($coupon_code,$key1,$totalOrderPrice, $totalProductsQuantity);
                    $totalOrderPrice = $result['cartTotal'];
                }
            
                // Now, update the productPriceArray with the store's total (after coupon, if any)
                if (isset($productPriceArray[$key1])) {
                    $productPriceArray[$key1] = $totalOrderPrice + $productPriceArray[$key1];
                } else {
                    $productPriceArray[$key1] = $totalOrderPrice;
                }
        }
        $orderIds = [];
        foreach ($order_info['orderData'] as $key => $val) {
            try{
                DB::beginTransaction();
            $chargeInfo = $order_info['charges'][$key];
            $platform_fee = $chargeInfo['platform_fee'];
            $charge_status = $chargeInfo['charge_status'] == 'paid' ? 'paid':'unpaid';
            $payable_amount = $chargeInfo['payable_amount'];
            $paymentID = $chargeInfo['paymentId'];
            $balanceTransaction = $chargeInfo['balance_transaction'];
            $payerID = $chargeInfo['payerID'];
            $payment_status = 'pending';
            $default_order_status = 'pending';
            $justVirtualProducts = true; // using this to check if order as all virtual products then set order status directly
            // if($val['product_type'] == 'virtual'){
            //     $default_order_status = 'completed';
            //     // $payment_status = 'completed';
            // }
            if ($key != 'products_price' && $key != 'shipping_price' && $key != 'total_price' && $key != 'total_tax') {
                $this->store_order = new StoreOrder;
                $this->discount_usage = new DiscountUsage;

                            $store_id = $key;
                            $storeInfo = getStoreStatusById((int)$store_id);

                            $google_analytics_arr[$counter_g] = array('transaction_id' => rand(258, 9854), 'affiliation' => 'Google online store', 'value' => "", 'currency' => 'USDC', 'tax' => 0, 'shipping' => $shippingPriceArray[$key]);
                            $val['total_amount'] = 350;
                            $val['shiping_amount'] = 30;
                            $this->store_order->buyer_id = $get_user_id;
                            if ($paymentID == "" &&  $order_info['balance_transaction'] == "") {
                                $this->store_order->payment_method = 'crypto';
                                $this->store_order->buyer_address = $order_info['buyer_address'];
                            } else {
                                $this->store_order->payment_method = 'stripe';
                            }
                            $this->store_order->payment_status = $payment_status;
                            $this->store_order->charge_status = $charge_status;
                            $this->store_order->platform_fee = $platform_fee;
                            $this->store_order->payable_amount = $payable_amount;
                            $this->store_order->order_status = $default_order_status;
                            $this->store_order->total_amount = $productPriceArray[$key] + $shippingPriceArray[$key];

                            $this->store_order->shipping_address = $shipping_address_id;
                            $this->store_order->shipping_method = !empty($val['shipping_method']) ? $val['shipping_method'] : '';
                            $this->store_order->total_items = $val[0]['product_quantity'];
                            $this->store_order->transaction_id = $paymentID;
                            $this->store_order->balance_transaction = $balanceTransaction;
                            $this->store_order->payerID = $payerID;
                            $this->store_order->shipping_amount = $shippingPriceArray[$key];

                            $this->store_order->store_id = $store_id;

                            $order_id = $this->store_order->save();
                            $store_order_id = $this->store_order->order_id;
                            $orderIds[] = $store_order_id;
                            $order_info['orderData'][$key]['store-order-id'] = $store_order_id;
                            $onSaleProductFound = false;
                            $usedCouponCode = '';

                            if (isset($coupon_code) && !empty($coupon_code)) {
                                $usedCouponCode = $coupon_code; 
                            // if (isset($val['coupon_code']) && !empty($val['coupon_code'])) {
                            //     $usedCouponCode = $val['coupon_code']; 
                                // break; 
                            }else{
                            foreach ($val as $val1) {
                                if ($onSaleProductFound == false) {
                                    if (isset($val1['on_sale']) && $val1['on_sale'] == 1 && $val1['store_id'] == $store_id) {
                                        $onSaleProductFound = true;
                                        $sale_id = $val1['sale_id'];
                                        break;
                                    }
                                }
                            }
                        }

                        if (isset($usedCouponCode) && !empty($usedCouponCode)) {
                            // Track the coupon usage count
                            $coupon = App\Models\Discount::where('code', $usedCouponCode)
                                                        ->where('store_id', $store_id) 
                                                        ->first();
                            
                            if ($coupon) {
                                $discount_id = $coupon->id; 
                                $coupon->usage_count += 1; 
                                $coupon->save();
                                
                                $this->discount_usage->discount_id = $discount_id; 
                                $this->discount_usage->user_id = $get_user_id; 
                                $this->discount_usage->order_id = $store_order_id;
                                $this->discount_usage->store_id = $store_id;
                                $this->discount_usage->used_at = Carbon::now();
                                $this->discount_usage->save();
                            }
                        }else{
                        

                            if (isset($onSaleProductFound) && $onSaleProductFound == true) {
                                $this->discount_usage->discount_id = $sale_id;
                                $this->discount_usage->user_id = $get_user_id; // Make sure to set discount_id in order_info
                                $this->discount_usage->order_id = $store_order_id;
                                $this->discount_usage->store_id = $store_id;
                                $this->discount_usage->used_at = Carbon::now();
                                $this->discount_usage->save();

                                $discount = App\Models\Discount::where('id', $sale_id)->first();
                                $discount->usage_count += 1;
                                $discount->save();
                            }
                        }


                            $lastInsertedId = $this->store_order->order_id;
                            $items_Array = array();
                            $sipping_amount = 0;
                            if ($order_id) {
                                $store_data = getStoreInfoById((int)$store_id);
                                $currentDate = date('Y-m-d');
                                // $paymentDate = $currentDate;
                                $paymentDate = date('Y-m-d', strtotime($currentDate . ' + 10 days'));
                                $amountWithOutShip = $productPriceArray[$key] - $shippingPriceArray[$key];
                                $paymentProfit = $amountWithOutShip * 0.045;
                                $payableAmount = $productPriceArray[$key] - $paymentProfit;
                                $storeWallet = !empty($store_data[0]->wallet_address) ? $store_data[0]->wallet_address : '';

                                if ($paymentID == "" &&  $balanceTransaction == "") {
                                    $storeEscrew = new StoreEscrow();
                                    $storeEscrew->order_id = $lastInsertedId;
                                    $storeEscrew->store_id = $store_id;
                                    $storeEscrew->store_wallet_address = $storeWallet;
                                    $storeEscrew->order_price = $productPriceArray[$key];
                                    $storeEscrew->ship_price =  $shippingPriceArray[$key];
                                    $storeEscrew->order_price_with_out_shipping =  $amountWithOutShip;
                                    $storeEscrew->payable_amount =  $payableAmount;
                                    $storeEscrew->profit =  $paymentProfit;
                                    $storeEscrew->status =  'pending';
                                    $storeEscrew->profit_status =  'pending';
                                    $storeEscrew->payment_date =  $paymentDate;
                                    $storeEscrew->save();
                                }
                                if (count($val) > 0) {
                                    $total_amount = 0;
                                    foreach ($val as $key => $product) {
                                        if (isset($product['product_id']) && $product['product_id'] != '') {
                                            $quantity = $product['product_quantity'];
                                            if ($product['product_type'] == 'variant') {
                                                if (isset($product['on_sale']) && $product['on_sale'] == 1 ){
                                                    $sale_price = isset($product['product_variation_sale_price']) ? $product['product_variation_sale_price'] : $product['sale_price'];
                                                    $justVirtualProducts = false;
                                                }
                                                    $item_price = isset($product['variation_sale_price']) ? $product['variation_sale_price'] : $product['price'];
                                                    $justVirtualProducts = false;
                                                
                                            } else if ($product['product_type'] == 'simple') {
                                                $justVirtualProducts = false;
                                                if (isset($product['on_sale']) && $product['on_sale'] == 1 )
                                                {
                                                    $sale_price = $product['sale_price'];
                                                }
                                                $item_price = isset($product['price']) ? $product['price'] : $product['price'];
                                                
                                            } else if ($product['product_type'] == 'virtual') {
                                                if (isset($product['on_sale']) && $product['on_sale'] == 1 )
                                                {
                                                    $sale_price = $product['sale_price'];
                                                }
                                                    $item_price = isset($product['price']) ? $product['price'] : $product['price'];
                                                
                                            }

                                            if (!is_numeric($quantity)) {
                                                $quantity = 1;
                                            }
                                            if(!empty($sale_price)){
                                                $item_price_condition = is_numeric($sale_price) ? $sale_price : 0; // Check if $item_price is numeric, if not, set it to 0
                                                $product_order_price = floatval($quantity * $item_price_condition);
                                            }else{
                                                $item_price_condition = is_numeric($item_price) ? $item_price : 0; // Check if $item_price is numeric, if not, set it to 0
                                                $product_order_price = floatval($quantity * $item_price_condition);
                                            }
                                            

                                $shipping_method = isset($product['method']) ? $product['method'] : "";
                                $store_id_pr = $product['store_id'];
                                $store_p = getStoreInfoById((int) $store_id_pr);
                                $shipping_charges = isset($product['shipping_price']) ? $product['shipping_price'] : 0;
                                if ($shipping_charges == '') {
                                    $shipping_charges = 0;
                                }
                                $order_per_total = $shipping_charges + $product_order_price;
                                $product_variation_id = isset($product['variation_id']) ? $product['variation_id'] : "";
                                $shipping_price = isset($product['shipping_price']) ? $product['shipping_price'] : "";

                                // $getproduct = getProductById($product['product_id']);
                                // $p_name = $getproduct[0]->title ?? 'Not Found';
                                $p_name = $product['title'] ?? 'Not Found';
                                $variation_info = '';
                                        if ($product_variation_id != '') {
                                            $variation_info = getVariationNameById($product_variation_id);
                                        }

                                $image = isset($product['feature_image']) ? $product['feature_image'] : '';
                                        $image = (int) $image;
                                        $image_url = '';
                                        $newImagePath = '';
                                        
                                        // dd($image);
                                        if (!empty($image)) {
                                            if($product['product_type'] == 'simple' ){
                                                $image_url = getAttachmentUrlById((int) $image, 'small');
                                            }elseif($product['product_type'] == 'variant'){
                                                $image_url = getAttachmentUrlById((int) (isset($product['product_variation_image']) && $product['product_variation_image'] != "" ? $product['product_variation_image'] : $image) , 'medium');
                                                if(empty($image_url)){
                                                    $image_url = getAttachmentUrlById((int) $image, 'small');
                                                }
                                            }else{ 
                                                $image_url = getAttachmentUrlById((int) $image, 'small');
                                            }
                                        }
                                    if($image_url != ''){
                                        $newImagePath = copyImageFromUrlToNewFolder($image_url);
                                    }
                                if ($shipping_method == 'calculated' && isset($product['ship_id']) && $product['ship_id'] != '') {
                                    $shipment = $client->shipment->retrieve($product['ship_id']);

                                    $created_date = date("Y-m-d H:i:s");
                                    $items_Array[] = array(
                                        'order_id' => $lastInsertedId, 'store_id' => $store_id, 'item_id' => $product['product_id'],
                                        // 'sold_price' => $product['sale_price'],
                                       'sold_price' => (!empty($sale_price)) ? $sale_price : 0,
                                        'sale_id' => $product['sale_id'],
                                        'variation_id' => $product_variation_id, 'quantity' => $product['product_quantity'], 
                                        'price' => $item_price,
                                        //'price' => (!empty($product['variation_sale_price'])) ? $product['variation_sale_price'] : $product['price'],
                                        'shipping_amount' => $product['shipping_price'], 'order_note' => '', 'item_tax' => '',
                                        'method' => $shipping_method, 'total' => $order_per_total, 'shipment_id' => $product['ship_id'], 'payment_service' => $product['shipping_method'], 'shipment_id' => $shipment->id, 'rate_id' => '', 'label_url' => '', 'lable_pdf_url' => '', 'tracking_id' => '', 'tracking_code' => '', 'shipment_object' => '', 'created_at' => $created_date,
                                        'product_name' => $p_name, 'variation_info' => $variation_info, 'item_image' => $newImagePath
                                    );
                                } else {
                                    $created_date = date("Y-m-d H:i:s");
                                    $items_Array[] = array(
                                        'order_id' => $lastInsertedId, 'store_id' => $store_id, 'item_id' => $product['product_id'],
                                        // 'sold_price' => $product['sale_price'],
                                        
                                        'sold_price' => (!empty($sale_price)) ? $sale_price : 0,
                                        'sale_id' => $product['sale_id'],
                                        'variation_id' => $product_variation_id, 'quantity' => $product['product_quantity'], 
                                        'price' => $item_price,
                                        
                                        //'price' => (!empty($product['variation_sale_price'])) ? $product['variation_sale_price'] : $product['price'],
                                        'shipping_amount' => $shipping_price, 'order_note' => '', 'item_tax' => '',
                                        'shipment_id' => '', 'rate_id' => '', 'label_url' => '',
                                        'lable_pdf_url' => '', 'tracking_id' => '', 'tracking_code' => '', 'payment_service' => '', 'shipment_object' => '',
                                        'method' => $shipping_method, 'total' => $order_per_total, 'created_at' => $created_date,
                                        'product_name' => $p_name, 'variation_info' => $variation_info, 'item_image' => $newImagePath
                                    );
                                }
                                $storeInfo = getStoreStatusById((int)$store_id);
                                // echo '$storeInfo: <pre>' .print_r($storeInfo,true). '</pre>'; die();
                                if (isset($storeInfo->stripe) && $storeInfo->stripe == 1) {
                                    if (isset($_COOKIE['cartCookie'])) {
                                        unset($_COOKIE['cartCookie']);
                                        setcookie('cartCookie', null, -1, '/');
                                    }
                                    if (isset($_COOKIE['cartCookieCrypto'])) {
                                        unset($_COOKIE['cartCookieCrypto']);
                                        setcookie('cartCookieCrypto', null, -1, '/');
                                    }
                                }


                                            $remainStock = "";
                                            if (isset($product['variation_id']) && $product['variation_id'] != '') {
                                                variableUpdateCurrentStock($product['variation_id'], $product['product_id'], $product['product_quantity']);
                                                // change status of flash sale
                                                $productFlashsaleStatus = DB::table('products')->where('product_id', $product['product_id'])->value('flashsale_status');
                                                if ($productFlashsaleStatus == 'choosen') {
                                                    $productData = getvariableProduct($product['product_id']);
                                                    $remainStock = $productData['current_stock'];
                                                    if ($remainStock <= 0) {
                                                        DB::table('products')->where('product_id', $product['product_id'])->update(['flashsale_status' => 'standard']);
                                                    }
                                                }
                                            } else {
                                                simpleUpdateCurrentStock($product['product_id'], $product['product_quantity']);
                                                // change flash sale status
                                                $productquery = DB::table('products')->where('product_id', $product['product_id'])->select('flashsale_status', 'current_stock')->first();
                                                $remainStock = $productquery->current_stock;
                                                if ($productquery->flashsale_status == 'choosen' && $remainStock <= 0) {
                                                    DB::table('products')->where('product_id', $product['product_id'])->update(['flashsale_status' => 'standard']);
                                                }
                                            }
                                        }
                                    }

                                    if (count($items_Array) > 0) {
                                        $this->add_order_items($items_Array);
                                    }
                                }

                            }

                            $userData = getUserByIdchat($get_user_id);


                            $subject = 'Your Order is Submitted successfully';
                            $subjectStore = Constants::ORDER_CREATE_SUBJECT_SELLER.$lastInsertedId;
                            // $descriptionStore = Constants::ORDER_CREATE_DESCRIPTION_SELLER;
                            $subjectBuyer = Constants::ORDER_CREATE_SUBJECT_BUYER.$lastInsertedId;
                            // $descriptionBuyer = Constants::ORDER_CREATE_SUBJECT_BUYER;
                            $templateSlug = 'new-order';
                            $templateSlugStore = 'new-order-store';
                            $toEmail = $userData->email;

                            $toCompletename = $userData->name;
                            $store = getStoreInfoById((int) $store_id);

                            $user_id = isset($store[0]->user_id) ? $store[0]->user_id : '';
                            $userDataStore = getUserByIdchat((int) $user_id);

                            if ($userDataStore != '' && isset($userDataStore->email)) {
                                $toEmailStore = $userDataStore->email;
                            }
                            if ($userDataStore != '' && isset($userDataStore->name)) {
                                $store_name = $userDataStore->name;
                            }

                            
                            if(isset($get_user_id) && !is_null($get_user_id)){
                                $BuyerData = User::with('device_token')->findOrFail($get_user_id);
                                $device_tokens = DeviceToken::where('user_id',$BuyerData->user_id)->where('app_flag','buyer')->get();
                                if ($device_tokens) {
                                    $tokens = [];
                                    if(isset($device_tokens)&& count($device_tokens) > 0){
                                    foreach ($device_tokens as $device_token_obj) {
                                        
                                        // dd($device_token_obj->fcm_token);
                                    
                                    
                                    $subject = $subjectBuyer;
                                    // foreach($tokens as $token){
                                        $notification = FCMService::send(
                                            $device_token_obj->fcm_token,
                                            [
                                                'title' => $subject,
                                            ]
                                        );

                                        
                                    }
                                    }
                                    addNotifications($get_user_id,$user_id, $subjectBuyer, '',URL::to('new-buyer-orders-detail/'.$lastInsertedId),'buyer_order_details',$lastInsertedId,'buyer');

                                }
                            }
                            if(isset($user_id) && !is_null($user_id)){
                                $userData = User::with('device_token')->findOrFail($user_id);
                                $device_tokens = DeviceToken::where('user_id',$userData->user_id)->where('app_flag','seller')->get();
                                if ($device_tokens) {
                                    $tokens = [];
                                    if(isset($device_tokens)&& count($device_tokens) > 0){
                                    foreach ($device_tokens as $device_token_obj) {
                                        // dd($device_token_obj->fcm_token);
                                    
                                    
                                    $subject = $subjectStore;
                                    // foreach($tokens as $token){
                                        $notification = SellerFcmService::send(
                                            $device_token_obj->fcm_token,
                                            [
                                                'title' => $subject,
                                            ]
                                        );

                                    }
                                }
                                addNotifications($user_id, getCurrentUserId(), $subjectStore, '', URL::to('/brand-order-details/'.$lastInsertedId),'brand_order_details',$lastInsertedId,'seller');

                                }
                            }

                            $state_id = $order_info['states_countries'];
                            $country_id = $order_info['s_country'];
                            $state = getStatebyId($state_id);
                            $state_name = $state['state_name'];
                            $country = getCountryById($country_id);
                            $country_name = $country['code'];
                        

                            $html = view('mail.storecreateOrder', [
                                'email' => $order_info['useremail'], 'name' => $order_info['full_name'],
                                'city' => $order_info['city'], 'street' => $order_info['s_street'], 'postalcode' => $order_info['s_post_code'],
                                'state' => $state_name, 'country' => $country_name, 'storeName' => $store_name, 'order_id' => $store_order_id, 'productDetails' => $val, 'coupon_code' => $coupon_code
                            ]);
                            $subjectVendor = 'You’ve Received an Order! ';

                $params = [
                    'from'    => 'MallShark  ' . env('SUPPORT_EMAIL'),
                    'to'      => $toEmailStore,
                    'subject' => $subjectVendor,
                    'html'    => "$html",
                ];
                $results = $this->mailgun->SendMailgunMail($params);
                
            }
            if($order_id && $justVirtualProducts){
                $this->store_order->order_status = 'completed';
                $this->store_order->virtual = true;
                $this->store_order->save();
            }
            $counter_g++;
            DB::commit();
        }catch(Exception $e){
            DB::rollBack();
            $paymentLog = new Logger('');
            $paymentLog->pushHandler(new StreamHandler(storage_path('logs/orders-exception.log')), Logger::ERROR);
            $paymentLog->error('Failed', ['Order_id' => $lastInsertedId,'store_id' => '', 'order_date' => Carbon::now(), 'Message' => $e->getMessage()]);
        }
        }



        $state_id = $order_info['states_countries'];
        $country_id = $order_info['s_country'];
        $state = getStatebyId($state_id);
        $state_name = $state['state_name'];
        $country = getCountryById($country_id);
        $country_name = $country['code'];
        $html = view('mail.new-order-client-side', [
            'customerDetail' => $BuyerData,
            'productDetails' => $order_info['orderData'], 'email' => $order_info['useremail'], 'name' => $order_info['full_name'],
            'city' => $order_info['city'], 'street' => $order_info['s_street'], 'postalcode' => $order_info['s_post_code'],
            'state' => $state_name, 'country' => $country_name, 'order_id' => $store_order_id, 'orderIds' => $orderIds,
        ]);
        $subject = 'Order Placed! Order #'.implode(', ', $orderIds);
        $toEmail = $BuyerData->email;
        $bccEmails = ['sajidoon@mindblazetech.com','robocoder42@gmail.com','care@mallshark.com','fugatesklcc@yahoo.com','aatika@mallshark.com','chadfugate85@gmail.com','chad@mallshark.com'];
        $params = [
            'from'    => 'MallShark  ' . env('SUPPORT_EMAIL'),
            'to'      => $toEmail,
            'bcc'     => implode(',', $bccEmails),
            'subject' => $subject,
            'html'    => "$html",
        ];
        $results = $this->mailgun->SendMailgunMail($params);
        Session::put('google_analytics', $google_analytics_arr);
        if(!empty($_COOKIE['cartCookie'])){
            $randomKey = $_COOKIE['cartCookie'];
          
            DB::table('products_cart')->where('random_key', '=', $randomKey)->delete();
        }
        
        DB::table('products_cart')->where('user_id', '=', getCurrentUserId())->delete();
        unset($_COOKIE['cartCookie']);
        setcookie('cartCookie', null, -1, '/');

        // return 'order created succesfully and your order id is:' . $lastInsertedId;
 
    DB::commit();
    return true;
}catch(Exception $e){
    DB::rollBack();
    $paymentLog = new Logger('');
    $paymentLog->pushHandler(new StreamHandler(storage_path('logs/orders-exception.log')), Logger::ERROR);
    $paymentLog->error('Failed', ['Order_id' => $lastInsertedId,'store_id' => '', 'order_date' => Carbon::now(), 'Message' => $e->getMessage()]);
    return false;
}

    }
    public function create_orderApi($order_info = array())
    {
        try{

        
        $client = new \EasyPost\EasyPostClient(env('EASYPOST_API_KEY'));
        $sess = array();
        $insertedData = array();
        
        $order_ids = [];
        $shipping_address_id = '';
        $get_user_id = '';
        $item_price = '';


        $user_email = isset($order_info['useremail']) ? $order_info['useremail'] : "";

        $user = User::where('email', $user_email)->first();

        $login_key = str_random(25);
        // check if user doesn't exists create new one
        if ($user) {
            $get_user_id = $user->user_id;
            $this->shipping_address->user_id = $user->user_id;

            $this->shipping_address->full_name = $order_info['full_name'];
            $this->shipping_address->country = $order_info['country'];
            $this->shipping_address->state = isset($order_info['states']) ? $order_info['states'] : "46";
            $this->shipping_address->city = $order_info['city'];
            $this->shipping_address->street = $order_info['address'];
            $this->shipping_address->post_code = $order_info['post_code'];
            $this->shipping_address->save();
            $shipping_address_id = $this->shipping_address->sh_id;
        } else {
            // User does not exist, insert new record
            $this->_user->name = $order_info['full_name'];
            $this->_user->email = $user_email;
            $this->_user->password = md5('mallshark123456');
            $this->_user->forget_key = $login_key;
            $this->_user->register_source = 'MallShark';

            $this->_user->contact_no = isset($order_info['phone_number']) ? $order_info['phone_number'] : "";

            $this->_user->save();

            $lastInsertid = $this->_user->user_id;
            $get_user_id = $this->_user->user_id;



            $this->shipping_address->full_name = $order_info['full_name'];
            $this->shipping_address->country = $order_info['country'];
            $this->shipping_address->state = $order_info['states'];
            $this->shipping_address->street = $order_info['address'];
            $this->shipping_address->post_code = $order_info['post_code'];
            $this->shipping_address->city = $order_info['city'];
            $this->shipping_address->user_id = $lastInsertid;
            $this->shipping_address->save();
            $shipping_address_id = $this->shipping_address->sh_id;

            $this->_userRole->user_id = $lastInsertid;
            $this->_userRole->role_id = 4;
            $this->_userRole->save();
            $toCompletename = $order_info['full_name'];
            $this->_esearch->userMapping($lastInsertid);
        }
        //  _____________________USER CHECK ENDS________________

        $counter_g = 0;

        $shippingPriceArray = array();
        $productPriceArray = array();
        foreach ($order_info['orderData'] as $key1 => $val) {



            foreach ($val as $pval) {

                $totalOrderShimpentPrice = 0;
                $totalOrderPrice = 0;
                if ($key1 == $pval['store_id']) {

                    $product = Product::find($pval['product_id']);
                    if($product){
                        $product->sales += $pval['product_quantity'];
                        $product->save();
                        $product->searchable();
                    }
                    if ($pval['product_type'] == 'variant') {
                        if($pval['on_sale'] == 1){
                            $vaiablePrice = isset($pval['sale_price']) ? $pval['sale_price'] : $pval['price'];
                        }else{
                            $vaiablePrice = $pval['price'];
                        }
                        

                        if (is_numeric($vaiablePrice) && is_numeric($pval['product_quantity'])) {
                            $totalOrderPrice = $totalOrderPrice + ($vaiablePrice * $pval['product_quantity']);
                        }
                    } else if ($pval['product_type'] == 'simple') {
                        if($pval['on_sale'] == 1){
                            $simplePrice = isset($pval['sale_price']) ? $pval['sale_price'] : $pval['price'];
                        }else{
                            $simplePrice = $pval['price'];
                        }
                        $quantity = $pval['product_quantity'];
                        if (!is_numeric($quantity)) {
                            $quantity = 1;
                        }

                        $totalOrderPrice = $totalOrderPrice + ($simplePrice * $quantity);
                    } else if ($pval['product_type'] == 'virtual') {
                        $virtualPrice = $pval['price'];
                        $totalOrderPrice = $totalOrderPrice +  $virtualPrice;
                    }
                    $shipping_price = isset($pval['shipping_price']) ? $pval['shipping_price'] : 0;
                    $totalOrderShimpentPrice = $totalOrderShimpentPrice + $shipping_price;

                    $str[$key1]['ship_price'] = $totalOrderShimpentPrice;
                    if (isset($shippingPriceArray[$key1])) {
                        $shippingPriceArray[$key1] = $totalOrderShimpentPrice + $shippingPriceArray[$key1];
                    } else {
                        $shippingPriceArray[$key1] = $totalOrderShimpentPrice;
                    }

                    if (isset($productPriceArray[$key1])) {
                        $productPriceArray[$key1] = $totalOrderPrice + $productPriceArray[$key1];
                    } else {
                        $productPriceArray[$key1] = $totalOrderPrice;
                    }
                }
            }
        }
        



        foreach ($order_info['orderData'] as $key => $val) {
            $chargeInfo = $order_info['charges'][$key];
            $platform_fee = $chargeInfo['platform_fee'];
            $payable_amount = $chargeInfo['payable_amount'];
            $paymentID = isset($chargeInfo['paymentId']) ? $chargeInfo['paymentId'] : '';
            $balanceTransaction = isset($chargeInfo['balance_transaction']) ? $chargeInfo['balance_transaction'] : '';
            $payerID = isset( $chargeInfo['payerID']) ?  $chargeInfo['payerID'] : '';
            $payment_status = $order_info['payment_status'] ? $order_info['payment_status'] : 'pending';
            $charge_status = $order_info['charge_status'] ? $order_info['charge_status'] : 'unpaid';
            if ($key != 'products_price' && $key != 'shipping_price' && $key != 'total_price' && $key != 'total_tax') {
                $this->store_order = new StoreOrder;
                
                $store_id = $key;
                $storeInfo = getStoreStatusById((int)$store_id);

                $google_analytics_arr[$counter_g] = array('transaction_id' => rand(258, 9854), 'affiliation' => 'Google online store', 'value' => "", 'currency' => 'USDC', 'tax' => 0, 'shipping' => $shippingPriceArray[$key]);
                $val['total_amount'] = 350;
                $val['shiping_amount'] = 30;
                $this->store_order->buyer_id = $get_user_id;
                // uncomment for crypto,further handle statuses 
                // if ($paymentID == "" &&  $order_info['balance_transaction'] == "") {
                //     $this->store_order->payment_method = 'crypto';
                //     $this->store_order->buyer_address = $order_info['buyer_address'];
                // } else {
                    $this->store_order->payment_method = 'stripe';
                // }
                $this->store_order->payment_status = $payment_status;
                $this->store_order->charge_status = $charge_status;
                $this->store_order->platform_fee = $platform_fee;
                $this->store_order->payable_amount = $payable_amount;
                $this->store_order->order_status = 'pending';
                $this->store_order->total_amount = $productPriceArray[$key] + $shippingPriceArray[$key];

                $this->store_order->shipping_address = $shipping_address_id;
                $this->store_order->shipping_method = !empty($val['shipping_method']) ? $val['shipping_method'] : '';
                $this->store_order->total_items = $val[0]['product_quantity'];
                $this->store_order->transaction_id = $paymentID;
                $this->store_order->balance_transaction = $balanceTransaction;
                $this->store_order->payerID = $payerID;
                $this->store_order->shipping_amount = $shippingPriceArray[$key];

                $this->store_order->store_id = $store_id;

                $order_id = $this->store_order->save();

                $lastInsertedId = $this->store_order->order_id;
                $items_Array = array();
                $sipping_amount = 0;
                if ($order_id) {
                    $store_data = getStoreInfoById((int)$store_id);
                    $currentDate = date('Y-m-d');
                    // $paymentDate = $currentDate;
                    $paymentDate = date('Y-m-d', strtotime($currentDate . ' + 10 days'));
                    $amountWithOutShip = $productPriceArray[$key] - $shippingPriceArray[$key];
                    $paymentProfit = $amountWithOutShip * 0.045;
                    $payableAmount = $productPriceArray[$key] - $paymentProfit;
                    $storeWallet = !empty($store_data[0]->wallet_address) ? $store_data[0]->wallet_address : '';

                    if ($paymentID == "" &&  $balanceTransaction == "") {
                        // $storeEscrew = new StoreEscrow();
                        // $storeEscrew->order_id = $lastInsertedId;
                        // $storeEscrew->store_id = $store_id;
                        // $storeEscrew->store_wallet_address = $storeWallet;
                        // $storeEscrew->order_price = $productPriceArray[$key];
                        // $storeEscrew->ship_price =  $shippingPriceArray[$key];
                        // $storeEscrew->order_price_with_out_shipping =  $amountWithOutShip;
                        // $storeEscrew->payable_amount =  $payableAmount;
                        // $storeEscrew->profit =  $paymentProfit;
                        // $storeEscrew->status =  'pending';
                        // $storeEscrew->profit_status =  'pending';
                        // $storeEscrew->payment_date =  $paymentDate;
                        // $storeEscrew->save();
                    }
                    
                    if (count($val) > 0) {
                        $total_amount = 0;
                        foreach ($val as $key => $product) {
                            if (isset($product['product_id']) && $product['product_id'] != '') {

                                if ($product['product_type'] == 'variant') {
                                    if($product['on_sale'] == 1){
                                     // $item_price = isset($product['sale_price']) ? $product['sale_price'] : $product['price'];
                                     $item_price = isset($product['variation_sale_price']) ? $product['variation_sale_price'] : $product['price'];

                                    }else{
                                      //  $item_price = $product['price'];
                                      $item_price = isset($product['variation_sale_price']) ? $product['variation_sale_price'] : $product['price'];
                                    }
                                    $quantity = $product['product_quantity'];
                                } else if ($product['product_type'] == 'simple') {
                                    if($product['on_sale'] == 1){
                                        $item_price = isset($product['sale_price']) ? $product['sale_price'] : $product['price'];
                                    }else{
                                        $item_price = $product['price'];
                                    }
                                    $quantity = $product['product_quantity'];
                                } else if ($product['product_type'] == 'virtual') {
                                  //  $item_price = isset($product['price']) ? $product['price'] : $product['price'];
                                   // $quantity = $product['product_quantity'];
                                   if (isset($product['on_sale']) && $product['on_sale'] == 1 )
                                    {
                                        $item_price = $product['sale_price'];
                                    }else{
                                        $item_price = isset($product['price']) ? $product['price'] : $product['price'];
                                    }
                                }

                                if (!is_numeric($quantity)) {
                                    $quantity = 1;
                                }
                                
                                $item_price_condition = is_numeric($item_price) ? $item_price : 0; // Check if $item_price is numeric, if not, set it to 0
                                //$item_price = floatval($quantity * $item_price_condition);
                                $item_price = floatval($item_price_condition);
                                $shipping_method = isset($product['method']) ? $product['method'] : "";
                                $store_id_pr = $product['store_id'];
                                $store_p = getStoreInfoById((int) $store_id_pr);
                                $shipping_charges = isset($product['shipping_price']) ? $product['shipping_price'] : 0;
                                if ($shipping_charges == '') {
                                    $shipping_charges = 0;
                                }
                                
                                $order_per_total = $shipping_charges + $item_price;
                                $product_variation_id = isset($product['variation_id']) ? $product['variation_id'] : "";
                                $shipping_price = isset($product['shipping_price']) ? $product['shipping_price'] : "";

                                $p_name = $product['title'] ?? 'Not Found';
                                $variation_info = '';
                                        if ($product_variation_id != '') {
                                            $variation_info = getVariationNameById($product_variation_id);
                                        }

                                $image_url = isset($product['feature_image']) ? $product['feature_image'] : '';
                                       
                                    if($image_url != ''){
                                        $newImagePath = copyImageFromUrlToNewFolder($image_url);
                                    }

                                if ($shipping_method == 'calculated' && isset($product['ship_id']) && $product['ship_id'] != '') {
                                    $shipment = $client->shipment->retrieve($product['ship_id']);

                                    $created_date = date("Y-m-d H:i:s");
                                    $items_Array[] = array(
                                        'order_id' => $lastInsertedId, 'store_id' => $store_id, 'item_id' => $product['product_id'],
                                        'sold_price' => $product['sale_price'],'sale_id' => $product['sale_id'],
                                        'variation_id' => $product_variation_id, 'quantity' => $product['product_quantity'], 'price' => $item_price,
                                        'shipping_amount' => $product['shipping_price'], 'order_note' => '', 'item_tax' => '',
                                        'method' => $shipping_method, 'total' => $order_per_total, 'shipment_id' => $product['ship_id'], 'payment_service' => $product['shipping_method'], 'shipment_id' => $shipment->id, 'rate_id' => '', 'label_url' => '', 'lable_pdf_url' => '', 'tracking_id' => '', 'tracking_code' => '', 'shipment_object' => '', 'created_at' => $created_date,
                                        'product_name' => $p_name, 'variation_info' => $variation_info, 'item_image' => $newImagePath
                                    );
                                
                                } else {
                                    $created_date = date("Y-m-d H:i:s");
                                    $items_Array[] = array(
                                        'order_id' => $lastInsertedId, 'store_id' => $store_id, 'item_id' => $product['product_id'],
                                        'sold_price' => $product['sale_price'],'sale_id' => $product['sale_id'],
                                        'variation_id' => $product_variation_id, 'quantity' => $product['product_quantity'], 'price' => $item_price,
                                        'shipping_amount' => $shipping_price, 'order_note' => '', 'item_tax' => '',
                                        'shipment_id' => '', 'rate_id' => '', 'label_url' => '',
                                        'lable_pdf_url' => '', 'tracking_id' => '', 'tracking_code' => '', 'payment_service' => '', 'shipment_object' => '',
                                        'method' => $shipping_method, 'total' => $order_per_total, 'created_at' => $created_date,
                                        'product_name' => $p_name, 'variation_info' => $variation_info, 'item_image' => $newImagePath
                                    );
                                }
                                

                                $storeInfo = getStoreStatusById((int)$store_id);
                                

                                // echo '$storeInfo: <pre>' .print_r($storeInfo,true). '</pre>'; die();
                                if (isset($storeInfo->stripe) && $storeInfo->stripe == 1) {
                                    
                                    if (isset($_COOKIE['cartCookie'])) {
                                        unset($_COOKIE['cartCookie']);
                                        setcookie('cartCookie', null, -1, '/');
                                    }
                                    if (isset($_COOKIE['cartCookieCrypto'])) {
                                        unset($_COOKIE['cartCookieCrypto']);
                                        setcookie('cartCookieCrypto', null, -1, '/');
                                    }
                                }


                                $remainStock = "";
                                if (isset($product['variation_id']) && $product['variation_id'] != '') {
                                   
                                    variableUpdateCurrentStock($product['variation_id'], $product['product_id'], $product['product_quantity']);
                                    // change status of flash sale
                                    
                                    $productFlashsaleStatus = DB::table('products')->where('product_id', $product['product_id'])->value('flashsale_status');
                                    if ($productFlashsaleStatus == 'choosen') {
                                        
                                        $productData = getvariableProduct($product['product_id']);
                                        $remainStock = $productData['current_stock'];
                                        if ($remainStock <= 0) {
                                            
                                            DB::table('products')->where('product_id', $product['product_id'])->update(['flashsale_status' => 'standard']);
                                        }
                                        
                                    }
                                    
                                } else {
                                    
                                    simpleUpdateCurrentStock($product['product_id'], $product['product_quantity']);
                                    // change flash sale status
                                    $productquery = DB::table('products')->where('product_id', $product['product_id'])->select('flashsale_status', 'current_stock')->first();
                                    $remainStock = $productquery->current_stock;
                                    if ($productquery->flashsale_status == 'choosen' && $remainStock <= 0) {
                                        DB::table('products')->where('product_id', $product['product_id'])->update(['flashsale_status' => 'standard']);
                                    }
                                }
                                
                            }
                        }

                        if (count($items_Array) > 0) {
                            
                            $this->add_order_items($items_Array);
                        }
                        
                    }

                    //die();
                }
                
                $userData = getUserByIdchat($get_user_id);

                $subject = 'Your Order is Submitted successfully';
                $subjectStore = Constants::ORDER_CREATE_SUBJECT_SELLER.$lastInsertedId;
                $subjectBuyer = Constants::ORDER_CREATE_SUBJECT_BUYER.$lastInsertedId;
                $toEmail = $userData->email;

                $toCompletename = $userData->name;
                $store = getStoreInfoById((int) $store_id);

                $user_id = isset($store[0]->user_id) ? $store[0]->user_id : '';
                $userDataStore = getUserByIdchat((int) $user_id);

                if ($userDataStore != '' && isset($userDataStore->email)) {
                    $toEmailStore = $userDataStore->email;
                }
                if ($userDataStore != '' && isset($userDataStore->name)) {
                    $store_name = $userDataStore->name;
                }
                $country_name = $order_info['country'];
                if(isset($order_info['states'])){
                    $state_id = $order_info['states'];
                    $state = getStatebyId($state_id);
                    $state_name = $state['state_name'];
                }
                // $country = getCountryById($country_id);
                // $country_name = $country['code'];
                
                $html = view('mail.storecreateOrder', [
                    'email' => $order_info['useremail'], 'name' => $order_info['full_name'],
                    'city' => $order_info['city'], 'street' => $order_info['address'], 'postalcode' => $order_info['post_code'],
                    'state' =>isset($state_name) ? $state_name : null, 'country' => $country_name, 'storeName' => $store_name, 'order_id' => $order_id, 'productDetails' => $val
                ]);
                
                $subjectVendor = 'You’ve Received an Order! ';

                $params = [
                    'from'    => 'MallShark  ' . env('SUPPORT_EMAIL'),
                    'to'      => $toEmailStore,
                    'subject' => $subjectVendor,
                    'html'    => "$html",
                ];
                
                $results = $this->mailgun->SendMailgunMail($params);
                
            }
            $counter_g++;
            $order_ids[]=$lastInsertedId;
        }
          
        Session::put('google_analytics', $google_analytics_arr);
        DB::table('products_cart')->where('user_id', '=', getCurrentUserId())->delete();
        // die();
        return $order_ids;
        return $lastInsertedId;
    }catch(Exception $e){
        dd($e->getMessage());
    }
    }
   

    public function create_order_crypto($order_info = array())
    {
        $client = new \EasyPost\EasyPostClient(env('EASYPOST_API_KEY'));
        //  dd($order_info);
        //   dd($order_info);
        //  echo '$order_info: <pre>' .print_r($order_info,true). '</pre>'; die();
        $sess = array();
        $insertedData = array();
        // if (Session::has('orderData')) {
        //     $sess = Session::get('orderData');
        // }
        //  dd($order_info);
        // if (count($sess) > 0) {

        $shipping_address_id = '';
        $get_user_id = '';
        $item_price = '';
        // if (isset($order_info['shipping_id'])) {
        //     $shipping_address_id = $order_info['shipping_id'];
        // } else {

        $user_email = isset($order_info['useremail']) ? $order_info['useremail'] : "";
        $user = User::where('email', $user_email)->first();

        $login_key = str_random(25);
        if ($user) {
            $get_user_id = $user->user_id;
            $this->shipping_address->user_id = $user->user_id;
            // $this->shipping_address->user_id = getCurrentUserId();
            $this->shipping_address->full_name = $order_info['full_name'];
            $this->shipping_address->country = $order_info['s_country'];
            $this->shipping_address->state = isset($order_info['states_countries']) ? $order_info['states_countries'] : "46";
            $this->shipping_address->city = $order_info['city'];
            $this->shipping_address->street = $order_info['s_street'];
            $this->shipping_address->post_code = $order_info['s_post_code'];
            // $this->shipping_address->flat_other = $order_info['s_flat_other'];
            $this->shipping_address->save();
            $shipping_address_id = $this->shipping_address->sh_id;
        } else {
            // User does not exist, insert new record
            $this->_user->name = $order_info['full_name'];
            $this->_user->email = $user_email;
            $this->_user->password = md5('mallshark123456');
            $this->_user->login_key = $login_key;
            $this->_user->register_source = 'MallShark';

            $this->_user->contact_no = isset($order_info['phone_number']) ? $order_info['phone_number'] : "";

            $this->_user->save();

            $lastInsertid = $this->_user->user_id;
            $get_user_id = $this->_user->user_id;
            //   dd($get_user_id);


            $this->shipping_address->full_name = $order_info['full_name'];
            $this->shipping_address->country = $order_info['s_country'];
            $this->shipping_address->state = $order_info['states_countries'];
            $this->shipping_address->street = $order_info['s_street'];
            $this->shipping_address->post_code = $order_info['s_post_code'];
            $this->shipping_address->city = $order_info['city'];
            $this->shipping_address->user_id = $lastInsertid;
            $this->shipping_address->save();

            $this->_userRole->user_id = $lastInsertid;
            $this->_userRole->role_id = 4;
            $this->_userRole->save();
            $url = URL::to('/validate-email/' . $login_key);
            $html = view('mail.activateUser_checkout', ['activate_url' => $url]);
            $subject = 'Welcome to MallShark - Get Ready for an Unforgettable Shopping Experience!';
            //  $templateSlug = 'signup';
            $toEmail = $order_info['useremail'];
            $toCompletename = $order_info['full_name'];
            $params = [
                'from'    => 'support@mallshark.net',
                'to'      => $toEmail,
                'subject' => $subject,
                'html'    => "$html",
            ];
            $results = $this->mailgun->SendMailgunMail($params);
            $this->_esearch->userMapping($lastInsertid);
        }
        // }
        $counter_g = 0;
        $shippingPriceArray = array();
        $productPriceArray = array();
        foreach ($order_info['orderData'] as $key1 => $val) {



            foreach ($val as $pval) {
                $totalOrderShimpentPrice = 0;
                $totalOrderPrice = 0;
                if ($key1 == $pval['store_id']) {

                    if ($pval['product_type'] == 'variant') {
                        $vaiablePrice = isset($pval['variation_sale_price']) ? $pval['variation_sale_price'] : $pval['price'];
                        if (is_numeric($vaiablePrice) && is_numeric($pval['product_quantity'])) {
                            $totalOrderPrice = $totalOrderPrice + ($vaiablePrice * $pval['product_quantity']);
                        }
                    } else if ($pval['product_type'] == 'simple') {
                        $simplePrice = isset($pval['price']) ? $pval['price'] : $pval['price'];
                        $quantity = $pval['product_quantity'];
                        if (!is_numeric($quantity)) {
                            $quantity = 1;
                        }

                        $totalOrderPrice = $totalOrderPrice + ($simplePrice * $quantity);
                    } else if ($pval['product_type'] == 'virtual') {
                        $virtualPrice = $pval['price'];
                        $totalOrderPrice = $totalOrderPrice +  $virtualPrice;
                    }
                    // $totalOrderPrice = $totalOrderPrice + ($pval['price'] * $pval['product_quantity']);
                    $shipping_price = isset($pval['shipping_price']) ? $pval['shipping_price'] : 0;
                    $totalOrderShimpentPrice = $totalOrderShimpentPrice + $shipping_price;

                    $str[$key1]['ship_price'] = $totalOrderShimpentPrice;
                    if (isset($shippingPriceArray[$key1])) {
                        $shippingPriceArray[$key1] = $totalOrderShimpentPrice + $shippingPriceArray[$key1];
                    } else {
                        $shippingPriceArray[$key1] = $totalOrderShimpentPrice;
                    }

                    if (isset($productPriceArray[$key1])) {
                        $productPriceArray[$key1] = $totalOrderPrice + $productPriceArray[$key1];
                    } else {
                        $productPriceArray[$key1] = $totalOrderPrice;
                    }
                }
            }
        }
        //   echo 'Data: <pre>' .print_r($order_info['orderData'],true). '</pre>'; die();
        foreach ($order_info['orderData'] as $key => $val) {
            $chargeInfo = $order_info['charges'][$key];
            $paymentID = $chargeInfo['paymentID'];
            $balanceTransaction = $chargeInfo['balance_transaction'];
            $payerID = $chargeInfo['payerID'];
            // echo 'Data: <pre>' .print_r($val['shipping_method'],true). '</pre>'; die();

            if ($key != 'products_price' && $key != 'shipping_price' && $key != 'total_price' && $key != 'total_tax') {
                $this->store_order = new StoreOrder;
                // $xploded_key = explode("-", $key);
                // $store_id = $xploded_key[1];
                $store_id = $key;
                $storeInfo = getStoreStatusById((int)$store_id);
                // echo '$storeInfo: <pre>' .print_r($storeInfo,true). '</pre>'; die();
                $google_analytics_arr[$counter_g] = array('transaction_id' => rand(258, 9854), 'affiliation' => 'Google online store', 'value' => $productPriceArray[$key], 'currency' => 'USDC', 'tax' => 0, 'shipping' => $shippingPriceArray[$key]);
                $val['total_amount'] = 350;
                $val['shiping_amount'] = 30;
                $this->store_order->buyer_id = $get_user_id;
                if ($order_info['paymentID'] == "" &&  $order_info['balance_transaction'] == "") {
                    $this->store_order->payment_method = 'crypto';
                    $this->store_order->buyer_address = $order_info['buyer_address'];
                } else {
                    $this->store_order->payment_method = 'stripe';
                }

                $this->store_order->order_status = 'pending';
                $this->store_order->total_amount = $productPriceArray[$key]  + $shippingPriceArray[$key];
                $this->store_order->shipping_address = $shipping_address_id;
                $this->store_order->shipping_method = !empty($val['shipping_method']) ? $val['shipping_method'] : '';
                $this->store_order->total_items = $val[0]['product_quantity'];
                $this->store_order->transaction_id = $paymentID;
                $this->store_order->balance_transaction = $balanceTransaction;
                $this->store_order->payerID = $payerID;
                $this->store_order->shipping_amount = $shippingPriceArray[$key];
                // $this->store_order->store_tax = $val['store_tax'];
                $this->store_order->store_id = $store_id;

                $order_id = $this->store_order->save();

                $lastInsertedId = $this->store_order->order_id;
                $items_Array = array();
                $sipping_amount = 0;
                if ($order_id) {
                    $store_data = getStoreInfoById((int)$store_id);
                    $currentDate = date('Y-m-d');
                    // $paymentDate = $currentDate;
                    $paymentDate = date('Y-m-d', strtotime($currentDate . ' + 10 days'));
                    $amountWithOutShip = $productPriceArray[$key] - $shippingPriceArray[$key];
                    $paymentProfit = $amountWithOutShip * 0.045;
                    $payableAmount = $productPriceArray[$key] - $paymentProfit;
                    $storeWallet = !empty($store_data[0]->wallet_address) ? $store_data[0]->wallet_address : '';

                    if ($payerID == "" &&  $balanceTransaction == "") {
                        $storeEscrew = new StoreEscrow();
                        $storeEscrew->order_id = $lastInsertedId;
                        $storeEscrew->store_id = $store_id;
                        $storeEscrew->store_wallet_address = $storeWallet;
                        $storeEscrew->order_price =  $productPriceArray[$key];
                        $storeEscrew->ship_price =  $shippingPriceArray[$key];
                        $storeEscrew->order_price_with_out_shipping =  $amountWithOutShip;
                        $storeEscrew->payable_amount =  $payableAmount;
                        $storeEscrew->profit =  $paymentProfit;
                        $storeEscrew->status =  'pending';
                        $storeEscrew->profit_status =  'pending';
                        $storeEscrew->payment_date =  $paymentDate;
                        $storeEscrew->save();
                    }
                    if (count($val) > 0) {
                        $total_amount = 0;
                        foreach ($val as $key => $product) {


                            if (isset($product['product_id']) && $product['product_id'] != '') {
                                //DB::table('products')->increment('sales');
                                //$product_quantity = isset($product['product_quantity]']) ? $product['product_quantity]'] : "";
                                if ($product['product_type'] == 'variant') {
                                    $item_price = isset($product['variation_sale_price']) ? $product['variation_sale_price'] : $product['price'];
                                    $quantity = $product['product_quantity'];

                                    if (!is_numeric($quantity)) {
                                        $quantity = 1;
                                    }
                                } else if ($product['product_type'] == 'simple') {

                                    $item_price = isset($product['price']) ? $product['price'] : $product['price'];
                                    // dd($item_price);
                                    $quantity = $product['product_quantity'];

                                    if (!is_numeric($quantity)) {
                                        $quantity = 1;
                                    }
                                } else if ($product['product_type'] == 'virtual') {
                                    $item_price = isset($product['price']) ? $product['price'] : $product['price'];
                                    $quantity = $product['product_quantity'];
                                }

                                // if($key == 1){

                                //     dd($item_price);
                                // }
                                $item_price = $quantity * $item_price;
                                $shipping_method = isset($product['method']) ? $product['method'] : "";
                                $store_id_pr = $product['store_id'];
                                // $store_p = getStoreInfoById((int) $store_id_pr);
                                $shipping_charges = isset($product['shipping_price']) ? $product['shipping_price'] : 0;
                                if ($shipping_charges == '') {
                                    $shipping_charges = 0;
                                }
                                $order_per_total = $shipping_charges + $item_price;
                                $product_variation_id = isset($product['variation_id']) ? $product['variation_id'] : "";
                                $shipping_price = isset($product['shipping_price']) ? $product['shipping_price'] : "";
                                // update cookie

                                $key = $_COOKIE['cartCookieCrypto'];
                                $this->_cart->where('product_id', '=', $product['product_id'])->where('crypto_random', '=', $key)->delete();



                                if ($shipping_method == 'calculated' && isset($product['ship_id']) && $product['ship_id'] != '') {
                                    $shipment = $client->shipment->retrieve($product['ship_id']);

                                    $created_date = date("Y-m-d H:i:s");
                                    $items_Array[] = array(
                                        'order_id' => $lastInsertedId, 'store_id' => $store_id, 'item_id' => $product['product_id'],
                                        'variation_id' => $product_variation_id, 'quantity' => $product['product_quantity'], 'price' => $item_price,
                                        'shipping_amount' => $product['shipping_price'], 'order_note' => '', 'item_tax' => '',
                                        'method' => $shipping_method, 'total' => $order_per_total, 'shipment_id' => $product['ship_id'], 'payment_service' => $product['shipping_method'], 'shipment_id' => $shipment->id, 'rate_id' => '', 'label_url' => '', 'lable_pdf_url' => '', 'tracking_id' => '', 'tracking_code' => '', 'shipment_object' => '', 'created_at' => $created_date
                                    );
                                } else {
                                    $created_date = date("Y-m-d H:i:s");
                                    $items_Array[] = array(
                                        'order_id' => $lastInsertedId, 'store_id' => $store_id, 'item_id' => $product['product_id'],
                                        'variation_id' => $product_variation_id, 'quantity' => $product['product_quantity'], 'price' => $item_price,
                                        'shipping_amount' => $shipping_price, 'order_note' => '', 'item_tax' => '',
                                        'shipment_id' => '', 'rate_id' => '', 'label_url' => '',
                                        'lable_pdf_url' => '', 'tracking_id' => '', 'tracking_code' => '', 'payment_service' => '', 'shipment_object' => '',
                                        'method' => $shipping_method, 'total' => $order_per_total, 'created_at' => $created_date
                                    );
                                }
                                //  dd($items_Array);

                                // $cashback = new PoolTController();
                                // $cashback->clearCashbacksProduct($product['product_id'], $store_id, getCurrentUserId(), $lastInsertedId, $quantity);
                                $remainStock = "";
                                if (isset($product['variation_id']) && $product['variation_id'] != '') {
                                    variableUpdateCurrentStock($product['variation_id'], $product['product_id'], $product['product_quantity']);
                                    // change status of flash sale
                                    $productFlashsaleStatus = DB::table('products')->where('product_id', $product['product_id'])->value('flashsale_status');
                                    if ($productFlashsaleStatus == 'choosen') {
                                        $productData = getvariableProduct($product['product_id']);
                                        $remainStock = $productData['current_stock'];
                                        if ($remainStock <= 0) {
                                            DB::table('products')->where('product_id', $product['product_id'])->update(['flashsale_status' => 'standard']);
                                        }
                                    }
                                } else {
                                    simpleUpdateCurrentStock($product['product_id'], $product['product_quantity']);
                                    // change flash sale status
                                    $productquery = DB::table('products')->where('product_id', $product['product_id'])->select('flashsale_status', 'current_stock')->first();
                                    $remainStock = $productquery->current_stock;
                                    if ($productquery->flashsale_status == 'choosen' && $remainStock <= 0) {
                                        DB::table('products')->where('product_id', $product['product_id'])->update(['flashsale_status' => 'standard']);
                                    }
                                }
                            }
                        }
                        //   echo 'Data: <pre>' .print_r($items_Array,true). '</pre>'; die();
                        if (count($items_Array) > 0) {
                            $this->add_order_items($items_Array);

                            $storeInfo = getStoreStatusById((int)$store_id);





                            // return 'order crated';
                        }
                    }
                    // if (isset($_COOKIE['cartCookieCrypto'])) {
                    //     unset($_COOKIE['cartCookieCrypto']); 
                    //     setcookie('cartCookieCrypto', null, -1, '/'); 
                    // }
                    //die();
                }
                // dd($get_user_id);
                $userData = getUserByIdchat($get_user_id);


                $subject = 'Your Order is Submitted successfully';
                $subjectStore = 'Order Received';
                $templateSlug = 'new-order';
                $templateSlugStore = 'new-order-store';
                $toEmail = $userData->email;

                $toCompletename = $userData->name;
                $store = getStoreInfoById((int) $store_id);
                // $store_name = isset($store[0]->store_name) ? $store[0]->store_name : '';
                $user_id = isset($store[0]->user_id) ? $store[0]->user_id : '';
                $userDataStore = getUserByIdchat((int) $user_id);
                // dd($userDataStore);
                // $toEmailStore = 'dddddddddddd@dddddd.com';
                if ($userDataStore != '' && isset($userDataStore->email)) {
                    $toEmailStore = $userDataStore->email;
                }
                if ($userDataStore != '' && isset($userDataStore->name)) {
                    $store_name = $userDataStore->name;
                }

                $description = 'You received a new order on from ' . $toCompletename;
                addNotifications($user_id, getCurrentUserId(), $subjectStore . ' With ID' . $lastInsertedId, $description, route('brand-orders') . '?status=pending&selector=' . $lastInsertedId);
                //sleep(1);
                $html = view('mail.createOrder', ['customerDetail' => $userData, 'productDetails' => $val]);
                $subject = 'Created Order. Your Order NO ' . $order_id;
                $toEmail = $userData->email;
                $params = [
                    'from'    => 'MallShark  ' . env('SUPPORT_EMAIL'),
                    'to'      => $toEmail,
                    'subject' => $subject,
                    'html'    => "$html",
                ];
                // echo 'Data: <pre>' .print_r($params,true). '</pre>'; die();
                try {
                    $this->mailgun->SendMailgunMail($params);
                    // The email was sent successfully
                } catch (\Exception $e) {
                    // An error occurred while sending the email
                    // Handle the error here
                }
                // $results = $this->mailgun->SendMailgunMail($params); 

                // store email
                $html = view('mail.storecreateOrder', ['storeDetail' => $userData, 'storeName' => $store_name, 'order_id' => $order_id, 'productDetails' => $val]);
                $subjectVendor = 'You’ve Received an Order! ' . $order_id;

                $params = [
                    'from'    => 'MallShark  ' . env('SUPPORT_EMAIL'),
                    'to'      => $toEmailStore,
                    'subject' => $subjectVendor,
                    'html'    => "$html",
                ];
                try {
                    $this->mailgun->SendMailgunMail($params);
                    // The email was sent successfully
                } catch (\Exception $e) {
                    // An error occurred while sending the email
                    // Handle the error here
                }
                // $results = $this->mailgun->SendMailgunMail($params); 
            }
            $counter_g++;
        }
        if (isset($_COOKIE['cartCookieCrypto'])) {
            unset($_COOKIE['cartCookieCrypto']);
            setcookie('cartCookieCrypto', null, -1, '/');
        }
        // Session::put('google_analytics', $google_analytics_arr);
        // DB::table('products_cart')->where('user_id', '=', getCurrentUserId())->delete();

        // return Redirect::to('/');
        // die();
        return 'order created succesfully and your order id is:' . $lastInsertedId;
        // } else {
        //     return 'unable to create order';
        // }
    }

    public function add_order_items($item_array = array())
    {
        $this->order_items->insert($item_array);
    }

    public function getOrderItemsByOrderId($orderId = '')
    {
     
        $orderItems = getOrderItemsByOrderId($orderId);
        $orderdetails =  StoreOrder::with('orderItems', 'shippingAddress', 'store', 'user')->where('order_id', $orderId)->get();
        // dump("data",$orderdetails);
        // // echo '$orderItems: <pre>' .print_r($orderItems,true). '</pre>';
        // $orderInfo = getStoreOrder($orderId);
        // $shipping_address_id = $orderInfo->shipping_address;
       
        // $shippingInfo =  getShippingAddressbyId($shipping_address_id);
        // $street = 
        //   dump("productData", $shippingInfo);
        // $productArray = [];
        // $shippingDetails = [];

        // $shippingDetails[] = [
        //     'street' => $shippingInfo['street'],
        // ];


        // foreach($orderItems as $key => $value){
        //     $product_id = $value->item_id;
        //     $productData = getProductIdSpecificColums($product_id);
        //     // $shipping_address = getShippingAddressbyId($value->shipping_address);
        //     // dump("productData", $shipping_address);
        //     $productArray[$value->order_id][] = [
        //                 'title' => $productData->title,
        //                 'slug' => $productData->slug,
        //                 'feature_image' => $productData->feature_image,
        //                 'product_type' => $productData->product_type,
        //                 'price_total' => $value->total,
        //                 'quantity' => $value->quantity,
        //                 'store_id' => $value->store_id,
        //                 'price' => $value->price,
        //                 'shipping_amount' => $value->shipping_amount,
        //                 'total' => $value->total,
                       
        
        //             ];
        // }
        // dump("s", $productArray);
        // die;
      



        $orders = array();
        if ($orderId != '') {
            $orders = DB::table('store_orders')
                ->where('store_orders.order_id', '=', $orderId)
                ->join('order_items', 'store_orders.order_id', '=', 'order_items.order_id')
                ->join('stores', 'store_orders.store_id', '=', 'stores.store_id')
                ->join('shipping_addresses', 'store_orders.shipping_address', '=', 'shipping_addresses.sh_id')
                ->select(
                    'store_orders.order_id',
                    'store_orders.store_tax',
                    'stores.store_id',
                    'stores.store_logo',
                    'store_orders.buyer_id',
                    'store_orders.order_status',
                    'store_orders.shipping_amount',
                    'store_orders.shipping_address',
                    'store_orders.payment_method',
                    'store_orders.total_amount',
                    'store_orders.created_at',
                    'order_items.item_id',
                    'order_items.quantity',
                    'order_items.variation_id',
                    'order_items.order_note',
                    'order_items.price as order_per_price',
                    'order_items.shipping_amount as order_per_shipping',
                    'shipping_addresses.street',
                    'shipping_addresses.country',
                    'shipping_addresses.flat_other',
                    'order_items.total as order_total',
                    'shipping_addresses.city',
                    'shipping_addresses.state',
                    'shipping_addresses.full_name',
                    'shipping_addresses.user_id',
                    'shipping_addresses.post_code'
                )
                ->get()
                ->toArray();
        }

        return $orderdetails;
    }

    public function getOrderDetailByOrderId($order_id)
    {

        try {
            $orderdetails =  StoreOrder::with('orderItems', 'refundItems','shippingAddress', 'store', 'user' )->where('charge_status','paid')->where('order_id', $order_id)->get();
            
            if (count($orderdetails) > 0) {
                return $orderdetails;
            } else {
                return false;
            }
        } catch (\Throwable $th) {
            echo $th->getMessage();
        }
    }

    public function getOrderDetailByOrderIdApi($order_id)
    {
        $orderdetails = StoreOrder::with([
            'orderItemsList' => function ($itemlist) {
                $itemlist->select('order_item_id','order_id', 'item_id', 'store_id', 'quantity', 'price', 'shipping_amount', 'shipment_id', 'label_url','lable_pdf_url','tracking_id','tracking_code','method', 'total','payment_service', 'created_at');
            }, 
            'shippingAddress' => function ($shipping) {
                $shipping->select('sh_id', 'country', 'full_name', 'state', 'city', 'street', 'post_code', 'flat_other');
            },
            // 'shippingAddress.country',
            'store' => function ($store) {
                $store->select('store_id', 'store_country', 'store_state', 'store_city', 'street_address', 'store_name', 'store_slug', 'store_logo', 'contact_info', 'store_status' );
            },
            'user' => function ($user) {
                $user->select('user_id', 'name', 'slug', 'email', 'status', 'user_country_id', 'user_state_id','user_zip_code', 'contact_no', 'physical_address' );
            }
        ])->select('order_id', 'buyer_id', 'store_id', 'payment_method', 'total_amount', 'payable_amount', 'payment_status', 'shipped_date','order_status','shipping_amount','shipping_address')
        ->where('order_id', $order_id)->get()->toArray();
       
        if (count($orderdetails) > 0) {
            return $orderdetails;
        } else {
            return false;
        }
      
    }

    // public function get_orders_with_items_all_new($store_id, $input = array())
    // {
    //     $storeOrders = StoreOrder::with('orderItemsList')->where('charge_status','paid')->orderBy('created_at', 'desc')->where('store_id', $store_id)->paginate(15)->toArray();
    //     if (count($storeOrders) > 0) {
    //         return $storeOrders;
    //     } else {
    //         return false;
    //     }
    // }

    public function get_orders_with_items_all_new($store_id, $input = array())
    {
        // Get status from input, defaulting to 'All' if not set
        $order_status = isset($input['order_status']) ? $input['order_status'] : 'All';

        // Start the query with necessary conditions
        $query = StoreOrder::with('orderItemsList')
                        ->where('charge_status', 'paid')
                        ->where('store_id', $store_id)
                        ->orderBy('created_at', 'desc');
        
        // If the order status filter is not 'All', add the condition
        if ($order_status && $order_status !== 'All') {
            $query->where('order_status', strtolower($order_status)); // Assuming order_status is in lowercase
        }

        // Paginate results
        $storeOrders = $query->paginate(15)->withQueryString()->toArray();

        // Return orders or false if none found
        return count($storeOrders) > 0 ? $storeOrders : false;
    }
    
     public function get_orders_with_items_all_new_Api($store_id, $order_id = null, $order_status = null, $keyword = null, $From_Date = null, $To_Date = null)
    {
        $query = StoreOrder::with('orderItemsList', 'user', 'shippingAddress')
            ->where('charge_status', 'paid')
            ->where('store_id', $store_id);

        // Handle date filtering
        if ($From_Date && $To_Date) {
            $From_Date = \Carbon\Carbon::createFromFormat('m/d/Y', $From_Date)->format('Y-m-d');
            $To_Date = \Carbon\Carbon::createFromFormat('m/d/Y', $To_Date)->format('Y-m-d');
            $query->whereBetween('created_at', [$From_Date . ' 00:00:00', $To_Date . ' 23:59:59']);
        }

        // Handle keyword filtering
        if ($keyword !== null) {
            if (is_numeric($keyword) && (int)$keyword == $keyword) {
                $orderId = (int)$keyword;

                // Check if any orders match the given order_id
                $storeOrder = $query->where('order_id', $orderId)->paginate(10);

                // If matching orders are found, return them
                if ($storeOrder->total() > 0) {
                    return $storeOrder->toArray();
                }
            }

            $query->where(function ($q) use ($keyword) {
                $q->whereHas('user', function ($userQuery) use ($keyword) {
                    $userQuery->where('email', 'LIKE', "%{$keyword}%")
                        ->orWhere('name', 'LIKE', "%{$keyword}%");
                })->orWhereHas('orderItemsList', function ($orderItemQuery) use ($keyword) {
                    $orderItemQuery->whereHas('products', function ($productQuery) use ($keyword) {
                        $productQuery->where('title', 'LIKE', "%{$keyword}%");
                    });
                });
            });
        }

        // Handle order_id filtering
        if ($order_id !== null) {
            $query->where('order_id', $order_id);
        }

        // Handle order_status filtering
        if ($order_status !== null) {
            $query->where('order_status', $order_status);
        }

        // Fetch and paginate results
        $storeOrders = $query->orderBy('created_at', 'desc')->paginate(10);

        return $storeOrders->toArray();
    }
    public function get_orders_with_items_all_new_Api_no_pagination($store_id, $order_id = null, $order_status = null)
    {
        $query = StoreOrder::with('orderItemsList','user','shippingAddress')->where('store_id', $store_id);
    
        if ($order_id !== null && $order_status !== null) {
            
            $storeOrder = $query->where('order_id', $order_id)->get();
    
            if ($storeOrder) {
                return $storeOrder->toArray();
            } else {
                return false;
            }
        } elseif ($order_id !== null) {
          
            $storeOrder = $query->where('order_id', $order_id)->get();
    
            if ($storeOrder) {
                return $storeOrder->toArray();
            } else {
                return false;
            }
        } elseif ($order_status !== null) {
        
            $storeOrders = $query->where('order_status', $order_status)->orderBy('created_at', 'desc')->get();
    
            if (count($storeOrders) > 0) {
                return $storeOrders;
            } else {
                return false;
            }
        } else {
            
            $storeOrders = $query->orderBy('created_at', 'desc')->get();
            if (count($storeOrders) > 0) {
                return $storeOrders->toArray();
            } else {
                return false;
            }
        }
    }
    public function get_buyer_orders_with_items_all_new_Api($user_id, $order_id = null, $order_status = null)
    {
        $query = StoreOrder::with('orderItemsList.review','user','shippingAddress')->where('charge_status','paid')->where('buyer_id', $user_id);
        
        if ($order_id !== null && $order_status !== null) {
            
            $storeOrder = $query->where('order_id', $order_id)->get();
    
            if ($storeOrder) {
                return $storeOrder->toArray();
            } else {
                return false;
            }
        } elseif ($order_id !== null) {
          
            $storeOrder = $query->where('order_id', $order_id)->get();
    
            if ($storeOrder) {
                return $storeOrder->toArray();
            } else {
                return false;
            }
        } elseif ($order_status !== null) {
        
            $storeOrders = $query->where('order_status', $order_status)->orderBy('created_at', 'desc')->get();
    
            if (count($storeOrders) > 0) {
                return $storeOrders->toArray();
            } else {
                return false;
            }
        } else {
            
            $storeOrders = $query->orderBy('created_at', 'desc')->get();
            if (count($storeOrders) > 0) {
                return $storeOrders->toArray();
            } else {
                return false;
            }
        }
    }

    // lastest commit 
    //     public function get_orders_with_items_all($store_ids = array(), $input = array())
    // {
    //     $query = DB::table('store_orders')
    //         ->whereIn('store_orders.store_id', $store_ids)
    //         ->join('order_items', 'store_orders.order_id', '=', 'order_items.order_id')
    //         ->join('shipping_addresses', 'store_orders.shipping_address', '=', 'shipping_addresses.sh_id')
    //         ->join('stores', 'store_orders.store_id', '=', 'stores.store_id')
    //         ->orderBy('store_orders.created_at', 'DESC')
    //         ->select('store_orders.order_id', 'store_orders.buyer_address', 'order_items.shipment_id', 'order_items.method', 'order_items.order_item_id', 'order_items.payment_service', 'stores.wallet_address', 'store_orders.is_refund', 'store_orders.store_id', 'store_orders.buyer_id', 'order_items.total', 'order_items.tracking_id', 'order_items.tracking_code', 'order_items.label_url', 'order_items.lable_pdf_url', 'store_orders.order_status', 'store_orders.payment_status', 'store_orders.shipping_address', 'store_orders.payment_method', 'store_orders.total_amount', 'store_orders.created_at', 'order_items.item_id', 'order_items.quantity', 'order_items.variation_id', 'order_items.order_note', 'order_items.price', 'shipping_addresses.street', 'shipping_addresses.country', 'shipping_addresses.flat_other', 'shipping_addresses.city', 'shipping_addresses.state', 'shipping_addresses.full_name', 'shipping_addresses.user_id', 'shipping_addresses.post_code');

    //     if (isset($input['search']) && $input['search'] != '') {
    //         $search = $input['search'];
    //         $user_id = getUserIdBy($search);

    //         if (count($user_id) == 0) {
    //             $query->where('store_orders.order_id', '=', $search);
    //         } else {
    //             $query->whereIn('store_orders.buyer_id', $user_id);
    //         }
    //     }

    //     $orders = $query->paginate(10); // Specify the number of orders per page
    // // echo '$orders: <pre>' .print_r($orders,true). '</pre>'; die();
    //     if (count($orders) > 0) {
    //         $order_items = array();
    //         foreach ($orders as $order) {
    //             $order_items[$order->order_id][] = $order;
    //         }
    //         return $order_items;
    //     }
    // }


    public function get_orders_with_items_all($store_ids = array(), $input = array())
    {


        if (isset($input['search']) && $input['search'] != '') {
            $search = $input['search'];
            $user_id = getUserIdBy($search);

            if (count($user_id) == 0) {


                $orders = DB::table('store_orders')
                    ->whereIn('store_orders.store_id', $store_ids)
                    ->Where('store_orders.order_id', '=', $search)
                    ->join('order_items', 'store_orders.order_id', '=', 'order_items.order_id')
                    ->join('shipping_addresses', 'store_orders.shipping_address', '=', 'shipping_addresses.sh_id')
                    ->join('stores', 'store_orders.store_id', '=', 'stores.store_id')
                    ->orderBy('store_orders.created_at', 'DESC')
                    //->get()
                    ->select('store_orders.order_id', 'store_orders.buyer_address', 'order_items.shipment_id', 'order_items.method', 'order_items.order_item_id', 'order_items.payment_service', 'stores.wallet_address', 'store_orders.is_refund', 'order_items.total', 'order_items.tracking_id', 'order_items.label_url', 'order_items.lable_pdf_url', 'order_items.tracking_code', 'store_orders.buyer_id', 'store_orders.order_status', 'store_orders.payment_status', 'store_orders.shipping_address', 'store_orders.payment_method', 'store_orders.total_amount', 'store_orders.created_at as store_create_date', 'order_items.item_id', 'order_items.quantity', 'order_items.variation_id', 'order_items.order_note', 'order_items.price', 'shipping_addresses.street', 'shipping_addresses.country', 'shipping_addresses.flat_other', 'shipping_addresses.city', 'shipping_addresses.state', 'shipping_addresses.full_name', 'shipping_addresses.user_id', 'shipping_addresses.post_code')
                    ->get()
                    ->toArray();
            } else {

                $orders = DB::table('store_orders')
                    ->whereIn('store_orders.store_id', $store_ids)
                    ->whereIn('store_orders.buyer_id', $user_id)
                    ->join('order_items', 'store_orders.order_id', '=', 'order_items.order_id')
                    ->join('shipping_addresses', 'store_orders.shipping_address', '=', 'shipping_addresses.sh_id')
                    ->join('stores', 'store_orders.store_id', '=', 'stores.store_id')
                    ->orderBy('store_orders.created_at', 'DESC')
                    //->get()
                    ->select('store_orders.order_id', 'store_orders.buyer_address', 'order_items.shipment_id', 'order_items.method', 'order_items.order_item_id', 'order_items.payment_service', 'stores.wallet_address', 'store_orders.is_refund', 'order_items.total', 'order_items.tracking_id', 'order_items.label_url', 'order_items.lable_pdf_url', 'order_items.tracking_code', 'store_orders.buyer_id', 'store_orders.order_status', 'store_orders.payment_status', 'store_orders.shipping_address', 'store_orders.payment_method', 'store_orders.total_amount', 'store_orders.created_at', 'order_items.item_id', 'order_items.quantity', 'order_items.variation_id', 'order_items.order_note', 'order_items.price', 'shipping_addresses.street', 'shipping_addresses.country', 'shipping_addresses.flat_other', 'shipping_addresses.city', 'shipping_addresses.state', 'shipping_addresses.full_name', 'shipping_addresses.user_id', 'shipping_addresses.post_code')
                    ->get()
                    ->toArray();
            }
        } else {
            // echo 'Data: <pre>' .print_r($store_ids[0],true). '</pre>'; die();
            $orders = DB::table('store_orders')
                ->whereIn('store_orders.store_id', $store_ids)
                ->join('order_items', 'store_orders.order_id', '=', 'order_items.order_id')
                ->join('shipping_addresses', 'store_orders.shipping_address', '=', 'shipping_addresses.sh_id')
                ->join('stores', 'store_orders.store_id', '=', 'stores.store_id')
                ->orderBy('store_orders.created_at', 'DESC')
                //->get()
                ->select('store_orders.order_id', 'store_orders.buyer_address', 'order_items.shipment_id', 'order_items.method', 'order_items.order_item_id', 'order_items.payment_service', 'stores.wallet_address', 'store_orders.is_refund', 'store_orders.store_id', 'store_orders.buyer_id', 'order_items.total', 'order_items.tracking_id', 'order_items.tracking_code', 'order_items.label_url', 'order_items.lable_pdf_url', 'store_orders.order_status', 'store_orders.payment_status', 'store_orders.shipping_address', 'store_orders.payment_method', 'store_orders.total_amount', 'store_orders.created_at', 'order_items.item_id', 'order_items.quantity', 'order_items.variation_id', 'order_items.order_note', 'order_items.price', 'shipping_addresses.street', 'shipping_addresses.country', 'shipping_addresses.flat_other', 'shipping_addresses.city', 'shipping_addresses.state', 'shipping_addresses.full_name', 'shipping_addresses.user_id', 'shipping_addresses.post_code')
                ->get()
                ->toArray();
            // echo '$orders: <pre>' .print_r($orders,true). '</pre>'; die();
        }
        if (count($orders) > 0) {
            $order_items = array();
            foreach ($orders as $order) {
                $order_items[$order->order_id][] = $order;
            }
            //  echo '$order_items: <pre>' .print_r($order_items,true). '</pre>'; die;
            return $order_items;
        }
    }

    public function get_orders_with_items_allApi($store_ids = array())
    {
        $orders = StoreOrder::whereIn('store_id', $store_ids)
            ->with('store')
            ->with('shippingAddress')
            ->with('orderItemsList')
            ->orderBy('created_at', 'DESC')
            ->get()
            ->toArray();

        if (!empty($orders)) {
            $groupedOrders = [];

            foreach ($orders as $order) {
                $storeId = $order['store']['store_id'];

                if (!isset($groupedOrders[$storeId])) {
                    $groupedOrders[$storeId] = [];
                }

                $groupedOrders[$storeId][] = $order;
            }

            return $groupedOrders;
        }

        return [];
    }
    
    

    public function get_refund_cancel_orders_with_items_status($store_ids = array(), $input = array())
    {

        $status = '';
        if (array_key_exists('current_tab', $input)) {
            $status = $input['current_tab'];
        } else {
            $status = isset($_GET['status']) ? $_GET['status'] : '';
        }

        if (isset($input['search']) && $input['search'] != '') {
            $search = $input['search'];
            $user_id = getUserIdBy($search);
            if (count($user_id) == 0) {

                $orders = DB::table('store_orders')
                    ->whereIn('store_orders.store_id', $store_ids)
                    ->where('store_orders.order_status', '=', $status)
                    ->Where('store_orders.order_id', '=', $search)
                    ->join('product_refund', 'store_orders.order_id', '=', 'product_refund.order_id')
                    ->join('shipping_addresses', 'store_orders.shipping_address', '=', 'shipping_addresses.sh_id')
                    ->join('stores', 'store_orders.store_id', '=', 'stores.store_id')
                    ->orderBy('store_orders.created_at', 'DESC')
                    //->get()
                    ->select('store_orders.order_id', 'stores.wallet_address', 'store_orders.is_refund', 'store_orders.tracking_id', 'store_orders.is_refund', 'store_orders.buyer_id', 'store_orders.payment_status', 'store_orders.order_status', 'store_orders.shipping_address', 'store_orders.payment_method', 'store_orders.total_amount', 'store_orders.created_at', 'product_refund.product_id', 'product_refund.id', 'product_refund.quantity', 'product_refund.reason_type', 'product_refund.reason', 'product_refund.is_refund', 'product_refund.price', 'product_refund.shipping_amount', 'shipping_addresses.street', 'shipping_addresses.country', 'shipping_addresses.flat_other', 'shipping_addresses.city', 'shipping_addresses.state', 'shipping_addresses.full_name', 'shipping_addresses.user_id', 'shipping_addresses.post_code')
                    ->get()
                    ->toArray();
            } else {

                $orders = DB::table('store_orders')
                    ->whereIn('store_orders.store_id', $store_ids)
                    ->where('store_orders.order_status', '=', $status)
                    ->whereIn('store_orders.buyer_id', $user_id)
                    ->join('product_refund', 'store_orders.order_id', '=', 'product_refund.order_id')
                    ->join('shipping_addresses', 'store_orders.shipping_address', '=', 'shipping_addresses.sh_id')
                    ->join('stores', 'store_orders.store_id', '=', 'stores.store_id')
                    ->orderBy('store_orders.created_at', 'DESC')
                    //->get()
                    // ->select('store_orders.order_id', 'store_orders.is_refund', 'store_orders.tracking_id', 'store_orders.is_refund', 'store_orders.buyer_id', 'store_orders.payment_status', 'store_orders.order_status', 'store_orders.shipping_address', 'store_orders.payment_method', 'store_orders.total_amount', 'store_orders.created_at', 'product_refund.product_id', 'product_refund.id', 'product_refund.quantity', 'product_refund.reason_type','product_refund.is_refund' , 'product_refund.reason' , 'product_refund.price', 'shipping_addresses.street', 'shipping_addresses.country', 'shipping_addresses.flat_other', 'shipping_addresses.city', 'shipping_addresses.state', 'shipping_addresses.full_name', 'shipping_addresses.user_id', 'shipping_addresses.post_code')
                    ->select('store_orders.order_id', 'stores.wallet_address', 'store_orders.is_refund', 'store_orders.tracking_id', 'store_orders.is_refund', 'store_orders.buyer_id', 'store_orders.payment_status', 'store_orders.order_status', 'store_orders.shipping_address', 'store_orders.payment_method', 'store_orders.total_amount', 'store_orders.created_at', 'product_refund.product_id', 'product_refund.id', 'product_refund.quantity', 'product_refund.reason_type', 'product_refund.reason', 'product_refund.is_refund', 'product_refund.price', 'product_refund.shipping_amount', 'shipping_addresses.street', 'shipping_addresses.country', 'shipping_addresses.flat_other', 'shipping_addresses.city', 'shipping_addresses.state', 'shipping_addresses.full_name', 'shipping_addresses.user_id', 'shipping_addresses.post_code')
                    ->get()
                    ->toArray();
            }
        } else {

            $orders = DB::table('store_orders')
                ->whereIn('store_orders.store_id', $store_ids)
                ->join('product_refund', 'store_orders.order_id', '=', 'product_refund.order_id')
                ->join('shipping_addresses', 'store_orders.shipping_address', '=', 'shipping_addresses.sh_id')
                ->join('stores', 'store_orders.store_id', '=', 'stores.store_id')
                ->orderBy('store_orders.created_at', 'DESC')
                //->get()
                // ->select('store_orders.order_id', 'store_orders.tracking_id', 'store_orders.buyer_id', 'store_orders.payment_status', 'store_orders.order_status', 'store_orders.shipping_address', 'store_orders.payment_method', 'store_orders.total_amount', 'store_orders.created_at', 'product_refund.product_id', 'product_refund.quantity', 'product_refund.reason_type', 'product_refund.id', 'product_refund.is_refund' , 'product_refund.reason', 'product_refund.price', 'shipping_addresses.street', 'shipping_addresses.country', 'shipping_addresses.flat_other', 'shipping_addresses.city', 'shipping_addresses.state', 'shipping_addresses.full_name', 'shipping_addresses.user_id', 'shipping_addresses.post_code')
                ->select('store_orders.order_id', 'stores.wallet_address', 'store_orders.is_refund', 'store_orders.tracking_id', 'store_orders.is_refund', 'store_orders.buyer_id', 'store_orders.payment_status', 'store_orders.order_status', 'store_orders.shipping_address', 'store_orders.payment_method', 'store_orders.total_amount', 'store_orders.created_at', 'product_refund.product_id', 'product_refund.id', 'product_refund.quantity', 'product_refund.reason_type', 'product_refund.reason', 'product_refund.is_refund', 'product_refund.price', 'product_refund.shipping_amount', 'shipping_addresses.street', 'shipping_addresses.country', 'shipping_addresses.flat_other', 'shipping_addresses.city', 'shipping_addresses.state', 'shipping_addresses.full_name', 'shipping_addresses.user_id', 'shipping_addresses.post_code')
                ->get()
                ->toArray();
        }
        if (count($orders) > 0) {
            $order_items = array();
            foreach ($orders as $order) {
                $order_items[$order->order_id][] = $order;
            }
            return $order_items;
        }
    }

    public function get_orders_with_items_status($store_ids = array(), $input = array())
    {
        $status = '';
        if (array_key_exists('current_tab', $input)) {
            $status = $input['current_tab'];
        } else {
            $status = isset($_GET['status']) ? $_GET['status'] : '';
        }

        if (isset($input['search']) && $input['search'] != '') {
            $search = $input['search'];
            $user_id = getUserIdBy($search);
            if (count($user_id) == 0) {
                $orders = DB::table('store_orders')
                    ->whereIn('store_orders.store_id', $store_ids)
                    ->where('store_orders.order_status', '=', $status)
                    ->Where('store_orders.order_id', '=', $search)
                    ->join('order_items', 'store_orders.order_id', '=', 'order_items.order_id')
                    ->join('shipping_addresses', 'store_orders.shipping_address', '=', 'shipping_addresses.sh_id')
                    ->orderBy('store_orders.created_at', 'DESC')
                    //->get()
                    ->select('store_orders.order_id', 'store_orders.is_refund', 'store_orders.tracking_id', 'store_orders.is_refund', 'store_orders.buyer_id', 'store_orders.payment_status', 'store_orders.order_status', 'store_orders.shipping_address', 'store_orders.payment_method', 'store_orders.total_amount', 'store_orders.created_at', 'order_items.item_id', 'order_items.quantity', 'order_items.variation_id', 'order_items.order_note', 'order_items.price', 'shipping_addresses.street', 'shipping_addresses.country', 'shipping_addresses.flat_other', 'shipping_addresses.city', 'shipping_addresses.state', 'shipping_addresses.full_name', 'shipping_addresses.user_id', 'shipping_addresses.post_code')
                    ->get()
                    ->toArray();
            } else {
                $orders = DB::table('store_orders')
                    ->whereIn('store_orders.store_id', $store_ids)
                    ->where('store_orders.order_status', '=', $status)
                    ->whereIn('store_orders.buyer_id', $user_id)
                    ->join('order_items', 'store_orders.order_id', '=', 'order_items.order_id')
                    ->join('shipping_addresses', 'store_orders.shipping_address', '=', 'shipping_addresses.sh_id')
                    ->orderBy('store_orders.created_at', 'DESC')
                    //->get()
                    ->select('store_orders.order_id',  'store_orders.is_refund', 'store_orders.tracking_id', 'store_orders.is_refund', 'store_orders.buyer_id', 'store_orders.payment_status', 'store_orders.order_status', 'store_orders.shipping_address', 'store_orders.payment_method', 'store_orders.total_amount', 'store_orders.created_at', 'order_items.item_id', 'order_items.quantity', 'order_items.variation_id', 'order_items.order_note', 'order_items.price', 'shipping_addresses.street', 'shipping_addresses.country', 'shipping_addresses.flat_other', 'shipping_addresses.city', 'shipping_addresses.state', 'shipping_addresses.full_name', 'shipping_addresses.user_id', 'shipping_addresses.post_code')
                    ->get()
                    ->toArray();
            }
        } else {
            $orders = DB::table('store_orders')
                ->whereIn('store_orders.store_id', $store_ids)
                ->where('store_orders.order_status', '=', $status)
                ->join('order_items', 'store_orders.order_id', '=', 'order_items.order_id')
                ->join('shipping_addresses', 'store_orders.shipping_address', '=', 'shipping_addresses.sh_id')
                ->orderBy('store_orders.created_at', 'DESC')
                //->get()
                ->select('store_orders.order_id', 'store_orders.tracking_id', 'store_orders.buyer_id', 'store_orders.payment_status', 'store_orders.order_status', 'store_orders.shipping_address', 'store_orders.payment_method', 'store_orders.total_amount', 'store_orders.created_at', 'order_items.item_id', 'order_items.quantity', 'order_items.variation_id', 'order_items.order_note', 'order_items.price', 'shipping_addresses.street', 'shipping_addresses.country', 'shipping_addresses.flat_other', 'shipping_addresses.city', 'shipping_addresses.state', 'shipping_addresses.full_name', 'shipping_addresses.user_id', 'shipping_addresses.post_code')
                ->get()
                ->toArray();
        }
        if (count($orders) > 0) {
            $order_items = array();
            foreach ($orders as $order) {
                $order_items[$order->order_id][] = $order;
            }
            return $order_items;
        }
    }

    public function update_order_status($change_status = array())
    {
        // dd($change_status);
      
        $client = new \EasyPost\EasyPostClient(env('EASYPOST_API_KEY'));
        $val = false;
        $paid = false;
        $mesage= '';
        $redund_status = '';
        if (count($change_status) > 1) {

            $order_id = $change_status['order_id'];
            $orderItems = getOrderItemsByOrderId($order_id);
            $productArray = [];
            foreach ($orderItems as $key => $value) {
                $product_id = $value->item_id;
                $productData = getProductIdSpecificColums($product_id);
                $variableData = null;
                if($productData->product_type== 'variant'){

                    $variableData = getvariableDataByProduct_id($product_id);
                }

                $productArray[] = [
                    'title' => $productData->title,
                    'slug' => $productData->slug,
                    'feature_image' => $productData->feature_image,
                    'product_type' => $productData->product_type,
                    'price' => $value->price,
                    'sold_price' => $value->sold_price,
                    'price_total' => $value->total,
                    'quantity' => $value->quantity,
                    'variable_image' => $variableData ?  $variableData->product_variation_image: '',
                    'variation_id' => $variableData ?  $variableData->variation_id: '',
                    'customizations' => $value->customizations ?? null,

                ];
            }
            
            $order_information = DB::table('store_orders')->where('order_id', $order_id)->select('total_amount', 'payment_status', 'store_id', 'buyer_id')->get()->toArray();
            $buyer_id = $order_information[0]->buyer_id;
            if ($change_status['status'] == 'cancelled' && $change_status['payment_method'] == 'crypto') {

               
                $Stripeobj = new StripeController();
                if ($Stripeobj->refundBack($order_id)) {
                    $mesage = 'Refund was successfull';
                    $redund_status = '1';

                    $query_update = DB::table('store_orders')->where('order_id', $order_id)->update(['order_status' => 'refunded']);
                    $query_update = DB::table('store_orders')->where('order_id', $order_id)->update(['is_refund' => $change_status['is_refund']]);

                    $userData = getUserById($buyer_id);
                    if (!empty($userData)) {
                        $name  = $userData->name;
                        $email = $userData->email;
                    } else {
                        $name  = "";
                        $email = "noman@mindblazetech.com";
                    }


                    $description = '';
                    $html = view('mail.order-update-vendor-shipped-to-cancelled', ['userData' => $userData, 'status' => $change_status['status'], 'orderItems' => $productArray]);
                    $subject = 'Update on Your Order NO ' . $order_id;
                    $toEmail = $email;
                    $toCompletename = $name;
                    $params = [
                        'from'    => 'MallShark  ' . env('SUPPORT_EMAIL'),
                        'to'      => $toEmail,
                        'subject' => $subject,
                        'html'    => "$html",
                    ];
                    $results = $this->mailgun->SendMailgunMail($params);
                    addNotifications($buyer_id, getCurrentUserId(), $subject, $description, URL::to('/buyer-orders'));
                } else {
                    $mesage = 'Refund is not successfull please contact to super admin';
                }
            }
            if ($change_status['status'] == 'refunded' && $change_status['payment_method'] == 'crypto') {
                

                $Stripeobj = new StripeController();
                if ($Stripeobj->refundBack($order_id)) {
                    $mesage = 'Refund was successfull';
                    $redund_status = '1';

                    $query_update = DB::table('store_orders')->where('order_id', $order_id)->update(['order_status' => 'refunded']);
                    $query_update = DB::table('store_orders')->where('order_id', $order_id)->update(['is_refund' => $change_status['is_refund']]);

                    $userData = getUserById($buyer_id);
                    if (!empty($userData)) {
                        $name  = $userData->name;
                        $email = $userData->email;
                    } else {
                        $name  = "";
                        $email = "noman@mindblazetech.com";
                    }


                    $description = '';
                    $html = view('mail.order-update-vendor-shipped-to-cancelled', ['userData' => $userData, 'status' => $change_status['status'], 'orderItems' => $productArray]);
                    $subject = 'Update on Your Order NO ' . $order_id;
                    $toEmail = $email;
                    $toCompletename = $name;
                    $params = [
                        'from'    => 'MallShark  ' . env('SUPPORT_EMAIL'),
                        'to'      => $toEmail,
                        'subject' => $subject,
                        'html'    => "$html",
                    ];
                    $results = $this->mailgun->SendMailgunMail($params);
                    addNotifications($buyer_id, getCurrentUserId(), $subject, $description, URL::to('/buyer-orders'));
                } else {
                    $mesage = 'Refund is not successfull please contact to super admin';
                }
            }
            if ($change_status['status'] == 'cancelled' && $change_status['payment_method'] == 'stripe' && $change_status['shipped_cancel'] == '0') {
                $order_items_array = OrderItems::where('order_id',$order_id)->get();
                // dd($order_items_array);
                foreach($order_items_array as $single_order_item){
                    if(!is_null($single_order_item)){
                        if($single_order_item->variation_id == 0 || $single_order_item->variation_id == null){
                            $product_id = $single_order_item->item_id;
                            $product_quantity = $single_order_item->quantity;
                            // dd($product_quantity);
                            $product_stock_update = Product::find($product_id);
                            $old_Stock = $product_stock_update->current_stock;
                            // dd($old_Stock);
                            $updated_stock = $old_Stock+$product_quantity;
                            // dd($updated_stock);
                            $product_stock_update->current_stock = $updated_stock;
                            
                            $product_stock_update->save();
                        }else{
                            $variation_id = $single_order_item->variation_id;
                            $product_quantity = $single_order_item->quantity;
                            // dd($product_quantity);
                            $product_stock_update = ProductVariation::find($variation_id);
                            $old_Stock = $product_stock_update->current_stock;
                            // dd($old_Stock);
                            $updated_stock = $old_Stock+$product_quantity;
                            // dd($updated_stock);
                            $product_stock_update->current_stock = $updated_stock;
                            
                            $product_stock_update->save();
                        }
                        
                    }
                }
               
                $Stripeobj = new StripeController();
                // dd($Stripeobj->refundBack($order_id));
                if ($Stripeobj->refundBack($order_id)) {
                    $mesage = 'Refund was successfull';
                    $redund_status = '1';

                    $query_update = DB::table('store_orders')->where('order_id', $order_id)->update(['order_status' => 'refunded']);
                    $query_update = DB::table('store_orders')->where('order_id', $order_id)->update(['is_refund' => $change_status['is_refund']]);

                   
                    $usershipping = getShippingAddressbyUserId($buyer_id);
                    $userData = getUserById($buyer_id);
                    if (!empty($userData)) {
                        $name  = $userData->name;
                        $email = $userData->email;
                    } else {
                        $name  = "";
                        $email = "noman@mindblazetech.com";
                    }


                    $description = ''; 
                    $html = view('mail.order-update-vendor-cancel-to-client', ['userData' => $userData, "shippingAddress" => $usershipping, 'status' => $change_status['status'], 'orderItems' => $productArray, 'orderId' => $order_id]);
                    $subject = Constants::CANCEL_ORDER_BUYER_SUBJECT.$order_id;
                    $toEmail = $email;
                    $toCompletename = $name;
                    $params = [
                        'from'    => 'MallShark  ' . env('SUPPORT_EMAIL'),
                        'to'      => $toEmail,
                        'subject' => $subject,
                        'html'    => "$html",
                    ];
                    $results = $this->mailgun->SendMailgunMail($params);
                    if(isset($buyer_id) && !is_null($buyer_id)){
                        $userData = User::with('device_token')->findOrFail($buyer_id);
                        $device_tokens = DeviceToken::where('user_id',$userData->user_id)->where('app_flag','buyer')->get();
                        if ($device_tokens) {
                            $tokens = [];
                            if(isset($device_tokens)&& count($device_tokens) > 0){

                            foreach ($device_tokens as $device_token_obj) {
                                // dd($device_token_obj->fcm_token);
                            
                            
                            $subject = $subject;
                            // foreach($tokens as $token){
                                $notification = FCMService::send(
                                    $device_token_obj->fcm_token,
                                    [
                                        'title' => $subject,
                                    ]
                                );
                            }
                        }
                            addNotifications($buyer_id,getCurrentUserId(), $subject, $description,URL::to('new-buyer-orders-detail/'.$order_id),'buyer_order_details',$order_id,'buyer');
        
                        }
                    }
                    
                } else {
                    $mesage = 'Refund is not successfull please contact to super admin';
                    
                }
                
            }

            if ($change_status['status'] == 'cancelled' && $change_status['payment_method'] == 'stripe' && $change_status['shipped_cancel'] == '1') {
                $order_items_array = OrderItems::where('order_id',$order_id)->get();
                // dd($order_items_array);
                foreach($order_items_array as $single_order_item){
                    if(!is_null($single_order_item)){
                        if($single_order_item->variation_id == 0 || $single_order_item->variation_id == null){
                            $product_id = $single_order_item->item_id;
                            $product_quantity = $single_order_item->quantity;
                            // dd($product_quantity);
                            $product_stock_update = Product::find($product_id);
                            $old_Stock = $product_stock_update->current_stock;
                            // dd($old_Stock);
                            $updated_stock = $old_Stock+$product_quantity;
                            // dd($updated_stock);
                            $product_stock_update->current_stock = $updated_stock;
                            
                            $product_stock_update->save();
                        }else{
                            $variation_id = $single_order_item->variation_id;
                            $product_quantity = $single_order_item->quantity;
                            // dd($product_quantity);
                            $product_stock_update = ProductVariation::find($variation_id);
                            $old_Stock = $product_stock_update->current_stock;
                            // dd($old_Stock);
                            $updated_stock = $old_Stock+$product_quantity;
                            // dd($updated_stock);
                            $product_stock_update->current_stock = $updated_stock;
                            
                            $product_stock_update->save();
                        }
                        
                    }
                }
               
                $Stripeobj = new StripeController();
                if ($Stripeobj->refundBack($order_id)) {
                    $mesage = 'Refund was successfull';
                    $redund_status = '1';

                    $query_update = DB::table('store_orders')->where('order_id', $order_id)->update(['order_status' => 'refunded']);
                    $query_update = DB::table('store_orders')->where('order_id', $order_id)->update(['is_refund' => $change_status['is_refund']]);


                    $usershipping = getShippingAddressbyUserId($buyer_id);
                    $userData = getUserById($buyer_id);
                    if (!empty($userData)) {
                        $name  = $userData->name;
                        $email = $userData->email;
                    } else {
                        $name  = "";
                        $email = "noman@mindblazetech.com";
                    }


                    $description = '';
                    $html = view('mail.order-update-vendor-shipped-to-cancelled', ['userData' => $userData, "shippingAddress" => $usershipping, 'status' => $change_status['status'], 'orderItems' => $productArray, 'orderId' => $order_id]);
                    $subject = Constants::CANCEL_ORDER_BUYER_SUBJECT.$order_id;
                    $toEmail = $email;
                    $toCompletename = $name;
                    $params = [
                        'from'    => 'MallShark  ' . env('SUPPORT_EMAIL'),
                        'to'      => $toEmail,
                        'subject' => $subject,
                        'html'    => "$html",
                    ];
                    $results = $this->mailgun->SendMailgunMail($params);
                    if(isset($buyer_id) && !is_null($buyer_id)){
                        $userData = User::with('device_token')->findOrFail($buyer_id);
                        $device_tokens = DeviceToken::where('user_id',$userData->user_id)->where('app_flag','buyer')->get();
                        if ($device_tokens) {
                            $tokens = [];
                            if(isset($device_tokens)&& count($device_tokens) > 0){

                            foreach ($device_tokens as $device_token_obj) {
                                // dd($device_token_obj->fcm_token);
                            
                            
                            $subject = $subject;
                            // foreach($tokens as $token){
                                $notification = FCMService::send(
                                    $device_token_obj->fcm_token,
                                    [
                                        'title' => $subject,
                                    ]
                                );
                            }
                        }
                            addNotifications($buyer_id,getCurrentUserId(), $subject, $description,URL::to('new-buyer-orders-detail/'.$order_id),'buyer_order_details',$order_id,'buyer');
        
                        }
                    }
                } else {
                    $mesage = 'Refund is not successfull please contact to super admin';
                }
            }
            if ($change_status['status'] == 'refunded' && $change_status['payment_method'] == 'stripe') {
               

                $Stripeobj = new StripeController();
                if ($Stripeobj->refundBack($order_id)) {
                    $mesage = 'Refund was successfull';
                    $redund_status = '1';
                } else {
                    $mesage = 'Refund is not successfull please contact to super admin';
                }

                $query_update = DB::table('store_orders')->where('order_id', $order_id)->update(['order_status' => 'refunded']);
                $query_update = DB::table('store_orders')->where('order_id', $order_id)->update(['is_refund' => $change_status['is_refund']]);
            }



            if ($this->current_user_can_update_order($order_id)) {
                
                if (isset($change_status["ship_id"])) {
                    $ship_id = $change_status["ship_id"];
                    $ship_id = '[' . $ship_id . ']';
                    $shipping_ids = json_decode($ship_id, true);

                   
                    $items_Update_Array = array();
                    
                    if ($change_status['status'] == 'shipped') {
                        foreach ($shipping_ids as $key => $item) {

                            foreach ($item as $key => $value) {


                                $easypost_ship_id = $value['shipping_id'];
                                $order_item_id = $value['order_item_id'];
                                $shipping_service = $key;
                                // echo 'Data: <pre>' .print_r($shipping_service,true). '</pre>'; die();
                                $shipping_method = $value['payment_method'];
                                if ($shipping_method == 'calculated' && isset($easypost_ship_id) && $easypost_ship_id != '') {
                                    try{

                                        $shipment = $client->shipment->retrieve($easypost_ship_id);
                                        // dd($shipment);
                                    }catch(\Exception $e){
                                        // dd($e);

                                    }
                                    //  echo 'Data: <pre>' .print_r($shipment,true). '</pre>'; die();
                                    for ($i = 0; $i < count($shipment->rates); $i++) {
                                        //match our rate to the service
                                        if ($shipment->rates[$i]->service == $shipping_service) {
                                            $flagSelected = true;
                                            $selectedRate = $shipment->rates[$i];
                                            try{

                                                $boughtShipment = $client->shipment->buy($shipment->id, $selectedRate);
                                            }catch(\Exception $e){
                                                // dd($e);
                                            }
                                            // dd($boughtShipment);

                                            $shipmentWithLabel =   $client->shipment->label($shipment->id, ["file_format" => "pdf"]);
                                            $insertedData['easyPostLabelPNG'] = $shipmentWithLabel->postage_label->label_url;
                                            $insertedData['easyPostLabelPDF'] = $shipmentWithLabel->postage_label->label_pdf_url;
                                            $insertedData['easyPostLabelRateId'] = $shipmentWithLabel->selected_rate->id;

                                            $insertedData['easyPostLabelCarrier'] = $shipmentWithLabel->selected_rate->carrier;
                                            $insertedData['easyPostLabelService'] = $shipmentWithLabel->selected_rate->service;
                                            $insertedData['shipmentStatus'] = $shipmentWithLabel->tracker->status;
                                            $insertedData['estDeliveryDate'] = $shipmentWithLabel->tracker->est_delivery_date;
                                            $insertedData['trackingCode'] = $shipmentWithLabel->tracking_code;

                                            $insertedData['shipmentCost'] = number_format($selectedRate->rate, 2, '.', '');
                                            $insertedData['easyPostShipmentId'] = $shipment->id;
                                            $insertedData['easyPostShipmentTrackingUrl'] = $shipmentWithLabel->tracker->public_url;
                                            $insertedData['easyPostLabelSize'] = $shipmentWithLabel->postage_label->label_size;

                                            $tracking_number = $insertedData['trackingCode'];
                                            $randomFileName = $tracking_number . ".png";
                                            $randomFileNamePDF = $tracking_number . ".pdf";
                                            $downloadResult = file_get_contents($shipmentWithLabel->postage_label->label_url);
                                            $downloadResultPDF = file_get_contents($shipmentWithLabel->postage_label->label_pdf_url);

                                            $logs_dir = 'downloads/labels/';
                                            if (!is_dir($logs_dir)) {
                                                mkdir($logs_dir, 0777, true);
                                            }
                                            File::put($logs_dir . $randomFileName, $downloadResult);
                                            File::put($logs_dir . $randomFileNamePDF, $downloadResultPDF);

                                            $items_Update_Array = array(
                                                'rate_id' => $insertedData['easyPostLabelRateId'], 'label_url' => $insertedData['easyPostLabelPNG'],
                                                'lable_pdf_url' => $insertedData['easyPostLabelPDF'], 'tracking_id' => $insertedData['easyPostShipmentTrackingUrl'],
                                                'tracking_code' => $tracking_number, 'shipment_object' => json_encode($insertedData),
                                            );
                                            $this->order_items->where('order_item_id', '=', $order_item_id)->update($items_Update_Array);
                                        }
                                    }
                                }
                            }
                        }
                        $currentDateTime = Carbon::now();
                        
                        $query_update = DB::table('store_orders')->where('order_id', $order_id)->update(['order_status' => $change_status['status'], 'shipped_date' => $currentDateTime]);
                        $mesage = Constants::SHIPPED_ORDER_BUYER_DESCRIPTION;
                        $subject = Constants::SHIPPED_ORDER_BUYER_SUBJECT.$order_id;
                        if(isset($buyer_id) && !is_null($buyer_id)){
                            $userData = User::with('device_token')->findOrFail($buyer_id);
                            $device_tokens = DeviceToken::where('user_id',$userData->user_id)->where('app_flag','buyer')->get();
                            if ($device_tokens) {
                                $tokens = [];
                                if(isset($device_tokens)&& count($device_tokens) > 0){
    
                                foreach ($device_tokens as $device_token_obj) {
                                    // dd($device_token_obj->fcm_token);
                                
                                
                                $subject = $subject;
                                // foreach($tokens as $token){
                                    $notification = FCMService::send(
                                        $device_token_obj->fcm_token,
                                        [
                                            'title' => $subject,
                                        ]
                                    );
                                }
                            }
                            addNotifications($buyer_id, getCurrentUserId(), $subject, '',URL::to('new-buyer-orders-detail/'.$order_id),'buyer_order_details',$order_id,'buyer');
            
                            }
                        }
                        
                    }
                    $order_information = DB::table('store_orders')->where('order_id', $order_id)->select('total_amount', 'payment_status', 'store_id', 'buyer_id')->get()->toArray();
                $buyer_id = $order_information[0]->buyer_id;
                $val = true;
                $userData = getUserById($buyer_id);
                $usershipping = getShippingAddressbyUserId($buyer_id);
                if (!empty($userData)) {
                    $name  = $userData->name;
                    $email = $userData->email;
                } else {
                    $name  = "";
                    $email = "noman@mindblazetech.com";
                }


                $description = '';
                // $this->mandrill->sendMandrillEmail($subject, $templateSlug, $toEmail, $toCompletename, $varsArray);
                $html = view('mail.shipped-update-client', ['userData' => $userData, "shippingAddress" => $usershipping, 'status' => $change_status['status'], 'orderItems' => $productArray]);
                $subject = 'Your MallShark Order Has Been Shipped!';
                $toEmail = $email;
                $toCompletename = $name;
                $params = [
                    'from'    => 'MallShark  ' . env('SUPPORT_EMAIL'),
                    'to'      => $toEmail,
                    'subject' => $subject,
                    'html'    => "$html",
                ];
                $results = $this->mailgun->SendMailgunMail($params);
                }
                
                
            }
        }
        return [
            'val' => $val,
            'message' => $mesage,
            'redund_status' => $redund_status,
        ];
    }

    public function update_orderdetail_status($change_status = array())
    {
      
        $client = new \EasyPost\EasyPostClient(env('EASYPOST_API_KEY'));
        $val = false;
        $paid = false;
        $mesage= '';
        if (count($change_status) > 1) {

            $order_id = $change_status['order_id'];
            $orderItems = getOrderItemsByOrderId($order_id);
            $productArray = [];
            foreach ($orderItems as $key => $value) {
                $product_id = $value->item_id;
                $productData = getProductIdSpecificColums($product_id);
                $variableData = null;
                if($productData->product_type== 'variant'){

                    $variableData = getvariableDataByProduct_id($product_id);
                }

                $productArray[] = [
                    'title' => $productData->title,
                    'slug' => $productData->slug,
                    'feature_image' => $productData->feature_image,
                    'product_type' => $productData->product_type,
                    'price_total' => $value->total,
                    'quantity' => $value->quantity,
                    'variable_image' => $variableData ?  $variableData->product_variation_image: '',
                    'price' => $value->price,
                    'sold_price' => $value->sold_price

                ];
            }
            $order_information = DB::table('store_orders')->where('order_id', $order_id)->select('total_amount', 'payment_status', 'store_id', 'buyer_id')->get()->toArray();
            $buyer_id = $order_information[0]->buyer_id;
            if ($change_status['status'] == 'cancelled' && $change_status['payment_method'] == 'crypto') {


                $Stripeobj = new StripeController();
                if ($Stripeobj->refundBack($order_id)) {
                    $mesage = 'Refund was successfull';

                    $query_update = DB::table('store_orders')->where('order_id', $order_id)->update(['order_status' => 'refunded']);
                    $query_update = DB::table('store_orders')->where('order_id', $order_id)->update(['is_refund' => $change_status['is_refund']]);

                    $userData = getUserById($buyer_id);
                    if (!empty($userData)) {
                        $name  = $userData->name;
                        $email = $userData->email;
                    } else {
                        $name  = "";
                        $email = "noman@mindblazetech.com";
                    }


                    $description = '';
                    $html = view('mail.order-update-vendor-shipped-to-cancelled', ['userData' => $userData, 'status' => $change_status['status'], 'orderItems' => $productArray]);
                    $subject = 'Update on Your Order NO ' . $order_id;
                    $toEmail = $email;
                    $toCompletename = $name;
                    $params = [
                        'from'    => 'MallShark  ' . env('SUPPORT_EMAIL'),
                        'to'      => $toEmail,
                        'subject' => $subject,
                        'html'    => "$html",
                    ];
                    $results = $this->mailgun->SendMailgunMail($params);
                    addNotifications($buyer_id, getCurrentUserId(), $subject, $description, URL::to('/buyer-orders'));
                } else {
                    $mesage = 'Refund is not successfull please contact to super admin';
                }
            }
            if ($change_status['status'] == 'refunded' && $change_status['payment_method'] == 'crypto') {


                $Stripeobj = new StripeController();
                if ($Stripeobj->refundBack($order_id)) {
                    $mesage = 'Refund was successfull';

                    $query_update = DB::table('store_orders')->where('order_id', $order_id)->update(['order_status' => 'refunded']);
                    $query_update = DB::table('store_orders')->where('order_id', $order_id)->update(['is_refund' => $change_status['is_refund']]);

                    $userData = getUserById($buyer_id);
                    if (!empty($userData)) {
                        $name  = $userData->name;
                        $email = $userData->email;
                    } else {
                        $name  = "";
                        $email = "noman@mindblazetech.com";
                    }


                    $description = '';
                    $html = view('mail.order-update-vendor-shipped-to-cancelled', ['userData' => $userData, 'status' => $change_status['status'], 'orderItems' => $productArray]);
                    $subject = 'Update on Your Order NO ' . $order_id;
                    $toEmail = $email;
                    $toCompletename = $name;
                    $params = [
                        'from'    => 'MallShark  ' . env('SUPPORT_EMAIL'),
                        'to'      => $toEmail,
                        'subject' => $subject,
                        'html'    => "$html",
                    ];
                    $results = $this->mailgun->SendMailgunMail($params);
                    addNotifications($buyer_id, getCurrentUserId(), $subject, $description, URL::to('/buyer-orders'));
                } else {
                    $mesage = 'Refund is not successfull please contact to super admin';
                }
            }
            if ($change_status['status'] == 'cancelled'  && $change_status['shipped_cancel'] == '0') {
                $order_items_array = OrderItems::where('order_id',$order_id)->get();
                // dd($order_items_array);
                foreach($order_items_array as $single_order_item){
                    if(!is_null($single_order_item)){
                        if($single_order_item->variation_id == 0 || $single_order_item->variation_id == null){
                            $product_id = $single_order_item->item_id;
                            $product_quantity = $single_order_item->quantity;
                            // dd($product_quantity);
                            $product_stock_update = Product::find($product_id);
                            $old_Stock = $product_stock_update->current_stock;
                            // dd($old_Stock);
                            $updated_stock = $old_Stock+$product_quantity;
                            // dd($updated_stock);
                            $product_stock_update->current_stock = $updated_stock;
                            
                            $product_stock_update->save();
                        }else{
                            $variation_id = $single_order_item->variation_id;
                            $product_quantity = $single_order_item->quantity;
                            // dd($product_quantity);
                            $product_stock_update = ProductVariation::find($variation_id);
                            $old_Stock = $product_stock_update->current_stock;
                            // dd($old_Stock);
                            $updated_stock = $old_Stock+$product_quantity;
                            // dd($updated_stock);
                            $product_stock_update->current_stock = $updated_stock;
                            
                            $product_stock_update->save();
                        }
                        
                    }
                }
            // if ($change_status['status'] == 'cancelled' && $change_status['payment_method'] == 'stripe' && $change_status['shipped_cancel'] == '0') {
               
                $Stripeobj = new StripeController();
                // dd($Stripeobj->refundBack($order_id));
                if ($Stripeobj->refundBack($order_id)) {
                    $mesage = 'Refund was successfull';

                    $query_update = DB::table('store_orders')->where('order_id', $order_id)->update(['order_status' => 'refunded']);
                    $query_update = DB::table('store_orders')->where('order_id', $order_id)->update(['is_refund' => $change_status['is_refund']]);
                    
                   
                    $usershipping = getShippingAddressbyUserId($buyer_id);
                    $userData = getUserById($buyer_id);
                    if (!empty($userData)) {
                        $name  = $userData->name;
                        $email = $userData->email;
                    } else {
                        $name  = "";
                        $email = "noman@mindblazetech.com";
                    }


                    $description = ''; 
                    $html = view('mail.order-update-vendor-cancel-to-client', ['userData' => $userData, "shippingAddress" => $usershipping, 'status' => $change_status['status'], 'orderItems' => $productArray, 'orderId' => $order_id]);
                    $subject = Constants::CANCEL_ORDER_BUYER_SUBJECT.$order_id;
                    $toEmail = $email;
                    $toCompletename = $name;
                    $params = [
                        'from'    => 'MallShark  ' . env('SUPPORT_EMAIL'),
                        'to'      => $toEmail,
                        'subject' => $subject,
                        'html'    => "$html",
                    ];
                    $results = $this->mailgun->SendMailgunMail($params);
                    if(isset($buyer_id) && !is_null($buyer_id)){
                        $userData = User::with('device_token')->findOrFail($buyer_id);
                        $device_tokens = DeviceToken::where('user_id',$userData->user_id)->where('app_flag','buyer')->get();
                        if ($device_tokens) {
                            $tokens = [];
                            if(isset($device_tokens)&& count($device_tokens) > 0){

                            foreach ($device_tokens as $device_token_obj) {
                                // dd($device_token_obj->fcm_token);
                            
                            
                            $subject = $subject;
                            
                            // foreach($tokens as $token){
                                $notification = FCMService::send(
                                    $device_token_obj->fcm_token,
                                    [
                                        'title' => $subject,
                                    ]
                                );
                            }
                        }
                            addNotifications($buyer_id,getCurrentUserId(), $subject, $description,URL::to('new-buyer-orders-detail/'.$order_id),'buyer_order_details',$order_id,'buyer');
        
                        }
                    }
                } else {
                    $mesage = 'Refund is not successfull please contact to super admin';
                    
                }
                
            }

            if ($change_status['status'] == 'cancelled' && $change_status['payment_method'] == 'stripe' && $change_status['shipped_cancel'] == '1') {

                $order_items_array = OrderItems::where('order_id',$order_id)->get();
                // dd($order_items_array);
                foreach($order_items_array as $single_order_item){
                    if(!is_null($single_order_item)){
                        if($single_order_item->variation_id == 0 || $single_order_item->variation_id == null){
                            $product_id = $single_order_item->item_id;
                            $product_quantity = $single_order_item->quantity;
                            // dd($product_quantity);
                            $product_stock_update = Product::find($product_id);
                            $old_Stock = $product_stock_update->current_stock;
                            // dd($old_Stock);
                            $updated_stock = $old_Stock+$product_quantity;
                            // dd($updated_stock);
                            $product_stock_update->current_stock = $updated_stock;
                            
                            $product_stock_update->save();
                        }else{
                            $variation_id = $single_order_item->variation_id;
                            $product_quantity = $single_order_item->quantity;
                            // dd($product_quantity);
                            $product_stock_update = ProductVariation::find($variation_id);
                            $old_Stock = $product_stock_update->current_stock;
                            // dd($old_Stock);
                            $updated_stock = $old_Stock+$product_quantity;
                            // dd($updated_stock);
                            $product_stock_update->current_stock = $updated_stock;
                            
                            $product_stock_update->save();
                        }
                        
                    }
                }
                   
                $Stripeobj = new StripeController();
                if ($Stripeobj->refundBack($order_id)) {
                    $mesage = 'Refund was successfull';

                    $query_update = DB::table('store_orders')->where('order_id', $order_id)->update(['order_status' => 'refunded']);
                    $query_update = DB::table('store_orders')->where('order_id', $order_id)->update(['is_refund' => $change_status['is_refund']]);


                    $usershipping = getShippingAddressbyUserId($buyer_id);
                    $userData = getUserById($buyer_id);
                    if (!empty($userData)) {
                        $name  = $userData->name;
                        $email = $userData->email;
                    } else {
                        $name  = "";
                        $email = "noman@mindblazetech.com";
                    }


                    $description = '';
                    $html = view('mail.order-update-vendor-shipped-to-cancelled', ['userData' => $userData, "shippingAddress" => $usershipping, 'status' => $change_status['status'], 'orderItems' => $productArray, 'orderId' => $order_id]);
                    $subject = Constants::CANCEL_ORDER_BUYER_SUBJECT.$order_id;
                    $toEmail = $email;
                    $toCompletename = $name;
                    $params = [
                        'from'    => 'MallShark  ' . env('SUPPORT_EMAIL'),
                        'to'      => $toEmail,
                        'subject' => $subject,
                        'html'    => "$html",
                    ];
                    $results = $this->mailgun->SendMailgunMail($params);
                    if(isset($buyer_id) && !is_null($buyer_id)){
                        $userData = User::with('device_token')->findOrFail($buyer_id);
                        $device_tokens = DeviceToken::where('user_id',$userData->user_id)->where('app_flag','buyer')->get();
                        if ($device_tokens) {
                            $tokens = [];
                            if(isset($device_tokens)&& count($device_tokens) > 0){

                            foreach ($device_tokens as $device_token_obj) {
                                // dd($device_token_obj->fcm_token);
                            
                            
                            $subject = $subject;
                            // foreach($tokens as $token){
                                $notification = FCMService::send(
                                    $device_token_obj->fcm_token,
                                    [
                                        'title' => $subject,
                                    ]
                                );
                            }
                        }
                            addNotifications($buyer_id,getCurrentUserId(), $subject, $description, URL::to('new-buyer-orders-detail/'.$order_id),'buyer_order_details',$order_id,'buyer');
        
                        }
                    }
                } else {
                    $mesage = 'Refund is not successfull please contact to super admin';
                }
            }
            if ($change_status['status'] == 'refunded' && $change_status['payment_method'] == 'stripe') {


                $Stripeobj = new StripeController();
                if ($Stripeobj->refundBack($order_id)) {
                    $mesage = 'Refund was successfull';
                } else {
                    $mesage = 'Refund is not successfull please contact to super admin';
                }

                $query_update = DB::table('store_orders')->where('order_id', $order_id)->update(['order_status' => 'refunded']);
                $query_update = DB::table('store_orders')->where('order_id', $order_id)->update(['is_refund' => $change_status['is_refund']]);
            }



            if ($this->current_user_can_update_order($order_id)) {
                
                if (isset($change_status["ship_id"])) {
                    // $ship_id = $change_status["ship_id"];
                    // $ship_id = '[' . $ship_id . ']';
                    // $shipping_ids = json_decode($ship_id, true);
                   // $shipping_ids = json_decode($change_status["ship_id"], true);
                    
                    $shipping_ids = $change_status["ship_id"];
                    // dd($shipping_ids);
                   
                    $items_Update_Array = array();
                    
                    if ($change_status['status'] == 'shipped') {
                        foreach ($shipping_ids as $key => $item) {
                            $item = json_decode($item, true);
                          
                            foreach ($item as $key => $value) {


                                $easypost_ship_id = $value['shipping_id'];
                                $order_item_id = $value['order_item_id'];
                                $shipping_service = $key;
                                // echo 'Data: <pre>' .print_r($shipping_service,true). '</pre>'; die();
                                $shipping_method = $value['payment_method'];
                                if ($shipping_method == 'calculated' && isset($easypost_ship_id) && $easypost_ship_id != '') {

                                    $shipment = $client->shipment->retrieve($easypost_ship_id);
                                    //  echo 'Data: <pre>' .print_r($shipment,true). '</pre>'; die();
                                    for ($i = 0; $i < count($shipment->rates); $i++) {
                                        //match our rate to the service
                                        if ($shipment->rates[$i]->service == $shipping_service) {
                                            $flagSelected = true;
                                            $selectedRate = $shipment->rates[$i];
                                            $boughtShipment = $client->shipment->buy($shipment->id, $selectedRate);

                                            $shipmentWithLabel =   $client->shipment->label($shipment->id, ["file_format" => "pdf"]);
                                            // dd($shipmentWithLabel);
                                            $insertedData['easyPostLabelPNG'] = $shipmentWithLabel->postage_label->label_url;
                                            $insertedData['easyPostLabelPDF'] = $shipmentWithLabel->postage_label->label_pdf_url;
                                            $insertedData['easyPostLabelRateId'] = $shipmentWithLabel->selected_rate->id;

                                            $insertedData['easyPostLabelCarrier'] = $shipmentWithLabel->selected_rate->carrier;
                                            $insertedData['easyPostLabelService'] = $shipmentWithLabel->selected_rate->service;
                                            $insertedData['shipmentStatus'] = $shipmentWithLabel->tracker->status;
                                            $insertedData['estDeliveryDate'] = $shipmentWithLabel->tracker->est_delivery_date;
                                            $insertedData['trackingCode'] = $shipmentWithLabel->tracking_code;

                                            $insertedData['shipmentCost'] = number_format($selectedRate->rate, 2, '.', '');
                                            $insertedData['easyPostShipmentId'] = $shipment->id;
                                            $insertedData['easyPostShipmentTrackingUrl'] = $shipmentWithLabel->tracker->public_url;
                                            $insertedData['easyPostLabelSize'] = $shipmentWithLabel->postage_label->label_size;

                                            $tracking_number = $insertedData['trackingCode'];
                                            $randomFileName = $tracking_number . ".png";
                                            $randomFileNamePDF = $tracking_number . ".pdf";
                                            $downloadResult = file_get_contents($shipmentWithLabel->postage_label->label_url);
                                            $downloadResultPDF = file_get_contents($shipmentWithLabel->postage_label->label_pdf_url);

                                            $logs_dir = 'downloads/labels/';
                                            if (!is_dir($logs_dir)) {
                                                mkdir($logs_dir, 0777, true);
                                            }
                                            File::put($logs_dir . $randomFileName, $downloadResult);
                                            File::put($logs_dir . $randomFileNamePDF, $downloadResultPDF);

                                            $items_Update_Array = array(
                                                'rate_id' => $insertedData['easyPostLabelRateId'], 'label_url' => $insertedData['easyPostLabelPNG'],
                                                'lable_pdf_url' => $insertedData['easyPostLabelPDF'], 'tracking_id' => $insertedData['easyPostShipmentTrackingUrl'],
                                                'tracking_code' => $tracking_number, 'shipment_object' => json_encode($insertedData),
                                            );
                                            $this->order_items->where('order_item_id', '=', $order_item_id)->update($items_Update_Array);
                                        }
                                    }
                                }
                            }
                        }
                        $currentDateTime = Carbon::now();
                        $query_update = DB::table('store_orders')->where('order_id', $order_id)->update(['order_status' => $change_status['status'], 'shipped_date' => $currentDateTime]);
                        $mesage = Constants::SHIPPED_ORDER_BUYER_DESCRIPTION;
                        $subject = Constants::SHIPPED_ORDER_BUYER_SUBJECT.$order_id;
                        if(isset($buyer_id) && !is_null($buyer_id)){
                            $userData = User::with('device_token')->findOrFail($buyer_id);
                            $device_tokens = DeviceToken::where('user_id',$userData->user_id)->where('app_flag','buyer')->get();
                            if ($device_tokens) {
                                $tokens = [];
                                if(isset($device_tokens)&& count($device_tokens) > 0){
    
                                foreach ($device_tokens as $device_token_obj) {
                                    // dd($device_token_obj->fcm_token);
                                
                                
                                $subject = $subject;
                                // foreach($tokens as $token){
                                    $notification = FCMService::send(
                                        $device_token_obj->fcm_token,
                                        [
                                            'title' => $subject,
                                        ]
                                    );
                                }
                            }
                            addNotifications($buyer_id, getCurrentUserId(), $subject, '',URL::to('new-buyer-orders-detail/'.$order_id),'buyer_order_details',$order_id,'buyer');
            
                            }
                        }
                        $order_information = DB::table('store_orders')->where('order_id', $order_id)->select('total_amount', 'payment_status', 'store_id', 'buyer_id')->get()->toArray();
                        $buyer_id = $order_information[0]->buyer_id;
        
                       
                        $val = true;
                        $userData = getUserById($buyer_id);
                        $usershipping = getShippingAddressbyUserId($buyer_id);
                        if (!empty($userData)) {
                            $name  = $userData->name;
                            $email = $userData->email;
                        } else {
                            $name  = "";
                            $email = "noman@mindblazetech.com";
                        }
        
        
                        $description = '';
                        // $this->mandrill->sendMandrillEmail($subject, $templateSlug, $toEmail, $toCompletename, $varsArray);
                        $html = view('mail.shipped-update-client', ['userData' => $userData, "shippingAddress" => $usershipping, 'status' => $change_status['status'], 'orderItems' => $productArray]);
                        $subject = 'Your MallShark Order Has Been Shipped!';
                        $toEmail = $email;
                        $toCompletename = $name;
                        $params = [
                            'from'    => 'MallShark  ' . env('SUPPORT_EMAIL'),
                            'to'      => $toEmail,
                            'subject' => $subject,
                            'html'    => "$html",
                        ];
                        $results = $this->mailgun->SendMailgunMail($params);
                    }
                }
                
                
                
            }
        }
        return [
            'val' => $val,
            'message' => $mesage,
        ];
    }

    /*
     * Update Order
     */
    public function update_order_items($item_array = array())
    {


        $this->order_items->where('sh_id', '=', $shippingAddressId)->update($item_array);
    }

    /*
     * GET yearly orders
     */

    public function getYearlyOrdersOfStore()
    {
        $data = DB::table('store_orders')->whereYear('created_at', date('Y'))->get();
        print_rr($data);
        die();
    }
    public function updateOrderApi($change_status = array())
    {
        
    //   dd($change_status);
        $client = new \EasyPost\EasyPostClient(env('EASYPOST_API_KEY'));
        $val = false;
        $paid = false;
        $mesage= '';
        $redund_status = '';
        if (count($change_status) > 1) {
            $order_id = $change_status['order_id'];
            $orderItems = getOrderItemsByOrderId($order_id);
            $productArray = [];
            foreach ($orderItems as $key => $value) {
                $product_id = $value->item_id;
                $productData = getProductIdSpecificColums($product_id);
                $variableData = null;
                if($productData->product_type== 'variant'){

                    $variableData = getvariableDataByProduct_id($product_id);
                }

                $productArray[] = [
                    'title' => $productData->title,
                    'slug' => $productData->slug,
                    'feature_image' => $productData->feature_image,
                    'product_type' => $productData->product_type,
                    'price_total' => $value->total,
                    'quantity' => $value->quantity,
                    'variable_image' => $variableData ?  $variableData->product_variation_image: '',

                ];
            }
            $order_information = DB::table('store_orders')->where('order_id', $order_id)->select('total_amount', 'payment_status', 'store_id', 'buyer_id')->get()->toArray();
            $buyer_id = $order_information[0]->buyer_id;
            if ($change_status['status'] == 'cancelled' && $change_status['payment_method'] == 'crypto') {

                $Stripeobj = new StripeController();
                if ($Stripeobj->refundBack($order_id)) {
                    $mesage = 'Refund was successfull';
                    $redund_status = '1';

                    $query_update = DB::table('store_orders')->where('order_id', $order_id)->update(['order_status' => 'refunded']);
                    $query_update = DB::table('store_orders')->where('order_id', $order_id)->update(['is_refund' => $change_status['is_refund']]);

                    $userData = getUserById($buyer_id);
                    if (!empty($userData)) {
                        $name  = $userData->name;
                        $email = $userData->email;
                    } else {
                        $name  = "";
                        $email = "noman@mindblazetech.com";
                    }


                    $description = '';
                    $html = view('mail.order-update-vendor-shipped-to-cancelled', ['userData' => $userData, 'status' => $change_status['status'], 'orderItems' => $productArray]);
                    $subject = Constants::CANCEL_ORDER_BUYER_SUBJECT.$order_id;
                    $toEmail = $email;
                    $toCompletename = $name;
                    $params = [
                        'from'    => 'MallShark  ' . env('SUPPORT_EMAIL'),
                        'to'      => $toEmail,
                        'subject' => $subject,
                        'html'    => "$html",
                    ];
                    $results = $this->mailgun->SendMailgunMail($params);
                    if(isset($buyer_id) && !is_null($buyer_id)){
                        $userData = User::with('device_token')->findOrFail($buyer_id);
                        $device_tokens = DeviceToken::where('user_id',$userData->user_id)->where('app_flag','buyer')->get();
                        if ($device_tokens) {
                            $tokens = [];
                            if(isset($device_tokens)&& count($device_tokens) > 0){

                            foreach ($device_tokens as $device_token_obj) {
                                // dd($device_token_obj->fcm_token);
                            
                            
                            $subject = $subject;
                            // foreach($tokens as $token){
                                $notification = FCMService::send(
                                    $device_token_obj->fcm_token,
                                    [
                                        'title' => $subject,
                                    ]
                                );
                            }
                        }
                            addNotifications($buyer_id,getCurrentUserId(), $subject, $description, URL::to('buyer-orders'),'buyer');
        
                        }
                    }
                    // addNotifications($buyer_id, getCurrentUserId(), $subject, $description, URL::to('/buyer-orders'));
                } else {
                    $mesage = 'Refund is not successfull please contact to super admin';
                }
            }
            if ($change_status['status'] == 'refunded' && $change_status['payment_method'] == 'crypto') {

                $Stripeobj = new StripeController();
                if ($Stripeobj->refundBack($order_id)) {
                    $mesage = 'Refund was successfull';
                    $redund_status = '1';

                    $query_update = DB::table('store_orders')->where('order_id', $order_id)->update(['order_status' => 'refunded']);
                    $query_update = DB::table('store_orders')->where('order_id', $order_id)->update(['is_refund' => $change_status['is_refund']]);

                    $userData = getUserById($buyer_id);
                    if (!empty($userData)) {
                        $name  = $userData->name;
                        $email = $userData->email;
                    } else {
                        $name  = "";
                        $email = "noman@mindblazetech.com";
                    }


                    $description = '';
                    $html = view('mail.order-update-vendor-shipped-to-cancelled', ['userData' => $userData, 'status' => $change_status['status'], 'orderItems' => $productArray]);
                    $subject = 'Update on Your Order NO ' . $order_id;
                    $toEmail = $email;
                    $toCompletename = $name;
                    $params = [
                        'from'    => 'MallShark  ' . env('SUPPORT_EMAIL'),
                        'to'      => $toEmail,
                        'subject' => $subject,
                        'html'    => "$html",
                    ];
                    $results = $this->mailgun->SendMailgunMail($params);
                    addNotifications($buyer_id, getCurrentUserId(), $subject, $description, URL::to('/buyer-orders'));
                } else {
                    $mesage = 'Refund is not successfull please contact to super admin';
                }
            }
            if ($change_status['status'] == 'cancelled' && $change_status['shipped_cancel'] == '0') {
                $order_items_array = OrderItems::where('order_id',$order_id)->get();
                // dd($order_items_array);
                foreach($order_items_array as $single_order_item){
                    if(!is_null($single_order_item)){
                        if($single_order_item->variation_id == 0 || $single_order_item->variation_id == null){
                            $product_id = $single_order_item->item_id;
                            $product_quantity = $single_order_item->quantity;
                            // dd($product_quantity);
                            $product_stock_update = Product::find($product_id);
                            $old_Stock = $product_stock_update->current_stock;
                            // dd($old_Stock);
                            $updated_stock = $old_Stock+$product_quantity;
                            // dd($updated_stock);
                            $product_stock_update->current_stock = $updated_stock;
                            
                            $product_stock_update->save();
                        }else{
                            $variation_id = $single_order_item->variation_id;
                            $product_quantity = $single_order_item->quantity;
                            // dd($product_quantity);
                            $product_stock_update = ProductVariation::find($variation_id);
                            $old_Stock = $product_stock_update->current_stock;
                            // dd($old_Stock);
                            $updated_stock = $old_Stock+$product_quantity;
                            // dd($updated_stock);
                            $product_stock_update->current_stock = $updated_stock;
                            
                            $product_stock_update->save();
                        }
                        
                    }
                }
                $Stripeobj = new StripeController();
                // dd($Stripeobj->refundBack($order_id));
                if ($Stripeobj->refundBack($order_id)) {
                    $mesage = 'Refund was successfull';
                    $redund_status = '1';

                    $query_update = DB::table('store_orders')->where('order_id', $order_id)->update(['order_status' => 'refunded']);
                    $query_update = DB::table('store_orders')->where('order_id', $order_id)->update(['is_refund' => $change_status['is_refund']]);

                   
                    $usershipping = getShippingAddressbyUserId($buyer_id);
                    $userData = getUserById($buyer_id);
                    if (!empty($userData)) {
                        $name  = $userData->name;
                        $email = $userData->email;
                    } else {
                        $name  = "";
                        $email = "noman@mindblazetech.com";
                    }


                    $description = ''; 
                    $html = view('mail.order-update-vendor-cancel-to-client', ['userData' => $userData, "shippingAddress" => $usershipping, 'status' => $change_status['status'], 'orderItems' => $productArray, 'orderId' => $order_id]);
                    $subject = Constants::CANCEL_ORDER_BUYER_SUBJECT.$order_id;
                    $toEmail = $email;
                    $toCompletename = $name;
                    $params = [
                        'from'    => 'MallShark  ' . env('SUPPORT_EMAIL'),
                        'to'      => $toEmail,
                        'subject' => $subject,
                        'html'    => "$html",
                    ];
                    $results = $this->mailgun->SendMailgunMail($params);
                    if(isset($buyer_id) && !is_null($buyer_id)){
                        $userData = User::with('device_token')->findOrFail($buyer_id);
                        $device_tokens = DeviceToken::where('user_id',$userData->user_id)->where('app_flag','buyer')->get();
                        if ($device_tokens) {
                            $tokens = [];
                            if(isset($device_tokens)&& count($device_tokens) > 0){

                            foreach ($device_tokens as $device_token_obj) {
                                // dd($device_token_obj->fcm_token);
                            
                            
                            $subject = $subject;
                            // foreach($tokens as $token){
                                $notification = FCMService::send(
                                    $device_token_obj->fcm_token,
                                    [
                                        'title' => $subject,
                                    ]
                                );
                            }
                        }
                            addNotifications($buyer_id,getCurrentUserId(), $subject, $description,URL::to('new-buyer-orders-detail/'.$order_id),'buyer_order_details',$order_id,'buyer');
        
                        }
                    }
                } else {
                    $mesage = 'Refund is not successfull please contact to super admin';
                    
                }
                
            }

            if ($change_status['status'] == 'cancelled' && $change_status['shipped_cancel'] == '1') {
                $order_items_array = OrderItems::where('order_id',$order_id)->get();
                // dd($order_items_array);
                foreach($order_items_array as $single_order_item){
                    if(!is_null($single_order_item)){
                        if($single_order_item->variation_id == 0 || $single_order_item->variation_id == null){
                            $product_id = $single_order_item->item_id;
                            $product_quantity = $single_order_item->quantity;
                            // dd($product_quantity);
                            $product_stock_update = Product::find($product_id);
                            $old_Stock = $product_stock_update->current_stock;
                            // dd($old_Stock);
                            $updated_stock = $old_Stock+$product_quantity;
                            // dd($updated_stock);
                            $product_stock_update->current_stock = $updated_stock;
                            
                            $product_stock_update->save();
                        }else{
                            $variation_id = $single_order_item->variation_id;
                            $product_quantity = $single_order_item->quantity;
                            // dd($product_quantity);
                            $product_stock_update = ProductVariation::find($variation_id);
                            $old_Stock = $product_stock_update->current_stock;
                            // dd($old_Stock);
                            $updated_stock = $old_Stock+$product_quantity;
                            // dd($updated_stock);
                            $product_stock_update->current_stock = $updated_stock;
                            
                            $product_stock_update->save();
                        }
                        
                    }
                }
                $Stripeobj = new StripeController();
                if ($Stripeobj->refundBack($order_id)) {
                    $mesage = 'Refund was successfull';
                    $redund_status = '1';

                    $query_update = DB::table('store_orders')->where('order_id', $order_id)->update(['order_status' => 'refunded']);
                    $query_update = DB::table('store_orders')->where('order_id', $order_id)->update(['is_refund' => $change_status['is_refund']]);


                    $usershipping = getShippingAddressbyUserId($buyer_id);
                    $userData = getUserById($buyer_id);
                    if (!empty($userData)) {
                        $name  = $userData->name;
                        $email = $userData->email;
                    } else {
                        $name  = "";
                        $email = "noman@mindblazetech.com";
                    }


                    $description = '';
                    $html = view('mail.order-update-vendor-shipped-to-cancelled', ['userData' => $userData, "shippingAddress" => $usershipping, 'status' => $change_status['status'], 'orderItems' => $productArray, 'orderId' => $order_id]);
                    $subject = Constants::CANCEL_ORDER_BUYER_SUBJECT.$order_id;
                    $toEmail = $email;
                    $toCompletename = $name;
                    $params = [
                        'from'    => 'MallShark  ' . env('SUPPORT_EMAIL'),
                        'to'      => $toEmail,
                        'subject' => $subject,
                        'html'    => "$html",
                    ];
                    $results = $this->mailgun->SendMailgunMail($params);
                    if(isset($buyer_id) && !is_null($buyer_id)){
                        $userData = User::with('device_token')->findOrFail($buyer_id);
                        $device_tokens = DeviceToken::where('user_id',$userData->user_id)->where('app_flag','buyer')->get();
                        if ($device_tokens) {
                            $tokens = [];
                            if(isset($device_tokens)&& count($device_tokens) > 0){

                            foreach ($device_tokens as $device_token_obj) {
                                // dd($device_token_obj->fcm_token);
                            
                            
                            $subject = $subject;
                            // foreach($tokens as $token){
                                $notification = FCMService::send(
                                    $device_token_obj->fcm_token,
                                    [
                                        'title' => $subject,
                                    ]
                                );
        
                            }
                        }
                            addNotifications($buyer_id,getCurrentUserId(), $subject, $description,URL::to('new-buyer-orders-detail/'.$order_id),'buyer_order_details',$order_id,'buyer');
        
                        }
                    }
                } else {
                    $mesage = 'Refund is not successfull please contact to super admin';
                }
            }
            if ($change_status['status'] == 'refunded' && $change_status['payment_method'] == 'stripe') {
                $Stripeobj = new StripeController();
                if ($Stripeobj->refundBack($order_id)) {
                    $mesage = 'Refund was successfull';
                    $redund_status = '1';
                } else {
                    $mesage = 'Refund is not successfull please contact to super admin';
                }

                $query_update = DB::table('store_orders')->where('order_id', $order_id)->update(['order_status' => 'refunded']);
                $query_update = DB::table('store_orders')->where('order_id', $order_id)->update(['is_refund' => $change_status['is_refund']]);
            }
            if ($this->current_user_can_update_order_api($order_id)) {
                
            //   dd($change_status);
                if (isset($change_status["ship_id"])) {
                    $ship_id = $change_status["ship_id"];
                    $ship_id = '[' . $ship_id . ']';
                    $shipping_ids = json_decode($ship_id, true);

                   
                    $items_Update_Array = array();
                    
                    if ($change_status['status'] == 'shipped') {
                        foreach ($shipping_ids as $key => $item) {

                            foreach ($item as $key => $value) {


                                $easypost_ship_id = $value['shipping_id'];
                                $order_item_id = $value['order_item_id'];
                                $shipping_service = $key;
                                // echo 'Data: <pre>' .print_r($shipping_service,true). '</pre>'; die();
                                $shipping_method = $value['payment_method'];
                                // dd($shipping_method);
                                if ($shipping_method == 'calculated' && isset($easypost_ship_id) && $easypost_ship_id != '') {

                                    $shipment = $client->shipment->retrieve($easypost_ship_id);
                                    // dd($shipment);
                                    for ($i = 0; $i < count($shipment->rates); $i++) {
                                        //match our rate to the service
                                        if ($shipment->rates[$i]->service == $shipping_service) {
                                            $flagSelected = true;
                                            $selectedRate = $shipment->rates[$i];
                                            $boughtShipment = $client->shipment->buy($shipment->id, $selectedRate);

                                            $shipmentWithLabel =   $client->shipment->label($shipment->id, ["file_format" => "pdf"]);
                                            // dd($shipmentWithLabel);
                                            $insertedData['easyPostLabelPNG'] = $shipmentWithLabel->postage_label->label_url;
                                            $insertedData['easyPostLabelPDF'] = $shipmentWithLabel->postage_label->label_pdf_url;
                                            $insertedData['easyPostLabelRateId'] = $shipmentWithLabel->selected_rate->id;

                                            $insertedData['easyPostLabelCarrier'] = $shipmentWithLabel->selected_rate->carrier;
                                            $insertedData['easyPostLabelService'] = $shipmentWithLabel->selected_rate->service;
                                            $insertedData['shipmentStatus'] = $shipmentWithLabel->tracker->status;
                                            $insertedData['estDeliveryDate'] = $shipmentWithLabel->tracker->est_delivery_date;
                                            $insertedData['trackingCode'] = $shipmentWithLabel->tracking_code;

                                            $insertedData['shipmentCost'] = number_format($selectedRate->rate, 2, '.', '');
                                            $insertedData['easyPostShipmentId'] = $shipment->id;
                                            $insertedData['easyPostShipmentTrackingUrl'] = $shipmentWithLabel->tracker->public_url;
                                            $insertedData['easyPostLabelSize'] = $shipmentWithLabel->postage_label->label_size;

                                            $tracking_number = $insertedData['trackingCode'];
                                            $randomFileName = $tracking_number . ".png";
                                            $randomFileNamePDF = $tracking_number . ".pdf";
                                            $downloadResult = file_get_contents($shipmentWithLabel->postage_label->label_url);
                                            $downloadResultPDF = file_get_contents($shipmentWithLabel->postage_label->label_pdf_url);

                                            $logs_dir = 'downloads/labels/';
                                            if (!is_dir($logs_dir)) {
                                                mkdir($logs_dir, 0777, true);
                                            }
                                            File::put($logs_dir . $randomFileName, $downloadResult);
                                            File::put($logs_dir . $randomFileNamePDF, $downloadResultPDF);

                                            $items_Update_Array = array(
                                                'rate_id' => $insertedData['easyPostLabelRateId'], 'label_url' => $insertedData['easyPostLabelPNG'],
                                                'lable_pdf_url' => $insertedData['easyPostLabelPDF'], 'tracking_id' => $insertedData['easyPostShipmentTrackingUrl'],
                                                'tracking_code' => $tracking_number, 'shipment_object' => json_encode($insertedData),
                                            );
                                            $this->order_items->where('order_item_id', '=', $order_item_id)->update($items_Update_Array);
                                        }
                                    }
                                }
                            }
                        }
                        $currentDateTime = Carbon::now();
                        $query_update = DB::table('store_orders')->where('order_id', $order_id)->update(['order_status' => $change_status['status'], 'shipped_date' => $currentDateTime]);
                        $mesage = Constants::SHIPPED_ORDER_BUYER_DESCRIPTION;
                        $subject = Constants::SHIPPED_ORDER_BUYER_SUBJECT.$order_id;
                        if(isset($buyer_id) && !is_null($buyer_id)){
                            $userData = User::with('device_token')->findOrFail($buyer_id);
                        $device_tokens = DeviceToken::where('user_id',$userData->user_id)->where('app_flag','buyer')->get();
                            
                            if ($device_tokens) {
                                $tokens = [];
                                if(isset($device_tokens)&& count($device_tokens) > 0){
    
                                foreach ($device_tokens as $device_token_obj) {
                                    // dd($device_token_obj->fcm_token);
                                
                                
                                $subject = $subject;
                                // foreach($tokens as $token){
                                    $notification = FCMService::send(
                                        $device_token_obj->fcm_token,
                                        [
                                            'title' => $subject,
                                        ]
                                    );
            
                                }
                            }
                            addNotifications($buyer_id, getCurrentUserId(), $subject, '',URL::to('new-buyer-orders-detail/'.$order_id),'buyer_order_details',$order_id,'buyer');
            
                            }
                        }
                    }
                }
                
               
                $order_information = DB::table('store_orders')->where('order_id', $order_id)->select('total_amount', 'payment_status', 'store_id', 'buyer_id')->get()->toArray();
                $buyer_id = $order_information[0]->buyer_id;

               

                $val = true;
                $userData = getUserById($buyer_id);
                $usershipping = getShippingAddressbyUserId($buyer_id);
                if (!empty($userData)) {
                    $name  = $userData->name;
                    $email = $userData->email;
                } else {
                    $name  = "";
                    $email = "noman@mindblazetech.com";
                }


                $description = '';
                // $this->mandrill->sendMandrillEmail($subject, $templateSlug, $toEmail, $toCompletename, $varsArray);
                $html = view('mail.shipped-update-client', ['userData' => $userData, "shippingAddress" => $usershipping, 'status' => $change_status['status'], 'orderItems' => $productArray]);
                $subject = 'Your MallShark Order Has Been Shipped!';
                $toEmail = $email;
                $toCompletename = $name;
                $params = [
                    'from'    => 'MallShark  ' . env('SUPPORT_EMAIL'),
                    'to'      => $toEmail,
                    'subject' => $subject,
                    'html'    => "$html",
                ];
                $results = $this->mailgun->SendMailgunMail($params);
                
            }
        }
        return [
            'val' => $val,
            'message' => $mesage,
            'redund_status' => $redund_status,
        ];
    }
}
