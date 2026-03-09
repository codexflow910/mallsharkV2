
@extends('layouts.new-main')
<!-- Main Banner Starts -->
@section('content')
@section('title', 'Checkout')
@section('google-analytics')
{{-- <script src="https://code.jquery.com/jquery-3.5.1.min.js" crossorigin="anonymous"></script>
<script src="https://js.stripe.com/v3/"></script> --}}
<link href="{{ mix('css/app.css') }}" rel="stylesheet">
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
<?php
if (!getCurrentUserId()) {
    echo '<div class="alert alert-danger container" style="margin-top:20px"><h5 style="text-align:center;" >Please login to continue</h5></div>';
}
if (Session::has('orderData')) {
    $sess = Session::get('orderData');

    $orderData = $sess;
}
$total_amount = isset($sess['total_amount']) ? $sess['total_amount'] : '';
$orderDatatotal = $orderData['total_price'];
$total_amount = $orderDatatotal;
//$orderDatatotal = $orderDatatotal * 10;
$orderDatatotal = $orderDatatotal * 100;
// $ercTotal = $orderData['total_price'] / 100;
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
                                    $counter = 1;
                                    foreach ($results as $res) {
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
                                        <div class="payment-list-box <?php echo $class; ?>  col-sm-6">
                                            <input type="radio" required name="address-option-select" {{$selectd}} value="{{$res->sh_id}}" id="address-otion<?php echo $counter; ?>">
                                            <label for="address-otion<?php echo $counter; ?>">
                                                <p>{{$res->full_name}}<br>{{$res->street}}<br>{{$res->city}}<br>{{$state_name}}<br>{{$country_name}} </p>
                                                <?php if ($counter == 0) { ?>  <p class="detault-address">Default</p> <?php } ?>
                                            </label>
                                        </div>
                                        <?php
                                        $counter++;
                                    }
                                    ?>

                                </div>
                                <div class="continue-to-next-step text-center">
                                    <input type="submit" name="submit" value="continue" class="btn btn-primary btn-simple show-loader">
                                </div>
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
                                            ?>" type="text" name="full_name" id="full_name" class="form-control">
                                        </div>
                                    </div>


                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <label>Country</label>
                                            <select  class="form-control" id="s_country" name="country" onchange="get_state_by_code(this.value)">
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

                                            <select class="form-control" id="states_countries" name="state">


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
                                            <input maxlength="90" type="text" id="s_street" name="street" class="form-control" value="<?php
                                            if (isset($session_data['street'])) {
                                                echo $session_data['street'];
                                            }
                                            ?>">
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="form-group" id="staticParent">
                                            <label>Postal Code</label>
                                            <input id="s_post_code" onkeypress="return isNumber(event)"  type="text" name="post_code" class="form-control" value="<?php
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
        </div>
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
                            if ($userDetailWallet >= $orderData['total_price']) {
                                
                                // echo '<input class="primary-btn btn btn-primary  " type="button" name="submit" value="Pay Via Stripe">';
                               
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
                        <a href="<?php echo URL::to('/checkout'); ?>" id="back-two" class="btn btn-primary btn-simple">Back</a>
                        <a href="javascript:;" id="continue-two" class="btn btn-primary btn-simple">Continue</a>
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
// print_rr($orderData);
// if (isset($sess['products']) and count($sess['products']) > 0) {

                        foreach ($orderData as $key => $val) {
                            $counter = 0;
                            if (is_array($val) && count($val) > 0) {
                                foreach ($val as $key2 => $val2) {
                                    if (is_numeric($key2)) {
                                        $product = getProductById($val2['product_id']);

                                        $image = $val2['feature_image'];

                                        $title = isset($product[0]->title) ? $product[0]->title : '';
                                        $image_url = getAttachmentUrlById((int) $image, 'small');
                                        $shipping_price = isset($val2['shipping_price']) ? $val2['shipping_price'] : '0';
                                        $product_tax = isset($val2['product_tax']) ? $val2['product_tax'] : '0';
                                        $product_price = isset($val2['price_cad']) ? $val2['price_cad'] : $val2['price'];
                                        $note = '';
                                        if ($shipping_price == 0) {
                                            $note = '<p> Note: shipping cost for this product is not included. </p>';
                                        }
                                        ?>
                                        <div class="list-repeat-mbt">
                                            <div class="image-cntainer">
                                                <a href="https://placeholder.com"> <img src="<?php echo $image_url; ?>" alt="{{$val2['title']}}" title="{{$val2['title']}}" class="img-fluid"></a>
                                            </div>
                                            <div class="description-order-text">
                                                <div class="body-text-left">
                                                    <h3>{{$title}}</h3>
                                                    <?php
                                                    if (isset($val2['option1']) && isset($val2['option1']) && $val2['option1'] != '') {
                                                        echo '<p>' . $val2['option1'] . ' ' . $val2['option2'] . '</p>';
                                                    }
                                                    ?>
                                                    <p> {{$val2['product_quantity']}} item</p>
                                                    <p><span class="zit-price">USDC {{number_format(round($product_price/$val2['product_quantity'],4),2)}}</span</p>
                                                    
                                                    <?php if ($note == '') { ?>
                                                        <p>shipping: <span class="zit-price">USDC {{number_format($shipping_price,2)}} </span></p>
                                                      
                                                        <?php
                                                    } else {

                                                        echo $note;
                                                    }
                                                    ?>
                                                    <p>Tax: <span class="zit-price">USDC {{number_format($product_tax,2)}}</span></p>
                                                </div>
                                                <div class="pricea-text-right">
                                                    <span> <span class="text-uppercase zit-price">USDC <?php echo number_format($val2['price'], 2); ?></span></span>
                                                    
                                                </div>
                                            </div>
                                        </div><!-- list-repeat-mbt -->
                                        <?php
                                    }
                                }
                            }
                            $counter++;
                        }

//}
                        ?>

                        <div class="total-cost">
                            <p class="text-right">Total: <b><span class="zit-price">USDC {{number_format($total_amount,2)}}</span></b> </p>
                            <p class="text-right">Total USDC: <b><span class="zit-price">USDC {{ $ercTotal }}</span></b> </p>
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
                            <form action="/meta-mask" method="POST">
                              
                              
                                {{ csrf_field() }}
                                {{-- <input type="hidden" id="credit_purchase" name="credit_user_purchase" value="yes">
                                 --}}
                                 <input type="hidden" id="shipping_data" name="shipping_data">
                                
                                <?php
                            
                             
                               
                                //    echo '<input class="primary-btn btn btn-primary finalsubmit" type="submit" name="submit2" value="Pay Via Stripe">';
                                   echo '<div id="polygon"> </div>';
                            
                                echo '<br /></br /><a href="/cart">Update Your Cart</a>';
                                ?>
                            </form> 
                            <script>
                                    $(document).ready(function () {
                                    // setTimeout(function () {
                                    //     $(".stripe-button-el").click();
                                    // }, 1000);

                                         });
                                jQuery(document).on('click', '.finalsubmit', function () {
                                    jQuery(this).hide();
                                });
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
                            <h3 class="text-center">Thankyou! Your order is received</h3>
                            <!-- <h3 class="text-center">{{$message}}</h3> -->
                        </div>
                    </div>
                    <div class="submit-order text-center" style="    display: block;
                         clear: both;
                         float: none;
                         margin: 40px auto;">

                        <a href="http://shopzit.mindblaze.net"  class="btn btn-primary btn-simple">Continue shopping</a>


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
            $('#address-otion1').attr('checked', 'checked');
        });
    </script>
    <?php
}
?>

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

        if (jQuery('#shispping_form').hasClass('usrbsc')) {
            full_name = jQuery('#full_name').val();
            s_country = jQuery('#s_country').val();
            states_countries = jQuery('#states_countries').val();
            s_street = jQuery('#s_street').val();
            s_post_code = jQuery('#s_post_code').val();
            s_flat_other = jQuery('#s_flat_other').val();
            city = jQuery('#s_city').val();
            data2 = '&full_name=' + full_name + '&s_country=' + s_country + '&city=' + city + '&states_countries=' + states_countries + '&s_street=' + s_street + '&s_post_code=' + s_post_code + '&s_flat_other=' + s_flat_other;
            jQuery('#shipping_data').val(data2);
        } else {
            val = jQuery('input[name=address-option-select]:checked').val();
            data2 = '&shipping_id=' + val;
            jQuery('#shipping_data').val(data2);
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
            jQuery('.usrbsc input, .usrbsc select').each(function () {
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
            jQuery('.usrbsc input').each(function () {
                jQuery(this).removeClass('hass_error');
            });
            has_error = false;
            jQuery('.usrbsc input, .usrbsc select').each(function () {
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
                s_post_code = jQuery('#s_post_code').val();
                s_flat_other = jQuery('#s_flat_other').val();
                data2 = '&full_name=' + full_name + '&s_country=' + s_country + '&states_countries=' + states_countries + '&s_street=' + s_street + '&s_post_code=' + s_post_code + '&s_flat_other=' + s_flat_other;
            } else {
                val = jQuery('input[name=address-option-select]:checked').val();
                data2 = '&shipping_id=' + val;
            }


        });

        $('#continue-two').click(function () {
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