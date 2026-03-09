 
           <style>
            .btn-simple:hover{
                background-color: #e7efff;
    color: #fff !important;
            }
           </style>
                <?php
                 $query_category = url()->current() . '?';
                if (count($categories) > 0) {
                    $catList = [];
                    if(isset($productResults['records']) && count($productResults['records']) > 0){
                        foreach($productResults['records'] as $productcate){
                            $cat = getCategoriesByProductID($productcate['_source']['product_id']);
                            foreach($cat as $key=>$item){
        
                                $catList[] = $item['category_id'];
                            }
                            }
                            $unique_array = array_unique($catList);
        
                            $getcategories = array();
                            foreach($unique_array as $detail){
                            $getcategories[] =getCategoriesNameByProductID($detail);
                          
                            }
                    }
                   
                    ?>
                    <div>
                        <div class="sidebar-filter pos-absolute mt-minu50 mb-20">

                            <div class="sidebar-content mobile-mt-0  hide-mobile767 srchbxint">

                                <h3 class="bros-heading gray-text fnt18" style="display:none">
                                <span class="broser-cate-cion"><i class="material-icons">list</i></span>
                                Search</h3>
                                <div>
                    <div class="search-body-mbt">
                        <form method="get" action="<?php echo route('home.search'); ?>" class="search-autocomplete-form" id="search-form">
                        <span style="color:#cfcfcf; position: relative;top: 35px; left: 10px; font-size: 18px;"><i class="fa fa-search"></i></span>
                            <input style="padding-left:35px" type="text " name="keywordstore" class="form-control autocomplete-field-storehome" placeholder="" value="<?php
                                    if (isset($_GET['keywordstore'])) {
                                        echo $_GET['keywordstore'];
                                    }
                                    ?>">
                            <?php if (isset($_GET['category']) && $_GET['category'] != '') {
                                        ?>
                            <input id="category_parentjs" type="hidden" name="category" value="<?php echo $_GET['category']; ?>">
                            <?php
                                } else {
                                    ?>
                            <input id="category_parentjs" type="hidden" name="category" value="">
                            <?php
                                }
                                ?>                        
                            <?php if (isset($_GET['category_parent']) && $_GET['category_parent'] != '') {
                                    ?>
                            <input type="hidden" name="category_parent" value="<?php echo $_GET['category_parent']; ?>">
                            <?php
                                }
                                ?>
                            <?php if (isset($_GET['recent']) && $_GET['recent'] != '') {
                                    ?>
                            <input type="hidden" name="recent" value="<?php echo $_GET['recent']; ?>">
                            <?php
                                }
                                ?>
                            <?php if (isset($_GET['min_price']) && $_GET['min_price'] != '') {
                                    ?>
                            <input type="hidden" name="min_price" value="<?php echo $_GET['min_price']; ?>">
                            <?php
                                }
                                ?>
                            <?php if (isset($_GET['max_price']) && $_GET['max_price'] != '') {
                                    ?>
                            <input type="hidden" name="max_price" value="<?php echo $_GET['max_price']; ?>">
                            <?php
                                }
                                if (isset($_GET['sort_by']) && isset($_GET['sort_order'])) {
                                    echo '<input type="hidden" name="sort_by" value="' . $_GET['sort_by'] . '">';
                                    echo '<input type="hidden" name="sort_order" value="' . $_GET['sort_order'] . '">';
                                }
                                if (isset($_GET['lat']) && isset($_GET['lon']) && isset($_GET['radius'])) {
                                    echo '<input type="hidden" name="lat" value="' . $_GET['lat'] . '">';
                                    echo '<input type="hidden" name="lon" value="' . $_GET['lon'] . '">';
                                    echo '<input type="hidden" name="radius" value="' . $_GET['radius'] . '">';
                                }
                                if (isset($_GET['condition']) && $_GET['condition'] != '') {
                                    echo '<input type="hidden" name="condition" value="' . $_GET['condition'] . '">';
                                }
                                ?>
                        
                        </form>
                    
                    </div>
                </div>
               
                                <?php
                    $parentClass = '';
                    if (isset($_GET['category']) && $_GET['category'] != '') {
                       
                        $parentClass = 'parent-cat-link';
                        
                    }
                    ?>
                      <a href="{{url()->current()}}" style=""  class="showallcats shw-all-cts btn btn-simple ">
                     <!-- <i class="fa fa-chevron-left" aria-hidden="true"></i> -->
                     <span class=" fnt18 strong">   <span class="broser-cate-cion"><i class="material-icons">list</i></span> <i class="fa fa-angle-left pr-1"></i>  Browse Categories </span></a>
                                <a href="{{url()->current()}}" style="display:none;"  class="showallcats shw-all-cts">
                                <!-- <i class="fa fa-chevron-left" aria-hidden="true"></i>  -->
                               
                                <ul class="nav flex-column flex-nowrap <?php echo $parentClass; ?>" >
                        <input type="hidden" name="url" id="" value="{{$query_category}}" class="url">
                        <?php
                        
                       $allCategories = $categories;
                        foreach ($allCategories as $keyParent => $val) {
                            foreach ($val as $keyChild => $value) {
                                # code...
                                
                                $category = getCategoryBySlug($keyChild);
                            $parent_Category_title = isset($category->categoryTitle) ? $category->categoryTitle : ucwords(str_replace('-', ' ', $keyChild));
                            $parent_Category_slug = isset($keyChild) ? $keyChild : '';
                            $parent_Category_id = $category ? $category->id : '';
                            // $parent_total = getDealsCategoryProduct($keyChild,$slug) ?? '0';
                            ?>
                            <?php
                            //echo $_GET['name'];
                            $cl = '';
                            
                            if ($parent_Category_slug) {
                                // dd($slug,$parent_Category_slug);
                                $cl = 'parent-active-link';
                                
                            }
                            ?>
                            <li class="nav-item {{$cl}} nav-item nav-list-item" style="list-style-type:none">
                                <div  class=" prent-nav-lnk">
                                    @if(!isset($_GET['category']))
                                    <i class="fa fa-angle-right pr-1"></i> 
                                    @else
                                    @if(isset($_GET['category']) != '')
                                     <span  class="pl-1">All In</span><br>
                                     @endif
                                    @endif
                                    <?php
                                    $class_have_child = 'no-childs';
                                    if (isset($value['child']) && count($value['child']) > 0) {
                                        

                                        $class_have_child = '';
                                        
                                        ?>
                                        <a style="display:inline-block" class="nav-link collapsed parent-nave refreshcate <?php
                                        
                                        if (isset($_GET['category']) && $_GET['category'] == $parent_Category_slug) {
                                            
                                            echo 'parent-active';
                                        }
                                        ?>" href="javascript:void(0);" data-toggle="collapse"
                                           data-target="#nav-{{$parent_Category_id}}"></a> <?php } ?>
                                    <a  style="display:inline-block" class="cat-link nav-link ajax-link refreshcate mallshark-categories <?php echo $class_have_child;?>"
                                       href="javascript:void(0)" slug="{{$keyChild}}">{{$parent_Category_title}}</a>
                                </div>
                                <?php
                                $collapse = '';
                                if (isset($value['child']) && count($value['child']) > 0) {
                                    if (Request::ajax() && isset($_GET['category'])) {
                                    $collapse = 'show';
                                    }
                                    ?>
                                    <div class="collapse {{$collapse}}" id="nav-{{$parent_Category_id}}" aria-expanded="false">
                                        <ul class="flex-column nav inner-nav-mbt">
                                            
                                            <?php foreach ($value['child'] as $newVal) {
                                                if(isset($newVal)){
                                                    $child_Category = getCategoryBySlug($newVal);
                                                    $child_Category_title = $child_Category->categoryTitle;
                                                }else{
                                                    $child_Category_title = isset($newVal) ? ucwords(str_replace('-', ' ', $newVal)) : '';
                                                }
                                                
                                                
                                                ?>
                                                <li class="nav-item"> <a class="nav-link ajax-link cat-link <?php
                                                    if (isset($_GET['category']) && $_GET['category'] == $newVal) {
                                                        echo 'active';
                                                       
                                                    }
                                                    
                                                    ?>"
                                                                        href="javascript:void(0)" slug="{{$newVal}}"><i class="fa fa-angle-right pr-2"></i><?php echo isset($child_Category_title) ? $child_Category_title : ''; ?></a>
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
                            <div style="margin-top:30px; margin-bottom:30px;  color:#183b56; text-decoration:underline;padding-left:10px; display:none">
                                <span>1654 Sales</span></br>
                                <span>254 Followers</span>
                            </div>
                        <div class="sidebar-content mt-0 mobile-mt-0 mobile-style-filter bkclr-mbt sndbr-box" style="border:0px;margin-top:30px!important;padding:0px;">
                            <!--<i class="material-icons">      filter_list                   </i>  -->

                            
                        </div>


                        




















                        <div class="price_filter">


                <div class="parent-class"> 
                
                    
                </div>



                <div itemscope="" itemtype="https://schema.org/WebSite">
                <form action="https://dev.mallshark.net/search" method="GET" id="search-filters" itemprop="potentialAction" itemscope="" itemtype="https://schema.org/SearchAction">
                 <meta itemprop="url" content="https://shopzit.mindblaze.net/">
                    <!-- <form action="" method="GET" id="search-filters" itemprop="potentialAction" itemscope itemtype="https://schema.org/SearchAction"> -->
                        <meta itemprop="target" content="https://shopzit.mindblaze.net/search?min_price">


                        <div class="row les-spacing">                                        <div class="col-sm-12 abc">
                                                                         
                            </div>
                                                          
                                   
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
                        
                        
                            




<br>




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
                                                        $filter_1_to_25 = '';
                                                        $filter_25_to_50 = '';
                                                        $filter_50_to_100 = '';
                                                        $filter100 = '';
                                                        if(isset($filters['filter_1_to_25']) && !$filters['filter_1_to_25']){
                                                            $filter_1_to_25 = 'd-none';
                                                        }
                                                        if(isset($filters['filter_25_to_50']) && !$filters['filter_25_to_50']){
                                                            $filter_25_to_50 = 'd-none';
                                                        }
                                                        if(isset($filters['filter_50_to_100']) && !$filters['filter_50_to_100']){
                                                            $filter_50_to_100 = 'd-none';
                                                        }
                                                        if(isset($filters['filter100']) && !$filters['filter100']){
                                                            $filter100 = 'd-none';
                                                        }
                                                        @endphp

                                                        <div class="accordion" id="priceRangeAccordion">
                                                                    <div class="card crdfltr  {{$accordian_open}}">
                                                                        <div class="card-header" id="headingOne">
                                                                            <h5 class="mb-0">
                                                                                <button class="btn btn-link" type="button" data-toggle="collapse" data-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                                                                                    Price Range
                                                                                    <span class="float-right">
                                                                                        <i class="fa fa-minus" aria-hidden="true"></i>
                                                                                    </span>
                                                                                </button>
                                                                            </h5>
                                                                        </div>
                                                                
                                                                        <div id="collapseOne" class="collapse {{$collapse}}" aria-labelledby="headingOne" data-parent="#priceRangeAccordion">
                                                                            <div class="card-body">
                                                                                <div class="form-group">
                                                                                    <div class="custom-control custom-radio {{$filter_1_to_25}}">
                                                                                        <input type="radio" id="priceRange1" name="priceRange" class="custom-control-input"  
                                                                                        <?php
                                                                                        if (isset($_GET['min_price']) && $_GET['min_price'] == '1' && isset($_GET['max_price']) && $_GET['max_price'] == '25') {
                                                                                            echo 'checked';
                                                                                        } ?>
                                                                                        >
                                                                                        <label class="custom-control-label radio-label" for="priceRange1" min='1' max='25'>$1 - $25</label>
                                                                                    </div>
                                                                                    <div class="custom-control custom-radio {{$filter_25_to_50}}">
                                                                                        <input type="radio" id="priceRange2" name="priceRange" class="custom-control-input"
                                                                                        <?php
                                                                                        if (isset($_GET['min_price']) && $_GET['min_price'] == '25' && isset($_GET['max_price']) && $_GET['max_price'] == '50') {
                                                                                            echo 'checked';
                                                                                        } ?>
                                                                                        >
                                                                                        <label class="custom-control-label radio-label" for="priceRange2" min='25' max='50'>$25 - $50</label>
                                                                                    </div>
                                                                                    <div class="custom-control custom-radio {{$filter_50_to_100}}">
                                                                                        <input type="radio" id="priceRange3" name="priceRange" class="custom-control-input"
                                                                                        <?php
                                                                                        if (isset($_GET['min_price']) && $_GET['min_price'] == '50' && isset($_GET['max_price']) && $_GET['max_price'] == '100') {
                                                                                            echo 'checked';
                                                                                        } ?>
                                                                                        >
                                                                                        <label class="custom-control-label radio-label" for="priceRange3" min='50' max='100'>$50 - $100</label>
                                                                                    </div>
                                                                                    <div class="custom-control custom-radio {{$filter100}}">
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

                                                                <button type="button" id="submit-apply-filter" class="btn btn-primary   btn-block check-fields">Apply
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
                                /*
                                if ($campaign->product_id == $productDetail['product_id']) {
                                $main_class = 'green-box';
                                $cashback_perc = $constants['CHOOSEN_CASHBACK'];
                                } else {
                                $main_class = 'blue-box';
                                $cashback_perc = $constants['STANDARD_CASHBACK'];
                                }
                                * 
                                */
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
                                                    USDC Back on every purchase<br>
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
                                                <input id="campaign_pledge" type="number" min="1" max="100" class="" value="" placeholder="Min 1 USDC">
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
                <?php
                    }
                    ?>
 
<style>a.nav-link.collapsed.parent-nave {
        display: none !important;
    }
    a.nav-link.no-childs {
        margin-left: 0px !important;
    }</style>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/jquery.jssocials/1.4.0/jssocials.min.js"></script>
<script>
    
    var pagination_counter = 1;

// bind change event to select
    $(document).on('change', '#sorting_dropdown', function (event) {
        pagination_counter = 1;
        var url = $(this).val(); // get selected value

        if (url) { // require a URL
            data_param_url = url;
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
    //     $("#search-form").submit(function(e){
    //     e.preventDefault();
    // });
        
    $('#submit-apply-filter').on('click', (event) => {
    event.preventDefault();
    
    // Trigger form submission manually
    let form = $("#search-filters");
    let checkedRadio = $('input[name="priceRange"]:checked');
    let min = checkedRadio.next('label').attr('min');
    let max = checkedRadio.next('label').attr('max');

    // Disable empty fields
    form.find(":input[type=number],select,input[type=hidden]").filter(function () {
        return !this.value;
    }).attr("disabled", "disabled");

    $('input[name="priceRange"]').removeAttr('name');
    let data_param = form.serialize();
    console.log(data_param);
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

    jQuery.ajax({
        url: data_param_url,
        method: 'GET',
        success: function (res) {
            window.history.pushState({urlPath: data_param_url}, null, data_param_url);
            jQuery('.ajax-res').html(res);
            $(".uploadImages").hide();

            form.find(":input[type=number],select,input[type=hidden]").filter(function () {
                return true;
            }).attr("disabled", false);

            jQuery('.cdt-bdy').addClass('ajax-loaded');
            $('#reset-filter').removeClass('d-none').addClass('d-block');
        }
    });

    return false;
});

     
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
    jQuery(document).on("click", ".ajax-link, .page-link", function (event) {
        pagination_counter = 1;
        event.preventDefault();
        url_href = jQuery(this).attr('href');
        if (url_href != '') {
            if (jQuery(this).hasClass('nav-link')) {
                $(".uploadImages").show();
                jQuery('.nav-item').each(function () {
                    jQuery(this).removeClass('parent-active-link');
                });
                jQuery('.nav-link').each(function () {
                    jQuery(this).removeClass('active');
                })
                if (jQuery(this).prev('a').hasClass('parent-nave')) {

                    jQuery(this).parent().parent().addClass('parent-active-link');
                    //jQuery(this).prev('a').slideClick();
                } else if (jQuery(this).hasClass('no-childs')) {

                    jQuery(this).parent().parent().addClass('parent-active-link');
                    //jQuery(this).prev('a').slideClick();
                } else {
                    jQuery(this).addClass('active');
                }
            }
            //window.history.pushState({urlPath: url_href}, null, url_href);
            category = urlParam('category', url_href);
            if (category != '') {
                jQuery('#category-ajax').val(category);
                jQuery('.cdt-bdy').addClass('fix-nav');
                jQuery('#search-filters')[0].reset();
            }
            // jQuery('.search-reults-head').remove();
            jQuery.ajax({
                url: url_href,
                method: 'GET',
                type: 'GET',
                success: function (res) {
                    window.history.pushState({urlPath: url_href}, null, url_href);
                    jQuery('.ajax-res').html(res);
                    $(".uploadImages").hide();
                    jQuery('.cdt-bdy').addClass('ajax-loaded');
                    jQuery('.nav-list-item').each(function () {
                        if (jQuery(this).hasClass('parent-active-link')) {
                            jQuery(this).find('.collapsed').trigger('click');
                        } else {
                            jQuery(this).hide();
                            jQuery('.showallcats').show();
                        }
                    });

                }
            });
        }
    });
    jQuery(document).ready(function () {
        category = urlParam('category');
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
        if (category) {
            jQuery('.nav-list-item').hide();
            jQuery('.showallcats').show();
            jQuery('.nav-link.active').parent().parent().parent().parent().addClass('parent-active-link');
            jQuery('.parent-active-link').show();
            jQuery('.cdt-bdy').addClass('ajax-loaded ');
            jQuery('.cdt-bdy').addClass('fix-nav');


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
    var storename = $("#storename-brand").val();
    var requestt;
    jQuery(".autocomplete-field-storehome").autocomplete({
       
        minLength: 2,
        source: function(request, response) {
            requestt = request.term;
            store = storename;
            
            jQuery.ajax({
                url: "/esearch-keyword-store",
                method: "POST",
                data: {
                    keyword: request.term, store: store
                },
                success: function(data) {
                    /*
                     var arr = [];
                     jQuery(data).each(function (key, val)
                     {
                     valnew = val;
                     valnew = valnew.bold();
                     $old_tex=jQuery('#dummytext').html(valnew);
                     show_val=jQuery('#dummytext').html();
                     arr.push(valnew);
                     });
                     
                     */
                    response(data);
                },
                focus: function(event, ui) {

                },
            });
        },
        select: function(event, ui) {

            if (ui.item) {
                val2 = ui.item.value;
                //ParsedElements = $(htmlToParse);
             
                var store_slug= "<?php echo $storeInfo[0]->store_slug ; ?>";
                var res = val2.split(" in "+store_slug);

                    
                    if (res.length > 1) {
                        
                        window.location.href = "/brand/"+store_slug+"?keywordstore=" + requestt;
                        return false;
                    }
                
                $('#category_parentjs').removeAttr("disabled");
            }
            jQuery(".search-autocomplete-form").submit();
        }

    })

    jQuery(document).on('click', '.brand-search-loadmore', function (event) {
        
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
                    jQuery('#results-box-mbt').append(res);
                    // jQuery('.ajax-res .col-md-99 .results-box-mbt').append(res);
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
            //document.location = url;
        } else {
            jQuery(this).html('No More Records');
        }
    });


    $(window).on('popstate', function (event) {

        // if (pathname == '/search-result') {

        location.reload();
        //}
    });

</script>
<style>
    styles.css


h4 {
  color: #000;
  margin-bottom: 20px;
  text-align: center;
  font-weight: 600;
}

.price-content {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin: 0 120px;
}

/* label {
  font-size: 12px;
  font-weight: 500;
}

p {
  font-size: 16px;
  font-weight: 600;
} */

.range-slider {
  width: 400px;
  position: relative;
  margin: 15px 0 30px 0;
}

input[type=range] {
  -webkit-appearance: none;
  width: 100%;
  background: transparent; 
  position: absolute;
  left: 0;
}

input[type=range]::-webkit-slider-thumb {
  -webkit-appearance: none;
  height: 15px;
  width: 15px;
  border-radius: 50%;
  background: #36b37e;
  cursor: pointer;
  margin-top: -5px;
  position: relative;
  z-index: 1;
}

input[type=range]::-webkit-slider-runnable-track {
  width: 100%;
  height: 5px;
  background: #e8e8e8;
  border-radius: 3px;
  border: none;
}
</style>

<script>
$(function() {
  $("#price-range").slider({
    step: 20,
    range: true, 
    min: 0, 
    max:5000, 
    values: [0, 5000], 
    slide: function(event, ui)
    {
        $("#priceRange").val(ui.values[0] + " - " + ui.values[1]);
        $("#min_price").val(ui.values[0]);
        $("#max_price").val(ui.values[1]);
    }
  });
  $("#priceRange").val($("#price-range").slider("values", 0) + " - " + $("#price-range").slider("values", 1));
  
});

$(document).on('click', '.cat-link', function (event) {
        console.log('cat-link')
        slug = $(this).attr('slug');
        url = $('.url').val();
        data_param_url = url + 'category=' + slug;
        window.history.pushState({urlPath: data_param_url}, null, data_param_url);
        jQuery.ajax({
                url: data_param_url,
                method: 'GET',
                type: 'GET',
                async: false,
                success: function (res) {
                    jQuery('.ajax-res').html(res);
                    $(".uploadImages").hide();
                    jQuery('.cdt-bdy').addClass('ajax-loaded');

                }
            });
    })
</script>