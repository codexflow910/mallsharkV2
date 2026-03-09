<div class="container" style="margin-top:0px">
    <div class="row">
        <div class="col-md-12 text-center bstslrbx">
            <h3>MallShark's Best Sellers</h3>
            <!-- <p class="text-center">This section is to showcase popular items</p> -->
        </div>
    </div>


    <div>
        <!-- <div class="col-md-3 adbx"><a href="{{ URL::to('/')}}"><img style="width:100%" src="{{ asset('assets/images/best-seller.jpg') }}" alt="ad" title="ad"></a></div> -->
        <div>
            <div class="row">
                <?php
                $className = '';
                $opecity = '';
                ?>
                @if(count($bestSellers) > 0)
                    @foreach ($bestSellers as $bSeller)
                        @php

                            if ($bSeller->product_type == 'simple') {
                                $current_stock = $bSeller->current_stock;
                                if ($current_stock > 0) {
                                    $className = 'current-stock-active';
                                    $opecity = '';

                                }else{
                                    $className = 'current-stock-deactive';
                                    $opecity = 'opecity-deactive';
                                }
                            }

                             $image_url = getAttachmentUrlById((int) $bSeller->feature_image, 'medium');
                             if (!isImageExists($image_url)) {
                             $image_url = url(asset('assets/images/placeholder.jpg', env('HTTPS_STATUS')));
                                }
                                $priceExplode = explode('.',$bSeller->price);
                                $prePrice = isset($priceExplode[0]) ? $priceExplode[0] : '';
                                $postPrice = isset($priceExplode[1]) ? ('.'.$priceExplode[1]) : '';

                                 if ($bSeller->on_sale == 1){
                                            $salePriceExplode = explode('.',$bSeller->sale_price);
                                            $preSalePrice = isset($salePriceExplode[0]) ? $salePriceExplode[0] : '';
                                            $postSalePrice = isset($salePriceExplode[1]) ? ('.'.$salePriceExplode[1]) : '';
                                 }
                        @endphp
                        <div class="col-md-6 bstsllr">


                            <!-- card starts -->
                            <div class="card">

                                <div class="row">
                                    <div class="col-md-4 col-6"><a
                                                href="{{  URL::to('/product/' . $bSeller->slug) }}"><img
                                                    src="{{ $image_url }}" class="card-img-top product-thumbnail"
                                                    alt="{{ $bSeller->title }}" title="{{ $bSeller->title }}"></a></div>
                                    <div class="col-md-8 col-6">
                                        <div class="card-body">
                                            <h4 class="card-title"><a
                                                        href="{{  URL::to('/product/' . $bSeller->slug) }}"> {{ $bSeller->title }}</a>
                                            </h4>
                                            <div>
                                                    <?php
                                                foreach ($bSeller['categories'] as $key => $cat) {
                                                if ($cat->parentCategoryID == 0) { ?>

                                                <span class="catnm"><?php echo $cat->categoryTitle . '&nbsp;' ?></span>
                                                <?php } else { ?>
                                                <span class="catnm catnm1"><?php echo '(' . Str::limit($cat->categoryTitle, 5, '..') . ')'; ?></span>

                                                <?php }
                                                }
                                                    ?>

                                            </div>
                                            <div class="prcbxint">
                                                <div><p class="card-text prc">
                                                        @if($bSeller->on_sale == 1)
                                                            <span class="n-price"><span class="z-symbl">$</span>{{ $preSalePrice}}<span
                                                                        class="deci">{{ $postSalePrice}}</span></span>
                                                            <del class="sale-price"><del class="z-symbl">$</del>{{ $prePrice}}<del
                                                                        class="deci">{{ $postPrice}}</del></del>
                                                        @else
                                                            <span class="n-price"><span class="z-symbl">$</span>{{ $prePrice}}<span
                                                                        class="deci">{{ $postPrice}}</span></span>
                                                        @endif
                                                        &nbsp;| &nbsp;
                                                        <span class="n-stars  push-right">
                                                            <!-- @php echo getProductReviewAverage($bSeller->product_id) @endphp -->

                                                                <?php
                                                                $review = getProductTotalRatingById($bSeller->product_id);
                                                                $reviews = get_reviews($bSeller->product_id);
                                                                // echo 'Data: <pre>' .print_r($reviews,true). '</pre>'; die;
                                                                $average = 0;
                                                            if (isset($reviews) && count($reviews) > 0) {

                                                                $numer = 0;

                                                                $star1_count = 0;
                                                                $star2_count = 0;
                                                                $star3_count = 0;
                                                                $star4_count = 0;
                                                                $star5_count = 0;
                                                                foreach ($reviews as $rev) {
                                                                    $numer += $rev->rating;
                                                                    if ($rev->rating == 1) {
                                                                        $star1_count = $star1_count + 1;
                                                                    }
                                                                    if ($rev->rating == 2) {
                                                                        $star2_count = $star2_count + 1;
                                                                    }
                                                                    if ($rev->rating == 3) {
                                                                        $star3_count = $star3_count + 1;
                                                                    }
                                                                    if ($rev->rating == 4) {
                                                                        $star4_count = $star4_count + 1;
                                                                    }
                                                                    if ($rev->rating == 5) {

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
                                                                $average = isset($average) ? $average : ""; ?>
                                       <span class="rating-counter">{{$average}}.0</span>
                                      <span>
                                      <?php
                                          for ($i = 1;
                                               $i <= 5;
                                               $i++) {
                                              if ($i <= $average) {
                                                  $class = "fill-rating";
                                              } else {
                                                  $class = "notfill-rating";
                                              }
                                              ?>
   
                                            <img class="<?php echo $class; ?>"
                                                 style="width:16px; position:relative; top:-2px"
                                                 src="{{ URL::asset('assets/images/rate.svg')}}" alt="rate">
                                        <?php } ?>
                                    
                                    </span>
                                   


                                    <?php } else {
                                    if (canUserReview($bSeller->product_id)) { ?>
                                          <span class="rating-counter">{{$average}}.0</span>
                                           <span>
                                           <?php
                                               for ($i = 1;
                                                    $i <= 5;
                                                    $i++) {
                                                   if ($i <= $average) {
                                                       $class = "fill-rating";
                                                   } else {
                                                       $class = "notfill-rating";
                                                   }
                                                   ?>

                                            <img class="<?php echo $class; ?>"
                                                 style="width:16px; position:relative; top:-2px"
                                                 src="{{ URL::asset('assets/images/rate.svg')}}" alt="rate">
                                        <?php } ?>
                                        </span> 
                                       <?php } else { ?>
                                        <span class="rating-counter">{{$average}}.0</span>
                                      <span>
                                      <?php
                                          for ($i = 1;
                                               $i <= 5;
                                               $i++) {
                                              if ($i <= $average) {
                                                  $class = "fill-rating";
                                              } else {
                                                  $class = "notfill-rating";
                                              }
                                              ?>

                                            <img class="<?php echo $class; ?>"
                                                 style="width:16px; position:relative; top:-2px"
                                                 src="{{ URL::asset('assets/images/rate.svg')}}" alt="rate">
                                        <?php } ?>
                                    </span> 
                                  
                                       <?php }
                                       }
                                           ?>
                          
                         </span>
                                                    </p>
                                                    <div class="total-reviews">
                                                        <p>
                                                            <a href="{{  URL::to('/product/' . $bSeller->slug) }}"><?php echo $review['total']; ?>
                                                                Total Reviews</a></p>
                                                    </div>
                                                </div>
                                                <div></div>

                                                <div class="card-text small d-flex">

                                                    <div class="splopt">
                                                            <?php foreach ($bSeller['categories'] as $key => $cat) {
                                                        if ($cat->parentCategoryID == 0) { ?>
                                                        <span class="product_parent_category"
                                                              data-category="{{$cat->categoryTitle}}"></span>
                                                        <?php } else { ?>
                                                        <span class="product_child_category"
                                                              data-category="{{$cat->categoryTitle}}"></span>
                                                        <?php }
                                                        }
                                                            $store_name = '';
                                                            if (isset($bSeller->store)) {
                                                                $store_name = $bSeller->store->store_name;
                                                            }
                                                            ?>
                                                        <span class="product_title"
                                                              data-title="{{$bSeller->title}}"></span>
                                                        <span class="product_store" data-store="{{$store_name}}"></span>
                                                        <span class="product_price"
                                                              data-product-price="{{$bSeller->price}}"></span>
                                                        <span class="p_id"
                                                              data-product-id="{{$bSeller->product_id}}"></span>
                                                        <span class="product_stock"
                                                              data-stock="{{$bSeller->current_stock}}"></span>
                                                            <?php
                                                            $fill = "";
                                                        if (Session::has('userData')) {

                                                            $favProductId = getBuyerFavProducts();
                                                            if (!empty($favProductId) && in_array($bSeller->product_id, $favProductId)) {
                                                                $fill = "fillheart";
                                                            } ?>
                                                        <a data-product-id="<?php echo $bSeller->product_id; ?>"
                                                           class="dill adfv adtofv-home <?php echo $fill; ?>"
                                                        ><img class="image-add_tofav-s" style="width:30px; position:relative;"  src="{{ URL::asset($fill ? 'assets/images/heart.svg' : 'assets/images/heart-v2.svg') }}" alt="rate">
                                                        </a>
                                                            <?php }else{
                                                                $currenturl = url()->full();
                                                                ?>
                                                            <a class=" adfv fav-login login-popup"
                                                               data-location="{{$currenturl}}"
                                                               href="javascript:void(0)" data-toggle="modal"
                                                               data-target="#login-registerpopup">
                                                                <img class="image-add_tofav"
                                                                     style="width:30px; position:relative; top:-7px"
                                                                     src="{{ URL::asset('assets/images/heart-v2.svg')}}"
                                                                     alt="rate">

                                                            </a>
                                                            <?php } ?>
                                                                <?php
                                                                $userId = isset(Session::get('userData')['user_id']) ? Session::get('userData')['user_id'] : '';
                                                            if (Session::has('userData') && $userId == $bSeller->user_id) { ?>
                                                            <a class="notbuy" href="javascript:void(0)"><img
                                                                        style="width:30px; position:relative; top:-7px"
                                                                        src="{{ URL::asset('assets/images/basket-v2.svg')}}"
                                                                        alt="rate"> </a>
                                                            <?php } else if ($bSeller->product_type == 'variant') { ?>
                                                            <a href="{{  URL::to('/product/' . $bSeller->slug) }}"><img
                                                                        style="width:30px; position:relative; top:-7px"
                                                                        src="{{ URL::asset('assets/images/basket-v2.svg')}}"
                                                                        alt="rate"> </a>
                                                            <?php } else{ ?>
                                                            <a class="cartadd-home {{$className}}"
                                                               data-store-id="<?php echo $bSeller->store_id; ?>"
                                                               data-product-id="<?php echo $bSeller->product_id; ?>"
                                                               href="javascript:void(0)"><img class="{{$opecity}}"
                                                                                              style="width:30px; position:relative; top:-7px"
                                                                                              src="{{ URL::asset('assets/images/basket-v2.svg')}}"
                                                                                              alt="rate"> </a>
                                                        <?php } ?>

                                                    </div>
                                                </div>
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