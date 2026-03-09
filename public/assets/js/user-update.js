jQuery('body').on('click', '#showAvatar', function () {
    $("#user-profile-popup").modal('show');
});
// $(document).on("click", '.fa-user', function () {
//     $("#file_upload_profile").click();
// });
// $(document).on("click", '.avatar', function () {
//     $("#file_upload_profile").click();
// });
$(document).ready(function () {
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
function cropper(imgSrc) {
    var thumbnailHtml;

    thumbnailHtml = '<div class="modal-dialog animated bounceInDown" style="max-width:800px"> <div class="modal-content"> ';
    thumbnailHtml += '<div class="modal-body"> <div class="row imagecrop-area"> <div class="col-xs-12 col-sm-12"> <div class="img-container"> ';
    thumbnailHtml += ' <img class="Cropperimage" src="' + imgSrc + '" alt="Picture"> </div>';
    thumbnailHtml += '<div class="clearfix"></div><br></div><div class="clearfix spacer20"></div><div class="col-md-12 docs-toggles"> ';
    thumbnailHtml += '</div></div></div>';
    thumbnailHtml += '<div class="btn-group btn-group-justified" data-toggle="buttons">';
    thumbnailHtml += '<div class="modal-footer"> <button data-dismiss="modal" class="btn btn-primary btn-simple" type="button">Close</button> <button id="getCropImage_profile" class="btn-primary btn btn-success getCropImage_profile btn-primary-background text-white" type="button" data-dismiss="modal">Save changes</button> </div></div></div>';
    $('#user-profile-popup').append(thumbnailHtml);
}
function cropper_mobile(imgSrc) {
    var thumbnailHtml;

    thumbnailHtml = '<div class="modal-dialog animated bounceInDown" style="max-width:800px"> <div class="modal-content"> ';
    thumbnailHtml += '<div class="modal-body"> <div class="row imagecrop-area"> <div class="col-xs-12 col-sm-12"> <div class="img-container"> ';
    thumbnailHtml += ' <img class="Cropperimage" src="' + imgSrc + '" alt="Picture"> </div>';
    thumbnailHtml += '<div class="clearfix"></div><br></div><div class="clearfix spacer20"></div><div class="col-md-12 docs-toggles"> ';
    thumbnailHtml += '</div></div></div>';
    thumbnailHtml += '<div class="btn-group btn-group-justified" data-toggle="buttons">';
    thumbnailHtml += '<div class="modal-footer"> <button data-dismiss="modal" class="btn btn-primary btn-simple" type="button">Close</button> <button class="btn-primary btn btn-success getCropImage_profile_mobile btn-primary-background text-white" type="button" data-dismiss="modal">Save changes</button> </div></div></div>';
    $('#user-profile-popup_mobile').append(thumbnailHtml);
}
function readFile(input) {
    $(".upload_image_error").html("");
    var file = input.files[0];
    var filename = $('#file_upload_profile').val();
    var ext = filename.split('.').pop();
    var allowed_extensions = ['png', 'PNG', 'jpg', 'JPG', 'JPEG', 'jpeg'];
    if ($.inArray(ext, allowed_extensions) == -1) {
        
        swal("Sorry - Invalid Image type.");
        $('#file_upload_profile').closest(".form-group").append("<div class='upload_image_error'>Invalid Image type.</div>");
        return false;
    }
    console.log(file);
    if (file) {
        var vol_size = file.size;
        if (vol_size <= 0) {
            vol_size = 1;
        }
        vol_size = (vol_size) / 10240;
        vol_size = parseInt(vol_size);
        console.log("vol_size "+vol_size);
        if (vol_size < 20481) {
            console.log("called");
            var reader = new FileReader();
            reader.addEventListener("load", function () {
                $('body').find('#user-profile-popup').html('');
                $('#file_upload_profile').val('');
                cropper(reader.result);
                var options = {
                    aspectRatio: 3 / 3,
                    crop: function (e) {
                    }
                };
                // Cropper
                $('body').find('.Cropperimage').on({
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

                $('#user-profile-popup').modal('show');

                setTimeout(function () {
                    $(window).trigger('resize');

                    $('body').find(".docs-toggles .btn-primary").trigger("click");
                }, 200);
            }, false);
            if (file) {
                reader.readAsDataURL(file);
            }
        } else {
            alert('Your Image size is ' + vol_size + 'kb. Max allowed size is 20MB.');
        }
    } else {
        swal("Sorry - you're browser doesn't support the FileReader API");
    }
}

function readFile_mobile(input) {
    $(".upload_image_error").html("");
    var file_mobile = input.files[0];
    var filename_mobile = $('#file_upload_mobile').val();
    var ext = filename_mobile.split('.').pop();
    var allowed_extensions = ['png', 'PNG', 'jpg', 'JPG', 'JPEG', 'jpeg'];
    if ($.inArray(ext, allowed_extensions) == -1) {
        swal("Sorry - Invalid Image type.");
        $('#file_upload_mobile').closest(".form-group").append("<div class='upload_image_error'>Invalid Image type.</div>");
        return false;
    }
    console.log(file_mobile);
    if (file_mobile) {
        var vol_size = file_mobile.size;
        if (vol_size <= 0) {
            vol_size = 1;
        }
        vol_size = (vol_size) / 10240;
        vol_size = parseInt(vol_size);
        console.log("vol_size "+vol_size);
        if (vol_size < 20481) {
            console.log("called");
            var reader = new FileReader();
            reader.addEventListener("load", function () {
                $('body').find('#user-profile-popup_mobile').html('');
                $('#file_upload_mobile').val('');
                cropper_mobile(reader.result);
                var options = {
                    aspectRatio: 3 / 3,
                    crop: function (e) {
                    }
                };
                // Cropper
                $('body').find('.Cropperimage').on({
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

                $('#user-profile-popup_mobile').modal('show');

                setTimeout(function () {
                    $(window).trigger('resize');

                    $('body').find(".docs-toggles .btn-primary").trigger("click");
                }, 200);
            }, false);
            if (file_mobile) {
                reader.readAsDataURL(file_mobile);
            }
        } else {
            alert('Your Image size is ' + vol_size + 'kb. Max allowed size is 20MB.');
        }
    } else {
        swal("Sorry - you're browser doesn't support the FileReader API");
    }
}
jQuery('body').on('click', '#getCropImage_profile', function (e) {
    
    $(".uploadImages").show();
    var mainImageDiv = $('body');
    var cropcanvas = mainImageDiv.find('.Cropperimage').cropper('getCroppedCanvas', {
        width: 217,
        height: 146,
    });
    $('body').find('.photoSection .uploadImages').delay(1000).show();
    var croppng = cropcanvas.toDataURL("image/png");
    mainImageDiv.find('.previewClass').attr('src', croppng);
    mainImageDiv.find('.viewImages').attr('data-featherlight', croppng);
    var formData = new FormData();
    formData.append('ImageData', croppng);
    $.ajax({
        url: "/user-avatar",
        method: 'POST',
        processData: false,
         contentType: false,
        data: formData,
        success: function(obj) {
            
            ///  console.log(res);
            const res = JSON.parse(obj);
           $("#avatar_image_hidden").val(res.id);
           $('#user-avatar_source').attr('src', res.image).css("display", "block");
            $(".uploadImages").show();
           //  $('.user-avatar').attr('src', res.image).css("display", "block");

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

jQuery('body').on('click', '.getCropImage_profile_mobile', function (e) {
    $(".uploadImages").show();
    var mainImageDiv = $('body');
    var cropcanvas = mainImageDiv.find('.Cropperimage').cropper('getCroppedCanvas', {
        width: 217,
        height: 146,
    });
    $('body').find('.photoSection .uploadImages').delay(1000).show();
    var croppng = cropcanvas.toDataURL("image/png");
    mainImageDiv.find('.previewClass').attr('src', croppng);
    mainImageDiv.find('.viewImages').attr('data-featherlight', croppng);
    var formData = new FormData();
    formData.append('ImageData', croppng);
    $.ajax({
        url: "/user-avatar",
        method: 'POST',
        processData: false,
         contentType: false,
        data: formData,
        success: function(obj) {
            ///  console.log(res);
            const res = JSON.parse(obj);
         console.log(res.id);
           $("#avatar_image_hidden").val(res.id);
            $(".uploadImages").show();
             $('.user-avatar_mobile').attr('src', res.image).css("display", "block");
           //  $('.user-avatar').attr('src', res.image).css("display", "block");

            $(".input-container-div").hide();
            $(".fa-user").hide();
            setTimeout(function () {
                $(".uploadImages").hide();
            }, 1000);

            $("#user-profile-popup_mobile").modal('hide');
        },
        error: function () {
            $("#user-profile-popup_mobile").modal('hide');
            alert("sorry something went wrong");
            setTimeout(function () {
                $(".uploadImages").hide();
            }, 1000);

        }

    });
});

//$(function () {
//    $("#contact_no").mask("(999) 999-9999");
//    $("#contact_no").on("blur", function () {
//        var last = $(this).val().substr($(this).val().indexOf("-") + 1);
//        if (last.length == 5) {
//            var move = $(this).val().substr($(this).val().indexOf("-") + 1, 1);
//            var lastfour = last.substr(1, 4);
//            var first = $(this).val().substr(0, 9);
//            $(this).val(first + move + '-' + lastfour);
//        }
//    });
//});