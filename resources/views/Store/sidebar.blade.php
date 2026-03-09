<style>
    html body .row-large .alert {
        top: 0px;
    }
</style>
<?php
    $userid = Session::get('userData')['user_id'];
    $storeData = getStoreDataByUserId($userid);
    $super_status = $storeData->super_status;
    $totalProducts = getTotalProductsByStore($storeData->store_id);
    $status = checkStoreActive();

    $companyActivecheck = false;
    $totalProductsActivecheck = false;
    $stripeActivecheck = false;
    $store_status = false;
    $super_status_check = false;

    if (isset($storeData) && $storeData->is_completed == '1') {
        $companyActivecheck = true;
    }
    if (isset($storeData) && $storeData->stripe == '1') {
        $stripeActivecheck = true;
    }
    if ($totalProducts >= 1) {
        $totalProductsActivecheck = true;
    }
    if ($status == 'active') {
        $store_status = true;
    }
    if ($super_status == '1') {
        $super_status_check = true;
    }
    
    ?>
@if($companyActivecheck !=true || $totalProductsActivecheck !=true  || $stripeActivecheck !=true || $store_status !=true || $super_status_check !=true)
<div class="row" id="store-dashboard-status">
    <div class="col-12 pl-0 pr-0">
        <div class="wzrdbbxot">
            <div class="wzrdlft"><img src="{{asset('assets/images/warning-sign.png')}}" alt="Warning"></div>
            <div class="wzrdrght">
                <h1><span>Hey!</span> You are just few steps away from <b>activating</b> your store.</h1>
                @if (isset($storeData) && $storeData->is_completed == 0)
                    <div class="rqustbx">
                        <span><i class="fa fa-exclamation-circle error-icon mr-2" aria-hidden="true"></i>Complete your store profile to make your store visible to others.</span>
                        <span class="mbl"><a class="btn btn-small btn-primary" href="{{URL::to('/store/edit')}}">Update Profile</a></span>
                    </div>
                @endif
                @if (isset($storeData) && $totalProducts < 1) 
                     <div class="rqustbx">
                     <span><i class="fa fa-exclamation-circle error-icon mr-2" aria-hidden="true"></i>You must add atleast <b>1</b> products in order to activate your store.</span>
                        <span class="mbl"><a class="btn btn-small btn-primary" href="{{URL::to('/add-product')}}">Add Product</a></span>
                    </div>
                    @endif
                @if (isset($storeData) && $storeData->stripe == "0")
                    <div class="rqustbx stripe-account">
                        <span><i class="fa fa-exclamation-circle error-icon mr-2 stripe-account" aria-hidden="true"></i>You must connect to stripe for payment processing.</span>
                        <span class="mbl"><a class="btn btn-small btn-primary stripe-account" href="{{URL::to('/store/payments/')}}">Connect Stripe</a></span>
                    </div>
                @endif
                
                @if($super_status == '1')
                    @if ($status == 'deactivated')
                        <div class="rqustbx">
                            <span><i class="fa fa-exclamation-circle error-icon error-class-deactive mr-2" aria-hidden="true"></i>Your store is currently inactive. To resume sales, please activate it.</span>
                            <span class="mbl"><a class="btn btn-small btn-primary" id="switch_active_status" data-value="2" data-stripe="{{$storeData->stripe}}" data-company="{{$storeData->is_completed}}" data-totalProduct="{{$totalProducts}}" href="javascript:void(0)">Activate</a></span>
                        </div>
                    @elseif($status == 'rejected')
                        <div class="rqustbx">
                            <span><i class="fa fa-exclamation-circle error-icon error-class-deactive mr-2" aria-hidden="true"></i>Your store isn't approved. Please check your email for more details.</span>
                            <span class="mbl"><a class="btn btn-small btn-primary" href="{{URL::to('/contact-us/')}}">Contact Support</a></span>
                        </div>
                    @elseif($status == 'pending')
                        <div class="rqustbx">
                            <span><i class="fa fa-exclamation-circle error-icon mr-2" aria-hidden="true"></i>Your store is currently under moderation. Kindly await approval</span>
                            <span class="mbl"><a class="btn btn-small btn-primary" href="{{URL::to('/contact-us/')}}">Contact Support</a></span>
                        </div>
                    @endif
                @endif

                @if($super_status == '0')
                <div class="rqustbx">
                    <span><i class="fa fa-exclamation-circle error-icon mr-2" aria-hidden="true"></i>Your store is restricted by Mallshark. Contact for details</span>
                    <span class="mbl"><a class="btn btn-small btn-primary" href="{{URL::to('/contact-us/')}}">Contact Support</a></span>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endif

<script>

$(document).on("click", "#switch_active_status", function() {
    var store_stripe = $(this).attr('data-stripe');
    var store_complete = $(this).attr('data-company');
    var store_totalProducts = $(this).attr('data-totalProduct');

    if(store_stripe != 1 || store_complete != 1 || store_totalProducts < 1){
        
        toastr.options = {
        closeButton: true,
        tapToDismiss: true,
        positionClass: 'toast-top-right',
        toastClass: 'toast-icon custom-toast-success',
        progressBar: true,
        };
        toastr.error("Failed! Complete your store setup to activate");
        return;
    }
    
    var res = {}
    var storeStatus = $(this).attr('data-value');
    res['store_status'] = storeStatus;
    // var result = confirm("Are you sure to change Store Status");
    swal({
            title: "Are you sure?",
            text: "Are you sure you want to change the status of your store?",
            type: "warning",
            showCancelButton: true,
            confirmButtonClass: "btn-success",
            confirmButtonText: "Yes, Do it!",
            closeOnConfirm: false
        },
        function() {
            $('#loading').show();
            swal.close();

            $.ajax({
                type: 'POST',
              //  dataType: 'json',
                url: '/store-status/change',
                data: res,
                success: function(response) {
                    
                    if(response.status){
                        $('#loading').hide();
                        $(".store_note").addClass("d-none");
                      
                         $(".switch-field").removeClass("inactive-state");
                         $(".switch-field").addClass("active-state");
                        toastr.options = {
                        closeButton: true,
                        tapToDismiss: true,
                        positionClass: 'toast-top-right',
                        toastClass: 'toast-icon custom-toast-success',
                        progressBar: true,
                        };
                        toastr.success(response.message);
                       
                    }else{
                        $('#loading').hide();
                        toastr.options = {
                        closeButton: true,
                        tapToDismiss: true,
                        positionClass: 'toast-top-right',
                        toastClass: 'toast-icon custom-toast-success',
                        progressBar: true,
                        };
                        toastr.error(response.message);
                    } 
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                },
            });
        });

});
</script>