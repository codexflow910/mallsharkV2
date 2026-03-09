<style>
    .activeBorder{
        border: 1px solid #0065fc!important;
    }
</style>
<div class="col-md-2 col-12 ipad-full-width">

    <?php
                                

    if (count($llCategoreis) > 0) {
        ?>
        <div class="pos-rel">
            <div class="sidebar-filter pos-absolute mt-minu50 mb-20 mt-20">

                <div class="sidebar-content mobile-mt-0  hide-mobile767">
                   
                   
                  @if($slug == '')
                  <h2 class="bros-heading gray-text fnt18" style="padding-left: 0px;">
                    <span class="broser-cate-cion"><i class="material-icons">list</i></span>
                    Browse Categories</h2>
                  @elseif(isset($slug) && $slug != 'search')
                  
                    <a href="{{url('search?keyword=')}}" style="display:none;"  class="showallcats shw-all-cts btn btn-simple ">
                     <!-- <i class="fa fa-chevron-left" aria-hidden="true"></i> -->
                     <span class=" fnt18 strong">   <span class="broser-cate-cion"><i class="material-icons">list</i></span> <i class="fa fa-angle-left pr-1"></i>  Browse Categories </span></a>
                    @endif
                    <?php
                    $parentClass = '';
                    if (isset($slug) && $slug != '' && $slug != 'search') {
                        $parentClass = 'parent-cat-link';
                        
                    }
                    ?>
                     <ul class="nav flex-column flex-nowrap <?php echo $parentClass; ?>">
                        <?php
                    
                        foreach ($llCategoreis as $key => $val) {
                        
                            $parent_Category_title = isset($val['cat_info']['title']) ? $val['cat_info']['title'] : '';
                            $parent_Category_slug = isset($val['cat_info']['slug']) ? $val['cat_info']['slug'] : '';
                            $parent_Category_id = isset($val['cat_info']['id']) ? $val['cat_info']['id'] : '';
                            $parent_totald = isset($val['cat_info']['total']) ? $val['cat_info']['total'] : '0';
                            ?>
                            <?php
                            //echo $_GET['name'];
                            $cl = '';
                            
                            if (isset($slug) && $slug == $parent_Category_slug) {
                                // dd($slug,$parent_Category_slug);
                                $cl = 'parent-active-link';
                                
                            }
                            ?>
                            <li class="nav-item {{$cl}} nav-item nav-list-item">
                                <div  class=" prent-nav-lnk">
                                   
                                    @if(!isset($slug))
                                    <i class="fa fa-angle-right pr-1"></i> 
                                    @else
                                    @if($slug != '')
                                     <span  class="pl-1">All In</span><br>
                                     @endif
                                    
                                    @endif
                                    <?php
                                    $class_have_child = 'no-childs';
                                    if (isset($val['childs']) && count($val['childs']) > 0) {
                                        

                                        $class_have_child = '';
                                        
                                        ?>
                                        <a style="display:inline-block" class="nav-link collapsed parent-nave refreshcate <?php
                                        if (isset($slug) && $slug == $parent_Category_slug) {
                                            echo 'parent-active';
                                        }
                                        ?>" href="javascript:void(0);" data-toggle="collapse"
                                           data-target="#nav-{{$parent_Category_id}}"></a> <?php } ?>
                                    <a  style="display:inline-block" class="nav-link ajax-link refreshcate mallshark-categories <?php echo $class_have_child;?>"
                                       href="{{route('category.landing',['slug'=>$parent_Category_slug])}}">{{$parent_Category_title}}</a><span class="product-items-counter">
                                        (<?php echo $parent_totald; ?>)</span>
                                </div>
                                <?php
                                if (isset($val['childs']) && count($val['childs']) > 0) {
                                    ?>
                                    <div class="collapse" id="nav-{{$parent_Category_id}}" aria-expanded="false">
                                        <ul class="flex-column nav inner-nav-mbt">
                                            <?php foreach ($val['childs'] as $newVal) {
                                                ?>
                                                <li class="nav-item"> <a class="nav-link ajax-link <?php
                                                    if (isset($slug) && $slug == $newVal['slug']) {
                                                        echo 'active';
                                                    }
                                                    ?>"
                                                                        href="{{route('category.landing',['slug' => $parent_Category_slug , 'subSlug'=>isset($newVal['slug']) ? $newVal['slug'] : ''])}}"><i class="fa fa-angle-right pr-2"></i><?php echo isset($newVal['title']) ? $newVal['title'] : ''; ?></a>
                                                </li>
                                            <?php } ?>
                                        </ul>
                                    </div>

                                    <?php
                                }
                            }
                            ?>
                        </li>
                    </ul>
                    <?php
                }
                ?>
            </div>

            <!-- only for  the mobile view design start-->


        
            <div class="mobileShow" style="display: none;">

                <ul class="nav flex-column flex-nowrap <?php echo $parentClass; ?>" style="margin-top:-30px;">
                    <?php
                    foreach ($llCategoreis as $key => $val) {
                        $parent_Category_title = isset($val['cat_info']['title']) ? $val['cat_info']['title'] : '';
                        $parent_Category_slug = isset($val['cat_info']['slug']) ? $val['cat_info']['slug'] : '';
                        $parent_Category_id = isset($val['cat_info']['id']) ? $val['cat_info']['id'] : '';
        
                        $isParentActive = (isset($slug) && $slug == $parent_Category_slug);
                        $isChildActive = false;
                        if (isset($val['childs'])) {
                            foreach ($val['childs'] as $child) {
                                if (isset($slug) && $slug == $child['slug']) {
                                    $isChildActive = true;
                                    break;
                                }
                            }
                        }

                        if ($isParentActive || $isChildActive) {
                            ?>
                            <li class="nav-item parent-active-link nav-list-item" style="display: flex;gap:5px;align-items:center;margin-top:30px;">
                               
                                <?php
                                if (isset($val['childs']) && count($val['childs']) > 0) {
                                    ?>
                                    <?php
                                        if (!empty($val['childs']) && isset($slug)) {
                                            usort($val['childs'], function ($a, $b) use ($slug) {
                                                return ($a['slug'] === $slug) ? -1 : (($b['slug'] === $slug) ? 1 : 0);
                                            });
                                        }
                                        ?>
                                        <div class="collapse <?php echo $isParentActive || $isChildActive ? 'show' : ''; ?>" 
                                            id="nav-<?php echo $parent_Category_id; ?>" 
                                            aria-expanded="<?php echo $isParentActive || $isChildActive ? 'true' : 'false'; ?>" 
                                            style="overflow:auto; scrollbar-width:none; -ms-overflow-style:none;">
                                            <ul class="flex-column nav inner-nav-mbt"
                                                style="display:flex;gap:10px; flex-direction:row!important; flex-wrap:nowrap;white-space:nowrap!important;">
                                                <?php foreach ($val['childs'] as $newVal) { ?>
                                                    <li class="nav-item <?php echo (isset($slug) && $slug == $newVal['slug']) ? 'activeBorder' : ''; ?>" 
                                                        style="border: 1px solid grey;border-radius: 40px;padding: 5px 10px;">
                                                        <a class="nav-link ajax-link <?php echo (isset($slug) && $slug == $newVal['slug']) ? 'active' : ''; ?>"
                                                        href="{{route('category.landing',['slug' => $parent_Category_slug , 'subSlug'=>isset($newVal['slug']) ? $newVal['slug'] : ''])}}">
                                                            <?php echo isset($newVal['title']) ? $newVal['title'] : ''; ?>
                                                        </a>
                                                    </li>
                                                <?php } ?>
                                            </ul>
                                        </div>

                                <?php } ?>
                            </li>
                            <?php
                        }
                    }
                    ?>
                </ul>


            </div>


            <!--  only for the mobile view design End -->

            <div class="sidebar-contentx mt-0 mobile-mt-0 mobile-style-filter bkclr-mbt brtop-0 sndbr-box">
            <h4 class="hide-mobile767 gray-text fnt18"> <!--<i class="material-icons">      filter_list                   </i>  -->
                <span class="filter-text" style="font-weight:bold; font-size:16px">Filter Products</span></h4>
                <div class="price_filter">


                <div class="parent-class"> 
                <form action="<?php echo route('home.search'); ?>" method="GET" id="search-filters" itemprop="potentialAction" itemscope itemtype="https://schema.org/SearchAction">
                    
                </div>



                <div itemscope itemtype="https://schema.org/WebSite">
                 <meta itemprop="url" content="https://shopzit.mindblaze.net/"/>
                    <!-- <form action="<?php //echo URL::to('/q'); ?>" method="GET" id="search-filters" itemprop="potentialAction" itemscope itemtype="https://schema.org/SearchAction"> -->
                        <meta itemprop="target" content="https://shopzit.mindblaze.net/search?min_price"/>


                        <div class="row les-spacing">
                            <?php
                            $filterData = getFilterData(3);
                            if ($filterData['filter_status'] == 1) {
                                ?>

                                <!-- <div class="col-sm-12 location-area" style="display:none">
                                     <div class="filter-bar mb-10">
                                         <p>
                                             <label for="amount">Location Radius (Km):</label>

                                         </p>
                                         <input type="hidden" name="lat" id="loc_lat">
                                         <input type="hidden" name="lon" id="loc_lon">
                                         <div class="range-outer">
                                             <div id="slider-range-min"></div>
                                             <div class="km-shower">
                                                 <input type="text"
                                                        value="<?php echo isset($_GET["radius"]) ? $_GET["radius"] : 5 ?>"
                                                        id="amount" name="radius" readonly style="border:0;">
                                             </div>
                                         </div>
                                     </div>

                                 </div> -->


                            <?php } ?>
                            <?php
                            $constants = config('constants.shopzit_gamification');
                            ?>
                            <div class="col-sm-12 abc">
                                <!-- <div class="form-group clearfix mb-0">
                                    <label for="text" class="mt-0">USDC Back</label>
                                    <select class="form-control" data-prodcut-id="36" id="cashback" name="cashback">
                                        <option value="">all</option>       
                                      //  <option value="{{$constants['STANDARD_CASHBACK']}}" 
                                        <?php
                                        if (isset($_GET["cashback"]) && $_GET["cashback"] == $constants['STANDARD_CASHBACK']) {
                                            echo 'selected';
                                        }
                                        ?>>
                                        //{{$constants['STANDARD_CASHBACK']}}</option> 
                                        <option value="{{$constants['CHOOSEN_CASHBACK']}}" <?php
                                        if (isset($_GET["cashback"]) && $_GET["cashback"] == $constants['CHOOSEN_CASHBACK']) {
                                            echo 'selected';
                                        }
                                        ?>>
                                        //{{$constants['CHOOSEN_CASHBACK']}}</option>    
                                    </select>
                                </div>                                             -->
                            </div>
                            <?php
                            $filterData = getFilterData(2);
                            if ($filterData['filter_status'] == 1) {
                                ?>
                                <!-- <div class="col-6 padright-7">
                                    <div class="form-group">
                                        <label>Price Range</label>
                                        <input type="number"
                                               value="<?php // echo isset($_GET['min_price']) ? $_GET['min_price'] : ''; ?>"
                                               name="min_price" min="1" class="form-control" placeholder="Min">
                                    </div>
                                </div>
                                <div class="col-6 padleft-7">
                                    <div class="form-group">
                                        <label style="visibility: hidden;" class="hide-ipad-portrait">Price</label>
                                        <input type="number" name="max_price" min="1"
                                               value="<?php // echo isset($_GET['max_price']) ? $_GET['max_price'] : ''; ?>"
                                               class="form-control" placeholder="Max">
                                    </div>
                                </div> -->
                            <?php } ?>
                            <!-- Material checked -->
                          
                            <!-- <div class="col-12 padright-7 check-fltr-bx">
                                <div class="form-group">
                                    <label class="custom-control-label form-check-label container-check" for="supported-by">
                                        <input type="checkbox" name="stores" class="custom-control-input form-check-input" id="supported-by" <?php
                                      //  if (isset($_GET['stores']) && $_GET['stores'] != '') {
                                 //           echo 'checked';
                                      //  }
                                        ?>>
                                        <span class="checkmark"></span> 
                                        Supported by your herds
                                    </label>
                                </div>
                            </div> -->

                            <!--  <div class="col-sm-12" >
                                  <label>Condition</label>
                                  <div class="conditional-div">
                                      <select class="form-control" id="" name="condition">
                                          <option value="">all</option>
                                          <option value="new" <?php
                            if (isset($_GET['condition']) && $_GET['condition'] == 'new') {
                                echo 'selected';
                            }
                            ?>>new
                                          </option>
                                          <option value="used" <?php
                            if (isset($_GET['condition']) && $_GET['condition'] == 'used') {
                                echo 'selected';
                            }
                            ?>>used
                                          </option>
                                      </select>
                                  </div>
                              </div> -->
                        </div>

                        <?php
                        $catval = '';
                        // if (isset($slug) && $slug != '' && $slug != 'search') {

                        //     $catval = $slug;
                        //      echo '<input id="name-ajax" type="hidden" name="name" value="' . $catval . '">';
                        // }
                        if (isset($_GET['keyword']) && $_GET['keyword'] != '') {

                            echo '<input type="hidden" name="keyword" value="' . $_GET['keyword'] . '">';
                        }
                        if (isset($_GET['category']) && $_GET['category'] != '') {

                            $catval = $_GET['category'];
                            echo '<input id="name-ajax" type="hidden" name="category" value="' . $catval . '">';
                        }
                        

                        // if (isset($slug) && $slug != '') {

                        //     echo '<input id="name-ajax" type="hidden" name="name" value="' . $_GET['name'] . '">';
                        // }
                        if (isset($_GET['category_parent']) && $_GET['category_parent'] != '') {

                            echo '<input type="hidden" name="category_parent" value="' . $_GET['category_parent'] . '">';
                        }
                        if (isset($_GET['sort_by']) && isset($_GET['sort_order'])) {
                            echo '<input type="hidden" name="sort_by" value="' . $_GET['sort_by'] . '">';
                            echo '<input type="hidden" name="sort_order" value="' . $_GET['sort_order'] . '">';
                        }
                        ?>

                        <!--<div class="checkbox-list mt-20 hide-mobile767">
                                <label class="container-checkbox"> Free Shipping
                                    <input type="checkbox" checked="checked">
                                    <span class="checkmark"></span>
                                </label>
                            </div> -->
                            









                         

                            @php
                             $accordian_open = '';
                             $collapse = '';
                             $reset_filter = 'd-none';
                             if(isset($_GET['min_price']) && isset($_GET['max_price'])) {
                                $accordian_open = 'opened';
                                $collapse = 'show';
                                $reset_filter = 'd-block';
                             }
                            //  dd(isset($_GET['min_price']),isset($_GET['max_price']),$accordian_open,$collapse);
                            @endphp
                            <div class="accordion" id="priceRangeAccordion">
                                <div class="card crdfltr {{$accordian_open}}">
                                    <div class="card-header" id="headingOne">
                                        <h5 class="mb-0">
                                            <button class="btn btn-link" type="button" data-toggle="collapse" data-target="#collapseOne" aria-expanded="false" aria-controls="collapseOne">
                                                Price Range
                                                <span class="float-right">
                                                    <i class="fa fa-plus" aria-hidden="true"></i>
                                                </span>
                                            </button>
                                        </h5>
                                    </div>
                            
                                    <div id="collapseOne" class="collapse {{$collapse}}" aria-labelledby="headingOne" data-parent="#priceRangeAccordion">
                                        <div class="card-body">
                                            <div class="form-group">
                                                <div class="custom-control custom-radio">
                                                    <input type="radio" id="priceRange1" name="priceRange" class="custom-control-input"  
                                                    <?php
                                                    if (isset($_GET['min_price']) && $_GET['min_price'] == '1' && isset($_GET['max_price']) && $_GET['max_price'] == '25') {
                                                        echo 'checked';
                                                    } ?>
                                                    >
                                                    <label class="custom-control-label radio-label" for="priceRange1" min='1' max='25'>$1 - $25</label>
                                                </div>
                                                <div class="custom-control custom-radio">
                                                    <input type="radio" id="priceRange2" name="priceRange" class="custom-control-input"
                                                    <?php
                                                    if (isset($_GET['min_price']) && $_GET['min_price'] == '25' && isset($_GET['max_price']) && $_GET['max_price'] == '50') {
                                                        echo 'checked';
                                                    } ?>
                                                    >
                                                    <label class="custom-control-label radio-label" for="priceRange2" min='25' max='50'>$25 - $50</label>
                                                </div>
                                                <div class="custom-control custom-radio">
                                                    <input type="radio" id="priceRange3" name="priceRange" class="custom-control-input"
                                                    <?php
                                                    if (isset($_GET['min_price']) && $_GET['min_price'] == '50' && isset($_GET['max_price']) && $_GET['max_price'] == '100') {
                                                        echo 'checked';
                                                    } ?>
                                                    >
                                                    <label class="custom-control-label radio-label" for="priceRange3" min='50' max='100'>$50 - $100</label>
                                                </div>
                                                <div class="custom-control custom-radio">
                                                    <input type="radio" id="priceRange4" name="priceRange" class="custom-control-input"
                                                    <?php
                                                    if (isset($_GET['min_price']) && $_GET['min_price'] == '100' && isset($_GET['max_price'])) {
                                                        echo 'checked';
                                                    } ?>
                                                    >
                                                    <label class="custom-control-label radio-label" for="priceRange4" min='100' max=''>$100 and Above</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <div class="apply-filter-btn mt-5 hide-mobile767">

                            <button type="submit" class="btn btn-primary   btn-block check-fields">Apply
                                Filters
                            </button>
                            <div class="mt-15 d-flex justify-content-between ">
                                <a href="javascript:void(0)" class="check-fields {{$reset_filter}} m-auto font-weight-bold" id="reset-filter">Reset
                                    Filters
                                </a>
                                <p class="mb-zero d-none"> <a href="{{url()->current()}}" class="showallcats">All Listings</a></p>
                            </div>
                        </div>
                    </form>

                </div>

                </div>
            </div>

           

        </div><!-- sidebar-filter -->


        <?php
        if (Route::currentRouteName() == 'storehome') {
            ?>
            <div class="sidebar-option-tab">
                <?php
                if (is_object($campaign)) {
                    $pledges_required = $constants['FLASH_SALE_TARGET'] - $campaign->pledges;
                    if ($pledges_required <= 0) {
                        $pledges_perc = '100';
                        $pledges_required_text = $campaign->pledges . ' Pledges of ' . $constants['FLASH_SALE_TARGET'] . ' Goal';
                    } else {
                        $pledges_perc = number_format(($campaign->pledges / $constants['FLASH_SALE_TARGET']) * 100, 2);
                        $pledges_required_text = number_format($pledges_required, 0) . ' Pledges Required';
                    }
                    $main_class = 'blue-box';
                    $cashback_perc = $constants['STANDARD_CASHBACK'];
                    
                    ?>
                    <div class="brdr-panel-mbt clearifx goal-bonus-box <?php echo $main_class; ?>">
                        <div class="golbox-heading ">
                            <h3>Campaign Goal</h3>
                        </div><!-- golbox-heading -->
                        <div class="progress-strip">
                            <div class="progress">
                                <div class="progress-bar" role="progressbar" style="width: <?php echo $pledges_perc; ?>%" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>                                                    
                            <div class="goal-reached text-center">
                                <p><?php echo $pledges_required_text; ?></p>

                            </div>                                                        
                        </div><!-- progress-strip -->
                        <div class="pledges-needed">
                            <div class="bonus-takencnt">
                                <div class="persentage-counter">
                                    <h1><?php echo $cashback_perc; ?> <span>%</span></h1>
                                </div>
                                <div class="persentage-body">
                                    <p>
                                        $ Back on every purchase<br>
                                        when we reach a target of<br>
                                        <span class="clr-blue"><?php echo number_format($constants['FLASH_SALE_TARGET'], 0); ?></span> pledges till<br> 
                                        <span class="clr-blue"><?php echo date('jS F', strtotime($campaign->camp_end)); ?></span>                                                                                                          
                                    </p>
                                </div>
                            </div>                                        
                        </div><!-- pledges-needed -->

                        <?php if (Session::has('userData') && $userId != $storeinfo[0]->user_id) { ?>
                            <div class="make-pledge">
                                <span class="token-counter">
                                    <input id="campaign_pledge" type="number" min="1" max="100" class="" value="" placeholder="Min $1">
                                </span>
                                <a id="pledge_campaign" data-campaign-id="<?php echo $campaign->store_campaign_id; ?>" href="javascript:;">Pledge For Support</a>
                            </div><!-- make-pledge -->
                        <?php } ?>

                    </div>
                <?php } ?>


            </div><!-- sidebar-option-tab -->  
            <?php
        }
        ?>
    </div>
</div>
<style>a.nav-link.collapsed.parent-nave {
        display: none !important;
    }
    a.nav-link.no-childs {
        margin-left: 0px !important;
    }</style>
<script>
    var pagination_counter = 1;

// bind change event to select
    $(document).on('change', '#sorting_dropdown', function (event) {
        pagination_counter = 1;
        var url = $(this).val(); // get selected value
        console.log(url);
        if (url) { // require a URL
            data_param_url = url;
            console.log(data_param_url)
            $(".uploadImages").show();
            //window.history.pushState({urlPath: data_param_url}, null, data_param_url);
            //jQuery('.search-reults-head').remove();
            jQuery.ajax({
                url: data_param_url,
                method: 'GET',
                type: 'GET',
                success: function (res) {
                    window.history.pushState({urlPath: data_param_url}, null, data_param_url);
                    jQuery('.ajax-res').html(res);
                    $(".uploadImages").hide();
                    jQuery('.cdt-bdy').addClass('ajax-loaded');

                }
            });
        }

    });
    $(function () {

        $("#search-filters").submit(function (event) {
            event.preventDefault();
            pagination_counter = 1;
        // Get the checked radio button
        var checkedRadio = $('input[name="priceRange"]:checked');

        // Get the min and max values from the associated label
        var min = checkedRadio.next('label').attr('min');
        var max = checkedRadio.next('label').attr('max');
            $(this).find(":input[type=number],select,input[type=hidden]").filter(function () {

                return !this.value;
            }).attr("disabled", "disabled");
            $('input[name="priceRange"]').removeAttr('name');
            data_param = $(this).serialize();
            
            $('input[type="radio"]').attr('name', 'priceRange');
            let path = window.location.href.split('?')[0];
            let url_cur = window.location.href; // Keep the full URL, including query parameters
         // Initialize data_param to store query parameters

            if (url_cur.includes('?')) {
                let queryString = url_cur.split('?')[1]; // Get the query string part
                if (queryString && queryString.length > 0) {
                    // Check if the URL ends with "&"
                    if (url_cur.endsWith('&')) {
                        data_param += 'min_price=' + min + '&max_price=' + max;
                        data_param_url = path + data_param;
                    } else {
                        if(data_param == ''){
                            data_param += 'min_price=' + min + '&max_price=' + max;
                        }else{
                            data_param += '&min_price=' + min + '&max_price=' + max;
                        }
                        
                        data_param_url = path + '?' + data_param;
                    }
                } else {
                    // If no query parameters are found after "?"
                   
                    data_param += 'min_price=' + min + '&max_price=' + max;
                    data_param_url = url_cur + data_param;
                }
            } else {
                // If no "?" in the URL, add it and append query parameters
                data_param += 'min_price=' + min + '&max_price=' + max;
                data_param_url = path + '?' + data_param;
            }
            $(".uploadImages").show();
            // window.history.pushState({urlPath: data_param_url}, null, data_param_url);
            //jQuery('.search-reults-head').remove();
            jQuery.ajax({
                url: data_param_url,
                method: 'GET',
                type: 'GET',
                success: function (res) {
                    window.history.pushState({urlPath: data_param_url}, null, data_param_url);
                    jQuery('.ajax-res').html(res);
                    $(".uploadImages").hide();

                    $("#search-filters").find(":input[type=number],select,input[type=hidden]").filter(function () {
                        return true;
                    }).attr("disabled", false);
                    jQuery('.cdt-bdy').addClass('ajax-loaded');
                    $('#reset-filter').removeClass('d-none');
                    $('#reset-filter').addClass('d-block');

                }
            });
            return false;
            //return true; // ensure form still submits
        });

        $('#reset-filter').on('click',function(){
            let url = new URL(window.location.href);
          
// Remove 'min_price' and 'max_price' from the query string
            url.searchParams.delete('min_price');
            url.searchParams.delete('max_price');

            // Update the URL in the address bar without reloading (optional)
            window.history.replaceState({}, '', url);
            $('input[name="priceRange"]').prop('checked', false);
            $('#reset-filter').removeClass('d-block');
            $('#reset-filter').addClass('d-none');
            let new_url = window.location.href;
            jQuery.ajax({
                url: new_url,
                method: 'GET',
                type: 'GET',
                success: function (res) {
                    console.log(res);
                    window.history.pushState({urlPath: new_url}, null, new_url);
                    jQuery('.ajax-res').html(res);
                    $(".uploadImages").hide();
                    $("#search-filters").find(":input[type=number],select,input[type=hidden]").filter(function () {
                        return true;
                    }).attr("disabled", false);
                    jQuery('.cdt-bdy').addClass('ajax-loaded');
                }
            });
            return false;
        })
    });

    // jQuery(document).on("click", ".ajax-link, .page-link", function (event) {
    //     pagination_counter = 1;
    //     event.preventDefault();
    //     url_href = jQuery(this).attr('href');
    //     if (url_href != '') {
    //         if (jQuery(this).hasClass('nav-link')) {
    //             $(".uploadImages").show();
    //             jQuery('.nav-item').each(function () {
    //                 jQuery(this).removeClass('parent-active-link');
    //             });
    //             jQuery('.nav-link').each(function () {
    //                 jQuery(this).removeClass('active');
    //             })
    //             if (jQuery(this).prev('a').hasClass('parent-nave')) {

    //                 jQuery(this).parent().parent().addClass('parent-active-link');
    //                 //jQuery(this).prev('a').slideClick();
    //             } else if (jQuery(this).hasClass('no-childs')) {

    //                 jQuery(this).parent().parent().addClass('parent-active-link');
    //                 //jQuery(this).prev('a').slideClick();
    //             } else {
    //                 jQuery(this).addClass('active');
    //             }
    //         }
    //         //window.history.pushState({urlPath: url_href}, null, url_href);
    //         name = urlParam('name', url_href);
    //         if (name != '') {
    //             jQuery('#name-ajax').val(name);
    //             jQuery('.cdt-bdy').addClass('fix-nav');
    //             jQuery('#search-filters')[0].reset()
    //         }
    //         // jQuery('.search-reults-head').remove();
    //         jQuery.ajax({
    //             url: url_href,
    //             method: 'GET',
    //             type: 'GET',
    //             success: function (res) {
    //                 window.history.pushState({urlPath: url_href}, null, url_href);
    //                 jQuery('.ajax-res').html(res);
    //                 $(".uploadImages").hide();
    //                 jQuery('.cdt-bdy').addClass('ajax-loaded');
    //                 jQuery('.nav-list-item').each(function () {
    //                     if (jQuery(this).hasClass('parent-active-link')) {
    //                         jQuery(this).find('.collapsed').trigger('click');
    //                     } else {
    //                         jQuery(this).hide();
    //                         jQuery('.showallcats').show();
    //                     }
    //                 });

    //             }
    //         });
    //     }
    // });
    jQuery(document).ready(function () {
        var path = window.location.pathname; // Returns "/shoes"

// Extract the slug (remove leading/trailing slashes and split)
        var slug = path.split('/').filter(Boolean).pop(); // "shoes"
        let params = new URLSearchParams(window.location.search);
        let category = params.get('category');

        reload = urlParam('reload');
        if (reload) {

            url1 = window.location.href;
            url = url1.split('&');
            //alert(url.length);
            if (url.length > 1) {
                url = url[0];
                window.history.pushState({urlPath: url}, null, url);
                // location.href = url;
                //location.href='/';

            } else {
                url = url1.split('?')[0];
                window.history.pushState({urlPath: url}, null, url);
                //location.href='/';
                //location.href = url;
            }

        }
        if (slug) {
            if(slug != 'search'){
                jQuery('.nav-list-item').hide();
            jQuery('.showallcats').show();
            jQuery('.nav-link.active').parent().parent().parent().parent().addClass('parent-active-link');
            jQuery('.parent-active-link').show();
            jQuery('.cdt-bdy').addClass('ajax-loaded ');
            jQuery('.cdt-bdy').addClass('fix-nav');
            }else if(slug == 'search' && params.has('category')){
               
                jQuery('.nav-list-item').hide();
            
            jQuery('.showallcats').show();
            jQuery('.nav-link.active').parent().parent().parent().parent().addClass('parent-active-link');
            jQuery('.parent-active-link').show();
            jQuery('.cdt-bdy').addClass('ajax-loaded ');
            jQuery('.cdt-bdy').addClass('fix-nav');
            
            }else{
                
            
            jQuery('.showallcats').show();
            jQuery('.nav-link.active').parent().parent().parent().parent().addClass('parent-active-link');
            jQuery('.parent-active-link').show();
            jQuery('.cdt-bdy').addClass('ajax-loaded ');
            jQuery('.cdt-bdy').addClass('fix-nav');
            }
          


        }
        //$(".uploadImages").css('display','none !important');

    });
    jQuery(document).on('click', '.showallcats', function () {
        $(".uploadImages").show();

    });
    jQuery(document).on('click', '.inner-nav-mbt .nav-item', function () {
        jQuery(this).parent().parent().parent().addClass('parent-active-link');
        jQuery('.parent-active-link').show();

    });

   
    jQuery(document).on('click', '.loadmore', function (event) {
     
        max = jQuery(this).attr('data-max');
        if (pagination_counter < max) {
            jQuery(this).hide();
            $(".uploadImages").show();
            pagination_counter++;
            if (window.location.href.includes('?')) {
                var url = window.location.href + "&page=" + pagination_counter;
            } else {
                var url = window.location.href + "?page=" + pagination_counter;
            }
            url = url + '&loadmore=true';
            jQuery.ajax({
                url: url,
                method: 'GET',
                type: 'GET',
                success: function (res) {
                    jQuery('.ajax-res .col-md-99 .results-box-mbt').append(res);
                    $(".uploadImages").hide();
                    jQuery('.cdt-bdy').addClass('ajax-loaded');
                    jQuery('.loadmore').show();
                    if (pagination_counter >= max) {
                         jQuery('.loadmore').hide();
                    }
                }
            });
            if (pagination_counter >= max) {
                jQuery(this).html('No More Records');
            }
            // document.location = url;
        } else {
            jQuery(this).html('No More Records');
        }
    });
    // console.log("window.location.href", window.location.href);
    // var siteurl = window.location.href;
    // var splitUrl = siteurl.split('/');
    
    // $(window).on('popstate', function (event) {

    //      if (splitUrl['3'] == 'q') {
    //         alert('sidebar');

    //     location.reload();
    //     }
    // });
//     $('.refreshcate').click(function() {
//         setTimeout(function(){
//          window.location.reload();
// }, 200);
 
// });

$(function() {
  $("#price-range").slider({
    step: 10,
    range: true, 
    min: 0, 
    max:2000, 
    values: [0, 2000], 
    slide: function(event, ui)
    {
        $("#priceRange").val(ui.values[0] + " - " + ui.values[1]);
        $("#min_price").val(ui.values[0]);
        $("#max_price").val(ui.values[1]);
    }
  });
  $("#priceRange").val($("#price-range").slider("values", 0) + " - " + $("#price-range").slider("values", 1));
  
});

// $(document).ready(function() {
//     $(".mallshark-categories").click(function(event) {
//         event.preventDefault();
//         var href = $(this).attr("href");
//         var timeoutFlag = false;
        
//         setTimeout(function() {
//             if (!timeoutFlag) {
//                 console.log("Redirecting to:", href);
//                 window.location.href = href; 
//                 timeoutFlag = true;
//             }
//         }, 200); 
//     });
// });
</script>

<script>
    // Toggle + and - icon, and background color of the card on collapse
    $('#priceRangeAccordion .collapse').on('shown.bs.collapse', function () {
        $(this).parent().addClass('opened'); // Add grey background to the card
        $(this).parent().find('.fa').removeClass('fa-plus').addClass('fa-minus');
    }).on('hidden.bs.collapse', function () {
        $(this).parent().removeClass('opened'); // Revert to white background
        $(this).parent().find('.fa').removeClass('fa-minus').addClass('fa-plus');
    });
</script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/owl.carousel.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/owl.carousel.js"></script>
