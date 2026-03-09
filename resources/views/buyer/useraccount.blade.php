@extends('layouts.dashboard-main')
<!-- Main Banner Starts -->
@section('content')
@section('title', 'Buyer info')
<main>
    <!-- Dashboard Ends -->
    <!-- <script src="{{ URL::asset('assets/js/jquery.validate.js'),env('HTTPS_STATUS') }}"></script> -->
    <script src="{{ URL::asset('assets/js/form-validation.js',env('HTTPS_STATUS')) }}"></script>

    <div class="flex-parent-div bg-light-gray">
            <div class="change-password-page-cmt">
                @include('buyer.customsidebar')

            </div>
            <!-- cm-edit-ja -->
        <div class="dshbrdin buyer update-design  home-section cm-edit-ja no-full-wrap">
            <div class="container">        
                <div class="row  buyer update-design">
                    <!-- <div class="col-md-2"></div> -->
                    <div class="col-md-12">


                        <h1 class="account-heading ">
                            <div class="d-flex align-items-center gap-3">                        
                                    <a href="javascript:void(0)" class="d-flex align-items-center gap-10px sidebar-admin-burger-menu" id="open-sidebar-admin-panel">                                        
                                    <span id="menu-toggle-dashboard" class="shopzit-menu mt-0 ml-0 bx-menu">
                                        <span></span><span></span><span></span> <span></span>
                                    </span>
                                    <!-- <span>Menu</span> -->
                                    </a>
                                    <span>Security</span>
                                    </div>
                                </h1>
                        @include('buyer.sidebar') 
                        <div class="dshodrs">
                            <!-- Product Sorting Starts -->
                            {{-- <div class="row srtbx"><div class="col-md-6"><h3 class=" ">User Account</h3></div></div>     --}}
                            <!-- Product Sorting Ends--> 
                            <!-- User Form -->
                            <div class="row">
                                <form class="col-md-6 usrbsc" name="userInfo" id="userInfo" method="POST" action="{{URL::to('/user-info')}}" >
                                    @if(Session::has('updatesuccessmessage'))
                                        <script>
                                            toastr.options = {
                                                closeButton: true,
                                                tapToDismiss: true,
                                                positionClass: 'toast-top-right',
                                                toastClass: 'toast-icon custom-toast-success',
                                                progressBar: true,
                                            };
                                            toastr.success('<?php echo  Session::get('updatesuccessmessage'); ?> ');
                                        </script>
                                    @endif
                                    @if(Session::has('updateerrormessage'))
                                    <script>
                                        toastr.options = {
                                            closeButton: true,
                                            tapToDismiss: true,
                                            positionClass: 'toast-top-right',
                                            toastClass: 'toast-icon custom-toast-error',
                                            progressBar: true,
                                        };
                                        toastr.error('<?php echo  Session::get('updateerrormessage'); ?> ');
                                    </script>
                                    @endif
                <!--                                <div class="form-group">
                                        <label for=" ">Name</label>
                                        <input type="text" class="form-control" id="txtname" name="txtname" value="">
                                        
                                    </div>-->
                                    {{ csrf_field() }}
                                    {{-- <div class="form-group">
                                        <label for=" ">Email</label>
                                        <input type="text" class="form-control" id="email" name="email" value="{{$userData['email']}}">
                                    </div> --}}
                                    {{-- <hr> --}}

                                    <div class="security-form-wrapper">    
                                        <h3>Change Password</h3>
                                    @if(!empty($userData['password']))
                                        <div class="form-group">
                                            <label for=" ">Current Password</label>
                                            <input type="password" placeholder="Type Current Password" class="form-control" id="txtpassword" name="txtpassword" value="">
                                        </div>
                                    @endif
                                    <div class="form-group">
                                        <label for=" ">New Password</label>
                                        <input type="password" class="form-control" placeholder="*************" id="password" name="password" value="">
                                    </div>
                                    <div class="form-group">
                                        <label for=" ">Retype Password</label>
                                        <input type="password" placeholder="*************" class="form-control" id="password_confirmation" name="password_confirmation" value="">

                                    </div>
                                    <div class="form-check" style="padding-left:0px!important;">  
                                        <p style="margin-bottom:0px;"><button type="submit" class="btn btn-primary btn btn-primary-background text-white width100 mt-10">Save</button>
                                            <a href="/user-dashboard" class="btn btn-primary-back large-btn-mbt back_button text-white  mt-10"> 
                                            Back
                                            </a>
                                        </p>
                                    </div>
                                </div>
                                </form>
                            </div>
                            <!-- User Form Ends -->     
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
@endsection