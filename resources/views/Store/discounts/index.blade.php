@extends('layouts.dashboard-main')
@section('content')
    <style>
        .badge-active {
            margin-left: 10px;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .note {
            color: rgb(245, 28, 28);
            font-size: 0.9em;
        }

        #discount-sale-list button,
        #discount-coupon-list button {
            font-size: 14px
        }

        .discounts-title {
            font-size: 35px;
        }

        .large-loader {
            position: fixed;
            left: 0;
            right: 0;
            margin-left: auto;
            margin-right: auto;
            width: 100%;
            text-align: center;
            background: #ffffffb3;
            z-index: 999;
            height: 100%;
            top: 0;
            display: none
        }

        .large-loader span {
            position: absolute;
            width: 100px;
            height: 100px;
            top: 50%;
            margin-top: -50px;
            left: 0;
            right: 0;
            margin-left: auto;
            margin-right: auto;
        }
    </style>
    <!-- Bootstrap CSS -->

    <!-- jQuery (for the script) -->
    {{-- <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> --}}

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <?php
    use Carbon\Carbon;
    
    ?>
    <div class="flex-parent-div">
        <div class="change-password-page-cmt">
            @include('buyer.customsidebar')
        </div>
        @if (Session::has('SuccessMessage'))
            <?php $successMessage = Session::get('SuccessMessage'); ?>
            <script>
                toastr.options = {
                    closeButton: true,
                    tapToDismiss: true,
                    positionClass: 'toast-top-right',
                    toastClass: 'toast-icon custom-toast-success',
                    progressBar: true,
                };
                toastr.success('<?php echo $successMessage; ?> ');
            </script>
        @endif
        @if ($errors->any())
            <div class="alert alert-danger" role="alert">
                <strong>Oh snap!</strong> Change a few things up and try submitting again.
            </div>
        @endif
        <div
            class=" dshbrdin store-dashboard pro-dsb-cntainer home-section cm-edit-ja overflow-y-none mobile-adjust-scroll campaigns-page">
            <div class="container">
                <div class="row mt-15">
                    <div class="col-sm-12 col-12">
                        <h6 class="store-status-mbt">
                            <div class="d-flex align-items-center">
                                <a href="javascript:void(0)"
                                    class="d-flex align-items-center gap-10px sidebar-admin-burger-menu"
                                    id="open-sidebar-admin-panel">
                                    <span id="menu-toggle-dashboard" class="shopzit-menu mt-0 ml-0 bx-menu"
                                        style="margin-right: 5px;">
                                        <span></span><span></span><span></span> <span></span>
                                    </span>&nbsp;&nbsp;
                                </a>
                                <span class="discounts-title">Discounts</span>
                                <button class="btn btn-primary ml-auto mr-3" id="show-archive-list">View Archived</button>
                            </div>

                        </h6>
                    </div>
                </div>
                <div class="container-outrer mt-4">
                    <div class="container ">
                        <div class="row">
                            <div class="col-12">
                                <div class="header d-flex">
                                    <h4>Discount Sales</h4>
                                    <button onclick="redirectToDiscountDetails()" class="btn btn-primary mb-3">Create
                                        Discount Sale
                                    </button>
                                </div>
                                <div class="list-group " id="discount-sale-list">

                                </div>
                            </div>
                        </div>

                        <div class="row mt-4">
                            <div class="col-12">
                                <!-- Accordion Structure -->
                                <div class="accordion my-4" id="couponAccordion">
                                    <div class="card">
                                        <div class="card-header" id="headingOne">
                                            <h2 class="mb-0">
                                                <button class="btn btn-link collapsed" type="button" data-toggle="collapse" data-target="#collapseOne"
                                                    aria-expanded="true" aria-controls="collapseOne">
                                                    Archive Discounts
                                                </button>
                                            </h2>
                                        </div>
                                        <div id="collapseOne" class="collapse" aria-labelledby="headingOne" data-parent="#couponAccordion">
                                            <div class="card-body" style="border:1px solid #f7f7f7;">
                                                <div class="list-group" id="discount-coupon-list">
                                                    @forelse($discounts as $discount)
                                                        @if ($discount->archive == 1)
                                                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                                                <div class="font-weight-bold">{{ $discount->title ?? $discount->code}}</div>
                                                                <button class="btn btn-sm btn-outline-secondary btn-unarchive"
                                                                    data-id="{{ $discount->id }}" data-archive="{{ $discount->archive }}">
                                                                    Unarchive
                                                                </button>
                                                            </div>
                                                        @endif
                                                    @empty
                                                        <div class="list-group-item text-center text-muted">No discounts available.</div>
                                                    @endforelse
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
                    <script>
                        function redirectToDiscountDetails() {
                            window.location.href = "{{ route('discount-details') }}";
                        }

                       
                    </script>
                    <script>
                        $(document).ready(function() {
                            onLoadGetDiscounts();

                            function onLoadGetDiscounts() {
                                $('html, body').animate({
                                    scrollTop: 0
                                }, 500);
                                $.ajax({
                                    url: '/discounts-list',
                                    type: 'GET',
                                    success: function(response) {
                                        $('#discount-sale-list').empty();


                                        // Append the response content to the div
                                        $('#discount-sale-list').html(response);
                                        $('.active-discount').css('background-color', '#e9f7ef')
                                        if ($('#discount-sale-list .list-group-item').length === 0) {
                                            $('#discount-sale-list').append(
                                                '<div class="list-group-item">No discount offerings available</div>'
                                                );
                                        }
                                    },
                                })
                            }
                            $(document).ready(function() {
                                $('#show-archive-list').on('click', function() {
                                    // Close any open accordion
                                    $('.accordion .collapse').collapse('hide');

                                    // Scroll to the accordion
                                    $('html, body').animate({
                                        scrollTop: $('#couponAccordion').offset().top
                                    }, 500, function() {
                                        // Open the coupon accordion
                                        $('#collapseOne').collapse('show');
                                    });
                                });

                                $('.accordion-button').on('click', function() {
                                    if ($('#collapseOne').hasClass('show')) {
                                        // If it's open, hide the accordion and scroll to the top
                                        $('html, body').animate({
                                            scrollTop: 0
                                        }, 500);
                                        $('#collapseOne').collapse('hide');
                                       
                                    } else {
                                        // If it's not open, open the accordion
                                        $('#collapseOne').collapse('show');
                                    }
                                });
                            });
                            $(document).on('click', '.btn-activate, .btn-disable', function() {
                                var discountId = $(this).data('id');
                                var action = $(this).hasClass('btn-activate') ? 'active' : 'inActive';

                                $.ajax({
                                    url: '/update-status/' + discountId,
                                    type: 'POST',
                                    data: {
                                        status: action,
                                        _token: '{{ csrf_token() }}'
                                    },
                                    success: function(response) {
                                        if (response.requires_confirmation) {
                                            swal({
                                                title: "Are you sure?",
                                                text: response.message,
                                                type: "warning",
                                                showCancelButton: true,
                                                confirmButtonClass: "btn-primary",
                                                confirmButtonText: "Yes, Do it!",
                                                closeOnConfirm: false
                                            }, function() {
                                                $.ajax({
                                                    url: '/confirm-update-status/' + discountId,
                                                    type: 'POST',
                                                    data: {
                                                        _token: '{{ csrf_token() }}'
                                                    },
                                                    success: function(response) {

                                                        swal({
                                                            title: "Updated!",
                                                            text: response.message,
                                                            type: "success",
                                                            timer: 2000,
                                                            showConfirmButton: false
                                                        });
                                                        setTimeout(function() {
                                                            location.reload();
                                                        }, 2000);
                                                    },
                                                    error: function(response) {

                                                        swal("Error!", response.message,
                                                            "error");
                                                    }
                                                });
                                            });
                                        } else {
                                            swal({
                                                title: "Updated!",
                                                text: response.message,
                                                type: "success",
                                                timer: 2000,
                                                showConfirmButton: false
                                            });
                                            setTimeout(function() {
                                                location.reload();
                                            }, 2000);
                                        }
                                    },
                                    error: function(response) {
                                        if (response.responseJSON && response.responseJSON.message) {
                                            errorMessage = response.responseJSON.message;
                                        } else {
                                            errorMessage = response.message;
                                        }

                                        swal("Error!", errorMessage, "error");

                                    }
                                });
                            });


                            $('.btn-delete').click(function() {
                                if (confirm('Are you sure you want to delete this discount?')) {
                                    var discountId = $(this).data('id');
                                    $.ajax({
                                        url: '/discounts/' + discountId,
                                        method: 'DELETE',
                                        data: {
                                            _token: $('meta[name="csrf-token"]').attr('content')
                                        },
                                        success: function(response) {
                                            location.reload();
                                        }
                                    });
                                }
                            });
                            $(document).on('click', '.edit-button', function() {
                                var discountId = $(this).data('id');
                                window.location.href = '/discounts/' + discountId + '/edit';
                            });
                            $(document).on('click', '.btn-unarchive', function() {
                                var archive = $(this).data('archive');
                                var discountId = $(this).data('id');
                                if (archive == true) {
                                    $.ajax({
                                        url: '/unarchive-discount/' + discountId,
                                        method: 'POST',

                                        success: function(response) {
                                            if (response.status) {
                                                swal({
                                                    title: "Archived!",
                                                    text: response.message,
                                                    type: "success",
                                                    showConfirmButton: true
                                                }, function() {
                                                    location
                                                .reload(); // This will reload the page after the "Confirm" button is clicked

                                                });
                                            } else {
                                                swal({
                                                    title: "inform!",
                                                    text: response.message,
                                                    type: "danger",
                                                    showConfirmButton: true
                                                });
                                            }

                                        }
                                    });
                                }

                            });
                            $(document).on('click', '.archive-button', function() {
                                var status = $(this).data('status');
                                var discountId = $(this).data('id');
                                if (status != 'active') {
                                    $.ajax({
                                        url: '/discount/' + discountId,
                                        method: 'POST',
                                        data: {
                                            _token: $('meta[name="csrf-token"]').attr('content')
                                        },
                                        success: function(response) {
                                            if (response.status) {
                                                swal({
                                                    title: "Archived!",
                                                    text: response.message,
                                                    type: "success",
                                                    showConfirmButton: true
                                                }, function() {
                                                    location
                                                .reload(); // This will reload the page after the "Confirm" button is clicked
                                                });
                                            } else {
                                                swal({
                                                    title: "inform!",
                                                    text: response.message,
                                                    type: "danger",
                                                    showConfirmButton: true
                                                });
                                            }

                                        }
                                    });
                                } else {
                                    var message =
                                        'This sale is currently active if you want to archive this kindly InActive it'
                                    swal({
                                        title: "inform!",
                                        text: message,
                                        type: "warning",
                                        showConfirmButton: true
                                    });
                                }

                            });
                        });
                    </script>
                    <script>
                        $(document).ready(function() {
                            // Function to check if there are any discount items


                            // Function to check if there are any discount coupon items
                            function checkDiscountCouponItems() {
                                if ($('#discount-coupon-list .list-group-item').length === 0) {
                                    $('#discount-coupon-list').append(
                                        '<div class="list-group-item">No discount coupons available</div>');
                                }
                            }

                            // Handle delete button click for discount coupons
                            $(document).on('click', '#discount-coupon-list .btn-delete', function() {
                                var row = $(this).closest('.list-group-item');
                                if (confirm('Are you sure you want to delete this item?')) {
                                    row.remove();
                                    // Check if there are any discount coupon items
                                    checkDiscountCouponItems();
                                }
                            });

                            // Initial check for discount items

                            // Initial check for discount coupon items
                            checkDiscountCouponItems();
                        });
                    </script>
                </div>
            </div>
        </div>
    </div>
@endsection
