@extends('layouts.dashboard-main')
@section('content')
@section('title', 'Create Post')


<main>
    @include('admin.adminsidebar')

    <style>
        @media only screen and (min-width : 767px) and (max-width : 10000px) {
            .search-icon-header {
                display: none;
            }
        }
    </style>
    <style>
        .tmpbx {
            box-shadow: 0 0 5px 0 #64646459;
            display: inline-block
        }

        .clpgbx {
            background-color: #fff;
            box-shadow: 0 0 5px 0 #64646459;
            width: 100%;
            padding: 30px;
            border-radius: 3px;
            margin-bottom: 10px
        }

        .ovrly {
            background-color: #00a08e;
            position: absolute;
            width: 100%;
        }

        .parent_post_category {
            font-size: 18px;
            font-weight: 400;
        }
    </style>
    <script src="https://cdn.ckeditor.com/4.22.1/standard/ckeditor.js"></script>
    <script>
    CKEDITOR.config.versionCheck = false;
    </script>
    <script src="{{ URL::asset('assets/js/jquery.validate.js',env('HTTPS_STATUS')) }}"></script>
    <script src="{{ URL::asset('assets/js/fone-format.js',env('HTTPS_STATUS')) }}"></script>
    <section>


        <div class="container">
            <div class="row">
                @if (Session::has('message_error'))
                <div class="col-sm-12">
                    <div class="alert alert-danger error_message" >
                        <ul>
                            <li>{!! Session::get('message_error') !!}</li>
                        </ul>
                    <script>
                            setTimeout(function () {
                                jQuery(".error_message").fadeOut('slow');
                            }, 3000);
                    </script>
                    </div>
                </div>
                @endif
                @if (Session::has('message_success'))
                <div class="col-sm-12 success_message" >
                    <div class="alert alert-success">
                        <ul>
                            <li>{!! Session::get('message_success') !!}</li>
                        </ul>
                    <script>
                            setTimeout(function () {
                                jQuery(".success_message").fadeOut('slow');
                            }, 3000);
                    </script>
                    </div>
                </div>
                @endif

                <div class="col-md-10 ">
                    <h3>{{isset($title) ? $title: "Create Post"}}</h3>

                </div>

                <div class="col-md-2">
                    <a class="btn btn-primary btn-simple" href="{{URL::to('/admin/articles')}}">Back</a>
                </div>
            </div>
            <?php
            $actionUrl = isset($actionUrl) ? $actionUrl : '/admin/create/post'
            ?>
            <form action="{{URL::to($actionUrl)}}" method="POST" id="addpost_section">
                <div class="container">
                    <div class="row">
                        <div class="col-md-8 ">
                            @csrf

                            <div class="form-group mb-15">
                                <label>Post Title * </label>
                                <input type="text" class="form-control" name="title" value="{{isset($postData->title) ? $postData->title : ''}}">
                            </div>
                            <div class="form-group mb-15">
                                <label>Descrition *</label>
                                <textarea id='post_content' class="form-control" name="description">{{isset($postData->description) ? $postData->description : ''}}</textarea>
                                <span class="category_description error d-none"> Please enter description</span>
                            </div>
                        </div>
                        <?php
                        // echo 'Data: <pre>' .print_r($postData->categories,true). '</pre>'; die();
                        ?>
                        <div class="col-md-4 ">
                            <div class="form-group">
                                <select class="form-control" name="postCategory" id="postCategory">
                                    <option value="">Please Select Category *</option>


                                    <?php
                                    // $postCatgory = isset($postData->categories) ? $postData->categories : "";
                                    $allCategoreis = getAllPostCategoriesAdmin();
                                    foreach ($allCategoreis as $key => $val) {
                                        $parent_Category_title = isset($val['cat_info']['title']) ? $val['cat_info']['title'] : '';
                                        $parent_Category_slug = isset($val['cat_info']['slug']) ? $val['cat_info']['slug'] : '';
                                        $parent_Category_id = isset($val['cat_info']['id']) ? $val['cat_info']['id'] : '';
                                        $child_count = isset($val['cat_info']['child_count']) ? $val['cat_info']['child_count'] : '';
                                        $selected = '';
                                        if (isset($postData)) {

                                            foreach ($postData->categories as $cat) {
                                                if ($parent_Category_id == $cat->id) {
                                                    $selected = 'selected';
                                                }
                                            }
                                        }
                                    ?>
                                        <option class="parent_post_category" <?php echo $selected; ?> value="{{$parent_Category_id}}">{{ucfirst($parent_Category_title)}}</option>
                                        <?php if (isset($val['childs']) && count($val['childs']) > 0) {
                                            foreach ($val['childs'] as $newVal) {
                                                $selectedchild = '';
                                                if (isset($postData)) {
                                                    foreach ($postData->categories as $cat) {
                                                        if ($newVal['id'] == $cat->id) {
                                                            $selectedchild = 'selected';
                                                        }
                                                    }
                                                }
                                        ?>
                                                <option value="<?php echo isset($newVal['id']) ? $newVal['id'] : ''; ?>" {{$selectedchild}}> -- <?php echo isset($newVal['title']) ? ucfirst($newVal['title']) : ''; ?></option>
                                    <?php }
                                        }
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="post_image">Post Image *</label>
                                <input type="file" id="post_image" class="form-control" onchange="readFile_post(this)" accept=".jpg, .jpeg, .png">
                                <input type="hidden" name="blog_image_id" value="{{isset($postData->blog_image) ? $postData->blog_image : ''}}" id="blog_image_id">

                                @if(isset($postData))
                                <input type="hidden" name="editpost_id" id="editpost_id" value="{{isset($postData->id) ? $postData->id : ''}}">
                                <div class="upload-image-file d-flex justify-content-evenly">
                                    <?php
                                    $imgeUrl = "";
                                    $style = 'none';
                                    if (isset($postData['blog_image']) && $postData['blog_image'] != "") {
                                        $style = 'block';
                                        $imgeUrl = getAttachmentUrlById((int) $postData['blog_image'], 'media');
                                    }
                                    ?>
                                    <img id="post_feature_image" src="<?php echo $imgeUrl; ?>" style="width:280px; height:230px; display:<?php echo $style; ?>;border-radius: 20px;border: 2px solid blue;" alt="<?php echo isset($postData->title) ? $postData->title : '' ?>" title="<?php echo isset($postData->title) ? $postData->title : '' ?>" class="img-fluid store-avatar">

                                    <div class="uploadImages_store"><span></span></div>
                                </div>
                                @else
                                <img id="post_feature_image" src="" style="width:280px; height:230px;display:none;border-radius: 20px;border: 2px solid blue;" alt="" class="img-fluid">
                                @endif
                            </div>
                        </div>
                        <div class="">
                            <input class="btn btn-primary " type="submit" value="save">
                        </div>
                    </div>
                </div>
            </form>
        </div>
        </div>
    </section>
    <link href="{{ URL::asset('assets/image-cropper/css/cropper.css' ,env('HTTPS_STATUS'))}}" rel="stylesheet" type="text/css" media="screen" />
    <link href="{{ URL::asset('assets/image-cropper/css/main.css' ,env('HTTPS_STATUS'))}}" rel="stylesheet" type="text/css" media="screen" />
    <script src="{{ URL::asset('assets/image-cropper/js/cropper.min.js' ,env('HTTPS_STATUS'))}}" type="text/javascript" charset="utf-8"></script>
    <div id="post-profile-popup" class="modal fade popup-style create-shipping-profile">

        <script>
            CKEDITOR.replace('post_content');


            $("#addpost_section").validate({
                errorElement: "span",
                rules: {
                    title: {
                        required: true
                    },
                    postCategory: {
                        required: true
                    },
                    blog_image_id: {
                        required: true
                    },

                },
                messages: {
                    title: {
                        required: "Please Enter Post Title."
                    },
                    postCategory: {
                        required: "Please Select Category."
                    },
                    blog_image_id: {
                        required: "No File Chosen."
                    },

                },
                errorPlacement: function(error, element) {
                    error.appendTo(element.closest('.form-group'));

                },
                submitHandler: function(form) {
                    // Get CKEditor content
                    var editor = CKEDITOR.instances['post_content'];
                    var editorValue = editor.getData().trim();

                    // Perform CKEditor content validation
                    if (editorValue === '') {
                        $(".category_description").removeClass("d-none");
                        $(".category_description").addClass("d-block");
                        alert('CKEditor content is required.', "error");
                        return false; // Prevent form submission
                    }
                    form.submit();
                }
                // Trigger validation on CKEditor keyup event

            });


            jQuery('body').on('click', '.getCropImage_post', function(e) {
                $(".uploadImages_store").show();
                var mainImageDiv = $('body');
                var cropcanvas = mainImageDiv.find('.Cropperimage').cropper('getCroppedCanvas', {
                    width: 217,
                    height: 146,
                });
                $('body').find('.photoSection .uploadImages_store').delay(1000).show();
                var croppng = cropcanvas.toDataURL("image/png");
                mainImageDiv.find('.previewClass').attr('src', croppng);
                mainImageDiv.find('.viewImages').attr('data-featherlight', croppng);
                var formData = new FormData();
                formData.append('ImageData', croppng);
                $.ajax({
                    url: "/post-image",
                    method: 'POST',
                    processData: false,
                    contentType: false,
                    data: formData,
                    success: function(obj) {
                        console.log(obj);
                        const res = JSON.parse(obj);
                        console.log(res.id);

                        $("#blog_image_id").val(res.id);
                        //     $(".uploadImages").show();
                        $('#post_feature_image').attr('src', res.image).css("display", "block");

                        $(".input-container-div").hide();
                        $(".fa-user").hide();
                        setTimeout(function() {
                            $(".uploadImages").hide();
                        }, 1000);

                        $("#post-profile-popup").modal('hide');
                    },
                    error: function() {
                        $("#post-profile-popup").modal('hide');
                        alert("sorry something went wrong");
                        setTimeout(function() {
                            $(".uploadImages").hide();
                        }, 1000);

                    }

                });
            });

            function cropper_post(imgSrc) {
                var thumbnailHtml;
                console.log("imgSrc", imgSrc);
                thumbnailHtml = '<div class="modal-dialog animated bounceInDown" style="max-width:800px"> <div class="modal-content"> ';
                thumbnailHtml += '<div class="modal-body"> <div class="row imagecrop-area"> <div class="col-xs-12 col-sm-12"> <div class="img-container"> ';
                thumbnailHtml += ' <img class="Cropperimage" src="' + imgSrc + '" alt="Picture"> </div>';
                thumbnailHtml += '<div class="clearfix"></div><br></div><div class="clearfix spacer20"></div><div class="col-md-12 docs-toggles"> ';
                thumbnailHtml += '</div></div></div>';
                thumbnailHtml += '<div class="btn-group btn-group-justified" data-toggle="buttons">';
                thumbnailHtml += '<div class="modal-footer"> <button data-dismiss="modal" class="btn btn-primary btn-simple" type="button">Close</button> <button class="btn-primary btn btn-success getCropImage_post btn-primary-background text-white" type="button" data-dismiss="modal">Save changes</button> </div></div></div>';
                $('#post-profile-popup').append(thumbnailHtml);
            }

            function readFile_post(input) {
                $(".upload_image_error").html("");
                var file = input.files[0];
                var filename = $('#post_image').val();
                var ext = filename.split('.').pop();
                var allowed_extensions = ['png', 'PNG', 'jpg', 'JPG', 'JPEG', 'jpeg'];
                if ($.inArray(ext, allowed_extensions) == -1) {
                    $('#post_image').closest(".form-group").append("<div class='upload_image_error'>Invalid Image type.</div>");
                    return false;
                }
                console.log(file);
                if (file) {
                    var vol_size = file.size;
                    if (vol_size <= 0) {
                        vol_size = 1;
                    }
                    vol_size = (vol_size) / 1024;
                    vol_size = parseInt(vol_size);
                    console.log("vol_size " + vol_size);
                    if (vol_size < 20481) {
                        console.log("called");
                        var reader = new FileReader();
                        reader.addEventListener("load", function() {
                            $('body').find('#post-profile-popup').html('');
                            $('#post_image').val('');
                            console.log("reader", reader);
                            cropper_post(reader.result);
                            var options = {
                                aspectRatio: 3 / 2,
                                crop: function(e) {}
                            };
                            // Cropper
                            $('body').find('.Cropperimage').on({
                                'build.cropper': function(e) {
                                    console.log(e.type);
                                },
                                'built.cropper': function(e) {
                                    console.log(e.type);
                                },
                                'cropstart.cropper': function(e) {
                                    console.log(e.type, e.action);
                                },
                                'cropmove.cropper': function(e) {
                                    console.log(e.type, e.action);
                                },
                                'cropend.cropper': function(e) {
                                    console.log(e.type, e.action);
                                },
                                'crop.cropper': function(e) {
                                    console.log(e.type, e.x, e.y, e.width, e.height, e.rotate, e.scaleX, e.scaleY);
                                },
                                'zoom.cropper': function(e) {
                                    e.preventDefault(); // Prevent zoom out
                                    console.log(e.type, e.ratio);
                                }
                            }).cropper(options);

                            $('#post-profile-popup').modal('show');

                            setTimeout(function() {
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
        </script>

</main>


@endsection