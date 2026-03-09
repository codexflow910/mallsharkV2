@extends('layouts.register-main-mobile')
@section('content')
@section('title', 'Forget password')
<script src="{{ URL::asset('assets/js/jquery.validate.js'),env('HTTPS_STATUS') }}"></script>
<script src="{{ URL::asset('assets/js/form-validation.js',env('HTTPS_STATUS')) }}"></script>
<main>
    <div class="signbx">
        <div class="container">
            <div class="row">      
                <div class="col-md-12">                      
                    <h1 class="text-center forgot-heading">Recover Your Account</h1>
                    <!-- Sign Up Box -->
                    <div class="signbxin border radius">                            
                        <h2>Enter your Email to help us identify you.</h2>
                        @if(Session::has('SuccessMessage'))
                        <div class="alert alert-success alert-success-mbt" role="alert">
                            {{ Session::get('SuccessMessage') }}
                        </div>
                        @endif
                        @if(Session::has('errorMessage'))
                        <div class="alert alert-danger" role="alert">
                            {{ Session::get('errorMessage') }}
                        </div>
                        @endif
                        <form action="{{URL::to('/forget-password')}}" method="POST" id="forget_form">
                            {{ csrf_field() }}
                            <div class="form-group">
                                <label for="email">Email</label>
                                <input type="text" class="form-control" id="forgetemail" name="forgetemail" placeholder="Please enter your email">
                                @if ($errors->has('email'))
                                <div class="error">{{ $errors->first('forgetemail') }}</div>
                                @endif
                            </div>
                            <p><button type="submit" class="btn btn-primary">Submit</button></p>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
@endsection