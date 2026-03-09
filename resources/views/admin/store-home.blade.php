<?php
$aboutus = isset($storeinfo[0]->about_us) ? $storeinfo[0]->about_us : '';
$street_address = isset($storeinfo[0]->street_address) ? $storeinfo[0]->street_address : '';
$country_id = isset($storeinfo[0]->store_country) ? $storeinfo[0]->store_country : '';
$store_city = isset($storeinfo[0]->store_city) ? $storeinfo[0]->store_city : '';
$store_suit = isset($storeinfo[0]->store_suit) ? $storeinfo[0]->store_suit : '';
$store_state = isset($storeinfo[0]->store_state) ? $storeinfo[0]->store_state : '';
$zip_code = isset($storeinfo[0]->zip_code) ? $storeinfo[0]->zip_code : '';
$contact_email = isset($storeinfo[0]->contact_email) ? $storeinfo[0]->contact_email : '';
$contact_info = isset($storeinfo[0]->contact_info) ? $storeinfo[0]->contact_info : '';
$store_logo = isset($storeinfo[0]->store_logo) ? $storeinfo[0]->store_logo : '';
$store_banner1 = isset($storeinfo[0]->image_id1) ? $storeinfo[0]->image_id1 : '';
$store_banner2 = isset($storeinfo[0]->image_id2) ? $storeinfo[0]->image_id2 : '';
$store_banner3 = isset($storeinfo[0]->image_id3) ? $storeinfo[0]->image_id3 : '';
$logourl = '';
if ($store_logo != '') {
    $logourl = getAttachmentUrlById((int) $store_logo, 'small');
}

$country_name = '';
if ($country_id == 1) {
    $country_name = 'USA';
}
if ($country_id == 2) {
    $country_name = 'CANADA';
}
$state_name = '';
if ($store_state != '') {
    $state = getStateById($store_state);
    if (isset($state['state_name'])) {
        $state_name = $state['state_name'];
    }
}
?>
@extends('layouts.new-main')
<!-- Main Banner Starts -->
@section('content')
@section('title', 'Mallshark')
@section('google-analytics')
@include('layouts.google-analytics')
@stop
<link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.9.0/slick.css" media="all"/>
<style>
    .search-icon-header {
        display: inline-block;
    }

    @media only screen and (min-width: 320px) and (max-width: 767px) {
        #menu-toggle {
            display: inline-block;
        }

        header .navbar-brand {
            margin-left: 30px;
            width: 60px;
        }
        header .navbar-brand img{

        }
        .results-box-mbt.row .col-md-6:nth-child(odd){
            padding-right: 9px;
        }
        .results-box-mbt.row .col-md-6:nth-child(even){
            padding-left: 9px;
        }
    }
</style>

<div id="sidebar-wrapper" class="left-sidebar">
    <div class="scroll-sidebar ps ps--theme_default ps--active-y">
        <?php
        $query_category = Request::Url() . '?';

        if (isset($_GET['keyword']) && $_GET['keyword'] != '') {
            //$query_category .='keyword=' . $_GET['keyword'] . '&';
        }
        if (isset($_GET['min_price']) && $_GET['min_price'] != '') {
            //$query_category .='min_price=' . $_GET['min_price'] . '&';
        }
        if (isset($_GET['max_price']) && $_GET['max_price'] != '') {
            //$query_category .='max_price=' . $_GET['max_price'] . '&';
        }
        if (isset($_GET['sort_by']) && isset($_GET['sort_order'])) {
            // $query_category .='sort_by=' . $_GET['sort_by'] . '&';
            // $query_category .='sort_order=' . $_GET['sort_order'] . '&';
        }
        ?>
        <?php
        if (isset($_REQUEST['keyword']) || isset($_REQUEST['category_child']) || isset($_REQUEST['category']) || isset($_REQUEST['category_parent']) || isset($_REQUEST['min_price']) || isset($_REQUEST['max_price']) || isset($_REQUEST['sort_by']) || isset($_REQUEST['sort_order'])) {
            $symbol = '&';
        } else {
            $symbol = '?';
        }

        $query_String = Request::Url() . '?';
        if (isset($_REQUEST['category_child']) && $_REQUEST['category_child'] != '') {
            $query_String .= '&category_child=' . $_REQUEST['category_child'];
        }
        if (isset($_REQUEST['category_parent']) && $_REQUEST['category_parent'] != '') {
            $query_String .= '&category_parent=' . $_REQUEST['category_parent'];
        }
        if (isset($_REQUEST['category']) && $_REQUEST['category'] != '') {
            $query_String .= '&category=' . $_REQUEST['category'];
        }
        if (isset($_REQUEST['min_price']) && $_REQUEST['min_price'] != '') {
            $query_String .= '&min_price=' . $_REQUEST['min_price'];
        }
        if (isset($_REQUEST['max_price']) && $_REQUEST['max_price'] != '') {
            $query_String .= '&max_price=' . $_REQUEST['max_price'];
        }
        if (isset($_REQUEST['keyword']) && $_REQUEST['keyword'] != '') {
            $query_String .= '&keyword=' . $_REQUEST['keyword'];
        }
        if (isset($_REQUEST['condition']) && $_REQUEST['condition'] != '') {
            $query_String .= '&condition=' . $_REQUEST['condition'];
        }
        if (isset($_REQUEST['lat']) && isset($_REQUEST['lon']) && isset($_REQUEST['radius'])) {
            $query_String .= '&lat=' . $_REQUEST['lat'];
            $query_String .= '&lon=' . $_REQUEST['lon'];
            $query_String .= '&radius=' . $_REQUEST['radius'];
        }

        if ($symbol == '&') {
            $query_String .= '&';
        }
        // print_rr($productResults);
        //die();
        ?>
        <nav class="sidebar-nav-container">
            <?php
            $llCategoreis = getAllCategories();
            if (count($llCategoreis) > 0) {
                ?>
                <ul class="sidebar-nav">
                    <?php
                    foreach ($llCategoreis as $key => $val) {
                        $parent_Category_title = isset($val['cat_info']['title']) ? $val['cat_info']['title'] : '';
                        $parent_Category_slug = isset($val['cat_info']['slug']) ? $val['cat_info']['slug'] : '';
                        $parent_Category_id = isset($val['cat_info']['id']) ? $val['cat_info']['id'] : '';
                        ?>

                        <li class="nav-item">


                            <a style="display:block" class="nav-link"
                               href="{{$query_category}}category=<?php echo $parent_Category_slug; ?>">{{$parent_Category_title}} </a>
                               <?php if (isset($val['childs']) && count($val['childs']) > 0) { ?>
                                <a style="display:block" class="nav-link collapsed parent-nave <?php
                                if (isset($_GET['category']) && $_GET['category'] == $parent_Category_slug) {
                                    echo 'parent-active';
                                }
                                ?>" href="#" data-toggle="collapse" data-target="#nav-{{$parent_Category_id}}-mob">
                                    <i class="fa fa-chevron-right" aria-hidden="true"></i>

                                </a> <?php } ?>
                            <?php
                            if (isset($val['childs']) && count($val['childs']) > 0) {
                                ?>
                                <div class="sub-sect-cnt" id="nav-{{$parent_Category_id}}-mob" aria-expanded="false">
                                    <ul class="sub-menu-category">
                                        <?php foreach ($val['childs'] as $newVal) {
                                            ?>
                                            <li class="nav-item"><a class="nav-link <?php
                                                if (isset($_GET['category']) && $_GET['category'] == $newVal['slug']) {
                                                    echo 'active';
                                                }
                                                ?>"
                                                                    href="{{$query_category}}category=<?php echo isset($newVal['slug']) ? $newVal['slug'] : ''; ?>"><?php echo isset($newVal['title']) ? $newVal['title'] : ''; ?></a>
                                            </li>
                                        <?php } ?>
                                        <div class="go-back-btn-lnk">
                                            <a href="#">
                                                <i class="fa fa-chevron-left" aria-hidden="true"></i> Go Back </a>
                                        </div>
                                    </ul>
                                </div>

                                <?php
                            }
                        }
                        ?>
                    </li>
                </ul>
            <?php } ?>

        </nav>
    </div>
</div>
<!-- /#sidebar-wrapper -->

<section class="page-search-results store-home-page">
    <div class="home-container store-container-page store-slide">
        <div class="home-main-banner" style="background-image: url({{getAttachmentUrlById((int) $store_banner1,'media') }});">
            <div class="container d-flex h-100">
                <div class="row justify-content-center align-self-center">
                    <div class="col-sm-12">
                        <div class="home-banner-content " style="display:none">
                            <h1 class="">Wheel Chairs <br>
                                To <span class="yellow-fnt">Move</span> Easy and<br>
                                <span class="blue-text">Maintain</span>Easy
                            </h1>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="home-main-banner" style="background-image: url({{getAttachmentUrlById((int) $store_banner2,'media') }});">
            <div class="container d-flex h-100">
                <div class="row justify-content-center align-self-center">
                    <div class="col-sm-12">
                        <div class="home-banner-content " style="display:none">
                            <h1 class="">Wheel Chairs <br>
                                To <span class="yellow-fnt">Move</span> Easy and<br>
                                <span class="blue-text">Maintain</span>Easy
                            </h1>
                        </div>
                    </div>
                </div>
            </div>
        </div>   
    </div>
    <div class="store-menu-navigation">
        <div class="container">
            <div class="row">
                <div class="col-8">
                    <nav class="navbar-custom">
                        <!-- Links -->
                        <ul class="navbar-nav-store">
                            <li class="nav-item">
                                <a class="nav-link" href="#">Home</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="#">Products</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="#">About Us</a>
                            </li>
                        </ul>
                    </nav>
                </div>
                <div class="col-4">
                    <p class="text-right call-text">Call: <?php echo $contact_info; ?></p>
                </div>
            </div>
        </div> 
    </div>
    <!--    store-menu-navigation-->
    <div class="container">
        <div class="row cdt-bdy">
            <div class="col-md-3 hide-mobile">
                <div class="pos-rel">
                    <div class="sidebar-filter pos-absolute mb-20">
                        <div class="sidebar-content mt-40 mobile-mt-0">
                            <h3 class="bros-heading gray-text fnt18">
                                <span class="broser-cate-cion"><i class="material-icons">list</i></span>
                                Store Categories</h3>

                            <?php if (count($llCategoreis) > 0) { ?>


                                <ul class="nav flex-column flex-nowrap">
                                    <?php
                                    foreach ($llCategoreis as $key => $val) {
                                        $parent_Category_title = isset($val['cat_info']['title']) ? $val['cat_info']['title'] : '';
                                        $parent_Category_slug = isset($val['cat_info']['slug']) ? $val['cat_info']['slug'] : '';
                                        $parent_Category_id = isset($val['cat_info']['id']) ? $val['cat_info']['id'] : '';
                                        ?>

                                        <li class="nav-item">
                                            <div style="display: flex;">
                                                <?php if (isset($val['childs']) && count($val['childs']) > 0) { ?>
                                                    <a style="display:inline-block" class="nav-link collapsed parent-nave <?php
                                                    if (isset($_GET['category']) && $_GET['category'] == $parent_Category_slug) {
                                                        echo 'parent-active';
                                                    }
                                                    ?>" href="#" data-toggle="collapse"
                                                       data-target="#nav-{{$parent_Category_id}}"></a> <?php } ?>
                                                <a style="display:inline-block" class="nav-link"
                                                   href="{{$query_category}}category=<?php echo $parent_Category_slug; ?>">{{$parent_Category_title}}</a>
                                            </div>
                                            <?php
                                            if (isset($val['childs']) && count($val['childs']) > 0) {
                                                ?>
                                                <div class="collapse" id="nav-{{$parent_Category_id}}" aria-expanded="false">
                                                    <ul class="flex-column pl-2 nav inner-nav-mbt">
                                                        <?php foreach ($val['childs'] as $newVal) {
                                                            ?>
                                                            <li class="nav-item"><a class="nav-link <?php
                                                                if (isset($_GET['category']) && $_GET['category'] == $newVal['slug']) {
                                                                    echo 'active';
                                                                }
                                                                ?>"
                                                                                    href="{{$query_category}}category=<?php echo isset($newVal['slug']) ? $newVal['slug'] : ''; ?>"><?php echo isset($newVal['title']) ? $newVal['title'] : ''; ?></a>
                                                            </li>
                                                        <?php } ?>
                                                    </ul>
                                                </div>

                                                <?php
                                            }
                                        }
                                        ?>
                                    </li>
                                </ul>
                                <?php
                            }
                            ?>



                        </div>
                    </div>
                </div>
            </div>


            <div class="col-md-9 pt-40 ">

                <div class="results-box-mbt row">
                    <?php if (!empty($productResults) && count($productResults['records']) > 0) { ?>


                        <?php
                        $favProductId = getBuyerFavProducts();
                        foreach ($productResults['records'] as $pr) {
                            ?>
                            <div class="col-md-6 col-lg-4">
                                <div class="card card-white-sec pos-rel">
                                    <div class="add-to-fav">
                                        <?php
                                        if (Session::has('userData')) {
                                            if (!empty($favProductId) && in_array($pr['_source']['product_id'], $favProductId)) {
                                                $fill = "animate fillheart";
                                            } else {
                                                $fill = "";
                                            }
                                            ?>
                                            <div class="HeartAnimation <?php echo $fill; ?>"
                                                 data-product-id="<?php echo $pr['_source']['product_id']; ?>"></div>
                                             <?php } else { ?>

                                            <a class="fav-login login-popup" data-location="{{URL::to('q/')}}"
                                               href="javascript:void(0)" data-toggle="modal" data-target="#login-registerpopup">
                                                <div class="HeartAnimation"></div>

                                            </a>
                                        <?php } ?>
                                    </div><!-- add-to-fav -->
                                    <div class="box-img-mbt">
                                        <?php if (isset($pr['_source']['is_feature']) && $pr['_source']['is_feature'] == 1) {
                                            ?>
                                            <div class="feature-product-sec">
                                                <span>featured</span>
                                            </div>
                                            <?php
                                        }
                                        ?>
                                        <?php $imageUrl = getAttachmentUrlById((int) $pr['_source']['feature_image'], 'medium'); ?>
                                        <img src=" {{ $imageUrl }}" class="img-fluid" alt="{{$pr['_source']['title']}}" title="{{$pr['_source']['title']}}">
                                    </div>
                                    <div class="box-description-mbt text-center">
                                        <div class="data-cnt-bdy">
                                            <?php
                                            if ($pr['_source']['listing_type'] == 'post') {
                                                $url = URL::to('/post-detail/' . $pr['_source']['slug']);
                                            } else {
                                                $url = URL::to('/product/' . $pr['_source']['slug']);
                                            }
                                            ?>
                                            <h3 class="mb-10"><a
                                                    href="<?php echo $url; ?>"><?php echo $pr['_source']['title']; ?></a>
                                            </h3>
                                            <h4 class="price-show text-blue"><b><span class="zit-price">Z<?php echo number_format($pr['_source']['price'], 2); ?></span></b>
                                                <span class="show-mobileinline shiping-cost-bt">
                                                    + Shipping: $10, Tax: $20
                                                </span>
                                            </h4>
                                            <p>
                                                {!! \Illuminate\Support\Str::words($pr['_source']['description'], 10,'....')  !!}


                                            </p>
                                        </div>
                                        <?php
                                        if (isset($cartProducts) && array_key_exists($pr['_source']['product_id'], $cartProducts)) {
//                                            if ($variableproducts > 0) {
//                                                $class = "";
//                                            } else {
                                            $class = "show-quantitiy-panel";
                                            //}
                                            $key2 = $cartProducts[$pr['_source']['product_id']];
                                        } else {
                                            $class = "";
                                        }
                                        ?>
                                        <?php
                                        $storeDataId = "";
                                        if (!empty($pr['_source']['store_id'])) {
                                            $storeDataId = "data-store-id=" . $pr['_source']['store_id'];
                                        }
                                        ?>
                                        <?php
                                        //print_rr($pr);
                                        if ($pr['_source']['listing_type'] != 'post') {

                                            if (Session::has('userData')) {
                                                ?>

                                                <div class="crtbtn col  buttons-group-cnt <?php echo $class; ?>">
                                                    <?php if ($pr['_source']['product_type'] == 'variant' || $pr['_source']['weight'] > 30) { ?>
                                                        <a <?php echo $storeDataId; ?>  href="<?php echo URL::to('product/' . $pr['_source']['slug']); ?>"
                                                                                        class="primary-btn btn btn-primary btnorm">Add To Cart</a>
                                                                                    <?php } else { ?>
                                                        <button type="button"
                                                                <?php echo $storeDataId; ?> data-product-id="<?php echo $pr['_source']['product_id']; ?>"
                                                                class="primary-btn btn btn-primary btnorm cartadd ">Add To Cart
                                                        </button>

                                                        <div class="input-group quantity-number-option">
                                                            <span class="minus-icon first-icon-counter"
                                                                  data-pro-id="<?php echo $pr['_source']['product_id']; ?>" <?php echo $storeDataId; ?>>
                                                                <i class="fa fa-minus" aria-hidden="true"></i>
                                                            </span>
                                                            <span class="input-counter">

                                                                <input type="text" name="cart_quantity" id="cart_quantity"
                                                                       class="form-control input-number"
                                                                       placeholder="in cart" value="<?php
                                                                       if (!empty($key2)) {
                                                                           echo $key2;
                                                                       } else {
                                                                           echo 1;
                                                                       }
                                                                       ?>" min="1" max="50" readonly="">
                                                <!-- <span class="static-text"> in cart</span> -->
                                                            </span>
                                                            <span class="plus-icon second-icon-counter"
                                                                  data-pro-id="<?php echo $pr['_source']['product_id']; ?>" <?php echo $storeDataId; ?>><i
                                                                    class="fa fa-plus" aria-hidden="true"></i></span>
                                                        </div>
                                                    <?php } ?>

                                                </div>

                                            <?php } else {
                                                ?>
                                                <a class="fav-login login-popup primary-btn btn btn-primary" data-location="{{URL::to('q/')}}"
                                                   href="javascript:void(0)" data-toggle="modal" data-target="#login-registerpopup">Add To Cart</a>
                                                   <?php
                                               }
                                           } else {
                                               ?>

                                            <div class="crtbtn col  buttons-group-cnt  contactus-btn  contactus-btn">
                                                <a href="#" class="primary-btn btn btn-primary btnorm">Contact
                                                    Seller</a>
                                            </div>
                                            <div class="contact-info">
                                                <?php
                                                if (isset($pr['_source']['contact_info'])) {
                                                    // echo ($userRes->user_phone);
                                                }
                                                ?>
                                                <a href="<?php
                                                if (isset($pr['_source']['contact_info'])) {
                                                    echo($pr['_source']['contact_info']);
                                                }
                                                ?>"><?php
                                                       if (isset($pr['_source']['contact_info'])) {
                                                           echo($pr['_source']['contact_info']);
                                                       }
                                                       ?></a>
                                            </div>


                                        <?php } ?>


                                    </div><!-- box-description-mbt -->
                                </div>
                            </div>
                        <?php } ?>


                    <?php } else { ?>
                        <div class="col-sm-9">

                            No record found
                        </div>
                    <?php } ?>
                </div>





                <div class="about-us-section-mbt card card-white-sec pos-rel">
                    <h4>About Us</h4>
                    <p><?php echo $aboutus; ?>  </p>
                    <h4>Address</h4> 

                    <p>
                        <?php echo $store_suit . ' ' . $street_address; ?>,
                        <br> <?php echo $store_city . ' ' . $state_name; ?>, <?php echo $zip_code; ?>, <?php echo $country_name; ?></p>
                    <h4>Contact</h4>                    
                    <p><?php echo $contact_email; ?><br>
                        <?php echo $contact_info; ?></p>                    

                </div>
            </div>  
        </div>
    </div>
</section>
<section>
    <div class="container mt-30 boxes-payment hide-mobile background-white-inner ">
        <div class="row">
            <div class="row ml-14 mr-14 shahdow-all">
                <div class="col-md-2">
                    <div class="value-box text-center">
                        <span class="img-cnt-msd">
                            <img src=" {{ asset('assets/images/box-icon1.png',env('HTTPS_STATUS')) }}" alt="" class="img-fluid">
                        </span>
                        <h4>Great Value</h4>
                        <p>We offer competitive prices on our 100 million plus product range</p>
                    </div>
                </div>

                <div class="col-md-2">
                    <div class="value-box text-center">
                        <span class="img-cnt-msd">
                            <img src=" {{ asset('assets/images/box-icon2.png',env('HTTPS_STATUS')) }}" class="img-fluid">
                        </span>
                        <h4>Worldwide Delivery</h4>
                        <p>with sites in 5 languages, we ship to over 200 countries & regions,</p>
                    </div>
                </div>

                <div class="col-md-2">
                    <div class="value-box text-center">
                        <span class="img-cnt-msd">
                            <img src=" {{ asset('assets/images/box-icon3.png',env('HTTPS_STATUS')) }}" class="img-fluid">
                        </span>
                        <h4>Safe Payment</h4>
                        <p>Pay with the world's most popular and secure payment methods.</p>
                    </div>
                </div>

                <div class="col-md-2">
                    <div class="value-box text-center">
                        <span class="img-cnt-msd">
                            <img src=" {{ asset('assets/images/box-icon4.png',env('HTTPS_STATUS')) }}" class="img-fluid">
                        </span>
                        <h4>Shop with confidence</h4>
                        <p>Our Buyer protection covers your purchase from click to delivery</p>
                    </div>
                </div>


                <div class="col-md-2">
                    <div class="value-box text-center">
                        <span class="img-cnt-msd">
                            <img src=" {{ asset('assets/images/box-icon5.png',env('HTTPS_STATUS')) }}" class="img-fluid">
                        </span>
                        <h4>24/7 Help Center</h4>
                        <p>Round-the-clock assistance for a smooth shopping experience.</p>
                    </div>
                </div>

                <div class="col-md-2">
                    <div class="value-box text-center">
                        <span class="img-cnt-msd">
                            <img src=" {{ asset('assets/images/box-icon6.png',env('HTTPS_STATUS')) }}" class="img-fluid">
                        </span>
                        <h4>Shop on-the-Go</h4>
                        <p>Pay with the world's most popular and secure payment methods</p>
                    </div>
                </div>

            </div>


        </div>
    </div>
</section>


</main>

<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.9.0/slick.min.js"></script>
<script>
jQuery(document).ready(function () {
    jQuery('.active').closest('.collapse').parent().find('.parent-nave').click();

});
jQuery(document).ready(function () {
    jQuery('.parent-active').click();

});
$('.store-slide').slick({
    dots: true,
    infinite: true,
    speed: 500,
    cssEase: 'linear'
});



$(window).load(function () {
    $("body").addClass("elastic-section");
});


// script forr search results category filter
$(".sub-sect-cnt").hide();
$("#sidebar-wrapper .sidebar-nav > li > a.parent-nave").click(function () {
    event.stopPropagation();
    $(".sidebar-nav > li").hide();
    $(this).next().fadeIn();
    $(this).parent().fadeIn();
    $(this).prev().addClass('active-nav-menu');

});

$(".go-back-btn-lnk a").click(function () {
    event.stopPropagation();
    $(".sub-sect-cnt").hide();
    var $self = $(this).parentsUntil().find('.sidebar-nav li').fadeIn("");
    $('.sidebar-nav li a').removeClass('active-nav-menu');

});

// script forr  search results category menu
jQuery('.contact-info').hide();

$(".contactus-btn a").click(function () {
    $(this).parent().hide();
    $(this).parent().next('.contact-info').show();

});


$(document).ready(function () {
    $('#menu-toggle').click(function (event) {
        event.stopPropagation();
        $("#sidebar-wrapper").slideToggle("fast");
    });
    $("#sidebar-wrapper").on("click", function (event) {
        event.stopPropagation();
    });
});

$(document).on("click", function () {
    $("#sidebar-wrapper").slideUp();
});

$(function () {
    $("#search-filters").submit(function () {
        $(this).find(":input[type=number],select,input[type=hidden]").filter(function () {
            return !this.value;
        }).attr("disabled", "disabled");


        return true; // ensure form still submits
    });
});




</script>
<style>
    a.nav-link.active {
        color: #34a08d !important;
    }
</style>
@endsection
<!-- Content Ends-->




