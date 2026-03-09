@extends('layouts.mobile-main')
@section('content')
@section('title', 'Brand orders')

<main>

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
    $returned = '';
    $pending = '';
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
        } elseif ($_GET['status'] == 'returned') {
            $returned = 'active';
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
    <!-- Dashboard Starts -->
    @include('Store.mobile.sidebar')
    <!-- Dashboard Ends -->
    <div class="dshbrdin buyer">
        <div class="container">
            <div class="row"> 
                <div class="col-md-12"> 
                    <div class="dshodrs">
                        <!-- Product Sorting Starts -->
                        <div class="row srtbx">
                            <div class="col-md-6"><h3 class=" ">Orders</h3></div>    
                            <div class="col-md-6 text-right">

                            </div> 
                        </div>    
                        <!-- Product Sorting Ends--> 

                        <!-- Tab starts -->
                        <div class="form-group">
                            <select class="form-control" name="oders_status" id="oders_status"> 
                                <option value="{{$url_current}}" >All Order</option>
                                <option value="{{$url_current}}?status=pending" <?php
                                if (isset($_GET['status']) && $_GET['status'] == 'pending') {
                                    echo 'selected';
                                }
                                ?>>Pending</option>
                               <!-- <option value="{{$url_current}}?status=filled" <?php
                                
                                if (isset($_GET['status']) && $_GET['status'] == 'filled') {
                                    echo 'selected';
                                }
                                ?>>Filled</option> -->
                                <option value="{{$url_current}}?status=shipped" <?php
                                if (isset($_GET['status']) && $_GET['status'] == 'shipped') {
                                    echo 'selected';
                                }
                                ?>>Shipped</option>
                               <!-- <option value="{{$url_current}}?status=undelivered" <?php
                                if (isset($_GET['status']) && $_GET['status'] == 'undelivered') {
                                    echo 'selected';
                                }
                                ?>>Undelivered</option>
                                <option value="{{$url_current}}?status=returned" <?php
                                if (isset($_GET['status']) && $_GET['status'] == 'returned') {
                                    echo 'selected';
                                }
                                ?>>Returned</option>
                               -->
                                <option value="{{$url_current}}?status=completed" <?php
                                if (isset($_GET['status']) && $_GET['status'] == 'completed') {
                                    echo 'selected';
                                }
                                ?> >Completed</option>
                                <option value="{{$url_current}}?status=cancelled" <?php
                                if (isset($_GET['status']) && $_GET['status'] == 'cancelled') {
                                    echo 'selected';
                                }
                                ?>>Cancelled</option> 
                            </select>
                        </div>
                        <!-- Tab Ends -->  
                        <div class="ordtbscnt"  > 
                            <div class="row"> 
                                <div class="col-2 "><span>ID</span></div>
                                <div class="col-4"><span class="text-center">Status</span></div>
                                <div class="col-3"><span class="text-center">Date</span></div>
                                <div class="col-3"><span class="text-center">Total</span></div>
                            </div> 
                        </div>

                        <div class="accordion" id="accordionExample">
                            <?php
                            if (is_array($orders_all) && count($orders_all) > 0) {
                                $count = 0;
                                foreach ($orders_all as $key => $order) {
                                    if(is_array($order) && count($order)>0){
                                    $order_id = $order[0]->order_id;
                                    $user_id = $order[0]->buyer_id;
                                    $payment_status = $order[0]->payment_status;
                                    $order_status = $order[0]->order_status;
                                    $date = $order[0]->created_at;
                                    $shipping_address = $order[0]->street;
                                    $total_amount = $order[0]->total_amount;
                                    $user_info = getUserById($user_id);
                                    // $easypost_shipment_id = isset($order[0]->shipment_id) ? $order[0]->shipment_id: "";

                                    //$product = getProductById($order[0]->order_status);
                                    $payment_method = $order[0]->payment_method;
                                    if ($user_info) {
                                        $name = $user_info->name;
                                    }
                                    $country_code = isset($order[0]->country) ? $order[0]->country : '';
                                    $country_code = str_replace('"', '', $country_code);
                                    $country_code = (int) $country_code;
                                    $trackingId = isset($order[0]->tracking_id) ? $order[0]->tracking_id : '';
                                    if ($country_code == 2) {
                                        $country_name = 'CANADA';
                                    } else {
                                        $country_name = 'USA';
                                    }

                                    //$country_name = '';
                                    // $country_name = DB::table('country_list')->where('country_code', '=', $order[0]->country)->select('country_name')->get();
                                    // print_rr($country_name);
                                    // die();
                                    // $country_name = $country_name[0]->country_name;
                                    $order_state = isset($order[0]->state) ? $order[0]->state : '';

                                    $state_name = DB::table('states')->where('state_id', '=', $order_state)->where('country_id', '=', $country_code)->select('state_name')->get();

                                    $state_name = isset($state_name[0]->state_name) ? $state_name[0]->state_name : '';
                                    $shipping_address = $order[0]->street . ' ' . $state_name . ' ' . $country_name;

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
                                    $show = '';
                                    if ($count == 0) {
                                        $show = 'show';
                                    }
                                    ?>


                                    <div class="card">
                                        <div class="card-header" id="">

                                            <button class="btn btn-link btn-block" type="button" data-toggle="collapse" data-target="#order-accordian-{{$order_id}}" aria-expanded="true" aria-controls="collapseOne">
                                                <div class="ordtbscnt"> 
                                                    <div class="row"> 
                                                        <div class="col-2 "><span><a class="text-center" href="#">{{$order_id}}</a></span></div>
                                                        <div class="col-4"><span class="text-center"> {{$order_status}} </span></div>
                                                        <div class="col-3"><span class="text-center">{{ date('M d, Y', strtotime($date)) }}</span></div>
                                                        <div class="col-3"><span class="text-center">${{$total_amount}}</span></div>
                                                    </div> 
                                                </div>
                                            </button>

                                        </div>

                                        <div id="order-accordian-{{$order_id}}" class="collapse  {{$show}}" aria-labelledby="order-accordian-{{$order_id}}" data-parent="#accordionExample">
                                            <div class="card-body">
                                                <?php
                                                // echo 'Data: <pre>' .print_r($order,true). '</pre>'; die;
                                                foreach ($order as $val) {
                                                    // echo 'Data: <pre>' .print_r($val,true). '</pre>'; 
                                                    $product = getProductById($val->item_id);
                                                    // $variable_product = getvariableProduct_id($val->variation_id);
                                                    // if (count($variable_product) > 0) {
                                                    //     $product_variation_image = isset($variable_product[0]->product_variation_image) ? $variable_product[0]->product_variation_image : '';
                                                    // }
                                                    // echo 'Data: <pre>' .print_r($product_variation_image,true). '</pre>'; die();
                                                   
                                                    $easypost_shipment_id = isset($val->shipment_id) ? $val->shipment_id: "";
                                                    $payment_method = isset($val->payment_method) ? $val->payment_method : '';
                                                    $_method = isset($val->method) ? $val->method: "";
                                                    $order_item_id = isset($val->order_item_id) ? $val->order_item_id: "";
                                                    $payment_service = isset($val->payment_service) ? $val->payment_service: "";
                                                    $product = getProductById($val->item_id);
                                                    $is_refund = isset($val->is_refund) ? $val->is_refund : '';
                                                    $product_title = isset($product[0]->title) ? $product[0]->title : '';
                                                    $product_quantity = $val->quantity;
                                                    $price = $val->price;
                                                    $image = isset($product[0]->feature_image) ? $product[0]->feature_image : '';
                                                    $image = (int) $image;
                                                    $order_note = $val->order_note;
                                                    $pslug = isset($product[0]->slug) ? $product[0]->slug : '';
                                                    $tracking_code = isset($val->tracking_code) ? $val->tracking_code: "";
                                                    $created_at = isset($product[0]->created_at) ? $product[0]->created_at:'';
                                                    $product_type = isset($product[0]->product_type) ? $product[0]->product_type : '';
                                                    $image_url = '';

                                                    if($product_type == 'simple' ){
                                                        $image_url = getAttachmentUrlById((int) $image, 'medium');
                                                        }elseif($product_type == 'virtual'){
                                                            $image_url = getAttachmentUrlById((int) $image, 'medium');
                                                        }
                                                        else{
                                                            $image_url = URL::to('/assets/images/placeholder.jpg');
                                                        }
                                                        // else{ 
                                                        //     $image_url = getAttachmentUrlById((int) $product_variation_image, 'medium');
                                                        // }

                                                    $variation_name = '';
                                                    if ($val->variation_id != '') {
                                                        $variation_name = getVariationNameById($val->variation_id);
                                                    }
                                                    ?>
                                                    <div class="row">
                                                        <div class="col-md-12">
                                                            <h3>Order Id: {{$key}}</h3>
                                                        </div>
                                                        <div class="col-12"><hr></div>
                                                        <div class="col-6">Date: {{ date('M d, Y', strtotime($created_at)) }}</div>
                                                        <div class="col-6">Status: {{$order_status}}</div>
                                                        <div class="col-12"><hr></div>  
                                                        <div class="col-md-12">
                                                            Shipping Address: <br><b>{{$shipping_address}}</b>
                                                        </div>
                                                        <div class="col-12"><hr></div>    
                                                        <div class="col-6">Price: <b>USDC {{$price}}</b></div>
                                                        <div class="col-6 " style="display:block">         
                                                            <span class="spacing-mobile-order">
                                                            <?php
                                                            // echo 'Data: <pre>' .print_r($tracking_code,true). '</pre>'; die();
                                                            ?>

                                                            </div>
                                                                    <input type="hidden" class="{{$order_id}}_easypost_shipmet_id" value="{{$easypost_shipment_id}}" data-payment_method="{{$_method}}" data-service="{{$payment_service}}" data-order_item_id="{{$order_item_id}}">
                                                                <a style="padding-right: 25px;" title="download" @if(isset($_GET['order_id']) && $_GET['order_id']==$order_id) class="download-link" @endif href="{{route('order-pdf',$order_id)}}">invoice <i class="fa fa-download"></i></a> 
                                                                {{$shipping_address}}</span><?php if ($order_status == 'shipped') { ?> 
                                                                    <?php
                                                                       $path = ('downloads/labels/');
                                                                   
                                                                    if(!empty($tracking_code) && $order_status != 'completed' ){
                                                                       
                                                                    ?>
                                                                    <div class="col-md-3">
                                                                        <div class="shipmet_data">

                                                                        <a href="<?php echo $path.$tracking_code.'.png' ?>" class="btn btn-primary btn-simple btnauto" target="_blank">Label <i class="fa fa-download" aria-hidden="true"></i></a>  
                                                                        <a href="<?php echo $path.$tracking_code.'.pdf' ?>" class="btn btn-primary btn-simple btnauto" target="_blank">PDF Label <i class="fa fa-download" aria-hidden="true"></i></a>  
                                                                        </div>
                                                                    </div>
                                                                    <?php } ?>
                                                              
                                                              
                                                              <?php } ?>
                                                                 </div> 


                                                        <div class="col-12"><hr></div>
                                                        <div class="col-12 mb-20" >
                                                            <div class="row">
                                                                <div class="col-3" >
                                                                    <img style="width:100%" src="<?php echo $image_url; ?>"  alt="placeholder icon" title="placeholder icon" style="width:53px;">
                                                                </div>
                                                                <div class="col-9 nopad">
                                                                    <p class="mb-0">{{$product_title}}</p>
                                                                    <span>QTY: <b>{{$product_quantity}}</b></span> &nbsp;|&nbsp; <span>Price: <b>USDC {{$price}}</b></span><br>
                                                                    <span><a href="<?php echo URL::to('/product/' . $pslug); ?>">View Details</a></span> 
                                                                </div></div> 
                                                        </div>
                                                        <?php
                                                        if ($order_status == 'pending' || $order_status == 'filled' || $order_status == 'shipped') {
                                                            ?>

                                                            <div class="col-6 col-md-2 mobile-fix-div">

                                                                <div class="text-center"><a href="javascript:;" class="btn btn-primary btn-simple btnauto talk-to-buyer dropdown-toggle" data-toggle="dropdown"> Change Status </a>
                                                                    <div class="dropdown-menu">

                                                                        <?php
                                                                        if ($order_status != 'pending' && $order_status != 'cancelled' && $order_status != 'refunded' && $order_status != 'shipped') {
                                                                            ?>
                                                                            <a data-status="pending" data-order_id="{{$order_id}}" class="dropdown-item change-order-status" href="javascript:;"><i class="fa fa-ship" aria-hidden="true"></i> Pending</a>
                                                                        <?php }
                                                                        ?>
                                                                        <?php
                                                                        if ($order_status != 'filled') {
                                                                            ?>
                                                                           <!-- <a data-status="filled" data-order_id="{{$order_id}}" class="dropdown-item change-order-status" href="javascript:;"><i class="fa fa fa-codepen" aria-hidden="true"></i> Filled</a> -->
                                                                        <?php }
                                                                        ?>
                                                                        <?php
                                                                        if ($order_status != 'shipped' && $order_status != 'cancelled' && $order_status != 'refunded') {
                                                                            ?>
                                                                            <a data-status="shipped" data-order_id="{{$order_id}}" data-easypost_shop_id="{{$easypost_shipment_id}}" class="dropdown-item change-order-status" href="javascript:;"><i class="fa fa-ship" aria-hidden="true"></i> Shipped</a>
                                                                        <?php }
                                                                        ?>
                                                                        <?php
                                                                        if ($order_status != 'processing') {
                                                                            ?>
                                                                            <!--              <a data-status="processing" data-order_id="{{$order_id}}" class="dropdown-item change-order-status" href="javascript:;"><i class="fa fa-refresh" aria-hidden="true"></i> Processing</a>-->
                                                                        <?php }
                                                                        ?>

                                                                        <?php
                                                                        if ($order_status != 'undelivered') {
                                                                            ?>
                                                                            <!-- <a data-status="undelivered" data-order_id="{{$order_id}}" class="dropdown-item change-order-status" href="javascript:;"><i class="fa fa-history" aria-hidden="true"></i> Undelivered</a> -->
                                                                        <?php }
                                                                        ?>
                                                                        <?php
                                                                        if ($order_status != 'refunded' && $order_status != 'pending') {
                                                                            ?>
                                                                           <a data-status="refunded" data-payment-method="{{$payment_method}}" data-order_id="{{$order_id}}" class="dropdown-item change-order-status" href="javascript:;"><i class="fa fa-retweet" aria-hidden="true"></i> Refunded</a>
                                                                        <?php }
                                                                        ?>

                                                                        <?php
                                                                        if ($order_status != 'cancelled' && $order_status != 'refunded' && $order_status != 'shipped') {
                                                                            ?>
                                                                            <a data-status="cancelled" data-payment-method="{{$payment_method}}" data-order_id="{{$order_id}}" class="dropdown-item change-order-status" href="javascript:;"><i class="fa fa-ban" aria-hidden="true"></i> Cancel</a>
                                                                        <?php }
                                                                        ?>


                                                                    </div>
                                                                </div>
                                                            </div> 
                                                        <?php }
                                                         ?>
                                                        <?php
                                                        if ($order_status == 'cancelled' ) {
                                                            // if ($payment_status != 'paid' && $payment_status != 'refunded') {
                                                            //     if ($order_status == 'cancelled') {
                                                            //         $order_status_c = 'cancelled';
                                                            //     }
                                                            //     if ($order_status == 'returned') {
                                                            //         $order_status_c = 'returned';
                                                            //     }
                                                            //     if ($order_status == 'undelivered') {
                                                            //         $order_status_c = 'undelivered';
                                                            //     }
                                                                ?>
                                                            <a class="btn btn-danger refund-request-btn"
                                                                data-status="cancelled" href=""
                                                                data-product-id="" data-order-id="{{$order_id}}"
                                                                data-toggle="modal" data-target="#cancel_popup{{$order_id}}_all"
                                                            style="margin-left: 30px;" id="cancel_order">Refund Requested</a>
                                                         
                                                                <?php
                                                           
                                                        }elseif($order_status == 'dispute' ){  ?>
                                                            <p class="btn btn-warning" data-status="dispute" 
                                                            style="margin-left: 30px;" >Order Dispute</p>
                                                        
                                                        <?php  }elseif($order_status == 'refunded' && $is_refund == 'vendor' ){  ?>
                                                                <p class="btn btn-secondary refund-request-btn" data-status="refunded" 
                                                                    style="margin-left: 30px;" >Order Refunded</p>
                                                                    <?php   
                                                            }elseif($order_status == 'refunded' ){ ?>
                                                        
                                                        <div class=" btn btn-secondary refund-request-btn" style="display:block"><span style="text-align:left">Order Refunded</h5></div>
                                                                    <!-- <p class="btn btn-danger" data-order-id="{{$order_id}}" data-status="refunded"
                                                                  data-toggle="modal" data-target="#refund_popup{{$order_id}}_all"
                                                                     >Refund Requested</p> -->
                                                         <?php   }
                                                         elseif($order_status == 'dispute'){ ?>
                                                            <p class="btn btn-warning " style="margin-left: 30px;" data-status="dispute" 
                                                                style="margin-left: 30px;" >Order Disputed</p>
                                                     <?php   } ?>
                                                                <?php
                                                                //$url = URL::to('/site-support/' . $user_info->user_id . '/buyer');
                                                                ?>
                                                        <!-- <div class="col-6 text-center"><a href="#" onclick="return popitup('<?php // echo $url; ?>')" class="btn btn-primary btn-simple btnauto talk-to-buyer">Talk to Seller</a></div>     -->
                                                    </div>
                                                </div>
                                            <?php } ?>
                                        </div>
                                    </div>
                                    <?php
                                    $count++;
                                }
                            }
                            }
                            ?>

                        </div>
                        <div class="tab-content">
                        </div>
                    </div>
                </div>
            </div> 
        </div>
    </div>
    <?php

if (is_array($orders_all_refund_cancel) && count($orders_all_refund_cancel) > 0) {
    // echo '$orders_all_refund_cancel: <pre>' .print_r($orders_all_refund_cancel,true). '</pre>'; die;
foreach ($orders_all_refund_cancel as $key => $order) {
    if (is_array($order) && count($order) > 0) {
        $payment_status = $order[0]->payment_status;
        $order_id = isset($order[0]->order_id) ? $order[0]->order_id: "";
        $wallet_address = isset($order[0]->wallet_address) ? $order[0]->wallet_address : "";
        $refund_product_id = isset($order[0]->id) ? $order[0]->id: "";
        $store_id = isset($order[0]->store_id) ? $order[0]->store_id: "";
        $reason_type = isset($order[0]->reason_type) ? $order[0]->reason_type: "";
        $reason = isset($order[0]->reason) ? $order[0]->reason: "";
        $trackingId = isset($order[0]->tracking_id) ? $order[0]->tracking_id : '';
        $user_id = isset($order[0]->buyer_id) ? $order[0]->buyer_id : '';
        $order_status = isset($order[0]->order_status) ? $order[0]->order_status : '';
        $is_refund = isset($order[0]->is_refund) ? $order[0]->is_refund : '';
        $date = isset($order[0]->created_at) ? $order[0]->created_at : '';
        $shipping_address = isset($order[0]->shipping_address) ? $order[0]->shipping_address : '';
        $is_refund = isset($order[0]->is_refund) ? $order[0]->is_refund : '';
        $total_amount = isset($order[0]->total_amount) ? $order[0]->total_amount : '';
        $user_info = getUserById($user_id);
        //$product = getProductById($order[0]->order_status);
        $payment_method = isset($order[0]->payment_method) ? $order[0]->payment_method : '';
        $name = '';
        if ($user_info) {
            $name = $user_info->name;
        }
        $country_code = $order[0]->country;
        if ($country_code == 2) {
            $country_name = 'CANADA';
        } else {
            $country_name = 'USA';
        }
        $state_name = '';
        $street_name = isset($order[0]->street) ? $order[0]->street : '';
        $suit = isset($order[0]->flat_other) ? $order[0]->flat_other : '';
        $city = isset($order[0]->city) ? $order[0]->city : '';
        $post_code = isset($order[0]->post_code) ? $order[0]->post_code : '';
        if (isset($order[0]->state) && isset($order[0]->state)) {
            $state_name = DB::table('states')->where('state_id', '=', $order[0]->state)->where('country_id', '=', $order[0]->country)->select('state_name')->get();

            $state_name = $state_name[0]->state_name;
        }
        $shipping_address = $street_name . ' ' . $suit . ' ' . $state_name . ' ' . $country_name . ' ' . $post_code;


         ?>
         <!--Start Cancel order Popup Model !-->
         <div class="modal fade cancel-popup-modal" id="cancel_popup{{$order_id}}_all" tabindex="-1" role="dialog"
            aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog cancel-order-popup" role="document">
                <div class="modal-content">
                    <div class="modal-header">

                        <div class="col-md-6">
                            <h5 id="cancel_order_modal">Cancel Order <span class="text-primary">#{{$order_id}}</span></h5>

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
                        <form action="{{URL::to('/reject_order')}}" method="post" id="reject_order">
                            @csrf
                            <div class="container">
                                <input type="hidden" name="order_id" id="order_id" value="{{$order_id}}">
                                <input type="hidden" name="buyer_id" id="buyer_id" value="{{$user_id}}">
                                <input type="hidden" name="store_id" id="store_id" value="{{$store_id}}">
                                <input type="hidden" name="order_status" id="order_status" value="dispute">

                                <?php
                                $sum = 0;
                                foreach ($order as $val) {
                                    // echo 'Data: <pre>' .print_r($val,true). '</pre>'; 
                                 //   echo '$val: <pre>' .print_r($val,true). '</pre>'; continue;
                                    $product = getProductById($val->product_id);
                                    $product_title = isset($product[0]->title) ? $product[0]->title : '';
                                    $product_id = isset($val->id) ? $val->id : '';
                                    $payment_method = isset($val->payment_method) ? $val->payment_method : '';
                                    $p_id = isset($val->product_id) ? $val->product_id : '';
                                    $product_quantity = isset($val->quantity) ? $val->quantity : "";
                                    $price = isset($val->price) ? $val->price : "";
                                    $shipping_amount = isset($val->shipping_amount) ? $val->shipping_amount : "";
                                    $sum+= $price;
                                    // echo '$val: <pre>' .print_r($val,true). '</pre>';
                                    // die;
                                    $pslug = isset($product[0]->slug) ? $product[0]->slug : '';

                                    $image = isset($product[0]->feature_image) ? $product[0]->feature_image : '';
                                    $image = (int) $image;
                                    $image_url = '';
                                    if ($image != '') {
                                        $image_url = getAttachmentUrlById($image, 'small');
                                    }
                                    $dateCreated = isset($product[0]->created_at) ? $product[0]->created_at : '';
                                    ?>
                                    <input type="hidden" id="cancel_order_accept"
                                            data-price="{{$price}}"
                                            wallet-address = "{{ $wallet_address }}"
                                            data-quantity="{{$product_quantity}}" data-order-id="{{$order_id}}" data-product-id="{{$p_id}}" data-payment_method="{{$payment_method}}"
                                            value="{{$sum}}"
                                            class="form-check-input cancel_order_accept_{{$order_id}}" />
                                 <input type="hidden" name="refund_product_id[]" id="refund_product_id" value="{{$product_id}}">
                                 <input type="hidden" name="refund_product_title[]" id="refund_product_title" value="{{$product_title}}">
                                <div class="row mb-2 p-2 form-group">
                                    <div class="col-md-5">
                                        <div class="media">
                                            <img class="mr-2" src="<?php echo $image_url; ?>"
                                                onerror="this.src='{{url('/assets/images/placeholder.jpg')}}'"
                                                alt="Generic placeholder image" width="85px">
                                            <div class="media-body">
                                                <h5 class="mt-0">{{$product_title}}</h5>
                                                <p></p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-1">
                                        <p class="text-primary">Qty {{$product_quantity}}</p>
                                    </div>
                                    <div class="col-md-2">
                                        <p class="text-primary">View Detail |</p>
                                    </div>
                                    <div class="col-md-4">
                                        <p class="order_shipping_amount"><strong>{{$price}} USDC </strong>(Inc. Shippment {{$shipping_amount}}) </p>
                                    </div>
                                </div>
                                <?php }?>
                            </div>

                            <div class="return-prolicy">
                                <div class="row">
                                    <div class="col-12">
                                        <h5>Reason Of Return</h5>
                                        <p>
                                        <?php 
                                        if(isset($reason_type)){
                                            echo $reason_type.'<br/>';
                                        }
                                        if(isset($reason) ){
                                            echo $reason;
                                        }else{
                                            echo "";
                                        }
                                        ?>
                                        </p>
                                    </div>
                                </div>
                            </div>

                            
                            <div class="cancle-order-reason-fields">
                                <div class="form-group">
                                    <h5>Accept Refund Request</h5>
                                    <div class="row d-flex container">
                                        <div class="form-group mb-2">
                                            <p>Refund Request Amount: USDC {{$sum}} </p>

                                        </div>
                                        <div class="form-group mx-sm-3 mb-2">
                                            <input type="text" pattern="/^-?\d+\.?\d*$/" onKeyPress="if(this.value.length==5) return false;" id="cancel_order_price"  class="form-control product-price"  value="" placeholder="{{$sum}}" />
                                        </div>
                                        <?php
                                        if($payment_method == 'stripe'){ ?>
                                        <button type="button" data-product_id="" data-product_quantity="" data-payment_method="{{$payment_method}}" data-store_id="{{$store_id}}" data-order_id="{{$order_id}}" class="btn btn-success mb-2 accept_amount_stripe"> Accept Refund Request </button>

                                      <?php  }else{ ?>
                                        <button type="button" data-product_id="" data-product_quantity="" data-payment_method="{{$payment_method}}" data-store_id="{{$store_id}}" data-order_id="{{$order_id}}" class="btn btn-success mb-2 accept_amount"> Accept Refund Request </button>

                                  <?php      }  ?>
                                    </div>
                              
                                        <div class="form-group">

                                            <h5>Reject Refund Request</h5>
                                            <input type="text" name="reject_order_reson" class="form-control" id="reject_order_reson"
                                                 placeholder="Some Other Reason? Type Here" />
                                        </div>

                                        <?php $now = date("Y-m-d");?>
                                      
                                        <input type="hidden" name="is_reject" id="is_reject" value="1">
                                        <input type="hidden" name="is_refund" id="is_refund" value="dispute">
                                        <input type="hidden" name="order_status" id="order_status" value="dispute">
                                        <input type="hidden" name="reject_date" id="reject_date" value="{{$now}}">
                                        <input type="hidden" name="order_id" id="order_id" value="{{$order_id}}">

                                        <div class="row pt-3">
                                            <div class="col-md-6">
                                                <p class="text-primary reject_order_{{$order_id}}" id="reject_order_{{$order_id}}"></p>
                                                <!-- <input type="text" id="total1" readonly> -->
                                            </div>
                                            <div class="col-md-6 text-right">
                                                <button type="submit" id="reject_submit" class="btn btn-danger">Reject Refund Request</button>
                                                <!-- <input type="submit" id="return_submit"  class="btn btn-primary" value="Request Return"> -->
                                            </div>

                                        </div>
                                   
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
         <!--End Cancel order Popup Model !--> 

            <!--Start Refund order Popup Model !-->
            <div class="modal fade refund-popup-modal" id="refund_popup{{$order_id}}_all" tabindex="-1" role="dialog"
            aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog cancel-order-popup" role="document">
                <div class="modal-content">
                    <div class="modal-header">

                        <div class="col-md-6">
                            <h5 id="cancel_order_modal">Refund Order <span class="text-primary">#{{$order_id}}</span></h5>

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
                        <form action="{{URL::to('/reject_order')}}" method="post" id="reject_order">
                            @csrf
                            <div class="container">
                                <input type="hidden" name="order_id" id="order_id" value="{{$order_id}}">
                                <input type="hidden" name="buyer_id" id="buyer_id" value="{{$user_id}}">
                                <input type="hidden" name="store_id" id="store_id" value="{{$store_id}}">
                                <input type="hidden" name="order_status" id="order_status" value="dispute">

                                <?php
                                $sum = 0;
                                foreach ($order as $val) {
                                    $product = getProductById($val->product_id);
                                    $product_title = isset($product[0]->title) ? $product[0]->title : '';
                                    $product_id = isset($val->id) ? $val->id : '';
                                    $product_quantity = isset($val->quantity) ? $val->quantity : "";
                                    $price = isset($val->price) ? $val->price : "";
                                    $sum+= $price;
                                    // echo '$val: <pre>' .print_r($val,true). '</pre>';
                                    // die;
                                    $pslug = isset($product[0]->slug) ? $product[0]->slug : '';

                                    $image = isset($product[0]->feature_image) ? $product[0]->feature_image : '';
                                    $image = (int) $image;
                                    $image_url = '';
                                    if ($image != '') {
                                        $image_url = getAttachmentUrlById($image, 'small');
                                    }
                                    $dateCreated = isset($product[0]->created_at) ? $product[0]->created_at : '';
                                    ?>
                                        <input type="hidden" id="refunded_order_accept"
                                            data-price="{{$price}}"
                                            wallet-address = "{{ $wallet_address }}"
                                            data-quantity="{{$product_quantity}}" data-order-id="{{$order_id}}" data-product-id="{{$p_id}}" data-payment_method="{{$payment_method}}"
                                            value="{{$sum}}"
                                            class="form-check-input refunded_order_accept_{{$order_id}}" />

                                        <input type="hidden" name="refund_product_id[]" id="refund_product_id" value="{{$product_id}}">
                                        <div class="row mb-2 p-2 form-group">
                                            <div class="col-md-8">
                                                <div class="media">
                                                    <img class="mr-2" src="<?php echo $image_url; ?>"
                                                        onerror="this.src='{{url('/assets/images/placeholder.jpg')}}'"
                                                        alt="Generic placeholder image" width="85px">
                                                    <div class="media-body">
                                                        <h5 class="mt-0">{{$product_title}}</h5>
                                                        <p></p>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-md-2">
                                                <p class="text-primary">View Detail |</p>
                                            </div>
                                            <div class="col-md-2">
                                                <p><strong>{{$price}} USDC</strong></p>
                                            </div>
                                        </div>
                                <?php }?>
                            </div>

                            <div class="return-prolicy">
                                <div class="row">
                                    <div class="col-12">
                                        <h5>Reason Of Return</h5>
                                        <p>
                                        <?php 
                                        if(isset($reason_type)){
                                            echo $reason_type.'<br/><br/>';
                                        }
                                        if(isset($reason) ){
                                            echo $reason;
                                        }else{
                                            echo "";
                                        }
                                        ?>
                                        </p>
                                    </div>
                                </div>
                            </div>

                            
                            <div class="cancle-order-reason-fields">
                                <div class="form-group">
                                    <h5>Accept Return Request</h5>
                                    <div class="row d-flex container">
                                        <div class="form-group mb-2">
                                            <p>Refund Request Amount: USDC {{$sum}} </p>

                                        </div>
                                        <div class="form-group mx-sm-3 mb-2">
                                            <input type="text" class="form-control product-price" value="" placeholder="${{$sum}}" />
                                        </div>
                                        <?php
                                        if($payment_method == 'stripe'){ ?>
                                        <button type="button" data-product_id="" data-product_quantity="" data-payment_method="{{$payment_method}}" data-store_id="{{$store_id}}" data-order_id="{{$order_id}}" class="btn btn-success mb-2 accept_amount_stripe_refund"> Accept Refund Request </button>

                                     
                                        <?php  }else{ ?>
                                        <button type="button" data-product_id="" data-product_quantity="" data-payment_method="{{$payment_method}}" data-store_id="{{$store_id}}" data-order_id="{{$order_id}}" class="btn btn-success mb-2 accept_amount_refund"> Accept Refund Request </button>

                                  <?php      }  ?>
                                    </div>
                              
                                        <div class="form-group">

                                            <h5>Reject Return Request</h5>
                                            <input type="text" name="reject_order_reson" class="form-control" id="reject_order_reson"
                                                 placeholder="Some Other Reason? Type Here" />
                                        </div>

                                        <?php $now = date("Y-m-d");?>
                                      
                                        <input type="hidden" name="is_reject" id="is_reject" value="1">
                                        <input type="hidden" name="is_refund" id="is_refund" value="dispute">
                                        <input type="hidden" name="order_status" id="order_status" value="dispute">
                                        <input type="hidden" name="reject_date" id="reject_date" value="{{$now}}">
                                        <input type="hidden" name="order_id" id="order_id" value="{{$order_id}}">

                                        <div class="row pt-3">
                                            <div class="col-md-6">
                                                <p class="text-primary reject_order_{{$order_id}}" id="reject_order_{{$order_id}}"></p>
                                                <!-- <input type="text" id="total1" readonly> -->
                                            </div>
                                            <div class="col-md-6 text-right">
                                                <button type="submit" id="reject_submit" class="btn btn-danger">Request Return</button>
                                                <!-- <input type="submit" id="return_submit"  class="btn btn-primary" value="Request Return"> -->
                                            </div>

                                        </div>
                                   
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
         <!--End Refund Order Popup MOdel !--> 
                <?php 
    }
     } 
}
?>
</main>
<script>
    var current_url;
    $(document).ready(function () {
        current_url = '{{Request::url()}}';
        $('[data-toggle="popover"]').popover();
        jQuery('.uploadImages').hide();
        //alert(current_url);
        // window.history.pushState(null, null, current_url);
    });
    $(document).ready(function () {
        var uri = window.location.toString();
        if (uri.indexOf("?") > 0) {
            var clean_uri = uri.substring(0, uri.indexOf("?"));
            window.history.replaceState({}, document.title, clean_uri);
        }
    });
    jQuery(document).on('click', '.change-order-status', function () {
        
        active_tab = jQuery('.tab-pane.active').attr('id');
        currrent_status=jQuery('.dshodrs .nav-link.active').attr('data-current');
       // alert(currrent_status);
        
        var order_id = jQuery(this).attr('data-order_id');
        let easypost_shop_id = jQuery(this).attr('data-easypost_shop_id');
        let total_ammount = jQuery(this).attr('total-amount');
        let wallet_addresss = jQuery(this).attr('wallet-address');
        let vendor = 'vendor';
        let payment_method = jQuery(this).attr('data-payment-method');
        status = jQuery(this).attr('data-status');
        console.log(payment_method);
        console.log(status);
        
        notification = 'This will send a notification to the buyer';
        if (status == 'processing') {
            active_tab = 'menu2';
            notification = 'This will send a notification to the buyer';
        }
        if (status == 'completed') {
            active_tab = 'menu4';
            notification = 'This will send a notification to the buyer';
        }
        if (status == 'refunded') {
           
            active_tab = 'menu5';
            notification = 'Canceling order will refund buyer';
        }
        if (status == 'shipped') {
            active_tab = 'menu3';
            notification = 'This will send a notification to the buyer ';
        }

        if (order_id != '' && status != '') {
            swal({
                title: "Are you sure?",
                text: notification,
                type: "warning",
                showCancelButton: true,
                confirmButtonClass: "btn-success",
                confirmButtonText: "Yes, Do it!",
                closeOnConfirm: true
            },
                    function () {
                        jQuery('.uploadImages').show();
                        // canel and refund here
                        console.log("payment_method", payment_method);
                        console.log("payment_method", status);
                        // debugger
                        if (status == 'cancelled' && payment_method == 'crypto') {
                            window.refundPaymentNode(total_ammount,wallet_addresss).then(res=>{
                                //added return to avoid ajax
                                        // console.log("refundPayment => ",res);
                                        // debugger;
                                        // change response res?.data?. to res?. for using app end refund
                                        if(res?.data?.status == true){
                                        
                                            new_url = current_url + '?status=' + status + '&order_id=' + order_id + '&active_tab=' + active_tab+'&payment_method='+payment_method+'&current_tab='+currrent_status+'&is_refund='+vendor;

                                            console.log(new_url);

                                            window.location.assign(new_url);
                                            swal("Success!", "Status update successfully.", "success");
                                            

                                        }else{
                                            swal("Failed!", "Failed to cancel and refund", "error");
                                            setTimeout(function () {
                                                window.location.reload();
                                            }, 2000);
                                        }
                            })
                        }else if(status == 'refunded' && payment_method == 'crypto'){
                            window.refundPaymentNode(total_ammount,wallet_addresss).then(res=>{
                                //added return to avoid ajax
                                        // console.log("refundPayment => ",res);
                                        // debugger;
                                        // change response res?.data?. to res?. for using app end refund
                                        if(res?.data?.status == true){
                                        
                                            new_url = current_url + '?status=' + status + '&order_id=' + order_id + '&active_tab=' + active_tab+'&current_tab='+currrent_status+'&payment_method='+payment_method+'&is_refund='+vendor;

                                            console.log(new_url);

                                            window.location.assign(new_url);
                                            swal("Success!", "Status update successfully.", "success");
                                            

                                        }else{
                                            swal("Failed!", "Failed to cancel and refund", "error");
                                            setTimeout(function () {
                                                window.location.reload();
                                            }, 2000);
                                        }
                            })

                        }else if(status == 'cancelled' &&  payment_method == 'stripe'){

                            new_url = current_url + '?status=' + status + '&order_id=' + order_id + '&active_tab=' + active_tab+'&current_tab='+currrent_status+'&payment_method='+payment_method+'&is_refund='+vendor;

                            console.log(new_url);

                            window.location.assign(new_url);
                            swal("Success!", "Status update successfully.", "success");

                        }else if(status == 'refunded' &&  payment_method == 'stripe'){ 
                            new_url = current_url + '?status=' + status + '&order_id=' + order_id + '&active_tab=' + active_tab+'&current_tab='+currrent_status+'&payment_method='+payment_method+'&is_refund='+vendor;

                            console.log(new_url);

                            window.location.assign(new_url);
                            swal("Success!", "Status update successfully.", "success");
                        }else if(status == 'shipped'){

                            var ship_id =[];
                            $('.'+order_id +'_easypost_shipmet_id').each(function( key, value ) {
                                // console.log("key", key);
                                // console.log("value", value);
                                // debugger
                            shipping_id =  $(this).val();
                            payment_service =  $(this).attr('data-service');
                            order_item_id =  $(this).attr('data-order_item_id');
                            payment_method =  $(this).attr('data-payment_method');
                            // console.log(order_item_id);
                            // debugger
                            var obj = {};
                            obj[payment_service] = {
                                'shipping_id': shipping_id,
                                'order_item_id': order_item_id,
                                'payment_method': payment_method,
                                
                            };
                            var ship_id_json = JSON.stringify(obj);
                            ship_id.push(ship_id_json);
                            // ship_id.push(payment_method);
                            // console.log("shipping_id", shipping_id);
                            // debugger;
                          //  console.log('valeu',$(this).attr('data-ship-price'));
                        });
                            new_url = current_url + '?status=' + status + '&order_id=' + order_id + '&active_tab=' + active_tab+'&current_tab='+currrent_status+'&ship_id='+ship_id;

                            console.log(new_url);

                            window.location.assign(new_url);
                            swal("Success!", "Status update successfully.", "success");
                        }else{
                            new_url = current_url + '?status=' + status + '&order_id=' + order_id + '&active_tab=' + active_tab+'&current_tab='+currrent_status;

                            console.log(new_url);

                            window.location.assign(new_url);
                            swal("Success!", "Status update successfully.", "success");
                        }

                       

                    });
            /*
             if (confirm('Are you sure you want to change the stauts')) {
             
             } else {
             // do nothing
             }
             */
        }

    });
    jQuery(document).on('click', '.refundorder', function () {
        active_tab = jQuery('.tab-pane.active').attr('id');

        order_id = jQuery(this).attr('data-order_id');
        status = jQuery(this).attr('data-order_status');
        notification = 'This will send a notification to the buyer';
        if (status == 'processing') {
            active_tab = 'menu2';

        }
        if (status == 'completed') {
            active_tab = 'menu4';

        }
        if (status == 'cancelled') {
            active_tab = 'menu5';

        }
        if (status == 'shipped') {
            active_tab = 'menu3';

        }
        notification = 'Your payment would be refunded back to buyer';
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
                    function () {
                        //jQuery('.uploadImages').show();




                        new_url = current_url + '?status=' + status + '&order_id=' + order_id + '&active_tab=' + active_tab + '&purpose=refund';
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
    setTimeout(function () {
        jQuery(".search_message").fadeOut('slow')
    }, 3000);
<?php if ($active != '') {
    ?>
        jQuery(document).ready(function () {
            jQuery('a').each(function (i) {
                if (jQuery(this).attr("href") == '<?php echo $active; ?>')
                {
                    jQuery(this).click();
                }
            });


        });
    <?php
}
?>
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
$(document).ready(function(){
        
        $(".accept_amount").click(function(){
               let OrderId = $(this).attr('data-order_id');
               let payment_method = $(this).attr('data-payment_method');
             
           
                
                $('.cancel_order_accept_'+OrderId).each(function(){
                   // alert('called');
                   $(".cancel-order-popup").hide();
                   let loader = jQuery('.uploadImages').show();
    
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
               
                    // console.log("product_id"+product_id);
                    // console.log("product_qunatity"+product_quantity);
                    // console.log("product_price"+product_price);
                    // console.log("product_order"+product_order_id);
                });
                
                // console.log(refund_complate_array);
                // return false;
                var formData = {
    
                    'amount' : product_price,
                    'order_id' : product_order_id,
                    'refund_process': refund_complate_array,
                    'payment_method': payment_method,
                };
                // let refundPayments = $('#cancel_order_price').val();
                // console.log("refundPayments => ",amountI)
                // return;
                let tm = amountI;
                
                if(amountI > 0){
                    
                  let refundResponse =   window.refundPaymentNode(amountI,wallet_address).then(res=>{
    
                    //added return to avoid ajax
                    console.log("refundPayment => ",res);
                    
                    // change response res?.data?. to res?. for using app end refund
                    if(res?.data?.status == true){
                       
                        
                        $.ajax({
                    url: 'return_refund',
                    type: 'POST',
                    data: formData,
                    dataType: 'json',
                    encode: true,
                    success: function(response) {
                        $(".cancel-order-popup").hide();
                        $(".modal-backdrop").hide();
                     
                        jQuery('.uploadImages').hide();
                        swal("Success!", 'Your Order Refunded is Complete', "success");
                        setTimeout(function () {
                                window.location.reload();
                            }, 2000);
                    },
                    error:function(response){
                        
                        $(".cancel-order-popup").hide();
                        $(".modal-backdrop").hide();
                        jQuery('.uploadImages').hide();
                       
                        //   swal('error!', 'Order Refund Failed!', 'error');
                        setTimeout(function () {
                            window.location.reload();
                        }, 2000);
                    }
                    
                });
                
            }else if(res?.code == 417){
                // this transction is failed due to low balance
                console.log('Your escrow balance is low for this transaction')
                swal('error!', 'Your escrow balance is low for this transaction!', 'error');
                setTimeout(function () {
                                window.location.reload();
                            }, 3000);
                    }else if(res?.code == "INVALID_ARGUMENT" && res?.value == "refundee"){
                        // this transction is failed due long refundee address which is to address of transactin
                        console.log('Refundee address is wrong')
                        swal('error!', 'Customer wallet address is wrong!', 'error');
                        setTimeout(function () {
                                window.location.reload();
                            }, 3000);
                      }
                      else{
                        $(".cancel-order-popup").hide();
                        $(".modal-backdrop").hide();
                        
                       
                        // setTimeout(function () {
                        //         window.location.reload();
                        //     }, 2000);
                        console.log('refundpaymentNode response failed')
                        jQuery('.uploadImages').hide();
                      }
                    });
                    // console.log("refundResponse=> " ,refundResponse)
                  
                }else{
                    
                }
               
               
               
    
            });
    
            // Refund case when order refund request through client
        $(".accept_amount_refund").click(function(){
               let OrderId = $(this).attr('data-order_id');
               let payment_method = $(this).attr('data-payment_method');
             
           
                
                $('.refunded_order_accept_'+OrderId).each(function(){
                   // alert('called');
                   $(".cancel-order-popup").hide();
                   let loader = jQuery('.uploadImages').show();
    
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
               
                    // console.log("product_id"+product_id);
                    // console.log("product_qunatity"+product_quantity);
                    // console.log("product_price"+product_price);
                    // console.log("product_order"+product_order_id);
                });
                
                // console.log(refund_complate_array);
                // return false;
                var formData = {
    
                    'amount' : product_price,
                    'order_id' : product_order_id,
                    'refund_process': refund_complate_array,
                    'payment_method': payment_method,
                };
                // let refundPayments = $('#cancel_order_price').val();
                // console.log("refundPayments => ",amountI)
                // return;
                let tm = amountI;
                
                if(amountI > 0){
                    
                  let refundResponse =   window.refundPaymentNode(amountI,wallet_address).then(res=>{
    
                    //added return to avoid ajax
                    console.log("refundPayment => ",res);
                    
                    // change response res?.data?. to res?. for using app end refund
                    if(res?.data?.status == true){
                       
                        
                        $.ajax({
                    url: 'return_refund',
                    type: 'POST',
                    data: formData,
                    dataType: 'json',
                    encode: true,
                    success: function(response) {
                        $(".cancel-order-popup").hide();
                        $(".modal-backdrop").hide();
                     
                        jQuery('.uploadImages').hide();
                        swal("Success!", 'Your Order Refunded is Complete', "success");
                        setTimeout(function () {
                                window.location.reload();
                            }, 2000);
                    },
                    error:function(response){
                        
                        $(".cancel-order-popup").hide();
                        $(".modal-backdrop").hide();
                        jQuery('.uploadImages').hide();
                       
                        //   swal('error!', 'Order Refund Failed!', 'error');
                        setTimeout(function () {
                            window.location.reload();
                        }, 2000);
                    }
                    
                });
                
            }else if(res?.code == 417){
                // this transction is failed due to low balance
                console.log('Your escrow balance is low for this transaction')
                swal('error!', 'Your escrow balance is low for this transaction!', 'error');
                setTimeout(function () {
                                window.location.reload();
                            }, 3000);
                    }else if(res?.code == "INVALID_ARGUMENT" && res?.value == "refundee"){
                        // this transction is failed due long refundee address which is to address of transactin
                        console.log('Refundee address is wrong')
                        swal('error!', 'Customer wallet address is wrong!', 'error');
                        setTimeout(function () {
                                window.location.reload();
                            }, 3000);
                      }
                      else{
                        $(".cancel-order-popup").hide();
                        $(".modal-backdrop").hide();
                        
                       
                        // setTimeout(function () {
                        //         window.location.reload();
                        //     }, 2000);
                        console.log('refundpaymentNode response failed')
                        jQuery('.uploadImages').hide();
                      }
                    });
                    // console.log("refundResponse=> " ,refundResponse)
                  
                }else{
                    
                }
               
               
               
    
            });
           
        });
        $(".accept_amount_stripe").click(function(){
               let OrderId = $(this).attr('data-order_id');
               let payment_method = $(this).attr('data-payment_method');
             
           
                
                $('.cancel_order_accept_'+OrderId).each(function(){
                   // alert('called');
                   $(".cancel-order-popup").hide();
                   let loader = jQuery('.uploadImages').show();
    
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
               
                    // console.log("product_id"+product_id);
                    // console.log("product_qunatity"+product_quantity);
                    // console.log("product_price"+product_price);
                    // console.log("product_order"+product_order_id);
                });
                
                // console.log(refund_complate_array);
                // return false;
                var formData = {
    
                    'amount' : product_price,
                    'order_id' : product_order_id,
                    'refund_process': refund_complate_array,
                    'payment_method': payment_method,
                };
                // let refundPayments = $('#cancel_order_price').val();
                // console.log("refundPayments => ",amountI)
                // return;
                // let tm = amountI;
                
                // if(amountI > 0){
                    
                //   let refundResponse =   window.refundPaymentNode(amountI,wallet_address).then(res=>{
    
                    //added return to avoid ajax
                    // console.log("refundPayment => ",res);
                    
                    // change response res?.data?. to res?. for using app end refund
                    // if(res?.data?.status == true){
                        $(".cancel-order-popup").hide();
                         $(".modal-backdrop").hide();
                            
                        $.ajax({
                        url: 'return_refund_stripe',
                        type: 'POST',
                        data: formData,
                        dataType: 'json',
                        encode: true,
                        success: function(response) {
                            $(".cancel-order-popup").hide();
                            $(".modal-backdrop").hide();
                        
                            jQuery('.uploadImages').hide();
                            swal("Success!", 'Your Order Refunded is Complete', "success");
                            setTimeout(function () {
                                    window.location.reload();
                                }, 2000);
                        },
                        error:function(response){
                            
                            $(".cancel-order-popup").hide();
                            $(".modal-backdrop").hide();
                            jQuery('.uploadImages').hide();
                        
                            //   swal('error!', 'Order Refund Failed!', 'error');
                            setTimeout(function () {
                                window.location.reload();
                            }, 2000);
                         }
                    
                     });
                
                    // }
                    // else if(res?.code == 417){
                    //     // this transction is failed due to low balance
                    //     console.log('Your escrow balance is low for this transaction')
                    //     swal('error!', 'Your escrow balance is low for this transaction!', 'error');
                    //     setTimeout(function () {
                    //                     window.location.reload();
                    //                 }, 3000);
                    //         }
                    // else if(res?.code == "INVALID_ARGUMENT" && res?.value == "refundee"){
                    //     // this transction is failed due long refundee address which is to address of transactin
                    //     console.log('Refundee address is wrong')
                    //     swal('error!', 'Customer wallet address is wrong!', 'error');
                    //     setTimeout(function () {
                    //             window.location.reload();
                    //         }, 3000);
                    //   }
                    //   else{
                    //     $(".cancel-order-popup").hide();
                    //     $(".modal-backdrop").hide();
                        
                       
                    //     // setTimeout(function () {
                    //     //         window.location.reload();
                    //     //     }, 2000);
                    //     console.log('refundpaymentNode response failed')
                    //     jQuery('.uploadImages').hide();
                    //   }
                    // });
                    // console.log("refundResponse=> " ,refundResponse)
                  
                // }
               
               
               
    
            });
    
        $(".accept_amount_stripe_refund").click(function(){
               let OrderId = $(this).attr('data-order_id');
               let payment_method = $(this).attr('data-payment_method');
             
                $('.refunded_order_accept_'+OrderId).each(function(){
                   // alert('called');
                   $(".refunded_order_accept").hide();
                   let loader = jQuery('.uploadImages').show();
    
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
                
                // console.log(refund_complate_array);
                // return false;
                var formData = {
    
                    'amount' : product_price,
                    'order_id' : product_order_id,
                    'refund_process': refund_complate_array,
                    'payment_method': payment_method,
                };
    
                        $(".cancel-order-popup").hide();
                         $(".modal-backdrop").hide();
                            
                        $.ajax({
                        url: 'return_refund_stripe',
                        type: 'POST',
                        data: formData,
                        dataType: 'json',
                        encode: true,
                        success: function(response) {
                            $(".cancel-order-popup").hide();
                            $(".modal-backdrop").hide();
                        
                            jQuery('.uploadImages').hide();
                            swal("Success!", 'Your Order Refunded is Complete', "success");
                            setTimeout(function () {
                                    window.location.reload();
                                }, 2000);
                        },
                        error:function(response){
                            
                            $(".cancel-order-popup").hide();
                            $(".modal-backdrop").hide();
                            jQuery('.uploadImages').hide();
                        
                            //   swal('error!', 'Order Refund Failed!', 'error');
                            setTimeout(function () {
                                window.location.reload();
                            }, 2000);
                         }
                    
                     });
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
    $(document).on('change', '#oders_status', function () {

        currentUrl = $(this).val();
        window.location.href = currentUrl;
    });
  jQuery(document).on('click', '.add-tracking', function () {

        order_id = jQuery(this).attr('data-orderid');

        if (order_id != '') {

            tracking_id = jQuery(this).prev('.tracking_id').val();
            //if (tracking_id != '') {
            jQuery('.uploadImages').show();
            data = 'order_id=' + order_id + '&tracking_id=' + tracking_id + '&action=saveTrackingId';
            jQuery.ajax({
                data: data,
                url: "{{route('update-tracking')}}",
                method: 'post',
                success: function (response) {
                    console.log(response);
                    jQuery('.uploadImages').hide();
                    swal("Success!", "Tracking Id is updated!", "success")
                }
            });
            //}

        }


    });
 jQuery(document).on('click', '.toggle-linkbtn', function () {
        jQuery(this).next('.trackinginfo').slideToggle();
    });

</script>
@endsection