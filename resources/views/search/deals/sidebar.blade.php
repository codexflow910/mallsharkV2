
<div class="col-md-2 col-12 ipad-full-width">

    
        <div class="pos-rel">
            <div class="sidebar-filter pos-absolute mt-minu50 mb-20 mt-20">

                <div class="sidebar-content mobile-mt-0  hide-mobile767">
                   
                <?php
    if (count($allCategories) > 0) {
        ?>
                  @if($slug == '')
                  
                  <h2 class="bros-heading gray-text fnt18" style="padding-left: 0px;">
                    <span class="broser-cate-cion"><i class="material-icons">list</i></span>
                    Browse Categories</h2>
                    @elseif(isset($slug) && ($slug == 'welcome-deal' || $slug == 'best-sellers' || $slug == 'christmas-deal'))
                    <a href="{{route('category.landing',['slug' => $slug])}}" style="display:none;"  class="showallcats shw-all-cts btn btn-simple ">
                     <!-- <i class="fa fa-chevron-left" aria-hidden="true"></i> -->
                     <span class=" fnt18 strong">   <span class="broser-cate-cion"><i class="material-icons">list</i></span> <i class="fa fa-angle-left pr-1"></i>  Browse Categories </span></a>
                  @elseif(isset($slug) && $slug != 'search')
                    <a href="{{url('search?keyword=')}}" style="display:none;"  class="showallcats shw-all-cts btn btn-simple ">
                     <!-- <i class="fa fa-chevron-left" aria-hidden="true"></i> -->
                     <span class=" fnt18 strong">   <span class="broser-cate-cion"><i class="material-icons">list</i></span> <i class="fa fa-angle-left pr-1"></i>  Browse Categories </span></a>
                    @endif
                    <?php
                    $parentClass = '';
                    if (isset($_GET['category']) && $_GET['category'] != '') {
                       
                        $parentClass = 'parent-cat-link';
                        
                    }
                    ?>
                    <!-- <div class="mobileViewSidebarNav"> -->
                     <ul class="nav flex-column flex-nowrap <?php echo $parentClass; ?>" >
                        <input type="hidden" name="url" id="" value="{{$query_category}}" class="url">
                        <?php
                        
                       
                        foreach ($allCategories as $keyParent => $val) {
                            foreach ($val as $keyChild => $value) {
                                # code...
                                
                                $category = getCategoryBySlug($keyChild);
                            $parent_Category_title = isset($category->categoryTitle) ? $category->categoryTitle : ucwords(str_replace('-', ' ', $keyChild));
                            $parent_Category_slug = isset($keyChild) ? $keyChild : '';
                            $parent_Category_id = $category->id;
                            $parent_total = getDealsCategoryProduct($keyChild,$slug) ?? '0';
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
                                       href="javascript:void(0)" slug="{{$keyChild}}">{{$parent_Category_title}}</a><span class="product-items-counter">
                                        (<?php echo $parent_total; ?>)</span>
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
                }
                ?>
            </div>
            <!-- </div> -->


            <div class="sidebar-contentx mt-0 mobile-mt-0 mobile-style-filter bkclr-mbt brtop-0 sndbr-box">
            <h4 class="hide-mobile767 gray-text fnt18"> <!--<i class="material-icons">      filter_list                   </i>  -->
                <span class="filter-text" style="font-weight:bold; font-size:16px">Filter Products</span></h4>
                <div class="price_filter">


                <div class="parent-class"> 
                
                    
                

            </div>

                <div itemscope itemtype="https://schema.org/WebSite">
                <form action="<?php echo route('home.search'); ?>" method="GET" id="search-filters" itemprop="potentialAction" itemscope itemtype="https://schema.org/SearchAction">
                 <meta itemprop="url" content="https://shopzit.mindblaze.net/"/>
                    <!-- <form action="<?php //echo URL::to('/q'); ?>" method="GET" id="search-filters" itemprop="potentialAction" itemscope itemtype="https://schema.org/SearchAction"> -->
                        <meta itemprop="target" content="https://shopzit.mindblaze.net/search?min_price"/>


                        

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
                            @php
                             $accordian_open = '';
                             $collapse = '';
                             $reset_filter = 'd-none';
                             if(isset($_GET['min_price']) && isset($_GET['max_price'])) {
                                $accordian_open = 'opened';
                                $collapse = 'show';
                                $reset_filter = 'd-block';
                             }
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
    }
    </style>
    
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.0/jquery.validate.js"></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/jquery.jssocials/1.4.0/jssocials.min.js"></script>
<script>
   
   
// bind change event to select
   
   
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
    
    // window.history.pushState({urlPath: data_param_url}, null, data_param_url);
    //jQuery('.search-reults-head').remove();
   
    jQuery.ajax({
        url: data_param_url,
        method: 'GET',
        type: 'GET',
        success: function (res) {
            console.log(res);
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
