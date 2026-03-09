@extends('layouts.mobile-main')
<!-- Main Banner Starts -->
@section('content')
@section('title', 'Add product')
<!-- Content Starts -->


<main>
    @include('Store.sidebar')

    @if(Session::has('SuccessMessage')) 

    <div class="alert alert-success alert-success-mbt" role="alert">
        <strong>Well done!</strong> {{ Session::get('SuccessMessage') }}
    </div>
    @endif
    @if ($errors->any())
    <div  class="alert alert-danger" role="alert">
        <strong>Oh snap!</strong> Change a few things up and try submitting again.
    </div>
    @endif
    <div class="alert alert-danger errorpage fade" role="alert" style="display: none">
        <strong><i class="fa fa-exclamation-triangle" aria-hidden="true"></i> </strong> Fill out all of the required fields correctly in order to preview or save
    </div>
    <!-- Dashboard Ends -->
    <div class="dshbrdin mobile-top-spacing-less">
        <div class="container"><!-- hide-when-preview --> 
            <div class="row">
                <div class="col-md-12">
                    <div class="d-flex justify-content-between">
                        <div class="hd-bt"> <h1>Add Product </h1></div>  
                        <div class="add-pr-lnk">
                            <a class="btn btn-primary btn-simple small-btn-mbt" href="{{URL::to('/store-dashboard')}}">
                                Back to Dashboard
                            </a>                                        
                        </div>                        

                    </div>
                </div>   
            </div>
        </div>
       
        <!--<button class="btn btn-primary btn-simple loadShippingOption" id="loadShippingOption" type="button" >Shipping Options</button>-->
        <form id="addProductForm" action="{{ url('add-product') }}" method="post" enctype="multipart/form-data">
            <!-- hide-when-preview --> 
            <!-- Add Product Section Starts -->
            <div class="addprd mt-10 ">
                <div class="container">



                    <div class="row"> 
                        <div class="col-sm-4 lftexp">
                        <h6>Select Category *
                        </h6>
                       <p>Choose appropriate Category for your product</p>
                        </div>
                        <div class="col-md-8 rghtexp">
                            <!--<label class="labled-item"></label>-->
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">

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
                                    <div class="form-group" id="subcategory" style="display: none">

                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group" id="childcategory" style="display: none">

                                    </div>
                                </div>    
                            </div>  
                        </div>
                        <div class="col-sm-4 lftexp">
                        <h6>Product Type *
                        </h6>
                       <p>Select product type if its simple or variable (color, size etc)</p>
                        </div>
                        <div class="col-md-8 rghtexp">
                            <!--<label class="labled-item"></label>-->
                            <div class="col-md-4-large small-width">
                                <div class="form-group">
                                    <select class="form-control" id="Product-type-variant" name="product_type">
                                        <option value="simple" selected="">Simple</option>
                                        <option value="variant">Variable</option>
                                        <option value="virtual">Virtual</option>
                                    </select>
                                </div>
                            </div>
                        </div>   
                        <div class="col-md-12 rghtexp variant colors mt-5 hide-me">
                            @include('product.productvariations')
                        </div>

                    </div>


                    @include('product.attributes')

                    <div class="row">     
                        <div class="card prdsec shippingTab w-100 col-md-12">
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
                                <div class="card-body ">
                   
                                    <div class="row ">
                                        <div class="col-sm-4 lftexp">
                                            <h6>Shipping Options
                                            </h6>
                                                    <p>Set clear and realistic shipping expectations for shoppers by providing accurate processing time and shipping rates.</p>

                                        </div>
                                        <div class="col-md-8 rghtexp">
                                            <div class="shipping-section-container ui-toolkit shipping-option-container">
                                                <div class="list-container-scroll">

                                                    @include('product.shipping.previousShippingOption')
                                                </div>
                                                <!-- panel-repeater-radio -->
                                                <div class="panel-repeater-radio custom-shipping-container br-top-none emphty-space-zero"></div>
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


                    <div class="card-body faqs-body-add no-padd-left ">
                        <div class="row">    
                            <div class="col-md-4 lftexp ">
                                <h6>Setup FAQs</h6>
                                <p class="mb-0">Answer the possible questions shopper may ask before buying the product</p>   
                            </div>  


                            <div class="col-md-8 rghtexp causelist"> 
                                <div class="form-group mb-15">
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

                                </div>
                                <!-- faqs-list-results --> 
                            </div>  
                        </div>

                    </div> 
                </div>  




            </div>
 
    <div class="stop-btn-pos" id="stop-position-fixed"></div>

    <!-- Add Product Section Ends -->

    <input type="hidden"  name="shippingOptionKey" value="">
    <input type="hidden" id="edit_product_id" name="edit_product_id" value="">
     <input type="hidden" id="productVideoURL" name="productVideoURL" value="">

    <!-- Form Option Ends -->

    <div class="container-fluid bxsub ">

        <div class="container">

            <div class="row">
                <div class="col-md-12">
                    <p class="text-left">
                        <button class="btn btn-primary  addProductSubmit save-button btn-primary-background width30 text-white"   saveproduct="active" id="btn_addProductSubmit" type="button">Save</button>    &nbsp;&nbsp;&nbsp;&nbsp;                       
                        <a href="{{ url('store-products') }}" class="">Discard Changes</a>
                        <!--                        <a href="{{ url('brand-products') }}" class=" hide-div blue-text">Preview</a>                                             -->

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

<script>

    $(function () {
        $('#Product-type-variant').change(function () {
            // if product is virtual
         if($(this).val() == 'virtual') {
            $('.shippingTab').hide();
            $('.virtual-hide').hide();
         } else {
            $('.shippingTab').show();
            $('.virtual-hide').show();

         }
            $('.colors').hide();
            $('.' + $(this).val()).show();
        });
    });


    var fixmeTop = $('#stop-position-fixed').offset().top - 460;
    $(window).scroll(function () {
        var currentScroll = $(window).scrollTop();
        if (currentScroll >= fixmeTop) {
            $("#stop-position-fixed").addClass('sticky-strip');
            $("body").addClass('btn-fixed-disable');

        } else {
            $("#stop-position-fixed").removeClass('sticky-strip');
            $("body").removeClass('btn-fixed-disable');

        }
    });


    $('.shipping-section-container input.parent-radio-buttons').click(function () {
        if ($(this).attr('id') === 'custom-shipping') {

            $('.showon-customshipping-select').css("display", "flex");
            $('.cl-bx-mbt').addClass("showdiv");

        } else {
            // $('#show-me').hide();   
            $('.showon-customshipping-select').css("display", "none");
            $('.cl-bx-mbt').removeClass("showdiv");
        }

    });
</script>
@include('product.shipping.shippingCountriesJs')
@include('product.shipping.addShippingOptions')

@endsection