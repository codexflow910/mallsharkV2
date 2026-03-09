@extends('layouts.new-main')
<!-- Main Banner Starts -->
@section('content')
@section('title', 'Mallshark Product Detail')




<link rel="stylesheet" type="text/css"
      href="{{ URL::asset('assets/js/product-detail/xzoom.css' ,env('HTTPS_STATUS')) }}" media="all"/>

<link rel="stylesheet" type="text/css" href="{{ URL::asset('assets/css/fancy-modal.css' ,env('HTTPS_STATUS')) }}"
      media="all"/>

<link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/flexslider/2.7.0/flexslider.css"
      media="all"/>


<script type="text/javascript" src="{{ URL::asset('assets/js/fancy-modalPopup.js' ,env('HTTPS_STATUS')) }}"></script>

<!-- for magnifier popup for desktop version-->


<link type="text/css" rel="stylesheet" media="screen and (min-width: 767px) and (max-width: 40000px)"
      href="{{ URL::asset('assets/css/product-detail/jquery.fancybox.css' ,env('HTTPS_STATUS')) }}"/>


<link type="text/css" rel="stylesheet" media="screen and (min-width: 767px) and (max-width: 40000px)"
      href="{{ URL::asset('assets/css/product-detail/magnific-popup.css' ,env('HTTPS_STATUS')) }}"/>


<script type="text/javascript"
        src="https://cdnjs.cloudflare.com/ajax/libs/flexslider/2.7.1/jquery.flexslider-min.js"></script>


<script type="text/javascript"
        src="{{ URL::asset('assets/js/product-detail/xzoom.min.js' ,env('HTTPS_STATUS')) }}"></script>


<script type="text/javascript">

    (function () {
        if (window.innerWidth > 767) {
            var theScript = document.createElement('script');
            theScript.type = 'text/javascript';
            theScript.src = '/assets/js/product-detail/jquery.fancybox.js';

            var s = document.getElementsByTagName('script')[0];
            s.parentNode.insertBefore(theScript, s);
        }
    })();


    (function () {
        if (window.innerWidth > 767) {
            var theScript = document.createElement('script');
            theScript.type = 'text/javascript';
            theScript.src = '/assets/js/product-detail/magnific-popup.js';

            var s = document.getElementsByTagName('script')[1];
            s.parentNode.insertBefore(theScript, s);
        }
    })();

</script>


<style>
    .has-error {
        border: 1px solid red !important;
    }
</style>
<section>


    <?php
    if (!empty($productDetail)) {
    // print_rr($userData);
    ?>
    <div class="container product-detail-mbt mt-55">
        <div class="row no-margin">
            <div class="white-background cart-sec-opt shahdow-all w100p">

                <div class="mt-0 row no-margin">
                    <div class="col-md-4">
                        <div class="product-detail-img-cnt">
                            <!-- Place somewhere in the <body> of your page -->
                            <div class="flexslider">
                                <?php
                                $fill = "";
                                if (Session::has('userData')) {
                                $favProductId = getBuyerFavProducts();
                                if (!empty($favProductId) && in_array($productDetail['product_id'], $favProductId)) {
                                    $fill = "animate fillheart";
                                }?>

                                <div class="add-to-fav">
                                    <div class="HeartAnimation  adfvt <?php echo $fill; ?>"
                                         data-product-id="<?php echo $productDetail['product_id']; ?>"></div>
                                </div>
                                <?php }else{
                                ?>
                                <a class="fav-login login-popup"
                                   data-location="{{URL::to('product/'.$productDetail['slug'])}}"
                                   href="javascript:void(0)" data-toggle="modal" data-target="#login-registerpopup">
                                    <div class="add-to-fav">
                                        <div class="HeartAnimation"></div>
                                    </div>
                                </a>
                                <?php } ?>


                                <div class="column hide-mobile">
                                    <div class="xzoom-container">

                                        <?php
                                        $images = unserialize($productDetail['gallery']);
                                        if (!empty($images)) {
                                            foreach ($images as $img) {
                                                $imageUrl = getAttachmentUrlById((int)$img, 'product');
                                                ?>
                                                    <?php
                                            }
                                        }
                                        if (!empty($productDetail['feature_image'])) {
                                        $imageUrl = getAttachmentUrlById((int)$productDetail['feature_image'], 'product');
                                        ?>
                                        <div class="max-height-div">
                                            <img id="xzoom-magnific" src="<?php echo $imageUrl ?>"
                                                 class="xzoom img-responsive" xoriginal="<?php echo $imageUrl ?>"
                                                 title="{{$productDetail['slug']}}" alt="{{$productDetail['slug']}}">
                                        </div>
                                        <?php }
                                        ?>
                                        <div class="xzoom-thumbs">
                                            <?php
                                            $images = unserialize($productDetail['gallery']);
                                            if (!empty($images)) {
                                            foreach ($images as $img) {
                                            $imageUrl = getAttachmentUrlById((int)$img, 'medium');
                                            $productimageUrl = getAttachmentUrlById((int)$img, 'product');
                                            $thumbimageUrl = getAttachmentUrlById((int)$img, 'small');
                                            ?>
                                            <a href="<?php echo $productimageUrl; ?>">
                                                <img src="<?php echo $thumbimageUrl; ?>" class="xzoom-gallery"
                                                     width="70" xpreview="<?php echo $imageUrl ?>" title="{{$productDetail['slug']}}" alt="{{$productDetail['slug']}}">
                                            </a>
                                            <?php
                                            }
                                            }
                                            if (!empty($productDetail['feature_image'])) {
                                            $imageUrl = getAttachmentUrlById((int)$productDetail['feature_image'], 'media');
                                            ?>
                                            <a href="<?php echo $imageUrl ?>">
                                                <img src="<?php echo $imageUrl ?>" class="xzoom-gallery" width="70"
                                                     xpreview="<?php echo $imageUrl ?>" title="{{$productDetail['slug']}}" alt="{{$productDetail['slug']}}">
                                            <?php }
                                            ?>

                                        </div>
                                    </div>
                                </div>


                                <div class="flexslider-mobile  show-on-mobile">


                                    <?php
                                    $images = unserialize($productDetail['gallery']);
                                    if (!empty($images)) {
                                    ?>
                                    <ul class="slides">
                                        <?php
                                        foreach ($images as $img) {

                                        $productimageUrl = getAttachmentUrlById((int)$img, 'product');
                                        $thumbimageUrl = getAttachmentUrlById((int)$img, 'medium');
                                        ?>
                                        <li>
                                            <a href="{{$productimageUrl}}" data-fancybox="images">
                                                <img src="{{$thumbimageUrl}}"/>
                                            </a>
                                        </li>
                                        <?php
                                        }
                                        }
                                        ?>
                                        <?php
                                        if (!empty($productDetail['feature_image'])) {
                                        $imageUrl = getAttachmentUrlById((int)$productDetail['feature_image'], 'product');
                                        $medimageUrl = getAttachmentUrlById((int)$productDetail['feature_image'], 'medium');
                                        ?>
                                        <li>
                                            <a href="{{$imageUrl}}" data-fancybox="images">
                                                <img src="{{$medimageUrl}}"/></a></li>
                                        <?php } ?>
                                    </ul>
                                </div>


                            </div>


                        </div><!-- product-detail-img-cnt -->
                        <div class="vendor-box-sec hide-mobile">

                            <div class="crt-hed pos-rel bb-1px info-box-mbt common-option-box">
                                <p class="product-by display-table-block w200px mb-20">
                                <?php
                                $imageUrl = "";
                                $storeData = getStoreDataByUserId($productDetail['user_id']);
                                if (isset($storeData->store_logo) && $storeData->store_logo != "") {
                                $imageUrl = getAttachmentUrlById((int)$storeData->store_logo, 'medium');
                                ?>


                                <div class="logo-store text-center">


                                                        <span class="pos-rel"> 
                                                            <span class="sold-by-tag"> Sold By:</span>
                                                            <?php if (isset($imageUrl) && $imageUrl != "") { ?>
                                                            <img src=" <?php echo $imageUrl; ?>" class="img-fluid"
                                                                 style="max-height:35px;">
                                                            <?php } ?>
                                                        </span>
                                    <span class="cmpony-name">
                                                            <?php
                                        if (isset($storeData->store_name)) {
                                            echo $storeData->store_name;
                                        }
                                        ?>

                                                        </span>
                                </div>
                                <div class="member-since text-center">
                                                        <span>
                                                            <?php
                                                            $timestamp = strtotime($storeData->created_at);
                                                            $date = date('d-M-Y', $timestamp);
                                                            ?>
                                                            Member Since: <?php echo $date; ?>
                                                            <p>
                                                            <?php
                                                            $stateId = "";
                                                            $cityName = "";
                                                            $storeData = getStoreDataByUserId($productDetail['user_id']);
                                                            //  print_rr($storeData);

                                                            $stateId = $storeData->store_state;
                                                            if (isset($storeData->store_state)) {
                                                                $stateId = $storeData->store_state;
                                                                $stateName = getStateById($stateId);
                                                            }
                                                            if ($storeData->store_country == "1") {
                                                                $cityName = "USA";
                                                            }
                                                            if ($storeData->store_country == "2") {
                                                                $cityName = "Canada";
                                                            }

                                                            ?>
                                                            <p><?php echo $cityName . " , " . $stateName['state_name']; ?></p>


                                                            </p>
                                                        </span>
                                </div>
                                <?php } ?>
                                </p>

                            </div>

                            <!-- crt-hed -->
                        </div>
                    </div>

                    <div class="col-md-8">
                        <div class="pro-description-detail-mbt">
                            <h2 class="pro-deading"><?php echo $productDetail['title']; ?></h2>

                            <p class="clearfix strp-tag match-clr">
                                <?php if (!empty($postCategories)) { ?>

                                <span class="gray-text pull-left fnt14">
                                                        <?php
                                    $numItems = count($postCategories);
                                    $i = 0;
                                    foreach ($postCategories as $pc) {
                                        if (++$i === $numItems) {
                                            echo $pc[0]['categoryTitle'] . " ";
                                        } else {
                                            echo $pc[0]['categoryTitle'] . " , ";
                                        }
                                    }
                                    ?>
                                </span>
                                <?php
                                }
                                ?>


                                <span class="pull-right">
                                                    <span class="">Item #: <?php echo $productDetail['product_id']; ?> </span>
                                    <?php
                                    $filterData = getFilterData(1);
                                    if ($filterData['filter_status'] == 1) {
                                    ?>
                                    <span class="conditional-text">Condition: <?php echo isset($productDetail['product_condition']) ? ucfirst($productDetail['product_condition']) : ""; ?> </span> <?php } ?>
                                                </span>


                            </p>

                            <h3 class="text-blue mb-5 fnt24 mt-35 M-display-inline">
                                <b>$<?php echo $productDetail['price'] ?></b></h3>

                            <?php if (!empty($productShippingPrice)) { ?>
                            <p class="tag-shippingcost"><b>Shipping
                                    Cost:$<?php echo $productShippingPrice[0]['due']; ?></b></p>

                            <?php } ?>
                            <?php
                            if ($productDetail['weight'] > 30 && $productDetail['productSize_length'] < 75 && $productDetail['productSize_width'] < 75 && $productDetail['productSize_height'] < 75) {
                            if (!empty($availablbeTransporter)) {
                            ?>
                            <div class="product-alert-box mb-40">
                                <div class="alert alert-danger text-left mb-0" role="alert">
                                    <h3 class="mb-10"><span> <i class="fa fa-exclamation-circle" aria-hidden="true"></i></span>
                                        Alert Before you buy this product</h3>
                                    <p class="text-white mb-0">This product is weighing more than 30kg. It cannot by
                                        shipped by Canada Post,
                                        Here are following transporters can help you deliver this product
                                    </p>
                                </div>
                                <div class="alert-body-section pl-20 pr-20 pt-20 pb-20">
                                    <?php foreach ($availablbeTransporter as $at) { ?>
                                    <div class="alert-listing row">
                                        <div class="col-sm-6 no-padd-left">
                                        <p class="services-provider-names"><?php echo $at->name; ?></p>
                                        </div>
                                        <div class="col-sm-4 text-center">
                                            <p class="services-provider-number"><?php echo $at->contact_detail; ?></p>
                                        </div>
                                        <div class="col-sm-2 no-padd-right">
                                        <p class="services-provider-detial"><a href="<?php echo $at->website; ?>">View                                           Details</a></p>
                                        </div>
                                    </div>
                                    <?php
                                    break;
                                    }
                                    ?>
                                </div>
                            </div>
                            <?php
                            } else {
                            ?>
                            <div class="alert alert-danger text-left mb-0" role="alert">
                                <h3 class="mb-10"><span> <i class="fa fa-exclamation-circle"
                                                            aria-hidden="true"></i></span> Alert Before you buy this
                                    product</h3>
                                <p class="text-white mb-0">This product is weighing more than 30kg.
                                    No transporter is avialable for shipping
                                </p>
                            </div>
                            <?php
                            }
                            }
                            ?>
                            <div class="shipping-option-quanity-box">
                                <?php
                                if (count($variations) > 0) {

                                $attribute1 = $attribute2 = $attribute3 = $option1 = $option2 = $option3 = array();
                                $attributevalue1 = $attributevalue2 = $attributevalue3 = $unit1 = $unit3 = $unit2 = '';
                                $checkBoxprice1 = $checkBoxprice2 = $checkBoxprice3 = $checkBoxquantity1 = $checkBoxquantity2 = $checkBoxquantity3 = $checkBoxsku1 = $checkBoxsku2 = $checkBoxsku3='';

                                foreach ($variations as $key => $attribute) {
                                    if ($attribute->attribute1) {
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
                                        $attributevalue3 = $attribute->attribute3;
                                        $unit3 = $attribute->unit3;
                                        $attribute->option3;
                                        if (!(in_array($attribute->option3, $option3))) {
                                            $option3[] = $attribute->option3;
                                        }
                                    }
                                }
                                ?>
                                <div class="row">
                                    <?php if (!empty($attributevalue1)) { ?>
                                    <div class="col-4 col-md-4">
                                        <div class="form-group clearfix mb-0 ">
                                            <label for="text"
                                                   class="mt-10 fnt14  black"><?php echo ucfirst($attributevalue1); ?>
                                                :</label>
                                            <select class="form-control variation-change" id="variation1" name="">
                                                <option value="">please select</option>
                                                <?php foreach ($option1 as $key => $v1) { ?>
                                                <option value="<?php echo $v1; ?>"><?php echo ucfirst($v1); ?></option>
                                                <?php } ?>
                                            </select>
                                        </div>
                                    </div>
                                    <?php } ?>
                                    <?php if (!empty($attributevalue2)) { ?>
                                    <div class="col-4 col-md-4">
                                        <div class="form-group clearfix mb-0 ">
                                            <label for="text"
                                                   class="mt-10 fnt14  black"><?php echo ucfirst($attributevalue2); ?>
                                                :</label>
                                            <select class="form-control variation-change" id="variation2" name="">
                                                <option value="">please select</option>
                                                <?php foreach ($option2 as $v2) { ?>
                                                <option value="<?php echo $v2 ?>"><?php echo ucfirst($v2); ?></option>
                                                <?php } ?>
                                            </select>
                                        </div>
                                    </div>
                                    <?php } ?>
                                    <?php if (!empty($attributevalue3)) {
                                    ?>
                                    <div class="col-4 col-md-4">
                                        <div class="form-group clearfix mb-0 ">
                                            <label for="text"
                                                   class="mt-10 fnt14  black"><?php echo ucfirst($attributevalue3); ?>
                                                :</label>
                                            <select class="form-control variation-change" id="variation3" name="">
                                                <option value="">please select</option>
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
                            <?php } ?>
                            <!--                                <div class="row">
                                                                                    <div class="col-4 col-md-4">                       
                                                                                        <div class="form-group clearfix mb-0 ">                            
                                                                                            <label for="text" class="mt-10 fnt14  black">Quantity:</label>
                                                                                            <select class="form-control" id="" name="">
                                                                                                <option value="1">1</option>
                                                                                                <option value="2">10</option>
                                                                                            </select>
                                                                                        </div>
                                                                                    </div>     
                                                                                </div>-->
                                <div class="row pro-buttons-group mt-20">
                                    <div class="col-md-4 pr-7">
                                        <button type="button"
                                                class="primary-btn btn btn-primary  w100p text-white cartadds background-clr"
                                                data-product-id="<?php echo $productDetail['product_id']; ?>"
                                                data-store-id="<?php echo $productDetail['store_id']; ?>">Add To Cart
                                        </button>
                                    </div>
                                    {{--<div class="col-md-4 pl-7">--}}
                                    {{--<button type="button"--}}
                                    {{--class="primary-btn btn btn-primary  w100p white-background">Add to--}}
                                    {{--Wishlist--}}
                                    {{--</button>--}}
                                    {{--</div>--}}

                                </div>


                            </div><!-- shipping-option-quanity-box -->

                            <div class="ability-pro-detail row mt-40">
                                <div class="col-md-10">
                                    <h4>Details</h4>
                                    <p>
                                        <?php echo $productDetail['description']; ?>
                                    </p>
                                </div>
                                <div class="col-md-5">
                                    <div class="table-specs mt-20">
                                        <h2 class="mb-20">Specifications</h2>
                                        <table class="table">

                                            <tbody>
                                            <tr>
                                                <td>Width:</td>
                                                <td><?php echo $productDetail['productSize_width']; ?>"</td>

                                            </tr>
                                            <tr>
                                                <td>Length:</td>
                                                <td><?php echo $productDetail['productSize_length']; ?>"</td>
                                            </tr>
                                            <tr>
                                                <td>Height:</td>
                                                <td><?php echo $productDetail['productSize_height']; ?>"</td>
                                            </tr>
                                            <tr>
                                                <td>Weight:</td>
                                                <td><?php echo $productDetail['weight']; ?>Kg</td>
                                            </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <div class="col-md-5">
                                    <div class="table-specs mt-20">
                                        <h2 class="mb-20">Taxation</h2>
                                        <table class="table">

                                            <tbody>
                                            <?php if ($productDetail['taxation_gst'] == 1) { ?>
                                            <tr>
                                                <td>GST</td>
                                            </tr>
                                            <?php
                                            }
                                            if ($productDetail['taxation_pst'] == 1) {
                                            ?>
                                            <tr>
                                                <td>PST</td>
                                            </tr>
                                            <?php } ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <!-- <div class="features-div col-md-11">
                                     <h4 class="mb-20 mt-15">Features</h4>
                                     <ul>
                                         <li>Supports the lumbar curve of your spine, thus decreasing pressure on the
                                             spinal discs
                                         </li>
                                         <li>Pop-off rear wheels</li>
                                         <li>Desk-top Length Armrests</li>
                                         <li>Adjustable Backrest</li>
                                         <li>Quick-Release Wheels</li>
                                         <li>Swing-away Detachable Footrests</li>
                                         <li>Anti-tippers are included</li>
                                     </ul>
                                 </div> -->
                            </div><!-- ability-pro-detail -->


                            <div class="vendor-box-sec show-on-mobile common-option-box">
                                <p class="text-center social-icon-style mt-30 mb-30">
                                    <a class="sclicn f-icon" href=""><i class="fa fa-facebook"
                                                                        aria-hidden="true"></i></a>
                                    <a class="sclicn t-icon" href=""><i class="fa fa-twitter"
                                                                        aria-hidden="true"></i></a>
                                    <a class="sclicn g-icon" href=""><i class="fa fa-google-plus"
                                                                        aria-hidden="true"></i></a>
                                    <a class="sclicn hide-div" href=""><i class="fa fa-pinterest"
                                                                          aria-hidden="true"></i></a>
                                </p>

                                <div class="crt-hed pos-rel bb-1px info-box-mbt ">
                                    <p class="product-by display-table-block w200px mb-20">
                                    <?php
                                    $imageUrl = "";
                                    $storeData = getStoreDataByUserId($productDetail['user_id']);
                                    if (isset($storeData->store_logo) && $storeData->store_logo != "") {
                                    $imageUrl = getAttachmentUrlById((int)$storeData->store_logo, 'medium');
                                    ?>


                                    <div class="logo-store text-center">


                                                            <span class="pos-rel"> 
                                                                <span class="sold-by-tag"> Sold By:</span>
                                                                <?php if (isset($imageUrl) && $imageUrl != "") { ?>
                                                                <img src=" <?php echo $imageUrl; ?>" class="img-fluid"
                                                                     style="max-height:35px;">
                                                                <?php } ?>
                                                            </span>
                                        <span class="cmpony-name">
                                                                <?php
                                            if (isset($storeData->store_name)) {
                                                echo $storeData->store_name;
                                            }
                                            ?>

                                                            </span>
                                    </div>
                                    <div class="member-since text-center">
                                                            <span>
                                                                <?php
                                                                $timestamp = strtotime($storeData->created_at);
                                                                $date = date('d-M-Y', $timestamp);
                                                                ?>
                                                                Member Since: <?php echo $date; ?>
                                                            </span>
                                        <?php } ?>
                                        <p>
                                        <?php
                                        $stateName = "";
                                        $cityName = "";
                                        $storeData = getStoreDataByUserId($productDetail['user_id']);

                                        $stateId = $storeData->store_state;
                                        if (isset($storeData->store_state)) {
                                            $stateId = $storeData->store_state;
                                            $stateName = getStateById($stateId);
                                            $stateName['state_name'];
                                        }
                                        if (isset($userData[0]->store_city)) {
                                            $cityName = $storeData->store_city;
                                        }

                                        ?>
                                        <p><?php  echo $stateName['state_name']; ?></p>
                                        <p><?php  echo $cityName; ?></p>

                                        </p>
                                    </div>


                                </div>

                                <!-- crt-hed -->
                            </div>

                        </div><!-- pro-description-detail-mbt -->
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php } else { ?>
    <div class="container text-center">
        <div class="mt-50">
            <h4 class="bold">No record Found</h4>
        </div>
    </div>
    <?php } ?>
</section>
<!-- <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/flexslider/2.7.0/jquery.flexslider.js"></script> -->
<script>

    // Can also be used with $(document).ready()

    $(document).on("change", ".variation-change", function () {
        var variation1 = $('#variation1').val();
        var variation2 = $('#variation2').val();
//console.log(variation2);
        var variation3 = $('#variation3').val();
        var productId = "<?php echo $productDetail['product_id']; ?>"
        var data = {};

        if (variation1 != "" && variation2 != "" && variation3 == undefined) {
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
                    $(".fnt24").html("$" + obj[0].price);
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
                    $(".fnt24").html("$" + obj[0].price);
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
                    $(".fnt24").html("$" + obj[0].price);
                }
            });
// sendreq(data, 'product', 'get-variations-info', 'callbackVariations');
        }

    });
    $(document).on("click", '.cartadds', function () {
        has_error = false;

        var optionArray = [];
        jQuery('.variation-change').each(function () {
            val = jQuery(this).val();
            if (val == '') {
                jQuery(this).addClass('has-error');
                has_error = true;
            } else {
                optionArray.push($(this).val());
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
        data['product_quantity'] = 1;
        if (!has_error) {
// jQuery('.uploadImages').show();
            sendreq(data, 'cart', 'add', 'callbackBasket');
        }
    });
    function callbackBasket(data) {
        $(".cartmenu").html(data);
        $("html, body").animate({scrollTop: 0}, "slow");
//  jQuery('.uploadImages').hide();
    }


    $('.flexslider-mobile').flexslider({
        animation: "slide"
    });

    $().fancybox({
        selector: '.slides li:not(.clone) a',
        hash: false,
        thumbs: {
            autoStart: true
        }
    });

    $(document).on('click', '.adfvt', function () {
        current = $(this);
        var data = {};
        data['product_id'] = $(this).attr('data-product-id');
        sendreq(data, 'favourite', 'add', 'callbackfavourites');
        $(this).toggleClass("animate");

    });
    function callbackfavourites(data) {

    }

</script>
<!-- scripts for zoomer image  -->

<script>
    
    (function () {
        if (window.innerWidth > 767) {
            var theScript = document.createElement('script');
            theScript.type = 'text/javascript';
            theScript.src = '/assets/js/product-detail/setup.js';

            var s = document.getElementsByTagName('script')[1];
            s.parentNode.insertBefore(theScript, s);
        }
    })();

</script>

<!-- <script src="{{ URL::asset('assets/js/product-detail/setup.js' ,env('HTTPS_STATUS')) }}"></script> -->

@endsection