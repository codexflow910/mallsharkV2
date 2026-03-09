<p class="variation_image_text_note">The selected image will be assigned after all required fields have been filled and the changes have been saved/updated  by clicking on the button at the bottom.</p>
<?php
$counterGallery = rand(9999, 999999999);

if (isset($productInfo) && is_array($productInfo)) {
    if ($productInfo['gallery']) {
        // dump("va",$productInfo['gallery']);
        foreach ($productInfo['gallery'] as $imageID) {
            $imageGalleryUrl = getAttachmentUrlById((int) $imageID);
            // echo 'Data: <pre>' .print_r($imageGalleryUrl,true). '</pre>'; die();
            $isChecked = ($variationImageId == $imageID) ? "checked" : "";
            $borderClass = '';
            if ($isChecked == 'checked') {
                $borderClass  = 'border-class';
            }
                ?>

            <div id="img_" class="image-panel variation_images_insert second_class {{$borderClass}}" data-veriKey="{{$variationKey}}">
                <input id="imagesrc" class="select_variation_image_insert" type="hidden" value="<?php echo $imageID; ?>"
                 data-key="{{$variationKey}}" data-selecter="{{$variationImageId}}" 
                 data-image="product_variation_image_<?php echo $imageID; ?>" name="variation_image">
                <div class="image-inner-mbt">
                    <div class="img-only">

                        <img class="previewClass" src="<?php echo $imageGalleryUrl; ?>" alt="">

                    </div>
                   
                  
                </div>
            </div>


<?php
            $counterGallery++;
        }
    }
}
?>
<style>
    .border-class {
        border: 2px solid #0510fa !important;
    }
    .variation_image_text_note{
        font-size: 10px;
    text-align: center;
    font-style: italic;
    font-weight: 600; 
    }
</style>
<script>
        jQuery('body').on('click', '.variation_images_insert', function(e) {
            
            // variationImage();
            $(".variation_images_insert").removeClass("border-class");
            $(this).addClass("border-class");
            var imageid = $(this).find(".select_variation_image_insert").val();
            var variationKey = $(this).find(".select_variation_image_insert").data('key');
           
            
        $('.product_variation_image_specialcase').each(function(key, index) {
            var elementKey = $(this).data('variationimage-key');
            
            if (key === variationKey) {
            
                $(this).val(imageid);
                $(this).siblings().attr('data-variationimage-id', imageid);
            } else if (!$(this).val()) {
                console.log("Preserving empty value for an element with key " + elementKey + ": " + $(this).attr("name"));
            }
        });

    });

</script>