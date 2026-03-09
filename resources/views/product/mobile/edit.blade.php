@extends('layouts.mobile-main')
<!-- Main Banner Starts -->
@section('content')
@section('title', 'Edit product')
<!-- Content Starts -->

<main class="editproduct">
    @include('Store.sidebar')
    @if(Session::has('SuccessMessage'))
    <div class="alert alert-success alert-success-mbt" role="alert">
        <strong>Well done!</strong> {{ Session::get('SuccessMessage') }}
    </div>
    @endif
    @php 
    $productType =  !empty($prodcutDetails['product_type']) ? $prodcutDetails['product_type'] :'';
    @endphp
    <script>
        var prodType = "{{$productType}}";


    </script>
    @if ($errors->any())
    <div class="alert alert-danger" role="alert">
        <strong>Oh snap!</strong> Change a few things up and try submitting again.
    </div>
    @endif
        <?php 
            // echo  request()->get('edit-by');
            if(!empty(request()->get('edit-by')) && request()->get('edit-by')== 'admin'){
                $urlData = url('admin/store-products');
                $class = 'hide_section';
            }else{
                $urlData = url('store-products');
                $class = '';
                
            }
        ?>
    <div class="alert alert-danger errorpage fade" role="alert" style="display: none">
        <strong><i class="fa fa-exclamation-triangle" aria-hidden="true"></i> </strong> Fill out all of the required fields correctly in order to preview or save
    </div>
    <!-- Dashboard Ends -->
    <div class="dshbrdin mobile-top-spacing-less">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <div class="">              
                        <div class="d-flex justify-content-between">
                            <div class="hd-bt">  <h1 style="">Edit Product </h1></div>  
                            <div class="add-pr-lnk">
                                <?php
                                $role = getCurrentUserRoleId();
                                if ($role->role_id == 1) {
                                    $back_to_dashboard = '/admin/dashboard';
                                } else {
                                    $back_to_dashboard = '/store-dashboard';
                                }
                                ?>
                                <a class="btn btn-primary btn-simple small-btn-mbt" href="{{URL::to($back_to_dashboard)}}">
                                    Back to Dashboard
                                </a>                                        
                            </div>                        
                        </div>            
                    </div>                    



                </div>   
            </div>
        </div>
        <form id="addProductForm" action="{{ url('editproductAction') }}" method="post" enctype="multipart/form-data">
            <!-- Add Product Section Starts -->
            <div class="addprd ">
                <div class="container   ">
                    <div class="row">
                        <div class="col-sm-4 lftexp">
                            <h6>Select Category *
                            </h6>
                            <p>Choose appropriate Category for your product</p>
                        </div>
                        <div class="col-md-8 rghtexp">
                            <div class="row">

                                <div class="col-md-4">
                                    <div class="form-group">
                                        <!--<label class="labled-item"></label>-->
                                        <select class="form-control" id="productMainCategory" name="productCategory[]">

                                            <?php
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
                                    <!--<label class="labled-item visiblity-hidden childcat" style="display:none;">Select Category</label>-->
                                    <div class="form-group" id="subcategory" style="display: none">

                                    </div>
                                </div>

                            </div>  
                        </div>

                    </div>
        
                    <div class="">
                        @include('product.product-variation-edit')
                    </div>
                </div>  
                <div class="container">
                    @include('product.attributes')
                </div> 

                <div class="container <?php echo $class; ?>">
                    <div class=" ">     
                        <div class="card prdsec shippingTab w-100">
                            <div class="card-header" role="tab" id="">
                                <!--                                <h5 class="mb-0">
                                                                    <a class="collapsed" data-toggle="collapse" href="#collapsefive" aria-expanded="false" aria-controls="collapseThree">
                                                                        Shipping
                                                                        <i class="fa fa-plus pull-right" aria-hidden="true"></i> 
                                                                        <i class="fa fa-minus pull-right" aria-hidden="true"></i>   
                                                                    </a>
                                                                </h5>-->
                            </div>
                            <!--                            <div id="collapsefive" class="panel-collapse  collapse show" role="tabpanel" aria-labelledby="headingfive" data-parent="#accordion">-->
                                @php 
                                $class = '';
                                if(isset($prodcutDetails['product_type']) && $prodcutDetails['product_type'] == 'virtual') {
                                    $class = 'hide-me';
                                }
                                @endphp    
                            <div class="card-body {{$class}}">

                                <div class="row w">
                                    <div class="col-sm-4 lftexp">
                                        <h6>Shipping Option
                                        </h6>
                                    </div>
                                    <div class="col-md-8 rghtexp">
                                        <div class="shipping-section-container ui-toolkit shipping-option-container">

                                            <div class="list-container-scroll">

                                                @include('product.shipping.previousShippingOption')
                                            </div>
                                            <!-- panel-repeater-radio -->
                                            <div class="panel-repeater-radio custom-shipping-container br-top-none emphty-space-zero">



                                            </div>
                                            <!-- panel-repeater-radio -->
                                        </div>
                                    </div>


                                    <div class="cl-bx-mbt bckred">
                                        <div class="row recomandedShippigOptionFields">


                                        </div>

                                        <!-- clculateRecommeded end -->
                                        <div class="row">
                                            <div class="col-sm-4 showon-customshipping-select cl-bx-mbt bckred"></div>
                                            <div class="col-sm-8 showon-customshipping-select cl-bx-mbt bckred">

                                            </div>
                                        </div>
                                    </div>
                                    <!--                                        <div class="col-md-4 lftexp">
                                                                                <h6>Item weight *</h6>
                                                                                <p class="small">Weight and size are required for calculated profiles. Learn more about calculated shipping.</p>     
                                                                            </div> -->
                                    <!--                                        <div class="col-md-4 rghtexp small-lable"> 
                                                                                <div class="form-group">
                                                                                    <label>lbs</label>
                                                                                    <input onkeypress='Numvalidate(event, "price", "productWeight_lbs")' type="text" class="form-control required-field" id="productWeight_lbs" name="productWeight_lbs"  placeholder="lbs">
                                                                                </div>   
                                                                            </div>-->
                                    <!--                                        <div class="col-md-4 rghtexp small-lable"> 
                                                                                <div class="form-group">
                                                                                    <label>oz</label>
                                                                                    <input onkeypress='Numvalidate(event, "price", "productWeight_oz")' type="text" class="form-control required-field" id="productWeight_oz" name="productWeight_oz"  placeholder="oz" >
                                                                                </div>   
                                                                            </div>-->

                                    <!--                                        <div class="col-md-4 lftexp">
                                                                                <h6>Item size (when packed) *</h6>
                                                                                <p class="small">Enter the weight and size of the item plus packing materials. This will be used to determine package size.</p>     
                                                                            </div> -->
                                    <!--                                        <div class="col-md-8 rghtexp"> 
                                                                                <div class="row" >
                                                                                    <div class="col-md-4 small-lable"> 
                                                                                        <div class="form-group">
                                                                                            <label>Length</label>
                                                                                            <input onkeypress='Numvalidate(event, "price", "productSize_length")' type="text" class="form-control required-field" id="productSize_length" name="productSize_length"  placeholder="Length" >
                                    
                                                                                        </div>   
                                                                                    </div>
                                                                                    <div class="col-md-4 small-lable"> 
                                                                                        <div class="form-group">
                                                                                            <label>Width</label> 
                                                                                            <input onkeypress='Numvalidate(event, "price", "productSize_width")' type="text" class="form-control required-field" id="productSize_width" name="productSize_width"  placeholder="Width" >
                                                                                        </div>   
                                                                                    </div>
                                                                                    <div class="col-md-4 small-lable"> 
                                                                                        <div class="form-group">
                                                                                            <label>Height</label>   
                                                                                            <input onkeypress='Numvalidate(event, "price", "productSize_height")' type="text" class="form-control required-field" id="productSize_height" name="productSize_height"  placeholder="Height" >
                                    
                                                                                        </div>   
                                                                                    </div>
                                    
                                                                                </div>
                                                                            </div>-->



                                    <div class="col-md-4 lftexp" style="display: none">
                                        <h6>Preview shipping cost</h6>
                                        <p class="small">See what buyers will pay for shipping.</p>     
                                    </div> 
                                    <div class="col-md-4 rghtexp" style="display: none"> 
                                        <div class="form-group">
                                            <select class="form-control" id="">
                                                <option>If a buyer lives in</option>
                                                <option>USA</option>
                                                <option>England</option>
                                                <option>Australia</option>
                                                <option>China</option>
                                                <option>Russia</option>
                                                <option>Germany</option>
                                                <option>Brazil</option>
                                            </select>
                                        </div>  
                                    </div>
                                    <div class="col-md-4 rghtexp" style="display: none"> 
                                        <p class=" ">Their cost would be around <br> <span> &#36; 0.00</span></p>   
                                    </div> 
                                </div>  

                            </div>
                            <!--                            </div>-->
                        </div>  <!-- card prdsec -->
                    </div>

                    <div class="container">                   
                        <div class="card-body faqs-body-add no-padd-left">
                            <div class="row">    
                                <div class="col-md-4 lftexp ">
                                    <h6>Setup FAQs *</h6>
                                    <p>Answer the possible questions shopper may ask before buying the product</p>   
                                </div>
                                <div class="col-md-8 rghtexp causelist"> 
                                    <div class="form-group ">
                                        <label>
                                            Question
                                        </label>
                                        <input type="text" onkeypress='alphaNumvalidate(event, "faqQuestion", 150)'  id="faqQuestion" class="form-control"   maxlength="150">
                                    </div>
                                    <div class="form-group mb-15">
                                        <label>
                                            Answer
                                        </label>
                                        <textarea  onkeypress='alphaNumvalidate(event, "faqAnswer", 700)'  id="faqAnswer" class="form-control" max="700"></textarea>
                                    </div>
                                    <div class="add-button-option">
                                        <button class="btn btn-primary btn-simple" id="addFaqBtn" type="button" data-toggle="">Add FAQs</button>

                                    </div>
                                    <div class="faqs-list-results">
                                        <?php
                                        if (isset($prodcutDetails['faqs']) && is_array($prodcutDetails['faqs']) && count($prodcutDetails['faqs']) > 0) {
                                            foreach ($prodcutDetails['faqs'] as $key => $value) {

                                                echo ' <div class="list-repeater-mbt">
                                                                        <h4>Q:
                                                                            ' . $value->question . '<span class="pull-right text-center"> <a href="javascript:;" class="align-with-btn text-gray-lighter cross-icon-cont remove-containertabs"><span class="ss-etsy ss-delete icon-smaller icon-t-2"><i class="fa fa-times" aria-hidden="true"></i></span></a></span>
                                                                            <input id="faqQuestion' . $key . '" type="hidden" value= "' . $value->question . '" name="faqQuestion[]"/> 
                                                                        </h4>
                                                                        <p>
                                                                             ' . $value->answer . '
                                                                            <input id="faqAnswer_' . $key . '" type="hidden" value= "' . $value->answer . '" name="faqAnswer[]"/> 
                                                                        </p>
                                                                    </div>';
                                            }
                                        }
                                        ?>
                                    </div>
                                    <!-- faqs-list-results -->



                                </div>  
                            </div>
                        </div> 
                    </div>
                </div>

                <input type="hidden" id="edit_product_id" name="edit_product_id" value="{{ collect(request()->segments())->last() }}">


                <input type="hidden"  name="userID" value="<?php echo $prodcutDetails['user_id']; ?>">
                <input type="hidden"  name="storeID" value="<?php echo $prodcutDetails['store_id']; ?>">
                <input type="hidden"  name="storeID" value="<?php echo isset($prodcutDetails['shipping_option_id']) ? $prodcutDetails['shipping_option_id'] : ''; ?>">
                <input type="hidden" id="productVideoURL" name="video_url" value="<?php echo isset($prodcutDetails['video_url']) ? $prodcutDetails['video_url'] : ''; ?>">
                <input type="hidden" id="edit-by" name="edit-by" value="<?php echo isset($_GET['edit-by']) ? $_GET['edit-by'] : ''; ?>">
                <!-- Add Product Section Ends -->

                <!-- Form Option Ends -->

                <div class="container-fluid bxsub">

                    <div class="container">

                        <div class="row">
                          
                            <div class="col-md-12 text-right">
                            <a href="{{ $urlData }}" class="btn btn-outline-primary cancle">Cancel</a>
                                    <button class="btn btn-primary addProductSubmit text-white" id="btn_addProductSubmit" type="button">Update</button></p>  
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>

        <!-- Modal -->



</main>

<script type="text/javascript">
    $("a[rel^='prettyPhoto']").prettyPhoto({
        social_tools: ''
    });
</script>

<script type="text/javascript">
    jQuery().ready(function () {

        $('#productMainCategory').val(<?php echo isset($prodcutDetails['category'][0]->category_id) ? $prodcutDetails['category'][0]->category_id : ''; ?>).change();

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
<style>
    body .ui-sortable-helper{

        position: relative !important;
        z-index: inherit !important;
    }
    .pp_pic_holder {
        top: 0% !important;
    }
    .pp_expand{
        display: none !important;
    }
    .hide_section{
        display: none;
    }


</style>
@include('product.shipping.shippingCountriesJs')
@include('product.shipping.addShippingOptions')

@endsection