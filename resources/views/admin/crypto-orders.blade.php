@extends('layouts.dashboard-main')
<!-- Main Banner Starts -->
@section('content')
@section('title', 'Crypto Orders')

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
    .order-product-page.home-section {
        background-color: #fff !important;
        padding-top: 30px !important;
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
        transform: translate(-50%, -50%);
    }
</style>

<main>
    <section class="admin-dashboard dshbrdin buyer mt-0 user-management manage-categories-section d-flex">
    @include('admin.adminsidebar')
    <div class="flex-parent-div bg-light-gray w-100">
        <div class="order-product-page home-section">
            <div class="content container">
                <h3 class="buyer-order-heading" style="margin-left: 0px;">
                    <div id="loading" class="show-setion" style="display:none;">
                        <img id="loading-image" src="{{ asset('assets/images/Loader-Animation.gif') }}" alt="Loading..." />
                    </div>
                    <div class="d-flex align-items-center gap-3">
                        <span>Crypto Order Refund Requests</span>
                    </div>
                </h3>

                @if ($orders_all && count($orders_all) > 0)

                <div class="w-100 text-end">
                    <form method="get" action="{{ URL::to('/admin/crypto-refunds') }}" class="d-flex justify-content-end align-items-center flex-wrap gap-2 mb-3">

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
                        <button type="submit" class="btn btn-primary mb-2">Apply Filter</button>
                    </form>
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
                                    <th class="text-center">Order Date</th>
                                    <th class="text-center">Status</th>
                                    <th class="text-center">Refund Amount</th>
                                    <th class="text-center">Order Total</th>
                                    <th class="text-center">Wallet Address</th>
                                    <th class="text-center">Action</th>
                                </tr>
                            </thead>

                            <tbody>
                                <?php
                                foreach ($orders_all as $key => $value) {
                                    $order_id = $value->order_id;
                                    $user_id = $value->buyer_id;
                                    $order_status = $value->order_status;
                                    $date = $value->created_at;
                                    $total_amount = $value->total_amount;
                                    $total_refund = $value->total_refund;

                                ?>
                                    <tr>
                                        <td class="table-column-pe-0">
                                            <div class="form-check">
                                                <label class="form-check-label" for="ordersCheck1"></label>
                                            </div>
                                        </td>
                                        <td class="table-column-ps-0 text-center"> #{{ $order_id }} </td>
                                        <td class="text-center">{{ date('M d, Y', strtotime($date)) }}</td>
                                        <td class="text-center">
                                            <span class="badge bg-gradient-secondary text-secondary">
                                                <span class="legend-indicator bg-secondary"></span>
                                                Refund Requested
                                            </span>
                                        </td>
                                        <td class="text-center">${{ $total_refund }}</td>
                                        <td class="text-center">${{ $total_amount }}</td>
                                        <td class="text-center">
                                            {{ $value->customerWallet?->currency ?? '' }}

                                            @if (empty($value->customerWallet?->wallet_address))
                                                <span class="wallet-address text-primary"
                                                    style="cursor:pointer; text-decoration:underline;"
                                                    data-user_id="{{ $user_id }}"
                                                    data-order_id="{{ $order_id }}">
                                                    Send Email
                                                </span>
                                            @else
                                                <span class="text-success"></span>
                                            @endif

                                            <br>

                                            @if (!empty($value->customerWallet?->wallet_address))
                                                @php
                                                    $fullAddress = $value->customerWallet->wallet_address;
                                                    $shortAddress = substr($fullAddress, 0, 10) . '...';
                                                @endphp

                                                <small class="wallet-display" 
                                                    data-full-address="{{ $fullAddress }}"
                                                    style="cursor:pointer;">
                                                    {{ $shortAddress }}
                                                    <i class="fa fa-copy copy-icon text-secondary ml-1" title="Copy"></i>
                                                </small>
                                            @else
                                                <small>-</small>
                                            @endif

                                            <div class="response-msg-email text-danger mt-1" style="font-size: 13px; display:none;"></div>
                                        </td>
                                        <td class="text-center">
                                            <button class="btn btn-sm btn-outline-primary change-status-refunded"
                                                data-order_id="{{ $order_id }}"
                                                data-refund_items='@json($value->refundItems->pluck("id")->toArray())'>
                                                Mark as Refunded
                                            </button>
                                            <div class="response-msg-status mt-1" style="font-size: 13px; display:none;"></div>
                                        </td>
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

    </section>
</main>
<script>
    $(document).on('click', '.wallet-address', function() {
        let $btn = $(this);
        let userId = $btn.data('user_id');
        let orderId = $btn.data('order_id');
        let $responseMsg = $btn.closest('td').find('.response-msg-email');

        $btn.text('Sending...').css('pointer-events', 'none');
        $responseMsg.text(''); // clear previous messages

        $.ajax({
            url: '/send-wallet-address-email',
            type: 'POST',
            data: {
                user_id: userId,
                order_id: orderId,
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                // Use response.message in all cases
                if (response.status === true) {
                    $btn.text('Email Sent ✓')
                        .removeClass('text-primary')
                        .addClass('text-success');
                    $responseMsg
                        .removeClass('text-danger')
                        .addClass('text-success')
                        .text(response.message || 'Wallet setup email sent successfully.')
                        .fadeIn();
                } else {
                    $btn.text('Send Email')
                        .css('pointer-events', 'auto');
                    $responseMsg
                        .removeClass('text-success')
                        .addClass('text-danger')
                        .text(response.message || 'Something went wrong!')
                        .fadeIn();
                }

                // Hide message after 3 seconds
                setTimeout(() => {
                    $responseMsg.fadeOut();
                }, 3000);
            },
            error: function(xhr) {
                console.error(xhr.responseText);
                $btn.text('Send Email')
                    .css('pointer-events', 'auto');
                $responseMsg
                    .removeClass('text-success')
                    .addClass('text-danger')
                    // 👇 only used if backend didn't respond with JSON
                    .text('An unexpected error occurred: Unable to reach server.')
                    .fadeIn();

                setTimeout(() => {
                    $responseMsg.fadeOut();
                }, 3000);
            }
        });
    });

    $(document).on('click', '.change-status-refunded', function() {
        let $btn = $(this);
        let orderId = $btn.data('order_id');
        let refundItems = $btn.data('refund_items');
        let $responseMsg = $btn.closest('td').find('.response-msg-status');

        // Defensive: ensure refundItems is always an array
        if (!Array.isArray(refundItems) || refundItems.length === 0) {
            $responseMsg
                .removeClass('text-success')
                .addClass('text-danger')
                .text('No refund items found for this order.')
                .fadeIn();
            setTimeout(() => $responseMsg.fadeOut(), 3000);
            return;
        }

        $btn.text('Changing...').css('pointer-events', 'none');
        $responseMsg.hide().text('');

        $.ajax({
            url: '/items-mark-refunded',
            type: 'POST',
            data: {
                order_id: orderId,
                refund_items: refundItems,
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.status === true) {
                    $btn.text('Status Changed ✓')
                        .removeClass('btn-outline-primary')
                        .addClass('btn-success')
                        .css('pointer-events', 'none');
                    $responseMsg
                        .removeClass('text-danger')
                        .addClass('text-success')
                        .text(response.message || 'Refund status updated successfully.')
                        .fadeIn();
                } else {
                    $btn.text('Mark as Refunded')
                        .css('pointer-events', 'auto');
                    $responseMsg
                        .removeClass('text-success')
                        .addClass('text-danger')
                        .text(response.message || 'Something went wrong.')
                        .fadeIn();
                }
                setTimeout(() => $responseMsg.fadeOut(), 3000);
            },
            error: function(xhr) {
                console.error(xhr.responseText);
                $btn.text('Mark as Refunded').css('pointer-events', 'auto');
                $responseMsg
                    .removeClass('text-success')
                    .addClass('text-danger')
                    .text('Server error: Please try again later.')
                    .fadeIn();
                setTimeout(() => $responseMsg.fadeOut(), 3000);
            }
        });
    });

    $(document).on('click', '.copy-icon', function() {
        let $icon = $(this);
        let $walletDisplay = $icon.closest('.wallet-display');
        let fullAddress = $walletDisplay.data('full-address');
        let $responseMsg = $walletDisplay.closest('td').find('.response-msg-email');

        // Copy to clipboard
        navigator.clipboard.writeText(fullAddress)
            .then(() => {
                $responseMsg
                    .removeClass('text-danger')
                    .addClass('text-success')
                    .text('Copied!')
                    .fadeIn();

                // Temporary color feedback
                $icon.removeClass('text-secondary').addClass('text-success');
                setTimeout(() => {
                    $responseMsg.fadeOut();
                    $icon.removeClass('text-success').addClass('text-secondary');
                }, 2000);
            })
            .catch(err => {
                console.error('Copy failed:', err);
                $responseMsg
                    .removeClass('text-success')
                    .addClass('text-danger')
                    .text('Failed to copy.')
                    .fadeIn();
                setTimeout(() => $responseMsg.fadeOut(), 2000);
            });
    });
</script>
@endsection