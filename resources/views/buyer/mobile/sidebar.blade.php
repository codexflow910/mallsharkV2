<div class="dropdown mn-sec">
    <button type="button" class="btn btn-primary btn-block btn-simple btn-sec dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
        Dashboard
    </button>
    <div class="dropdown-menu" x-placement="bottom-start" style="position: absolute; transform: translate3d(-5px, 54px, 0px); top: 0px; left: 0px; will-change: transform;">
        <?php
        if (collect(request()->segments())->last() == 'user-dashboard') {
            ?>
            <a class="dropdown-item crnt" href="{{URL::to('/user-dashboard')}}">User Dashboard</a>
        <?php } else { ?>
            <a class="dropdown-item" href="{{URL::to('/user-dashboard')}}">User Dashboard</a>
        <?php } ?>
        <div class="dropdown-divider"></div>
        <?php
        if (collect(request()->segments())->last() == 'user-info') {
            ?>
            <a class="dropdown-item crnt" href="{{URL::to('/user-info')}}">My Account</a>
        <?php } else { ?>
            <a class="dropdown-item" href="{{URL::to('/user-info')}}">My Account</a>
        <?php } ?>
        <div class="dropdown-divider"></div>  
        <?php
        if (collect(request()->segments())->last() == 'user-account') {
            ?>
            <a class="dropdown-item crnt" href="{{URL::to('/user-account')}}">User Info</a>
        <?php } else { ?>
            <a class="dropdown-item" href="{{URL::to('/user-account')}}">User Info</a>
        <?php } ?>
        <div class="dropdown-divider"></div>
        <?php
        if (collect(request()->segments())->last() == 'buyer-orders') {
            ?>
            <a class="dropdown-item crnt" href="{{URL::to('/buyer-orders')}}">Orders</a>
        <?php } else { ?>
            <a class="dropdown-item" href="{{URL::to('/buyer-orders')}}">Orders</a>
        <?php } ?>
        <div class="dropdown-divider"></div>
        <!-- </?php
       // if (collect(request()->segments())->last() == 'tickets') {
            ?>
            <a class="dropdown-item crnt" href="
            {{-- {{URL::to('/user/tickets')}} --}}
            ">Support</a>
        <//?php// } else { ?>
            <a class="dropdown-item" href="
            {{-- {{URL::to('/user/tickets')}} --}}
            ">Support</a>
        <//?php// } ?> -->
        <!-- <div class="dropdown-divider"></div> -->
        <?php if (collect(request()->segments())->last() == 'buyer-favorites') { ?>
            <a class="dropdown-item crnt" href="{{URL::to('/buyer-favorites')}}">Favorites</a>
        <?php } else { ?>
            <a class="dropdown-item" href="{{URL::to('/buyer-favorites')}}">Favorites</a>
        <?php } ?>
        <!-- <div class="dropdown-divider"></div>    
        <a class="dropdown-item" href="{{URL::to('#')}}">Wallet</a> -->
        <div class="dropdown-divider"></div>     
        <a class="dropdown-item" href="{{URL::to('/logout')}}">Logout</a>     
    </div>
</div>
