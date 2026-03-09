@extends('layouts.main')
@section('content')
@section('title', 'User detail')
<main>
    <script src="{{ URL::asset('assets/js/jquery.validate.js',env('HTTPS_STATUS')) }}"></script>
    <link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
    <script src="{{ URL::asset('assets/js/fone-format.js',env('HTTPS_STATUS')) }}"></script>

    @include('admin.adminsidebar')
    <!-- Dashboard Ends -->
    <div class="dshbrdin store-profile-mbt">
        <div class="container">
            <div class="row">
                <div class="col-md-12"> 
                    <div class="dshodrs">
                        @if(Session::has('updatesuccessmessage'))
                        <div class="alert alert-success alert-success-mbt" id="success_message" role="alert">
                            {{ Session::get('updatesuccessmessage') }}
                            <script>
                                setTimeout(function () {
                                    jQuery("#success_message").fadeOut('slow')
                                }, 3000);
                            </script>
                        </div>
                        @endif
                        <!-- Product Sorting Starts -->
                        <div class="row srtbx"><div class="col-md-6"><h3 class=" ">User Profile</h3></div></div>    
                        <!-- Product Sorting Ends--> 

                        <!-- User Image -->

                        <div class="usrimgup">
                            <div class="thumbnail border avatar">
                                <a href="#" style="margin:auto">
                                    <?php
                                    $image_url = '';
                                    if ($userData['user_avatar'] != '') {
                                        $image_url = getAttachmentUrlById((int) $userData['user_avatar'], 'avatar');
                                    }
                                    ?>
                                    <?php if ($image_url) { ?>
                                        <img class="user-avatar" src="<?php echo $image_url; ?>" alt="icon user" title="icon user" style="max-width: 100px;">
                                    <?php } else {
                                        ?>
                                        <i class="fa fa-user" aria-hidden="true"></i>
                                        <img class="user-avatar" src="" style="max-width: 100px;" alt="icon user" title="icon user" style="display: none;">
                                    <?php } ?>
                                    <div class="caption">
<!--                                        <p class="small grey">Drag Your Picture Here</p>-->
                                    </div>
                                </a>
                                <div class="uploadImages"><span></span></div>
                            </div> 
                        </div>
                        <div class="upload-image-file">
<!--                            <img id="banner_avatar_image" style="max-width:272px; max-height:100px; display:none;" alt="Banner" class="img-fluid user-avatar">      -->

                        </div>




                        <!-- User Image Ends -->    
                        <!-- User Form -->
                        <div class="row">
                            <form class="col-md-6 usrbsc" id="user_account" method="Post" action="<?php echo URL::to('/admin/userdetail/' . $userData['user_id']) ?>" enctype="multipart/form-data">

                                <div class="form-group custom-panel-style mb-30 ">
                                    <label class="custom-file-upload mt-20">
                                        Upload Your Picture
                                        <input type="file" name="file_upload" id="file_upload" class="form-control" onchange="readFile(this)">
                                    </label>
                                    <p class="ligt-grey mt-10 mb-0 fnt-12">Note: Only PNG , JPG files to upload, Maximum files sizes 3 mb</p>
                                </div>
                                <div class="form-group">
                                    <label>User Name</label>
                                    <input type="text" class="form-control" id="user_name" maxlength="25" name="user_name" value="<?php echo ($userData['name']) ? $userData['name'] : ""; ?>">
                                    <span class="error">{{ $errors->first('user_name') }}</span>
                                    {{ csrf_field() }}
                                </div>
                                <input type="hidden" name="avatar_image_hidden" id="avatar_image_hidden" value="">
                                <div class="form-group">
                                    <label>Address</label>
                                    <textarea class="form-control" id="physical_address" maxlength="100" name="physical_address"><?php echo ($userData['physical_address']) ? $userData['physical_address'] : ""; ?></textarea>
<!--                                    <input type="text" class="form-control" id="user_address" name="user_address" value="<?php //echo ($userData['user_address']) ? $userData['user_address'] : "";     ?>">-->
                                    <span class="error"></span>

                                </div>

                                <div class="form-group">
                                    <label for="exampleFormControlSelect1">Country</label>
                                    <select class="form-control" name="user_country" id="user_country">
                                    @php
                                        $countries = getAllCountries();
                                    @endphp
                                    @foreach ($countries as $country)
                                                <option value="{{ $country['id'] }}" 
                                                    {{ isset($userData['user_country_id']) && $userData['user_country_id'] == $country['id'] ? 'selected' : '' }}>
                                                    {{ $country['name'] }}
                                                </option>
                                            @endforeach



                                    </select>
                                    <span class="error">{{ $errors->first('user_country') }}</span>
                                </div>

                                <div class="form-group">
                                    <label for="exampleFormContlaserrolSelect1">State/Province</label>
                                    <select class="form-control" name="user_state" id="user_state">


                                        <?php
                                        if (!empty($states)) {
                                            $b = 0;
                                            foreach ($states as $state) {
                                                $selcted = '';
                                                if (isset($userData['user_state_id']) && $state['state_id'] == $userData['user_state_id']) {
                                                    $selcted = 'selected';
                                                }
                                                ?>
                                                <?php
                                                if ($b == 0) {
                                                    ?>
                                                    <option value="">Please select</option>
                                                <?php } ?>
                                                <option value="<?php echo $state['state_id']; ?>" <?php echo $selcted; ?>><?php echo $state['state_name']; ?></option>
                                                <?php
                                                $b = 1;
                                            }
                                        } else {
                                            ?>
                                            <option value="">Please select</option>
                                        <?php } ?>
                                    </select>
                                </div>
                                <div class="form-group hide-city">
                                    <label>City</label>
                                    <input type="text" name="user_city_id" id="user_city" class="form-control" value="<?php echo ($userData['user_city_id']) ? $userData['user_city_id'] : ""; ?>">
                                    <span class="error">{{ $errors->first('user_city_id') }}</span>
                                </div>
                                <div class="form-group">
                                    <label>Zip/Postal Code</label>
                                    <input type="text" name="user_zip_code" id="user_zip_code" class="form-control" maxlength="5" id="user_zip_code" value="<?php echo ($userData['user_zip_code']) ? $userData['user_zip_code'] : ""; ?>">
                                    <span class="error">{{ $errors->first('user_zip_code') }}</span>
                                </div>
                                <div class="form-group">
                                    <label>Mobile</label>
                                    <input type="tel" name="contact_no" class="form-control" id="contact_no" value="<?php echo ($userData['contact_no']) ? $userData['contact_no'] : ""; ?>">
                                    <span class="error">{{ $errors->first('contact_no') }}</span>
                                </div>
                                @php
                                    $roles = getAllRoles(); // returns array of roles
                                    $userRoleIds = isset($userData['roles'])
                                        ? collect($userData['roles'])->pluck('role_id')->toArray()
                                        : [];
                                @endphp

                                <div class="form-group">
                                    <label>Roles</label>
                                    <div class="row">
                                        @foreach($roles as $role)
                                            <div class="col-md-4 mb-2">
                                                <div class="form-check">
                                                    <input 
                                                        type="checkbox"
                                                        name="roles[]"
                                                        value="{{ $role['role_id'] }}"
                                                        id="role-{{ $role['role_id'] }}"
                                                        class="form-check-input"
                                                        {{ in_array($role['role_id'], $userRoleIds) ? 'checked' : '' }}
                                                    >
                                                    <label class="form-check-label" for="role-{{ $role['role_id'] }}">
                                                        {{ $role['role_name'] }}
                                                    </label>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                    <span class="error">{{ $errors->first('roles') }}</span>
                                </div>
                                <div class="form-check">

                                    <p><button type="submit" class="btn btn-primary width70 btn-primary-background text-white">Save</button></p> 
                                </div>
                            </form>
                        </div>
                        <!-- User Form Ends -->     
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
<div id="store-logo-popup" class="modal fade popup-style create-shipping-profile">
    <div class="modal-dialog">

        <!-- Modal content-->
        <div class="modal-content clearfix">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Brand Logo</h4>
            </div>
            <div class="modal-body">
                <div class="input-container-div">
                    <div class="input-label">
                        <label for="image_upload" class="label">Upload image:<br>Allowed image format (<strong>png</strong>)
                        </label>
                    </div>
                    <div class="form-input">
                        <div>
                            <input name="file_name" value="" id="file_upload" class="file_upload form-control" type="file">
                        </div>            
                        <div class="flie_upload_bar_wrapper">
                            <div class="flie_upload_bar">

                            </div>
                            <span style="display:none;"></span>
                        </div>
                        <div class="clearfix"></div>
                        <div class="upload-image-file">
                            <img id="blah" src="#" class="img-fluid" alt="your image" style="display:none;"/>
                            <input name="avatar_image_hidden" value="" id="avatar_image_hidden" type="hidden">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button"  class="btn btn-default btn-primary btn-simple " id="save_logo">Save logo</button>
                </div>
            </div>
            <div class="uploadImages-popup"><span></span></div>

        </div>

    </div>

</div>

<link href="{{ URL::asset('assets/image-cropper/css/cropper.css' ,env('HTTPS_STATUS'))}}" rel="stylesheet" type="text/css" media="screen"/>
<link href="{{ URL::asset('assets/image-cropper/css/main.css' ,env('HTTPS_STATUS'))}}" rel="stylesheet" type="text/css" media="screen"/>
<script src="{{ URL::asset('assets/image-cropper/js/cropper.min.js' ,env('HTTPS_STATUS'))}}" type="text/javascript" charset="utf-8"></script>

<div id="user-profile-popup" class="modal fade popup-style create-shipping-profile" >


</div>
<script>
                                            jQuery('body').on('click', '#showAvatar', function () {
                                                $("#user-profile-popup").modal('show');
                                            });
                                            $(document).ready(function () {
                                                jQuery.validator.addMethod("alphanumeric", function (value, element) {
                                                    return this.optional(element) || /^[a-zA-Z0-9]+$/.test(value);
                                                }, "Only alphanumeric are allowed.");
                                                jQuery.validator.addMethod("lettersonly", function (e, r) {
                                                    return this.optional(r) || /^[a-z ]+$/i.test(e)
                                                }, "Only letters are allowed."),
                                                        jQuery.validator.addMethod("numbersonly", function (e, r) {
                                                            return this.optional(r) || /^[0-9 +]+$/i.test(e)
                                                        }, "Only numbers are allowed."),
                                                        $("#user_account").validate({
                                                    errorElement: "span",
                                                    rules: {
                                                        user_name: {required: true, minlength: 4, lettersonly: !0,
                                                            maxlength: 25},
                                                        user_country: {required: true
                                                        },
                                                        user_state: {},
                                                        physical_address: {required: true, maxlength: 500},
                                                        user_city: {required: true},
                                                        user_zip_code: {required: true, maxlength: 15},
                                                        contact_no: {required: true, maxlength: 15},
                                                        user_avatar: {accept: "jpg,png,jpeg"},
                                                    },
                                                    messages: {
                                                        user_name: {required: "Name is required."},
                                                        user_country: {required: "Country is required."},
                                                        user_city: {required: "City is required."},
                                                        contact_no: {required: "Phone no is required."},
                                                        physical_address: {required: "Address is required."},
                                                        user_zip_code: {required: "Zip code is required."},
                                                        user_avatar: {accept: "please upload valid image."},
                                                        email: {required: "Email  is required.", remote: "<div class='clear'></div><div id='register_user_form'>This username is already registered.</div></div>"},
                                                    },
                                                    errorPlacement: function (error, element) {
                                                        error.appendTo(element.closest('.form-group'));
                                                    },
                                                    submitHandler: function (form) {
                                                        form.submit();
                                                    }
                                                });
                                            });</script>
<script>
    $(document).on("click", '.fa-user', function () {
        $("#file_upload").click();

    });
    $(document).on("click", '.avatar', function () {
        $("#file_upload").click();

    });
    
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
<script>

    function cropper(imgSrc) {
        var thumbnailHtml;

        thumbnailHtml = '<div class="modal-dialog animated bounceInDown" style="max-width:800px"> <div class="modal-content"> ';
        thumbnailHtml += '<div class="modal-body"> <div class="row imagecrop-area"> <div class="col-xs-12 col-sm-12"> <div class="img-container"> ';
        thumbnailHtml += ' <img class="Cropperimage" src="' + imgSrc + '" alt="Picture" title="Picture"> </div>';
        thumbnailHtml += '<div class="clearfix"></div><br></div><div class="clearfix spacer20"></div><div class="col-md-12 docs-toggles"> ';
        thumbnailHtml += '</div></div></div>';
        thumbnailHtml += '<div class="btn-group btn-group-justified" data-toggle="buttons">';
        thumbnailHtml += '<div class="modal-footer"> <button data-dismiss="modal" class="btn btn-primary btn-simple" type="button">Close</button> <button class="btn-primary btn btn-success getCropImage btn-primary-background text-white" type="button" data-dismiss="modal">Save changes</button> </div></div></div>';
        $('#user-profile-popup').append(thumbnailHtml);
    }
    function readFile(input) {
        $(".upload_image_error").html("");
        var file = input.files[0];
        var filename = $('#file_upload').val();
        var ext = filename.split('.').pop();
        var allowed_extensions = ['png', 'PNG', 'jpg', 'JPG', 'JPEG', 'jpeg'];
        if ($.inArray(ext, allowed_extensions) == -1) {
            $('#file_upload').closest(".form-group").append("<div class='upload_image_error'>Invalid Image type.</div>");
            return false;
        }

        if (file) {

            var vol_size = file.size;
            if (vol_size <= 0) {
                vol_size = 1;
            }
            vol_size = (vol_size) / 1024;
            vol_size = parseInt(vol_size);
            if (vol_size < 3072) {
                var reader = new FileReader();
                reader.addEventListener("load", function () {
                    $('body').find('#user-profile-popup').html('');
                    $('#file_upload').val('');

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
                alert('Your Image size is ' + vol_size + 'kb. Max allowed size is 3MB.');
            }
        } else {
            swal("Sorry - you're browser doesn't support the FileReader API");
        }
    }
    jQuery('body').on('click', '.getCropImage', function (e) {
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
            success: function (data) {
                ///  console.log(res);
                $("#avatar_image_hidden").val(data['id']);
                $(".uploadImages").show();
                $('.user-avatar').attr('src', data['image']).css("display", "block");

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
    $(function () {

        $("#contact_no").mask("(999) 999-9999");


        $("#contact_no").on("blur", function () {
            var last = $(this).val().substr($(this).val().indexOf("-") + 1);

            if (last.length == 5) {
                var move = $(this).val().substr($(this).val().indexOf("-") + 1, 1);

                var lastfour = last.substr(1, 4);

                var first = $(this).val().substr(0, 9);

                $(this).val(first + move + '-' + lastfour);
            }
        });
    });

</script>
@endsection