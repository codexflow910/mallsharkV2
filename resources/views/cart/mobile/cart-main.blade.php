@extends('layouts.mobile-main')
<!-- Main Banner Starts -->
@section('content')
@section('title', 'Cart')
@section('description', 'Cart')
<!-- Content Starts -->
<script src="{{ URL::asset('assets/js/cart.js',env('HTTPS_STATUS')) }}"></script>
<!--<link rel="stylesheet" href="{{ URL::asset('assets/css/ability-theme-style.css' ,env('HTTPS_STATUS')) }}" type="text/css">-->
<section class="main-container">


    <div class="container cart-container-mbt ">
        <h3 class="cart-class mt-30 mb-30">Your Cart</h3>
        <?php if (!empty($cartdata)) {
            ?>
            <div class="row">
                <div class="col-sm-12">
                    <div class="white-background cart-sec-opt">
                        <div class="large-loader">
                            <span>
                                <img src=" {{ asset('assets/images/loader-large.svg',env('HTTPS_STATUS')) }}" alt="loader icon" title="loader icon"
                                     class="img-fluid all-browser">

                                <img src=" {{ asset('assets/images/ajax-loader.gif',env('HTTPS_STATUS')) }}" alt="loader icon" title="loader icon" 
                                     class="img-fluid IE-loader">

                            </span>
                        </div>
                        <div class="row main">
                            <div class="col-md-9">
                                <div class="cart-left-content">
                                    <?php
                                    $totalAmount = 0;
                                    $totalproductPrice = 0;
                                    $totalShippingAmount = 0;
                                    $grandTotal = 0;
                                    $iconUrl = '';
                           
                                    $cryptpPrice = 0;
                                //   if(!empty($cartdataCrypto)){

                                  
                                //     foreach ($cartdataCrypto as $cr) { 

                                //         if (!empty($cr['product_variation_price'])) {
                                //             $cryptpPrice += $cr['product_variation_price'] * (float) $cr['product_quantity'];
                                //         } else {
                                //             $cryptpPrice += $cr['price'] * (float) $cr['product_quantity'];
                                //         }
                                //         $currentStock = 0;
                                //     }
                                //     if($cryptpPrice !=0 ){
                                //         $totalproductPrice = $cryptpPrice ;
                                //     }
                                // }

                                    //   echo '$cartdata: <pre>' .print_r($cartdata,true). '</pre>'; 
                                    foreach ($cartdata as $cd) { 
                                        $store_info = getStoreInfoById($cd['store_id']);
                                        $store_icon = isset($store_info[0]->store_icon) ? $store_info[0]->store_icon : '';
                                        if (!empty($store_icon)) {
                                            $iconUrl = getAttachmentUrlById((int) $store_icon, 'icons');
                                        }
                                        $shippingPrice = isset($cd['shipping_price']) ? $cd['shipping_price'] : 0;
                                        $total_tax = isset($cd['product_tax']) ? $cd['product_tax'] : 0;
                                        $unit_tax = $total_tax / $cd['product_quantity'];
                                        $currentStock = 0;
                                        ?>
                                        <div class="cart-item-table" data-cart-id="<?php echo $cd['cart_id']; ?>">
                                            <div class="row cart-item-repeater clearfix mt-20" >
                                                <div class="mobile-left-cnt col-12 col-md-3">
                                                    <div class="cart-feature-img display-table">
                                                        <span class="display-table-cell">
                                                            <div class="dropdown show animal-dropdown">
                                                                <a class=" dropdown-toggle" href="#" role="button" id="dropdownMenuLink" aria-expanded="false">
                                                                    <span class="anml anmls prdic icsml"> 
                                                                        <img class="img-fluid" src="{{ $iconUrl }}" alt="{{$store_info[0]->store_name}}" title="{{$store_info[0]->store_name}}">
                                                                    </span> 
                                                                </a>
                                                            </div>   
                                                            <?php
                                                            if($cd['product_type'] == 'simple' ){
                                                             $imageUrl = getAttachmentUrlById((int) $cd['feature_image'], 'medium');
                                                             }elseif($cd['product_type'] == 'virtual'){
                                                             $imageUrl = getAttachmentUrlById((int) $cd['feature_image'], 'medium');
                                                             }else{ 
                                                             $imageUrl = getAttachmentUrlById((int) $cd['product_variation_image'], 'medium');
                                                             }
                                                            // $imageUrl = getAttachmentUrlById((int) $cd['feature_image'], 'medium');
                                                            // $imageUrl = "https://dev.ecommedge.com/assets/images/placeholder.jpg";
                                                            // $imageUrl = getAttachmentUrlById((int) $cd['feature_image'], 'medium'); 
                                                            ?>
                                                            <img src="<?php echo $imageUrl; ?>" class="img-fluid" alt="{{$cd['title']}}" title="{{$cd['title']}}">

                                                        </span>



                                                    </div>
                                                </div>
                                                <?php
                                                $title = isset($cd['title']) ? $cd['title'] : '';
                                                $slug = isset($cd['slug']) ? $cd['slug'] : '';
                                                $short_description = isset($cd['short_description']) ? $cd['short_description'] : '';
                                                $featureImage = getAttachmentUrlById((int) $cd['feature_image'], 'product');
                                                ?>
                                                <div class="mobile-right-cnt col-12 col-md-9">
                                                    <div class="cart-description-cnt clearfix">
                                                       <a style="cursor:pointer;" href="<?php echo URL::to('/product/' . $slug); ?>"> <h1 class=" " style="cursor:pointer;"><?php echo $cd['title']; ?></h1></a>
                                                        <div class="quantity-box mt-0 show-quantitiy-panel">

                                                            <?php
                                                            if (isset($cd['variation_id'])) {
                                                                $varation = 'data-variation-id=' . $cd['variation_id'];
                                                            } else {
                                                                $varation = "";
                                                            }
                                                            if ($cd['product_type'] != 'virtual' && isset($cd['current_stock']) && $cd['current_stock'] != '' && $cd['current_stock'] != 0) {
                                                                if ($cd['current_stock'] > 10) {
                                                                    $currentStock = 10;
                                                                } else {
                                                                    $currentStock = $cd['current_stock'];
                                                                }
                                                            } else if($cd['product_type'] == 'virtual') { 
                                                                $currentStock = 10;
                                                            }elseif($cd['product_type'] == 'variant'){
                                                                $currentStock = 10;
                                                                $variable_maxquantitylimit = isset($cd['variable_maxquantitylimit']) && $cd['variable_maxquantitylimit'] != '';

                                                            } else if (isset($cd['variable_currentstock']) && $cd['variable_currentstock'] != '' && $cd['variable_currentstock'] != 0) {
                                                                if ($cd['variable_currentstock'] > 10) {
                                                                    $currentStock = 10;
                                                                } else {
                                                                    $currentStock = $cd['variable_currentstock'];
                                                                }
                                                            }
                                                            if($cd['product_type'] == 'simple' ){
                                                                $maxquantitylimit = isset($cd['maxquantitylimit']) ? $cd['maxquantitylimit']: "";
                                                                if($maxquantitylimit == 0){
                                                                    $currentStock = 1;
                                                                }elseif($currentStock > $maxquantitylimit && $maxquantitylimit != '') {
                                                                    $currentStock = $maxquantitylimit;
                                                                }elseif($currentStock < $maxquantitylimit){
                                                                    $currentStock = $currentStock;
                                                                }elseif($maxquantitylimit == 0){
                                                                    $currentStock = 1;
                                                                }

                                                            }else{
                                                                $maxquantitylimit = isset($cd['variable_maxquantitylimit']) ? $cd['variable_maxquantitylimit']: "";
                                                                if($maxquantitylimit == 0){
                                                                    $currentStock = 1;
                                                                }elseif($currentStock > $maxquantitylimit && $maxquantitylimit != '') {
                                                                    $currentStock = $maxquantitylimit;
                                                                }elseif($currentStock < $maxquantitylimit){
                                                                    $currentStock = $currentStock;
                                                                }elseif($maxquantitylimit == 0){
                                                                    $currentStock = 1;
                                                                }
                                                            }
                                                            
                                                            ?>
                                                            <?php if($cd['product_type'] != 'virtual'){ ?>
                                                                <div class="form-group crt-drp-dwn d-flex mt-10">
                                                                    <?php if ($currentStock > 0) { ?>
                                                                        <span class="mb-5 mr-10" style="line-height:30px">Quantity: </span>
                                                                        <select class="form-control qty-add" id="" name="" data-store-id="<?php echo $cd['store_id']; ?>" <?php echo $varation; ?> data-cart-id="<?php echo $cd['cart_id']; ?>">
                                                                            <?php
                                                                            for ($i = 1; $i <= $currentStock; $i++) {
                                                                                $sel = '';
                                                                                if ($i == $cd['product_quantity']) {
                                                                                    $sel = "selected='selected'";
                                                                                }
                                                                                ?> 
                                                                                <option value="<?php echo $i; ?>" <?php echo $sel; ?>><?php echo $i; ?></option>
                                                                            <?php } ?>
                                                                        </select>
                                                                    <?php } else { ?>
                                                                        <span style="color:red;">out of stock</span>
                                                                    <?php } ?>
                                                                </div>   
                                                            <?php  } ?>
                                                            <?php
                                                            if (!empty($cd['product_variation_price'])) {
                                                            $unitprice = $cd['product_variation_price'];
                                                            } else {
                                                             $unitprice = $cd['price'];
                                                            }
                                                            ?>                                                   
                                                            <?php ?>
                                                            <div class="total-amount clearfix mt-10">
                                                                <p class="mb-10">


                                                                    <!-- <strong>Unit Price: Z<//?php echo number_format($unitprice, 2); ?></strong> -->

                                                                </p>
                                                                <h2 class="text-blue">Total: USDC &nbsp;<b
                                                                        class="product-price  classtotal-<?php echo $cd['cart_id']; ?>"><?php
                                                                            $perproductprice = (float) $cd['product_quantity'] * (float) $unitprice;


                                                                            // $totalPerProcut = $perproductprice + (float) $shippingPrice;
                                                                            $totalPerProcut = $perproductprice;
                                                                            echo number_format($totalPerProcut, 2);
                                                                            ?></b>
                                                                </h2>
                                                                <!-- <div class="clearfix pos-rel">
                                                                    <?php
                                                                      //  if (!empty($cd['shipping_price'])) {
                                                                        ?>
                                                                        <span>Includes shipping USDC 
                                                                            <span class="shipping-<?php //echo $cd['cart_id']; ?> ship-price"><?php // echo isset($cd['shipping_price']) ? number_format($cd['shipping_price'], 2) : 0; ?></span> 
                                                                        <?php //} ?>
                                                                    </span>

                                                                </div> -->
                                                            </div>
                                                            <?php
                                                            echo '<span class="option-clr">';
                                                            echo (ucfirst($cd['option1'])) ? "&nbsp;" . ucfirst($cd['attribute1']).': '.$cd['option1'] : "";
                                                            echo (ucfirst($cd['unit1'])) ? $cd['unit1'] : "";
                                                            if ($cd['option2']) {
                                                                echo ', ';
                                                            }
                                                            echo '</span>';

                                                            echo '<span class="option-kilogram">';
                                                            echo (ucfirst(ucfirst($cd['option2']))) ? ucfirst($cd['attribute2']).': '.$cd['option2'] : "";
                                                            echo (ucfirst($cd['unit2'])) ? ' '.$cd['unit2'] : "";
                                                            if (isset($cd['option3'])) {
                                                                echo ', ';
                                                            }
                                                            echo '</span>';
                                                            echo '<span class="option-kilogram">';
                                                            echo (ucfirst(ucfirst($cd['option3']))) ? ucfirst($cd['attribute3']).': '.$cd['option3'] : "";
                                                            echo (ucfirst($cd['unit3'])) ? ' '.$cd['unit3'] : "";
                                                            echo '</span>';
                                                            
                                                            ?>
                                                        </div>

                                                        <div class="remove-pro-cart">
                                                            <div class="remove-cart-item">
                                                                <a href="javascript:;"
                                                                   data-cart-id="<?php echo $cd['cart_id'] ?>"
                                                                   class="remove-item">
                                                                    <i class="material-icons">
                                                                        delete
                                                                    </i>
                                                                </a>
                                                            </div>                                                            
                                                        </div>

                                                    </div>
                                                </div>
                                            </div><!-- row cart-item-repeater -->
                                        </div>
                                        <?php
                                        $totalproductPrice += $perproductprice;
                                        if (!empty($cd['shipping_price'])) {
                                            $totalShippingAmount = $totalShippingAmount;
                                        }
                                    }
                                    ?>
                                </div><!-- cart-left-content -->
                            </div>
                            <div class="col-md-3 total-container">
                                <div class="cart-total-box card mt-0">
                                    <h3 class="mb-20 pt-15">Cart Total</h3>
                                    <p class="clearfix mb-10">
                                        <span class="lft-prombt">Total Price</span>
                                        <span class="right-prombt pull-right"><b>USDC &nbsp;<?php echo number_format($totalproductPrice, 2); ?></b></span>
                                    </p>
                                    <!-- <p class="clearfix mb-10">
                                        <span class="lft-prombt">Total Shipping</span>
                                        <span class="right-prombt pull-right"><b>USDC &nbsp;<?php // echo number_format($totalShippingAmount, 2); ?></b></span>
                                    </p> -->

                                    <hr>
                                    <h4 class="text-blue mb-10 m-pull-left lne-hgt"><span
                                            class="grand-total-text"> Grand Total:</span> <span
                                            class="pull-right"> <span class="show-mobile767"> Total: </span> <b>USDC &nbsp;<?php
                                                    // $grandTotal = (float) $totalproductPrice + (float) $totalShippingAmount;
                                                    $grandTotal = (float) $totalproductPrice;
                                                    echo number_format($grandTotal, 2);
                                                    ?></b></span></h4>
                                    <a href="/checkout"
                                       class="primary-btn btn btn-primary text-white m-pull-right">Checkout</a>
                                </div><!-- cart-total-box -->
                            </div>
                        </div>
                    </div><!-- cart-sec-opt -->
                </div>
            </div>
        <?php } ?>
    </div><!-- cart-container-mbt -->

  
</section>
@section('meta')
    @include('layouts.cart-meta')
@stop
<!-- Content Ends-->
@endsection