@extends('layouts.new-main')
<!-- Main Banner Starts -->
@section('content')
@section('title', 'Store Favorites')
<main>
    @include('buyer.sidebar')
    <!-- Dashboard Ends -->
    <?php

    if (!empty($favData)) {
    ?>
    <div class="dshbrdin favorites-section">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <div class="d-flex justify-content-between mb-25">
                        <div class="hd-bt">
                            <h1></h1>
                        </div>
                        <div class="add-pr-lnk">
                            <a class="btn btn-primary btn-simple small-btn-mbt" href="{{ URL::to('/user-dashboard') }}">
                                Back to Dashboard
                            </a>
                        </div>
                    </div>

                    <div class="dashprdlst">
                        <div class="row">
                            <div class="col-md-6">
                                <h3 class="mb-0">Favorite Stores</h3>
                            </div>
                            <div class="col-md-6 d-none">
                                <div class="  form-inline small pull-right ">
                                    <label for="text">Sort By:</label>&nbsp;&nbsp;
                                    <select class="form-control sort-field" id="fav-filter" name="order">
                                        <!-- <option>Sort by</option> -->
                                        <option <?php
                                        if (isset($_GET['order']) && $_GET['order'] == 'DESC') {
                                            echo 'selected';
                                        }
                                        ?> value="{{ Request::Url() }}?order=DESC">New to old
                                        </option>
                                        <option <?php
                                        if (isset($_GET['order']) && $_GET['order'] == 'ASC') {
                                            echo 'selected';
                                        }
                                        ?> value="{{ Request::Url() }}?order=ASC">Old to new
                                        </option>
                                        <option <?php
                                        if (isset($_GET['price']) && $_GET['price'] == 'DESC') {
                                            echo 'selected';
                                        }
                                        ?> value="{{ Request::Url() }}?price=DESC">Price: High
                                            to Low
                                        </option>
                                        <option <?php
                                        if (isset($_GET['price']) && $_GET['price'] == 'ASC') {
                                            echo 'selected';
                                        }
                                        ?> value="{{ Request::Url() }}?price=ASC">Price: Low to
                                            High
                                        </option>

                                    </select>
                                </div>
                            </div>
                        </div>
                        <!-- Product List -->
                        <?php
                            foreach ($favData as $fd) {
                                if ($fd['store_status'] != "1") {

                                    $class = "otofstock";
                                } else {
                                    $class = "";
                                }
                                $link = URL::to('/brand/' . $fd['store_slug']);
                            ?>
                        <div class="dshprdbx border">
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="fav-img-cnt">
                                        <a href="<?php echo $link; ?>">
                                            <img class="img-responsive" width="150px"
                                                src="{{ getAttachmentUrlById((int) $fd['store_logo'], $size = 'medium') }}"
                                                onerror="this.src='{{ url('/assets/images/placeholder.jpg') }}'"
                                                alt="{{ $fd['store_name'] }}" title="{{ $fd['store_name'] }}">
                                        </a>
                                    </div>
                                </div>
                                <div class=" col-md-9">
                                    <div class="row ">
                                        <div class="col-md-8 ">
                                            <a href="<?php echo $link; ?>">
                                                <h4 class="card-title">

                                                    {{ $fd['store_name'] }}
                                                </h4>
                                            </a>
                                            <p class="small">{{ date('F d, Y', strtotime($fd['created_at'])) }}</p>
                                            <!-- <p class="card-text">With supporting text below as a natural lead-in to additional content.</p> -->
                                        </div>
                                        <div class="col-md-4">


                                            <a class="small removefav" data-fav-id="{{ $fd['id'] }}"
                                                href="javascript:void(0)"><svg xmlns="http://www.w3.org/2000/svg"
                                                    fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                                    stroke="currentColor" class="w-6 h-6">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M6 18L18 6M6 6l12 12" />
                                                </svg>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php
                            }
                        } else {
                            ?>

                        <div class="dshbrdin">
                            <div class="container">
                                <div class="row">
                                    <div class="col-md-12">

                                        No record Found.
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php }                                   ?>
                        <!-- Product List Ends -->

                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="uploadImages">
        <span></span>
    </div>
</main>
<script>
    $(document).ready(function() {
        $(".removefav").on("click", function() {
            html = $(this).closest('.dshprdbx');
            var favId = $(this).attr('data-fav-id');
            data = {};
            data['favourite_id'] = favId;
            console.log(data);
            swal({
                title: "",
                text: " Are you sure you want to remove this product from favorites ?",
                type: "warning",
                showCancelButton: true,
                confirmButtonClass: "btn-success",
                confirmButtonText: "Yes, Do it!",
                closeOnConfirm: false
            }, function() {

                $(".uploadImages").show();

                sendreq(data, 'favourite', 'remove', 'callremoveItem');
                swal("Success!", "", "success");
                $(".uploadImages").hide();
                // return false;
            });

            //var result = confirm(" Are you sure want to remove from favorites.?");
            //        $(".uploadImages").show();
            //        if (!result) {
            //            $(".uploadImages").hide();
            //            return false;
            //        }
            //        html = $(this).closest('.dshprdbx');
            //        var favId = $(this).attr('data-fav-id');
            //        data = {};
            //        data['favourite_id'] = favId;
            //        sendreq(data, 'favourite', 'remove', 'callremoveItem');
        });
    });

    function callremoveItem(data) {
        if (data == 'success') {
            html.remove();
        }
        //        setTimeout(function () {
        $(".uploadImages").hide();
        //        }, 1000);
    }
</script>
<script>
    //                                                jQuery(document).ready(function () {
    //                                                    jQuery(document).on('change', '#vendor-filters .sort-field', function () {
    //
    //                                                        jQuery('#vendor-filters').trigger('submit');
    //                                                    })
    //                                                });
    $(document).on('change', '#fav-filter', function() {
        var url = $(this).val();
        window.location = url;
    });
</script>
@endsection
