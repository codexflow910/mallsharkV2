@extends('layouts.mobile-main')
<!-- Main Banner Starts -->
@section('content')
@section('title', 'Mallshark')
@section('description', 'Mallshark')

<style>
    .reward-campaigns-container, .container.mt-35{
        opacity:0;
    }
    .gray-background{ border-top: 0px;}
</style>
<section>
    <div class="full-container gray-background " style="    display: none;">
        <div class="container">
            <div class="row topinf">
                <div class="col">

                    <span class="light-gray-text display-inline-block"> Supporters</span>
                    <p>14,209,145</p>
                </div>
                <!-- <div class="col">
                    <span class="light-gray-text display-inline-block"> Given Back</span>
                    <p>142,091</p>
                </div> -->
                <div class="col">
                    <span class="light-gray-text display-inline-block"> Campaigns</span>
                    <p>166,54</p>
                </div>
                <div class="col">
                    <span class="light-gray-text display-inline-block">Brands</span>
                    <p>364</p>
                </div>

            </div>
        </div>
    </div>
    <!-- full-container -->
    <!-- Active Products -->
    @include('home.mobile.active-products')
    @include('home.mobile.top-brands')
    @include('home.mobile.recent-trades')
    @include('home.mobile.recent-products')
    <!-- Recents Products -->   
</section>
<section class="mnbnr" style="display: none;">
    <div class="">
        <div class="">
            <div class="">
                <!-- Main Banner Starts -->
                <div class="home-slide">
                    <div class="jumbotron" style="background-image: url({{ asset('assets/images/banner1.jpg',env('HTTPS_STATUS')) }});">
                        <div class="container">
                            <h1 class="display-3">Bring new ideas to life, anywhere.</h1>
                            <h2>Explore creative projects and help bring bold new ideas into the world with the S6 app.</h2>
                            <p><a class="btn btn-primary btn-lg" href="#" role="button">Learn more »</a></p>
                        </div>
                    </div>
                    <!-- Main Banner Ends -->
                    <div class="jumbotron" style="background-image: url({{ asset('assets/images/banner1.jpg',env('HTTPS_STATUS')) }});">
                        <div class="container">
                            <h1 class="display-3">Bring new ideas to life, anywhere.</h1>
                            <h2>Explore creative projects and help bring bold new ideas into the world with the S6 app.</h2>
                            <p><a class="btn btn-primary btn-lg" href="#" role="button">Learn more »</a></p>
                        </div>
                    </div>
                    <!-- Main Banner Ends -->
                    <div class="jumbotron" style="background-image: url({{ asset('assets/images/banner1.jpg',env('HTTPS_STATUS')) }});">
                        <div class="container">
                            <h1 class="display-3">Bring new ideas to life, anywhere.</h1>
                            <h2>Explore creative projects and help bring bold new ideas into the world with the S6 app.</h2>
                            <p><a class="btn btn-primary btn-lg" href="#" role="button">Learn more »</a></p>
                        </div>
                    </div>
                    <!-- Main Banner Ends -->
                    <div class="jumbotron" style="background-image: url({{ asset('assets/images/banner1.jpg',env('HTTPS_STATUS')) }});">
                        <div class="container">
                            <h1 class="display-3">Bring new ideas to life, anywhere.</h1>
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
    <div class="container mt-35 ipad-style">
        <div class="row">
            <div class="col-md-12">
                <h3>Top Selling</h3>  
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
                        ?>
                        <div class="col-md-4">
                            <!-- Product Box -->
                            <div class="card"  >
                                <a href="/product/<?php echo $val->slug; ?>" class="home-product-img"><img src="{{ $image_url }}" alt="{{$val->title}}" title="{{$val->title}}" style="width:100%"></a>
                                <div class="card-body">
                                    <h4  class="card-title"><a href="/product/<?php echo $val->slug; ?>"> {{$val->title}}</a></h4>
                                    <!--progress bar -->
                                    <!-- <div class="active-compaign ml-min20 mr-min20 pl-20 pr-20 mb-15">
                                        <div class="active-compaing-persent display-inline-block">
                                            <p>Active Compaign
                                                <span class="text-blue">%</span>
                                            </p>
                                        </div>
                                        <span class="line-seprator-br" style="display:none;"></span>
                                        <div class="days-left-compaign display-inline-block" style="display:none;">
                                            <p><span class="text-blue">
                                                    </span>
                                                Days Left

                                            </p>
                                        </div>

                                    </div> -->
                                    <!-- <div class="progress"><div class="progress-bar" style="width:70%"></div></div> -->
                                    <!-- Progress bar ends -->
                                    <p class="card-text prc">
                                <span class="n-price"><span class="z-symbl">USDC </span> <?php echo $val->price;?></span> 

                                <span  class="n-stars  push-right"><?php echo getProductReviewAverage($val->product_id); ?></span><br><br>
                                <?php
                                            $authorName = '';
                                            $author = getProductAuthorById($val->product_id);
                                            if (!$author) {
                                                // $authorName = $author->name;
                                            } else {
                                                $authorName = $author->name;
                                            }
                                            ?>
                                            <p class="small">By: {{$authorName}}</p> 
                            </p>
                                    <div class="infbx small  justify-content-between">
                                        <div>
                                            <?php
                                            //$productId = getSupporters($val->product_id);
                                            ?>
                                            <!-- <span>Supporters: <b></b></span><br>
                                            <span>Given Back: <b>$78896</b></span><br>
                                            <span>Pledges Needed: <b></b></span>  -->
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
    <!-- Most Popular Ends -->

    <section class="bb-1px mb-25 pb-70 bt-1px mt-35 zomeed-slider-home " style="opacity:0;">
        <div class="container">
            <h2 class="text-center mt-40 mb-40 ">Making A Difference</h2>
            <div class="slider common-arrow-slide">

                <article>
                    <div class="col-sm-12 img-box">
                        <img class="img-fluid" src="https://placeimg.com/250/250/grayscale" alt="Project" title="Project"> 
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
                        <!-- <p>
                            Lorem ipsum dolor sit amet, consectetur adipisicing elit. Corrupti quos iusto praesentium, vitae officia similique, quo sapiente obcaecati modi quia laborum facere.
                        </p> -->
                        <h3>-Warn</h3>
                    </div>
                </article>
                <article>
                    <div class="col-sm-12 img-box">
                        <img class="img-fluid" src="https://placeimg.com/250/250/people" alt="Project" title="Project"> 
                    </div>
                    <div class="slider-cntnt-body text-center">
                        <p>My all-time favorite online resource when it comes to shocking my kitchen ...this has been the biggest game-changer since we started using it almost a year ago</p>
                        <h3>-Katie</h3>
                    </div>
                </article>
                <article>
                    <div class="col-sm-12 img-box ">
                        <img class="img-fluid" src="https://placeimg.com/250/250/nature" alt="Project" title="Project"> 
                    </div>
                    <div class="slider-cntnt-body text-center">
                        <!-- <p>top
                            Lorem ipsum dolor sit amet, consectetur adipisicing elit. Corrupti quos iusto praesentium, vitae officia similique, quo sapiente obcaecati modi quia laborum facere.
                        </p> -->
                        <h3>-Katie</h3>
                    </div>
                </article>
            </div>

        </div>

    </section>

    <div class="home-banner-box mt-30 pt-30 mb-40 pb-30">
        <div class="container">
            <div class="row">
                <div class="col-md-6 col-sm-12 mb-20">
                    <div class="row bAll-1px">
                        <div class="col-sm-6 pr-0 blue-background">
                            <div class="banner-box-sec ">
                                <h3>How Do ABC<br> Tokens Work <br>And How Do You <br>Get Them?</h3>
                                <a href="javascript:;" class="btn btn-primary btn-simple mt-35">Learn More</a>
                            </div>
                        </div>
                        <div class="col-sm-6 pl-0 pr-0">
                            <div class="banner-box-img">
                                <img class="img-fluid" src="https://placeimg.com/350/310/tech" alt="Project" title="Project">
                            </div>
                        </div>

                    </div>
                </div>


                {{-- <div class="col-md-6 col-sm-12 mb-20">
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
                                <img class="img-fluid" src="https://placeimg.com/350/310/animal" alt="Project" title="Project">
                            </div>
                        </div>

                    </div>
                </div> --}}


            </div>
        </div>
    </div>



    <!-- How it works Content -->
    <div class="container markt" style="display: none;">
        <div class="row"> 
            <div class="col-md-12">
                <h1 class="text-center">How Businesses Works With S6?</h1>
                <!-- Inner Content Box -->
                <div class="row mrkcont">
                    <div class="col-md-5"><img class="img-fluid" src="{{ asset('assets/images/prdct.jpg',env('HTTPS_STATUS')) }}" alt="Project" title="Project"></div>
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
                    <div class="col-md-5"><img class="img-fluid" src="{{ asset('assets/images/prdct.jpg',env('HTTPS_STATUS')) }}" alt="Project" title="Project"></div>
                </div> 
                <!-- Inner Content Ends -->
                <div class="row mrkcont">
                    <div class="col-md-5"><img class="img-fluid" src="{{ asset('assets/images/prdct.jpg',env('HTTPS_STATUS')) }}" alt="Project" title="Project"></div>
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
    //     //echo '$userroles: <pre>' .print_r($userroles,true). '</pre>';
    //         if (!empty($userroles) && $userroles['role_id'] != 2) {
            
    //         $show = true;
    //         $link = "store/create";
    //     }
    // } else
     if (!Session::has('userData')) {
        $show = true;
        $link = "signup";
    }

    if ($show == true) {
        ?>

        <div class="btmsinup">
            <h5>Start Selling Today</h5>
            <p><a href="<?php echo URL::to($link); ?>" class="btn btn-primary">Sign Up Now</a></p>  

        </div>
    <?php } ?>
    <!-- Bottom Sign Up Section Ends --> 
    <!-- Quick Contact Section Starts -->
    <div class="qckcnt text-center">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <h1>Got A Question?</h1>
                    <h2>Get your answers by visiting our FAQ or contact us via feedback and support requests</h2>
                    <p><a href="<?php echo URL::to('/generalfaq'); ?>"><button type="button" class="btn btn-primary btn-simple">FAQs</button></a>
                    <a href="<?php echo URL::to('contact-us'); ?>"><button type="button" class="btn btn-primary btn-simple">Contact Us</button></p></a>     
                </div>
            </div>    
        </div>
    </div>  

    <!-- Quick Contact Section Ends -->

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
            breakpoint: 767,
            settings: {
                slidesToShow: 1,
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
    jQuery('.reward-campaigns-container, .container.mt-35').css('opacity', '1');
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
//        jQuery('.recent-products-compaign .hide-list-container').slideToggle('fast');

    });














});

    </script>


</main>
@endsection
<!-- Content Ends-->




