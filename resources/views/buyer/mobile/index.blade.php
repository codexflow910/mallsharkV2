@extends('layouts.mobile-main')
@section('content')
@section('title', 'User dashboard')
<link href="https://cdn.jsdelivr.net/gh/gitbrent/bootstrap4-toggle@3.5.0/css/bootstrap4-toggle.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/gh/gitbrent/bootstrap4-toggle@3.5.0/js/bootstrap4-toggle.min.js"></script>
<main>

    <!-- Menu Mobile Start -->
 @include('buyer.mobile.sidebar')
    <!-- Menu Mobile Ends -->



    <div class="dshbrdin buyer">
        <div class="container">
            <div class="row"> 
               <div class="col-md-6">
                    <div class="wdgtbx border">
                        <h6>User Info<a class="pull-right" href="{{URL::to('/user-account')}}"><i class="fa fa-pencil"
                                                                                                  aria-hidden="true"></i></a>
                        </h6>
                        <div class="wdgcnt ">
                            <div class="dshord">
                                <div class="usr">
                                    <?php
                                    $image_url = '';
                                    if ($userData['user_avatar'] != '') {
                                        $image_url = getAttachmentUrlById((int) $userData['user_avatar'], 'small');
                                        ?>
                                        <!--                                    {{asset('assets/images/user-img.jpg',env('HTTPS_STATUS'))}}-->
                                        <p><img class="rounded-circle" src="<?php echo $image_url; ?>" alt="user icon" title="user icon"></p>
                                    <?php } else { ?>
                                        <p class="image-outer-mbt">
                                            <?php
                                            if (!empty($userData)) {
                                                $name = "";
                                                $username = $userData['name'];
                                                $words = preg_split("/\s+/", $username);
                                                foreach ($words as $w) {
                                                    //$name .= strtoupper($w[0]);
                                                }
                                                ?>
                                            <?php }
                                            ?>
                                            <span class="user-image">
                                                <span class="head-upper-mbt">
                                                    <span class="login-name"><?php //echo $name;   ?></span>
                                                </span>
                                            </span>
                                        </p>
                                    <?php } ?>
                                </div>
                                <div class="shp">
                                    <p>Member since: {{ date('F d, Y', strtotime($userData['created_at'])) }}</p>
                                    <?php if (!empty($userData['physical_address'])) { ?>
                                        <?php echo $userData['physical_address']; ?>
                                    <?php }
                                    ?>
                                    <p><?php echo ($userData['user_city_id']) ? $userData['user_city_id'] . " ," : "";
                                    ?> <?php
                                        if (isset($userData['user_state_id'])) {
                                            $stateData = getStatebyId($userData['user_state_id']);
                                            // print_rr($stateDta);
                                            echo ' ' . $stateData['state_name'];
                                        } else {
                                            echo "";
                                        }
                                        ?>  <?php
                                        if (isset($userData['user_country_id'])) {
                                            $country = getCountryById($userData['user_country_id']);
                                            echo $country['name'];
                                        } else {
                                            echo "";
                                        }
                                        ?></p>
                                    <!--                                    <p>Supporting: 4 Products</p>   -->
                                    <!-- <a href="/user-profile">View Profile</a> -->
                                </div>
                            </div>

                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="wdgtbx border" style="background-color:#f0f0f0">
                        <h6>Account<a class="pull-right" href="{{URL::to('user-info')}}"><i class="fa fa-pencil" aria-hidden="true"></i></a></h6>
                        <div class="wdgcnt">
                            <div class="dshacct">
                                 <p>Email: @if(!empty($userData['email']))
                                    {{ $userData['email']}}
                                    @endif</p>
                                <p><a href="{{URL::to('/user-info')}}">Change Password</a></p>
                                <?php if(!empty($shippingInfo)) { ?>
                                <p><a href="{{URL::to('/edit-shipping-address')}}">Change Shipping Address</a></p>   
                                <?php } ?>
                            </div>   
                        </div>      
                    </div>  
                </div>

                <div class="col-md-6">
                    <div class="wdgtbx border" >
                        <h6>Favorites</h6>
                        <div class="wdgcnt">
                            <div class=" dshprdbx ">
                                <p>You have <?php
                                    if ($favorites == 0) {
                                        echo "no item";
                                    } else if ($favorites > 1) {
                                        echo $favorites . " items";
                                    } else {
                                        echo $favorites . " item";
                                    }
                                    ?> in your favorite list&nbsp;&nbsp;</p>
                                <?php if ($favorites > 0) { ?>
                                    <a class="btn btn-primary btn-simple" href="<?php echo URL::to('/buyer-favorites'); ?>">View
                                        Favorites</a>
                                <?php } ?>
                            </div>    
                        </div>      
                    </div>    
                </div>

                <div class="col-md-6">
                    <div class="wdgtbx border" style="background-color:#f0f0f0">
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
                                    $count = getOrdersCount($userData['user_id'], $role = 'buyer', $status = 'pending');
                                   
                                    ?>
                                    <?php if ($count > 0) { ?>
                                        &nbsp;&nbsp;<a href="{{URL::to('/buyer-orders?status=pending')}}">Check
                                            Status</a>
                                        <?php
                                    } else {
                                        echo 0;
                                    }
                                    ?>  </p>
                            </div>    
                        </div>      
                    </div>    
                </div>
                <?php if(!empty($shippingInfo)) { ?>
                <div class="col-md-6">
                    <div class="wdgtbx border">
                        <h6>Shipping Address <a class="pull-right" href="{{URL::to('/edit-shipping-address')}}"><i
                                    class="fa fa-pencil" aria-hidden="true"></i></a></h6>
                        <div class="wdgcnt">
                            <div class="dshshp text-center">
                                <br>
                                <?php if (!isset($shippingInfo) && $shippingInfo != '') { ?>
                                    <p><b>You have not set any shipping address</b>
                                    <?php } else { ?>
                                    <p><b>Shipping Address:</b> <br> <?php
                                        if (isset($shippingInfo['street'])) {
                                            echo ($shippingInfo['street']) ? $shippingInfo['street'] . " " : "";
                                        }
                                        ?><?php
                                        if (isset($shippingInfo['shipping_state']['state_name'])) {
                                            echo ($shippingInfo['shipping_state']['state_name']) ? $shippingInfo['shipping_state']['state_name'] . "  " : "";
                                        }
                                        ?>
                                        <?php
                                        if (isset($shippingInfo['shipping_country']['name'])) {
                                            echo $shippingInfo['shipping_country']['name'];
                                        }

                                        //echo($shippingInfo['shipping_country'][0]['country_name']) ? $shippingInfo['shipping_country'][0]['country_name'] . "  " : "";
                                    } ?> </p>
                            </div>
                        </div> 
                    </div>    
                </div>  
                <?php } ?>
                <div class="col-md-6 ">
                    <div class="wdgtbx border h-95">
                        <h6>Notifications</h6>
                        <!--                        <div class="wdgcnt">-->
                        <!--                            <div class="dshord">-->
                        <?php
                        $smschecked = "";
                        $emailchecked = "";

                        if (isset($userData['sms_alerts']) && ($userData['sms_alerts'] == 1)) {
                            $smschecked = 'checked';
                        }
                        if (isset($userData['email_alerts']) && $userData['email_alerts'] == 1) {
                            $emailchecked = 'checked';
                        }
                        ?>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-check pl-0 toggle-mbt-button">
                                    <label for="sms_alert" class="mr-10">Sms notification</label>
                                    <input id="sms_notification" name="sms_alert" type="checkbox" data-toggle="toggle" data-size="small" data-style="slow" value="1" <?php echo $smschecked; ?>>
                                </div>
                            </div>   
                            <div class="col-md-6">
                                <div class="form-check pl-0 toggle-mbt-button">
                                    <label for="email_alert" class="mr-10">Email notification</label>
                                    <input id="email_notification" name="email_alert" type="checkbox" data-toggle="toggle"  data-size="small" data-style="slow"  value="1" <?php echo $emailchecked; ?>>
                                </div> 
                            </div>   
                        </div>
                        <div  class="form-group cat-contan " style="display: block;">
                            <label for="pwd">My Interests</label>
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
                                            <label class="form-check-label container-check">
                                                <input class="form-check-input catchek" type="checkbox" <?php echo $chk; ?>  value="<?php echo $cs->id; ?>" name="categories[]">
                                                <span class="checkmark"></span>
                                                <p><?php echo $cs->categoryTitle; ?></p>
                                            </label> 
                                        </div>  
                                        <?php
                                    }
                                }
                                ?>
                            </div>  
                            <div class="slct-cat"><span class="cat-select" style="cursor: pointer;">Select All</span><span class="text-center">
                                    |</span><span class="cat-deselect" style="cursor: pointer;">Deselect All</span><span class="text-center">
                                    |</span><span class="update_intrest" style="cursor: pointer;">Update Interest</span></div>

                        </div>
                    </div>

                    <!--                            </div>-->
                    <!--                        </div>-->
                </div>
            </div> 
        </div>
    </div> 
    <script>

$("#email_notification").on("change", function () {
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
            function () {
                var data = {};
                data['alert_status'] = mode;
                data['type'] = 'email_alerts';
                sendreq(data, 'user', 'change-status', 'callbackreq1');
                message = "Email notification status has been changed.";
                swal("Success!", message, "success");
                $('#email_notification').bootstrapToggle();
            });
});
$("#sms_notification").on("change", function () {
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
            function () {
                var data = {};
                var data = {};
                data['alert_status'] = mode;
                data['type'] = 'sms_alerts';
                sendreq(data, 'user', 'change-status', 'callbackreq2');
                message = "Sms notification status has been changed.";
                swal("Success!", message, "success");
            });
});
function callbackreq2() {}
function callbackreq1() {}
$(document).on("click", ".cat-select", function (e) {
    $(".catchek").prop('checked', 'checked');
});
$(document).on("click", ".cat-deselect", function () {
    $(".catchek").prop('checked', '');
});
$(document).ready(function () {
    var data = {};
    var myarr = [];
    $(".update_intrest").on("click", function () {
        if ($('input[name^="categories"]:checked').length > 0) {
            $('input[name^="categories"]:checked').each(function () {
                myarr.push($(this).val());
            });
            data['categories'] = myarr;
            sendreq(data, 'buyer', 'intrest', 'callbackBasket');
        } else {
            // swal("success","please select at least one category");
            swal("Error!", "please select at least one category!", "error");
        }
    });

});
function callbackBasket() {
    
    // swal("Success!", "Your Interest Updated Successfully!", "success");
    toastr.options = {
                                closeButton: true,
                                tapToDismiss: true,
                                positionClass: 'toast-top-right',
                                toastClass: 'toast-icon custom-toast-success',
                                progressBar: true,
                            };
                            toastr.success( "Your Interest Updated Successfully!");
}
$(".my_interests").on("click", function () {
    $(".cat-contan").toggle("slow");
});
</script>
</main>
@endsection