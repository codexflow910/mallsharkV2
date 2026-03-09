@extends('layouts.dashboard-main')
<!-- Main Banner Starts -->
@section('content')
@section('title', 'Create Store')

<script src="https://cdn.ckeditor.com/4.22.1/standard/ckeditor.js"></script>
<script>
    CKEDITOR.config.versionCheck = false;
    </script>
<link href="{{ URL::asset('assets/css/prettyPhoto.css' ,env('HTTPS_STATUS'))}}" rel="stylesheet" type="text/css" media="screen" />
<style>
    .upload_btn_wrapper {
        background: rgba(0, 0, 0, 0) url("/images/upload_btn.png") no-repeat scroll 0 0;
        height: 29px;
        margin-bottom: 5px;
        overflow: hidden;
        width: 138px;
    }

    div.upload_btn_wrapper input {}

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

    [type="checkbox"]:checked,
    [type="checkbox"]:not(:checked) {
        position: static;
        left: -9999px;
    }

    .zip-alert {
        color: #f24a4a;
        padding: 5px;
        font-size: 12px;
    }

    .hide_image_counter {
        display: none;
        color: green;
    }

    #loading {
        position: fixed;
        display: block;
        width: 100%;
        height: 100%;
        opacity: 0.7;
        background-color: #fff;
        z-index: 99;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
    }

    #loading-image {
        position: absolute;
        top: 50%;
        z-index: 100;
        width: 150px;
        left: 50%;
        transform: translate(-50%,-50%);
    }
    .humber{
        background-color: #0000a7!important;
    }
    /* #menu-toggle-dashboard{
        margin-right: 10px;
    } */
    #open-sidebar-admin-panel{
            margin-top: 5px;
            display: block!important;
        }
    @media screen and (max-width: 573px){
        #open-sidebar-admin-panel{
            margin-top: -35px;
        }
    }


</style>
<main>
    <script src="{{ URL::asset('assets/js/jquery.validate.js', env('HTTPS_STATUS')) }}"></script>
    <script src="{{ URL::asset('assets/js/fone-format.js', env('HTTPS_STATUS')) }}"></script>
    <script src="{{ URL::asset('assets/js/storevalidatonform.js', env('HTTPS_STATUS')) }}"></script>
    <script src="{{ URL::asset('assets/js/storecustom.js', env('HTTPS_STATUS')) }}"></script>
    <!-- @include('Store.sidebar') -->
    <!-- Dashboard Ends -->
    <?php //print_rr($storeData);
    ?>
      @if(session('dataLayerCreateStoreEvent'))
            <script>
                dataLayer = dataLayer || [];
                dataLayer.push(@json(session('dataLayerCreateStoreEvent')));
            </script>
        @endif
        <div class="change-password-page-cmt">
            @include('buyer.customsidebar')

        </div>
    <div class="dshbrdin store-profile-mbt mb-20 cm-edit-ja">
        <?php
        $store_id = $storeData['store_id'];
        $productcount =  getStoreTotalProducts($store_id);
        if (!$storeData && $storeData['stripe'] != 1 && $productcount < 0) { ?>
            <div class="seller-name-page stock-your-page">
                <div class="container">
                    <div class="progress">
                        <div class="progress-bar w-50" role="progressbar" aria-valuenow="50" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                </div>
            </div>
        <?php } ?>
        

        <div class="container">
            @include('Store.sidebar')

            <div class="row">
                <div class="col-md-12">
                    <div class="dshodrs">
                        @if(Session::has('SuccessMessage'))
                        <?php $successMessage = Session::get('SuccessMessage') ?>
                            <script>
                            toastr.options = {
                                closeButton: true,
                                tapToDismiss: true,
                                positionClass: 'toast-top-right',
                                toastClass: 'toast-icon custom-toast-success',
                                progressBar: true,
                            };
                            toastr.success('<?php echo  $successMessage ?> ');
                            </script>
                        @endif


                        <!-- Product Sorting Starts -->
                        <div class="row srtbx">
                            <div class="col-md-6" style="padding: 0px;">
                                <div class="hd-bt">
                                    <h3 style="margin-bottom: 0px;"> 
                                    <div class="d-flex align-items-center gap-3">                        
                                                <a href="javascript:void(0)" class="d-flex align-items-center gap-10px sidebar-admin-burger-menu" id="open-sidebar-admin-panel">                                        
                                                <span id="menu-toggle-dashboard" class="shopzit-menu mt-0 ml-0 bx-menu">
                                                    <span class="humber"></span><span class="humber"></span ><span  class="humber"></span><span  class="humber"></span>
                                                </span>
                                                <!-- <span>Menu</span> -->
                                                </a>
                                                <span class="storeName">{{$storeData['store_name']}}</span>
                                                </div>    
                        
                                       </h3>
                                    </div>
                            </div>
                        </div>
                        <!-- Product Sorting Ends-->

                        <!-- User Form -->
                        <div class="row">
                       
                            <form class="col-md-7 usrbsc" id="store-user_account" method="Post" action="{{ URL::to($actionUrl) }}" enctype="multipart/form-data">
                                {{ csrf_field() }}

                                <!--Maximum file size 1 MB-->
                                <div class="row">
                                
                                <div class="col-md-12" id="file_upload_store">
                                        <h5 class="ml-1 mb-0">Upload your store logo</h5>
                                        
                                        <div class="form-group custom-panel-style mb-30 d-flex ">
                                            
                                            <div class="mr-4">
                                                <div class="upload-image-file d-flex justify-content-evenly">
                                                <?php
                                                $imgeUrl = '';
                                                $style = 'none';
                                                if (isset($storeData['store_logo']) && $storeData['store_logo'] != '') {
                                                    $style = 'block';
                                                    $imgeUrl = getAttachmentUrlById((int) $storeData['store_logo'], 'media');
                                                }
                                                ?>
                                                <img id="banner_avatar_image" src="<?php echo $imgeUrl; ?>" style="width:150px; height:150px; display:<?php echo $style; ?>;border-radius: 100%; border: 2px solid #e7e7e7; " alt="<?php echo isset($storeData['store_name']) ? $storeData['store_name'] : ''; ?>" title="<?php echo isset($storeData['store_name']) ? $storeData['store_name'] : ''; ?>" class="img-fluid store-avatar">
                                                <input name="avatar_image_hidden" value="<?php echo $soreLogoId = isset($storeData['store_logo']) ? $storeData['store_logo'] : ''; ?>" id="avatar_image_hidden" type="hidden">
                                                
                                                <div class="uploadImages_store"><span></span></div>
                                                </div>
                                             </div>
                                            <div>
                                                 <label class="custom-file-upload mt-30">
                                                Upload Image
                                                <input type="file" name="file_upload" id="file_upload" class="form-control" >
                                            </label>
                                            
                                            <p class="ligt-grey mt-10 mb-0 fnt-12">Note: Only PNG, JPG files accepted.
                                            </p> 
                                            <p id="logo_image_size_note" class="text-danger mb-0 fnt-12">Image size should be minimum
                                                350x350 (px)</p>
                                            <?php
                                            // echo 'Data: <pre>' .print_r($storeData,true). '</pre>'; die();
                                            ?>
                                            @if (isset($storeData))
                                            
                                            @endif
                                            
                                            </div>
                                            
                                            
                                            
                                            
                                        </div>
                                       

                                    </div>
                                    <div class="col-md-12">
                                        <input type="hidden" id="store_id" value="{{$storeData['store_id']}}">
                                        <div class="form-group custom-panel-style mb-30" style="margin-top:0px!important;">
                                            <h5>Upload Your Store Banners</h5> 
                                            @if (isset($storeData))
                                            <div class="upload-image-file row justify-content-left">
                                                <?php
                                                if (isset($storeData)) {
                                                    $bannerSerialize = $storeData['banner_image_id'];
                                                    if (isset($bannerSerialize) && !empty($bannerSerialize)) {
                                                        $bannerIds = unserialize($bannerSerialize);
                                                        foreach ($bannerIds as $bannerId) {
                                                            $imageUrl = getAttachmentUrlByIdMedia((int) $bannerId, 'media');
                                                            $mediaUrl = asset('uploads/media/' . basename($imageUrl));
                                                            $basepath = basename($imageUrl);
                                                            $mediaUrl = str_replace($imageUrl, '',  $basepath);
                                                ?>
                                                            <div class="image-wrapper col-6 image-banners store-image-banner-edit updateImage-<?php echo $bannerId; ?>" data-noman="noman">
                                                                <a class="viewImages" rel="prettyPhoto" href="<?php echo $imageUrl; ?>">
                                                                    <img src="<?php echo $imageUrl; ?>" style="width:100%; height:87px; display: block;border-radius: 20px;border: 2px solid blue;object-fit:cover" alt="" class="img-fluid store-banner-image previewClass">
                                                                </a>
                                                                <input type="hidden" name="previous_image_id" value="<?php echo $bannerId; ?>" >
                                                                <input id="imagesrc-<?php echo $bannerId; ?>" type="hidden" value= "" name="storeBannerImage[{{$bannerId}}]"/>
                                                                <div class="image-actions d-flex justify-content-center">
                                                                    <a href="javascript:void(0)" data-toggle="modal" data-target="#cropper_<?php echo $bannerId; ?>" data-storeid="{{ $storeData['store_id'] }}" data-filename="{{ $mediaUrl }}" class="editEventCrop pl-2 pr-2" data-image-id="<?php echo $bannerId; ?>">
                                                                        <i class="fa fa-crop" aria-hidden="true"></i>
                                                                    </a>
                                                                    <div class="modal" id="cropper_<?php echo $bannerId; ?>" tabindex="-1" role="dialog" aria-hidden="true">
                                                                        <div class="modal-dialog animated bounceInDown" style="max-width:800px">
                                                                            <div class="modal-content">
                                                                                <div class="modal-body">
                                                                                    <div class="row imagecrop-area">
                                                                                        <div class="col-xs-12 col-sm-12">
                                                                                            <div class="img-container">
                                                                                                <img class="Cropperimage previewClass" src="<?php echo $imageUrl; ?>" alt="Picture">
                                                                                            </div>
                                                                                            <div class="clearfix"></div><br>
                                                                                        </div>
                                                                                        <div class="clearfix spacer20"></div>
                                                                                        <div class="col-md-12 docs-toggles">
                                                                                            <!-- Content for docs-toggles can be added here -->
                                                                                        </div>
                                                                                    </div>
                                                                                </div>
                                                                                <div class="modal-footer">
                                                                                    <button data-dismiss="modal" class="btn btn-primary btn-simple" type="button">Close</button>
                                                                                    <button class="btn-primary btn text-white btn-success getCropImageExist" data-image-id="<?php echo $bannerId; ?>" type="button" data-dismiss="modal">Save</button>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <!-- <a href="javascript:void(0)" data-storeid="{{ $storeData['store_id'] }}" data-filename="{{ $mediaUrl }}" class="edit-image pl-2 pr-2" data-image-id="<?php echo $bannerId; ?>">
                                                                        <i class="fa fa-pencil" aria-hidden="true"></i>
                                                                    </a> -->
                                                                    <a href="javascript:void(0)" data-store-id="{{ $storeData['store_id'] }}" data-filename="{{ $mediaUrl }}" class="delete-banner-image pl-2 pr-2" data-image-id="<?php echo $bannerId; ?>">
                                                                        <i class="fa fa-trash" aria-hidden="true"></i>
                                                                    </a>
                                                                </div>
                                                                <!-- Start Update Banner Image Modal -->
                                                                <div class="modal fade" id="updateImage-<?php echo $bannerId; ?>" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                                                                    <div class="modal-dialog" role="document">
                                                                        <div class="modal-content">
                                                                            <div class="modal-header">
                                                                                <h5 class="modal-title" id="exampleModalLabel">
                                                                                    Update Image</h5>
                                                                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                                                    <span aria-hidden="true">&times;</span>
                                                                                </button>
                                                                            </div>
                                                                            <div class="modal-body text-center">
                                                                                <!-- <form class="col-md-12 usrbsc" id="store-user_account" method="POST" action="{{ URL::to($actionUrl) }}" enctype="multipart/form-data"> -->
                                                                                <div class="form-group custom-panel-style mb-30">
                                                                                    <h5>Update Your Store Banners</h5>
                                                                                    <label class="custom-file-upload mt-20 ">
                                                                                        Upload Image
                                                                                        <input type="file" name="update_upload_banner" data-storeid="{{ $storeData['store_id'] }}" data-filename="{{ $mediaUrl }}" data-image-id="<?php echo $bannerId; ?>" id="update_upload_banner-<?php echo $bannerId; ?>" class="update_upload_banner-<?php echo $bannerId; ?>" class="form-control">
                                                                                        <input type="hidden" name="update_banner_image_id" id="update_banner_images_serilize" value="">
                                                                                    </label>
                                                                                    <p class="ligt-grey mt-10 mb-0 fnt-12">
                                                                                        Note: Only PNG, JPG files accepted.</p>

                                                                                </div>
                                                                                <!-- </form> -->
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <!-- End Update Banner Image Modal -->


                                                                
                                                                
                                                                
                                                            </div>

                                                      
                                                        <?php }
                                                    } else { ?>
                                                       
                                                <?php }
                                                } ?>

                                                <input name="avatar_image_hidden" value="<?php echo $soreLogoId = isset($storeData['store_logo']) ? $storeData['store_logo'] : ''; ?>" id="avatar_image_hidden" type="hidden">

                                                <div class="uploadImages_store">
                                                    <span></span>
                                                </div>
                                            </div>
                                            @endif
                                            <label class="custom-file-upload mt-20 mb-20">
                                                Upload Image
                                                <input type="file" name="file_upload_banner[]" id="file_upload_banner" class="form-control" onchange="readURLBanners(this)" multiple >
                                                <input type="hidden" name="banner_image_id" id="banner_images_serilize" value="">
                                            </label>
                                            <p class="hide_image_counter"></p>
                                            <p class="ligt-grey  mb-0 fnt-12">Note: Only PNG, JPG files accepted.
                                            </p>
                                            <p id="banner_image_size_note" class="text-danger   mb-5 fnt-12">Image size should be 1180x350 (px)
                                            </p>
                                            <div id="banner_image_store" ></div>
                                             <div id="all-uplpad-banners-image" class="d-flex"></div>
                                           
                                        </div>

                                    </div>
                                    
                                </div>
                                {{-- <div class="form-group">
                                    <label>Brand Name *</label>
                                    <input type="text" class="form-control" id="store_name" name="store_name"
                                           value="<?php echo isset($storeData['store_name']) ? $storeData['store_name'] : ''; ?>">
                                    <span class="error">{{ $errors->first('store_name') }}</span>

                        </div> --}}
                        <input type="hidden" name="store_image_id" id="store_image_id" value="">

                        <div class="address">
                            <h5>Address*</h5>

                            <div class="row" style="margin-left:-10px;margin-right:-10px;">

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label style="color: #183B56;">Street</label>
                                        <input name="street_address" id="street_address" class="form-control" placeholder="Ex. 124 Birch St." value="<?php echo isset($storeData['street_address']) ? $storeData['street_address'] : ''; ?>">
                                        <span class="error"></span>

                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label style="color: #183B56;">City</label>
                                        <input type="text" name="store_city" id="store_city" class="form-control" placeholder="Ex. Chicago" value="<?php echo isset($storeData['store_city']) ? $storeData['store_city'] : ''; ?>">
                                        <small id="cityError" style="color: red; display: none;">City name should only have alphabets.</small>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="Country" style="color: #183B56;">Country</label>
                                        <select class="form-control" name="store_country" id="user_country">
                                            <!-- Optional Placeholder Option -->
                                            <option value="">Please Select Your Country</option>

                                            @php
                                                $countries = getAllCountries();
                                                $selectedCountry = $storeData['store_country'] ?? '';
                                            @endphp

                                            @foreach ($countries as $country)
                                                <option value="{{ $country['id'] }}" {{ $selectedCountry == $country['id'] ? 'selected' : '' }}>
                                                    {{ $country['name'] }}
                                                </option>
                                            @endforeach
                                        </select>

                                        <span class="error">{{ $errors->first('store_country') }}</span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        
                                        <label for="Province" style="color: #183B56;">State/Province</label>
                                        <select class="form-control" name="store_state" id="user_state">
                                            <option value="">Please Select Your State</option>
                                            @if (isset($storeData['store_country']) && $storeData['store_country'])
                                                @php
                                                    $states = getStatesOfCountry($storeData['store_country']);
                                                @endphp
                                                @foreach ($states as $state)
                                                    <option value="{{ $state['state_id'] }}" {{ $storeData['store_state'] == $state['state_id'] ? 'selected' : '' }}>
                                                        {{ ucfirst($state['state_name']) }}
                                                    </option>
                                                @endforeach
                                            @endif
                                        </select>
                                        <span class="error">{{ $errors->first('store_state') }}</span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label style="color: #183B56;">Zip/Postal Code</label>
                                        <input type="text" name="zip_code" class="form-control" placeholder="Ex. 00012" id="zip_code" maxlength="5" value="<?php echo isset($storeData['zip_code']) ? $storeData['zip_code'] : ''; ?>">
                                        <small id="zipError" style="color: red; display: none;">Zip code should only have 5 numbers.</small>
                                        <span class="error">{{ $errors->first('store_phone') }}</span>
                                    </div>
                                </div>
                                <div class="col-md-6 same-as-shipping-btn">
                                    <!-- <button class="btn btn-primary"> Same as Shipping</button> -->
                                </div>


                            </div>


                        </div>

                        <hr  style="margin-top:15px!important;"/>
                        {{--
                                <div class="form-group">
                                    <label>Contact No</label>
                                    <input type="text" name="store_phone" class="form-control" id="store_phone"
                                           value="<?php echo isset($storeData['contact_info']) ? $storeData['contact_info'] : ''; ?>">
                                    <span class="error">{{ $errors->first('store_phone') }}</span>
                    </div> --}}






                    <br />

                    <div class="form-group website-field d-none">
                        <h5>Website</h5>
                        <!-- <input type="text" class="form-control" id="store_website" name="store_website" placeholder="https://website.com" value="<?php echo isset($storeData['store_website']) ? $storeData['store_website'] : ''; ?>"> -->
                        <!-- <span class="error">{{ $errors->first('user_name') }}</span> -->

                    </div>

                    <div class="form-group pt-3">
                        <h5 style="margin-bottom: 39px;">Shop Bio:</h5>
                        {{-- <p class="frame-in-text" style="font-size:12px;">Note: Iframe width should be 100% and height should be 450 when you are adding a video. </p> --}}
                        <textarea class="form-control" placeholder="Your store bio is the perfect opportunity to showcase your brand’s personality and values to potential customers. Use this section to introduce yourself, highlight what makes your store unique, and explain what kind of products you offer." id="about_merchant" name="about_store"><?php echo $aboutMerchant = isset($storeData['about_store']) ? $storeData['about_store'] : ''; ?></textarea>
                        <span class="error"></span>
                    </div>
                    <div class="form-group" style="width: 200%; display:none;">
                        <label>Policies </label>
                        <?php
                        $policies_status_yes = '';
                        $policies_status_no = '';
                        if (isset($storeData['policies_status']) && $storeData['policies_status'] == 'Yes') {
                            $policies_status_yes = 'checked="checked"';
                        } elseif (isset($storeData['policies_status']) && $storeData['policies_status'] == 'No') {
                            $policies_status_no = 'checked="checked"';
                        } else {
                            $policies_status_no = 'checked="checked"';
                        }
                        ?>
                        <div class="radio-policy fnt-12" style="margin-top: 25px; position: relative;">
                            <label class="radio-inline">
                                <input type="radio" name="shop_policy_radio" <?php echo $policies_status_yes; ?> style="position: relative;left: -9px;" value="Yes">
                                Add Shop Policy
                            </label>
                            <label class="radio-inline" style="margin-left: 25px;">
                                <input type="radio" name="shop_policy_radio" <?php echo $policies_status_no; ?> style="position: relative;left: -9px;" value="No">
                                Do Not Add Shop Policy
                            </label>
                        </div>
                        <span class="error"></span>
                        <div id="shop-policy" style="display: none;">

                            <?php echo !empty($storeData['policies']) ? $storeData['policies'] : ''; ?>
                        </div>
                    </div>
                    <!-- <?php // if (!isset($storeData['terms_condtions'])) {
                            ?> -->
                    <div class="form-group d-none">
                        <input type="checkbox" id="store_term_conditon" class="terms-condtions-ratio" name="store_term_conditon" value="1" <?php if (isset($storeData['terms_condtions']) && $storeData['terms_condtions'] == '1') {
                                                                                                                                                echo 'checked';
                                                                                                                                            } ?>>
                        <span class="error">{{ $errors->first('store_term_conditon') }}</span>
                        <label>I accept <a href="{{ URL::to('/terms') }}" class="bold-class">terms and
                                conditions</a>
                        </label>
                    </div>
                    <?php // }
                    ?>

                    <div class="form-check" style="padding: 0px;">
                        <p>
                            <?php
                            if ($actionUrl == 'store/edit') {
                                $text = 'Update';
                            } else {
                                $text = 'Create';
                            }
                            ?>
                            <button type="submit" id="submitButton" class="btn btn-primary width70 text-white" disabled>{{ $text }} My
                                MallShark Store</button>
                        </p>
                    </div>
                    </form>
                    <div class="col-md-5 image-col-wrapper">

                        <div class="coins-img">
                            <img src="{{ URL::asset('assets/images/coins.png') }}" alt="image" srcset="">

                        </div>

                        <div class="store-img">
                            <img src="{{ URL::asset('assets/images/store.png') }}" alt="image" srcset="">
                        </div>
                        <div class="shipping-boxes-img storeEditIMage">
                            <img src="{{ URL::asset('assets/images/shipping-boxes.png') }}" alt="image" srcset="">
                        </div>
                    </div>
                </div>
                <!-- User Form Ends -->
            </div>

        </div>
    </div>

    </div>
    </div>
</main>
<!-- <div id="store-logo-popup" class="modal fade popup-style create-shipping-profile">
    <div class="modal-dialog"> -->
<!-- Modal content-->
<!-- <div class="modal-content clearfix">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Brand Logo</h4>
            </div>
            <div class="modal-body">
                <div class="input-container-div">
                    <div class="input-label"> -->
<!-- <label for="image_upload" class="label">Upload image:<br>Allowed image format
                            (<strong>png</strong>) -->
<!-- </label>
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
</div> -->

<div class="modal showproductmodal" id="showBannersmodal" tabindex="-1" role="dialog" st>
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Select Variation Image</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p class="variation_image_text_note">The selected image will be assigned after all required fields have been filled and the changes have been saved/updated by clicking on the button at the bottom.</p>
                <div id="showImagePopup_modal" class="showImagePopup_modal">

                     
                   
                </div>

            </div>
        </div>
    </div>

</div>

<div id="loading" class="show-setion">
        <img id="loading-image" src="{{asset('assets/images/Loader-Animation.gif')}}" alt="Loading..." />
</div>
<link href="{{ URL::asset('assets/image-cropper/css/cropper.css', env('HTTPS_STATUS')) }}" rel="stylesheet" type="text/css" media="screen" />
<link href="{{ URL::asset('assets/image-cropper/css/main.css', env('HTTPS_STATUS')) }}" rel="stylesheet" type="text/css" media="screen" />
<script src="{{ URL::asset('assets/image-cropper/js/cropper.min.js', env('HTTPS_STATUS')) }}" type="text/javascript" charset="utf-8"></script>
<script src="{{ URL::asset('assets/js/jquery.prettyPhoto.js',env('HTTPS_STATUS')) }}"></script>
<div id="user-profile-popup" class="modal fade popup-style create-shipping-profile">

</div>
<script>
var cityInput = document.getElementById('store_city');
    var zipInput = document.getElementById('zip_code');
    var submitButton = document.getElementById('submitButton');
    var cityError = document.getElementById('cityError');
    var zipError = document.getElementById('zipError');

    // Function to validate city and zip code
    function validateInputs() {
        var cityValue = cityInput.value;
        var zipValue = zipInput.value;

        var isCityValid = /^[a-zA-Z\s]+$/.test(cityValue);  // Only letters and spaces
        var isZipValid = /^[0-9]{5}$/.test(zipValue);  // Only 5 digits

        // Display errors and enable/disable button accordingly
        if (!isCityValid) {
            cityError.style.display = 'block';
        } else {
            cityError.style.display = 'none';
        }

        if (!isZipValid) {
            zipError.style.display = 'block';
        } else {
            zipError.style.display = 'none';
        }

        // Enable submit button only if both city and zip are valid
        submitButton.disabled = !(isCityValid && isZipValid);
    }

    // Listen for input changes on both fields
    cityInput.addEventListener('input', validateInputs);
    zipInput.addEventListener('input', validateInputs);

    // Initial validation on page load
    validateInputs();
  var fileInput = $('#file_upload');

  
  fileInput.on('click', function() {
        $(this).val('');
        $('#user-profile-popup').empty();
      fileInput.on('change', function() {
        var currentFile = fileInput[0].files[0];

        var req_width = 350, req_height = 350; 
    $(".upload_image_error").html("");
    $('#user-profile-popup').empty();
    var file = currentFile;
    var filename = $('#file_upload').val();
    var ext = filename.split('.').pop();
    var allowed_extensions = ['png', 'PNG', 'jpg', 'JPG', 'JPEG', 'jpeg'];
    if ($.inArray(ext, allowed_extensions) == -1) {
        $('#file_upload_store').append("<div class='upload_image_error'>Invalid Image type.</div>");
        swal('Please select only .jpg, .jpeg, or .png image files.');
        return false;
    }
    console.log(file);
    if (file) {
        var vol_size = file.size;
        if (vol_size <= 0) {
            vol_size = 1;
        }
        vol_size = Math.floor(vol_size / 1024); // Using Math.floor to round down
        console.log("vol_size " + vol_size);
        if (vol_size < 20481) {
            console.log("called");
            var reader = new FileReader();

            reader.onloadend = function (e) {
                var image = new Image();
                image.src = e.target.result;
                image.onload = function () {
                    var dWidth = this.naturalWidth,
                        dHeight = this.naturalHeight;

                    if (dWidth >= req_width && dHeight >= req_height) {
                        // Process the image because dimensions are valid
                        $('#user-profile-popup').empty();
                        cropper_store(e.target.result);
                        var options = {
                            aspectRatio: 35 / 35,
                            crop: function (e) {
                                // Your crop function logic here
                            }
                        };
                        $('body').find('.storecropperimage').on({
                            'build.cropper': function (e) {
                                console.log(e.type);
                            },
                            'built.cropper': function (e) {
                                console.log(e.type);
                            },
                            'cropstart.cropper': function (e) {
                                console.log(e.type, e.action);
                            },
                            'cropmove.cropper': function (e) {
                                console.log(e.type, e.action);
                            },
                            'cropend.cropper': function (e) {
                                console.log(e.type, e.action);
                            },
                            'crop.cropper': function (e) {
                                console.log(e.type, e.x, e.y, e.width, e.height, e.rotate, e.scaleX, e.scaleY);
                            },
                            'zoom.cropper': function (e) {
                                e.preventDefault(); // Prevent zoom out
                                console.log(e.type, e.ratio);
                            }
                        }).cropper(options);
                        // Rest of your cropper setup

                        $('#user-profile-popup').modal('show');
                        setTimeout(function () {
                            $(window).trigger('resize');
                            $('body').find(".docs-toggles .btn-primary").trigger("click");
                        }, 200);
                    } else {
                        swal('Your Image size is (' + dWidth + 'px x ' + dHeight + 'px). Please set dimensions to (' + req_width + 'px x ' + req_height + 'px) ');
                        // Clear the file input field since dimensions are invalid
                        $('#file_upload').val('');
                    }
                };
            };
            reader.readAsDataURL(file);
        } else {
            alert('Your Image size is ' + vol_size + 'kb. Max allowed size is 20MB.');
        }
    } else {
        swal("Sorry - your browser doesn't support the FileReader API");
    }

    
    });

    var file = fileInput;
   

  });

     $('#loading').hide();
    $(document).ready(function() {
        if ($("input[type=radio][name='shop_policy_radio']:checked").val() == 'Yes') {
            $('#shop-policy').show();
        } else {
            $('#shop-policy').hide();
        }
        $('input:radio[name="shop_policy_radio"]').change(function() {
            // checks that the clicked radio button is the one of value 'Yes'
            // the value of the element is the one that's checked (as noted by @shef in comments)
            if ($(this).val() == 'Yes') {
                $('#shop-policy').show();
            } else {
                $('#shop-policy').hide();
            }
        });
    });
    CKEDITOR.replace('about_merchant');
    CKEDITOR.config.allowedContent = true;
    // $(document).ready(function() {
    //     // $("#user_country").on("change", function() {
    //         // countryId = $(this).val();
    //         countryId = 1;
    //         data = {};
    //         data['country_id'] = countryId;
    //         sendreq(data, 'buyer', 'state', 'getStates');
    //     // });
    // });

    // function getStates(data) {
    //     $("#user_state").html(data);
    // }
    // $("#user_state").on("change", function() {
    //     var stateId = $("#user_state").val();
    //     if (stateId.length <= 0) {
    //         $(".hide-city").hide();
    //     } else {
    //         $(".hide-city").show();
    //     }
    // });

    // function getStates(data) {
    //     $("#user_state").html(data);
    // }
    // $("#user_state").on("change", function() {
    //     var stateId = $("#user_state").val();
    //     if (stateId.length <= 0) {
    //         $(".hide-city").hide();
    //     } else {
    //         $(".hide-city").show();
    //     }
    // });

    // $('#file_upload_banner').on('change', function() {
    //     var files = $(this)[0].files;
    //     var formData = new FormData();
    //     var validFiles = true;
    //     var uploadedCount = 0; // Counter variable for uploaded images

    //     $('.hide_image_counter').show();
    //     // $('.hide_image_counter').html("You are selected "+files.length+" Image ");
    //     for (var i = 0; i < files.length; i++) {

    //         var file = files[i];
    //         var reader = new FileReader();


    //         // Create a closure to capture the 'file' variable
    //         (function(file) {
    //             // Validate file dimensions
    //             reader.onload = function(e) {
    //                 var image = new Image();
    //                 image.src = e.target.result;
    //                 image.onload = function() {
    //                     var height = this.height;
    //                     var width = this.width;

    //                     if (height < 350) {
    //                         validFiles = false;
    //                         swal("Error", "Height must not be less than 350.", "error");
    //                     }
    //                     if (width < 1180) {
    //                         validFiles = false;
    //                         swal("Error", "Width must not be less than 1180.", "error");
    //                     }

    //                     if (validFiles && uploadedCount < 4) {
    //                         // Append valid files to the FormData object
    //                         formData.append('file_upload_banner[]', file);
    //                         console.log("file", file);
    //                         uploadedCount++; // Increment the uploaded count

    //                         if (uploadedCount === 4) {
    //                             $('#file_upload_banner').closest(".form-group").append("<div class='upload_image_error'>You can only upload up to 3 images.</div>");
    //                             swal("Error", "You have uploaded the maximum of three images.", "error");
    //                             return false;
    //                         }

    //                         // Check if all files have been validated
    //                         if (uploadedCount === files.length) {
    //                             // Send AJAX request
    //                             $.ajax({
    //                                 url: '/storebanner', // Replace with the actual URL to uploadUserImg() function
    //                                 type: 'POST',
    //                                 data: formData,
    //                                 processData: false,
    //                                 contentType: false,
    //                                 success: function(response) {
    //                                     console.log("response", response);
    //                                     // Handle the response from the server
    //                                     $("#banner_images_serilize").val(response.data);
    //                                     var images = response.imageUrl;

    //                                     var imageUrl = '';
    //                                     // Loop through the array of images
    //                                     for (var i = 0; i < images.length; i++) {
    //                                         var imageUrl = images[i].image;
    //                                         var imageid = images[i].id;
    //                                         console.log("imageid",imageid);

    //                                         // Create a new <img> element for each image
    //                                         var imgElement = $('<img>').attr({
    //                                         src: imageUrl,
    //                                         class: 'image_thumb_'+imageid,
    //                                         style: 'width:80px; height:80px; display: block;border-radius: 20px;border: 2px solid blue;object-fit:cover'
    //                                         });

    //                                         // Create the image actions container
    //                                         var imageActions = $('<div>').addClass('image-actions d-flex justify-content-center');

    //                                         // Create the edit image link
    //                                         var editLink = $('<a>').attr({
    //                                         href: 'javascript:void(0)',
    //                                         class: 'edit-image-create pl-2 pr-2',
    //                                         'data-image-id': +imageid
    //                                         }).append($('<i>').addClass('fa fa-pencil').attr('aria-hidden', 'true'));

    //                                         // Create the delete image link
    //                                         var deleteLink = $('<a>').attr({
    //                                         href: 'javascript:void(0)',
    //                                         class: 'createstore-delete-image  pl-2 pr-2  createStoreDeleteImage_'+imageid,
    //                                         'data-image-id': +imageid
    //                                         }).append($('<i>').addClass('fa fa-trash').attr('aria-hidden', 'true'));

    //                                         // Append the edit and delete links to the image actions container
    //                                         imageActions.append(editLink, deleteLink);

    //                                         // Create a container for the image and image actions
    //                                         var imageContainer = $('<div>').addClass('image-container image_thumbnail_created_'+ imageid)
    //                                         .append(imgElement, imageActions);

    //                                         // Append the image container to the thumbnails container
    //                                         $('.image-wrapper-banner').append(imageContainer);
    //                                         // Create the modal HTML
    //                                         var modalHtml = '<div class="modal fade" id="updateImage-' + imageid + '" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">' +
    //                                         '<div class="modal-dialog" role="document">' +
    //                                         '<div class="modal-content">' +
    //                                         '<div class="modal-header">' +
    //                                         '<h5 class="modal-title" id="exampleModalLabel">Update Image</h5>' +
    //                                         '<button type="button" class="close" data-dismiss="modal" aria-label="Close">' +
    //                                         '<span aria-hidden="true">&times;</span>' +
    //                                         '</button>' +
    //                                         '</div>' +
    //                                         '<div class="modal-body text-center">' +
    //                                         '<div class="form-group custom-panel-style mb-30">' +
    //                                         '<h5>Update your store banner</h5>' +
    //                                         '<label class="custom-file-upload mt-20">' +
    //                                         'Upload Image' +
    //                                         '<input type="file" name="update_upload_banner"   data-image-id="' + imageid + '" id="create_update_upload_banner-' + imageid + '" class="update_upload_banner-' + imageid + ' form-control">' +
    //                                         '<input type="hidden" name="update_banner_image_id" id="update_banner_images_serilize" value="">' +
    //                                         '</label>' +
    //                                         '<p class="ligt-grey mt-10 mb-0 fnt-12">Note: Only PNG, JPG files accepted.</p>' +
    //                                         '</div>' +
    //                                         '</div>' +
    //                                         '</div>' +
    //                                         '</div>' +
    //                                         '</div>';
    //                                         console.log(modalHtml);
    //                                         // Append the modal HTML to the imageContainer
    //                                         imageContainer.append(modalHtml);
    //                                     }

    //                                     // You can perform further actions here based on the response
    //                                 },
    //                                 error: function(xhr, status, error) {
    //                                     // Handle the error if the request fails
    //                                     console.log(error);
    //                                 }
    //                             });
    //                         }
    //                     }
    //                 };
    //             };
    //         })(file); // Pass the 'file' variable to the closure

    //         reader.readAsDataURL(file);
    //     }
    // });

    
    $("a[rel^='prettyPhoto']").prettyPhoto({
        
    });


    $(document).on('click', '.createstore-delete-image', function() {
        var imageElement = $(this).closest('.image-container'); // Get the closest image container element
        var imageID = $(this).data('image-id');
        var serialize_images = $('#banner_images_serilize').val();

        $.ajax({
            url: '/deleteBannerCreate',
            type: 'POST',
            data: {
                imageID: imageID,
                serialize_images: serialize_images
            },
            success: function(response) {

                if (response) {
                    swal("Success!", response.message, "success");
                    $('#banner_images_serilize').val(response.data);
                }
                // Hide the image container
                imageElement.hide();
            },
            error: function(xhr, status, error) {
                // Handle the error if the request fails
                console.log(error);
            }
        });
    });



    var imageID = '';
    $('.edit-image').on('click', function() {
        imageID = $(this).data('image-id');
        let filename = $(this).data('filename');
        let storeid = $(this).data('storeid');
        $('#updateImage-' + imageID).modal('show');
        console.log("imageID", imageID);


    });
    // when store create
    var imageID = '';
    $(document).on('click', '.edit-image-create', function() {

        imageID = $(this).data('image-id');
        let filename = $(this).data('filename');
        let storeid = $(this).data('storeid');
        $('#updateImage-' + imageID).modal('show');
        console.log("imageID", imageID);


    });



    // when create store



    //     // $('#update_upload_banner-'+imageID ).on('change', function() {
    //     // $(document).on('change', '#update_upload_banner-' + imageID, function() {
    //         $(document).on('change', '[id^="update_upload_banner-"]', function() {

    //     var bannerImageId  = $(this).data('image-id');
    //     if (bannerImageId === imageID) {



    //     var files = $(this)[0].files;
    //     console.log("files", files);
    //     console.log("bannerImageId", bannerImageId);
    //     var formData = new FormData();
    //     var validFiles = true;
    //     var uploadedCount = 0; // Counter variable for uploaded images
    //     debugger
    //     for (var i = 0; i < files.length; i++) {
    //         var file = files[i];
    //         var reader = new FileReader();

    //         // Validate file dimensions
    //         reader.onload = function(e) {
    //             var image = new Image();
    //             image.src = e.target.result;
    //             image.onload = function() {
    //                 var height = this.height;
    //                 var width = this.width;

    //                 if (height < 350) {
    //                     validFiles = false;
    //                     alert("Height must not be less than 350.");
    //                 }
    //                 if (width < 1180) {
    //                     validFiles = false;
    //                     alert("Width must not be less than 1180.");
    //                 }

    //                 if (validFiles && uploadedCount < 4) {
    //                     // Append valid files to the FormData object
    //                     formData.append('update_upload_banner-'+imageID, file);
    //                     console.log("file", file);
    //                     uploadedCount++; // Increment the uploaded count

    //                     if (uploadedCount === 4) {
    //                         $('#file_upload_banner').closest(".form-group").append("<div class='upload_image_error'>You can only upload up to 3 images.</div>");
    //                         alert("You have uploaded the maximum of three images.");
    //                         return false;
    //                     }

    //                     // Check if all files have been validated
    //                     if (uploadedCount === files.length) {
    //                         // Send AJAX request
    //                         $.ajax({
    //                             url: '/updatebanner', // Replace with the actual URL to uploadUserImg() function
    //                             type: 'POST',
    //                             data: formData,
    //                             processData: false,
    //                             contentType: false,
    //                             success: function(response) {
    //                                 // Handle the response from the server
    //                                 $("#banner_images_serilize").val(response.data);
    //                                 console.log("response", response);
    //                                 // You can perform further actions here based on the response
    //                             },
    //                             error: function(xhr, status, error) {
    //                                 // Handle the error if the request fails
    //                                 console.log(error);
    //                             }
    //                         });
    //                     }
    //                 }
    //             };
    //         };
    //         reader.readAsDataURL(file);
    //     }
    // }
    // });



    // $.ajax({
    //     url: '/updateBanner', // Replace with the actual URL for the deleteBanner endpoint
    //     type: 'POST',
    //     data: {
    //         imageID: imageID,
    //         filename: filename,
    //         storeid: storeid
    //     }, // Pass the image ID as a data parameter

    //     success: function(response) {
    //         // setTimeout(() => {

    //         //     swal("Success!", response, "success");
    //         //     location.reload(true);
    //         // }, 2000);
    //         // Handle the response from the server
    //         // $("#banner_images_serialize").val(response.data);
    //         // console.log("response", response);
    //         // You can perform further actions here based on the response
    //     },
    //     error: function(xhr, status, error) {
    //         // Handle the error if the request fails
    //         console.log(error);
    //     }
    // });

    $('#store-user_account a.editEventCrop.pl-2.pr-2 > i').on('click', function(){
        setTimeout(()=>{
            $('body .modal-backdrop').addClass('d-block');
        },100)
    });
    
    $(document).ready(function() {
    $('#file_upload').on('change', function() {
        var fileInput = $(this)[0];  // Get the file input element
        
        // Check if the image element already has a source
        var hasExistingImage = $('#banner_avatar_image').attr('src') !== '';

        if (fileInput.files && fileInput.files.length > 0) {
            // If an image is uploaded, change the text to light grey
            $('#logo_image_size_note').removeClass('text-danger').addClass('light-grey');
        } else if (hasExistingImage) {
            // If there's already an image, also set the text to light grey
            $('#logo_image_size_note').removeClass('text-danger').addClass('light-grey');
        } else {
            // If no image is uploaded and no existing image, revert the text back to red
            $('#logo_image_size_note').removeClass('light-grey').addClass('text-danger');
        }
    });

    // Initial check on page load
    var hasExistingImage = $('#banner_avatar_image').attr('src') !== '';
    if (hasExistingImage) {
        $('#logo_image_size_note').removeClass('text-danger').addClass('light-grey');
    }
});

$(document).ready(function() {
    // Function to update the note color based on image presence
    function updateNoteColor() {
        var fileInput = $('#file_upload_banner')[0]; // Get the file input element
        var imageSrc = $('img.store-banner-image').attr('src'); // Get the image source

        // Check if the image source is empty or placeholder-like
        var hasExistingImage = imageSrc && imageSrc.trim() !== '' && !imageSrc.includes('placeholder');

        // Change color based on the image preview or file input
        if ((fileInput.files && fileInput.files.length > 0) || hasExistingImage) {
            $('#banner_image_size_note').removeClass('text-danger').addClass('light-grey');
        } else {
            $('#banner_image_size_note').removeClass('light-grey').addClass('text-danger');
        }
    }

    // Check on input file change
    $('#file_upload_banner').on('change', function() {
        updateNoteColor();
    });

    // Initial check on page load
    updateNoteColor();

    // Delete image functionality
    $('.delete-banner-image').on('click', function() {
        // Clear the image source and file input
        $('img.store-banner-image').attr('src', ''); // Clear the image preview
        $('#file_upload_banner').val(''); // Clear the input field

        // Change the text color back to red
        updateNoteColor();
    });
});




</script>
<script>
    $(document).ready(function () {
        $('#user_country').on('change', function () {
            const countryId = $(this).val(); // Get the selected country ID
            const stateDropdown = $('#user_state'); // Reference the state dropdown

            // Clear existing state options
            stateDropdown.empty();
            stateDropdown.append('<option value="">Please Select Your State</option>');

            if (countryId) {
                // AJAX POST request to fetch states based on selected country
                $.ajax({
                    url: '/buyer/state', // POST request to the appropriate route
                    type: 'POST',
                    data: {
                        country_id: countryId,
                        _token: '{{ csrf_token() }}' // Include CSRF token for security
                    },
                    success: function (response) {
                        if (response) {
                            // Append the received HTML (state options) to the state dropdown
                            stateDropdown.append(response);
                        } else {
                            stateDropdown.append('<option value="">No states available</option>');
                        }
                    },
                    error: function () {
                        alert('Unable to fetch states. Please try again.');
                    }
                });
            }
        });
    });
</script>
@endsection