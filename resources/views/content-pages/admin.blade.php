@extends('layouts.dashboard-main')

@section('content')
@section('title', 'About Us')
<main>

    <style>
        @media only screen and (min-width : 767px) and (max-width : 10000px) {
            .search-icon-header{ display: none; }
        }
    </style>
    <style>
        .tmpbx { box-shadow: 0 0 5px 0 #64646459; display:inline-block}
        .clpgbx { background-color: #fff; box-shadow: 0 0 5px 0 #64646459; width: 100%; padding: 30px; border-radius: 3px; margin-bottom: 10px}
        .ovrly{ background-color: #00a08e; position: absolute; width: 100%;}
    </style>
    <section class="d-flex">
        @include('admin.adminsidebar')

            <div class="container">
                <div class="row px-4">
                {!! Session::has('message') ? Session::get("message") : '' !!}

                <div class="col-md-12 mt-40 px-1">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class=""><h3 class=" ">Content Management</h3></div>
                        <div class="">
                            <a href="javascript:;" class="btn btn-primary btn-simple" data-toggle="modal" data-target="#exampleModal">
                                Add Page
                            </a>
                        </div> 
                    </div>
                </div>
                <div class="col-md-12">
                    <?php
                    if (count($pages) > 0) {
                        foreach ($pages as $page) {
                            $content = $page['page_content'];
                            if ($page['status'] == 1) {
                                $status = 0;
                                $show_status = 'deactivate';
                            } else {
                                $status = 1;
                                $show_status = 'activate';
                            }
                            ?>
                            <div class="clpgbx col-md-12"><?php echo $page['page_name']; ?> 
                                <div class="small f-right-md"><a class="editpage" href="#editpagetarget" data-pname="<?php echo $page['page_name']; ?>" data-id="<?php echo $page['id']; ?>" >Edit</a> &nbsp;|&nbsp; <a data-id="<?php echo $page['id']; ?>"  class="deleteme" href="#" >Delete</a> &nbsp;|&nbsp; <a href="#" data-id="<?php echo $page['id']; ?>"  data-status="<?php echo $status; ?>" class="changestatus">{{$show_status}}</a></div>
                            </div>
                            <div class="edit-page-<?php echo $page['id']; ?>" style="display:none" ><?php echo $page['page_content']; ?></div>
                            <?php
                        }
                    }
                    ?>
                    <div class="clpgbx showhide col-md-12" style="display:none">
                        <h3 class="editor_heading">Privacy Policy</h3> 
                        <form action="{{URL::to('/admin/edit-page')}}" method="post">
                            <div class="mt-20 text-right" id="editpagetarget">
                                <input type="hidden" name="page_id" id="page_id" value="">
                                <input type="hidden" name="page_name" id="page_name" value="">
                                <textarea name="page_content" class="page_contentt "></textarea>
                                <button type="submit" class="mt-2 btn btn-outline-primary cancelme" href="#"> Cancel </button>
                            <button type="submit" class="mt-2 primary-btn btn btn-primary   text-white cartadds background-clr" data-product-id="14" data-store-id="4">Save
                                </button> 
                            </div>
                            {!! csrf_field() !!}
                        </form>
                    </div>
                    <!-- Page Cell Ends -->
                </div> 
            </div>
        </div>
    </section>
    <div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Add Page</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="{{URL::to('/admin/add-page')}}" method="POST" id="create_content_pages"> 
                    <div class="modal-body">

                        <div class="form-group">
                            <label for="recipient-name" class="col-form-label">Page Title:</label>
                            <input type="text" class="form-control" id="page-name" name="page_name">
                        </div>

                    </div>
                    <div class="modal-footer">
                        {!! csrf_field() !!}
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save</button>
                    </div>

                </form>
            </div>
        </div>
    </div>
    <script src="https://cdn.ckeditor.com/4.22.1/standard/ckeditor.js"></script>
    <script>
    CKEDITOR.config.versionCheck = false;
    </script>
    <style>.show-more-results{
            display:none;
        }
    </style>
<script src="{{ URL::asset('assets/js/jquery.validate.js',env('HTTPS_STATUS')) }}"></script>
    <script>
current_url = '<?php echo URL::to('/admin/content-pages'); ?>';

CKEDITOR.replace('page_content');
jQuery(document).on('click', '.editpage', function () {
    id = jQuery(this).attr('data-id');
    name = jQuery(this).attr('data-pname');

    class_final = '.edit-page-' + id;
    content = jQuery(class_final).html();
    jQuery('#page_id').val(id);
    jQuery('#page_name').val(name);
    jQuery('.page_contentt').val(content);
    jQuery('.showhide').show();
    jQuery('.editor_heading').html(name);
    CKEDITOR.instances['page_content'].setData(content)

});
jQuery(document).on('click', '.cancelme', function () {
    jQuery('.showhide').hide();

});
jQuery('.deleteme').click(function () {
    id = jQuery(this).attr('data-id');
    if (id != '') {
        swal({
            title: "Are you sure?",
            text: 'Once page is deleted its not possible to restore it',
            type: "warning",
            showCancelButton: true,
            confirmButtonClass: "btn-success",
            confirmButtonText: "Yes, Do it!",
            closeOnConfirm: false
        },
                function () {
                    //jQuery('.uploadImages').show();
                    new_url = current_url + '?page_id=' + id;
                    window.location.assign(new_url);
                    swal("Success!", "Status update successfully.", "success");

                });
        /*
         if (confirm('Are you sure you want to change the stauts')) {
         
         } else {
         // do nothing
         }
         */
    }

});
jQuery('.changestatus').click(function () {
    id = jQuery(this).attr('data-id');
    status = jQuery(this).attr('data-status');
    if (id != '') {
        swal({
            title: "Are you sure?",
            text: 'You want to change the page status, pages with inactive deactive status are not visible on frontend',
            type: "warning",
            showCancelButton: true,
            confirmButtonClass: "btn-success",
            confirmButtonText: "Yes, Do it!",
            closeOnConfirm: false
        },
                function () {
                    //jQuery('.uploadImages').show();
                    new_url = current_url + '?page_id=' + id + '&status=' + status;
                    //alert(new_url);
                    window.location.assign(new_url);
                    swal("Success!", "Status update successfully.", "success");

                });
        /*
         if (confirm('Are you sure you want to change the stauts')) {
         
         } else {
         // do nothing
         }
         */
    }

});

    $("#create_content_pages").validate({
        errorElement: "span",
        rules: {
            page_name: {required: true},

         
        },
        messages: {
            page_name: {required: "Page Title is required."},
           
        },
        errorPlacement: function (error, element) {
            error.appendTo(element.closest('.form-group'));
        },
        submitHandler: function (form) {
            form.submit();
        }
    });
    </script>

</main>
@endsection
<!-- Content Ends-->




