<!doctype html>
<html lang="en">

<head>
    <title>@yield('title')</title>
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
    <link rel="stylesheet" href="{{ asset('assets/css/style.css', env('HTTPS_STATUS')) }}" type="text/css">
    <link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Roboto:400,700" rel="stylesheet">

    <link href="https://fonts.googleapis.com/css?family=Open+Sans:400,400i,600,700,700i" rel="stylesheet">

    <link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
    <link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">

    <link rel="stylesheet" href="{{ URL::asset('assets/css/theme-style.css', env('HTTPS_STATUS')) }}" type="text/css">
    <link rel="stylesheet" href="{{ URL::asset('assets/css/new-theme-style.css', env('HTTPS_STATUS')) }}"
        type="text/css">
    <link rel="stylesheet" href="{{ URL::asset('assets/css/reject.css', env('HTTPS_STATUS')) }}" type="text/css">
    <link rel="stylesheet" href="{{ URL::asset('assets/css/ishi-style.css', env('HTTPS_STATUS')) }}" type="text/css">
    <link rel="stylesheet" href="{{ URL::asset('assets/css/new-pages.css', env('HTTPS_STATUS')) }}" type="text/css">

    <!--DESKTOP VERSION ONLY -->
    <link rel="stylesheet" href="{{ URL::asset('assets/css/desktop-style.css', env('HTTPS_STATUS')) }}" type="text/css">
    <link rel="stylesheet" href="{{ URL::asset('assets/css/custom.css', env('HTTPS_STATUS')) }}" type="text/css">
 

    
    <!--HEADER SCRIPTS -->
    <script src="https://code.jquery.com/jquery-1.12.4.js"></script>
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-tagsinput/0.8.0/bootstrap-tagsinput.css" type="text/css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-tagsinput/0.8.0/bootstrap-tagsinput.min.js"></script>
    <script src="//cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <script src="{{ URL::asset('assets/js/autocomplete.min.js', env('HTTPS_STATUS')) }}"></script>
    <script src="{{ URL::asset('assets/js/reject.js', env('HTTPS_STATUS')) }}"></script>
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
            opacity: 1!important;
        }
        .custom-toast-success {
            opacity: 1!important;
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
    }],
    "description":
    "Pay less everyday on food, health and nuitrition products using cryptos (bitcoin, eth). Same day delivery on 100s of items. Ph. Sales +1 4446 4460054."
    }
    </script>
   
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-9SDB3QG8XW"></script>
<script>
 
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());

  gtag('config', 'G-9SDB3QG8XW');
</script>
</head>
<?php

use Stripe\EphemeralKey;

$bodyClass = '';
if (Session::has('userData')) {
    $bodyClass = 'logged-in';
}
?>

<body class="{{ $bodyClass }}">
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
                        $username = isset($userData->name) ? $userData->name: "";
                        if(!empty($username)){

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
                        <?php  }else { ?>
                        <span class="head-upper-mbt">

                            <img class="img-fluid" src={{ asset('assets/images/personLogo.png') }} alt="used icon"
                                title="used icon">
                            alt="used icon" title="used icon">
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
                        <li><a href="/comingsoon">Father's Day</a></li>
                        <li><a href="/comingsoon">Deals</a></li>
                    </ul>
                    <hr />
                    <?php
                    $allCategoreis = getCategories();
                   
                    ?>
                    <h5>Categories</h5>
                    <ul class="mt-1">
                        <?php
                        if (count($allCategoreis) > 0) {

                            $query_category_lin = '/search?';
                            foreach ($allCategoreis as $key => $val) { 
                                $parent_Category_slug = isset($val['parent_category']['slug']) ? $val['parent_category']['slug'] : "";
                                if (isset($val['childs']) && count($val['childs']) > 0) {
                                    $child_counter = 0; 
                                    if ($child_counter == 0) { ?>
                        <li>
                            <a class="parent_category" data-parent_cate_id="<?php echo $val['parent_category']['id']; ?>"
                                href="javascript:void(0);">
                                <span><?php echo $val['parent_category']['title']; ?></span>
                                <span><i class="material-icons">chevron_right</i></span>
                            </a>
                        </li>

                        <?php }
                                    $child_counter++;
                                }else{ 

                                
                                ?>
                        <li><a href="<?php echo $query_category_lin . 'category=' . $parent_Category_slug; ?>"><span><?php echo $val['parent_category']['title']; ?></span></a>
                        </li>
                        <?php } }
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
                    if (count($allCategoreis) > 0) {                       
                        foreach ($allCategoreis as $key => $val) {
                            $parent_Category_slug = isset($val['parent_category']['slug']) ? $val['parent_category']['slug'] : "";
                    ?>
                    <!-- <h5><?php // echo $val['parent_category']['title'];
                    ?></h5> -->

                    <ul class="mt-1  child-category child-category-container-<?php echo $val['parent_category']['id']; ?>">

                        <?php
                                $class_have_child = 'no-childs';
                                if (isset($val['childs']) && count($val['childs']) > 0) {
                                    $class_have_child = 'expanded';
                                }
                                if (isset($val['childs']) && count($val['childs']) > 0) {
                                    
                                    $query_category_lin = '/search?';
                                    foreach ($val['childs'] as $newVal) { ?>
                        <li><a href="<?php echo $query_category_lin . 'category=' . $newVal['slug']; ?>"><span><?php echo $newVal['title']; ?></span></a>
                        </li>
                        <?php }
                                } ?>
                    </ul>

                    <?php }
                    } ?>
                    <hr />

                    <h5>Explore More</h5>

                    <ul class="mt-1">
                        <li><a href="/brands">Store Directory</a></li>
                        <!-- <li><a href="#">Sign Out</a></li> -->
                    </ul>
                    <hr>
                    <h5>Help & Settings</h5>
                    <ul class="mt-1">
                        <!-- <li><a href="{{ URL::to('/user-info') }}">Account</a></li> -->
                        <li><a href="/contact-us">Customer Support</a></li>
                        <!-- <li><a href="{{ URL::to('/logout') }}">Sign Out</a></li> -->
                    </ul>

                </div>



                <!-- <div id="accordion-nav"> -->
                <!-- <div class="card" style="display:none;">
                        <div class="card-header" >
                            <h5 class="mb-0">
                                <a role="button" data-toggle="collapse" href="#collapse-01" aria-expanded="false" aria-controls="collapse-1">
                                    <span class="with-icon">
                                        <i class="material-icons">
                                            notification_important
                                        </i>
                                    </span>
                                    <span>Notification</span>
                                    <span class="notifier-message">2</span>
                                </a>
                            </h5>
                        </div>

                        <div id="collapse-01" class="collapse notification-link" data-parent="#accordion-nav" aria-labelledby="">
                            <div class="card-body">
                                <div class="message-body-nav" onclick=" event.stopPropagation();" >
                                    <ul>
                                        <li>
                                            <p>I hope that you will be there in time. See you then</p>
                                        </li>
                                        <li>
                                            <p>Hello this is an example message. Just want to show how it look</p>
                                        </li>
                                        <li>
                                            <p>I hope that you will be there in time</p>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>                                 -->


                <?php
                // $userid = Session::get('userData')['user_id'];
                // $userroles = getUserroles($userid);
                // $userroutes = array('user-dashboard', 'user-info', 'user-profile', 'buyer-orders');
                // $stor = array('store-dashboard', 'edit', 'add-product', 'store-products', 'reports', 'payments', 'brand-orders', 'templates');
                // $show = '';
                // $arialExpanded = 'false';
                // $showstore = '';
                // foreach ($userroles as $role) {
                ?>
                <?php // if ($role['role_id'] == 4) {
                ?>
                <!-- <div class=" user-disable">
                            <div class="card-header" id="heading-1"> -->
                <!-- <span class="mb-0"> -->
                <?php
                // $lasturlIndex = collect(request()->segments())->last();
                // if (in_array($lasturlIndex, $userroutes)) {
                //     $show = 'show';
                //     $arialExpanded = 'true';
                // }
                ?>
                <!-- <a role="button" data-toggle="collapse" href="#collapse-1"
                                    aria-expanded="<?php // echo $arialExpanded;
                                    ?>" aria-controls="collapse-1">
                                    <span class="with-icon"><i class="material-icons">person</i></span>
                                    Profile
                                </a>
                            </span>
                        </div> -->
                <?php
                // $lasturlIndex = collect(request()->segments())->last();
                // if (in_array($lasturlIndex, $userroutes)) {
                //     $show = 'show';
                // }
                ?>
                <!-- <div id="collapse-1" class="collapse  <?php // echo $show;
                ?>" data-parent="#accordion-nav"
                            aria-labelledby="heading-1">
                            <div class="card-body"> -->
                <?php
                // if (collect(request()->segments())->last() == 'user-dashboard') {
                ?>
                <!-- <a class="nav-link active" href="{{ URL::to('/user-dashboard') }}">Dashboard</a> -->
                <?php // } else {
                ?>
                <!-- <a class="nav-link " href="{{ URL::to('/user-dashboard') }}">Dashboard</a> -->
                <?php
                // }
                ?>




                <!-- <//?php
                                        if //(collect(request()->segments())->last() == 'user-profile') {
                                            ?>
                                            <a class="nav-link active" href="{{ URL::to('/user-profile') }}">Kingdom</a>
                                        <//?php } else { ?>
                                            <a class="nav-link" href="{{ URL::to('/user-profile') }}">Kingdom</a>
                                        <//?php } ?>

                                        <a href="javascirpt:;" style="display:none;" >

                                            <span>Reviews</span>
                                        </a> -->


                <?php // if (collect(request()->segments())->last() == 'buyer-orders') {
                ?>
                <!-- <a class="nav-link active" href="{{ URL::to('/buyer-orders') }}">Orders</a> -->
                <?php // } else {
                ?>
                <!-- <a class="nav-link" href="{{ URL::to('/buyer-orders') }}">Orders</a> -->
                <!-- <?php //  }
                ?> -->



                <!-- </div>
                        </div>
                    </div> -->
                <?php
                // }
                // if ($role['role_id'] == 2) {
                ?>
                <!-- <div class="">
                        <div class="card-header" id="heading-2">
                            <span class="mb-0"> -->
                <?php
                // $lasturlIndex = collect(request()->segments())->last();
                // if (in_array($lasturlIndex, $stor)) {
                //     $showstore = 'show';
                //     $arialExpanded = 'true';
                // }
                ?>
                <!-- <a class="collapsed" role="button" data-toggle="collapse" href="#collapse-2"
                                    aria-expanded="<?php // echo $arialExpanded;
                                    ?>" aria-controls="collapse-2">
                                    <span class="with-icon"><i class="material-icons">assessment</i></span>
                                    Store
                                </a>
                            </span>
                        </div> -->
                <?php
                // $lasturlIndex = collect(request()->segments())->last();
                // if (in_array($lasturlIndex, $stor)) {
                //     $showstore = 'show';
                // }
                ?>
                <!-- <div id="collapse-2" class="collapse <?php //  echo $showstore;
                ?>" data-parent="#accordion-nav"
                            aria-labelledby="heading-2">
                            <div class="card-body"> -->

                <?php
                // if (collect(request()->segments())->last() == 'store-dashboard') {
                ?>
                <!-- <a class="nav-link active" href="{{ URL::to('/store-dashboard') }}">Dashboard</a> -->
                <?php
                // } else {
                ?>
                <!-- <a class="nav-link" href="{{ URL::to('/store-dashboard') }}">Dashboard</a> -->
                <?php
                // }
                ?>


                <?php
                // if (collect(request()->segments())->last() == 'add-product') {
                ?>
                <!-- <a class="nav-link active" href="{{ URL::to('/add-product') }}">Add Product</a> -->
                <?php
                // } else {
                ?>
                <!-- <a class="nav-link" href="{{ URL::to('/add-product') }}">Add Product</a> -->
                <?php
                // }
                ?>



                <?php
                // if (collect(request()->segments())->last() == 'store-products') {
                ?>
                <!-- <a class="nav-link active" href="{{ URL::to('/store-products') }}">View All Products</a> -->
                <?php
                // } else {
                ?>
                <!-- <a class="nav-link" href="{{ URL::to('/store-products') }}">View All Products</a> -->
                <?php
                // }
                ?>
                <?php
                // if (collect(request()->segments())->last() == 'campaign') {
                ?>
                <!-- <a class="nav-link active" href="{{ URL::to('/store/campaign') }}">Campaigns</a> -->
                <?php
                // } else {
                ?>
                <!-- <a class="nav-link" href="{{ URL::to('store/campaign') }}">Campaigns</a> -->
                <?php
                // }
                ?>
                <?php
                // if (collect(request()->segments())->last() == 'reports') {
                ?>
                <!-- <a class="nav-link active" href="{{ URL::to('/store/reports') }}">Reports</a> -->
                <?php
                // } else {
                ?>

                <!-- <a class="nav-link" href="{{ URL::to('/store/reports') }}">Reports</a> -->


                <?php
                // }
                ?>

                <?php
                // if (collect(request()->segments())->last() == 'brand-orders') {
                ?>
                <!-- <a class="nav-link active" href="{{ URL::to('/brand-orders') }}">Orders</a> -->
                <?php
                // } else {
                ?>

                <!-- <a class="nav-link" href="{{ URL::to('/brand-orders') }}">Orders</a> -->


                <?php
                // }
                ?>
                <?php
                // if (collect(request()->segments())->last() == 'store/templates') {
                ?>
                <!-- <a class="nav-link active" href="{{ URL::to('/store/templates') }}">Store Banner</a> -->
                <?php
                // } else {
                ?>

                <!-- <a class="nav-link" href="{{ URL::to('/store/templates') }}">Store Banner</a> -->


                <?php
                // }
                ?>
                <!-- @if (collect(request()->segments())->last() == 'importshopify')
<a class="nav-link active" href="{{ URL::to('/importshopify') }}">Import Products</a>
@else
<a class="nav-link" href="{{ URL::to('/importshopify') }}">Import Products</a>
@endif -->




                <!-- </div>
                        </div>
                    </div> -->
                <?php // }
                ?>
                <?php // if ($role['role_id'] == 1) {
                ?>
                <!-- <div class="menu-link-nav hidden-mobile">

                        <a href="<?php // echo URL::to('admin/dashboard');
                        ?>">
                            <span class="with-icon">
                                <i class="material-icons">dashboard</i>
                            </span>

                            Admin Dashboard</a>
                    </div> -->
                <?php
                //     }
                // }
                ?>
                <!-- </div> -->

                <div class="menu-link-nav">
                    <?php
                    if (Session::has('userData')) {
                    $userid = isset(Session::get('userData')['user_id']) ? Session::get('userData')['user_id'] : "";
                    $userData = getUserById($userid);
                    $name = "";
                    $username = isset($userData->name) ?  $userData->name: "";
                    if(!empty($username)){

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
    ?>



    <header class="  bd-highlight container desktop-header" id="desktop-header">

        <?php
        if (collect(request()->segments())->last() == 'checkout') {
            $class = 'col-md-12 col-12 text-center';
        } else {
            $class = 'col-md-2 col-3 text-left';
        }
        ?>
        <div class="row">
            <div class="{{ $class }} clickable-formenu">
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
            <div class="col-md-7 col-6 srchbxin" id="search-hdr-bx">
                <form method="get" action="{{ route('home.search') }}" class="search-autocomplete-form">
                    <div class="input-group">
                        <input type="text" name="keyword" class="form-control autocomplete-field"
                            placeholder="Search" value="<?php if (isset($_GET['keyword'])) {
                                echo $_GET['keyword'];
                            }
                            ?>">

                        <div class="input-group-append">
                            <button class="btn btn-secondary" type="button">
                                <div class="input-group-append">
                                    <a class=" " type="button" href="{{ URL::to('/search?keyword=') }}">
                                        <i class="fa fa-search"></i> </a>
                            </button>
                        </div>
                    </div>



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

            <div class="col-md-3 col-9">

                <!-- Right Nav Starts -->
                <nav class="icnbar navrght text-right clickable-formenu dshbrd-in ">
                    <ul>
                        <li style="display:none" class="icn1">
                            <a href="javascript:;" class="search-icon-header">
                                <!-- <i class="fa fa-search" aria-hidden="true"> </i> -->
                                <img src="<?php echo asset('assets/images/search.svg'); ?>" height="34" width="26" alt="search"
                                    title="search" />
                            </a>
                        </li>
                        <?php
                            if (Session::has('userData')) { 
                                $userId = !empty(Session::get('userData')['user_id']) ? Session::get('userData')['user_id']:'';
                                $has_notification = !empty(Session::get('userData')['has_notification']) ? Session::get('userData')['has_notification']:'';
                                $noti_class = '';
                                if($has_notification == 1){
                                    $noti_class = 'arrow-explore';
                                }else{
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
                                    class="img-fluid cart-icon-nv shake-icon" alt="user icon" title="cart icon"
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
                                    <h5 style="margin-bottom:3px">Updates</h5>
                                    <ul class="dropdown-menu-list ntfc" id="show-noti"
                                        onclick="event.stopPropagation();">
                                        <?php
                                        if ($counter > 0) {
                                            foreach ($res['notification'] as $nf) { ?>

                                        <li class="list-notifications" style="background-color:<?php echo ($nf['status'] == 0) ? '#e6e6e6' : 'white'; ?>;"><span class="mark-read" data-id="{{ $nf['notification_id'] }}"> <a class="notification_message"
                                                    href="<?php echo $nf['url_link']; ?>"><?php echo $nf['subject']; ?></a></span>
                                            <span class="delete-notification" data-id="{{ $nf['notification_id'] }}"><i
                                                    class="trash-icon fa fa-trash" aria-hidden="true"></i></span>
                                            <!-- <span class="notification_year"><?php //  echo time_ago_in_php($nf['created_at']);
                                            ?></span></li> -->

                                            <?php }
                                         if ($counter > 0 ) {
                                            ?>
                                        <li><a class=" " href="<?php echo URL::to('/all-notifications'); ?>">See all
                                                notifications</a>
                                        </li>
                                        <?php
                                        }
                                        }else{ ?>
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
                                <!-- <i class="fa fa-user-o" aria-hidden="true"></i> -->
                                <img class="headermobileicon" src="<?php echo asset('assets/images/user.svg'); ?>" height="34"
                                    width="36" alt="user" title="user" />

                                <?php } else { ?>
                                <span class="user-image">
                                    <span class="head-upper-mbt">
                                        <?php

                                                $image_url = '';
                                                $userData = getUserById($userid);
                                                if (isset($userData->user_avatar) != '') {
                                                    $image_url = getAttachmentUrlById((int) $userData->user_avatar, 'avatar');
                                                ?>
                                        <img class="img-fluid" src="<?php echo $image_url; ?>" alt="used icon"
                                            title="used icon">
                                        <?php
                                                } else {
                                                ?>
                                        <!-- <img src="http://via.placeholder.com/35x35" alt="alt"> -->
                                        <span class="login-name"><?php echo $name; ?></span>
                                        <?php } ?>
                                    </span>
                                </span>
                                <span><i style="font-size:12px; margin-left:1px" class="fa fa-caret-down"
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
                                                if($role['role_id'] == 2) { ?>
                                        <li><a href="{{ URL::to('/store-dashboard') }}">Seller Dashboard <span
                                                    class="seller-staric">*</span></a></li>
                                        <?php } } ?>
                                        <li><a href="{{ URL::to('/buyer-orders') }} ">Purchases & Reviews</a></li>
                                        <!-- <li><a href="#">Messages</a></li> -->
                                        <li><a href="{{ URL::to('/buyer-favorites') }} ">Favorites </a></li>
                                        <li><a href="{{ URL::to('/user-info') }}  ">Account Settings</a></li>
                                        <?php
                                            $userroles = getUserroles($userid);
                                            $storeData = getStoreDataByUserId($userid);
                                            foreach ($userroles as $role) {
                                                if($role['role_id'] == 1) {  ?>
                                        <li><a href="{{ URL::to('/admin/dashboard') }}">Super Admin <span
                                                    class="superamdin-staric">*</span></a></li>
                                        <?php } } ?>
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
                                <?php }  } ?>
                            </div>
                        </li>

                        <li class="nav-item dropdown icn3 cart cart-list-nav">
                            <a class="nav-link  left-minu110" href="#" id="navbardrop1"
                                data-toggle="dropdown">
                                <!-- <i class="fa fa-shopping-basket" aria-hidden="true">
                                     </i> -->
                                <img class="headermobileicon" src="<?php echo asset('assets/images/cart.svg'); ?>" alt="cart icon"
                                    title="cart icon" width="36" height="34" alt="cart" />
                                <span><i style="font-size:12px" class="fa fa-caret-down"
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
                        <?php

                            if (Session::has('userData')) { 
                                $userId = isset(Session::get('userData')['user_id']) ? Session::get('userData')['user_id']:'';
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
            $currentRoute !== 'reports' &&
            $currentRoute !== 'store-featureproduct' &&
            $currentRoute !== 'analytics2' &&
            $currentRoute !== 'dashboard' &&
            $currentRoute !== 'managedshipping' &&
            $currentRoute !== 'importshopify' &&
            $currentRoute !== 'campaigns' &&
            $currentRoute !== 'stores' &&
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
                                        <span></span><span></span><span></span> <span></span>
                                    </div>
                                </div>
                                <a id="menu-toggle-all" href="javascript:void(0)"> <span>All</span> </a>
                            </li>
                            <?php
                        $llCategoreis = getCategoriesHome();
                        //print_rr($llCategoreis);
                        if (count($llCategoreis) > 0) {

                        }
                        $query_category_lin = '/search?';
                        foreach ($llCategoreis as $key => $val) {
                            $parent_Category_title = isset($val['parent_category']['title']) ? $val['parent_category']['title'] : '';
                            $parent_Category_slug = isset($val['parent_category']['slug']) ? $val['parent_category']['slug'] : ''; 
                             if (isset($val['childs']) && count($val['childs']) > 0) {
                                    $child_counter = 0; 
                                    if ($child_counter == 0) { ?>
                            <li class="menu-list-category"><a id="parent_category_home" class="parent_category_home"
                                    data-parent_cate_id="<?php echo $val['parent_category']['id']; ?>" href="javascript:void(0)">
                                    {{ $parent_Category_title }} </a></li>

                            <?php }
                                    $child_counter++;
                                }else{ ?>

                            <li>
                                <a class="parent_category" data-parent_cate_id="<?php echo $val['parent_category']['id']; ?>"
                                    href="<?php echo $query_category_lin . 'category=' . $parent_Category_slug; ?>"">
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
    <div class=" search-container-header" id="search-hdr-bx">
        <div class="search-body-mbt" id="search-headerInner">

            <form method="get" action="{{ route('home.search') }}" class="search-autocomplete-form">
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
                                                    <a href="/q" class="inline-block type-34-md">all
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
                                                        <a href="/q" class="inline-block type-34-md">All
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


    <script type="text/javascript">
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
                let elm = $("#sidebar-wrapper-nav");
                setTimeout(function() {
                    elm.addClass("wide");
                }, 100);

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
                    // alert();
                    if ($('.search-container-header').hasClass("show-div-search")) {
                        // alert();
                        $('.search-container-header').fadeOut(300, function() {
                            $('.search-container-header').removeClass(
                                'show-div-search');
                        });
                    } else {

                        //alert('no class');
                        $('.search-container-header').fadeIn(200, function() {
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
                        });
                    }
                });
            });
            $(document).click(function(e) {
                if (e.target.id != 'search-hdr-bx' && !$('#search-headerInner').find(e.target).length) {
                    $('.search-container-header').fadeOut(300, function() {
                        $('.search-container-header').removeClass('show-div-search');
                    });
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
        var requestt;
        jQuery(".autocomplete-field").autocomplete({
            minLength: 2,
            source: function(request, response) {

                requestt = request.term;
                jQuery.ajax({
                    url: "/esearch-keyword",
                    method: "POST",
                    data: {
                        keyword: request.term
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
                console.log(event);
                if (ui.item) {
                    val2 = ui.item.value;
                    var res = val2.split(" in category ");
                    //ParsedElements = $(htmlToParse);
                    if (res.length > 1) {
                        $('.autocomplete-field').val(res[0]);
                        jQuery('#category_parentjs').val(res[1]);
                    } else {
                        var res = val2.split(" in brands");
                        if (res.length > 1) {

                            window.location.href = "/brands?keyword=" + requestt;
                            return false;
                        }
                    }
                    $('#category_parentjs').removeAttr("disabled");
                }
                jQuery(".search-autocomplete-form").submit();
            }

        })
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
            $("#menu-toggle").on("click", function(e) {
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
            $('#menu-toggle').click(function(event) {
                event.stopPropagation();
            });
        });

        $(".hmenu-dark-bkg-color").on("click", function(e) {
            $("#sidebar-wrapper-nav").removeClass("wide");
            $("body").removeClass("wide-sidebar-nav");
        });

        $(function() {
            $("#menu-toggle-all").on("click", function(e) {
                $("#sidebar-wrapper-nav").addClass("wide");

            });
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

        .list-notifications .trash-icon {
            font-size: 14px;
        }
    </style>
