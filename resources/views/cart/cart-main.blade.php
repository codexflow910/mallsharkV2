@extends('layouts.frontend-main')
<!-- Main Banner Starts -->
@section('content')
@section('title', 'Cart')
@section('google-analytics')
@include('layouts.google-analytics')
@stop
<!-- Content Starts -->
<script src="{{ URL::asset('assets/js/cart.js',env('HTTPS_STATUS')) }}"></script>
<!--<link rel="stylesheet" href="{{ URL::asset('assets/css/ability-theme-style.css' ,env('HTTPS_STATUS')) }}" type="text/css">-->
<section class="main-container">


    <div class="container cart-container-mbt ">
        <h3 class="cart-class mt-30 mb-30">Your Cart</h3>
        <?php
        // echo 'Data: <pre>' .print_r($cartdata,true). '</pre>'; die();
        if (!empty($cartdata)) {
        ?>
            <div class="row">
                <div class="col-sm-12">
                    <div class="white-background cart-sec-opt">
                        <div class="large-loader">
                            <span>
                                <img src=" {{ asset('assets/images/loader-large.svg',env('HTTPS_STATUS')) }}" alt="loader icon" title="loader icon" class="img-fluid all-browser">

                                <img src=" {{ asset('assets/images/ajax-loader.gif',env('HTTPS_STATUS')) }}" alt="loader icon" title="loader icon" class="img-fluid IE-loader">

                            </span>
                        </div>
                        <div class="row main">
                            <div class="col-md-12">
                                <div class="cart-left-content">
                                    <div class="crtpgttl crtinfbxa">
                                        <div class="row">
                                            <div class=" col-md-6 col-12 cartHeading">Product Info</div>
                                            <div class=" col-md-3 col-6 cartHeading">Quantity</div>
                                            <div class=" col-md-3 col-6 cartHeading">Price</div>
                                        </div>
                                    </div>
                                    <?php
                                    // echo 'Data: <pre>' .print_r($cartdata,true). '</pre>'; die();
                                    $totalAmount = 0;
                                    $totalproductPrice = 0;
                                    $totalShippingAmount = 0;
                                    $grandTotal = 0;
                                    $iconUrl = '';
                                    $product_counter_index = 0;
                                    $subTotal = 0;
                                    $cryptpPrice = 0;
                                    $totalDiscount = 0;
                                    $perPoductPrice = 0;
                                    foreach ($cartdata as $cd) {
                                        $product_counter_index++;
                                        $store_info = getStoreInfoById($cd['store_id']);
                                        $store_icon = isset($store_info[0]->store_icon) ? $store_info[0]->store_icon : '';
                                        if (!empty($store_icon)) {
                                            $iconUrl = getAttachmentUrlById((int) $store_icon, 'icons');
                                        }
                                        $shippingPrice = isset($cd['shipping_price']) ? $cd['shipping_price'] : 0;
                                        // $total_tax = isset($cd['product_tax']) ? $cd['product_tax'] : 0;
                                        // $unit_tax = $total_tax / $cd['product_quantity'];
                                        $currentStock = 0;

                                        $title = isset($cd['title']) ? $cd['title'] : '';
                                        $slug = isset($cd['slug']) ? $cd['slug'] : '';
                                        $short_description = isset($cd['short_description']) ? $cd['short_description'] : '';

                                       $category_id = getCategoriesByProductID($cd['product_id']);
                                       $product_category = getCategoryByCateId($category_id[0]);
                                       $store = getStoreInfoByIdApi($cd['store_id']);

                                    ?>
                                    {{-- @dd($cd); --}}
                                        <div class="cart-item-table crtpgttl inrlop product-cart_id-<?php echo $cd['cart_id']; ?>" data-product_id="<?php echo $cd['product_id']; ?>" data-variation_id="{{$cd['variation_id']}}" data-store_id="<?php echo $store->store_name; ?>" data-index="{{$product_counter_index}}">
                                         @if($cd['product_type'] == 'simple')
                                            @if($cd['status'] == '2')
                                                <?php

                                                    $unitprice = $cd['on_sale'] == 1 ? $cd['sale_price'] : $cd['price'];
                                                   
                                                    $actualPrice = $cd['price'];
                                                    $perproductprice = (float) $cd['product_quantity'] * (float) $unitprice;
                                                    $totalPerProcut = $perproductprice;
                                                    $actualperproductprice = (float) $cd['product_quantity'] * (float) $actualPrice;
                                                   
                                                                // echo number_format($totalPerProcut, 2);
                                                ?>
                                                <div class="row" style="pointer-events: none;opacity: 0.5;">
                                            @else
                                                    <div class="row">
                                                        <?php
                                                        //  if (!empty($cd['product_variation_price'])) {
                                                        // $unitprice = $cd['product_variation_price'];
                                                    // } else {
                                                        $unitprice = $cd['on_sale'] == 1 ? $cd['sale_price'] : $cd['price'];
                                                        $actualPrice = $cd['price'];
                                                    // }
                                                    $perproductprice = (float) $cd['product_quantity'] * (float) $unitprice;
                                                    $totalPerProcut = $perproductprice;
                                                                    // echo number_format($totalPerProcut, 2);
                                                    $totalproductPrice += $totalPerProcut;
                                                    $actualperproductprice = (float) $cd['product_quantity'] * (float) $actualPrice;
                                                    if (!empty($cd['shipping_price'])) {
                                                        $totalShippingAmount += $totalShippingAmount;
                                                    }
                                                    $subTotal += $actualperproductprice;
                                                    if($cd['on_sale'] == 1){
                                                        $perProductDiscount = $actualperproductprice - $perproductprice;
                                                        $totalDiscount += $perProductDiscount;
                                                    }
                                                    ?>
                                         @endif
                                          @elseif($cd['product_type'] == 'variant')
                                                @if($cd['status'] == '2')

                                                    <div class="row" style="pointer-events: none;opacity: 0.5;">
                                                @elseif($cd['variation_id'] == "")
                                                <?php
                                                    $unitprice = $cd['on_sale'] == 1 ?  $cd['product_variation_sale_price'] : $cd['product_variation_price'];
                                                    $perproductprice = (float) $cd['product_quantity'] * (float) $unitprice;
                                                    $totalPerProcut = $perproductprice;
                                                    $actualperproductprice = (float) $cd['product_quantity'] * (float) $cd['product_variation_price'];
                                                  
                                                    
                                                        ?>
                                                    <div class="row" style="pointer-events: none;opacity: 0.5;">
                                                    @else
                                                        <div class="row">
                                                            <?php
                                                            //  if (!empty($cd['product_variation_price'])) {
                                                        $unitprice = $cd['on_sale'] == 1 ?  $cd['product_variation_sale_price'] : $cd['product_variation_price'];
                                                    // } else {
                                                    //     $unitprice = $cd['price'];
                                                    // }
                                                    $perproductprice = (float) $cd['product_quantity'] * (float) $unitprice;
                                                                    $totalPerProcut = $perproductprice;
                                                                    // echo number_format($totalPerProcut, 2);
                                                    $totalproductPrice += $totalPerProcut;
                                                    if (!empty($cd['shipping_price'])) {
                                                        $totalShippingAmount += $totalShippingAmount;
                                                    }
                                                    $actualperproductprice = (float) $cd['product_quantity'] * (float) $cd['product_variation_price'];
                                                    $subTotal += $actualperproductprice;
                                                    if($cd['on_sale'] == 1){
                                                        // $perProductDiscount = $actualperproductprice - $perproductprice;
                                                        $perProductDiscount = (($cd['product_variation_price'] - $unitprice) * $cd['product_quantity']);
                                                        $totalDiscount += $perProductDiscount;
                                                    }
                                                    ?>
                                                @endif
                                            @elseif($cd['product_type'] == 'virtual')
                                            @if($cd['status'] == '2')
                                                <?php
                                                      $unitprice = $cd['on_sale'] == 1 ? $cd['sale_price'] : $cd['price'];
                                                     $actualPrice = $cd['price'];
                                                     $actualperproductprice = (float) $cd['product_quantity'] * (float) $actualPrice;
                                                    $perproductprice = (float) $cd['product_quantity'] * (float) $unitprice;
                                                    $totalPerProcut = $perproductprice;
                                                   
                                                                    // echo number_format($totalPerProcut, 2);
                                                ?>
                                                <div class="row" style="pointer-events: none;opacity: 0.5;">
                                            @else
                                                    <div class="row">
                                                        <?php
                                                        //  if (!empty($cd['product_variation_price'])) {
                                                        // $unitprice = $cd['product_variation_price'];
                                                    // } else {
                                                          $unitprice = $cd['on_sale'] == 1 ? $cd['sale_price'] : $cd['price'];
                                                          $actualPrice = $cd['price'];
                                                    // }
                                                            $actualperproductprice = (float) $cd['product_quantity'] * (float) $actualPrice;
                                                    $perproductprice = (float) $cd['product_quantity'] * (float) $unitprice;
                                                                    $totalPerProcut = $perproductprice;
                                                                    // echo number_format($totalPerProcut, 2);
                                                    $totalproductPrice += $totalPerProcut;
                                                    if (!empty($cd['shipping_price'])) {
                                                        $totalShippingAmount += $totalShippingAmount;
                                                    }
                                                    $subTotal += $actualperproductprice;
                                                    if($cd['on_sale'] == 1){
                                                        $perProductDiscount = $actualperproductprice - $perproductprice;
                                                        $totalDiscount += $perProductDiscount;
                                                    }
                                                    ?>
                                            @endif
                                            @else
                                            <div class="row">
                                          @endif


                                            <span class="cart-category" data-category-name="{{$product_category['categoryTitle']}}"></span>



                                                <div class=" col-md-6">
                                                    <div class="mobile-left-cnt  d-flex">
                                                        <div class="cart-feature-img  ">
                                                            <span class="display-table-cell">
                                                                <div class="dropdown show animal-dropdown">
                                                                    <a class=" dropdown-toggle" href="#" role="button" id="dropdownMenuLink" aria-expanded="false">
                                                                        <span class="anml anmls prdic icsml">
                                                                            <img class="img-fluid" src="{{ $iconUrl }}" alt="{{$store_info[0]->store_name}}" title="{{$store_info[0]->store_name}}">
                                                                        </span>
                                                                    </a>
                                                                </div>

                                                                <?php
                                                                if ($cd['product_type'] == 'simple') {
                                                                    $imageUrl = getAttachmentUrlById((int) $cd['feature_image'], 'medium');
                                                                } elseif ($cd['product_type'] == 'variant') {
                                                                    $imageUrl = getAttachmentUrlById((int) $cd['product_variation_image'], 'medium');
                                                                    if(empty($imageUrl)){
                                                                            $imageUrl = getAttachmentUrlById((int) $cd['feature_image'], 'medium');
                                                                        }
                                                                } else {
                                                                    $imageUrl = getAttachmentUrlById((int) $cd['feature_image'], 'medium');
                                                                }
                                                                // $image_url = getAttachmentUrlById((int) $cd['feature_image'], 'medium');
                                                                ?>
                                                                <img src="<?php echo !empty($imageUrl) ? $imageUrl : URL::asset('/assets/images/placeholder.jpg')  ; ?>" class=" cart-product-thumbnail" style="max-width:100px; border:1px solid #d3d3d3; border-radius:6px; margin-right:10px" alt="{{$cd['title']}}" title="{{$cd['title']}}">

                                                            </span>
                                                        </div>
                                                        <div>
                                                        <a style="cursor:pointer;" href="<?php echo URL::to('/product/' . $slug); ?>">
                                                            <h1 class="cart-item-name" data-item-name="{{$cd['title']}}"><?php echo $cd['title']; ?>

                                                                <div class="remove-pro-cart">
                                                                    <div class="remove-cart-item">
                                                                        <a href="javascript:;" style="color:#bdbdbd;display:inline-block" data-cart-id="<?php  echo $cd['cart_id']; ?>" class="remove-item">
                                                                            <i class="fa fa-trash" aria-hidden="true" style="color: #d5d5d5;"></i>
                                                                        </a>
                                                                    </div>
                                                                </div>
                                                            </h1>
                                                        </a>
                                                        <div class="cartProductVariation">
                                                            <?php
                                                                echo '<span class="option-clr" data-variations="' . $cd["option1"] . '">';
                                                                echo (ucfirst($cd['option1'])) ? "&nbsp;" . ucfirst($cd['attribute1']).': '.$cd['option1'] : "";
                                                                echo (ucfirst($cd['unit1'])) ? $cd['unit1'] : "";
                                                                if ($cd['option2']) {
                                                                    echo ', ';
                                                                }
                                                                echo '</span>';

                                                                echo '<span class="option-kilogram">';
                                                                echo (ucfirst(ucfirst($cd['option2']))) ? ucfirst($cd['attribute2']).': '.$cd['option2'] : "";
                                                                echo (ucfirst($cd['unit2'])) ? ' '.$cd['unit2'] : "";
                                                                if (isset($cd['option3']) && !empty($cd['option3'])) {
                                                                    echo ', ';
                                                                }
                                                                echo '</span>';
                                                                echo '<span class="option-kilogram">';
                                                                echo (ucfirst(ucfirst($cd['option3']))) ? ucfirst($cd['attribute3']).': '.$cd['option3'] : "";
                                                                echo (ucfirst($cd['unit3'])) ? ' '.$cd['unit3'] : "";
                                                                echo '</span>';

                                                            ?>
                                                        </div>
                                                    </div>
                                                    </div>

                                                </div>
                                                <div class=" col-md-3 col-6"><?php
                                                    // dump($cd);
                                                                        if (isset($cd['variation_id'])) {
                                                                            $varation =  $cd['variation_id'];
                                                                        } else {
                                                                            $varation = "";
                                                                        }
                                                                        if ($cd['product_type'] == 'simple' && isset($cd['current_stock']) && $cd['current_stock'] != '' && $cd['current_stock'] != 0) {
                                                                            if ($cd['current_stock'] > 10) {
                                                                                $currentStock = 10;
                                                                            } else {
                                                                                $currentStock = $cd['current_stock'];
                                                                            }
                                                                        } else if ($cd['product_type'] == 'virtual') {
                                                                            $currentStock = 10;
                                                                        } elseif ($cd['product_type'] == 'variant') {

                                                                            // $variable_maxquantitylimit = isset($cd['variable_maxquantitylimit']) && $cd['variable_maxquantitylimit'] != '';
                                                                            if (isset($cd['variable_currentstock']) && $cd['variable_currentstock'] != '' && $cd['variable_currentstock'] != 0) {
                                                                                if ($cd['variable_currentstock'] > 10) {
                                                                                    $currentStock = 10;
                                                                                } else {
                                                                                    $currentStock = $cd['variable_currentstock'];
                                                                                }
                                                                            }
                                                                        }
                                                                        if ($cd['product_type'] == 'simple') {
                                                                            $maxquantitylimit = isset($cd['maxquantitylimit']) ? $cd['maxquantitylimit'] : "";
                                                                            if ($maxquantitylimit == 0) {
                                                                                $currentStock = 1;
                                                                            } elseif ($currentStock > $maxquantitylimit && $maxquantitylimit != '') {
                                                                                $currentStock = $maxquantitylimit;
                                                                            } elseif ($currentStock < $maxquantitylimit) {
                                                                                $currentStock = $currentStock;
                                                                            } elseif ($maxquantitylimit == 0) {
                                                                                $currentStock = 1;
                                                                            }
                                                                        } else {
                                                                            $maxquantitylimit = isset($cd['variable_maxquantitylimit']) ? $cd['variable_maxquantitylimit'] : "";
                                                                            if ($maxquantitylimit == 0) {
                                                                                $currentStock = 1;
                                                                            } elseif ($currentStock > $maxquantitylimit && $maxquantitylimit != '') {
                                                                                $currentStock = $maxquantitylimit;
                                                                            } elseif ($currentStock < $maxquantitylimit) {
                                                                                $currentStock = $currentStock;
                                                                            } elseif ($maxquantitylimit == 0) {
                                                                                $currentStock = 1;
                                                                            }
                                                                        }

                                                                    ?>
                                                                     <?php if ($cd['product_type'] != 'virtual') {
                                                                         if ($cd['product_type'] == 'variant') {
                                                                             $productPrice = $cd['product_variation_price'];
                                                                             $productSalePrice = $cd['product_variation_sale_price'];
                                                                             $unitprice = $cd['on_sale'] == 1 ? $productSalePrice : $productPrice;
                                                                             $actualPrice = $productPrice;
                                                                         } else {
                                                                             $productPrice = $cd['price'];
                                                                             $unitprice = $cd['on_sale'] == 1 ? $cd['sale_price'] : $productPrice;
                                                                             $actualPrice = $productPrice;
                                                                         }

                                                                         $actualperproductprice = (float)$cd['product_quantity'] * (float)$actualPrice;
                                                                         $perproductprice = (float)$cd['product_quantity'] * (float)$unitprice;
                                                                         $totalPerProcut = $perproductprice;

                                                                     ?>
                                                        <div class="form-group crt-drp-dwn d-flex product-cart-price" style="margin-bottom:0px;" data-price={{$productPrice}}>
                                                            <?php if ($currentStock > 0) { ?>
                                                                <div class="qty-input if-variation">
                                                                    <div class="quantity-control" data-quantity="10">
                                                                        <button data-cart-id="<?php echo $cd['cart_id']; ?>" data-pro-id="<?php echo $cd['product_id']; ?>" variation-id="{{$varation}}" data-stor-id="<?php echo $cd['store_id']; ?>" data-on-sale="{{$cd['on_sale']}}" data-price="{{$productPrice}}" data-price-sale="{{$unitprice}}" class="quantity-btn minus-icon" data-quantity-minus=""> <svg viewBox="0 0 409.6 409.6">
                                                                                <g>
                                                                                    <g>
                                                                                        <path d="M392.533,187.733H17.067C7.641,187.733,0,195.374,0,204.8s7.641,17.067,17.067,17.067h375.467 c9.426,0,17.067-7.641,17.067-17.067S401.959,187.733,392.533,187.733z" />
                                                                                    </g>
                                                                                </g>
                                                                            </svg>
                                                                        </button>

                                                                        <input type="number" class="quantity-input " data-quantity-target="" value="<?php echo $cd['product_quantity']; ?>" step="1" min="1" max="10" name="quantity" cart-count="{{$cd['cart_count']}}" disabled>
                                                                        <button data-cart-id="<?php echo $cd['cart_id']; ?>" data-maxquantity_simple="{{$currentStock}}" variation-id="{{$varation}}" data-stor-id="<?php echo $cd['store_id']; ?>" data-pro-id="<?php echo $cd['product_id']; ?>" data-on-sale="{{$cd['on_sale']}}" data-price="{{$productPrice}}" data-price-sale="{{$unitprice}}" data-product-type="{{$cd['product_type']}}" class="quantity-btn plus-icon" data-quantity-plus="">
                                                                            <svg viewBox="0 0 426.66667 426.66667">
                                                                                <path d="m405.332031 192h-170.664062v-170.667969c0-11.773437-9.558594-21.332031-21.335938-21.332031-11.773437 0-21.332031 9.558594-21.332031 21.332031v170.667969h-170.667969c-11.773437 0-21.332031 9.558594-21.332031 21.332031 0 11.777344 9.558594 21.335938 21.332031 21.335938h170.667969v170.664062c0 11.777344 9.558594 21.335938 21.332031 21.335938 11.777344 0 21.335938-9.558594 21.335938-21.335938v-170.664062h170.664062c11.777344 0 21.335938-9.558594 21.335938-21.335938 0-11.773437-9.558594-21.332031-21.335938-21.332031zm0 0" />
                                                                            </svg>
                                                                        </button>
                                                                    </div>
                                                                </div>
                                                            <?php } else { ?>
                                                                <span style="color:red;">out of stock</span>
                                                            <?php } ?>
                                                        </div>
                                                    <?php  }else{ ?>
                                                        <span style="">N/A</span>
                                                    <?php } ?>


                                                </div>
                                                <div class=" col-md-3 col-6">
                                                    <?php
                                                    // if (!empty($cd['product_variation_price'])) {
                                                    //     $unitprice = $cd['product_variation_price'];
                                                    // } else {
                                                    //     $unitprice = $cd['price'];
                                                    // }
                                                    ?>
                                                    <div class="total-amount clearfix ">
                                                        <p >
                                                            @if($cd['on_sale'] == 1)
                                                                <b class="product-price classtotal  classtotal-<?php echo $cd['cart_id']; ?>" style="font-size:16px; font-weight:bold; color: red !important">
                                                                    $<?php
                                                                         // $perproductprice = (float) $cd['product_quantity'] * (float) $unitprice;
                                                                         // $totalPerProcut = $perproductprice;
                                                                         echo number_format($totalPerProcut, 2);
                                                                         ?>
                                                                </b><br>
                                                                <del class="product-price actualPrice  actualPrice-<?php echo $cd['cart_id']; ?>" style="font-size:11px;">
                                                                    $<?php
                                                                         echo number_format($actualperproductprice, 2);
                                                                         ?>
                                                                </del>
                                                                <del class="product-price discount-amount  discount-amount-<?php echo $cd['cart_id']; ?> d-none" style="font-size:11px;">
                                                                    $<?php
                                                                   $discount_amount = $actualperproductprice - $totalPerProcut;
                                                                         echo number_format($discount_amount, 2);
                                                                         ?>
                                                                </del>
                                                            @else
                                                                <b class="product-price actualPrice  classtotal-<?php echo $cd['cart_id']; ?>" style="font-size:16px; font-weight:bold">
                                                                    $<?php
                                                                         // $perproductprice = (float) $cd['product_quantity'] * (float) $unitprice;
                                                                         // $totalPerProcut = $perproductprice;
                                                                         echo number_format($actualperproductprice, 2);
                                                                         ?>
                                                                </b>
                                                            @endif

                                                        </p>
                                                    </div>
                                                </div>

                                            </div>
                                        </div>

                                <?php

                            }

                                ?>

                                <div class="crtpgttl btmttl">
                                    <div class="row">
                                        <div class=" col-md-3">
                                            <a href="/checkout" class="primary-btn btn bold btn-primary text-white btn-block m-pull-right">Checkout</a>
                                        </div>
                                        <div class=" col-md-4 d-none d-md-block">
                                            <!-- <p class="   prcbxin"><span class="grand-total-text">
                                                    Sub
                                                    Total:</span>
                                                <span class=" ">
                                                    <span class="show-mobile767"> Total:
                                                    </span> <b class="total-price-cart">$&nbsp;<?php
                                                    // $grandTotal = (float) $totalproductPrice + (float) $totalShippingAmount;
                                                    // $grandTotal = isset($totalproductPrice)? (float) $totalproductPrice : 0 ;
                                                    // echo number_format($grandTotal, 2);
                                                    ?></b></span>
                                            </p> -->
                                        </div>
                                        <div class=" col-md-5  ">
                                            <p class="   prcbxin">
                                                 <span class="">
                                                    <span class="show-mobile767"> Discount :
                                                    </span> <b class="total-discount-cart">$<?php
                                                    // $grandTotal = (float) $totalproductPrice + (float) $totalShippingAmount;
                                                    $grandTotal = isset($totalDiscount)? (float) $totalDiscount : 0 ;
                                                    echo number_format($grandTotal, 2);
                                                    ?></b>
                                                </span>
                                                &nbsp;&nbsp;<span style="color: #bdbdbd; font-weight:200">|</span>&nbsp;&nbsp;
                                                <span class=" ">
                                                    <span class="show-mobile767"> Sub
                                                    Total:
                                                    </span> <b class="total-price-cart">$<?php
                                                    // $grandTotal = (float) $totalproductPrice + (float) $totalShippingAmount;
                                                    $grandTotal = isset($subTotal)? (float) $subTotal : 0 ;
                                                    echo number_format($grandTotal, 2);
                                                    ?></b>
                                                </span>
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <?php
                                }else{
                                    ?>
                                     <div class="no-record-found-banner cart-page-img">
                                        <a href="https://mallshark.net/search?keyword=">
                                        <img src="{{ URL::asset('assets/images/No-Orders.png') }}" alt="">
                                    </a>
                                    </div>
                                    <?php
                                    //echo "Your Cart is Empty Now";
                                } ?>



</section>
@section('meta')
@include('layouts.cart-meta')
@stop
<!-- Content Ends-->
<script>
    $(document).ready(function () {
        const items = [];
        $('.cart-item-table').each(function () {
            let productId = $(this).data('product_id');
            let product_index = $(this).attr('data-index');
            let store = $(this).data('store_id');
            let item_name =  $(this).find('.cart-item-name').attr('data-item-name');
            let category = $(this).find('.cart-category').attr('data-category-name');
            let product_price = $(this).find('.product-cart-price').attr('data-price');
            let product_quantity = $(this).find('.quantity-input ').val();
            
            const item = {
                item_id: productId,
                item_name: item_name,
                item_category: category,
                index: product_index,
                price: parseFloat(product_price),
                quantity: parseInt(product_quantity),
                item_brand: store,
                item_category: category,
                item_list_id: "cart_products",
                item_list_name: "Cart Products",
            };

            items.push(item);
        });

        dataLayer.push({ ecommerce: null }); 
        dataLayer.push({
        event: "view_cart",
            ecommerce: {
                currency: "USD",
                value: 7.77,
                items: items,
            }
        });
    });
</script>
@endsection