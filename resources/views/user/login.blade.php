@extends('layouts.frontend-main')
@section('content')
@section('title', 'Login to MallShark')
@section('description', 'Securely login to your MallShark account and access exclusive features. Enjoy a seamless shopping experience with easy account management.')
@section('keyword', "MallShark login, MallShark.net,Account access,Login page, User authentication, Secure login, MallShark account, Customer login, Member login, Shopping login, Account credentials, MallShark website")
<main>
    <style>
    .mallshark_passscode{
        width: 50% !important;
    }
    #resend-email-btn{
    height: 31px;
    width: 23%;
    padding: 5px 20px
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
    <script src="{{ URL::asset('assets/js/form-validation.js',env('HTTPS_STATUS')) }}"></script>
    <!-- Sign Up Section Starts -->
    <h1 style="display: none;">Sign in</h1>
    <div class="signbx ">
        <div class="container">
            <div class="row">
                {{-- <div class="col-md-9 ">
                    <div class="signinbnrrght">
                    <img src="{{ URL::asset('assets/images/Login-Banner.jpg') }}" alt="">
                        
                    </div>

                </div> --}}
                <div class="col-md-4 mx-auto">

                   
                    <!-- Sign Up Box -->
                    <div class="signbxin radius left-sign-in-form">
                         <h2 style="font-weight: 600" >Hello!</h2>
                         <h6>Enter your email and password</h6>   
                            <br/>
                        @if(Session::has('SuccessMessage'))
                        <?php $successMessage = Session::get('SuccessMessage') ?>
                            <script>
                            toastr.options = {
                                closeButton: true,
                                tapToDismiss: true,
                                positionClass: 'toast-top-right',
                                toastClass: 'toast-icon custom-toast-success',
                                progressBar: true,
                            };
                            toastr.success('<?php echo  $successMessage ?> ');
                            </script>
                        @endif
                        @php 
                            $emailClass = '';
                        @endphp
                        @if(Session::has('validationMessage'))
                        @php 
                        $emailClass = 'disabled';
                        @endphp
                        <?php $validationMessage = Session::get('validationMessage') ?>
                            <script>
                            toastr.options = {
                                closeButton: true,
                                tapToDismiss: true,
                                positionClass: 'toast-top-right',
                                toastClass: 'toast-icon custom-toast-success',
                                progressBar: true,
                            };
                            toastr.error('<?php echo $validationMessage ?> ');
                            </script>

                        
                        <div class="mb-3">

                            <p>Note: If you have not received the email, please request a resend, and we will send the email again</p>
                            <button id="resend-email-btn" class="btn btn-primary btn-sm">Resend</button>
                        </div>
                        @endif

                        @if(Session::has('errorMessage'))
                        <?php $errorMessage = Session::get('errorMessage') ?>
                            <script>
                            toastr.options = {
                                closeButton: true,
                                tapToDismiss: true,
                                positionClass: 'toast-top-right',
                                toastClass: 'toast-icon custom-toast-error',
                                progressBar: true,
                            };
                            toastr.error('<?php echo  $errorMessage ?> ');
                            </script>
                     
                        
                        @endif
                        <form id="login_form" method="POST" action="{{ URL::to('/login') }}">
                            {{ csrf_field() }}
                            <div class="form-group forgot-user-name">
                                <label for="email">Email:</label>
                                <input type="email" class="form-control" id="email" name="email" placeholder="Email address" value="{{ old('email') }}" {{$emailClass}}>
                                @if ($errors->has('email'))
                                <div class="error">{{ $errors->first('email') }}</div>
                                @endif
                            </div>
                            <?php 
                                if(isset($_GET['store'])){  ?>
                                <input type="hidden" name="store_creation" id="store_creation" value="{{ $_GET['store']}}" >
                               <?php } ?>

                               <?php  if(isset($_GET['created'])){ ?>
                                <input type="hidden"  name="created_store" id="created_store" value="{{$_GET['created']}}">
                                <?php } ?>

                               <?php  if(isset($_GET['campaign'])){ ?>
                                <input type="hidden"  name="campaign" id="campaign" value="{{$_GET['campaign']}}">
                                <?php } ?>

                               <?php  if(isset($_GET['referred'])){ ?>
                                <input type="hidden"  name="referred" id="referred" value="{{$_GET['referred']}}">
                                <?php } ?>
                            <div class="form-group eye-icon-user">
                                <label for="pwd">Password:</label>
                                <input type="password" class="form-control" id="passwor" name="password" placeholder="Password">
                                <a class="frgtps forgot-pass"href="{{URL::to('/forget-password')}}">Forgot Password?</a>
                                <i class="fa fa-eye " id="togglePassword" style="cursor: pointer;"></i>
                                @if ($errors->has('password'))
                                <div class="error">{{ $errors->first('password') }}</div>
                                @endif
                            </div>
                            <?php
                            if(env('User_restrict') == 1){ ?>
                                <div class="form-group">
                                <label for="pwd">Passcode:</label>
                                    <input type="text" class="form-control mallshark_passscode" maxlength="4" name="passcode" id="passcode" placeholder="Please Enter Passcode" value="{{ old('passcode') }}">
                                </div>
                             <?php  } ?>
                            <div class="form-check">
                                <label class="form-check-label container-check">
                                    <input class="form-check-input" type="checkbox" name="chkrememberme"> Stay Signed In
                                    <span class="checkmark"></span>
                                </label>  
                            </div>
                            <p style="margin-bottom:10px"><button type="submit" class="btn btn-primary">Sign In</button></p>
                            <p class="text-right fnt-lrge "><a href="{{URL::to('/signup')}}" class="text-right"><span style="color:#eb2274;font-size:16px; margin-top:-5px; font-weight:600">Create an Account</span></a></p>
                                <div class="soclbtns d-flex">
                                    <div class="pr-3"><h5 class="mt-3">Sign in with:</h5> </div>
                                    <div> 
                                    <a href="{{URL::to('/google-login')}}" class="  gmail ">                   
                                    <img style="width: 32px; " src="{{ URL::asset('assets/images/google-icon.PNG') }}" alt="">
                                    </a> 
                                    <a class=" " href="{{URL::to('/facebook-login')}}">
                                    {{-- <i class="fa fa-facebook" aria-hidden="true"></i>   &nbsp; &nbsp; --}}
                                    <img  style="width: 32px; position: relative; top:2px" src="{{ URL::asset('assets/images/facebook-icon.PNG') }}" alt="">
                                     </a>
                            </div>

                                  
                                
                               

                                </div>
                        </form>
                    </div>
                    <!-- Sign UP Box Ends -->
                </div>
            </div>
        </div>
    </div>
</main>
<script>
  const togglePassword = document.querySelector('#togglePassword');
  const password = document.querySelector('#passwor');

  togglePassword.addEventListener('click', function (e) {
    // toggle the type attribute
    const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
    password.setAttribute('type', type);
    // toggle the eye slash icon
    this.classList.toggle('fa-eye-slash');
});

var email = $("#email").val();
console.log("email", email);
$(document).ready(function() {
  $('#resend-email-btn').click(function() {
    // Data to be sent in the POST request
    var email = $("#email").val();
    var requestData = {
        email: email,
      // Add more key-value pairs as needed
    };

    $.ajax({
      url: '/resend-email', // Replace with the actual server URL
      type: 'POST',
      data: requestData,
      dataType: 'json',
      success: function(response) {
        
        if(response.status){
            toastr.options = {
                    closeButton: true,
                    tapToDismiss: true,
                    positionClass: 'toast-top-right',
                    toastClass: 'toast-icon custom-toast-success',
                    progressBar: true,
                };
                toastr.success(response.message);
            
        }
        // Handle the successful response from the server
     
      },
      error: function(xhr, status, error) {
        // Handle the error
        
        console.error(error);
      }
    });
  });
});


    let totalChildren = $('.signbxin.border.radius.left-sign-in-form').children().length;
    if(totalChildren === 3){
        $('.signinbnrrght').css('height', '520px');
    }else{
        $('.signinbnrrght').css('height', '413px');
    }
    console.log(totalChildren, 'totalChildren');

</script> 
   
<!-- Content Ends-->
@endsection