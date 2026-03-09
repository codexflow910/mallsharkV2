
@extends('layouts.frontend-main')
<!-- Main Banner Starts -->
@section('content')
@section('title', 'Reset Password')
@section('description', 'Mallshark Reset password')
<script src="{{ URL::asset('assets/js/jquery.validate.js'),env('HTTPS_STATUS') }}"></script>

<style> 
.recover-email-design.signbxin.border {
    box-shadow: 0px 0px 0px #00000029 !important;
    border: 0px solid #0510FA !important;
    border-radius:6px !important;
    background-color: #fff;
}
.recover-email-design h3 {
    font-size: 25px;
    color: #2f2f2f;
    text-align: center;
    font-weight: 500;
}
.recover-email-design .btn-primary {
    background-color: #073ba5;
    border-color: #073ba5;
    border-radius: 6px;
    width: 100% !important;
}
h2.reset-heading {
    font-weight: 600;
    font-size: 32px !important;
}
</style>

<main>
    <div class="signbx">
        <div class="container">
            <div class="row">       
                <div class="col-md-12">
                    <h2 class="text-center reset-heading">Reset your password</h2>
                    <!-- Sign Up Box -->
                    <div class="signbxin border radius recover-email-design">                            
                        <br>
                        <form action="{{URL::to('/change-password')}}" method="post" id="change_password_form">
                            {{ csrf_field() }}
                            <div class="form-group" style="position: relative;">
                                <input aria-label="New password" id="password" name="password" placeholder="New password" type="password" class="form-control">
                                <i class="fa fa-eye" id="togglePassword1" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); cursor: pointer;"></i>
                            </div>
                            <div class="form-group" style="position: relative;">
                                <input class="form-control" aria-label="Retype new password" id="password_confirmation" name="password_confirmation" placeholder="Retype new password" type="password">
                                <i class="fa fa-eye" id="togglePassword2" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); cursor: pointer;"></i>
                            </div>
                            <input type="hidden" name="resetkey" value="{{$resetKey}}">
                            <p class="text-center" style="margin-bottom: 0px;"><button type="submit" class="btn btn-primary" style="width: fit-content;">Reset My Password</button></p>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>        
</main>
<script>
    // Toggle visibility for new password
    document.getElementById('togglePassword1').addEventListener('click', function () {
        const passwordField = document.getElementById('password');
        const type = passwordField.getAttribute('type') === 'password' ? 'text' : 'password';
        passwordField.setAttribute('type', type);
        this.classList.toggle('fa-eye-slash');
    });

    // Toggle visibility for confirm password
    document.getElementById('togglePassword2').addEventListener('click', function () {
        const passwordField = document.getElementById('password_confirmation');
        const type = passwordField.getAttribute('type') === 'password' ? 'text' : 'password';
        passwordField.setAttribute('type', type);
        this.classList.toggle('fa-eye-slash');
    });
</script>
<script>
$(document).ready(function () {
    $("#change_password_form").validate({
        errorElement: "span",
        rules: {
            password: {required: true},
            password_confirmation: {required: true, equalTo: "#password"},
        },
        messages: {
            password: {required: "Password is required."},
            password_confirmation: {required: "Confirm password  is required."},
        },
        errorPlacement: function (error, element) {
            error.appendTo(element.closest('.form-group'));
        },
        submitHandler: function (form) {
            form.submit();
        }
    });
});
</script>
@endsection