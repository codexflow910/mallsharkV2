<nav class="dshbrd clearfix dropdown custom-dropdown-ipad mn-sec ipad-no-brder ipmt-15 ">
    <button class="btn btn-secondary dropdown-toggle hide-desktop" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
        Dashboard
    </button>  
    <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">     
        <ul class="nav hide-mobile">
            <?php
            if (collect(request()->segments())->last() == 'store-dashboard') {
                ?>
                <li class="nav-item crnt"><a class="nav-link active" href="{{URL::to('/store-dashboard')}}">Dashboard</a></li>
                <?php
            } else {
                ?>
                <li class="nav-item"><a class="nav-link active" href="{{URL::to('/store-dashboard')}}">Dashboard</a></li>
                <?php
            }
            ?>
            <?php
            if (collect(request()->segments())->last() == 'edit') {
                ?>
                <li class="nav-item crnt"><a class="nav-link" href="{{URL::to('/store/edit')}}">Company profile</a></li>
                <?php
            } else {
                ?>
                <li class="nav-item"><a class="nav-link" href="{{URL::to('/store/edit')}}">Company profile</a></li>
                <?php
            }
            ?>
            <?php
            if (collect(request()->segments())->last() == 'store-products') {
                ?>
                <li class="nav-item crnt"><a class="nav-link active" dropdown-toggle="" href="#" id="navbardropone" data-toggle="dropdown" aria-expanded="false" href="{{URL::to('/store-products')}}">Products</a>
                    <div class="dropdown-menu" id="navbardropone">
                        <ul class="dropdown-menu-nav" id="submenu">
                            <li>
                                <a href="{{URL::to('/addproduct')}}">Add Product</a>
                            </li>
                            <li>
                                <a href="{{URL::to('/store-products')}}">View All Products</a>
                            </li>                
                        </ul>
                    </div>

                </li>
                <?php
            } else {
                ?>
                <li class="nav-item"><a class="nav-link active" dropdown-toggle="" href="#" id="navbardropone" data-toggle="dropdown" aria-expanded="false" href="{{URL::to('/store-products')}}">Products</a>
                    <div class="dropdown-menu" id="navbardropone">
                        <ul class="dropdown-menu-nav" id="submenu">
                            <li>
                                <a href="{{URL::to('/addproduct')}}">Add Product</a>
                            </li>
                            <li>
                                <a href="{{URL::to('/store-products')}}">View All Products</a>
                            </li>                
                        </ul>
                    </div>
                </li>
                <?php
            }
            ?>


            <?php
            if (collect(request()->segments())->last() == 'reports') {
                ?>
                <li class="nav-item crnt"><a class="nav-link" href="{{URL::to('/store/reports')}}">Reports</a></li>     
                <?php
            } else {
                ?>

                <li class="nav-item"><a class="nav-link" href="{{URL::to('/store/reports')}}">Reports</a></li>             


                <?php
            }
            ?>
            <?php
            if (collect(request()->segments())->last() == 'payments') {
                ?>
                <li class="nav-item crnt"><a class="nav-link" href="{{URL::to('/store/payments')}}">Payment</a></li>     
                <?php
            } else {
                ?>

                <li class="nav-item"><a class="nav-link" href="{{URL::to('/store/payments')}}">Payment</a></li>
                <?php
            }
            ?>
            <?php
            if (collect(request()->segments())->last() == 'brand-orders') {
                ?>
                <li class="nav-item crnt"><a class="nav-link" href="{{URL::to('/brand-orders')}}">Orders</a></li>     
                <?php
            } else {
                ?>

                <li class="nav-item"><a class="nav-link" href="{{URL::to('/brand-orders')}}">Orders</a></li>             


                <?php
            }
            ?>
            <?php
            // if (collect(request()->segments())->last() == 'purchase-credits') {
                ?>
                <!-- <li class="nav-item crnt"><a class="nav-link" href="{{URL::to('/purchase-credits')}}">Credits</a></li>      -->
                <?php
            // } else {
                ?>

                <!-- <li class="nav-item"><a class="nav-link" href="{{URL::to('/purchase-credits')}}">Credits</a></li>              -->


                <?php
            // }
            ?>
            <?php
            if (collect(request()->segments())->last() == 'tickets') {
                ?>
                <li class="nav-item crnt"><a class="nav-link" href="{{URL::to('/store/tickets')}}">Support</a></li>    
                <?php
            } else {
                ?>
                <li class="nav-item"><a class="nav-link" href="{{URL::to('/store/tickets')}}">Support</a></li>
                <?php
            }
            ?>

            <?php
            if (collect(request()->segments())->last() == 'templates') {
                ?>
                <li class="nav-item crnt"><a class="nav-link" href="{{URL::to('/store/templates')}}">Templates</a></li>    
                <?php
            } else {
                ?>
                <li class="nav-item"><a class="nav-link" href="{{URL::to('/store/templates')}}">Templates</a></li>
                <?php
            }
            ?>
            <li class="nav-item"><a class="nav-link" href="{{URL::to('/logout')}}">Logout</a></li>  
        </ul>
    </div>
</nav>


