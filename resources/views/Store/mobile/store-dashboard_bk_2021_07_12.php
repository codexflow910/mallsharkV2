@extends('layouts.mobile-main')
@section('content')
@section('title', 'Store Dashboard')
<main>
    <?php
    $companyActivecheck = false;
    $totalProductsActivecheck = false;
    $stripeActivecheck = false;
    $companyActive = '';
    $totalProductsActive = '';
    $stripeActive = '';
    if (isset($storeData) && $storeData->is_completed == '1') {
        $companyActive = 'st2';

        $companyActivecheck = true;
    }
    if ($totalProducts > 0) {
        $totalProductsActive = 'st4';
        $totalProductsActivecheck = true;
    }
    $stripeConnectedAccount =true;
    if ($stripeConnectedAccount) {
        $stripeActive = 'st5';
        $stripeActivecheck = true;
    }
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

                <div class="col-md-12">
                    <div class="wdgtbx border">
                        <h6>Account Status</h6>
                        <div class="stbr">
                            <div class="col <?php echo $companyActive; ?>">Company<br/>Profile <span><img
                                        src="{{ asset('assets/images/chkst.png',env('HTTPS_STATUS')) }}"></span>
                            </div>
                            <div class="col {{$stripeActive}} ">Stripe <br/>Account <span><img
                                        src="{{ asset('assets/images/chkst.png',env('HTTPS_STATUS')) }}"></span>
                            </div>
                            <div class="col {{$totalProductsActive}}">Company<br/>Products <span><img
                                        src="{{ asset('assets/images/chkst.png',env('HTTPS_STATUS')) }}"></span>
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
                <?php if ($companyActivecheck && $totalProductsActivecheck && $stripeActivecheck) {
                    ?>



                    <div class="col-6">
                        <h6 class="store-status-mbt">Store Status</h6>
                    </div>
                    <?php
                    $class = "inactive-state";
                    if (isset($storeData) && $storeData->store_status == "2") {
                        $class = "active-state";
                    }
                    ?>

                    <div class="col-6 text-right">

                        <div class="switch-field <?php echo $class; ?>">
                            <input type="radio" id="switch_active" name="switch_2"
                                   value=2" <?php
                                   if (isset($storeData) && $storeData->store_status == "2") {
                                       echo "checked";
                                   } else {
                                       echo '';
                                   }
                                   ?>/>
                            <label for="switch_active">Active</label>
                            <input type="radio" id="switch_inactive" name="switch_2"
                                   value="0" <?php
                                   if (isset($storeData) && $storeData->store_status == "0") {
                                       echo "checked";
                                   } else {
                                       echo '';
                                   }
                                   ?>/>
                            <label for="switch_inactive">InActive</label>
                        </div>

                    </div>

                    <?php
                }
                ?>

                <!-- <div class="col-md-12">
                    <div class="wdgtbx border">
                        <h6>Company Info<a class="pull-right" href="//{{URL::to('store/edit')}}"><i class="fa fa-pencil" aria-hidden="true"></i></a></h6>
                        <div class="wdgcnt ">
                            </?php
                            $imgeUrl = "";
                            $storeIcon = "";
                            $storeData->store_logo;
                            if //(!empty($storeData->store_icon)) {
                                $image_id = getImageIdByIconId//((int) $storeData->store_icon);
                                if// (!empty($image_id))
                                    $storeIcon = getAttachmentUrlById//((int) $image_id, 'icons');
                           // }
                            if// (!empty($storeData->store_logo)) {
                           //     $imgeUrl = getAttachmentUrlById((int) $storeData->store_logo, 'small');
                           // }
                            ?>
                            <div class="dshord row ">
                                <div class="usr bsns col-4 no-gutters text-left">
                                    <p class="image-outer-mbt"> 
                                        <span class="head-upper-mbt bsns"> 
                               //             <span class="login-name"><img style="width:60px; height:60px" src="//{{$storeIcon}}"></span>
                                        </span> 
                                    </p>
                                </div>
                                <div class = "shp bsnsinf col-8 ">
                                   // <p><img style="max-height:60px" src="{{$imgeUrl}}" alt="{{$storeData->store_name}}"> </p>
                                  //  <p>Since: {{ date('F d, Y', strtotime($storeData->created_at)) }}</p> 
                                 //   <p></?php echo!empty($storeData->street_address) ? $storeData->street_address : ''; ?></p> 
                                    <p style="margin-top:10px"><a class="btn btn-primary btn-simple" href="{{URL::to('store/edit')}}">Edit</a></p>
                                </div>
                            </div>  
                            <div class="dshord row inp">
                                <div class="usr col-4 no-gutters text-left">
                                    <p class="image-outer-mbt">
                                        <span class="user-image">

                                            </?php
                                            $imageUrl = '';
                                          //  if ($userData->user_avatar != '') {
                                            //    $imageUrl = getAttachmentUrlById((int) $userData->user_avatar, 'small');
                                                ?>
                                                <span class="login-name"><img class="rounded-circle"
                                                                              src="</?php echo $imageUrl; ?>"
                                                                              alt="">
                                                </span>
                                        //        </?php
                                        //    } else {
                                           //     if (!empty($userData)) {
                                                    $name = "";

                                                    $username = $userData->name;
                                               //     $words = preg_split("/\s+/", $username);
                                                //    foreach ($words as $w) {
                                              //          $name .= strtoupper($w[0]);
                                                //    }
                                                    ?>
                                               //     <span class="login-name">{{$name}}</span>
                                                    <//?php
                                            //    }
                                          //  }
                                            ?>

                                        </span>
                                    </p>
                                </div>
                                <div class = "shp col-8">
                                  //  <p><b>{{!empty($userData->name)?$userData->name:''}}</b></p>

                                </div>
                            </div>


                        </div>    
                    </div>  
                </div> -->
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
                                &nbsp;&nbsp;<?php if ($totalProducts > 0) { ?>
                                    <a href="{{URL::to('/store-products')}}" class="hide-button">View Products</a>
                                <?php } ?>
                                <?php if ($totalProducts >= 1) { ?>
                                    <a href="{{URL::to('/add-product')}}" class="btn btn-primary btn-simple">Add
                                        More Products</a>
                                <?php } else { ?>
                                    <a href="{{URL::to('/add-product')}}" class="btn btn-primary btn-simple">Add
                                        your first Product</a>
                                <?php } ?>
                            </div>    
                        </div>      
                    </div>    
                </div>

                <div class="col-md-6">
                    <div class="wdgtbx border">
                        <h6>Orders</h6>
                        <div class="wdgcnt">
                            <div class="dshord">  
                                <p>Orders Completed: <?php
                                    $count = getOrdersCount($storeData->store_id, $role = 'store', $status = 'completed');
                                    echo $count;
                                    ?> </p>
                                <p>Orders Canceled: <?php
                                    $count = getOrdersCount($storeData->store_id, $role = 'store', $status = 'cancelled');
                                    echo $count;
                                    ?></p>
                                <p>Orders Pending:
                                    <?php
                                    $count = getOrdersCount($storeData->store_id, $role = 'store', $status = 'pending');
                                    echo $count;
                                    ?>
                                    <?php if ($count > 0) { ?>
                                        &nbsp;&nbsp;<a href="{{URL::to('/brand-orders?status=pending')}}">Check
                                            Status</a>
                                        <?php
                                    } else {
                                        //echo 0;
                                    }
                                    ?></p>
                                <P></P>
                            </div>    
                        </div>      
                    </div>    
                </div>

                <div class="col-md-6">
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
                    </div>    
                </div>  
                <div class="col-md-6">
                    <div class="wdgtbx border">
                        <h6>Payment</h6>
                        <?php
                        $stripeIcon = 'fa-check';

                        if (!$stripeConnectedAccount) {
                            $stripeIcon = 'fa-close';
                            ?>

                            <h4 class="mt-15 fnt-16">You don't have any active payment method</h4>
                        <?php } else {
                            ?>
                            <h4 class="mt-15 fnt-16">Active Payment Method</h4>
                            <?php
                        }
                        ?>
                        <div class="wdgcnt">
                            <div class="dshord"> 
                                <div>
                                    <span class="fnt-16 mb-0"><i class="fa {{$stripeIcon}}"
                                                                 aria-hidden="true"></i> Stripe</span>
                                    <span></span>
                                </div>
                                <br>
                            </div>    
                        </div>      
                    </div>    
                </div>
            </div> 
        </div>
    </div>    
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
                text: notification,
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