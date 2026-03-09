@extends('layouts.dashboard-main')
<!-- Main Banner Starts -->
@section('content')
@section('title', 'Add product')
<!-- Content Starts -->
<style>

.CSVProductsScrollbar::-webkit-scrollbar {
  width: 20px;
  margin-top:10px;
}

.CSVProductsScrollbar::-webkit-scrollbar-track {
  box-shadow: inset 0 0 5px grey; 
  border-radius: 10px;
}
 
.CSVProductsScrollbar::-webkit-scrollbar-thumb {
  background: rgba(0,0,0,.05);
  border-radius: 10px;
}

    .import-checkbox {
        position: relative !important;
        left: 0px !important;
    }


    #loading {
        position: fixed;
        display: block;
        width: 100%;
        height: 100%;
        opacity: 0.7;
        background-color: #fff;
        z-index: 99;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        display: none
    }

    #loading-image {
        position: absolute;
        top: 50%;
        z-index: 100;
        width: 150px;
        left: 50%;
        transform: translate(-50%,-50%);
    }
    .humber{
        background-color: #0000a7!important;
    }
    /* #menu-toggle-dashboard{
        margin-right: 10px;
    } */
    #open-sidebar-admin-panel{
            margin-top: 5px;
            display: block!important;
        }

    @media screen and (max-width: 573px){
        #open-sidebar-admin-panel{
            margin-top: -53px;
        }
    }
    @media (min-width: 768px){
        .col-md-8 {
            max-width: 100%;
         }
        }
</style>
<div class="change-password-page-cmt">
    @include('buyer.customsidebar')
  
  </div>
<main>
    @php
        // if (session()->has('userData')) {
        //   $userData = session()->get('userData', 'default');
        // }
    @endphp
    <!-- Dashboard Ends -->
    <div class="dshbrdin mobile-top-spacing-less cm-edit-ja">
        <div class="container mncntnr">
            @include('Store.sidebar')

            <div id="loading" class="hide-setion">
                <img id="loading-image" src="{{ asset('assets/images/Loader-Animation.gif') }}" alt="Loading..." />
            </div>
            <div class="row">
                <div class="col-12 " style="padding: 0px;">
                    <div class="hd-bt" style="margin-top: 25px;">
                        <h3 style="margin-bottom: 25px;">
                        <div class="d-flex align-items-center gap-3">                        
                                    <a href="javascript:void(0)" class="d-flex align-items-center gap-10px sidebar-admin-burger-menu" id="open-sidebar-admin-panel">                                        
                                    <span id="menu-toggle-dashboard" class="shopzit-menu mt-0 ml-0 bx-menu">
                                        <span class="humber"></span><span class="humber"></span ><span  class="humber"></span><span  class="humber"></span>
                                    </span>
                                    <!-- <span>Menu</span> -->
                                    </a>
                                    <span style="font-size: 35px;font-weight:bold;">Import Your Products From<img  style="margin-left: 10px; position:relative; top:-5px; width:120px"
                                        src="{{ asset('assets/images/shopify.png') }}"> </span>
                                    </div>    
            
                           </h3>
                        </div>

                    <div class="ShopifyCont <?php echo ($stripe === 0) ? '' : 'mb-4'; ?>" role="alert">
                        <h3 class="mb-2" style="color:#bd1d1d!important;">Prerequisites for Importing Data</h3>
                        <p style="color:#856404">Your products in shopify must have the following data fields filled out
                            before they can be brought over to MallShark. If the data has errors, or any of the fields
                            mentioned below are empty, please fill them out on your shopify store and then click the
                            refresh button. Our system will detect and load them for import.</p>
                        <ul class="pl-3" style="font-size:14px">
                            <li style="list-style: circle;">Title</li>
                            <li style="list-style: circle;">Price</li>
                            {{-- <li style="list-style: circle;">Current Stock</li> --}}
                            {{-- <li style="list-style: circle;">Weight</li> --}}
                            <li style="list-style: circle;">Store Currency : USD</li>
                            {{-- <li style="list-style: circle;">SKU of each Variant</li>  --}}
                        </ul>

                    </div>
                    @if ($stripe == 0)
                        <div class="alert alert-warning" role="alert" style="padding:0.7rem!important;margin-top:-17px!important;margin-bottom:20px!important;">
                            <h3 class="mb-2" style="color:#bd1d1d;font-weight:normal!important;">To use our product import tool. It is necessary to
                                connect your bank account with our payment gateway.<br> Please fill out your banking
                                details by clicking <a href="{{ URL::to('/store/payments') }}"> <strong>here
                                    </strong><a>. </h3>
                        </div>
                    @endif


                    <div class="sttbr row" style="width: 100%;">
                        <span class="CSVProductsScrollbar" style="overflow-x: auto!important;width:100%;">
                        <div class="col-8 nopad" style="padding: 0px;">
                            <div class="  col-12 justify-content-between">

                                <div class="row">
                                    <div class="form-group col-md-8" style="margin-bottom: 0px;padding:0px 0px 0px 5px;">
                                        <input class="form-control" {{ $stripe == 0 ? 'disabled' : '' }} type="file" placeholder="Import csv file"
                                            name="csv_file" id="csvFile" style="border-radius: 30px;">
                                        <span class="error d-none fileInputError">Csv File required</span>
                                        <span class="error d-none headerMissingError">Headers must include handle,title,and price.</span>
                                        <span class="error d-none fileDataError">some products has invalid data.</span>
                                    </div>
                                    <div class="form-group col-md-4 disabled" style="margin-bottom: 0px;">
                                        <a class="shpbtn" style="{{ $stripe == 0 ? ' pointer-events: none; background-color:#0510fa40;' : '' }}" href="#" id="importShopifyCsv">Import CSV</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul>
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <div class="content">

                        </div>
                        <div class="alert alert-danger import-error" style="display: none; font-size:16px"
                            role="alert">Failed to fetch products</div>
                        @if ($foundError)
                            <div class="alert alert-danger found-error" style="display: Block; font-size:16px"
                                role="alert">Failed! Shopify Store currency should be USD</div>
                        @endif


                        <!-- Table Ends -->
                        {{-- <input type="checkbox" class="shopifyCheckBox" >
          @if (Session::has('userData') && isset(Session::get('userData')['accessToken']) && isset(Session::get('userData')['shopUrl']))
          <div class="text-right" style="margin-top: 30px; margin-bottom:30px"><a class="btn btn-primary" id="addToMallshark"
              style="border:0px; background-color:#202020; color:#fff; border-radius:0px">Add Products To MallShark</a>
          </div>
          @endif --}}
        </span>

                    </div>
                </div>
            </div>

        </div><!-- dshbrdin -->



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
<script>

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    $('#importShopifyCsv').on('click', function(e) {
        e.preventDefault();
        // Get the CSV file input element
        var csvFileInput = document.getElementById('csvFile');
        var file = csvFileInput.files[0];
        var allowedExtensions = /(\.csv)$/i;
        if (file && file.name.endsWith('.csv')) {
            // debugger
            $('#loading').show();
            $('.fileInputError').addClass('d-none');
            $('.headerMissingError').addClass('d-none');
            var formData = new FormData();
            formData.append('csv_file', file);
            // Make the AJAX request
            $.ajax({
                url: "{{ route('admin.save.csv') }}",
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(data) {
                    if (data?.status) {
                        // debugger
                        $('#loading').hide()
                        // Handle success response here
                        $('.content').html(data?.view);
                        $('.import-error').hide()
                        $('#importProducts').text('Refresh Products')
                    } else {
                        // debugger
                        // Handle error response here
                        $('#loading').hide()
                        var errorMessage = 'uploading the file.';
                        csvFileInput.value = ''; 
                        $('.headerMissingError').removeClass('d-none');
                        $('.content').empty();
                    }
                },
                error: function(foundError) {
                    // debugger
                    // Handle AJAX error here
                    $('#loading').hide()
                    console.log('error', foundError);
                    csvFileInput.value = ''; 
                    $('.headerMissingError').removeClass('d-none');
                    $('.content').empty();
                }
            });
        } else {
            $('#loading').hide()
            csvFileInput.value = ''; 
            $('.fileInputError').removeClass('d-none');
            $('.content').empty();

        }
    });
    $('#importProducts').on('click', function(e) {
        e.preventDefault();
        $('#loading').show()

        $.ajax({
            url: "{{ route('importallproducts') }}",
            type: 'get',
            success: function(data) {
                if (data?.status) {
                    $('.content').html(data?.view);
                    $('.import-error').hide()
                    $('#loading').hide()
                    $('#importProducts').text('Refresh Products')
                } else {

                    $('.content').html(null);
                    $('.import-error').show()
                    $('#loading').hide()
                }
            },
            error: function(error) {
                $('#loading').hide()
                console.log('error', error)
            }
        })
        $('#loading').hide()

    })

    function sCategoryChanged(e) {
        let sCategoryId = e.dataset.key;
        let mCategoryId = e.value;
        if (sCategoryId && mCategoryId) {
            $('select.category.' + sCategoryId).each(function() {
                $(this).val(mCategoryId)
            });


        }

    }

    function naCategoryChanged(e) {
        let mCategoryId = e.value;
        if (mCategoryId) {
            $('select.category.NA').each(function() {
                $(this).val(mCategoryId)
            });


        }

    }

    function profileChanged(e) {
        let sKey = e.dataset.key;
        let mProfileId = e.value;
        if (sKey && mProfileId) {
            let set = $('select.profile.' + sKey).each(function() {
                if (!$(this).hasClass('no-weight')) {

                    $(this).val(mProfileId)
                }
            })

        }
    }

    function naProfileChanged(e) {

        let mProfileId = e.value;
        if (mProfileId) {

            let set = $('select.profile.NA').each(function() {
                if (!$(this).hasClass('no-weight')) {

                    $(this).val(mProfileId)
                }
            })

        }
    }
</script>

@endsection
