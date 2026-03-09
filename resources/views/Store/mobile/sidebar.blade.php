<div class="dropdown mn-sec">
    <button type="button" class="btn btn-primary btn-block btn-simple btn-sec dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
        Dashboard
    </button>
    <div class="dropdown-menu" x-placement="bottom-start" style="position: absolute; transform: translate3d(-5px, 54px, 0px); top: 0px; left: 0px; will-change: transform;">
        <?php if (collect(request()->segments())->last() == 'store-dashboard') { ?>
            <a class="dropdown-item crnt" href="{{URL::to('/store-dashboard')}}">Brand Dashboard</a>
        <?php } else { ?>
            <a class="dropdown-item" href="{{URL::to('/store-dashboard')}}">Brand Dashboard</a>
        <?php } ?>
        <div class="dropdown-divider"></div>
        <?php if (collect(request()->segments())->last() == 'edit') { ?>
            <a class="dropdown-item crnt" href="{{URL::to('/store/edit')}}">Company Profile</a>
        <?php } else { ?>
            <a class="dropdown-item" href="{{URL::to('/store/edit')}}">Company Profile</a>
        <?php } ?>
        <div class="dropdown-divider"></div>  
        <?php if (collect(request()->segments())->last() == 'store-products') { ?>
            <a class="dropdown-item crnt" href="{{URL::to('/store-products')}}">Products</a>
        <?php } else { ?>
            <a class="dropdown-item" href="{{URL::to('/store-products')}}">Products</a>
        <?php } ?>
        <div class="dropdown-divider"></div>
        <?php if (collect(request()->segments())->last() == 'add-product') { ?>

            <a class="dropdown-item crnt" href="{{URL::to('/add-product')}}">Add Products</a>
        <?php } else { ?>
            <a class="dropdown-item " href="{{URL::to('/add-product')}}">Add Products</a>
        <?php } ?>
        <div class="dropdown-divider"></div>
        <?php if (collect(request()->segments())->last() == 'reports') { ?>
            <a class="dropdown-item crnt" href="{{URL::to('/store/reports')}}">Reports</a>
        <?php } else { ?>
            <a class="dropdown-item" href="{{URL::to('/store/reports')}}">Reports</a>
        <?php } ?>
        <div class="dropdown-divider"></div>
        <?php if (collect(request()->segments())->last() == 'payments') { ?>
            <a class="dropdown-item crnt" href="{{URL::to('/store/payments')}}">Payments</a>
        <?php } else { ?>
            <a class="dropdown-item " href="{{URL::to('/store/payments')}}">Payments</a>
        <?php } ?>
        <div class="dropdown-divider"></div>    
        <?php if (collect(request()->segments())->last() == 'brand-orders') { ?>
            <a class="dropdown-item crnt" href="{{URL::to('/brand-orders')}}">Orders</a>
        <?php } else { ?>
            <a class="dropdown-item" href="{{URL::to('/brand-orders')}}">Orders</a>
        <?php } ?>
        <div class="dropdown-divider"></div>
    
       
        <?php if (collect(request()->segments())->last() == 'templates') { ?>
            <a class="dropdown-item crnt" href="{{URL::to('/store/templates')}}">Templates</a>
        <?php } else { ?>
            <a class="dropdown-item" href="{{URL::to('/store/templates')}}">Templates</a>
        <?php } ?>
        <div class="dropdown-divider"></div>     
        <a class="dropdown-item" href="{{URL::to('/logout')}}">Logout</a>
    </div>
</div>
