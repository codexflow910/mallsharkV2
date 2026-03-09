@extends('layouts.mobile-main')
@section('content')
@section('title', 'Buyer orders')

<main>

<div class="dshbrdin orders-container buyer-orders-mbt ">
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <div class="uploadImages" style="display: block;">
                    <span></span>
                </div>
                @if($mesage!='')
                <div class="alert alert-info alert-success-mbt search_message" role="alert">
                    {{$mesage}}
                </div>
                @endif

                <div class="dshodrs">

                    <!-- Product Sorting Starts -->
                    <div class="row srtbx">
                        <div class="col-md-6">
                            <h3 class=" ">Orders</h3>
                        </div>
                        <div class="col-md-6 text-right">
                            <!--                            <div class="  form-inline small pull-right ">
                                                            <label for="text">Search Orders:</label>&nbsp;&nbsp;
                                                            <input type="text" class="form-control" id=" " aria-describedby="" placeholder="Order Id, email or name">
                                                        </div>   -->
                        </div>
                    </div>
                    <!-- Product Sorting Ends-->


                    <?php
                        $unprocessed = '';
                        $procesing = '';
                        $pending = '';
                        $filled = '';
                        $shipped = '';
                        $completed = '';
                        $cancelled = '';
                        $all = '';
                        $undelivered = '';
                        $refunded = '';
                        $pending = '';
                        $dispute = '';
                        if (isset($_GET['status'])) {
                            if ($_GET['status'] == 'unprocessed') {
                                $unprocessed = 'active';
                            }
                            if ($_GET['status'] == 'pending') {
                                $pending = 'active';
                            } elseif ($_GET['status'] == 'processing') {
                                $procesing = 'active';
                            } elseif ($_GET['status'] == 'shipped') {
                                $shipped = 'active';
                            } elseif ($_GET['status'] == 'unprocessed') {
                                $unprocessed = 'active';
                            } elseif ($_GET['status'] == 'completed') {
                                $completed = 'active';
                            } elseif ($_GET['status'] == 'cancelled') {
                                $cancelled = 'active';
                            } elseif ($_GET['status'] == 'pending') {
                                $pending = 'active';
                            } elseif ($_GET['status'] == 'filled') {
                                $filled = 'active';
                            } elseif ($_GET['status'] == 'refunded') {
                                $refunded = 'active';
                            } elseif ($_GET['status'] == 'dispute') {
                                $dispute = 'active';
                            } elseif ($_GET['status'] == 'undelivered') {
                                $undelivered = 'active';
                            } else {
                                $all = 'active';
                            }
                        } else {
                            $all = 'active';
                        }
                        $url_current = Request::url();
                        ?>

                    <!-- Tab starts -->
                    <ul class="nav nav-tabs mobile-tabs-order">
                        <li class="nav-item">
                            <a href="{{$url_current}}" class="nav-link {{$all}}" href="#">All</a>
                        </li>
                        <li class="nav-item">
                            <a href="{{$url_current}}?status=pending" class="nav-link {{$pending}}">Pending</a>
                        </li>
                        <!--  <li class="nav-item">
                              <a  href="{{$url_current}}?status=filled" class="nav-link {{$filled}}">Filled</a>
                          </li>
                        ->
                                                <li class="nav-item">
                                                    <a  href="{{$url_current}}?status=processing"class="nav-link {{$procesing}}" >Processing</a>
                                                </li>-->
                        <li class="nav-item">
                            <a href="{{$url_current}}?status=shipped" class="nav-link {{$shipped}}">Shipped</a>
                        </li>

                        <!-- <li class="nav-item">
                            <a  href="{{$url_current}}?status=undelivered" class="nav-link {{$undelivered}}" >Undelivered</a>
                        </li> -->
                        <li class="nav-item">
                            <a href="{{$url_current}}?status=refunded" class="nav-link {{$refunded}}">Refunded</a>
                        </li>

                        
                        <li class="nav-item">
                            <a class="nav-link {{$cancelled}}" href="{{$url_current}}?status=cancelled">Cancelled</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{$cancelled}}" href="{{$url_current}}?status=dispute">Disputed</a>
                        </li>
                        <li class="nav-item">
                            <a href="{{$url_current}}?status=completed" class="nav-link {{$completed}}" >Completed</a>
                        </li>


                    </ul>
                    <!-- Tab Ends -->


                    @if(is_array($orders_all) && count($orders_all)>0)
                    <div class="tab-content">

                        <div id="home" class="tab-pane  in active">
                            <!-- Tab Heads Starts -->
                            <div class="ordtbscnt hide-mobile">

                                <div class="row">
                                    <div class="col-md-1 "><span>Order ID</span></div>
                                    <div class="col-md-1 "><span class="text-center">Status</span></div>
                                    <div class="col-md-1 "><span class="text-center">Date</span></div>
                                    <div class="col-md-1 "><span class="text-center">Total</span></div>
                                    <div class="col-md-6 "><span class="spacing-mobile-order">Shipping Address</span>
                                    </div>
                                    <div class="col-md-2 " style="text-align: center;"><span>Action</span></div>
                                </div>

                            </div>
                            <!-- Tab Heads Ends -->

                            <script>
                            $(document).ready(function() {

                                if ($(window).width() <= 767) {
                                    $(".card-link").click(function() {
                                        id = jQuery(this).attr('href');
                                        jQuery(id).show();
                                        $('html, body').animate({
                                            scrollTop: $(id).offset().top - 60
                                        }, 1500);
                                    });
                                }

                            });
                            </script>


                            <!-- Tab Content Starts -->

                        <?php
                        if (is_array($orders_all) && count($orders_all) > 0) {
                        // echo 'Data: <pre>' .print_r($orders_all,true). '</pre>'; die;
                            foreach ($orders_all as $key => $order) {
                         //echo 'Data: <pre>' .print_r($order,true). '</pre>';

                                foreach($order as $value){

                                    $order_id = $value->order_id;
                                    $store_id = $value->store_id;
                                    $user_id = $value->buyer_id;
                                    $payment_status = $value->payment_status;
                                    $order_status = $value->order_status;
                                    $date = $value->created_at;
                                    $shipping_address = $value->street;
                                    $total_amount = $value->total_amount;
                                    $item_id = $value->item_id;
                                    $user_info = getUserById($user_id);
    
                                    $product = getProductById($value->item_id);
                                    $pslug = isset($product->slug) ? $product->slug : '';
                                    //$product = getProductById($order->order_status);
                                    $payment_method = $value->payment_method;
                                    if ($user_info) {
                                        $name = $user_info->name;
                                    }
                                    $country_code = isset($value->country) ? $value->country : '';
                                    $country_code = str_replace('"', '', $country_code);
                                    $country_code = (int) $country_code;
                                    $trackingId = isset($order->tracking_id) ? $value->tracking_id : '';
                                    if ($country_code == 2) {
                                        $country_name = 'CANADA';
                                    } else {
                                        $country_name = 'USA';
                                    }

                                    // $order_id = $value[0]->order_id;
                                    // $store_id = $value[0]->store_id;
                                    // $user_id = $value[0]->buyer_id;
                                    // $payment_status = $value[0]->payment_status;
                                    // $order_status = $value[0]->order_status;
                                    // $date = $value[0]->created_at;
                                    // $shipping_address = $value[0]->street;
                                    // $total_amount = $value[0]->total_amount;
                                    // $item_id = $value[0]->item_id;
                                    // $user_info = getUserById($user_id);
    
                                    // $product = getProductById($value[0]->item_id);
                                    // $pslug = isset($product->slug) ? $product->slug : '';
                                    // //$product = getProductById($order[0]->order_status);
                                    // $payment_method = $value[0]->payment_method;
                                    // if ($user_info) {
                                    //     $name = $user_info->name;
                                    // }
                                    // $country_code = isset($value[0]->country) ? $value[0]->country : '';
                                    // $country_code = str_replace('"', '', $country_code);
                                    // $country_code = (int) $country_code;
                                    // $trackingId = isset($order[0]->tracking_id) ? $value[0]->tracking_id : '';
                                    // if ($country_code == 2) {
                                    //     $country_name = 'CANADA';
                                    // } else {
                                    //     $country_name = 'USA';
                                    // }
    
                                    //$country_name = '';
                                    // $country_name = DB::table('country_list')->where('country_code', '=', $order[0]->country)->select('country_name')->get();
                                    // print_rr($country_name);
                                    // die();
                                    // $country_name = $country_name[0]->country_name;
                                    $order_state = isset($value->state) ? $value->state : '';
    
                                    $state_name = DB::table('states')->where('state_id', '=', $order_state)->where('country_id', '=', $country_code)->select('state_name')->get();
    
                                    $state_name = isset($state_name->state_name) ? $state_name->state_name : '';
                                    $shipping_address = $value->street . ' ' . $state_name . ' ' . $country_name;
    
                                    $icon = '';
                                    if ($order_status == 'new') {
                                        $icon = '<i class="fa fa-clock-o" aria-hidden="true"></i>';
                                    }
                                    if ($order_status == 'completed') {
                                        $icon = '<i class="fa fa-check-square-o" aria-hidden="true"></i>';
                                    }
                                    if ($order_status == 'shipped') {
                                        $icon = '<i class="fa fa-hourglass-o" aria-hidden="true"></i>';
                                    }
                                    if ($order_status == 'processing') {
                                        $icon = '<i class="fa fa-refresh" aria-hidden="true"></i>';
                                    }
                                    if ($order_status == 'cancelled') {
                                        $icon = '<i class="fa fa-clock-o" aria-hidden="true"></i>';
                                    }
                                   // echo '$value: <pre>' .print_r($value,true). '</pre>'; die;

                                }

                             
                            
                                ?>




                            <div class="ordtbscnt container" id="accordion">

                                <div class="row">

                                    <div class="col-md-1">

                                        <span>

                                            <a class="card-link" data-toggle="collapse" data-parent="#accordion"
                                                href="#collapse{{$order_id}}_all">
                                                <span class="arw arrows-style-mot">
                                                    <i style="font-size:18px" class="fa fa-angle-down"
                                                        aria-hidden="true"></i>
                                                </span>
                                                &nbsp;&nbsp; #{{$key}}</a></span>
                                    </div>
                                    <div class="col-md-1 text-center set-width-mobile" style="font-size:14px">

                                        <span class="text-center  text-capitalize"><?php echo $order_status; ?>
                                        </span>
                                    </div>
                                    <div class="col-md-1 set-width-mobile">

                                        <span class="text-center">{{ date('M d, Y', strtotime($date)) }}</span>
                                    </div>
                                    <div class="col-md-1 set-width-mobile">

                                        <span class="text-center">USDC {{$total_amount}}</span>
                                    </div>
                                    <div class="col-md-6 " style="display:block">
                                        <span class="spacing-mobile-order">

                                            {{$shipping_address}}
                                        </span>
                                         
                                            <a style="padding-right: 25px;" title="download" @if(isset($_GET['order_id'])
                                            && $_GET['order_id']==$order_id) class="download-link" @endif
                                            href="{{route('order-pdf',$order_id)}}">invoice <i class="fa fa-download"></i></a>

                                                <?php if ($order_status == 'shipped') { ?>
                                        <div class="trackinginfo" style="margin-top:10px;">
                                            <?php if ($trackingId != '') {?><strong>Tracking
                                                Id:{{$trackingId}}</strong><?php }?></div>
                                                <?php }?>
                                    </div>
                                    <div class="col-4 col-md-2 mobile-fix-div">
                                    <?php // if ($order_status == 'pending' || $order_status == 'filled' || $order_status == 'shipped') { ?>

                                    <!-- <div class="col-6 col-md-2 mobile-fix-div" style="display:block"> -->

                                        <?php
                                        $now = date("Y-m-d");
                                       //echo 'Data: <pre>' .print_r($order_status,true). '</pre>';
                                         if ($order_status != 'completed' && $order_status != 'cancelled' && $order_status != 'returned' && $order_status != 'undelivered') { ?>
                                        <!-- <a data-status="completed" data-order_id="{{$order_id}}" class="dropdown-item change-order-status" href="javascript:;"><i class="fa fa-check-square-o" aria-hidden="true"></i> Completed</a> -->
                                        <?php } ?> <?php
                                        if ($order_status == 'pending' && $order_status != 'dispute' && $order_status != 'shipped' && $order_status != 'cancelled') {    ?>
                                        <!-- <a data-status="cancelled" data-order_id="{{$order_id}}" data-refunded_date="{{$now}}" data-is_status="cancle" class="dropdown-item change-order-status" href="javascript:;"><i class="fa fa-ban" aria-hidden="true"></i> Cancel</a> -->
                                         {{-- <a class="btn btn-primary btn-simple btn-sm btnauto getpid"
                                            data-status="cancelled" href="<?php //echo URL::to('/product/' . $pslug); ?>"
                                            data-product-id="<?php //echo $item_id; ?>" data-order-id="{{$order_id}}"
                                            data-toggle="modal" data-target="#refund-popup{{$order_id}}_all"
                                            style="margin-left: 30px;" id="cancel_order">Cancel Order</a> --}}
                                        <?php  }  ?> 
                                        <?php if ($order_status == 'cancelled' ) {   ?>
                                        <a class="btn btn-primary btn-simple btn-sm btnauto" id="cancel_order">Requested For Refund</a>
                                        <?php  }  ?> 
                                        <?php if ($order_status == 'dispute' ) {   ?>
                                        <a class="btn btn-primary btn-simple btn-sm btnauto " style="margin-left: 30px;" id="cancel_order">Dispute Order</a>
                                        <?php }  ?> 
                                        <?php if ($order_status == 'refunded' ) {   ?>
                                        <a class="btn btn-primary btn-simple btn-sm btnauto " style="margin-left: 20px;">Refunded</a>
                                        <?php }  ?> 
                                        <?php
                                         if ($order_status == 'shipped') { ?>
                                        <!-- <a data-status="returned" data-order_id="{{$order_id}}" data-refunded_date="{{$now}}" data-is_status="refund" class="dropdown-item change-order-status" href="javascript:;"><i class="fa fa-ban" aria-hidden="true"></i> Refund</a> -->
                                        <a class="btn btn-primary btn-simple btn-sm btnauto getpid"
                                            data-status="refunded" href="<?php //echo URL::to('/product/' . $pslug); ?>"
                                            data-product-id="<?php //echo $item_id; ?>" data-order-id="{{$order_id}}"
                                            data-toggle="modal" data-target="#refund-order-popup{{$order_id}}_all"
                                            style="margin-left: 30px; margin-top: 17px;" id="refund_order">Refund Order</a>

                                        <?php } ?>




                                        <!-- <span class="text-center"><a href="javascript:;" class="btn btn-primary btn-simple btnauto dropdown-toggle" data-toggle="dropdown"> Change Status </a>
                                                        <div class="dropdown-menu">





                                                        </div>
                                                    </span> -->
                                    <!-- </div> -->
                                    <?php //}?>
                                    <?php
                                        if ($payment_status == 'paid') {
                                                    echo '<div class="" style="display:block"><span class="text-status" style="text-align:center,margin:40px">Completed</h5></div>';
                                                }else if ($payment_status == 'refunded') {
                                                    // echo '<div  style="display:block"><span class="text-status" style="text-align:center">Refunded</h5></div>';
                                                }elseif($order_status == 'pending'){
                                                 ?> 
                                                 <a class="btn btn-primary btn-simple btn-sm btnauto getpid"
                                                 data-status="cancelled" href="<?php //echo URL::to('/product/' . $pslug); ?>"
                                                 data-product-id="<?php //echo $item_id; ?>" data-order-id="{{$order_id}}"
                                                 data-toggle="modal" data-target="#refund-popup{{$order_id}}_all"
                                                 style="margin-left: 30px;" id="cancel_order">Cancel Order</a>
                                                 <?php   
                                                }
                                                ?>
                                                <?php
                                                 $userId = getUserIdbyOrderId($order_id);
                                                $url = URL::to('/site-support/' . $userId['user_id'] . '/seller');
                                                ?>


                                            </div>
                                    <!-- <div class="col-md-2">
                                        <a href="#" onclick="return popitup('<?php //echo $url; ?>')"
                                            class="btn btn-primary btn-simple btnauto talk-to-buyer">Talk to Seller</a>
                                    </div> -->
                                </div>

                                <div id="collapse{{$order_id}}_all" class="collapse ordinf">
                                    <?php
                                //    echo '$order: <pre>' .print_r($order,true). '</pre>'; 
                                    foreach ($order as $val) {
                                        $product = getProductById($val->item_id);
                                        $variable_product = getvariableProduct_id($val->variation_id);

                                        if (count($variable_product) > 0) {
                                            $product_variation_image = isset($variable_product[0]->product_variation_image) ? $variable_product[0]->product_variation_image : '';

                                        }
                                        // echo $product_variation_image;
                                        $product_title = isset($product[0]->title) ? $product[0]->title : '';
                                        $product_id = isset($product[0]->product_id) ? $product[0]->product_id : '';
                                        $product_quantity = $val->quantity;
                                        $payment_method = $val->payment_method;
                                        $shipping_amount = isset($val->shipping_amount) ? $val->shipping_amount:"0";
                                        $price = $val->price;
                                        $price_shipemnt = $price + $shipping_amount;
                                        $product_type = !empty($product[0]->product_type) ? $product[0]->product_type : '';
                                        $comment = $val->comment;
                                        $rating = $val->rating;
                                        $pslug = isset($product[0]->slug) ? $product[0]->slug : '';
                                        if ($rating == '') {
                                            $rating = 0;
                                        }
                                        $image = isset($product[0]->feature_image) ? $product[0]->feature_image : '';
                                        $image = (int) $image;
                                        $image_url = '';
                                        // if ($image != '') {
                                        //     $image_url = getAttachmentUrlById($image, 'small');
                                        // }
                                        $variation_name = '';
                                        if ($val->variation_id != '') {
                                             $variation_name = getVariationNameById($val->variation_id);
                                            

                                           
                                        }
                                        if(!empty($image)){

                                       
                                        if($product_type == 'simple' ){
                                            $image_url = getAttachmentUrlById((int) $image, 'medium');
                                            }elseif($product_type == 'virtual'){
                                                $image_url = getAttachmentUrlById((int) $image, 'medium');
                                            }else{ 
                                                $image_url = getAttachmentUrlById((int) $product_variation_image, 'medium');
                                            }
                                        }
                                        $dateCreated = isset($product[0]->created_at) ? $product[0]->created_at : '';
                                        $cl1 = 3; $cl2 =1;
                                        if($product_type=='virtual') {
                                            $cl1 = 2; $cl2 =2;
                                        }  
                                        ?>

                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-{{$cl1}}">
                                                <a href="<?php echo URL::to('/product/' . $pslug); ?>">
                                                    <div class="lstbr-itm d-flex justify-content-between">
                                                        <span class="padleft align-self-center">
                                                            <img src="<?php echo $image_url; ?>"
                                                                onerror="this.src='{{url('/assets/images/placeholder.jpg')}}'"
                                                                alt="{{$product_title}}" title="{{$product_title}}"
                                                                style="width:53px;">
                                                        </span>
                                                        <span class="align-self-center pl-15opt">
                                                            {{$product_title}}
                                                        </span>
                                                        <span style="padding:10px;" class="variation-txt">{{$variation_name}}</span>
                                                    </div>
                                                </a>
                                            </div>
                                            <!--        <div class="col-md-1 text-center hide-border">
                                                             <a style="font-size:20px; display:inline-block; width:100%;" href="javascript:;" title="" data-toggle="popover" data-placement="top" data-content="" data-original-title="Buyer Note">
                                                                <i class="fa fa-sticky-note-o" aria-hidden="true"></i>
                                                            </a>
                                                            {{ date('F d, Y', strtotime($dateCreated)) }}

                                                        </div>-->
                                                @if(!empty($product_type) && $product_type=='virtual')
                                                    <div class="col-md-{{$cl2}}">
                                                        <a class="btn btn-primary btn-simple btn-sm btnauto getpid"
                                                        href="<?php echo URL::to('/download-files/' . $product_id); ?>" style="margin-left: 30px;">DownLoad files</a>
                                                    </div>
                                                @endif
                                            <div class="col-md-1">
                                                <?php 
                                                if($product_type !='virtual') {  
                                                    
                                                 
                                                            ?>
                                                <span class="text-center">Qty :{{$product_quantity}}</span>
                                                <?php } ?>
                                            </div>
                                            <div class="col-md-2">
                                                <span>Price: USDC {{$price_shipemnt}}<br>
                                                    <span class="small grey"></span>
                                                </span>
                                            </div>
                                            <div class="col-md-5">
                                                <span class="text-left col" style="padding-left:10px">
                                                    <span>Your Review:&nbsp;&nbsp;

                                                        </a>&nbsp;&nbsp;&nbsp;
                                                        <?php
                                                        for ($i = 0; $i < 5; $i++) {
                                                                        if ($i < $rating) {
                                                                            $class = "fa fa-star";
                                                                        } else {
                                                                            $class = "fa fa-star-o";
                                                                        }
                                                                        echo ' <i class="' . $class . '" aria-hidden="true"></i>';
                                                                    }
                                                                    ?>


                                                    </span>


                                                    <a href="javascript:;" title="Your Review" data-toggle="popover"
                                                        data-placement="top" data-content="{{$comment}}"
                                                        style="padding-left:10px;">View Detail</a>
                                                    <?php if ($rating == 0) {?>
                                                    <a class="btn btn-primary btn-simple btn-sm btnauto getpid"
                                                        href="<?php echo URL::to('/product/' . $pslug); ?>"
                                                        data-product-id="<?php echo $val->item_id; ?>"
                                                        data-order-id="<?php echo $order_id; ?>" data-toggle="modal"
                                                        data-target="#review-popup" style="margin-left: 30px;">Post
                                                        Review</a>
                                                    <?php }?>
                                                </span>
                                            </div>

                                        </div>
                                    </div>



                                    <?php }?>

                                </div>

                            </div>
                               <!--Start Cancel order Popup MOdel !-->
                            <div class="modal fade refund-popup-modal" id="refund-popup{{$order_id}}_all" tabindex="-1"
                                role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                                <div class="modal-dialog cancel-order-popup" role="document">
                                    <div class="modal-content">
                                        <div class="modal-header">

                                            <div class="col-md-6">
                                                <h5 id="cancel_order_modal">Cancel Order <span
                                                        class="text-primary">#{{$order_id}}</span></h5>
                                              
                                                <p>Order Date: {{ date('M d, Y', strtotime($date)) }}</p>
                                            </div>
                                            <div class="col-md-6 text-right">
                                                <small>Vendor</small>
                                                <h3 class="text-danger ">WIRELESS HUB</h3>

                                            </div>
                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                <span aria-hidden="true">&times;</span>
                                            </button>

                                        </div>

                                        <div class="popup-wrapper">
                                            <form  id="order_refund">
                                              
                                                <div class="container">
                                                    <input type="hidden" name="order_id" id="order_id" class="order_id" value="{{$order_id}}">
                                                    <input type="hidden" name="buyer_id" id="buyer_id" class="buyer_id" value="{{$user_id}}">
                                                    <input type="hidden" name="store_id" id="store_id" class="store_id" value="{{$store_id}}">
                                                    <input type="hidden" name="order_status" id="order_status" class="order_status" value="cancelled">
                                                    <input type="hidden" name="payment_method" id="payment_method" class="payment_method" value="{{$payment_method}}">

                                                    <?php
                                                    // echo '$order: <pre>' .print_r($order,true). '</pre>'; 
                                                    foreach ($order as $val) {
                                                        $product = getProductById($val->item_id);

                                                        $product_title = isset($product[0]->title) ? $product[0]->title : '';
                                                        $product_quantity = $val->quantity;
                                                        $price = $val->price;
                                                        
                                                        $shipping_amount = isset($val->shipping_amount) ? $val->shipping_amount: "0";
                                                        
                                                        $payment_method = isset($val->payment_method) ? $val->payment_method: "";
                                                        $price_shipment = $price + $shipping_amount;
                                                        $comment = $val->comment;
                                                        $rating = $val->rating;
                                                        $pslug = isset($product[0]->slug) ? $product[0]->slug : '';
                                                        if ($rating == '') {
                                                            $rating = 0;
                                                        }
                                                        $image = isset($product[0]->feature_image) ? $product[0]->feature_image : '';
                                                        $image = (int) $image;
                                                        $image_url = '';
                                                        if ($image != '') {
                                                            $image_url = getAttachmentUrlById($image, 'small');
                                                        }
                                                        $dateCreated = isset($product[0]->created_at) ? $product[0]->created_at : '';
                                                        ?>

                                                    <div class="row mb-2 p-2 form-group">
                                                        <div class="col-md-1 ">

                                                            <input type="checkbox" name="product_id[]" id="select_price_refund"
                                                                data-price="{{$price_shipment}}"
                                                                data-shipment="{{$shipping_amount}}"
                                                                data-quantity="{{$product_quantity}}" data-order-id="{{$order_id}}"
                                                                data-product-title="{{$product_title}}"
                                                                value="{{$val->item_id}}"
                                                                class="form-check-input select_price_cancel_order" />

                                                        </div>
                                                        <div class="col-md-5">
                                                            <div class="media">
                                                                <img class="mr-2" src="<?php echo $image_url; ?>"
                                                                    onerror="this.src='{{url('/assets/images/placeholder.jpg')}}'"
                                                                    alt="Generic placeholder image" width="85px">
                                                                <div class="media-body">
                                                                    <h5 class="mt-0">{{$product_title}}</h5>
                                                                    <p><?php echo $payment_method; ?></p>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <?php
                                                            if($product_quantity > 1){ ?>
                                                        <div class="col-md-1 cancel_popup_quantity" style="display:none" id="cancel_popup_quantity">
                                                                <div class="form-group clearfix mb-0">
                                                                    <select class="form-control refund_product_qty" data-prodcut-id="<?php echo isset($val->item_id) ? $val->item_id : ''; ?>" name="refund_product_qty" id="refund_product_qty">
                                                                        <?php for ($i = 1; $i <= $product_quantity; $i++) { ?>
                                                                            <option data-product_quantity="<?php echo $i; ?>" value="<?php echo $i == $product_quantity ? $product_quantity:$i ?>" <?php echo $i == $product_quantity ? 'selected':'' ?>><?php echo $i; ?></option>
                                                                        <?php } ?>
                                                                    </select>
                                                                </div>
                                                           
                                                        </div>
                                                        <?php  }  ?>
                                                        <div class="col-md-2">
                                                            <a href="<?php echo URL::to('/product/' . $pslug); ?>" target="_blank">
                                                                <p class="text-primary">View Detail |</p>
                                                            </a>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <p><strong>{{$price_shipment}} (Inc. Shippment {{$shipping_amount}}) USDC</strong></p>
                                                        </div>
                                                    </div>
                                                    <?php }?>
                                                </div>

                                                <div class="return-prolicy">
                                                    <div class="row">
                                                        <div class="col-12">
                                                            <h5>Return Policy</h5>
                                                            <p>Terms and policy
                                                             </p>
                                                        </div>
                                                    </div>
                                                </div>


                                                <div class="cancle-order-reason-fields">
                                                    <div class="form-group">
                                                        <h5>Reason Of Return</h5>
                                                        <select class="form-control cancel_reason_type_{{$order_id}}" id="refund_reason_type"
                                                            name="refund_reason_type">
                                                            <option>select Reason</option>
                                                            <option
                                                                value="The customer ordered the wrong product or size">
                                                                The customer ordered the wrong product or size</option>
                                                            <option
                                                                value="The merchant shipped the wrong product or size">
                                                                The merchant shipped the wrong product or size</option>
                                                            <option value="The product was damaged or defective">The
                                                                product was damaged or defective</option>
                                                            <option value="The product arrived too late">The product
                                                                arrived too late</option>
                                                            <option value="The customer no longer needed the product">
                                                                The customer no longer needed the product</option>
                                                            <option value="The product did not match the description">
                                                                The product did not match the description</option>
                                                            <option
                                                                value="The product did not meet the customer’s expectations">
                                                                The product did not meet the customer’s expectations
                                                            </option>
                                                        </select>
                                                    </div>

                                                    <div class="form-group">
                                                        <textarea type="text" id="reason" class="form-control cancel_reason_{{$order_id}}"
                                                            name="reason"
                                                            placeholder="Some Other Reason? Type Here"></textarea>
                                                    </div>
                                                    <?php $now = date("Y-m-d");?>
                                                    <input type="hidden" name="is_refund" id="cancle" value="cancelled">
                                                    <input type="hidden" name="refund_date" id="refund_date"
                                                        value="{{$now}}">

                                                    <div class="row pt-3">
                                                        <div class="col-md-6">
                                                        <p class="text-primary total1_{{$order_id}}" id="total1_{{$order_id}}" ></p>
                                                            <!-- <input type="text" id="total1" readonly> -->
                                                        </div>
                                                        <div class="col-md-6 text-right">
                                                            <button id="cancel_order_submit"
                                                                class="btn btn-primary cancel_order_submit">Refund</button>
                                                          
                                                        </div>

                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>

                            </div>
                                 <!--End Cancel order Popup MOdel !-->

                              <!--Start Refund order Popup MOdel !-->
                            <div class="modal fade refund-order-popup-modal" id="refund-order-popup{{$order_id}}_all" tabindex="-1"
                                role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                                <div class="modal-dialog refund-order-popup" role="document">
                                    <div class="modal-content">
                                        <div class="modal-header">

                                            <div class="col-md-6">
                                               
                                                <h5 id="refund_order_modal">Refund Order <span
                                                        class="text-primary">#{{$order_id}}</span></h5>
                                                <p>Order Date: {{ date('M d, Y', strtotime($date)) }}</p>
                                            </div>
                                            <div class="col-md-6 text-right">
                                                <small>Vendor</small>
                                                <h3 class="text-danger ">WIRELESS HUB</h3>

                                            </div>
                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                <span aria-hidden="true">&times;</span>
                                            </button>

                                        </div>

                                        <div class="popup-wrapper">
                                            <form id="order_refunded">
                                                @csrf
                                                <div class="container">
                                                    <input type="hidden" name="order_id" id="order_id" value="{{$order_id}}">
                                                    <input type="hidden" name="buyer_id" id="buyer_id" value="{{$user_id}}">
                                                    <input type="hidden" name="store_id" id="store_id" value="{{$store_id}}">
                                                    <input type="hidden" name="order_status" id="order_status_refund" value="refunded">

                                                    <?php
                                                    //echo '$order: <pre>' .print_r($order,true). '</pre>'; 
                                                    foreach ($order as $val) {
                                                        $product = getProductById($val->item_id);
                                                        // echo '$product: <pre>' .print_r($product,true). '</pre>'; continue;

                                                        $product_title = isset($product[0]->title) ? $product[0]->title : '';
                                                        $product_id = isset($product[0]->product_id) ? $product[0]->product_id : '';
                                                        $product_quantity = isset($val->quantity) ? $val->quantity:"";
                                                        $price = isset($val->price) ? $val->price: "";
                                                        $variation_id = isset($val->variation_id) ? $val->variation_id: "";
                                                        $attribute1 = isset($val->attribute1) ? $val->attribute1: "";
                                                        $option1 = isset($val->option1) ? $val->option1: "";
                                                        $comment = isset($val->comment) ? $val->comment: "";
                                                        $rating = isset($val->rating) ? $val->rating: "";
                                                        $pslug = isset($product[0]->slug) ? $product[0]->slug : '';
                                                        if ($rating == '') {
                                                            $rating = 0;
                                                        }
                                                        $image = isset($product[0]->feature_image) ? $product[0]->feature_image : '';
                                                        $image = (int) $image;
                                                        $image_url = '';
                                                        if ($image != '') {
                                                            $image_url = getAttachmentUrlById($image, 'small');
                                                        }
                                                        $dateCreated = isset($product[0]->created_at) ? $product[0]->created_at : '';
                                                        ?>

                                                        <div class="row mb-2 p-2 form-group">
                                                            <div class="col-md-1 ">

                                                                <input type="checkbox" name="product_id_order_refund[]" data-refund_quantity="{{$product_quantity}}" id="select_price" data-refund_price="{{$price}}" value="{{$val->item_id}}"
                                                                    class="form-check-input refund_select_price" data-product-title="{{$product_title}}"  data-order-id="{{$order_id}}"/>
                                                                 
                                                                  

                                                            </div>
                                                            <div class="col-md-6">
                                                                <div class="media">
                                                                    <img class="mr-2" src="<?php echo $image_url; ?>"
                                                                        onerror="this.src='{{url('/assets/images/placeholder.jpg')}}'"
                                                                        alt="Generic placeholder image" width="85px">
                                                                       
                                                                        <div class="media-body">
                                                                            <h5 class="mt-0">{{$product_title}} </h5>
                                                                            <p></p>
                                                                        </div>
                                                                       
                                                                </div>
                                                            </div>
                                                          
                                                                <div class="col-md-1 refund_popup_quantity_{{$val->item_id}}" style="display:none" id="refund_popup_quantity">
                                                                <?php
                                                                if($product_quantity > 1){ ?>
                                                                    <div class="form-group clearfix mb-0">
                                                                    

                                                                        <select class="form-control" data-prodcut-id="<?php echo isset($val->item_id) ? $val->item_id : ''; ?>" name="refund_product_qty" id="refund_product_qty">
                                                                            <?php for ($i = 1; $i <= $product_quantity; $i++) { ?>
                                                                                <option value="<?php echo $i == $product_quantity ? $product_quantity:$i ?>" <?php echo $i == $product_quantity ? 'selected':'' ?>><?php echo $i; ?></option>
                                                                            <?php } ?>
                                                                        </select>

                                                                    </div>
                                                        

                                                                <?php  }  ?>
                                                                </div>
                                                            

                                                            <div class="col-md-2">
                                                            <a href="<?php echo URL::to('/product/' . $pslug); ?>" target="_blank">
                                                                <p class="text-primary">View Detail |</p>
                                                                </a>
                                                            </div>
                                                            <div class="col-md-2">
                                                                <p id="product-price" data-price=" <?php echo $price; ?>"> USDC  <?php echo isset($price) ?  number_format($price, 2) : ''; ?></p>
                                                            </div>
                                               
                                                        </div>
                                                    <?php }?>
                                                </div>

                                                <div class="return-prolicy">
                                                    <div class="row">
                                                        <div class="col-12">
                                                            <h5>Return Policy</h5>
                                                            <p>Refund Terms-n-Policy
                                                                </p>
                                                        </div>
                                                    </div>
                                                </div>


                                                <div class="cancle-order-reason-fields">
                                                    <div class="form-group">
                                                        <h5>Reason Of Return</h5>
                                                        <select class="form-control refund_reason_type_{{$order_id}}" id="refund_reason"
                                                            name="refund_reason_type">
                                                            <option>select Reason</option>
                                                            <option
                                                                value="The customer ordered the wrong product or size">
                                                                The customer ordered the wrong product or size</option>
                                                            <option
                                                                value="The merchant shipped the wrong product or size">
                                                                The merchant shipped the wrong product or size</option>
                                                            <option value="The product was damaged or defective">The
                                                                product was damaged or defective</option>
                                                            <option value="The product arrived too late">The product
                                                                arrived too late</option>
                                                            <option value="The customer no longer needed the product">
                                                                The customer no longer needed the product</option>
                                                            <option value="The product did not match the description">
                                                                The product did not match the description</option>
                                                            <option
                                                                value="The product did not meet the customer’s expectations">
                                                                The product did not meet the customer’s expectations
                                                            </option>
                                                        </select>
                                                    </div>

                                                    <div class="form-group">
                                                        <textarea type="text" id="reason_text" class="form-control refund_reason_{{$order_id}}"
                                                            name="reason"
                                                            placeholder="Some Other Reason? Type Here"></textarea>
                                                    </div>
                                                    <?php $date_refund = date("Y-m-d");?>
                                                    <input type="hidden" name="is_refund" id="Refunded_order" value="refunded">
                                                    <input type="hidden" name="refund_date" id="refund_date"
                                                        value="{{$date_refund}}">

                                                    <div class="row pt-3">
                                                        <div class="col-md-6">
                                                            <p class="text-primary total1_{{$order_id}}" id="total1_{{$order_id}}" ></p>
                                                            <!-- <input type="text" id="total1" readonly> -->
                                                        </div>
                                                        <div class="col-md-6 text-right">
                                                            <button id="return_refund_submit"
                                                                class="btn btn-primary return_refund_submit">Refund Order</button>
                                                          
                                                        </div>

                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                               

                            </div>
                             <!--End Refund order Popup MOdel !-->

                        </div>
                    </div>
                    <?php
                        }
                      }
                        ?>
                    <!-- Tab Content Ends -->
                </div>
            </div>
            @else
            <h5>No orders placed yet. Head over to our main <a href="{{route('home.search')}}"><u>search page</u></a> and find things that you love. </h5>
            @endif
        </div>
    </div>
</div>
</div>
</div>





        <div class="modal fade review-popup-modal" id="review-popup" tabindex="-1" role="dialog"
            aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">Post Review</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>

                    <section class='rating-widget mt-30'>
                        <div class="modal-body">
                            <form action="" method="post">
                                <div class="review-body" style="margin-left:0px;">
                                    <div class="response"></div>
                                    <p>Describing your experience with this product or seller</p>
                                    <textarea class="form-control" id="rating-text"></textarea>
                                    <!--Rating Stars Box -->
                                    <div class='rating-stars clearfix'>
                                        <div class="your-rating-headeing">Your Rating:</div>
                                        <div class="star-rating-panel-mbt">
                                            <div class="star-rating">
                                                <span class="fa fa-star-o" data-rating="1"></span>
                                                <span class="fa fa-star-o" data-rating="2"></span>
                                                <span class="fa fa-star-o" data-rating="3"></span>
                                                <span class="fa fa-star-o" data-rating="4"></span>
                                                <span class="fa fa-star-o" data-rating="5"></span>
                                                <div class="ratingerror" style="color:red;font-size: 13px;margin-top: -10px;">
                                                </div>
                                                <input type="hidden" id="rating_count" name="rating_count" class="rating-value"
                                                    value="0">
                                            </div>
                                        </div>

                                        <div class="review-button-post">
                                            <button class="btn btn-primary" id="submit-review">
                                                Post Your Review
                                            </button>
                                            <span>Note: you can post your review and rating only once.</span>
                                        </div>
                                    </div>

                                </div>
                            </form>
                        </div>


                    </section>
                </div>

            </div>
        </div>
        <!-- Content Starts -->
<script>
    var current_url;
    $(document).ready(function() {
        current_url = '{{Request::url()}}';
        $('[data-toggle="popover"]').popover();
        jQuery('.uploadImages').hide();
        //alert(current_url);
        // window.history.pushState(null, null, current_url);
    });
    $(document).ready(function() {
        var uri = window.location.toString();
        if (uri.indexOf("?") > 0) {
            var clean_uri = uri.substring(0, uri.indexOf("?"));
            window.history.replaceState({}, document.title, clean_uri);
        }
    });
    jQuery(document).on('click', '.change-order-status', function() {
        active_tab = jQuery('.tab-pane.active').attr('id');

        order_id = jQuery(this).attr('data-order_id');
        status = jQuery(this).attr('data-status');
        is_refund = jQuery(this).attr('data-is_status');
        refunded_date = jQuery(this).attr('data-refunded_date');

        notification = 'You want to change the order status!';
        if (status == 'processing') {
            active_tab = 'menu2';
            notification = 'This will give a notification to store owner that your product is being processed';
        }
        if (status == 'completed') {
            active_tab = 'menu4';
            notification = 'This will give  a notification to store owner that your order has been completed';
        }
        if (status == 'cancelled') {
            active_tab = 'menu5';
            notification = 'This will give a notification to store owner that your order has been cancelled';
        }
        if (status == 'shipped') {
            active_tab = 'menu3';
            notification = 'This will a notification to store owner that your product has been shipped';
        }

        if (order_id != '' && status != '') {
            swal({
                    title: "Are you sure?",
                    text: notification,
                    type: "warning",
                    showCancelButton: true,
                    confirmButtonClass: "btn-success",
                    confirmButtonText: "Yes, Do it!",
                    closeOnConfirm: false
                },
                function() {
                    //jQuery('.uploadImages').show();



                    new_url = current_url + '?status=' + status + '&order_id=' + order_id + '&active_tab=' +
                        active_tab + '&is_refund=' + is_refund + '&refunded_date=' + refunded_date;

                    console.log(new_url);
                    window.location.assign(new_url);
                    swal("Success!", "Status update successfully.", "success");

                });
            /*
            if (confirm('Are you sure you want to change the stauts')) {

            } else {
            // do nothing
            }
            */
        }

    });
    setTimeout(function() {
        jQuery(".search_message").fadeOut('slow')
    }, 3000);
    <?php if ($active != '') {
        ?>
    jQuery(document).ready(function() {
        jQuery('a').each(function(i) {
            if (jQuery(this).attr("href") == '<?php echo $active; ?>') {
                jQuery(this).click();
            }
        });


    });
    <?php
    }
    ?>
    </script>
    <script>
    $(document).ready(function() {
        jQuery('.uploadImages').hide();
        $('.cartadd').on("click", function() {
            jQuery('.uploadImages').show();
            var data = {};
            data['product_id'] = $(this).attr('data-product-id');
            data['store_id'] = $(this).attr('data-store-id');
            data['product_quantity'] = 1;
            sendreq(data, 'cart', 'add', 'callbackBasket');
            checkCounter();
        });

        // submit button click
        // $("#order_refund_cancel").click(function(){

        // var store_id = $("#store_id").val();
        // var buyer_id = $("#buyer_id").val();
        // var order_id = $("#order_id").val();
        // var cancle   = $("#cancle").val();
        // var refund_date = $("#refund_date").val();
        // var product_id = [];

        // // Initializing array with Checkbox checked values
        // $("input[name='select_price']:checked").each(function(){
        //     product_id.push(this.attr("data-product_id"));
        // });


        //     $.ajax({
        //         url: 'refund_order',
        //         type: 'post',
        //         data: {store_id:store_id,buyer_id:buyer_id,order_id:order_id,cancle:cancle,refund_date:refund_date, product_id:product_id},
        //         dataType: 'JSON',
        //         success: function(response){

        //             // $('.details').show();

        //         }
        //     });

        // });


    });

    function callbackBasket(data) {
        $(".cartmenu").html(data);
    }

    function checkCounter() {
        var res = {};
        res['cuneter'] = 1;
        sendreq(res, 'cart', 'counter', 'callBackBasketCounter');
    }

    function callBackBasketCounter(res) {
        $("#cart_counter").html(res);
        $("html, body").animate({
            scrollTop: 0
        }, "slow");
        setTimeout(function() {
            jQuery('.uploadImages').hide();
        }, 2000);
    }

    jQuery(document).on("click", ".nav-tabs .nav-item a", function() {
        jQuery(".uploadImages").show();
    });
    </script>
    <script>
    function popitup(url) {
        newwindow = window.open(url, 'name', 'height=600,width=500');
        if (window.focus) {
            newwindow.focus()
        }
        return false;
    }

    $(document).ready(function() {
        var $star_rating = $('.star-rating .fa');
        var SetRatingStar = function() {
            return $star_rating.each(function() {
                if (parseInt($star_rating.siblings('input.rating-value').val()) >= parseInt($(this)
                        .data('rating'))) {
                    return $(this).removeClass('fa-star-o').addClass('fa-star');
                } else {
                    return $(this).removeClass('fa-star').addClass('fa-star-o');
                }
            });
        };
        $star_rating.on('click', function() {
            $star_rating.siblings('input.rating-value').val($(this).data('rating'));
            return SetRatingStar();
        });
        SetRatingStar();
        $(document).ready(function() {

        });
    });
    var product_idd;
    var order_idd;
    jQuery(document).on('click', '.getpid', function() {
        product_idd = jQuery(this).attr('data-product-id');
        order_idd = jQuery(this).attr('data-order-id');

    });
    jQuery(document).on('click', '#submit-review', function(event) {
        //jQuery('.ratingerror').html('');
        jQuery('.ratingerror').hide();
        comment = jQuery('#rating-text').val();
        jQuery('#rating-text').removeClass('has-error');
        event.preventDefault();
        rating_count = jQuery('#rating_count').val();
        if (rating_count < 1) {
            jQuery('.ratingerror').html('Please select at least one star');
            jQuery('.ratingerror').show('fast', 'linear');
            return false;
        }
        if (comment == '') {
            jQuery('#rating-text').addClass('has-error');
        }
        if (rating_count != '' && comment != '') {
            jQuery(this).show();
            jQuery("#submit-review").hide();
            jQuery('.uploadImages').show();
            jQuery('#review-popup').hide();
            jQuery.ajax({
                url: "/add-review",
                data: 'product_id=' + product_idd + '&author_id={{getCurrentUserId()}}&rating_count=' +
                    rating_count + '&comment=' + comment + '&action=add_review',
                method: 'POST',
                success: function(res) {
                    var obj = jQuery.parseJSON(res);
                    jQuery('.response').html(obj.message);
                    swal("Success!", "Review added successfully", "success");
                    setTimeout(function() {
                        //location.reload();
                        window.location.href = "/buyer-orders?selector=" + order_idd;
                    }, 1000);
                }
            });
        }
    });
    <?php if (isset($_GET['selector']) && $_GET["selector"] != '') {?>
    jQuery('.uploadImages').show();
    jQuery(document).ready(function() {
        selected_order = '#collapse<?php echo $_GET["selector"]; ?>_all';
        jQuery('.card-link').each(function() {
            if (jQuery(this).attr('href') == selected_order) {
                jQuery(this).click();
                $('html, body').animate({
                    scrollTop: $(selected_order).offset().top - 150
                }, 1000);

            }

        });
        setTimeout(function() {
            jQuery('.uploadImages').hide();
        }, 1000);

    });
    <?php }?>
    </script>

    <script>
    var array = [];
    var array_refund = [];
    var product_id = "product_id";
    var cancel_product_id = "product_id";

    $(document).ready(function() {
        // $('#order_refund').submit(function (event) {
        //  ;
        //alert('5656')
    jQuery('body').on('click' , '.return_refund_submit' , function(event) {
        event.preventDefault();
   /// })
      //  $("#").click(function(event){
        // alert('called')
        // return true;
      $('.refund_select_price:checked').each(function() {
                $("#refund_popup_quantity").show();
                price = $(this).attr("data-refund_price");
                product_id = $(this).val();
                quantity = $(this).attr("data-refund_quantity");
                store_id = $(".store_id").val();
                buyer_id = $(".buyer_id").val();
                order_id = $(this).attr("data-order-id");
                cancelled = $("#Refunded_order").val();
                // message = $('textarea#reason_text').val(),
                refund_date = $("#refund_date").val();
                product_title = $(this).attr('data-product-title');
                                //refund_reason_type = $('#refund_reason').val();
                status = $(this).attr('data-status');

                var data_refund = {
                    'product_id': product_id,
                    'price': price,
                    'quantity': quantity,
                    'store_id': store_id,
                    'buyer_id': buyer_id,
                    'order_id': order_id,
                    'cancelled': cancelled,
                    'product_title': product_title,
              
                   // 'refund_reason_type': refund_reason_type,
                    'refund_date': refund_date,

                };
                array_refund.push(data_refund);
                console.log(array_refund);

            })
            refund_reason_type = $('.refund_reason_type_'+order_id).find(":selected").val();
            refund_reason = $('.refund_reason_'+order_id).val();

            var formData = {
                'refund_date': $('#refund_date').val(),
                'message': $('textarea#reason_text').val(),
                'order_id': order_id,
                'order_status': $("#order_status_refund").val(),
                'is_refund' : $("#Refunded_order").val(),
                'refund_reason_type':refund_reason_type,
                'message': refund_reason,
                'product_id': array_refund, // only this part has a problem
                'product_title': product_title,
            };

            $.ajax({
                url: 'refund_order',
                type: 'POST',
                data: formData,
                dataType: 'json',
                encode: true,
                success: function(response) {
                    $(".refund-order-popup-modal").hide();
                    $(".modal-backdrop").hide();
                        swal("Success!", response.success, "success");
                        setTimeout(function () {
                            window.location.reload();
                        }, 2000);
                }

            });
          

                //   var form = $("#order_refunded");
                //   $.ajax({
                //         type: "POST",
                //         url: 'refund_order',
                //         data: form.serialize(),
                //         success: function(data) {
                            
                //             // Ajax call completed successfully
                //             alert("Form Submited Successfully");
                //         },
                //         error: function(data) {
                            
                //             // Some error in ajax call
                //             alert("some Error");
                //         }
                //     });
        event.preventDefault();
    });
   
    $(document).on('change', '.refund_product_qty', function () {
        var product_quantity,update_quantity ;
     
               // data['quantity'] = $(this).val();
                //  price = $('#product-price').attr('data-price');
                product_quantity = $(this).val();
                update_quantity = $(this).closest(".select_price_cancel_order").find('form');
                // update_quantity = $(".select_price_cancel_order").data('quantity', product_quantity);

                console.log("product_quantity"+product_quantity);
                console.log(update_quantity);
                    // alert('called quantity');
                    // total = price * quantity;
                    // console.log("total "+total);
                     //console.log("product_quantity "+product_quantity);
                    // console.log("price "+price );
                   

                });
               
             //   console.log("product_quantity "+product_quantity);
    jQuery('body').on('click' , '.cancel_order_submit' , function(event) {
        event.preventDefault();
        // $('#cancel_order_submit').click(function(event) {
        
                  //  console.log("product_quantity "+product_quantity);
            $('.select_price_cancel_order:checked').each(function() {
                // var quantity = $('.refund_product_qty').find(":selected").val();
                price = $(this).attr("data-price");
                shipment = $(this).attr("data-shipment");
                order_id = $(this).attr("data-order-id");               
                product_id = $(this).val();
               // quantity = $(".refund_product_qty").val();
                quantity = $(this).attr("data-quantity");
                store_id = $("#store_id").val();
                buyer_id = $("#buyer_id").val();
                payment_method = $("#payment_method").val();
                console.log("payment_method", payment_method);
                
               // order_id = $("#order_id").val();
                cancelled = $("#cancle").val();
                product_title = $(this).attr('data-product-title');
                // console.log("product_title", product_title);
                // return false;
                refund_date = $("#refund_date").val();
             
                status = $(this).attr('data-status');
        
              
              // console.log("Quantitys "+quantity_store);
                var data = {
                    'product_id': product_id,
                    'price': price,
                    'shipment': shipment,
                    'quantity': quantity,
                    'store_id': store_id,
                    'buyer_id': buyer_id,
                    'order_id': order_id,
                    'cancelled': cancelled,
                    'payment_method': payment_method,
                    'product_title': product_title,
                    'refund_date': refund_date,

                };
                array.push(data);
                //array.push(product_quantity);
               

                
            });
            // $('.refund_product_qty').on("change",function(){
            // var dataid = $(".refund_product_qty option:selected").val();
            // alert(dataid);
            // });
            // refund_reason_type = $('.cancel_reason_type').val();
            cancel_reason_type = $('.cancel_reason_type_'+order_id).find(":selected").val();
            cancel_reason = $('.cancel_reason_'+order_id).val();

            // console.log(array);
   
            var formData_cancel = {
                'refund_date': $('#refund_date').val(),
                'message': $('textarea#reason').val(),
                'order_id': order_id,
                'order_status': $("#order_status").val(),
                'is_refund' : $("#cancle").val(),
                'cancel_product_id': array,
                'message': cancel_reason,
                'refund_reason_type': cancel_reason_type,
                'product_title': product_title,
                'payment_method': payment_method,
            };
            // console.log("formdata"+formData_cancel);
             //return false;

            $.ajax({
                url: 'cancel_order',
                type: 'POST',
                data: formData_cancel,
                dataType: 'json',
                encode: true,
                success: function(response) {
                    console.log("response cancel order",response );
                    // return false; 
                    // debugger
                    $(".refund-popup-modal").hide();
                    $(".modal-backdrop").hide();
                        swal("Success!", response.success, "success");
                        setTimeout(function () {
                            window.location.reload();
                        }, 2000);
                }

            });
            event.preventDefault();
        });

        // $('#return_refund_submit').click(function(event) {
        //     alert('called');
           
        //     $('.select_price:checked').each(function() {
        //         price = $(this).attr("data-price");
        //         product_id = $(this).val();
        //         quantity = $(this).attr("data-quantity");
        //         store_id = $("#store_id").val();
        //         buyer_id = $("#buyer_id").val();
        //         order_id = $("#order_id").val();
        //         cancelled = $("#cancle").val();
        //         message = $('textarea#reason').val(),
        //             refund_date = $("#refund_date").val();
        //         refund_reason_type = $('#refund_reason_type').val();
        //         status = $(this).attr('data-status');

        //         var data = {
        //             'product_id': product_id,
        //             'price': price,
        //             'quantity': quantity,
        //             'store_id': store_id,
        //             'buyer_id': buyer_id,
        //             'order_id': order_id,
        //             'cancelled': cancelled,
        //             'message': message,
        //             'refund_reason_type': refund_reason_type,
        //             'refund_date': refund_date,

        //         };
        //         array.push(data);

        //         console.log(array);
        //     })

        //     var formData = {
        //         'refund_date': $('#refund_date').val(),
        //         'message': $('textarea#reason').val(),
        //         'order_id': $("#order_id").val(),
        //         'order_status': $("#order_status").val(),
        //         'is_refund' : $("#cancle").val(),

        //         'product_id': array // only this part has a problem
        //     };

        //     $.ajax({
        //         url: 'refund_order',
        //         type: 'POST',
        //         data: formData,
        //         dataType: 'json',
        //         encode: true,
        //         success: function(response) {
        //             $(".refund-popup-modal").hide();
        //             $(".modal-backdrop").hide();
        //             console.log(response.details);
        //             swal("Success!", response.details, "success");
        //         }

        //     });
        //     event.preventDefault();
        // });


    });

 

// $("document").ready(function(){
//     //jQuery('body').on('click' , '.return_refund_submit' , function(event) {
//         var order_id,product_id,price;
//         jQuery('body','.select_price_cancel_order:checked').each(function() {
//             alert('called');
//         order_id = $(this).attr("data-order-id");
//         product_id = $(this).val();
//         price = $(this).attr("data-price");
            

//     });
//     console.log("order_id"+order_id);
//             console.log("product_id"+product_id);
//             console.log("price"+price);
// })
    
    $(function() {
        function getTotal(isInit) {
            var total = 0;
            var order_id = '';
           
            // console.log(order_id);

            var selector = isInit ? ".select_price_cancel_order" : ".select_price_cancel_order:checked";
            $(selector).each(function() {
                order_id = $(this).attr("data-order-id");
                total += parseInt($(this).attr("data-price"));
              //  console.log("total val "+ total);
            });
           //console.log("total  "+order_id);
            if (total == 0) {
                $('.total1').val('');
            } else {
                $('.total1').html("Returning Total: " + total + " USDC");
            }

        }

        $(".select_price").click(function(event) {
            getTotal();
        });
        getTotal(true);
    });
</script>

<script>
                        
    $("input[type='checkbox']").change(function(){
        if($(this).is(":checked")){
            // var selecter = $(".select_price > #refund-section").find();
         
            $(".cancel_popup_quantity").show();
            $("#refund_popup_quantity").show();
            $(this).parent().parent().addClass("redBackground"); 

            $(this).parent().parent().addClass("redBackground"); 
        }else{
            $(".cancel_popup_quantity").hide();
            $("#refund_popup_quantity").hide();
            console.log('checked');

            
            $(this).parent().parent().removeClass("redBackground");  
        $(this).parent().parent().removeClass("redBackground");  
            $(this).parent().parent().removeClass("redBackground");  
        }
    });

                          

$(document).on('change', '#refund_product_qty', function () {
    $(".large-loader").show();
    var data = {};
    data['product_id'] = $(this).attr('data-prodcut-id');
    data['quantity'] = $(this).val();
   
    price = $('#product-price').attr('data-price');
    quantity = $(this).val();
    if(quantity == 1){
        alert('called');
        total = price / quantity;
        console.log("ded "+total);
        console.log("quantity "+quantity);
        console.log("price "+price );
     

   
    }else{
        total = parseFloat(price) * quantity;
    }
    //total = parseFloat(price) * quantity;
    
    $('#product-price').html("USDC "+total.toFixed(2));
    usdprice = parseFloat(total / 10).toFixed(2);
  //  $("#product-price").html('(USDC ' + usdprice + ')');
    $(".large-loader").hide();
   // sendreq(data, 'shipping', 'calculate', 'callbackship');
    });


    $(document).on('change', '.cancel_popup_quantity', function () {
    $(".large-loader").show();
    var data = {};
    data['product_id'] = $(this).attr('data-prodcut-id');
    data['quantity'] = $(this).val();
   
    price = $('#product-price').attr('data-price');
    quantity = $(this).val();
    if(quantity == 1){
     
        total = price / quantity;
        console.log("ded "+total);
        console.log("quantity "+quantity);
        console.log("price "+price );
     

   
    }else{
        total = parseFloat(price) * quantity;
    }
    //total = parseFloat(price) * quantity;
    
    $('#product-price').html("USDC "+total.toFixed(2));
    usdprice = parseFloat(total / 10).toFixed(2);
  //  $("#product-price").html('(USDC ' + usdprice + ')');
    $(".large-loader").hide();
   // sendreq(data, 'shipping', 'calculate', 'callbackship');
    });

    
    </script>

    <style>
    textarea.has-error {
        border: 1px solid red;
    }
    .refund-order-popup {
        max-width: 850px;
    }
    .cancel-order-popup{
        max-width: 850px;
    }
    .text-status{
        margin: 40px;
    }
    </style>
@endsection