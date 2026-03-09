@extends('layouts.new-main')
@section('content')
@section('title', 'Store Templates')
<main>
    @include('Store.sidebar')
    <style>
        .noteactive {
            position: absolute;
            z-index: 9999;
            width: 100%;
            height: 100%;
            left: 0px;
            right: 0px;
        }
        html body .switch-buttonk input{position: static !important; z-index: 88;}
        .alert{
            margin-top: 0px;
            margin-bottom: 30px;
            position: absolute;
            width: auto;
            left: 0;
            right: 0;
            margin-left: auto;
            margin-right: auto;
            z-index: 999;
            top: 142px;
        }

    </style>
    <style>
        .tmpbx { box-shadow: 0 0 5px 0 #64646459; display:inline-block}
        .radio img{
            border:3px solid #fff;

        }
        .radio img.active{
            border:3px solid #34a08d;

        }

        .ovrly{ background-color: #00a08e; position: absolute; width: 100%;}
    </style>
    <div class="dshbrdin store-dashboard">
        <div class="container">


            <h2>Store Template Management</h2>
            <p style="display:none">Select Default Template for Your store</p>
            <?php
            $img_id1 = '';
            $img_id2 = '';
            $img_id3 = '';

            if (isset($results[0]->image_id1)) {
                $img_id1 = $results[0]->image_id1;
            }
            if (isset($results[0]->image_id2)) {
                $img_id2 = $results[0]->image_id2;
            }
            if (isset($results[0]->image_id3)) {
                $img_id3 = $results[0]->image_id3;
            }




            if ($message != '') {
                echo '<div class="alert alert-success">' . $message . '</div>';
            }
            ?>
            <form action="" method="POST" enctype="multipart/form-data">
                <div class="row">
                    <div class="col-md-4 text-center" style="display:none">
                        <div class="radio">
                            <label>
                                <img src="{{ asset('assets/images/temp2.jpg',env('HTTPS_STATUS')) }}" alt="brand" <?php
                                if ($selected == 1) {
                                    echo 'class="active"';
                                }
                                ?>><br>
                                <input type="radio" name="defaulttemplate" value="1" <?php
                                if ($selected == 1) {
                                    echo 'checked';
                                }
                                ?> > <p class="text-center  mt-20">Template 1</p></label>






                        </div>
                    </div>
                    <div class="col-md-4 text-center" style="display:none"> 
                        <div class="radio">
                            <label>
                                <img src="{{ asset('assets/images/temp1.jpg',env('HTTPS_STATUS')) }}" <?php
                                if ($selected == 2) {
                                    echo 'class="active"';
                                }
                                ?>><br>
                                <input type="radio" name="defaulttemplate" value="2" <?php
                                if ($selected == 2) {
                                    echo 'checked';
                                }
                                ?>><p class="text-center  mt-20">Template 2</p>
                            </label>

                        </div>



                    </div>
                    <div class="col-md-4 text-center" style="display:none">
                        <div class="radio">
                            <label>
                                <img src="{{ asset('assets/images/temp3.jpg',env('HTTPS_STATUS')) }}" alt="brand"  <?php
                                if ($selected == 3) {
                                    echo 'class="active"';
                                }
                                ?>><br>
                                <input type="radio" name="defaulttemplate" value="3" <?php
                                if ($selected == 3) {
                                    echo 'checked';
                                }
                                ?>> <p class="text-center mt-20">Template 3</p></label>

                        </div>



                    </div>
                    <div class="mt-30"><div class=" "><h3>Choose Banner Image <small>(min one banner image required)</small></h3></div></div>

                    <div class="col-md-12">
                        <div class="row no-mrgn mb-30">
                            <div class="co-md-4">

                                <div class="form-group">
                                    <label for="exampleFormControlFile1">Banner Image 1</label>
                                    <input type="file" class="form-control-file fileupload" id="exampleFormControlFile1" name='banner1'>
                                    <small>Minimum 1200 x 550, jpg or png only</small>
                                    <?php
                                    if ($img_id1 != '') {
                                        $src1 = getAttachmentUrlById((int) $img_id1, 'small', '');
                                        echo '  <p class="mt-35"><img src="' . $src1 . '" alt="brand" ></p>';
                                    }
                                    ?>


                                </div>

                            </div> 

                            <div class="co-md-4">

                                <div class="form-group">
                                    <label for="exampleFormControlFile1">Banner Image 2</label>
                                    <input type="file" class="form-control-file fileupload" id="exampleFormControlFile2" name='banner2'>
                                    <small>Minimum 1200 x 550, jpg or png only</small>
                                    <?php
                                    if ($img_id2 != '') {
                                        $src2 = getAttachmentUrlById((int) $img_id2, 'small', '');
                                        echo '  <p class="mt-35"><img src="' . $src2 . '" alt="brand" ></p>';
                                    }
                                    ?>
                                   <!-- <p><a href="#">Delete</a></p>  -->
                                </div>

                            </div>   

                            <div class="co-md-4">

                                <div class="form-group">
                                    <label for="exampleFormControlFile1">Banner Image 3</label>
                                    <input type="file" class="form-control-file fileupload" id="exampleFormControlFile3" name='banner3'>
                                    <small>Minimum 1200 x 550, jpg or png only</small>  
                                    <?php
                                    if ($img_id3 != '') {
                                        $src3 = getAttachmentUrlById((int) $img_id3, 'small', '');
                                        echo '  <p class="mt-35"><img src="' . $src3 . '" alt="brand" > </p>';
                                    }
                                    ?>
                                    <!-- <p><a href="#">Delete</a></p>  -->
                                </div>

                            </div>
                            <?php
                            if ($img_id1 != '') {
                                echo ' <input type="hidden" name="banner1_img" id="category_img" class="form-control" value="' . $img_id1 . '">';
                            }
                            ?>
                            <?php
                            if ($img_id2 != '') {
                                echo ' <input type="hidden" name="banner2_img" id="category_img" class="form-control" value="' . $img_id2 . '">';
                            }
                            ?>
                            <?php
                            if ($img_id3 != '') {
                                echo ' <input type="hidden" name="banner3_img" id="category_img" class="form-control" value="' . $img_id3 . '">';
                            }
                            ?>
                        </div> 
                    </div> 
                </div>   



                {!! csrf_field() !!}
                <input type="submit" class="primary-btn btn btn-primary  text-white cartadds background-clr" name="submit" value="save">
            </form>
        </div>
    </div>
</main>
<script>
    jQuery(document).on('click', '.radio', function () {
        jQuery('.radio img').removeClass('active');
        jQuery(this).find('img').addClass('active');
    })

    $(function () {
        //$("#exampleFormControlFile1").bind("click", function () {
        jQuery(document).on("change", ".fileupload", function () {
            //Get reference of FileUpload.
            var fileUpload = $(this)[0];

            //Check whether the file is valid Image.
            var regex = new RegExp("([a-zA-Z0-9\s_\\.\-:])+(.jpg|.png|.JPEG|.gif)$");
            //if (regex.test(fileUpload.value.toLowerCase())) {
            //Check whether HTML5 is supported.
            if (typeof (fileUpload.files) != "undefined") {
                //Initiate the FileReader object.
                var reader = new FileReader();
                //Read the contents of Image File.
                reader.readAsDataURL(fileUpload.files[0]);
                reader.onload = function (e) {
                    //Initiate the JavaScript Image object.
                    var image = new Image();
                    //Set the Base64 string return from FileReader as source.
                    image.src = e.target.result;
                    image.onload = function () {
                        //Determine the Height and Width.
                        var height = this.height;
                        var width = this.width;
                        if (height < 500) {
                            alert("Height must not less than 500.");
                            jQuery("#exampleFormControlFile1").val("");
                            return false;
                        }
                        if (width < 1200) {
                            alert("Width must not less than 1200.");
                            jQuery("#exampleFormControlFile1").val("");
                            return false;
                        }
                        //alert("Uploaded image has valid Height and Width.");
                        return true;
                    };
                }
            } else {
                alert("This browser does not support HTML5.");
                return false;
            }
            /*
             } else {
             alert("Please select a valid Image file.");
             return false;
             }
             */
        });
    });
</script>

@endsection