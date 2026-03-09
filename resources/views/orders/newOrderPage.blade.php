@extends('layouts.dashboard-main')
<!-- Main Banner Starts -->
@section('content')
@section('title', 'Store orders')

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
        
        <div class="content container">
            @include('Store.sidebar')
            <h3 class="buyer-order-heading">
                <div id="loading" class="show-setion" style="display:none;">
                    <img id="loading-image" src="{{ asset('assets/images/Loader-Animation.gif') }}" alt="Loading..." />
                </div>
                <div class="d-flex align-items-center gap-3">

                    <a href="javascript:void(0)" class="d-flex align-items-center gap-10px sidebar-admin-burger-menu"
                        id="open-sidebar-admin-panel">
                        <span id="menu-toggle-dashboard" class="shopzit-menu mt-0 ml-0 bx-menu">

                            <span></span><span></span><span></span> <span></span>
                        </span>
                        <!-- <span>Menu</span> -->
                    </a>

                    <span>Orders</span>

                </div>
                <!-- <div class="uploadImages" style="display: block;"> -->
            </h3>
            @if (session('success'))
                @if(session('success')['redund_status'] == 1)
                <script>
                        var transaction_id = "{{ session('success')['transactionId'] }}";
                        var value = "{{ session('success')['total_amount'] }}";
                        var shipping = "{{ session('success')['shipping_amount'] }}";
                        var items = @json(session('success')['orderData']);

                        dataLayer.push({ ecommerce: null }); 
                            dataLayer.push({
                            event: "refund",
                            ecommerce: {
                                transaction_id: transaction_id,
                                value:  value,
                                tax: 0,
                                shipping: shipping,
                                currency: "USD",
                                items: items 
                            }
                        });
                    </script>
                @endif
                <div class="alert alert-success">
                    {{ session('success')['msg'] }}
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger">
                    {{ session('error')['msg'] }}
                </div>
            @endif

            @if ($allOrders['data'] && count($allOrders['data']) > 0)
                <form method="GET" action="{{ route('brand-orders') }}" style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 15px;">
                    <select id="order-status-filter" name="order_status" class="form-control" style="width: 13%; margin-left: auto; margin-right: 15px;">
                        <option value="All" {{ request('order_status') == 'All' ? 'selected' : '' }}>All</option>
                        <option value="Pending" {{ request('order_status') == 'Pending' ? 'selected' : '' }}>Pending</option>
                        <option value="Shipped" {{ request('order_status') == 'Shipped' ? 'selected' : '' }}>Shipped</option>
                        <option value="Refunded" {{ request('order_status') == 'Refunded' ? 'selected' : '' }}>Refunded</option>
                        <option value="Cancelled" {{ request('order_status') == 'Cancelled' ? 'selected' : '' }}>Cancelled</option>
                        <option value="Disputed" {{ request('order_status') == 'Disputed' ? 'selected' : '' }}>Disputed</option>
                        <option value="Completed" {{ request('order_status') == 'Completed' ? 'selected' : '' }}>Completed</option>
                    </select>
                    <button type="submit" class="btn btn-primary">Apply Filter</button>
                </form>
                <div class="card  mycrd" id="home" class="tab-pane in active">
                    <div class="table-responsive datatable-custom">

                        <table id="datatable"
                            class=" tblish table table-hover table-borderless table-thead-bordered table-striped table-hover table-nowrap table-align-middle card-table"
                            style="width: 100%">

                            <thead class="thead-light">
                                <tr class="tblishrw">
                                    <th class="table-column-pe-0">
                                        <div class="form-check">

                                        </div>
                                    </th>
                                    <th class="table-column-ps-0 text-center">Order</th>
                                    <th class="text-center">Date</th>
                                    <th class="text-center">Shipping Label</th>
                                    <th class="text-center">Order status</th>
                                    <!-- <th>Fulfillment status</th> -->
                                    <th class="text-center">Total</th>
                                    <th class="text-center">Actions</th>
                                </tr>
                            </thead>

                            <tbody>
                                <?php
              foreach ($allOrders['data'] as $order) {
                //   echo 'Data: <pre>' .print_r($order,true). '</pre>'; die();

                $class = '';
                $text = '';
                $fulfilment = '';
                if ($order['order_status'] == 'pending') {
                  $class = 'bg-primary';
                  $text = 'primary';
                } elseif ($order['order_status'] == 'shipped') {
                  $class = 'bg-info';
                  $text = 'info';
                } elseif ($order['order_status'] == 'completed') {
                  $class = 'bg-success';
                  $text = 'success';
                } elseif ($order['order_status'] == 'cancelled') {
                  $class = 'bg-danger';
                  $text = 'danger';
                } elseif ($order['order_status'] == 'dispute') {
                  $class = 'bg-dark';
                  $text = 'dark';
                } elseif ($order['order_status'] == 'refunded') {
                  $class = 'bg-secondary';
                  $text = 'secondary';
                }
                if ($order['order_status'] == 'failed') {
                  $fulfilment  = 'No Fulfilled';
                } else {

                  $fulfilment  = 'Fulfilled';
                }
              ?>


                                <tr class="tbe" data-order_id="{{ $order['order_id'] }}">
                                    <td class="table-column-pe-0">
                                        <div class="form-check">
                                            <label class="form-check-label" for="ordersCheck1"></label>
                                        </div>
                                    </td>
                                    <td class="table-column-ps-0 text-center">
                                        <a
                                            href="{{ 'brand-order-details/' . $order['order_id'] }}">#{{ $order['order_id'] }}</a>
                                    </td>
                                    <td class="text-center"><a class="text-body" href="{{ 'brand-order-details/' . $order['order_id'] }}">
                                            {{ date('M d, Y', strtotime($order['created_at'])) }}</a></td>
                                    {{-- <td class="text-center">
                                        <a class="text-body"
                                            href="{{ 'brand-order-details/' . $order['order_id'] }}">{{ isset($order['user']->name) ? $order['user']->name : '' }}</a>
                                    </td> --}}
                                    <td class="text-center"> <a title="download"
                                        @if (isset($_GET['order_id']) && $_GET['order_id'] == $order['order_id']) class="download-link" @endif
                                        href="{{ route('order-pdf', $order['order_id']) }}">invoice <i
                                            class="fa fa-download"></i></a>
                                </td>
                                    <td class="text-center">
                                        <a class="text-body" href="javascript:void(0)">
                                            <span
                                                class="badge bg-gradient-{{ $text }} text-{{ $text }}">
                                                <span
                                                    class="legend-indicator {{ $class }}"></span>{{ ucfirst($order['order_status']) }}
                                            </span>
                                        </a>
                                    </td>
                                    <!-- <td>
                                        <a class="text-body" href="javascript:void(0)">
                                            <span class="badge bg-soft-info text-info">
                                                <span class="legend-indicator bg-info"></span>{{ $fulfilment }}
                                            </span>
                                        </a>
                                    </td> -->
                                   
                                    <td class="text-center"><a class="text-body" href="javascript:void(0)">${{ $order['total_amount'] }}</a>
                                    </td>
                                    <?php
                //   echo 'Data: <pre>' .print_r($order['order_items_list'],true). '</pre>'; die();
                                  foreach ($order['order_items_list'] as $item) { 

                    ?>
                                    <input type="hidden" class="{{ $item['order_id'] }}_easypost_shipmet_id"
                                        value="{{ $item['shipment_id'] }}" data-payment_method="{{ $item['method'] }}"
                                        data-service="{{ $item['payment_service'] }}"
                                        data-order_item_id="{{ $item['order_item_id'] }}">
                                    <?php } ?>
                                    <td class="text-center">
                                        <?php if ($order['order_status'] == 'refunded') { ?>
                                        <div class="text-warning">Refunded</div>
                                        <?php } else if ($order['order_status'] == 'dispute') { ?>
                                        <div class="text-dark">Disputed</div>
                                        <?php } elseif ($order['order_status'] == 'cancelled') { ?>
                                        <button type="button" class="btn btn-sm btn-danger  order-modal-btn"
                                            data-order-id="<?php echo $order['order_id']; ?>">
                                            Accept Refund
                                        </button>


                                        <?php } elseif($order['order_status'] == 'completed') { ?>

                                            <div class="text-success">Order Completed</div>
                                        <?php } else { ?>
                                        <div class="btn-group">
                                             <button type="button" class="btn btn-secondary btn-sm dropdown-toggle"
                                                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                Action
                                            </button>

                                            <div class="dropdown-menu">
                                                <?php
                          if ($order['order_status'] != 'shipped' && $order['order_status'] != 'refunded' && $order['order_status'] != 'cancelled') {   ?>
                                                <a data-status="shipped" data-order_id="{{ $order['order_id'] }}"
                                                    data-easypost_shop_id="{{ $item['shipment_id'] }}"
                                                    data-payment-method="{{ $order['payment_method'] }}"
                                                    class="dropdown-item change-order-status"
                                                    href="javascript:void(0)">Shipped</a>

                                                <?php
                          }
                          if ($order['order_status'] != 'cancelled' && $order['order_status'] != 'refunded' && $order['order_status'] != 'shipped') {
                          ?>
                                                <a data-shipped-cancel="0" data-status="cancelled"
                                                    data-order_id="{{ $order['order_id'] }}"
                                                    total-amount="{{ $order['total_amount'] }}"
                                                    data-payment-method="{{ $order['payment_method'] }}"
                                                    wallet-address="{{ $order['buyer_address'] }}"
                                                    class="dropdown-item change-order-status"
                                                    href="javascript:void(0)">Cancel</a>

                                                <?php
                          }
                          if ($order['order_status'] != 'cancelled' && $order['order_status'] != 'refunded' && $order['order_status'] == 'shipped') {
                          ?>
                                                <a data-shipped-cancel="1" data-status="cancelled"
                                                    data-order_id="{{ $order['order_id'] }}"
                                                    total-amount="{{ $order['total_amount'] }}"
                                                    data-payment-method="{{ $order['payment_method'] }}"
                                                    wallet-address="{{ $order['buyer_address'] }}"
                                                    class="dropdown-item change-order-status"
                                                    href="javascript:void(0)">Cancel</a>
                                                <?php } ?>
                                                <!-- <div class="dropdown-divider"></div>
                          <a class="dropdown-item" href="#">Separated link</a> -->
                                            </div>
                                        </div>
                                        <?php } ?>

                                        <?php //}
                                        ?>
                                    </td>
                                </tr>
                                <?php //} 

              }
              ?>

                            </tbody>
                        </table>
                    </div>
                    <!-- End Table -->
                    <!-- <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#exampleModal">
        Launch demo modal
      </button> -->
                    <div class="modal fade" id="orderModal" tabindex="-1" role="dialog"
                        aria-labelledby="orderModalLabel" aria-hidden="true">
                        <div class="modal-dialog cancel-order-popup" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h3 class="modal-title" id="orderModalLabel"></h3>
                                    <button type="button" class="close modal-close" data-dismiss="modal"
                                        aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body" id="orderModalBody" style="padding:20px!important;">
                                    <!-- Order details will be loaded here using JavaScript -->
                                </div>

                            </div>
                        </div>
                    </div>

                </div>
                {{-- @dd($allOrders) --}}
               @if(isset($allOrders['data']) && count($allOrders['data']) > 0)
                <div class="container">
                    <div class="row">
                        <div class="col-md-12" style="padding: 0px;">
                            <nav aria-label="Page navigation">
                                <ul class="pagination">
                                @foreach($allOrders['links'] as $link)
                                <li class="page-item {{ $link['active'] ? 'active' : '' }}">
                                <a class="page-link" href="{{ $link['url'] }}">
                                    {!! $link['label'] !!}
                                </a>
                                </li>
                                @endforeach
                                </ul>
                            </nav>
                        </div>
                    </div>
                </div>
                @endif
            @else
                <div class="no-record-found-banner">
                    <a href="https://mallshark.net/search?keyword=">
                        <img src="{{ URL::asset('assets/images/No-Orders-Seller.png') }}" alt="">

                    </a>
                </div>
            @endif
        </div>
    </div>

    <div class="order-controller mt-5" style="display:none">
        <div class="page-title">
            <h1 class="fs-1">Order</h1>
        </div>
        <div class="dropdown order-status ml-auto">
            <button class="btn btn-secondary dropdown-toggle bg-white text-dark shadow" type="button"
                id="status_list" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <span>Show:</span><strong class="pl-2">Shipped</strong>
            </button>
            <div class="dropdown-menu w-100 py-0 border-bottom-0" aria-labelledby="status_list">
                @foreach ($status_list as $key => $item)
                    <li class="nav-item dropdown-item px-0 border-bottom">
                        <a href="#" class="nav-link text-dark py-1">{{ $item }}</a>
                    </li>
                @endforeach
            </div>
        </div>
        {{-- main content --}}
        <div class="order-content-container px-3 py-4 mt-5">
            <div class="content-header">
                <ul class="d-flex list-unstyled">
                    <li class="w-bold col-2">Order ID</li>
                    <li class="w-bold col-1">Status</li>
                    <li class="w-bold col-1 order-date">Date</li>
                    <li class="w-bold col-1">Total</li>
                    <li class="w-bold col-5">Shipping Address</li>
                    <li class="w-bold col-2">Action</li>
                </ul>
            </div>
            <div class="content-data pt-4" id="accordion">
                @foreach ($data as $key => $each)
                    <div class="mt-4">
                        <ul class="d-flex list-unstyled">
                            <li class="col-2">
                                <a data-toggle="collapse" data-target="#userId_{{ $key }}"
                                    aria-expanded="true" aria-controls="userId_{{ $key }}"
                                    class="accordion_btn">
                                    <i class="fa fa-angle-down border px-1 rounded" aria-hidden="true"></i>
                                    <span class="pl-2"> #1906 </span>
                                </a>
                                <span class="pl-3">By: Noman</span>
                            </li>
                            <li class="col-1">Dispute</li>
                            <li class="col-1 order-date-value">Jul 08, 2023</li>
                            <li class="col-1">$261.07</li>
                            <li class="col-5">Camden Street, Camden, London, NW1 0HY Alabama USA 29020</li>
                            <li class="col-2">
                                <div class="dropdown">
                                    <span class="dropdown-toggle action_btn border px-3 py-1" id="action_button"
                                        data-toggle="dropdown">
                                        Change Status
                                    </span>
                                    <div class="dropdown-menu border-0 p-0" aria-labelledby="action_button">
                                        <a class="dropdown-item refunded" href="javascript:;">
                                            <i class="fa fa-retweet" aria-hidden="true"></i>Refunded
                                        </a>
                                    </div>
                                </div>
                            </li>
                        </ul>
                        <div class="" style="top: 30px">
                            <div class="card border-0">
                                <div id="userId_{{ $key }}" class="collapse" aria-labelledby="headingOne"
                                    data-parent="#accordion">
                                    <div class="card-body pt-2">
                                        <ul class="d-flex list-unstyled align-items-center border-top pt-2">
                                            <li class="col-1" style="min-width:120px">
                                                <img src="https://dev.mallshark.net/uploads/medium/cathedral_peak_range_drakensberg_south_africa_1_2520.jpg"
                                                    alt="" class="w-100">
                                            </li>
                                            <li class="order-date-value"><a href="#">Christine 14ml Nail Polish
                                                    -
                                                    Shade 421</a></li>
                                            <li class="pl-5">Qty: 1</li>
                                            <li class="px-5">Price: $106</li>
                                            <li class="pl-5"><a href="#">View Details</a></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
        {{-- main content end --}}
    </div>
</div>

<script src="{{ URL::asset('js/app.js', env('HTTPS_STATUS')) }}"></script>
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
                            url: 'return_refund',
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
                url: 'return_refund_stripe',
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

    var current_url = '';

jQuery(".change-order-status").click(function() {

        active_tab = jQuery('.tab-pane.active').attr('id');

        var order_id = jQuery(this).attr('data-order_id');
        let easypost_shop_id = jQuery(this).attr('data-easypost_shop_id');
        let total_ammount = jQuery(this).attr('total-amount');
        let wallet_addresss = jQuery(this).attr('wallet-address');
        let vendor = 'vendor';
        let payment_method = jQuery(this).attr('data-payment-method');
        let data_shipped_cancel = jQuery(this).attr('data-shipped-cancel');

        status = jQuery(this).attr('data-status');
        notification = 'Should you choose to cancel this order, MallShark will reimburse the buyer for the corresponding transaction amount';
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
            notification = 'Marking this order as shipped means that your items have been sent to the shipping provider for delivery';
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
                function() {

                    jQuery('#loading').show();

                    // debugger
                    if (status == 'cancelled' && payment_method == 'crypto') {
                        window.refundPaymentNode(total_ammount, wallet_addresss).then(res => {
                            //added return to avoid ajax
                            // console.log("refundPayment => ",res);
                            // debugger;
                            // change response res?.data?. to res?. for using app end refund
                            if (res?.data?.status == true) {
                                new_url = current_url + '?status=' + status + '&order_id=' +
                                    order_id + '&active_tab=' + active_tab + '&payment_method=' +
                                    payment_method + '&current_tab=' + status + '&is_refund=' +
                                    vendor + '&shipped_cancel=' + data_shipped_cancel;

                                console.log(new_url);

                                window.location.assign(new_url);
                                swal("Success!", "Status update successfully.", "success");


                            } else {
                                swal("Failed!", "Failed to cancel and refund", "error");
                                setTimeout(function() {
                                    window.location.reload();
                                }, 2000);
                            }
                        })
                    } else if (status == 'refunded' && payment_method == 'crypto') {
                        window.refundPaymentNode(total_ammount, wallet_addresss).then(res => {
                            //added return to avoid ajax
                            // console.log("refundPayment => ",res);
                            // debugger;
                            // change response res?.data?. to res?. for using app end refund
                            if (res?.data?.status == true) {

                                new_url = current_url + '?status=' + status + '&order_id=' +
                                    order_id + '&active_tab=' + active_tab + '&current_tab=' +
                                    status + '&payment_method=' + payment_method + '&is_refund=' +
                                    vendor + '&shipped_cancel=' + data_shipped_cancel;

                                console.log(new_url);

                                window.location.assign(new_url);
                                swal("Success!", "Status update successfully.", "success");


                            } else {
                                swal("Failed!", "Failed to cancel and refund", "error");
                                setTimeout(function() {
                                    window.location.reload();
                                }, 2000);
                            }
                        })

                    } else if (status == 'cancelled' && payment_method == 'stripe') {

                        new_url = current_url + '?status=' + status + '&order_id=' + order_id +
                            '&active_tab=' + active_tab + '&current_tab=' + status + '&payment_method=' +
                            payment_method + '&is_refund=' + vendor + '&shipped_cancel=' +
                            data_shipped_cancel;

                        console.log(new_url);
                        // debugger;
                        window.location.assign(new_url);
                        swal("Success!", "Status update successfully.", "success");

                    } else if (status == 'refunded' && payment_method == 'stripe') {
                        new_url = current_url + '?status=' + status + '&order_id=' + order_id +
                            '&active_tab=' + active_tab + '&current_tab=' + status + '&payment_method=' +
                            payment_method + '&is_refund=' + vendor + '&shipped_cancel=' +
                            data_shipped_cancel;

                        console.log(new_url);
                        // debugger;
                        window.location.assign(new_url);
                        swal("Success!", "Status update successfully.", "success");
                    } else if (status == 'shipped') {
                        var ship_id = [];
                        $('.' + order_id + '_easypost_shipmet_id').each(function(key, value) {
                            // console.log("key", key);
                            // console.log("value", value);
                            // debugger
                            shipping_id = $(this).val();
                        
                            payment_service = $(this).attr('data-service');
                            order_item_id = $(this).attr('data-order_item_id');
                            payment_method = $(this).attr('data-payment_method');
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
                        new_url = current_url + '?status=' + status + '&order_id=' + order_id +
                            '&active_tab=' + active_tab + '&current_tab=' + status + '&ship_id=' + ship_id;
                            console.log(new_url);
                        

                        window.location.assign(new_url);
                        swal("Success!", "Status update successfully.", "success");
                    } else {
                        new_url = current_url + '?status=' + status + '&order_id=' + order_id +
                            '&active_tab=' + active_tab + '&current_tab=' + status;
                        console.log(new_url);
                        window.location.assign(new_url);
                        swal("Success!", "Status update successfully.", "success");
                    }



                });

        }

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
<script>
        $(document).ready(function() {
            var orderButton = document.querySelector('.order-modal-btn');
            var orderId = orderButton.getAttribute('data-order-id');
            $('#orderModalLabel').text('Refund Product: ' + orderId);
    });

</script>

@endsection
