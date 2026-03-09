@extends('layouts.dashboard-main')
@section('content')
@section('title', 'Store Wallet Address ')
<main>
    <!-- Dashboard Ends -->
    <script src="{{ URL::asset('assets/js/jquery.validate.js'),env('HTTPS_STATUS') }}"></script>
    <script src="{{ URL::asset('assets/js/form-validation.js',env('HTTPS_STATUS')) }}"></script>
    <script src="{{ URL::asset('js/app.js',env('HTTPS_STATUS')) }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/web3@latest/dist/web3.min.js"></script>
    <script src="{{ URL::asset('assets/js/storevalidatonform.js',env('HTTPS_STATUS')) }}"></script>
   <style>

    input.invalid, textarea.invalid{
	border: 2px solid red;
    }

    input.valid, textarea.valid{
        border: 2px solid green;
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
            margin-top: 0px;
        }
    }
   </style>
<div class="change-password-page-cmt">
    @include('buyer.customsidebar')
  
  </div>

    <div class="dshbrdin cm-edit-ja">
        <div class="container" style="margin-top: 0px;"> 
            
            <div class="row" >
                <div class="col-md-12">
                    @include('Store.sidebar')

                </div>

            <div class="container"><!-- hide-when-preview --> 
                <div class="row">

                    <div class="col-md-12">
                        <div class="d-flex justify-content-between">
                            <div class="hd-bt"> </div>  
                            {{-- <div class="add-pr-lnk">
                                <a class="btn btn-primary btn-simple small-btn-mbt mb-3" href="{{URL::to('/store-dashboard')}}">
                                    Back to Dashboard
                                </a>                                        
                            </div>                         --}}

                        </div>
                    </div>   
                </div>
            </div>
                <div class="col-md-12"> 
                    <div class="dshodrs " style="margin-top: 44px;">
                        <div class="row srtbx">
                            <div class="col-md-6" style="padding-left: 0px;">
                                <div class="hd-bt" >
                                    <h3>
                                    <div class="d-flex align-items-center">                        
                                                <a href="javascript:void(0)" class="d-flex align-items-center gap-10px sidebar-admin-burger-menu" id="open-sidebar-admin-panel">                                        
                                                <span id="menu-toggle-dashboard" class="shopzit-menu mt-0 ml-0 bx-menu">
                                                    <span class="humber"></span><span class="humber"></span ><span  class="humber"></span><span  class="humber"></span>
                                                </span>
                                                <!-- <span>Menu</span> -->
                                                </a>
                                                <span style="font-size: 35px;font-weight:bold;">Receive Payments in USDC</span>
                                                </div>    
                        
                                       </h3>
                                    </div>
                            </div>
                        </div>    
                        <!-- Wallet Address Form -->
                        <div class="row">
                            <form class="col-md-8 usrbsc mt-0" style="padding-left: 0px;" name="userInfo" id="userInfo_through_payment" method="POST" action="{{URL::to('/store/paidby')}}" >
                                       
                                @if(Session::has('updatesuccessmessage'))
                                    <script>
                                        toastr.options = {
                                            closeButton: true,
                                            tapToDismiss: true,
                                            positionClass: 'toast-top-right',
                                            toastClass: 'toast-icon custom-toast-success',
                                            progressBar: true,
                                        };
                                        toastr.success('<?php echo  Session::get('updatesuccessmessage'); ?> ');
                                    </script>
                                @endif
                                @if(Session::has('updateerrormessage'))
                                    <script>
                                        toastr.options = {
                                            closeButton: true,
                                            tapToDismiss: true,
                                            positionClass: 'toast-top-right',
                                            toastClass: 'toast-icon custom-toast-error',
                                            progressBar: true,
                                        };
                                        toastr.error('<?php echo  Session::get('updateerrormessage'); ?> ');
                                    </script>
                                 @endif

                                {{ csrf_field() }}
                                <div class="form-group">
                                    <p style="color:#195dfa"><i>Note: We support USDC as the only method for payment at this moment. In order for you to receive funds from MallShark. We need you to enter your public wallet address.The list of wallets that we support are mentioned. <a style="color:#195dfa"  href="javascript:void(0)"><b>click here to view list</b></a>.</i></p>
                                    <label for=" ">Enter Your Wallet Address</label>
                                    <input type="text" class="form-control" id="wallet_address" name="wallet_address" value="{{$userData['wallet_address']}}">
                                </div>
                                {{-- <hr> --}}
                                <div class="form-check" style="padding-left: 0px;">  
                                    <p><button type="submit" class="btn btn-primary btn btn-primary-background text-white width100 mt-10">Save</button>
                                    </p>
                                </div>
                            </form>
                        </div>
                        <!-- Wallet Address Form Ends -->     
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<script type="text/javascript" src="{{ URL::asset('assets/js/sweetalert.js') }}"></script>
     <link rel = "stylesheet" href = "{{ URL::asset('assets/css/sweetalert.css') }}" > 
<script>

// store create form validation start of wallet address
 const isAddress = (address) => {
        return window.Web3.utils.isAddress(address);
    };
    $(document).ready(function() {
        $("#userInfo_through_payment").validate({
            rules: {
                wallet_address: {
                required: true,
                custom: true
            }
            },
            messages: {
                wallet_address: {
                required: "Wallet Address is required.",
                custom: "Please enter a valid wallet address."
            }
            },
            submitHandler: function(form) {
            form.submit();
            },
            errorPlacement: function(error, element) {
            error.insertAfter(element.closest('.form-group'));
            }
        });

        $.validator.addMethod("custom", function(value, element) {
            return isAddress(value);
        });
    });
        
    // store create form validation end of wallet address
    
</script>
@endsection