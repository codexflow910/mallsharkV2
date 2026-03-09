@extends('layouts.dashboard-main')
<!-- Main Banner Starts -->
@section('content')
@section('title', 'Add product')
<!-- Content Starts -->
<?php
$productType = isset($prodcutDetails['product_type']) ? $prodcutDetails['product_type'] : '';
$prodcutTitle = isset($prodcutDetails['title']) ? $prodcutDetails['title'] : '';
?>
<main>

<div class="flex-parent-div ">
    <div class="change-password-page-cmt">
        @include('buyer.customsidebar')
    </div>

    
<div class="home-section cm-edit-ja addproductsidebar no-flex-div">
    <div class="container">
    @include('Store.sidebar')
</div>

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
    @if ($errors->any())
    <div class="alert alert-danger" role="alert">
        <strong>Oh snap!</strong> Change a few things up and try submitting again.
    </div>
    @endif
    <div class="alert alert-danger errorpage fade" role="alert" style="display: none">
        <strong><i class="fa fa-exclamation-triangle" aria-hidden="true"></i> </strong> Fill out all of the required
        fields correctly in order to preview or save
    </div>
    <!-- Dashboard Ends -->
    <div class="dshbrdin mobile-top-spacing-less addProductpageWrapper ">



        <div class="container">
            {{-- <div class="progress">
                <div class="progress-bar bg-primary" role="progressbar" style="width: 70%; background-color: #0510fa; border-radius: 30px;"></div>
            </div> --}}


            <!-- hide-when-preview -->
            <div class="row">
                <div class="col-md-12">
               
                    <div class="d-flex mobile-section-div justify-content-between align-items-center mt-2 pt-4 pb-3">
                        <div class="hd-bt">
                        <h1>
                        <div class="d-flex align-items-center gap-3">                        
                                    <a href="javascript:void(0)" class="d-flex align-items-center gap-10px sidebar-admin-burger-menu" id="open-sidebar-admin-panel">                                        
                                    <span id="menu-toggle-dashboard" class="shopzit-menu mt-0 ml-0 bx-menu">
                                        <span></span><span></span><span></span> <span></span>
                                    </span>
                                    <!-- <span>Menu</span> -->
                                    </a>
                                    <span> Add Product </span>
                                    </div>    
            
                           </h1>
                        </div>
                        <div class="add-pr-lnk">

                            <div class="btn-group mobile-dropdown">
                                <button type="button" class="btn btn-primary dropdown-toggle product-import-btn" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                  Import
                                </button>
                                <div class="dropdown-menu ">
                                    <a class="btn btn-primary small-btn-mbt bigIcon shopify-btn-add addPWoCommerceBtn" href="{{ URL::to('/woocommerce') }}">
                                        <img src="{{ URL::asset('assets/images/WooCommerce.png') }}" class="shopify-icon-image" alt="image" srcset="" style="margin-top: 2px;"> WooCommerce Import
                                     </a>
                                    <a class="btn btn-primary small-btn-mbt bigIcon shopify-btn-add" href="{{URL::to('/importshopify')}}">
                                        <img src="{{ URL::asset('assets/images/Shopify-Logo.png') }}" class="shopify-icon-image" alt="image" srcset=""> Shopify Import
                                     </a>
                                </div>
                              </div>
                            <a class="btn btn-primary small-btn-mbt bigIcon shopify-btn-add addPWoCommerceBtn" href="{{ URL::to('/woocommerce') }}">
                                <img src="{{ URL::asset('assets/images/WooCommerce.png') }}" class="shopify-icon-image" alt="image" srcset="" style="margin-top: 2px;"> WooCommerce Import
                             </a>
                            <a class="btn btn-primary small-btn-mbt bigIcon shopify-btn-add" href="{{URL::to('/importshopify')}}">
                                <img src="{{ URL::asset('assets/images/Shopify-Logo.png') }}" class="shopify-icon-image" alt="image" srcset=""> Shopify Import
                             </a>
                            <a class="btn btn-primary btn-simple mobile-back-to-dashboard" href="{{URL::to('/store-dashboard')}}">
                               Back to Dashboard
                            </a>


                        </div>

                    </div>
                </div>
            </div>
        </div>

        <!--<button class="btn btn-primary btn-simple loadShippingOption" id="loadShippingOption" type="button" >Shipping Options</button>-->
        <form id="addProductForm" action="{{ url('add-product') }}" method="post" enctype="multipart/form-data">
            <!-- hide-when-preview -->
            <!-- Add Product Section Starts -->
            <div class="addprd mt-10 ">

                <div class="container">
                    <div class="container-outrer">

                        <div class="row">
                            <div class="col-md-7 asdfsfa">
                                <div class="row">
                                    <div class="col-md-12">
                                        <h6>Product Name *</h6>
                                        <div class="rghtexp bottomSpacing">
                                            <!--<label class="labled-item">Product Title *</label>-->
                                            <div class="form-group width538">
                                                <input type="text" class="form-control" id="productTitle" placeholder="Include keywords that buyers would use to search for your item." name="productTitle" value="<?php echo $prodcutTitle; ?>" onkeypress='alphaNumvalidate(event, "productTitle", 100)' maxlength="100" minlength="15" placeholder="">
                                            </div>
                                        </div>
                                        <h6>Category *
                                        </h6>
                                        <div class="form-group">

                                            <select class="form-control" id="productMainCategory" name="productCategory[]">

                                                <?php
                                                if (isset($parentCategory) && is_array($parentCategory) && !empty($parentCategory)) {
                                                    echo '<option value="0">Select Category</option>';
                                                    foreach ($parentCategory as $key => $value) {
                                                        echo '<option value="' . $value['id'] . '">' . $value['categoryTitle'] . '</option>';
                                                    }
                                                } else {
                                                    echo '<option value="0">No category found</option>';
                                                }
                                                ?>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-md-12 asfsafsfsf">
                                        <div class="form-group" id="subcategory" style="display: none">

                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="form-group" id="childcategory" style="display: none">

                                        </div>
                                    </div>
                                    <div class="col-md-12 rghtexp bottomSpacing">
                                        <!--<label class="labled-item"></label>-->
                                        <div class="col-md-12-hh ">
                                            <h6>Product Type *
                                            </h6>
                                            <div class="form-group">
                                                <select class="form-control" id="Product-type-variant" name="product_type">
                                                    <!-- <option value="default" disabled selected="">Select product type if its simple or variable (color, size etc)</option> -->
                                                    <option value="simple">Simple</option>
                                                    <option value="variant">Variable</option>
                                                    <option value="virtual">Virtual</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-12-dfdf rghtexp variant colors mt-5 hide-me">
                                            @include('product.productvariations')
                                        </div>
                                    </div>

                                </div>
                            </div>
                            <div class="col-md-5">
                         
                            </div>

                            

                        </div>
                        <div class="row">
                            <div class="col-sm-12 lftexp">

                                <p style="display:none;">Choose appropriate Category for your product</p>
                            </div>





                        </div>



                        @php
                                $class = 'hide-me';
                                $wclss = '';
                                if($productType == 'virtual') {
                                $class = '';
                                $wclss = 'hide-me';
                                }
                                @endphp
                                <div class="col-md-7 rghtexp virtual colors mt-5 {{$class}}">
                                    <div class="row photoSection">
                                        <div class="col-md-12 lftexp ">
                                            <h6>Product Files *</h6>
                                            <p>
                                                (Include all files) Use up to five files to show your item's most important</p>

                                        </div>
                                        <div class="col-md-12 rghtexp ">
                                            <div class="justify-content-between row">
                                                <!--<label class="labled-item col-sm-6"></label>-->
                                                <span class="col-sm-6 text-left ligt-grey fnt-12  mobile-text-right-none" style="font-size:11px">Note: You can upload multiple files</span>
                                            </div>

                                            <div class="form-group files-custom-panel-style mb-20">

                                                <!-- <input type="file" name="file_upload" id="file_upload" class="form-control" onchange="readFile(this)"> -->
                                                <input type="file" accept="image/png,image/jpeg,image/jpg,application/pdf" multiple="" id="product_files" name="product_files[]" class="form-control upload-file-input">
                                                </label>
                                                <p class="ligt-grey mt-10 mb-0 fnt-12">Note: Only jpeg or png, pdf files can be uploaded</p>
                                                <p class="ligt-grey mt-10 mb-0 fnt-12" id="imageUploadCounter"></p>
                                                <input type="hidden" name="feature_image" class="feature_image" id="feature_image" value="">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                @if(isset($prodcutDetails['digital_files']) && !empty($prodcutDetails['digital_files']))


                                @foreach ($prodcutDetails['digital_files'] as $files)
                                <div class="image-panel uploaded-images-section">
                                    @php

                                    $imageUrl = URL::asset('uploads/digital/'.$files->file_name);
                                    $ext = pathinfo($files->file_name, PATHINFO_EXTENSION);
                                    if($ext == 'pdf') {
                                    $imageUrl = URL::asset('assets/images/pdf.svg');
                                    }
                                    $fileName = !empty($files->orignal_file_name) ? $files->orignal_file_name :'';
                                    @endphp

                                    <div class="image-inner-mbt">
                                        <div class="img-only">
                                            <a class="viewImages" rel="prettyPhoto" href="<?php echo $imageUrl; ?>">
                                                <img class="previewClass" src="<?php echo $imageUrl; ?>" alt="{{$prodcutTitle}}" title="{{$prodcutTitle}}">
                                                <p> {{$fileName}}</p>
                                            </a>
                                        </div>
                                        <div class="loading-image">
                                            <span></span>
                                        </div>
                                        <input id="imagesrc70009280520" type="hidden" value="<?php echo $files->id; ?>" name="file_ids[]">
                                        <div class="dele-icn">
                                            <a href="javascript:;" data-file-ids="{{ $files->id}}" class=" product_file_delete">
                                                <i class="fa fa-trash-o" aria-hidden="true"></i>
                                            </a>
                                        </div>

                                    </div>
                                </div>
                                @endforeach
                                <input type="hidden" value="" id="delete_file_ids" name="delete_file_ids">
                                @endif





                        <input type="hidden" name="edit_product_type" value="{{$productType}}" />
                        <div class="row photoSection">
                            <div class="col-md-7 lftexp ">
                                <h6>Product Images </h6>
                                <i class="fa fa-info-circle tooltip-icon"></i>
                                <div class="tooltip-text">
                                    <p class="photoNotification">Upload Gallery and Variation Images: Gallery images display your product from various angles, while variation images show different options like colors or sizes.
                                        <br>
                                        Tips: <br>Use natural light and no flash. Include a common object for scale. Show the item being held, worn,
                                        or used.
                                        Shoot against a clean, simple background.
                                    </p>
                                </div>

                   
                            </div>
                            <div class="col-md-5 productThumbnail-tool">
                            <h6>Product Thumbnail </h6>
                                 <i class="fa fa-info-circle tooltip-icon"></i>
                                <div class="tooltip-text">
                                    <p class="photoNotification">The product thumbnail image for display in listings and search results.</p>
                                </div>
                           
                    </div>
                            <div class="col-md-7 rghtexp ">
                                <div class="justify-content-between row" style="display:none;">
                                    <!--<label class="labled-item col-sm-6"></label>-->
                                    <span class="col-sm-6 text-left ligt-grey fnt-12  mobile-text-right-none" style="font-size:11px">Note: You can upload multiple files</span>
                                </div>

                                <div class="form-group custom-panel-style mb-20">
                                    <label class="custom-file-upload mt-20">
                                        Upload Images 
                                        <!-- <input type="file" name="file_upload" id="file_upload" class="form-control" onchange="readFile(this)"> -->
                                        <input type="file"  multiple id="productImage" onchange="readURL(this)" class="">
                                    </label>
                                    <p class="ligt-grey mt-10 mb-0 fnt-12">Note: Only jpeg or png, files can be uploaded</p>
                                    <p class="ligt-grey mt-10 mb-0 fnt-12" id="imageUploadCounter"></p>
                                    <input type="hidden" name="feature_image" class="feature_image" id="feature_image" value="">
                                </div>
                            </div>
                            <div class="col-md-5 mb-20 d-flex align-items-center justify-content-start">
                           <!-- Button trigger modal -->
                         <!-- Button trigger modal -->
                        <button type="button" class="btn btn-primary-back selectFeatureImageAdd" >Set Thumbnail </button>

                            <div class="set-feature-image ">
                               <!-- <img src="" alt="Thumbnail" class="d-none product-thumbnail-image" >
                               <a class="featureaddCropperEvent editEvent d-none" href="javascript:;" data-toggle="modal" data-target=""><i class="fa fa-crop" aria-hidden="true"></i></a> -->
                               <div id="showFeatureImagePopup"></div>
                            </div>
                            
                            </div>
                            <div class="col-md-12 rghtexp mt-0">

                                <div class="uploadImages"><span></span></div>
                                <div class="row">

                                    <div class="modal showproductmodal" id="showproductmodal" tabindex="-1" role="dialog" st>
                                        <div class="modal-dialog modal-lg" role="document">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Select Variation Image</h5>
                                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                        <span aria-hidden="true">&times;</span>
                                                    </button>
                                                </div>
                                                <div class="modal-body">
                                                <p class="variation_image_text_note">The selected image will be assigned after all required fields have been filled and the changes have been saved/updated  by clicking on the button at the bottom.</p>
                                                    <div id="showImagePopup_modal" class="showImagePopup_modal">
                                                        <?php
                                                        $imageCount = 0;
                                                       
                                                        if (!empty($prodcutDetails['gallery'])) {

                                                            $counterGallery = rand(9999, 999999999);
                                                            foreach ($prodcutDetails['gallery'] as $imageID) {
                                                                $imageGalleryUrl = getAttachmentUrlById((int) $imageID);
                                                            ?>
                                                                <div id="img_<?php echo $counterGallery; ?>" class="image-panel second_class noman-second-class">
                                                                    <div class="image-inner-mbt">
                                                                        <div class="img-only">

                                                                            <img class="previewClass" src="<?php echo $imageGalleryUrl; ?>" alt="{{$prodcutTitle}}" title="{{$prodcutTitle}}">

                                                                        </div>
                                                                        <div class="loading-image">
                                                                            <span></span>
                                                                        </div>
                                                                        <input id="imagesrc<?php echo $counterGallery; ?>" type="hidden" value="<?php echo (int) $imageID; ?>" name="variation_image">
                                                                        <div class="dele-icn">
                                                                            <input type="radio" class="form-check-input select_variation_image" data-image="product_variation_image_<?php echo (int) $imageID; ?>" name="variation_image" value="<?php echo (int) $imageID; ?>">Select


                                                                        </div>
                                                                        <div class="modal" id="cropper_<?php echo $counterGallery; ?>" tabindex="-1" role="dialog" aria-hidden="true">
                                                                            <div class="modal-dialog animated bounceInDown" style="max-width:800px">
                                                                                <div class="modal-content">
                                                                                    <div class="modal-body">
                                                                                        <div class="row imagecrop-area">
                                                                                            <div class="col-xs-12 col-sm-12">
                                                                                                <div class="img-container">
                                                                                                    <img class="Cropperimage" src="<?php echo $imageGalleryUrl; ?>" alt="{{$prodcutTitle}}" title="{{$prodcutTitle}}">
                                                                                                </div>
                                                                                                <div class="clearfix"></div>
                                                                                                <br>
                                                                                            </div>
                                                                                            <div class="clearfix spacer20"></div>
                                                                                            <div class="col-md-12 docs-toggles"></div>

                                                                                        </div>

                                                                                    </div>
                                                                                    <div class="modal-footer">
                                                                                        <button data-dismiss="modal" class="btn btn-default btn btn-primary btn-simple" type="button">Close
                                                                                        </button>
                                                                                        <button class="btn btn-success getCropImage btn btn-primary" type="button" data-dismiss="modal">Save
                                                                                        </button>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                        <?php
                                                                $counterGallery++;
                                                            }
                                                        }
                                                        ?>
                                                    </div>
                             
                                                </div>
                                            </div>
                                        </div>

                                    </div>
                                    
                                    <!-- Product Thumbnail (feature iamge) -->
                                    <div class="modal showproductFeaturemodal" id="showproductFeaturemodal" tabindex="-1" role="dialog" st>
                                        <div class="modal-dialog modal-lg" role="document">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Select Thumbnail Image</h5>
                                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                        <span aria-hidden="true">&times;</span>
                                                    </button>
                                                </div>
                                                <div class="modal-body">
                                               
                                                    <div id="showFeatureImagePopup_modal" class="showFeatureImagePopup_modal">
                                                        <?php
                                                        $imageCount = 0;
                                                       
                                                        if (!empty($prodcutDetails['gallery'])) {

                                                            $counterGallery = rand(9999, 999999999);
                                                            foreach ($prodcutDetails['gallery'] as $imageID) {
                                                                $imageGalleryUrl = getAttachmentUrlById((int) $imageID);
                                                            ?>
                                                                <div id="img_<?php echo $counterGallery; ?>" class="image-panel second_class noman-second-class">
                                                                    <div class="image-inner-mbt">
                                                                        <div class="img-only">

                                                                            <img class="previewClass" src="<?php echo $imageGalleryUrl; ?>" alt="{{$prodcutTitle}}" title="{{$prodcutTitle}}">

                                                                        </div>
                                                                        <div class="loading-image">
                                                                            <span></span>
                                                                        </div>
                                                                        <input id="imagesrc<?php echo $counterGallery; ?>" type="hidden" value="<?php echo (int) $imageID; ?>" name="variation_image">
                                                                        <div class="dele-icn">
                                                                            <input type="radio" class="form-check-input select_variation_image" data-image="product_variation_image_<?php echo (int) $imageID; ?>" name="variation_image" value="<?php echo (int) $imageID; ?>">Select


                                                                        </div>
                                                                        <div class="modal" id="cropper_<?php echo $counterGallery; ?>" tabindex="-1" role="dialog" aria-hidden="true">
                                                                            <div class="modal-dialog animated bounceInDown" style="max-width:800px">
                                                                                <div class="modal-content">
                                                                                    <div class="modal-body">
                                                                                        <div class="row imagecrop-area">
                                                                                            <div class="col-xs-12 col-sm-12">
                                                                                                <div class="img-container">
                                                                                                    <img class="Cropperimage" src="<?php echo $imageGalleryUrl; ?>" alt="{{$prodcutTitle}}" title="{{$prodcutTitle}}">
                                                                                                </div>
                                                                                                <div class="clearfix"></div>
                                                                                                <br>
                                                                                            </div>
                                                                                            <div class="clearfix spacer20"></div>
                                                                                            <div class="col-md-12 docs-toggles"></div>

                                                                                        </div>

                                                                                    </div>
                                                                                    <div class="modal-footer">
                                                                                        <button data-dismiss="modal" class="btn btn-default btn btn-primary btn-simple" type="button">Close
                                                                                        </button>
                                                                                        <button class="btn btn-success getCropImage btn btn-primary" type="button" data-dismiss="modal">Save
                                                                                        </button>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                        <?php
                                                                $counterGallery++;
                                                            }
                                                        }
                                                        ?>
                                                    </div>
                             
                                                </div>
                                            </div>
                                        </div>

                                    </div>
                                    <div id="all-uplpad-image" class="noman_class">

                                        <?php
                                        $imageCount = 0;
                                        $featureValidator = '';
                                        if (isset($prodcutDetails) && is_array($prodcutDetails)) {
                                            //echo 'Data: <pre>' .print_r($prodcutDetails,true). '</pre>'; die;
                                            $counterFeatureImage = rand(9999, 999999999);
                                            if ($prodcutDetails['featureImage']) {
                                                $imageUrl = getAttachmentUrlById((int) $prodcutDetails['featureImage']);
                                                if ($imageUrl) {
                                                    $featureValidator = 'style="display: none;"';
                                                }
                                        ?>
                                                <div id="img_<?php echo $counterFeatureImage; ?>" class="image-panel ">
                                                    <div class="image-inner-mbt">
                                                        <div class="img-only">
                                                            <a class="viewImages" rel="prettyPhoto" href="<?php echo $imageUrl; ?>">
                                                                <img class="previewClass" src="<?php echo $imageUrl; ?>" alt="{{$prodcutTitle}}" title="{{$prodcutTitle}}">
                                                            </a>
                                                        </div>
                                                        <div class="loading-image">
                                                            <span></span>
                                                        </div>
                                                        <input id="imagesrc<?php echo $counterFeatureImage; ?>" type="hidden" value="<?php echo (int) $prodcutDetails['featureImage']; ?>" name="productImage[]">
                                                        <div class="dele-icn">
                                                            <a class="editEvent" href="javascript:;" data-toggle="modal" data-target="#cropper_<?php echo $counterFeatureImage; ?>"><i class="fa fa-crop" aria-hidden="true"></i></a><a href="javascript:;" class="dete-parent" onclick="deleteImage(<?php echo $counterFeatureImage; ?>)"><i class="fa fa-trash-o" aria-hidden="true"></i></a>
                                                        </div>
                                                        <div class="modal" id="cropper_<?php echo $counterFeatureImage; ?>" tabindex="-1" role="dialog" aria-hidden="true">
                                                            <div class="modal-dialog animated bounceInDown" style="max-width:800px">
                                                                <div class="modal-content">
                                                                    <div class="modal-body">
                                                                        <div class="row imagecrop-area">
                                                                            <div class="col-xs-12 col-sm-12">
                                                                                <div class="img-container">
                                                                                    <img class="Cropperimage" src="<?php echo $imageUrl; ?>" alt="{{$prodcutTitle}}" title="{{$prodcutTitle}}">
                                                                                </div>
                                                                                <div class="clearfix"></div>
                                                                                <br>
                                                                            </div>
                                                                            <div class="clearfix spacer20"></div>
                                                                            <div class="col-md-12 docs-toggles"></div>

                                                                        </div>

                                                                    </div>
                                                                    <div class="modal-footer">
                                                                        <button data-dismiss="modal" class="btn btn-default btn btn-primary btn-simple" type="button">Close
                                                                        </button>
                                                                        <button class="btn btn-success getCropImage btn btn-primary" type="button" data-dismiss="modal">Save
                                                                        </button>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php
                                            }
                                        }

                                        if (!empty($prodcutDetails['gallery'])) {

                                            $counterGallery = rand(9999, 999999999);
                                            foreach ($prodcutDetails['gallery'] as $imageID) {
                                                $imageGalleryUrl = getAttachmentUrlById((int) $imageID);
                                            ?>
                                                <div id="img_<?php echo $counterGallery; ?>" class="image-panel second_class">
                                                    <div class="image-inner-mbt">
                                                        <div class="img-only">
                                                            <a class="viewImages" rel="prettyPhoto" href="<?php echo $imageGalleryUrl; ?>">
                                                                <img class="previewClass" src="<?php echo $imageGalleryUrl; ?>" alt="{{$prodcutTitle}}" title="{{$prodcutTitle}}">
                                                            </a>
                                                        </div>
                                                        <div class="loading-image">
                                                            <span></span>
                                                        </div>
                                                        <input id="imagesrc<?php echo $counterGallery; ?>" type="hidden" value="<?php echo (int) $imageID; ?>" name="productImage[]">
                                                        <div class="dele-icn">
                                                            <a class="editEvent" href="javascript:;"data-toggle="modal" data-target="#cropper_<?php echo $counterGallery;?>">
                                                                <i class="fa fa-crop" aria-hidden="true"></i>
                                                            </a><a href="javascript:;" class="dete-parent" onclick="deleteImage(<?php echo $counterGallery; ?>)">
                                                                <i class="fa fa-trash-o" aria-hidden="true"></i>
                                                            </a>
                                                        </div>
                                                        <div class="modal" id="cropper_<?php echo $counterGallery; ?>" tabindex="-1" role="dialog" aria-hidden="true">
                                                            <div class="modal-dialog animated bounceInDown" style="max-width:800px">
                                                                <div class="modal-content">
                                                                    <div class="modal-body">
                                                                        <div class="row imagecrop-area">
                                                                            <div class="col-xs-12 col-sm-12">
                                                                                <div class="img-container">
                                                                                    <img class="Cropperimage" src="<?php echo $imageGalleryUrl; ?>" alt="{{$prodcutTitle}}" title="{{$prodcutTitle}}">
                                                                                </div>
                                                                                <div class="clearfix"></div>
                                                                                <br>
                                                                            </div>
                                                                            <div class="clearfix spacer20"></div>
                                                                            <div class="col-md-12 docs-toggles"></div>

                                                                        </div>

                                                                    </div>
                                                                    <div class="modal-footer">
                                                                        <button data-dismiss="modal" class="btn btn-default btn btn-primary btn-simple" type="button">Close
                                                                        </button>
                                                                        <button class="btn btn-success getCropImage btn btn-primary" type="button" data-dismiss="modal">Save
                                                                        </button>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                        <?php
                                                $counterGallery++;
                                            }
                                        }
                                        ?>


                                    </div>

                                </div>
                            </div>      

                        </div>
                                                    

                        @include('product.attributes')
                        <div class="row">
                            <div class="card prdsec shippingTab w-100 col-md-12">
                                <div class="card-header" role="tab" id="" style="display:none">
                                    <!--                                <h5 class="mb-0">
                                                                    <a class="collapsed" data-toggle="collapse" href="#collapsefive" aria-expanded="false" aria-controls="collapseThree">
                                                                        Shipping
                                                                        <i class="fa fa-plus pull-right" aria-hidden="true"></i> 
                                                                        <i class="fa fa-minus pull-right" aria-hidden="true"></i>   
                                                                    </a>
                                                                </h5>-->
                                </div>
                                <!--   <div id="collapsefive" class="panel-collapse  collapse show" role="tabpanel" aria-labelledby="headingfive" data-parent="#accordion">-->
                                <div class="card-body ">

                                    <div class="row ">
                                        <div class="col-sm-12 lftexp">
                                            <h6>Shipping Options
                                            </h6>
                                            <p>Set clear and realistic shipping expectations for shoppers by providing
                                                accurate processing time and shipping rates.</p>

                                        </div>
                                        <div class="col-md-12 rghtexp">
                                            <div class="shipping-section-container ui-toolkit shipping-option-container">
                                                <div class="list-container-scroll">

                                                    @include('product.shipping.previousShippingOption')
                                                </div>
                                                <!-- panel-repeater-radio -->
                                                <div class="panel-repeater-radio custom-shipping-container br-top-none emphty-space-zero">
                                                </div>
                                                <!-- panel-repeater-radio -->
                                            </div>
                                        </div>
                                        <div class="addCustomShip">
                                            <a href="javascript:void(0);" id="add-shipping-profile-custom" class="defaultbtn add-shipping-profile">Enter Custom Shipping Options</a>
                                        </div>

                                        <div class="cl-bx-mbt bckred">
                                            <div class="row recomandedShippigOptionFields">


                                            </div>

                                            <!-- clculateRecommeded end -->
                                            <div class="row">
                                                <div class="col-sm-4 showon-customshipping-select cl-bx-mbt bckred"></div>
                                                <div class="col-sm-8 showon-customshipping-select cl-bx-mbt bckred">

                                                </div>
                                            </div>
                                        </div>
                                        <!--                                        <div class="col-md-12 lftexp">
                                                                                <h6>Item weight *</h6>
                                                                                <p class="small">Weight and size are required for calculated profiles. Learn more about calculated shipping.</p>     
                                                                            </div> -->
                                        <!--                                        <div class="col-md-4 rghtexp small-lable"> 
                                                                                <div class="form-group">
                                                                                    <label>lbs</label>
                                                                                    <input onkeypress='Numvalidate(event, "price", "productWeight_lbs")' type="text" class="form-control required-field" id="productWeight_lbs" name="productWeight_lbs"  placeholder="lbs">
                                                                                </div>   
                                                                            </div>-->
                                        <!--                                        <div class="col-md-4 rghtexp small-lable"> 
                                                                                <div class="form-group">
                                                                                    <label>oz</label>
                                                                                    <input onkeypress='Numvalidate(event, "price", "productWeight_oz")' type="text" class="form-control required-field" id="productWeight_oz" name="productWeight_oz"  placeholder="oz" >
                                                                                </div>   
                                                                            </div>-->

                                        <!--                                        <div class="col-md-12 lftexp">
                                                                                <h6>Item size (when packed) *</h6>
                                                                                <p class="small">Enter the weight and size of the item plus packing materials. This will be used to determine package size.</p>     
                                                                            </div> -->
                                        <!--                                        <div class="col-md-12 rghtexp"> 
                                                                                <div class="row" >
                                                            <div class="col-md-4 small-lable"> 
                                                                <div class="form-group">
                                                                    <label>Length</label>
                                                                    <input onkeypress='Numvalidate(event, "price", "productSize_length")' type="text" class="form-control required-field" id="productSize_length" name="productSize_length"  placeholder="Length" >

                                                                </div>   
                                                            </div>
                                                            <div class="col-md-4 small-lable"> 
                                                                <div class="form-group">
                                                                    <label>Width</label> 
                                                                    <input onkeypress='Numvalidate(event, "price", "productSize_width")' type="text" class="form-control required-field" id="productSize_width" name="productSize_width"  placeholder="Width" >
                                                                </div>   
                                                            </div>
                                                            <div class="col-md-4 small-lable"> 
                                                                <div class="form-group">
                                                                    <label>Height</label>   
                                                                    <input onkeypress='Numvalidate(event, "price", "productSize_height")' type="text" class="form-control required-field" id="productSize_height" name="productSize_height"  placeholder="Height" >

                                                                </div>   
                                                            </div>

                                                        </div>
                                                    </div>-->



                                        <div class="col-md-12 lftexp" style="display: none">
                                            <h6>Preview shipping cost</h6>
                                            <p class="small">See what buyers will pay for shipping.</p>
                                        </div>
                                        <div class="col-md-4 rghtexp" style="display: none">
                                            <div class="form-group">
                                                <select class="form-control" id="">
                                                    <option>If a buyer lives in</option>
                                                    <option>USA</option>
                                                    <option>England</option>
                                                    <option>Australia</option>
                                                    <option>China</option>
                                                    <option>Russia</option>
                                                    <option>Germany</option>
                                                    <option>Brazil</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-4 rghtexp" style="display: none">
                                            <p class=" ">Their cost would be around <br> <span> &#36; 0.00</span></p>
                                        </div>
                                    </div>

                                </div>
                                <!--                            </div>-->
                            </div> <!-- card prdsec -->
                        </div>


                        <div class="card-body faqs-body-add no-padd-left ">
                            <div class="row">
                                <div class="col-md-12 lftexp ">
                                    <h6>Product Frequently Asked Questions</h6>
                                    <p class="mb-10">Answer the possible questions shopper may ask before buying the product
                                    </p>
                                </div>


                                <div class="col-md-12 rghtexp causelist">
                                    <div class="form-group mb-15">
                                        <label>
                                            Question
                                        </label>
                                        <input type="text" onkeypress='alphaNumvalidate(event, "faqQuestion", 150)' id="faqQuestion" class="form-control" maxlength="150" placeholder="Make sure to price your products competitively">
                                    </div>
                                    <div class="form-group mb-15">
                                        <label>
                                            Answer
                                        </label>
                                        <textarea onkeypress='alphaNumvalidate(event, "faqAnswer", 700)' id="faqAnswer" class="form-control" max="700" placeholder="Make sure to price your product competitively"></textarea>
                                    </div>
                                    <div class="add-button-option">
                                        <button class="btn btn-primary btn-simple" id="addFaqBtn" type="button" data-toggle="">Add Another</button>
                                    </div>
                                    <div class="faqs-list-results">

                                    </div>
                                    <!-- faqs-list-results -->
                                </div>
                            </div>

                        </div>


                        <!-- Add Product Section Ends -->

                        <input type="hidden" name="shippingOptionKey" value="">
                        <input type="hidden" id="edit_product_id" name="edit_product_id" value="">
                        <input type="hidden" id="productVideoURL" name="productVideoURL" value="">

                        <!-- Form Option Ends -->

                        <div class="container-fluid bxsub largeButton">

                            <div class="container-df">

                                <div class="row">
                                    <div class="col-md-12">
                                        <p class="text-left">
                                            <button  name="add_another_product" value="add_another_product" id="btn_addAnotherProduct" class="btn btn-primary  addProductSubmit save-button btn-primary-background width30 text-white" type="button"> Add Another Product</button>
                                            <input type="hidden" name="another_product_add" value="0" id="another_product_add">
                                            <button  name="finsh_adding_product" value="finish_adding_product" id="btn_finishAddingProduct" class="btn btn-primary addProductSubmitFinish  btn-simple save-button btn-primary-background width30 text-blue btnOuterBorder" saveproduct="active" id="btn_addProductSubmit" type="button">Finish Adding Product</button>
                                            <button  name="draft_product" value="draft_product" id="draft_product" class="btn btn-primary draft_product  save-button btn-primary-background width30 text-blue btnOuterBorder btn-simple" type="button">Draft</button>
                                            &nbsp;&nbsp;&nbsp;&nbsp;
                                            <a href="{{ url('store-products') }}" class="discardChanges"> <i class="fa fa-trash" aria-hidden="true"></i>
                                                Discard Changes</a>
                                            <!--                        <a href="{{ url('brand-products') }}" class=" hide-div blue-text">Preview</a>                                             -->

                                    </div>
                                </div>

                            </div>

                        </div>


                    </div>
                    <!-- Ends container-outrer -->

                </div>


            </div>

            <div class="stop-btn-pos" id="stop-position-fixed"></div>



        </form>




    </div><!-- dshbrdin -->



</main>
<!-- Content Ends-->

<style>
    body .ui-sortable-helper {
        position: relative !important;
        z-index: inherit !important;
    }

    .product-slide-values .slick-slide {
        display: block !important;
    }

    .values_product_visibility {
        display: none;
    }

    .pp_pic_holder {
        top: 0% !important;
    }

    .pp_expand {
        display: none !important;
    }

    .border-class {
        border: 2px solid #0510fa !important;
    }
    .variation_images{
        border-radius: 20px;  
    }
    .variation_image_text_note{
        font-size: 10px;
    text-align: center;
    font-style: italic;
    font-weight: 600; 
    }
    .select_feature_image{
    position: absolute !important;
    width: 200px;
    opacity: 0;
    height: 200px;
    left: 0px !important;
    
}
.set-feature-image{
    margin-left: 25px;
    border: 2px blue solid;
    padding: 10px;
    border-radius: 20px;
    width: 150px;
    height: 100px;
}
.product-thumbnail-image{
    max-width: 100%;
}
.col-md-6.productThumbnail-tool .tooltip-icon {
    position: relative;
    margin-left: 30px;
    top: -30px;
}
.col-md-6.productThumbnail-tool p.photoNotification{
    color:#fff;
    margin-bottom:0px ;
}
</style>

<script>
    $('input[name=productTags]').tagsinput({
        maxChars: 20,
        maxTags: 10,
    });
    $(function() {
        $('#Product-type-variant').change(function() {
            $('.colors').hide();
            // if product is virtual
            if ($(this).val() == 'virtual') {
                $('input[name=previousShippingOptions]').prop('checked', false);

                $('.shippingTab').hide();
                $('.virtual-hide').hide();
            } else {
                $('.shippingTab').show();
                $('.virtual-hide').show();
            }
            $('.' + $(this).val()).show();
        });
    });


    var fixmeTop = $('#stop-position-fixed').offset().top - 460;
    $(window).scroll(function() {
        var currentScroll = $(window).scrollTop();
        if (currentScroll >= fixmeTop) {
            $("#stop-position-fixed").addClass('sticky-strip');
            $("body").addClass('btn-fixed-disable');

        } else {
            $("#stop-position-fixed").removeClass('sticky-strip');
            $("body").removeClass('btn-fixed-disable');

        }
    });


    $('.shipping-section-container input.parent-radio-buttons').click(function() {
        if ($(this).attr('id') === 'custom-shipping') {

            $('.showon-customshipping-select').css("display", "flex");
            $('.cl-bx-mbt').addClass("showdiv");

        } else {
            // $('#show-me').hide();   
            $('.showon-customshipping-select').css("display", "none");
            $('.cl-bx-mbt').removeClass("showdiv");
        }

    });

    $('#btn_addAnotherProduct').click(function() {
        $('#another_product_add').val(1);
    });
    $('#btn_finishAddingProduct').click(function() {
        $('#another_product_add').val(0);
    });

    $('#draft_product').click(function() {
        $('#another_product_add').val(5);
    });

   $('#btn_addAnotherProduct').click(function () {
    let productPrices = $('#productPrice').val();
    if(productPrices === '') {
        document.scrollingElement.scroll(0, 850);
    }
    console.log(productPrices, 'productPrice');
   });
   
</script>
@include('product.shipping.shippingCountriesJs')
@include('product.shipping.addShippingOptions')
@endsection