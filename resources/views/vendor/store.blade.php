@extends('layouts.new-main')
@section('content')
@section('title', 'Store Dashboard')
<main>
    @include('vendor.sidebar')
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
            top: 142px;
        }

    </style>
    <?php

   // print_rr($storeData);
    ?>
    <div class="dshbrdin store-dashboard">
        <div class="container">
            <div class="row">
                <div class="alert alert-success" id="successMessage" style="display: none;">
                </div>
                <div class="col-md-6 mb-30">
                    <div class="white-background all-shahdow dashbrd-sec height100 ">
                        <div class="wdgtbx border height100 mb-0 white-background all-shahdow">
                            <div class="row justify-content-between">
                                <div class="col-sm-6">
                                    <h6>Store Status xxx</h6>
                                </div>
                                <div class="col-sm-4 text-right">
                                    <?php
                                    $activateMessage = true;
                                    $activeStatus = true;

                                    if ($totalProducts > 0 && $storeData->is_completed == '1' && $stripeConnectedAccount == true && $stripeSub['status'] == 'active' && aboutStore($storeData->store_id) == true ) {
                                        $activeStatus = true;
                                    } else {
                                    $activeStatus = false;
                                    ?>
                                    <div class="noteactive"></div>
                                    <?php }
                                    ?>
                                    <?php if ($activeStatus == true) {
                                    if ($storeData->store_status == '2') {
                                        $word = "Deactive";
                                    } else {
                                        $word = "Active";
                                    }
                                    ?>
                                    <span class="check-status name-select-mbt"><?php echo $word; ?></span>
                                    <div class="checkbox-list mt-0 display-inline">
                                        <label class="container-checkbox switch-buttonk  simple-checkbox">
                                            <input type="checkbox" class="checked-store" name="storeactive" id="storeactive">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                    <?php }else{ ?>
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

                                    if($res == true){ ?>

                                    <span><i class="fa fa-check" aria-hidden="true"></i> About us</span>
                                    <?php } else{
                                    $activateMessage = false;
                                    ?>

                                    <span><i class="fa fa-times" aria-hidden="true"></i> About us</span>
                                    <?php } ?>

                                </div>

                                <div>
                                    <?php
                                    if (!$stripeConnectedAccount) {
                                    $activateMessage = false;
                                    ?>
                                    <span><i class="fa fa-times" aria-hidden="true"></i> Stripe Account </span><?php
                                    } else {
                                    ?>
                                    <span><i class="fa fa-check" aria-hidden="true"></i> Stripe Account </span>
                                    <?php
                                    }
                                    ?>
                                    <?php if ($stripeSub) { ?>

                                    <?php if ($stripeSub['status'] == 'active') { ?>
                                    <span class="ml-0 mb-0 "><i class="fa fa-check" aria-hidden="true"></i> Subscription <p
                                                class="ligt-grey mb-0 mt-15 fnt12">Note:Your subscription package is "<?php echo $stripeSub['name']; ?>
                                            " and it will expire on <?php echo $stripeSub['current_period_end']; ?></p></span>
                                    <?php } else { ?>
                                    <span class="ml-0 mb-0"><i class="fa fa-times"
                                                               aria-hidden="true"></i>Subscription <p
                                                class="ligt-grey mb-0">Note:Your subscription package was "<?php echo $stripeSub['name']; ?>
                                            " and it expired on <?php echo $stripeSub['current_period_end']; ?></p></span>
                                    <?php } ?>


                                    <?php
                                    } else {
                                    ?>
                                    <span><i class="fa fa-times" aria-hidden="true"></i> Subscription </span>
                                    <?php
                                    }
                                    ?>

                                </div>
                                <?php
                                if (Session::has('response_array')) {
                                $response_array = Session::pull('response_array');
                                if (isset($response_array['success'])) {
                                    echo '<div class="alert alert-success" style="top:0px;">
  <strong>Success!</strong>' . $response_array['success'] . '
</div>';
                                } if (isset($response_array['error'])) {
                                ?>
                                <div class="alert alert-danger" style="top:0px;">
                                    <strong>Error!</strong>
                                    <?php echo $response_array["error"]; ?>
                                </div>
                                <?php
                                }
                                } if (!$stripeConnectedAccount) {
                                $activateMessage = false;
                                ?>
                                <div class="view-al static-position mb-15 mt-10">
                                    <a href="{{$connectAccount}}" class="button btn ">Connect Your stripe Account</a>
                                </div>
                                <?php
                                }
                                if ($stripeConnectedAccount != false && $stripeSub == false) {
                                //$activateMessage=false;
                                ?>
                                <a href="{{route('stripe-pans')}}" class="button btn">Subscribe</a>
                                <?php } ?>

                            </div>
                            <?php if ($activateMessage == false) { ?>
                            <p class="ligt-grey mb-0">Note: Please ensure that the all the sections mentioned above are
                                filled out before the store can be turned on.</p>
                            <?php } ?>
                        </div>
                    </div><!-- white-background -->
                </div>

                <div class="col-md-6 mb-30">
                    <div class="white-background all-shahdow dashbrd-sec height100 ">
                        <div class="wdgtbx border height100 mb-0 white-background all-shahdow">
                            <h6>Company Info<a class="pull-right" href="<?php echo URL::to('/store/edit'); ?>><i class="
                                               fa fa-pencil" aria-hidden="true"></i></a></h6>
                            <div class="wdgcnt justify-content-md-center">
                                <div class="usr hide-div">
                                    <?php
                                    $imageUrl = '';
                                    if ($userData[0]->user_avatar != '') {
                                    $imageUrl = getAttachmentUrlById($userData[0]->user_avatar, 'small');
                                    ?>
                                    <p><img class="rounded-circle" src="<?php echo $imageUrl; ?>" alt="user icon" title="user icon"></p>
                                    <?php } else { ?>
                                    <p class="image-outer-mbt">
                                        <?php
                                        if (!empty($userData)) {
                                            $name = "";

                                            $username = $userData[0]->name;
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
                                <div class="shp site-width300">

                                    <?php
                                    $imgeUrl = "";
                                    $storeData->store_logo;
                                    if (!empty($storeData->store_logo)) {
                                        $imgeUrl = getAttachmentUrlById((int)$storeData->store_logo, 'small');
                                    }
                                    ?>
                                    <div class="seprate-image display-table-cell">
                                        <p><img src="<?php echo $imgeUrl; ?>" alt="{{$storeData->store_name}}" title="{{$storeData->store_name}}"></p>
                                    </div>
                                <!--                                <p><?php //echo $storeCountry['country_name'];                                                 ?></p>-->
                                    <div class="content-des-mbt display-table-cell">

                                        <?php if (!empty($storeData->contact_info)) { ?>
                                        <p>Member since: {{ date('F d, Y', strtotime($storeData->created_at)) }}</p>
                                        <p><?php echo $storeData->street_address; ?></p>
                                        <p class="mb-10 mt-0">Contact: <?php echo $storeData->contact_info; ?></p>

                                        <?php } ?>

                                        <div class="view-al static-position">
                                            <a href="<?php echo URL::to('/store/edit'); ?>">Manage</a>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-12 mt-25 mb-20 hide-mobile">
                    <div id="columnchart_material" style="height: 500px;"></div>
                </div>


                <div class="col-md-6 mb-30">
                    <div class="wdgtbx border height100 mb-0 white-background all-shahdow">
                        <h6>Products</h6>
                        <h4 class="mt-15 fnt-16">You have <?php
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
                            ?> &nbsp;&nbsp;<?php if ($totalProducts > 0) { ?>
                            <a href="{{URL::to('/store-products')}}" class="hide-button">View Products</a>
                            <?php } ?>
                        </h4>

                        <div class="wdgcnt">

                            <div class=" dshprdbx ">

                                <?php if ($totalProducts >= 1) { ?>
                                <a href="{{URL::to('/addproduct')}}" class="btn btn-primary btn-simple hide-button">Add
                                    More Products</a>
                                <?php } else { ?>
                                <a href="{{URL::to('/addproduct')}}" class="btn btn-primary btn-simple hide-button">Add
                                    your first Product</a>
                                <?php } ?>
                            </div>
                        </div>
                        <div class="view-al text-right">
                            <?php if ($totalProducts >= 1) { ?>
                            <a href="{{URL::to('/store-products')}}">Manage</a>
                            <?php } ?>
                        </div>

                    </div>
                </div>

                <div class="col-md-6 mb-30">
                    <div class="wdgtbx border height100 mb-0 white-background all-shahdow">
                        <h6>Orders <a class="pull-right" href="#"><i class="fa fa-pencil" aria-hidden="true"></i></a>
                        </h6>
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
                                <p>Orders Pending: <?php
                                    $count = getOrdersCount($storeData->store_id, $role = 'store', $status = 'pending');
                                    echo $count;
                                    ?>
                                    <?php if ($count > 0) { ?>
                                    &nbsp;&nbsp;<a href="{{URL::to('/buyer-orders?status=pending')}}">Check
                                        Status</a>
                                    <?php
                                    } else {
                                    echo 0;
                                    }
                                    ?>  </p>

                                <?php /// echo $userData['pendingorders'];          ?>
                                <!--                                    &nbsp;&nbsp;<a href="{{URL::to('/buyer-orders')}}">Check Status</a>-->
                                <?php
                                //                                    } else {
                                //                                        echo 0;
                                //                                    }
                                ?>  </p>

                                <br>
                            </div>
                            <div class="view-al text-right">
                                <a href="javascript:;">Manage</a>
                            </div>

                        </div>
                    </div>

                </div>

                <div class="col-md-6 mb-15">
                    <div class="wdgtbx border mb-0 height100 white-background all-shahdow">
                        <h6>Shipping <a class="pull-right" href="#" class="disable-link"><i class="fa fa-pencil"
                                                                                            aria-hidden="true"></i></a>
                        </h6>
                        <div class="wdgcnt">
                            <div class="dshshp hide-div">
                                <img class="border" src="{{ asset('assets/images/ship1.jpg',env('HTTPS_STATUS')) }}" alt="ship1" title="ship1">
                                <img class="border" src="{{ asset('assets/images/ship2.jpg',env('HTTPS_STATUS')) }}" alt="ship2" title="ship2">
                                <img class="border" src="{{ asset('assets/images/ship3.jpg',env('HTTPS_STATUS')) }}" alt="ship3" title="ship3">
                                <img class="border" src="{{ asset('assets/images/ship4.jpg',env('HTTPS_STATUS')) }}" alt="ship4" title="ship4">
                            </div>
                            <div class="shippint-method-mbt">
                                <h4 class="mt-15 fnt-16">Active Shipping Methods</h4>
                                <p>Flat Shipping</p>
                                <p>Local Pickup</p>
                                <p>Mover</p>
                            </div>

                        </div>
                        <div class="view-al text-right">
                            <a href="javascript:;">Manage</a>
                        </div>

                    </div>
                </div>

                <div class="col-md-6 mb-15">
                    <div class="wdgtbx border mb-0 height100 white-background all-shahdow">
                        <h6>Payments <a class="pull-right" href="#"><i class="fa fa-pencil" aria-hidden="true"></i></a>
                        </h6>
                        <h4 class="mt-15 fnt-16">Active Payment Method</h4>
                        <div class="checked-ticks mt-0">
                            <div>
                                <span class="fnt-16 mb-0"><i class="fa fa-check" aria-hidden="true"></i> Stripe</span>
                                <span></span>
                            </div>
                        </div>

                        <div class="wdgcnt hide-div">
                            <div class="dshshp ">
                                <img class="border" src="{{ asset('assets/images/paypal.jpg',env('HTTPS_STATUS')) }}" alt="paypal" title="paypal">
                                <img class="border" src="{{ asset('assets/images/bitcoin.jpg',env('HTTPS_STATUS')) }}" alt="bitcoin" title="bitcoin">
                                <img class="border" src="{{ asset('assets/images/am-exp.jpg',env('HTTPS_STATUS')) }}" alt="am-exp" title="am-exp">
                                <img class="border" src="{{ asset('assets/images/visa.jpg',env('HTTPS_STATUS')) }}" alt="visa" title="visa">
                                <img class="border" src="{{ asset('assets/images/master.jpg',env('HTTPS_STATUS')) }}" alt="master" title="master">
                            </div>
                            <div class="checked-ticks">
                                <div>
                                    <span><i class="fa fa-check" aria-hidden="true"></i> Stripe</span>
                                </div>
                            </div>
                        </div>
                        <div class="view-al text-right">
                            <a href="javascript:;">Manage</a>
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
    <?php
    $arr=array();
    $storeSales=getYearlyOrdersOfStore();
    for ($i=0; $i<=12; $i++){
    if(array_key_exists($i,$storeSales)){
    $arr[$i]=$storeSales[$i];
    }else{
    $arr[$i]=0;
    }
    }
    ?>
</main>
<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
<script type="text/javascript">
    google.charts.load('current', {'packages': ['bar']});
    google.charts.setOnLoadCallback(drawChart);

    function drawChart() {
        var data = google.visualization.arrayToDataTable([
            ['Month', 'Sales'],
            ['Jan', <?php echo isset($arr[1])? $arr[1]:0;?>],
            ['Feb', <?php echo isset($arr[2])? $arr[2]:0;?>],
            ['Mar', <?php echo isset($arr[3])? $arr[3]:0;?>],
            ['Apr', <?php echo isset($arr[4])? $arr[4]:0;?>],
            ['May', <?php echo isset($arr[5])? $arr[5]:0;?>],
            ['June', <?php echo isset($arr[6])? $arr[6]:0;?>],
            ['Jul', <?php echo isset($arr[7])? $arr[7]:0;?>],
            ['Aug', <?php echo isset($arr[8])? $arr[8]:0;?>],
            ['Sep', <?php echo isset($arr[9])? $arr[9]:0;?>],
            ['Oct', <?php echo isset($arr[10])? $arr[10]:0;?>],
            ['Nov', <?php echo isset($arr[11])? $arr[11]:0;?>],
            ['Dec', <?php echo isset($arr[12])? $arr[12]:0;?>]
        ]);

        var options = {
            width: 1120,
            is3D: true,
            // colors: ['#34a08d'],
            colors: ['#34a08d'],
            chart: {
                title: 'Yearly Sales Report',
                // subtitle: 'Sales, Expenses, and Profit: 2016-2017',
            }
        };

        var chart = new google.charts.Bar(document.getElementById('columnchart_material'));

        chart.draw(data, google.charts.Bar.convertOptions(options));
    }
</script>
<script>
    var message = "";
    $('.checked-store').on('click', function () {

        checked = $('input', this).is(':checked');
        res = {}

        if (checked == true) {
            res['store_status'] = "2";
            message = "Store has been activated successfully";
        } else {
//            var rep = confirm("Are you want to sure to deactivate your store.");
//            if (rep == false)
//                return false;
            res['store_status'] = "0";
            message = "Store has been deactivated successfully";
        }


        sendreq(res, 'store-status', 'change', 'callbacksutatus');
        $('span.check-status').text(checked ? 'Activeted' : 'Deactive');
    });
    //    $(".noteactive").on('click',function(){
    //         $("#successMessage").html(message);
    //        $("#successMessage").show();
    //    });
    function callbacksutatus() {
        $("#successMessage").html(message);
        $("#successMessage").show();
        setTimeout(function () {
            jQuery("#successMessage").fadeOut('slow')
        }, 3000);
    }

</script>
@endsection
