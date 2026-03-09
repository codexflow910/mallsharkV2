@extends('layouts.register-main-mobile')
@section('content')
@section('title', 'Sign Up - Create an Account ')
@section('description', 'Create an account on Mallshark. Please enter your details and create a password to set up your account')
@section('content')
<!-- Content Starts mob-->

<main>

<style>
    
@media only screen and (max-width: 767px){
    body {
        padding-top: 0px;
    }    
}
.error{
    color:red;
}
    
</style>

<!--    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.17.0/jquery.validate.js"></script>-->
    <script src="{{ URL::asset('assets/js/jquery.validate.js',env('HTTPS_STATUS')) }}"></script>
    <script src="{{ URL::asset('assets/js/form-validation.js',env('HTTPS_STATUS')) }}"></script>
    <script src="{{ URL::asset('assets/js/fone-format.js',env('HTTPS_STATUS')) }}"></script>
    <h1 style="display: none;">Sign Up</h1>
    <div class="signbx">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <p class="text-center">Have and account? <a href="{{URL::to('/login')}}"> <b> Sign in </b></a></p>
                    <!-- Sign Up Box -->
                    <div class="signbxin border radius">
                        <h2>Sign Up</h2>
                        @if(Session::has('RegistersuccessMessage'))
                        <div class="alert alert-success alert-success-mbt" role="alert">
                            {{ Session::get('RegistersuccessMessage') }}
                            <script>
                            $(document).ready(function () {
                            //   setTimeout(
                            //     function ()
                            //     {
                            //         window.location.href = '/login';
                            //     }, 4000);
                            });

                            </script>
                        </div>
                        @endif
                        <form id="register_form" name="register_form" method="POST" action="{{Url::to('/signup')}}">
                            {{ csrf_field() }}
                            <div class="form-group">
                                <label for="email">Full Name</label>
                                <input type="text" class="form-control" placeholder="Enter Your Full Name" id="txtname" name="txtname">

                                @if ($errors->has('txtname'))
                                <div class="error">{{ $errors->first('txtname') }}</div>
                                @endif
                            </div>
                            <div class="form-group">
                                <label for="pwd">Email*</label>
                                <input type="text" class="form-control" id="email" placeholder="Enter Your Email" name="email">
                                @if ($errors->has('email'))
                                <div class="error">{{ $errors->first('email') }}</div>
                                @endif
                            </div>
                            <!-- <div class="form-group">
                                <label for="pwd">Physical Address*</label>
                                <textarea class="form-control" id="physical_address" placeholder="Enter Your Address" name="physical_address"></textarea>
                                @if ($errors->has('physical_address'))
                                <div class="error">{{ $errors->first('physical_address') }}</div>
                                @endif
                            </div> -->
                            <div class="form-group">
                                <label for="pwd">Contact Number</label>
                                <input type="text" class="form-control" id="contact_no" placeholder="Enter Your Contact Number" name="contact_no">
                                @if ($errors->has('contact_no'))
                                <div class="error">{{ $errors->first('contact_no') }}</div>
                                @endif
                            </div>
                            <!-- <div class="form-group">
                                <label for="pwd">Wallet Address*</label>
                                <input type="text" class="form-control" id="wallet_address" placeholder="Enter Your Wallet Address" name="wallet_address">
                                @if ($errors->has('wallet_address'))
                                <div class="error">{{ $errors->first('wallet_address') }}</div>
                                @endif
                            </div> -->
                            <div class="form-group eye-icon-user">
                                <label for="pwd">Password*</label>
                                <input type="password" class="form-control" id="password" placeholder="Enter Your Password" name="password">
                                <i class="fa fa-eye " id="togglePassword" style="cursor: pointer;"></i>
                                @if ($errors->has('password'))
                                <div class="error">{{ $errors->first('password') }}</div>
                                @endif
                            </div>
                            <div class="form-group eye-icon-user">
                                <label for="pwd">Re-type Password*</label>
                                <input type="password" class="form-control" id="password_confirmation" placeholder="Enter Your Confirm Password" name="password_confirmation">
                                <i class="fa fa-eye " id="togglePassword_confirm" style="cursor: pointer;"></i>
                                @if ($errors->has('password_confirmation'))
                                <div class="error">{{ $errors->first('password_confirmation') }}</div>
                                @endif
                            </div>
                            <div >
                                <!-- <label class="form-check-label container-check">
                                    <input class="form-check-input" type="checkbox" id="chk_newsletter" value="1" name="chk_newsletter" checked="">
                                    <span class="checkmark"></span>
                                    <p>Receive our weekly newsletter and other occasional updates</p>

                                </label>    -->
                                <p><button type="submit" class="btn btn-primary" style="padding-left:10px;padding-right:10px">Create your MallShark account</button></p>
                                <a class="btn btn-primary btn-simple facebook" style="padding-left:10px; padding-right:10px" href="{{URL::to('/facebook-login')}}">
                                    <i class="fa fa-facebook" aria-hidden="true"></i> Connect With Facebook </a>
                                <a href="{{URL::to('/google-login')}}" class="btn btn-primary btn-simple gmail "  style="padding-left:10px; padding-right:10px"> <i class="fa fa-google" aria-hidden="true"></i> Connect With Google</a>
                                 <p>By signing up, you agree to our <a href="{{URL::to('/terms');}}" class="bold-class">terms and conditions</a> of use, <a href="{{URL::to('/privacy-policy');}}"  class="bold-class"> privacy policy</a>, and cookie policy.</p>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
<!--<script>
$(function () {
    $("#contact_no").mask("(999) 999-9999");
    $("#contact_no").on("blur", function () {
        var last = $(this).val().substr($(this).val().indexOf("-") + 1);
        if (last.length == 5) {
            var move = $(this).val().substr($(this).val().indexOf("-") + 1, 1);
            var lastfour = last.substr(1, 4);
            var first = $(this).val().substr(0, 9);
            $(this).val(first + move + '-' + lastfour);
        }
    });
});
</script>-->
<script>
    const togglePassword = document.querySelector('#togglePassword');
    const togglePassword_confirm = document.querySelector('#togglePassword_confirm');
    const password = document.querySelector('#password');
    const password_confirmation = document.querySelector('#password_confirmation');

    togglePassword.addEventListener('click', function (e) {
    // toggle the type attribute
    const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
    password.setAttribute('type', type);
    // toggle the eye slash icon
    this.classList.toggle('fa-eye-slash');
    });
    togglePassword_confirm.addEventListener('click', function (e) {
        // toggle the type attribute
        const type_confirm = password_confirmation.getAttribute('type') === 'password' ? 'text' : 'password';
        password_confirmation.setAttribute('type', type_confirm);
        // toggle the eye slash icon
        this.classList.toggle('fa-eye-slash');
    });
  </script> 
@endsection
