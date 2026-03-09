<div class="container" style="margin-top:10px">
    <div class="row">
        <div class="col-md-12">
            <h3 class="page-heading">Recommended For You</h3>

            <div class="view-al place-into-tap" style="top:0px;">
                <a href="{{  URL::to('/search?keyword=')  }}" class="viewalllink">Show All</a>
            </div>
        </div>
    </div>
    <div class="row">
        <?php
        $class = '';
        $opecity = '';
        // echo 'Data: <pre>' .print_r($recommendedProducts,true). '</pre>'; die();
        ?>
        @if(count($recommendedProducts) > 0)
            @foreach ($recommendedProducts as $rProduct)
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

                <div class="col-sm-2 col-md-2 col-6 ">
                    <!-- card starts -->
                    <div class="card">


                        <a href="{{  URL::to('/product/' . $rProduct->slug) }}"><img src="{{ $image_url }}"
                                                                                     class="card-img-top product-thumbnail"
                                                                                     alt="{{ $rProduct->title }}"
                                                                                     title="{{ $rProduct->title }}">
                                                                                     <!-- <span class="blur-effect" style="background-image: url('{{$image_url}}'); filter:blur(30px)"></span> -->
                     
                                                                                    </a>   
                        <div class="card-body">
                        <div style="min-height: 143px;">
                            <div class="mb-1 discount-container">
                                 @if($rProduct->on_sale == 1)
                                    @if($rProduct->discount_percentage > 0)
                                        <span class="z-symbl product-price discountbadge" style="position:relative!important;top:0px!important;font-weight:600;">
                                            -{{$rProduct->discount_percentage}}% off
                                        </span><b class="discountbadge-text">Limited time offer</b>
                                        @else
                                        <span style="display: inline-block;margin-bottom: 8px;font-size: 18px;font-weight: 600;position: relative!important;top:0px!important; z-in" class="z-symbl product-price discountbadge" >
                                            {{$rProduct->discount_percentage}}% off
                                        </span><b class="discountbadge-text">Limited time offer</b>
                                    @endif
                                @endif
                            </div>
                            <?php
                            // Flag to check if child category is displayed
                            $categoryDisplayed = false;
                        
                            foreach ($rProduct['categories'] as $cat) {
                                // If a child category exists, display it and stop
                                if ($cat->parentCategoryID != 0) {
                                    echo '<span class="product_child_category card-category" data-category="' . $cat->categoryTitle . '"></span>';
                                    echo '<span class="catnm catnm1 card-category">' . $cat->categoryTitle . '</span>';
                                    $categoryDisplayed = true;
                                    break;
                                }
                            }
                        
                            // If no child category is displayed, show the parent category
                            if (!$categoryDisplayed) {
                                foreach ($rProduct['categories'] as $cat) {
                                    if ($cat->parentCategoryID == 0) {
                                        echo '<span class="product_parent_category card-category" data-category="' . $cat->categoryTitle . '"></span>';
                                        echo '<span class="catnm card-category">' . $cat->categoryTitle . '&nbsp;</span>';
                                        break;
                                    }
                                }
                            }
                        ?>
                        

                            
                            <h4 class="card-title"><a
                                        href="{{  URL::to('/product/' . $rProduct->slug) }}"> {{ $rProduct->title }}</a>
                            </h4>
                        </div>
                            <div>

                                <div class="prcbxint">
                                    <div><p class="card-text prc">
                                            @if($rProduct->on_sale == 1)
                                                <span class="n-price red-color"><span class="z-symbl">$</span>{{ $preSalePrice}}<span
                                                            class="deci">{{ $postSalePrice}}</span></span>
                                                <del class="sale-price "><del class="z-symbl">$</del>{{ $prePrice}}<del
                                                            class="deci">{{ $postPrice}}</del></del>
                                                            <!-- @if($rProduct->discount_percentage > 0)
                                                            <span class="n-price mybadge">- {{ $rProduct->discount_percentage }}%</span>
                                                            @else
                                                            <span class="n-price mybadge">{{ $rProduct->discount_percentage }}%</span>
                                                            @endif -->
                                            @else
                                                <span class="n-price"><span class="z-symbl">$</span>{{ $prePrice}}<span
                                                            class="deci">{{ $postPrice}}</span></span>
                                            @endif
                                            <span class="n-stars  push-right" style="display:none">
                            <span><img style="width:16px; position:relative; top:-2px"
                                       src="{{ URL::asset('assets/images/rate.svg')}}"
                                       alt="rate"> @php echo productTotalRatings($rProduct->product_id) @endphp.0</span>
                            </span>
                                        </p>
                                    </div>

                                    <div class="card-text small ">

                                        <div class="splopt">
                                                <?php foreach ($rProduct['categories'] as $key => $cat) {
                                            if ($cat->parentCategoryID == 0) { ?>
                                            <span class="product_parent_category"
                                                  data-category="{{$cat->categoryTitle}}"></span>
                                            <?php } else { ?>
                                            <span class="product_child_category"
                                                  data-category="{{$cat->categoryTitle}}"></span>
                                            <?php }
                                            }
                                                $store_name = '';
                                                if (isset($rProduct->store)) {
                                                    $store_name = $rProduct->store->store_name;
                                                }
                                                ?>
                                            <span class="product_title" data-title="{{$rProduct->title}}"></span>
                                            <span class="product_store" data-store="{{$store_name}}"></span>
                                            <span class="product_price"
                                                  data-product-price="{{$rProduct->price}}"></span>
                                            <span class="p_id" data-product-id="{{$rProduct->product_id}}"></span>
                                            <span class="product_stock"
                                                  data-stock="{{$rProduct->current_stock}}"></span>

                                                <?php
                                                $fill = "";
                                            if (Session::has('userData')) {

                                                $favProductId = getBuyerFavProducts();
                                                // dump($favProductId);
                                                if (!empty($favProductId) && in_array($rProduct->product_id, $favProductId)) {
                                                    // dump("always has");
                                                    $fill = "fillheart";
                                                } ?>
                                                
                                            <a data-product-id="<?php echo $rProduct->product_id; ?>"
                                               class="dill adfv adtofv-home <?php echo $fill; ?>" ><img
                                                        class="image-add_tofav-s"
                                                        style="width:30px; position:relative; top:-7px"
                                                        src="{{ URL::asset($fill ? 'assets/images/heart.svg' : 'assets/images/heart-v2.svg') }}" alt="rate">
                                            </a>
                                                <?php } else {
                                                    $currenturl = url()->full();
                                                    ?>
                                                <a class=" adfv fav-login login-popup" data-location="{{$currenturl}}"
                                                   href="javascript:void(0)" data-toggle="modal"
                                                   data-target="#login-registerpopup">
                                                    <img class="image-add_tofav"
                                                         data-product-id="<?php echo $rProduct->product_id; ?>"
                                                         style="width:30px; position:relative; top:-7px"
                                                         src="{{ URL::asset('assets/images/heart-v2.svg')}}" alt="rate">

                                                </a>
                                                <?php } ?>

                                                    <?php
                                                    $userId = isset(Session::get('userData')['user_id']) ? Session::get('userData')['user_id'] : '';
                                                if (Session::has('userData') && $userId == $rProduct->user_id) { ?>
                                                <a class="notbuy" href="javascript:void(0)"> <span style="background-color: #073ba5;padding:10px;border-radius:50%;">
                                                <i class="fa fa-shopping-cart" style="color:#fff;font-size:17px;position:relative;top:1px;" aria-hidden="true"></i> </span></a>
                                                <?php } else if ($rProduct->product_type == 'variant') { ?>
                                                <a class="" data-type="{{$rProduct->product_type }}"
                                                   href="{{  URL::to('/product/' . $rProduct->slug) }}"> <span style="background-color: #073ba5;padding:10px;border-radius:50%;">
                                                   <i class="fa fa-shopping-cart" style="color:#fff;font-size:17px;position:relative;top:1px;" aria-hidden="true"></i> </span></a>
                                                <?php } else { ?>
                                                <a class="cartadd-home {{$class}} "
                                                   data-type="{{$rProduct->product_type }}"
                                                   data-stock="{{$rProduct->current_stock}}"
                                                   data-store-id="<?php echo $rProduct->store_id; ?>"
                                                   data-product-id="<?php echo $rProduct->product_id; ?>"
                                                   href="javascript:void(0)"> <span style="background-color: #073ba5;padding:10px;border-radius:50%;">
                                                   <i class="fa fa-shopping-cart {{$opecity}}" style="color:#fff;font-size:17px;position:relative;top:1px;" aria-hidden="true"></i> </span> </a>
                                            <?php } ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>


                    </div>
                </div>
                <!-- card ends-->

            @endforeach
        @endif
    </div>


</div>

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