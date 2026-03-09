@extends('layouts.dashboard-main')
@section('content')
@section('title', 'User dashboard')
<link href="https://cdn.jsdelivr.net/gh/gitbrent/bootstrap4-toggle@3.5.0/css/bootstrap4-toggle.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/gh/gitbrent/bootstrap4-toggle@3.5.0/js/bootstrap4-toggle.min.js"></script>
<main>
    <!-- Dashboard Starts -->


    <div class="flex-parent-div bg-light-gray user-dashboard-ja ">

        <div class="change-password-page-cmt">
            @include('buyer.customsidebar')
        </div>

        <!-- Dashboard Ends -->
        <div class="dshbrdin buyer update-design home-section cm-edit-ja">
            <div class="container">

                <div class="row">
                    {{-- <div class="col-md-2"></div> --}}
                    <?php //print_rr($userData);
                    ?>
                    <div class="col-md-10">
                        {{-- <h1 class="account-heading">
                        
                        <div class="d-flex align-items-center gap-3">
                            <a href="javascript:void(0)" class="d-flex align-items-center gap-10px sidebar-admin-burger-menu" id="open-sidebar-admin-panel">                                        
                                            <span id="menu-toggle-dashboard" class="shopzit-menu mt-0 ml-0 bx-menu">
                                                <span></span><span></span><span></span> <span></span>
                                            </span>
                                            <!-- <span>Menu</span> -->
                                            </a>
                                            <span>Account</span>
                            </div>
                        
                        </h1> --}}
                        {{-- @include('buyer.sidebar') --}}
                        <h3 class="buyer-order-heading" >
                            <div id="loading" class="show-setion" style="display:none;">
                                <img id="loading-image" src="http://127.0.0.1:8000/assets/images/Loader-Animation.gif" alt="Loading...">
                            </div>
                            <div class="d-flex align-items-center gap-3 mbml-5" style="margin-top: 30px;">
            
                                <a href="javascript:void(0)" class="d-flex align-items-center gap-10px sidebar-admin-burger-menu" id="open-sidebar-admin-panel">
                                    <span id="menu-toggle-dashboard" class="shopzit-menu mt-0 ml-0 bx-menu">
            
                                        <span></span><span></span><span></span> <span></span>
                                    </span>
                                    <!-- <span>Menu</span> -->
                                </a>
            
                                <span>User Dashboard</span>
            
                            </div>
                            <!-- <div class="uploadImages" style="display: block;"> -->
                        </h3>
                        <div class="row" style="margin-bottom: 34px;">
                        <div class="col-md-6 d-flex;">
                            <div class="wdgtbx border user-information-box">
                                <h6>User Information
                                </h6>
                                <div class="wdgcnt ">
                                    <div class="dshord">
                                        <div class="usr">
                                            <?php
                                            //  echo 'Data: <pre>' .print_r($userData,true). '</pre>'; die();
                                        $image_url = '';
                                        if ($userData['user_avatar'] != '') {
                                            $image_url = getAttachmentUrlById((int) $userData['user_avatar'], 'small');
                                            ?>
                                            <!--                                    {{ asset('assets/images/user-img.jpg', env('HTTPS_STATUS')) }}-->
                                            <p><img class="rounded-circle" src="<?php echo $image_url; ?>"
                                                    alt="{{ $userData['name'] }}" title="{{ $userData['name'] }}"></p>
                                            <?php } else { ?>
                                            <p class="image-outer-mbt">
                                                <?php
                                               
                                                if (!empty($userData)) {
                                                    $name = "";
                                                    $username = $userData['name'];
                                                    $words = preg_split("/\s+/", $username);
                                                    foreach ($words as $w) {
                                                        $name .= strtoupper($w[0]);
                                                    }
                                                    ?>
                                                <?php }
                                                ?>
                                                <span class="user-image">
                                                    <span class="head-upper-mbt">
                                                        <span class="login-name"><?php echo $name; ?></span>
                                                    </span>
                                                </span>
                                            </p>
                                            <?php } ?>
                                        </div>
                                        <div class="shp">
                                            <p><b>Name:</b> <?php echo isset($userData['name']) ? $userData['name'] : ''; ?></p>
                                            <p><b>Shipping Address: </b><?php if (!empty($userData['physical_address'])) { ?>
                                                <span style="color: #183B56;font-size: 14px;">
                                                    <?php echo $userData['physical_address']; ?>
                                                    
                                                </span></p>
                                            
                                            <?php 

                                            $cityId = isset($userData['user_city_id']) ? $userData['user_city_id'] : '';
                                    }
                                        ?>
                                            <p style="color: #183B56">
                                                <?php
                                                echo isset($cityId) ? $cityId : '';
                                            
                                                if (isset($userData['user_state_id']) && !empty($userData['user_state_id'])) {
                                                    $stateData = getStatebyId($userData['user_state_id']);
                                                    echo isset($stateData['state_name']) ? ' ' . $stateData['state_name'] : '';
                                                }
                                            
                                                if (isset($userData['user_country_id']) && !empty($userData['user_country_id'])) {
                                                    $country = getCountryById($userData['user_country_id']);
                                                    echo isset($country['name']) ? ' ' . $country['name'] : '';
                                                }
                                            
                                                echo isset($userData['user_zip_code']) ? ' ' . $userData['user_zip_code'] : '';
                                                ?>
                                            </p>
                                            
                                            <p><b>Member since: </b>
                                                {{ date('F d, Y', strtotime($userData['created_at'])) }}</p>
                                               
                                            <div>
                                                <a class="edit-form-icon" href="<?php echo URL::to('/user-account'); ?>">
                                                    
                                                    <button class="btn btn-primary">Edit</button>
                                                    {{-- <img
                                                        src="{{ URL::asset('assets/images/edit.PNG') }}" alt="image"
                                                        srcset=""
                                                        style="height:25px; object-fit:cover;margin-top: -69px;"> --}}
                                                </a>
                                            </div>

                                            {{-- <a href="<?php // echo URL::to('/user-profile/' . $userData['user_id']);
                                            ?>">View Public Profile</a> --}}
                                            <br>
                                            <?php
                                        $userId = Session::get('userData')['user_id'];
                                        $storeData = getStoreDataByUserId($userId);
                                        if (!empty($storeData)) {
                                            ?>
                                            {{-- <a href="<?php echo URL::to('store-dashboard'); ?>" class="btn btn-primary btn-simple mt-5">Manage Store</a>    --}}
                                            <?php } ?>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>

                        <div class="col-md-6 d-flex">
                            <div class="wdgtbx border user-information-box referral-box" style="flex-grow:1;">
                                <span class="d-flex justify-content-between">
                                <h6>Referral Link
                                </h6>
                                <p style="cursor: pointer;" id="copyButton"><i class="fa fa-clipboard"></i> Copy URL</p>
                                </span>
                                    <div class="dshord">
                                    <div class="referral_code" style="margin-top: -5px;">
                                    <p id="urlInput">{{ URL::to('/signup?referred='.$userData['referral_code']) }}</p>
                                    </div>
                                    </div>
                            </div>
                        </div>
                        </div>
                        <div class="col-md-12 ">
                            <div class="wdgtbx border notifications-section">
                                <h6>Notification Settings </h6>
                                <!--                        <div class="wdgcnt">-->
                                <!--                            <div class="dshord">-->
                                <?php
                                $smschecked = '';
                                $emailchecked = '';
                                
                                if (isset($userData['sms_alerts']) && $userData['sms_alerts'] == 1) {
                                    $smschecked = 'checked';
                                }
                                if (isset($userData['email_alerts']) && $userData['email_alerts'] == 1) {
                                    $emailchecked = 'checked';
                                }
                                ?>
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-check pl-0 toggle-mbt-button notification-toggle-button">
                                            <label for="sms_alert" class="mr-10 notification-label">SMS
                                                Notifications</label>
                                            <input id="sms_notification" name="sms_alert" type="checkbox"
                                                data-toggle="toggle" data-size="small" data-style="slow" value="1"
                                                <?php echo $smschecked; ?>>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-check pl-0 toggle-mbt-button notification-toggle-button">
                                            <label for="email_alert" class="mr-10 notification-label">Email
                                                Notifications</label>
                                            <input id="email_notification" name="email_alert" type="checkbox"
                                                data-toggle="toggle" data-size="small" data-style="slow" value="1"
                                                <?php echo $emailchecked; ?>>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group cat-contan my-intrest-section " style="display: block;">
                                    <label for="pwd" class="label-intrest">My Interests</label>
                                    <div class="row catbox">
                                        <?php
                                    $useintrests = userIntrests();
                                    if (!empty($categories)) {
                                        foreach ($categories as $key => $cs) {
                                            $chk = "";
                                            if (in_array($cs->id, $useintrests)) {
                                                $chk = "checked";
                                            }
                                            ?>
                                        <div class="col-4">
                                            <label class="form-check-label container-check notification-check">
                                                <input class="form-check-input catchek" type="checkbox"
                                                    <?php echo $chk; ?> value="<?php echo $cs->id; ?>"
                                                    name="categories[]">
                                                <span class="checkmark"></span>
                                                <p><?php echo $cs->categoryTitle; ?></p>
                                            </label>
                                        </div>
                                        <?php
                                        }
                                    }
                                    ?>
                                    </div>
                                    <div class="slct-cat notofication-slct-cat"><span class="cat-select"
                                            style="cursor: pointer;">Select All</span><span class="text-center">
                                            |</span><span class="cat-deselect" style="cursor: pointer;">Deselect
                                            All</span><span class="text-center">
                                            |</span><span class="update_intrest" style="cursor: pointer;">Update
                                            Interest</span></div>

                                </div>
                            </div>

                            <!--                            </div>-->
                            <!--                        </div>-->
                        </div>
                    </div>

                    {{-- <div class="col-md-6">
                    <div class="wdgtbx border">
                        <h6>Account<a class="pull-right" href="{{URL::to('/user-info')}}"><i class="fa fa-pencil"
                                                                                             aria-hidden="true"></i></a>
                        </h6>
                        <div class="wdgcnt">
                            <div class="dshacct">
                                <p>Email: @if (!empty($userData['email']))
                                    {{ $userData['email']}}
                                    @endif</p>
                                <p><a href="{{URL::to('/user-info')}}">Change Password</a></p>
                                <!--                                <p><a href="#" class="disable-link">Change Biling Address</a></p> -->
    <!--                                <p><a href="{{URL::to('/edit-shipping-address')}}">Change Shipping Address</a></p>-->
                            </div>
                        </div>
                    </div>
                </div> --}}
                    {{-- <div class="col-md-6">
                    <div class="wdgtbx border">
                        <h6>Favorites</h6>
                        <div class="wdgcnt">
                            <div class=" dshprdbx ">
                                <p>You have <?php
                                if ($favorites == 0) {
                                    echo 'no item';
                                } elseif ($favorites > 1) {
                                    echo $favorites . ' items';
                                } else {
                                    echo $favorites . ' item';
                                }
                                ?> in your favorite list&nbsp;&nbsp;</p>
                                <?php if ($favorites > 0) { ?>
                                    <a class="btn btn-primary btn-simple" href="<?php echo URL::to('/buyer-favorites'); ?>">View
                                        Favorites</a>
                                <?php } ?>
                            </div>
                        </div>
                    </div>
                </div> --}}
                    {{-- <div class="col-md-6">
                    <div class="wdgtbx border">
                        <h6>Orders</h6>
                        <div class="wdgcnt">
                            <div class="dshord">
                                <p>Orders Completed: <?php
                                $count = getOrdersCount($userData['user_id'], $role = 'buyer', $status = 'completed');
                                echo $count;
                                ?> </p>
                                <p>Orders Canceled: <?php
                                $count = getOrdersCount($userData['user_id'], $role = 'buyer', $status = 'cancelled');
                                echo $count;
                                ?></p>
                                <p>Orders Pending: <?php
                                echo $count = getOrdersCount($userData['user_id'], $role = 'buyer', $status = 'pending');
                                ?>
                                <p>Orders Refund: <?php
                                $count = getOrdersCount($userData['user_id'], $role = 'buyer', $status = 'returned');
                                ?>
                                    <?php if ($count > 0) { ?>
                                        &nbsp;&nbsp;<a href="{{URL::to('/buyer-orders?status=pending')}}">Check
                                            Status</a>
                                        <?php
                                    } else {
                                        echo 0;
                                    }
                                    ?>  </p>

                                <?php /// echo $userData['pendingorders'];
                                ?>
                                <!--                                    &nbsp;&nbsp;<a href="{{URL::to('/buyer-orders')}}">Check Status</a>-->
                                <?php
                                //                                    } else {
                                //                                        echo 0;
                                //                                    }
                                ?>  </p>

                                <br>
                            </div>
                        </div>
                    </div>
                </div> --}}
                    <?php 
                
                //echo 'Data: <pre>' .print_r($shippingInfo,true). '</pre>'; 
                if(!empty($shippingInfo)) { ?>
                    {{-- <div class="col-md-6">
                    <div class="wdgtbx border mb-15">
                        <h6>Shipping <a class="pull-right" href="{{URL::to('/edit-shipping-address')}}"><i
                                    class="fa fa-pencil" aria-hidden="true"></i></a></h6>
                        <div class="wdgcnt">
                            <div class="dshshp text-left">
                                <?php if (!isset($shippingInfo) && $shippingInfo != '') { ?>
                                    <p><b>You have not set any shipping address</b>
                                    <?php } else { ?>
                                    <p><b>Shipping Address:</b> <br> <?php
                                    if (isset($shippingInfo['street'])) {
                                        echo $shippingInfo['street'] ? $shippingInfo['street'] . ' ' : '';
                                    }
                                    ?><?php
                                    if (isset($shippingInfo['shipping_state']['state_name'])) {
                                        echo $shippingInfo['shipping_state']['state_name'] ? $shippingInfo['shipping_state']['state_name'] . '  ' : '';
                                    }
                                    ?>
                                        <?php
                                        if (isset($shippingInfo['shipping_country']['name'])) {
                                            echo $shippingInfo['shipping_country']['name'];
                                        }
                                        
                                        //echo($shippingInfo['shipping_country'][0]['country_name']) ? $shippingInfo['shipping_country'][0]['country_name'] . "  " : "";
                                        
                                        ?> </p>
                                    <!--                                <p><b>Billing Address:</b> <br> Magnus Jepson, 566 Nowheresville, 3500 Stavanger, Norway</p>-->
                                </div>
                            <?php } ?>
                        </div>
                    </div>
                </div> --}}
                    <?php } ?>

                </div>

            </div>
        </div>
    </div>
    </div>
    <script>
        $("#email_notification").on("change", function() {
            var mode = $(this).prop('checked');
            swal({
                    title: "Are you sure?",
                    text: "Are you want to sure to change status?",
                    type: "warning",
                    showCancelButton: true,
                    confirmButtonClass: "btn-success",
                    confirmButtonText: "Yes, Do it!",
                    closeOnConfirm: false
                },
                function() {
                    swal.close();
                    var data = {};
                    data['alert_status'] = mode;
                    data['type'] = 'email_alerts';
                    sendreq(data, 'user', 'change-status', 'callbackreq1');
                    message = "Email notification status changed.";
                    swal("Success!", message, "success");
                    toastr.options = {
                                closeButton: true,
                                tapToDismiss: true,
                                positionClass: 'toast-top-right',
                                toastClass: 'toast-icon custom-toast-success',
                                progressBar: true,
                            };
                            toastr.success(message);
                    $('#email_notification').bootstrapToggle();
                });
        });
        $("#sms_notification").on("change", function() {
            var mode = $(this).prop('checked');
            swal({
                    title: "Are you sure?",
                    text: "Are you want to sure to change status?",
                    type: "warning",
                    showCancelButton: true,
                    confirmButtonClass: "btn-success",
                    confirmButtonText: "Yes, Do it!",
                    closeOnConfirm: false
                },
                function() {
                    swal.close();
                    var data = {};
                    var data = {};
                    data['alert_status'] = mode;
                    data['type'] = 'sms_alerts';
                    sendreq(data, 'user', 'change-status', 'callbackreq2');
                    message = "Sms notification status changed.";
                   
                    toastr.options = {
                                closeButton: true,
                                tapToDismiss: true,
                                positionClass: 'toast-top-right',
                                toastClass: 'toast-icon custom-toast-success',
                                progressBar: true,
                            };
                            toastr.success(message);
                });
        });

        function callbackreq2() {}

        function callbackreq1() {}
        $(document).on("click", ".cat-select", function(e) {
            $(".catchek").prop('checked', 'checked');
        });
        $(document).on("click", ".cat-deselect", function() {
            $(".catchek").prop('checked', '');
        });
        $(document).ready(function() {
            var data = {};
            var myarr = [];
            $(".update_intrest").on("click", function() {
                if ($('input[name^="categories"]:checked').length > 0) {
                    $('input[name^="categories"]:checked').each(function() {
                        myarr.push($(this).val());
                    });
                    data['categories'] = myarr;
                    sendreq(data, 'buyer', 'intrest', 'callbackInterest');
                } else {
                    // swal("success","please select at least one category");
                    swal("Error!", "please select at least one category!", "error");
                }
            });

        });

        function callbackInterest() {
            toastr.options = {
                                closeButton: true,
                                tapToDismiss: true,
                                positionClass: 'toast-top-right',
                                toastClass: 'toast-icon custom-toast-success',
                                progressBar: true,
                            };
                            toastr.success( "Your Interest Updated Successfully!");
        }
        $(".my_interests").on("click", function() {
            $(".cat-contan").toggle("slow");
        });

        $(document).ready(function () {
            $("#copyButton").click(function () {
    var urlInput = document.getElementById("urlInput");
    var range = document.createRange();
    range.selectNode(urlInput);
    window.getSelection().removeAllRanges();
    window.getSelection().addRange(range);
    document.execCommand("copy");
    $(this).text("Copied!");
    setTimeout(function () {
        $("#copyButton").html('<i class="fa fa-clipboard"></i> Copy URL');
    }, 2000);
});
        });
    </script>
</main>
@endsection
