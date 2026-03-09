<?php $child_sub_disable = ''; ?>
<?php
$queryString = url('/search-results') . '?';
$input = app('request')->request->all();
if (count($input) > 0) {
    if (isset($input['parent_category']) && $input['parent_category'] != '') {
        $queryString .='parent_category=' . $input['parent_category'] . '&';
    }

    if (isset($input['child_categories']) && count($input['child_categories']) > 0) {
        $queryString .='child_categories[]=' . $input['child_categories'][0] . '&';
    }
    if (isset($input['child_categories_sub']) && count($input['child_categories_sub']) > 0) {
        $queryString .='child_categories_sub[]=' . $input['child_categories_sub'][0] . '&';
    }
    if (isset($input['value_id']) && count($input['value_id']) > 0) {
        $valuess = $input['value_id'];
        foreach ($valuess as $val) {
            $queryString .='value_id[]=' . $val . '&';
        }
    }
    if (isset($input['location']) && $input['location'] != '') {
        $queryString .='location=' . $input['location'] . '&';
    }
    if (isset($input['price_min']) && $input['price_min'] != '') {
        $queryString .='price_min=' . $input['price_min'] . '&';
    }
    if (isset($input['price_max']) && $input['price_max'] != '') {
        $queryString .='price_max=' . $input['price_max'] . '&';
    }
    if (isset($input['store']) && $input['store'] != '') {
        $queryString .='store=' . $input['store'] . '&';
    }
    if (isset($input['discount']) && $input['discount'] != '') {
        $queryString .='discount=' . $input['discount'] . '&';
    }
}


//print_rr($values);
?>
<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
<div class="search-section-container">
    <div class="search-section-body">
        <div class="container-fluide">
            <div class="col-full flex justify-center">
                <div class="inline-block ">
                    <div class="div-container-search">
                        <div class="search-box all-category-cnt value-category-search">
                            <span class="show-me-value"> Show me</span>
                            <div class="search-box-single">
                                <a href="javascript:;" class="js-filter-delete"><i class="fa fa-times" aria-hidden="true"></i></a>
                                <a href="javascript:;" class="value-select ">
                                    <?php
                                    $parent_category_class = "display-none";
                                    $locationClass = "display-none";
                                    $moreFliters = "display-none";
                                    if (!isset($_GET['parent_category'])) {
                                        echo ' <span class="all-option">All</span>';
                                    }
                                    ?>

                                    <span class="append-contents"><?php
                                        if (isset($_GET['parent_category'])) {
                                            echo $_GET['parent_category'];
                                            if (!isset($_GET['_collaps'])) {
                                                $parent_category_class = "display-block";
                                            }
                                        }
                                        ?></span>
                                </a>
                                <?php
                                $selected_Val = 'All';
                                $classMain = '';
                                if (count($values) > 0) {

                                    $classMain = '';
                                    foreach ($values as $val) {


                                        if (isset($_GET['value_id']) && in_array($val->value_id, $_GET['value_id'])) {

                                            $selected_Val = $val->value_name;
                                            if (!isset($_GET['_collaps'])) {
                                                $classMain = "display-block";
                                            }
                                            $parent_category_class = "display-none";
                                        }
                                    }
                                }

                                if (isset($_GET['location'])) {

                                    $parent_category_class = "display-none";
                                    $classMain = "display-none";
                                    if (!isset($_GET['_collaps'])) {
                                        $locationClass = "display-block";
                                    }
                                }
                                if (isset($_GET['price_min']) || isset($_GET['price_max']) || isset($_GET['store']) || isset($_GET['discount'])) {

                                    $parent_category_class = "display-none";
                                    $classMain = "display-none";
                                    $locationClass = "display-none";
                                    if (!isset($_GET['_collaps'])) {
                                        $moreFliters = "display-block";
                                    }
                                }
                                ?>
                                <div class="absolute-sm clip {{$parent_category_class}} w50-sm">
                                    <div class="category-container">
                                        <div class="container-results-option display-table-cell">
                                            <div class="category-heading-top">
                                                <h5>Categories</h5>
                                            </div>
                                            <div class="js-root-categories">
                                                <ul class="js-categories columns2-sm ">
                                                    <li class="block">
                                                        <a class="block fastclick" href="/search-results">All categories</a>
                                                    </li>
                                                    <div class="single-chosen-mbt ">
                                                        <?php
                                                        $mianForChild = 'Parent';
                                                        $parent_Categories = getAllCategoriesParent();
                                                        $mainCategory = '';


                                                        foreach ($parent_Categories as $key => $val) {
                                                            $selected = "";
                                                            $activeLink = '';

                                                            if (isset($_GET['parent_category']) && $_GET['parent_category'] == $key) {
                                                                $selected = 'selected';
                                                                $activeLink = 'active-link';
                                                                $mianForChild = $val;
                                                                $mainCategory = $key;
                                                            }
                                                            echo '<li class="block">
                                                        <a class="block fastclick ' . $activeLink . '" href="/search-results?parent_category=' . $key . '">' . $val . '</a>
                                                    </li>';
                                                        }
                                                        ?>


                                                </ul>
                                            </div>
                                        </div>
                                        <?php
                                        $childs_sub = array();
                                        $child_class = "display-none";

                                        if (isset($_GET['parent_category']) && $_GET['parent_category'] != '') {
                                            $parent_category_id = getCategoryIdByName($_GET['parent_category']);
                                            $childs_sub = getChildsCategories($parent_category_id);
                                        }
                                        ?>
                                        <!-- container-results-option -->
                                        <?php
                                        if (count($childs_sub) > 0) {
                                            $child_class = "display-block";
                                            ?>
                                            <div class="container-results-option sub-cat-cantainer display-table-cell border-left-single {{$child_class}}">
                                                <div class="category-heading-top">
                                                    <h5>{{$mianForChild}}</h5>
                                                </div>
                                                <div class="js-root-categories">
                                                    <ul class="js-categories columns2-sm ">
                                                        <?php
                                                        $activeLinkAll = 'active-link';

                                                        foreach ($childs_sub as $key => $val) {
                                                            $selected = "";
                                                            $activeLink = '';
                                                            if (isset($_GET['child_categories']) && $_GET['child_categories'] != '') {
                                                                $childs = $_GET['child_categories'];
                                                                if (in_array($key, $childs)) {
                                                                    $selected = 'selected';
                                                                    $activeLink = 'active-link';
                                                                    $mianForChild = $val;
                                                                    $mainCategory_parent = $key;
                                                                    $activeLinkAll = '';
                                                                }
                                                            }

                                                            echo ' <li class="block">
                                                            <a class="block fastclick ' . $activeLink . '" href="/search-results?parent_category=' . $mainCategory . '&child_categories[]=' . $key . '">' . $val . '</a>
                                                        </li>';
                                                        }
                                                        echo ' <li class="block">
                                                            <a class="block fastclick ' . $activeLinkAll . '" href="/search-results?parent_category=' . $mainCategory . '">All</a>
                                                        </li>';
                                                        ?>

                                                    </ul>
                                                </div>
                                            </div>
                                        <?php } ?>
                                        <?php
                                        $childs_sub = array();

                                        if (isset($_GET['child_categories']) && $_GET['child_categories'] != '') {

                                            $sub_categories_ids = getCategoryIdBySlugMultiple($_GET['child_categories']);

                                            $childs_sub = getChildsCategoriesMultiple($sub_categories_ids);
                                        }
                                        ?>

                                        <?php
                                        if (count($childs_sub) > 0) {
                                            $child_class = "display-block";
                                            ?>
                                            <div class="container-results-option sub-cat-cantainer display-table-cell border-left-single {{$child_class}}">
                                                <div class="category-heading-top">
                                                    <h5>{{$mianForChild}}</h5>
                                                </div>
                                                <div class="js-root-categories">
                                                    <ul class="js-categories columns2-sm ">
                                                        <?php
                                                        foreach ($childs_sub as $key => $val) {
                                                            $selected = "";
                                                            $activeLink = '';
                                                            $activeLinkAll = "active-link";
                                                            if (isset($_GET['child_categories_sub']) && $_GET['child_categories_sub'] != '') {
                                                                $childs = $_GET['child_categories_sub'];

                                                                if (in_array($key, $childs)) {
                                                                    $selected = 'selected';
                                                                    $activeLink = 'active-link';
                                                                    $activeLinkAll = '';
                                                                }
                                                            }

                                                            echo ' <li class="block">
                                                            <a class="block fastclick ' . $activeLink . '" href="/search-results?parent_category=' . $mainCategory . '&child_categories[]=' . $mainCategory_parent . '&child_categories_sub[]=' . $key . '">' . $val . '</a>
                                                        </li>';
                                                        }
                                                        echo ' <li class="block">
                                                            <a class="block fastclick ' . $activeLinkAll . '" href="/search-results?parent_category=' . $mainCategory . '&child_categories[]=' . $mainCategory_parent . '">All</a>
                                                        </li>';
                                                        ?>

                                                    </ul>
                                                </div>
                                            </div>
                                        <?php } ?>

                                    </div>
                                    <!-- category-container -->
                                </div>
                            </div>
                            <!-- search-box -->
                        </div>
                        <!-- container-centering -->
                    </div>
                    <!-- inline-block -->

                    <div class="div-container-search">
                        <div class="search-box all-category-cnt values-container-search">
                            <span class="show-me-value"> Values</span>
                            <div class="search-box-single">
                                <a href="javascript:;" class="js-filter-delete">
                                    <i class="fa fa-times" aria-hidden="true"></i>
                                </a>

                                <a href="javascript:;" class="value-select">
                                    <span class="all-option">{{$selected_Val}}</span>
                                    <span class="append-contents"></span>
                                </a>
                                <div class="absolute-sm clip display-none w50-sm left-minus160 {{$classMain}}">
                                    <div class="category-container">
                                        <div class="container-results-option display-table-cell">
                                            <div class="category-heading-top">
                                                <h5>Values</h5>
                                            </div>
                                            <?php
                                            $hrf = '';
                                            if (count($values) > 0) {
                                                ?>
                                                <div class="js-root-categories">
                                                    <?php
                                                    /*
                                                      $hrfOrg = '';
                                                      if (isset($_GET['value_id'])) {
                                                      $currentSelected = $_GET['value_id'];
                                                      foreach ($currentSelected as $val) {
                                                      $hrfOrg .='value_id[]=' . $val . '&';
                                                      }
                                                      }
                                                     * 
                                                     */

                                                    foreach ($values as $val) {
                                                        $class = '';
                                                        $selected_Val = 'All';


                                                        if (isset($_GET['value_id']) && in_array($val->value_id, $_GET['value_id'])) {
                                                            $class = "check-checkbox";
                                                            $selected_Val = $val->value_name;
                                                            $queryString_show = $queryString;
                                                            $hrfOrgUpdate = str_replace("&value_id[]=" . $val->value_id, "", $queryString_show);
                                                            $queryString_show = str_replace("value_id[]=" . $val->value_id, "", $hrfOrgUpdate);
                                                            $queryString_show = $queryString_show . '&';
                                                        } else {
                                                            $queryString_show = $queryString . 'value_id[]=' . $val->value_id . '&';
                                                        }
                                                        ?>
                                                        <a href="{{$queryString_show}}" class="value-anchor">
                                                            <span class="values-selector">

                                                                <label class="{{$class}}">
                                                                    <div class="tick-icon-show">
                                                                        <i class="fa fa-check" aria-hidden="true"></i>
                                                                    </div> 
                                                                    <span class="border img-check">
                                                                        <i class="fa fa-{{$val->value_icon}}" aria-hidden="true"></i>
                                                                    </span>
                                                                   <!-- <input type="checkbox" name="productValues" id="{{$val->value_id}}">-->
                                                                    <span class="text-descption">{{$val->value_name}}</span>
                                                                </label>

                                                            </span>
                                                        </a>
                                                        <?php
                                                    }
                                                    ?>
                                                </div>
                                            <?php } ?>
                                        </div>

                                    </div>
                                    <!-- category-container -->
                                </div>

                            </div>
                        </div>
                    </div>
                    <!-- inline-block -->
                    <?php $location_array = array('1' => 'pakistan', '2' => 'turkey', '3' => 'china', '4' => 'USA', '5' => 'UK');
                    ?>
                    <div class="div-container-search">
                        <div class="search-box all-category-cnt where-container-search common-style-search">
                            <span class="show-me-value"> Where</span>
                            <div class="search-box-single">
                                <a href="javascript:;" class="js-filter-delete">
                                    <i class="fa fa-times" aria-hidden="true"></i>
                                </a>
                                <a href="javascript:;" class="value-select">
                                    <span class="all-option"><?php
                                        if (isset($_GET['location']) && $_GET['location'] != '') {
                                            echo $_GET['location'];
                                        } else {
                                            echo 'All';
                                        }
                                        ?></span>
                                    <span class="append-contents"></span>
                                </a>
                                <div class="absolute-sm clip {{$locationClass}} w50-sm left-minus160" style="z-index:99">
                                    <div class="category-container">
                                        <div class="container-results-option display-table-cell">
                                            <div class="category-heading-top">
                                                <h5>Where</h5>
                                            </div>
                                            <div class="js-root-categories">
                                                @if(count($location_array)>0)
                                                <ul class="js-categories columns2-sm ">
                                                    <li class="block">
                                                        <a class="block fastclick" href="{{$queryString}}">All Countries</a>
                                                    </li>
                                                    <div class="single-chosen-mbt ">
                                                        <?php
                                                        $queryStringLoc = $queryString;
                                                        $location = isset($_GET['location']) ? $_GET['location'] : '';
                                                        if ($location != '') {
                                                            $queryStringLoc = str_replace('&location=' . $location, '', $queryString);
                                                            $queryStringLoc = str_replace('location=' . $location, '', $queryStringLoc);
                                                        }
                                                        foreach ($location_array as $key => $val) {
                                                            $classCurrent = '';
                                                            if ($location != '') {

                                                                if ($val == $_GET['location']) {
                                                                    $classCurrent = 'active-link';
                                                                }
                                                            }
                                                            ?>
                                                            <li class = "block">
                                                                <a class = "block fastclick {{$classCurrent}}" href = "{{$queryStringLoc}}&location={{$val}}">{{$val}}</a>
                                                            </li>

                                                            <?php
                                                        }
                                                        ?>

                                                    </div></ul>
                                                @endif
                                            </div>
                                            <div class="js-root-categories">
                                                <!-- <div class="filter-search-box">
                                                    <input data-query="{{$queryString}}" id="location_auto" type="text" name="location_auto" class="form-control"  value="<?php
                                                if (isset($_GET['location'])) {
                                                    echo $_GET['location'];
                                                }
                                                ?>" placeholder="Search by city, state, country...">
                                                    <button  type="submit"><i class="fa fa-search" aria-hidden="true"></i></button>
                                                </div> -->
                                                <!--<div class="suggestet-search">
                                                    <ul>
                                                        <li>
                                                            <a href="javascript:;">Allover World</a>
                                                        </li>
                                                        <li>
                                                            <a href="javascript:;">USA,</a>
                                                        </li>
                                                        <li>
                                                            <a href="javascript:;">Newyork, USA</a>
                                                        </li>

                                                    </ul>
                                                </div> -->

                                            </div>
                                        </div>

                                    </div>
                                    <!-- category-container -->
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="more-filters-search div-container-search">
                        <div class="search-box all-category-cnt where-container-search common-style-search">
                            <span class="show-me-value"> </span>
                            <div class="search-box-single">
                                <a href="javascript:;" class="js-filter-delete">
                                    <i class="fa fa-times" aria-hidden="true"></i>
                                </a>
                                <a href="javascript:;" class="value-select">
                                    <span class="all-option">More Filters</span>
                                    <span class="append-contents"></span>
                                </a>
                                <div class="absolute-sm clip {{$moreFliters}} w50-sm left-minus160">
                                    <div class="category-container">
                                        <div class="container-results-option display-table-cell">
                                            <div class="category-heading-top">
                                                <h5>Filter</h5>
                                            </div>
                                            <div class="js-root-categories">

                                                <div class="filter-search-box">
                                                    <input type="text" name="" class="form-control" placeholder="3D, Food Truck, Dogs">
                                                    <button type="submit"><i class="fa fa-search" aria-hidden="true"></i></button>
                                                </div>

                                                <div class="row">
                                                    <div class="col-sm-6">
                                                        <div class="suggestet-search">
                                                            <ul>
                                                                <li>
                                                                    <a href="javascript:;">Explore What's Trending</a>
                                                                </li>
                                                                <li>
                                                                    <a href="javascript:;">Discover our brands</a>
                                                                </li>
                                                                <li>
                                                                    <a href="javascript:;">Items of the week</a>
                                                                </li>
                                                                <li>
                                                                    <a href="javascript:;">Explore What's Trending</a>
                                                                </li>
                                                                <li>
                                                                    <a href="javascript:;">Discover our brands</a>
                                                                </li>
                                                                <li>
                                                                    <a href="javascript:;">Items of the week</a>
                                                                </li>

                                                            </ul>
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-6">
                                                        <div class="explore-section-search ">
                                                            <div class="form-group">
                                                                <div class="row">
                                                                    <div class="col-sm-5 dollar-signadded">
                                                                        <label>Price Low</label>
                                                                        <input type="text" name="price_min" id="price_min" class="form-control" value="<?php
                                                                        if (isset($_GET['price_min'])) {
                                                                            echo $_GET['price_min'];
                                                                        } else {
                                                                            echo '0';
                                                                        }
                                                                        ?>">
                                                                        <span class="price-dollar">$</span>
                                                                    </div>
                                                                    <div class="col-sm-2">
                                                                        <div class="h-100 d-table">
                                                                            <span class=" span-seprator d-table-cell align-bottom">To</span>
                                                                        </div>

                                                                    </div>
                                                                    <div class="col-sm-5 dollar-signadded">
                                                                        <label>Price High</label>
                                                                        <input type="text" name="price_max" id="price_max" class="form-control" value="<?php
                                                                        if (isset($_GET['price_max'])) {
                                                                            echo $_GET['price_max'];
                                                                        } else {
                                                                            echo '1000000';
                                                                        }
                                                                        ?>"> <span class="price-dollar">$</span>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="form-group">
                                                                <label>
                                                                    Certification & Awards
                                                                </label>
                                                                <select class="form-control">
                                                                    <option>1 Business Day</option>
                                                                    <option>1-2 Business Day</option>
                                                                    <option>1-3 Business Day</option>
                                                                    <option>3-5 Business Day</option>
                                                                    <option>1-2 Weeks</option>
                                                                    <option>2-3 Weeks</option>
                                                                    <option>3-4 Weeks</option>
                                                                    <option>4-6 Weeks</option>
                                                                    <option>6-8 Weeks</option>
                                                                </select>

                                                            </div>

                                                            <div class="form-group">
                                                                <label>
                                                                    Stores
                                                                </label>
                                                                @if(count($stores)>0)
                                                                <?php
                                                                $selectdVal = isset($_GET['store']) ? $_GET['store'] : '';
                                                                $queryStringStore = $queryString;

                                                                if ($selectdVal != '') {
                                                                    $queryStringStore = str_replace('&store=' . $selectdVal, '', $queryStringStore);
                                                                    $queryStringStore = str_replace('store=' . $selectdVal, '', $queryStringStore);
                                                                }
                                                                ?>
                                                                <select class="form-control" onchange="if (this.value) window.location.href ='{{$queryStringStore}}store = '+this.value">
                                                                    <?php
                                                                    foreach ($stores as $val) {
                                                                        $selected = '';
                                                                        if ($selectdVal == $val->store_id) {
                                                                            $selected = 'selected';
                                                                        }
                                                                        echo '<option value="' . $val->store_id . '" ' . $selected . '>' . $val->shop_name . '</option>';
                                                                    }
                                                                    ?>

                                                                </select>
                                                                @endif
                                                            </div>

                                                            <div class="discounted-product-only">
                                                                <?php
                                                                $selectdVal = isset($_GET['discount']) ? $_GET['discount'] : '';
                                                                $queryStringStore = $queryString;

                                                                if ($selectdVal != '') {
                                                                    $queryStringStore = str_replace('&discount=' . $selectdVal, '', $queryStringStore);
                                                                    $queryStringStore = str_replace('discount=' . $selectdVal, '', $queryStringStore);
                                                                }
                                                                if ($selectdVal == 'true') {
                                                                    $discountlink = $queryStringStore . 'discount=false';
                                                                    $checked = 'checked';
                                                                } else {
                                                                    $discountlink = $queryStringStore . 'discount=true';
                                                                    $checked = '';
                                                                }
                                                                ?>
                                                                <h5 class = "mb-0">

                                                                    <label class = "container-check">
                                                                        <a href = "{{$discountlink}}">
                                                                            <input type = "checkbox" class = "checkbox countryCheckBox" {{$checked}} id = "asia-pacific-check">
                                                                            <span class = "checkmark"></span>
                                                                            Discounted Products Only
                                                                        </a>
                                                                    </label>

                                                                </h5>


                                                            </div>


                                                        </div>
                                                        <!--explore-section search -->
                                                    </div>

                                                </div>

                                            </div>
                                            <!--js-root-categories -->
                                        </div>

                                    </div>
                                    <!--category-container -->
                                </div>
                            </div>
                        </div>

                    </div>
                    <!--more-filters-search -->

                </div>
                <!--inline-block -->

            </div>
        </div>
    </div>

</div>
<span id = "total_query" data-query = "{{$queryString}}"></span>
<!--search-section-container -->




<div class = " border fltrhor" id = "accordion" role = "tablist" style = "display: none;">
    <h6 id = "headingOne" ><a data-toggle = "collapse" href = "#collapseOne" aria-expanded = "false" aria-controls = "collapseOne" class = "collapsed">Search Filter<span class = "pull-right small">Show More</span></a></h6>
    <div class = "timeline-item" id = "form-loader" style = "display:none">
        <div class = "animated-background">
            <div class = "background-masker header-top"></div>
            <div class = "background-masker header-left"></div>
            <div class = "background-masker header-right"></div>
            <div class = "background-masker header-bottom"></div>
            <div class = "background-masker subheader-left"></div>
            <div class = "background-masker subheader-right"></div>
            <div class = "background-masker subheader-bottom"></div>
            <div class = "background-masker content-top"></div>
            <div class = "background-masker content-first-end"></div>
            <div class = "background-masker content-second-line"></div>
            <div class = "background-masker content-second-end"></div>
            <div class = "background-masker content-third-line"></div>
            <div class = "background-masker content-third-end"></div>
        </div>
    </div>
    <form action = "{{route('searchresults')}}" id = "collapseOne" class = "collapse @if(isset($_GET['_collaps']) && $_GET['_collaps']!='') show @endif" role = "tabpanel" aria-labelledby = "headingOne" data-parent = "#accordion" method = "get">

        <div class = "row">
            <!--drop down -->
            <div class = "col-md-4">
                <div class = "single-chosen-mbt ">
                    <?php $parent_Categories = getAllCategoriesParent();
                    ?>
                    <select class="chzn-select search-field top-parent" name="parent_category"   id="selectsinglw">
                        <option value="">Categories</option>
                        <?php
                        foreach ($parent_Categories as $key => $val) {
                            $selected = "";
                            if (isset($_GET['parent_category']) && $_GET['parent_category'] == $key) {
                                $selected = 'selected';
                            }
                            echo '<option value="' . $key . '" ' . $selected . '>' . $val . '</option>';
                        }
                        ?>
                    </select>
                </div>

            </div>
            <!-- drop down ends -->
            <!-- drop down -->
            <?php
            $childs_sub = '';
            if (isset($_GET['parent_category']) && $_GET['parent_category'] != '') {
                $parent_category_id = getCategoryIdByName($_GET['parent_category']);
                $childs_sub = getChildsCategories($parent_category_id);
            }
            ?>
            <div class="col-md-4"> 
                <div class="multiselect-chosen-mbt">
                    <select data-placeholder="Sub Categories" @if($childs_sub=='' || empty($childs_sub)) disabled="disabled" @endif class="chzn-select search-field child-parent" multiple="true" name="child_categories[]" multiple id="child_categories">
                            <option val="">Sub categories</option>
                                <?php
                                if (count($childs_sub) > 0) {
                                    ?>
                                    <?php
                                    if ($childs_sub != '') {
                                        foreach ($childs_sub as $key => $val) {
                                            $selected = "";
                                            if (isset($_GET['child_categories']) && $_GET['child_categories'] != '') {
                                                $childs = $_GET['child_categories'];
                                                if (in_array($key, $childs)) {
                                                    $selected = 'selected';
                                                }
                                            }
                                            echo '<option value="' . $key . '" ' . $selected . '>' . $val . '</option>';
                                        }
                                    }
                                    ?>
                                    <?php
                                } else {
                                    $child_sub_disable = true;
                                }
                                ?>

                    </select>
                </div>
            </div>



            <!-- drop down ends -->

            <!-- drop down -->
            <?php
            $childs_sub = '';
            if (isset($_GET['child_categories']) && $_GET['child_categories'] != '') {

                $sub_categories_ids = getCategoryIdBySlugMultiple($_GET['child_categories']);

                $childs_sub = getChildsCategoriesMultiple($sub_categories_ids);
            }
            ?>
            <div class="col-md-4"> 
                <div class="multiselect-chosen-mbt">
                    <select data-placeholder="Sub Sub Categories" @if($childs_sub=='' || empty($childs_sub) || $child_sub_disable) disabled="disabled" @endif; class="chzn-select search-field child-child" multiple="true" name="child_categories_sub[]" multiple id="child_categories">
                            <option val="">sub sub categories</option>
                                <?php
                                if (count($childs_sub) > 0 && $childs_sub != '' && !$child_sub_disable) {
                                    foreach ($childs_sub as $key => $val) {
                                        $selected = "";
                                        if (isset($_GET['child_categories_sub']) && $_GET['child_categories_sub'] != '') {
                                            $childs = $_GET['child_categories_sub'];
                                            if (in_array($key, $childs)) {
                                                $selected = 'selected';
                                            }
                                        }
                                        echo '<option value="' . $key . '" ' . $selected . '>' . $val . '</option>';
                                    }
                                }
                                ?>
                    </select>
                </div>
            </div>

            <!-- drop down ends -->

            <!-- drop down -->
            <div class="col-md-4"> 
                <div class="dropdown">
                    <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenu2" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        Values
                    </button>
                    <div class="dropdown-menu" aria-labelledby="dropdownMenu2">
                        <div class="valicn"> 
                            <span class="border"><i class="fa fa-american-sign-language-interpreting" aria-hidden="true"></i>
                                <p>High protein</p>
                            </span>
                            <span class="border"><i class="fa fa-car" aria-hidden="true"></i><p>High protein</p></span>
                            <span class="border"><i class="fa fa-balance-scale" aria-hidden="true"></i><p>High fiber</p></span>
                            <span class="border"><i class="fa fa-beer" aria-hidden="true"></i><p>Vegitarian</p></span>
                            <span class="border"><i class="fa fa-certificate" aria-hidden="true"></i><p>Orgranic</p></span>
                            <span class="border"><i class="fa fa-deaf" aria-hidden="true"></i><p>Farm fresh</p></span>
                            <span class="border"><i class="fa fa-heart-o" aria-hidden="true"></i><p>Fresh & freez</p></span>
                            <span class="border"><i class="fa fa-paw" aria-hidden="true"></i><p>High protein</p></span>
                            <span class="border"><i class="fa fa-podcast" aria-hidden="true"></i><p>High protein</p></span>
                            <span class="border"><i class="fa fa-american-sign-language-interpreting" aria-hidden="true"></i><p>High protein</p></span>
                            <span class="border"><i class="fa fa-car" aria-hidden="true"></i><p>Vegitarian</p></span>
                            <span class="border"><i class="fa fa-balance-scale" aria-hidden="true"></i><p>Orgranic</p></span>
                            <span class="border"><i class="fa fa-beer" aria-hidden="true"></i><p>Farm fresh</p></span>
                            <span class="border"><i class="fa fa-certificate" aria-hidden="true"></i><p>Fresh & freez</p></span>
                            <span class="border"><i class="fa fa-deaf" aria-hidden="true"></i><p>High protein</p></span>
                            <span class="border"><i class="fa fa-heart-o" aria-hidden="true"></i><p>High fiber</p></span>
                            <span class="border"><i class="fa fa-paw" aria-hidden="true"></i><p>Vegitarian</p></span>
                            <span class="border"><i class="fa fa-podcast" aria-hidden="true"></i><p>Orgranic</p></span> 
                        </div>
                    </div>
                </div>

            </div>
            <!-- drop down ends -->
            <!-- drop down -->
            <?php
            $brands = getAllBrands();
            if (count($brands) > 0) {
                ?>
                <div class="col-md-4"> 
                    <div class="single-chosen-mbt ">

                        <select class="chzn-select search-field" name="brand">
                            <option value="">Brand</option>
                            <?php
                            foreach ($brands as $val) {
                                $selected = "";
                                if (isset($_GET['brand']) && $_GET['brand'] == $val) {
                                    $selected = 'selected';
                                }
                                echo '<option value="' . $val . '" ' . $selected . '>' . $val . '</option>';
                            }
                            ?>
                        </select>
                    </div>

                </div>
            <?php } ?>
            <!-- drop down ends -->
            <!-- drop down -->
            <div class="col-md-4"> 
                <div class="dropdown">
                    <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenu2" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        Awards & Certificates
                    </button>
                    <div class="dropdown-menu" aria-labelledby="dropdownMenu2">
                        <button class="dropdown-item" type="button">Action</button>
                        <button class="dropdown-item" type="button">Another action</button>
                        <button class="dropdown-item" type="button">Something else here</button>
                    </div>
                </div> 
            </div>
            <!-- drop down ends -->
            <!-- drop down -->
            <div class="col-md-4"> 
                <div class="dropdown">
                    <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenu2" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        Environmental And Social
                    </button>
                    <div class="dropdown-menu" aria-labelledby="dropdownMenu2">
                        <button class="dropdown-item" type="button">Action</button>
                        <button class="dropdown-item" type="button">Another action</button>
                        <button class="dropdown-item" type="button">Something else here</button>
                    </div>
                </div> 
            </div>
            <!-- drop down ends -->
            <!-- drop down -->
            <div class="col-md-4"> 
                <div class="dropdown">
                    <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenu2" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        Ship To
                    </button>
                    <div class="dropdown-menu" aria-labelledby="dropdownMenu2">
                        <button class="dropdown-item" type="button">Action</button>
                        <button class="dropdown-item" type="button">Another action</button>
                        <button class="dropdown-item" type="button">Something else here</button>
                    </div>
                </div> 
            </div>
            <!-- drop down ends -->

            <!-- drop down -->
            <div class="col-md-4"> 
                <div class="row">

                    <div class="col-md-4  ">
                        <div class="form-group">
                            <input type="text" name="price_min" class="form-control" id=" " value="<?php
                            if (isset($_GET['price_min']) && $_GET['price_min'] != '') {
                                echo $_GET['price_min'];
                            }
                            ?>" aria-describedby="" placeholder="Pice Low"> <span class="adj">To</span>
                        </div>
                    </div>
                    <div class="col-md-4  ">
                        <div class="form-group">
                            <input type="text" name="price_max" value="<?php
                            if (isset($_GET['price_max']) && $_GET['price_max'] != '') {
                                echo $_GET['price_max'];
                            }
                            ?>" class="form-control" id=" " aria-describedby="" placeholder="High">
                        </div>
                    </div>
                    <div class="col-md-4 nopad text-left" >
                        <button type="submit" class="btn btn-primary btn-simple btnpd10 text-center submit-form"  style="position:relative; left:0px">
                            <i class="fa fa-play" aria-hidden="true"></i></button>&nbsp;&nbsp;&nbsp;
                        <a href="#" class="btn btn-primary btn-simple btnpd10 text-center form-refresh" data-url="{{route('searchresults')}}"  style="position:relative; left:0px">
                            <i class="fa fa-refresh" aria-hidden="true"></i></a> 
                    </div> 
                </div>
                <!-- drop down ends -->

                <!-- drop down -->
                <!--<div class="col-md-4">  
                    <span class="slnly">
                    <input type="checkbox" id="test5">
                    <label for="test5">On Sale Only</label>
                    </span>   
                 </div>-->
                <!-- drop down ends -->




            </div>

            <input name="_collaps" type="hidden" value="true">
            </form>    
        </div>

</div> 

<script src="https://cdnjs.cloudflare.com/ajax/libs/chosen/1.8.2/chosen.jquery.js"></script>
<script type="text/javascript">
                                                                    $(function () {
                                                                    $(".chzn-select").chosen({
                                                                    width: '100%',
                                                                            disable_search_threshold: 5
                                                                    });
                                                                    });</script>
<script>


     
            jQuery(document).ready(function () {
    jQuery("body").on('click', '.value-select', function () {

    jQuery(this).next('.absolute-sm').toggleClass('display-block');
    jQuery(this).parent('').parent().parent().prevAll('.div-container-search').find('.display-block').removeClass("display-block");
    jQuery(this).parent('').parent().parent().nextAll('.div-container-search').find('.display-block').removeClass("display-block");
    // jQuery(this).parent('').toggleClass("icon-cross");
    });
    jQuery('.absolute-sm').click(function (event) {
    event.stopPropagation();
    });
    var mouse_is_inside = false;
    jQuery(document).ready(function ()
    {
    jQuery('.search-box-single').hover(function () {
    mouse_is_inside = true;
    }, function () {
    mouse_is_inside = false;
    });
    jQuery("body").mouseup(function () {
    if (!mouse_is_inside)
            jQuery('.absolute-sm').removeClass('display-block');
    });
    });
    $(".js-root-categories a.block.fastclick").click(function (event) {
    var textValue = $(this).text();
    // var contentGet=$(textValue).appendTo($(".append-contents"));
    // $(".append-contents").append(textValue);



    var parentValue = $(this).closest('.search-box-single').find(".append-contents");
    var parentAnchorALl = $(this).closest('.search-box-single').find(".all-option");
    //console.log(removeLast);
    // var valueGet  = $(parentValue).append(textValue);

    $(this).closest('.search-box-single').addClass("hide-all-option");
    // var checkData = ( $.trim($(parentAnchorALl).html())); 
    // alert(textValue);

    // $('.sub-cat-cantainer').show();
    $("a.active-link").removeClass("active-link");
    $(this).addClass("active-link");
    $('.value-category-search .category-container').each(function () {
    // Cache the highest
    var highestBox = 0;
    // Select and loop the elements you want to equalise
    $('.container-results-option', this).each(function () {

    // If this box is higher than the cached highest then store it
    if ($(this).height() > highestBox) {
    highestBox = $(this).height();
    }

    });
    // Set the height of all those children to whichever was highest 
    $('.container-results-option', this).height(highestBox);
    });
    })

            // $(".js-filter-delete").click(function(event){
            //     $(this).next('.value-select ').find( ".append-contents" ).hide();
            //      $(this).next('.value-select ').find( ".all-option" ).show();
            // }); 


    });
    jQuery(document).ready(function () {

    jQuery(document).on('change', '.top-parent', function () {
    jQuery('.child-parent').remove();
    jQuery('.child-child').remove();
    });
    jQuery(document).on('change', '.child-parent', function () {
    var $option = jQuery(this).find('option:selected');
    var value = $option.val();
    if (value == 'Sub categories') {
    jQuery(this).remove();
    }
    jQuery('.child-child').remove();
    });
    jQuery(document).on('change', '.child-child', function () {
    var $option = jQuery(this).find('option:selected');
    var value = $option.val();
    if (value == 'sub sub categories') {
    jQuery(this).remove();
    }

    });
    jQuery(document).on('change', '#collapseOne .search-field', function () {
    jQuery('.uploadImages').show();
    jQuery('#collapseOne .submit-form').click();
    })
    });
    jQuery(document).on('submit', '#collapseOne', function () {
    jQuery('.uploadImages').show();
    jQuery('#collapseOne').hide();
    $("#collapseOne *").filter(":input").each(function () {
    if ($(this).val() == '') {
    $(this).prop("disabled", true);
    }

    });
    return true;
    });
    jQuery(document).on('click', '.form-refresh', function () {
    jQuery('#collapseOne').trigger("reset");
    //window.location.href(jQuery(this).attr('data-url'));
    jQuery('.uploadImages').show();
    window.location.assign(jQuery(this).attr('data-url') + '?_collaps=true');
    });
    jQuery(document).ready(function () {
    $("#location_auto").focusout(function () { // focusout method to bind function
    if (jQuery(this).val() == '') {
    url = jQuery(this).attr('data-query');
    var cat = getURLParameter(url, 'location');
    url_rep = '&location=' + cat;
    var res = url.replace(url_rep, "");
    window.location.assign(res);
    }
    });
    $("#price_min").focusout(function () { // focusout method to bind function
    val = jQuery(this).val();
    if (jQuery(this).val() == '') {
    url = jQuery('#total_query').attr('data-query');
    var cat = getURLParameter(url, 'price_min');
    url_rep = '&price_min=' + cat;
    var res = url.replace(url_rep, "");
    jQuery('.uploadImages').show();
    window.location.assign(res);
    }
    if (val != '') {
    url = jQuery('#total_query').attr('data-query');
    var cat = getURLParameter(url, 'price_min');
    if (cat != '') {
    url_rep = '&price_min=' + cat;
    url = url.replace(url_rep, "");
    url_rep = 'price_min=' + cat;
    url = url.replace(url_rep, "");
    }
    url += 'price_min=' + val + '&';
    jQuery('.uploadImages').show();
    window.location.assign(url);
    }
    });
    $("#price_max").focusout(function () { // focusout method to bind function
    val = jQuery(this).val();
    if (jQuery(this).val() == '') {
    url = jQuery('#total_query').attr('data-query');
    var cat = getURLParameter(url, 'price_max');
    url_rep = '&price_max=' + cat;
    var res = url.replace(url_rep, "");
    jQuery('.uploadImages').show();
    window.location.assign(res);
    }
    if (val != '') {
    url = jQuery('#total_query').attr('data-query');
    var cat = getURLParameter(url, 'price_max');
    if (cat != '') {
    url_rep = '&price_max=' + cat;
    url = url.replace(url_rep, "");
    url_rep = 'price_max=' + cat;
    url = url.replace(url_rep, "");
    }
    url += 'price_max=' + val + '&';
    jQuery('.uploadImages').show();
    window.location.assign(url);
    }
    });
    });
    function getURLParameter(url, name) {
    return (RegExp(name + '=' + '(.+?)(&|$)').exec(url) || [, null])[1];
    }
    $("input[name='location_auto']").autocomplete({
    minLength: 4,
            select: function (event, ui) {
            // here I need the input element that have triggered the event
            val = ui['item'].value
                    if (val != '') {
            url = jQuery(this).attr('data-query');
            var cat = getURLParameter(url, 'location');
            if (cat != '') {
            url_rep = '&location=' + cat;
            url = url.replace(url_rep, "");
            url_rep = 'location=' + cat;
            url = url.replace(url_rep, "");
            }
            url += 'location=' + val + '&';
            jQuery('.uploadImages').show();
            window.location.assign(url);
            } else {
            alert('emp');
            }
            },
            source: function (request, response) {
            $.getJSON("{{URL::to('/auto-location')}}", {term: $("input[name='location_auto']").val()},
                    response);
            },
    });


</script>
<style>
    .search-box-single .absolute-sm{
        display:none;
    }
    .display-block{
        display:table-cell !important;
    }
    .uploadImages{
        z-index: 99999999;
    }
</style>

