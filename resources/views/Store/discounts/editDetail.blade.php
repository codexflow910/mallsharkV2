@extends('layouts.dashboard-main')
@section('content')

    <head>


    </head>
    <style>
        #productAccordion1 input {
            width: auto !important;
        }

        #discountTab {
            margin-bottom: 20px;
            margin-left: 10px;
            display: flex;
            gap: 20px;
        }

        #discount-code-tab,
        #auto-discount-tab {
            font-size: 18px;
            font-weight: bold
        }

        .noshow {
            display: none
        }

        .tab-content {
            margin-top: 20px;
        }

        .form-group {
            position: relative;
        }

        .generate-code {
            position: absolute;
            right: 0;
            top: 0;
            cursor: pointer;
            color: #0510fa;
            text-decoration: none;
            padding: 6px;
        }

        .discount-input {
            display: none;
        }

        .form-check-custom {
            display: flex;
            align-items: center;
            height: 2.3rem;
            /* Set a fixed height for each row */
        }

        .form-check-custom input[type="number"] {
            width: 70px;
            height: calc(1em + .5rem + 2px);
            /* Bootstrap's small input height */
            padding: .15rem .5rem;
            font-size: .875rem;
            margin-left: 0.5rem;
        }

        .toggle-arrow {
            float: right;
            cursor: pointer;
            font-size: 12px
        }

        .form-control {
            box-shadow: inset 0px 3px 6px #00000029;
            border: 1px solid #0510FA;
            border-radius: 20px;
            padding: 9px;
        }

        .mdldsc input,
        .bdrbxds input {
            width: auto;
        }

        .mdldsc .card {
            margin-bottom: 3px
        }

        .bdrbxds {
            border: 1px solid #d3d3d3;
            border-radius: 12px;
            padding: 15px;
            margin-bottom: 20px
        }

        #startDatePicker,
        #endDatePicker {
            border-radius: 36px;
            overflow: hidden;
        }

        #productAccordion1 .card {
            margin-bottom: 3px !important;
        }

        #subCategoriesModal1 input {
            width: auto !important;
        }

        .tooltip-icon {
            color: #9B9B9B;
            cursor: pointer;
            margin-left: 5px;
            position: relative;
        }

        .tooltip-text {
            display: none;
            position: absolute;
            background-color: #333333;
            border: 1px solid #ccc;
            padding: 10px;
            top: 30px;
            left: 0;
            border-radius: 12px;
            z-index: 1;
            max-width: 400px;
        }

        p.photoNotification {
            color: #fff;
        }

        .tooltip-icon:hover+.tooltip-text {
            display: block;
        }

        .datepicker.datepicker-dropdown.dropdown-menu.datepicker-orient-left.datepicker-orient-top {
            padding: 13px !important;
            display: none;
        }

        .datepicker.datepicker-dropdown.dropdown-menu.datepicker-orient-left.datepicker-orient-bottom {
            padding: 13px !important;
            display: none;
        }

        .datepicker table {
            border-collapse: collapse;
            width: 275px;
            padding: 42px !important;
            line-height: 35px;
            border-spacing: 5px;
        }

        .datepicker table tr td {
            border-radius: 2px;
            background-color: #f6f6f6;
            border: 1px solid #c5c5c5;
            font-weight: normal;
            color: #454545;
            text-align: center;
            cursor: pointer;
        }

        .datepicker table>thead>tr:nth-child(2)>th.datepicker-switch {
            background: #f6f6f6;
            border: 1px solid #c5c5c5;
            text-align: center;
        }

        .datepicker .datepicker-days>table>thead>tr:nth-child(2)>th.prev,
        .datepicker table>thead>tr:nth-child(2)>th.next {
            text-align: center;
        }

        .datepicker table tr td.active,
        .datepicker table tr td.active:hover {
            background-color: #0510FA !important;
            color: white;
        }

        .day.disabled,
        .month.disabled,
        .year.disabled,
        .start-date-active.disabled {
            cursor: not-allowed;
            filter: alpha(opacity=65);
            -webkit-box-shadow: none;
            box-shadow: none;
            opacity: .65;
        }

        .nav-tabs .nav-item.show .nav-link,
        .nav-tabs .nav-link.active {
            border: none;
            border-bottom: 2px solid #0510FA;
        }

        .nav-tabs .nav-link:hover {
            border: none;
        }

        .nav-tabs {
            border: none;
        }

        .nav-tabs .nav-link {
            padding-left: 0px;
            padding-right: 0px;
            border: none;

        }

        #discount-code-tab,
        #auto-discount-tab {
            font-size: 19px;
        }

        .discount-dashboard-title {
            font-size: 35px;
            font-weight: bold;
            color: #183b56;
        }

        .disabled-date {
            color: #d9534f !important;
            pointer-events: none;
        }
    </style>

    <div class="flex-parent-div">
        <div class="change-password-page-cmt">
            @include('buyer.customsidebar')
        </div>
        @if (Session::has('SuccessMessage'))
            <?php $successMessage = Session::get('SuccessMessage'); ?>
            <script>
                toastr.options = {
                    closeButton: true,
                    tapToDismiss: true,
                    positionClass: 'toast-top-right',
                    toastClass: 'toast-icon custom-toast-success',
                    progressBar: true,
                };
                toastr.success('<?php echo $successMessage; ?> ');
            </script>
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
        <div
            class=" dshbrdin store-dashboard pro-dsb-cntainer home-section cm-edit-ja overflow-y-none mobile-adjust-scroll campaigns-page">
            <div class="container">
                <div class="row mt-15">
                    <div class="col-sm-12 col-12">
                        <h6 class="store-status-mbt">
                            <div class="d-flex align-items-center ">
                                <a href="javascript:void(0)"
                                    class="d-flex align-items-center gap-10px sidebar-admin-burger-menu"
                                    id="open-sidebar-admin-panel">
                                    <span id="menu-toggle-dashboard" class="shopzit-menu mt-0 ml-0 bx-menu"
                                        style="margin-right: 5px;">
                                        <span></span><span></span><span></span> <span></span>
                                    </span>&nbsp;&nbsp;
                                </a>
                                <span class="discount-dashboard-title">Edit Discount</span>
                            </div>
                        </h6>
                    </div>
                </div>
                <div class="container-outrer mt-4">
                    <form id="discountUpdateForm">
                        <!------------Code Starts---------------->
                        <div class="container mt-5">
                            <input id="discount_id" type="hidden" name="discount_id" value="{{ $discount->id }}">


                            <ul class="nav nav-tabs" id="discountTab" role="tablist">
                                <li class="nav-item">
                                    <a class="nav-link {{ $discount->code != null ? 'active' : '' }}" id="discount-code-tab"
                                        data-toggle="tab" href="#discount-code" role="tab" aria-controls="discount-code"
                                        aria-selected="true">Coupons</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link {{ $discount->title != null ? 'active' : '' }}"
                                        id="auto-discount-tab" data-toggle="tab" href="#auto-discount" role="tab"
                                        aria-controls="auto-discount" aria-selected="false">Sales</a>
                                </li>
                            </ul>
                            <input type="hidden" name="discountActive" id="discountActive"
                                value="{{ $discount->title != null ? 'Sales' : 'Coupons' }}">
                            <div class="tab-content" id="discountTabContent">
                                <div class="tab-pane fade show {{ $discount->code != null ? 'active show' : '' }}"
                                    id="discount-code" role="tabpanel" aria-labelledby="discount-code-tab">
                                    <div class="form-group col-md-6">
                                        <label for="discountCode">Discount Code</label>
                                        <i class="fa fa-info-circle tooltip-icon"></i>
                                        <div class="tooltip-text">
                                            <p class="photoNotification">
                                            </p>
                                        </div>
                                        <span class="generate-code" id="generateCode">Generate Random Code</span>
                                        <input name="discountCode" type="text" class="form-control" id="discountCode"
                                            placeholder="Add Coupon Code" maxlength="100" value="{{ $discount->code }}">
                                        <small class="form-text text-muted">Customers must enter this code at
                                            checkout.</small>
                                    </div>
                                </div>
                                <div class="tab-pane fade {{ $discount->title != null ? 'active show' : '' }}"
                                    id="auto-discount" role="tabpanel" aria-labelledby="auto-discount-tab">
                                    <div class="form-group col-md-6">
                                        <label for="autoDiscountTitle">Title</label>
                                        <input name="autoDiscountTitle" type="text" class="form-control"
                                            id="autoDiscountTitle" placeholder="Type Discount Title" maxlength="100"
                                            value="{{ $discount->title }}">
                                            <span id="titleError" class="text-danger"></span>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group col-md-6">
                                <label for="discountAppliesOn">Discount Applies On</label>
                                <i class="fa fa-info-circle tooltip-icon"></i>
                                <div class="tooltip-text">
                                    <p class="photoNotification">Upload Gallery and Variation Images: Gallery images
                                        display your product from various angles, while variation images show
                                        different options like colors or sizes.
                                    </p>
                                </div>
                                <select name="discount_applicable" class="form-control" id="discountAppliesOn">
                                    <option {{ $discount->platform_wide == true ? 'selected' : '' }} value="wholeInventory">
                                        Whole Inventory & Whole Cart Order</option>
                                    <option value="categories">Discount on Categories</option>
                                    <option value="products">Discount on Products</option>
                                </select>
                            </div>

                            <div id="discountCategoriesSection" class="form-group" style="display: none;">
                                <label for="discountCategories" class="mt-1 mr-2">Select Categories</label>
                                <button type="button" class="btn btn-sm" id="browseCategories"
                                    style="background-color: #EDB83F; padding: 5px 20px">Click to select categories
                                </button>
                                <ul id="selectedCategories" class="list-group mt-2"></ul>
                            </div>

                            <div id="discountProductsSection" class="form-group" style="display: none;">
                                <label for="discountProducts" class="mt-1 mr-2">Browse Products</label>
                                <button type="button" class="btn btn-sm " id="browseProducts"
                                    style="background-color: #EDB83F; padding: 5px 20px">Click to select products
                                </button>
                                <ul id="selectedProducts" class="list-group mt-2"></ul>
                            </div>

                            <!-- Categories Modal -->
                            <div class="modal fade mdldsc" id="categoriesModal" tabindex="-1" role="dialog"
                                aria-labelledby="categoriesModalLabel" aria-hidden="true">
                                <div class="modal-dialog" role="document">
                                    <div class="modal-content" style="max-height: 600px;overflow: auto;">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="categoriesModalLabel">Choose Categories</h5>
                                            <button type="button" class="close" data-dismiss="modal"
                                                aria-label="Close">
                                                <span aria-hidden="true">&times;</span>
                                            </button>
                                        </div>
                                        <div class="modal-body" id="categoriesModalBody2">
                                            <div class="form-group">
                                                <!-- Add more categories as needed -->
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close
                                            </button>
                                            <button type="button" class="btn btn-primary" id="saveCategories">Done
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- Products Modal -->
                            <div class="modal fade mdldsc" id="productsModal" tabindex="-1" role="dialog"
                                aria-labelledby="productsModalLabel" aria-hidden="true">
                                <div class="modal-dialog" role="document">
                                    <div class="modal-content" style="max-height: 600px; overflow: auto">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="productsModalLabel">Choose Products</h5>
                                            <button type="button" class="close" data-dismiss="modal"
                                                aria-label="Close">
                                                <span aria-hidden="true">&times;</span>
                                            </button>
                                        </div>
                                        <div class="modal-body">
                                            <input type="text" id="productSearch" class="form-control"
                                                placeholder="Search Products" style="width: 100%;">
                                            <div class="accordion mt-3" id="productAccordion">

                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close
                                            </button>
                                            <button type="button" class="btn btn-primary" id="saveProducts">Done
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- Exclude By Section -->
                            <div
                                style="background-color: #f3f3f3; padding: 20px; margin-bottom: 20px; margin-top: 20px;border-radius:12px;position:relative;">
                                <div class="mb-4">
                                    <h5><label>Exclude By:</label> <i class="fa fa-info-circle tooltip-icon"></i>
                                        <div class="tooltip-text" style="top:42px;line-height:20px;">
                                            <p class="photoNotification">Upload Gallery and Variation Images: Gallery
                                                images
                                                display your product from various angles, while variation images show
                                                different options like colors or sizes.
                                            </p>
                                        </div>
                                    </h5>

                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input noshow" type="radio" name="excludeOption"
                                            id="excludeProducts1" value="products" checked="">
                                        <label class="form-check-label" for="excludeProducts1">Products</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input noshow" type="radio" name="excludeOption"
                                            id="excludeSubCategories1" value="subcategories">
                                        <label class="form-check-label" for="excludeSubCategories1">Sub
                                            Category(s)</label>
                                    </div>
                                </div>

                                <!-- Select Products Section -->
                                <div id="productsContainer1" style="margin-bottom: 20px;">
                                    <button class="btn btn-primary" type="button" data-toggle="modal"
                                        data-target="#productsModal1">Select Products
                                    </button>
                                </div>

                                <!-- Select Sub Categories Section -->
                                <div id="subCategoriesContainer1" class="d-none">
                                    <button type="button" class="btn btn-primary mb-3" id="selectSubCategories">Select
                                        Sub Categories
                                    </button>
                                </div>

                                <!-- Items List Starts Here -->
                                <ul id="selectedItems1" class="list-group">
                                    <!-- Selected items will be appended here -->
                                </ul>
                                <!-- Items List Ends Here -->

                                <!-- Categories Modal Start -->
                                <div class="modal fade" id="categoriesModal1" tabindex="-1" role="dialog"
                                    aria-labelledby="categoriesModalLabel1" aria-hidden="true">
                                    <div class="modal-dialog" role="document">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="categoriesModalLabel1">Choose
                                                    Categories</h5>
                                                <button type="button" class="close" data-dismiss="modal"
                                                    aria-label="Close">
                                                    <span aria-hidden="true">&times;</span>
                                                </button>
                                            </div>
                                            <div class="modal-body" id="categoriesModalBody1">
                                                <div class="form-group">
                                                    <!-- Add more categories as needed -->
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary"
                                                    data-dismiss="modal">Close
                                                </button>
                                                <button type="button" class="btn btn-primary" id="saveCategories1">Done
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- Categories Modal Ends -->

                                <!-- Sub Categories Modal Start -->
                                <div class="modal fade" id="subCategoriesModal1" tabindex="-1" role="dialog"
                                    aria-labelledby="subCategoriesModalLabel1" aria-hidden="true">
                                    <div class="modal-dialog" role="document">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="subCategoriesModalLabel1">Choose Sub
                                                    Categories</h5>
                                                <button type="button" class="close" data-dismiss="modal"
                                                    aria-label="Close">
                                                    <span aria-hidden="true">&times;</span>
                                                </button>
                                            </div>
                                            <div class="modal-body" id="categoriesModalBody">
                                                <div class="form-group">
                                                    <!-- Add more sub categories as needed -->
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary"
                                                    data-dismiss="modal">Close
                                                </button>
                                                <button type="button" class="btn btn-primary"
                                                    id="saveSubCategories1">Done
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- Sub Categories Modal Ends -->

                                <!-- Products Modal Starts -->
                                <div class="modal fade" id="productsModal1" tabindex="-1" role="dialog"
                                    aria-labelledby="productsModalLabel1" aria-hidden="true">
                                    <div class="modal-dialog" role="document">
                                        <div class="modal-content" style="max-height: 600px;overflow: auto;">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="productsModalLabel1">Choose Products</h5>
                                                <button type="button" class="close" data-dismiss="modal"
                                                    aria-label="Close">
                                                    <span aria-hidden="true">&times;</span>
                                                </button>
                                            </div>
                                            <div class="modal-body">
                                                <input type="text" id="productSearch1" class="form-control"
                                                    placeholder="Search Products" style="width: 100%;">
                                                <div class="accordion mt-3" id="productAccordion1">

                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary"
                                                    data-dismiss="modal">Close
                                                </button>
                                                <button type="button" class="btn btn-primary" id="saveProducts1">Done
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- Products Modal Ends -->
                            </div>
                            <div class="container">
                                <div class="row"
                                    style="background-color: #f3f3f3; padding: 20px; margin-bottom: 20px; margin-top: 20px; border-radius:12px;">
                                    <div class="col-md-4">
                                        <div class="form-group mb-0">
                                            <label for="discountType">Set Discount Type</label>
                                            <select name="discount_type" class="form-control" id="discountType">
                                                <option {{ $discount->discount_value_type == 'percent' ? 'selected' : '' }}
                                                    value="percentage">Percentage</option>
                                                <option {{ $discount->discount_value_type == 'fixed' ? 'selected' : '' }}
                                                    value="fixed">Fixed amount</option>
                                            </select>
                                        </div>
                                        <div style="{{ $discount->discount_value_type == 'percent' ? 'display: block;' : 'display: none;' }}"
                                            class="form-group discount-input mt-2" id="percentageInput">
                                            <label for="percentageValue">Percentage (%)</label>
                                            <input name="percentage_value" type="number" class="form-control"
                                                id="percentageValue" placeholder="%" min="1" max="99"
                                                value="{{ $discount->discount_value_type == 'percent' ? $discount->discount_value : '' }}">
                                                <span class="percentage-error d-none">disocunt amount is invalid</span>
                                        </div>
                                        <div style="{{ $discount->discount_value_type == 'fixed' ? 'display: block;' : 'display: none;' }}"
                                            class="form-group discount-input mt-2" id="fixedInput">
                                            <label for="fixedValue">Fixed amount ($)</label>
                                            <input min="0" name="fixed_value" type="number" class="form-control"
                                                id="fixedValue" placeholder="$"
                                                value="{{ $discount->discount_value_type == 'fixed' ? $discount->discount_value : '' }}">
                                                <span class="fixed-error d-none">Discount amount is invalid</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row ">
                                <!-- Minimum Purchase Requirements Column -->
                                <div class="col-md-6 ">
                                    <div class="bdrbxds">
                                        <h5><label>Minimum Purchase Requirements</label></h5>
                                        <div class="form-check form-check-custom pl-0">
                                            <input class="form-check-input noshow" type="radio" name="minPurchase"
                                                id="noMinReq" checked onclick="toggleInputs()">
                                            <label class="form-check-label" for="noMinReq">
                                                No minimum requirements
                                            </label>
                                        </div>
                                        <div class="form-check form-check-custom pl-0">
                                            <input class="form-check-input noshow" type="radio" name="minPurchase"
                                                id="minAmount" onclick="toggleInputs()">
                                            <label class="form-check-label mr-2" for="minAmount">
                                                Minimum Purchase Amount ($)
                                            </label>
                                            <input type="number" class="form-control form-control-sm"
                                                id="minAmountInput" name="min_purchase_amount_value" value={{$discount->minimum_purchase_amount ? $discount->minimum_purchase_amount : ''}} disabled
                                                min="0" style="
    margin-right: 2px;">
                                        </div>
                                        <div class="form-check form-check-custom pl-0">
                                            <input class="form-check-input noshow" type="radio" name="minPurchase"
                                                id="minQuantity" onclick="toggleInputs()">
                                            <label class="form-check-label mr-2" for="minQuantity">
                                                Minimum Quantity of Items
                                            </label>
                                            <input type="number" class="form-control form-control-sm"
                                                id="minQuantityInput" name="min_quantity_value"  value={{$discount->minimum_quantity_items ? $discount->minimum_quantity_items : ''}} disabled min="0"
                                                style="
    margin-right: 2px;">
                                        </div>
                                    </div>
                                </div>
                                <!-- Maximum Discount Uses Column -->
                                <div class="col-md-6  ">
                                    <div class=" bdrbxds ">
                                        <h5><label>Maximum Discount Uses</label> <i
                                                class="fa fa-info-circle tooltip-icon"></i>
                                            <div class="tooltip-text" style="top:42px; line-height: 20px;">
                                                <p class="photoNotification">
                                                    Description goes here
                                                </p>
                                            </div>
                                        </h5>
                                        <div class="form-check form-check-custom ">
                                            <input {{ $discount->max_usage_limit != null ? 'checked' : '' }}
                                                class="form-check-input" name="totalUsesCheck" type="checkbox"
                                                id="limitTotal">
                                            <label class="form-check-label" for="limitTotal" style="color: #666;">
                                                Limit number of times this discount can be used in total
                                            </label>
                                        </div>
                                        <div class="form-group" id="totalUsesInput"
                                            style="{{ $discount->max_usage_limit != null ? 'display: block;' : 'display: none;' }}">
                                            <label for="totalUses">Total Uses:</label>
                                            <input type="number" class="form-control smaller-input" id="totalUses"
                                                name="totalUses" style=" width: 100px;" min="0"
                                                value="{{ $discount->max_usage_limit }}">
                                        </div>
                                        <div class="form-check form-check-custom ">
                                            <input {{ $discount->one_use_per_customer == 1 ? 'checked' : '' }}
                                                class="form-check-input" type="checkbox" id="limitPerCustomer"
                                                name="one_use_per_customer">
                                            <label class="form-check-label" for="limitPerCustomer" style="color: #666;">
                                                Limit to one use per customer
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <h6 style="margin-bottom:20px"><label>Activation Time</label></h6>

                            <div class="form-group row">
                                <label for="startDate" class="col-sm-1 col-form-label" style="color: #666;">Start
                                    On:</label>
                                <div class="col-sm-3">
                                    <div class="input-group date  start-date" id="startDatePicker">
                                        <input type="text" name="start_date" class="form-control" id="startDate" style="border-top-left-radius:20px!important;border-bottom-left-radius:20px!important;"
                                            placeholder="YYYY-MM-DD"
                                            value="{{ \Carbon\Carbon::parse($discount->start_time)->format('Y-m-d') }}">
                                        <div class="input-group-append">
                                            <span class="input-group-text"><i class="fa fa-calendar"></i></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-2">
                                   
                                    
                                    <input type="time" name="start_time" class="form-control" id="startTime"
                                        placeholder="HH:MM AM/PM"
                                        value="{{ $discount->start_time && \Carbon\Carbon::parse($discount->start_time)->format('H:i:s') != '00:00:00' ? \Carbon\Carbon::parse($discount->start_time)->format('H:i') : '' }}">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="endDate" class="col-sm-1 col-form-label" style="color: #666;">Ends
                                    On:</label>
                                <div class="col-sm-3">
                                    <div class="end-date input-group date" id="endDatePicker">
                                        <input type="text" name="end_date" class="form-control" id="endDate" style="border-top-left-radius:20px!important;border-bottom-left-radius:20px!important;"
                                            placeholder="YYYY-MM-DD"
                                            value="{{ $discount->end_time && $discount->end_time != '0000-00-00 00:00:00' ? \Carbon\Carbon::parse($discount->end_time)->format('Y-m-d') : '' }}">

                                        <div class="input-group-append">
                                            <span class="input-group-text"><i class="fa fa-calendar"></i></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-2">
                                    <input type="time" name="end_time" class="form-control" id="endTime"
                                        placeholder="HH:MM AM/PM"
                                        value="{{ $discount->end_time && \Carbon\Carbon::parse($discount->end_time)->format('H:i:s') != '00:00:00' ? \Carbon\Carbon::parse($discount->end_time)->format('H:i') : '' }}">
                                </div>
                            </div>

                            <div style="margin-top: 30px">
                                <button class="btn btn-primary" onclick="validateAndSubmitForm()" type="button" value="update"> Save
                                </button>
                                <button class="btn btn-primary" onclick="validateAndSubmitForm()" type="button" value="active"> Save & Active
                                    Discount
                                </button>
                                <a href="{{ route('discount.index') }}" class="btn btn-secondary">Cancel</a>
                            </div>

                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/js/bootstrap-datepicker.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>
    <script>
        $(document).ready(function() {
            jQuery('#percentageValue').on('input', function() {
                var percentageValue = $(this).val();
                if (percentageValue >= 100 || percentageValue < 1) {
                    $(this).addClass("is-invalid");
                    if (percentageValue >= 100) {
                        $('.percentage-error').removeClass('d-none');
                        $('.percentage-error').addClass('d-block');
                        $(this).val(100).prop('readonly', true);
                    }
                } else {
                    $(this).removeClass("is-invalid");
                    $('.percentage-error').addClass('d-none');
                    $(this).prop('readonly', false);
                }
            });
            $('#percentageValue').on('keydown', function(e) {
        // Check if the pressed key is Backspace (keyCode 8)
                if (e.keyCode === 8) {
                    // Temporarily remove readonly when Backspace is pressed
                    $(this).prop('readonly', false);
                    $(this).removeClass("is-invalid");
                    $('.percentage-error').removeClass('d-block');
                    $('.percentage-error').addClass('d-none');

                }
            });
            jQuery('#fixedValue').on('input', function() {
                var fixedValue = $(this).val();
                if (fixedValue.length > 5 || fixedValue < 0) {
                    $(this).addClass("is-invalid");
                    if (fixedValue.length > 5) {
                        $('.fixed-error').removeClass('d-none');
                        $('.fixed-error').addClass('d-block');
                        $(this).val(10000).prop('readonly', true);
                    } else if (fixedValue < 0) {
                        $('.fixed-error').removeClass('d-none');
                        $('.fixed-error').addClass('d-block');
                        $(this).val(0).prop('readonly', true);
                    }
                } else {
                    $(this).removeClass("is-invalid");
                    $('.fixed-error').removeClass('d-block');
                    $('.fixed-error').addClass('d-none');
                }
            });
            $('#fixedValue').on('keydown', function(e) {
        // Check if the pressed key is Backspace (keyCode 8)
                if (e.keyCode === 8) {
                    // Temporarily remove readonly when Backspace is pressed
                    $(this).prop('readonly', false);
                    $(this).removeClass("is-invalid");
                    $('.fixed-error').removeClass('d-block');
                    $('.fixed-error').addClass('d-none');
                }
            });
            $('#autoDiscountTitle').attr('maxlength', 50);
            $('#discountCode').attr('maxlength', 50);
        });
        document.addEventListener('DOMContentLoaded', function() {
            const discountTab = document.getElementById('discountTab');
            const discountTypeInput = document.getElementById('discountActive');

            discountTab.addEventListener('click', function(event) {
                const clickedTab = event.target;
                if (clickedTab.id === 'discount-code-tab') {
                    discountTypeInput.value = 'Coupons';
                } else if (clickedTab.id === 'auto-discount-tab') {
                    discountTypeInput.value = 'Sales';
                }
            });
        });
        document.addEventListener('DOMContentLoaded', function() {
            const discountTab = document.getElementById('discountTab');
            const discountTypeInput = document.getElementById('discountType');

            discountTab.addEventListener('click', function(event) {
                const clickedTab = event.target;
                if (clickedTab.id === 'discount-code-tab') {
                    discountTypeInput.value = 'Coupons';
                } else if (clickedTab.id === 'auto-discount-tab') {
                    discountTypeInput.value = 'Sales';
                }
            });
        });
        document.getElementById('generateCode').addEventListener('click', function() {
            const randomCode = generateRandomCode(5);
            document.getElementById('discountCode').value = randomCode;
        });

        function generateRandomCode(length) {
            const characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
            let result = '';
            for (let i = 0; i < length; i++) {
                result += characters.charAt(Math.floor(Math.random() * characters.length));
            }
            return result;
        }

        // Handle discount applies on change
        document.getElementById('discountAppliesOn').addEventListener('change', function() {
            const selectedOption = this.value;
            document.getElementById('discountCategoriesSection').style.display = 'none';
            document.getElementById('discountProductsSection').style.display = 'none';

            if (selectedOption === 'categories') {
                document.getElementById('discountCategoriesSection').style.display = 'block';
            } else if (selectedOption === 'products') {
                document.getElementById('discountProductsSection').style.display = 'block';
            }
        });

        // Handle browse products button click
        document.getElementById('browseProducts').addEventListener('click', function() {
            $('#productsModal').modal('show');
        });

        // Handle save categories button click
        document.getElementById('saveCategories').addEventListener('click', function() {
            const selectedCategories = [];
            $('#categoriesModal .form-check-input:checked').each(function() {
                const label = $(this).siblings('.form-check-label')
                    .text(); // Fetching the label associated with the checkbox
                selectedCategories.push(label);
            });
            const categoryList = document.getElementById('selectedCategories');
            categoryList.innerHTML = '';
            selectedCategories.forEach(function(category) {
                const li = document.createElement('li');
                li.className = 'list-group-item';
                li.textContent = category;
                categoryList.appendChild(li);
            });
            $('#categoriesModal').modal('hide');
        });

        // Handle save products button click
        document.getElementById('saveProducts').addEventListener('click', function() {
            const selectedProducts = [];
            $('#productsModal .form-check-input:checked').each(function() {
                const label = $(this).siblings('.form-check-label')
                    .text(); // Fetching the label associated with the checkbox
                selectedProducts.push(label);
            });
            const productList = document.getElementById('selectedProducts');
            productList.innerHTML = '';
            selectedProducts.forEach(function(product) {
                const li = document.createElement('li');
                li.className = 'list-group-item';
                li.textContent = product;
                productList.appendChild(li);
            });
            $('#productsModal').modal('hide');
        });
    </script>

    <script>
        document.getElementById('productSearch').addEventListener('input', function() {
            var searchValue = this.value.toLowerCase();
            var products = document.querySelectorAll('.form-check-label');
            products.forEach(function(product) {
                var productName = product.textContent.toLowerCase();
                if (productName.includes(searchValue)) {
                    product.parentNode.style.display = 'block';
                } else {
                    product.parentNode.style.display = 'none';
                }
            });
        });
    </script>

    <script>
        $(document).ready(function() {
            $('#discountType').change(function() {
                $('.discount-input').hide();
                if ($(this).val() === 'percentage') {
                    $('#percentageInput').show();
                } else if ($(this).val() === 'fixed') {
                    $('#fixedInput').show();
                }
            });
        });
    </script>

    <script>
        $(document).ready(function() {
            var products = ["xxxHeritage 42mm Men's Watch", "MuscleMax Dumbbell Set", "The Secret By Rhonda Byrne"];
            var subCategories = ["Sub Category 1", "Sub Category 2"];
            var selectedProducts = [];
            var selectedSubCategories = [];

            function populateForm(formId, items, selectedItems) {
                var form = $(formId);
                form.empty();
                items.forEach(function(item, index) {
                    var checkboxId = formId.slice(1) + "Item" + index;
                    var isChecked = selectedItems.includes(item) ? 'checked' : '';
                    form.append(
                        '<div class="form-check">' +
                        '<input class="form-check-input" type="checkbox" value="' + item + '" id="' +
                        checkboxId + '" ' + isChecked + '>' +
                        '<label class="form-check-label" for="' + checkboxId + '">' + item +
                        '</label>' +
                        '</div>'
                    );
                });
            }

            $('input[name="excludeOption"]').change(function() {
                var selectedOption = $(this).val();
                if (selectedOption === 'products') {
                    $('#productsContainer1').removeClass('d-none');
                    $('#subCategoriesContainer1').addClass('d-none');
                } else {
                    $('#productsContainer1').addClass('d-none');
                    $('#subCategoriesContainer1').removeClass('d-none');
                }
                // Clear the selected items list when the option changes
                $('#selectedItems1').empty();
            });

            $('#productsModal1').on('show.bs.modal', function() {
                populateForm('#productsForm1', products, selectedProducts);
            });

            $('#subCategoriesModal1').on('show.bs.modal', function() {
                populateForm('#subCategoriesForm1', subCategories, selectedSubCategories);
            });

            $('#saveProducts1').click(function() {
                selectedProducts = [];
                $('#productsForm1 input[type="checkbox"]:checked').each(function() {
                    const label = $(this).siblings('.form-check-label')
                        .text();
                    alert(label);
                    selectedProducts.push(label);
                });
                updateSelectedItemsList(selectedProducts);
                $('#productsModal1').modal('hide');
            });

            $('#saveSubCategories1').click(function() {
                selectedSubCategories = [];
                $('#subCategoriesForm1 input[type="checkbox"]:checked').each(function() {
                    selectedSubCategories.push($(this).val());
                });
                updateSelectedItemsList(selectedSubCategories);
                $('#subCategoriesModal1').modal('hide');
            });

            function updateSelectedItemsList(items) {
                $('#selectedItems1').empty();
                items.forEach(function(item) {
                    $('#selectedItems1').append('<li class="list-group-item">' + item + '</li>');
                });
            }
        });
    </script>

    <script>
        $(document).ready(function() {

            // Functionality to check/uncheck child products when the parent product is checked/unchecked
            $('#parentProduct1').change(function() {
                var isChecked = $(this).is(':checked');
                $('#collapseOne .child-checkbox').prop('checked', isChecked);
            });

            // Prevent the collapse when clicking on the parent checkbox
            $('#parentProduct1').click(function(event) {
                event.stopPropagation();
            });

            // Functionality to uncheck the parent product if any child product is unchecked
            $('#collapseOne .child-checkbox').change(function() {
                if (!$(this).is(':checked')) {
                    $('#parentProduct1').prop('checked', false);
                } else {
                    // Check if all child checkboxes are checked
                    if ($('#collapseOne .child-checkbox:checked').length === $(
                            '#collapseOne .child-checkbox').length) {
                        $('#parentProduct1').prop('checked', true);
                    }
                }
            });
        });
    </script>

    <script>
        function toggleInputs() {
            var minAmountInput = document.getElementById('minAmountInput');
            var minQuantityInput = document.getElementById('minQuantityInput');

            if (document.getElementById('minAmount').checked) {
                minAmountInput.disabled = false;
                minQuantityInput.disabled = true;
            } else if (document.getElementById('minQuantity').checked) {
                minAmountInput.disabled = true;
                minQuantityInput.disabled = false;
            } else {
                minAmountInput.disabled = true;
                minQuantityInput.disabled = true;
            }
        }
    </script>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            var limitTotalCheckbox = document.getElementById('limitTotal');
            var totalUsesInput = document.getElementById('totalUsesInput');

            limitTotalCheckbox.addEventListener('change', function() {
                totalUsesInput.style.display = limitTotalCheckbox.checked ? 'block' : 'none';
            });
        });
    </script>

    <script>
        $(document).ready(function() {
           
            jQuery('#autoDiscountTitle').on('input', function() {
                let maxLength = jQuery(this).attr('maxlength');
                let currentLength = jQuery(this).val().length;

                if (currentLength >= maxLength) {
                    jQuery('#titleError').text('Maximum character limit reached.');
                } else {
                    jQuery('#titleError').text(''); // Clear the error message if below the limit
                }
            });
            $('.start-date').datepicker({
                format: "yyyy-mm-dd",
                startDate: new Date(),
                keyboardNavigation: false,
                autoclose: true,
                todayHighlight: true,
                disableTouchKeyboard: true,
                orientation: "bottom auto"
            });

            $('.end-date').datepicker({
                format: "yyyy-mm-dd",
                startDate: moment().add(1, 'days').toDate(),
                keyboardNavigation: false,
                autoclose: true,
                todayHighlight: true,
                disableTouchKeyboard: true,
                orientation: "bottom auto"

            });


            // $('.start-date').datepicker().on("changeDate", function () {
            //     var startDate = $('.start-date').datepicker('getDate');
            //     var oneDayFromStartDate = moment(startDate).add(1, 'days').toDate();
            //     $('.end-date').datepicker('setStartDate', oneDayFromStartDate);
            //     // $('.end-date').datepicker('setDate', oneDayFromStartDate);
            //     $('#startTime').prop('disabled', false);
            // });

            // $('.end-date').datepicker().on("show", function () {
            //     var startDate = $('.start-date').datepicker('getDate');
            //     $('.day.disabled').filter(function (index) {
            //         return $(this).text() === moment(startDate).format('D');
            //     }).addClass('active');
            // });


            $('.start-date').datepicker().on("changeDate", function() {
                var startDate = $('.start-date').datepicker('getDate');

                if (startDate) {
                    // Add one day to the selected start date
                    var oneDayFromStartDate = moment(startDate).add(1, 'days').toDate();

                    // Set the start date in the end datepicker to be one day after the selected start date
                    $('.end-date').datepicker('setStartDate', oneDayFromStartDate);

                    // Enable the start time input
                    $('#startTime').prop('disabled', false);

                    // If an end date is already selected and it's before the new start date, clear it
                    var endDate = $('.end-date').datepicker('getDate');
                    if (endDate && endDate <= oneDayFromStartDate) {
                        $('.end-date').datepicker('setDate', null);
                    }
                } else {
                    // Clear the end date and reset the start date restrictions in the end datepicker
                    $('.end-date').datepicker('setDate', null);
                    $('.end-date').datepicker('setStartDate', null);
                    $('#startTime').prop('disabled', true);
                }
            });

            $('.end-date').datepicker().on("changeDate", function() {
                var endDate = $('.end-date').datepicker('getDate');

                if (endDate) {
                    // Subtract one day from the selected end date
                    var oneDayBeforeEndDate = moment(endDate).subtract(1, 'days').toDate();

                    // Set the maxDate in the start datepicker to the day before the selected end date
                    $('.start-date').datepicker('setEndDate', oneDayBeforeEndDate);
                } else {
                    // Reset the end date restrictions in the start datepicker
                    $('.start-date').datepicker('setEndDate', null);
                }
            }).on("show", function() {
                var startDate = $('.start-date').datepicker('getDate');
                if (startDate) {
                    $('.day.disabled').filter(function(index) {
                        return $(this).text() === moment(startDate).format('D');
                    }).addClass('active');
                }
            });

        });
        // Start Date Datepicker


        $('#endTime').on('change', function() {
            validateEndTime();
        });

        $('#startDate').on('change', function() {
            if ($('#startDate').val() !== '') {
                $('#startTime').prop('disabled', false);
                $('#endTime').prop('disabled', false);
                $('#endDate').prop('disabled', false);
            } else {
                $('.end-date').datepicker('setDate', '');
                $('#endDate').prop('disabled', true);
                $('#startTime').prop('disabled', true);
                $('#endTime').prop('disabled', true);

            }
        });

        function validateEndTime() {
            let startDate = $('#startDate').val();
            let endDate = $('#endDate').val();
            let startTime = $('#startTime').val();
            let endTime = $('#endTime').val();

            // Check if end time is provided
            if (endTime) {
                // If no end date is provided, validate times for the same day
                if (!endDate || startDate === endDate) {
                    if (startTime && endTime && new Date('1970/01/01 ' + startTime) >= new Date('1970/01/01 ' + endTime)) {
                        $('#endTime').addClass('is-invalid').after(
                            '<div class="invalid-feedback">End time must be greater than start time.</div>');
                        return false;
                    } else {
                        $('#endTime').removeClass('is-invalid').next('.invalid-feedback').remove();
                    }
                } else if (startDate && endDate) {
                    // If end date is provided, validate full datetime
                    let startDateTime = new Date(startDate + ' ' + startTime);
                    let endDateTime = new Date(endDate + ' ' + endTime);

                    if (startDateTime >= endDateTime) {
                        $('#endTime').addClass('is-invalid').after(
                            '<div class="invalid-feedback">End time must be greater than start time.</div>');
                        return false;
                    } else {
                        $('#endTime').removeClass('is-invalid').next('.invalid-feedback').remove();
                    }
                }
            }

            return true;
        }


        function validateAndSubmitForm() {
            let isValid = true;
            let saleflag = event.target.value;

            $('.is-invalid').removeClass('is-invalid');
            $('.invalid-feedback').remove();

            if ($('#discount-code-tab').hasClass('active')) {
                $('#autoDiscountTitle').val('');
                if ($('#discountCode').val() === '') {
                    $('#discountCode').addClass('is-invalid').after(
                        '<div class="invalid-feedback">Discount code is required.</div>');
                    isValid = false;
                }
            } else if ($('#auto-discount-tab').hasClass('active')) {
                $('#discountCode').val('');
                if ($('#autoDiscountTitle').val() === '') {
                    $('#autoDiscountTitle').addClass('is-invalid').after(
                        '<div class="invalid-feedback">Title is required.</div>');
                    isValid = false;
                }
            }

            if ($('#discountType').val() === null) {
                $('#discountType').addClass('is-invalid').after(
                    '<div class="invalid-feedback">Discount type is required.</div>');
                isValid = false;
            } else {
                if ($('#discountType').val() === 'percentage') {
                    if ($('#percentageValue').val() === '') {
                        $('#percentageValue').addClass('is-invalid').after(
                            '<div class="invalid-feedback">Percentage value is required.</div>');
                        isValid = false;
                    }
                    let percentageValue = $('#percentageValue').val();
                    if (percentageValue != '' && (percentageValue < 1 || percentageValue > 99)) {
                        $('#percentageValue').addClass('is-invalid').after(
                            '<div class="invalid-feedback">Percentage value must be between 1 and 99.</div>');
                        isValid = false;
                    }
                } else if ($('#discountType').val() === 'fixed') {
                    if ($('#fixedValue').val() === '') {
                        $('#fixedValue').addClass('is-invalid').after(
                            '<div class="invalid-feedback">Fixed amount value is required.</div>');
                        isValid = false;
                    }
                    let fixedValue = $('#fixedValue').val();
                    if (fixedValue != '' && (fixedValue < 1 || fixedValue > 1000)) {
                        $('#fixedValue').addClass('is-invalid').after(
                            '<div class="invalid-feedback">Fixed Amount must be between 1 and 1000.</div>');
                        isValid = false;
                    }
                }
            }

            if ($('#minAmount').is(':checked')) {
                if ($('#minAmountInput').val() === '') {
                    $('#minAmountInput').addClass('is-invalid').after(
                        '<div class="invalid-feedback">Minimum purchase amount is required.</div>');
                    isValid = false;
                }
            } else if ($('#minQuantity').is(':checked')) {
                if ($('#minQuantityInput').val() === '') {
                    $('#minQuantityInput').addClass('is-invalid').after(
                        '<div class="invalid-feedback">Minimum quantity is required.</div>');
                    isValid = false;
                }
            }

            if ($('#limitTotal').is(':checked')) {
                if ($('#totalUses').val() === '') {
                    $('#totalUses').addClass('is-invalid').after(
                        '<div class="invalid-feedback">Total uses is required.</div>');
                    isValid = false;
                }
            }

            if ($('#startDate').val() === '') {
                $('#startDate').addClass('is-invalid');
                isValid = false;
            }

            if (!validateEndTime()) {
                isValid = false;
            }

            if (isValid) {
                submitForm(saleflag);
            } else {
                var $div2 = $(".errorpage");
                toastr.options = {
                    closeButton: true,
                    tapToDismiss: true,
                    positionClass: 'toast-top-right',
                    toastClass: 'toast-icon custom-toast-success',
                    progressBar: true,
                };
                toastr.error('Please fill out the missing fields');
            }
        }
        $('#discountCode, #autoDiscountTitle, #discountType, #startDate, #endDate, #endTime').on('input change',
            function() {
                $(this).removeClass('is-invalid');
                $(this).next('.invalid-feedback').remove();
            });

            function submitForm(saleflag) {
                let saveUrl = '';
                if (saleflag == 'update') {
                    saveUrl = 'discounts';
                } else if (saleflag == 'active') {
                    saveUrl = 'update-discount-active';
                }
                let discountId = $('#discount_id').val();
                let formData = $('#discountUpdateForm').serialize();

                $.ajax({
                    url: '/' + saveUrl + '/' + discountId,
                    type: 'PUT',
                    data: formData,
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function (response) {
                        if (response.requires_confirmation) {
                            swal({
                                title: "Are you sure?",
                                text: response.message || "Do you want to proceed?",
                                type: "warning",
                                showCancelButton: true,
                                confirmButtonClass: "btn-primary",
                                confirmButtonText: "Yes, Do it!",
                                closeOnConfirm: false
                            }, function () {
                                formData += '&sale_flag=' + encodeURIComponent(saleflag);
                                $.ajax({
                                    url: '/update-discount-active-confirm/' + discountId,
                                    type: 'PUT',
                                    data: formData,
                                    success: function (response) {
                                        // Show toastr before redirecting
                                        toastr.options = {
                                            closeButton: true,
                                            tapToDismiss: true,
                                            positionClass: 'toast-top-right',
                                            progressBar: true,
                                        };
                                        let message = response.message || "Discount updated successfully!";
                                        toastr.success(message);
                                        
                                        if (response.status) {
                                            setTimeout(function () {
                                                window.location.href = "{{ route('discount.index') }}";
                                            }, 2000);
                                        } else {
                                            setTimeout(function () {
                                                location.reload();
                                            }, 2000);
                                        }
                                    },
                                    error: function (response) {
                                        toastr.options = {
                                            closeButton: true,
                                            tapToDismiss: true,
                                            positionClass: 'toast-top-right',
                                            progressBar: true,
                                        };
                                        let message = response.message || "An error occurred!";
                                        toastr.error(message);
                                        setTimeout(function () {
                                            location.reload();
                                        }, 2000);
                                    }
                                });
                            });
                        } else if (response.status === true) {
                            // Show toastr before redirecting
                            toastr.options = {
                                closeButton: true,
                                tapToDismiss: true,
                                positionClass: 'toast-top-right',
                                progressBar: true,
                            };
                            let message = response.message || "Discount updated successfully!";
                            toastr.success(message);

                            setTimeout(function () {
                                window.location.href = "{{ route('discount.index') }}";
                            }, 2000);
                        } else {
                            alert('Failed to update the discount. Please try again.');
                        }
                    },
                    error: function(xhr) {
                        if (xhr.status === 422) {
                            let errors = xhr.responseJSON.errors;
                            for (let field in errors) {
                                $('#' + field).addClass('is-invalid').after('<div class="invalid-feedback">' +
                                    errors[field][0] + '</div>');
                            }
                        }
                    }
                });
            }

    </script>

    <script>
        $(document).ready(function() {
            // Function to toggle between product and sub-category selection
            function toggleSelectionContainers(selectedOption) {
                if (selectedOption === 'products') {
                    $('#productsContainer1').removeClass('d-none');
                    $('#subCategoriesContainer1').addClass('d-none');
                } else {
                    $('#productsContainer1').addClass('d-none');
                    $('#subCategoriesContainer1').removeClass('d-none');
                }
                // Clear the selected items list when the option changes
                $('#selectedItems1').empty();
            }

            $('input[name="excludeOption"]').change(function() {
                var selectedOption = $(this).val();
                toggleSelectionContainers(selectedOption);
            });

            function updateSelectedItemsList(items) {
                $('#selectedItems1').empty();
                items.forEach(function(item) {
                    $('#selectedItems1').append('<li class="list-group-item">' + item + '</li>');
                });
            }

            // Search function for products in the modal
            $('#productSearch1').on('input', function() {
                var searchText = $(this).val().toLowerCase();
                $('#productAccordion1 .card').each(function() {
                    var cardText = $(this).text().toLowerCase();
                    if (cardText.includes(searchText)) {
                        $(this).show();
                    } else {
                        $(this).hide();
                    }
                });
            });

            //  clear the search field and show all cards when the modal is closed
            $('#productsModal1').on('hidden.bs.modal', function() {
                $('#productSearch1').val('');
                $('#productAccordion1 .card').show();
            });
        });
    </script>

    <script>
        $(document).ready(function() {
            // Search function for products in the modal
            $('#productSearch1').on('input', function() {
                var searchText = $(this).val().toLowerCase();
                $('#productAccordion1 .card').each(function() {
                    var cardText = $(this).text().toLowerCase();
                    if (cardText.includes(searchText)) {
                        $(this).show();
                    } else {
                        $(this).hide();
                    }
                });
            });

            // Clear the search field and show all cards when the modal is closed
            $('#productsModal1').on('hidden.bs.modal', function() {
                $('#productSearch1').val('');
                $('#productAccordion1 .card').show();
            });
        });
    </script>

    <script>
        $(document).ready(function() {
            // Function to toggle between product and sub-category selection
            function toggleSelectionContainers(selectedOption) {
                if (selectedOption === 'products') {
                    $('#productsContainer1').removeClass('d-none');
                    $('#subCategoriesContainer1').addClass('d-none');
                } else {
                    $('#productsContainer1').addClass('d-none');
                    $('#subCategoriesContainer1').removeClass('d-none');
                }
                // Clear the selected items list when the option changes
                $('#selectedItems1').empty();
            }

            $('input[name="excludeOption"]').change(function() {
                var selectedOption = $(this).val();
                toggleSelectionContainers(selectedOption);
            });

            function updateSelectedItemsList(items) {
                $('#selectedItems1').empty();
                items.forEach(function(item) {
                    $('#selectedItems1').append('<li class="list-group-item">' + item + '</li>');
                });
            }

            // Save selected categories and close modal
            $('#saveCategories1').click(function() {
                var selectedCategories = [];
                $('#categoriesModal1 input[type="checkbox"]:checked').each(function() {
                    selectedCategories.push($(this).val());
                });
                updateSelectedItemsList(selectedCategories);
                $('#categoriesModal1').modal('hide'); // Close the modal
            });

            // Save selected sub-categories and close modal
            $('#saveSubCategories1').click(function() {
                var selectedSubCategories = [];
                $('#subCategoriesModal1 input[type="checkbox"]:checked').each(function() {
                    selectedSubCategories.push($(this).val());
                });
                updateSelectedItemsList(selectedSubCategories);
                $('#subCategoriesModal1').modal('hide'); // Close the modal
            });

            // Save selected products and close modal
            $('#saveProducts1').click(function() {
                var selectedProducts = [];
                $('#productAccordion1 input[type="checkbox"]:checked').each(function() {
                    selectedProducts.push($(this).val());
                });
                updateSelectedItemsList(selectedProducts);
                $('#productsModal1').modal('hide'); // Close the modal
            });

            // Search function for products in the modal
            $('#productSearch1').on('input', function() {
                var searchText = $(this).val().toLowerCase();
                $('#productAccordion1 .card').each(function() {
                    var cardText = $(this).text().toLowerCase();
                    if (cardText.includes(searchText)) {
                        $(this).show();
                    } else {
                        $(this).hide();
                    }
                });
            });

            //  clear the search field and show all cards when the modal is closed
            $('#productsModal1').on('hidden.bs.modal', function() {
                $('#productSearch1').val('');
                $('#productAccordion1 .card').show();
            });
        });
    </script>

    <!-------------fetching products-------------->
    <script>
        $(document).ready(function() {
            $.ajax({
                url: '/product-listing-discounts',
                method: 'GET',
                success: function(response) {
                    if (response.status) {
                        updateProductModal(response.data.products);
                    } else {
                        alert('Failed to fetch products.');
                    }
                },
                error: function() {
                    alert('Error in AJAX request.');
                }
            });
        });

        function updateProductModal(products) {
            const productAccordion1 = $('#productAccordion1');
            productAccordion1.empty();
            const productAccordion = $('#productAccordion');
            productAccordion.empty();

            products.forEach((product, index) => {
                let cardHeaderApplicable = `
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center" id="heading${index}">
                    <div class="form-check">
                        <input class="form-check-input" name="applicableProducts[${index}][product]" type="checkbox" value="${product.product_id}" id="parentProduct${index}">
                        <label class="form-check-label" for="parentProduct${index}">
                            ${product.title}
                        </label>
                    </div>`;

                let cardHeaderExcluded = `
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center" id="headingEx${index}">
                    <div class="form-check">
                        <input class="form-check-input" name="excludedProducts[${index}][product]" type="checkbox" value="${product.product_id}" id="parentProductEx${index}">
                        <label class="form-check-label" for="parentProductEx${index}">
                            ${product.title}
                        </label>
                    </div>`;

                if (product.variations.length > 0) {
                    cardHeaderApplicable += `
                <span class="toggle-arrow" data-toggle="collapse" data-target="#collapse${index}" aria-expanded="true" aria-controls="collapse${index}">
                    Select Variations ▼
                </span>
                </div>
                <div id="collapse${index}" class="collapse" aria-labelledby="heading${index}" data-parent="#productAccordion1">
                    <div class="card-body">
                        ${product.variations.map((variation, varIndex) => `
                                    <div class="form-check">
                                        <input class="form-check-input child-checkbox" name="applicableProducts[${index}][variations][]" type="checkbox" value="${variation.variation_id}" id="childProduct${variation.variation_id}">
                                        <label class="form-check-label" for="childProduct${variation.variation_id}">
                                            ${variation.option1} - ${variation.option2}
                                        </label>
                                    </div>
                                `).join('')}
                    </div>
                </div>
            </div>`;
                    cardHeaderExcluded += `
                <span class="toggle-arrow" data-toggle="collapse" data-target="#collapseEx${index}" aria-expanded="true" aria-controls="collapseEx${index}">
                    Select Variations ▼
                </span>
                </div>
                <div id="collapseEx${index}" class="collapse" aria-labelledby="headingEx${index}" data-parent="#productAccordion">
                    <div class="card-body">
                        ${product.variations.map((variation, varIndex) => `
                                    <div class="form-check">
                                        <input class="form-check-input child-checkbox" name="excludedProducts[${index}][variations][]" type="checkbox" value="${variation.variation_id}" id="childProductEx${variation.variation_id}">
                                        <label class="form-check-label" for="childProductEx${variation.variation_id}">
                                            ${variation.option1} - ${variation.option2}
                                        </label>
                                    </div>
                                `).join('')}
                    </div>
                </div>
            </div>`;
                } else {
                    cardHeaderApplicable += `
                </div>
            </div>`;
                    cardHeaderExcluded += `
                </div>
            </div>`;
                }
                productAccordion1.append(cardHeaderExcluded);
                productAccordion.append(cardHeaderApplicable);
            });
        }
    </script>

    <script>
        $(document).ready(function() {
            fetchCategories(false);
            fetchCategories(true);

            function fetchCategories(isSubCategory) {
                const url = isSubCategory ? '/subCategory-listing-discounts?sub=true' :
                    '/subCategory-listing-discounts';

                $.ajax({
                    url: url,
                    method: 'GET',
                    success: function(response) {
                        let nameParam = null;
                        if (url.includes('sub=true')) {
                            nameParam = 'subCategories';
                        } else {
                            nameParam = 'Categories';
                        }

                        if (response.status && response.data.categorySales) {
                            const categories = response.data.categorySales;
                            let categoryHtml = '';

                            categories.forEach(category => {
                                categoryHtml += `
                            <div class="form-check">
                                <input class="form-check-input" name="${nameParam}[]" type="checkbox" value="${category.categoryId}" id="category${category.categoryId}">
                                <label class="form-check-label" for="category${category.categoryId}">
                                    ${category.categoryName}
                                </label>
                            </div>
                        `;
                            });

                            if (isSubCategory) {
                                $('#categoriesModalBody .form-group').html(categoryHtml);

                            } else {
                                $('#categoriesModalBody2 .form-group').html(categoryHtml);
                            }
                        }
                    },
                    error: function(error) {
                        console.error('Error fetching categories:', error);
                    }
                });
            }

            $('#browseCategories').on('click', function() {
                $('#categoriesModal').modal('show');
            });

            $('#selectSubCategories').on('click', function() {
                $('#subCategoriesModal1').modal('show');
            });
        });
    </script>

    <!-------------Code Ends-------------->
@endsection
