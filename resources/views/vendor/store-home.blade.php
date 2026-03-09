<?php
$aboutus = isset($storeinfo[0]->about_us) ? $storeinfo[0]->about_us : '';
$street_address = isset($storeinfo[0]->street_address) ? $storeinfo[0]->street_address : '';
$country_id = isset($storeinfo[0]->store_country) ? $storeinfo[0]->store_country : '';
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
@section('content')
@section('title', 'Product Results')
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
            margin-left: 30px;
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


<script src="{{ URL::asset('assets/js/cart.js', env('HTTPS_STATUS')) }}"></script>
<section class="page-search-results">

    <div class="strhd">

        <div class="container-fluid">
            <div class="row">
                <div class="col hdlft">
                    <span>
                        <i class="fa fa-star" aria-hidden="true"></i>
                        <i class="fa fa-star" aria-hidden="true"></i>
                        <i class="fa fa-star" aria-hidden="true"></i>
                        <i class="fa fa-star" aria-hidden="true"></i>
                        <i class="fa fa-star-half-o" aria-hidden="true"></i>
                        <b>445</b> Ratings</span>
                    <br>
                    <span class="small">6435 Sales &nbsp;| &nbsp; Since 2016 &nbsp;| &nbsp; 45 Items</span>

                </div>
                <div class="col strlgo hdcnt"><a href="#"><img src="images/st-logo.png" alt="brand"></a></div>
                <div class="col hdrght">
                    <button class="btn btn-primary btn-simple">Follow Us</button>
                    &nbsp; &nbsp;
                    <button class="btn btn-primary">Become Ambassador</button>
                </div>
            </div>
        </div>

    </div>

    <div class="strnav">
        <div class="col-md-12">
            <nav class="container-fluide dshbrd">
                <div class="row">
                    <ul class="nav">
                        <li class="nav-item crnt"><a class="nav-link active" href="#">Products</a></li>
                        <li class="nav-item "><a class="nav-link" href="#">Review</a></li>
                        <li class="nav-item"><a class="nav-link" href="#">About</a></li>
                        <li class="nav-item"><a class="nav-link active" href="#">Affiliate Program</a></li>
                        <li class="nav-item"><a class="nav-link" href="#">Policies</a></li>
                        <li class="nav-item"><a class="nav-link" href="#">Contact</a></li>
                    </ul>

                    <span class=" strsoc pull-right">
                        <a class="sclicn" href=""><img src="images/icon-fb.jpg"></a> &nbsp;
                        <a class="sclicn" href=""><img src="images/icon-twt.jpg"></a> &nbsp;
                        <a class="sclicn" href=""><img src="images/icon-ggl.jpg"></a> &nbsp;
                        <a class="sclicn" href=""><img src="images/icon-pnt.jpg"></a>
                    </span>
                </div>
            </nav>
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
    ?>
    <div class="strbnr">
        <div id="carouselExampleIndicators" class="carousel slide" data-ride="carousel">
            <ol class="carousel-indicators">
                <li data-target="#carouselExampleIndicators" data-slide-to="0" class="active"></li>
                <li data-target="#carouselExampleIndicators" data-slide-to="1"></li>
                <li data-target="#carouselExampleIndicators" data-slide-to="2"></li>
            </ol>
            <div class="carousel-inner">
                <div class="carousel-item active">
                    <img class="d-block w-100" src="{{ $url_img }}" alt="First slide">
                </div>
                <div class="carousel-item">
                    <img class="d-block w-100" src="{{ $url_img2 }}" alt="Second slide">
                </div>
                <div class="carousel-item">
                    <img class="d-block w-100" src="{{ $url_img3 }}" alt="Third slide">
                </div>
            </div>
            <a class="carousel-control-prev" href="#carouselExampleIndicators" role="button" data-slide="prev">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                <span class="sr-only">Previous</span>
            </a>
            <a class="carousel-control-next" href="#carouselExampleIndicators" role="button" data-slide="next">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                <span class="sr-only">Next</span>
            </a>
        </div>

    </div>
    <div class="container-fluide rslts">


        <div class="row search-reults-head mt-50 mb-10 hide-mobile767">

            <div class="col-md-12 col-lg-3"></div>

            <div class="col-md-8 col-lg-6">

                <p class="mt-10"><?php if (isset($_GET["keyword"]) && $_GET["keyword"] != '') { ?>Results Found For
                    <span class="search-keywords">“{{ $_GET['keyword'] }}�?</span> <?php } ?>
                    <!--                     <span class="records-found text-blue display-inline-block ml-10"> ({{ isset($productResults['total']) ? $productResults['total'] : '0' }} results found) </span> -->




                </p>
            </div>

            <div class="col-md-4 col-lg-3">
                <div class="sec-right">
                    <div class="form-group clearfix mb-0 pull-right-large pull-right">

                        <select class="form-control" id="sorting_dropdown" name="">
                            <option value="{{ $query_String }}">Sort By</option>
                            <option <?php
                            if (isset($_GET['sort_by']) && $_GET['sort_by'] == 'created_at' && isset($_GET['sort_order']) && $_GET['sort_order'] == 'DESC') {
                                echo 'selected';
                            }
                            ?> value="{{ $query_String }}&sort_by=created_at&sort_order=DESC">
                                New to Old
                            </option>
                            <option <?php
                            if (isset($_GET['sort_by']) && $_GET['sort_by'] == 'created_at' && isset($_GET['sort_order']) && $_GET['sort_order'] == 'ASC') {
                                echo 'selected';
                            }
                            ?> value="{{ $query_String }}&sort_by=created_at&sort_order=ASC">
                                Old to New
                            </option>
                            <option <?php
                            if (isset($_GET['sort_by']) && $_GET['sort_by'] == 'price' && isset($_GET['sort_order']) && $_GET['sort_order'] == 'DESC') {
                                echo 'selected';
                            }
                            ?> value="{{ $query_String }}&sort_by=price&sort_order=DESC">
                                Price: High to Low
                            </option>
                            <option <?php
                            if (isset($_GET['sort_by']) && $_GET['sort_by'] == 'price' && isset($_GET['sort_order']) && $_GET['sort_order'] == 'ASC') {
                                echo 'selected';
                            }
                            ?> value="{{ $query_String }}&sort_by=price&sort_order=ASC">
                                Price: Low to High
                            </option>

                        </select>
                    </div>
                </div>
            </div>
        </div><!-- search-reults-head -->

        <div class="row cdt-bdy">
            <div class="col-md-3">

                <div class="pos-rel">
                    <div class="sidebar-filter pos-absolute mt-minu50 mb-20">
                        <div class="sidebar-content mobile-mt-0  hide-mobile767">
                            <?php if (count($llCategoreis) > 0) { ?>
                            <h3 class="bros-heading gray-text fnt18">
                                <span class="broser-cate-cion"><i class="material-icons">list</i></span>
                                Browse Categories
                            </h3>

                            <ul class="nav flex-column flex-nowrap">
                                <?php
                                    foreach ($llCategoreis as $key => $val) {
                                        $parent_Category_title = isset($val['cat_info']['title']) ? $val['cat_info']['title'] : '';
                                        $parent_Category_slug = isset($val['cat_info']['slug']) ? $val['cat_info']['slug'] : '';
                                        $parent_Category_id = isset($val['cat_info']['id']) ? $val['cat_info']['id'] : '';
                                        ?>

                                <li class="nav-item">
                                    <div style="display: flex;">
                                        <?php
                                                $class_have_child = 'no-childs';
                                                if (isset($val['childs']) && count($val['childs']) > 0) {


                                                    $class_have_child = '';
                                                    ?>
                                        <a style="display:inline-block"
                                            class="nav-link collapsed parent-nave <?php
                                            if (isset($_GET['category']) && $_GET['category'] == $parent_Category_slug) {
                                                echo 'parent-active';
                                            }
                                            ?>"
                                            href="javascript:;" data-toggle="collapse"
                                            data-target="#nav-{{ $parent_Category_id }}"></a> <?php } ?>
                                        <a style="display:inline-block" class="nav-link <?php echo $class_have_child; ?>"
                                            href="{{ $query_category }}category=<?php echo $parent_Category_slug; ?>">{{ $parent_Category_title }}</a>
                                    </div>
                                    <?php
                                            if (isset($val['childs']) && count($val['childs']) > 0) {
                                                ?>
                                    <div class="collapse" id="nav-{{ $parent_Category_id }}" aria-expanded="false">
                                        <ul class="flex-column pl-2 nav inner-nav-mbt">
                                            <?php foreach ($val['childs'] as $newVal) {
                                                            ?>
                                            <li class="nav-item"><a class="nav-link <?php
                                            if (isset($_GET['category']) && $_GET['category'] == $newVal['slug']) {
                                                echo 'active';
                                            }
                                            ?>"
                                                    href="{{ $query_category }}category=<?php echo isset($newVal['slug']) ? $newVal['slug'] : ''; ?>"><?php echo isset($newVal['title']) ? $newVal['title'] : ''; ?></a>
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
                        <div class="sidebar-content mt-30 mobile-mt-0 mobile-style-filter bkclr-mbt">
                            <h3 class="hide-mobile767"><i class="material-icons">

                                </i> <span class="filter-text">Filters</span></h3>
                            <div class="price_filter">
                                <form action="{{ route('searchresult') }}" method="GET" id="search-filters">



                                    <div class="row les-spacing">
                                        <!--
                                        <div class="col-sm-12 location-area">
                                            <div class="filter-bar mb-10">
                                                <p>
                                                    <label for="amount">Location Radius (Km):</label>

                                                </p>
                                                <input type="hidden" name="lat" id="loc_lat">
                                                <input type="hidden" name="lon" id="loc_lon">
                                                <div class="range-outer">
                                                    <div id="slider-range-min"></div>
                                                    <div class="km-shower">
                                                        <input type="text"
                                                               value="<?php echo isset($_GET['radius']) ? $_GET['radius'] : 5; ?>"
                                                               id="amount" name="radius" readonly style="border:0;">
                                                    </div>
                                                </div>
                                            </div>

                                        </div>
                                        -->
                                        <?php
                                        $constants = config('constants.shopzit_gamification');
                                        ?>
                                        <div class="col-sm-12">
                                            <div class="form-group clearfix mb-0">
                                                <label for="text" class="mt-10">$ Back</label>
                                                <select class="form-control" data-prodcut-id="36" id="cashback"
                                                    name="cashback">
                                                    <option value="">all</option>
                                                    <option value="{{ $constants['STANDARD_CASHBACK'] }}"
                                                        <?php
                                                        if (isset($_GET['cashback']) && $_GET['cashback'] == $constants['STANDARD_CASHBACK']) {
                                                            echo 'selected';
                                                        }
                                                        ?>>{{ $constants['STANDARD_CASHBACK'] }}
                                                    </option>
                                                    <option value="{{ $constants['CHOOSEN_CASHBACK'] }}"
                                                        <?php
                                                        if (isset($_GET['cashback']) && $_GET['cashback'] == $constants['CHOOSEN_CASHBACK']) {
                                                            echo 'selected';
                                                        }
                                                        ?>>{{ $constants['CHOOSEN_CASHBACK'] }}
                                                    </option>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="col-6 padright-7">
                                            <div class="form-group">
                                                <label>Price Range</label>
                                                <input type="number" value="<?php echo isset($_GET['min_price']) ? $_GET['min_price'] : ''; ?>" name="min_price"
                                                    min="1" class="form-control" placeholder="Min">
                                            </div>
                                        </div>
                                        <div class="col-6 padleft-7">
                                            <div class="form-group">
                                                <label style="visibility: hidden;">Price</label>
                                                <input type="number" name="max_price" min="1"
                                                    value="<?php echo isset($_GET['max_price']) ? $_GET['max_price'] : ''; ?>" class="form-control"
                                                    placeholder="Max">
                                            </div>
                                        </div>
                                        <div class="col-sm-12">
                                            <label>Condition</label>
                                            <div class="conditional-div">
                                                <select class="form-control" id="" name="condition">
                                                    <option value="">all</option>
                                                    <option value="new" <?php
                                                    if (isset($_GET['condition']) && $_GET['condition'] == 'new') {
                                                        echo 'selected';
                                                    }
                                                    ?>>new
                                                    </option>
                                                    <option value="used" <?php
                                                    if (isset($_GET['condition']) && $_GET['condition'] == 'used') {
                                                        echo 'selected';
                                                    }
                                                    ?>>used
                                                    </option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                    <?php
                                    if (isset($_GET['keyword']) && $_GET['keyword'] != '') {
                                        echo '<input type="hidden" name="keyword" value="' . $_GET['keyword'] . '">';
                                    }
                                    
                                    if (isset($_GET['category']) && $_GET['category'] != '') {
                                        echo '<input type="hidden" name="category" value="' . $_GET['category'] . '">';
                                    }
                                    if (isset($_GET['category_parent']) && $_GET['category_parent'] != '') {
                                        echo '<input type="hidden" name="category_parent" value="' . $_GET['category_parent'] . '">';
                                    }
                                    if (isset($_GET['sort_by']) && isset($_GET['sort_order'])) {
                                        echo '<input type="hidden" name="sort_by" value="' . $_GET['sort_by'] . '">';
                                        echo '<input type="hidden" name="sort_order" value="' . $_GET['sort_order'] . '">';
                                    }
                                    ?>

                                    <!--<div class="checkbox-list mt-20 hide-mobile767">
                                            <label class="container-checkbox"> Free Shipping
                                                <input type="checkbox" checked="checked">
                                                <span class="checkmark"></span>
                                            </label>
                                        </div> -->


                                    <div class="apply-filter-btn mt-20 hide-mobile767">

                                        <button type="submit"
                                            class="btn btn-primary btn-simple btn-block check-fields">Apply
                                            Filters
                                        </button>
                                        <br />
                                        <p> <a href="/q">Reset Filters</a></p>

                                    </div>
                                </form>

                            </div>
                        </div>


                    </div><!-- sidebar-filter -->
                </div>
            </div>
            <?php if (!empty($productResults) && count($productResults['records']) > 0) { ?>

            <div class="col-md-9">

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
                        $class = 'show-quantitiy-panel';
                        //}
                        $key2 = $cartProducts[$pr['_source']['product_id']];
                    } else {
                        $class = '';
                    }
                    ?>
                    <?php
                    $storeDataId = '';
                    if (!empty($pr['_source']['store_id'])) {
                        $storeDataId = 'data-store-id=' . $pr['_source']['store_id'];
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

                                <a class="fav-login login-popup" data-location="{{ URL::to('q/') }}"
                                    href="javascript:void(0)" data-toggle="modal" data-target="#login-registerpopup">
                                    <span>
                                        <i class="fa fa-heart-o" aria-hidden="true"></i>
                                    </span>

                                </a>
                                <?php } ?>
                            </div><!-- add-to-fav -->

                            <?php if (isset($pr['_source']['is_feature']) && $pr['_source']['is_feature'] == 1) {
                                        ?>
                            <div class="feature-product-sec" style="display:none">
                                <span>featured</span>
                            </div>
                            <?php
                                    }
                                    ?>
                            <?php $imageUrl = getAttachmentUrlById((int) $pr['_source']['feature_image'], 'medium'); ?>
                            <a href="{{ $url }}" class="image-container-mbt"> <img
                                    src="https://dev.ecommedge.com/uploads/product/cotton_60.jpg" alt="image"
                                    style="width:100%"> </a>

                            <div class="card-body" data-stock="10">


                                <h5 class="card-title"><a href="<?php echo $url; ?>"><?php echo $pr['_source']['title']; ?></a>
                                </h5>
                                <div class="cartsec">
                                    <!--                                show-quantitiy-panel-->
                                    <div class="crtbtn col  buttons-group-cnt <?php echo $class; ?>">
                                        <?php if ($pr['_source']['product_type'] == 'variant' || $pr['_source']['weight'] > 30) { ?>
                                        <a <?php echo $storeDataId; ?> href="<?php echo URL::to('product/' . $pr['_source']['slug']); ?>"
                                            class="primary-btn btn btn-primary btnorm">Add To Cart</a>
                                        <?php } else { ?>
                                        <button type="button" <?php echo $storeDataId; ?>
                                            data-product-id="<?php echo $pr['_source']['product_id']; ?>"
                                            class="primary-btn btn btn-primary btnorm cartadd ">Add To Cart
                                        </button>

                                        <div class="input-group quantity-number-option">
                                            <span class="minus-icon first-icon-counter"
                                                data-pro-id="<?php echo $pr['_source']['product_id']; ?>" <?php echo $storeDataId; ?>>
                                                <i class="fa fa-minus" aria-hidden="true"></i>
                                            </span>
                                            <span class="input-counter">

                                                <input type="text" name="cart_quantity" id="cart_quantity"
                                                    class="form-control input-number" placeholder="in cart"
                                                    value="<?php
                                                    if (!empty($key2)) {
                                                        echo $key2;
                                                    } else {
                                                        echo 1;
                                                    }
                                                    ?>" min="1" max="50"
                                                    readonly="">
                                                <!-- <span class="static-text"> in cart</span> -->
                                            </span>
                                            <span class="plus-icon second-icon-counter"
                                                data-pro-id="<?php echo $pr['_source']['product_id']; ?>" <?php echo $storeDataId; ?>><i
                                                    class="fa fa-plus" aria-hidden="true"></i></span>
                                        </div>
                                        <?php } ?>

                                    </div>

                                    <div class="prc col text-right"><span class="throg small"> Z2 </span><br><span>
                                            $12 </span></div>
                                </div>
                                <div class="progress">
                                    <div class="progress-bar" style="width:70%"></div>
                                </div>
                                <!-- Progress bar ends -->
                                <div class="infbx small  justify-content-between">
                                    <div>
                                        <span>Supporters: <b>0</b></span><br>
                                        <span>Gave Back:<b> $78896</b></span><br>

                                    </div>

                                    <div class="text-right">
                                        <span>
                                            <div class="rating-strip">
                                                <i class="fa fa-star-o" aria-hidden="true"></i>
                                                <i class="fa fa-star-o" aria-hidden="true"></i>
                                                <i class="fa fa-star-o" aria-hidden="true"></i>
                                                <i class="fa fa-star-o" aria-hidden="true"></i>
                                                <i class="fa fa-star-o" aria-hidden="true"></i>
                                            </div>


                                            <span><a href="javascript:void(0);">Grandmaknitslove</a></span>
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
    jQuery(document).ready(function() {

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
    $(document).on('click', '.HeartAnimation', function() {
        current = $(this);
        var data = {};
        data['product_id'] = $(this).attr('data-product-id');
        sendreq(data, 'favourite', 'add', 'callbackfavourites');
        $(this).toggleClass("animate");

    });

    function callbackfavourites(data) {

    }
    $(function() {
        // bind change event to select
        $('#sorting_dropdown').on('change', function() {
            var url = $(this).val(); // get selected value
            if (url) { // require a URL
                window.location = url; // redirect
            }
            return false;
        });
    });
    jQuery(document).ready(function() {
        jQuery('.active').closest('.collapse').parent().find('.parent-nave').click();

    });
    jQuery(document).ready(function() {
        jQuery('.parent-active').click();

    });
</script>
<!-- js for product search results slider range -->
<script src="https://code.jquery.com/jquery-1.12.4.js"></script>
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
<script>
    $(function() {
        $("#slider-range-min").slider({
            range: "min",
            value: <?php echo isset($_GET['radius']) ? $_GET['radius'] : 5; ?>,
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
                $(".mobile-style-filter").fadeOut();
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
</script>


<style>
    a.nav-link.active {
        color: #34a08d !important;
    }

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
</style>
@endsection
