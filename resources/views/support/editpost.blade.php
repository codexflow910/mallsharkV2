@extends('layouts.new-main')
<!-- Main Banner Starts -->
@section('content')
@section('title', 'Edit post')
<!-- Content Starts -->
<main>
   
    @if(Session::has('SuccessMessage'))

    <div class="alert alert-success alert-success-mbt" id="success_message" role="alert">
        <strong>Well done!</strong> {{ Session::get('SuccessMessage') }}
        <script>
            setTimeout(function () {
                jQuery("#success_message").fadeOut('slow');
            }, 3000);
        </script>
    </div>
    @endif

    @if ($errors->any())
    <div  class="alert alert-danger" id="error_message" role="alert">
        <strong>Oh snap!</strong> Change a few things up and try submitting again.
    </div>
    <script>
        setTimeout(function () {
            jQuery("#error_message").fadeOut('slow');
        }, 3000);
    </script>
    @endif
    <div class="alert alert-danger errorpage fade" role="alert" style="display: none">
        <strong><i class="fa fa-exclamation-triangle" aria-hidden="true"></i> </strong> Fill out all of the required fields correctly in order to preview or save
    </div>
    <!-- Dashboard Ends -->
    <div class="dshbrdin">
        <div class="container"><!-- hide-when-preview --> 
            <div class="row">
                <div class="col-md-12">
                    <h1>Edit Post</h1>  
                </div>   
            </div>
        </div>
        <!--<button class="btn btn-primary btn-simple loadShippingOption" id="loadShippingOption" type="button" >Shipping Options</button>-->
        <?php
        $action='support-update-post';
        ?>
        
        
        <form id="addProductForm" action="<?php echo URL::to($action); ?>" method="post" enctype="multipart/form-data"><!-- hide-when-preview --> 
            <!-- Add Product Section Starts -->
            <div class="addprd mt-10">
                <div class="container">



                    <div class="row">
                        <div class="col-md-8 rghtexp">
                            <label class="labled-item">Select Category</label>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <select class="form-control" id="productMainCategory" name="productCategory[]">
                                            <?php
                                            $parentCategory = getAllCategory("default", 0);
                                            if (isset($parentCategory) && is_array($parentCategory) && !empty($parentCategory)) {
                                                echo '<option value="0">Select Category</option>';
                                                foreach ($parentCategory as $key => $value) {
                                                    echo '<option value="' . $value['id'] . '">' . $value['categoryTitle'] . '</option>';
                                                }
                                            } else {
                                                echo '<option value="0">No category found</option>';
                                            }
                                            ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group" id="subcategory" style="display: none">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group" id="childcategory" style="display: none">

                                    </div>
                                </div>    
                            </div>  
                        </div>
                    </div>
                    @include('posts.attributes')
                </div>
            </div>
    </div>

    <!-- Add Product Section Ends -->
    <input type="hidden"  name="shippingOptionKey" value="">
    <input type="hidden" id="edit_product_id" name="edit_product_id" value="<?php echo Crypt::encryptString($prodcutDetails['product_id']); ?>">
    <!-- Form Option Ends -->
    <div class="container-fluid bxsub">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <p class="text-left">
                        <button class="btn btn-primary  addProductSubmit save-button btn-primary-background width30 text-white"   saveproduct="active" id="btn_addProductSubmit" type="button">Save</button>    &nbsp;&nbsp;&nbsp;&nbsp; 
                        <!--                        <a href="{{ url('brand-products') }}" class="">Discard Changes</a>
                                                <a href="{{ url('brand-products') }}" class=" hide-div blue-text">Preview</a>                                             -->

                </div>
            </div>
        </div>
    </div>
</form>
</div><!-- dshbrdin -->
</main>
<!-- Content Ends-->

<style>
    body .ui-sortable-helper{

        position: relative !important;
        z-index: inherit !important;
    }
    .product-slide-values .slick-slide{
        display: block !important;
    }
    .values_product_visibility{
        display: none;
    }
    .pp_pic_holder {
        top: 0% !important;
    }
    .pp_expand{
        display: none !important;
    }


</style>
<script type="text/javascript">
    jQuery().ready(function () {

        $('#productMainCategory').val(<?php echo $prodcutDetails['category'][0]->category_id; ?>).change();

        setTimeout(function () {

<?php if (isset($prodcutDetails['category'][1]) && property_exists($prodcutDetails['category'][1], 'category_id')) { ?>

                $('#subcategory select option[value=<?php echo $prodcutDetails['category'][1]->category_id; ?>]').attr('selected', 'selected');
                $('#subcategory select').val(<?php echo $prodcutDetails['category'][1]->category_id; ?>).change();

<?php } ?>


            setTimeout(function () {

<?php if (isset($prodcutDetails['category'][2]) && property_exists($prodcutDetails['category'][2], 'category_id')) { ?>
                    $('#childcategory select option[value=<?php echo $prodcutDetails['category'][2]->category_id; ?>]').attr('selected', 'selected');
<?php } ?>

            }, 500);
        }, 1000);
    })


</script>
@endsection