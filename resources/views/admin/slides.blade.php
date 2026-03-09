@extends('layouts.dashboard-main')
@section('content')
@section('title', 'Slides Management')
<link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-colorpicker/2.5.3/css/bootstrap-colorpicker.min.css"
    rel="stylesheet">
    <style>
         .colorpicker-2x .colorpicker-saturation {
        width: 200px;
        height: 200px;
    }

    .colorpicker-2x .colorpicker-hue,
    .colorpicker-2x .colorpicker-alpha {
        width: 30px;
        height: 200px;
    }

    .colorpicker-2x .colorpicker-color,
    .colorpicker-2x .colorpicker-color div {
        height: 30px;
    }
    .colorpicker-element .input-group-addon {
    position: relative;
    top: -2px;
}
.colorpicker-element .input-group-addon i {
    width: 40px;
    height: 41px;
}
    </style>
<main>
    <section class="admin-dashboard dshbrdin buyer mt-0 user-management manage-categories-section d-flex">
        @include('admin.adminsidebar')
        <div class="container">
            
            <div class="dshbrdinadmin list-management">
                <div class="row px-4">
                    <div class="col-sm-12 mt-40">
                        <h3>Manage Slides</h3>
                        <div class="mt-2">
                            <ul>
                                <li>
                                    <a class="addslide-link" href="javascript:void(0)" style="color:#195dfa">Add Slide</a>
                                </li>
                            </ul>
                        </div>
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
                    <div class="col-sm-12 mt-20 add-slide" style="display:none;">
                        <form  action="{{route('addslide')}}" method="POST" enctype="multipart/form-data" id="add-slide">
                            <div class="form-group">
                                <p> Note: Best dimension for large slide is 730*380 and for small slide is 350*380</p>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text" id="inputGroupFileAddon01">Upload</span>
                                    </div>
                                    <div class="custom-file">
                                        <input type="file" name="slide_image" required="" class="custom-file-input" id="slide_image"
                                               aria-describedby="inputGroupFileAddon01">
                                        <label class="custom-file-label" for="slide_image">Choose file</label>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="bg_color">Background Color</label>
                                        <div id="cp2" class="input-group colorpicker-component">
                                            <input type="text" value="#001dbb" name="bg_color"
                                                class="form-control" id="bg_color" />
                                            <span class="input-group-addon"><i></i></span>
                                        </div>
                            </div>
                            <div class="form-group">
                                <label for="slide_url">Slide Url:</label>
                                <input class="form-control" type="text" id="slide_url" name="slide_url" required>
                            </div>
                            <div class="form-group">
                                 <label for="">Slide Category:</label>
                                <select class="form-control" name="slide_category">
                                     
                                    <option value="home">Home</option>
                                    <option value="home-small">Home Small</option>
                                </select>
                            </div>

                            {!! csrf_field() !!}
                            <div class="form-group">
                                <input type="submit" value="Save" name="submit">
                                <a href="/admin/slides" class="btn button">Cancel</a>
                            </div>
                        </form>
                    </div>


                    <div class="col-sm-12 mt-20 edit-slide" style="display:none;">
                        <form  action="{{route('editslide')}}" method="POST" enctype="multipart/form-data" id="add-slide">
                            <div class="form-group">
                                 <p> Note: Best dimension for large slide is 730*380 and for small slide is 350*380</p>
                
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        
                                        <span class="input-group-text" id="inputGroupFileAddon01">Upload</span>
                                    </div>
                                   
                                    <input type="hidden" id="slide_id_edit" name="slide_id">
                                    <input type="hidden" id="slide_attachment" name="slide_attachment">

                                    <div class="custom-file">
                                        <input type="file" name="slide_image"  class="custom-file-input" id="slide_image"
                                               aria-describedby="inputGroupFileAddon01">
                                        <label class="custom-file-label" for="slide_image">Choose file</label>
                                       
                                    </div>

                                </div>
                                <div class="slide-pic-edit" style="max-width:250px;"><img src="" style="width:100%;">

                                </div>
                                
                            </div>
                            <div class="form-group">
                            <label for="bg_color">Background Color</label>
                                        <div id="cp3" class="input-group colorpicker-component">
                                            <input type="text" value="#001dbb" name="bg_color"
                                                class="form-control" id="bg_color_edit" />
                                            <span class="input-group-addon"><i></i></span>
                                        </div>
                            </div>
                            <div class="form-group">
                               
                                <label for="slide_url">Slide Url:</label>
                                <input class="form-control" type="text" id="slide_url_edit" name="slide_url" required>
                            </div>
                            <div class="form-group">
                                 <label for="">Slide Category:</label>
                                <select class="form-control" name="slide_category" id="slide_category">
                                    <option value="home">Home</option>
                                    <option value="home-small">Home Small</option>
                                </select>
                            </div>

                            {!! csrf_field() !!}
                            <div class="form-group">
                                <input type="submit"  value="Save" name="submit">

                            </div>

                        </form>
                        <a href="/admin/slides" class="btn button">Cancel</a>
                    </div>
                    <?php
                    // if (count($all_slides) >
                    //         0) {
                    //     echo '<div class="col-sm-12 table-layout mt-40"> <div class="repeater-list-table panel panel-default table-body-container">';
                    //     echo ' <div class="row no-mrgn data-div-table panel-heading"> <div class="col-3 col-sm-3 col-md-3 user-col-send">Image</div>  <div class="col-3 col-sm-3 col-md-3 user-col-third text-center">Slide Link</div> <div class="col-3 col-sm-3 col-md-3 user-col-fourth text-center">Category</div><div class="col-3 col-sm-3  col-md-3 user-col-eight">Actions</div>  </div>';
                    //     echo '<div class="data-body-content-ability">';
                    //     foreach ($all_slides as $slide) {
                    //         echo ' <div class="row no-mrgn data-div-table repeater-list-ability-row">';
                    //         $attachment_id = $slide->attachment_id;
                    //         $slide_id = $slide->slide_id;
                    //         $link = $slide->link;
                    //         $status = $slide->status;
                    //         $cat = $slide->category;
                    //         $bgcolor = $slide->bg_color;
                    //         //$fileName = $slide->fileName;
                    //         // $fileExtension = $slide->fileExtension;
                    //         $image_url = getAttachmentUrlById($attachment_id, 'small');
                    //         echo '<div class="col-3 col-sm-3 col-md-3 user-col-send"><img src="' . $image_url . '" alt="Sale for discounted product" title="Sale for discounted product" style="max-width:100%;"></img></div> <div class="col-3 col-sm-3 col-md-3 user-col-third text-center">' . $link . '</div> <div class="col-3 col-sm-3 col-md-3 user-col-fourth text-center">' . $cat . '</div>  <div class="col-3 col-sm-3  col-md-3 "><a href="javascript:void(0);" class="editslide-link" data-slide_id="' . $slide_id . '" data-link="' . $link . '" data-status="' . $status . '" data-cat="' . $cat . '" data-attachment_id="' . $attachment_id . '" data-img-url="' . $image_url . '" data-cat="'.$cat.'" " data-bgcolor="'.$bgcolor.'" >Edit</a> | <a href="/admin/delete-slide/' . $slide_id . '">Delete</a></div>';
                        
                    //         echo '</div>';
                    //     }
                    //     echo '</div></div></div>';
                    // }

                        if (count($all_slides) > 0) {
                            // Define categories
                            $homeSlides = [];
                            $smallHomeSlides = [];

                            // Separate slides by category
                            foreach ($all_slides as $slide) {
                                if ($slide->category === 'home') {
                                    $homeSlides[] = $slide;
                                } elseif ($slide->category === 'home-small') {
                                    $smallHomeSlides[] = $slide;
                                }
                            }

                            function renderSlidesSection($slides, $title)
                            {
                                if (count($slides) > 0) {
                                    echo '<div class="col-sm-12 table-layout mt-20 mb-20">';
                                    echo '<div class="py-3"><h3>' . $title . '</h3></div>';
                                    echo '<div class="repeater-list-table panel panel-default table-body-container">';
                                    echo '<div class="row no-mrgn data-div-table panel-heading">
                                            <div class="col-3 col-sm-3 col-md-3 user-col-send">Image</div>
                                            <div class="col-3 col-sm-3 col-md-3 user-col-third text-center">Slide Link</div>
                                            <div class="col-3 col-sm-3 col-md-3 user-col-fourth text-center">Category</div>
                                            <div class="col-3 col-sm-3 col-md-3 user-col-eight">Actions</div>
                                        </div>';
                                    echo '<div class="data-body-content-ability">';

                                    foreach ($slides as $slide) {
                                        $attachment_id = $slide->attachment_id;
                                        $slide_id = $slide->slide_id;
                                        $link = $slide->link;
                                        $status = $slide->status;
                                        $cat = $slide->category;
                                        $bgcolor = $slide->bg_color;
                                        $image_url = getAttachmentUrlById($attachment_id, 'small');

                                        echo '<div class="row no-mrgn data-div-table repeater-list-ability-row">';
                                        echo '<div class="col-3 col-sm-3 col-md-3 user-col-send">
                                                <img src="' . $image_url . '" alt="Slide Image" title="Slide Image" style="max-width:100%;">
                                            </div>
                                            <div class="col-3 col-sm-3 col-md-3 user-col-third text-center">' . $link . '</div>
                                            <div class="col-3 col-sm-3 col-md-3 user-col-fourth text-center">' . $cat . '</div>
                                            <div class="col-3 col-sm-3 col-md-3">
                                                <a href="javascript:void(0);" class="editslide-link" 
                                                data-slide_id="' . $slide_id . '" 
                                                data-link="' . $link . '" 
                                                data-status="' . $status . '" 
                                                data-cat="' . $cat . '" 
                                                data-attachment_id="' . $attachment_id . '" 
                                                data-img-url="' . $image_url . '" 
                                                data-bgcolor="' . $bgcolor . '">Edit</a> | 
                                                <a href="/admin/delete-slide/' . $slide_id . '">Delete</a>
                                            </div>';
                                        echo '</div>';
                                    }
                                    echo '</div></div></div>';
                                }
                            }

                            // Render Home section
                            renderSlidesSection($homeSlides, 'Home Category Slides');

                            // Render Small Home section
                            renderSlidesSection($smallHomeSlides, 'Small Home Category Slides');
                        }

                    ?>
                    

                </div>


            </div>



        </div>
        </div><!-- dshbrdinadmin -->

    </section>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-colorpicker/2.5.3/js/bootstrap-colorpicker.min.js">
    </script>
    <script type="application/javascript">

$('#cp2').colorpicker({
            customClass: 'colorpicker-2x',
            sliders: {
                saturation: {
                    maxLeft: 200,
                    maxTop: 200
                },
                hue: {
                    maxTop: 200
                },
                alpha: {
                    maxTop: 200
                }
            }
        }).on('changeColor', function(e) {
        // debugger
            // $('body')[0].style.backgroundColor = e.color
            //     .toString('rgba');
        });
$('#cp3').colorpicker({
            customClass: 'colorpicker-2x',
            sliders: {
                saturation: {
                    maxLeft: 200,
                    maxTop: 200
                },
                hue: {
                    maxTop: 200
                },
                alpha: {
                    maxTop: 200
                }
            }
        }).on('changeColor', function(e) {
        // debugger
            // $('body')[0].style.backgroundColor = e.color
            //     .toString('rgba');
        });


        $('input[type="file"]').change(function(e){
        var fileName = e.target.files[0].name;
        $(this).next('.custom-file-label').html(fileName);
        jQuery('.slide-pic-edit').hide();
        });
        jQuery(document).on('click','.addslide-link',function(){
        jQuery('.add-slide').show();
        jQuery('.edit-slide').hide();
        });
        jQuery(document).on('click','.editslide-link',function(){
        jQuery('.add-slide').hide();
        jQuery('.edit-slide').show();
        slide_id=jQuery(this).attr('data-slide_id');
        slide_url=jQuery(this).attr('data-link');
        img_url=jQuery(this).attr('data-img-url');

        slide_status=jQuery(this).attr('data-status');
        slide_cat=jQuery(this).attr('data-cat');
        slide_attachment=jQuery(this).attr('data-attachment_id');
        bgcolor=jQuery(this).attr('data-bgcolor');
        
        jQuery('#slide_attachment').val(slide_attachment);
        jQuery('#bg_color_edit').val(bgcolor)
        jQuery('#cp3').colorpicker('setValue', bgcolor)
        jQuery('#slide_url_edit').val(slide_url);
        jQuery('#slide_id_edit').val(slide_id);
        jQuery('#slide_category').val(slide_cat);
        jQuery('.slide-pic-edit').show();
        jQuery('.slide-pic-edit img').attr('src',img_url);
    
        jQuery(window).scrollTop(0);
        });

    </script>
    @endsection