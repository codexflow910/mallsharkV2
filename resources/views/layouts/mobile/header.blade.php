<!doctype html>
<html lang="en">
    <head>
        <title>@yield('title')</title>
        <!-- Required meta tags mobile-->
        <meta charset="utf-8">
        <!-- <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no"> -->
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="shortcut icon" type="image/x-icon" href="{{ URL::asset('assets/images/mallsharkicon.png') }}">
      
        
        <meta name="title" content="@yield('title')"/>
        <meta name="description" content="@yield('description')"/>
        <meta name="keyword" content="@yield('keyword')"/>
        <link rel='canonical' href='{{url()->current()}}' />
        <meta name="csrf-token" content="{{ csrf_token() }}">
        @yield('meta')
        <!-- Bootstrap CSS -->
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
        <link rel="stylesheet" href="{{asset('assets/css/style.css',env('HTTPS_STATUS'))  }}" type="text/css">
        <link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet">
        <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
        <link href="https://fonts.googleapis.com/css?family=Roboto:400,700" rel="stylesheet">
        
        <link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
        <link rel="stylesheet" href="{{ URL::asset('assets/css/theme-style.css' ,env('HTTPS_STATUS')) }}" type="text/css">
        <link rel="stylesheet" href="{{ URL::asset('assets/css/reject.css' ,env('HTTPS_STATUS')) }}" type="text/css">
        <link rel="stylesheet" href="{{ URL::asset('assets/css/ishi-style.css' ,env('HTTPS_STATUS')) }}" type="text/css">
        <!--Mobile Style-->        
        <link rel="stylesheet" href="{{ URL::asset('assets/css/mobile-style.css' ,env('HTTPS_STATUS')) }}" type="text/css">        
        <link rel="stylesheet" href="{{ URL::asset('assets/css/custom.css' ,env('HTTPS_STATUS')) }}" type="text/css">
        <!--HEADER SCRIPTS -->
        <script src="https://code.jquery.com/jquery-1.12.4.js"></script>
        <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-tagsinput/0.8.0/bootstrap-tagsinput.css" type="text/css">
       <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-tagsinput/0.8.0/bootstrap-tagsinput.min.js"></script>
        <script src="{{ URL::asset('assets/js/autocomplete.min.js' ,env('HTTPS_STATUS')) }}"></script>
        <script src="{{ URL::asset('assets/js/reject.js' ,env('HTTPS_STATUS')) }}"></script>
        <!-- {!! Minify::javascript(['reject.js','cmGauge.js','product-script.js','fone-format.js','sweetalert.js','offset.js','autocomplete.min.js','jquery.plugin.min.js','jquery.countdown.new.js']) !!} -->

        <style>
            .slickSliderForMobile .slick-arrow.slick-next {
                font-size: 0px;
                cursor: pointer;
                right:0;
            }            
            .slickSliderForMobile .slick-arrow{
                right: 0px;
                left: inherit;
                height: 33px;
                top: -26px;
                background-color: #f1f1f1;
                border: 0;
                min-width: 26px;
                max-width: 25px;

            }
                    .slick-arrow {
            position: absolute;
            top: 50%;
            z-index: 50;
            margin-top: -12px;
        }
                .slickSliderForMobile .slick-arrow.slick-next:before {
            /* position: absolute; */
            content: "\f054";
            font: normal normal normal 15px/1 FontAwesome;
            top: 50%;
            margin-top: -6px;
            left: 15px;color: #195dfa;
        }
        .slickSliderForMobile .slick-arrow.slick-prev {
            right: 28px;
            font-size: 0px;
            cursor: pointer;
        }
        .slickSliderForMobile .slick-arrow.slick-prev:before {
                /* position: absolute; */
                content: "\f053";
                font: normal normal normal 15px/1 FontAwesome;
                top: 50%;
                margin-top: -6px;color: #195dfa;
            }
            .reward-conainer-filter ul{
                display: block;
            }
            .reward-conainer-filter{
                padding-right: 0px;
            }
            .slickSliderForMobile a.show-more-results {
                display: none;
            }
            .reward-conainer-filter{
                overflow-x: initial;
                border: 0px;
            }
            .reward-conainer-filter ul li a{
                padding-top: 9px;
                padding-bottom: 9px;
            }
            .homePage-heading {
                margin-bottom: 8px;
            }

        </style>

    </head>
    <?php
    $bodyClass = '';
    if (Session::has('userData')) {
        $bodyClass = 'logged-in';
    }
    ?>
    <body class="{{$bodyClass}}">
        <input type="hidden" name="site_url" id="site_url" value="<?php echo URL::to('/'); ?>">
        <!-- Header Starts Here -->
        <header class="d-flex justify-content-between bd-highlight mobile-header " id="mobile-header">


<?php if (Session::has('userData')) { ?>

                <div id="sidebar-wrapper-nav">
                    <div id="sidebar-wrapper-left">
                        <div class="wrapin">
                            <div class="head-section">
                                <a href="javascript:;" class="menu-toggle-close">
                                    <i class="material-icons">
                                        clear
                                    </i>
                                </a>
                                <a class="navbar-brand nav-logo" href="{{URL::to('/')}}">
                                    <!-- <img src=" {{ asset('assets/images/logo.png',env('HTTPS_STATUS')) }}" alt="MallShark logo" title="MallShark logo">  -->
                                </a>                            

                            </div>
                            <hr>

                            <div id="accordion-nav">
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
                                $userid = Session::get('userData')['user_id'];
                                $userroles = getUserroles($userid);
                                $userroutes = array('user-dashboard', 'user-info', 'user-profile', 'buyer-orders');
                                $stor = array('store-dashboard', 'edit', 'add-product', 'store-products', 'reports', 'payments', 'brand-orders', 'templates');
                                $show = '';
                                $arialExpanded = 'false';
                                $showstore = '';
                                foreach ($userroles as $role) {
                                    ?>
                                    <?php if ($role['role_id'] == 4) { ?>
                                        <div class="card user-disable">
                                            <div class="card-header" id="heading-1">
                                                <span class="mb-0">
                                                    <?php
                                                    $lasturlIndex = collect(request()->segments())->last();
                                                    if (in_array($lasturlIndex, $userroutes)) {
                                                        $show = 'show';
                                                        $arialExpanded = 'true';
                                                    }
                                                    ?>                                                    
                                                    <a role="button" data-toggle="collapse" href="#collapse-1" aria-expanded="<?php echo $arialExpanded; ?>" aria-controls="collapse-1">
                                                        <span class="with-icon"><i class="material-icons">person</i></span>
                                                        Profile
                                                    </a>
                                                </span>
                                            </div>
                                            <?php
                                            $lasturlIndex = collect(request()->segments())->last();
                                            if (in_array($lasturlIndex, $userroutes)) {
                                                $show = 'show';
                                            }
                                            ?>
                                            <div id="collapse-1" class="collapse  <?php echo $show; ?>" data-parent="#accordion-nav" aria-labelledby="heading-1">
                                                <div class="card-body">
                                                    <?php
                                                    if (collect(request()->segments())->last() == 'user-dashboard') {
                                                        ?>
                                                        <a class="nav-link active" href="{{ URL::to('/user-dashboard')}}">Dashboard</a>
                                                    <?php } else {
                                                        ?>
                                                        <a class="nav-link " href="{{ URL::to('/user-dashboard')}}">Dashboard</a>
                                                        <?php
                                                    }
                                                    ?>                                        




                                                    <!-- <//?php
                                                    if //(collect(request()->segments())->last() == 'user-profile') {
                                                        ?>
                                                        <a class="nav-link active" href="{{ URL::to('/user-profile')}}">Kingdom</a>
                                                    <//?php } else { ?>
                                                        <a class="nav-link" href="{{ URL::to('/user-profile')}}">Kingdom</a>
                                                    <//?php } ?>              

                                                    <a href="javascirpt:;" style="display:none;" > 

                                                        <span>Reviews</span>
                                                    </a> -->


                                                    <?php if (collect(request()->segments())->last() == 'buyer-orders') { ?>
                                                        <a class="nav-link active" href="{{ URL::to('/buyer-orders')}}">Orders</a>
                                                    <?php } else { ?>
                                                        <a class="nav-link" href="{{ URL::to('/buyer-orders')}}">Orders</a>
                                                    <?php } ?>            



                                                </div>
                                            </div>
                                        </div>
                                        <?php
                                    }
                                    if ($role['role_id'] == 2) {
                                        ?>
                                        <div class="card">
                                            <div class="card-header" id="heading-2">
                                                <span class="mb-0">
                                                    <?php
                                                    $lasturlIndex = collect(request()->segments())->last();
                                                    if (in_array($lasturlIndex, $stor)) {
                                                        $showstore = 'show';
                                                        $arialExpanded = 'true';
                                                    }
                                                    ?>                                                    
                                                    <a class="collapsed" role="button" data-toggle="collapse" href="#collapse-2" aria-expanded="<?php echo $arialExpanded; ?>" aria-controls="collapse-2">
                                                        <span class="with-icon"><i class="material-icons">assessment</i></span>
                                                        Store
                                                    </a>
                                                </span>
                                            </div>
                                            <?php
                                            $lasturlIndex = collect(request()->segments())->last();
                                            if (in_array($lasturlIndex, $stor)) {
                                                $showstore = 'show';
                                            }
                                            ?>
                                            <div id="collapse-2" class="collapse <?php echo $showstore; ?>" data-parent="#accordion-nav" aria-labelledby="heading-2">
                                                <div class="card-body">

                                                    <?php
                                                    if (collect(request()->segments())->last() == 'store-dashboard') {
                                                        ?>
                                                        <a class="nav-link active" href="{{URL::to('/store-dashboard')}}">Dashboard</a>
                                                        <?php
                                                    } else {
                                                        ?>
                                                        <a class="nav-link" href="{{URL::to('/store-dashboard')}}">Dashboard</a>
                                                        <?php
                                                    }
                                                    ?>                                        


                                                    <?php
                                                    if (collect(request()->segments())->last() == 'add-product') {
                                                        ?>
                                                        <a class="nav-link active" href="{{URL::to('/add-product')}}">Add Product</a>
                                                        <?php
                                                    } else {
                                                        ?>
                                                        <a class="nav-link" href="{{URL::to('/add-product')}}">Add Product</a>
                                                        <?php
                                                    }
                                                    ?>                  



                                                    <?php
                                                    if (collect(request()->segments())->last() == 'store-products') {
                                                        ?>
                                                        <a class="nav-link active" href="{{URL::to('/store-products')}}">View All Products</a>
                                                        <?php
                                                    } else {
                                                        ?>
                                                        <a class="nav-link" href="{{URL::to('/store-products')}}">View All Products</a>
                                                        <?php
                                                    }
                                                    ?>   
                                                    <?php
                                                    // if (collect(request()->segments())->last() == 'campaign') {
                                                        ?>
                                                        <!-- <a class="nav-link active" href="{{URL::to('/store/campaign')}}">Campaigns</a> -->
                                                        <?php
                                                    // } else {
                                                        ?>
                                                        <!-- <a class="nav-link" href="{{URL::to('store/campaign')}}">Campaigns</a> -->
                                                        <?php
                                                    // }
                                                    ?>
                                                    <?php
                                                    if (collect(request()->segments())->last() == 'reports') {
                                                        ?>
                                                        <a class="nav-link active" href="{{URL::to('/store/reports')}}">Reports</a>
                                                        <?php
                                                    } else {
                                                        ?>

                                                        <a class="nav-link" href="{{URL::to('/store/reports')}}">Reports</a>


                                                        <?php
                                                    }
                                                    ?>

                                                    <?php
                                                    if (collect(request()->segments())->last() == 'brand-orders') {
                                                        ?>
                                                        <a class="nav-link active" href="{{URL::to('/brand-orders')}}">Orders</a>
                                                        <?php
                                                    } else {
                                                        ?>

                                                        <a class="nav-link" href="{{URL::to('/brand-orders')}}">Orders</a>


                                                        <?php
                                                    }
                                                    ?>    





                                                </div>
                                            </div>
                                        </div>
                                        <?php }
                                    ?>
                                    <?php if ($role['role_id'] == 1) { ?>
                                        <div class="menu-link-nav hidden-mobile">

                                            <a href="<?php echo URL::to('admin/dashboard'); ?>">
                                                <span class="with-icon"> 
                                                    <i class="material-icons">dashboard</i>
                                                </span>                                        

                                                Admin Dashboard</a></div>
                                    <?php
 }
                                }
                                ?>
                            </div>                

                            <div class="menu-link-nav">
                            <?php
                            if (Session::has('userData')) {
                                $userid = Session::get('userData')['user_id'];
                                $userData = getUserById($userid);
                                $name = "";
                                $username = $userData->name;
                                $words = preg_split("/\s+/", $username);
                                foreach ($words as $w) {
                                    $name .= strtoupper($w[0]);
                                }
                                $storeData = getStoreDataByUserId($userid);
                                if (empty($storeData)) {
                                    ?>
                                    <li><a href="{{URL::to('/sellonmallshark')}}"> <span class="with-icon"><i class="material-icons">assessment</i></span> Become a Seller </a></li>
                                    <?php
                                }
                            } else {
                                ?>
                                <li><a href="{{URL::to('/login')}}">Become a Seller  <span class="with-icon"><i class="material-icons">assessment</i></span></a></li>
                            <?php } ?>
    <?php if (collect(request()->segments())->last() == 'buyer-favorites') { ?>
                                    <a class="nav-link active" href="{{ URL::to('/buyer-favorites')}}">
                                        <span class="with-icon"> 
                                            <i class="material-icons">favorite_border</i>
                                        </span>
                                        Favorites</a>
    <?php } else { ?>
                                    <a class="nav-link" href="{{ URL::to('/buyer-favorites')}}">
                                        <span class="with-icon"> 
                                            <i class="material-icons">favorite_border</i>
                                        </span> Favorites
                                    </a>
    <?php } ?>

                                <a class="nav-link awesm-fnt" href="{{ URL::to('/logout')}}">
                                    <span class="with-icon"> 
                                        <i class="material-icons">power_settings_new</i>
                                    </span>
                                    <span class="logout-lnk">Logout</span>
                                </a>
                            </div>

                        </div>
                    </div>  
                </div>        
<?php } ?>

            
            <!-- Left Nav Starts -->
            <nav class="navlft col text-left hidden">
                <ul>
                    </li>
                    <?php
                    if (Session::has('userData')) {
                        $userid = Session::get('userData')['user_id'];
                        $userData = getUserById($userid);
                        $name = "";
                        $username = $userData->name;
                        $words = preg_split("/\s+/", $username);
                        foreach ($words as $w) {
                            $name .= strtoupper($w[0]);
                        }
                        $storeData = getStoreDataByUserId($userid);
                        if (empty($storeData)) {
                            ?>
                            <li><a href="{{URL::to('/sellonmallshark')}}">Become a Seller</a></li>
                            <?php
                        }
                    } else {
                        ?>
                        <li><a href="{{URL::to('/login')}}">Become a Seller</a></li>
                    <?php } ?>
                </ul>
            </nav>
            <!-- Left Nav Ends -->

            <div class="text-center mobile-logo">
                <!-- Brand Starts Here -->
                <a class="navbar-brand" href="{{URL::to('/')}}"><img
                        src=" {{ asset('assets/images/logo.png',env('HTTPS_STATUS')) }}" alt="MallShark logo" title="MallShark logo"> </a>
                <!-- Brand Ends Here -->
            </div>
            <!-- Right Nav Starts -->
            <nav class="icnbar navrght col text-right menu-strip">
                <ul>
                <li>
                        <a href="javascript:;" class="search-icon-header">
                            <!-- <i class="fa fa-search" aria-hidden="true"> </i> -->
                            <img    src="<?php echo asset('uploads/headericons/search.svg') ?>" height="20" alt="search" title="search"/>
                        </a>
                    </li>
            
                    <li class="nav-item dropdown" id="login-dropdown-mbt">
                        <a nav-link dropdown-toggle href="#" id="navbardrop0" data-toggle="dropdown">
                            <?php
                            if (!Session::has('userData')) {
                                ?>
                                <!-- <i class="fa fa-user-o" aria-hidden="true"></i> -->
                            <img class="headermobileicon" src="<?php echo asset('assets/images/user.svg') ?>" height="34" alt="user" title="user"/>
                            <?php } else { ?>
                                <span class="user-image">
                                    <span class="head-upper-mbt">
                                        <?php
                                        $image_url = '';
                                        $userData = getUserById($userid);
                                        if ($userData->user_avatar != '') {
                                            $image_url = getAttachmentUrlById((int) $userData->user_avatar, 'avatar');
                                            ?>
                                            <img class="img-fluid" src="<?php echo $image_url; ?>" alt="used icon" title="used icon">
                                            <?php
                                        } else {
                                            ?>
                                        <!-- <img src="http://via.placeholder.com/35x35" alt="alt"> -->
                                            <span class="login-name"><?php echo $name; ?></span>
                                        <?php } ?>
                                    </span>
                                </span>
                            <?php } ?>
                        </a>
                        <div class="dropdown-menu" id="navbardrop0">
                            <?php
                            if (Session::has('userData')) {
                                ?>
                                <div class="login-menu text-center">
                                    <div class="name-sec-menu">
                                        <span class="image-outer-mbt">
                                            <span class="inner-image-ct">
                                                <?php
                                                $userData = getUserById($userid);
                                                if ($userData->user_avatar != '') {
                                                    $image_url = getAttachmentUrlById((int) $userData->user_avatar, 'avatar');
                                                    ?>
                                                    <img class="img-fluid" src="<?php echo $image_url; ?>"
                                                         alt="user icon" title="user icon">
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
                                            <?php echo $username = $userData->name; ?>
                                        </span>
                                    </div>
                                    <?php
                                    $userroles = getUserroles($userid);
                                    $storeData = getStoreDataByUserId($userid);
                                    foreach ($userroles as $role) {

                                        if ($role['role_id'] == 4) {
                                            ?>
                                            <div class="manage-account">
                                                <a href="{{ URL::to('/user-dashboard')}}">Dashboard</a>
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
                                                        <a href="{{ URL::to('/store-dashboard')}}">
                                                            <img class="img-fluid image-default-width"
                                                                 src="<?php echo $imgeUrl; ?>" alt="{{$storeData->store_name}}" title="{{$storeData->store_name}}">
                                                        </a>
                                                    </div>
                                                <?php } else { ?>
                                                    <a class="manage-account"
                                                       href="{{ URL::to('/store-dashboard')}}"><?php echo $storeData->store_name; ?></a>
                                                   <?php } ?>
                                            </div><!-- dashboard-menu -->
                                            <!--                                            <div class="manage-account">
                                             </div>-->
                                            <?php
                                        }

                                        if ($role['role_id'] == 1) {
                                            ?>
                                            <div class="manage-account">
                                                <a href="{{ URL::to('/admin/dashboard')}}">Admin Dashboard</a>
                                            </div>
                                            <?php
                                        }
                                        if ($role['role_id'] == 5) {
                                            ?>
                                            <div class="manage-account">
                                                <a href="{{ URL::to('/support/dashboard')}}">Support Dashboard</a>
                                            </div>
                                            <?php
                                        }
                                    }
                                    ?>
                                </div>
                                <a class="dropdown-item" href="{{ URL::to('/logout')}}">Log out</a>
                            <?php } else { ?>
                                <a class="dropdown-item" href="{{ URL::to('/signup')}}">Sign Up</a>
                                <a class="dropdown-item" href="{{ URL::to('/login')}}">Login</a>
                            <?php } ?>
                        </div>
                    </li>

                    <li class="nav-item dropdown">
                        <a class="nav-link  left-minu110" href="#" id="navbardrop1" data-toggle="dropdown">
                            <!-- <i class="fa fa-shopping-basket" aria-hidden="true">
                            </i> -->
                            <img class="headermobileicon" src="<?php echo asset('assets/images/cart.svg') ?>" alt="cart icon" title="cart icon" height="34" alt="cart"/>
                                <?php
                                $count = 0;

                                $cartdata = cartDetail();
                                if (!empty($cartdata)) {
                                    $count = count($cartdata);
                                }
                                ?>

                            <span class="badge badge-primary" id="cart_counter"><?php echo $count; ?></span>
                        </a>
                        <div class="dropdown-menu" id="navbardrop-dropdown">
                            <div class="cartmenu">

                                @include('cart.carthtml')
                            </div>
                        </div>
                    </li>
                    <?php
                    if (Session::has('userData')) {
                        ?>

                        <?php $res = getmess(); ?>
                        <li class="nav-item dropdown notifi-email" style="display:none;">
                            <a href="#" id="menu1" data-toggle="dropdown">
                                <!-- <i class="fa fa-envelope-o" aria-hidden="true"></i> -->
                            <img class="headermobileicon" src="<?php echo asset('uploads/headericons/message.svg') ?>" height="34" alt="message" title="message"/>
                                
                                <span class="badge badge-success"><?php echo count($res); ?></span>
                            </a>
                            <div class="dropdown-menu left-position" id="">
                                <ul>
                                    <?php
                                    $res = getmess();
                                    if (!empty($res)) {
                                        foreach ($res as $e) {
                                            $userData = getUserByIdchat($e);
                                            $url = URL::to('/chat/' . $userData->id);
                                            ?>
                                            <li>
                                                <p>You have received message from <a href="#"
                                                                                     onclick="return popitup('<?php echo $url; ?>')"
                                                                                     class="message-received"><?php echo $userData->name; ?>

                                                    </a></p>
                                            </li>
                                            <?php
                                        }
                                    } else {
                                        ?>
                                        <li>
                                            <p>you have no message.</p>
                                        </li>

                                    <?php } ?>
                                </ul>
                            </div>
                        </li>

                    <?php } ?>
                    <?php
$res = getuserNotifications();
$class = "";
if (isset($res['unread']) && $res['unread'] <= 0) {
    $class = "zero";
}
?>

<li class="nav-item dropdown notify notifi-message-nav <?php $class; ?>" id="new-noti">
    <a href="#" class="notify-anchor" id="" data-toggle="dropdown">
        <!-- <i class="fa fa-bell-o" aria-hidden="true"></i> -->
        <img class="headermobileicon" src="<?php echo asset('uploads/headericons/notifications.svg') ?>" height="34" alt="notifications" title="notifications"/>

        <span class="badge badge-success badge-num arrow-explore <?php echo $class; ?>" id="mark-read">
            <div class="entypo-bell" href="javascript:;"><?php echo isset($res['unread']) ? $res['unread'] : 0; ?></div>
        </span>                                   
    </a>
    <div class="dropdown-menu dropdown-menu-right">
        <ul class="dropdown-menu-list" id="show-noti" onclick="event.stopPropagation();">

            <?php
            $counter = count($res['notification']);

            if ($counter > 0) {
                foreach ($res['notification'] as $nf) {
                    ?>
                    <li>
                        <div> <span class="time"><?php echo time_ago_in_php($nf['created_at']); ?></span> <span class="details">  <b> </b><a href="<?php echo $nf['url_link']; ?>"><?php echo $nf['subject']; ?></a>. </span> </div>
                    </li>
                    <?php
                }
                if ($counter >= 10) {
                    ?>
                    <li><a href="<?php echo URL::to('/all-notifications'); ?>">View all</a></li>
                    <?php
                }
            } else {
                ?>
                <li> <div><span class="details">No new notifications. </span> </div></li>
                <?php } ?>
        </ul>
    </div>
</li>

<!--                     <li class="nav-item dropdown menu-drop"><a nav-link dropdown-toggle href="#" id="navbardrop4"                                                     data-toggle="dropdown"><i class="fa fa-bars" aria-hidden="true"></i></a>
                        <div class="dropdown-menu" id="navbardrop3">
                            <a class="dropdown-item" href="{{URL::To('/')}}">Home</a>
                            <a class="dropdown-item" href="{{URL::To('/about-us')}}">About</a>
                            <a class="dropdown-item" href="{{URL::To('/store/edit')}}">Sell</a>
                            <a class="dropdown-item" href="{{URL::To('/faqs')}}">FAQs</a>
                            <a class="dropdown-item" href="{{URL::To('/about-us')}}">Learn</a>
                            <a class="dropdown-item" href="{{URL::To('/about-us')}}">Help</a>
                            <a class="dropdown-item" href="{{URL::To('/contact-us')}}">Contact</a>
                        </div>
                    </li> -->

                    <?php
                    if (Session::has('userData')) {
                        ?>

                    <li class="nav-item dropdown menu-drop">
                        <div id="menu-toggle" class="shopzit-menu">
                            <span></span>
                            <span></span>
                            <span></span>
                            <span></span>
                        </div>                        
                    </li>
                       <?php } ?>

                       <li class="nav-item dropdown">
                     <a href="javascript:;" class="shopzit-menu"  id="menu-toggle"><i class="fa fa-bars" aria-hidden="true"></i></a>
                     </li>
                </ul>
            </nav>

            <!-- Right Nav Ends -->
            <?php rediredctUrls(); ?>

        </header>

        <div class="search-container-header">
            <div class="search-body-mbt">
            
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
        <form method="get" action="{{route('home.search') }}">
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
                                                    <a href="/search?recent=on"
                                                        class="inline-block type-34-md">recent addition</a>
                                                </li>
                                                <!-- <li>
                                                    <a href="/search?stores=1"
                                                        class="inline-block type-34-md">Supported by your herd</a>
                                                </li> -->
                                                <li>
                                                    <a href="/brands" class="inline-block type-34-md">All Brands</a>
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
                                                    <li class="{{$class_have_child}}">
                                                        <a style="display:inline-block"
                                                            class="inline-block type-34-md <?php echo $class_have_child; ?>"
                                                            href="{{$query_category_lin}}category=<?php echo $parent_Category_slug; ?>">{{$parent_Category_title}}
                                                            <span
                                                                class="pro-cntr">(<?php echo $parent_totald; ?>)</span>
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
                                                                <a href="{{$query_category_lin}}category=<?php echo $parent_Category_slug; ?>"
                                                                    class="px2 py1 transition-all">All Products</a>
                                                            </li>
                                                            <?php } $child_counter++; ?>
                                                            <li class="inline-block mr1 mb2"><a
                                                                    class="px2 py1 transition-al"
                                                                    href="{{$query_category_lin}}category=<?php echo isset($newVal['slug']) ? $newVal['slug'] : ''; ?>"><?php echo isset($newVal['title']) ? $newVal['title'] : ''; ?></a>
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
        $(document).ready(function () {

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

            $(function () {
                $('.search-icon-header').click(function (event) {
                    // alert();
                    if ($('.search-container-header').hasClass("show-div-search")) {
                        // alert();
                        $('.search-container-header').fadeOut(300, function () {
                            $('.search-container-header').removeClass('show-div-search');
                        });
                    } else {

                        //alert('no class');
                        $('.search-container-header').fadeIn(200, function () {
                            $('.search-container-header').addClass('show-div-search');
                        });
                    }

                    
                });
                
                
                
                $('.cross-search-header').click(function (event) {
                    if ($('.search-container-header').hasClass("show-div-search")) {
                        // alert();
                        $('.search-container-header').fadeOut(300, function () {
                            $('.search-container-header').removeClass('show-div-search');
                        });
                    }
                });
            });
            $(document).on("click", function (e) {
                // if ($(e.target).is(".search-container-header") === false) {
                //   $(".search-container-header").removeClass("show-div-search");

                // }
            });
            $(".img-check").click(function () {
                $(this).parent().toggleClass("check-checkbox");
            });
        });
        // jQuery(".autocomplete-field").autocomplete({
        //     minLength: 2,
        //     source: function (request, response) {
        //         jQuery.ajax({
        //             url: "/esearch-keyword",
        //             method: "POST",
        //             data: {
        //                 keyword: request.term
        //             },
        //             success: function (data) {
        //                 response(data);
        //             }
        //         });
        //     },
        //     select: function (event, ui) {
        //         if (ui.item) {
        //             $('.autocomplete-field').val(ui.item.value);
        //         }
        //         jQuery(".search-autocomplete-form").submit();
        //     }

        // });
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
        jQuery(".autocomplete-field2").autocomplete({
            minLength: 2,
            source: function (request, response) {
                jQuery.ajax({
                    url: "/esearch-keyword-ads",
                    method: "POST",
                    data: {
                        keyword: request.term
                    },
                    success: function (data) {
                        response(data);
                    }
                });
            },
            select: function (event, ui) {
                if (ui.item) {
                    $('.autocomplete-field2').val(ui.item.value);
                }
                jQuery(".search-autocomplete-form").submit();
            }
        });
        jQuery(".autocomplete-field2").autocomplete({
            minLength: 2,
            source: function (request, response) {
                jQuery.ajax({
                    url: "/esearch-keyword-ads",
                    method: "POST",
                    data: {
                        keyword: request.term
                    },
                    success: function (data) {
                        response(data);
                    }
                });
            },
            select: function (event, ui) {
                jQuery(".search-autocomplete-form").submit();
            }

        });
        jQuery(".autocomplete-field3").autocomplete({
            minLength: 2,
            source: function (request, response) {
                jQuery.ajax({
                    url: "/esearch-keyword-user",
                    method: "POST",
                    data: {
                        keyword: request.term
                    },
                    success: function (data) {
                        response(data);
                    }
                });
            }

        });



        $(function () {
        $("#menu-toggle").on("click", function (e) {
        $("#sidebar-wrapper-nav").addClass("wide");
        });
        $(".menu-toggle-close").on("click", function (e) {
        $("#sidebar-wrapper-nav").removeClass("wide");
        });
        $(document).click(function (e) {
        if (e.target.id != 'sidebar-wrapper-nav' && !$('#sidebar-wrapper-nav').find(e.target).length) {
        $("#sidebar-wrapper-nav").removeClass("wide");
        }
        });
        $('#menu-toggle').click(function (event) {
        event.stopPropagation();
        });
        });

        // $(".m.notify-anchor").on('click', function () {
        $(".notify-anchor").click(function () {
        var data = {};
        sendreq(data, 'user', 'read', 'callbackreq');
        });
        function callbackreq() {
            $(".entypo-bell").html("0");
            $("#new-noti").addClass("zero");
        }
    </script>
    <script>
        function popitup(url) {
            newwindow = window.open(url, 'name', 'height=800,width=800');
            if (window.focus) {
                newwindow.focus()
            }
            return false;
        }
        // $(document).ready(function () {

        //     $.reject({
        //         reject: {
        //             // safari: true, // Apple Safari  
        //             //chrome:true, // Google Chrome  
        //             //firefox: true, // Mozilla Firefox  
        //             msie: 5, // Microsoft Internet Explorer  
        //             opera: true, // Opera  
        //             konqueror: true, // Konqueror (Linux)  
        //             unknown: true // Everything else  

        //         },
        //         closeCookie: true,
        //     }); // Customized Browsers  

        //     return false;

        // });
        
        
        
        
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
            border: 0px;
        }

        .ui-state-active,
        .ui-widget-content .ui-state-active,
        .ui-widget-header .ui-state-active,
        a.ui-button:active,
        .ui-button:active,
        .ui-button.ui-state-active:hover {
            border: 1px solid #faf9f8;
            background: #faf9f8 !important;
            font-weight: normal;
            color: #000;

        }
        .headermobileicon{
            width: 24px !important;
            margin-bottom: 10px; 
        }
.navrght .badge {
    top: 3px;
    left: 22px;
}
.notifi-message-nav span.badge.badge-success.badge-num.arrow-explore {
    left: 20px;
}
.shopzit-menu {margin: 0;}
.mobile-header nav ul li > a {
    padding: 5px;
}
.mobile-header nav ul li > a {
    padding: 0;
    width: 40px;
    text-align: center;
}
.navrght .dropdown {
    position: relative;
    margin-left: 0;
}
.icnbar.navrght ul {
    float: none;
    display: inline-block;
    margin-top: 0px;
}
a.search-icon-header {
    position: relative;
    top: -6px;
}

li.nav-item.dropdown.menu-drop {
    margin-left: 10px;
}
span#cart_counter {
    position: absolute;
    top: 12px;
}
.icnbar.navrght ul #navbardrop-dropdown {
    min-width: 361px;
    transform: translate3d(-175px,85px,0px);
}

header .dropdown-menu.show {
    display: block;
    line-height: normal;
    top: -27px!important;
}
.list-container-scroll {
    max-height: auto;
    overflow-y: auto;
    overflow-x: hidden;
}
    </style>