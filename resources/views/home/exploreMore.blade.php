<div class="container prdctlov" style="margin-top:10px">
    <div class="row">
        <div class="col-md-12">
            <h3 class="page-heading">Explore More</h3>
            <h4 class="f6 gray lh-title ma0 mt2 mt1-m normal ">Check out what creators are sharing</h4>
            <div class="view-al place-into-tap" style="top:0px;">
            <a href="{{URL::to('/search?keyword=')}}" class="viewalllink">Show All</a>
        </div>
        </div> 
    </div>
<div class="row">           
        @if(count($exploreMore) > 0)
        @foreach ($exploreMore as $product)
        @php
        
         $image_url = getAttachmentUrlById((int) $product->feature_image, 'medium');
         if (!isImageExists($image_url)) {
         $image_url = url(asset('assets/images/placeholder.jpg', env('HTTPS_STATUS')));
            }
            $priceExplode = explode('.',$product->price);
                $prePrice = isset($priceExplode[0]) ? $priceExplode[0] : '';
                $postPrice = isset($priceExplode[1]) ? ('.'.$priceExplode[1]) : '';

                if ($product->on_sale == 1){
                        $salePriceExplode = explode('.',$product->sale_price);
                        $preSalePrice = isset($salePriceExplode[0]) ? $salePriceExplode[0] : '';
                        $postSalePrice = isset($salePriceExplode[1]) ? ('.'.$salePriceExplode[1]) : '';
                }
        @endphp

        @if($product->current_stock > 0)
        <div class="col-md-3 col-6">
            <!-- card starts -->
            <div class="card">
                                         
                <a href="{{  URL::to('/product/' . $product->slug) }}"><img src="{{ $image_url }}" class="card-img-top product-thumbnail" alt="{{ $product->title }}" title="{{ $product->title }}"></a>
                <div class="card-body">
                    <h4 class="card-title"><a href="{{  URL::to('/product/' . $product->slug) }}"> {{ $product->title }}</a></h4>
                    <div>
                        <?php 
                            foreach ($product['categories'] as $key => $cat) {
                            if ($cat->parentCategoryID == 0) { ?>
                               
                               <span class="catnm"><?php echo  $cat->categoryTitle . '&nbsp;' ?></span>
                            <?php  } else { ?>
                                <span class="catnm catnm1"><?php echo '(' . Str::limit($cat->categoryTitle, 5 ,'..') . ')'; ?></span>
                             
                           <?php }
                            }
                            ?></div>
                            <div class="prcbxint">
                                <div>
                                <p class="card-text prc">
                                    @if($product->on_sale == 1)
                                        <span class="n-price"><span class="z-symbl">$</span>{{ $preSalePrice}}<span
                                                    class="deci">{{ $postSalePrice}}</span></span>
                                        <del class="sale-price"><del class="z-symbl">$</del>{{ $prePrice}}<del
                                                    class="deci">{{ $postPrice}}</del></del>
                                    @else
                                        <span class="n-price"><span class="z-symbl">$</span>{{ $prePrice}}<span
                                                    class="deci">{{ $postPrice}}</span></span>
                                    @endif

                                <span class="n-stars  push-right" style="display:none">
                                <!-- @php echo getProductReviewAverage($product->product_id) @endphp--> <span><img style="width:16px; position:relative; top:-2px" src="{{ URL::asset('assets/images/rate.svg')}}"  alt="rate" > @php echo productTotalRatings($product->product_id) @endphp.0</span> 
                                </span>
                                </p>
                            </div>
                             <div class="card-text small d-flex">
                        
                        <div class="splopt"> 
                            
                        <?php foreach ($product['categories'] as $key => $cat) {
                            if ($cat->parentCategoryID == 0) { ?>
                                <span class="product_parent_category" data-category="{{$cat->categoryTitle}}" ></span>
                            <?php  } else { ?>
                                <span class="product_child_category" data-category="{{$cat->categoryTitle}}" ></span>
                            <?php }
                            }
                            $store_name = '';
                            if(isset($product->store)){
                                $store_name = $product->store->store_name;
                            }
                            ?> 
                            <span class="product_title" data-title="{{$product->title}}"></span>
                            <span class="product_store" data-store="{{$store_name}}"></span>
                            <span class="product_price" data-product-price="{{$product->price}}"></span>
                            <span class="p_id" data-product-id="{{$product->product_id}}"></span>
                            <span class="product_stock" data-stock="{{$product->current_stock}}"></span>
                        <?php
                        $fill = "";  
                        if (Session::has('userData')) {
                                    
                                    $favProductId = getBuyerFavProducts();
                                    if (!empty($favProductId) && in_array($product->product_id, $favProductId)) {
                                        $fill =  "fillheart";
                                    } ?>
                                        <a data-product-id="<?php echo $product->product_id; ?>" class="adfv adtofv-home <?php echo $fill; ?>" href="javascript:void(0);"><img class="image-add_tofav"  style="width:30px; position:relative; top:-7px" src="{{ URL::asset('assets/images/heart-v2.svg')}}"  alt="rate" >
                                    <?php  }else{ 
                                        $currenturl = url()->full();
                                        ?>
                                        <a class=" adfv fav-login login-popup"
                                               data-location="{{$currenturl}}"
                                               href="javascript:void(0)" data-toggle="modal" data-target="#login-registerpopup">
                                               <img class="image-add_tofav"  style="width:30px; position:relative; top:-7px" src="{{ URL::asset('assets/images/heart-v2.svg')}}"  alt="rate" >
                                              
                                            </a>
                                     <?php   } ?>
                        <?php 
                        $userId = isset(Session::get('userData')['user_id']) ? Session::get('userData')['user_id']:'';
                        if (Session::has('userData') && $userId == $product->user_id) { ?>
                            <a  class="notbuy" href="javascript:void(0)"><img style="width:30px; position:relative; top:-7px" src="{{ URL::asset('assets/images/basket-v2.svg')}}"  alt="rate" > </a>
                        <?php  } else if ($product->product_type == 'variant') { ?>
                            <a  href="{{  URL::to('/product/' . $product->slug) }}"><img style="width:30px; position:relative; top:-7px" src="{{ URL::asset('assets/images/basket-v2.svg')}}"  alt="rate" > </a>
                        <?php  } else{ ?>
                            <a  class="cartadd-home" data-store-id="<?php echo $product->store_id; ?>" data-product-id="<?php echo $product->product_id; ?>"  href="javascript:void(0)"><img style="width:30px; position:relative; top:-7px" src="{{ URL::asset('assets/images/basket-v2.svg')}}"  alt="rate" > </a>
                        <?php   }  ?>
                        </div> 
                    </div>
                            </div>
                    
            
                         
                </div>
            </div>
            <!-- card ends-->

        </div>
        @endif
        @endforeach
        @endif


        
</div>
</div>