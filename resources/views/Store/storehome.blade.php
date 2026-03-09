@extends('layouts.new-main')
@section('content')
@section('title', 'Brand')
@section('google-analytics')
@include('layouts.google-analytics')
@stop
<main>
    <div class="strhd">
        <div class="container-fluid">
            <div class="row">
                <div class="col hdlft">
                    <span>
                        <?php
                        if (count($totalRating) > 0) {
                            for ($i = 1; $i <= 5; $i++) {
                                if ($i <= $totalRating['product_rating']) {
                                    $class = "fa fa-star";
                                } else {
                                    $class = "fa fa-star-o";
                                }
                                ?>
                                <i class = "<?php echo $class; ?>" aria-hidden = "true"></i>
                                <?php
                            }
                        } else {
                            ?>

                            <i class="fa fa-star-o" aria-hidden="true"></i>
                            <i class="fa fa-star-o" aria-hidden="true"></i>
                            <i class="fa fa-star-o" aria-hidden="true"></i>
                            <i class="fa fa-star-o" aria-hidden="true"></i>
                            <i class="fa fa-star-o" aria-hidden="true"></i>
                        <?php } ?>
                        <b><?php echo $totalRating['review_count']; ?></b> Rating<?php
                        if ($totalRating['review_count'] > 0) {
                            echo 's';
                        } else {
                            echo '';
                        }
                        ?></span>
                    <br>
                    <span class="small"><?php
                        if ($storeSales > 1) {
                            echo $storeSales . " Sales";
                        } else {
                            echo $storeSales . " Sale";
                        }
                        ?>     &nbsp;| &nbsp;   Since <?php
                        $timestamp = strtotime($storeData['created_at']);
                        echo $date = date('Y', $timestamp);
                        ?>    &nbsp;| &nbsp;   <?php echo $totalProducts; ?> Items</span>

                </div>
                <div class="col strlgo hdcnt">
                    <?php
                    if (!empty($storeData['store_logo'])) {
                        $imageUrl = getAttachmentUrlById((int) $storeData['store_logo'], 'small');
                        ?>
                        <a href="<?php echo URL::to('/brand') . "/" . $storeId; ?>">
                            <img src="<?php echo $imageUrl; ?>" alt="<?php echo $storeData['shop_name']; ?>" title="<?php echo $storeData['shop_name']; ?>"></a>
                    <?php } else { ?>
                        <h3><?php echo $storeData['shop_name']; ?></h3>
                    <?php } ?>
                </div>      
                <div class="col hdrght">
                    <button class="btn btn-primary btn-simple">Follow Us</button>
                    &nbsp; &nbsp;
                    <button class="btn btn-primary hide-div">Become Ambassador</button> 
                </div>      
            </div>
        </div>

    </div>

    <!-- Store Header Ends-->   

    <!-- Store Features Starts -->

    <div class="strftr">
        <div class="container-fluide">
            <div class="row">
                <div class="col-md-2">
                    <div class="card">
                        <a class="fvrt" href="#"><span><i class="fa fa-heart-o" aria-hidden="true"></i></span></a> 
                        <a href="#"><img src="{{ asset('assets/images/prdct.png',env('HTTPS_STATUS')) }}" alt="product"  title="product"style="width:100%"></a>
                        <div class="card-body">
                            <h5 class="card-title"><a href="#">1.09 Ct Natural Loose Diamond Slice Shape Blue Color 8 Pcs L8972</a></h5> 
                            <div class="infbx small  justify-content-between">
                                <div>
                                    <span>Supporters: <b>4356</b></span>
                                    &nbsp; | &nbsp;
                                    <span>Price: <b>$235</b></span> 
                                </div> 

                            </div>  
                        </div>
                    </div>
                </div>


            </div>           
        </div>   
    </div>      

    <!-- Store Features Ends -->  

    <!-- Store Nav Starts -->
    @include('Store.storehomesidebar')
    @include('Store.store-form')
    <!-- Store Nav Ends -->

    <!-- Most Popular Starts -->
    <div class="container-fluide rslts">
        <div class="row">
            <?php
            if (count($result) > 0) {
                ?>
                <div class="col-md-12"><h3>All Categories ({{$totalresults}} Results)</h3></div>
                <div class="storehome">
                    <div class="search-results row">
                        <?php
                        $currentUserId = getCurrentUserId();
                        $favProductId = getBuyerFavProducts();
                        foreach ($result as $key => $val) {
                            if (!empty($favProductId)) {

                                $res = array_search($val->product_id, array_column($favProductId, 'product_id'));
                            }
                            $image = $val->feature_Image;
                            $image_url = getAttachmentUrlById($val->feature_Image, 'product');
                            if (!isImageExists($image_url)) {
                                $image_url = url(asset('assets/images/placeholder.jpg', env('HTTPS_STATUS')));
                            }
                            ?>

                            <div class="col-xl-3  col-lg-4 col-md-6 col-sm-6 ">
                                <!-- Product Box -->
                                <div class="card">
                                    <?php
                                    if (Session::has('userData')) {
                                        if (!empty($res)) {
                                            $fill = "fillheart";
                                        } else {
                                            $fill = "";
                                        }
                                        ?>
                                        <a class="fvrt fvrate-icon <?php echo $fill; ?>" href="javascript:void(0)" data-product-id="{{$val->product_id}}">
                                            <span>
                                                <i class="fa fa-heart-o" aria-hidden="true"></i>
                                                <i class="fa fa-heart hide-me" aria-hidden="true"></i>
                                            </span>

                                        </a> 
                                    <?php } else { ?>
                                        <a class="fav-login login-popup" data-location="{{URL::to('search-results/')}}" href="javascript:void(0)" data-toggle="modal" data-target="#login-registerpopup"  >
                                            <span>
                                                <i class="fa fa-heart-o" aria-hidden="true"></i>
                                            </span>

                                        </a> 
                                    <?php } ?>
                                    <?php ?>
                                    <a class="image-container-mbt" href="/product/<?php echo $val->slug; ?>"><img src="<?php echo $image_url; ?>"  alt="{{$val->slug}}" alt="{{$val->slug}}" style="width:100%"></a>
                                    <div class="card-body" data-stock="<?php //echo $val->current_stock; ?>">
                                        <h5  class="card-title"><a href="/product/<?php echo $val->slug; ?>"><?php
                                                echo $val->title;
                                                ?></a></h5>

                                        <!-- Cart and Price Starts -->
                                        <div class="cartsec">
                                            <!--                                show-quantitiy-panel noman-->
                                            <?php
                                            $variableproducts = getVariationProducts($val->product_id);
                                            $key2 = 0;
                                            if (isset($cartProducts) && array_key_exists($val->product_id, $cartProducts)) {
                                                if ($variableproducts > 0) {
                                                    $class = "";
                                                } else {
                                                    $class = "show-quantitiy-panel";
                                                }
                                                $key2 = $cartProducts[$val->product_id];
                                            } else {
                                                $class = "";
                                            }
                                            ?>
                                            <div class="crtbtn col  buttons-group-cnt <?php echo $class; ?>">
                                                <?php
                                                $cartdisable = '';
                                                if ($currentUserId == $val->user_id) {
                                                    $cartdisable = 'disabled';
                                                }
                                                if ($variableproducts > 0) {
                                                    ?>
                                                    <a  href="/product/<?php echo $val->slug; ?>" class="btn btn-primary btnorm <?php echo $cartdisable; ?>">Add To Cart</a>
                                                <?php } else { ?>
                                                    <button type="button" data-product-id="{{$val->product_id }}" data-store-id="{{$val->store_id }}" class="btn btn-primary btnorm cartadd  <?php echo $cartdisable; ?>">Add To Cart</button>
                                                <?php } ?>
                                                <div class="input-group quantity-number-option">
                                                    <span class="minus-icon first-icon-counter" data-pro-id="{{$val->product_id}}" data-stor-id="{{$val->store_id}}">
                                                        <i class="fa fa-minus" aria-hidden="true"></i>
                                                    </span>
                                                    <span class="input-counter">

                                                        <input type="text" name="cart_quantity" id="cart_quantity"  class="form-control input-number" placeholder="in cart" value="<?php
                                                        if (!empty($key2)) {
                                                            echo $key2;
                                                        } else {
                                                            echo 1;
                                                        }
                                                        ?>" min="1" max="50" readonly>
                                                        <!-- <span class="static-text"> in cart</span> -->
                                                    </span>
                                                    <?php
                                                    if ($key2 == $val->current_stock) {
                                                        $disabled = 'disabled';
                                                    } else {
                                                        $disabled = '';
                                                    }
                                                    ?>
                                                    <span class="plus-icon second-icon-counter <?php echo $disabled; ?>" data-pro-id="{{$val->product_id}}" data-stor-id="{{$val->store_id}}"><i class="fa fa-plus" aria-hidden="true"></i></span>            
                                                </div>
                                            </div>
                                            <div class="prc col text-right"><span class="throg small">@if(property_exists($val,'salePrice') && $val->salePrice!='') ${{$val->salePrice}} @endif</span><br><span>@if( property_exists($val,'price') && $val->price!='') ${{$val->price}} @endif</span></div>
                                        </div>  
                                        <!-- Cart and Price Ends -->

                                        <!--progress bar -->
                                        <div class="progress"><div class="progress-bar" style="width:70%"></div></div>
                                        <!-- Progress bar ends -->
                                        <div class="infbx small  justify-content-between">
                                            <div>
                                                <?php
                                                $productId = getSupporters($val->product_id);
                                                ?>
                                                <span>Supporters: <b><?php echo count($productId); ?></b></span><br>
                                                <span>Gave Back: <b>$78896</b></span><br>
                                                <span>Pledges Needed: <b><?php echo rand(100, 400); ?></b></span> 
                                            </div> 

                                            <div class="text-right">
                                                <?php
                                                $rating = getProductRatingById($val->product_id);
                                                if (count($rating) > 0) {
                                                    ?>
                                                    <span>
                                                        <div class = "rating-strip">
                                                            <?php
                                                            // die();
                                                            for ($i = 1; $i <= 5; $i++) {
                                                                if ($i <= $rating['average']) {
                                                                    $class = "fa fa-star";
                                                                } else {
                                                                    $class = "fa fa-star-o";
                                                                }
                                                                ?>
                                                                <i class = "<?php echo $class; ?>" aria-hidden = "true"></i>
                                                            <?php } ?>

                                                        </div>

                                                        <b>{{$rating['total']}}</b> Ratings</span>
                                                    <?php
                                                } else {
                                                    ?>
                                                    <span>
                                                        <div class = "rating-strip">
                                                            <i class = "fa fa-star-o" aria-hidden = "true"></i>
                                                            <i class = "fa fa-star-o" aria-hidden = "true"></i>
                                                            <i class = "fa fa-star-o" aria-hidden = "true"></i>
                                                            <i class = "fa fa-star-o" aria-hidden = "true"></i>
                                                            <i class = "fa fa-star-o" aria-hidden = "true"></i>
                                                        </div>
                                                        <b>0</b> Ratings</span>
                                                    <?php
                                                }
                                                ?>
                                                <br>
                                                <?php
                                                $author = getStoreDataByUserId($val->user_id);
                                                $authorName = '';
                                                if (count($author) > 0) {
                                                    $authorName = $author->shop_name;
                                                }
                                                ?>
                                                <span>By: {{$authorName}}</span> 
                                            </div>
                                        </div>  
                                    </div>
                                </div>

                                <!-- Product Box Ends -->
                            </div> 
                            <?php
                        }
                        ?>
                    </div>
                    <div class="container "> 
                        <div class="row">
                            <div class="col-md-12 text-center" ><p>
                                    <button class="btn  btn-primary btn-simple loadmore" data-store-id="<?php echo $storeId; ?>" data-page="<?php echo 2; ?>">Load More</button>
                                </p>
                            </div> 
                        </div>
                        <!-- Loading Button Ends -->   
                    </div>
                    <?php
                } else {
                    echo '<h2> No records found</h2>';
                }
                ?>
            </div></div>
        <!-- Most Popular Ends -->
    </div>
</main>
<script>
    $(document).ready(function () {
        jQuery('.uploadImages').hide();
    });
    $(document).on("click", '.cartadd', function () {
        $(this).parent().addClass("show-quantitiy-panel");
        currentBody = $(this).closest('.card-body');
        currentStock = currentBody.attr('data-stock');
        quantity = currentBody.find('.crtbtn').find("input[type=text]").val();
        //jQuery('.uploadImages').show();
        var data = {};
        data['product_id'] = $(this).attr('data-product-id');
        data['store_id'] = $(this).attr('data-store-id');
        data['product_quantity'] = 1;
        sendreq(data, 'cart', 'add', 'callbackBasket');
        if (currentStock == quantity) {
            currentBody.find(".plus-icon").addClass('disabled');
        }
    });
    $(document).on("click", ".fvrt", function () {
        current = $(this);
        var data = {};
        data['product_id'] = $(this).attr('data-product-id');
        sendreq(data, 'favourite', 'add', 'callbackfavourites');
    });
    $(document).on("click", ".plus-icon", function () {

        var currentStock = $(this).closest('.card-body').attr('data-stock');
        var plusVal = $(this).prev(".input-counter").find("input[type=text]").val();
        plusVal++;
        if (currentStock >= plusVal) {
            var data = {};
            data['product_id'] = $(this).attr('data-pro-id');
            data['store_id'] = $(this).attr('data-stor-id');
            data['product_quantity_plus'] = plusVal;
            sendreq(data, 'cart', 'add', 'callbackBasket');
            $(this).prev(".input-counter").find("input[type=text]").val(plusVal);
            if (currentStock == plusVal) {
                $(this).addClass('disabled');
            }
        } else {
            $(this).addClass('disabled');
        }
    });
    $(document).on("click", '.minus-icon', function () {
        var minusVal = $(this).next(".input-counter").find("input[type=text]").val();
        if (minusVal > 1) {
            minusVal--;
            var data = {};
            data['product_id'] = $(this).attr('data-pro-id');
            data['store_id'] = $(this).attr('data-stor-id');
            data['product_quantity_minus'] = minusVal;
            sendreq(data, 'cart', 'add', 'callbackBasket');
            $(this).next(".input-counter").find("input[type=text]").val(minusVal);
            $(this).closest(".card-body").find(".plus-icon").removeClass('disabled');

        } else {
            $(this).closest('.show-quantitiy-panel').removeClass('show-quantitiy-panel');
            $(this).closest(".card-body").find(".plus-icon").removeClass('disabled');
            $(this).next(".input-counter").find("input[type=text]").val(1);
            var data = {};
            data['product_id'] = $(this).attr('data-pro-id');
            sendreq(data, 'cart', 'deletebasket', 'callBackDelBasket');

        }
    });

    function callBackDelBasket(data) {
        $(".cartmenu").html(data);
    }
    function callbackBasket(data) {
        $(".cartmenu").html(data);
        // $("html, body").animate({scrollTop: 0}, "slow");
        jQuery('.uploadImages').hide();
    }
    function callbackfavourites(data) {
        current.toggleClass('fillheart');

    }

</script>
<script>
    var counter = 2;
    var perPage = '<?php echo $page; ?>';
    $(document).ready(function () {
        if (counter > perPage) {
            $(".loadmore").hide()

        }
        $(".loadmore").on("click", function () {
            var res = getParams(window.location.href);
            res['store_id'] = $(this).attr('data-store-id');
            res['page'] = $(this).attr('data-page');
            sendreq(res, 'brand', 'search', 'callbacksearchresult');
        });
    });
    var getParams = function (url) {
        var params = {};
        var parser = document.createElement('a');
        parser.href = url;
        var query = parser.search.substring(1);
        var vars = query.split('&');
        for (var i = 0; i < vars.length; i++) {
            var pair = vars[i].split('=');
            params[pair[0]] = decodeURIComponent(pair[1]);
        }
        return params;
    };
    function callbacksearchresult(data) {
        $(".search-results").append(data);
        counter++;
        $(".loadmore").attr('data-page', counter);
        if (counter > perPage) {
            $(".loadmore").hide();

        }
    }
</script>
@endsection