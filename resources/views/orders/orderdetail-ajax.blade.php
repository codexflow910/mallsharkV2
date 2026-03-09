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
    .cancel-order-popup {
        max-width: 850px;
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
<div class="flex-parent-div bg-light-gray">
    <div class="change-password-page-cmt">
        @include('buyer.customsidebar')
    </div>
    <div class="order-product-page home-section cm-edit-ja">
        <div class="content container-fluid">
        <div id="loading" class="show-setion" style="display:none;">
                    <img id="loading-image" src="{{ asset('assets/images/Loader-Animation.gif') }}" alt="Loading..." />
                </div>
        <h3 class="buyer-order-heading d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-3">     
                    <a href="javascript:void(0)" class="d-flex align-items-center gap-10px sidebar-admin-burger-menu" id="open-sidebar-admin-panel">
                        <span id="menu-toggle-dashboard" class="shopzit-menu mt-0 ml-0 bx-menu">
                            <span></span><span></span><span></span> <span></span>
                        </span>
                        <!-- <span>Menu</span> -->
                    </a>
                    <span>Orders Details</span>
                    </div>
                    <a  class="btn btn-primary-back large-btn-mbt btn-primary-background back_button text-white" href="/brand-orders"> Back</a>

               

            </h3>
            <div class="row order-detrail-page">

                <?php
                // echo 'Data: <pre>' .print_r($orderDetails,true). '</pre>'; die();
                foreach ($orderDetails as $orders) { 
                    $status_text = '';
                    $status_color = '';
                    if($orders->order_status == 'cancelled'){
                        $status_text = 'cancelled';
                        $status_color = '#dc3545';
                    }elseif($orders->order_status == 'shipped'){
                        $status_text = 'shipped';
                        $status_color = '#17a2b8';
                    }elseif($orders->order_status == 'pending'){
                        $status_text = 'pending';
                        $status_color = 'blue';
                    }elseif($orders->order_status == 'refunded'){
                        $status_text = 'refunded';
                        $status_color = '6c757d';
                    }elseif($orders->order_status == 'dispute'){
                        $status_text = 'disputed';
                        $status_color = '#343a40';
                    }

                    // dump("details" ,$orders );
                    ?>
                    <div class="col-lg-8 mb-3 mb-lg-0">
                        <!-- Card -->
                        <div class="card mycrd mb-3 mb-lg-5">
                            <!-- Header -->
                            <div class="card-header card-header-content-between"> 
                          
                            <h3 class="card-header-title mb-2"><b>Order ID</b> <span class=" text-dark rounded-circle ms-1">#{{$orders->order_id}}</span></h3>
                               <b>Order Status: <span style="color:{{$status_color}}">{{ucfirst($status_text)}}</span></b>
                                <!-- <a class="link" href="javascript:;">Edit</a> -->
                            </div>
                            <!-- End Header -->

                            <!-- Body -->
                            <div class="card-body">
                                
                                <div class="d-flex w-100 hidemob">
                                          <div class="flex-shrink-0">
                                            <div class="avatar avatar-xl">
                                                
                                            </div>
                                        </div>  

                                        <div class="flex-grow-1 ms-3" style="margin-right: 5px;">
                                            <div class="row">
                                                <div class="col-md-4 mb-3 mb-md-0">
                                                    <a class="h5 d-block" style="font-weight: bold;color:#183b56!important;margin-left:-40px;" >Product Name</a>

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
                                                <h5>Unit Price</h5>
                                                </div>

                                                <div class="col col-md-2 text-center align-self-center">
                                                <h5>Quantity</h5>
                                                </div>
                                                <!-- End Col -->

                                                <div class="col col-md-2 text-center align-self-center">
                                                    <h5>Shipping</h5>
                                                </div>
                                                <!-- End Col -->

                                                <div class="col col-md-2 text-center align-self-center text-end">
                                                  
                                                    <h5><b> Total</b></h5>
                                                </div>
                                                <!-- End Col -->
                                            </div>
                                            <!-- End Row -->
                                        </div>
                                    </div>


                                <?php
                                if(count($orders['orderItems']) > 0){

                                // dd($orders['orderItems']);

                                $total_price = 0;
                                $seller_order = [];
                                foreach ($orders['orderItems'] as $detail) {
                                    $seller_order['shipment_id'] = $detail->shipment_id;
                                    
                                    $tracking_code = isset($detail->tracking_code) ? $detail->tracking_code: "";
                                    $order_status = isset($detail->order_status) ? $detail->order_status: "";
                                    // if(!empty($detail->sale_id)){
                                    //         $price = $detail->sold_price;
                                    //     }else{
                                    //         $price = $detail->price;
                                    //     }
                                    $price = $detail->sold_price && $detail->sold_price != 0 ? $detail->sold_price : $detail->price;
                                    $total_price += $price * $detail->quantity;
                                    $productDetails = getProductId($detail->item_id);
                                    if($detail->variation_id != 0){

                                        $variable_product = getvariableByProduct_id($detail->variation_id);
                                    }
                                    $product_variation_image = '';
                                    if (!empty($variable_product)) {
                                        $product_variation_image = isset($variable_product->product_variation_image) ? $variable_product->product_variation_image : '';
                                    }
                                    // echo 'Data: <pre>' .print_r($product_variation_image,true). '</pre>'; die();
                                    $product_type = isset($productDetails->product_type) ? $productDetails->product_type : '';
                                    $product_title = isset($detail->product_name) ? $detail->product_name : $productDetails->title;
                                    $pslug = isset($productDetails->slug) ? $productDetails->slug : '';
                                    $image = isset($productDetails->feature_image) ? $productDetails->feature_image : '';
                                    $image = (int) $image;
                                    $image_url = '';
                                    if($product_type == 'simple' ){
                                        $image_url = getAttachmentUrlById((int) $image, 'small');
                                        }elseif($product_type == 'variant'){
                                            if($product_variation_image){

                                                $image_url = getAttachmentUrlById((int) $product_variation_image, 'medium');
                                            }
                                            if(empty($image_url)){
                                                $image_url = getAttachmentUrlById((int) $image, 'small');

                                            }
                                        }else{ 
                                            $image_url = getAttachmentUrlById((int) $image, 'small');
                                    }
                                    $image_url = isset($detail->item_image) ? getOrderImage($detail->item_image) : $image_url;
                                    $variation_name = '';
                                    // if ($detail->variation_id != '') {
                                    //      $variation_name = getVariationNmaeById($detail->variation_id);
                                    // }
                                    $variation_name = $detail->variation_info ?? getVariationNameById($detail->variation_id);
                                    // dump("product", $productDetails);
                                ?>
                                    <!-- Media -->
                                     
                                    <div class="d-flex w-100">
                                        <div class="flex-shrink-0">
                                            <div class="avatar avatar-xl">
                                                <img class="img-fluid cart-product-thumbnail" src="{{$image_url}}" alt="{{$product_title}}">
                                            </div>
                                        </div>

                                        <div class="flex-grow-1 ms-3" style="margin-right: 5px;">
                                            <div class="row">
                                                <div class="col-md-4 mb-3 mb-md-0">
                                                    <a class="h5 d-block" style="color:#183b56!important;" href="<?php echo URL::to('/product/' . $pslug); ?>"><b>{{$product_title}}</b></a>

                                                    <div class="fs-6 text-body"> 
                                                        <span>{{$variation_name}}</span>
                                                    </div>
                                                
                                                </div>
                                                <!-- End Col -->

                                                <div class="col col-md-2 crtlwd text-center align-self-center">
                                                <h5> <span class="dskhid">$</span> ${{$price}}</h5>
                                                </div>
                                                <!-- End Col -->

                                                <div class="col col-md-2 crtlwd text-center align-self-center">
                                                    <h5><span class="dskhid">QTY:</span> {{$detail->quantity}}</h5>
                                                </div>
                                                <!-- End Col -->

                                                <div class="col col-md-2 crtlwd text-center align-self-center  ">
                                                  
                                                    <h5><span class="dskhid">Shipping:</span> ${{$detail->shipping_amount}}</h5>
                                                </div>

                                                <div class="col col-md-2 crtlwd text-center align-self-center text-end">
                                                    <h5><span class="dskhid">Total :</span> ${{$price * $detail->quantity}}</h5>
                                                </div>
                                                <!-- End Col -->
                                            </div>
                                            <!-- End Row -->
                                        </div>
                                    </div>
                                    <!-- End Media -->
                                    <?php
                                        $path =  url('downloads/labels/');
                                    
                                    if(!empty($tracking_code) && $order_status != 'completed' ){
                                        
                                    ?>
                                    <div class="col-md-3" style="padding-left: 0px!important;margin-top:12px;">
                                        <div class="shipmet_data d-flex">

                                        <a style="padding: 4px 20px;" href="<?php echo $path.'/'.$tracking_code.'.png' ?>" class="btn btn-primary btn-sm mr-1" target="_blank">Label <i class="fa fa-download" aria-hidden="true"></i></a>  
                                        <a style="padding: 4px 20px; margin-left:10px;" href="<?php echo $path.'/'.$tracking_code.'.pdf' ?>" class="btn btn-primary btn-sm" target="_blank">PDF Label <i class="fa fa-download" aria-hidden="true"></i></a>  
                                        </div>
                                    </div>
                                    <?php } ?>
                                    <hr>
                                <?php } 
                                // echo 'Data: <pre>' .print_r($orders,true). '</pre>'; die();
                                }
                                ?>

                                    
                                    <div class="row justify-content-md-end mb-3 new-make-section">
                                        <div class="col-md-7 col-lg-7" style="border-right:1px solid rgba(0,0,0,.125);">
                                            <h3>Order Actions</h3> 
                                            <?php if(count($orders['orderItems']) > 0) { ?>
                                                <?php foreach ($orders['orderItems'] as $item) { ?>
                                                    <input type="hidden" class="{{ $item->order_id }}_easypost_shipmet_id"
                                                        value="{{ $item->shipment_id }}" data-payment_method="{{ $item->method }}"
                                                        data-service="{{ $item->payment_service }}"
                                                        data-order_item_id="{{ $item->order_item_id }}">
                                                <?php } ?>
                                            <?php } ?>

                                            <?php if ($orders->order_status == 'refunded') { ?>
                                                <div class="text-warning">Refunded</div>
                                            <?php } else if ($orders->order_status == 'dispute') { ?>
                                                <div class="text-dark">Disputed</div>
                                            <?php } elseif ($orders->order_status == 'cancelled') { ?>
                                                <button type="button" class="btn btn-sm btn-danger order-modal-btn"
                                                    data-order-id="<?php echo $orders->order_id; ?>">Accept Refund</button>
                                            <?php } elseif($orders->order_status == 'completed') { ?>
                                                <div class="text-success">Order Completed</div>
                                            <?php } else { ?>
                                                <?php if ($orders->order_status != 'shipped' && $orders->order_status != 'refunded' && $orders->order_status != 'cancelled') { ?>
                                                    <button type="button" data-status="shipped" data-order_id="{{ $orders->order_id }}"
                                                        data-easypost_shop_id="{{ isset($seller_order['shipment_id']) ? $seller_order['shipment_id'] : '' }}"
                                                        data-payment-method="{{ $orders->payment_method }}"
                                                        class="btn btn-primary btn-sm change-order-status"
                                                        data-notification="Marking this order as shipped means that your items have been sent to the shipping provider for delivery">Ship</button>
                                                <?php } ?>
                                                <?php if ($orders->order_status != 'cancelled' && $orders->order_status != 'refunded' && $orders->order_status != 'shipped') { ?>
                                                    <button type="button" data-shipped-cancel="0" data-status="cancelled"
                                                        data-order_id="{{ $orders->order_id }}" total-amount="{{ $orders->total_amount }}"
                                                        data-payment-method="{{ $orders->payment_method }}"
                                                        wallet-address="{{ $orders->buyer_address }}" class="btn btn-primary btn-sm change-order-status"
                                                        style="margin-left: 18px;" data-notification="Should you choose to cancel this order, MallShark will reimburse the buyer for the corresponding transaction amount">Cancel</button>
                                                <?php } ?>
                                                <?php if ($orders->order_status != 'cancelled' && $orders->order_status != 'refunded' && $orders->order_status == 'shipped') { ?>
                                                    <button type="button" data-shipped-cancel="1" data-status="cancelled"
                                                        data-order_id="{{ $orders->order_id }}" total-amount="{{ $orders->total_amount }}"
                                                        data-payment-method="{{ $orders->payment_method }}"
                                                        wallet-address="{{ $orders->buyer_address }}" class="btn btn-primary btn-sm change-order-status"
                                                        data-notification="Should you choose to cancel this order, MallShark will reimburse the buyer for the corresponding transaction amount">Cancel Order</button>
                                                <?php } ?>
                                            <?php } ?>
                                            <!-- Invoice Button -->
                                            <div class="d-flex justify-content-start mt-3">
                                                <a title="Download Invoice" href="{{ route('order-pdf', $orders->order_id) }}" class="btn btn-outline-secondary btn-sm">
                                                    Invoice <i class="fa fa-download"></i>
                                                </a>
                                            </div>
                                        </div>

                                        <div class="col-md-5 col-lg-5">
                                            <dl class="row text-sm-end shippingFeeBrandOrder">
                                                <dt class="col-6 text-center">Shipping fee:</dt>
                                                <dd class="col-6 text-center">${{$orders->shipping_amount}}</dd>
                                                <dt class="col-6 text-center">Price:</dt>
                                                <dd class="col-6 text-center">${{isset($total_price) ? $total_price : ''}}</dd>
                                                
                                                <dt class="col-6 text-center">Total:</dt>
                                                <dd class="col-6 text-center">${{$orders->total_amount}}</dd>
                                            </dl>
                                        </div>
                                    </div>


                                <!-- End Row -->
                            </div>
                            <!-- End Body -->
                        </div>
                        <!-- End Card -->


                    </div> 

                    <div class="col-lg-4">
                        <!-- Card -->
                        <div class="card mycrd">
                            <!-- Header -->
                            <div class="card-header">
                                <h4 class="card-header-title"><b>Customer Info</b></h4>
                            </div>
                            <!-- End Header -->
                            <?php
                                $image = isset($orders['user']->user_avatar) ? $orders['user']->user_avatar : '';
                                $image = (int) $image;
                                $image_url = '';
                                    if(!empty($image_url)){

                                        $image_url = getAttachmentUrlById((int) $image, 'medium');
                                    } else{
                                        $image_url = URL::asset('assets/images/icon.jpg');
                                    }   
                                        //echo $orders->buyer_id;
                                    $countOrders = getBuyerOrdersCount($orders->buyer_id, $orders->store_id);
                            ?>
                            <!-- Body -->
                            <div class="card-body">
                                <!-- List Group -->
                                <ul class="list-group list-group-flush list-group-no-gutters contactinformation">
                                    <li class="list-group-item">
                                        <div class="d-flex align-items-center">
                                            <div class="avatar avatar-circle">
                                                <img class="avatar-img" src="{{$image_url }}" alt="{{isset($orders['user']->name) ? $orders['user']->name: ''}}">
                                            </div>
                                            <div class="flex-grow-1 ms-3">
                                                <span class="text-body text-inherit">{{isset($orders['shippingAddress']->full_name) ? $orders['shippingAddress']->full_name: ""}}</span>
                                            </div>
                                        </div>
                                    </li>

                                    <li class="list-group-item">
                                        <div class="d-flex align-items-center">
                                            <div class="icon icon-soft-info icon-circle" style="margin-right: 0px;">
                                                <i class="fa fa-cart-arrow-down" aria-hidden="true"></i>

                                            </div>
                                           
                                            <div class="flex-grow-1 ms-3">
                                                <span class="text-body text-inherit">{{$countOrders}} orders</span>
                                            </div>
                                            <div class="flex-grow-1 text-end">
                                                <i class="bi-chevron-right text-body"></i>
                                            </div>
                                        </div>
                                    </li>

                                    <li class="list-group-item">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <h5>Contact info</h5>
                                            <!-- <a class="link" href="javascript:;">Edit</a> -->
                                        </div>

                                        <ul class="list-unstyled list-py-2 text-body">
                                            <li><i class="bi-at me-2"></i>{{!empty($orders['user']->email) ? $orders['user']->email: ""}}</li>
                                            <li><i class="bi-phone me-2"></i>{{!empty($orders['user']->contact_no) ?  '+'.$orders['user']->contact_no : ""}}</li>
                                        </ul>
                                    </li>

                                    <li class="list-group-item">
                                        <?php
                                        if(!empty($orders['shippingAddress']->state)){
                                        ?>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <h5>Shipping address</h5>
                                            <!-- <a class="link" href="javascript:;">Edit</a> -->
                                        </div>
                                        <?php
                                        if($orders['shippingAddress']->state){
                                            $state = getStatebyId($orders['shippingAddress']->state);
                                            $state_name = $state['state_name'];
                                        }
                                        ?>
                                        <span class="d-block text-body">
                                            {{!empty($orders['shippingAddress']->street) ?  $orders['shippingAddress']->street : ""}}<br>
                                            {{!empty($orders['shippingAddress']->city) ?  $orders['shippingAddress']->city: ""}}<br>
                                            {{!empty($orders['shippingAddress']->post_code) ? $orders['shippingAddress']->post_code : ""}}, {{!empty($state_name) ? $state_name: ""}}<br>
                                            USA
                                        </span>
                                        <?php } ?>
                                        <a title="download" style="color:#007bff;font-size:14px"
                                            @if (isset($_GET['order_id']) && $_GET['order_id'] == $orders->order_id) class="download-link" @endif
                                            href="{{ route('order-pdf', $orders->order_id) }}">Print Shipping Label <i
                                                class="fa fa-download"></i></a>
                                    </li>

                                    <!-- <li class="list-group-item">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <h5>Billing address</h5> -->
                                            <!-- <a class="link" href="javascript:;">Edit</a> -->
                                        <!-- </div> -->

                                        <!-- <span class="d-block text-body">
                                            45 Roker Terrace<br>
                                            Latheronwheel<br>
                                            KW5 8NW, London<br>
                                            UK
                                        </span> -->

                                    <!-- </li> -->
                                        <!-- <div class="mt-3">
                                            <h5 class="mb-0">Mastercard</h5>
                                            <span class="d-block text-body">Card Number: ************4291</span>
                                        </div> -->
                                </ul>
                                <!-- End List Group -->
                            </div>
                            <!-- End Body -->
                        </div>
                        <!-- End Card -->
                    </div>
                <?php } ?>
            </div>

        </div>
    </div>
</div>
<div class="modal fade" id="orderModal" tabindex="-1" role="dialog" aria-labelledby="orderModalLabel" aria-hidden="true">
    <div class="modal-dialog cancel-order-popup" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title" id="orderModalLabel">Product Refund / Cancel</h3>
                <button type="button" class="close modal-close" data-dismiss="modal"
                    aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="orderModalBody">
                <!-- Order details will be loaded here using JavaScript -->
            </div>

        </div>
    </div>
</div>
<!-- Include jQuery library if not already included -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
     var amountI = 1;
    $(document).on('keyup', '#cancel_order_price', function() {
        var $field = $(this);
        amountI = parseFloat($field.val());


    });
    var array = [];
    var product_id = "product_id";
    var wallet_address = "";
    var refund_complate_array = []
    $('#reject_submit').click(function(event) {
        
        $('#refund_product_id').each(function() {
            product_id = $(this).val();
            console.log(product_id);
        });
    });

    $(document).ready(function() {
        $(".refunded").on("click", () => {
            TrigerSweetAler()
        })


        $(document).on("click", ".accept_amount", function() {

            let OrderId = $(this).attr('data-order_id');
            let payment_method = $(this).attr('data-payment_method');

            $('.cancel_order_accept_' + OrderId).each(function() {
                // alert('called');
                $(".cancel-order-popup").hide();
                let loader = jQuery('#loading').show();

                $(".modal-backdrop").hide();
                product_id = $(this).attr('data-product-id');
                product_quantity = $(this).attr('data-quantity');
                product_price = $(this).val();
                product_order_id = $(this).attr('data-order-id');
                wallet_address = $(this).attr('wallet-address');
                payment_method = $(this).attr('data-payment_method');
                var formComplete = {
                    'product_id': product_id,
                    'product_quantity': product_quantity,
                    'product_order_id': product_order_id,
                    'payment_method': payment_method,


                };
                refund_complate_array.push(formComplete);
            });

            var formData = {

                'amount': product_price,
                'order_id': product_order_id,
                'refund_process': refund_complate_array,
                'payment_method': payment_method,
            };
            let tm = amountI;
            if (amountI > 0) {
                let refundResponse = window.refundPaymentNode(amountI, wallet_address).then(res => {

                    // change response res?.data?. to res?. for using app end refund
                    if (res?.data?.status == true) {

                        $.ajax({
                            url: '{{ route('return_refund') }}',
                            type: 'POST',
                            data: formData,
                            dataType: 'json',
                            encode: true,
                            success: function(response) {

                                $(".cancel-order-popup").hide();
                                $(".modal-backdrop").hide();

                                jQuery('#loading').hide();
                                swal("Success!", 'Your Order Refunded is Complete',
                                    "success");
                                setTimeout(function() {
                                    window.location.reload();
                                }, 2000);
                            },
                            error: function(response) {

                                $(".cancel-order-popup").hide();
                                $(".modal-backdrop").hide();
                                jQuery('#loading').hide();

                                //   swal('error!', 'Order Refund Failed!', 'error');
                                setTimeout(function() {
                                    window.location.reload();
                                }, 2000);
                            }

                        });

                    } else if (res?.code == 417) {
                        // this transction is failed due to low balance
                        console.log('Your escrow balance is low for this transaction')
                        swal('error!', 'Your escrow balance is low for this transaction!',
                            'error');
                        setTimeout(function() {
                            window.location.reload();
                        }, 3000);
                    } else if (res?.code == "INVALID_ARGUMENT" && res?.value == "refundee") {
                        // this transction is failed due long refundee address which is to address of transactin
                        console.log('Refundee address is wrong')
                        swal('error!', 'Customer wallet address is wrong!', 'error');
                        setTimeout(function() {
                            window.location.reload();
                        }, 3000);
                    } else {
                        $(".cancel-order-popup").hide();
                        $(".modal-backdrop").hide();


                        // setTimeout(function () {
                        //         window.location.reload();
                        //     }, 2000);
                        console.log('refundpaymentNode response failed')
                        jQuery('#loading').hide();
                    }
                });

            } else {

            }
        });

        $(".modal-close").on('click', function() {

            $("#orderModal").modal("hide");
        });
        // Remove modal backdrop when modal is closed

        $(document).on("click", ".accept_amount_stripe", function() {

            let OrderId = $(this).attr('data-order_id');
            let payment_method = $(this).attr('data-payment_method');
            let price = $(this).attr('data-price-product');



            $('.cancel_order_accept_' + OrderId).each(function() {
                // alert('called');
                $(".cancel-order-popup").hide();
                let loader = jQuery('#loading').show();

                $(".modal-backdrop").hide();
                product_id = $(this).attr('data-product-id');
                product_quantity = $(this).attr('data-quantity');
                product_price = $(this).val();
                product_order_id = $(this).attr('data-order-id');
                wallet_address = $(this).attr('wallet-address');
                payment_method = $(this).attr('data-payment_method');
                var formComplete = {
                    'product_id': product_id,
                    'product_quantity': product_quantity,
                    'product_order_id': product_order_id,
                    'payment_method': payment_method,


                };
                refund_complate_array.push(formComplete);

            });
            var formData = {

                'amount': product_price,
                'order_id': product_order_id,
                'refund_process': refund_complate_array,
                'payment_method': payment_method,
            };
            $(".cancel-order-popup").hide();
            $(".modal-backdrop").hide();

            $.ajax({
                url: '{{ route('return_refund_stripe') }}',
                type: 'POST',
                data: formData,
                dataType: 'json',
                encode: true,
                success: function(response) {

                    $(".cancel-order-popup").hide();
                    $(".modal-backdrop").hide();

                    jQuery('#loading').hide();
                    swal("Success!", 'Your Order Refunded is Complete', "success");
                    setTimeout(function() {
                        window.location.reload();
                    }, 2000);
                },
                error: function(response) {
                    swal('error!', 'Order Refund Failed!', 'error');
                    // setTimeout(function () {
                    //     window.location.reload();
                    // }, 2000);
                }

            });
        });


    });


    function TrigerSweetAler() {
        swal({
                title: "Are you sure?",
                text: "Canceling order will refund buyer",
                type: "warning",
                showCancelButton: true,
                confirmButtonClass: "btn-success",
                confirmButtonText: "Yes, Do it!",
                closeOnConfirm: true
            },
            function() {

            }
        )
    }

// Assuming you have a URL where you want to send the AJAX request
var ajaxUrl = '{{ route('brand-order-details-status') }}';

jQuery(".change-order-status").click(function() {
    // Get the data attributes
    var order_id = jQuery(this).attr('data-order_id');
    var easypost_shop_id = jQuery(this).attr('data-easypost_shop_id');
    var total_ammount = jQuery(this).attr('total-amount');
    var wallet_addresss = jQuery(this).attr('wallet-address');
    var vendor = 'vendor';
    var payment_method = jQuery(this).attr('data-payment-method');
    var data_shipped_cancel = jQuery(this).attr('data-shipped-cancel');
    var status = jQuery(this).attr('data-status');
    var notification = jQuery(this).attr('data-notification');


        var ship_id = [];
        $('.' + order_id + '_easypost_shipmet_id').each(function(key, value) {
            
            shipping_id = $(this).val();
            payment_service = $(this).attr('data-service');
            order_item_id = $(this).attr('data-order_item_id');
            payment_method_easy = $(this).attr('data-payment_method');

            var obj = {};
            obj[payment_service] = {
                'shipping_id': shipping_id,
                'order_item_id': order_item_id,
                'payment_method': payment_method_easy,

            };
            var ship_id_json = JSON.stringify(obj);
            ship_id.push(ship_id_json);

        });

    var requestData = {
        order_id: order_id,
        ship_id: ship_id,
        total_ammount: total_ammount,
        wallet_addresss: wallet_addresss,
        is_refund: vendor,
        payment_method: payment_method,
        shipped_cancel: data_shipped_cancel,
        status: status
    };
    // Show a confirmation dialog
    swal({
        title: "Are you sure?",
        text: notification,
        type: "warning",
        showCancelButton: true,
        confirmButtonClass: "btn-success",
        confirmButtonText: "Yes, Do it!",
        closeOnConfirm: true
    }, function() {
        jQuery('#loading').show();
        jQuery.ajax({
            url: ajaxUrl,
            type: 'POST',
            data: requestData,
            dataType: 'json',
            success: function(response) {
                jQuery('#loading').hide();
                if (response.status === true) {

                    dataLayer.push({ ecommerce: null }); 
                        dataLayer.push({
                        event: "refund",
                        ecommerce: {
                            transaction_id: response.transactionId,
                            value: response.total_amount,
                            tax: 0,
                            shipping: response.shipping_amount,
                            currency: "USD",
                            items: response.orderData 
                        }
                    });

                    toastr.options = {
                        closeButton: true, // Add close button
                        disableTimeOut: true,
                        tapToDismiss: true,
                        positionClass: 'toast-top-right',
                        toastClass: 'toast-icon custom-toast-success'
                        };
                    toastr.success(response.message);
                    setTimeout(() => {
                        window.location.reload();
                    }, 2000);

                } else {
                    // Handle error
                        toastr.options = {
                            closeButton: true, // Add close button
                            disableTimeOut: true,
                            tapToDismiss: true,
                            positionClass: 'toast-top-right',
                            toastClass: 'toast-icon custom-toast-success'
                            };
                        toastr.error(response.message);
                    setTimeout(function() {
                        window.location.reload();
                    }, 2000);
                }
            },
            error: function(xhr, status, error) {
                // Handle AJAX error
                console.error(xhr, status, error);
            }
        });
    });
});
$(document).ready(function() {
        // Add a click event listener to all buttons with the 'order-modal-btn' class
        $('.order-modal-btn').click(function() {
            // Get the order_id from the data attribute
            var orderId = $(this).data('order-id');

            $.ajax({
                url: '/order-detail-items',
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
    });
</script>

@endsection