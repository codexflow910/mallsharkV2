@extends('layouts.mobile-main')
@section('content')
@section('title', 'Store Payment')
<main>
 @include('Store.sidebar')
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
        border-radius: 6px;
        padding: 20px;
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
        list-style-type: circle;
    }
    .center{
        text-align: CENTER;
    }
    </style>
    <?php
    // print_rr($storeData);
    ?>
   
    <div class="dshbrdin store-dashboard">
        <div class="container">
            <div class="row">
               
                    
                        
                           
            <div class="container"><!-- hide-when-preview --> 
                <div class="row">
                    <div class="col-md-12">
                        <div class="d-flex justify-content-between">
                            <div class="hd-bt"> </div>  
                            <div class="add-pr-lnk">
                                <a class="btn btn-primary btn-simple small-btn-mbt mb-3" href="{{URL::to('/store-dashboard')}}">
                                    Back to Dashboard
                                </a>                                        
                            </div>                        

                        </div>
                    </div>   
                </div>
            </div>
                        
                  

                <?php if (isset($storeData) && $storeData->stripe == "1") {
                    $button = 'Connected';
                } else {
                    $button = 'InActive';
                }

                ?>
              
                   
                <div class= "main-div">
                                <div id="loading" class="hide-setion">
                                    <img id="loading-image" src="{{asset('assets/images/Loader-Animation.gif')}}" alt="Loading..." />
                                </div>
                     <?php  if (isset($storeData) && $storeData->stripe == "1") {  ?>
                        
                        <ul>
                            <li>Your banking information is in our stripe account. You are ready to recieve payments for orders placed against your products.</li>
                            <li>If you want to temporarily disconnect your banking information. Click on the disconnect button below (Note: This will de-activate your store and all the products listed).</li>
                            <li>If you want to change your banking information click on the Edit Banking Information button.</li>
                        </ul>
                     <!-- Account Status : {{$button}} <br> Payment Method : Stripe<br /> -->
                   
                     
                    
                      <?php }else{ ?>
                        <p>Mallshark has partnered with Stripe to process payments for our vendors. Please provide your banking details by 
                           clicking on the button below and following the steps given by stripe. </p>
                           <?php }
                           if (isset($storeData) && $storeData->account_link == "") {
                           ?>
                           <div style="text-align:center;">
                           <a  id="appaendurl"  class="btn btn-primary center mt-4">Connect Now</a>
                           </div>
                          <?php }else{ ?>
                            <div style="text-align:center;">
                            <button data-account-id="{{$storeData->account_id}}" class="btn btn-primary center mt-4 editStripeAccount" id="editStripeAccount">Edit Banking Information</button>
                        </div>
                            <?php }?>
                 </div>
    
                        
                <div class="col-md-12">
                    <div class="row justify-content-between">
                        <div class="col-sm-6">
                            <h6 class="store-status-mbt"></h6>
                           
                        </div>
                        <?php
                        $class = "inactive-state";
                        if (isset($storeData) && $storeData->stripe == "1") {
                            $class = "active-state";
                        }
                        ?>
                        <div class="col-sm-4 text-right">

                            <div class="switch-field <?php echo $class; ?>">
                                <input type="radio" id="switch_active" data-account_id="{{$storeData->account_id}}" name="switch_2" value="1" <?php
                                    if (isset($storeData) && $storeData->stripe == "1") {
                                        echo "checked";
                                    } else {
                                        echo '';
                                    }
                                    ?> />
                                <label for="switch_active">Connect Bank Account </label>
                                <input type="radio" id="switch_inactive" data-account_id="{{$storeData->account_id}}" name="switch_2" value="0" <?php
                                    if (isset($storeData) && $storeData->stripe == "0") {
                                        echo "checked";
                                    } else {
                                        echo '';
                                    }
                                    ?> />
                                <label for="switch_inactive">Disconnect Bank Account</label>
                            </div>

                        </div>
                    </div>
                </div>
                
               
         
              



            </div>
        </div>

</main>
<script>
  
  $(window).load(function() {
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
            //            var rep = confirm("Are you want to sure to deactivate your store.");
            //            if (rep == false)
            //                return false;
            res['store_status'] = "0";
            message = "Store has been deactivated successfully";
        }


        sendreq(res, 'store-status', 'change', 'callbacksutatus');
        $('span.check-status').text(checked ? 'Activeted' : 'Deactive');
    });
    //    $(".noteactive").on('click',function(){
    //         $("#successMessage").html(message);
    //        $("#successMessage").show();
    //    });
    function callbacksutatus() {
        $("#successMessage").html(message);
        $("#successMessage").show();
        setTimeout(function() {
            jQuery("#successMessage").fadeOut('slow')
        }, 3000);
    }
    $(document).on("click", "#appaendurl", function() {
        var res = {}
        var storeStatus = $("#switch_active").val();
        $('#loading').show();
        res['stripe_status'] = storeStatus;

        jQuery('.uploadImages').show();
        $.ajax({
            type: 'POST',
            url: '{{url('transferAmount ')}}',
            data: res,
            success: function(response) {
                console.log(response); // handle the response from the server
                $(".hide-setion").show();
                $("#generatelink").hide();
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
        var storeStatus = $("#switch_active").val();
        var accountId = $("#switch_active").attr('data-account_id');
        res['stripe_status'] = storeStatus;
        res['account_id'] = accountId;
        var result = confirm("Are you sure to change Stripe Status");
        if (result == true) {
            jQuery('.uploadImages').show();
            $.ajax({
                type: 'POST',
              //  dataType: 'json',
                url: '{{url('getstripeloginlink ')}}',
                data: res,
                success: function(response) {
                    console.log("response",response);
                    // debugger

                    if(response == 1){
                        swal("Success!", "Your Stripe Account Activated", "success");
                        setTimeout(function() {
                        window.location.reload();
                    }, 2000);
                    }else{
                      
                        swal("Error!", "Please complete your Stripe details before your Stripe account can be activated. ", "error");
                    }

                  

                },
                // error: function(xhr, textStatus, errorThrown) {
                //     console.log('Error: ' + errorThrown); // handle any errors that occur
                //     // swal("", xhr.responseJSON, textStatus);

                // }
            });
        } else {
            return false;
        }
    });

    $(document).on("click", "#switch_inactive", function() {
        var res = {}
        var storeStatus = $("#switch_active").val();
        var accountId = $("#switch_active").attr('data-account_id');
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
            function() {
                jQuery('.uploadImages').show();
                $.ajax({
                    type: 'POST',
                    url: '{{url('getstripeloginlink ')}}',
                    data: res,
                    success: function(response) {
                        $(".hide-setion").hide();

                        $(".switch-field").removeClass("active-state");
                        $(".switch-field").addClass("inactive-state");
                        setTimeout(function() {
                            window.location.reload();
                        }, 2000);

                    },
                    error: function(xhr, textStatus, errorThrown) {
                        console.log('Error: ' + errorThrown); // handle any errors that occur
                    }
                });
                message = "Stripe has been inactive successfully";
                swal("Success!", message, "success");

            });

    });

    $(document).ready(function() {
        $('#connectStripe').submit(function(event) {
            event.preventDefault(); // prevent the form from submitting via the browser
            var formData = $(this).serialize(); // serialize the form data
            $.ajax({
                type: 'POST',
                url: '{{url('transferAmount ')}}',
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
    });
</script>
@endsection