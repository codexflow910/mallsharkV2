<?php
$query_category = url()->current() . '?';
  $slug = request()->segment(1);
  if($slug && $slug != 'brand'){
     $slug = $slug;
  }else{
      $slug = isset($_GET['category']) ? $_GET['category'] : '';
  }
  $aboutus = isset($storeinfo[0]->about_store) ? $storeinfo[0]->about_store : '';
  $policies = isset($shop_policies) ? $shop_policies : '';
?>

<div itemprop="brand" itemscope itemtype="http://schema.org/Brand">
            <div class=" store-home-mbt ">
                <section id="product-sec">
                    <div class=" ">
                        <div class="row cdt-bdy custom-non-padding-dection">


                        <div class="col-md-2 col-12  ipad-full-width">
                           <!-- Search Widget Start  -->
 
                           <div class="search-body-mbt d-none">
                                <form method="get" action="{{ URL::full() }}" class="search-autocomplete-form" id="search-form">
                                <span style="color:#cfcfcf; position: relative;top: 35px; left: 10px; font-size: 18px;"><i class="fa fa-search"></i></span>
                                <input type="hidden" value="{{$storeinfo[0]->store_slug}}" id="storename-brand">
                                    <input style="padding-left:35px" type="text" name="keyword" class="form-control autocomplete-field-storehome" placeholder="Search in Store" value="<?php
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
                                      
                           <!-- Search Widget Ends  -->         
                        @include('search.search-sidebar-storehome')
                        </div>

                          




                           
                            <div class=" col-sm-10 mobwdthctrl">
                                <div class="shadow-custom-main" >

                                <?php if (!empty($productResults) && count($productResults['records']) > 0) { ?>
                                    @include('search.top-filters')

                                    <div class=" proinv" >
                                        <div class=" row " id="results-box-mbt">

                                            @include('search.search-ajax-loadmore')
                                        </div><!-- results-box-mbt -->
                                        <div class="search-ajax-products text-center"></div>
                                        <div class="row">
                                            <div class="custom-pagination col-sm-12 mt-15">
                                                <?php

                                                function remove_url_query($url, $key)
                                                {
                                                    $url = preg_replace('/(?:&|(\?))' . $key . '=[^&]*(?(1)&|)?/i', "$1", $url);
                                                    $url = rtrim($url, '?');
                                                    $url = rtrim($url, '&');
                                                    return $url;
                                                }

                                                $q = [];
                                                if (isset($_GET['status'])) {
                                                    $q['status'] = $_GET['status'];
                                                }
                                                if (isset($_GET['order'])) {
                                                    $q['order'] = $_GET['order'];
                                                }
                                                // echo 'no';

                                                //echo $listingresults->appends($q)->links('pagination.defaultpagination');
                                                //echo paginate($item_per_page, $page_number, $get_total_rows[0], $total_pages, $page_url);

                                                if (isset($productResults['total']) && isset($productResults['records'])) {
                                                    // echo 'yes';
                                                    $per_page = 9;
                                                    $total_records = $productResults['total']['value'];
                                                    $max = $total_records / $per_page;
                                                    $page_number = isset($_GET['page']) ? $_GET['page'] : 1;
                                                    unset($_GET['page']);
                                                    $pageurl = Request::fullUrl();

                                                    // delete edit parameter;
                                                    $pageurl = remove_url_query($pageurl, 'page');
                                                    if (!$_GET) {
                                                        $pageurl = $pageurl . '?';
                                                    }
                                                    if ($productResults['total'] > $per_page) {
                                                        // echo paginate(1, $page_number, 10, $max, $pageurl);
                                                    }
                                                }
                                                ?>
                                            </div>
                                        </div>
                                        <?php
                                       

                                        if ($productResults['total']['value'] > $per_page) {
                                        ?>
                                         <script>
                                            var maxdata = '<?php echo  $max; ?>';
                                        </script>
                                            <!-- <div class="loadmore noman button ldmre-txt brand-search-loadmore " data-max="{{$max}}">
                                                <button type="" class="btn btn-primary btn-simple btn-block check-fields">Load More</button>
                                            </div> -->
                                        <?php } ?>
                                    </div>


                                <?php } else { ?>
                                    <div class="col-sm-12">
                                        @include('search.no-record')
                                        <!-- No record found -->
                                    </div>
                                <?php } ?>
                            </div>
                        </div>
                        </div>
                    </div>
                </section>
            </div>

            <?php if(isset($rating['ratings_reviews']) && count($rating['ratings_reviews']) > 0){ ?>
            <div itemprop="review" itemscope itemtype="https://schema.org/Review" class="container" >
                <section id="reviews-sec">
                    <!-- store average ratings & reviews code starts here by Naveed 2018-12-31-->
                    <h3 class="d-head tb-hdg " id="smoothGoto">Reviews</h3>
                    <div class="average-rating-box d-flex justify-content-between avgbxin" style="display:none!important">
                        <div class="table-cell-row left-row col text-left align-self-center">
                            <div class="star-rating-head">
                                <span class="average-rating">Average Rating:</span>

                                <span>
                                    <?php
                                    $average = number_format($rating['average'], 1);
                                    for ($i = 1; $i <= 5; $i++) {
                                        if ($i <= $average) {
                                            $class = "fa fa-star";
                                        } else {
                                            $class = "fa fa-star-o";
                                        }
                                    ?>
                                        <i class="<?php echo $class; ?>" aria-hidden="true"></i>
                                    <?php } ?>
                                    <span itemprop="ratingValue"><?php echo $average; ?></span>
                                </span>
                            </div>
                        </div><!-- table-cell-row -->

                        <div class="table-cell-row right-row col text-left align-self-center">
                            <?php foreach ($rating['ratings_reviews'] as $key => $ratings_reviews) { ?>
                                <div class="star-row-strip" style="display: inline;">
                                    <span class="star-rating-start">
                                        <span class="sart-counter-mbt"><?php echo $key; ?></span>
                                        <i class="fa fa-star" aria-hidden="true"></i>
                                    </span>

                                    <div class="parent-item-star">
                                        <span class="progress">
                                            <div class="progress-bar" role="progressbar" style="width: <?php echo $ratings_reviews['percentage']; ?>%" aria-valuenow="50" aria-valuemin="0" aria-valuemax="100"></div>
                                        </span>
                                    </div>
                                    <span class="counter-words">{{$ratings_reviews['ratings']}}</span>
                                </div>
                            <?php } ?>
                        </div>

                    </div>
                    <div itemprop="review" itemscope itemtype="https://schema.org/Review">
                        <div class="col text-left align-self-center average-rating-box  listing-ct-review">
                            <div class="review-section-container col text-left align-self-center">
                                <?php
                                foreach ($rating['ratings_reviews'] as $key => $ratings_reviews) {
                                    if (!empty($ratings_reviews['comment_details'])) {
                                        foreach ($ratings_reviews['comment_details'] as $five_comments) {
                                            // $image_url = '//i0.wp.com/picjumbo.com/wp-content/uploads/HNCK8891-1.jpg?resize=1570,1047&amp;quality=50';
                                            if ($five_comments['user_avatar'] != '') {
                                                $image_url = getAttachmentUrlById((int) $five_comments['user_avatar'], 'media');
                                            }
                                            if ($five_comments['feature_image'] != '') {
                                                $product_image_url = getAttachmentUrlById((int) $five_comments['feature_image'], 'media');
                                            }
                                           
                                            $emptyimage = asset('assets/images/personLogo.png');
                                ?>
                                            <div class="row-table d-flex justify-content-between">

                                                <div class="col-sm-10-cell no-padd-left">
                                                    <div class="image-review-cnt">
                                                        <img src="{{!empty($image_url) ?  $image_url : $emptyimage }}" alt="{{$five_comments['title']}}" title="{{$five_comments['title']}}" class="img-fluid" style="height: 85px; width: 85px;">
                                                    </div><!--image-review-cnt -->
                                                    <div class="body-review-cntiner">
                                                        <div class="row-review-head">
                                                            <span class="rating-start-right">
                                                                <div class="rating-strip">
                                                                    <?php
                                                                    for ($i = 1; $i <= 5; $i++) {
                                                                        if ($i <= $key) {
                                                                            $class = "fa fa-star";
                                                                        } else {
                                                                            $class = "fa fa-star-o";
                                                                        }
                                                                    ?>
                                                                        <i class="<?php echo $class; ?>" aria-hidden="true"></i>
                                                                    <?php } ?>
                                                                </div>
                                                            </span>
                                                            <span class="name-show-rev" itemprop="author"><?php echo $five_comments['name']; ?></span>
                                                            <span class="seprator-line"> | </span>
                                                            <span class="date-reivew-rev" itemprop="datePublished" content="<?php echo $five_comments['created_at']; ?>"><?php echo $five_comments['created_at']; ?></span>
                                                        </div>
                                                        <div class="body-review-text-bdy">
                                                            <h4>This is nice review caption</h4>
                                                            <p class="fts" itemprop="reviewBody">{{$five_comments['comment']}}</p>
                                                        </div>
                                                    </div><!--body-review-container -->
                                                </div>
                                            </div>
                                          <?php
                                        }
                                    }
                                }
                                ?>

                            </div>
                        </div>
                    </div>
                </section>
            </div>
            <?php } ?>
            <section id="about-sec">
                <div class="container">
                    <div class="row">
                        <div class="col-sm-12">
                            <div class="rwboxin">
                                <div class="">
                                    <h3 class="d-head">Our Story</h3>
                                    <p itemprop="description">
                                        {!!$aboutus!!}
                                    </p>
                                    <br>
                                    <h3 class="d-head">Returns, Exchanges, Store Policies</h3>
                                    <?php if ($policies != '') { ?>
                                        <p>Last Updated on <?php echo date('d M, Y', strtotime($date_updated)); ?></p>
                                        <p>{!!$policies!!}</p>
                                    <?php
                                    } else {
                                        echo '<p style="margin-bottom:0px;">No shipping policies added</p>';
                                    }
                                    ?>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
            </section>



        </div>

<script>
        $(document).ready(function () {
    var loading = false;
    var ajax_pagination_counter = 1;

    $(window).off('scroll').on('scroll', function () {
        var ajax = @json($ajax);
        if(ajax){
            const scrollPosition = $(window).scrollTop() + $(window).height();
            const triggerPoint = $(document).height() * 0.7; // 70% of total page height

            // Check if we've reached 70% of the page and aren't currently loading
            if (!loading && scrollPosition >= triggerPoint) {
                loading = true; // Prevent multiple requests

                if (ajax_pagination_counter < maxdata) {
                    ajax_pagination_counter++;

                    let url = window.location.href.includes('?')
                        ? `${window.location.href}&page=${ajax_pagination_counter}`
                        : `${window.location.href}?page=${ajax_pagination_counter}`;
                    url += '&loadmore=true';

                    $.ajax({
                        url: url,
                        method: 'GET',
                        success: function (res) {
                            if (res !== '') {
                                jQuery('#results-box-mbt').append(res);
                                loading = false; // Allow further requests
                            } else {
                                loading = false;
                            }
                        },
                        error: function () {
                            loading = false; // Reset on error
                        }
                    });
                }
            }
        }
    });
});
</script>