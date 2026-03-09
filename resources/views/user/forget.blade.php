
@extends('layouts.frontend-main')
<!-- Main Banner Starts -->
@section('content')
@section('title', 'Forgot Password')
@section('description', 'Mallshark Forgot password')
<!-- Content Starts -->
<script src="{{ URL::asset('assets/js/jquery.validate.js'),env('HTTPS_STATUS') }}"></script>
<script src="{{ URL::asset('assets/js/form-validation.js',env('HTTPS_STATUS')) }}"></script>
<main>
    <div class="signbx">
        <div class="container">
            <div class="row">      
                <div class="col-md-12">                      
                   
                    <!-- Sign Up Box -->
                    <div class="signbxin  radius recovery-password" style="max-width: 552px;">  
                        <div class="container">        
                            <h1 class="text-center h4" style="font-size: 35px;font-weight:bold;">Recover Password</h1>                  
                        <p class="text-center" style="margin-bottom: 0.5rem;">Enter your Email and instructions will be sent to you!</p>
                        @if(Session::has('SuccessMessage'))
                                        <script>
                                            toastr.options = {
                                                closeButton: true,
                                                tapToDismiss: true,
                                                positionClass: 'toast-top-right',
                                                toastClass: 'toast-icon custom-toast-success',
                                                progressBar: true,
                                            };
                                            toastr.success('<?php echo  Session::get('SuccessMessage'); ?> ');
                                        </script>
                                    @endif
                        @if(Session::has('errorMessage'))
                            <script>
                                toastr.options = {
                                    closeButton: true,
                                    tapToDismiss: true,
                                    positionClass: 'toast-top-right',
                                    toastClass: 'toast-icon custom-toast-success',
                                    progressBar: true,
                                };
                                toastr.error('<?php echo  Session::get('errorMessage'); ?> ');
                            </script>
                        @endif
                        <form action="{{URL::to('/forget-password')}}" method="POST" id="forget_form">
                            {{ csrf_field() }}
                            <div class="form-group">
                                <label for="email" style="font-weight:600;color:#183b56;font-size:16px;">Email</label>
                                <input type="text" class="form-control" id="forgetemail" name="forgetemail" placeholder="Please Enter Your Email">
                                @if ($errors->has('email'))
                                <div class="error">{{ $errors->first('forgetemail') }}</div>
                                @endif
                            </div>
                            <p class="text-center" style="margin-bottom: 4px; width:154px;margin:auto;"><button type="submit" class="btn btn-primary">Reset</button></p>
                        </form>
                    </div>
                </div>
                </div>
            </div>
        </div>

        <!-- <div class="background-color-wrapper"></div> -->
    </div>
</main>
@endsection