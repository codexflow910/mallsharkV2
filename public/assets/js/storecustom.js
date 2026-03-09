

jQuery('body').on('click', '#showAvatar', function () {
    $("#user-profile-popup").modal('show');
});
function cropper_store(imgSrc) {
    var thumbnailHtml;

    thumbnailHtml = '<div class="modal-dialog animated bounceInDown logocropper" style="max-width:800px"> <div class="modal-content"> ';
    thumbnailHtml += '<div class="modal-body"> <div class="row imagecrop-area"> <div class="col-xs-12 col-sm-12"> <div class="img-container" style="margin-bottom: 0px!important;"> ';
    thumbnailHtml += ' <img class="Cropperimage storecropperimage" src="' + imgSrc + '" alt="Picture"> </div>';
    thumbnailHtml += '<div class="clearfix"></div></div><div class="clearfix spacer20"></div><div class="col-md-12 docs-toggles"> ';
    thumbnailHtml += '</div></div></div>';
    thumbnailHtml += '<div class="btn-group btn-group-justified" data-toggle="buttons">';
    thumbnailHtml += '<div class="modal-footer" style="padding:10px!important;"> <button data-dismiss="modal" class="btn btn-primary btn-simple" type="button">Close</button> <button class="btn-primary btn btn-success getCropImage_store btn-primary-background text-white" type="button" data-dismiss="modal">Save changes</button> </div></div></div>';
    $('#user-profile-popup').append(thumbnailHtml);
}
// function readFile_store(input) {
//     var req_width = 350, req_height = 350; 
//     $(".upload_image_error").html("");
//     $('#user-profile-popup').empty();
//     var file = input.files[0];
//     var filename = $('#file_upload').val();
//     var ext = filename.split('.').pop();
//     var allowed_extensions = ['png', 'PNG', 'jpg', 'JPG', 'JPEG', 'jpeg'];
//     if ($.inArray(ext, allowed_extensions) == -1) {
//         $('#file_upload_store').append("<div class='upload_image_error'>Invalid Image type.</div>");
//         swal('Please select only .jpg, .jpeg, or .png image files.');
//         return false;
//     }
//     console.log(file);
//     if (file) {
//         var vol_size = file.size;
//         if (vol_size <= 0) {
//             vol_size = 1;
//         }
//         vol_size = Math.floor(vol_size / 1024); // Using Math.floor to round down
//         console.log("vol_size " + vol_size);
//         if (vol_size < 20481) {
//             console.log("called");
//             var reader = new FileReader();

//             reader.onloadend = function (e) {
//                 var image = new Image();
//                 image.src = e.target.result;
//                 image.onload = function () {
//                     var dWidth = this.naturalWidth,
//                         dHeight = this.naturalHeight;

//                     if (dWidth >= req_width && dHeight >= req_height) {
//                         // Process the image because dimensions are valid
//                         cropper_store(e.target.result);
//                         var options = {
//                             aspectRatio: 35 / 35,
//                             crop: function (e) {
//                                 // Your crop function logic here
//                             }
//                         };
//                         $('body').find('.storecropperimage').on({
//                             'build.cropper': function (e) {
//                                 console.log(e.type);
//                             },
//                             'built.cropper': function (e) {
//                                 console.log(e.type);
//                             },
//                             'cropstart.cropper': function (e) {
//                                 console.log(e.type, e.action);
//                             },
//                             'cropmove.cropper': function (e) {
//                                 console.log(e.type, e.action);
//                             },
//                             'cropend.cropper': function (e) {
//                                 console.log(e.type, e.action);
//                             },
//                             'crop.cropper': function (e) {
//                                 console.log(e.type, e.x, e.y, e.width, e.height, e.rotate, e.scaleX, e.scaleY);
//                             },
//                             'zoom.cropper': function (e) {
//                                 e.preventDefault(); // Prevent zoom out
//                                 console.log(e.type, e.ratio);
//                             }
//                         }).cropper(options);
//                         // Rest of your cropper setup

//                         $('#user-profile-popup').modal('show');
//                         setTimeout(function () {
//                             $(window).trigger('resize');
//                             $('body').find(".docs-toggles .btn-primary").trigger("click");
//                         }, 200);
//                     } else {
//                         swal('Your Image size is (' + dWidth + 'px x ' + dHeight + 'px). Please set dimensions to (' + req_width + 'px x ' + req_height + 'px) ');
//                         // Clear the file input field since dimensions are invalid
//                         $('#file_upload').val('');
//                     }
//                 };
//             };
//             reader.readAsDataURL(file);
//         } else {
//             alert('Your Image size is ' + vol_size + 'kb. Max allowed size is 20MB.');
//         }
//     } else {
//         swal("Sorry - your browser doesn't support the FileReader API");
//     }
// }


function readFile_store_banner(input) {
    $(".upload_image_error").html(""); // Clear any previous error messages
    var files = input.files; // Get the list of selected files

    for (var i = 0; i < files.length; i++) {
        var file = files[i];
        var filename = file.name;
        var ext = filename.split('.').pop();
        var allowed_extensions = ['png', 'jpg', 'jpeg'];

        if (!allowed_extensions.includes(ext.toLowerCase())) {
            $('#file_upload_banner').closest(".form-group").append("<div class='upload_image_error'>Invalid Image type.</div>");
            // continue; // Skip to the next file if the current one is invalid
            return;
        }

        var vol_size = file.size;
        if (vol_size <= 0) {
            vol_size = 1;
        }
        vol_size = Math.floor(vol_size / 1024); // Convert to kilobytes

        if (vol_size < 20481) {
            var reader = new FileReader();
            reader.addEventListener("load", function () {
                var image = new Image();
                image.src = reader.result;
                
                image.onload = function () {
                    if (image.width < 1180 || image.height < 350) {
                        alert("Image dimensions must be at least 1180x350 pixels.");
                        return; // Skip uploading this image
                    }
                    
                    // Image dimensions are valid, proceed with displaying and cropping
                    var imageElement = document.createElement("img");
                    imageElement.src = reader.result;
                    $('#user-profile-popup').append(imageElement);

                    var cropperImage = $(imageElement).cropper({
                        aspectRatio: 1180 / 350,
                        viewMode: 2,
                    });

                    $('#cropButton').on('click', function () {
                        var croppedCanvas = cropperImage.cropper('getCroppedCanvas');
                        var croppedImage = croppedCanvas.toDataURL();

                        // Display cropped image, you can replace this with your own logic
                        var croppedImageElement = document.createElement("img");
                        croppedImageElement.src = croppedImage;
                        $('#cropped-image-container').html(croppedImageElement);
                    });

                    $('#user-profile-popup').modal('show');

                    setTimeout(function () {
                        $(window).trigger('resize');
                        $(".docs-toggles .btn-primary").trigger("click");
                    }, 200);
                };
            }, false);

            reader.readAsDataURL(file); // Read the uploaded file as data URL
        } else {
            swal('Your Image size is ' + vol_size + 'kb. Max allowed size is 20MB.');
        }
    }
}


$(document).on('click', '#banner_avatar_image', function () {
    $("#upload-logo").click();
});


jQuery('body').on('click', '.getCropImage_store', function (e) {
    
    $(".uploadImages_store").show();
    var mainImageDiv = $('body');
    var cropcanvas = mainImageDiv.find('.storecropperimage').cropper('getCroppedCanvas', {
        width: 217,
        height: 146,
    });
    $('body').find('.photoSection .uploadImages_store').delay(1000).show();
    var croppng = cropcanvas.toDataURL("image/png");
    // mainImageDiv.find('.previewClass').attr('src', croppng);
    // mainImageDiv.find('.viewImages').attr('data-featherlight', croppng);
    var formData = new FormData();
    formData.append('ImageData', croppng);
    $.ajax({
        url: "/store-logo",
        method: 'POST',
        processData: false,
        contentType: false,
        data: formData,
        success: function (obj) {
            
            console.log(obj);
            const res = JSON.parse(obj);
            console.log(res.id);

            $("#store_image_id").val(res.id);
            //     $(".uploadImages").show();
            $('.store-avatar').attr('src', res.image).css("display", "block");

            $(".input-container-div").hide();
            $(".fa-user").hide();
            setTimeout(function () {
                $(".uploadImages").hide();
            }, 1000);

            $("#user-profile-popup").modal('hide');
        },
        error: function () {
            $("#user-profile-popup").modal('hide');
            alert("sorry something went wrong");
            setTimeout(function () {
                $(".uploadImages").hide();
            }, 1000);

        }

    });
});

function readURL(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function (e) {
            $('#blah').attr('src', e.target.result);
        }
        reader.readAsDataURL(input.files[0]);
        $("#blah").show();
    }
}

$(document).on("click", '.fa-user', function () {
    $("#file_upload").click();
});
$(document).on("click", '.avatar', function () {
    $("#file_upload").click();
});
$(document).ready(function () {
    $("#store_country").on("change", function () {
        countryId = $(this).val();
        if (countryId.length == 0) {
            $(".hide-city").show();
        }
        data = {};
        var countryCode = $(this).val();
        data['country_id'] = countryCode;
        sendreq(data, 'vendor', 'state', 'getStates');
    });
});


function randomNumberFromRange(min, max) {
    return Math.floor(Math.random() * (max - min + 1) + min);
}

function showUploadedBanners(source) {
    var imagDiv = document.getElementById('all-uplpad-banners-image');
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


        randomCode = randomNumberFromRange(101, 99999999999);
        html += '<div id="img_' + randomCode + '" class="image-panel image-banners store-image-banner-edit' + validateClass + '">';
        html += '<div class="image-inner-mbt"><div class="img-only">';
        html += '<a class="viewImages" rel="prettyPhoto" href="/uploads/temp/' + source.fileName[i_inner] + '">';
        html += '<img  class="previewClass" src= "/uploads/temp/' + source.fileName[i_inner] + '" />';
        html += '</a>';
        html += '</div><div class="loading-image"><span></span></div> ';
        html += '<input id="imagesrc' + randomCode + '" type="hidden" value= "' + source.fileName[i_inner] + '" name="storeBannerImage[]"/> ';
        html += '<div class="dele-icn">';
       
        html += '<a class="editEvent" href="javascript:;"  data-toggle="modal" data-target="#cropper_' + randomCode + '" >';
        html += '<i class="fa fa-crop" aria-hidden="true"></i></a>&nbsp;&nbsp;';
        html += '<a href="javascript:;" class="dete-parent" onclick="deleteImage(' + randomCode + ')">';
        html += '<i class="fa fa-trash-o" aria-hidden="true"></i></a>';
        html += '</div><div class="modal" id="cropper_' + randomCode + '" tabindex="-1" role="dialog" aria-hidden="true" >';
        html += '<div class="modal-dialog animated bounceInDown" style="max-width:800px"> <div class="modal-content"> ';
        html += '<div class="modal-body"> <div class="row imagecrop-area"> <div class="col-xs-12 col-sm-12"> <div class="img-container" style="margin-bottom:0px!important;"> ';
        html += ' <img class="Cropperimage" src="/uploads/temp/' + source.fileName[i_inner] + '" alt="Picture"> </div>';
        html += '<div class="clearfix"></div></div><div class="clearfix spacer20"></div><div class="col-md-12 docs-toggles"> ';
        html += '</div></div></div>';
        html += '<div class="modal-footer" style="padding:10px!important;"> <button data-dismiss="modal" class="btn btn-primary btn-simple" type="button">Close</button> <button class="btn-primary btn  text-white btn-success getCropImage" type="button" data-dismiss="modal">Save</button> </div></div></div></div></div></div> </div>';

    }
    $('#all-uplpad-banners-image').append(html);
    $("a[rel^='prettyPhoto']").prettyPhoto({
        
    });

}

var uploadingImages = [];

function readURLBanners(input) {
    
    var req_width = 1180,
        req_height = 350;
    var i_inner = 0,
        len = input.files.length,
        reader, file;
    var totalImages = jQuery('.store-image-banner-edit').length;
    
    if(totalImages > 5){
        
        toastr.options = {
            closeButton: true,
            tapToDismiss: true,
            positionClass: 'toast-top-right',
            toastClass: 'toast-icon custom-toast-success',
            progressBar: true,
        };
        toastr.error('You can upload a maximum of 6 images.');
        return;
    }
    
    var imgCounter = totalImages;
    var formdata = new FormData();

    function processNextImage() {
        if (i_inner < len) {
            $('body').find('.photoSection .uploadImages').delay(1000).show();

            file = input.files[i_inner];
            var vol_size = Math.max(file.size, 1);
            vol_size = Math.floor(vol_size / 1024);


            if (vol_size < 11000) {
                if (!!file.type.match(/image\/(png|jpe?g)/)) {
                    reader = new FileReader();
                    reader.onloadend = function (e) {
                        var image = new Image();
                        image.src = e.target.result;
                        image.onload = function () {
                            var dWidth = this.naturalWidth,
                                dHeight = this.naturalHeight;

                            if (dWidth >= req_width && dHeight >= req_height) {
                                uploadingImages.push(e.target.result);
                                i_inner++;
                                formdata.append("file_upload_banner[]", file);
                                processNextImage(); // Process next image
                            } else {
                                swal('Your Image size is (' + dWidth + 'w x ' + dHeight + 'h). Please set dimensions to (' + req_width + 'w x ' + req_height + 'h) pixel ');
                                $('body').find('.photoSection .uploadImages').delay(1000).hide();
                            }
                        };
                    };
                    reader.readAsDataURL(file);
                } else {
                    swal('Please select only .jpg, .jpeg, or .png image files.');
                    $('body').find('.photoSection .uploadImages').delay(1000).hide();
                    $('#banner_image_store').append("<div class='upload_image_error'>Invalid Image type.</div>");
                    i_inner++;
                    processNextImage(); // Process next image
                }
            } else {
                $('body').find('.photoSection .uploadImages').delay(1000).hide();
                swal('Your Image size is ' + vol_size/1000 + 'mb. Max allowed size is 11MB.');
                i_inner++;
                processNextImage(); // Process next image
            }
        } else {
            // All images processed, proceed with sending AJAX request if needed
            var store_id = $('#store_id').val();
            if (formdata.has("file_upload_banner[]")) {
                $('#loading').show();
                $.ajax({
                    url: "/updateStorebannersImageRequest",
                    method: 'POST',
                    data: formdata,
                    processData: false,
                    contentType: false,
                    success: function (response) {
                        $('#loading').hide();
                        if (response) {
                            showUploadedBanners(response);
                        } else {
                            toastr.options = {
                                closeButton: true,
                                tapToDismiss: true,
                                positionClass: 'toast-top-right',
                                toastClass: 'toast-icon custom-toast-success',
                                progressBar: true,
                            };
                            toastr.error('You can upload a maximum of 6 images.');
                        }
                    },
                    error: function () {
                        alert('Something went wrong');
                        $('body').find('.photoSection .uploadImages').delay(1000).hide();
                    }
                });
            }

            if (document.getElementById("file_upload_banner").files.length > 0) {
                jQuery('#file_upload_banner').val('');
            }
        }
    }

    processNextImage(); // Start processing images
}

jQuery('body').on('click', '.editEvent', function (e) {
   
    var mainImageDiv = $(this).parentsUntil('.image-panel');
    var options = {
        aspectRatio: 118 / 35,
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
// exist image croper uploaded
jQuery('body').on('click', '.editEventCrop', function (e) {
    
    setTimeout(() => {
        $(".modal-backdrop").hide();
    }, 500);

    var cropimage = $(this).parentsUntil('.image-wrapper');
    var options = {
        aspectRatio: 118 / 35,
        crop: function (e) {

        }
    };
    // Cropper
    cropimage.find('.Cropperimage').on({
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
    // $(".modal-backdrop").model('hide');
    // $(".modal-backdrop").hide();
    // Options
    cropimage.find('.docs-toggles').on('change', 'input', function () {
        var $this = $(this);
        var name = $this.attr('name');
        var type = $this.prop('type');
        var cropBoxData;
        var canvasData;
        if (!cropimage.find('.Cropperimage').data('cropper')) {
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
        cropimage.find('.Cropperimage').cropper('destroy').cropper(options);
    });
    setTimeout(function () {
        cropimage.find(".docs-toggles .btn-primary").trigger("click");
    }, 1);
});

    // Croper Image uplaoded on server request 
jQuery('body').on('click', '.getCropImageExist', function (e) {
    
    
    // $(".modal-backdrop").model('hide');
    var image_id = $(this).attr('data-image-id');
    var mainImageDiv = $(this).parents('.store-image-banner-edit');
    let noman = mainImageDiv.attr('data-noman');
    
    var cropcanvas = mainImageDiv.find('.Cropperimage').cropper('getCroppedCanvas', {});

    var croppng = cropcanvas.toDataURL("image/png");
    mainImageDiv.find('.previewClass').attr('src', croppng);
    mainImageDiv.find('.viewImages').attr('data-featherlight', croppng);

    var formData = new FormData();
    formData.append('croppedImage', croppng);
    formData.append('image_id', image_id); // Append the store_id to the formData
    
    $.ajax({
        url: "/bannerCropeduploadImage",
        method: 'POST',
        processData: false,
        contentType: false,
        data: formData,
        success: function (res) {
            
            $('#imagesrc-'+image_id).val(res);
            mainImageDiv.find('.viewImages').attr('href', '/uploads/temp/' + res);
            mainImageDiv.find('.previewClass').attr('src', '/uploads/temp/' + res);
            console.log('Upload success');
        },
        error: function () {
            console.log('Upload error');
        }
    });
});

jQuery('body').on('click', '.getCropImage', function (e) {
    var image_id = $(this).attr('data-image-id');
    var mainImageDiv = $(this).parentsUntil('.image-panel');
    var cropcanvas = mainImageDiv.find('.Cropperimage').cropper('getCroppedCanvas', {});

    var croppng = cropcanvas.toDataURL("image/png");
    mainImageDiv.find('.previewClass').attr('src', croppng);
    mainImageDiv.find('.viewImages').attr('data-featherlight', croppng);

    var formData = new FormData();
    formData.append('croppedImage', croppng);
    formData.append('image_id', image_id); // Append the store_id to the formData

    $.ajax({
        url: "/bannerCropeduploadImage",
        method: 'POST',
        processData: false,
        contentType: false,
        data: formData,
        success: function (res) {
            mainImageDiv.find('input[type=hidden]').val(res);
            mainImageDiv.find('.viewImages').attr('data-featherlight', '/uploads/temp/' + res);
            console.log('Upload success');
        },
        error: function () {
            console.log('Upload error');
        }
    });
});


$(document).on('click', '.delete-banner-image', function() {
    
    var removesection = $(this).closest('.storeBannerDelete');
    var removesectionsecond = $(this).closest('.image-wrapper');

    let imageID = $(this).data('image-id');
    let filename = $(this).data('filename');
    let storeid = $(this).data('store-id');

    $.ajax({
        url: '/deleteBanner', // Replace with the actual URL for the deleteBanner endpoint
        type: 'POST',
        data: {
            imageID: imageID,
            filename: filename,
            storeid: storeid
        }, // Pass the image ID as a data parameter

        success: function(response) {

            toastr.options = {
                closeButton: true,
                tapToDismiss: true,
                positionClass: 'toast-top-right',
                toastClass: 'toast-icon custom-toast-success',
                progressBar: true,
            };
            toastr.success('Banner Image deleted');

            if (removesection.length > 0) {
                removesection.remove();
            } else if (removesectionsecond.length > 0) {
                removesectionsecond.remove();
            } else {
               
            }

        },
        error: function(xhr, status, error) {
            // Handle the error if the request fails
            console.log(error);
        }
    });
});

//update banner 
jQuery(document).on("change", '[id^="update_upload_banner-"]', function() {
    // Get reference of FileUpload.
    
    var bannerImageId = $(this).data('image-id');
    var filename = $(this).data('filename');
    var storeid = $(this).data('storeid');
    console.log("bannerImageId", bannerImageId);
    console.log("imageID", imageID);
    if (bannerImageId === imageID) {
        var fileUpload = $(this)[0];
        var fieldid = jQuery(this).attr("id");
        fieldid = "#" + fieldid;
        console.log("fieldid", fieldid);


        // Check whether the file is a valid image.
        var regex = new RegExp("([a-zA-Z0-9\s_\\.\-:])+(.jpg|.png|.jpeg)$");
        if (regex.test(fileUpload.value.toLowerCase())) {
            // Check whether HTML5 is supported.
            if (typeof(fileUpload.files) != "undefined") {
                // Initiate the FileReader object.
                var reader = new FileReader();
                // Read the contents of the Image File.
                reader.readAsDataURL(fileUpload.files[0]);
                reader.onload = function(e) {
                    // Initiate the JavaScript Image object.
                    var image = new Image();
                    // Set the Base64 string returned from FileReader as the source.
                    image.src = e.target.result;
                    image.onload = function() {
                        // Determine the Height and Width.
                        var height = this.height;
                        var width = this.width;
                        if (height < 350) {
                            $('#updateImage-' + bannerImageId).modal('hide');
                            swal("Error", "Height must not be less than 350.", "error");
                            jQuery(fieldid).val("");
                            return false;
                        }
                        if (width < 1180) {
                            $('#updateImage-' + bannerImageId).modal('hide');
                            swal("Error", "Width must not be less than 1180.", "error");
                            jQuery(fieldid).val("");
                            return false;
                        }
                        // alert("Uploaded image has a valid Height and Width.");

                        // Make the AJAX request here, inside the validation success block.

                        var formData = new FormData();
                        formData.append('update_upload_banner', fileUpload.files[0]);

                        formData.append('imageID', bannerImageId);
                        formData.append('filename', filename);
                        formData.append('storeid', storeid);
                        $.ajax({
                            url: '/updatebanner', // Replace with the actual URL to the server-side script
                            type: 'POST',
                            data: formData,

                            processData: false,
                            contentType: false,
                            success: function(response) {
                                
                                if (response.status) {

                                    $('.updateImage-'+response.data.edit_id).hide();
                                    $.ajax({
                                        url: '/rendorImagePathBanner/' + response.data.id, // Replace with your route
                                        method: 'GET',
                                        success: function (imageUrl) {

                                        
                                            
                                            randomCode = randomNumberFromRange(101, 99999999999);
                                            // Append each image to the imagePreview container
                                            
                                        var html = '';
                                            html += '<div id="img_' + randomCode + '" class="image-panel storeBannerDelete">';
                                            html += '<div class="image-inner-mbt"><div class="img-only">';
                    
                                            html += '<img  class="previewClass" src= "' + imageUrl + '" />';
                                            html += '</div><div class="loading-image"><span></span></div> ';
                                            html += '<input id="imagesrc' + randomCode + '" type="hidden" value= "' + response.data.id + '" name="storeBannerImage[]"/> ';
                                            html += '<div class="dele-icn">';
                                            html += ' <input type="radio" class="form-check-input select_primary_image" name="optradio" value="">';
                                            html += '<a class="editEvent" href="javascript:;"  data-toggle="modal" data-target="#cropper_' + randomCode + '" >';
                                            html += '<i class="fa fa-crop" aria-hidden="true"></i></a>&nbsp;&nbsp;';
                                            html += '<a href="javascript:;" class="delete-banner-image" data-image-id="'+response.data.id+'" data-store-id="'+store_id+'" data-filename="'+imageUrl+'">';
                                            html += '<i class="fa fa-trash-o" aria-hidden="true"></i></a>';
                                            html += '</div><div class="modal" id="cropper_' + randomCode + '" tabindex="-1" role="dialog" aria-hidden="true" >';
                                            html += '<div class="modal-dialog animated bounceInDown" style="max-width:800px"> <div class="modal-content"> ';
                                            html += '<div class="modal-body"> <div class="row imagecrop-area"> <div class="col-xs-12 col-sm-12"> <div class="img-container"> ';
                                            html += ' <img class="Cropperimage" src="' + imageUrl + '" alt="Picture"> </div>';
                                            html += '<div class="clearfix"></div><br></div><div class="clearfix spacer20"></div><div class="col-md-12 docs-toggles"> ';
                                            html += '</div></div></div>';
                                            html += '<div class="modal-footer"> <button data-dismiss="modal" class="btn btn-primary btn-simple" type="button">Close</button> <button class="btn-primary btn  text-white btn-success getCropImage" data-image-id="'+response.data.id+'" type="button" data-dismiss="modal">Save</button> </div></div></div></div></div></div> </div>';
            
                                            $('#all-uplpad-banners-image').append(html);
                                            
                                            $("a[rel^='prettyPhoto']").prettyPhoto({
                                                
                                            });
                                            
                                        },
                                        error: function (error) {
                                            console.error(error);
                                        }
                                    });
                                    
                                    $('#updateImage-' + bannerImageId).modal('hide');
                                    toastr.options = {
                                        closeButton: true,
                                        tapToDismiss: true,
                                        positionClass: 'toast-top-right',
                                        toastClass: 'toast-icon custom-toast-success',
                                        progressBar: true,
                                    };
                                    toastr.success(response.message);

                                }
                                // Handle the response from the server

                                console.log("response", response);
                                // You can perform further actions here based on the response
                            },
                            error: function(xhr, status, error) {
                                // Handle the error if the request fails
                                console.log(error);
                            }
                        });
                        return true;
                    };
                }
            } else {
                alert("This browser does not support HTML5.");
                return false;
            }
        } else {
            alert("Please select a valid image file.");
            return false;
        }
    }
});
function deleteImage(imageID) {
    var input_field_value = jQuery('#imagesrc' + imageID).val();
    
    if (input_field_value) {

        $('#img_' + imageID).remove();
        $.ajax({
            url: "/deleteProductImage",
            method: 'POST',
            data: { imageName: input_field_value },
            success: function (res) { 
                
            }
        });
    }
}