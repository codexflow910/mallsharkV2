@extends('layouts.new-main')
@section('content')
@section('title', 'Mallshark - The Best Crypto Enabled Shopping Portal')
@section('description', 'Pay less everyday on food, health and nuitrition products using cryptos (bitcoin, eth). Same day delivery on 100s of items. Ph. Sales +1 4446 4460054.')
@section('keyword', 'MallShark and Crypto E-commerce, Crypto Enabled Shopping portal, Crypto payment gateway')
<!-- @section('keyword', 'Crypto powered Ecommerce, Crypto Enabled Shopping portal, Crypto payment gateway') -->
@section('google-analytics')
@include('layouts.google-analytics')
@stop
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.8.1/slick.css"/>

<style>
    .reward-campaigns-container, .container.mt-35{
        /*        opacity:0;*/
    }
    .gray-background{ border-top: 0px;}
</style>
<h1 style="display: none">Mallshark The Best Crypto Enabled Shopping Portal Home Page</h1>
<section>
    <div class="full-container gray-background" style="display :none;">
        <div class="container">
            <div class="row">
                <div class="col-sm-3">

                    <span class="light-gray-text display-inline-block">Total Supporterss</span>
                    <p><?php echo number_format($totalSupports); ?></p>
                </div>
                <!-- <div class="col-sm-3">
                    <span class="light-gray-text display-inline-block">Total Given Back</span>
                    <p><?php //echo number_format($totalGivenBack); ?></p>
                </div> -->
                <div class="col-sm-3">
                    <span class="light-gray-text display-inline-block">Live Campaigns</span>
                    <p><?php echo number_format($liveCampaigns); ?></p>
                </div>
                <div class="col-sm-3">
                    <span class="light-gray-text display-inline-block">Number Of Brands</span>
                    <p><?php echo number_format($totalStores); ?></p>
                </div>

            </div>
        </div>
    </div>
</section>
<!-- full-container -->
<!-- Active Products -->
@include('home.banners')
@include('home.active-products')
@include('home.top-brands')
@include('home.recent-trades')
@include('home.recent-products')
@include('home.featureflashsale')
@include('home.brandsofweek')   
@include('home.featuredproducts')
@include('home.topsellingproducts')
{{-- @include('home.featureProducts') --}}

<!-- Recents Products -->

<section class="mnbnr" style="display: none;">
    <div class="">
        <div class="">
            <div class="">
                <!-- Main Banner Starts -->

                <div class="home-slide">

                    <div class="jumbotron" style="background-image: url({{ asset('assets/images/banner1.jpg',env('HTTPS_STATUS')) }});">
                        <div class="container">
                            <h2 class="display-3">Bring new ideas to life, anywhere.</h2>
                            <h2>Explore creative projects and help bring bold new ideas into the world with the S6 app.</h2>
                            <p><a class="btn btn-primary btn-lg" href="#" role="button">Learn more »</a></p>
                        </div>
                    </div>
                    <!-- Main Banner Ends -->
                    <div class="jumbotron" style="background-image: url({{ asset('assets/images/banner1.jpg',env('HTTPS_STATUS')) }});">
                        <div class="container">
                            <h2 class="display-3">Bring new ideas to life, anywhere.</h2>
                            <h2>Explore creative projects and help bring bold new ideas into the world with the S6 app.</h2>
                            <p><a class="btn btn-primary btn-lg" href="#" role="button">Learn more »</a></p>
                        </div>
                    </div>
                    <!-- Main Banner Ends -->
                    <div class="jumbotron" style="background-image: url({{ asset('assets/images/banner1.jpg',env('HTTPS_STATUS')) }});">
                        <div class="container">
                            <h2 class="display-3">Bring new ideas to life, anywhere.</h2>
                            <h2>Explore creative projects and help bring bold new ideas into the world with the S6 app.</h2>
                            <p><a class="btn btn-primary btn-lg" href="#" role="button">Learn more »</a></p>
                        </div>
                    </div>
                    <!-- Main Banner Ends -->
                    <div class="jumbotron" style="background-image: url({{ asset('assets/images/banner1.jpg',env('HTTPS_STATUS')) }});">
                        <div class="container">
                            <h2 class="display-3">Bring new ideas to life, anywhere.</h2>
                            <h2>Explore creative projects and help bring bold new ideas into the world with the S6 app.</h2>
                            <p><a class="btn btn-primary btn-lg" href="#" role="button">Learn more »</a></p>
                        </div>
                    </div>
                    <!-- Main Banner Ends -->
                </div>
            </div>     
        </div>
    </div>
</section>


<!-- Content Starts -->
<main >
    <!-- Main Features -->

    <!-- Main Features Ends-->

    <!-- Recent Products -->

    <!-- Recent Products Ends -->
    <!-- Most Popular Starts -->
    <?php
    $filterId = 5;
    $filterData = getFilterData($filterId);
    if ($filterData['filter_status'] == 1) {
        ?>
        <div class="container mt-35 top-selling-container">
            <div class="row">
                <div class="col-md-12">

                    <h3>Top Selling </h3>  
                    <!-- Listing Box Starts -->
                    <div class="row">
                        <?php
                        foreach ($products as $key => $val) {

                            //echo $image_url = getAttachmentUrlById((int)$val->feature_image, 'product');
                            $image_url = getAttachmentUrlById((int) $val->feature_image, 'medium');
                            if (!isImageExists($image_url)) {
                                $image_url = url(asset('assets/images/placeholder.jpg', env('HTTPS_STATUS')));
                            }
                            $reviews = getProductRatingById($val->product_id);
                            $achieved = getPercentageTarget($val->store_id);
                            // $store_info=getStoreInfoById($val->store_id);
                            // print_rr($store_info);die;
                            //$store_icon = $store_info[0]->store_icon;

                            $store_icon = $val->store_icon;
                            $storeIconData = getZAnimalById($store_icon);
                            //$zanimalName = $storeIconData->name;
                            $image_id = getImageIdByIconId((int) $store_icon);
                            $iconUrl = getAttachmentUrlById((int) $image_id, 'icons');
                            ?>
                            <div class="col-md-4">
                                <!-- Product Box -->
                                <div class="card"  >
        <?php $store_slug = isset($val->store_slug) ? $val->store_slug : ''; ?>
                                    <div class="dropdown show animal-dropdown">
                                        <a class=" dropdown-toggle" href="{{URL::to('/brand/'.$store_slug)}}" role="button" id="dropdownMenuLink" aria-expanded="false" target="_blank"  data-toggle="tooltip" data-placement="bottom" title="<?php echo!empty($zanimalName) ? $zanimalName : ""; ?>">
                                            <span class="anml anmls"> 
                                                <img class="img-fluid" src="{{ $iconUrl }}" alt="{{!empty($storeIconData->name) ?$storeIconData->name:'' }}" title="{{!empty($storeIconData->name) ? $storeIconData->name :'' }}">
                                            </span> 
                                        </a>
                                    </div>                                

                                    <a href="/product/<?php echo $val->slug; ?>" class="home-product-img"><img src="{{ $image_url }}" alt="placeholder icon" title="placeholder icon" style="width:100%"></a>
                                    <div class="card-body">
                                        <h4 class="card-title"><a href="/product/<?php echo $val->slug; ?>"> {{$val->title}}</a></h4>
                                        <!--progress bar --><div class="active-compaign ml-min20 mr-min20 pl-20 pr-20 mb-15">
                                            <div class="active-compaing-persent display-inline-block">
                                                <p>Active Campaign
                                                    <span class="text-blue"><?php echo $achieved; ?>%</span>
                                                </p>
                                            </div>
                                            <span class="line-seprator-br" style="display:none;"></span>
                                            <div class="days-left-compaign display-inline-block" style="display:none;">
                                                <p>
                                                    <span class="text-blue"><?php echo rand(1, 6); ?></span>
                                                    Days Left
                                                </p>
                                            </div>

                                        </div>
                                        <!-- <div class="progress"><div class="progress-bar" style="width:<?php echo $achieved; ?>%"></div></div> -->
                                        <!-- Progress bar ends -->
                                        <div class="infbx small  justify-content-between">
                                            <div>
                                                <?php
                                                $productIdd = getTotalSupports($val->store_id);
                                                ?>
                                                <span>Supporters: <b>{{$productIdd}}</b></span><br>
                                                <span>Given Back: <b> <span class="zit-price">USDC {{number_format(getProductGaveBack($val->product_id),2)}}</span></b></span><br>
                                                
                                            </div> 

                                            <div class="text-right">


                                                <span>

        <?php echo getProductReviewAverage($val->product_id); ?>
                                                    <b class="spacing-left-15">  <?php echo productTotalRatings($val->product_id); ?></b> Ratings</span>

                                                <br>
                                                <?php
                                                $authorName = '';
                                                $author = getProductAuthorById($val->product_id);
                                                if (!$author) {
                                                    // $authorName = $author->name;
                                                } else {
                                                    $authorName = $author->name;
                                                }
                                                ?>
                                                By: <a href="{{Url::to('brand/'.$val->store_slug)}}" target="_blank"><span>{{$val->store_name}}</span> </a>
                                            </div>

                                        </div>  
                                    </div>
                                </div>
                                <!-- Product Box Ends -->

                            </div>
                            <?php
                        }
                        ?>
                    </div>   
                    <!-- Listing Box Ends -->     
                </div>      
            </div>
        </div> 
<?php } ?>
    <!-- Most Popular Ends -->

    <section class="bb-1px mb-25 pb-70 bt-1px mt-35 zomeed-slider-home hidden" style="opacity:0;">
        <div class="container">
            <h2 class="text-center mt-40 mb-40 ">Making A Difference</h2>
            <div class="slider common-arrow-slide">

                <article>
                    <div class="col-sm-12 img-box">
                        <img class="img-fluid" src="https://placeimg.com/250/250/animals" alt="Project" title="Project"> 
                    </div>
                    <div class="slider-cntnt-body text-center">
                        <p>My all-time favorite online resource when it comes to shocking my kitchen ...this has been the biggest game-changer since we started using it almost a year ago</p>
                        <h3>-Katie</h3>
                    </div>
                </article>
                <article>
                    <div class="col-sm-12 img-box">
                        <img class="img-fluid" src="https://placeimg.com/250/250/tech" alt="Project" title="Project"> 
                    </div>
                    <div class="slider-cntnt-body text-center">
                        <p>
                            <!-- Lorem ipsum dolor sit amet, consectetur adipisicing elit. Corrupti quos iusto praesentium, vitae officia similique, quo sapiente obcaecati modi quia laborum facere. -->
                        </p>
                        <h3>-Warn</h3>
                    </div>
                </article>
                <article>
                    <div class="col-sm-12 img-box">
                        <img class="img-fluid" src="https://placeimg.com/250/250/nature" alt="Project" title="Project"> 
                    </div>
                    <div class="slider-cntnt-body text-center">
                        <p>My all-time favorite online resource when it comes to shocking my kitchen ...this has been the biggest game-changer since we started using it almost a year ago</p>
                        <h3>-Katie</h3>
                    </div>
                </article>
                <article>
                    <div class="col-sm-12 img-box ">
                        <img class="img-fluid" src="https://placeimg.com/250/250/arch/grayscale" alt="Project" title="Project"> 
                    </div>
                    <div class="slider-cntnt-body text-center">
                        <p>
                            <!-- Lorem ipsum dolor sit amet, consectetur adipisicing elit. Corrupti quos iusto praesentium, vitae officia similique, quo sapiente obcaecati modi quia laborum facere. -->
                        </p>
                        <h3>-Katie</h3>
                    </div>
                </article>
            </div>

        </div>

    </section>

    <div class="home-banner-box mt-30 pt-30 mb-40 pb-30 hidden">
        <div class="container">
            <div class="row">
                <div class="col-sm-6 mr-7 min-h260">
                    <div class="row bAll-1px ">
                        <div class="col-sm-6 pr-0 blue-background">
                            <div class="banner-box-sec ">
                                <h3>How Do ABC<br> Tokens Work <br>And How Do You <br>Get Them?</h3>
                                <a href="javascript:;" class="btn btn-primary btn-simple mt-35">Learn More</a>
                            </div>
                        </div>
                        <div class="col-sm-6 pl-0 pr-0">
                            <div class="banner-box-img">
                                <img class="img-fluid" src="https://placeimg.com/350/350/tech" alt="Project" title="Project">
                            </div>
                        </div>

                    </div>
                </div>


                <div class="col-sm-6 ml-7 min-h260">
                    <div class="row bAll-1px ">
                        <div class="col-sm-6 pr-0">
                            <div class="banner-box-sec white-background">
                                <h3 class="mb-15">Go To Leader Boards</h3>
                                <p>Score Special Points(SP) everytime you help someone out. Build a team and challenge the S6 community</p>
                                <a href="javascript:;" class="btn btn-primary btn-simple mt-15">Learn More</a>
                            </div>
                        </div>
                        <div class="col-sm-6 pl-0 pr-0">
                            <div class="banner-box-img">
                                <img class="img-fluid" src="https://placeimg.com/350/350/tech" alt="Project" title="Project">
                            </div>
                        </div>

                    </div>
                </div>


            </div>
        </div>
    </div>



    <!-- How it works Content -->
    <div class="container markt" style="display: none;">
        <div class="row"> 
            <div class="col-md-12">
                <h2 class="text-center">How Businesses Works With S6?</h2>
                <!-- Inner Content Box -->
                <div class="row mrkcont">
                    <div class="col-md-5"><img class="img-fluid" src="{{ asset('assets/images/prdct.jpg',env('HTTPS_STATUS')) }}" alt="project" title="Project"></div>
                    <div class="col-md-7">
                        <h2>STEP 1</h2>
                        <h3>Get Registered</h3> 
                        <!-- <p>Het is al geruime tijd een bekend gegeven dat een lezer, tijdens het bekijken van de layout van een pagina, afgeleid wordt door de tekstuele inhoud. Het belangrijke punt van het gebruik van Lorem Ipsum is dat het uit een min of meer normale verdeling van letters bestaat, in tegenstelling tot "Hier uw tekst, hier uw tekst" wat het tot min of meer leesbaar nederlands maakt. Veel desktop publishing pakketten en web pagina editors gebruiken tegenwoordig Lorem Ipsum als hun standaard model tekst, en een zoekopdracht naar "lorem ipsum" ontsluit veel websites die nog in aanbouw zijn. </p> -->
                        <p><button type="button" class="btn btn-primary">Learn More</button></p>  
                    </div>
                </div> 
                <div class="row mrkcont">
                    <div class="col-md-7">
                        <h2>STEP 2</h2>
                        <h3>Promote Yourself</h3> 
                        <!-- <p>Het is al geruime tijd een bekend gegeven dat een lezer, tijdens het bekijken van de layout van een pagina, afgeleid wordt door de tekstuele inhoud. Het belangrijke punt van het gebruik van Lorem Ipsum is dat het uit een min of meer normale verdeling van letters bestaat, in tegenstelling tot "Hier uw tekst, hier uw tekst" wat het tot min of meer leesbaar nederlands maakt. Veel desktop publishing pakketten en web pagina editors gebruiken tegenwoordig Lorem Ipsum als hun standaard model tekst, en een zoekopdracht naar "lorem ipsum" ontsluit veel websites die nog in aanbouw zijn. </p> -->
                        <p><button type="button" class="btn btn-primary">Learn More</button></p>  
                    </div>
                    <div class="col-md-5"><img class="img-fluid" src="{{ asset('assets/images/prdct.jpg',env('HTTPS_STATUS')) }}" alt="project" title="Project"></div>
                </div> 
                <!-- Inner Content Ends -->
                <div class="row mrkcont">
                    <div class="col-md-5"><img class="img-fluid" src="{{ asset('assets/images/prdct.jpg',env('HTTPS_STATUS')) }}" alt="project" title="Project"></div>
                    <div class="col-md-7">
                        <h2>STEP 3</h2>
                        <h3>Start Business</h3> 
                        <!-- <p>Het is al geruime tijd een bekend gegeven dat een lezer, tijdens het bekijken van de layout van een pagina, afgeleid wordt door de tekstuele inhoud. Het belangrijke punt van het gebruik van Lorem Ipsum is dat het uit een min of meer normale verdeling van letters bestaat, in tegenstelling tot "Hier uw tekst, hier uw tekst" wat het tot min of meer leesbaar nederlands maakt. Veel desktop publishing pakketten en web pagina editors gebruiken tegenwoordig Lorem Ipsum als hun standaard model tekst, en een zoekopdracht naar "lorem ipsum" ontsluit veel websites die nog in aanbouw zijn. </p> -->
                        <p><button type="button" class="btn btn-primary">Learn More</button></p>  
                    </div>
                </div> 
                <!-- Inner Content Ends -->    

            </div> 
        </div>
    </div>
    <!-- How it works Content Ends -->
    <!-- Bottom Sign Up Section Starts -->
    <?php
    $show = false;
    $link = "";
    // if (Session::has('userData')) {
        
    //     $userid = Session::get('userData')['user_id'];
        
    //     $userroles = getUserRolesId(2);
    //    // echo '$userid: <pre>' .print_r($userroles,true). '</pre>'; die;
    //      if (!empty($userroles) && $userroles !=2  ) {
        
    //         if (!empty($userroles) && $userroles !=2  ) {
    //           //  die('called');
    //         $show = true;
    //         $link = "store/create";
    //     }
    //  }

    // }
     if (!Session::has('userData')) {
       
        $show = true;
        $link = "signup";
    }

    if ($show == true) {
       
        ?>

        <section class="n-market">
            <div class="container">
                <div class="row">
                    <div class="col-md-12">
                        <h2>Start Selling Today</h2>
                        <p><a class="btn btn-primary btn-blue-big" href="<?php echo URL::to($link); ?>">Sign Up Now</a></p> 
                    </div>

                </div>
            </div>
        </section>

<?php }  ?>
    <!-- Bottom Sign Up Section Ends --> 
    <!-- Quick Contact Section Starts -->
    <!--<div class="qckcnt text-center">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <h2>Got A Question?</h2>
                    <h3>Get your answers by visiting our FAQ or contact us via feedback and support requests</h3>
                    <p><a href="<?php echo URL::to('content-page/faqs'); ?>" class="btn btn-primary btn-simple">FAQs</a> <a href="<?php echo URL::to('content-page/contact-us'); ?>" class="btn btn-primary btn-simple">Contact Us</a></p>        
                </div>
            </div>    
        </div>
    </div>  --> 
    <section class="n-quest">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <h2>Got Question?</h2>
                    <h3>Get answer. Visit our FAQ, or contact us with feedback, support requests, or compliments.</h3>
                    <p class="text-center"><a href="<?php echo URL::to('generalfaq'); ?>" class="btn btn-primary btn-simple">FAQs</a> <a href="<?php echo URL::to('contact-us'); ?>" class="btn btn-primary btn-simple">Contact Us</a></p>        
                </div>
            </div>  
        </div>

    </section>
    <!-- Quick Contact Section Ends -->

    <!-- <section class="n-contant">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    
                <p> Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed sollicitudin varius tincidunt. Suspendisse mattis rutrum odio. Nullam gravida varius nulla, rhoncus finibus ante varius non. Aenean at augue ac nibh feugiat iaculis. Proin eu vestibulum sapien. Proin tempor tempus purus et placerat. Nunc volutpat nisl a libero rhoncus pellentesque. Etiam eu augue eros.</p>
                
                <h4> Missing Headline of the Home Page </h4>
                <p> Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed sollicitudin varius tincidunt. Suspendisse mattis rutrum odio. Nullam gravida varius nulla, rhoncus finibus ante varius non. Aenean at augue ac nibh feugiat iaculis. Proin eu vestibulum sapien. Proin tempor tempus purus et placerat. Nunc volutpat nisl a libero rhoncus pellentesque. Etiam eu augue eros.</p>
                </div>
            </div>  
        </div>

    </section> -->
<style>
 a.carousel-control-next, a.carousel-control-prev {
    background: #00000000 !important;
 }
</style>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.8.1/slick.min.js"></script>
    <script>
    jQuery('.home-slide').slick({
        dots: true,
        infinite: false,
        speed: 600,
        slidesToShow: 1,
        arrows: false,
        slidesToScroll: 1,
        responsive: [
            {
                breakpoint: 1024,
                settings: {
                    slidesToShow: 1,
                    slidesToScroll: 1,
                    infinite: true,
                    dots: false
                }
            },
            {
                breakpoint: 600,
                settings: {
                    slidesToShow: 1,
                    slidesToScroll: 1
                }
            },
            {
                breakpoint: 480,
                settings: {
                    slidesToShow: 1,
                    slidesToScroll: 1
                }
            }
            // You can unslick at a given breakpoint now by adding:
            // settings: "unslick"
            // instead of a settings object
        ]
    });
//Playing with Ken Wheeler Slick carousel
//Playing with Ken Wheeler Slick carousel
    $('.slider').slick({
        //dots: true,
        infinite: true,
        centerMode: true,
        centerPadding: '0%',
        slidesToShow: 3,
        speed: 500,
        responsive: [{
                breakpoint: 992,
                settings: {
                    slidesToShow: 3,
                    centerPadding: '0%'
                }
            }]
    });
    /* $(".slick-center").addClass(
     "switch");
     $(".slick-current").prev().addClass(
     "switch");
     $('.slider').on('init', function(currentSlide) {
     console.log(currentSlide);
     $(".slick-center").prev().toggleClass("switch"); 
     }); */

    /* $(".slick-center").addClass(
     "switch");
     $(".slick-current").prev().addClass(
     "switch");
     $('.slider').on('init', function(currentSlide) {
     console.log(currentSlide);
     $(".slick-center").prev().toggleClass("switch"); 
     }); */
    $(document).ready(function () {
//    jQuery('.reward-campaigns-container, .container.mt-35').css('opacity', '1');
        $(".award-campaigns div.filtered-boxed").each(function () {
            $(this).hide();
            if ($(this).attr('id') == 'main') {
                $(this).show();
            }
        });
        $('.award-campaigns .reward-conainer-filter ul li a').on("click", function (e) {
            e.preventDefault();
            var id = $(this).attr('data-related');
            $(".award-campaigns div.filtered-boxed").each(function () {
                $(this).hide();
                if ($(this).attr('id') == id) {
                    $(this).show();
                }
            });
        });
        $('.award-campaigns .reward-conainer-filter ul li a').on('click', function (e) {
            e.preventDefault();
            $('.reward-conainer-filter ul li a.active').removeClass('active');
            $(this).addClass('active');
        });
        $('.award-campaigns .section_project-collection-tabbed-block ul li a').on('click', function (e) {
            e.preventDefault();
            $('.section_project-collection-tabbed-block ul li a.active').removeClass('active');
            $(this).addClass('active');
        });
        $(".award-campaigns div.items-start").each(function () {
            $(this).hide();
            if ($(this).attr('id') == 'most-recents') {
                $(this).show();
            }
        });
        $('.award-campaigns .section_project-collection-tabbed-block ul li a').on("click", function (e) {
            e.preventDefault();
            var id = $(this).attr('data-related');
            $(".award-campaigns div.items-start").each(function () {
                $(this).hide();
                if ($(this).attr('id') == id) {
                    $(this).show();
                }
            });
        });
        jQuery('.active-reward-compaign .show-more-results').on("click", function (e) {
            jQuery(this).toggleClass("showless");
            jQuery('.active-reward-compaign .hide-list-container').slideToggle('fast');
        });
        jQuery('.top-brands-compaign .show-more-results').on("click", function (e) {
            jQuery(this).toggleClass("showless");
            jQuery('.top-brands-compaign .hide-list-container').slideToggle('fast');
        });
        jQuery('.recent-trades-compaign .show-more-results').on("click", function (e) {
            jQuery(this).toggleClass("showless");
            jQuery('.recent-trades-compaign .hide-list-container').slideToggle('fast');
        });
        jQuery('.recent-products-compaign .show-more-results').on("click", function (e) {
            jQuery(this).toggleClass("showless");
            jQuery('.recent-products-compaign .hide-list-container').slideToggle('fast');
        });
    });
    jQuery(document).ready(function () {
        reload = urlParam('reload');
        if (reload) {
            window.history.pushState({urlPath: '/'}, null, '/');
        }
    });
    </script>
</main>
@endsection




