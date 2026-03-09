@extends('layouts.mobile-main')
@section('content')
@section('title', 'Store Dashboard')
<main>
    <?php
    $stripeActivecheck = true;
    $companyActivecheck = false;
    $walletActivecheck = false;
    $totalProductsActivecheck = false;
    $stripeActivecheck = false;
    $companyActive = '';
    $WalletActive = '';
    $totalProductsActive = '';
    $stripeActive = '';


   
    if (isset($storeData) && $storeData->is_completed == '1') {
        $companyActive = 'st5';

        $companyActivecheck = true;
    }
    // if (!empty($storeData->wallet_address)) {
    //     $WalletActive = 'st3';

    //     $walletActivecheck = true;
    // }
    if ($totalProducts > 0) {
        $totalProductsActive = 'st2';
        $totalProductsActivecheck = true;
    }
    if (isset($storeData) && $storeData->stripe == '1') {
        $stripeActive = 'st4';
        $stripeActivecheck = true;
    }
    $stripeConnectedAccount =true;
    // if ($stripeConnectedAccount) {
    //     $stripeActive = 'st5';
    //     $stripeActivecheck = true;
    // }
    ?>    

    @include('Store.mobile.sidebar')
    <div class="dshbrdin buyer">
        <div class="container">
            <div class="row"> 
                <div class="alert alert-success" id="successMessage" style="display: none;">
                </div>
                @if(Session::has('SuccessMessage'))
                <div class="alert alert-success" id="success_message"
                     role="alert">{{ Session::get('SuccessMessage') }}</div>
                <script>
                    setTimeout(function () {
                        jQuery("#success_message").fadeOut('slow');
                    }, 3000);
                </script>
                @endif
                @if(Session::has('errorMessage'))
                <div class="alert alert-danger" id="error_message"
                     role="alert">{{ Session::get('errorMessage') }}</div>
                <script>
                    setTimeout(function () {
                        jQuery("#error_message").fadeOut('slow');
                    }, 3000);
                </script>
                @endif

                <div class="container mt-15">
                    <div class="row">
                        <?php 
                            $userid = Session::get('userData')['user_id'];
                            $storeData = getStoreDataByUserId($userid);
                            if (!empty($storeData)) {
                                $status = checkStoreActive();
                                if ($status == false) {
                        ?>
                        <div class="col-sm-10">
                            <div class="alert alert-danger" id="error_message"
                                role="alert">You're store is deactive at the moment and needs to be activated for your items to be visible on the website.</a></div>
                        </div>
                        <?php }
                            } ?>
                        <?php
                            //echo '$walletActivecheck: <pre>' .print_r($walletActivecheck,true). '</pre>'; die;
                            if ($companyActivecheck && $totalProductsActivecheck  && $stripeActivecheck) {
                                ?>
                            <!-- <div class="row justify-content-between"> -->
                                <!-- <div class="col-sm-6">
                                    <h6 class="store-status-mbt">Store Status</h6>
                                </div> -->
                                <?php
                                    $class = "inactive-state";
                                    if (isset($storeData) && $storeData->store_status == "2") {
                                        $class = "active-state";
                                    }
                                    ?>

                                <div class="col-sm-2 text-right">

                                    <div class="switch-field d-flex <?php echo $class; ?>">
                                        <input type="radio" id="switch_active" name="switch_2" value="2" <?php
                                                if (isset($storeData) && $storeData->store_status == "2") {
                                                    echo "checked";
                                                } else {
                                                    echo '';
                                                        }
                                                        ?> />
                                                <label for="switch_active">Active</label>
                                                <input type="radio" id="switch_inactive" name="switch_2" value="0" <?php
                                                        if (isset($storeData) && $storeData->store_status == "0") {
                                                            echo "checked";
                                                        } else {
                                                            echo '';
                                                        }
                                                        ?> />
                                                <label for="switch_inactive">InActive</label>
                                            </div>

                                        </div>
                                    <!-- </div> -->

                                    <?php
                                    }
                                    ?>
                                </div>
                            </div>
                           
                <div class="col-md-12">
                    <div class="wdgtbx border">
                        <h6>Account Status</h6>
                        <div class="stbr">
                            <div class="col <?php echo $companyActive; ?>">Company<br/>Profile <span><img
                                        src="{{ asset('assets/images/chkst.png',env('HTTPS_STATUS')) }}" alt="check box icon" title="check box icon"></span>
                            </div>
                            <!-- <div class="col {{$WalletActive}}">Wallet Address <span><img
                                        src="{{ asset('assets/images/chkst.png',env('HTTPS_STATUS')) }}" alt="check box icon"></span>
                            </div> -->
                            <div class="col {{$stripeActive}} ">Stripe <br/>Account <span>
                            <?php 
                                 if (isset($storeData) && $storeData->stripe == "0") {
                                ?>
                                 <a href="{{URL::to('/store/payments/')}}" class="btn btn-primary btn-simple small-btn-mbt">Manage</a>
                                    <?php } ?>    
                            <img
                                        src="{{ asset('assets/images/chkst.png',env('HTTPS_STATUS')) }}" alt="check box icon" title="check box icon"></span>
                            </div>
                            <div class="col {{$totalProductsActive}}">Company<br/>Products <span>
                            <?php if ($totalProducts <= 0) { ?>
                            <a  class="btn btn-primary btn-simple small-btn-mbt" href="{{URL::to('/add-product')}}">Add Products</a>
                            <?php } ?>
                            <img
                                        src="{{ asset('assets/images/chkst.png',env('HTTPS_STATUS')) }}" alt="check box icon" title="check box icon"></span>
                            </div>
                            <br>
                        </div>
                        <?php if ($companyActivecheck == false || $totalProductsActivecheck == false || $stripeActivecheck == false) { ?>
                            <div class="text-center  small"><span class="red">Note:</span> Please ensure that all sections
                                mentioned above are filled out. This is necessary for your store to go live.
                            </div>
                        <?php } ?>

                    </div>
                </div>  
              
                <div class="col-md-12">
                    <div class="wdgtbx border">
                        <h6>Company Info
                            <!-- <a class="pull-right" href="<?php // echo URL::to('/store/edit'); ?>"><i
                                    class="fa fa-pencil" aria-hidden="true"></i></a> -->
                                </h6>
                        <div class="wdgcnt ">
                            <div class="dshord col-md-8 no-padd-left">

                                <?php
                                $imgeUrl = "";
                                $storeIcon = "";
                                $storeData->store_logo;
                                if (!empty($storeData->store_icon)) {
                                    $image_id = getImageIdByIconId((int) $storeData->store_icon);
                                    if (!empty($image_id))
                                        $storeIcon = getAttachmentUrlById((int) $image_id, 'icons');
                                }
                                if (!empty($storeData->store_logo)) {
                                    $imgeUrl = getAttachmentUrlById((int) $storeData->store_logo, 'small');
                                }
                                ?>
                                <div class="seprate-image display-table-cell">
                                    <p><img src="<?php echo $storeIcon; ?>"></p>
                                </div>
                                                                                                
                                <div class="content-des-mbt display-table-cell verticl-top pl-20">
                                        @if(!empty($imgeUrl)) 
                                        <p><img style="max-height:60px" src="<?php echo $imgeUrl; ?>"
                                            alt="<?php echo $storeData->store_name; ?>"></p>
                                        @endif
                                       

                                    <p>Member since: {{ date('F d, Y', strtotime($storeData->created_at)) }}</p>
                                    <p><?php echo isset($storeData->street_address) ? $storeData->street_address : ''; ?></p>
                                    <p class="mb-10 mt-0">
                                        Contact: <?php echo isset($storeData->contact_info) ? $storeData->contact_info : ''; ?></p>


                                    <div class="view-al static-position pull-left">
                                        <a href="<?php echo URL::to('/store/edit'); ?>">Manage</a>
                                    </div>
                                    <div class="view-al static-position pull-left" style="margin-left:10px;">
                                        <a href="<?php echo URL::to('/brand/' . $storeData->store_slug); ?>"
                                           target="_blank">Visit Store</a>
                                    </div>
                                </div>

                            </div>
                             <div class="dshord col-md-4">
                                <div class="usr">
                                    <p class="image-outer-mbt">

                                        <span class="user-image">
                                            <span class="head-upper-mbt">
                                                <?php
                                                $imageUrl = '';
                                                if ($userData->user_avatar != '') {
                                                    $imageUrl = getAttachmentUrlById((int) $userData->user_avatar, 'small');
                                                    ?>
                                                    <span class="login-name"><img class="rounded-circle"
                                                                                  src="<?php echo $imageUrl; ?>"
                                                                                  alt=""></span>
                                                        <?php
                                                    } else {
                                                        if (!empty($userData)) {
                                                            $name = "";

                                                            $username = $userData->name;
                                                            $words = preg_split("/\s+/", $username);
                                                            foreach ($words as $w) {
                                                                $name .= strtoupper($w[0]);
                                                            }
                                                            ?>
                                                        <?php }
                                                        ?>
                                                    <span class="login-name"><?php echo $name; ?></span>

                                                <?php } ?>
                                            </span>
                                        </span>

                                    </p>
                                </div>
                 <div class="shp">
                                    <?php
                                    $username = $userData->name;
                                    ?>
                                    <p><?php
                                        if (isset($userData->name)) {
                                            echo $userData->name;
                                        } else {
                                            echo '';
                                        }
                                        ?></p>

                    <a class="btn btn-primary btn-simple small-btn-mbt " href="<?php echo URL::to('user-dashboard') ?>">Manage Profile </a>    
                                </div>
                            </div>
                        </div>
                    </div>
                </div> 

                <div class="col-md-6">
                    <div class="wdgtbx border" style="background-color:#f0f0f0" >
                        <h6>Add Product</h6>
                        <div class="wdgcnt">
                            <div class=" dshprdbx ">
                                <p><b> You have <?php
                                        if ($totalProducts == 0) {
                                            echo "no";
                                        } else {
                                            echo $totalProducts;
                                        }
                                        ?>  <?php
                                        if ($totalProducts > 1) {
                                            echo "products";
                                        } else {
                                            echo "product";
                                        }
                                        ?></b></p>
                               <?php if ($totalProducts > 0) { ?>
                                <div class="store-section-buttons">
                                    <a   href="{{URL::to('/store-products')}}" class="btn btn-primary btn-simple store-section-button mb-3">View Products</a>
                                <?php } ?>
                                <?php if ($totalProducts >= 1) { ?>
                                    <br/><a href="{{URL::to('/add-product')}}" class="btn btn-primary btn-simple">Add
                                        More Products</a>
                                <?php } else { ?>
                                    <a href="{{URL::to('/add-product')}}" class="btn btn-primary btn-simple">Add
                                        your first Product</a>
                                <?php } ?>
                               </div>    
                            </div>    
                        </div>      
                    </div>    
                </div>

                <div class="col-md-6 mb-30">
                    <div class="wdgtbx border height100 mb-0 white-background all-shahdow">
                        <h6>Orders
                             <!-- <a class="pull-right" href="<?php // echo URL::to('/brand-orders') ?>"><i
                                    class="fa fa-pencil" aria-hidden="true"></i></a> -->
                        </h6>
                        <div class="wdgcnt">
                            <div class="dshord ">
                                <p>Orders Completed: <?php
                                    $count = getOrdersCount($storeData->store_id, $role = 'store', $status = 'completed');
                                    echo $count;
                                    ?> </p>
                                <p>Orders Canceled: <?php
                                    $count = getOrdersCount($storeData->store_id, $role = 'store', $status = 'cancelled');
                                    echo $count;
                                    ?></p>
                                <p>Orders Pending: <?php
                                    $count = getOrdersCount($storeData->store_id, $role = 'store', $status = 'pending');
                                    echo $count;
                                    ?>
                                    <?php //if ($count > 0) { ?>
                                    <!-- &nbsp;&nbsp;<a href="{{URL::to('/brand-orders?status=pending')}}">Check
                                        Status</a> -->
                                    <?php
                                    // }
                                    //  else {
                                    //     echo 0;
                                    // }
                                    ?> </p>

                                <?php /// echo $userData['pendingorders'];           ?>
                                <!--                                    &nbsp;&nbsp;<a href="{{URL::to('/buyer-orders')}}">Check Status</a>-->
                                <?php
                                //                                    } else {
                                //                                        echo 0;
                                //                                    }
                                ?> </p>

                                <br>
                            </div>
                            <div class="view-al text-right">
                                <a href="<?php echo URL::to('/brand-orders'); ?>">Manage</a>
                            </div>

                        </div>
                    </div>

                </div>
                <?php   if($storeData->store_status  == 2){ ?> 
                <!-- <div class="col-md-6">
                    <div class="wdgtbx border" style="background-color:#f0f0f0">
                        <h6>Shipping</h6>
                        <div class="wdgcnt">
                            <div class="dshshp text-center">
                                <br>                             
                                <h4 class="mt-15 fnt-16 custom-style-heading">Active Shipping Methods</h4>
                                <p>Flat Shipping</p>
                                <p>Local Pickup</p>
                                <p>Mover</p>
                            </div>
                        </div> 
                        <div class="view-al">
                            <a href="<?php // echo URL::to('/edit-shipping-address'); ?>">Manage</a>
                        </div>
                    </div>    
                </div>  -->
                <?php  } ?>
                <div class="col-md-6 mb-15">
                    <div class="wdgtbx border mb-0 height100 white-background all-shahdow">
                        <?php
                          if (isset($storeData) && $storeData->stripe == '0') { ?>
                       
                        <h6>Connect your bank account 
                            <?php
                          }else{
                            ?>
                              <h6>Connected  
                                <?php } ?>
                            <!-- <a class="pull-right" href="{{URL::to('/store/payments/')}}"><i
                                    class="fa fa-pencil" aria-hidden="true"></i></a> -->
                        </h6>
                        <?php
                        

                        if (isset($storeData) && $storeData->stripe == '0') {
                            $stripeIcon = 'fa-close';
                            ?>

                            <h4 class="mt-15 fnt-16"> <i class="fa {{$stripeIcon}}" aria-hidden="true"></i> You do not have an active bank account attached with our payment processor. Mallshark needs an active bank account to release your payments when your products are sold. Please click on the button below(Manage Button) and attach your account information.</h4>
                        <?php } else {
                            $stripeIcon = 'fa-check';
                            ?>
                            <h4 class="mt-15 fnt-16"> <i class="fa {{$stripeIcon}}" aria-hidden="true"></i>  Bank Account is currently connected with Mallshark's partner, Stripe.</h4>
                            <?php
                        }
                        ?>

                     
                                     <!-- 
                                    <div class="checked-ticks mt-0">
                                        <div>
                                            <span class="fnt-16 mb-0"><i class="fa {{$stripeIcon}}" aria-hidden="true"></i>
                                                <span class="z-symbl zit-lrge">
                                                    Stripe
                                                </span>

                                            </span>
                                            <span></span>
                                        </div>
                                    </div> -->

                    
                        <div class="view-al text-right">
                                <a href="{{URL::to('/store/payments/')}}">Manage</a>
                        </div>

                    </div>
                </div>
                <div class="col-md-6 mb-15">
                    <div class="wdgtbx border mb-0 height100 white-background all-shahdow">
                        <h6> Connect your ERC20 Wallet
                            <!-- <a class="pull-right" href="{{URL::to('/store/paidby/')}}"><i
                                    class="fa fa-pencil" aria-hidden="true"></i></a> -->
                        </h6>
                        <?php
                     
                        

                            if (isset($storeData) && $storeData->wallet_address == '') {
                                // die('called');
                                $stripeIcon = 'fa-close';
                                ?>

                                <h4 class="mt-15 fnt-16"> <i class="fa {{$stripeIcon}}" aria-hidden="true"></i> You do not have a Wallet Connected with Mallshark. You cannot receive Crypto payments at the moment. Please click on the button below and add your ERC20 wallet Address. If you want to learn more about ERC20 wallets <a href="https://coindcx.com/blog/cryptocurrency/top-9-erc20-wallets-2022/">click here</a></h4>
                            <?php } else {
                                $stripeIcon = 'fa-check';

                                ?>
                                <h4 class="mt-15 fnt-16"><i class="fa {{$stripeIcon}}" aria-hidden="true"></i> Your Wallet is currently connected with Mallshark for crypto payments.</h4>
                                <?php
                            } 
                            ?>

                        <!-- <h4 class="mt-15 fnt-16">Active Payment Method</h4> -->

                        <!-- <div class="checked-ticks mt-0">
                            <div>
                                <span class="fnt-16 mb-0"><i class="fa {{$stripeIcon}}" aria-hidden="true"></i>
                                    <span class="zit-lrge">
                                        Wallet Address
                                    </span>

                                </span>
                                <span></span>
                            </div>
                        </div> -->

                     
                        <div class="view-al text-right">
                            <a href="{{URL::to('/store/paidby/')}}" class="">Manage</a>
                        </div>

                    </div>
                </div>
            </div> 
        </div>
    </div>
    <style>
.store-section-button{
    margin: 4px;
}
    </style>    
    <script>
        var message = "";
        jQuery(document).on('click', '.management-payment', function () {
            swal("Payment Methods!", "We support stripe as the only method for payment at this moment");
        });

        $(document).on("click", "#switch_active", function () {
            var res = {}
            var storeStatus = $(this).val();
            res['store_status'] = storeStatus;
            var result = confirm("Are you sure to change Store Status");
            if (result == true) {
                sendreq(res, 'store-status', 'change', 'callbacksutatus');
                message = "Store has been  activated successfully";
                $(".switch-field").removeClass("inactive-state");
                $(".switch-field").addClass("active-state");
            } else {
                return false;
            }
        });
        $(document).on("click", "#switch_inactive", function () {
            var res = {}
            var storeStatus = $(this).val();
            res['store_status'] = storeStatus;
            //var result = confirm("Are you sure to change Store Status");
            swal({
                title: "Are you sure?",
                text: "notification",
                type: "warning",
                showCancelButton: true,
                confirmButtonClass: "btn-success",
                confirmButtonText: "Yes, Do it!",
                closeOnConfirm: false
            },
                    function () {
                        //jQuery('.uploadImages').show();


                        sendreq(res, 'store-status', 'change', 'callbacksutatus');
                        $(".switch-field").removeClass("active-state");
                        $(".switch-field").addClass("inactive-state");

                        message = "Store has been inactive successfully";
                        swal("Success!", message, "success");

                    });
                    /*
            if (result == true) {

            } else {
                return false;
            }
            */

        });
        function callbacksutatus() {
            $("#successMessage").html(message);
            $("#successMessage").show();
            setTimeout(function () {
                jQuery("#successMessage").fadeOut('slow')
            }, 3000);
        }
    </script>
</main>
@endsection