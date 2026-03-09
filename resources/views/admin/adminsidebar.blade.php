
<!-- dshbrd clearfix dropdown custom-dropdown-ipad mn-sec ipad-no-brder ipmt-15 -->
<nav class="dshbrdClass">
        <div class="clickable-formenu py-2 d-flex justify-content-center alignment-items-center">
            <!-- Brand Starts Here -->
            <a class="navbar-brand m-0" href="{{ URL::to('/') }}"><img
                    src="{{ asset('assets/images/logo.svg', env('HTTPS_STATUS')) }}"
                    alt="Mallshark logo" title="Mallshark logo" class="logo-white" style="width: 120px; position:relative;"> </a>
            <!-- Brand Ends Here -->
        </div>
    <button class="btn btn-secondary dropdown-toggle hide-desktop d-none" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
        Dashboard
    </button>  
    <div class="sidebar-scroll" aria-labelledby="">     
        <ul class="sideBarNav hide-mobile">
            <?php
            if (collect(request()->segments())->last() == 'dashboard') {
                ?>
                <li class="nav-Item crnt"><a class="nav-link active" href="{{ URL::to('/admin/dashboard')}}">Dashboard</a></li>
            <?php } else {
                ?>
                <li class="nav-Item"><a class="nav-link active" href="{{ URL::to('/admin/dashboard')}}">Dashboard</a></li>
                <?php
            }
            ?>
            @if (canAccess('manage-categories'))
            <?php
            if (collect(request()->segments())->last() == 'categories') {
                ?>
                <li class="nav-Item crnt"><a class="nav-link" href="{{URL::to('admin/categories')}}">Categories</a></li>
                <?php
            } else {
                ?>
                <li class="nav-Item"><a class="nav-link" href="{{URL::to('admin/categories')}}">Categories</a></li>
                <?php
            }
            ?>
            
            <?php
            if (collect(request()->segments())->last() == 'postcategories') {
                ?>
                <li class="nav-Item crnt"><a class="nav-link" href="{{URL::to('admin/postcategories')}}">Post Categories</a></li>
                <?php
            } else {
                ?>
                <li class="nav-Item"><a class="nav-link" href="{{URL::to('admin/postcategories')}}">Post Categories</a></li>
                <?php
            }
            ?>
            @endif
            @if (canAccess('manage-cms-pages'))
            <?php
            if (collect(request()->segments())->last() == 'articles') {
                ?>
                <li class="nav-Item crnt"><a class="nav-link" href="{{URL::to('admin/articles')}}">Articles</a></li>
                <?php
            } else {
                ?>
                <li class="nav-Item"><a class="nav-link" href="{{URL::to('admin/articles')}}">Articles</a></li>
                <?php
            }
            ?>
            @endif
            
            @if (canAccess('manage-faqs'))
            <?php
            if (collect(request()->segments())->last() == 'faqs') {
                ?>
                <li class="nav-Item crnt"><a class="nav-link" href="{{URL::to('admin/faqs')}}">Faqs</a></li>
                <?php
            } else {
                ?>
                <li class="nav-Item"><a class="nav-link" href="{{URL::to('admin/faqs')}}">Faqs</a></li>
                <?php
            }
            ?>
            @endif
            @if (canAccess('manage-cms-pages'))
            <?php
            if (collect(request()->segments())->last() == 'content-pages') {
                ?>
                <li class="nav-Item crnt"><a class="nav-link" href="{{URL::to('/admin/content-pages')}}">Content Pages</a>
                </li>
                <?php
            } else {
                ?>
                <li class="nav-Item"><a class="nav-link" href="{{URL::to('/admin/content-pages')}}">Content Pages</a></li>
                <?php
            }
            ?>
            @endif
            @if (canAccess('view-users'))
            <?php
            if (collect(request()->segments())->last() == 'allusers') {
                ?>
                <li class="nav-Item crnt"><a class="nav-link" href="{{URL::to('admin/allusers')}}">Users</a></li>
                <?php
            } else {
                ?>
                <li class="nav-Item"><a class="nav-link" href="{{URL::to('admin/allusers')}}">Users</a></li>
                <?php
            }
            ?>
            @endif
            @if (canAccess('view-orders'))
            <?php
            if (collect(request()->segments())->last() == 'orders' || Route::is('orderDetailsAdmin')) {
                ?>
                <li class="nav-Item crnt"><a class="nav-link" href="{{URL::to('admin/orders')}}">Orders</a></li>
                <?php
            } else {
                ?>
                <li class="nav-Item"><a class="nav-link" href="{{URL::to('admin/orders')}}">Orders</a></li>
                <?php
            }
            ?>
            @endif
            @if (canAccess('view-orders'))
            <?php
            if (collect(request()->segments())->last() == 'crypto-refunds') {
                ?>
                <li class="nav-item crnt"><a class="nav-link" href="{{URL::to('admin/crypto-refunds')}}">Crypto Refunds</a></li>
                <?php
            } else {
                ?>
                <li class="nav-item"><a class="nav-link" href="{{URL::to('admin/crypto-refunds')}}">Crypto Refunds</a></li>
                <?php
            }
            ?>
            @endif
            @if (canAccess('view-sales-reports'))
            <?php
            if (collect(request()->segments())->last() == 'sale-report') {
                ?>
                <li class="nav-Item crnt"><a class="nav-link" href="{{URL::to('admin/sale-report')}}">Sales Report</a></li>
                <?php
            } else {
                ?>
                <li class="nav-Item"><a class="nav-link" href="{{URL::to('admin/sale-report')}}">Sales Report</a></li>
                <?php
            }
            ?>
            @endif

            @if (canAccess('view-vendors'))
            <?php
            if (collect(request()->segments())->last() == 'mission-complete-sellers') {
                ?>
                <li class="nav-item crnt"><a class="nav-link" href="{{URL::to('admin/mission-complete-sellers')}}">Mission Complete Sellers</a></li>
                <?php
            } else {
                ?>
                <li class="nav-item"><a class="nav-link" href="{{URL::to('admin/mission-complete-sellers')}}">Mission Complete Sellers</a></li>
                <?php
            }
            ?>
            @endif
            @if (canAccess('view-feature-products'))
            <?php
            if (collect(request()->segments())->last() == 'store-featureproduct') {
                ?>
                <li class="nav-Item crnt"><a class="nav-link" href="{{URL::to('/store-featureproduct')}}">Featured Products</a></li>
                <?php
            } else {
                ?>
                <li class="nav-Item"><a class="nav-link" href="{{URL::to('/store-featureproduct')}}">Featured Products</a></li>
                <?php
            }
            ?>
            @endif
            <?php
            if (collect(request()->segments())->last() == 'analytics2') {
                ?>
                <li class="nav-Item crnt"><a class="nav-link" href="{{URL::to('admin/analytics2')}}">Google Analytics</a></li>
                <?php
            } else {
                ?>
                <li class="nav-Item"><a class="nav-link" href="{{URL::to('admin/analytics2')}}">Google Analytics</a></li>
                <?php
            }
            ?>
            @if (canAccess('manage-youtube-videos'))
            <?php
            if (collect(request()->segments())->last() == 'youtube-videos') {
                ?>
                <li class="nav-Item crnt"><a class="nav-link" href="{{URL::to('admin/youtube-videos')}}">Youtube Videos</a></li>
                <?php
            } else {
                ?>
                <li class="nav-Item"><a class="nav-link" href="{{URL::to('admin/youtube-videos')}}">Youtube Videos</a></li>
                <?php
            }
            ?>
            @endif
            @if (canAccess('manage-seller-packages'))
            <?php
            if (collect(request()->segments())->last() == 'mallshark-purchased-packages') {
                ?>
                <li class="nav-Item crnt"><a class="nav-link" href="{{URL::to('admin/mallshark-purchased-packages')}}">Purchased Packages</a></li>
                <?php
            } else {
                ?>
                <li class="nav-Item"><a class="nav-link" href="{{URL::to('admin/mallshark-purchased-packages')}}">Purchased Packages</a></li>
                <?php
            }
            ?>
            @endif
            @if (canAccess('manage-banners'))
            <?php
            if (collect(request()->segments())->last() == 'banner-management') {
                ?>
                <li class="nav-Item crnt"><a class="nav-link" href="{{URL::to('slides/banner-management')}}">Banner Management</a></li>
                <?php
            } else {
                ?>
                <li class="nav-Item"><a class="nav-link" href="{{URL::to('slides/banner-management')}}">Banner Management</a></li>
                <?php
            }
            ?>
            @endif
            @if (canAccess('manage-banners'))
            <?php
            if (collect(request()->segments())->last() == 'slides-sets') {
                ?>
                <li class="nav-item crnt"><a class="nav-link" href="{{URL::to('slides/sets')}}">Slides Sets</a></li>
                <?php
            } else {
                ?>
                <li class="nav-item"><a class="nav-link" href="{{URL::to('slides/sets')}}">Slides Sets</a></li>
                <?php
            }
            ?>
            @endif
            @if (canAccess('manage-roles'))
            <?php
            if (collect(request()->segments())->last() == 'roles') {
                ?>
                <li class="nav-item crnt"><a class="nav-link" href="{{URL::to('admin/roles')}}">Roles Management</a></li>
                <?php
            } else {
                ?>
                <li class="nav-item"><a class="nav-link" href="{{URL::to('admin/roles')}}">Roles Management</a></li>
                <?php
            }
            ?>
            @endif
            @if (canAccess('manage-distributors'))
            <?php
            if (collect(request()->segments())->last() == 'list') {
                ?>
                <li class="nav-item crnt"><a class="nav-link" href="{{URL::to('admin/distributors/list')}}">Distributors</a></li>
                <?php
            } else {
                ?>
                <li class="nav-item"><a class="nav-link" href="{{URL::to('admin/distributors/list')}}">Distributors</a></li>
                <?php
            }
            ?>
            @endif
            @if (canAccess('manage-distributors'))
            <?php
            if (collect(request()->segments())->last() == 'brands-list') {
                ?>
                <li class="nav-item crnt"><a class="nav-link" href="{{URL::to('admin/distributor/brands-list')}}">Brands</a></li>
                <?php
            } else {
                ?>
                <li class="nav-item"><a class="nav-link" href="{{URL::to('admin/distributor/brands-list')}}">Brands</a></li>
                <?php
            }
            ?>
            @endif
            <li class="nav-item"><a class="nav-link" href="{{URL::to('/logout')}}">Logout</a></li>
        </ul>
    </div>
</nav>