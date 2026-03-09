@extends('layouts.dashboard-main')
<!-- Main Banner Starts -->
@section('content')
@section('title', 'Favorites')
<main>

    <div class="flex-parent-div bg-light-gray favrote-section-ja ">
        <div class="change-password-page-cmt">
            @include('buyer.customsidebar')
        </div>


        <!-- Dashboard Ends -->
        <?php
    
    if (!empty($favData)) {
        ?>
        <div class="dshbrdin favorites-section home-section cm-edit-ja">
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <h3 class="mb-0 favorites_heading">
                            <div class="d-flex align-items-center gap-3">
                                <a href="javascript:void(0)"
                                    class="d-flex align-items-center gap-10px sidebar-admin-burger-menu"
                                    id="open-sidebar-admin-panel">
                                    <span id="menu-toggle-dashboard" class="shopzit-menu mt-0 ml-0 bx-menu">
                                        <span></span><span></span><span></span> <span></span>
                                    </span>
                                    <!-- <span>Menu</span> -->
                                </a>
                                <span>Favorites</span>
                            </div>



                        </h3>
                    </div>
                    <div class="col-md-6 text-right mb-25">
                        <div class="hd-bt">
                            <h1></h1>
                        </div>
                        <div class="add-pr-lnk">
                            <a class="btn btn-primary btn-simple small-btn-mbt" href="{{ URL::to('/user-dashboard') }}">
                                Back to Dashboard
                            </a>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6"></div>
                    <div class="col-md-6 sortby-filter">
                        <div class="  form-inline small pull-right ">
                            <label for="text">Sort By:</label>&nbsp;&nbsp;
                            <select class="form-control sort-field" id="fav-filter" name="order">
                                <!-- <option>Sort by</option> -->
                                <option <?php
                                if (isset($_GET['order']) && $_GET['order'] == 'DESC') {
                                    echo 'selected';
                                }
                                ?> value="{{ Request::Url() }}?order=DESC">New to old</option>
                                <option <?php
                                if (isset($_GET['order']) && $_GET['order'] == 'ASC') {
                                    echo 'selected';
                                }
                                ?> value="{{ Request::Url() }}?order=ASC">Old to new</option>
                                <option <?php
                                if (isset($_GET['price']) && $_GET['price'] == 'DESC') {
                                    echo 'selected';
                                }
                                ?> value="{{ Request::Url() }}?price=DESC">Price: High to Low
                                </option>
                                <option <?php
                                if (isset($_GET['price']) && $_GET['price'] == 'ASC') {
                                    echo 'selected';
                                }
                                ?> value="{{ Request::Url() }}?price=ASC">Price: Low to High
                                </option>

                            </select>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                    <span id="fav-section-noproducts" ></span>
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



                            <!-- Product List -->
                            <?php
                            foreach ($favData as $fd) {
                                if ($fd['status'] != "1") {

                                    $class = "otofstock";
                                } else {
                                    $class = "";
                                }
                                ?>
                             <div class="dshprdbx <?php echo $class; ?>" style="margin:20px 0px;padding-bottom:15px;">
                                <div class="row align-items-start ">
                                    <div class="col-md-3">
                                        <div class="fav-img-cnt">
                                            <img class="img-responsive"
                                                src="{{ getAttachmentUrlById((int) $fd['feature_Image'], $size = 'medium') }}"
                                                onerror="this.src='{{ url('/assets/images/placeholder.jpg') }}'"
                                                alt="{{ $fd['title'] }}" title="{{ $fd['title'] }}">
                                        </div>
                                    </div>
                                    <div class=" col-md-9">
                                        <div class="row position-relative">
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
                                                <p class="small date-title">
                                                    {{ date('F d, Y', strtotime($fd['created_at'])) }}</p>
                                                <!-- <p class="card-text">With supporting text below as a natural lead-in to additional content.</p> -->
                                            </div>
                                            <div class="col-md-2">
                                                <p class="fav-frice-tag  custom-position"> <span class="zit-price">$
                                                        {{ number_format($fd['price'], 2) }}</span>

                                                </p>


                                            </div>

                                            <div class="col-md-2">
                                                <div class="trash-icon  custom-position">
                                                    <a class="small removefav" data-fav-id="{{ $fd['favourite_id'] }}"
                                                        href="javascript:void(0)"><svg
                                                            xmlns="http://www.w3.org/2000/svg" fill="none"
                                                            viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                                                            class="w-6 h-6">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                                                        </svg>

                                                    </a>
                                                </div>
                                            </div>

                                        </div>
                                    </div>
                                </div>
                            </div>
                           
                            <?php
                            }
                        } else {
                            ?>

                            <div class="dshbrdin home-section cm-edit-ja">
                                <div class="container">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <h1 class="account-heading favorite">
                                                <div class="d-flex align-items-center gap-3">
                                                    <a href="javascript:void(0)"
                                                        class="d-flex align-items-center gap-10px sidebar-admin-burger-menu"
                                                        id="open-sidebar-admin-panel">
                                                        <span id="menu-toggle-dashboard"
                                                            class="shopzit-menu mt-0 ml-0 bx-menu">
                                                            <span></span><span></span><span></span> <span></span>
                                                        </span>
                                                        <!-- <span>Menu</span> -->
                                                    </a>
                                                    <h4>Favorites</h4>
                                                </div>


                                            </h1>
                                            {{-- No record Found. --}}

                                            <div class="no-record-found-banner">
                                                <a href="https://mallshark.net/search?keyword=">
                                                    <img src="{{ URL::asset('assets/images/No-Favs.png') }}"
                                                        alt="">
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php }  ?>
                            <!-- Product List Ends -->

                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="uploadImages">
            <span></span>
        </div>

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
                swal.close();
                toastr.options = {
                    closeButton: true, // Add close button
                    disableTimeOut: true,
                    tapToDismiss: true,
                    positionClass: 'toast-top-right',
                    toastClass: 'toast-icon custom-toast-success'
                    };
                toastr.success('Product removed from favorites successfully');
                
                $(".uploadImages").hide();
            });

        });
    });

    function callremoveItem(data) {
        
    if (data.status) {
        html.remove();
    } else{
        html.remove();
        $('.dashprdlst').remove();
        $('.sortby-filter').remove();
        // Replace the HTML content of the element with id "fav-section-noproducts"
        $("#fav-section-noproducts").html(`
            <div class="no-record-found-banner">
                <a href="https://mallshark.net/search?keyword=">
                    <img src="{{ URL::asset('assets/images/No-Favs.png') }}" alt="">
                </a>
            </div>
        `);
    }
}

</script>
<script>

    $(document).on('change', '#fav-filter', function() {
        var url = $(this).val();
        window.location = url;
    });
</script>
@endsection
