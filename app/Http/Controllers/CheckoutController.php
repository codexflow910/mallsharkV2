<?php

namespace App\Http\Controllers;

use DB;
use PayPal;
use Session;
use Redirect;
use App\Models\User;
use App\Models\Store;
use App\Models\Userrole;
use Illuminate\Http\Request;
use App\Http\Controllers\UspsT;
use App\Models\{ShippingAddress,Product};
use Illuminate\Support\Facades\URL;
use Illuminate\Database\Eloquent\Model;
use App\Repositories\Checkout\Checkoutaccess;
use App\Repositories\Location\LocationAccess;
use App\Repositories\Orders\StoreOrdersAccess;


//use Redirect;
class CheckoutController extends Controller
{

    private $_request;
    private $_esearch;
    private $_checkoutobj;
    private $access_obj;
    private $shipping_address;
    private $location;
    private $_user;
    private $_userRole;
    private $mailgun;
    private $usps;

    public function __construct(Request $request)
    {
        $this->access_obj = new StoreOrdersAccess();
        $this->_checkoutobj = new Checkoutaccess();
        $this->_request = $request;
        $this->location = new LocationAccess();
        $this->shipping_address = new ShippingAddress();
        $this->_esearch = new ElasticSearchController();
        $this->_userRole = new Userrole();
        $this->_user = new User();
        $this->usps = new UspsT();
        $this->mailgun = new MailgunController();
    }

    public function getUspsrate()
    {
        return $this->usps->getDpmesticRates('91601', '90001', '15.5', "");
    }

    public function SaveOrder()
    {
        $postedData = $this->_request->input();

        echo $this->access_obj->create_order($postedData);
    }

    public function checkout()
    {

        // if ($this->isMobileDevice()) {
        //     return view('checkout.mobile.checkout_new');
        // } else {
        return view('checkout.checkout_new');
        // }
    }

    public function checkout_save(Request $request)
    {

        $all_inputs = $request->all();
        // echo 'Data: <pre>' .print_r($all_inputs,true). '</pre>'; die();

        if (count($all_inputs) > 0) {

            $user_email = isset($all_inputs['useremail']) ? $all_inputs['useremail'] : "";
            $user = User::where('email', $user_email)->first();
            $fullname = $all_inputs['first_name'] . ' ' . $all_inputs['last_name'];
            $address = new ShippingAddress();
            $login_key = str_random(25);
            // DB::transaction(function () {


            if ($user) {

                $address->full_name = $fullname;
                $address->country = $all_inputs['country'];
                $address->state = $all_inputs['states_countries'];
                $address->street = $all_inputs['street_address'];
                $address->post_code = $all_inputs['zipcode'];
                $address->city = $all_inputs['city_name'];
                $address->user_id = $user->user_id;
                $address->save();

                // User already exists, do not insert
                // return "user exist";
            } else {

                // User does not exist, insert new record
                $this->_user->name = $fullname;
                $this->_user->email = $user_email;
                $this->_user->password = md5('12345678');
                $this->_user->login_key = $login_key;
                $this->_user->register_source = 'MallShark';

                $this->_user->contact_no = isset($all_inputs['phone_number']) ? $all_inputs['phone_number'] : "";

                $this->_user->save();

                $lastInsertid = $this->_user->user_id;

                $address->full_name = $fullname;
                $address->country = $all_inputs['country'];
                $address->state = $all_inputs['states_countries'];
                $address->street = $all_inputs['street_address'];
                $address->post_code = $all_inputs['zipcode'];
                $address->city = $all_inputs['city_name'];
                $address->user_id = $lastInsertid;
                $address->save();




                $this->_userRole->user_id = $lastInsertid;
                $this->_userRole->role_id = 4;
                $this->_userRole->save();

                $url = URL::to('/validate-email/' . $login_key);
                $html = view('mail.activateUser', ['activate_url' => $url]);
                $subject = 'Welcome to MallShark - Get Ready for an Unforgettable Shopping Experience!';
                //  $templateSlug = 'signup';
                $toEmail = $all_inputs['useremail'];
                $toCompletename = $all_inputs['first_name'];
                $params = [
                    'from'    => 'support@mallshark.net',
                    'to'      => $toEmail,
                    'subject' => $subject,
                    'html'    => "$html",
                ];
                $results = $this->mailgun->SendMailgunMail($params);
                $this->_esearch->userMapping($lastInsertid);
            }
        }
        // $last_record = ShippingAddress::where('user_id', '=', getCurrentUserId())->orderBy('sh_id', 'desc')->first();

        // //$countries = $this->location->getAllCountries();
        // $countries = array();
        // $states = array();
        // if (isset($last_record['country']) && $last_record['country'] != '') {
        //     $states = $this->location->getStateByCountryCode($last_record['country']);
        // }
    }

    public function getCheckout($reload = '', $shiptCountryId = '', $shipStateId = '')
    {
        $result = $this->_checkoutobj->cartDetail($shiptCountryId, $shipStateId);
        if (empty($result)) {
            $sessionData = '';
        } else {
            $arr = array();
            $totalPrice = 0;
            $productPrice = 0;
            $productPrice_simple = 0;
            $shipingAmout = 0;
            $shipingAmout_simple = 0;
            $tax = 0;
            $storeShippingMethod = '';
            $storePaymentMethodCrypto = '';
            $storePaymentMethodStripe = '';
            //die();
            // echo '$result: <pre>' .print_r($result,true). '</pre>'; die();
            $totalshipingAmout = 0;
            $totalshipingAmout_simple = 0;
            $storeproductPrice = 0;
            $storeproductPrice_simple = 0;
            $store_stripe_account_id = '';
            $ordershippingAmount = 0;
            $orderPrice = 0;
            $store_ids = [];
            $totalDiscount = 0;
            $subtotal = 0;
            foreach ($result as $mian_key => $rs) {
                if(trim($mian_key) != '' && isset($mian_key)){
                    $store_array = explode('-',$mian_key);
                    if(isset($store_array[1])){
                      $store_id = $store_array[1];
                      array_push($store_ids,$store_id);  
                    }
                }
                
                $storetotalPrice = 0;
                $prevProductIds = [];
                $shipping_price = true;
                $productStock = [];
                foreach ($rs as $key => $val) {
                    $store_stripe_account_id = $val['stripe_account_id'];
                    // echo 'Data: <pre>' .print_r($val,true). '</pre>'; 
                    if (isset($val['variation_id']) && $val['variation_id'] != '') {

                        if (!in_array($val['product_id'], $prevProductIds)) {
                            if ($val['shipping_method'] == 'flat') {
                                $flat_ship_price =  $val['shipping_price'];
                                $totalshipingAmout += $flat_ship_price;
                                $val['shipping_price'] = $flat_ship_price;
                            } else {
                                $flat_ship_price =  $val['shipping_price'];
                                $totalshipingAmout += $flat_ship_price;
                            }
                        }
                       
                        $var_id = $val['variation_id'];
                        $res = DB::table('product_variation')->where('variation_id', $var_id)->select('current_stock')->first();
                        // echo '$res: <pre>' .print_r($res,true). '</pre>'; die();

                        // $productType = $res->product_type;
                        $res_res = $res->current_stock;
                        //die();
                        if ($res_res <= 0) {
                            ?>
                            <style>
                                .checkout-page {
                                    display: none !important;
                                }
                            </style>
                        <?php

                            echo '<div class="alert alert-danger" style="font-size:17px">' . $val['title'] . '(' . $val['option1'] . ' ' . $val['option2'] . ') is out of stock please update your <a href="/cart" style="color:blue;font-weight: bold;">cart</a></div>';
                            return view('cart/cart-alert');
                        }
                        if ($res_res < $val['product_quantity']) {
                        ?>
                            <style>
                                .checkout-page {
                                    display: none !important;
                                }
                            </style>

                        <?php


                            echo '<div class="alert alert-danger" style="font-size:17px">Product ' . $val['title'] . '(' . $val['option1'] . ' ' . $val['option2'] . ') has current stock of ' . $res_res . ' please update <a href="/cart" style="color:blue;font-weight: bold;">cart</a> accordingly</div> ';
                            return view('cart/cart-alert');
                        }

                        if (is_numeric($key)) {
                            $product_type = $val['product_type'];
                            $price = $val['on_sale'] == 1 ? $price = $val['product_variation_sale_price'] : $price = $val['variation_sale_price'] ;
                            $productActualPrice = $val['variation_sale_price'];
                            $subtotal += $val['product_quantity'] * $productActualPrice; 
                            if($val['on_sale'] == 1){
                                $totalDiscount += ($val['product_quantity'] * $productActualPrice) - ($val['product_quantity'] * $val['product_variation_sale_price']);
                            }
                            $storeproductPrice += $val['product_quantity'] * $price;
                            //  $totalshipingAmout += $flat_ship_price;
                            $storeShippingMethod = isset($val['shipping_method']) ? $val['shipping_method'] : 0;
                            // $storetotalPrice += $storeproductPrice + $totalshipingAmout;
                            $productPrice += $val['product_quantity'] * $price;
                            //   $shipingAmout += $totalshipingAmout;
                            //   $shipingAmout = $totalshipingAmout;
                            //$tax += $val['product_tax'];
                            $tax += 0;
                        }
                    } else {
                        $storePaymentMethodCrypto = $val['store_wallet_address'];
                        $storePaymentMethodStripe = $val['stripe'];
                        $price = $val['on_sale'] == 1 ? $val['sale_price'] : $val['price'];
                        $productActualPrice = $val['price'];
                        $storeproductPrice_simple += $val['product_quantity'] * $price;
                        $productPrice_simple += $val['product_quantity'] * $price;
                        $flat_ship_price =  isset($val['shipping_price']) ? $val['shipping_price'] : "0";
                        $storeShippingMethod = isset($val['shipping_method']) ? $val['shipping_method'] : 0;
                        $totalshipingAmout_simple += $flat_ship_price;
                        $shipingAmout_simple = $totalshipingAmout_simple;
                        $subtotal += $val['product_quantity'] * $productActualPrice; 
                        if($val['on_sale'] == 1){
                            $totalDiscount += ($val['product_quantity'] * $productActualPrice) - ($val['product_quantity'] * $val['sale_price']);
                        }
                    }
                    // $prevProductId = $val['product_id'];
                    array_unshift($prevProductIds, $val['product_id']);
                    // $flat_ship_price =  $val['shipping_price'];
                    if (!empty($val['product_id']) && $val['product_id'] != '' && $val['variation_id'] == '') {
                        $product_id = $val['product_id'];
                        $res = DB::table('products')->where('product_id', $product_id)->select('current_stock', 'product_type')->first();
                        $res_res = $res->current_stock;
                        $productType = $res->product_type;
                        $tempProduct = array(
                            'currunt_stock' => $res_res,
                            'product_type' => $productType
                        );
                        $productStock[] = $tempProduct;

                        if ($productType != 'virtual' && $res_res <= 0) {

                        ?>
                            <style>
                                .checkout-page {
                                    display: none !important;
                                }
                            </style>
                        <?php

                            echo '<div class="alert alert-danger" style="font-size:17px">' . $val['title'] . ' is out of stock please update your <a href="/cart" style="color:blue;font-weight: bold;">cart</a></div>';

                            return view('cart/cart-alert');
                        }
                        if ($productType != 'virtual' && $res_res < $val['product_quantity']) {

                        ?>
                            <style>
                                .checkout-page {
                                    display: none !important;
                                }
                            </style>
                            
                        <?php
                        // die;
                        // return redirect()->back();
                            
                            echo '<div class="alert alert-danger" style="font-size:17px">Product ' . $val['title'] . ' has current stock of ' . $res_res . ' please update <a href="/cart" style="color:blue;font-weight: bold;">cart</a> accordingly</div> ';
                           
                            return view('cart/cart-alert');
                           
                        }
                    }
                }

                foreach ($productStock as $key => $value) {
                    if ($value['product_type'] == 'virtual') {
                        continue;
                    }
                    if ($value['currunt_stock'] == 0) {
                        die();
                    }

                    $result[$mian_key]['store_product_price'] = $storeproductPrice + $storeproductPrice_simple;
                    $result[$mian_key]['store_shipping_price'] =  $totalshipingAmout + $shipingAmout_simple;
                    $result[$mian_key]['shipping_method'] = $storeShippingMethod;
                    $result[$mian_key]['store_tax'] = $tax;
                    $result[$mian_key]['store_wallet_address'] = $storePaymentMethodCrypto;
                    $result[$mian_key]['store_stripe_gateway'] = $storePaymentMethodStripe;
                    $result[$mian_key]['store_total_price'] =  $result[$mian_key]['store_product_price'] + $result[$mian_key]['store_shipping_price'] + $result[$mian_key]['store_tax'];
                    $result[$mian_key]['store_stripe_account'] =  $store_stripe_account_id;
                }

                foreach ($productStock as $key => $value) {
                    if ($value['product_type'] == 'virtual') {
                        continue;
                    }
                    if ($value['currunt_stock'] == 0) {
                        die();
                    }
                }
                $orderPrice = $productPrice + $productPrice_simple;
                // $orderPrice = $orderPrice;
                $ordershippingAmount = $totalshipingAmout + $shipingAmout_simple;
                // $ordershippingAmount += $ordershippingAmount;
                $arr['total_tax'] = $tax;
                $arr['Shipping_method'] = $storeShippingMethod;
                $arr['products_price'] = $orderPrice;
            $arr['shipping_price'] = $ordershippingAmount;
            $arr['subtotal_price'] = $arr['products_price'];
            // $arr['total_price'] = $arr['products_price']  + $arr['shipping_price'];
            $arr['total_price'] = $arr['products_price'];

            }

            if (empty($result)) {
                $sessionData = '';
            } else {
                $sessionData = array_merge($result, $arr);
                //   echo 'Data: <pre>' .print_r($sessionData,true). '</pre>'; die();
            }
        }
        // dd($sessionData);
        $crypto_result = $this->_checkoutobj->cartDetail_crypto($shiptCountryId, $shipStateId);
        if (empty($crypto_result)) {
            $sessionData_crypto = '';
        } else {
            // The result is not empty

            $arr = array();
            $totalPrice = 0;
            $productPriceCrypto = 0;
            $productPrice_simpleCrypto = 0;
            $shipingAmout = 0;
            $shipingAmout_simpleCrypto = 0;
            $tax = 0;
            $storeShippingMethodCrypto = '';
            $storePaymentMethodCrypto = '';
            $storePaymentMethodStripe = '';
            //die();
            // echo '$result: <pre>' .print_r($result,true). '</pre>'; die();
            $totalshipingAmoutCrypto = 0;
            $totalshipingAmout_simpleCrypto = 0;
            $storeproductPriceCrypto = 0;
            $storeproductPrice_simpleCrypto = 0;
            foreach ($crypto_result as $mian_key => $rs) {
                $storetotalPrice = 0;


                $shipping_price = true;
                foreach ($rs as $key => $val) {
                    if (isset($val['variation_id']) && $val['variation_id'] != '') {


                        if ($shipping_price) {
                            if ($val['shipping_method'] == 'flat') {
                                $flat_ship_price =  $val['shipping_price'];
                                $totalshipingAmoutCrypto += $flat_ship_price;
                                $val['shipping_price'] = $flat_ship_price;
                            } else {
                                $flat_ship_price =  $val['shipping_price'];
                                $totalshipingAmoutCrypto += $flat_ship_price;
                            }

                            $shipping_price = false;
                        } else {
                            $flat_ship_price =  0;
                            $shipping_price = false;
                            $totalshipingAmoutCrypto += $flat_ship_price;
                        }


                        $var_id = $val['variation_id'];
                        $res = DB::table('product_variation')->where('variation_id', $var_id)->select('current_stock')->first();
                        // echo '$res: <pre>' .print_r($res,true). '</pre>'; die();

                        // $productType = $res->product_type;
                        $res_res = $res->current_stock;
                        //die();
                        if ($res_res <= 0) {
                        ?>
                            <style>
                                .checkout-page {
                                    display: none !important;
                                }
                            </style>
                        <?php

                            echo '<div class="alert alert-danger" style="font-size:17px">' . $val['title'] . '(' . $val['option1'] . ' ' . $val['option2'] . ') is out of stock please update your <a href="/cart" style="color:blue;font-weight: bold;">cart</a></div>';
                            return view('cart/cart-alert');
                        }
                        if ($res_res < $val['product_quantity']) {
                        ?>
                            <style>
                                .checkout-page {
                                    display: none !important;
                                }
                            </style>
                        <?php

                            echo '<div class="alert alert-danger" style="font-size:17px">Product ' . $val['title'] . '(' . $val['option1'] . ' ' . $val['option2'] . ') has current stock of ' . $res_res . ' please update <a href="/cart" style="color:blue;font-weight: bold;">cart</a> accordingly</div> ';
                            return view('cart/cart-alert');
                        }

                        if (is_numeric($key)) {
                            // $price = $val['price'];
                            $variation_sale_price = $val['variation_sale_price'];
                            $product_type = $val['product_type'];
                            $price = $val['on_sale'] == 1 ? $val['product_variation_sale_price'] : $val['variation_sale_price'];
                            $storeproductPriceCrypto += $val['product_quantity'] * $price;
                            //  $totalshipingAmout += $flat_ship_price;
                            $storeShippingMethodCrypto = isset($val['shipping_method']) ? $val['shipping_method'] : 0;
                            // $storetotalPrice += $storeproductPrice + $totalshipingAmout;
                            $productPriceCrypto += $val['product_quantity'] * $price;
                            //   $shipingAmout += $totalshipingAmout;
                            //   $shipingAmout = $totalshipingAmout;
                            //$tax += $val['product_tax'];
                            $tax += 0;
                        }
                    } else {

                        $storePaymentMethodCrypto = $val['store_wallet_address'];
                        $storePaymentMethodStripe = $val['stripe'];

                        $price = $val['on_sale'] == 1 ? $val['sale_price'] : $val['price'];
                        $storeproductPrice_simpleCrypto += $val['product_quantity'] * $price;
                        $productPrice_simpleCrypto += $val['product_quantity'] * $price;
                        $flat_ship_price =  isset($val['shipping_price']) ? $val['shipping_price'] : "0";
                        $storeShippingMethodCrypto = isset($val['shipping_method']) ? $val['shipping_method'] : 0;
                        $totalshipingAmout_simpleCrypto += $flat_ship_price;
                        $shipingAmout_simpleCrypto = $totalshipingAmout_simpleCrypto;
                    }

                    // $flat_ship_price =  $val['shipping_price'];
                    // echo '$flat_ship_price: <pre>' .print_r($flat_ship_price,true). '</pre>'; 
                    if (isset($val['product_id']) && $val['product_id'] != '' && $val['variation_id'] == '') {
                        $product_id = $val['product_id'];
                        $res = DB::table('products')->where('product_id', $product_id)->select('current_stock', 'product_type')->first();


                        $res_res = $res->current_stock;
                        $productType = $res->product_type;

                        if ($productType != 'virtual' && $res_res <= 0) {
                        ?>
                            <style>
                                .checkout-page {
                                    display: none !important;
                                }
                            </style>
                        <?php

                            echo '<div class="alert alert-danger" style="font-size:17px">' . $val['title'] . ' is out of stock please update your <a href="/cart" style="color:blue;font-weight: bold;">cart</a></div>';

                            return view('cart/cart-alert');
                        }
                        if ($productType != 'virtual' && $res_res < $val['product_quantity']) {
                        ?>
                            <style>
                                .checkout-page {
                                    display: none !important;
                                }
                            </style>
<?php
                           
                            echo '<div class="alert alert-danger" style="font-size:17px">Product ' . $val['title'] . ' has current stock of ' . $res_res . ' please update <a href="/cart" style="color:blue;font-weight: bold;">cart</a> accordingly</div> ';
                            return view('cart/cart-alert');
                        }
                    }
                }

                $crypto_result[$mian_key]['store_product_price'] = $storeproductPriceCrypto + $storeproductPrice_simpleCrypto;
                $crypto_result[$mian_key]['store_shipping_price'] = $totalshipingAmoutCrypto + $shipingAmout_simpleCrypto;;
                $crypto_result[$mian_key]['shipping_method'] = $storeShippingMethodCrypto;
                $crypto_result[$mian_key]['store_tax'] = $tax;
                $crypto_result[$mian_key]['store_wallet_address'] = $storePaymentMethodCrypto;
                $crypto_result[$mian_key]['store_stripe_gateway'] = $storePaymentMethodStripe;
                $crypto_result[$mian_key]['store_total_price'] = $crypto_result[$mian_key]['store_product_price'] + $crypto_result[$mian_key]['store_shipping_price'] + $crypto_result[$mian_key]['store_tax'];
            }
            $arr['products_price'] = $productPriceCrypto + $productPrice_simpleCrypto;
            $arr['shipping_price'] = $totalshipingAmoutCrypto + $shipingAmout_simpleCrypto;
            $arr['total_tax'] = $tax;
            $arr['subtotal_price_crypto'] = $arr['products_price'];
            // $arr['total_price_crypto'] = $arr['products_price'] + $arr['shipping_price'] ;
            $arr['total_price_crypto'] = $arr['products_price'];
            $arr['Shipping_method'] = $storeShippingMethodCrypto;
            $sessionData_crypto = array_merge($crypto_result, $arr);
            // dd($sessionData_crypto);
        }
       
        if (empty($sessionData) && empty($sessionData_crypto)) {
            return redirect('/');
        }
        $store_owners_email = [];
        if(count($store_ids) > 0){
            foreach($store_ids as $storeId){
                $getStore = Store::find($storeId);
                if($getStore){
                    $storeOwnerEmail = $getStore->user()->exists() ? $getStore->user->email : '';
                    if(isset($storeOwnerEmail) &&  trim($storeOwnerEmail) != ''){
                        array_push($store_owners_email,$storeOwnerEmail);
                        
                    }
                }
            }
        }
        $sessionData['subtotal'] = $subtotal;
        $sessionData['total_discount'] = $totalDiscount;
        return view('checkout.checkout_new', ['orderData' => $sessionData, 'alldata' => '', 'orderDataCrypto' => $sessionData_crypto,'store_owners'=>$store_owners_email]);
    }

    public function deleteCart()
    {
        $this->_checkoutobj->deleteCartData();
    }

    public function getCheckout_step2()
    {
        // if ($this->isMobileDevice()) {
        //     return view('checkout.mobile.checkout-main')->with('step', '2');
        // } else {
        return view('checkout.checkout-main')->with('step', '2');
        // }
    }

    public function getCheckout_step3()
    {
        // if ($this->isMobileDevice()) {
        //     return view('checkout.mobile.checkout-main')->with('step', '3');
        // } else {
        return view('checkout.checkout-main')->with('step', '3');
        // }
    }

    public function getCheckout_step4()
    {
        // if ($this->isMobileDevice()) {
        //     return view('checkout.mobile.checkout-main')->with('step', '4');
        // } else {
        return view('checkout.checkout-main')->with('step', '4');
        // }
    }

    public function getCancel()
    {
        // Curse and humiliate the user for cancelling this most sacred payment (yours)

        return view('checkout.cancel');
    }

    public function index(Request $request)
    {
        if (!getCurrentUserId()) {
            return redirect('/cart');
        }
        $cartdata = cartDetail();

        if (empty($cartdata)) {
            return redirect('/cart');
        }


        $all_inputs = $request->all();

        if (count($all_inputs) > 0) {
            if (isset($all_inputs['sh_id']) && $all_inputs['sh_id'] != '') {
                $address = ShippingAddress::find($all_inputs['sh_id']);

                $address->user_id = getCurrentUserId();
                $address->full_name = $all_inputs['full_name'];
                $address->country = $all_inputs['country'];
                $address->state = $all_inputs['state'];
                $address->street = $all_inputs['street'];
                $address->post_code = $all_inputs['post_code'];
                $address->flat_other = $all_inputs['flat_other'];
                $address->save();
            } else {
                $this->shipping_address->user_id = getCurrentUserId();
                $this->shipping_address->full_name = $all_inputs['full_name'];
                $this->shipping_address->country = $all_inputs['country'];
                $this->shipping_address->state = $all_inputs['state'];
                $this->shipping_address->street = $all_inputs['street'];
                $this->shipping_address->post_code = $all_inputs['post_code'];
                $this->shipping_address->flat_other = $all_inputs['flat_other'];
                $this->shipping_address->save();
            }
        }
        $last_record = ShippingAddress::where('user_id', '=', getCurrentUserId())->orderBy('sh_id', 'desc')->first();

        //$countries = $this->location->getAllCountries();
        $countries = array();
        $states = array();
        if (isset($last_record['country']) && $last_record['country'] != '') {
            $states = $this->location->getStateByCountryCode($last_record['country']);
        }
        // return view('shipping.shipping_address', array('countries' => $countries, 'last_record' => $last_record, 'states' => $states));
        // if ($this->isMobileDevice()) {
        //     return view('checkout.mobile.checkout-main', array('countries' => $countries, 'last_record' => $last_record, 'states' => $states));
        // } else {
        return view('checkout.checkout-main', array('countries' => $countries, 'last_record' => $last_record, 'states' => $states));
        // }
    }

    public function saveShippingAddress(Request $request)
    {

        $allData = $request->all();


        if (!empty($allData)) {
            if (isset($allData['address-option-select']) && $allData['address-option-select'] != '') {
                $results = DB::table('shipping_addresses')
                    ->where('sh_id', '=', $allData['address-option-select'])
                    ->select('*')
                    ->get()
                    ->toArray();
                if (!empty($results) && isset($results[0]->country) && isset($results[0]->state)) {
                    $this->getCheckout('true', $results[0]->country, $results[0]->state);
                }
            } else {
                $this->getCheckout('true', $allData['country'], $allData['state']);
            }
        }

        $post_code = isset($allData["post_code"]) ? $allData["post_code"] : '';
        if ($post_code == '') {
            $shipping_id = isset($allData["address-option-select"]) ? $allData["address-option-select"] : '';
            $last_record = ShippingAddress::where('sh_id', '=', $shipping_id)->select('post_code')->get()->toArray();
            $post_code = $last_record[0]['post_code'];
        }
        //        print_rr(Session::get('orderData'));
        //        exit;
        // if ($this->isMobileDevice()) {
        //     return view('checkout.mobile.checkout-main')->with('alldata', $allData);
        // } else {
        return view('checkout.checkout-main')->with('alldata', $allData);
        // }
        //return back();    
    }
    public function OrderConfirmation()
    {
        if (!session()->has('checkout_complete')) {
            return redirect('/');
        }
    
        // Clear session variable after successful redirection to the thank you page
        session()->forget('checkout_complete');
        
        return view('checkout.order-confirmation');
    }
    public function setCheckoutSession()
    {
        session(['checkout_complete' => true]);
        return response()->json(['status' => 'session_set']);
    }

    public function check_checkout_product(Request $request){
        $email = $request->input('email');
        $product_id = $request->input('product_id');
        $product = Product::find($product_id);
    }
    public function alert_checkout(){
        return view('cart/cart-alert');
    }
}
