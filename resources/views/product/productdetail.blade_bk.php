@extends('layouts.new-main')
<!-- Main Banner Starts -->
@section('content')
@section('title',$productDetail['title'])
@section('google-analytics')
@include('layouts.google-analytics')
@stop
<link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/flexslider/2.7.0/flexslider.css" media="all"/>
<!-- for magnifier popup for desktop version-->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.8.1/slick.css"/>
<!--<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/magnific-popup.js/1.1.0/magnific-popup.min.css"/>-->
<link rel="stylesheet" href="https://mreq.github.io/slick-lightbox/dist/slick-lightbox.css"/>
<link type="text/css" rel="stylesheet" href="https://cdn.jsdelivr.net/jquery.jssocials/1.4.0/jssocials.css" />
<link type="text/css" rel="stylesheet" href="https://cdn.jsdelivr.net/jquery.jssocials/1.4.0/jssocials-theme-flat.css" />

<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/flexslider/2.7.1/jquery.flexslider-min.js"></script>

<?php
$userId = Session::get('userData')['user_id'];
if (empty($productDetail)) {
    echo '<h1>404</h1>';
    exit;
}
$minimumPrice = round($productDetail['price'] / 2);
$constants = config('constants.shopzit_gamification');
?>
<!-- Content Starts -->
<main class="product-detail prductdetl-cntr">
    <div class="strhd hdr-btFxd">
        <div class="container-fluid">
            <div class="row">
                <div class="col hdlft row">
                    <div class=" ">
                        <?php
                        $iconUrl = '';
                        $store_info = getStoreInfoById($productDetail['store_id']);
                        $store_icon = isset($store_info[0]->store_icon) ? $store_info[0]->store_icon : '';
                        if (!empty($store_icon)) {
                            $image_id = getImageIdByIconId((int) $store_icon);
                            if (!empty($image_id)) $iconUrl = getAttachmentUrlById((int) $image_id, 'icons');
                        }
                        ?>
                        <a href="<?php echo URL::to('/brand/' . $storeInfo[0]->store_slug); ?>"><img width="50" style="margin-right:10px" src="<?php echo $iconUrl; ?>"></a>
                        <!--<span>
                            <i class = "fa fa-star-o" aria-hidden = "true"></i>
                            <i class = "fa fa-star-o" aria-hidden = "true"></i>
                            <i class = "fa fa-star-o" aria-hidden = "true"></i>
                            <i class = "fa fa-star-o" aria-hidden = "true"></i>
                            <i class = "fa fa-star-o" aria-hidden = "true"></i>
                            <b>0</b> Rating</span>-->
                        <span class="small">
                            <?php
                            if ($storeSales > 1) {
                                echo $storeSales . " Sales";
                            } else {
                                echo $storeSales . " Sale";
                            }
                            ?>      &nbsp;| &nbsp;   Since <?php
                            $timestamp = strtotime($storeInfo[0]->created_at);
                            echo $date = date('Y', $timestamp);
                            ?>    &nbsp;| &nbsp; <?php
                            if ($totalProducts > 1) {
                                echo $totalProducts . " items";
                            } else {
                                echo $totalProducts . " item";
                            }
                            ?>
                        </span>
                    </div>
                </div>
                <div class="col strlgo hdcnt ">
                    <?php
                    $imageUrl = '';
                    if (isset($storeInfo[0]->store_logo) && $storeInfo[0]->store_logo != "") {
                        $imageUrl = getAttachmentUrlById((int) $storeInfo[0]->store_logo, 'medium');
                    }
                    ?>
                    <a href="<?php echo URL::to('/brand/' . $storeInfo[0]->store_slug); ?>">
                        <img src="<?php echo $imageUrl; ?>" class="img-fluid" alt="<?php echo isset($storeInfo[0]->name) ? $storeInfo[0]->name : ''; ?>" >
                    </a>
                </div>
                <div class="col hdrght">
                    <a href="<?php echo URL::to('/brand/' . $storeInfo[0]->store_slug); ?>" class="btn btn-primary btn-simple">Brand Home</a>
                </div>
            </div>
        </div>
    </div><!-- strhd -->
    <section>
        @if(Session::has('pledge_added'))
        <p class="alert alert-success">{{ Session::get('pledge_added') }}</p>
        @endif
        <div class="product-slider-section ">
            <?php
            $clstyle = "hidden";
            if ($incartProduct > 0) {
                $clstyle = "";
            }
            ?>
            <div class="container pos-rel <?php echo $clstyle; ?>" id="view_cart">
                <div class="col-sm-12">
                    <div class="view-to-cart d-flex justify-content-between">
                        <span class=" crt-success-messge  align-self-center"> <?php echo isset($productDetail['title']) ? $productDetail['title'] : ""; ?> added to your cart </span>
                        <div class="col-sm-3" style="margin-right: -40px;">
                            <a href="<?php echo URL::to('/cart'); ?>" class="">View cart</a>
                            <a href="<?php echo URL::to('/checkout'); ?>" class="">Check out</a>
                        </div>
                    </div>
                </div>
            </div>
            <?php $title = isset($productDetail['title']) ? $productDetail['title'] : ''; ?>
            <div class="container">
                <div class="search_message alert alert-danger alert-success-mbt col-sm-offset-2 col-sm-8 pun" style="display: none;"></div>
                <div class="row">
                    <div class="col-md-12 ">
                        <h4 class="title-prp"> <?php echo $title; ?> </h4>
                    </div>
                    <div class="large-loader" style="display: none;">
                        <span>
                            <img src=" {{ asset('assets/images/loader-large.svg',env('HTTPS_STATUS')) }}"
                                 class="img-fluid all-browser">

                            <img src=" {{ asset('assets/images/ajax-loader.gif',env('HTTPS_STATUS')) }}"
                                 class="img-fluid IE-loader">
                        </span>
                    </div>
                    <!-- Filter Box Starts -->
                    <div class=" col-lg-8 col-md-8 ">
                        <div class="product-detail-img-cnt">
                            <!-- Place somewhere in the <body> of your page -->
                            <div class="flexslider">
                                <?php
                                $fill = "";
                                if (Session::has('userData')) {
                                    $favProductId = getBuyerFavProducts();
                                    if (!empty($favProductId) && in_array($productDetail['product_id'], $favProductId)) {
                                        $fill = "animate fillheart";
                                    }
                                    ?>
                                    <div class="add-to-fav">
                                        <div class="heart-addtofav adfvt <?php echo $fill; ?>" data-product-id="<?php echo $productDetail['product_id']; ?>"></div>
                                    </div>
                                <?php } else {
                                    ?>
                                    <a class="fav-login login-popup"
                                       data-location="{{URL::to('product/'.$productDetail['slug'])}}"
                                       href="javascript:void(0)" data-toggle="modal" data-target="#login-registerpopup">
                                        <div class="add-to-fav">
                                            <div class="heart-addtofav fav-add"></div>
                                        </div>
                                    </a>
                                <?php } ?>

                                <div class="slider-for">
                                    <?php
                                    if (isset($productDetail['video_url']) && !empty($productDetail['video_url'])) {
                                        ?>
                                        <iframe class="youtube-video" width="420" height="420" src="<?php echo isset($productDetail['videoUrlSource']) ? $productDetail['videoUrlSource'] : ''; ?>" frameborder="0" allowfullscreen></iframe>
        <!--                                   <iframe width="560" height="315" src="<?php //echo $productDetail['video_url'];         ?>" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>-->
        <!--                                         <a href="<?php //echo isset($productDetail['video_url'])?$productDetail['video_url']:"";       ?>" target="_blank"><img src="<?php //echo isset($productDetail['videoUrlSource'])?$productDetail['videoUrlSource']:"";         ?>" alt=""></a>-->
                                        <?php
                                    }

                                    if (!empty($productDetail['feature_image'])) {
                                        $featureImage = getAttachmentUrlById((int) $productDetail['feature_image'], 'product');
                                        ?>
                                        <a href="<?php echo $featureImage; ?>" class="item-slick"><img src="<?php echo $featureImage; ?>" alt="Alt"></a>
                                        <?php
                                    }
                                    $images = unserialize($productDetail['gallery']);
                                    if (!empty($images)) {
                                        foreach ($images as $img) {
                                            $productImage = getAttachmentUrlById((int) $img, 'product');
                                            ?>
                                            <a href="<?php echo $productImage; ?>" class="item-slick"><img src="<?php echo $productImage; ?>" alt="Alt"></a>
                                            <?php
                                        }
                                    }
                                    ?>
                                </div>
                                <!--<div class="slider-for ">
                                    <a href="https://www.youtube.com/embed/n_dZNLr2cME?rel=0&hd=1" target="_blank"><img src="http://img.youtube.com/vi/gPuI_pbCYOI/0.jpg" alt=""></a>
                                </div>-->
                            </div>
                        </div><!-- product-detail-img-cnt -->
                        <!-- pro-left=lrge -->
                    </div>
                    <div class="col-lg-4 col-md-4 ">
                        <div class="row-sidebar ">
                            <div class="sec-right ">
                                <?php
                                if (isset($productDetail['flashsale_status']) && $productDetail['flashsale_status'] == 'choosen') {
                                    ?>
                                    <div class="green-box-cashback clearfix">
                                        <div class="counter-campaign text-center clearfix">
                                            <div class="cntr-bx-left">
                                                <p>Time Left</p>
                                                <div id="countdown-campaign"></div>
                                            </div>
                                            <div class="metor-gauge">
                                                <div class="gauge-persnt"> <?php echo!empty($remainPledges) ? $remainPledges : ""; ?>% Left </div>
                                                <div id="pro-gauge" class="gauge gauge-big gauge-green">
                                                    <div class="gauge-arrow" data-percentage="<?php echo!empty($remainPledges) ? $remainPledges : ""; ?>"
                                                         style="transform: rotate(0deg);"></div>
                                                </div>
                                                <div class="mbr-cntr"></div>
                                            </div>
                                            <!--<p id="note"></p>-->
                                        </div>
                                        <div class="cashback-available persenage-cashback">
                                            <p>Zit Back <b>75%</b></p>
                                        </div>
                                    </div>
                                    <?php
                                } else if (isset($productDetail['flashsale_status']) && $productDetail['flashsale_status'] == 'standard') {
                                    ?>
                                    <div class="blue-box-cashback clearfix">
                                        <div class="counter-campaign text-center clearfix">
                                            <div class="cntr-bx-left">
                                                <p>Time Left</p>
                                                <div id="countdown-campaign"></div>
                                            </div>

                                            <div class="metor-gauge">
                                                <div class="gauge-persnt"> <?php echo!empty($remainPledges) ? $remainPledges : ""; ?>% Left </div>
                                                <div id="pro-gauge" class="gauge gauge-big gauge-green">
                                                    <div class="gauge-arrow" data-percentage="<?php echo!empty($remainPledges) ? $remainPledges : ""; ?>"
                                                         style="transform: rotate(0deg);"></div>
                                                </div>
                                                <div class="mbr-cntr"></div>
                                            </div>

                                        </div>
                                            <!--<p id="note"></p>-->


                                        <div class="cashback-available persenage-cashback">
                                            <p>Zit Back <b>20%</b></p>
                                        </div>
                                    </div>
                                <?php } else { ?>
                                    <div class="cashback-available">
                                        <p>Zit Back Available <b>2%</b></p>
                                    </div>
                                <?php } ?>
                                <button onclick="goBack()" class="primary-btn btn btn-primary pull-right text-white gobackbutton" style="display:none !important;">Go Back</button>
                                <?php /* ?><h4 class="gave-back-over">
                                  Gave Back Over: <span class="z-blue">z</span>5000

                                  <div class="icn-box-two center display-inline-block pull-right text-right">
                                  <a href="/cdn-cgi/l/email-protection#d0a4b1a9a9b9b2b190b7bdb1b9bcfeb3bfbd"><i class="fa fa-envelope-o" aria-hidden="true"></i><!--  Contact --></a>
                                  </div><!-- icn-box-two -->

                                  </h4><?php */ ?>
                                <hr class="blue-border-strip">
                                <div class="price-sec">
                                    <div class="instock-disount  clearfix">
                                        <div class="pull-left">
                                        <!--<h3 class="blue-text cut-off-price"> <strike>  Z<?php // echo isset($productDetail['price']) ? $productDetail['price'] : '';                                                               ?> </strike> </h3>-->

                                        <!--<h3 class="blue-text cut-off-price"> <strike>  Z<?php // echo isset($productDetail['price']) ? $productDetail['price'] : '';                                                               ?> </strike> </h3>-->
                                        <!--<h3 class="blue-text cut-off-price"> <strike>  Z<?php // echo isset($productDetail['price']) ? $productDetail['price'] : '';                                                               ?> </strike> </h3>-->

                                        <!--<h3 class="blue-text cut-off-price"> <strike>  Z<?php // echo isset($productDetail['price']) ? $productDetail['price'] : '';                                                               ?> </strike> </h3>-->
                                        <!--<h3 class="blue-text cut-off-price"> <strike>  Z<?php // echo isset($productDetail['price']) ? $productDetail['price'] : '';                                                               ?> </strike> </h3>-->

                                        <!--<h3 class="blue-text cut-off-price"> <strike>  Z<?php // echo isset($productDetail['price']) ? $productDetail['price'] : '';                                                               ?> </strike> </h3>-->
                                        <!--<h3 class="blue-text cut-off-price"> <strike>  Z<?php // echo isset($productDetail['price']) ? $productDetail['price'] : '';                                                               ?> </strike> </h3>-->

                                        <!--<h3 class="blue-text cut-off-price"> <strike>  Z<?php // echo isset($productDetail['price']) ? $productDetail['price'] : '';                                                               ?> </strike> </h3>-->
                                        </div>
                                    </div>
                                    <div class="justify-content-between clearfix ">
                                        <div class="pull-left-large pull-left fnt24">
                                            <h3 class="blue-text" id="prod-price">
                                                <!-- <span class="lsnt-smbl">
                                                    
                                                     <img src=" {{ asset('assets/images/z-symbal.png',env('HTTPS_STATUS')) }}" alt="" class="img-fluid"> 
                                                </span> -->
                                                <b id="product-price" data-price=" <?php echo $productDetail['price']; ?>">USDC <?php echo isset($productDetail['price']) ?  number_format($productDetail['price'], 2) : ''; ?></b>
                                                <!-- <span class="usdprice">//(USD <?php //echo number_format($productDetail['price'] / 10, 2); ?>)</span> -->
                                            </h3>
                                            <h3 class="not-available" style="display:none;"></h3>
                                        </div>
                                        <div class="empty-response"></div>
                                        <?php
                                        if ($productDetail['product_type'] == 'simple') {
                                            $currentStock = isset($productDetail['current_stock']) ? $productDetail['current_stock'] : 0;
                                            $classqty = '';
                                            if ($currentStock > 10) {
                                                $currentStock = 10;
                                            }
                                            ?>
                                            <div class="pull-right-large pull-right">
                                                <?php if ($currentStock > 0) { ?>
                                                    <div class="form-group clearfix mb-0">
                                                        <label for="text" class="mt-10">Quantity</label>

                                                        <select class="form-control" data-prodcut-id="<?php echo isset($productDetail['product_id']) ? $productDetail['product_id'] : ''; ?>" id="product_quantity" name="product_quantity">
                                                            <?php for ($i = 1; $i <= $currentStock; $i++) { ?>
                                                                <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                                                            <?php } ?>
                                                        </select>

                                                    </div>
                                                <?php } else { ?>
                                                    <span class="outofstock">Out of stock</span>
                                                <?php } ?>
                                            </div>

                                        <?php } else if ($productDetail['product_type'] == 'variant') { ?>
                                            <div class="pull-right-large pull-right" id="variable_product" style="display:none;">
                                                <div class="form-group clearfix mb-0">
                                                    <label for="text" class="mt-10">Quantity</label>
                                                    <select class="form-control" data-prodcut-id="<?php echo isset($productDetail['product_id']) ? $productDetail['product_id'] : ''; ?>" id="product_quantity" name="product_quantity">
                                                        <?php for ($i = 1; $i <= 10; $i++) { ?>
                                                            <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                                                        <?php } ?>
                                                    </select>

                                                </div>
                                            </div>
                                        <?php } ?>
                                    </div>

                                    <div class="shipping-option-quanity-box">
                                        <?php
                                        if (count($variations) > 0) {

                                            $attribute1 = $attribute2 = $attribute3 = $option1 = $option2 = $option3 = array();
                                            $attributevalue1 = $attributevalue2 = $attributevalue3 = $unit1 = $unit3 = $unit2 = '';
                                            $checkBoxprice1 = $checkBoxprice2 = $checkBoxprice3 = $checkBoxquantity1 = $checkBoxquantity2 = $checkBoxquantity3 = $checkBoxsku1 = $checkBoxsku2 = $checkBoxsku3 = '';
                                            $selectedClass = "";
                                            foreach ($variations as $key => $attribute) {
                                                if ($attribute->attribute1) {
                                                    $selectedClass = "col-12 col-md-12 pl-0 pr-0";
                                                    $attributevalue1 = $attribute->attribute1;
                                                    $checkBoxprice1 = $attribute->check_box_price1;
                                                    $checkBoxquantity1 = $attribute->check_box_quantity1;
                                                    $checkBoxsku1 = $attribute->check_box_sku1;

                                                    $unit1 = $attribute->unit1;
                                                    if ($attribute->option1) {
                                                        if (!(in_array($attribute->option1, $option1))) {
                                                            $option1[] = $attribute->option1;
                                                        }
                                                    }
                                                }
                                                if ($attribute->attribute2) {
                                                    $selectedClass = "col-6 col-md-6";
                                                    $attributevalue2 = $attribute->attribute2;
                                                    $checkBoxprice2 = $attribute->check_box_price2;
                                                    $checkBoxquantity2 = $attribute->check_box_quantity2;
                                                    $checkBoxsku2 = $attribute->check_box_sku2;
                                                    $unit2 = $attribute->unit2;
                                                    if (!(in_array($attribute->option2, $option2))) {
                                                        $option2[] = $attribute->option2;
                                                    }
                                                }
                                                if ($attribute->attribute3) {
                                                    $selectedClass = "col-4 col-md-4";
                                                    $attributevalue3 = $attribute->attribute3;
                                                    $unit3 = $attribute->unit3;
                                                    $attribute->option3;
                                                    if (!(in_array($attribute->option3, $option3))) {
                                                        $option3[] = $attribute->option3;
                                                    }
                                                }
                                            }
                                            ?>
                                            <div class="row quantity-price-bbnr no-mrgn bt-1px bb-1px pt-10  pb-15 mt-20 mb-10">
                                                <?php if (!empty($attributevalue1)) { ?>
                                                    <div class="<?php echo $selectedClass; ?>">
                                                        <div class="form-group clearfix mb-0 ">
                                                            <label for="text"
                                                                   class="mt-10 fnt14  black"><?php echo ucfirst($attributevalue1); ?>
                                                                :</label>
                                                            <select class="form-control variation-change" id="variation1" name="">
                                                                <option value="">Select</option>
                                                                <?php foreach ($option1 as $key => $v1) { ?>
                                                                    <option value="<?php echo $v1; ?>"><?php echo ucfirst($v1); ?></option>
                                                                <?php } ?>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <?php
                                                }
                                                if (!empty($attributevalue2)) {
                                                    ?>
                                                    <div class="{{$selectedClass}}}">
                                                        <div class="form-group clearfix mb-0 ">
                                                            <label for="text"
                                                                   class="mt-10 fnt14  black"><?php echo ucfirst($attributevalue2); ?>
                                                                :</label>
                                                            <select class="form-control variation-change" id="variation2" name="">
                                                                <option value="">Select</option>
                                                                <?php foreach ($option2 as $v2) { ?>
                                                                    <option value="<?php echo $v2 ?>"><?php echo ucfirst($v2); ?></option>
                                                                <?php } ?>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <?php
                                                }
                                                if (!empty($attributevalue3)) {
                                                    ?>
                                                    <div class="{{$selectedClass}}">
                                                        <div class="form-group clearfix mb-0 ">
                                                            <label for="text"
                                                                   class="mt-10 fnt14  black"><?php echo ucfirst($attributevalue3); ?>
                                                                :</label>
                                                            <select class="form-control variation-change" id="variation3" name="">
                                                                <option value="">Select</option>
                                                                <?php
                                                                foreach ($option3 as $v3) {
                                                                    ?>
                                                                    <option value="<?php echo $v3 ?>"><?php echo ucfirst($v3); ?></option>
                                                                <?php } ?>
                                                            </select>
                                                        </div>
                                                    </div>
                                                <?php } ?>
                                            </div>
                                        </div>
                                    <?php } ?>
                                </div><!-- shipping-option-quanity-box -->
                                <!-- quantity-price-bbnr -->
                                <div class="local-taxes-include">
                                    <?php
                                    $productShippingPrice = isset($productShippingPrice) ? $productShippingPrice : 0;
                                    $productShippingPrice_usd = $productShippingPrice / 10;
                                    ?>
                                    <span>Local Taxes include (where applicable)
                                        + Z<span class="shipping_price"> <?php echo isset($productShippingPrice) ? $productShippingPrice : 0; ?> </span> <span class="usdshipping">(USDC {{$productShippingPrice_usd}})</span>shipping
                                    </span>
                                </div>
                                <?php if (!Session::has('userData')) { ?>
                                    <div class="cart-sec-btn">
                                        <a class="primary-btn btn btn-primary cartlogin w100p text-white  background-clr"
                                           data-location="{{URL::to('product/'.$productDetail['slug'])}}"
                                           href="javascript:void(0)" data-toggle="modal" data-target="#login-registerpopup">Add to Cart
                                        </a>
                                    </div>
                                <?php } else if ($productDetail['product_type'] == 'simple' && $productDetail['current_stock'] == 0) {
                                    ?>
                                    <button type="button" class="primary-btn btn btn-primary w100p text-white notinstock  background-clr">Out of stock</button>
                                    <?php
                                } else if (Session::has('userData') && $userId == $productDetail['user_id']) {
                                    ?>
                                    <div class="cart-sec-btn">
                                        <button type="button"
                                                class="primary-btn btn btn-primary w100p text-white  background-clr notbuy">Add To Cart
                                        </button>
                                    </div>
                                <?php } else { ?>
                                    <div class = "cart-sec-btn">
                                        <button class = "btn btn-primary cartadd " data-product-id = "<?php echo $productDetail['product_id']; ?>" data-store-id = "<?php echo $productDetail['store_id']; ?>">Add to cart</button>
                                    </div>
                                <?php } ?>
                                <!--cart-sec-btn -->

                            </div>

                            <!--price-sec -->

                            <?php
                            /* ?><div class = "active-rewar-compaign mt-10 mb-10">
                              <div class = "active-rewar-card text-center">
                              <p>Active Reward Campaign</p>
                              <h3 class = "blue-text mt-10 mb-10">28% Reward</h3>
                              <div class = "dasys-minuts-counter ">
                              <span class = "display-inline-block days-cnt">9 days
                              </span>
                              <span class = "seprator-line"></span>
                              <span class = "display-inline-block hrs-cnt">9 hrs
                              </span>
                              <span class = "seprator-line"></span>
                              <span class = "display-inline-block minuts-cnt">32 min
                              </span>


                              </div><!--dasys-minuts-counter -->
                              </div> */
                            if ($incartProduct > 0) {
                                ?>
                                <p class="pl-xs-1 add-cart">
                                    {{$incartProduct}} people have this in their cart right now
                                </p>
                            <?php }
                            ?>
                            <div class="product-overview">
                                <div class="col-xs-12 pl-lg-0 pr-lg-0">
                                    <h6 class="text-body-larger strong text-gray-darker">Overview</h6>
                                </div>
                                <div class="col-xs-12 pl-md-0" style="font-size: 12px;">
                                    <table class="table-overview">
                                        <tr>
                                            <td class="overview">- Type: </td>
                                            <td class="overview"><b>{{ucfirst($productDetail['product_type'])}}</b></td>
                                            <?php
                                            if (isset($productDetail['product_type']) && $productDetail['product_type'] == 'variant') {
                                                $variableProductInfo = getvariableProduct($productDetail['product_id']);
                                                ?>

                                                <td class="overview">- Sale: </td>
                                                <td class="overview"><b>{{isset($variableProductInfo['total_sales']) ? $variableProductInfo['total_sales'] : '0'}}</b></td>
                                            <?php } else { ?>
                                                <td class="overview">- Sale: </td>
                                                <td class="overview"><b>{{isset($productDetail['sales']) ? $productDetail['sales'] : '0'}}</b></td>
                                            <?php } ?>
                                        </tr>
                                        <tr>
                                            <?php
                                            if (isset($productDetail['product_type']) && $productDetail['product_type'] == 'variant') {
                                                $variableProductInfo = getvariableProduct($productDetail['product_id']);
                                                ?>
                                                <td class="overview">- Stock: </td>
                                                <td class="overview"><b>{{$variableProductInfo['current_stock']}}</b></td>
                                            <?php } else { ?>
                                                <td class="overview">- Stock: </td>
                                                <td class="overview"><b>{{$productDetail['current_stock']}}</b></td>
                                            <?php } ?>
                                            <td class="overview">- Weight: </td>
                                            <td class="overview"><b>{{$productDetail['weight']}} lb</b></td>
                                        </tr>
                                        <!--<tr>
                                        <?php
                                        //if (count($variations) > 0) {
                                        //if (!empty($attributevalue1)) {
                                        ?>
                                                    <td class="overview"><?php //echo ucfirst($attributevalue1) . ': ';            ?></td>
                                                    <td class="overview"><b>
                                        <?php
                                        //foreach ($option1 as $key => $v1) {
                                        //if ($key == 0) {
                                        //echo ucfirst($v1);
                                        //} else {
                                        //echo ', ' . ucfirst($v1);
                                        //}
                                        //}
                                        ?></b>
                                                    </td>
                                                    <td> - </td>
                                        <?php
                                        //}
                                        //}
                                        //if (count($variations) > 0) {
                                        //if (!empty($attributevalue2)) {
                                        ?>
                                                    <td class="overview"><?php //echo ucfirst($attributevalue2) . ': ';            ?></td>
                                                    <td class="overview"><b>
                                        <?php
                                        //foreach ($option2 as $key2 => $v2) {
                                        //if ($key2 == 0) {
                                        //echo ucfirst($v2);
                                        //} else {
                                        //echo ', ' . ucfirst($v2);
                                        //}
                                        //}
                                        ?></b>
                                                    </td>
                                        <?php
                                        //}
                                        //}
                                        ?>
                                        </tr>
                                        <tr>
                                            <td class="overview">
                                        <?php
                                        //if (count($variations) > 0) {
                                        //if (!empty($attributevalue3)) {
                                        //echo ucfirst($attributevalue3) . ': ';
                                        //}
                                        //}
                                        ?>
                                            </td>
                                            <td class="overview"><b>
                                        <?php
                                        //if (count($variations) > 0) {
                                        //if (!empty($attributevalue3)) {
                                        //foreach ($option3 as $key3 => $v3) {
                                        //if ($key3 == 0) {
                                        //echo ucfirst($v3);
                                        //} else {
                                        //echo ', ' . ucfirst($v3);
                                        //}
                                        //}
                                        //}
                                        //}
                                        ?></b>
                                            </td>
                                            <td> - </td>
                                            <td>Supporters:</td>
                                            <td><b>{{count($superSupporters)}} people</b></td>
                                        </tr>-->
                                    </table>
                                </div>
                            </div>
                            <div class="share-with-friends">
                                <div class="share-icons-pro text-center">
                                    <!-- flex-sm ai-fe -->
                                    <div class="social-icons">
                                        <div id="share"></div>
                                    </div>
                                </div> <!--flex-sm jc-sb ai-fe -->
                            </div>
                        </div><!--sec-right -->
                        <aside>

                            <div class="sidebar-option-tab">
                                <?php
                                if (is_object($campaign)) {
                                    $pledges_required = $constants['FLASH_SALE_TARGET'] - $campaign->pledges;
                                    if ($pledges_required <= 0) {
                                        $pledges_perc = '100';
                                        $pledges_required_text = $campaign->pledges . ' Pledges of ' . $constants['FLASH_SALE_TARGET'] . ' Goal';
                                    } else {
                                        $pledges_perc = number_format(($campaign->pledges / $constants['FLASH_SALE_TARGET']) * 100, 2);
                                        $pledges_required_text = number_format($pledges_required, 0) . ' Pledges Required';
                                    }
                                    if ($campaign->product_id == $productDetail['product_id']) {
                                        $main_class = 'green-box';
                                        $cashback_perc = $constants['CHOOSEN_CASHBACK'];
                                    } else {
                                        $main_class = 'blue-box';
                                        $cashback_perc = $constants['STANDARD_CASHBACK'];
                                    }
                                    ?>
                                    <div class="brdr-panel-mbt clearifx goal-bonus-box <?php echo $main_class; ?>">
                                        <div class="golbox-heading ">
                                            <h3>Campaign Goal</h3>
                                        </div><!-- golbox-heading -->
                                        <div class="progress-strip">
                                            <div class="progress">
                                                <div class="progress-bar" role="progressbar" style="width: <?php echo $pledges_perc; ?>%" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100"></div>
                                            </div>
                                            <div class="goal-reached text-center">
                                                <p><?php echo $pledges_required_text; ?></p>

                                            </div>
                                        </div><!-- progress-strip -->
                                        <div class="pledges-needed">
                                            <div class="bonus-takencnt">
                                                <div class="persentage-counter">
                                                    <h1><?php echo $cashback_perc; ?> <span>%</span></h1>
                                                </div>
                                                <div class="persentage-body">
                                                    <p>
                                                        Zit Back on every purchase<br>
                                                        when we reach a target of<br>
                                                        <span class="clr-blue"><?php echo number_format($constants['FLASH_SALE_TARGET'], 0); ?></span> pledges till<br>
                                                        <span class="clr-blue"><?php echo date('jS F', strtotime($campaign->camp_end)); ?></span>
                                                    </p>
                                                </div>
                                            </div>
                                        </div><!-- pledges-needed -->

                                        <?php if (Session::has('userData') && $userId != $productDetail['user_id']) { ?>
                                            <div class="make-pledge">
                                                <span class="token-counter">
                                                    <input id="campaign_pledge" type="number" min="1" max="100" class="" value="" placeholder="Min pledge of 1 Zit">
                                                </span>
                                                <a id="pledge_campaign" data-campaign-id="<?php echo $campaign->store_campaign_id; ?>" href="javascript:;">Pledge For Support</a>
                                            </div><!-- make-pledge -->
                                        <?php } ?>

                                    </div>
                                <?php } ?>


                                <?php /* ?><div class="brdr-panel-mbt clearifx goal-bonus-box blue-box">
                                  <div class="golbox-heading ">
                                  <h3>Goal Bonus for this Brand</h3>
                                  </div><!-- golbox-heading -->
                                  <div class="progress-strip">
                                  <div class="progress">
                                  <div class="progress-bar" role="progressbar" style="width: 80%" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100"></div>
                                  </div>
                                  <div class="goal-reached text-center">
                                  <p>70% Goal Achieved</p>
                                  <h4>21 Days To Go</h4>
                                  </div>
                                  </div><!-- progress-strip -->
                                  <div class="pledges-needed">
                                  <div class="circle-pledges text-center">
                                  <h3>54</h3>
                                  <span>Pledges</span>
                                  <span>needed</span>
                                  </div>
                                  </div><!-- pledges-needed -->
                                  <?php if (Session::has('userData') && $userId == $productDetail['user_id']) { ?>
                                  <div class="make-pledge">
                                  <p>Make a pledge of support</p>
                                  <form action="{{URL::TO('/pledge-amount')}}" method="POST">
                                  <span class="token-counter">
                                  <input type="hidden" name="product_id" value="{{$productDetail['product_id']}}">
                                  <input type="hidden" name="product_slug" value="{{$productDetail['slug']}}">
                                  <input type="number" value="{{$minimumPrice}}" name="pledge_amount" class="tockeninput" placeholder="Min {{$minimumPrice}} $">
                                  </span>
                                  {{ csrf_field() }}
                                  <button type="submit" class="sndtockens">Send Token</button>
                                  </form>
                                  </div><!-- make-pledge -->
                                  <?php } ?>
                                  <hr class="hr-seprator">
                                  <div class="bonus-takencnt">
                                  <div class="persentage-counter">
                                  <h1>40 <span>%</span></h1>
                                  </div>
                                  <div class="persentage-body">
                                  <p>bonus tokens for purchasers of this Brand products if goal is met</p>
                                  </div>
                                  </div><!-- bonus-takencnt -->
                                  </div><?php */ ?>

                            </div><!-- sidebar-option-tab -->
                            <!--related-product-->
                        </aside>
                    </div>
                </div>
            </div>
            <!--row -->
        </div><!--product-detail -->
        <div class = "product-tabs" id="fixme-lab" >
            <div class = "container">
                <div class = "row">
                    <div class = "col-md-12 ">
                        <ul class = "nav nav-tabs" role = "tablist">
                            <li class = "nav-item">
                                <a class = "nav-link " href="#produt-detail" >Product Detail</a>
                            </li>
                            <li class = "nav-item">
                                <a class = "nav-link faqtab" href="#faq" >FAQ</a>
                            </li>
                            <li class = "nav-item">
                                <a class = "nav-link" href="#rating" >Reviews</a>
                            </li>
                            <li class = "nav-item">
                                <a class = "nav-link" href="#supporters">Supporters</a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div><!--product-tabs ends -->
        <?php $short_description = isset($productDetail['short_description']) ? $productDetail['short_description'] : ''; ?>
        <div class = "product-tabs-body">
            <div class = "container-lrge">
                <div class = "row no-mrgn">
                    <!--Tab panes -->
                    <div class = "tab-content col-sm-12">
                        <div role = "tabpanel" class = "tab-pane fade in active  show" id="produt-detail">
                            <div class = "container">
                                <div class = "row">
                                    <div class = "col-sm-8">
                                        <div class = "productDescriptionContainer" >
                                            <h3 class="d-head">Product Detail</h3>
                                            <p><?php echo isset($productDetail['description']) ? $productDetail['description'] : ''; ?></p>
                                        </div>
                                    </div>
                                    <div class="col-sm-4">
                                        <aside>
                                            <?php if (!$relatedProduct->isEmpty()) { ?>
                                                <div class="related-product">
                                                    <h5>Related Products</h5>
                                                    <div class="row">
                                                        <?php foreach ($relatedProduct as $rp) { ?>
                                                            <div class="col-sm-6">
                                                                <div class="prdt-sngle-bx">
                                                                    <div class="card">
                                                                        <?php $productImage = getAttachmentUrlById((int) !empty($rp->feature_image) ? $rp->feature_image : '', 'medium'); ?>
                                                                        <a href="<?php echo URL::to('product/' . $rp->slug); ?>" class="image-container-mbt"> <img src="<?php echo $productImage; ?>" alt="image" class="img-fluid"> </a>
                                                                        <div class="card-body">
                                                                            <h5 class="card-title"><a href="<?php echo URL::to('product/' . $rp->slug); ?>"><?php echo!empty($rp->title) ? substr($rp->title, 0, 35) : ''; ?></a>
                                                                            </h5>
                                                                            <div class="cartsec">
                                                                                <p>
                                                                                    <span class="z-symbl"> USDC
                                                                                        <!-- <img
                                                                                            src=" {{ asset('assets/images/z-symbal.png',env('HTTPS_STATUS')) }}" alt="" class="img-fluid"> -->
                                                                                    </span>
                                                                                    <?php echo!empty($rp->price) ? $rp->price : ''; ?></p>
                                                                            </div><!-- box-description-mbt -->
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        <?php } ?>
                                                    </div>
                                                </div>
                                            <?php } ?>
                                            <!--related-product-->
                                        </aside>

                                    </div>
                                </div>
                            </div>

                        </div>

                        <?php
                        if (!getCurrentUserId()) {
                            $str = 'class="btn btn-primary btn-simple get-p-url"  data-toggle="modal" data-target="#login-registerpopup"';
                        } else {
                            $str = 'class="btn btn-primary aska-question"';
                        }
                        ?>
                        <div role="tabpanel" class="tab-pane fade in active show" id="faq">
                            <div class="container">

                                <h3 class="d-head">FAQ</h3>
                                <div class = "ask-question-section  top-space-remove">
                                    <?php
                                    $counter = 1;
                                    foreach ($faqs as $faq) {
                                        ?>
                                        <div class="ask-repeater">

                                            <h4><span class="q-counter">Q{{$counter}}:</span>
                                                <?php echo $faq->question; ?>
                                            </h4>
                                            <p>
                                                <?php echo $faq->answer; ?>
                                            </p>
                                        </div>
                                        <?php
                                        $counter++;
                                    }
                                    ?>
                                </div><!-- ask-question-section -->
                                <div class="ask-a-question">
                                    <?php if ($counter == 1) { ?>
                                        <!-- <h2>Frequently Asked Question</h2> -->
                                        <p>Looks like there aren't any frequently asked question yet. Ask the store owner directly.</p>
                                    <?php } ?>
                                    <p>
                                        <?php if (!Session::has('userData')) { ?>
                                        <div class="cart-sec-btn">
                                            <a class="primary-btn btn btn-primary cartlogin" style="width:200px !important;"
                                               data-location="{{URL::to('product/'.$productDetail['slug'])}}"
                                               href="javascript:void(0)" data-toggle="modal" data-target="#login-registerpopup">Ask a question
                                            </a>
                                        </div>
                                        <?php
                                    } else {
                                        if (getCurrentUserId() != $productDetail['user_id']) {
                                            ?>
                                            <button <?php echo $str; ?>>Ask a question</button>

                                            <?php
                                        }
                                    }
                                    ?>
                                    </p>
                                </div>
                                <div class="ask-container-box-section row ">
                                    <div class="response-div" id="response-div"></div>
                                    <form>
                                        <div class="card">
                                            <div class="form-group">
                                                <label>
                                                    Question
                                                </label>
                                                <input type="text" id="faqQuestion" class="form-control"   maxlength="500">
                                            </div>
                                            <!--<div class="form-group">
                                                <label>
                                                    Answer
                                                </label>
                                                <input type="text"  id="" class="form-control" max="100">
                                            </div> -->

                                            <div class="add-button-option">
                                                <button  class="btn btn-primary faq-question" id="" type="button" >Submit</button>
                                            </div>
                                        </div>

                                    </form>
                                </div>
                            </div><!-- faq -->
                        </div>
                        <div role="tabpanel" class="tab-pane fade in active show" id="rating">
                            <div class="container">
                                <div class="rating-review-tab">
                                    <h3 class="d-head">Reviews</h3>
                                    <?php
                                    if (!canUserReview($productDetail['product_id'])) {
                                        if (!alreadyReviewed($productDetail['product_id'])) {
                                            ?>
                                            <div class="error-onlysupporters">
                                                <p><span>!</span>Only buyers can rate and review</p>
                                            </div><!-- error-onlysupporters -->
                                            <?php
                                        }
                                    }
                                    if (isset($reviews) && count($reviews) > 0 && canUserReview($productDetail['product_id'])) {
                                        ?>

                                        <div class="describe-experience-sec">
                                            <div class="body-section-mbt">
                                                <p>Describing your experience with this product is important for others</p>
                                                <div class="review-button-post">
                                                    <button class="btn btn-primary" id="rate-review-option">
                                                        Rate & Review
                                                    </button>
                                                </div>

                                            </div>
                                        </div><!-- describe-experience-sec -->
                                    <?php } ?>
                                    <?php
                                    //print_rr($reviews);
                                    if (isset($reviews) && count($reviews) > 0) {
                                        $numer = 0;

                                        $star1_count = 0;
                                        $star2_count = 0;
                                        $star3_count = 0;
                                        $star4_count = 0;
                                        $star5_count = 0;
                                        foreach ($reviews as $rev) {
                                            $numer += $rev['rating'];
                                            if ($rev['rating'] == 1) {
                                                $star1_count = $star1_count + 1;
                                            }
                                            if ($rev['rating'] == 2) {
                                                $star2_count = $star2_count + 1;
                                            }
                                            if ($rev['rating'] == 3) {
                                                $star3_count = $star3_count + 1;
                                            }
                                            if ($rev['rating'] == 4) {
                                                $star4_count = $star4_count + 1;
                                            }
                                            if ($rev['rating'] == 5) {

                                                $star5_count = $star5_count + 1;
                                            }
                                        }
                                        $total = count($reviews);
                                        $star1_count_percentage = round(($star1_count / $total) * 100);
                                        $star2_count_percentage = round(($star2_count / $total) * 100);
                                        $star3_count_percentage = round(($star3_count / $total) * 100);
                                        $star4_count_percentage = round(($star4_count / $total) * 100);
                                        $star5_count_percentage = round(($star5_count / $total) * 100);

                                        $average = round($numer / count($reviews));
                                        ?>
                                        <div class="average-rating-box ">
                                            <div class="table-cell-row left-row">
                                                <div class="star-rating-head">
                                                    <span class="average-rating">Average Rating:</span>
                                                    <span class="star-rating-span">
                                                        <div class="rating-strip">
                                                            <?php
                                                            for ($i = 1; $i <= 5; $i++) {
                                                                if ($i <= $average) {
                                                                    $class = "fa fa-star";
                                                                } else {
                                                                    $class = "fa fa-star-o";
                                                                }
                                                                ?>
                                                                <i class = "<?php echo $class; ?>" aria-hidden = "true"></i>
                                                            <?php } ?>

                                                        </div>
                                                    </span>
                                                    <span class="rating-counter">{{$average}}.0</span>
                                                </div>
                                            </div><!-- table-cell-row -->
                                            <div class="table-cell-row right-row">

                                                <div class="star-row-strip">
                                                    <span class="star-rating-start">
                                                        <span class="sart-counter-mbt">5</span>
                                                        <i class="fa fa-star" aria-hidden="true"></i>
                                                    </span>

                                                    <div class="parent-item-star">
                                                        <span class="progress">
                                                            <div class="progress-bar" role="progressbar" style="width: {{$star5_count_percentage}}%" aria-valuenow="50" aria-valuemin="0" aria-valuemax="100"></div>
                                                        </span>
                                                    </div>
                                                    <span class="counter-words">{{$star5_count}}</span>
                                                </div>
                                                <div class="star-row-strip">
                                                    <span class="star-rating-start">
                                                        <span class="sart-counter-mbt">4</span>
                                                        <i class="fa fa-star" aria-hidden="true"></i>
                                                    </span>

                                                    <div class="parent-item-star">
                                                        <span class="progress">
                                                            <div class="progress-bar" role="progressbar" style="width: {{$star4_count_percentage}}%" aria-valuenow="50" aria-valuemin="0" aria-valuemax="100"></div>
                                                        </span>
                                                    </div>
                                                    <span class="counter-words">{{$star4_count}}</span>
                                                </div>
                                                <div class="star-row-strip">
                                                    <span class="star-rating-start">
                                                        <span class="sart-counter-mbt">3</span>
                                                        <i class="fa fa-star" aria-hidden="true"></i>
                                                    </span>

                                                    <div class="parent-item-star">
                                                        <span class="progress">
                                                            <div class="progress-bar" role="progressbar" style="width: {{$star3_count_percentage}}%" aria-valuenow="50" aria-valuemin="0" aria-valuemax="100"></div>
                                                        </span>
                                                    </div>
                                                    <span class="counter-words">{{$star3_count}}</span>
                                                </div>
                                                <div class="star-row-strip">
                                                    <span class="star-rating-start">
                                                        <span class="sart-counter-mbt">2</span>
                                                        <i class="fa fa-star" aria-hidden="true"></i>
                                                    </span>

                                                    <div class="parent-item-star">
                                                        <span class="progress">
                                                            <div class="progress-bar" role="progressbar" style="width: {{$star2_count_percentage}}%" aria-valuenow="50" aria-valuemin="0" aria-valuemax="100"></div>
                                                        </span>
                                                    </div>
                                                    <span class="counter-words">{{$star2_count}}</span>
                                                </div>
                                                <div class="star-row-strip">
                                                    <span class="star-rating-start">
                                                        <span class="sart-counter-mbt">1</span>
                                                        <i class="fa fa-star" aria-hidden="true"></i>
                                                    </span>

                                                    <div class="parent-item-star">
                                                        <span class="progress">
                                                            <div class="progress-bar" role="progressbar" style="width: {{$star1_count_percentage}}%" aria-valuenow="50" aria-valuemin="0" aria-valuemax="100"></div>
                                                        </span>
                                                    </div>
                                                    <span class="counter-words">{{$star1_count}}</span>
                                                </div>




                                            </div>
                                        </div><!-- average-rating-box -->

                                        <div class="star-rating-head">
                                           <!-- <span class="average-rating">Average Rating</span> -->
                                           <!-- <span class="star-rating-span">
                                                <div class="rating-strip">

                                                    <i class="fa fa-star" aria-hidden="true"></i>
                                                    <i class="fa fa-star" aria-hidden="true"></i>
                                                    <i class="fa fa-star" aria-hidden="true"></i>
                                                    <i class="fa fa-star" aria-hidden="true"></i>
                                                    <i class="fa fa-star-half-o" aria-hidden="true"></i>

                                                </div>
                                            </span>
                                            <span class="rating-counter">{{$numer}}.0</span> -->
                                            <span class="average-rating spacing-left30">Reviews:</span>
                                            <span class="rating-counter">{{count($reviews)}}</span>
                                        </div><!-- star-rating-head -->

                                        <div class="review-section-container">
                                            <?php
                                            foreach ($reviews as $rev) {
                                                $count = $rev['rating'];
                                                $comment = $rev['comment'];
                                                $date = $rev['created_at'];
                                                $user_id = $rev['user_id'];
                                                $user_data = getUserById($user_id);
                                                $user_name = $user_data->name;
                                                $user_avatar = $user_data->user_avatar;
                                                $image_url = '//i0.wp.com/picjumbo.com/wp-content/uploads/HNCK8891-1.jpg?resize=1570,1047&amp;quality=50';
                                                if ($user_avatar != '') {
                                                    $image_url = getAttachmentUrlById((int) $user_avatar, 'media');
                                                }

                                                if (is_string($date)) {
                                                    $timestamp = (strtotime($date));
                                                    $date =date('m/d/Y H:i:s', $timestamp);
                                                }
                                                ?>
                                                <div class = "row-table">
                                                    <div class = "col-sm-2-cell">
                                                        <div class = "image-review-cnt">
                                                            <img src = "{{$image_url}}" alt = "" class = "img-fluid" style = "height: 85px; width: 85px;">

                                                        </div><!--image-review-cnt -->
                                                    </div>
                                                    <div class = "col-sm-10-cell no-padd-left">
                                                        <div class = "body-review-cntiner">
                                                            <div class = "row-review-head">
                                                                <span class = "name-show-rev">{{$user_name}}</span>
                                                                <span class = "seprator-line"> | </span>
                                                                <span class = "date-reivew-rev">{{$date}}</span>
                                                                <span class = "rating-start-right pull-right">
                                                                    <div class = "rating-strip">
                                                                        <?php
                                                                        for ($i = 1; $i <= 5; $i++) {
                                                                            if ($i <= $count) {
                                                                                $class = "fa fa-star";
                                                                            } else {
                                                                                $class = "fa fa-star-o";
                                                                            }
                                                                            ?>
                                                                            <i class = "<?php echo $class; ?>" aria-hidden = "true"></i>
                                                                        <?php } ?>

                                                                    </div>
                                                                </span>
                                                            </div>
                                                            <div class = "body-review-text-bdy">
                                                                <p>{{$comment}}</p>
                                                            </div>
                                                        </div><!--body-review-cntiner -->
                                                    </div>

                                                </div>
                                                <?php
                                            }
                                            ?>
                                        </div><!--review-section-container -->
                                        <?php
                                    } else {
                                        if (canUserReview($productDetail['product_id'])) {
                                            echo '<h2> No reviews found be the first to add review</h2>';
                                        } else {
                                            echo '<h2> No Review Found </h2>';
                                        }
                                    }
                                    ?>
                                    <section class = 'rating-widget mt-30'>
                                        <?php if (canUserReview($productDetail['product_id'])) {
                                            ?>
                                            <div class = "post-review-heading">
                                                <h3>Post Review</h3>
                                            </div>


                                            <form action = "" method = "post">
                                                <div class = "review-body" style="margin-left:0px;">
                                                    <div class = "response"></div>
                                                    <p>Describing your experience with this product or seller</p>
                                                    <textarea class = "form-control" id = "rating-text"></textarea>
                                                    <!--Rating Stars Box -->
                                                    <div class = 'rating-stars clearfix'>
                                                        <div class = "your-rating-headeing">Your Rating:</div>
                                                        <div class = "star-rating-panel-mbt">
                                                            <div class = "star-rating">
                                                                <span class = "fa fa-star-o" data-rating = "1"></span>
                                                                <span class = "fa fa-star-o" data-rating = "2"></span>
                                                                <span class = "fa fa-star-o" data-rating = "3"></span>
                                                                <span class = "fa fa-star-o" data-rating = "4"></span>
                                                                <span class = "fa fa-star-o" data-rating = "5"></span>
                                                                <input type = "hidden" id = "rating_count" name = "rating_count" class = "rating-value" value = "0">
                                                            </div>
                                                        </div>

                                                        <div class = "review-button-post">
                                                            <button class = "btn btn-primary" id = "submit-review">
                                                                Post Your Review
                                                            </button>
                                                            <span>Note: you can post your review and rating only once.</span>
                                                        </div>
                                                    </div>

                                                </div>
                                            </form>
                                        </section>
                                    <?php } ?>
                                </div>
                            </div>
                        </div>
                        <div role="tabpanel" class="tab-pane fade in active show" id="supporters">
                            <div class="container">
                                <div class="supports-sec">
                                    <h3 class="d-head">Supporters</h3>
                                    <?php if ($superSupporters) { ?>
                                        <div class="people-supporting-green hidden">
                                            <h3><?php echo count($superSupporters); ?> people are supporting <?php echo $storeInfo[0]->store_name; ?> that reflects the values of animal lovers </h3>
                                        </div><!-- people-supporting-green -->
                                        <?php /* ?><div class="top-cities">
                                          <h3 class="text-center">Where Backers Come From Top Cities</h3>
                                          <div class="cities-listing">
                                          <div class="city-item">

                                          <span class="city-name">Oregon</span>
                                          <span class="country-name">United States</span>
                                          <span class="total-supports">1 Supporters </span>
                                          </div>
                                          <div class="city-item">

                                          <span class="city-name">Punjab</span>
                                          <span class="country-name">Pakistan</span>
                                          <span class="total-supports">1 Supporters </span>
                                          </div>
                                          </div><!-- cities-listing -->
                                          </div><!-- top-cities --><?php */ ?>
                                    <?php } ?>
                                    <div class="supporting-product">
                                        <?php
                                        if ($superSupporters) {

                                            //print_rr($superSupporters);
                                            ?>
                                            <h3 class="text-center">Here are a few of the people supporting this product</h3>
                                            <div class="supporting-product-body row">

                                                <?php
                                                foreach ($superSupporters as $superSupporter) {
                                                    $user_avatar = '';
                                                    $storeIconData = getZAnimalById($superSupporter->icon_id);
                                                    $zanimalName = $storeIconData->name;
                                                    $iconImageUrl = '';
                                                    if ($superSupporter->user_avatar) $user_avatar = getAttachmentUrlById((int) $superSupporter->user_avatar, 'avatar');
                                                    $icon_image_id = getImageIdByIconId((int) $superSupporter->icon_id);
                                                    if ($icon_image_id) $iconImageUrl = getAttachmentUrlById((int) $icon_image_id, 'icons');
                                                    ?>
                                                    <div class="col-sm-3">
                                                        <div class="card">
                                                            <div class="card-body">
                                                                <div class="supporter-img">
                                                                    <span class="anml anmls anmlsml">

                                                                        <span class="pwr "><?php echo $superSupporter->icon_price; ?></span>
                                                                        <!-- <span class="prg"><span class="up">▲</span> <?php echo $superSupporter->perc_inc_dec; ?>%</span> -->
                                                                        <span class="pwr "><?php echo $superSupporter->icon_price; ?></span>
                                                                        <!-- <span class="prg"><span class="up">▲</span> <?php echo $superSupporter->perc_inc_dec; ?>%</span> -->

                                                                        <a href="<?php echo URL::to('/brand/' . $storeInfo[0]->store_slug); ?>" data-toggle="tooltip" data-placement="bottom" title="<?php echo!empty($zanimalName) ? $zanimalName : ""; ?>"  target="_blank">
                                                                            <img class="img-fluid" src="<?php echo $iconImageUrl; ?>" alt="">

                                                                        </a>
                                                                    </span>
                                                                    <?php if ($user_avatar) { ?>
                                                                        <img src="<?php echo $user_avatar; ?>" alt="" class="img-fluid">
                                                                    <?php } else {
                                                                        ?>
                                                                        <p class="image-outer-mbt">
                                                                            <?php
                                                                            if (!empty($superSupporter->name)) {
                                                                                $name = "";
                                                                                $username = $superSupporter->name;
                                                                                $words = preg_split("/\s+/", $username);
                                                                                foreach ($words as $w) {
                                                                                    $name .= strtoupper($w[0]);
                                                                                }
                                                                                ?>
                                                                            <?php }
                                                                            ?>
                                                                            <span class="user-image">
                                                                                <span class="head-upper-mbt">
                                                                                    <span class="login-name"><?php echo $name; ?></span>
                                                                                </span>
                                                                            </span>
                                                                        </p>
                                                                    <?php } ?>
                                                                </div>
                                                                <div class="text-bpdy">
                                                                    <h4><?php echo $superSupporter->name; ?></h4>
                                                                    <h6><?php /* ?>Supporters 4 Products<?php */ ?></h6>
                                                                   <!-- <a href="/user-profile/<?php echo $superSupporter->user_id; ?>" class="btn btn-primary btn-simple" target="_blank">View Profile</a> -->
                                                                    <?php
                                                                    if (getCurrentUserId()) {
                                                                        if (alreadyPurchased($superSupporter->herd_id)) {
                                                                            ?>
                                                                            <a href="javascript:;" data-herd-id="<?php echo $superSupporter->herd_id; ?>" class="btn btn-primary btn-simpl icon-purchase" data-total="<?php echo $superSupporter->icon_debt + $superSupporter->icon_price; ?>">Add To Your Herd</a>
                                                                            <?php
                                                                        } else {
                                                                            echo ' <a class="btn btn-simple disabled" href="javascript:;">Already in herd</a>';
                                                                        }
                                                                    }
                                                                    ?>
                                                                </div><!-- text-bpdy -->
                                                            </div>
                                                        </div><!-- card -->
                                                    </div>
                                                <?php } ?>
                                                <?php /* ?><div class="col-sm-3">
                                                  <div class="card">
                                                  <div class="card-body">
                                                  <div class="supporter-img">
                                                  <span class="anml anmls anmlsml"> <span class="pwr ">100</span><span class="prg"><span class="up">▲</span> 100%</span><a href="javascript:;">
                                                  <img class="img-fluid" src="images/icon1.png" alt="Project">
                                                  </a>
                                                  </span>
                                                  <img src="https://stage.ecommedge.com/uploads/avatar/HJSmI8pA32ED5Tt_88.png" alt="" class="img-fluid">
                                                  </div>
                                                  <div class="text-bpdy">
                                                  <h4>Asif javaid</h4>
                                                  <h6>Supporters 1 Products</h6>
                                                  <a href="#" class="btn btn-primary btn-simple">View Profile</a>
                                                  </div><!-- text-bpdy -->
                                                  </div>
                                                  </div><!-- card -->
                                                  </div><?php */ ?>
                                            </div><!-- supporting-product-body -->
                                            <?php
                                        } else {
                                            echo '<div class="text-center not-supporting-heading"><h2>No one is supporting this product yet.<span class="block clr-blue"> You can become the first one by pledging  for support using the box on the left.</span></h2></div>';
                                        }
                                        ?>
                                    </div><!-- supporting-product -->
                                </div>
                            </div><!-- supporters -->
                        </div>
                    </div>
                </div>
            </div><!--rating-review-tab -->
        </div>
        </div>
        </div>
        <!--tabs-body -->
        <div class="shipping-gray-sec shipped-box-btm">
            <div class="container">
                <div class="row row-repeater-list">
                    <?php
                    if (!empty($shippingDetails)) {
                        // print_rr($shippingDetails);
                        ?>
                        <div class="col-sm-3">
                            <div class="left-shipping-det">
                                <h4>Shipping Policies: </h4>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="row-list-mbt">
                                <span class="icon-view"></span>
                                <span class="text-pre-vie">
                                    <span class="icn-shp">
                                        <img src="{{ asset('assets/images/ship-icon1.png',env('HTTPS_STATUS')) }}" alt="image" >
                                    </span>
                                    <?php if ($shippingDetails->processing_time_id == 11) { ?>
                                        Preparation takes: Unknown
                                    <?php } else { ?>
                                        Preparation takes <?php echo!empty($shippingDetails->process_start_range) ? $shippingDetails->process_start_range : ''; ?> - <?php echo!empty($shippingDetails->process_end_range) ? $shippingDetails->process_end_range : ''; ?> business  <?php echo!empty($shippingDetails->process_end_range) ? $shippingDetails->process_unit : ''; ?></span>
                                <?php } ?>
                            </div><!--row-list-mbt -->
                            <div class="row-list-mbt">
                                <span class="icon-view"></span>

                                <span class="text-pre-vie">
                                    <span class="icn-shp">
                                        <img src="{{ asset('assets/images/ship-icon2.png',env('HTTPS_STATUS')) }}" alt="image" >
                                    </span>
                                    Arrives from United States</span>
                            </div><!--row-list-mbt -->
                            <div class="row-list-mbt">
                                <span class="text-pre-vie">Estimated Shipping Cost</span>
                                <div class="form-group inline-div">
                                    <select class="form-control" id="" name="">
                                        <option selected="" disabled="">US Only</option>
                                    </select>
                                </div> <!--inline-div -->
                                <?php
                                if (!empty($shippingDetails) && $shippingDetails->is_domesticfree == 1) {
                                    $shipPrice = "Free Shipping in USA";
                                }
//                                else if (!empty($shippingDetails) && $shippingDetails->is_internationalfree == 1) {
//                                    $shipPrice = "Free Shipping in all over the world";
//                                }
                                else {
                                    $productShippingPrice = isset($productShippingPrice) ? "ZIT " . $productShippingPrice : 0;

                                    $shipPrice = !empty($productShippingPrice_usd) ? "(USDC " . $productShippingPrice_usd . ")" : 0;
                                }
                                ?>

                                <span class="shipping-cost">Shipping Cost: <b><?php echo $productShippingPrice . " " . $shipPrice; ?></b></span>
                            </div><!--row-list-mbt -->

                        </div>
                    <?php } ?>
                </div><!--row-repeater-list -->

                <?php
                //$productUserId=$productDetail['user_id'];
                //$storeData=getStoreDataByUserId($productUserId);
                //  print_rr($storeData);
                //echo $storeData->policies;
                ?>

                <div class="row row-repeater-list brdr-remove">
                    <div class="col-sm-3">
                        <div class="left-shipping-det">
                            <h4>Returns &amp; Exchange:</h4>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="row-list-mbt">
                            <p>
                                <span class="text-pre-vie">I gladly accept returns, exchanges and cancellations.</span>
                            </p>
                            <ul>
                                <li>- Just contact me within: 14 days of delivery.</li>
                                <li>- Ship items back to me within: 30 days of delivery.</li>
                                <li>- Request a cancellation: before item has shipped.</li>
                            </ul>
                            <p>

                            </p>
                        </div><!--row-list-mbt -->

                    </div>
                </div><!--row-repeater-list -->
            </div>
        </div>
    </section>
    @section('meta')
    @include('layouts.products-meta')
    @stop
    <div class = "uploadImages" style = "display:none;">
        <span></span>
    </div>
</main>

<!--Content Ends-->
<style>
    hr{ margin-top: 0px;
    }
    iframe {
        height: 80vh;
        width: 80vw;
    }
    .slick-lightbox-close:before{    font-size: 40px;}
    .slick-lightbox .slick-arrow.slick-next:before,.slick-lightbox .slick-arrow.slick-prev:before{
        font: normal normal normal 32px/1 FontAwesome;
    }
    /*    body{ padding-top: 80px;}*/

    .add-cart-notification {
        margin-top: -13px !important;
        text-align: center;
    }
    .add-cart {
        font-size: 12px;
        text-align: center;
    }
    .table-overview .overview {
        width: 25%;
        border-bottom: 1px solid #d3d3d3;
        padding: 5px;
        padding-right: 0;
    }
    .product-overview{
        margin-bottom: 20px
    }
    table.table-overview {
        width: 100%;
    }
    .productDescriptionContainer{
        overflow: hidden;
    }
    .productDescriptionContainer img{
        max-width: 100%
    }
    .has-error{
        border:1px solid red !important;
    }
    .jssocials-share-link {
        border-radius: 50%;
    }

    .supporter-img .anmls{ margin-bottom: 5px;}

</style>
<!--script for slick slider -->
<script src = "https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.8.1/slick.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/jquery.jssocials/1.4.0/jssocials.min.js"></script>
<!--<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/magnific-popup.js/1.1.0/jquery.magnific-popup.min.js"></script>                    -->
<script type="text/javascript" src="https://jongacnik.github.io/slick-lightbox/dist/slick-lightbox.js"></script>

<!--script for countdown js-->
<?php
$time = getFlashSaleDate($productDetail['product_id']);
?>
<script type="text/javascript" src="{{ URL::asset('assets/js/jquery.plugin.min.js') }}"></script>
<script type="text/javascript" src="{{ URL::asset('assets/js/jquery.countdown.new.js') }}"></script>
<script type="text/javascript" src="{{ URL::asset('assets/js/cmGauge.js') }}"></script>
<?php if ($time) { ?>
    <script>
    $(function () {
    var austDay = new Date("<?php echo $time; ?>");
    // austDay = new Date(austDay.getFullYear() + 1, 1 - 1, 1);
    $('#countdown-campaign').countdown({onExpiry: liftOff, until: austDay, format: 'HMS'});
    $('#year').text(austDay.getFullYear());
    });
    function liftOff(){
    window.location.reload();
    }
    </script>
<?php } ?>
<?php
$oncamPaign = campaignEndDate($productDetail['store_id']);
if ($oncamPaign) {
    ?>
    <script>
        $(function () {
        var austDay = new Date("<?php echo $oncamPaign; ?>");
        // austDay = new Date(austDay.getFullYear() + 1, 1 - 1, 1);
        $('#countdown-campaign').countdown({until: austDay, format: 'DHM'});
        $('#year').text(austDay.getFullYear());
        });</script>
<?php }
?>
<script>
    $("#share").jsSocials({
    showLabel: false,
            showCount: false,
            shares: ["facebook", "twitter", "googleplus", "pinterest"]
    });
    // Can also be used with $(document).ready()

    $(document).on("change", ".variation-change", function () {
    var variation1 = $('#variation1').val();
    var variation2 = $('#variation2').val();
    var variation3 = $('#variation3').val();
    var productId = "<?php echo $productDetail['product_id']; ?>"
            var data = {};
    if (variation1 !== "" && variation2 !== "" && variation3 == undefined) {
    data['variation1'] = variation1;
    data['variation2'] = variation2;
    data['productId'] = productId;
    jQuery.ajax({
    'url': '/product/get-variations-info',
            'Type': 'POST',
            'method': 'POSt',
            'data': data,
            'success': function (res) {
            obj = JSON.parse(res);
            if (obj == "") {
            //$(".cartadd").hide();
            $(".cartadd").addClass("disabled");
            jQuery('.empty-response').html("<div class='responsive-body add-cart-notification'>This combination is not available</div>");
            $(".not-available").show();
            $("#prod-price").hide();
            $(".tag-shippingcost").hide();
            jQuery('#variable_product').hide();
            } else {
            $(".cartadd").removeClass("disabled");
            $(".cartadd").show();
            $(".local-taxes-include").show();
            jQuery('.empty-response').html("");
            $(".not-available").hide();
            $("#prod-price").show();
            quantity = $("#product_quantity").val();
            if (quantity == undefined){
            quantity = 1;
            }
            total = (obj[0].price) * quantity;
            console.log(quantity);

            $("#product-price").html("USDC "+total);
            $("#product-price").attr('data-price', obj[0].price);
            if (obj[0].current_stock != ''){
            if (obj[0].current_stock >= 10){
            obj[0].current_stock = 10;
            }
            var options = '';
            for ($i = 1; $i <= obj[0].current_stock; $i++){
            options += '<option value="' + $i + '">' + $i + '</option>'
            }
            jQuery('#product_quantity').html(options);
            jQuery('#variable_product').show();
            }

            }
            }
    });
// sendreq(data, 'product', 'get-variations-info', 'callbackVariations');
    }


    if (variation1 != "" && variation2 == undefined && variation3 == undefined) {
    data['variation1'] = variation1;
    data['productId'] = productId;
    jQuery.ajax({
    'url': '/product/get-variations-info',
            'Type': 'POST',
            'method': 'POSt',
            'data': data,
            'success': function (res) {
            obj = JSON.parse(res);
            if (obj == "") {
            //$(".cartadd").hide();
            $(".cartadd").addClass("disabled");
            jQuery('.empty-response').html("<div class='responsive-body add-cart-notification'>This combination is not available</div>");
            $(".not-available").show();
            $("#prod-price").hide();
            $(".tag-shippingcost").hide();
            jQuery('#variable_product').hide();
            } else {
            $(".cartadd").removeClass("disabled");
            $(".cartadd").show();
            $(".local-taxes-include").show();
            jQuery('.empty-response').html("");
            $(".not-available").hide();
            $("#prod-price").show();
            quantity = $("#product_quantity").val();
            if (quantity == undefined){
            quantity = 1;
            }
            total = parseFloat(obj[0].price) * quantity;
            $("#product-price").html("USDC "+total);
            $("#product-price").attr('data-price', obj[0].price);
            }
            if (obj[0].current_stock != ''){
            if (obj[0].current_stock >= 10){
            obj[0].current_stock = 10;
            }
            jQuery('.price-sec .stock-remaining span').html(obj[0].current_stock + ' instock');
            var options = '';
            for ($i = 1; $i <= obj[0].current_stock; $i++){
            options += '<option value="' + $i + '">' + $i + '</option>'
            }
            jQuery('#product_quantity').html(options);
            jQuery('#variable_product').show();
            }

            }
    });
// sendreq(data, 'product', 'get-variations-info', 'callbackVariations');
    }
    if (variation1 != "" && variation2 != "" && variation3 != "") {
    data['variation1'] = variation1;
    data['variation2'] = variation2;
    data['variation3'] = variation3;
    data['productId'] = productId;
    jQuery.ajax({
    'url': '/product/get-variations-info',
            'Type': 'POST',
            'method': 'POSt',
            'data': data,
            'success': function (res) {
            obj = JSON.parse(res);
            if (obj == "") {
            //$(".cartadd").hide();
            $(".cartadd").addClass("disabled");
            jQuery('.empty-response').html("<div class='responsive-body add-cart-notification'>This combination is not available</div>");
            jQuery('#variable_product').hide();
            $(".not-available").show();
            $("#prod-price").hide();
            $(".tag-shippingcost").hide();
            } else {
            $(".cartadd").removeClass("disabled");
            $(".cartadd").show();
            $(".local-taxes-include").show();
            jQuery('.empty-response').html("");
            $(".not-available").hide();
            $("#prod-price").show();
            quantity = $("#product_quantity").val();
            if (quantity == undefined){
            quantity = 1;
            }
            total = parseFloat(obj[0].price) * quantity;
            $("#product-price").html("USDC "+total);
            $("#product-price").attr('data-price', obj[0].price);
            if (obj[0].current_stock != ''){
            if (obj[0].current_stock >= 10){
            obj[0].current_stock = 10;
            }
            var options = '';
            for ($i = 1; $i <= obj[0].current_stock; $i++){
            options += '<option value="' + $i + '">' + $i + '</option>'
            }
            jQuery('#product_quantity').html(options);
            jQuery('#variable_product').show();
            }
            }
            }
    });
// sendreq(data, 'product', 'get-variations-info', 'callbackVariations');
    }

    });
    $(document).on("click", '.cartadd', function () {
    has_error = false;
    var optionArray = [];
    jQuery('.variation-change').each(function () {
    val = jQuery(this).val();
    if (val == '') {
    jQuery(this).addClass('has-error');
    has_error = true;
    } else {
    optionArray.push($(this).val());
    jQuery(this).removeClass('has-error');
    }

    });
//        console.log(optionArray);
//        return false;
    var data = {};
    if (optionArray != "") {
    data['vartion_options'] = optionArray;
    }
    data['product_id'] = $(this).attr('data-product-id');
    data['store_id'] = $(this).attr('data-store-id');
    data['product_quantity'] = $("#product_quantity").val();
    if (!has_error) {
// jQuery('.uploadImages').show();

    sendreq(data, 'cart', 'add', 'callbackBasket');
    }
    });
    function callbackBasket(data) {
    $(".cartmenu").html(data);
    $("html, body").animate({scrollTop: 0}, "slow");
//  jQuery('.uploadImages').hide();
    $(".cart-list-nav").addClass("bounce-up");
    setTimeout(function () {
    $(".cart-list-nav").removeClass("bounce-up");
    }, 500);
    $("#view_cart").show();
    }

    $(document).on('click', '.adfvt', function () {
    current = $(this);
    var data = {};
    data['product_id'] = $(this).attr('data-product-id');
    sendreq(data, 'favourite', 'add', 'callbackfavourites');
    $(this).toggleClass("animate");
    });
    function callbackfavourites(data) {

    }
    $(document).on('change', '#product_quantity', function () {
    $(".large-loader").show();
    var data = {};
    data['product_id'] = $(this).attr('data-prodcut-id');
    data['quantity'] = $(this).val();
    price = $('#product-price').attr('data-price');
    quantity = $(this).val();
    total = parseFloat(price) * quantity;
    $('#product-price').html("USDC "+total.toFixed(2));
    usdprice = parseFloat(total / 10).toFixed(2);
    $(".usdprice").html('(USDC ' + usdprice + ')');
    $(".large-loader").hide();
   // sendreq(data, 'shipping', 'calculate', 'callbackship');
    });
    function callbackship(data) {
    usdshipping = parseFloat(data / 10).toFixed(2);
    $(".usdshipping").html('(USDC ' + usdshipping + ')');
    $(".shipping_price").html(data);
    $(".large-loader").hide();
    }
    $('.flexslider-mobile').flexslider({
    animation: "slide"
    });
    $(document).on('click', '.notbuy', function () {
    //alert("You cannot buy your own product");
    swal("You cannot buy your own product");
    });
    $('.slider-for').slick({
    dots: false,
            infinite: true,
            speed: 500,
            fade: true,
            cssEase: 'linear'
    });
//$('.slider-for').slickLightbox({
//        itemSelector: 'a',
//        centerMode: true,
//        dots: true,
//        centerMode: true,
//        navigateByKeyboarditemSelector: 'a',
//        centerMode: true,: true
//});


    /**
     * Custom Slick init to load/unload iframe src on events
     */
    $('.slider-for').slickLightbox({
    itemSelector: 'a',
            centerMode: true,
            navigateByKeyboarditemSelector: 'a',
            closeOnBackdropClick : false,
            slick : function ($e) {

            $e.find('.slick-lightbox-slick-iframe').each(function () {
            $(this)
                    .attr('data-src', $(this).attr('src'))
                    .attr('src', '')
            })

                    function clearIframe (slick, index) {
                    var $iframe = $(slick.$slides.get(index)).find('.slick-lightbox-slick-iframe')
                            if ($iframe.length) {
                    setTimeout(function () {
                    $iframe.attr('src', '')
                    }, slick.options.speed)
                    }
                    }

            function loadIframe (slick, index) {
            var $iframe = $(slick.$slides.get(index)).find('.slick-lightbox-slick-iframe')
                    if ($iframe.length) $iframe.attr('src', $iframe.attr('data-src'))
            }

            /**
             * Return slick instance
             */
            return $e.find('.slick-lightbox-slick')
                    .on('init', function (event, slick) {
                    loadIframe(slick, slick.currentSlide)
                    })
                    .on('beforeChange', function (event, slick, currentSlide, nextSlide) {
                    clearIframe(slick, currentSlide)
                            loadIframe(slick, nextSlide)
                    })
                    .slick()
            }
    });</script>
<script>
    jQuery(document).ready(function () {
    if (history.length == 1 || history.length == '1') {
    jQuery('.gobackbutton').hide();
    } else {
    jQuery('.gobackbutton').show();
    }
    });
    function goBack() {

    window.history.back();
    }

    jQuery(document).on('click', '.faq-question', function () {
    question = jQuery('#faqQuestion').val();
    if (question == '') {
    jQuery('#faqQuestion').addClass('has-error');
    } else {
    jQuery('#faqQuestion').removeClass('has-error');
    jQuery.ajax({
    url: '{{route("ask_question")}}',
            data: 'question=' + question + '&product_id={{$productDetail["product_id"]}}',
            method: 'POST',
            success: function (res) {

            if (res.status == 'success') {
            jQuery('.response-div').html('<div class="alert alert-success">' + res.message + '</div>');
            window.setTimeout(function() {
            $(".alert-success").fadeTo(500, 0).slideUp(500, function(){
            //$(this).remove();
            });
            }, 4000);
            jQuery('#faqQuestion').val('');
            }
            if (res.status == 'error') {
            jQuery('.response-div').html('<div class="alert alert-danger">' + res.message + '</div>');
            }
            }

    });
    }
    });
    $(document).ready(function () {
    var $star_rating = $('.star-rating .fa');
    var SetRatingStar = function () {
    return $star_rating.each(function () {
    if (parseInt($star_rating.siblings('input.rating-value').val()) >= parseInt($(this).data('rating'))) {
    return $(this).removeClass('fa-star-o').addClass('fa-star');
    } else {
    return $(this).removeClass('fa-star').addClass('fa-star-o');
    }
    });
    };
    $star_rating.on('click', function () {
    $star_rating.siblings('input.rating-value').val($(this).data('rating'));
    return SetRatingStar();
    });
    SetRatingStar();
    $(document).ready(function () {

    });
    });
    jQuery(document).on('click', '#submit-review', function (event) {
    comment = jQuery('#rating-text').val();
    jQuery('#rating-text').removeClass('has-error');
    event.preventDefault();
    rating_count = jQuery('#rating_count').val();
    if (rating_count < 1){
    swal("Please select at least one star");
    return false;
    }
    if (comment == '') {
    jQuery('#rating-text').addClass('has-error');
    }
    if (rating_count != '' && comment != '') {
    jQuery(this).show();
    jQuery("#submit-review").hide();
    //comment='';
    jQuery.ajax({
    url: "/add-review",
            data: 'product_id={{$productDetail['product_id']}}&author_id={{getCurrentUserId()}}&rating_count=' + rating_count + '&comment=' + comment + '&action=add_review',
            method: 'POST',
            success: function (res) {
            var obj = jQuery.parseJSON(res);
            jQuery('.response').html(obj.message);
            setTimeout(function () {
            location.reload();
            }, 1000);
            }
    });
    }
    });
    $('.ask-a-question .aska-question').on("click", function () {
    $(this).parent().parent().hide();
    $(this).parent().parent().parent().addClass('show-hide-div');
    });
    $("#rate-review-option").click(function () {
    $('html, body').animate({
    scrollTop: $("#rating-text").offset().top - 80
    }, 800);
    });</script>
<script>
    jQuery(document).on('click', '.sndtockens', function(e){
    var min = {{$minimumPrice}};
    var val = parseInt(jQuery('.tockeninput').val());
    if (val < min)
    {
    event.preventDefault();
    alert('value should be greater or equal to ' + min);
    return false;
    }
    });
    $(function(){

    var note = $('#note'),
            ts = new Date(2012, 0, 1),
            newYear = true;
    if ((new Date()) > ts){
    // The new year is here! Count towards something else.
    // Notice the *1000 at the end - time must be in milliseconds
    ts = (new Date()).getTime() + 10 * 24 * 60 * 60 * 1000;
    newYear = false;
    }


    });
    /*
     jQuery('input[type=number]').on('mouseup keyup', function () {
     jQuery(this).val(Math.min({{$minimumPrice}}, Math.max(5000000000000, jQuery(this).val())));
     });
     */
    var pledging = 'no';
    jQuery(document).on('click', '#pledge_campaign', function(e){
    if (pledging == 'yes')
            return;
    var campaign_id = jQuery(this).data('campaign-id');
    var pledge_amount = parseInt(jQuery('#campaign_pledge').val());
    if (!pledge_amount || pledge_amount < 1){
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
            'success': function(res){
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
    $(document).on('click', '.notinstock', function () {
    alert("product is out of stock");
    });
    $(window).on("load resize scroll", function(e){
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
    $("#fixme-lab").css({

    });
    }
    });
    $(document).ready(function() {
    $('html, body').animate({
    scrollTop: $(window).height() - 27000
    }, 500, function(){

    //window.location.hash = hash;
    //scrollTop: $(hash).offset().top -130
    });
    });
    $(document).on('keyup', function(evt) {
    if (evt.keyCode == 27) {
    $("body").removeClass('overfollow-hidden');
    }
    });
    $(document).ready(function(){
    $('body').scrollspy({target: ".nav-tabs", offset: 130});
    $(".product-tabs li  a[href^='#']").on('click', function(e) {
    // prevent default anchor click behavior
    e.preventDefault();
    // store hash
    var hash = this.hash;
    // animate
    $('html, body').animate({
    scrollTop: $(hash).offset().top - 126
    }, 600, function(){

    //window.location.hash = hash;
    scrollTop: $(hash).offset().top - 126
    });
    });
    });
//    $(function () {
//    $("button").click(function () {
//    var randomNum = Math.floor((Math.random() * 100));
//    $('#pro-gauge .gauge-arrow').trigger('updateGauge', randomNum);
//    });
//    $('#pro-gauge .gauge-arrow').cmGauge();
//    });


    $(function () {
    var randomNum = Math.floor((Math.random() * 100));
    $('#pro-gauge .gauge-arrow').trigger('updateGauge', randomNum);
    $('#pro-gauge .gauge-arrow').cmGauge();
    });
    $('[data-toggle="tooltip"]').tooltip();
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
            success: function (res) {
            if (res.status == 'success') {
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
            swal("", res.message, "success");
            //setTimeout(function(){
            //location.reload();
            //}, 1000);
            } else {
            if (res.message == 'Icon already purchased')
                    jQuery(current_actions).html('');
            swal("", res.message, "error");
            }
            }
    });
    }
    jQuery(document).ready(function () {
    jQuery('.icon-purchase').on('click', function (event) {
    total_to_pay = jQuery(this).attr('data-total');
    that = this;
    swal({
    title: "Are you sure?",
            text: "Your wallet will be debited by " + total_to_pay + " Zit",
            type: "warning",
            showCancelButton: true,
            confirmButtonClass: "btn-success",
            confirmButtonText: "Yes, Do it!",
            closeOnConfirm: false
    },
            function () {
            icon_purchase(that);
            });
    });
    });
    $('.product-detail-img-cnt .flexslider .slick-next,.product-detail-img-cnt .flexslider .slick-prev').click(function(){

//      $('.youtube-video').each(function(){
//        var el_src = $(this).attr("src");
//        $(this).attr("src",el_src);
//      });
    });</script>
<script>
    gtag('event', 'view_item', {
    "items": [
    {
    "id": "<?php echo $productDetail['product_id']; ?>",
            "name": "<?php echo $title; ?>",
            "list_name": "Product Detail",
            "brand": "<?php echo $storeInfo[0]->store_name; ?>",
            "list_position": 1,
            "quantity": <?php echo $productDetail['current_stock']; ?>,
            "price": '<?php echo $productDetail['price']; ?>'
    }
    ]
    });
</script>
@endsection
