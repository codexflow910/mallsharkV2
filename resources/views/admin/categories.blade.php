@extends('layouts.dashboard-main')
@section('content')
@section('title', 'Categories Management')

<main>

<section class="admin-dashboard dshbrdin buyer mt-0 user-management manage-categories-section d-flex">
    @include('admin.adminsidebar')
    <div class="dshbrdinadmin list-management">
        <div class="container">
            <div class="row px-4">
                <div class="col-sm-12 mt-40">
                    <h3>Manage Categories</h3>
                </div>
                @if (Session::has('message_error'))
                <div class="col-sm-12">
                    <div class="alert alert-danger">
                        <ul>
                            <li>{!! Session::get('message_error') !!}</li>
                        </ul>
                    </div>
                </div>
                @endif
                @if (Session::has('message_success'))
                <div class="col-sm-12">
                    <div class="alert alert-success">
                        <ul>
                            <li>{!! Session::get('message_success') !!}</li>
                        </ul>
                    </div>
                </div>
                @endif
                <div class="col-sm-12 mt-20">
                    <?php
                    //print_rr($updated_category);
                    if (count($updated_category) > 0) {
                        echo '<h3><a href="' . URL::to('/') . '/admin/categories" class="primary-btn btn btn-primary  white-background blue-text">Add New Category</a></h3>';
                    } else {
                        ?>
                        <h3>Add New Category</h3>
                    <?php } ?>
                    <form class="col-md-1-full usrbsc" id="user_account" method="Post" action="{{URL::to('/')}}/admin/addcategories" enctype="multipart/form-data">
                        <div class="row mb-45">
                            <?php
                            $show_drop = true;
                            $parent_id = 0;
                            $categoryTitle = '';
                            $description = '';
                            $button_text = 'Save';
                            $heading_text = 'Add New Category';
                            $img_id = '';
                            $icon_id = '';
                            $meta_title = '';
                            $meta_description = '';
                            $banner_image_id = '';
                            $landing_image_id = '';
                            $title = '';
                            $category_heading ='';
                            $category_slug = '';
                            ?>
                            <?php
                            if (count($updated_category) > 0) {
                                $button_text = 'Update';
                                $heading_text = 'Update Category';
                                if (isset($updated_category[0]['parentCategoryID'])) {
                                    $parent_id = $updated_category[0]['parentCategoryID'];
                                }
                                if (isset($updated_category[0]['categoryTitle'])) {
                                    $categoryTitle = $updated_category[0]['categoryTitle'];
                                }
                                if (isset($updated_category[0]['title'])) {
                                    $title = $updated_category[0]['title'];
                                }
                                if (isset($updated_category[0]['meta_title'])) {
                                    $meta_title = $updated_category[0]['meta_title'];
                                }
                                if (isset($updated_category[0]['category_heading'])) {
                                    $category_heading = $updated_category[0]['category_heading'];
                                }
                                if (isset($updated_category[0]['category_slug'])) {
                                    $category_slug = $updated_category[0]['category_slug'];
                                }
                                if (isset($updated_category[0]['meta_description'])) {
                                    $meta_description = $updated_category[0]['meta_description'];
                                }
                                if (isset($updated_category[0]['description'])) {
                                    $description = $updated_category[0]['description'];
                                }
                                if (isset($updated_category[0]['image_id'])) {
                                    $img_id = $updated_category[0]['image_id'];
                                }
                                if (isset($updated_category[0]['icon_id'])) {
                                    $icon_id = $updated_category[0]['icon_id'];
                                }
                                if (isset($updated_category[0]['parentCategoryID']) && $updated_category[0]['parentCategoryID'] == 0) {
                                    $show_drop = false;
                                }
                                if (isset($updated_category[0]['category_banner_image'])) {
                                    $banner_image_id = $updated_category[0]['category_banner_image'];
                                }
                                if (isset($updated_category[0]['category_landing_image'])) {
                                    $landing_image_id = $updated_category[0]['category_landing_image'];
                                }
                                ?>
                                <input type="hidden"  name="category_id" value="<?php echo $updated_category[0]['id']; ?>">
                                <input type="hidden"  name="last_parent" value="<?php echo $updated_category[0]['parentCategoryID']; ?>">
                            <?php } ?>
                            <div class="col-sm-6 col-lg-5">

                                <label for="parent_cat">Parent</label>
                                <select class="form-control" name="parent_cat" id="parent_cat">

                                    <option value="0">Please select</option>
                                    <?php
                                    $allCategories = getAllCategoriesParent();
                                    if (count($allCategories) > 0 && $show_drop) {
                                        foreach ($allCategories as $key => $val) {
                                            $selected = '';
                                            if ($key == $parent_id) {
                                                $selected = 'selected';
                                            }
                                            echo '<option value="' . $key . '" ' . $selected . '>' . $val . '</option>';
                                        }
                                        ?>

                                    <?php } ?>

                                </select>

                            </div>

                             {{ csrf_field() }}

                            <div class="col-sm-6  col-lg-5">
                                <label>Name *</label>
                                <input type="text" name="category_name" id="category_name" class="form-control" value="<?php echo $categoryTitle; ?>">
                                <?php
                                if ($img_id != '') {
                                    echo ' <input type="hidden" name="category_img" id="category_img" class="form-control" value="' . $img_id . '">';
                                }
                                ?>
                            </div>
                            <div class="col-sm-6  col-lg-5 mt-3">
                                <label>Heading</label>

                                <select class="form-control" name="category_heading" id="category_heading">
                                    <option value="">Please select</option>
                                    <option value="Clothing, Shoes, Jewelry, & Watches" {{$category_heading == 'Clothing, Shoes, Jewelry, & Watches' ? 'selected' : ''}}>Clothing, Shoes, Jewelry, & Watches</option>
                                    <option value="Home & Garden" {{$category_heading == 'Home & Garden' ? 'selected' : ''}}>Home & Garden</option>
                                    <option value="Home Improvement Essentials" {{$category_heading == 'Home Improvement Essentials' ? 'selected' : ''}}>Home Improvement Essentials</option>
                                    <option value="Pet Supplies" {{$category_heading == 'Pet Supplies' ? 'selected' : ''}}>Pet Supplies</option>
                                    <option value="Toys, Kids & Baby" {{$category_heading == 'Toys, Kids & Baby' ? 'selected' : ''}}>Toys, Kids & Baby</option>
                                    <option value="Beauty & Personal Care" {{$category_heading == 'Beauty & Personal Care' ? 'selected' : ''}}>Beauty & Personal Care</option>
                                    <option value="Health & Household" {{$category_heading == 'Health & Household' ? 'selected' : ''}}>Health & Household</option>
                                    <option value="Sports & Outdoors" {{$category_heading == 'Sports & Outdoors' ? 'selected' : ''}}>Sports & Outdoors</option>
                                </select>

                               
                               
                            </div>
                            <div class="col-sm-6  col-lg-5 mt-3">
                                <label>Category Slug</label>
                                <input type="hidden" id="category-id" name="category_id" value="{{ $updated_category[0]['id'] ?? '' }}">
                                <input type="text" name="category_slug" id="category_slug" class="form-control" value="<?php echo $category_slug; ?>">
                                <span id="slug-error" style="color:red;"></span>
                            </div>
                            <div class="col-sm-6  col-lg-5 mt-3">
                                <label>Meta Title</label>
                                <input type="text" name="meta_title" id="meta_title" class="form-control" value="<?php echo $meta_title; ?>">
                               
                            </div>
                            <div class="col-sm-6  col-lg-5 mt-3">
                                <label>Meta Discription</label>
                                <input type="text" name="meta_description" id="meta_description" class="form-control" value="<?php echo $meta_description; ?>">
                               
                            </div>
                            <div class="col-md-6 col-lg-5 section-attachment-mbt">
                                <label>Title</label>
                                <input type="text" name="title" id="title" class="form-control" value="<?php echo $title; ?>">

                                </div>
                            <div class="col-md-6 col-lg-5 section-attachment-mbt">
                                <label>Description</label>
                                <input type="text" name="description" id="description" class="form-control" value="<?php echo $description; ?>">

                                </div>
                            <div class="col-sm-6  col-lg-5 mt-3">
                                <div class="image-panel-image">
                                    <?php
                                    if ($banner_image_id != '') {
                                        $src = getAttachmentUrlById((int) $banner_image_id, 'small', '');
                                        echo '<img src="' . $src . '"  title="'.$title.'" alt="'.$title.'">';
                                    }
                                    ?>
                                </div>
                                <br>
                                <label>Banner image</label>
                                <input type="file" name="category_banner_image" id="category_banner_image" class="form-control" value="">
                               
                            </div>
                            <div class="col-sm-6  col-lg-5 mt-3">
                                <div class="image-panel-image">
                                    <?php
                                    if ($landing_image_id != '') {
                                        $src = getAttachmentUrlById((int) $landing_image_id, 'small', '');
                                        echo '<img src="' . $src . '"  title="'.$title.'" alt="'.$title.'">';
                                    }
                                    ?>
                                </div>
                                <br>
                                <label>Landing Image</label>
                                <input type="file" name="category_landing_image" id="category_landing_image" class="form-control" value="">
                               
                            </div>
                            <div class="col-lg-10 section-attachment-mbt"> 
                                <div class="image-attachment">
                                   
                                    <div class="image-panel-image">
                                        <?php
                                        if ($img_id != '') {
                                            $src = getAttachmentUrlById((int) $img_id, 'small', '');
                                            echo '<img src="' . $src . '"  title="'.$title.'" alt="'.$title.'">';
                                        }
                                        ?>
                                    </div>
                                    {{-- <div class="image-panel-image">
                                        <?php
                                        if ($icon_id != '') {
                                            $src = getAttachmentUrlById((int) $icon_id, 'small', '');
                                            echo '<img src="' . $src . '"  title="'.$title.'" alt="'.$title.'">';
                                        }
                                        ?>
                                    </div> --}}
                                    <div class="inline-block-mbt">
                                        <div class="inline-block-mbt">
                                            <label>Category Avatar *</label>
                                            <input name="file_name" value="" id="file_upload" class="file_upload form-control" type="file">
                                        </div>
                                        

                                        <div class="form-check">

                                           
                                           
                                        </div>
                                    </div>
                                </div>
                                
                            </div>
                            <div class="col-lg-10 section-attachment-mbt"> 
                                <div class="image-attachment">
                                   
                                    {{-- <div class="image-panel-image">
                                        <?php
                                        if ($img_id != '') {
                                            $src = getAttachmentUrlById((int) $img_id, 'small', '');
                                            echo '<img src="' . $src . '"  title="'.$title.'" alt="'.$title.'">';
                                        }
                                        ?>
                                    </div> --}}
                                    <div class="image-panel-image">
                                        <?php
                                        if ($icon_id != '') {
                                            $src = getAttachmentUrlById((int) $icon_id, 'small', '');
                                            echo '<img src="' . $src . '"  title="'.$title.'" alt="'.$title.'">';
                                        }
                                        ?>
                                    </div>
                                    <div class="inline-block-mbt">
                                        
                                        <div class="inline-block-mbt icon-image-block" style="display: block">
                                            <label>Upload Icon</label>
                                            <input name="icon_file_name" value="" id="icon_file_upload" class="file_upload form-control" type="file">
                                        </div>

                                        <div class="form-check">


                                           
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class=" col-lg-5 mt-2" >
                            <button type="submit" class="primary-btn btn btn-primary category-submit"> {{$button_text}}</button>
                            </div>
                            <div class="form-check col-lg-5 " style="display: none;">

                                <label style="visibility:hidden;display:block">button</label>
                                <button type="submit" class="btn button">{{$button_text}}</button>
                            </div>
                        </div>
                    </form>
                </div>
                <?php
                // $llCategoreis = getAllCategories();
                $allCategoreis = getAllCategoriesAdmin();
                if (count($allCategoreis) > 0) {
                    ?>


                    <ul class="nav categoriesnav nav-categories">
                        <?php
                        // echo 'Data: <pre>' .print_r($allCategoreis,true). '</pre>'; die();
                        foreach ($allCategoreis as $key => $val) {

                            $parent_Category_title = isset($val['cat_info']['title']) ? $val['cat_info']['title'] : '';
                            $parent_Category_slug = isset($val['cat_info']['slug']) ? $val['cat_info']['slug'] : '';
                            $parent_Category_id = isset($val['cat_info']['id']) ? $val['cat_info']['id'] : '';
                            $img_id = isset($val['cat_info']['image_url']) ? $val['cat_info']['image_url'] : '';
                            $child_count = isset($val['cat_info']['child_count']) ? $val['cat_info']['child_count'] : '';
                            ?>
                            <li class = "col-md-12 parent-list">
                                <div class="parent-ct">
                                    <span class="thumbnail-ct">
                                        <?php
                                        if ($img_id != '') {
                                            $src = getAttachmentUrlById((int) $img_id, 'small', '');
                                            ?>
                                            <img src="{{ $src }}" title="{{$parent_Category_title}}" alt="{{$parent_Category_title}}" class="img-fluid" >
                                            <?php
                                        }
                                        ?>

                                    </span>                                    
                                    <span  data-target = "#nav-{{$parent_Category_id}}">{{$parent_Category_title}}</span>
                                    @if($val['cat_info']['status'] == 1)
                                        <button class="deactivate-category btn-success" data-category-id="{{ $parent_Category_id }}" data-status="0" style="margin-left:10px;">Deactivate</button>
                                    @elseif($val['cat_info']['status'] == 0)
                                        <button class="deactivate-category btn-danger" data-category-id="{{ $parent_Category_id }}" data-status="1" style="margin-left:10px;">Activate</button>
                                    @endif
                                    
                                    <span class= "edit-button"><a href="{{URL::to('/')}}/admin/categories?cat_id={{$parent_Category_id}}">Edit</a></span>
                                    <?php
                                    if($child_count == 0){ ?>
                                    
                                     <span  class= "edit-button"> <a data-category="{{$parent_Category_id}}" style="margin-right:8px;" href="javascript:void(0);"  class="remove_category_admin">Remove</a> </span> 
                 
    
                                     <?php }
                                    ?>
                                </div>


                                <?php
                                if (isset($val['childs']) && count($val['childs']) > 0) {
                                    ?>
                                    <div class = "" id = "nav-{{$parent_Category_id}}" aria-expanded = "false">
                                        <ul class = "childcategories" style="margin-left: 40px;">
                                            <?php foreach ($val['childs'] as $newVal) {
                                                $img_id = isset($newVal['image_url']) ? $newVal['image_url'] : '';
                                                ?>
                                                <li class = "nav-item">
                                                    <span class="thumbnail-ct">
                                                        <?php if ($img_id != '') {
                                                             $src = getAttachmentUrlById((int) $img_id, 'small', '');
                                                            ?>
                                                            <img src="{{ $src }}" title="{{$newVal['title']}}" alt="{{$newVal['title']}}" class="img-fluid">
                                                            <?php
                                                        }
                                                        ?>

                                                    </span>
                                                    <span> 
                                                        <?php echo isset($newVal['title']) ? $newVal['title'] : ''; ?>
                                                    </span> 
                                                    <span  class= "edit-button"><a href="{{URL::to('/')}}/admin/categories?cat_id={{$newVal['id']}}">Edit</a></span>
                                                    <span  class= "edit-button"> <a data-category="{{$newVal['id']}}" style="margin-right:20px;" href="javascript:void(0);"  class="remove_category_admin">Remove</a> </span> 
                                                    @if($newVal['status'] == 1)
                                                    <button class="deactivate-category btn btn-success" data-category-id="{{ $newVal['id'] }}" data-status="0" style="margin-right:20px;">Deactivate</button>
                                                    @elseif($newVal['status'] == 0)
                                                    <button class="deactivate-category btn btn-danger" data-category-id="{{ $newVal['id'] }}" data-status="1" style="margin-right:20px;">Activate</button>
                                                    @endif
                                                </li>
                                            <?php } ?>
                                        </ul>
                                    </div>

                                    <?php
                                }
                                ?> </li>
                            <?php
                        }
                        ?>

                    </ul>
                    <?php
                }
                ?>

            </div>

            <div id="myModal" class="modal fade" role="dialog">
                <div class="modal-dialog">

                    <!-- Modal content-->
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal">&times;</button>

                        </div>
                        <div class="modal-body">
                            <div id="del-content"></div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                        </div>
                    </div>

                </div>
            </div>

        </div>
    </div><!-- dshbrdinadmin -->
    {{-- <div class="large-loader">
        <span>
            <img src="{{ asset('assets/images/loader-large.svg',env('HTTPS_STATUS')) }}" class="img-fluid" title="image loader"  alt="image loader">
        </span>
    </div> --}}
</section>
<!-- <script src="//cdn.ckeditor.com/4.11.1/standard/ckeditor.js"></script> -->
<script>
    $('#parent_cat').on('change',function(){
       let value  = $(this).val();
        if(value == 0){
          $('.icon-image-block').show()  
        }else{
            
            $('.icon-image-block').hide()  
        }
        
    })
    // CKEDITOR.replace('page_category');

    $(".remove_category_admin").click(function(event){
               
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
                           url: '/detecategory',
                           type: 'POST',
                           data: 'category_id=' + cat_id,
                           dataType: 'json',
                           encode: true,
                           success: function(response) {

                            if(response.status){
                                swal("Success!", response.message, "success");
                                //    setTimeout(function () {
                                //        window.location.reload();
                                //    }, 2000);
                            }else{
                                swal("Error!", response.message, "error");
                                //    setTimeout(function () {
                                //        window.location.reload();
                                //    }, 4000);
                            }     
                           }
                       });
                   });
           event.preventDefault();
       });
       $(document).ready(function() {
            $('form').on('submit', function(event) {
                event.preventDefault(); // Prevent the form from submitting initially
                var categorySlug = $('input[name="category_slug"]').val();
                var categoryId = $('#category-id').val(); // Optional category ID for existing categories
                var form = $(this);

                // Clear any previous error messages
                $('#slug-error').text('');

                // Send AJAX request to check if the slug is unique
                $.ajax({
                    url: '/check-slug-unique',
                    type: 'POST',
                    data: {
                        slug: categorySlug,
                        category_id: categoryId, // Send category ID if exists
                        _token: $('meta[name="csrf-token"]').attr('content') // CSRF token
                    },
                    success: function(response) {
                        if (response.exists) {
                            // Slug is already taken by another category
                            $('#slug-error').text('This slug is already taken by another category.');
                            $('html, body').animate({ scrollTop: 0 }, 'slow');
                        } else {
                            // Slug is unique, submit the form
                            form.off('submit').submit(); // Unbind the submit handler and submit the form
                        }
                    },
                    error: function(xhr, status, error) {
                        console.log('An error occurred: ' + error);
                    }
                });
            });
        });
        $(document).on("click", ".deactivate-category", function() {
            var categoryId = $(this).data('category-id');
            var status = $(this).data('status');
            // Send an AJAX request to check if the category or child categories have products
            $.ajax({
                url: '/change-category-status',
                type: 'POST',
                data: {
                    category_id: categoryId,
                    status : status,
                    _token: $('meta[name="csrf-token"]').attr('content') // CSRF token
                },
                success: function(response) {
                    if (response.status === 'error') {
                        // If category or child categories have products, show a SweetAlert pop-up

                         swal("Error" ,response.message, "error" );

                        // Swal.fire({
                        //     icon: 'error',
                        //     title: 'Deactivation Failed',
                        //     text: response.message, // Error message from the server
                        //     confirmButtonText: 'OK'
                        // });
                    } else if (response.status === 'success') {
                        // Successfully deactivated the category and child categories
                       
                        swal("Success" ,response.message, "success");setTimeout(function () {
                                        location.reload();
                                    }, 2000);
                        
                    }
                },
                error: function(xhr, status, error) {
                    console.log('An error occurred: ' + error);
                    swal({
                        icon: 'error',
                        title: 'Error',
                        text: 'An unexpected error occurred. Please try again.',
                        confirmButtonText: 'OK'
                    });
                }
            });
        });

    $(document).ready(function () {
        $(".user-roles").on("change", function () {
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
            setTimeout(function () {
                window.location.reload();
            }, 3000);
        } else {
            jQuery("#error_message").show();
            setTimeout(function () {
                window.location.reload();
            }, 3000);
        }
//        setTimeout(function () {
//            window.location.reload();
//        }, 3000);
    }

</script>
<style>
    .edit-button,.deactivate-category{
        float:right;
    }
    .deactivate-category:hover{
        background-color: transparent;
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