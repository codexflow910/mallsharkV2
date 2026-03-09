@extends('layouts.mobile-main')
@section('content')
@section('title', 'Product Results')
@section('description', 'Product Results')
@section('meta')
@include('layouts.searchresults-meta')
@stop

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
// print_rr($llCategoreis);
//die();

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

<?php
$llCategoreis = getAllCategories();
?>

<!-- /#sidebar-wrapper -->



<main> 

    <!-- Most Popular Starts -->
    <div class="container-fluide rslts fixes-ipd-dsn">
        <div class="row">
            <!-- Filter Box Starts -->
            <div class="col md-12"> 
                <div class=" border fltrhor" id="accordion" role="tablist">
                    <h5 id="headingOne"  ><a href="javascript:;" class="search-filter-open" >Search Filter<span class="pull-right small" style="color:#3d3d3d">Show More</span></a></h5>
                    <form action=" {{ route('home.search') }}" method="GET" id="collapseOne"   class=" search-filters full-page-cover-filter" role="tabpanel" aria-labelledby="headingOne" data-parent="#accordion" >    
                       
                        <div class="container d-flex pt-3">
                            <div class="row justify-content-center align-self-center">
                                <!-- drop down -->
                                <div class="col-md">
                                    <div class="form-group">


                                        <select class="form-control" id="productMainCategory" name="category" style="border-color: rgb(206, 212, 218);">
                                            <option value="0">Select Category</option>
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
                                    $slectedid = $parent_Category_id;
                                    $childCategories = getChildCategoriesByParentId($slectedid);
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
                                    <div class="form-group" id="subcategory" >
                                        <select class="form-control" name="subcategory" id="subcategory-res"><option value="">Select Child Category</option>
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
                                        </select>
                                    </div>
                                </div>
                                <!-- drop down ends -->

                                <!-- drop down -->
                                <div class="col-md-4 fll-ipd"> 
                                    <div class="row">
                                        <?php
                                        $constants = config('constants.shopzit_gamification');
                                        ?>
                                        <!-- <div class="col-sm-12 mb-15"> -->
                                            <!-- <div class="form-group clearfix mb-0">
                                                <label for="text" class="mt-10">USDC Back</label>
                                                <select class="form-control" data-prodcut-id="36" id="cashback" name="cashback">
                                                    <option value="">all</option>       
                                                   // <option value="{{$constants['STANDARD_CASHBACK']}}" <?php
                                                  //  if (isset($_GET["cashback"]) && $_GET["cashback"] == $constants['STANDARD_CASHBACK']) {
                                                      //  echo 'selected';
                                                   // }
                                                   // ?>>{{$constants['STANDARD_CASHBACK']}}</option> 
                                                   // <option value="{{$constants['CHOOSEN_CASHBACK']}}" <?php
                                                   // if (isset($_GET["cashback"]) && $_GET["cashback"] == $constants['CHOOSEN_CASHBACK']) {
                                                 //       echo 'selected';
                                                 //   }
                                                 //   ?>>{{$constants['CHOOSEN_CASHBACK']}}</option>    
                                                </select>
                                            </div>                                            
                                        </div> -->
                                        <?php
                                        $filterData = getFilterData(2);
                                        if ($filterData['filter_status'] == 1) {
                                            ?>
                                            <div class="col-md-4 col-6  div-jp-lftone">
                                                <div class="form-group">
                                                    <input type="number"
                                                           value="<?php echo isset($_GET['min_price']) ? $_GET['min_price'] : ''; ?>"
                                                           name="min_price" min="0" class="form-control" placeholder="Min Price">  
                                                </div>
                                            </div>
                                            <div class="col-md-4 col-6 div-jp-lfttwo">
                                                <div class="form-group">

                                                    <input type="number" name="max_price" min="1"
                                                           value="<?php echo isset($_GET['max_price']) ? $_GET['max_price'] : ''; ?>"
                                                           class="form-control" placeholder="Max Price">
                                                </div>
                                            </div>
                                        <?php } ?>
                                        <div class="col-md-4 d-flex  text-left div-jp-lftthree" >
                                            <button type="submit" class="btn btn-primary btn-simple btnpd10 text-center mr-2  "  >
                                                Apply Filter
                                            </button>
                                            <br>
                                            <a href=" {{ route('home.search') }}" class="text-center rest-ipd-btn btn btn-primary btn-simple btnpd10 text" style="">Reset Filter</a>
                                            <a href="javascript:;" class="cross-search-filter">
                            <span> </span>
                            <i class="material-icons">
                                clear
                            </i>
                           
                        </a>
                                        </div> 
                                    </div>
                                    <!-- drop down ends --> 
                                </div> 
                            </div>
                        </div>  

                    </form>
                </div> 

                <!-- Filter Box Ends -->

                <!-- Products Section Starts -->
                <div class="row">
                    <?php
                    if (!empty($productResults['records']) && count($productResults['records']) > 0) { 
                        $favProductId = getBuyerFavProducts();
                        $userId = !empty(Session::get('userData')['user_id'])?Session::get('userData')['user_id']:'';
                        $totalSupporters = 0;
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
                            } else {
                                $storeDataId = "data-store-id=2";
                            }
                            $store_info = getStoreInfoById((int) $pr['_source']['store_id']);
                            $store_icon = isset($store_info[0]->store_icon) ? $store_info[0]->store_icon : '';
                            $image_id = getImageIdByIconId((int) $store_icon);
                            $iconUrl = getAttachmentUrlById((int) $image_id, 'icons');
                            ?>

                            <div class="col-xl-3 col-lg-4 col-md-6 col-sm-6 ">
                                <div class="card">
                                    <div class="add-to-fav">
                                        <?php
                                        /*
                                          if ($pr['_source']['listing_type'] == 'post') {
                                          $url = URL::to('/post-detail/' . $pr['_source']['slug']);
                                          } else {
                                          $url = URL::to('/product/' . $pr['_source']['slug']);
                                          }
                                         * 
                                         */
                                        $url = URL::to('/product/' . $pr['_source']['slug']);
                                        if (Session::has('userData')) {
                                            if (!empty($favProductId) && in_array($pr['_source']['product_id'], $favProductId)) {
                                                $fill = "animate heart-addtofav";
                                            } else {
                                                $fill = "";
                                            }
                                            ?>
                                            <div class="heart-addtofav fav-add <?php echo $fill; ?>"
                                                 data-product-id="<?php echo $pr['_source']['product_id']; ?>"></div>
                                             <?php } else { ?>

                                            <a class="login-popup favlogin" data-location="{{URL::to('q/')}}"
                                               href="javascript:void(0)" data-toggle="modal" data-target="#login-registerpopup">
                                                <div class="heart-addtofav no-animate"></div>
                                            </a>
                                        <?php } ?>
                                    </div><!-- add-to-fav -->

                                    <div class="dropdown show animal-dropdown">
                                        <a class=" dropdown-toggle" href="#" role="button" id="dropdownMenuLink" aria-expanded="false">
                                            <span class="anml anmls"> 
                                                <img class="img-fluid" src="{{ $iconUrl }}" alt="{{$store_info[0]->store_name}}" title="{{$store_info[0]->store_name}}">
                                            </span> 
                                        </a>
                                    </div>



                                    <?php if (isset($pr['_source']['is_feature']) && $pr['_source']['is_feature'] == 1) {
                                        ?>
                                        <div class="feature-product-sec" style="display:none">
                                            <span>featured</span>
                                        </div>
                                        <?php
                                    }
                                    ?>
                                    <?php $imageUrl = getAttachmentUrlById((int) $pr['_source']['feature_image'], 'medium'); ?>
                                    <a href="{{$url}}" class="image-container-mbt"> <img src="{{$imageUrl}}" alt="{{$pr['_source']['title']}}" title="{{$pr['_source']['title']}}" style="width:100%"> </a>

                                    <div class="card-body" data-stock="<?php
                                    if ($pr['_source']['current_stock'] >= 10) {
                                        echo 10;
                                    } else {
                                        echo $pr['_source']['current_stock'];
                                    }
                                    ?>">


                                        <h5 class="card-title"><a
                                                href="<?php echo $url; ?>"><?php echo $pr['_source']['title']; ?></a>
                                        </h5>
                                        <div class="cartsec"  style="justify-content:space-between">
                                            <!--                 
                                            show-quantitiy-panel-->
                                            <?php
                                            if (Session::has('userData')) {
                                                ?>
                                                <div class="crtbtn col  buttons-group-cnt <?php echo $class; ?>">
                                                    <?php if ($pr['_source']['product_type'] == 'variant') { 
                                                       
                                                        ?>
                                                        <a <?php echo $storeDataId; ?>  href="<?php echo URL::to('product/' . $pr['_source']['slug']); ?>"
                                                                                        class="primary-btn btn btn-primary btnorm">Add To Cart</a>
                                                        <?php  }else if ($pr['_source']['product_type'] == 'virtual') { ?>
                                                            <button type="button"
                                                                <?php echo $storeDataId; ?> data-product-id="<?php echo $pr['_source']['product_id'];  ?>"
                                                                class="primary-btn btn btn-primary btnorm cartadded">Add To Cart 
                                                        </button>
                                                      <?php  }else if (Session::has('userData') && $userId == $pr['_source']['user_id']) {
                                                             
                                                            ?>
                                                        <button type="button"class="primary-btn btn btn-primary btnorm notbuy">Add To Cart</button> 
                                                        <?php } else if ($pr['_source']['current_stock'] <= 0) { ?>
                                                          <button type="button"class="primary-btn btn btn-primary btnorm notinstock">Out of stock</button> 
                                                    <?php } else {
                                                        //die('callde');
                                                        ?>
                                                        <button type="button"
                                                                <?php echo $storeDataId; ?> data-product-id="<?php echo $pr['_source']['product_id'];  ?>"
                                                                class="primary-btn btn btn-primary btnorm cartadd cartadded">Add To Cart 
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
                                            <?php } else { ?>
                                                <div class="crtbtn spacing-side-adj">
                                                    <a class="fav-login primary-btn btn btn-primary btnorm" data-location="{{URL::to('q/')}}"
                                                       href="javascript:void(0)" data-toggle="modal" data-target="#login-registerpopup">Add To Cart</a>
                                                </div>

                                            <?php } ?>
                                            <div class="prc text-right"><!--<span class="throg small"> Z2 </span>--><br><span class="zit-price"> $ {{number_format($pr['_source']['price'],2)}} </span></div>
                                        </div>
                                        <!-- <div class="progress"><div class="progress-bar" style="width:70%"></div></div> -->
                                        <!-- Progress bar ends -->
                                        <div class="infbx small  justify-content-between">
                                            <div>
                                                <?php
                                                $store_name = isset($store_info[0]->store_name) ? $store_info[0]->store_name : '';
                                                $store_slug = isset($store_info[0]->store_slug) ? $store_info[0]->store_slug : '';
                                                ?>
                                                <span><a href="{{URL::to('/brand/'.$store_slug)}}"><?php echo $store_name; ?></a></span> 
                                                <!-- <span><a href="{{URL::to('/store-home /'.$store_slug)}}"><?php echo $store_name; ?></a></span>  -->

                                            </div> 

                                            <div class="">
                                                <span>
                                                    <div class="rating-strip">
                                                        <i class="fa fa-star-o" aria-hidden="true"></i>
                                                        <i class="fa fa-star-o" aria-hidden="true"></i>
                                                        <i class="fa fa-star-o" aria-hidden="true"></i>
                                                        <i class="fa fa-star-o" aria-hidden="true"></i>
                                                        <i class="fa fa-star-o" aria-hidden="true"></i>
                                                    </div>

                                                    <?php
                                                    $store_name = isset($store_info[0]->store_name) ? $store_info[0]->store_name : '';
                                                    $store_slug = isset($store_info[0]->store_slug) ? $store_info[0]->store_slug : '';
                                                    ?>

                                            </div>
                                        </div>  







                                    </div><!-- box-description-mbt -->
                                </div>
                            </div>
                        <?php }  ?>
                    </div><!-- results-box-mbt -->

                    <div class="row">
                        <div class="custom-pagination col-sm-12 mt-15 mobile">
                            <?php

                            function remove_url_query($url, $key) {
                                $url = preg_replace('/(?:&|(\?))' . $key . '=[^&]*(?(1)&|)?/i', "$1", $url);
                                $url = rtrim($url, '?');
                                $url = rtrim($url, '&');
                                return $url;
                            }

                            $q = [];
                            if (isset($_GET['status'])) {
                                $q['status'] = $_GET['status'];
                            }
                            if (isset($_GET['order'])) {
                                $q['order'] = $_GET['order'];
                            }
                            // echo 'no';
                            // print_rr($listingresults);
                            //echo $listingresults->appends($q)->links('pagination.defaultpagination');
                            //echo paginate($item_per_page, $page_number, $get_total_rows[0], $total_pages, $page_url);

                            if (isset($productResults['total']['value']) && isset($productResults['records'])) {
                                // echo 'yes';
                                $per_page = 20;
                                $total_records =  (int)$productResults['total']['value'];
                                $max = $total_records / $per_page;
                                $page_number = isset($_GET['page']) ? $_GET['page'] : 1;
                                unset($_GET['page']);
                                $pageurl = Request::fullUrl();

                                // delete edit parameter;
                                $pageurl = remove_url_query($pageurl, 'page');
                                if (!$_GET) {
                                    $pageurl = $pageurl . '?';
                                }
                                if ($productResults['total']['value'] > $per_page) {
                                    echo paginate(1, $page_number, 10, $max, $pageurl);
                                }
                            }
                            ?>

                        </div>

                    <?php } else { ?>
                        <div class="col-sm-9">

                            No record found
                        </div>
                    <?php } ?>


                    <!-- Products Section Ends -->
                </div>

            </div>

        </div>    
    </div> 
    <!-- Most Popular Ends -->











</main>
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
//$(document).on('click', '.HeartAnimation', function () {
//    current = $(this);
//    var data = {};
//    data['product_id'] = $(this).attr('data-product-id');
//    sendreq(data, 'favourite', 'add', 'callbackfavourites');
//    $(this).toggleClass("animate");
//
//});
//function callbackfavourites(data) {
//
//}
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
<script src="{{ URL::asset('assets/js/mobilecart.js',env('HTTPS_STATUS')) }}"></script>
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
    if (document.location.search.length) {

//        jQuery('#headingOne a').click();

    } else {
        // no query string exists
    }
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
    $(".search-filters").submit(function () {
        $(this).find(":input[type=number],select,input[type=hidden]").filter(function () {
            return !this.value;
        }).attr("disabled", "disabled");


        return true; // ensure form still submits
    });
});


//$(document).on('click', '.heart-addtofav', function () {
//  $(this).toggleClass("animate");
//
//});
$(document).on('click', '.fav-add', function () {
    current = $(this);
    var data = {};
    data['product_id'] = $(this).attr('data-product-id');
    sendreq(data, 'favourite', 'add', 'callbackfavourites');
    $(this).toggleClass("animate");

});
function callbackfavourites(data) {

}
$(document).on('click', '.notbuy', function () {
    alert("you cannot by your own product");
});
</script>
<script>//plugin bootstrap minus and plus
//http://jsfiddle.net/laelitenetwork/puJ6G/
    $('.btn-number').click(function (e) {
        e.preventDefault();

        fieldName = $(this).attr('data-field');
        type = $(this).attr('data-type');
        var input = $("input[name='" + fieldName + "']");
        var currentVal = parseInt(input.val());
        if (!isNaN(currentVal)) {
            if (type == 'minus') {

                if (currentVal > input.attr('min')) {
                    input.val(currentVal - 1).change();
                }
                if (parseInt(input.val()) == input.attr('min')) {
                    $(this).attr('disabled', true);
                }

            } else if (type == 'plus') {

                if (currentVal < input.attr('max')) {
                    input.val(currentVal + 1).change();
                }
                if (parseInt(input.val()) == input.attr('max')) {
                    $(this).attr('disabled', true);
                }

            }
        } else {
            input.val(0);
        }
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


</script> 

<style>
    a.nav-link.active {
        color: #34a08d !important;
    }



</style>
@endsection
