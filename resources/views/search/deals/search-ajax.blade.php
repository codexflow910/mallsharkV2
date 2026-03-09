
<?php
$allCategories = $categories;
$query_category = url()->current() . '?';

$slug = request()->segment(1);
if($slug && ($slug == 'welcome-deal' || $slug == 'best-sellers' || $slug == 'christmas-deal')){
    $slug = $slug;
}else{
    $slug = request()->segment(1);
    if($slug && $slug != 'search'){
       $slug = $slug;
    }
}
$url = 'category.landing';
?>

    <div class="container   srchbxot default-spacing-bootstrap">
        
            {{-- <h1 class="categorytitle">{{$catetitle}}</h1> --}}
            
                <div class="btn-group btn-breadcrumb breadcrumb-default">
                    <?php if (isset($productResults['total']['value']) && $productResults['total']['value'] > 0) {  ?>
                    <div itemprop="breadcrumb" style="display:block">
                        <a href="/" class="btn btn-default visible-lg-block visible-md-block">Home</a>
    
                        <!-- <a href="/q" class="btn btn-default visible-lg-block visible-md-block" ></a> -->
                    </div>
                   
                   @if(isset($slug) && $slug == '')
                   <i class="fa fa-angle-right "></i>
                   <a href="javascript:void(0)" class="btn btn-default visible-lg-block visible-md-block">Categories</a>
                   @endif
                <?php
                        if (isset($slug) && $slug != '' && ($slug == 'welcome-deal' || $slug == 'best-sellers' || $slug == 'christmas-deal')) {
                           
                            ?>
                    <i class="fa fa-angle-right "></i>
                    <a href="{{route($url,['slug'=>$slug])}}" class="btn btn-default visible-lg-block visible-md-block"
                        itemprop="name"><?php echo str_replace('-', ' ', $slug); ?></a>
                    <!-- <a href="" class="btn btn-default visible-lg-block visible-md-block" >(<?php echo $productResults['total']['value']; ?> Designed)</a> -->
                    <?php }if (isset($_GET['category']) && $_GET['category'] != '') {
                        ?>
                <?php
                       
                            $slugCategoryName = getCategoryBySlug($_GET['category']);
                            if (getChildCategoryParent($_GET['category'])) {
                               $slugParentCategory = getChildCategoryParent($_GET['category']);
                            ?>

    <i class="fa fa-angle-right "></i>
            <a href="{{route($url,['slug'=>$slug])}}?category={{$slugParentCategory->category_slug}}"
                class="btn btn-default visible-lg-block visible-md-block"><?php echo $slugParentCategory->categoryTitle;
                ?></a>
                <i class="fa fa-angle-right "></i>
            <a href="{{route($url,['slug'=>$slug])}}?category={{$_GET['category']}}" class="btn btn-default visible-lg-block visible-md-block"
                itemprop="name"><?php echo $slugCategoryName->categoryTitle; ?></a>
            <?php }else{

            
                    ?>
            <i class="fa fa-angle-right "></i>
            <a href="{{route($url,['slug'=>$slug])}}?category={{$_GET['category']}}" class="btn btn-default visible-lg-block visible-md-block"
                itemprop="name"><?php echo $slugCategoryName->categoryTitle; ?></a>
            <!-- <a href="" class="btn btn-default visible-lg-block visible-md-block" >(<?php echo $productResults['total']['value']; ?> Designed)</a> -->
            <?php }} ?>
    
                    <?php if (isset($_GET['keyword']) && $_GET['keyword'] != '') { ?>
                    <i class="fa fa-angle-right"></i>
                    <a href="/search?keyword=<?php echo $_GET['keyword']; ?>&category=<?php echo isset($_GET['category']) ? $_GET['category'] : '';  ?>"
                        class="btn btn-default visible-lg-block visible-md-block"><?php echo str_replace('-', ' ', $_GET['keyword']); ?></a>
                    <?php
                        }
                    }
                    ?>
    
    
                </div>
            
        <div class="row cdt-bdy">
            @include('search.deals.sidebar')
            <div class=" col-sm-10 main-search-bar-design ">
                <?php
                // if(!isset($_GET['category'])){
                    $imageUrl = '/assets/images/horizontal-banner-template-black-friday-sales_23-2150867247.jpg';
                    if ($imageUrl) {
                    ?>
                    <!-- <div class="category-landing">
                        <div class="landing-image" style="background-image:url({{$imageUrl}}); ">
                           <div class="cat-tex-up">
                          
                        </div>
                        </div>
                        
                    </div> -->
                    <?php
                    }   
                // }
                ?>
                
                <?php if (!empty($productResults) && count($productResults['records']) > 0) { ?>
                    
                    @include('search.deals.top-filters')
                    <div class="col-md-99">

                        <div class="results-box-mbt row">
                            @include('search.search-ajax-loadmore')
                        </div><!-- results-box-mbt -->
                        <div class="search-ajax-products text-center"></div>
                        <div class="row">
                            <div class="custom-pagination col-sm-12 mt-15">
                                <?php
                                $q = [];
                                if (isset($_GET['status'])) {
                                    $q['status'] = $_GET['status'];
                                }
                                if (isset($_GET['order'])) {
                                    $q['order'] = $_GET['order'];
                                }
                                ?>

                            </div>
                        </div>
                        
                    </div>

                <?php } else { ?>
                    <div class="col-sm-99">
                        @include('search.no-record')
                        <!-- No record found -->
                    </div>
                <?php } ?>
                    <?php if(isset($_GET['category'])) {
                     $description = getDescriptionCategorySlug($_GET['category']);
                     ?>
                <!-- <div class="categorytitle  mt-3">
                    <p><?php echo isset($description->description) ? $description->description: ''; ?> Should have a section to place a 100 words subtitle section.
                 And at the bottom of the page should have the option to place category specific 500 words content.</p>
                </div> -->
                <?php }else{ ?>
                    <!-- <div class="subtitle  mt-3">
                    <p>Should have a section to place a 100 words subtitle section.
                 And at the bottom of the page should have the option to place category specific 500 words content.</p>
                </div> -->
                <?php } ?>
            </div>
            
        </div>


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
                        console.log(ajax);
                        console.log({'paginate' : ajax_pagination_counter});
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
                                        $('.ajax-res .col-md-99 .results-box-mbt').append(res);
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
