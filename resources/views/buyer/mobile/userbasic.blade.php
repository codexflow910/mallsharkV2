@extends('layouts.mobile-main')
<!-- Main Banner Starts -->
@section('content')
@section('title', 'User info')
<main>
    <script src="{{ URL::asset('assets/js/jquery.validate.js',env('HTTPS_STATUS')) }}"></script>
    <link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
    <script src="{{ URL::asset('assets/js/form-validation.js',env('HTTPS_STATUS')) }}"></script>
     <script src="{{ URL::asset('assets/js/fone-format.js',env('HTTPS_STATUS')) }}"></script>
     <script src="{{ URL::asset('assets/js/user-update.js',env('HTTPS_STATUS')) }}"></script>
@include('buyer.mobile.sidebar')


    <div class="dshbrdin store-profile-mbt">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <div class="dshodrs">
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
                    <!-- Product Sorting Starts -->
                        <div class="row srtbx">
                            <div class="col-md-6"><h3 class=" ">User Profile</h3></div>
                        </div>
                        <!-- Product Sorting Ends-->

                        <!-- User Image -->

                        <div class="usrimgup">
                            <div class="thumbnail border avatar">
                                <a href="#" style="margin:auto">
                                    <?php
                                    $image_url = '';
                                    if ($userData['user_avatar'] != '') {
                                        $image_url = getAttachmentUrlById((int)$userData['user_avatar'], 'avatar');                                        
                                    }
                                    ?>
                                    <?php if ($image_url) { ?>
                                    <img class="user-avatar_mobile" src="<?php echo $image_url; ?>" style="max-width: 100px;" title="user icon" alt="user icon">
                                    <?php } else {
                                    ?>
                                    <i class="fa fa-user" aria-hidden="true"></i>

                                    <img class="user-avatar_mobile" src="" style="max-width: 100px;" style="display: none;">
                                    <?php } ?>
                                    <div class="caption">
                                        <!--                                        <p class="small grey">Drag Your Picture Here</p>-->
                                    </div>
                                </a>
                                <div class="uploadImages"><span></span></div>
                            </div>
                        </div>
                        <div class="upload-image-file">
                            <!--                            <img id="banner_avatar_image" style="max-width:272px; max-height:100px; display:none;" alt="Banner" class="img-fluid user-avatar">      -->

                        </div>


                        <!-- User Image Ends -->
                        <!-- User Form -->
                        <div class="row">
                            <form class="col-md-6 usrbsc" id="user_account" method="Post"
                                  action="{{URL::to('/user-account')}}" enctype="multipart/form-data">

                                <div class="form-group custom-panel-style mb-30 ">
                                    <label class="custom-file-upload mt-20">
                                        Upload Your Picture
                                        <input type="file" name="file_upload" id="file_upload_mobile" class="form-control noman"
                                               onchange="readFile_mobile(this)">
                                    </label>
                                    <p class="ligt-grey mt-10 mb-0 fnt-12">Note: Only PNG , JPG files to upload</p>
                                </div>
                                <div class="form-group">
                                    <label>User Name *</label>
                                    <input type="text" class="form-control" id="user_name" name="user_name"
                                           value="<?php echo ($userData['name']) ? $userData['name'] : ""; ?>">
                                    <span class="error">{{ $errors->first('user_name') }}</span>
                                    {{ csrf_field() }}
                                </div>
                                <input type="hidden" name="avatar_image_hidden" id="avatar_image_hidden" value="">
                                <div class="form-group">
                                    <label>Address *</label>
                                    <textarea class="form-control" id="physical_address"name="physical_address"><?php echo isset($userData['physical_address']) ? $userData['physical_address'] : ""; ?></textarea>
                                <!--                                    <input type="text" class="form-control" id="user_address" name="user_address" value="<?php //echo ($userData['user_address']) ? $userData['user_address'] : "";   ?>">-->
                                    <span class="error"></span>

                                </div>

                                <div class="form-group">
                                    <label for="exampleFormControlSelect1">Country *</label>
                                    <select class="form-control" name="user_country" id="user_country">

                                        <option value="">Please select</option>
                                        <?php
                                        $countries=getAllCountries();
                                        if(!empty($countries)){
                                        foreach($countries as $cn){
                                        $selec='';
                                        if(isset($userData['user_country_id']) && $userData['user_country_id']!='' && $userData['user_country_id']==$cn['id'] ){
                                        $selec='selected';
                                        }
                                        ?>
                                        <option value="<?php echo $cn['id']; ?>" <?php echo $selec; ?>><?php echo strtolower($cn['name']); ?></option>
                                        <?php }

                                        }?>

                                    </select>
                                    <span class="error">{{ $errors->first('user_country') }}</span>
                                </div>

                                <div class="form-group">
                                    <label for="exampleFormContlaserrolSelect1">Province *</label>
                                    <select class="form-control" name="user_state" id="user_state">
                                        <?php

                                        if(isset($userData['user_country_id']) && $userData['user_country_id']!=''){
                                        $states=getStatesOfCountry($userData['user_country_id']);
                                        foreach($states as $st){
                                        $selected='';
                                        if(isset($userData['user_state_id']) && $userData['user_state_id']!='' && $userData['user_state_id']==$st['state_id'] ){
                                        $selected='selected';
                                        }
                                        ?>
                                        <option value="<?php echo $st['state_id'] ?>" <?php echo $selected; ?>><?php echo strtolower($st['state_name']); ?></option>
                                        <?php  }
                                        }?>
                                        <option value="">Please select</option>
                                    </select>
                                    <span class="error">{{ $errors->first('user_state') }}</span>
                                </div>
                                <div class="form-group">
                                    <label>City *</label>
                                    <input type="text" name="user_city_id" id="user_city" class="form-control"
                                           value="<?php echo isset($userData['user_city_id']) ? $userData['user_city_id'] : ""; ?>">
                                    <span class="error">{{ $errors->first('user_city_id') }}</span>
                                </div>
                                <div class="form-group">
                                    <label>Postal Code *</label>
                                    <input type="text" name="user_zip_code" id="user_zip_code" class="form-control"
                                           id="user_zip_code"
                                           value="<?php echo isset($userData['user_zip_code']) ? $userData['user_zip_code'] : ""; ?>">
                                    <span class="error">{{ $errors->first('user_zip_code') }}</span>
                                </div>
                                <div class="form-group">
                                    <label>Mobile *</label>
                                    <input type="tel" name="contact_no" class="form-control" id="contact_no"
                                           value="<?php echo isset($userData['contact_no']) ? $userData['contact_no'] : ""; ?>">
                                    <span class="error">{{ $errors->first('contact_no') }}</span>
                                </div>
                                <div class="form-group">
                                    <label>About</label>
                                    <textarea name="about" class="form-control" id="about"><?php echo isset($userData['about']) ? $userData['about']:""; ?></textarea>
                                    <span class="error">{{ $errors->first('about') }}</span>
                                </div>
                                <div class="form-check">

                                    <p>
                                        <button type="submit"
                                                class="btn btn-primary width70 btn-primary-background text-white">Save
                                        </button>
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

<div id="user-profile-popup_mobile" class="modal fade popup-style">

</div>

<script>
    // setTimeout(function() {
    //     window.location.href = "/user-dashboard";
    // }, 3000);
</script>
@endsection