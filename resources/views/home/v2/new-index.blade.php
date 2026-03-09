@extends('layouts.frontend-main')
@section('content')
@section('title', 'Shop The Most Sharktacular Deals in Town!')
@section('description', 'Shop the best of the best at unbeatable prices. Our handpicked selection features the hottest deals in town on extraordinary items. Grab something new today.')
@section('keyword', 'MallShark Online shopping, Ecommerce platform, Online marketplace, Secure transactions, Convenient shopping experience')
@section('google-analytics')
    @include('layouts.google-analytics')
@stop
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.8.1/slick.css" />
<script type="text/javascript" src="{{ URL::asset('assets/js/sweetalert.js') }}"></script>
<link rel="stylesheet" href="{{ URL::asset('assets/css/sweetalert.css') }}">

<style>
    .reward-campaigns-container,
    .container.mt-35 {
        /*        opacity:0;*/
    }

    .current-stock-deactive {
        pointer-events: none;
    }

    .opecity-deactive {
        opacity: 0.5;
    }

    .gray-background {
        border-top: 0px;
    }
    .sale-price {
        font-weight: 400 !important;
        font-size: 13px;
    }
    .red-color{
        color: red !important;
    }
</style>

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
                    <p><?php //echo number_format($totalGivenBack);
                    ?></p>
                </div> -->
                <div class="col-sm-3">
                    <span class="light-gray-text display-inline-block">Live Campaigns</span>
                    <p></p>
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
@include('home.new-banners')
{{-- @include('home.new-active-products') --}}
{{-- @include('home.new-top-brands') --}}
{{-- @include('home.new-recent-trades') --}}

<!-- Super Deals Starts-->
<div class="container adjut-mobile-layout">
    <div class="row midsec ">
        <div class="col-md-6 pl-0">

            <?php
            if (Session::has('userData')) {
                $userid = isset(Session::get('userData')['user_id']) ? Session::get('userData')['user_id'] : "";
                $storeData = getStoreDataByUserId($userid);
                if (!empty($storeData)) {
                  ?>

            <a href="{{ URL::to('/store-dashboard') }}">
                <div class="midbg">
                    <img src="<?php echo asset('assets/images/MS-banner-1.jpg'); ?>">
                </div>
            </a>
            <?php  }else{ ?>
            <a href="{{ URL::to('/campaign') }}">
                <div class="midbg">
                    <img src="<?php echo asset('assets/images/MS-banner-1.jpg'); ?>">
                </div>
            </a>
            <?php }
            } else { ?>
            <a href="{{ URL::to('/campaign') }}">
                <div class="midbg">
                    <img src="<?php echo asset('assets/images/MS-banner-1.jpg'); ?>">
                </div>
            </a>
            <?php } ?>
        </div>
        <div class="col-md-6 pr-0">
            <div class="row">
                <div class="col-md-6">
                    <a href="{{ URL::to('/campaign') }}">
                        <div class="midbg2 midbg-background mb-3"><img src="<?php echo asset('assets/images/campaign-banner-small.png'); ?>">
                            <img src="<?php echo asset('assets/images/pile-of-dollars.png'); ?>" class="pile-of-dollar" srcset="">
                        </div>
                        <a>
                            <a href="{{ URL::to('/comingsoon') }}">
                                <div class="midbg2"><img src="<?php echo asset('assets/images/MS-banner-3.jpg'); ?>"></div>
                                <a>
                </div>
                <div class="col-md-6">
                    <a href="{{ URL::to('/comingsoon') }}">
                        <div class="midbg"><img src="<?php echo asset('assets/images/MS-banner-4.jpg'); ?>"></div>
                        <a>
                </div>
            </div>
        </div>
    </div>

</div>
<!-- Super Deals Ends-->


<!-- Super Deals Starts-->
<div class="container dealbx" style="display:none">
    <div class="row">
        <div class="col-md-12 text-center">
            <h3 class="mb-0"><b>Super Deals End In</b></h3>
            <div id="countdown">
                <ul class="d-flex text-center counterin">
                    <li><span id="days"></span>Days</li>
                    <li><span>:</span></li>
                    <li><span id="hours"></span>Hours</li>
                    <li><span>:</span></li>
                    <li><span id="minutes"></span>Minutes</li>
                    <li><span>:</span></li>
                    <li><span id="seconds"></span>Seconds</li>
                </ul>
                <hr />
            </div>
        </div>
    </div>
</div>



@include('home.simpleProducts')

<!-- Super Deals Ends -->



<!-- Category Box Starts-->
<div style="display:none">@include('home.categories')</div>
<!-- Category Box Ends -->
<!-- Recent Products Starts-->


    

@include('home.trendingProducts')
@include('home.recommendedProducts-v2')

<div class="container mb-4" style="margin-top:10px;max-width:1180px!important;">
    <div class="banner">
        <div class="row">
            <a href="<?php echo '/category?name=beauty'; ?>" class="col-lg-6 lg-6-Banner "  style="background-image:url({{ url('assets/images/banner-1-1.jpg') }}); background-size:100% 100%;">
           
        </a>

            <div class="col-lg-6 baner">
                <div class="row">

                    <div class="col-lg-6 lg-2-img-banner">
                        <a href="<?php echo '/category?name=home-kitchen'; ?>">
                            <div class="bdImgc"
                                style="background-image:url({{ url('assets/images/banner-2-1.jpg') }}); width:100%; height:100%;border-radius:36px;background-repeat:no-repeat;background-size:cover;position:relative;background-size:100% 100%;">
                                <div class="imgContent">
                                    Exclusive<br> New Collection
                                </div>
                            </div>
                        </a>
                    </div>
                    <a href="<?php echo '/category?name=womens-fashion'; ?>">
                        <div class="col-lg-6 lg-2-img-banner">
                            <div class="bdImgc"
                                style="background-image:url({{ url('assets/images/wig-1.jpg') }}); width:100%; height:100%;border-radius:36px;background-repeat:no-repeat;background-size:cover;background-position: 100% 13%;position:relative;">
                            </div>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
</div>

<!-- Recent Products Ends -->

<!-- Add Box Starts Here -->
<div class="container" style="display:none">
    <div class="row">
        <div class="col-md-12 adbx"><img src="{{ asset('assets/images/ad.jpg', env('HTTPS_STATUS')) }}" alt="ad"
                title="ad"></div>
    </div>
</div>
<!-- Add Box Ends Here -->

<!-- Best Seller Starts -->
@if (count($bestSellers) > 0)
    @include('home.bestSellers')
@endif

<!-- Best Seller Ends -->

<!-- Products Your Love Starts-->
@php
    // dd(count($productsYouMayLikeElastic['records']));
@endphp
@if (isset($productsYouMayLikeDB) && count($productsYouMayLikeDB) > 0)
    @include('home.productsYouMayLikeDB')
@endif
@if (isset($productsYouMayLikeElastic['records']) && count($productsYouMayLikeElastic['records']) > 0)
    @include('home.productsYouMayLikeElastic')
@endif
@include('home.exploreMore')
<!-- Products Your Love Ends -->

<!-- Brands Starts-->
<!-- @include('home.new-top-brands')    -->
<!-- Brands Ends -->

{{-- @include('home.new-recent-products')
@include('home.new-featureflashsale')
@include('home.new-brandsofweek')   
@include('home.new-featuredproducts')
@include('home.new-topsellingproducts')
@include('home.new-featureProducts') --}}

<!-- Recents Products -->


<section class="mnbnr" style="display: none;">
    <div class="">
        <div class="">
            <div class="">
                <!-- Main Banner Starts -->

                <div class="home-slide">

                    <div class="jumbotron"
                        style="background-image: url({{ asset('assets/images/banner1.jpg', env('HTTPS_STATUS')) }});">
                        <div class="container">
                            <h2 class="display-3">Bring new ideas to life, anywhere.</h2>
                            <h2>Explore creative projects and help bring bold new ideas into the world with the S6 app.
                            </h2>
                            <p><a class="btn btn-primary btn-lg" href="#" role="button">Learn more »</a></p>
                        </div>
                    </div>
                    <!-- Main Banner Ends -->
                    <div class="jumbotron"
                        style="background-image: url({{ asset('assets/images/banner1.jpg', env('HTTPS_STATUS')) }});">
                        <div class="container">
                            <h2 class="display-3">Bring new ideas to life, anywhere.</h2>
                            <h2>Explore creative projects and help bring bold new ideas into the world with the S6 app.
                            </h2>
                            <p><a class="btn btn-primary btn-lg" href="#" role="button">Learn more »</a></p>
                        </div>
                    </div>
                    <!-- Main Banner Ends -->
                    <div class="jumbotron"
                        style="background-image: url({{ asset('assets/images/banner1.jpg', env('HTTPS_STATUS')) }});">
                        <div class="container">
                            <h2 class="display-3">Bring new ideas to life, anywhere.</h2>
                            <h2>Explore creative projects and help bring bold new ideas into the world with the S6 app.
                            </h2>
                            <p><a class="btn btn-primary btn-lg" href="#" role="button">Learn more »</a></p>
                        </div>
                    </div>
                    <!-- Main Banner Ends -->
                    <div class="jumbotron"
                        style="background-image: url({{ asset('assets/images/banner1.jpg', env('HTTPS_STATUS')) }});">
                        <div class="container">
                            <h2 class="display-3">Bring new ideas to life, anywhere.</h2>
                            <h2>Explore creative projects and help bring bold new ideas into the world with the S6 app.
                            </h2>
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
<main>

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
//                            @dd($val);
                            //echo $image_url = getAttachmentUrlById((int)$val->feature_image, 'product');
                            $image_url = getAttachmentUrlById((int) $val->feature_image, 'medium');
                            if (!isImageExists($image_url)) {
                                $image_url = url(asset('assets/images/placeholder.jpg', env('HTTPS_STATUS')));
                            }
                            $reviews = getProductRatingById($val->product_id);
                
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
                        <div class="card">
                            <?php $store_slug = isset($val->store_slug) ? $val->store_slug : ''; ?>
                            <div class="dropdown show animal-dropdown">
                                <a class=" dropdown-toggle" href="{{ URL::to('/brand/' . $store_slug) }}"
                                    role="button" id="dropdownMenuLink" aria-expanded="false" target="_blank"
                                    data-toggle="tooltip" data-placement="bottom" title="<?php echo !empty($zanimalName) ? $zanimalName : ''; ?>">
                                    <span class="anml anmls">
                                        <img class="img-fluid" src="{{ $iconUrl }}"
                                            alt="{{ !empty($storeIconData->name) ? $storeIconData->name : '' }}"
                                            title="{{ !empty($storeIconData->name) ? $storeIconData->name : '' }}">
                                    </span>
                                </a>
                            </div>

                            <a href="/product/<?php echo $val->slug; ?>" class="home-product-img"><img
                                    src="{{ $image_url }}" alt="placeholder icon" title="placeholder icon"
                                    style="width:100%">
                                
                                </a>
                            <div class="card-body">
                                <h4 class="card-title"><a href="/product/<?php echo $val->slug; ?>">
                                        {{ $val->title }}</a></h4>
                                <!--progress bar -->
                                <div class="active-compaign ml-min20 mr-min20 pl-20 pr-20 mb-15">
                                    <div class="active-compaing-persent display-inline-block">
                                        <p>Active Campaign

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

                                <!-- Progress bar ends -->
                                <div class="infbx small  justify-content-between">


                                    <div class="text-right">


                                        <span>

                                            <?php echo getProductReviewAverage($val->product_id); ?>
                                            <b class="spacing-left-15"> <?php echo productTotalRatings($val->product_id); ?></b> Ratings</span>

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
                                        By: <a href="{{ Url::to('brand/' . $val->store_slug) }}"
                                            target="_blank"><span>{{ $val->store_name }}</span> </a>
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
                        <img class="img-fluid" src="https://placeimg.com/250/250/animals" alt="Project"
                            title="Project">
                    </div>
                    <div class="slider-cntnt-body text-center">
                        <p>My all-time favorite online resource when it comes to shocking my kitchen ...this has been
                            the biggest game-changer since we started using it almost a year ago</p>
                        <h3>-Katie</h3>
                    </div>
                </article>
                <article>
                    <div class="col-sm-12 img-box">
                        <img class="img-fluid" src="https://placeimg.com/250/250/tech" alt="Project"
                            title="Project">
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
                        <img class="img-fluid" src="https://placeimg.com/250/250/nature" alt="Project"
                            title="Project">
                    </div>
                    <div class="slider-cntnt-body text-center">
                        <p>My all-time favorite online resource when it comes to shocking my kitchen ...this has been
                            the biggest game-changer since we started using it almost a year ago</p>
                        <h3>-Katie</h3>
                    </div>
                </article>
                <article>
                    <div class="col-sm-12 img-box ">
                        <img class="img-fluid" src="https://placeimg.com/250/250/arch/grayscale" alt="Project"
                            title="Project">
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
                                <img class="img-fluid" src="https://placeimg.com/350/350/tech" alt="Project"
                                    title="Project">
                            </div>
                        </div>

                    </div>
                </div>


                <div class="col-sm-6 ml-7 min-h260">
                    <div class="row bAll-1px ">
                        <div class="col-sm-6 pr-0">
                            <div class="banner-box-sec white-background">
                                <h3 class="mb-15">Go To Leader Boards</h3>
                                <p>Score Special Points(SP) everytime you help someone out. Build a team and challenge
                                    the S6 community</p>
                                <a href="javascript:;" class="btn btn-primary btn-simple mt-15">Learn More</a>
                            </div>
                        </div>
                        <div class="col-sm-6 pl-0 pr-0">
                            <div class="banner-box-img">
                                <img class="img-fluid" src="https://placeimg.com/350/350/tech" alt="Project"
                                    title="Project">
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
                    <div class="col-md-5"><img class="img-fluid"
                            src="{{ asset('assets/images/prdct.jpg', env('HTTPS_STATUS')) }}" alt="project"
                            title="Project"></div>
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
                    <div class="col-md-5"><img class="img-fluid"
                            src="{{ asset('assets/images/prdct.jpg', env('HTTPS_STATUS')) }}" alt="project"
                            title="Project"></div>
                </div>
                <!-- Inner Content Ends -->
                <div class="row mrkcont">
                    <div class="col-md-5"><img class="img-fluid"
                            src="{{ asset('assets/images/prdct.jpg', env('HTTPS_STATUS')) }}" alt="project"
                            title="Project"></div>
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

    <section class="n-market" style="display:none">
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
    <section class="">
        <div class="container" style="max-width:1180px!important;">
            <div class="row">
                <div class="col-md-12 nwsbx">
                    <div class="row">
                        <div class="col-md-6"><img src="{{ URL::asset('assets/images/news-img.png') }}"></div>
                        <div class="col-md-6">
                            <div class="nwsbxin">
                                <h2 style="font-size: 35px;">Never miss a Promo</h2>
                                <h3 style="margin-bottom:20px">Subscribe to our newsletter to receive 10% off your next
                                    purchase!</h3>
                                <form class="form-inline" action="" method="post" id="subscriptionForm">
                                    @csrf
                                    <div class="form-group">
                                        <input type="email" name="s_email" class="form-control" id="s_email"
                                            placeholder="Your Email" required>
                                    </div>
                                    <button type="submit" class="btn btn-primary">Subscribe</button>
                                </form>
                            </div>


                        </div>
                    </div>
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
    <script type="text/javascript" src="{{ URL::asset('assets/js/sweetalert.js') }}"></script>
    <link rel="stylesheet" href="{{ URL::asset('assets/css/sweetalert.css') }}">
    <script>
        // get the success message from the session
        //   var successMsg = "{{ session('success') }}";

        //   // if the success message exists, show the alert and change the opacity after 5 seconds
        //   if (successMsg) {
        //     $('body').css('opacity', '0.5');
        //     swal({
        //       title: "Success!",
        //       text: successMsg,
        //       icon: "success",
        //        timer: 5000 // 5000 milliseconds = 5 seconds
        //     });
        //     setTimeout(function() {
        //         $('body').css('opacity', '1');

        //     }, 5000); // 5000 milliseconds = 5 seconds
        //   }
    </script>
    <style>
        a.carousel-control-next,
        a.carousel-control-prev {
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
            responsive: [{
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
        $(document).ready(function() {
            //    jQuery('.reward-campaigns-container, .container.mt-35').css('opacity', '1');
            $(".award-campaigns div.filtered-boxed").each(function() {
                $(this).hide();
                if ($(this).attr('id') == 'main') {
                    $(this).show();
                }
            });
            $('.award-campaigns .reward-conainer-filter ul li a').on("click", function(e) {
                e.preventDefault();
                var id = $(this).attr('data-related');
                $(".award-campaigns div.filtered-boxed").each(function() {
                    $(this).hide();
                    if ($(this).attr('id') == id) {
                        $(this).show();
                    }
                });
            });
            $('.award-campaigns .reward-conainer-filter ul li a').on('click', function(e) {
                e.preventDefault();
                $('.reward-conainer-filter ul li a.active').removeClass('active');
                $(this).addClass('active');
            });
            $('.award-campaigns .section_project-collection-tabbed-block ul li a').on('click', function(e) {
                e.preventDefault();
                $('.section_project-collection-tabbed-block ul li a.active').removeClass('active');
                $(this).addClass('active');
            });
            $(".award-campaigns div.items-start").each(function() {
                $(this).hide();
                if ($(this).attr('id') == 'most-recents') {
                    $(this).show();
                }
            });
            $('.award-campaigns .section_project-collection-tabbed-block ul li a').on("click", function(e) {
                e.preventDefault();
                var id = $(this).attr('data-related');
                $(".award-campaigns div.items-start").each(function() {
                    $(this).hide();
                    if ($(this).attr('id') == id) {
                        $(this).show();
                    }
                });
            });
            jQuery('.active-reward-compaign .show-more-results').on("click", function(e) {
                jQuery(this).toggleClass("showless");
                jQuery('.active-reward-compaign .hide-list-container').slideToggle('fast');
            });
            jQuery('.top-brands-compaign .show-more-results').on("click", function(e) {
                jQuery(this).toggleClass("showless");
                jQuery('.top-brands-compaign .hide-list-container').slideToggle('fast');
            });
            jQuery('.recent-trades-compaign .show-more-results').on("click", function(e) {
                jQuery(this).toggleClass("showless");
                jQuery('.recent-trades-compaign .hide-list-container').slideToggle('fast');
            });
            jQuery('.recent-products-compaign .show-more-results').on("click", function(e) {
                jQuery(this).toggleClass("showless");
                jQuery('.recent-products-compaign .hide-list-container').slideToggle('fast');
            });
        });
        jQuery(document).ready(function() {
            reload = urlParam('reload');
            if (reload) {
                window.history.pushState({
                    urlPath: '/'
                }, null, '/');
            }
        });
    </script>
    <script>
        (function() {
            const second = 1000,
                minute = second * 60,
                hour = minute * 60,
                day = hour * 24;

            //I'm adding this section so I don't have to keep updating this pen every year :-)
            //remove this if you don't need it
            let today = new Date(),
                dd = String(today.getDate()).padStart(2, "0"),
                mm = String(today.getMonth() + 1).padStart(2, "0"),
                yyyy = today.getFullYear(),
                nextYear = yyyy + 1,
                dayMonth = "09/30/",
                birthday = dayMonth + yyyy;

            today = mm + "/" + dd + "/" + yyyy;
            if (today > birthday) {
                birthday = dayMonth + nextYear;
            }
            //end

            const countDown = new Date(birthday).getTime(),
                x = setInterval(function() {

                    const now = new Date().getTime(),
                        distance = countDown - now;

                    document.getElementById("days").innerText = Math.floor(distance / (day)),
                        document.getElementById("hours").innerText = Math.floor((distance % (day)) / (hour)),
                        document.getElementById("minutes").innerText = Math.floor((distance % (hour)) / (minute)),
                        document.getElementById("seconds").innerText = Math.floor((distance % (minute)) / second);

                    //do something later when date is reached
                    if (distance < 0) {
                        document.getElementById("headline").innerText = " ";
                        document.getElementById("countdown").style.display = "none";
                        document.getElementById("content").style.display = "block";
                        clearInterval(x);
                    }
                    //seconds
                }, 0)
        }());

        $(document).ready(function() {
            $('#subscriptionFormm').submit(function(e) {
                e.preventDefault(); // Prevent the default form submission
                // Get the email input value
                var emailInput = $('#s_email').val();

                // Email validation regular expression
                var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

                // Check if the email is valid
                if (!emailRegex.test(emailInput)) {
                    swal("Error!", "Please enter a valid email address.", "error");
                    return; // Stop further execution
                }
                var formData = $(this).serialize(); // Serialize the form data

                $.ajax({
                    url: '/email-validate',
                    type: 'POST',
                    data: formData,
                    success: function(response) {
                        if (response.status == '1') {
                            swal("Success!",
                                "Subscription successful! Welcome to our newsletter community.",
                                "success");
                            setTimeout(() => {
                                location.reload();
                            }, 2000);
                        } else {
                            swal("Information!",
                                "You are already subscribed! Thank you for being part of our newsletter community",
                                "info");
                            setTimeout(() => {
                                location.reload();
                            }, 2000);
                        }
                    },
                    error: function(xhr, status, error) {
                        toastr.error(error);
                    }
                });
            });
        });
    </script>
</main>
@endsection
