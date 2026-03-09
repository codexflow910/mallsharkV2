@extends('layouts.dashboard-main')
@section('content')
@section('title', 'All Stores')
<!-- Content Starts -->
<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
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
    }

    #loading-image {
        position: absolute;
        top: 50%;
        z-index: 100;
        width: 150px;
        left: 50%;
        transform: translate(-50%,-50%);
    }
    .rght-prf.d-flex.store-statuses.justify-content-evenly {
    justify-content:start;
}
#ordersTable tbody tr:nth-child(even) {
    background-color: #f9f9f9;
}

#ordersTable tbody tr:hover {
    background-color: #eef6ff;
}

#ordersTable th, #ordersTable td {
    padding: 12px;
    border-bottom: 1px solid #ddd;
    white-space: nowrap;
}

#ordersTable input[type="checkbox"] {
    transform: scale(1.2);
    cursor: pointer;
}

@media (max-width: 600px) {
    #ordersTable th, #ordersTable td {
        padding: 8px;
        font-size: 12px;
    }

    #transferModal > div {
        padding: 20px;
    }
}

</style>
<main>
    <!-- @include('Store.sidebar') -->
    <!-- Dashboard Ends -->
    <div class="dshbrdin dashprdlst storeproducts-page ">
        <div class="container">
            <div class="row">
                <div class="col-md-12"> 
                    <div class="dashprdlst">
                        <!-- Product Sorting Starts -->

                        @if(Session::has('SuccessMessage'))
                        <div class="alert alert-success alert-success-mbt" id="success_message" role="alert">
                            {{ Session::get('SuccessMessage') }}
                            <script>
                            setTimeout(function () {
                                jQuery("#success_message").fadeOut('slow')
                            }, 4000);
                            </script>
                        </div>
                        @endif
                        @if(Session::has('errorMessage'))
                        <div class="alert alert-danger" id="error_messages" role="alert">{{ Session::get('errorMessage') }}</div>
                        <script>
                            setTimeout(function () {
                                jQuery("#error_messages").fadeOut('slow')
                            }, 4000);
                        </script>
                        @endif
                        <br>
                        <?php

                            use function Ramsey\Uuid\v1;

                           
                            ?>
                            <div class="row">
                                <div class="col-sm-12 search-bar-top">                                
                                    <div class="d-flex justify-content-between w100p br-top-thin mt-20"> 

                                        <div class="align-self-center">
                                            <h3 class="mb-0">All Stores </h3>
                                        </div>
                                        <form action="{{URL::to('/admin/stores')}}" method="get" id="product_search_form"> 
                                            <div class="bd-highlight">
                                                <div class="form-inline-large form-control-optn">
                                                    <div class="inline-block-ele pos-rel">
                                                        <input type="search" class="form-control" id="search_products" name="search_stores" value="<?php echo isset($_GET['search_stores']) ? $_GET['search_stores'] : ''; ?>" placeholder="Search Stores" />
                                                    </div>
                                                    <div class="custm-slect-form-optin inline-block-ele">
                                                        <select class="form-control search-fieldd" id="" name="status" style="width: auto;">
                                                            <?php
                                                            $select_status_all = '';
                                                            $select_status_active = '';
                                                            $select_status_deactive = '';
                                                            $select_status_pending = '';
                                                            $select_status_reject = '';
                                                            $select_status_almost_active = '';
                                                            
                                                            if (isset($_GET['status']) && $_GET['status'] == 'all') {
                                                                $select_status_all = 'selected="selected"';
                                                            } else if (isset($_GET['status']) && $_GET['status'] == 'active') {
                                                                $select_status_active = 'selected="selected"';
                                                            } else if (isset($_GET['status']) && $_GET['status'] == 'deactive') {
                                                                $select_status_deactive = 'selected="selected"';
                                                            }  else if (isset($_GET['status']) && $_GET['status'] == 'pending') {
                                                                $select_status_pending = 'selected="selected"';
                                                            } else if (isset($_GET['status']) && $_GET['status'] == 'reject') {
                                                                $select_status_reject = 'selected="selected"';
                                                            } else if (isset($_GET['status']) && $_GET['status'] == 'almost-active') {
                                                                $select_status_almost_active = 'selected="selected"';
                                                            } 
                                                            ?>
                                                            <option value="">Select Store Status</option>
                                                            <option value="all" <?php echo $select_status_all; ?>>All</option>
                                                            <option value="active" <?php echo $select_status_active; ?>>Active</option>
                                                            <option value="deactive" <?php echo $select_status_deactive; ?>>Deactivate</option>
                                                            <option value="pending" <?php echo $select_status_pending; ?>>Pending</option>
                                                            <option value="reject" <?php echo $select_status_reject; ?>>Reject</option>
                                                            <option value="almost-active" <?php echo $select_status_almost_active; ?>>Almost Active</option>
                                                        </select>
                                                    </div>
                                                    <div class="btn-submit-optn inline-block-ele pos-rel">
                                                        <button type="submit" class="btn btn-simple small-btn-mbt"> Search
                                                        </button>                                                    
                                                    </div>

                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>                            
                            <!-- Product List --> 
                            <?php
                             if (!$results->isEmpty()) {
                            foreach ($results as $res) {
                                // if($res->product_count >= 10){

                                $store_logo = getAttachmentUrlById((int) $res->store_logo, $size = 'small');
                                ?>
                                <div class="dshprdbx border">
                                    <div class="row">     
                                        <div class="col-sm-6 lp-bar">
                                            <div class="row">
                                                <div class="col-md-3"> 
                                                    <div class="img-div-mbt">
                                                        <a href="{{ URL::to('/brand/'.$res->store_slug) }}" target="_blank">
                                                             <img class="img-responsive" src="<?php echo !empty($store_logo) ? $store_logo: URL::to('/assets/images/placeholder.jpg') ?>" alt="{{$res->store_name}}" title="{{$res->store_name}}">
                                                        </a>
                                                    </div>
                                                </div>   
                                                <div class=" col-md-9">
                                                    <div class="body-content-mbt">
                                                        <a href="{{ URL::to('/brand/'.$res->store_slug) }}" target="_blank">
                                                             <h4 class="card-title">{{$res->store_name}}</h4>
                                                        </a>
                                                        <?php
                                                        $timestamp = strtotime($res->created_at);
                                                        $date = date('d-M-Y', $timestamp);
                                                        ?>
                                                        <p class="small lgt-gry" style="margin-bottom:5px">{{$date}}</p>          
                                                    </div> 
                                                </div>
                                               
                                            </div>
                                        </div>
                                        


                                    
                                        <div class="col-sm-6 lrt-bar  d-flex justify-content-between ">
                                            <div class="rw-tp wt-left-tab align-self-center">
                                                <div class="co-rvnu">
                                                    <div class="rght-prf d-flex  store-statuses justify-content-evenly">
                                                        <span>Owner Name:</span>
                                                        <span class="ml-2" id="ownerName">{{$res->name}}</span>
                                                        <p style="cursor: pointer; margin-left: 10px;" class="copyButton" data-target="ownerName"><i class="fa fa-clipboard"></i></p>
                                                    </div> 
                                                    <div class="rght-prf d-flex store-statuses justify-content-evenly">
                                                    <span>Store Status:</span>
                                                        <?php 
                                                       $store_status = $res->store_status;
                                                       $super_status = $res->super_status;
                                                       $storeStatus = '';
                                                       if($store_status == 1){
                                                        // pending
                                                        $storeStatus = 'Pending';
                                                        
                                                       }else if($store_status == 3){
                                                        // rejected
                                                        $storeStatus = 'Rejected';
                                                       }else{
                                                        // approved
                                                        if($super_status == 1){
                                                            if($store_status == 2){
                                                                $storeStatus = 'Activated';
                                                            }
                                                            else if($store_status == 0){
                                                                
                                                                $storeStatus = 'Deactivated';
                                                            }
                                                        }else{
                                                            $storeStatus = 'Disabled';
                                                        }

                                                       }
                                                       $stripe = $res->stripe;
                                                       $stripe_status = '';
                                                       if($stripe == 1){
                                                        $stripe_status = 'Connected';
                                                    }else{
                                                        $stripe_status = 'Disconnected';

                                                    } ?>
                                                    <span class="getstore-status ml-2">{{$storeStatus}}</span>
                                                       
                                                    </div>
                                                    <div class="rght-prf d-flex store-statuses justify-content-evenly align-items-center">
                                                        <span>Stripe Status:</span>
                                                        <span class="selected-success"></span>
                                                        <input 
                                                            type="checkbox"
                                                            class=" ml-3"
                                                            data-store-id="{{ $res->store_id }}"
                                                            style="width: 16px; height: 16px;"
                                                            {{ $res->stripe ? 'checked ' : 'disabled' }}
                                                        >
                                                        
                                                    </div>
                                                    <div class="rght-prf d-flex  store-statuses justify-content-evenly">
                                                        <span>Store Logo:</span>
                                                        <input 
                                                            type="checkbox"
                                                            class=" ml-3"
                                                            data-store-id="{{ $res->store_id }}"
                                                            style="width: 16px; height: 16px;"
                                                            {{ $res->store_logo ? 'checked ' : 'disabled' }}
                                                        >
                                                    </div>
                                                    @if (canAccess('allow-shopify'))
                                                    <div class="rght-prf d-flex justify-content-evenly">
                                                        <span>Shopify Connect: </span>
                                                        <span class="selected-success"></span>
                                                            <input type="checkbox" 
                                                                class="shopify-connected-checkbox ml-3" 
                                                                data-store-id="<?php echo $res->store_id; ?>" 
                                                                name="shopify_connected" 
                                                                value="" 
                                                                style="width: 16px; height: 16px;"
                                                                <?php if ($res->is_shopify_allowed) echo 'checked'; ?>>
                                                                <span class="selected-success ml-2"></span>
                                                        </span>
                                                                <span class="selected-success ml-2"></span>
                                                    </div>
                                                    @endif
                                                    @if (canAccess('enable-ai'))
                                                    <div class="rght-prf d-flex store-statuses justify-content-evenly align-items-center">
                                                        <span for="ai_enabled_{{ $res->store_id }}" class="me-2">Enable AI:</span>
                                                        <span class="selected-success"></span>
                                                        <input 
                                                            type="checkbox"
                                                            id="ai_enabled_{{ $res->store_id }}"
                                                            class="mallshark-ai-checkbox ml-2"
                                                            data-store-id="{{ $res->store_id }}"
                                                            style="width: 16px; height: 16px;"
                                                            {{ $res->ai_enabled ? 'checked' : '' }}
                                                        >
                                                    </div>
                                                    @endif
                                                    <div class="rght-prf d-flex store-statuses justify-content-evenly align-items-center">
                                                        <span for="unique_product_selling_{{ $res->store_id }}" class="me-2">Enable Unique Product Selling:</span>
                                                        <span class="selected-success"></span>
                                                        <input 
                                                            type="checkbox"
                                                            id="unique_product_selling_{{ $res->store_id }}"
                                                            class="unique-product-selling-checkbox ml-2"
                                                            data-store-id="{{ $res->store_id }}"
                                                            style="width: 16px; height: 16px;"
                                                            {{ $res->unique_product_selling ? 'checked' : '' }}
                                                        >
                                                    </div>
                                                </div>
                                            </div> 
                                            <div class="rw-tp wt-rgt-tab align-self-center">
                                                <div class="co-rvnu ">
                                                    <div class="rght-prf d-flex store-statuses justify-content-evenly">
                                                        <span>Email:</span>
                                                        <span class="ml-2" id="ownerEmail">{{$res->email ?? null}}</span>
                                                        <p style="cursor: pointer; margin-left: 10px;" class="copyButton" data-target="ownerEmail"><i class="fa fa-clipboard"></i></p>
                                                    </div> 
                                                    <div class="rght-prf d-flex store-statuses justify-content-evenly align-items-center">
                                                        <span class="me-2">Banner Uploaded:</span>
                                                        @php
                                                            $bannerIds = @unserialize($res->banner_image_id) ?: [];
                                                        @endphp

                                                        <input 
                                                            type="checkbox"
                                                            class=" ml-3"
                                                            data-store-id="{{ $res->store_id }}"
                                                            style="width: 16px; height: 16px;"
                                                            {{ !empty($bannerIds) ? 'checked ' : 'disabled' }}
                                                        >
                                                    </div> 
                                                    <div class="rght-prf d-flex store-statuses justify-content-evenly">
                                                        <span>Total Products:</span>
                                                        <span class="ml-2">{{$res->product_count}}</span>
                                                        
                                                    </div> 
                                                    <div class="rght-prf d-flex store-statuses justify-content-evenly">
                                                    <span>Total Orders:</span>
                                                    <span class="ml-2">{{$res->order_count}}</span>
                                                    </div>
                                                    @if (canAccess('allow-wholesale'))
                                                    <div class="rght-prf d-flex justify-content-evenly align-items-center">
                                                        <span>Allow Wholesale: </span>
                                                        <span class="selected-success"></span>
                                                            <input type="checkbox" 
                                                                class="wholesale-connected-checkbox ml-3" 
                                                                data-store-id="<?php echo $res->store_id; ?>" 
                                                                name="wholesale_connected" 
                                                                value="" 
                                                                style="width: 16px; height: 16px;"
                                                                <?php if ($res->is_wholesale_allowed) echo 'checked'; ?>>
                                                    </div>
                                                    @endif                                                      
                                                </div>
                                            </div>
                                            {{-- @if(!empty($res->name))
                                            <div class="rw-tp wt-rgt-tab align-self-center">
                                                <div class="co-rvnu ">
                                                    <div class="rght-prf d-flex store-statuses justify-content-evenly">
                                                        <span>Referred By :</span>
                                                        <span class="ml-2">{{$res->name ?? null}}</span>
                                                        
                                                    </div> 
                                                    <div class="rght-prf d-flex store-statuses justify-content-evenly">
                                                  
                                                    </div>                                                     
                                                </div>
                                            </div>
                                            @endif --}}
                                        </div>
                                    </div>
                                   
                                    <div class="row pr-edit-strip">
                                        <div class="col-sm-12">
                                            <div class="d-flex justify-content-between w100p">
                                                <div class="edit-delete-box align-self-center">
                                                    <p> 
                                                        <?php
                                                        $status_text = 'Deactivate';
                                                        if ($res->store_status == 2) {
                                                            $status_text = 'Activate';
                                                        }elseif($res->store_status == 0){
                                                            $status_text = 'Deactivate';
                                                        }elseif($res->store_status == 1){
                                                            $status_text = 'Pending';
                                                        }
                                                        $userData = getUserById($res->user_id);
                                                           
                                                            ?>
                                                            <div class="d-flex">
                                                            @if (canAccess('view-payouts'))
                                                            <a class="initiate-order-transfer-btn" data-store_id="{{ $res->store_id }}" href="javascript:void(0)">
                                                               Pay Seller
                                                            </a>
                                                            <span class="sprtr-lne"> | </span>
                                                            @endif
                                                            @if (canAccess('view-commissions'))
                                                            <a class="initiate-payment-transfer-btn" data-store_id="{{ $res->store_id }}" href="javascript:void(0)">
                                                               Edit Comission
                                                            </a>
                                                            <span class="sprtr-lne"> | </span>
                                                            @endif
                                                            <?php 
                                                               
                                                            if($store_status == 1){ ?>
                                                            @if (canAccess('approve-vendor'))
                                                            <a class="foractivation-store approved-store" data-store_id="<?php echo $res->store_id; ?>" data-user_id="<?php echo $res->user_id; ?>"  data-store_status="<?php echo $res->store_status; ?>" href="javascript:void(0)">Approve </a> <span class="sprtr-lne"> |  </span>
                                                            @endif
                                                            
                                                            @if (canAccess('reject-vendor'))
                                                            <a class="foractivation-store rejectstore" data-store_id="<?php echo $res->store_id; ?>" data-user_id="<?php echo $res->user_id; ?>"  data-reject-status="<?php echo $res->store_status; ?>" data-reject-status="Reject" data-toggle="modal" data-target="#store-reject-message" href="javascript:void(0)">Reject Store</a> <span class="sprtr-lne"> |  </span>
                                                            @endif
                                                            <?php }else if($store_status == 3){ ?>
                                                                @if (canAccess('approve-vendor'))
                                                                <a class="foractivation-store approved-store" data-store_id="<?php echo $res->store_id; ?>" data-user_id="<?php echo $res->user_id; ?>"  data-store_status="<?php echo $res->store_status; ?>" href="javascript:void(0)">Approve </a> <span class="sprtr-lne"> |  </span>
                                                                @endif
                                                                <?php  }else{ ?>
                                                            
                                                            <?php if($super_status == 1){
                                                                if($store_status == 2){ ?>
                                                                @if (canAccess('deactivate-vendor'))
                                                                  <a class="deactivate-store " data-store_id="<?php echo $res->store_id; ?>" data-user_id="<?php echo $res->user_id; ?>" data-store_status="<?php echo $res->store_status; ?>" href="javascript:void(0)">Deactivate </a><span class="sprtr-lne"> |  </span>
                                                                @endif
                                                                @if (canAccess('disable-vendor'))
                                                                  <a class="disable-store " data-store_id="<?php echo $res->store_id; ?>" data-user_id="<?php echo $res->user_id; ?>" data-store_status="<?php echo $res->store_status; ?>" href="javascript:void(0)">Disable </a><span class="sprtr-lne"> |  </span>
                                                                @endif
                                                                <?php }
                                                                else if($store_status == 0){ ?>
                                                                    @if (canAccess('activate-vendor'))
                                                                    <a class="activate-store" data-store_id="<?php echo $res->store_id; ?>" data-user_id="<?php echo $res->user_id; ?>"  data-store_status="<?php echo $res->store_status; ?>" data-stripe="<?php echo $res->stripe; ?>" data-iscomplete="<?php echo $res->is_completed; ?>" href="javascript:void(0)">Activate </a> <span class="sprtr-lne"> |  </span>
                                                                    @endif

                                                                    @if (canAccess('disable-vendor'))
                                                                    <a class="disable-store " data-store_id="<?php echo $res->store_id; ?>" data-user_id="<?php echo $res->user_id; ?>" data-store_status="<?php echo $res->store_status; ?>" href="javascript:void(0)">Disable </a><span class="sprtr-lne"> |  </span>
                                                                    @endif
                                                                <?php } 
                                                            }else{ ?>
                                                                @if (canAccess('enable-vendor'))
                                                                <a class="enable-store" data-store_id="<?php echo $res->store_id; ?>" data-user_id="<?php echo $res->user_id; ?>" data-store_status="<?php echo $res->store_status; ?>" href="javascript:void(0)">Enable </a><span class="sprtr-lne"> |  </span>
                                                                @endif
                                                            <?php }
    
                                                           }
                                                           ?>                                                      
                                                  
                                                            @if (canAccess('contact-seller'))   
                                                            <a href="mailto:<?php echo!empty($userData->email) ? $userData->email : "" ?>?Subject=Product issue" target="_top">Contact Seller</a>
                                                            <span class="sprtr-lne"> | </span>
                                                            @endif

                                                            @if (canAccess('access-store'))
                                                            <a href="{{ env('ADMIN_URL') }}/auth?email={{ $userData->email ?? '' }}" target="_blank">Access</a><span class="sprtr-lne"> | </span>
                                                            @endif

                                                            @if (canAccess('reset-ai-token'))
                                                            <a class="reset-ai-token" data-store_id="<?php echo $res->store_id; ?>" data-user_id="<?php echo $res->user_id; ?>" data-store_status="<?php echo $res->store_status; ?>" href="javascript:void(0)">Reset AI Tokens</a>
                                                            @endif

                                                            @if (canAccess('edit-user'))
                                                            <span class="sprtr-lne"> | </span>
                                                            <a class="delete-store" data-store_id="<?php echo $res->store_id; ?>" href="javascript:void(0)" style="color:red;">Delete Store</a>
                                                            @endif
                                                        </div>
                                                    </p>                                                    
                                                </div>
                                            </div>   
                                        </div>
                                    </div>
                                </div>
                                <?php
                                }
                            // }
                            ?>
                            <!-- Product List Ends -->
                            <div class="row">
                                <div class="custom-pagination col-sm-12 mt-15">
                                {{$results->links('pagination::bootstrap-4')}}
                                    <?php
                                    // $q = [];
                                    // if (isset($_GET['search_stores'])) {
                                    //     $q['search_stores'] = $_GET['search_stores'];
                                    // }
                                    // if (isset($_GET['status'])) {
                                    //     $q['status'] = $_GET['status'];
                                    // }
                                    // //print_rr($q);
                                    // echo $results->appends($q)->links('pagination.defaultpagination');
                                    ?>
                                </div>
                            </div>
                            <?php
                        } else {
                            ?>  
                            @include('search.no-record');
                        <?php } ?>
                        <!-- Popup Starts -->


                    </div>
                </div>
            </div>
        </div>
    </div>
    <div id="store-reject-message" class="modal fade" role="dialog">
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
                    <input type="hidden" name="reject_store_id" id="reject_store_id" value="">
                    <input type="hidden" name="reject_user_id" id="reject_user_id" value="">
                    <input type="hidden" name="reject_store_status" id="resject_product_status" value="">

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default btn-primary btn-simple w-100" id="reject_status" style="pointer-events: auto;">Submit and Reject</button>

                </div>
            </div>

        </div>
    </div>
    <div id="loading" class="show-setion">
        <img id="loading-image" src="{{asset('assets/images/Loader-Animation.gif')}}" alt="Loading..." />
    </div>
   
 <!-- Modal content for Transfer -->
<div id="transferToVendorModal" style="display:none; position:fixed; top:20%; left:50%; transform:translateX(-50%); background:#fff; padding:20px 30px; border-radius:12px; box-shadow:0 4px 15px rgba(0,0,0,0.2); border:1px solid #ddd; z-index:1000; width:300px;">
    <form id="vendorTransferForm">
        <h3 style="margin-top:0; text-align:center;">Transfer to Vendor</h3>
        
        <label for="transferAmount" style="font-weight:bold;">Transfer Amount:</label><br>
        <input type="number" id="transferAmount" step="0.01" name="transferAmount" required 
               style="width:100%; padding:8px; margin-top:5px; margin-bottom:15px; border-radius:6px; border:1px solid #ccc;"><br>

        <input type="hidden" id="transferStoreId" name="transferStoreId">

        <div style="display:flex; justify-content:space-between;">
            @if (canAccess('execute-payouts'))
            <button type="submit" id="confirmTransferBtn" style="padding:8px 15px; background:#28a745; color:#fff; border:none; border-radius:6px; cursor:pointer;">Send</button>
            @endif
            <button type="button" id="cancelTransferBtn" style="padding:8px 15px; background:#dc3545; color:#fff; border:none; border-radius:6px; cursor:pointer;">Cancel</button>
        </div>
    </form>
    <div id="transferResponseMessage" style="margin-top: 10px; text-align:center; font-size:0.9em;"></div>
</div>


<script>
$(document).ready(function() {

    $('.initiate-transfer-btn').on('click', function() {
        const storeId = $(this).data('store_id');

        $.ajax({
            url: `/get-transfer-Amount?store_id=${storeId}`,
            method: 'GET',
            
            success: function(response) {
                if (response.status) {
                    const transferAmount = response.data;

                    $('#transferAmount').val(transferAmount);
                    $('#transferStoreId').val(storeId);
                    $('#transferNote').val('');
                    $('#transferAmount').attr('placeholder', `Amount: ${transferAmount} Points`);

                    $('#transferToVendorModal').show();
                } else {
                    $('#transferResponseMessage').html('<span style="color:red;">Failed to fetch transfer amount.</span>');
                }
            },
            error: function() {
                $('#transferResponseMessage').html('<span style="color:red;">Error calling API.</span>');
            }
        });
    });

    $('#vendorTransferForm').on('submit', function(e) {
        e.preventDefault();

       let transferAmount = $('#transferAmount').val(); 
        const storeId =$('#transferStoreId').val();
        transferAmount = parseFloat(transferAmount);
       if (isNaN(transferAmount) || transferAmount <= 0) {
    $('#transferResponseMessage').html('<span style="color:red;">Please enter a valid amount greater than zero (e.g., 1.23).</span>');
    return;
}
        // Disable send button
        $('#confirmTransferBtn').prop('disabled', true).text('Sending...');

        $.ajax({
            url: `/transfer-Amount?store_id=${storeId}&amount=${transferAmount}`,
            method: 'POST',
         
            success: function(response) {
                if (response.status) {
                    $('#transferResponseMessage').html(`<span style="color:green;">${response.message} (Transferred: ${response.data})</span>`);

                    setTimeout(() => {
                        $('#transferToVendorModal').hide();
                        $('#transferResponseMessage').empty();
                    }, 1500);
                } else {
                    $('#transferResponseMessage').html('<span style="color:red;">Transfer failed: Unexpected response.</span>');
                }
            },
            error: function(xhr) {
                let errorMsg = 'An error occurred.';
                if (xhr.status === 404) {
                    errorMsg = 'No Orders of this Store';
                } else if (xhr.status === 500) {
                    errorMsg = 'Server error (500).';
                }
                $('#transferResponseMessage').html(`<span style="color:red;">${errorMsg}</span>`);
            },
            complete: function() {
                $('#confirmTransferBtn').prop('disabled', false).text('Send');
            }
        });
    });

    $('#cancelTransferBtn').on('click', function() {
        $('#transferToVendorModal').hide();
        $('#transferResponseMessage').empty();
    });
});

</script>
<!--Edit seller Payment Percenatge Modal--->
<!-- Modal -->
<!-- Modal -->
<div id="paymentTransferModal" class="modal" style="display:none; position: fixed; z-index: 9999; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.6);">
    <div class="modal-content" style="max-width: 500px; background: #fff; margin: 80px auto; padding: 30px; border-radius: 12px; box-shadow: 0 20px 40px rgba(0,0,0,0.2); font-family: 'Segoe UI', sans-serif;">
        <h2 id="modalTitle" style="margin-bottom: 20px; font-size: 24px; color: #333;">Store Deduction</h2>

        <form id="storeDeductionForm">
            <input type="hidden" name="store_id" id="store_id">

            <div style="margin-bottom: 16px;">
                <label for="mall_shark_amount" style="display:block; margin-bottom: 6px; font-weight: 500;">MallShark Amount <span style="color:red;">*</span></label>
                <input type="number" step="0.01" name="mall_shark_amount" id="mall_shark_amount" required
                       style="width: 100%; padding: 10px 12px; border: 1px solid #ccc; border-radius: 6px;">
                <div class="error mall_shark_amount_error" style="color:red; font-size: 13px;"></div>
            </div>

            <div style="margin-bottom: 16px;">
                <label for="shipping_check" style="display:block; margin-bottom: 6px; font-weight: 500;">Shipping Check <span style="color:red;">*</span></label>
                <select name="shipping_check" id="shipping_check" required
                        style="width: 100%; padding: 10px 12px; border: 1px solid #ccc; border-radius: 6px;">
                    <option value="1">Yes</option>
                    <option value="0">No</option>
                </select>
                <div class="error shipping_check_error" style="color:red; font-size: 13px;"></div>
            </div>

            <div style="margin-bottom: 16px; display: none;">
                <label for="ads_check" style="display:block; margin-bottom: 6px; font-weight: 500;">Ads Check <span style="color:red;">*</span></label>
                <select name="ads_check" id="ads_check" required
                        style="width: 100%; padding: 10px 12px; border: 1px solid #ccc; border-radius: 6px;">
                    <option value="1">Yes</option>
                    <option value="0">No</option>
                </select>
                <div class="error ads_check_error" style="color:red; font-size: 13px;"></div>
            </div>


            <div style="margin-bottom: 16px; display:none;">
                <input type="number" step="0.01" name="ad_amount" id="ad_amount" value="0">
                <div class="error ad_amount_error" style="color:red; font-size: 13px;"></div>
            </div>

            <div style="margin-top: 25px; display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" id="cancelBtn" style="padding: 10px 18px; background: #f44336; border: none; color: white; border-radius: 6px; cursor: pointer;">Cancel</button>
                <button type="submit" id="saveBtn" style="padding: 10px 18px; background: #4CAF50; border: none; color: white; border-radius: 6px; cursor: pointer;">Save</button>
            </div>

            <div id="formMessage" style="margin-top:15px; font-size: 14px;"></div>
        </form>
    </div>
</div>


<script>
$(document).ready(function () {
    // Open Modal
    $('.initiate-payment-transfer-btn').click(function () {
        const storeId = $(this).data('store_id');
        resetForm();

        $('#store_id').val(storeId);
        $('#modalTitle').text('Store Deduction - Loading...');
        $('#paymentTransferModal').fadeIn();

        $.ajax({
            url: `/store-deduction/${storeId}`,
            type: 'GET',
            success: function (response) {
                const data = response.data;
                $('#modalTitle').text('Update Store Deduction');
                $('#mall_shark_amount').val(data.mall_shark_amount);
                $('#shipping_check').val(data.shipping_check ? 1 : 0);
                $('#ads_check').val(data.ads_check ? 1 : 0);
                $('#ad_amount').val(data.ad_amount ?? 0);
            },
            error: function (xhr) {
                if (xhr.status === 404) {
                    $('#modalTitle').text('Create Store Deduction');
                    $('#shipping_check').val(0); // Default shipping to 0
                } else {
                    $('#paymentTransferModal').fadeOut();
                    Swal.fire({
                        title: 'Error',
                        text: 'Unexpected error occurred.',
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                }
            }
        });
    });

    // Cancel Button
    $('#cancelBtn').click(function () {
        $('#paymentTransferModal').fadeOut();
    });

    // Save Form
    $('#storeDeductionForm').submit(function (e) {
        e.preventDefault();

        const mallSharkAmount = $('#mall_shark_amount').val();

        clearErrors();

        // Validate percentage and decimal places
        if (parseFloat(mallSharkAmount) > 100) {
            $('.mall_shark_amount_error').text("MallShark amount cannot exceed 100%");
            return;
        }
        if (!/^\d+(\.\d{1,2})?$/.test(mallSharkAmount)) {
            $('.mall_shark_amount_error').text("Maximum two decimal places allowed.");
            return;
        }

        const formData = {
            store_id: $('#store_id').val(),
            mall_shark_amount: mallSharkAmount,
            shipping_check: $('#shipping_check').val(),
            ads_check:0,// $('#ads_check').val(),
            ad_amount: 0 // Always send 0
        };

        // Disable Save button
        $('#saveBtn').prop('disabled', true).text('Saving...');

        $.ajax({
            url: `/store-deduction`,
            type: 'POST',
            data: formData,
            success: function (response) {
                Swal.fire({
                    title: 'Success',
                    text: 'Saved successfully!',
                    icon: 'success',
                    confirmButtonText: 'OK'
                });
                $('#paymentTransferModal').fadeOut();
            },
            error: function (xhr) {
                if (xhr.status === 422) {
                    const errors = xhr.responseJSON.errors;
                    for (const field in errors) {
                        $(`.${field}_error`).text(errors[field][0]);
                    }
                } else {
                    Swal.fire({
                        title: 'Error',
                        text: 'Something went wrong. Please try again.',
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                }
            },
            complete: function () {
                // Re-enable Save button
                $('#saveBtn').prop('disabled', false).text('Save');
            }
        });
    });

    function resetForm() {
        $('#storeDeductionForm')[0].reset();
        $('#ad_amount').val(0); // Set hidden ad_amount to 0
        clearErrors();
        $('#formMessage').html('');
    }

    function clearErrors() {
        $('.error').text('');
    }
});
</script>

<!--Pay Seller Modal--->

<div id="transferModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; 
    background-color:rgba(0,0,0,0.5); z-index:9999; overflow:auto; padding:30px;">
    <div style="background:#fff; max-width:900px; margin:auto; padding:30px; border-radius:12px; box-shadow:0 4px 12px rgba(0,0,0,0.2); font-family:sans-serif;">
        <h2 style="margin-bottom:20px; border-bottom:1px solid #ddd; padding-bottom:10px;">Transfer Orders</h2>

        <label style="font-weight:bold; display:block; margin-bottom:5px;">Total Payable:</label>
        <input type="number" id="totalPayableInput" step="0.01" style="width:100%; padding:8px; margin-bottom:20px; border:1px solid #ccc; border-radius:6px;">

        <div style="overflow-x:auto;">
            <table id="ordersTable" style="width:100%; border-collapse:collapse; font-size:14px;">
                <thead>
                    <tr style="background-color:#f5f5f5;">
                        <th style="padding:12px; border-bottom:2px solid #ddd; text-align:left;"><input type="checkbox" id="selectAllOrders"></th>
                        <th style="padding:12px; border-bottom:2px solid #ddd; text-align:left;">Order ID</th>
                        <th style="padding:12px; border-bottom:2px solid #ddd; text-align:left;">Total</th>
                        <th style="padding:12px; border-bottom:2px solid #ddd; text-align:left;">Payable</th>
                        <th style="padding:12px; border-bottom:2px solid #ddd; text-align:left;">Status</th>
                        <th style="padding:12px; border-bottom:2px solid #ddd; text-align:left;">Date</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Populated by JS -->
                </tbody>
            </table>
        </div>

        <div style="margin-top:20px; text-align:right;">
            <button id="sendTransferBtn" style="background-color:#007bff; color:#fff; padding:10px 20px; border:none; border-radius:6px; cursor:pointer; margin-right:10px;">Send</button>
            <button onclick="$('#transferModal').hide()" style="background-color:#6c757d; color:#fff; padding:10px 20px; border:none; border-radius:6px; cursor:pointer;">Cancel</button>
        </div>

        <div id="responseMessage" style="margin-top:15px; color:green; font-weight:bold;"></div>
    </div>
</div>

<!--Pay Seller javascript--->
<script>
$(document).ready(function() {
    $('.initiate-order-transfer-btn').click(function() {
        let storeId = $(this).data('store_id');

       $.get('/get-orders-transfer-Amount?store_id=' + storeId, function(response) {
    if (response.status && response.data) {
        // Initialize totalPayable as 0
        $('#totalPayableInput').val(0);

        // Populate orders table
        let rows = '';
        response.data.processedOrders.forEach(order => {
            rows += `
                <tr>
                    <td><input type="checkbox" class="orderCheckbox" value="${order.order_id}"></td>
                    <td>${order.order_id}</td>
                    <td>${order.total_amount}</td>
                    <td>${order.payable_amount}</td>
                    <td>${order.order_status}</td>
                    <td>${formatDate(order.created_at)}</td>
                </tr>`;
        });
        $('#ordersTable tbody').html(rows);
        $('#transferModal').show().data('store-id', storeId);
        $('#responseMessage').text('');
    } else {
        alert('Failed to fetch data: ' + (response.message || 'Unknown error'));
    }
}).fail(function(jqXHR) {
    // Handle HTTP errors (like 404, 500)
    let message = 'Something went wrong';
    if (jqXHR.status === 404) {
        message = 'No Orders Found for this Store';
    } else if (jqXHR.status === 500) {
        message = 'Server error (500)';
    }else if(jqXHR.responseJSON && jqXHR.responseJSON.message) {
        message = jqXHR.responseJSON.message;
    } 
  Swal.fire({
        title: 'No Orders Found',
        text: message,
        icon: 'error',
        confirmButtonText: 'OK'
    });
   
});

    });
function formatDate(isoString) {
    const date = new Date(isoString);
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
}
    // Function to update total payable based on checked orders
    function updateTotalPayable() {
        let total = 0;
        $('.orderCheckbox:checked').each(function() {
            let payableText = $(this).closest('tr').find('td:nth-child(4)').text();
            let payable = parseFloat(payableText);
            if (!isNaN(payable)) {
                total += payable;
            }
        });
        $('#totalPayableInput').val(total.toFixed(2));
    }

    // Select all checkbox
    $('#selectAllOrders').on('change', function() {
        $('.orderCheckbox').prop('checked', this.checked);
        updateTotalPayable();
    });

    // Individual checkbox change event
    $(document).on('change', '.orderCheckbox', function() {
        updateTotalPayable();
    });

    // Send button
    $('#sendTransferBtn').click(function() {
        let storeId = $('#transferModal').data('store-id');
        let totalPayable = $('#totalPayableInput').val();
       if (totalPayable.includes('.')) {
    let decimals = totalPayable.split('.')[1];
    if (decimals.length > 2) {
        alert('Please enter a value with up to 2 decimal places only.');
        $('#totalPayableInput').focus();
         return;
    } 
        } 
        // Get selected order IDs
        let orderIds = $('.orderCheckbox:checked').map(function() {
            return $(this).val();
        }).get();

        if (orderIds.length === 0) {
            alert('Please select at least one order.');
            return;
        }
            $('#sendTransferBtn').prop('disabled', true).text('Sending...');
        $.ajax({
            url: '/order-transfer-Amount',
            method: 'POST',
            data: {
                store_id: storeId,
                amount: totalPayable,
                orderIds: orderIds
            },
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            success: function(res) {
              $('#transferModal').hide();
              Swal.fire({
                title: '✅ Success',
                text: 'Payment sent to the seller successfully.',
                icon: 'success',
                confirmButtonText: 'OK'
            });
            },
            error: function(err) {
                let msg = err.responseJSON?.message || 'Something went wrong';
                $('#responseMessage').css('color', 'red').text(msg);
            }
        }).always(function() {
            $('#sendTransferBtn').prop('disabled', false).text('Send');
        });
    });
});
</script>


<script>
   $(document).ready(function () {
        $(".copyButton").click(function () {
            var content = $(this).siblings('span.ml-2'); 

            if (content.length) {
                var range = document.createRange();
                try {
                    range.selectNodeContents(content[0]);  
                    window.getSelection().removeAllRanges();
                    window.getSelection().addRange(range);

                    document.execCommand("copy");
                    $(this).html('<i class="fa fa-check"></i>');

                    setTimeout(() => {
                        $(this).html('<i class="fa fa-clipboard"></i>');
                    }, 2000);

                    window.getSelection().removeAllRanges();
                } catch (err) {
                    console.error('Error copying text: ', err);
                }
            } else {
                console.error('Content not found to copy.');
            }
        });
    });
    $('#loading').hide();
    $(document).on("click", ".enable-store", function () {
        var res = {};
        var storeStatus = $(this).attr("data-store_status");
        var store_id = $(this).attr("data-store_id");
        var user_id = $(this).attr("data-user_id");

        res["storeStatus"] = storeStatus;
        res["store_id"] = store_id;
        res["user_id"] = user_id;

        Swal.fire({
            title: "Are you sure?",
            text: "Are you sure you want to enable this store?",
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Yes, Do it!",
            cancelButtonText: "Cancel",
            customClass: {
                confirmButton: "btn btn-success",
                cancelButton: "btn btn-danger"
            },
            buttonsStyling: false
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.close();
                $('#loading').show();

                $.ajax({
                    type: "POST",
                    url: "/admin/enableStore",
                    data: res,
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function (response) {
                        $('#loading').hide();
                        if (response.status) {
                            $(".active-store").addClass("d-none");
                            $(".deactivate-store").attr("data-store_status", 2);
                            $(".deactive-store").removeClass("d-none");

                            toastr.options = {
                                closeButton: true,
                                tapToDismiss: true,
                                positionClass: "toast-top-right",
                                toastClass: "toast-icon custom-toast-success",
                                progressBar: true,
                            };
                            toastr.success(response.message);

                            setTimeout(() => {
                                location.reload();
                            }, 1000);
                        } else {
                            toastr.options = {
                                closeButton: true,
                                tapToDismiss: true,
                                positionClass: "toast-top-right",
                                toastClass: "toast-icon custom-toast-success",
                                progressBar: true,
                            };
                            toastr.error(response.message);
                        }
                    },
                    error: function (xhr, textStatus, errorThrown) {
                        $('#loading').hide();
                        console.error("AJAX Error:", errorThrown);
                    }
                });
            }
        });
    });

    $(document).on("click", ".reset-ai-token", function () {
        var res = {};
        var store_id = $(this).attr("data-store_id");
        res["store_id"] = store_id;

        Swal.fire({
            title: "Are you sure?",
            text: "Are you sure you want to reset Ai tokens?",
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Yes, Do it!",
            cancelButtonText: "Cancel",
            customClass: {
                confirmButton: "btn btn-success",
                cancelButton: "btn btn-danger"
            },
            buttonsStyling: false
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.close();
                $('#loading').show();

                $.ajax({
                    type: "POST",
                    url: "/api/stores/reset-ai-tokens",
                    data: res,
                    success: function (response) {
                        $('#loading').hide();
                        if (response.status) {;
                            toastr.options = {
                                closeButton: true,
                                tapToDismiss: true,
                                positionClass: "toast-top-right",
                                toastClass: "toast-icon custom-toast-success",
                                progressBar: true,
                            };
                            toastr.success(response.message);
                        } else {
                            toastr.options = {
                                closeButton: true,
                                tapToDismiss: true,
                                positionClass: "toast-top-right",
                                toastClass: "toast-icon custom-toast-success",
                                progressBar: true,
                            };
                            toastr.error(response.message);
                        }
                    },
                    error: function (xhr, textStatus, errorThrown) {
                        $('#loading').hide();
                        toastr.options = {
                                closeButton: true,
                                tapToDismiss: true,
                                positionClass: "toast-top-right",
                                toastClass: "toast-icon custom-toast-success",
                                progressBar: true,
                            };
                            toastr.error('Ai feature is not enabled');
                    }
                });
            }
        });
    });

    $(document).on("click", ".delete-store", function () {
        var store_id = $(this).attr("data-store_id");

        Swal.fire({
            title: "Are you sure?",
            text: "This will permanently delete the store and all its products that have no orders. This action cannot be undone.",
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Yes, Delete Store",
            cancelButtonText: "Cancel",
            customClass: {
                confirmButton: "btn btn-danger",
                cancelButton: "btn btn-secondary"
            },
            buttonsStyling: false
        }).then((result) => {
            if (result.isConfirmed) {
                $('#loading').show();

                $.ajax({
                    type: "DELETE",
                    url: "/admin/delete-store/" + store_id,
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function (response) {
                        $('#loading').hide();
                        if (response.status) {
                            Swal.fire({
                                title: 'Deleted!',
                                text: response.message,
                                icon: 'success',
                                confirmButtonText: 'OK'
                            }).then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire({
                                title: 'Error',
                                text: response.message,
                                icon: 'error',
                                confirmButtonText: 'OK'
                            });
                        }
                    },
                    error: function (xhr) {
                        $('#loading').hide();
                        var message = 'Something went wrong. Please try again.';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            message = xhr.responseJSON.message;
                        }
                        Swal.fire({
                            title: 'Error',
                            text: message,
                            icon: 'error',
                            confirmButtonText: 'OK'
                        });
                    }
                });
            }
        });
    });

    $(document).on("click", ".activate-store", function () {
        const stripe = $(this).attr("data-stripe");
        const is_completed = $(this).attr("data-iscomplete");

        // Error for Stripe disconnected
        // if (stripe == 0) {
        //     toastr.options = {
        //         closeButton: true,
        //         tapToDismiss: true,
        //         positionClass: "toast-top-right",
        //         toastClass: "toast-icon custom-toast-success",
        //         progressBar: true,
        //     };
        //     toastr.error("The store's Stripe account has been diNsconnected");
        //     return;
        // }

        // Error for incomplete profile
        // if (is_completed == 0) {
        //     toastr.options = {
        //         closeButton: true,
        //         tapToDismiss: true,
        //         positionClass: "toast-top-right",
        //         toastClass: "toast-icon custom-toast-success",
        //         progressBar: true,
        //     };
        //     toastr.error("This store profile is not completed");
        //     return;
        // }

        const res = {
            storeStatus: $(this).attr("data-store_status"),
            store_id: $(this).attr("data-store_id"),
            user_id: $(this).attr("data-user_id"),
        };

        Swal.fire({
            title: "Are you sure?",
            text: "Are you sure you want to activate this store?",
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Yes, Do it!",
            cancelButtonText: "Cancel",
            customClass: {
                confirmButton: "btn btn-success",
                cancelButton: "btn btn-danger"
            },
            buttonsStyling: false
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.close();
                $('#loading').show();

                $.ajax({
                    type: "POST",
                    url: "/admin/activateStore",
                    data: res,
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function (response) {
                        $('#loading').hide();

                        toastr.options = {
                            closeButton: true,
                            tapToDismiss: true,
                            positionClass: "toast-top-right",
                            toastClass: "toast-icon custom-toast-success",
                            progressBar: true,
                        };

                        if (response.status) {
                            $(".active-store").addClass("d-none");
                            $(".deactivate-store").attr("data-store_status", 2);
                            $(".deactive-store").removeClass("d-none");

                            toastr.success(response.message);
                            setTimeout(() => {
                                location.reload();
                            }, 1000);
                        } else {
                            toastr.error(response.message);
                        }
                    },
                    error: function (xhr, textStatus, errorThrown) {
                        $('#loading').hide();
                        console.error("AJAX Error:", errorThrown);
                    }
                });
            }
        });
    });


    $(document).on("click", ".disable-store", function () {
        var res = {};

        var storeStatus = $(this).attr("data-store_status");
        var store_id = $(this).attr("data-store_id");
        var user_id = $(this).attr("data-user_id");

        res["storeStatus"] = storeStatus;
        res["store_id"] = store_id;
        res["user_id"] = user_id;

        Swal.fire({
            title: "Are you sure?",
            text: "Are you sure you want to disable this store? This store will be disabled and all products will not be available for sale.",
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Yes, Do it!",
            cancelButtonText: "Cancel",
            customClass: {
                confirmButton: "btn btn-success",
                cancelButton: "btn btn-danger"
            },
            buttonsStyling: false
        }).then((result) => {
            if (result.isConfirmed) {
                swal.close();
                $('#loading').show();

                $.ajax({
                    type: "POST",
                    url: "/admin/disableStore",
                    data: res,
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function (response) {
                        $('#loading').hide();
                        if (response.status) {
                            $(".active-store").removeClass("d-none");
                            $(".activate-store").attr("data-store_status", 0);
                            $(".deactive-store").addClass("d-none");

                            toastr.options = {
                                closeButton: true,
                                tapToDismiss: true,
                                positionClass: "toast-top-right",
                                toastClass: "toast-icon custom-toast-success",
                                progressBar: true,
                            };
                            toastr.success(response.message);

                            setTimeout(() => {
                                location.reload();
                            }, 1000);
                        } else {
                            toastr.options = {
                                closeButton: true,
                                tapToDismiss: true,
                                positionClass: "toast-top-right",
                                toastClass: "toast-icon custom-toast-success",
                                progressBar: true,
                            };
                            toastr.error(response.message);
                        }
                    },
                    error: function (xhr, textStatus, errorThrown) {
                        console.log("Error: " + errorThrown);
                    }
                });
            }
        });
    });


    $(document).on("click", ".deactivate-store", function () {
        var res = {};

        var storeStatus = $(this).attr("data-store_status");
        var store_id = $(this).attr("data-store_id");
        var user_id = $(this).attr("data-user_id");

        res["storeStatus"] = storeStatus;
        res["store_id"] = store_id;
        res["user_id"] = user_id;

        Swal.fire({
            title: "Are you sure?",
            text: "Are you sure you want to deactivate your account? This store will be deactivated and all product listings will be removed.",
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Yes, Do it!",
            cancelButtonText: "Cancel",
            customClass: {
                confirmButton: "btn btn-success",
                cancelButton: "btn btn-danger"
            },
            buttonsStyling: false
        }).then((result) => {
            if (result.isConfirmed) {
                $('#loading').show();
                $.ajax({
                    type: "POST",
                    url: "/admin/deactivateStore",
                    data: res,
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function (response) {
                        $('#loading').hide();
                        if (response.status) {
                            $(".active-store").removeClass("d-none");
                            $(".activate-store").attr("data-store_status", 0);
                            $(".deactive-store").addClass("d-none");

                            toastr.options = {
                                closeButton: true,
                                tapToDismiss: true,
                                positionClass: "toast-top-right",
                                toastClass: "toast-icon custom-toast-success",
                                progressBar: true,
                            };
                            toastr.success(response.message);

                            setTimeout(() => {
                                location.reload();
                            }, 1000);
                        } else {
                            toastr.error(response.message);
                        }
                    },
                    error: function (xhr, textStatus, errorThrown) {
                        console.log("Error: " + errorThrown);
                    }
                });
            }
        });
    });


    $(document).on("click", ".approved-store", function() {
        var res = {}
        var storeStatus = $(this).attr('data-store_status');
        var store_id = $(this).attr('data-store_id');
        var user_id = $(this).attr('data-user_id');
        
        res['storeStatus'] = storeStatus;
        res['store_id'] = store_id;
        res['user_id'] = user_id;
        //var result = confirm("Are you sure to change Store Status");
        Swal.fire({
            title: "Are you sure?",
            text: "Are you sure you want to activate this store?",
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Yes, Do it!",
            cancelButtonText: "Cancel",
            customClass: {
                confirmButton: "btn btn-success",
                cancelButton: "btn btn-danger"
            },
            buttonsStyling: false
        }).then((result) => {
            if (result.isConfirmed) {
                $('#loading').show();
                $.ajax({
                    type: 'POST',
                    url: '/admin/approvedStore',
                    data: res,
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function (response) {
                        $('#loading').hide();

                        if (response.status) {
                            $(".active-store").removeClass("d-none");
                            $(".activate-store").attr("data-store_status", 0);
                            $(".deactive-store, .approved-store, .rejectstore, .pending-store").addClass("d-none");

                            toastr.options = {
                                closeButton: true,
                                tapToDismiss: true,
                                positionClass: 'toast-top-right',
                                toastClass: 'toast-icon custom-toast-success',
                                progressBar: true,
                            };
                            toastr.success(response.message);

                            setTimeout(() => {
                                location.reload();
                            }, 1000);
                        } else {
                            toastr.error(response.message);
                        }
                    },
                    error: function (xhr, textStatus, errorThrown) {
                        console.log("Error: " + errorThrown);
                    }
                });
            }
        });
    });
    var status;
        $(document).on('click', '.rejectstore', function () {
            var storeId = $(this).attr('data-store_id');
            var userId = $(this).attr('data-user_id');
             status = $(this).attr('data-reject-status');
            $('#reject_store_id').val(storeId);
            $('#reject_user_id').val(userId);
             $('#reject_store_status').val(status);

        });
    $(document).on('click', '#reject_status', function () {

        if ($('#reject_reason').val() != "") {
            $(".error").html("");
            var data = {};
            data['store_id'] = $('#reject_store_id').val();
            data['user_id'] = $('#reject_user_id').val();
            data['status'] = status;
            data['reason'] = $('#reject_reason').val();
            $('#store-reject-message').modal('hide');
            $('#loading').show();
            $.ajax({
                    type: 'POST',
                    url: '/admin/rejectStore',
                    data: data,
                    success: function(response) {
                        if(response.status){
                            
                            setTimeout(function () {
                        toastr.options = {
                        closeButton: true,
                        tapToDismiss: true,
                        positionClass: 'toast-top-right',
                        toastClass: 'toast-icon custom-toast-success',
                        progressBar: true,
                        };
                        toastr.success(response.message);
                        location.reload();
                            }, 1000);

                      
                        $(".pending-store").addClass("d-none");
                   
                        
                       
                        }else{
                            $('#loading').hide();
                        toastr.options = {
                        closeButton: true,
                        tapToDismiss: true,
                        positionClass: 'toast-top-right',
                        toastClass: 'toast-icon custom-toast-success',
                        progressBar: true,
                        };
                        toastr.error(response.message);
                        }
            
                     

                    },
                    error: function(xhr, textStatus, errorThrown) {
                        console.log('Error: ' + errorThrown); // handle any errors that occur
                    }
                });
            // sendreq(data, 'admin', 'product-status', 'callbackReject');
        } else {
            $(".error").html("Reason of reject is required.");
        }
    });

 
    $('.active-store').each(function() {
        var storeStatus = $(this).find('a').data('store_status');
        
        if (storeStatus == 0) {
            $(this).removeClass('d-none');
        } 
    });

    $('.deactive-store').each(function() {
        var storeStatus = $(this).find('a').data('store_status');
        
        if (storeStatus == 2) {
            $(this).removeClass('d-none');
        } 
    });
    </script>
    <script>
        
        document.querySelectorAll('.shopify-connected-checkbox').forEach(function(checkbox) {
            checkbox.addEventListener('change', function() {
                const storeId = this.getAttribute('data-store-id');
                const isSelected = this.checked ? 1 : 0;

                const messageSpan = this.closest('.rght-prf').querySelector('.selected-success');

                    fetch(`/api/stores/${storeId}/shopify-connected`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({
                            is_shopify_allowed: isSelected
                        })
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.status) {
                            messageSpan.textContent = data.is_shopify_allowed ? 'Connected' : 'Disconnected';
                            messageSpan.style.color = data.is_shopify_allowed ? 'green' : 'red';
                        } else {
                            // Rollback checkbox state if API failed
                            checkbox.checked = !checkbox.checked;
                            messageSpan.textContent = 'Update failed';
                            messageSpan.style.color = 'red';
                        }

                        setTimeout(() => {
                            messageSpan.textContent = '';
                        }, 3000);
                    })
                    .catch(error => {
                        console.error('Update failed for store ' + storeId, error);

                        // Rollback checkbox state if error
                        checkbox.checked = !checkbox.checked;

                        messageSpan.textContent = 'Error';
                        messageSpan.style.color = 'red';
                        setTimeout(() => {
                            messageSpan.textContent = '';
                        }, 3000);
                    });
                });
        });
        document.querySelectorAll('.mallshark-ai-checkbox').forEach(function (checkbox) {
            checkbox.addEventListener('change', function () {
            
                const storeId = this.getAttribute('data-store-id');
                const isSelected = this.checked ? 1 : 0;
                if(isSelected){
                    var swalMessage = 'enable';
                }else{
                    var swalMessage = 'disable';
                }
                const messageSpan = this.closest('.rght-prf').querySelector('.selected-success');
                Swal.fire({
                title: "Are you sure?",
                text: "Are you sure you want to " + swalMessage+ " Ai?",
                icon: "warning",
                showCancelButton: true,
                confirmButtonText: "Yes, Do it!",
                cancelButtonText: "Cancel",
                customClass: {
                    confirmButton: "btn btn-success",
                    cancelButton: "btn btn-danger"
                },
                buttonsStyling: false
            }).then((result) => {
            if (result.isConfirmed) {
                fetch(`/api/stores/${storeId}/enable/ai`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        ai_enabled: isSelected
                    })
                })
                .then(response => response.json())
                .then(data => {
                    // Update message
                    console.log(data);
                    messageSpan.textContent = data.ai_enabled ? 'Enabled' : 'Disabled';
                    messageSpan.style.color = data.ai_enabled ? 'green' : 'red';

                    // Optional: Reset message after 3 seconds
                    setTimeout(() => {
                        messageSpan.textContent = '';
                    }, 3000);
                })
                .catch(error => {
                    console.error('Failed to update AI setting:', error);
                    messageSpan.textContent = 'Error';
                    messageSpan.style.color = 'red';

                    setTimeout(() => {
                        messageSpan.textContent = '';
                    }, 3000);
                });
                 }
            });
            });
        });
        
        document.querySelectorAll('.wholesale-connected-checkbox').forEach(function(checkbox) {
            checkbox.addEventListener('change', function() {
                const storeId = this.getAttribute('data-store-id');
                const isSelected = this.checked ? 1 : 0;

                const messageSpan = this.closest('.rght-prf').querySelector('.selected-success');

                fetch(`/api/stores/${storeId}/wholesale-connected`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({
                            is_wholesale_allowed: isSelected
                        })
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.status) {
                            messageSpan.textContent = data.is_wholesale_allowed ? 'Allowed' : 'Disconnected';
                            messageSpan.style.color = data.is_wholesale_allowed ? 'green' : 'red';
                        } else {
                            // Rollback checkbox state if API failed
                            checkbox.checked = !checkbox.checked;
                            messageSpan.textContent = 'Update failed';
                            messageSpan.style.color = 'red';
                        }

                        setTimeout(() => {
                            messageSpan.textContent = '';
                        }, 3000);
                    })
                    .catch(error => {
                        console.error('Update failed for store ' + storeId, error);

                        // Rollback checkbox state if error
                        checkbox.checked = !checkbox.checked;

                        messageSpan.textContent = 'Error';
                        messageSpan.style.color = 'red';
                        setTimeout(() => {
                            messageSpan.textContent = '';
                        }, 3000);
                    });
                });
        });
         document.querySelectorAll('.unique-product-selling-checkbox').forEach(function(checkbox) {
            checkbox.addEventListener('change', function() {
                const storeId = this.getAttribute('data-store-id');
                const isSelected = this.checked ? 1 : 0;

                const messageSpan = this.closest('.rght-prf').querySelector('.selected-success');

                fetch(`/api/stores/${storeId}/unique-product-selling`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({
                            unique_product_selling: isSelected
                        })
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.status) {
                            messageSpan.textContent = data.unique_product_selling ? 'Allowed' : 'Disconnected';
                            messageSpan.style.color = data.unique_product_selling ? 'green' : 'red';
                        } else {
                            // Rollback checkbox state if API failed
                            checkbox.checked = !checkbox.checked;
                            messageSpan.textContent = 'Update failed';
                            messageSpan.style.color = 'red';
                        }

                        setTimeout(() => {
                            messageSpan.textContent = '';
                        }, 3000);
                    })
                    .catch(error => {
                        console.error('Update failed for store ' + storeId, error);

                        // Rollback checkbox state if error
                        checkbox.checked = !checkbox.checked;

                        messageSpan.textContent = 'Error';
                        messageSpan.style.color = 'red';
                        setTimeout(() => {
                            messageSpan.textContent = '';
                        }, 3000);
                    });
                });
        });
    </script>
</main>
<!-- Content Ends-->
@endsection