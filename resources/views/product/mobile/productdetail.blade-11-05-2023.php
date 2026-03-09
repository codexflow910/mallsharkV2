<?php 
$title = ''.$productDetail['title'].' at Mallshark';
$store_info = getStoreInfoById($productDetail['store_id']); 
$storename =  $storeInfo[0]->store_name;
$description = 'Use Crypto to experience quality and reliablity with the '.$productDetail['title'].' from '.$storename.'. Our online shop accepts cryptocurrency (i.e. Bitcoin, Ethereum).';
$keyword = ''.$productDetail['title'].', buy '.$productDetail['title'].'';
?>
@extends('layouts.mobile-main')
<!-- Main Banner Starts -->
@section('content')
@section('title',$title)
@section('description', $description)
@section('keyword', $keyword)
@section('description',$productDetail['short_description'])



<link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/flexslider/2.7.0/flexslider.css"
      media="all"/>
<!-- for magnifier popup for desktop version-->


<script type="text/javascript"
src="https://cdnjs.cloudflare.com/ajax/libs/flexslider/2.7.1/jquery.flexslider-min.js"></script>

<link rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.8.1/slick.css"></style>

<!--<link rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/magnific-popup.js/1.1.0/magnific-popup.min.css"></style>-->

<link rel="stylesheet"
href="https://mreq.github.io/slick-lightbox/dist/slick-lightbox.css"></style>





<?php
$userId = !empty(Session::get('userData')['user_id'])?Session::get('userData')['user_id']:'';
if (empty($productDetail)) {
    echo '<h1>404</h1>';
    exit;
}
$minimumPrice = round($productDetail['price'] / 2);
?>
<!-- Content Starts -->
<main class="product-detail">

    <div class="container"> 
        <div class="row">
            <?php
            $iconUrl = '';
            $store_info = getStoreInfoById($productDetail['store_id']);
            $store_icon = isset($store_info[0]->store_icon) ? $store_info[0]->store_icon : '';
            if (!empty($store_icon)) {
                $image_id = getImageIdByIconId((int) $store_icon);
				if(!empty($image_id))
					$iconUrl = getAttachmentUrlById((int) $image_id, 'icons');
            }
            ?>
            <?php
            $imageUrl = '';
            if (isset($storeInfo[0]->store_logo) && $storeInfo[0]->store_logo != "") {
                $imageUrl = getAttachmentUrlById((int) $storeInfo[0]->store_logo, 'medium');
            }
            ?>



            <div class="col-12 strlgo ">
                <div class="row">
                    <div class="col-md-12 col-12 text-center">
                        <a href="<?php echo URL::to('/brand/' . $storeInfo[0]->store_slug); ?>">
                            <img src="<?php echo $imageUrl; ?>" class="img-fluid" alt="<?php echo isset($storeInfo[0]->store_name) ? $storeInfo[0]->store_name : ''; ?>" title="<?php echo isset($storeInfo[0]->store_name) ? $storeInfo[0]->store_name : ''; ?>" >
                        </a>
                    </div>
                    <!-- <div class="col-md-6 col-6 text-right"><span><img src="<?php //echo isset($iconUrl) ? $iconUrl : ''; ?>" width="50" alt="{{$store_info[0]->store_name}}"></span></div>       -->
                </div>
            </div>  
            <div class="col-12" style="margin-bottom:10px; border-bottom:1px solid #d5d5d5; padding-bottom:10px">
                <div class="row"> 
                    <div class="col-4" style="font-size:15px">
                        <i class="fa fa-star-o" aria-hidden="true"></i>
                        <i class="fa fa-star-o" aria-hidden="true"></i>
                        <i class="fa fa-star-o" aria-hidden="true"></i>
                        <i class="fa fa-star-o" aria-hidden="true"></i>
                        <i class="fa fa-star-o" aria-hidden="true"></i>
                    </div> 
                    <div class="small col-8 text-right">
                        <span class=""><?php
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
                            ?> </span>
                    </div>
                </div>    




            </div>
        </div>
    </div>
    <section>  
        @if(Session::has('pledge_added'))
        <p class="alert alert-success">{{ Session::get('pledge_added') }}</p>
        @endif        
        <div class="product-slider-section">

            <div class="container">
                <div class="search_message alert alert-danger alert-success-mbt col-sm-offset-2 col-sm-8 pun" style="display: none;"></div>
                <div class="row">
                    <div class="col-md-12 ">
                        <h1 class="title-prp"> <?php echo isset($productDetail['title']) ? $productDetail['title'] : ''; ?> </h1>
                    </div>
                    <div class="large-loader" style="display: none;">
                        <span>
                            <img src="<?php echo URL::asset('assets/images/loader-large.svg'); ?>" class="img-fluid" alt="loader icon" title="loader icon">
                        </span>
                    </div>
                    <?php
                     $title = isset($productDetail['title']) ? $productDetail['title'] : ''; 
                     $short_description = isset($productDetail['short_description']) ? $productDetail['short_description'] : ''; 
                     $featureImage = getAttachmentUrlById((int) $productDetail['feature_image'], 'product');
                    ?>
                    <!-- Filter Box Starts -->
                    <div class=" col-lg-8 col-md-8">
                        <div class="pro-left-lrge">
                            <!-- Place somewhere in the <body> of your page -->
                            <div class="product-sec">
                                <div id="carousel-thumb" class="carousel slide carousel-fade carousel-thumbnails" data-ride="carousel">
                                    <!--Slides-->
                                    <div class="carousel-inner" role="listbox">
                                       
                                            <div class="carousel-item active">
                                                <?php
                                                if (isset($productDetail['video_url']) && !empty($productDetail['video_url'])) {
                                                    $videoUrlSource = $productDetail['videoUrlSource'];
                                                    $youtubeId = explode("embed/",$videoUrlSource);
                                                    $makeThumb = 'http://img.youtube.com/vi/'.$youtubeId[1].'/hqdefault.jpg';
                                                    ?>
                                                
                                                <img src= "{{$makeThumb}}" alt="youtube emabaded"  class="video-btn-mobile" data-toggle="modal"
                                                data-src="{{$productDetail['videoUrlSource']}}" data-target="#myModal-mobile">
                                                 
                                                <?php } ?>
                                                <?php if (!empty($productDetail['feature_image'])) {
                                                    $productImage = getAttachmentUrlById((int) $productDetail['feature_image'], 'product');
                                             //       echo 'Data: <pre>' .print_r($productImage,true). '</pre>'; die;

                                                    ?>
                                                    <a itemprop="image" href="<?php echo $featureImage; ?>" class="item-slick">
                                                    <img class="d-block " src="{{$productImage}}" alt="{{$productDetail['title']}}" title="{{$productDetail['title']}}">
                                                    </a>
                                                </div>
                                                <?php
                                                    }
                                                $images = unserialize($productDetail['gallery']);
                                                if (!empty($images)) {
                                                foreach ($images as $img) {
                                                    $productImage = getAttachmentUrlById((int) $img, 'product');
                                                    ?>

                                                    <div class="carousel-item">
                                                        <img class="d-block " src="{{$productImage}}" alt="product" title="product">
                                                    </div>
                                                    <?php
                                                }
                                            }
                                            ?>

                                    </div>
                                    <!--/.Slides-->
                                      <!-- Yotube Video Popup Modal Start -->
                                    <div class="modal fade" id="myModal-mobile" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                                    <div class="modal-dialog" role="document">
                                    <div class="modal-content">
                                    <div class="modal-body">
                                            <button type="button" class="close closebutton" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                            
                                            </button>
                                        <div class="embed-responsive embed-responsive-16by9">
                                            <iframe class="embed-responsive-item" src="" id="video"  allowscriptaccess="always" allow="autoplay"></iframe>
                                        </div>
                                        </div>
                                    </div>
                                    </div>
                                    </div>
                  <!-- Yotube Video Popup Modal End -->
                                    <!--Controls-->
                                    <a class="carousel-control-prev" href="#carousel-thumb" role="button" data-slide="prev">
                                        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                        <span class="sr-only">Previous</span>
                                    </a>
                                    <a class="carousel-control-next" href="#carousel-thumb" role="button" data-slide="next">
                                        <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                        <span class="sr-only">Next</span>
                                    </a>
                                    <!--/.Controls-->
                                    <ol class="carousel-indicators">
                                        <?php
                                        if (!empty($productDetail['feature_image'])) {
                                            $thumbImage = getAttachmentUrlById((int) $productDetail['feature_image'], 'medium');
                                            ?>

                                            <li data-target="#carousel-thumb" data-slide-to="0" class="active"> <img class="d-block w-100" src="{{$thumbImage}}" class="img-fluid" alt="{{$productDetail['title']}}" title="{{$productDetail['title']}}"></li>
                                            <?php
                                        }
                                        if (!empty($images)) {
                                            foreach ($images as $img) {
                                                $thumbImage = getAttachmentUrlById((int) $img, 'medium');
                                                ?>

                                                <li data-target="#carousel-thumb" data-slide-to="1"><img class="d-block w-100" src="{{$thumbImage}}" class="img-fluid" alt="{{$productDetail['title']}}" title="{{$productDetail['title']}}"></li>
                                                <?php
                                            }
                                        }
                                        ?>
                                    </ol>
                                </div>
                            </div>


                        </div><!-- product-detail-img-cnt -->


                        <!-- pro-left=lrge -->

                    </div>                 
                    <div class="col-lg-4 col-md-4 ">
                        <div class="row-sidebar">
                            <div class="sec-right ">
                                <button onclick="goBack()" class="primary-btn btn btn-primary pull-right text-white gobackbutton" style="display:none !important;">Go Back</button>
                               


                                <!-- </h4>  -->
                                <hr class="blue-border-strip">
                                <div class="price-sec">
                                    <div class="instock-disount  clearfix">
                                        <div class="pull-left">
<!--                                                        <h3 class="blue-text cut-off-price"> <strike>  Z<?php // echo isset($productDetail['price']) ? $productDetail['price'] : '';                                                ?> </strike> </h3>-->

<!--                                                        <h3 class="blue-text cut-off-price"> <strike>  Z<?php // echo isset($productDetail['price']) ? $productDetail['price'] : '';                                               ?> </strike> </h3>-->
                                        </div>
                                    </div>


                                    <div class="justify-content-between clearfix ">

                                        <div class="pull-left-large pull-left fnt24">
                                            <h2 class="blue-text" id="prod-price">  USDC
                                                <b id="product-price" data-price="<?php echo $productDetail['price']; ?>"><?php echo isset($productDetail['price']) ? $productDetail['price'] : ''; ?></b></h3>
                                            <h2 class="not-available" style="display:none;"></h3>
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
                                        <?php if ($currentStock > 0) { ?>
                                        <div class="pull-right-large pull-right">                
                                            <div class="form-group clearfix mb-0">
                                                <label for="text" class="mt-10">Quantity</label> 
                                                <select class="form-control" data-prodcut-id="<?php echo isset($productDetail['product_id']) ? $productDetail['product_id'] : ''; ?>" id="product_quantity" name="product_quantity">
                                                <?php for ($i = 1; $i <= $currentStock; $i++) { ?>
                                                    <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                                                <?php } ?> 
                                                </select>
                                            </div>
                                        </div>
                                        <?php } else { ?>
                                                    <span class="outofstock">Out of stock</span>
                                        <?php } ?>
                                    </div>
                                    <?php 
                                    } else if ($productDetail['product_type'] == 'variant') {
                                        ?>
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
                                                    <div class="<?php echo $selectedClass; ?>"
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


                                                    <?php } ?>
                                                    <?php if (!empty($attributevalue2)) { ?>
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
                                                    <?php } ?>
                                                    <?php if (!empty($attributevalue3)) {
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
                                    <div class="local-taxes-include ">
                                        <span>Local Taxes include (where applicable)
                                            + $ <span class="shipping_price"> <?php echo isset($productShippingPrice) ? $productShippingPrice : 0; ?> </span> shipping
                                        </span>                                        
                                    </div>
                                    
                                    
                                    <?php
                                    
                                    if (!Session::has('userData')) { ?>
                                        <div class="cart-sec-btn">
                                            <a class="primary-btn btn btn-primary cartlogin w100p text-white  background-clr"
                                               data-location="{{URL::to('product/'.$productDetail['slug'])}}"
                                               href="javascript:void(0)" data-toggle="modal" data-target="#login-registerpopup">Add to Cart
                                            </a>
                                        </div>
                                    <?php }else if ($productDetail['product_type'] == 'simple' && $productDetail['current_stock'] == 0) {?>
                                        <button type="button" class="primary-btn btn btn-primary w100p text-white notinstock  background-clr">Out of stock</button>
                                 <!-- <?php // }elseif($productDetail['product_type'] == 'variant') { ?> -->
                                    <!-- <button type="button" class="primary-btn btn btn-primary w100p text-white notinstock  background-clr">Out of stock</button> -->

                                 <?php } else if (Session::has('userData') && $userId == $productDetail['user_id']) {
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

                                <!-- <div class = "active-rewar-compaign mt-10 mb-10">
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
                                            </span> -->


                                        <!-- </div> -->
                                        <!--dasys-minuts-counter -->
                                    <!-- </div> -->
                                </div><!--active-rewar-compaign" -->

                            </div><!--sec-right -->
                        </div>
                    </div>
                </div>
                <!--row -->
            </div>
        </div> <!--product-detail -->


        <div class = "product-tabs">
            <div class = "container">
                <div class = "row">

                    <ul class = "nav nav-tabs nav-fill" role = "tablist">
                        <li class = "nav-item">
                            <a class = "nav-link active" href = "#produt-detail" role = "tab" data-toggle = "tab">Product Detail</a>
                        </li>
                        <li class = "nav-item">
                            <a class = "nav-link faqtab" href = "#faq" role = "tab" data-toggle = "tab">FAQ</a>
                        </li>
                        <!-- <li class = "nav-item">
                            <a class = "nav-link" href = "#supporters" role = "tab" data-toggle = "tab">Supporters</a>
                        </li> -->
                        <li class = "nav-item">
                            <a class = "nav-link" href = "#rating" role = "tab" data-toggle = "tab">Rating & Reviews</a>
                        </li>
                    </ul>

                </div>
            </div>
        </div><!--product-tabs ends -->

        <div class = "product-tabs-body">
            <div class = "container">
                <div class = "row">
                    <!--Tab panes -->
                    <div class = "tab-content col-sm-12">
                        <div role = "tabpanel" class = "tab-pane fade in active  show" id = "produt-detail">
                            <div class = "row">
                                <div class = "col-sm-6">
                                    <div class = "productDescriptionContainer" >
                                        <p><?php echo isset($productDetail['description']) ? $productDetail['description'] : '';
                                    ?></p>                                                                            
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <aside>
                                        <div class="sidebar-option-tab">




                                            <!-- <div class="brdr-panel-mbt clearifx goal-bonus-box blue-box"> -->
                                                <!-- <div class="golbox-heading ">
                                                    <h3>Goal Bonus for this Brand</h3>
                                                </div> -->
                                                <!-- golbox-heading -->
                                                <!-- <div class="progress-strip">
                                                    <div class="progress">
                                                        <div class="progress-bar" role="progressbar" style="width: 80%" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100"></div>
                                                    </div>                                                    
                                                    <div class="goal-reached text-center">
                                                        <p>70% Goal Achieved</p>
                                                        <h4>21 Days To Go</h4>
                                                    </div>                                                        
                                                </div> -->
                                                <!-- progress-strip -->
                                                <!-- <div class="pledges-needed">
                                                    <div class="circle-pledges text-center">
                                                        <h3>54</h3>
                                                        <span>Pledges</span>
                                                        <span>needed</span>                                                        
                                                    </div>
                                                </div> -->
                                                <!-- pledges-needed -->
                                                <?php if (Session::has('userData') && $userId == $productDetail['user_id']) { ?>
                                                    <!-- <div class="make-pledge">
                                                        <p>Make a pledge of support</p>
                                                        <form action="{{URL::TO('/pledge-amount')}}" method="POST">
                                                            <span class="token-counter">
                                                              //  <input type="hidden" name="product_id" value="{{$productDetail['product_id']}}">
                                                               // <input type="hidden" name="product_slug" value="{{$productDetail['slug']}}">
                                                              //  <input type="number" value="{{$minimumPrice}}" name="pledge_amount" class="tockeninput" placeholder="Min {{$minimumPrice}} $">
                                                            </span>
                                                           // {{ csrf_field() }}
                                                            <button type="submit" class="sndtockens">Send Token</button>
                                                        </form>
                                                    </div> -->
                                                    <!-- make-pledge -->
                                                <?php } ?>
                                                <!-- <hr class="hr-seprator">
                                                <div class="bonus-takencnt">
                                                    <div class="persentage-counter">
                                                        <h1>40 <span>%</span></h1>
                                                    </div>
                                                    <div class="persentage-body">
                                                        <p>bonus tokens for purchasers of this Brand products if goal is met</p>
                                                    </div>
                                                </div> -->
                                                <!-- bonus-takencnt -->
                                            <!-- </div> -->



                                            <div class="brdr-panel-mbt clearifx">
                                                <!-- <div class="supporters-sec"> -->
                                                    <!-- <p>supporters :<b>0</b></p> -->
                                                    <!-- <p><b>3</b> days to go</p>
                                                    <div class="progress">
                                                        <div class="progress-bar" role="progressbar" style="width: 80%" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100"></div>
                                                    </div>

                                                </div> -->
                                                <!-- supporters-sec -->

                                                <div class="rating-stars">
                                                    <div class="text-center inner-mbt-text">
                                                        <div class="rating-strip">
                                                            <i class="fa fa-star-o" aria-hidden="true"></i>
                                                            <i class="fa fa-star-o" aria-hidden="true"></i>
                                                            <i class="fa fa-star-o" aria-hidden="true"></i>
                                                            <i class="fa fa-star-o" aria-hidden="true"></i>
                                                            <i class="fa fa-star-o" aria-hidden="true"></i>
                                                        </div>
                                                        <p><b>0</b> Rating</p>
                                                        <div class="review-strip">
                                                            <span><b>0</b> review</span>
                                                            <span><a href="javascript:;">go to review</a></span>
                                                        </div>

                                                    </div>
                                                    <?php /*?><div class="support-product">
                                                        <button class="btn btn-primary">support this products</button>
                                                    </div><?php */?>

                                                </div><!-- rating-stars -->
                                            </div>

                                            
                                        </div><!-- sidebar-option-tab -->
                                    </aside>

                                </div>
                            </div>

                        </div>
                        <?php
                        if (!getCurrentUserId()) {
                            $str = 'class="btn btn-primary btn-simple get-p-url" data-location="http://shopzit.mindblaze.net/product/test-product-added-1" data-toggle="modal" data-target="#login-registerpopup"';
                        } else {
                            $str = 'class="btn btn-primary aska-question"';
                        }
                        ?>
                        <div role="tabpanel" class="tab-pane fade" id="faq">


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

                                <h2>Frequently Asked Question</h2>
                                <p>Looks like there aren't any frequently asked question yet. Ask the store owner directly.</p>
                                <p><button <?php echo $str; ?>>Ask a question</button></p>
                            </div>
                            <div class="ask-container-box-section row">
                                <div class="response-div" id="response-div"></div>
                                <form>
                                    <div class="card">
                                        <div class="form-group">
                                            <label>
                                                Question
                                            </label>
                                            <input type="text" id="faqQuestion" class="form-control"   maxlength="150">
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



                        <!-- <div role="tabpanel" class="tab-pane fade" id="supporters">


                            <h2> No supporters found </h2>
                        </div> -->
                        <!-- supporters -->
                        <div role="tabpanel" class="tab-pane fade" id="rating">
                            <div class="rating-review-tab">
                                <?php if (!canUserReview($productDetail['product_id'])) { ?>
                                    <div class="error-onlysupporters">
                                        <p><span>!</span>Only buyers can rate and review</p> 
                                    </div><!-- error-onlysupporters -->
                                    <?php
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
                                    <div class="average-rating-box">
                                        <div class="table-cell-row left-row">
                                            <div class="star-rating-head">
                                                <!-- <span class="average-rating">Average Rating:</span> -->
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
                                        <span class="average-rating">Average Rating</span>
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
                                                $image_url = getAttachmentUrlById($user_avatar, 'media');
                                            }

                                            if (is_string($date)) {
                                                $timestamp = (strtotime($date));
                                                $date =date('m/d/Y H:i:s', $timestamp);
                                            }
                                            ?>
                                            <div class = "row-table">
                                                <div class = "col-sm-2-cell">
                                                    <div class = "image-review-cnt">
                                                        <img src = "{{$image_url}}" alt = "user" title= "user" class = "img-fluid" style = "height: 85px; width: 85px;">

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
                                                            <input type = "hidden" id = "rating_count" name = "rating_count" class = "rating-value" value = "2.56">
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
                                    <?php } ?>

                                </section>


                            </div><!--rating-review-tab -->
                        </div>
                    </div>
                </div>
            </div>
        </div> <!--tabs-body -->

        <div class = "shipping-gray-sec">
            <div class = "container">
                <div class = "row row-repeater-list">
                    <div class = "col-sm-3">
                        <div class = "left-shipping-det">
                            <h3>Shipping Policies:</h3>
                        </div>
                    </div>
                    <div class = "col-sm-6">
                        <div class = "row-list-mbt">
                            <span class = "icon-view"></span><span class = "text-pre-vie">Preparation takes 1-2 business days</span>
                        </div><!--row-list-mbt -->
                        <div class = "row-list-mbt">
                            <span class = "icon-view"></span><span class = "text-pre-vie">Arrives from United States</span>
                        </div><!--row-list-mbt -->
                        <div class = "row-list-mbt">
                            <span class = "text-pre-vie">Estimated Shipping Cost</span>
                            <div class = "form-group inline-div">
                                <select class = "form-control" id = "" name = "">
                                    <option>Country</option>
                                    <option>I did</option>
                                    <option>A member of my shop</option>
                                    <option>Another company or person</option>
                                </select>
                            </div> <!--inline-div -->
                            <span class = "shipping-cost">Shipping Cost: <b>$ 14.5</b></span>
                        </div><!--row-list-mbt -->

                    </div>
                </div><!--row-repeater-list -->

                <div class = "row row-repeater-list">
                    <div class = "col-sm-3">
                        <div class = "left-shipping-det">
                            <h4>Payment Options:</h4>
                        </div>
                    </div>
                    <div class = "col-sm-6">
                        <div class = "row-list-mbt payment-icon">
                            <span>
                                <img src = "<?php echo URL::asset('assets/images/visa.jpg'); ?>" alt = "visa" title = "visa"  >
                            </span>
                            <span>
                                <img src = "<?php echo URL::asset('assets/images/paypal.jpg'); ?>" alt = "paypal" title = "paypal" >
                            </span>
                            <span>
                                <img src = "<?php echo URL::asset('assets/images/master.jpg'); ?>" alt = "master" title = "master" >
                            </span>
                            <span>
                                <img src = "<?php echo URL::asset('assets/images/bitcoin.jpg'); ?>" alt = "bitcoin" title = "bitcoin" >
                            </span>
                        </div><!--row-list-mbt -->
                    </div>
                </div><!--row-repeater-list -->
                <div class = "row row-repeater-list brdr-remove">
                    <div class = "col-sm-3">
                        <div class = "left-shipping-det">
                            <h4>Returns & Exchange:</h4>
                        </div>
                    </div>
                    <div class = "col-sm-6">
                        <div class = "row-list-mbt">
                            <p>
                                <span class = "text-pre-vie">I gladly accept returns, exchanges and cancellations.</span>
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
        </div><!--shipping-gray-sec -->
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
</style>
<!--script for slick slider -->
<script src = "https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.8.1/slick.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/jquery.jssocials/1.4.0/jssocials.min.js"></script>
<!--<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/magnific-popup.js/1.1.0/jquery.magnific-popup.min.js"></script>                    -->
<script type="text/javascript" src="https://mreq.github.io/slick-lightbox/dist/slick-lightbox.js"></script>                    

<script>
  $(document).ready(function() {
        var $videoSrc;  
        $(document).on("click", '.video-btn-mobile', function () {
            $videoSrc = $(this).data( "src" );
        });
        // when the modal is opened autoplay it  
        $('#myModal-mobile').on('shown.bs.modal', function (e) {
        // set the video src to autoplay and not to show related video. Youtube related video is like a box of chocolates... you never know what you're gonna get
        $("#video").attr('src',$videoSrc + "?autoplay=1&amp;modestbranding=1&amp;showinfo=0" ); 
        })
        // stop playing the youtube video when I close the modal
        $('#myModal-mobile').on('hide.bs.modal', function (e) {
            // a poor man's stop video
            $("#video").attr('src',$videoSrc); 
        }) 
            
    $(".closebutton").click(function(){
    $("body").addClass("scrolling");
    });
    });

$('.product-slide1').slick({
dots: false,
        infinite: true,
        speed: 300,
        slidesToShow: 1,
        slidesToScroll: 1,
        responsive: [
        {
        breakpoint: 1024,
                settings: {
                slidesToShow: 1,
                        slidesToScroll: 1,
                        infinite: true,
                        dots: false
                }
        },
        {
        breakpoint: 600,
                settings: {
                slidesToShow: 1,
                        slidesToScroll: 1
                }
        },
        {
        breakpoint: 480,
                settings: {
                slidesToShow: 1,
                        slidesToScroll: 1
                }
        }
// You can unslick at a given breakpoint now by adding:
// settings: "unslick"
// instead of a settings object
        ]


        });</script>
<style>
    .productDescriptionContainer{
        overflow: hidden;
    }
    .productDescriptionContainer img{
        max-width: 100%
    }
    .has-error{
        border:1px solid red !important;
    }
    .jssocials-share-link { border-radius: 50%; }
</style>
<script>

    // Can also be used with $(document).ready()

    $(document).on("change", ".variation-change", function () {
    var variation1 = $('#variation1').val();
    var variation2 = $('#variation2').val();
    var variation3 = $('#variation3').val();
   console.log("variation1--- "+variation1);
   console.log("variation2--- "+variation2);
   console.log("variation3--- "+variation3);
//    
//    if (variation1 !== "" && variation2==undefined && variation3==undefined) {
//        alert("1");
//    }
//    if(variation1 !== "" && variation2!=="" && variation3==undefined){
//        alert("2");
//    }
//    if(variation1 !== "" && variation2!=="" && variation3!==""){
//        alert("3");
//    }
//    
//    
//    return false;
    var productId = "<?php echo $productDetail['product_id']; ?>"
            var data = {};
    if (variation1 &&  variation2 && (typeof variation3 === 'undefined')) {
    console.log('first');
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
            $(".cartadd").hide();
            jQuery('.empty-response').html("<div class='responsive-body'>This combination is not available</div>");
            $(".not-available").show();
            $("#prod-price").hide();
            $(".tag-shippingcost").hide();
            jQuery('#variable_product').hide();
            } else {
            $(".cartadd").show();
            $(".local-taxes-include").show();
            jQuery('.empty-response').html("");
            $(".not-available").hide();
            $("#prod-price").show();
            quantity = $("#product_quantity").val();
            total = parseFloat(obj[0].price) * quantity;

            $("#product-price").html("$ "+total);
            $("#product-price").attr('data-price', obj.results[0].price);
                if(obj.productImage != "" && obj.productImage != null){
                    // Feature Image src load variation image 
                    $('.slick-lightbox-slick-img').addClass("variation_image_selector");     
                    $('.feature_image_set').attr('src', obj.productImage);
                
                    // Galery Image src load variation image  
                    $('#set_variation_image').attr('src', obj.productImage);
                }else{
                    $('.feature_image_set').removeAttr('');
                    $('#set_variation_image').removeAttr('');
                }
            $("#product-price").html(total);
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

    if (variation1  && (typeof variation2 === 'undefined' ) && (typeof variation3 === 'undefined')) {
    // if (variation1 != "" && variation2 == undefined && variation3 == undefined) {
        console.log('second');
    data['variation1'] = variation1;
    data['productId'] = productId;
    jQuery.ajax({
    'url': '/product/get-variations-info',
            'Type': 'POST',
            'method': 'POSt',
            'data': data,
            'success': function (res) {
                obj = JSON.parse(res);
                console.log("obj", obj);
                if (obj == "") {
                    console.log("obj", obj);
                    $(".cartadd").hide();
                    jQuery('.empty-response').html("<div class='responsive-body'>This combination is not available</div>");
                    $(".not-available").show();
                    $("#prod-price").hide();
                    $(".tag-shippingcost").hide();
                } else
                {
                    $(".cartadd").show();
                    $(".local-taxes-include").show();
                    jQuery('.empty-response').html("");
                    $(".not-available").hide();
                    $("#prod-price").show();
                    quantity = $("#product_quantity").val();
                    total = parseFloat(obj.results[0].price) * quantity;
                
                    $("#product-price").html(total);
                    $("#product-price").attr('data-price', obj.results[0].price);
                    
                    if (obj.results[0].current_stock != ''){
                        if (obj.results[0].current_stock >= 10){
                        obj.results[0].current_stock = 10;
                        }
                    jQuery('.price-sec .stock-remaining span').html(obj.results[0].current_stock + ' instock');
                    var options = '';
                        for ($i = 1; $i <= obj.results[0].current_stock; $i++){
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
    if (variation1 && variation2 && variation3) {
    // if (variation1 != "" && variation2 != "" && variation3 != "") {
        console.log('third');
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
                $(".cartadd").hide();
                jQuery('.empty-response').html("<div class='responsive-body'>This combination is not available</div>");
                $(".not-available").show();
                $("#prod-price").hide();
                $(".tag-shippingcost").hide();
                } else {
                    $(".cartadd").show();
                    $(".local-taxes-include").show();
                    jQuery('.empty-response').html("");
                    $(".not-available").hide();
                    $("#prod-price").show();
                    quantity = $("#product_quantity").val();
                    total = parseFloat(obj[0].price) * quantity;
                    $("#product-price").html(total);
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
    $('#product-price').html(total);
    $(".large-loader").hide();
  //  sendreq(data, 'shipping', 'calculate', 'callbackship');
    });
    function callbackship(data) {
    $(".shipping_price").html(data);
    $(".large-loader").hide();
    }
    $('.flexslider-mobile').flexslider({
    animation: "slide"
    });
    $(document).on('click', '.notbuy', function () {
    alert("you cannot by your own product");
    });
//    $(function () {
//// Card's slider
//        var $carousel = $('.slider-for');
//
//        $carousel
//                .slick({
//                    slidesToShow: 1,
//                    slidesToScroll: 1,
//                    arrows: false,
//                    fade: true,
//                    adaptiveHeight: false,
//                    asNavFor: '.slider-nav'
//                })
//                .magnificPopup({
//                    type: 'image',
//                    delegate: 'a:not(.slick-cloned)',
//                    closeOnContentClick: false,
//                    tLoading: 'Загрузка...',
//                    mainClass: 'mfp-zoom-in mfp-img-mobile',
//                    image: {
//                        verticalFit: true,
//                        tError: '<a href="%url%">Фото #%curr%</a> не загрузилось.'
//                    },
//                    gallery: {
//                        enabled: true,
//                        navigateByImgClick: true,
//                        tCounter: '<span class="mfp-counter">%curr% из %total%</span>', // markup of counte
//                        preload: [0, 1] // Will preload 0 - before current, and 1 after the current image
//                    },
//                    zoom: {
//                        enabled: true,
//                        duration: 300
//                    },
//                    removalDelay: 300, //delay removal by X to allow out-animation
//                    callbacks: {
//                        open: function () {
//                            //overwrite default prev + next function. Add timeout for css3 crossfade animation
//                            $.magnificPopup.instance.next = function () {
//                                var self = this;
//                                self.wrap.removeClass('mfp-image-loaded');
//                                setTimeout(function () {
//                                    $.magnificPopup.proto.next.call(self);
//                                }, 120);
//                            };
//                            $.magnificPopup.instance.prev = function () {
//                                var self = this;
//                                self.wrap.removeClass('mfp-image-loaded');
//                                setTimeout(function () {
//                                    $.magnificPopup.proto.prev.call(self);
//                                }, 120);
//                            };
//                            var current = $carousel.slick('slickCurrentSlide');
//                            $carousel.magnificPopup('goTo', current);
//                        },
//                        imageLoadComplete: function () {
//                            var self = this;
//                            setTimeout(function () {
//                                self.wrap.addClass('mfp-image-loaded');
//                            }, 16);
//                        },
//                        beforeClose: function () {
//                            $carousel.slick('slickGoTo', parseInt(this.index));
//                        }
//                    }
//                });
//        $('.slider-nav').slick({
//            slidesToShow: 4,
//            slidesToScroll: 1,
//            asNavFor: '.slider-for',
//            dots: false,
//            centerMode: false,
//            focusOnSelect: true,
//            variableWidth: true
//        });
//
//
//    });





    $('.slider-for').slick({
    slidesToShow: 1,
            slidesToScroll: 1,
            arrows: false,
            fade: true,
            asNavFor: '.slider-nav'
    });
    $('.slider-nav').slick({
    slidesToShow: 4,
            slidesToScroll: 1,
            asNavFor: '.slider-for',
            dots: false,
            centerMode: true,
            focusOnSelect: true
    });
    $('.slider-for').slickLightbox({
    itemSelector: 'a',
            navigateByKeyboard: true
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
    $("#rate-review-option").click(function () {
    $('html, body').animate({
    scrollTop: $("#rating-text").offset().top - 80
    }, 800);
    });
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
    /*
     jQuery('input[type=number]').on('mouseup keyup', function () {
     jQuery(this).val(Math.min({{$minimumPrice}}, Math.max(5000000000000, jQuery(this).val())));
     });
     */
</script>
@endsection