@extends('layouts.dashboard-main')
<!-- Main Banner Starts -->
@section('content')
@section('title', 'Mallshark')



<link rel="stylesheet" href="{{ URL::asset('assets/css/order-page.css') }}">

<style>
    .table-responsive {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }
</style>




<style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }
    .cancel-order-popup {
        max-width: 850px;
    }

    .order-status {
        width: fit-content
    }

    body {
        background-color: #F9FBFE;
    }

    .order-controller {
        padding: 0 3rem 0 18rem
    }

    .order-controller .order-status #status_list {
        box-shadow: 0px 3px 6px #00000029;
        border-radius: 8px;
    }

    .order-content-container {
        background: #fff;
        box-shadow: 0px 3px 6px #00000029;
        border-radius: 32px !important;
        min-height: 700px
    }

    .w-bold {
        font-weight: bold
    }

    .content-header li {
        opacity: 50%;
    }

    .content-data .fa-angle-down,
    .content-data .fa-angle-up {
        font-size: 1.2rem
    }

    .content-data a {
        color: #2d2d2d;
    }

    .content-data a:hover {
        color: #195dfa;
        text-decoration: none
    }

    .content-data li {
        font-size: 14px
    }

    .order-date,
    .order-date-value {
        min-width: 110px;
    }

    .action_btn {
        border-radius: 26px;
        color: #0510fa;
        border-color: #0510fa !important;
        cursor: pointer;
    }

    .dropdown-menu {
        box-shadow: 0px 0px 26px rgba(124, 124, 124, .3);
    }

    .accordion_btn {
        cursor: pointer;
    }

    .fw-bold {
        font-weight: 800;
    }
</style>


<div class="flex-parent-div bg-light-gray">
    <div class="change-password-page-cmt">
        @include('buyer.customsidebar')
    </div>

    <div class="order-product-page home-section cm-edit-ja">
        <div class="content container-fluid">
            <h3 class="buyer-order-heading d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-3" >
                    <a href="javascript:void(0)" class="d-flex align-items-center gap-10px sidebar-admin-burger-menu" id="open-sidebar-admin-panel">
                        <span id="menu-toggle-dashboard" class="shopzit-menu mt-0 ml-0 bx-menu">
                            <span></span><span></span><span></span> <span></span>
                        </span>
                        <!-- <span>Menu</span> -->
                    </a>
                    <span>Orders Details</span>
                </div>
                <a  class="btn btn-primary-back large-btn-mbt btn-primary-background back_button text-white" href="/buyer-orders"> Back</a>


            </h3>
            <?php
                        $status_text = '';
                        $status_color = '';
                        if($status == 'cancelled'){
                            $status_text = 'cancelled';
                            $status_color = '#dc3545';
                        }elseif($status == 'shipped'){
                            $status_text = 'shipped';
                            $status_color = '#17a2b8';
                        }elseif($status == 'pending'){
                            $status_text = 'pending';
                            $status_color = 'blue';
                        }elseif($status == 'refunded'){
                            $status_text = 'refunded';
                            $status_color = '6c757d';
                        }elseif($status == 'dispute'){
                            $status_text = 'disputed';
                            $status_color = '#343a40';
                        }
                        
            if (is_array($orders_all) && count($orders_all) > 0) {

                foreach ($orders_all as $key => $order) {
                    // dump($order);
                    
            ?>
                    <div class="row order-detrail-page">
                        <div class="col-lg-8 mb-3 mb-lg-0">
                            <!-- Card -->
                            <div class="card mycrd mb-3 mb-lg-5">
                                <!-- Header -->
                                <div class="card-header card-header-content-between">
                                <h3 class="card-header-title mb-2" style="margin-bottom: 0px!important;"><b>Order ID</b> <span class=" rounded-circle ms-1" style="color:#2f2f2f;">#{{$key}}</span></h3>
                               <b>Order Status: <span style="color:{{$status_color}}"><?php echo ucfirst($status_text); ?></span></b>

                                </div>
                                <div class="card-body">
                                    


                                <div class="d-flex w-100 hidemob">
                                          <div class="flex-shrink-0">
                                            <div class="avatar avatar-xl">
                                                
                                            </div>
                                        </div>  

                                        <div class="flex-grow-1 ms-3">
                                            <div class="row">
                                                <div class="col-md-4 mb-3 mb-md-0">
                                                    <a class="h5 d-block" style="color: #183b56;margin-left:-40px;"><b>Product Name</b></a>

                                                    <!-- <div class="fs-6 text-body">
                                                        <span>Gender:</span>
                                                        <span class="fw-semibold">Women</span>
                                                    </div> -->

                                                     
                                                    <!-- <div class="fs-6 text-body">
                                                        <span>Size:</span>
                                                        <span class="fw-semibold">UK 7</span>
                                                    </div> -->
                                                </div>
                                                <!-- End Col -->
                                                <div class="col col-md-2 text-center align-self-center">
                                                <h5><b>Unit Price</b></h5>
                                                </div>

                                                <div class="col col-md-2 text-center align-self-center">
                                                <h5><b>Quantity</b></h5>
                                                </div>
                                                <!-- End Col -->

                                                <div class="col col-md-2 text-center align-self-center">
                                                    <h5><b>Shipping</b></h5>
                                                </div>
                                                <!-- End Col -->

                                                <div class="col col-md-2 text-center align-self-center text-end" style="margin-left: -6px;">
                                                  
                                                    <h5><b> Total</b></h5>
                                                </div>
                                                <!-- End Col -->
                                            </div>
                                            <!-- End Row -->
                                        </div>
                                    </div>


                                    <?php
                                    $totalpriceSum = 0;
                                    $totalShippriceSum = 0;
                                    $buyer_order = [];
                                    // echo 'Data: <pre>' .print_r($order,true). '</pre>'; die();
                                    foreach ($order as $value) {
                                        // dd($value);
                                        if ($value->variation_id != '' && $value->variation_id != 0) {
                                            $variable_product = getvariableByProduct_id($value->variation_id);
                                            if (!empty($variable_product)) {
                                                $product_variation_image = isset($variable_product->product_variation_image) ? $variable_product->product_variation_image : '';
                                                  //$image_url = getAttachmentUrlById((int) $image, 'small');
                                                // dump($product_variation_image);
                                            }
                                        }
                                        $order_id = $value->order_id;
                                        $buyer_order['order_id'] = $order_id;
                                        $store_id = $value->store_id;
                                        $user_id = $value->buyer_id;
                                        $payment_status = $value->payment_status;
                                        $order_status = $value->order_status;
                                        $buyer_order['order_status'] = $order_status;
                                        $date = $value->created_at;
                                        $shipping_address = $value->street;
                                        $shipping_amount = $value->shipping_amount;
                                        $total_amount = $value->total_amount;
                                        $item_id = $value->item_id;
                                        $variation_price = $value->variation_price;
                                        $user_info = getUserById($user_id);

                                        $product = getProductId($value->item_id);
                                        // dump("product", $product);

                                        // $product_title = isset($product->title) ? $product->title : '';
                                        $product_title = isset($value->product_name) ? $value->product_name : $product->title;
                                        $product_id = isset($product->product_id) ? $product->product_id : '';
                                        $product_quantity = $value->quantity;
                                        $payment_method = $value->payment_method;
                                        $shipping_amount = isset($value->shipping_amount) ? $value->shipping_amount : "0";
                                        if(!empty($value->sale_id)){
                                            $price = $value->sold_price;
                                        }else{
                                            if(!empty($value->variation_price)){
                                                $price = $variation_price;
                                            }else{
                                                $price = $value->price;
                                            }
                                        }

                                        // if(!empty($variation_price)){
                                        //     $totalpriceSum += ($variation_price * $product_quantity);
                                        // }else{
                                        //     $totalpriceSum += ($price * $product_quantity);
                                        // }
                                        $totalpriceSum += ($price * $product_quantity);
                                        $totalShippriceSum += $shipping_amount;
                                        // if(!empty($variation_price)){
                                        //     $price_shipemnt = ($variation_price * $product_quantity);
                                        // }else{
                                        //     $price_shipemnt = ($price * $product_quantity);
                                        // }
                                        $price_shipemnt = ($price * $product_quantity);
                                        $product_type = !empty($product->product_type) ? $product->product_type : '';
                                        $comment = $value->comment;
                                        $rating = $value->rating;
                                        $pslug = isset($product->slug) ? $product->slug : '';
                                        if ($rating == '') {
                                            $rating = 0;
                                        }
                                        $image = isset($product->feature_image) ? $product->feature_image : '';
                                        $image = (int) $image;
                                        $image_url = '';
                                        // if ($image != '') {
                                        //     $image_url = getAttachmentUrlById($image, 'small');
                                        // }
                                        $variation_name = '';
                                        if ($value->variation_id != '') {
                                            $variation_name = getVariationNameById($value->variation_id);
                                        }
                                        // dd($image);
                                        if (!empty($image)) {
                                            if($product_type == 'simple' ){
                                                $image_url = getAttachmentUrlById((int) $image, 'small');
                                            }elseif($product_type == 'variant'){
                                                $image_url = getAttachmentUrlById((int) (isset($product_variation_image) && $product_variation_image != "" ? $product_variation_image : $image) , 'medium');
                                                if(empty($image_url)){
                                                    $image_url = getAttachmentUrlById((int) $image, 'small');
                                                }
                                            }else{ 
                                                $image_url = getAttachmentUrlById((int) $image, 'small');
                                            }
                                        }
                                        $image_url = isset($value->item_image) ? getOrderImage($value->item_image) : $image_url;


                                        //$product = getProductById($order->order_status);
                                        $payment_method = $value->payment_method;
                                        if ($user_info) {
                                            $name = $user_info->name;
                                        }

                                        $trackingId = isset($order->tracking_id) ? $value->tracking_id : '';

                                        $variation_name = '';

                                        // if ($value->variation_id != '' && $value->variation_id != 0) {
                                        //     $variation_name = getVariationNmaeById($value->variation_id);
                                        // }
                                        $variation_name = $value->variation_info ?? getVariationNameById($value->variation_id);

                                     ?>
                                    <div>
                                        <div class="d-flex w-100 mb-5" style="margin-bottom: 16px!important;">
                                            <div class="flex-shrink-0">
                                                <div class="avatar avatar-xl">
                                                    <img class="img-fluid cart-product-thumbnail" src="{{$image_url}}" alt="Image Description">
                                                </div>
                                            </div>

                                            <div class="flex-grow-1 ms-3">
                                                <div class="row">
                                                    <div class="col-md-4 mb-3 mb-md-0">
                                                        <a class="h5 d-block" style="color: #183b56;" href="{{ URL::to('/product/' . $pslug)}}"><b>{{$product_title}}</b></a>

                                                        <div class="fs-6 text-body">
                                                            <span>{{!empty($variation_name) ? $variation_name : ""}}</span>

                                                        </div>

                                                    </div>
                                                    <!-- End Col -->

                                                    <div class="col col-md-2 crtlwd text-center align-self-center">
                                                        <h5><span class="dskhid">Price:</span> ${{$price}}</h5>
                                                    </div>
                                                    <!-- End Col -->

                                                    <div class="col col-md-2 crtlwd text-center align-self-center">
                                                        <h5><span class="dskhid">QTY:</span> {{$product_quantity}}</h5>
                                                    </div>
                                                    <!-- End Col -->

                                                    <div class="col col-md-2 crtlwd text-center  align-self-center ">
                                                        <h5><span class="dskhid">Shipping:</span> ${{$shipping_amount}}</h5>
                                                    </div>

                                                    <div class="col col-md-2 crtlwd   align-self-center text-end">
                                                        <h5><span class="dskhid">Total:</span> ${{$price_shipemnt}}</h5>
                                                    </div>
                                                    <!-- End Col -->
                                                </div>
                                                <!-- End Row -->
                                            </div>
                                        </div>
                                        <span class=" " >
                                            <?php if ($order_status === 'complete') { ?>
                                                    <span>Your Review:&nbsp;

                                                        </a>&nbsp;
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
                                                    <?php if ($rating == 0) {?>
                                                    <button type="button" class="btn btn-primary btn-simple btn-sm btnauto getpid post_modal" data-product-id="<?php echo $item_id; ?>" data-order-id="<?php echo $order_id; ?>"  style="margin-left:10px;">Post
                                                        Review </button>

                                            <?php }?>
                                            <?php } ?>
                                                </span>  
                                                <span class="col-md-3">
                                                @if(!empty($product_type) && $product_type=='virtual')
                                                        <a class="btn btn-primary btn-simple btn-sm btnauto getpid"
                                                        href="<?php echo URL::to('/download-files/' . $product_id); ?>">DownLoad files</a>
                                                   
                                                @endif
                                                </span>
                                                </div>
                                                <hr>
                                               
                                    <?php

                                    }
                                  
                                    ?>
                                    <!-- End Media -->

                                    


                                    <div class="row justify-content-md-end mb-3 new-make-section">
                                    <div class="col-md-7 col-lg-7" style="border-right:1px solid rgba(0,0,0,.125); padding:0px 0px 0px 8px;">
                                        <h3>Order Actions</h3>

                                        <?php if ($buyer_order['order_status'] == 'refunded') {
                                        ?>
                                        
                                        <span class="text-warning">Refunded</span>
                                        <?php } elseif ($buyer_order['order_status'] == 'cancelled') { ?>
                                      
                                        <span class="text-danger">Refund Requested</span>

                                        <?php } elseif ($buyer_order['order_status'] == 'dispute') { ?>
                                        <span class="text-warning">Disputed</span>

                                        <?php } elseif($buyer_order['order_status'] == 'completed') { ?>

                                        <span class="text-success">
                                            Order Completed
                                        </span>
                                        <?php } else 
                                        { ?>

                                            <?php if ($buyer_order['order_status'] == 'pending') { ?>
                                            <button type="button" class="btn btn-primary btn-sm order-modal-btn" data-order-id="<?php echo $buyer_order['order_id']; ?>">Cancel Order</button>
                                            <?php } ?>
                                            <?php if ($buyer_order['order_status'] == 'shipped') { ?>
                                                <button type="button" class="btn btn-primary btn-sm order-modal-btn" data-order-id="<?php echo $buyer_order['order_id']; ?>">Refund Order</button>

                                            <?php } 
                                        } ?>
                                         <!-- Invoice Button -->
                                            <div class="mt-3">
                                                <a title="Download Invoice" 
                                                href="{{ route('order-pdf', $buyer_order['order_id']) }}" 
                                                class="btn btn-outline-secondary btn-sm">
                                                    Invoice <i class="fa fa-download"></i>
                                                </a>
                                            </div>
                                       

                                    </div>
                                        <div class="col-md-5 col-lg-5">
                                            <dl class="row shippingFeeBuyerOrder">

                                                <dt class="col-6 text-center">Shipping fee:</dt>
                                                <dd class="col-6 text-center">${{$totalShippriceSum}}</dd>
                                                <dt class="col-6 text-center">Price:</dt>
                                                <dd class="col-6 text-center">${{$totalpriceSum}}</dd>
                                               
                                                <dt class="col-6 text-center">Total:</dt>
                                                <dd class="col-6 text-center">${{$totalShippriceSum + ($totalpriceSum) }}</dd>
                                            </dl>
                                            <!-- End Row -->
                                        </div>
                                        <!-- End Col -->
                                    </div>
                                    <!-- End Row -->
                                </div>
                                <!-- End Body -->
                            </div>
                            <!-- End Card -->


                        </div>


                        <?php
                        // dump("order", $order[0]);
                        $buyer_id = $order[0]->buyer_id;
                        $store_id = $order[0]->store_id;
                        $buyerDetails  = getUserById($buyer_id);
                        $shippingDetails = DB::table('shipping_addresses')->where('sh_id',$order[0]->shipping_address)->first();
                        $BuyerTotalOrders = getBuyerOrdersCount($buyer_id, $store_id);
                        //  echo 'Data: <pre>' .print_r($BuyerTotalOrders,true). '</pre>'; die();
                        $country_code = isset($order[0]->country) ? $order[0]->country : '';
                        $country_code = str_replace('"', '', $country_code);
                        $country_code = (int) $country_code;
                        if ($country_code == 2) {
                            $country_name = 'CANADA';
                        } else {
                            $country_name = 'USA';
                        }
                        $order_state = isset($order[0]->state) ? $order[0]->state : '';
                        $state_name = DB::table('states')->where('state_id', '=', $order_state)->where('country_id', '=', $country_code)->select('state_name')->first();
                        $state_name = isset($state_name->state_name) ? $state_name->state_name : '';
                        $city = isset($order[0]->city) ? $order[0]->city : '';
                        $post_code = isset($order[0]->post_code) ? $order[0]->post_code : '';
                        $shipping_address = $order[0]->street . ' ' . $state_name . ' ' . $country_name;

                        $image = isset($buyerDetails->user_avatar) ? $buyerDetails->user_avatar : '';
                        $image = (int) $image;
                        $image_url = '';
                        if (!empty($image_url)) {

                            $image_url = getAttachmentUrlById((int) $image, 'medium');
                        } else {
                            $image_url = URL::asset('assets/images/icon.jpg');
                        }
                        ?>
                        <div class="col-lg-4">
                            <!-- Card -->
                            <div class="card mycrd">
                                <!-- Header -->
                                <div class="card-header">
                                    <h4 class="card-header-title" style="font-weight:700;">Customer</h4>
                                </div>
                                <!-- End Header -->

                                <!-- Body -->
                                <div class="card-body">
                                    <!-- List Group -->
                                    <ul class="list-group list-group-flush list-group-no-gutters contactinformation">
                                        <li class="list-group-item">
                                            <div class="d-flex align-items-center">
                                                <div class="avatar avatar-circle">
                                                    <img class="avatar-img" src="{{$image_url}}" alt="Image Description">
                                                </div>
                                                <div class="flex-grow-1 ms-3">
                                                    <span class="text-body text-inherit">{{$shippingDetails->full_name}}</span>
                                                </div>
                                            </div>
                                        </li>

                                        <li class="list-group-item">
                                            <div class="d-flex align-items-center">
                                                <div class="icon icon-soft-info icon-circle" style="margin-right: 0px;">
                                                    <i class="fa fa-cart-arrow-down" aria-hidden="true"></i>

                                                </div>
                                                <div class="flex-grow-1 ms-3">
                                                    <span class="text-body text-inherit">{{$BuyerTotalOrders}} orders</span>
                                                </div>
                                                <div class="flex-grow-1 text-end">
                                                    <i class="bi-chevron-right text-body"></i>
                                                </div>
                                            </div>
                                        </li>

                                        <li class="list-group-item">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <h5>Contact info</h5>

                                            </div>

                                            <ul class="list-unstyled list-py-2 text-body">
                                                <li><i class="bi-at me-2"></i>{{$buyerDetails->email}}</li>
                                                <li><i class="bi-phone me-2"></i>+{{$buyerDetails->contact_no}}</li>
                                            </ul>
                                        </li>

                                        <li class="list-group-item">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <h5>Shipping address</h5>
                                            </div>

                                            <span class="d-block text-body">
                                                {{$order[0]->street}}<br>
                                                {{$city }}<br>
                                                {{$post_code }}, {{$state_name}}<br>
                                                USA
                                            </span>
                                        </li>

                                        <!-- <li class="list-group-item d-none">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h5>Billing address</h5>
                                        <a class="link" href="javascript:;">Edit</a>
                                    </div>

                                    <span class="d-block text-body">
                                        45 Roker Terrace<br>
                                        Latheronwheel<br>
                                        KW5 8NW, London<br>
                                        UK
                                    </span>

                                    <div class="mt-3">
                                        <h5 class="mb-0">Mastercard</h5>
                                        <span class="d-block text-body">Card Number: ************4291</span>
                                    </div>
                                </li> -->
                                    </ul>
                                    <!-- End List Group -->
                                </div>
                                <!-- End Body -->
                            </div>
                            <!-- End Card -->
                        </div>
                    </div>


            <?php }
            } ?>
        </div>
    </div>
</div>

<!-- Post review -->
<div class="modal fade review-popup-modal" id="review-popup" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Post Review</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <section class='rating-widget'>
                <div class="modal-body" id="postModalBody">
                   
                </div>


            </section>
        </div>

    </div>
</div>
<div class="modal fade refund-popup-modal" id="orderModal" tabindex="-1" role="dialog"
    aria-labelledby="orderModalLabel" aria-hidden="true">
    <div class="modal-dialog cancel-order-popup" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title" id="orderModalLabel">Order Refund / Cancel</h3>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="orderModalBody" >
                <!-- Order details will be loaded here using JavaScript -->
            </div>

        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function() {
        let isModalLoading = false;

        $('.post_modal').click(function() {
            // Get the product_id from the data attribute
            if (isModalLoading) {
            return;
        }
            var productId = $(this).data('product-id');
            var orderId = $(this).data('order-id');

            isModalLoading = true;

            $.ajax({
                url: '/new-buyer-post-modal',
                type: 'POST',
                data: {
                    productId: productId,
                    orderId: orderId
                },
                success: function(data) {
                    $('#postModalBody').html(data);
                    $('#review-popup').modal('show');
                },
                error: function() {
                    alert('Failed to fetch order details.');
                },complete: function() {
                isModalLoading = false;
            }
            });
        });
    });

        $(document).ready(function() {
        $('.order-modal-btn').click(function() {
            var orderId = $(this).data('order-id');
            
            $.ajax({
                url: '/new-buyer-orders-modal',
                type: 'POST',
                data: {
                    order_id: orderId
                },
                success: function(data) {
                    // Update the modal content with the order details
                    $('#orderModalBody').html(data);
                    // Show the modal
                    $('#orderModal').modal('show');
                },
                error: function() {
                    alert('Failed to fetch order details.');
                }
            });
        });

        $(document).on('change', '.refund_product_qty', function() {
            var product_quantity, update_quantity;
            product_quantity = $(this).val();
            update_quantity = $(this).closest(".select_price_cancel_order").find('form');
        });
        
        var selectProductCheckbox = 0;
        jQuery('body').on('click', '.cancel_order_submit', function(event) {
            event.preventDefault();

            var selectProductCheckbox = $('.select_price_cancel_order:checked').length;
            var refund_reason_type = $('#refund_reason_type').val();
            var someOtherReason = $('.some_other_reason').val();
            var hasError = false;

            if (parseInt(selectProductCheckbox) === 0) {
                $('.select_price_cancel_order').parent().parent().css('border', 'red 1px solid');
                hasError = true;
            } else {
                $('.select_price_cancel_order').parent().parent().css('border', 'initial');
            }

            if (refund_reason_type === '') {
                $('.refund_reason_type').css('border', 'red 1px solid');
                hasError = true;
            } else {
                $('.refund_reason_type').css('border', 'initial');
            }

            if (someOtherReason === '') {
                $('.some_other_reason').css('border', 'red 1px solid');
                hasError = true;
            } else {
                $('.some_other_reason').css('border', 'initial');
            }

            if (hasError) {
                return false;
            }
            var array = [];
            $('.select_price_cancel_order:checked').each(function() {

                price = $(this).attr("data-price");
                shipment = $(this).attr("data-shipment");
                order_id = $(this).attr("data-order-id");
                product_id = $(this).val();
                quantity = $(this).attr("data-quantity");
                store_id = $("#store_id").val();
                buyer_id = $("#buyer_id").val();
                payment_method = $("#payment_method").val();
                cancelled = $("#cancle").val();
                product_title = $(this).attr('data-product-title');
                product_slug = $(this).attr('data-product-slug');
                refund_date = $("#refund_date").val();
                status = $(this).attr('data-status');
                p_image = $(this).attr("data-image");
                variation_id = $(this).attr('data-variation-id') ? $(this).attr('data-variation-id') : '';
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
                    'product_slug': product_slug,
                    'refund_date': refund_date,
                    'product_image': p_image,
                    'variation_id':variation_id
                };
                array.push(data);

                isFormValid = true;
            });

            cancel_reason_type = $('.cancel_reason_type_' + order_id).find(":selected").val();
            cancel_reason = $('.cancel_reason_' + order_id).val();

            var formData_cancel = {
                'refund_date': $('#refund_date').val(),
                'message': $('textarea#reason').val(),
                'order_id': order_id,
                'order_status': $("#order_status").val(),
                'is_refund': $("#cancle").val(),
                'cancel_product_id': array,
                'message': cancel_reason,
                'refund_reason_type': cancel_reason_type,
                'product_title': product_title,
                'product_slug': product_slug,
                'payment_method': payment_method,
                'product_image': p_image,
                'buyer_id': buyer_id,
            };
            $("#loading").show();
            $.ajax({

                url: '{{ route('cancel_order') }}',
                type: 'POST',
                data: formData_cancel,
                dataType: 'json',
                encode: true,
                success: function(response) {

                    $("#loading").hide();
                    $("#orderModal").hide();
                    $('#orderModal').modal('hide');
                    // $(".modal-backdrop").hide();


                    swal("Success!", response.success, "success");
                    setTimeout(function() {
                        window.location.reload();
                    }, 3000);
                }
            });
            event.preventDefault();
        });

    });

    
   
</script>
@endsection