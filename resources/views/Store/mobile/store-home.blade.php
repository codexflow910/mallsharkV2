<?php
$aboutus = isset($storeinfo[0]->about_us) ? $storeinfo[0]->about_us : '';
$street_address = isset($storeinfo[0]->street_address) ? $storeinfo[0]->street_address : '';
$country_id = isset($storeinfo[0]->store_country) ? $storeinfo[0]->store_country : '';
$store_state = isset($storeinfo[0]->store_state) ? $storeinfo[0]->store_state : '';
$zip_code = isset($storeinfo[0]->zip_code) ? $storeinfo[0]->zip_code : '';
$store_name = isset($storeinfo[0]->store_name) ? $storeinfo[0]->store_name : '';
$contact_email = isset($storeinfo[0]->contact_email) ? $storeinfo[0]->contact_email : '';
$contact_info = isset($storeinfo[0]->contact_info) ? $storeinfo[0]->contact_info : '';
$store_logo = isset($storeinfo[0]->store_logo) ? $storeinfo[0]->store_logo : '';
$store_banner1 = isset($storeinfo[0]->image_id1) ? $storeinfo[0]->image_id1 : '';
$store_banner2 = isset($storeinfo[0]->image_id2) ? $storeinfo[0]->image_id2 : '';
$store_banner3 = isset($storeinfo[0]->image_id3) ? $storeinfo[0]->image_id3 : '';
$total_sales = isset($total_sales) ? $total_sales : '0';
$total_items = isset($total_items) ? $total_items : '0';
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
// For get brand categories in meta description and keyword seo 
$getProductTitle = array();
foreach($productResults['records'] as $productcate){
    $getProductTitle[]= $productcate['_source']['title'];
    $cat = getCategoriesByProductID($productcate['_source']['product_id']);
    foreach($cat as $key=>$item){

        $catList[] = $item['category_id'];
    }
    }
    $unique_array = array_unique($catList);
    $getcategories = array();
    foreach($unique_array as $detail){
        $getcategories[] =getCategoriesNameByProductID($detail);
    }
    $getstoreCategories = array();
    foreach($getcategories as $brandcat){
        $getstoreCategories[] = isset($brandcat[0]['cat_info']['title']) ? $brandcat[0]['cat_info']['title']: "";
    }
 
    if(isset($getstoreCategories[1])){
        $getbrandcategory1  = $getstoreCategories[1];
    }else{
        $getbrandcategory1 = '';
    }

    if(isset($getstoreCategories[2])){
        $getbrandcategory2  = $getstoreCategories[2];
    }else{
        $getbrandcategory2 = '';
    }

    if(count($getstoreCategories) >= 1){
      $description = "Shop for the latest $store_name, $getstoreCategories[0], $getbrandcategory1 , $getbrandcategory2 etc. 
       Discover bargains and the latest $store_name products online with Mallshark, Crypto powered Ecommerce.";
    }

    $title = "$store_name: Mallshark - Crypto Enabled Shopping portal";
    if(isset($getProductTitle[1])){
        $productTitleseo = $getProductTitle[1];
    }else{
        $productTitleseo = '';
    }
    $keyword = "$store_name, $store_name $getProductTitle[0], $store_name $productTitleseo";
?>
@extends('layouts.mobile-main')
@section('content')
@section('title', $title)
@section('description', $description)
@section('keyword', $keyword)
<link href="https://fonts.googleapis.com/icon?family=Material+Icons"
      rel="stylesheet">
<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
<style>
    .search-icon-header {
        display: inline-block;
    }
    @media only screen and (min-width: 320px) and (max-width: 767px) {
        #menu-toggle {
            display: inline-block;
        }
    }
</style>

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
//die();
?>

<?php
$llCategoreis = getAllCategories();
?>

<!-- /#sidebar-wrapper -->



<script src="{{ URL::asset('assets/js/mobilecart.js',env('HTTPS_STATUS')) }}"></script>
<h1 style="display: none;">{{$store_name}}</h1>
<section class="page-search-results">




    <div class="strhds">

        <div class="container-fluid">
            <div class="row" style="display:none;">
                <div class="col hdlft">
                    <span>
                        <i class="fa fa-star" aria-hidden="true"></i>
                        <i class="fa fa-star" aria-hidden="true"></i>
                        <i class="fa fa-star" aria-hidden="true"></i>
                        <i class="fa fa-star" aria-hidden="true"></i>
                        <i class="fa fa-star-half-o" aria-hidden="true"></i>
                        <b>445</b> Ratings</span>
                    <br>
                    <span class="small">{{$total_sales}} Sales    &nbsp;| &nbsp;   Since 2016    &nbsp;| &nbsp;   {{$total_items}} Items</span>

                </div>
                <div class="col strlgo hdcnt"><a href="#"><img src="{{ $logourl }}" alt="{{$store_name}}" title="{{$store_name}}"></a></div>      
                <div class="col hdrght">
                    <button class="btn btn-primary btn-simple">Follow Us</button>
                    &nbsp; &nbsp;
                    <button class="btn btn-primary">Become Ambassador</button> 
                </div>      
            </div>
        </div>

    </div>


    <?php
    $url_img = asset('/assets/images/banner-str.jpg');
    if ($store_banner1 != '') {

        $url_img = getAttachmentUrlById((int) $store_banner1, 'media');
    }
    $url_img2 = asset('/assets/images/banner-str.jpg');
    if ($store_banner2 != '') {

        $url_img2 = getAttachmentUrlById((int) $store_banner2, 'media');
    }
    $url_img3 = asset('/assets/images/banner-str.jpg');
    if ($store_banner3 != '') {

        $url_img3 = getAttachmentUrlById((int) $store_banner3, 'media');
    }
    $iconUrl = '';
    if (!empty($storeinfo[0]->store_logo)) {
        $iconUrl = getAttachmentUrlById((int) $storeinfo[0]->store_logo, 'small');
    }
    ?>

    <div class="container-fluide rslts ">




        <div class="row search-reults-head mt-20 mb-10 hide-mobile767">

            <div class="col-12">
                <div class="row strhdm">
                    <div class="col-4">
                        <?php if (!empty($iconUrl)) { ?>
                            <img class="" src="{{$iconUrl}}" alt="{{$store_name}}" title="{{$store_name}}">
                        <?php } ?>
                    </div>
                    <div class="col-8 text-right" style="display:none;">

                        <div class="dropdown" style="display:inline-block; float:right; margin-left:10px">
                            <button type="button" class="btn btn-primary btn-simple dropdown-toggle" data-toggle="dropdown" style=" padding-left: 10px; padding-right:10px;padding-top: 10px;" aria-expanded="false"></button>
                            <div class="dropdown-menu dropdown-menu-right" style="left: 0px; position: absolute; transform: translate3d(30px, 39px, 0px); top: 0px; will-change: transform;" x-placement="bottom-end">
                                <a class="dropdown-item" href="#">Products</a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item" href="#">Reviews </a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item" href="#">About</a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item" href="#">Contact</a>
                                <div class="dropdown-divider"></div>
                                <div class="dropdown-item notacnhor-text" onclick=" event.stopPropagation();">
                                    <span style="font-size:36px">
                                        <i class="fa fa-star" aria-hidden="true"></i>
                                        <i class="fa fa-star" aria-hidden="true"></i>
                                        <i class="fa fa-star" aria-hidden="true"></i>
                                        <i class="fa fa-star" aria-hidden="true"></i>
                                        <i class="fa fa-star-half-o" aria-hidden="true"></i>
                                    </span><br>
                                    <span class="small">6435 Sales &nbsp;|&nbsp; Since 2016 &nbsp;|&nbsp; 45 Items</span>      
                                </div>    
                            </div>
                        </div> 
                    </div>
                </div>
            </div>     



            <div class="col-12">
                <div class=" border fltrhor" id="accordion" role="tablist" >
                    <h2 id="headingOne"  ><a href="javascript:;" class="search-filter-open" >Filters<span class="pull-right small" style="color:#3d3d3d">Show More</span></a></h2>
                    <form action="{{url()->current()}}" method="GET" id="collapseOne"   class=" search-filters full-page-cover-filter" role="tabpanel" aria-labelledby="headingOne" data-parent="#accordion" >    
                       
                        <div class="  d-flex h-50 " style="position:relative;z-index:99">
                            <div class="row justify-content-center align-self-center">
                                <!-- drop down -->
                                <div class="col-md">
                                    <div class="form-group">


                                        <select class="form-control" id="productMainCategory" name="category" style="border-color: rgb(206, 212, 218);">
                                            <option value="">Select Category</option>
                                            <?php
                                            foreach ($llCategoreis as $key => $val) {
                                                $parent_Category_title = isset($val['cat_info']['title']) ? $val['cat_info']['title'] : '';
                                                $parent_Category_slug = isset($val['cat_info']['slug']) ? $val['cat_info']['slug'] : '';
                                                $parent_Category_id = isset($val['cat_info']['id']) ? $val['cat_info']['id'] : '';
                                                $selected = '';
                                                if (isset($_GET['category']) && $_GET['category'] == $parent_Category_slug) {
                                                    $selected = 'selected';
                                                    $slectedid = $parent_Category_id;
                                                }
                                                ?>
                                                <option data-catid="{{$parent_Category_id}}" value="{{$parent_Category_slug}}" {{$selected}}> {{$parent_Category_title}} </option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                </div>
                                <!-- drop down ends -->
                                <!-- drop down -->
                                <?php
                                $childCategories = array();
                                if (isset($_GET['category']) && $_GET['category'] != '') {
                                    $childCategories = getChildCategoriesByParentId($parent_Category_id);
                                }
                                $childCatDisplay = 'none';
                                $childCategorySlug = '';
                                if (isset($_GET['subcategory']) && $_GET['subcategory'] != '') {
                                    $childCatDisplay = 'block';
                                    $childCategorySlug = $_GET['subcategory'];
                                }
                                if (count($childCategories) > 0) {
                                    $childCatDisplay = 'block';
                                }
                                ?>
                                <div class="col-md-4 childcategoryshow" style="display:{{$childCatDisplay}}">
                                    <div class="form-group" id="subcategory" ><select class="form-control" name="subcategory" id="subcategory-res"><option value="">Select Child Category</option>
                                            <?php
                                            if (count($childCategories) > 0) {
                                                $childSelected = '';
                                                foreach ($childCategories as $childcat) {
                                                    if (isset($_GET['subcategory']) && $_GET['subcategory'] == $childcat->category_slug) {
                                                        $childSelected = 'selected';
                                                    }
                                                    echo '<option value="' . $childcat->category_slug . '" ' . $childSelected . '>' . $childcat->categoryTitle . '</option>';
                                                }
                                            }
                                            ?>
                                        </select></div>
                                </div>
                                <!-- drop down ends -->





                                <!-- drop down -->
                                <div class="col-md-4"> 
                                    <div class="row">
                                        <?php
                                        $constants = config('constants.shopzit_gamification');
                                        ?>
                                        <!-- <div class="col-sm-12 mb-15">
                                            <div class="form-group clearfix mb-0">
                                                <label for="text" class="mt-10">USDC Back</label>
                                                <select class="form-control" data-prodcut-id="36" id="cashback" name="cashback">
                                                    <option value="">all</option>       
                                                    //<option value="{{$constants['STANDARD_CASHBACK']}}" <?php
                                                  //  if (isset($_GET["cashback"]) && $_GET["cashback"] == $constants['STANDARD_CASHBACK']) {
                                                   //     echo 'selected';
                                                  //  }
                                                 //   ?>>{{$constants['STANDARD_CASHBACK']}}</option> 
                                                 //   <option value="{{$constants['CHOOSEN_CASHBACK']}}" <?php
                                                 //   if (isset($_GET["cashback"]) && $_GET["cashback"] == $constants['CHOOSEN_CASHBACK']) {
                                                 //       echo 'selected';
                                                   // }
                                                 //   ?>>{{$constants['CHOOSEN_CASHBACK']}}</option>    
                                                </select>
                                            </div>                                            
                                        </div> -->
                                        <?php
                                        $filterData = getFilterData(2);
                                        if ($filterData['filter_status'] == 1) {
                                            ?>
                                            <div class="col-md-4 col-6  ">
                                                <div class="form-group">
                                                    <input type="number"
                                                           value="<?php echo isset($_GET['min_price']) ? $_GET['min_price'] : ''; ?>"
                                                           name="min_price"  class="form-control" placeholder="Min Price">  
                                                </div>
                                            </div>
                                            <div class="col-md-4 col-6 ">
                                                <div class="form-group">

                                                    <input type="number" name="max_price" min="1"
                                                           value="<?php echo isset($_GET['max_price']) ? $_GET['max_price'] : ''; ?>"
                                                           class="form-control" placeholder="Max Price">
                                                </div>
                                            </div>
                                        <?php } ?>
                                        <div class="col-md-4  d-flex text-left" >
                                            <button type="submit" class="btn btn-primary btn-simple btnpd10 text-center btn-block ">
                                                Apply Filter
                                            </button>
                                            <a href="javascript:;" class="cross-search-filter">
                            <span> </span>
                            <i class="material-icons">
                                clear
                            </i>

                        </a>
                                            <br>
                                            <a href="javascript:void(0);" id="resetfilters" class="btn btn-primary btn-simple btnpd10 text-center btn-block" style="margin:auto";>Reset Filter</a>
                                        </div> 
                                    </div>
                                    <!-- drop down ends --> 
                                </div> 
                              
                            </div>
                          
                        </div>  
                    </form>
                </div>                


            </div>

            <div class="col-md-8 col-lg-6">

                <p class="mt-10"><?php if (isset($_GET["keyword"]) && $_GET["keyword"] != '') { ?>Results Found For
                        <span class="search-keywords">“{{$_GET["keyword"]}}�?</span> <?php } ?>                  




                </p>
            </div>
        </div><!-- search-reults-head -->

        <div class="row cdt-bdy">

            <?php if (!empty($productResults) && count($productResults['records']) > 0) { ?>

                <div class="col-12">

                    <div class="results-box-mbt row">

                        <?php
                        $favProductId = getBuyerFavProducts();
                        foreach ($productResults['records'] as $pr) {
                            ?>
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

                            <div class="col-6 col-xl-4  col-lg-4 col-md-6 col-sm-6">
                                <div class="card">
                                    <div class="add-to-fav">
                                        <?php
                                        $url = URL::to('/product/' . $pr['_source']['slug']);
                                        /*
                                          if ($pr['_source']['listing_type'] == 'post') {
                                          $url = URL::to('/post-detail/' . $pr['_source']['slug']);
                                          } else {
                                          $url = URL::to('/product/' . $pr['_source']['slug']);
                                          }
                                         */
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

                                            <a class="fav-login login-popup" data-location="{{url()->current()}}"
                                               href="javascript:void(0)" data-toggle="modal" data-target="#login-registerpopup">
                                                <span>
                                                    <i class="fa fa-heart-o" aria-hidden="true"></i>
                                                </span>

                                            </a>
                                        <?php } ?>
                                    </div><!-- add-to-fav -->

                                    <?php if (isset($pr['_source']['is_feature']) && $pr['_source']['is_feature'] == 1) {
                                        ?>
                                        <div class="feature-product-sec">
                                            <span class="badge badge-primary">featured</span>
                                        </div>
                                        <?php
                                    }
                                    ?>
                                    <?php $imageUrl = getAttachmentUrlById((int) $pr['_source']['feature_image'], 'medium'); ?>
                                    <a href="{{$url}}" class="image-container-mbt"> <img src="{{$imageUrl}}" alt="<?php echo $pr['_source']['title']; ?>" title="{{$pr['_source']['title']}}" style="width:100%"> </a>

                                    <div class="card-body" data-stock="10">


                                        <h3 class="card-title"><a
                                                href="<?php echo $url; ?>"><?php echo $pr['_source']['title']; ?></a>
                                        </h3>
                                        <div class="cartsec">
                                            <!-- show-quantitiy-panel-->
                                            <div class="crtbtn col  buttons-group-cnt <?php echo $class; ?>">
                                                <?php
                                              //  echo 'Data: <pre>' .print_r($pr['_source']['product_type'],true). '</pre>'; 
                                                
                                                // if ($pr['_source']['product_type'] == 'variant' || $pr['_source']['weight'] > 30) { 
                                                 ?>
                                                    <a <?php echo $storeDataId; ?>  href="<?php echo URL::to('product/' . $pr['_source']['slug']); ?>"
                                                                                    class="primary-btn btn btn-primary btnorm">Add To Cart</a>
                                                                       
                       

                                            </div>

                                            <div class="prc col text-right"><span class="throg small"> USDC </span><br><span class="zit-price"> USDC {{number_format($pr['_source']['price'],2)}} </span></div>
                                        </div>
                                        <div class="progress"><div class="progress-bar" style="width:70%"></div></div>
                                        <!-- Progress bar ends -->
                                        <div class="infbx small  justify-content-between">
                                            <!-- <div>
                                                <span>Supporters: <b>0</b></span><br>
                                                <span>Gave Back:<b> $78896</b></span><br>

                                            </div>  -->

                                            <div class="text-left">
                                                <span>
                                                    <div class="rating-strip">
                                                        <i class="fa fa-star-o" aria-hidden="true"></i>
                                                        <i class="fa fa-star-o" aria-hidden="true"></i>
                                                        <i class="fa fa-star-o" aria-hidden="true"></i>
                                                        <i class="fa fa-star-o" aria-hidden="true"></i>
                                                        <i class="fa fa-star-o" aria-hidden="true"></i>
                                                    </div>


                                                    <span><a href="javascript:void(0);">{{$store_name}}</a></span> 
                                            </div>
                                        </div>  







                                    </div><!-- box-description-mbt -->
                                </div>
                            </div>
                        <?php } ?>
                    </div><!-- results-box-mbt -->
                </div>
            <?php } else { ?>
                <div class="col-sm-9">

                    No record found
                </div>
            <?php } ?>
        </div>


    </div>

</section>
<script>
    jQuery(document).ready(function () {

        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(success_browser, fail_browser);
        } else {
            alert('Your browser did not support location');
            jQuery(".location-area").remove();
        }
    });
    function success_browser(position) {

        jQuery("#loc_lat").val(position.coords.latitude);
        jQuery("#loc_lon").val(position.coords.longitude);
    }

    function fail_browser() {
        jQuery(".location-area").remove();
    }
    $(document).on('click', '.HeartAnimation', function () {
        current = $(this);
        var data = {};
        data['product_id'] = $(this).attr('data-product-id');
        sendreq(data, 'favourite', 'add', 'callbackfavourites');
        $(this).toggleClass("animate");

    });
    function callbackfavourites(data) {

    }
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
    jQuery(document).ready(function () {
        jQuery('.active').closest('.collapse').parent().find('.parent-nave').click();

    });
    jQuery(document).ready(function () {
        jQuery('.parent-active').click();

    });
</script>
<!-- js for product search results slider range -->
<script src="https://code.jquery.com/jquery-1.12.4.js"></script>
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
<script>
    $(function () {
        $("#slider-range-min").slider({
            range: "min",
            value: <?php echo isset($_GET["radius"]) ? $_GET["radius"] : 5; ?>,
            min: 1,
            max: 30,
            slide: function (event, ui) {
                $("#amount").val(ui.value);
            }
        });

    });


    if ($(window).width() < 767) {

        $(".filter-text").click(function () {
            $(".mobile-style-filter").fadeIn();
        });

        $(document).click(function (e) {
            if (!$(e.target).hasClass("filter-text")
                    && $(e.target).parents(".mobile-style-filter").length === 0) {
                $(".mobile-style-filter").fadeOut();
            }
        });

        $(".done-filter").click(function () {
            $(".mobile-style-filter").fadeOut();
        });


    } else {
        // width more than 768px for PC
    }


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

    $(document).on('click', 'a.search-filter-open', function () {
        $(this).parent().parent().addClass("open-search-filter");
        $("#collapseOne").fadeIn();
    });

    $(document).on('click', '.cross-search-filter,form#collapseOne button[type="submit"]', function () {
        $(this).parent().parent().removeClass("open-search-filter");
        $("#collapseOne").fadeOut('slow');
    });

    $(function () {
        //caches a jQuery object containing the body element
        var body = $("body");
        $(window).scroll(function () {
            var scroll = $(window).scrollTop();

            if (scroll >= 1) {
                body.removeClass('no-scroll').addClass("scroll-page-down");
            } else {
                body.removeClass("scroll-page-down").addClass('no-scroll');
            }
        });
    });
    jQuery(document).on('change', '#productMainCategory', function () {
        //parentCategoryID=jQuery(this).attr('data-catid');
        parentCategoryID = jQuery(this).find(':selected').attr('data-catid');
        if (parentCategoryID != '') {
            jQuery.ajax({
                url: "{{URL::to('/getchlidcats')}}",
                method: 'POST',
                type: 'POST',
                data: 'parentCategoryID=' + parentCategoryID,
                success: function (res) {
                    if (res != '') {
                        jQuery('.childcategoryshow').show();
                        jQuery('#subcategory-res').html(res);
                    }
                    if (res == '') {

                        jQuery('#subcategory-res').html('');
                    }
                }

            });
        }

    });


</script>


<style>

    .sidebar-filter .nav-link[data-toggle]:not(.collapsed):before {
        font-family: 'Material Icons';
        content: "remove";
    }.page-search-results .sidebar-filter .nav-link[data-toggle].collapsed:before {
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

    .right-desption-two .gray-text, .gray-text {
        display: block;
        color: #a5a5a5;
        font-size: 12px;
        line-height: 14px;
    }
    body .fnt18 {
        font-size: 16px;
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

        text-align:center;

        font-size: 14px;
        display: block;
    }
    .filter-bar .ui-state-default, .filter-bar .ui-widget-content .ui-state-default, .filter-bar .ui-widget-header .ui-state-default, .filter-bar .ui-button, html .filter-bar .ui-button.ui-state-disabled:hover, html .filter-bar .ui-button.ui-state-disabled:active {
        border-radius: 100%;
        outline: none;
    }
    .apply-filter-btn {
        position: relative;
    }
    a.nav-link.no-childs {
        margin-left: 16px;
    }
</style>
@endsection




