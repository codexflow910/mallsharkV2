@extends('layouts.dashboard-main')
<!-- Main Banner Starts -->
@section('content')
@section('title', 'Edit product')
<!-- Content Starts -->
<?php

$productType = isset($prodcutDetails['product_type']) ? $prodcutDetails['product_type'] : '';
$prodcutTitle = isset($prodcutDetails['title']) ? $prodcutDetails['title'] : '';
?>
<style>
    input {
        padding: 7px 11px !important;
    }
</style>
<main class="editproduct">
    @if (Session::has('SuccessMessage'))
        <div class="alert alert-success alert-success-mbt" role="alert">
            <strong>Well done!</strong> {{ Session::get('SuccessMessage') }}
        </div>
    @endif
    @php
        $productType = !empty($prodcutDetails['product_type']) ? $prodcutDetails['product_type'] : '';
    @endphp
    <script>
        var prodType = "{{ $productType }}";
    </script>
    @if ($errors->any())
        <div class="alert alert-danger" role="alert">
            <strong>Oh snap!</strong> Change a few things up and try submitting again.
        </div>
    @endif
    <?php
    // echo  request()->get('edit-by');
    if (!empty(request()->get('edit-by')) && request()->get('edit-by') == 'admin') {
        $urlData = url('admin/store-products');
        $class = 'hide_section';
    } else {
        $urlData = url('store-products');
        $class = '';
    }
    ?>
    <div class="alert alert-danger errorpage fade" role="alert" style="display: none">
        <strong><i class="fa fa-exclamation-triangle" aria-hidden="true"></i> </strong> Fill out all of the required
        fields correctly in order to preview or save
    </div>
    <div class="flex-parent-div ">
        <div class="change-password-page-cmt">
            @include('buyer.customsidebar')
        </div>
        <!-- Dashboard Ends -->
        <div
            class="dshbrdin mobile-top-spacing-less addProductpageWrapper editProductPageWrapper home-section cm-edit-ja addproductsidebar no-flex-div ">
            <div class="container">
                @include('Store.sidebar')

                <div class="row">
                    <div class="col-md-12" style="padding: 0px;">
                        <div class="">
                            <div class="d-flex align-items-center pt-4 pb-3">
                                <div class="hd-bt">
                                    <h1>
                                        <div class="d-flex align-items-center gap-3">
                                            <a href="javascript:void(0)"
                                                class="d-flex align-items-center gap-10px sidebar-admin-burger-menu"
                                                id="open-sidebar-admin-panel">
                                                <span id="menu-toggle-dashboard" class="shopzit-menu mt-0 ml-0 bx-menu">
                                                    <span></span><span></span><span></span> <span></span>
                                                </span>
                                                <!-- <span>Menu</span> -->
                                            </a>
                                            <span> Edit Product </span>
                                        </div>

                                    </h1>
                                </div>


                                <div class="add-pr-lnk">
                                    <?php
                                    $role = getCurrentUserRoleId();
                                    if ($role->role_id == 1) {
                                        $back_to_dashboard = '/admin/dashboard';
                                    } else {
                                        $back_to_dashboard = '/store-dashboard';
                                    }
                                    ?>
                                    <a class="btn btn-primary btn-simple small-btn-mbt bigIcon"
                                        href="{{ URL::to($back_to_dashboard) }}">
                                        Back to Dashboard
                                    </a>
                                </div>
                            </div>
                        </div>



                    </div>
                </div>
            </div>

            <form id="addProductForm" action="{{ url('editproductAction') }}" method="post"
                enctype="multipart/form-data">
                <!-- Add Product Section Starts -->
                <div class="addprd container" style="border: 2px solid #073ba5;border-radius:6px;padding:0px;">
                    <div class="container-outrer" style="border:none;padding-bottom:1rem;">
                        <div class="container">
                            <div class="row">
                                <div class="col-md-7">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <h6>Product Name *</h6>
                                            <div class="rghtexp ">
                                                <!--<label class="labled-item">Product Title *</label>-->
                                                <div class="form-group width538">
                                                    <input type="text" class="form-control" id="productTitle"
                                                        placeholder="Include keywords that buyers would use to search for your item."
                                                        name="productTitle" value="<?php echo $prodcutTitle; ?>"
                                                        onkeypress='alphaNumvalidate(event, "productTitle", 100)'
                                                        maxlength="100" minlength="15">
                                                </div>
                                            </div>
                                            <h6>Category
                                            </h6>
                                            <div class="form-group">

                                                <select class="form-control" id="productMainCategory"
                                                    name="productCategory[]">

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
                                            <div class="form-group" style="margin-bottom: 20px;" id="subcategory"
                                                style="display: none">

                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="form-group" id="childcategory" style="display: none">

                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-12 rghtexp" style="display: none;">
                                    <div class="row">

                                        <div class="col-sm-12 lftexp">
                                            <h6>Select Category *
                                            </h6>
                                            <p>Choose appropriate Category for your product</p>
                                        </div>

                                        <div class="col-md-12 bottomSpacing">
                                            <div class="form-group">
                                                <!--<label class="labled-item"></label>-->
                                                <select class="form-control" id="productMainCategory"
                                                    name="productCategory[]">

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



                                        <div class="col-md-12">
                                            <!--<label class="labled-item visiblity-hidden childcat" style="display:none;">Select Category</label>-->
                                            <div class="form-group" id="subcategory" style="display: none">

                                            </div>
                                        </div>


                                    </div>
                                </div>

                                <div class="col-md-5">
                                    <div class="col-md-12">
                                        <h6>Item Condition </h6>
                                        <div class="form-group">
                                            <select class="form-control" id="Product-item-condition"
                                                name="item_condition">
                                                <option value="new"
                                                    {{ $prodcutDetails['item_condition'] == 'new' ? 'selected' : '' }}>
                                                    New</option>
                                                <option value="used"
                                                    {{ $prodcutDetails['item_condition'] == 'used' ? 'selected' : '' }}>
                                                    Used</option>

                                            </select>
                                        </div>
                                    </div>


                                </div>
                            </div>

                           


                            <input type="hidden" name="edit_product_type" value="{{ $productType }}" />
                            <div class="row photoSection">
                                <div class="col-md-7 lftexp ">
                                    <h6>Product Images</h6>
                                    <i class="fa fa-info-circle tooltip-icon"></i>
                                    <div class="tooltip-text">
                                        <p class="photoNotification">Upload Gallery and Variation Images: Gallery images
                                            display your product from various angles, while variation images show
                                            different options like colors or sizes.
                                            <br>
                                            Tips: <br>Use natural light and no flash. Include a common object for scale.
                                            Show the item being held, worn,
                                            or used.
                                            Shoot against a clean, simple background.
                                        </p>
                                    </div>
                                    <div class="col-md-12 rghtexp " style="padding: 0px;">
                                        <div class="justify-content-between row" style="display:none;">
                                            <!--<label class="labled-item col-sm-6"></label>-->
                                            <span class="col-sm-6 text-left ligt-grey fnt-12  mobile-text-right-none"
                                                style="font-size:11px">Note: You can upload multiple files</span>
                                        </div>

                                        <div class="form-group custom-panel-style mb-20">
                                            <label class="custom-file-upload mt-20">
                                                Upload Images
                                                <!-- <input type="file" name="file_upload" id="file_upload" class="form-control" onchange="readFile(this)"> -->
                                                <input type="file" multiple id="productImage" name="productimages"
                                                    class="editProductImage">
                                                <input type="hidden" name="product_id" id="productId"
                                                    value="{{ $prodcutDetails['product_id'] }}">
                                            </label>
                                            <p class="ligt-grey mt-10 mb-0 fnt-12">Note: Only jpeg or png, files can be
                                                uploaded</p>
                                            <p class="ligt-grey mt-10 mb-0 fnt-12" id="imageUploadCounter"></p>
                                            <input type="hidden" name="
                                            "
                                                class="feature_image" id="feature_image" value="">
                                        </div>

                                        <div class="col-md-12 rghtexp mt-0">

                                            <div class="uploadImages"><span></span></div>
                                            <div class="row">


                                                <div id="all-uplpad-image" class="noman_class edit">

                                                    <?php
        if (!empty($prodcutDetails['gallery'])) {

            $counterGallery = rand(9999, 999999999);
            foreach ($prodcutDetails['gallery'] as $imageID) {
                $imageGalleryUrl = getAttachmentUrlById((int) $imageID);
            ?>
                                                    <div id="img_<?php echo $counterGallery; ?>"
                                                        class="image-panel product-image-panel ProductImageDelete">
                                                        <div class="image-inner-mbt">
                                                            <div class="img-only">
                                                                <a class="viewImages" rel="prettyPhoto"
                                                                    href="<?php echo $imageGalleryUrl; ?>">
                                                                    <img class="previewClass"
                                                                        src="<?php echo $imageGalleryUrl; ?>"
                                                                        alt="{{ $prodcutTitle }}"
                                                                        title="{{ $prodcutTitle }}">
                                                                </a>
                                                            </div>
                                                            <div class="loading-image">
                                                                <span></span>
                                                            </div>
                                                            <input id="imagesrc" type="hidden"
                                                                value="<?php echo (int) $imageID; ?>" name="productImage[]" class="prev-image-url">
                                                            <div class="dele-icn">
                                                                <!-- <input type="radio" class="form-check-input select_primary_image" name="optradio" value="<?php //echo (int) $imageID;
                                                                ?>">Feature -->
                                                                <!-- <a class="editEvent" href="javascript:;"
                                                                    data-toggle="modal"
                                                                    data-target="#cropper_<?php echo $counterGallery; ?>">
                                                                    <i class="fa fa-crop" aria-hidden="true"></i>
                                                                </a> -->
                                                                <a href="javascript:;" class="delete-parent-image"
                                                                    data-image-id="{{ $imageID }}"
                                                                    data-product-id="{{ $prodcutDetails['product_id'] }}">
                                                                    <!-- <a href="javascript:;" class="dete-parent" data-image-id="{{ $imageID }}" data-product-id="{{ $prodcutDetails['product_id'] }}" onclick="deleteImage(<?php echo $counterGallery; ?>)"> -->
                                                                    <i class="fa fa-trash-o" aria-hidden="true"></i>
                                                                </a>
                                                            </div>
                                                            <div class="modal" id="cropper_<?php echo $counterGallery; ?>"
                                                                tabindex="-1" role="dialog" aria-hidden="true">
                                                                <div class="modal-dialog animated bounceInDown"
                                                                    style="max-width:800px">
                                                                    <div class="modal-content">
                                                                        <div class="modal-body">
                                                                            <div class="row imagecrop-area">
                                                                                <div class="col-xs-12 col-sm-12">
                                                                                    <div class="img-container">
                                                                                        <img class="Cropperimage"
                                                                                            src="<?php echo $imageGalleryUrl; ?>"
                                                                                            alt="{{ $prodcutTitle }}"
                                                                                            title="{{ $prodcutTitle }}">
                                                                                    </div>
                                                                                    <div class="clearfix"></div>
                                                                                    <br>
                                                                                </div>
                                                                                <div class="clearfix spacer20"></div>
                                                                                <div class="col-md-12 docs-toggles">
                                                                                </div>

                                                                            </div>

                                                                        </div>
                                                                        <div class="modal-footer">
                                                                            <button data-dismiss="modal"
                                                                                class="btn btn-default btn btn-primary btn-simple"
                                                                                type="button">Close
                                                                            </button>
                                                                            <button
                                                                                class="btn btn-success getCropImage btn btn-primary"
                                                                                type="button"
                                                                                data-dismiss="modal">Save
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

                                <div class="col-md-5 productThumbnail-tool">
                                    <h6>Product Thumbnail </h6>
                                    <i class="fa fa-info-circle tooltip-icon "></i>
                                    <div class="tooltip-text">
                                        <p class="photoNotification">The product thumbnail image for display in
                                            listings and search results.</p>
                                    </div>


                                    <div class="mb-20 d-flex align-items-center justify-content-start">
                                        <!-- Button trigger modal -->

                                        <button type="button" data-product-id="{{ $prodcutDetails['product_id'] }}"
                                            data-productFeature-id="{{ $prodcutDetails['featureImage'] }}"
                                            class="btn btn-primary-back selectFeatureImageAdd showFeatureImage">Set
                                            Thumbnail</button>

                                        <div class="set-feature-image ml-2">
                                            <?php
                                 $imageCount = 0;
                                $featureValidator = '';
                                $featureClass= '';
                                if (isset($prodcutDetails) && is_array($prodcutDetails)) {
                                    // echo 'Data: <pre>' .print_r($prodcutDetails,true). '</pre>'; die;
                                    $counterFeatureImage = rand(9999, 999999999);
                                    if ($prodcutDetails['featureImage']) {
                                        $featureClass ='feature_image_border';
                                        $imageUrl = getAttachmentUrlById((int) $prodcutDetails['featureImage'], 'media');
                                        // dd($imageUrl);
                                        if ($imageUrl) {
                                            $featureValidator ='style="display: none;"';
                                        }
                                             ?>
                                            <div id="img_<?php echo $counterFeatureImage; ?>"
                                                class="image-panel {{ $featureClass }}">
                                                <div class="image-inner-mbt">
                                                    @if ($prodcutDetails['featureImage'])
                                                        <div class="img-only">
                                                            <a class="viewImages viewImagePrettyPhoto"
                                                                rel="prettyPhoto" href="<?php echo $imageUrl; ?>">
                                                                <img class="productThumbnailEdit previewClass"
                                                                    src="<?php echo $imageUrl; ?>"
                                                                    alt="{{ $prodcutTitle }}"
                                                                    title="{{ $prodcutTitle }}">
                                                            </a>
                                                        </div>
                                                    @endif
                                                    <div class="loading-image">
                                                        <span></span>
                                                    </div>
                                                    <input id="imagesrc<?php echo $counterFeatureImage; ?>" type="hidden"
                                                        class="featureImage_type"
                                                        data-product-id="{{ $prodcutDetails['product_id'] }}"
                                                        value="<?php echo (int) $prodcutDetails['featureImage']; ?>" name="productthumbnal-crp">
                                                    <div class="dele-icn">
                                                        <!-- <a class="editEvent" href="javascript:;" data-toggle="modal"
                                                            data-target="#cropper_<?php echo $counterFeatureImage; ?>">
                                                            <i class="fa fa-crop" aria-hidden="true"></i>
                                                        </a> -->
                                                        <a href="javascript:;"
                                                            data-image-id="{{ $prodcutDetails['featureImage'] }}"
                                                            id="imagefeaturesrc"
                                                            class="dete-parent d-none imagefeaturesrc"
                                                            data-product-id="{{ $prodcutDetails['product_id'] }}"
                                                            data-value="<?php echo (int) $prodcutDetails['featureImage']; ?>"
                                                            onclick="deleteEditFeatureImage(<?php echo $counterFeatureImage; ?>)">
                                                            <i class="fa fa-trash-o" aria-hidden="true"></i>
                                                        </a>
                                                    </div>
                                                    <div class="modal" id="cropper_<?php echo $counterFeatureImage; ?>"
                                                        tabindex="-1" role="dialog" aria-hidden="true">
                                                        <div class="modal-dialog animated bounceInDown"
                                                            style="max-width:800px">
                                                            <div class="modal-content">
                                                                <div class="modal-body">
                                                                    <div class="row imagecrop-area">
                                                                        <div class="col-xs-12 col-sm-12">
                                                                            <div class="img-container">
                                                                                <img class="Cropperimage featureImageCropper"
                                                                                    src="<?php echo $imageUrl; ?>"
                                                                                    alt="{{ $prodcutTitle }}"
                                                                                    title="{{ $prodcutTitle }}">
                                                                            </div>
                                                                            <div class="clearfix"></div>
                                                                            <br>
                                                                        </div>
                                                                        <div class="clearfix spacer20"></div>
                                                                        <div class="col-md-12 docs-toggles"></div>

                                                                    </div>

                                                                </div>
                                                                <div class="modal-footer">
                                                                    <button data-dismiss="modal"
                                                                        class="btn btn-default btn btn-primary btn-simple "
                                                                        type="button">Close
                                                                    </button>
                                                                    <button
                                                                        class="btn btn-success getCropImage btn btn-primary"
                                                                        type="button" data-dismiss="modal">Save
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
                                ?>
                                        </div>

                                    </div>
                                </div>




                            </div>
                            <div class="">
                                @include('product.product-variation-edit')
                            </div>
                            @php
                                $classvirtual = 'hide-me';
                                $wclss = '';
                                if ($productType == 'virtual') {
                                    $classvirtual = '';
                                    $wclss = 'hide-me';
                                }
                            @endphp
                            <div class="col-md-7 rghtexp virtual colors mt-5 {{ $classvirtual }}">
                                <div class="row photoSection">
                                    <div class="col-md-12 lftexp ">
                                        <h6>Product Files *</h6>
                                        <p>
                                            (Include all files) Use up to five files to show your item's most important
                                        </p>

                                    </div>
                                    <div class="col-md-12 rghtexp ">
                                        <div class="justify-content-between row">
                                            <!--<label class="labled-item col-sm-6"></label>-->
                                            <span class="col-sm-6 text-left ligt-grey fnt-12  mobile-text-right-none"
                                                style="font-size:11px">Note: You can upload multiple files</span>
                                        </div>

                                        <div class="form-group files-custom-panel-style mb-20">

                                            <!-- <input type="file" name="file_upload" id="file_upload" class="form-control" onchange="readFile(this)"> -->
                                            <input type="file"
                                                accept="image/png,image/jpeg,image/jpg,application/pdf" multiple=""
                                                id="product_files" name="product_files[]"
                                                class="form-control upload-file-input">
                                            </label>
                                            <p class="ligt-grey mt-10 mb-0 fnt-12">Note: Only jpeg or png, pdf files
                                                can be
                                                uploaded</p>
                                            <p class="ligt-grey mt-10 mb-0 fnt-12" id="imageUploadCounter"></p>
                                            <input type="hidden" name="feature_image" class="feature_image"
                                                id="feature_image" value="">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @if (isset($prodcutDetails['digital_files']) && !empty($prodcutDetails['digital_files']))

                            @foreach ($prodcutDetails['digital_files'] as $files)
                                <div class="image-panel uploaded-images-section" style="margin-left: 16px;">
                                    @php

                                        $imageUrl = URL::asset('uploads/digital/' . $files->file_name);
                                        $ext = pathinfo($files->file_name, PATHINFO_EXTENSION);
                                        if ($ext == 'pdf') {
                                            $imageUrl = URL::asset('assets/images/pdf.svg');
                                        }
                                        $fileName = !empty($files->orignal_file_name) ? $files->orignal_file_name : '';
                                    @endphp

                                    <div class="image-inner-mbt">
                                        <div class="img-only">
                                            <a class="viewImages" rel="prettyPhoto" href="<?php echo $imageUrl; ?>">
                                                <img class="previewClass" src="<?php echo $imageUrl; ?>"
                                                    alt="{{ $prodcutTitle }}" title="{{ $prodcutTitle }}">
                                                <p> {{ $fileName }}</p>
                                            </a>
                                        </div>
                                        <div class="loading-image">
                                            <span></span>
                                        </div>
                                        <input id="imagesrc70009280520" type="hidden" value="<?php echo $files->id; ?>"
                                            name="file_ids[]">
                                        <div class="dele-icn">
                                            <a href="javascript:;" data-file-ids="{{ $files->id }}"
                                                class=" product_file_delete">
                                                <i class="fa fa-trash-o" aria-hidden="true"></i>
                                            </a>
                                        </div>

                                    </div>
                                </div>
                            @endforeach
                            <input type="hidden" value="" id="delete_file_ids" name="delete_file_ids">
                        @endif
                        <div class="container">
                           
                            @include('product.attributes')
                        </div>

                        {{-- <div class="container <?php echo $class; ?>" style="padding: 10px;">
                        <div class=" ">
                            <div class="card prdsec shippingTab w-100" style="margin-bottom: 12px;">
                                <div class="card-header" role="tab" id="" style="display:none;">
                                    <!--                                <h5 class="mb-0">
                                                                    <a class="collapsed" data-toggle="collapse" href="#collapsefive" aria-expanded="false" aria-controls="collapseThree">
                                                                        Shipping
                                                                        <i class="fa fa-plus pull-right" aria-hidden="true"></i>
                                                                        <i class="fa fa-minus pull-right" aria-hidden="true"></i>
                                                                    </a>
                                                                </h5>-->
                                </div>
                                <!--                            <div id="collapsefive" class="panel-collapse  collapse show" role="tabpanel" aria-labelledby="headingfive" data-parent="#accordion">-->
                                @php
                                $class = '';
                                if (isset($prodcutDetails['product_type']) && $prodcutDetails['product_type'] == 'virtual') {
                                $class = 'hide-me';
                                }
                                @endphp --}}
                        @if ($productType != 'virtual')
                            <div class="card-body" style="padding-left:0px;padding-right:0px;">

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
                                            <div
                                                class="panel-repeater-radio custom-shipping-container br-top-none emphty-space-zero">
                                            </div>
                                            <!-- panel-repeater-radio -->
                                        </div>
                                    </div>
                                    <div class="addCustomShip">
                                        <a href="javascript:void(0);" id="add-shipping-profile-custom"
                                            class="defaultbtn add-shipping-profile">Create Custom Shipping Profiles</a>
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
                        @endif
                    </div> <!-- card prdsec -->
                </div>

                <div class="container" style="padding: 0px;">
                    <div class="card-body faqs-body-add no-padd-left" style="padding-right:0px;padding-bottom:0px;">
                        <div class="row">
                            <div class="col-md-12 lftexp ">
                                <h6>Product Frequently Asked Questions</h6>
                                <p>Answer the possible questions shopper may ask before buying the product</p>
                            </div>
                            <div class="col-md-12 rghtexp causelist ">
                                <div class="form-group form-group mb-15">
                                    <label>
                                        Question
                                    </label>
                                    <input type="text" onkeypress='alphaNumvalidate(event, "faqQuestion", 150)'
                                        id="faqQuestion" class="form-control" maxlength="150"
                                        placeholder="Write Question">
                                </div>
                                <div class="form-group mb-15 ">
                                    <label>
                                        Answer
                                    </label>
                                    <textarea onkeypress='alphaNumvalidate(event, "faqAnswer", 700)' id="faqAnswer" class="form-control"
                                        style="max-height:36px;" max="700" placeholder="Write Answer"></textarea>
                                </div>
                                <div class="add-button-option">
                                    <button class="btn btn-primary btn-simple" id="addFaqBtn" type="button"
                                        data-toggle="">Add
                                        FAQs</button>

                                </div>
                                <div class="faqs-list-results">
                                    <?php
                                    if (isset($prodcutDetails['faqs']) && is_array($prodcutDetails['faqs']) && count($prodcutDetails['faqs']) > 0) {
                                        foreach ($prodcutDetails['faqs'] as $key => $value) {
                                            echo ' <div class="list-repeater-mbt">
                                                                                                                                                                                                                    <h4>Q:
                                                                                                                                                                                                                        ' .
                                                $value->question .
                                                '<span class="pull-right text-center"> <a href="javascript:;" class="align-with-btn text-gray-lighter cross-icon-cont remove-containertabs"><span class="ss-etsy ss-delete icon-smaller icon-t-2"><i class="fa fa-times" aria-hidden="true"></i></span></a></span>
                                                                                                                                                                                                                        <input id="faqQuestion' .
                                                $key .
                                                '" type="hidden" value= "' .
                                                $value->question .
                                                '" name="faqQuestion[]"/> 
                                                                                                                                                                                                                    </h4>
                                                                                                                                                                                                                    <p>
                                                                                                                                                                                                                         ' .
                                                $value->answer .
                                                '
                                                                                                                                                                                                                        <input id="faqAnswer_' .
                                                $key .
                                                '" type="hidden" value= "' .
                                                $value->answer .
                                                '" name="faqAnswer[]"/> 
                                                                                                                                                                                                                    </p>
                                                                                                                                                                                                                </div>';
                                        }
                                    }
                                    ?>
                                </div>
                                <!-- faqs-list-results -->



                            </div>
                        </div>
                    </div>
                </div>
        </div>

        <input type="hidden" id="product_status" name="product_status" value="<?php echo isset($prodcutDetails['status']) ? $prodcutDetails['status'] : ''; ?>">
        <input type="hidden" id="edit_product_id" name="edit_product_id"
            value="{{ collect(request()->segments())->last() }}">


        <input type="hidden" name="userID" value="<?php echo $prodcutDetails['user_id']; ?>">
        <input type="hidden" name="storeID" value="<?php echo $prodcutDetails['store_id']; ?>">
        <input type="hidden" name="storeID" value="<?php echo isset($prodcutDetails['shipping_option_id']) ? $prodcutDetails['shipping_option_id'] : ''; ?>">
        <input type="hidden" id="productVideoURL" name="video_url" value="<?php echo isset($prodcutDetails['video_url']) ? $prodcutDetails['video_url'] : ''; ?>">
        <input type="hidden" id="edit-by" name="edit-by" value="<?php echo isset($_GET['edit-by']) ? $_GET['edit-by'] : ''; ?>">
        <!-- Add Product Section Ends -->

        <!-- Form Option Ends -->

        <div class="container-fluid bxsub" style="padding: 0rem 0rem 1.8rem 1rem;">

            <div class="container" style="padding: 0px;">

                <div class="row">

                    <div class="col-md-12">
                        <div class="d-flex align-items-center itemgap15">
                            {{-- btn_addProductSubmit  addProductSubmit --}}
                            <button class="btn btn-primary addProductSubmit text-white" id="btn_addProductSubmit"
                                type="submit">Update</button>
                            <!-- btn btn-outline-primary -->
                            <a href="{{ $urlData }}" class=" cancle discardChanges"><i class="fa fa-trash"
                                    aria-hidden="true"></i> Discard Changes</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>
    <!-- Edit Product Select Feature Images Start-->
    <div class="modal fade" id="prodcutFeatureModal" tabindex="-1" role="dialog"
        aria-labelledby="prodcutFeatureModalLabel" aria-hidden="true">
        <div class="modal-dialog cancel-order-popup modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h3 class="modal-title" id="prodcutFeatureModalLabel">Select Thumbnail Image</h3>

                    <button type="button" class="close modal-close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="productFeatureModalBody" style="padding: 20px 20px 0px 20px;">

                </div>
            </div>
        </div>
    </div>
    <input type="hidden" name="optradio" value="" id="productThumbnailEdit" class="productThumbnailEdit">
    <!-- Edit Product Select Feature Images End-->
    </form>

    <!-- Modal -->


    </div>

    <!-- Edit Product Select Variable Images Start-->
    <div class="modal fade" id="prodcutModal" tabindex="-1" role="dialog" aria-labelledby="prodcutModalLabel"
        aria-hidden="true">
        <div class="modal-dialog cancel-order-popup modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h3 class="modal-title" id="prodcutModalLabel">Select Variation Image</h3>
                    <button type="button" class="close modal-close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="productModalBody">

                    <!-- Order details will be loaded here using JavaScript -->
                </div>
            </div>
        </div>
    </div>
    <!-- Edit Product Select Variable Images End-->


</main>

<script type="text/javascript">
    $("a[rel^='prettyPhoto']").prettyPhoto({
        social_tools: ''
    });
</script>

<script type="text/javascript">
    jQuery().ready(function() {

        $('#productMainCategory').val(<?php echo isset($prodcutDetails['category'][0]->category_id) ? $prodcutDetails['category'][0]->category_id : ''; ?>).change();

        setTimeout(function() {

            <?php if (isset($prodcutDetails['category'][1]) && property_exists($prodcutDetails['category'][1], 'category_id')) { ?>

            $('#subcategory select option[value=<?php echo $prodcutDetails['category'][1]->category_id; ?>]').attr('selected', 'selected');
            $('#subcategory select').val(<?php echo $prodcutDetails['category'][1]->category_id; ?>).change();

            <?php } ?>


            setTimeout(function() {
                <?php if (isset($prodcutDetails['category'][2]) && property_exists($prodcutDetails['category'][2], 'category_id')) { ?>
                $('#childcategory select option[value=<?php echo $prodcutDetails['category'][2]->category_id; ?>]').attr('selected',
                    'selected');
                <?php } ?>

            }, 500);
        }, 1000);
        // $('#btn_addProductSubmit').click(function(){
        //     $(this).prop('disabled', true);
        // });

    })
</script>
<style>
    body .ui-sortable-helper {

        position: relative !important;
        z-index: inherit !important;
    }

    .pp_pic_holder {
        top: 0% !important;
    }

    .pp_expand {
        display: none !important;
    }

    .hide_section {
        display: none;
    }

    .feature_image_border {
        border: 2px solid #0510fa;
    }

    .col-md-6.productThumbnail-tool .tooltip-icon {
        position: relative;
        margin-left: 30px;
        top: -30px;
    }

    .col-md-6.productThumbnail-tool p.photoNotification {
        color: #fff;
    }
</style>
@include('product.shipping.shippingCountriesJs')
@include('product.shipping.addShippingOptions')
@endsection
