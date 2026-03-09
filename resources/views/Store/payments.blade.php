@extends('layouts.dashboard-main')
@section('content')
@section('title', 'Store Payment')
<main>

    <style>
    .noteactive {
        position: absolute;
        z-index: 9999;
        width: 100%;
        height: 100%;
        left: 0px;
        right: 0px;
    }

    html body .switch-buttonk input {
        position: static !important;
        z-index: 88;
    }

    .alert {
        margin-top: 0px;
        margin-bottom: 30px;
        /* position: absolute; */
        width: auto;
        left: 0;
        right: 0;
        margin-left: auto;
        margin-right: auto;
        z-index: 999;
        /* top: 142px; */
    }

    .hide-setion {
        display: none;
    }

    .show_button {
        display: block;
    }

    .disbaled {
        pointer-events: none;
        opacity: 0.5;
    }

    .error {
        color: red;
    }


    #loading {
        position: fixed;
        display: block;
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
    .main-div{
        border: 1px solid #ddd;
        border-radius: 12px;
        padding:40px;
        float: none;
        clear: both;
        display: block;
        width: 100%;
        margin-bottom: 40px;
        /* text-align: CENTER; */
    }
    li {
        font-size: 14px;
        margin-left: 10px;
        /* list-style-type: circle; */
    }
    .center{
        text-align: CENTER;
    }
    .store-dashboard .hd-bt{ line-height: 1;}
    .store-dashboard .hd-bt a#open-sidebar-admin-panel {
        max-height: initial !important;
    }
    .disconnected-stripe {
        background-color: #d52f50 !important;
        color: #fff !important;
    }
    .active-stripe{
        background-color: #0510fa !important;
        color: #fff !important;
    }

    </style>
<div class="flex-parent-div bg-light-gray">
    <div class="change-password-page-cmt">
        @include('buyer.customsidebar')
    </div>
    
    <div class="dshbrdin store-dashboard home-section cm-edit-ja">
        <div class="container">
            <div class="row">     
                <div class="container">
                    <div class="row">
                        <div class="col-md-12">
                            @include('Store.sidebar')
                            <div class="d-flex  justify-content-between align-items-center">
                                <div class="hd-bt">
                                    <h3 class="buyer-order-heading"> 
                                        <div class="d-flex align-items-center mt-4 gap-3" style="margin-top: 35px!important;">                        
                                            <a href="javascript:void(0)" class="d-flex align-items-center gap-10px sidebar-admin-burger-menu" id="open-sidebar-admin-panel">                                        
                                            <span id="menu-toggle-dashboard" class="shopzit-menu mt-5 ml-0 bx-menu">
                                                <span></span><span></span><span></span> <span></span>
                                            </span>
                                            <!-- <span>Menu</span> -->
                                            </a>
                                            <span>Manage Bank Info</span>
                                        </div>    
                                    </h3>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                        
                  

                <?php if (isset($storeData) && $storeData->stripe == "1") {
                    $button = 'Connected';  } else {  $button = 'InActive'; } ?>
              
                <div class= "main-div" style="margin-bottom: 80px;">
                    <div id="loading" class="hide-setion">
                        <img id="loading-image" src="{{asset('assets/images/Loader-Animation.gif')}}" alt="Loading..." />
                    </div>
                     <?php  if (isset($storeData) && $storeData->stripe == "1") {  ?>
                      
                        <ul class="connectStripeList">
                            <li>Your banking information is in our stripe account. You are ready to recieve payments for orders placed against your products.</li>
                            <li>If you want to temporarily disconnect your banking information. Click on the disconnect button below <span style="color:#195dfa">(Note: This will de-activate your store and all the products listed)</span>.</li>
                            <li>If you want to change your banking information click on the Edit Banking Information button.</li>
                        </ul>
                     <!-- Account Status : {{$button}} <br> Payment Method : Stripe<br /> -->
                   
                     
                    
                      <?php }else{ ?>
                        <p>Mallshark has partnered with Stripe to process payments for our vendors. Please provide your banking details by 
                           clicking on the button below and following the steps given by stripe. </p>
                           <?php }
                           if (isset($storeData) && $storeData->account_link == "") { ?>
                           <div style="text-align:center;">
                              <a  id="appaendurl"  class="btn btn-primary center mt-4" style="background-color:#0510fa;color: #fff!important;">Connect Bank Account</a>
                           </div>   
                          <?php }else{ ?>
                            <div style="text-align:center;">
                                <button data-account-id="{{$storeData->account_id}}" class="btn btn-primary center mt-4 editStripeAccount" id="editStripeAccount">Edit Banking Info</button>
                            </div>
                            <?php } ?>
             
    
                        
                    <div class="col-md-12">
                        <div class="row justify-content-between">
                            <div class="col-sm-12">
                                <h6 class="store-status-mbt"></h6>
                            
                            </div>
                            <?php
                            $class = "inactive-state";
                            if (isset($storeData) && $storeData->stripe == "1") {
                                $class = "active-state";
                            }
                            if(!empty($storeData->account_id)){  ?>
                            <div class="col-sm-12 text-center">
                                <div class="switch-field <?php echo $class; ?> d-none">
                                    <input type="hidden" id="stripe-case-status" value="{{$storeData->stripe}}">
                                    <div class="stripe-active d-none">
                                        <input type="radio" id="switch_active" data-account_id="{{$storeData->account_id}}" name="switch_2" value="1">
                                        <label class="active-stripe" for="switch_active">Connect</label>
                                    </div>
                                    <div class="stripe-inactive d-none">
                                        <input type="radio" id="switch_inactive" data-account_id="{{$storeData->account_id}}" name="switch_2" value="0">
                                        <label class="disconnected-stripe"  for="switch_inactive">Disconnect</label>
                                    </div>
                                </div>
                            </div>
                            <?php } ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</main>
<script>
  
  $(window).load(function() {
    var storeStripe ='<?php echo $storeData->stripe; ?>';
    if(storeStripe == '1'){
        $(".stripe-account").hide();
    }else{
        $(".stripe-account").show();
    }
    
    $('#loading').hide();
  });

    var message = "";
    $('.checked-store').on('click', function() {
        checked = $('input', this).is(':checked');
        res = {}

        if (checked == true) {
            res['store_status'] = "2";
            message = "Store has been activated successfully";
        } else {
            res['store_status'] = "0";
            message = "Store has been deactivated successfully";
        }


        sendreq(res, 'store-status', 'change', 'callbacksutatus');
        $('span.check-status').text(checked ? 'Activeted' : 'Deactive');
    });
    function callbacksutatus() {
        $("#successMessage").html(message);
        $("#successMessage").show();
        setTimeout(function() {
            jQuery("#successMessage").fadeOut('slow')
        }, 3000);
    }
    $(document).on("click", "#appaendurl", function() {
        var res = {}
        // $(".stripe-account").hide();
        var storeStatus = $("#switch_active").val();
        $('#loading').show();
        res['stripe_status'] = storeStatus;

        jQuery('.uploadImages').show();
        $.ajax({
            type: 'POST',
            url: '{{url('transferAmount')}}',
            data: res,
            success: function(response) {
                
                console.log(response); // handle the response from the server
                $(".hide-setion").show();
                $("#generatelink").hide();
                $(".stripe-account").hide();
                $('#loading').hide();
                if (response.trim() === '') {
                    
                } else {
                    location.href = response;
                }
            },
            error: function(xhr, textStatus, errorThrown) {
                console.log('Error: ' + errorThrown); // handle any errors that occur
            }
        });

    });

    // Edit account
    $(document).on("click", "#editStripeAccount", function() {

        var res = {}
        var accountId = $(this).attr('data-account-id');
        $('#loading').show();
        res['account_id'] = accountId;
        $.ajax({
            type: 'POST',
            url: '{{url('editstripeuser ')}}',
            data: res,
            success: function(response) {
                $('#loading').hide();
                location.href = response;
            },
            error: function(xhr, textStatus, errorThrown) {
                console.log('Error: ' + errorThrown); // handle any errors that occur
            }
        });

    });

    $(document).on("click", "#switch_active", function() {
        

        var res = {}
        var storeStatus = $(this).val();
        var accountId = $(this).attr('data-account_id');
        res['stripe_status'] = storeStatus;
        res['account_id'] = accountId;

        swal({
                title: "Are you sure?",
                text: "Are you sure to connect bank account to your Store? Please review your banking information by clicking Edit Bank Information ",
                type: "warning",
                showCancelButton: true,
                confirmButtonClass: "btn-success",
                confirmButtonText: "Yes, Do it!",
                closeOnConfirm: false
            },
            function(isConfirmed) {
                if (isConfirmed) {
                    swal.close();
            jQuery('.uploadImages').show();
            $.ajax({
                type: 'POST',
              //  dataType: 'json',
                url: '{{url('getstripeloginlink')}}',
                data: res,
                success: function(response) {
                    
                    if(response == 1){
                        $(".switch-field").removeClass("inactive-state");
                        $(".switch-field").addClass("active-state");
                        $(".stripe-inactive").removeClass("d-none");
                        $(".stripe-active").addClass("d-none");

                        toastr.options = {
                        closeButton: true,
                        tapToDismiss: true,
                        positionClass: 'toast-top-right',
                        toastClass: 'toast-icon custom-toast-success',
                        progressBar: true,
                        };
                        toastr.success('Your Stripe Account Activated');
                    
                       setTimeout(() => {
                            window.location.reload();
                       }, 2000);
                    }else{
                        toastr.options = {
                                closeButton: true,
                                tapToDismiss: true,
                                positionClass: 'toast-top-right',
                                toastClass: 'toast-icon custom-toast-success',
                                progressBar: true,
                            };
                            toastr.error("Please complete your Stripe details before your Stripe account can be activated. ");
                        // swal("Error!", "Please complete your Stripe details before your Stripe account can be activated. ", "error");
                    } 
                },
            });
        }
        });
    });

    $(document).on("click", "#switch_inactive", function() {
        var res = {}
        var storeStatus = $(this).val();
        var accountId = $(this).attr('data-account_id');
        res['stripe_status'] = storeStatus;
        res['account_id'] = accountId;
        
        //var result = confirm("Are you sure to change Store Status");
        swal({
                title: "Are you sure?",
                text: "Are you sure you want to deactivate your stripe account ? You won't be able to receive payments, your store will be deactivated and your product listings will be removed.",
                type: "warning",
                showCancelButton: true,
                confirmButtonClass: "btn-success",
                confirmButtonText: "Yes, Do it!",
                closeOnConfirm: false
            },
            function(isConfirmed) {
                if (isConfirmed) {
                    swal.close();
                jQuery('.uploadImages').show();
                $.ajax({
                    type: 'POST',
                    url: '{{url('getstripeloginlink')}}',
                    data: res,
                    success: function(response) {
                        
                        
                       
                        $(".hide-setion").hide();

                        $(".switch-field").removeClass("active-state");
                        $(".switch-field").addClass("inactive-state");
                        $(".stripe-active").removeClass("d-none");
                        $(".stripe-inactive").addClass("d-none");

                        toastr.options = {
                        closeButton: true,
                        tapToDismiss: true,
                        positionClass: 'toast-top-right',
                        toastClass: 'toast-icon custom-toast-success',
                        progressBar: true,
                        };
                        toastr.success('Stripe has been inactive successfully');
                        setTimeout(() => { 
                        window.location.reload();
                       }, 2000);
                    },
                    error: function(xhr, textStatus, errorThrown) {
                        console.log('Error: ' + errorThrown); // handle any errors that occur
                    }
                });
                

            }
        });
    });

    $(document).ready(function() {
        $('#connectStripe').submit(function(event) {
            event.preventDefault(); // prevent the form from submitting via the browser
            var formData = $(this).serialize(); // serialize the form data
            $.ajax({
                type: 'POST',
                url: '{{url('getstripeloginlink')}}',
                data: formData,
                success: function(response) {
                    
                    console.log(response); // handle the response from the server
                    $(".hide-setion").show();
                    $("#appaendurl").attr('href', response);

                },
                error: function(xhr, textStatus, errorThrown) {
                    console.log('Error: ' + errorThrown); // handle any errors that occur
                }
            });
        });

       var stripe_status =  $('#stripe-case-status').val();
        if(stripe_status == 0){

            $(".stripe-active").removeClass("d-none");
            $(".inactive-state").removeClass("d-none");
            $(".stripe-active").show();
        }else{
            $(".stripe-inactive").removeClass("d-none");
            $(".active-state").removeClass("d-none");
            $(".stripe-inactive").show();
        }
        var accountId = $("#switch_active").attr('data-account_id');
        $.ajax({
                type: 'POST',
                url: '{{url('onloadstripeconnect')}}',
                data: {accountId:accountId} ,
                success: function(response) {
                    if(response){

                        toastr.options = {
                        closeButton: true,
                        tapToDismiss: true,
                        positionClass: 'toast-top-right',
                        toastClass: 'toast-icon custom-toast-success',
                        progressBar: true,
                        };
                        toastr.success('Stripe has been activated successfully');

                        $(".switch-field").removeClass("inactive-state");
                        $(".switch-field").addClass("active-state");
                        $(".stripe-inactive").removeClass("d-none");
                        $(".stripe-active").addClass("d-none");

                        setTimeout(() => { 
                            window.location.reload();
                       }, 2000);

                    }
                    
                },
                error: function(xhr, textStatus, errorThrown) {
                    console.log('Error: ' + errorThrown); // handle any errors that occur
                }
            });

    });
</script>
@endsection