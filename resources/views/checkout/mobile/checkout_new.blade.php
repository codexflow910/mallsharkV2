@extends('layouts.register-main-mobile')
@section('content')
<script>
  
  $(window).load(function() {
    $('#loading').hide();
  });
</script>
<style>

.flat_price{
    font-size: 12px;
}
.no_shipping{
    font-size: 12px;

}
.hide-setion{
    display: none;
}
#loading {
        position: fixed;
        display: block;
        width: 100%;
        height: 100%;
        opacity: 0.7;
        background-color: #fff;
        z-index: 99;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
    }

    #loading-image {
        position: absolute;
        top: 50%;
        z-index: 100;
        width: 150px;
        left: 50%;
        transform: translate(-50%,-50%);
    }
.logged-in-user{
    border: 1px solid #DDDDDD;
        margin-bottom: 33px;
        border-radius: 2px;
        border-width: 2px 1px 3px 2px;
        position: relative;
        padding: 10px;
    }
    .displayNameUser, .user-full-name{
        font-size: 24px;
    }
    .displayNameUser{
        font-size: 24px;
        color: blue;
    }
    .user-logout-link{
        right: 5px;
        position: absolute; 
    }
    </style>
<?php
if (isset($orderData['total_price']) && is_numeric($orderData['total_price'])) {
    $total_amount = $orderData['total_price'];
} else {
    $total_amount = 0; // or any default value you want to use
}
$orderDatatotal = $total_amount * 100;
$session_data = isset($alldata) ? $alldata: "";
if (isset($orderData['total_price']) && is_numeric($orderData['total_price'])) {
$ercTotal = $orderData['total_price'];
} 

if (isset($orderDataCrypto['total_price']) && is_numeric($orderDataCrypto['total_price'])) {
$ercTotalCrypto = $orderDataCrypto['total_price'];
} 

?>
<div class="container chkout checkout-page" id="checkout-page">
    <div class="row">
            @if($errors->any())$state_name
                <div class="alert alert-danger">
                    <ul>
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
             @endif
        <div class="col-sm-6">
        <?php
            if (!Session::has('userData')) {
        ?>
            <div class="alert  alert-primary" role="alert">
                Returning Customer? <a href="{{ URL::to('/login')}}">Click here to login</a>
            </div>
        
        <?php } else { 

            $userid = Session::get('userData')['user_id'];
            $userData = getUserById($userid);
            // echo 'Data: <pre>' .print_r($resu$userDatalt,true). '</pre>'; die();
            $fullName = $userData->name;
                 ?>
                  <div class="logged-in-user">      
                     <span class="user-full-name">Welcome  <span class="displayNameUser">{{$fullName}} </span></span></br />
                     <span class="user-email-add">Your email address is<span class="emailFieldText">: {{$userData->email}}</span></span>
                     <a class="user-logout-link" href="{{ URL::to('/logout')}}">Logout <i class="fa fa-sign-out" aria-hidden="true"></i></a>
                  </div>
                <?php } ?>
                <?php
                 if (!Session::has('userData')) {
                 ?>
            <h2 class="frmhd">Contact Information</h2>
                <?php } ?>
            <form id="shispping_form" action="/checkout-save-new" method="post" autocomplete="off">
                {{ csrf_field() }}
                <?php  if (Session::has('userData')) { ?>
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label for="useremail">Email *</label>
                        <input type="email" class="form-control" id="useremail" name="useremail" value="<?php echo $userData->email ? $userData->email: "";?>" disabled >
                    </div>
                    <div class="form-group col-md-6">
                        <label for="phone_number">Phone</label>
                        <input type="text" class="form-control" id="phone_number" name="phone_number" value="<?php echo $userData->contact_no ? $userData->contact_no: "";?>" disabled>  
                    </div>
                </div>
                <?php }else{ ?>
                    <div class="form-row">
                    <div class="form-group col-md-6">
                        <label for="useremail">Email *</label>
                        <input type="email" class="form-control no-session" id="useremail" name="useremail" value="">
                    </div>
                    <div class="form-group col-md-6">
                        <label for="phone_number">Phone</label>
                        <input type="text" class="form-control" id="phone_number" name="phone_number" >  
                    </div>
                </div>
              <?php  } ?>
                <h2 class="frmhd">Shipping Address</h2>

                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label for="inputFirstName">First Name *</label>
                        <input type="text" class="form-control" id="inputFirstNameShipping" name="first_name">
                    </div>
                    <div class="form-group col-md-6">
                        <label for="inputLastName">Last Name</label>
                        <input type="text" class="form-control" id="inputLastNameShipping" name="last_name">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label for="inputCountry">Country *</label>
                        <select class="form-control required_class getshippingrate" id="s_country" name="country" onchange="get_state_by_code(this.value)" autocomplete="off">
                            <option value="">Please Select</option>
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
                        <!-- <input type="text" class="form-control" id="inputCountry"> -->
                    </div>
                    <div id="loading" class="hide-setion">
                                    <img id="loading-image" src="{{asset('assets/images/Loader-Animation.gif')}}" alt="Loading..." />
                                </div>
                    <div class="form-group col-md-6">
                        <label for="states_countries">States *</label>
                        <select class="form-control required_class getshippingrate" id="states_countries" name="states_countries" autocomplete="off">


                            <?php
                            if (isset($session_data['state'])) {
                                echo '<script>jQuery(document).ready(function(){setTimeout(function() {
                                    jQuery("#add-another-shipping").trigger("click");
                                        jQuery("#continue-one").trigger("click");
                                    }, 20);});</script>';
                                $country_id = $session_data['country'];
                                // echo '$country_id: <pre>' .print_r($country_id,true). '</pre>'; die();
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
                                echo '<option class="noman" value="' . $session_data['state'] . '">' . $state_name . '</option>';
                            } else {
                                echo '<option value="46"></option>';
                            }
                            $states = array();
                            $selected = '';
                            if (count($states) > 0) {
                                echo 'Data: <pre>' .print_r($result,true). '</pre>'; 
                                foreach ($states as $statee) {
                                    if ($statee['state_code'] == $state) {

                                        $selected = "selected='selected'";
                                    } else {
                                        $selected = '';
                                    }

                                    echo '<option class="noman2"  value="' . $statee['state_code'] . '">' . $statee['state_name'] . '</option>';
                                }
                            }
                            ?>

                        </select>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group col-md-12">
                        <label for="street_address">Street Address *</label>
                        <input type="text" class="form-control getshippingrate" id="street_address" name="street_address">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label for="city_name">Town/City *</label>
                        <input type="text" class="form-control getshippingrate" id="city_name" name="city_name">
                    </div>
                    <div class="form-group col-md-6">
                        <label for="zipcode">Post Code *</label>
                        <input type="text" onkeypress="return isNumber(event)" maxlength="5"  minlength="5" class="form-control getshippingrate" id="zipcode" name="zipcode">
                    </div>
                   
                </div>
                  

            </form>
            <h2 class="frmhd">Billing Address</h2>
            <div class="form-check" style="margin-bottom:30px">
                <input class="form-check-input" type="checkbox" id="showFormCheckbox">
                <label class="form-check-label" for="showFormCheckbox">
                    I have different billing address
                </label>
            </div>
            <div class="form" style="display: none;">
                <form>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="inputFirstName">First Name</label>
                            <input type="text" class="form-control" id="inputFirstName">
                        </div>
                        <div class="form-group col-md-6">
                            <label for="inputLastName">Last Name</label>
                            <input type="text" class="form-control" id="inputLastName">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="inputCountry">Country</label>
                            <input type="text" class="form-control" id="inputCountry">
                        </div>
                        <div class="form-group col-md-6">
                            <label for="inputAddress">Street Address</label>
                            <input type="text" class="form-control" id="inputAddress">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-4">
                            <label for="inputTownCity">Town/City</label>
                            <input type="text" class="form-control" id="inputTownCity">
                        </div>
                        <div class="form-group col-md-4">
                            <label for="inputPostCode">Post Code</label>
                            <input type="text"  class="form-control" id="inputPostCode">
                        </div>
                        <div class="form-group col-md-4">
                            <label for="inputState">State</label>
                            <input type="text" class="form-control" id="inputState">
                        </div>
                    </div>
                </form>
            </div>

        </div>
 
         <!-- Stripe Strat -->
        <div class="col-sm-6">
            <div class="heading">
                <h2>Your Items</h2>
                <div class="subheading ">Payment Methods Accepted:

                    <img style="position:relative; top:-2px" src="{{asset('assets/images/icnp-2w.png')}}"><span> Master / Visa</span>
                </div>
            </div>





            <div class="table mt-0">
                <?php
                $mainArray =  array();
                $totalStoreShipPrice = 0;
                if(empty($orderData)) {
                    echo 'No Result Found';
                } else {
                
                    //  echo '$orderData: <pre>' .print_r($orderData,true). '</pre>'; die();
                foreach ($orderData as $key => $val) {
                    $store = explode('-', $key);
                    $storeId = !empty($store[1]) ? $store[1] : '';
                    $totalStoreShipPrice = isset($val['store_shipping_price']) ? $val['store_shipping_price'] : "";
                    $store_stripe_gateway = isset($val['store_stripe_gateway']) ? $val['store_stripe_gateway'] : "";
                    $counter = 0;
                    $storeTotalPrice = 0;
                    $totalShipPrice = 0;
                    $prevValue = null;
                    // echo '$val: <pre>' .print_r($val,true). '</pre>';
                    // echo 'Data: <pre>' .print_r($val,true). '</pre>'; die();
                    // if($store_stripe_gateway == '1'){
                    
                    if (is_array($val) && count($val) > 0) {
                        foreach ($val as $key2 => $val2) {
                            // echo '$val2: <pre>' .print_r($val2,true). '</pre>'; 

                            if (is_numeric($key2)) {
                                $product = getProductById($val2['product_id']);
                                $mainArray[$storeId][] =  $val2;
                                // echo 'Data: <pre>' .print_r($mainArray,true). '</pre>'; die();
                                // $mainArray[$storeId][$val2['shipping_method']] =  'usps-priority';
                                if ($val2['product_type'] == 'simple') {
                                    $image = $val2['feature_image'];
                                } elseif ($val2['product_type'] == 'virtual') {
                                    $image = $val2['feature_image'];
                                } else {
                                    $image = $val2['product_variation_image'];
                                }

                                // $image = $val2['feature_image'];

                                $slug = $val2['slug'];

                                $title = isset($product[0]->title) ? $product[0]->title : '';
                                $image_url = getAttachmentUrlById((int) $image, 'small');
                                $shipping_price = isset($val2['shipping_price']) ? $val2['shipping_price'] : '0';
                                $shipping_method = isset($val2['method']) ? $val2['method'] : '';
                                $is_domestic_free = isset($val2['is_domestic_free']) ? $val2['is_domestic_free'] : '0';
                                $flat_shipping_rate = isset($val2['flat_shipping_rate']) ? $val2['flat_shipping_rate'] : '0';
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
                                if ($weightType == 'pound') {
                                    $newWeight = number_format($product_weight * 16, 3);
                                    $newWeight = (float) $newWeight;
                                    $product_weight = round($newWeight);
                                    if($product_weight == 0){
                                        $product_weight = 1;
                                    }
                                }

                                if ($dimentionType == 'cm') {
                                    $new_length = number_format($productSize_length * 16, 3);
                                    $new_length = (float) $new_length;
                                    $productSize_length = round($new_length);

                                    if($productSize_length == 0){
                                        $productSize_length = 1;
                                    }

                                    $new_width = number_format($productSize_width * 16, 3);
                                    $new_width = (float) $new_width;
                                    $productSize_width = round($new_width);
                                    if($productSize_width == 0){
                                        $productSize_width = 1;
                                    }
                                    $new_height = number_format($productSize_height * 16, 3);
                                    $new_height = (float) $new_height;
                                    $productSize_height = round($new_height);
                                    if($productSize_height == 0){
                                        $productSize_height = 1;
                                    }

                                    // $productSize_length = round(number_format($productSize_length / 2.54, 3));
                                    // $productSize_width = round(number_format($productSize_width / 2.54, 3));
                                    // $productSize_height = round(number_format($productSize_height / 2.54, 3));
                                } else if ($dimentionType == 'mm') {

                                    $new_length = number_format($productSize_length / 25.4, 3);
                                    $new_length = (float) $new_length;
                                    $productSize_length = round($new_length);
                                    if($productSize_length == 0){
                                        $productSize_length = 1;
                                    }
                                    $new_width = number_format($productSize_width / 25.4, 3);
                                    $new_width = (float) $new_width;
                                    $productSize_width = round($new_width);
                                    if($productSize_width == 0){
                                        $productSize_width = 1;
                                    }

                                    $new_height = number_format($productSize_height / 25.4, 3);
                                    $new_height = (float) $new_height;
                                    $productSize_height = round($new_height);
                                    if($productSize_height == 0){
                                        $productSize_height = 1;
                                    }

                                    // $productSize_length = round(number_format($productSize_length / 25.4, 3));
                                    // $productSize_width = round(number_format($productSize_width / 25.4, 3));
                                    // $productSize_height = round(number_format($productSize_height / 25.4, 3));
                                }


                                $shipping_option_id =  isset($val2['shipping_option_id']) ? $val2['shipping_option_id'] : '0';
                                $product_tax = isset($val2['product_tax']) ? $val2['product_tax'] : '0';

                                $product_variant_price = isset($val2['variation_sale_price']) ? $val2['variation_sale_price'] : $val2['price'];
                                $product_type = isset($val2['product_type']) ? $val2['product_type'] : "";

                                if ($product_type == 'variant') {
                                    $product_price = isset($val2['variation_sale_price']) ? $val2['variation_sale_price'] : $val2['price'];
                                } else {
                                    $product_price = isset($val2['price_cad']) ? $val2['price_cad'] : $val2['price'];
                                }
                                // $new_price = $product_price;
                                // $new_price = (float) $new_price;
                                // $product_price = $new_price;


                                $shipping_service = isset($val2['shipping_service']) ? $val2['shipping_service'] : '';
                                $proPrice = ($product_price * $val2['product_quantity']) + $shipping_price;
                                $proPriceReal = ($product_price * $val2['product_quantity']);
                                $product_quantity = isset($val2['product_quantity']) ? $val2['product_quantity'] : "1";
                                $note = '';
                                $totalShipPrice = $totalShipPrice + $totalStoreShipPrice;
                                $attribute1 = isset($val2['attribute1']) ? $val2['attribute1'] : "";
                                $attribute2 = isset($val2['attribute2']) ? $val2['attribute2'] : "";
                                $attribute3 = isset($val2['attribute3']) ? $val2['attribute3'] : "";

                                if ($shipping_price == 0 && $shipping_method == 'free') {
                                    $note = '<p class="no_shipping"> Note: shipping cost for this product is not included. </p>';
                                }

                                ?>
                                <div class="row" id="list_{{$val2['product_id']}}">
                                    <div class="cell prdcl"><span class="prdcnt">{{$val2['product_quantity']}}</span><img src="<?php echo $image_url; ?>" alt="{{$val2['title']}}" width="64"></div>
                                    <div class="cell text-left">{{$title}}</div>
                                    <div class="cell" style="max-width: 150px; position:relative">
                                        <?php if ($val2['product_type'] != 'virtual') {
                                            if ($is_domestic_free == '0') {
                                                if ($flat_shipping_rate == '0') { ?>
                                                    <select class="custom-select custom-select-sm shipping_service_checkout"  id="shipping_service">
                                                        <?php foreach ($shipping_service as $service) { ?>
                                                            <option class="{{ $service}}" value="{{$service}}_{{$storeId}}_{{$val2['product_id']}}_{{$product_weight}}_{{$origin_zip}}_{{$shipping_option_id}}_{{$productSize_length}}_{{$productSize_width}}_{{$productSize_height}}_{{$product_quantity}}_{{$proPriceReal}}_{{$title}}_{{$key2}}" disabled>{{$service}}</option>
                                                <?php }
                                                    }
                                                } ?>

                                                    </select>
                                                <?php } ?>
                                                <?php 
                                                     
                                                     if ($note == '') {  
                                                      if($shipping_method =='flat' ){ ?>
                                                        <input type="hidden" name="shipping_data" class="get_data_calulate_ship_flatship" value="{{$storeId}}_{{$val2['product_id']}}">
                                                        <span class="flat_price shipping_price_calculated_flatshipping" data-ship-price="{{number_format($flat_shipping_rate,2)}}" id="shipping_price_calculated">(flat) USDC {{number_format($flat_shipping_rate,2)}} </span>
                                                        <?php }else{ ?>
                                                            <p class="small shpnginf">
                                                                Shipping USDC:
                                                                <input type="hidden" name="shipping_data" class="get_data_calulate_ship" value="Priority_{{$storeId}}_{{$val2['product_id']}}_{{$product_weight}}_{{$origin_zip}}_{{$shipping_option_id}}_{{$productSize_length}}_{{$productSize_width}}_{{$productSize_height}}_{{$product_quantity}}_{{$proPriceReal}}_{{$title}}_{{$key2}}">
                                                                <span class="shipping_price_calculated" data-ship-price="{{number_format($shipping_price,2)}}" id="shipping_price_calculated"> </span>
                                                            </p>
                                                      <?php }
                                                    } else {
                                                       
                                                        echo $note;
                                                    }
                                                    ?>
                                               
                                    </div>
                                    <div class="cell text-right" style="max-width: 85px!important; font-size:12px; line-height:16px">USDC: <span class="prcbx real_product_price" data-real-price="{{$proPriceReal}}">{{$proPriceReal}}.00</span></div>
                                </div>
                             <?php
                                $storeTotalPrice = $storeTotalPrice + $product_price;
                            }
                        }
                    }
                //   }
                    $counter++;
                    if (!empty($storeId)) {
                        // $mainArray[$storeId]['store_ship_price'] =  $totalShipPrice;
                        // $mainArray[$storeId]['store_total_price'] = $totalShipPrice + $storeTotalPrice;
                    }
                }
                // echo '$mainArray: <pre>' .print_r($mainArray,true). '</pre>'; die();
            
                ?>
                <script>

                var orderData = <?php echo json_encode($mainArray); ?>;
                </script>


            </div>




            <div class="table mt-0 mb-5" style="background: #efefef;">
                <div class="row">
                    <div class="col text-left">Sub Total:</div>
                    <div class="col text-right">USDC<span class="prcbx subtotal_product_stripe">{{number_format($total_amount,2)}}</span></div>
                </div>
                <div class="row">
                    <div class="col text-left">Tax:</div>
                    <div class="col text-right">USDC<span class="prcbx">0.00</span></div>
                </div>
                <div class="row">
                    <div class="col text-left">Shipping:</div>
                    <div class="col text-right">USDC<span class="prcbx product_shipping_total" data-product_shipping_total= "">0.00</span></div>
                </div>
                <div class="row ttlbx">
                    <div class="col text-left">Total:</div>
                    <div class="col text-right">USDC: <span class="prcbx grandtotal_cal">{{number_format($total_amount,2)}}</span></div>
                </div>
            </div>

            <h2 class="frmhd" style="margin-bottom: 0!important; margin-top:50px!important">Stripe Payment Method</h2>

           
            <div class="box pymntslct" id="box2">
                <form action="/stripe-response" method="POST" id="payment-form">
                    @csrf
                    <h2>Enter your credit/debit card information</h2>
                  
                    <input type="hidden" name="stripe_email" class="form-control" id="stripe_email" value="">

                    <div class="form-group">
                        <div id="card-element"></div>
                        <div id="card-errors" role="alert"></div>
                    </div>

                    <input type="hidden" name="total_amount_ship" id="total_amount_ship" value="<?php echo $orderDatatotal; ?>">
                    <input type="hidden" id="shipping_data_stripe" name="shipping_data_stripe">
                    <input type="hidden" id="orderData_stripe" name="orderdata" >

                    <!-- <div class="row" style="padding: 0; padding-top: 0; margin: 0; margin-bottom:4px; border-bottom:0px">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="card-number">Card Number</label>
                                <input type="text" class="form-control" id="card-number" name="card-number" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="expiry-date">MM/YY</label>
                                <input type="text" class="form-control" id="expiry-date" name="expiry-date" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="cvc">CVC</label>
                                <input type="text" class="form-control" id="cvc" name="cvc" required>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <button class="button active" id="btn2">Place Order </button>
                        </div>
                    </div> -->
                    <div class="col-md-12">
                            <button  type="submit" class="button active" id="place-order-btn">Place Order </button>
                        </div>
                    
                </form>
                <div style="text-align: center; margin-top: 20px;">
        <h2>Select Payment Method</h2>
        <button class="payment-button" onclick="payWithGoogle()">Pay with Google Pay</button>
        <button class="payment-button" onclick="payWithApple()">Pay with Apple Pay</button>
        <button class="payment-button" onclick="showCreditCardForm()">Pay with Credit/Debit Card</button>
    </div>
            </div>
            <?php } ?>
        </div>
         <!-- Stripe End -->
    </div>

     <!-- Crypto Start -->
     
    <div class="row">
        <div class="col-sm-6">
        </div>
         <div class="col-sm-6">
                <?php 
                // echo '$orderDataCrypto: <pre>' .print_r($orderDataCrypto,true). '</pre>'; die();
                if(empty($orderDataCrypto)) {
                ?>
                <div class="heading">
                    <h2>Your Items</h2>
                    <div class="subheading ">USDC Methods Accepted:
                        <img style="position:relative; top:-2px" src="{{asset('assets/images/icnp-1w.png')}}"><span> USDC</span>
                    </div>
                </div>
               
                    <?php
        
                    // echo 'Data: <pre>' .print_r($orderDataCrypto,true). '</pre>'; 
                  
                        echo 'No Result Found';
         } else { ?>
                       <div class="heading">
                            <h2>Your Items</h2>
                            <div class="subheading ">USDC Methods Accepted:
                                <img style="position:relative; top:-2px" src="{{asset('assets/images/icnp-1w.png')}}"><span> USDC</span>
                            </div>
                        </div>
                    <div class="table mt-0">
                    <?php
                    $mainArrayCrypto =  array();
                    $totalStoreShipPrice = 0;
                    $totalProdductsPriceCrypto = 0;
                    // echo '$orderDataCrypto: <pre>' .print_r($orderDataCrypto,true). '</pre>'; 
                    foreach ($orderDataCrypto as $key => $val) {
                        $store = explode('-', $key);
                        $storeId = !empty($store[1]) ? $store[1] : '';
                        $totalStoreShipPrice = isset($val['store_shipping_price']) ? $val['store_shipping_price'] : "";
                        $counter = 0;
                        $storeTotalPrice = 0;
                        $totalShipPrice = 0;
                        $prevValue = null;

                        if (is_array($val) && count($val) > 0) {
                            //  echo '$val: <pre>' .print_r($val,true). '</pre>';
                            // if(!empty($val['store_wallet_address'])){

                            
                            foreach ($val as $key2 => $val2) {
                                //echo '$key2: <pre>' .print_r($key2,true). '</pre>'; 

                                if (is_numeric($key2)) {
                                    $product = getProductById($val2['product_id']);
                                    $mainArrayCrypto[$storeId][] =  $val2;
                                    // $mainArray[$storeId][$val2['shipping_method']] =  'usps-priority';
                                    if ($val2['product_type'] == 'simple') {
                                        $image = $val2['feature_image'];
                                    } elseif ($val2['product_type'] == 'virtual') {
                                        $image = $val2['feature_image'];
                                    } else {
                                        $image = $val2['product_variation_image'];
                                    }

                                    // $image = $val2['feature_image'];

                                    $slug = $val2['slug'];

                                    $title = isset($product[0]->title) ? $product[0]->title : '';
                                    $image_url = getAttachmentUrlById((int) $image, 'small');
                                    $shipping_price = isset($val2['shipping_price']) ? $val2['shipping_price'] : '0';
                                    $shipping_method = isset($val2['method']) ? $val2['method'] : '';
                                    $is_domestic_free = isset($val2['is_domestic_free']) ? $val2['is_domestic_free'] : '0';
                                    $flat_shipping_rate = isset($val2['flat_shipping_rate']) ? $val2['flat_shipping_rate'] : '0';
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
                                    if ($weightType == 'pound') {
                                        $newWeight = number_format($product_weight * 16, 3);
                                        $newWeight = (float) $newWeight;
                                        $product_weight = round($newWeight);
                                        if($product_weight == 0){
                                            $product_weight = 1;
                                        }
                                    }
    
                                    if ($dimentionType == 'cm') {
                                        $new_length = number_format($productSize_length * 16, 3);
                                        $new_length = (float) $new_length;
                                        $productSize_length = round($new_length);
    
                                        if($productSize_length == 0){
                                            $productSize_length = 1;
                                        }
    
                                        $new_width = number_format($productSize_width * 16, 3);
                                        $new_width = (float) $new_width;
                                        $productSize_width = round($new_width);
                                        if($productSize_width == 0){
                                            $productSize_width = 1;
                                        }
                                        $new_height = number_format($productSize_height * 16, 3);
                                        $new_height = (float) $new_height;
                                        $productSize_height = round($new_height);
                                        if($productSize_height == 0){
                                            $productSize_height = 1;
                                        }
    
                                        // $productSize_length = round(number_format($productSize_length / 2.54, 3));
                                        // $productSize_width = round(number_format($productSize_width / 2.54, 3));
                                        // $productSize_height = round(number_format($productSize_height / 2.54, 3));
                                    } else if ($dimentionType == 'mm') {
    
                                        $new_length = number_format($productSize_length / 25.4, 3);
                                        $new_length = (float) $new_length;
                                        $productSize_length = round($new_length);
                                        if($productSize_length == 0){
                                            $productSize_length = 1;
                                        }
                                        $new_width = number_format($productSize_width / 25.4, 3);
                                        $new_width = (float) $new_width;
                                        $productSize_width = round($new_width);
                                        if($productSize_width == 0){
                                            $productSize_width = 1;
                                        }
    
                                        $new_height = number_format($productSize_height / 25.4, 3);
                                        $new_height = (float) $new_height;
                                        $productSize_height = round($new_height);
                                        if($productSize_height == 0){
                                            $productSize_height = 1;
                                        }

                                        // $productSize_length = round(number_format($productSize_length / 25.4, 3));
                                        // $productSize_width = round(number_format($productSize_width / 25.4, 3));
                                        // $productSize_height = round(number_format($productSize_height / 25.4, 3));
                                    }


                                    $shipping_option_id =  isset($val2['shipping_option_id']) ? $val2['shipping_option_id'] : '0';
                                    $product_tax = isset($val2['product_tax']) ? $val2['product_tax'] : '0';

                                    $product_variant_price = isset($val2['variation_sale_price']) ? $val2['variation_sale_price'] : $val2['price'];
                                    $product_type = isset($val2['product_type']) ? $val2['product_type'] : "";

                                    if ($product_type == 'variant') {
                                        $product_price = isset($val2['variation_sale_price']) ? $val2['variation_sale_price'] : $val2['price'];
                                    } else {
                                        $product_price = isset($val2['price_cad']) ? $val2['price_cad'] : $val2['price'];
                                    }
                                    $totalProdductsPriceCrypto += $product_price + $shipping_price;
                                    

                                    $shipping_service = isset($val2['shipping_service']) ? $val2['shipping_service'] : '';
                                    $proPrice = ($product_price * $val2['product_quantity']) + $shipping_price;
                                    $proPriceRealCrypto = ($product_price * $val2['product_quantity']);
                                    $product_quantity = isset($val2['product_quantity']) ? $val2['product_quantity'] : "1";
                                    $note = '';
                                    $totalShipPrice = $totalShipPrice + $totalStoreShipPrice;
                                    $attribute1 = isset($val2['attribute1']) ? $val2['attribute1'] : "";
                                    $attribute2 = isset($val2['attribute2']) ? $val2['attribute2'] : "";
                                    $attribute3 = isset($val2['attribute3']) ? $val2['attribute3'] : "";
                                    if ($shipping_price == 0 && $shipping_method == 'free') {
                                        $note = '<p class="no_shipping"> Note: shipping cost for this product is not included. </p>';
                                    }

                                    ?>
                                    <div class="row" id="list_crypto_{{$val2['product_id']}}">
                                        <div class="cell prdcl"><span class="prdcnt">{{$val2['product_quantity']}}</span><img src="<?php echo $image_url; ?>" alt="{{$val2['title']}}" width="64"></div>
                                        <div class="cell text-left">{{$title}}</div>
                                        <div class="cell" style="max-width: 150px; position:relative">
                                            <?php if ($val2['product_type'] != 'virtual') {
                                                if ($is_domestic_free == '0') {
                                                    if ($flat_shipping_rate == '0') { ?>
                                                        <select class="custom-select custom-select-sm shipping_service_checkout_crypto"  id="shipping_service_crypto">
                                                            <?php foreach ($shipping_service as $service) { ?>
                                                                <option class="{{ $service}}" value="{{$service}}_{{$storeId}}_{{$val2['product_id']}}_{{$product_weight}}_{{$origin_zip}}_{{$shipping_option_id}}_{{$productSize_length}}_{{$productSize_width}}_{{$productSize_height}}_{{$product_quantity}}_{{$proPriceReal}}_{{$title}}_{{$key2}}" disabled>{{$service}}</option>
                                                    <?php }
                                                        }
                                                    } ?>

                                                        </select>
                                                    <?php } ?>
                                                    <?php 
                                                        
                                                        if ($note == '') {  
                                                        if($shipping_method =='flat' ){ ?>
                                                            <input type="hidden" name="shipping_data" class="get_data_calulate_ship_flatship_crypto" value="{{$storeId}}_{{$val2['product_id']}}">
                                                            <span class="flat_price shipping_price_calculated_flatshipping_crypto" data-ship-price="{{number_format($flat_shipping_rate,2)}}" id="shipping_price_calculated">(flat) USDC {{number_format($flat_shipping_rate,2)}} </span>
                                                            <?php }else{ ?>
                                                                <p class="small shpnginf">
                                                                    Shipping USDC:
                                                                    <input type="hidden" name="shipping_data" class="get_data_calulate_ship_crypto" value="Priority_{{$storeId}}_{{$val2['product_id']}}_{{$product_weight}}_{{$origin_zip}}_{{$shipping_option_id}}_{{$productSize_length}}_{{$productSize_width}}_{{$productSize_height}}_{{$product_quantity}}_{{$proPriceReal}}_{{$title}}_{{$key2}}">
                                                                    <span class="shipping_price_calculated_crypto" data-ship-price="{{number_format($shipping_price,2)}}" id="shipping_price_calculated"> </span>
                                                                </p>
                                                        <?php }
                                                        } else {
                                                        
                                                            echo $note;
                                                        }
                                                        ?>
                                                
                                        </div>
                                        <div class="cell text-right" style="max-width: 85px!important; font-size:12px; line-height:16px">USDC: <span class="prcbx real_product_price_crypto" data-real-price="{{$proPriceRealCrypto}}">{{number_format(round($product_price * $val2['product_quantity'],4),2)}}</span></div>
                                    </div>
                                    <?php
                                    $storeTotalPrice = $storeTotalPrice + $product_price;
                                }
                            }
                        // }
                        }
                        $counter++;
                        if (!empty($storeId)) {
                            // $mainArrayCrypto[$storeId]['store_ship_price'] =  $totalShipPrice;
                            // $mainArrayCrypto[$storeId]['store_total_price'] = $totalShipPrice + $storeTotalPrice;
                        }
                        // echo 'Data: <pre>' .print_r($mainArrayCrypto,true). '</pre>'; die();
                    }
                   
                // }
                    ?>
                    


                </div>




                <div class="table mt-0 mb-5" style="background: #efefef;">
                    <div class="row">
                        <div class="col text-left">Sub Total:</div>
                        <div class="col text-right">USDC<span class="prcbx ">{{number_format($ercTotalCrypto,2)}}</span></div>
                    </div>
                    <div class="row">
                        <div class="col text-left">Tax:</div>
                        <div class="col text-right">USDC<span class="prcbx">0.00</span></div>
                    </div>
                    <div class="row">
                        <div class="col text-left">Shipping:</div>
                        <div class="col text-right">USDC<span class="prcbx product_shipping_total_crypto" data-product_shipping_total= "">0.00</span></div>
                    </div>
                    <div class="row ttlbx">
                        <div class="col text-left">Total:</div>
                        <div class="col text-right">USDC: <span class="prcbx grandtotal_cal_crypto">{{number_format($ercTotalCrypto,2)}}</span></div>
                    </div>
                </div>
                   
                <h2 class="frmhd" style="margin-bottom: 0!important; margin-top:50px!important">USDC Payment Method</h2>
                
                <div class="pymntslct row" style="border-bottom: 0;">
                    
                
                    
                        <div class="box" id="box1">
                            <form action="/meta-mask" method="POST" id="orderDataSend">
                                {{ csrf_field() }}
                                <h2>Connect with Meta Mask</h2>
                                <?php  if (Session::has('userData')) { ?>
                                <input type="hidden" name="stripe_email" class="form-control" id="crypto_email" value="{{$userData->email}}">
                                <?php } ?>
                                <input type="hidden" name="stripe_email" class="form-control" id="crypto_email" value="">
                                <input type="hidden" id="shipping_data" name="shipping_data">                                
                                <input type="hidden" id="orderData" name="orderdata" value="">
                                <input class="primary-btn btn btn-primary" id="usdcprice"  hidden  price="0.2"   type="button" name="submit" value="Pay Via MetaMask">
                                <div class="row" style="padding:20px; padding-top:0; border-bottom:0px">
                                    <div class="col-md-12">
                                        <p class="text-center"> <?php echo '<div id="polygon"> </div>'; ?></p>
                                    </div>
                                </div>
                            </form>   
                        </div>
                </div>
                <script>

                var orderDataCrypto = <?php echo json_encode($mainArrayCrypto); ?>;
                    console.log("orderDataCrypto order",orderDataCrypto);
                    $('#orderData').val(JSON.stringify(orderDataCrypto));
                    // debugger

                </script>
               <?php } ?>
            </div>

            
        </div>
    <!-- Crypto End -->
    
        </div>

</div>
<section class="readypayment-contianer">

<div class="shipping_amount" style="display:none">


    <div class="container">
        <div class="row">
            <div class="col-sm-12">
                <div class="success-message-sec">
                    <h3 class="text-center">Thankyou! Your order is received, If you have purchased any virtual product please check orders section.</h3>

                </div>
            </div>
            <div class="submit-order text-center" style="    display: block;
                clear: both;
                float: none;
                margin: 40px auto;">

                <a href="{{ URL::to('/')  }}"  class="btn btn-primary btn-simple">Continue shopping</a>


            </div>
        </div>
    
    </div>
</div>
</section>
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
        });
        function stripeTokenHandler(token) {
        // Insert the token ID into the form so it gets submitted to the server
        var form = document.getElementById('payment-form');
        var hiddenInput = document.createElement('input');
        hiddenInput.setAttribute('type', 'hidden');
        hiddenInput.setAttribute('name', 'stripeToken');
        hiddenInput.setAttribute('value', token.id);
        form.appendChild(hiddenInput);
        $('#loading').show();
        // Submit the form
        // form.submit();
        }
</script>

<style>
.stripe-button-el{
    display:none;
}
</style>
<script src="{{ URL::asset('assets/js/jquery.validate.js'),env('HTTPS_STATUS') }}"></script>
<script>
  // get the success message from the session
  var successMsg = "{{ session('success') }}";

  // if the success message exists, show the alert and change the opacity
  if (successMsg) {
    alert(successMsg);
    document.getElementById("checkout-page").style.opacity = "0.5";
  }
</script>
<script>

    $(document).ready(function() {
        // console.log("orderDataCrypto" , orderDataCrypto);
        // debugger

        // var orderDataCrypto = []; 
        // if (orderDataCrypto && orderDataCrypto.length > 0) {
        //     $('#orderData').val(JSON.stringify(orderDataCrypto));
        // }
        $('#orderData_stripe').val(JSON.stringify(orderData));
        

        $("#orderDataSend").submit(function(e) {

        e.preventDefault(); // avoid to execute the actual submit of the form.

        var form = $(this);
        var firstname = $('#inputFirstNameShipping').val();
        var lastname = $('#inputLastNameShipping').val();
        var fullName = firstname + ' ' + lastname;
        var UserEmail = $('#useremail').val();
        var country = $('#s_country').val();
        var states = $('#states_countries').val();
        var street = $('#street_address').val();
        var city = $('#city_name').val();
        var zipcode = $('#zipcode').val();

        if (!country || !states || !street || !city || !zipcode || !fullName) {
        alert('Please fill in all required fields');
        } else {
        var data2 = '&full_name=' + fullName + '&s_country=' + country + '&city=' + city + '&states_countries=' + states + '&s_street=' + street + '&s_post_code=' + zipcode;

        jQuery('#shipping_data').val(data2);
        jQuery('#shipping_data_stripe').val(data2);

        var actionUrl = form.attr('action');
        var orderdata = orderDataCrypto;

        var fdata = {};
        fdata['order_info'] = orderdata;
        fdata['formData'] = $('#shipping_data').val();
        fdata['formEmail'] = $('#crypto_email').val();
        fdata['_token'] = '{{ csrf_token() }}';

        $.ajax({
            type: "POST",
            url: actionUrl,
            data: fdata, 
            success: function(data) {
            swal("Success!", "Created Order. if you purchased virtual product please check order section Thank you.", "success");
            var url = '{{Request::root()}}';
            
            // setTimeout(function() {
            //     window.location.href = url;
            // }, 3000);
            }
        });
        }




        });

        // On chnage request
        var tshpPrice = 0;
        $('.shipping_service_checkout').on('change',function (key, value ) {
                console.log('key',key);
                console.log('value',value);
                var shipment_data = $('#shispping_form').serialize();
                var that = $(this);
                var val = $(this).val().split("_");
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
                var  product_keyChange = val[12];
                // console.log("product_keyChange",product_keyChange);
                const keyObjectChange = {
                    product_keyChange : product_keyChange,
                    };
                   const numericKeysChange = Object.keys(keyObjectChange).filter(key => !isNaN(key));
                      console.log("before orderData", orderData);
                //    console.log("numericKeysChange", numericKeysChange)
                $('#loading').show();
                setTimeout(function() {
                $.ajax({
                    url: '/calculate_service_change',
                    type: "POST",
                    data: {value: value,storeId:storeId, origin_zip:origin_zip,shipping_option_id: shipping_option_id,weight:weight, product_length:product_length,
                        product_width:product_width,product_height:product_height,product_quantity: product_quantity, 
                        product_price:product_price,product_title:product_title, shipment_data:shipment_data, _token: '{{ csrf_token() }}'},
                    success: function (data) {
                        console.log('ship rate',data);
                        $('#loading').hide();
                    //    var total= $(".subtotal_cal").text();
                    //    var subtotal = total+data;
                      
                    //    var shipping_data = $(".subtotal_cal").text(subtotal);

                    
                      $(that).parent().parent().find('.shipping_price_calculated_crypto').html(data.rate);
                      $(that).parent().parent().find('.shipping_price_calculated_crypto').attr('data-ship-price',data.rate);

                      $(that).parent().parent().find('.shipping_price_calculated').html(data.rate);
                      $(that).parent().parent().find('.shipping_price_calculated').attr('data-ship-price',data.rate);
                     
                        var shipTotalPrice = 0;
                        var realTotalPrice = 0;
                        var flatShipping = 0;
                        $('.shipping_price_calculated').each(function( key, value ) {
                            shipTotalPrice = parseFloat(shipTotalPrice) + parseFloat($(this).attr('data-ship-price'));
                            $(".product_shipping_total").html(shipTotalPrice);
                        });
                        
                        $('.shipping_price_calculated_crypto').each(function( key, value ) {
                            shipTotalPrice = parseFloat(shipTotalPrice) + parseFloat($(this).attr('data-ship-price'));
                            $(".product_shipping_total").html(shipTotalPrice);
                        });
                        
                        // console.log("on change shipping price ", shipTotalPrice);

                        $('.real_product_price').each(function( key, value ) {
                            realTotalPrice = parseFloat(realTotalPrice) + parseFloat($(this).attr('data-real-price'));
                        });
                        // console.log("on change realTotalPrice price ", realTotalPrice);
                  
                        $('.shipping_price_calculated_flatshipping').each(function( key, value ) {
                            flatShipping = parseFloat(flatShipping) + parseFloat($(this).attr('data-ship-price'));
                        });
                        // console.log("on change flatShipping price ", flatShipping);
                        
                        var grandTotal_integer = shipTotalPrice + realTotalPrice + flatShipping;
                        var grandTotal = grandTotal_integer.toFixed(2);

                        orderData[storeId][product_keyChange].shipping_price = data.rate;
                        orderData[storeId][product_keyChange].shipping_method = value;

                        orderData[storeId][product_keyChange].ship_id = data.ship_id;
                        orderData[storeId][product_keyChange].rate_id = data.rate_id;
                        orderData[storeId][product_keyChange].store_data = data.store_data;
                        orderData[storeId][product_keyChange].user_info = data.user_info;
                        // orderData[storeId].ship_id = data.ship_id;
                        // orderData[storeId].rate_id = data.rate_id;
                        // orderData[storeId][product_keyChange].priceobj = data;
                        

                        //const totalPriceByStore = {};
                        // // Iterate over store IDs
                        // for (const storeId in orderData) {
                        // // Initialize total price for this store
                        // let totalStorePrice = 0;
                        
                        // // Iterate over product prices for this store
                        // for (const product of orderData) {
                        //     totalStorePrice += product.product_price;
                        // }
                        
                        // // Store the total price for this store
                        // totalPriceByStore[storeId] = totalStorePrice;
                        // }
                        // console.log("TOTALL", totalPriceByStore);

                        // orderData[storeId].store_ship_price = shipTotalPrice;
                        // orderData[storeId].store_total_price = grandTotal;
                        // orderData[storeId].shipping_method = value;
                        // orderData[storeId].priceobj = data;
                        tshpPrice = tshpPrice + shipTotalPrice;
                        console.log("AFTER orderData", orderData);
                        $('#orderData').val(JSON.stringify(orderData));
                        $('#orderData_stripe').val(JSON.stringify(orderData));
                        $('.subtotal_cal').html("USDC "+grandTotal);
                        $('.grandtotal_cal').html(grandTotal);
                        $('#amount-calculated').html("USDC "+grandTotal);
                        // $("#usdcprice").attr('price', grandTotal);
                        var stripe_total = grandTotal*100;
                        // loadStripeCheckout_Change(stripe_total);
                        $(".finalsubmit").show();
                                $("#total_amount_ship").val(stripe_total);
                    }
                });
            }, 2000); // Delay the request by 2 seconds      



            }); 
                     console.log('shipTotalPrice',tshpPrice);   


              // On chnage request Crypto
        $('.shipping_service_checkout_crypto').on('change',function () {
            
               
               var shipment_data = $('#shispping_form').serialize();
               var that = $(this);
               var val = $(this).val().split("_");
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
               console.log('product_key_change', product_key );
              
               $('#loading').show();
               setTimeout(function() {
               $.ajax({
                   url: '/calculate_service_change_crypto',
                   type: "POST",
                   data: {value: value,storeId:storeId, origin_zip:origin_zip,shipping_option_id: shipping_option_id,weight:weight, product_length:product_length,
                       product_width:product_width,product_height:product_height,product_quantity: product_quantity, 
                       product_price:product_price,product_title:product_title, shipment_data:shipment_data, _token: '{{ csrf_token() }}'},
                   success: function (data) {
                    $('#loading').hide();
                     // crypto
                     $(that).parent().parent().find('.shipping_price_calculated_crypto').html(data.rate);
                     $(that).parent().parent().find('.shipping_price_calculated_crypto').attr('data-ship-price',data.rate);
                     var shipTotalPrice_crypto = 0;
                     var realTotalPrice_crypto = 0;
                     var flatShipping_crypto = 0;
                    
                       //crypto
                       $('.shipping_price_calculated_crypto').each(function( key, value ) {
                           shipTotalPrice_crypto = parseFloat(shipTotalPrice_crypto) + parseFloat($(this).attr('data-ship-price'));
                           $(".product_shipping_total_crypto").html(shipTotalPrice_crypto);
                          
                       });

                       // crypto
                       $('.real_product_price_crypto').each(function( key, value ) {
                           realTotalPrice_crypto = parseFloat(realTotalPrice_crypto) + parseFloat($(this).attr('data-real-price'));
                          // console.log('valeu',$(this).attr('data-real-price'));
                       });
                       //crypto
                       $('.shipping_price_calculated_flatshipping_crypto').each(function( key, value ) {
                           flatShipping_crypto = parseFloat(flatShipping_crypto) + parseFloat($(this).attr('data-ship-price'));
                       });
                       // crypto
                       var grandTotal_integer_crypto = shipTotalPrice_crypto + realTotalPrice_crypto + flatShipping_crypto;
                       var grandTotal_crypto = grandTotal_integer_crypto.toFixed(2);

                       orderDataCrypto[storeId][product_key].shipping_price = data.rate;
                       orderDataCrypto[storeId][product_key].shipping_method = value;
                       orderDataCrypto[storeId].shipping_method = value;
                    //    orderDataCrypto[storeId].store_ship_price = shipTotalPrice_crypto;
                    //    orderDataCrypto[storeId].store_total_price = grandTotal_crypto;
                       orderDataCrypto[storeId][product_key].ship_id = data.ship_id;
                       orderDataCrypto[storeId][product_key].rate_id = data.rate_id;
                       orderDataCrypto[storeId][product_key].store_data = data.store_data;
                       orderDataCrypto[storeId][product_key].user_info = data.user_info;
                       orderDataCrypto[storeId].ship_id = data.ship_id;
                       orderDataCrypto[storeId].rate_id = data.rate_id;


                       console.log("orderDataCrypto",orderDataCrypto )
                       $('#orderData').val(JSON.stringify(orderDataCrypto));
                       
                       $('.grandtotal_cal_crypto').html(grandTotal_crypto);
                    //    $("#usdcprice").attr('price', grandTotal_crypto);
                       var actual_price= $("#usdcprice").attr('price', "0.2");
                       $(".finalsubmit").show();


                   }
               });
            }, 2000); // Delay the request by 2 seconds
           });
           // Payment with meta mask hardcoded
           $("#usdcprice").attr('price', "0.2");
     
           $('#s_country , #states_countries, #street_address, #city_name, #zipcode').on('keyup', function() {

            var country = $('#s_country').val();
            var firstname = $('#inputFirstNameShipping').val();
            var lastname = $('#inputLastNameShipping').val();
            var fullName = firstname + ' ' + lastname;
            var UserEmail = $('#useremail').val();
            var country = $('#s_country').val();
            var states = $('#states_countries').val();
            var street = $('#street_address').val();
            var city = $('#city_name').val();
            var zipcode = $('#zipcode').val();

            data2 = '&full_name=' + fullName + '&s_country=' + country + '&city=' + city + '&states_countries='
             + states + '&s_street=' + street + '&s_post_code=' + zipcode;

             if (country && states && street && city && zipcode && fullName) {
                jQuery('#shipping_data').val(data2);
                jQuery('#shipping_data_stripe').val(data2);
             }

            if (country && states && street && city && zipcode.length >= 5) {

                if (UserEmail) {
                    // input field is not empty, set the value 
                    $("#stripe_email").val(UserEmail);
                    $("#crypto_email").val(UserEmail);
                } else {
                    // input field is empty, set the value 
                    $("#stripe_email").val("abc@gmail.com");
                    $("#crypto_email").val("abc@gmail.com");
                }
               
                shipment_data = $('#shispping_form').serialize(); 
        
                // Flate shipping condition
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
                
                });
                }

                var myArray = [];
                var prevValue = null;
                // console.log("prevValue", prevValue);
                // console.log("on set value orderData", orderData);
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
                   var   product_height = val[8];
                   var   product_quantity = val[9];
                   var   product_price = val[10];
                   var   product_title = val[11];
                   var product_key = val[12];
                   //    console.log("product_key", product_key);
                  
                    const keyObject = {};
                    keyObject[product_key] = product_key;

                    var numericKeys = Object.keys(keyObject).filter(key => !isNaN(key)).map(key => parseInt(key));
               
                  
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
                   product_key : product_key,
                   
                    };
              
                    //     let shipment = shipment_data.indexOf('=&') > -1;
                        
                    //    debugger
                 
                    var total =  $('.subtotal_product_stripe').text();
                    // console.log("before request",total);
                    if (prevValue === null) {
                            prevValue = productId;
                            //  console.log('first value of if condition', prevValue);
                        
                         // console.log("value of request", val);
                         $('#loading').show();
                         setTimeout(function() {
                            $.ajax({
                                url: '/calculate_service',
                                type: "POST",
                                dataType: "json",
                                data: {value: value,storeId:storeId, origin_zip:origin_zip,shipping_option_id: shipping_option_id,weight:weight, 
                                    product_length:product_length,product_width:product_width,product_height:product_height, product_quantity: product_quantity, 
                                    product_price:product_price,product_title:product_title, shipment_data:shipment_data, _token: '{{ csrf_token() }}'},
                                success: function (data) {
                                    $('#loading').hide();
                                    // console.log("get shipping data ", data );
                                    // debugger
                                 
                                    if(data.error){
                                        shipping_error = data.error;
                                        shippingErrorResponse(shipping_error);
                                    
                                    }else{
                                        $('.pymntslct').show();
                                    }
                                    
                                    var shipTotalPrice = 0;
                                    var realTotalPrice = 0;
                                    $('.shipping_price_calculated').each(function( key, value ) {
                                        shipTotalPrice = parseFloat(shipTotalPrice) + parseFloat($(this).attr('data-ship-price'));
                                    });
                                    // console.log("shipTotalPrice",shipTotalPrice);
                                    // crypto
                                    // $('.shipping_price_calculated_crypto').each(function( key, value ) {
                                    //     shipTotalPrice = parseFloat(shipTotalPrice) + parseFloat($(this).attr('data-ship-price'));
                                    // });

                                    $('.real_product_price').each(function( key, value ) {
                                        realTotalPrice = parseFloat(realTotalPrice) + parseFloat($(this).attr('data-real-price'));
                                    // console.log('valeu',$(this).attr('data-real-price'));
                                    });
                                    // var total =  $('.subtotal_product_stripe').text();
                                    var grandTotal_integer = shipTotalPrice + realTotalPrice + flatShipping;
                                    var grandTotal = grandTotal_integer;
                                    // console.log("after grandTotal", grandTotal)


                                    var vservice =   $("#list_"+productId).find('.'+data.service);
                                    data.forEach( (item )=>{
                                        
                                    orderData[storeId][product_key].shipping_price = item.rate;
                                    orderData[storeId][product_key].shipping_method = item.service;
                                    // orderData[storeId][key].shipping_method = value;
                                    // orderData[storeId].store_ship_price = shipTotalPrice;
                                    // orderData[storeId].store_total_price = grandTotal;
                                    // orderData[storeId][product_key].store_data = data.store_data;
                                    // orderData[storeId][product_key].user_info = data.user_info;
                                    orderData[storeId][product_key].ship_id = item.ship_id;
                                    orderData[storeId][product_key].rate_id = item.rate_id;
                                      
                                        if(item.service == 'ParcelSelect'){
                                            $("#list_"+productId).find('.' + item.service).attr('disabled', false);
                                            $("#list_"+productId).find(".shipping_price_calculated").html(item.rate);
                                            $("#list_"+productId).find(".shipping_price_calculated").attr('data-ship-price',item.rate);
                                            // crypto
                                            // $("#list_crypto_"+productId).find('.' + item.service).attr('disabled', false);
                                            // $("#list_crypto_"+productId).find(".shipping_price_calculated_crypto").html(item.rate);
                                            // $("#list_crypto_"+productId).find(".shipping_price_calculated_crypto").attr('data-ship-price',item.rate);
                                        }
                                        if(item.service == 'Express'){
                                            $("#list_"+productId).find('.' + item.service).attr('disabled', false);
                                            $("#list_"+productId).find(".shipping_price_calculated").html(item.rate);
                                            $("#list_"+productId).find(".shipping_price_calculated").attr('data-ship-price',item.rate);
                                            // crypto
                                            // $("#list_crypto_"+productId).find('.' + item.service).attr('disabled', false);
                                            // $("#list_crypto_"+productId).find(".shipping_price_calculated_crypto").html(item.rate);
                                            // $("#list_crypto_"+productId).find(".shipping_price_calculated_crypto").attr('data-ship-price',item.rate);
                                        }
                                        if(item.service == 'Priority'){
                                            $("#list_"+productId).find('.' + item.service).attr('disabled', false);
                                            $("#list_"+productId).find(".shipping_price_calculated").html(item.rate);
                                            $("#list_"+productId).find(".shipping_price_calculated").attr('data-ship-price',item.rate);
                                            // crypto
                                            // $("#list_crypto_"+productId).find('.' + item.service).attr('disabled', false);
                                            // $("#list_crypto_"+productId).find(".shipping_price_calculated_crypto").html(item.rate);
                                            // $("#list_crypto_"+productId).find(".shipping_price_calculated_crypto").attr('data-ship-price',item.rate);
                                        }
                                        if(item.service == 'First'){
                                            $("#list_"+productId).find('.' + item.service).attr('disabled', false);
                                            $("#list_"+productId).find(".shipping_price_calculated").html(item.rate);
                                            $("#list_"+productId).find(".shipping_price_calculated").attr('data-ship-price',item.rate);
                                            // // crypto
                                            // $("#list_crypto_"+productId).find('.' + item.service).attr('disabled', false);
                                            // $("#list_crypto_"+productId).find(".shipping_price_calculated_crypto").html(item.rate);
                                            // $("#list_crypto_"+productId).find(".shipping_price_calculated_crypto").attr('data-ship-price',item.rate);
                                        }
                                    })
                                    let elm = $("#list_"+productId).find('option:not(:disabled)').first();
                                    // let elm_crypto = $("#list_crypto_"+productId).find('option:not(:disabled)').first();
                                    
                                    elm.attr('selected', true).trigger('change');
                                    // elm_crypto.attr('selected', true).trigger('change');

                                  
                                    $("#usdcprice").attr('price', "0.2");
                                    // pay with metamask
                                //    var actual_price= $("#usdcprice").attr('price', "0.2");
                                //    console.log("flatShipping", flatShipping);

                                //    console.log("actual_price",actual_price);
                                    

                                   

                                    
                                    $('#orderData').val(JSON.stringify(orderData));
                                    $('#orderData_stripe').val(JSON.stringify(orderData));
                                  
                                    var stripe_total = grandTotal*100;
                            
                                    $("#total_amount_ship").val(stripe_total);
                                    $(".finalsubmit").show();
                                    $('.subtotal_cal').html("USDC "+grandTotal);
                                    $('.grandtotal_cal').html(grandTotal);
                                    $('#amount-calculated').html("USDC "+grandTotal);
                                    // $("#usdcprice").attr('price', grandTotal);

                                }
                        
                            });
                         myArray.push(obj);
                        }, 2000); // Delay the request by 2 seconds
                        }else if (prevValue === productId) {
                            // console.log('on load if else condition second');

                            // Do something if the value is repeating
                            // console.log('Value ' + productId + ' is repeating');
                        }else {
                             prevValue = productId;
                             $('#loading').show();
                             setTimeout(function() {
                            $.ajax({
                            url: '/calculate_service',
                            type: "POST",
                            dataType: "json",
                            data: {value: value,storeId:storeId, origin_zip:origin_zip,shipping_option_id: shipping_option_id,weight:weight, 
                                product_length:product_length,product_width:product_width,product_height:product_height, product_quantity: product_quantity, 
                                product_price:product_price,product_title:product_title, shipment_data:shipment_data, _token: '{{ csrf_token() }}'},
                            success: function (data) {
                                $('#loading').hide();
                                // debugger
                              
                                if(data.error){
                                    shipping_error = data.error;
                                    shippingErrorResponse(shipping_error);
                                    // accessGlobalResponse();
                                    
                                    // console.log("dataerror",shipping_error);
                                    //return false;
                                }else{
                                    $('.pymntslct').show();
                                }
                                
                                // console.log("return rate",data.rate);
                                $(that).next(".shipping_price_calculated").html(data.rate);
                                $(that).next(".shipping_price_calculated").attr('data-ship-price',data.rate);
                                // $(that).next(".shipping_price_calculated_crypto").html(data.rate);
                                // $(that).next(".shipping_price_calculated_crypto").attr('data-ship-price',data.rate);

                                var shipTotalPrice = 0;
                                var realTotalPrice = 0;
                                $('.shipping_price_calculated').each(function( key, value ) {
                                
                                    shipTotalPrice = parseFloat(shipTotalPrice) + parseFloat($(this).attr('data-ship-price'));
                                //  console.log('valeu',$(this).attr('data-ship-price'));
                                });
                                // $('.shipping_price_calculated_crypto').each(function( key, value ) {
                                
                                //     shipTotalPrice = parseFloat(shipTotalPrice) + parseFloat($(this).attr('data-ship-price'));
                                // //  console.log('valeu',$(this).attr('data-ship-price'));
                                // });
                                var vservice =   $("#list_"+productId).find('.'+data.service);
                                data.forEach( (item )=>{
                                        
                                        orderData[storeId][product_key].shipping_price = item.rate;
                                        orderData[storeId][product_key].shipping_method = item.service;
                                        // orderData[storeId][key].shipping_method = value;
                                        // orderData[storeId].store_ship_price = shipTotalPrice;
                                        // orderData[storeId].store_total_price = grandTotal;
                                        // orderData[storeId][product_key].store_data = data.store_data;
                                        // orderData[storeId][product_key].user_info = data.user_info;
                                        orderData[storeId][product_key].ship_id = item.ship_id;
                                        orderData[storeId][product_key].rate_id = item.rate_id;
                                          
                                            if(item.service == 'ParcelSelect'){
                                                $("#list_"+productId).find('.' + item.service).attr('disabled', false);
                                                $("#list_"+productId).find(".shipping_price_calculated").html(item.rate);
                                                $("#list_"+productId).find(".shipping_price_calculated").attr('data-ship-price',item.rate);
                                                // crypto
                                                // $("#list_crypto_"+productId).find('.' + item.service).attr('disabled', false);
                                                // $("#list_crypto_"+productId).find(".shipping_price_calculated_crypto").html(item.rate);
                                                // $("#list_crypto_"+productId).find(".shipping_price_calculated_crypto").attr('data-ship-price',item.rate);
                                            }
                                            if(item.service == 'Express'){
                                                $("#list_"+productId).find('.' + item.service).attr('disabled', false);
                                                $("#list_"+productId).find(".shipping_price_calculated").html(item.rate);
                                                $("#list_"+productId).find(".shipping_price_calculated").attr('data-ship-price',item.rate);
                                                // crypto
                                                // $("#list_crypto_"+productId).find('.' + item.service).attr('disabled', false);
                                                // $("#list_crypto_"+productId).find(".shipping_price_calculated_crypto").html(item.rate);
                                                // $("#list_crypto_"+productId).find(".shipping_price_calculated_crypto").attr('data-ship-price',item.rate);
                                            }
                                            if(item.service == 'Priority'){
                                                $("#list_"+productId).find('.' + item.service).attr('disabled', false);
                                                $("#list_"+productId).find(".shipping_price_calculated").html(item.rate);
                                                $("#list_"+productId).find(".shipping_price_calculated").attr('data-ship-price',item.rate);
                                                // crypto
                                                // $("#list_crypto_"+productId).find('.' + item.service).attr('disabled', false);
                                                // $("#list_crypto_"+productId).find(".shipping_price_calculated_crypto").html(item.rate);
                                                // $("#list_crypto_"+productId).find(".shipping_price_calculated_crypto").attr('data-ship-price',item.rate);
                                            }
                                            if(item.service == 'First'){
                                                $("#list_"+productId).find('.' + item.service).attr('disabled', false);
                                                $("#list_"+productId).find(".shipping_price_calculated").html(item.rate);
                                                $("#list_"+productId).find(".shipping_price_calculated").attr('data-ship-price',item.rate);
                                                // // crypto
                                                // $("#list_crypto_"+productId).find('.' + item.service).attr('disabled', false);
                                                // $("#list_crypto_"+productId).find(".shipping_price_calculated_crypto").html(item.rate);
                                                // $("#list_crypto_"+productId).find(".shipping_price_calculated_crypto").attr('data-ship-price',item.rate);
                                            }
                                        })
                                    let elm = $("#list_"+productId).find('option:not(:disabled)').first();
                                    
                                    elm.attr('selected', true).trigger('change');
                             
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
                  
                               
      

                                orderData[storeId][product_key].shipping_price = data.rate;
                                orderData[storeId][product_key].shipping_method = data.service;
                                // orderData[storeId].store_ship_price = shipTotalPrice;
                                // orderData[storeId].store_total_price = grandTotal;
                                orderData[storeId][product_key].store_data = data.store_data;
                                orderData[storeId][product_key].user_info = data.user_info;
                                orderData[storeId][product_key].ship_id = data.ship_id;
                                orderData[storeId][product_key].rate_id = data.rate_id;


                                $('#orderData').val(JSON.stringify(orderData));
                                $('#orderData_stripe').val(JSON.stringify(orderData));
                                // console.log('=====last start=====');
                                $('.subtotal_cal').html("USDC "+grandTotal);
                                $('.grandtotal_cal').html(grandTotal);
                                $('#amount-calculated').html("USDC "+grandTotal);
                                // $("#usdcprice").attr('price', grandTotal);
                                var stripe_total = grandTotal*100;

                                $(".finalsubmit").show();
                                $("#total_amount_ship").val(stripe_total);
                                $("#usdcprice").attr('price', "0.2");
    

                            }
                      
                        });
                    }, 2000); // Delay the request by 2 seconds
                            // Do something else if the value is not repeating
                            
                            prevValue = productId;
                       }
            });

            // Crypto Calculate Price of Easypost

              // Flate shipping condition
              var flatShippingCrypto = 0;
                $('.shipping_price_calculated_flatshipping').each(function( key, value ) {
                    flatShippingCrypto = parseFloat(flatShipping) + parseFloat($(this).attr('data-ship-price'));
                });
          
                if( typeof flatShippingCrypto !== 'undefined'){
                    // console.log("called");
                    var realpriceCrypto = 0;
                    $('.get_data_calulate_ship_flatship_crypto').each(function( key, value ) {
                        var that = $(this);
                        var val = $(this).val().split("_");
                        var storeid = val[0];
                        var productId = val[1];
                
                });
                }

                var myArrayCrypto = [];
                var prevValueCrypto = null;
                // console.log("prevValue", prevValue);
                // console.log("on set value orderData", orderData);
                $('.get_data_calulate_ship_crypto').each(function( key, value ) {
                    
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
                   var   product_height = val[8];
                   var   product_quantity = val[9];
                   var   product_price = val[10];
                   var   product_title = val[11];
                   var   product_key = val[12];
                       console.log("product_key_crypto", product_key);
                  
                    const keyObject = {};
                    keyObject[product_key] = product_key;

                    var numericKeys = Object.keys(keyObject).filter(key => !isNaN(key)).map(key => parseInt(key));
               
                  
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
                   product_key : product_key,
                   
                    };
              
                    //     let shipment = shipment_data.indexOf('=&') > -1;
                        
                       
                    if (prevValueCrypto === null) {
                        prevValueCrypto = productId;
                        $('#loading').show();
                            //  console.log('first value of if condition', prevValue);
                         // console.log("value of request", val);
                         setTimeout(function() {
                            $.ajax({
                                // orderData[storeId].store_total_price = grandTotal;
                                url: '/calculate_service_crypto',
                                type: "POST",
                                dataType: "json",
                                data: {value: value,storeId:storeId, origin_zip:origin_zip,shipping_option_id: shipping_option_id,weight:weight, 
                                    product_length:product_length,product_width:product_width,product_height:product_height, product_quantity: product_quantity, 
                                    product_price:product_price,product_title:product_title, shipment_data:shipment_data, _token: '{{ csrf_token() }}'},
                                success: function (data) {
                                    $('#loading').hide();
                                    // console.log("get shipping data ", data );
                                    // debugger
                                 
                                    if(data.error){
                                        shipping_error = data.error;
                                        shippingErrorResponse(shipping_error);
                                    
                                    }else{
                                        $('.pymntslct').show();
                                    }
                                    
                                    var shipTotalPriceCrypto = 0;
                                    var realTotalPriceCrypto = 0;
                                  
                                    // crypto
                                    $('.shipping_price_calculated_crypto').each(function( key, value ) {
                                        shipTotalPriceCrypto = parseFloat(shipTotalPriceCrypto) + parseFloat($(this).attr('data-ship-price'));
                                    });
                                    console.log("shipTotalPriceCrypto", shipTotalPriceCrypto);

                                    $('.real_product_price_crypto').each(function( key, value ) {
                                        realTotalPriceCrypto = parseFloat(realTotalPriceCrypto) + parseFloat($(this).attr('data-real-price'));
                                    // console.log('valeu',$(this).attr('data-real-price'));
                                    });

                                    var grandTotal_integer = shipTotalPriceCrypto + realTotalPriceCrypto + flatShippingCrypto;
                                    var grandTotal = grandTotal_integer.toFixed(2);


                                
                                    data.forEach( (item )=>{
                                        
                                        orderDataCrypto[storeId][product_key].shipping_price = item.rate;
                                        orderDataCrypto[storeId][product_key].shipping_method = item.service;
                                        // orderDataCrypto[storeId][key].shipping_method = value;
                                        // orderDataCrypto[storeId].store_ship_price = shipTotalPriceCrypto;
                                        // orderDataCrypto[storeId].store_total_price = grandTotal;
                                        // orderDataCrypto[storeId][product_key].store_data = data.store_data;
                                        // orderDataCrypto[storeId][product_key].user_info = data.user_info;
                                        orderDataCrypto[storeId][product_key].ship_id = item.ship_id;
                                        orderDataCrypto[storeId][product_key].rate_id = item.rate_id;

                                      
                                      
                                        if(item.service == 'ParcelSelect'){
                                           
                                            // crypto
                                            $("#list_crypto_"+productId).find('.' + item.service).attr('disabled', false);
                                            $("#list_crypto_"+productId).find(".shipping_price_calculated_crypto").html(item.rate);
                                            $("#list_crypto_"+productId).find(".shipping_price_calculated_crypto").attr('data-ship-price',item.rate);
                                        }
                                        if(item.service == 'Express'){
                                           
                                            // crypto
                                            $("#list_crypto_"+productId).find('.' + item.service).attr('disabled', false);
                                            $("#list_crypto_"+productId).find(".shipping_price_calculated_crypto").html(item.rate);
                                            $("#list_crypto_"+productId).find(".shipping_price_calculated_crypto").attr('data-ship-price',item.rate);
                                        }
                                        if(item.service == 'Priority'){
                                           
                                            // crypto
                                            $("#list_crypto_"+productId).find('.' + item.service).attr('disabled', false);
                                            $("#list_crypto_"+productId).find(".shipping_price_calculated_crypto").html(item.rate);
                                            $("#list_crypto_"+productId).find(".shipping_price_calculated_crypto").attr('data-ship-price',item.rate);
                                        }
                                        if(item.service == 'First'){
                                            
                                            // crypto
                                            $("#list_crypto_"+productId).find('.' + item.service).attr('disabled', false);
                                            $("#list_crypto_"+productId).find(".shipping_price_calculated_crypto").html(item.rate);
                                            $("#list_crypto_"+productId).find(".shipping_price_calculated_crypto").attr('data-ship-price',item.rate);
                                        }
                                    })
                                    
                                    let elm_crypto = $("#list_crypto_"+productId).find('option:not(:disabled)').first();
                                    elm_crypto.attr('selected', true).trigger('change');

                                  
                                   
                                    // pay with metamask
                                //    var actual_price= $("#usdcprice").attr('price', "0.2");
                                //    console.log("flatShipping", flatShipping);

                                //    console.log("actual_price",actual_price);
                                    

                                   

                                    
                                    $('#orderData').val(JSON.stringify(orderDataCrypto));
                                    $(".finalsubmit").show();
                                    $('.subtotal_cal').html("USDC "+grandTotal);
                                    $('.grandtotal_cal').html(grandTotal);
                                    $('#amount-calculated').html("USDC "+grandTotal);
                                    // $("#usdcprice").attr('price', grandTotal);
                                    $("#usdcprice").attr('price', "0.2");

                                }
                        
                            });
                        }, 2000); // Delay the request by 2 seconds
                        myArrayCrypto.push(obj);
                        }else if (prevValueCrypto === productId) {
                            // console.log('on load if else condition second');

                            // Do something if the value is repeating
                            // console.log('Value ' + productId + ' is repeating');
                        }else {
                            $('#loading').show();

                            prevValueCrypto = productId;
                            setTimeout(function() {
                            $.ajax({
                            url: '/calculate_service_crypto',
                            type: "POST",
                            dataType: "json",
                            data: {value: value,storeId:storeId, origin_zip:origin_zip,shipping_option_id: shipping_option_id,weight:weight, 
                                product_length:product_length,product_width:product_width,product_height:product_height, product_quantity: product_quantity, 
                                product_price:product_price,product_title:product_title, shipment_data:shipment_data, _token: '{{ csrf_token() }}'},
                            success: function (data) {
                                $('#loading').hide();
                                // debugger
                              
                                if(data.error){
                                    shipping_error = data.error;
                                    shippingErrorResponse(shipping_error);
                                    // accessGlobalResponse();
                                    
                                    //console.log("dataerror",shipping_error);
                                    //return false;
                                }else{
                                    $('.pymntslct').show();
                                }
                                
                                // console.log("return rate",data.rate);
                                
                                $(that).next(".shipping_price_calculated_crypto").html(data.rate);
                                $(that).next(".shipping_price_calculated_crypto").attr('data-ship-price',data.rate);

                                var shipTotalPrice = 0;
                                var realTotalPrice = 0;
                            
                                $('.shipping_price_calculated_crypto').each(function( key, value ) {
                                
                                    shipTotalPrice = parseFloat(shipTotalPrice) + parseFloat($(this).attr('data-ship-price'));
                                //  console.log('valeu',$(this).attr('data-ship-price'));
                                });
                                var vservice =   $("#list_"+productId).find('.'+data.service);
                                    data.forEach( (item )=>{
                                   
                                        if(item.service == 'ParcelSelect'){
                                           
                                           // crypto
                                           $("#list_crypto_"+productId).find('.' + item.service).attr('disabled', false);
                                           $("#list_crypto_"+productId).find(".shipping_price_calculated_crypto").html(item.rate);
                                           $("#list_crypto_"+productId).find(".shipping_price_calculated_crypto").attr('data-ship-price',item.rate);
                                       }
                                       if(item.service == 'Express'){
                                          
                                           // crypto
                                           $("#list_crypto_"+productId).find('.' + item.service).attr('disabled', false);
                                           $("#list_crypto_"+productId).find(".shipping_price_calculated_crypto").html(item.rate);
                                           $("#list_crypto_"+productId).find(".shipping_price_calculated_crypto").attr('data-ship-price',item.rate);
                                       }
                                       if(item.service == 'Priority'){
                                          
                                           // crypto
                                           $("#list_crypto_"+productId).find('.' + item.service).attr('disabled', false);
                                           $("#list_crypto_"+productId).find(".shipping_price_calculated_crypto").html(item.rate);
                                           $("#list_crypto_"+productId).find(".shipping_price_calculated_crypto").attr('data-ship-price',item.rate);
                                       }
                                       if(item.service == 'First'){
                                           
                                           // crypto
                                           $("#list_crypto_"+productId).find('.' + item.service).attr('disabled', false);
                                           $("#list_crypto_"+productId).find(".shipping_price_calculated_crypto").html(item.rate);
                                           $("#list_crypto_"+productId).find(".shipping_price_calculated_crypto").attr('data-ship-price',item.rate);
                                       }
                                })
                                let elm_crypto = $("#list_crypto_"+productId).find('option:not(:disabled)').first();
                                    elm_crypto.attr('selected', true)
                                    // .trigger('change');

                                    // let elm = $("#list_"+productId).find('option:not(:disabled)').first();
                                    
                                    // elm.attr('selected', true).trigger('change');
                             
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
                  
                               
      

                                orderDataCrypto[storeId][product_key].shipping_price = data.rate;
                                orderDataCrypto[storeId][product_key].shipping_method = data.service;
                                // orderDataCrypto[storeId].store_ship_price = shipTotalPrice;
                                // orderDataCrypto[storeId].store_total_price = grandTotal;
                                orderDataCrypto[storeId][product_key].store_data = data.store_data;
                                orderDataCrypto[storeId][product_key].user_info = data.user_info;
                                orderDataCrypto[storeId][product_key].ship_id = data.ship_id;
                                orderDataCrypto[storeId][product_key].rate_id = data.rate_id;


                                $('#orderData').val(JSON.stringify(orderDataCrypto));
                               
                                // console.log('=====last start=====');
                                $('.subtotal_cal').html("USDC "+grandTotal);
                                $('.grandtotal_cal').html(grandTotal);
                                $('#amount-calculated').html("USDC "+grandTotal);
                                // $("#usdcprice").attr('price', grandTotal);
                                $("#usdcprice").attr('price', "0.2");
                                

                                $(".finalsubmit").show();
                                
    

                            }
                      
                        });
                    }, 2000); // Delay the request by 2 seconds
                        
                            // Do something else if the value is not repeating
                            
                            prevValueCrypto = productId;
                       }
            });


          }
        });
    });


    function shippingErrorResponse(response) {
    swal("If Any Query Please Contact Store Owner!", response, "error");
        // $('.chkout').addClass('disable-page');
        var selectElement = $('#shipping_service');

        // reset select element
        selectElement.prop('selectedIndex', -1);
        $('.pymntslct').hide();
        // $("#city_name").val('');
        $("#zipcode").val('');
    }
    // login validaton start

    $("#shispping_form").validate({
        errorElement: "span",
        rules: {
            useremail: {required: true,email:true},
            first_name: {required: true},
            last_name: {required: true},
            country: {required: true, min:1},
            // states_countries: {required: true},
            street_address: {required: true},
            city_name: {required: true},
            zipcode: {required: true},
        },
        messages: {
            useremail: {required: "Email is required."},
            first_name: {required: "First name is required."},
            last_name: {required: "Last name is required."},
            country: {required: "Country is required.",
                min: "Please select Country"},
            // states_countries: {required: "States is required.",},
            street_address: {required: "Address is required."},
            city_name: {required: "City is required."},
            zipcode: {required: "Zipcode is required."},
        },
        errorPlacement: function (error, element) {
            error.appendTo(element.closest('.form-group'));
        },
        submitHandler: function (form) {

            // debugger
            stripe.createToken(card).then(function(result) {
                
                if (result.error) {
                    // Inform the user if there was an error
                    var errorElement = document.getElementById('card-errors');
                    errorElement.textContent = result.error.message;
                } else 
                {
                    
                    // form.submit();
                    var form = document.getElementById('payment-form');
                    var hiddenInput = document.createElement('input');
                    hiddenInput.setAttribute('type', 'hidden');
                    hiddenInput.setAttribute('name', 'stripeToken');
                    hiddenInput.setAttribute('value', result.token.id);
                    form.appendChild(hiddenInput);

                    var firstname = $('#inputFirstNameShipping').val();
                    var lastname = $('#inputLastNameShipping').val();
                    var fullName = firstname + ' ' + lastname;
                    var UserEmail = $('#useremail').val();
                    var country = $('#s_country').val();
                    var states = $('#states_countries').val();
                    var street = $('#street_address').val();
                    var city = $('#city_name').val();
                    var zipcode = $('#zipcode').val();

                    if (!country && !states && !street && !city && !zipcode && !fullName && !UserEmail) {
                    alert('Please fill in all Shipping Form required fields');
                    return false;
                    } else {
                        var data2 = '&full_name=' + fullName + '&s_country=' + country + '&city=' + city + '&states_countries=' + states + '&s_street=' + street + '&s_post_code=' + zipcode;

                                if (UserEmail) {
                                    // input field is not empty, set the value 
                                    $("#stripe_email").val(UserEmail);
                                    $("#crypto_email").val(UserEmail);
                                }
                    
                        jQuery('#shipping_data_stripe').val(data2);
                    }
                    $('#loading').show();
                    $.ajax({
                            url: $('#payment-form').attr('action'), 
                            type: $('#payment-form').attr('method'), 
                            data: $('#payment-form').serialize(), 
                            success: function(response) {
                                $('#loading').hide();
                                $('.readypayment-contianer').removeClass('show-section');
                                $('.shipping-contianer').removeClass('hide-section');
                                $('.shipping_amount').show();
                                $('#checkout-page').hide();
                                // setTimeout(function() {
                                //     window.location.href = '/';
                                // }, 5000);
                            },
                            error: function(xhr, status, error) {
                                // Handle AJAX error here
                            }
                        });
                        // $('#payment-form').submit();
                    
                }
            });
        }
        
    });
        $('#place-order-btn').on('click',function() {
            
            $('#shispping_form').submit();
         
                   
            // var selectedValue = $('#s_country').val();
            // if (selectedValue === '') {
            // swal("Error", "Please select country", "error");
            
            // return false; // stop the process
            // } else {
            
            // return true; // continue with the process
            // }
            // var selectedEmail = $('#useremail').val();
            // var pattern = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
            // if (!pattern.test(selectedEmail)) {
            //     alert('Please enter a valid email address.');
            //     event.preventDefault();
            // }
            // if(selectedEmail === ''){
            //     swal("Error", "Please Enter your email", "error");
            
            // return false; // stop the process
            // }else{
            //     return true;
            // }

                    
                        // debugger
                            
        
        
            var firstname = $('#inputFirstNameShipping').val();
            var lastname = $('#inputLastNameShipping').val();
            var fullName = firstname + ' ' + lastname;
            var UserEmail = $('#useremail').val();
            var country = $('#s_country').val();
            var states = $('#states_countries').val();
            var street = $('#street_address').val();
            var city = $('#city_name').val();
            var zipcode = $('#zipcode').val();

            if (!country && !states && !street && !city && !zipcode && !fullName && !UserEmail) {
            alert('Please fill in all Shipping Form required fields');
            return false;
            } else {
            var data2 = '&full_name=' + fullName + '&s_country=' + country + '&city=' + city + '&states_countries=' + states + '&s_street=' + street + '&s_post_code=' + zipcode;

            if (UserEmail) {
                        // input field is not empty, set the value 
                        $("#stripe_email").val(UserEmail);
                        $("#crypto_email").val(UserEmail);
                    }
        
            jQuery('#shipping_data_stripe').val(data2);
            }
        });

        $('.paywithmetamask').click(function() {
            $('#shispping_form').valid();
        });

    
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
                  
                    jQuery.each(obj, function (i, item) {

                        options += '<option value="' + item.state_id + '">' + item.state_name + '</option>';
                    });
                    jQuery('#states_countries').html(options);
                }
            });
        }

    }
    var showFormCheckbox = document.getElementById('showFormCheckbox');
    var form = document.querySelector('.form');

    showFormCheckbox.addEventListener('change', function() {
        if (this.checked) {
            form.style.display = 'block';
        } else {
            form.style.display = 'none';
        }
    });
</script>

<script>




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
</script>
<script src="{{ mix('js/app.js') }}" defer></script>
@endsection 