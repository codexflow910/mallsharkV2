<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.carousel.css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.theme.default.css" />

<style>
    .treding-cart{
        display: flex;
    }
    .tredig-prcbxint{
        margin-top: 10px;
    }
    .trending-slider button.owl-prev{
        position: absolute ;
        left: -20px ;
        top: 34% ;
        
    }
    .category-slider button.owl-prev{
        position: absolute ;
        left: -20px ;
        top: 22%;
    }
    .owl-carousel .owl-nav.disabled{
        display: block!important ;
    }
    .owl-carousel .owl-nav button.owl-prev,.owl-carousel .owl-nav button.owl-next{
        background-color: #bfbfbfa1;
        color: #fff;
        border-radius: 13px;
        }
        .owl-carousel .owl-nav button.owl-prev:hover,.owl-carousel .owl-nav button.owl-next:hover{
            background-color: #7f7758a8!important;
            color: #fff!important;
        }
    button:focus{
        outline: none !important;
    }
    button.owl-prev span,button.owl-next span{ 
        font-size: 35px;
        font-weight: 100;
        /* padding: 10px; */
        /* display: none; */
    }
    .trending-slider:hover  button.owl-prev span, .trending-slider:hover button.owl-next span{
      display: block;
    }
    .category-slider:hover  button.owl-prev span, .category-slider:hover button.owl-next span{
      display: block;
    }
    button.owl-prev span:focus-visible,button.owl-next span:focus-visible{
        outline: 0px !important;
    border: none !important;
    }
    .owl-theme .owl-nav [class*='owl-']:hover {
        background: none !important;
        color: #000 !important;
    }
   .category-slider button.owl-next{    
        position: absolute ;
        right: -20px ;
        top: 22% ;
    }
   .trending-slider button.owl-next{    
        position: absolute ;
        right: -20px ;
        top: 34% ;
    }
    .product-thumbnail-slider{
        height: 226px;
        object-fit: cover;
    }
    .category-slider .product-thumbnail-slider{
        height: 182px;
        object-fit: cover;
    }
    .trending-slider{ 
        margin-top: 40px;
    background: #f0f0f0;
    padding: 25px 35px;
    margin-left: -35px;
    margin-right: -35px;
    margin: auto;
    border-radius: 32px;
    padding-bottom: 0;
    margin-bottom: 45px;
    }
    .category-slider{ 
    margin-top: 40px;
    padding: 25px 35px;
    margin-left: -35px;
    margin-right: -35px;
    margin: auto;
    border-radius: 32px;
    padding-bottom: 0;
    margin-bottom: 45px;
    }
    .lg-2-img-banner a:hover{
        color:#000;
    }
   .browseCategories .card .card-body .card-title{
        text-align: center;    margin-bottom: 0;
    }
    .browseCategories .card-title a {
        font-weight: 500;font-size: 12px;
    }
    .browseCategories .card-body {
        padding-top: 0px;padding-bottom: 0;
    }
.container.category-slider .owl-nav{
    margin-top: 0px !important;
 }

    @media screen and (max-width:767px){
        button.owl-prev{
        position: absolute ;
        left: -20px ;
        top: 30% ;
        
    }
    button.owl-next{
        position: absolute ;
        right: -20px ;
        top: 30% ;
    }
    .product-thumbnail-slider{
        height: 185px;
    }
    .trending-slider{ 
    border-radius: 0px;
    padding: 25px 10px;
    }
    .owl-carousel.owl-drag .owl-item {
        padding: 5px !important;
    }
    }
  
</style>
@if(count($trendingProducts) > 0)
<div class="container trending-slider optionONe" style="margin-top:40px;max-width:1180px!important;display:none;">
    <div class="row">
        <div class="col-md-12">
            <h3 class="trendingProductsHeading">Trending Products</h3>

            <div class="owl-carousel owl-theme">
                <?php
                
                $class = '';
                $opecity = '';
                ?>
                
                @foreach ($trendingProducts as $rProduct)
                @php

                if ($rProduct->product_type == 'simple') {
                $current_stock = $rProduct->current_stock;
                if ($current_stock > 0) {
                $class = 'current-stock-active';
                $opecity = '';

                }else{
                $class = 'current-stock-deactive';
                $opecity = 'opecity-deactive';
                }
                }

                // dd($rProduct->store);
                $image_url = getAttachmentUrlById((int) $rProduct->feature_image, 'medium');
                if (!isImageExists($image_url)) {
                $image_url = url(asset('assets/images/placeholder.jpg', env('HTTPS_STATUS')));
                }
                $priceExplode = explode('.',$rProduct->price);
                $prePrice = isset($priceExplode[0]) ? $priceExplode[0] : '';
                $postPrice = isset($priceExplode[1]) ? ('.'.$priceExplode[1]) : '';
                    if ($rProduct->on_sale == 1){
                        $salePriceExplode = explode('.',$rProduct->sale_price);
                        $preSalePrice = isset($salePriceExplode[0]) ? $salePriceExplode[0] : '';
                        $postSalePrice = isset($salePriceExplode[1]) ? ('.'.$salePriceExplode[1]) : '';
                    }
                @endphp

                <div class="item">
                    <div class="card">
                        <a href="{{  URL::to('/product/' . $rProduct->slug) }}"><img src="{{ $image_url }}" class="card-img-top product-thumbnail-slider" style="height: 200px;" alt="{{ $rProduct->title }}" title="{{ $rProduct->title }}" ></a>
                        <div class="card-body">
                            <h4 class="card-title"><a href="{{  URL::to('/product/' . $rProduct->slug) }}"> {{ $rProduct->title }}</a></h4>
                            <div>
                                <div class="prcbxint tredig-prcbxint">
                                    <div>
                                        <p class="card-text prc">
                                            @if($rProduct->on_sale == 1)
                                                <span class="n-price red-color "><span class="z-symbl">$</span>{{ $preSalePrice}}<span
                                                            class="deci">{{ $postSalePrice}}</span></span>
                                                <del class="sale-price"><del class="z-symbl">$</del>{{ $prePrice}}<del
                                                            class="deci">{{ $postPrice}}</del></del>
                                                            
                                            @else
                                                <span class="n-price"><span class="z-symbl">$</span>{{ $prePrice}}<span
                                                            class="deci">{{ $postPrice}}</span></span>
                                            @endif
                                            <span class="n-stars  push-right" style="display:none">
                                                <span><img style="width:16px; position:relative; top:-2px" src="{{ URL::asset('assets/images/rate.svg')}}" alt="rate"> @php echo productTotalRatings($rProduct->product_id) @endphp.0</span>
                                            </span>
                                        </p>
                                    </div>

                                    <div class="card-text small ">

                                        <div class="splopt treding-cart">

                                            <span class="product_parent_category" data-category="{{$rProduct->categoryTitle}}" ></span>
                                            <span class="product_title" data-title="{{$rProduct->title}}"></span>
                                            <span class="product_store" data-store="{{$rProduct->store_name}}"></span>
                                            <span class="product_price" data-product-price="{{$rProduct->price}}"></span>
                                            <span class="p_id" data-product-id="{{$rProduct->product_id}}"></span>
                                            <span class="product_stock" data-stock="{{$rProduct->current_stock}}"></span>

                                            <?php
                                            $fill = "";
                                            if (Session::has('userData')) {

                                                $favProductId = getBuyerFavProducts();
                                                if (!empty($favProductId) && in_array($rProduct->product_id, $favProductId)) {
                                                    $fill =  "fillheart";
                                                } ?>
                                                <a data-product-id="<?php echo $rProduct->product_id; ?>" class="adfv adtofv-home <?php echo $fill; ?>" href="javascript:void(0);"><img class="image-add_tofav" style="width:30px; position:relative; top:-7px" src="{{ URL::asset('assets/images/heart-v2.svg')}}" alt="rate">
                                                <?php  } else {
                                                $currenturl = url()->full();
                                                ?>
                                                    <a class=" adfv fav-login login-popup" data-location="{{$currenturl}}" href="javascript:void(0)" data-toggle="modal" data-target="#login-registerpopup">
                                                        <img class="image-add_tofav" data-product-id="<?php echo $rProduct->product_id; ?>" style="width:30px; position:relative; top:-7px" src="{{ URL::asset('assets/images/heart-v2.svg')}}" alt="rate">

                                                    </a>
                                                <?php   } ?>

                                                <?php
                                                $userId = isset(Session::get('userData')['user_id']) ? Session::get('userData')['user_id'] : '';
                                                if (Session::has('userData') && $userId == $rProduct->user_id) { ?>
                                                    <a class="notbuy" href="javascript:void(0)"><img style="width:30px; position:relative; top:-7px" src="{{ URL::asset('assets/images/basket-v2.svg')}}" alt="rate"> </a>
                                                <?php  } else if ($rProduct->product_type == 'variant') { ?>
                                                    <a class="" data-type="{{$rProduct->product_type }}" href="{{  URL::to('/product/' . $rProduct->slug) }}"><img style="width:30px; position:relative; top:-7px" src="{{ URL::asset('assets/images/basket-v2.svg')}}" alt="rate"> </a>
                                                <?php  } else { ?>
                                                    <a class="cartadd-home {{$class}} " data-type="{{$rProduct->product_type }}" data-stock="{{$rProduct->current_stock}}" data-store-id="<?php echo $rProduct->store_id; ?>" data-product-id="<?php echo $rProduct->product_id; ?>" href="javascript:void(0)"><img class="{{$opecity}}" style="width:30px; position:relative; top:-7px" src="{{ URL::asset('assets/images/basket-v2.svg')}}" alt="rate"> </a>
                                                <?php   }  ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                @endforeach
            </div>
        </div>
    </div>
</div>

<div class="container category-slider" style="margin-top:30px">
    <div class="row">
        <div class="col-md-12">
            <h3 class="page-heading">Browse Categories</h3>

            {{-- 
<div class="owl-carousel owl-theme">
    @foreach ($categories as $category)
    <?php
    // $category_image = '';
    // if(!empty($category['image_id'])){
    //     $category_image = getAttachmentUrlById( (int)$category['image_id'], 'medium'); 
    // }else{
    //     $category_image = 'https://cdn.pixabay.com/photo/2021/01/11/09/42/maximize-5907715_1280.png'; 
    // }
    ?>

    <div class="item categoryCard">
        <div class="card">
            <a href="{{route('category.landing',['slug' => $category['category_slug']]) }}">
                <img src="{{$category_image}}" class="card-img-top product-thumbnail-slider" 
                style="border-radius:100%;width:100%;max-height:100%;object-fit:cover;" 
                alt="{{ $rProduct->title }}" title="{{ $rProduct->title }}">
            </a>
            <div class="card-body">
                <div class="card-title">
                    <a href="{{route('category.landing',['slug' => $category['category_slug']]) }}">
                        {{$category['categoryTitle']}}
                    </a>
                </div>
            </div>
        </div>
    </div>

    @endforeach
</div>
--}}


            <div class="owl-carousel owl-theme">
                <?php
                $llCategoreis = getCategoriesHome();
                $query_category_lin = 'category.landing';
                ?>

                @foreach ($llCategoreis as $val)
                    <?php
                    $category = $val['parent_category'];
                    $category_image = '';
                    if (!empty($category['image'])) {
                        $category_image = getAttachmentUrlById((int) $category['image'], 'medium');
                    } else {
                        $category_image = 'https://cdn.pixabay.com/photo/2021/01/11/09/42/maximize-5907715_1280.png';
                    }
                    ?>

                    <div class="item categoryCard">
                        <div class="card">
                            <a href="{{ route($query_category_lin, ['slug' => $category['slug']]) }}">
                                <img src="{{ $category_image }}" class="card-img-top product-thumbnail-slider" style="border-radius:100%;width:100%;max-height:100%;object-fit:cover;" alt="{{ $category['title'] }}" title="{{ $category['title'] }}">
                            </a>
                            <div class="card-body">
                                <div class="card-title">
                                    <a href="{{ route($query_category_lin, ['slug' => $category['slug']]) }}">{{ $category['title'] }}</a>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach

            </div>
        </div>
    </div>
</div>
@endif

<script src="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/owl.carousel.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/owl.carousel.js"></script>

<script>
    $('.owl-carousel').owlCarousel({
    items:4,
    loop:true,
    nav:true,
    dots:false,
    margin:10,
    autoplay:true,
    autoplayTimeout:3000,
    smartSpeed: 1000,
    autoplayHoverPause:true,
        responsive: {
            0: {
                items: 2
            },
            600: {
                items: 6
            },
            1000: {
                items: 8
            }
        }
    })
</script>