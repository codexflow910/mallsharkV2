 


<script>
    
document.addEventListener("DOMContentLoaded", function () {
    const imageContainers = document.querySelectorAll(".image-container-mbt > img");

    imageContainers.forEach(image => {
        const img = new Image();
        img.src = image.src;

        img.onload = function () {
            const width = img.naturalWidth;
            const height = img.naturalHeight;

            if (width > height) {
                image.classList.add('landscape');
            } else if (height > width) {
                image.classList.add('portrait');
            } else {
                image.classList.add('square');
            }
        };
    });
});
    </script>

<?php


if (!empty($productResults) && count($productResults['records']) > 0) {
        $per_page = 9;
        $total_records = (int)$productResults['total']['value'];
        $max = $total_records / $per_page;
        
        $page_number = isset($_GET['page']) ? $_GET['page'] : 1;
        unset($_GET['page']);
        $pageurl = Request::fullUrl(); 
        if (!$_GET) {
            $pageurl = $pageurl . '?';
        }
        
        if ($productResults['total']['value'] > $per_page) {
            ?>
            <script>
                var maxdata = '<?php echo  $max; ?>';
                
            </script>
        <?php }
    $favProductId = getBuyerFavProducts();
    $userId = !empty(Session::get('userData')['user_id']) ? Session::get('userData')['user_id'] : '';
    $totalSupporters = 0;
    if (Route::currentRouteName() == 'storehome') {
        if (!empty($storeinfo[0]->store_id)) {
        }
    }
    try {

        $params  = $_GET;
        if(isset($params['search']) && $params['search'] == '/search' && !isset($params['sort_by']) 
        && !isset($params['sort_order']) && !isset($params['min_price']) && !isset($params['max_price']) && !isset($params['recent']) && !isset($params['category'])){
       
    $productResults['records'];
        }elseif(isset($params['category']) && isset($params['search']) && !isset($params['sort_by']) 
        && !isset($params['sort_order']) && !isset($params['min_price']) && !isset($params['max_price']) && !isset($params['recent'])){
            $productResults['records'];
        }else{
          
            $productResults['records'];
        }
    } catch (\Throwable $th) {
        echo $th->getMessage();
    }
    $product_count = 0;
    // echo '<span class="loadmore" data="'false'"></span>';
    foreach ($productResults['records'] as $pr) { 
        $product_count++;

        
        if (isset($cartProducts) && array_key_exists($pr['_source']['product_id'], $cartProducts)) {
            $class = "show-quantitiy-panel";
             $key2 = $cartProducts[$pr['_source']['product_id']];
        } else {
            $class = "";
            $key2 = "";
        }
        ?>

        
        <?php
        $storeDataId = "";
        if (!empty($pr['_source']['store_id'])) {
            $storeDataId = "data-store-id=" . $pr['_source']['store_id'];
        } else {
            $storeDataId = "data-store-id=2";
        }
        $store_info = getStoreInfoById((int) $pr['_source']['store_id']);
        $category = getCategoriesByProductIDGTM($pr['_source']['product_id']);
        
        if ($category !== null) {
            $productCategory = getCategoryByCateId($category->category_id); ?>
       
            <span class="product_parent_category" data-category="{{$productCategory['categoryTitle']}}"></span>
            <?php }
        // dump($store_info[0]->store_name);
        $store_icon = isset($store_info[0]->store_icon) ? $store_info[0]->store_icon : '';
        $image_id = getImageIdByIconId((int) $store_icon);
        $iconUrl = getAttachmentUrlById((int) $image_id, 'icons');
        $storeIconData = getZAnimalById($store_icon);
        if (isset($storeIconData)) {
            $zanimalName = $storeIconData->name;
        }

        $categories = isset($pr['_source']['categories']) ? $pr['_source']['categories'] : "";

        ?>
        <div class="col-6  col-lg-3 col-md-4 col-sm-6 search-listing-items" data-product-count={{$product_count}}>
           
            <div class="card">


                <div class="add-to-fav">
                    <?php
                    $url = route('pro.detail',['slug' => $pr['_source']['slug']]);
                    if (Session::has('userData')) {
                        if (!empty($favProductId) && in_array($pr['_source']['product_id'], $favProductId)) {
                            $fill = "fillheart";
                        } else {
                            $fill = "";
                        }
                    ?>
                    <a data-product-id="<?php echo $pr['_source']['product_id']; ?>"
                                               class="dill adfv adtofv-home <?php echo $fill; ?>" ><img
                                                        class="image-add_tofav-s"
                                                        style="width:30px; position:relative; top:-7px"
                                                        src="{{ URL::asset($fill ? 'assets/images/heart.svg' : 'assets/images/heart-v2.svg') }}" alt="rate">
                                            </a>
                        
                    <?php } else { ?>

                        <a class="login-popup favlogin" data-location="{{URL::to('q/')}}" href="javascript:void(0)" data-toggle="modal" data-target="#login-registerpopup">
                            <div class="heart-addtofav no-animate"></div>
                        </a>
                    <?php } ?>
                </div><!-- add-to-fav -->
                <?php $store_slug = isset($store_info[0]->store_slug) ? $store_info[0]->store_slug : ''; ?>
               
                <?php
                if (isset($pr['_source']['is_feature']) && $pr['_source']['is_feature'] == 1) {
                ?>
                    <div class="feature-product-sec">
                        <span class="badge badge-primary">Featured</span>
                    </div>
                <?php
                }
                ?>
                <?php $imageUrl = getAttachmentUrlById((int) $pr['_source']['feature_image'], 'medium');
                if($imageUrl){
                    $imageUrl = $imageUrl;
                }else{
                    $imageUrl = URL::asset('assets/images/placeholder.jpg');
                }
                if ($imageUrl) { ?>
                    
                    <a href="{{$url}}" class="image-container-mbt">
                        <img itemprop="image" src="{{$imageUrl}}" alt="{{$pr['_source']['title']}}" title="{{$pr['_source']['title']}}" >
                        <!-- <span class="blur-effect" style="background-image: url('{{$imageUrl}}'); filter:blur(30px)"></span>
                    </a> -->
                  
                <?php } else { ?>
                    <a href="{{$url}}" class="image-container-mbt">
                        <img itemprop="image" src="{{ URL::asset('assets/images/placeholder.jpg')}}" alt="{{$pr['_source']['title']}}" title="{{$pr['_source']['title']}}">
                    </a>
                <?php   }
                ?>

                <div class="card-body" data-stock="<?php
                                                    if ($pr['_source']['current_stock'] >= 10) {
                                                        echo "10";
                                                    } else {
                                                        echo $pr['_source']['current_stock'];
                                                    }
                                                    $product_title =   ucfirst($pr['_source']['title']);
                                                    ?>">
                <div style="min-height: 143px;">
                    <div class="cartbxup">
                          

                           
                            <span class="product_title" data-title="{{$pr['_source']['title']}}"></span>
                            <span class="product_store" data-store="{{$store_info[0]->store_name}}"></span>
                            <span class="product_price" data-product-price="{{$pr['_source']['price']}}"></span>
                            <span class="p_id" data-product-id="{{$pr['_source']['product_id']}}"></span>
                            <span class="product_stock" data-stock="{{$pr['_source']['current_stock']}}"></span>


                        <?php if (Session::has('userData') && $userId == $pr['_source']['user_id']) {  ?>
                            <a href="javascript:void(0)" class="notbuy storeowner_product"><i class="fa fa-shopping-cart" aria-hidden="true"></i> </a>

                        <?php  } else if (($pr['_source']['product_type'] == 'variant')) { ?>
                            <a class="variable_product" <?php echo $storeDataId; ?> href="<?php echo URL::to('product/' . $pr['_source']['slug']); ?>"><i class="fa fa-shopping-cart" aria-hidden="true"></i> </a>
                        <?php } else if ($pr['_source']['product_type'] != 'virtual' && $pr['_source']['current_stock'] <= 0) { ?>
                            <a href="javascript:void(0)"  class=" simple_product current-stock-deactive"
                             data-product-id="<?php echo $pr['_source']['product_id']; ?>">
                             <i style="opacity: 0.5;" class="fa fa-shopping-cart" aria-hidden="true"></i> </a>
                            <!-- <a class="out-of-stock" href="#">Out of stock </a> -->
                        <?php } else if ($pr['_source']['product_type'] == 'virtual') {  ?>
                            <a href="javascript:void(0)" <?php echo $storeDataId; ?> class="cartadded virtial_product" data-product-id="<?php echo $pr['_source']['product_id']; ?>"><i class="fa fa-shopping-cart" aria-hidden="true"></i> </a>
                        <?php } else {  ?>
                            <a href="javascript:void(0)" <?php echo $storeDataId;?> class="cartadded simple_product" data-product-id="<?php echo $pr['_source']['product_id']; ?>"><i class="fa fa-shopping-cart" aria-hidden="true"></i> </a>
                        <?php } ?>
                    </div>
                    <div class="mb-1 discount-container">
                        @if(isset($pr['_source']['on_sale']) && $pr['_source']['on_sale'] == 1)
                            @if($pr['_source']['discount_percentage'] > 0)
                                <span class="z-symbl product-price discountbadge" style="position:relative!important;top:0px!important;font-weight:600;">
                                    -{{$pr['_source']['discount_percentage']}}% off
                                </span><b class="discountbadge-text">Limited time offer</b>
                                @else
                                <span style="display: inline-block;margin-bottom: 8px;font-size: 18px;font-weight: 600;position: relative!important;top:0px!important; z-in" class="z-symbl product-price discountbadge" >
                                    {{$pr['_source']['discount_percentage']}}% off
                                </span><b class="discountbadge-text">Limited time offer</b>
                            @endif
                        @endif
                    </div>
                    <div class="infbx small  justify-content-between">
                        <?php if (Route::currentRouteName() == 'storehome') { ?>
                          
                        <?php } ?>
                        <div>
                            <?php
                            if (isset($categories[1])) {
                                $childCategory = \App\Models\category::where('category_slug',$categories[1])->select('categoryTitle')->first();
                                    // Show only the child category if it exists
                                    // $childCat = str_replace('-', ' ', $categories[1]);
                                    // $childCategory = ucwords($childCat);
                                if (isset($childCategory)){
                                    echo '<span class="card-category">' . $childCategory->categoryTitle . '</span>';
                                }
                                } else {
                                    // Otherwise, show the parent category
                                    if (isset($categories[0])) {
                                        $parentCategory = \App\Models\category::where('category_slug',$categories[0])->select('categoryTitle')->first();
                                        // $parentCat = str_replace('-', ' ', $categories[0]);
                                        // $parentCategory = ucwords($parentCat);
                                        echo '<span class="parent">' . $parentCategory->categoryTitle . '&nbsp;</span>';
                                    }
                                }
                            ?>
                        </div>
                    </div>
                    <h3 class="card-title">
                        <a class="product-item-name" href="<?php echo $url; ?>" title="<?php echo ucfirst($pr['_source']['title']); ?>" alt="<?php echo $pr['_source']['title']; ?>">{{ $product_title }}</a>
                    </h3>
                   </div>
                    <div class="  no-flex">

                        <div class="prc d-flex justify-content-between">
                            <!--<span class="throg small"> Z2 </span>-->

                            @if(isset($pr['_source']['on_sale']) && $pr['_source']['on_sale'] == 1)
                                <span>
                                    <span style="display: inline-block;margin-bottom: 8px;font-size: 18px;font-weight: 600; color: #262626 !important;" class="z-symbl product-price  ">
                                        ${{number_format($pr['_source']['sale_price'],2)}}
                                    </span>
                                    <del style="font-size: 13px !important;" class="product-price">${{number_format($pr['_source']['price'],2)}}</del>
                                   
                                 
                                </span>
                            @else
                                <span class="zit-price">
                                    <span class="z-symbl product-price">${{number_format($pr['_source']['price'],2)}}</span>
                                </span>
                            @endif


                            <!--rating stars -->
                            <div class="text-right" style="display:none">
                                <div class="rating-strip">
                                    <?php echo getProductReviewAverage($pr['_source']['product_id']); ?>
                                </div>
                            </div>
                        </div>
                        <!--                 
                        show-quantitiy-panel-->
                        <?php
                        // if (Session::has('userData')) {
                        ?>
                        <div class="cnt-bnt-ct abc" style="display:none">
                            <div style="display:none" class="crtbtn col align-self-center buttons-group-cnt <?php echo $class; ?>">
                                <?php if (Session::has('userData') && $userId == $pr['_source']['user_id']) {  ?>
                                    <button type="button" class="primary-btn btn btn-primary btnorm notbuy">Add To Cart</button>
                                <?php
                                } else if (($pr['_source']['product_type'] == 'variant')) {
                                ?>
                                    <a <?php echo $storeDataId; ?> href="<?php echo URL::to('product/' . $pr['_source']['slug']); ?>" class="primary-btn btn btn-primary btnorm">Add To Cart</a>

                                <?php } else if ($pr['_source']['product_type'] != 'virtual' && $pr['_source']['current_stock'] <= 0) { ?>
                                    <button type="button" class="primary-btn btn btn-primary btnorm notinstock">Out of stock</button>
                                <?php } else if ($pr['_source']['product_type'] == 'virtual') {  ?>
                                    <!-- <button type="button"
                                                <?php // echo $storeDataId; 
                                                ?> data-product-id="<?php // echo $pr['_source']['product_id']; 
                                                                                                ?>"
                                                class="primary-btn btn btn-primary btnorm cartadded ">Add To Cart
                                        </button> -->
                                <?php } else {
                                ?>
                                    <!-- <button type="button"
                                                <?php // echo $storeDataId; 
                                                ?> data-product-id="<?php // echo $pr['_source']['product_id']; 
                                                                                                ?>"
                                                class="primary-btn btn btn-primary btnorm cartadd cartadded ">Add To Cart
                                        </button> -->
                                    <div class="input-group quantity-number-option cntr-txt-fld">
                                        <span class="minus-icon first-icon-counter" data-pro-id="<?php echo $pr['_source']['product_id']; ?>" data-stor-id="<?php echo $pr['_source']['store_id']; ?>" <?php echo $storeDataId; ?>>
                                            <i class="fa fa-minus" aria-hidden="true"></i>
                                        </span>
                                        <span class="input-counter">
                                            <input type="text" name="cart_quantity" id="cart_quantity_<?php echo $pr['_source']['product_id'];  ?>" class="form-control input-number" placeholder="in cart" value="<?php
                                                                                                                                                                                                                    if (!empty($key2)) {
                                                                                                                                                                                                                        echo $key2;
                                                                                                                                                                                                                    } else {
                                                                                                                                                                                                                        echo 1;
                                                                                                                                                                                                                    }
                                                                                                                                                                                                                    ?>" min="1" max="50" readonly="">
                                            <span class="static-text crt-itm-st"> in cart</span>
                                        </span>
                                        <?php
                                        if ($pr['_source']['product_type'] != 'virtual' &&  $key2 == $pr['_source']['current_stock']) {
                                            $disabled = 'disabled';
                                        } else if ($pr['_source']['product_type'] == 'virtual') {
                                            $disabled = '';
                                        } else if ($key2 == 10) {
                                            $disabled = 'disabled';
                                        } else {
                                            $disabled = '';
                                        }
                                        $maxquantitylimit = isset($pr['_source']['maxquantitylimit']) ? $pr['_source']['maxquantitylimit'] : "";
                                        // echo 'Data: <pre>' .print_r($maxquantitylimit,true). '</pre>'; 
                                        ?>
                                        <span data-product-type="{{$pr['_source']['product_type']}}" class="plus-icon second-icon-counter {{$disabled}}" data-maxquantity="{{$maxquantitylimit}}" data-stor-id="<?php echo $pr['_source']['store_id']; ?>" data-pro-id="<?php echo $pr['_source']['product_id']; ?>" <?php echo $storeDataId; ?>>
                                            <i class="fa fa-plus" aria-hidden="true"></i>
                                        </span>

                                    </div>
                                <?php
                                }
                                $cashBack = "2%";
                                $textclass = '';
                                if (isset($pr['_source']['flashsale_status']) && $pr['_source']['flashsale_status'] == 'choosen') {
                                    $cashBack = "75%";
                                    $textclass = 'green-text';
                                } else if (isset($pr['_source']['flashsale_status']) && $pr['_source']['flashsale_status'] == 'standard') {
                                    $cashBack = "20%";
                                    $textclass = 'blue-text';
                                }
                                ?>
                            </div>
                            <!-- <span class="cashback <//?php// echo $textclass; ?>">
                                <span class="persnt-value"><//?php //echo $cashBack; ?></span> 
                                <span class="cashbk-txt">USDC back</span></span> -->
                        </div>

                        <?php
                        // } else {

                        $cashBack = "2%";
                        $textclass = '';
                        if (isset($pr['_source']['flashsale_status']) && $pr['_source']['flashsale_status'] == 'choosen') {
                            $cashBack = "75%";
                            $textclass = 'green-text';
                        } else if (isset($pr['_source']['flashsale_status']) && $pr['_source']['flashsale_status'] == 'standard') {
                            $cashBack = "20%";
                            $textclass = 'blue-text';
                        }
                        ?>
                        <!-- <div class="cnt-bnt-ct"> -->
                        <!-- <div class="crtbtn align-self-center spacing-side-adj col ">
                                    <a class="fav-login primary-btn btn btn-primary btnorm" data-location="{{URL::to('q/')}}"
                                       href="javascript:void(0)" data-toggle="modal" data-target="#login-registerpopup">Add To Cart</a>

                                </div> -->
                        <!-- <span class="cashback <?php //echo $textclass; 
                                                    ?>"><span class="persnt-value"><?php // echo $cashBack; 
                                                                                                            ?></span> <span class="cashbk-txt">USDC back</span></span> -->
                        <!-- </div> -->
                        <?php // } 
                        ?>
                    </div>
                    <!-- <div class="progress"><div class="progress-bar" style="width:0%"></div></div> -->
                    <!-- Progress bar ends -->

                </div><!-- box-description-mbt -->
            </div>
        </div>
        
<?php } ?>
    <script>
            dataLayer.push({ ecommerce: null }); 
                dataLayer.push({
                event: "view_item_list",
                ecommerce: {
                    item_list_id: "related_products",
                    item_list_name: "Related products",
                    items: [
                        @foreach ($productResults['records'] as $pr) 
                        @php
                            $storeinfo = getStoreInfoByIdApi($pr['_source']['store_id']);
                            $store_name = $storeinfo->store_name;
                            $category = isset($pr['_source']['categories'][0]) ? str_replace('-', ' ', ucfirst($pr['_source']['categories'][0])) : "Other Category";
                        @endphp
                        {
                        item_id: "{{$pr['_source']['product_id']}}",
                        item_name: "{{$pr['_source']['title']}}",
                        item_brand: "{{$store_name}}",
                        item_category: "{{$category}}",
                        item_list_id: "related_products",
                        item_list_name: "Related Products",
                        price: {{$pr['_source']['price']}},
                        quantity: {{$pr['_source']['current_stock']}}
                        },
                        @endforeach
                    ]
                }
            });
        </script>

<?php } ?>


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