@extends('layouts.dashboard-main')
<!-- Main Banner Starts -->
@section('content')
@section('title', 'Featured Product')
<main>

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
                            <a class="btn btn-primary btn-simple small-btn-mbt" href="{{ URL::to('/store-dashboard') }}">
                                Back to Dashboard
                            </a>
                        </div>
                    </div>

                    <div class="dashprdlst">
                        <!-- Product Sorting Starts -->
                        <div class="row">

                            <!--                            <div class="col-md-6 text-                                                            right">
                                                                            <div class="btn-group pull-right" role="group" aria-label="                                                                Basic example">
                                                                                <button type="button" class="btn btn-primary btn-simple ac                                                                tive">Products</button>
                                                                                <button type="button" class="btn btn-prim                                                            ary btn-simple">Brands</button>
                                                                                                   </div>
                                                           </                        div> -->
                        </div>
                        <!-- Product Sorting Ends-->

                        <div class="row">
                            <div class="col-md-6">
                                <h3 class="mb-0">Featrued Product </h3>
                            </div>
                            <div class="col-md-6">
                                <div class="  form-inline small pull-right ">
                                    <label for="text">Sort By:</label>&nbsp;&nbsp;
                                    <select class="form-control sort-field" id="fav-filter" name="order">
                                        <option>Sort by</option>
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
                 //       echo 'Data: <pre>' .print_r($favData,true). '</pre>'; die;
                            foreach ($favData as $fd) {

                                if ($fd['status'] != "1") {

                                    $class = "otofstock";
                                } else {
                                    $class = "";
                                }
                                ?>
                        <div class="dshprdbx border <?php echo $class; ?>">
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="fav-img-cnt">
                                        <img class="img-responsive"
                                            src="{{ getAttachmentUrlById((int) $fd['feature_Image'], $size = 'medium') }}"
                                            onerror="this.src='{{ url('/assets/images/placeholder.jpg') }}'"
                                            alt="{{ $fd['title'] }}" title="{{ $fd['title'] }}">
                                    </div>
                                </div>
                                <div class=" col-md-9">
                                    <div class="row ">
                                        <div class="col-md-8 ">
                                            <h4 class="card-title">
                                                <?php
                                                //                                                        if ($fd['listing_type'] == 'post') {
                                                //                                                            $link = URL::to('/post-detail/' . $fd['slug']);
                                                //                                                        } else {
                                                $link = URL::to('/product/' . $fd['slug']);
                                                //}
                                                ?>
                                                <a href="<?php echo $link; ?>">
                                                    {{ $fd['title'] }}
                                                </a>
                                            </h4>
                                            <p class="small">{{ date('F d, Y', strtotime($fd['feature_startdate'])) }}
                                            </p>
                                            <p class="small">{{ date('F d, Y', strtotime($fd['feature_enddate'])) }}
                                            </p>
                                            <!-- <p class="card-text">With supporting text below as a natural lead-in to additional content.</p> -->
                                        </div>
                                        <div class="col-md-4">
                                            <p>Price: <span class="zit-price">USDC
                                                    {{ number_format($fd['price'], 2) }}</span></p>

                                            <button><a class="small removefeatured"
                                                    data-feature-id="{{ $fd['product_id'] }}"
                                                    href="javascript:void(0)">Remove Featured</a></button>
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
        $(".removefeatured").on("click", function() {

            html = $(this).closest('.dshprdbx');
            var featureId = $(this).attr('data-feature-id');

            data = {};
            data['featured_id'] = featureId;
            swal({
                title: "",
                text: " Are you sure you want to remove this product from featured Product ?",
                type: "warning",
                showCancelButton: true,
                confirmButtonClass: "btn-success",
                confirmButtonText: "Yes, Do it!",
                closeOnConfirm: false
            }, function() {
                console.log(data);

                $(".uploadImages").show();

                sendreq(data, 'featureproduct', 'remove', 'callremoveItem');
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
