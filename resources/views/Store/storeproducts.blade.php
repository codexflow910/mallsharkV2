@extends('layouts.dashboard-main')
@section('content')
    @section('title', 'Products!')
    <!-- Content Starts -->
    <link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>

    <script src="{{ mix('js/app.js') }}" defer></script>
    <?php
    ?>
    <main>
        <!-- Dashboard Ends -->
        <div class="flex-parent-div bg-light-gray brand-orders-ai">
            <div class="change-password-page-cmt">
                @include('buyer.customsidebar')
            </div>
            <div class="dshbrdin dashprdlst storeproducts-page home-section cm-edit-ja">

                <div class="container">
                    <div class="row">
                        <div class="col-md-12">
                            @include('Store.sidebar')

                            <div class="dashprdlst">
                                <!-- Product Sorting Starts -->
                                <div class="row srtbx">
                                    <div class="col-md-12" style="padding: 0px!important;">
                                        <div
                                                class="d-flex justify-content-between custom-position-top align-items-center mngmob">
                                            <div class="heading-pr">
                                                <div class="hd-bt">
                                                    <h3 class="buyer-order-heading ">
                                                        <div class="d-flex align-items-center gap-3">
                                                            <a href="javascript:void(0)"
                                                               class="d-flex align-items-center gap-10px sidebar-admin-burger-menu"
                                                               id="open-sidebar-admin-panel">
                                                            <span id="menu-toggle-dashboard"
                                                                  class="shopzit-menu mt-0 ml-0 bx-menu">
                                                                <span></span><span></span><span></span> <span></span>
                                                            </span>
                                                                <!-- <span>Menu</span> -->
                                                            </a>
                                                            <span>Products</span>
                                                        </div>
                                                    </h3>

                                                </div>
                                                <div class="row productBtnRow">
                                                    <div class="col-sm-12 search-bar-top">
                                                        <div class="d-flex ">
                                                            <div class="align-self-center">
                                                            </div>
                                                            <form action="{{ URL::to('/store-products') }}" method="get"
                                                                  id="product_search_form">
                                                                <div class="bd-highlight bd-custom-padding">
                                                                    <div class="form-inline-large form-control-optn">
                                                                        <div class="inline-block-ele pos-rel"
                                                                             style="margin-left: 0px!important;">

                                                                            <input type="search" class="form-control"
                                                                                   id="search_products"
                                                                                   name="search_products"
                                                                                   value="<?php echo isset($_GET['search_products']) ? $_GET['search_products'] : ''; ?>"
                                                                                   placeholder="Search Products"/>
                                                                            <!--                                                    <button type="submit" class="btn">
                                                                            <i class="fa fa-search" aria-hidden="true"> </i>
                                                                        </button>     -->
                                                                        </div>
                                                                        <div
                                                                                class="custm-slect-form-optin inline-block-ele">
                                                                            <select class="form-control search-fieldd"
                                                                                    id="" name="status"
                                                                                    style="width: auto;">
                                                                                <?php
                                                                                $select_status_all = '';
                                                                                $select_status_pending = '';
                                                                                $select_status_active = '';
                                                                                $select_status_approve = '';
                                                                                $select_status_deactive = '';
                                                                                $select_status_reject = '';
                                                                                $select_status_draft = '';
                                                                                if (isset($_GET['status']) && $_GET['status'] == 'all') {
                                                                                    $select_status_all = 'selected="selected"';
                                                                                } elseif (isset($_GET['status']) && $_GET['status'] == 'pending') {
                                                                                    $select_status_pending = 'selected="selected"';
                                                                                } elseif (isset($_GET['status']) && $_GET['status'] == 'active') {
                                                                                    $select_status_active = 'selected="selected"';
                                                                                } elseif (isset($_GET['status']) && $_GET['status'] == 'deactive') {
                                                                                    $select_status_deactive = 'selected="selected"';
                                                                                } elseif (isset($_GET['status']) && $_GET['status'] == 'reject') {
                                                                                    $select_status_reject = 'selected="selected"';
                                                                                } elseif (isset($_GET['status']) && $_GET['status'] == 'approve') {
                                                                                    $select_status_approve = 'selected="selected"';
                                                                                } elseif (isset($_GET['status']) && $_GET['status'] == 'draft') {
                                                                                    $select_status_draft = 'selected="selected"';
                                                                                }

                                                                                ?>
                                                                                <option value="">Select Product Status
                                                                                </option>
                                                                                <option value="all" <?php echo $select_status_all; ?>>
                                                                                    All
                                                                                </option>
                                                                                <option value="pending" <?php echo $select_status_pending; ?>>
                                                                                    Pending
                                                                                </option>
                                                                                <option value="active" <?php echo $select_status_active; ?>>
                                                                                    Activated
                                                                                </option>
                                                                                <option value="approve" <?php echo $select_status_approve; ?>>
                                                                                    Approved
                                                                                </option>
                                                                                <option value="deactive"
                                                                                    <?php echo $select_status_deactive; ?>>
                                                                                    Deactivated
                                                                                </option>
                                                                                <option value="reject" <?php echo $select_status_reject; ?>>
                                                                                    Rejected
                                                                                </option>
                                                                                <option value="draft" <?php echo $select_status_draft; ?>>
                                                                                    Draft
                                                                                </option>
                                                                            </select>
                                                                        </div>
                                                                        <div
                                                                                class="btn-submit-optn inline-block-ele pos-rel">
                                                                            <button type="submit"
                                                                                    class=" btn btn-simple">
                                                                                Search
                                                                            </button>
                                                                        </div>

                                                                    </div>
                                                                </div>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="add-pr-lnk addProductBtn">
                                                {{-- <div class="px-5">
                                                    <button class="btn btn-primary btn-simple" id="export"><svg
                                                            xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                                            fill="currentColor" class="bi bi-save" viewBox="0 0 16 16">
                                                            <path
                                                                d="M2 1a1 1 0 0 0-1 1v12a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V2a1 1 0 0 0-1-1H9.5a1 1 0 0 0-1 1v7.293l2.646-2.647a.5.5 0 0 1 .708.708l-3.5 3.5a.5.5 0 0 1-.708 0l-3.5-3.5a.5.5 0 1 1 .708-.708L7.5 9.293V2a2 2 0 0 1 2-2H14a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V2a2 2 0 0 1 2-2h2.5a.5.5 0 0 1 0 1H2z" />
                                                        </svg>&nbsp;Export</button>
                                                </div> --}}
                                                <a class="btn btn-primary btn-simple small-btn-mbt"
                                                   style="margin-right: 6px;"
                                                   href="{{ URL::to('/add-product') }}">
                                                    Add Product
                                                </a>
                                                <a class="btn btn-primary btn-simple small-btn-mbt"
                                                   style="margin-right: 6px;"
                                                   href="{{ URL::to('/bulk-update') }}">
                                                    Bulk Update 
                                                </a>
                                                <a class="btn btn-primary btn-simple small-btn-mbt"
                                                   href="{{ URL::to('/store-dashboard') }}">
                                                    Back to Dashboard
                                                </a>
                                            </div>
                                        </div>
                                    </div>
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
                                @if (Session::has('errorMessage'))
                                        <?php $errorMessage = Session::get('SuccessMessage'); ?>
                                    <script>
                                        toastr.options = {
                                            closeButton: true,
                                            tapToDismiss: true,
                                            positionClass: 'toast-top-right',
                                            toastClass: 'toast-icon custom-toast-success',
                                            progressBar: true,
                                        };
                                        toastr.error('<?php echo $errorMessage; ?> ');
                                    </script>
                                @endif

                                <?php
                                if (!$results->isEmpty()){
                                    ?>

                                        <!-- Product List -->
                                    <?php

                                    //  echo '$results: <pre>' .print_r($results,true). '</pre>'; die();
                                foreach ($results as $res) {
                                    if ($res->product_type == 'variant') {
                                        $variableData = getvariableProduct($res->product_id);
                                    }
                                    $productImage = getAttachmentUrlById((int)$res->feature_Image, $size = 'medium');
                                    ?>
                                <div class="dshprdbx border storeProductsBoxCont">
                                    <div class="row">
                                        <div class="col-sm-6 lp-bar">
                                            <div class="row">
                                                <div class="col-md-3">
                                                    <a href="<?php echo URL::to('product/' . $res->slug); ?>">
                                                        <div class="img-div-mbt">
                                                            <img class="img-responsive"
                                                                 src="<?php echo !empty($productImage) ? $productImage : URL::asset('assets/images/placeholder.jpg'); ?>"
                                                                 alt="{{ $res->title }}" title="{{ $res->title }}">
                                                        </div>
                                                    </a>
                                                </div>

                                                <div class=" col-md-9">
                                                    <div class="body-content-mbt">
                                                            <?php if ($res->status == 3) { ?>
                                                        <h4 class="card-title">{{ $res->title }}</h4>
                                                        <?php } else{ ?>
                                                        <a href="<?php echo URL::to('product/' . $res->slug); ?>">
                                                            <h4 class="card-title">{{ $res->title }}</h4>
                                                        </a>
                                                        <?php } ?>
                                                            <?php
                                                            $timestamp = strtotime($res->created_at);
                                                            $date = date('d-M-Y', $timestamp);
                                                            ?>
                                                        <p class="small lgt-gry" style="margin-bottom:5px">
                                                            {{ $date }}
                                                        </p>
                                                        <p class="card-text" style="margin-bottom:5px;display:none;">
                                                            {{ strip_tags($res->short_description) }}</p>

                                                            <?php
                                                            $totalAdded = $res->current_stock + $res->sales;
                                                            ?>
                                                        <p class="inline-block-ele" style="margin-bottom:5px">
                                                                <?php if ($res->product_type == 'variant') { ?>
                                                            @if($res->on_sale == 1)
                                                                <span class="bold-element zit-price">Price: $
                                                            {{ $res->sale_price }}</span>
                                                                <del style="color: rgb(153, 153, 153);" class="bold-element zit-price">$
                                                                    {{ $res->price }}</del>
                                                               @if($res->discount_percentage)
                                                               <span class="bold-element" style="color: rgb(221, 13, 13);">
                                                                -{{ $res->discount_percentage }}%</span>
                                                               @else
                                                               <span class="bold-element" style="color: rgb(221, 13, 13);">
                                                                {{ $res->discount_percentage }}%</span>
                                                               @endif
                                                                    
                                                            @else
                                                                <span class="bold-element zit-price">Price: $
                                                            {{ $res->price }}</span>
                                                            @endif



                                                            <?php } else { ?>
                                                            @if($res->on_sale == 1)
                                                                <span class="bold-element zit-price">Price: $
                                                            {{ $res->sale_price }}</span>
                                                                <del style="color: rgb(153, 153, 153);" class="bold-element zit-price">$
                                                            {{ $res->price }}</del>
                                                            @if($res->discount_percentage)
                                                            <span class="bold-element" style="color: rgb(221, 13, 13);">
                                                             -{{ $res->discount_percentage }}%</span>
                                                            @else
                                                            <span class="bold-element" style="color: rgb(221, 13, 13);">
                                                             {{ $res->discount_percentage }}%</span>
                                                            @endif
                                                            @else
                                                                <span class="bold-element zit-price">Price: $
                                                            {{ $res->price }}</span>
                                                           @endif
                                                        <?php } ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-sm-6 lrt-bar  d-flex justify-content-between ">
                                            <div class="rw-tp wt-left-tab align-self-center">
                                                <div class="co-rvnu">
                                                    <div class="lft-prf d-flex justify-content-between">
                                                        <span id="">Featured Product:</span>
                                                            <?php
                                                            foreach ($featureData as $feature) {
                                                                if ($res->is_feature == 1) {
                                                                    echo 'Featured';
                                                                }
                                                            }
                                                            ?>
                                                                <!-- <div id="polygon-store"></div>
                                                       <input type="hidden" id="store_product_id" name="product_id" value="{{ $res->product_id }}"> -->
                                                    </div>
                                                </div>
                                                <div class="co-rvnu">
                                                    <div class="lft-prf d-flex justify-content-between">
                                                        <span>Product Type:</span>
                                                            <?php if ($res->product_type == 'variant') { ?>
                                                        <span>
                                                        <a href="javascript:;" class="productvariation"
                                                           data-product-id="{{ $res->product_id }}"
                                                           data-toggle="modal">Variable</a>
                                                    </span>
                                                        <?php }
                                                        if ($res->product_type == 'virtual') { ?>
                                                        <span>Virtual</span>
                                                        <?php } ?>
                                                            <?php if ($res->product_type == 'simple') { ?>
                                                        <span>Simple</span>
                                                        <?php } ?>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="rw-tp wt-rgt-tab align-self-center">
                                                <div class="co-rvnu ">
                                                    @if ($res->product_type != 'virtual')
                                                        <div class="rght-prf d-flex justify-content-between">
                                                            <span style="margin-top: 4px;">Current Stock:</span>
                                                                <?php if ($res->product_type == 'variant') {
                                                                ?>
                                                            <span style="margin-top: 4px;"><?php echo isset($variableData['current_stock']) ? $variableData['current_stock'] : 0; ?></span>
                                                            <?php } else { ?>
                                                            <span style="margin-top: 4px;"><?php echo isset($res->current_stock) ? $res->current_stock : 0; ?></span>
                                                            <?php } ?>
                                                        </div>
                                                    @endif
                                                    <div class="rght-prf d-flex justify-content-between">
                                                        <span style="margin-top: -6px;">All-time Sales:</span>
                                                            <?php if ($res->product_type == 'variant') { ?>
                                                        <span style="margin-top: -6px;"><?php echo isset($variableData['total_sales']) ? $variableData['total_sales'] : 0; ?></span>

                                                        <?php } else { ?>
                                                        <span style="margin-top: -6px;"><?php echo isset($res->sales) ? $res->sales : 0; ?></span>

                                                        <?php } ?>
                                                    </div>
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

                                                        if ($res->status < 3) {
                                                        if ($res->status != 2) {
                                                            ?>
                                                        <a href="<?php echo URL::to('/edit-product') . '/' . $res->product_id; ?>">Edit</a>
                                                        <span class="sprtr-lne"> |
                                                    </span>
                                                        <a class="alert-message-mbt"
                                                           href="<?php echo URL::to('/store-products-delete') . '/' . $res->product_id; ?>">Delete</a>
                                                        <span class="sprtr-lne"> | </span>
                                                        <?php }

                                                            // if($res->store_status != 0) {
                                                            ?>
                                                        <a class="product-deactive"
                                                           href="<?php echo URL::to('change-status/' . $res->product_id . '/' . $status_text); ?>"><?php echo $status_text; ?></a>
                                                            <?php //}
                                                        } else {
                                                        if ($res->status == 3) { ?>
                                                        <a href="<?php echo URL::to('/edit-product') . '/' . $res->product_id; ?>">Edit</a>
                                                        <span class="sprtr-lne"> |
                                                        <span class="red">Pending approval from moderators</span>
                                                        <?php } else if ($res->status == 4) { ?>
                                                        <a href="<?php echo URL::to('/edit-product') . '/' . $res->product_id; ?>">Edit</a> <span
                                                                    class="sprtr-lne"> |
                                                        </span>
                                                        <?php
                                                                echo '<span class="red">Product Rejected please make changes and again submit for approval</span>';
                                                            }elseif($res->status == 5){ ?>
                                                        <a href="<?php echo URL::to('/edit-product') . '/' . $res->product_id; ?>">Edit</a> <span
                                                                    class="sprtr-lne"> |
                                                        </span>
                                                        <?php
                                                                echo '<span class="red">Product Draft, To ensure a clear project vision, it essential that we finalize and incorporate the product requirements. </span>';
                                                                ?>
                                                            <?php }
                                                                // }
                                                            }
                                                                ?>
                                                            @if ($res->feature_status != 1 && $res->status == 1)
                                                                <span class="sprtr-lne"> | </span>
                                                                <a data-product-id="{{ !empty($res->product_id) ? $res->product_id : '' }}"
                                                                   class="make_feature" style="display: none;">Feature
                                                                this product</a>
                                                            @endif
                                                        <span class="sprtr-lne" style="display:none"> |<span> <a
                                                                        href="<?php echo URL::to('/editproductfaqs') . '/' . $res->product_id; ?>">All Faqs </a> <span
                                                                        class="sprtr-lne"> |
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
                                                        <a href="javascript:;" class="active-flash-an changeflash">
                                                            <i class="material-icons">
                                                                done
                                                            </i>Flash sale Enabled
                                                        </a>
                                                        <a href="javascript:;" id="select_flash_sale"
                                                           class="changeflash not-active"
                                                           data-product-id="{{ $res->product_id }}"
                                                           data-camp-id="{{ $campaignId }}">
                                                            Choose For Flash sale
                                                        </a>
                                                        <?php } else { ?>
                                                        <a href="javascript:;" id="select_flash_sale"
                                                           class="not-active changeflash"
                                                           data-product-id="{{ $res->product_id }}"
                                                           data-camp-id="{{ $campaignId }}">
                                                            Choose For Flash sale
                                                        </a>
                                                        <a href="javascript:;" class="active-flash-an changeflash"> <a
                                                                    href="javascript:;"
                                                                    class="active-flash-an changeflash">
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
                                <div class="row">
                                    <div class="custom-pagination col-sm-12 mt-15">
                                            <?php
                                            $q = [];
                                            if (isset($_GET['search_products'])) {
                                                $q['search_products'] = $_GET['search_products'];
                                            }
                                            if (isset($_GET['store_campaign'])) {
                                                $q['store_campaign'] = $_GET['store_campaign'];
                                            }
                                            //print_rr($q);
                                            echo $results->appends($q)->links('pagination.defaultpagination');
                                            ?>
                                    </div>
                                </div>
                                    <?php
                                } else {
                                    ?>
                                {{-- @include('search.no-record') --}}

                                <div class="no-inverntery-image"
                                     style="margin-bottom:20px;background: linear-gradient(0deg, rgba(207, 249, 255, 0.58) -106.72%, #FFF 58.82%)">
                                    <div class="reward-notification text-center">
                                        <p>
                                            <img src="{{ URL::asset('assets/images/warning.png') }}"
                                                 alt="not found any campaign" srcset="">
                                            <span>No products found in this section.</span>
                                        </p>
                                    </div>
                                </div>

                                <!-- <div class="no-inverntry-buttons text-center">
                               <button class="btn btn-primary"> <a href="/add-product"> Add Product</a></button>
                               <button class="btn btn-warning"> <a href="/importshopify"> Import from Shopify</a></button>

                            </div> -->
                                <!--                            <div class="alert alert-danger alert-success-mbt" role="alert">  No record found</div>                     -->
                                <?php } ?>
                                        <!-- Popup Starts -->


                            </div>
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
                                    <div class="col text-right small">Start Falsh sale if target of</div>
                                    <div class="col"><input class="form-control form-control-sm" type="text"
                                                            placeholder="Tokens"></div>
                                    <div class="col small"> meet in pledged</div>
                                </div>
                                <div class="row">
                                    <div class="col text-right small">Discount in Flash sale</div>
                                    <div class="col"><input class="form-control form-control-sm" type="text"
                                                            placeholder="%"></div>
                                    <div class="col small"></div>
                                </div>
                                <div class="row">
                                    <div class="col text-right small">Sales Lasts till</div>
                                    <div class="col">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="exampleRadios"
                                                   id="exampleRadios1" value="option1" checked>
                                            <label class="form-check-label small" for="exampleRadios1">
                                                I Stop
                                            </label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="exampleRadios"
                                                   id="exampleRadios2" value="option2">
                                            <label class="form-check-label small" for="exampleRadios2">
                                                Target Meet <input class="form-control form-control-sm" type="text"
                                                                   placeholder="$">
                                            </label>

                                        </div>

                                    </div>
                                    <div class="col small"></div>
                                </div>
                                <div class="row">
                                    <div class="col text-right small"><span>Revenue shared with super supporters
                                        monthly</span>
                                    </div>
                                    <div class="col"><input class="form-control form-control-sm" type="text"
                                                            placeholder=""></div>
                                    <div class="col small"></div>
                                </div>
                            </form>
                        </div>
                        <!-- Popup Ends -->
                    </div>
                    <div class="modal-footer">
                        <a href="javascript:;" class="btn btn-default btn-primary pull-right"
                           data-dismiss="modal">Save</a>
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
                        <h4 class="modal-title" style="font-size: 16px!important;font-weight:700;">Product Variants</h4>
                        <button type="button" id="modalClose" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body" id="showvariation">

                    </div>

                </div>

            </div>
        </div>

        <div id="featureProduct" class="modal fade start-campaign-pop" role="dialog" data-backdrop="static">
            <div class="modal-dialog modal-dialog-centered">

                <!-- Modal content-->
                <div class="modal-content">
                    <div class="large-loader">
                    <span>
                        <img src=" {{ asset('assets/images/loader-large.svg', env('HTTPS_STATUS')) }}"
                             class="img-fluid" alt="load icon" title="load icon">
                    </span>
                    </div>
                    <div class="modal-header">
                        <h3>Packages</h3>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <div class="strtcamp" style="height: auto">

                            <form class="">

                                <div class="row">

                                </div>
                                <div id="feature_payment"></div>
                                <input type="hidden" name="feature_product_id" id="feature_product_id">
                                <input type="hidden" name="package_id" id="package_id">
                                <input type="hidden" name="store_id" id="store_id"
                                       value="{{ !empty($storeData->store_id) ? $storeData->store_id : '' }}">
                                <input type="hidden" name="user_id" id="user_id"
                                       value="{{ !empty(Session::get('userData')['user_id']) ? Session::get('userData')['user_id'] : '' }}">
                            </form>
                        </div>
                        <!-- Popup Ends -->
                    </div>
                </div>

            </div>
        </div>
        <script>
            $(document).ready(function () {
                $(".alert-message-mbt").on("click", function (event) {
                    event.preventDefault();
                    //var result = confirm(" Are you sure want to removee.?");
                    hreff = jQuery(this).attr('href');
                    swal({
                            title: "Are you sure?",
                            text: "Are you sure you want to remove this product?",
                            type: "warning",
                            showCancelButton: true,
                            confirmButtonClass: "btn-success",
                            confirmButtonText: "Yes, Do it!",
                            closeOnConfirm: false
                        },
                        function () {

                            swal("Success!", "deleted successfully", "success");
                            // $(this).unbind('click').click();
                            window.location.href = hreff;

                        });
                    //return false;

                });
                $(".product-deactive").on("click", function (event) {
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
                        function () {

                            swal("Success!", "Product status change successfully", "success");
                            // $(this).unbind('click').click();
                            window.location.href = hreff;

                        });
                    //return false;

                });
                $(".product-approve").on("click", function (event) {
                    event.preventDefault();
                    //var result = confirm(" Are you sure want to removee.?");
                    hreff = jQuery(this).attr('href');
                    swal({
                            title: "Are you sure?",
                            text: "Are you sure you want to approve the product?",
                            type: "warning",
                            showCancelButton: true,
                            confirmButtonClass: "btn-success",
                            confirmButtonText: "Yes, Do it!",
                            closeOnConfirm: false
                        },
                        function () {

                            swal("Success!", "Product status change successfully", "success");
                            // $(this).unbind('click').click();
                            window.location.href = hreff;

                        });
                    //return false;

                });
                $(".product-reject").on("click", function (event) {
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
                        function () {

                            swal("Success!", "Product status change successfully", "success");
                            // $(this).unbind('click').click();
                            window.location.href = hreff;

                        });
                    //return false;

                });
            });
            jQuery(document).on('change', '#store_campaign', function () {
                //var storeCampaign=$(this).attr('data-camp-status');

                jQuery('#vendor-filters').trigger('submit');
            });
            $(document).on('click', '.changeflash', function () {
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
                    function () {
                        if (data != "") {
                            sendreq(data, 'camp', 'flashsale', 'callback');
                        }
                    });
            });

            function callback(res) {
                if (res == "success") {
                    jQuery('.dshprdbx').each(function () {
                        aa = jQuery(this).closest('.border');
                        aa.removeClass('flashedsale');
                        currentContainer.addClass('flashedsale');
                        swal("Success!", "", "success");
                    });
                } else {
                    swal("", "Please activate your product before choosing it for flash sale.", "error");
                }
            }

            $(document).on("click", ".productvariation", function (e) {
                var data = {};
                data['product_id'] = $(this).attr('data-product-id');
                sendreq(data, 'product-variation', 'show', 'callbackvariation');
            });

            function callbackvariation(data) {
                $("#variable-popup").modal('show');
                $("#showvariation").html(data);
            }

            $(document).ready(function () {
                var storeCampaign = $("#store_campaign :selected").attr('data-camp-status');
                if (storeCampaign == 'support') {
                    $('#current_camp').css('visibility', 'visible');
                    //  $('#camp_progress').show();
                } else {
                    $('#current_camp').css('visibility', 'hidden');
                    // $('#camp_progress').hide();
                }
            });
            // $(function() {
            //     var url = "<?php echo URL::to('product-search?role=storeowner'); ?>";
            //     $("#search_products").autocomplete({
            //         classes: {
            //             "ui-autocomplete": "storeViewProducts",
            //         },
            //         source: url,
            //         minLength: 3,
            //         select: function() {
            //             //jQuery('#product_search_form').trigger('submit');
            //         }
            //     });
            // });
            //        $(document).on('click', ".page-link", function () {
            //            jQuery('#product_search_form-filters').trigger('submit');
            //            //return false;
            //        });
            $(function () {
                $('#modalClose').on('click', function () {
                    $('#variable-popup').modal('hide');
                })
            })
            $(".make_feature").on("click", function () {
                var product_id = $(this).attr('data-product-id');
                $("#feature_product_id").val(product_id);
                $("#featureProduct").modal('show');
            });
            $(".buypkg").on("click", function () {
                // var package_id = $(this).attr('data-id');
                // var product_id = $("#feature_product_id").val();
                // var store_id = $("#store_id").val();
                // var user_id = $("#user_id").val();
                // var data = {};
                // data['product_id'] = product_id;
                // data['package_id'] = package_id;
                // data['store_id'] = store_id;
                // data['user_id'] = user_id;
                // data["_token"] = "{{ csrf_token() }}";
                // $.ajax({
                //     url: '/make-feature-product',
                //     type: 'POSt',
                //     data: data,
                //     success: function(result) {
                //         console.log(result);
                //     }
                // });
            });
            $('.close').on('click', function () {
                $("#featureProduct").modal('hide');
            });
            $('#export').on('click', function (e) {
                $.ajax({
                    url: "{{ route('csv.export') }}",
                    type: 'GET',
                    success: function (response) {
                        // Trigger a download using Blob and createObjectURL
                        var blob = new Blob([response], {
                            type: 'text/csv'
                        });
                        var url = window.URL.createObjectURL(blob);
                        var a = document.createElement('a');
                        a.href = url;
                        a.download = 'products.csv';
                        document.body.appendChild(a);
                        a.click();
                        window.URL.revokeObjectURL(url);
                    },
                    error: function (error) {
                        console.log(error);
                    }
                });


            })
        </script>

    </main>
    <!-- Content Ends-->
@endsection
