@extends('layouts.new-main')
@section('content')
@section('title', 'Store Bank Infomation')
<main>
    @include('Store.sidebar')
    <!-- Dashboard Ends -->
    <script src="{{ URL::asset('assets/js/jquery.validate.js'),env('HTTPS_STATUS') }}"></script>
    <script src="{{ URL::asset('assets/js/form-validation.js',env('HTTPS_STATUS')) }}"></script>
    <script src="{{ URL::asset('js/app.js',env('HTTPS_STATUS')) }}"></script>

    <style>
        input.invalid,
        textarea.invalid {
            border: 2px solid red;
        }

        input.valid,
        textarea.valid {
            border: 2px solid green;
        }
    </style>
    <div class="dshbrdin">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <div class="dshodrs">
                        <div class="row srtbx">
                            <div class="col-md-6">
                                <h3 class=" ">Get Bank Payment</h3>
                            </div>
                        </div>
                        <!-- Wallet Address Form -->
                        <div class="row">
                            <form class=" col-md-12 usrbsc" id="getbankinformation" method="POST" action="{{URL::to('/store/bankdetail')}}">
                                @if(Session::has('updatesuccessmessage'))
                                <div class="alert alert-success alert-success-mbt" id="success_message" role="alert">
                                    {{ Session::get('updatesuccessmessage') }}
                                    <script>
                                        setTimeout(function() {
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
                                    setTimeout(function() {
                                        jQuery("#error_message").fadeOut('slow')
                                    }, 3000);
                                </script>
                                @endif

                                {{ csrf_field() }}
                                <div class="row">
                                    <div class="col-md-6 ">
                                        <div class="form-group">
                                           
                                            <label for=" ">Account Holder Name</label>
                                            <input type="text" class="form-control" id="account_holder_name" name="account_holder_name" value="{{$bankinfo['account_holder_name']}}">
                                            <input type="hidden" name="currency" id="currency" value="usd">
                                            <input type="hidden" name="country" id="country" value="US">
                                        </div>
                                        <div class="form-group">
                                            
                                        <label for=" ">Account Holder Type</label>
                                            <select class="form-select" name="account_holder_type" id="account_holder_type">
                                                <option {{ $bankinfo['account_holder_type'] == 'company' ? 'selected' : '' }} value="company">Company</option>
                                                <option {{ $bankinfo['account_holder_type'] == 'individual' ? 'selected' : '' }} value="individual">Individual</option>
                                            </select>
                                         
                                           
                                        </div>
                                        <div class="form-group">
                                            <label for=" ">Routing Number</label>
                                            <input type="text" class="form-control" id="routing_number" name="routing_number" placeholder="Exe:110000000" value="{{$bankinfo['routing_number']}}" maxlength="9">
                                        </div>
                                        <div class="form-group">
                                            <label for=" ">Account Number</label>
                                            <input type="text" class="form-control" id="account_number" name="account_number" placeholder="Exe:000123456789" value="{{$bankinfo['account_number']}}" maxlength="12">
                                        </div>
                                        <div class="form-check">
                                            <p><button type="submit" class="btn btn-primary btn btn-primary-background text-white width100 mt-10">Save</button>
                                            </p>
                                        </div>
                                    </div>

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
<link rel="stylesheet" href="{{ URL::asset('assets/css/sweetalert.css') }}">
<script>
    // store create form validation start of wallet address

    $(document).ready(function() {
        $("#getbankinformation").validate({
            rules: {
                account_holder_name: {
                    required: true,
                   
                },
                routing_number: {
                    required: true,
                    number: true,
                    minlength: 9,
                    
                },
                account_number: {
                    required: true,
                    number: true,
                    minlength: 12,
                },
            },
            messages: {
                account_holder_name: {
                    required: "Account Holder Name is required.",
                    
                },
                routing_number: {
                    required: "Routing Number is required.",
                    number: "Please enter a valid number",
                    minlength: "Account number should be at least 9 digits long",
                  
                  
                },
                account_number: {
                    required: "Account NUmber is required.",
                    number: "Please enter a valid number",
                    minlength: "Account number should be at least 12 digits long",
                  
                },
            },
            submitHandler: function(form) {
                form.submit();
            },
            errorPlacement: function(error, element) {
                error.insertAfter(element.closest('.form-group'));
            }
        });

        // $.validator.addMethod("custom", function(value, element) {
        //     return isAddress(value);
        // });
    });

    // store create form validation end of wallet address
</script>
@endsection