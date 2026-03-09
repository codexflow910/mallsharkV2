<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Repositories\Checkout;

use DB;
use App;
use Hash;
use Cookie;
use Request;
use Session;
use App\BaseAccess;
use App\Models\Cart;
use App\Models\ProductCart;
use Illuminate\Support\Str;
use App\Http\Controllers\UspsT;
use App\Johnpaulmedina\Usps\RatePackage;

/**
 * Description of StoreAccess
 *
 * @author Asif
 */
class Checkoutaccess extends BaseAccess {

    private $_cart;
    private $shiprate;
    public function __construct() {
        $this->_cart = new Cart();
        $this->shiprate = new UspsT();
       

    }

    public function cartDetail($userCountryId = '', $userStateId = '') {
        $arr = array();
        $arr2 = array();
        $alldata = array();

        
        if (isset($_COOKIE['cartCookie']) && !empty($_COOKIE['cartCookie'])) {
            $keyval = $_COOKIE['cartCookie'];
        
            $cartDetail = ProductCart::with(['product', 'variation', 'store', 'product.store', 'product.shippingOption'])->where('random_key', $keyval)->get()->toArray();
            // $cartDetail = ProductCart::with('product','store', 'shippingOption', 'variation')->where('random_key', $key)->get();

        // $query = DB::table('products_cart')
                // ->select('products.product_id', 'products_cart.store_id', 'products.weight_type', 'products.dimention_type', 'products_cart.user_id', 'products_cart.random_key', 'products.product_type',
                //  'products.title', 'products.price','products.slug', 'products.feature_image', 'products.weight', 'products.productSize_length', 'products.productSize_width',
                //   'products.productSize_height', 'products_cart.cart_id', 'products_cart.product_quantity', 'product_variation.variation_id', 'product_variation.option1',
                //   'product_variation.attribute1','product_variation.attribute2','product_variation.attribute3',
                //   'product_variation.unit1', 'product_variation.option2','product_variation.option3','product_variation.product_variation_image as product_variation_image',
                //   'product_variation.unit2', 'product_variation.unit3','product_variation.quantity as product_variation_quantity',
                //   'product_variation.price as variation_sale_price','stores.store_state','stores.store_country','stores.store_city','stores.store_name',
                //   'stores.zip_code', 'stores.contact_info','products.productSize_length', 'products.productSize_width',
                //   'products.productSize_height', 'products.shipping_option_id', 'stores.zip_code', 'shipping_options.shipping_service',
                //   'shipping_options.domestic_handling_fee', 'shipping_options.flat_shipping_rate' ,'shipping_options.is_domesticfree','shipping_options.profile_name' ,'shipping_options.origin_zipcode as shipping_option_zip')
                // ->Join('products', 'products.product_id', '=', 'products_cart.product_id')
                // ->LeftJoin('stores', 'products_cart.store_id', '=', 'stores.store_id')
                // ->LeftJoin('shipping_options', 'shipping_options.id', '=', 'products.shipping_option_id')
                // ->LeftJoin('product_variation', 'product_variation.variation_id', '=', 'products_cart.variation_id');
        // if (Session::has('userData')) {
        //     $userId = Session::get('userData')['user_id'];
        //     $query->where('products_cart.user_id', $userId);
        // } else if (!empty($_COOKIE['cartCookie'])) {
        //     $randomKey = $_COOKIE['cartCookie'];
        //     $query->where('products_cart.random_key', $randomKey);
        // } else if (empty($userId)) {
        //     $userId = Request::segment(2);
        // }
        // $cartDetail = $query->get();
        // echo '$cartDetails: <pre>' .print_r($cartDetail,true). '</pre>'; die();
        if (!empty($cartDetail)) {
            $prodcutData = $this->objToArray($cartDetail);
            $productweight = "";
            // echo '$prodcutData: <pre>' .print_r($prodcutData,true). '</pre>'; die();
            // $shipmentData = $this->getShippingPrice($prodcutData);
            foreach ($prodcutData as $key => $pd) {
                $arr['usps_services'] = ['First','Priority','Express'];
                $arr['product_id'] = $pd['product_id'];
                $arr['store_id'] = $pd['store_id'];
                $arr['origin_zip'] = $pd['store']['zip_code'];
                $arr['user_id'] = $pd['user_id'];
                $arr['random_key'] = $pd['random_key'];
                $arr['title'] = $pd['product']['title'];
                $arr['price'] = $pd['product']['price'];
                $arr['on_sale'] = $pd['product']['on_sale'];
                $arr['sale_id'] = $pd['product']['discount_id'];
                $arr['sale_price'] = $pd['product']['sale_price'];
                $arr['slug'] = $pd['product']['slug'];
                $arr['feature_image'] = $pd['product']['feature_image'];
                $arr['cart_id'] = $pd['cart_id'];
                $arr['product_quantity'] = $pd['product_quantity'];
                $arr['variation_sale_price'] = isset($pd['variation']['price']) ? $pd['variation']['price']: "";
                $arr['product_variation_sale_price'] = isset($pd['variation']['sale_price']) ? $pd['variation']['sale_price']: $arr['variation_sale_price'];
                $arr['variation_id'] = isset($pd['variation']['variation_id']) ? $pd['variation']['variation_id']: "";
                $arr['product_variation_image'] = isset($pd['variation']['product_variation_image']) ? $pd['variation']['product_variation_image']: "";
                $arr['option1'] = isset($pd['variation']['option1']) ? $pd['variation']['option1']:"";
                $arr['attribute1'] = isset($pd['variation']['attribute1']) ? $pd['variation']['attribute1']:"";
                $arr['attribute2'] = isset($pd['variation']['attribute2']) ? $pd['variation']['attribute2']: ""; 
                $arr['attribute3'] = isset($pd['variation']['attribute3']) ? $pd['variation']['attribute3']:"";
                $arr['unit1'] = isset($pd['variation']['unit1']) ? $pd['variation']['unit1']: "";
                $arr['option2'] = isset($pd['variation']['option2']) ? $pd['variation']['option2']: "";
                $arr['option3'] = isset($pd['variation']['option3']) ? $pd['variation']['option3'] : "";
                $arr['unit2'] = isset($pd['variation']['unit2']) ? $pd['variation']['unit2']: "";
                $arr['unit3'] = isset($pd['variation']['unit3']) ? $pd['variation']['unit3']: "";
                $arr['product_variation_quantity'] = isset($pd['variation']['product_variation_quantity']) ? $pd['variation']['product_variation_quantity']: "";
                $arr['productSize_length'] = isset($pd['product']['productSize_length']) ?  $pd['product']['productSize_length'] : "";
                $arr['productSize_width'] = isset($pd['product']['productSize_width']) ? $pd['product']['productSize_width'] : "";
                $arr['productSize_height'] = isset($pd['product']['productSize_height']) ? $pd['product']['productSize_height'] : "";
                $arr['weight'] = isset($pd['product']['weight']) ? $pd['product']['weight'] * $pd['product_quantity']: "";
                $arr['shipping_option_id'] = isset($pd['product']['shipping_option_id']) ? $pd['product']['shipping_option_id']: "";
                $arr['domestic_handling_fee'] = isset($pd['product']['shipping_option']['domestic_handling_fee']) ? $pd['product']['shipping_option']['domestic_handling_fee']: "" ;
                $arr['flat_shipping_rate'] = isset($pd['product']['shipping_option']['flat_shipping_rate']) ? $pd['product']['shipping_option']['flat_shipping_rate']: "";
                $arr['is_domestic_free'] = isset($pd['product']['shipping_option']['is_domesticfree']) ? $pd['product']['shipping_option']['is_domesticfree']: "";
                $arr['profile_name'] = isset($pd['product']['shipping_option']['profile_name']) ? $pd['product']['shipping_option']['profile_name']: "";
                $arr['origin_zipcode'] = isset($pd['product']['shipping_option']['origin_zipcode']) ? $pd['product']['shipping_option']['origin_zipcode']: "";
                $service_expload = isset($pd['product']['shipping_option']['shipping_service']) ? $pd['product']['shipping_option']['shipping_service']: "";
                $all_services = explode(",",$service_expload);
                $arr['shipping_service'] = $all_services;
                $arr['product_type'] = $pd['product']['product_type'];
                $arr['dimention_type'] = $pd['product']['dimention_type'];
                $arr['weight_type'] = $pd['product']['weight_type'];
                // $arr['product_type'] = $pd['product_type'];
                // $arr['shipping_price'] = $shipmentData[$key]['due'];
                // $arr['expected-delivery-date'] = $shipmentData[$key]['expected-delivery-date'];
                $arr['store_country'] = $pd['store']['store_country'];
                $arr['store_state'] = $pd['store']['store_state'];
                $arr['store_city'] = $pd['store']['store_city'];
                $arr['store_state'] = $pd['store']['store_state'];
                $arr['store_wallet_address'] = $pd['store']['wallet_address'];
                $arr['stripe_account_id'] = $pd['store']['account_id'];
                $arr['stripe'] = $pd['store']['stripe'];
                $arr['stripe_account_id'] = $pd['store']['account_id'];
                $arr['store_zip_code'] = isset($pd['product']['shipping_option']['origin_zipcode']) ? $pd['product']['shipping_option']['origin_zipcode']: "";
                $arr['store_contact_info'] = $pd['store']['contact_info'];
                $arr['ship_id'] = "";
                $arr['rate_id'] = "";
                $arr['store_data'] = [];
                $arr['user_info'] = [];
                $arr['store_total'] = "";
                if (!empty($userId)) {
                    if (isset($arr['variation_sale_price'])) {
                        $price = $arr['variation_sale_price'];
                    } else {
                        $price = $arr['price'];
                    }

                    $productTax = 0;
                    $arr['product_tax'] = $productTax;
                }

                $shipService_uspspri = '';
                if (isset($arr['shipping_option_id']) && $arr['shipping_option_id'] != '0') {
                    $query = DB::table('shipping_options')->where('id', $arr['shipping_option_id'])->first();
                    if (isset($query->is_domesticfree) && $query->is_domesticfree == 0) {
                        $shipService = $query->shipping_service;
                        $shipService_uspspri = 'Priority';
                        $handlingFee = !empty($query->domestic_handling_fee) ? $query->domestic_handling_fee : 0;

                            if ($query->flat_shipping_rate > 0 ) {
                          
                             $arr['shipping_method'] = 'flat';
                             $arr['method'] = 'flat';
                            //  $arr['shipping_price'] = (float) $query->flat_shipping_rate + (float) $handlingFee;
                             $arr['shipping_price'] = (float) $query->flat_shipping_rate;
                             } else {   
                               
                            $arr['shipping_method'] = $shipService_uspspri;
                            $arr['method'] = 'calculated';
                            $arr['shipping_price'] = 0;                    

                                // if ($arr['weight'] > 70) {
                                //     $arr['error'] = "This product weight cross limits of Shipping Api Please update your product weight";
                                //     $arr['shipping_price'] = 100;
                                // } 
                        }

                    }else{
                        
                        $arr['shipping_price'] = 0;
                        $arr['shipping_method'] = 'free';
                        $arr['method'] = 'free';
                    }

                } else {

                    if($arr['product_type'] == 'virtual'){
                  
                        $arr['shipping_method'] = ''; 
                        $arr['method'] = 'free';    
                        $arr['shipping_price'] = 0;

                     }
                }

                $arr['expected-delivery-date'] = 0;
                array_push($alldata, $arr);
                // echo 'alldata: <pre>' .print_r($alldata,true). '</pre>';
            }
            foreach ($alldata as $key => $val) {
                $arr2['storeid-' . $val['store_id']][] = $val;
            }
            // echo '$arr2: <pre>' .print_r($arr2,true). '</pre>'; die();
            return $arr2;
        } else {
            return false;
        }
    //   }else{
    //     header('HTTP/1.0 400 Bad Request');
    //     echo 'Your Checkout is empty. Please add the product to your cart again.';
    //     exit;
     }
    }
    public function cartDetailApi($data) {
        $arr = array();
        $arr2 = array();
        $alldata = array();
        if(isset($data['cart_id'])){
            $cartDetail = ProductCart::with(['product', 'variation', 'store', 'product.store', 'product.shippingOption'])->where('cart_id', $data['cart_id'])->where('is_check',1)->get()->toArray();
        }else{
            if($data['guest']){
                $cartDetail = ProductCart::with(['product', 'variation', 'store', 'product.store', 'product.shippingOption'])->where('random_key', $data['id'])->where('is_check',1)->get()->toArray();
            }else{
                $cartDetail = ProductCart::with(['product', 'variation', 'store', 'product.store', 'product.shippingOption'])->where('user_id', $data['id'])->where('is_check',1)->get()->toArray();
            }
        }
        if (!empty($cartDetail)) {
            $prodcutData = $this->objToArray($cartDetail);
            $productweight = "";
           
            foreach ($prodcutData as $key => $pd) {
                $arr['usps_services'] = ['First','Priority','Express'];
                $arr['product_id'] = $pd['product_id'];
                $arr['store_id'] = $pd['store_id'];
                $arr['origin_zip'] = $pd['store']['zip_code'];
                $arr['user_id'] = $pd['user_id'];
                $arr['title'] = $pd['product']['title'];
                $arr['price'] = $pd['product']['price'];
                $arr['on_sale'] = $pd['product']['on_sale'];
                $arr['sale_id'] = $pd['product']['discount_id'];
                $arr['sale_price'] = $pd['product']['sale_price'];
                $arr['slug'] = $pd['product']['slug'];
                $arr['feature_image'] = $pd['product']['feature_image'];
                $arr['cart_id'] = $pd['cart_id'];
                $arr['product_quantity'] = $pd['product_quantity'];
                $arr['variation_sale_price'] = isset($pd['variation']['price']) ? $pd['variation']['price']: "";
                $arr['product_variation_sale_price'] = isset($pd['variation']['sale_price']) ? $pd['variation']['sale_price']: $arr['variation_sale_price'];
                $arr['variation_id'] = isset($pd['variation']['variation_id']) ? $pd['variation']['variation_id']: "";
                $arr['product_variation_image'] = isset($pd['variation']['product_variation_image']) ? $pd['variation']['product_variation_image']: "";
                $arr['option1'] = isset($pd['variation']['option1']) ? $pd['variation']['option1']:"";
                $arr['attribute1'] = isset($pd['variation']['attribute1']) ? $pd['variation']['attribute1']:"";
                $arr['attribute2'] = isset($pd['variation']['attribute2']) ? $pd['variation']['attribute2']: ""; 
                $arr['attribute3'] = isset($pd['variation']['attribute3']) ? $pd['variation']['attribute3']:"";
                $arr['unit1'] = isset($pd['variation']['unit1']) ? $pd['variation']['unit1']: "";
                $arr['option2'] = isset($pd['variation']['option2']) ? $pd['variation']['option2']: "";
                $arr['option3'] = isset($pd['variation']['option3']) ? $pd['variation']['option3'] : "";
                $arr['unit2'] = isset($pd['variation']['unit2']) ? $pd['variation']['unit2']: "";
                $arr['unit3'] = isset($pd['variation']['unit3']) ? $pd['variation']['unit3']: "";
                $arr['product_variation_quantity'] = isset($pd['variation']['product_variation_quantity']) ? $pd['variation']['product_variation_quantity']: "";
                $arr['productSize_length'] = isset($pd['product']['productSize_length']) ?  $pd['product']['productSize_length'] : "";
                $arr['productSize_width'] = isset($pd['product']['productSize_width']) ? $pd['product']['productSize_width'] : "";
                $arr['productSize_height'] = isset($pd['product']['productSize_height']) ? $pd['product']['productSize_height'] : "";
                $arr['weight'] = isset($pd['product']['weight']) ? $pd['product']['weight'] * $pd['product_quantity']: "";
                $arr['shipping_option_id'] = isset($pd['product']['shipping_option_id']) ? $pd['product']['shipping_option_id']: "";
                $arr['domestic_handling_fee'] = isset($pd['product']['shipping_option']['domestic_handling_fee']) ? $pd['product']['shipping_option']['domestic_handling_fee']: "" ;
                $arr['flat_shipping_rate'] = isset($pd['product']['shipping_option']['flat_shipping_rate']) ? $pd['product']['shipping_option']['flat_shipping_rate']: "";
                $arr['is_domestic_free'] = isset($pd['product']['shipping_option']['is_domesticfree']) ? $pd['product']['shipping_option']['is_domesticfree']: "";
                $arr['profile_name'] = isset($pd['product']['shipping_option']['profile_name']) ? $pd['product']['shipping_option']['profile_name']: "";
                $arr['origin_zipcode'] = isset($pd['product']['shipping_option']['origin_zipcode']) ? $pd['product']['shipping_option']['origin_zipcode']: "";
                $service_expload = isset($pd['product']['shipping_option']['shipping_service']) ? $pd['product']['shipping_option']['shipping_service']: "";
                $all_services = explode(",",$service_expload);
                $arr['shipping_service'] = $all_services;
                $arr['product_type'] = $pd['product']['product_type'];
                $arr['dimention_type'] = $pd['product']['dimention_type'];
                $arr['weight_type'] = $pd['product']['weight_type'];
                $arr['store_country'] = $pd['store']['store_country'];
                $arr['store_state'] = $pd['store']['store_state'];
                $arr['store_city'] = $pd['store']['store_city'];
                $arr['store_state'] = $pd['store']['store_state'];
                $arr['store_wallet_address'] = $pd['store']['wallet_address'];
                $arr['stripe_account_id'] = $pd['store']['account_id'];
                $arr['stripe'] = $pd['store']['stripe'];
                $arr['stripe_account_id'] = $pd['store']['account_id'];
                $arr['store_zip_code'] = isset($pd['product']['shipping_option']['origin_zipcode']) ? $pd['product']['shipping_option']['origin_zipcode']: "";
                $arr['store_contact_info'] = $pd['store']['contact_info'];
                $arr['ship_id'] = "";
                $arr['rate_id'] = "";
                $arr['store_data'] = [];
                $arr['user_info'] = [];
                $arr['store_total'] = "";
                if (isset($arr['variation_sale_price'])) {
                    $price = $arr['variation_sale_price'];
                } else {
                    $price = $arr['price'];
                }

                $productTax = 0;
                $arr['product_tax'] = $productTax;

                $shipService_uspspri = '';
                if (isset($arr['shipping_option_id']) && $arr['shipping_option_id'] != '0') {
                    $query = DB::table('shipping_options')->where('id', $arr['shipping_option_id'])->first();
                    if (isset($query->is_domesticfree) && $query->is_domesticfree == 0) {
                        $shipService = $query->shipping_service;
                        $shipService_uspspri = 'Priority';
                        $handlingFee = !empty($query->domestic_handling_fee) ? $query->domestic_handling_fee : 0;

                            if ($query->flat_shipping_rate > 0 ) {
                          
                             $arr['shipping_method'] = 'flat';
                             $arr['method'] = 'flat';
                            //  $arr['shipping_price'] = (float) $query->flat_shipping_rate + (float) $handlingFee;
                             $arr['shipping_price'] = (float) $query->flat_shipping_rate;
                             } else {   
                               
                            $arr['shipping_method'] = $shipService_uspspri;
                            $arr['method'] = 'calculated';
                            $arr['shipping_price'] = 0;    
                        }

                    }else{
                        
                        $arr['shipping_price'] = 0;
                        $arr['shipping_method'] = 'free';
                        $arr['method'] = 'free';
                    }

                } else {

                    if($arr['product_type'] == 'virtual'){
                  
                        $arr['shipping_method'] = ''; 
                        $arr['method'] = 'free';    
                        $arr['shipping_price'] = 0;

                     }
                }

                $arr['expected-delivery-date'] = 0;
                array_push($alldata, $arr);
                // echo 'alldata: <pre>' .print_r($alldata,true). '</pre>';
            }
            foreach ($alldata as $key => $val) {
                $arr2[$val['store_id']][] = $val;
            }
            // echo '$arr2: <pre>' .print_r($arr2,true). '</pre>'; die();
            return $arr2;
        } else {
            return false;
        }
    }
    public function cartDetail_crypto($userCountryId = '', $userStateId = '') {
        $arr = array();
        $arr2 = array();
        $alldata = array();

    
        if (isset($_COOKIE['cartCookieCrypto']) && !empty($_COOKIE['cartCookieCrypto'])) {
            $keyval_crypto = $_COOKIE['cartCookieCrypto'];
        
            $cartDetail = ProductCart::with(['product', 'variation', 'store', 'product.store', 'product.shippingOption'])->where('crypto_random', $keyval_crypto)->get()->toArray();
          
        if (!empty($cartDetail)) {
            $prodcutData = $this->objToArray($cartDetail);
            $productweight = "";

            foreach ($prodcutData as $key => $pd) {
                $arr['usps_services'] = ['First','Priority','Express'];
                $arr['product_id'] = $pd['product_id'];
                $arr['store_id'] = $pd['store_id'];
                $arr['origin_zip'] = $pd['store']['zip_code'];
                $arr['user_id'] = $pd['user_id'];
                $arr['random_key'] = $pd['random_key'];
                $arr['title'] = $pd['product']['title'];
                $arr['price'] = $pd['product']['price'];
                $arr['on_sale'] = $pd['product']['on_sale'];
                $arr['sale_price'] = $pd['product']['sale_price'];
                $arr['slug'] = $pd['product']['slug'];
                $arr['feature_image'] = $pd['product']['feature_image'];
                $arr['cart_id'] = $pd['cart_id'];
                $arr['product_quantity'] = $pd['product_quantity'];
                $arr['variation_sale_price'] = isset($pd['variation']['price']) ? $pd['variation']['price']: "";
                $arr['product_variation_sale_price'] = isset($pd['variation']['sale_price']) ? $pd['variation']['sale_price']: $arr['variation_sale_price'];
                $arr['variation_id'] = isset($pd['variation']['variation_id']) ? $pd['variation']['variation_id']: "";
                $arr['product_variation_image'] = isset($pd['variation']['product_variation_image']) ? $pd['variation']['product_variation_image']: "";
                $arr['option1'] = isset($pd['variation']['option1']) ? $pd['variation']['option1']:"";
                $arr['attribute1'] = isset($pd['variation']['attribute1']) ? $pd['variation']['attribute1']:"";
                $arr['attribute2'] = isset($pd['variation']['attribute2']) ? $pd['variation']['attribute2']: ""; 
                $arr['attribute3'] = isset($pd['variation']['attribute3']) ? $pd['variation']['attribute3']:"";
                $arr['unit1'] = isset($pd['variation']['unit1']) ? $pd['variation']['unit1']: "";
                $arr['option2'] = isset($pd['variation']['option2']) ? $pd['variation']['option2']: "";
                $arr['option3'] = isset($pd['variation']['option3']) ? $pd['variation']['option3'] : "";
                $arr['unit2'] = isset($pd['variation']['unit2']) ? $pd['variation']['unit2']: "";
                $arr['unit3'] = isset($pd['variation']['unit3']) ? $pd['variation']['unit3']: "";
                $arr['product_variation_quantity'] = isset($pd['variation']['product_variation_quantity']) ? $pd['variation']['product_variation_quantity']: "";
                $arr['productSize_length'] = isset($pd['product']['productSize_length']) ?  $pd['product']['productSize_length'] : "";
                $arr['productSize_width'] = isset($pd['product']['productSize_width']) ? $pd['product']['productSize_width'] : "";
                $arr['productSize_height'] = isset($pd['product']['productSize_height']) ? $pd['product']['productSize_height'] : "";
                $arr['weight'] = isset($pd['product']['weight']) ? $pd['product']['weight'] * $pd['product_quantity']: "";
                $arr['shipping_option_id'] = isset($pd['product']['shipping_option_id']) ? $pd['product']['shipping_option_id']: "";
                $arr['domestic_handling_fee'] = isset($pd['product']['shipping_option']['domestic_handling_fee']) ? $pd['product']['shipping_option']['domestic_handling_fee']: "" ;
                $arr['flat_shipping_rate'] = isset($pd['product']['shipping_option']['flat_shipping_rate']) ? $pd['product']['shipping_option']['flat_shipping_rate']: "";
                $arr['is_domestic_free'] = isset($pd['product']['shipping_option']['is_domesticfree']) ? $pd['product']['shipping_option']['is_domesticfree']: "";
                $arr['profile_name'] = isset($pd['product']['shipping_option']['profile_name']) ? $pd['product']['shipping_option']['profile_name']: "";
                $arr['origin_zipcode'] = isset($pd['product']['shipping_option']['origin_zipcode']) ? $pd['product']['shipping_option']['origin_zipcode']: "";
                $service_expload = isset($pd['product']['shipping_option']['shipping_service']) ? $pd['product']['shipping_option']['shipping_service']: "";
                $all_services = explode(",",$service_expload);
                $arr['shipping_service'] = $all_services;
                $arr['product_type'] = $pd['product']['product_type'];
                $arr['dimention_type'] = $pd['product']['dimention_type'];
                $arr['weight_type'] = $pd['product']['weight_type'];
                // $arr['product_type'] = $pd['product_type'];
                // $arr['shipping_price'] = $shipmentData[$key]['due'];
                // $arr['expected-delivery-date'] = $shipmentData[$key]['expected-delivery-date'];
                $arr['store_country'] = $pd['store']['store_country'];
                $arr['store_state'] = $pd['store']['store_state'];
                $arr['store_city'] = $pd['store']['store_city'];
                $arr['store_state'] = $pd['store']['store_state'];
                $arr['store_wallet_address'] = $pd['store']['wallet_address'];
                $arr['stripe'] = $pd['store']['stripe'];
                $arr['store_zip_code'] = isset($pd['product']['shipping_option']['origin_zipcode']) ? $pd['product']['shipping_option']['origin_zipcode']: "";
                $arr['store_contact_info'] = $pd['store']['contact_info'];
                $arr['ship_id'] = "";
                $arr['rate_id'] = "";
                $arr['store_data'] = [];
                $arr['user_info'] = [];
                if (!empty($userId)) {
                    if (isset($arr['variation_sale_price'])) {
                        $price = $arr['variation_sale_price'];
                    } else {
                        $price = $arr['price'];
                    }

                    $productTax = 0;
                    $arr['product_tax'] = $productTax;
                }

                $shipService_uspspri = '';
                if (isset($arr['shipping_option_id']) && $arr['shipping_option_id'] != '0') {
                    $query = DB::table('shipping_options')->where('id', $arr['shipping_option_id'])->first();
                    if (isset($query->is_domesticfree) && $query->is_domesticfree == 0) {
                        $shipService = $query->shipping_service;
                        $shipService_uspspri = 'Priority';
                        $handlingFee = !empty($query->domestic_handling_fee) ? $query->domestic_handling_fee : 0;

                            if ($query->flat_shipping_rate > 0 ) {
                          
                             $arr['shipping_method'] = 'flat';
                             $arr['method'] = 'flat';
                            //  $arr['shipping_price'] = (float) $query->flat_shipping_rate + (float) $handlingFee;
                             $arr['shipping_price'] = (float) $query->flat_shipping_rate;
                             } else {   
                               
                            $arr['shipping_method'] = $shipService_uspspri;
                            $arr['method'] = 'calculated';
                            $arr['shipping_price'] = 0;                    

                                // if ($arr['weight'] > 70) {
                                //     $arr['error'] = "This product weight cross limits of Shipping Api Please update your product weight";
                                //     $arr['shipping_price'] = 100;
                                // } 
                        }

                    }else{
                        
                        $arr['shipping_price'] = 0;
                        $arr['shipping_method'] = 'free';
                        $arr['method'] = 'free';
                    }

                } else {

                    if($arr['product_type'] == 'virtual'){
                  
                        $arr['shipping_method'] = ''; 
                        $arr['method'] = 'free';    
                        $arr['shipping_price'] = 0;

                     }
                }

                $arr['expected-delivery-date'] = 0;
                array_push($alldata, $arr);
            }
            foreach ($alldata as $key => $val) {
                $arr2['storeid-' . $val['store_id']][] = $val;
            }
            return $arr2;
        } else {
            return false;
        }
      }else{
        // header('HTTP/1.0 400 Bad Request');
        return  false;
        
     }
    }

    public function getShippingPrice($productData) {
        $arr2 = array();
        $location = json_decode(file_get_contents('https://ipinfo.io/json'), TRUE);
        $cp = new CanadaPost(
                new CanadaPostCredentials(
                array(
            'username' => '1c857270f513b80e',
            'password' => 'ec6dc8171a2a91b71dda14',
            'customerNumber' => '0008701696',
            'mode' => 'test'
                )
                )
        );
        $origin = CanadaPostShipment::validatePostal('K1A0A4');
        $destination = CanadaPostShipment::validatePostal('K1K4T3');
        foreach ($productData as $pd) {
            if ($origin !== false && $destination !== false) {
                $result = $cp->getRates(new CanadaPostShipment(
                        array(
                    'destination' => array(
                        'domestic' => array(
                            'postal-code' => $origin
                        )
                    ),
                    'origin-postal-code' => $destination,
//                    'parcel-characteristics' => array(
//                        'weight' => 10,
//                        'dimensions' => array(
//                            'width' => 50,
//                            'length' => 20,
//                            'height' => 30
//                        )
//                    )
                    'parcel-characteristics' => array(
                        'weight' => $pd['weight'],
                        'dimensions' => array(
                            'width' => $pd['productSize_length'],
                            'length' => $pd['productSize_width'],
                            'height' => $pd['productSize_height']
                        )
                    )
                        )
                ));
                $arr = array();
                $totalresult = $result['price-quotes']['price-quote'];
                $count = 0;
                foreach ($totalresult as $key => $d) {
                    if ($count == 0) {
                        if (!empty($d['price-details']['due'])) {
                            $arr['due'] = $d['price-details']['due'];
                        }
                        if ($d['service-standard']['expected-delivery-date']) {
                            $arr['expected-delivery-date'] = $d['service-standard']['expected-delivery-date'];
                        }
                        $arr;
                    }
                    $count++;
                }
            }
            array_push($arr2, $arr);
        }
        return $arr2;
    }

    public function deleteCartData() {
        if (Session::has('userData')) {
            $userId = Session::get('userData')['user_id'];
            $this->_cart->where('user_id', '=', $userId)->delete();
        } else {
            return false;
        }
    }

    public function objToArray($arr) {
        $arr = json_encode($arr);
        $res = json_decode($arr, true);
        return $res;
    }

    public function calculateTax($storeStateId = '', $storeCountryiD, $price, $productQuantity, $shipCountryId = '', $shipStateId = '') {
        $userData = getUserById(getCurrentUserId());
        $totalTax = 0;
        $productPrice = $price * $productQuantity;
        $userCountryId = $shipCountryId;
        if ($shipCountryId == '') {
            $userCountryId = isset($userData[0]->user_country_id) ? $userData[0]->user_country_id : '';
        }
        $userStateId = $shipStateId;
        if ($shipStateId == '') {
            $userStateId = isset($userData[0]->user_state_id) ? $userData[0]->user_state_id : '';
        }
        //  If buyer resides inside of canada and seller is inside canada
        if ($storeCountryiD == 2 && $userCountryId != '' && $userCountryId == 2) {
            $query = DB::table('taxation')->select('total_rate', 'province_name')->where('state_id', $userStateId)->first();
            if (!empty($query)) {
                $percentage = $query->total_rate;

                $totalTax = ($percentage / 100) * $productPrice;
            } else {
                $totalTax = 0;
            }
            return $totalTax;
        } //If buyer resides outside of canada and seller is inside canada
        else if ($storeCountryiD == 2 && $userCountryId != '' && $userCountryId == 1) {
            $totalTax = 0;
            return $totalTax;
        } // If buyer resides outside of canada and seller is outside canada
        else if ($storeCountryiD == 1 && $userCountryId != '' && $userCountryId == 1) {
            $totalTax = 0;
            return $totalTax;
        } // If buyer resides inside of canada and seller is outside canada
        else if ($storeCountryiD == 1 && $userCountryId != '' && $userCountryId == 2) {
            $query = DB::table('taxation')->select('total_rate', 'province_name')->where('state_id', $userStateId)->first();
            if (!empty($query)) {
                $percentage = $query->total_rate;
                $totalTax = ($percentage / 100) * $productPrice;
            } else {
                $totalTax = 0;
            }
            return $totalTax;
        } //if state is not present
        else if (empty($userCountryId) && empty($userStateId)) {
            $totalTax = (5 / 100) * $productPrice;
            return $totalTax;
        }
    }

    public function getShippingPrice2($pd) {
        $arr2 = array();
        $location = json_decode(file_get_contents('https://ipinfo.io/json'), TRUE);
        $cp = new CanadaPost(
                new CanadaPostCredentials(
                array(
            'username' => '1c857270f513b80e',
            'password' => 'ec6dc8171a2a91b71dda14',
            'customerNumber' => '0008701696',
            'mode' => 'test'
                )
                )
        );
        $origin = CanadaPostShipment::validatePostal('K1A0A4');
        $destination = CanadaPostShipment::validatePostal('K1K4T3');
        // foreach ($productData as $pd) {
        //if ($pd['weight'] < 30) {
        if ($origin !== false && $destination !== false) {
            $result = $cp->getRates(new CanadaPostShipment(
                    array(
                'destination' => array(
                    'domestic' => array(
                        'postal-code' => $origin
                    )
                ),
                'origin-postal-code' => $destination,
//                    'parcel-characteristics' => array(
//                        'weight' => 10,
//                        'dimensions' => array(
//                            'width' => 50,
//                            'length' => 20,
//                            'height' => 30
//                        )
//                    )
                'parcel-characteristics' => array(
                    'weight' => $pd['weight'],
                    'dimensions' => array(
                        'width' => $pd['productSize_length'],
                        'length' => $pd['productSize_width'],
                        'height' => $pd['productSize_height']
                    )
                )
                    )
            ));
            $arr = array();
            $totalresult = $result['price-quotes']['price-quote'];
            $count = 0;
            foreach ($totalresult as $key => $d) {
                if ($count == 0) {
                    if (!empty($d['price-details']['due'])) {
                        $arr['due'] = $d['price-details']['due'];
                    }
                    if ($d['service-standard']['expected-delivery-date']) {
                        $arr['expected-delivery-date'] = $d['service-standard']['expected-delivery-date'];
                    }
                    $arr;
                }
                $count++;
            }
        }
        array_push($arr2, $arr);
        // }
        // }
        if (!empty($arr2)) {
            return $arr2;
        } else {
            return false;
        }
    }

}
