@extends('layouts.dashboard-main')
@section('content')
@section('title', 'Store Dashboard')
<main>
    <style>
    .noteactive {
        position: absolute;
        z-index: 9999;
        width: 100%;
        height: 100%;
        left: 0px;
        right: 0px;
    }

    html body .switch-buttonk input {
        position: static !important;
        z-index: 88;
    }

    .alert {
        margin-top: 0px;
        margin-bottom: 30px;
        position: absolute;
        width: auto;
        left: 0;
        right: 0;
        margin-left: auto;
        margin-right: auto;
        z-index: 999;
        margin-top: -10px;
    }
    @media screen and (max-width:767px) {
        .cm-edit-ja {
        padding: 0;
        overflow: hidden;
        }
    }
    a.btn.btn-primary.btn-simple.small-btn-mbt.store-profile-mbt {
    left: 171px;
    position: relative;
    margin-top: -5px;
}
.profile-box{
    background-color: #e0e0e0 !important;
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
</style>
<?php


    $companyActivecheck = false;
    $walletActivecheck = false;
    $totalProductsActivecheck = false;
    $stripeActivecheck = false;
    $companyActive = '';
    $WalletActive = '';
    $totalProductsActive = '';
    $stripeActive = '';

    if (isset($storeData) && $storeData->is_completed == '1') {
        $companyActive = 'st2';

        $companyActivecheck = true;
    }
    if (isset($storeData) && $storeData->stripe == '1') {
        $stripeActive = 'st4';
        $stripeActivecheck = true;
    }

    if ($totalProducts > 10) {
        $totalProductsActive = 'st6';
        $totalProductsActivecheck = true;
    }
    ?>
<div class="flex-parent-div">
    <div class="change-password-page-cmt">
            @include('buyer.customsidebar')

        </div>
        
    <div class="dshbrdin store-dashboard pro-dsb-cntainer home-section cm-edit-ja overflow-y-none mobile-adjust-scroll" style="margin-bottom: 15px;">
        <div class="container">
        @include('Store.sidebar')
            <div class="row">
                <div class="alert alert-success" id="successMessage" style="display: none;">
                </div>
                @if(Session::has('SuccessMessage'))
              
                
                <?php $successMessage = Session::get('SuccessMessage') ?>
                <script>
                toastr.options = {
                    closeButton: true,
                    tapToDismiss: true,
                    positionClass: 'toast-top-right',
                    toastClass: 'toast-icon custom-toast-success',
                    progressBar: true,
                };
                toastr.success('<?php echo  $successMessage ?> ');
                </script>
                @endif
                @if(Session::has('errorMessage'))
                
                <?php $errorMessage = Session::get('errorMessage') ?>
                <script>
                toastr.options = {
                    closeButton: true,
                    tapToDismiss: true,
                    positionClass: 'toast-top-right',
                    toastClass: 'toast-icon custom-toast-success',
                    progressBar: true,
                };
                toastr.error('{{ $errorMessage }}');
                </script>
                @endif
                
                            <div class="row boxCont" style="margin-top:20px;">
                                
                                   
                                    <?php
                                        //echo '$walletActivecheck: <pre>' .print_r($walletActivecheck,true). '</pre>'; die;
                                        // if ($companyActivecheck && $totalProductsActivecheck  && $stripeActivecheck) {
                                            ?>
                                        
                                <div class="col-sm-12 col-12">
                                    <h6 class="store-status-mbt">
                                    <div class="d-flex align-items-center" style="    justify-content: space-between;">  
                                       <div class="col dashbrdcol">
                                            <div style="display: flex; align-items:center;gap:10px;">
                                                <a href="javascript:void(0)" class="d-flex align-items-center gap-10px sidebar-admin-burger-menu" id="open-sidebar-admin-panel">
                                                    <span id="menu-toggle-dashboard" class="shopzit-menu mt-0 ml-0 bx-menu">
                                                        <span></span><span></span><span></span> <span></span>
                                                    </span>
                                                    <!-- <span>Menu</span> -->
                                                </a>
                                                <h2 style="margin: 0;">Dashboard</h2>
                                            </div> 
                                        </div>                    
                                        <!-- <a href="javascript:void(0)" class="d-flex align-items-center gap-10px sidebar-admin-burger-menu" id="open-sidebar-admin-panel">                                        
                                        <span id="menu-toggle-dashboard" class="shopzit-menu mt-0 ml-0 bx-menu" style="margin-right: 5px;">
                                            <span></span><span></span><span></span> <span></span>
                                        </span>
                                      
                                        </a> -->
                                        <?php  
                                        if($storeData->store_status != '1'){ ?>

                                      
                                       
                                        <?php
                                                $class = "inactive-state";
                                                if (isset($storeData) && $storeData->store_status == "2") {
                                                    $class = "active-state";
                                                }
                                                ?>

                                            <div class="col text-left storeStatus" style="display: flex; justify-content: end;">
                                            <span class="small" style="margin-right: 10px; position: relative;top: 10px;"> Store Status</span>
                                                <span class="switch-field d-flex <?php echo $class; ?>" style="width:220px">
                                                    <input type="radio" id="switch_active" name="switch_2" value="2" data-stripe="{{$storeData->stripe}}" data-company="{{$storeData->is_completed}}" data-totalProduct="{{$totalProducts}}" <?php
                                                            if (isset($storeData) && $storeData->store_status == "2") {
                                                                echo "checked";
                                                            } else {
                                                                echo '';
                                                                    }
                                                                    ?> />
                                                            <label for="switch_active">Activate</label>
                                                            <input type="radio" id="switch_inactive" name="switch_2" value="0" <?php
                                                                    if (isset($storeData) && $storeData->store_status == "0") {
                                                                        echo "checked";
                                                                    } else {
                                                                        echo '';
                                                                    }
                                                                    ?> />
                                                            <label for="switch_inactive">Deactivate</label>
                                                                </span>
                                            </div>
                                        <?php  } ?>
                                    </div>   
      
                                    </h6>
                                </div>
                                            
                                                <!-- </div> -->

                                                <?php
                                                // }
                                                ?>
                                
                           
                       
                <div class="col col-md-12 col-sm-12 col-12">
                     
                <div class="d-flex align-items-center gap-3">
                    <!-- <a href="javacript:;" class="d-flex align-items-center gap-10px sidebar-admin-burger-menu" id="open-sidebar-admin-panel">                                        
                                    <span id="menu-toggle-dashboard" class="shopzit-menu mt-0 ml-0 bx-menu">
                                        <span></span><span></span><span></span> <span></span>
                                    </span> -->
                                    <!-- <span>Menu</span> -->
                                    <!-- </a> -->
                                    <!-- <span>Dashboard</span> -->
                    </div>


                    <div class="d-none">
                        
                        <div class="stbr">
                           <?php 
                            if(!empty($storeData->is_completed)){ ?>
                            <div class="col strpbx1 st5"> Profile 
                                <span><img src="{{ asset('assets/images/chkst.png',env('HTTPS_STATUS')) }}" alt="check box icon" ></span>
                            </div>
                               
                          <?php  }else{ ?>
                            <div class="col profile-box">Profile
                            <a href="{{URL::to('/store/edit')}}" class="btn btn-primary btn-simple small-btn-mbt store-profile-mbt">Manage</a>
                            </div>
                            <?php }
                           ?>
                            <div class="col strpbx2 {{$stripeActive}}">Stripe Account <span>
                                <?php 
                                 if (isset($storeData) && $storeData->stripe == "0") {
                                ?>
                                 <a href="{{URL::to('/store/payments/')}}" class="btn btn-primary stripe-manage-btn btn-simple small-btn-mbt">Manage</a>
                                    <?php } ?>
                                 <img src="{{ asset('assets/images/chkst.png',env('HTTPS_STATUS')) }}" alt="check box icon" ></span>
                            </div>
                            <!-- <div class="col {{$WalletActive}}">Wallet Address <span><img
                                        src="{{ asset('assets/images/chkst.png',env('HTTPS_STATUS')) }}" alt="check box icon"></span>
                            </div> -->
                        
                            <div class="col strpbx3 {{$totalProductsActive}}"> Products <span>
                            <?php if ($totalProducts <= 0) { ?>
                            <a  class="btn btn-primary stripe-product-btn btn-simple small-btn-mbt" href="{{URL::to('/add-product')}}">Add Products</a>
                            <?php } ?>
                            <img
                                        src="{{ asset('assets/images/chkst.png',env('HTTPS_STATUS')) }}" alt="check box icon"></span>
                            </div>
                            <br>

                        </div>
                        <?php if ($companyActivecheck == false || $totalProductsActivecheck == false || $stripeActivecheck == false ) { ?>
                        <div class="text-center  small"><span class="red">Note:</span> Please ensure that all sections mentioned above are filled out. They are all necessary for your store to go live.
                        </div>
                        <?php } ?>
                    </div>

                 
                </div>
                <div class="col col-md-12 col-sm-12 col-12">
                    <div class="wdgtbx border" style="background-color:#eaeaf8; border:1px solid #eaeaf8">
                        <h6>Store Info
                            <!-- <a class="pull-right" href="<?php // echo URL::to('/store/edit'); ?>"><i
                                    class="fa fa-pencil" aria-hidden="true"></i></a> -->
                                </h6>
                        <div class="wdgcnt ">
                            <div class="dshord col-md-8 col-sm-12 col-12 no-padd-left">

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
                                        @if(!empty($imgeUrl)) 
                                        <img style=" " src="<?php echo $imgeUrl; ?>"
                                            alt="<?php echo $storeData->store_name; ?>"> 
                                        @endif 
                                </div>
                                                                                                
                                <div class="content-des-mbt display-table-cell verticl-top pl-20"> 
                                    <h4 class="storeName"><?php echo isset($storeData->store_name) ? ucwords($storeData->store_name) : ''; ?></h4>
                                    <div class="strinfbx">
                                    <div class="d-flex">
                                        <p>Member since:</p> 
                                        <p class="ml-1"> {{ date('F d, Y', strtotime($storeData->created_at)) }}
                                        </p>
                                    </div>
                                    <div class="">
                                        <p style="word-break: break-all;">Location: <?php echo isset($storeData->street_address) ? $storeData->street_address : ''; ?>
                                        </p>
                                    </div>
                                    <p class="mb-10 mt-0">
                                    <!-- <span>Contact:</span><span><?php //echo isset($storeData->contact_info) ? $storeData->contact_info : ''; ?></span></p> -->
                                    </div>
                                    <div class="mt-20">
                                        <div class="view-al static-position">
                                            <a class="small" href="<?php echo URL::to('/store/edit'); ?>">Manage</a> &nbsp; <a class="small" href="<?php echo URL::to('/brand/' . $storeData->store_slug); ?>"
                                            target="_blank">Preview Store</a>
                                        </div> 
                                    </div>
                                </div>

                            </div>
                             <div class="dshord col-md-4 col-sm-4 col-4">
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
                                    <p style="margin-bottom:10px;font-weight:bold;"><?php
                                        if (isset($userData->name)) {
                                            echo $userData->name;
                                        } else {  echo '';   }
                                        ?></p>

                    <a class="btn btn-primary btn-simple small-btn-mbt " href="<?php echo URL::to('user-dashboard') ?>">Manage Profile </a>    
                                </div>
                            </div>
                        </div>
                    </div>
                </div> 
                <div class="col col-md-6 col-sm-6 col-6 mb-30 hide-div">
                    <div class="white-background all-shahdow dashbrd-sec height100 ">
                        <div class="wdgtbx border height100 mb-0 white-background all-shahdow">
                            <div class="row justify-content-between">
                                <div class="col-sm-6">
                                    <h6>Store Status</h6>
                                </div>
                                <div class="col-sm-4 text-right ">
                                    <?php
                                    $activateMessage = true;
                                    $activeStatus = true;
                                    //   $stripeConnectedAccount = true;
                                    //$stripeSub['status'] == 'active'
                                    if ($totalProducts > 0 && $storeData->is_completed == '1' && aboutStore($storeData->store_id) == true) {
                                        $activeStatus = true;
                                    } else {
                                        $activeStatus = false;
                                        ?>
                                    <div class="noteactive"></div>
                                    <?php }
                                    ?>
                                    <?php
                                    if ($activeStatus == true) {
                                        if ($storeData->store_status == '2') {
                                            $word = "Deactive";
                                        } else {
                                            $word = "Active";
                                        }
                                        ?>
                                    <span class="check-status name-select-mbt"><?php echo $word; ?></span>
                                    <div class="checkbox-list mt-0 display-inline">
                                        <label class="container-checkbox switch-buttonk  simple-checkbox">
                                            <input type="checkbox" class="checked-store" name="storeactive"
                                                id="storeactive">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                    <?php } else { ?>
                                    <span class="check-status name-select-mbt">Deactive</span>
                                    <?php } ?>
                                </div>
                            </div>
                            <div class="checked-ticks adjust-sp">
                                <div>
                                    <span><?php if ($storeData->is_completed == '1') { ?>
                                        <i class="fa fa-check" aria-hidden="true"></i>
                                        <?php
                                        } else {
                                            $activateMessage = false;
                                            ?>
                                        <i class="fa fa-times" aria-hidden="true"></i>
                                        <?php } ?>
                                        Company Profile</span>
                                    <span><i class="fa fa-check" aria-hidden="true"></i> Shipping</span>
                                </div>
                                <div>
                                    <span><?php if ($totalProducts > 0) { ?><i class="fa fa-check"
                                            aria-hidden="true"></i>
                                        <?php
                                                                               } else {
                                                                                   $activateMessage = false;
                                                                                   ?>
                                        <i class="fa fa-times" aria-hidden="true"></i>
                                        <?php
                                        }
                                        ?>
                                        Products

                                    </span>
                                    <span><i class="fa fa-check" aria-hidden="true"></i> Payment</span>
                                    <?php
                                    $res = aboutStore($storeData->store_id);

                                    if ($res == true) {
                                        ?>

                                    <span><i class="fa fa-check" aria-hidden="true"></i> About us</span>
                                    <?php
                                    } else {
                                        $activateMessage = false;
                                        ?>

                                    <span><i class="fa fa-times" aria-hidden="true"></i> About us</span>
                                    <?php } ?>

                                </div>

                                <div>
                                    <span><i class="fa fa-times" aria-hidden="true"></i> USDC Account </span>
                                    <?php // if ($stripeSub['status'] == 'active') {    ?>
                                    <span class="ml-0 mb-0 "><i class="fa fa-check" aria-hidden="true"></i> Subscription
                                        <p class="ligt-grey mb-0 mt-15 fnt12">Note:Your subscription package is
                                            "<?php //echo $stripeSub['name'];              ?>
                                            " and it will expire on
                                            <?php //echo $stripeSub['current_period_end'];             ?></p></span>
                                    <?php //} else {     ?>
                                    <span class="ml-0 mb-0"><i class="fa fa-times" aria-hidden="true"></i>Subscription
                                        <p class="ligt-grey mb-0">Note:Your subscription package was
                                            "<?php // echo $stripeSub['name'];             ?>
                                            " and it expired on
                                            <?php // echo $stripeSub['current_period_end'];              ?></p></span>
                                    <?php // }    ?>


                                    <?php
                                    //   } else {
                                    ?>
                                    <span><i class="fa fa-times" aria-hidden="true"></i> Subscription </span>
                                    <?php
                                    //  }
                                    ?>

                                </div>
                                <?php
                                //  if (Session::has('response_array')) {
                                // $response_array = Session::pull('response_array');
                                //  if (isset($response_array['success'])) {
                                //                                    echo '<div class="alert alert-success" style="top:0px;">
                                //  <strong>Success!</strong>' . $response_array['success'] . '
                                //</div>';
                                // } if (isset($response_array['error'])) {
                                ?>
                                <div class="alert alert-danger" style="top:0px;">
                                    <strong>Error!</strong>
                                    <?php //echo $response_array["error"];  ?>
                                </div>
                                <?php
                                // }
                                // } if (!$stripeConnectedAccount) {
                                // $activateMessage = false;
                                ?>
                                <div class="view-al static-position mb-15 mt-10">
                                    <a href="<?php //echo $connectAccount;            ?>" class="button btn ">Connect
                                        Your stripe
                                        Account</a>
                                </div>
                                <?php
                                //   }
                                //if ($stripeConnectedAccount != false && $stripeSub == false) {
                                //$activateMessage=false;
                                ?>
                                {{--<a href="{{route('stripe-pans')}}" class="button btn">Subscribe</a>--}}

                                <?php // }   ?>

                            </div>
                            <?php //if ($activateMessage == false) {   ?>
                            <p class="ligt-grey mb-0">Note: Please ensure that the all the sections mentioned above are
                                filled out before the store can be turned on.</p>
                            <?php //}    ?>
                        </div>
                    </div><!-- white-background -->
                </div>
                <div class="col col-md-6 col-sm-12 col-12">
                    <div class="wdgtbx border minHeight-361 mb-0 white-background all-shahdow">
                        <h6>Reports</h6>

                        <div class="row">
                            <div class="col-4 col-sm-4 col-4"><img src="{{asset('assets/images/report-img.png')}}" alt="wallet" title="wallet" width="100%"></div>
                                <div class="col-8 col-sm-8 col-8"><div class="wdgcnt wdgnnt-btn-width"> 
                                <div class="dshord ordersBlock">
                                <p style="display: flex; justify-content: space-between;">Today's Sale: <b>${{$todaySales}}</b></p>
                                <p style="display: flex; justify-content: space-between;">This Week's Sale: <b>${{$thisWeekSales}}</b></p>
                                <p style="display: flex; justify-content: space-between;">Last Month's Sale: <b>${{$lastMonthSales}}</b></p>
                                    <br>
                                   
                            <div class="view-al text-center">
                                <a href="{{URL::to('/reports')}}">View Reports</a>
                            </div>
                           
                            </div>
                                </div>

                            </div>
                                    
                        </div>


                        
                        

                    </div>
                </div>
 




                <div class="col col-md-6 col-sm-12 col-12">
                    <div class="wdgtbx border minHeight-361 mb-0 white-background all-shahdow">
                        <h6>Products</h6>

                        <div class="row">
                            <div class="col-4 col-sm-4 col-4"><img src="{{asset('assets/images/products-img.png')}}" alt="wallet" title="wallet" width="100%"></div>
                            <div class="col-8 col-sm-8 col-8"><div class="wdgcnt wdgnnt-btn-width">

                                    <div style="margin-top: -25px; text-align:center ">

                                        <h4 class="mt-15 fnt-16">You have <?php echo $totalProducts;
                                            // if ($activeProducts == 0) {
                                            //     echo "no";
                                            // } else {
                                            //     echo $activeProducts;
                                            // }
                                            //
                                            // if ($activeProducts > 1) {
                                            //     echo "products";
                                            // } else {
                                            //     echo "product";
                                            // }
                                            ?> products <br><?php if ($totalProducts > 0) { ?>
                                            <!-- <a href="{{URL::to('/store-products')}}" class="hide-button btn btn-primary btn-simple small-btn-mbt mt-3 view-product-btn ">View Products</a> -->
                                            <?php } ?>
                                        </h4>
                                        @env('production')
                                            <style>
                                                .production-disable {
                                                    display: none;
                                                }
                                            </style>
                                        @endenv
                                        <?php if ($totalProducts >= 1) { ?>
                                        <a href="{{URL::to('/add-product')}}" class="btn btn-primary btn-simple hide-button">Add
                                            More Products</a>
                                            <a href="{{URL::to('/woocommerce')}}" class="btn btn-primary btn-simple hide-button mt-2 shopify-import-btn production-disable">Woocommerce Import</a>
                                            <a href="{{URL::to('/importshopify')}}" class="btn btn-primary btn-simple hide-button mt-2 shopify-import-btn production-disable">Shopify Import</a>
                                            <a href="{{URL::to('/shopify-csv')}}" class="btn btn-primary btn-simple hide-button mt-2 shopify-import-btn production-disable">Import Shopify Csv</a>

                                        <?php } else { ?>
                                            
                                        <a href="{{URL::to('/add-product')}}" class="btn btn-primary btn-simple hide-button">Add
                                            your first Product</a>
                                            <a href="{{URL::to('/woocommerce')}}" class="btn btn-primary btn-simple hide-button mt-2 shopify-import-btn production-disable">Woocommerce Import</a>
                                            <a href="{{URL::to('/importshopify')}}" class="btn btn-primary btn-simple hide-button mt-2 shopify-import-btn production-disable">Shopify Import</a>
                                            <a href="{{URL::to('/shopify-csv')}}" class="btn btn-primary btn-simple hide-button mt-2 shopify-import-btn production-disable">Import Shopify Csv</a>

                                        <?php } ?>
                                        <p class="view-al text-center " style="margin-top: 20px;margin-bottom:0px;">
                                        <?php if ($totalProducts >= 1) { ?>
                                        <a href="{{URL::to('/store-products')}}">Manage</a>
                                        <?php } ?>
                                        </p>
                                    </div>
                                     
                                </div>

                            </div>
                                    
                        </div>


                        
                        

                    </div>
                </div>
























                <div class="col col-md-6 col-sm-12 col-12">
                    <div class="wdgtbx border minHeight-361 mb-0 white-background all-shahdow">
                        <h6>Orders
                             <!-- <a class="pull-right" href="<?php // echo URL::to('/brand-orders') ?>"><i
                                    class="fa fa-pencil" aria-hidden="true"></i></a> -->
                        </h6>
                        <div class="row">
                        <div class="col-4"><img src="{{asset('assets/images/orders-img.png')}}" alt="wallet" title="wallet" width="100%"></div>
                                    <div class="col-8"> <div class="wdgcnt orders-manage-btn"> 

                            <div class="dshord ordersBlock">
                                <p class="Dashboard-order-detail">Orders Completed: <b><?php
                                    $count = getOrdersCount($storeData->store_id, $role = 'store', $status = 'completed');
                                    echo $count;
                                    ?></b> </p>
                                <p class="Dashboard-order-detail">Cancellation Requests: <b><?php
                                    $count = getOrdersCount($storeData->store_id, $role = 'store', $status = 'cancelled');
                                    echo $count;
                                    ?></b></p>
                                <p class="Dashboard-order-detail">Orders Pending: <b><?php
                                    $count = getOrdersCount($storeData->store_id, $role = 'store', $status = 'pending');
                                    echo $count;
                                    ?>
                                    </b></p>
                                    <br>
                                   
                            <div class="view-al text-center">
                                <a href="<?php echo URL::to('/brand-orders'); ?>">Manage</a>
                            </div>
                           
                            </div>
                           

                        </div>
                        </div>
                                  </div>          

                       
                    </div>

                </div>
                <?php 
                if($storeData->store_status  == 2){
                    
              
                ?>
                <!-- <div class="col-md-6 mb-15">
                    <div class="wdgtbx border mb-0 height100 white-background all-shahdow">
                        <h6>Shipping <a class="pull-right" href="<?php echo URL::to('/edit-shipping-address'); ?>" class="disable-link"><i class="fa fa-pencil"
                                    aria-hidden="true"></i></a>
                        </h6>
                        <div class="wdgcnt">
                            <div class="dshshp hide-div">
                                <img class="border" src="{{ asset('assets/images/ship1.jpg',env('HTTPS_STATUS')) }}" alt="zotzot" title="zotzot"> 
                                <img class="border" src="{{ asset('assets/images/ship2.jpg',env('HTTPS_STATUS')) }}" alt="zotzot" title="zotzot">
                                <img class="border" src="{{ asset('assets/images/ship3.jpg',env('HTTPS_STATUS')) }}" alt="zotzot" title="zotzot">
                                <img class="border" src="{{ asset('assets/images/ship4.jpg',env('HTTPS_STATUS')) }}" alt="zotzot" title="zotzot">
                            </div>
                            <div class="shippint-method-mbt">
                                <h4 class="mt-15 fnt-16 custom-style-heading">Active Shipping Methods</h4>
                                <p>Flat Shipping</p>
                                <p>Local Pickup</p>
                                <p>Mover</p>
                            </div>

                        </div>
                        <div class="view-al text-center">
                            <a href="<?php echo URL::to('/edit-shipping-address'); ?>">Manage</a>
                        </div>

                    </div>
                </div> -->
                    <?php  } ?>
                <div class="col col-md-6 col-sm-12 col-12 mb-15 mto-10">

                    <div class="wdgtbx border mb-0 minHeight-361 white-background all-shahdow">
                    <div class="row">
                    <div class="col-12">
                        <?php
                          if (isset($storeData) && $storeData->stripe == '0') { ?>
                       
                        <h6 class="ml-1">Connect your bank account 
                            <?php
                          }else{
                            ?>
                              <h6 class="ml-1">Connect your bank account  
                                <?php } ?>
                            <!-- <a class="pull-right" href="{{URL::to('/store/payments/')}}"><i
                                    class="fa fa-pencil" aria-hidden="true"></i></a> -->
                        </h6>
                    </div>    
                    <div class="col-4"><img src="{{asset('assets/images/bank-img.png')}}" alt="wallet" title="wallet" width="100%"></div>
                    <div class="col-8">
                        <div style="min-height: 200px;">
                      
                        <?php
                        

                        if (isset($storeData) && $storeData->stripe == '0') {
                            $stripeIcon = 'fa-close';
                            ?>

                            <h4 class="mt-15 fnt-14 account-text-height text-center " style="color:#2f2f2f!important; max-width:250px; margin:auto"> <p style="font-size:38px!important; color:#ffc107;"><i class="fa fa-exclamation-triangle" aria-hidden="true"></i></p> You do not have an active bank account attached with our payment processor. Mallshark needs an active bank account to release your payments when your products are sold. Please click on the button below.</h4 >
                        <?php } else {
                            $stripeIcon = 'fa-check';
                            ?>
                            <h4  class=" fnt-14 account-text-height text-center" style="color:#2f2f2f!important;max-width:250px; margin:auto"> <p style="font-size:38px!important; color:#47a647;"><i class="fa {{$stripeIcon}}" aria-hidden="true"></i></p>  Bank Account is currently connected with MallShark's partner, Stripe.</h4 >
                            <?php
                        }
                        ?>
                        <div class="view-al text-center product-manage-btn">
                                <a href="{{URL::to('/store/payments/')}}">Manage</a>
                        </div>
                        </div>   
                        </div> 
                    </div>
                        
                        
                     
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

                    
                       

                    </div>
                </div>
                <div class="col col-md-6 col-sm-12 col-12 mb-15 mto-10" style="display: none;">
                    <div class="wdgtbx border mb-0 minHeight-361 white-background all-shahdow">
                        <h6> Connect your ERC20 Wallet
                            <!-- <a class="pull-right" href="{{URL::to('/store/paidby/')}}"><i
                                    class="fa fa-pencil" aria-hidden="true"></i></a> -->
                        </h6>
                        <div class="row">
                        <div class="col-5"><img src="{{asset('assets/images/wallet.png')}}" alt="wallet" title="wallet" width="100%"></div>
                        <div class="col-7"><?php
                     
                        

                            if (isset($storeData) && $storeData->wallet_address == '') {
                                // die('called');
                                $stripeIcon = 'fa-close';
                                ?>

                                <h4 class="mt-15 fnt-14 account-text-height  text-center" style="color:#2f2f2f!important;"><p style="font-size:38px !important; color:#ffc107;"><i class="fa fa-exclamation-triangle" aria-hidden="true"></i></p> You do not have a Wallet Connected with Mallshark. You cannot receive Crypto payments at the moment. Please click on the button below and add your ERC20 wallet Address. If you want to learn more about ERC20 wallets
                                <a href="https://coindcx.com/blog/cryptocurrency/top-9-erc20-wallets-2022/" class="click-here-btn-wallet" >click here</a>    
                            </h4>
                                 
                            <?php } else {
                                $stripeIcon = 'fa-check';

                                ?>
                                <h4 class="mt-15 fnt-14 account-text-height  text-center" style="color:#2f2f2f!important;"><p style="font-size:38px!important; color:#47a647"><i class="fa {{$stripeIcon}}" aria-hidden="true"></i></p> Your wallet is currently connected with MallShark for crypto payments.</h4>
                                <?php
                            } 
                            ?> <div class="view-al text-center product-manage-btn">
                            <a href="{{URL::to('/store/paidby/')}}">Manage</a>
                        </div></div>
                        </div>
                        

                        <!-- <h4 class="mt-15 fnt-16">Active Payment Method</h4> -->

                        <!-- <div class="checked-ticks mt-0">
                            <div>
                                <span class="fnt-16 mb-0"><i class="fa {{$stripeIcon}}" aria-hidden="true"></i>
                                    <span class="zit-lrge">
                                        Wallet Address
                                    </span>
                                    <i class="fa fa-info" data-toggle="tooltip" title="USD Coin (USDC) is an asset backed stablecoin whose value is pegged to the U.S. Dollar (USD). Every USDC token in circulation is backed by $1 USD in cash or cash equivalents, such as short-term U.S. Treasury bonds held by regulated U.S. financial institutions."></i>

                                </span>
                                <span></span>
                            </div>
                        </div> -->

                     
                       

                    </div>
                </div>
            </div>

            <div class="col-md-6 mb-15" style="display:none">
                    <div class="wdgtbx border mb-0 height100 white-background all-shahdow">
                        <h6>Get Bank Info <a class="pull-right" href="{{URL::to('/store/bankdetail/')}}"><i
                                    class="fa fa-pencil" aria-hidden="true"></i></a>
                        </h6>
                        <?php $stripeIcon = 'fa-check'; ?>

                        <!-- <h4 class="mt-15 fnt-16">Active Payment Method</h4> -->

                        <div class="checked-ticks mt-0">
                            <div>
                                <span class="fnt-16 mb-0"><i class="fa {{$stripeIcon}}" aria-hidden="true"></i>
                                    <span class="zit-lrge">
                                        Enter Your Bank 
                                    </span>

                                </span>
                                <span></span>
                            </div>
                        </div>

                        <div class="wdgcnt ">
                       
                            <div class="checked-ticks">
                                <div>
                                    <span><i class="fa fa-check" aria-hidden="true"></i> Stripe</span>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

            <div class="col-md-6 hide-div">
                <div class="wdgtbx border">
                    <h6>Account<a class="pull-right" href="#"><i class="fa fa-pencil" aria-hidden="true"></i></a></h6>
                    <div class="wdgcnt">
                        <div class="dshacct">
                            <p>Email: Johanmark@gmail.com></p>
                            <p><a href="#">Change Password</a></p>
                            <p><a href="#">Change Biling Address</a></p>
                            <p><a href="#">Change Shipping Address</a></p>
                        </div>
                    </div>
                </div>
            </div>


        </div>
    </div>

    </div>
    <div id="loading" class="show-setion">
        <img id="loading-image" src="{{asset('assets/images/Loader-Animation.gif')}}" alt="Loading..." />
    </div>
    <?php
    //    $arr=array();
    //    $storeSales=getYearlyOrdersOfStore();
    //    for ($i=0; $i<=12; $i++){
    //    if(array_key_exists($i,$storeSales)){
    //    $arr[$i]=$storeSales[$i];
    //    }else{
    //    $arr[$i]=0;
    //    }
    //    }
    ?>
</main>


<script>
     $('#loading').hide();
var message = "";
jQuery(document).on('click', '.management-payment', function() {
    swal("Payment Methods!", "We support USDC as the only method for payment at this moment");
});

$(document).on("click", "#switch_active", function() {
    var store_stripe = $(this).attr('data-stripe');
    var store_complete = $(this).attr('data-company');
    var store_totalProducts = $(this).attr('data-totalProduct');

    if(store_stripe != 1 || store_complete != 1 || store_totalProducts < 10){
        
        toastr.options = {
        closeButton: true,
        tapToDismiss: true,
        positionClass: 'toast-top-right',
        toastClass: 'toast-icon custom-toast-success',
        progressBar: true,
        };
        toastr.error("Failed! Complete your store setup to activate");
        return;
    }
    
    var res = {}
    var storeStatus = $(this).val();
    res['store_status'] = storeStatus;
    // var result = confirm("Are you sure to change Store Status");
    swal({
            title: "Are you sure?",
            text: "Are you sure you want to change the status of your store?",
            type: "warning",
            showCancelButton: true,
            confirmButtonClass: "btn-success",
            confirmButtonText: "Yes, Do it!",
            closeOnConfirm: false
        },
        function() {
            $('#loading').show();
            swal.close();

            $.ajax({
                type: 'POST',
              //  dataType: 'json',
                url: '/store-status/change',
                data: res,
                success: function(response) {
                    
                    if(response.status){
                        $('#loading').hide();
                        $(".store_note").addClass("d-none");
                      
                         $(".switch-field").removeClass("inactive-state");
                         $(".switch-field").addClass("active-state");
                        toastr.options = {
                        closeButton: true,
                        tapToDismiss: true,
                        positionClass: 'toast-top-right',
                        toastClass: 'toast-icon custom-toast-success',
                        progressBar: true,
                        };
                        toastr.success(response.message);
                       
                    }else{
                        $('#loading').hide();
                        toastr.options = {
                        closeButton: true,
                        tapToDismiss: true,
                        positionClass: 'toast-top-right',
                        toastClass: 'toast-icon custom-toast-success',
                        progressBar: true,
                        };
                        toastr.error(response.message);
                    } 
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                },
            });
        });

});
$(document).on("click", "#switch_inactive", function() {
    var res = {};
    var storeStatus = $(this).val();
    res['store_status'] = storeStatus;
    swal({
            title: "Are you sure?",
            text: "Are you sure you want to change the status of your store?",
            type: "warning",
            showCancelButton: true,
            confirmButtonClass: "btn-success",
            confirmButtonText: "Yes, Do it!",
            closeOnConfirm: false
        },
        function() {
            $('#loading').show();
            swal.close();

            $.ajax({
                type: 'POST',
                url: '/store-status/change',
                data: res,
                success: function(response) {
                    
                    if(response.status){
                        $('#loading').hide();
                        $(".store_note").removeClass("d-none");
                        $(".switch-field").removeClass("active-state");
                        $(".switch-field").addClass("inactive-state");
                        toastr.options = {
                        closeButton: true,
                        tapToDismiss: true,
                        positionClass: 'toast-top-right',
                        toastClass: 'toast-icon custom-toast-success',
                        progressBar: true,
                        };
                        toastr.success(response.message);
                       
                    }else{
                        $('#loading').hide();
                        toastr.options = {
                        closeButton: true,
                        tapToDismiss: true,
                        positionClass: 'toast-top-right',
                        toastClass: 'toast-icon custom-toast-success',
                        progressBar: true,
                        };
                        toastr.error(response.message);
                    } 
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                },
            });



            // sendreq(res, 'store-status', 'change', 'callbacksutatusError');
            
          

        });
    /*
     if (result == true) {
     
     } else {
     // return false;
     }
     */
});

function callbacksutatusSuccess() {
    $("#successMessage").html(message);
    $("#successMessage").show();
    $("#error_message").hide();

    $(".switch-field").removeClass("inactive-state");
    $(".switch-field").addClass("active-state");
    setTimeout(function() {
        jQuery("#successMessage").fadeOut('slow')
    }, 3000);
}
function callbacksutatusError() {
    $("#error_message").html(message);
    $("#error_message").show();
    $("#successMessage").hide();
    setTimeout(function() {
        jQuery("#errorMessage").fadeOut('slow')
    }, 3000);
}
</script>

@endsection