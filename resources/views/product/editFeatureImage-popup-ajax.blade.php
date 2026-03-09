<?php
$counterGallery = rand(9999, 999999999);
$i = 0;
if (isset($productInfo) && is_array($productInfo)) {
    if ($productInfo['gallery']) {
        
        foreach ($productInfo['gallery'] as $imageID) {
            $imageGalleryUrl = getAttachmentUrlById((int) $imageID);
            // echo 'Data: <pre>' .print_r($imageGalleryUrl,true). '</pre>'; die();
            $isChecked = ($featureImage == $imageID) ? "checked" : "";
            $borderClass = '';
            if ($isChecked == 'checked') {
                $borderClass  = 'border-class';
            }
                ?>

            <div id="img_" class="image-panel feature_images_insert second_class {{$borderClass}}" >
                <input id="imageThumbnail" class="select_feature_image_insert" type="radio" data-productThumbnal="<?php echo $imageGalleryUrl; ?>" data-product-id="<?php echo $productInfo['product_id']; ?>" value="<?php echo $imageID; ?>"  name="optradio">
                <div class="image-inner-mbt">
                    <div class="img-only">
                        <img class="previewClass" src="<?php echo $imageGalleryUrl; ?>" alt="">
                    </div>
                    <div class="loading-image">
                        <span></span>
                    </div>
                  
                    <div class="dele-icn d-none">
                        <a class="editEvent" href="javascript:;" data-toggle="modal" data-target="#cropper_<?php echo $counterGallery; ?>">
                            <i class="fa fa-crop" aria-hidden="true"></i>
                        </a>
                        <a href="javascript:;"  data-image-id="" id="imagefeaturesrc" class="dete-parent imagefeaturesrc" data-product-id="">
                            <i class="fa fa-trash-o" aria-hidden="true"></i>
                        </a>
                    </div>
                    <div class="modal" id="cropper_<?php echo $counterGallery; ?>" class="cropperMOdel" tabindex="-1" role="dialog" aria-hidden="true">
                        <div class="modal-dialog animated bounceInDown" style="max-width:800px">
                            <div class="modal-content">
                                <div class="modal-body">
                                    <div class="row imagecrop-area">
                                        <div class="col-xs-12 col-sm-12">
                                            <div class="img-container">
                                                <img class="Cropperimage featureImageCropper" src="<?php echo $imageGalleryUrl; ?>" >
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
                            $i++;
                        }
                    }
                }
                ?>
<style>
    .border-class {
        border: 2px solid #0510fa !important;
    }
    
</style>
<script>
    var isRequestInProgress = false;
    jQuery('body').on('click', '.feature_images_insert', function(e) {
        if (isRequestInProgress) {
        return; // If a request is already in progress, do nothing
        }

        $(".feature_images_insert").removeClass("border-class");
        $(this).addClass("border-class");
        var imageId = $(this).find(".select_feature_image_insert").val();
        var productId = $(this).find(".select_feature_image_insert").attr('data-product-id');
        var imageThumbnail = $(this).find(".select_feature_image_insert").attr('data-productThumbnal');
        var editImageTarget = $(this).find(".editEvent").attr('data-target');
        const parts = editImageTarget.split(/#/);
        const desiredString = parts[1];


        
        $('#productThumbnailEdit').val(imageId);      

        $('.productThumbnailEdit').attr('src', imageThumbnail);
        $('.featureImageCropper').attr('src' , imageThumbnail);
        $('.viewImagePrettyPhoto').attr('href' , imageThumbnail);
        // $('#imagefeaturesrc').attr('data-value' , imageId);
        // $('#imagefeaturesrc').attr('data-image-id' , imageId);
        // $('.featureImage_type').val(imageId);
        
            $.ajax({
            type: 'POST',
            url: '/edit-set-selected-feature-image',
            data: {
                imageId: imageId, productId: productId
            },
            // success: function(response) {
             
            //     toastr.options = {
            //     closeButton: true, // Add close button
            //     disableTimeOut: true,
            //     tapToDismiss: true,
            //     positionClass: 'toast-top-right',
            //     toastClass: 'toast-icon custom-toast-success'
            //  };
            //      toastr.success(response.message);
            // },
            error: function(xhr, textStatus, errorThrown) {
                console.log('Error: ' + errorThrown); // handle any errors that occur
                swal("Error!", errorThrown, "error");
            },
            complete: function() {
            isRequestInProgress = false; // Reset the flag when the request is completed
        }
        });
    });

</script>