<div class="container prdctlov" style="margin-top:10px">
    <div class="row">
        <div class="col-md-12">
            <h3 class="page-heading">Products You May Like</h3>
        </div> 
    </div>
<div class="row">           
        @if( !empty($productsYouMayLikeElastic) && count($productsYouMayLikeElastic['records']) > 0)
        @foreach ($productsYouMayLikeElastic['records'] as $product)
        @php
          $product = json_decode(json_encode($product['_source']));
        //   dd($product);
        //   dd(json_decode(json_encode($product['_source']))->product_id);  
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
        <div class="col-sm-3 col-md-2 col-6 ">
            <!-- card starts -->
            <div class="card">
                                         
                <a href="{{  URL::to('/product/' . $product->slug) }}"><img src="{{ $image_url }}" class="card-img-top product-thumbnail" alt="{{ $product->title }}" title="{{ $product->title }}"></a>
                <div class="card-body">
                                                <div>
                                                <?php 
                                                     if (isset($product->categories[1])) {
                                                        $childCategory = \App\Models\category::where('category_slug',$product->categories[1])->select('categoryTitle')->first();
                                                            // Show only the child category if it exists
                                                            // $childCat = str_replace('-', ' ', $categories[1]);
                                                            // $childCategory = ucwords($childCat);
                                                            echo '<span class="product_child_category card-category">'.$childCategory->categoryTitle.'</span>';
                                                        } else {
                                                            // Otherwise, show the parent category
                                                            if (isset($product->categories[0])) {
                                                                $parentCategory = \App\Models\category::where('category_slug',$product->categories[0])->select('categoryTitle')->first();
                                                                // $parentCat = str_replace('-', ' ', $categories[0]);
                                                                // $parentCategory = ucwords($parentCat);
                                                                echo '<span class="product_child_category card-category">'.$parentCategory->categoryTitle.'</span>';
                                                            }
                                                        }?>
                                                </div>
                    <h4 class="card-title"><a href="{{  URL::to('/product/' . $product->slug) }}"> {{ $product->title }}</a></h4>
                    <div class="d-none">
                        <?php 
                       
                        if (!empty($product->categories)) {
                            $categoryToShow = count($product->categories) > 1 ? end($product->categories) : $product->categories[0];
                            $categoryClass = (count($product->categories) > 1) ? 'product_child_category' : 'product_parent_category';
                            ?>
                            <span class="<?php echo $categoryClass; ?> card-category" data-category="<?php echo $categoryToShow; ?>">
                                <?php echo $categoryToShow . '&nbsp;'; ?>
                            </span>
                        <?php } ?>
                    </div>
                    
                            <div class="prcbxint">
                                <div>
                                <p class="card-text prc">
                                    @if($product->on_sale == 1)
                                        <span class="n-price"><span class="z-symbl">$</span>{{ $preSalePrice}}<span
                                                    class="deci">{{ $postSalePrice}}</span></span>
                                        <del class="sale-price"><del class="z-symbl">$</del>{{ $prePrice}}<del
                                                    class="deci">{{ $postPrice}}</del></del>
                                                    @if($product->discount_percentage > 0)
                                                    <span class="n-price mybadge">-{{ $product->discount_percentage }}%</span>
                                                    @else
                                                    <span class="n-price mybadge">{{ $product->discount_percentage }}%</span>
                                                    @endif
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
                        <?php foreach ($product->categories as $key => $cat) { ?>
                                <span class="product_parent_category" data-category="{{$cat}}" ></span>
                            <?php 
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
                                        <a data-product-id="<?php echo $product->product_id; ?>" class="dill adfv adtofv-home <?php echo $fill; ?>" ><img class="image-add_tofav-s" style="width:30px; position:relative;"  src="{{ URL::asset($fill ? 'assets/images/heart.svg' : 'assets/images/heart-v2.svg') }}" alt="rate">
                                        </a>
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
                            <a  class="notbuy" href="javascript:void(0)"> <span style="background-color: #073ba5;padding:10px;border-radius:50%;">
                            <i class="fa fa-shopping-cart" style="color:#fff;font-size:17px;position:relative;top:1px;" aria-hidden="true"></i> </span></a>
                        <?php  } else if ($product->product_type == 'variant') { ?>
                            <a  href="{{  URL::to('/product/' . $product->slug) }}"> <span style="background-color: #073ba5;padding:10px;border-radius:50%;">
                            <i class="fa fa-shopping-cart" style="color:#fff;font-size:17px;position:relative;top:1px;" aria-hidden="true"></i> </span></a>
                        <?php  } else{ ?>
                            <a  class="cartadd-home" data-store-id="<?php echo $product->store_id; ?>" data-product-id="<?php echo $product->product_id; ?>"  href="javascript:void(0)"> <span style="background-color: #073ba5;padding:10px;border-radius:50%;">
                            <i class="fa fa-shopping-cart" style="color:#fff;font-size:17px;position:relative;top:1px;" aria-hidden="true"></i> </span></a>
                        <?php   }  ?>
                        </div> 
                    </div>
                            </div>
                    
            
                         
                </div>
            </div>
            <!-- card ends-->

        </div>
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