

<nav class="dshbrd dropdown custom-dropdown-ipad mn-sec ipad-no-brder ipmt-15 ">
    
  <button class="btn btn-secondary dropdown-toggle hide-desktop" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
    Dashboard
  </button>    
    
 <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">    
    <ul class="nav hide-mobile">
        <?php
        if(collect(request()->segments())->last() == 'user-dashboard'){
            
            $active = 'crnt';
        }elseif(collect(request()->segments())->last() == 'user-account'){
            $active = 'crnt'; 
        }else{
            $active = ''; 
        }
        if (collect(request()->segments())->last() == 'user-dashboard') {
            ?>
            <li class="nav-item crnt"><a class="nav-link active" href="{{ URL::to('/user-dashboard')}}">Edit Profile</a></li>
        <?php } elseif(collect(request()->segments())->last() == 'user-account') {
            ?>
            <li class="nav-item {{$active}}"><a class="nav-link active" href="{{ URL::to('/user-dashboard')}}">Edit profile</a></li>
            <?php
        }else{ ?>
            <li class="nav-item {{$active}}"><a class="nav-link active" href="{{ URL::to('/user-account')}}">Edit Profile</a></li>

        <?php }
        ?>
       

       
        <?php
        if (collect(request()->segments())->last() == 'user-info') {
            ?>
            <li class="nav-item crnt"><a class="nav-link" href="{{ URL::to('/user-info')}}">Change Password</a></li>
        <?php } else { ?>
            <li class="nav-item"><a class="nav-link" href="{{ URL::to('/user-info')}}">Change Password</a></li>
        <?php } ?>
       
    </ul>
 </div>
</nav>