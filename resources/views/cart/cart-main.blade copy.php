@extends('layouts.new-main')
<!-- Main Banner Starts -->
@section('content')
@section('title', 'Cart')
<!-- Content Starts -->
<main class="cart-container">
    <div class="container">
        <div class="row">
            <div class="col-sm-12">
                <div class="item-in-basket">
                    <?php
                    $counter = 0;
                    $cartData = cartDetail();
                    if (!empty($cartData)) {
                        $counter = count($cartData);
                    }
                    ?>
                    <h3 class="basket-count">Your Basket (<?php echo $counter; ?> items)</h3>
                    @if(Session::has('SuccessMessage'))
                    <div class="alert alert-success alert-success-mbt" role="alert">
                        {{ Session::get('SuccessMessage') }}
                    </div>
                    @endif
                </div>	
                <?php
                if (!empty($cartdata)) {
                    foreach ($cartdata as $key => $val) {
                        $totalAmount = 0;
                        $totalShipamount = 0
                        ?>
                        <div class="table-container shopcart">
                            <div class="leftlarge-cart">
                                <div class="cart-item-product">	
                                    <?php foreach ($val as $row) { ?>
                                        <div class="product-cart-list product_<?php echo $row['product_id'] ?>">
                                            <div class="product-list-top">
                                                <div class="product-image">
                                                    <div class="img-cnt-box">
                                                        <?php $image_url = getAttachmentUrlById($row['feature_Image'], 'medium'); ?>
                                                        <img src="<?php echo $image_url; ?>" alt="" class="img-fluid" onerror="this.src='{{url('/assets/images/placeholder.jpg')}}'">
                                                    </div>
                                                    <div class="add-notes-to-vendor">
                                                        <input type="text" class="form-control" data-product-id="{{$row['product_id']}}" name="here" id="here" value='' placeholder="Add note for brand" maxlength="150" />
                                                        <span class="error">you have reached a maximum charaters</span>
                                                    </div>
                                                </div>
                                                <div class="product-body-right">
                                                    <div class="right-desption-one">
                                                        <h2><?php ?><?php echo $row['title']; ?></h2>
                                                        <div class="store-logo">
                                                            <span>By: <?php echo $row['shop_name']; ?></span>
                <!--                                                        <img src="{{ asset('assets/images/st-logo.png',env('HTTPS_STATUS')) }}" alt="image" >    -->
                                                        </div>
                                                        <div class="save-remove-links">
                                                            <a href="javascript:;" class="save-later">Save For Later</a>
                                                            <!--                                                        <a href="{{URL::to('item-delete/')."/".$row['cart_id']}}" class="remove-item">Remove Item</a>-->
                                                            <a href="javascript:void(0);" class="remove-item" data-store-id="<?php echo $row['store_id']; ?>" data-cart-id="<?php echo $row['cart_id']; ?>">Remove Item</a>
                                                        </div>
                                                        <?php
                                                        echo '<span class="option-clr">';
                                                        echo (ucfirst($row['option1'])) ? $row['option1'] : "";
                                                        echo (ucfirst($row['unit1'])) ? $row['unit1'] : "";



                                                        echo '</span>';

                                                        echo '<span class="option-kilogram">';
                                                        echo (ucfirst(ucfirst($row['option2']))) ? $row['option2'] : "";
                                                        echo (ucfirst($row['unit2'])) ? $row['unit2'] : "";
                                                        echo '</span>';
                                                        ?>
                                                    </div>
                                                    <div class="right-desption-two">
                                                        <div class="pro-price">
                                                            <div>
                                                                <?php
                                                                if (!empty($row['variation_sale_price'])) {
                                                                    $price = $row['variation_sale_price'];
                                                                } else {
                                                                    $price = $row['price'];
                                                                }
                                                                ?>
                                                                <span class="black-text">USDC :</span><span class="blue-text product-amount"><?php echo $res = (float) $row['product_quantity'] * (float) $price; ?></span>
                                                            </div>
                                                            <div>
                                                                <span class="gray-text">Z<?php echo $price; ?> Each</span>
                                                            </div>
                                                            <div class="form-group quantity-box">
                                                                <?php
                                                                if (isset($row['variation_id'])) {
                                                                    $varation = 'data-variation-id=' . $row['variation_id'];
                                                                } else {
                                                                    $varation = "";
                                                                }
                                                                ?>
                                                                <select class="form-control productquantity" data-cart-id="<?php echo $row['cart_id']; ?>" data-store-id="<?php echo $row['store_id']; ?>" <?php echo $varation; ?> id="product_quantity" name="product_quantity">
                                                                    <?php
                                                                    if (!empty($row['product_variation_quantity'])) {
                                                                        $quantity = $row['product_variation_quantity'];
                                                                    } else {
                                                                        $quantity = $row['current_stock'];
                                                                    }
                                                                    if ($quantity >= 20) {
                                                                        $quantity = 20;
                                                                    }
                                                                    $productQuantity = $row['product_quantity'];
                                                                    $productQuantity;
                                                                    $selected = '';
                                                                    for ($i = 1; $i <= $quantity; $i++) {
                                                                        if ($i == $productQuantity) {
                                                                            $selected = "selected='selected'";
                                                                        } else {
                                                                            $selected = '';
                                                                        }
                                                                        ?>
                                                                        <option value="<?php echo $i; ?>" <?php echo $selected; ?>><?php echo $i; ?></option>
                                                                    <?php } ?>
                                                                </select>
                                                            </div>
                                                            <div class="shipping-option-caption">
                                                                <div>
                                                                    <?php
                                                                    $var = "";
                                                                    if (isset($row['handling_fee']) &&!empty($row['handling_fee'])) {
                                                                      
                                                                        $var =$row['handling_fee'];
                                                                        if (!empty($var['domestic'])) {
                                                                            $totalShipamount = $totalShipamount + $var['domestic'];
                                                                            ?>
                                                                            <span class="gray-text">Shipping: $<?php echo $var['domestic']; ?></span>

                                                                            <?php
                                                                        }
                                                                    }
                                                                    ?>
                                                                    <?php
                                                                    $ship = "";
                                                                    if ($row['processing_time'] == '0') {
                                                                        $ship = '1  Day';
                                                                    } elseif ($row['processing_time'] == '1') {
                                                                        $ship = '1-2 Business Day';
                                                                    } elseif ($row['processing_time'] == '2') {
                                                                        $ship = '1-3 Business Day';
                                                                    } elseif ($row['processing_time'] == '3') {
                                                                        $ship = '3-5 Business Day';
                                                                    } elseif ($row['processing_time'] == '4') {
                                                                        $ship = '1-2 Weeks';
                                                                    } elseif ($row['processing_time'] == '5') {
                                                                        $ship = '2-3 Weeks';
                                                                    } elseif ($row['processing_time'] == '6') {
                                                                        $ship = '>3-4 Weeks';
                                                                    } elseif ($row['processing_time'] == '8') {
                                                                        $ship = '4-6 Weeks';
                                                                    } elseif ($row['processing_time'] == '6') {
                                                                        $ship = '6-8 Weeks';
                                                                    }
                                                                    ?>
                                                                    <?php
                                                                    if (!empty($var['domestic'])) {
                                                                        ?>
                                                                        <span class="gray-text">Ready to ship in <?php echo $ship; ?></span>                                             
                                                                    <?php } ?>
                                                                </div>

                                                            </div>

                                                        </div>
                                                        <div class="form-group shipping-option">


                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="checkout-pro-button">
                                                @if(getCurrentUserId())
                                                <a class="checkout-item" href="{{URL::to('checkout/')."/".base64_encode($val[0]['store_id'])}}">Check out from only this brand <span class="arrow-icon"><i class="fa fa-angle-right" aria-hidden="true"></i></span></a> 
                                                @else
                                                <a  href="havascript:void(0)"  data-toggle="modal" data-target="#login-registerpopup">Check out from only this brand <span class="arrow-icon"><i class="fa fa-angle-right" aria-hidden="true"></i></span></a>
                                                @endif
                                            </div>
                                        </div>
                                        <?php
                                        if (!empty($row['variation_sale_price'])) {
                                            $price = $row['variation_sale_price'];
                                        } else {
                                            $price = $row['price'];
                                        }
                                        $totalAmount = $totalAmount + (float) $row['product_quantity'] * (float) $price;
                                    }
                                    ?>
                                </div>
                            </div>
                            <div class="aside-section total-section">
                                <div class="checkout-option-container">
                                    <div class="payment-section">
                                        <h3>How You'll Pay</h3>
                                        <div class="section-head">
                                            <div class="items-calculated">
                                                <div class="item-counter">
                                                    <span class="item-text">Items(s)</span>
                                                    <span class="item-price">Z<?php echo $totalAmount; ?></span>									
                                                </div>
                                                <div class="item-counter">
                                                    <span class="item-text">Shipping</span>
                                                    <span class="item-price">Z<?php echo $totalShipamount; ?></span>									
                                                </div>
                                            </div><!-- items-calculated -->

                                            <hr class="out-side-both">							
                                            <div class="items-calculated">
                                                <div class="item-counter">
                                                    <span class="item-text">Total <b><?php echo count($val); ?></b> Items</span>
                                                    <span class="item-price">Z<?php
                                                        echo $res = (float) $totalAmount +
                                                        (float) $totalShipamount;
                                                        ?></span>									
                                                </div>
                                            </div><!-- items-calculated -->
                                        </div>
                                        <div class="chekout-btn">

                                            <!--                                            <a href="#" data-store-id="{{$val[0]['store_id']}}" class="btn-primary checkout-item">Checkout</a> -->
                                            @if(getCurrentUserId())
                                            <a href="{{URL::to('checkout/')."/".base64_encode($val[0]['store_id'])}}" class="btn-primary checkout-item">Checkout</a>
                                            @else
                                            <a data-location="{{URL::to('checkout/')."/".base64_encode($val[0]['store_id'])}}" href="havascript:void(0)"  class="btn-primary checkout-item" data-toggle="modal" data-target="#login-registerpopup">Checkout</a>
                                            @endif
                                        </div>
                                        <div class="shipping-caption-mbt">
                                            <p>Sellers never see your credit card information</p>
                                            <p class="gray-text">Additional duties and taxes may apply.</p>
                                        </div>

                                    </div>

                                </div>
                            </div>
                        </div><!-- table-container -->
                        <?php
                    }
                } else {
                    ?>
                    <div class="table-container ">
                        <div class="body-message text-center">
                            <div class="inner-body-cart-emphty">
                                <h3><i class="fa fa-shopping-basket" aria-hidden="true"></i> Shopping cart is empty.</h3>
                                <p>Ways to Shop — new, novel and beautiful</p>
                            </div>
                        </div>
                    </div>
                <?php } ?>
                <div class="table-container empty-basket" style="display: none;">
                    <div class="body-message text-center">
                        <div class="inner-body-cart-emphty">
                            <h3><i class="fa fa-shopping-basket" aria-hidden="true"></i> Shopping cart is empty.</h3>
                            <p>Ways to Shop — new, novel and beautiful</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

<!--        <div class="saveforlater mt-85">
            <div class="row-large">
                <div class="col-sm-12-large">
                    <h2>Save For Later</h2>
                </div>
                <div class="row save-later-repeater">
                    <div class="col-sm-3">
                        <div class="image-container-save">
                            <img src="https://placeimg.com/190/120/tech" class="img-fluid">
                        </div>
                    </div>
                    <div class="col-sm-9">
                        <div class="save-container-body">
                            <h3> Up to 50% off Highly-Rated Italian <br>Cuisine
                                <div class="pull-right movetocart-sec">
                                    <div class="clearfix">
                                        <a class="btn btn-primary btn-simple" href="javascript:;">Move To Cart</a>
                                    </div>
                                    <div class="text-right">
                                        <a href="javascript:;" class="remove-item-red">Remove Item</a>
                                    </div>
                                </div>
                            </h3>
                            <div class="by-herbs">
                                <span>By:Herbal Arts</span>
                            </div>
                            <div class="price-usd">
                                <span>Z : <span class="blue-text">$50</span></span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row save-later-repeater">
                    <div class="col-sm-3">
                        <div class="image-container-save">
                            <img src="https://placeimg.com/190/120/sepia" class="img-fluid">
                        </div>
                    </div>
                    <div class="col-sm-9">
                        <div class="save-container-body">
                            <h3> Up to 50% off Highly-Rated Italian <br>Cuisine
                                <div class="pull-right movetocart-sec">
                                    <div class="clearfix">
                                        <a class="btn btn-primary btn-simple" href="javascript:;">Move To Cart</a>
                                    </div>
                                    <div class="text-right">
                                        <a href="javascript:;" class="remove-item-red">Remove Item</a>
                                    </div>
                                </div>
                            </h3>
                            <div class="by-herbs">
                                <span>By:Herbal Arts</span>
                            </div>
                            <div class="price-usd">
                                <span>Z : <span class="blue-text">$50</span></span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row save-later-repeater">
                    <div class="col-sm-3">
                        <div class="image-container-save">
                            <img src="https://placeimg.com/190/120/people" class="img-fluid">
                        </div>
                    </div>
                    <div class="col-sm-9">
                        <div class="save-container-body">
                            <h3> Up to 50% off Highly-Rated Italian <br>Cuisine
                                <div class="pull-right movetocart-sec">
                                    <div class="clearfix">
                                        <a class="btn btn-primary btn-simple" href="javascript:;">Move To Cart</a>
                                    </div>
                                    <div class="text-right">
                                        <a href="javascript:;" class="remove-item-red">Remove Item</a>
                                    </div>
                                </div>
                            </h3>
                            <div class="by-herbs">
                                <span>By:Herbal Arts</span>
                            </div>
                            <div class="price-usd">
                                <span>Z : <span class="blue-text">$50</span></span>
                            </div>
                        </div>
                    </div>
                </div>



            </div>
        </div> saveforlater -->



        <div class="uploadImages">
            <span></span>
        </div> 

    </div>
</main>
<script>
    $(document).ready(function () {
        $(".remove-item").on("click", function () {
            var totalProductlenght = 0;
            var result = confirm(" Are you sure want to remove this product from cart.?");
            $(".uploadImages").show();
            if (!result) {
                $(".uploadImages").hide();
                return false;
            }
            var currentProductContainer = $(this).closest(".product-cart-list");
            var currentContainer = $(this).closest(".table-container");
            calculationContainer = currentContainer.find('.section-head');
            currenSidebar = currentContainer.find('.total-section');
            data = {};
            data['cart_id'] = $(this).attr('data-cart-id');
            storeId = $(this).attr('data-store-id');
            sendreq(data, 'cart', 'delete', 'delproducts');
            var cartproducts = $(this).closest(".leftlarge-cart");
            cartproducts.find('.product-cart-list').each(function () {
                totalProductlenght = totalProductlenght + $(this).length;
            });
            currentProductContainer.remove();
            if (totalProductlenght <= 1) {
                currenSidebar.remove();
            }
            setTimeout(function () {
                $(".uploadImages").hide();
            }, 1000);
        });
        $(document).on("click", '.remove-bask', function () {
            var productId = $(this).attr('data-product-id');
            storeId = $(this).attr('data-store-id');
            storeId = $(this).attr('data-store-id');
            var html = $(this).closest('.cartitm');
            var totalProductlenght = 0;
            removeClass = ".product_" + productId + "";
            cartproducts = $(removeClass).closest(".table-container");
            side = cartproducts.find('.total-section');
            calculationContainer = cartproducts.find('.section-head');
            data = {};
            data['cart_id'] = $(this).attr('data-cart-id');
            sendreq(data, 'cart', 'delete', 'delproducts');
            cartproducts.find('.product-cart-list').each(function () {
                totalProductlenght = totalProductlenght + $(this).length;
            });
            if (totalProductlenght <= 1) {
                side.remove();
            }
            $(removeClass).remove();
            html.remove();
        });
        $(".productquantity").on("change", function () {
            $(".uploadImages").show();
            var currentPriceContainer = $(this).closest('.pro-price');
            CurrentPriceValue = currentPriceContainer.find('.product-amount');
            var currentContainer = $(this).closest(".table-container");
            calculationContainer = currentContainer.find('.section-head');
            currenSidebar = currentContainer.find('.total-section');
            product_quantity = $(this).val();
            cartId = $(this).attr('data-cart-id');
            storeId = $(this).attr('data-store-id');
            variationId = $(this).attr('data-variation-id');
            data = {};
            if (variationId != "") {
                data['variation_id'] = variationId;
            }
            data['product_quantity'] = product_quantity;
            data['cart_id'] = cartId;
            sendreq(data, 'cart', 'update', 'callBackupdatecart');
//            updateBasket();
            setTimeout(function () {
                $(".uploadImages").hide();
            }, 1000);
        });
    });
    $(".checkout-item").on("click", function () {
        var ajaxsend = false;
        var myArray = [];
        var productArray = [];
        data = {};
        var currentContainer = $(this).closest(".table-container");
        currentContainer.find('input[name=here]').each(function () {
            noteValue = $(this).val();
            if (noteValue.length > 0) {
                myArray.push($(this).val());
                productArray.push($(this).attr('data-product-id'));
                ajaxsend = true;
            }

        });
        if (ajaxsend == true) {
            data['product_id'] = productArray;
            data['brand_note'] = myArray;
            req(data, 'cart', 'addnote', 'callBackAddnotes');
        }
    });
    function req(data, controller, action, functiontocall) {
//          $(".uploadImages").show();
        var sitebaseurl = $('#site_url').val();
        $.ajax({
            url: sitebaseurl + '/' + controller + '/' + action,
            type: 'POST',
            async: false,
            data: data,
            //dataType: 'JSON',
            success: function (data) {
//           $(".uploadImages").show();
                eval(functiontocall)(data);
            },
            beforeSend: function () {

            }
        });
    }
    function callBackupdatecart(data) {
        CurrentPriceValue.html(data);
        res = {};
        res['store_id'] = storeId;
        sendreq(res, 'cart', 'calculate', 'calculateproducts');
    }
    function callBackAddnotes(data) {
        if (data == 'success') {
        }
    }
    function calculateproducts(data) {
        calculationContainer.html(data);
        checkCounter();
    }
    function checkCounter() {
        var res = {};
        res['cuneter'] = 1;
        sendreq(res, 'cart', 'counter', 'callBackBasketCounter');
    }
    function callBackBasketCounter(res) {
        $("#cart_counter").html(res);
        if (res == 0) {
            $(".empty-basket").show();
            $(".cart-container").addClass("remove");
        }
        $('.basket-count').html("Your Basket (" + res + " items)");
//        $("html, body").animate({scrollTop: 0}, "slow");
        updateBasket();
    }
    function updateBasket() {
        res['cuneter'] = 1;
        sendreq(res, 'cart', 'updatebasket', 'callBackupdatebasket');
    }
    function delproducts() {
        res = {};
        res['store_id'] = storeId;
        sendreq(res, 'cart', 'calculate', 'calculateproducts');
    }
    function callBackupdatebasket(data) {
        $(".cartmenu").html(data);
    }
    $(".add-notes-to-vendor input").on('input', function () {
        if ($(this).val().length >= 150) {
            $(this).next('span.error').show();
        } else {
            $(this).next('span.error').hide();
        }
    });

</script>
<!-- Content Ends-->
@endsection