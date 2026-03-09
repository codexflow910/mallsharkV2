

<?php
if (Request::ajax()) {
   
    $llCategoreis = getAllCategories();
    $query_category = url()->current() . '?';

    if (isset($_GET['keyword']) && $_GET['keyword'] != '') {
        //$query_category .='keyword=' . $_GET['keyword'] . '&';
    }
    if (isset($_GET['min_price']) && $_GET['min_price'] != '') {
        //$query_category .='min_price=' . $_GET['min_price'] . '&';
    }
    if (isset($_GET['max_price']) && $_GET['max_price'] != '') {
        //$query_category .='max_price=' . $_GET['max_price'] . '&';
    }
    if (isset($_GET['sort_by']) && isset($_GET['sort_order'])) {
        
        // $query_category .='sort_by=' . $_GET['sort_by'] . '&';
        // $query_category .='sort_order=' . $_GET['sort_order'] . '&';
    }

    
    
    $query_String = url()->current();
  
    if (isset($_REQUEST['category_child']) && $_REQUEST['category_child'] != '') {
        $query_String .= '&category_child=' . $_REQUEST['category_child'];
    }
    if (isset($_REQUEST['category_parent']) && $_REQUEST['category_parent'] != '') {
        $query_String .= '&category_parent=' . $_REQUEST['category_parent'];
    }
    // if (isset($_REQUEST['category']) && $_REQUEST['category'] != '') {
    //     $query_String .= '&category=' . $_REQUEST['category'];
    // }
    if (isset($_REQUEST['min_price']) && $_REQUEST['min_price'] != '') {
        $query_String .= '?min_price=' . $_REQUEST['min_price'];
    }
    if (isset($_REQUEST['max_price']) && $_REQUEST['max_price'] != '') {
        $query_String .= '&max_price=' . $_REQUEST['max_price'];
    }
    // if (isset($_REQUEST['keyword']) && $_REQUEST['keyword'] != '') {
    //     $query_String .= '&keyword=' . $_REQUEST['keyword'];
    // }
    if (isset($_REQUEST['condition']) && $_REQUEST['condition'] != '') {
        $query_String .= '&condition=' . $_REQUEST['condition'];
    }
    if (isset($_REQUEST['lat']) && isset($_REQUEST['lon']) && isset($_REQUEST['radius'])) {
        $query_String .= '&lat=' . $_REQUEST['lat'];
        $query_String .= '&lon=' . $_REQUEST['lon'];
        $query_String .= '&radius=' . $_REQUEST['radius'];
    }
    
    $url_cur = $_SERVER['REQUEST_URI'];

    $path = strtok($url_cur, '?'); // Split URL at "?" to get the base URL (path)

    // Initialize query parameters
    $data_param = '';
    $min = $_GET['min_price'] ?? ''; // Set your min price value here
    $max = $_GET['max_price'] ?? ''; // Set your max price value here

    // Check if the URL has query parameters
    if (strpos($url_cur, '?') !== false) {
        // Get the query string part after the "?"
        $urlParts = parse_url($url_cur);
        parse_str($urlParts['query'], $queryParams);
        if(isset($queryParams['sort_by']) && isset($queryParams['sort_order'])){
                
                unset($queryParams['sort_by'], $queryParams['sort_order']);
            
        }
        if ($queryParams && count($queryParams) > 0) {
            $queryParams = http_build_query($queryParams);
            if (substr($url_cur, -1) == '&') {

                if (substr($path, -1) == '?') {
                $query_String = $path . $queryParams;
                }else{
                $query_String = $path . '?' . $queryParams;
                }
            } else {
            
                if (substr($path, -1) == '?') {
                $query_String = $path . $queryParams;
                }else{
                $query_String = $path . '?' . $queryParams;
                }
            }
        } else {
            $queryParams = http_build_query($queryParams);
            $query_String = $path . '?' . $queryParams;
        }
    } else {
        $query_String = $path . '?';
    }
    
}
$url = 'category.landing';
// print_rr($productResults);
// die();

?>

<div class="row search-reults-head mt-0 mb-10 hide-mobile767 " style="margin-bottom:0px!important;">

    <div class="col-sm-12 hide-ipad padleft-7 padright-7 d-flex justify-content-between">
        <span class="found-results-dv">
            <p class="mb-0">

                <?php
                
            if (isset($productResults['total']['value']) && $productResults['total']['value'] > 1) {
                $text_results = 'Results';
            } else {
                $text_results = 'Result';
            }
            if (isset($_GET["keyword"]) && $_GET["keyword"] != '' && isset($productResults['total']) && $productResults['total'] > 0) {
                ?>
                <!-- Results Found For -->
                <span class="search-keywords">{{ $_GET['keyword'] }} 
                    <!-- (<?php echo $productResults['total']['value']; ?>) -->
            </span> <?php } else { ?>
                    <?php
                        $parsedUrl = parse_url($query_String);
                        $dealName = trim($parsedUrl['path'], '/');
                        $category = null;
                        if (isset($parsedUrl['query'])) {
                            parse_str($parsedUrl['query'], $queryParams);
                            $category = $queryParams['category'] ?? null;
                        }
                        if ($category) {
                            ?>
                            <span class="records-found display-inline-block ml-10" style="position:relative; top:18px">
                                <?php  echo ucfirst(str_replace('-', ' ', $category)) . " in " . ucfirst($dealName); ?>
                            </span>
                            <?php
                           
                        } else {
                            ?>
                            <span class="records-found display-inline-block ml-10" style="position:relative; top:18px">
                                <?php echo ucfirst($dealName); ?>
                            </span>
                            <?php
                        }
                    ?>
                <!-- <span class="records-found display-inline-block ml-10" style="position:relative; top:18px"> Found <span
                        class="text-blue"> <?php //isset($productResults['total']) ? $productResults['total']:0
                        ?> </span> {{ $text_results }} (<?php echo $productResults['total']['value']; ?>) </span> -->
                <?php } ?>
                </p>
        </span>
        <div class="d-flex justify-content-between no-padding-section">
            <div class="d-none btn-group btn-breadcrumb breadcrumb-default">
                <?php if (isset($productResults['total']['value']) && $productResults['total']['value'] > 0) {  ?>
                <div itemprop="breadcrumb" style="display:block">
                    <a href="/" class="btn btn-default visible-lg-block visible-md-block">Home</a>

                    <!-- <a href="/q" class="btn btn-default visible-lg-block visible-md-block" ></a> -->
                </div>
                <?php
                    if (isset($_GET['category']) && $_GET['category'] != '') {
                        ?>
                <?php
                       $slugCategoryName = getCategoryBySlug($_GET['category']);
                       if (getChildCategoryParent($_GET['category'])) {
                          $slugParentCategory = getChildCategoryParent($_GET['category']);
                            
                            ?>

                <i class="fa fa-angle-right"></i>
                <a href="{{ route($url , ['slug' => $_GET['category']]) }}"
                    class="btn btn-default visible-lg-block visible-md-block"><?php echo $slugParentCategory->categoryTitle;
                    ?></a>
                <?php }
                        ?>
                <i class="fa fa-angle-right "></i>
                <a href="{{ route($url, ['slug' => $_GET['category']]) }}" class="btn btn-default visible-lg-block visible-md-block"
                    itemprop="name"><?php echo $slugCategoryName; ?></a>
                <!-- <a href="" class="btn btn-default visible-lg-block visible-md-block" >(<?php echo $productResults['total']['value']; ?> Designed)</a> -->
                <?php } ?>

                <?php if (isset($_GET['keyword']) && $_GET['keyword'] != '') { ?>
                <i class="fa fa-angle-right btn"></i>
                <a href="/search?keyword=<?php echo $_GET['keyword']; ?>"
                    class="btn btn-default visible-lg-block visible-md-block"><?php echo str_replace('-', ' ', $_GET['keyword']); ?></a>
                <?php
                    }
                }
                ?>


            </div>


            <div>
                <div class="sec-right">
                    <div class="form-group clearfix mb-0 pull-right-large pull-right">
                        
                        <select class="form-control" id="sorting_dropdown" name="">
                            <option value="{{ $query_String }}">Sort By</option>
                            <option <?php
                            if (isset($_GET['sort_by']) && $_GET['sort_by'] == 'product_id' && isset($_GET['sort_order']) && $_GET['sort_order'] == 'DESC') {
                                echo 'selected';
                            }
                            if (str_ends_with($query_String, '?')) {
                                    echo ' value="' . $query_String . 'sort_by=product_id&sort_order=DESC"';
                                } else {
                                    echo ' value="' . $query_String . '&sort_by=product_id&sort_order=DESC"';
                                }
                                ?>>
                                New to Old
                            </option>
                            <option <?php
                            if (isset($_GET['sort_by']) && $_GET['sort_by'] == 'product_id' && isset($_GET['sort_order']) && $_GET['sort_order'] == 'ASC') {
                                echo 'selected';
                            }

                            if (str_ends_with($query_String, '?')) {
                                    echo ' value="' . $query_String . 'sort_by=product_id&sort_order=ASC"';
                                } else {
                                    echo ' value="' . $query_String . '&sort_by=product_id&sort_order=ASC"';
                                }
                                ?>>
                                Old to New
                            </option>
                            <option <?php
                            if (isset($_GET['sort_by']) && $_GET['sort_by'] == 'price' && isset($_GET['sort_order']) && $_GET['sort_order'] == 'DESC') {
                                echo 'selected';
                            }

                            if (str_ends_with($query_String, '?')) {
                                    echo ' value="' . $query_String . 'sort_by=price&sort_order=DESC"';
                                } else {
                                    echo ' value="' . $query_String . '&sort_by=price&sort_order=DESC"';
                                }
                                ?>>
                                Price: High to Low
                            </option>
                            <option <?php
                            if (isset($_GET['sort_by']) && $_GET['sort_by'] == 'price' && isset($_GET['sort_order']) && $_GET['sort_order'] == 'ASC') {
                                echo 'selected';
                            }

                            if (str_ends_with($query_String, '?')) {
                                    echo ' value="' . $query_String . 'sort_by=price&sort_order=ASC"';
                                } else {
                                    echo ' value="' . $query_String . '&sort_by=price&sort_order=ASC"';
                                }
                                ?>>

                          Price: Low to High
                            </option>

                        </select>
                    </div>
                </div>
            </div>


        </div>



    </div>




</div><!-- search-reults-head -->
