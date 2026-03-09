 

<?php
    $singleCategory = (isset($name) && !empty($name)) ? getCategoryBySlug($name) : '';
if(isset($_GET['category'])){
$productCategory = $_GET['category'];
}else{
    $productCategory = '';
}
if(isset($productResults['total']['value'])){
    $totalProduct = $productResults['total']['value'];
}else{
    $totalProduct = '';
}
if(isset($_GET['keyword'])){
$keyword = $_GET['keyword'];
}else{
    $keyword = '';
}
if(isset($_GET['category'])){
    $title = 'Shop '.$productCategory.' Online |  Best '.$productCategory.' Available - Mallshark';
    $description = 'Explore top-quality '.$productCategory.' at Mallshark. With '.$totalProduct.' different options, find the perfect '.$productCategory.' to suit your needs. Enjoy shopping with secure payments.';
    $keyword = $productCategory;
}else{
    $title = isset($singleCategory->meta_title	) ? $singleCategory->meta_title	 : 'Shop Mallshark - Find Your Online Products';
$description = isset($singleCategory->meta_description) ? $singleCategory->meta_description : 'Discover a wide range of products on Mallshark. Browse through the best options available online, perfect for your needs. Shop now and enjoy our secure payment options.';
}
?>
@extends('layouts.frontend-main')
@section('content')
@section('title', $title)
@section('description', $description)
@section('keyword', $keyword)
@section('google-analytics')
@include('layouts.google-analytics')
@stop
@section('meta')
@include('layouts.searchresults-meta', ['singleCategory1' => $singleCategory])
@stop

<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
<style>
    .search-icon-header {
        display: inline-block;
    }

    @media only screen and (min-width: 320px) and (max-width: 767px) {
        #menu-toggle {
            display: inline-block;
        }

        header .navbar-brand {
            /* margin-left: 30px; */
        }

    }
    .current-stock-deactive {
    pointer-events: none;
    opacity: 0.6;
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

<?php
$llCategoreis = getCategoriesHaveProducts();
$query_category = url()->current() . '?';
$slug = request()->segment(2);
if($slug && $slug != 'search'){
    $slug = $slug;
}else{
    $slug = request()->segment(1);
    if($slug && $slug != 'search'){
       $slug = $slug;
    }else{
        $slug = isset($_GET['category']) ? $_GET['category'] : '';
    }
}

// if(isset($slug) && $slug != '' && $slug != 'search'){
//     $slug = $slug;
// }else{
//     $slug = isset($_GET['category']) ? $_GET['category'] : '';
// }

$singleCategory = (isset($slug) && !empty($slug)) ? getCategoryBySlug($slug) : '';
// dd($singleCategory);
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

// Get the full URL
$query_String = url()->current() . '?';
    $currentUrlWithQuery = $query_String;
        
        $urlParts = parse_url($currentUrlWithQuery);

    // Parse the query string into an array
    if(isset($urlParts['query'])){
        parse_str($urlParts['query'], $queryParams);
       
        if(isset($queryParams['sort_by']) && isset($queryParams['sort_order'])){
            
            unset($queryParams['sort_by'], $queryParams['sort_order']);
          
        }
        if(isset($queryParams['keyword'])){
            unset($queryParams['keyword']);
        }
        $newQueryString = http_build_query($queryParams);
        $newUrl = $urlParts['scheme'] . '://' . $urlParts['host'] . $urlParts['path'] . '?' . $newQueryString;
       
        if (strpos($newUrl, '?') !== false) {
            $query_String = $newUrl;
        }else{
            $query_String = $newUrl . '?';
        } 
    }
$url_cur = $_SERVER['REQUEST_URI'];

$path = strtok($url_cur, '?'); 
$data_param = '';

// Check if the URL has query parameters
if (strpos($url_cur, '?') !== false) {
    // Get the query string part after the "?"
    $urlParts = parse_url($url_cur);
    parse_str($urlParts['query'], $queryParams);
    if(isset($queryParams['sort_by']) && isset($queryParams['sort_order'])){
            
            unset($queryParams['sort_by'], $queryParams['sort_order']);
          
    }
    if ($queryParams && count($queryParams) > 0) {
        $queryParams = http_build_query($queryParams);
        // Check if the URL ends with "&"
        if (substr($url_cur, -1) == '&') {

            if (substr($path, -1) == '?') {
            $query_String = $path . $queryParams;
            }else{
            $query_String = $path . '?' . $queryParams;
            }
        } else {
          
            if (substr($path, -1) == '?') {
            $query_String = $path . $queryParams;
            }else{
            $query_String = $path . '?' . $queryParams;
            }
        }
    } else {
        // If no query parameters are found after "?"
        $queryParams = http_build_query($queryParams);
        $query_String = $path . '?' . $queryParams;
    }
} else {
    // If no "?" in the URL, add it and append query parameters
    $queryParams = http_build_query($queryParams);
    $query_String = $path . '?' . $queryParams;
    
}

// Output the new URL



        $url = 'category.landing';
?>

<!-- /#sidebar-wrapper -->

<script src="{{ URL::asset('assets/js/cart.js',env('HTTPS_STATUS')) }}"></script>
<section class="page-search-results search-results-page-container category-title-remove">
    <div class="container   srchbxot default-spacing-bootstrap">
        
            {{-- <h1 class="categorytitle">{{$catetitle}}</h1> --}}
            
                <div class="btn-group btn-breadcrumb breadcrumb-default">
                    <?php
                        
                        if (isset($productResults['total']['value']) && $productResults['total']['value'] > 0) {  ?>
                    <div itemprop="breadcrumb" style="display:block">
                        <a href="/" class="btn btn-default visible-lg-block visible-md-block">Home</a>
    
                        <!-- <a href="/q" class="btn btn-default visible-lg-block visible-md-block" ></a> -->
                    </div>
                   
                   @if(isset($slug) && $slug == '')
                   <i class="fa fa-angle-right "></i>
                   <a href="javascript:void(0)" class="btn btn-default visible-lg-block visible-md-block">Categories</a>
                   @endif
                <?php
                        if (isset($slug) && $slug != '' && $slug != 'search') {
                            
                            ?>
                    <?php
                    $slugCategoryName = getCategoryBySlug($slug);
                            if (getChildCategoryParent($slug)) {
                               $slugParentCategory = getChildCategoryParent($slug);
                               
                                ?>
    
                    <i class="fa fa-angle-right "></i>
                    <a href="{{route($url,['slug'=>$slugParentCategory->category_slug])}}"
                        class="btn btn-default visible-lg-block visible-md-block"><?php echo $slugParentCategory->categoryTitle;
                        ?></a>
                        <i class="fa fa-angle-right "></i>
                        <a href="{{route($url,['slug'=>$slugParentCategory->category_slug , 'subSlug' => $slug])}}" class="btn btn-default visible-lg-block visible-md-block"
                            itemprop="name"><?php echo $slugCategoryName->categoryTitle; ?></a>
                    <?php }else{
                     
                            ?>
                    <i class="fa fa-angle-right "></i>
                    <a href="{{route($url,['slug'=>$slug])}}" class="btn btn-default visible-lg-block visible-md-block"
                        itemprop="name"><?php echo isset($slugCategoryName) ? $slugCategoryName->categoryTitle : ""; ?></a>
                    <!-- <a href="" class="btn btn-default visible-lg-block visible-md-block" >(<?php echo $productResults['total']['value']; ?> Designed)</a> -->
                    <?php }}else if (isset($_GET['category']) && $_GET['category'] != '') {

                        
                        ?>
                <?php
                 $slugCategoryName = getCategoryBySlug($_GET['category']);
                        if (getChildCategoryParent($_GET['category'])) {
                            $slugParentCategory = getChildCategoryParent($_GET['category']);
                            ?>

                <i class="fa fa-angle-right "></i>
                <a href="{{route($url,['slug'=>$_GET['category']])}}"
                    class="btn btn-default visible-lg-block visible-md-block"><?php echo $slugParentCategory->categoryTitle;
                    ?></a>
                <?php }
                
                        ?>
                <i class="fa fa-angle-right "></i>
                <a href="{{route($url,['slug'=>$_GET['category']])}}" class="btn btn-default visible-lg-block visible-md-block"
                    itemprop="name"><?php echo $slugCategoryName->categoryTitle; ?></a>
                <!-- <a href="" class="btn btn-default visible-lg-block visible-md-block" >(<?php echo $productResults['total']['value']; ?> Designed)</a> -->
                <?php } ?>
    
                    <?php if (isset($_GET['keyword']) && $_GET['keyword'] != '') { ?>
                    <i class="fa fa-angle-right"></i>
                    <a href="/search?keyword=<?php echo $_GET['keyword']; ?>&category=<?php echo isset($_GET['category']) ? $_GET['category'] : '';  ?>"
                        class="btn btn-default visible-lg-block visible-md-block"><?php echo str_replace('-', ' ', $_GET['keyword']); ?></a>
                    <?php
                        }
                    }
                    ?>
    
    
                </div>
            
        <div class="row cdt-bdy">
            @include('search.search-sidebar')
            <div class="ajax-res col-sm-10 main-search-bar-design ">
                <?php
                if($singleCategory != ''){
                    $imageUrl = getAttachmentUrlById((int) $singleCategory->category_landing_image, 'media');
                    if ($imageUrl) {
                    ?>
                    <div class="category-landing">
                        <div class="landing-image" style="background-image:url({{$imageUrl}}); ">
                           <div class="cat-tex-up">
                            <h2>{{$singleCategory->categoryTitle}}</h2>
                            <p>{{$singleCategory->description}}</p> 
                            {{-- <img src="{{$imageUrl}}" alt=""> --}}
                        </div>
                        </div>
                        
                    </div>
                    <?php
                    }   
                }
                ?>
                
                <?php if (!empty($productResults) && count($productResults['records']) > 0) { ?>
                    
                    @include('search.top-filters')
                    <div class="col-md-99">

                        <div class="results-box-mbt row">
                            @include('search.search-ajax-loadmore')
                        </div><!-- results-box-mbt -->
                        <div class="search-ajax-products text-center"></div>
                        <div class="row">
                            <div class="custom-pagination col-sm-12 mt-15">
                                <?php
                                $q = [];
                                if (isset($_GET['status'])) {
                                    $q['status'] = $_GET['status'];
                                }
                                if (isset($_GET['order'])) {
                                    $q['order'] = $_GET['order'];
                                }
                                ?>

                            </div>
                        </div>
                        
                    </div>

                <?php } else { ?>
                    <div class="col-sm-99">
                        @include('search.no-record')
                        <!-- No record found -->
                    </div>
                <?php } ?>
                    <?php if(isset($_GET['category'])) {
                     $description = getDescriptionCategorySlug($_GET['category']);
                     ?>
                <!-- <div class="categorytitle  mt-3">
                    <p><?php echo isset($description->description) ? $description->description: ''; ?> Should have a section to place a 100 words subtitle section.
                 And at the bottom of the page should have the option to place category specific 500 words content.</p>
                </div> -->
                <?php }else{ ?>
                    <!-- <div class="subtitle  mt-3">
                    <p>Should have a section to place a 100 words subtitle section.
                 And at the bottom of the page should have the option to place category specific 500 words content.</p>
                </div> -->
                <?php } ?>
            </div>
            
        </div>


    </div>

</section>
<script>
jQuery(document).ready(function () {
    jQuery('.active').closest('.collapse').parent().find('.parent-nave').click();

});
jQuery(document).ready(function () {
    jQuery('.parent-active').click();

});
</script>
<!-- js for product search results slider range -->
<!-- <script src="https://code.jquery.com/jquery-1.12.4.js"></script> -->
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
$(document).on('click', '.fav-add', function () {
    current = $(this);
    var data = {};
    data['product_id'] = $(this).attr('data-product-id');
    sendreq(data, 'favourite', 'add', 'callbackfavourites');
    $(this).toggleClass("animate");

});
function callbackfavourites(data) {
    if(data.status == 'success'){
              toastr.options = {
            closeButton: true, // Add close button
            disableTimeOut: true,
            tapToDismiss: true,
            positionClass: 'toast-top-right',
            toastClass: 'toast-icon custom-toast-success'
            };
            toastr.success(data.message);
        }else if(data.status == 'error'){
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
$(document).on('click', '.notbuy', function () {
//alert("you cannot by your own product");
    swal("You cannot buy your own product");
});
$(document).on('click', '.notinstock', function () {
    alert("product is out of stock");
});

if ($('.inner-nav-mbt a').is(".active")) {
    $(this).parent().parent().parent().addClass("red");
}



</script>
<script>//plugin bootstrap minus and plus
//http://jsfiddle.net/laelitenetwork/puJ6G/
    $(document).on('click', '.btn-number', function () {

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
</script> 
<script>

    $(document).ready(function () {
        $('.search-listing-items').each(function () {
            let product_index = $(this).attr('data-product-count');
            // const productId = $(this).data('product_id');
            // const variationId = $(this).data('variation_id');
            // const storeId = $(this).data('store_id');
            var item_name =  $(this).find('.product-item-name').text();
            var child_category = $(this).find('.child-category').attr('data-child-category');
            let parent_category = $(this).find('.parent-category').attr('data-category-name');
            let product_price = $(this).find('.product-price').text();
            let brand_name = $(this).find('.product-store-name').attr('data-store-name');
            let product_quantity = $(this).find('.product-stock').attr('data-product-stock');
            // debugger
            // trackAddToCart(productId);
        });
    });
    


// managage back button click (and backspace)
window.addEventListener('popstate', function(event) {
    return "Are you sure you wish to leave?";
});
</script>
<style>
    a.nav-link.active {
        color: #34a08d !important;
    }



</style>
<div class="uploadImages slts-ldmore-loader" style="display: none;">
    <span></span>
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
                                $('.ajax-res .col-md-99 .results-box-mbt').append(res);
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

</script>
@endsection
