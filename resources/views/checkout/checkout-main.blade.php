
@extends('layouts.new-main')
<!-- Main Banner Starts -->
@section('content')
@section('title', 'Checkout')
@section('google-analytics')
<script src="https://code.jquery.com/jquery-3.5.1.min.js" crossorigin="anonymous"></script>
<script src="https://js.stripe.com/v3/"></script> 
<link href="{{ mix('css/app.css') }}" rel="stylesheet">
<style>
#desktop-header .row > *{ width:auto;}

</style>


@include('layouts.google-analytics')
@stop

<?php

use App\Repositories\Location\LocationAccess;

$location_obj = new LocationAccess();
$full_name = isset($last_record['full_name']) ? $last_record['full_name'] : '';
$country = isset($last_record['country']) ? $last_record['country'] : '';
$state = isset($last_record['state']) ? $last_record['state'] : '';
$street = isset($last_record['street']) ? $last_record['street'] : '';
$post_code = isset($last_record['post_code']) ? $last_record['post_code'] : '';
$flat_other = isset($last_record['flat_other']) ? $last_record['flat_other'] : '';
$sh_id = isset($last_record['sh_id']) ? $last_record['sh_id'] : '';
?>
<script src="https://www.paypalobjects.com/api/checkout.js"></script>
<?php
if (!getCurrentUserId()) {
    echo '<div class="alert alert-danger container" style="margin-top:20px"><h5 style="text-align:center;" >Please login to continue</h5></div>';
}
if (Session::has('orderData')) {
    $sess = Session::get('orderData');

    $orderData = $sess;
}
$total_amount = isset($sess['total_price']) ? $sess['total_price'] : '';
$orderDatatotal  = $total_amount;
// $total_amount = $orderData['total_price'];
// $total_amount = $orderDatatotal;
$orderDatatotal = $orderDatatotal * 100;
// $orderDatatotal = $orderDatatotal * 100;
// $ercTotal = $orderData['total_price'] / 100;
// echo '$total_amount: <pre>' .print_r($total_amount,true). '</pre>'; die();

$ercTotal = $orderData['total_price'];
$session_data = $alldata;
?>
@if(getCurrentUserId())

<!-- Content Starts -->
<main class="checkout-page steps">

    <div class="large-loader">
        <span>
            <img src=" {{ asset('assets/images/loader-large.svg',env('HTTPS_STATUS')) }}" class="img-fluid" alt="load icon" title="load icon">
        </span>
    </div>
    
    <div class=" container stepwizard">

        <div class="stepwizard-row d-flex justify-content-between">
            <div class="stepwizard-step " id="step1-circle">
                <button type="button" class="btn btn-default btn-circle <?php
                if (Session::has('msg')) {
                    echo 'crnt  pstfil';
                }
                ?>"><i class="fa fa-check" aria-hidden="true"></i></button>
                <p>
                    <!--                    Basket -->
                    Shipping</p>
            </div>
            <div class="stepwizard-step" id="step2-circle">
                <button type="button" class="btn btn-default btn-circle <?php
                if (Session::has('msg')) {
                    echo 'crnt  pstfil';
                }
                ?>"><i class="fa fa-check" aria-hidden="true"></i></button>
                <p>
                    <!--                    Shipping -->

                    Payment</p>
            </div>
            <div class="stepwizard-step" id="step3-circle">
                <button type="button" class="btn btn-default btn-circle <?php
                if (Session::has('msg')) {
                    echo 'crnt  pstfil';
                }
                ?>"><i class="fa fa-check" aria-hidden="true"></i></button>
                <p>
                    <!--                    Payment -->
                    Submit
                </p>
            </div>
            <!--            <div class="stepwizard-step" id="step4-circle">
                            <button type="button" class="btn btn-default btn-circle <?php
            if (Session::has('msg')) {
                echo 'crnt  pstfil';
            }
            ?>" ><i class="fa fa-check" aria-hidden="true"></i></button> disabled="disabled"
                            <p>Submit</p>
                        </div>-->
        </div>
    </div>

    <?php
    $style = 'block';

    if (Session::has('msg')) {
        $style = 'none';
    }
    ?>
    <section class="basket-contianer" style="display:{{$style}}">
        <div class="container">
            <div class="row">
                <div class="col-sm-12">
                    <h2 class="text-center"> Where do you want this shipped?</h2>
                    <?php
                    $results = DB::table('shipping_addresses')
                            ->where('user_id', '=', getCurrentUserId())
                            ->select('*')
                            ->get()
                            ->toArray();
                    $class = 'usrbsc';
                    $style = "display:block;";
                    if (count($results) > 0) {
                        $class = "";
                        $style = "display:none;";
                        ?>
                        <div class="ship-address gray-box">
                            <form action="" method="post">
                                {{ csrf_field() }}
                                <div class="container-shiping clearfix row">
                                    <?php
                                     $user_shipping_address = array();
                                 
                                    $counter = 1;
                                    foreach ($results as $res) { 
                                        $ship_id = isset($res->sh_id) ?$res->sh_id:'';
                                        $user_shipping_address = $res;
                                        $class = '';
                                        if ($counter == 1) {
                                            $class = "checked-checkbox";
                                        }
                                        $country_name = '';
                                        if ($res->country == 1) {
                                            $country_name = 'USA';
                                            $country_code = 'us';
                                        }
                                        if ($res->country == 2) {
                                            $country_name = 'CANADA';
                                            $country_code = 'ca';
                                        }


                                        $state = $location_obj->getStateByStateCode($res->state, $res->country);

                                        $state_name = '';
                                        if (is_array($state) && count($state) > 0) {
                                            $state_name = $state[0]['state_name'];
                                        }
                                        $selectd = '';
                                        if (isset($session_data['address-option-select']) && $session_data['address-option-select'] == $res->sh_id) {
                                            $selectd = 'checked';
                                            echo '<script>jQuery(document).ready(function(){setTimeout(function() {
                                                jQuery("#continue-one").trigger("click");

                                            }, 200);});</script>';
                                        }
                                        ?>
                                        <input type="hidden" name="shipment_id" id="shipment_id" value="{{$ship_id}}">
                                        <div class="payment-list-box <?php echo $class; ?>  col-sm-6">
                                            <input type="radio" required name="address-option-select" {{$selectd}} value="{{$res->sh_id}}" id="address-otion<?php echo $counter; ?>">
                                            <label for="address-otion<?php echo $counter; ?>">
                                                <p>{{$res->full_name}}<br>{{$res->street}}<br>{{$res->post_code}}<br>{{$state_name}}<br>{{$country_name}} </p>
                                                <?php if ($counter == 0) { ?>  <p class="detault-address">Default</p> <?php } ?>
                                            </label>
                                        </div>
                                        <?php
                                        $counter++;
                                    }
                                    ?>

                                </div>
                                <div class="continue-to-next-step text-center">
                                    <input type="submit" id="calculate_shipping" name="submit" value="continue" class="btn btn-primary btn-simple show-loader">
                                </div>
                                <!-- <div class="text-center">
                                <p class="information">Note: Please enter correct Address, City, Zipcode, and State</p>
                                </div> -->
                                <div class="shipping-address-btn d-flex justify-content-between clearfix">
                                    <a href="javascript:;" id="add-another-shipping">+ add another shipping address</a>
                                    <a href="javascript:;" id="close-another-shipping">- close shipping address</a>
                                </div>

                            </form>
                            {{-- <div class="large-loader">
                                <span>
                                    <img src=" {{ asset('assets/images/loader-large.svg',env('HTTPS_STATUS')) }}" class="img-fluid" alt="load icon" title="load icon">
                                </span>
                            </div> --}}
                        </div><!-- ship-address -->
                    <?php } ?>
                    <div class="another-shipping-address" style="<?php echo $style; ?>">
                        <div class="row">
                            <form id="shispping_form" class="col-md-12 <?php echo $class; ?>" action="" method="post">
                                {{ csrf_field() }}

                                <div class="row">
                                    <div class="col-sm-12">
                                        <div class="form-group">
                                            <label>Full Name</label>
                                            <input maxlength="40" value="<?php
                                            if (isset($session_data['full_name'])) {
                                                echo $session_data['full_name'];
                                            }
                                            ?>" type="text" name="full_name" id="full_name" class="form-control required_class">
                                        </div>
                                    </div>


                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <label>Country</label>
                                            <select  class="form-control required_class" id="s_country" name="country" onchange="get_state_by_code(this.value)">
                                                <option vlaue="">Please Select</option>
                                                <?php
                                                $Canada_S = '';
                                                $usa_S = '';
                                                if (isset($session_data['country']) && $session_data['country'] == '1') {
                                                    $usa_S = 'selected';
                                                }
                                                if (isset($session_data['country']) && $session_data['country'] == '2') {
                                                    $Canada_S = 'selected';
                                                }
                                                ?>
                                                <?php
                                                $countries = array();
                                                if (count($countries) > 0) {
                                                    foreach ($countries as $countryy) {
                                                        if ($countryy['country_code'] == $country) {
                                                            $selected = "selected='selected'";
                                                        } else {
                                                            $selected = '';
                                                        }
                                                        echo '<option  value="' . $countryy['country_code'] . '">' . $countryy['country_name'] . '</option>';
                                                    }
                                                }

                                                echo '<option  value="1" ' . $usa_S . '>USA</option>';
                                                echo '<option  value="2" ' . $Canada_S . '>CANADA</option>';
                                                ?>

                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <?php ?>
                                            <label>State</label>

                                            <select class="form-control required_class" id="states_countries" name="state">


                                                <?php
                                                if (isset($session_data['state'])) {
                                                    echo '<script>jQuery(document).ready(function(){setTimeout(function() {
                                                        jQuery("#add-another-shipping").trigger("click");
                                                            jQuery("#continue-one").trigger("click");
                                                        }, 20);});</script>';
                                                    $country_id = $session_data['country'];
                                                    if ($country_id == 2) {
                                                        $country_code = 'CA';
                                                    } else {
                                                        $country_code = 'US';
                                                    }
                                                    $state = $location_obj->getStateByStateCode2($session_data['state'], $country_code);
                                                    $state_name = '';
                                                    if (is_array($state) && count($state) > 0) {
                                                        $state_name = $state[0]['state_name'];
                                                    }
                                                    echo '<option value="' . $session_data['state'] . '">' . $state_name . '</option>';
                                                } else {
                                                    echo '<option vlaue="">Please Select</option>';
                                                }
                                                $states = array();
                                                $selected = '';
                                                if (count($states) > 0) {
                                                    foreach ($states as $statee) {
                                                        if ($statee['state_code'] == $state) {

                                                            $selected = "selected='selected'";
                                                        } else {
                                                            $selected = '';
                                                        }

                                                        echo '<option  value="' . $statee['state_code'] . '">' . $statee['state_name'] . '</option>';
                                                    }
                                                }
                                                ?>

                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-sm-12">
                                        <div class="form-group">
                                            <label>Street</label>
                                            <input maxlength="90" type="text" id="s_street" name="street" class="form-control required_class" value="<?php
                                            if (isset($session_data['street'])) {
                                                echo $session_data['street'];
                                            }
                                            ?>">
                                        </div>
                                    </div>
                                    <div class="col-sm-12">
                                        <div class="form-group">
                                            <label>City</label>
                                            <input maxlength="90" type="text" id="s_city" name="city" class="form-control required_class" value="<?php
                                            if (isset($session_data['city'])) {
                                                echo $session_data['city'];
                                            }
                                            ?>">
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="form-group" id="staticParent">
                                            <label>Postal Code</label>
                                            <input id="s_post_code" onkeypress="return isNumber(event)"  type="text" name="post_code" maxlength="5" class="form-control required_class" value="<?php
                                            if (isset($session_data['post_code'])) {
                                                echo $session_data['post_code'];
                                            }
                                            ?>">

                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <label>Flat/Other </label>
                                            <input maxlength="40" type="text" id="s_flat_other" name="flat_other" class="form-control" value="<?php
                                            if (isset($session_data['flat_other'])) {
                                                echo $session_data['flat_other'];
                                            }
                                            ?>">
                                        </div>
                                    </div>
                                </div>
                               <!-- <input type="submit" name="submit" class="btn" value="save"> -->
                                <input type="submit" name="submit" value="continue" class ="btn btn-primary btn-simple show-loader">
                                <div class="continue-to-next-step text-center">
                                <p class="information">Note: Please enter correct Address, City, Zipcode, and State</p>
                                </div>
                            </form>
                        </div>


                    </div>
                </div><!-- another-shipping-address -->                    <hr class="seprator-line">

                <div class="continue-to-next-step text-center">
                    <a href="javascript:;" id="continue-one" class="btn btn-primary btn-simple" style="display:none">Continue</a>
                </div>
            </div>
        </div>
        <!-- </div> -->
    </section>
    <!-- basket-contianer -->


    <section class="shipping-contianer hide-section">
        <div class="container">
            <div class="row">               
                <div class="col-sm-12">
                    <h2 class="text-center"> Select Your Payment detail</h2>
                    <div class="small-container text-center">                      
                        <div class="payment-list-box">
                            <?php
                            $userDetailWallet = getUserWalletTotal(getCurrentUserId());
                            echo '<input class="primary-btn btn btn-primary  " type="button" name="submit" value="Pay Via MetaMask">';
                            echo '<input class="primary-btn btn btn-primary  " type="button" name="submit" value="Pay Via Stripe">';
                            if ($userDetailWallet >= $orderData['total_price']) {
                                
                                
                               
                                ?>
                                
                                <?php
                                
                                // echo $orderData["total_price"];
                            } 
                            ?>

                              
                            {{-- <div class="row form-group">
                                <div class="col-md-6">

                                    <input type="button" value="Pay via Stripe" class="btn btn-primary pay-via-stripe-btn stripe-button">
                                    
                                </div>
                            </div> --}}
                            {{-- <input type="radio" name="payment-option-select" checked="" value="payment-option-visa" id="payment-option-visa">
                            <label for="payment-option-visa">
                                <span class="only-text-mbt"> Pay with USDC</span>
<!--                                <img src="{{asset('assets/images/visa-card.gif',env('HTTPS_STATUS'))}}">-->
                            </label> --}}
                        </div>
                        <!--                        <div class="payment-list-box">
                                                    <input type="radio" name="payment-option-select" checked="" value="payment-option-paypal" id="payment-option-paypal">
                                                    <label for="payment-option-paypal">
                                                        <img src="{{asset('assets/images/paypal-card.gif',env('HTTPS_STATUS'))}}">
                                                    </label>
                                                </div>
                                                <div class="payment-list-box">
                                                    <input type="radio" name="payment-option-select" checked="" value="payment-option-bitcoin" id="payment-option-bitcoin">
                                                    <label for="payment-option-bitcoin">
                                                        <img src="{{asset('assets/images/s6-card.gif',env('HTTPS_STATUS'))}}">
                                                    </label>
                                                </div>-->

                        {{--<div class="redeem-a-gift card">--}}
                        {{--<a href="javascript:;">Redeem a gift card</a>--}}
                        {{--</div><!-- redeem-a-gift -->--}}
                        </div><!-- small-container -->

                    <div class="continue-to-next-step text-center">
                        <!-- <a href="<?php //echo URL::to('/cart'); ?>" id="back-two" class="btn btn-primary btn-simple">Back</a> -->
                        <a href="javascript:;" id="continue-two"  class="btn btn-primary btn-simple">Continue</a>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- shipping-contianer -->
    <section class="readypayment-contianer hide-section">
        <div class="container">
            <div class="row">
                <div class="col-sm-12">
                    <h2 class="text-center">Your order is ready to  submit</h2>
                    <div class="container-order-list">

                        <?php
//print_rr($orderData); exit;
// if (isset($sess['products']) and count($sess['products']) > 0) {
   
    
    // array_push($orderData , $user_shipping_address);
                // echo '$orderData: <pre>' .print_r($orderData,true). '</pre>'; 
                        $mainArray =  array();
                        $totalStoreShipPrice = 0;
                        foreach ($orderData as $key => $val) {
                            $store = explode('-',$key);
                             $storeId = !empty($store[1]) ? $store[1] :'';
                             $totalStoreShipPrice = isset($val['store_shipping_price']) ? $val['store_shipping_price'] : "";
                            //  $shipping_fullname = $val->full_name;
                            //  echo '$shipping_fullname: <pre>' .print_r($val,true). '</pre>'; 
                            // $location = json_decode(file_get_contents('https://ipinfo.io/json'), TRUE);
                            $counter = 0;
                            $storeTotalPrice = 0 ;
                            $totalShipPrice = 0;
                          
                            $prevValue = null;
                            if (is_array($val) && count($val) > 0) {
                                foreach ($val as $key2 => $val2) {
                                    //echo '$key2: <pre>' .print_r($key2,true). '</pre>'; 

                                    if (is_numeric($key2)) {
                                        $product = getProductById($val2['product_id']);
                                        $mainArray[$storeId][] =  $val2;
                                        // $mainArray[$storeId][$val2['shipping_method']] =  'usps-priority';
                                        if($val2['product_type'] == 'simple' ){
                                            $image = $val2['feature_image'];
                                        }elseif($val2['product_type'] == 'virtual'){
                                            $image = $val2['feature_image'];
                                        }else{
                                            $image = $val2['product_variation_image'];
                                        }

                                        // $image = $val2['feature_image'];
                                        
                                        $slug = $val2['slug'];
                                      
                                        $title = isset($product[0]->title) ? $product[0]->title : '';
                                        $image_url = getAttachmentUrlById((int) $image, 'small');
                                         $shipping_price = isset($val2['shipping_price']) ? $val2['shipping_price'] : '0';
                                         $shipping_method = isset($val2['method']) ? $val2['method'] : '';
                                        //   echo 'Data: <pre>' .print_r($shipping_method,true). '</pre>'; die();
                                         $is_domestic_free = isset($val2['is_domestic_free']) ? $val2['is_domestic_free'] : '0';
                                         $flat_shipping_rate = isset($val2['flat_shipping_rate']) ? $val2['flat_shipping_rate'] : '0';
                                            // echo '$flat_shipping_rate: <pre>' .print_r($flat_shipping_rate,true). '</pre>'; die();
                                        //$shipping_price = 0.40;
                                        $origin_zip = isset($val2['origin_zip']) ? $val2['origin_zip'] : '0';
                                        // $destination_zip = $location['postal'];
                                        $destination_zip = '90001';
                                        $productSize_length =  isset($val2['productSize_length']) ? $val2['productSize_length'] : '0';
                                        $productSize_width =  isset($val2['productSize_width']) ? $val2['productSize_width'] : '0';
                                        $productSize_height =  isset($val2['productSize_height']) ? $val2['productSize_height'] : '0';
                                        $product_weight =  isset($val2['weight']) ? $val2['weight'] : '0';
                                        $weightType =  isset($val2['weight_type']) ? $val2['weight_type'] : '0';
                                        $dimentionType =  isset($val2['dimention_type']) ? $val2['dimention_type'] : '0';
                                        if($shipping_method == 'calculated'){

                                       
                                        if($weightType == 'pound') {
                                                $product_weight_cal = number_format($product_weight * 16);
                                                // $product_weight = round($product_weight_cal,2);
                                            }

                                            if($dimentionType == 'cm') {
                                                $productSize_length = number_format($productSize_length / 2.54);
                                                // $productSize_length = round($productSize_length ,2);

                                                if($productSize_length == '0'){
                                                    $productSize_length = 1;
                                                   }
                                                $productSize_width = number_format($productSize_width / 2.54);
                                                // $productSize_width = round($productSize_width, 2);
                                                if($productSize_width == '0'){
                                                    $productSize_width = 1;
                                                   }
                                                $productSize_height = number_format($productSize_height / 2.54);
                                                // $productSize_height = round($productSize_height ,2);
                                                if($productSize_height == '0'){
                                                    $productSize_height = 1;
                                                   }
                                            
                                            }
                                            else if($dimentionType == 'mm') {
                                               $productSize_length = number_format($productSize_length / 25.4);

                                            //    $productSize_length = round($productSize_length, 2);

                                               if($productSize_length == '0'){
                                                $productSize_length = 1;
                                               }
                                               $productSize_width = number_format($productSize_width / 25.4);
                                            //    $productSize_width = round($productSize_width , 2);
                                               if($productSize_width == '0'){
                                                $productSize_width = 1;
                                               }
                                               $productSize_height = number_format($productSize_height / 25.4);
                                            //    $productSize_height = round($productSize_height , 2);
                                               if($productSize_height == '0'){
                                                $productSize_height = 1;
                                               }
                                            }

                                        }
                                        $shipping_option_id =  isset($val2['shipping_option_id']) ? $val2['shipping_option_id'] : '0';
                                        $product_tax = isset($val2['product_tax']) ? $val2['product_tax'] : '0';

                                        $product_variant_price = isset($val2['variation_sale_price']) ? $val2['variation_sale_price'] : $val2['price'];
                                        $product_type = isset($val2['product_type']) ? $val2['product_type'] : "";

                                        if($product_type == 'variant'){
                                        $product_price = isset($val2['variation_sale_price']) ? $val2['variation_sale_price'] : $val2['price'];
                                        }else{
                                        $product_price = isset($val2['price_cad']) ? $val2['price_cad'] : $val2['price'];
                                            
                                        }
                                    
                                        $shipping_service = isset($val2['shipping_service']) ? $val2['shipping_service'] : '';
                                        $proPrice = ($product_price * $val2['product_quantity']) + $shipping_price;
                                        $proPriceReal = ($product_price * $val2['product_quantity']);
                                        $product_quantity = isset($val2['product_quantity']) ? $val2['product_quantity']: "1";
                                        $note = '';
                                        $totalShipPrice = $totalShipPrice + $totalStoreShipPrice;
                                        $attribute1 = isset($val2['attribute1']) ? $val2['attribute1']: "";
                                        $attribute2 = isset($val2['attribute2']) ? $val2['attribute2']: "";
                                        $attribute3 = isset($val2['attribute3']) ? $val2['attribute3']: "";
                                        if ($shipping_price == 0 && $shipping_method == 'free') {
                                            $note = '<p> Note: shipping cost for this product is not included. </p>';
                                        }
                                        ?>
                                        <div class="list-repeat-mbt" id="list_{{$val2['product_id']}}">
                                            <div class="image-cntainer">
                                                <a href="<?php echo URL::to('/product/' . $slug); ?>"> <img src="<?php echo $image_url; ?>" alt="{{$val2['title']}}" title="{{$val2['title']}}" class="img-fluid"></a>
                                            </div>
                                            <div class="description-order-text">
                                                <div class="body-text-left">
                                                    <h3>{{$title}}</h3>
                                                    <?php
                                                    if (isset($val2['option1']) && isset($val2['option1']) && $val2['option1'] != '') {
                                                        echo '<p>'; 
                                                        // .ucfirst($val2['attribute1']).': '.$val2['option1'].' '.$val2['unit1'].', ' .ucfirst($val2['attribute2']).': '.$val2['option2'].' '.$val2['unit2']; 
                                                        echo '<span class="option-clr">';
                                                        echo (ucfirst($val2['option1'])) ? "&nbsp;" . ucfirst($val2['attribute1']).': '.$val2['option1'] : "";
                                                        echo (ucfirst($val2['unit1'])) ? $val2['unit1'] : "";
                                                        if ($val2['option2']) {
                                                            echo ', ';
                                                        }
                                                        echo '</span>';

                                                        echo '<span class="option-kilogram">';
                                                        echo (ucfirst(ucfirst($val2['option2']))) ? ucfirst($val2['attribute2']).': '.$val2['option2'] : "";
                                                        echo (ucfirst($val2['unit2'])) ? ' '.$val2['unit2'] : "";
                                                        if (isset($val2['option3'])) {
                                                            echo ', ';
                                                        }
                                                        echo '</span>';
                                                        echo '<span class="option-kilogram">';
                                                        echo (ucfirst(ucfirst($val2['option3']))) ? ucfirst($val2['attribute3']).': '.$val2['option3'] : "";
                                                        echo (ucfirst($val2['unit3'])) ? ' '.$val2['unit3'] : "";
                                                        echo '</span>';
                                                       
                                                        '</p>';
                                                    }
                                                    ?>
                                                    <p> {{$val2['product_quantity']}} item</p>
                                                    
                                                    <p><span class="zit-price real_product_price" data-real-price="{{number_format(round($product_price * $val2['product_quantity'],4),2)}}">USDC {{number_format(round($product_price * $val2['product_quantity'],4),2)}}</span></p>
                                                   
                                                    <?php 
                                                     
                                                    if ($note == '') {  
                                                     if($shipping_method =='flat' ){ ?>
                                                               
                                                               
                                                        <p><b>Flat Shipping:</b> 
                                                        <input type="hidden" name="shipping_data" class="get_data_calulate_ship_flatship" value="{{$storeId}}_{{$val2['product_id']}}">
                                                        <span class="zit-price shipping_price_calculated_flatshipping" data-ship-price="{{number_format($flat_shipping_rate,2)}}" id="shipping_price_calculated">USDC {{number_format($flat_shipping_rate,2)}} </span>
                                                            <?php }else{ ?>

                                                        <p><b>Shipping:</b> 
                                                        <input type="hidden" name="shipping_data" class="get_data_calulate_ship" value="Priority_{{$storeId}}_{{$val2['product_id']}}_{{$product_weight}}_{{$origin_zip}}_{{$shipping_option_id}}_{{$productSize_length}}_{{$productSize_width}}_{{$productSize_height}}_{{$product_quantity}}_{{$proPriceReal}}_{{$title}}_{{$key2}}">
                                                        <span class="zit-price shipping_price_calculated" data-ship-price="{{number_format($shipping_price,2)}}" id="shipping_price_calculated">USDC {{number_format($shipping_price,2)}} </span>
                                                        </p>
                                                     <?php }
                                                    } else {
                                                       
                                                        echo $note;
                                                    }
                                                    ?>
                                             
                                                    <!-- <p>Tax: <span class="zit-price">USDC {{number_format($product_tax,2)}}</span></p> -->
                                                </div>
                                                <?php if ($prevValue === null) { 
                                                    $prevValue = $val2['product_id'];  ?>
                                                    <div class="pricea-text-right">
                                                        <span> <span class="text-uppercase zit-price">USDC {{number_format(round($product_price,4),2)}}</span></span>
                                                        <?php if($val2['product_type'] != 'virtual'){ 
                                                        if($is_domestic_free == '0'){
                                                        if($flat_shipping_rate == '0'){
                                                    
                                                        ?>
                                                        <h6>Shipping service</h6> 
                                                        <select class="custom-select custom-select-sm shipping_service_checkout"  id="shipping_service">
                                                    
                                                        <?php 
                                                        foreach($shipping_service as $service){ ?>

                                                        <option class="{{ $service}}" value="{{$service}}_{{$storeId}}_{{$val2['product_id']}}_{{$product_weight}}_{{$origin_zip}}_{{$shipping_option_id}}_{{$productSize_length}}_{{$productSize_width}}_{{$productSize_height}}_{{$product_quantity}}_{{$proPriceReal}}_{{$title}}_{{$key2}}" disabled>{{$service}}</option>
                                                        <?php } } }
                                                        ?>    
                                                    
                                                        </select> 
                                                        <?php } ?>
                                                    </div>

                                                 
                                                <?php  }  else if ($prevValue === $val2['product_id']) {
                                                    // Do something if the value is repeating
                                                    // echo 'second';
                                                    // echo 'Value ' . $val2['product_id'] . ' is repeating<br>';
                                                  } else {
                                                    $prevValue = $val2['product_id'];?>
                                                     <div class="pricea-text-right">
                                                        <span> <span class="text-uppercase zit-price">USDC {{number_format(round($product_price,4),2)}}</span></span>
                                                        <?php if($val2['product_type'] != 'virtual'){ 
                                                        if($is_domestic_free == '0'){
                                                        if($flat_shipping_rate == '0'){
                                                    
                                                        ?>
                                                        <h6>Shipping service</h6> 
                                                        <select class="custom-select custom-select-sm shipping_service_checkout"  id="shipping_service">
                                                    
                                                        <?php 
                                                        foreach($shipping_service as $service){ ?>
                                                        <option  class="{{ $service}}"  value="{{$service}}_{{$storeId}}_{{$val2['product_id']}}_{{$product_weight}}_{{$origin_zip}}_{{$shipping_option_id}}_{{$productSize_length}}_{{$productSize_width}}_{{$productSize_height}}_{{$product_quantity}}_{{$proPriceReal}}_{{$title}}_{{$key2}}" disabled>{{$service}}</option>
                                                        <?php } } }
                                                        ?>    
                                                    
                                                        </select> 
                                                        <?php } ?>
                                                    </div>
                                               <?php   }
                                                ?>
                                              
                                            </div>
                                        </div><!-- list-repeat-mbt -->
                                        <?php
                                        $storeTotalPrice = $storeTotalPrice + $product_price;
                                        
                                    }
                                }

                            }
                            $counter++;
                           if(!empty($storeId)) {
                            // $mainArray[$storeId]['store_total_price'] =  $storeTotalPrice;
                           $mainArray[$storeId]['store_ship_price'] =  $totalShipPrice;

                           $mainArray[$storeId]['store_total_price'] = $totalShipPrice + $storeTotalPrice;
                        //    $mainArray[$storeId]['store_ship_price'] =  $totalStoreShipPrice;
                        //    $mainArray[$storeId]['store_total_price'] = $totalStoreShipPrice + $storeTotalPrice;
                        //    $mainArray[$storeId]['store_ship_price'] =  $totalStoreShipPrice;
                           }
                           // $mainArray[$storeId]['store_total_price'] =  '13';
                        }
                        //   echo 'Data: <pre>' .print_r($mainArray,true). '</pre>';
//}                     
                        ?>
                        <script>

                            var orderData = <?php echo json_encode($mainArray); ?>;
                        </script>
                      
                        <div class="total-cost">
                            <?php
                            // echo 'subtotal'.$total_amount.' </ br>';
                            // echo 'total'.$ercTotal;
                            ?>
                            <p class="text-right"><small>Sub Total:</small> <b><small class="zit-price subtotal_cal">USDC {{number_format($total_amount,2)}}</small></b> </p>
                            <p class="text-right">Total: <b><span class="zit-price grandtotal_cal">USDC {{ $ercTotal }}</span></b> </p>
                            <input class="primary-btn btn btn-primary" id="usdcprice"  hidden  price="{{$ercTotal}}"   type="button" name="submit" value="Pay Via MetaMask">
                            {{-- <input class="primary-btn btn btn-primary" id="ercprice"  hidden  price="{{$ercTotal}}"   type="button" name="submit" value="Pay Via MetaMask"> --}}
                          
                        </div><!-- total-cost -->
                        <div class="submit-order text-center">
                            <a href="javascript:;" id="back-three" class="btn btn-primary btn-simple bck-step">Back</a>
                            <a href="javascript:;"  id="continue-three" class="btn btn-primary btn-simple" style="display:none">Continue</a>


                            <?php
                            $user_info = getUserById(getCurrentUserId());
                            $user_email = '';

                            if (isset($user_info->email)) {
                                $user_email = $user_info->email;
                            }

                            //print_rr($orderData);
                            ?>
                            @if($orderData['total_price']!='')
                            <script src="https://code.jquery.com/jquery-1.12.4.js"></script>

                            <h3 class="mb-2"><b>Select Payment Method</b></h3>
                            <div class="row">
                                <div class="col" style="background-color:#f3f3f3; padding:20px; margin:10px"><h6 style="margin-bottom:30px; font-size:18px">Pay Via USDC</h6>
                                <form action="/meta-mask" method="POST" id="orderDataSend">
                              
                               
                              {{ csrf_field() }}
                              {{-- <input type="hidden" id="credit_purchase" name="credit_user_purchase" value="yes">
                               --}}
                               <input type="hidden" id="shipping_data" name="shipping_data">
                               
                               <input type="hidden" id="orderData" name="orderdata">
                              
                              <?php
                          
                           
                             
                              //   echo '<input class="primary-btn btn btn-primary finalsubmit" type="submit" id="createOrder" name="submit2" value="Pay Via Stripe">';
                                 echo '<div id="polygon"> </div>';
                          
                              echo '<br /></br /><a style="display:none" href="/cart">Update Your Cart</a>';
                              ?>
                          </form> 
                            </div>
                                <div class="col" style="background-color:#f3f3f3; padding:20px; margin:10px"><h6 style="margin-bottom:30px; font-size:18px">Pay via credit/debit card</h6>

                                <form action="/stripe-response" method="POST" id="payment-form">
                                @csrf
                                <input type="hidden" name="stripe_email" class="form-control" id="stripe_email" value="<?php echo $user_email; ?>">
                                <div class="form-group">
                                    <div id="card-element"></div>
                                    <div id="card-errors" role="alert"></div>
                                </div>
                               
                                    <p id="amount-calculated" style="margin:0">USD: <b><?php echo $total_amount; ?></b></p>  
                               
                                <input type="hidden" name="total_amount_ship" id="total_amount_ship" value="<?php echo $orderDatatotal; ?>">
                                 <input type="hidden" id="shipping_data_stripe" name="shipping_data_stripe">
                                 <input type="hidden" id="orderData_stripe" name="orderdata">
                                <button type="submit" class="btn btn-primary">Pay Via Stripe</button>
                            </form>
                            
                            </div>  
                            </div>
                              
                            
                            
                            <!-- <form action="/stripe-response" method="POST" id="stripe-account-form" class="stripe_account_form">
                                
                            <div id="stripe_account_form">
                                <?php // if($orderData['Shipping_method'] == 'flat'){ ?>
                                <script
                                    src="https://checkout.stripe.com/checkout.js" class="stripe-button" id="stripe_button_payment"
                                    data-key="{{env('STRIPE_API_KEY_PUBLISHABLE')}}"
                                    data-amount="<?php echo $orderDatatotal; ?>"
                                    data-name="MallShark"
                                    data-description="Checkout Charge"
                                    data-image="https://stripe.com/img/documentation/checkout/marketplace.png"
                                    data-email="<?php echo $user_email; ?>"
                                    data-currency="usd"
                                    data-locale="auto">
                                </script>
                                <?php // } ?>
                                </div>
                                {{ csrf_field() }}
                                {{-- <input type="hidden" id="credit_purchase" name="credit_user_purchase" value="yes">
                                    --}}
                                    <input type="hidden" name="total_amount_ship" id="total_amount_ship" value="<?php echo $orderDatatotal; ?>">
                                 <input type="hidden" id="shipping_data_stripe" name="shipping_data_stripe">
                                 <input type="hidden" id="orderData_stripe" name="orderdata">
                                 <input class="primary-btn btn btn-primary finalsubmit" type="submit" name="submit2" value="Pay Via Stripe">
                                <?php
                              // $userDetailWallet = getUserWalletTotal(getCurrentUserId());
                             
                            //    if ($userDetailWallet >= $orderData['total_price']) {
                                   //echo '<input class="primary-btn btn btn-primary finalsubmit" type="submit" name="submit2" value="Pay Via Stripe">';
                            //    } else {
                            //         echo '<h3 style="color:red">You dont have enough zits in your wallet to continue. Your wallet total is: ' . round($userDetailWallet, 4) . ' please click <a href="/wallet">here</a> to purchase more zits. </h3>';
                            //   }
                                // echo '<br /></br /><a href="/cart">Update Your Cart</a>';
                                ?>
                            </form> -->



                            
                                <script src="https://js.stripe.com/v3/"></script>
                                <script>
                                    
                                        // Create a Stripe client
                                        var stripe = Stripe('<?php echo env('STRIPE_API_KEY_PUBLISHABLE') ?>');
                                        var elements = stripe.elements();
                                        var card = elements.create('card');
                                    
                                        card.mount('#card-element');
                                        // Handle form submission
                                        var form = document.getElementById('payment-form');
                                        form.addEventListener('submit', function(event) {
                                            event.preventDefault();

                                            stripe.createToken(card).then(function(result) {
                                                if (result.error) {
                                                    // Inform the user if there was an error
                                                    var errorElement = document.getElementById('card-errors');
                                                    errorElement.textContent = result.error.message;
                                                } else {
                                                    stripeTokenHandler(result.token);
                                                }
                                            });
                                        });
                                        function stripeTokenHandler(token) {
                                        // Insert the token ID into the form so it gets submitted to the server
                                        var form = document.getElementById('payment-form');
                                        var hiddenInput = document.createElement('input');
                                        hiddenInput.setAttribute('type', 'hidden');
                                        hiddenInput.setAttribute('name', 'stripeToken');
                                        hiddenInput.setAttribute('value', token.id);
                                        form.appendChild(hiddenInput);

                                        // Submit the form
                                        form.submit();
                                        }
                                </script>
                            <script>
                                    $(document).ready(function () {
                                    // setTimeout(function () {
                                    //     $(".stripe-button-el").click();
                                    // }, 1000);

                                         });
                                // jQuery(document).on('click', '.finalsubmit', function () {
                                //     jQuery(this).hide();
                                // });
                                </script>
                                 <style>
                                    .stripe-button-el{
                                        display:none;
                                    }
                                    </style>

                            @endif

                        </div>

                    </div>
                    <!-- container-order-list -->
                </div>
            </div>
        </div>

    </section>
    <?php
    $style = 'none';
    $message = '';
    if (Session::has('msg')) {
        $message = Session::pull('msg');

        if ($message == 'Your order is submitted') {
            $analytics_data = Session::pull('google_analytics');
            if (count($analytics_data) > 0) {
                foreach ($analytics_data as $ann) {
                    $data = json_encode($ann);
                    ?>
                    <script>

                        gtag('event', 'purchase',<?php echo $data; ?>);
                    </script>
                    <?php
                }
            }
            Session::pull('orderData');
        }
        $style = 'block';
    }
    ?>
    <section class="readypayment-contianer">

        <div class="shipping_amount" style="display:{{$style
             }}">


            <div class="container">
                <div class="row">
                    <div class="col-sm-12">
                        <div class="success-message-sec">
                            <h3 class="text-center">Thankyou! Your order is received, If you have purchased any virtual product please check orders section.</h3>
                            <!-- <h3 class="text-center">{{$message}}</h3> -->
                        </div>
                    </div>
                    <div class="submit-order text-center" style="    display: block;
                         clear: both;
                         float: none;
                         margin: 40px auto;">

                        <a href="{{ URL::to('/')  }}"  class="btn btn-primary btn-simple">Continue shopping</a>


                    </div>
                </div>
                <div class="row" style="display:none">
                    <div class="static-image text-center  mt-50 mb-50">

                        <img src="{{ asset('assets/images/success-image.png',env('HTTPS_STATUS')) }}" alt="success icon" title="success icon" class="img-fluid" >
                    </div>
                    <div class="col-sm-12">
                        <br />
                        <h2 class="text-center">Your order Summery</h2>
                        <div class="container-order-list">

                            <?php
                            if (isset($sess['products']) and count($sess['products']) > 0) {
                                foreach ($sess['products'] as $key => $val) {
                                    $product = getProductById($val['product_id']);

                                    $image = $val['feature_Image'];
                                    $title = isset($product[0]->title) ? $product[0]->title : '';

                                    $image_url = getAttachmentUrlById($image, 'small');
                                    ?>
                                    <div class="list-repeat-mbt">
                                        <div class="image-cntainer">
                                            <a href="https://placeholder.com"> <img src="<?php echo $image_url; ?>" onerror="this.src='{{url('/assets/images/placeholder.jpg')}}'" alt="placeholer icon" class="img-fluid"></a>
                                        </div>
                                        <div class="description-order-text">
                                            <div class="body-text-left">
                                                <h3>P{{$title}}</h3>
                                                <p> {{$val['product_quantity']}} item</p>
                                                <p>USDC{{$val['price']/$val['product_quantity']}}</p>
                                            </div>
                                            <div class="pricea-text-right">
                                                <span>$<?php echo $val['price']; ?></span>
                                            </div>
                                        </div>
                                    </div><!-- list-repeat-mbt -->
                                    <?php
                                }
                            }
                            ?>

                            <div class="total-cost">
                                <p class="text-right">Total: <b>USDC {{$total_amount}}</b> </p>
                            </div><!-- total-cost -->


                        </div>
                        <!-- container-order-list -->
                    </div>
                </div>
            </div>
        </div>
    </section>







</main>
<?php
if (isset($session_data['address-option-select'])) {
    //
} else {
    ?>
    <script>
        jQuery(document).ready(function () {
           // alert('fdsfsd');
            
            $('#address-otion1').attr('checked', 'checked');
        });
    </script>
    <?php
}
?>
<script>
     function loadStripeCheckout_Change(amount) {

        $('#stripe_button_payment').remove();
        const stripeCheckoutScript = $('<script>')
            .attr('src', 'https://checkout.stripe.com/checkout.js')
            .addClass('stripe-button')
            .attr('id', 'stripe_button_payment')
            .attr('data-key', '<?php echo env('STRIPE_API_KEY_PUBLISHABLE') ?>')
            .attr('data-amount', amount)
            .attr('data-name', 'MallShark')
            .attr('data-description', 'Checkout Charge')
            .attr('data-image', 'https://stripe.com/img/documentation/checkout/marketplace.png')
            .attr('data-email', '{{ $user_email }}')
            .attr('data-currency', 'USD')
            .attr('data-locale', 'auto');

        const form = $('#stripe_account_form');
        form.empty();
        form.html(stripeCheckoutScript);
}
        jQuery(document).ready(function () {

          
       




            var shipment_data ;
            // var shipment_id;
            $('#orderData').val(JSON.stringify(orderData));
            $('#orderData_stripe').val(JSON.stringify(orderData));
                // var flatShipping = $(".shipping_price_calculated_flatshipping").attr('data-ship-price');
                // var realprice = $(".real_product_price").attr('data-real-price');
                // var total = parseFloat(flatShipping) + parseFloat(realprice);
                // console.log("ready_total", total)
                // console.log("ready_realprice", realprice)
                // $('.subtotal_cal').html("USDC "+ parseFloat(total).toFixed(2));
                // $('.grandtotal_cal').html("USDC "+ parseFloat(total).toFixed(2));
            console.log("ordersessionData", orderData);
           // alert('fdsfsd');
            $('.shipping_service_checkout').on('change',function () {
                 shipment_data = $('#shispping_form').serialize();
                 shipmentData = shipment_data.indexOf('=&') > -1;

                if(shipmentData == true){
                    shipment_data = $('#address-otion1').val();
                }

                // console.log("shipment_data", shipment_data);
                
                var that = $(this);
                var val = $(this).val().split("_");
                // console.log("val", val);
                var value = val[0];
                var storeId = val[1];
                var productId = val[2];
                var weight = val[3];
                var origin_zip = val[4];
                var shipping_option_id = val[5];
                var product_length = val[6];
                var product_width = val[7];
                var product_height = val[8];
                var product_quantity = val[9];
                var product_price = val[10];
                var product_title = val[11];
                var  product_key = val[12];
               
                $.ajax({
                    url: '/calculate_service_change',
                    type: "POST",
                    data: {value: value,storeId:storeId, origin_zip:origin_zip,shipping_option_id: shipping_option_id,weight:weight, product_length:product_length,
                        product_width:product_width,product_height:product_height,product_quantity: product_quantity, 
                        product_price:product_price,product_title:product_title, shipment_data:shipment_data, _token: '{{ csrf_token() }}'},
                    success: function (data) {
                    //    var total= $(".subtotal_cal").text();
                    //    var subtotal = total+data;
                        console.log("data",data);
                    //    var shipping_data = $(".subtotal_cal").text(subtotal);
                      $(that).parent().parent().find('.shipping_price_calculated').html(data.rate);
                      $(that).parent().parent().find('.shipping_price_calculated').attr('data-ship-price',data.rate);
                        var shipTotalPrice = 0;
                        var realTotalPrice = 0;
                        var flatShipping = 0;
                        $('.shipping_price_calculated').each(function( key, value ) {
                            shipTotalPrice = parseFloat(shipTotalPrice) + parseFloat($(this).attr('data-ship-price'));
                          //  console.log('valeu',$(this).attr('data-ship-price'));
                        });
                        $('.real_product_price').each(function( key, value ) {
                            realTotalPrice = parseFloat(realTotalPrice) + parseFloat($(this).attr('data-real-price'));
                           // console.log('valeu',$(this).attr('data-real-price'));
                        });
                       
                        $('.shipping_price_calculated_flatshipping').each(function( key, value ) {
                            flatShipping = parseFloat(flatShipping) + parseFloat($(this).attr('data-ship-price'));
                        });
                       // console.log('product id',productId);
                      //  console.log('store id',storeId);
                        var grandTotal_integer = shipTotalPrice + realTotalPrice + flatShipping;

                        var grandTotal = grandTotal_integer.toFixed(2);

                        orderData[storeId][product_key].shipping_price = data.rate;
                        orderData[storeId][product_key].shipping_method = value;
                        orderData[storeId].shipping_method = value;
                        orderData[storeId].store_ship_price = shipTotalPrice;
                        orderData[storeId].store_total_price = grandTotal;
                        orderData[storeId][product_key].ship_id = data.ship_id;
                        orderData[storeId][product_key].rate_id = data.rate_id;
                        orderData[storeId][product_key].store_data = data.store_data;
                        orderData[storeId][product_key].user_info = data.user_info;
                        orderData[storeId].ship_id = data.ship_id;
                        orderData[storeId].rate_id = data.rate_id;



                        $('#orderData').val(JSON.stringify(orderData));
                        $('#orderData_stripe').val(JSON.stringify(orderData));

                        console.log('===== first start ======');
                        $('.subtotal_cal').html("USDC "+grandTotal);
                        $('.grandtotal_cal').html("USDC "+grandTotal);
                        $('#amount-calculated').html("USDC "+grandTotal);
                        $("#usdcprice").attr('price', grandTotal);
                        var stripe_total = grandTotal*100;
                        // loadStripeCheckout_Change(stripe_total);
                        $(".finalsubmit").show();
                                $("#total_amount_ship").val(stripe_total);
                    //   var stripe=  $('#stripe_button_payment').attr('data-amount',grandTotal);
                    
                        console.log('===== first end =======');



           
                    // orderData.forEach((item) => {
                    //     console.log('item',item);
                    // });
                        // if (data.success == 1) {
                        //     selector.closest('tr').hide('slow');
                        //     toastr.success(data.msg, '@lang('app.success')', toastr_options);
                        // }
                    }
                });

            }); 
          
            $("#orderDataSend").submit(function(e) {

                e.preventDefault(); // avoid to execute the actual submit of the form.

                var form = $(this);
                var actionUrl = form.attr('action');
                var orderdata = orderData;
        
                 var fdata = {};
                 fdata['order_info'] = orderdata;
                 fdata['formData'] = $('#shipping_data').val();
                 fdata['_token'] = '{{ csrf_token() }}';
                    $.ajax({
                        type: "POST",
                        url: actionUrl,
                        data: fdata, 
                        success: function(data)
                        {
                            swal("Success!", "Created Order. if you purchased virtual product please check order section Thank you.", "success");
                            var url = '{{Request::root()}}';
                               
                            setTimeout(function(){
                                window.location.href = url;
                            }, 3000);
                        }
                    });

                

            });
        });
    </script>
<script>
    //onkeypress="return isNumber(event)"
    function isNumber(evt) {
        var iKeyCode = (evt.which) ? evt.which : evt.keyCode;
        if (iKeyCode != 46 && iKeyCode > 31 && (iKeyCode < 48 || iKeyCode > 57)) {
            return false;
        }
        return true;
    }
    $(function () {
        $('#staticParentt').on('keydown', '#s_post_code', function (e) {
            -1 !== $.inArray(e.keyCode, [46, 8, 9, 27, 13, 110, 190]) || (/65|67|86|88/.test(e.keyCode) && (e.ctrlKey === true || e.metaKey === true)) && (!0 === e.ctrlKey || !0 === e.metaKey) || 35 <= e.keyCode && 40 >= e.keyCode || (e.shiftKey || 48 > e.keyCode || 57 < e.keyCode) && (96 > e.keyCode || 105 < e.keyCode) && e.preventDefault()
        });
    })
    var data2;
    jQuery(document).on('click', '#continue-two', function () {
        //alert('called');
        if (jQuery('#shispping_form').hasClass('usrbsc')) {
            full_name = jQuery('#full_name').val();
            s_country = jQuery('#s_country').val();
            states_countries = jQuery('#states_countries').val();
            s_street = jQuery('#s_street').val();
            s_post_code = jQuery('#s_post_code').val();
            s_flat_other = jQuery('#s_flat_other').val();
            city = jQuery('#s_city').val();
            data2 = '&full_name=' + full_name + '&s_country=' + s_country + '&city=' + city + '&states_countries=' + states_countries + '&s_street=' + s_street + '&s_post_code=' + s_post_code + '&s_flat_other=' + s_flat_other;
            var data3 = $('#shispping_form').serialize();
             console.log("data2",data2);
            console.log("city check",city);
            jQuery('#shipping_data').val(data2);
            jQuery('#shipping_data_stripe').val(data2);
        } else {
            val = jQuery('input[name=address-option-select]:checked').val();
            data2 = '&shipping_id=' + val;
            jQuery('#shipping_data').val(data2);
            jQuery('#shipping_data_stripe').val(data2);
        }
    });
    $(document).ready(function () {

        $('#step1-circle button').addClass('pstfil');
        $('.payment-list-box input:radio').click(function () {
            var parentCheck = $('.payment-list-box input:not(:checked)').parent().removeClass("checked-checkbox");
            $('.payment-list-box input:checked').parent().addClass("checked-checkbox");
        });

        $('#close-another-shipping').hide();
        $('#add-another-shipping').click(function () {
            $('.another-shipping-address').show();
            jQuery('#shispping_form').addClass('usrbsc');
            $('#close-another-shipping').show();
            $(this).hide();

        });
        $('#close-another-shipping').click(function () {
            $('.another-shipping-address').hide();
            $('#add-another-shipping').show();
            jQuery('#shispping_form').removeClass('usrbsc');
            $(this).hide();
        });

        $('#shispping_form').on('submit', function (event) {
            jQuery('.usrbsc input').each(function () {
                jQuery(this).removeClass('hass_error');
            });
            has_error = false;
            jQuery('.usrbsc .required_class, .usrbsc select').each(function () {
                if (jQuery(this).val() == '') {
                    jQuery(this).addClass('hass_error');
                    has_error = true;
                }

            });
            jQuery('.usrbsc select').each(function () {
                var $option = jQuery(this).find('option:selected');

                if ($option.val() == '' || $option.val() == 'Please Select') {
                    jQuery(this).addClass('hass_error');
                    has_error = true;


                }

            });
            if (has_error) {
                jQuery(".large-loader").hide();

                event.preventDefault();

                return false;
            }
        });
        $('#continue-one').click(function (event) {
            jQuery('.usrbsc required_class').each(function () {
                jQuery(this).removeClass('hass_error');
            });
            has_error = false;
            jQuery('.usrbsc required_class, .usrbsc select').each(function () {
                if (jQuery(this).val() == '') {
                    jQuery(this).addClass('hass_error');
                    has_error = true;


                }

            });
            jQuery('.usrbsc select').each(function () {
                var $option = jQuery(this).find('option:selected');

                if ($option.val() == '' || $option.val() == 'Please Select') {
                    jQuery(this).addClass('hass_error');
                    has_error = true;


                }

            });
            if (has_error) {


                event.preventDefault();

                return false;
            }
            $(this).parentsUntil('.basket-contianer').find('.row').addClass('hide-section');
            $('.shipping-contianer').addClass('show-section');
            $('#step2-circle button').addClass('pstfil');
            $('#step2-circle button').addClass('crnt');

            if (jQuery('#shispping_form').hasClass('usrbsc')) {
                full_name = jQuery('#full_name').val();
                s_country = jQuery('#s_country').val();
                states_countries = jQuery('#states_countries').val();
                s_street = jQuery('#s_street').val();
                city = jQuery('#s_city').val();
                s_post_code = jQuery('#s_post_code').val();
                s_flat_other = jQuery('#s_flat_other').val();
                data2 = '&full_name=' + full_name + '&s_country=' + s_country +  '&city=' + city + '&states_countries=' + states_countries + '&s_street=' + s_street + '&s_post_code=' + s_post_code + '&s_flat_other=' + s_flat_other;
            } else {
                val = jQuery('input[name=address-option-select]:checked').val();
                data2 = '&shipping_id=' + val;
            }


        });
        var shipment_data ;
        var shipping_error ;

      

        $('#continue-two').click(function () {
            shipment_data = $('#shispping_form').serialize();
               let shipment = shipment_data.indexOf('=&') > -1;
                
                // console.log("shipment", shipment);
                if(shipment == true){
                    shipment_data = $('#address-otion1').val();
                }else{
                    shipment_data = $('#shispping_form').serialize();
                }
                // console.log("shipment_data", shipment_data);
                // var flatShipping = 2;
                var flatShipping = 0;
                $('.shipping_price_calculated_flatshipping').each(function( key, value ) {
                    flatShipping = parseFloat(flatShipping) + parseFloat($(this).attr('data-ship-price'));
                });
          
                if( typeof flatShipping !== 'undefined'){
                    // console.log("called");
                    var realprice = 0;
                    $('.get_data_calulate_ship_flatship').each(function( key, value ) {
                        var that = $(this);
                        var val = $(this).val().split("_");
                        var storeid = val[0];
                        var productId = val[1];
                        
                       // var storeid = $(this).val();
                    //    var actual_price= $("#usdcprice").attr('price', "0.2");
                
                });
                }

                var myArray = [];

                
                var prevValue = null;
                  $('.get_data_calulate_ship').each(function( key, value ) {
                   var shipping_error;
                   var  that = $(this);
                   var  val = $(this).val().split("_");
                   var  value = val[0];
                   var  storeId = val[1];
                   var  productId = val[2];
                   var   weight = val[3];
                   var   origin_zip = val[4];
                   var   shipping_option_id = val[5];
                   var   product_length = val[6];
                   var   product_width = val[7];
                   var    product_height = val[8];
                   var    product_quantity = val[9];
                   var    product_price = val[10];
                   var    product_title = val[11];
                   var  product_key = val[12];
                        //  console.log("value all", val);
                        //  return false;
                    var obj = {
                   
                         value : value,
                         storeId : storeId,
                         productId :productId,
                         weight : weight,
                         origin_zip : origin_zip,
                         shipping_option_id :shipping_option_id,
                         product_length : product_length,
                         product_width : product_width,
                         product_height : product_height,
                         product_quantity : product_height,
                         product_price : product_price,
                         product_title : product_title,
                         
                    };

                        if (prevValue === null) {
                            prevValue = productId;
                            //  console.log('first value of if condition', prevValue);
                        
                        // console.log("value of request", val);
                        $.ajax({
                            url: '/calculate_service',
                            type: "POST",
                            dataType: "json",
                            data: {value: value,storeId:storeId, origin_zip:origin_zip,shipping_option_id: shipping_option_id,weight:weight, 
                                product_length:product_length,product_width:product_width,product_height:product_height, product_quantity: product_quantity, 
                                product_price:product_price,product_title:product_title, shipment_data:shipment_data, _token: '{{ csrf_token() }}'},
                            success: function (data) {
                                // debugger
                                console.log("data",data);
                                if(data.error){
                                    shipping_error = data.error;
                                    shippingErrorResponse(shipping_error);
                                    
                                    // accessGlobalResponse();
                                    
                                    // console.log("dataerror",shipping_error);
                                    //return false;
                                }
                                
                                // console.log("return rate",data.rate);
                                $(that).next(".shipping_price_calculated").html(data.rate);
                                $(that).next(".shipping_price_calculated").attr('data-ship-price',data.rate);
                                var shipTotalPrice = 0;
                                var realTotalPrice = 0;
                                $('.shipping_price_calculated').each(function( key, value ) {
                                    // console.log('value of dropdown', value);
                                    
                                   
                                    shipTotalPrice = parseFloat(shipTotalPrice) + parseFloat($(this).attr('data-ship-price'));
                                //  console.log('valeu',$(this).attr('data-ship-price'));
                                });
                                   
                                var vservice =   $("#list_"+productId).find('.'+data.service);
                                data.forEach( (item )=>{
                                    if(item.service == 'ParcelSelect'){
                                        $("#list_"+productId).find('.' + item.service).attr('disabled', false);
                                    }
                                    if(item.service == 'Express'){
                                        $("#list_"+productId).find('.' + item.service).attr('disabled', false);
                                    }
                                    if(item.service == 'Priority'){
                                        $("#list_"+productId).find('.' + item.service).attr('disabled', false);
                                    }
                                    if(item.service == 'First'){
                                        $("#list_"+productId).find('.' + item.service).attr('disabled', false);
                                    }
                                })
                                let elm = $("#list_"+productId).find('option:not(:disabled)').first();
                                
                                elm.attr('selected', true).trigger('change');

                                $('.shipping_price_calculated').each(function( key, value ) {
                                
                                     
                               
                                // debugger
                                // if (vservice.length > 0) {
                                //     // If an element with matching class is found
                                //     vservice.attr('selected', true);  // Set its 'selected' attribute to true
                                //     $("#list_"+productId).find('option:not(.' + data.service + ')').attr('disabled', true); // Disable all other options
                                // } 
                                // else {
                                //     // If an element with matching class is not found
                                //     $("#list_"+productId).find('option').attr('disabled', false); // Enable all options
                                // }

                            });
                                $('.real_product_price').each(function( key, value ) {
                                    realTotalPrice = parseFloat(realTotalPrice) + parseFloat($(this).attr('data-real-price'));
                                // console.log('valeu',$(this).attr('data-real-price'));
                                });
                                // pay with metamask
                            //    var actual_price= $("#usdcprice").attr('price', "0.2");
                            //    console.log("flatShipping", flatShipping);

                            //    console.log("actual_price",actual_price);
                                var grandTotal_integer = shipTotalPrice + realTotalPrice + flatShipping;
                                var grandTotal = grandTotal_integer.toFixed(2);
                                // console.log('shipTotalPrice',shipTotalPrice);
                                // console.log('grandTotal',grandTotal);

                                orderData[storeId][product_key].shipping_price = data.rate;
                                orderData[storeId][product_key].shipping_method = data.service;
                                // orderData[storeId][key].shipping_method = value;
                                orderData[storeId].store_ship_price = shipTotalPrice;
                                orderData[storeId].store_total_price = grandTotal;
                                orderData[storeId][product_key].store_data = data.store_data;
                                orderData[storeId][product_key].user_info = data.user_info;
                                orderData[storeId][product_key].ship_id = data.ship_id;
                                orderData[storeId][product_key].rate_id = data.rate_id;

                                
                                $('#orderData').val(JSON.stringify(orderData));
                                $('#orderData_stripe').val(JSON.stringify(orderData));
                                console.log('=====last start=====');
                               
                                var stripe_total = grandTotal*100;
                             
                                // loadStripeCheckout(stripe_total);
                                $("#total_amount_ship").val(stripe_total);
                                $(".finalsubmit").show();
                                $('.subtotal_cal').html("USDC "+grandTotal);
                                $('.grandtotal_cal').html("USDC "+grandTotal);
                                $('#amount-calculated').html("USDC "+grandTotal);
                                $("#usdcprice").attr('price', grandTotal);
                        //         var stripe=  $('#stripe_button_payment').attr('data-amount',grandTotal);
                        // console.log("stripe00000 load ", stripe);
                      
                                console.log('=====last end=====');

                            }
                      
                        });
                        myArray.push(obj);
                    } 
                    else if (prevValue === productId) {
                            // console.log('on load if else condition second');

                            // Do something if the value is repeating
                            // console.log('Value ' + productId + ' is repeating');
                        } 
                        else {
                             prevValue = productId;
                            //  console.log("else condition" ,prevValue);
                            //     console.log('third');
                            $.ajax({
                            url: '/calculate_service',
                            type: "POST",
                            dataType: "json",
                            data: {value: value,storeId:storeId, origin_zip:origin_zip,shipping_option_id: shipping_option_id,weight:weight, 
                                product_length:product_length,product_width:product_width,product_height:product_height, product_quantity: product_quantity, 
                                product_price:product_price,product_title:product_title, shipment_data:shipment_data, _token: '{{ csrf_token() }}'},
                            success: function (data) {
                                // debugger
                                console.log("data_on load else condition ",data);
                                if(data.error){
                                    shipping_error = data.error;
                                    shippingErrorResponse(shipping_error);
                                    // accessGlobalResponse();
                                    
                                    // console.log("dataerror",shipping_error);
                                    //return false;
                                }
                                
                                // console.log("return rate",data.rate);
                                $(that).next(".shipping_price_calculated").html(data.rate);
                                $(that).next(".shipping_price_calculated").attr('data-ship-price',data.rate);

                                var shipTotalPrice = 0;
                                var realTotalPrice = 0;
                                $('.shipping_price_calculated').each(function( key, value ) {
                                
                                    shipTotalPrice = parseFloat(shipTotalPrice) + parseFloat($(this).attr('data-ship-price'));
                                //  console.log('valeu',$(this).attr('data-ship-price'));
                                });
                                var vservice =   $("#list_"+productId).find('.'+data.service);
                                    data.forEach( (item )=>{
                                   
                                    if(item.service == 'Express'){
                                        $("#list_"+productId).find('.' + item.service).attr('disabled', false);
                                    }
                                    if(item.service == 'Priority'){
                                        $("#list_"+productId).find('.' + item.service).attr('disabled', false);
                                    }
                                    if(item.service == 'First'){
                                        $("#list_"+productId).find('.' + item.service).attr('disabled', false);
                                    }
                                    if(item.service == 'ParcelSelect'){
                                        $("#list_"+productId).find('.' + item.service).attr('disabled', false);
                                    }
                                })
                                    let elm = $("#list_"+productId).find('option:not(:disabled)').first();
                                    
                                    elm.attr('selected', true).trigger('change');
                                $('.shipping_price_calculated').each(function( key, value ) {
                                
                                     
                                  
                                  
                                    // if (vservice.length > 0) {
                                    //     // If an element with matching class is found
                                    //     vservice.attr('selected', true);  // Set its 'selected' attribute to true
                                    //     $("#list_"+productId).find('option:not(.' + data.service + ')').attr('disabled', true); // Disable all other options
                                    // } 
                                    // else {
                                    //     // If an element with matching class is not found
                                    //     $("#list_"+productId).find('option').attr('disabled', false); // Enable all options
                                    // }

                                });
                                $('.real_product_price').each(function( key, value ) {
                                    realTotalPrice = parseFloat(realTotalPrice) + parseFloat($(this).attr('data-real-price'));
                                // console.log('valeu',$(this).attr('data-real-price'));
                                });
                                // pay with metamask
                            //    var actual_price= $("#usdcprice").attr('price', "0.2");
                             //    console.log("flatShipping", flatShipping);

                             //    console.log("actual_price",actual_price);
                                var grandTotal_integer = shipTotalPrice + realTotalPrice + flatShipping;
                                var grandTotal = grandTotal_integer.toFixed(2);
                                console.log('+++++++++++ Else Condition ++++++++++++')
                               
      

                                orderData[storeId][product_key].shipping_price = data.rate;
                                orderData[storeId][product_key].shipping_method = data.service;
                                orderData[storeId].store_ship_price = shipTotalPrice;
                                orderData[storeId].store_total_price = grandTotal;
                                orderData[storeId][product_key].store_data = data.store_data;
                                orderData[storeId][product_key].user_info = data.user_info;
                                orderData[storeId][product_key].ship_id = data.ship_id;
                                orderData[storeId][product_key].rate_id = data.rate_id;


                                $('#orderData').val(JSON.stringify(orderData));
                                $('#orderData_stripe').val(JSON.stringify(orderData));
                                // console.log('=====last start=====');
                                $('.subtotal_cal').html("USDC "+grandTotal);
                                $('.grandtotal_cal').html("USDC "+grandTotal);
                                $('#amount-calculated').html("USDC "+grandTotal);
                                $("#usdcprice").attr('price', grandTotal);
                                var stripe_total = grandTotal*100;
                              
                                // loadStripeCheckout(stripe_total);
                                $(".finalsubmit").show();
                                $("#total_amount_ship").val(stripe_total);
                                // $('#stripe_button_payment').attr('data-amount',grandTotal);
                                // console.log('=====last end=====');
    

                            }
                      
                        });
                        
                            // Do something else if the value is not repeating
                            console.log('Value ' + productId + ' is not repeating');
                            prevValue = productId;
                       }
                       
                
                });
                   
                setTimeout(function(){
                    $(".finalsubmit").show();
                            }, 2000);

                   
         
            // }
    // function loadStripeCheckout(amount) {
    //     var stripeCheckoutScript = document.createElement('script');
    //     stripeCheckoutScript.src = 'https://checkout.stripe.com/checkout.js';
    //     stripeCheckoutScript.classList.add('stripe-button');
    //     stripeCheckoutScript.id = 'stripe_button_payment';
    //     stripeCheckoutScript.setAttribute('data-key', '<?php echo env('STRIPE_API_KEY_PUBLISHABLE'); ?>');
    //     stripeCheckoutScript.setAttribute('data-amount', amount);
    //     stripeCheckoutScript.setAttribute('data-name', 'MallShark');
    //     stripeCheckoutScript.setAttribute('data-description', 'Checkout Charge');
    //     stripeCheckoutScript.setAttribute('data-image', 'https://stripe.com/img/documentation/checkout/marketplace.png');
    //     stripeCheckoutScript.setAttribute('data-email', '<?php echo $user_email; ?>');
    //     stripeCheckoutScript.setAttribute('data-currency', 'USD');
    //     stripeCheckoutScript.setAttribute('data-locale', 'auto');
        
    //     // Render the Stripe checkout button inside the form with ID "stripe-account-form"
    //     $('#stripe_account_form').html(stripeCheckoutScript);
    //     // var elemnet = document.createElement('input');
    //     //     elemnet.id = 'shipping_data_stripe';
    //     //     elemnet.setAttribute('name', 'shipping_data_stripe');
    //     //     elemnet.setAttribute('type', 'hidden');
    //     //     $('.stripe_account_form').html(elemnet);
    // }
    function loadStripeCheckout(amount) {
        $('#stripe_button_payment').remove();
        const stripeCheckoutScript = $('<script>')
            .attr('src', 'https://checkout.stripe.com/checkout.js')
            .addClass('stripe-button')
            .attr('id', 'stripe_button_payment')
            .attr('data-key', '<?php echo env('STRIPE_API_KEY_PUBLISHABLE') ?>')
            .attr('data-amount', amount)
            .attr('data-name', 'MallShark')
            .attr('data-description', 'Checkout Charge')
            .attr('data-image', 'https://stripe.com/img/documentation/checkout/marketplace.png')
            .attr('data-email', '{{ $user_email }}')
            .attr('data-currency', 'USD')
            .attr('data-locale', 'auto');

        
        const form = $('#stripe_account_form');
        form.empty();
        form.html(stripeCheckoutScript);
      
}



            function shippingErrorResponse(response) {
                swal("If Any Query Please Contact Store Owner!", response, "error");
                  $('.checkout-page').addClass('disable-page');
           
                }


            val = jQuery('input[name=payment-option-select]:checked').val();
            jQuery('.error-message').remove();
            if (val == '') {
                jQuery('.small-container').append('<div class="error-message"><strong>Please Select a payment method</strong></div>');
                return false;
            }
         
            // if (val != 'payment-option-visa') {
            //     jQuery('.small-container').append('<div class="error-message"><strong>This payment option is not available please select any other</strong></div>');
            //     return false;
            // }
            $('.basket-contianer').removeClass('show-section');
            $(this).parentsUntil('.shipping-contianer').find('.row').addClass('hide-section');
            $('.readypayment-contianer').addClass('show-section');
            $('#step1-circle button').addClass('pstfil');
            $('#step2-circle button').addClass('pstfil');
            $('#step3-circle button').addClass('pstfil');
            $('#step3-circle button').addClass('crnt');
        });


        $('#continue-three').click(function () {
            $('.shipping-contianer').removeClass('show-section');
            $('.readypayment-contianer').removeClass('show-section');
            $('#step1-circle button').addClass('pstfil');
            $('#step2-circle button').addClass('pstfil');
            $('#step3-circle button').addClass('pstfil');
            $('#step4-circle button').addClass('pstfil');
        });
        $('#continue-shopping').click(function () {
        //    window.location = 'http://shopzit.mindblaze.net';
        //    location.replace('http://shopzit.mindblaze.net');
        window.location.href = 'http://mallshark.io'
        });







        $(function () {
            $('#full-name').keydown(function (e) {
                if (e.shiftKey || e.ctrlKey || e.altKey) {
                    e.preventDefault();
                } else {
                    var key = e.keyCode;
                    if (!((key == 8) || (key == 32) || (key == 46) || (key >= 35 && key <= 40) || (key >= 65 && key <= 90))) {
                        e.preventDefault();
                    }
                }
            });
        });







    });

</script>
<script src="{{ mix('js/app.js') }}" defer></script>
<script>
    
    function get_state_by_code(country_code) {
        if (country_code != '') {
            jQuery.ajax({
                url: '/get-states',
                data: 'country_code=' + country_code + '&action=get_states_by_country_code',
                type: 'POST',
                //dataType:'json',
                success: function (response) {
                    obj = JSON.parse(response);
                    var options = '';
                    console.log(obj);
                    jQuery.each(obj, function (i, item) {

                        options += '<option value="' + item.state_id + '">' + item.state_name + '</option>';
                    });
                    jQuery('#states_countries').html(options);
                }
            });
        }

    }
</script>
<script>
    jQuery(document).on("click", ".show-loader", function () {

        jQuery(".large-loader").show();
    })

</script>
<style>
    .hass_error {
        border-color: red;
    }
    .disable-page{
        pointer-events: none;
        cursor: not-allowed;
        opacity: 0.5;
    }
    .information  {
        
    color: red;
    font-size: 17px;

    }
    .finalsubmit{
        display: none;
    }
</style>

@if($total_amount=='iiiiiiiii')
<script>
        
    //{{$total_amount}}
    // Render the PayPal button

    //    paypal.Button.render({
    //        // Set your environment
    //
    //        env: 'sandbox', // sandbox | production
    //
    //        // Specify the style of the button
    //
    //        style: {
    //            label: 'paypal',
    //            size: 'medium', // small | medium | large | responsive
    //            shape: 'rect', // pill | rect
    //            color: 'blue', // gold | blue | silver | black
    //            tagline: false
    //        },
    //        // PayPal Client IDs - replace with your own
    //        // Create a PayPal app: https://developer.paypal.com/developer/applications/create
    //
    //        client: {
    //            sandbox: 'AVfWoSR83fKLRqWywDYeIaM7kv28dm7-oIe2qS0l4bWmo--grJIj24f6btvdUR03-pBmHKIGeqvltWjm',
    //            //sandbox: ' Ae4fHI1yoAGg.VY6dmhnWmixPLMfAii9.hcaiFnBOAAVRDc.SWjrVg.x',
    //
    //            production: '<insert production client id>'
    //        },
    //        payment: function (data, actions) {
    //            return actions.payment.create({
    //                payment: {
    //                    transactions: [
    //                        {
    //                            amount: {total: '{{$total_amount}}', currency: 'Z '}
    //                        }
    //                    ]
    //                },
    //                experience: {
    //                    input_fields: {
    //                        no_shipping: 1
    //                    }
    //                }
    //            });
    //        },
    //        onAuthorize: function (data, actions) {
    //            return actions.payment.execute().then(function () {
    //                //alert(data.paymentToken + ',' + data.payerID + ',' + data.paymentID + ',' + data.intent+','+data.returnUrl);
    //                if (data[data.paymentToken] != '' && data.paymentID != '') {
    //                    jQuery.ajax({
    //                        'url': '/save-order',
    //                        'method': 'POSt',
    //                        'Type': 'POST',
    //                        'data': 'paymentToken=' + data.paymentToken + '&payerID=' + data.payerID + '&paymentID=' + data.paymentID + '&intent=' + data.intent + '&returnurl=' + data.returnUrl + '&paymentmethod=paypal' + data2,
    //                        'success': function (res) {
    //
    //
    //                            jQuery('#continue-three').click();
    //                            jQuery('.shipping_amount').show();
    //                            jQuery('#step4-circle .btn-circle').addClass('crnt');
    //                            jQuery('#paypal-button-container').hide();
    //                        }
    //                    });
    //                }
    //            });
    //        },
    //        onCancel: function (data) {
    //            alert('payment was cancelled');
    //        },
    //        onError: function (err) {
    //            alert('there was an error we could not process your transaction');
    //        }
    //
    //    }, '#paypal-button-container');
    //    jQuery("#add-another-shipping").click(function () {
    //        setTimeout(
    //                function ()
    //                {
    //                    jQuery('html, body').animate({
    //                        scrollTop: jQuery("#shispping_form").offset().top
    //                    }, 2000);
    //                }, 500);
    //
    //    });

</script>

@endif
@endif

@endsection