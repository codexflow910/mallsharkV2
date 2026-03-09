<?php
// $store_info = getStoreInfoById($productDetail['store_id']);
// $storename =  $storeInfo[0]->store_name;
// $description = 'Use Crypto to experience quality and reliablity with the '.$productDetail['title'].' from '.$storename.'. Our online shop accepts cryptocurrency (i.e. Bitcoin, Ethereum).';
// $keyword = ''.$productDetail['title'].', buy '.$productDetail['title'].'';
// echo 'Data: <pre>' .print_r($productDetail['description'],true). '</pre>'; die();

use Facade\Ignition\DumpRecorder\Dump;
use Carbon\Carbon;
$PshortDes = !empty($productDetail['short_description']) ? strip_tags($productDetail['short_description']) : ($productDetail['description'] ? strip_tags($productDetail['description']) : 'Discover the future of shopping with MallShark! Our innovative e-commerce platform offers a streamlined way to shop for goods and services. Join us today for a modern and hassle-free shopping experience.');
$ecoFriendlyTags = isset($productDetail['eco_friendly']) ? json_decode($productDetail['eco_friendly'], true) : [];
?>
@extends('layouts.frontend-main')
<!-- Main Banner Starts -->
@section('content')
@section('title', $productDetail['title'])
@section('description', $PshortDes)
@section('google-analytics')
    @include('layouts.google-analytics')
@stop
<link rel="stylesheet" type="text/css"
    href="https://cdnjs.cloudflare.com/ajax/libs/fancybox/3.5.7/jquery.fancybox.min.css" />
<link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/flexslider/2.7.0/flexslider.css"
    media="all" />
<!-- for magnifier popup for desktop version-->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.9.0/slick.min.css" />
<!--<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/magnific-popup.js/1.1.0/magnific-popup.min.css"/>-->
<link rel="stylesheet" href="https://mreq.github.io/slick-lightbox/dist/slick-lightbox.css" />
<link type="text/css" rel="stylesheet" href="https://cdn.jsdelivr.net/jquery.jssocials/1.4.0/jssocials.css" />
<link type="text/css" rel="stylesheet"
    href="https://cdn.jsdelivr.net/jquery.jssocials/1.4.0/jssocials-theme-flat.css" />

<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/flexslider/2.7.1/jquery.flexslider-min.js">
</script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/fancybox/3.5.7/jquery.fancybox.min.js"></script>
<script async src="https://static.addtoany.com/menu/page.js"></script>
<script>
    var almostSoldOutHandler;
</script>

<style>

    .current-stock-deactive {
        pointer-events: none;
    }

    .opecity-deactive {
        opacity: 0.5;
    }

    .sale-price {
        font-weight: 400 !important;
        font-size: 13px;
    }

    .red-color {
        /* color: #4a69f1; */
    }

    .productDetailCOntainerMain button.slick-arrow {
        position: absolute;
        bottom: -30px;
        z-index: 12;
        background: none;
        border: 0;
        padding: 0;
        width: 14px;
        height: 14px;
        background-size: contain;
        background-repeat: no-repeat;
        background-position: center;
    }

    #mallshark-sold-out-badge{
        margin-top: 14px;
        display: flex;
        justify-content: end;
    }
    #mallshark-sold-out-badge>span{
        color: #ff1673;
        font-size: 16px;
        background: none;
        font-weight: 600;
    }
    button.slick-next.slick-arrow {
        right: 36%;
        background-image: url(/assets/images/right-arrow.svg);
        width: 14px;
        height: 14px;
    }

    button.slick-prev.slick-arrow {
        background-image: url(/assets/images/left-arrow.svg);
        left: 36%;
        width: 14px;
        height: 14px;
    }

    .productDetailCOntainerMain button.slick-arrow {
        text-indent: -99999999px;
    }

    .slider-counter.caption {
        text-align: center;
        font-size: 10px;
        padding-top:15px;
    }

    .mallSharkSelectBAdgeToolTip{
        position: absolute;
        background: #f0f0fa;
        border-radius: 3px;
        padding: 5px;
        top: 29px;
        font-size: 12px;
        left: 110px;
        width:250px;
        border: 1px solid #073ba5;
        display:none;
        z-index:1;
    }
    .custombadge:hover ~ .mallSharkSelectBAdgeToolTip {
    display: block !important;
    cursor: pointer;
}


    ul.accordion-list>li {
        position: relative;
        line-height: 1;
        padding: 1rem 0;
        border-bottom: 1px solid #e3e4e5;
        border-top: .0625rem solid #e3e4e5;

    }

    .dtxt {
        font-size: 1rem;
        font-family: "Montserrat", sans-serif;
    }

    ul.accordion-list>li.no-border-top {
        border-top: 0;
    }
    .container-description p,
        .container-description ul li {
            font-family: "Montserrat", sans-serif;
            line-height: 1.5;
            font-size: 14px;
            font-weight: 400;
        }
        .buyNowBtn{
            background-color: #ca7d00!important;
            border: 1px solid #ca7d00!important;
        }
        .buyNowBtn:hover{
            background-color: inherit!important;
            color:#ca7d00!important;
        }

    @media only screen and (max-width: 767px) {

        .product-detail-page-container .shipping-gray-sec {
            margin-top: 2rem;
        }
        a.product-brand_name {
            font-size: 13px;
            color: #073ba5;
            font-weight: 500;
            font-family: "Roboto", sans-serif;
        }

        .prductdetl-cntr .product-detail-img-cnt {
            margin-left: -15px;
            margin-right: -15px;
        }

        .product-slider-section .title-prp {
            font-family: "Montserrat", sans-serif;
            line-height: 1.2;
            margin-bottom: 0.5rem !important;
        }

        b#product-price,b#sale-price {
            font-size: 18px;
            position: relative;
            top: 0px;
            font-family: "Montserrat", sans-serif;          
            font-weight: 600;
        }

        .notfill-rating {
            width: 16px;
        }

        .product-detail .prddtlinbx .procomttl {
            margin-bottom: 0;
            padding-bottom: 5px;       width: 100%;
        }

        div#variable_product {
            margin-top: 0px;
        }

        h2#about-this-item-section {
            font-size: 18px;
            font-weight: 700;
            font-family: "Montserrat", sans-serif;
        }

        .shipping-option-quanity-box label {
            margin: 0
        }

        .productDescriptionContainer {
            padding-top: 24px !important;
        }



        .rating-review-tab h2 {
            font-size: 14px;
            font-family: "Montserrat", sans-serif;
        }

        .returnExchangeBox h3,
        .returnExchangeBox h5,
        .returnExchangeBox ul li,
        .shrtdesc.not-empty-des p b {
            font-family: "Montserrat", sans-serif;
            line-height: 1.3;
            /* font-size: 18px; */
            margin-bottom: 15px;
        }

        .returnExchangeBox ul li,
        .shrtdesc.not-empty-des p b {
            line-height: 1.3;
            font-size: 14px;
            font-weight: 400;
        }

        .quantitytxt,
        .shipping-option-quanity-box label {
            font-size: 14px;
            font-family: "Montserrat", sans-serif;        margin-bottom: 4px;
        }

        .notinstock_out_of_stock {
            min-height: 46px;
            font-weight: 500;
            font-size: 14PX;
            letter-spacing: .1rem;
            font-family: "Montserrat", sans-serif;
        }

        .productDetailCOntainerMain select.form-control:not([size]):not([multiple]) {
            height: calc(2.78rem + 2px);
            font-family: "Montserrat", sans-serif;
        }

        .productDescriptionContainer ol {
            padding-left: 0px;
        }

        .shrtdesc.not-empty-des {
            padding-left: 10px;
            font-size: 14px;
            font-family: "Montserrat", sans-serif;
        }


        .productDetailCOntainerMain span.zoomerImage {
            position: absolute;
            left: 25px;
            z-index: 1;
            width: 28px;
            height: 28px;
            background: #fff;
            border-radius: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            top: 15px;
        }

        .productDetailCOntainerMain span.zoomerImage img {
            max-width: 18px;
        }


    }

</style>

<?php
$userId = isset(Session::get('userData')['user_id']) ? Session::get('userData')['user_id'] : '';
if (empty($productDetail)) {
    echo '<h1>404</h1>';
    exit();
}
$minimumPrice = round($productDetail['price'] / 2);
$constants = config('constants.shopzit_gamification');
?>
<!-- Content Starts -->
<div>
<input type="hidden" id="productId" value="{{ $productDetail['product_id'] }}" />
<input type="hidden" id="productType" value="{{ $productDetail['product_type'] }}" />
    <div>    
<div class="productDetailCOntainerMain" itemtype="https://schema.org/Product" itemscope>
    <meta itemprop="name" content="<?php echo isset($productDetail['title']) ? $productDetail['title'] : ''; ?>" />
    <meta itemprop="sku" content="<?php echo isset($productDetail['sku']) ? $productDetail['sku'] : ''; ?>" />
    <main class="product-detail prductdetl-cntr">
        <div class="strhd hdr-btFxd" style="display:none">
            <div class="container">
                <div class="row">
                    <div class="col hdlft row">
                        <div class=" ">
                            <?php
                            
                            // start store logo icon
                            $imageUrl = '';
                            if (isset($storeInfo[0]->store_logo) && $storeInfo[0]->store_logo != '') {
                                $imageUrl = getAttachmentUrlById((int) $storeInfo[0]->store_logo, 'medium');
                            }
                            // end store logo icon
                            
                            $iconUrl = '';
                            $store_info = getStoreInfoById($productDetail['store_id']);
                            $store_icon = isset($store_info[0]->store_icon) ? $store_info[0]->store_icon : '';
                            if (!empty($store_icon)) {
                                $image_id = getImageIdByIconId((int) $store_icon);
                                if (!empty($image_id)) {
                                    $iconUrl = getAttachmentUrlById((int) $image_id, 'icons');
                                }
                            }
                            ?>
                            <a href="<?php echo URL::to('/brand/' . $storeInfo[0]->store_slug); ?>"><img width="50" style="margin-right:10px"
                                    src="<?php echo $imageUrl; ?>" alt="{{ $store_info[0]->store_name }}"
                                    title="{{ $store_info[0]->store_name }}"></a>

                            <span class="small">
                                <?php
                                
                                ?> &nbsp; Since <?php
                                $timestamp = strtotime($storeInfo[0]->created_at);
                                echo $date = date('Y', $timestamp);
                                ?> &nbsp;| &nbsp; <?php
                                if ($totalProducts > 1) {
                                    echo $totalProducts . ' items';
                                } else {
                                    echo $totalProducts . ' item';
                                }
                                ?>
                            </span>
                        </div>
                    </div>

                    <div class="col hdrght">
                        <a href="<?php echo URL::to('/brand/' . $storeInfo[0]->store_slug); ?>" class="btn btn-primary btn-simple">Brand Home</a>
                    </div>
                </div>
            </div>
        </div><!-- strhd -->
        <div itemprop="brand" itemtype="https://schema.org/Brand" itemscope>
            <meta itemprop="name" content="<?php echo isset($storeInfo[0]->store_name) ? $storeInfo[0]->store_name : ''; ?>" />
        </div>
        <section>
            @if (Session::has('pledge_added'))
                <p class="alert alert-success">{{ Session::get('pledge_added') }}</p>
            @endif
            <div class="product-slider-section  productDeailV2">
                <?php
                $clstyle = 'hidden';
                if ($incartProduct > 0) {
                    $clstyle = '';
                }
                ?>
                <div class="container pos-rel <?php echo $clstyle; ?>" id="view_cart">
                    <div class="col-sm-12">
                        <div class="view-to-cart d-flex justify-content-between">
                            <span class=" crt-success-messge  align-self-center"> <?php echo isset($productDetail['title']) ? $productDetail['title'] : ''; ?> added to your
                                cart </span>
                            <div class="col-sm-3" style="margin-right: -40px;">
                                <a href="<?php echo URL::to('/cart'); ?>" class="">View cart</a>
                                <a href="<?php echo URL::to('/checkout'); ?>" class="">Check out</a>
                            </div>
                        </div>
                    </div>
                </div>
                <?php $titleProduct = isset($productDetail['title']) ? $productDetail['title'] : '';
                $category_id = getCategoriesByProductID($productDetail['product_id']);
                $productCategory = getCategoryByCateId(isset($category_id[0]) ? $category_id[0] : '');
                ?>
                <div class="container">
                    <div class="prddtlinbx">
                        <div class="search_message alert alert-danger alert-success-mbt col-sm-offset-2 col-sm-8 pun"
                            style="display: none;"></div>
                        <div class="row">

                            <!-- Filter Box Starts -->
                            <div class=" col-lg-6 col-md-6  adjustLargeScreen">

                                <div class="large-loader" style="display: none;">
                                    <span>
                                        <img src=" {{ asset('assets/images/loader-large.svg', env('HTTPS_STATUS')) }}"
                                            alt="icon load" title="icon load" class="img-fluid all-browser">

                                        <img src=" {{ asset('assets/images/ajax-loader.gif', env('HTTPS_STATUS')) }}"
                                            alt="icon load" title="icon load" class="img-fluid IE-loader">
                                    </span>
                                </div>
                                <div class="product-detail-img-cnt product-detail-img-custom-set">
                                    <!-- Place somewhere in the <body> of your page -->
                                    <div class="flexslidereee">


                                        <?php
                                        $fill = "";
                                        if (Session::has('userData')) {
                                            $favProductId = getBuyerFavProducts();
                                            if (!empty($favProductId) && in_array($productDetail['product_id'], $favProductId)) {
                                                $fill = "animate fillheart";
                                            }
                                            ?>

                                        <?php } else {
                                            ?>
                                        <a class="fav-login login-popup"
                                            data-location="{{ URL::to('product/' . $productDetail['slug']) }}"
                                            href="javascript:void(0)" data-toggle="modal"
                                            data-target="#login-registerpopup" style="display:none">
                                            <div class="add-to-fav">
                                                <div class="heart-addtofav fav-add"></div>
                                            </div>
                                        </a>
                                        <?php } 
                                        ?>

                                        <div class="sliderWrapperParent">
                                            <div class="hiddenMobile justforDesktop flexWrapperSlider slider-container">

                                                <div class="slider-counter caption hiddenMobile counterForDesktop d-none">
                                                    <span class="slider-counter--current">1</span>
                                                    <span aria-hidden="true"> / </span>
                                                    {{-- <span class="visually-hidden">of</span> --}}
                                                    <span class="slider-counter--total">4</span>
                                                </div>

                                                <div class="slider-fortttt ">
                                                <div class="slider-for">
                                                    <?php
                                                    if (isset($productDetail['video_url']) && !empty($productDetail['video_url'])) {
                                                    $videoUrlSource = $productDetail['videoUrlSource'];
                                                    $youtubeId = explode("embed/",$videoUrlSource);
                                                    $makeThumb = 'http://img.youtube.com/vi/'.$youtubeId[1].'/hqdefault.jpg';
                                                    ?>
                                                    <div>
                                                        <img src="{{ $makeThumb }}" class="video-btn" data-toggle="modal"
                                                            alt="youtube embaded"
                                                            data-src="{{ $productDetail['videoUrlSource'] }}"
                                                            data-target="#myModal">
                                                        <div class="logo-overlay"><img class="video-btn" data-toggle="modal"
                                                                src="https://www.freeiconspng.com/thumbs/play-button-icon-png/play-button-icon-png-27.png"
                                                                data-src="{{ $productDetail['videoUrlSource'] }}"
                                                                data-target="#myModal" alt="videoLogo"
                                                                data-target="#myModal" /></div>
                                                    </div <?php
                                                    }
                                                    $featureImage = $productDetail['feature_image'];
                                                    if (isset($productDetail['gallery']) && $productDetail['gallery'] != '') {
                                                        $images = unserialize($productDetail['gallery']);
                                                    
                                                    if (!empty($images)) {
                                                        // Create a new array to hold the matching image(s)
                                                        $matchingImages = [];
                                                        $remainingImages = [];
                                                
                                                        foreach ($images as $img) {
                                                            if ($img == $featureImage) {
                                                                // If the image matches the feature image, add it to the matching images array
                                                                $matchingImages[] = $img;
                                                            } else {
                                                                // If it doesn't match, keep it in the original array
                                                                $remainingImages[] = $img;
                                                            }
                                                        }
                                                
                                                        // Combine the matching images and the remaining images
                                                        $images = array_merge($matchingImages, $remainingImages);
                                                    }
                                                    }else{
                                                        $productImage = asset('assets/images/placeholder.jpg');
                                                        echo '<img class="set_variation_image gallery_images"
                                                        src="' . $productImage . '" alt="' . $productDetail['title'] . '"
                                                        title="' . $productDetail['title'] . '">';
                                                    }
    
                                                    // echo 'Data: <pre>' .print_r($images,true). '</pre>'; die();
                                                
                                                    // if(isset($productDetail['gallery']) && $productDetail['gallery'] != ''){
                                                    
                                                        // $images = unserialize($productDetail['gallery']);
                                                    if (!empty($images)) {
                                                        $i = 0;
                                                        foreach ($images as $img) {
                                                            $productImage = getAttachmentUrlById((int) $img, 'product');
                                                            ?> <div
                                                        class="product__media media media--transparent">
                                                    <a itemprop="image" href="<?php echo $productImage; ?>"
                                                        class="item-slick gallery-items" data-fancybox="product-gallery"
                                                        id="{{ $img }}" data-index="{{ $i }}"
                                                        data-image-id="{{ $img }}">
                                                        <img class="set_variation_image gallery_images"
                                                            id="set_variation_image_{{ $img }}"
                                                            src="<?php echo $productImage; ?>"
                                                            alt="{{ $productDetail['title'] }}"
                                                            title="{{ $productDetail['title'] }}"></a>
                                                </div>
                                                <?php
                                                $i++;
                                                        }
                                                    }
                                                
                                                ?>
                                            </div>





                                            </div>



                                            <div class="slider-nav hiddenMobile">

                                                <?php
                                                    if (isset($productDetail['video_url']) && !empty($productDetail['video_url'])) {
                                                        $videoUrlSource = $productDetail['videoUrlSource'];
                                                        $youtubeId = explode("embed/",$videoUrlSource);
                                                        $makeThumb = 'http://img.youtube.com/vi/'.$youtubeId[1].'/hqdefault.jpg';
                                                        ?>
                                                <div>
                                                    <img src="{{ $makeThumb }}" class="video-btn" data-toggle="modal"
                                                        alt="youtube embaded"
                                                        data-src="{{ $productDetail['videoUrlSource'] }}"
                                                        data-target="#myModal">
                                                
                                                </div><?php
                                                        }
                                                
                                                
                                                    if(isset($productDetail['gallery']) && $productDetail['gallery'] != ''){
                                                
                                                        // $images = unserialize($productDetail['gallery']);
                                                        if (!empty($images)) {
                                                            foreach ($images as $img) {
                                                                $productImage = getAttachmentUrlById((int) $img, 'product');
                                                                ?> <a itemprop="image" href="javascript:;"
                                                                class="item-slick">
                                                            <img class="set_variation_image" id="set_variation_image"
                                                                src="<?php echo $productImage; ?>" alt="{{ $productDetail['title'] }}"
                                                                title="{{ $productDetail['title'] }}"></a>
                                                            <?php
                                                                        }
                                                                    }
                                                                }
                                                                ?>
                                                
                                                
                                                </div>
                                            </div>


                                            <div class="slider-fortttt hiddenDesktop">
                                                <?php
                                                if (isset($productDetail['video_url']) && !empty($productDetail['video_url'])) {
                                                $videoUrlSource = $productDetail['videoUrlSource'];
                                                $youtubeId = explode("embed/",$videoUrlSource);
                                                $makeThumb = 'http://img.youtube.com/vi/'.$youtubeId[1].'/hqdefault.jpg';
                                                ?>
                                                <div>
                                                    <img src="{{ $makeThumb }}" class="video-btn" data-toggle="modal"
                                                        alt="youtube embaded"
                                                        data-src="{{ $productDetail['videoUrlSource'] }}"
                                                        data-target="#myModal">
                                                    <div class="logo-overlay"><img class="video-btn" data-toggle="modal"
                                                            src="https://www.freeiconspng.com/thumbs/play-button-icon-png/play-button-icon-png-27.png"
                                                            data-src="{{ $productDetail['videoUrlSource'] }}"
                                                            data-target="#myModal" alt="videoLogo"
                                                            data-target="#myModal" /></div>
                                                </div <?php
                                                }
                                                $featureImage = $productDetail['feature_image'];
                                                if (isset($productDetail['gallery']) && $productDetail['gallery'] != '') {
                                                    $images = unserialize($productDetail['gallery']);
                                                
                                                if (!empty($images)) {
                                                    // Create a new array to hold the matching image(s)
                                                    $matchingImages = [];
                                                    $remainingImages = [];
                                            
                                                    foreach ($images as $img) {
                                                        if ($img == $featureImage) {
                                                            // If the image matches the feature image, add it to the matching images array
                                                            $matchingImages[] = $img;
                                                        } else {
                                                            // If it doesn't match, keep it in the original array
                                                            $remainingImages[] = $img;
                                                        }
                                                    }
                                            
                                                    // Combine the matching images and the remaining images
                                                    $images = array_merge($matchingImages, $remainingImages);
                                                }
                                                }else{
                                                    $productImage = asset('assets/images/placeholder.jpg');
                                                    echo '<img class="set_variation_image gallery_images"
                                                    src="' . $productImage . '" alt="' . $productDetail['title'] . '"
                                                    title="' . $productDetail['title'] . '">';
                                                }

                                                // echo 'Data: <pre>' .print_r($images,true). '</pre>'; die();
                                            
                                                // if(isset($productDetail['gallery']) && $productDetail['gallery'] != ''){
                                                
                                                    // $images = unserialize($productDetail['gallery']);
                                                if (!empty($images)) {
                                                    $i = 0;
                                                    foreach ($images as $img) {
                                                        $productImage = getAttachmentUrlById((int) $img, 'product');
                                                        ?> 
                                                        <div class="product__media media media--transparent">
                                                         <a itemprop="image" href="<?php echo $productImage; ?>"
                                                            class="item-slick gallery-items" data-fancybox="product-gallery"
                                                            id="{{ $img }}" data-index="{{ $i }}"
                                                            data-image-id="{{ $img }}">
                                                            <img class="set_variation_image gallery_images"
                                                                id="set_variation_image_{{ $img }}"
                                                                src="<?php echo $productImage; ?>"
                                                                alt="{{ $productDetail['title'] }}"
                                                                title="{{ $productDetail['title'] }}">
                                                         </a>
                                                        </div>
                                                    <?php
                                                    $i++;
                                                            }
                                                        }
                                                    
                                                    ?>
                                        </div>



   

                                    </div>
                                    <div class="slider-counter caption hiddenDesktop">
                                        <span class="slider-counter--current">1</span>
                                        <span aria-hidden="true"> / </span>
                                        {{-- <span class="visually-hidden">of</span> --}}
                                        <span class="slider-counter--total">4</span>
                                    </div>



                                    <!--<div class="slider-for ">
                                            <a href="https://www.youtube.com/embed/n_dZNLr2cME?rel=0&hd=1" target="_blank"><img src="http://img.youtube.com/vi/gPuI_pbCYOI/0.jpg" alt=""></a>
                                        </div>-->
                                </div>
                            </div><!-- product-detail-img-cnt -->


                            <div class="productDetailContent forMobileOnly hiddenDesktop">

                                <div class="shopNameVen" style="display: flex;">
                                    <h5 class="procomttl caption-with-letter-spacing">
                                        <a class="product-brand_name"
                                            href="<?php echo URL::to('/brand/' . $storeInfo[0]->store_slug); ?>">
                                            <img class="storeIcon" src="{{ URL::asset('assets/images/store-icon.svg') }}" alt="rate">    {{ ucfirst($store_info[0]->store_name) }}</a>
                                    </h5>
                                    <div class="splopt d-flex" style="position:relative; right:6px">
                                        <?php 
                                        $fill = "";
                                        if (Session::has('userData')) {
                                     
                                         $favProductId = getBuyerFavProducts();
                                         if (!empty($favProductId) && in_array($productDetail['product_id'], $favProductId)) {
                                             $fill =  "fillheart";
                                         }?>
                                        <a data-product-id="<?php echo $productDetail['product_id']; ?>"
                                            class="dill adfv adtofv-home <?php echo $fill; ?>"
                                            href="javascript:void(0);"><img class="image-add_tofav-s" style="width:30px; position:relative;"  src="{{ URL::asset($fill ? 'assets/images/heart.svg' : 'assets/images/heart-v2.svg') }}" alt="rate">
                                            </a>
                                            <?php  }else{ 
                                        $currenturl = url()->full();
                                        ?>
                                            <a class=" adfv fav-login login-popup"
                                                data-location="{{ $currenturl }}" href="javascript:void(0)"
                                                data-toggle="modal" data-target="#login-registerpopup">
                                                <img class="image-add_tofav" data-product-id="<?php echo $productDetail['product_id']; ?>"
                                                    style="width:30px; position:relative;"
                                                    src="{{ URL::asset('assets/images/heart-v2.svg') }}"
                                                    alt="rate">

                                            </a>
                                            <?php   } ?>
                                            <a class=" "></a>

                                    </div>
                                </div>






                            </div>


                            <!-- pro-left=lrge -->
                        </div>

                        <!-- Yotube Video Popup Modal Start -->
                        <div class="modal fade" id="myModal" tabindex="-1" role="dialog"
                            aria-labelledby="exampleModalLabel" aria-hidden="true">
                            <div class="modal-dialog" role="document">
                                <div class="modal-content">
                                    <div class="modal-body">
                                        <button type="button" class="close closebutton" data-dismiss="modal"
                                            aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                        <div class="embed-responsive embed-responsive-16by9">
                                            <iframe class="embed-responsive-item" src="" id="video"
                                                allowscriptaccess="always" allow="autoplay"></iframe>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Yotube Video Popup Modal End -->

                        <div class="col-lg-6 col-md-6 adjustLargeScreenTwo">
                            @if ($productDetail['product_type'] != 'variant')
                                 <!-- @dump($productDetail['product_type']) -->
                            <div class="row rowWrapperproduct desktopMobileCommon simple-product-wrapper">
                                @else
                                    <!-- @dump($productDetail['product_type']) -->
                                <div class="row rowWrapperproduct desktopMobileCommon ">
                                    @endif
                                        <div class="col-md-7">
                                            <div class="productInformation">
                                                <div class="prdbxota">
                                                    <h1 class="title-prp"> <?php echo $titleProduct; ?> </h1>
                                                    <div class="d-flex align-items-center startsWrapperparent">
                                                         <?php
                                                            $average = 0;
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
                                                            $average = isset($average) ? $average: ""; ?>
                                                    <span class="starsWrapperParnt">
                                                        <?php
                                                            for ($i = 1; $i <= 5; $i++) {
                                                                if ($i <= $average) {
                                                                    $class = "fill-rating";
                                                                } else {
                                                                    $class = "notfill-rating";
                                                                }
                                                                ?>
                                                        <img class="<?php echo $class; ?>"
                                                            style="width:16px;"
                                                            src="{{ URL::asset('assets/images/rate.svg') }}" alt="rate">
                                                        <?php } ?>
                
                                                    </span>
                
                                                    <?php } else {
                                                        if(count($reviews) > 0){

                                                        
                                                            if (canUserReview($productDetail['product_id'])) { ?>
                                                    <span class="starsWrapperParnt">
                                                        <?php
                                                            for ($i = 1; $i <= 5; $i++) {
                                                                if ($i <= $average) {
                                                                    $class = "fill-rating";
                                                                } else {
                                                                    $class = "notfill-rating";
                                                                }
                                                                ?>
                
                                                        <img class="<?php echo $class; ?>"
                                                            style="width:16px; "
                                                            src="{{ URL::asset('assets/images/rate.svg') }}" alt="rate">
                                                        <?php } ?>
                                                    </span>
                                                    <?php } else { ?>
                                                    <span class="starsWrapperParnt">
                                                        <?php
                                                            for ($i = 1; $i <= 5; $i++) {
                                                                if ($i <= $average) {
                                                                    $class = "fill-rating";
                                                                } else {
                                                                    $class = "notfill-rating";
                                                                }
                                                                ?>
                
                                                        <img class="<?php echo $class; ?>"
                                                            style="width:16px;"
                                                            src="{{ URL::asset('assets/images/rate.svg') }}" alt="rate">
                                                        <?php }} ?>
                                                    </span>
                                                    <?php }
                                                        }
                                                        ?>
                                                        @if ($productDetail['product_type'] == 'variant')
                                                        @php
                                                            $variableData = getvariableProduct($productDetail['product_id']);
                                                        @endphp
                                                        @if($variableData['total_sales'] >= 2)
                                                        <!-- <span class="soldItems">Sold: {{ $variableData['total_sales'] }}</span> -->
                                                        @endif
                                                    @else
                                                        @if($productDetail['sales'] >= 2)
                                                            <!-- <span class="soldItems">Sold: {{ $productDetail['sales'] }}</span> -->
                                                        @endif
                                                    @endif
                                                    
                                                    </div>
                                                    <span class="product_id"
                                                        data-product-id="{{ $productDetail['product_id'] }}"></span>
                                                    <span class="product_category"
                                                        data-category= "{{ isset($productCategory['categoryTitle']) ? $productCategory['categoryTitle'] : '' }}"></span>
                                                    <div class="pull-left-large fnt24">
                                                        <h2 id="prod-price">
                                                            @if ($productDetail['product_type'] != 'variant')
                                                                @if ($productDetail['on_sale'] == 1)
                                                                    <b class="red-color" id="sale-price"
                                                                        data-price=" <?php echo $productDetail['sale_price']; ?>">$<?php echo isset($productDetail['sale_price']) ? number_format($productDetail['sale_price'], 2) : ''; ?></b>
                                                                    <div class="priceDiv salePrice">
                                                                        <del class="sale-price" id="product-price"
                                                                            data-price=" <?php echo $productDetail['price']; ?>">$<?php echo isset($productDetail['price']) ? number_format($productDetail['price'], 2) : ''; ?></del>
                                    
                                                                        @if($productDetail['discount_percentage'] > 0)
                                                                        <b class="red-color offPricePercentage" id="sale-price"
                                                                        data-price=" <?php echo $productDetail['discount_percentage']; ?>">-
                                                                        <?php echo isset($productDetail['discount_percentage']) ? round(floatval($productDetail['discount_percentage'])) : ''; ?>%</b>
                                                                        @else 
                                                                        <b class="red-color offPricePercentage" id="sale-price"
                                                                        data-price=" <?php echo $productDetail['discount_percentage']; ?>">
                                                                        <?php echo isset($productDetail['discount_percentage']) ? round(floatval($productDetail['discount_percentage'])) : ''; ?>%</b>
                                                                        @endif
                                                                    </div>
                                                                @else
                                                                    <b id="product-price"
                                                                        data-price=" <?php echo $productDetail['price']; ?>">$<?php echo isset($productDetail['price']) ? number_format($productDetail['price'], 2) : ''; ?></b>
                                                                @endif
                                                            @else
                                                                @if ($productDetail['on_sale'] == 1)
                                                                <div class="priceDiv priceONSale">
                                                                    <b class="red-color" id="sale-price"
                                                                        data-price=" <?php echo $productDetail['sale_price']; ?>">$<?php echo isset( $productDetail['sale_price']) ? number_format( $productDetail['sale_price'], 2) : ''; ?></b>

                                                                    <div class="priceONsaleInnerWrapper">
                                                                        <del class="sale-price cuttnigPriceDisplay" id="product-price"
                                                                        data-price=" <?php echo  $productDetail['price']; ?>">$<?php echo isset( $productDetail['price']) ? number_format( $productDetail['price'], 2) : ''; ?></del>
                                                                        <span class="sepratorLine"></span>
                                                                        @if($productDetail['discount_percentage'] > 0)
                                                                            <b class="red-color offPricePercentage" id="sale-price"
                                                                                data-price=" <?php echo $productDetail['discount_percentage']; ?>">-
                                                                                <?php echo isset($productDetail['discount_percentage']) ? round(floatval($productDetail['discount_percentage'])) : ''; ?>% off</b>
                                                                        @else
                                                                            <b class="red-color offPricePercentage" id="sale-price"
                                                                            data-price=" <?php echo $productDetail['discount_percentage']; ?>">
                                                                            <?php echo isset($productDetail['discount_percentage']) ? round(floatval($productDetail['discount_percentage'])) : ''; ?>% off</b>
                                                                        @endif
                                                                        </div>
                                                                    </div>
                                                                    @else
                                                                    <b id="product-price"
                                                                                data-price="<?php echo isset($variations[0]) ? $variations[0]->price : $productDetail['price']; ?>">
                                                                                    $<?php echo isset($variations[0]) ? number_format($variations[0]->price, 2) : number_format($productDetail['price'], 2); ?>
                                                                                </b>
                                                                    @endif
                                                                @endif
                                                            </h2>
                                                            @if($productDetail['product_type'] != 'variant' && $productDetail['current_stock'] > 0)
                                                            <h3 class="text-success" style="font-size: 18px;line-height:24px;color:#007600!important;">In Stock</h3>
                                                            @elseif($productDetail['product_type'] == 'variant')
                                                            <h3 class="text-success variation-instock d-none" style="font-size: 18px;line-height:24px;color:#007600!important;">In Stock</h3>
                                                            @endif
                                                            @if ($productDetail['mallshark_selected'] == 1)
                                                                <div class="mb-1 " style="position:relative;">
                                                                    <span class="badge badge-info custombadge">MallShark Select</span>
                                                                    <span class="mallSharkSelectBAdgeToolTip">MallShark Select is our premium range of products, meticulously curated and thoroughly vetted through a rigorous quality assurance process.</span>
                                                                </div>
                                                            @endif
                                                            
                                                            {{-- <h3 class="not-available" style="display:none;"></h3> --}}
                                                            <div>
                                                                <div class="local-taxes-include">
                                                                    <?php
                                                                    $productShippingPrice = isset($productShippingPrice) ? $productShippingPrice : 0;
                                                                    $productShippingPrice_usd = $productShippingPrice / 10;
                                                                    $shortDescription = isset($productDetail['short_description']) ? $productDetail['short_description'] : '';
                                                                    ?>
                                                                    @if($productDetail['shipping_option_id'] == 221)
                                                                    <h3>Take advantage of fast, no-cost delivery!</h3>
                                                                    @endif
                                                                    <!-- <span>Local Taxes include (where applicable)
                                                                        + <span class="usdshipping">(
                                                                            ${{ $productShippingPrice_usd }})</span>shipping
                                                                    </span> -->
                                                                </div> 
                                                            </div> 
                                                        </div>
                                                     

                
                                                </div>
                                                {{-- @dd($shortDescription) --}}
                                                <div class="shrtdesc"><?php echo stripslashes($shortDescription); ?></div>
                                                <div class="shipping-option-quanity-box">
                                                    <?php
                                                            if (count($variations) > 0) {
        
                                                            $attribute1 = $attribute2 = $attribute3 = $option1 = $option2 = $option3 = array();
                                                            $attributevalue1 = $attributevalue2 = $attributevalue3 = $unit1 = $unit3 = $unit2 = '';
                                                            $checkBoxprice1 = $checkBoxprice2 = $checkBoxprice3 = $checkBoxquantity1 = $checkBoxquantity2 = $checkBoxquantity3 = $checkBoxsku1 = $checkBoxsku2 = $checkBoxsku3 = '';
                                                            $selectedClass = "";
                                                            foreach ($variations as $key => $attribute) {
                                                                if ($attribute->attribute1) {
                                                                    $selectedClass = "col-12 col-md-12";
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
                                                    <div class="row quantity-price-bbnr no-mrgn align-items-center"
                                                        style="margin-top: 0px!important;">
                                                        <?php if (!empty($attributevalue1)) { ?>
                                                        <div class="<?php echo $selectedClass; ?>">
                                                            <div class="form-group clearfix mb-0 ">
                                                                <label for="text"
                                                                    class="fnt14  black"><?php echo ucfirst($attributevalue1); ?>
                                                                    :</label>
                                                                <select class="form-control variation-change" id="variation1"
                                                                    name="">
                                                                    <option value="">Select</option>
                                                                    <?php foreach ($option1 as $key => $v1) { ?>
                                                                    <option value="<?php echo htmlspecialchars($v1, ENT_QUOTES, 'UTF-8'); ?>">
                                                                        <?php echo ucfirst($v1); ?></option>
                                                                    <?php } ?>
                                                                </select>
                                                            </div>
                                                        </div>
                                                        <?php
                                                        }
                                                        if (!empty($attributevalue2)) {
                                                            ?>
                                                        <div class="{{ $selectedClass }}">
                                                            <div class="form-group clearfix mb-0 ">
                                                                <label for="text"
                                                                    class="fnt14  black"><?php echo ucfirst($attributevalue2); ?>
                                                                    :</label>
                                                                <select class="form-control variation-change" id="variation2"
                                                                    name="">
                                                                    <option value="">Select</option>
                                                                    <?php foreach ($option2 as $v2) { ?>
                                                                    <option value="<?php echo htmlspecialchars($v2, ENT_QUOTES, 'UTF-8'); ?>">
                                                                        <?php echo ucfirst($v2); ?></option>
                                                                    <?php } ?>
                                                                </select>
                                                            </div>
                                                        </div>
                                                        <?php
                                                        }
                                                        if (!empty($attributevalue3)) {
                                                            ?>
                                                        <div class="{{ $selectedClass }}">
                                                            <div class="form-group clearfix mb-0 ">
                                                                <label for="text"
                                                                    class="fnt14  black"><?php echo ucfirst($attributevalue3); ?>
                                                                    :</label>
                                                                <select class="form-control variation-change" id="variation3"
                                                                    name="">
                                                                    <option value="">Select</option>
                                                                    <?php
                                                                        foreach ($option3 as $v3) {
                                                                            ?>
                                                                    <option value="<?php echo htmlspecialchars($v3, ENT_QUOTES, 'UTF-8'); ?>">
                                                                        <?php echo ucfirst($v3); ?></option>
                                                                    <?php } ?>
                                                                </select>
                                                            </div>
                                                        </div>
                                                        <?php } ?>
                                                    </div>
                                                </div>
                                                <?php } ?>

                                            </div>
                                        </div>  
                                        <div class="col-md-5">
                                            <div class="productAddToCardOption">
                                                 {{-- <div class="shrtdesc"><?php echo stripslashes($shortDescription) ?></div> --}}
                                                <div class="showOnScrollTopDesktop hiddenMobile">
                                                    <h2 id="prod-price">
                                                        @if ($productDetail['product_type'] != 'variant')
                                                            @if ($productDetail['on_sale'] == 1)
                                                                <b class="red-color" id="sale-price"
                                                                    data-price=" <?php echo $productDetail['sale_price']; ?>">$<?php echo isset($productDetail['sale_price']) ? number_format($productDetail['sale_price'], 2) : ''; ?></b>
    
                                                                <div class="priceDiv salePrice">
                                                                    <del class="sale-price" id="product-price"
                                                                        data-price=" <?php echo $productDetail['price']; ?>">$<?php echo isset($productDetail['price']) ? number_format($productDetail['price'], 2) : ''; ?></del>
                                                                
                                                                
                                                                    @if($productDetail['discount_percentage'] > 0)
                                                                    <b class="red-color" id="sale-price"
                                                                    data-price=" <?php echo $productDetail['discount_percentage']; ?>">-
                                                                    <?php echo isset($productDetail['discount_percentage']) ? round(floatval($productDetail['discount_percentage'])) : ''; ?>%</b>
                                                                    @else 
                                                                    <b class="red-color" id="sale-price"
                                                                    data-price=" <?php echo $productDetail['discount_percentage']; ?>">
                                                                    <?php echo isset($productDetail['discount_percentage']) ? round(floatval($productDetail['discount_percentage'])) : ''; ?>%</b>
                                                                    @endif
                                                                </div>
                                                            @else
                                                                <b id="product-price"
                                                                    data-price=" <?php echo $productDetail['price']; ?>">$<?php echo isset($productDetail['price']) ? number_format($productDetail['price'], 2) : ''; ?></b>
                                                            @endif
                                                        @else
                                                            @if ($productDetail['on_sale'] == 1)
                                                            <div class="priceDiv priceONSale">
                                                                <b class="red-color" id="sale-price"
                                                                    data-price=" <?php echo $productDetail['sale_price']; ?>">$<?php echo isset($productDetail['sale_price']) ? number_format($productDetail['sale_price'], 2) : ''; ?></b>

                                                                    <div class="priceONsaleInnerWrapper">
                                                                        <del class="sale-price cuttnigPriceDisplay" id="product-price" data-price=" <?php echo $productDetail['price']; ?>">$<?php echo isset($productDetail['price']) ? number_format($productDetail['price'], 2) : ''; ?></del>
                                                                        <span class="sepratorLine"></span>

                                                                        @if($productDetail['discount_percentage'] > 0)
                                                                            <b class="red-color offPricePercentage" id="sale-price"
                                                                                data-price=" <?php echo $productDetail['discount_percentage']; ?>">-
                                                                                <?php echo isset($productDetail['discount_percentage']) ? round(floatval($productDetail['discount_percentage'])) : ''; ?>% off</b>
                                                                        @else
                                                                            <b class="red-color offPricePercentage" id="sale-price"
                                                                            data-price=" <?php echo $productDetail['discount_percentage']; ?>">
                                                                            <?php echo isset($productDetail['discount_percentage']) ? round(floatval($productDetail['discount_percentage'])) : ''; ?>% off</b>
                                                                        @endif
                                                                    </div>
                                                            </div>
                                                            @else
                                                            <b id="product-price"
                                                                data-price="<?php echo isset($variations[0]) ? $variations[0]->price : $productDetail['price']; ?>">
                                                                $<?php echo isset($variations[0]) ? number_format($variations[0]->price, 2) : number_format($productDetail['price'], 2); ?>
                                                                </b>
                                                            @endif
                                                        @endif
                                                    </h2>
                                                    <div>
                                                        </div>
                                                            </div>


                                                <h5 class="procomttl caption-with-letter-spacing hiddenMobile">
                                                    <a class="product-brand_name"
                                                        href="<?php echo URL::to('/brand/' . $storeInfo[0]->store_slug); ?>">
                                                        <img class="storeIcon" src="{{ URL::asset('assets/images/store-icon.svg') }}" alt="rate">   
                                                        <span class="storeNameDisplay">Visit {{ ucfirst($store_info[0]->store_name) }} store</span>
                                                    </a>
                                                </h5>
                                                {{-- plce sidebar here for tsting--}}

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
                                                                    <div class="gauge-persnt"> <?php echo !empty($remainPledges) ? $remainPledges : ''; ?>% Left </div>
                                                                    <div id="pro-gauge" class="gauge gauge-big gauge-green">
                                                                        <div class="gauge-arrow" data-percentage="<?php echo !empty($remainPledges) ? $remainPledges : ''; ?>"
                                                                            style="transform: rotate(0deg);"></div>
                                                                    </div>
                                                                    <div class="mbr-cntr"></div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <?php
                                                        } else if (isset($productDetail['flashsale_status']) && $productDetail['flashsale_status'] == 'standard') {
                                                            ?>
                    
                                                        <?php } else { ?>
                                                        <!-- <div class="cashback-available">
                                                            <p>USDC Back Available <b>2%</b></p>
                                                        </div> -->
                                                        <?php } ?>
                                                        <button onclick="goBack()"
                                                            class="primary-btn btn btn-primary pull-right text-white gobackbutton"
                                                            style="display:none !important;">Go Back</button>
                                                        <div class="price-sec">
                                                            <div class="instock-disount  clearfix">
                                                                <div class="pull-left"></div>
                                                            </div>
                                                            <div class="justify-content-between">
                                                                
                                                                <!-- <div class="shrtdesc">{{ $shortDescription }}</div> -->
                                                                <div class="empty-response"> </div>
                                                                <span class="product_stock"
                                                                    data-stock="{{ $productDetail['current_stock'] }}"></span>
                                                                <?php
                                                                // echo '$productDetail: <pre>' .print_r($productDetail,true). '</pre>'; die();
                                                                $product_stock = 0;
                                                                if ($productDetail['product_type'] == 'simple') {
                                                                    $currentStock = isset($productDetail['current_stock']) ? $productDetail['current_stock'] : 0;
                                                                    $product_stock = $currentStock;
                                                                    $classqty = '';
                                                                    if ($currentStock > 10) {
                                                                        $currentStock = 10;
                                                                    }
                                                                    ?>
                                                                    <div class="pull-right-large ">
                                                                        <?php
                                                                        
                                                                        $maxquantitylimit = isset($productDetail['maxquantitylimit']) ? $productDetail['maxquantitylimit']: "";
                                                                        if($maxquantitylimit == 0){
                                                                            $currentStock = 1;
                                                                        }else if($currentStock > $maxquantitylimit && $maxquantitylimit != '') {
                                                                            $currentStock = $maxquantitylimit;
                                                                        }elseif($currentStock < $maxquantitylimit){
                                                                            $currentStock = $currentStock;
                                                                        }
                                                                    
                                                                        if ($currentStock > 0) { ?>
                                                                        <div class="form-group clearfix mb-0">
                        
                        
                                                                        </div>
                                                                        <?php 
                                                                        } else { 
                                                                            ?>
                        
                                                                        <?php  } ?>
                                                                    </div>
                        
                                                                    <?php } else if ($productDetail['product_type'] == 'variant') {
                                                                
                                                                    ?>
                                                                    <div id="variable_product" style="display:block;">
                                                                        <div class="form-group clearfix mb-0">
        
                                                                            <?php
                                                                            $variablearray = [];
                                                                            if(!empty($variations)){
                                                                            foreach ($variations as $key => $attribute) {
                                                                                $variablearray['currunt_stock'] = isset($attribute->current_stock) ? $attribute->current_stock : '';
                                                                                $product_stock = $variablearray['currunt_stock'];
                                                                                $variablearray['maxquantitylimit'] = isset($attribute->maxquantitylimit) ? $attribute->maxquantitylimit : '';
                                                                            
                                                                                // dd($variablearray);
                                                                                }
                                                                            }
                                                                            if (isset($variablearray['currunt_stock']) && $variablearray['currunt_stock'] > 10) {
                                                                                $variablearray['currunt_stock'] = 10;
                                                                            }
                                                                            if (isset($variablearray['currunt_stock']) && $variablearray['currunt_stock'] > $variablearray['maxquantitylimit']) {
                                                                                $variablearray['currunt_stock'] = $variablearray['maxquantitylimit'];
                                                                            } elseif (isset($variablearray['currunt_stock']) && $variablearray['currunt_stock'] < $variablearray['maxquantitylimit']) {
                                                                                $variablearray['currunt_stock'] = $variablearray['currunt_stock'];
                                                                            }
                                                                            // dd($variablearray);
                                                                            ?>
                        
                                                                            <div class="qty-input-wrap if-variation">
                                                                                <div class="quantityText">
                                                                                    Quantity:</div>
                        
                                                                                <div class="quantity-control" data-quantity="">
                                                                                    <button data-pro-id="<?php echo $productDetail['product_id']; ?>"
                                                                                        data-stor-id="<?php echo $productDetail['store_id']; ?>"
                                                                                        class="quantity-btn" data-quantity-minus=""> <svg
                                                                                            viewBox="0 0 409.6 409.6">
                                                                                            <g>
                                                                                                <g>
                                                                                                    <path
                                                                                                        d="M392.533,187.733H17.067C7.641,187.733,0,195.374,0,204.8s7.641,17.067,17.067,17.067h375.467 c9.426,0,17.067-7.641,17.067-17.067S401.959,187.733,392.533,187.733z" />
                                                                                                </g>
                                                                                            </g>
                                                                                        </svg>
                                                                                    </button>
                                                                                    <input type="number" class="quantity-input "
                                                                                        data-quantity-target="" value="1" step="1"
                                                                                        min="1" max="10" name="quantity"
                                                                                        disabled>
                                                                                    <button
                                                                                        data-product-type="{{ $productDetail['product_type'] }}"
                                                                                        data-maxquantitylimit="{{isset($variablearray['maxquantitylimit']) ? $variablearray['maxquantitylimit'] : 0 }}"
                                                                                        data-current_stock="{{isset($variablearray['current_stock']) ? $variablearray['current_stock'] : 0 }}"
                                                                                        data-maxquantity=""
                                                                                        data-stor-id="<?php echo $productDetail['store_id']; ?>"
                                                                                        data-pro-id="<?php echo $productDetail['product_id']; ?>"
                                                                                        data-product-type="{{ $productDetail['product_type'] }}"
                                                                                        class="quantity-btn variant-quantity-plus"
                                                                                        data-quantity-plus=""><svg
                                                                                            viewBox="0 0 426.66667 426.66667">
                                                                                            <path
                                                                                                d="m405.332031 192h-170.664062v-170.667969c0-11.773437-9.558594-21.332031-21.335938-21.332031-11.773437 0-21.332031 9.558594-21.332031 21.332031v170.667969h-170.667969c-11.773437 0-21.332031 9.558594-21.332031 21.332031 0 11.777344 9.558594 21.335938 21.332031 21.335938h170.667969v170.664062c0 11.777344 9.558594 21.335938 21.332031 21.335938 11.777344 0 21.335938-9.558594 21.335938-21.335938v-170.664062h170.664062c11.777344 0 21.335938-9.558594 21.335938-21.335938 0-11.773437-9.558594-21.332031-21.335938-21.332031zm0 0" />
                                                                                        </svg>
                                                                                    </button>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <?php } ?>      
                                                            </div>
                                                            <span class="product_stock_" data-stock="{{ $product_stock }}"></span>
                                                        </div><!-- shipping-option-quanity-box -->
                                                        <?php
                                                        $storeDataId = 'data-store-id=' . $productDetail['store_id'];
                                                        $currentStock = isset($productDetail['current_stock']) ? $productDetail['current_stock'] : 0;
                                                        $classqty = '';
                                                        if ($currentStock > 10) {
                                                            $currentStock = 10;
                                                        }
                                                        $maxquantitylimit = isset($productDetail['maxquantitylimit']) ? $productDetail['maxquantitylimit'] : '';
                                                        if ($maxquantitylimit == 0) {
                                                            $currentStock = 1;
                                                        } elseif ($currentStock > $maxquantitylimit && $maxquantitylimit != '') {
                                                            $currentStock = $maxquantitylimit;
                                                        } elseif ($currentStock < $maxquantitylimit) {
                                                            $currentStock = $currentStock;
                                                        }
                                                        ?>

                                                        <div class="qntbx row-qntbx row">
                                                            <div class="col-md-12">
                                                                <?php  if($productDetail['product_type'] == 'simple') { 
                                                             if($currentStock > 0 ) {
                                                                ?>
                    
                    
                                                                <div class="qty-input ">
                                                                    <label class="quantityText">Quantity:</label>
                    
                                                                    <div class="quantity-control" data-quantity="10">
                                                                        <button data-pro-id="<?php echo $productDetail['product_id']; ?>"
                                                                            data-product-type="{{ $productDetail['product_type'] }}"
                                                                            data-stor-id="<?php echo $productDetail['store_id']; ?>" <?php echo $storeDataId; ?>
                                                                            class="quantity-btn" data-quantity-minus=""> <svg
                                                                                viewBox="0 0 409.6 409.6">
                                                                                <g>
                                                                                    <g>
                                                                                        <path
                                                                                            d="M392.533,187.733H17.067C7.641,187.733,0,195.374,0,204.8s7.641,17.067,17.067,17.067h375.467 c9.426,0,17.067-7.641,17.067-17.067S401.959,187.733,392.533,187.733z" />
                                                                                    </g>
                                                                                </g>
                                                                            </svg>
                                                                        </button>
                                                                        <input type="number" class="quantity-input "
                                                                            data-quantity-target="" value="1" step="1"
                                                                            min="1" max="10" name="quantity" disabled>
                                                                        <button data-maxquantity_simple="{{ $currentStock }}"
                                                                            data-stor-id="<?php echo $productDetail['store_id']; ?>"
                                                                            data-pro-id="<?php echo $productDetail['product_id']; ?>"
                                                                            data-product-type="{{ $productDetail['product_type'] }}"
                                                                            class="quantity-btn" data-quantity-plus=""><svg
                                                                                viewBox="0 0 426.66667 426.66667">
                                                                                <path
                                                                                    d="m405.332031 192h-170.664062v-170.667969c0-11.773437-9.558594-21.332031-21.335938-21.332031-11.773437 0-21.332031 9.558594-21.332031 21.332031v170.667969h-170.667969c-11.773437 0-21.332031 9.558594-21.332031 21.332031 0 11.777344 9.558594 21.335938 21.332031 21.335938h170.667969v170.664062c0 11.777344 9.558594 21.335938 21.332031 21.335938 11.777344 0 21.335938-9.558594 21.335938-21.335938v-170.664062h170.664062c11.777344 0 21.335938-9.558594 21.335938-21.335938 0-11.773437-9.558594-21.332031-21.335938-21.332031zm0 0" />
                                                                            </svg>
                                                                        </button>
                                                                    </div>
                                                                </div>
                                                                 
                                                                <?php }else{  ?>
                                                                {{-- <span class="outofstock">Out of stock</span> --}}
                    
                                                                <?php } } elseif($productDetail['product_type'] == 'virtual') { ?>
                                                                <div class="qty-input">
                                                                    <div style="margin-right: 10px; line-height: 42px; display:none;">
                                                                        Quantity:</div>
                    
                                                                    <div class="quantity-control" data-quantity="" style="display:none;">
                                                                        <button disabled data-pro-id="<?php echo $productDetail['product_id']; ?>"
                                                                            data-stor-id="<?php echo $productDetail['store_id']; ?>" <?php echo $storeDataId; ?>
                                                                            class="quantity-btn myButton" data-quantity-minus=""> <svg
                                                                                class="mySVG" viewBox="0 0 409.6 409.6">
                                                                                <g>
                                                                                    <g>
                                                                                        <path
                                                                                            d="M392.533,187.733H17.067C7.641,187.733,0,195.374,0,204.8s7.641,17.067,17.067,17.067h375.467 c9.426,0,17.067-7.641,17.067-17.067S401.959,187.733,392.533,187.733z" />
                                                                                    </g>
                                                                                </g>
                                                                            </svg>
                                                                        </button>
                                                                        <input disabled type="number" class="quantity-input "
                                                                            data-quantity-target="" value="1" step="1"
                                                                            min="1" max="10" name="quantity" disabled>
                                                                        <button disabled
                                                                            data-product-type="{{ $productDetail['product_type'] }}"
                                                                            data-maxquantity="{{ $currentStock }}"
                                                                            data-stor-id="<?php echo $productDetail['store_id']; ?>"
                                                                            data-pro-id="<?php echo $productDetail['product_id']; ?>"
                                                                            data-product-type="{{ $productDetail['product_type'] }}"
                                                                            class="quantity-btn myButton" data-quantity-plus=""><svg
                                                                                class="mySVG" viewBox="0 0 426.66667 426.66667">
                                                                                <path
                                                                                    d="m405.332031 192h-170.664062v-170.667969c0-11.773437-9.558594-21.332031-21.335938-21.332031-11.773437 0-21.332031 9.558594-21.332031 21.332031v170.667969h-170.667969c-11.773437 0-21.332031 9.558594-21.332031 21.332031 0 11.777344 9.558594 21.335938 21.332031 21.335938h170.667969v170.664062c0 11.777344 9.558594 21.335938 21.332031 21.335938 11.777344 0 21.335938-9.558594 21.335938-21.335938v-170.664062h170.664062c11.777344 0 21.335938-9.558594 21.335938-21.335938 0-11.773437-9.558594-21.332031-21.335938-21.332031zm0 0" />
                                                                            </svg>
                                                                        </button>
                                                                    </div>
                                                                </div>
                    
                    
                                                                <?php }else{ ?>
                    
                    
                                                                <?php } ?>
                    
                    
                                                                <!-- quantity-price-bbnr -->
                                                                <div class="local-taxes-include" style="display:none">
                                                                    <?php
                                                                    $productShippingPrice = isset($productShippingPrice) ? $productShippingPrice : 0;
                                                                    $productShippingPrice_usd = $productShippingPrice / 10;
                                                                    ?>
                    
                                                                </div>
                    
                                                                <?php 
                                                                if ($productDetail['product_type'] == 'simple' && $productDetail['current_stock'] <= 0) {
                                                                    ?>
                                                                    <div class="stockQuantityCheck">
                                                                        <div class="d-flex align-item-center justify-content-end outOf-stock">
                                                                            <span  class="notinstock"><i class="fa fa-times" aria-hidden="true"></i> Out of stock</span>
                                                                        </div>
                                                                    </div>
                                                                <?php 
                    
                                                        
                                                                }
                                                            
                                                                else if (Session::has('userData') && $userId == $productDetail['user_id']) {
                                                                    if ($productDetail['product_type'] == 'simple' && $productDetail['current_stock'] > 0) { ?>
                                                                <div class="cart-sec-btn simple">
                                                                    <button type="button"
                                                                        class="primary-btn btn btn-primary w100p text-white cartadd  background-clr notbuy">Add
                                                                        To Cart
                                                                        @if ($productDetail['on_sale'] == 1)
                                                                        <span> - <?php echo isset($productDetail['discount_percentage']) ? round(floatval($productDetail['discount_percentage'])) : ''; ?>% off</span>
                                                                        @endif
                                                                    </button>
                                                                    <button class="btn btn-primary mt-2 buyNowBtn">
                                                                        Buy Now 
                                                                        @if ($productDetail['on_sale'] == 1)
                                                                        <span> - <?php echo isset($productDetail['discount_percentage']) ? round(floatval($productDetail['discount_percentage'])) : ''; ?>% off</span>
                                                                        @endif
                                                                </button>
                                                                </div>
                                                                <?php }elseif($productDetail['product_type'] == 'virtual'){ ?>
                                                                <div class="cart-sec-btn virtual">
                                                                    <button type="button"
                                                                        class="primary-btn btn btn-primary w100p text-white cartadd  background-clr notbuy">Add
                                                                        To Cart
                                                                        @if ($productDetail['on_sale'] == 1)
                                                                        <span> - <?php echo isset($productDetail['discount_percentage']) ? round(floatval($productDetail['discount_percentage'])) : ''; ?>% off</span>
                                                                        @endif
                                                                    </button>
                                                                    <button class="btn btn-primary mt-2 buyNowBtn">
                                                                        Buy Now 
                                                                        @if ($productDetail['on_sale'] == 1)
                                                                        <span> - <?php echo isset($productDetail['discount_percentage']) ? round(floatval($productDetail['discount_percentage'])) : ''; ?>% off</span>
                                                                        @endif
                                                                </button>
                                                                </div>
                                                                <?php } elseif(Session::has('userData') && $userId == $productDetail['user_id']){  ?>
                                                                <div class="cart-sec-btn session-cart">
                                                                    <button type="button"
                                                                        class="btn btn-primary cartadd out-of-stock-product notbuy ">Add to
                                                                        cart
                                                                        @if ($productDetail['on_sale'] == 1)
                                                                        <span> - <?php echo isset($productDetail['discount_percentage']) ? round(floatval($productDetail['discount_percentage'])) : ''; ?>% off</span>
                                                                        @endif
                                                                    </button>
                                                                    <button class="btn btn-primary mt-2 buyNowBtn">
                                                                        Buy Now 
                                                                        @if ($productDetail['on_sale'] == 1)
                                                                        <span> - <?php echo isset($productDetail['discount_percentage']) ? round(floatval($productDetail['discount_percentage'])) : ''; ?>% off</span>
                                                                        @endif
                                                                </button>
                                                                </div>
                                                                <?php }
                                                                } else {                                    
                                                                    ?>
                                                                <div class="mb-1" id="mallshark-sold-out-badge" style="display: none;">
                                                                    <span class="badge badge-warning" style="padding: 0px;">Almost sold out</span>
                                                                </div>
                                                                <div class="cart-sec-btn cart-not-session">
                                                                    <button class="btn btn-primary cartadd out-of-stock-product"
                                                                        data-product-id="<?php echo $productDetail['product_id']; ?>"
                                                                        data-store-id="<?php echo $productDetail['store_id']; ?>">Add to Cart 
                                                                        @if ($productDetail['on_sale'] == 1)
                                                                        <span> - <?php echo isset($productDetail['discount_percentage']) ? round(floatval($productDetail['discount_percentage'])) : ''; ?>% off</span>
                                                                        @endif
                                                                </button>
                                                                    <button class="btn btn-primary mt-2 buyNowBtn">
                                                                        Buy Now 
                                                                        @if ($productDetail['on_sale'] == 1)
                                                                        <span> - <?php echo isset($productDetail['discount_percentage']) ? round(floatval($productDetail['discount_percentage'])) : ''; ?>% off</span>
                                                                        @endif
                                                                </button>
                                                                </div>
                    
                                                                <?php } ?>
                                                                <div class="stockQuantityCheck">
                                                                    <div class="d-flex align-item-center justify-content-end outOf-stock">
                                                                        <span 
                                                                            class="notinstock_out_of_stock "
                                                                            style="display: none;"><i class="fa fa-times" aria-hidden="true"></i> Out of
                                                                            stock</span>
                                                                    </div>
                                                                </div>
                                                                <!--cart-sec-btn -->
                    
                                                            </div>
                                                        </div>
                                                        
                                                        <!-- <div class="stockQuantityCheck hiddenMobile">
                                                            <?php if ($productDetail['current_stock'] > 0) { ?>
                                                                <div class="d-flex align-item-center justify-content-end In-stock">
                                                                    <img style="max-width: 22px;" src="{{ URL::asset('assets/images/tick_icon.svg') }}" alt="" />
                                                                    Instock
                                                                </div>
                                                            <?php } ?>
                                                        </div> -->
                                                        
                                                        <div class="d-flex rowSharingContainer">
                                                            <div class="likeBtn">
                                                                <div class="splopt d-flex ">
                                                                    <?php 
                                                                    $fill = "";
                                                                    if (Session::has('userData')) {
                                                                    
                                                                        $favProductId = getBuyerFavProducts();
                                                                        if (!empty($favProductId) && in_array($productDetail['product_id'], $favProductId)) {
                                                                            $fill =  "fillheart";
                                                                        }?>
                                                                    <a data-product-id="<?php echo $productDetail['product_id']; ?>"
                                                                        class="dill adfv adtofv-home btnDes <?php echo $fill; ?>"
                                                                       ><img class="image-add_tofav-s" style="width:30px; position:relative;"  src="{{ URL::asset($fill ? 'assets/images/heart.svg' : 'assets/images/heart-v2.svg') }}" alt="rate">
                                                                       </a>
                                                                        <?php  }else{ 
                                                                    $currenturl = url()->full();
                                                                    ?>
                                                                        <a class=" adfv fav-login login-popup btnDes"
                                                                            data-location="{{ $currenturl }}" href="javascript:void(0)"
                                                                            data-toggle="modal" data-target="#login-registerpopup">
                                                                            <img class="image-add_tofav" data-product-id="<?php echo $productDetail['product_id']; ?>"
                                                                                style="width:30px; position:relative;"
                                                                                src="{{ URL::asset('assets/images/heart-v2.svg') }}"
                                                                                alt="rate">
                                                                
                                                                        </a>
                                                                        <?php   } ?>
                                                                        <a class=" "></a>
                                                                
                                                                </div>
                                                            </div>
                                                            <div class="sharingBtnLink dropdown">
                                                                <a href="javascript:;" class="btnDes" id="shareButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" >
                                                                    <img  src="{{ URL::asset('assets/images/share_social_icon.svg') }}" alt="" class="img-22" />
                                                                </a>
                                                                <div class="dropdown-menu" aria-labelledby="shareButton" style="border-radius: 0 0 20px 20px; padding: 10px; width:166px; border-radius:3px">
                                                                    <div class="a2a_kit a2a_kit_size_32 a2a_default_style">
                                                                        <a class="a2a_button_facebook"></a>
                                                                        <a class="a2a_button_whatsapp"></a>
                                                                        {{-- <a class="a2a_button_instagram"></a> --}}
                                                                        <a class="a2a_button_x"></a>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                       <?php if(!is_null($productDetail['delivery_days'])){
                                                        $days = $productDetail['delivery_days'];
                                                            $futureDate = Carbon::now()->addDays($days);
                                                            $deliverydate = $futureDate->format('j F');?>
                                                        <div>
                                                            <strong>This product will be delivered by {{$deliverydate ?? ''}} </strong>
                                                        </div>
                                                                        <?php } ?>
                                                                            
                                                        <div class="securityPrivacy-container">
                                                            <div class="d-flex align-items-center">
                                                                <img style="max-width: 22px;" src="{{ URL::asset('assets/images/shield_icon.svg') }}" alt=""  />&nbsp;
                                                                <strong>Security & Privacy</strong>
                                                            </div>
                                                            <p class="mb-10"> <strong>Safe payments:</strong> We use Stripe for secure payment processing, ensuring your transactions are safe and reliable.<br>
                                                                <strong>Privacy Protection:</strong> We never share your personal information with third parties without your consent and are committed to keeping your details private and secure.

                                                                </p>
                                                                <div class="eco-friendly-tags mb-20">
                                                                @foreach ($ecoFriendlyTags as $tag)
                                                                    @php
                                                                        $tagImages = [
                                                                            'Cruelty-Free' => "assets/images/cruelty.png",
                                                                            'Vegan' => 'assets/images/vegan.png',
                                                                            'Plastic-Free' => 'assets/images/plasticFree.png',
                                                                            'Biodegradable' => 'assets/images/biodegradable.png',
                                                                            'Recycled Materials' => 'assets/images/recycleMaterail.png',
                                                                            'Renewable Resources' => 'assets/images/renewableResource.png',
                                                                            'Energy Efficient Production' => 'assets/images/energyEfficient.png',
                                                                            'Carbon Neutral' => 'assets/images/carbonNeutral.png',
                                                                        ];
                                                                        $imagePath = $tagImages[$tag] ?? 'path/to/default.png';
                                                                    @endphp
                                                                    <div style="display: inline-block; text-align: center;">
                                                                        <img src="{{ asset($imagePath) }}" alt="{{ $tag }}" style="width: 20px; height: 20px;">
                                                                         <span style="font-size:9px;color:green;font-weight:bold;margin-left:-2px;"> {{$tag}} </span>
                                                                    </div>
                                                                @endforeach
                                                            </div>
                                                                   <hr/>         
                                                                <p class="pt-p d-flex align-items-center">
                                                                    {{-- <img class="paypal" src="/assets/images/paypal.svg" alt="payments">    --}}
                                                                    <img class="visaPay" src="/assets/images/visa_icon.svg" alt="payments">   
                                                                    <img class="paymentPay" src="/assets/images/payment_icon.svg" alt="payments">   
                                                                    <img class="googlePay" src="/assets/images/google-pay.svg" alt="payments">   
                                                                    <img class="applePay" src="/assets/images/apple-pay.svg" alt="payments">   
                                                                </p>
                                                        </div>


                                                       
                    
                                                                <?php
                                                                //if($incartProduct > 0) {
                                                                    ?>
                                                                        {{-- <p class="pl-xs-1 add-cart">
                                                                            {{ $incartProduct }} people have this in their cart right now
                                                                        </p> --}}
                                                                        <?php //}
                                                                ?>

                                                    </div><!--sec-right -->
                    
                                                </div>




                                                
                                            </div>
                                        </div>
                                        @if ($productDetail['product_type'] != 'variant')
                                                </div>
                                             @endif  

                                </div>

                            
                                {{-- plce sidebar here --}}



                            
                        </div>
                    </div>
                </div>
            </div>
        </section>
            <!--row -->
</div><!--product-detail -->










<div class="tabsWrapperDesktop hiddenMobile">
    <div class="product-tabs " id="fixme-lab">
        <div class="container">
            <div class="col-md-12 pl-0">
                <ul class="nav nav-tabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link " href="#produt-detail">Product Detail</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link faqtab" href="#faq">FAQ</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#rating">Reviews</a>
                    </li>

                </ul>
            </div>
        </div>
    </div>


    <div class="product-tabs-body">

        <div class="container-lrge">
            <div>
                <div class="tab-content col-sm-12">
                    <div role="tabpanel" class="tab-pane fade in active  show" id="produt-detail">
                        <div class="container">
                            <div class="row">
                                <div class="col-sm-9">
                                    <h3 class="d-head">Product Detail</h3>
                                            <p><?php echo isset($productDetail['description']) ? $productDetail['description'] : ''; ?></p>
                                            <meta itemprop="description"
                                                content="{{ isset($productDetail['description']) ? $productDetail['description'] : '' }}">

                                </div>
                            </div>
                        </div>

                        <div class="relatedProductWrapperDesktop hiddenMobile">

                                                                        </br>
                            <div class="container rltdprdcts">
                                <div class="row">
                                    <div class="col-12">
                            
                                        <aside>
                                            <?php
                                             $class = '';
                                             $opecity = '';
                                            // echo 'Data: <pre>' .print_r($relatedProduct,true). '</pre>'; die();
                                            if (!$relatedProduct->isEmpty()) { ?>
                                                <div class="related-product">
                            
                                                    <h3 class="page-heading">Related Products</h3>
                                                    <div class="row">
                                                        <?php foreach ($relatedProduct as $rp) { 
                                                             if ($rp->product_type == 'simple') {
                                                                $current_stock = $rp->current_stock;
                                                                if ($current_stock > 0) {
                                                                    $class = 'current-stock-active';
                                                                    $opecity = '';
                                    
                                                                }else{
                                                                    $class = 'current-stock-deactive';
                                                                    $opecity = 'opecity-deactive';
                                                                }
                                                            }
                                                            ?>
                                                            <div class="col-md-2 col-6">
                                                                <!-- card starts -->
                                                                <div class="card">
                                                                    <?php $productImage = getAttachmentUrlById((int) !empty($rp->feature_image) ? $rp->feature_image : '', 'medium'); ?>
                                                                    <a href="{{ URL::to('/product/' . $rp->slug) }}"><img src="{{ $productImage }}"
                                                                            class="card-img-top product-thumbnail" alt="{{ $rp->title }}"
                                                                            title="{{ $rp->title }}"></a>
                                                                    <div class="card-body">
                                                                    <div style="min-height: 143px;">
                                                                    <div class="mb-1 discount-container">
                                                                        
                                                                            @if($rp->on_sale == 1 && $rp->discount_percentage > 0)
                                                                                <span class="z-symbl product-price discountbadge" style="position:relative!important;top:0px!important;font-weight:600;">
                                                                                    -{{ $rp->discount_percentage }}% off
                                                                                </span><b class="discountbadge-text">Limited time offer</b>
                                                                                @else
                                                                                <!-- <span style="display: inline-block;margin-bottom: 8px;font-size: 18px;font-weight: 600;position: relative!important;top:0px!important; z-in" class="z-symbl product-price discountbadge" >
                                                                                    {{ $rp->discount_percentage }}% off
                                                                                </span><b class="discountbadge-text">Limited time offer</b> -->
                                                                            @endif
                                                                    
                                                                    </div>
                                                                        <div class="sub-cateogry-title product_parent_category ">
                                                                            <span class="catnm card-category"><?php echo $rp->parentCategoryTitle . '&nbsp;'; ?></span>
                                                                            <span class="catnm catnm1 card-category"><?php echo '(' . $rp->categoryTitle . ')'; ?></span>
                                                                        </div>
                                                                        <h4 class="card-title"><a href="{{ URL::to('/product/' . $rp->slug) }}">
                                                                                {{ $rp->title }}</a></h4>
                                                                    </div>
                                                                        <div>
                            
                            
                                                                            <div class="prcbxint">
                                                                                <div>
                                                                                    <p class="card-text prc">
                                                                                        @if($rp->on_sale == 1)
                                                                                            <span class="n-price"><span class="z-symbl">$<?php echo $rp->sale_price ?></span>
                                                                                                </span >
                                                                                            <del class=""><span class="">$<?php echo $rp->price; ?></span>
                                                                                                </del>
                                                                                        @else
                                                                                            <span class="n-price"><span class="z-symbl">$<?php echo$rp->price; ?></span>
                                                                                            </span>
                                                                                        @endif
                                                                                            <!-- @if($rp->discount_percentage > 0)
                                                                                            <span class="n-price mybadge">- {{ $rp->discount_percentage }}%</span>
                                                                                            @else
                                                                                            <span class="n-price mybadge">{{ $rp->discount_percentage }}%</span>
                                                                                            @endif -->
                                                                                    </p>
                                                                                </div>
                            
                                                                                <div class="card-text small ">
                                                                                    <div class="splopt">
                            
                                                                                        <?php
                                                                                        $fill = "";
                                                                                        if (Session::has('userData')) {
                            
                                                                                            $favProductId = getBuyerFavProducts();
                                                                                            if (!empty($favProductId) && in_array($rp->product_id, $favProductId)) {
                                                                                                $fill =  "fillheart";
                                                                                            } ?>
                                                                                            <a data-product-id="<?php echo $rp->product_id; ?>"
                                                                                                class="dill adfv adtofv-home <?php echo $fill; ?>"
                                                                                            ><img class="image-add_tofav-s" style="width:30px; position:relative;"  src="{{ URL::asset($fill ? 'assets/images/heart.svg' : 'assets/images/heart-v2.svg') }}" alt="rate">
                                                                                            </a>
                                                                                            <?php  } else {
                                                                                            $currenturl = url()->full();
                                                                                            ?>
                                                                                                <a class=" adfv fav-login login-popup"
                                                                                                    data-location="{{ $currenturl }}"
                                                                                                    href="javascript:void(0)" data-toggle="modal"
                                                                                                    data-target="#login-registerpopup">
                                                                                                    <img class="image-add_tofav"
                                                                                                        data-product-id="<?php echo $rp->product_id; ?>"
                                                                                                        style="width:30px; position:relative;"
                                                                                                        src="{{ URL::asset('assets/images/heart-v2.svg') }}"
                                                                                                        alt="rate">
                            
                                                                                                </a>
                                                                                            <?php   } ?>
                            
                                                                                            <?php
                                                                                            $userId = "";
                                                                                            $userId = isset(Session::get('userData')['user_id']) ? Session::get('userData')['user_id'] : '';
                                                                                            if (Session::has('userData') && $userId == $rp->user_id) { ?>
                                                                                                <a class="notbuy" href="javascript:void(0)"><span style="background-color: #073ba5;padding:10px;border-radius:50%;">
                                                                                                <i class="fa fa-shopping-cart" style="color:#fff;font-size:17px;position:relative;top:1px;" aria-hidden="true"></i> </span></a>
                                                                                            <?php  } else if ($rp->product_type == 'variant') { ?>
                                                                                                <a class="" data-type="{{ $rp->product_type }}"
                                                                                                    href="{{ URL::to('/product/' . $rp->slug) }}"><span style="background-color: #073ba5;padding:10px;border-radius:50%;">
                                                                                                    <i class="fa fa-shopping-cart" style="color:#fff;font-size:17px;position:relative;top:1px;" aria-hidden="true"></i> </span> </a>
                                                                                            <?php  } else { ?>
                                                                                                <a class="cartadd-home {{$class}}"
                                                                                                    data-type="{{ $rp->product_type }}"
                                                                                                    data-stock="{{ $rp->current_stock }}"
                                                                                                    data-store-id="<?php echo $rp->store_id; ?>"
                                                                                                    data-product-id="<?php echo $rp->product_id; ?>"
                                                                                                    href="javascript:void(0)"><span style="background-color: #073ba5;padding:10px;border-radius:50%;">
                                                                                                    <i class="fa fa-shopping-cart {{$opecity}}" style="color:#fff;font-size:17px;position:relative;top:1px;" aria-hidden="true"></i> </span></a>
                                                                                            <?php   }  ?>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                            
                                                            <div class="col-lg-2 col-md-4 col-sm-6 d-none">
                                                                <div class="prdt-sngle-bx">
                                                                    <div class="card">
                                                                        <?php $productImage = getAttachmentUrlById((int) !empty($rp->feature_image) ? $rp->feature_image : '', 'medium'); ?>
                                                                        <a href="<?php echo URL::to('product/' . $rp->slug); ?>" class="image-container-mbt"> <img
                                                                                src="<?php echo $productImage; ?>" alt="{{ $rp->title }}"
                                                                                title="{{ $rp->title }}" class="img-fluid"> </a>
                            
                                                                        <div class="card-body">
                                                                            <h3 class="card-title"><a
                                                                                    href="<?php echo URL::to('product/' . $rp->slug); ?>"><?php echo !empty($rp->title) ? substr($rp->title, 0, 35) : ''; ?></a>
                                                                            </h3>
                            
                                                                            <div class="d-flex" style="justify-content: space-between; ">
                            
                                                                                <div class="product-category">
                                                                                    <p>
                                                                                        <span class="cat catnm">
                                                                                            <?php
                                                                                            echo isset($rp->categoryTitle) ? $rp->categoryTitle : '';
                                                                                            ?>
                                                                                        </span>
                                                                                    </p>
                            
                                                                                </div><!-- box-description-mbt -->
                                                                                <div class="cartsec" style="display:none;">
                                                                                    <p>
                            
                                                                                        <span class="z-symbl">$
                                                                                            <!-- <img
                                                                                        src=" {{ asset('assets/images/z-symbal.png', env('HTTPS_STATUS')) }}" alt="" class="img-fluid"> -->
                                                                                        </span>
                                                                                        <?php echo !empty($rp->price) ? $rp->price : ''; ?>
                            
                                                                                    </p>
                            
                                                                                </div><!-- box-description-mbt -->
                            
                                                                            </div>
                            
                                                                            <div class="prc d-flex justify-content-between">
                            
                            
                                                                                <span class="zit-price">
                                                                                    <span class="z-symbl">$
                                                                                        <!-- <img
                                                                                        src=" {{ asset('assets/images/z-symbal.png', env('HTTPS_STATUS')) }}" alt="" class="img-fluid"> -->
                                                                                    </span>
                                                                                    <?php echo !empty($rp->price) ? $rp->price : ''; ?>
                            
                                                                            </div>
                            
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
                    </div>

                            <?php
                            if (!getCurrentUserId()) {
                                $str = 'class="btn btn-primary btn-simple get-p-url"  data-toggle="modal" data-target="#login-registerpopup"';
                            } else {
                                $str = 'class="btn btn-primary aska-question"';
                            }
                            ?>
                            <div itemscope itemtype="https://schema.org/FAQPage" role="tabpanel" class="faqcontent"
                                id="faq">
                                <div class="container container-description">
                                    <div class="productDescriptionContainer">
                                        {{-- <h3 class="d-head">Frequently Asked Questions For
                                                        {{ $productDetail['title'] }}</h3> --}}
                                        <div class="ask-question-section  top-space-remove">
                                            <?php
                                                $counter = 1;
                                                foreach ($faqs as $faq) {
                                                    if(!$faq){
                                                         echo '<style>.faqcontent { display: none; }</style>';
                                                         echo '<style>.nav-link[href="#faq"] { display: none; }</style>';
                                                    }
                                                    ?>

                                            <div class="ask-repeater" itemscope itemprop="mainEntity"
                                                itemtype="https://schema.org/Question">

                                                <h4 itemprop="name"><span
                                                        class="q-counter">Q{{ $counter }}:</span>
                                                    <?php echo $faq->question; ?>
                                                </h4>
                                                <div itemscope itemprop="acceptedAnswer"
                                                    itemtype="https://schema.org/Answer">
                                                    <p itemprop="text" style="font-weight: 300!important;">
                                                        <?php echo $faq->answer; ?>
                                                    </p>
                                                </div>
                                            </div>

                                            <?php
                                                    $counter++;
                                                }
                                                ?>
                                        </div><!-- ask-question-section -->
                                        <div class="ask-a-question">
                                            <?php if ($counter == 1) {echo '<style>.faqcontent { display: none; }</style>';
                                                echo '<style>.nav-link[href="#faq"] { display: none; }</style>'; ?>
                                                
                                            <!-- <h2>Frequently Asked Question</h2> -->
                                            <p>Looks like there aren't any frequently asked question yet. Ask the store
                                                owner directly.</p>
                                            <?php }
                                            ?>
                                            <p style="margin:0px;">
                                                <?php if (!Session::has('userData')) { ?>
                                            <div class="cart-sec-btn">
                                                <a class="primary-btn btn btn-primary cartlogin"
                                                    style="width:200px !important;"
                                                    data-location="{{ URL::to('product/' . $productDetail['slug']) }}"
                                                    href="javascript:void(0)" data-toggle="modal"
                                                    data-target="#login-registerpopup">Ask a question
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
                                                        <input type="text" id="faqQuestion" class="form-control"
                                                            maxlength="500">
                                                    </div>


                                                    <div class="add-button-option">
                                                        <button class="btn btn-primary faq-question" id=""
                                                            type="button">Submit</button>
                                                    </div>
                                                </div>

                                            </form>
                                        </div>
                                    </div>

                                </div><!-- faq -->
                            </div>  

                    <div role="tabpanel" class="tab-pane fade in active show" id="rating">
                        <div class="container">
                            <div class="rating-review-tab">

                                   <div class="rating-review-tab">
                                        {{-- <h3 class="d-head">Reviews</h3> --}}
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
                                                <p>Describing your experience with this product is important for others
                                                </p>
                                                <div class="review-button-post">
                                                    <button class="btn btn-primary" id="rate-review-option">
                                                        Rate & Review
                                                    </button>
                                                </div>

                                            </div>
                                        </div><!-- describe-experience-sec -->
                                        <?php } ?>
                                        <?php
                                                
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
                                                            <i class="<?php echo $class; ?>" aria-hidden="true"></i>
                                                            <?php } ?>

                                                        </div>
                                                    </span>
                                                    <span class="rating-counter">{{ $average }}.0</span>

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
                                                            <div class="progress-bar" role="progressbar"
                                                                style="width: {{ $star5_count_percentage }}%"
                                                                aria-valuenow="50" aria-valuemin="0"
                                                                aria-valuemax="100"></div>
                                                        </span>
                                                    </div>
                                                    <span class="counter-words">{{ $star5_count }}</span>
                                                </div>
                                                <div class="star-row-strip">
                                                    <span class="star-rating-start">
                                                        <span class="sart-counter-mbt">4</span>
                                                        <i class="fa fa-star" aria-hidden="true"></i>
                                                    </span>

                                                    <div class="parent-item-star">
                                                        <span class="progress">
                                                            <div class="progress-bar" role="progressbar"
                                                                style="width: {{ $star4_count_percentage }}%"
                                                                aria-valuenow="50" aria-valuemin="0"
                                                                aria-valuemax="100"></div>
                                                        </span>
                                                    </div>
                                                    <span class="counter-words">{{ $star4_count }}</span>
                                                </div>
                                                <div class="star-row-strip">
                                                    <span class="star-rating-start">
                                                        <span class="sart-counter-mbt">3</span>
                                                        <i class="fa fa-star" aria-hidden="true"></i>
                                                    </span>

                                                    <div class="parent-item-star">
                                                        <span class="progress">
                                                            <div class="progress-bar" role="progressbar"
                                                                style="width: {{ $star3_count_percentage }}%"
                                                                aria-valuenow="50" aria-valuemin="0"
                                                                aria-valuemax="100"></div>
                                                        </span>
                                                    </div>
                                                    <span class="counter-words">{{ $star3_count }}</span>
                                                </div>
                                                <div class="star-row-strip">
                                                    <span class="star-rating-start">
                                                        <span class="sart-counter-mbt">2</span>
                                                        <i class="fa fa-star" aria-hidden="true"></i>
                                                    </span>

                                                    <div class="parent-item-star">
                                                        <span class="progress">
                                                            <div class="progress-bar" role="progressbar"
                                                                style="width: {{ $star2_count_percentage }}%"
                                                                aria-valuenow="50" aria-valuemin="0"
                                                                aria-valuemax="100"></div>
                                                        </span>
                                                    </div>
                                                    <span class="counter-words">{{ $star2_count }}</span>
                                                </div>
                                                <div class="star-row-strip">
                                                    <span class="star-rating-start">
                                                        <span class="sart-counter-mbt">1</span>
                                                        <i class="fa fa-star" aria-hidden="true"></i>
                                                    </span>

                                                    <div class="parent-item-star">
                                                        <span class="progress">
                                                            <div class="progress-bar" role="progressbar"
                                                                style="width: {{ $star1_count_percentage }}%"
                                                                aria-valuenow="50" aria-valuemin="0"
                                                                aria-valuemax="100"></div>
                                                        </span>
                                                    </div>
                                                    <span class="counter-words">{{ $star1_count }}</span>
                                                </div>




                                            </div>
                                        </div><!-- average-rating-box -->
                                        <div class="star-rating-head">

                                            {{-- <span class="average-rating spacing-left30">Reviews:</span>
                                                        <span class="rating-counter">{{ count($reviews) }}</span> --}}

                                        </div><!-- star-rating-head -->

                                        <div itemprop="aggregateRating" itemtype="https://schema.org/AggregateRating"
                                            itemscope>
                                            <meta itemprop="reviewCount" content="{{ count($reviews) }}" />
                                            <meta itemprop="ratingValue" content="{{ $average }}" />
                                        </div>
                                        <div class="review-section-container">
                                            <?php
                                                      
                                                        foreach ($reviews as $rev) {
                                                            $count = $rev['rating'];
                                                            $comment = $rev['comment'];
                                                            $date = $rev['created_at'];
                                                            $user_id = $rev['user_id'];
                                                            $user_data = getUserById($user_id);
                                                            // $user_name = isset($user_data->name) ? $user_data->name : "";
                                                            $user_name = isset($rev['name']) ? $rev['name'] : (isset($user_data->name) ? $user_data->name : "");
                                                            $user_avatar = isset($user_data->user_avatar) ? $user_data->user_avatar: "";
                                                            // $image_url = '//i0.wp.com/picjumbo.com/wp-content/uploads/HNCK8891-1.jpg?resize=1570,1047&amp;quality=50';
                                                            $image_url = '';
                                                            if ($user_avatar != '' &&  $user_id != '12') {
                                                                $image_url = getAttachmentUrlById((int) $user_avatar, 'media');
                                                            }
            
                                                            if (is_string($date)) {
                                                                $timestamp = (strtotime($date));
                                                                $date =date('m-d-Y H:i:s', $timestamp);
                                                            }
                                                            ?>
                                            <div class="row-table pddtailPageReview">
                                                <div class="col-sm-2-cell">
                                                    <div class="image-review-cnt">
                                                        <img src="{{ !empty($image_url) ? $image_url : URL::asset('/assets/images/personLogo.png') }}"
                                                            alt="{{ $user_name }}" title="{{ $user_name }}"
                                                            class="img-fluid" style="height: 85px; width: 85px;">

                                                    </div><!--image-review-cnt -->
                                                </div>
                                                <div itemprop="review" itemtype="https://schema.org/Review" itemscope>
                                                    <div class="col-sm-10-cell">
                                                        <div class="body-review-cntiner">
                                                            <div class="row-review-head">
                                                                <div class="rating-strip">
                                                                    <?php
                                                                                    for ($i = 1; $i <= 5; $i++) {
                                                                                        if ($i <= $count) {
                                                                                            $class = "fa fa-star";
                                                                                        } else {
                                                                                            $class = "fa fa-star-o";
                                                                                        }
                                                                                        ?>
                                                                    <i class="<?php echo $class; ?>"
                                                                        aria-hidden="true"></i>
                                                                    <?php } ?>

                                                                </div>
                                                            </div>
                                                            <div><span itemprop="author" class="name-show-rev"
                                                                    style="font-size: 12px;">{{ $user_name }}</span>
                                                                <span class="seprator-line">|</span>
                                                                <span itemprop="datePublished"
                                                                    content="{{ $date }}"
                                                                    class="date-reivew-rev"
                                                                    style="font-size: 12px;">{{ $date }}</span>
                                                            </div>
                                                        </div>
                                                        <div itemprop="reviewBody" class="body-review-text-bdy">
                                                            <p style="margin-top: -6px;">{{ $comment }}</p>
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
                                                        echo '<style>.m-review-t { display: none; }</style>';
                                                    }
                                                }
                                                ?>
                                        <section class='rating-widget'>
                                            <?php if (canUserReview($productDetail['product_id'])) {
                                                        ?>
                                            <div class="post-review-heading">
                                                <h3>Post Review</h3>
                                            </div>


                                            <form action="" method="post">
                                                <div class="review-body" style="margin-left:0px;">
                                                    <div class="response"></div>
                                                    <p>Describing your experience with this product or seller</p>
                                                    <textarea class="form-control" id="rating-text"></textarea>
                                                    <!--Rating Stars Box -->
                                                    <div class='rating-stars clearfix'>
                                                        <div class="your-rating-headeing">Your Rating:</div>
                                                        <div class="star-rating-panel-mbt">
                                                            <div class="star-rating">
                                                                <span class="fa fa-star-o" data-rating="1"></span>
                                                                <span class="fa fa-star-o" data-rating="2"></span>
                                                                <span class="fa fa-star-o" data-rating="3"></span>
                                                                <span class="fa fa-star-o" data-rating="4"></span>
                                                                <span class="fa fa-star-o" data-rating="5"></span>
                                                                <input type="hidden" id="rating_count"
                                                                    name="rating_count" class="rating-value"
                                                                    value="0">
                                                            </div>
                                                        </div>

                                                        <div class="review-button-post">
                                                            <button class="btn btn-primary" id="submit-review">
                                                                Post Your Review
                                                            </button>
                                                            <span>Note: you can post your review and rating only
                                                                once.</span>
                                                        </div>
                                                    </div>

                                                </div>
                                            </form>
                                        </section>
                                        <?php } ?>
                                    </div>

                            </div>
                        </div>
                    </div>

                </div>


            </div>


        </div>

    </div>


</div>






<div class="product-tabs hiddenDesktop" id="fixme-labMobile">
    <div class="container">
        {{-- <h2  class="aboutThisItem" id="about-this-item-section">About this item</h2> --}}
        <div class="row-full">
            <div class="col-md-12-full ">
                <ul class="accordion-list">
                    <li class="nav-item">
                        <div class="dtxt m-nav-p">
                            Product Detail
                        </div>
                        <div class="answer">
                            <div class="container-description">
                                <div class="row-full">
                                    <div class="col-sm-9-full">
                                        <div class="productDescriptionContainer">
                                            {{-- <h3 class="d-head">Product Detail</h3> --}}
                                            <p><?php echo isset($productDetail['description']) ? $productDetail['description'] : ''; 
                                            ?></p>
                                            <meta itemprop="description"
                                                content="{{ isset($productDetail['description']) ? $productDetail['description'] : '' }}">
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>

                    </li>
                    <li class="nav-item no-border-top m-nav-p m-faq-t">
                        <div class="dtxt">FAQ</div>
                        <div class="answer">

                            <?php
                            if (!getCurrentUserId()) {
                                $str = 'class="btn btn-primary btn-simple get-p-url"  data-toggle="modal" data-target="#login-registerpopup"';
                            } else {
                                $str = 'class="btn btn-primary aska-question"';
                            }
                            ?>
                            <div itemscope itemtype="https://schema.org/FAQPage" role="tabpanel" class="faqcontent"
                                id="faq-mobile">
                                <div class="container container-description">
                                    <div class="productDescriptionContainer">
                                        {{-- <h3 class="d-head">Frequently Asked Questions For
                                                        {{ $productDetail['title'] }}</h3> --}}
                                        <div class="ask-question-section  top-space-remove">
                                            <?php
                                                $counter = 1;
                                                foreach ($faqs as $faq) {
                                                    if(!$faq){
                                                        echo '<style>.faqcontent { display: none; }</style>';
                                                        echo '<style>.m-faq-t { display: none; }</style>';
                                                   }
                                                    ?>

                                            <div class="ask-repeater" itemscope itemprop="mainEntity"
                                                itemtype="https://schema.org/Question">

                                                <h4 itemprop="name"><span
                                                        class="q-counter">Q{{ $counter }}:</span>
                                                    <?php echo $faq->question; ?>
                                                </h4>
                                                <div itemscope itemprop="acceptedAnswer"
                                                    itemtype="https://schema.org/Answer">
                                                    <p itemprop="text" style="font-weight: 300!important;">
                                                        <?php echo $faq->answer; ?>
                                                    </p>
                                                </div>
                                            </div>

                                            <?php
                                                    $counter++;
                                                }
                                                ?>
                                        </div><!-- ask-question-section -->
                                        <div class="ask-a-question">
                                            <?php if ($counter == 1) {
                                                 echo '<style>.faqcontent { display: none; }</style>';
                                                 echo '<style>.m-faq-t { display: none; }</style>'; ?>
                                            <!-- <h2>Frequently Asked Question</h2> -->
                                            <p>Looks like there aren't any frequently asked question yet. Ask the store
                                                owner directly.</p>
                                            <?php } ?>
                                            <p style="margin:0px;">
                                                <?php if (!Session::has('userData')) { ?>
                                            <div class="cart-sec-btn">
                                                <a class="primary-btn btn btn-primary cartlogin"
                                                    style="width:200px !important;"
                                                    data-location="{{ URL::to('product/' . $productDetail['slug']) }}"
                                                    href="javascript:void(0)" data-toggle="modal"
                                                    data-target="#login-registerpopup">Ask a question
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
                                                        <input type="text" id="faqQuestion" class="form-control"
                                                            maxlength="500">
                                                    </div>


                                                    <div class="add-button-option">
                                                        <button class="btn btn-primary faq-question" id=""
                                                            type="button">Submit</button>
                                                    </div>
                                                </div>

                                            </form>
                                        </div>
                                    </div>

                                </div><!-- faq -->
                            </div>


                        </div>

                    </li>
                    <li class="nav-item no-border-top m-review-t">
                        <div class="dtxt">Reviews</div>
                        <div class="answer">

                            <div class="ratingCOntainerDetail container-description productDescriptionContainer"
                                id="rating-mobile">
                                <div class="container-detail">
                                    <div class="rating-review-tab">
                                        {{-- <h3 class="d-head">Reviews</h3> --}}
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
                                                <p>Describing your experience with this product is important for others
                                                </p>
                                                <div class="review-button-post">
                                                    <button class="btn btn-primary" id="rate-review-option">
                                                        Rate & Review
                                                    </button>
                                                </div>

                                            </div>
                                        </div><!-- describe-experience-sec -->
                                        <?php } ?>
                                        <?php
                                                
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
                                                            <i class="<?php echo $class; ?>" aria-hidden="true"></i>
                                                            <?php } ?>

                                                        </div>
                                                    </span>
                                                    <span class="rating-counter">{{ $average }}.0</span>

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
                                                            <div class="progress-bar" role="progressbar"
                                                                style="width: {{ $star5_count_percentage }}%"
                                                                aria-valuenow="50" aria-valuemin="0"
                                                                aria-valuemax="100"></div>
                                                        </span>
                                                    </div>
                                                    <span class="counter-words">{{ $star5_count }}</span>
                                                </div>
                                                <div class="star-row-strip">
                                                    <span class="star-rating-start">
                                                        <span class="sart-counter-mbt">4</span>
                                                        <i class="fa fa-star" aria-hidden="true"></i>
                                                    </span>

                                                    <div class="parent-item-star">
                                                        <span class="progress">
                                                            <div class="progress-bar" role="progressbar"
                                                                style="width: {{ $star4_count_percentage }}%"
                                                                aria-valuenow="50" aria-valuemin="0"
                                                                aria-valuemax="100"></div>
                                                        </span>
                                                    </div>
                                                    <span class="counter-words">{{ $star4_count }}</span>
                                                </div>
                                                <div class="star-row-strip">
                                                    <span class="star-rating-start">
                                                        <span class="sart-counter-mbt">3</span>
                                                        <i class="fa fa-star" aria-hidden="true"></i>
                                                    </span>

                                                    <div class="parent-item-star">
                                                        <span class="progress">
                                                            <div class="progress-bar" role="progressbar"
                                                                style="width: {{ $star3_count_percentage }}%"
                                                                aria-valuenow="50" aria-valuemin="0"
                                                                aria-valuemax="100"></div>
                                                        </span>
                                                    </div>
                                                    <span class="counter-words">{{ $star3_count }}</span>
                                                </div>
                                                <div class="star-row-strip">
                                                    <span class="star-rating-start">
                                                        <span class="sart-counter-mbt">2</span>
                                                        <i class="fa fa-star" aria-hidden="true"></i>
                                                    </span>

                                                    <div class="parent-item-star">
                                                        <span class="progress">
                                                            <div class="progress-bar" role="progressbar"
                                                                style="width: {{ $star2_count_percentage }}%"
                                                                aria-valuenow="50" aria-valuemin="0"
                                                                aria-valuemax="100"></div>
                                                        </span>
                                                    </div>
                                                    <span class="counter-words">{{ $star2_count }}</span>
                                                </div>
                                                <div class="star-row-strip">
                                                    <span class="star-rating-start">
                                                        <span class="sart-counter-mbt">1</span>
                                                        <i class="fa fa-star" aria-hidden="true"></i>
                                                    </span>

                                                    <div class="parent-item-star">
                                                        <span class="progress">
                                                            <div class="progress-bar" role="progressbar"
                                                                style="width: {{ $star1_count_percentage }}%"
                                                                aria-valuenow="50" aria-valuemin="0"
                                                                aria-valuemax="100"></div>
                                                        </span>
                                                    </div>
                                                    <span class="counter-words">{{ $star1_count }}</span>
                                                </div>




                                            </div>
                                        </div><!-- average-rating-box -->
                                        <div class="star-rating-head">

                                            {{-- <span class="average-rating spacing-left30">Reviews:</span>
                                                        <span class="rating-counter">{{ count($reviews) }}</span> --}}

                                        </div><!-- star-rating-head -->

                                        <div itemprop="aggregateRating" itemtype="https://schema.org/AggregateRating"
                                            itemscope>
                                            <meta itemprop="reviewCount" content="{{ count($reviews) }}" />
                                            <meta itemprop="ratingValue" content="{{ $average }}" />
                                        </div>
                                        <div class="review-section-container">
                                            <?php
                                                      
                                                        foreach ($reviews as $rev) {
                                                            $count = $rev['rating'];
                                                            $comment = $rev['comment'];
                                                            $date = $rev['created_at'];
                                                            $user_id = $rev['user_id'];
                                                            $user_data = getUserById($user_id);
                                                            $user_name = isset($user_data->name) ? $user_data->name : "";
                                                            $user_avatar = isset($user_data->user_avatar) ? $user_data->user_avatar: "";
                                                            // $image_url = '//i0.wp.com/picjumbo.com/wp-content/uploads/HNCK8891-1.jpg?resize=1570,1047&amp;quality=50';
                                                            $image_url = '';
                                                            if ($user_avatar != '') {
                                                                $image_url = getAttachmentUrlById((int) $user_avatar, 'media');
                                                            }
            
                                                            if (is_string($date)) {
                                                                $timestamp = (strtotime($date));
                                                                $date =date('m-d-Y H:i:s', $timestamp);
                                                            }
                                                            ?>
                                            <div class="row-table pddtailPageReview">
                                                <div class="col-sm-2-cell">
                                                    <div class="image-review-cnt">
                                                        <img src="{{ !empty($image_url) ? $image_url : URL::asset('/assets/images/personLogo.png') }}"
                                                            alt="{{ $user_name }}" title="{{ $user_name }}"
                                                            class="img-fluid" style="height: 85px; width: 85px;">

                                                    </div><!--image-review-cnt -->
                                                </div>
                                                <div itemprop="review" itemtype="https://schema.org/Review" itemscope>
                                                    <div class="col-sm-10-cell">
                                                        <div class="body-review-cntiner">
                                                            <div class="row-review-head">
                                                                <div class="rating-strip">
                                                                    <?php
                                                                                    for ($i = 1; $i <= 5; $i++) {
                                                                                        if ($i <= $count) {
                                                                                            $class = "fa fa-star";
                                                                                        } else {
                                                                                            $class = "fa fa-star-o";
                                                                                        }
                                                                                        ?>
                                                                    <i class="<?php echo $class; ?>"
                                                                        aria-hidden="true"></i>
                                                                    <?php } ?>

                                                                </div>
                                                            </div>
                                                            <div><span itemprop="author" class="name-show-rev"
                                                                    style="font-size: 12px;">{{ $user_name }}</span>
                                                                <span class="seprator-line">|</span>
                                                                <span itemprop="datePublished"
                                                                    content="{{ $date }}"
                                                                    class="date-reivew-rev"
                                                                    style="font-size: 12px;">{{ $date }}</span>
                                                            </div>
                                                        </div>
                                                        <div itemprop="reviewBody" class="body-review-text-bdy">
                                                            <p style="margin-top: -6px;">{{ $comment }}</p>
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
                                                        echo '<style>#rating { display: none; }</style>';
                                                        echo '<style>.returnExchangeBox { margin-top: 25px; }</style>';
                                                        echo '<style>.nav-link[href="#rating"] { display: none; }</style>';
                                                    }
                                                }
                                                ?>
                                        <section class='rating-widget'>
                                            <?php if (canUserReview($productDetail['product_id'])) {
                                                        ?>
                                            <div class="post-review-heading">
                                                <h3>Post Review</h3>
                                            </div>


                                            <form action="" method="post">
                                                <div class="review-body" style="margin-left:0px;">
                                                    <div class="response"></div>
                                                    <p>Describing your experience with this product or seller</p>
                                                    <textarea class="form-control" id="rating-text"></textarea>
                                                    <!--Rating Stars Box -->
                                                    <div class='rating-stars clearfix'>
                                                        <div class="your-rating-headeing">Your Rating:</div>
                                                        <div class="star-rating-panel-mbt">
                                                            <div class="star-rating">
                                                                <span class="fa fa-star-o" data-rating="1"></span>
                                                                <span class="fa fa-star-o" data-rating="2"></span>
                                                                <span class="fa fa-star-o" data-rating="3"></span>
                                                                <span class="fa fa-star-o" data-rating="4"></span>
                                                                <span class="fa fa-star-o" data-rating="5"></span>
                                                                <input type="hidden" id="rating_count"
                                                                    name="rating_count" class="rating-value"
                                                                    value="0">
                                                            </div>
                                                        </div>

                                                        <div class="review-button-post">
                                                            <button class="btn btn-primary" id="submit-review">
                                                                Post Your Review
                                                            </button>
                                                            <span>Note: you can post your review and rating only
                                                                once.</span>
                                                        </div>
                                                    </div>

                                                </div>
                                            </form>
                                        </section>
                                        <?php } ?>
                                    </div>
                                </div>
                            </div>




                        </div>
                    </li>

                </ul>
            </div>
        </div>
    </div>
</div><!--product-tabs ends -->
<div class="short-description">
    <?php $short_description = isset($productDetail['short_description']) ? $productDetail['short_description'] : ''; ?>
</div>

</div>
</div>
<!--tabs-body -->
<div class="shipping-gray-sec shipped-box-btm" style="background-color: white;">
    <div class="container">
        <div class="row row-repeater-list">
            <?php
                    if (!empty($shippingDetails)) {
                        
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
                            <img src="{{ asset('assets/images/ship-icon1.png', env('HTTPS_STATUS')) }}"
                                alt="image" title="image">
                        </span>
                        <?php if ($shippingDetails->processing_time_id == 11) { ?>
                        Preparation takes: Unknown
                        <?php } else { ?>
                        Preparation takes <?php echo !empty($shippingDetails->process_start_range) ? $shippingDetails->process_start_range : ''; ?> - <?php echo !empty($shippingDetails->process_end_range) ? $shippingDetails->process_end_range : ''; ?> business
                        <?php echo !empty($shippingDetails->process_end_range) ? $shippingDetails->process_unit : ''; ?></span>
                    <?php } ?>
                </div><!--row-list-mbt -->
                <div class="row-list-mbt">
                    <span class="icon-view"></span>

                    <span class="text-pre-vie">
                        <span class="icn-shp">
                            <img src="{{ asset('assets/images/ship-icon2.png', env('HTTPS_STATUS')) }}"
                                alt="image" title="image">
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
                        $shipPrice = 'Free Shipping in USA';
                    } else {
                        $productShippingPrice = isset($productShippingPrice) ? '$ ' . $productShippingPrice : 0;
                    
                        $shipPrice = !empty($productShippingPrice_usd) ? '($ ' . $productShippingPrice_usd . ')' : 0;
                    }
                    ?>

                    <span class="shipping-cost">Shipping Cost: <b><?php echo $productShippingPrice . ' ' . $shipPrice; ?></b></span>
                </div><!--row-list-mbt -->

            </div>
            <?php } ?>
        </div><!--row-repeater-list -->

 

        <div class="row ">
            
 
            <div class=" returnExchangeBox">
                <div class="col-lg-12">

                <div>
                <h3>Returns, Exchanges, Store Policies
                    </h3>

                    <ul>
                        <li>- I gladly accept returns, exchanges and cancelations.</li>
                        <li>- Just contact me within: 14 days of delivery.</li>
                        <li>- Ship items back to me within: 30 days of delivery.</li>
                        <li>- Request a cancelation: before item has shipped.</li>
                    </ul>
                </div>

                    <div class="mt-50" style="display: none;">
                    <h5>Product Warranty</h5>
                    <p style="font-size: 14px;color: #2f2f2f;margin-bottom:0px;">This item has no store warranty included.</p>
                    </div>
                   
                    
                </div>

            </div>
            



        </div>
        <!--row-repeater-list -->

    </div>

</div>
<div class="container rltdprdcts hiddenDesktop">
    <div class="row">
    <?php
       
        // echo 'Data: <pre>' .print_r($recommendedProducts,true). '</pre>'; die();
    ?>
        <div class="col-12">


            <aside>



                <?php 
                // echo 'Data: <pre>' .print_r($relatedProduct,true). '</pre>'; die();
                if (!$relatedProduct->isEmpty()) { ?>
                <div class="related-product">

                    <h3 class="page-heading">Related Products</h3>
                    <div class="row">
                        <?php foreach ($relatedProduct as $rp) { 
                             if ($rp->product_type == 'simple') {
                                $current_stock = $rp->current_stock;
                                if ($current_stock > 0) {
                                    $class = 'current-stock-active';
                                    $opecity = '';
    
                                }else{
                                    $class = 'current-stock-deactive';
                                    $opecity = 'opecity-deactive';
                                }
                            }

                            ?>




                        <div class="col-md-2 col-6">
                            <!-- card starts -->
                            <div class="card">
                                <?php $productImage = getAttachmentUrlById((int) !empty($rp->feature_image) ? $rp->feature_image : '', 'medium'); ?>
                                <a href="{{ URL::to('/product/' . $rp->slug) }}"><img src="{{ $productImage }}"
                                        class="card-img-top product-thumbnail" alt="{{ $rp->title }}"
                                        title="{{ $rp->title }}"></a>
                                <div class="card-body">
                                    <div style="min-height: 143px;">
                                    <div class="mb-1 discount-container">
                                                                        
                                        @if($rp->on_sale == 1 && $rp->discount_percentage > 0)
                                            <span class="z-symbl product-price discountbadge" style="position:relative!important;top:0px!important;font-weight:600;">
                                                -{{ $rp->discount_percentage }}% off
                                            </span><b class="discountbadge-text">Limited time offer</b>
                                            @else
                                            <!-- <span style="display: inline-block;margin-bottom: 8px;font-size: 18px;font-weight: 600;position: relative!important;top:0px!important; z-in" class="z-symbl product-price discountbadge" >
                                                {{ $rp->discount_percentage }}% off
                                            </span><b class="discountbadge-text">Limited time offer</b> -->
                                        @endif
                                    
                                    </div>
                                    <div class="sub-cateogry-title product_parent_category ">
                                        <span class="catnm card-category"><?php echo $rp->parentCategoryTitle . '&nbsp;'; ?></span>
                                        <span class="catnm catnm1 card-category"><?php echo '(' . $rp->categoryTitle . ')'; ?></span>
                                    </div>
                                    <h4 class="card-title"><a href="{{ URL::to('/product/' . $rp->slug) }}">
                                            {{ $rp->title }}</a></h4>
                                    </div>

                                    <div>






                                        <div class="prcbxint">
                                            <div>
                                                <p class="card-text prc">
                                                    @if($rp->on_sale == 1)
                                                        <span class="n-price"><span class="z-symbl">$<?php echo $rp->sale_price ?></span>
                                                            </span >
                                                        <del class=""><span class="">$<?php echo $rp->price; ?></span>
                                                            </del>
                                                    @else
                                                        <span class="n-price"><span class="z-symbl">$<?php echo$rp->price; ?></span>
                                                        </span>
                                                    @endif

                                                </p>
                                            </div>

                                            <div class="card-text small ">
                                                <div class="splopt">

                                                    <?php
                            $fill = "";
                            if (Session::has('userData')) {

                                $favProductId = getBuyerFavProducts();
                                if (!empty($favProductId) && in_array($rp->product_id, $favProductId)) {
                                    $fill =  "fillheart";
                                } ?>
                                                    <a data-product-id="<?php echo $rp->product_id; ?>"
                                                        class="dill adfv adtofv-home <?php echo $fill; ?>"
                                                        href="javascript:void(0);"><img class="image-add_tofav-s" style="width:30px; position:relative;"  src="{{ URL::asset($fill ? 'assets/images/heart.svg' : 'assets/images/heart-v2.svg') }}" alt="rate">
                                                        </a>
                                                        <?php  } else {
                                $currenturl = url()->full();
                                ?>
                                                        <a class=" adfv fav-login login-popup"
                                                            data-location="{{ $currenturl }}"
                                                            href="javascript:void(0)" data-toggle="modal"
                                                            data-target="#login-registerpopup">
                                                            <img class="image-add_tofav"
                                                                data-product-id="<?php echo $rp->product_id; ?>"
                                                                style="width:30px; position:relative;"
                                                                src="{{ URL::asset('assets/images/heart-v2.svg') }}"
                                                                alt="rate">

                                                        </a>
                                                        <?php   } ?>

                                                        <?php
                                $userId= "";
                                $userId = isset(Session::get('userData')['user_id']) ? Session::get('userData')['user_id'] : '';
                                if (Session::has('userData') && $userId == $rp->user_id) { ?>
                                                        <a class="notbuy" href="javascript:void(0)"><span style="background-color: #073ba5;padding:10px;border-radius:50%;">
                                                        <i class="fa fa-shopping-cart" style="color:#fff;font-size:17px;position:relative;top:1px;" aria-hidden="true"></i> </span> </a>
                                                        <?php  } else if ($rp->product_type == 'variant') { ?>
                                                        <a class="" data-type="{{ $rp->product_type }}"
                                                            href="{{ URL::to('/product/' . $rp->slug) }}"><span style="background-color: #073ba5;padding:10px;border-radius:50%;">
                                                            <i class="fa fa-shopping-cart" style="color:#fff;font-size:17px;position:relative;top:1px;" aria-hidden="true"></i> </span></a>
                                                        <?php  } else { ?>
                                                        <a class="cartadd-home {{$class}}"
                                                            data-type="{{ $rp->product_type }}"
                                                            data-stock="{{ $rp->current_stock }}"
                                                            data-store-id="<?php echo $rp->store_id; ?>"
                                                            data-product-id="<?php echo $rp->product_id; ?>"
                                                            href="javascript:void(0)"><span style="background-color: #073ba5;padding:10px;border-radius:50%;">
                                                            <i class="fa fa-shopping-cart {{$opecity}}" style="color:#fff;font-size:17px;position:relative;top:1px;" aria-hidden="true"></i> </span></a>
                                                        <?php   }  ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>







                        <div class="col-lg-2 col-md-4 col-sm-6 d-none">
                            <div class="prdt-sngle-bx">
                                <div class="card">
                                    <?php $productImage = getAttachmentUrlById((int) !empty($rp->feature_image) ? $rp->feature_image : '', 'medium'); ?>
                                    <a href="<?php echo URL::to('product/' . $rp->slug); ?>" class="image-container-mbt"> <img
                                            src="<?php echo $productImage; ?>" alt="{{ $rp->title }}"
                                            title="{{ $rp->title }}" class="img-fluid"> </a>

                                    <div class="card-body">
                                        <h3 class="card-title"><a
                                                href="<?php echo URL::to('product/' . $rp->slug); ?>"><?php echo !empty($rp->title) ? substr($rp->title, 0, 35) : ''; ?></a>
                                        </h3>

                                        <div class="d-flex" style="justify-content: space-between; ">

                                            <div class="product-category">
                                                <p>
                                                    <span class="cat catnm">
                                                        <?php
                                                        echo isset($rp->categoryTitle) ? $rp->categoryTitle : '';
                                                        ?>
                                                    </span>
                                                </p>

                                            </div><!-- box-description-mbt -->
                                            <div class="cartsec" style="display:none;">
                                                <p>

                                                    <span class="z-symbl">$
                                                        <!-- <img
                                                            src=" {{ asset('assets/images/z-symbal.png', env('HTTPS_STATUS')) }}" alt="" class="img-fluid"> -->
                                                    </span>
                                                    <?php echo !empty($rp->price) ? $rp->price : ''; ?>

                                                </p>

                                            </div><!-- box-description-mbt -->

                                        </div>

                                        <div class="prc d-flex justify-content-between">


                                            <span class="zit-price">
                                                <span class="z-symbl">$
                                                    <!-- <img
                                                            src=" {{ asset('assets/images/z-symbal.png', env('HTTPS_STATUS')) }}" alt="" class="img-fluid"> -->
                                                </span>
                                                <?php echo !empty($rp->price) ? $rp->price : ''; ?>

                                        </div>


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
</section>
@section('meta')
    @include('layouts.products-meta')
@stop
<div class="uploadImages" style="display:none;">
    <span></span>
</div>
</main>
</div>
<!--Content Ends-->
<style>
    .myButton:disabled,
    .mySVG {
        pointer-events: none;
    }

    hr {
        margin-top: 0px;
    }

    iframe {
        height: 80vh;
        width: 80vw;
    }

    .slick-lightbox-close:before {
        font-size: 40px;
    }

    .slick-lightbox .slick-arrow.slick-next:before,
    .slick-lightbox .slick-arrow.slick-prev:before {
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

    .product-overview {
        margin-bottom: 20px
    }

    table.table-overview {
        width: 100%;
    }

    .productDescriptionContainer {
        overflow: hidden;
    }

    .productDescriptionContainer img {
        max-width: 100%
    }

    .has-error {
        border: 1px solid red !important;
    }

    .jssocials-share-link {
        border-radius: 50%;
    }

    .supporter-img .anmls {
        margin-bottom: 5px;
    }

    .modal-dialog {
        max-width: 800px;
        margin: 30px auto;
    }

    .scrolling {
        overflow-y: scroll !important;
    }

    .shrtdesc li {
        list-style-type: disc;
        margin-left: 5px;
    }
   .productDetailCOntainerMain > main > section div.shrtdesc.not-empty-des > ul{
    margin-left: 13px;
   }

    .slider-opascity {
        opacity: 1 !important;
    }

    button.primary-btn.btn.btn-primary.w100p.text-white.notinstock_out_of_stock.background-clr {
        border-radius: 20px !important;
    }

    .out-of-stock-product {
        display: none;
        border-radius: 6px;
    }

    .out-of-stock-product:hover {
        background-color: #fff;
        color: #0510fa !important;
    }
</style>
<!--script for slick slider -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.9.0/slick.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/jquery.jssocials/1.4.0/jssocials.min.js"></script>
<script type="text/javascript"
    src="https://cdnjs.cloudflare.com/ajax/libs/magnific-popup.js/1.1.0/jquery.magnific-popup.min.js"></script>
<script type="text/javascript" src="https://jongacnik.github.io/slick-lightbox/dist/slick-lightbox.js"></script>

<!--script for countdown js-->
<?php
$time = getFlashSaleDate($productDetail['product_id']);
?>
<script type="text/javascript" src="{{ URL::asset('assets/js/jquery.plugin.min.js') }}"></script>
<script type="text/javascript" src="{{ URL::asset('assets/js/jquery.countdown.new.js') }}"></script>
<script type="text/javascript" src="{{ URL::asset('assets/js/cmGauge.js') }}"></script>



<script>
  let variation_discount_percentage_handler = (res) => {
      const parsedRes = JSON.parse(res);
      const results = parsedRes.results;
      if (results && results.length > 0) {
        return results.map(item => item.discount_percentage);
        } else {
            return []; 
        }
    }
    $("#share").jsSocials({
        showLabel: false,
        showCount: false,
        shares: ["facebook", "twitter", "googleplus", "pinterest"]
    });
    // Can also be used with $(document).ready()
    function getCurrentURL() {
        return window.location.origin
    }
    var csrfToken = $('meta[name="csrf-token"]').attr('content');
    $(document).on("change", ".variation-change", function() {
        var variation_discount_percentage = '';
        var variation1 = $('#variation1').val();
        var variation2 = $('#variation2').val();
        var variation3 = $('#variation3').val();
        
        almostSoldOutHandler();

        var productId = "<?php echo $productDetail['product_id'];?>"
        var onSales = "<?php echo $productDetail['on_sale']; ?>"
        var data = {};

        if (variation1 && variation2 && (typeof variation3 === 'undefined')) {
            $('.variation-instock').addClass('d-none');
            data['variation1'] = variation1;
            data['variation2'] = variation2;
            data['productId'] = productId;
            console.log('first');
            jQuery.ajax({
                'url': '/product/get-variations-info',
                'Type': 'POST',
                'method': 'POST',
                'data': {
                    data,
                    _token: csrfToken
                },
                'success': function(res) {
                 variation_discount_percentage = variation_discount_percentage_handler(res);
                    obj = JSON.parse(res);


                    if (obj == "" || obj.results.length == 0) {
                        $(".cartadd").text("Out of stock");
                        $(".cartadd").addClass("disabled");
                        $(".cartadd").addClass("out-of-stock-product");
                        $(".cartadd").removeClass("btn-primary");
                        $(".cartadd").addClass("btn-simple");
                        //$(".cartadd").hide();
                        jQuery(".notinstock_out_of_stock").show();
                        jQuery(".outofstock").show();
                        jQuery("#variable_product").hide();
                        // jQuery("#product-price").hide();
                        jQuery(".cart-sec-btn").hide();
                        $(".cartadd").addClass("disabled");
                        // jQuery('.empty-response').html("<div class='responsive-body add-cart-notification'>This combination is not available</div>");
                        // $('.notinstock_out_of_stock').show()
                        $(".not-available").show();
                        // $("#prod-price").hide();
                        $(".tag-shippingcost").hide();
                        jQuery('#variable_product').hide();
                    } else {
                        if(onSales > 0){
                            if(variation_discount_percentage > 0){
                                    $(".cartadd").text("Add to Cart - " + variation_discount_percentage + "% off");
                                    $(".buyNowBtn").text("Buy Now - " + variation_discount_percentage + "% off");
                                }else{
                                    $(".cartadd").text("Add to Cart");
                                    $(".buyNowBtn").text("Buy Now");
                                }
                        }else{
                            $(".cartadd").text("Add to Cart");
                            $(".buyNowBtn").text("Buy Now");
                        }
                        // $(".cartadd").text("Add to Cart");
                        $(".cartadd").prop("disabled", false);
                        $(".cartadd").removeClass("btn-simple");
                        $(".cartadd").addClass("btn-primary");
                        $(".cartadd").removeClass("out-of-stock-product");
                        jQuery(".outofstock").hide();
                        jQuery("#product-price").show();
                        jQuery(".cart-sec-btn").show();
                        $('.notinstock_out_of_stock').hide()
                        jQuery('#variable_product').show();
                        $(".cartadd").removeClass("disabled");
                        $(".cartadd").show();
                        $(".local-taxes-include").show();
                        jQuery('.empty-response').html("");
                        $(".not-available").hide();
                        $("#prod-price").show();
                        quantity = $("#product_quantity").val();
                        if (quantity == undefined) {
                            quantity = 1;
                        }
                        total = (obj.results[0].price) * quantity;
                        console.log(quantity);
                        if (obj.results[0].sale_price && obj.results[0].sale_price != 0 && obj
                            .results[0].sale_price != null) {
                            totalSalePrice = parseFloat(obj.results[0].sale_price) * quantity;
                            $("#sale-price").html("$" + totalSalePrice.toFixed(2));
                            $("#sale-price").attr('data-price', obj.results[0].sale_price);
                        }

                        
                        $("#product-price").html("$" + total.toFixed(2));
                        $("#product-price").attr('data-price', obj.results[0].price);
                        $(".offPricePercentage").html("- " + parseFloat(variation_discount_percentage) + " off");

                        //alert(rooturl);
                        if (obj.productImage != "" && obj.productImage != null) {
                            // Feature Image src load variation image 
                            $('.slick-lightbox-slick-img').addClass("variation_image_selector");
                            $('.feature_image_set').attr('src', obj.productImage);

                            $('.gallery-items').each(function(index) {
                                var element = $(this);
                                let imageid = element.attr('data-image-id');
                                let dataindex = element.attr('data-index');

                                let variable_image_id = obj.results[0]
                                    .product_variation_image;
                                if (variable_image_id == imageid) {
                                    $('.slider-for').slick('slickGoTo', dataindex);
                                }

                            });

                            // $('#set_variation_image').attr('src', obj.productImage);
                            // $('#set_variation_image_'+obj.results[0].feature_image).attr('src', obj.productImage);
                        } else {
                            $('.feature_image_set').removeAttr('');
                            $('#set_variation_image').removeAttr('');
                        }
                        if (obj.results[0].current_stock != '') {
                            if(obj.results[0].current_stock > 0){
                                    $('.variation-instock').removeClass('d-none');
                                }
                            if (obj.results[0].current_stock >= 10) {
                                obj.results[0].current_stock = 10;
                            }

                            if (obj.results[0].maxquantitylimit == null || obj.results[0]
                                .maxquantitylimit === '') {
                                // If max quantity is not defined, use current stock
                                obj.results[0].current_stock = obj.results[0].current_stock;
                            } else if (obj.results[0].maxquantitylimit == 0) {
                                obj.results[0].current_stock = 1;
                            } else if (obj.results[0].current_stock > obj.results[0]
                                .maxquantitylimit) {
                                // If current stock is greater than max quantity, use max quantity
                                obj.results[0].current_stock = obj.results[0].maxquantitylimit;
                            } else if (obj.results[0].current_stock < obj.results[0]
                                .maxquantitylimit) {
                                obj.results[0].current_stock = obj.results[0].current_stock;
                            }
                            $('.variant-quantity-plus').prev().val(1)
                            $('.variant-quantity-plus').prev().attr('data-quantity-target', 1)
                            $('.variant-quantity-plus').attr('data-current_stock', obj.results[0]
                                .current_stock)
                            $('.variant-quantity-plus').attr('data-maxquantitylimit', obj.results[0]
                                .current_stock)

                            var options = '';
                            for ($i = 1; $i <= obj.results[0].current_stock; $i++) {
                                options += '<option value="' + $i + '">' + $i + '</option>'
                            }
                            jQuery('#product_quantity').html(options);
                            jQuery('#variable_products').show();
                        }

                    }
                }
            });

        }


        if (variation1 && (typeof variation2 === 'undefined') && (typeof variation3 === 'undefined')) {
            
            $('.variation-instock').addClass('d-none');

            data['variation1'] = variation1;
            data['productId'] = productId;
            jQuery.ajax({
                'url': '/product/get-variations-info',
                'Type': 'POST',
                'method': 'POST',
                'data': {
                    data,
                    _token: csrfToken
                },
                'success': function(res) {
                     variation_discount_percentage = variation_discount_percentage_handler(res);

                    obj = JSON.parse(res);
                    if (obj == "") {
                        //$(".cartadd").hide();
                        $(".cartadd").addClass("disabled");
                        // jQuery('.empty-response').html("<div class='responsive-body add-cart-notification'>This combination is not available</div>");
                        $(".not-available").show();
                        // $("#prod-price").hide();
                        $(".tag-shippingcost").hide();
                        $('.notinstock_out_of_stock').show()
                        jQuery('#variable_product').hide();
                    } else {
                        $('.notinstock_out_of_stock').hide()
                        jQuery('#variable_product').show();
                        $(".cartadd").removeClass("disabled");
                        $(".cartadd").show();
                        $(".local-taxes-include").show();
                        jQuery('.empty-response').html("");
                        $(".not-available").hide();
                        $("#prod-price").show();
                        quantity = $("#product_quantity").val();
                        if (quantity == undefined) {
                            quantity = 1;
                        }
                        if (obj.results === undefined || obj.results.length == 0) {
                            $(".cartadd").text("Out of stock");
                            $(".cartadd").addClass("disabled");
                            $(".cartadd").prop("disabled", true);
                            $(".cartadd").addClass("out-of-stock-product");
                            $(".cartadd").removeClass("btn-primary");
                            $(".cartadd").addClass("btn-simple");

                            jQuery(".notinstock_out_of_stock").show();
                            jQuery(".outofstock").show();
                            jQuery("#variable_product").hide();
                            // jQuery("#product-price").hide();
                            jQuery(".cart-sec-btn").hide();
                        } else {
                            if(variation_discount_percentage > 0){
                                $(".cartadd").text("Add to Cart - " + variation_discount_percentage + "% off");
                                $(".buyNowBtn").text("Buy Now - " + variation_discount_percentage + "% off");
                            }else{
                                $(".cartadd").text("Add to Cart");
                                $(".buyNowBtn").text("Buy Now");
                            }
                            $(".cartadd").removeClass("btn-simple");
                            $(".cartadd").addClass("btn-primary");
                            jQuery(".notinstock_out_of_stock").hide();
                            $(".cartadd").prop("disabled", false);
                            $(".cartadd").removeClass("out-of-stock-product");
                            jQuery("#variable_product").show();
                            jQuery("#product-price").show();
                            jQuery(".cart-sec-btn").show();
                            total = parseFloat(obj.results[0].price) * quantity;
                            if (obj.results[0].sale_price && obj.results[0].sale_price != 0 && obj
                                .results[0].sale_price != null) {
                                totalSalePrice = parseFloat(obj.results[0].sale_price) * quantity;
                                $("#sale-price").html("$" + totalSalePrice.toFixed(2));
                                $("#sale-price").attr('data-price', obj.results[0].sale_price);
                            }
                            $("#product-price").html("$" + total.toFixed(2));
                            $("#product-price").attr('data-price', obj.results[0].price);
                            $(".offPricePercentage").html("- " + parseFloat(variation_discount_percentage) + " off");
                            
                            if (obj.results[0].current_stock != '') {
                                if(obj.results[0].current_stock > 0){
                                    $('.variation-instock').removeClass('d-none');
                                }
                                if (obj.results[0].current_stock >= 10) {
                                    obj.results[0].current_stock = 10;
                                }
                                if (obj.results[0].maxquantitylimit == null || obj.results[0]
                                    .maxquantitylimit === '') {
                                    // If max quantity is not defined, use current stock
                                    obj.results[0].current_stock = obj.results[0].current_stock;
                                } else if (obj.results[0].maxquantitylimit == 0) {
                                    obj.results[0].current_stock = 1;
                                } else if (obj.results[0].current_stock > obj.results[0]
                                    .maxquantitylimit) {
                                    // If current stock is greater than max quantity, use max quantity
                                    obj.results[0].current_stock = obj.results[0].maxquantitylimit;
                                } else if (obj.results[0].current_stock < obj.results[0]
                                    .maxquantitylimit) {
                                    obj.results[0].current_stock = obj.results[0].current_stock;
                                }
                               
                                $('.variant-quantity-plus').prev().val(1)
                                $('.variant-quantity-plus').prev().attr('data-quantity-target', 1)
                                $('.variant-quantity-plus').attr('data-current_stock', obj.results[
                                    0].current_stock)
                                $('.variant-quantity-plus').attr('data-maxquantitylimit', obj
                                    .results[0].current_stock)
                                jQuery('.price-sec .stock-remaining span').html(obj.results[0]
                                    .current_stock + ' instock');
                                var options = '';
                                for ($i = 1; $i <= obj.results[0].current_stock; $i++) {
                                    options += '<option value="' + $i + '">' + $i + '</option>'
                                }
                                jQuery('#product_quantity').html(options);
                                jQuery('#variable_product').show();
                            }
                        }
                    }
                    if (obj.productImage != "" && obj.productImage != null) {
                        $('.gallery-items').each(function(index) {
                            var element = $(this);
                            let imageid = element.attr('data-image-id');
                            let dataindex = element.attr('data-index');

                            let variable_image_id = obj.results[0].product_variation_image;
                            if (variable_image_id == imageid) {
                                $('.slider-for').slick('slickGoTo', dataindex);
                            }

                        });

                    } else {
                        $('.feature_image_set').removeAttr('');
                        $('#set_variation_image').removeAttr('');
                    }


                }
            });

        }
        if (variation1 && variation2 && variation3) {
           
            $('.variation-instock').addClass('d-none');
            data['variation1'] = variation1;
            data['variation2'] = variation2;
            data['variation3'] = variation3;
            data['productId'] = productId;
            jQuery.ajax({
                'url': '/product/get-variations-info',
                'Type': 'POST',
                'method': 'POSt',
                'data': {
                    data,
                    _token: csrfToken
                },
                'success': function(res) {
                 variation_discount_percentage = variation_discount_percentage_handler(res);
                    obj = JSON.parse(res);

                    if (obj.results.length == 0) {
                        //$(".cartadd").hide();

                        $(".cartadd").text("Out of stock");
                        $(".cartadd").addClass("disabled");
                        $(".cartadd").prop("disabled", true);

                        $(".cartadd").addClass("out-of-stock-product");
                        $(".cartadd").removeClass("btn-primary");
                        $(".cartadd").addClass("btn-simple");
                        jQuery(".notinstock_out_of_stock").show();
                        jQuery(".outofstock").show();
                        // jQuery('.empty-response').html("<div class='responsive-body add-cart-notification'>This combination is not available</div>");
                        jQuery('#variable_product').hide();
                        // $(".not-available").show();
                        // $("#prod-price").hide();
                        // $(".tag-shippingcost").hide();
                        jQuery(".cart-sec-btn").hide();
                    } else {
                        if(variation_discount_percentage > 0){
                                $(".cartadd").text("Add to Cart - " + variation_discount_percentage + "% off");
                                $(".buyNowBtn").text("Buy Now - " + variation_discount_percentage + "% off");
                            }else{
                                $(".cartadd").text("Add to Cart");
                                $(".buyNowBtn").text("Buy Now");
                            }
                        $(".cartadd").removeClass("disabled");
                        $(".cartadd").prop("disabled", false);
                        jQuery(".cart-sec-btn").show();
                        $(".cartadd").removeClass("out-of-stock-product");
                        $(".cartadd").show();
                        $(".cartadd").addClass("btn-primary");
                        $(".cartadd").removeClass("btn-simple");
                        $(".local-taxes-include").show();
                        jQuery('.empty-response').html("");
                        $(".not-available").hide();
                        $("#prod-price").show();
                        quantity = $("#product_quantity").val();
                        if (quantity == undefined) {
                            quantity = 1;
                        }
                        if (obj.results[0].sale_price && obj.results[0].sale_price != 0 && obj
                            .results[0].sale_price != null) {
                            totalSalePrice = parseFloat(obj.results[0].sale_price) * quantity;
                            $("#sale-price").html("$" + totalSalePrice.toFixed(2));
                            $("#sale-price").attr('data-price', obj.results[0].sale_price);
                        }
                        total = parseFloat(obj.results[0].price) * quantity;
                        $("#product-price").html("$ " + total.toFixed(2));
                        $("#product-price").attr('data-price', obj.results[0].price);
                        $(".offPricePercentage").html("- " + parseFloat(variation_discount_percentage) + "% off");
                        if (obj.productImage != "" && obj.productImage != null) {
                            // Feature Image src load variation image 

                            $('.feature_image_set').attr('src', obj.productImage);

                            // Galery Image src load variation image 
                            $('.gallery-items').each(function(index) {
                                var element = $(this);
                                let imageid = element.attr('data-image-id');
                                let dataindex = element.attr('data-index');

                                let variable_image_id = obj.results[0]
                                    .product_variation_image;
                                if (variable_image_id == imageid) {
                                    $('.slider-for').slick('slickGoTo', dataindex);
                                }


                            });
                            //$('#set_variation_image').attr('src', obj.productImage);
                            // $('#set_variation_image_'+obj.results[0].feature_image).attr('src', obj.productImage);
                        } else {
                            $('.feature_image_set').removeAttr('');
                            $('#set_variation_image').removeAttr('');
                        }

                        if (obj.results[0].current_stock != '') {
                            if(obj.results[0].current_stock > 0){
                                    $('.variation-instock').removeClass('d-none');
                                }
                            if (obj.results[0].current_stock >= 10) {
                                obj.results[0].current_stock = 10;
                            }
                            if (obj.results[0].maxquantitylimit == null || obj.results[0]
                                .maxquantitylimit === '') {
                                // If max quantity is not defined, use current stock
                                obj.results[0].current_stock = obj.results[0].current_stock;
                            } else if (obj.results[0].maxquantitylimit == 0) {
                                obj.results[0].current_stock = 1;
                            } else if (obj.results[0].current_stock > obj.results[0]
                                .maxquantitylimit) {
                                // If current stock is greater than max quantity, use max quantity
                                obj.results[0].current_stock = obj.results[0].maxquantitylimit;
                            } else if (obj.results[0].current_stock < obj.results[0]
                                .maxquantitylimit) {
                                obj.results[0].current_stock = obj.results[0].current_stock;
                            }
                            $('.variant-quantity-plus').prev().val(1)
                            $('.variant-quantity-plus').prev().attr('data-quantity-target', 1)
                            $('.variant-quantity-plus').attr('data-current_stock', obj.results[0]
                                .current_stock)
                            $('.variant-quantity-plus').attr('data-maxquantitylimit', obj.results[0]
                                .current_stock)
                            var options = '';
                            for ($i = 1; $i <= obj.results[0].current_stock; $i++) {
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


    function callbackBasket(data) {
        $(".cartmenu").html(data);
        $("html, body").animate({
            scrollTop: 0
        }, "slow");
        //  jQuery('.uploadImages').hide();
        $(".cart-list-nav").addClass("bounce-up");
        setTimeout(function() {
            $(".cart-list-nav").removeClass("bounce-up");
        }, 500);
        $("#view_cart").show();
    }

    function callbackBasket(data) {
        $(".cartmenu").html(data);
        $("html, body").animate({
            scrollTop: 0
        }, "slow");
        //  jQuery('.uploadImages').hide();
        $(".cart-list-nav").addClass("bounce-up");
        setTimeout(function() {
            $(".cart-list-nav").removeClass("bounce-up");
        }, 500);
        $("#view_cart").show();
    }

    $(document).on('click', '.adfvt', function() {
        current = $(this);
        var data = {};
        data['product_id'] = $(this).attr('data-product-id');
        sendreq(data, 'favourite', 'add', 'callbackfavourites');
        $(this).toggleClass("animate");
    });

    function callbackfavourites(data) {

    }
    $(document).on('change', '#product_quantity', function() {
        $(".large-loader").show();
        var data = {};
        data['product_id'] = $(this).attr('data-prodcut-id');
        data['quantity'] = $(this).val();
        price = $('#product-price').attr('data-price');
        quantity = $(this).val();
        total = parseFloat(price) * quantity;
        // $('#product-price').html("USDC "+total.toFixed(2));
        usdprice = parseFloat(total / 10).toFixed(2);
        $(".usdprice").html('($ ' + usdprice + ')');
        $(".large-loader").hide();
        // sendreq(data, 'shipping', 'calculate', 'callbackship');
    });

    function callbackship(data) {
        usdshipping = parseFloat(data / 10).toFixed(2);
        $(".usdshipping").html('($ ' + usdshipping + ')');
        $(".shipping_price").html(data);
        $(".large-loader").hide();
    }
    $('.flexslider-mobile').flexslider({
        animation: "slide"
    });



    $(document).ready(function(){
    // Initialize the main slider
    $('.slider-for').slick({
        slidesToShow: 1,
        slidesToScroll: 1,
        arrows: false,
        fade: true,
        asNavFor: '.slider-nav'
    });

    // Initialize the navigation slider
    var $sliderNav = $('.slider-nav');
    $sliderNav.slick({
        slidesToShow: 5,
        slidesToScroll: 1,
        vertical: true,
        asNavFor: '.slider-for',
        centerMode: false,
        focusOnSelect: true,
        arrows: true,
        verticalSwiping: true,
        infinite: false,
    });

    // Check the number of slides in the navigation slider
    // if ($sliderNav.find('.slick-slide').length < 5) {
    //     $sliderNav.addClass('less-than-five-slides');
    // }
});


 


    // $('.slider-nav').slick({
    //     slidesToShow: 3, // Number of thumbnails to show
    //     slidesToScroll: 1,
    //     asNavFor: '.slider-for', // Link to the main slider
    //     dots: false,
    //     centerMode: false,

    //     focusOnSelect: true
    // });

    // $('.slider-for').slick({
    //     dots: false,
    //     infinite: true,
    //     speed: 500,
    //     fade: true,
    //     cssEase: 'linear',
    //     asNavFor: '.slider-nav' // Add this line to link the main slider with the thumbnail slider
    // });




    $(document).ready(function() {
        // Add class to body on page load
        $('body').addClass('page-loaded-for-slick');

        // Initialize Slick Slider
        var $slider = $('.slider-fortttt');
        var $sliderCounter = $('.slider-counter');

        $slider.on('init', function(event, slick) {
            // Set the total number of slides
            $sliderCounter.find('.slider-counter--total').text(slick.slideCount);
            // Set the current slide number to 1 on load
            $sliderCounter.find('.slider-counter--current').text(slick.currentSlide + 1);
        });


        $slider.slick({
            centerMode: true,
            centerPadding: '0px',
            slidesToShow: 1,
            focusOnSelect: true,
            dots: false,
            infinite: false,
            responsive: [{
                    breakpoint: 768,
                    settings: {
                        arrows: true,
                        centerMode: true,
                        centerPadding: '20px',
                        slidesToShow: 1
                    }
                },
                {
                    breakpoint: 480,
                    settings: {
                        arrows: true,
                        centerMode: true,
                        centerPadding: '20px',
                        slidesToShow: 1
                    }
                }
            ]
        });

        // Listen for the slide change event
        $slider.on('afterChange', function(event, slick, currentSlide) {
            // Update the current slide number
            $sliderCounter.find('.slider-counter--current').text(currentSlide + 1);

            // Remove the class when the next button is clicked (or any slide change)
            if (currentSlide > 0) {
                $('body').removeClass('page-loaded-for-slick');
            }
        });





    });


    $(document).ready(function() {
        $('.accordion-list > li > .answer').hide();

        $('.accordion-list > li').click(function(event) {
            // Check if the click was inside the .answer div
            if ($(event.target).closest('.answer').length > 0) {
                return; // Do nothing if clicked inside .answer
            }

            if ($(this).hasClass("active")) {
                $(this).removeClass("active").find(".answer").slideUp();
            } else {
                $(".accordion-list > li.active .answer").slideUp();
                $(".accordion-list > li.active").removeClass("active");
                $(this).addClass("active").find(".answer").slideDown();
            }
            return false;
        });
    });


    $(document).on('click', '.notbuy', function() {
        //alert("You cannot buy your own product");
        swal("You cannot buy your own product");
    });
    // $('.slider-for').slick({
    // dots: false,
    //         infinite: true,
    //         speed: 500,
    //         fade: true,
    //         cssEase: 'linear'
    // });

    /**
     * Custom Slick init to load/unload iframe src on events
     */
    // $('.slider-for').slick({
    //         slidesToShow: 1,
    //         slidesToScroll: 1,
    //         arrows: true,
    //         dots: true,
    //         // Other Slick options
    //     });
    $('.fancybox').fancybox({
        // Fancybox options
        animationEffect: 'zoom', // Animation effect when opening the popup ('zoom', 'fade', 'slide')
        transitionEffect: 'slide', // Transition effect between slides ('fade', 'slide', 'circular', etc.)
        transitionDuration: 500, // Duration of the transition in milliseconds
        loop: true, // Enable looping through images
        buttons: ['slideShow', 'fullScreen', 'thumbs', 'close'], // Show navigation buttons
        clickContent: 'close', // Clicking the content closes the popup
        autoFocus: false, // Do not autofocus on the first element inside the popup
        protect: true, // Prevent right-click and keyboard shortcuts
        infobar: true, // Show the image count and arrows
        toolbar: true, // Show toolbar with buttons
        touch: {
            vertical: false // Disable vertical swipe gestures
        },
        image: {
            preload: true // Preload images for smoother transitions
        },
        // Callback functions
        beforeShow: function(instance, slide) {
            console.log('Before show:', slide);
        },
        afterShow: function(instance, slide) {
            console.log('After show:', slide);
        }
        // Add more options as needed
    })
    // Fancybox.close();

    // $('.slider-for').slickLightbox({
    // itemSelector: 'a',
    //         centerMode: false,
    //         navigateByKeyboarditemSelector: 'a',
    //         closeOnBackdropClick : true,
    //         closeOnEscape:  true,
    //         background: 'rgba(0, 0, 0, .7)',

    //         // src: 'src',
    //         // itemSelector: '.slick-slide img',
    //         // background: 'rgba(0, 0, 0, .7)',

    //         slick : function ($e) {

    //         $e.find('.slick-lightbox-slick-iframe').each(function () {
    //         $(this)
    //                 .attr('data-src', $(this).attr('src'))
    //                 .attr('src', '')
    //         })

    //                 function clearIframe (slick, index) {
    //                 var $iframe = $(slick.$slides.get(index)).find('.slick-lightbox-slick-iframe')
    //                         if ($iframe.length) {
    //                 setTimeout(function () {
    //                 $iframe.attr('src', '')
    //                 }, slick.options.speed)
    //                 }
    //                 }

    //         function loadIframe (slick, index) {
    //         var $iframe = $(slick.$slides.get(index)).find('.slick-lightbox-slick-iframe')
    //                 if ($iframe.length) $iframe.attr('src', $iframe.attr('data-src'))
    //         }

    //         /**
    //          * Return slick instance
    //          */
    //         return $e.find('.slick-lightbox-slick')
    //                 .on('init', function (event, slick) {
    //                 loadIframe(slick, slick.currentSlide)
    //                 })
    //                 .on('beforeChange', function (event, slick, currentSlide, nextSlide) {
    //                 clearIframe(slick, currentSlide)
    //                         loadIframe(slick, nextSlide)
    //                 })
    //                 .slick()
    //         }
    // }).on({
    // 'hidden.slickLightbox': function(){ 
    //   $("body").removeClass('overfollow-hidden'); }
    //     });
</script>
<script>
    jQuery(document).ready(function() {
        if (history.length == 1 || history.length == '1') {
            jQuery('.gobackbutton').hide();
        } else {
            jQuery('.gobackbutton').show();
        }
    });

    function goBack() {

        window.history.back();
    }

    jQuery(document).on('click', '.faq-question', function() {
        question = jQuery('#faqQuestion').val();
        if (question == '') {
            jQuery('#faqQuestion').addClass('has-error');
        } else {
            jQuery('#faqQuestion').removeClass('has-error');
            jQuery.ajax({
                url: '{{ route('ask_question') }}',
                data: 'question=' + question + '&product_id={{ $productDetail['product_id'] }}',
                method: 'POST',
                success: function(res) {

                    if (res.status == 'success') {
                        toastr.options = {
                            closeButton: true,
                            tapToDismiss: true,
                            positionClass: 'toast-top-right',
                            toastClass: 'toast-icon custom-toast-success',
                            progressBar: true,
                        };
                        toastr.success(res.message);
                        jQuery('#faqQuestion').val('');
                    }
                    if (res.status == 'error') {
                        toastr.options = {
                            closeButton: true,
                            tapToDismiss: true,
                            positionClass: 'toast-top-right',
                            toastClass: 'toast-icon custom-toast-error',
                            progressBar: true,
                        };
                        toastr.error(res.message);
                    }
                }
            });
        }
    });
    $(document).ready(function() {
        var $star_rating = $('.star-rating .fa');
        var SetRatingStar = function() {
            return $star_rating.each(function() {
                if (parseInt($star_rating.siblings('input.rating-value').val()) >= parseInt($(this)
                        .data('rating'))) {
                    return $(this).removeClass('fa-star-o').addClass('fa-star');
                } else {
                    return $(this).removeClass('fa-star').addClass('fa-star-o');
                }
            });
        };
        $star_rating.on('click', function() {
            $star_rating.siblings('input.rating-value').val($(this).data('rating'));
            return SetRatingStar();
        });
        SetRatingStar();

    });
    jQuery(document).on('click', '#submit-review', function(event) {
        comment = jQuery('#rating-text').val();
        jQuery('#rating-text').removeClass('has-error');
        event.preventDefault();
        rating_count = jQuery('#rating_count').val();
        if (rating_count < 1) {
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
                data: 'product_id={{ $productDetail['product_id'] }}&author_id={{ getCurrentUserId() }}&rating_count=' +
                    rating_count + '&comment=' + comment + '&action=add_review',
                method: 'POST',
                success: function(res) {
                    var obj = jQuery.parseJSON(res);
                    jQuery('.response').html(obj.message);
                    setTimeout(function() {
                        location.reload();
                    }, 1000);
                }
            });
        }
    });
    $('.ask-a-question .aska-question').on("click", function() {
        $(this).parent().parent().hide();
        $(this).parent().parent().parent().addClass('show-hide-div');
    });
    $("#rate-review-option").click(function() {
        $('html, body').animate({
            scrollTop: $("#rating-text").offset().top - 80
        }, 800);
    });
</script>
<script>
    jQuery(document).on('click', '.sndtockens', function(e) {
        var min = {{ $minimumPrice }};
        var val = parseInt(jQuery('.tockeninput').val());
        if (val < min) {
            event.preventDefault();
            alert('value should be greater or equal to ' + min);
            return false;
        }
    });
    $(function() {

        var note = $('#note'),
            ts = new Date(2012, 0, 1),
            newYear = true;
        if ((new Date()) > ts) {
            // The new year is here! Count towards something else.
            // Notice the *1000 at the end - time must be in milliseconds
            ts = (new Date()).getTime() + 10 * 24 * 60 * 60 * 1000;
            newYear = false;
        }


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
                    jQuery('nav.icnbar ul li.nav-item div.wallet-icon-header p span.pwr').html(obj
                        .user_wallet);
                    swal("", obj.message, "success");

                }
                if (obj.status == 'error') {
                    swal("", obj.message, "error");

                }
            }
        });
    });
    $(document).on('click', '.notinstock', function() {
        // alert("product is out of stock");
    });
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
            $("#fixme-lab").css({

            });
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
    $(document).on('keyup', function(evt) {
        if (evt.keyCode == 27) {
            $("body").removeClass('overfollow-hidden');
        }
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
                scrollTop: $(hash).offset().top - 126
            }, 600, function() {

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


    $(function() {
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
            success: function(res) {
                if (res.status == 'success') {
                    jQuery(current_actions).html('');
                    if (obj.user_wallet)
                        jQuery('nav.icnbar ul li.nav-item div.wallet-icon-header p span.pwr').html(obj
                            .user_wallet);
                    if (res.herd_details) {
                        var current_row = jQuery(current_actions).parent().parent().parent().parent();
                        jQuery(current_row).find('.anml .pwr').html(res.herd_details.icon_price);
                        jQuery(current_row).find('.icon_details_last_increase').html(res.herd_details
                            .perc_inc_dec + '%');
                        if (res.herd_details.herdInfo.lastTransaction)
                            jQuery(current_row).find('.icon_details_last_transaction').html(res.herd_details
                                .herdInfo.lastTransaction + '$');
                        else
                            jQuery(current_row).find('.icon_details_last_transaction').html('0USDC');
                        jQuery(current_row).find('.icon_details_icon_debt').html(res.herd_details
                            .icon_debt);
                        jQuery(current_row).find('.icon_details_total_earning').html(res.herd_details
                            .total_earning + '$');
                        jQuery(current_row).find('.icon_details_in_herd').html(res.herd_details.herdInfo
                            .inHerd);
                        jQuery(current_row).find('.icon_details_part_of_herd').html(res.herd_details
                            .herdInfo.partOfHerd);
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
    jQuery(document).ready(function() {
        jQuery('.icon-purchase').on('click', function(event) {
            total_to_pay = jQuery(this).attr('data-total');
            that = this;
            swal({
                    title: "Are you sure?",
                    text: "Your wallet will be debited by " + total_to_pay + " $",
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
    $('.product-detail-img-cnt .flexslider .slick-next,.product-detail-img-cnt .flexslider .slick-prev').click(
        function() {


        });
</script>
<script>
    // gtag('event', 'view_item', {
    // "items": [
    // {
    // "id": "<?php echo $productDetail['product_id']; ?>",
    //         "name": "<?php echo $titleProduct; ?>",
    //         "list_name": "Product Detail",
    //         "brand": "<?php echo $storeInfo[0]->store_name; ?>",
    //         "list_position": 1,
    //         "quantity": <?php echo $productDetail['current_stock']; ?>,
    //         "price": '<?php echo $productDetail['price']; ?>'
    // }
    // ]
    // });

    $(document).ready(function() {
        var $videoSrc;
        $('.video-btn').click(function() {
            $videoSrc = $(this).data("src");
        });
        // when the modal is opened autoplay it  
        $('#myModal').on('shown.bs.modal', function(e) {
            // set the video src to autoplay and not to show related video. Youtube related video is like a box of chocolates... you never know what you're gonna get
            $("#video").attr('src', $videoSrc + "?autoplay=1&amp;modestbranding=1&amp;showinfo=0");
        })
        // stop playing the youtube video when I close the modal
        $('#myModal').on('hide.bs.modal', function(e) {
            // a poor man's stop video
            $("#video").attr('src', $videoSrc);
        })

        $(".closebutton").click(function() {
            $("body").addClass("scrolling");
        });
    });
</script>
<script>
    (function() {
        "use strict";
        var jQueryPlugin = (window.jQueryPlugin = function(ident, func) {
            return function(arg) {
                if (this.length > 1) {
                    this.each(function() {
                        var $this = $(this);

                        if (!$this.data(ident)) {
                            $this.data(ident, func($this, arg));
                        }
                    });

                    return this;
                } else if (this.length === 1) {
                    if (!this.data(ident)) {
                        this.data(ident, func(this, arg));
                    }

                    return this.data(ident);
                }
            };
        });
    })();

    (function() {
        "use strict";

        function Guantity($root) {
            const element = $root;
            const quantity = $root.first("data-quantity");
            const quantity_target = $root.find("[data-quantity-target]");
            const quantity_minus = $root.find("[data-quantity-minus]");
            const quantity_plus = $root.find("[data-quantity-plus]");
            var quantity_ = quantity_target.val();
            let new_val = $('.quantity-input').val();



            $(quantity_minus).click(function() {
                var variation1 = $('#variation1').val();
                var variation2 = $('#variation2').val();
                var variation3 = $('#variation3').val();

                let prod_type = $(this).attr('data-product-type')

                if (prod_type != 'simple') {

                    if (variation1 && (typeof variation2 === 'undefined') && (typeof variation3 ===
                            'undefined')) {

                    } else if (variation1 && variation2 && (typeof variation3 === 'undefined')) {

                    } else if (variation1 && variation2 && variation3) {

                    } else {
                        return;
                    }
                }

                var quantity_ = $('.quantity-input').val();
                if (quantity_ > 1) {
                    quantity_target.val(--quantity_);
                }
            });

            $(quantity_plus).click(function() {
                var variation1 = $('#variation1').val();
                var variation2 = $('#variation2').val();
                var variation3 = $('#variation3').val();
                let prod_type = $(this).attr('data-product-type')

                if (prod_type != 'simple') {

                    if (variation1 && (typeof variation2 === 'undefined') && (typeof variation3 ===
                            'undefined')) {

                    } else if (variation1 && variation2 && (typeof variation3 === 'undefined')) {

                    } else if (variation1 && variation2 && variation3) {

                    } else {
                        return;
                    }
                }
                var quantity_ = $('.quantity-input').val();


                let product_type = $(this).attr('data-product-type');

                let variable_maximum_quantity = $(this).attr('data-maxquantitylimit');
                let variable_current_quantity = $(this).attr('data-current_stock');
                let sproduct_maximum_quantity = $(this).attr('data-maxquantity_simple');

                var currunt_stock = 0;
                if (product_type === 'virtual') {
                    // For virtual products, keep the quantity unchanged (not increase or decrease)
                    return;
                }
                if (product_type === 'variant') {

                    if (variable_maximum_quantity == null || variable_maximum_quantity === '') {
                        // If max quantity is not defined, use current stock      
                        variable_current_quantity = variable_current_quantity;
                    } else if (variable_maximum_quantity == 0) {
                        variable_current_quantity = 1;
                    } else if (variable_current_quantity > variable_maximum_quantity) {
                        // If current stock is greater than max quantity, use max quantity
                        variable_current_quantity = variable_maximum_quantity;
                    } else if (variable_current_quantity < variable_maximum_quantity) {
                        variable_current_quantity = variable_current_quantity;
                    }

                    if (parseInt(quantity_) < parseInt(variable_current_quantity)) {
                        quantity_target.val(++quantity_);
                    } else {
                        toastr.options = {
                            closeButton: true, // Add close button
                            disableTimeOut: true,
                            tapToDismiss: true,
                            positionClass: 'toast-top-right',
                            toastClass: 'toast-icon custom-toast-success'
                        };
                        toastr.success('You have reached maximum quantity limit');
                    }
                } else {

                    if (quantity_ && sproduct_maximum_quantity && parseInt(quantity_) < parseInt(
                            sproduct_maximum_quantity)) {
                        quantity_target.val(++quantity_);
                    } else {
                        toastr.options = {
                            closeButton: true, // Add close button
                            disableTimeOut: true,
                            tapToDismiss: true,
                            positionClass: 'toast-top-right',
                            toastClass: 'toast-icon custom-toast-success'
                        };
                        toastr.success('You have reached maximum quantity limit');
                    }
                }
            });
        }

        $.fn.Guantity = jQueryPlugin("Guantity", Guantity);
        $("[data-quantity]").Guantity();
    })();

    $(document).on("click", '.cartadd', function() {
        window.dataLayer = window.dataLayer || [];
        has_error = false;
        var optionArray = [];
        jQuery('.variation-change').each(function() {
            val = jQuery(this).val();
            if (val == '') {
                jQuery(this).addClass('has-error');
                has_error = true;
            } else {
                optionArray.push($(this).val());
                jQuery(this).removeClass('has-error');
            }
        });

        var data = {};
        if (optionArray != "") {
            data['vartion_options'] = optionArray;
        }
        data['product_id'] = $(this).attr('data-product-id');
        data['store_id'] = $(this).attr('data-store-id');
        data['product_quantity'] = $(".quantity-input").val();
        if (!has_error) {

            let product_title = $('.title-prp').text();
            let product_brand = $('.product-brand_name').text();
            let product_price = $('#product-price').text();
            let product_category = $('.product_category').attr('data-category');
            let product_stock = $('.product_stock_').attr('data-stock');
            let product_id = $('.product_id').attr('data-product-id');

            dataLayer.push({
                ecommerce: null
            });
            dataLayer.push({
                event: "add_to_cart",
                ecommerce: {
                    currency: "USD",
                    value: 7.77,
                    items: [{
                        item_id: product_id,
                        item_name: product_title,
                        index: 0,
                        item_brand: product_brand,
                        item_category: product_category,
                        item_list_id: "add_to_cart",
                        item_list_name: "Add to Cart",
                        price: product_price,
                        quantity: product_stock
                    }]
                }
            });

            sendreq(data, 'cart', 'add', 'callbackBasket');
        }
    });

    function callbackBasket(data) {

        $(".cartmenu").html(data);
        $("html, body").animate({
            scrollTop: 0
        }, "slow");
        //  jQuery('.uploadImages').hide();
        $(".cart-list-nav").addClass("bounce-up");
        setTimeout(function() {
            $(".cart-list-nav").removeClass("bounce-up");
        }, 500);
        $("#view_cart").show();


    }

    // Check if div.shrtdesc is empty
    if ($('div.shrtdesc').is(':empty')) {
        // Add class if empty
        $('div.shrtdesc').addClass('empty-class');
        $('div.shrtdesc').removeClass('not-empty-des');
    } else {
        // Remove class if not empty
        $('div.shrtdesc').removeClass('empty-class');
        $('div.shrtdesc').addClass('not-empty-des');
    }


    // Check if div.shrtdesc is empty
    if ($('div.ask-question-section').is(':empty')) {
        // Add class if empty
        $('div.ask-question-section').addClass('not-empty-des');

    } else {
        // Remove class if not empty
        $('div.ask-question-section').removeClass('not-empty-des');
    }


    // Check if p element contains only spaces
    if ($('.rating-widget').text().trim() === '') {
        // Hide the p element
        $('.rating-widget').addClass("mt-0");
    }



    $('body').addClass('product-detail-page-container');

    $(document).ready(function() {
        let product_title = $('.title-prp').text();
        let product_brand = $('.product-brand_name').text();
        let product_price = $('#product-price').text();
        let product_category = $('.product_category').attr('data-category');
        let product_stock = $('.product_stock_').attr('data-stock');
        let product_id = $('.product_id').attr('data-product-id');


        dataLayer.push({
            ecommerce: null
        });
        dataLayer.push({
            event: "view_item",
            ecommerce: {
                currency: "USD",
                value: 7.77,
                items: [{
                    item_id: product_id,
                    item_name: product_title,
                    index: 0,
                    item_brand: product_brand,
                    item_category: product_category,
                    item_list_id: "product_details",
                    item_list_name: "Product Detail",
                    price: product_price,
                    quantity: product_stock
                }]
            }
        });
    });


// addclass on body when scroll more than 200pixls

$(document).ready(function() {
    $(window).scroll(function() {
        var scrollTop = $(window).scrollTop();
        var windowWidth = $(window).width();

        // Apply only for screens larger than 768px (desktop size)
        if (windowWidth > 768) {
            if (scrollTop > 250) {
                $('body').addClass('scrolled'); // Adds class when scrolled past 200px
                $('.productAddToCardOption').addClass('fixed'); // Fixes the productAddToCardOption after 200px
            } else {
                $('body').removeClass('scrolled'); // Removes class when scrolled up
                $('.productAddToCardOption').removeClass('fixed'); // Unfixes the productAddToCardOption
            }
        } else {
            // Remove classes if on mobile
            $('body').removeClass('scrolled');
            $('.productAddToCardOption').removeClass('fixed');
        }
    });
});





</script>
<script>
    $(document).ready(function() {
        $('.dill').click(function() {
            const $this = $(this);
            const $img = $this.find('.image-add_tofav-s');
            $this.toggleClass('fillheart');
            
            if ($this.hasClass('fillheart')) {
                $this.removeClass('fillheart');
                $img.attr('src', "{{ URL::asset('assets/images/heart.svg') }}");
            } else {
                $this.addClass('fillheart');
                $img.attr('src', "{{ URL::asset('assets/images/heart-v2.svg') }}");
            }
            console.log('Current classes:', $this.attr('class'));

        });
    });
</script>
<script>

    // let badge = $(".custombadge");
    // let tooltip = $(".mallSharkSelectBAdgeToolTip");

    // badge.on("mouseenter", function () {
    //     tooltip.show();
    //     badge.css("cursor", "pointer");
    // });

    // badge.on("mouseleave", function () {
    //     tooltip.hide();
    // });


    $(document).ready(function () {
         almostSoldOutHandler = () => {

            const productId = $('#productId').val();
            const productType = $('#productType').val();
            var variation1 = $('#variation1').val();
            var variation2 = $('#variation2').val();
            var variation3 = $('#variation3').val();
    
            if (productId) {
                $.ajax({
                    url: "{{ route('pro.detail.almostSoldOut') }}",
                    type: 'POST',
                    data: {
                        id: productId,
                        type: productType,
                        v1: variation1,
                        v2: variation2,
                        v3: variation3,
                        _token: '{{ csrf_token() }}'
                    },
                    success: function (response) {
                        if(response.isAlmostSoldOut){
                            $('#mallshark-sold-out-badge').show();
                        }else{
                            $('#mallshark-sold-out-badge').hide();
                        }
                    },
                    error: function (xhr, status, error) {
                        console.error('Error:', error);
                        alert('An error occurred while processing your request.');
                    }
                });
            }
        }
        almostSoldOutHandler();
    });
</script>
<script>
var a2a_config = a2a_config || {};
a2a_config.onclick = 1;
a2a_config.num_services = 4;
</script>
<script>
  $(document).ready(function () {
    const buyNowButton = $('.buyNowBtn'); 
    buyNowButton.on('click', function (event) {
    event.preventDefault();
    const productType = $('#productType').val();
    const variations = [$('#variation1').val(), $('#variation2').val(), $('#variation3').val()];
    
    if (productType === 'variant') {
        const selectedVariations = variations.filter(variation => variation);
        const requiredVariations = variations.slice(0, variations.filter(v => v !== undefined).length); 

        if (selectedVariations.length < requiredVariations.length) {
            return false;
        }
        else{
            $('.cartadd').click();
            setTimeout(() => {
                window.location.href = '/checkout';
            }, 1000);

        }
    }else{
        $('.cartadd').click();
        setTimeout(() => {
                window.location.href = '/checkout';
            }, 1000);
    }
   
});

});

</script>


@endsection
