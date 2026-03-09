@extends('layouts.dashboard-main')
<!-- Main Banner Starts -->
@section('content')
@section('title', 'Orders Management')



<link rel="stylesheet" href="{{ URL::asset('assets/css/order-page.css') }}">

<style>
    .table-responsive {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }

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

    .pagination {
        margin-top: 47px;
        margin-bottom: 10px;
    }

    .pagination .page-item {
        margin: 0 5px;
    }

    .pagination .page-item .page-link {
        color: #333;
        background-color: #fff;
        border: 1px solid #ddd;
        border-radius: 20px;
        padding: 6px 12px;
    }

    .pagination .page-item .page-link:hover {
        background-color: #f5f5f5;
    }

    .pagination .page-item.active .page-link {
        background-color: #007bff;
        color: #fff;
        border-color: #007bff;
    }

    .pagination .page-item.disabled .page-link {
        color: #6c757d;
        pointer-events: none;
    }

    /* .home-section {
        min-height: 500px;
    } */
    .order-product-page.home-section.cm-edit-ja{
        background-color:  #fff !important;
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


<div class="flex-parent-div bg-light-gray d-flex"> 
    @include('admin.adminsidebar') 
    <div class="order-product-page home-section cm-edit-ja">
        <div class="content container">
            <h3 class="buyer-order-heading" style="margin-left: 0px;">
                <div id="loading" class="show-setion" style="display:none;">
                    <img id="loading-image" src="{{ asset('assets/images/Loader-Animation.gif') }}" alt="Loading..." />
                </div>
                <div class="d-flex align-items-center gap-3">
                    <span>Orders</span>
                </div>

            </h3>
           
            @if ($orders_all && count($orders_all) > 0)

                <div class="w-100 text-end">
                    <form method="get" action="{{ URL::to('/admin/orders') }}" class="d-flex justify-content-end align-items-center flex-wrap gap-2 mb-3">

                        <!-- <label for="" class="mr-2">Show By: </label> -->
                        <label for="from-date" class="mb-0 mr-2">From:</label>
                        <input type="date" id="from-date" name="from_date" class="form-control mb-2 mr-2" value="{{ request('from_date') }}" style="width: 150px;">

                        <label for="to-date" class="mb-0 mr-2">To:</label>
                        <input type="date" id="to-date" name="to_date" class="form-control mb-2 mr-2" value="{{ request('to_date') }}" style="width: 150px;">

                        @php
                            $selectedState = request('order_state');
                            $states = isset($states) ? $states : [];
                        @endphp
                        <select name="order_state" id="order-state" class="form-control mb-2 mr-2" style="width: 160px;">
                            <option value="" selected>Select State</option>
                            @foreach ($states as $state)
                                <option value="{{ $state['state_id'] }}" {{ $selectedState == $state['state_id'] ? 'selected' : '' }}>
                                    {{ $state['state_name'] }}
                                </option>
                            @endforeach
                        </select>

                        <select id="order-status-filter" name="order_status" class="form-control mb-2 mr-2" style="width: 160px;">
                            <option value="All" {{ request('order_status') == 'All' ? 'selected' : '' }}>All</option>
                            <option value="Pending" {{ request('order_status') == 'Pending' ? 'selected' : '' }}>Pending</option>
                            <option value="Shipped" {{ request('order_status') == 'Shipped' ? 'selected' : '' }}>Shipped</option>
                            <option value="Refunded" {{ request('order_status') == 'Refunded' ? 'selected' : '' }}>Refunded</option>
                            <option value="Cancelled" {{ request('order_status') == 'Cancelled' ? 'selected' : '' }}>Cancelled</option>
                            <option value="Disputed" {{ request('order_status') == 'Disputed' ? 'selected' : '' }}>Disputed</option>
                            <option value="Completed" {{ request('order_status') == 'Completed' ? 'selected' : '' }}>Completed</option>
                        </select>

                        <button type="submit" class="btn btn-primary mb-2">Apply Filter</button>
                    </form>
                    <div class="d-flex justify-content-between align-items-center mb-3 border border-2 form-control p-2"  style="font-weight: bold;">
                        <div>
                            Showing {{ $orders_all->total() }} 
                            <span><b>"{{ ucfirst(request('order_status')) }}"</b> Orders</span>
                        </div>
                        <div>
                            <span>Total Amount: ${{ number_format($ordersTotalAmount, 2) }}</span>
                        </div>
                        <div>
                            <a href="#" onclick="exportOrders()" class="btn btn-secondary">
                                <i class="fa fa-download"></i> Export CSV
                            </a>
                        </div>
                    </div>
                </div>     
                
                <div class="card mycrd">
                    <div class="table-responsive datatable-custom">
                        <table id="datatable"
                            class="table table-hover table-borderless table-thead-bordered table-nowrap table-align-middle card-table"
                            style="width: 100%">
                            <thead class="thead-light">
                                <tr>
                                    <th class="table-column-pe-0">
                                        <div class="form-check">
                                            <label class="form-check-label" for="datatableCheckAll"></label>
                                        </div>
                                    </th>
                                    <th class="table-column-ps-0 text-center">Order ID</th>
                                    <th class="text-center">Date</th>
                                    <th class="text-center">invoice</th>
                                    <th class="text-center">Order status</th>
                                     <th class="text-center">Total</th>
                                </tr>
                            </thead>

                            <tbody>
                                <?php
                            foreach ($orders_all as $key => $value) {

                                $order_id = $value->order_id;
                                $store_id = $value->store_id;
                                $user_id = $value->buyer_id;
                                $payment_status = $value->payment_status;
                                $order_status = $value->order_status;
                                $date = $value->created_at;
                                $shipping_amount = $value->shipping_amount;
                                $total_amount = $value->total_amount;

                                $class = '';
                                $text = '';
                                if ($order_status == 'pending') {
                                    $class = 'bg-primary';
                                    $text = 'primary';
                                }
                                if ($order_status == 'completed') {
                                    $icon = '<i class="fa fa-check-square-o" aria-hidden="true"></i>';
                                }
                                if ($order_status == 'shipped') {
                                    $class = 'bg-info';
                                    $text = 'info';
                                }
                                if ($order_status == 'processing') {
                                    $icon = '<i class="fa fa-refresh" aria-hidden="true"></i>';
                                }
                                if ($order_status == 'cancelled') {
                                    $class = 'bg-danger';
                                    $text = 'danger';
                                }
                                if ($order_status == 'completed') {
                                    $class = 'bg-success';
                                    $text = 'success';
                                }
                                if ($order_status == 'dispute') {
                                    $class = 'bg-dark';
                                    $text = 'dark';
                                }
                                if ($order_status == 'refunded') {
                                    $class = 'bg-secondary';
                                    $text = 'secondary';
                                }
                                if ($order_status == 'failed') {
                                    $fulfilment  = 'No Fulfilled';
                                } else {
                                    $fulfilment  = 'Fulfilled';
                                }
                            ?>
                                <tr>
                                    <td class="table-column-pe-0">
                                        <div class="form-check">

                                            <label class="form-check-label" for="ordersCheck1"></label>
                                        </div>
                                    </td>
                                    <td class="table-column-ps-0 text-center">
                                        <a href="{{ route('orderDetailsAdmin',$order_id)}}">#{{ $order_id }}</a>
                                    </td>
                                    <td class="text-center"><a class="text-body"
                                            href="{{ route('orderDetailsAdmin',$order_id)}}">{{ date('M d, Y', strtotime($date)) }}</a>
                                    </td>
                                    <td class="text-center"> <a title="download"
                                            @if (isset($_GET['order_id']) && $_GET['order_id'] == $order_id) class="download-link" @endif
                                            href="{{ route('order-pdf', $order_id) }}">invoice <i
                                                class="fa fa-download"></i></a>
                                    </td>

                                    <td class="text-center">
                                        <a class="text-body" href="{{ route('orderDetailsAdmin',$order_id)}}">
                                            <span
                                                class="badge bg-gradient-{{ $text }} text-{{ $text }}">
                                                <span
                                                    class="legend-indicator {{ $class }}"></span>{{ ucfirst($order_status) }}
                                            </span>
                                        </a>
                                    </td>
                                    <td class="text-center"><a class="text-body" href="">${{ $total_amount }}</a></td>   
                                </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                    <!-- End Table -->
                </div>
                <div class="custom-pagination col-sm-12 mt-15">
                    {{$orders_all->links('pagination::bootstrap-4')}}    
                </div>
                {{-- @if($orders_all && count($orders_all) > 0)
                <div class="container">
                    <div class="row">
                        <div class="col-md-12" style="padding: 0px;">
                            <nav aria-label="Page navigation">
                                <ul class="pagination">
                                @foreach($orders_all['links'] as $link)
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
                @endif --}}

                {{-- <ul class="pagination">
                    {{ $orders_all->links() }}
                </ul> --}}

        </div>
    @else
        <div class="no-record-found-banner">
            <a href="https://mallshark.net/search?keyword=">
                <img src="{{ URL::asset('assets/images/No-Order.png') }}" alt="">
            </a>
        </div>
        @endif

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
            <div class="modal-body" id="orderModalBody">
                <!-- Order details will be loaded here using JavaScript -->
            </div>

        </div>
    </div>
</div>



<script>
    function exportOrders() {
        const fromDate = document.getElementById('from-date')?.value || '';
        const toDate = document.getElementById('to-date')?.value || '';
        const orderState = document.getElementById('order-by-state')?.value || '';
        const orderStatus = document.getElementById('order-status-filter')?.value || '';

        const query = new URLSearchParams({
            from_date: fromDate,
            to_date: toDate,
            order_state: orderState,
            order_status: orderStatus
        });

        window.location.href = `/api/admin/orders/export?${query.toString()}`;
    }
</script>


<script>
    $(document).ready(function() {
        $("body").addClass("open-sidebar");
        $(".refunded").on("click", () => {
            TrigerSweetAler()
        })
    })

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
    });
</script>
<script>
    var current_url;
    $(document).ready(function() {
        current_url = '{{ Request::url() }}';
        $('[data-toggle="popover"]').popover();
        jQuery('.uploadImages').hide();
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
    jQuery(document).on("click", ".nav-tabs .nav-item a", function() {
        jQuery(".uploadImages").show();
    });



    <?php if (isset($_GET['selector']) && $_GET["selector"] != '') { ?>
    jQuery('.uploadImages').show();
    jQuery(document).ready(function() {
        selected_order = '#collapse<?php echo $_GET['selector']; ?>_all';
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
    <?php } ?>
</script>

<script>
    var array_refund = [];
    var product_id = "product_id";
    var cancel_product_id = "product_id";

    $(document).ready(function() {

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
            $('.cancel_order_submit').prop('disabled', true);
// Define the function outside the conditional block
   

    // Check if disable_button is true, then call the function
   
            if (parseInt(selectProductCheckbox) === 0) {
                $('.select_price_cancel_order').parent().parent().css('border', 'red 1px solid');
                hasError = true;
                $('.cancel_order_submit').prop('disabled', false);
            } else {
                $('.select_price_cancel_order').parent().parent().css('border', 'initial');
                $('.cancel_order_submit').prop('disabled', true);
            }

            if (refund_reason_type === '') {
                $('.refund_reason_type').css('border', 'red 1px solid');
                hasError = true;
                $('.cancel_order_submit').prop('disabled', false);
            } else {
                $('.refund_reason_type').css('border', 'initial');
                $('.cancel_order_submit').prop('disabled', true);
            }

            if (someOtherReason === '') {
                $('.some_other_reason').css('border', 'red 1px solid');
                hasError = true;
                $('.cancel_order_submit').prop('disabled', false);
            } else {
                $('.some_other_reason').css('border', 'initial');
                $('.cancel_order_submit').prop('disabled', true);
            }

            if (hasError) {
                return false;
                $('.cancel_order_submit').prop('disabled', false);
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

                url: 'cancel_order',
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

    $(function() {
        function getTotal(isInit) {
            var total = 0;
            var order_id = '';
            var selector = isInit ? ".select_price_cancel_order" : ".select_price_cancel_order:checked";
            $(selector).each(function() {
                order_id = $(this).attr("data-order-id");
                total += parseInt($(this).attr("data-price"));
            });
            if (total == 0) {
                $('.total1').val('');
            } else {
                $('.total1').html("Returning Total: " + total + " $");
            }

        }

        $(".select_price").click(function(event) {
            getTotal();
        });
        getTotal(true);
    });
</script>

<script>
    $("input[type='checkbox']").change(function() {
        if ($(this).is(":checked")) {
            // var selecter = $(".select_price > #refund-section").find();

            $(".cancel_popup_quantity").show();
            $("#refund_popup_quantity").show();
            $(this).parent().parent().addClass("redBackground");

            $(this).parent().parent().addClass("redBackground");
        } else {
            $(".cancel_popup_quantity").hide();
            $("#refund_popup_quantity").hide();
            console.log('checked');


            $(this).parent().parent().removeClass("redBackground");
            $(this).parent().parent().removeClass("redBackground");
            $(this).parent().parent().removeClass("redBackground");
        }
    });

    $(document).on('change', '#refund_product_qty', function() {
        $(".large-loader").show();
        var data = {};
        data['product_id'] = $(this).attr('data-prodcut-id');
        data['quantity'] = $(this).val();

        price = $('#product-price').attr('data-price');
        quantity = $(this).val();
        if (quantity == 1) {
            alert('called');
            total = price / quantity;
            console.log("ded " + total);
            console.log("quantity " + quantity);
            console.log("price " + price);

        } else {
            total = parseFloat(price) * quantity;
        }

        $('#product-price').html("$ " + total.toFixed(2));
        usdprice = parseFloat(total / 10).toFixed(2);
        $(".large-loader").hide();
    });


    $(document).on('change', '.cancel_popup_quantity', function() {
        $(".large-loader").show();
        var data = {};
        data['product_id'] = $(this).attr('data-prodcut-id');
        data['quantity'] = $(this).val();

        price = $('#product-price').attr('data-price');
        quantity = $(this).val();
        if (quantity == 1) {
            total = price / quantity;
        } else {
            total = parseFloat(price) * quantity;
        }
        $('#product-price').html("$ " + total.toFixed(2));
        usdprice = parseFloat(total / 10).toFixed(2);
        $(".large-loader").hide();
    });
</script>
@endsection
