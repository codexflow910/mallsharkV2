var quantityvalidator1 = false;
var quantityvalidator2 = false;
var quantityvalidatorDuplicate = false;
var skuvalidator1 = false;
var skuvalidator2 = false;
var skuvalidatorDuplicate = false;
var pricevalidator1 = false;
var pricevalidator2 = false;
var pricevalidatorDuplicate = false;

function isValidHTML(html) {
    // Regular expression to match opening, closing, and self-closing tags
    const pattern = /<\/?([a-zA-Z0-9]+)([^>]*)>/g;
    
    // Stack to hold opening tags
    const stack = [];
    
    // Find all matches for opening, closing, and self-closing tags
    let matches;
    while ((matches = pattern.exec(html)) !== null) {
        const tag = matches[0];
        
        // Check if it's a self-closing tag (like <br />)
        if (tag.endsWith('/>')) {
            continue;  // Ignore self-closing tags
        }

        if (tag.startsWith('</')) {
            // This is a closing tag
            const tagName = tag.match(/^<\/([a-zA-Z0-9]+)/)[1];
            if (stack.length === 0 || stack[stack.length - 1] !== tagName) {
                // Stack is empty or top of the stack doesn't match closing tag
                return false;  // Unmatched closing tag
            }
            stack.pop();  // Pop the matched opening tag
        } else {
            // This is an opening tag
            const tagName = tag.match(/^<([a-zA-Z0-9]+)/)[1];
            stack.push(tagName);  // Push the opening tag onto the stack
        }
    }
    
    // If the stack is empty, all tags were matched
    return stack.length === 0;
}
function decodeHTMLEntities(text) {
    var textarea = document.createElement('textarea');
    textarea.innerHTML = text;
    return textarea.value;
}
jQuery('body').on('click', '#data-ui-edit', function (e) {

    $('.shipping-address-gray.section-insight').show();
    $('.hide-restore-setting').show();
    $(".shipping-address-gray.top-selected").hide();
});
jQuery('body').on('click', '#advanced-option-sec', function (e) {

    $('.advanced-option-body').toggleClass("shoeme-body");
    $(this).parent().addClass('open-body-detail');
});
jQuery('body').on('click', '#restore-defaults', function (e) {

    $('.shipping-address-gray.section-insight').hide();
    $('.shipping-address-gray.top-selected').show();
    $(this).parent().hide();
});
jQuery('body').on('click', '.data-ui-edit-popup', function (e) {

    $('.shipping-popup-modal .shipping-address-gray.section-insight').show();
    $('.shipping-popup-modal .hide-restore-setting').show();
    $(".shipping-popup-modal  .shipping-address-gray.top-selected").hide();
});
jQuery('body').on('click', '.add-shipping-profile', function (e) {
    $('#save_shipping_custom').modal('show');
    
    // $(this).parent().addClass('open-body-detail');
});

jQuery('body').on('click', '.advanced-option-sec-popup', function (e) {

    $('.shipping-popup-modal  .advanced-option-body').toggleClass("shoeme-body");
    $(this).parent().addClass('open-body-detail');
});
jQuery('body').on('click', '.restore-defaults-popup', function (e) {

    $('.shipping-popup-modal  .shipping-address-gray.section-insight').hide();
    $('.shipping-popup-modal  .shipping-address-gray.top-selected').show();
    $(this).parent().hide();
});
jQuery('body').on('click', '.countryCheckBox', function (e) {
    var card = $(this).parentsUntil('.card').parent();
    if ($(this).is(':checked')) {
        card.find('input').prop('checked', true);
    } else {
        card.find('input').prop('checked', false);
    }
});
jQuery('body').on('click', '#all-countries-checkbox', function (e) {

    if ($(this).is(':checked')) {
        $('.countriesListCheckBox input').prop('checked', true);
    } else {
        $('.countriesListCheckBox input').prop('checked', false);
    }
});
jQuery('body').on('keyup', '#causeTitle', function (e) {
    if (e.keyCode == 13) {
        $("#addCauseBtn").trigger("click");
    }
});
jQuery().ready(function (en) {

    $('#select-shipoption').on('change', function () {
        var selectedOption = $(this).val();
        if (selectedOption == 'united-state') {
            $('#add_domestic_free_shipping').prop('checked', false);
            $('input[name="flat_shipping_rate"]').val(0);
        } else {
            $('input[name="edit_ship_service[]').each(function () {
                this.checked = false;
            });
            $('#add_domestic_free_shipping').prop('checked', false);
        }
    });

    setTimeout(function () {
        $('.alert-success-mbt').css('disply', 'none');
    }, 5000);
    
    // Product save as Draft
    jQuery('body').on('click', '.draft_product', function (e) {
        
        fieldsID = jQuery('#productTitle');
            if (fieldsID.val() != "") {
                fieldsID.css('border-color', 'rgba(0, 128, 0, 0.39)');
                $('#addProductForm').submit();
            }else{
                fieldsID.css('border-color', 'red');
                fieldsID.focus();
                return false;
            }
    });
   
    jQuery('body').on('click', '.addProductSubmit , .addProductSubmitFinish', function (e) {
       
       

        
        $('#prductSaved').val($(this).attr('saveproduct'));
        $('.alert-success-mbt').css('disply', 'none');
        var numvalidate = /^[0-9.]*$/;
        var field_error = false;
        var field_counter = 0;
        var ptype = '';
        var productVariationImagesedit = $(".product_variation_image");
        
        let htmlString = CKEDITOR.instances.producsdes.getData();  // Get the content of the CKEditor instance
    
        let decodedHTML = decodeHTMLEntities(htmlString);  // Decode the HTML entities
        // debugger
        // decodedHTML = decodedHTML.replace(/&ZeroWidthSpace;/g, '')  // Remove ZeroWidthSpace
        // .replace(/\u00A0/g, '')           // Remove non-breaking space
        // .replace(/\n/g, ''); 
        
        if (isValidHTML(decodedHTML)) {
            console.log("Valid HTML!");
            $('.cke').css('border-color', '');
        } else {
            $('html, body').animate({
                scrollTop: $('#producDescriptionDiv').offset().top
            }, 'slow');
            $('.cke').css('border-color', 'red');  // Highlight CKEditor instance
            toastr.options = {
                closeButton: true,
                tapToDismiss: true,
                positionClass: 'toast-top-right',
                toastClass: 'toast-icon custom-toast-success',
                progressBar: true,
            };
            toastr.error('Product Descriptipon content is not valid');
            e.preventDefault();
            return false;
        }
        if (mode == 'edit') {

            ptype = prodType;
        } else {
            ptype = $("#Product-type-variant").val();
        }
        if (ptype == 'virtual') {

            $('input[name=previousShippingOptions]').prop('checked', false);

        }
       
        jQuery('#addProductForm').find('.required-field').each(function (index, element) {
         
            var obj = jQuery(this);
            var obj_value = obj.val();
            var obj_id = obj.attr('id');

            if (obj_value == '') {
                field_counter++;
                obj.css('border-color', 'red');
                if (field_counter == 1) {
                    obj.focus();
                }
                field_error = true;
                if (field_error) {
                    console.log(obj.attr('id') + 'Error ')
                }
            } else {
                obj.css('border-color', 'rgba(0, 128, 0, 0.39)');
            }
        });
       
        if ($("#Product-type-variant").val() == 'simple' || productType == 'simple' || ptype == 'virtual') {
            
            if ($("#productPrice").val() == '' || $("#productPrice").val() <= 0 || !numvalidate.test($("#productPrice").val())) {
                $("#productPrice").css('border-color', 'red');
                field_error = true;
                //return false;
            } else {
                $("#productPrice").css('border-color', 'rgba(0, 128, 0, 0.39)');
            }
            if(ptype != 'virtual'){
                if ($("#productcurrentstock").val() == '' || $("#productcurrentstock").val() <= 0 || !numvalidate.test($("#productcurrentstock").val()) || $("#productcurrentstock").val() > 100000) {
                    $("#productcurrentstock").css('border-color', 'red');
                    field_error = true;
                    //return false;
                } else {
                    $("#productcurrentstock").css('border-color', 'rgba(0, 128, 0, 0.39)');
                }
            }
           
           

        } else if ($("#Product-type-variant").val() == 'variant'  || productType == 'variant' || ptype == 'variant') {
            
            var rowCount = $('.tblvrnt tr').length;
            if (rowCount == 0) {
                validateFieldById('addVariationBtn');
                validateFieldById('productTitle');
                validateFieldByClass('custom-panel-style');
                e.preventDefault();
                return false;
            }
            
            
            // Perform further actions based on the validation result
           
            
        
        }
      
        var countryShipSelectedVal = $('#editselect-shipoption').val();
        console.log("condition countryShipSelectedVal", countryShipSelectedVal);
        var editor = CKEDITOR.instances.productShortDescription;
        if (editor) {
            var text = editor.getData().replace(/<[^>]*>/g, ''); // Remove HTML tags
            var charCount = text.length;
            if(charCount > 300){
                field_error = true;
                $("#charCount").html("<p style='color:red;'>Please add 300 characters in length.</p>")
                $("#cke_productShortDescription").css({ 'border': '1px solid #fa0505' });
            }
             
            
        }
        if (ptype != 'virtual') {

            let productSize_length = Math.round($("#productSize_length").val() * 10) / 10;
            let productSize_width = Math.round($("#productSize_width").val() * 10) / 10;
            let productSize_height = Math.round($("#productSize_height").val() * 10) / 10;
            let productWeight = Math.round($("#productWeight").val() * 10) / 10;
            // var shipping_service = $('#store_shiping_method').val();
            let shippingList = $("input[type='radio'][name='previousShippingOptions']:checked");
            shipping_service = shippingList.attr('data-shipping');
            var free_service_edit = $('#domestic_free_shipping').is(":checked");
            var free_service_add = $('#add_domestic_free_shipping').is(":checked");
            console.log("shipping_service", shipping_service);
            
             if ($("#weighttype").val() == '' || $("#weighttype").val() == null) {
                $("#weighttype").css('border-color', 'red');
                field_error = true;
            }
            else {
                $("#weighttype").css('border-color', 'rgba(0, 128, 0, 0.39)');
            }
             if (shipping_service != 'other' && shipping_service != 'free') {
                
                if (productSize_length == 0 && productSize_width == 0 && productSize_height == 0 && productWeight == 0) {
                    productWeightId = jQuery('#productWeight');
                  
                    toastr.options = {
                        closeButton: true,
                        tapToDismiss: true,
                        positionClass: 'toast-top-right',
                        toastClass: 'toast-icon custom-toast-success',
                        progressBar: true,
                    };
                    field_error = true;
                    productWeightId.css('border-color', 'red');
                    productWeightId.focus();
                    // toastr.error('Selected Shipping profile requires atleast weight for Product dimensions');
                    toastr.error('Selected Shipping profile requires atleast weight for Product dimensions');
                    // field_error = false;
                    
                    
                }else if(productWeight <= 0){
                        field_error = true;
                        toastr.options = {
                            closeButton: true,
                            tapToDismiss: true,
                            positionClass: 'toast-top-right',
                            toastClass: 'toast-icon custom-toast-success',
                            progressBar: true,
                        };
                        field_error = true;
                        productWeightId.css('border-color', 'red');
                        productWeightId.focus();
                        // toastr.error('Selected Shipping profile requires atleast weight for Product dimensions');
                        // field_error = true;
                        toastr.error('Selected Shipping profile requires atleast weight for Product dimensions');
                }else if(productWeight > 0  && productSize_length == 0 && productSize_width == 0 && productSize_height == 0 ){

                    // Usps request for check rate get or not
                    var data = {};
                    var pound = $("#weighttype").val();
                    var cwight = 0;
                  

                    if (pound == 'oz') {
                        cwight = productWeight / 16;
                        data['productWeight'] = cwight.toFixed(3);
                    } else {
                        data['productWeight'] = Math.round(productWeight * 10) / 10;
                    }
                    
                    $.ajax({
                        url: "/getrate_usps_service",
                        method: 'POST',
                        async: false,
                        data: data,
                        success: function (response) {
                            if (response.status == false) {
                                field_error = true;
                                console.log("error", response.message);
                                swal("Error", response.message, "error");
                            }       
                        }
                    });
                
                } else if(productWeight > 0  || productSize_length == 0 || productSize_width == 0 || productSize_height == 0 ) {
                    // Easy Post Request
                    
                     if(productSize_length <= 0 || productSize_width <= 0 || productSize_height <= 0){
                        if (productSize_length <= 0) {
                            $("#productSize_length").css({
                                'border-color': 'red'          
                            });
                        }else {
                                $("#productSize_length").css('border-color', 'rgba(0, 128, 0, 0.39)');
                            }
                        if (productSize_width <= 0) {
                            $("#productSize_width").css({
                                'border-color': 'red'          
                            });
                        }else {
                            $("#productSize_width").css('border-color', 'rgba(0, 128, 0, 0.39)');
                        }
                        if (productSize_height <= 0) {
                            $("#productSize_height").css({
                                'border-color': 'red'          
                            });
                        }else {
                            $("#productSize_height").css('border-color', 'rgba(0, 128, 0, 0.39)');
                        }
                        
                        field_error = true;
                        return;
                    }
                    var data = {};
                    var pound = $("#weighttype").val();
                    var mtype = $("#dimensionstype").val();
                    var clentgth = 0;
                    var cheight = 0;
                    var cweidth = 0;
                    var cwight = 0;
                    data['productSize_length'] = Math.round(productSize_length * 10) / 10;
                    data['productSize_width'] = Math.round(productSize_width * 10) / 10;
                    data['productSize_height'] = Math.round(productSize_height * 10) / 10;

                    if (pound == 'pound') {
                        cwight = productWeight * 16;
                        data['productWeight'] = cwight.toFixed(3);
                    } else {
                        data['productWeight'] = Math.round(productWeight * 10) / 10;
                    }

                    if (mtype == 'cm') {
                        clentgth = productSize_length / 2.54;
                        cheight = productSize_height / 2.54;
                        cweidth = productSize_width / 2.54;
                        data['productSize_length'] = clentgth.toFixed(3);
                        data['productSize_width'] = cweidth.toFixed(3);
                        data['productSize_height'] = cheight.toFixed(3);
                    }
                    else if (mtype == 'mm') {
                        clentgth = productSize_length / 25.4;
                        cheight = productSize_height / 25.4;
                        cweidth = productSize_width / 25.4;
                        data['productSize_length'] = clentgth.toFixed(3);
                        data['productSize_height'] = cheight.toFixed(3);
                        data['productSize_width'] = cweidth.toFixed(3);
                    }
                    
                    $.ajax({
                        url: "/getrates",
                        method: 'POST',
                        async: false,
                        data: data,
                        success: function (response) {
                            
                            if (response.error) {
                                field_error = true;
                                console.log("error", response.error);
                                swal("Error", "No matching dimensions found. Please set the correct dimensions", "error");
                            } else {
                                $("#maxquantitylimit").val(response.maxLimitQuantity);
                                console.log('rate matched');
                            }
                            if (response == '') {
                                field_error = true;
                                swal("Error", "No matching dimensions found. Please set the correct dimensions", "error");
                            }
                        }
                    });

                }


            }

        }
        console.log("checking dimentions ptype", ptype);
        if (ptype != 'virtual') {

        }
        var ShipProfile = $("input[name=previousShippingOptions]").closest('.panel-repeater-radio');
        if (ptype != 'virtual') {
            if ($("input[name=previousShippingOptions]").is(':checked') == false || $('input[name=previousShippingOptions]:checked').val() == 'custom-shipping') {
               
                $("input[name=previousShippingOptions]").closest('.panel-repeater-radio');
                ShipProfile.addClass("required-ship");
                field_error = true;
            } else {
                ShipProfile.removeClass("required-ship");
            }
        }

        if (validatorProductForm(e, ptype)) {
          
            if (field_error) {
                
                e.preventDefault();
                return false;
            } else {
                $('#addProductForm').submit();
            }

        } else {
            // $("html, body").animate({ scrollTop: 0 }, "slow");
            
            var $div2 = $(".errorpage");
            toastr.options = {
                closeButton: true,
                tapToDismiss: true,
                positionClass: 'toast-top-right',
                toastClass: 'toast-icon custom-toast-success',
                progressBar: true,
            };
            toastr.error('Please fill out the missing fields');
            e.preventDefault();
            return false;
        }

    })
});
jQuery('body').on('click', '.img-check', function (e) {
    $(this).parent().toggleClass("check-checkbox");
});

jQuery('body').on('change', '#productMainCategory', function (e) {
    $('#subcategory').hide();
    $('#subcategory').empty();
    $('#childcategory').hide();
    $('#childcategory').empty();

    var getProductID = $('#edit_product_id').val();

    if (this.value != 0) {
        $(this).css('border-color', '#ced4da');
        $.ajax({
            url: "/childCategory",
            method: 'POST',
            data: { parentID: this.value },
            success: function (response) {
                if (response != '') {
                    $(".childcat").show();
                    $('#subcategory').append(response);
                    $('#subcategory').show();
                }
            }
        });
    }

});
jQuery('body').on('change', '#subcategory select', function (e) {

    $('#childcategory').hide();
    $('#childcategory').empty();
    if (this.value != 0) {
        $.ajax({
            url: "/childCategory",
            method: 'POST',
            data: { parentID: this.value },
            success: function (response) {
                if (response) {
                    $('#childcategory').append(response);
                    $('#childcategory').show();
                }


            }
        });
    }

});


function validateField(fieldsID, fieldValue, e) {
    var isDisabled = fieldsID.is(':disabled');
    if (isDisabled) {
        fieldsID.css('border-color', 'none');

        return true;
    } else {
        if (fieldValue == 'TRUE') {
            fieldsID.css('border-color', 'rgba(0, 128, 0, 0.39)');
            return true;
        } else {
            fieldsID.css('border-color', 'red');
            fieldsID.focus();
            e.preventDefault();
            $('body').find('.panel-collapse').removeClass('show');
            $('body').find('.card-header').removeClass('active');

            $('body').find(fieldsID).parents().closest(".panel-collapse").addClass('show');
            $('body').find(fieldsID).parents().closest(".card-header").addClass('show');
            return false;
        }
    }

}
function formatBytes(bytes, decimals) {
    if (bytes == 0)
        return '0 Bytes';
    var k = 1024,
        dm = decimals || 2,
        sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'],
        i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + ' ' + sizes[i];
}

function showShippingModal() {

    var shippingProfileId = $(".loadShippingOption").attr('shippingOptionId');
    var shippingAction = $(".loadShippingOption").attr('data-ui');
    $('#shipping-popup .uploadariation').show();
    $('#shipping-popup').modal('show');

    $.ajax({
        url: "/shippingOptions",
        method: 'POST',
        data: { shippingProfileId: shippingProfileId, action: shippingAction },
        /// processData: false,
        //// contentType: false,
        success: function (response) {

            /// console.log(response);
            $('#shipping-popup .shippingProfileResposne').empty();
            $('#shipping-popup .shippingProfileResposne').append(response);
            setTimeout(function () {
                $('#shipping-popup .uploadariation').hide();
            }, 1000);

        },
        error: function () {
            setTimeout(function () {
                $('#shipping-popup .uploadariation').hide();
            }, 1000);
            ///alert('Something Wrong Here');
        }
    });
}



function progressHandler(event) {
    jQuery('body').find("#" + itemId + " .loaded_n_total").html("Uploaded " + formatBytes(event.loaded) + " bytes of " + formatBytes(event.total));
    var percent = (event.loaded / event.total) * 100;
    ///// _("progressBar").value = Math.round(percent);
    jQuery('body').find("#" + itemId + " .upload-status").html(Math.round(percent) + "% uploaded... please wait");
}

function completeHandler(event) {
    ///_("status").innerHTML = event.target.responseText;
    jQuery('body').find("#" + itemId + " .upload-status").html('Complete');
    //// _("progressBar").value = 0;
}

function errorHandler(event) {
    jQuery('body').find("#" + itemId + " .upload-status").html('Upload Failed');
}

function abortHandler(event) {
    jQuery('body').find("#" + itemId + " .upload-status").html('Upload Aborted');
}
var uploadingItems = new Array();
function digitalUpload(input) {

    var checkCount = $('body').find('.uploadItemSection .upload-container-section .list-panel-upload').length;
    ///alert(checkCount)
    if (checkCount > 4) {
        ///  alert(checkCount)
        alert('You can  upload max 5 files for each  file max allowed size is  20 MB.');
        return false;

    }
    var i_inner = 0, len = input.files.length, file;
    var totalItem = jQuery('#all-uplpad-image .image-panel').length;
    var formdata = new FormData();
    for (; i_inner < len; i_inner++) {

        file = input.files[i_inner];
        var vol_size = file.size;
        if (vol_size <= 0) {
            vol_size = 1;
        }
        vol_size = (vol_size) / 1024;
        vol_size = parseInt(vol_size);
        if (vol_size < 20480) {
            totalItem = totalItem + 1;
            regex = /.jpg|.jpeg|.gif|.png|.bmp|.doc|.gif|.mobi|.mov|.mp3|.mpeg|.pdf|.psp|.rtf|.stl|.zip|.ePUB|.iBook/;
            if ((!!file.type.match(regex))) {
                var itemId = 'item_' + randomNumberFromRange(213, 5606565656);
                formdata.append("digitalItem[]", file);
                var items = new Array();
                items['size'] = file.size;
                items['name'] = file.name;
                items['type'] = file.type;
                uploadingItems.push(items);
                checkCount++;
            } else {
                $('body').find('.uploadItemSection .uploadImages').delay(2000).hide();
                alert('Please upload files that end in .bmp, .doc, .gif, .jpeg, .jpg, .mobi, .mov, .mp3, .mpeg, .pdf, .png, .psp, .rtf, .stl, .txt, .zip, .ePUB, or .iBook.');
            }
        } else {
            $('body').find('.uploadItemSection .uploadImages').delay(2000).hide();
            alert('Your file size is ' + formatBytes(file.size) + '. Max allowed size is  20 MB.');
        }
        /// alert(checkCount)
        if (checkCount > 5) {
            break;
        }
    }
    if (uploadingItems.length > 0) {

        $('body').find('.uploadItemSection .uploadImages').delay(2000).show();
        $('body').find('.uploadItemSection .upload-container-section').delay(2000).hide();
        $.ajax({
            url: "/uploadDigital",
            method: 'POST',
            data: formdata,
            processData: false,
            contentType: false,
            success: function (resposneItems) {

                showUploadedDigitalItem(resposneItems);
            },
            error: function () {
                alert('Something Wrong Here');
                $('body').find('.uploadItemSection .upload-container-section').delay(2000).hide();
            }
        });
        
    }
    if (document.getElementById("productDigital").files.length > 0) {
        jQuery('#productDigital').val('');
    }
}
function validateFieldById(id) {
    fieldsID = jQuery('#' + id);
    if (fieldsID.val() != "") {
        fieldsID.css('border-color', 'rgba(0, 128, 0, 0.39)');

    } else if (fieldsID.val() <= 0) {
        fieldsID.css('border-color', 'red');
        fieldsID.focus();
    } else {
        fieldsID.css('border-color', 'red');
        fieldsID.focus();

    }
}

function validateFieldByClass(cl) {
    fieldsID = jQuery('.' + cl);
    if (fieldsID.val() != "") {
        fieldsID.css('border-color', 'rgba(0, 128, 0, 0.39)');

    } else if (fieldsID.val() <= 0) {
        fieldsID.css('border-color', 'red');
        fieldsID.focus();
    } else {
        fieldsID.css('border-color', 'red');
        fieldsID.focus();

    }
}
function deleteItem(itemId) {
    $('body').find('.uploadItemSection .uploadImages').delay(2000).show();
    var input_field_value = jQuery('#itemSource_' + itemId).val();
    if (input_field_value) {

        $('#item_' + itemId).remove();
        $.ajax({
            url: "/deleteProductItem",
            method: 'POST',
            data: { fileName: input_field_value },
            success: function (res) {

                if (($('body').find('.uploadItemSection .upload-container-section .list-panel-upload').length) == 0) {
                    $('body').find('.uploadItemSection .upload-container-section').delay(2000).hide();
                } else {
                    $('body').find('.uploadItemSection .upload-container-section').delay(2000).show();
                }
            }
        });
    }
    $('body').find('.uploadItemSection .uploadImages').delay(2000).hide();
}
function showUploadedDigitalItem(source) {

    var itemDiv = document.getElementById('upload-container-section');
    var html = '';
    var i_inner = 0, len = source.fileName.length;
    for (; i_inner < len; i_inner++) {
        /// console.log(uploadingItems);
        randomCode = randomNumberFromRange(101, 99999999999);
        html += '<div  id="item_' + randomCode + '" class="list-panel-upload">';
        html += '<span class="name-file">' + uploadingItems[i_inner].name + '</span>';
        html += '<span class="name-size">' + formatBytes(uploadingItems[i_inner].size) + '</span>';
        html += '<input type="hidden" id="itemSource_' + randomCode + '" name="digitalItem[]" value="' + source.fileName[i_inner] + '" />';
        html += '<span class="file-remove">';
        html += '<a href="javascript:;" onclick="deleteItem(' + randomCode + ')">Remove</a>';
        html += '</span>';
        html += '</div>';
    }


    uploadingItems = new Array();
    ///console.log(html);
    itemDiv.insertAdjacentHTML('beforeend', html);
    $('body').find('.uploadItemSection .uploadImages').delay(2000).hide();
    $('body').find('.uploadItemSection .upload-container-section').delay(2000).show();
}



var uploadingImages = new Array();
var Ima = new Array();
function readURL(input) {
    
    var req_width = 720,
        req_height = 480;
    var validate = false;
    var i_inner = 0, len = input.files.length, reader, file;
    var totalImages = jQuery('#all-uplpad-image .image-panel').length;
    imgCounter = totalImages;
    if (imgCounter >= 5) {
        alert('Already 5 images Uploaded.');
        return false;
    } else {

    }
    formdata = new FormData();
    for (; i_inner < len; i_inner++) {
        $('body').find('.photoSection .uploadImages').delay(1000).show();
        ////$('.uploadImages').delay(1000).show();
        file = input.files[i_inner];
        var vol_size = file.size;
        if (vol_size <= 0) {
            vol_size = 1;
        }
        vol_size = (vol_size) / 1024;
        vol_size = parseInt(vol_size);
        if (vol_size < 3072) {
            imgCounter = imgCounter + 1;
            if (!!file.type.match(/image.*/)) {

                if (window.FileReader) {
                    reader = new FileReader();
                    reader.onloadend = function (e) {

                        var image = new Image();
                        image.src = e.target.result;
                        image.onload = function () {
                            // access image size here 
                            var dWidth = this.naturalWidth,
                                dHeight = this.naturalHeight;
                            if (dWidth < req_width || dHeight < req_height) {
                                alert('Your Image size is (' + dWidth + 'w x ' + dHeight + 'h). The selected Image should be (' + req_width + 'w x ' + req_height + 'h) pixel or greater in size.');
                            } else {
                                uploadingImages.push(e.target.result);
                            }
                        };
                    };
                    reader.readAsDataURL(file);
                }
                if (formdata) {
                    validate = 1;
                    formdata.append("productImage[]", file);
                }
            } else {
                alert('Please select jpg image file only.');
                $('body').find('.photoSection .uploadImages').delay(1000).hide();
            }
        } else {
            $('body').find('.photoSection .uploadImages').delay(1000).hide();
            ///$('.uploadImages').delay(1000).hide();
            /// imgCounter = imgCounter - 1;
            //// $('#thumbnail-image-box').html(' <div class="prdimg pi2 "><span class="imgtg">Thumbnail</span> </div> ');
            alert('Your Image size is ' + vol_size + 'kb. Max allowed size is 3MB.');


        }
        ///  alert(imgCounter)
        if (imgCounter > 0) {
            if (imgCounter == 5) {
                break;
            }
        }


    }
    //    alert(imgCounter);
    //    if (imgCounter) {
    //        $('.imageBox').eq(imgCounter - 1).hide();
    //    }


    if (validate) {
        $.ajax({
            url: "/ProductImageRequest",
            method: 'POST',
            data: formdata,
            processData: false,
            contentType: false,
            success: function (uploadingImages) {



                showUploadedItem(uploadingImages);
                var totalImages = jQuery('#all-uplpad-image .image-panel').length;

                //alert(totalImages);

            },
            error: function () {
                alert('Something Wrong Here');
                $('body').find('.photoSection .uploadImages').delay(1000).hide();
            }
        });
    }

    if (document.getElementById("productImage").files.length > 0) {
        jQuery('#productImage').val('');
    }
}

var formShippingData = new FormData();
function abc() {
    alert("abc called");
}
function shippingValidation() {
    formShippingData = new FormData();
    $shippingoption = document.getElementById("calculate-shipping-option");
    $shippingOptionValue = $shippingoption.options[$shippingoption.selectedIndex].value;
    if ($shippingOptionValue == 'clculateRecommeded' || $shippingOptionValue == 'fixedCost') {
        formShippingData.append("shippingCost", $shippingOptionValue);
        $shippingoption.setAttribute("style", "border-color: #ced4da");
    } else {
        $shippingoption.setAttribute("style", "border-color: red;");
        console.log('Please select Shipping Method');
        return false;
    }
    if ($shippingOptionValue == 'clculateRecommeded') {
        $zipCode = document.getElementById("zipCode");
        $zipCodeValue = $zipCode.value;
        if ($zipCodeValue) {
            formShippingData.append("zipCode", $zipCodeValue);
            $zipCode.setAttribute("style", "border-color: #ced4da;");
        } else {
            $zipCode.setAttribute("style", "border-color: red;");
            console.log('Please fill zipcode');
            return false;
        }
        $countryCheck = true;
        $('input[name="countries[]"]:checked').each(function () {
            var countryId = this.value;
            if (countryId) {
                formShippingData.append("countries[]", this.value);
                countryId = '';
                $countryCheck = false;
            }
        });
        if ($countryCheck) {
            console.log('Please select countries will you ship to');
            return false;
        }

        $serviceCheck = true;
        $('input[name="shippingServices[]"]:checked').each(function () {
            var serviceId = this.value;
            if (serviceId) {
                formShippingData.append("shippingServices[]", this.value);
                serviceId = '';
                $serviceCheck = false;
            }
        });
        if ($serviceCheck) {
            console.log('Please select shipping services');
            return false;
        }
        var domesticFreeShippings = $('input[name="domesticFreeShippings"]:checked');
        if (domesticFreeShippings) {
            formShippingData.append("domesticFreeShippings", 1);
        } else {
            formShippingData.append("domesticFreeShippings", 0);
        }
        var internationalFreeShipping = $('input[name="internationalFreeShipping"]:checked');
        if (internationalFreeShipping) {
            formShippingData.append("internationalFreeShipping", 1);
        } else {
            formShippingData.append("internationalFreeShipping", 0);
        }


        var domesticHandlingFee = $('input[name="domesticHandlingFee"]').val();
        if (domesticHandlingFee) {
            formShippingData.append("domesticHandlingFee", domesticHandlingFee);
        }
        var internationalHandlingFee = $('input[name="internationalHandlingFee"]').val();
        if (internationalHandlingFee) {
            formShippingData.append("internationalHandlingFee", internationalHandlingFee);
        }

    } else {
        $originCountry = document.getElementById("shippingOriginCountry");
        $originCountryValue = $originCountry.options[$originCountry.selectedIndex].value;

        if ($originCountryValue) {
            formShippingData.append("shippingOrigin", $originCountryValue);
            $originCountry.setAttribute("style", "border-color: #ced4da;");
        } else {
            $originCountry.setAttribute("style", "border-color: red;");
            console.log('Please select shipping origin');
            return false;
        }
    }
    $processingTime = document.getElementById("processing-time-perioud");
    $processingTimeValue = $processingTime.value;
    if ($processingTimeValue) {
        formShippingData.append("processTimeOption", $processingTimeValue);
        if ($processingTimeValue == 'custom-range') {
            $startRange = document.getElementById("startRange");
            $startRangeValue = $startRange.options[$startRange.selectedIndex].value;
            $endRange = document.getElementById("endRange");
            $endRangeValue = $endRange.options[$endRange.selectedIndex].value;

            if ($startRangeValue == '' || $startRangeValue == 'From...') {
                console.log('Please select start time');
                return false;
            }
            if ($endRangeValue == '' || $endRangeValue == 'To...') {
                console.log('Please select end time');
                return false;
            } else {
                if ($startRangeValue > $endRangeValue) {
                    console.log('Please select end time greater then start date.');
                    return false;
                } else {
                    formShippingData.append("processTimeStart", $startRangeValue);
                    formShippingData.append("processTimeEnd", $endRangeValue);

                }
            }

            if (document.getElementById('business-days').checked) {
                $selectTimeValue = document.getElementById('business-days').value;
            } else {
                $selectTimeValue = document.getElementById('business-days').value;
            }

            if ($selectTimeValue == 'day' || $selectTimeValue == 'week') {
                formShippingData.append("processTime", $selectTimeValue);
            } else {
                console.log('Please select processing time');
                return false;
            }
        }
    } else {
        console.log('Please select processing period');
        return false;
    }
    if ($shippingOptionValue == 'fixedCost') {


        $fixCostOneItem = document.getElementById("fixCostOneItem");
        $fixCostOneItemValue = $fixCostOneItem.value;
        if ($fixCostOneItemValue == '') {
            $fixCostOneItem.setAttribute("style", "border-color: red;");
            console.log('Please fill price');
            return false;
        } else {
            $fixCostOneItem.setAttribute("style", "border-color: #ced4da;");
        }
        $counterIndex = 0;
        var fieldName, fieldOneItemPrice, fieldEachItemPrice;
        $('.fixedShippingCostMainDiv').find('input , select').each(function () {
            $counterIndex++;
            if ($counterIndex == 1) {
                fieldName = $(this).val();
            } else if ($counterIndex == 2) {
                fieldOneItemPrice = $(this).val();
            } else {
                fieldEachItemPrice = $(this).val();
            }
            if ($counterIndex == 3) {
                formShippingData.append("shippingFixCost[destinationId][]", fieldName);
                formShippingData.append("shippingFixCost[oneItem][]", fieldOneItemPrice);
                formShippingData.append("shippingFixCost[eachItem][]", fieldEachItemPrice);
                $counterIndex = 0;
            }
        });
        $counterUpgradesIndex = 0;
        var upgradename, destination, upgradeOneItemPrice, upgradeEachItemPrice;
        $('.shipping-upgrade-optional').find('input , select').each(function () {
            $counterUpgradesIndex++;
            if ($counterUpgradesIndex == 1) {
                upgradename = $(this).val();
            } else if ($counterUpgradesIndex == 2) {

                if ($(this).val() == 'international') {
                    destination = $(this).val();
                } else {
                    destination = 'domestic';
                }
            } else if ($counterUpgradesIndex == 3) {
                upgradeOneItemPrice = $(this).val();
            } else {
                upgradeEachItemPrice = $(this).val();
            }
            if ($counterUpgradesIndex == 4) {
                if (upgradename && upgradeOneItemPrice) {
                    formShippingData.append("shippingUpgrades[upgrapeName][]", upgradename);
                    formShippingData.append("shippingUpgrades[level][]", destination);
                    formShippingData.append("shippingUpgrades[oneItem][]", upgradeOneItemPrice);
                    formShippingData.append("shippingUpgrades[eachItem][]", upgradeEachItemPrice);
                }

                $counterUpgradesIndex = 0;
            }
        });

    }
    return true;
}
jQuery('body').on('click', '#saveShippingOption', function (e) {



    if (shippingValidation()) {
        console.log('Done Validation');
        $('#create-profile-popup').modal('show');
    } else {
        console.log('Error Validation');
    }

});
function shippingValidationPopup() {

    formShippingData = new FormData();
    $shippingoptionId = document.getElementById("shippingOptionIdPopup").value;
    if ($shippingoptionId) {
        formShippingData.append("shippingOptionKey", $shippingoptionId);
    }
    $shippingoptionMethod = document.getElementById("shippingOptionMethodPopup").value;
    formShippingData.append("shippingCost", $shippingoptionMethod);
    if ($shippingoptionMethod == 'clculateRecommeded') {
        $zipCode = document.getElementById("zipCodePopup");
        $zipCodeValue = $zipCode.value;
        if ($zipCodeValue) {
            formShippingData.append("zipCode", $zipCodeValue);
            $zipCode.setAttribute("style", "border-color: #ced4da;");
        } else {
            $zipCode.setAttribute("style", "border-color: red;");
            console.log('Please fill zipcode');
            return false;
        }
        $countryCheck = true;
        jQuery('body').find('input[name="countries-popup[]"]:checked').each(function () {
            var countryId = this.value;
            if (countryId) {
                formShippingData.append("countries[]", this.value);
                countryId = '';
                $countryCheck = false;
            }
        });
        if ($countryCheck) {
            console.log('Please select countries will you ship to');
            return false;
        }

        $serviceCheck = true;
        jQuery('body').find('input[name="shippingServicesPopup[]"]:checked').each(function () {
            var serviceId = this.value;
            if (serviceId) {
                formShippingData.append("shippingServices[]", this.value);
                serviceId = '';
                $serviceCheck = false;
            }
        });
        if ($serviceCheck) {
            console.log('Please select shipping services');
            return false;
        }
        var domesticFreeShippings = jQuery('body').find('input[name="domesticFreeShippingsPopup"]:checked');
        if (domesticFreeShippings) {
            formShippingData.append("domesticFreeShippings", 1);
        } else {
            formShippingData.append("domesticFreeShippings", 0);
        }
        var internationalFreeShipping = jQuery('body').find('input[name="internationalFreeShippingPopup"]:checked');
        if (internationalFreeShipping) {
            formShippingData.append("internationalFreeShipping", 1);
        } else {
            formShippingData.append("internationalFreeShipping", 0);
        }


        var domesticHandlingFee = $('input[name="domesticHandlingFeePopup"]').val();
        if (domesticHandlingFee) {
            formShippingData.append("domesticHandlingFee", domesticHandlingFee);
        }
        var internationalHandlingFee = $('input[name="internationalHandlingFeePopup"]').val();
        if (internationalHandlingFee) {
            formShippingData.append("internationalHandlingFee", internationalHandlingFee);
        }

    } else {
        $originCountry = document.getElementById("shippingOriginCountryPopup");
        $originCountryValue = $originCountry.options[$originCountry.selectedIndex].value;

        if ($originCountryValue) {
            formShippingData.append("shippingOrigin", $originCountryValue);
            $originCountry.setAttribute("style", "border-color: #ced4da;");
        } else {
            $originCountry.setAttribute("style", "border-color: red;");
            console.log('Please select shipping origin');
            return false;
        }
    }
    $processingTime = document.getElementById("processing-time-perioud-popup");
    $processingTimeValue = $processingTime.value;
    if ($processingTimeValue) {
        formShippingData.append("processTimeOption", $processingTimeValue);
        if ($processingTimeValue == 'custom-range-select-popup') {
            formShippingData.append("processTimeOption", 'custom-range');
            $startRange = document.getElementById("startRangePopup");
            $startRangeValue = $startRange.options[$startRange.selectedIndex].value;
            $endRange = document.getElementById("endRangePopup");
            $endRangeValue = $endRange.options[$endRange.selectedIndex].value;
            if ($startRangeValue == '' || $startRangeValue == 'From...') {
                console.log('Please select start time');
                return false;
            }
            if ($endRangeValue == '' || $endRangeValue == 'To...') {
                console.log('Please select end time');
                return false;
            } else {
                if ($startRangeValue > $endRangeValue) {
                    console.log('Please select end time greater then start date.');
                    return false;
                } else {
                    formShippingData.append("processTimeStart", $startRangeValue);
                    formShippingData.append("processTimeEnd", $endRangeValue);

                }
            }

            if (document.getElementById('business-days-popup').checked) {
                $selectTimeValue = document.getElementById('business-days-popup').value;
            } else {
                $selectTimeValue = document.getElementById('business-week-popup').value;
            }

            if ($selectTimeValue == 'day' || $selectTimeValue == 'week') {
                formShippingData.append("processTime", $selectTimeValue);
            } else {
                console.log('Please select processing time');
                return false;
            }
        }
    } else {
        console.log('Please select processing period');
        return false;
    }
    if ($shippingoptionMethod == 'fixedCost') {

        //        $fixCostOneItem = document.getElementById("fixCostOneItem");
        //        $fixCostOneItemValue = $fixCostOneItem.value;
        //        if ($fixCostOneItemValue == '') {
        //            $fixCostOneItem.setAttribute("style", "border-color: red;");
        //            console.log('Please fill price');
        //            return false;
        //        } else {
        //            $fixCostOneItem.setAttribute("style", "border-color: #ced4da;");
        //        }
        $counterIndex = 0;
        var fieldName, fieldOneItemPrice, fieldEachItemPrice;
        jQuery('body').find('.fixedShippingCostMainDivPopup input ,.fixedShippingCostMainDivPopup select').each(function () {
            $counterIndex++;
            console.log($(this).val());
            if ($counterIndex == 1) {
                fieldName = $(this).val();
            } else if ($counterIndex == 2) {
                fieldOneItemPrice = $(this).val();
            } else {
                fieldEachItemPrice = $(this).val();
            }
            if ($counterIndex == 3) {
                formShippingData.append("shippingFixCost[destinationId][]", fieldName);
                formShippingData.append("shippingFixCost[oneItem][]", fieldOneItemPrice);
                formShippingData.append("shippingFixCost[eachItem][]", fieldEachItemPrice);
                $counterIndex = 0;
            }
        });
        $counterUpgradesIndex = 0;
        var upgradename, destination, upgradeOneItemPrice, upgradeEachItemPrice;
        jQuery('body').find('.shippingUpgradeOptionalPopup input ,.shippingUpgradeOptionalPopup select').each(function () {
            $counterUpgradesIndex++;
            if ($counterUpgradesIndex == 1) {
                upgradename = $(this).val();
            } else if ($counterUpgradesIndex == 2) {

                if ($(this).val() == 'international') {
                    destination = $(this).val();
                } else {
                    destination = 'domestic';
                }
            } else if ($counterUpgradesIndex == 3) {
                upgradeOneItemPrice = $(this).val();
            } else {
                upgradeEachItemPrice = $(this).val();
            }
            if ($counterUpgradesIndex == 4) {
                if (upgradename && upgradeOneItemPrice) {
                    formShippingData.append("shippingUpgrades[upgrapeName][]", upgradename);
                    formShippingData.append("shippingUpgrades[level][]", destination);
                    formShippingData.append("shippingUpgrades[oneItem][]", upgradeOneItemPrice);
                    formShippingData.append("shippingUpgrades[eachItem][]", upgradeEachItemPrice);
                }

                $counterUpgradesIndex = 0;
            }
        });

    }
    $shippingProfileName = document.getElementById("shippingProfileNamePopup");
    $shippingProfileNameValue = $shippingProfileName.value;

    if ($shippingProfileNameValue == '') {
        $shippingProfileName.setAttribute("style", "border-color: red;");
        alert('Please fill shipping  profile name');
        return false;
    } else {
        $shippingProfileName.setAttribute("style", "border-color: #ced4da;");
        formShippingData.append("profileName", $shippingProfileNameValue);
    }
    return true;

}
jQuery('body').on('click', '#create-profile', function (e) {

    ////$('#create-profile-popup').modal('show');
    $shippingProfileName = document.getElementById("shippingProfileName");
    $shippingProfileNameValue = $shippingProfileName.value;
    ///   alert($shippingProfileNameValue)
    /// return false;
    if ($shippingProfileNameValue == '') {
        $shippingProfileName.setAttribute("style", "border-color: red;");
        alert('Please fill shipping  profile name');
        return false;
    } else {
        $shippingProfileName.setAttribute("style", "border-color: #ced4da;");
        formShippingData.append("profileName", $shippingProfileNameValue);
        jQuery(".uploadImages-popup").show();
        sendShippingRequest();
    }
});

jQuery('body').on('change', '[name="shippingOriginCountry"]', function (e) {
    ///alert(this.options[this.selectedIndex].text)
    document.getElementById('defaultShippingCountry').value = this.value;
    document.getElementById('originCountry').innerHTML = this.options[this.selectedIndex].text;

});
jQuery('body').on('click', '.loadShippingOption', function (e) {
    var shippingProfileId = $(this).attr('shippingOptionId');
    var shippingAction = $(this).attr('data-ui');
    $('#shipping-popup .uploadariation').show();
    $('#shipping-popup').modal('show');

    $.ajax({
        url: "/shippingOptions",
        method: 'POST',
        data: { shippingProfileId: shippingProfileId, action: shippingAction },
        /// processData: false,
        //// contentType: false,
        success: function (response) {
            //  console.log(response);
            $('#shipping-popup .shippingProfileResposne').empty();
            $('#shipping-popup .shippingProfileResposne').append(response);
            setTimeout(function () {
                $('#shipping-popup .uploadariation').hide();
            }, 1000);
            $('#editselect-shipoption').on('change', function () {
                setTimeout(function () {
                    jQuery(document).on('change', '#editselect-shipoption', function () {
                        if ($("option#other-shipping-po:selected").length) {
                            $('#save_shipping_custom .shipping-services').css("display", "none");
                            $('#save_shipping_custom .fixed-shipping-cost-mbtt').css("display", "flex");
                        } else if ($("option#editfree-shipping:selected").length) {

                            $('.shipping-services').css("display", "none");

                            $('#shipping-popup .fixed-shipping-cost-mbtt').css("display", "none");


                        } else {
                            $('#save_shipping_custom .shipping-services').css("display", "flex");
                            $('#save_shipping_custom .fixed-shipping-cost-mbtt').css("display", "none");
                        }
                    });
                }, 1000);
                if ($(this).val() == 'united-state') {
                    console.log("if condition");
                    $('#domestic_free_shipping').prop('checked', false);
                    $('input[name="flat_shipping_rate"]').val(0);
                } else {
                    console.log("else condition");
                    $('input[name="edit_ship_service[]').each(function () {
                        this.checked = false;
                    });
                    $('#domestic_free_shipping').prop('checked', false);
                }
            });
        },
        error: function () {
            setTimeout(function () {
                $('#shipping-popup .uploadariation').hide();
            }, 1000);
            ///alert('Something Wrong Here');
        }
    });
});
function deleteImage(imageID) {
    var input_field_value = jQuery('#imagesrc' + imageID).val();
    if (input_field_value) {

        Imagesbox = jQuery('#all-uplpad-image .image-panel').length;
        totalImages = Imagesbox - 1;
        if (totalImages < 6) {
            $('#uploadImages').show();
            $('.imageBox').show();
            ///  $('body').find('.photoSection .uploadImages').delay(1000).show();
            ////$('.imageBox').eq(Imagesbox - 1).show();
        }
        if (totalImages == 3) {
            jQuery('body').find('.col-md-8.rghtexp').removeClass('image-four');
            jQuery('body').find('.col-md-8.rghtexp').removeClass('image-five');
            jQuery('body').find('.col-md-8.rghtexp').removeClass('image-six');
        }


        if (totalImages == 4) {
            jQuery('body').find('.col-md-8.rghtexp').addClass('image-four');
            jQuery('body').find('.col-md-8.rghtexp').removeClass('image-five');
            jQuery('body').find('.col-md-8.rghtexp').removeClass('image-six');
        }

        if (totalImages == 5) {
            jQuery('body').find('.col-md-8.rghtexp').removeClass('image-four');
            jQuery('body').find('.col-md-8.rghtexp').addClass('image-five');
            jQuery('body').find('.col-md-8.rghtexp').removeClass('image-six');
        }



        if (totalImages == 0) {
            $('.images-added .prdimg.pi1 .imgtg').show();
        } else {
            $('.images-added .prdimg.pi1 .imgtg').hide();
        }
        $('#img_' + imageID).remove();
        $.ajax({
            url: "/deleteProductImage",
            method: 'POST',
            data: { imageName: input_field_value },
            success: function (res) { 
                debugger
            }
        });
    }
}



function randomNumberFromRange(min, max) {
    return Math.floor(Math.random() * (max - min + 1) + min);
}


$('#productImage').change(function () {
    var checkImages = $(this)[0].files.length + ' images chosen';
    if (checkImages > 1) {
        // alert("i am less thanone");
    } else {
        jQuery("#all-uplpad-image").parent().addClass("images-added");
        // alert("i am greater than one");
    }

});
$(".dele-icn a.dete-parent").click(function () {
    // $(this).parent().parent().remove();
});
jQuery('body').on('keyup keypress', '#addProductForm', function (e) {
    var keyCode = e.keyCode || e.which;
    if (keyCode === 13) {
        e.preventDefault();
        return false;
    }
});
//jQuery('body').on('click', '.variationOptionID a', function (e) {
//    
//    var optionSelect = $(this).parent().parent().parent().has(".attributeOptions");
//    if (optionSelect.length) {
//        var optionVal = $(this).attr("optionVal");
//        $(this).parent().parent().parent().find('option[value="' + optionVal + '"]').show();
//    }
//    $(this).parent().remove();
//    var attribute1 = $('#varidationOptionValues').find('input[type=text]').length;
//    var attribute2 = $('#varidationOptionValues2').find('input[type=text]').length;
//    if (attribute1 == 0 && attribute2 == 0) {
//        $('#btnvariationcheck').attr('disabled', 'disabled');
//    } else {
//        $('#btnvariationcheck').removeAttr('disabled');
//    }
//
//});
jQuery('body').on('change', '.attributeOptions', function (e) {
    var mainDivVariation = $(this).parentsUntil('.VariationMainDiv').parent();
    var optionHTML = '';
    //// $('.attributeOptions').on('change', function () {
    var options = mainDivVariation.find('.attributeOptions option:selected').text();
    var optionsVal = mainDivVariation.find('.attributeOptions option:selected').val();
    switch (optionsVal) {

        case '__custom':
            mainDivVariation.find('.customFeildVariation').empty();
            var inputField = customTextField('Type Variations here');
            mainDivVariation.find('.customFeildVariation').append(inputField);
            break;
        case '__add_all':
            $(this).find('option').each(function () {
                var opValue = $(this).val();
                var opText = $(this).text();
                switch (opValue) {
                    case '__custom':
                        var inputField = customTextField('Type Variations here');
                        $(this).parent().parent().last().append(inputField);
                        break;
                    case '':
                        break;
                    case '__add_all':
                        break;
                    default:
                        if (opValue) {
                            optionHTML += '<div class="variationOptionID"><input type="text" readonly class="form-control" value="' + opText + '"><a optionVal = "' + opValue + '" class="cross-icon"><i class="fa fa-times" aria-hidden="true"></i></a></div>';
                        }

                        break;
                }
            });
            $(this).hide();
            var inputField = customTextField('Type Variations here', '');
            $(this).parent().append(inputField);
            // $(this).find('option:selected').hide();
            //  $('.attributeOptions option').prop('selected', function () {
            //      return this.defaultSelected;
            //   });
            //   optionHTML = '<div class="variationOptionID"><input type="text" class="form-control" value="' + options + '"><a optionVal = "' + optionsVal + '" class="cross-icon"><i class="fa fa-times" aria-hidden="true"></i></a></div>';
            break;
        default:
            $(this).find('option:selected').hide();
            mainDivVariation.find('.attributeOptions option').prop('selected', function () {
                return this.defaultSelected;
            });
            if (optionsVal) {
                optionHTML = '<div class="variationOptionID"><input type="text" readonly class="form-control" value="' + options + '"><a optionVal = "' + optionsVal + '" class="cross-icon"><i class="fa fa-times" aria-hidden="true"></i></a></div>';
            }

            break;
    }


    mainDivVariation.find('.varidationOptionValues').append(optionHTML);
    var attribute1 = $('#varidationOptionValues').find('input[type=text]').length;
    var attribute2 = $('#varidationOptionValues2').find('input[type=text]').length;
    var attribute3 = $('#varidationOptionValuesthree').find('input[type=text]').length;
    if (attribute1 == 0 && attribute2 == 0 && attribute3 == 0) {
        $('#btnvariationcheck').attr('disabled', 'disabled');
    } else {
        $('#btnvariationcheck').removeAttr('disabled');
    }
});
$(document).on("click", ".delete-variation", function () {
    var mainDivVariation = $(this).parentsUntil('.VariationMainDiv').parent();
    $('.VariationMainDiv').find("[value='" + mainDivVariation.find('.variation_attribute').val() + "']").show();
    mainDivVariation.find('.varidationOptionValues').empty();
    mainDivVariation.find(".hide-on-select").show();
    mainDivVariation.find('.open-secondary').hide();
    mainDivVariation.find('.results-text .color').empty()
    mainDivVariation.find('.variation_attribute').val('');
    mainDivVariation.find('.variation_attribute_unit').val('');
    mainDivVariation.find('.show-on-select-box').hide();
    mainDivVariation.find('.variationUnit').empty();
    mainDivVariation.find('.variationOptions').empty();
    mainDivVariation.find('.customFeildVariation').empty();
    var attribute1 = $('#varidationOptionValues').find('input[type=text]').length;
    var attribute2 = $('#varidationOptionValues2').find('input[type=text]').length;
    var attribute3 = $('#varidationOptionValuesthree').find('input[type=text]').length;
    if (!$('#variationAttributeone').val()) {
        if (!$('#variationAttributetwo').val()) {
            $(".VariationMainDiv").eq(1).css("display", "none");
            $('.show-on-select-box').eq(0).css("display", "none");
        }
    }
    if (!$('#variationAttributetwo').val()) {
        if (!$('#variationAttributethree').val()) {

            $(".VariationMainDiv").eq(2).css("display", "none");
            $('.show-on-select-box').eq(1).css("display", "none");
        }
    }


    if (attribute1 == 0 && attribute2 == 0 && attribute3 == 0 && mode == "edit") {
        $('#btnvariationcheck').attr('disabled', 'disabled');
        $('#edit-variation-txt').html('<p class="inside-variation">Please add at least one variation for this variable product</p>');
    } else {
        $('#btnvariationcheck').removeAttr('disabled');
    }
    mainDivVariation.find('.select-variation option').prop('selected', function () {
        return this.defaultSelected;
    });
});

function isEmptyObject(obj) {

    // null and undefined are "empty"
    if (obj == null)
        return true;
    // Assume if it has a length property with a non-zero value
    // that that property is correct.
    if (obj.length > 0)
        return false;
    if (obj.length === 0)
        return true;
   
    if (typeof obj !== "object")
        return true;
  
    for (var key in obj) {
        if (hasOwnProperty.call(obj, key))
            return false;
    }

    return true;
}
function capitalize(string) {
    return string.charAt(0).toUpperCase() + string.slice(1).toLowerCase();
}

function customTextField(optionHTML, inputID = '') {
    if (inputID) {
        inputID = inputID;
    } else {
        inputID = 'variation_optin_event';
    }
    var textBoxFeildHTML = '';
    textBoxFeildHTML += '<div class="input-group-body form-group">';
    textBoxFeildHTML += '<div class="input-group-body">';
    textBoxFeildHTML += '<input type="text" class="form-control add_new_option_input" data-unit="' + capitalize(optionHTML) + '" name="add_new_option_input" placeholder="' + capitalize(optionHTML) + '">';
    textBoxFeildHTML += '</div>';
    textBoxFeildHTML += '<div class="input-group-btnn">';
    textBoxFeildHTML += '<button name="add-custom" type="button" class="btn btn-primary btn-append ' + inputID + '" id="' + inputID + '">Add</button>';
    textBoxFeildHTML += '</div>';
    textBoxFeildHTML += '</div>';
    return textBoxFeildHTML;
}

jQuery('body').on('click', '.customAttributeField', function (e) {

    var mainDivVariation = $(this).parentsUntil('.VariationMainDiv').parent();
    ///console.log(mainDivVariation);
    var nameVariation = mainDivVariation.find('.add_new_option_input').val();
    if($.trim(nameVariation) == ''){
        return;
    }
    mainDivVariation.find('.customAttribute').hide()
    mainDivVariation.find('.customAttribute').find('.add_new_option_input').remove();
    mainDivVariation.find('.customAttribute').find('.customAttributeField').remove();
  
    mainDivVariation.find('#variationOption1').show();
    mainDivVariation.find('.show-on-select-box').show();
    mainDivVariation.find('.open-secondary').show();
    mainDivVariation.find(".hide-on-select").hide();
    mainDivVariation.find('.customvariation').empty();
    mainDivVariation.find('.variationUnit').empty();
    mainDivVariation.find('.variationOptions').empty();
    mainDivVariation.find('.customFeildVariation').empty();
    mainDivVariation.find('.variation_attribute').val(nameVariation);
    mainDivVariation.find('.results-text .color').text(nameVariation);
    var inputField = customTextField('Type Variations here');
    mainDivVariation.find('.customFeildVariation').append(inputField);
    // $('.VariationMainDiv').show();
     $(this).parents('.VariationMainDiv').show();
   
    let next = mainDivVariation.nextAll('.VariationMainDiv').first();
    if(next){
        next.show()
    }
});
jQuery('body').on('click', '.variation_optin_event', function (e) {
        function escapeHtml(str) {
            return str
                .replace(/&/g, '&amp;')    // Encode &
                .replace(/"/g, '&quot;')   // Encode "
                .replace(/'/g, '&#39;')    // Encode '
                .replace(/</g, '&lt;')     // Encode <
                .replace(/>/g, '&gt;')     // Encode >
                .replace(/\s+/g, ' ');     // Collapse multiple spaces into one
        }
    
        var optionHTML = '';
        var mainDivVariation = $(this).parentsUntil('.VariationMainDiv').parent();
        var nameVariation = mainDivVariation.find('.selct-box-tags .add_new_option_input').val();
    
        // Check for empty or whitespace-only input
        if (isEmptyOrSpaces(nameVariation) == false && nameVariation !== undefined) {
            // Escape special characters in nameVariation
            nameVariation = escapeHtml(nameVariation);
            
            // Properly enclose the value in quotes and insert it
            optionHTML = '<div class="variationOptionID">' +
                '<input type="text" readonly class="form-control" value="' + nameVariation + '">' + // Enclose in double quotes
                '<a optionVal="' + nameVariation + '" class="cross-icon">' +
                '<i class="fa fa-times" aria-hidden="true"></i></a>' +
                '</div>';
            
            // Append the new option to the parent container
            mainDivVariation.find('.varidationOptionValues').append(optionHTML);
            
            // Clear the input field
            mainDivVariation.find('.add_new_option_input').val('');
        }
    var attribute1 = $('#varidationOptionValues').find('input[type=text]').length;
    var attribute2 = $('#varidationOptionValues2').find('input[type=text]').length;
    var attribute3 = $('#varidationOptionValuesthree').find('input[type=text]').length;
    if (attribute1 > 0 || attribute2 > 0 || attribute3 > 0) {
        $('#btnvariationcheck').removeAttr('disabled');
    }
});
var quantityvalidator1 = false;
var quantityvalidator2 = false;
var quantityvalidatorDuplicate = false;
var skuvalidator1 = false;
var skuvalidator2 = false;
var skuvalidatorDuplicate = false;
var pricevalidator1 = false;
var pricevalidator2 = false;
var pricevalidatorDuplicate = false;

jQuery('body').on('click', '#data-ui-edit', function (e) {

    $('.shipping-address-gray.section-insight').show();
    $('.hide-restore-setting').show();
    $(".shipping-address-gray.top-selected").hide();
});
jQuery('body').on('click', '#advanced-option-sec', function (e) {

    $('.advanced-option-body').toggleClass("shoeme-body");
    $(this).parent().addClass('open-body-detail');
});
jQuery('body').on('click', '#restore-defaults', function (e) {

    $('.shipping-address-gray.section-insight').hide();
    $('.shipping-address-gray.top-selected').show();
    $(this).parent().hide();
});
jQuery('body').on('click', '.advanced-option-sec-popup', function (e) {

    $('.shipping-popup-modal  .advanced-option-body').toggleClass("shoeme-body");
    $(this).parent().addClass('open-body-detail');
});
jQuery('body').on('click', '.restore-defaults-popup', function (e) {

    $('.shipping-popup-modal  .shipping-address-gray.section-insight').hide();
    $('.shipping-popup-modal  .shipping-address-gray.top-selected').show();
    $(this).parent().hide();
});
jQuery('body').on('click', '.countryCheckBox', function (e) {
    var card = $(this).parentsUntil('.card').parent();
    if ($(this).is(':checked')) {
        card.find('input').prop('checked', true);
    } else {
        card.find('input').prop('checked', false);
    }
});
jQuery('body').on('click', '#all-countries-checkbox', function (e) {

    if ($(this).is(':checked')) {
        $('.countriesListCheckBox input').prop('checked', true);
    } else {
        $('.countriesListCheckBox input').prop('checked', false);
    }
});
jQuery('body').on('keyup', '#causeTitle', function (e) {
    if (e.keyCode == 13) {
        $("#addCauseBtn").trigger("click");
    }
});
jQuery().ready(function (en) {

    setTimeout(function () {
        $('.alert-success-mbt').css('disply', 'none');
    }, 5000);
   
});
jQuery('body').on('click', '.img-check', function (e) {
    $(this).parent().toggleClass("check-checkbox");
});
//jQuery('body').on('change', '#whomade , #whatisit , #whenwasitmade', function (e) {
//
//    if (this.value != 0) {
//        $(this).css('border-color', '#ced4da');
//    }
//});


function validatorProductForm(e, ptype = '') {

    var checkValidate = true;
    var fields = '';
    var numvalidate = /^[0-9.]*$/;
    console.log("ptype product type", ptype);

    if (ptype == 'virtual') {

        fields = ['productImage', 'productTitle', 'product_files', 'productMainCategory', 'productDescription', 'productstatus'];
    } else {
        fields = ['productImage', 'productTitle', 'productMainCategory', 'productDescription', 'productstatus', 'weight_type' ]
    }

    for (i = 0; i < fields.length; i++) {
        var fieldsID, fieldValue;
        if (fields[i] == 'productImage') {

            ///fieldsID = jQuery('#' + fields[i]);
            fieldsID = jQuery("[name='productImage[]']");
            fieldValue = fieldsID.val();

            if (typeof fieldValue === 'undefined') {

                fieldsID = jQuery('#' + fields[i]);
                fieldValue = fieldsID.val();
            } else {

                fieldsID = jQuery("[name='productImage[]']");
                fieldValue = fieldsID.val();
            }
            if (fieldValue != '' && fieldValue != 0) {

                fieldsID = jQuery('.custom-panel-style');
                validateField(fieldsID, 'TRUE', e);
            } else {
                checkValidate = false;
                fieldsID = jQuery('.custom-panel-style');
                validateField(fieldsID, 'FALSE', e);
            }
          
        }
        else if (fields[i] == 'product_files') {
            ///fieldsID = jQuery('#' + fields[i]);
            fieldsID = jQuery("[name='file_ids[]']");
            fieldValue = fieldsID.val();
            // console.log(fieldValue)
            if (typeof fieldValue === 'undefined') {

                fieldsID = jQuery('#' + fields[i]);
                fieldValue = fieldsID.val();
            } else {

                fieldsID = jQuery("[name='file_ids[]']");
                fieldValue = fieldsID.val();

            }
            if (fieldValue != '' && fieldValue != 0) {
                fieldsID = jQuery('.files-custom-panel-style');
                validateFieldDigitalFile(fieldsID, 'TRUE', e);
            } else {
                checkValidate = false;
                fieldsID = jQuery('.files-custom-panel-style');
                validateFieldDigitalFile(fieldsID, 'FALSE', e);
            }
          
        }
        else {
            fieldsID = jQuery('#' + fields[i]);
            fieldValue = fieldsID.val();
            if (fieldValue != '' && fieldValue != 0) {
                validateField(fieldsID, 'TRUE', e);
            } else {
                checkValidate = false;
                validateField(fieldsID, 'FALSE', e);
            }
            if ($("#Product-type-variant").val() == 'simple' || productType == 'simple') {
                if ($("#productcurrentstock").val() == '' || !numvalidate.test($("#productcurrentstock").val())) {
                    $("#productcurrentstock").css('border-color', 'red');
                    $("#productcurrentstock").focus();
                    checkValidate = false;
                }
            }else if($("#Product-type-variant").val() == 'variant' || productType == 'variant' || ptype == 'variant'){
                $('.vrstk').each(function(key) {
                    key++
                    if ($(this).val().trim() === '') {
                    $(this).css('border-color', 'red');
                         $(this).focus();
                         checkValidate = false;
                    }else{
                        $(this).css('border-color', '');
                    }
                });

                var isValid = true;

                $(".product_variation_image").each(function () {
                    let inputValue = $(this).val();
                    if (!inputValue) {
                        isValid = false; // Set validation flag to false
                        $(this).addClass('required-field'); // Add error class to highlight the invalid input
                        $(this).parent().parent().css('border', '1px solid red');
                        // Optionally, show an error message
                        if ($(this).siblings('.text-danger').length === 0) {
                            // Show an error message
                            $(this).after('<br><span class="text-danger" style="font-size: 12px; font-weight: 400;">Variation image missing</span>');
                        }
                
                        // Remove the error message after 5 seconds (5000 ms)
                        setTimeout(() => {
                            $(this).siblings('br, .text-danger').fadeOut(500, function () {
                                $(this).remove(); // Completely remove the element after fading out
                            });
                        }, 3000);
                        $("html, body").animate({
                            scrollTop: $('#tablevaridation').offset().top - 70
                        }, 200); 
                        checkValidate = false;
                    } else {
                        $(this).removeClass('required-field'); // Remove error class if valid
                        $(this).siblings('.text-danger').remove(); // Remove previous error message
                        $(this).parent().parent().css('border', '');
                    }
                });
            var isValid = true;
            $(".product_variation_image_new").each(function () {
               
                let inputValue = $(this).val(); // Get the value of the input
                if (!inputValue) {
                   
                    isValid = false; // Set validation flag to false
                    $(this).addClass('required-field'); // Add error class to highlight the invalid input
                    $(this).parent().parent().css('border', '1px solid red');
                    // Optionally, show an error message
                    if ($(this).siblings('.text-danger').length === 0) {
                        // Show an error message
                        $(this).after('<br><span class="text-danger" style="font-size: 12px; font-weight: 400;">Variation image missing</span>');
                    }
                    
                    // Remove the error message after 5 seconds (5000 ms)
                    setTimeout(() => {
                        $(this).siblings('br,.text-danger').fadeOut(500, function () {
                            $(this).remove(); // Completely remove the element after fading out
                        });
                    }, 3000);
                    $("html, body").animate({
                        scrollTop: $('#tablevaridation').offset().top - 70
                    }, 200); 
                    checkValidate = false;
                 
                } else {
                    $(this).removeClass('required-field'); // Remove error class if valid
                    $(this).siblings('.text-danger').remove(); // Remove previous error message
                    $(this).parent().parent().css('border', '');
                }
               
            });
            var isValid = true;
            $(".product_variation_image_specialcase").each(function () {
               
                let inputValue = $(this).val(); // Get the value of the input
                if (!inputValue) {
                   
                    isValid = false; // Set validation flag to false
                    $(this).addClass('required-field'); // Add error class to highlight the invalid input
                    $(this).parent().parent().css('border', '1px solid red');
                    // Optionally, show an error message
                    if ($(this).siblings('.text-danger').length === 0) {
                        // Show an error message
                        $(this).after('<br><span class="text-danger" style="font-size: 12px; font-weight: 400;">Variation image missing</span>');
                    }
                    
                    // Remove the error message after 5 seconds (5000 ms)
                    setTimeout(() => {
                        $(this).siblings('br,.text-danger').fadeOut(500, function () {
                            $(this).remove(); // Completely remove the element after fading out
                        });
                    }, 3000);
                    $("html, body").animate({
                        scrollTop: $('#tablevaridation').offset().top - 70
                    }, 200); 
                    checkValidate = false;
                 
                } else {
                    $(this).removeClass('required-field'); // Remove error class if valid
                    $(this).siblings('.text-danger').remove(); // Remove previous error message
                    $(this).parent().parent().css('border', '');
                }
               
            });
            var isValid = true;
            $(".product_variation_image_specialcase").each(function () {
               
                let inputValue = $(this).val(); // Get the value of the input
               
                if (!inputValue) {
                   
                    isValid = false; // Set validation flag to false
                    $(this).addClass('required-field'); // Add error class to highlight the invalid input
                    $(this).parent().parent().css('border', '1px solid red');
                    // Optionally, show an error message
                    if ($(this).siblings('.text-danger').length === 0) {
                       
                        // Show an error message
                        $(this).after('<br><span class="text-danger" style="font-size: 12px; font-weight: 400;">Variation image missing</span>');
                    }
                    
                    // Remove the error message after 5 seconds (5000 ms)
                    setTimeout(() => {
                        $(this).siblings('br,.text-danger').fadeOut(500, function () {
                            $(this).remove(); // Completely remove the element after fading out
                        });
                    }, 3000);
                    $("html, body").animate({
                        scrollTop: $('#tablevaridation').offset().top - 70
                    }, 200); 
                    checkValidate = false;
                 
                } else {
                    $(this).removeClass('required-field'); // Remove error class if valid
                    $(this).siblings('.text-danger').remove(); // Remove previous error message
                    $(this).parent().parent().css('border', '');
                }
               
            });
            }
        }
        // if (fields[i] == 'productTags') {
        //     fieldsID = jQuery("input[name=productTags]");
        //     fieldValue = fieldsID.val();
        //     if (fieldValue == '') {
        //         fieldsID = jQuery('.bootstrap-tagsinput');
        //         validateField(fieldsID, 'FALSE', e);
        //         checkValidate = false;
        //     } else {
        //         fieldsID = jQuery('.bootstrap-tagsinput input');
        //         validateField(fieldsID, 'TRUE', e);
        //     }
        // }
    }
    return checkValidate;
}
function validateField(fieldsID, fieldValue, e) {
    // console.log(fieldsID)
    // console.log(fieldValue)
    var isDisabled = fieldsID.is(':disabled');
    if (isDisabled) {
        fieldsID.css('border-color', 'none');
        return true;
    } else {
        if (fieldValue == 'TRUE') {
            fieldsID.css('border-color', 'rgba(0, 128, 0, 0.39)');
            return true;
        } else {
            fieldsID.css('border-color', 'red');
            fieldsID.focus();
            e.preventDefault();
            $('body').find('.panel-collapse').removeClass('show');
            $('body').find('.card-header').removeClass('active');

            $('body').find(fieldsID).parents().closest(".panel-collapse").addClass('show');
            $('body').find(fieldsID).parents().closest(".card-header").addClass('show');
            return false;
        }
    }

}
function validateFieldDigitalFile(fieldsID, fieldValue, e) {

    var isDisabled = fieldsID.is(':disabled');
    if (isDisabled) {
        fieldsID.css('border-color', 'none');
        return true;
    } else {
        if (fieldValue == 'TRUE') {
            fieldsID.css('border-color', 'rgba(0, 128, 0, 0.39)');
            return true;
        } else {
            fieldsID.css('border', '2px solid red');
            fieldsID.css('padding', '20px 0px');
            fieldsID.focus();
            e.preventDefault();
            $('body').find('.panel-collapse').removeClass('show');
            $('body').find('.card-header').removeClass('active');

            $('body').find(fieldsID).parents().closest(".panel-collapse").addClass('show');
            $('body').find(fieldsID).parents().closest(".card-header").addClass('show');
            return false;
        }
    }

}
function formatBytes(bytes, decimals) {
    if (bytes == 0)
        return '0 Bytes';
    var k = 1024,
        dm = decimals || 2,
        sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'],
        i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + ' ' + sizes[i];
}





function progressHandler(event) {
    jQuery('body').find("#" + itemId + " .loaded_n_total").html("Uploaded " + formatBytes(event.loaded) + " bytes of " + formatBytes(event.total));
    var percent = (event.loaded / event.total) * 100;
    ///// _("progressBar").value = Math.round(percent);
    jQuery('body').find("#" + itemId + " .upload-status").html(Math.round(percent) + "% uploaded... please wait");
}

function completeHandler(event) {
    ///_("status").innerHTML = event.target.responseText;
    jQuery('body').find("#" + itemId + " .upload-status").html('Complete');
    //// _("progressBar").value = 0;
}

function errorHandler(event) {
    jQuery('body').find("#" + itemId + " .upload-status").html('Upload Failed');
}

function abortHandler(event) {
    jQuery('body').find("#" + itemId + " .upload-status").html('Upload Aborted');
}
var uploadingItems = new Array();
function digitalUpload(input) {

    var checkCount = $('body').find('.uploadItemSection .upload-container-section .list-panel-upload').length;
    ///alert(checkCount)
    if (checkCount > 4) {
        ///  alert(checkCount)
        alert('You can  upload max 5 files for each  file max allowed size is  20 MB.');
        return false;
        ``
    }
    var i_inner = 0, len = input.files.length, file;
    var totalItem = jQuery('#all-uplpad-image .image-panel').length;
    var formdata = new FormData();
    for (; i_inner < len; i_inner++) {

        file = input.files[i_inner];
        var vol_size = file.size;
        if (vol_size <= 0) {
            vol_size = 1;
        }
        vol_size = (vol_size) / 1024;
        vol_size = parseInt(vol_size);
        if (vol_size < 20480) {
            totalItem = totalItem + 1;
            regex = /.jpg|.jpeg|.gif|.png|.bmp|.doc|.gif|.mobi|.mov|.mp3|.mpeg|.pdf|.psp|.rtf|.stl|.zip|.ePUB|.iBook/;
            if ((!!file.type.match(regex))) {
                var itemId = 'item_' + randomNumberFromRange(213, 5606565656);
                formdata.append("digitalItem[]", file);
                var items = new Array();
                items['size'] = file.size;
                items['name'] = file.name;
                items['type'] = file.type;
                uploadingItems.push(items);
                checkCount++;
            } else {
                $('body').find('.uploadItemSection .uploadImages').delay(2000).hide();
                alert('Please upload files that end in .bmp, .doc, .gif, .jpeg, .jpg, .mobi, .mov, .mp3, .mpeg, .pdf, .png, .psp, .rtf, .stl, .txt, .zip, .ePUB, or .iBook.');
            }
        } else {
            $('body').find('.uploadItemSection .uploadImages').delay(2000).hide();
            alert('Your file size is ' + formatBytes(file.size) + '. Max allowed size is  20 MB.');
        }
        /// alert(checkCount)
        if (checkCount > 5) {
            break;
        }
    }
    if (uploadingItems.length > 0) {

        $('body').find('.uploadItemSection .uploadImages').delay(2000).show();
        $('body').find('.uploadItemSection .upload-container-section').delay(2000).hide();
        $.ajax({
            url: "/uploadDigital",
            method: 'POST',
            data: formdata,
            processData: false,
            contentType: false,
            success: function (resposneItems) {
                showUploadedDigitalItem(resposneItems);
            },
            error: function () {
                alert('Something Wrong Here');
                $('body').find('.uploadItemSection .upload-container-section').delay(2000).hide();
            }
        });
        //        var ajax = new XMLHttpRequest();
        //
        //        ajax.upload.addEventListener("progress",
        //                function (event) {
        //                    jQuery('body').find("#" + itemId + " .loaded_n_total").html("Uploaded " + formatBytes(event.loaded) + " bytes of " + formatBytes(event.total));
        //                    var percent = (event.loaded / event.total) * 100;
        //                    jQuery('body').find("#" + itemId + " .upload-status").html(Math.round(percent) + "% uploaded... please wait");
        //                }
        //        , false);
        //        ajax.addEventListener("load", function (event) {
        //            jQuery('.rghtexp').append('bbbbb');
        //            jQuery('body').find("#" + itemId + " .upload-status").html('Complete');
        //        }, false);
        //        ajax.addEventListener("error", function (event) {
        //            jQuery('body').find("#" + itemId + " .upload-status").html('Upload Failed');
        //        }, false);
        //        ajax.addEventListener("abort", function (event) {
        //            jQuery('body').find("#" + itemId + " .upload-status").html('Upload Aborted');
        //        }, false);
        //        ajax.open("POST", "/uploadDigital", false);
        //        ajax.setRequestHeader('X-CSRF-Token', $('meta[name=csrf-token]').attr('content'));
        //        ajax.send(formdata);
    }
    if (document.getElementById("productDigital").files.length > 0) {
        jQuery('#productDigital').val('');
    }
}
function validateFieldById(id) {
    fieldsID = jQuery('#' + id);
    if (fieldsID.val() != "") {
        fieldsID.css('border-color', 'rgba(0, 128, 0, 0.39)');

    } else if (fieldsID.val() <= 0) {
        fieldsID.css('border-color', 'red');
        fieldsID.focus();
    } else {
        fieldsID.css('border-color', 'red');
        fieldsID.focus();

    }
}
function deleteItem(itemId) {
    $('body').find('.uploadItemSection .uploadImages').delay(2000).show();
    var input_field_value = jQuery('#itemSource_' + itemId).val();
    if (input_field_value) {

        $('#item_' + itemId).remove();
        $.ajax({
            url: "/deleteProductItem",
            method: 'POST',
            data: { fileName: input_field_value },
            success: function (res) {
                if (($('body').find('.uploadItemSection .upload-container-section .list-panel-upload').length) == 0) {
                    $('body').find('.uploadItemSection .upload-container-section').delay(2000).hide();
                } else {
                    $('body').find('.uploadItemSection .upload-container-section').delay(2000).show();
                }
            }
        });
    }
    $('body').find('.uploadItemSection .uploadImages').delay(2000).hide();
}
function showUploadedDigitalItem(source) {

    var itemDiv = document.getElementById('upload-container-section');
    var html = '';
    var i_inner = 0, len = source.fileName.length;
    for (; i_inner < len; i_inner++) {
        /// console.log(uploadingItems);
        randomCode = randomNumberFromRange(101, 99999999999);
        html += '<div  id="item_' + randomCode + '" class="list-panel-upload">';
        html += '<span class="name-file">' + uploadingItems[i_inner].name + '</span>';
        html += '<span class="name-size">' + formatBytes(uploadingItems[i_inner].size) + '</span>';
        html += '<input type="hidden" id="itemSource_' + randomCode + '" name="digitalItem[]" value="' + source.fileName[i_inner] + '" />';
        html += '<span class="file-remove">';
        html += '<a href="javascript:;" onclick="deleteItem(' + randomCode + ')">Remove</a>';
        html += '</span>';
        html += '</div>';
    }


    uploadingItems = new Array();
    ///console.log(html);
    itemDiv.insertAdjacentHTML('beforeend', html);
    $('body').find('.uploadItemSection .uploadImages').delay(2000).hide();
    $('body').find('.uploadItemSection .upload-container-section').delay(2000).show();
}



var uploadingImages = new Array();
var Ima = new Array();
function readURL(input) {
    
   
    var req_width = 720,
        req_height = 480;
    var validate = false;
    var i_inner = 0, len = input.files.length, reader, file;
    var totalImages = jQuery('#all-uplpad-image .image-panel').length;
    imgCounter = totalImages;
    if (imgCounter >= 20) {
        alert('Already 20 images Uploaded.');
        return false;
    } else {

    }
    formdata = new FormData();
    for (; i_inner < len; i_inner++) {
        $('body').find('.photoSection .uploadImages').delay(1000).show();
        $('.uploadImages').show();
        file = input.files[i_inner];
        var vol_size = file.size;
        if (vol_size <= 0) {
            vol_size = 1;
        }
        vol_size = (vol_size) / 1024;
        vol_size = parseInt(vol_size);
        if (vol_size < 50000000000000000) {
            imgCounter = imgCounter + 1;
            if (!!file.type.match(/^image\/(png|jpeg|jpg)$/)) {

                if (window.FileReader) {
                    reader = new FileReader();
                    reader.onloadend = function (e) {

                        var image = new Image();
                        image.src = e.target.result;
                        image.onload = function () {
                            // access image size here 
                            var dWidth = this.naturalWidth,
                                dHeight = this.naturalHeight;
                            // if (dWidth < req_width || dHeight < req_height) {
                            //     alert('Your Image size is (' + dWidth + 'w x ' + dHeight + 'h). The selected Image should be (' + req_width + 'w x ' + req_height + 'h) pixel or greater in size.');
                            // } else {
                            uploadingImages.push(e.target.result);
                            // }
                        };
                    };
                    reader.readAsDataURL(file);
                }
                if (formdata) {
                    validate = 1;
                    formdata.append("productImage[]", file);
                }
            } else {
                swal('File with disallowed extension ' +  file.name);
                $('body').find('.photoSection .uploadImages').delay(1000).hide();
            }
        } else {
            $('body').find('.photoSection .uploadImages').delay(1000).hide();
            ///$('.uploadImages').delay(1000).hide();
            /// imgCounter = imgCounter - 1;
            //// $('#thumbnail-image-box').html(' <div class="prdimg pi2 "><span class="imgtg">Thumbnail</span> </div> ');
            alert('Your Image size is ' + vol_size + 'kb. Max allowed size is 50MB.');


        }
        ///  alert(imgCounter)
        if (imgCounter > 0) {
            if (imgCounter == 5) {
                break;
            }
        }


    }
    //    alert(imgCounter);
    //    if (imgCounter) {
    //        $('.imageBox').eq(imgCounter - 1).hide();
    //    }

    if (validate) {
        $.ajax({
            url: "/ProductImageRequest",
            method: 'POST',
            data: formdata,
            processData: false,
            contentType: false,
            success: function (uploadingImages) {
                console.log(uploadingImages);
                $('.uploadImages').show();

                showUploadedItem(uploadingImages);
                showUploadedItem_popup(uploadingImages);
                var totalImages = jQuery('#all-uplpad-image .image-panel').length;

if (totalImages <= 0) {
    // Disable the button
    jQuery('.btn-primary-back.selectFeatureImageAdd').attr('disabled', 'disabled');
    jQuery('.variationImageIcon').css({
        'pointer-events' : 'none',
        'opacity' : '0.5'
    });
    
} else {
    // Enable the button
    jQuery('.btn-primary-back.selectFeatureImageAdd').removeAttr('disabled');
    jQuery('.variationImageIcon').css({
        'pointer-events': 'auto',
        'opacity': ''
    });
    
}
                //alert(totalImages);

            },
            error: function () {
                alert('Something Wrong Here');
                $('body').find('.photoSection .uploadImages').delay(1000).hide();
            }
        });
    }

    if (document.getElementById("productImage").files.length > 0) {
        jQuery('#productImage').val('');
    }
}

var formShippingData = new FormData();
function abc() {
    alert("abc called");
}
function shippingValidation() {
    formShippingData = new FormData();
    $shippingoption = document.getElementById("calculate-shipping-option");
    $shippingOptionValue = $shippingoption.options[$shippingoption.selectedIndex].value;
    if ($shippingOptionValue == 'clculateRecommeded' || $shippingOptionValue == 'fixedCost') {
        formShippingData.append("shippingCost", $shippingOptionValue);
        $shippingoption.setAttribute("style", "border-color: #ced4da");
    } else {
        $shippingoption.setAttribute("style", "border-color: red;");
        console.log('Please select Shipping Method');
        return false;
    }
    if ($shippingOptionValue == 'clculateRecommeded') {
        $zipCode = document.getElementById("zipCode");
        $zipCodeValue = $zipCode.value;
        if ($zipCodeValue) {
            formShippingData.append("zipCode", $zipCodeValue);
            $zipCode.setAttribute("style", "border-color: #ced4da;");
        } else {
            $zipCode.setAttribute("style", "border-color: red;");
            console.log('Please fill zipcode');
            return false;
        }
        $countryCheck = true;
        $('input[name="countries[]"]:checked').each(function () {
            var countryId = this.value;
            if (countryId) {
                formShippingData.append("countries[]", this.value);
                countryId = '';
                $countryCheck = false;
            }
        });
        if ($countryCheck) {
            console.log('Please select countries will you ship to');
            return false;
        }

        $serviceCheck = true;
        $('input[name="shippingServices[]"]:checked').each(function () {
            var serviceId = this.value;
            if (serviceId) {
                formShippingData.append("shippingServices[]", this.value);
                serviceId = '';
                $serviceCheck = false;
            }
        });
        if ($serviceCheck) {
            console.log('Please select shipping services');
            return false;
        }
        var domesticFreeShippings = $('input[name="domesticFreeShippings"]:checked');
        if (domesticFreeShippings) {
            formShippingData.append("domesticFreeShippings", 1);
        } else {
            formShippingData.append("domesticFreeShippings", 0);
        }
        var internationalFreeShipping = $('input[name="internationalFreeShipping"]:checked');
        if (internationalFreeShipping) {
            formShippingData.append("internationalFreeShipping", 1);
        } else {
            formShippingData.append("internationalFreeShipping", 0);
        }


        var domesticHandlingFee = $('input[name="domesticHandlingFee"]').val();
        if (domesticHandlingFee) {
            formShippingData.append("domesticHandlingFee", domesticHandlingFee);
        }
        var internationalHandlingFee = $('input[name="internationalHandlingFee"]').val();
        if (internationalHandlingFee) {
            formShippingData.append("internationalHandlingFee", internationalHandlingFee);
        }

    } else {
        $originCountry = document.getElementById("shippingOriginCountry");
        $originCountryValue = $originCountry.options[$originCountry.selectedIndex].value;

        if ($originCountryValue) {
            formShippingData.append("shippingOrigin", $originCountryValue);
            $originCountry.setAttribute("style", "border-color: #ced4da;");
        } else {
            $originCountry.setAttribute("style", "border-color: red;");
            console.log('Please select shipping origin');
            return false;
        }
    }
    $processingTime = document.getElementById("processing-time-perioud");
    $processingTimeValue = $processingTime.value;
    if ($processingTimeValue) {
        formShippingData.append("processTimeOption", $processingTimeValue);
        if ($processingTimeValue == 'custom-range') {
            $startRange = document.getElementById("startRange");
            $startRangeValue = $startRange.options[$startRange.selectedIndex].value;
            $endRange = document.getElementById("endRange");
            $endRangeValue = $endRange.options[$endRange.selectedIndex].value;

            if ($startRangeValue == '' || $startRangeValue == 'From...') {
                console.log('Please select start time');
                return false;
            }
            if ($endRangeValue == '' || $endRangeValue == 'To...') {
                console.log('Please select end time');
                return false;
            } else {
                if ($startRangeValue > $endRangeValue) {
                    console.log('Please select end time greater then start date.');
                    return false;
                } else {
                    formShippingData.append("processTimeStart", $startRangeValue);
                    formShippingData.append("processTimeEnd", $endRangeValue);

                }
            }

            if (document.getElementById('business-days').checked) {
                $selectTimeValue = document.getElementById('business-days').value;
            } else {
                $selectTimeValue = document.getElementById('business-days').value;
            }

            if ($selectTimeValue == 'day' || $selectTimeValue == 'week') {
                formShippingData.append("processTime", $selectTimeValue);
            } else {
                console.log('Please select processing time');
                return false;
            }
        }
    } else {
        console.log('Please select processing period');
        return false;
    }
    if ($shippingOptionValue == 'fixedCost') {


        $fixCostOneItem = document.getElementById("fixCostOneItem");
        $fixCostOneItemValue = $fixCostOneItem.value;
        if ($fixCostOneItemValue == '') {
            $fixCostOneItem.setAttribute("style", "border-color: red;");
            console.log('Please fill price');
            return false;
        } else {
            $fixCostOneItem.setAttribute("style", "border-color: #ced4da;");
        }
        $counterIndex = 0;
        var fieldName, fieldOneItemPrice, fieldEachItemPrice;
        $('.fixedShippingCostMainDiv').find('input , select').each(function () {
            $counterIndex++;
            if ($counterIndex == 1) {
                fieldName = $(this).val();
            } else if ($counterIndex == 2) {
                fieldOneItemPrice = $(this).val();
            } else {
                fieldEachItemPrice = $(this).val();
            }
            if ($counterIndex == 3) {
                formShippingData.append("shippingFixCost[destinationId][]", fieldName);
                formShippingData.append("shippingFixCost[oneItem][]", fieldOneItemPrice);
                formShippingData.append("shippingFixCost[eachItem][]", fieldEachItemPrice);
                $counterIndex = 0;
            }
        });
        $counterUpgradesIndex = 0;
        var upgradename, destination, upgradeOneItemPrice, upgradeEachItemPrice;
        $('.shipping-upgrade-optional').find('input , select').each(function () {
            $counterUpgradesIndex++;
            if ($counterUpgradesIndex == 1) {
                upgradename = $(this).val();
            } else if ($counterUpgradesIndex == 2) {

                if ($(this).val() == 'international') {
                    destination = $(this).val();
                } else {
                    destination = 'domestic';
                }
            } else if ($counterUpgradesIndex == 3) {
                upgradeOneItemPrice = $(this).val();
            } else {
                upgradeEachItemPrice = $(this).val();
            }
            if ($counterUpgradesIndex == 4) {
                if (upgradename && upgradeOneItemPrice) {
                    formShippingData.append("shippingUpgrades[upgrapeName][]", upgradename);
                    formShippingData.append("shippingUpgrades[level][]", destination);
                    formShippingData.append("shippingUpgrades[oneItem][]", upgradeOneItemPrice);
                    formShippingData.append("shippingUpgrades[eachItem][]", upgradeEachItemPrice);
                }

                $counterUpgradesIndex = 0;
            }
        });

    }
    return true;
}
jQuery('body').on('click', '#saveShippingOption', function (e) {



    if (shippingValidation()) {
        console.log('Done Validation');
        $('#create-profile-popup').modal('show');
    } else {
        console.log('Error Validation');
    }
    //// $(".delete-variation").trigger("click");
    ///jQuery(".uploadImages-popup").show();

});


//jQuery('body').on('click', '#saveShippingOption', function (e) {
//
//
//
//    if (shippingValidation()) {
//        console.log('Done Validation');
//        $('#create-profile-popup').modal('show');
//    } else {
//        console.log('Error Validation');
//    }
//    //// $(".delete-variation").trigger("click");
//    ///jQuery(".uploadImages-popup").show();
//
//});
function addvalidateShippingPopup() {
    
    var error = true;
    var addzipCode = $('#add_origin_zip_code').val();
    var addprofilename = $('#add_profile_name');
    var shipping_address1 = $('#shipping_address1');
    var shipping_address2 = $('#shipping_address2');
    var shipping_city = $('#shipping_city');
    var shipping_state_id = $('#shipping_state_id');
    var shipping_country_id = $('#shipping_country_id');
    var processTime = $('select[name="process_time"]').val();
    var addShipSelectedVal = $('#select-shipoption').val();
    // var adddomesticShipVal = $('#add_domestic_handling_fee');
    var numvalidate = /^[0-9.]*$/;
    if (addzipCode == "" || !numvalidate.test(addzipCode)) {
        $('#add_origin_zip_code').css('border-color', 'red');
        console.log("2447");
        error = false;
    } else {
        $('#add_origin_zip_code').css('border-color', 'rgba(0, 128, 0, 0.39)');
    }

    if (addprofilename.val() == "") {
        addprofilename.css('border-color', 'red');
        error = false;
        console.log("2455");
    } else {
        addprofilename.css('border-color', 'rgba(0, 128, 0, 0.39)');
    }

    if (shipping_address1.val() == "") {
        shipping_address1.css('border-color', 'red');
        error = false;
    } else {
        shipping_address1.css('border-color', 'rgba(0, 128, 0, 0.39)');
    }

    if (shipping_address2.val() == "") {
        shipping_address2.css('border-color', 'red');
        error = false;
    } else {
        shipping_address2.css('border-color', 'rgba(0, 128, 0, 0.39)');
    }

    if (shipping_city.val() == "") {
        shipping_city.css('border-color', 'red');
        error = false;
    } else {
        shipping_city.css('border-color', 'rgba(0, 128, 0, 0.39)');
    }

    if (shipping_state_id.val() == "") {
        shipping_state_id.css('border-color', 'red');
        error = false;
    } else {
        shipping_state_id.css('border-color', 'rgba(0, 128, 0, 0.39)');
    }

    if (shipping_country_id.val() == "") {
        shipping_country_id.css('border-color', 'red');
        error = false;
    } else {
        shipping_country_id.css('border-color', 'rgba(0, 128, 0, 0.39)');
    }

    if ($('#add_domestic_free_shipping').is(":checked") == true) {
        $('input[name="edit_ship_service[]').prop('checked', false);
        $('input[name="flat_shipping_rate"]').val(0);
    }
    if ($($('input[name="edit_ship_service[]').is(":checked") == true) && $('input[name="edit_ship_service[]"]:checked').length > 0 && $('input[name="flat_shipping_rate"]').val() > 0) {
        $('.sweet-alert').addClass("shipping_method_error");
        swal('Please Select one shipping method');
        $('input[name="flat_shipping_rate"]').val(0);

        error = false;
    } else {
        console.log("else cross check");
    }
    if (addShipSelectedVal == 'united-state' && $('#add_domestic_free_shipping').is(":checked") == false && $('input[name="edit_ship_service[]"]:checked').length == 0) {
        error = false;
        $('.listing-checkbox').css('border-color', 'red');
        // $("input[name=add_ship_service[]]").closest('.listing-checkbox').addClass('required-ship');
    }
    else {
        $('.listing-checkbox').css('border-color', 'rgba(0, 128, 0, 0.39)');
    }
    if (addShipSelectedVal == 'other' && $('#add_domestic_free_shipping').is(":checked") == false && ($('input[name="flat_shipping_rate"]').val() == "" || $('input[name="flat_shipping_rate"]').val() == 0)) {
        console.log("other");
        error = false;
        $('input[name="flat_shipping_rate"]').css('border-color', 'red');
    } else {
        
        $('input[name="flat_shipping_rate"]').css('border-color', 'rgba(0, 128, 0, 0.39)');

    }
    // if (adddomesticShipVal.val() != "" && !numvalidate.test(adddomesticShipVal.val())) {
    //     adddomesticShipVal.css('border-color', 'red');
    //     error = false;
    // } else {
    //     adddomesticShipVal.css('border-color', 'rgba(0, 128, 0, 0.39)');
    // }
    // if (processTime == 'custom-range') {
    //     if ($('select[name="start_range"]').val() == "") {
    //         $('select[name="start_range"]').css('border-color', 'red');
    //         error = false;
    //     }
    //     if ($('select[name="end_range"]').val() == "") {
    //         $('select[name="end_range"]').css('border-color', 'red');
    //         error = false;
    //     }
    //     if ($('select[name="start_range"]').val() >= $('select[name="end_range"]').val()) {
    //         $('select[name="start_range"]').css('border-color', 'red');
    //         $('select[name="end_range"]').css('border-color', 'red');
    //         error = false;
    //     }
    // }
    return error;
}
function editvalidateShippingPopup() {
    var error = true;
    var editzipCode = $('#edit_origin_zip_code').val();
    var editprofilename = $('#edit_profile_name');
    var processTime = $('select[name="process_time"]').val();
    var countryShipSelectedVal = $('#editselect-shipoption').val();
    var shipping_address1 = $('#shipping_address1');
    var shipping_address2 = $('#shipping_address2');
    var shipping_city = $('#shipping_city');
    var shipping_state_id = $('#shipping_state_id');
    var shipping_country_id = $('#shipping_country_id');
    // var editdomesticShipVal = $('#edit_domestic_handling_fee');
    var numvalidate = /^[0-9.]*$/;
    var alphanumvalidate = /^[a-zA-Z0-9]*$/;
    var productWeight = $("#productWeight");
    var shipService = $('input[name="edit_ship_service"]:checked');
    //zipcode
    if (editzipCode == "" || !alphanumvalidate.test(editzipCode)) {
        $('#edit_origin_zip_code').css('border-color', 'red');
        console.log("2505");
        error = false;
    } else {
        $('#edit_origin_zip_code').css('border-color', 'rgba(0, 128, 0, 0.39)');
    }
    //profile
    if (editprofilename.val() == "") {
        console.log("2512");
        editprofilename.css('border-color', 'red');
        error = false;
    } else {
        editprofilename.css('border-color', 'rgba(0, 128, 0, 0.39)');
    }
    if (shipping_address1.val() == "") {
        shipping_address1.css('border-color', 'red');
        error = false;
    } else {
        shipping_address1.css('border-color', 'rgba(0, 128, 0, 0.39)');
    }

    if (shipping_address2.val() == "") {
        shipping_address2.css('border-color', 'red');
        error = false;
    } else {
        shipping_address2.css('border-color', 'rgba(0, 128, 0, 0.39)');
    }

    if (shipping_city.val() == "") {
        shipping_city.css('border-color', 'red');
        error = false;
    } else {
        shipping_city.css('border-color', 'rgba(0, 128, 0, 0.39)');
    }

    if (shipping_state_id.val() == "") {
        shipping_state_id.css('border-color', 'red');
        error = false;
    } else {
        shipping_state_id.css('border-color', 'rgba(0, 128, 0, 0.39)');
    }

    if (shipping_country_id.val() == "") {
        shipping_country_id.css('border-color', 'red');
        error = false;
    } else {
        shipping_country_id.css('border-color', 'rgba(0, 128, 0, 0.39)');
    }


    if ($('#domestic_free_shipping').is(":checked") == true) {
        $('input[name="edit_ship_service[]').prop('checked', false);
        $('input[name="flat_shipping_rate"]').val(0);
    }
    if (countryShipSelectedVal == 'united-state' && $('#domestic_free_shipping').is(":checked") == false && $('input[name="edit_ship_service[]"]:checked').length == 0) {
        error = false;
        $('.listing-checkbox').css('border-color', 'red');
    }
    else {
        $('.listing-checkbox').css('border-color', 'rgba(0, 128, 0, 0.39)');
    }
    if (countryShipSelectedVal == 'other' && $('#domestic_free_shipping').is(":checked") == false && ($('input[name="flat_shipping_rate"]').val() == "" || $('input[name="flat_shipping_rate"]').val() == 0)) {
        console.log("2524");
        error = false;
        $('input[name="flat_shipping_rate"]').css('border-color', 'red');
    } else {
        $('input[name="flat_shipping_rate"]').css('border-color', 'rgba(0, 128, 0, 0.39)');

    }
    // if (editdomesticShipVal.val() != "" && !numvalidate.test(editdomesticShipVal.val())) {
    //     console.log("2532");
    //     editdomesticShipVal.css('border-color', 'red');
    //     error = false;
    // } else {
    //     editdomesticShipVal.css('border-color', 'rgba(0, 128, 0, 0.39)');
    // }
    // //}
    // if (processTime == 'custom-range') {
    //     if ($('select[name="start_range"]').val() == "") {
    //         $('select[name="start_range"]').css('border-color', 'red');
    //         error = false;
    //     }
    //     if ($('select[name="end_range"]').val() == "") {
    //         $('select[name="end_range"]').css('border-color', 'red');
    //         error = false;
    //     }
    //     if ($('select[name="start_range"]').val() > $('select[name="end_range"]').val()) {
    //         $('select[name="start_range"]').css('border-color', 'red');
    //         $('select[name="end_range"]').css('border-color', 'red');
    //         error = false;
    //     }
    // }
    if ($('input[name="domestic_free_shipping"]').prop("checked") == false) {
        if (productWeight.val() > 0.8 && shipService.val() == 'usps-first-class' && countryShipSelectedVal == 'united-state') {
            error = false;
            alert("product weight can not greater then 0.8 lbs in case of First Class Package Services.");

        }
    }
    return error;
}
    jQuery('body').on('click', '#updateDuplicateProfile', function (e) {
        if (editvalidateShippingPopup() == true) {
            var submitedForm = $('#shipping-form').serialize();
            sendShippingRequest(submitedForm);
        }
    });
    jQuery('body').on('click', '#updateDuplicateMangeShippingProfile', function (e) {
        if (editvalidateShippingPopup() == true) {
            var submitedForm = $('#shipping-form').serialize();
            sendShippingRequestManageShip(submitedForm,'update');
        }
    });
    $(document).on('click', '#saveShippingOption', function () {
        if (validateShippingPopup() == true) {
            var submitedForm = $('#shipping-custom-form').serialize();
            sendShippingRequest(submitedForm);
        }
    });
    $(document).on('click', '#save_custom_shipp', function () {
        if (addvalidateShippingPopup() == true) {
            var submitedForm = $('#save_custom_shipping').serialize();
            sendShippingRequest(submitedForm);
        }
    });
    $(document).on('click', '#save_custom_shipp_manageship', function () {
        if (addvalidateShippingPopup() == true) {
            var submitedForm = $('#save_custom_shipping').serialize();
            sendShippingRequestManageShip(submitedForm,'added');
        }
    });
function sendShippingRequest(submitedForm) {
    $.ajax({
        url: "/saveshipping",
        method: 'POST',
        data: submitedForm,
        //        processData: false,
        //        contentType: false,

        success: function (response) {
           
            if (response.error) {
                toastr.options = {
                    closeButton: true, // Add close button
                    disableTimeOut: true,
                    tapToDismiss: true,
                    positionClass: 'toast-top-right',
                    toastClass: 'toast-icon custom-toast-success'
                    };
                toastr.error(response.error);
            }else{
                toastr.options = {
                    closeButton: true, // Add close button
                    disableTimeOut: true,
                    tapToDismiss: true,
                    positionClass: 'toast-top-right',
                    toastClass: 'toast-icon custom-toast-success'
                    };
                toastr.success('Shipping profile added'); 
            }
           
            $('.modal').modal('hide');
            jQuery(".uploadImages-popup").hide();
            $('#create-profile-popup').modal('hide');
            jQuery("#shippingProfileName").val('');
            $('.list-container-scroll').empty();
            $('.list-container-scroll').append(response.view);
            

        },
        // error: function () {
        //     alert('Something Wrong Here');
        // }
    });
}
function sendShippingRequestManageShip(submitedForm,$flag) {
    $.ajax({
        url: "/savemanagedshipping",
        method: 'POST',
        data: submitedForm,
        //        processData: false,
        //        contentType: false,
        success: function (response) {
           
            if(response.status == false){
            
                toastr.options = {
                    closeButton: true, // Add close button
                    disableTimeOut: true,
                    tapToDismiss: true,
                    positionClass: 'toast-top-right',
                    toastClass: 'toast-icon custom-toast-success'
                    };
                toastr.error("shipping profile name already exists for this user");
            $('.modal').modal('hide');
            jQuery(".uploadImages-popup").hide();
            $('#create-profile-popup').modal('hide');
            jQuery("#shippingProfileName").val('');
            $('.list-container-scroll').empty();
            $('.list-container-scroll').append(response.view);
            }else{
               if($flag == 'added'){
                toastr.options = {
                    closeButton: true, // Add close button
                    disableTimeOut: true,
                    tapToDismiss: true,
                    positionClass: 'toast-top-right',
                    toastClass: 'toast-icon custom-toast-success'
                    };
            toastr.success("shipping profile added");
               }else{
                toastr.options = {
                    closeButton: true, // Add close button
                    disableTimeOut: true,
                    tapToDismiss: true,
                    positionClass: 'toast-top-right',
                    toastClass: 'toast-icon custom-toast-success'
                    };
            toastr.success("shipping profile updated");
               }
               
            $('.modal').modal('hide');
            jQuery(".uploadImages-popup").hide();
            $('#create-profile-popup').modal('hide');
            jQuery("#shippingProfileName").val('');
            $('.list-container-scroll').empty();
            $('.list-container-scroll').append(response.view);
            }
            

        },
        error: function () {
            alert('Something Wrong Here');
        }
    });
}

jQuery('body').on('click', '#create-profile', function (e) {

    ////$('#create-profile-popup').modal('show');
    $shippingProfileName = document.getElementById("shippingProfileName");
    $shippingProfileNameValue = $shippingProfileName.value;
    ///   alert($shippingProfileNameValue)
    /// return false;
    if ($shippingProfileNameValue == '') {
        $shippingProfileName.setAttribute("style", "border-color: red;");
        alert('Please fill shipping  profile name');
        return false;
    } else {
        $shippingProfileName.setAttribute("style", "border-color: #ced4da;");
        formShippingData.append("profileName", $shippingProfileNameValue);
        jQuery(".uploadImages-popup").show();
        sendShippingRequest();
    }
});

jQuery('body').on('change', '[name="shippingOriginCountry"]', function (e) {
    ///alert(this.options[this.selectedIndex].text)
    document.getElementById('defaultShippingCountry').value = this.value;
    document.getElementById('originCountry').innerHTML = this.options[this.selectedIndex].text;

});
function deleteImage(imageID) {
    var input_field_value = jQuery('#imagesrc' + imageID).val();
    
    if (input_field_value) {

        Imagesbox = jQuery('#all-uplpad-image .image-panel').length;
        totalImages = Imagesbox - 1;
        if (totalImages < 6) {
            $('#uploadImages').show();
            $('.imageBox').show();
            ///  $('body').find('.photoSection .uploadImages').delay(1000).show();
            ////$('.imageBox').eq(Imagesbox - 1).show();
        }
        if (totalImages == 3) {
            jQuery('body').find('.col-md-8.rghtexp').removeClass('image-four');
            jQuery('body').find('.col-md-8.rghtexp').removeClass('image-five');
            jQuery('body').find('.col-md-8.rghtexp').removeClass('image-six');
        }


        if (totalImages == 4) {
            jQuery('body').find('.col-md-8.rghtexp').addClass('image-four');
            jQuery('body').find('.col-md-8.rghtexp').removeClass('image-five');
            jQuery('body').find('.col-md-8.rghtexp').removeClass('image-six');
        }

        if (totalImages == 5) {
            jQuery('body').find('.col-md-8.rghtexp').removeClass('image-four');
            jQuery('body').find('.col-md-8.rghtexp').addClass('image-five');
            jQuery('body').find('.col-md-8.rghtexp').removeClass('image-six');
        }



        if (totalImages == 0) {
            $('.images-added .prdimg.pi1 .imgtg').show();
        } else {
            $('.images-added .prdimg.pi1 .imgtg').hide();
        }
      
        $('#img_' + imageID).remove();

        // feature image delete in popup set thumbnail 
        var featureimageLength = $('.selectFeatureImages').length;
        for (let i = 0; i < featureimageLength; i++) {
            let found = $('.selectFeatureImages')[i];
            let imagssr = $(found).find('.select_feature_image').attr('data-featrue-image');
            if(imagssr == input_field_value){
                found.remove();
            }
        }
        
        $.ajax({
            url: "/deleteProducttempImage",
            method: 'POST',
            data: { imageName: input_field_value },
            success: function (res) {
                // alert(12);
                toastr.options = {
                    closeButton: true, // Add close button
                    disableTimeOut: true,
                    tapToDismiss: true,
                    positionClass: 'toast-top-right',
                    toastClass: 'toast-icon custom-toast-success'
                    };
                toastr.success('image deleted successfully');
               
                // var totalImages = jQuery('#all-uplpad-image .image-panel').length;
                // console.log(totalImages);
                // debugger;
                $('.select_variation_image_create').each(function(){
                    let imagepath = $(this).val();
                    if(imagepath == input_field_value){
                        
                        $(this).parent().parent().remove();

                    }
                });

                var featureImage = $('#imageFeatureSrc').val();
                if(featureImage == input_field_value){
                    $('#imageFeatureSrc').parent().parent().remove();
                }
                $('.product_variation_image').each(function(){
                    let imagepathValue = $(this).val();
                    if(imagepathValue == input_field_value){
                        
                        $(this).val('');

                    }
                });

                var totalImages = jQuery('#all-uplpad-image .image-panel').length;
                // console.log(totalImages);
                // debugger;
                if (totalImages <= 0) {
                    
                    // Disable the button
                    jQuery('.btn-primary-back.selectFeatureImageAdd').attr('disabled', 'disabled');
                    jQuery('.variationImageIcon').css({
                        'pointer-events' : 'none',
                        'opacity' : '0.5'
                    });
                    
                } else {
                    // Enable the button
                    jQuery('.btn-primary-back.selectFeatureImageAdd').removeAttr('disabled');
                    jQuery('.variationImageIcon').css({
                        'pointer-events' : 'auto',
                        'opacity' : ''
                    });
                    
                }
                console.log(res);
            }
        });
    }
}
function deleteEditImage(imageID) {
    var input_field_value = jQuery('#imagesrc' + imageID).val();
    var input_field_product_id = jQuery('#imagesrc' + imageID).attr('data-product-id');
   
    let image_panel = $('.image-panel').length;
           if(image_panel < 2){
                toastr.options = {
                        closeButton: true, // Add close button
                        disableTimeOut: true,
                        tapToDismiss: true,
                        positionClass: 'toast-top-right',
                        toastClass: 'toast-icon custom-toast-success'
                        };
                toastr.error("Feature image is not deleted");
                return;
           }
           
    if (input_field_value) {
        
        Imagesbox = jQuery('#all-uplpad-image .image-panel').length;
        totalImages = Imagesbox - 1;
        

        $('#img_' + imageID).remove();
        $.ajax({
            url: "/deleteEditProducttempImage",
            method: 'POST',
            data: { imageId: input_field_value , productId: input_field_product_id},
            success: function (res) {

                toastr.options = {
                    closeButton: true, // Add close button
                    disableTimeOut: true,
                    tapToDismiss: true,
                    positionClass: 'toast-top-right',
                    toastClass: 'toast-icon custom-toast-success'
                    };
                toastr.success(res);
                
                $('.select_variation_image_create').each(function(){
                    let imagepath = $(this).val();
                    if(imagepath == input_field_value){
                        
                        $(this).parent().parent().remove();

                    }
                });

                $('.product_variation_image').each(function(){
                    let imagepathValue = $(this).val();
                    if(imagepathValue == input_field_value){
                        
                        $(this).val('');

                    }
                });

                
                console.log(res);
            }
        });
    }
}

function deleteEditFeatureImage(imageID) {
    
    var input_field_value = jQuery('#imagefeaturesrc').attr('data-value');
    var input_field_product_id = jQuery('#imagefeaturesrc').attr('data-product-id');
   
    let image_panel = $('.image-panel').length;
           if(image_panel < 2){
                toastr.options = {
                        closeButton: true, // Add close button
                        disableTimeOut: true,
                        tapToDismiss: true,
                        positionClass: 'toast-top-right',
                        toastClass: 'toast-icon custom-toast-success'
                        };
                toastr.error("Feature image is not deleted");
                return;
           }
           
    if (input_field_value) {
        
        Imagesbox = jQuery('#all-uplpad-image .image-panel').length;
        totalImages = Imagesbox - 1;
        

        // $('#img_' + imageID).remove();
        $.ajax({
            url: "/deleteEditProductThumbnailImage",
            method: 'POST',
            data: { imageId: input_field_value , productId: input_field_product_id},
            success: function (res) {
                

                $('.productThumbnailEdit').attr('src' , res.imageData);
                $('.featureImageCropper').attr('src' , res.imageData);
                $('.viewImagePrettyPhoto').attr('href' , res.imageData);
                toastr.options = {
                    closeButton: true, // Add close button
                    disableTimeOut: true,
                    tapToDismiss: true,
                    positionClass: 'toast-top-right',
                    toastClass: 'toast-icon custom-toast-success'
                    };
                toastr.success(res.message);
                
                $('.select_variation_image_create').each(function(){
                    let imagepath = $(this).val();
                    if(imagepath == input_field_value){
                        
                        $(this).parent().parent().remove();

                    }
                });

                $('.product_variation_image').each(function(){
                    let imagepathValue = $(this).val();
                    if(imagepathValue == input_field_value){
                        
                        $(this).val('');

                    }
                });

                
                console.log(res);
            }
        });
    }
}



function randomNumberFromRange(min, max) {
    return Math.floor(Math.random() * (max - min + 1) + min);
}


$('#productImage').change(function () {
    var checkImages = $(this)[0].files.length + ' images chosen';
    if (checkImages > 1) {
        // alert("i am less thanone");
    } else {
        jQuery("#all-uplpad-image").parent().addClass("images-added");
        // alert("i am greater than one");
    }

});
$(".dele-icn a.dete-parent").click(function () {
    // $(this).parent().parent().remove();
});
jQuery('body').on('keyup keypress', '#addProductForm', function (e) {
    var keyCode = e.keyCode || e.which;
    if (keyCode === 13) {
        e.preventDefault();
        return false;
    }
});
jQuery('body').on('click', '.variationOptionID a', function (e) {
    var optionSelect = $(this).parent().parent().parent().has(".attributeOptions");
    if (optionSelect.length) {
        var optionVal = $(this).attr("optionVal");
        $(this).parent().parent().parent().find('option[value="' + optionVal + '"]').show();
    }
    $(this).parent().remove();
    var attribute1 = $('#varidationOptionValues').find('input[type=text]').length;
    var attribute2 = $('#varidationOptionValues2').find('input[type=text]').length;
    var attribute3 = $('#varidationOptionValuesthree').find('input[type=text]').length;
    if (attribute1 == 0 && attribute2 == 0 && attribute3 == 0 && mode == "edit") {
        $('#btnvariationcheck').attr('disabled', 'disabled');
    } else {
        $('#btnvariationcheck').removeAttr('disabled');
    }

});
jQuery('body').on('change', '.attributeOptions', function (e) {
    var mainDivVariation = $(this).parentsUntil('.VariationMainDiv').parent();
    var optionHTML = '';
    //// $('.attributeOptions').on('change', function () {
    var options = mainDivVariation.find('.attributeOptions option:selected').text();
    var optionsVal = mainDivVariation.find('.attributeOptions option:selected').val();
    switch (optionsVal) {

        case '__custom':
            mainDivVariation.find('.customFeildVariation').empty();
            var inputField = customTextField('Type Variations here');
            mainDivVariation.find('.customFeildVariation').append(inputField);
            break;
        case '__add_all':
            $(this).find('option').each(function () {
                var opValue = $(this).val();
                var opText = $(this).text();
                switch (opValue) {
                    case '__custom':
                        var inputField = customTextField('Type Variations here');
                        $(this).parent().parent().last().append(inputField);
                        break;
                    case '':
                        break;
                    case '__add_all':
                        break;
                    default:
                        if (opValue) {
                            optionHTML += '<div class="variationOptionID"><input type="text" readonly class="form-control" readonly value="' + opText + '"><a optionVal = "' + opValue + '" class="cross-icon"><i class="fa fa-times" aria-hidden="true"></i></a></div>';
                        }

                        break;
                }
            });
            $(this).hide();
            var inputField = customTextField('Type Variations here', '');
            $(this).parent().append(inputField);
            // $(this).find('option:selected').hide();
            //  $('.attributeOptions option').prop('selected', function () {
            //      return this.defaultSelected;
            //   });
            //   optionHTML = '<div class="variationOptionID"><input type="text" class="form-control" value="' + options + '"><a optionVal = "' + optionsVal + '" class="cross-icon"><i class="fa fa-times" aria-hidden="true"></i></a></div>';
            break;
        default:
            $(this).find('option:selected').hide();
            mainDivVariation.find('.attributeOptions option').prop('selected', function () {
                return this.defaultSelected;
            });
            if (optionsVal) {
                optionHTML = '<div class="variationOptionID"><input type="text" readonly class="form-control" value="' + options + '"><a optionVal = "' + optionsVal + '" class="cross-icon"><i class="fa fa-times" aria-hidden="true"></i></a></div>';
            }

            break;
    }


    mainDivVariation.find('.varidationOptionValues').append(optionHTML);
    var attribute1 = $('#varidationOptionValues').find('input[type=text]').length;
    var attribute2 = $('#varidationOptionValues2').find('input[type=text]').length;
    var attribute3 = $('#varidationOptionValuesthree').find('input[type=text]').length;
    if (attribute1 == 0 && attribute2 == 0 && attribute3 == 0) {
        $('#btnvariationcheck').attr('disabled', 'disabled');
    } else {
        $('#btnvariationcheck').removeAttr('disabled');
    }
});
function isEmptyObject(obj) {

    // null and undefined are "empty"
    if (obj == null)
        return true;
    // Assume if it has a length property with a non-zero value
    // that that property is correct.
    if (obj.length > 0)
        return false;
    if (obj.length === 0)
        return true;
    // If it isn't an object at this point
    // it is empty, but it can't be anything *but* empty
    // Is it empty?  Depends on your application.
    if (typeof obj !== "object")
        return true;
    // Otherwise, does it have any properties of its own?
    // Note that this doesn't handle
    // toString and valueOf enumeration bugs in IE < 9
    for (var key in obj) {
        if (hasOwnProperty.call(obj, key))
            return false;
    }

    return true;
}
function capitalize(string) {
    return string.charAt(0).toUpperCase() + string.slice(1).toLowerCase();
}

function customTextField(optionHTML, inputID = '') {
    if (inputID) {
        inputID = inputID;
    } else {
        inputID = 'variation_optin_event';
    }
    var textBoxFeildHTML = '';
    textBoxFeildHTML += '<div class="input-group-body form-group">';
    textBoxFeildHTML += '<div class="input-group-body">';
    textBoxFeildHTML += '<input type="text" class="form-control add_new_option_input" name="add_new_option_input" placeholder="' + capitalize(optionHTML) + '">';
    textBoxFeildHTML += '</div>';
    textBoxFeildHTML += '<div class="input-group-btnn">';
    textBoxFeildHTML += '<button name="add-custom" type="button" class="btn btn-primary btn-append ' + inputID + '" id="' + inputID + '">Add</button>';
    textBoxFeildHTML += '</div>';
    textBoxFeildHTML += '</div>';
    return textBoxFeildHTML;
}

// jQuery('body').on('click', '.customAttributeField', function (e) {
//     var mainDivVariation = $(this).parentsUntil('.VariationMainDiv').parent();
//     ///console.log(mainDivVariation);
//     var nameVariation = mainDivVariation.find('.add_new_option_input').val();
//     /// console.log(nameVariation);
//     $(this).parent().parent().hide();
//     ////alert(nameVariation);
//     // $('#select-variation').hide();
//     mainDivVariation.find('#variationOption1').show();
//     mainDivVariation.find(".hide-on-select").hide();
//     mainDivVariation.find('.open-secondary').show();
//     mainDivVariation.find('.show-on-select-box').show();
//     mainDivVariation.find('.results-text .color').text(nameVariation);
//     mainDivVariation.find('.customvariation').empty();
//     mainDivVariation.find('.variation_attribute').val(nameVariation);
//     mainDivVariation.find('.variationUnit').empty();
//     mainDivVariation.find('.variationOptions').empty();
//     mainDivVariation.find('.customFeildVariation').empty();
//     var inputField = customTextField('Type Variations here');
//     mainDivVariation.find('.customFeildVariation').append(inputField);
//     $('.VariationMainDiv').show();
// });
jQuery('body').on('click', '.variation_optin_event', function (e) {
    var optionHTML = '';
    var mainDivVariation = $(this).parentsUntil('.VariationMainDiv').parent();
    var nameVariation = mainDivVariation.find('.selct-box-tags .add_new_option_input').val();
   
    if (isEmptyOrSpaces(nameVariation) == false && nameVariation !== undefined) {
        var nameVariation = nameVariation
        .trim()  
        .replace(/&/g, '&amp;')    // Encode & as &amp;
        .replace(/"/g, '&quot;')  // Encode " as &quot;
        .replace(/'/g, '&#39;')   // Encode ' as &#39;
        .replace(/</g, '&lt;')    // Encode < as &lt;
        .replace(/>/g, '&gt;');
        optionHTML = '<div class="variationOptionID"><input type="text" class="form-control" readonly value="' + nameVariation + '"><a optionVal = "' + nameVariation + '" class="cross-icon"><i class="fa fa-times" aria-hidden="true"></i></a></div>';
        mainDivVariation.find('.varidationOptionValues').append(optionHTML);
        mainDivVariation.find('.add_new_option_input').val('');
    }
    var attribute1 = $('#varidationOptionValues').find('input[type=text]').length;
    var attribute2 = $('#varidationOptionValues2').find('input[type=text]').length;
    var attribute3 = $('#varidationOptionValuesthree').find('input[type=text]').length;
    if (attribute1 > 0 || attribute2 > 0 || attribute3 > 0) {
        $('#btnvariationcheck').removeAttr('disabled');
    }
});
jQuery('body').on('change', '.attributeUnits', function (e) {
    var mainDivVariation = $(this).parentsUntil('.VariationMainDiv').parent();
    var unitVal = $(this).find('option:selected').val();
    var optionHTML = customTextField(unitVal);
    mainDivVariation.find('.variationUnit').empty();
    mainDivVariation.find('.variationOptions').empty();
    mainDivVariation.find('.customFeildVariation').empty();
    mainDivVariation.find('.variation_attribute_unit').val(unitVal);
    mainDivVariation.find('.variationOptions').append(optionHTML);
    /// alert(unitVal);

});
jQuery('#productTitle', '#productShortDescription', '#productQuantity , #productWeight_lbs , #productWeight_oz ').on('keyup blur', function (e) {
    var $th = jQuery(this);
    var input_field_value = jQuery(this).val();
    if (input_field_value != '') {
        $th.css('border-color', '#ced4da');
    } else {
        $th.css('border-color', 'red');
    }

});
// $('input[name=productTags]').tagsinput({
//     maxChars: 20,
//     maxTags: 10,

// });
$('input[name=productTags]').on('beforeItemAdd', function (event) {
    var tag = event;
    var word = tag.item;
    var regex = new RegExp("^[a-zA-Z0-9 _]+$");
    if (!regex.test(word)) {
        event.cancel = true;
    }
});
$("input[name='productType']").each(function () {

    if ($(this).is(':checked')) {
        if ($(this).val() == 'digital') {

            $('.shippingTab').hide();
            $('.btn_parentVariation').hide();
            $('#productVariationTable').hide();
            $('.digitalProductTab').show();
        } else {

            $('.shippingTab').show();
            $('.digitalProductTab').hide();
            if (jQuery('#productMainCategory').val() != 0) {

                $('.btn_parentVariation').show();
                $('#productVariationTable').show();
            }
        }
    }
});
//jQuery('#productTitle').on('keyup', function (e) { // fires on every keyup & blur
//    var $th = jQuery(this);
//    var productTitle = jQuery(this).val();
//    if (productTitle != '') {
//        var regex = new RegExp("^[a-zA-Z0-9_@$*\s]+$");
//        var str = String.fromCharCode(!e.charCode ? e.which : e.charCode);
//        if (regex.test(str)) {
//            jQuery('.productTitle-error').css('display', 'none');
//            jQuery(this).css('border-color', 'rgba(0, 128, 0, 0.39)');
//            return true;
//        } else {
//            $th.val($th.val().replace(/[^a-zA-Z0-9_@$*\s]/g, function (str) {
//                jQuery('.productTitle-error').css('display', 'block');
//                jQuery(this).css('border-color', 'red');
//                jQuery(this).focus();
//                return '';
//            }));
//        }
//        e.preventDefault();
//        return false;
//    } else {
//        jQuery(this).css('border-color', 'red');
//        jQuery(this).focus();
//        return;
//    }
//});
jQuery('#productTitle').on('blur', function (e) { // fires on every keyup & blur
    var productTitle = jQuery(this).val();
    if (productTitle == '') {
        jQuery(this).css('border-color', 'red');
        // jQuery(this).focus();
        // return;
    } else {
        jQuery('.productTitle-error').css('display', 'none');
        jQuery(this).css('border-color', '#ced4da');
        return true;
    }
});
//jQuery('#productQuantity').on('keyup', function (e) { // fires on every keyup & blur
//    var $th = jQuery(this);
//    var productQuantity = jQuery(this).val();
//    if (productQuantity != '') {
//        var regex = new RegExp("^[0-9]+$");
//        var str = String.fromCharCode(!e.charCode ? e.which : e.charCode);
//        if (regex.test(str)) {
//            jQuery('.productQuantity-error').css('display', 'none');
//            jQuery(this).css('border-color', '#ced4da');
//            return true;
//        } else {
//            $th.val($th.val().replace(/[^0-9]/g, function (str) {
//                jQuery('.productQuantity-error').css('display', 'block');
//                jQuery(this).css('border-color', 'red');
//                jQuery(this).focus();
//                return '';
//            }));
//        }
//        e.preventDefault();
//        return false;
//    } else {
//        jQuery(this).css('border-color', 'red');
//        jQuery(this).focus();
//        return;
//    }
//});
jQuery('#productQuantity , #productPrice').on('keyup blur', function (e) { // fires on every keyup & blur
    var productQuantity = jQuery(this).val();
    if (productQuantity == '' || productQuantity <= 0) {
        jQuery(this).css('border-color', 'red');
        // jQuery(this).focus();
        return;
    } else {
        jQuery('.productQuantity-error').css('display', 'none');
        jQuery(this).css('border-color', '#ced4da');
        return true;
    }
});



//function genrateKeyEvent(type, ID) {
//    return Numvalidate(event, type, ID);
//}
//$(window).keydown(function(event){
//    alert(event.keyCode);
//});
//jQuery('.keypressNumValidate').on("keypress", function (event) {
//    
//    console.log(event)
//    Numvalidate(event, 'Price', $(this).attr('id'));
//});



//function alphaNumvalidate(evt, _this, charLength) {
//    ////  console.log(evt);
//    var theEvent = evt || window.event;
//    var key = theEvent.keyCode || theEvent.which;
//    key = String.fromCharCode(key);
//    var regex = /^[a-z\d\-+,.'&*%$#@()=_\s\b]+$/i;
//    if (!regex.test(key)) {
//        theEvent.returnValue = false;
//        if (theEvent.preventDefault)
//            theEvent.preventDefault();
//    } else {
//        var checkLength = jQuery('#' + _this).val();
//        if (checkLength.length > charLength) {
//            theEvent.returnValue = false;
//            if (theEvent.preventDefault)
//                theEvent.preventDefault();
//        }
//        if (checkLength) {
//            jQuery('#' + _this).css('border-color', '#ced4da');
//            /// jQuery('#' + _this).css('border-color', '')
//        }
//    }
//}


//function shippingValidat(event, _this) {
//
//    var key = window.event ? event.keyCode : event.which;
//
//    if (event.keyCode == 8 || event.keyCode == 46
//            || event.keyCode == 37 || event.keyCode == 39) {
//        return true;
//    } else if (key < 48 || key > 57) {
//        return false;
//    } else {
//        var checkLength = jQuery('body').find('#' + _this).val();
//        if (checkLength.length >= 2) {
////            && checkLength>30
//             //jQuery('#' + _this).css('border-color', 'red');
//            //jQuery('#' + _this).css('border-color', 'red');
//            if ($("#productSize_length").val() > 100) {
//                jQuery('#productSize_length').css('border-color', 'red');
//            }  if ($("#productSize_width").val() > 80) {
//                jQuery('#productSize_width').css('border-color', 'red');
//            }  if ($("#productSize_height").val() > 100) {
//                jQuery('#productSize_height').css('border-color', 'red');
//            }  if ($("#productWeight").val() > 30) {
//                jQuery('#productWeight').css('border-color', 'red');
//            } 
//                
//
//           // return false;
//        } else {
//            jQuery('#' + _this).css('border-color', '#ced4da');
//        }
//        return true;
//    }
//}

function Numvalidate(evt, _this) {
    var theEvent = evt || window.event;
    var key = theEvent.keyCode || theEvent.which;
    key = String.fromCharCode(key);
    var regex = /^[0-9._\b]+$/;
    //var regex = /[0-9]|\./;
    if (!regex.test(key)) {
        theEvent.returnValue = false;
        if (theEvent.preventDefault)
            theEvent.preventDefault();
    } else {
        var checkLength = jQuery('body').find('#' + _this).val();
        if (checkLength.length > 4) {
            return false;
        }
        if (checkLength) {
            jQuery('#' + _this).css('border-color', '#ced4da');
            /// jQuery('#' + _this).css('border-color', '')
        }
    }
}

$('#price-popup').on('hidden.bs.modal', function () {
    var attribute1 = $('#varidationOptionValues').find('input[type=text]').length;
    var attribute2 = $('#varidationOptionValues2').find('input[type=text]').length;
    var attribute3 = $('#varidationOptionValuesthree').find('input[type=text]').length;
    if (attribute1 == 0 && attribute2 == 0 && attribute3 == 0) {
        $(".delete-variation").trigger("click");
    }
})



// function showUploadedItem(source) {
//     // console.log(source);
//     var imagDiv = document.getElementById('all-uplpad-image');
//     // var thumbnailImagDiv = document.getElementById('thumbnail-image-box');

//     $('body').find('.photoSection .uploadImages').delay(1000).hide();
//     var totalImages = jQuery('#all-uplpad-image .image-panel').length;
//     imgCounter = totalImages;

//     var html = '';
//     var thumbnailHtml = '';
//     var i_inner = 0, len = source.fileName.length;

//     for (; i_inner < len; i_inner++) {

//         var validateClass = '';
//         if (source.validate[i_inner] == false) {
//             validateClass = 'required-field';
//             return false;
//         }

//         $('#thumbnail-image-box').empty();
//         $('#uploadImages').hide();
//         $('.imageBox').hide();

// //        if (imgCounter > 0) {
// //            if (imgCounter == 1) {
// //                ////  $('#uploadImages').hide();
// //                break;
// //            } else {
// //                $('.images-added .prdimg.pi1 .imgtg').hide();
// //                $('.imageBox').eq(imgCounter - 1).hide();
// //            }
// //        }

//         randomCode = randomNumberFromRange(101, 99999999999);
//         html += '<div id="img_' + randomCode + '" class="image-panel ' + validateClass + '">';
//         html += '<div class="image-inner-mbt"><div class="img-only">';
//         html += '<a class="viewImages" rel="prettyPhoto" href="/uploads/temp/' + source.fileName[i_inner] + '">';
//         html += '<img  class="previewClass" src= "/uploads/temp/' + source.fileName[i_inner] + '" />';
//         html += '</a>';
//         html += '</div><div class="loading-image"><span></span></div> ';
//         html += '<input id="imagesrc' + randomCode + '" type="hidden" value= "' + source.fileName[i_inner] + '" name="productImage[]"/> ';
//         html += '<div class="dele-icn">';
//         html += '<a class="editEvent" href="javascript:;"  data-toggle="modal" data-target="#cropper_' + randomCode + '" >';
//         html += '<i class="fa fa-crop" aria-hidden="true"></i></a>&nbsp;&nbsp;';
//         html += '<a href="javascript:;" class="dete-parent" onclick="deleteImage(' + randomCode + ')">';
//         html += '<i class="fa fa-trash-o" aria-hidden="true"></i></a>';
//         html += '</div><div class="modal" id="cropper_' + randomCode + '" tabindex="-1" role="dialog" aria-hidden="true" >';
//         html += '<div class="modal-dialog animated bounceInDown" style="max-width:800px"> <div class="modal-content"> ';
//         html += '<div class="modal-body"> <div class="row imagecrop-area"> <div class="col-xs-12 col-sm-12"> <div class="img-container"> ';
//         html += ' <img class="Cropperimage" src="/uploads/temp/' + source.fileName[i_inner] + '" alt="Picture"> </div>';
//         html += '<div class="clearfix"></div><br></div><div class="clearfix spacer20"></div><div class="col-md-12 docs-toggles"> ';
//         /// html += '<div class="btn-group btn-group-justified" data-toggle="buttons">';
//         ///html += '<label class="btn btn-primary active"> <input type="radio" class="sr-only" id="aspectRatio0" name="aspectRatio" value="1.7777777777777777"> <span class="docs-tooltip" data-toggle="tooltip" data-container="body" title="aspectRatio: 16 / 9"> 16:9 </span> </label> <label class="btn btn-primary"> <input type="radio" class="sr-only" id="aspectRatio1" name="aspectRatio" value="1.3333333333333333"> <span class="docs-tooltip" data-toggle="tooltip" data-container="body" title="aspectRatio: 4 / 3"> 4:3 </span> </label> <label class="btn btn-primary"> <input type="radio" class="sr-only" id="aspectRatio2" name="aspectRatio" value="1"> <span class="docs-tooltip" data-toggle="tooltip" data-container="body" title="aspectRatio: 1 / 1"> 1:1 </span> </label> <label class="btn btn-primary"> <input type="radio" class="sr-only" id="aspectRatio3" name="aspectRatio" value="0.6666666666666666"> <span class="docs-tooltip" data-toggle="tooltip" data-container="body" title="aspectRatio: 2 / 3"> 2:3 </span> </label> <label class="btn btn-primary"> <input type="radio" class="sr-only" id="aspectRatio4" name="aspectRatio" value="NaN"> <span class="docs-tooltip" data-toggle="tooltip" data-container="body" title="aspectRatio: NaN" checked="checked" > Free </span> </label>';
//         ///html += '</div>';
//         html += '</div></div></div>';
//         html += '<div class="modal-footer"> <button data-dismiss="modal" class="btn btn-primary btn-simple" type="button">Close</button> <button class="btn-primary btn  text-white btn-success getCropImage" type="button" data-dismiss="modal">Save</button> </div></div></div></div></div></div> </div>';



//         randomCode = randomNumberFromRange(101, 99999999999);
//         thumbnailHtml += '<div id="img_' + randomCode + '" class="image-panel ' + validateClass + '">';
//         thumbnailHtml += '<div class="image-inner-mbt"><div class="img-only">';
//         thumbnailHtml += '<a class="viewImages" rel="prettyPhoto" href="/uploads/temp/' + source.fileName[i_inner] + '">';
//         thumbnailHtml += '<img  class="previewClass" src= "/uploads/temp/' + source.fileName[i_inner] + '" />';
//         thumbnailHtml += '</a>';
//         thumbnailHtml += '</div><div class="loading-image"><span></span></div> ';
//         thumbnailHtml += '<input id="imagesrc' + randomCode + '" type="hidden" value= "' + source.fileName[i_inner] + '" name="productImageThumbnail"/> ';
//         thumbnailHtml += '<span class="imgtg" style="    display: inline-block;">Thumbnail</span><div class="dele-icn">';
//         thumbnailHtml += '<a class="editEvent" href="javascript:;"  data-toggle="modal" data-target="#cropper_' + randomCode + '" >';
//         thumbnailHtml += '<i class="fa fa-crop" aria-hidden="true"></i></a>';
//         ///thumbnailHtml += '<a href="javascript:;" class="dete-parent" onclick="deleteImage(' + randomCode + ')">';
//         /// thumbnailHtml += '<i class="fa fa-trash-o" aria-hidden="true"></i></a>';
//         thumbnailHtml += '</div><div class="modal" id="cropper_' + randomCode + '" tabindex="-1" role="dialog" aria-hidden="true" >';
//         thumbnailHtml += '<div class="modal-dialog animated bounceInDown" style="max-width:800px"> <div class="modal-content"> ';
//         thumbnailHtml += '<div class="modal-body"> <div class="row imagecrop-area"> <div class="col-xs-12 col-sm-12"> <div class="img-container"> ';
//         thumbnailHtml += ' <img class="Cropperimage" src="/uploads/temp/' + source.fileName[i_inner] + '" alt="Picture"> </div>';
//         thumbnailHtml += '<div class="clearfix"></div><br></div><div class="clearfix spacer20"></div><div class="col-md-12 docs-toggles"> ';
//         /// html += '<div class="btn-group btn-group-justified" data-toggle="buttons">';
//         ///html += '<label class="btn btn-primary active"> <input type="radio" class="sr-only" id="aspectRatio0" name="aspectRatio" value="1.7777777777777777"> <span class="docs-tooltip" data-toggle="tooltip" data-container="body" title="aspectRatio: 16 / 9"> 16:9 </span> </label> <label class="btn btn-primary"> <input type="radio" class="sr-only" id="aspectRatio1" name="aspectRatio" value="1.3333333333333333"> <span class="docs-tooltip" data-toggle="tooltip" data-container="body" title="aspectRatio: 4 / 3"> 4:3 </span> </label> <label class="btn btn-primary"> <input type="radio" class="sr-only" id="aspectRatio2" name="aspectRatio" value="1"> <span class="docs-tooltip" data-toggle="tooltip" data-container="body" title="aspectRatio: 1 / 1"> 1:1 </span> </label> <label class="btn btn-primary"> <input type="radio" class="sr-only" id="aspectRatio3" name="aspectRatio" value="0.6666666666666666"> <span class="docs-tooltip" data-toggle="tooltip" data-container="body" title="aspectRatio: 2 / 3"> 2:3 </span> </label> <label class="btn btn-primary"> <input type="radio" class="sr-only" id="aspectRatio4" name="aspectRatio" value="NaN"> <span class="docs-tooltip" data-toggle="tooltip" data-container="body" title="aspectRatio: NaN" checked="checked" > Free </span> </label>';
//         ///html += '</div>';
//         thumbnailHtml += '</div></div></div>';
//         thumbnailHtml += '<div class="modal-footer"> <button data-dismiss="modal" class="btn btn-primary btn-simple" type="button">Close</button> <button class="btn-primary btn btn-success getCropImage" type="button" data-dismiss="modal">Save</button> </div></div></div></div></div></div> </div>';
//     }

// ///document.getElementById('thumbnailImage').insertAdjacentHTML('beforeend', html);
//     //imagDiv.append(html);
//     //thumbnailImagDiv.append(thumbnailHtml);
//     imagDiv.insertAdjacentHTML('beforeend', html);

//     $("a[rel^='prettyPhoto']").prettyPhoto({
//         social_tools: ''
//     });

// //    var Imagesbox = jQuery('#all-uplpad-image .image-panel').length;
// //    if (Imagesbox == 4) {
// //        jQuery('body').find('.col-md-8.rghtexp').addClass('image-four');
// //        jQuery('body').find('.col-md-8.rghtexp').removeClass('image-five');
// //        jQuery('body').find('.col-md-8.rghtexp').removeClass('image-six');
// //    }
// //
// //    if (Imagesbox == 5) {
// //        jQuery('body').find('.col-md-8.rghtexp').removeClass('image-four');
// //        jQuery('body').find('.col-md-8.rghtexp').addClass('image-five');
// //        jQuery('body').find('.col-md-8.rghtexp').removeClass('image-six');
// //    }
// //    if (Imagesbox == 6) {
// //        jQuery('body').find('.col-md-8.rghtexp').removeClass('image-four');
// //        jQuery('body').find('.col-md-8.rghtexp').removeClass('image-five');
// //        jQuery('body').find('.col-md-8.rghtexp').addClass('image-six');
// //    }

//     //  $('body').find('.photoSection .uploadImages').delay(2000).hide();


//     //// $('.uploadImages').delay(2000).hide();
// }


jQuery('body').on('click', '#addCauseBtn', function (e) {
    var causeTitle = jQuery('#causeTitle').val();
    if (causeTitle == '') {
        jQuery('#causeTitle').css('border-color', 'red');
        //  jQuery(this).focus();
        //// return;
    } else {
        jQuery('#causeTitle').css('border-color', 'rgba(0, 128, 0, 0.39)');
    }
    var causeDesription = jQuery('#causeDesription').val();
    if (causeDesription == '') {
        jQuery('#causeDesription').css('border-color', 'red');
        // jQuery(this).focus();
        return;
    } else {
        jQuery('#causeDesription').css('border-color', 'rgba(0, 128, 0, 0.39)');
    }

    var html = '';
    randomCode = randomNumberFromRange(12, 10000);
    html += '<div class="list-repeater-mbt">';
    html += '<h4>';
    html += causeTitle;
    html += '<input id="causeListTitle' + randomCode + '" type="hidden" value= "' + causeTitle + '" name="causeListTitle[]"/> ';
    html += '<span class="pull-right text-center">';
    html += ' <a href="javascript:;" class="align-with-btn text-gray-lighter cross-icon-cont remove-containertabs">';
    html += '<span class="ss-etsy ss-delete icon-smaller icon-t-2">';
    html += '<i class="fa fa-times" aria-hidden="true"></i></span>';
    html += '</a>';
    html += '</span>';
    html += '</h4>';
    /// html += '<p>';
    /// html += causeDesription;
    /// html += '<input id="causeListDescription' + randomCode + '" type="hidden" value= "' + causeDesription + '" name="causeListDescription[]"/> ';
    /// html += '</p>';
    html += ' </div>';
    jQuery('.cause-list-results').append(html);
    jQuery('#causeTitle').val('');
    /// jQuery('#causeDesription').val('');
});
/*
 jQuery(document).on('click', '#addFaqBtn', function (e) {
 var faqQuestion = jQuery('#faqQuestion').val();
 alert(faqQuestion);
 if (faqQuestion == '') {
 alert('hello');
 jQuery('#faqQuestion').css('border-color', 'red');
 jQuery('#faqQuestion').focus();
 //// return;
 } else {
 jQuery('#faqQuestion').css('border-color', 'rgba(0, 128, 0, 0.39)');
 ///return true;
 }
 var faqAnswer = jQuery('#faqAnswer').val();
 if (faqAnswer == '') {
 jQuery('#faqAnswer').css('border-color', 'red');
 jQuery('#faqAnswer').focus();
 return;
 } else {
 
 jQuery('#faqAnswer').css('border-color', 'rgba(0, 128, 0, 0.39)');
 /// return true;
 }
 if (faqQuestion == '') {
 
 return false;
 }
 
 var html = '';
 var childHtml = '';
 randomCode = randomNumberFromRange(12, 10000);
 
 html += '<div class="list-repeater-mbt">';
 childHtml += '<h4>Q: ';
 childHtml += faqQuestion;
 childHtml += '<input id="causeListTitle' + randomCode + '" type="hidden" value= "' + faqQuestion + '" name="faqQuestion[]"/> ';
 childHtml += '<span class="pull-right text-center">';
 childHtml += ' <a href="javascript:;" class="align-with-btn text-gray-lighter cross-icon-cont remove-containertabs">';
 childHtml += '<span class="ss-etsy ss-delete icon-smaller icon-t-2">';
 childHtml += '<i class="fa fa-times" aria-hidden="true"></i></span>';
 childHtml += '</a>';
 childHtml += '</span>';
 childHtml += '</h4>';
 
 childHtml += '<p>A: ';
 childHtml += faqAnswer;
 childHtml += '<input id="faqAnswer' + randomCode + '" type="hidden" value= "' + faqAnswer + '" name="faqAnswer[]"/> ';
 childHtml += '</p>';
 
 html += childHtml + ' </div>';
 //jQuery('.ask-question-section').append('<div class="ask-repeater">' + childHtml + '</div>');
 //jQuery('.faqs-list-results').append(html);
 
 jQuery('#faqQuestion').val('');
 jQuery('#faqAnswer').val('');
 });
 */
jQuery('body').on('click', '.getCropImage', function (e) {
   
    var mainImageDiv = $(this).parentsUntil('.image-panel ');
    var cropcanvas = mainImageDiv.find('.Cropperimage').cropper('getCroppedCanvas', {
        //        width: 217,
        //        height: 146,
    });
    $('body').find('.photoSection .uploadImages').delay(1000).show();
    var croppng = cropcanvas.toDataURL("image/png");
    mainImageDiv.find('.previewClass').attr('src', croppng);
    mainImageDiv.find('.viewImages').attr('data-featherlight', croppng);
    var formData = new FormData();
    ////  console.log(croppng);
    formData.append('croppedImage', croppng);
    $.ajax({
        url: "/uploadImage",
        method: 'POST',
        processData: false,
        contentType: false,
        data: formData,
        success: function (res) {

            mainImageDiv.find('input[type=hidden]').val(res);
            mainImageDiv.find('.viewImages').attr('data-featherlight', '/uploads/temp/' + res);
            console.log('Upload success');
            $('body').find('.photoSection .uploadImages').delay(1000).hide();
        },
        error: function () {
            console.log('Upload error');
            $('body').find('.photoSection .uploadImages').delay(1000).hide();
        }

    });
});
jQuery('body').on('click', '.editEvent', function (e) {
   
    var mainImageDiv = $(this).parentsUntil('.image-panel');
    var options = {
        aspectRatio: 278 / 278,
        crop: function (e) {

        }
    };
    // Cropper
    mainImageDiv.find('.Cropperimage').on({
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
    
    // Options
    mainImageDiv.find('.docs-toggles').on('change', 'input', function () {
        var $this = $(this);
        var name = $this.attr('name');
        var type = $this.prop('type');
        var cropBoxData;
        var canvasData;
        if (!mainImageDiv.find('.Cropperimage').data('cropper')) {
            return;
        }


        if (type === 'checkbox') {
            options[name] = $this.prop('checked');
            cropBoxData = mainImageDiv.find('.Cropperimage').cropper('getCropBoxData');
            canvasData = mainImageDiv.find('.Cropperimage').cropper('getCanvasData');
            options.built = function () {
                mainImageDiv.find('.Cropperimage').cropper('setCropBoxData', cropBoxData);
                mainImageDiv.find('.Cropperimage').cropper('setCanvasData', canvasData);
            };
        } else if (type === 'radio') {
            options[name] = $this.val();
        }
        /// console.log( $().cropper('getCroppedCanvas').toDataURL('image/jpeg'));
        mainImageDiv.find('.Cropperimage').cropper('destroy').cropper(options);
    });
    setTimeout(function () {
        mainImageDiv.find(".docs-toggles .btn-primary").trigger("click");
    }, 1);
});
//console.clear();

// scripts fro add product page




jQuery(document).on('change', '#shipping-popup #processing-time-perioud-popup', function () {

    $('#shipping-popup .show-on-custom-range-select').removeClass("show-div");
    $('.' + $(this).val()).addClass("show-div");
    if ($("option#custom-range-select-popup:selected").length) {
        $('#shipping-popup .hide-when-custom-range-select').hide();

    } else {
        $('#shipping-popup .hide-when-custom-range-select').show();
    }
});


jQuery(document).on('change', '#add-process-time', function () {
    if ($("option#custom-range-select:selected").length) {
        $('.custom-range.custom-range-select-popup.show-on-custom-range-select').show();

    } else {
        ////  $('#shipping-popup .hide-when-custom-range-select').show();
    }
});


//
$('.shipping-section-container input.parent-radio-buttons').click(function () {

    if ($(this).attr('id') === 'custom-shipping') {

        $('.showon-customshipping-select').css("display", "flex");

    } else {
        // $('#show-me').hide();   
        $('.showon-customshipping-select').css("display", "none");
    }

});



if ($('input#custom-shipping').is(':checked')) {
    $('.shipping-section-container .showon-customshipping-select').css("display", "flex");
}






$('.shipping-section-container input.primary-radio').click(function () {
    $('.clculateRecommeded-div').removeClass("show-div");
    $('.fixed-shipping-cost').removeClass("show-div");
    $('#calculate-shipping-option').prop('selectedIndex', 0);
});

jQuery('body').on('click', '.this-hide-all', function (e) {

    //                        $(".hidden-after-link-select").hide();
    $(".anchor-link").hide();
    $(".hidden-first").show();
    $(".remove-added-list").show();
    $("#internationalHandlingFee").val('');
    $(".visible-hidden-hide").css("visibility", "visible");


});
jQuery('body').on('click', '.remove-added-list', function (e) {

    //                        $(".hidden-after-link-select").show();
    $(".anchor-link").show();
    $(".hidden-first").hide();
    $(".remove-added-list").hide();
    $("#internationalHandlingFee").val($("#domesticHandlingFee").val());

    $(".visible-hidden-hide").css("visibility", "hidden");
});

jQuery('body').on('change', '#calculate-shipping-option', function (e) {

    $('.show-on-cost-manually').removeClass("show-div");
    $('.clculateRecommeded-div').removeClass("show-div");
    $('.' + $(this).val()).addClass("show-div");
});


jQuery('body').on('click', '#add-upgrade-shipping,#add-upgrade-shipping-pop', function (event) {

    var randomId = randomNumberFromRange(898, 56565656);
    $html = '';
    $html += '<div class="row entry-body upgrade-shipping"><div class="col-sm-3">';
    $html += '<label class="input-prepend has-validation"><input type="text" class="input form-control" name="shippingUpgrades[upgrapeName][]"  value="" placeholder="Upgrade name"></label></div>';
    $html += '<div class="col-sm-3"><div class="upgrade-type common-mbt-stle">';
    $html += '<div class="form-group"><select  class="form-control" name="shippingUpgrades[level][]"><option value="domestic">Domestic</option><option value="international">International</option></select>';
    $html += '</div></div></div><div class="col-sm-3"><div class="secondary-cost common-mbt-stle">';
    $html += '<label class="input-prepend has-validation"><div class="input-prepend-item"> &#36;</div>';
    $html += '<input type="text" class="input form-control" name="shippingUpgrades[oneItem][]" id="appendPrice_' + randomId + '" value="" onkeypress="Numvalidate(event, \'price\', \'appendPrice_' + randomId + ')" placeholder="0.00"></label></div></div>';
    $html += '<div class="col-sm-2"><div class="secondary-cost common-mbt-stle"><label class="input-prepend has-validation">';
    $html += '<div class="input-prepend-item"> &#36;</div><input type="text"  name="shippingUpgrades[eachItem][]" class="input form-control" value="" placeholder=""></label></div></div>';
    $html += '<div class="col-sm-1 text-center"><a href="javascript:;" class="align-with-btn text-gray-lighter cross-icon-cont remove-container">';
    $html += '<span class="ss-etsy ss-delete icon-smaller icon-t-2"><i class="fa fa-times" aria-hidden="true"></i></span></a></div></div>';
    $(event.target).closest('.fixed-shipping-cost').find('.add-shipping-upgrade-container').append($html);
});

$(document).on('click', 'a.remove-container', function (e) {
    e.preventDefault();
    $(this).parent().parent().remove();
    ///// console.log($(this).parent().parent().find('.popupCountriesMange').text());
    /// console.log($(this).parent().parent().find(".popupCountriesMange option:selected").text());
    if (typeof value === "undefined") {
        $counter = $(this).parent().parent().find(".popupCountriesMange option:selected").val();
        countriesObj[$counter] = $(this).parent().parent().find(".popupCountriesMange option:selected").text();

    }
});

$(document).on('click', 'a.remove-containertabs', function (e) {
    e.preventDefault();
    $(this).parent().parent().parent().remove();

});
$('.list-option-mbt  #processing-time-perioud').change(function () {
    $('.list-option-mbt .show-on-custom-range-select').removeClass("show-div");
    $('.' + $(this).val()).addClass("show-div");
    if ($("option#custom-range-select:selected").length) {
        $('.list-option-mbt  .hide-when-custom-range-select').hide();
    } else {
        $('.list-option-mbt  .hide-when-custom-range-select').show();
    }
});
jQuery(function ($) {
    var $active = $('#accordion .panel-collapse.show').prev().addClass('active');
    $active.find('a').append('<span class="glyphicon glyphicon-minus pull-right"></span>');
    $('#accordion .panel-heading').not($active).find('a').prepend('<span class="glyphicon glyphicon-plus pull-right"></span>');
    $('#accordion').on('show.bs.collapse', function (e) {
        $('#accordion .panel-heading.active').removeClass('active').find('.glyphicon').toggleClass('glyphicon-plus glyphicon-minus');
        $(e.target).prev().addClass('active').find('.glyphicon').toggleClass('glyphicon-plus glyphicon-minus');
    });
    $('#accordion').on('hide.bs.collapse', function (e) {
        $(e.target).prev().removeClass('active').find('.glyphicon').removeClass('glyphicon-minus').addClass('glyphicon-plus');
    });
});
$('.product-values-add-product .values-selector input:checked').each(function () {
    alert($(this).val());
});
jQuery('body').on('change', '.attributeUnits', function (e) {
    var mainDivVariation = $(this).parentsUntil('.VariationMainDiv').parent();
    var unitVal = $(this).find('option:selected').val();
    var optionHTML = customTextField(unitVal);
    mainDivVariation.find('.variationUnit').empty();
    mainDivVariation.find('.variationOptions').empty();
    mainDivVariation.find('.customFeildVariation').empty();
    mainDivVariation.find('.variation_attribute_unit').val(unitVal);
    mainDivVariation.find('.variationOptions').append(optionHTML);
    /// alert(unitVal);

});
jQuery('#productTitle', '#productShortDescription', '#productQuantity , #productWeight_lbs , #productWeight_oz').on('keyup blur', function (e) {
    var $th = jQuery(this);
    var input_field_value = jQuery(this).val();
    if (input_field_value != '') {
        $th.css('border-color', '#ced4da');
    } else {
        $th.css('border-color', 'red');
    }

});
// $('#productTagname').tagsinput({
//     maxChars: 60,
//     maxTags: 13
// });
$('#productTagname').on('itemAdded', function (event) {
    var Count = $('.ProducttagsCounter').text();
    var NewCount = parseInt(Count) - 1;
    $('.ProducttagsCounter').text(NewCount);
});
$('#productTagname').on('itemRemoved', function (event) {
    var Count = $('.ProducttagsCounter').text();
    var NewCount = parseInt(Count) + 1;
    $('.ProducttagsCounter').text(NewCount);
});
$("input[name='productType']").each(function () {

    if ($(this).is(':checked')) {
        if ($(this).val() == 'digital') {

            $('.shippingTab').hide();
            $('.btn_parentVariation').hide();
            $('#productVariationTable').hide();
            $('.digitalProductTab').show();
        } else {

            $('.shippingTab').show();
            $('.digitalProductTab').hide();
            if (jQuery('#productMainCategory').val() != 0) {

                $('.btn_parentVariation').show();
                $('#productVariationTable').show();
            }
        }
    }
});
//jQuery('#productTitle').on('keyup', function (e) { // fires on every keyup & blur
//    var $th = jQuery(this);
//    var productTitle = jQuery(this).val();
//    if (productTitle != '') {
//        var regex = new RegExp("^[a-zA-Z0-9_@$*\s]+$");
//        var str = String.fromCharCode(!e.charCode ? e.which : e.charCode);
//        if (regex.test(str)) {
//            jQuery('.productTitle-error').css('display', 'none');
//            jQuery(this).css('border-color', 'rgba(0, 128, 0, 0.39)');
//            return true;
//        } else {
//            $th.val($th.val().replace(/[^a-zA-Z0-9_@$*\s]/g, function (str) {
//                jQuery('.productTitle-error').css('display', 'block');
//                jQuery(this).css('border-color', 'red');
//                jQuery(this).focus();
//                return '';
//            }));
//        }
//        e.preventDefault();
//        return false;
//    } else {
//        jQuery(this).css('border-color', 'red');
//        jQuery(this).focus();
//        return;
//    }
//});
jQuery('#productTitle').on('blur', function (e) { // fires on every keyup & blur
    var productTitle = jQuery(this).val();
    if (productTitle == '') {
        jQuery(this).css('border-color', 'red');
        // jQuery(this).focus();
        // return;
    } else {
        jQuery('.productTitle-error').css('display', 'none');
        jQuery(this).css('border-color', '#ced4da');
        return true;
    }
});
//jQuery('#productQuantity').on('keyup', function (e) { // fires on every keyup & blur
//    var $th = jQuery(this);
//    var productQuantity = jQuery(this).val();
//    if (productQuantity != '') {
//        var regex = new RegExp("^[0-9]+$");
//        var str = String.fromCharCode(!e.charCode ? e.which : e.charCode);
//        if (regex.test(str)) {
//            jQuery('.productQuantity-error').css('display', 'none');
//            jQuery(this).css('border-color', '#ced4da');
//            return true;
//        } else {
//            $th.val($th.val().replace(/[^0-9]/g, function (str) {
//                jQuery('.productQuantity-error').css('display', 'block');
//                jQuery(this).css('border-color', 'red');
//                jQuery(this).focus();
//                return '';
//            }));
//        }
//        e.preventDefault();
//        return false;
//    } else {
//        jQuery(this).css('border-color', 'red');
//        jQuery(this).focus();
//        return;
//    }
//});
jQuery('#productcurrentstock , #productPrice').on('keyup blur', function (e) { // fires on every keyup & blur
    var productQuantity = jQuery(this).val();
    if (productQuantity == '' || productQuantity <= 0) {
        jQuery(this).css('border-color', 'red');
        // jQuery(this).focus();
        return;
    } else {
        jQuery('.productQuantity-error').css('display', 'none');
        jQuery(this).css('border-color', '#ced4da');
        return true;
    }
});



//function genrateKeyEvent(type, ID) {
//    return Numvalidate(event, type, ID);
//}
//$(window).keydown(function(event){
//    alert(event.keyCode);
//});
//jQuery('.keypressNumValidate').on("keypress", function (event) {
//    
//    console.log(event)
//    Numvalidate(event, 'Price', $(this).attr('id'));
//});



function alphaNumvalidate(evt, _this, charLength) {
    ////  console.log(evt);
    var theEvent = evt || window.event;
    var key = theEvent.keyCode || theEvent.which;
    key = String.fromCharCode(key);
    var regex = /^[a-z\d\''""-+'.?&*%$#@()=_\s\b~!^]+$/i;
    if (!regex.test(key)) {
        theEvent.returnValue = false;
        if (theEvent.preventDefault)
            theEvent.preventDefault();
    } else {
        var checkLength = jQuery('#' + _this).val();
        if (checkLength.length > charLength) {
            theEvent.returnValue = false;
            if (theEvent.preventDefault)
                theEvent.preventDefault();
        }
        if (checkLength) {
            jQuery('#' + _this).css('border-color', '#ced4da');
            /// jQuery('#' + _this).css('border-color', '')
        }
    }
}
function alphaNum(evt, _this, charLength) {
    alert("called");
    ////  console.log(evt);
    var theEvent = evt || window.event;
    var key = theEvent.keyCode || theEvent.which;
    key = String.fromCharCode(key);
    //var regex = /^[a-z0-9\b\n.]+$/i;
    var regex = /^[a-z\d\''""-+'.?&*%$#@()=_\s\b~!^]+$/i;
    ;
    if (!regex.test(key)) {
        theEvent.returnValue = false;
        if (theEvent.preventDefault)
            theEvent.preventDefault();
    } else {
        var checkLength = jQuery('#' + _this).val();
        if (checkLength.length > charLength) {
            theEvent.returnValue = false;
            if (theEvent.preventDefault)
                theEvent.preventDefault();
        }
        if (checkLength) {
            jQuery('#' + _this).css('border-color', '#ced4da');
            /// jQuery('#' + _this).css('border-color', '')
        }
    }
}


//function shippingValidat(event, _this) {
//
//    var key = window.event ? event.keyCode : event.which;
//
//    if (event.keyCode == 8 || event.keyCode == 46
//            || event.keyCode == 37 || event.keyCode == 39) {
//        return true;
//    } else if (key < 48 || key > 57) {
//        return false;
//    } else {
//        var checkLength = jQuery('body').find('#' + _this).val();
//        if (checkLength.length >= 2) {
////            && checkLength>30
//             //jQuery('#' + _this).css('border-color', 'red');
//            //jQuery('#' + _this).css('border-color', 'red');
//            if ($("#productSize_length").val() > 100) {
//                jQuery('#productSize_length').css('border-color', 'red');
//            }  if ($("#productSize_width").val() > 80) {
//                jQuery('#productSize_width').css('border-color', 'red');
//            }  if ($("#productSize_height").val() > 100) {
//                jQuery('#productSize_height').css('border-color', 'red');
//            }  if ($("#productWeight").val() > 30) {
//                jQuery('#productWeight').css('border-color', 'red');
//            } 
//                
//
//           // return false;
//        } else {
//            jQuery('#' + _this).css('border-color', '#ced4da');
//        }
//        return true;
//    }
//}

function Numvalidate(evt, _this) {
    var theEvent = evt || window.event;
    var key = theEvent.keyCode || theEvent.which;
    key = String.fromCharCode(key);
    var regex = /^[0-9._\b]+$/;
    //var regex = /[0-9]|\./;
    if (!regex.test(key)) {
        theEvent.returnValue = false;
        if (theEvent.preventDefault)
            theEvent.preventDefault();
    } else {
        var checkLength = jQuery('body').find('#' + _this).val();
        if (checkLength.length > 4) {
            return false;
        }
        if (checkLength) {
            jQuery('#' + _this).css('border-color', '#ced4da');
            /// jQuery('#' + _this).css('border-color', '')
        }
    }
}


function showUploadedItem_popup(source) {

    var imagDiv_popup = document.getElementById('showImagePopup_modal');

    $('body').find('.photoSection .uploadImages').delay(1000).hide();
    var totalImages = jQuery('.showImagePopup_modal .image-panel').length;
    imgCounter = totalImages;

    var html = '';
    var i_inner = 0, len = source.fileName.length;

    for (; i_inner < len; i_inner++) {

        var validateClass = '';
        if (source.validate[i_inner] == false) {
            validateClass = 'required-field';
            return false;
        }

        $('#thumbnail-image-box').empty();
        $('#uploadImages').hide();
        $('.imageBox').hide();

        
        randomCode = randomNumberFromRange(101, 99999999999);
        html += '<div id="img_' + randomCode + '" class=" variation_images_create image-panel variation_images  ' + validateClass + '">';
        html += '<div class="image-inner-mbt"><div class="img-only">';
        //html += '<a class="viewImages" rel="prettyPhoto" href="/uploads/temp/' + source.fileName[i_inner] + '">';
        html += '<img  class="previewClass" src= "/uploads/temp/' + source.fileName[i_inner] + '" />';
        // html += '</a>';
        html += '</div><div class="loading-image"><span></span></div> ';
        html += '<input id="imagesrc' + randomCode + '" type="hidden" data-variation-selector="" class="select_variation_image_create"  value= "' + source.fileName[i_inner] + '" name="productImage_variation[]"/> ';
        
        html += '<div class="clearfix spacer20"></div><div class="col-md-12 docs-toggles"> ';
        /// html += '<div class="btn-group btn-group-justified" data-toggle="buttons">';
        ///html += '<label class="btn btn-primary active"> <input type="radio" class="sr-only" id="aspectRatio0" name="aspectRatio" value="1.7777777777777777"> <span class="docs-tooltip" data-toggle="tooltip" data-container="body" title="aspectRatio: 16 / 9"> 16:9 </span> </label> <label class="btn btn-primary"> <input type="radio" class="sr-only" id="aspectRatio1" name="aspectRatio" value="1.3333333333333333"> <span class="docs-tooltip" data-toggle="tooltip" data-container="body" title="aspectRatio: 4 / 3"> 4:3 </span> </label> <label class="btn btn-primary"> <input type="radio" class="sr-only" id="aspectRatio2" name="aspectRatio" value="1"> <span class="docs-tooltip" data-toggle="tooltip" data-container="body" title="aspectRatio: 1 / 1"> 1:1 </span> </label> <label class="btn btn-primary"> <input type="radio" class="sr-only" id="aspectRatio3" name="aspectRatio" value="0.6666666666666666"> <span class="docs-tooltip" data-toggle="tooltip" data-container="body" title="aspectRatio: 2 / 3"> 2:3 </span> </label> <label class="btn btn-primary"> <input type="radio" class="sr-only" id="aspectRatio4" name="aspectRatio" value="NaN"> <span class="docs-tooltip" data-toggle="tooltip" data-container="body" title="aspectRatio: NaN" checked="checked" > Free </span> </label>';
        ///html += '</div>';
        html += '</div></div></div>';
        html += '</div></div></div></div></div> </div>';

    }
    
    imagDiv_popup.insertAdjacentHTML('beforeend', html);

    // Product Thumbnail
    var productThumbnail = document.getElementById('showFeatureImagePopup_modal');

    $('body').find('.photoSection .uploadImages').delay(1000).hide();
    var totalImages = jQuery('.showFeatureImagePopup_modal .image-panel').length;
    imgCounter = totalImages;

    var htmlThumbnail = '';
    var i_inner = 0, len = source.fileName.length;

    for (; i_inner < len; i_inner++) {

        var validateClass = '';
        if (source.validate[i_inner] == false) {
            validateClass = 'required-field';
            return false;
        }

        $('#thumbnail-image-box').empty();
        $('#uploadImages').hide();
        $('.imageBox').hide();


        randomCode = randomNumberFromRange(101, 99999999999);
        htmlThumbnail += '<div id="img_' + randomCode + '" class=" feature_images_create image-panel selectFeatureImages  ' + validateClass + '">';
        htmlThumbnail += '<div class="image-inner-mbt"><div class="img-only">';
        htmlThumbnail += '<img  class="previewClass" src= "/uploads/temp/' + source.fileName[i_inner] + '" />';
        htmlThumbnail += '</div><div class="loading-image"><span></span></div> ';
        htmlThumbnail += ' <input type="radio" class="form-check-input select_feature_image" data-featrue-image="'+ source.fileName[i_inner] + '" data-image-path="/uploads/temp/' + source.fileName[i_inner] + '" name="optradio" value="' + i_inner + '">';
        // htmlThumbnail += '<input id="feature_iamge' + randomCode + '" type="radio" data-feature-selector=""  class="select_feature_image_create"  value= "' + i_inner + '" name="optradio"/> ';
        htmlThumbnail += '<div class="dele-icn d-none">';
        // html += ' <input type="radio" class="form-check-input select_primary_image select_feature_image" name="optradio" value="' + i_inner + '">Feature';
        htmlThumbnail += '<a class="editEvent d-none" href="javascript:;"  data-toggle="modal" data-target="#cropper_' + randomCode + '" >';
        htmlThumbnail += '<i class="fa fa-crop" aria-hidden="true"></i></a>&nbsp;&nbsp;';
        htmlThumbnail += '<a href="javascript:;" class="dete-parent d-none" onclick="deleteImage(' + randomCode + ')">';
        htmlThumbnail += '<i class="fa fa-trash-o" aria-hidden="true"></i></a>';
        htmlThumbnail += '</div><div class="modal" id="cropper_' + randomCode + '" tabindex="-1" role="dialog" aria-hidden="true" >';
        htmlThumbnail += '<div class="modal-dialog animated bounceInDown" style="max-width:800px"> <div class="modal-content"> ';
        htmlThumbnail += '<div class="modal-body"> <div class="row imagecrop-area"> <div class="col-xs-12 col-sm-12"> <div class="img-container"> ';
        htmlThumbnail += ' <img class="Cropperimage" src="/uploads/temp/' + source.fileName[i_inner] + '" alt="Picture"> </div>';
        htmlThumbnail += '<div class="clearfix"></div><br></div><div class="clearfix spacer20"></div><div class="col-md-12 docs-toggles"> ';
        htmlThumbnail += '</div></div></div>';
        htmlThumbnail += '</div></div></div></div></div> </div>';

    }

    productThumbnail.insertAdjacentHTML('beforeend', htmlThumbnail);
}

function showUploadedItem(source) {
    // console.log(source);
    var imagDiv = document.getElementById('all-uplpad-image');
    // var thumbnailImagDiv = document.getElementById('thumbnail-image-box');

    $('body').find('.photoSection .uploadImages').delay(1000).hide();
    var totalImages = jQuery('#all-uplpad-image .image-panel').length;
    imgCounter = totalImages;

    var html = '';
    var thumbnailHtml = '';
    var i_inner = 0, len = source.fileName.length;

    for (; i_inner < len; i_inner++) {

        var validateClass = '';
        if (source.validate[i_inner] == false) {
            validateClass = 'required-field';
            return false;
        }

        $('#thumbnail-image-box').empty();
        $('#uploadImages').hide();
        $('.imageBox').hide();

        //        if (imgCounter > 0) {
        //            if (imgCounter == 1) {
        //                ////  $('#uploadImages').hide();
        //                break;
        //            } else {
        //                $('.images-added .prdimg.pi1 .imgtg').hide();
        //                $('.imageBox').eq(imgCounter - 1).hide();
        //            }
        //        }

        randomCode = randomNumberFromRange(101, 99999999999);
        html += '<div id="img_' + randomCode + '" class="image-panel ' + validateClass + '">';
        html += '<div class="image-inner-mbt"><div class="img-only">';
        html += '<a class="viewImages" rel="prettyPhoto" href="/uploads/temp/' + source.fileName[i_inner] + '">';
        html += '<img  class="previewClass" src= "/uploads/temp/' + source.fileName[i_inner] + '" />';
        html += '</a>';
        html += '</div><div class="loading-image"><span></span></div> ';
        html += '<input id="imagesrc' + randomCode + '" type="hidden" value= "' + source.fileName[i_inner] + '" name="productImage[]" class="prev-image-url"/> ';
        html += '<div class="dele-icn">';
        // html += ' <input type="radio" class="form-check-input select_primary_image select_feature_image" name="optradio" value="' + i_inner + '">Feature';
        html += '<a class="editEvent" href="javascript:;"  data-toggle="modal" data-target="#cropper_' + randomCode + '" >';
        html += '<i class="fa fa-crop" aria-hidden="true"></i></a>&nbsp;&nbsp;';
        html += '<a href="javascript:;" class="dete-parent" onclick="deleteImage(' + randomCode + ')">';
        html += '<i class="fa fa-trash-o" aria-hidden="true"></i></a>';
        html += '</div><div class="modal" id="cropper_' + randomCode + '" tabindex="-1" role="dialog" aria-hidden="true" >';
        html += '<div class="modal-dialog animated bounceInDown" style="max-width:800px"> <div class="modal-content"> ';
        html += '<div class="modal-body"> <div class="row imagecrop-area"> <div class="col-xs-12 col-sm-12"> <div class="img-container"> ';
        html += ' <img class="Cropperimage" src="/uploads/temp/' + source.fileName[i_inner] + '" alt="Picture"> </div>';
        html += '<div class="clearfix"></div><br></div><div class="clearfix spacer20"></div><div class="col-md-12 docs-toggles"> ';
        /// html += '<div class="btn-group btn-group-justified" data-toggle="buttons">';
        ///html += '<label class="btn btn-primary active"> <input type="radio" class="sr-only" id="aspectRatio0" name="aspectRatio" value="1.7777777777777777"> <span class="docs-tooltip" data-toggle="tooltip" data-container="body" title="aspectRatio: 16 / 9"> 16:9 </span> </label> <label class="btn btn-primary"> <input type="radio" class="sr-only" id="aspectRatio1" name="aspectRatio" value="1.3333333333333333"> <span class="docs-tooltip" data-toggle="tooltip" data-container="body" title="aspectRatio: 4 / 3"> 4:3 </span> </label> <label class="btn btn-primary"> <input type="radio" class="sr-only" id="aspectRatio2" name="aspectRatio" value="1"> <span class="docs-tooltip" data-toggle="tooltip" data-container="body" title="aspectRatio: 1 / 1"> 1:1 </span> </label> <label class="btn btn-primary"> <input type="radio" class="sr-only" id="aspectRatio3" name="aspectRatio" value="0.6666666666666666"> <span class="docs-tooltip" data-toggle="tooltip" data-container="body" title="aspectRatio: 2 / 3"> 2:3 </span> </label> <label class="btn btn-primary"> <input type="radio" class="sr-only" id="aspectRatio4" name="aspectRatio" value="NaN"> <span class="docs-tooltip" data-toggle="tooltip" data-container="body" title="aspectRatio: NaN" checked="checked" > Free </span> </label>';
        ///html += '</div>';
        html += '</div></div></div>';
        html += '<div class="modal-footer"> <button data-dismiss="modal" class="btn btn-primary btn-simple" type="button">Close</button> <button class="btn-primary btn  text-white btn-success getCropImage" type="button" data-dismiss="modal">Save</button> </div></div></div></div></div></div> </div>';



        randomCode = randomNumberFromRange(101, 99999999999);
        thumbnailHtml += '<div id="img_' + randomCode + '" class="image-panel ' + validateClass + '">';
        thumbnailHtml += '<div class="image-inner-mbt"><div class="img-only">';
        thumbnailHtml += '<a class="viewImages" rel="prettyPhoto" href="/uploads/temp/' + source.fileName[i_inner] + '">';
        thumbnailHtml += '<img  class="previewClass" src= "/uploads/temp/' + source.fileName[i_inner] + '" />';
        thumbnailHtml += '</a>';
        thumbnailHtml += '</div><div class="loading-image"><span></span></div> ';
        thumbnailHtml += '<input id="imagesrc' + randomCode + '" type="hidden" value= "' + source.fileName[i_inner] + '" name="productImageThumbnail"/> ';
        thumbnailHtml += '<span class="imgtg" style="    display: inline-block;">Thumbnail</span><div class="dele-icn">';
        thumbnailHtml += '<a class="editEvent" href="javascript:;"  data-toggle="modal" data-target="#cropper_' + randomCode + '" >';
        thumbnailHtml += '<i class="fa fa-crop" aria-hidden="true"></i></a>';
        ///thumbnailHtml += '<a href="javascript:;" class="dete-parent" onclick="deleteImage(' + randomCode + ')">';
        /// thumbnailHtml += '<i class="fa fa-trash-o" aria-hidden="true"></i></a>';
        thumbnailHtml += '</div><div class="modal" id="cropper_' + randomCode + '" tabindex="-1" role="dialog" aria-hidden="true" >';
        thumbnailHtml += '<div class="modal-dialog animated bounceInDown" style="max-width:800px"> <div class="modal-content"> ';
        thumbnailHtml += '<div class="modal-body"> <div class="row imagecrop-area"> <div class="col-xs-12 col-sm-12"> <div class="img-container"> ';
        thumbnailHtml += ' <img class="Cropperimage" src="/uploads/temp/' + source.fileName[i_inner] + '" alt="Picture"> </div>';
        thumbnailHtml += '<div class="clearfix"></div><br></div><div class="clearfix spacer20"></div><div class="col-md-12 docs-toggles"> ';
        /// html += '<div class="btn-group btn-group-justified" data-toggle="buttons">';
        ///html += '<label class="btn btn-primary active"> <input type="radio" class="sr-only" id="aspectRatio0" name="aspectRatio" value="1.7777777777777777"> <span class="docs-tooltip" data-toggle="tooltip" data-container="body" title="aspectRatio: 16 / 9"> 16:9 </span> </label> <label class="btn btn-primary"> <input type="radio" class="sr-only" id="aspectRatio1" name="aspectRatio" value="1.3333333333333333"> <span class="docs-tooltip" data-toggle="tooltip" data-container="body" title="aspectRatio: 4 / 3"> 4:3 </span> </label> <label class="btn btn-primary"> <input type="radio" class="sr-only" id="aspectRatio2" name="aspectRatio" value="1"> <span class="docs-tooltip" data-toggle="tooltip" data-container="body" title="aspectRatio: 1 / 1"> 1:1 </span> </label> <label class="btn btn-primary"> <input type="radio" class="sr-only" id="aspectRatio3" name="aspectRatio" value="0.6666666666666666"> <span class="docs-tooltip" data-toggle="tooltip" data-container="body" title="aspectRatio: 2 / 3"> 2:3 </span> </label> <label class="btn btn-primary"> <input type="radio" class="sr-only" id="aspectRatio4" name="aspectRatio" value="NaN"> <span class="docs-tooltip" data-toggle="tooltip" data-container="body" title="aspectRatio: NaN" checked="checked" > Free </span> </label>';
        ///html += '</div>';
        thumbnailHtml += '</div></div></div>';
        thumbnailHtml += '<div class="modal-footer"> <button data-dismiss="modal" class="btn btn-primary btn-simple" type="button">Close</button> <button class="btn-primary btn btn-success getCropImage" type="button" data-dismiss="modal">Save</button> </div></div></div></div></div></div> </div>';
    }

    ///document.getElementById('thumbnailImage').insertAdjacentHTML('beforeend', html);
    //imagDiv.append(html);
    //thumbnailImagDiv.append(thumbnailHtml);
    imagDiv.insertAdjacentHTML('beforeend', html);

    $("a[rel^='prettyPhoto']").prettyPhoto({
        social_tools: ''
    });

    //    var Imagesbox = jQuery('#all-uplpad-image .image-panel').length;
    //    if (Imagesbox == 4) {
    //        jQuery('body').find('.col-md-8.rghtexp').addClass('image-four');
    //        jQuery('body').find('.col-md-8.rghtexp').removeClass('image-five');
    //        jQuery('body').find('.col-md-8.rghtexp').removeClass('image-six');
    //    }
    //
    //    if (Imagesbox == 5) {
    //        jQuery('body').find('.col-md-8.rghtexp').removeClass('image-four');
    //        jQuery('body').find('.col-md-8.rghtexp').addClass('image-five');
    //        jQuery('body').find('.col-md-8.rghtexp').removeClass('image-six');
    //    }
    //    if (Imagesbox == 6) {
    //        jQuery('body').find('.col-md-8.rghtexp').removeClass('image-four');
    //        jQuery('body').find('.col-md-8.rghtexp').removeClass('image-five');
    //        jQuery('body').find('.col-md-8.rghtexp').addClass('image-six');
    //    }

    //  $('body').find('.photoSection .uploadImages').delay(2000).hide();


    //// $('.uploadImages').delay(2000).hide();
}


jQuery('body').on('click', '#addCauseBtn', function (e) {
    var causeTitle = jQuery('#causeTitle').val();
    if (causeTitle == '') {
        jQuery('#causeTitle').css('border-color', 'red');
        jQuery(this).focus();
        //// return;
    } else {
        jQuery('#causeTitle').css('border-color', 'rgba(0, 128, 0, 0.39)');
    }
    var causeDesription = jQuery('#causeDesription').val();
    if (causeDesription == '') {
        jQuery('#causeDesription').css('border-color', 'red');
        jQuery(this).focus();
        return;
    } else {
        jQuery('#causeDesription').css('border-color', 'rgba(0, 128, 0, 0.39)');
    }

    var html = '';
    randomCode = randomNumberFromRange(12, 10000);
    html += '<div class="list-repeater-mbt">';
    html += '<h4>';
    html += causeTitle;
    html += '<input id="causeListTitle' + randomCode + '" type="hidden" value= "' + causeTitle + '" name="causeListTitle[]"/> ';
    html += '<span class="pull-right text-center">';
    html += ' <a href="javascript:;" class="align-with-btn text-gray-lighter cross-icon-cont remove-containertabs">';
    html += '<span class="ss-etsy ss-delete icon-smaller icon-t-2">';
    html += '<i class="fa fa-times" aria-hidden="true"></i></span>';
    html += '</a>';
    html += '</span>';
    html += '</h4>';
    /// html += '<p>';
    /// html += causeDesription;
    /// html += '<input id="causeListDescription' + randomCode + '" type="hidden" value= "' + causeDesription + '" name="causeListDescription[]"/> ';
    /// html += '</p>';
    html += ' </div>';
    jQuery('.cause-list-results').append(html);
    jQuery('#causeTitle').val('');
    /// jQuery('#causeDesription').val('');
});
jQuery('body').on('click', '#addFaqBtn', function (e) {
    var faqQuestion = jQuery('#faqQuestion').val();
    if (faqQuestion == '') {
        jQuery('#faqQuestion').css('border-color', 'red');
        jQuery('#faqQuestion').focus();
        //// return;
    } else {
        jQuery('#faqQuestion').css('border-color', 'rgba(0, 128, 0, 0.39)');
        ///return true;
    }
    var faqAnswer = jQuery('#faqAnswer').val();
    if (faqAnswer == '') {
        jQuery('#faqAnswer').css('border-color', 'red');
        jQuery('#faqAnswer').focus();
        return;
    } else {

        jQuery('#faqAnswer').css('border-color', 'rgba(0, 128, 0, 0.39)');
        /// return true;
    }
    if (faqQuestion == '') {
        return false;
    }
    var html = '';
    var childHtml = '';
    randomCode = randomNumberFromRange(12, 10000);
    html += '<div class="list-repeater-mbt">';
    childHtml += '<h4>Q: ';
    childHtml += faqQuestion;
    childHtml += '<input id="causeListTitle' + randomCode + '" type="hidden" value= "' + faqQuestion + '" name="faqQuestion[]"/> ';
    childHtml += '<span class="pull-right text-center">';
    childHtml += ' <a href="javascript:;" class="align-with-btn text-gray-lighter cross-icon-cont remove-containertabs">';
    childHtml += '<span class="ss-etsy ss-delete icon-smaller icon-t-2">';
    childHtml += '<i class="fa fa-times" aria-hidden="true"></i></span>';
    childHtml += '</a>';
    childHtml += '</span>';
    childHtml += '</h4>';

    childHtml += '<p>A: ';
    childHtml += faqAnswer;
    childHtml += '<input id="faqAnswer' + randomCode + '" type="hidden" value= "' + faqAnswer + '" name="faqAnswer[]"/> ';
    childHtml += '</p>';

    html += childHtml + ' </div>';
    jQuery('.ask-question-section').append('<div class="ask-repeater">' + childHtml + '</div>');
    jQuery('.faqs-list-results').append(html);
    jQuery('#faqQuestion').val('');
    jQuery('#faqAnswer').val('');
});
jQuery('body').on('click', '.getCropImage', function (e) {
   
    var mainImageDiv = $(this).parentsUntil('.image-panel ');
    var cropcanvas = mainImageDiv.find('.Cropperimage').cropper('getCroppedCanvas', {
        //        width: 217,
        //        height: 146,
    });
    var prevImageUrl = $(this).parentsUntil('.image-panel ').find('.prev-image-url').val();
    $('body').find('.photoSection .uploadImages').delay(1000).show();
    var croppng = cropcanvas.toDataURL("image/png");
    
    mainImageDiv.find('.previewClass').attr('src', croppng);
    mainImageDiv.find('.viewImages').attr('data-featherlight', croppng);
    var formData = new FormData();
    ////  console.log(croppng);
    formData.append('croppedImage', croppng);
    $.ajax({
        url: "/uploadImage",
        method: 'POST',
        processData: false,
        contentType: false,
        data: formData,
        success: function (res) {

            mainImageDiv.find('input[type=hidden]').val(res);
            mainImageDiv.find('.viewImages').attr('data-featherlight', '/uploads/temp/' + res);
            $('body').find('.photoSection .uploadImages').delay(1000).hide();
            var featureimageLength = $('.selectFeatureImages').length;
            for (let i = 0; i < featureimageLength; i++) {
                let found = $('.selectFeatureImages')[i];
                let imagssr = $(found).find('.select_feature_image').attr('data-featrue-image');
                if(imagssr == prevImageUrl){
                    found.remove();
                }
            }
            var featureImage = $('#imageFeatureSrc').val();
            if(featureImage == prevImageUrl){
                $('#imageFeatureSrc').parent().parent().remove();
            }
            
            $('.select_variation_image_create').each(function(){
                let imagepath = $(this).val();
                if(imagepath == prevImageUrl){
                    
                    $(this).parent().parent().remove();

                }
            });
            $('.product_variation_image').each(function(){
                let imagepathValue = $(this).val();
                if(imagepathValue == prevImageUrl){
                    
                    $(this).val('');

                }
            });

            var select_variation_image_insert = document.getElementsByClassName('select_variation_image_insert');
            if(select_variation_image_insert != undefined){
                
                $('.select_variation_image_insert').each(function(){
                    let imagepath = $(this).val();
                    if(imagepath == prevImageUrl){
                     
                        $(this).parent().remove();
    
                    }
                });
            }
            


            var html = '';

            // add cropped image into variation image popup
            randomCode = randomNumberFromRange(101, 99999999999);
            html += '<div id="img_' + randomCode + '" class=" variation_images_create image-panel variation_images ">';
            html += '<div class="image-inner-mbt"><div class="img-only">';
            //html += '<a class="viewImages" rel="prettyPhoto" href="/uploads/temp/' + source.fileName[i_inner] + '">';
            html += '<img  class="previewClass" src= "/uploads/temp/' + res + '" />';
            // html += '</a>';
            html += '</div><div class="loading-image"><span></span></div> ';
            html += '<input id="imagesrc' + randomCode + '" type="hidden" data-variation-selector="" class="select_variation_image_create"  value= "' + res + '" name="productImage_variation[]"/> ';
            
            html += '<div class="clearfix spacer20"></div><div class="col-md-12 docs-toggles"> ';
            /// html += '<div class="btn-group btn-group-justified" data-toggle="buttons">';
            ///html += '<label class="btn btn-primary active"> <input type="radio" class="sr-only" id="aspectRatio0" name="aspectRatio" value="1.7777777777777777"> <span class="docs-tooltip" data-toggle="tooltip" data-container="body" title="aspectRatio: 16 / 9"> 16:9 </span> </label> <label class="btn btn-primary"> <input type="radio" class="sr-only" id="aspectRatio1" name="aspectRatio" value="1.3333333333333333"> <span class="docs-tooltip" data-toggle="tooltip" data-container="body" title="aspectRatio: 4 / 3"> 4:3 </span> </label> <label class="btn btn-primary"> <input type="radio" class="sr-only" id="aspectRatio2" name="aspectRatio" value="1"> <span class="docs-tooltip" data-toggle="tooltip" data-container="body" title="aspectRatio: 1 / 1"> 1:1 </span> </label> <label class="btn btn-primary"> <input type="radio" class="sr-only" id="aspectRatio3" name="aspectRatio" value="0.6666666666666666"> <span class="docs-tooltip" data-toggle="tooltip" data-container="body" title="aspectRatio: 2 / 3"> 2:3 </span> </label> <label class="btn btn-primary"> <input type="radio" class="sr-only" id="aspectRatio4" name="aspectRatio" value="NaN"> <span class="docs-tooltip" data-toggle="tooltip" data-container="body" title="aspectRatio: NaN" checked="checked" > Free </span> </label>';
            ///html += '</div>';
            html += '</div></div></div>';
            html += '</div></div></div></div></div> </div>';
            var htmlThumbnail = '';
            var imagDiv_popup = document.getElementById('showImagePopup_modal');
           if(imagDiv_popup != undefined){
            imagDiv_popup.insertAdjacentHTML('beforeend', html);
           }
            
          
            // add cropped image into thumbnail image popup
           
            randomCode = randomNumberFromRange(101, 99999999999);
            htmlThumbnail += '<div id="img_' + randomCode + '" class=" feature_images_create image-panel selectFeatureImages">';
            htmlThumbnail += '<div class="image-inner-mbt"><div class="img-only">';
            htmlThumbnail += '<img  class="previewClass" src= "/uploads/temp/' + res + '" />';
            htmlThumbnail += '</div><div class="loading-image"><span></span></div> ';
            htmlThumbnail += ' <input type="radio" class="form-check-input select_feature_image" data-featrue-image="'+ res + '" data-image-path="/uploads/temp/' + res + '" name="optradio" value="' + 0 + '">';
            // htmlThumbnail += '<input id="feature_iamge' + randomCode + '" type="radio" data-feature-selector=""  class="select_feature_image_create"  value= "' + i_inner + '" name="optradio"/> ';
            htmlThumbnail += '<div class="dele-icn d-none">';
            // html += ' <input type="radio" class="form-check-input select_primary_image select_feature_image" name="optradio" value="' + i_inner + '">Feature';
            htmlThumbnail += '<a class="editEvent d-none" href="javascript:;"  data-toggle="modal" data-target="#cropper_' + randomCode + '" >';
            htmlThumbnail += '<i class="fa fa-crop" aria-hidden="true"></i></a>&nbsp;&nbsp;';
            htmlThumbnail += '<a href="javascript:;" class="dete-parent d-none" onclick="deleteImage(' + randomCode + ')">';
            htmlThumbnail += '<i class="fa fa-trash-o" aria-hidden="true"></i></a>';
            htmlThumbnail += '</div><div class="modal" id="cropper_' + randomCode + '" tabindex="-1" role="dialog" aria-hidden="true" >';
            htmlThumbnail += '<div class="modal-dialog animated bounceInDown" style="max-width:800px"> <div class="modal-content"> ';
            htmlThumbnail += '<div class="modal-body"> <div class="row imagecrop-area"> <div class="col-xs-12 col-sm-12"> <div class="img-container"> ';
            htmlThumbnail += ' <img class="Cropperimage" src="/uploads/temp/' + res + '" alt="Picture"> </div>';
            htmlThumbnail += '<div class="clearfix"></div><br></div><div class="clearfix spacer20"></div><div class="col-md-12 docs-toggles"> ';
            htmlThumbnail += '</div></div></div>';
            htmlThumbnail += '</div></div></div></div></div> </div>';
            var productThumbnail = document.getElementById('showFeatureImagePopup_modal');
            if(productThumbnail != undefined){
                productThumbnail.insertAdjacentHTML('beforeend', htmlThumbnail);
            }
            
        },
        error: function () {
            console.log('Upload error');
            $('body').find('.photoSection .uploadImages').delay(1000).hide();
        }

    });
});
jQuery('body').on('click', '.editEvent', function (e) {
  
    var mainImageDiv = $(this).parentsUntil('.image-panel');
    var options = {
        aspectRatio: 16 / 9,
        crop: function (e) {

        }
    };
    // Cropper
    mainImageDiv.find('.Cropperimage').on({
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
    // Options
    mainImageDiv.find('.docs-toggles').on('change', 'input', function () {
        var $this = $(this);
        var name = $this.attr('name');
        var type = $this.prop('type');
        var cropBoxData;
        var canvasData;
        if (!mainImageDiv.find('.Cropperimage').data('cropper')) {
            return;
        }


        if (type === 'checkbox') {
            options[name] = $this.prop('checked');
            cropBoxData = mainImageDiv.find('.Cropperimage').cropper('getCropBoxData');
            canvasData = mainImageDiv.find('.Cropperimage').cropper('getCanvasData');
            options.built = function () {
                mainImageDiv.find('.Cropperimage').cropper('setCropBoxData', cropBoxData);
                mainImageDiv.find('.Cropperimage').cropper('setCanvasData', canvasData);
            };
        } else if (type === 'radio') {
            options[name] = $this.val();
        }
        /// console.log( $().cropper('getCroppedCanvas').toDataURL('image/jpeg'));
        mainImageDiv.find('.Cropperimage').cropper('destroy').cropper(options);
    });
    setTimeout(function () {
        mainImageDiv.find(".docs-toggles .btn-primary").trigger("click");
    }, 1);
});
//console.clear();

// scripts fro add product page




jQuery(document).on('change', '#shipping-popup #processing-time-perioud-popup', function () {

    $('#shipping-popup .show-on-custom-range-select').removeClass("show-div");
    $('.' + $(this).val()).addClass("show-div");
    if ($("option#custom-range-select-popup:selected").length) {
        ///  $('#shipping-popup .hide-when-custom-range-select').hide();

    } else {
        ////  $('#shipping-popup .hide-when-custom-range-select').show();
    }
});

//
$('.shipping-section-container input.parent-radio-buttons').click(function () {
    if ($(this).attr('id') === 'custom-shipping') {

        $('.showon-customshipping-select').css("display", "flex");

    } else {
        // $('#show-me').hide();   
        $('.showon-customshipping-select').css("display", "none");
    }

});

if ($('input#custom-shipping').is(':checked')) {
    $('.shipping-section-container .showon-customshipping-select').css("display", "flex");
}






$('.shipping-section-container input.primary-radio').click(function () {
    $('.clculateRecommeded-div').removeClass("show-div");
    $('.fixed-shipping-cost').removeClass("show-div");
    $('#calculate-shipping-option').prop('selectedIndex', 0);
});

jQuery('body').on('click', '.this-hide-all', function (e) {

    //                        $(".hidden-after-link-select").hide();
    $(".anchor-link").hide();
    $(".hidden-first").show();
    $(".remove-added-list").show();
    $("#internationalHandlingFee").val('');
    $(".visible-hidden-hide").css("visibility", "visible");


});
jQuery('body').on('click', '.remove-added-list', function (e) {
    //                        $(".hidden-after-link-select").show();
    $(".anchor-link").show();
    $(".hidden-first").hide();
    $(".remove-added-list").hide();
    $("#international_handling_fee").val($("#domestic_handling_fee").val());

    $(".visible-hidden-hide").css("visibility", "hidden");
});

jQuery('body').on('change', '#calculate-shipping-option', function (e) {

    $('.show-on-cost-manually').removeClass("show-div");
    $('.clculateRecommeded-div').removeClass("show-div");
    $('.' + $(this).val()).addClass("show-div");
});


jQuery('body').on('click', '#add-upgrade-shipping,#add-upgrade-shipping-pop', function (event) {

    var randomId = randomNumberFromRange(898, 56565656);
    $html = '';
    $html += '<div class="row entry-body upgrade-shipping"><div class="col-sm-3">';
    $html += '<label class="input-prepend has-validation"><input type="text" class="input form-control" name="shippingUpgrades[upgrapeName][]"  value="" placeholder="Upgrade name"></label></div>';
    $html += '<div class="col-sm-3"><div class="upgrade-type common-mbt-stle">';
    $html += '<div class="form-group"><select  class="form-control" name="shippingUpgrades[level][]"><option value="domestic">Domestic</option><option value="international">International</option></select>';
    $html += '</div></div></div><div class="col-sm-3"><div class="secondary-cost common-mbt-stle">';
    $html += '<label class="input-prepend has-validation"><div class="input-prepend-item"> &#36;</div>';
    $html += '<input type="text" class="input form-control" name="shippingUpgrades[oneItem][]" id="appendPrice_' + randomId + '" value="" onkeypress="Numvalidate(event, \'price\', \'appendPrice_' + randomId + ')" placeholder="0.00"></label></div></div>';
    $html += '<div class="col-sm-2"><div class="secondary-cost common-mbt-stle"><label class="input-prepend has-validation">';
    $html += '<div class="input-prepend-item"> &#36;</div><input type="text"  name="shippingUpgrades[eachItem][]" class="input form-control" value="" placeholder=""></label></div></div>';
    $html += '<div class="col-sm-1 text-center"><a href="javascript:;" class="align-with-btn text-gray-lighter cross-icon-cont remove-container">';
    $html += '<span class="ss-etsy ss-delete icon-smaller icon-t-2"><i class="fa fa-times" aria-hidden="true"></i></span></a></div></div>';
    $(event.target).closest('.fixed-shipping-cost').find('.add-shipping-upgrade-container').append($html);
});

$(document).on('click', 'a.remove-container', function (e) {
    e.preventDefault();
    $(this).parent().parent().remove();
    ///// console.log($(this).parent().parent().find('.popupCountriesMange').text());
    /// console.log($(this).parent().parent().find(".popupCountriesMange option:selected").text());
    if (typeof value === "undefined") {
        $counter = $(this).parent().parent().find(".popupCountriesMange option:selected").val();
        countriesObj[$counter] = $(this).parent().parent().find(".popupCountriesMange option:selected").text();

    }
});

$(document).on('click', 'a.remove-containertabs', function (e) {
    e.preventDefault();
    $(this).parent().parent().parent().remove();

});
$('.list-option-mbt  #processing-time-perioud').change(function () {
    $('.list-option-mbt .show-on-custom-range-select').removeClass("show-div");
    $('.' + $(this).val()).addClass("show-div");
    if ($("option#custom-range-select:selected").length) {
        $('.list-option-mbt  .hide-when-custom-range-select').hide();
    } else {
        $('.list-option-mbt  .hide-when-custom-range-select').show();
    }
});


jQuery('body').on('change', 'input[name=productTags]', function (e) {
    $('body').find('.bootstrap-tagsinput input').css('border-color', '#ced4da');

});
jQuery(function ($) {
    var $active = $('#accordion .panel-collapse.show').prev().addClass('active');
    $active.find('a').append('<span class="glyphicon glyphicon-minus pull-right"></span>');
    $('#accordion .panel-heading').not($active).find('a').prepend('<span class="glyphicon glyphicon-plus pull-right"></span>');
    $('#accordion').on('show.bs.collapse', function (e) {
        $('#accordion .panel-heading.active').removeClass('active').find('.glyphicon').toggleClass('glyphicon-plus glyphicon-minus');
        $(e.target).prev().addClass('active').find('.glyphicon').toggleClass('glyphicon-plus glyphicon-minus');
    });
    $('#accordion').on('hide.bs.collapse', function (e) {
        $(e.target).prev().removeClass('active').find('.glyphicon').removeClass('glyphicon-minus').addClass('glyphicon-plus');
    });
});
$('.product-values-add-product .values-selector input:checked').each(function () {
    alert($(this).val());
});

$(document).on("click", "#btnvariationcheck", function (e) {
    // alert(12);
    var arr = [];
    var arr2 = [];
    var arr3 = [];
    var priceArr = [];
    var SkueArr = [];
    var StockArr = [];
    var main_array = [];
    var variation_images = [];
    var edit_variation_images = [];
    var attribute1 = $('#varidationOptionValues').find('input[type=text]');
    // console.log(attribute1);
    // debugger;
    var attribute2 = $('#varidationOptionValues2').find('input[type=text]');
    var attribute3 = $('#varidationOptionValuesthree').find('input[type=text]');
    var variablePrice = $('.vrpris');
    var variationImage= $('.product_variation_image');
    var editVariationImage= $('.product_variation_image_new');

    var variableSku = $('.vrsku');
    var variableStock = $('.vrstk');
    if (variablePrice.length > 0) {
        variablePrice.each(function () {
            priceArr.push($(this).val());
        });
    }
    if (variationImage.length > 0) {
        variationImage.each(function () {
            variation_images.push($(this).val());
        });
    }
    if (editVariationImage.length > 0) {
        editVariationImage.each(function () {
            edit_variation_images.push($(this).val());
        });
    }
    // if (variableSku.length > 0) {
    //     variableSku.each(function () {
    //         SkueArr.push($(this).val());
    //     });
    // }
    if (variableStock.length > 0) {
        variableStock.each(function () {
            StockArr.push($(this).val());
        });
    }
// console.log(attribute1.length)
// debugger;
    if (attribute1.length > 0) {
        attribute1.each(function () {
            if (isEmptyOrSpaces($(this).val()) == false && $(this).val() !== undefined) { // Check if value is not empty or undefined
                arr.push($(this).val().trim()); // Push the value to the array
            }
        });
        main_array.push(arr);
    }

    if (attribute2.length > 0) {
        // attribute2.each(function () {
        //     arr2.push($(this).val());
        // });
        attribute2.each(function () {
            if (isEmptyOrSpaces($(this).val()) == false && $(this).val() !== undefined) { // Check if value is not empty or undefined
                arr2.push($(this).val().trim()); // Push the value to the array
            }
        });
        main_array.push(arr2);
    }
    if (attribute3.length > 0) {
        // attribute3.each(function () {
        //     arr3.push($(this).val());
        // });
        attribute3.each(function () {
            if (isEmptyOrSpaces($(this).val()) == false && $(this).val() !== undefined) { // Check if value is not empty or undefined
                arr3.push($(this).val().trim()); // Push the value to the array
            }
        });
        main_array.push(arr3);
    }
    var data = {};
    // console.log(main_array);
    // debugger;
    data['variation_options'] = main_array;
    data['variation_price'] = priceArr;
    data['variation_sku'] = SkueArr;
    data['variation_stock'] = StockArr;
    data['variation_images'] = variation_images;
    data['edit_variation_images'] = edit_variation_images;
    if ($('#variationAttributetwo').val() !== "" && attribute2.length > 0) {
        data['variationAttributetwo'] = $('#variationAttributetwo').val();
        data['variationAttributeUnittwo'] = $('#variationAttributeUnittwo').val();
    }
    if ($('#variationAttributeone').val() !== "" && attribute1.length > 0) {
        data['variationAttributeone'] = $('#variationAttributeone').val();
        data['variationAttributeUnitone'] = $('#variationAttributeUnitone').val();

    }
    if ($('#variationAttributethree').val() !== "" && attribute3.length > 0) {
        data['variationAttributethree'] = $('#variationAttributethree').val();
        data['variationAttributeUnitthree'] = $('#variationAttributeUnitthree').val();
    }
    var productId = $("#edit_product_id").val();
    if (productId != '') {
        data['product_id'] = $('#edit_product_id').val();
    }
    
    sendreq(data, 'variation', 'add', 'callBackVariationSuccsess');
});
function isEmptyOrSpaces(str) {
    return /^[\s\xa0]*$/.test(str);
}
function callBackVariationSuccsess(data) {
    $('#productVariationTable').empty();
    $('#productVariationTable').append(data);

    $('.btn_parentVariation').addClass("active-edit-option");
    //return false;
}
$(document).on("click", ".variation-visible", function () {
    if ($(this).prop('checked') == true) {
        aa = $(this).closest('.variation_tr');
        bb = aa.find('input[type=text]').addClass('required-field');
    } else {
        aa = $(this).closest('.variation_tr');
        bb = aa.find('input[type=text]').removeClass('required-field');
        bb.css('border-color', '');
    }

});
$(document).on("click", ".remove-variation", function () {
    $("#editVariationBtn").click();
});
function isNumber(evt) {
    var iKeyCode = (evt.which) ? evt.which : evt.keyCode;
    if (iKeyCode != 46 && iKeyCode > 31 && (iKeyCode < 48 || iKeyCode > 57)) {
        return false;
    }
    return true;
}
//$(document).on("keyUp", "input[name=productTags]", function () {
//    alert("called");
//});


jQuery(document).on('change', '#select-shipoption', function () {
    if ($("option#other-shipping-po:selected").length) {
        $('#save_shipping_custom .shipping-services').css("display", "none");
        $('#save_shipping_custom .fixed-shipping-cost-mbtt').css("display", "flex");

    } else if ($("option#editfree-shipping:selected").length) {

        $('.shipping-services').css("display", "none");

        $('.fixed-shipping-cost-mbtt').css("display", "none");
        // $('#shipping-popup .fixed-shipping-cost-mbtt').css("display", "none");

    } else {
        $('#save_shipping_custom .shipping-services').css("display", "flex");
        $('#save_shipping_custom .fixed-shipping-cost-mbtt').css("display", "none");
    }
    
});


jQuery(document).on('change', '.slctshipoption', function () {
    
    $('#store_shiping_method').val($(this).val());
    if ($("option#editother-shipping-po:selected").length) {
        $('#shipping-popup .shipping-services').css("display", "none");
        $('#shipping-popup .fixed-shipping-cost-mbtt').css("display", "flex");
        $(".editshipservice").show();

    } else if ($("option#editfree-shipping:selected").length) {

        $('.shipping-services').css("display", "none");

        $('#shipping-popup .fixed-shipping-cost-mbtt').css("display", "none");

    }

    else {
        $('#shipping-popup .shipping-services').css("display", "flex");
        $('#shipping-popup .fixed-shipping-cost-mbtt').css("display", "none");
    }
});
function validateYouTubeUrl(url) {
    if (url != undefined || url != '') {
        var regExp = /^.*(youtu.be\/|v\/|u\/\w\/|embed\/|watch\?v=|\&v=|\?v=)([^#\&\?]*).*/;
        var match = url.match(regExp);
        if (match && match[2].length == 11) {
            return true;
        } else {
            return false;
            // Do anything for not being valid
        }
    }
}

//video upload
jQuery('body').on('click', '#viewVideoBtn', function (event) {
    var error = false;

    var mediaURL = $('#media_video_url').val();
    if (mediaURL == "") {
        $('#media_video_url').css('border-color', 'red');
        error = true;
        event.preventDefault();
    }
    else if (error == false && validateYouTubeUrl(mediaURL) == true) {
        $('body').find('.photoSection .uploadImages').delay(1000).show();
        $.ajax({
            url: "/videothumb",
            method: 'POST',
            data: { url: mediaURL },
            success: function (response) {
                if (response) {
                    $('#productVideoURL').val(mediaURL);
                    $('.selectVideo').hide();
                    $('.uploadVideoResponse').show();
                    $('.uploadVideoResponse').empty();
                    $('.uploadVideoResponse').append(response);
                    $("a[rel^='prettyPhoto']").prettyPhoto({
                        social_tools: ''
                    });
                    setTimeout(function () {
                        $('body').find('.photoSection .uploadImages').delay(1000).hide();
                    }, 1000);
                    $('#video-popup').modal('hide');
                    $(".error").html("");
                }
            },
            error: function () {
                swal('Something Wrong Here', 'error');
                $('body').find('.photoSection .uploadImages').delay(1000).hide();
            }

        });
    } else {
        $(".error").html("please enter a valid URL.");
    }


});
jQuery('body').on('click', '.add-media-cancel', function (e) {
    $('.uploadVideoResponse').empty();
    $('.selectVideo').show();
    $('.uploadVideoResponse').hide();
    $('#productVideoURL').val('');
    $('#media_video_url').empty();
});
var delFileArray = [];
jQuery(document).on('click', '.product_file_delete', function () {
    // alert('ddd');
    jQuery(this).closest('.uploaded-images-section').remove();
    var currentId = jQuery(this).attr('data-file-ids');
    delFileArray.push(currentId);
    const str = delFileArray.join(',');
    console.log('str', str);
    jQuery('#delete_file_ids').val(str);
});
$("#product_files").change(function () {
    var selectedFile = $(this).val();
    if (selectedFile) {
        var fileExtension = ['jpeg', 'jpg', 'png', 'gif', 'pdf'];
        var extension = selectedFile.split('.').pop().toLowerCase();
        if ($.inArray(extension, fileExtension) == -1) {
            swal("Only formats are allowed : " + fileExtension.join(', '));
            $("#product_files").val('');
            return false;
        }
    }
});

$(document).on('change', '.dwtype', function () {
    $('#productSize_length').val('');
    $('#productSize_width').val('');
    $('#productWeight').val('');
    $('#productSize_height').val('');
});


