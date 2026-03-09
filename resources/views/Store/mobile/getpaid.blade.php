@extends('layouts.mobile-main')
@section('content')
@section('title', 'Store Payment')
<main>
 @include('Store.sidebar')
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
   </style>

    <div class="dshbrdin">
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
                <div class="col-md-12"> 
                    <div class="dshodrs">
                        <div class="row srtbx">
                            <div class="col-md-6">
                            <h3 class=" ">We support USDC as the only method for payment at this moment</h3><br />
                            <h3 class=" ">Receive Payments in USDC</h3>
                            </div>
                        </div>    
                        <!-- Wallet Address Form -->
                        <div class="row">
                            <form class="col-md-8 usrbsc mt-0" name="userInfo" id="userInfo_through_payment" method="POST" action="{{URL::to('/store/paidby')}}" >
                                        @if(Session::has('updatesuccessmessage'))
                                        <div class="alert alert-success alert-success-mbt" id="success_message" role="alert">
                                            {{ Session::get('updatesuccessmessage') }}
                                            <script>
                                            setTimeout(function () {
                                                jQuery("#success_message").fadeOut('slow')
                                            }, 3000);
                                        </script>
                                        </div>
                                        @endif
                                        @if(Session::has('updateerrormessage'))
                                        <div class="alert alert-danger" role="alert" id="error_message">
                                            {{ Session::get('updateerrormessage') }}
                                        </div>
                                        <script>
                                            setTimeout(function () {
                                                jQuery("#error_message").fadeOut('slow')
                                            }, 3000);
                                        </script>
                                        @endif

                                {{ csrf_field() }}
                                <div class="form-group">
                                    <p style="color:#195dfa"><i>Note: In order for you to receive funds from Mallshark. We need you to enter your public wallet address.The list of wallets that we support are mentioned. <a style="color:#195dfa" target="_blank" href="https://wiki.polygon.technology/docs/develop/wallets/getting-started/#partner-wallets"><b>click here to view list</b></a>.</i></p>
                                    <label for=" ">Enter Your Wallet Address</label>
                                    <input type="text" class="form-control" id="wallet_address" name="wallet_address" value="{{$userData['wallet_address']}}">
                                </div>
                                <hr>
                                <div class="form-check">  
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