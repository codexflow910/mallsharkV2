@extends('layouts.frontend-main')
@section('content')
@section('title', 'Sign Up for MallShark: Join the Shopping Revolution Today!')
@section('description', "Join MallShark's crypto ecommerce revolution by signing up today. Experience secure registration and unlock exclusive offers for a seamless shopping journey.")
@section('keyword', "MallShark sign up, MallShark.net, Create an account, Register for MallShark, Join MallShark, Crypto ecommerce, Secure signup, MallShark membership, New user registration, Crypto shopping platform")
@section('content')
<!-- Content Starts -->

<main>
<style>
    .mallshark_passscode{
        width: 30% !important;
    }

@media only screen and (max-width: 767px){
    body {
        padding-top: 0px;
    }    
}


</style>
<script>
    window.user_restriction = {
    passcodeCheck: "{{ env('User_restrict') }}"
};
    window.user_passcode = {
    passcode: "{{ env('Passcode') }}"
};
</script>

<!--    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.17.0/jquery.validate.js"></script>-->
    <!-- <script src="{{ URL::asset('assets/js/jquery.validate.js',env('HTTPS_STATUS')) }}"></script> -->
    <script src="{{ URL::asset('assets/js/form-validation.js',env('HTTPS_STATUS')) }}"></script>
    <script src="{{ URL::asset('assets/js/fone-format.js',env('HTTPS_STATUS')) }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/web3@latest/dist/web3.min.js"></script>
    <h1 style="display: none;">Sign Up</h1>
    <div class="signbx signup-main-form">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <p class="text-center fnt-lrge already-have-acc">Already have an account? <a href="{{URL::to('/login?created=store')}}"><b>Sign In</b></a></p>
                    <!-- Sign Up Box -->
                    <div class="signbxin  radius">
                        <h3>Sign Up</h3>
                        @if(Session::has('RegistersuccessMessage'))
                        <div class="alert alert-success alert-success-mbt" role="alert">
                            {{ Session::get('RegistersuccessMessage') }}
                            <script>
                            $(document).ready(function () {
                            //  setTimeout(
                            //     function ()
                            //     {
                            //         window.location.href = '/login';
                            //     }, 10000);
                            });

                            </script>
                        </div>
                        @endif
                        @if(Session::has('ErrorMessage'))
                        <div class="alert alert-danger alert-success-mbt" id="error_message"  role="alert">
                            {{ Session::get('ErrorMessage') }}
                            <script>
                                // setTimeout(function () {
                                //     jQuery("#error_message").fadeOut('slow');
                                //     //window.location.href = '/signup';
                                // }, 3000);
                            </script>
                        </div>
                        @endif
                        <form id="register_form" name="register_form" method="POST" action="{{Url::to('/signup')}}">
                            {{ csrf_field() }}
                            <div class="form-group ">
                                <label for="email" class="signUpPageLabel">Full Name*</label>
                                <input type="text" class="form-control" id="txtname" name="txtname" autocomplete="off"  placeholder="Enter Your Full Name" value="{{ old('txtname') }}">

                                @if ($errors->has('txtname'))
                                <div class="error">{{ $errors->first('txtname') }}</div>
                                @endif
                            </div>
                            <?php  
                            // dd($_GET);
                            if(isset($_GET['store'])){ ?>
                            <input type="hidden"  name="store_creation" id="store_creation" value="{{$_GET['store']}}">
                            <?php } ?>
                            <?php 
                            if(isset($_GET['campaign'])){ ?>
                            <input type="hidden"  name="store_campaign" id="store_campaign" value="{{$_GET['campaign']}}">
                            <?php }  ?>

                            <?php 
                            if(isset($_GET['referred'])){ ?>
                            <input type="hidden"  name="referred" id="referred" value="{{$_GET['referred']}}">
                            <?php } ?>
                           
                            <div class="form-group">
                                <label for="pwd" class="signUpPageLabel">Email*</label>
                                <input type="email" class="form-control" id="email" name="email" autocomplete="off" placeholder="Enter Your Email" value="{{ old('email') }}">
                                @if ($errors->has('email'))
                                <div class="error">{{ $errors->first('email') }}</div>
                                @endif
                                {{-- <p class="error" id="result"></p> --}}
                            </div>
                            <!-- <div class="form-group">
                                <label for="pwd">Physical Address</label>
                                <textarea class="form-control" id="physical_address" name="physical_address" placeholder="Enter Your Address"></textarea>
                                @if ($errors->has('physical_address'))
                                <div class="error">{{ $errors->first('physical_address') }}</div>
                                @endif
                            </div> -->
                            <div class="form-group">
                                <label for="contact_no" class="signUpPageLabel">Contact Number</label>
                                <input type="text" class="form-control" id="contact_no" name="contact_no" autocomplete="off" placeholder="Enter Your Contact Number" value="">
                                @if ($errors->has('contact_no'))
                                <div class="error">{{ $errors->first('contact_no') }}</div>
                                @endif
                            </div>
                            <!-- <div class="form-group">
                                <label for="pwd">Wallet Address</label>
                                <input type="text" class="form-control" id="wallet_address" name="wallet_address" placeholder="Enter Your Wallet Address">
                                @if ($errors->has('wallet_address'))
                                <div class="error">{{ $errors->first('wallet_address') }}</div>
                                @endif
                            </div> -->
                            <div class="form-group eye-icon-user">
                                <label for="pwd" class="signUpPageLabel">Password*</label>
                                <input type="password" class="form-control" id="password" name="password" placeholder="Enter Your Password" autocomplete="off">
                                <i class="fa fa-eye " id="togglePassword" style="cursor: pointer;"></i>
                                @if ($errors->has('password'))
                                <div class="error">{{ $errors->first('password') }}</div>
                                @endif
                            </div>
                            <div class="form-group eye-icon-user">
                                <label for="pwd" class="signUpPageLabel">Confirm Password*</label>
                                <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" placeholder="Enter Your Confirm Password" autocomplete="off" >
                                <i class="fa fa-eye " id="togglePassword_confirm" style="cursor: pointer;"></i>
                                @if ($errors->has('password_confirmation'))
                                <div class="error">{{ $errors->first('password_confirmation') }}</div>
                                @endif
                            </div>
                           <?php if(env('User_restrict') == 1){ ?>
                            <label for="pwd">Passcode*</label>
                            <div class="form-group">
                                <input type="text" class="form-control mallshark_passscode" maxlength="4" name="passcode" autocomplete="off"  id="passcode" placeholder="Please Enter Passcode" value="{{ old('passcode') }}">
                            </div>
                            <?php } ?>
                            <div class="form-check" style="margin-bottom: 0px;">
                                <!-- <label class="form-check-label container-check">
                                    <input class="form-check-input" type="checkbox" id="chk_newsletter" value="1" name="chk_newsletter">
                                    <span class="checkmark"></span>
                                    <p>Receive our weekly newsletter and other occasional updates</p>

                                </label> -->
                                <div class="col-md-12 text-center   mt-30">
                                {{ romanzipp\Turnstile\Captcha::getChallenge('light') }}
                                <!-- <div class="cf-turnstile" data-sitekey="0x4AAAAAAAjvVl0f_Ysk-S4K"></div> -->
                                </div>
                                <div  class="form-group mt-10 cat-contan registeBtnCont" >
                                
                                <p style="margin-bottom: 29px;"><button type="submit" class="btn btn-primary w100p">Create your MallShark account</button></p>
                               
                               <div class="soclbtns social-main-wrapper">
                                <h5 class="connect-with">Connect with:</h5>  

                                <div class="social-btn-wrapper">
                                <a href="/google-login" class="  gmail ">                   
                                   <img src="{{ asset('assets/images/google-icon.PNG') }}" alt="">
                               </a>  
                               <a class=" " href="/facebook-login">
                                   
                                   <img src="{{ asset('assets/images/facebook-icon.PNG') }}" alt="">
                               </a>
                            </div>
                              

                               </div>
                               {{-- <h4>Connect with:</h4>
                                <a class="btn btn-primary btn-simple facebook" style="padding-left:10px; padding-right:10px" href="{{URL::to('/facebook-login')}}">
                                    <i class="fa fa-facebook" aria-hidden="true"></i> Connect With Facebook </a>
                                <a href="{{URL::to('/google-login')}}" class="btn btn-primary btn-simple gmail "  style="padding-left:10px; padding-right:10px"> <i class="fa fa-google" aria-hidden="true"></i> Connect With Google</a> --}}
                                <p style="margin-top:10px;margin-bottom:0px;">By signing up, you agree to our <a href="{{URL::to('/terms');}}" class="bold-class" style="color: #073ba5">terms and conditions</a> of use, <span class="bold-class" style="color: #073ba5"> <a style="color:#073ba5;" href="{{URL::to('/privacy-policy');}}"> privacy policy</a>, </span> and <span class="bold-class" style="color: #0510FA"> <a href="{{URL::to('/comingsoon');}}">cookie policy</a>.</span></p>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
<script>
const validateEmail = (email) => {
  return email.match(
    // /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/
/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/  
);
};

const validate = () => {
  const $result = $('#result');
  const email = $('#email').val();
  $result.text('');

  if (validateEmail(email)) {
    $result.text(email + ' is valid :)');
    $result.css('color', 'green');
  } else {
    $result.text(email + ' is not valid :(');
    $result.css('color', 'red');
  }
  return false;
}

$('#email').on('input', validate);
const isAddress = (address) => {
        return window.Web3.utils.isAddress(address);
    };

    jQuery.validator.addMethod("lettersonly", function (e, r) {
        return this.optional(r) || /^[a-z ]+$/i.test(e)
    }, "Only letters are allowed.");
    jQuery.validator.addMethod("customPasscode", function (value, element) {
        // Check if user_restrict is true and passcode matches the environment variable
        if (user_restriction.passcodeCheck == '1' && value === user_passcode.passcode) {
            return true;
        }
        return false;
    }, "Please enter a valid Passcode.");

    $.validator.addMethod("customEmail", function (value, element) {
        // Check if the email has a valid domain with a TLD
        return this.optional(element) || /^[^\s][a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/.test(value);
    }, "Please enter a valid email address.");

    $("#register_form").validate({
        errorElement: "span",
        rules: {
            txtname: {
                required: true, minlength: 4, lettersonly: !0,
                maxlength: 25
            },
            email: {
                required: true, customEmail: true, 
                remote: "/email-exist"
            },
            password: {
                required: true, minlength: 6,
                maxlength: 25
            },
            passcode : {
                customPasscode: true
            },

            contact_no: { maxlength: 20, numbersonly: !0},
            password_confirmation: {required: true, equalTo: "#password"},
        },
        messages: {
            txtname: {required: "Name is required."},
            passcode: {
                customPasscode: "Please enter a valid Passcode."
            },
            password: {required: "Password  is required."},
            password_confirmation: {required: "Confirm password is required."},
            //physical_address: {required: "Physical address  is required."},
            // contact_no: {required: "Contact number is required."},
            // 'categories[]': {required: "Please select at least on category."},
            email: {
                required: "Email is required.",
                remote: "<div class='clear'></div><div id='register_user_form'>This Email is already registered. <a href='/forget-password'>Forgot Password?</a></div></div>"
            },

        },
        errorPlacement: function (error, element) {
            error.appendTo(element.closest('.form-group'));
        },
        submitHandler: function (form) {
            form.submit();
        }
    });
    $.validator.addMethod("custom", function(value, element) {
        return isAddress(value);
    });
</script>

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
