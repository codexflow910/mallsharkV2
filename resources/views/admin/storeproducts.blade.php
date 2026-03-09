@extends('layouts.dashboard-main')
@section('content')
@section('title', 'All Products')
<!-- Content Starts -->
<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>


<style>
    ::placeholder,
    button,
    #review_comment,
    #review_name {
        font-family: Montserrat, sans-serif;
    }

    .has-error {
        border: 1px solid red !important;
    }

    .fa.fa-star-o {
        color: grey !important;
    }

    #imagePreview img {
        max-width: 100px;
        max-height: 100px;
        object-fit: cover;
        margin-right: 10px;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    /* checkbox styles for product cards */
    .dshprdbx.border {
        margin-bottom: 4px;
    }

    .product-image-col {
        position: relative;
    }

    .product-select-checkbox {
        position: absolute;
        top: 6px;
        left: 8px;
        z-index: 20;
        width: 18px;
        height: 18px;
        cursor: pointer;
    }
    /* Select2 custom styling */
    .select2-container--default .select2-selection--multiple {
        border: 1px solid #ddd;
        border-radius: 6px;
        min-height: 38px;
        padding: 2px 5px;
    }

    .select2-container--default .select2-selection--multiple .select2-selection__choice {
        background-color: #073ba5;
        border: 1px solid #073ba5;
        color: white;
        padding: 2px 8px;
        margin-top: 3px;
        border-radius: 4px;
    }

    .select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
        color: white;
        margin-right: 5px;
    }

    .select2-container--default .select2-selection--multiple .select2-selection__choice__remove:hover {
        color: #ff6b6b;
    }

    .select2-container .select2-search--inline .select2-search__field {
        margin-top: 5px;
    }

    /* Custom Multi-Select Dropdown Styling */
    .custom-multiselect {
        position: relative;
        width: 150px;
        display: inline-block;
    }

    .multiselect-button {
        width: 100%;
        padding: 8px 12px;
        border: 1px solid #ddd;
        border-radius: 6px;
        background: white;
        text-align: left;
        cursor: pointer;
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-size: 14px;
    }

    .multiselect-button:hover {
        border-color: #073ba5;
    }

    .multiselect-dropdown {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        background: white;
        border: 1px solid #ddd;
        border-radius: 6px;
        margin-top: 4px;
        max-height: 300px;
        overflow-y: auto;
        display: none;
        z-index: 1000;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    }

    .multiselect-dropdown.active {
        display: block;
    }

    .multiselect-option {
        padding: 8px 12px;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .multiselect-option:hover {
        background-color: #f5f5f5;
    }

    .multiselect-option input[type="checkbox"] {
        margin: 0;
        cursor: pointer;
    }

    .multiselect-search {
        padding: 8px;
        border-bottom: 1px solid #eee;
    }

    .multiselect-search input {
        width: 100%;
        padding: 6px 10px;
        border: 1px solid #ddd;
        border-radius: 4px;
        font-size: 13px;
    }

    .selected-stores-tags {
        display: flex;
        flex-wrap: wrap;
        gap: 4px;
        align-items: center;
    }

    .store-tag {
        background-color: #073ba5;
        color: white;
        padding: 2px 8px;
        border-radius: 4px;
        font-size: 12px;
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }

    .store-tag .remove {
        cursor: pointer;
        font-weight: bold;
    }
</style>
<?php
?>
<main>
    {{-- @include('Store.sidebar') --}}
    <!-- Dashboard Ends -->
    <div class="dshbrdin dashprdlst storeproducts-page ">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <div class="dashprdlst">
                        <!-- Product Sorting Starts -->

                        @if(Session::has('SuccessMessage'))

                        <?php $successMessage = Session::get('SuccessMessage') ?>
                        <script>
                            toastr.options = {
                                closeButton: true,
                                tapToDismiss: true,
                                positionClass: 'toast-top-right',
                                toastClass: 'toast-icon custom-toast-success',
                                progressBar: true,
                            };
                            toastr.success("<?php echo  $successMessage ?> ");
                        </script>

                        @endif
                        @if(Session::has('errorMessage'))

                        <?php $errorMessage = Session::get("errorMessage") ?>
                        <script>
                            toastr.options = {
                                closeButton: true,
                                tapToDismiss: true,
                                positionClass: 'toast-top-right',
                                toastClass: 'toast-icon custom-toast-success',
                                progressBar: true,
                            };
                            toastr.error("<?php echo  $errorMessage ?> ");
                        </script>

                        @endif
                        <br>
                        <div class="row">
                            <div class="col-sm-12 search-bar-top">
                                <div class="d-flex justify-content-between align-items-center w100p br-top-thin mt-20 mobile-topbar-block">
                                    <div class="" style="align-self-center">
                                        <h3 class="mb-0">All Products </h3>
                                    </div>
                                    <form class="d-flex" id="bulkForm">
                                    <input type="hidden" name="ids" id="selectedIds">
                                        <select class="form-control search-fieldd" id="" name="status" style="width: auto; ">
                                                      
                                                <option value="">Bulk Actions</option>
                                                <option value="approve" >Approved</option>
                                                <option value="disapprove">Disapproved</option>
                                        </select>
                                        <div class="btn-submit-optn inline-block-ele ml-2">
                                            <button class=" btn-primary" style="border-radius: 6px;"  id="bulk_apply" >Apply</button>
                                        </div>
                                    </form>
                                    <form action="{{URL::to('/admin/store-products')}}" method="get" id="product_search_form">
                                        <input type="hidden" name="store_id" id="store_id_hidden">
                                        <div class="bd-highlight">
                                            <div class="form-inline-large form-control-optn">
                                                <div class="inline-block-ele pos-rel mobile-full-width-search">
                                                    <div class="custom-multiselect">
                                                        <div class="multiselect-button" id="multiselect_trigger">
                                                            <span class="selected-stores-tags" id="selected_stores_display">
                                                                Select Stores
                                                            </span>
                                                            <span>▼</span>
                                                        </div>
                                                        <div class="multiselect-dropdown" id="multiselect_dropdown">
                                                            <div class="multiselect-search">
                                                                <input type="text" id="store_search" placeholder="Search stores..." autocomplete="off">
                                                            </div>
                                                            <div id="store_options">
                                                                @if(isset($stores) && count($stores) > 0)
                                                                    @foreach($stores as $store)
                                                                        <div class="multiselect-option">
                                                                            <input type="checkbox"
                                                                                   value="{{$store->store_id}}"
                                                                                   id="store_{{$store->store_id}}"
                                                                                   class="store-checkbox"
                                                                                   data-name="{{$store->store_name}}"
                                                                                   {{ (isset($_GET['store_id']) && in_array($store->store_id, explode(',', $_GET['store_id']))) ? 'checked' : '' }}>
                                                                            <label for="store_{{$store->store_id}}" style="margin:0; cursor:pointer;">{{$store->store_name}}</label>
                                                                        </div>
                                                                    @endforeach
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="custm-slect-form-optin inline-block-ele">
                                                    <select class="form-control search-fieldd" id="" name="per_page" style="min-width:0px">
                                                       <?php
                                                        $select_all_page = '';
                                                        $page_50 = '';
                                                        $page_100 = '';
                                                        $page_200 = '';
                                                        $page_250 = '';
                                                        $page_500 = '';
                                                        
                                                        if (isset($_GET['per_page']) && $_GET['per_page'] == '50') {
                                                            $page_50 = 'selected="selected"';
                                                        } else if (isset($_GET['per_page']) && $_GET['per_page'] == '100') {
                                                            $page_100 = 'selected="selected"';
                                                        } else if (isset($_GET['per_page']) && $_GET['per_page'] == '200') {
                                                            $page_200 = 'selected="selected"';
                                                        } else if (isset($_GET['per_page']) && $_GET['per_page'] == '250') {
                                                            $page_250 = 'selected="selected"';
                                                        } else if (isset($_GET['per_page']) && $_GET['per_page'] == '500') {
                                                            $page_500 = 'selected="selected"';
                                                        }
                                                        ?>
                                                        <option value="">Per Page</option>
                                                        <option value="50" <?php echo $page_50; ?>>50</option>
                                                        <option value="100" <?php echo $page_100; ?>>100</option>
                                                        <option value="200" <?php echo $page_200; ?>>200</option>
                                                        <option value="250" <?php echo $page_250; ?>>250</option>
                                                        <option value="500" <?php echo $page_500; ?>>500</option>


                                                    </select>
                                                </div>
                                                <div class="inline-block-ele pos-rel mobile-full-width-search">
                                                    <input type="search" class="form-control" id="search_products" name="search_products" value="<?php echo isset($_GET['search_products']) ? $_GET['search_products'] : ''; ?>" placeholder="Search Products" />
                                                    <!--                                                    <button type="submit" class="btn">
                                                                                                                <i class="fa fa-search" aria-hidden="true"> </i>
                                                                                                            </button>     -->
                                                </div>
                                                <div class="custm-slect-form-optin inline-block-ele">
                                                    <select class="form-control search-fieldd" id="" name="status" style="width: auto;">
                                                        <?php
                                                        $select_status_all = '';
                                                        $select_status_pending = '';
                                                        $select_status_active = '';
                                                        $select_status_approve = '';
                                                        $select_status_deactive = '';
                                                        $select_status_delete = '';
                                                        $select_status_reject = '';

                                                        if (isset($_GET['status']) && $_GET['status'] == 'all') {
                                                            $select_status_all = 'selected="selected"';
                                                        } else if (isset($_GET['status']) && $_GET['status'] == 'pending') {
                                                            $select_status_pending = 'selected="selected"';
                                                        } else if (isset($_GET['status']) && $_GET['status'] == 'active') {
                                                            $select_status_active = 'selected="selected"';
                                                        } else if (isset($_GET['status']) && $_GET['status'] == 'deactive') {
                                                            $select_status_deactive = 'selected="selected"';
                                                        } else if (isset($_GET['status']) && $_GET['status'] == 'delete') {
                                                            $select_status_delete = 'selected="selected"';
                                                        } else if (isset($_GET['status']) && $_GET['status'] == 'reject') {
                                                            $select_status_reject = 'selected="selected"';
                                                        } else if (isset($_GET['status']) && $_GET['status'] == 'approve') {
                                                            $select_status_approve = 'selected="selected"';
                                                        }
                                                        ?>
                                                        <option value="">Select Product Status</option>
                                                        <option value="all" <?php echo $select_status_all; ?>>All</option>
                                                        <option value="pending" <?php echo $select_status_pending; ?>>Pending</option>
                                                        <option value="approve" <?php echo $select_status_approve; ?>>Approved</option>
                                                        <option value="delete" <?php echo $select_status_delete; ?>>Deleted</option>
                                                        <option value="reject" <?php echo $select_status_reject; ?>>Rejected</option>


                                                    </select>
                                                </div>
                                                <div class="btn-submit-optn inline-block-ele pos-rel">
                                                    <button type="submit" class=" btn-primary" style="border-radius: 6px;"> Search
                                                    </button>
                                                </div>

                                            </div>
                                        </div>
                                    </form>
                                    
                                </div>
                            </div>
                        </div>
                        <div class="mt-4 d-flex justify-content-between align-items-center">
                                <div class="d-flex align-items-center">
                                        <input type="checkbox" style="height:16px; width:16px;" id="select_all_products_checkbox" <?php if (!isset($_GET['status']) || $_GET['status'] == '' || $_GET['status'] == 'all') { echo 'disabled'; } ?>>
                                        <label for="select_all_products_checkbox" class="form-check-label ml-2 fw-light">Select All</label>
                                </div>
                                <div class="btn-submit-optn">
                                        <button class="btn-secondary" style="border-radius: 6px;" id="generate_facebook_feed">Generate FB XML</button>
                                </div>
                        </div>
                        <!-- Product List -->
                        <?php
                        if (!$results->isEmpty()) {
                            
                            foreach ($results as $res) {
                                
                                if ($res->product_type == 'variant') {
                                    $variableData = getvariableProduct($res->product_id);
                                }
                                $storeData = getStoreStatusById($res->store_id);

                                $productimage = getAttachmentUrlById((int) $res->feature_Image, $size = 'medium');
                        ?>
                                <div class="dshprdbx border">
                                    <div class="row">
                                        <div class="col-sm-6 lp-bar">
                                            <div class="row">
                                                <div class="col-md-3 product-image-col">
                                                    <input type="checkbox" class="product-select-checkbox" data-product-id="<?php echo $res->product_id; ?>" data-store-id="<?php echo $res->store_id; ?>">

                                                    <div class="img-div-mbt">
                                                        <img class="img-responsive" src="<?php echo !empty($productimage) ?  $productimage : URL::to('/assets/images/placeholder.jpg') ?>" alt="{{$res->title}}" title="{{$res->title}}">
                                                    </div>
                                                </div>

                                                <div class=" col-md-9">
                                                    <div class="body-content-mbt">
                                                        <!--                                                        <a href="<?php //echo URL::to('admin/product/' . $res->slug);       
                                                                                                                                ?>"></a>-->
                                                        <h4 class="card-title">{{$res->title}}</h4>
                                                        <?php
                                                        $timestamp = strtotime($res->created_at);
                                                        $date = date('d-M-Y', $timestamp);
                                                        ?>
                                                        <p class="small lgt-gry" style="margin-bottom:5px">{{$date}}</p>
                                                        <p class="card-text" style="margin-bottom:5px;display:none;">{{strip_tags($res->short_description)}}</p>

                                                        <?php
                                                        $totalAdded = $res->current_stock + $res->sales;
                                                        ?>
                                                        <p class="inline-block-ele" style="margin-bottom:5px">
                                                            <?php if ($res->product_type == 'variant') { ?>
                                                                <span class="bold-element zit-price">Price: $ {{isset($variableData['min_price'])?$variableData['min_price']:""}}</span>
                                                            <?php } else { ?>
                                                                <span class="bold-element zit-price">Price: $ {{$res->price}}</span>
                                                            <?php } ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-sm-6 lrt-bar  d-flex justify-content-between ">
                                            <div class="rw-tp wt-left-tab align-self-center">
                                                <div class="co-rvnu">
                                                    <div class="lft-prf d-flex justify-content-between">
                                                        <span>Store Name:</span>
                                                        <span><a href="{{route('storeHome',$res->store_slug)}}" style="color: #073ba5">{{$res->store_name}}</a></span>
                                                    </div>
                                                    <div class="lft-prf d-flex justify-content-between">
                                                        <span>Product Status:</span>
                                                        <?php
                                                        if ($res->status == '0') {
                                                            $productstatus = 'De-activate';
                                                        } elseif ($res->status == '1') {
                                                            $productstatus = 'Active';
                                                        } elseif ($res->status == '2') {
                                                            $productstatus = 'Delete';
                                                        } elseif ($res->status == '3') {
                                                            $productstatus = 'Pending';
                                                        } elseif ($res->status == '4') {
                                                            $productstatus = 'Reject';
                                                        } else {
                                                            $productstatus = '';
                                                        } ?>
                                                        <span>{{$productstatus}}</span>

                                                    </div>
                                                    <div class="lft-prf d-flex justify-content-between">
                                                        <span>Product Type:</span>
                                                        <?php if ($res->product_type == 'variant') { ?>
                                                            <span>
                                                                <a href="#" class="productvariation" data-product-id="{{$res->product_id}}" data-toggle="modal">Variable</a>
                                                            </span>
                                                        <?php }
                                                        if ($res->product_type == 'virtual') { ?>
                                                            <span>Virtual</span>
                                                        <?php } ?>
                                                        <?php if ($res->product_type == 'simple') { ?>
                                                            <span>Simple</span>
                                                        <?php } ?>
                                                    </div>
                                                    <div class="lft-prf d-flex justify-content-between">
                                                        <span>Feature Product:</span>
                                                        <span><input type="checkbox" data-id="{{ $res->product_id }}"  class="feature_status_checkbox"  {{$res->feature_status ? 'checked' : '' }}  style="width: 16px; height: 16px;"></span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="rw-tp wt-rgt-tab align-self-center">
                                                <div class="co-rvnu ">
                                                    <div class="rght-prf d-flex justify-content-between">
                                                        <span>Current Stock:</span>
                                                        <?php if ($res->product_type == 'variant') {
                                                        ?>
                                                            <span><?php echo isset($variableData['current_stock']) ? $variableData['current_stock'] : 0; ?></span>
                                                        <?php } else { ?>
                                                            <span><?php echo isset($res->current_stock) ? $res->current_stock : 0; ?></span>
                                                        <?php } ?>
                                                    </div>
                                                    <div class="rght-prf d-flex justify-content-between">
                                                        <span>All-time Sales:</span>
                                                        <?php if ($res->product_type == 'variant') { ?>
                                                            <span><?php echo isset($variableData['total_sales']) ? $variableData['total_sales'] : 0; ?></span>

                                                        <?php } else { ?>
                                                            <span><?php echo isset($res->sales) ? $res->sales : 0; ?></span>

                                                        <?php } ?>
                                                    </div>
                                                    @if (canAccess('edit-mallshark-selected'))
                                                    <div class="rght-prf d-flex justify-content-between align-items-center">
                                                        <span>Mallshark Selected: </span>
                                                        <span class="selected-success"></span>
                                                        <span>
                                                            <input type="checkbox" 
                                                                class="mallshark-selected-checkbox" 
                                                                data-product-id="<?php echo $res->product_id; ?>" 
                                                                name="mallshark_selected" 
                                                                value="" 
                                                                style="width: 16px; height: 16px;"
                                                                <?php if ($res->mallshark_selected) echo 'checked'; ?>>
                                                        </span>
                                                    </div>
                                                    @endif                                                          
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row pr-edit-strip">
                                        <div class="col-sm-12">
                                            <div class="d-flex justify-content-between w100p">
                                                <div class="edit-delete-box align-self-center">
                                                    <p>
                                                        <?php
                                                        $status_text = 'Deactivate';
                                                        if ($res->status == 0) {
                                                            $status_text = 'Activate';
                                                        }
                                                        if ($res->status == 2) {
                                                            $status_text = 'Restore';
                                                        }
                                                        //                                                        if ($res->status < 3) {
                                                        if ($res->status != 2) {
                                                        ?>
                                                        @if (canAccess('edit-product'))
                                                            <!-- <a href="<?php echo URL::to('/admin-edit-product') . "/" . $res->product_id . "?edit-by=admin"; ?>">Edit</a> <span class="sprtr-lne"> | </span> -->
                                                        @endif
                                                        @if (canAccess('delete-product'))
                                                            <a href="#" class="alert-message-mbt delproduct" data-product-id="<?php echo $res->product_id; ?>" data-status="Delete" data-toggle="modal" data-target="#product-detete-message">Delete</a> <span class="sprtr-lne"> | </span>
                                                        @endif
                                                        <?php } ?>

                                                        <?php
                                                        //                                                        } else {
                                                        if ($res->status == 3) {
                                                            $alertMessage = "Are you sure you want to approve the product?";
                                                        ?>
                                                        @if (canAccess('approve-product'))
                                                            <a class="product-approve  {{isset($storeData->store_status) && $storeData->store_status == 2 ? '' : 'disabled' }}" data-store_id="<?php echo isset($storeData->store_status) ?? ''; ?>" href="<?php echo URL::to('admin-change-status/' . $res->product_id . '/Approve/' . (isset($storeData->store_status) ? $storeData->store_status : '')); ?>">Approve Product</a> <span class="sprtr-lne"> | </span>
                                                        @endif
                                                        @if (canAccess('reject-product'))
                                                            <a href="#" class="alert-message-mbt rejectproduct" data-reject-id="<?php echo $res->product_id; ?>" data-reject-status="Reject" data-toggle="modal" data-target="#product-reject-message">Reject Product</a><span class="sprtr-lne"> | </span>
                                                        @endif
                                                        <?php
                                                        } else if ($res->status == 4) {
                                                            echo '<span class="red">Product Rejected</span> <span class="sprtr-lne"> |  </span>';
                                                        ?>
                                                        @if (canAccess('approve-product'))
                                                            <!-- <a class="product-approve" href="<?php //echo URL::to('admin-change-status/' . $res->product_id . '/Approve');      
                                                                    ?>">Approve Product</a>-->
                                                        @endif
                                                        <?php
                                                        } else if ($res->status == 2) {
                                                            $alertMessage = "Are you sure you want to approve the product?";
                                                        ?>
                                                        @if (canAccess('restore-product'))
                                                            <a class="product-approve" href="<?php echo URL::to('admin-change-status/' . $res->product_id . '/Restore'); ?>">Restore</a><span class="sprtr-lne"> |</span>
                                                        @endif
                                                        <?php
                                                        }
                                                        //}
                                                        $userData = getUserById($res->user_id);
                                                        //                                                        print_rr($storeData);
                                                        ?>
                                                        @if (canAccess('contact-seller'))
                                                        <a href="mailto:<?php echo !empty($userData->email) ? $userData->email : "" ?>?Subject=Product issue" target="_top">Contact Seller</a>
                                                        @endif
                                                        @if (canAccess('manage-reviews'))
                                                        <span class="sprtr-lne"> | </span>
                                                        <a href="#" class="reviews-link" data-product-id="<?php echo $res->product_id; ?>" data-toggle="modal" data-target="#product-reviews">Reviews</a>
                                                        @endif
                                                        @if (canAccess('product-link-videos'))
                                                        <span class="sprtr-lne"> | </span>
                                                            <a href="/admin/product-videos/<?php echo $res->product_id; ?>" class="product-videos">Link Videos</a>
                                                        @endif
                                                    </p>                                                    
                                                </div>
                                                <?php
                                                if (isset($_GET['store_campaign']) && $_GET['store_campaign'] != '') {
                                                    $campaignId = $_GET['store_campaign'];
                                                }
                                                if (!empty($campaignId)) {
                                                    $flashSale = checkFlashSaleProduct($campaignId, $res->product_id);
                                                ?>
                                                    <div class="chose-flashsale align-self-center">
                                                        <p>
                                                            <?php if ($flashSale == true) { ?>
                                                                <a href="#" class="active-flash-an changeflash">
                                                                    <i class="material-icons">
                                                                        done
                                                                    </i>Flash sale Enabled
                                                                </a>
                                                                <a href="#" id="select_flash_sale" class="changeflash not-active" data-product-id="{{$res->product_id}}" data-camp-id="{{$campaignId}}">
                                                                    Choose For Flash sale
                                                                </a>
                                                            <?php } else { ?>
                                                                <a href="#" id="select_flash_sale" class="not-active changeflash" data-product-id="{{$res->product_id}}" data-camp-id="{{$campaignId}}">
                                                                    Choose For Flash sale
                                                                </a>
                                                                <a href="#" class="active-flash-an changeflash"> <a href="#" class="active-flash-an changeflash">
                                                                        <i class="material-icons">
                                                                            done
                                                                        </i>Flash sale Enabled
                                                                    </a>
                                                                <?php } ?>
                                                        </p>
                                                    </div>
                                                <?php } ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php
                            }
                            ?>
                            <!-- Product List Ends -->

                            <div class="custom-pagination col-sm-12 mt-15">
                                {{$results->links('pagination::bootstrap-4')}}



                                <!-- <div class="custom-pagination col-sm-12 mt-15">
                                    <?php
                                    // $q = [];
                                    // if (isset($_GET['search_products'])) {
                                    //     $q['search_products'] = $_GET['search_products'];
                                    // }
                                    // if (isset($_GET['status'])) {
                                    //     $q['status'] = $_GET['status'];
                                    // }
                                    //print_rr($q);
                                    // echo $results->appends($q)->links('pagination.defaultpagination');
                                    ?>
                                </div> -->
                            </div>
                        <?php
                        } else {
                        ?>
                            @include('search.no-record')
                        <?php } ?>
                        <!-- Popup Starts -->
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div id="myModal" class="modal fade start-campaign-pop" role="dialog">
        <div class="modal-dialog modal-dialog-centered">

            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <h3>Start Campaign</h3>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="strtcamp">

                        <form class="">

                            <div class="row">
                                <div class="col text-right small">Start Falsh sale if target of </div>
                                <div class="col"><input class="form-control form-control-sm" type="text" placeholder="Tokens"></div>
                                <div class="col small"> meet in pledged</div>
                            </div>
                            <div class="row">
                                <div class="col text-right small">Discount in Flash sale </div>
                                <div class="col"><input class="form-control form-control-sm" type="text" placeholder="%"></div>
                                <div class="col small"></div>
                            </div>
                            <div class="row">
                                <div class="col text-right small">Sales Lasts till </div>
                                <div class="col">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="exampleRadios" id="exampleRadios1" value="option1" checked>
                                        <label class="form-check-label small" for="exampleRadios1">
                                            I Stop
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="exampleRadios" id="exampleRadios2" value="option2">
                                        <label class="form-check-label small" for="exampleRadios2">
                                            Target Meet <input class="form-control form-control-sm" type="text" placeholder="$">
                                        </label>

                                    </div>

                                </div>
                                <div class="col small"></div>
                            </div>
                            <div class="row">
                                <div class="col text-right small"><span>Revenue shared with super supporters monthly</span>
                                </div>
                                <div class="col"><input class="form-control form-control-sm" type="text" placeholder=""></div>
                                <div class="col small"></div>
                            </div>
                        </form>
                    </div>
                    <!-- Popup Ends -->
                </div>
                <div class="modal-footer">
                    <a href="#" class="btn btn-default btn-primary pull-right" data-dismiss="modal">Save</a>
                </div>
            </div>

        </div>
    </div>
    <!-- Modal -->
    <div id="variable-popup" class="modal fade vaiable-popup-edit" role="dialog">
        <div class="modal-dialog">

            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Product Variants</h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body" id="showvariation">

                </div>

            </div>

        </div>
    </div>


    <!-- Modal -->
    <div id="product-detete-message" class="modal fade" role="dialog">
        <div class="modal-dialog">

            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Please specify a reason</h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-success alert-success-mbt" id="reason_box_success" role="alert" style="display:none"></div>
                    <div class="alert alert-danger" id="reason_box_error" role="alert" style="display:none"></div>

                    <textarea class="form-control" name="reason" id="reason" placeholder="Your sellers would like to know, how can this be avoided in the future. Please make sure to elaborate their mistakes politely."></textarea>
                    <span class="error"></span>
                    <input type="hidden" name="product_id" id="product_id" value="">
                    <input type="hidden" name="product_status" id="product_status" value="">

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default btn-primary btn-simple w-100" id="change_status" style="pointer-events: auto;">Submit and Delete</button>

                </div>
            </div>

        </div>
    </div>
    <div id="product-reject-message" class="modal fade" role="dialog">
        <div class="modal-dialog">

            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Please specify a reason</h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-success alert-success-mbt" id="reject_box_success" role="alert" style="display:none"></div>
                    <div class="alert alert-danger" id="reject_box_error" role="alert" style="display:none"></div>

                    <textarea class="form-control" name="reason" id="reject_reason" placeholder="Your sellers would like to know, how can this be avoided in the future. Please make sure to elaborate their mistakes politely."></textarea>
                    <span class="error"></span>
                    <input type="hidden" name="reject_product_id" id="reject_product_id" value="">
                    <input type="hidden" name="reject_product_status" id="resject_product_status" value="">

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default btn-primary btn-simple w-100" id="reject_status" style="pointer-events: auto;">Submit and Reject</button>

                </div>
            </div>

        </div>
    </div>

    <!-- Modal for Reviews -->
    <div id="product-reviews" class="modal fade" role="dialog">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Product Reviews</h4>
                    <button type="button" class="btn btn-primary ml-auto" id="add_review_btn">Add Review</button>
                </div>
                <div class="modal-body" style="max-height: 572px;overflow:auto;">
                    <!-- Review Input Section -->
                    <div id="review_input_section" style="display: none; margin-bottom: 20px;">
                        <h5>Write a Review</h5>
                        <div class="form-group">
                            <label for="review_name">Name*</label>
                            <input type="text" id="review_name" class="form-control" placeholder="Enter your name" required>
                        </div>
                        <div class="form-group">
                            <label for="review_datetime">Date & Time*</label>
                            <input type="datetime-local" id="review_datetime" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="review_comment">Comment:</label>
                            <textarea id="review_comment" class="form-control" rows="3" placeholder="Write your comment"></textarea>
                        </div>
                        <div class="form-group">
                            <label for="review_images">Images (max 5):</label>
                            <input
                                type="file"
                                id="review_images"
                                name="gallery[]"
                                class="form-control"
                                accept="image/*"
                                multiple
                                onchange="previewImages(this)">
                        </div>
                        <div id="imagePreview" class="d-flex flex-wrap gap-2 mt-2"></div>

                        <div class='rating-stars clearfix'>
                            <div for="review_rating" style="font-weight: 600;color: #183b56;font-size: 16px;">Rating:</div>
                            <div class="star-rating-panel-mbt" style="margin-left: 10px;margin-top:-3px;">
                                <div class="star-rating">
                                    <span class="fa fa-star-o" data-rating="1"></span>
                                    <span class="fa fa-star-o" data-rating="2"></span>
                                    <span class="fa fa-star-o" data-rating="3"></span>
                                    <span class="fa fa-star-o" data-rating="4"></span>
                                    <span class="fa fa-star-o" data-rating="5"></span>
                                    <div class="ratingerror" style="color:red;font-size: 13px;margin-top: -10px;">
                                    </div>
                                    <input type="hidden" id="rating_count" name="rating_count" class="rating-value" value="1">
                                </div>
                            </div>
                        </div>
                        <br />
                        <button id="submit_review_btn" class="btn btn-primary">Submit</button>
                    </div>

                    <div id="reviews_container">
                        <p>No reviews available at the moment.</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary btn-simple" data-dismiss="modal">Close</button>
                </div>

            </div>
        </div>
    </div>


    <script>
        var $star_rating = $('.star-rating .fa');

        function showRatingValue() {
            var ratingValue = parseInt($('#rating_count').val());
            $star_rating.each(function() {
                var starRating = parseInt($(this).data('rating'));
                if (starRating <= ratingValue) {
                    $(this).removeClass('fa-star-o').addClass('fa-star'); // Filled star
                } else {
                    $(this).removeClass('fa-star').addClass('fa-star-o'); // Empty star
                }
            });
        }

        $(document).ready(function() {
            var $star_rating = $('.star-rating .fa');

            function SetRatingStar() {
                var ratingValue = parseInt($('#rating_count').val());
                $star_rating.each(function() {
                    var starRating = parseInt($(this).data('rating'));
                    if (starRating <= ratingValue) {
                        $(this).removeClass('fa-star-o').addClass('fa-star'); // Filled star
                    } else {
                        $(this).removeClass('fa-star').addClass('fa-star-o'); // Empty star
                    }
                });
            }
            $star_rating.on('click', function() {

                $star_rating.siblings('input.rating-value').val($(this).data('rating'));
                return SetRatingStar();
            });
            SetRatingStar();

        });

        let dt = new DataTransfer(); // Persistent
        function previewImages(input) {
            const previewContainer = document.getElementById('imagePreview');
            previewContainer.innerHTML = '';

            // Add only new files if total is less than 5
            Array.from(input.files).forEach((file) => {
                if (!file.type.startsWith('image/')) return;

                const alreadyExists = Array.from(dt.files).some(f => f.name === file.name && f.lastModified === file.lastModified);
                if (!alreadyExists && dt.files.length < 5) {
                    dt.items.add(file);
                }
            });

            // Disable input if limit reached
            if (dt.files.length >= 5) {
                input.disabled = true;
            }

            // Render all previews
            Array.from(dt.files).forEach((file, index) => {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const wrapper = document.createElement('div');
                    wrapper.style.position = 'relative';
                    wrapper.style.display = 'inline-block';
                    wrapper.style.margin = '5px';

                    const img = document.createElement('img');
                    img.src = e.target.result;
                    img.style.width = '100px';
                    img.style.height = '100px';
                    img.style.objectFit = 'cover';
                    img.style.border = '1px solid #ccc';
                    img.style.borderRadius = '6px';

                    const removeBtn = document.createElement('span');
                    removeBtn.innerHTML = '❌';
                    removeBtn.style.position = 'absolute';
                    removeBtn.style.top = '-8px';
                    removeBtn.style.right = '-8px';
                    removeBtn.style.background = 'red';
                    removeBtn.style.color = 'white';
                    removeBtn.style.borderRadius = '50%';
                    removeBtn.style.cursor = 'pointer';
                    removeBtn.style.padding = '2px 6px';
                    removeBtn.setAttribute('data-index', index);

                    removeBtn.onclick = function() {
                        removeImage(index, input);
                    };

                    wrapper.appendChild(img);
                    wrapper.appendChild(removeBtn);
                    previewContainer.appendChild(wrapper);
                };
                reader.readAsDataURL(file);
            });

            // Assign the updated file list to the input
            input.files = dt.files;
        }

        function removeImage(indexToRemove, input) {
            const newDT = new DataTransfer();
            Array.from(dt.files).forEach((file, index) => {
                if (index !== indexToRemove) {
                    newDT.items.add(file);
                }
            });

            dt = newDT;
            input.disabled = false; // Re-enable input if an image is removed
            input.files = dt.files;
            previewImages(input); // Re-render
        }
    </script>

    <script>
        $(document).ready(function() {
            // $(".alert-message-mbt").on("click", function (event) {
            //     event.preventDefault();
            //     //var result = confirm(" Are you sure want to removee.?");
            //     hreff = jQuery(this).attr('href');
            //     swal({
            //         title: "Are you sure?",
            //         text: "Are you sure you want to remove this product?",
            //         type: "warning",
            //         showCancelButton: true,
            //         confirmButtonClass: "btn-success",
            //         confirmButtonText: "Yes, Do it!",
            //         closeOnConfirm: false
            //     },
            //             function () {

            //                 swal("Success!", "deleted successfully", "success");
            //                 // $(this).unbind('click').click();
            //                 window.location.href = hreff;

            //             }); 
            //     //return false;

            // });
            $(".product-deactive").on("click", function(event) {
                event.preventDefault();
                //var result = confirm(" Are you sure want to removee.?");
                hreff = jQuery(this).attr('href');
                swal({
                        title: "Are you sure?",
                        text: "Are you sure you want to change status of this product?",
                        type: "warning",
                        showCancelButton: true,
                        confirmButtonClass: "btn-success",
                        confirmButtonText: "Yes, Do it!",
                        closeOnConfirm: false
                    },
                    function() {

                        swal("Success!", "Product status change successfully", "success");
                        // $(this).unbind('click').click();
                        window.location.href = hreff;

                    });
                //return false;

            });
            $(".product-approve").on("click", function(event) {
                event.preventDefault();
                //var result = confirm(" Are you sure want to removee.?");
                hreff = jQuery(this).attr('href');
                swal({
                        title: "Are you sure?",
                        text: "<?php echo !empty($alertMessage) ? $alertMessage : ""; ?>",
                        type: "warning",
                        showCancelButton: true,
                        confirmButtonClass: "btn-primary",
                        confirmButtonText: "Yes, Do it!",
                        closeOnConfirm: false
                    },
                    function() {

                        //swal("Success!", "Product status change successfully", "success");
                        // $(this).unbind('click').click();
                        window.location.href = hreff;

                    });
                //return false;

            });
            $(".product-reject").on("click", function(event) {
                event.preventDefault();
                //var result = confirm(" Are you sure want to removee.?");
                hreff = jQuery(this).attr('href');
                swal({
                        title: "Are you sure?",
                        text: "Are you sure you want to reject the product?",
                        type: "warning",
                        showCancelButton: true,
                        confirmButtonClass: "btn-success",
                        confirmButtonText: "Yes, Do it!",
                        closeOnConfirm: false
                    },
                    function() {

                        swal("Success!", "Product status change successfully", "success");
                        // $(this).unbind('click').click();
                        window.location.href = hreff;

                    });
                //return false;

            });
        });
        jQuery(document).on('change', '#store_campaign', function() {
            //var storeCampaign=$(this).attr('data-camp-status');

            jQuery('#vendor-filters').trigger('submit');
        });
        $(document).on('click', '.changeflash', function() {
            var data = {};
            data['product_id'] = $(this).attr('data-product-id');
            data['store_campaign_id'] = $(this).attr('data-camp-id');
            var aa = '';
            var that = this;

            currentContainer = $(this).closest('.dshprdbx');

            swal({
                    title: "Are you sure?",
                    text: "Are you sure you want to change the product for flash sale ?",
                    type: "warning",
                    showCancelButton: true,
                    confirmButtonClass: "btn-success",
                    confirmButtonText: "Yes, Do it!",
                    closeOnConfirm: false,
                    showLoaderOnConfirm: true,
                },
                function() {
                    if (data != "") {
                        sendreq(data, 'camp', 'flashsale', 'callback');
                    }
                });
        });

        function callback(res) {
            if (res == "success") {
                jQuery('.dshprdbx').each(function() {
                    aa = jQuery(this).closest('.border');
                    aa.removeClass('flashedsale');
                    currentContainer.addClass('flashedsale');
                    swal("Success!", "", "success");
                });
            } else {
                swal("", "Please activate your product before choosing it for flash sale.", "error");
            }
        }
        $(document).on("click", ".productvariation", function(e) {
            var data = {};
            data['product_id'] = $(this).attr('data-product-id');
            sendreq(data, 'product-variation', 'show', 'callbackvariation');
        });

        function callbackvariation(data) {
            $("#variable-popup").modal('show');
            $("#showvariation").html(data);
        }
        $(document).ready(function() {
            var storeCampaign = $("#store_campaign :selected").attr('data-camp-status');
            if (storeCampaign == 'support') {
                $('#current_camp').css('visibility', 'visible');
                //  $('#camp_progress').show();
            } else {
                $('#current_camp').css('visibility', 'hidden');
                // $('#camp_progress').hide();
            }
        });
        // $(function () {
        //     var url = "<?php echo URL::to('product-search?all=true'); ?>";
        //     // $("#search_products").autocomplete({
        //     //     classes: {
        //     //             "ui-autocomplete": "store-search-products",
        //     //         },
        //     //     source: url,
        //     //     minLength: 3,
        //     //     select: function () {
        //     //         //  jQuery('#product_search_form').trigger('submit');
        //     //     }
        //     // });
        // });
        //        $(document).on('click', ".page-link", function () {
        //            jQuery('#product_search_form-filters').trigger('submit');
        //            //return false;
        //        });

        $(document).on('click', '.delproduct', function() {
            var productId = $(this).attr('data-product-id');
            var status = $(this).attr('data-status');
            $('#product_id').val(productId);
            $('#product_status').val(status);
        });
        $(document).on('click', '#change_status', function() {
            if ($('#reason').val() != "") {
                $(".error").html("");
                var data = {};
                data['product_id'] = $('#product_id').val();
                data['status'] = $('#product_status').val();
                data['reason'] = $('#reason').val();
                sendreq(data, 'admin', 'product-status', 'callbackStatus');
            } else {
                $(".error").html("Reason of delete is required.");
            }
        });

        function callbackStatus(data) {
            if (data == 'success') {
                $("#reason_box_success").html('Product deleted successfully.');
                $("#reason_box_success").show();
                setTimeout(function() {
                    window.location.reload();
                }, 3000);
            } else if (data == 'flashsale') {
                $("#reason_box_success").hide();
                $("#reason_box_error").html('This product is currently enabled for flash sale and cannot be delete.');
                $("#reason_box_error").show();
            } else {
                $("#reason_box_error").html('Something went wrong please try again.');
                $("#reason_box_error").show();
            }

        }

        //reject
        var status;
        $(document).on('click', '.rejectproduct', function() {
            var productId = $(this).attr('data-reject-id');
            status = $(this).attr('data-reject-status');
            $('#reject_product_id').val(productId);
            $('#reject_product_status').val(status);

        });
        $(document).on('click', '#reject_status', function() {

            if ($('#reject_reason').val() != "") {
                $(".error").html("");
                var data = {};
                data['product_id'] = $('#reject_product_id').val();
                data['status'] = status;
                data['reason'] = $('#reject_reason').val();
                sendreq(data, 'admin', 'product-status', 'callbackReject');
            } else {
                $(".error").html("Reason of reject is required.");
            }
        });

        function callbackReject(data) {
            if (data == 'success') {
                $("#reject_box_success").html('Product rejected successfully.');
                $("#reject_box_success").show();
                setTimeout(function() {
                    window.location.reload();
                }, 3000);
            } else {
                $("#reject_box_error").html('Something went wrong please try again.');
                $("#reject_box_error").show();
            }

        }
    </script>
    <script>
        $(document).ready(function() {
            // Load reviews when the modal opens
            $(document).on('click', '.reviews-link', function() {
                var productId = $(this).data('product-id');
                $('#product-reviews').data('product-id', productId);
                // Fetch and display reviews
                loadReviews(productId);
            });

            // Show/Hide the review input section when "Add Review" button is clicked
            $('#add_review_btn').on('click', function() {
                $('#submit_review_btn').text('Submit');
                // $('#review_input_section').toggle(); // Toggle the visibility
                $('#review_input_section').show();
                clearReviewInput();
                showRatingValue();
            });

            // Function to fetch and display reviews
            function loadReviews(productId) {
                $.ajax({
                    url: '/reviews/' + productId,
                    type: 'GET',
                    success: function(data) {
                        var reviewsContainer = $('#reviews_container');
                        reviewsContainer.empty(); // Clear any existing reviews

                        if (data.length > 0) {
                            $.each(data, function(index, review) {
                                if (review.user_id === 12) {
                                    // Show admin's name directly if user_id is 12 (Admin)
                                    appendReview(review.name || 'Admin', review, true);
                                } else {
                                    // Fetch user name for non-admin users
                                    $.ajax({
                                        url: '/getUserById/' + review.user_id,
                                        type: 'GET',
                                        success: function(userData) {
                                            var userName = userData && userData.name ? userData.name : 'Unknown';
                                            appendReview(userName, review, false);
                                        },
                                        error: function() {
                                            appendReview('Unknown', review, false);
                                        }
                                    });
                                }
                            });
                        } else {
                            reviewsContainer.append('<p>No reviews available for this product.</p>');
                        }
                    },
                    error: function() {
                        $('#reviews_container').html('<p>Error fetching reviews. Please try again later.</p>');
                    }
                });

                // Helper function to append a review with the given name and review details
                function appendReview(name, review, isAdmin) {
                    let imageHTML = '';
                    if (review.gallery_urls && review.gallery_urls.length > 0) {
                        imageHTML = `
                    <div class="mt-2 d-flex flex-wrap gap-2">
                        ${review.gallery_urls.map(url => `
                            <img src="${url}" class="img-thumbnail" style="width: 100px; height: auto;" alt="Review Image" />
                        `).join('')}
                    </div>`;
                    }

                    $('#reviews_container').append(
                        `<div class="col-12 mb-3" style="padding:0px;font-family: Montserrat, sans-serif;">
                    <div class="card shadow p-3" style="border-radius: 10px; background-color: #f8f9fa;">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">${name}</h5>
                            <span style="color:#fff;font-family: Montserrat, sans-serif;font-weight:400;" class="badge ${isAdmin ? 'bg-success' : 'bg-secondary'}">
                                ${isAdmin ? 'Admin' : 'User'}
                            </span>
                        </div>
                        <div>
                            <p class="mt-2 reviewText">${review.comment || ''}</p>
                            <div style="display:flex;justify-content:space-between;align-items:center;">
                                <div class="star-rating">
                                    ${'<i class="fas fa-star text-warning"></i> '.repeat(review.rating)}
                                    ${'<i class="far fa-star text-warning"></i> '.repeat(5 - review.rating)}
                                </div>
                                <div class="d-flex justify-content-end">
                                    <a href="#"
                                        style="background-color:#073ba5!important;"
                                        class="btn btn-primary btn-sm mr-2 edit-review ${review.user_id !== 12 ? 'disabled' : ''}"
                                        data-review-id="${review.review_id}"
                                        data-name="${name}"
                                        data-created-at="${review.created_at}"
                                        data-rating="${review.rating}"
                                        data-comment="${review.comment}"
                                        data-gallery='${JSON.stringify(review.gallery_urls || []).replace(/'/g, '&#39;')}'
                                        >
                                        <i class="fas fa-pen"></i>
                                        </a>

                                    <a href="#" class="btn btn-danger btn-sm delete-review" data-user-id="${review.user_id}" data-review-id="${review.review_id}">
                                        <i class="fas fa-trash-alt"></i>
                                    </a>
                                </div>
                            </div>
                            ${imageHTML}
                        </div>
                    </div>
                </div>`
                    );
                }
            }


            // Handle edit review button click
            $(document).on('click', '.edit-review', function(e) {
                e.preventDefault();
                $('#submit_review_btn').text('Update');

                const reviewId = $(this).data('review-id');
                const name = $(this).data('name');
                const review_datetime = $(this).data('created-at');
                const rating = $(this).data('rating');
                const comment = $(this).data('comment');
                let galleryUrls = $(this).data('gallery');
                try {
                    galleryUrls = typeof galleryUrls === 'string' ? JSON.parse(galleryUrls) : (galleryUrls || []);
                } catch (e) {
                    galleryUrls = [];
                }
                console.log(galleryUrls)
                //returns  ['http://127.0.0.1:8000/uploads/review/photo6_17528.jpg', 'http://127.0.0.1:8000/uploads/review/photo5_17529.jpg']

                // Reset form
                $('#review_name').val(name);
                $('#rating_count').val(rating);
                $('#review_comment').val(comment);
                $('#review_datetime').val(new Date(review_datetime).toISOString().slice(0, 16));
                showRatingValue();
                $('#submit_review_btn').data('review-id', reviewId);

                // Reset image preview
                dt = new DataTransfer(); // Clear existing files
                $('#review_images').val('');
                $('#imagePreview').empty();

                // Append existing gallery images with remove button
                galleryUrls.forEach((url, i) => {
                    const imageId = url.split('_').pop().split('.')[0]; // Get "17529" from "photo5_17529.jpg"

                    const wrapper = document.createElement('div');
                    wrapper.style.position = 'relative';
                    wrapper.style.display = 'inline-block';
                    wrapper.style.margin = '5px';

                    const img = document.createElement('img');
                    img.src = url;
                    img.style.width = '100px';
                    img.style.height = '100px';
                    img.style.objectFit = 'cover';
                    img.style.border = '1px solid #ccc';
                    img.style.borderRadius = '6px';

                    const hiddenInput = document.createElement('input');
                    hiddenInput.type = 'hidden';
                    hiddenInput.className = 'existing-image-id';
                    hiddenInput.name = 'existing_gallery[]';
                    hiddenInput.value = imageId;

                    const removeBtn = document.createElement('span');
                    removeBtn.innerHTML = '❌';
                    removeBtn.style.position = 'absolute';
                    removeBtn.style.top = '-8px';
                    removeBtn.style.right = '-8px';
                    removeBtn.style.background = 'red';
                    removeBtn.style.color = 'white';
                    removeBtn.style.borderRadius = '50%';
                    removeBtn.style.cursor = 'pointer';
                    removeBtn.style.padding = '2px 6px';

                    removeBtn.onclick = function () {
                        wrapper.remove();
                        checkImageInputLimit(); // optional function, probably already defined
                    };

                    wrapper.appendChild(img);
                    wrapper.appendChild(removeBtn);
                    wrapper.appendChild(hiddenInput); // ✅ THIS IS THE KEY PART
                    $('#imagePreview')[0].appendChild(wrapper);
                });

                checkImageInputLimit();
                $('#review_input_section').show();
            });

            function checkImageInputLimit() {
                const existingImageCount = $('#imagePreview img').length;
                $('#review_images').prop('disabled', existingImageCount >= 5);
            }
            // Clear review input fields
            function clearReviewInput() {
                $('#review_name').val('');
                $('#rating_count').val('1');
                $('#review_comment').val('');
                $('#review_datetime').val('');
                $('#imagePreview').html('');
                dt = new DataTransfer(); // Reset image selection state
                $('#review_images').prop('disabled', false);
                $('#submit_review_btn').removeData('review-id');
            }


            // Submit a new review
            $('#submit_review_btn').on('click', function(event) {
                event.preventDefault(); // prevent default form behavior

                var name = $('#review_name').val();
                var rating = $('#rating_count').val();
                var comment = $('#review_comment').val();
                var review_datetime = $('#review_datetime').val();
                var productId = $('#product-reviews').data('product-id');
                var reviewId = $(this).data('review-id');
                var url = reviewId ? '/admin-reviews/' + reviewId : '/admin-reviews/' + productId;

                // Debug
                console.log(name, rating, comment, review_datetime, productId, reviewId, url);

                // Reset error styling
                $('#review_name, #review_datetime').removeClass('is-invalid');

                // Validation
                if (name.trim() === '') {
                    $('#review_name').addClass('is-invalid');
                    return;
                }
                if (review_datetime.trim() === '') {
                    $('#review_datetime').addClass('is-invalid');
                    return;
                }

                // Create FormData object
                var formData = new FormData();
                formData.append('product_id', productId);
                formData.append('rating', rating);
                formData.append('comment', comment);
                formData.append('review_datetime', review_datetime);
                formData.append('name', name);
                formData.append('_token', $('meta[name="csrf-token"]').attr('content'));

                // Add method override for PUT
                if (reviewId) {
                    formData.append('_method', 'PUT');
                }

                // Append new uploaded files
                var galleryFiles = document.getElementById('review_images').files;
                for (let i = 0; i < galleryFiles.length; i++) {
                    formData.append('gallery[]', galleryFiles[i]);
                }

                $('.existing-image-id').each(function () {
                    console.log('Preserving image ID:', $(this).val());
                    formData.append('existing_gallery[]', $(this).val());
                });

                $.ajax({
                    url: url,
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        $('#review_name').val('');
                        $('#rating_count').val('1');
                        $('#review_comment').val('');
                        $('#review_datetime').val('');
                        $('#review_images').val('');
                        $('#imagePreview').html('');
                        dt = new DataTransfer(); // Reset image selection state
                        $('#review_images').prop('disabled', false);
                        $('#review_input_section').hide();
                        $('#submit_review_btn').removeData('review-id').text('Submit');
                        loadReviews(productId); // Refresh reviews list
                    },
                    error: function(xhr) {
                        if (xhr.status === 422) {
                            var errors = xhr.responseJSON.errors;
                            console.log(errors);

                            // Highlight errors if needed
                            if (errors.name) {
                                $('#review_name').addClass('is-invalid');
                            }
                            if (errors.review_datetime) {
                                $('#review_datetime').addClass('is-invalid');
                            }
                            if (errors.rating) {
                                $('.ratingerror').text(errors.rating[0]);
                            }
                        } else {
                            alert('Error submitting review. Please try again later.');
                        }
                    }
                });
            });


            $('#product-reviews').on('hidden.bs.modal', function() {
                $('#review_input_section').hide(); // Hide the review input section
                clearReviewInput(); // Clear the input fields
            });

            $(document).on('click', '.delete-review', function(e) {
                e.preventDefault();

                var userId = $(this).data('user-id');
                var reviewId = $(this).data('review-id');

                // Confirm before deletion
                if (!confirm('Are you sure you want to delete this review?')) {
                    return;
                }
                var url = userId === 12 ? '/admin-reviews/' + reviewId : '/delete-review/' + reviewId;

                $.ajax({
                    url: url,
                    type: 'DELETE',
                    data: {
                        _token: '{{ csrf_token() }}' // CSRF token for Laravel
                    },
                    success: function(response) {
                        alert(response.success);

                        // Reload the reviews list to reflect the deletion
                        var productId = $('#product-reviews').data('product-id');
                        loadReviews(productId);
                    },
                    error: function(xhr) {
                        alert('Error deleting review. Please try again later.');
                    }
                });
            });

        });
    </script>
    
<script>
    document.querySelectorAll('.mallshark-selected-checkbox').forEach(function (checkbox) {
        checkbox.addEventListener('change', function () {
            const productId = this.getAttribute('data-product-id');
            const isSelected = this.checked ? 1 : 0;

            // Get the corresponding success message container
            const messageSpan = this.closest('.rght-prf').querySelector('.selected-success');

            fetch(`/api/products/${productId}/mallshark-selected`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    mallshark_selected: isSelected
                })
            })
            .then(res => res.json())
            .then(data => {
                // Show success message
                messageSpan.textContent = isSelected ? 'Selected' : 'Deselected';
                messageSpan.style.color = isSelected ? 'green' : 'red';

                // Clear message after 3 seconds
                setTimeout(() => {
                    messageSpan.textContent = '';
                }, 3000);
            })
            .catch(error => {
                console.error('Update failed for product ' + productId, error);
                messageSpan.textContent = 'Error';
                messageSpan.style.color = 'red';

                setTimeout(() => {
                    messageSpan.textContent = '';
                }, 3000);
            });
        });
    });
</script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
    // Select All / Individual product checkbox synchronization
    (function ($) {
        $(document).ready(function () {
            // When master checkbox is toggled, toggle all product checkboxes
            $(document).on('change', '#select_all_products_checkbox', function () {
                var checked = $(this).is(':checked');
                $('.product-select-checkbox').each(function () {
                    $(this).prop('checked', checked);
                });
            });

            // When any individual checkbox changes, update master checkbox
            $(document).on('change', '.product-select-checkbox', function () {
                var total = $('.product-select-checkbox').length;
                var checked = $('.product-select-checkbox:checked').length;
                $('#select_all_products_checkbox').prop('checked', total > 0 && total === checked);
            });

            // Initialize master checkbox state on page load
            var totalInit = $('.product-select-checkbox').length;
            var checkedInit = $('.product-select-checkbox:checked').length;
            $('#select_all_products_checkbox').prop('checked', totalInit > 0 && totalInit === checkedInit);

        });
    })(jQuery);
            document.getElementById("bulk_apply").addEventListener("click", function (event) {
                let ids = [];
                let statusSelect = document.querySelector('#bulkForm select[name="status"]');
                let selectedStatus = statusSelect.value;
                document.querySelectorAll('.product-select-checkbox:checked')
                    .forEach(cb => ids.push(cb.dataset.productId));

                // Store ids inside hidden input
                document.getElementById("selectedIds").value = ids;
                

               
                // Show SweetAlert if ,$sestatus is approve or disapprove
                if ((selectedStatus === 'approve' || selectedStatus === 'disapprove') && ids.length > 0) {
                    event.preventDefault();

                    let confirmText = selectedStatus === 'approve'
                        ? 'Are you sure you want to approve the selected products?'
                        : 'Are you sure you want to disapprove the selected products?';

                    swal({
                        title: "Are you sure?",
                        text: confirmText,
                        type: "warning",
                        showCancelButton: true,
                        confirmButtonClass: "btn-primary",
                        confirmButtonText: "Yes, Do it!",
                        closeOnConfirm: true
                    }, function() {
                        // ✅ Submit form via AJAX
                        console.log(selectedStatus)
                        console.log(ids)
                        
                        $.ajax({
                            url: '/admin/product-bulk-update',
                            method: 'POST',
                            data: {
                                    ids: ids.join(','), // send as string
                                    status: selectedStatus,
                                _token: $('meta[name="csrf-token"]').attr('content')
                                },
                                
                            success: function(response) {
                                if (response.success) {
                                    toastr.options = {
                                    closeButton: true,
                                    tapToDismiss: true,
                                    positionClass: 'toast-top-right',
                                    toastClass: 'toast-icon custom-toast-success',
                                    progressBar: true,
                                };
                                    toastr.success(
                                        selectedStatus === 'approve'
                                        ? 'Products approved successfully!'
                                        : 'Products disapproved successfully!'
                                    );
                                    // ✅ Reset the form (checkboxes, dropdowns, hidden inputs)
                                            $('#bulkForm')[0].reset(); // resets dropdown & form inputs
                                            // ✅ Uncheck all checkboxes manually (if not part of the form)
                                            $('.product-select-checkbox').prop('checked', false);
                                            $('#select_all_products_checkbox').prop('checked', false);
                                            
                                            window.history.replaceState({}, document.title, window.location.pathname);

                                            window.location.reload();
                                } else {
                                    toastr.options = {
                                    closeButton: true,
                                    tapToDismiss: true,
                                    positionClass: 'toast-top-right',
                                    toastClass: 'toast-icon custom-toast-success',
                                    progressBar: true,
                                };
                                    toastr.error(response.message || 'Something went wrong!');
                                }
                                
                            },
                            error: function(err) {
                                toastr.options = {
                                    closeButton: true,
                                    tapToDismiss: true,
                                    positionClass: 'toast-top-right',
                                    toastClass: 'toast-icon custom-toast-success',
                                    progressBar: true,
                                };

                                let message = 'API request failed!';
                                if (err.responseJSON && err.responseJSON.message) {
                                    message = err.responseJSON.message;
                                    if (err.responseJSON.deleted_ids && err.responseJSON.deleted_ids.length) {
                                        message += ' Deleted IDs: ' + err.responseJSON.deleted_ids.join(', ');
                                    }
                                }

                                toastr.error(message);
                                console.error(err);
                            }
                        });
                    });
                } else if (ids.length === 0 || selectedStatus === '') {
                    event.preventDefault();
                    toastr.options = {
                        closeButton: true,
                        tapToDismiss: true,
                        positionClass: 'toast-top-right',
                        toastClass: 'toast-icon custom-toast-success',
                        progressBar: true,
                    };
                    if (ids.length === 0 && selectedStatus === '') {
                        toastr.error('Please select at least one product and choose a status.');
                    } 
                    else if (ids.length === 0) {
                        toastr.error('Please select at least one product.');
                    } 
                    else if (selectedStatus === '') {
                        toastr.error('Please choose a product status.');
                    }
                    
                };
            });
</script>


<script>
// Listen for change event on all checkboxes with class "feature_status_checkbox"
$(document).on('change', '.feature_status_checkbox', function () {

    // Get product id from data-id attribute of checkbox
    let productId = $(this).data('id');

    // Check if checkbox is checked, if yes then status = 1 else = 0
    let status = $(this).is(':checked') ? 1 : 0;

    // Send AJAX request to update feature status in database
    $.ajax({
    url: "/admin/feature-status-update",
    method: "POST",
    data: {
        _token: "{{ csrf_token() }}",
        id: productId,
        feature_status: status
    },
    success: function (response) {

        toastr.options = {
            closeButton: true,
            tapToDismiss: true,
            positionClass: 'toast-top-right',
            toastClass: 'toast-icon custom-toast-success',
            progressBar: true,
        };

        toastr.success(response.message, 'Success');
        console.log("Updated:", response.message);
    },
    error: function () {
        toastr.options = {
            closeButton: true,
            tapToDismiss: true,
            positionClass: 'toast-top-right',
            toastClass: 'toast-icon custom-toast-success',
            progressBar: true,
        };
        toastr.error('Something went wrong!', 'Error');
    }
});

});
 // ================== CUSTOM MULTI-SELECT FOR STORE FILTER ==================
            $(document).ready(function() {
                // Toggle dropdown
                $('#multiselect_trigger').on('click', function(e) {
                    e.stopPropagation();
                    $('#multiselect_dropdown').toggleClass('active');
                });

                // Close dropdown when clicking outside
                $(document).on('click', function(e) {
                    if (!$(e.target).closest('.custom-multiselect').length) {
                        $('#multiselect_dropdown').removeClass('active');
                    }
                });

                // Search functionality
                $('#store_search').on('keyup', function() {
                    var searchText = $(this).val().toLowerCase();
                    $('.multiselect-option').each(function() {
                        var storeName = $(this).find('label').text().toLowerCase();
                        if (storeName.indexOf(searchText) > -1) {
                            $(this).show();
                        } else {
                            $(this).hide();
                        }
                    });
                });

                // Update display when checkboxes change
                $('.store-checkbox').on('change', function() {
                    updateStoreDisplay();
                });

                function updateStoreDisplay() {
                    var selectedStores = [];
                    var selectedIds = [];
                    $('.store-checkbox:checked').each(function() {
                        selectedStores.push({
                            id: $(this).val(),
                            name: $(this).data('name')
                        });
                        selectedIds.push($(this).val());
                    });

                    // Update hidden field with comma-separated IDs
                    $('#store_id_hidden').val(selectedIds.join(','));

                    var displayHtml = '';
                    if (selectedStores.length === 0) {
                        displayHtml = 'Select Stores';
                    } else {
                        displayHtml = selectedStores.map(function(store) {
                            return '<span class="store-tag">' + store.name + ' <span class="remove" data-id="' + store.id + '">×</span></span>';
                        }).join('');
                    }

                    $('#selected_stores_display').html(displayHtml);

                    // Add remove functionality to tags
                    $('.store-tag .remove').on('click', function(e) {
                        e.stopPropagation();
                        var storeId = $(this).data('id');
                        $('#store_' + storeId).prop('checked', false);
                        updateStoreDisplay();
                    });
                }

                function submitForm() {
                    // Only submit if at least one store is selected or if we want to clear filters
                    // Comment out auto-submit to avoid issues
                    // setTimeout(function() {
                    //     $('#product_search_form').submit();
                    // }, 300);
                }

                // Initialize display on page load
                updateStoreDisplay();
            });

            // ================== FACEBOOK FEED GENERATION ==================
            $('#generate_facebook_feed').on('click', function(e) {
                e.preventDefault();

                var ids = [];
                var storeIds = {};
                var storeNames = {};

                $('.product-select-checkbox:checked').each(function() {
                    var productId = $(this).data('product-id');
                    var storeId = $(this).data('store-id');

                    ids.push(productId);

                    if (storeId) {
                        if (!storeIds[storeId]) {
                            storeIds[storeId] = [];
                        }
                        storeIds[storeId].push(productId);

                        // Get store name from the product row
                        var storeName = $(this).closest('.dshprdbx').find('a[href*="storeHome"]').first().text().trim();
                        if (storeName) {
                            storeNames[storeId] = storeName;
                        }
                    }
                });

                if (ids.length === 0) {
                    toastr.options = {
                        closeButton: true,
                        tapToDismiss: true,
                        positionClass: 'toast-top-right',
                        toastClass: 'toast-icon custom-toast-success',
                        progressBar: true,
                    };
                    toastr.error('Please select at least one product to generate feed.');
                    return;
                }

                var stores = Object.keys(storeIds);
                var storeCount = stores.length;

                // Build summary message
                var summaryText = 'You are about to generate a Facebook Product Feed with:\n\n';
                summaryText += '• ' + ids.length + ' product(s)\n';
                summaryText += '• From ' + storeCount + ' store(s)\n\n';

                stores.forEach(function(storeId) {
                    var storeName = storeNames[storeId] || 'Store ' + storeId;
                    summaryText += storeName + ': ' + storeIds[storeId].length + ' products\n';
                });

                summaryText += '\nContinue?';

                // Show confirmation dialog
                swal({
                    title: "Generate Facebook Feed?",
                    text: summaryText,
                    type: "info",
                    showCancelButton: true,
                    confirmButtonClass: "btn-primary",
                    confirmButtonText: "Yes, Generate Feed!",
                    cancelButtonText: "Cancel",
                    closeOnConfirm: false,
                    showLoaderOnConfirm: true
                }, function(isConfirm) {
                    if (isConfirm) {
                        generateFacebookFeed(ids, storeCount, ids.length);
                    }
                });
            });

            function generateFacebookFeed(productIds, storeCount, productCount) {
                $.ajax({
                    url: '/admin/facebook-feed-multi',
                    method: 'POST',
                    data: {
                        product_ids: productIds,
                        _token: $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(res) {
                        if (res.url) {
                            swal({
                                title: "Success!",
                                html: true,
                                text: '<div style="text-align: left;">' +
                                    '<p><strong>Facebook feed generated successfully!</strong></p>' +
                                    '<p>📊 Products: ' + productCount + '</p>' +
                                    '<p>🏪 Stores: ' + storeCount + '</p>' +
                                    '<p>📁 Filename: ' + res.filename + '</p>' +
                                    '<hr>' +
                                    '<p><strong>Feed URL:</strong></p>' +
                                    '<input type="text" id="feed_url_input" value="' + res.url + '" ' +
                                    'style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; margin-top: 5px;" ' +
                                    'readonly onclick="this.select()">' +
                                    '<p style="margin-top: 10px; font-size: 12px; color: #666;">' +
                                    'Copy this URL and paste it into Facebook Catalog Manager' +
                                    '</p>' +
                                    '</div>',
                                type: "success",
                                confirmButtonText: "Copy URL & Close",
                                confirmButtonClass: "btn-primary"
                            }, function() {
                                // Copy to clipboard
                                var tempInput = document.createElement('input');
                                tempInput.value = res.url;
                                document.body.appendChild(tempInput);
                                tempInput.select();
                                document.execCommand('copy');
                                document.body.removeChild(tempInput);

                                toastr.options = {
                                    closeButton: true,
                                    tapToDismiss: true,
                                    positionClass: 'toast-top-right',
                                    toastClass: 'toast-icon custom-toast-success',
                                    progressBar: true,
                                };
                                toastr.success('URL copied to clipboard!');

                                // Optionally uncheck all products
                                $('.product-select-checkbox').prop('checked', false);
                                $('#select_all_products_checkbox').prop('checked', false);
                            });
                        } else {
                            swal("Error", 'Feed generated but no URL returned.', "error");
                        }
                    },
                    error: function(err) {
                        var errorMsg = 'Failed to generate feed.';
                        if (err.responseJSON && err.responseJSON.error) {
                            errorMsg = err.responseJSON.error;
                        }
                        swal("Error", errorMsg, "error");
                        console.error(err);
                    }
                });
            }
</script>
</main>
<!-- Content Ends-->
@endsection
