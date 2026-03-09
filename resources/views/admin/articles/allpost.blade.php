@extends('layouts.dashboard-main')
@section('content')
@section('title', 'Articles')


<section class="admin-dashboard dshbrdin buyer mt-0 user-management manage-categories-section d-flex">
    @include('admin.adminsidebar')

    <div class="container">
        <div class="dshbrdinadmin list-management">
            <div class="row px-4">

                <div class="d-flex justify-content-between align-items-center col-sm-12 mt-40">
                    <div class="">
                        <h3>Manage Posts</h3>
                    </div>
                    <div class="text-right">
                        <a class="addfaqs-link btn btn-primary" href="{{url('admin/create/post')}}" style="color:#fff">Create Post</a>
                    </div>
                </div>
                @if (Session::has('message_error'))
                <div class="col-sm-12">
                    <div class="alert alert-danger error_message">
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
                <div class="col-sm-12">

                    <div class="alert alert-success success_message">
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
                <div class="col-sm-12 table-layout mt-40">
                    <div class="repeater-list-table panel panel-default table-body-container">
                        <div class="row no-mrgn data-div-table panel-heading">
                            <div class="col-2 col-sm-1 col-md-1 user-col-third text-center">Post ID</div>
                            <div class="col-3 col-sm-3 col-md-3 user-col-fourth text-center">Title</div>
                            <div class="col-2 col-sm-4 col-md-4 user-col-fourth text-center">description</div>
                            <div class="col-2 col-sm-2 col-md-2 user-col-fourth text-center">Category</div>
                            <div class="col-3 col-sm-2 col-md-2 user-col-eight text-center">Actions</div>
                        </div>
                        <div class="data-body-content-ability">
                            <?php $id = 0;
                            foreach ($posts as $val) {
                                // echo 'Data: <pre>' .print_r($val->categories,true). '</pre>';
                                $id++;
                            ?>
                                <div class="row no-mrgn data-div-table repeater-list-ability-row" id="removeablefaqs">

                                    <div class="col-2 col-sm-1 col-md-1 user-col-third text-center"> {{$val->id}}
                                    </div>
                                    <div class="col-3 col-sm-3 col-md-3 user-col-third text-center">

                                        {{ Str::limit($val->title, 40)  }}
                                    </div>

                                    <div class="col-4 col-sm-4 col-md-4 user-col-third text-center">

                                        {{ Str::limit(strip_tags($val->description), 50)  }}
                                    </div>
                                    <div class="col-4 col-sm-2 col-md-2 user-col-third text-center">

                                        <?php
                                        foreach ($val->categories as $cat) {
                                            echo    $cat->category_name;
                                        }

                                        ?>


                                    </div>


                                    <div class="col-3 col-sm-2 col-md-2 text-center">
                                        <a href="<?php echo URL::to('/admin/editpost/' . $val->id); ?>" class="editslide-link">Edit</a>
                                        | <a href="javascript:void(0)" class="deletepost" data-id="{{$val->id}}">Delete</a>
                                    </div>

                                </div>
                            <?php } ?>
                        </div>
                    </div>
                </div>
                <div class="d-flex justify-content-center m-3">
                    {{ $posts->withQueryString()->links() }}
                </div>
            </div>
        </div>
    </div>

    <!-- dshbrdinadmin -->

</section>

<script>
    $(".deletepost").click(function(event) {

        postId = jQuery(this).attr("data-id");
        swal({
            title: "Are you sure?",
            text: "Are You Sure You want to Delete this Post ?",
            type: "warning",
            showCancelButton: true,
            closeOnConfirm: false,
            confirmButtonText: "Confirm",
            confirmButtonColor: "#ec6c62"
        }, function() {
            
            $.ajax({
                url: '/deletepost',
                type: 'POST',
                data: 'postId=' + postId,
                dataType: 'json',
                encode: true,
                success: function(response) {

                    if (response.status) {
                        swal("Success!", response.message, "success");
                        // setTimeout(function() {
                        //     window.location.reload();
                        // }, 2000);
                    } else {
                        swal("Error!", response.message, "error");
                        // setTimeout(function() {
                        //     window.location.reload();
                        // }, 4000);
                    }
                }
            });
        });
        event.preventDefault();
    });
</script>
@endsection