<?php

use Stripe\EphemeralKey;

if (!empty($storeinfo[0])) {

    $store_owner = getUserById($storeinfo[0]->user_id);
}
$aboutus = isset($storeinfo[0]->about_store) ? $storeinfo[0]->about_store : '';
$policies = isset($shop_policies) ? $shop_policies : '';
$store_id = isset($storeinfo[0]->store_id) ? $storeinfo[0]->store_id : '';
$street_address = isset($storeinfo[0]->street_address) ? $storeinfo[0]->street_address : '';
$country_id = isset($storeinfo[0]->store_country) ? $storeinfo[0]->store_country : '';
$store_state = isset($storeinfo[0]->store_state) ? $storeinfo[0]->store_state : '';
$store_city = isset($storeinfo[0]->store_city) ? $storeinfo[0]->store_city : '';
$zip_code = isset($storeinfo[0]->zip_code) ? $storeinfo[0]->zip_code : '';
$store_name = isset($storeinfo[0]->store_name) ? $storeinfo[0]->store_name : '';
$contact_email = isset($store_owner->email) ? $store_owner->email : '';
$contact_info = isset($storeinfo[0]->contact_info) ? $storeinfo[0]->contact_info : '';
$store_logo = isset($storeinfo[0]->store_logo) ? $storeinfo[0]->store_logo : '';
$store_banner1 = isset($storeinfo[0]->image_id1) ? $storeinfo[0]->image_id1 : '';
$store_banner2 = isset($storeinfo[0]->image_id2) ? $storeinfo[0]->image_id2 : '';
$store_banner3 = isset($storeinfo[0]->image_id3) ? $storeinfo[0]->image_id3 : '';
$date_created = isset($storeinfo[0]->created_at) ? $storeinfo[0]->created_at : '';
$date_updated = isset($storeinfo[0]->updated_at) ? $storeinfo[0]->updated_at : '';
$bannerSerialize = isset($storeinfo[0]->banner_image_id) ? $storeinfo[0]->banner_image_id : '';
// dd($bannerSerialize);
$date_store_created = date('Y', strtotime($date_created));
$total_sales = isset($total_sales) ? $total_sales : '0';
$total_items = isset($total_items) ? $total_items : '0';
$constants = config('constants.shopzit_gamification');
$logourl = '';
if ($store_logo != '') {
    $logourl = getAttachmentUrlById((int) $store_logo, 'small');
}
$userId = isset(Session::get('userData')['user_id']) ? Session::get('userData')['user_id'] : '';
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

$country_name = '';
if ($country_id != '') {
    $country = getCountryById($country_id);
    if (isset($country['name'])) {
        $country_name = $country['name'];
    }
}


$totalSupporters = 0;
if (!empty($storeinfo[0]->store_id)) {
    // $totalSupporters = getTotalSupports($storeinfo[0]->store_id);
    // $totalgaveBack = getTotalgaveBack($storeinfo[0]->store_id);
}
// For get brand categories in meta description and keyword seo 
$getProductTitle = array();
 if (!empty($productResults) && count($productResults['records']) > 0) { 
foreach ($productResults['records'] as $productcate) {
    $getProductTitle[] = $productcate['_source']['title'];
    $cat = getCategoriesByProductID($productcate['_source']['product_id']);
    foreach ($cat as $key => $item) {

        $catList[] = $item['category_id'];
    }
}
$unique_array = array_unique($catList);

$getcategories = array();
foreach ($unique_array as $detail) {
    $getcategories[] = getCategoriesNameByProductID($detail);
}
$getstoreCategories = array();
foreach ($getcategories as $brandcat) {
    foreach ($brandcat as $brand) {
        $getstoreCategories[] = $brand['cat_info']['title'];
    }
}

if (isset($getstoreCategories[1])) {
    $getbrandcategory1  = $getstoreCategories[1];
} else {
    $getbrandcategory1 = '';
}

if (isset($getstoreCategories[2])) {
    $getbrandcategory2  = $getstoreCategories[2];
} else {
    $getbrandcategory2 = '';
}

if (count($getstoreCategories) >= 1) {
    $description = "Shop for the latest $store_name, $getstoreCategories[0], $getbrandcategory1 , $getbrandcategory2 etc. 
       Discover bargains and the latest $store_name products online with Mallshark, Crypto powered Ecommerce.";
}

$storedescription  = !empty($aboutus) ? strip_tags(html_entity_decode($aboutus)) : 'Discover the future of shopping with MallShark! Our innovative e-commerce platform offers a streamlined way to shop for goods and services. Join us today for a modern and hassle-free shopping experience.';
$title = $store_name;
if (isset($getProductTitle[1])) {
    $productTitleseo = $getProductTitle[1];
} else {
    $productTitleseo = '';
}
$keyword = "$store_name, $store_name $getProductTitle[0], $store_name $productTitleseo";
 }
 $query_category = url()->current() . '?';



    $slug = request()->segment(1);
    if($slug && $slug != 'brand'){
       $slug = $slug;
    }else{
        $slug = isset($_GET['category']) ? $_GET['category'] : '';
    }

?>
@extends('layouts.frontend-main')
@section('content')
@section('title', isset($title) ? $title : "No Title" )
@section('description', isset($storedescription) ? $storedescription: "")
<!-- @section('keyword', isset($keyword) ? $keyword : "No Keyword") -->
@section('google-analytics')
@include('layouts.google-analytics')
@stop
<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
<script type="text/javascript" src="//cdnjs.cloudflare.com/ajax/libs/jqueryui-touch-punch/0.2.3/jquery.ui.touch-punch.min.js"></script>
<style>
    .search-icon-header {
        display: inline-block;
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

    .owner-image {
        height: 50px;
        width: 50px;
        border-radius: 50px;
        display: inline-block;
        background: #ededed;
        line-height: 50px;
        text-align: center;
        position: relative;
    }

    .owner-head-upper-mbt {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        overflow: hidden;
        display: inline-block;
    }

    @media only screen and (min-width: 320px) and (max-width: 767px) {
        #menu-toggle {
            display: inline-block;
        }

        header .navbar-brand {
            margin-left: 0px;
        }

    }
</style>

<?php
$query_category = url()->current() . '?';

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

if (isset($_REQUEST['keyword']) || isset($_REQUEST['category_child']) || isset($_REQUEST['category']) || isset($_REQUEST['category_parent']) || isset($_REQUEST['min_price']) || isset($_REQUEST['max_price']) || isset($_REQUEST['sort_by']) || isset($_REQUEST['sort_order'])) {
    $symbol = '&';
} else {
    $symbol = '?';
}

$query_String = url()->current() . '?';
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
$llCategoreis = getAllCategories();
?>
<!-- /#sidebar-wrapper -->
<script src="{{ URL::asset('assets/js/cart.js',env('HTTPS_STATUS')) }}"></script>
<h1 style="display: none;">{{$store_name}}</h1>
{{-- <div class="page-search-results store-home-container custom-home-container-wrapper"> --}}
    <div class="page-search-results store-home-container container  ">
    <?php
    if ($bannerSerialize) {
       

        $bannerIds = unserialize($bannerSerialize);
        $banner_hider = true;
        $url_images = []; // Array to store the URLs of the banner images

        foreach ($bannerIds as $index => $bannerId) {
            $url_img = getAttachmentUrlById((int) $bannerId, 'media');
            $url_images[] = $url_img; // Append URL to the end of the array

            if ($banner_hider) {
                $url_img_main = $url_img;
            }
            $banner_hider = false;
        }

        $url_images = array_values($url_images); // Re-index the array starting from 0
    }

    ?>

    <div class="" >
        <div class="row store-landing-MobileView">
            <div class="col-md-12">
                <div class="strbnr">
                    <div id="carouselExampleIndicators" class="carousel slide" data-ride="carousel">
                        <ol class="carousel-indicators">

                            <?php

                            $dataArray = unserialize($bannerSerialize);
                          
                            if (!empty($dataArray)) {
                               
                                foreach ($url_images as $index => $url_img) : ?>
                                    <li data-target="#carouselExampleIndicators" data-slide-to="<?= $index ?>" <?= $index === 0 ? 'class="active"' : '' ?>></li>
                            <?php endforeach;
                            } ?>
                        </ol>
                        <div class="carousel-inner">
                            <?php
                            
                            if (!empty($dataArray)) {
                                foreach ($url_images as $index => $url_img) : ?>
                                
                                    <div class="carousel-item <?= $index === 0 ? 'active' : '' ?>" style="background-image:url('<?= URL::asset($url_img) ?: URL::asset('assets/images/dummy-store-hero.jpg') ?>')"></div>
                                <?php endforeach;
                            } else { ?>
                                <div class="carousel-item active " style="background-image:url('<?php echo URL::asset('assets/images/dummy-store-hero.jpg'); ?>') ">
                                <?php  } ?>
                                </div>
                                <?php 
                                    if (is_array($dataArray) && count($dataArray) > 1) { ?>
                                <a class="carousel-control-prev" href="#carouselExampleIndicators" role="button" data-slide="prev">
                                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                    <span class="sr-only">Previous</span>
                                </a>
                                <a class="carousel-control-next" href="#carouselExampleIndicators" role="button" data-slide="next">
                                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                    <span class="sr-only">Next</span>
                                </a>
                                <?php } ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>


        <div class="strhd fxd-hdbtm column-nine-no-padding">
            <?php
            if (!empty($storeinfo[0]->store_id)) {
                $store_icon = $storeinfo[0]->store_icon;
            }
            $image_id = getImageIdByIconId((int) $store_icon);
            $iconUrl = getAttachmentUrlById((int) $image_id, 'icons');
            $owner_image_style = 'style="margin-right: 10px;"';
            // echo 'Data: <pre>' .print_r($store_owner,true). '</pre>'; die();
           
              
             $logourl = getAttachmentUrlById((int) $store_logo, 'small');
           
            if (isset($store_owner->user_avatar) ? $store_owner->user_avatar : '') {
                $owner_image_url = getAttachmentUrlById((int) $store_owner->user_avatar, 'avatar');
                $owner_image_style = '';
            }
            ?>
            
            <div class="  stre-strp-tp custom-container-no-padding ">
                <div class="row  ">
                    <div class="col-md-6">
                        <div class="hdlft text-left">

                           
                                <div>
                                <?php 
                               
                                    if(!empty($store_logo)){  ?>
                                        
                                    <img width="110"  class="img-fluid" src="{{$logourl}}" alt="{{$store_owner->name}}">
                                    <?php  } else if(!empty($store_owner->user_avatar)){    ?>
                                       <?php if(!empty($owner_image_url)){ ?>
                                             <img width="110"  class="img-fluid" src="{{$owner_image_url}}" alt="{{$store_owner->name}}">
                                      <?php } ?>
                                            
    
                                    <?php }else{
                                            if(empty($store_logo)){
                                                
                                        ?>
                                        <img width="110"  class="img-fluid" src={{ URL::asset('assets/images/white_image.PNG') }}>
                                        <?php } ?>

                                        
                                        {{-- <span class="owner-image else">
                                            <span class="owner-head-upper-mbt">
                                                <span class="login-name"></span>
                                            </span>
                                        </span> --}}

                                    <?php } ?>

                                  
                                </div>
                          
                            <div class="stre-logo-sore">
                                        <h2><span><img style="display:none" src="{{ $logourl }}" class="img-fluid" alt="{{$storeinfo[0]->store_name}}" title="{{$storeinfo[0]->store_name}}"> {{ucfirst($storeinfo[0]->store_name)}}</span></h2>
                                        <?php
                                        if (!empty($store_city) && !empty($country_name)) {
                                        ?>
                                            <p>{{ucfirst($store_city)}}, {{ucfirst($country_name)}}</p>
                                        <?php    } elseif (!empty($store_city) && empty($country_name)) { ?>
                                            <p>{{ucfirst($store_city)}}</p>
                                        <?php } elseif (empty($store_city) && !empty($country_name)) { ?>
                                            <p>{{ucfirst($country_name)}}</p>
                                        <?php } else { ?>
                                            <p></p>
                                        <?php }
                                        ?>
                                        
                                          


                                          <div class="stre-reviw">
                                            <?php $rating = getStoreReviewAverage($store_id); ?>
                                                        <span class="text-left d-flex ">
                                                
                                                            
                                                            <?php
                                                            if ($rating['total_count'] > 0) {
                                                                echo $rating['html'];
                                                            } else { ?>
                                                                <span>
                                                                    <i class="fa fa-star-o" aria-hidden="true"></i>
                                                                    <i class="fa fa-star-o" aria-hidden="true"></i>
                                                                    <i class="fa fa-star-o" aria-hidden="true"></i>
                                                                    <i class="fa fa-star-o" aria-hidden="true"></i>
                                                                    <i class="fa fa-star-o" aria-hidden="true"></i>
                                                                </span>
                                                            <?php  } ?>&nbsp; 
                                                            <span><?php echo $rating['total_count']; ?>  </span>&nbsp;&nbsp;|&nbsp;&nbsp;<span><a href="#" class="txtblu ">Reviews</a> </span>
                                                            
                                                            </span>
                                       
                                         
                                        </div>

                                       
                                        <p style="display:none" class="small">{{$total_sales}} Sales &nbsp;| &nbsp; Since {{$date_store_created}} &nbsp;| &nbsp; {{$total_items}} Items</p>
                                    </div>
                          
                            
                             
                            <!-- <div class="ds-sde store-owner-name-middle  ">
                                <h5><?php
                                    // $storename = isset($store_owner->name) ? $store_owner->name : '';
                                    // echo $storename;
                                    ?></h5>
                                <p style="display:none">store owner <a href="mailto:{{ $contact_email }}?Subject=Hello%20Owner" target="_top">
                                        <i class="material-icons">
                                            mail_outline
                                        </i>
                                    </a></p>
                            </div> -->
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="hdrght"> 
                                        <span>                        
                                        <?php
                                        $fill = "fa-heart-o";
                                        if (Session::has('userData')) {
                                                        
                                                $favStoreId = getBuyerFavStores();
                                                if (!empty($favStoreId) && in_array($store_id, $favStoreId)) {
                                                    $fill =  "fa-heart";
                                                }?>
                                                  
                                                    <a href="javascript:void(0);" data-store-id="<?php echo $store_id; ?>"  class="btn btn-primary btn-simple adfv  adtofv-home-store " style="font-size:14px">
                                                        <i style="font-size:16px; position:relative; top:2px" class="fa {{$fill}}" aria-hidden="true"></i> &nbsp; Follow Shop
                                                    </a>
                                                    
                                               
                                                <?php }else
                                                { 
                                                    $currenturl = url()->full(); ?>
                                                    <a class="fav-login login-popup btn btn-primary btn-simple favbtn"                                                         data-location="{{$currenturl}}"
                                                        href="javascript:void(0)" data-toggle="modal" data-target="#login-registerpopup" style="font-size:14px">
                                                            <i style="font-size:16px; position:relative; top:2px" class="fa fa-heart-o" aria-hidden="true"></i> &nbsp; Follow Shop
                                                        </a>
                                                        
                                          <?php }
                                          if(!empty($storeinfo) && $storeinfo[0]->user_id) {
                                    $userData = getStorebyUserInfo($storeinfo[0]->user_id); 
                                    if(!empty($userData)){
                                        $useremail = $userData->email;
                                    }else{
                                        $useremail ="";
                                    }
                                }
                            ?>
                            &nbsp;
                            <a href="mailto:{{$useremail}}?Subject=Contact%20Seller%20-%20MallShark%20Message" class="btn btn-primary block" style="font-size:14px"><i class="fa fa-comment-o"></i> &nbsp; Contact Seller</a>
                            </span>
                            <div class="hidden">
                                <?php if (!empty($storeinfo) && $storeinfo[0]->user_id == getCurrentUserId()) { ?>
                                    <a class="btn btn-primary" href="<?php echo URL::to('store-dashboard'); ?>">Back to My Dashboard</a>
                                <?php } ?>
                            </div>
                            
                        </div>
                    </div>

                </div>
            </div>
        </div>
        <div class="product-tabs" id="fixme-lab">
            <div class="container">
                <div class="row">
                    <div class="col-md-12 nopad ">
                        <ul class="nav nav-tabs" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link " href="#product-sec">Brand Product</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link faqtab" href="#reviews-sec">Brand Reviews</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="#about-sec">Brand Story</a>
                            </li>
                            <!-- <li class = "nav-item">
                            <a class = "nav-link" href="#supporters">Supporters</a>
                        </li>                             -->
                            <li class="nav-item ">
                                <a class="nav-link" href="#policies-sec">Brand Policies</a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        <div class="ajax-res">
        @include('Store.search-ajax')
        </div>
        <!--product-tabs ends -->
    
        <!-- store average ratings & reviews code ends here by Naveed -->
    </div>
    <div id="loading" class="show-setion" style="display:none;">
        <img id="loading-image" src="{{ asset('assets/images/Loader-Animation.gif') }}" alt="Loading..." />
    </div>
    <script>
    $(document).ready(function () {
    
        var loading = false;
        let pagination_counter = 1;

        $(window).off('scroll').on('scroll', function () {
            var ajax = @json($ajax);
            if(!ajax){
            const scrollPosition = $(window).scrollTop() + $(window).height();
            const triggerPoint = $(document).height() * 0.7; // 70% of total page height

            // Check if we've reached 70% of the page and aren't currently loading
            if (!loading && scrollPosition >= triggerPoint) {
                loading = true; // Prevent multiple requests
                
                if (pagination_counter < maxdata) {
                    pagination_counter++;

                    let url = window.location.href.includes('?')
                        ? `${window.location.href}&page=${pagination_counter}`
                        : `${window.location.href}?page=${pagination_counter}`;
                    url += '&loadmore=true';

                    $.ajax({
                        url: url,
                        method: 'GET',
                        success: function (res) {
                            if (res !== '') {
                                jQuery('#results-box-mbt').append(res);
                                loading = false; // Allow further requests
                            } else {
                                loading = false;
                            }
                        },
                        error: function () {
                            loading = false; // Reset on error
                        }
                    });
                }
            }
        }
    });
});

        // jQuery(document).ready(function() {

        //     if (navigator.geolocation) {
        //         navigator.geolocation.getCurrentPosition(success_browser, fail_browser);
        //     } else {
        //         alert('Your browser did not support location');
        //         jQuery(".location-area").remove();
        //     }
        // });

        function success_browser(position) {

            jQuery("#loc_lat").val(position.coords.latitude);
            jQuery("#loc_lon").val(position.coords.longitude);
        }

        function fail_browser() {
            jQuery(".location-area").remove();
        }
        $(document).on('click', '.HeartAnimation', function() {
            current = $(this);
            var data = {};
            data['product_id'] = $(this).attr('data-product-id');
            sendreq(data, 'favourite', 'add', 'callbackfavourites');
            $(this).toggleClass("animate");

        });

        function callbackfavourites(data) {
            if (data.status == 'success') {
                toastr.options = {
                closeButton: true, // Add close button
                disableTimeOut: true,
                tapToDismiss: true,
                positionClass: 'toast-top-right',
                toastClass: 'toast-icon custom-toast-success'
                };
                toastr.success(data.message);
            } else if (data.status == 'error') {
                toastr.options = {
                closeButton: true, // Add close button
                disableTimeOut: true,
                tapToDismiss: true,
                positionClass: 'toast-top-right',
                toastClass: 'toast-icon custom-toast-success'
                };
                toastr.error(data.message);
            }
        }
        /*
         $(function () {
         // bind change event to select
         $('#sorting_dropdown').on('change', function () {
         var url = $(this).val(); // get selected value
         if (url) { // require a URL
         window.location = url; // redirect
         }
         return false;
         });
         });
         */
        jQuery(document).ready(function() {
            jQuery('.active').closest('.collapse').parent().find('.parent-nave').click();

        });
        jQuery(document).ready(function() {
            jQuery('.parent-active').click();

        });

        $(document).on('click', '.fav-add', function() {
            current = $(this);
            var data = {};
            data['product_id'] = $(this).attr('data-product-id');
            sendreq(data, 'favourite', 'add', 'callbackfavourites');
            $(this).toggleClass("animate");

        });
    </script>
    <!-- js for product search results slider range -->
    <!-- <script src="https://code.jquery.com/jquery-1.12.4.js"></script> -->
    <!-- <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script> -->

    <script>
        $(function() {
            $("#slider-range-min").slider({
                range: "min",
                value: <?php echo isset($_GET["radius"]) ? $_GET["radius"] : 5; ?>,
                min: 1,
                max: 30,
                slide: function(event, ui) {
                    $("#amount").val(ui.value);
                }
            });

        });

        if ($(window).width() < 767) {
            $(".filter-text").click(function() {
                $(".mobile-style-filter").fadeIn();
            });
            $(document).click(function(e) {
                
                if (!$(e.target).hasClass("filter-text") &&
                    $(e.target).parents(".mobile-style-filter").length === 0) {
                        
                    // $(".mobile-style-filter").fadeOut();
                }
            });
            $(".done-filter").click(function() {
                
                $(".mobile-style-filter").fadeOut();
            });
        } else {
            // width more than 768px for PC
        }

        // script forr search results category filter
        $(".sub-sect-cnt").hide();
        $("#sidebar-wrapper .sidebar-nav > li > a.parent-nave").click(function() {
            event.stopPropagation();
            $(".sidebar-nav > li").hide();
            $(this).next().fadeIn();
            $(this).parent().fadeIn();
            $(this).prev().addClass('active-nav-menu');

        });

        $(".go-back-btn-lnk a").click(function() {
            event.stopPropagation();
            $(".sub-sect-cnt").hide();
            var $self = $(this).parentsUntil().find('.sidebar-nav li').fadeIn("");
            $('.sidebar-nav li a').removeClass('active-nav-menu');

        });

        // script forr  search results category menu
        jQuery('.contact-info').hide();

        $(".contactus-btn a").click(function() {
            $(this).parent().hide();
            $(this).parent().next('.contact-info').show();

        });


        $(document).ready(function() {
            $('#menu-toggle').click(function(event) {
                event.stopPropagation();
                $("#sidebar-wrapper").slideToggle("fast");
            });
            $("#sidebar-wrapper").on("click", function(event) {
                event.stopPropagation();
            });
        });

        $(document).on("click", function() {
            $("#sidebar-wrapper").slideUp();
        });

        $(function() {
            $("#search-filters").submit(function() {
                $(this).find(":input[type=number],select,input[type=hidden]").filter(function() {
                    return !this.value;
                }).attr("disabled", "disabled");


                return true; // ensure form still submits
            });
        });

        $(document).on('click', '.notbuy', function() {
            //alert("you cannot by your own product");
            swal("You cannot buy your own product");
        });
        $(document).on('click', '.notinstock', function() {
            alert("product is out of stock");
        });

        $(window).scroll(function() {
            //    if ($(this).scrollTop() > 100) {
            //        $('.fxd-hdbtm').addClass("sticky-br");
            //    } else {
            //        $('.fxd-hdbtm').removeClass("sticky-br");
            //    }
        });

        $(document).ready(function() {
            // Hide the div
            $(".fxd-hdbtm").css("opacity", "0");
            // Show the div after 5s
            $(".fxd-hdbtm").delay(200).css("opacity", "1");


            $('[data-toggle="tooltip"]').tooltip();

        });



        $(window).on("load resize scroll", function(e) {
            var scroll = $(window).scrollTop();
            //    $('#fixme-lab').toggleClass('sticky-fixedpos', scroll >= $('#fixme-lab').offset().top - 80);

        });
        //trigger the scroll
        //$(window).scroll(); //ensure if you're in current position when page is refreshed
        //var sOffset = $("#fixme-lab").offset().top - 80;
        //var shareheight = $("#fixme-lab").height() - 46;
        //var headerHeight = $("div#header").height();
        //$(window).scroll(function () {
        //    var scrollYpos = $(document).scrollTop();
        //    if (scrollYpos > sOffset - shareheight) {
        //        $("#fixme-lab").css({
        //            'top': '80px',
        //            'position': 'sticky',
        //            'background': 'white',
        //            'width': '100%',
        //            'z-index': '999'
        //        });
        //    } else {
        //        $("#fixme-lab").css({
        //            'position': 'relative',
        //            'top': 'auto'
        //        });
        //    }
        //});

        $(window).on("load resize scroll", function(e) {
            var scroll = $(window).scrollTop();
            $('#fixme-lab').toggleClass('sticky-fixedpos', scroll >= $('#fixme-lab').offset().top - 80);
        });
        //trigger the scroll
        $(window).scroll(); //ensure if you're in current position when page is refreshed
        var sOffset = $("#fixme-lab").offset().top;
        //var shareheight = $("#fixme-lab").height();
        var scrollYpos;
        scrollYpos = $(document).scrollTop();
        $(window).scroll(function() {
            scrollYpos = $(document).scrollTop();
            if (scrollYpos > sOffset) {

                $("#fixme-lab").css({
                    'position': 'sticky',
                    'background': 'white',
                    'width': '100%'
                });
            } else {
                $("#fixme-lab").css({});
            }
        });
        $(document).ready(function() {
            $('html, body').animate({
                scrollTop: $(window).height() - 27000
            }, 500, function() {

                //window.location.hash = hash;
                //scrollTop: $(hash).offset().top -130
            });
        });

        $(document).ready(function() {
            $('body').scrollspy({
                target: ".nav-tabs",
                offset: 130
            });
            $(".product-tabs li  a[href^='#']").on('click', function(e) {
                // prevent default anchor click behavior
                e.preventDefault();
                // store hash
                var hash = this.hash;
                // animate
                $('html, body').animate({
                    scrollTop: $(hash).offset().top - 123
                }, 600, function() {

                    //window.location.hash = hash;
                    scrollTop: $(hash).offset().top - 123
                });
            });
        });




        function icon_purchase(obj) {
            var current_actions = jQuery(obj).parent();
            var seller_herd_id = jQuery(obj).data('herd-id');
            var data = {};
            data['seller_herd_id'] = seller_herd_id;
            jQuery.ajax({
                url: '/icon-purchase',
                method: 'POST',
                type: 'POST',
                data: data,
                success: function(res) {
                    if (res.status == 'success') {
                        /*
                         jQuery(current_actions).html('');
                         if (obj.user_wallet)
                         jQuery('nav.icnbar ul li.nav-item div.wallet-icon-header p span.pwr').html(obj.user_wallet);
                         if (res.herd_details) {
                         var current_row = jQuery(current_actions).parent().parent().parent().parent();
                         jQuery(current_row).find('.anml .pwr').html(res.herd_details.icon_price);
                         jQuery(current_row).find('.icon_details_last_increase').html(res.herd_details.perc_inc_dec + '%');
                         if (res.herd_details.herdInfo.lastTransaction)
                         jQuery(current_row).find('.icon_details_last_transaction').html(res.herd_details.herdInfo.lastTransaction + 'ZIT');
                         else
                         jQuery(current_row).find('.icon_details_last_transaction').html('0ZIT');
                         jQuery(current_row).find('.icon_details_icon_debt').html(res.herd_details.icon_debt);
                         jQuery(current_row).find('.icon_details_total_earning').html(res.herd_details.total_earning + 'ZIT');
                         jQuery(current_row).find('.icon_details_in_herd').html(res.herd_details.herdInfo.inHerd);
                         jQuery(current_row).find('.icon_details_part_of_herd').html(res.herd_details.herdInfo.partOfHerd);
                         }
                         */
                        swal("", res.message, "success");
                        setTimeout(function() {
                            location.reload();
                        }, 1000);
                    } else {
                        if (res.status == 'error') {
                            // jQuery(current_actions).html('');
                            swal("", res.message, "error");
                        }
                    }
                }
            });
        }
        jQuery(document).ready(function() {
            jQuery('.icon-purchase').on('click', function(event) {
                total_to_pay = jQuery(this).attr('data-total');
                that = this;
                swal({
                        title: "Are you sure?",
                        text: "Your wallet will be debited by " + total_to_pay + " USDC",
                        type: "warning",
                        showCancelButton: true,
                        confirmButtonClass: "btn-success",
                        confirmButtonText: "Yes, Do it!",
                        closeOnConfirm: false
                    },
                    function() {
                        icon_purchase(that);


                    });

            });
        });

        var pledging = 'no';
        jQuery(document).on('click', '#pledge_campaign', function(e) {
            if (pledging == 'yes')
                return;
            var campaign_id = jQuery(this).data('campaign-id');
            var pledge_amount = parseInt(jQuery('#campaign_pledge').val());
            if (!pledge_amount || pledge_amount < 1) {
                jQuery('#campaign_pledge').focus();
                swal("Please enter numeric value to pledge for support");
                //alert('Please enter numeric value to pledge for support');
                return;
            }
            var data = {};
            pledging = 'yes';
            data['campaign_id'] = campaign_id;
            data['pledge_amount'] = pledge_amount;
            jQuery.ajax({
                'url': '/product/pledge-support',
                'Type': 'POST',
                'method': 'POST',
                'data': data,
                'success': function(res) {
                    obj = JSON.parse(res);
                    pledging = 'no';
                    if (obj.status == 'success') {
                        jQuery('#campaign_pledge').val('1');
                        jQuery('nav.icnbar ul li.nav-item div.wallet-icon-header p span.pwr').html(obj.user_wallet);
                        swal("", obj.message, "success");
                        //alert(obj.message);
                        //jQuery('.response-div').html('<div class="alert alert-success">' + obj.message + '</div>');
                    }
                    if (obj.status == 'error') {
                        swal("", obj.message, "error");
                        //jQuery('.response-div').html('<div class="alert alert-danger">' + obj.message + '</div>');
                    }
                }
            });
        });
        $('[data-toggle="tooltip"]').tooltip();

        function scrolltodiv() {
            $('html, body').animate({
                scrollTop: $('#about-sec').offset().top - 122
            }, 500, 'linear');

        }

        // $(document).on('click', '.txtblu', function(e) {
        //    e.preventDefault(e);
        
        //    if(window.screen.width <= 800){
        //     document.body.scrollTop = 2680;
        //    document.documentElement.scrollTop = 2780;
        //    }
        //    else{
        //     document.body.scrollTop = 1650;
        //    document.documentElement.scrollTop = 1650;
        //    }
          
        // });
        $(document).on('click', '.txtblu', function(e) {
            e.preventDefault(); // Prevent default anchor behavior

            // Smooth scrolling to the reviews section
            $('html, body').animate({
                scrollTop: $('#reviews-sec').offset().top
            }, 1000); // Scroll duration in milliseconds
        });

        

        
    </script>
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const userAgent = navigator.userAgent;
        if (/Safari/.test(userAgent) && !/Chrome/.test(userAgent)) {
           const sortingContainer = document.querySelector("#search-filters > div.apply-filter-btn.mt-5.hide-mobile767 > div");
           if (sortingContainer) {
                sortingContainer.style.setProperty('margin-top', '40px', 'important'); // Adjust this value as needed
            }
        }
    });
</script>

    <style>
        .sidebar-filter .nav-link[data-toggle]:not(.collapsed):before {
            font-family: 'Material Icons';
            content: "remove";
        }

        .page-search-results .sidebar-filter .nav-link[data-toggle].collapsed:before {
            font-family: 'Material Icons';
            content: "add";
        }

        .page-search-results .sidebar-filter .nav-link {
            font-size: 14px;
            padding-right: 0;
            padding: .1rem;
        }

        .sidebar-filter .nav-link {
            padding-left: 0;
        }

        .sidebar-filter .bros-heading {
            margin-bottom: 15px;
        }

        .right-desption-two .gray-text,
        .gray-text {
            display: block;
            color: #a5a5a5;
            font-size: 12px;
            line-height: 14px;
        }

        body .fnt18 {
            font-size: 16px;
            margin-top: 5px;
        }

        .broser-cate-cion {
            display: inline-block;
            position: relative;
            top: 5px;
        }

        .rslts .col-md-3 {
            -ms-flex: 0 0 25%;
            flex: 0 0 25%;
            max-width: 25%;
        }

        .rslts .col-md-9 {
            -ms-flex: 0 0 75%;
            flex: 0 0 75%;
            max-width: 75%;
        }

        .sidebar-filter .sidebar-content {
            box-shadow: 0 0 5px 0 #64646459;
            padding: 20px;
            background: #ffffff;
        }

        .price_filter .form-control {
            height: 30px;
            font-size: 14px;
        }

        .filter-bar .ui-slider-horizontal {
            height: .3em;
            border-left: 0;
            border-right: 0;
            border-radius: 0px;
            border: 1px solid #c5c5c5;
        }

        .km-shower input {
            text-align: center;
            font-size: 14px;
            padding-top: 5px;
            padding-bottom: 5px;
            background: transparent;
            color: #495057;
        }

        .range-outer {
            border: 1px solid #ccc;
            border-top: 0;
        }

        .filter-bar .ui-widget-header {
            background: #195dfa;
        }

        .filter-bar .ui-slider-horizontal .ui-slider-handle {
            top: -.6em;
            margin-left: -.6em;
        }

        .price_filter .apply-filter-btn a {

            text-align: center;

            font-size: 14px;
            display: block;
        }

        .filter-bar .ui-state-default,
        .filter-bar .ui-widget-content .ui-state-default,
        .filter-bar .ui-widget-header .ui-state-default,
        .filter-bar .ui-button,
        html .filter-bar .ui-button.ui-state-disabled:hover,
        html .filter-bar .ui-button.ui-state-disabled:active {
            border-radius: 100%;
            outline: none;
        }

        .apply-filter-btn {
            position: relative;
        }

        a.nav-link.no-childs {
            margin-left: 16px;
        }

        .policies-inline {
            position: relative;
            float: left;
            margin-right: 60px;
        }

        #about-sec .mx-wdh750.container img {
            margin: auto;
            text-align: center;
            display: block;
        }

    </style>
    <div class="uploadImages slts-ldmore-loader store-search-loader" style="display: none;">
        <span></span>
    </div>
</div>
    @endsection
