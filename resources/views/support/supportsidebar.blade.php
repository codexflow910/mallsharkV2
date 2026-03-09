<nav class="dshbrd">

    <div class="dropdown show-mobile-mbt clearfix mobile-navigation-menu-mbt">
        <span class="pull-left option-admin-mbt">admin</span>

        <a href="javascript:;" class="btn btn-default navbar-toggle dropdown-toggle pull-right mobile-option-nav" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
<!--                 <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>         -->
            <i class="fa fa-plus" aria-hidden="true"></i>

        </a>
        <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
            <ul class="nav">
                <?php
                if (collect(request()->segments())->last() == 'dashboard') {
                    ?>
                    <li class="nav-item crnt"><a class="nav-link active" href="{{ URL::to('/support/dashboard')}}">Dashboard</a></li>
                <?php } else {
                    ?>
                    <li class="nav-item"><a class="nav-link active" href="{{ URL::to('/support/dashboard')}}">Dashboard</a></li>
                    <?php
                }
                ?>
                <?php if (collect(request()->segments())->last() == 'support-filters') { ?>
                    <li class="nav-item crnt"><a class="nav-link" href="{{ URL::to('support-filters')}}">Listing Filters</a></li>
                <?php } else { ?>
                    <li class="nav-item"><a class="nav-link" href="{{ URL::to('support-filters')}}">Listing Filters</a></li>
                <?php } ?>


                <?php if (collect(request()->segments())->last() == 'email-templates') { ?>
                    <li class="nav-item crnt"><a class="nav-link hide-div" href="{{ URL::to('/email-templates')}}">Email Templates</a></li>
                <?php } else { ?>
                    <li class="nav-item "><a class="nav-link hide-div" href="{{ URL::to('/email-templates')}}">Email Templates</a></li>
                <?php } ?>
                <?php
                if (collect(request()->segments())->last() == 'ticket-management') {
                    ?>
                   <li class="nav-item crnt"><a class="nav-link" href="">Ticket Management</a></li>   
                    <?php
                } else {
                    ?>
                  <li class="nav-item"><a class="nav-link" href="">Ticket Management</a></li>   
                    <?php
               }

                if (collect(request()->segments())->last() == 'users') {
                    ?>
                    <li class="nav-item crnt"><a class="nav-link" href="{{URL::to('/support/allusers')}}">Users</a></li>     
                    <?php
                } else {
                    ?>
                    <li class="nav-item"><a class="nav-link" href="{{URL::to('support/allusers')}}">Users</a></li>             
                    <?php
                }
                ?>
              
                <li class="nav-item"><a class="nav-link hide-div" href="#" >Wallet</a></li>     
                <li class="nav-item"><a class="nav-link hide-div" href="{{URL::to('/logout')}}">Logout</a></li>  
            </ul> 
        </div>
    </div>
    <ul class="nav hide-mobile">
        <?php
        if (collect(request()->segments())->last() == 'dashboard') {
            ?>
            <li class="nav-item crnt"><a class="nav-link active" href="{{ URL::to('/support/dashboard')}}">Dashboard</a></li>
        <?php } else {
            ?>
            <li class="nav-item"><a class="nav-link active" href="{{ URL::to('/support/dashboard')}}">Dashboard</a></li>
            <?php
        }
        ?>
        <?php if (collect(request()->segments())->last() == 'filters') { ?>
            <li class="nav-item crnt"><a class="nav-link" href="{{ URL::to('/support-filters')}}">Listing Filters</a></li>
        <?php } else { ?>
            <li class="nav-item"><a class="nav-link" href="{{ URL::to('/support-filters')}}">Listing Filters</a></li>
        <?php } ?>



        <?php if (collect(request()->segments())->last() == 'email-templates') { ?>
            <li class="nav-item crnt"><a class="nav-link hide-div" href="{{ URL::to('/email-templates')}}">Email Templates</a></li>
        <?php } else { ?>
            <li class="nav-item "><a class="nav-link hide-div" href="{{ URL::to('/email-templates')}}">Email Templates</a></li>
        <?php } ?>
        <?php
//        if (collect(request()->segments())->last() == 'movers') {
//            ?>
<!--            <li class="nav-item crnt"><a class="nav-link" href="{{URL::to('/movers')}}">Movers</a></li>     -->
            <?php
//        } else {
//            ?>
<!--            <li class="nav-item"><a class="nav-link" href="{{URL::to('/movers')}}">Movers</a></li> -->
            <?php
//        }

//        if (collect(request()->segments())->last() == 'ticket-management') {
            ?>
            <!--<li class="nav-item crnt"><a class="nav-link" href="">Ticket Management</a></li>     -->
            <?php
//        } else {
            ?>
          <!--  <li class="nav-item"><a class="nav-link" href="">Ticket Management</a></li>     -->
            <?php
//        }


        if (collect(request()->segments())->last() == 'users') {
            ?>
            <li class="nav-item crnt"><a class="nav-link" href="{{URL::to('support/allusers')}}">Users</a></li>     
            <?php
        } else {
            ?>
            <li class="nav-item"><a class="nav-link" href="{{URL::to('support/allusers')}}">Users</a></li>             
            <?php
        }
        ?>  
        <li class="nav-item"><a class="nav-link" href="{{URL::to('/logout')}}">Logout</a></li>  
    </ul>
</nav>
