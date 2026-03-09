@extends('layouts.dashboard-main')
<!-- Main Banner Starts -->
@section('content')
@section('title', 'Bulk Update Products')
<!-- Content Starts -->
<style>
    .CSVProductsScrollbar::-webkit-scrollbar {
        width: 20px;
        margin-top: 10px;
    }

    .CSVProductsScrollbar::-webkit-scrollbar-track {
        box-shadow: inset 0 0 5px grey;
        border-radius: 10px;
    }

    .CSVProductsScrollbar::-webkit-scrollbar-thumb {
        background: rgba(0, 0, 0, .05);
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
        transform: translate(-50%, -50%);
    }

    .humber {
        background-color: #0000a7 !important;
    }

    /* #menu-toggle-dashboard{
        margin-right: 10px;
    } */
    #open-sidebar-admin-panel {
        margin-top: 5px;
        display: block !important;
    }

    @media screen and (max-width: 573px) {
        #open-sidebar-admin-panel {
            margin-top: -53px;
        }
    }

    @media (min-width: 768px) {
        .col-md-8 {
            max-width: 100%;
        }
    }
</style>
<div class="change-password-page-cmt">
    @include('buyer.customsidebar')

</div>
<main>

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
                        <h3 style="margin-bottom: 25px; width:100%; display: flex; align-items: center; justify-content: space-between;">
                            <div class="d-flex align-items-center gap-3">
                                <a href="javascript:void(0)" class="d-flex align-items-center gap-10px sidebar-admin-burger-menu" id="open-sidebar-admin-panel">
                                    <span id="menu-toggle-dashboard" class="shopzit-menu mt-0 ml-0 bx-menu">
                                        <span class="humber"></span><span class="humber"></span><span class="humber"></span><span class="humber"></span>
                                    </span>
                                    <!-- <span>Menu</span> -->
                                </a>
                                <span style="font-size: 35px;font-weight:bold;">
                                    Import 
                                    <img style="position:relative; top:-5px; height: 50px; width:50px" src="{{ asset('assets/images/csv-icon.png') }}"> 
                                    CSV To Update Products
                                </span>
                            </div>
                            <a class="btn btn-primary btn-simple small-btn-mbt" href="{{ URL::to('/store-products') }}">
                                Back to Store Products
                            </a>
                        </h3>

                    </div>

                    <div class="ShopifyCont" role="alert">
                        <h3 class="mb-2" style="color:#bd1d1d!important;">When you upload a CSV file, only these below fields will be updated. Other fields will not be affected.</p>
                            <ul class="pl-3" style="font-size:14px">
                                <li style="list-style: circle;">Price</li>
                                <li style="list-style: circle;">Inventory</li>
                            </ul>

                    </div>
                    <div class="sttbr row" style="width: 100%;">
                        <span class="CSVProductsScrollbar" style="overflow-x: auto!important;width:100%;">
                            <div class="col-8 nopad" style="padding: 0px;">
                                <div class="col-12 justify-content-between">
                                    <div class="row form-group">
                                        <!-- File Input and Errors -->
                                        <div class="form-group col-md-8" style="margin-bottom: 0px;padding:0px 0px 0px 5px;">
                                            <input class="form-control" type="file" placeholder="Import csv file"
                                                name="csv_file" id="csvFile" style="border-radius: 30px;">
                                            <!-- Error Messages Below Input -->
                                            <span class="error d-none fileInputError"></span>
                                            <span class="error d-none headerMissingError"></span>
                                            <span class="error alert-danger d-none import-error"></span>
                                            <span class="error d-none fileDataError"></span>
                                        </div>
                                        <div class="form-group col-md-4" style="margin-bottom: 0px;">
                                            <a class="shpbtn" href="#" id="importCsv">Import CSV</a>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Success or Error Content -->
                            <div class="content"></div>
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

    $('#importCsv').on('click', function (e) {
        e.preventDefault();

        var csvFileInput = document.getElementById('csvFile');
        var file = csvFileInput.files[0];

        if (file && file.name.endsWith('.csv')) {
            $('#loading').show();
            $('.fileInputError, .headerMissingError, .import-error, .fileDataError').addClass('d-none').text('');

            var formData = new FormData();
            formData.append('csv_file', file);

            $.ajax({
                url: "{{ route('bulk-update-products') }}",
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function (data) {
                    $('#loading').hide();

                    if (data?.status) {
                        $('.content').html('<p>Products updated successfully.</p>');
                        window.location.href = '/store-products';   
                    } else {
                        // Error: Show the general error message
                        $('.headerMissingError').removeClass('d-none').text(data?.message || 'Uploading the file failed.');

                        csvFileInput.value = ''; 
                    }
                },
                error: function (xhr) {
                    $('#loading').hide();
                    
                    // Check if there are errors in the server response
                    if (xhr?.responseJSON?.errors?.length) {
                        // Create a detailed error message HTML string
                        var detailedErrors = '';
                        xhr.responseJSON.errors.forEach(function (error) {
                            detailedErrors += `<p>${error}</p>`;
                        });

                        // Show the error messages in the .import-error div
                        $('.import-error').removeClass('d-none').html(detailedErrors);
                    } else {
                        // If no detailed errors, show a fallback message
                        $('.import-error').removeClass('d-none').text('An unexpected error occurred.');
                    }

                    // Clear the file input for retry
                    csvFileInput.value = ''; 
                }

            });
        } else {
            // Show error if file is missing or invalid
            $('.fileInputError').removeClass('d-none').text('Please select a valid CSV file.');
            csvFileInput.value = ''; // Clear the file input for retry
        }
    });
</script>


@endsection