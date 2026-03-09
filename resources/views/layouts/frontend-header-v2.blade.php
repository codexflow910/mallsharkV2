<!doctype html>
<html lang="en">

<head>
    <title>@yield('title')</title>
    @if(env('APP_ENV') === 'development')
        <meta name="robots" content="noindex, nofollow">
    @endif

    <!-- Required meta tags mobile-->
    <meta charset="utf-8">
    <!-- <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no"> -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" type="image/x-icon" href="{{ URL::asset('assets/images/mallsharkicon.png') }}">


    <meta name="title" content="@yield('title')" />
    <meta name="description" content="@yield('description')" />
    <meta name="keyword" content="@yield('keyword')" />
    <link rel='canonical' href='{{ url()->current() }}' />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @yield('meta')
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
    {{-- <link rel="stylesheet" href="{{ asset('assets/css/style.css', env('HTTPS_STATUS')) }}" type="text/css"> --}}
    <link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Roboto:400,700" rel="stylesheet">

    <link href="https://fonts.googleapis.com/css?family=Open+Sans:400,400i,600,700,700i" rel="stylesheet">


    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">


    <link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
    <link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">

    {{-- <link rel="stylesheet" href="{{ URL::asset('assets/css/theme-style.css', env('HTTPS_STATUS')) }}" type="text/css">
    <link rel="stylesheet" href="{{ URL::asset('assets/css/new-theme-style.css', env('HTTPS_STATUS')) }}"
        type="text/css">
    <link rel="stylesheet" href="{{ URL::asset('assets/css/reject.css', env('HTTPS_STATUS')) }}" type="text/css">
    <link rel="stylesheet" href="{{ URL::asset('assets/css/ishi-style.css', env('HTTPS_STATUS')) }}" type="text/css">
    <link rel="stylesheet" href="{{ URL::asset('assets/css/new-pages.css', env('HTTPS_STATUS')) }}" type="text/css"> --}}

    <!--DESKTOP VERSION ONLY -->
    {{-- <link rel="stylesheet" href="{{ URL::asset('assets/css/desktop-style.css', env('HTTPS_STATUS')) }}" type="text/css">
    <link rel="stylesheet" href="{{ URL::asset('assets/css/custom.css', env('HTTPS_STATUS')) }}" type="text/css"> --}}
    <link rel="stylesheet" href="{{ URL::asset('assets/css/frontend-v2.css?ver=1.6.93', env('HTTPS_STATUS')) }}" type="text/css">
    <link rel="stylesheet" href="{{ URL::asset('assets/css/general-v2.css?ver=1.5.63', env('HTTPS_STATUS')) }}" type="text/css">
    <!--HEADER SCRIPTS -->
    <script src="https://code.jquery.com/jquery-1.12.4.js"></script>
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-tagsinput/0.8.0/bootstrap-tagsinput.css" type="text/css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-tagsinput/0.8.0/bootstrap-tagsinput.min.js"></script>
    <script src="//cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <script src="{{ URL::asset('assets/js/autocomplete.min.js', env('HTTPS_STATUS')) }}"></script>
    <script src="{{ URL::asset('assets/js/reject.js', env('HTTPS_STATUS')) }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.8.1/slick.min.js"></script>
    {{ romanzipp\Turnstile\Captcha::getScript() }}
    <!-- {!! Minify::javascript([
        'reject.js',
        'cmGauge.js',
        'product-script.js',
        'fone-format.js',
        'sweetalert.js',
        'offset.js',
        'autocomplete.min.js',
        'jquery.plugin.min.js',
        'jquery.countdown.new.js',
    ]) !!} -->
    <style>
        .custom-toast-success.toast-icon.toast-success {
            opacity: 1 !important;
        }

        .custom-toast-success {
            opacity: 1 !important;
        }
        .headerWrapperNav {
            transition: top 0.3s;
        }

        .header-hidden {
           display: none;
        }

        .header-visible {
           display: block;
        }
    </style>
    <link type="text/css" rel="stylesheet" href="https://cdn.jsdelivr.net/jquery.jssocials/1.4.0/jssocials.css" />
    <link type="text/css" rel="stylesheet"
        href="https://cdn.jsdelivr.net/jquery.jssocials/1.4.0/jssocials-theme-flat.css" />
    <script type="text/javascript" src="{{ URL::asset('assets/js/sweetalert.js') }}"></script>
    <link rel="stylesheet" href="{{ URL::asset('assets/css/sweetalert.css') }}">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.0/jquery.validate.js"></script>


    <script type="application/ld+json">
        {
            "@context": "https://schema.org/",
            "@type": "Organization",
            "name": "Mallshark",
            "image": "http://shopzit.mindblaze.net/assets/images/logo.png",
            "author": "Mike Stone"
        },
        "contactPoint": [{
            "@type": "ContactPoint",
            "telephone": "+1 4446 4460054",
            "email": "bass@gmail.com"
        }],{
        "description":
        "Pay less everyday on food, health and nuitrition products using cryptos (bitcoin, eth). Same day delivery on 100s of items. Ph. Sales +1 4446 4460054."
        }
    </script>


    <!-- <script async src="https://www.googletagmanager.com/gtag/js?id=G-{{ env('GOOGLE_ANALYTICS_ID') }}"></script> -->
   <!-- Google Tag Manager -->
<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
})(window,document,'script','dataLayer',"{{ env('GTM_ID') }}");</script>
<!-- End Google Tag Manager -->

    @if(env('APP_ENV') === 'production')
    <!-- Hotjar Tracking Code for Site 5132842 (name missing) -->
    <!-- <script>
        (function(h, o, t, j, a, r) {
            h.hj = h.hj || function() {
                (h.hj.q = h.hj.q || []).push(arguments)
            };
            h._hjSettings = {
                hjid: 5132842,
                hjsv: 6
            };
            a = o.getElementsByTagName('head')[0];
            r = o.createElement('script');
            r.async = 1;
            r.src = t + h._hjSettings.hjid + j + h._hjSettings.hjsv;
            a.appendChild(r);
        })(window, document, 'https://static.hotjar.com/c/hotjar-', '.js?sv=');
    </script> -->


    <!-- Meta Pixel Code -->
    <!-- <script>
                !function(f,b,e,v,n,t,s)
                {if(f.fbq)return;n=f.fbq=function(){n.callMethod?
                n.callMethod.apply(n,arguments):n.queue.push(arguments)};
                if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
                n.queue=[];t=b.createElement(e);t.async=!0;
                t.src=v;s=b.getElementsByTagName(e)[0];
                s.parentNode.insertBefore(t,s)}(window, document,'script',
                'https://connect.facebook.net/en_US/fbevents.js');
                fbq('init', '536816979224775');
                fbq('track', 'PageView');
            </script>
            <noscript><img height="1" width="1" style="display:none"
                src="https://www.facebook.com/tr?id=536816979224775&ev=PageView&noscript=1"/></noscript> -->
    <!-- End Meta Pixel Code -->
    @endif
</head>
<?php

use Stripe\EphemeralKey;

$bodyClass = '';
if (Session::has('userData')) {
    $bodyClass = 'logged-in';
}
?>

<body class="{{ $bodyClass }}" style="display: none;">
    <!-- Google Tag Manager (noscript) -->
    <noscript><iframe src="https://www.googletagmanager.com/ns.html?id={{ env('GTM_ID') }}"
height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
<!-- End Google Tag Manager (noscript) -->
    <div id="landscapeWarning" class="hide-on-desktop" style="display: none;">
        For ideal viewing, please shift to portrait mode.
    </div>


    <input type="hidden" name="site_url" id="site_url" value="<?php echo URL::to('/'); ?>">
    <!-- Header Starts Here -->


    <?php // if (Session::has('userData')) {
    ?>
    <div id="sidebar-wrapper-nav">
        <div class="hmenu-dark-bkg-color"></div>
        <div id="sidebar-wrapper-left">
            <div class="wrapin">
                <div class="head-section">
                    <p class="d-flex">

                        <?php
                        if (Session::has('userData')) {
                            $userid = isset(Session::get('userData')['user_id']) ? Session::get('userData')['user_id'] : "";
                            $image_url = '';
                            $userData = getUserById($userid);
                            $name = "";
                            $username = isset($userData->name) ? $userData->name : "";
                            if (!empty($username)) {

                                $words = preg_split("/\s+/", $username);
                                foreach ($words as $w) {
                                    $name .= strtoupper($w[0]);
                                }
                            }
                            if (isset($userData->user_avatar) != '') {
                                $image_url = getAttachmentUrlById((int) $userData->user_avatar, 'avatar');
                        ?>
                                <span class="head-upper-mbt">

                                    <img class="img-fluid"
                                        src={{ !empty($image_url) ? $image_url : asset('assets/images/personLogo.png') }}
                                        alt="used icon" title="used icon">
                                </span>
                            <?php  } else { ?>
                                <span class="head-upper-mbt">

                                    <img class="img-fluid" src={{ asset('assets/images/personLogo.png') }} alt="used icon"
                                        title="used icon">
                                    <!-- alt="used icon" title="used icon"> -->
                                </span>


                            <?php   } ?>
                            <span>

                                <span>Hi, </span> <span><?php echo $username; ?>!</span></span>

                        <?php

                        } else {
                        ?>

                            <!-- <img src="http://via.placeholder.com/35x35" alt="alt"> -->

                        <?php } ?>

                    </p>
                    <a href="javascript:;" class="menu-toggle-close">
                        <i class="material-icons">
                            clear
                        </i>
                    </a>
                    <span
                        style="position: absolute;top: 23px;left: 32px;font-size: 16px;font-family: Roboto, sans-serif;">{{ !empty($userid) ? '' : 'Welcome to MallShark' }}</span>
                    <a class="navbar-brand nav-logo" href="{{ URL::to('/') }}">
                        <!-- <img src=" {{ asset('assets/images/logo.png', env('HTTPS_STATUS')) }}" alt="MallShark logo" title="MallShark logo">  -->
                    </a>
                </div>

                <div class="menin" id="menin">
                    <h5>Trending</h5>
                    <ul class="mt-1">
                        <!-- <li><a href="/comingsoon">Father's Day</a></li> -->
                        <!-- <li><a href="/comingsoon">New Releases</a></li> -->
                        <li><a href="/best-sellers">Best Sellers</a></li>
                        <li><a href="{{route('category.landing',['slug' => 'christmas-deal'])}}">Christmas Deals</a></li>
                        <!-- <li><a href="/comingsoon">Deals</a></li> -->
                    </ul>
                    <hr />
                    <?php
                    $allCategoreis = getCategories();
                    $allHeadings = getSideNavCategories();
                    ?>
                    <h5>Categories</h5>
                    <ul class="mt-1">
                        <?php
                        if (count($allHeadings) > 0) {
                            $query_category_lin = 'category.landing';
                            foreach ($allHeadings as $key => $val) {
                                // $parent_Category_slug = isset($val['headings']['slug']) ? $val['parent_category']['slug'] : "";
                        ?>
                                <li>
                                    <a class="parent_category" data-parent_cate_id="<?php echo $val['headings']['counterId']; ?>"
                                        href="javascript:void(0);">
                                        <span><?php echo $val['headings']['heading']; ?></span>
                                        <span><i class="material-icons">chevron_right</i></span>
                                    </a>
                                </li>
                        <?php }
                        } ?>
                    </ul>
                    <hr />

                    <h5>Help & Settings</h5>
                    <ul class="mt-1">
                        <li><a href="{{ URL::to('/user-info') }}">Account</a></li>
                        <li><a href="{{ URL::to('/contact-us') }}">Customer Support</a></li>
                        <!-- <li><a href="{{ URL::to('/logout') }}">Sign Out</a></li> -->
                    </ul>
                </div>


                <div class="menin ovrmn" style="display:none">
                    <a class="back-main-category" href="javascript:void(0);"><span style="position:relative; top:0px"
                            class="inner-span-back-to-main"><i class="material-icons">chevron_left</i></span> MAIN
                        MENU</a>
                    <hr class="after-back-to-main" />

                    <?php
                    if (count($allHeadings) > 0) {
                        foreach ($allHeadings as $key => $heading) {
                            foreach ($heading['categories'] as $key => $val) {
                                # code...

                                $parent_Category_slug = isset($val['slug']) ? $val['slug'] : "";
                    ?>
                                <!-- <h5><?php // echo $val['parent_category']['title'];
                                            ?></h5> -->

                                <ul class="mt-1  child-category child-category-container-<?php echo $val['counterId']; ?>">

                                    <?php

                                    $class_have_child = 'no-childs';
                                    if (isset($val['parentid']) && $val['parentid'] != 0) {
                                        $class_have_child = 'expanded';
                                    }
                                    $query_category_lin = 'category.landing';
                                    ?>
                                    <?php
                                    if (isset($val['parentid']) && $val['parentid'] == 0 && $val['parent_slug'] == '') { ?>
                                        <li><a href="{{ route($query_category_lin, ['slug' => $val['slug']]) }}" target="_blank"><span><?php echo $val['title']; ?></span></a></li>
                                    <?php
                                    } elseif (isset($val['parentid']) && $val['parentid'] != 0 && $val['parent_slug'] != '') { ?>
                                        <li><a href="{{ route($query_category_lin, ['slug' => $val['parent_slug'], 'subSlug' => $val['slug']]) }}" target="_blank"><span><?php echo $val['title']; ?></span></a></li>

                                    <?php
                                    } ?>
                                </ul>

                    <?php }
                        }
                    } ?>
                    <hr />

                    <!-- <h5>Explore More</h5>

                    <ul class="mt-1">
                        <li><a href="/brands">Store Directory</a></li>
                        <li><a href="#">Sign Out</a></li>
                    </ul> -->
                    <hr>
                    <h5>Help & Settings</h5>
                    <ul class="mt-1">
                        <!-- <li><a href="{{ URL::to('/user-info') }}">Account</a></li> -->
                        <li><a href="/contact-us">Customer Support</a></li>
                        <!-- <li><a href="{{ URL::to('/logout') }}">Sign Out</a></li> -->
                    </ul>

                </div>
                <div class="menu-link-nav">
                    <?php
                    if (Session::has('userData')) {
                        $userid = isset(Session::get('userData')['user_id']) ? Session::get('userData')['user_id'] : "";
                        $userData = getUserById($userid);
                        $name = "";
                        $username = isset($userData->name) ?  $userData->name : "";
                        if (!empty($username)) {

                            $words = preg_split("/\s+/", $username);
                            foreach ($words as $w) {
                                $name .= strtoupper($w[0]);
                            }
                        }
                        //     $storeData = getStoreDataByUserId($userid);
                        // if (empty($storeData)) {
                    ?>
                        <!-- <li><a href="{{ URL::to('/sellonmallshark') }}"> <span class="with-icon"><i
                                    class="material-icons">assessment</i></span> Become a Seller </a></li> -->
                    <?php
                    }
                    // } else {
                    ?>
                    <!-- <li><a href="{{ URL::to('/login') }}">Become a Seller <span class="with-icon"><i
                                    class="material-icons">assessment</i></span></a></li> -->
                    <?php // }
                    ?>
                    <?php // if (collect(request()->segments())->last() == 'buyer-favorites') {
                    ?>
                    <!-- <a class="nav-link active" href="{{ URL::to('/buyer-favorites') }}">
                        <span class="with-icon">
                            <i class="material-icons">favorite_border</i>
                        </span>
                        Favorites</a> -->
                    <?php // }
                    // else {
                    ?>
                    <!-- <a class="nav-link" href="{{ URL::to('/buyer-favorites') }}">
                        <span class="with-icon">
                            <i class="material-icons">favorite_border</i>
                        </span> Favorites
                    </a> -->
                    <!-- <?php // }
                            ?> -->

                    <!-- <a class="nav-link awesm-fnt" href="{{ URL::to('/logout') }}">
                        <span class="with-icon">
                            <i class="material-icons">power_settings_new</i>
                        </span>
                        <span class="logout-lnk">Logout</span>
                    </a> -->
                </div>

            </div>
        </div>
    </div>
    <?php // }
    $slug = request()->segment(1);
if($slug && ($slug != 'search' || $slug != 'welcome-deal' || $slug != 'best-sellers' || $slug != 'christmas-deal')){
    $slug = $slug;
}else{
    $slug = request()->segment(1);
    if($slug && $slug != 'search'){
       $slug = $slug;
    }else{
        $slug = isset($_GET['category']) ? $_GET['category'] : '';
    }
}
    ?>


<div class="headerWrapperNav">
    <div class="announcement-bar">
        <div class="row">
            <div class="col col-sm col-12 col-md-4">
                <div style="text-align: justify;max-width: fit-content;width: fit-content;margin: auto;">
                    <div style="display: flex;align-items:center;">
                        <div>
                            <svg style="margin-right: 11px;" xmlns="http://www.w3.org/2000/svg" width="30" height="30" fill="currentColor" class="bi bi-truck" viewBox="0 0 16 16">
                                <path d="M0 3.5A1.5 1.5 0 0 1 1.5 2h9A1.5 1.5 0 0 1 12 3.5V5h1.02a1.5 1.5 0 0 1 1.17.563l1.481 1.85a1.5 1.5 0 0 1 .329.938V10.5a1.5 1.5 0 0 1-1.5 1.5H14a2 2 0 1 1-4 0H5a2 2 0 1 1-3.998-.085A1.5 1.5 0 0 1 0 10.5zm1.294 7.456A2 2 0 0 1 4.732 11h5.536a2 2 0 0 1 .732-.732V3.5a.5.5 0 0 0-.5-.5h-9a.5.5 0 0 0-.5.5v7a.5.5 0 0 0 .294.456M12 10a2 2 0 0 1 1.732 1h.768a.5.5 0 0 0 .5-.5V8.35a.5.5 0 0 0-.11-.312l-1.48-1.85A.5.5 0 0 0 13.02 6H12zm-9 1a1 1 0 1 0 0 2 1 1 0 0 0 0-2m9 0a1 1 0 1 0 0 2 1 1 0 0 0 0-2"/>
                            </svg>
                        </div>
                        <div>
                            <b>Free Shipping on hundreds of products</b>
                            <div style="color: #fff;margin-top:-4px;">Limited-time-offer</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Price Adjustment with Vertical Slider -->
            <div class="col col-sm col-12 col-md-4">
                <div class="price-adjustment-sliders">
                    <div>
                        <div style="text-align: justify;max-width: fit-content;width: fit-content;margin: auto;">
                            <div style="display: flex;align-items:center;">
                                <div>
                                <svg style="margin-right: 11px;" xmlns="http://www.w3.org/2000/svg" width="30" height="30" fill="currentColor" class="bi bi-backpack4" viewBox="0 0 16 16">
                                    <path d="M4 9.5a.5.5 0 0 1 .5-.5h7a.5.5 0 0 1 .5.5v4a.5.5 0 0 1-.5.5h-7a.5.5 0 0 1-.5-.5zm1 .5v3h6v-3h-1v.5a.5.5 0 0 1-1 0V10z"/>
                                    <path d="M8 0a2 2 0 0 0-2 2H3.5a2 2 0 0 0-2 2v1c0 .52.198.993.523 1.349A.5.5 0 0 0 2 6.5V14a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V6.5a.5.5 0 0 0-.023-.151c.325-.356.523-.83.523-1.349V4a2 2 0 0 0-2-2H10a2 2 0 0 0-2-2m0 1a1 1 0 0 0-1 1h2a1 1 0 0 0-1-1M3 14V6.937q.24.062.5.063h4v.5a.5.5 0 0 0 1 0V7h4q.26 0 .5-.063V14a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1m9.5-11a1 1 0 0 1 1 1v1a1 1 0 0 1-1 1h-9a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1z"/>
                                </svg>
                                </div>
                                <div>
                                    <b>Free returns</b>
                                    <div style="color: #fff;margin-top:-4px;">Up to 30 days*</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div>
                        <div style="text-align: justify;max-width: fit-content;width: fit-content;margin: auto;">
                            <div style="display: flex;align-items:center;">
                                <div>
                                    <svg style="margin-right: 11px;" xmlns="http://www.w3.org/2000/svg" width="30" height="30" fill="currentColor" class="bi bi-currency-dollar" viewBox="0 0 16 16">
                                        <path d="M4 10.781c.148 1.667 1.513 2.85 3.591 3.003V15h1.043v-1.216c2.27-.179 3.678-1.438 3.678-3.3 0-1.59-.947-2.51-2.956-3.028l-.722-.187V3.467c1.122.11 1.879.714 2.07 1.616h1.47c-.166-1.6-1.54-2.748-3.54-2.875V1H7.591v1.233c-1.939.23-3.27 1.472-3.27 3.156 0 1.454.966 2.483 2.661 2.917l.61.162v4.031c-1.149-.17-1.94-.8-2.131-1.718zm3.391-3.836c-1.043-.263-1.6-.825-1.6-1.616 0-.944.704-1.641 1.8-1.828v3.495l-.2-.05zm1.591 1.872c1.287.323 1.852.859 1.852 1.769 0 1.097-.826 1.828-2.2 1.939V8.73z"/>
                                    </svg>
                                </div>
                                <div>
                                    <b>Premium discounted products</b>
                                    <div style="color: #fff;margin-top:-4px;">Campaign ending soon</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col col-sm col-12 col-md-4">
                <div style="    
                /* text-align: justify; */
                                width: fit-content;
                                margin: auto;
                                ">
                    <div style="display: flex;align-items:center;">
                        <div>
                            <svg style="margin-right: 11px;margin-top:5px;" xmlns="http://www.w3.org/2000/svg" width="30" height="30" fill="currentColor" class="bi bi-tablet" viewBox="0 0 16 16">
                                <path d="M12 1a1 1 0 0 1 1 1v12a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1zM4 0a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2z"/>
                                <path d="M8 14a1 1 0 1 0 0-2 1 1 0 0 0 0 2"/>
                            </svg>
                        </div>
                        <div>
                            <b>Get the MallShark App</b>
                        </div>
                        <div class="column-downloadApp announcebarDownloadAppImg text-center" >
                            <ul style="display: flex;gap:5px;margin-left:10px;align-items:center;">
                                <li><a target="_blank" href="https://play.google.com/store/apps/details?id=com.mallsharkmobile&hl=en"><img src="{{ URL::asset('assets/images//google-play.svg')}}" alt="Google Play"></a></li>
                                <li><a target="_blank" href="https://apps.apple.com/pk/app/mallshark/id6479016187"><img src="{{ URL::asset('assets/images/app-store.svg')}}" alt="App Store"></a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <header class="bd-highlight  desktop-header full-width-header" id="desktop-header">
        <div class="container">
        <?php
        if (collect(request()->segments())->last() == 'checkout') {
            $class = 'col-md-12 col-12 text-center';
        } else {
            $class = 'col-md-2 col-3 text-left';
        }
        ?>
        <div class="d-flex">
            <div class="clickable-formenu">
                {{-- {{ $class }} --}}
                <!-- Brand Starts Here -->
                <a class="navbar-brand" href="{{ URL::to('/') }}"><img
                        src="{{ asset('assets/images/logo.png', env('HTTPS_STATUS')) }}"
                        alt="Mallshark logo" title="Mallshark logo" style="width: 120px; position:relative;"> </a>
                <!-- Brand Ends Here -->
            </div>
            <?php
                     if (collect(request()->segments())->last() == 'checkout') { 
                        ?>
            <?php  } else{ ?>
            <div class="srchbxin flex-auto" id="search-hdr-bx">
                {{-- col-md-7 col-6  --}}
                <?php
                $categoryPayload = false;
                if(isset($slug) && $slug != ''){
                    $category = getCategoryBySlug($slug);
                    if($category){
                        $categoryPayload = true;

                            $action = route('category.landing',['slug'=>$slug]);
                            $placeHolder = $category->categoryTitle;
                        $placeholder = 'Search in '.$placeHolder.' ';
                    }else{
                        if($slug == 'welcome-deal' || $slug == 'best-sellers' || $slug == 'christmas-deal'){
                            $action = route('category.landing',['slug'=>$slug]);
                            $placeHolder = ucwords(str_replace('-', ' ', $slug));
                        $placeholder = 'Search in '.$placeHolder.' ';
                        }else{
                            if($slug == 'brand'){
                                $slug = request()->segment(2);
                                $placeHolder = ucwords(str_replace('-', ' ', $slug));
                                $placeholder = 'Search in '.$placeHolder.' ';
                            }else{
                                $placeholder = 'Search for any Products, use @ to search Brands';
                            }
                            $action = route('home.search');
                        }
                        
                    }
                  
                    
                }else{
                    $placeholder = 'Search for any Products, use @ to search Brands';
                    $action = route('home.search');
                }
                $brand = false;
                if (strpos(url()->current(), 'brand/') !== false) {
                   
                   $store_id = DB::table('stores')->where('store_slug',$slug)->select('store_id')->first();
                    if(isset($store_id) && !empty($store_id)){
                        $brand = true;
                    }
                   $action = route('storeHome', ['storeslug' => $slug]);
                }
                ?>
                
                <form method="get" action="{{$action}}" class="search-autocomplete-form">
                    <div class="input-group">
                        <input maxlength="50" type="text" name="keyword" class="form-control autocomplete-field brand-id"
                            placeholder="{{$placeholder}}" value="<?php if (isset($_GET['keyword'])) {
                                echo $_GET['keyword'];
                            }
                            ?>" brand-id="{{$brand ? $store_id->store_id : ''}}">

                                    <div class="input-group-append">
                                        <button class="btn btn-secondary" type="submit">
                                            <i class="fa fa-search"></i>
                                        </button>
                                    </div>
                                </div>



                    <?php 
                    if($categoryPayload){
                        if (isset($_GET['category']) && $_GET['category'] != '') {
                            ?>
                                <input type="hidden" name="category" value="<?php echo $_GET['category']; ?>">
                                <?php
                            }
                    }
                   
                ?>
                    <?php if (isset($_GET['category_parent']) && $_GET['category_parent'] != '') {
                ?>
                    <input type="hidden" name="category_parent" value="<?php echo $_GET['category_parent']; ?>">
                    <?php
                }
                ?>
                    <?php if (isset($_GET['cashback']) && $_GET['cashback'] != '') {
                ?>
                    <input type="hidden" name="cashback" value="<?php echo $_GET['cashback']; ?>">
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
                ?>
                    <?php if (isset($_GET['recent']) && $_GET['recent'] != '') {
                ?>
                    <input type="hidden" name="recent" value="<?php echo $_GET['recent']; ?>">
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


                            <!-- Seach Icon Starts -->
                            <!-- <div class="input-group">
                        <input type="text" class="form-control" placeholder="Search">
                        <div class="input-group-append">
                            <button class="btn btn-secondary" type="button">
                                <i class="fa fa-search"></i>
                            </button>
                        </div>
                    </div> -->
                            <!-- Search Icon Ends -->


                        </div>

                        <div class="accountCart">
                            {{-- col-md-3 col-9 --}}
                            <!-- Right Nav Starts -->
                            <nav class="icnbar navrght text-right clickable-formenu frNav ">
                                <ul>
                                    <li style="display:none" class="icn1">
                                        <a href="javascript:;" class="search-icon-header">
                                            <!-- <i class="fa fa-search" aria-hidden="true"> </i> -->
                                            <img src="<?php echo asset('assets/images/search-icon.svg'); ?>" width="31" alt="search"
                                                title="search" />
                                        </a>
                                    </li>
                                    <?php
                                    if (Session::has('userData')) {
                                        $userId = !empty(Session::get('userData')['user_id']) ? Session::get('userData')['user_id'] : '';
                                        $has_notification = !empty(Session::get('userData')['has_notification']) ? Session::get('userData')['has_notification'] : '';
                                        $noti_class = '';
                                        if ($has_notification == 1) {
                                            $noti_class = 'arrow-explore';
                                        } else {
                                            $noti_class = 'new-arrow-explore';
                                        }

                                        $res = getuserNotifications();
                                        // echo 'Data: <pre>' .print_r($res,true). '</pre>'; 
                                        $counter = count($res['notification']);
                                        $class = "";
                                        if (isset($res['unread']) && $res['unread'] <= 0) {
                                            $class = "zero";
                                        }
                                    ?>
                                        <li class="nav-item dropdown noman">
                                            <!-- <a class="nav-link  left-minu110" href="#" id="navbardrop1" data-toggle="dropdown">
                                    <img class="headermobileicon" src="<?php // echo asset('assets/images/bell.svg')
                                                                        ?>"
                                        alt="cart icon" title="cart icon" width="38" height="36" alt="alert" />
                                        <span ><i style="font-size:12px;position:relative; left:-8px" class="fa fa-caret-down" aria-hidden="true"></i></span>
                                    <span class="badge badge-primary" id="cart_counter"><?php //echo $counter;
                                                                                        ?></span>
                                </a> -->
                                            <a href="javascript:void(0)" class="notify-anchor left-minu110" id="user_notification"
                                                data-user_id="{{ $userId }}" data-toggle="dropdown">
                                                <img class="headermobileicon" src="{{ asset('assets/images/bell.svg') }}"
                                                    class="img-fluid cart-icon-nv shake-icon" alt="user icon" title="notification icon"
                                                    width="38" height="36" />
                                                <span><i style="font-size:12px;position:relative; left:-8px" class="fa fa-caret-down"
                                                        aria-hidden="true"></i></span>
                                                <span
                                                    class="badge badge-primary notification-badge  <?php echo $noti_class; ?> <?php echo $class; ?>"
                                                    id="mark-read">
                                                    <?php
                                                    $num = $res['unread'];
                                                    $result = $num > 9 ? '9+' : $num;
                                                    substr($result, 0, 2);
                                                    ?>
                                                    <div class="entypo-bell" href="javascript:;"><?php echo isset($res['unread']) ? $result : 0; ?></div>
                                                </span>
                                            </a>
                                            <div class="dropdown-menu cartmnu" id="navbardrop-dropdown">
                                                <div class="cartmenu-notification">
                                                    <span class="trangl"><img
                                                            src="{{ URL::asset('assets/images/trangl.svg') }}"></span>
                                                    <h5 style="margin-bottom:10px">Updates</h5>
                                                    <ul class="dropdown-menu-list ntfc" id="show-noti"
                                                        onclick="event.stopPropagation();">
                                                        <?php
                                                        if ($counter > 0) {
                                                            foreach ($res['notification'] as $nf) { ?>

                                                                <li class="list-notifications" style="background-color:<?php echo ($nf['status'] == 0) ? '#eaf0ff' : 'white'; ?>;padding:10px;border-radius:12px;"><span class="mark-read" data-id="{{ $nf['notification_id'] }}"> <a class="notification_message" style="color:black;"
                                                                            href="<?php echo $nf['url_link']; ?>"><?php echo $nf['subject']; ?></a></span>
                                                                    <span class="delete-notification" data-id="{{ $nf['notification_id'] }}"><i
                                                                            class="trash-icon fa fa-trash" aria-hidden="true"></i></span>
                                                                    <!-- <span class="notification_year"><?php //  echo time_ago_in_php($nf['created_at']);
                                                                                                            ?></span></li> -->

                                                                <?php }
                                                            if ($counter > 0) {
                                                                ?>
                                                                <li><a  href="<?php echo URL::to('/all-notifications'); ?> ">See All
                                                                        Notifications</a>
                                                                </li>
                                                            <?php
                                                            }
                                                        } else { ?>
                                                            <li>
                                                                <p class="notification_message">0 Unread Notification</p>
                                                            </li>
                                                        <?php }
                                                        ?>

                                                    </ul>
                                                </div>
                                            </div>
                                        </li>
                                    <?php } ?>

                                    <li class="nav-item dropdown icn2" id="login-dropdown-mbt">
                                        <a nav-link dropdown-toggle href="#" id="navbardrop0" data-toggle="dropdown">
                                            <?php
                                            if (!Session::has('userData')) {
                                            ?>
                                                <div class="d-flex align-items-center">
                                                    <div class="icon-user-small">
                                                        {{-- <img class="headermobileicon" src="<?php //echo asset('assets/images/user.svg'); 
                                                                                                ?>" height="34"
                                             width="16" alt="user" title="user" /> --}}
                                                        {{-- <i class="fa fa-user-o" aria-hidden="true" style="color:#fff;"></i>  --}}
                                                        <img class="headermobileicon" src="<?php echo asset('assets/images/user.svg'); ?>" height="34"
                                                            width="28" alt="user" title="user" />
                                                    </div>
                                                </div>


                                            <?php } else { ?>
                                                <span class="user-image">
                                                    <span class="head-upper-mbt">
                                                        <?php

                                                        $image_url = '';
                                                        $userData = getUserById($userid);
                                                        if (isset($userData->user_avatar) != '') {
                                                            $image_url = getAttachmentUrlById((int) $userData->user_avatar, 'avatar');
                                                        ?>
                                                            <img class="img-fluid" src="<?php echo $image_url; ?>" alt="user icon"
                                                                title="user icon">
                                                        <?php
                                                        } else {
                                                        ?>
                                                            <!-- <img src="http://via.placeholder.com/35x35" alt="alt"> -->
                                                            <span class="login-name"><?php echo $name; ?></span>
                                                        <?php } ?>
                                                    </span>
                                                </span>
                                                <span><i style="font-size:12px; margin-left:1px;" class="fa fa-caret-down"
                                                        aria-hidden="true"></i></span>
                                            <?php } ?>
                                        </a>
                                        <div class="dropdown-menu nwmnot" id="navbardrop0">
                                            <span class="trangl"><img src="{{ URL::asset('assets/images/trangl.svg') }}"></span>
                                            <?php
                                            if (Session::has('userData')) {
                                            ?>
                                                <div class="inmnew">
                                                    <ul>
                                                        <li><a href="{{ URL::to('/user-account') }}">View Your Profile</a></li>
                                                        <?php
                                                        $userroles = getUserroles($userid);
                                                        $storeData = getStoreDataByUserId($userid);
                                                        foreach ($userroles as $role) {
                                                            if ($role['role_id'] == 2) { ?>
                                                                <li><a href="{{ URL::to('/store-dashboard') }}">Seller Dashboard <span
                                                                            class="seller-staric">*</span></a></li>
                                                        <?php }
                                                        } ?>
                                                        <li><a href="{{ URL::to('/buyer-orders') }} ">Purchases & Reviews</a></li>
                                                        <!-- <li><a href="#">Messages</a></li> -->
                                                        <li><a href="{{ URL::to('/buyer-favorites') }} ">Favorites </a></li>
                                                        <li><a href="{{ URL::to('/user-info') }}  ">Account Settings</a></li>
                                                        <?php
                                                        $userroles = getUserroles($userid);
                                                        $storeData = getStoreDataByUserId($userid);
                                                        foreach ($userroles as $role) {
                                                            if ($role['role_id'] == 1) {  ?>
                                                                <li><a href="{{ URL::to('/admin/dashboard') }}">Super Admin <span
                                                                            class="superamdin-staric">*</span></a></li>
                                                        <?php }
                                                        } ?>
                                                        <li><a href="{{ URL::to('/logout') }}">Sign Out</a></li>
                                                    </ul>
                                                </div>
                                                <div class="login-menu text-center" style="display:none">
                                                    <div class="name-sec-menu">


                                                        <span class="image-outer-mbt">
                                                            <span class="inner-image-ct">
                                                                <?php
                                                                $userData = getUserById($userid);
                                                                if (isset($userData->user_avatar) != '') {
                                                                    $image_url = getAttachmentUrlById((int) $userData->user_avatar, 'avatar');
                                                                ?>
                                                                    <img class="img-fluid" src="<?php echo $image_url; ?>" alt="user icon"
                                                                        title="user icon">
                                                                <?php
                                                                } else {
                                                                ?>
                                                                    <span> <span
                                                                            class="name-show-header"><?php echo $name; ?></span></span>
                                                                <?php } ?>
                                                            </span>
                                                        </span>
                                                    </div><!-- name-sec-menu -->
                                                    <div class="name-option">
                                                        <span class="user-name-menu">
                                                            <?php echo isset($userData->name) ? $userData->name : ''; ?>
                                                        </span>
                                                    </div>
                                                    <?php
                                                    $userroles = getUserroles($userid);
                                                    $storeData = getStoreDataByUserId($userid);
                                                    foreach ($userroles as $role) {

                                                        if ($role['role_id'] == 4) {
                                                    ?>
                                                            <div class="manage-account">
                                                                <a href="{{ URL::to('/user-dashboard') }}">Dashboard</a>
                                                            </div>
                                                        <?php
                                                        }
                                                        if ($role['role_id'] == 2 && !empty($storeData)) {

                                                            $storeData = getStoreDataByUserId($userid);
                                                        ?>
                                                            <div class="dashboard-menu text-center">
                                                                <span>Switch To</span>
                                                                <?php
                                                                $imgeUrl = "";
                                                                if (!empty($storeData->store_logo)) {
                                                                    $imgeUrl = getAttachmentUrlById((int) $storeData->store_logo, 'small');
                                                                ?>
                                                                    <div class="store-logo">
                                                                        <a href="{{ URL::to('/store-dashboard') }}">
                                                                            <img class="img-fluid image-default-width" src="<?php echo $imgeUrl; ?>"
                                                                                alt="{{ $storeData->store_name }}"
                                                                                title="{{ $storeData->store_name }}">
                                                                        </a>
                                                                    </div>
                                                                <?php } else { ?>
                                                                    <a class="manage-account"
                                                                        href="{{ URL::to('/store-dashboard') }}"><?php echo $storeData->store_name; ?></a>
                                                                <?php } ?>
                                                            </div><!-- dashboard-menu -->
                                                            <!--                                            <div class="manage-account">
                                             </div>-->
                                                        <?php
                                                        }

                                                        if ($role['role_id'] == 1) {
                                                        ?>
                                                            <div class="manage-account">
                                                                <a href="{{ URL::to('/admin/dashboard') }}">Admin Dashboard</a>
                                                            </div>
                                                        <?php
                                                        }
                                                        if ($role['role_id'] == 5) {
                                                        ?>
                                                            <div class="manage-account">
                                                                <a href="{{ URL::to('/support/dashboard') }}">Support Dashboard</a>
                                                            </div>
                                                    <?php
                                                        }
                                                    }
                                                    ?>
                                                </div>
                                                <!-- <a class="dropdown-item" href="{{ URL::to('/logout') }}">Log out</a> -->
                                            <?php } else { ?>
                                                <h5 style="text-align: center">Welcome to MallShark!</h5>
                                                <a class="dropdown-item btn btn-primary" href="{{ URL::to('/login') }}">Sign In</a>
                                                <a class="dropdown-item btn btn-primary" href="{{ URL::to('/signup') }}">Sign Up</a>
                                                <div class="text-center mb-1">&#8213;&#8213;&#8213;&#8213;&#8213; OR
                                                    &#8213;&#8213;&#8213;&#8213;&#8213;</div>

                                                <?php
                                                $userid = isset(Session::get('userData')['user_id']) ? Session::get('userData')['user_id'] : "";
                                                $storeData = getStoreDataByUserId($userid);
                                                if (!empty($storeData)) {
                                                ?>
                                                    <a class="dropdown-item btn btn-primary" href="{{ URL::to('/store-dashboard') }}"
                                                        style="background-color:#ffbc00"><b>BECOME A SELLER</b></a>
                                                <?php
                                                } else { ?>
                                                    <a class="dropdown-item btn btn-primary" href="{{ URL::to('/sellonmallshark') }}"
                                                        style="background-color:#ffbc00"><b>BECOME A SELLER</b></a>
                                            <?php }
                                            } ?>
                                        </div>
                                    </li>

                                    <li class="nav-item dropdown icn3 cart cart-list-nav">
                                        <a class="nav-link  left-minu110" href="#" id="navbardrop1"
                                            data-toggle="dropdown">
                                            <!-- <i class="fa fa-shopping-basket" aria-hidden="true">
                                     </i> -->
                                            {{-- <img class="headermobileicon" src="<?php //echo asset('assets/images/cart.svg'); 
                                                                                    ?>" alt="cart icon"
                                    title="cart icon" width="36" height="34" alt="cart" /> --}}
                                            {{-- <i class="fa fa-cart-plus basketICon" aria-hidden="true" style="color: #fff"></i> --}}
                                            <img class="headermobileicon" src="<?php echo asset('assets/images/cart.svg'); ?>" alt="cart icon"
                                                title="cart icon" width="28" height="28" alt="cart" />
                                            <span><i style="font-size:12px;color: #000;" class="fa fa-caret-down"
                                                    aria-hidden="true"></i></span>

                                            <?php
                                            $count = 0;
                                            $cartdata = cartDetail();
                                            if (!empty($cartdata)) {
                                                $count = !empty($cartdata['total_quantity']) ? $cartdata['total_quantity'] : 0;
                                            }
                                            ?>

                                            <span class="badge badge-primary basket"
                                                id="cart_counter_ajax"><?php echo $count; ?></span>
                                        </a>
                                        <div class="dropdown-menu cartmnu" id="navbardrop-dropdown">
                                            <div class="cartmenu">
                                                <span class="trangl" style="position: absolute; left: 252px;  top: -17px;"><img
                                                        src="{{ URL::asset('assets/images/trangl.svg') }}"></span>
                                                @include('cart.carthtml')
                                            </div>
                                        </div>
                                    </li>

                                    <li class="mobileNavigationMenu">
                                        <div id="" class="menu-toggle">
                                            <img class="iconMenuHOme" src="{{ URL::asset('assets/images/navigation_icon_black.svg') }}" alt="side menu" title="side menu" width="24">
                                        </div>
                                    </li>

                                    <?php

                                    if (Session::has('userData')) {
                                        $userId = isset(Session::get('userData')['user_id']) ? Session::get('userData')['user_id'] : '';
                                        $userrole = getUserroles($userId);
                                        // dd('called');

                                        $res = getuserNotifications();
                                        $counter = count($res['notification']);
                                    ?>


                                    <?php
                                    } ?>

                                </ul>
                            </nav>

                            <!-- Right Nav Ends -->

                        </div>


                    <?php } ?>

                </div>


                <!-- Left Nav Starts -->
                <nav class="navlft text-left align-self-center" style="display:none">
                    <?php
                    if (Session::has('userData')) {
                        $userid = Session::get('userData')['user_id'];
                        $userData = getUserById($userid);
                    ?>
                        <div class="left-menu-icon">

                            <!-- <div id="menu-toggle" class="shopzit-menu">
                    <span></span>
                    <span></span>
                    <span></span>
                    <span></span>
                </div> -->
                        </div>
                        <ul class="lft-ul-lst ">
                            <li>
                                <a href="javascript:;" id="explore-search" style="display: none;"> Shop</a>
                            </li>
                            <?php
                            $re = getStoreDataByUserId($userid);
                            if (empty($re)) {
                            ?>
                                <li><a class="btn" href="<?php echo URL::to('/sellonmallshark'); ?>">Become a Seller</a></li>
                            <?php
                            }
                        } else {
                            ?>
                            <li class="style-inline-element">
                                <a href="javascript:;" id="explore-search" style="display: none;"> Shop</a>
                            </li>
                            <li class="style-inline-element"><a class="btn" href="{{ URL::to('/login') }}">Become a
                                    Seller</a></li>

                        <?php } ?>
                        </ul>
                </nav>
                <!-- Left Nav Ends -->






                <?php rediredctUrls(); ?>
            </div>
        </header>
        @php
        $currentRouteFirst = collect(request()->segments())->first();
        $currentRoute = collect(request()->segments())->last();

        @endphp

        @if (
        $currentRoute !== 'allusers' &&
        $currentRoute !== 'store-products' &&
        $currentRoute !== 'add-product' &&
        $currentRoute !== 'user-dashboard' &&
        $currentRoute !== 'user-account' &&
        $currentRoute !== 'buyer-orders' &&
        $currentRoute !== 'user-info' &&
        $currentRoute !== 'brand-orders' &&
        $currentRoute !== 'payments' &&
        $currentRoute !== 'articles' &&
        $currentRoute !== 'store-dashboard' &&
        $currentRoute !== 'paidby' &&
        $currentRoute !== 'buyer-favorites' &&
        $currentRoute !== 'sale-report' &&
        $currentRoute !== 'categories' &&
        $currentRoute !== 'postcategories' &&
        $currentRoute !== 'slides' &&
        $currentRoute !== 'faqs' &&
        $currentRoute !== 'content-pages' &&
        $currentRoute !== 'orders' &&
        $currentRoute !== 'store-featureproduct' &&
        $currentRoute !== 'analytics2' &&
        $currentRoute !== 'dashboard' &&
        $currentRoute !== 'managedshipping' &&
        $currentRoute !== 'importshopify' &&
        $currentRouteFirst !== 'brand-order-details' &&
        $currentRouteFirst !== 'new-buyer-orders-detail')
        <!-- Sidebar HTML code -->
        <div class="subnav">
            <div class="container">
                <div class="row">

                    <div class="col-md-12 col-12">
                        <ul>
                            <li class="start-menu-all">
                                <div class="left-menu-icon brgmen">
                                    <div id="menu-toggle" class="shopzit-menu">
                                        {{-- <span></span><span></span><span></span> <span></span> --}}
                                        <img class="iconMenuHOme" src="<?php echo asset('/assets/images/navigation_icon_white-v2.svg'); ?>" alt="side menu"
                                            title="side menu" width="24" height="24" alt="cart" />
                                    </div>
                                </div>
                                {{-- <a id="menu-toggle-all" href="javascript:void(0)"> <span>All</span> </a> --}}
                            </li>
                            <?php
                            $llCategoreis = getCategoriesHome();

                            $query_category_lin = 'category.landing';
                            foreach ($llCategoreis as $key => $val) {
                                $parent_Category_title = isset($val['parent_category']['title']) ? $val['parent_category']['title'] : '';

                                $parent_Category_slug = isset($val['parent_category']['slug']) ? $val['parent_category']['slug'] : '';
                                if (isset($val['childs']) && count($val['childs']) > 0) {
                                    $child_counter = 0;
                                    if ($child_counter == 0) { ?>
                                        <li class="menu-list-category"><a href="{{route($query_category_lin,['slug'=>$parent_Category_slug])}}" id="parent_category_home" class="parent_category_home"
                                                data-parent_cate_id="<?php echo $val['parent_category']['id']; ?>" href="javascript:void(0)">
                                                {{ $parent_Category_title }} </a></li>

                                    <?php }
                                    $child_counter++;
                                } else { ?>

                                    <li>
                                        <a class="parent_category" data-parent_cate_id="<?php echo $val['parent_category']['id']; ?>"
                                            href="{{route($query_category_lin,['slug'=>$parent_Category_slug])}}">
                                            <span><?php echo $val['parent_category']['title']; ?></span>

                                        </a>
                                    </li>
                            <?php
                                }
                            } ?>

                        </ul>
                    </div>

                </div>
            </div>
        </div>
        @endif
    </div>
    <?php
                     if (collect(request()->segments())->last() == 'checkout') { 
                        ?>
            <?php  } else{ ?>
    <div class=" search-container-header" id="search-hdr-bx">
        <div class="search-body-mbt" id="search-headerInner">

            <form method="get" action="{{$action}}" class="search-autocomplete-form">
                <input type="text" name="keyword" class="form-control autocomplete-field" placeholder="Search"
                    value="<?php
                            if (isset($_GET['keyword'])) {
                                echo $_GET['keyword'];
                            }
                            ?>">
                <?php if (isset($_GET['category']) && $_GET['category'] != '') {
                ?>
                    <input type="hidden" name="category" value="<?php echo $_GET['category']; ?>">
                <?php
                }
                ?>
                <?php if (isset($_GET['category_parent']) && $_GET['category_parent'] != '') {
                ?>
                    <input type="hidden" name="category_parent" value="<?php echo $_GET['category_parent']; ?>">
                <?php
                }
                ?>
                <?php if (isset($_GET['cashback']) && $_GET['cashback'] != '') {
                ?>
                    <input type="hidden" name="cashback" value="<?php echo $_GET['cashback']; ?>">
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
                ?>
                <?php if (isset($_GET['recent']) && $_GET['recent'] != '') {
                ?>
                    <input type="hidden" name="recent" value="<?php echo $_GET['recent']; ?>">
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
                <a href="javascript:;" class="cross-search-header">
                    <!-- <i class="fa fa-times" aria-hidden="true"></i> -->
                    <i class="material-icons">
                        clear
                    </i>

                </a>
            </form>
        </div>
    </div>
    <?php } ?> 
    <!-- Heder Ends Here -->

    <div class="search-container explore-category-list-page">
        <form method="get" action="{{ route('home.search') }}">
            <div class="search-inner-body">
                <div class="container-fluide">
                    <div class="row">
                        <div class="col-sm-12">
                            <div class="search-header clearfix">
                                <div class="d-flex justify-content-between">
                                    <span>Shop</span>
                                    <a href="javascript:;" id="close-search-panel">
                                        <i class="material-icons">
                                            clear
                                        </i>
                                    </a>
                                </div>
                            </div><!-- search-header -->
                            <!--
                                <div class="search-section-header">
                                    <label>Keywords</label>
                                    <input type="text" name="arch" class="form-control" placeholder="Product Name, Store Name">
                                </div>
                                -->

                            <!-- search-section-header -->
                            <div class="search-body-data">
                                <div class="section-cat search-panel-body">
                                    <span class="compaigns-span">Campaigns</span>
                                    <div
                                        class="values-section-check valicn transition-all transition-delay-400 relativ">

                                        <div class="col-full mb4 pt3">
                                            <ul class="tablist cmapign-lst-nav">
                                                <!-- <li>
                                                    <a href="/search?cashback=75"
                                                        class="inline-block type-34-md">current flash sales</a>
                                                </li>
                                                <li>
                                                    <a href="/search?cashback=20"
                                                        class="inline-block type-34-md">current zit back offers</a>
                                                </li>
                                                <li style="display:none">
                                                    <a href="/search?cashback=75"
                                                        class="inline-block type-34-md">More than halfway</a>
                                                </li> -->
                                                <li>
                                                    <a href="/search?recent=on" class="inline-block type-34-md">recent
                                                        addition</a>
                                                </li>
                                                <!-- <li>
                                                    <a href="/search?stores=1"
                                                        class="inline-block type-34-md">Supported by your herd</a>
                                                </li> -->
                                                <li>
                                                    <a href="/brands" class="inline-block type-34-md">All
                                                        Brands</a>
                                                </li>
                                                <li>
                                                    <a href="/search" class="inline-block type-34-md">all
                                                        MallShark</a>
                                                </li>
                                            </ul>

                                        </div>


                                        <?php
                                        $llCategoreis = getAllCategories();
                                        //print_rr($llCategoreis);
                                        if (count($llCategoreis) > 0) {
                                        ?>
                                            <span>Categories</span>

                                            <div class="body-sec-mbt">

                                                <div class="col-full mb4 pt3">
                                                    <ul class="tablist accordian-nav">
                                                        <li>
                                                            <a href="/search" class="inline-block type-34-md">All
                                                                Categories</a>
                                                        </li>
                                                        <?php
                                                        $query_category_lin = '/search?';
                                                        foreach ($llCategoreis as $key => $val) {
                                                            $parent_Category_title = isset($val['cat_info']['title']) ? $val['cat_info']['title'] : '';
                                                            $parent_Category_slug = isset($val['cat_info']['slug']) ? $val['cat_info']['slug'] : '';
                                                            $parent_Category_id = isset($val['cat_info']['id']) ? $val['cat_info']['id'] : '';
                                                            $parent_totald = isset($val['cat_info']['total']) ? $val['cat_info']['total'] : '0';
                                                        ?>
                                                            <?php
                                                            $class_have_child = 'no-childs';
                                                            if (isset($val['childs']) && count($val['childs']) > 0) {


                                                                $class_have_child = 'expanded';
                                                                //echo count($val['childs']);
                                                            ?>

                                                            <?php } ?>
                                                            <li class="{{ $class_have_child }}">
                                                                <a style="display:inline-block"
                                                                    class="inline-block type-34-md <?php echo $class_have_child; ?>"
                                                                    href="{{ $query_category_lin }}category=<?php echo $parent_Category_slug; ?>">{{ $parent_Category_title }}
                                                                    <span class="pro-cntr">(<?php echo $parent_totald; ?>)</span>
                                                                </a>

                                                                <?php
                                                                if (isset($val['childs']) && count($val['childs']) > 0) {
                                                                ?>

                                                                    <ul class="">
                                                                        <?php
                                                                        $child_counter = 0;
                                                                        foreach ($val['childs'] as $newVal) {
                                                                        ?>
                                                                            <?php if ($child_counter == 0) { ?>
                                                                                <li class="inline-block mr1 mb2">
                                                                                    <a href="{{ $query_category_lin }}category=<?php echo $parent_Category_slug; ?>"
                                                                                        class="px2 py1 transition-all">All
                                                                                        Products</a>
                                                                                </li>
                                                                            <?php }
                                                                            $child_counter++; ?>
                                                                            <li class="inline-block mr1 mb2"><a
                                                                                    class="px2 py1 transition-al"
                                                                                    href="{{ $query_category_lin }}category=<?php echo isset($newVal['slug']) ? $newVal['slug'] : ''; ?>"><?php echo isset($newVal['title']) ? $newVal['title'] : ''; ?></a>
                                                                            </li>
                                                                        <?php } ?>
                                                                    </ul>


                                                                <?php
                                                                }
                                                                ?>

                                                            </li>
                                                        <?php
                                                        }
                                                        ?>





                                                    </ul>
                                                </div>


                                            </div><!-- body-sec-mbt -->
                                        <?php } ?>
                                    </div>
                                    <!-- section-cat search-panel-body -->


                                    <!-- radio-category-list -->




                                </div><!-- search-panel-body -->
                            </div><!-- search-body-content -->


                        </div>
                    </div>
                </div>

        </form>
    </div><!-- search-inner-body -->
    </div><!-- search-container -->
    <script>
    const routes = {
        esearchKeyword: "{{ route('esearch.keyword') }}",
        esearchKeywordStore: "{{ route('esearch-keyword-store') }}",
        // Add other routes here if needed
    };
</script>

    <script type="text/javascript">
        $(document).ready(function() {
            $('body').css('display', 'block');
        });
        $(document).ready(function() {
            var parentId;
            $('.child-category').hide(); // Hide all child category containers initially
            $(".parent_category").on('click', function() {

                var parentId = $(this).data('parent_cate_id');
                // Toggle visibility of parent and child category divs
                $('#menin').hide();
                $('.menin.ovrmn').show();
                $('.child-category-container-' + parentId).show();
                // debugger


                $(".back-main-category").click(function() {
                    $('#menin').show();
                    $('.menin.ovrmn').hide();
                    if (parentId) {
                        $('.child-category').hide();
                        $('.child-category-container-' + parentId).hide();
                    }
                    parentId = null; // Reset the selected parent ID
                });
            });

            var parentId;
            $('.child-category').hide(); // Hide all child category containers initially
            $(".parent_category_home").on('click', function() {
                // let elm = $("#sidebar-wrapper-nav");
                // setTimeout(function() {
                //     elm.addClass("wide");
                // }, 100);

                var parentId = $(this).data('parent_cate_id');
                $('.child-category').hide();

                $('#menin').hide();
                $('.menin.ovrmn').show();
                $('.child-category-container-' + parentId).show();
                // debugger
                $(".back-main-category").click(function() {
                    $('#menin').show();
                    $('.menin.ovrmn').hide();
                    if (parentId) {
                        $('.child-category').hide();
                        $('.child-category-container-' + parentId).hide();
                    }
                    parentId = null; // Reset the selected parent ID
                });

            });



            $('.results-ui-list ul li a.mr-20,.results-ui-list ul li .heading-large .heading-large,.results-ui-list ul li .supported-div p,.results-ui-list ul li  .stars-rating-cnt')
                .show();
            $('.timeline-item').hide();
            $('.background-loading img').css('opacity', '1');
            // $('input[type="radio"]').click(function () {
            //     var inputValue = $(this).attr("value");
            //     var targetBox = $("." + inputValue);
            //     $(".box").not(targetBox).hide();
            //     $(targetBox).show();
            // });

            // $(".search-icon-header").click(function () {
            //     $('.search-container-header').toggleClass('show-div');
            // });

            // Show navigation //

            $(function() {
                $('.search-icon-header').click(function(event) {
                    if ($('.search-container-header').hasClass("show-div-search")) {
                        // alert(1);
                        $('.search-container-header').fadeOut(300, function() {
                            $('.search-container-header').removeClass(
                                'show-div-search');
                        });
                    } else {

                        // alert(2);
                        $('.search-container-header').fadeIn(50, function() {
                            $('.search-container-header').addClass(
                                'show-div-search');
                            jQuery('.ui-autocomplete-input').focus();
                        });
                    }
                });
                $('.cross-search-header').click(function(event) {
                    if ($('.search-container-header').hasClass("show-div-search")) {
                        // alert();
                        $('.search-container-header').fadeOut(300, function() {
                            $('.search-container-header').removeClass(
                                'show-div-search');
                            $('.autocomplete-field').val('');
                        });
                    }
                });
            });
            $(document).click(function(e) {
    var searchSuggestion = $('#ui-id-2');

    if (searchSuggestion.css('display') === 'none') {
        if (e.target.id != 'search-hdr-bx' && !$('#search-headerInner').find(e.target).length) {
            $('.search-container-header').fadeOut(300, function() {
                $('.search-container-header').removeClass('show-div-search');
            });
        }
    }
});

            $('.search-icon-header').click(function(event) {
                event.stopPropagation();
            });
            $(".img-check").click(function() {
                $(this).parent().toggleClass("check-checkbox");
            });
        });
        var decodeHTML = function(html) {
            var txt = document.createElement('textarea');
            txt.innerHTML = html;
            return txt.value;
        };

        var brandTerm;
        jQuery(".autocomplete-field").autocomplete({
    minLength: 2,
    source: function(request, response) {
        
        brandTerm = request.term;
         let urlParams = new URLSearchParams(window.location.search);
         var path = window.location.pathname; // Returns "/shoes"

// Extract the slug (remove leading/trailing slashes and split)
        var category = path.split('/').filter(Boolean).shift();
        let url = routes.esearchKeyword;
         
            // If 'name' doesn't exist, check if 'category' exists
             category = category ? category : urlParams.get('category');
             const predefinedUrls = <?php echo json_encode(['cart','brands','login','signup','contact-us',
            'about-us','campaign','terms','privacy-policy','generalfaq','buyerfaq','sellerfaq','features','product','brand','brand-order-details']); ?>; 
        function checkCategory(param) {
            return predefinedUrls.includes(param) ? null : param;
        }
        var brand_id = $('.brand-id').attr('brand-id');
       
        category = checkCategory(category) || urlParams.get('category');
             if(category == 'search'){
                data = { keyword: request.term };
             }else{
                if(category == 'welcome-deal' || category == 'best-sellers' || category == 'christmas-deal'){
                    data = { keyword: request.term,deal:category };

                }else if(brand_id != ''){
                    data = { keyword: request.term,category,brand_id};
                    url = routes.esearchKeywordStore;
                }else{
                    data = { keyword: request.term,category};
                }
                
             }
        jQuery.ajax({
            url: url,
            method: "POST",
            data: data,
            success: function(data) {  
               
                response(data);
            }
        });
    },
    select: function(event, ui) {
        
        let label = ui.item.label;
        let category = ui.item.category;

        let urlParams = new URLSearchParams(window.location.search);
        var path = window.location.pathname;
        var url = path.split('/').filter(Boolean)[0];
            
        
            // If 'name' doesn't exist, check if 'category' exists
             slug = url ? url : urlParams.get('category');
             console.log(slug)
             let originalCategory = ui.item.originalCategory;
             
        if (category !== "General") {
           
            // console.log([label,category,slug,originalCategory]);
            // debugger
            let categoryLandingRoutes = {
                'welcome-deal': @json(route('category.landing', ['slug' => 'welcome-deal'])),
                'best-sellers': @json(route('category.landing', ['slug' => 'best-sellers'])),
                'christmas-deal': @json(route('category.landing', ['slug' => 'christmas-deal'])),
            };
            let storeHome = function(slug) {
                return @json(route('storeHome', ['storeslug' => ':slug'])).replace(':slug', slug);
            };
            if (slug === 'welcome-deal' || slug === 'best-sellers' || slug === 'christmas-deal') {
                // Get the pre-defined route for that deal
                let categoryLandingRoute = categoryLandingRoutes[slug];
                // Redirect using the correct route and params
                window.location.href = categoryLandingRoute + '?keyword=' + label + '&category=' + originalCategory;
            }else if (slug === 'brand') {
                slug = path.split('/').filter(Boolean).pop();
                let storehome = storeHome(slug);
                window.location.href = storehome + '?keyword=' + label + '&category=' + originalCategory;
            } else {
             
                // Regular category search
                 window.location.href = "/search?keyword=" + label + '&category=' + originalCategory;

                // window.location.href = storehome + '?keyword' + label + '&category=' + originalCategory;
            }
            
            
        } else {
            let originalLabel = ui.item.originalLabel
            window.location.href = "/brands?keyword=" + originalLabel;
        }
        return false;
    }
}).autocomplete("instance")._renderItem = function(ul, item) {
    var $li = $("<li>");
    if (item.category !== "General") {
        $li.append("<div style='font-size:16px;'><span style='font-size:18px;'>&rdca;</span>" + item.label + "</div>");
        // $li.append("<div><b style='font-size:16px;'>" + item.label + "</b><br><span style='font-size:18px;'>&rdca;</span><small style='font-size:14px;'>" + item.category + "</small></div>");
    } else {
        $li.append("<div><b style='font-size:16px;'>" + item.label + "</b></div>");
    }
    return $li.appendTo(ul);
};

        $.ui.autocomplete.prototype._renderMenu = function(ul, items) {
            var self = this;
            var currentCategory = "";
            $.each(items, function(index, item) {

                if (item.category !== currentCategory && item.category != "General") {
                    ul.append("<li class='ui-autocomplete-category'><b style='font-weight:bold; cursor:auto'> " + item.category + "</b></li>");
                    currentCategory = item.category;
                }
                self._renderItemData(ul, item);
            });
        };
        /*.data("ui-autocomplete")._renderItem = function(ul, item) {
                 return $("<li>")
                 //.data("ui-autocomplete-item", item)
                 .append("<a><strong>" + item.label + "</strong> / " + item.actor + "</a>")
                 .appendTo(ul);
                 }*/
        ;
        jQuery(".autocomplete-field2").autocomplete({
            minLength: 2,
            source: function(request, response) {
                jQuery.ajax({
                    url: "/esearch-keyword-ads",
                    method: "POST",
                    data: {
                        keyword: request.term
                    },
                    success: function(data) {
                        response(data);
                    }
                });
            },
            select: function(event, ui) {
                if (ui.item) {
                    $('.autocomplete-field2').val(ui.item.value);
                }
                jQuery(".search-autocomplete-form").submit();
            }
        });
        jQuery(".autocomplete-field2").autocomplete({
            minLength: 2,
            source: function(request, response) {
                jQuery.ajax({
                    url: "/esearch-keyword-ads",
                    method: "POST",
                    data: {
                        keyword: request.term
                    },
                    success: function(data) {
                        response(data);
                    }
                });
            },
            select: function(event, ui) {
                jQuery(".search-autocomplete-form").submit();
            }

        });
        jQuery(".autocomplete-field3").autocomplete({
            minLength: 2,
            source: function(request, response) {
                jQuery.ajax({
                    url: "/esearch-keyword-user",
                    method: "POST",
                    data: {
                        keyword: request.term
                    },
                    success: function(data) {
                        response(data);
                    }
                });
            }

        });
        // $(document).keydown(function(eventValue) {
        //     if (eventValue.keyCode == 27) {
        //         debugger
        //         $('body').removeClass("ovfw-hde");
        //         $(".search-inner-body").delay().fadeOut();
        //         $('.search-container').hide();
        //         $('#sidebar-wrapper-nav').removeClass("wide");
        //         $('#search-hdr-bx').removeClass("show-div-search").hide();
        //     }
        // });
    </script>
    <script>
        function popitup(url) {
            newwindow = window.open(url, 'name', 'height=800,width=800');
            if (window.focus) {
                newwindow.focus()
            }
            return false;
        }
        jQuery(document).ready(function() {
            /*
             $.reject({
             reject: {
             // safari: true, // Apple Safari  
             //chrome:true, // Google Chrome  
             //firefox: true, // Mozilla Firefox  
             msie: 10, // Microsoft Internet Explorer  
             opera: true, // Opera  
             konqueror: true, // Konqueror (Linux)  
             unknown: true // Everything else  
             
             },
             closeCookie: true,
             }); // Customized Browsers  
             
             return false;
             */
        });
        $(document).ready(function() {
            $('.shopzit-menu').click(function() {
                //                $(this).toggleClass('open');
            });
            //            $("#menu-toggle").click(function (e) {
            //                e.preventDefault();
            //                $("body").toggleClass("toggled");
            //            });
            resizeboxes();
            $(window).resize(function() {
                resizeboxes();
            });
        });
        $(function() {
            $("#menu-toggle,.menu-toggle").on("click", function(e) {
                $("#sidebar-wrapper-nav").addClass("wide");
                $("body").addClass("wide-sidebar-nav");
            });
            $(".menu-toggle-close").on("click", function(e) {
                $("#sidebar-wrapper-nav").removeClass("wide");
                $("body").removeClass("wide-sidebar-nav");
            });
            $(document).click(function(e) {
                if (e.target.id != 'sidebar-wrapper-nav' && !$('#sidebar-wrapper-nav').find(e
                        .target)
                    .length) {
                    $("#sidebar-wrapper-nav").removeClass("wide");
                    $("body").removeClass("wide-sidebar-nav");
                }
            });
            $('#menu-toggle,.menu-toggle').click(function(event) {
                event.stopPropagation();
            });
        });

        $(".hmenu-dark-bkg-color").on("click", function(e) {
            $("#sidebar-wrapper-nav").removeClass("wide");
            $("body").removeClass("wide-sidebar-nav");
        });

        $(function() {
            // $("#menu-toggle-all").on("click", function(e) {
            //     $("#sidebar-wrapper-nav").addClass("wide");

            // });
            $(".menu-toggle-close").on("click", function(e) {
                $("#sidebar-wrapper-nav").removeClass("wide");
            });
            $(document).click(function(e) {
                if (e.target.id != 'sidebar-wrapper-nav' && !$('#sidebar-wrapper-nav').find(e
                        .target)
                    .length) {
                    $("#sidebar-wrapper-nav").removeClass("wide");
                }
            });
            $('#menu-toggle-all').click(function(event) {
                event.stopPropagation();
            });
        });
        var resizeboxes = function() {
            if ($(".wallet-icon-header .pwr").width() > 26) {
                $(".wallet-icon-header .pwr").addClass("pos-left-14");
            } else {
                $(".wallet-icon-header .pwr").removeClass('pos-left-14')
            }
        };
        //$(document).ready(function() {
        //    $('ul.accordian-nav ul').hide();
        //    $('ul.accordian-nav li.expanded a').removeAttr("href");
        //    $('ul.accordian-nav li.expanded > a').click(function(event) {
        //        $('ul.accordian-nav ul').hide('');
        //        $(this).parent().find('ul').toggle('');
        //    });
        //});;

        //$(document).ready(function() {
        //    $('ul.accordian-nav li.expanded a').each(function(i){
        //     $(this).removeAttr("href");   
        //    var subUl = $(this).parent().find('ul'); //Get the sub ul.
        //    $(this).bind('click',function(e){
        //            e.preventDefault(); // Prevent the default action of the link
        //            $('.expanded ul').hide(); // hide all the other ULs
        //            subUl.toggle();
        //        }) ;   
        //    });
        //});      


        $('li.expanded >  a').click(function(e) {

            e.preventDefault();
            // var $this = $(this);
            // if ($this.next().hasClass('show')) {
            //     alert('called');
            //     $this.next().removeClass('show');
            //     // $this.next().slideUp(350);
            // } else {
            //     $this.parent().parent().find('.expanded ul').removeClass('show');
            //     $this.parent().parent().find('.expanded ul').slideUp(350);
            //     $this.next().toggleClass('show');
            //     $this.next().slideToggle(350);
            // }
        });
        $('.accordian-nav li a').click(function() {
            $('ul li.current-lnk-bt').removeClass('current-lnk-bt');
            $(this).closest('li').addClass('current-lnk-bt');
            $(".accordian-nav > li").nextAll().addClass("next-all-items");
            $(".accordian-nav > li").prevAll().addClass("pre-all-items");
        });
        $(function() {
            $('body').on('click', function() {
                if ($('.inner-nav-mbt a').hasClass('active')) {
                    var item = $(this).parent();
                    $(item).parent().parent().parent().addClass('prt-actv');
                }
            });
        });

        function makeNumberFormate() {
            jQuery(document).ready(function() {
                jQuery('.zit-price').each(function() {
                    htmll = jQuery(this).html();
                    htmll = htmll.split('.');
                    // alert(htmll.length);
                    if (htmll.length > 1) {
                        // do nothing
                    } else {

                        jQuery(this).html(htmll + '.00');
                    }
                })
            });
        }
        makeNumberFormate();

        function urlParam(name, url = window.location.href) {

            var results = new RegExp('[\?&]' + name + '=([^&#]*)').exec(url);
            if (results == null) {
                return null;
            }
            return decodeURI(results[1]) || 0;
        }


        $(document).ready(function() {
            $("header .navlft,header .clickable-formenu").css("opacity", "1");
        });
        //$(".uploadImages").css('display','none !important')


        $(document).ready(function() {
            if ($(window).width() >= 768) {
                $('#desktop-header').css("opacity", "1");
                $('#desktop-header').wrap('<div class="header-wrapper"></div>');
                $('body').css("padding-top", "100px");
            }
        });
    </script>
    <script>
        // $(".notify-anchor").on('click', function () {
        //         var data = {};
        //         sendreq(data, 'user', 'read', 'callbackreq');
        //     });
        // function callbackreq() {
        //     $(".entypo-bell").html("0");
        //     $("#new-noti").addClass("zero");
        // }
    </script>
    <script>
    document.addEventListener("DOMContentLoaded", function() {
        let lastScrollTop = 0;
        const header = document.querySelector('.headerWrapperNav');
        const searchbar = document.querySelector('#search-headerInner');
        const searchinput = document.querySelector('#search-headerInner > form > input');
        const searchAutocomplete = document.querySelector('#ui-id-2');

        function handleScroll() {
            let currentScroll = window.pageYOffset || document.documentElement.scrollTop;

            if (searchinput.value.trim() !== "" && getComputedStyle(searchAutocomplete).display === "block") {
                return;
            }

            if (currentScroll > lastScrollTop) {
                header.classList.remove("header-visible");
                header.classList.add("header-hidden");
                searchbar.style.display = "none";
                searchinput.value == "";
            } else {
                header.classList.remove("header-hidden");
                header.classList.add("header-visible");
                searchbar.style.display = "block";
            }

            lastScrollTop = currentScroll <= 0 ? 0 : currentScroll;
        }

        function checkMobileView() {
            if (window.innerWidth <= 768) { 
                window.addEventListener("scroll", handleScroll);
            } else {
                window.removeEventListener("scroll", handleScroll);
                header.classList.remove("header-hidden");
            }
        }

        checkMobileView();
        window.addEventListener("resize", checkMobileView);
    });
</script>




    <style>

        .ui-autocomplete.ui-front {

            position: relative;
            z-index: 9999;
            max-width: 100%;

        }

        .ui-autocomplete.ui-front li {
            cursor: pointer;
            padding: 5px 0px 1px 6px;
            font-size: 14px;
            color: #000;
            border: 1px solid #faf9f8;

        }

        .ui-autocomplete .ui-state-active {
            background: transparent;
            color: #34a08d;
            border: 1px solid transparent;
        }

        .ui-state-active,
        .ui-widget-content .ui-state-active,
        .ui-widget-header .ui-state-active,
        a.ui-button:active,
        .ui-button:active,
        .ui-button.ui-state-active:hover {
            background: transparent !important;
            font-weight: normal;
            color: #000 !important;

        }

        .ui-state-active,
        .ui-widget-content .ui-state-active {
            border: 0px !important;
            background: transparent;
            margin: 0px !important;
        }

        body:not(.logged-in) #new-noti {
            display: none !important;
        }

        .superamdin-staric,
        .seller-staric {
            font-size: 20px;
            color: red;
        }

        .notify-anchor .notification-badge {
            left: 30px;
            .ntfc span: last-child
        }

        .delete-notification {
            color: black;
            cursor: pointer;
        }

        .dropdown-menu-list .notification_message {
            font-size: 12px !important;
        }

        .notification_message:hover {
            color: #0510fa !important;
        }

        .list-notifications .trash-icon {
            font-size: 14px;
        }
    </style>

<script>
    $(document).ready(function() {
        $('.price-adjustment-sliders').slick({
            vertical: true,    // Enable vertical scrolling
            slidesToShow: 1,   // How many slides to show at once
            slidesToScroll: 1, // How many slides to scroll per step
            autoplay: true,    // Enable autoplay
            autoplaySpeed: 2000, // Speed of the autoplay
            arrows: false,     // Hide arrows (optional)
            dots: false,        // Enable dots for navigation
        });
    });
</script>
