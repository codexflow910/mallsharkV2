@extends('layouts.dashboard-main')
<!-- Main Banner Starts -->
@section('content')
@section('title', 'User info')
<main>
    <!-- <script src="{{ URL::asset('assets/js/jquery.validate.js',env('HTTPS_STATUS')) }}"></script> -->
    <link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
    <script src="{{ URL::asset('assets/js/form-validation.js',env('HTTPS_STATUS')) }}"></script>
     <script src="{{ URL::asset('assets/js/fone-format.js',env('HTTPS_STATUS')) }}"></script>
     <script src="{{ URL::asset('assets/js/user-update.js',env('HTTPS_STATUS')) }}"></script>
<!-- Dashboard Ends -->
 @php 
    $countries = getAllCountries();
 @endphp
<div class="flex-parent-div bg-light-gray account-wrapper-ja ">
        <div class="change-password-page-cmt">
            @include('buyer.customsidebar')
        </div>


    <div class="dshbrdin store-profile-mbt home-section cm-edit-ja no-full-wrap">
        <div class="container">
        <div>         
            <div class="row buyer update-design">
                <!-- <div class="col-md-2"></div> -->
                <div class="col-md-12">
                    <h1 class="account-heading">
                    <div class="d-flex align-items-center gap-3">
                    <a href="javascript:void(0)" class="d-flex align-items-center gap-10px sidebar-admin-burger-menu" id="open-sidebar-admin-panel">                                        
                                    <span id="menu-toggle-dashboard" class="shopzit-menu mt-0 ml-0 bx-menu">
                                        <span></span><span></span><span></span> <span></span>
                                    </span>
                                    <!-- <span>Menu</span> -->
                                    </a>
                                    <span>Account</span>
                    </div>
                    
                
                </h1>
                    @include('buyer.sidebar')

                    <div class="dshodrs mobileM">
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
                    <!-- Product Sorting Starts -->
                        
                        <!-- Product Sorting Ends-->

                        <!-- User Image -->

                        <div class="row srtbx eidt-user-form-container">
                            <div class="col-md-12">

                                <form class="usrbsc" id="user_account_buyer" method="POST"
                                action="{{URL::to('/user-account')}}" enctype="multipart/form-data">
                                @csrf
                             <div class="row">
                                <div class="col-md-6">
                                <div class=""><h3 class=" ">User Information</h3></div>

                                    <div class="form-group">
                                        <label>Full Name</label>
                                        <input type="text" class="form-control" placeholder="Full Name" id="user_name" name="user_name"
                                               value="<?php echo ($userData['name']) ? $userData['name'] : ""; ?>">
                                        <span class="error">{{ $errors->first('user_name') }}</span>
                                       
                                    </div>
                                    <!-- <div class="form-group">
                                      <label>First Name</label>
                                      <input type="text" class="form-control" placeholder="First Name" id="user_name" name="user_name"
                                             value="<?php // echo ($userData['name']) ? $userData['name'] : ""; ?>">
                                      <span class="error">{{ $errors->first('user_name') }}</span>
                                     
                                  </div> -->
                                  <h5>Address *</h5>
                                  <div class="form-group">
                                      <label>Street </label>
                                      <input class="form-control" placeholder="Ex. 124 Birch St." id="physical_address" name="physical_address" value="<?php echo isset($userData['physical_address']) ? $userData['physical_address'] : ""; ?>" >
                                  <!--                                    <input type="text" class="form-control" id="user_address" name="user_address" value="<?php //echo ($userData['user_address']) ? $userData['user_address'] : "";   ?>">-->
                                      <span class="error"></span>
    
                                  </div>
                                </div>
                                <div class="col-md-6 user-account-image">
                                    <div class="user-profile-card-wrapper">
                                    <div class="usrimgup ">
                                        <div class="thumbnail avatar">
                                            <a href="#" style="margin:auto">
                                                <?php
                                                $image_url = '';
                                                $class = '';
                                                if ($userData['user_avatar'] != '') {
                                                    $image_url = getAttachmentUrlById((int)$userData['user_avatar'], 'avatar');
                                                }
                                                ?>
    
                                                <i class="fa fa-user noman {{empty($$image_url) ? 'd-none' : 'd-block'}}" aria-hidden="true"></i>
                                                <img id="user-avatar_source" class="user-avatar_source" src="<?php echo !empty($image_url) ? $image_url :  asset('assets/images/personLogo.png') ?>" alt="{{$userData['name']}}" title="{{$userData['name']}}" style="max-width: 100px; ">
                                            
                                                <div class="caption">
                                                   
                                                </div>
                                            </a>
                                            <div class="uploadImages"><span></span></div>
                                        </div>
                                    </div>
                                    <div class="form-group custom-panel-style mb-30 ">
                                        <h5>Upload a profile image</h5>
                                        <label class="custom-file-upload ">
                                           Upload Image
                                            <input type="file" name="file_upload" id="file_upload_profile" class="form-control"
                                                     onchange="readFile(this)" accept=".jpg,.jpeg,.png"> 
                                        </label>
                                        <p class="ligt-grey mt-10 mb-0 fnt-12">Note: Only PNG , JPG files accepted.</p>
                                    </div>
                                </div>
                            </div>
                             </div>
                             <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="exampleFormControlSelect1">Country</label>
                                        <select class="form-control" name="user_country" id="user_country" style="height: calc(2.25rem + 5px);">
      
                                            @foreach ($countries as $country)
                                                <option value="{{ $country['id'] }}" 
                                                    {{ isset($userData['user_country_id']) && $userData['user_country_id'] == $country['id'] ? 'selected' : '' }}>
                                                    {{ $country['name'] }}
                                                </option>
                                            @endforeach
                                            
                                        </select>
                                        <span class="error">{{ $errors->first('user_country') }}</span>
                                    </div>
                                    <div class="form-group">
                                        <label>Zip/Postal Code</label>
                                        <input type="text" name="user_zip_code" placeholder="Ex. 00012" id="user_zip_code" class="form-control"
                                               id="user_zip_code"
                                               value="<?php echo isset($userData['user_zip_code']) ? $userData['user_zip_code'] : ""; ?>">
                                        <span class="error">{{ $errors->first('user_zip_code') }}</span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group city-filed-eidt-form">
                                        <label>City</label>
                                        <input type="text" placeholder="Ex. Chicago" name="user_city_id" id="user_city_id" class="form-control"
                                               value="<?php echo isset($userData['user_city_id']) ? $userData['user_city_id'] : ""; ?>">
                                        <span class="error">{{ $errors->first('user_city_id') }}</span>
                                    </div>
                                    <div class="form-group">
                                        <label for="exampleFormContlaserrolSelect1"> State/Province</label>
                                        <select class="form-control" name="user_state" id="user_state"  style="height: calc(2.25rem + 5px);">
                                            <option value="">Select State/Province</option>
                                            <?php
      
                                            if(isset($userData['user_country_id']) && $userData['user_country_id']!=''){
                                            $states=getStatesOfCountry($userData['user_country_id']);
                                            foreach($states as $st){
                                            $selected='';
                                            if(isset($userData['user_state_id']) && $userData['user_state_id']!='' && $userData['user_state_id']==$st['state_id'] ){
                                            $selected='selected';
                                            }
                                            ?>
                                            
                                            <option value="<?php echo $st['state_id'] ?>" <?php echo $selected; ?>><?php echo $st['state_name']; ?></option>
                                            <?php  }
                                            }else{
                                                $states=getStatesOfCountry(1);
                                            foreach($states as $st){
                                            $selected='';
                                            if(isset($userData['user_state_id']) && $userData['user_state_id']!='' && $userData['user_state_id']==$st['state_id'] ){
                                            $selected='selected';
                                            }
                                            ?>
                                            
                                            <option value="<?php echo $st['state_id'] ?>" <?php echo $selected; ?>><?php echo $st['state_name']; ?></option>
                                            <?php  }
                                            }

                                            ?>
                                            <!-- <option value="">Please select</option> -->
                                        </select>
                                        <span class="error">{{ $errors->first('user_state') }}</span>
                                    </div>
                                </div>
                            </div>   

                                <div class="row">
                                <div class="col-md-6">
                                    <h5>Contact</h5>
                                    <div class="form-group" style="margin-bottom:32px;">
                                        <label>Mobile Phone</label>
                                        <input type="tel" name="contact_no" placeholder="(111) 111-1111" class="form-control" id="contact_no"
                                               value="<?php echo isset($userData['contact_no']) ? $userData['contact_no'] : ""; ?>">
                                       
                                    </div>
                                    <div class="form-group">
                                        <label>Email* </label>
                                        <input type="email" name="email" placeholder="Please Enter Your Email" 
                                        class="form-control" value="<?php echo isset($userData['email']) ? $userData['email'] : ""; ?>">
                                        <span class="error">{{ $errors->first('email') }}</span>
                                    </div>
                                </div>

                                   
                                    {{-- <div class="form-check">

                                        <p>
                                            <button type="submit"
                                                    class="btn btn-primary btn-primary-background text-white">Save
                                            </button>
                                           <a href="/user-dashboard" class="btn btn-primary-back large-btn-mbt back_button text-white"> 
                                                    Back
                                            </a>
                                        </p>
                                    </div> --}}

                             </div>
                             <div class="form-check">

                                <p>
                                    <button type="submit"
                                            class="btn btn-primary btn-primary-background text-white">Save
                                    </button>
                                   <a href="/user-dashboard" class="btn btn-primary-back large-btn-mbt back_button text-white"> 
                                            Back
                                    </a>
                                </p>
                            </div>
                             
                              <input type="hidden" name="avatar_image_hidden" id="avatar_image_hidden" value="">
                             
                             
                             
                              
                             
                              {{-- <div class="form-group">
                                  <label>About</label>
                                  <textarea name="about" class="form-control" id="about"><?php echo isset($userData['about']) ? $userData['about']:""; ?></textarea>
                                  <span class="error">{{ $errors->first('about') }}</span>
                              </div> --}}
                            
                          </form>
                            </div>
                           
                        </div>
                        
                       
                        <div class="upload-image-file">
                            <!--                            <img id="banner_avatar_image" style="max-width:272px; max-height:100px; display:none;" alt="Banner" class="img-fluid user-avatar">      -->

                        </div>


                        <!-- User Image Ends -->
                        <!-- User Form -->
                        <div class="row">
                           
                        </div>
                        <!-- User Form Ends -->
                    </div>
                </div>
            </div>
        </div>
        </div>
    </div>
</div>

</main>
<div id="store-logo-popup" class="modal fade popup-style create-shipping-profile">
    <div class="modal-dialog">

        <!-- Modal content-->
        <div class="modal-content clearfix">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Brand Logo</h4>
            </div>
            <div class="modal-body">
                <div class="input-container-div">
                    <div class="input-label">
                        <label for="image_upload" class="label">Upload image:<br>Allowed image format
                            (<strong>png</strong>)
                        </label>
                    </div>
                    <div class="form-input">
                        <div>
                            <input name="file_name" value="" id="file_upload" class="file_upload form-control"
                                   type="file">
                        </div>
                        <div class="flie_upload_bar_wrapper">
                            <div class="flie_upload_bar">

                            </div>
                            <span style="display:none;"></span>
                        </div>
                        <div class="clearfix"></div>
                        <div class="upload-image-file">
                            <img id="blah" src="#" class="img-fluid" alt="your image" style="display:none;"/>
                            <input name="avatar_image_hidden" value="" id="avatar_image_hidden" type="hidden">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default btn-primary btn-simple " id="save_logo">Save logo
                    </button>
                </div>
            </div>
            <div class="uploadImages-popup"><span></span></div>

        </div>

    </div>

</div>
<link href="{{ URL::asset('assets/image-cropper/css/cropper.css' ,env('HTTPS_STATUS'))}}" rel="stylesheet"
      type="text/css" media="screen"/>
<link href="{{ URL::asset('assets/image-cropper/css/main.css' ,env('HTTPS_STATUS'))}}" rel="stylesheet" type="text/css"
      media="screen"/>
<script src="{{ URL::asset('assets/image-cropper/js/cropper.min.js' ,env('HTTPS_STATUS'))}}" type="text/javascript"
        charset="utf-8"></script>

<div id="user-profile-popup" class="modal fade popup-style create-shipping-profile">


</div>
<script src="{{ URL::asset('assets/js/fone-format.js',env('HTTPS_STATUS')) }}"></script>
<script>
    $(function() {
        $("#contact_no").mask("(999) 999-9999");

        $("#contact_no").on("blur", function() {
        var last = $(this).val().substr($(this).val().indexOf("-") + 1);

            if (last.length == 5) {
                var move = $(this).val().substr($(this).val().indexOf("-") + 1, 1);
                var lastfour = last.substr(1, 4);
                var first = $(this).val().substr(0, 9);
                $(this).val(first + move + '-' + lastfour);
            }
        });
    });

    // setTimeout(function() {
    //     window.location.href = "/user-dashboard";
    // }, 3000);
</script>
@endsection