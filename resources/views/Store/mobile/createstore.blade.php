@extends('layouts.mobile-main')
<!-- Main Banner Starts -->
@section('content')
@section('title', 'Create Store')
<script src="https://cdn.ckeditor.com/ckeditor5/42.0.0/ckeditor5.js"></script>
<style>
    .upload_btn_wrapper {
        background: rgba(0, 0, 0, 0) url("/images/upload_btn.png") no-repeat scroll 0 0;
        height: 29px;
        margin-bottom: 5px;
        overflow: hidden;
        width: 138px;
    }

    div.upload_btn_wrapper input {

    }

    .flie_upload_bar_wrapper {
        float: left;
        margin: 5px 0;
        width: 138px;
        position: relative;
    }

    .flie_upload_bar {
        float: left;
        background: #82c451 none repeat scroll 0 0;
        height: 5px;
        margin-top: 5px;
    }

    .flie_upload_bar_wrapper span {
        float: left;
        color: #c00;
        font-size: 13px;
        position: absolute;
        left: 0px;
    }
    [type="checkbox"]:checked, [type="checkbox"]:not(:checked) {
        position: static;
        left: -9999px;
    }

</style>
<main>
    <script src="{{ URL::asset('assets/js/jquery.validate.js',env('HTTPS_STATUS')) }}"></script>
    <script src="{{ URL::asset('assets/js/fone-format.js',env('HTTPS_STATUS')) }}"></script>
    <script src="{{ URL::asset('assets/js/storevalidatonform.js',env('HTTPS_STATUS')) }}"></script>
    <script src="{{ URL::asset('assets/js/storecustom.js',env('HTTPS_STATUS')) }}"></script>
    @include('Store.mobile.sidebar')
    <!-- Dashboard Ends -->
    <?php //print_rr($storeData); 
    //echo '$storeData: <pre>' .print_r($storeData,true). '</pre>'; die;
    ?>
    <div class="dshbrdin store-profile-mbt mb-20">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <div class="dshodrs">
                        @if(Session::has('SuccessMessage'))
                        <div class="alert alert-success alert-success-mbt" id="success_message" role="alert">
                            {{ Session::get('SuccessMessage') }}
                            <script>
                                setTimeout(function () {
                                jQuery("#success_message").fadeOut('slow')
                                }, 3000);
                            </script>
                        </div>
                        @endif
                        <!-- Product Sorting Starts -->
                        <div class="row srtbx">
                            <div class="col-md-6"><h3 class=" ">Company Profile</h3></div>
                        </div>
                        <!-- Product Sorting Ends-->

                        <!-- User Image -->
                        <div class="upload-image-file">
                            <?php
                            $imgeUrl = "";
                            $style = 'none';
                            if (isset($storeData['store_logo']) && $storeData['store_logo'] != "") {
                                $style = 'block';
                                $imgeUrl = getAttachmentUrlById((int) $storeData['store_logo'], 'media');
                            }
                            ?>
                            <img id="banner_avatar_image" src="<?php echo $imgeUrl; ?>"
                                 style="max-width:272px; max-height:100px; display:<?php echo $style; ?>;" 
                                 class="img-fluid">
                            <input name="avatar_image_hidden"
                                   value="<?php echo $soreLogoId = isset($storeData['store_logo']) ? $storeData['store_logo'] : '' ?>"
                                   id="avatar_image_hidden" type="hidden">

                            <div class="uploadImages"><span></span></div>
                        </div>
                        <!-- User Image Ends -->
                        <!-- User Form -->
                        <div class="row">
                            <form class="col-md-6 usrbsc" id="store-user_account" method="Post"
                                  action="{{URL::to($actionUrl)}}" enctype="multipart/form-data">

                                <div class="form-group custom-panel-style mb-30 ">
                                    <button class="custom-file-upload mt-20" type="button" id="upload-logo">Upload
                                        logo
                                    </button>
                                    <label class="custom-file-upload mt-20" style="display:none;">
                                        Upload Logo
                                        <input type="file" name="file_upload_new" id="file_upload_new"
                                               class="form-control" onchange="readFile(this)">
                                    </label>
                                    <p class="ligt-grey mt-10 mb-0 fnt-12">Note: Only PNG and JPG files to upload
                                        <!--                                        Maximum file size 1 MB-->
                                    </p>
                                </div>
                                <div class="form-group">
                                    <label>Brand Name </label>
                                    <input type="text" class="form-control" id="store_name" name="store_name"
                                           value="<?php echo (isset($storeData['store_name'])) ? $storeData['store_name'] : ""; ?>">
                                    <span class="error">{{ $errors->first('user_name') }}</span>
                                    {{ csrf_field() }}
                                </div>
                                <input type="hidden" name="store_image_id" id="store_image_id" value="">
                                <div class="form-group">
                                    <label>Address</label>
                                    <textarea name="street_address" id="street_address"
                                              class="form-control"><?php echo (isset($storeData['street_address'])) ? $storeData['street_address'] : ""; ?></textarea>
                                    <span class="error"></span>

                                </div>
                                
                                <div class="form-group">
                                    <label>Contact No</label>
                                    <input type="text" name="store_phone" class="form-control" id="store_phone"
                                           value="<?php echo (isset($storeData['contact_info'])) ? $storeData['contact_info'] : ""; ?>">
                                    <span class="error">{{ $errors->first('store_phone') }}</span>
                                </div>
                                <div class="form-group">
                                    <label for="Country">Country *</label>
                                    <select class="form-control" name="store_country" id="user_country">

                                        <option value="">Please select</option>
                                        <?php
                                        $Canada_S = '';
                                        $usa_S = '';
                                        if (isset($storeData['store_country']) && $storeData['store_country'] == '1') {
                                            $usa_S = 'selected';
                                        }
                                        if (isset($storeData['store_country']) && $storeData['store_country'] == '2') {
                                            $Canada_S = 'selected';
                                        }

                                        $countries=getAllCountries();
                                        if(!empty($countries)){
                                        foreach($countries as $cn){
                                        $selec='';
                                        if(isset($storeData['store_country']) && $storeData['store_country']!='' && $storeData['store_country']==$cn['id'] ){
                                        $selec='selected';
                                        }
                                        ?>
                                        <!-- <option value="<?php echo $cn['id']; ?>" <?php echo $selec; ?>><?php echo strtolower($cn['name']); ?></option> -->
                                        <?php }

                                        }
                                        echo '<option  value="1" ' . $usa_S . '>USA</option>';
                                        echo '<option  value="2" ' . $Canada_S . '>CANADA</option>';
                                        ?>

                                    </select>
                                    <span class="error">{{ $errors->first('store_country') }}</span>
                                </div>

                                <div class="form-group">
                                    <label for="Province">Province *</label>
                                    <select class="form-control" name="store_state" id="user_state">
                                        <?php

                                        if(isset($storeData['store_country']) && $storeData['store_country']!=''){
                                        $states=getStatesOfCountry($storeData['store_country']);
                                        foreach($states as $st){
                                        $selected='';
                                        if(isset($storeData['store_state']) && $storeData['store_state']!='' && $storeData['store_state']==$st['state_id'] ){
                                        $selected='selected';
                                        }
                                        ?>
                                        <option value="<?php echo $st['state_id'] ?>" <?php echo $selected; ?>><?php echo strtolower($st['state_name']); ?></option>
                                        <?php  }
                                        }?>
                                        <option value="">Please select</option>
                                    </select>
                                    <span class="error">{{ $errors->first('store_state') }}</span>
                                </div>

                                <div class="form-group">
                                    <label>City *</label>
                                    <input type="text" name="store_city" id="store_city" class="form-control"
                                           value="<?php echo isset($storeData['store_city']) ? $storeData['store_city'] : ""; ?>">
                                </div>
                                    <br/>

                                <div class="form-group">
                                    <label>Zip Code</label>
                                    <input type="text" name="zip_code" class="form-control" id="zip_code"
                                           value="<?php echo (isset($storeData['zip_code'])) ? $storeData['zip_code'] : ""; ?>">
                                    <span class="error">{{ $errors->first('store_phone') }}</span>
                                </div>
                                <div class="form-group">
                                    <label>Web Site</label>
                                    <input type="text" class="form-control" id="store_website" name="store_website"
                                           value="<?php echo (isset($storeData['store_website'])) ? $storeData['store_website'] : ""; ?>">
                                    <span class="error">{{ $errors->first('user_name') }}</span>

                                </div>
                                 <div class="form-group">
                                                <label>About Store</label>
                                                <textarea class="form-control" id="about_merchant-mobile" name="about_store"><?php echo $aboutMerchant = isset($storeData['about_store']) ? $storeData['about_store'] : '' ?></textarea>
                                            </div>
                                <?php // if(!isset($storeData['terms_condtions'])){ ?>
                                <div class="form-group">
                                    <input type="checkbox"  id="store_term_conditon" name="store_term_conditon"
                                           value="1" <?php if(isset($storeData['terms_condtions']) && $storeData['terms_condtions'] == '1') { echo 'checked'; } ?>>
                                    <span class="error">{{ $errors->first('store_term_conditon') }}</span>
                                    <label>I accept <a href="<?php echo URL::to('/terms') ?>" target="blank">terms and conditions</a></label>
                                </div>
                                <?php // } ?>

                                <div class="form-check">
                                    <p>
                                        <button type="submit" class="btn btn-primary width70 text-white">Save</button>
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
                        <!-- <label for="image_upload" class="label">Upload image:<br>Allowed image format
                            (<strong>png</strong>)
                        </label> -->
                    </div>
                    <div class="form-input">
                        <div>
                            <input name="file_name" value="" id="file_upload" class="file_upload form-control"type="file">
                        </div>
                        <div class="flie_upload_bar_wrapper">
                            <div class="flie_upload_bar">

                            </div>
                            <span style="display:none;"></span>
                        </div>
                        <div class="clearfix"></div>
                        <div class="upload-image-file">
                            <img id="blah" src="#" class="img-fluid" alt="your image" style="display:none;"/>

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
<script>
    CKEDITOR.replace('about_merchant-mobile');
CKEDITOR.config.allowedContent = true;

    $(document).ready(function () {
        if($('input[name="store_term_conditon"]').is(':checked') && $('input[name="store_term_conditon"]').val() == '1') {
            $('#shop-policy').show();
        } else {
            $('#shop-policy').hide();
        }
        $('input:radio[name="store_term_conditon"]').change(function () {
            // checks that the clicked radio button is the one of value 'Yes'
            // the value of the element is the one that's checked (as noted by @shef in comments)
            if ($(this).val() == 'Yes') {
                $('#shop-policy').show();
            } else {
                $('#shop-policy').hide();
            }
        });
    

        $("#user_country").on("change", function () {
            countryId = $(this).val();
            data = {};
            data['country_id'] = countryId;
            sendreq(data, 'buyer', 'state', 'getStates');
        });
    });
    function getStates(data) {
        $("#user_state").html(data);
    }
    $("#user_state").on("change", function () {
        var stateId = $("#user_state").val();
        if (stateId.length <= 0) {
            $(".hide-city").hide();
        } else {
            $(".hide-city").show();
        }
    });
    function getStates(data) {
        $("#user_state").html(data);
    }
    $("#user_state").on("change", function () {
        var stateId = $("#user_state").val();
        if (stateId.length <= 0) {
            $(".hide-city").hide();
        } else {
            $(".hide-city").show();
        }
    });
    </script>
@endsection