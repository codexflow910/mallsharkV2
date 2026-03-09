<link href="{{ URL::asset('assets/css/prettyPhoto.css' ,env('HTTPS_STATUS'))}}" rel="stylesheet" type="text/css"
      media="screen"/>
<link href="{{ URL::asset('assets/image-cropper/css/cropper.css' ,env('HTTPS_STATUS'))}}" rel="stylesheet"
      type="text/css" media="screen"/>
<link href="{{ URL::asset('assets/image-cropper/css/main.css' ,env('HTTPS_STATUS'))}}" rel="stylesheet" type="text/css"
      media="screen"/>
{{ csrf_field() }}

<?php
$prodcutTitle = isset($prodcutDetails['title']) ? $prodcutDetails['title'] : '';
$prodcutDescription = isset($prodcutDetails['description']) ? $prodcutDetails['description'] : '';
$prodcutPrice = isset($prodcutDetails['price']) ? $prodcutDetails['price'] : '';
$prodcutCurrentStock = isset($prodcutDetails['current_stock']) ? $prodcutDetails['current_stock'] : '';
$prodcutSKU = isset($prodcutDetails['sku']) ? $prodcutDetails['sku'] : '';
$prodcutWeight = isset($prodcutDetails['weight']) ? $prodcutDetails['weight'] : '';
$prodcutFeatureImage = isset($prodcutDetails['featureImage']) ? $prodcutDetails['featureImage'] : '';
$prodcutShortDescription = isset($prodcutDetails['shortDescription']) ? $prodcutDetails['shortDescription'] : '';
$productSizeLength = isset($prodcutDetails['productSizeLength']) ? $prodcutDetails['productSizeLength'] : '';
$productSizeWidth = isset($prodcutDetails['productSizeWidth']) ? $prodcutDetails['productSizeWidth'] : '';
$productSizeHeight = isset($prodcutDetails['productSizeHeight']) ? $prodcutDetails['productSizeHeight'] : '';


$productShippingLocal = isset($prodcutDetails['productShippingLocal']) ? $prodcutDetails['productShippingLocal'] : '';
$productShippingCouries = isset($prodcutDetails['productShippingCouries']) ? $prodcutDetails['productShippingCouries'] : '';
$productShippingMovers = isset($prodcutDetails['productShippingMovers']) ? $prodcutDetails['productShippingMovers'] : '';
$productTaxationPST = isset($prodcutDetails['productTaxationPST']) ? $prodcutDetails['productTaxationPST'] : '';
$productTaxationGST = isset($prodcutDetails['productTaxationGST']) ? $prodcutDetails['productTaxationGST'] : '';
?>
<div class="row">
    <div class="col-md-4 lftexp hide-div ">
        <h6>Product Title *</h6>
        <p>Include keywords that buyers would use to search for your item.</p>
    </div>
    <div class="col-md-8 rghtexp">
        <label class="labled-item">Product Title *</label>
        <div class="form-group width538">
            <input type="text" class="form-control" id="productTitle" name="productTitle"
                   value="<?php echo $prodcutTitle; ?>" onkeypress='alphaNumvalidate(event, "productTitle", 40)'
                   maxlength="40" placeholder="">
        </div>
    </div>
</div>
<div class="row">
    <?php if (empty($prodcutPrice) || $prodcutDetails['product_type'] == 'simple') { ?>
        <div class="col-md-8 rghtexp simple colors">

            <div class="row">
                <div class="col-md-5">
                    <label class="labled-item">Price *</label>
                    <input type="text" id="productPrice" name="productPrice" class="form-control"
                           value="<?php echo $prodcutPrice; ?>" placeholder=" "
                           onkeypress='Numvalidate(event, "productPrice")' maxlength="5"/>
                </div>

            </div>
        </div>
    <?php } else { ?>
        <input type="hidden" name="productvarientPrice" value="<?php echo $prodcutPrice; ?>"/>
    <?php } ?>

    <?php
    //for condition filter id is 1
    // $filterData = getFilterData(1);
    // if ($filterData['filter_status'] == 1) {
    ?>
    <div class="col-md-8 rghtexp">
        <label class="labled-item">Status *</label>
        <div class="row">
            <div class="col-md-4">
                <div class="form-group">
                    <select class="form-control" id="productstatus" name="product_status">
                        <option value="1" <?php
                        if (isset($prodcutDetails['product_status']) && $prodcutDetails['product_status'] == '1') {
                            echo 'selected';
                        }
                        ?>> Available</option>
                        <option value="2" <?php
                        if (isset($prodcutDetails['product_status']) && $prodcutDetails['product_status'] == '2') {
                            echo 'selected';
                        }
                        ?> > Out of stock</option>
                        <option value="3" <?php
                                if (isset($prodcutDetails['product_status']) && $prodcutDetails['product_status'] == '3') {
                                    echo 'selected';
                                }
                                ?>> Pre order</option>
                    </select>
                </div>
            </div>
        </div>
    </div>
<?php // }    ?>
<?php if (empty($prodcutCurrentStock) || $prodcutDetails['product_type'] == 'simple') { ?>
        <div class="col-md-8 rghtexp simple colors">
            <label class="labled-item">Current Stock</label>
            <div class="row">
                <div class="col-md-4">
                    <input type="text" id="productcurrentstock"  name="productcurrentstock" value="<?php echo $prodcutCurrentStock; ?>" class="form-control" maxlength="20"/>
                </div>
            </div>
        </div>
<?php } ?>
<?php if (empty($prodcutSKU) || $prodcutDetails['product_type'] == 'simple') { ?>
        <div class="col-md-8 rghtexp simple colors">
            <label class="labled-item">SKU  (Optional)</label>
            <div class="row">
                <div class="col-md-4">
                    <input type="text" id="productSKU"  name="productSKU" value="<?php echo $prodcutSKU; ?>" class="form-control" maxlength="20"/>
                </div>
            </div>
        </div>
<?php } ?>

</div>
<div class="row photoSection">
    <div class="col-md-4 lftexp hide-div ">
        <h6>Add as many as you can so buyers can see every detail.</h6>
        <p>Photos * <br>
            Use up to six photos to show your item's most important qualities.</p>
        <p>Tips:<br>Use natural light and no flash. Include a common object for scale. Show the item being held, worn,
            or used.
            Shoot against a clean, simple background. </p>
    </div>
    <div class="col-md-6 rghtexp ">
        <div class="justify-content-between row">
            <label class="labled-item col-sm-6">Product Pictures</label>
            <span class="col-sm-6 text-right ligt-grey fnt-12">Note: You can upload multiple files</span>
        </div>

        <div class="form-group custom-panel-style mb-20">
            <label class="custom-file-upload mt-20">
                Upload Photos
                <!--                  <input type="file" name="file_upload" id="file_upload" class="form-control" onchange="readFile(this)"> -->
                <input type="file" accept="image/png,image/jpeg,image/jpg" multiple id="productImage"
                       onchange="readURL(this)" class="">
            </label>
            <p class="ligt-grey mt-10 mb-0 fnt-12">Note: Only jpeg or png, files can be uploaded, Maximum file size is
                3MB</p>
            <p class="ligt-grey mt-10 mb-0 fnt-12" id="imageUploadCounter"></p>
        </div>
    </div>
    <div class="col-md-12 rghtexp mt-0">

        <div class="uploadImages"><span></span></div>
        <div class="row">

            <div id="all-uplpad-image">

                <?php
                $imageCount = 0;
                $featureValidator = '';
                if (isset($prodcutDetails) && is_array($prodcutDetails)) {

                    if ($prodcutDetails['featureImage']) {
                        $imageUrl = getAttachmentUrlById((int) $prodcutDetails['featureImage']);
                        if ($imageUrl) {
                            $featureValidator = 'style="display: none;"';
                        }
                        ?>
                        <div id="img_70009280520" class="image-panel ">
                            <div class="image-inner-mbt">
                                <div class="img-only">
<!--                                    <a class="viewImages" rel="prettyPhoto" href="<?php echo $imageUrl; ?>">-->
                                        <img class="previewClass" src="<?php echo $imageUrl; ?>" alt="{{$prodcutDetails['title']}}" title="{{$prodcutDetails['title']}}">
<!--                                    </a>-->
                                </div>
                                <div class="loading-image">
                                    <span></span>
                                </div>
                                <input id="imagesrc70009280520" type="hidden"
                                       value="<?php echo (int) $prodcutDetails['featureImage']; ?>" name="productImage[]">
                                <div class="dele-icn">
                                    <a class="editEvent" href="javascript:;" data-toggle="modal"
                                       data-target="#cropper_70009280520">
                                        <i class="fa fa-crop" aria-hidden="true"></i>
                                    </a>
                                    <a href="javascript:;" class="dete-parent" onclick="deleteImage(70009280520)">
                                        <i class="fa fa-trash-o" aria-hidden="true"></i>
                                    </a>
                                </div>
                                <div class="modal" id="cropper_70009280520" tabindex="-1" role="dialog" aria-hidden="true">
                                    <div class="modal-dialog animated bounceInDown" style="max-width:800px">
                                        <div class="modal-content">
                                            <div class="modal-body">
                                                <div class="row imagecrop-area">
                                                    <div class="col-xs-12 col-sm-12">
                                                        <div class="img-container">
                                                            <img class="Cropperimage" src="<?php echo $imageUrl; ?>"
                                                            alt="{{$prodcutDetails['title']}}" title="{{$prodcutDetails['title']}}">
                                                        </div>
                                                        <div class="clearfix"></div>
                                                        <br>
                                                    </div>
                                                    <div class="clearfix spacer20"></div>
                                                    <div class="col-md-12 docs-toggles"></div>

                                                </div>

                                            </div>
                                            <div class="modal-footer">
                                                <button data-dismiss="modal" class="btn btn-default btn btn-primary btn-simple"
                                                        type="button">Close
                                                </button>
                                                <button class="btn btn-success getCropImage btn btn-primary" type="button"
                                                        data-dismiss="modal">Save changes
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
                        <div id="img_<?php echo $counterGallery; ?>" class="image-panel">
                            <div class="image-inner-mbt">
                                <div class="img-only">
<!--                                    <a class="viewImages" rel="prettyPhoto" href="<?php //echo $imageGalleryUrl; ?>">-->
                                        <img class="previewClass" src="<?php echo $imageGalleryUrl; ?>" alt="product images" title="product images">
<!--                                    </a>-->
                                </div>
                                <div class="loading-image">
                                    <span></span>
                                </div>
                                <input id="imagesrc<?php echo $counterGallery; ?>" type="hidden"
                                       value="<?php echo (int) $imageID; ?>" name="productImage[]">
                                <div class="dele-icn">
                                    <a class="editEvent" href="javascript:;" data-toggle="modal"
                                       data-target="#cropper_<?php echo $counterGallery; ?>">
                                        <i class="fa fa-crop" aria-hidden="true"></i>
                                    </a>
                                    <a href="javascript:;" class="dete-parent"
                                       onclick="deleteImage(<?php echo $counterGallery; ?>)">
                                        <i class="fa fa-trash-o" aria-hidden="true"></i>
                                    </a>
                                </div>
                                <div class="modal" id="cropper_<?php echo $counterGallery; ?>" tabindex="-1" role="dialog"
                                     aria-hidden="true">
                                    <div class="modal-dialog animated bounceInDown" style="max-width:800px">
                                        <div class="modal-content">
                                            <div class="modal-body">
                                                <div class="row imagecrop-area">
                                                    <div class="col-xs-12 col-sm-12">
                                                        <div class="img-container">
                                                            <img class="Cropperimage" src="<?php echo $imageGalleryUrl; ?>"
                                                                 alt="Product images" title="Product images">
                                                        </div>
                                                        <div class="clearfix"></div>
                                                        <br>
                                                    </div>
                                                    <div class="clearfix spacer20"></div>
                                                    <div class="col-md-12 docs-toggles"></div>

                                                </div>

                                            </div>
                                            <div class="modal-footer">
                                                <button data-dismiss="modal" class="btn btn-default btn btn-primary btn-simple"
                                                        type="button">Close
                                                </button>
                                                <button class="btn btn-success getCropImage btn btn-primary" type="button"
                                                        data-dismiss="modal">Save changes
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
        <!--            <div class=" col-md-3" id="uploadImages" <?php //echo $featureValidator;      ?>>
                 <input type="file"  accept="image/png,image/jpeg,image/jpg" id="productImage" onchange="readURL(this)" class=""> 
                <div class="prdimg pi1">
                    <span class="imgtg" style="display: inline;">Product Image</span>
                    <i class="fa fa-camera" aria-hidden="true"></i>
                </div>
                <input type="file" id="imgupload" style="display:none"> 
            </div>-->
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-8 rghtexp mt-0">
        <label class="labled-item">Short Description *</label>
        <div class="form-group">
            <textarea class="form-control required-field"rows="5" onkeypress='alphaNumvalidate(event, "productShortDescription", 500)'
                      id="productShortDescription" maxlength="500" id="productDescription"
                      name="productShortDescription"><?php echo $prodcutShortDescription; ?></textarea>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-8 rghtexp">
        <label class="labled-item">Description *</label>
        <div class="form-group">
            <textarea class="form-control producsdes" id="producsdes" rows="5" onkeypress='alphaNumvalidate(event, "producDescription", 1000)'
                      maxlength="1000" id="productDescription"
                      name="productDescription"><?php echo $prodcutDescription; ?></textarea>
        </div>
    </div>
</div>

<div class="row">

    <div class="col-md-4 lftexp hide-div ">
        <h6>Dimensions (cm)</h6>

    </div>
    <div class="col-md-8 rghtexp">
        <label class="labled-item">Dimensions (cm)</label>
        <div class="row">
            <div class="col-md-4 small-lable">
                <div class="form-group">
                    <label>Length</label>
                    <input onkeyup='return Numvalidate(event, "productSize_length")'
                           value="<?php echo $productSizeLength; ?>" type="text" class="form-control required-field"
                           id="productSize_length" name="productSize_length" placeholder="Length">

                </div>
            </div>
            <div class="col-md-4 small-lable">
                <div class="form-group">
                    <label>Width</label>
                    <input onkeyup='return Numvalidate(event, "productSize_width")'
                           value="<?php echo $productSizeWidth; ?>" type="text" class="form-control required-field"
                           id="productSize_width" name="productSize_width" placeholder="Width">
                </div>
            </div>
            <div class="col-md-4 small-lable">
                <div class="form-group">
                    <label>Height</label>
                    <input onkeyup='return Numvalidate(event, "productSize_height")'
                           value="<?php echo $productSizeHeight; ?>" type="text" class="form-control required-field"
                           id="productSize_height" name="productSize_height" placeholder="Height">

                </div>
            </div>

        </div>
    </div>
    <div class="col-md-4 lftexp hide-div ">
        <h6>weight (lbs)</h6>
    </div>
    <div class="col-md-8 rghtexp">
        <dvi class="row">
            <div class="col-md-4">
                <label class="labled-item">weight (lbs)</label>
                <div class="form-group">
                    <input onkeyup='return Numvalidate(event, "productWeight")' value="<?php echo $prodcutWeight; ?>"
                           type="text" class="form-control required-field" id="productWeight" name="productWeight"
                           placeholder="">
                </div>
            </div>
    </div>
</div>
<script src="https://cdn.ckeditor.com/4.22.1/standard/ckeditor.js"></script>
<script>
    CKEDITOR.config.versionCheck = false;
    </script>
<script src="{{ URL::asset('assets/js/product-script.js' ,env('HTTPS_STATUS'))}}" type="text/javascript"
charset="utf-8"></script>
<script src="{{ URL::asset('assets/image-cropper/js/cropper.min.js' ,env('HTTPS_STATUS'))}}" type="text/javascript"
charset="utf-8"></script>
<script src="{{ URL::asset('assets/js/jquery.prettyPhoto.js',env('HTTPS_STATUS')) }}"></script>
<script>
                        CKEDITOR.replace('producsdes');


</script>