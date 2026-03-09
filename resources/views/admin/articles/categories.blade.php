@extends('layouts.dashboard-main')
@section('content')
@section('title', 'Blog Categories Management')

<main>

    <section class="admin-dashboard dshbrdin buyer mt-0 user-management manage-categories-section d-flex">
        @include('admin.adminsidebar')
        <div class="dshbrdinadmin list-management w-100">
            <div class="container">
                <div class="row px-4">
                    <div class="col-sm-12 mt-40">
                        <h3>Manage Post Categories</h3>
                    </div>
                    @if (Session::has('message_error'))
                    <div class="col-sm-12">
                        <div class="alert alert-danger" id="error_message">
                            <ul>
                                <li>{!! Session::get('message_error') !!}</li>
                            </ul>
                            <script>
                            setTimeout(function () {
                                jQuery("#error_message").fadeOut('slow');
                            }, 3000);
                        </script>
                        </div>
                       
                    </div>
                    @endif
                    @if (Session::has('message_success'))
                    <div class="col-sm-12">
                        <div class="alert alert-success" id="success_message">
                            <ul>
                                
                                <li >{!! Session::get('message_success') !!}</li>
                            </ul>
                        </div>
                        <script>
                            setTimeout(function () {
                                jQuery("#success_message").fadeOut('slow');
                            }, 3000);
                        </script>
                    </div>
                    @endif
                    <div class="col-sm-12 mt-20">


                        <h3>{{isset($title) ? $title: "Add New Category"}}</h3>

                        <?php
                        $allCategories = getAllPostCategoriesParent();
                        // echo 'Data: <pre>' .print_r($allCategories,true). '</pre>'; die();
                        $actionUrl = isset($actionUrl) ? $actionUrl : '/admin/addpostcategories'
                        ?>
                        <form class="col-md-1-full usrbsc" id="createPostCategory" method="Post" action="{{URL::to($actionUrl)}}" enctype="multipart/form-data">
                            <div class="row mb-45">

                                {{ csrf_field() }}

                                <div class="col-sm-6 col-lg-5">

                                    <label for="parent_cat">Parent</label>
                                    <select class="form-control" name="parent_category" id="parent_category">

                                        <option value="0">Please select</option>
                                        <?php
                                        $allCategories = getAllPostCategoriesParent();
                                        if (count($allCategories) > 0) {
                                            foreach ($allCategories as $key => $val) {
                                                $selected = '';
                                                if (isset($categoryData)) {
                                                    if ($categoryData->parent_id == $val->id) {
                                                        $selected = 'selected';
                                                    }
                                                }
                                                if($val->parent_id == 0 || $val->parent_id == '0' ){

                                                    echo '<option value="' . $val->id . '" ' . $selected . '>' . ucfirst($val->category_name) . '</option>';
                                                }
                                            }
                                        ?>

                                        <?php } ?>

                                    </select>

                                </div>


                                <div class="col-sm-6  col-lg-5 form-group">
                                    <label>Name *</label>
                                    
                                    @if(isset($categoryData))
                                    <input type="text" name="edit_category_name" id="edit_category_name" class="form-control" value="{{isset($categoryData->category_name) ? $categoryData->category_name : ''}}">
                                    <input type="hidden" name="editcategory_id" value="{{isset($categoryData->id) ? $categoryData->id : ''}}" >
                                    @else
                                    <input type="text" name="category_name" id="category_name" class="form-control" value="">
                                    @endif
                                </div>


                                <div class="col-lg-10 section-attachment-mbt">
                                    <label>About Category</label>
                                    <textarea class="form-control" name="description">{{isset($categoryData->description) ? $categoryData->description : ''}}</textarea>
                                </div>
                                <div class=" col-lg-5 mt-2">
                                    <button type="submit" class="primary-btn btn btn-primary">SAVE </button>
                                </div>

                            </div>
                        </form>
                    </div>
                    <div class="col-md-12">


                        <?php
                        // $llCategoreis = getAllCategories();
                        $allCategoreis = getAllPostCategoriesAdmin();
                        if (count($allCategoreis) > 0) {
                        ?>


                            <ul class="nav categoriesnav nav-categories">
                                <?php
                                // echo 'Data: <pre>' .print_r($allCategoreis,true). '</pre>'; die();
                                foreach ($allCategoreis as $key => $val) {

                                    $parent_Category_title = isset($val['cat_info']['title']) ? $val['cat_info']['title'] : '';
                                    $parent_Category_slug = isset($val['cat_info']['slug']) ? $val['cat_info']['slug'] : '';
                                    $parent_Category_id = isset($val['cat_info']['id']) ? $val['cat_info']['id'] : '';
                                    $child_count = isset($val['cat_info']['child_count']) ? $val['cat_info']['child_count'] : '';
                                ?>
                                    <li class="col-md-12 category-list">
                                        <div class="parent-ct">

                                            <span data-target="#nav-{{$parent_Category_id}}">{{ucfirst($parent_Category_title)}}</span>


                                            <span class="edit-button"><a href="{{URL::to('/admin/editcategory/'.$parent_Category_id)}}">Edit</a></span>
                                            <?php
                                            if ($child_count == 0) { ?>

                                                <span class="edit-button"> <a data-category="{{$parent_Category_id}}" style="margin-right:8px;" href="javascript:void(0);" class="remove_postcategory_admin">Remove</a> </span>


                                            <?php }
                                            ?>
                                        </div>


                                        <?php
                                        if (isset($val['childs']) && count($val['childs']) > 0) {
                                        ?>
                                            <div class="" id="nav-{{$parent_Category_id}}" aria-expanded="false">
                                                <ul class="childcategories" style="margin-left: 40px;">
                                                    <?php foreach ($val['childs'] as $newVal) { ?>
                                                        <li class="nav-item category-list">

                                                            <span>
                                                                <?php echo isset($newVal['title']) ? ucfirst($newVal['title']) : ''; ?>
                                                            </span>
                                                            <span class="edit-button"><a href=" {{URL::to('/admin/editcategory/'.$newVal['id'])}} ">Edit</a></span>
                                                            <span class="edit-button"> <a data-category="{{$newVal['id']}}" style="margin-right:20px;" href="javascript:void(0);" class="remove_postcategory_admin">Remove</a> </span>
                                                        </li>

                                                    <?php } ?>
                                                </ul>
                                            </div>

                                        <?php
                                        }
                                        ?>
                                    </li>
                                <?php
                                }
                                ?>

                            </ul>
                        <?php
                        }
                        ?>
                    </div>
                    <div class="col-md-12 large-loader">
                        <span>
                            <img src="{{ asset('assets/images/loader-large.svg',env('HTTPS_STATUS')) }}" class="img-fluid" title="image loader" alt="image loader">
                        </span>
                    </div>
                </div>

            </div>
        </div><!-- dshbrdinadmin -->
        
    </section>
    <!-- <script src="//cdn.ckeditor.com/4.11.1/standard/ckeditor.js"></script> -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.0/jquery.validate.js"></script>
    <script>
        jQuery.validator.addMethod("lettersonly", function(e, r) {
                return this.optional(r) || /^[a-z ]+$/i.test(e)
            }, "Only letters are allowed."),
            jQuery.validator.addMethod("numbersonly", function(e, r) {
                return this.optional(r) || /^[0-9+]+$/i.test(e)
            }, "Only numbers are allowed."),
            $("#createPostCategory").validate({
                    errorElement: "span",
                    rules: {
                        category_name: {
                            required: true,
                            minlength: 4,
                            maxlength: 25,
                            lettersonly: true,
                            remote: {
                                url: "/category/available",
                                type: "get",
                                data: {
                                    category_name: function() {
                                        return $("#category_name").val();
                                    },
                                    id: function() {
                                        return $("#category_id").val(); // Assuming you have an input field with the category ID
                                    }
                                }
                            }
                        }
                    },
                    messages: {
                        category_name: {
                            required: "Category Name is required.",
                            remote: "<div class='clear'></div><div id='register_user_form'>This Category name is already exist.</div></div>"
                        }
                    },
                    errorPlacement: function(error, element) {
                        error.appendTo(element.closest(".form-group"));
                    },
                    submitHandler: function(form) {
                        form.submit();
                    }
                });


        $('#parent_cat').on('change', function() {
            let value = $(this).val();
            if (value == 0) {
                $('.icon-image-block').show()
            } else {

                $('.icon-image-block').hide()
            }

        })
        // CKEDITOR.replace('page_category');

        $(".remove_postcategory_admin").click(function(event) {
            var row = $(this).closest("li.category-list");
            cat_id = jQuery(this).attr("data-category");
            swal({
                title: "Are you sure?",
                text: "Are You Sure You want to Delete this Category ?",
                type: "warning",
                showCancelButton: true,
                closeOnConfirm: false,
                confirmButtonText: "Confirm",
                confirmButtonColor: "#ec6c62"
            }, function() {
                $.ajax({
                    url: '/deletecategory',
                    type: 'POST',
                    data: 'category_id=' + cat_id,
                    dataType: 'json',
                    encode: true,
                    success: function(response) {

                        if (response.status) {
                            swal("Success!", response.message, "success");
                            row.slideUp("slow", function() {
                                row.remove(); // Remove the row after animation
                            });
                        } else {
                            swal("Error!", response.message, "error");
                            setTimeout(function() {
                                window.location.reload();
                            }, 4000);
                        }
                    }
                });
            });
            event.preventDefault();
        });

        $(document).ready(function() {
            $(".user-roles").on("change", function() {
                var result = confirm(" Are you sure want to update role.?");
                if (!result) {
                    return false;
                }
                var data = {};
                var role = $(this).val();
                var userId = $(this).attr('data-id');
                data['role_id'] = role;
                data['user_id'] = userId;
                sendreq(data, 'admin', 'assignrole', 'callBackResponse');
            });
        });

        function callBackResponse(data) {
            console.log(data);
            if (data == 'success') {
                jQuery("#success_message").show();
                setTimeout(function() {
                    window.location.reload();
                }, 3000);
            } else {
                jQuery("#error_message").show();
                setTimeout(function() {
                    window.location.reload();
                }, 3000);
            }
            //        setTimeout(function () {
            //            window.location.reload();
            //        }, 3000);
        }
    </script>
    <style>
        .edit-button {
            float: right;
        }

        ul.categoriesnav li {
            margin-bottom: 12px;
        }

        ul.nav.categoriesnav div li {
            border: 1px solid #ddd;
            padding: 10px;
        }

        ul.nav.categoriesnav>li {
            border: 1px solid #ddd;
            padding: 15px;
        }

        li.col-md-12.parent-list>span {
            margin-right: 12px;
        }

        ul.nav.categoriesnav li div {
            margin-top: 15px;
        }
    </style>
    @endsection