<link href="{{ URL::asset('assets/css/prettyPhoto.css' ,env('HTTPS_STATUS'))}}" rel="stylesheet" type="text/css" media="screen" />
<link href="{{ URL::asset('assets/image-cropper/css/cropper.css' ,env('HTTPS_STATUS'))}}" rel="stylesheet" type="text/css" media="screen" />
<link href="{{ URL::asset('assets/image-cropper/css/main.css' ,env('HTTPS_STATUS'))}}" rel="stylesheet" type="text/css" media="screen" />


{{ csrf_field() }}
<style>
    body .bootstrap-tagsinput .tag {
        background-color: #e9e2e2b8;
        color: #111010;

    }

    .select_primary_image {
        position: relative !important;
        width: 20px;
        height: 20px;
        left: 0px !important;
    }

    .select_variation_image {
        position: relative !important;
        width: 20px;
        height: 20px;
        left: 0px !important;
    }

    .border-class {
        border: 2px solid #0510fa !important;
    }
    .dropdown-item{
        display: flex!important;
        align-items: center;
        font-size: 14px;
    }
    .chkboxWidth{
        width: 5%;
        margin-right: 15px; 
        transform: scale(1.5);
    }
    .btn-secondary:not(:disabled):not(.disabled).active, .btn-secondary:not(:disabled):not(.disabled):active, #sampleDropdownMenu{
        color: #495561!important;
        background-color: #ffffff!important;
        border: 1px solid #073ba5!important;
        width: 100%;
        text-align: start;
        font-size: 14px;
    }
    .btn-secondary.focus, .btn-secondary:focus{
        box-shadow: none!important;
    }
    .dropdown-menu.show{
        width: 100%;
    }
    #sampleDropdownMenu::after{
        float: right;
        margin-top: 10px;
    }
    
</style>
<script>
    var mode = "<?php echo isset($mode) ? $mode : ''; ?>";
    var productType = "<?php echo isset($prodcutDetails['product_type']) ? $prodcutDetails['product_type'] : ''; ?>";
</script>
<?php
// echo '$prodcutDetails: <pre>' .print_r($prodcutDetails,true). '</pre>'; die;
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

$userShare = isset($prodcutDetails['user_revenue']) ? $prodcutDetails['user_revenue'] : '';
$cashbackPool = isset($prodcutDetails['cashback_pool']) ? $prodcutDetails['cashback_pool'] : '';
$supersupporterPool = isset($prodcutDetails['supersupporter_pool']) ? $prodcutDetails['supersupporter_pool'] : '';
$productType = isset($prodcutDetails['product_type']) ? $prodcutDetails['product_type'] : '';
$weight_type = isset($prodcutDetails['weight_type']) ? $prodcutDetails['weight_type'] : '';
$dimention_type = isset($prodcutDetails['dimention_type']) ? $prodcutDetails['dimention_type'] : '';
$delivery_days = isset($prodcutDetails['delivery_days']) ? $prodcutDetails['delivery_days'] : '';
?>

<h6>Keywords</h6>
<p style="display:none;">What words might someone use to search for your listing, Use all 10 tags to get found. (And also used for SEO)</p>

<div class="row">
    <div class="col-md-7 rghtexp">
        <label class="labled-item" style="display:none;"></label>
        <div class="form-group width538 tagsinput">
            <?php
            $productTags = array();
            $tags = '';
            $counterTags = 0;
            if (isset($prodcutDetails['tags'])) {
                foreach ($prodcutDetails['tags'] as $key => $value) {
                    $productTags[] = $value->tag_title;
                }
                $counterTags = count($productTags);
                $tags = implode(',', $productTags);
            }
            ?>

            <input type="text" class="form-control tagsfld" name="productTags" value="<?php echo !empty($tags) ? $tags : ''; ?>" id="productTagname" data-role="tagsinput" placeholder="Choose up to 10 keywords to improve product discovery" >
            <button class="btn btn-primary btn-simple" type="button" id="addtag"><i class="fa fa-plus" aria-hidden="true"></i></button><span class="small"><i class="ProducttagsCounter"><?php echo (10 - $counterTags); ?></i> Left</span>
        </div>
    </div>
    <div class="col-md-5">
        <div class="dropdown">
            <button class="btn btn-secondary dropdown-toggle" type="button" id="sampleDropdownMenu" data-toggle="dropdown">
                Eco Friendly
            </button>
            <div class="dropdown-menu">
                <?php
                $selectedEcoFriendly = isset($prodcutDetails['eco_friendly']) ? json_decode($prodcutDetails['eco_friendly'], true) : [];
                ?>
                <button class="dropdown-item" type="button">
                    <input type="checkbox" class="chkboxWidth" value="Cruelty-Free"
                    <?php echo in_array('Cruelty-Free', $selectedEcoFriendly) ? 'checked' : ''; ?>> Cruelty-Free
                </button>
                <button class="dropdown-item" type="button">
                    <input type="checkbox" class="chkboxWidth" value="Vegan"
                    <?php echo in_array('Vegan', $selectedEcoFriendly) ? 'checked' : ''; ?>> Vegan
                </button>
                <button class="dropdown-item" type="button">
                    <input type="checkbox" class="chkboxWidth" value="Plastic-Free"
                    <?php echo in_array('Plastic-Free', $selectedEcoFriendly) ? 'checked' : ''; ?>> Plastic-Free
                </button>
                <button class="dropdown-item" type="button">
                    <input type="checkbox" class="chkboxWidth" value="Biodegradable"
                    <?php echo in_array('Biodegradable', $selectedEcoFriendly) ? 'checked' : ''; ?>> Biodegradable
                </button>
                <button class="dropdown-item" type="button">
                    <input type="checkbox" class="chkboxWidth" value="Recycled Materials"
                    <?php echo in_array('Recycled Materials', $selectedEcoFriendly) ? 'checked' : ''; ?>> Recycled Materials
                </button>
                <button class="dropdown-item" type="button">
                    <input type="checkbox" class="chkboxWidth" value="Renewable Resources"
                    <?php echo in_array('Renewable Resources', $selectedEcoFriendly) ? 'checked' : ''; ?>> Renewable Resources
                </button>
                <button class="dropdown-item" type="button">
                    <input type="checkbox" class="chkboxWidth" value="Energy Efficient Production"
                    <?php echo in_array('Energy Efficient Production', $selectedEcoFriendly) ? 'checked' : ''; ?>> Energy Efficient Production
                </button>
                <button class="dropdown-item" type="button">
                    <input type="checkbox" class="chkboxWidth" value="Carbon Neutral"
                    <?php echo in_array('Carbon Neutral', $selectedEcoFriendly) ? 'checked' : ''; ?>> Carbon Neutral
                </button>
            </div>
        </div>
        <input type="hidden" name="eco_friendly" id="ecoFriendlyTags" value="">
    </div>
</div>
<div class="row">
    <?php
    $typeArray = array('simple', 'virtual');
    if (empty($prodcutPrice) || (isset($prodcutDetails['product_type']) && in_array($prodcutDetails['product_type'], $typeArray))) { ?>
            
        <div class="col-md-7 rghtexp simple colors virtual" style="margin-bottom: 18px;">
                <div class="simple colors virtual ">
                    <h6>Pricing *</h6>
                    <p style="display:none;">Make sure to price your products competitively. </p>
                </div>                

                    <!--<label class="labled-item"></label>-->
                    <input type="text" id="productPrice" name="productPrice" class="form-control" value="<?php echo $prodcutPrice; ?>" placeholder="What pricing you use" onkeypress='Numvalidate(event, "productPrice")' maxlength="10" />

                    <span class="price-error d-none">Price value is invalid</span>
        </div>
        
    <?php } else { ?>
        <input type="hidden" name="productvarientPrice" value="<?php echo $prodcutPrice; ?>" />
    <?php } ?>

    <?php // }      
    ?>
    <?php if (empty($prodcutDetails) || (isset($prodcutDetails) && $prodcutDetails['product_type'] == 'simple')) { ?>
        <div class="col-md-5 rghtexp simple colors">
            <!--<label class="labled-item"></label>-->
                    <div class="lftexp simple colors ">
                        <h6>Stock on Hand *</h6>
                        <p style="display:none;">Product will keep displaying in the listing untill stock ends.</p>
                    </div>

                    <input type="text" placeholder="Product will keep displaying in the listing untill stock ends." onkeypress="return isNumber(event)" id="productcurrentstock" name="productcurrentstock" value="<?php echo $prodcutCurrentStock; ?>" class="form-control"  />

                    <span class="stock-error d-none">Stock value is invalid</span>
        </div>

        <div class="col-md-7 rghtexp simple colors" style="margin-bottom: 19px;">
            <!--<label class="labled-item"></label>-->  
                <div class="lftexp simple colors ">
                    <h6>SKU </h6>
                    <p style="display:none;">SKUs are for your use only, buyers won't see them.</p>
                </div>

                    <input type="text" id="productSKU" name="productSKU" value="<?php echo $prodcutSKU; ?>" class="form-control" maxlength="20" placeholder="Enter Sku" />

        </div>
        <div class="col-md-4 rghtexp">
            <h6>Select Delivery Days</h6>
            <select name="delivery_days" id="" class="form-control">
            <option value="1" {{ $delivery_days == '1' ? 'selected' : '' }}>1</option>
            <option value="2" {{ $delivery_days == '2' ? 'selected' : '' }}>2</option>
            <option value="3" {{ $delivery_days == '3' ? 'selected' : '' }}>3</option>
            <option value="4" {{ $delivery_days == '4' ? 'selected' : '' }}>4</option>
            <option value="5" {{ $delivery_days == '5' ? 'selected' : 'selected' }}>5</option>
            <option value="6" {{ $delivery_days == '6' ? 'selected' : '' }}>6</option>
            <option value="7" {{ $delivery_days == '7' ? 'selected' : '' }}>7</option>
            <option value="8" {{ $delivery_days == '8' ? 'selected' : '' }}>8</option>
            <option value="9" {{ $delivery_days == '9' ? 'selected' : '' }}>9</option>
            <option value="10" {{ $delivery_days == '10' ? 'selected' : '' }}>10</option>
                
            </select>
        </div>
    <?php } ?>

</div>



<div class="row photoSection">
    <div class="col-md-12 lftexp ">
        <h6 style="margin-bottom: 5px;">Product Video</h6>
        <p>
            Select a video from youtube. You may have to upload the video first.</p>
        <p>Tips:<br>Try and make the video short. Center your video around the story, not the Sale. Educate your customers with this video. Make your video feature the best 10 seconds ever. One fifth of your viewers will click away from a video within 10 seconds or less. Short and to the point – that’s what we recommend. </p>
    </div>
    <div class="col-md-12 rghtexp ">
        <div class="justify-content-between row">
            <!--<label class="labled-item col-sm-6"></label>-->

        </div>

        <div class="form-group video-custom-panel-style" style="margin-bottom: 0px;">
            <label class="custom-file-upload mt-20" style="margin-top:10px!important;">
                Upload video
                <!-- <input type="file" name="file_upload" id="file_upload" class="form-control" onchange="readFile(this)"> -->
                <input type="button" data-toggle="modal" data-target="#video-popup" style="cursor: pointer !important; display: none;">
            </label>
            <p class="ligt-grey mt-10 mb-0 fnt-12" id="imageUploadCounter"></p>
        </div>
    </div>
</div>
<div id="videoContainer" class="col-md-4 uploadVideoResponse" style="padding:0px;margin-top:20px;" <?php
                                            if (isset($prodcutDetails['video_url'])) {
                                                echo 'style="display: block;"';
                                            } else {
                                                echo 'style="display: none;"';
                                            }
                                            ?>>
                             <?php echo isset($prodcutDetails['videoUrlSource']) ? $prodcutDetails['videoUrlSource'] : ""; ?>
                            </div>
                            <div class="row">
                                <div class="col-md-12 lftexp">
                                    <h6 style="margin-bottom: 9px;margin-top:20px;">Product Short Description  </h6>
                                    <p style="display:none;">Tell buyers a bit about your process or the story behind this item. But it helps for SEO</p>
                                </div>
                                <div class="col-md-12 rghtexp">
                                    <!--<label class="labled-item"></label>-->
                                    <div class="form-group">
                                        <textarea class="form-control producsdes" rows="5" maxlength="500" id="productShortDescription" name="productShortDescription" placeholder="This Description will appear next to your product and pricing information. Be sure to lighlight features here."><?php echo $prodcutShortDescription; ?></textarea>
                                    </div>
                                    <div id="charCount"></div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-12 lftexp">
                                    <h6 style="margin-bottom: 9px;">Product Description</h6>
                                    <p style="display:none;">Start with a brief overview that describes your item's finest features. Tell buyers a bit about your process or the story behind this item.</p>
                                </div>
                                <div class="col-md-12 rghtexp" id="producDescriptionDiv">
                                    <!--<label class="labled-item"></label>-->
                                    <div class="form-group">
                                        <textarea class="form-control producsdes" id="producsdes" rows="5" onkeypress='alphaNumvalidate(event, "producDescription", 1000)' maxlength="1000" name="productDescription" placeholder="This description will appeaar in the description area. Start will a brief overview that describes you item's feature. Provide a clear and concise explanation of the product and its features. This should include technical details such as material used, components included, and any relevant certification or safety features. Additionally, you should highlight the product's unique selling points. Provide informatioon on the product's warranty, return policy, and customer supportt can also can also help build trust with potential buyers. Tip: Utilize keywords here to help with product discovery."><?php echo $prodcutDescription; ?></textarea>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                @php
                                $class = 'hide-me';
                                $wclss = '';
                                if($productType == 'virtual') {
                                $class = '';
                                $wclss = 'hide-me';
                                }
                                @endphp
            <div class="col-md-12 lftexp virtual-hide {{$wclss}}">
                <h6 style="margin-bottom: 9px;">Product Dimensions</h6>
                <p style="display:none;">Enter the size and weight of the item. </p>

                <div class="row">
                <div class="col-md-6">
                        {{-- <label for="dimention_type" class="virtual-hide" style="margin-top:10px;font-size: 14px;display:none;"></label> --}}
                        <select name="dimention_type" id="dimensionstype" class="dwtype form-control virtual-hide">
                            {{-- <option value=""  disabled>Choose a dimension type</option> --}}
                            <option  {{($dimention_type) == 'inch' ? 'selected':''}} value="inch">Inch</option>
                            <option {{($dimention_type) == 'cm' ? 'selected':''}} value="cm">Centimeters</option>
                            <option {{($dimention_type) == 'mm' ? 'selected':''}} value="mm">Millimeters</option>
                        </select>
                    </div>
                    <div class="col-md-6 mm-b15">
                        {{-- <label class="virtual-hide" for="weighttype" style="font-size: 14px; display:none;"></label> --}}
                        <select name="weight_type" id="weighttype" class="dwtype form-control virtual-hide">
                            {{-- <option value=""  disabled> Choose a weight type</option> --}}
                            <option  {{($weight_type) == 'oz' ? 'selected':''}} value="oz">Ounce</option>
                            <option {{($weight_type) == 'pound' ? 'selected':''}} value="pound">Pounds</option>
                        </select>
                    </div>
                    
                </div>
            </div>
    <label class="labled-item"></label>
    <div class="col-md-12 rghtexp virtual-hide {{$wclss}}">
        <input type="hidden" name="maxquantitylimit" class="maxquantitylimit" id="maxquantitylimit" value="">
        <div class="row">
            <div class="col-md-3 small-lable">
                <div class="form-group">
                    <label style="opacity:0;" class="hideZero">Length </label>
                    <input onkeypress='return isNumber(event)' value="<?php echo $productSizeLength; ?>" type="text" class="form-control" id="productSize_length" name="productSize_length" placeholder="Length">
                </div>
            </div>
            <div class="col-md-3 small-lable">
                <div class="form-group">
                    <label style="opacity:0;" class="hideZero">Width </label>
                    <input onkeypress='return isNumber(event)' value="<?php echo $productSizeWidth; ?>" type="text" class="form-control" id="productSize_width" name="productSize_width" placeholder="Width">
                </div>
            </div>
            <div class="col-md-3 small-lable">
                <div class="form-group">
                    <label style="opacity:0;" class="hideZero">Height </label>
                    <input onkeypress='return isNumber(event)' value="<?php echo $productSizeHeight; ?>" type="text" class="form-control" id="productSize_height" name="productSize_height" placeholder="Height">
                </div>
            </div>
            <div class="col-md-3 small-lable">
                <label class="labled-item hideZero" style="opacity:0;">Weight (OZ) *</label>
                <div class="form-group">
                    <input onkeypress='return isNumber(event)' value="<?php echo $prodcutWeight; ?>" type="text" class="form-control" id="productWeight" name="productWeight" required placeholder="Weight *">
                    <span class="weight-error d-none">Weight is invalid</span>
            </div>
        </div>
    </div>
    <div class="col-md-12 lftexp hide-div ">
        <h6>weight (lbs) *</h6>
    </div>
    <div class="col-md-12 rghtexp">

        <div id="video-popup" class="modal fade popup-style" role="dialog">
            <div class="modal-dialog">
                <!-- Modal content-->
                <div class="modal-content clearfix">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <h4 class="modal-title">Upload Video (YouTube Only)</h4>
                    </div>
                    <div class="modal-body">
                        <div class="uploadariation"><span></span></div>
                        <div class="col-group col-flush mb-xs-3">
                            <small class="text-muted">Put video URL here i.e. https://www.youtube.com/watch?v=YTR21os8gTA</small>
                            <div class="add-media-video-popup mt-1">
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group" style="margin-bottom:0px;">
                                            <input type="text" class="form-control" id="media_video_url" name="media_video_url" placeholder="URL">
                                            <span class="error"></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer" style="padding: 16px 0px 0px 0px;">
                            <button type="button" class="btn btn-default btn-primary-back large-btn-mbt btn-simple " id="viewVideoBtn">Update</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @php

        $shippingOptionId = isset($prodcutDetails['shipping_option_id']) ? $prodcutDetails['shipping_option_id'] : '';
        $shippingData = getShippingOptionMethodById($shippingOptionId);
        $flat_rate = !empty($shippingData['shippingOptions']->flat_shipping_rate) ? $shippingData['shippingOptions']->flat_shipping_rate :'';
       
        $service = 'united-state';
        if(!empty($flat_rate)) {
        $service ="other";
        }
        @endphp
        <input type="hidden" name="store_shiping_method" id="store_shiping_method" value="{{$service}}">
        <input type="hidden" name="redirect" value="<?php echo isset($_GET['stock']) ? 'stock_update' : ""; ?>">
        <script src="https://cdn.ckeditor.com/4.22.1/standard/ckeditor.js"></script>
        <script>
    CKEDITOR.config.versionCheck = false;
    </script>
        <script src="{{ URL::asset('assets/js/product-script.js' ,env('HTTPS_STATUS'))}}" type="text/javascript" charset="utf-8"></script>
        <script src="{{ URL::asset('assets/image-cropper/js/cropper.min.js' ,env('HTTPS_STATUS'))}}" type="text/javascript" charset="utf-8"></script>
        <script src="{{ URL::asset('assets/js/jquery.prettyPhoto.js',env('HTTPS_STATUS')) }}"></script>


        <script>

            // $(document).ready(function() {
            // // Get a reference to the textarea and the character count display
            // const $textarea = $("#productShortDescription");
            // const $charCountDisplay = $("#charCount");

            // // Define the maximum allowed length
            // const maxLength = 100;

            // // Add an input event handler to the textarea
            // $textarea.on("input", function () {
            //     // Get the current length of the textarea's value
            //     const currentLength = $textarea.val().length;

            //     // Update the character count display
            //     $charCountDisplay.text(`${currentLength} / ${maxLength}`);

            //     // Check if the current length exceeds the maximum length
            //     if (currentLength > maxLength) {
            //     // If it does, truncate the text to the maximum length
            //     $textarea.val($textarea.val().slice(0, maxLength));
            //     }
            // });
            // });

            CKEDITOR.replace('producsdes');
            CKEDITOR.config.allowedContent = true;
            CKEDITOR.config.versionCheck = false;
           



      // Make sure CKEditor is properly loaded before using it
      


            // CKEDITOR.replace('productShortDescription');
            // var editor = CKEDITOR.replace('productShortDescription',{
            //     maxLength: 10, 
            // });
            // editor.on("instanceReady", function(){
            //     this.document.on("keyup", ck_jq);
            //     this.document.on("paste", ck_jq);
            // });

            // function ck_jq()
            // {
            //     var len = CKEDITOR.instances['productShortDescription'].getData().replace(/<("[^"]*"|'[^']*'|[^'">])*>/gi, '').replace(/^\s+|\s+$/g, '');
            //     $("#count").val(len.length);
            // }
    // var editor = CKEDITOR.instances['productShortDescription'];

    // editor.on('key', function (evt) {
    //     var maxLength = 100; // Maximum length you want to enforce
    //     var content = editor.getData().replace(/<[^>]*>/g, '');

    //       // Ignore the backspace key (keyCode 8)
    //       if (evt.data.keyCode === 8) {
    //         return;
    //     }
        
    //     if (content.length > maxLength) {
    //         toastr.options = {
    //             closeButton: true,
    //             tapToDismiss: true,
    //             positionClass: 'toast-top-right',
    //             toastClass: 'toast-icon custom-toast-success',
    //             progressBar: true,
    //         };
            
            
    //         // Prevent further input by removing the last typed character
    //         editor.setData(content.substring(0, maxLength));
    //     }
    // });
        </script>

        <script>
            $(document).ready(function() {
                var videoContainer = $("#videoContainer");
                if ($.trim(videoContainer.html()) === "") {
                    videoContainer.hide();
                } else {
                    videoContainer.show();
                }
            });
        </script>

        <script>
            $(document).on('click', 'body *', function() {
                $(".numaric-only,.price-field,.sku-field,.avl-stock-field").on("keypress keyup blur", function(event) {
                    //this.value = this.value.replace(/[^0-9\.]/g,'');
                    $(this).val($(this).val().replace(/[^0-9\.\b]/g, ''));
                    if ((event.which != 46 || $(this).val().indexOf('.') != -1) && (event.which < 48 || event.which > 57)) {
                        event.preventDefault();
                    }

                });
            });
            var getImageval = '';
            var hidImage = '';
             var imageSelector = '';
            $(document).on('click', '.showImagePopup', function() {

                $('#showproductmodal').modal('show');
              
                let context = $(this);
                hidImage = $(this).next('.product_variation_image');
                imageSelector = context.attr('data-value');
                let imageKey = $(this).attr('data-variationkey');
                $('.variation_images_create').each(function(key, index){
                    $(this).find(".select_variation_image_create").attr('data-variation-selector', imageKey);
                });
                console.log()
                // var inserted_value  =  $('.product_variation_image').val();
             
                     $('.select_variation_image_create').each(function(key, index) {
                
                    var selected_val = $(this).val();
                    if(selected_val == imageSelector ){
                            
                        $(this).parent().parent().addClass("border-class");
                    }else{
                        $(this).parent().parent().removeClass("border-class");
                    }
                });
          
                // getImageValue();
                
            });

            // Product Thumbnail (feature image)
            $(document).on('click', '.selectFeatureImageAdd', function() {
            
                $('#showproductFeaturemodal').modal('show');
            });
             //Select Product Thumbnail (feature image choise)
            $(document).on('click', '.select_feature_image', function() {
          
                $('.select_feature_image').parent().parent().removeClass('border-class');
                $(this).parent().parent().addClass('border-class');

                var imagePathComplete = $(this).attr('data-image-path');
                var imagePath = $(this).attr('data-featrue-image'); 
                var imageCropper = $(this).closest('.selectFeatureImages').find('.editEvent').attr('data-target');
                $('.product-thumbnail-image').removeClass('d-none');
                $('.set-feature-image').removeClass('d-none');
                $('.product-thumbnail-image').attr('src', imagePathComplete);
                $('.featureaddCropperEvent').removeClass('d-none');
                $('.featureaddCropperEvent').attr('data-target', imageCropper);
                const parts = imageCropper.split('#');
                const idCropper = parts[1];
                var productThumbnail = document.getElementById('showFeatureImagePopup');
                var htmlThumbnail = '';
                htmlThumbnail += '<div id="img_123" class="image-panel">';
                htmlThumbnail += '<div class="image-inner-mbt"><div class="img-only">';
                htmlThumbnail += '<a class="viewImages feature-image-preview" rel="prettyPhoto" href="javascript:void(0)">';
                // htmlThumbnail += '<a class="viewImages" rel="prettyPhoto" href="'+imagePathComplete+'">';
                htmlThumbnail += '<img  class="previewClass" src= "'+imagePathComplete+'" />';
                htmlThumbnail += '</a>';
                htmlThumbnail += '</div><div class="loading-image"><span></span></div> ';
                htmlThumbnail += '<input id="imageFeatureSrc" type="hidden" value= "' + imagePath + '" name="feature_image"/> ';
                //htmlThumbnail += '<div class="dele-icn">';
                //htmlThumbnail += '<a class="editEvent" href="javascript:;"  data-toggle="modal" data-target="'+imageCropper+'" >';
                //htmlThumbnail += '<i class="fa fa-crop" aria-hidden="true"></i></a>&nbsp;&nbsp;';
                htmlThumbnail += '<div class="modal" id="'+idCropper+'" tabindex="-1" role="dialog" aria-hidden="true" >';
                htmlThumbnail += '<div class="modal-dialog animated bounceInDown" style="max-width:800px"> <div class="modal-content"> ';
                htmlThumbnail += '<div class="modal-body"> <div class="row imagecrop-area"> <div class="col-xs-12 col-sm-12"> <div class="img-container"> ';
                htmlThumbnail += ' <img class="Cropperimage" src="'+imagePathComplete+'" alt="Picture"> </div>';
                htmlThumbnail += '<div class="clearfix"></div><br></div><div class="clearfix spacer20"></div><div class="col-md-12 docs-toggles"> ';
                htmlThumbnail += '</div></div></div>';
                htmlThumbnail += '<div class="modal-footer"> <button data-dismiss="modal" class="btn btn-primary btn-simple" type="button">Close</button> <button class="btn-primary btn  text-white btn-success getCropImage" type="button" data-dismiss="modal">Save</button> </div></div></div></div></div></div> </div>';
                
                $('#showFeatureImagePopup').html(htmlThumbnail);

            });


            // $(document).on('click', '.select_variation_image', function() {
            //     var value = $(this).val();
            //     hidImage.val(value);
                
            // });
            jQuery('body').on('click', '.variation_images', function(e) {
                let context = '';
            // variationImage();
            $(".variation_images").removeClass("border-class");

            $(this).addClass("border-class");
            let imageid = $(this).find(".select_variation_image_create").val();
            let variationKey = $(this).find(".select_variation_image_create").attr('data-variation-selector');
                      console.log(imageid);
            //  hidImage.val(imageid);
            //  context.attr('data-value', imageid);
            
                $('.product_variation_image').each(function(key, index) {
                   
                   
                    if (key == parseInt(variationKey)) {
                       
                       
                        $(this).val(imageid);
                        $(this).siblings().attr('data-value', imageid);
                    } else if (!$(this).val()) {
                        console.log("Preserving empty value for an element with key " + imageid + ": " + $(this).attr("name"));
                    }
                });
          
             
            });
      
        

    $(document).ready(function() {
        $('.showVeriationImage').click(function() {
           
           // Get the order_id from the data attribute
           var productId = $(this).attr('data-product-id');
           let variationImageId = $(this).attr('data-variationimage-id');
           
           var variationKey = $(this).attr('data-variationimage-key');
            $.ajax({
                url: '/get-variation-details',
                type: 'POST',
                data: {
                    productId: productId , variationImageId:variationImageId, variationKey:variationKey
                },
                success: function(data) {
                 

                // Update the modal content with the order details
                $('#productModalBody').html(data);

                // add class to the element where image id matchessa
                $('.select_variation_image_insert').each(function(key, index) {

                    var selected_val = $(this).val();
                    if(selected_val == variationImageId ){
                            
                        $(this).parent().addClass("border-class");
                    }else{
                        $(this).parent().removeClass("border-class");
                    }
                });


                // Show the modal
                $('#prodcutModal').modal('show');
                
                },
                error: function() {
                alert('Failed to fetch product details.');
                }
            });
        });


        $('.showFeatureImage').click(function() {
           
           // Get the order_id from the data attribute
           var productId = $(this).attr('data-product-id');
           var productFeature = $(this).attr('data-productFeature-id');
        
            $.ajax({
                url: '/edit-select-feature-image',
                type: 'POST',
                data: {
                    productId: productId, productFeature: productFeature
                },
                success: function(data) {
                 

                // Update the modal content with the order details
                $('#productFeatureModalBody').html(data);

                // add class to the element where image id matchessa
                $('.select_variation_image_insert').each(function(key, index) {

                    var selected_val = $(this).val();
                    if(selected_val == productFeature){
                            
                        $(this).parent().addClass("border-class");
                    }else{
                        $(this).parent().removeClass("border-class");
                    }
                });


                // Show the modal
                $('#prodcutFeatureModal').modal('show');
                
                },
                error: function() {
                alert('Failed to fetch order details.');
                }
            });
        });

        
    jQuery('body').on('click', '.showImagePopupdata', function(e) {

           // Get the order_id from the data attribute
           var productId = $(this).attr('data-product-id');
           let variationImageId = $(this).attr('data-variationimage-id');
          
           
           var variationKey = $(this).attr('data-variationkey');
            $.ajax({
                url: '/edit-variation-details',
                type: 'POST',
                data: {
                    productId: productId , variationImageId:variationImageId, variationKey:variationKey
                },

                success: function(data) {
                 

                // Update the modal content with the order details
                $('#productModalBody').html(data);

                // add class to the element where image id matchessa
                $('.select_variation_image_insert').each(function(key, index) {

                    var selected_val = $(this).val();
                    if(selected_val == variationImageId ){
                            
                        $(this).parent().addClass("border-class");
                    }else{
                        $(this).parent().removeClass("border-class");
                    }
                });


                // Show the modal
                $('#prodcutModal').modal('show');
                
                },
                error: function() {
                alert('Failed to fetch order details.');
                }
            });
        });

            
       
        
            $('body').on('click', '.delete-parent-image', function() {
            let removeElement = $(this).closest('.ProductImageDelete');
            let image_panel = $('.product-image-panel').length;
           if(image_panel < 2){
                toastr.options = {
                        closeButton: true, 
                        disableTimeOut: true,
                        tapToDismiss: true,
                        positionClass: 'toast-top-right',
                        toastClass: 'toast-icon custom-toast-success'
                        };
                toastr.error("Minimum one image required for feature selection");
                return;
           }
           
            
           // Get the order_id from the data attribute
           var productId = $(this).attr('data-product-id');
           var imageId = $(this).attr('data-image-id');
                
            $.ajax({
                url: '/deleteEditableProductImage',
                type: 'POST',
                data: {
                    productId: productId , imageId:imageId
                },
                success: function(data) {

                    toastr.options = {
                    closeButton: true, // Add close button
                    disableTimeOut: true,
                    tapToDismiss: true,
                    positionClass: 'toast-top-right',
                    toastClass: 'toast-icon custom-toast-success'
                    };
                     toastr.success(data);
                    
                    removeElement.remove();

                    $('.product_variation_image_new').each(function(){
                    let imagepathValue = $(this).val();
                    if(imagepathValue == imageId){
                        
                        $(this).val('');

                    }
                    });

                    var featureImg = $('.featureImage_type').val();
                   
                    if(imageId == featureImg){
                        $('#productThumbnailEdit').val('');      

                        $('.productThumbnailEdit').attr('src', '');
                        $('.productThumbnailEdit').attr('alt' , '');
                        $('.productThumbnailEdit').attr('title' , '');
                        $('.viewImagePrettyPhoto').attr('href' , '');
                    }
                },
                error: function() {
                alert('Failed to fetch order details.');
                }
            });
        });


        // Edit variation image select start
        // Edit variation image select End

        $('.editProductImage').change(function() {

           let formData = new FormData();
            let images = $('.editProductImage')[0].files;
            let productId = $('#productId').val();
            formData.append('product_id', productId);

            for (let i = 0; i < images.length; i++) {
                let fileExtension = images[i].name.split('.').pop().toLowerCase();
                if (['png', 'jpeg', 'jpg'].includes(fileExtension)) {
                    formData.append('productimages[]', images[i]);
                }else{
                    swal('File with disallowed extension (' + fileExtension + ') was skipped: ' + images[i].name);
                    return;
                }
            }
            
            $.ajax({
                url: '/editProductImageUpload',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function (response) {
                   
                        
                  
                    let attachmentIds = response;
                    var imagDiv = document.getElementById('all-uplpad-image');
                    $('body').find('.photoSection .uploadImages').delay(1000).hide();
                    for (let i = 0; i < attachmentIds.length; i++) {
                        
                        // Call your controller function to get the image URL
                        $.ajax({
                            url: '/rendorImagePath/' + attachmentIds[i], // Replace with your route
                            method: 'GET',
                            success: function (imageUrl) {
                                var html = '';
                               
                                randomCode = randomNumberFromRange(101, 99999999999);
                                // Append each image to the imagePreview container
                                html += '<div id="img_' + randomCode + '" class="image-panel product-image-panel ProductImageDelete">';
                                html += '<div class="image-inner-mbt"><div class="img-only">';
                                html += '<a class="viewImages" rel="prettyPhoto" href="' + imageUrl + '">';
                                html += '<img  class="previewClass" src="' + imageUrl + '" />';
                                html += '</a>';
                                html += '</div><div class="loading-image"><span></span></div> ';
                                html += '<input id="imagesrc' + randomCode + '" type="hidden" value= "' + attachmentIds[i] + '" name="productImage[]" class="prev-image-url"/> ';
                                html += '<div class="dele-icn">';
                                html += '<a href="javascript:;" class="delete-parent-image" data-image-id="'+attachmentIds[i]+'" data-product-id="'+productId+'" >';
                                html += '<i class="fa fa-trash-o" aria-hidden="true"></i></a>';
                                html += '</div><div class="modal" id="cropper_' + randomCode + '" tabindex="-1" role="dialog" aria-hidden="true" >';
                                html += '<div class="modal-dialog animated bounceInDown" style="max-width:800px"> <div class="modal-content"> ';
                                html += '<div class="modal-body"> <div class="row imagecrop-area"> <div class="col-xs-12 col-sm-12"> <div class="img-container"> ';
                                html += ' <img class="Cropperimage" src="' + imageUrl + '" alt="Picture"> </div>';
                                html += '<div class="clearfix"></div><br></div><div class="clearfix spacer20"></div><div class="col-md-12 docs-toggles"> ';

                                html += '</div></div></div>';
                                html += '<div class="modal-footer"> <button data-dismiss="modal" class="btn btn-primary btn-simple" type="button">Close</button> <button class="btn-primary btn  text-white btn-success getCropImage" type="button" data-dismiss="modal">Save</button> </div></div></div></div></div></div> </div>';
                                    console.log(html);
                                // imagDiv.insertAdjacentHTML('beforeend', html);
                                imagDiv.insertAdjacentHTML('beforeend', html);
                            },
                            error: function (error) {
                                console.error(error);
                            }
                        });
                    }
               
                },
                error: function() {
                alert('Failed to fetch order details.');
                }
            });
        });
    
    });
    $(document).on("click", "#delete_shipping_profile", function() {
        var row = $(this).closest(".removingShipping");
        $('#save_shipping_custom input[type="text"]').val('');
        $('#save_shipping_custom select').val('');
        $('#save_shipping_custom input[type="checkbox"]').prop('checked', false);
        var res = {}

        var shipping_id = $(this).attr('data-shipping-id');
        res['ship_id'] = shipping_id;
        
        swal({
                title: "Are you sure?",
                text: "Are you sure you want to Delete Shipping Profile",
                type: "warning",
                showCancelButton: true,
                confirmButtonClass: "btn-success",
                confirmButtonText: "Yes, Do it!",
                closeOnConfirm: false
            },
            function() {
                
                $.ajax({
                    type: 'POST',
                    url: '{{url('deleteshipping')}}',
                    data: res,
                    success: function(response) {
                        row.slideUp("slow", function() {
                                row.remove(); // Remove the row after animation
                            });      
                        swal("Success!", response.message, "success");       
                    },
                    error: function(xhr, textStatus, errorThrown) {
                        console.log('Error: ' + errorThrown); // handle any errors that occur
                        swal("Error!", errorThrown, "error");
                    }
                });
                
            });

    });

    $('input[name="optradio"]').change(function() {
    // Check which radio button is checked
    var selectedOption = $('.select_feature_image:checked').val();

});

        </script>
<script>
    jQuery('#productcurrentstock').on('input', function() {
    var stockValue = $(this).val();
    
    // Check if input is greater than 9999 or less than 0
    if (stockValue.length > 4 || parseInt(stockValue) > 9999 || parseInt(stockValue) < 0) {
        $(this).addClass("is-invalid");
        
        // Revert value to 9999 if it exceeds 4 digits
        if (parseInt(stockValue) > 9999) {
            $('.stock-error').removeClass('d-none').addClass('d-block');
            $(this).val(9999).prop('readonly', true);
        } else if (parseInt(stockValue) < 0) {
            // Revert value to 0 if it goes negative
            $('.stock-error').removeClass('d-none').addClass('d-block');
            $(this).val(0).prop('readonly', true);
        }
    } else {
        // If value is valid, remove error state
        $(this).removeClass("is-invalid");
        $('.stock-error').removeClass('d-block').addClass('d-none');
    }
});

// Allow backspace to correct mistakes by removing readonly behavior
$('#productcurrentstock').on('keydown', function(e) {
    if (e.keyCode === 8) {
        $(this).prop('readonly', false);
        $(this).removeClass("is-invalid");
        $('.stock-error').removeClass('d-block').addClass('d-none');
    }
});


jQuery('#productPrice').on('input', function() {
    var priceValue = $(this).val();
    
    // Check if input exceeds 9999 or is negative
    if (priceValue.length > 4 || parseInt(priceValue) > 9999 || parseInt(priceValue) < 0) {
        $(this).addClass("is-invalid");
        
        // Revert value to 9999 if it exceeds 4 digits
        if (parseInt(priceValue) > 9999) {
            $('.price-error').removeClass('d-none').addClass('d-block');
            $(this).val(9999).prop('readonly', true);
        } else if (parseInt(priceValue) < 0) {
            // Revert value to 0 if it goes negative
            $('.price-error').removeClass('d-none').addClass('d-block');
            $(this).val(0).prop('readonly', true);
        }
    } else {
        // Remove error state if the value is valid
        $(this).removeClass("is-invalid");
        $('.price-error').removeClass('d-block').addClass('d-none');
    }
});

// Allow backspace to correct mistakes by removing readonly behavior
$('#productPrice').on('keydown', function(e) {
    if (e.keyCode === 8) {
        $(this).prop('readonly', false);
        $(this).removeClass("is-invalid");
        $('.price-error').removeClass('d-block').addClass('d-none');
    }
});


jQuery('#productWeight').on('input', function() {
    var weightValue = $(this).val();
    
    // Check if input exceeds 999 or is negative
    if (weightValue.length > 3 || parseInt(weightValue) > 999 || parseInt(weightValue) < 0) {
        $(this).addClass("is-invalid");
        
        // Revert value to 999 if it exceeds 3 digits
        if (parseInt(weightValue) > 999) {
            $('.weight-error').removeClass('d-none').addClass('d-block');
            $(this).val(999).prop('readonly', true);
        } else if (parseInt(weightValue) < 0) {
            // Revert value to 0 if it goes negative
            $('.weight-error').removeClass('d-none').addClass('d-block');
            $(this).val(0).prop('readonly', true);
        }
    } else {
        // Remove error state if the value is valid
        $(this).removeClass("is-invalid");
        $('.weight-error').removeClass('d-block').addClass('d-none');
    }
});

// Allow backspace to correct mistakes by removing readonly behavior
$('#productWeight').on('keydown', function(e) {
    if (e.keyCode === 8) {
        $(this).prop('readonly', false);
        $(this).removeClass("is-invalid");
        $('.weight-error').removeClass('d-block').addClass('d-none');
    }
});
</script>

<script>
    $(document).ready(function () {
    function updateEcoFriendlyTags() {
        const selectedValues = [];
        $('.chkboxWidth:checked').each(function () {
            selectedValues.push($(this).val());
        });
        $('#ecoFriendlyTags').val(JSON.stringify(selectedValues));
    }

    $('.chkboxWidth').on('change', function () {
        updateEcoFriendlyTags();
    });
    
    updateEcoFriendlyTags();
});

</script>