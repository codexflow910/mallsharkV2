@extends('layouts.dashboard-main')
<!-- Main Banner Starts -->
@section('content')
@section('title', 'Manage   ')
<!-- Content Starts -->
<?php
$productType = isset($prodcutDetails['product_type']) ? $prodcutDetails['product_type'] : '';
$prodcutTitle = isset($prodcutDetails['title']) ? $prodcutDetails['title'] : '';
?>
<main>



<div class="flex-parent-div">
        <div class="change-password-page-cmt">
            @include('buyer.customsidebar')

        </div>




    @if(Session::has('SuccessMessage'))

    <div class="alert alert-success alert-success-mbt" role="alert">
        <strong>Well done!</strong> {{ Session::get('SuccessMessage') }}
    </div>
    @endif
    @if ($errors->any())
    <div class="alert alert-danger" role="alert">
        <strong>Oh snap!</strong> Change a few things up and try submitting again.
    </div>
    @endif
    <div class="alert alert-danger errorpage fade" role="alert" style="display: none">
        <strong><i class="fa fa-exclamation-triangle" aria-hidden="true"></i> </strong> Fill out all of the required
        fields correctly in order to preview or save
    </div>
    <!-- Dashboard Ends -->
    <div class="dshbrdin mobile-top-spacing-less addProductpageWrapper home-section cm-edit-ja">

            <!-- Add Product Section Starts -->
            <div class="addprd mt-10 ">


            <div class="container">
            <!-- hide-when-preview -->
            @include('Store.sidebar')
            <div class="row mt-2">
                <div class="col-md-12">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="hd-bt">
                        <h3 class="buyer-order-heading" style="margin-left: 0px;"> 
                <div class="d-flex align-items-center gap-3">                        
                                    <a href="javascript:void(0)" class="d-flex align-items-center gap-10px sidebar-admin-burger-menu" id="open-sidebar-admin-panel">                                        
                                    <span id="menu-toggle-dashboard" class="shopzit-menu mt-0 ml-0 bx-menu">
                                        <span></span><span></span><span></span> <span></span>
                                    </span>
                                    <!-- <span>Menu</span> -->
                                    </a>
                                    <span>Manage Shipping Profiles</span>
                                    </div>    
                
                </h3>

                        
                        </div>
                        <div class="add-pr-lnk manageShippingBtn">
                            
                            <a class="btn btn-primary btn-simple small-btn-mbt bigIcon" href="{{URL::to('/store-dashboard')}}">
                               Back to Dashboard
                            </a>
                        </div>

                    </div>
                </div>
            </div>
        </div>

                <div class="container">
                    <div class="container-outrer">

                        <div class="row">
                            <div class="card prdsec shippingTab w-100 col-md-12">
                                <div class="card-header" role="tab" id="" style="display:none">

                                </div>
                                <!--   <div id="collapsefive" class="panel-collapse  collapse show" role="tabpanel" aria-labelledby="headingfive" data-parent="#accordion">-->
                                <div class="card-body " style="margin-top: 30px!important;">

                                    <div class="row ">
                                        <div class="col-sm-12 lftexp">
                                            <h6>Shipping Options
                                            </h6>
                                            <p>Set clear and realistic shipping expectations for shoppers by providing
                                                accurate processing time and shipping rates.</p>

                                        </div>
                                        <div class="col-md-12 rghtexp">
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
                                        <div class="addCustomShip">
                                            <a href="javascript:void(0);" id="add-shipping-profile-custom" class="defaultbtn add-shipping-profile">Create Custom Shipping Profiles</a>
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
                                   
                                     
                                    </div>

                                </div>
   
                            </div> <!-- card prdsec -->
                        </div>

                        <input type="hidden" name="shippingOptionKey" value="">
                        <input type="hidden" id="edit_product_id" name="edit_product_id" value="">
                        <input type="hidden" id="productVideoURL" name="productVideoURL" value="">

                        <!-- Form Option Ends -->
                    </div>
                    <!-- Ends container-outrer -->
                </div>
            </div>

            <div class="stop-btn-pos" id="stop-position-fixed"></div>
    </div><!-- dshbrdin -->

</div>
</main>
<!-- Content Ends-->

<style>
    body .ui-sortable-helper {
        position: relative !important;
        z-index: inherit !important;
    }

    .product-slide-values .slick-slide {
        display: block !important;
    }

    .values_product_visibility {
        display: none;
    }

    .pp_pic_holder {
        top: 0% !important;
    }

    .pp_expand {
        display: none !important;
    }
</style>
<script src="{{ URL::asset('assets/js/product-script.js' ,env('HTTPS_STATUS'))}}" type="text/javascript"
charset="utf-8"></script>
<script>
    $('input[name=productTags]').tagsinput({
        maxChars: 20,
        maxTags: 10,
    });
    $(function() {
        $('#Product-type-variant').change(function() {
            $('.colors').hide();
            // if product is virtual
            if ($(this).val() == 'virtual') {
                $('input[name=previousShippingOptions]').prop('checked', false);

                $('.shippingTab').hide();
                $('.virtual-hide').hide();
            } else {
                $('.shippingTab').show();
                $('.virtual-hide').show();
            }
            $('.' + $(this).val()).show();
        });
    });


    var fixmeTop = $('#stop-position-fixed').offset().top - 460;
    $(window).scroll(function() {
        var currentScroll = $(window).scrollTop();
        if (currentScroll >= fixmeTop) {
            $("#stop-position-fixed").addClass('sticky-strip');
            $("body").addClass('btn-fixed-disable');

        } else {
            $("#stop-position-fixed").removeClass('sticky-strip');
            $("body").removeClass('btn-fixed-disable');

        }
    });


    $('.shipping-section-container input.parent-radio-buttons').click(function() {
        if ($(this).attr('id') === 'custom-shipping') {

            $('.showon-customshipping-select').css("display", "flex");
            $('.cl-bx-mbt').addClass("showdiv");

        } else {
            // $('#show-me').hide();   
            $('.showon-customshipping-select').css("display", "none");
            $('.cl-bx-mbt').removeClass("showdiv");
        }

    });

    $('#btn_addAnotherProduct').click(function() {
        $('#another_product_add').val(1);
    });
    $('#btn_finishAddingProduct').click(function() {
        $('#another_product_add').val(0);
    });


    $(document).on("click", "#delete_shipping_profile", function() {
        var row = $(this).closest(".removingShipping");
        $('#save_shipping_custom input[type="text"]').val('');
        $('#save_shipping_custom select').val('');
        $('#save_shipping_custom input[type="checkbox"]').prop('checked', false);
        var res = {}

        var shipping_id = $(this).attr('data-shipping-id');
        res['ship_id'] = shipping_id;
        
        swal({
                title: "Are you sure?",
                text: "Are you sure you want to Delete Shipping Profile",
                type: "warning",
                showCancelButton: true,
                confirmButtonClass: "btn-success",
                confirmButtonText: "Yes, Do it!",
                closeOnConfirm: false
            },
            function() {
                
                $.ajax({
                    type: 'POST',
                    url: '{{url('deleteshipping')}}',
                    data: res,
                    success: function(response) {
                        row.slideUp("slow", function() {
                                row.remove(); // Remove the row after animation
                            });      
                        swal("Success!", response.message, "success");       
                    },
                    error: function(xhr, textStatus, errorThrown) {
                        console.log('Error: ' + errorThrown); // handle any errors that occur
                        swal("Error!", errorThrown, "error");
                    }
                });
                
            });

    });
    // jQuery('body').on('click', '.add-shipping-profile', function (e) {

    //     $('#save_shipping_custom_page').modal('show');
    //     // $(this).parent().addClass('open-body-detail');
    //  });
</script>
@include('product.shipping.shippingCountriesJs')
@include('product.shipping.addShippingOptions')
@endsection