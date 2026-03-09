@extends('layouts.frontend-main')
@section('content')
<link rel="stylesheet" href="{{ asset('assets/css/checkout.css', env('HTTPS_STATUS')) }}" type="text/css">
<script>
    $(window).load(function () {
        // $('#loading').hide();
    });
</script>
<style>
    .flat_price {
        font-size: 12px;
    }

    .mall-shark-section .no_shipping {
        font-size: 12px;

    }

    .hide-setion {
        display: none;
    }

    .show-section {
        display: block;
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
        transform: translate(-50%, -50%);
    }

    .logged-in-user {
        border: 1px solid #DDDDDD;
        margin-bottom: 33px;
        border-radius: 2px;
        border-width: 2px 1px 3px 2px;
        position: relative;
        padding: 10px;
    }

    #error-message-container {
        display: none;
        /* Initially hide the container */
        margin-top: 10px;
        /* Optional: Add some margin for spacing */
    }

    .payment-methods {
        /* Use flexbox to layout the title and buttons */
        align-items: center;
        /* Center align the items vertically */
        margin-bottom: 10px;
        /* Add some space below the payment methods */
    }

    .txt-p {
        margin-right: 15px;
        /* Add space between the text and buttons */
    }

    .pt-p {
        /* Align buttons to the start of the container */
        gap: 15px;
        /* Add space between buttons */
    }

    .payment-button {
        background: none;
        /* Remove button background */
        border: none;
        /* Remove button border */
        cursor: pointer;
        /* Change cursor to pointer */
        padding: 0;
        /* Remove padding */
    }

    .payment-button img {
        width: 50px;
        /* Set a fixed width for icons */
        height: auto;
        /* Maintain aspect ratio */
        transition: transform 0.2s;
        /* Smooth transition on hover */
    }

    .payment-button img:hover {
        transform: scale(1.1);
        /* Slightly enlarge icon on hover */
    }


    .displayNameUser,
    .user-full-name {
        font-size: 24px;
    }

    .displayNameUser {
        font-size: 24px;
        color: blue;
    }

    .user-logout-link {
        right: 5px;
        position: absolute;
    }

    .overlay-second {
        /* opacity: 0.2;
                                                                                    pointer-events: none; */
    }

    .mall-shark-section .readypayment-contianer {
        max-width: 100%;
    }

    .mall-shark-section .heading {
        background-color: #fff;
    }

    .mall-shark-section .heading h1 {
        color: #183B56;
        font-size: 62px;
        text-align: center;
        font-weight: 600;
    }

    .mall-shark-section .confirmation_images {
        background-color: #f9fbfe;
        margin-top: 60px;
        margin-bottom: 120px;
    }

    .mall-shark-section .images-section {
        display: flex;
        justify-content: space-between;
    }

    .mall-shark-section .confirmaion-shark-img {
        position: relative;
    }

    .mall-shark-section .confirmaion-shark-img img {
        max-width: 600px;
        right: 150px;
        position: relative;
        top: 53px;
    }

    .mall-shark-section .congragulationImg {
        position: absolute;
        top: 35px;
        right: -35%;
    }

    .mall-shark-section .heading {
        display: block
    }

    .mall-shark-section .details {
        text-align: center;
    }

    .mall-shark-section .confirmaion-CoinBox-img img {
        max-width: 400px;
        position: relative;
        top: 135px;
        left: -30px;
    }

    .mall-shark-section .custom-checkout-hide-section {
        display: block;
    }

    .checkout-custom-btn .btn-primary {
        background-color: #0510fa;
        border-color: #0510fa;
        border-radius: 26px;
    }

    .checkout-custom-btn .btn-primary a {
        color: #fff
    }

    .checkout-custom-btn .btn-primary:hover {
        background-color: #0510fa;
    }

    .error {
        color: red !important;
    }
    .stripe-input {
        border: 1px solid #ccc;
        padding: 10px;
        border-radius: 5px;
        margin-bottom: 10px;
         }
        #card-details-container {
            display: flex;
            gap: 10px; /* Add spacing between the fields */
            align-items: center; /* Align fields vertically */
        }

        #card-details-container > div {
            flex: 1; /* Ensure all fields take equal space */
        }
        .expiry-input {
            padding: 10px;
            font-size: 16px;
            border: 1px solid #ccc;
            border-radius: 4px;
            margin-bottom: 10px;
            width: 100%;
        }
        .cvc-input {
            padding: 10px;
            font-size: 16px;
            border: 1px solid #ccc;
            border-radius: 4px;
            margin-bottom: 10px;
            width: 100%;
        }
        #scrollable-container::-webkit-scrollbar {
            width: 6px; /* Set the scrollbar width */
        }
        #scrollable-container::-webkit-scrollbar-thumb {
            background-color: #888; /* Color of the scrollbar thumb */
            border-radius: 10px; /* Round edges of the scrollbar thumb */
        }
        #scrollable-container::-webkit-scrollbar-thumb:hover {
            background-color: #555; /* Darker color on hover */
        }
        @media (min-width: 1025px) {
            .payment-methods {
                display: none;
            }
        }
</style>
<?php
//  echo 'Data: <pre>' .print_r($orderData,true). '</pre>'; die;
if (isset($orderData['total_price']) && is_numeric($orderData['total_price']) && isset($orderData['subtotal_price']) && isset($orderData['shipping_price'])) {
    $total_amount = $orderData['total_price'];
    $subtotal_amount = $orderData['subtotal'];
    $shipping_amount = $orderData['shipping_price'];
    $totalDiscount = $orderData['total_discount'];
    // dd($shipping_amount);
} else {
    $total_amount = 0; // or any default value you want to use
    $shipping_amount = 0;
    $subtotal_amount = 0;
    $totalDiscount = 0;
}
$orderDatatotal = $total_amount * 100;
$session_data = isset($alldata) ? $alldata : '';
if (isset($orderData['total_price']) && is_numeric($orderData['total_price'])) {
    $ercTotal = $orderData['total_price'];
}

if (isset($orderDataCrypto['total_price_crypto']) && is_numeric($orderDataCrypto['total_price_crypto']) && isset($orderDataCrypto['subtotal_price_crypto']) && isset($orderDataCrypto['shipping_price'])) {
    $ercTotalCrypto = $orderDataCrypto['total_price_crypto'];
    $ercSubTotalCrypto = $orderDataCrypto['subtotal_price_crypto'];
    $shippingAmountCrypto = $orderDataCrypto['shipping_price'];
} else {
    $ercTotalCrypto = 0;
    $ercSubTotalCrypto = 0;
    $shippingAmountCrypto = 0;
}
    
    ?>
<script>
    window.user_restriction = {
        passcodeCheck: "{{ env('User_restrict') }}"
    };
    window.user_passcode = {
        passcode: "{{ env('Passcode') }}"
    };
</script>
<div class="checkoutWrapperContainer">
    <div class="container custom-checkout-hide-section ">
        <div class="row">
            <div class="col-md-6 nopad">
                <?php
if (!Session::has('userData')) {
    $first_name = '';
    $last_name = '';

    $userCity = '';
    $userState = '';
    $userCountry = '';
    $userZipcode = '';
    $userAddress = '';
                        ?>
                <!-- <div class="alert  alert-primary" role="alert">
                                                                                                            Returning Customer? <a href="{{ URL::to('/login') }}">Click here to login</a>
                                                                                                        </div> -->

                <?php } else {

    $userid = Session::get('userData')['user_id'];
    $userData = getUserById($userid);

    $name = explode(' ', $userData->name);
    if (is_array($name) && count($name) > 1) {
        $first_name = $name[0];
        $last_name = $name[1];
    } else {
        $first_name = $userData->name;
        $last_name = $userData->name;
    }
    $userCity = $userData->user_city_id;
    $userState = $userData->user_state_id;
    $userCountry = $userData->user_country_id;
    $userZipcode = $userData->user_zip_code;
    $userAddress = $userData->physical_address;

    // echo 'Data: <pre>' .print_r($resu$userDatalt,true). '</pre>'; die();
    $fullName = $userData->name;
                                ?>
                <div class="logged-in-user text-left" style="display: none;">
                    <b class="user-full-name">Welcome <span class="displayNameUser">{{ $fullName }}
                        </span></b></br />
                    <span class="user-email-add">Your email address is<span class="emailFieldText">:
                            {{ $userData->email }}</span></span>

                </div> <a class="user-logout-link" href="{{ URL::to('/logout') }}"
                    style="display: none;>Logout <i class=" fa fa-sign-out" aria-hidden="true"></i></a>

                <?php } ?>
            </div>
            <div class="col-md-6"></div>

        </div>
        <div class="container custom-checkout-hide-section mt-4">
            <div class="row">
                <div class="col-md-6 nopad">
                    <h1 style="margin-top: 20px;">Checkout </h1>
                </div>
                <div class="col-md-6 sharkbx"><img src="{{ asset('/assets/images/Shark2.png') }}"></div>
            </div>
            <div class="col-md-6 sharkbx"><img src="{{ asset('/assets/images/Shark2.png') }}"></div>
        </div>
    </div>
    <div class="container chkout checkout-page" id="checkout-page">
        <div class="row">
            @if (Session::has('updatesuccessmessage'))
                <div class="alert alert-success alert-success-mbt" id="success_message" role="alert">
                    {{ Session::get('updatesuccessmessage') }}
                    <script>
                        setTimeout(function () {
                            jQuery("#success_message").fadeOut('slow')
                        }, 3000);
                    </script>
                </div>
            @endif
            @if (Session::has('ErrorMessage'))
                <div class="alert alert-danger" role="alert" id="error_message">
                    {{ Session::get('ErrorMessage') }}
                </div>
                <script>
                    setTimeout(function () {
                        jQuery("#error_message").fadeOut('slow')
                    }, 3000);
                </script>
            @endif
            <div class="shopping" style="margin-bottom: 20px;">
                <a style="background: #073ba5 0% 0% no-repeat padding-box;border-radius: 6px;opacity: 1; font-size:14px; padding:10px 30px; color:#fff"
                    href="/cart"><i class="fa fa-light fa-arrow-left"></i> Edit Cart</a>
                {{-- <a
                    style="background: #EDB83F 0% 0% no-repeat padding-box;border-radius: 6px;opacity: 1; font-size:14px; padding:10px 30px; color:#fff"
                    href="/q">Continue Shopping</a> --}}
            </div>
            <div class="col-md-12">
                <h3>Delivery Details</h3>
            </div>
            @if ($errors->any())
                $state_name
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
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
                    Returning Customer? <a href="{{ URL::to('/login') }}">Click here to login</a>
                </div>

                <?php } else {

    $userid = Session::get('userData')['user_id'];
    $userData = getUserById($userid);

    $fullName = $userData->name;
                 ?>
                <div class="logged-in-user" style="display: none;">
                    <span class="user-full-name">Welcomee <span class="displayNameUser">{{ $fullName }}
                        </span></span></br />
                    <span class="user-email-add">Your email address is<span class="emailFieldText">:
                            {{ $userData->email }}</span></span>
                    <a class="user-logout-link" href="{{ URL::to('/logout') }}">Logout <i class="fa fa-sign-out"
                            aria-hidden="true"></i></a>
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
                            <!-- Solution: replaced disabled with readonly but now it will submit with form , to avoid submission remove name too  -->
                            <input type="email" class="form-control" id="useremail" name="useremail"
                                value="<?php    echo $userData->email ? $userData->email : ''; ?>" readonly>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="phone_number">Phone</label>
                            <input type="text" class="form-control" id="phone_number" name="phone_number"
                                value="<?php    echo $userData->contact_no ? $userData->contact_no : ''; ?>" disabled>
                        </div>
                    </div>
                    <?php } else { ?>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="useremail">Email *</label>
                            <input type="text" class="form-control no-session" id="useremail" name="useremail" value="">
                        </div>
                        <div class="form-group col-md-6">
                            <label for="phone_number">Phone</label>
                            <input type="text" class="form-control" id="phone_number" name="phone_number">
                        </div>
                    </div>
                    <?php  } ?>
                    <h2 class="frmhd">Shipping Address</h2>

                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="inputFirstName">First Name *</label>
                            <input type="text" value="{{ $first_name ? $first_name : '' }}" class="form-control"
                                id="inputFirstNameShipping" name="first_name">
                        </div>
                        <div class="form-group col-md-6">
                            <label for="inputLastName">Last Name</label>
                            <input type="text" class="form-control" value="{{ $last_name ? $last_name : '' }}"
                                id="inputLastNameShipping" name="last_name">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="inputCountry">Country *</label>
                            <select class="form-control required_class getshippingrate" id="s_country" name="country"
                                onchange="get_state_by_code(this.value)" autocomplete="off">
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
$countries = [];
// if (count($countries) > 0) {
//     foreach ($countries as $countryy) {
//         if ($countryy['country_code'] == $country) {
//             $selected = "selected='selected'";
//         } else {
//             $selected = '';
//         }
//         echo '<option  value="' . $countryy['country_code'] . '">' . $countryy['country_name'] . '</option>';
//     }
// }

echo '<option  value="1" ' . $usa_S . ' selected >USA</option>';
// echo '<option  value="2" ' . $Canada_S . '>CANADA</option>';
                                    ?>

                            </select>
                            <!-- <input type="text" class="form-control" id="inputCountry"> -->
                        </div>
                        <div id="loading" class="show-setion">
                            <img id="loading-image" src="{{ asset('assets/images/Loader-Animation.gif') }}"
                                alt="Loading..." />
                        </div>
                        <div class="form-group col-md-6">
                            <label for="states_countries">States *</label>

                            <select class="form-control required_class getshippingrate" id="states_countries"
                                name="states_countries" autocomplete="off">


                                <?php
echo '<script>jQuery(document).ready(function(){setTimeout(function() {
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                              $(".getshippingrate").trigger("change");
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                }, 20);});</script>';
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
$states = [];
$selected = '';
if (count($states) > 0) {
    // echo 'Data: <pre>' .print_r($result,true). '</pre>';
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
                            <input type="text" class="form-control getshippingrate" id="street_address"
                                value="{{ $userAddress }}" name="street_address">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="city_name">Town/City *</label>
                            <input type="text" class="form-control getshippingrate" id="city_name" name="city_name"
                                value="{{ $userCity }}">
                        </div>
                        <div class="form-group col-md-6">
                            <label for="zipcode">Zip Code *</label>
                            <input type="text" maxlength="6" minlength="5" class="form-control getshippingrate"
                                id="zipcode" name="zipcode" value="{{ $userZipcode }}">
                        </div>

                    </div>
                    <?php
if (env('User_restrict') == 1) { ?>
                    <div class="form-group">
                        <label for="pwd">Passcode:</label>
                        <input type="text" class="form-control mallshark_passscode" maxlength="4" name="passcode"
                            id="passcode" placeholder="Please Enter Passcode" value="{{ old('passcode') }}">
                    </div>
                    <?php  } ?>


                </form>
                <h2 class="frmhd" style="display: none;">Billing Address</h2>
                <div class="form-check" style="margin-bottom:30px" style="display: none;">
                    <input class="form-check-input" type="checkbox" id="showFormCheckbox" style="display: none;">
                    <label class="form-check-label" for="showFormCheckbox" style="display: none;">
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
                                <input type="text" class="form-control" id="inputPostCode">
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
            <div class="col-sm-6 prcngbxiny overlay-second" id="prcngbxiny">
                <div class="heading">
                    <h2>Order Details</h2>
                    <div class="subheading ">Payment Methods Accepted:

                        <img style="position:relative; top:-2px; filter:invert(1); opacity:.3"
                            src="{{ asset('assets/images/icnp-2w.png') }}"><span> Master
                            / Visa</span>
                    </div>
                </div>

                <div class="table mt-0 noman">
                    <?php
$mainArray = array();
$totalStoreShipPrice = 0;
if (empty($orderData)) {
    echo 'No Result Found';
} else {
    foreach ($orderData as $key => $val) {
        // if($val['current_stock'] <= 0){
        //     echo 'called';
        // }else{
        //     echo 'else';
        // }
        $store = explode('-', $key);
        $storeId = !empty($store[1]) ? $store[1] : '';
        $totalStoreShipPrice = isset($val['store_shipping_price']) ? $val['store_shipping_price'] : 0;
        $store_stripe_gateway = isset($val['store_stripe_gateway']) ? $val['store_stripe_gateway'] : "";
        $store_total_price = isset($val['store_total_price']) ? $val['store_total_price'] : "";
        $counter = 0;
        $storeTotalPrice = 0;
        $totalShipPrice = 0;
        $prevValue = null;

        $prevProductIds = [];
        $prevVariationId = null;
        $product_index = 0;
        // dd($val);
        if (is_array($val) && count($val) > 0) {
            foreach ($val as $key2 => $val2) {

                if (is_numeric($key2)) {
                    $product = getProductById($val2['product_id']);
                    $category_id = getCategoriesByProductID($val2['product_id']);
                    if (isset($category_id[0])) {

                        $productCategory = getCategoryByCateId($category_id[0]);
                    } else {
                        $productCategory = null;
                    }

                    $productStore = getStoreInfoByIdCartApi($val2['store_id']);

                    $mainArray[$storeId][] = $val2;
                    // dd($val2);
                    if ($val2['product_type'] == 'simple') {
                        $image = $val2['feature_image'];
                    } elseif ($val2['product_type'] == 'virtual') {
                        $image = $val2['feature_image'];
                    } elseif (!empty($val2['product_type'] == 'variant')) {
                        if (!empty($val2['product_variation_image'])) {
                            $image = $val2['product_variation_image'];
                        } else {
                            $image = $val2['feature_image'];
                        }
                    }

                    // dd($image);
                    $slug = $val2['slug'];

                    $title = isset($product[0]->title) ? $product[0]->title : '';
                    $image_url = getAttachmentUrlById((int) $image, 'small');
                    $shipping_price = isset($val2['shipping_price']) ? $val2['shipping_price'] : 0;
                    $shipping_method = isset($val2['method']) ? $val2['method'] : '';
                    $is_domestic_free = isset($val2['is_domestic_free']) ? $val2['is_domestic_free'] : 0;
                    $flat_shipping_rate = isset($val2['flat_shipping_rate']) ? $val2['flat_shipping_rate'] : 0;
                    $origin_zip = isset($val2['origin_zip']) ? $val2['origin_zip'] : '0';
                    $destination_zip = '90001';
                    $productSize_length = (isset($val2['productSize_length']) && !empty($val2['productSize_length'])) ? $val2['productSize_length'] : 0;
                    $productSize_width = (isset($val2['productSize_width']) && !empty($val2['productSize_width'])) ? $val2['productSize_width'] : 0;
                    $productSize_height = (isset($val2['productSize_height']) && !empty($val2['productSize_height'])) ? $val2['productSize_height'] : 0;
                    $product_id = isset($val2['product_id']) ? $val2['product_id'] : '0';
                    $product_weight = isset($val2['weight']) ? $val2['weight'] : '0';
                    $usps_services = isset($val2['usps_services']) ? $val2['usps_services'] : [];
                    $weightType = isset($val2['weight_type']) ? $val2['weight_type'] : '0';
                    $dimentionType = isset($val2['dimention_type']) ? $val2['dimention_type'] : '0';
                    $productType = (isset($val2['product_type']) && !empty($val2['product_type'])) ? $val2['product_type'] : '';
                    $variationId = (isset($val2['variation_id']) && !empty($val2['variation_id'])) ? $val2['variation_id'] : '';
                    $appendVariationId = !empty($variationId) ? '_' . $variationId : '';
                    // dump($productType);
                    // dump($variationId);
                    if ($weightType == 'pound') {
                        if ($product_weight) {
                            $newWeight = number_format($product_weight * 16, 3);
                            $newWeight = (float) $newWeight;
                            $product_weight = round($newWeight);
                            if ($product_weight == 0) {
                                $product_weight = 1;
                            }
                        } else {
                            $product_weight = 1;

                        }
                    }

                    if ($dimentionType == 'cm') {
                        $new_length = number_format($productSize_length * 0.393701, 3);

                        $new_length = (float) $new_length;
                        $productSize_length = round($new_length);

                        if ($productSize_length == 0 && $new_length > 0) {
                            $productSize_length = 1;
                        }

                        $new_width = number_format($productSize_width * 0.393701, 3);
                        $new_width = (float) $new_width;
                        $productSize_width = round($new_width);
                        if ($productSize_width == 0 && $new_width > 0) {
                            $productSize_width = 1;
                        }
                        $new_height = number_format($productSize_height * 0.393701, 3);
                        $new_height = (float) $new_height;
                        $productSize_height = round($new_height);
                        if ($productSize_height == 0 && $new_height > 0) {
                            $productSize_height = 1;
                        }

                    } else if ($dimentionType == 'mm') {

                        $new_length = number_format($productSize_length * 0.03937, 3);
                        $new_length = (float) $new_length;
                        $productSize_length = round($new_length);
                        if ($productSize_length == 0 && $new_length > 0) {
                            $productSize_length = 1;
                        }
                        $new_width = number_format($productSize_width * 0.03937, 3);
                        $new_width = (float) $new_width;
                        $productSize_width = round($new_width);
                        if ($productSize_width == 0 && $new_width > 0) {
                            $productSize_width = 1;
                        }

                        $new_height = number_format($productSize_height * 0.03937, 3);
                        $new_height = (float) $new_height;
                        $productSize_height = round($new_height);
                        if ($productSize_height == 0 && $new_height > 0) {
                            $productSize_height = 1;
                        }

                    }

                    $shipping_option_id = isset($val2['shipping_option_id']) ? $val2['shipping_option_id'] : '0';
                    $product_tax = isset($val2['product_tax']) ? $val2['product_tax'] : '0';

                    $product_variant_price = isset($val2['variation_sale_price']) ? $val2['variation_sale_price'] : $val2['price'];
                    $product_type = isset($val2['product_type']) ? $val2['product_type'] : "";

                    if ($product_type == 'variant') {
                        $actualPrice = $val2['variation_sale_price'];
                        $product_price = $val2['on_sale'] == 1 ? $val2['product_variation_sale_price'] : $actualPrice;
                    } else {
                        $product_price = $val2['on_sale'] == 1 ? $val2['sale_price'] : $val2['price'];
                        $actualPrice = $val2['price'];
                        //                                    $product_price = isset($val2['price_cad']) ? $val2['price_cad'] : $val2['price'];
                    }


                    $shipping_service = isset($val2['shipping_service']) ? $val2['shipping_service'] : '';
                    $proPrice = ($product_price * $val2['product_quantity']) + $shipping_price;
                    $proPriceReal = ($product_price * $val2['product_quantity']);
                    $proPriceActual = ($actualPrice * $val2['product_quantity']);
                    $product_quantity = isset($val2['product_quantity']) ? $val2['product_quantity'] : 1;
                    $note = '';
                    $attribute1 = isset($val2['attribute1']) ? $val2['attribute1'] : "";
                    $attribute2 = isset($val2['attribute2']) ? $val2['attribute2'] : "";
                    $attribute3 = isset($val2['attribute3']) ? $val2['attribute3'] : "";


                    if ($shipping_price == 0 && $shipping_method == 'free') {

                        $note = '<p class="no_shipping"> Note: shipping cost for this product is included. </p>';
                    }

                                ?>
                    <div class="row list_{{ $val2['product_id'] . $appendVariationId }} checkout_product_list"
                        data-index="{{ $product_index }}" data-product_id="{{ $val2['product_id'] }}"
                        id="list_{{ $val2['product_id'] . $appendVariationId }}" style="align-items: start;">
                        {{-- @dd($image_url) --}}
                        <span class="product_category"
                            data-category="{{ isset($productCategory) ? $productCategory['categoryTitle'] : '' }}"></span>
                        <span class="quantity-input" data-quantity="{{ $val2['product_quantity'] }}"></span>
                        <span class="product_store" data-store="{{ $productStore['store_name'] }}"></span>
                        <div class="cell prdcl"><span class="prdcnt">{{ $val2['product_quantity'] }}</span><img
                                class="cart-product-thumbnail"
                                src="<?php                    echo !empty($image_url) ? $image_url : URL::asset('/assets/images/placeholder.jpg'); ?>"
                                alt="{{ $val2['title'] }}" width="64"></div>
                        <div class="product_title cell text-left " style="word-break: break-all;">{{ $title }}
                        </div>

                        <div class="remove-cart-item d-flex" style="position: relative;top:7px;left:-4px;">
                            <a href="javascript:;" style="color:#bdbdbd; margin-top:3px; display:inline-block"
                                data-product-id="{{ $val2['product_id'] }}" {{ !empty($variationId) ? 'data-variation-id=' . $variationId : '' }} class="remove-product">
                                <i class="fa fa-trash" aria-hidden="true"></i>
                            </a>
                        </div>
                        <div class="cell" style="max-width: 150px; position:relative">
                            <?php                    if ($val2['product_type'] != 'virtual') {
                        if ($is_domestic_free == '0') {
                            if ($flat_shipping_rate == '0') { ?>
                            <?php                                if (($productSize_width == null || $productSize_width == 0) && ($productSize_length == null || $productSize_length == 0) && ($productSize_height == null || $productSize_height == 0)) { ?>
                            @if ($productType == 'simple')
                                <select
                                    class="custom-select custom-select-sm shipping_usps_service_checkout  shipping_service_{{ $val2['product_id'] }}"
                                    id="shipping_service">
                                    <?php                                        foreach ($usps_services as $key => $service) { ?>
                                    <option class="{{ $service }}"
                                        value="{{ $service }}_{{ $product_weight }}_{{ $origin_zip }}_{{ $product_quantity }}_{{ $key2 }}_{{ $storeId }} "
                                        disabled>{{ $service }}</option>
                                    <?php                                        }?>


                                </select>
                            @elseif($productType == 'variant')
                                <select
                                    class="custom-select custom-select-sm shipping_usps_service_checkout  shipping_service_{{ $val2['product_id'] }}"
                                    id="shipping_service">
                                    <?php                                        foreach ($usps_services as $key => $service) { ?>
                                    <option class="{{ $service }}"
                                        value="{{ $service }}_{{ $product_weight }}_{{ $origin_zip }}_{{ $product_quantity }}_{{ $key2 }}_{{ $storeId }} "
                                        disabled>{{ $service }}</option>
                                    <?php                                        }?>


                                </select>
                            @endif
                            <?php                                } else { ?>
                            @if ($productType == 'simple')
                                <select
                                    class="custom-select custom-select-sm shipping_service_checkout  shipping_service_{{ $val2['product_id'] }}"
                                    id="shipping_service">
                                    <?php                                        foreach ($shipping_service as $service) { ?>
                                    <option class="{{ $service }}"
                                        value="{{ $service }}_{{ $storeId }}_{{ $val2['product_id'] }}_{{ $product_weight }}_{{ $origin_zip }}_{{ $shipping_option_id }}_{{ $productSize_length }}_{{ $productSize_width }}_{{ $productSize_height }}_{{ $product_quantity }}_{{ $proPriceReal }}_{{ $title }}_{{ $key2 }}"
                                        disabled>{{ $service }}</option>
                                    <?php                                        }?>


                                </select>
                            @elseif($productType == 'variant')
                                <select
                                    class="custom-select custom-select-sm shipping_service_checkout  shipping_service_{{ $val2['product_id'] }}"
                                    id="shipping_service">
                                    <?php                                        foreach ($shipping_service as $service) { ?>
                                    <option class="{{ $service }}"
                                        value="{{ $service }}_{{ $storeId }}_{{ $val2['product_id'] }}_{{ $product_weight }}_{{ $origin_zip }}_{{ $shipping_option_id }}_{{ $productSize_length }}_{{ $productSize_width }}_{{ $productSize_height }}_{{ $product_quantity }}_{{ $proPriceReal }}_{{ $title }}_{{ $key2 }}"
                                        disabled>{{ $service }}</option>
                                    <?php                                        }?>


                                </select>
                            @endif

                            <?php                                } ?>

                            <?php                            }
                        } ?>
                            <?php                    } ?>
                            <?php

                    if ($note == '') {
                        if ($shipping_method == 'flat') { ?>
                            @if ($productType == 'simple')
                                <input type="hidden" name="shipping_data" class="get_data_calulate_ship"
                                    data-shipping-price="{{ number_format($flat_shipping_rate, 2) }}"
                                    value="Priority_{{ $storeId }}_{{ $val2['product_id'] }}_{{ $product_weight }}_{{ $origin_zip }}_{{ $shipping_option_id }}_{{ $product_quantity }}_{{ $proPriceReal }}_{{ $title }}_{{ $key2 }}_{{ $productSize_length }}_{{ $productSize_width }}_{{ $productSize_height }}_{{ $productType }}_{{ $variationId }}_{{ $is_domestic_free }}_{{ $flat_shipping_rate }}_{{ $product_id }}">
                                <span class="flat_price shipping_price_calculated"
                                    data-ship-price="{{ number_format($shipping_price, 2) }}" data-shipment-id=""
                                    data-rate-id="" id="shipping_price_calculated">
                                    (flat) {{ number_format($flat_shipping_rate, 2) }} </span>
                            @elseif($productType == 'variant')
                                <input type="hidden" name="shipping_data" class="get_data_calulate_ship"
                                    data-shipping-price="{{ number_format($flat_shipping_rate, 2) }}"
                                    value="Priority_{{ $storeId }}_{{ $val2['product_id'] }}_{{ $product_weight }}_{{ $origin_zip }}_{{ $shipping_option_id }}_{{ $product_quantity }}_{{ $proPriceReal }}_{{ $title }}_{{ $key2 }}_{{ $productSize_length }}_{{ $productSize_width }}_{{ $productSize_height }}_{{ $productType }}_{{ $variationId }}_{{ $is_domestic_free }}_{{ $flat_shipping_rate }}_{{ $product_id }}">
                                <span class="flat_price shipping_price_calculated"
                                    data-ship-price="{{ number_format($shipping_price, 2) }}" data-shipment-id=""
                                    data-rate-id="" id="shipping_price_calculated">
                                    (flat) {{ number_format($flat_shipping_rate, 2) }} </span>
                            @endif
                            <?php                        } else {
                                                            ?>
                            @if ($productType == 'simple')
                                <p class="small shpnginf">
                                    Shipping :
                                    <input type="hidden" name="shipping_data" class="get_data_calulate_ship"
                                        value="Priority_{{ $storeId }}_{{ $val2['product_id'] }}_{{ $product_weight }}_{{ $origin_zip }}_{{ $shipping_option_id }}_{{ $product_quantity }}_{{ $proPriceReal }}_{{ $title }}_{{ $key2 }}_{{ $productSize_length }}_{{ $productSize_width }}_{{ $productSize_height }}_{{ $productType }}_{{ $variationId }}_{{ $is_domestic_free }}_{{ $flat_shipping_rate }}_{{ $product_id }}">
                                    <span class="shipping_price_calculated"
                                        data-ship-price="{{ number_format($shipping_price, 2) }}" data-shipment-id=""
                                        data-rate-id="" id="shipping_price_calculated">
                                    </span>
                                </p>
                            @elseif($productType == 'variant')
                                <p class="small shpnginf">
                                    Shipping :
                                    <input type="hidden" name="shipping_data" class="get_data_calulate_ship"
                                        value="Priority_{{ $storeId }}_{{ $val2['product_id'] }}_{{ $product_weight }}_{{ $origin_zip }}_{{ $shipping_option_id }}_{{ $product_quantity }}_{{ $proPriceReal }}_{{ $title }}_{{ $key2 }}_{{ $productSize_length }}_{{ $productSize_width }}_{{ $productSize_height }}_{{ $productType }}_{{ $variationId }}_{{ $is_domestic_free }}_{{ $flat_shipping_rate }}_{{ $product_id }}">
                                    <span class="shipping_price_calculated"
                                        data-ship-price="{{ number_format($shipping_price, 2) }}" data-shipment-id=""
                                        data-rate-id="" id="shipping_price_calculated">
                                    </span>
                                </p>
                            @endif
                            <?php                        }
                    } else { ?>

                            <input type="hidden" name="shipping_data" class="get_data_calulate_ship"
                                value="Priority_{{ $storeId }}_{{ $val2['product_id'] }}_{{ $product_weight }}_{{ $origin_zip }}_{{ $shipping_option_id }}_{{ $product_quantity }}_{{ $proPriceReal }}_{{ $title }}_{{ $key2 }}_{{ $productSize_length }}_{{ $productSize_width }}_{{ $productSize_height }}_{{ $productType }}_{{ $variationId }}_{{ $is_domestic_free }}_{{ $flat_shipping_rate }}_{{ $product_id }}">


                            <?php
                        echo $note;
                    }
                                                    ?>

                        </div>
                        <div class="cell text-right"
                            style="max-width: 85px!important; font-size:12px; line-height:16px">
                            @if ($val2['on_sale'] == 1)
                                <span class="prcbx real_product_price" style="color: red !important;"
                                    data-real-price="{{ $proPriceReal }}">${{ number_format(round($proPriceReal, 4), 2) }}</span>
                                <del class="real_product_price" style="font-size: 11px"
                                    data-real-price="{{ $proPriceActual }}">${{ number_format(round($proPriceActual, 4), 2) }}</del>
                            @else
                                <span class="prcbx real_product_price"
                                    data-real-price="{{ $proPriceReal }}">${{ number_format(round($proPriceReal, 4), 2) }}</span>
                            @endif
                        </div>
                    </div>
                    <?php
                    $storeTotalPrice = $storeTotalPrice + $product_price;

                    // $prevProductId = $val2['product_id'];

                }
                $product_index++;
            }
        }
        //   }
        $counter++;
        if (!empty($storeId)) {

        }
    }
       
            
                ?>
                    <script>
                        var orderData = <?php    echo json_encode($mainArray); ?>;
                    </script>

                    <input type="hidden" name="store_total" id="store_total" value="{{ $storeTotalPrice }}">
                </div>




                <div class="table mt-0 mb-5" style="background: #fff;">
                    <div class="row">
                        <div class="col text-left">Sub Total:</div>
                        <div class="col text-right"><span
                                class="prcbx subtotal_product_stripe">${{ number_format($subtotal_amount, 2) }}</span>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col text-left">Total Discount:</div>
                        <div class="col text-right"><span class="prcbx" id="finalTotalDiscount">${{ number_format($totalDiscount, 2) }}</span>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col text-left">Shipping:</div>
                        <div class="col text-right"><span class="prcbx product_shipping_total"
                                data-product_shipping_total="">$0.00</span></div>
                    </div>

                    <div class="row ttlbx">
                        <div class="col text-left">Total:</div>
                        <div class="col text-right"><span
                                class="prcbx grandtotal_cal" id="finalTotalPrice"><span>$</span>{{ number_format($total_amount, 2) }}</span>
                        </div>
                    </div>
                </div>

                <h2 class="frmhd" style="margin-bottom: 0!important; margin-top:20px!important">Pay with Credit/Debit
                    Card</h2>


                <div class="box pymntslct" id="box2">
                    <form action="/stripe-response" method="POST" id="payment-form">
                        @csrf
                        <h2>Enter your credit/debit card information</h2>

                        <input type="hidden" name="stripe_email" class="form-control" id="stripe_email" value="">
                        <div class="form-group">
                                    <label for="card-number-element">Card Number</label>
                                    <div id="card-number-element" class="stripe-input"></div>
                                </div>

                                <!-- Expiry, CVC, and Zip Code -->
                                <div class="form-group" id="card-details-container">
                                    <div>
                                        <label for="card-expiry-element">Expiry</label>
                                        <div id="card-expiry-element" class="expiry-input"></div>
                                    </div>
                                    <div>
                                        <label for="card-cvc-element">CVC</label>
                                        <div id="card-cvc-element" class="cvc-input"></div>
                                    </div>
                                </div>

                                <div id="card-errors" role="alert"></div>
                        <input type="hidden" name="total_amount_ship" id="total_amount_ship"
                            value="<?php    echo $orderDatatotal; ?>">
                        <input type="hidden" id="shipping_data_stripe" name="shipping_data_stripe">
                        <input type="hidden" id="orderData_stripe" name="orderdata">

                       
                        <div class="col-md-12">
                            <button type="submit" class="button active place-order-btn" id="place-order-btn">Place
                                Order </button>
                        </div>

                    </form>
                    <div class="payment-methods pt-4" >
                            <span class="txt-p" style="display: none;">Or Pay With:</span>
                            <span class="pt-p align-items-center payment-options">
                                <button type="button" class="payment-button" onclick="payWithPayPal()"
                                    style="display: none">
                                    <img class="paypal" src="{{ URL::asset('assets/images/paypal.svg') }}"
                                        alt="PayPal">
                                </button>

                                <div id="payment-request-button">
                                <!-- <button id="dynamic-button" style="background-color: #ffffff;border: 0.1mm solid #c7bfbf;">
                                    <img class="paypal" src="{{ URL::asset('assets/images/google-pay.svg') }}"
                                        alt="PayPal">
                                </button>  -->
                            </div>
                                <div id="error-message-container"></div>

                                <button type="button" class="payment-button" onclick="payWithApple()"
                                    style="display: none">
                                    <img class="applePay" src="{{ URL::asset('assets/images/apple-pay.svg') }}"
                                        alt="Apple Pay">
                                </button>
                            </span>

                        </div>


                </div>
                <?php } ?>

                <div class="mt-20">

                    <div style="display: none;">
                        <?php 
                // echo '$orderDataCrypto: <pre>' .print_r($orderDataCrypto,true). '</pre>'; die();
if (empty($orderDataCrypto)) {
                ?>
                        <div class="heading" style="display: none;">
                            <h2>Your Items</h2>
                            <div class="subheading "> Methods Accepted:
                                <img style="position:relative; top:-2px; filter:invert(1); opacity:.3"
                                    src="{{ asset('assets/images/icnp-1w.png') }}"><span>
                                </span>
                            </div>
                        </div>

                        <?php

    // echo 'Data: <pre>' .print_r($orderDataCrypto,true). '</pre>'; 

    echo 'No Result Found';
} else {
    $cryptoServices = false ?>
                        @if ($cryptoServices)

                                            <div class="heading">
                                                <h2>Order Details</h2>
                                                <div class="subheading "> Methods Accepted:
                                                    <img style="position:relative; top:-2px; filter:invert(1); opacity:.3"
                                                        src="{{ asset('assets/images/icnp-1w.png') }}"><span>
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="table mt-0">
                                                <?php
                            $mainArrayCrypto = array();
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
                                $prevProductIdCrypto = null;
                                $prevVariationIdCrypto = null;
                                if (is_array($val) && count($val) > 0) {
                                    foreach ($val as $key2 => $val2) {
                                        if (is_numeric($key2)) {
                                            $product = getProductById($val2['product_id']);
                                            $mainArrayCrypto[$storeId][] = $val2;
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
                                            $origin_zip = isset($val2['origin_zip']) ? $val2['origin_zip'] : '0';
                                            $destination_zip = '90001';

                                            $productSize_length = (isset($val2['productSize_length']) && !empty($val2['productSize_length'])) ? $val2['productSize_length'] : 0;
                                            $productSize_width = (isset($val2['productSize_width']) && !empty($val2['productSize_width'])) ? $val2['productSize_width'] : 0;
                                            $productSize_height = (isset($val2['productSize_height']) && !empty($val2['productSize_height'])) ? $val2['productSize_height'] : 0;

                                            $product_id = isset($val2['product_id']) ? $val2['product_id'] : '0';
                                            $product_weight = isset($val2['weight']) ? $val2['weight'] : '0';
                                            $usps_services = isset($val2['usps_services']) ? $val2['usps_services'] : [];
                                            $weightType = isset($val2['weight_type']) ? $val2['weight_type'] : '0';
                                            $dimentionType = isset($val2['dimention_type']) ? $val2['dimention_type'] : '0';
                                            $productType = (isset($val2['product_type']) && !empty($val2['product_type'])) ? $val2['product_type'] : '';
                                            $variationId = (isset($val2['variation_id']) && !empty($val2['variation_id'])) ? $val2['variation_id'] : '';
                                            $appendVariationId = !empty($variationId) ? '_' . $variationId : '';
                                            if ($weightType == 'pound') {
                                                $newWeight = number_format($product_weight * 16, 3);
                                                $newWeight = (float) $newWeight;
                                                $product_weight = round($newWeight);
                                                if ($product_weight == 0) {
                                                    $product_weight = 1;
                                                }
                                            }

                                            if ($dimentionType == 'cm') {
                                                $new_length = number_format($productSize_length * 0.393701, 3);
                                                $new_length = (float) $new_length;
                                                $productSize_length = round($new_length);

                                                if ($productSize_length == 0 && $new_length > 0) {
                                                    $productSize_length = 1;
                                                }

                                                $new_width = number_format($productSize_width * 0.393701, 3);
                                                $new_width = (float) $new_width;
                                                $productSize_width = round($new_width);
                                                if ($productSize_width == 0 && $new_width > 0) {
                                                    $productSize_width = 1;
                                                }
                                                $new_height = number_format($productSize_height * 0.393701, 3);
                                                $new_height = (float) $new_height;
                                                $productSize_height = round($new_height);
                                                if ($productSize_height == 0 && $new_height > 0) {
                                                    $productSize_height = 1;
                                                }


                                            } else if ($dimentionType == 'mm') {

                                                $new_length = number_format($productSize_length * 0.03937, 3);
                                                $new_length = (float) $new_length;
                                                $productSize_length = round($new_length);
                                                if ($productSize_length == 0 && $new_length > 0) {
                                                    $productSize_length = 1;
                                                }
                                                $new_width = number_format($productSize_width * 0.03937, 3);
                                                $new_width = (float) $new_width;
                                                $productSize_width = round($new_width);
                                                if ($productSize_width == 0 && $new_width > 0) {
                                                    $productSize_width = 1;
                                                }

                                                $new_height = number_format($productSize_height * 0.03937, 3);
                                                $new_height = (float) $new_height;
                                                $productSize_height = round($new_height);
                                                if ($productSize_height == 0 && $new_height > 0) {
                                                    $productSize_height = 1;
                                                }


                                            }


                                            $shipping_option_id = isset($val2['shipping_option_id']) ? $val2['shipping_option_id'] : '0';
                                            $product_tax = isset($val2['product_tax']) ? $val2['product_tax'] : '0';

                                            $product_variant_price = isset($val2['variation_sale_price']) ? $val2['variation_sale_price'] : $val2['price'];
                                            $product_type = isset($val2['product_type']) ? $val2['product_type'] : "";

                                            if ($product_type == 'variant') {
                                                $product_price = isset($val2['variation_sale_price']) ? $val2['variation_sale_price'] : $val2['price'];
                                                $actualPrice = $val2['price'];
                                            } else {
                                                $product_price = $val2['on_sale'] == 1 ? $val2['sale_price'] : $val2['price'];
                                                $actualPrice = $val2['price'];
                                                //                                        $product_price = isset($val2['price_cad']) ? $val2['price_cad'] : $val2['price'];
                                            }
                                            $totalProdductsPriceCrypto += $product_price + $shipping_price;


                                            $shipping_service = isset($val2['shipping_service']) ? $val2['shipping_service'] : '';
                                            $proPrice = ($product_price * $val2['product_quantity']) + $shipping_price;
                                            $proPriceRealCrypto = ($product_price * $val2['product_quantity']);
                                            $proPriceActual = ($actualPrice * $val2['product_quantity']);
                                            $product_quantity = isset($val2['product_quantity']) ? $val2['product_quantity'] : "1";
                                            $note = '';
                                            $totalShipPrice = $totalShipPrice + $totalStoreShipPrice;
                                            $attribute1 = isset($val2['attribute1']) ? $val2['attribute1'] : "";
                                            $attribute2 = isset($val2['attribute2']) ? $val2['attribute2'] : "";
                                            $attribute3 = isset($val2['attribute3']) ? $val2['attribute3'] : "";
                                            if ($shipping_price == 0 && $shipping_method == 'free') {
                                                $note = '<p class="no_shipping"> Note: shipping cost for this product is included. </p>';
                                            }

                                                                                                ?>
                                                <div class="row list_crypto_{{ $val2['product_id'] . $appendVariationId }}"
                                                    id="list_crypto_{{ $val2['product_id'] . $appendVariationId }}">
                                                    <div class="cell prdcl"><span class="prdcnt">{{ $val2['product_quantity'] }}</span><img
                                                            src="<?php                        echo !empty($image_url) ? $image_url : URL::asset('/assets/images/placeholder.jpg'); ?>"
                                                            alt="{{ $val2['title'] }}" class="cart-product-thumbnail" width="64"></div>
                                                    <div class="cell text-left">{{ $title }}</div>
                                                    <div class="cell" style="max-width: 150px; position:relative">
                                                        <?php                        if ($val2['product_type'] != 'virtual') {
                                                if ($is_domestic_free == '0') {
                                                    if ($flat_shipping_rate == '0') { ?>
                                                        <?php                                    if (($productSize_width == null || $productSize_width == 0) && ($productSize_length == null || $productSize_length == 0) && ($productSize_height == null || $productSize_height == 0)) { ?>
                                                        @if ($productType == 'simple')
                                                            <select
                                                                class="custom-select custom-select-sm shipping_usps_service_checkout_crypto  shipping_service_{{ $val2['product_id'] }}"
                                                                id="shipping_service">
                                                                <?php                                            foreach ($usps_services as $key => $service) { ?>
                                                                <option class="{{ $service }}"
                                                                    value="{{ $service }}_{{ $product_weight }}_{{ $origin_zip }}_{{ $product_quantity }}_{{ $key2 }}_{{ $storeId }} "
                                                                    disabled>{{ $service }}</option>
                                                                <?php                                            }?>


                                                            </select>
                                                        @elseif($productType == 'variant')
                                                            @if ($prevProductIdCrypto != $val2['product_id'] && !empty($variationId))
                                                                <select
                                                                    class="custom-select custom-select-sm shipping_usps_service_checkout_crypto  shipping_service_{{ $val2['product_id'] }}"
                                                                    id="shipping_service">
                                                                    <?php                                                foreach ($usps_services as $key => $service) { ?>
                                                                    <option class="{{ $service }}"
                                                                        value="{{ $service }}_{{ $product_weight }}_{{ $origin_zip }}_{{ $product_quantity }}_{{ $key2 }}_{{ $storeId }} "
                                                                        disabled>{{ $service }}</option>
                                                                    <?php                                                }?>


                                                                </select>
                                                            @endif
                                                        @endif
                                                        <?php                                    } else { ?>
                                                        @if ($productType == 'simple')
                                                            <select
                                                                class="custom-select custom-select-sm shipping_service_checkout_crypto  shipping_service_{{ $val2['product_id'] }}"
                                                                id="shipping_service">
                                                                <?php                                            foreach ($shipping_service as $service) { ?>
                                                                <option class="{{ $service }}"
                                                                    value="{{ $service }}_{{ $storeId }}_{{ $val2['product_id'] }}_{{ $product_weight }}_{{ $origin_zip }}_{{ $shipping_option_id }}_{{ $productSize_length }}_{{ $productSize_width }}_{{ $productSize_height }}_{{ $product_quantity }}_{{ $proPriceRealCrypto }}_{{ $title }}_{{ $key2 }}"
                                                                    disabled>{{ $service }}</option>
                                                                <?php                                            }?>


                                                            </select>
                                                        @elseif($productType == 'variant')
                                                            @if ($prevProductIdCrypto != $val2['product_id'] && !empty($variationId))
                                                                <select
                                                                    class="custom-select custom-select-sm shipping_service_checkout_crypto  shipping_service_{{ $val2['product_id'] }}"
                                                                    id="shipping_service">
                                                                    <?php                                                foreach ($shipping_service as $service) { ?>
                                                                    <option class="{{ $service }}"
                                                                        value="{{ $service }}_{{ $storeId }}_{{ $val2['product_id'] }}_{{ $product_weight }}_{{ $origin_zip }}_{{ $shipping_option_id }}_{{ $productSize_length }}_{{ $productSize_width }}_{{ $productSize_height }}_{{ $product_quantity }}_{{ $proPriceRealCrypto }}_{{ $title }}_{{ $key2 }}"
                                                                        disabled>{{ $service }}</option>
                                                                    <?php                                                }?>


                                                                </select>
                                                            @endif
                                                        @endif

                                                        <?php                                    } ?>

                                                        <?php                                }
                                                } ?>
                                                        <?php                        } ?>
                                                        <?php 

                                                                                                                    if ($note == '') {

                                                if ($shipping_method == 'flat') { ?>
                                                        @if ($productType == 'simple')
                                                            <input type="hidden" name="shipping_data" class="get_data_calulate_ship_crypto"
                                                                data-shipping-price="{{ number_format($flat_shipping_rate, 2) }}"
                                                                value="Priority_{{ $storeId }}_{{ $val2['product_id'] }}_{{ $product_weight }}_{{ $origin_zip }}_{{ $shipping_option_id }}_{{ $product_quantity }}_{{ $proPriceRealCrypto }}_{{ $title }}_{{ $key2 }}_{{ $productSize_length }}_{{ $productSize_width }}_{{ $productSize_height }}_{{ $productType }}_{{ $variationId }}_{{ $is_domestic_free }}_{{ $flat_shipping_rate }}_{{ $product_id }}">
                                                            <span class="flat_price shipping_price_calculated_crypto"
                                                                data-ship-price="{{ number_format($shipping_price, 2) }}" data-shipment-id=""
                                                                data-rate-id="" id="shipping_price_calculated_crypto">
                                                                (flat) {{ number_format($flat_shipping_rate, 2) }} </span>
                                                        @elseif($productType == 'variant')
                                                            @if ($prevProductIdCrypto != $val2['product_id'] && !empty($variationId))
                                                                <input type="hidden" name="shipping_data" class="get_data_calulate_ship_crypto"
                                                                    data-shipping-price="{{ number_format($flat_shipping_rate, 2) }}"
                                                                    value="Priority_{{ $storeId }}_{{ $val2['product_id'] }}_{{ $product_weight }}_{{ $origin_zip }}_{{ $shipping_option_id }}_{{ $product_quantity }}_{{ $proPriceRealCrypto }}_{{ $title }}_{{ $key2 }}_{{ $productSize_length }}_{{ $productSize_width }}_{{ $productSize_height }}_{{ $productType }}_{{ $variationId }}_{{ $is_domestic_free }}_{{ $flat_shipping_rate }}_{{ $product_id }}">
                                                                <span class="flat_price shipping_price_calculated_crypto"
                                                                    data-ship-price="{{ number_format($shipping_price, 2) }}" data-shipment-id=""
                                                                    data-rate-id="" id="shipping_price_calculated_crypto">
                                                                    (flat) {{ number_format($flat_shipping_rate, 2) }} </span>
                                                            @else
                                                                <input type="hidden" name="shipping_data" class="get_data_calulate_ship_crypto"
                                                                    data-shipping-price="{{ number_format($flat_shipping_rate, 2) }}"
                                                                    value="Priority_{{ $storeId }}_{{ $val2['product_id'] }}_{{ $product_weight }}_{{ $origin_zip }}_{{ $shipping_option_id }}_{{ $product_quantity }}_{{ $proPriceRealCrypto }}_{{ $title }}_{{ $key2 }}_{{ $productSize_length }}_{{ $productSize_width }}_{{ $productSize_height }}_{{ $productType }}_{{ $variationId }}_{{ $is_domestic_free }}_{{ $flat_shipping_rate }}_{{ $product_id }}">

                                                                {{-- <span class="flat_price shipping_price_calculated"
                                                                    data-ship-price="{{number_format($shipping_price,2)}}" data-shipment-id=""
                                                                    data-rate-id="" id="shipping_price_calculated">
                                                                    (flat) {{number_format($flat_shipping_rate,2)}} </span> --}}
                                                            @endif
                                                        @endif
                                                        <?php                            } else {  
                                                                                                                        ?>
                                                        @if ($productType == 'simple')
                                                            <p class="small shpnginf">
                                                                Shipping :
                                                                <input type="hidden" name="shipping_data" class="get_data_calulate_ship_crypto"
                                                                    value="Priority_{{ $storeId }}_{{ $val2['product_id'] }}_{{ $product_weight }}_{{ $origin_zip }}_{{ $shipping_option_id }}_{{ $product_quantity }}_{{ $proPriceRealCrypto }}_{{ $title }}_{{ $key2 }}_{{ $productSize_length }}_{{ $productSize_width }}_{{ $productSize_height }}_{{ $productType }}_{{ $variationId }}_{{ $is_domestic_free }}_{{ $flat_shipping_rate }}_{{ $product_id }}">
                                                                <span class="shipping_price_calculated_crypto"
                                                                    data-ship-price="{{ number_format($shipping_price, 2) }}"
                                                                    data-shipment-id="" data-rate-id="" id="shipping_price_calculated_crypto">
                                                                </span>
                                                            </p>
                                                        @elseif($productType == 'variant')
                                                            @if ($prevProductIdCrypto != $val2['product_id'] && !empty($variationId))
                                                                <p class="small shpnginf">
                                                                    Shipping :
                                                                    <input type="hidden" name="shipping_data" class="get_data_calulate_ship_crypto"
                                                                        value="Priority_{{ $storeId }}_{{ $val2['product_id'] }}_{{ $product_weight }}_{{ $origin_zip }}_{{ $shipping_option_id }}_{{ $product_quantity }}_{{ $proPriceRealCrypto }}_{{ $title }}_{{ $key2 }}_{{ $productSize_length }}_{{ $productSize_width }}_{{ $productSize_height }}_{{ $productType }}_{{ $variationId }}_{{ $is_domestic_free }}_{{ $flat_shipping_rate }}_{{ $product_id }}">
                                                                    <span class="shipping_price_calculated_crypto"
                                                                        data-ship-price="{{ number_format($shipping_price, 2) }}"
                                                                        data-shipment-id="" data-rate-id="" id="shipping_price_calculated_crypto">
                                                                    </span>
                                                                </p>
                                                            @else
                                                                <p class="small shpnginf">

                                                                    <input type="hidden" name="shipping_data" class="get_data_calulate_ship_crypto"
                                                                        value="Priority_{{ $storeId }}_{{ $val2['product_id'] }}_{{ $product_weight }}_{{ $origin_zip }}_{{ $shipping_option_id }}_{{ $product_quantity }}_{{ $proPriceRealCrypto }}_{{ $title }}_{{ $key2 }}_{{ $productSize_length }}_{{ $productSize_width }}_{{ $productSize_height }}_{{ $productType }}_{{ $variationId }}_{{ $is_domestic_free }}_{{ $flat_shipping_rate }}_{{ $product_id }}">

                                                                </p>
                                                            @endif
                                                            @if ($prevProductIdCrypto == $val2['product_id'] && ($variationId != null || !empty($variationId)))
                                                            @endif
                                                        @endif
                                                        <?php                            }
                                            } else { ?>

                                                        <input type="hidden" name="shipping_data" class="get_data_calulate_ship_crypto"
                                                            value="Priority_{{ $storeId }}_{{ $val2['product_id'] }}_{{ $product_weight }}_{{ $origin_zip }}_{{ $shipping_option_id }}_{{ $product_quantity }}_{{ $proPriceRealCrypto }}_{{ $title }}_{{ $key2 }}_{{ $productSize_length }}_{{ $productSize_width }}_{{ $productSize_height }}_{{ $productType }}_{{ $variationId }}_{{ $is_domestic_free }}_{{ $flat_shipping_rate }}_{{ $product_id }}">


                                                        <?php  
                                                                                                                echo $note;
                                            }
                                                                                                                ?>

                                                    </div>
                                                    <div class="cell text-right"
                                                        style="max-width: 85px!important; font-size:12px; line-height:16px">
                                                        : <span class="prcbx real_product_price_crypto"
                                                            data-real-price="{{ $proPriceRealCrypto }}">${{ number_format(round($product_price * $val2['product_quantity'], 4), 2) }}</span>
                                                    </div>
                                                </div>
                                                <?php
                                            $storeTotalPrice = $storeTotalPrice + $product_price;
                                            $prevProductIdCrypto = $val2['product_id'];
                                            $prevVariationIdCrypto = $variationId;
                                        }
                                    }

                                }
                                $counter++;
                                if (!empty($storeId)) {

                                }

                            }


                                                                                ?>



                                            </div>




                                            <div class="table mt-0 mb-5" style="background: #fff;">
                                                <div class="row">
                                                    <div class="col text-left">Sub Total:</div>
                                                    <div class="col text-right">$<span
                                                            class="prcbx subtotal_product_stripe_crypto">{{ number_format($ercSubTotalCrypto, 2) }}</span>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col text-left">Tax:</div>
                                                    <div class="col text-right"><span class="prcbx">$0.00</span></div>
                                                </div>
                                                <div class="row">
                                                    <div class="col text-left">Shipping:</div>
                                                    <div class="col text-right"><span class="prcbx product_shipping_total_crypto"
                                                            data-product_shipping_total="">$0.00</span></div>
                                                </div>
                                                <div class="row ttlbx">
                                                    <div class="col text-left">Total:</div>
                                                    <div class="col text-right">: <span
                                                            class="prcbx grandtotal_cal_crypto">{{ number_format($ercTotalCrypto, 2) }}</span>
                                                    </div>
                                                </div>
                                            </div>

                                            <h2 class="frmhd" style="margin-bottom: 0!important; margin-top:50px!important">Pay with
                                            </h2>

                                            <div class="pymntslct row" style="border-bottom: 0;">



                                                <div class="box" id="box1">
                                                    <form action="/meta-mask" method="POST" id="orderDataSend">
                                                        {{ csrf_field() }}
                                                        <h2>Connect with Meta Mask</h2>
                                                        <?php        if (Session::has('userData')) { ?>
                                                        <input type="hidden" name="stripe_email" class="form-control" id="crypto_email"
                                                            value="{{ $userData->email }}">
                                                        <?php        } ?>
                                                        <input type="hidden" name="stripe_email" class="form-control" id="crypto_email"
                                                            value="">
                                                        <input type="hidden" id="shipping_data" name="shipping_data">
                                                        <input type="hidden" id="orderData" name="orderdata" value="">
                                                        <input class="primary-btn btn btn-primary" id="usdcprice" hidden price="0.2"
                                                            type="button" name="submit" value="Pay Via MetaMask">
                                                        <div class="row" style="padding:20px; padding-top:0; border-bottom:0px">
                                                            <div class="col-md-12">
                                                                <p class="text-center">
                                                                    <?php        echo '<div id="polygon"> </div>'; ?>
                                                                </p>
                                                            </div>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                            <script>
                                                var orderDataCrypto = <?php        echo json_encode($mainArrayCrypto); ?>;
                                                console.log("orderDataCrypto order", orderDataCrypto);
                                                $('#orderData').val(JSON.stringify(orderDataCrypto));
                                            </script>
                        @endif
                        <?php } ?>
                    </div>
                    <!-- Crypto End -->

                </div>
            </div>
            <!--<div class="col-12">
                                                                                        <a style="background: #EDB83F 0% 0% no-repeat padding-box;border-radius: 22px;opacity: 1; font-size:14px; padding:10px 30px; color:#fff" href="/q">Continue Shopping</a>
                                                                                    </div> -->
            <!-- Stripe End -->
        </div>

        <!-- Crypto Start -->



    </div>
    <section class="readypayment-contianer">

        <div class="shipping_amount" style="display:none;">


            <div class="container">
                <div class="confirmation-heading-section">
                    <div class="checkoutSuccessHeading">
                        <h1>Your Order Has Been Sent!</h1>
                    </div>
                    <div class="details">
                        <h3 style="color:#0510FA!important;font-weight:500;font-size: 17px;"> <span
                                style="font-weight:600">Congratulations!</span> Check your email for your order
                            details!</h4>
                    </div>
                </div>
            </div>

            <div class="confirmation_images">
                <div class="container">
                    <div class="images-section">
                        <div class="confirmaion-shark-img">
                            <img src="{{ URL::asset('assets/images/Sharkflip.png') }}" alt="image" srcset="">

                            <div class="congragulationImg">
                                <img src="{{ URL::asset('assets/images/congragulation-img.PNG') }}" alt="image"
                                    srcset="">

                            </div>
                        </div>
                        <div class="confirmaion-CoinBox-img">
                            <img src="{{ URL::asset('assets/images/wooden-treasure-chest copy.png') }}" alt="image"
                                srcset="">

                        </div>
                    </div>
                </div>
            </div>

        </div>

        <div class="checkout-custom-btn text-center" style="display: none">
            <button class="btn btn-primary">
                <a href="https://mallshark.net/search?keyword=">Continue Shopping</a>
            </button>
        </div>
</div>
</section>
<script src="https://js.stripe.com/v3/"></script>
<script>
    var stripe = Stripe('<?php echo env('STRIPE_API_KEY_PUBLISHABLE'); ?>');
var elements = stripe.elements();

// Create individual elements
var cardNumber = elements.create('cardNumber', {
    style: {
        base: {
            color: '#32325d',
            fontSize: '16px',
            '::placeholder': {
                color: '#aab7c4'
            }
        }
    }
});

var cardExpiry = elements.create('cardExpiry', {
    style: {
        base: {
            color: '#32325d',
            fontSize: '16px',
            '::placeholder': {
                color: '#aab7c4'
            }
        }
    }
});

var cardCvc = elements.create('cardCvc', {
    style: {
        base: {
            color: '#32325d',
            fontSize: '16px',
            '::placeholder': {
                color: '#aab7c4'
            }
        }
    }
});
// var cardZipCode = elements.create('cardZipCode', {
//     style: {
//         base: {
//             color: '#32325d',
//             fontSize: '16px',
//             '::placeholder': {
//                 color: '#aab7c4'
//             }
//         }
//     }
// });

// Mount the elements to the DOM
cardNumber.mount('#card-number-element');
cardExpiry.mount('#card-expiry-element');
cardCvc.mount('#card-cvc-element');
// cardZipCode.mount('#card-zip-element')

// Handle form submission
var form = document.getElementById('payment-form');
form.addEventListener('submit', function(event) {
    event.preventDefault();

    stripe.createToken(cardNumber).then(function(result) {
        if (result.error) {
            // Display error in card-errors div
            var errorElement = document.getElementById('card-errors');
            errorElement.classList.add('error');
            errorElement.textContent = result.error.message;
            setTimeout(function () {
                errorElement.textContent = '';
            }, 3000);
        } else {
            // Send token to the server
           
            stripeTokenHandler(result.token);
        }
    });
});
        // Handle form submission
        // var form = document.getElementById('payment-form');
        // form.addEventListener('submit', function(event) {
        //     event.preventDefault();
        // });

        function stripeTokenHandler(token) {
            // Insert the token ID into the form so it gets submitted to the server
            var form = document.getElementById('payment-form');
            var hiddenInput = document.createElement('input');
            hiddenInput.setAttribute('type', 'hidden');
            hiddenInput.setAttribute('name', 'stripeToken');
            hiddenInput.setAttribute('value', token.id);
            form.appendChild(hiddenInput);
        }



    //KEYUP FLAG
    var started = false;
    // var enablePaymentMethod = false;
    var paymentRequestContainer = document.getElementById('payment-request-button');

    async function fetchClientSecret(paymentMethodId, customerData) {
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        return new Promise((resolve, reject) => {
            $.ajax({
                url: "{{ env('base_url') }}" + '/create-payment-intent',
                type: 'POST',
                contentType: 'application/json',
                headers: {
                    'X-CSRF-TOKEN': csrfToken
                },
                data: JSON.stringify({
                    paymentMethod: paymentMethodId,
                    first_name: customerData.first_name,
                    last_name: customerData.last_name,
                    user_email: customerData.user_email,
                    address: customerData.address,
                    city: customerData.city,
                    states: customerData.states,
                    country: customerData.country,
                    post_code: customerData.post_code,
                    orderdata: customerData.orderdata,
                    total_amount: customerData.total_amount,
                    amount: customerData.amount,
                    fcm_token: customerData.fcm_token,
                    app_flag: customerData.app_flag,
                    isChecked: customerData.isChecked
                }),
                success: function (response) {
                    // Handle successful response and extract clientSecret, ephemeralKey, customerId
                    if (response.paymentIntent) {
                        resolve(
                            response
                        ); // Send the entire response object back to use required fields
                    } else {
                        reject('Failed to retrieve payment intent.');
                    }
                },
                error: function (xhr, status, error) {
                    console.error('Error occurred:', error);
                    reject(error); // Reject the promise with the error
                }
            });
        });
    }

</script>


<style>
    .stripe-button-el {
        display: none;
    }
</style>
<!-- <script src="{{ URL::asset('assets/js/jquery.validate.js'), env('HTTPS_STATUS') }}"></script> -->
<script>
    // get the success message from the session
    var successMsg = "{{ session('success') }}";

    // if the success message exists, show the alert and change the opacity
    if (successMsg) {
        alert(successMsg);
        document.getElementById("checkout-page").style.opacity = "0.5";
    }
</script>


<!-- JS starts -->

<script>
    $(document).ready(function () {
        // setTimeout(() => {

        onLoadRates();
        // }, 0);
        $.validator.addMethod("validEmail", function (value, element) {
            // Regular expression pattern to validate email
            let emailPattern = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;

            // Check if the email matches the pattern
            return this.optional(element) || emailPattern.test(value);
        }, "Please enter a valid email address.");


        $.validator.addMethod("storeOwnerCheck", function (value, element, storeOwners) {
            // Check if the email exists in the store owners array
            let userEmailLower = value.toLowerCase();
            let storeOwnersLower = storeOwners.map(owner => owner.toLowerCase());
            return !storeOwnersLower.includes(userEmailLower);
        },
            "You can not buy your own product, Please remove own  product or try with another email address."
        );

        // console.log("orderDataCrypto" , orderDataCrypto);


        // var orderDataCrypto = []; 
        // if (orderDataCrypto && orderDataCrypto.length > 0) {
        //     $('#orderData').val(JSON.stringify(orderDataCrypto));
        // }
        // $('#orderData_stripe').val(JSON.stringify(orderData));


        $("#orderDataSend").submit(function (e) {

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
                var data2 = '&full_name=' + fullName + '&s_country=' + country + '&city=' + city +
                    '&states_countries=' + states + '&s_street=' + street + '&s_post_code=' + zipcode;

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
                    success: function (data) {
                        // window.location.href = '/order-confirmation'; 
                        swal("Success!",
                            "Created Order. if you purchased virtual product please check order section Thank you.",
                            "success");
                        var url = '{{ Request::root() }}';


                    }
                });
            }

        });

        // On chnage request
        var tshpPrice = 0;
        $('.shipping_usps_service_checkout').on('change', function (key, value) {
            // return;
            console.log('key', key);
            console.log('value', value);
            let shipment_data = $('#shispping_form').serialize();
            let that = $(this);
            let val = $(this).val().split("_");
            let service = val[0];
            let weight = val[1];
            let origin_zip = val[2];
            let product_quantity = val[3];
            let product_keyChange = val[4];
            let storeId = parseInt(val[5]);

            console.log("product_keyChange", product_keyChange);
            const keyObjectChange = {
                product_keyChange
            };
            //     return;

            // setTimeout(function() {
            $.ajax({
                url: '/calculate_usps_service_change',
                type: "POST",
                data: {
                    service,
                    origin_zip,
                    weight,
                    product_quantity,
                    shipment_data,
                    _token: '{{ csrf_token() }}'
                },
                success: function (data) {
                    let response = JSON.parse(data)

                    $(that).parent().parent().find('.shipping_price_calculated').html(
                        response.rate);
                    $(that).parent().parent().find('.shipping_price_calculated').attr(
                        'data-ship-price', response.rate);

                    // update OrderData

                    orderData[storeId][product_keyChange].shipping_price = response.rate;
                    orderData[storeId][product_keyChange].shipping_method = service;
                    $('#orderData_stripe').val(JSON.stringify(orderData));
                    let total = 0;
                    let subTotal = 0;
                    let totalShipping = 0;
                    Object.keys(orderData).forEach(key => {
                        let store = orderData[key]
                        store.forEach(product => {
                            let price = product?.product_type == 'variant' ?
                                product?.variation_sale_price : product
                                    .price;
                            subTotal += parseFloat(price * product
                                .product_quantity)
                            totalShipping += parseFloat(product
                                ?.shipping_price)
                        });
                    })
                    total = subTotal + totalShipping;
                    $(".subtotal_product_stripe").html(subTotal.toFixed(2));
                    $(".product_shipping_total").html(totalShipping.toFixed(2));
                    $(".grandtotal_cal").html(total.toFixed(2));
                    $("#total_amount_ship").val(total * 100);

                    // loadStripeCheckout_Change(stripe_total);
                    $(".finalsubmit").show();
                    // $("#total_amount_ship").val(stripe_total);
                }
            });
            // }, 1); // Delay the request by 2 seconds      

        });
        $('.shipping_usps_service_checkout_crypto').on('change', function (key, value) {
            // return;

            let shipment_data = $('#shispping_form').serialize();
            let that = $(this);
            let val = $(this).val().split("_");
            let service = val[0];
            let weight = val[1];
            let origin_zip = val[2];
            let product_quantity = val[3];
            let product_keyChange = val[4];
            let storeId = parseInt(val[5]);

            // setTimeout(function() {
            $.ajax({
                url: '/calculate_usps_service_change',
                type: "POST",
                data: {
                    service,
                    origin_zip,
                    weight,
                    product_quantity,
                    shipment_data,
                    _token: '{{ csrf_token() }}'
                },
                success: function (data) {
                    let response = JSON.parse(data);

                    // return;

                    $(that).parent().parent().find('.shipping_price_calculated_crypto')
                        .html(response.rate);
                    $(that).parent().parent().find('.shipping_price_calculated_crypto')
                        .attr('data-ship-price', response.rate);


                    // update OrderData

                    orderDataCrypto[storeId][product_keyChange].shipping_price = response
                        .rate;
                    orderDataCrypto[storeId][product_keyChange].shipping_method = service;
                    let total = 0;
                    let subTotal = 0;
                    let totalShipping = 0;
                    Object.keys(orderDataCrypto).forEach(key => {
                        let store = orderDataCrypto[key]
                        store.forEach(product => {
                            let price = product?.product_type == 'variant' ?
                                product?.variation_sale_price : product
                                    .price;
                            subTotal += parseFloat(price * product
                                .product_quantity)
                            totalShipping += parseFloat(product
                                ?.shipping_price)
                        });
                    })
                    total = subTotal + totalShipping;
                    $(".subtotal_product_stripe_crypto").html(subTotal.toFixed(2));
                    $(".product_shipping_total_crypto").html(totalShipping.toFixed(2));
                    $(".grandtotal_cal_crypto").html(total.toFixed(2));
                    setCryptoPayable(total)


                    // let stripe_total = grandTotal*100;

                    // $(".finalsubmit").show();
                    // $("#total_amount_ship").val(stripe_total);
                }
            });
            // }, 1); // Delay the request by 2 seconds      

        });
        $('.shipping_service_checkout').on('change', function (key, value) {
            // return;

            let shipment_data = $('#shispping_form').serialize();
            let that = $(this);
            let val = $(this).val().split("_");
            let shipping_method = val[0];
            let storeId = val[1];
            let productId = val[2];
            let weight = val[3];
            let origin_zip = val[4];
            let shipping_option_id = val[5];
            let product_length = val[6];
            let product_width = val[7];
            let product_height = val[8];
            let product_quantity = val[9];
            let product_price = val[10];
            let product_title = val[11];
            let product_keyChange = val[12];

            $.ajax({
                url: '/calculate_service_change',
                type: "POST",
                data: {
                    value: shipping_method,
                    storeId: storeId,
                    origin_zip: origin_zip,
                    shipping_option_id: shipping_option_id,
                    weight: weight,
                    product_length: product_length,
                    product_width: product_width,
                    product_height: product_height,
                    product_quantity: product_quantity,
                    product_price: product_price,
                    product_title: product_title,
                    shipment_data: shipment_data,
                    _token: '{{ csrf_token() }}'
                },
                success: function (data) {
                    console.log('ship rate', data);
                    let response = data;

                    if (response?.error) {
                        shippingErrorResponse('Post Code is not correct', true);
                        return;
                    }

                    $(that).parent().parent().find('.shipping_price_calculated').html(data
                        .rate);
                    $(that).parent().parent().find('.shipping_price_calculated').attr(
                        'data-ship-price', data.rate);

                    orderData[storeId][product_keyChange].shipping_price = response.rate;
                    orderData[storeId][product_keyChange].shipping_method =
                        shipping_method; //value is service
                    //   add rate_id and ship_id in easypost case
                    orderData[storeId][product_keyChange].ship_id = response.shipment_id ?
                        response.shipment_id : '';
                    orderData[storeId][product_keyChange].rate_id = response.rate_id ?
                        response.rate_id : '';

                    $('#orderData_stripe').val(JSON.stringify(orderData));
                    let total = 0;
                    let subTotal = 0;
                    let totalShipping = 0;
                    Object.keys(orderData).forEach(key => {

                        let store = orderData[key]
                        store.forEach(product => {

                            let price = product?.product_type == 'variant' ?
                                product?.variation_sale_price : product
                                    .price;
                            subTotal += parseFloat(price * product
                                .product_quantity)
                            totalShipping += parseFloat(product
                                ?.shipping_price)

                        });
                    })
                    total = subTotal + totalShipping;

                    $(".subtotal_product_stripe").html(subTotal.toFixed(2));
                    $(".product_shipping_total").html(totalShipping.toFixed(2));
                    $(".grandtotal_cal").html(total.toFixed(2));
                    $("#total_amount_ship").val(total * 100);

                    return;

                    $(".finalsubmit").show();

                }
            });

        });
        $('.shipping_service_checkout_crypto').on('change', function (key, value) {
            // return;

            let shipment_data = $('#shispping_form').serialize();
            let that = $(this);
            let val = $(this).val().split("_");
            let shipping_method = val[0];
            let storeId = val[1];
            let productId = val[2];
            let weight = val[3];
            let origin_zip = val[4];
            let shipping_option_id = val[5];
            let product_length = val[6];
            let product_width = val[7];
            let product_height = val[8];
            let product_quantity = val[9];
            let product_price = val[10];
            let product_title = val[11];
            let product_keyChange = val[12];

            // setTimeout(function() {
            $.ajax({
                url: '/calculate_service_change',
                type: "POST",
                data: {
                    value: shipping_method,
                    storeId: storeId,
                    origin_zip: origin_zip,
                    shipping_option_id: shipping_option_id,
                    weight: weight,
                    product_length: product_length,
                    product_width: product_width,
                    product_height: product_height,
                    product_quantity: product_quantity,
                    product_price: product_price,
                    product_title: product_title,
                    shipment_data: shipment_data,
                    _token: '{{ csrf_token() }}'
                },
                success: function (data) {
                    console.log('ship rate', data);
                    let response = data;
                    if (response?.error) {
                        shippingErrorResponse('Post Code is not correct', true);
                        return;
                    }

                    $(that).parent().parent().find('.shipping_price_calculated_crypto')
                        .html(data.rate);
                    $(that).parent().parent().find('.shipping_price_calculated_crypto')
                        .attr('data-ship-price', data.rate);

                    orderDataCrypto[storeId][product_keyChange].shipping_price = response
                        .rate;
                    orderDataCrypto[storeId][product_keyChange].shipping_method =
                        shipping_method; //shipping_method is service
                    //   add rate_id and ship_id in easypost case
                    orderDataCrypto[storeId][product_keyChange].ship_id = response
                        .shipment_id ? response.shipment_id : '';
                    orderDataCrypto[storeId][product_keyChange].rate_id = response.rate_id ?
                        response.rate_id : '';
                    let total = 0;
                    let subTotal = 0;
                    let totalShipping = 0;
                    Object.keys(orderDataCrypto).forEach(key => {
                        let store = orderDataCrypto[key]
                        store.forEach(product => {
                            let price = product?.product_type == 'variant' ?
                                product?.variation_sale_price : product
                                    .price;
                            subTotal += parseFloat(price * product
                                .product_quantity)
                            totalShipping += parseFloat(product
                                ?.shipping_price)
                        });
                    })
                    total = subTotal + totalShipping;
                    $(".subtotal_product_stripe_crypto").html(subTotal.toFixed(2));
                    $(".product_shipping_total_crypto").html(totalShipping.toFixed(2));
                    $(".grandtotal_cal_crypto").html(total.toFixed(2));
                    setCryptoPayable(total)

                    return;

                    $(".finalsubmit").show();

                }
            });

        });

        // Payment with meta mask hardcoded
        //    $("#usdcprice").attr('price', "0.2");


        // keyupevent
        var startedCrypto = false;
        var timeoutId;
        $('#useremail,#inputFirstNameShipping,#inputLastNameShipping,#s_country , #states_countries, #street_address, #city_name, #zipcode')
            .on('keyup', function () {

                // if(!started){


                let stripeCal = {
                    subTotal: 0,
                    tax: 0,
                    shipping: 0,
                    total: 0
                }
                let cryptoCal = {
                    subTotal: 0,
                    tax: 0,
                    shipping: 0,
                    total: 0
                }
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
                clearTimeout(timeoutId);
                timeoutId = setTimeout(() => {

                    data2 = '&full_name=' + fullName + '&s_country=' + country + '&city=' + city +
                        '&states_countries=' +
                        states + '&s_street=' + street + '&s_post_code=' + zipcode;

                    if (country && states && street && city && zipcode && fullName) {
                        jQuery('#shipping_data').val(data2);
                        jQuery('#shipping_data_stripe').val(data2);
                    }

                    let emailPattern = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
                    if (!emailPattern.test(UserEmail)) {
                        return;
                    }

                    let storeOwners = <?php echo json_encode($store_owners); ?>;
                    let userEmailLower = UserEmail.toLowerCase();
                    let storeOwnersLower = storeOwners.map(owner => owner.toLowerCase());


                    if (storeOwnersLower.includes(userEmailLower)) {
                        return;
                    }

                    if (!started && country && states && street && city && firstname.trim() != '' &&
                        lastname.trim() != '' && UserEmail.trim() != '' && zipcode.length >= 5) {

                        $('#loading').show();

                        started = true;

                        //send ajax request to check if store ids has this email as owner
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


                        // var myArray = [];
                        var prevValue = null;
                        let prevProductId = null;
                        let prevProductIds = [];
                        let prevVariationId = null;
                        let totalItem = $('.get_data_calulate_ship').length - 1;

                        setTimeout(() => {
                            $('.get_data_calulate_ship').each(function (key, value) {
                                var shipping_error;
                                var that = $(this);
                                var val = $(this).val().split("_");
                                var value = val[0];
                                var storeId = val[1];
                                var productId = val[2];
                                var weight = val[3];
                                var origin_zip = val[4];
                                var shipping_option_id = val[5];
                                var product_quantity = val[6];
                                var product_price = val[7];
                                var product_title = val[8];
                                var product_key = val[9];
                                var product_length = val[10];
                                var product_width = val[11];
                                var product_height = val[12];
                                let product_type = val[13];
                                let variation_id = val[14]
                                let is_domestic_free = val[15]
                                let flat_shipping_rate = val[16]
                                let product_id = val[17]

                                var obj = {
                                    value: value,
                                    storeId: storeId,
                                    productId: productId,
                                    weight: weight,
                                    origin_zip: origin_zip,
                                    shipping_option_id: shipping_option_id,
                                    product_length: product_length,
                                    product_width: product_width,
                                    product_height: product_height,
                                    product_quantity: product_height,
                                    product_price: product_price,
                                    product_title: product_title,
                                    product_key: product_key,

                                };
                                let simple = false;
                                let variant = false;
                                let virtual = false;
                                console.log("obj", obj);

                                // var total =  $('.subtotal_product_stripe').text();

                                if (is_domestic_free > 0) {

                                    stripeCal.subTotal += parseFloat(product_price);
                                    stripeCal.total = stripeCal.subTotal + stripeCal
                                        .shipping;

                                    if (key == totalItem) {
                                        /*     $(".subtotal_product_stripe").html(stripeCal
                                                 .subTotal.toFixed(2));
                                                 */
                                        $(".product_shipping_total").html(stripeCal
                                            .shipping.toFixed(2));
                                        $(".grandtotal_cal").html(stripeCal.total
                                            .toFixed(2));
                                        $("#total_amount_ship").val(stripeCal
                                            .total * 100);
                                        $('#orderData_stripe').val(JSON.stringify(
                                            orderData));
                                        started = false;

                                        $('#loading').hide();
                                        $('#prcngbxiny').removeClass(
                                            'overlay-second');
                                    }
                                    // console.log('orderDataaa' , orderData)

                                    return;
                                } else if (flat_shipping_rate > 0) {

                                    stripeCal.shipping += parseFloat(
                                        flat_shipping_rate);
                                    stripeCal.subTotal += parseFloat(product_price);
                                    stripeCal.total = stripeCal.subTotal + stripeCal
                                        .shipping;

                                    orderData[storeId][product_key].shipping_price =
                                        flat_shipping_rate;

                                    // get rates here of flatshipping and add in shipping


                                    if (key == totalItem) {
                                        /*     $(".subtotal_product_stripe").html(stripeCal
                                           .subTotal.toFixed(2));
                                           */
                                        $(".product_shipping_total").html(stripeCal
                                            .shipping.toFixed(2));
                                        $(".grandtotal_cal").html(stripeCal.total
                                            .toFixed(2));
                                        $("#total_amount_ship").val(stripeCal
                                            .total * 100);
                                        $('#orderData_stripe').val(JSON.stringify(
                                            orderData));
                                        started = false;
                                        $('#loading').hide();
                                        $('#prcngbxiny').removeClass(
                                            'overlay-second');

                                    }

                                    return;
                                }
                                switch (product_type) {
                                    case 'simple':
                                        simple = true
                                        break;
                                    case 'variant':
                                        variant = true
                                        break;
                                    case 'virtual':
                                        virtual = true
                                        break;
                                    default:
                                        break;
                                }
                                if (product_type == 'simple') {
                                    simple = true;
                                } else if (product_type == 'variant') {
                                    variant = true;
                                }
                                if (simple) {

                                    let data = null;
                                    $.ajax({
                                        url: '/calculate_service',
                                        type: "POST",
                                        async: false,
                                        dataType: "json",
                                        data: {
                                            value: value,
                                            storeId: storeId,
                                            origin_zip: origin_zip,
                                            shipping_option_id: shipping_option_id,
                                            weight: weight,
                                            product_length: product_length,
                                            product_width: product_width,
                                            product_height: product_height,
                                            product_quantity: product_quantity,
                                            product_price: product_price,
                                            product_title: product_title,
                                            shipment_data: shipment_data,
                                            _token: '{{ csrf_token() }}'
                                        },
                                        success: function (res) {
                                            $('#prcngbxiny')
                                                .removeClass(
                                                    'overlay-second');
                                            if (Array.isArray(res)) {

                                                data = res;
                                            } else {
                                                data = Object.values(
                                                    res)
                                            }
                                            if (Array.isArray(data) &&
                                                data.length < 1) {
                                                shippingErrorResponse(
                                                    'Post Code is not correct',
                                                    true);
                                                return;
                                            }

                                            if (data.error || data[0]
                                                ?.error) {
                                                shipping_error = data
                                                    .error;
                                                shippingErrorResponse(
                                                    shipping_error);
                                                started = false;
                                                startedCrypto = false;
                                                return;

                                            } else {
                                                $('.pymntslct').show();
                                            }

                                            var vservice = $(".list_" +
                                                productId).find(
                                                    '.' + data.service);
                                            data.forEach((item) => {

                                                if (item
                                                    .service ==
                                                    'ParcelSelect'
                                                ) {
                                                    $(".list_" +
                                                        productId
                                                    )
                                                        .find(
                                                            '.' +
                                                            item
                                                                .service
                                                        )
                                                        .attr(
                                                            'disabled',
                                                            false
                                                        );
                                                    $(".list_" +
                                                        productId
                                                    )
                                                        .find(
                                                            ".shipping_price_calculated"
                                                        )
                                                        .html(
                                                            item
                                                                .rate
                                                        );
                                                    $(".list_" +
                                                        productId
                                                    )
                                                        .find(
                                                            ".shipping_price_calculated"
                                                        )
                                                        .attr(
                                                            'data-ship-price',
                                                            item
                                                                .rate
                                                        );

                                                    $(".list_" +
                                                        productId
                                                    )
                                                        .find(
                                                            ".shipping_price_calculated"
                                                        )
                                                        .attr(
                                                            'data-shipment-id',
                                                            item
                                                                ?.shipment_id ?
                                                                item
                                                                    .shipment_id :
                                                                '');
                                                    $(".list_" +
                                                        productId
                                                    )
                                                        .find(
                                                            ".shipping_price_calculated"
                                                        )
                                                        .attr(
                                                            'data-rate-id',
                                                            item
                                                                ?.id ?
                                                                item
                                                                    .id :
                                                                '');

                                                }
                                                if (item
                                                    .service ==
                                                    'Express') {

                                                    $(".list_" +
                                                        productId
                                                    )
                                                        .find(
                                                            '.' +
                                                            item
                                                                .service
                                                        )
                                                        .attr(
                                                            'disabled',
                                                            false
                                                        );
                                                    $(".list_" +
                                                        productId
                                                    )
                                                        .find(
                                                            ".shipping_price_calculated"
                                                        )
                                                        .html(
                                                            item
                                                                .rate
                                                        );
                                                    $(".list_" +
                                                        productId
                                                    )
                                                        .find(
                                                            ".shipping_price_calculated"
                                                        )
                                                        .attr(
                                                            'data-ship-price',
                                                            item
                                                                .rate
                                                        );

                                                    $(".list_" +
                                                        productId
                                                    )
                                                        .find(
                                                            ".shipping_price_calculated"
                                                        )
                                                        .attr(
                                                            'data-shipment-id',
                                                            item
                                                                ?.shipment_id ?
                                                                item
                                                                    .shipment_id :
                                                                '');
                                                    $(".list_" +
                                                        productId
                                                    )
                                                        .find(
                                                            ".shipping_price_calculated"
                                                        )
                                                        .attr(
                                                            'data-rate-id',
                                                            item
                                                                ?.id ?
                                                                item
                                                                    .id :
                                                                '');


                                                }
                                                if (item
                                                    .service ==
                                                    'Priority'
                                                ) {

                                                    $(".list_" +
                                                        productId
                                                    )
                                                        .find(
                                                            '.' +
                                                            item
                                                                .service
                                                        )
                                                        .attr(
                                                            'disabled',
                                                            false
                                                        );
                                                    $(".list_" +
                                                        productId
                                                    )
                                                        .find(
                                                            ".shipping_price_calculated"
                                                        )
                                                        .html(
                                                            item
                                                                .rate
                                                        );
                                                    $(".list_" +
                                                        productId
                                                    )
                                                        .find(
                                                            ".shipping_price_calculated"
                                                        )
                                                        .attr(
                                                            'data-ship-price',
                                                            item
                                                                .rate
                                                        );

                                                    $(".list_" +
                                                        productId
                                                    )
                                                        .find(
                                                            ".shipping_price_calculated"
                                                        )
                                                        .attr(
                                                            'data-shipment-id',
                                                            item
                                                                ?.shipment_id ?
                                                                item
                                                                    .shipment_id :
                                                                '');
                                                    $(".list_" +
                                                        productId
                                                    )
                                                        .find(
                                                            ".shipping_price_calculated"
                                                        )
                                                        .attr(
                                                            'data-rate-id',
                                                            item
                                                                ?.id ?
                                                                item
                                                                    .id :
                                                                '');

                                                }
                                                if (item
                                                    .service ==
                                                    'First') {
                                                    $(".list_" +
                                                        productId
                                                    )
                                                        .find(
                                                            '.' +
                                                            item
                                                                .service
                                                        )
                                                        .attr(
                                                            'disabled',
                                                            false
                                                        );
                                                    $(".list_" +
                                                        productId
                                                    )
                                                        .find(
                                                            ".shipping_price_calculated"
                                                        )
                                                        .html(
                                                            item
                                                                .rate
                                                        );
                                                    $(".list_" +
                                                        productId
                                                    )
                                                        .find(
                                                            ".shipping_price_calculated"
                                                        )
                                                        .attr(
                                                            'data-ship-price',
                                                            item
                                                                .rate
                                                        );

                                                    $(".list_" +
                                                        productId
                                                    )
                                                        .find(
                                                            ".shipping_price_calculated"
                                                        )
                                                        .attr(
                                                            'data-shipment-id',
                                                            item
                                                                ?.shipment_id ?
                                                                item
                                                                    .shipment_id :
                                                                '');
                                                    $(".list_" +
                                                        productId
                                                    )
                                                        .find(
                                                            ".shipping_price_calculated"
                                                        )
                                                        .attr(
                                                            'data-rate-id',
                                                            item
                                                                ?.id ?
                                                                item
                                                                    .id :
                                                                '');

                                                }
                                            })
                                            let elm = $("#list_" +
                                                productId).find(
                                                    'option:not(:disabled)'
                                                ).first();

                                            if (elm.length < 1) {
                                                shippingErrorResponse(
                                                    "There's some issue with " +
                                                    product_title +
                                                    ', Remove this product to continue',
                                                    true);
                                                return;
                                            }

                                            let shipment_id = $(
                                                "#list_" + productId
                                            ).find(
                                                '.shipping_price_calculated'
                                            ).attr(
                                                'data-shipment-id');
                                            let rate_id = $("#list_" +
                                                productId).find(
                                                    '.shipping_price_calculated'
                                                ).attr(
                                                    'data-rate-id');

                                            let services = elm.val()
                                                .split('_');
                                            if (services.length > 0) {
                                                let selected = services[
                                                    0];

                                                let selectedService =
                                                    data.find(obj => obj
                                                        .service ==
                                                        selected);
                                                let serviceRate =
                                                    selectedService ?
                                                        selectedService
                                                            .rate : null;

                                                if (serviceRate) {

                                                    $(that).parent()
                                                        .parent().find(
                                                            '.shipping_price_calculated'
                                                        ).html(
                                                            serviceRate
                                                        );
                                                    $(that).parent()
                                                        .parent().find(
                                                            '.shipping_price_calculated'
                                                        ).attr(
                                                            'data-ship-price',
                                                            serviceRate
                                                        );

                                                    stripeCal
                                                        .shipping +=
                                                        parseFloat(
                                                            serviceRate
                                                        );
                                                    stripeCal
                                                        .subTotal +=
                                                        parseFloat(
                                                            product_price
                                                        );
                                                    stripeCal.total =
                                                        stripeCal
                                                            .subTotal +
                                                        stripeCal
                                                            .shipping;

                                                    // update OrderData

                                                    orderData[storeId][
                                                        product_key
                                                    ]
                                                        .shipping_price =
                                                        serviceRate;
                                                    orderData[storeId][
                                                        product_key
                                                    ]
                                                        .shipping_method =
                                                        selectedService
                                                            .service

                                                    orderData[storeId][
                                                        product_key
                                                    ].ship_id =
                                                        shipment_id ?
                                                            shipment_id :
                                                            '';
                                                    orderData[storeId][
                                                        product_key
                                                    ].rate_id =
                                                        rate_id ?
                                                            rate_id : '';

                                                    if (key ==
                                                        totalItem) {
                                                        /*     $(".subtotal_product_stripe").html(stripeCal
                                             .subTotal.toFixed(2));
                                             */
                                                        $(".product_shipping_total")
                                                            .html(
                                                                stripeCal
                                                                    .shipping
                                                                    .toFixed(
                                                                        2));
                                                        $(".grandtotal_cal")
                                                            .html(
                                                                stripeCal
                                                                    .total
                                                                    .toFixed(
                                                                        2));
                                                        $("#total_amount_ship")
                                                            .val(
                                                                stripeCal
                                                                    .total *
                                                                100);
                                                        $('#orderData_stripe')
                                                            .val(JSON
                                                                .stringify(
                                                                    orderData
                                                                ));
                                                        started = false;
                                                        $('#loading')
                                                            .hide()

                                                    }

                                                }
                                            }
                                            let optionValue = elm.val()
                                            elm.parent().val(
                                                optionValue)

                                        }

                                    });
                                } else if (variant) {
                                    let data = null;


                                    // this product should be calculated

                                    $.ajax({
                                        url: '/calculate_service',
                                        type: "POST",
                                        dataType: "json",
                                        async: false,
                                        data: {
                                            value: value,
                                            storeId: storeId,
                                            origin_zip: origin_zip,
                                            shipping_option_id: shipping_option_id,
                                            weight: weight,
                                            product_length: product_length,
                                            product_width: product_width,
                                            product_height: product_height,
                                            product_quantity: product_quantity,
                                            product_price: product_price,
                                            product_title: product_title,
                                            shipment_data: shipment_data,
                                            _token: '{{ csrf_token() }}'
                                        },
                                        success: function (res) {

                                            $('#prcngbxiny')
                                                .removeClass(
                                                    'overlay-second');
                                            if (Array.isArray(res)) {

                                                data = res;
                                            } else {
                                                data = Object.values(
                                                    res)
                                            }
                                            if (Array.isArray(data) &&
                                                data.length < 1) {
                                                shippingErrorResponse(
                                                    'Post Code is not correct',
                                                    true);
                                                return;
                                            }

                                            if (data.error || data[0]
                                                ?.error) {

                                                shipping_error = data
                                                    .error;
                                                shippingErrorResponse(
                                                    shipping_error);
                                                started = false;
                                                startedCrypto = false;

                                                return;

                                            } else {
                                                $('.pymntslct').show();
                                            }

                                            $('.shipping_price_calculated')
                                                .html(data.rate);
                                            $('.shipping_price_calculated')
                                                .attr('data-ship-price',
                                                    data.rate);

                                            var shipTotalPrice = 0;
                                            var realTotalPrice = 0;
                                            $('.shipping_price_calculated')
                                                .each(function (key,
                                                    value) {
                                                    shipTotalPrice =
                                                        parseFloat(
                                                            shipTotalPrice
                                                        ) +
                                                        parseFloat(
                                                            $(this)
                                                                .attr(
                                                                    'data-ship-price'
                                                                ));
                                                    $(".product_shipping_total")
                                                        .html(
                                                            shipTotalPrice
                                                                .toFixed(
                                                                    2));
                                                });

                                            var vservice = $(".list_" +
                                                productId + '_' +
                                                variation_id).find(
                                                    '.' + data.service);
                                            data.forEach((item) => {

                                                if (item
                                                    .service ==
                                                    'ParcelSelect'
                                                ) {
                                                    $(".list_" +
                                                        productId +
                                                        '_' +
                                                        variation_id
                                                    )
                                                        .find(
                                                            '.' +
                                                            item
                                                                .service
                                                        )
                                                        .attr(
                                                            'disabled',
                                                            false
                                                        );
                                                    $(".list_" +
                                                        productId +
                                                        '_' +
                                                        variation_id
                                                    )
                                                        .find(
                                                            ".shipping_price_calculated"
                                                        )
                                                        .html(
                                                            item
                                                                .rate
                                                        );
                                                    $(".list_" +
                                                        productId +
                                                        '_' +
                                                        variation_id
                                                    )
                                                        .find(
                                                            ".shipping_price_calculated"
                                                        )
                                                        .attr(
                                                            'data-ship-price',
                                                            item
                                                                .rate
                                                        );

                                                    $(".list_" +
                                                        productId +
                                                        '_' +
                                                        variation_id
                                                    )
                                                        .find(
                                                            ".shipping_price_calculated"
                                                        )
                                                        .attr(
                                                            'data-shipment-id',
                                                            item
                                                                ?.shipment_id ?
                                                                item
                                                                    .shipment_id :
                                                                '');
                                                    $(".list_" +
                                                        productId +
                                                        '_' +
                                                        variation_id
                                                    )
                                                        .find(
                                                            ".shipping_price_calculated"
                                                        )
                                                        .attr(
                                                            'data-rate-id',
                                                            item
                                                                ?.id ?
                                                                item
                                                                    .id :
                                                                '');

                                                }
                                                if (item
                                                    .service ==
                                                    'Express') {

                                                    $(".list_" +
                                                        productId +
                                                        '_' +
                                                        variation_id
                                                    )
                                                        .find(
                                                            '.' +
                                                            item
                                                                .service
                                                        )
                                                        .attr(
                                                            'disabled',
                                                            false
                                                        );
                                                    $(".list_" +
                                                        productId +
                                                        '_' +
                                                        variation_id
                                                    )
                                                        .find(
                                                            ".shipping_price_calculated"
                                                        )
                                                        .html(
                                                            item
                                                                .rate
                                                        );
                                                    $(".list_" +
                                                        productId +
                                                        '_' +
                                                        variation_id
                                                    )
                                                        .find(
                                                            ".shipping_price_calculated"
                                                        )
                                                        .attr(
                                                            'data-ship-price',
                                                            item
                                                                .rate
                                                        );

                                                    $(".list_" +
                                                        productId +
                                                        '_' +
                                                        variation_id
                                                    )
                                                        .find(
                                                            ".shipping_price_calculated"
                                                        )
                                                        .attr(
                                                            'data-shipment-id',
                                                            item
                                                                ?.shipment_id ?
                                                                item
                                                                    .shipment_id :
                                                                '');
                                                    $(".list_" +
                                                        productId +
                                                        '_' +
                                                        variation_id
                                                    )
                                                        .find(
                                                            ".shipping_price_calculated"
                                                        )
                                                        .attr(
                                                            'data-rate-id',
                                                            item
                                                                ?.id ?
                                                                item
                                                                    .id :
                                                                '');

                                                }
                                                if (item
                                                    .service ==
                                                    'Priority'
                                                ) {

                                                    $(".list_" +
                                                        productId +
                                                        '_' +
                                                        variation_id
                                                    )
                                                        .find(
                                                            '.' +
                                                            item
                                                                .service
                                                        )
                                                        .attr(
                                                            'disabled',
                                                            false
                                                        );
                                                    $(".list_" +
                                                        productId +
                                                        '_' +
                                                        variation_id
                                                    )
                                                        .find(
                                                            ".shipping_price_calculated"
                                                        )
                                                        .html(
                                                            item
                                                                .rate
                                                        );
                                                    $(".list_" +
                                                        productId +
                                                        '_' +
                                                        variation_id
                                                    )
                                                        .find(
                                                            ".shipping_price_calculated"
                                                        )
                                                        .attr(
                                                            'data-ship-price',
                                                            item
                                                                .rate
                                                        );

                                                    $(".list_" +
                                                        productId +
                                                        '_' +
                                                        variation_id
                                                    )
                                                        .find(
                                                            ".shipping_price_calculated"
                                                        )
                                                        .attr(
                                                            'data-shipment-id',
                                                            item
                                                                ?.shipment_id ?
                                                                item
                                                                    .shipment_id :
                                                                '');
                                                    $(".list_" +
                                                        productId +
                                                        '_' +
                                                        variation_id
                                                    )
                                                        .find(
                                                            ".shipping_price_calculated"
                                                        )
                                                        .attr(
                                                            'data-rate-id',
                                                            item
                                                                ?.id ?
                                                                item
                                                                    .id :
                                                                '');

                                                }
                                                if (item
                                                    .service ==
                                                    'First') {
                                                    $(".list_" +
                                                        productId +
                                                        '_' +
                                                        variation_id
                                                    )
                                                        .find(
                                                            '.' +
                                                            item
                                                                .service
                                                        )
                                                        .attr(
                                                            'disabled',
                                                            false
                                                        );
                                                    $(".list_" +
                                                        productId +
                                                        '_' +
                                                        variation_id
                                                    )
                                                        .find(
                                                            ".shipping_price_calculated"
                                                        )
                                                        .html(
                                                            item
                                                                .rate
                                                        );
                                                    $(".list_" +
                                                        productId +
                                                        '_' +
                                                        variation_id
                                                    )
                                                        .find(
                                                            ".shipping_price_calculated"
                                                        )
                                                        .attr(
                                                            'data-ship-price',
                                                            item
                                                                .rate
                                                        );

                                                    $(".list_" +
                                                        productId +
                                                        '_' +
                                                        variation_id
                                                    )
                                                        .find(
                                                            ".shipping_price_calculated"
                                                        )
                                                        .attr(
                                                            'data-shipment-id',
                                                            item
                                                                ?.shipment_id ?
                                                                item
                                                                    .shipment_id :
                                                                '');
                                                    $(".list_" +
                                                        productId +
                                                        '_' +
                                                        variation_id
                                                    )
                                                        .find(
                                                            ".shipping_price_calculated"
                                                        )
                                                        .attr(
                                                            'data-rate-id',
                                                            item
                                                                ?.id ?
                                                                item
                                                                    .id :
                                                                '');

                                                }
                                            })
                                            let elm = $("#list_" +
                                                productId + '_' +
                                                variation_id).find(
                                                    'option:not(:disabled)'
                                                ).first();
                                            if (elm.length < 1) {
                                                shippingErrorResponse(
                                                    "There's some issue with " +
                                                    product_title +
                                                    ', Remove this product to continue',
                                                    true);
                                                return;
                                            }
                                            // find this list elm and get shipment id and rate id
                                            let shipment_id = $(
                                                "#list_" +
                                                productId + '_' +
                                                variation_id).find(
                                                    '.shipping_price_calculated'
                                                ).attr(
                                                    'data-shipment-id');
                                            let rate_id = $("#list_" +
                                                productId + '_' +
                                                variation_id).find(
                                                    '.shipping_price_calculated'
                                                ).attr(
                                                    'data-rate-id');

                                            let services = elm.val()
                                                .split('_');
                                            if (services.length > 0) {
                                                let selected = services[
                                                    0];

                                                let selectedService =
                                                    data.find(obj => obj
                                                        .service ==
                                                        selected);
                                                let serviceRate =
                                                    selectedService ?
                                                        selectedService
                                                            .rate : null;

                                                if (serviceRate) {


                                                    $(that).parent()
                                                        .parent().find(
                                                            '.shipping_price_calculated'
                                                        ).html(
                                                            serviceRate
                                                        );
                                                    $(that).parent()
                                                        .parent().find(
                                                            '.shipping_price_calculated'
                                                        ).attr(
                                                            'data-ship-price',
                                                            serviceRate
                                                        );
                                                    stripeCal
                                                        .shipping +=
                                                        parseFloat(
                                                            serviceRate
                                                        );
                                                    stripeCal
                                                        .subTotal +=
                                                        parseFloat(
                                                            product_price
                                                        );
                                                    stripeCal.total =
                                                        stripeCal
                                                            .subTotal +
                                                        stripeCal
                                                            .shipping;

                                                    orderData[storeId][
                                                        product_key
                                                    ]
                                                        .shipping_price =
                                                        serviceRate;
                                                    orderData[storeId][
                                                        product_key
                                                    ]
                                                        .shipping_method =
                                                        selectedService
                                                            .service;

                                                    orderData[storeId][
                                                        product_key
                                                    ].ship_id =
                                                        shipment_id ?
                                                            shipment_id :
                                                            '';
                                                    orderData[storeId][
                                                        product_key
                                                    ].rate_id =
                                                        rate_id ?
                                                            rate_id : '';

                                                    if (key ==
                                                        totalItem) {


                                                        /*   $(".subtotal_product_stripe")
                                                               .html(
                                                                   stripeCal
                                                                   .subTotal
                                                                   .toFixed(
                                                                       2));
                                                                       */
                                                        $(".product_shipping_total")
                                                            .html(
                                                                stripeCal
                                                                    .shipping
                                                                    .toFixed(
                                                                        2));
                                                        $(".grandtotal_cal")
                                                            .html(
                                                                stripeCal
                                                                    .total
                                                                    .toFixed(
                                                                        2));
                                                        $("#total_amount_ship")
                                                            .val(
                                                                stripeCal
                                                                    .total *
                                                                100);
                                                        $('#orderData_stripe')
                                                            .val(JSON
                                                                .stringify(
                                                                    orderData
                                                                ));
                                                        started = false;
                                                        $('#loading')
                                                            .hide()
                                                    }

                                                    // $(".grandtotal_cal").html(stripeCal.total);
                                                }
                                            }
                                            // debugger
                                            let optionValue = elm.val()
                                            elm.parent().val(
                                                optionValue)


                                        }

                                    });

                                } else if (virtual) {

                                    stripeCal.subTotal += parseFloat(product_price);
                                    stripeCal.total = stripeCal.subTotal + stripeCal
                                        .shipping;

                                    if (key == totalItem) {


                                        /*     $(".subtotal_product_stripe").html(stripeCal
                                           .subTotal.toFixed(2));
                                           */
                                        $(".product_shipping_total").html(stripeCal
                                            .shipping.toFixed(2));
                                        $(".grandtotal_cal").html(stripeCal.total
                                            .toFixed(2));
                                        $("#total_amount_ship").val(stripeCal
                                            .total * 100);
                                        $('#orderData_stripe').val(JSON.stringify(
                                            orderData));
                                        started = false;
                                        $('#loading').hide();
                                        $('#prcngbxiny').removeClass(
                                            'overlay-second');

                                    }
                                }

                            });
                            payWithGoogle();
                        }, 200);
                    }

                    if (!startedCrypto && country && states && street && city && firstname.trim() !=
                        '' && lastname.trim() != '' && UserEmail.trim() != '' && zipcode.length >= 5
                    ) {
                        return;

                        $('#loading').show()

                        startedCrypto = true;
                        let prevProductIds = [];

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

                        var prevValue = null;
                        let prevProductId = null;
                        let prevVariationId = null;
                        let totalItem = $('.get_data_calulate_ship_crypto').length - 1;
                        setTimeout(() => {
                            $('.get_data_calulate_ship_crypto').each(function (key, value) {
                                var shipping_error;
                                var that = $(this);
                                var val = $(this).val().split("_");
                                var value = val[0];
                                var storeId = val[1];
                                var productId = val[2];
                                var weight = val[3];
                                var origin_zip = val[4];
                                var shipping_option_id = val[5];
                                var product_quantity = val[6];
                                var product_price = val[7];
                                var product_title = val[8];
                                var product_key = val[9];
                                var product_length = val[10];
                                var product_width = val[11];
                                var product_height = val[12];
                                let product_type = val[13];
                                let variation_id = val[14]
                                let is_domestic_free = val[15]
                                let flat_shipping_rate = val[16]
                                let product_id = val[17]

                                var obj = {
                                    value: value,
                                    storeId: storeId,
                                    productId: productId,
                                    weight: weight,
                                    origin_zip: origin_zip,
                                    shipping_option_id: shipping_option_id,
                                    product_length: product_length,
                                    product_width: product_width,
                                    product_height: product_height,
                                    product_quantity: product_height,
                                    product_price: product_price,
                                    product_title: product_title,
                                    product_key: product_key,

                                };
                                let simple = false;
                                let variant = false;
                                let virtual = false;
                                console.log("obj", obj);

                                if (is_domestic_free > 0) {

                                    cryptoCal.subTotal += parseFloat(product_price);
                                    cryptoCal.total = cryptoCal.subTotal + cryptoCal
                                        .shipping;

                                    if (key == totalItem) {

                                        $(".subtotal_product_stripe_crypto").html(
                                            cryptoCal.subTotal.toFixed(2));
                                        $(".product_shipping_total_crypto").html(
                                            cryptoCal.shipping.toFixed(2));
                                        $(".grandtotal_cal_crypto").html(cryptoCal
                                            .total.toFixed(2));
                                        $('#orderData').val(JSON.stringify(
                                            orderDataCrypto));
                                        setCryptoPayable(cryptoCal.total)
                                        startedCrypto = false;
                                        $('#loading').hide()

                                    }
                                    console.log('orderDataaa', orderDataCrypto)
                                    return;
                                } else if (flat_shipping_rate > 0) {
                                    // get rates here of flatshipping and add in shipping

                                    cryptoCal.shipping += parseFloat(
                                        flat_shipping_rate);
                                    cryptoCal.subTotal += parseFloat(product_price);
                                    cryptoCal.total = cryptoCal.subTotal + cryptoCal
                                        .shipping;

                                    orderDataCrypto[storeId][product_key]
                                        .shipping_price = flat_shipping_rate;

                                    if (key == totalItem) {
                                        $(".subtotal_product_stripe_crypto").html(
                                            cryptoCal.subTotal.toFixed(2));
                                        $(".product_shipping_total_crypto").html(
                                            cryptoCal.shipping.toFixed(2));
                                        $(".grandtotal_cal_crypto").html(cryptoCal
                                            .total.toFixed(2));
                                        $('#orderData').val(JSON.stringify(
                                            orderDataCrypto));
                                        setCryptoPayable(cryptoCal.total)
                                        startedCrypto = false;
                                        $('#loading').hide()

                                    }

                                    return;
                                }

                                switch (product_type) {
                                    case 'simple':
                                        simple = true
                                        break;
                                    case 'variant':
                                        variant = true
                                        break;
                                    case 'virtual':
                                        virtual = true
                                        break;
                                    default:
                                        break;
                                }
                                if (simple) {

                                    let data = null;
                                    $.ajax({
                                        url: '/calculate_service',
                                        type: "POST",
                                        async: false,
                                        dataType: "json",
                                        data: {
                                            value: value,
                                            storeId: storeId,
                                            origin_zip: origin_zip,
                                            shipping_option_id: shipping_option_id,
                                            weight: weight,
                                            product_length: product_length,
                                            product_width: product_width,
                                            product_height: product_height,
                                            product_quantity: product_quantity,
                                            product_price: product_price,
                                            product_title: product_title,
                                            shipment_data: shipment_data,
                                            _token: '{{ csrf_token() }}'
                                        },
                                        success: function (res) {
                                            if (Array.isArray(res)) {

                                                data = res;
                                            } else {
                                                data = Object.values(
                                                    res)
                                            }
                                            if (Array.isArray(data) &&
                                                data.length < 1) {
                                                shippingErrorResponse(
                                                    'Post Code is not correct',
                                                    true);
                                                return;
                                            }

                                            if (data.error || data[0]
                                                ?.error) {
                                                shipping_error = data
                                                    .error;
                                                shippingErrorResponse(
                                                    shipping_error);
                                                started = false;
                                                startedCrypto = false;
                                                return;
                                            } else {
                                                $('.pymntslct').show();
                                            }

                                            var vservice = $(
                                                ".list_crypto_" +
                                                productId).find(
                                                    '.' + data.service);
                                            data.forEach((item) => {

                                                if (item
                                                    .service ==
                                                    'ParcelSelect'
                                                ) {
                                                    $(".list_crypto_" +
                                                        productId
                                                    )
                                                        .find(
                                                            '.' +
                                                            item
                                                                .service
                                                        )
                                                        .attr(
                                                            'disabled',
                                                            false
                                                        );
                                                    $(".list_crypto_" +
                                                        productId
                                                    )
                                                        .find(
                                                            ".shipping_price_calculated_crypto"
                                                        )
                                                        .html(
                                                            item
                                                                .rate
                                                        );
                                                    $(".list_crypto_" +
                                                        productId
                                                    )
                                                        .find(
                                                            ".shipping_price_calculated_crypto"
                                                        )
                                                        .attr(
                                                            'data-ship-price',
                                                            item
                                                                .rate
                                                        );

                                                    $(".list_crypto_" +
                                                        productId
                                                    )
                                                        .find(
                                                            ".shipping_price_calculated_crypto"
                                                        )
                                                        .attr(
                                                            'data-shipment-id',
                                                            item
                                                                ?.shipment_id ?
                                                                item
                                                                    .shipment_id :
                                                                '');
                                                    $(".list_crypto_" +
                                                        productId
                                                    )
                                                        .find(
                                                            ".shipping_price_calculated_crypto"
                                                        )
                                                        .attr(
                                                            'data-rate-id',
                                                            item
                                                                ?.id ?
                                                                item
                                                                    .id :
                                                                '');


                                                }
                                                if (item
                                                    .service ==
                                                    'Express') {

                                                    $(".list_crypto_" +
                                                        productId
                                                    )
                                                        .find(
                                                            '.' +
                                                            item
                                                                .service
                                                        )
                                                        .attr(
                                                            'disabled',
                                                            false
                                                        );
                                                    $(".list_crypto_" +
                                                        productId
                                                    )
                                                        .find(
                                                            ".shipping_price_calculated_crypto"
                                                        )
                                                        .html(
                                                            item
                                                                .rate
                                                        );
                                                    $(".list_crypto_" +
                                                        productId
                                                    )
                                                        .find(
                                                            ".shipping_price_calculated_crypto"
                                                        )
                                                        .attr(
                                                            'data-ship-price',
                                                            item
                                                                .rate
                                                        );

                                                    $(".list_crypto_" +
                                                        productId
                                                    )
                                                        .find(
                                                            ".shipping_price_calculated_crypto"
                                                        )
                                                        .attr(
                                                            'data-shipment-id',
                                                            item
                                                                ?.shipment_id ?
                                                                item
                                                                    .shipment_id :
                                                                '');
                                                    $(".list_crypto_" +
                                                        productId
                                                    )
                                                        .find(
                                                            ".shipping_price_calculated_crypto"
                                                        )
                                                        .attr(
                                                            'data-rate-id',
                                                            item
                                                                ?.id ?
                                                                item
                                                                    .id :
                                                                '');


                                                }
                                                if (item
                                                    .service ==
                                                    'Priority'
                                                ) {

                                                    $(".list_crypto_" +
                                                        productId
                                                    )
                                                        .find(
                                                            '.' +
                                                            item
                                                                .service
                                                        )
                                                        .attr(
                                                            'disabled',
                                                            false
                                                        );
                                                    $(".list_crypto_" +
                                                        productId
                                                    )
                                                        .find(
                                                            ".shipping_price_calculated_crypto"
                                                        )
                                                        .html(
                                                            item
                                                                .rate
                                                        );
                                                    $(".list_crypto_" +
                                                        productId
                                                    )
                                                        .find(
                                                            ".shipping_price_calculated_crypto"
                                                        )
                                                        .attr(
                                                            'data-ship-price',
                                                            item
                                                                .rate
                                                        );

                                                    $(".list_crypto_" +
                                                        productId
                                                    )
                                                        .find(
                                                            ".shipping_price_calculated_crypto"
                                                        )
                                                        .attr(
                                                            'data-shipment-id',
                                                            item
                                                                ?.shipment_id ?
                                                                item
                                                                    .shipment_id :
                                                                '');
                                                    $(".list_crypto_" +
                                                        productId
                                                    )
                                                        .find(
                                                            ".shipping_price_calculated_crypto"
                                                        )
                                                        .attr(
                                                            'data-rate-id',
                                                            item
                                                                ?.id ?
                                                                item
                                                                    .id :
                                                                '');


                                                }
                                                if (item
                                                    .service ==
                                                    'First') {
                                                    $(".list_crypto_" +
                                                        productId
                                                    )
                                                        .find(
                                                            '.' +
                                                            item
                                                                .service
                                                        )
                                                        .attr(
                                                            'disabled',
                                                            false
                                                        );
                                                    $(".list_crypto_" +
                                                        productId
                                                    )
                                                        .find(
                                                            ".shipping_price_calculated_crypto"
                                                        )
                                                        .html(
                                                            item
                                                                .rate
                                                        );
                                                    $(".list_crypto_" +
                                                        productId
                                                    )
                                                        .find(
                                                            ".shipping_price_calculated_crypto"
                                                        )
                                                        .attr(
                                                            'data-ship-price',
                                                            item
                                                                .rate
                                                        );

                                                    $(".list_crypto_" +
                                                        productId
                                                    )
                                                        .find(
                                                            ".shipping_price_calculated_crypto"
                                                        )
                                                        .attr(
                                                            'data-shipment-id',
                                                            item
                                                                ?.shipment_id ?
                                                                item
                                                                    .shipment_id :
                                                                '');
                                                    $(".list_crypto_" +
                                                        productId
                                                    )
                                                        .find(
                                                            ".shipping_price_calculated_crypto"
                                                        )
                                                        .attr(
                                                            'data-rate-id',
                                                            item
                                                                ?.id ?
                                                                item
                                                                    .id :
                                                                '');

                                                }
                                            })
                                            let elm = $(
                                                "#list_crypto_" +
                                                productId).find(
                                                    'option:not(:disabled)'
                                                ).first();
                                            if (elm.length < 1) {
                                                shippingErrorResponse(
                                                    "There's some issue with " +
                                                    product_title +
                                                    ', Remove this product to continue',
                                                    true);
                                                return;
                                            }

                                            let shipment_id = $(
                                                "#list_crypto_" +
                                                productId).find(
                                                    '.shipping_price_calculated_crypto'
                                                ).attr(
                                                    'data-shipment-id');
                                            let rate_id = $(
                                                "#list_crypto_" +
                                                productId).find(
                                                    '.shipping_price_calculated_crypto'
                                                ).attr(
                                                    'data-rate-id');

                                            let services = elm.val()
                                                .split('_');
                                            if (services.length > 0) {
                                                let selected = services[
                                                    0];

                                                let selectedService =
                                                    data.find(obj => obj
                                                        .service ==
                                                        selected);
                                                let serviceRate =
                                                    selectedService ?
                                                        selectedService
                                                            .rate : null;

                                                if (serviceRate) {

                                                    $(that).parent()
                                                        .parent().find(
                                                            '.shipping_price_calculated_crypto'
                                                        ).html(
                                                            serviceRate
                                                        );
                                                    $(that).parent()
                                                        .parent().find(
                                                            '.shipping_price_calculated_crypto'
                                                        ).attr(
                                                            'data-ship-price',
                                                            serviceRate
                                                        );
                                                    cryptoCal
                                                        .shipping +=
                                                        parseFloat(
                                                            serviceRate
                                                        );
                                                    cryptoCal
                                                        .subTotal +=
                                                        parseFloat(
                                                            product_price
                                                        );
                                                    cryptoCal.total =
                                                        cryptoCal
                                                            .subTotal +
                                                        cryptoCal
                                                            .shipping;

                                                    // update OrderData

                                                    orderDataCrypto[
                                                        storeId][
                                                        product_key
                                                    ]
                                                        .shipping_price =
                                                        serviceRate;
                                                    orderDataCrypto[
                                                        storeId][
                                                        product_key
                                                    ]
                                                        .shipping_method =
                                                        selectedService
                                                            .service

                                                    orderDataCrypto[
                                                        storeId][
                                                        product_key
                                                    ].ship_id =
                                                        shipment_id ?
                                                            shipment_id :
                                                            '';
                                                    orderDataCrypto[
                                                        storeId][
                                                        product_key
                                                    ].rate_id =
                                                        rate_id ?
                                                            rate_id : '';


                                                    if (key ==
                                                        totalItem) {
                                                        $(".subtotal_product_stripe_crypto")
                                                            .html(
                                                                cryptoCal
                                                                    .subTotal
                                                                    .toFixed(
                                                                        2));
                                                        $(".product_shipping_total_crypto")
                                                            .html(
                                                                cryptoCal
                                                                    .shipping
                                                                    .toFixed(
                                                                        2));
                                                        $(".grandtotal_cal_crypto")
                                                            .html(
                                                                cryptoCal
                                                                    .total
                                                                    .toFixed(
                                                                        2));
                                                        $('#orderData')
                                                            .val(JSON
                                                                .stringify(
                                                                    orderDataCrypto
                                                                ));
                                                        setCryptoPayable
                                                            (cryptoCal
                                                                .total)
                                                        startedCrypto =
                                                            false;
                                                        $('#loading')
                                                            .hide()

                                                    }

                                                    // $(".grandtotal_cal").html(cryptoCal.total);

                                                }
                                            }
                                            let optionValue = elm.val()
                                            elm.parent().val(
                                                optionValue)

                                        }

                                    });
                                } else if (variant) {
                                    let data = null;

                                    // this product should be calculated
                                    $.ajax({
                                        url: '/calculate_service',
                                        type: "POST",
                                        dataType: "json",
                                        async: false,
                                        data: {
                                            value: value,
                                            storeId: storeId,
                                            origin_zip: origin_zip,
                                            shipping_option_id: shipping_option_id,
                                            weight: weight,
                                            product_length: product_length,
                                            product_width: product_width,
                                            product_height: product_height,
                                            product_quantity: product_quantity,
                                            product_price: product_price,
                                            product_title: product_title,
                                            shipment_data: shipment_data,
                                            _token: '{{ csrf_token() }}'
                                        },
                                        success: function (res) {
                                            if (Array.isArray(res)) {
                                                data = res;
                                            } else {
                                                data = Object.values(
                                                    res)
                                            }
                                            if (Array.isArray(data) &&
                                                data.length < 1) {
                                                shippingErrorResponse(
                                                    'Post Code is not correct',
                                                    true);
                                                return;
                                            }

                                            if (data.error || data[0]
                                                ?.error) {
                                                shipping_error = data
                                                    .error;
                                                shippingErrorResponse(
                                                    shipping_error);
                                                started = false;
                                                startedCrypto = false;
                                                return;

                                            } else {
                                                $('.pymntslct').show();
                                            }

                                            var vservice = $(
                                                ".list_crypto_" +
                                                productId + '_' +
                                                variation_id).find(
                                                    '.' + data.service);
                                            data.forEach((item) => {

                                                orderDataCrypto[
                                                    storeId]
                                                [
                                                    product_key
                                                ]
                                                    .shipping_price =
                                                    item.rate;
                                                orderDataCrypto[
                                                    storeId]
                                                [
                                                    product_key
                                                ]
                                                    .shipping_method =
                                                    item
                                                        .service;

                                                orderDataCrypto[
                                                    storeId]
                                                [
                                                    product_key
                                                ]
                                                    .ship_id =
                                                    item
                                                        .ship_id;
                                                orderDataCrypto[
                                                    storeId]
                                                [
                                                    product_key
                                                ]
                                                    .rate_id =
                                                    item
                                                        .rate_id;

                                                if (item
                                                    .service ==
                                                    'ParcelSelect'
                                                ) {
                                                    $(".list_crypto_" +
                                                        productId +
                                                        '_' +
                                                        variation_id
                                                    )
                                                        .find(
                                                            '.' +
                                                            item
                                                                .service
                                                        )
                                                        .attr(
                                                            'disabled',
                                                            false
                                                        );
                                                    $(".list_crypto_" +
                                                        productId +
                                                        '_' +
                                                        variation_id
                                                    )
                                                        .find(
                                                            ".shipping_price_calculated_crypto"
                                                        )
                                                        .html(
                                                            item
                                                                .rate
                                                        );
                                                    $(".list_crypto_" +
                                                        productId +
                                                        '_' +
                                                        variation_id
                                                    )
                                                        .find(
                                                            ".shipping_price_calculated_crypto"
                                                        )
                                                        .attr(
                                                            'data-ship-price',
                                                            item
                                                                .rate
                                                        );

                                                    $(".list_crypto_" +
                                                        productId +
                                                        '_' +
                                                        variation_id
                                                    )
                                                        .find(
                                                            ".shipping_price_calculated_crypto"
                                                        )
                                                        .attr(
                                                            'data-shipment-id',
                                                            item
                                                                ?.shipment_id ?
                                                                item
                                                                    .shipment_id :
                                                                '');
                                                    $(".list_crypto_" +
                                                        productId +
                                                        '_' +
                                                        variation_id
                                                    )
                                                        .find(
                                                            ".shipping_price_calculated_crypto"
                                                        )
                                                        .attr(
                                                            'data-rate-id',
                                                            item
                                                                ?.id ?
                                                                item
                                                                    .id :
                                                                '');

                                                }
                                                if (item
                                                    .service ==
                                                    'Express') {

                                                    $(".list_crypto_" +
                                                        productId +
                                                        '_' +
                                                        variation_id
                                                    )
                                                        .find(
                                                            '.' +
                                                            item
                                                                .service
                                                        )
                                                        .attr(
                                                            'disabled',
                                                            false
                                                        );
                                                    $(".list_crypto_" +
                                                        productId +
                                                        '_' +
                                                        variation_id
                                                    )
                                                        .find(
                                                            ".shipping_price_calculated_crypto"
                                                        )
                                                        .html(
                                                            item
                                                                .rate
                                                        );
                                                    $(".list_crypto_" +
                                                        productId +
                                                        '_' +
                                                        variation_id
                                                    )
                                                        .find(
                                                            ".shipping_price_calculated_crypto"
                                                        )
                                                        .attr(
                                                            'data-ship-price',
                                                            item
                                                                .rate
                                                        );

                                                    $(".list_crypto_" +
                                                        productId +
                                                        '_' +
                                                        variation_id
                                                    )
                                                        .find(
                                                            ".shipping_price_calculated_crypto"
                                                        )
                                                        .attr(
                                                            'data-shipment-id',
                                                            item
                                                                ?.shipment_id ?
                                                                item
                                                                    .shipment_id :
                                                                '');
                                                    $(".list_crypto_" +
                                                        productId +
                                                        '_' +
                                                        variation_id
                                                    )
                                                        .find(
                                                            ".shipping_price_calculated_crypto"
                                                        )
                                                        .attr(
                                                            'data-rate-id',
                                                            item
                                                                ?.id ?
                                                                item
                                                                    .id :
                                                                '');

                                                }
                                                if (item
                                                    .service ==
                                                    'Priority'
                                                ) {

                                                    $(".list_crypto_" +
                                                        productId +
                                                        '_' +
                                                        variation_id
                                                    )
                                                        .find(
                                                            '.' +
                                                            item
                                                                .service
                                                        )
                                                        .attr(
                                                            'disabled',
                                                            false
                                                        );
                                                    $(".list_crypto_" +
                                                        productId +
                                                        '_' +
                                                        variation_id
                                                    )
                                                        .find(
                                                            ".shipping_price_calculated_crypto"
                                                        )
                                                        .html(
                                                            item
                                                                .rate
                                                        );
                                                    $(".list_crypto_" +
                                                        productId +
                                                        '_' +
                                                        variation_id
                                                    )
                                                        .find(
                                                            ".shipping_price_calculated_crypto"
                                                        )
                                                        .attr(
                                                            'data-ship-price',
                                                            item
                                                                .rate
                                                        );

                                                    $(".list_crypto_" +
                                                        productId +
                                                        '_' +
                                                        variation_id
                                                    )
                                                        .find(
                                                            ".shipping_price_calculated_crypto"
                                                        )
                                                        .attr(
                                                            'data-shipment-id',
                                                            item
                                                                ?.shipment_id ?
                                                                item
                                                                    .shipment_id :
                                                                '');
                                                    $(".list_crypto_" +
                                                        productId +
                                                        '_' +
                                                        variation_id
                                                    )
                                                        .find(
                                                            ".shipping_price_calculated_crypto"
                                                        )
                                                        .attr(
                                                            'data-rate-id',
                                                            item
                                                                ?.id ?
                                                                item
                                                                    .id :
                                                                '');

                                                }
                                                if (item
                                                    .service ==
                                                    'First') {
                                                    $(".list_crypto_" +
                                                        productId +
                                                        '_' +
                                                        variation_id
                                                    )
                                                        .find(
                                                            '.' +
                                                            item
                                                                .service
                                                        )
                                                        .attr(
                                                            'disabled',
                                                            false
                                                        );
                                                    $(".list_crypto_" +
                                                        productId +
                                                        '_' +
                                                        variation_id
                                                    )
                                                        .find(
                                                            ".shipping_price_calculated_crypto"
                                                        )
                                                        .html(
                                                            item
                                                                .rate
                                                        );
                                                    $(".list_crypto_" +
                                                        productId +
                                                        '_' +
                                                        variation_id
                                                    )
                                                        .find(
                                                            ".shipping_price_calculated_crypto"
                                                        )
                                                        .attr(
                                                            'data-ship-price',
                                                            item
                                                                .rate
                                                        );

                                                    $(".list_crypto_" +
                                                        productId +
                                                        '_' +
                                                        variation_id
                                                    )
                                                        .find(
                                                            ".shipping_price_calculated_crypto"
                                                        )
                                                        .attr(
                                                            'data-shipment-id',
                                                            item
                                                                ?.shipment_id ?
                                                                item
                                                                    .shipment_id :
                                                                '');
                                                    $(".list_crypto_" +
                                                        productId +
                                                        '_' +
                                                        variation_id
                                                    )
                                                        .find(
                                                            ".shipping_price_calculated_crypto"
                                                        )
                                                        .attr(
                                                            'data-rate-id',
                                                            item
                                                                ?.id ?
                                                                item
                                                                    .id :
                                                                '');

                                                }
                                            })
                                            let elm = $(
                                                "#list_crypto_" +
                                                productId + '_' +
                                                variation_id).find(
                                                    'option:not(:disabled)'
                                                ).first();
                                            if (elm.length < 1) {
                                                shippingErrorResponse(
                                                    "There's some issue with " +
                                                    product_title +
                                                    ', Remove this product to continue',
                                                    true);
                                                return;
                                            }
                                            let shipment_id = $(
                                                "#list_crypto_" +
                                                productId + '_' +
                                                variation_id).find(
                                                    '.shipping_price_calculated_crypto'
                                                ).attr(
                                                    'data-shipment-id');
                                            let rate_id = $(
                                                "#list_crypto_" +
                                                productId + '_' +
                                                variation_id).find(
                                                    '.shipping_price_calculated_crypto'
                                                ).attr(
                                                    'data-rate-id');

                                            let services = elm.val()
                                                .split('_');
                                            if (services.length > 0) {
                                                let selected = services[
                                                    0];

                                                let selectedService =
                                                    data.find(obj => obj
                                                        .service ==
                                                        selected);
                                                let serviceRate =
                                                    selectedService ?
                                                        selectedService
                                                            .rate : null;

                                                if (serviceRate) {


                                                    $(that).parent()
                                                        .parent().find(
                                                            '.shipping_price_calculated_crypto'
                                                        ).html(
                                                            serviceRate
                                                        );
                                                    $(that).parent()
                                                        .parent().find(
                                                            '.shipping_price_calculated_crypto'
                                                        ).attr(
                                                            'data-ship-price',
                                                            serviceRate
                                                        );
                                                    cryptoCal
                                                        .shipping +=
                                                        parseFloat(
                                                            serviceRate
                                                        );
                                                    cryptoCal
                                                        .subTotal +=
                                                        parseFloat(
                                                            product_price
                                                        );
                                                    cryptoCal.total =
                                                        cryptoCal
                                                            .subTotal +
                                                        cryptoCal
                                                            .shipping;

                                                    orderDataCrypto[
                                                        storeId][
                                                        product_key
                                                    ]
                                                        .shipping_price =
                                                        serviceRate;
                                                    orderDataCrypto[
                                                        storeId][
                                                        product_key
                                                    ]
                                                        .shipping_method =
                                                        selectedService
                                                            .service;

                                                    orderDataCrypto[
                                                        storeId][
                                                        product_key
                                                    ].ship_id =
                                                        shipment_id ?
                                                            shipment_id :
                                                            '';
                                                    orderDataCrypto[
                                                        storeId][
                                                        product_key
                                                    ].rate_id =
                                                        rate_id ?
                                                            rate_id : '';

                                                    if (key ==
                                                        totalItem) {
                                                        $(".subtotal_product_stripe_crypto")
                                                            .html(
                                                                cryptoCal
                                                                    .subTotal
                                                                    .toFixed(
                                                                        2));
                                                        $(".product_shipping_total_crypto")
                                                            .html(
                                                                cryptoCal
                                                                    .shipping
                                                                    .toFixed(
                                                                        2));
                                                        $(".grandtotal_cal_crypto")
                                                            .html(
                                                                cryptoCal
                                                                    .total
                                                                    .toFixed(
                                                                        2));
                                                        $('#orderData')
                                                            .val(JSON
                                                                .stringify(
                                                                    orderDataCrypto
                                                                ));
                                                        setCryptoPayable
                                                            (cryptoCal
                                                                .total)
                                                        startedCrypto =
                                                            false;
                                                        $('#loading')
                                                            .hide()

                                                    }

                                                    // $(".grandtotal_cal").html(cryptoCal.total);
                                                }
                                            }

                                            let optionValue = elm.val()
                                            elm.parent().val(
                                                optionValue)

                                        }

                                    });

                                } else if (virtual) {

                                    cryptoCal.subTotal += parseFloat(product_price);
                                    cryptoCal.total = cryptoCal.subTotal + cryptoCal
                                        .shipping;

                                    if (key == totalItem) {
                                        $(".subtotal_product_stripe_crypto").html(
                                            cryptoCal.subTotal.toFixed(2));
                                        $(".product_shipping_total_crypto").html(
                                            cryptoCal.shipping.toFixed(2));
                                        $(".grandtotal_cal_crypto").html(cryptoCal
                                            .total.toFixed(2));
                                        $('#orderData').val(JSON.stringify(
                                            orderDataCrypto));
                                        setCryptoPayable(cryptoCal.total)
                                        startedCrypto = false;
                                        $('#loading').hide()


                                    }
                                }

                            });
                        }, 200);

                    }
                }, 5000)


            });
    });


    function shippingErrorResponse(response, reload = true) {

        // swal("If Any Query Please Contact Store Owner!", response, "error");
        swal({
            title: "If you have any query please contact Store Owner!",
            text: response,
            type: "warning",
            allowOutsideClick: false,
            showCancelButton: false,
            confirmButtonColor: "#DD6B55",
            confirmButtonText: "OK",
            closeOnConfirm: true,
            closeOnCancel: false,
            cancelButtonText: "No, cancel plx!",
        },
            function (isConfirm) {
                if (isConfirm && reload) {
                    window.location.reload()
                } else {
                    // swal("Cancelled", "Your imaginary file is safe :)", "error");
                }
            });
        //     Swal.fire({
        //   position: 'center',
        //   icon: 'error',
        //   title: "Here's something wrong!",
        //   text: 'If you have any query please contact store owner!',
        // //   timer: 3000,
        //   showConfirmButton: true,

        // }).then((resp)=>{
        //     window.location.reload()
        // })
        // $('.chkout').addClass('disable-page');
        var selectElement = $('#shipping_service');

        // reset select element
        // selectElement.prop('selectedIndex', -1);
        // $('.pymntslct').hide();
        // $("#city_name").val('');
        $("#zipcode").val('');
        $('#loading').hide()

    }
    // login validaton start


    jQuery.validator.addMethod("customPasscode", function (value, element) {
        // Check if user_restrict is true and passcode matches the environment variable
        if (user_restriction.passcodeCheck == '1' && value === user_passcode.passcode) {
            return true;
        }
        return false;
    }, "Please enter a valid Passcode.");

    let storeOwners = <?php echo json_encode($store_owners); ?>;
    $("#shispping_form").validate({
        errorElement: "span",
        rules: {
            useremail: {
                required: true,
                validEmail: true,
                storeOwnerCheck: storeOwners
            },
            first_name: {
                required: true
            },
            last_name: {
                required: true
            },
            country: {
                required: true,
                min: 1
            },
            // states_countries: {required: true},
            street_address: {
                required: true
            },
            city_name: {
                required: true
            },
            zipcode: {
                required: true
            },
            passcode: {
                customPasscode: true
            },

        },
        messages: {
            useremail: {
                required: "Email is required."
            },
            first_name: {
                required: "First name is required."
            },
            last_name: {
                required: "Last name is required."
            },
            country: {
                required: "Country is required.",
                min: "Please select Country"
            },
            // states_countries: {required: "States is required.",},
            street_address: {
                required: "Address is required."
            },
            city_name: {
                required: "City is required."
            },
            zipcode: {
                required: "Zipcode is required."
            },
            passcode: {
                customPasscode: "Please enter a valid Passcode."
            },

        },
        errorPlacement: function (error, element) {
            error.appendTo(element.closest('.form-group'));
        },
        submitHandler: function (form) {


            stripe.createToken(cardNumber).then(function (result) {

                if (result.error) {
                    // Inform the user if there was an error
                    disable_button = false;
                    enableAcceptRefundButton();

                    function enableAcceptRefundButton() {
                        $('.place-order-btn').prop('disabled', false);
                    }
                    var errorElement = document.getElementById('card-errors');
                    errorElement.classList.add('error');
                    errorElement.textContent = result.error.message;
                    setTimeout(function () {
                            errorElement.textContent = '';
                        }, 3000);
                } else {

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

                    if (!country && !states && !street && !city && !zipcode && !fullName && !
                        UserEmail) {
                        alert('Please fill in all Shipping Form required fields');
                        return false;
                    } else {
                        var data2 = '&full_name=' + fullName + '&s_country=' + country + '&city=' +
                            city + '&states_countries=' + states + '&s_street=' + street +
                            '&s_post_code=' + zipcode;

                        if (UserEmail) {
                            // input field is not empty, set the value 
                            $("#stripe_email").val(UserEmail);
                            $("#crypto_email").val(UserEmail);
                        }

                        jQuery('#shipping_data_stripe').val(data2);
                    }
                    $('#loading').show()

                    $.ajax({
                        url: $('#payment-form').attr('action'),
                        type: $('#payment-form').attr('method'),
                        data: $('#payment-form').serialize(),
                        success: function (response) {

                            if (response.status == false) {
                                swal("Error", response.message, "error");
                                $('#loading').hide();
                                $('.place-order-btn').prop('disabled', false);
                            } else {
                            

                                dataLayer.push({
                                    ecommerce: null
                                });
                                dataLayer.push({
                                    event: "purchase",
                                    ecommerce: {
                                        transaction_id: response.transactionId,
                                        value: response.total_amount,
                                        tax: 0,
                                        shipping: response.total_shipping,
                                        currency: "USD",
                                        items: response.data
                                    }
                                });
                                $.ajax({
                                    url: '/set-checkout-session', // Endpoint to set session
                                    type: 'POST',
                                    success: function () {
                                        // If the session is set successfully, redirect
                                        window.location.href = '/order-confirmation';
                                    }
                                });
                            }

                            },
                            error: function(xhr, status, error) {
                                $('#loading').hide();
                            }
                        });
                        // $('#payment-form').submit();

                }
            });
        }

    });

    if (user_restriction.passcodeCheck == '') {
        $("#passcode").rules("remove");
    }
    $('#place-order-btn').on('click', function () {

        var firstname = $('#inputFirstNameShipping').val();
        var lastname = $('#inputLastNameShipping').val();
        var fullName = firstname + ' ' + lastname;
        var UserEmail = $('#useremail').val();
        var country = $('#s_country').val();
        var states = $('#states_countries').val();
        var street = $('#street_address').val();
        var city = $('#city_name').val();
        var zipcode = $('#zipcode').val();
        $('#shispping_form').submit();
        var disable_button = true;

        // Define the function outside the conditional block
        function disableAcceptRefundButton() {

            if (!country || !states || !street || !city || !zipcode || !fullName || !UserEmail) {
                $('.place-order-btn').prop('disabled', false);

            } else {
                if($('#zipcode').hasClass('valid')){
                        $('.place-order-btn').prop('disabled', true);
                    }
            }

        }

        // Check if disable_button is true, then call the function
        if (disable_button) {
            disableAcceptRefundButton();
        }


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
            disable_button = false;
            enableAcceptRefundButton();

            function enableAcceptRefundButton() {
                $('.place-order-btn').prop('disabled', false);
            }
            alert('Please fill in all Shipping Form required fields');
            return false;
        } else {
            var data2 = '&full_name=' + fullName + '&s_country=' + country + '&city=' + city +
                '&states_countries=' + states + '&s_street=' + street + '&s_post_code=' + zipcode;

            if (UserEmail) {
                // input field is not empty, set the value 
                $("#stripe_email").val(UserEmail);
                $("#crypto_email").val(UserEmail);
            }

            jQuery('#shipping_data_stripe').val(data2);
            // $('#loader').show();
        }

    });

    $('.paywithmetamask').click(function () {
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

                        options += '<option value="' + item.state_id + '">' + item.state_name +
                            '</option>';
                    });
                    jQuery('#states_countries').html(options);
                }
            });
        }

    }

    function onLoadRates() {


        var started = false;
        var startedCrypto = false;
        var timeoutId;

        // if(!started){

        let stripeCal = {
            subTotal: 0,
            tax: 0,
            shipping: 0,
            total: 0
        }
        let cryptoCal = {
            subTotal: 0,
            tax: 0,
            shipping: 0,
            total: 0
        }
        var country = $('#s_country').val();
        var firstname = $('#inputFirstNameShipping').val();

        if (!firstname) {
            $('#loading').hide();
        }
        var lastname = $('#inputLastNameShipping').val();
        var fullName = firstname + ' ' + lastname;
        var UserEmail = $('#useremail').val();
        var country = $('#s_country').val();
        var states = $('#states_countries').val();
        var street = $('#street_address').val();
        var city = $('#city_name').val();
        var zipcode = $('#zipcode').val();
        clearTimeout(timeoutId);
        timeoutId = setTimeout(() => {

            data2 = '&full_name=' + fullName + '&s_country=' + country + '&city=' + city +
                '&states_countries=' +
                states + '&s_street=' + street + '&s_post_code=' + zipcode;

            if (country && states && street && city && zipcode && fullName) {
                jQuery('#shipping_data').val(data2);
                jQuery('#shipping_data_stripe').val(data2);
            } else {

                $('#loading').hide();
                return;
            }

            let emailPattern = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
            if (!emailPattern.test(UserEmail)) {
                return;
            }
            let storeOwners = <?php echo json_encode($store_owners); ?>;
            let userEmailLower = UserEmail.toLowerCase();
            let storeOwnersLower = storeOwners.map(owner => owner.toLowerCase());


            if (storeOwnersLower.includes(userEmailLower)) {

                // $('#useremail').after('<span id="useremail-error" class="error">You can not buy your own product, Please try with another email address or remove product.</span>')
                // add message new
                $('#loading').hide();
                return;
            }
            if (!started && country && states && street && city && firstname.trim() != '' && lastname.trim() !=
                '' && UserEmail.trim() != '' && zipcode.length >= 5) {

                $('#loading').show();


                started = true;

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


                // var myArray = [];
                var prevValue = null;
                let prevProductId = null;
                prevProductIds = [];
                let prevVariationId = null;
                let totalItem = $('.get_data_calulate_ship').length - 1;

                setTimeout(() => {
                    $('.get_data_calulate_ship').each(function (key, value) {
                        var shipping_error;
                        var that = $(this);
                        var val = $(this).val().split("_");

                        var value = val[0];
                        var storeId = val[1];
                        var productId = val[2];
                        var weight = val[3];
                        var origin_zip = val[4];
                        var shipping_option_id = val[5];
                        var product_quantity = val[6];
                        var product_price = val[7];
                        var product_title = val[8];
                        var product_key = val[9];
                        var product_length = val[10];
                        var product_width = val[11];
                        var product_height = val[12];
                        let product_type = val[13];
                        let variation_id = val[14]
                        let is_domestic_free = val[15]
                        let flat_shipping_rate = val[16]
                        let product_id = val[17]

                        var obj = {
                            value: value,
                            storeId: storeId,
                            productId: productId,
                            weight: weight,
                            origin_zip: origin_zip,
                            shipping_option_id: shipping_option_id,
                            product_length: product_length,
                            product_width: product_width,
                            product_height: product_height,
                            product_quantity: product_height,
                            product_price: product_price,
                            product_title: product_title,
                            product_key: product_key,

                        };
                        let simple = false;
                        let variant = false;
                        let virtual = false;
                        console.log("obj", obj);


                        // var total =  $('.subtotal_product_str
                        if (is_domestic_free > 0) {

                            stripeCal.subTotal += parseFloat(product_price);
                            stripeCal.total = stripeCal.subTotal + stripeCal.shipping;

                            if (key == totalItem) {
                                // $(".subtotal_product_stripe").html(stripeCal.subTotal.toFixed(2));
                                $(".product_shipping_total").html(stripeCal.shipping.toFixed(
                                    2));
                                $(".grandtotal_cal").html(stripeCal.total.toFixed(2));
                                $("#total_amount_ship").val(stripeCal.total * 100);
                                $('#orderData_stripe').val(JSON.stringify(orderData));
                                started = false;
                                $('#loading').hide();
                                $('#prcngbxiny').removeClass('overlay-second');
                            }
                            // console.log('orderDataaa' , orderData)

                            return;
                        } else if (flat_shipping_rate > 0) {

                            stripeCal.shipping += parseFloat(flat_shipping_rate);
                            stripeCal.subTotal += parseFloat(product_price);
                            stripeCal.total = stripeCal.subTotal + stripeCal.shipping;

                            orderData[storeId][product_key].shipping_price = flat_shipping_rate;

                            // get rates here of flatshipping and add in shipping

                            if (key == totalItem) {

                                // $(".subtotal_product_stripe").html(stripeCal.subTotal.toFixed(2));
                                $(".product_shipping_total").html(stripeCal.shipping.toFixed(
                                    2));
                                $(".grandtotal_cal").html(stripeCal.total.toFixed(2));
                                $("#total_amount_ship").val(stripeCal.total * 100);
                                $('#orderData_stripe').val(JSON.stringify(orderData));
                                started = false;
                                $('#loading').hide();
                                $('#prcngbxiny').removeClass('overlay-second');

                            }


                            return;
                        }
                        switch (product_type) {
                            case 'simple':
                                simple = true
                                break;
                            case 'variant':
                                variant = true
                                break;
                            case 'virtual':
                                virtual = true
                                break;
                            default:
                                break;
                        }
                        if (product_type == 'simple') {
                            simple = true;
                        } else if (product_type == 'variant') {
                            variant = true;
                        }
                        if (simple) {

                            let data = null;
                            $.ajax({
                                url: '/calculate_service',
                                type: "POST",
                                async: false,
                                dataType: "json",
                                data: {
                                    value: value,
                                    storeId: storeId,
                                    origin_zip: origin_zip,
                                    shipping_option_id: shipping_option_id,
                                    weight: weight,
                                    product_length: product_length,
                                    product_width: product_width,
                                    product_height: product_height,
                                    product_quantity: product_quantity,
                                    product_price: product_price,
                                    product_title: product_title,
                                    shipment_data: shipment_data,
                                    _token: '{{ csrf_token() }}'
                                },
                                success: function (res) {
                                    $('#prcngbxiny').removeClass('overlay-second');
                                    if (Array.isArray(res)) {

                                        data = res;
                                    } else {
                                        data = Object.values(res)
                                    }
                                    if (Array.isArray(data) && data.length < 1) {
                                        shippingErrorResponse(
                                            'Post Code is not correct', false);
                                        return;
                                    }

                                    if (data.error || data[0]?.error) {
                                        shipping_error = data.error;
                                        shippingErrorResponse(shipping_error);
                                        started = false;
                                        startedCrypto = false;
                                        return;

                                    } else {
                                        $('.pymntslct').show();
                                    }

                                    var vservice = $(".list_" + productId).find(
                                        '.' + data.service);
                                    data.forEach((item) => {

                                        if (item.service ==
                                            'ParcelSelect') {
                                            $(".list_" + productId).find(
                                                '.' + item.service)
                                                .attr('disabled', false);
                                            $(".list_" + productId).find(
                                                ".shipping_price_calculated"
                                            ).html(item.rate);
                                            $(".list_" + productId).find(
                                                ".shipping_price_calculated"
                                            ).attr(
                                                'data-ship-price', item
                                                .rate);

                                            $(".list_" + productId).find(
                                                ".shipping_price_calculated"
                                            ).attr(
                                                'data-shipment-id', item
                                                    ?.shipment_id ? item
                                                .shipment_id : '');
                                            $(".list_" + productId).find(
                                                ".shipping_price_calculated"
                                            ).attr('data-rate-id',
                                                item?.id ? item.id : '');


                                        }
                                        if (item.service == 'Express') {

                                            $(".list_" + productId).find(
                                                '.' + item.service)
                                                .attr('disabled', false);
                                            $(".list_" + productId).find(
                                                ".shipping_price_calculated"
                                            ).html(item.rate);
                                            $(".list_" + productId).find(
                                                ".shipping_price_calculated"
                                            ).attr(
                                                'data-ship-price', item
                                                .rate);

                                            $(".list_" + productId).find(
                                                ".shipping_price_calculated"
                                            ).attr(
                                                'data-shipment-id', item
                                                    ?.shipment_id ? item
                                                .shipment_id : '');
                                            $(".list_" + productId).find(
                                                ".shipping_price_calculated"
                                            ).attr('data-rate-id',
                                                item?.id ? item.id : '');

                                        }
                                        if (item.service == 'Priority') {

                                            $(".list_" + productId).find(
                                                '.' + item.service)
                                                .attr('disabled', false);
                                            $(".list_" + productId).find(
                                                ".shipping_price_calculated"
                                            ).html(item.rate);
                                            $(".list_" + productId).find(
                                                ".shipping_price_calculated"
                                            ).attr(
                                                'data-ship-price', item
                                                .rate);

                                            $(".list_" + productId).find(
                                                ".shipping_price_calculated"
                                            ).attr(
                                                'data-shipment-id', item
                                                    ?.shipment_id ? item
                                                .shipment_id : '');
                                            $(".list_" + productId).find(
                                                ".shipping_price_calculated"
                                            ).attr('data-rate-id',
                                                item?.id ? item.id : '');


                                        }
                                        if (item.service == 'First') {
                                            $(".list_" + productId).find(
                                                '.' + item.service)
                                                .attr('disabled', false);
                                            $(".list_" + productId).find(
                                                ".shipping_price_calculated"
                                            ).html(item.rate);
                                            $(".list_" + productId).find(
                                                ".shipping_price_calculated"
                                            ).attr(
                                                'data-ship-price', item
                                                .rate);

                                            $(".list_" + productId).find(
                                                ".shipping_price_calculated"
                                            ).attr(
                                                'data-shipment-id', item
                                                    ?.shipment_id ? item
                                                .shipment_id : '');
                                            $(".list_" + productId).find(
                                                ".shipping_price_calculated"
                                            ).attr('data-rate-id',
                                                item?.id ? item.id : '');


                                        }
                                    })
                                    let elm = $("#list_" + productId).find(
                                        'option:not(:disabled)').first();
                                    if (elm.length < 1) {
                                        shippingErrorResponse(
                                            "There's some issue with " +
                                            product_title +
                                            ', Remove this product to continue',
                                            true);
                                        return;
                                    }

                                    let shipment_id = $("#list_" + productId).find(
                                        '.shipping_price_calculated').attr(
                                            'data-shipment-id');
                                    let rate_id = $("#list_" + productId).find(
                                        '.shipping_price_calculated').attr(
                                            'data-rate-id');

                                    let services = elm.val().split('_');
                                    if (services.length > 0) {
                                        let selected = services[0];

                                        let selectedService = data.find(obj => obj
                                            .service == selected);
                                        let serviceRate = selectedService ?
                                            selectedService.rate : null;

                                        if (serviceRate) {


                                            $(that).parent().parent().find(
                                                '.shipping_price_calculated')
                                                .html(serviceRate);
                                            $(that).parent().parent().find(
                                                '.shipping_price_calculated')
                                                .attr('data-ship-price',
                                                    serviceRate);

                                            stripeCal.shipping += parseFloat(
                                                serviceRate);
                                            stripeCal.subTotal += parseFloat(
                                                product_price);
                                            stripeCal.total = stripeCal.subTotal +
                                                stripeCal.shipping;


                                            // update OrderData

                                            orderData[storeId][product_key]
                                                .shipping_price = serviceRate;
                                            orderData[storeId][product_key]
                                                .shipping_method = selectedService
                                                    .service

                                            orderData[storeId][product_key]
                                                .ship_id = shipment_id ?
                                                    shipment_id : '';
                                            orderData[storeId][product_key]
                                                .rate_id = rate_id ? rate_id : '';

                                            if (key == totalItem) {

                                                // $(".subtotal_product_stripe").html(stripeCal.subTotal.toFixed(2));
                                                $(".product_shipping_total").html(
                                                    stripeCal.shipping.toFixed(
                                                        2));
                                                $(".grandtotal_cal").html(stripeCal
                                                    .total.toFixed(2));
                                                $("#total_amount_ship").val(
                                                    stripeCal.total * 100);
                                                $('#orderData_stripe').val(JSON
                                                    .stringify(orderData));
                                                started = false;
                                                $('#loading').hide()

                                            }

                                        }
                                    }
                                    let optionValue = elm.val()
                                    elm.parent().val(optionValue)

                                }

                            });
                        } else if (variant) {
                            let data = null;



                            // this product should be calculated
                            $.ajax({
                                url: '/calculate_service',
                                type: "POST",
                                dataType: "json",
                                async: false,
                                data: {
                                    value: value,
                                    storeId: storeId,
                                    origin_zip: origin_zip,
                                    shipping_option_id: shipping_option_id,
                                    weight: weight,
                                    product_length: product_length,
                                    product_width: product_width,
                                    product_height: product_height,
                                    product_quantity: product_quantity,
                                    product_price: product_price,
                                    product_title: product_title,
                                    shipment_data: shipment_data,
                                    _token: '{{ csrf_token() }}'
                                },
                                success: function (res) {
                                    $('#prcngbxiny').removeClass('overlay-second');
                                    if (Array.isArray(res)) {

                                        data = res;
                                    } else {
                                        data = Object.values(res)
                                    }

                                    if (Array.isArray(data) && data.length < 1) {
                                        shippingErrorResponse(
                                            'Post Code is not correct', false);
                                        return;
                                    }

                                    if (data.error || data[0]?.error) {


                                        shipping_error = data.error;
                                        shippingErrorResponse(shipping_error);
                                        started = false;
                                        startedCrypto = false;

                                        return;

                                    } else {
                                        $('.pymntslct').show();
                                    }
                                    $('.shipping_price_calculated').html(data.rate);
                                    $('.shipping_price_calculated').attr(
                                        'data-ship-price', data.rate);

                                    var shipTotalPrice = 0;
                                    var realTotalPrice = 0;
                                    $('.shipping_price_calculated').each(function (
                                        key, value) {
                                        shipTotalPrice = parseFloat(
                                            shipTotalPrice) +
                                            parseFloat($(this).attr(
                                                'data-ship-price'));
                                        $(".product_shipping_total").html(
                                            shipTotalPrice.toFixed(2));
                                    });

                                    var vservice = $(".list_" + productId + '_' +
                                        variation_id).find('.' + data.service);
                                    data.forEach((item) => {

                                        if (item.service ==
                                            'ParcelSelect') {
                                            $(".list_" + productId + '_' +
                                                variation_id).find('.' +
                                                    item.service).attr(
                                                        'disabled', false);
                                            $(".list_" + productId + '_' +
                                                variation_id).find(
                                                    ".shipping_price_calculated"
                                                ).html(item.rate);
                                            $(".list_" + productId + '_' +
                                                variation_id).find(
                                                    ".shipping_price_calculated"
                                                ).attr(
                                                    'data-ship-price', item
                                                    .rate);

                                            $(".list_" + productId + '_' +
                                                variation_id).find(
                                                    ".shipping_price_calculated"
                                                ).attr(
                                                    'data-shipment-id', item
                                                        ?.shipment_id ? item
                                                    .shipment_id : '');
                                            $(".list_" + productId + '_' +
                                                variation_id).find(
                                                    ".shipping_price_calculated"
                                                ).attr('data-rate-id',
                                                    item?.id ? item.id : '');



                                        }
                                        if (item.service == 'Express') {

                                            $(".list_" + productId + '_' +
                                                variation_id).find('.' +
                                                    item.service).attr(
                                                        'disabled', false);
                                            $(".list_" + productId + '_' +
                                                variation_id).find(
                                                    ".shipping_price_calculated"
                                                ).html(item.rate);
                                            $(".list_" + productId + '_' +
                                                variation_id).find(
                                                    ".shipping_price_calculated"
                                                ).attr(
                                                    'data-ship-price', item
                                                    .rate);

                                            $(".list_" + productId + '_' +
                                                variation_id).find(
                                                    ".shipping_price_calculated"
                                                ).attr(
                                                    'data-shipment-id', item
                                                        ?.shipment_id ? item
                                                    .shipment_id : '');
                                            $(".list_" + productId + '_' +
                                                variation_id).find(
                                                    ".shipping_price_calculated"
                                                ).attr('data-rate-id',
                                                    item?.id ? item.id : '');



                                        }
                                        if (item.service == 'Priority') {

                                            $(".list_" + productId + '_' +
                                                variation_id).find('.' +
                                                    item.service).attr(
                                                        'disabled', false);
                                            $(".list_" + productId + '_' +
                                                variation_id).find(
                                                    ".shipping_price_calculated"
                                                ).html(item.rate);
                                            $(".list_" + productId + '_' +
                                                variation_id).find(
                                                    ".shipping_price_calculated"
                                                ).attr(
                                                    'data-ship-price', item
                                                    .rate);

                                            $(".list_" + productId + '_' +
                                                variation_id).find(
                                                    ".shipping_price_calculated"
                                                ).attr(
                                                    'data-shipment-id', item
                                                        ?.shipment_id ? item
                                                    .shipment_id : '');
                                            $(".list_" + productId + '_' +
                                                variation_id).find(
                                                    ".shipping_price_calculated"
                                                ).attr('data-rate-id',
                                                    item?.id ? item.id : '');


                                        }
                                        if (item.service == 'First') {
                                            $(".list_" + productId + '_' +
                                                variation_id).find('.' +
                                                    item.service).attr(
                                                        'disabled', false);
                                            $(".list_" + productId + '_' +
                                                variation_id).find(
                                                    ".shipping_price_calculated"
                                                ).html(item.rate);
                                            $(".list_" + productId + '_' +
                                                variation_id).find(
                                                    ".shipping_price_calculated"
                                                ).attr(
                                                    'data-ship-price', item
                                                    .rate);

                                            $(".list_" + productId + '_' +
                                                variation_id).find(
                                                    ".shipping_price_calculated"
                                                ).attr(
                                                    'data-shipment-id', item
                                                        ?.shipment_id ? item
                                                    .shipment_id : '');
                                            $(".list_" + productId + '_' +
                                                variation_id).find(
                                                    ".shipping_price_calculated"
                                                ).attr('data-rate-id',
                                                    item?.id ? item.id : '');


                                        }
                                    })
                                    let elm = $("#list_" + productId + '_' +
                                        variation_id).find(
                                            'option:not(:disabled)').first();
                                    if (elm.length < 1) {
                                        shippingErrorResponse(
                                            "There's some issue with " +
                                            product_title +
                                            ', Remove this product to continue',
                                            true);
                                        return;
                                    }
                                    // find this list elm and get shipment id and rate id

                                    let shipment_id = $("#list_" + productId + '_' +
                                        variation_id).find(
                                            '.shipping_price_calculated').attr(
                                                'data-shipment-id');
                                    let rate_id = $("#list_" + productId + '_' +
                                        variation_id).find(
                                            '.shipping_price_calculated').attr(
                                                'data-rate-id');

                                    let services = elm.val().split('_');
                                    if (services.length > 0) {
                                        let selected = services[0];

                                        let selectedService = data.find(obj => obj
                                            .service == selected);
                                        let serviceRate = selectedService ?
                                            selectedService.rate : null;

                                        if (serviceRate) {


                                            $(that).parent().parent().find(
                                                '.shipping_price_calculated')
                                                .html(serviceRate);
                                            $(that).parent().parent().find(
                                                '.shipping_price_calculated')
                                                .attr('data-ship-price',
                                                    serviceRate);
                                            stripeCal.shipping += parseFloat(
                                                serviceRate);
                                            stripeCal.subTotal += parseFloat(
                                                product_price);
                                            stripeCal.total = stripeCal.subTotal +
                                                stripeCal.shipping;

                                            orderData[storeId][product_key]
                                                .shipping_price = serviceRate;
                                            orderData[storeId][product_key]
                                                .shipping_method = selectedService
                                                    .service;

                                            orderData[storeId][product_key]
                                                .ship_id = shipment_id ?
                                                    shipment_id : '';
                                            orderData[storeId][product_key]
                                                .rate_id = rate_id ? rate_id : '';

                                            if (key == totalItem) {
                                                // $(".subtotal_product_stripe").html(stripeCal.subTotal.toFixed(2));
                                                $(".product_shipping_total").html(
                                                    stripeCal.shipping.toFixed(
                                                        2));
                                                $(".grandtotal_cal").html(stripeCal
                                                    .total.toFixed(2));
                                                $("#total_amount_ship").val(
                                                    stripeCal.total * 100);
                                                $('#orderData_stripe').val(JSON
                                                    .stringify(orderData));
                                                started = false;
                                                $('#loading').hide()
                                            }

                                            // $(".grandtotal_cal").html(stripeCal.total);
                                        }
                                    }
                                    let optionValue = elm.val()
                                    elm.parent().val(optionValue)


                                }

                            });

                        } else if (virtual) {

                            stripeCal.subTotal += parseFloat(product_price);
                            stripeCal.total = stripeCal.subTotal + stripeCal.shipping;

                            if (key == totalItem) {
                                // $(".subtotal_product_stripe").html(stripeCal.subTotal.toFixed(2));
                                $(".product_shipping_total").html(stripeCal.shipping.toFixed(
                                    2));
                                $(".grandtotal_cal").html(stripeCal.total.toFixed(2));
                                $("#total_amount_ship").val(stripeCal.total * 100);
                                $('#orderData_stripe').val(JSON.stringify(orderData));
                                started = false;
                                $('#loading').hide();
                                $('#prcngbxiny').removeClass('overlay-second');

                            }
                        }

                    });
                    payWithGoogle();
                }, 200);
            }

            if (!startedCrypto && country && states && street && city && firstname.trim() != '' && lastname
                .trim() != '' && UserEmail.trim() != '' && zipcode.length >= 5) {
                return;
                $('#loading').show()

                startedCrypto = true;

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

                var prevValue = null;
                let prevProductId = null;
                let prevVariationId = null;
                let totalItem = $('.get_data_calulate_ship_crypto').length - 1;
                setTimeout(() => {
                    $('.get_data_calulate_ship_crypto').each(function (key, value) {
                        var shipping_error;
                        var that = $(this);
                        var val = $(this).val().split("_");
                        var value = val[0];
                        var storeId = val[1];
                        var productId = val[2];
                        var weight = val[3];
                        var origin_zip = val[4];
                        var shipping_option_id = val[5];
                        var product_quantity = val[6];
                        var product_price = val[7];
                        var product_title = val[8];
                        var product_key = val[9];
                        var product_length = val[10];
                        var product_width = val[11];
                        var product_height = val[12];
                        let product_type = val[13];
                        let variation_id = val[14]
                        let is_domestic_free = val[15]
                        let flat_shipping_rate = val[16]
                        let product_id = val[17]


                        var obj = {
                            value: value,
                            storeId: storeId,
                            productId: productId,
                            weight: weight,
                            origin_zip: origin_zip,
                            shipping_option_id: shipping_option_id,
                            product_length: product_length,
                            product_width: product_width,
                            product_height: product_height,
                            product_quantity: product_height,
                            product_price: product_price,
                            product_title: product_title,
                            product_key: product_key,

                        };
                        let simple = false;
                        let variant = false;
                        let virtual = false;
                        console.log("obj", obj);


                        if (is_domestic_free > 0) {

                            cryptoCal.subTotal += parseFloat(product_price);
                            cryptoCal.total = cryptoCal.subTotal + cryptoCal.shipping;

                            if (key == totalItem) {
                                $(".subtotal_product_stripe_crypto").html(cryptoCal.subTotal
                                    .toFixed(2));
                                $(".product_shipping_total_crypto").html(cryptoCal.shipping
                                    .toFixed(2));
                                $(".grandtotal_cal_crypto").html(cryptoCal.total.toFixed(2));
                                $('#orderData').val(JSON.stringify(orderDataCrypto));
                                setCryptoPayable(cryptoCal.total)
                                startedCrypto = false;
                                $('#loading').hide()


                            }
                            console.log('orderDataaa', orderDataCrypto)
                            return;
                        } else if (flat_shipping_rate > 0) {
                            // get rates here of flatshipping and add in shipping


                            cryptoCal.shipping += parseFloat(flat_shipping_rate);
                            cryptoCal.subTotal += parseFloat(product_price);
                            cryptoCal.total = cryptoCal.subTotal + cryptoCal.shipping;

                            orderDataCrypto[storeId][product_key].shipping_price =
                                flat_shipping_rate;

                            if (key == totalItem) {
                                $(".subtotal_product_stripe_crypto").html(cryptoCal.subTotal
                                    .toFixed(2));
                                $(".product_shipping_total_crypto").html(cryptoCal.shipping
                                    .toFixed(2));
                                $(".grandtotal_cal_crypto").html(cryptoCal.total.toFixed(2));
                                $('#orderData').val(JSON.stringify(orderDataCrypto));
                                setCryptoPayable(cryptoCal.total)
                                startedCrypto = false;
                                $('#loading').hide()

                            }


                            return;
                        }

                        switch (product_type) {
                            case 'simple':
                                simple = true
                                break;
                            case 'variant':
                                variant = true
                                break;
                            case 'virtual':
                                virtual = true
                                break;
                            default:
                                break;
                        }
                        if (simple) {
                            let data = null;
                            $.ajax({
                                url: '/calculate_service',
                                type: "POST",
                                async: false,
                                dataType: "json",
                                data: {
                                    value: value,
                                    storeId: storeId,
                                    origin_zip: origin_zip,
                                    shipping_option_id: shipping_option_id,
                                    weight: weight,
                                    product_length: product_length,
                                    product_width: product_width,
                                    product_height: product_height,
                                    product_quantity: product_quantity,
                                    product_price: product_price,
                                    product_title: product_title,
                                    shipment_data: shipment_data,
                                    _token: '{{ csrf_token() }}'
                                },
                                success: function (res) {
                                    if (Array.isArray(res)) {

                                        data = res;
                                    } else {
                                        data = Object.values(res)
                                    }
                                    if (Array.isArray(data) && data.length < 1) {
                                        shippingErrorResponse(
                                            'Post Code is not correct', false);
                                        return;
                                    }

                                    if (data.error || data[0]?.error) {
                                        shipping_error = data.error;
                                        shippingErrorResponse(shipping_error);
                                        started = false;
                                        startedCrypto = false;
                                        return;
                                    } else {
                                        $('.pymntslct').show();
                                    }

                                    var vservice = $(".list_crypto_" + productId)
                                        .find('.' + data.service);
                                    data.forEach((item) => {

                                        if (item.service ==
                                            'ParcelSelect') {
                                            $(".list_crypto_" + productId)
                                                .find('.' + item.service)
                                                .attr('disabled', false);
                                            $(".list_crypto_" + productId)
                                                .find(
                                                    ".shipping_price_calculated_crypto"
                                                ).html(item.rate);
                                            $(".list_crypto_" + productId)
                                                .find(
                                                    ".shipping_price_calculated_crypto"
                                                ).attr(
                                                    'data-ship-price', item
                                                    .rate);

                                            $(".list_crypto_" + productId)
                                                .find(
                                                    ".shipping_price_calculated_crypto"
                                                ).attr(
                                                    'data-shipment-id', item
                                                        ?.shipment_id ? item
                                                    .shipment_id : '');
                                            $(".list_crypto_" + productId)
                                                .find(
                                                    ".shipping_price_calculated_crypto"
                                                ).attr('data-rate-id',
                                                    item?.id ? item.id : ''
                                                );


                                        }
                                        if (item.service == 'Express') {

                                            $(".list_crypto_" + productId)
                                                .find('.' + item.service)
                                                .attr('disabled', false);
                                            $(".list_crypto_" + productId)
                                                .find(
                                                    ".shipping_price_calculated_crypto"
                                                ).html(item.rate);
                                            $(".list_crypto_" + productId)
                                                .find(
                                                    ".shipping_price_calculated_crypto"
                                                ).attr(
                                                    'data-ship-price', item
                                                    .rate);

                                            $(".list_crypto_" + productId)
                                                .find(
                                                    ".shipping_price_calculated_crypto"
                                                ).attr(
                                                    'data-shipment-id', item
                                                        ?.shipment_id ? item
                                                    .shipment_id : '');
                                            $(".list_crypto_" + productId)
                                                .find(
                                                    ".shipping_price_calculated_crypto"
                                                ).attr('data-rate-id',
                                                    item?.id ? item.id : ''
                                                );

                                        }
                                        if (item.service == 'Priority') {

                                            $(".list_crypto_" + productId)
                                                .find('.' + item.service)
                                                .attr('disabled', false);
                                            $(".list_crypto_" + productId)
                                                .find(
                                                    ".shipping_price_calculated_crypto"
                                                ).html(item.rate);
                                            $(".list_crypto_" + productId)
                                                .find(
                                                    ".shipping_price_calculated_crypto"
                                                ).attr(
                                                    'data-ship-price', item
                                                    .rate);

                                            $(".list_crypto_" + productId)
                                                .find(
                                                    ".shipping_price_calculated_crypto"
                                                ).attr(
                                                    'data-shipment-id', item
                                                        ?.shipment_id ? item
                                                    .shipment_id : '');
                                            $(".list_crypto_" + productId)
                                                .find(
                                                    ".shipping_price_calculated_crypto"
                                                ).attr('data-rate-id',
                                                    item?.id ? item.id : ''
                                                );

                                        }
                                        if (item.service == 'First') {
                                            $(".list_crypto_" + productId)
                                                .find('.' + item.service)
                                                .attr('disabled', false);
                                            $(".list_crypto_" + productId)
                                                .find(
                                                    ".shipping_price_calculated_crypto"
                                                ).html(item.rate);
                                            $(".list_crypto_" + productId)
                                                .find(
                                                    ".shipping_price_calculated_crypto"
                                                ).attr(
                                                    'data-ship-price', item
                                                    .rate);

                                            $(".list_crypto_" + productId)
                                                .find(
                                                    ".shipping_price_calculated_crypto"
                                                ).attr(
                                                    'data-shipment-id', item
                                                        ?.shipment_id ? item
                                                    .shipment_id : '');
                                            $(".list_crypto_" + productId)
                                                .find(
                                                    ".shipping_price_calculated_crypto"
                                                ).attr('data-rate-id',
                                                    item?.id ? item.id : ''
                                                );

                                        }
                                    })
                                    let elm = $("#list_crypto_" + productId).find(
                                        'option:not(:disabled)').first();
                                    if (elm.length < 1) {
                                        shippingErrorResponse(
                                            "There's some issue with " +
                                            product_title +
                                            ', Remove this product to continue',
                                            true);
                                        return;
                                    }

                                    let shipment_id = $("#list_crypto_" + productId)
                                        .find('.shipping_price_calculated_crypto')
                                        .attr('data-shipment-id');
                                    let rate_id = $("#list_crypto_" + productId)
                                        .find('.shipping_price_calculated_crypto')
                                        .attr('data-rate-id');

                                    let services = elm.val().split('_');
                                    if (services.length > 0) {
                                        let selected = services[0];

                                        let selectedService = data.find(obj => obj
                                            .service == selected);
                                        let serviceRate = selectedService ?
                                            selectedService.rate : null;

                                        if (serviceRate) {

                                            $(that).parent().parent().find(
                                                '.shipping_price_calculated_crypto'
                                            ).html(serviceRate);
                                            $(that).parent().parent().find(
                                                '.shipping_price_calculated_crypto'
                                            ).attr('data-ship-price',
                                                serviceRate);
                                            cryptoCal.shipping += parseFloat(
                                                serviceRate);
                                            cryptoCal.subTotal += parseFloat(
                                                product_price);
                                            cryptoCal.total = cryptoCal.subTotal +
                                                cryptoCal.shipping;

                                            // update OrderData

                                            orderDataCrypto[storeId][product_key]
                                                .shipping_price = serviceRate;
                                            orderDataCrypto[storeId][product_key]
                                                .shipping_method = selectedService
                                                    .service

                                            orderDataCrypto[storeId][product_key]
                                                .ship_id = shipment_id ?
                                                    shipment_id : '';
                                            orderDataCrypto[storeId][product_key]
                                                .rate_id = rate_id ? rate_id : '';

                                            if (key == totalItem) {
                                                $(".subtotal_product_stripe_crypto")
                                                    .html(cryptoCal.subTotal
                                                        .toFixed(2));
                                                $(".product_shipping_total_crypto")
                                                    .html(cryptoCal.shipping
                                                        .toFixed(2));
                                                $(".grandtotal_cal_crypto").html(
                                                    cryptoCal.total.toFixed(2));
                                                $('#orderData').val(JSON.stringify(
                                                    orderDataCrypto));
                                                setCryptoPayable(cryptoCal.total)
                                                startedCrypto = false;
                                                $('#loading').hide()

                                            }

                                            // $(".grandtotal_cal").html(cryptoCal.total);

                                        }
                                    }
                                    let optionValue = elm.val()
                                    elm.parent().val(optionValue)

                                }

                            });
                        } else if (variant) {
                            let data = null;



                            // this product should be calculated
                            $.ajax({
                                url: '/calculate_service',
                                type: "POST",
                                dataType: "json",
                                async: false,
                                data: {
                                    value: value,
                                    storeId: storeId,
                                    origin_zip: origin_zip,
                                    shipping_option_id: shipping_option_id,
                                    weight: weight,
                                    product_length: product_length,
                                    product_width: product_width,
                                    product_height: product_height,
                                    product_quantity: product_quantity,
                                    product_price: product_price,
                                    product_title: product_title,
                                    shipment_data: shipment_data,
                                    _token: '{{ csrf_token() }}'
                                },
                                success: function (res) {
                                    if (Array.isArray(res)) {
                                        data = res;
                                    } else {
                                        data = Object.values(res)
                                    }
                                    if (Array.isArray(data) && data.length < 1) {
                                        shippingErrorResponse(
                                            'Post Code is not correct', false);
                                        return;
                                    }

                                    if (data.error || data[0]?.error) {
                                        shipping_error = data.error;
                                        shippingErrorResponse(shipping_error);
                                        started = false;
                                        startedCrypto = false;
                                        return;

                                    } else {
                                        $('.pymntslct').show();
                                    }


                                    var vservice = $(".list_crypto_" + productId)
                                        .find('.' + data.service);
                                    data.forEach((item) => {

                                        orderDataCrypto[storeId][
                                            product_key
                                        ]
                                            .shipping_price = item.rate;
                                        orderDataCrypto[storeId][
                                            product_key
                                        ]
                                            .shipping_method = item.service;

                                        orderDataCrypto[storeId][
                                            product_key
                                        ].ship_id = item
                                                .ship_id;
                                        orderDataCrypto[storeId][
                                            product_key
                                        ].rate_id = item
                                                .rate_id;

                                        if (item.service ==
                                            'ParcelSelect') {
                                            $(".list_crypto_" + productId)
                                                .find('.' + item.service)
                                                .attr('disabled', false);
                                            $(".list_crypto_" + productId)
                                                .find(
                                                    ".shipping_price_calculated_crypto"
                                                ).html(item.rate);
                                            $(".list_crypto_" + productId)
                                                .find(
                                                    ".shipping_price_calculated_crypto"
                                                ).attr(
                                                    'data-ship-price', item
                                                    .rate);

                                            $(".list_crypto_" + productId)
                                                .find(
                                                    ".shipping_price_calculated_crypto"
                                                ).attr(
                                                    'data-shipment-id', item
                                                        ?.shipment_id ? item
                                                    .shipment_id : '');
                                            $(".list_crypto_" + productId)
                                                .find(
                                                    ".shipping_price_calculated_crypto"
                                                ).attr('data-rate-id',
                                                    item?.id ? item.id : ''
                                                );

                                        }
                                        if (item.service == 'Express') {

                                            $(".list_crypto_" + productId)
                                                .find('.' + item.service)
                                                .attr('disabled', false);
                                            $(".list_crypto_" + productId)
                                                .find(
                                                    ".shipping_price_calculated_crypto"
                                                ).html(item.rate);
                                            $(".list_crypto_" + productId)
                                                .find(
                                                    ".shipping_price_calculated_crypto"
                                                ).attr(
                                                    'data-ship-price', item
                                                    .rate);

                                            $(".list_crypto_" + productId)
                                                .find(
                                                    ".shipping_price_calculated_crypto"
                                                ).attr(
                                                    'data-shipment-id', item
                                                        ?.shipment_id ? item
                                                    .shipment_id : '');
                                            $(".list_crypto_" + productId)
                                                .find(
                                                    ".shipping_price_calculated_crypto"
                                                ).attr('data-rate-id',
                                                    item?.id ? item.id : ''
                                                );



                                        }
                                        if (item.service == 'Priority') {

                                            $(".list_crypto_" + productId)
                                                .find('.' + item.service)
                                                .attr('disabled', false);
                                            $(".list_crypto_" + productId)
                                                .find(
                                                    ".shipping_price_calculated_crypto"
                                                ).html(item.rate);
                                            $(".list_crypto_" + productId)
                                                .find(
                                                    ".shipping_price_calculated_crypto"
                                                ).attr(
                                                    'data-ship-price', item
                                                    .rate);

                                            $(".list_crypto_" + productId)
                                                .find(
                                                    ".shipping_price_calculated_crypto"
                                                ).attr(
                                                    'data-shipment-id', item
                                                        ?.shipment_id ? item
                                                    .shipment_id : '');
                                            $(".list_crypto_" + productId)
                                                .find(
                                                    ".shipping_price_calculated_crypto"
                                                ).attr('data-rate-id',
                                                    item?.id ? item.id : ''
                                                );

                                        }
                                        if (item.service == 'First') {
                                            $(".list_crypto_" + productId)
                                                .find('.' + item.service)
                                                .attr('disabled', false);
                                            $(".list_crypto_" + productId)
                                                .find(
                                                    ".shipping_price_calculated_crypto"
                                                ).html(item.rate);
                                            $(".list_crypto_" + productId)
                                                .find(
                                                    ".shipping_price_calculated_crypto"
                                                ).attr(
                                                    'data-ship-price', item
                                                    .rate);

                                            $(".list_crypto_" + productId)
                                                .find(
                                                    ".shipping_price_calculated_crypto"
                                                ).attr(
                                                    'data-shipment-id', item
                                                        ?.shipment_id ? item
                                                    .shipment_id : '');
                                            $(".list_crypto_" + productId)
                                                .find(
                                                    ".shipping_price_calculated_crypto"
                                                ).attr('data-rate-id',
                                                    item?.id ? item.id : ''
                                                );

                                        }
                                    })
                                    let elm = $("#list_crypto_" + productId).find(
                                        'option:not(:disabled)').first();
                                    if (elm.length < 1) {
                                        shippingErrorResponse(
                                            "There's some issue with " +
                                            product_title +
                                            ', Remove this product to continue',
                                            true);
                                        return;
                                    }
                                    let shipment_id = $("#list_crypto_" + productId)
                                        .find('.shipping_price_calculated_crypto')
                                        .attr('data-shipment-id');
                                    let rate_id = $("#list_crypto_" + productId)
                                        .find('.shipping_price_calculated_crypto')
                                        .attr('data-rate-id');

                                    let services = elm.val().split('_');
                                    if (services.length > 0) {
                                        let selected = services[0];

                                        let selectedService = data.find(obj => obj
                                            .service == selected);
                                        let serviceRate = selectedService ?
                                            selectedService.rate : null;

                                        if (serviceRate) {


                                            $(that).parent().parent().find(
                                                '.shipping_price_calculated_crypto'
                                            ).html(serviceRate);
                                            $(that).parent().parent().find(
                                                '.shipping_price_calculated_crypto'
                                            ).attr('data-ship-price',
                                                serviceRate);
                                            cryptoCal.shipping += parseFloat(
                                                serviceRate);
                                            cryptoCal.subTotal += parseFloat(
                                                product_price);
                                            cryptoCal.total = cryptoCal.subTotal +
                                                cryptoCal.shipping;

                                            orderDataCrypto[storeId][product_key]
                                                .shipping_price = serviceRate;
                                            orderDataCrypto[storeId][product_key]
                                                .shipping_method = selectedService
                                                    .service;

                                            orderDataCrypto[storeId][product_key]
                                                .ship_id = shipment_id ?
                                                    shipment_id : '';
                                            orderDataCrypto[storeId][product_key]
                                                .rate_id = rate_id ? rate_id : '';

                                            if (key == totalItem) {
                                                $(".subtotal_product_stripe_crypto")
                                                    .html(cryptoCal.subTotal
                                                        .toFixed(2));
                                                $(".product_shipping_total_crypto")
                                                    .html(cryptoCal.shipping
                                                        .toFixed(2));
                                                $(".grandtotal_cal_crypto").html(
                                                    cryptoCal.total.toFixed(2));
                                                $('#orderData').val(JSON.stringify(
                                                    orderDataCrypto));
                                                setCryptoPayable(cryptoCal.total)
                                                startedCrypto = false;
                                                $('#loading').hide()

                                            }

                                            // $(".grandtotal_cal").html(cryptoCal.total);
                                        }
                                    }

                                    let optionValue = elm.val()
                                    elm.parent().val(optionValue)

                                }

                            });

                        } else if (virtual) {

                            cryptoCal.subTotal += parseFloat(product_price);
                            cryptoCal.total = cryptoCal.subTotal + cryptoCal.shipping;

                            if (key == totalItem) {
                                $(".subtotal_product_stripe_crypto").html(cryptoCal.subTotal
                                    .toFixed(2));
                                $(".product_shipping_total_crypto").html(cryptoCal.shipping
                                    .toFixed(2));
                                $(".grandtotal_cal_crypto").html(cryptoCal.total.toFixed(2));
                                $('#orderData').val(JSON.stringify(orderDataCrypto));
                                setCryptoPayable(cryptoCal.total)
                                startedCrypto = false;
                                $('#loading').hide()


                            }
                        }

                    });

                    
                }, 200);

            }
        }, 5000)

    }
    var showFormCheckbox = document.getElementById('showFormCheckbox');
    var form = document.querySelector('.form');

    showFormCheckbox.addEventListener('change', function () {
        if (this.checked) {
            form.style.display = 'block';
        } else {
            form.style.display = 'none';
        }
    });


    function payWithGoogle() {
        //paymentRequestContainer.removeChild(mybutton);

        const grandTotalElement = document.querySelector('.grandtotal_cal');
        let totalAmountText = grandTotalElement.textContent
            .trim(); // Gets the text with the dollar sign

        // Remove the dollar sign and commas, and convert to a number
        let totalAmount = parseFloat(totalAmountText.replace('$', '').replace(',', ''));

        // Get the shipping amount from the data attribute of the shipping total element
        const shippingTotalElement = document.querySelector('.product_shipping_total');

        // Get the data attribute value (if needed, ensure it has a valid number)
        let shippingAmountText = shippingTotalElement.textContent.trim();

        // Get the text co  ntent and convert it to a number (only if you want to use it)
        let shippingAmount = parseFloat(shippingAmountText.replace('$', '').replace(',', ''));

        function formatCurrency(value) {
            return '$' + value.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ",");
        }

        // Update grand total display
        $(".grandtotal_cal").html(formatCurrency(totalAmount));
        shippingTotalElement.innerHTML = formatCurrency(
            shippingAmount); // Update the shipping total display

        const paymentRequest = stripe.paymentRequest({
            country: 'US',
            currency: 'usd',
            total: {
                label: 'Grand Total',
                amount: Math.round(totalAmount * 100),
            },
        });
        const prButton = elements.create('paymentRequestButton', {
            paymentRequest,
        });
        const paymentButtonContainer = document.getElementById('payment-request-button');
        const spanText = document.querySelector('.txt-p');
        // Check availability of payment methods
        (async () => {
            const result = await paymentRequest.canMakePayment();
            if (result) {
                prButton.mount('#payment-request-button');
                spanText.style.display = 'inline';
            } else {
                paymentButtonContainer.style.display = 'none';
            }
        })();
        paymentRequest.on('paymentmethod', async (ev) => {
            console.log(orderData);
            $('#loading').show()
            try {
                var firstname = $('#inputFirstNameShipping').val();
                var lastname = $('#inputLastNameShipping').val();
                var UserEmail = $('#useremail').val();
                var country = $('#s_country').val();
                var states = $('#states_countries').val();
                var street = $('#street_address').val();
                var city = $('#city_name').val();
                var zipcode = $('#zipcode').val();

                // Replace assigned details with the new values
                var orderDetails = {
                    first_name: firstname ||
                        '', // Use value from input or empty string if not available
                    last_name: lastname || '',
                    user_email: UserEmail, // Extract email from input field
                    address: street, // Use street address input
                    city: city,
                    states: states,
                    country: country,
                    post_code: zipcode, // Use zipcode input
                    orderdata: orderData, // Assuming you have orderData already set
                    total_amount: totalAmount,
                    amount: totalAmount,
                    fcm_token: "eMUiUT7-RBqKQg6mUSifym:APA91bGX47hd6Oblqny5UojkKjk7_HFMZ3E_aRewye9LEj19WDO6CFMzTA0EnOlyVOEbfXVwUrk2DmDsYhS4APmfnTWuZfJZAQwZnmN_RC05VG0e6Xwo2j0juusprwSdGQ3OxpHQXKqD",
                    app_flag: "buyer",
                    isChecked: false
                };


                // Fetch the clientSecret and other required data from your backend
                const response = await fetchClientSecret(ev.paymentMethod.id, orderDetails);

                console.log(totalAmount, shippingAmount);
                if (response && response.paymentIntent) {
                    // Confirm the payment with the fetched clientSecret
                    const {
                        paymentIntent,
                        error
                    } = await stripe.confirmCardPayment(response.paymentIntent, {
                        payment_method: ev.paymentMethod.id
                    });

                    if (error) {
                        ev.complete('fail');
                        $('#loading').hide();
                        var errorMessage = document.createElement('p');
                        errorMessage.textContent = "Payment failed: " + error.message; // Use the error message
                        errorMessage.style.color = "red"; // Set text color to red

                        // Append the error message to the error message container
                        var errorMessageContainer = document.getElementById('error-message-container');
                        errorMessageContainer.innerHTML = ''; // Clear any previous messages
                        errorMessageContainer.appendChild(errorMessage); // Add the new error message

                        errorMessageContainer.style.display = 'block';
                    } else {

                        ev.complete('success');
                        if (paymentIntent.status === 'succeeded') {

                            $('.readypayment-contianer').removeClass(
                                'show-section');
                            $('.shipping-contianer').removeClass('hide-section');
                            $('.shipping_amount').show();
                            $('.custom-checkout-hide-section').hide();
                            // window.location.href = '/order-confirmation'; 
                            $('#checkout-page').hide();

                            dataLayer.push({
                                ecommerce: null
                            });
                            dataLayer.push({
                                event: "purchase",
                                ecommerce: {
                                    transaction_id: response.transactionId,
                                    value: response.total_amount,
                                    tax: 0,
                                    shipping: response.total_shipping,
                                    currency: "USD",
                                    items: response.data
                                }
                            });

                        }
                    }
                } else {
                    ev.complete('fail');
                    alert('Failed to get client secret.');
                }
            } catch (error) {
                ev.complete('fail');
                 $('#loading').hide();
                        var errorMessage = document.createElement('p');
                        errorMessage.textContent = "Payment failed: " + error.message; // Use the error message
                        errorMessage.style.color = "red"; // Set text color to red

                        // Append the error message to the error message container
                        var errorMessageContainer = document.getElementById('error-message-container');
                        errorMessageContainer.innerHTML = ''; // Clear any previous messages
                        errorMessageContainer.appendChild(errorMessage); // Add the new error message

                        errorMessageContainer.style.display = 'block';
            }
        });
    }

</script>

<script>
    function setCryptoPayable(price) {

        $("#usdcprice").attr('price', 0.1);
        // $("#usdcprice").attr('price', "0");
    }

    function isNumber(evt) {
        var iKeyCode = (evt.which) ? evt.which : evt.keyCode;
        if (iKeyCode != 46 && iKeyCode > 31 && (iKeyCode < 48 || iKeyCode > 57)) {
            return false;
        }
        return true;
    }
    $(function () {
        $('#staticParentt').on('keydown', '#s_post_code', function (e) {
            -1 !== $.inArray(e.keyCode, [46, 8, 9, 27, 13, 110, 190]) || (/65|67|86|88/.test(e
                .keyCode) && (e.ctrlKey === true || e.metaKey === true)) && (!0 === e.ctrlKey || !
                    0 === e.metaKey) || 35 <= e.keyCode && 40 >= e.keyCode || (e.shiftKey || 48 > e
                        .keyCode || 57 < e.keyCode) && (96 > e.keyCode || 105 < e.keyCode) && e
                            .preventDefault()
        });
    })
</script>
<script src="{{ URL::asset('assets/js/checkout.js', env('HTTPS_STATUS')) }}"></script>
<script>
    $(document).ready(function () {
        const shipping_items = [];
        const begin_checkout_items = [];
        $('.checkout_product_list').each(function () {
            let productId = $(this).data('product_id');
            let product_index = $(this).attr('data-index');
            let store = $(this).find(".product_store").attr('data-store');
            let item_name = $(this).find('.product_title').text();
            let category = $(this).find('.product_category').attr('data-category');
            let product_price = $(this).find('.real_product_price').attr('data-real-price');
            let product_quantity = $(this).find('.quantity-input ').attr('data-quantity');

            const shipping_item = {
                item_id: productId,
                item_name: item_name,
                item_category: category,
                index: product_index,
                price: parseFloat(product_price),
                quantity: parseInt(product_quantity),
                item_brand: store,
                item_category: category,
                item_list_id: "add_shipping_info",
                item_list_name: "Checkout Process",
            };
            const begin_checkout = {
                item_id: productId,
                item_name: item_name,
                item_category: category,
                index: product_index,
                price: parseFloat(product_price),
                quantity: parseInt(product_quantity),
                item_brand: store,
                item_category: category,
                item_list_id: "begin_checkout",
                item_list_name: "Begin Checkout",
            };

            shipping_items.push(shipping_item);
            begin_checkout_items.push(begin_checkout);
        });
        // shipping checkout product
        dataLayer.push({
            ecommerce: null
        });
        dataLayer.push({
            event: "add_shipping_info",
            ecommerce: {
                currency: "USD",
                value: 7.77,
                items: shipping_items,
            }
        });
        // begin checkout product
        dataLayer.push({
            event: "begin_checkout",
            ecommerce: {
                currency: "USD",
                value: 7.77,
                items: begin_checkout_items,
            }
        });
    });
</script>
@endsection
