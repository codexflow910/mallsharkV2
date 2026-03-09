
  
  <style type="text/css">
  
  .change-password-page-cmt{
    transform: translateX(0);
	transition: all .8s ease;
  position: relative;
	background: #020321;
	opacity: 1;
	transform: translateX(0);
	transition: all .8s ease;
  background: #fff;
  box-shadow: 4px -1px 13px #00000029;

  z-index: 123;
  display: flex;
  justify-content: space-between;
  flex-direction: column; 
  width: 350px;
  transition: all 0.5s ease;

  }

  .sidebar.close {
    width: 78px;
}
.sidebar.close ~ .home-section {
    left: 78px;
    width: calc(100% - 78px);
}
  .sidebar {
  /* width: 240px;
  background: #fff;
  box-shadow: 4px -1px 13px #00000029;
  padding: 100px 0px 20px 0px;
  position: fixed;
  height: 100%;
  left: 0;
  top: 0;
  z-index: 999999;
  display: flex;
  justify-content: space-between;
  flex-direction: column; */
  width: 100%;
  height: 100%;
  overflow-x: hidden;
  padding: 30px 0px 20px 0px;
}
.bg-light-gray{
  background-color: #F9FBFE;
}
.sidebar .card:hover {
  box-shadow: none;
  background: transparent
}
.sidebar ul.inner-list-item li a {
  padding: 10px 0px 10px 40px;
}
.sidebar ul.inner-list-item li a:hover {
  background-color: #073ba5;
  color: #fff !important;
}
.sidebar .card {
  border-radius: 0px !important;
} 
.sidebar.collapsed {
  width: 60px;
}

.sidebar.collapsed #sidebarToggle img {
    transform: rotate(0deg);
}
#sidebarToggle img {
    width: 30px;
    opacity: .5;
    position: relative;
    left: -5px;
    top: -2px;
    transform: rotate(180deg);
}
button#sidebarToggle {
  border: 0px;
  padding-left: 30px;
  padding-top: 25px;
  color: #716C6C;
  font-weight: 600;
  background:transparent;
}
.sidebar.collapsed button#sidebarToggle {
  padding-left: 0px
}
#sidebarToggle img {
  width: 30px;
}
.sidebar ul li {
  margin: 0px;
}
.logo {
  text-align: center;
  margin-bottom: 20px;
}
ul.inner-list-item li a {
  display: block
}
.menu li {
  position: relative;
}
.menu li a {
  padding: 15px 30px
}

.menu a {
 
}


.icon {
  margin-right: 10px;
}


.sidebar.collapsed .name {
  display: none;
}
.name_title h4 {
  font-size: 18px;
  font-weight: 600;
  text-align: center
}

.sidebar .fa-user:before {
    content: "\f007";
    font-size: 24px;
    margin-right: 20px;
    color: #716C6C;

}
.sidebar .card-body  {
  padding: 0px
}
.sidebar .fa-shopping-cart:before {
    content: "\f07a";
    font-size: 24px;
    color: #716C6C;
    margin-right: 20px;
}
.sidebar .fa-heart:before {
    content: "\f004";
    font-size: 24px;
    margin-right: 15px;
    color: #716C6C;

}
.sidebar .fa-shopping-basket:before {
     
    font-size: 24px;
    margin-right: 10px;
    color: #716C6C;
    position: relative;
    left: -4px;

}

.sidebar .fa-bolt:before {    
     font-size: 34px;
     margin-right: 20px;
     color: #716C6C;
     line-height: 19px;
 
 } 

.sidebar ul.menu> li:hover > a {
  background: #0510FA;
}
.sidebar ul.menu> li:hover ul li a {
  color: #716C6C
}
.collaspe .fa-chevron-right {
  transform: rotate(90deg)
}
.menu .fa-chevron-right {
position: absolute;
right: 30px;
top: 22px;
}
.fa-chevron-right:before {
  font-weight: 400;
  color:#716C6C;

}
.inner-list-item li a {
  color: #000 !!important;
}
.sidebar ul li a {
    color: #716C6C;
    display: block;
    font-weight: 600; 
}
.sidebar ul li:hover .card-body {
  background: transparent
}
.sidebar ul li:hover a,.sidebar ul li:hover .fa-user:before,.sidebar ul li:hover .fa-shopping-cart:before,.sidebar ul li:hover .fa-heart:before,.sidebar ul li:hover .fa-chevron-right:before{
  color: #fff
}
.sidebar.collapsed li {
  padding: 10px 0px
}
.sidebar.collapsed #sidebarToggle img {
  position: relative;
    left: -3px;
    width: 30px;
}
.sidebar-footer-content .name_title h4 {
  text-align: left;
  padding-left: 30px;
  font-size: 24px;
  color: #183B56;
}
.sidebar-footer-content  ul li a {
font-size: 18px;
font-weight: 600;
color: #183B56;

}
.sidebar-footer-content .menu li a {
  padding: 5px 30px
}
.sidebar.collapsed .menu li a {
  padding: 5px 15px;
}
.sidebar.collapsed .fa-user:before {
  position: relative;
  left: 5px;
}
.sidebar.collapsed button#sidebarToggle {
    padding-left: 15px;
}
#sidebarToggle img {
    width: 30px;
    opacity: .6;
    position: relative;
    left: -5px;
    top: -2px;
}
#sidebarToggle span {
    position: relative;
    left: 10px;
}
.sidebar-content-wrapper {
    position: relative;
    height: calc(100% - 161px);
}
.sidebar-content-wrapper #close-sidebar {
    position: absolute;
    right: 5px;
    top: -20px;
    color: #716c87;
    /* border: 1px solid #0000a7; */
    height: 20px;
    width: 20px;
    border-radius: 25px;
}
.sidebar-content-wrapper .after-back-to-main{
  margin: 8px 0 !important;
}
a#close-sidebar .material-icons {
    font-size: 18px;
}

.cm-edit-ja #menu-toggle-dashboard.shopzit-menu span {
  background: #0000a7;
}
.cm-edit-ja .gap-3 {
    gap: 15px;
}
.cm-edit-ja.buyer.update-design h1.account-heading{
    padding-right: 15px;
}
.cm-edit-ja{
	position: relative;
    width: calc(100% - 0px);
	height: 100%;
	padding: 37px 30px;
	overflow-x: hidden;
	display: flex;
	align-items: center;
	transition: all .8s ease;
}
body.open-sidebar  .cm-edit-ja{
    /* width: calc(100% - 250px); */
}
body.open-sidebar .change-password-page-cmt {
    /* width: 0; */
    transform: translateX(-350px);
    margin-left: -350px;
}

.flex-parent-div{display: flex;}

.home-section {
    position: relative;
    /* background: #e4e9f7;
    height: 100vh; */
    /* left: 260px; */
    /* width: calc(100% - 0px); */
    transition: all 0.5s ease;
}
/* a#open-sidebar-admin-panel {
    opacity: 0;  
} */
body.open-sidebar a#open-sidebar-admin-panel{
  opacity: 1;
}

a.collaspe[aria-expanded="true"] .fa-chevron-right{
  transform: rotate(-90deg);
}

.sidebar-content-wrapper .card{     margin-bottom: 5px;}
.sidebar-content-wrapper ul.menu {
    height: 82%;
    overflow: hidden;
    overflow-y: auto;
    overflow-y: scroll;
    overflow-x: hidden;
    margin: 0px;
    margin-right: -10px;

}
.store-dashboard a#open-sidebar-admin-panel {
       max-height: 22px;
}

@media screen and (min-width:768px) {

  /* a#open-sidebar-admin-panel {
      opacity: 0;    display: none !important;
  } */
  body.open-sidebar a#open-sidebar-admin-panel{
    opacity: 1;display: inline-block !important;
  }

}

@media (min-width: 1700px){
    body:not(.open-sidebar) .cm-edit-ja:not(.no-full-wrap) .container {
      /* background: red; */
      /* max-width: 100% !important; */
    
  }


}




@media screen and (max-width:767px) {

  .change-password-page-cmt{
    transform: translateX(-350px);
    margin-left: -350px;
  }
  a#open-sidebar-admin-panel{opacity:1}
  body.open-sidebar .change-password-page-cmt{
    margin-left: 350px;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 100%;
    flex: 0 0 100%;
    max-width: 100%;

  }
  .cm-edit-ja{
    padding: 0;
  }

}


  </style>

 <?php
  $userid = isset(Session::get('userData')['user_id']) ? Session::get('userData')['user_id'] : "";
  $userData = getUserById($userid);
  $storeData = getStoreDataByUserId($userid);
  $storename = isset($storeData->store_name) ?  $storeData->store_name : "";
  $name = "";
  $username = $userData->name;

 ?>

<div class="sidebar noman">

  <div class="sidebar-content-wrapper">

      <a href="javascript:;" class="menu-toggle-close close-sidebar" id="close-sidebar">
          <i class="material-icons">
              clear
          </i>
      </a>

    <div class="name_title">
      {{-- <h4 style="line-height:28px">Nice to see you, {{!empty($storename) ? substr(ucfirst($storename), 0,10) : $username}}!</h4> --}}
      <h4 style="line-height:28px">Hi, {{!empty($username) ? (explode(' ', $username)[0]) : substr(ucfirst($storename), 0,10)}}!</h4>
    </div>
    <ul class="menu">
      <li>
        <a class="collaspe" data-toggle="collapse" href="#collapseExample" role="button" aria-expanded="false" aria-controls="collapseExample">
          <i class="fa fa-user" aria-hidden="true"></i>
        <span class="name"> My Account </span>
             <i class="fa fa-chevron-right name" aria-hidden="true"></i>
            </a>
          <div class="collapse" id="collapseExample">
            <div class="card-X card-body-X">
             <ul class="inner-list-item">
             <?php
                $userroles = getUserroles($userid);
                $count = 0;
                foreach ($userroles as $role) {
                 
                 if($role['role_id'] == 4){ 
                  ?>
                  @if($count == 0)
                    <li>
                      <a href="{{ URL::to('/user-dashboard') }}">User Dashboard</a>
                    </li>
                    <li>
                      <a href="{{ URL::to('/buyer-orders') }}">Orders</a>
                    </li>
                    <li>
                      <a href="{{ URL::to('/user-info') }}">Security</a>
                    </li>

                    @endif
                  <?php $count++;}
                 } ?>
             </ul>
            </div>
          </div>
      </li>
      <?php
          $userroles = getUserroles($userid);
          foreach ($userroles as $role) {
              if($role['role_id'] == 2) { ?>
      <li>
        
        <a class="collaspe" data-toggle="collapse" href="#collapseSeller" role="button" aria-expanded="false" aria-controls="collapseExample">
          <i class="fa fa-shopping-basket" aria-hidden="true"></i>
        <span class="name">My Store </span>
             <i class="fa fa-chevron-right name" aria-hidden="true"></i>
            </a>
            @env('production')
              <style>
                  .production-disable {
                      display: none;
                  }
              </style>
            @endenv
          <div class="collapse" id="collapseSeller">
            <div class="card-X card-body-X">
             <ul class="inner-list-item">
             <?php
                $userroles = getUserroles($userid);
                foreach ($userroles as $role) {
                    if($role['role_id'] == 2) { ?>
                  <li><a href="{{ URL::to('/store-dashboard') }}">Dashboard </a></li>
                  <li><a href="{{ URL::to('/brand-orders') }}">Orders </a></li>
                  <li><a href="{{ URL::to('/reports') }}">Reports</a></li>
                  <li><a href="{{ URL::to('/discounts') }}">Discounts</a></li>
                  <li><a href="{{ URL::to('/add-product') }}">Add Product </a></li>
                  <li><a href="{{ URL::to('/store/edit') }}">Manage Store </a></li>
                  <li><a href="{{ URL::to('/managedshipping') }}">Shipping Profiles </a></li>
                  <li><a href="{{ URL::to('/campaigns') }}">Campaigns </a></li>
                  <li><a href="{{ URL::to('/store-products') }}">View Products </a></li>
                  <li class="production-disable"><a href="{{ URL::to('/importshopify') }}">Import Shopify
                  <img src="{{ URL::asset('assets/images/Shopify-Logo.png') }}"
                   class="shopify-icon-image ml-1" alt="image" srcset=""></a></li>
                  <li class="production-disable"><a href="{{ URL::to('/woocommerce') }}">Import WooCommerce
                    <img src="{{ URL::asset('assets/images/WooCommerce.png') }}"
                    class="shopify-icon-image ml-1" alt="image" srcset=""></a>
                  </li>
                  <li class="production-disable"><a href="{{ URL::to('/shopify-csv') }}">Import CSV
                    <img src="{{ URL::asset('assets/images/csv-icon.png') }}"
                     class="shopify-icon-image ml-1" alt="image" srcset=""></a></li>  
                  <li><a href="{{ URL::to('/store/payments') }}">Connect Stripe   <img src="{{ URL::asset('assets/images/Stripes.png') }}"
                    class="shopify-icon-image ml-1" alt="image" srcset="" style="height: 20px;
                    width: 21px;
                    border-radius: 4px;"></a></li>
                  {{-- <li><a href="{{ URL::to('/store/paidby') }}">Connect Meta Mask  <img src="{{ URL::asset('assets/images/meta-mask.png') }}"
                    class="shopify-icon-image ml-1" alt="image" srcset=""></a></li> --}}
                  <?php } 
                   
                   }
                  ?>
             </ul>
            </div>
          </div>
      </li>
      <?php } } 

      ?>
      <?php
          $userroles = getUserroles($userid);
          foreach ($userroles as $role) {
              if($role['role_id'] == 1) { ?>
      <li>
        
        <a class="collaspe" data-toggle="collapse" href="#collapseAdmin" role="button" aria-expanded="false" aria-controls="collapseExample">
          <i class="fa fa-bolt" aria-hidden="true"></i>
        <span class="name">Superadmin</span>
             <i class="fa fa-chevron-right name" aria-hidden="true"></i>
            </a>
          <div class="collapse" id="collapseAdmin">
            <div class="card-X card-body-X">
             <ul class="inner-list-item">
             <?php
                $userroles = getUserroles($userid);
                foreach ($userroles as $role) {
                    if($role['role_id'] == 2) { ?>
                  <li><a href="{{ URL::to('/admin/dashboard') }}">Dashboard </a></li>
                  <li><a href="{{ URL::to('/admin/allusers') }}">Users </a></li>
                  <li><a href="{{ URL::to('/admin/articles') }}">Articles </a></li>
                  <li><a href="{{ URL::to('/admin/store-products') }}">Products </a></li>
                  <li><a href="{{ URL::to('/admin/stores') }}">Stores </a></li>
                  <li><a href="{{ URL::to('/admin/sale-report') }}">Sales </a></li>
                
                  <?php } 
                   
                   }
                  ?>
             </ul>
            </div>
          </div>
      </li>
      <?php } } 

      ?>

          <!-- <li>
              <a href="{{ URL::to('/brand-orders') }}">
                <i class="fa fa-shopping-cart" aria-hidden="true"></i>
                <span class="name">Orders</span>
              </a>
           </li> -->
           
                   
      <li>
        <a href="{{ URL::to('/buyer-favorites') }}">
          
          <i class="fa fa-heart"  data-placement="right" aria-hidden="true"></i>
          <span class="name">Favorites</span>
        </a>
      </li>
                  
   
     
      <!-- Add more menu items as needed -->
    </ul>

    <!-- <button id="sidebarToggle"><img src="{{ URL::asset('assets/images/play.svg') }}" alt="image" srcset="">
      <span class="name">Collapse menu</span>
    </button> -->

  </div>

  <div class="sidebar-footer-content">
   
    {{-- <div class="name_title">
      <h4>Help & Settings</h4>
    </div> --}}
    <ul class="menu">
      <li>
        <a href="{{ URL::to('/contact-us') }}">
          <span class="name">Customer Support</span>
        </a>
      </li>
      <li>
        <a href="{{ URL::to('/logout')}}">
          <span class="name">Sign Out</span>
        </a>
      </li>
      <!-- Add more menu items as needed -->
    </ul>
  </div>


 
 

  {{-- <div class="name_title">
    <h4>Nice to see you, Kevin!</h4>
  </div> --}}

</div>




<script type="text/javascript">

$(document).ready(function() {


// Desktop script
if (window.matchMedia("(min-width: 768px)").matches) {
    $("#open-sidebar-admin-panel").click(function() {
      $("body").toggleClass("open-sidebar");
    });

    $(".close-sidebar").click(function() {
      $("body").addClass("open-sidebar");
    });
  }

// Mobile script
else {
    $("#open-sidebar-admin-panel").click(function() {
      $("body").addClass("open-sidebar");
    });

    $(".close-sidebar").click(function() {
      $("body").removeClass("open-sidebar");
    });
  }




  

  // jQuery code
  const sidebar = document.querySelector('.sidebar');
  const toggleButton = document.getElementById('sidebarToggle');

  if (sidebar && toggleButton) {
    toggleButton.addEventListener('click', function () {
      sidebar.classList.toggle('collapsed');
    });
  } else {
    console.log("One or both elements not found in the DOM.");
  }

  const sidebar2 = document.querySelector('.sidebar');
  const toggleButton2 = document.getElementById('sidebarToggle2');

  if (sidebar2 && toggleButton2) {
    toggleButton2.addEventListener('click', function () {
      sidebar2.classList.toggle('collapsed');
    });
  } else {
    console.log("One or both elements not found in the DOM.");
  }

  $(function () {
    $('[data-toggle="tooltip"]').tooltip();
  });
});

  /// some script
      
      // jquery ready start
  // $(document).ready(function() {
        // jQuery code
  //       const sidebar = document.querySelector('.sidebar');
  //       const toggleButton = document.getElementById('sidebarToggle');

  //   toggleButton.addEventListener('click', function () {
  //     sidebar.classList.toggle('collapsed');
  //   });

  //   const sidebar2 = document.querySelector('.sidebar');
  //   const toggleButton2 = document.getElementById('sidebarToggle2');

  //   toggleButton2.addEventListener('click', function () {
  //     sidebar2.classList.toggle('collapsed');
  //   });

  //   $(function () {
  //     $('[data-toggle="tooltip"]').tooltip()
  //   })
  // });
   // jquery end
  </script>



