

@extends('layouts.new-main')
<!-- Main Banner Starts -->
@section('content')
@section('title', 'Store orders')
@include('Store.sidebar')
<?php
// $res=getmess();
//         print_rr($res);
//foreach($res as $e){
//   $r= getUserById($e);
//}

/*
  echo '<pre>';
  print_r($orders_processing);
  echo '</pre>';
 * 
 */
//die();
?>

<main>
    <div class="dshbrdin orders-container orders-container buyer-orders-mbt">
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
                        <div class="col-sm-12">              
                            <div class="d-flex justify-content-between">
                                <div class="hd-bt">  <h1></h1></div>  
                                <div class="add-pr-lnk">
                                    <a class="btn btn-primary btn-simple small-btn-mbt" href="{{URL::to('/store-dashboard')}}">
                                        Back to Dashboard
                                    </a>                                        
                                </div>                        
                            </div>            
                        </div>

                        <!-- Product Sorting Starts -->
                        <div class="row srtbx">
                            <div class="col-md-6"><h3 class=" ">Orders</h3></div>    
                            <div class="col-md-6 text-right hide-mobile">
                                <div class="  form-inline small pull-right label-inline">
                                    <form method="get" action="">
                                        <label for="text">Search Orders:</label>&nbsp;&nbsp;
                                        <input type="text" name="search" class="form-control" value="<?php echo isset($_GET['search']) ? $_GET['search'] : ''; ?>" aria-describedby="" placeholder="Order Id, email or name">  
                                 
                                    </form>
                                </div>   
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
                        $returned = '';
                        $pending = '';
                        if (isset($_GET['status'])&& !isset($_GET['current_tab'])) {
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
                        } 
                        
                         else if (isset($_GET['current_tab'])) {
                            if ($_GET['current_tab'] == 'unprocessed') {
                                $unprocessed = 'active';
                            }
                            if ($_GET['current_tab'] == 'pending') {
                                $pending = 'active';
                            } elseif ($_GET['current_tab'] == 'processing') {
                                $procesing = 'active';
                            } elseif ($_GET['current_tab'] == 'shipped') {
                                $shipped = 'active';
                            } elseif ($_GET['current_tab'] == 'unprocessed') {
                                $unprocessed = 'active';
                            } elseif ($_GET['current_tab'] == 'completed') {
                                $completed = 'active';
                            } elseif ($_GET['current_tab'] == 'cancelled') {
                                $cancelled = 'active';
                            } elseif ($_GET['current_tab'] == 'pending') {
                                $pending = 'active';
                            } elseif ($_GET['current_tab'] == 'filled') {
                                $filled = 'active';
                            } elseif ($_GET['current_tab'] == 'returned') {
                                $returned = 'active';
                            } elseif ($_GET['current_tab'] == 'undelivered') {
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
                        <ul class="nav nav-tabs mobile-tabs-order hide-mobile">
                            <li class="nav-item">
                                <a href="{{$url_current}}" data-current="" class="nav-link {{$all}}" href="#">All</a>
                            </li>
                            <li class="nav-item">
                                <a  href="{{$url_current}}?status=pending" data-current="pending" class="nav-link {{$pending}}" >Pending</a>
                            </li>
                            <!--
                            <li class="nav-item">
                                <a  href="{{$url_current}}?status=filled" class="nav-link {{$filled}}">Filled</a>
                            </li>
                            -->
                            <!--                        <li class="nav-item">
                                                        <a  href="{{$url_current}}?status=processing"class="nav-link {{$procesing}}" >Processing</a>
                                                    </li>-->
                            <li class="nav-item">
                                <a  href="{{$url_current}}?status=shipped" data-current="shipped" class="nav-link {{$shipped}}" >Shipped</a>
                            </li>
                            
                            <!-- <li class="nav-item">
                                <a  href="{{$url_current}}?status=undelivered" class="nav-link {{$undelivered}}" >Undelivered</a>
                            </li> -->
                            
                            <li class="nav-item">
                                <a  href="{{$url_current}}?status=returned" class="nav-link {{$returned}}" >Returned</a>
                            </li>
                            
                            <!-- <li class="nav-item">
                                <a href="{{$url_current}}?status=completed" class="nav-link {{$completed}}" >Completed</a>
                            </li> -->
                           
                            <li class="nav-item">
                                <a  class="nav-link {{$cancelled}}" data-current="cancelled" href="{{$url_current}}?status=cancelled" >Cancelled</a>
                            </li>


                        </ul>
                        <!-- Tab starts -->

                        <!-- Tab Ends -->  
                        <div class="mobile-dropdown-menu mb-25">
                            <div class="form-group">
                                <select class="form-control" name="user_country" id="user_country">

                                    <option value="">All Order</option>
                                    <option value="2&quot;">Filled</option>
                                    <option value="1">Shipped</option>



                                </select>
                            </div>                        
                        </div>

                        @if(is_array($orders_all) && count($orders_all)>0)
                        <div class="tab-content">

                            <div id="home" class="tab-pane  in active"> 
                                <!-- Tab Heads Starts -->
                                <div class="ordtbscnt hide-mobile">

                                    <div class="row"> 
                                        <div class="col-md-3 "><span>Order ID</span></div>
                                        <div class="col-md-1 "><span class="text-center">Status</span></div>
                                        <div class="col-md-1 "><span class="text-center">Date</span></div>
                                        <div class="col-md-1 "><span class="text-center">Total</span></div>
                                        <div class="col-md-4 "><span>Shipping Address</span></div>
                                        <div class="col-md-2 "><span class="text-center">Action</span></div> 
                                    </div>

                                </div>
                                <!-- Tab Heads Ends --> 

                                <script>

                                    $(document).ready(function () {

                                        if ($(window).width() <= 767) {
                                        }
                                        $(".card-link").click(function () {
                                            id = jQuery(this).attr('href');
                                            jQuery(this).toggleClass("icon-down");
                                            //                                            jQuery(id).show();
                                            //                                            $('html, body').animate({
                                            //                                                scrollTop: $(id).offset().top - 60
                                            //                                            }, 1500);
                                        });


                                    });


                                </script>  

                                <!-- Tab Content Starts -->
                                <?php
                                // print_rr($orders_all);
                                // echo '$orders_all: <pre>' .print_r($orders_all,true). '</pre>';
                                // die();
                                if (is_array($orders_all) && count($orders_all) > 0) {
                                    foreach ($orders_all as $key => $order) {
                                        if (is_array($order) && count($order) > 0) {
                                            $payment_status = $order[0]->payment_status;
                                            $order_id = $order[0]->order_id;
                                            $store_id = $order[0]->store_id;
                                            $trackingId = isset($order[0]->tracking_id) ? $order[0]->tracking_id : '';
                                            $user_id = isset($order[0]->buyer_id) ? $order[0]->buyer_id : '';
                                            $order_status = isset($order[0]->order_status) ? $order[0]->order_status : '';
                                            $date = isset($order[0]->created_at) ? $order[0]->created_at : '';
                                            $shipping_address = isset($order[0]->shipping_address) ? $order[0]->shipping_address : '';
                                            $is_refund = isset($order[0]->is_refund) ? $order[0]->is_refund : '';
                                            $total_amount = isset($order[0]->total_amount) ? $order[0]->total_amount : '';
                                            $user_info = getUserById($user_id);
                                            $item_id = $order[0]->item_id;
                                            $product = getProductById($order[0]->item_id);
                                            $pslug = isset($product->slug) ? $product->slug : '';
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
                                            //$country_name = '';
                                            // $country_name = DB::table('country_list')->where('country_code', '=', $order[0]->country)->select('country_name')->get();
                                            // print_rr($country_name);
                                            // die();
                                            // $country_name = $country_name[0]->country_name;
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
                                            $icon = '';
                                            if ($order_status == 'new') {
                                                $icon = '<i class="fa fa-clock-o" aria-hidden="true"></i>';
                                            }
                                            if ($order_status == 'completed') {
                                                $icon = '<i class="fa fa-check-square-o" aria-hidden="true"></i>';
                                            }
                                            if ($order_status == 'shipped') {
                                                $icon = '<i class="fa fa-ship" aria-hidden="true"></i>';
                                            }
                                            if ($order_status == 'processing') {
                                                $icon = '<i class="fa fa-refresh" aria-hidden="true"></i>';
                                            }
                                            if ($order_status == 'cancelled') {
                                                $icon = '<i class="fa fa-ban" aria-hidden="true"></i>';
                                            }
                                            ?>
                                            <div class="ordtbscnt" id="accordion">

                                                <div class="row"> 
                                                    <div class="col-md-3">
                                                        <div class="show-mobile767 max-wdth120">
                                                            <span class="mobile-table"><b>Order ID</b></span>
                                                        </div>                                                 

                                                        <span>
                                                            <a class="card-link" data-toggle="collapse" data-parent="#accordion" href="#collapse{{$order_id}}_all">
                                                                <span class="arw">
                                                                    <i style="font-size:18px" class="fa fa-angle-down" aria-hidden="true"></i></span>&nbsp;&nbsp; #{{$key}}</a> &nbsp;&nbsp; By: {{$name}}
                                                        </span>
                                                    </div>
                                                    <div class="col-md-1 text-center set-width-mobile" style="font-size:14px">
                                                        <div class="show-mobile767 max-wdth120">
                                                            <span class="mobile-table"><b>Status</b></span>
                                                        </div>                                                
                                                        <span class="text-center text-capitalize"><?php echo $order_status; ?>
                                                        </span>
                                                    </div>
                                                    <div class="col-md-1 set-width-mobile">
                                                        <div class="show-mobile767 max-wdth120">
                                                            <span class="mobile-table"><b>Date</b></span>
                                                        </div>                                                
                                                        <span class="text-center">{{ date('M d, Y', strtotime($date)) }}</span>
                                                    </div>
                                                    <div class="col-md-1 set-width-mobile">
                                                        <div class="show-mobile767 max-wdth120">
                                                            <span class="mobile-table"><b>Total</b></span>
                                                        </div>                                                
                                                        <span class="text-center">USDC {{$total_amount}}</span>
                                                    </div>
                                                    <div class="col-md-4 " style="display:block">                                                
                                                        <span class="spacing-mobile-order">
                                                            <div class="show-mobile767 shipping-adrs-mb">
                                                                <span class="mobile-table"><b>Shipping Address</b></span>
                                                            </div>                                                       
                                                            {{$shipping_address}}</span>
                                                        <?php if ($order_status == 'shipped' && $shipped != '') {
                                                            ?>
                                                            <a style="padding-right: 25px;" title="download" @if(isset($_GET['order_id']) && $_GET['order_id']==$order_id) class="download-link" @endif href="{{route('order-pdf',$order_id)}}">invoice <i class="fa fa-download"></i></a>
                                                            <a class="toggle-linkbtn" href="javascript:void(0);">Tracking Id</a><div class="trackinginfo" style="display:none"><input class="tracking_id form-control" type="text" value="{{$trackingId}}" placeholder="tracking id">
                                                                <button class="btn btn-primary btn-simple btnauto add-tracking" data-orderid="{{$order_id}}">Save</button></div> <?php } ?></div>
                                                    <?php if ($payment_status != 'paid' && $payment_status != 'refunded' ) {
                                                        if ($order_status != 'dispute') {
                                                        ?>        
                                                        <div class="col-6 col-md-2 mobile-fix-div"><span class="text-center"><a href="javascript:;" class="btn btn-primary btn-simple btnauto dropdown-toggle" data-toggle="dropdown"> Change Status</a>
                                                                <div class="dropdown-menu">

                                                                    <?php
                                                                    if ($order_status != 'pending') {
                                                                        ?>
                                                                        <a data-status="pending" data-order_id="{{$order_id}}" class="dropdown-item change-order-status" href="javascript:;"><i class="fa fa-clock-o" aria-hidden="true"></i> Pending</a>
                                                                    <?php }
                                                                    ?>
                                                                    <?php
                                                                    if ($order_status != 'filled') {
                                                                        ?>
                                                                        <!-- <a data-status="filled" data-order_id="{{$order_id}}" class="dropdown-item change-order-status" href="javascript:;"><i class="fa fa fa-codepen" aria-hidden="true"></i> Filled</a>  -->
                                                                    <?php }
                                                                    ?>
                                                                    <?php
                                                                    if ($order_status != 'shipped') {
                                                                        ?>
                                                                        <a data-status="shipped" data-order_id="{{$order_id}}" class="dropdown-item change-order-status" href="javascript:;"><i class="fa fa-plane" aria-hidden="true"></i> Shipped</a>
                                                                    <?php }
                                                                    ?>
                                                                    <?php
                                                                    if ($order_status != 'processing') {
                                                                        ?>
                        <!--                                                            <a data-status="processing" data-order_id="{{$order_id}}" class="dropdown-item change-order-status" href="javascript:;"><i class="fa fa-refresh" aria-hidden="true"></i> Processing</a>-->
                                                                    <?php }
                                                                    ?>

                                                                    <?php
                                                                    if ($order_status != 'undelivered') {
                                                                        ?>
                                                                                                               <!-- <a data-status="undelivered" data-order_id="{{$order_id}}" class="dropdown-item change-order-status" href="javascript:;"><i class="fa fa-history" aria-hidden="true"></i> Undelivered</a> -->
                                                                    <?php }
                                                                    ?>
                                                                    <?php
                                                                    if ($order_status != 'returned' && $order_status != 'dispute') {
                                                                        ?>
                                                                             <a data-status="returned" data-order_id="{{$order_id}}" class="dropdown-item change-order-status" href="javascript:;"><i class="fa fa-retweet" aria-hidden="true"></i> Returned</a>
                                                                    <?php }
                                                                    ?>

                                                                    <?php
                                                                    if ($order_status != 'cancelled') {
                                                                        ?>
                                                                        <a data-status="cancelled" data-order_id="{{$order_id}}" class="dropdown-item change-order-status" href="javascript:;"><i class="fa fa-ban" aria-hidden="true"></i> Cancel</a>
                                                                    <?php }
                                                                    ?>


                                                                </div>
                                                                <?php  
                                                                 if ( $order_status == 'cancelled'  && $is_refund== 'cancelled') {
                                                                ?>
                                                                 <a class="btn btn-primary btn-simple btn-sm btnauto getpid"
                                            data-status="refunded" href="<?php echo URL::to('/product/' . $pslug); ?>"
                                            data-product-id="<?php echo $item_id; ?>" data-order-id="{{$order_id}}"
                                            data-toggle="modal" data-target="#refund-order-popup{{$order_id}}_all"
                                            style="margin-left: 30px;" id="refund_order">Refund Request</a>
                                                                <!--Start Refund order Popup MOdel !-->
                                                                <div class="modal fade refund-order-popup-modal" id="refund-order-popup{{$order_id}}_all" tabindex="-1"
                                                                                                role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                                                                    <div class="modal-dialog cancel-order-popup" role="document">
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
                                                                                <form action="{{URL::to('/refund_order')}}" method="post" id="order_refund_cancel">
                                                                                    @csrf
                                                                                    <div class="container">
                                                                                        <input type="hidden" name="order_id" id="order_id" value="{{$order_id}}">
                                                                                        <input type="hidden" name="buyer_id" id="buyer_id" value="{{$user_id}}">
                                                                                        <input type="hidden" name="store_id" id="store_id" value="{{$store_id}}">
                                                                                        <input type="hidden" name="order_status" id="order_status" value="Refunded">

                                                                                        <?php
                                                                                        //echo '$order: <pre>' .print_r($order,true). '</pre>';  die;
                                                                                        foreach ($order as $val) {
                                                                                            $product = getProductById($val->item_id);

                                                                                            $product_title = isset($product[0]->title) ? $product[0]->title : '';
                                                                                            $product_quantity = $val->quantity;
                                                                                            $price = $val->price;
                                                                                           
                                                                                           // $pslug = isset($product[0]->slug) ? $product[0]->slug : '';
                                                                                   
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

                                                                                                <input type="checkbox" name="product_id" id="select_price"
                                                                                                    data-price="{{$price}}"
                                                                                                    data-quantity="{{$product_quantity}}"
                                                                                                    value="{{$val->item_id}}"
                                                                                                    class="form-check-input select_price" />

                                                                                            </div>
                                                                                            <div class="col-md-7">
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
                                                                                                <h5>Return Policy</h5>
                                                                                                <p>Lorem ipsum dolor sit amet consectetur adipisicing elit.
                                                                                                    Nisi quam nobis dolorem voluptates autem rerum velit
                                                                                                    eaque tempora amet sunt,
                                                                                                    sapiente, animi quis consequatur molestiae nam sed
                                                                                                    fugiat aspernatur labore!
                                                                                                    Nisi quam nobis dolorem voluptates autem rerum velit
                                                                                                    eaque tempora amet sunt,
                                                                                                    sapiente, animi quis consequatur molestiae nam sed
                                                                                                    fugiat aspernatur labore!
                                                                                                    Nisi quam nobis dolorem voluptates autem rerum velit
                                                                                                    eaque tempora amet sunt,
                                                                                                    sapiente, animi quis consequatur molestiae nam sed
                                                                                                    fugiat aspernatur labore!
                                                                                                    Nisi quam nobis dolorem voluptates autem rerum velit
                                                                                                    eaque tempora amet sunt,
                                                                                                    sapiente, animi quis consequatur molestiae nam sed
                                                                                                    fugiat aspernatur labore!</p>
                                                                                            </div>
                                                                                        </div>
                                                                                    </div>


                                                                                    <div class="cancle-order-reason-fields">
                                                                                        <div class="form-group">
                                                                                            <h5>Reason Of Return</h5>
                                                                                            <select class="form-control" id="refund_reason_type"
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
                                                                                            <textarea type="text" id="reason" class="form-control"
                                                                                                name="reason"
                                                                                                placeholder="Some Other Reason? Type Here"></textarea>
                                                                                        </div>
                                                                                        <?php $date_refund = date("Y-m-d");?>
                                                                                        <input type="hidden" name="is_refund" id="cancle" value="cancelled">
                                                                                        <input type="hidden" name="refund_date" id="refund_date"
                                                                                            value="{{$date_refund}}">

                                                                                        <div class="row pt-3">
                                                                                            <div class="col-md-6">
                                                                                                <p class="text-primary total1_{{$order_id}}" id="total1_{{$order_id}}" ></p>
                                                                                                <!-- <input type="text" id="total1" readonly> -->
                                                                                            </div>
                                                                                            <div class="col-md-6 text-right">
                                                                                                <button id="return_refund_submit"
                                                                                                    class="btn btn-primary">Submit</button>
                                                                                                <!-- <input type="submit" id="return_submit"  class="btn btn-primary" value="Request Return"> -->
                                                                                            </div>

                                                                                        </div>
                                                                                    </div>
                                                                                </form>
                                                                            </div>
                                                                        </div>
                                                                    </div>

                                                                </div>
                                                                <!--End Refund order Popup MOdel !-->


                                                                <?php } ?>



                                                                <?php
                                                                  $dispute_date_now =date("Y-m-d");
                                                                // if ($order_status == 'cancelled' || $order_status == 'returned' || $order_status == 'undelivered' && $is_refund== 'refund') {
                                                                    if ( $order_status == 'returned'  && $is_refund== 'refund') {
                                                                    if ($payment_status != 'paid' && $payment_status != 'refunded') {
                                                                        // if ($order_status == 'cancelled') {
                                                                        //     $order_status_c = 'cancelled';
                                                                        // }
                                                                        if ($order_status == 'returned') {
                                                                            $order_status_c = 'dispute';
                                                                        }
                                                                        // if ($order_status == 'undelivered') {
                                                                        //     $order_status_c = 'undelivered';
                                                                        // }
                                                                        ?>
                                                                        <!-- <div class="d-flex">
                                                                        <a style="display:block;margin:7px;" href="javascript:;" data-order_status="{{$order_status_c}}" data-order_id="{{$order_id}}" class="btn btn-primary btn-simple refundorder-bk approve-order"> Approve</a>
                                                                        <a style="display:block;margin:7px;" href="javascript:;" data-order_status="{{$order_status_c}}" data-dispute_date="{{ $dispute_date_now}}" data-is_refund="dispute" data-order_id="{{$order_id}}" class="btn btn-primary btn-simple dispute-order"> Reject</a>
                                                                        </div> -->
                                                                        <?php
                                                                    }
                                                                }
                                                                // echo '$order_status: <pre>' .print_r($order_status,true). '</pre>'; 
                                                                // echo '$is_refund: <pre>' .print_r($is_refund,true). '</pre>'; die;
                                                                if ( $order_status == 'cancelled'  && $is_refund== 'cancelled') {
                                                                    
                                                                    if ($payment_status != 'paid' && $payment_status != 'refunded') {
                                                                        if ($order_status == 'cancelled') {
                                                                            $order_status_c = 'dispute';
                                                                        }
                                                                
                                                                        ?>
                                                                        <!-- <div class="d-flex">
                                                                        <a style="display:block;margin:7px;" href="javascript:;" data-order_status="{{$order_status_c}}" data-order_id="{{$order_id}}" class="btn btn-primary btn-simple approve-order "> Approve</a>
                                                                        <a style="display:block;margin:7px;" href="javascript:;" data-dispute_date="{{ $dispute_date_now}}" data-is_refund="dispute" data-order_status="{{$order_status_c}}" data-order_id="{{$order_id}}" class="btn btn-primary btn-simple dispute-order"> Reject</a>
                                                                        </div> -->
                                                                        <?php
                                                                    }
                                                                }
                                                                ?>
                                                            </span></div> 
                                                        <?php
                                                    }
                                                }
                                                    if ($payment_status == 'paid') {
                                                        echo '<div class="col-md-2" style="display:block"><span style="text-align:center">Completed</h5></div>';
                                                    }
                                                    if ($payment_status == 'refunded') {
                                                        echo '<div class="col-md-2" style="display:block"><span style="text-align:center">Refunded</h5></div>';
                                                    }
                                                    ?>

                                                    <?php
                                                    //$url = URL::to('/site-support/' . $user_info->user_id . '/buyer');

                                                    ?>
                                                    <div class="col-6 mobile-view-order">
                                                        <a href="javascript:;"  onclick="return popitup('<?php echo ''; ?>')" class="btn btn-primary btn-simple btnauto talk-to-buyer">Talk to Buyer</a>
                                                    </div>
                                                </div>     
                                                <div id="collapse{{$order_id}}_all" class="collapse ordinf">
                                                    <?php
                                                    foreach ($order as $val) {
                                                        $product = getProductById($val->item_id);
                                                        if (count($product) > 0) {
                                                            $product_title = isset($product[0]->title) ? $product[0]->title : '';
                                                            $product_quantity = $val->quantity;
                                                            $price = $val->price;
                                                            $image = isset($product[0]->feature_image) ? $product[0]->feature_image : '';
                                                            $image = (int) $image;
                                                            $order_note = $val->order_note;
                                                            $image_url = '';
                                                            if ($image != '') {
                                                                $image_url = getAttachmentUrlById($image, 'small');
                                                            }
                                                            $variation_name = '';
                                                            if ($val->variation_id != '') {
                                                                 $variation_name = getVariationNameById($val->variation_id);
                                                                
                                                            }/*
                                                            $comment = $val->comment;
                                                            $rating = $val->rating;
                                                            if($rating==''){
                                                                $rating=0;
                                                            }
                                                             * 
                                                             */
                                                            $rating=0;
                                                            ?>
                                                    
                                                            <div class="card-body">
                                                                <div class="row">
                                                                    <div class="col-md-5"><sapn class="padleft"><img src="<?php echo $image_url; ?>" onerror="this.src='{{url('/assets/images/placeholder.jpg')}}'" alt="image" title="image"></sapn><span>{{$product_title}}</span> <span class="variation-txt">{{$variation_name}}</span></div>
<!--                                                                    <div class="col-md-1 text-center hide-border"> 
                                                                      //  {{ date('F d, Y', strtotime($product[0]->created_at)) }}
                                                                         <a style="font-size:20px; display:inline-block; width:100%;" href="javascript:;" title="" data-toggle="popover" data-placement="top" data-content="{{$order_note}}" data-original-title="">
                                                                            <i class="fa fa-sticky-note-o" aria-hidden="true"></i>
                                                                        </a> 
                                                                    </div>-->
                                                                    <div class="col-md-1"><span class="text-center">Qty: {{$product_quantity}}</span></div>
                                                                    <div class="col-md-4"><span>Price: USDC {{$price}}<br><span class="small grey"></span></span>  </div>
                                                                    <div class="col-md-2"><span class="text-center"><a href="<?php echo URL::to('/product/' . $product[0]->slug); ?>">View Details</a></span></div>   
                                                                </div>
                                                            </div>
                                                            <?php
                                                        }
                                                    }
                                                    ?>
                                                </div>
                                            </div>
                                            <?php
                                        }
                                    }
                                }
                                ?>
                                <!-- Tab Content Ends --> 
                            </div>

                        </div>
                        @else
                        <h5>No Records Found</h5>
                        @endif

                    </div>

                </div>





            </div>
        </div>
    </div>
</main>
<!-- Content Starts -->
<?php $curr_status= isset($_GET['status']) ? $_GET['status']:'';
      $curr_status= isset($_GET['current_tab']) ? $_GET['current_tab']:$curr_status;
?>
<script>
    var currrent_status= '';
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
    var current_url='';


    jQuery(document).on('click', '.change-order-status', function () {
        active_tab = jQuery('.tab-pane.active').attr('id');
        currrent_status=jQuery('.dshodrs .nav-link.active').attr('data-current');
       // alert(currrent_status);
        
        order_id = jQuery(this).attr('data-order_id');
        status = jQuery(this).attr('data-status');
        notification = 'This will send a notification to the buyer';
        if (status == 'processing') {
            active_tab = 'menu2';
            notification = 'This will send a notification to the buyer';
        }
        if (status == 'completed') {
            active_tab = 'menu4';
            notification = 'This will send a notification to the buyer';
        }
        if (status == 'cancelled') {
            active_tab = 'menu5';
            notification = 'This will send a notification to the buyer';
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
                closeOnConfirm: false
            },
                    function () {
                        //jQuery('.uploadImages').show();



                        new_url = current_url + '?status=' + status + '&order_id=' + order_id + '&active_tab=' + active_tab+'&current_tab='+currrent_status;

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
    $(document).ready(function () {
        current_url = '{{route("brand-orders")}}';
        $('[data-toggle="popover"]').popover();
        /*
         $('body').on('click', function (e) {
         $('[data-toggle="popover"]').each(function () {
         //the 'is' for buttons that trigger popups
         //the 'has' for icons within a button that triggers a popup
         if (!$(this).is(e.target) && $(this).has(e.target).length === 0 && $('.popover').has(e.target).length === 0) {
         $(this).popover('hide');
         }
         });
         });
         */
        jQuery('.uploadImages').hide();
        window.history.pushState(null, null, current_url);
    });
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
if (isset($_GET['click']) && $_GET['click'] == 'yes') {
    ?>

        jQuery(document).ready(function () {


            window.location.href = jQuery(".download-link:eq(0)").attr('href');

        });
    <?php
}
?>
    jQuery(document).on('click', '.toggle-linkbtn', function () {
        jQuery(this).next('.trackinginfo').slideToggle();
    });
    jQuery(document).on('click', '.show-loader', function () {
        jQuery('.uploadImages').show();
    });
    jQuery(document).on("click", ".nav-tabs .nav-item a", function () {
        jQuery(".uploadImages").show();
    });
</script>

<style>
    .trackinginfo {
        margin-bottom: 10px;
    }
    .trackinginfo input {
        display: inline-block;
        width: 74%;
        height: 35px;
    }
    .trackinginfo strong {
        display: block;
        font-weight: 500;
    }

</style>
<script>
        $("input[type='checkbox']").change(function(){
    if($(this).is(":checked")){
        $(this).parent().parent().addClass("redBackground"); 
    }else{
        $(this).parent().parent().removeClass("redBackground");  
    }
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




</script>
@endsection
