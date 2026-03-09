@extends('layouts.register-main-mobile')
@section('content')
@section('title', 'Sign In')
@section('description', 'Sign in with your existing Mallshark account details.')
<main>

<style>
    
@media only screen and (max-width: 767px){
    body {
        padding-top: 0px;
    }    
}

    
</style>
    
    <script src="{{ URL::asset('assets/js/jquery.validate.js',env('HTTPS_STATUS')) }}"></script>
    <script src="{{ URL::asset('assets/js/form-validation.js',env('HTTPS_STATUS')) }}"></script>
    <!-- Sign Up Section Starts -->
    <h1 style="display: none;">Sign In</h1>
    <div class="signbx ">
        <div class="container">
            <div class="row">
                <div class="col-md-12">

                    <p class="text-center">Don't have an account? <a href="{{URL::to('/signup')}}"> <b> Sign Up </b></a></p>
                    <!-- Sign Up Box -->
                    <div class="signbxin radius">
                        <h2>Sign In</h2>

                        @if(Session::has('SuccessMessage'))
                        <div class="alert alert-success" role="alert">{{ Session::get('SuccessMessage') }}</div>
                        @endif
                        @if(Session::has('errorMessage'))
                        <div class="alert alert-danger" role="alert">{{ Session::get('errorMessage') }}</div>
                        @endif
                        <form id="login_form_page_mobile" method="POST" action="{{ URL::to('/login') }}">
                            {{ csrf_field() }}
                            <div class="form-group">
                                <label for="email">Email</label>
                                <input type="email" class="form-control" id="email" placeholder="Enter Your Email" name="email" value="" >
                                @if ($errors->has('email'))
                                <div class="error">{{ $errors->first('email') }}</div>
                                @endif
                            </div>
                            <div class="form-group eye-icon-user">
                                <label for="pwd">Password:</label>
                                <input type="password" class="form-control" id="passwor" placeholder="Enter Your Password" name="password" value="">
                                <i class="fa fa-eye " id="togglePassword" style="cursor: pointer;"></i>
                                @if ($errors->has('password'))
                                <div class="error">{{ $errors->first('password') }}</div>
                                @endif
                            </div>
                            <div class="form-check">
                                <label class="form-check-label container-check">
                                    <input class="form-check-input" type="checkbox" name="chkrememberme"> Remember me
                                    <span class="checkmark"></span>
                                </label>   &nbsp; &nbsp;| &nbsp; &nbsp; <a href="{{URL::to('/forget-password')}}">Forgot Password</a>
                            </div>
                            <p><button type="submit" class="col-sm-3 btn btn-primary">Sign in</button></p>
                         
                                <a class="btn btn-primary btn-simple col" href="{{URL::to('/facebook-login')}}">
                                    <i class="fa fa-facebook" aria-hidden="true"></i> Connect With Facebook </a> &nbsp; &nbsp;
                                <a href="{{URL::to('/google-login')}}" class="col-sm-3 btn btn-primary btn-simple gmail "> <i class="fa fa-google" aria-hidden="true"></i> Connect With Google</a>
                            </p>
                            <p>By signing in you agree to our <a href="{{URL::to('/terms');}}" class="bold-class">terms and conditions</a></p>
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
</script>    
<!-- Content Ends-->
@endsection