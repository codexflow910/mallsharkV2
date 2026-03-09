@extends('layouts.dashboard-main')
<!-- Main Banner Starts -->
@section('content')
@section('title', 'Import Woocommerce Products')
<!-- Content Starts -->
<style>
  .import-checkbox{
    position : relative !important;
    left: 0px !important;
  }
  

  #loading {
        position: fixed;
        display: none;
        width: 100%;
        height: 100%;
        opacity: 0.7;
        background-color: #fff;
        z-index: 99;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
    }

    #loading-image {
        position: absolute;
        top: 50%;
        z-index: 100;
        width: 150px;
        left: 50%;
        transform: translate(-50%,-50%);
    }
    .humber{
        background-color: #0000a7!important;
    }
    /* #menu-toggle-dashboard{
        margin-right: 10px;
    } */
    #open-sidebar-admin-panel{
            margin-top: 5px;
            display: block!important;
        }
    @media screen and (max-width: 573px){
        #open-sidebar-admin-panel{
            margin-top: -35px;
        }
    }
    
</style>
<div class="change-password-page-cmt">
  @include('buyer.customsidebar')

</div>
<main>
  @php
      // if (session()->has('userData')) {
      //   $userData = session()->get('userData', 'default');
      // }
  @endphp
  <!-- Dashboard Ends -->
  <div class="dshbrdin mobile-top-spacing-less cm-edit-ja">
    <div class="container mncntnr">
  @include('Store.sidebar')

      <div id="loading" class="hide-setion">
        <img id="loading-image" src="{{asset('assets/images/Loader-Animation.gif')}}" alt="Loading..." />
    </div>
      <div class="row">
        <div class="col-12 " style="padding: 0px;">
          <div class="hd-bt" style="margin-top: 25px;">
            <h3 style="margin-bottom: 25px;">
            <div class="d-flex align-items-center gap-3">                        
                        <a href="javascript:void(0)" class="d-flex align-items-center gap-10px sidebar-admin-burger-menu" id="open-sidebar-admin-panel">                                        
                        <span id="menu-toggle-dashboard" class="shopzit-menu mt-0 ml-0 bx-menu">
                            <span class="humber"></span><span class="humber"></span ><span  class="humber"></span><span  class="humber"></span>
                        </span>
                        <!-- <span>Menu</span> -->
                        </a>
                        <span style="font-size: 35px;font-weight:bold;">Import Your Products From<img style="margin-left: 10px; position:relative; width:170px"
                          src="{{asset('assets/images/wc-logo.png')}}"> </span>
                        </div>    

               </h3>
            </div>

              <div class="ShopifyCont <?php echo ($stripe === 0) ? '' : 'mb-4'; ?>" role="alert">
              <h3 class="mb-2" style="color:#bd1d1d!important;">Prerequisites for Importing Data</h3>
              <p style="color:#856404">Your products in WooCommerce must have the following data fields filled out before they can be brought over to MallShark. If the data has errors, or any of the fields mentioned below are empty, please fill them out on your WooCommerce store and then click the refresh button. Our system will detect and load them for import. </p>
              <ul class="pl-3 mb-2" style="font-size:14px">
                <li style="list-style: circle;">Title</li>
                <li style="list-style: circle;">Price</li>
                <li style="list-style: circle;">Store Currency : USD</li>
                {{-- <li style="list-style: circle;">Weight</li> --}}
                {{-- <li style="list-style: circle;">Current Stock</li>
                <li style="list-style: circle;">SKU of each Variant</li>  --}}
              </ul>
              <p style="color:#856404">(Note : If product's stock managing is disabled on WooCommerce store, in-stock status will be considered as stock = 1) </p>
              <p style="color:#856404"><a target="_blank" href="/how-to-connect-woocommerce" style="color: #0510fa;">Learn how to connect MallShark with WooCommerce Store?</a></p>
                
              </div>
              @if ($stripe == 0)
                  
              <div class="alert alert-warning" style="padding:0.75rem!important;margin-bottom:20px!important;" role="alert">
                <h3 class="mb-2" style="color:#bd1d1d;font-weight:normal!important;">To use our product import tool. It is necessary to connect your bank account with our payment gateway.<br> Please fill out your banking details by clicking <a href="{{ URL::to('/store/payments') }}"> <strong>here </strong><a>. </h3>
                </div>
              @endif


          <div class="sttbr row" style="width: 100%;overflow:hidden;overflow-x:auto;">
            <div class="col-8 nopad" style="display: flex;align-items:center;padding:0px!important;">
              <div class="  col-12 justify-content-between;" style="padding:0px!important;">
                {{-- <div class="col-3"> --}}
                  @if ($connected)
                      
                  <h1 style="padding:0px!important;"><img style="margin-right: 10px; position:relative; top:-3px;margin-left:-1px;"
                      src="{{asset('assets/images/connect.png')}}">Connected With Woocommerce Store</h1>
                  @else
                      
                {{-- </div> --}}
                    {{-- <div class="col-6 text-right"> --}}
                      <div class="row">
                        <div class="form-group col-md-6" style="padding-left: 16px;" >
                          <input class="form-control " style="border-radius:30px;" type="text" {{ $stripe == 0 ? 'disabled' : '' }} placeholder="Store URL" name="shopUrl" id="shopUrl">
                          <span class="error d-none storeUrlError">Store URL is required</span>
                        </div>
                        <div class="form-group col-md-6" style="width: 269px;">
                          <input style="border-radius:30px;" class="form-control" type="text" {{ $stripe == 0 ? 'disabled' : '' }} placeholder="Consumer Key" name="consumerKey" id="consumerKey">
                          <span class="error d-none consumerKeyError">Consumer key is required</span>
                        </div>
                      </div>
                      <div class="row">
                        <div class="form-group col-md-6" style="margin-bottom:0px;padding-left: 16px;">
                          <input style="border-radius:30px;" class="form-control " type="text" {{ $stripe == 0 ? 'disabled' : '' }} placeholder="Consumer Secret" name="consumerSecret" id="consumerSecret">
                          <span class="error d-none storeUrlError">Consumer secret is required</span>
                        </div>
                        
                      </div>
                      
                      
                  @endif
                    {{-- </div> --}}
              </div>
            </div>
            <div class="col-4 text-right" style="padding:0px!important;text-align:end;" >
              @if ($connected)
              @if (!$foundError)
                
              <a class="shpbtn"  href="#"  id="importProducts" style="{{ $stripe == 0 ? 'background-color: #0510fa40;  pointer-events: none;text-decoration: none;cursor: default;' : '' }}" {{ $stripe == 0 ? 'disabled' : '' }}>Import Products</a>
              @endif    
              &nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;
              <a href="#" id="disconnectWoo"  style="{{ $stripe == 0 ? ' pointer-events: none;cursor: default;background-color:#d52f5052;' : '' }}" {{ $stripe == 0 ? 'disabled' : '' }}>Disconnect</a></div>
              @else
              <span class="connection-failed error" style="display: none; padding-right:10px">Wrong URL or Access Token</span>    
              <a href="#" {{ $stripe == 0 ? 'disabled' : '' }} style="{{ $stripe == 0 ? 'background-color: #0510fa40;  pointer-events: none;text-decoration: none;cursor: default;' : '' }}"
               id="connectShopify">Connect</a></div>
              {{-- <a href="#" {{ $stripe == 0 ? 'class=disabled-link' : '' }} style="{{ $stripe == 0 ? 'color: #b7bfa9;' : '' }}" id="connectShopify">Connect</a> --}}

              @endif
              
          </div>
               @if ($errors->any())
               <div class="alert alert-danger">
                   <ul>
                       @foreach ($errors->all() as $error)
                           <li>{{ $error }}</li>
                       @endforeach
                   </ul>
               </div>
              @endif
            
          <div class="content">

          </div>
          <div class="alert alert-danger import-error" style="display: none; font-size:16px" role="alert">Failed to fetch products</div>
          @if ($foundError && $connected)
            
          <div class="alert alert-danger found-error" style="display: Block; font-size:16px" role="alert">Failed! Woocommerce Store currency should be USD</div>
          @endif

          
          <!-- Table Ends -->
          {{-- <input type="checkbox" class="wooCommerceCheckbox" > --}}
          {{-- @if(Session::has('userData') && isset(Session::get('userData')['consumerKey']) && isset(Session::get('userData')['shopUrl'])) --}}
          {{-- @if($stripe == 1 && $connected && !$foundError )
          <div class="text-right" style="margin-top: 30px; margin-bottom:30px"><a class="btn btn-primary" id="addToMallshark"
              style="border:0px; background-color:#202020; color:#fff; border-radius:0px">Add Products To MallShark</a>
          </div>
          @endif --}}
          {{-- @endif --}}

        </div>
      </div>
    </div>

  </div><!-- dshbrdin -->



</main>
<!-- Content Ends-->

<style>
  body .ui-sortable-helper {
    position: relative !important;
    z-index: inherit !important;
  }

  .product-slide-values .slick-slide {
    display: block !important;
  }

  .values_product_visibility {
    display: none;
  }

  .pp_pic_holder {
    top: 0% !important;
  }

  .pp_expand {
    display: none !important;
  }
</style>
<script>
  $.ajaxSetup({headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')}});
  $('#connectShopify').on('click',function(e){
    e.preventDefault();
    $('.storeUrlError').addClass('d-none');
    $('.consumerKeyError').addClass('d-none');
    $('.connection-failed').hide()


    let consumerKey = $('#consumerKey').val()
    let consumerSecret = $('#consumerSecret').val()
    let shopUrl = $('#shopUrl').val()
    if(consumerKey && shopUrl){
      let data = {
        consumerKey,
        consumerSecret,
        shopUrl
      }
      $.ajax({
        url: "{{ route('connectWoocommerce') }}",
        type: 'POST',
        data: data,
        success: function(data){
          
          if(data?.status){
            
            // $('#importProducts').show()
            // $('#disconnectWoo').show()
            // $('#connectShopify').hide()
            console.log(data?.status);
            window.location.reload();
          }else{
            $('.connection-failed').show()
          }
        },
        error: function(error){
          console.log('error',error)
        }
      })
    }else{
      if(!shopUrl)
      $('.storeUrlError').removeClass('d-none');
      if(!consumerKey)
      $('.consumerKeyError').removeClass('d-none');
    }
  })
  $('#disconnectWoo').on('click',function(e){
    
    e.preventDefault();
      $.ajax({
        url: "{{ route('disconnectWoocommerce') }}",
        type: 'get',

        success: function(data){
          if(data?.status){
            
            // $('#importProducts').show()
            // $('#disconnectWoo').show()
            // $('#connectShopify').hide()
            console.log(data?.status);
            window.location.reload();
          }
        },
        error: function(error){
          console.log('error',error)
        }
      })
    
  })
  $('#importProducts').on('click',function(e){
    e.preventDefault();
    $('#loading').show()
    
      $.ajax({
        url: "{{ route('importWoocommerce') }}", //continue writing this function
        type: 'get',
        success: function(data){
          if(data?.status){
            $('.content').html(data?.view);
            $('.import-error').hide()
            $('#loading').hide()
            $('#importProducts').text('Refresh Products')
          }else{
            
            $('.content').html(null);
            $('.import-error').show()
            $('#loading').hide()
          }
        },
        error: function(error){
          $('#loading').hide()
          console.log('error',error)
        }
      })
      // $('#loading').hide()
    
  })
//   $('#addToMallshark').on('click',function(e){
//     e.preventDefault();
//     $('#loading').show()
//     let errors = false;
//     let selected = $('input.select-import:checked');
//     let productsData = [];
//     let counter = 0;
//     let focusHere = null;
//     selected.each(function() {
//   let productId = $(this).val();
//   let categoryId = $(this).closest('tr.product-row').find('select.category').val();
  
//   let profileId = $(this).closest('tr.product-row').find('select.profile').val();
//   if(!categoryId){
//     errors = true;
    
//     if ($(this).closest('tr.product-row').find('select.category').next('.error').length === 0) {
      
//      $(this).closest('tr.product-row').find('select.category').after('<span class="error">Select Category</span>');
      
//     }
//     if(counter == 0){
      
//       focusHere = $(this).closest('tr.product-row'); 
      
      
//     }
    
//     counter++;
//   }else{
    
//     productsData.push({productId,categoryId,profileId});
//   }
// });

// if(!errors && productsData.length > 0){
  
//   $.ajax({
//     url : "{{ route('saveWooProducts') }}",
//     type: "POST",
//     data : {productsData},
//     success : function(response){
//       $('#loading').hide()
//       window.location.replace('/store-products')
//     },
//     error : function(error){
//       $('#loading').hide()
//     }
//   })
  
// }else{
//   $('#loading').hide()
//   $('html, body').scrollTop(focusHere.offset().top-100);
// }
  


// })

function sCategoryChanged(e){
let sCategoryId = e.dataset.key;
let mCategoryId = e.value;
if(sCategoryId  && mCategoryId){
  $('select.category.'+sCategoryId).each(function(){
    $(this).val(mCategoryId)
  });
  

}

}
function naCategoryChanged(e){
let mCategoryId = e.value;
if(mCategoryId){
  $('select.category.NA').each(function(){
    $(this).val(mCategoryId)
  });
  

}

}
function profileChanged(e){
  let sKey = e.dataset.key;
  let mProfileId = e.value;
  if(sKey && mProfileId){
    let set = $('select.profile.'+sKey).each(function(){
      $(this).val(mProfileId)
    })
    
  }
}
function naProfileChanged(e){
  
  let mProfileId = e.value;
  if(mProfileId){
    let set = $('select.profile.NA').each(function(){
      $(this).val(mProfileId)
    })
    
  }
}


</script>

@endsection