@extends('layouts.register-main-mobile')
@section('content')
@section('title', 'Reset password')
<script src="{{ URL::asset('assets/js/jquery.validate.js'),env('HTTPS_STATUS') }}"></script>
<main>
    <div class="signbx">
        <div class="container">
            <div class="row">       
                <div class="col-md-12">
                    <h2 class="text-center">Reset your password</h2>
                    <!-- Sign Up Box -->
                    <div class="signbxin border radius">                            
                        <h3>You have requested to reset the password</h3>
                        <form action="{{URL::to('/change-password')}}" method="post" id="change_password_form">
                            {{ csrf_field() }}
                            <div class="form-group">
                                <input aria-label="New password"id="password" name="password" placeholder="New password" type="password" class="form-control">
                            </div>
                            <div class="form-group">
                                <input  class="form-control" aria-label="Retype new password" id="password_confirmation" name="password_confirmation" placeholder="Retype new password" type="password">
                            </div>
                            <input type="hidden" name="resetkey" value="{{$resetKey}}">
                            <p><button type="submit" class="btn btn-primary">Reset my password</button></p>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>        
</main>
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