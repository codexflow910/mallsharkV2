@extends('layouts.mobile-main')
<!-- Main Banner Starts -->
@section('content')
@section('title', 'Buyer info')
<main>
    @include('buyer.mobile.sidebar')
    <!-- Dashboard Ends -->
    <script src="{{ URL::asset('assets/js/jquery.validate.js'),env('HTTPS_STATUS') }}"></script>
    <script src="{{ URL::asset('assets/js/form-validation.js',env('HTTPS_STATUS')) }}"></script>
    <div class="dshbrdin">
        <div class="container">
            <div class="row">
                <div class="col-md-12"> 
                    <div class="dshodrs">
                        <!-- Product Sorting Starts -->
                        <div class="row srtbx"><div class="col-md-6"><h3 class=" ">User Account</h3></div></div>    
                        <!-- Product Sorting Ends--> 
                        <!-- User Form -->
                        <div class="row">
                            <form class="col-md-4 usrbsc" name="userInfo" id="userInfo" method="POST" action="{{URL::to('/user-info')}}" >
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
<!--                                <div class="form-group">
                                    <label for=" ">Name</label>
                                    <input type="text" class="form-control" id="txtname" name="txtname" value="{{$userData['name']}}">
                                    
                                </div>-->
                                {{ csrf_field() }}
                                <div class="form-group">
                                    <label for=" ">Email</label>
                                    <input type="text" class="form-control" id="email" name="email" value="{{$userData['email']}}">
                                </div>
                                <hr>
                                <h6 class="grey">Change Password</h6>    
                                <br>
                                <div class="form-group">
                                    <label for=" ">Current Password</label>
                                    <input type="password" class="form-control" id="txtpassword" name="txtpassword" value="">
                                </div>
                                <div class="form-group">
                                    <label for=" ">New Password</label>
                                    <input type="password" class="form-control" id="password" name="password" value="">
                                </div>
                                <div class="form-group">
                                    <label for=" ">Retype Password</label>
                                    <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" value="">

                                </div>
                                <div class="form-check">  
                                    <p><button type="submit" class="btn btn-primary btn btn-primary-background text-white width100 mt-10">Save</button>
                                    </p>
                                </div>
                            </form>
                        </div>
                        <!-- User Form Ends -->     
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
@endsection