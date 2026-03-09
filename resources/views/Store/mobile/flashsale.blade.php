@extends('layouts.mobile-main')
<!-- Main Banner Starts -->
@section('content')
@section('title', 'User info')
<main>
    @include('Store.sidebar')
    <!-- Dashboard Ends -->
    <div class="dshbrdin dashprdlst storeproducts-page flash-sale-container">
        <div class="container">
            <div class="row">
                <div class="col-md-12"> 
                    <div class="dashprdlst sss">
                        <!-- Product Sorting Starts -->
                        <!--<div class="row srtbx">
                            <div class="col-md-12">
                                <div class="d-flex justify-content-between">
                                    <div class="heading-pr">
                                        <h3 class=" ">Products</h3>
                                    </div>
                                    <div class="add-pr-lnk">
                                        <a class="btn btn-primary btn-simple small-btn-mbt" href="{{URL::to('/add-product')}}">
                                            Add Product
                                        </a>
                                        <a class="btn btn-primary btn-simple small-btn-mbt" href="{{URL::to('/store-dashboard')}}">
                                            Back to Dashboard
                                        </a>                                        
                                    </div>                                    
                                </div>
                            </div> 
                        </div>  --> 
                        @if(Session::has('SuccessMessage'))
                        <div class="alert alert-success alert-success-mbt" id="success_message" role="alert">
                            {{ Session::get('SuccessMessage') }}
                            <script>
                                setTimeout(function () {
                                    jQuery("#success_message").fadeOut('slow')
                                }, 3000);
                            </script>
                        </div>
                        @endif
                        @if(Session::has('errorMessage'))
                        <div class="alert alert-danger" role="alert">{{ Session::get('errorMessage') }}</div>
                        @endif

                        <!-- Product Sorting Ends--> 
                        <!-- Tab sections start -->
                        <ul class=" prd nav nav-pills nav-fill">
                            <li class="nav-item">
                                <a class="nav-link" href="{{URL::to('/store/campaign')}}">Collecting Pledges</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="{{URL::to('/store/oncampaign')}}">Active Campaign</a>
                            </li>
                            <li class="nav-item">
                                <a href="<?php echo URL::to('store/next-campaign'); ?>" class="nav-link active">Next Campaigns</a>
                            </li>
                        </ul>
                        <br>
                        <!-- Popup Starts -->
                        <!-- flash data starts here -->
                        <?php if (!empty($storeCampaigns)) { ?>
                            <div class="col-sm-12" id="camp_progress">                                  
                                <div class="zit-container">
                                    <div class="  text-center" style="font-size:20px">Select your product for next flash sale</div>
                                    <?php if (!empty($storeCampaigns)) { ?>
                                        <div class=" nxtcmp ">
                                            <div class="btn-group btn-group-justified">
                                                <?php
                                                $campId = isset($storeCampaigns[0]['store_campaign_id']) ? $storeCampaigns[0]['store_campaign_id'] : "";
                                                if (isset($_GET['store_campaign']) && !empty($_GET['store_campaign'])) {
                                                    $campId = $_GET['store_campaign'];
                                                }

                                                foreach ($storeCampaigns as $scs) {
                                                    $timestamp = strtotime($scs['camp_start']);
                                                    $date = date('d M Y', $timestamp);
                                                    ?>
                                                    <a href="<?php echo URL::to('store/next-campaign?store_campaign=' . $scs['store_campaign_id']); ?>" class="btn btn-primary <?php
                                                    if ($campId == $scs['store_campaign_id']) {
                                                        echo 'active';
                                                    }
                                                    ?>" style="border-right:none!important"><?php echo $date; ?></a>

                                                <?php } ?>
                                            </div>

                                        </div>
                                    <?php } ?>
                                    <?php
                                    if (!empty($campId)) {

                                        if (Session::has('userData')) {
                                            $userId = Session::get('userData')['user_id'];
                                        }else{
                                            $userId=Request::segment(2);
                                        }
                                        $flashProduct = getFlashSaleProduct($campId,$userId);
                                        if (!empty($flashProduct)) {
                                            if (!empty($flashProduct) && $flashProduct->product_type == 'variant') {
                                                $variableData = getvariableProduct($flashProduct->product_id);
                                            }
                                            $timestamp = strtotime($flashProduct->created_at);
                                            $productdate = date('d M Y', $timestamp);
                                            $camPaignData = getCampaignIdataById($campId);
                                            $campaignStartDate = !empty($camPaignData->camp_start) ? $camPaignData->camp_start : "";
                                            $dateFormate = strtotime($campaignStartDate);
                                            $campStartDate = date('d M Y', $dateFormate);
                                        }
                                    }
                                    ?>
                                    <p class="small text-center" style="margin-bottom:30px"><b>Note:</b> The product below is applicable on the upcoming campaign that starts from <b><?php echo!empty($campStartDate) ? $campStartDate : ""; ?></b> , You can choose another product for this campaign before the pledge target is met.</p>
                                    <!-- Flash Product -->
                                    <?php if (!empty($flashProduct)) { ?>
                                        <div class="dshprdbx border inflash">
                                            <div class="row">     
                                                <div class="col-sm-6 lp-bar">
                                                    <div class="row">
                                                        <div class="col-md-3"> 
                                                            <div class="img-div-mbt">
                                                                <img class="img-responsive" src="<?php echo getAttachmentUrlById((int) $flashProduct->feature_Image, $size = 'medium') ?>" alt="{{$flashProduct->title}}" title="{{$flashProduct->title}}">

                                                            </div>
                                                        </div>   

                                                        <div class=" col-md-9">
                                                            <div class="body-content-mbt">
                                                                <a href="<?php echo URL::to('product/' . $flashProduct->slug); ?>"><h4 class="card-title"><?php echo!empty($flashProduct->title) ? $flashProduct->title : ""; ?></h4></a>
                                                                <p class="small lgt-gry" style="margin-bottom:5px"><?php echo!empty($productdate) ? $productdate : ""; ?></p>
                                                                <p class="card-text" style="margin-bottom:5px;display:none;"></p>
                                                                <p class="inline-block-ele" style="margin-bottom:5px">  
                                                                    <?php
                                                                    if (isset($flashProduct->product_type) && $flashProduct->product_type == 'variant') {
                                                                        $price = $variableData['min_price'];
                                                                        $currentStock = $variableData['current_stock'];
                                                                        $type = "Variable";
                                                                    } else {
                                                                        $price = $flashProduct->price;
                                                                        $currentStock = $flashProduct->current_stock;
                                                                        $type = "Simple";
                                                                    }
                                                                    ?>

                                                                    <span class="zit-price">Price: USDC <?php echo!empty($price) ? $price : ""; ?></span> 
                                                                    <span style="color:grey">&nbsp;&nbsp;|&nbsp;&nbsp;</span> 
                                                                    <?php if ($currentStock > 0) { ?>
                                                                        <span>In stock: <?php echo!empty($currentStock) ? $currentStock : 0; ?></span>
        <?php } else { ?>
                                                                        <span class="error">Out of stock</span>
                                                                    <?php } ?>
                                                                </p></div> 
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-sm-6 lrt-bar  d-flex justify-content-between arrow_box flshinf "> 
                                                    <div class="row">
                                                        <div class="col-md-12 mt-10">Selected for campaign starting from <b><?php echo!empty($campStartDate) ? $campStartDate : ""; ?></b></div> 
                                                        <div class="col-6 text-left"><b></b></div>
        <?php
        $storeProducts = getProductsByStoreId($flashProduct->store_id);
        $totalProdcuts = count($storeProducts);
        ?>
                                                        <?php if ($totalProdcuts > 1) { ?>
                                                            <div class="col-12"><button class="btn btn-primary change-flash-sale">Choose Another Product</button></div>
                                                        <?php } else { ?>
                                                            <div class="col-12"><button class="btn btn-primary flash-alert">Choose Another Product</button></div>
                                                        <?php } ?>
                                                    </div>
                                                </div>

                                            </div> 
                                        </div>
    <?php } ?>
                                    <!-- Flash Product Ends-->
                                </div>
                            </div>
                            <!-- flash data ends here -->
<?php } else { ?>
                            <!--                            <div class="alert alert-danger alert-success-mbt" role="alert">  No record found</div>-->
<?php } ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

<?php
if (!empty($results)) {
    $hidden = isset($_GET['search_products']) ? "" : "hidden";
    ?>
        <div class="container">
            <div class="col-sm-12 search-bar-top hidden">                                
                <div class="d-flex justify-content-between w100p br-top-thin mt-20">
                    <div class="align-self-center">
                        <span class="d-block">Select your Product for flash sale on  <?php echo!empty($dat) ? $dat : ""; ?></span>
                    </div>
                    <form action="{{URL::to('store/next-campaign')}}" method="get" id="product_search_form"> 
                        <div class="bd-highlight">
                            <div class="form-inline-large">
                                <input type="search" class="form-control" id="search_products" name="search_products" value="<?php echo isset($_GET['search_products']) ? $_GET['search_products'] : ''; ?>" placeholder="Search Products" />
                                <button type="submit" class="btn">
                                    <i class="fa fa-search" aria-hidden="true"> </i>
                                </button>                                                
                            </div>
                        </div>
                    </form>
                </div>
            </div>         


            <div class="col-sm-12 container-brall <?php echo $hidden; ?>">
                <!-- Product List --> 
    <?php
    $check = isset($_GET['search_products']) ? $_GET['search_products'] : "";
    $campaignId = !empty($supportCampaigns->store_campaign_id) ? $supportCampaigns->store_campaign_id : "";
    foreach ($results as $res) {

        if (!empty($campaignId)) {
            $flashSale = checkFlashSaleProduct($campaignId, $res->product_id);

            if (!empty($campaignId)) {
                $flashSale = checkFlashSaleProduct($campaignId, $res->product_id);

                if ($flashSale == true && $check == "") {
                    continue;
                }
            }
        }
        if ($res->product_type == 'variant') {
            $variableData = getvariableProduct($res->product_id);
        }
        ?>
                    <div class="dshprdbx border">
                        <div class="row">     
                            <div class="col-sm-6 lp-bar">
                                <div class="row">
                                    <div class="col-md-3"> 
                                        <div class="img-div-mbt">
                                            <img class="img-responsive" src="<?php echo getAttachmentUrlById((int) $res->feature_Image, $size = 'medium') ?>" alt="{{$res->title}}" title="{{$res->title}}">

                                        </div>
                                    </div>   

                                    <div class=" col-md-9">
                                        <div class="body-content-mbt">
                                            <a href="<?php echo URL::to('product/' . $res->slug); ?>"><h4 class="card-title">{{$res->title}}</h4></a>
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
                                                    <span class="bold-element zit-price">Price: USDC {{isset($variableData['min_price'])?$variableData['min_price']:""}}</span> 
                                            <?php } else { ?>
                                                    <span class="bold-element zit-price">Price: USDC {{$res->price}}</span>   
                                                <?php } ?>
                                        </div> 
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-6 lrt-bar  d-flex justify-content-between ">
                                <!-- OVERLAY BOX -->
                                <div class="ovrbx">
                                    <a class="btn btn-primary changeflash" data-camp-id="<?php echo!empty($campId) ? $campId : ""; ?>" data-product-id="<?php echo!empty($res->product_id) ? $res->product_id : ""; ?>">Choose For Flashsale</a>
                                    <p class="small">Once clicked, this product will be selected for flash sale</p>
                                </div>
                                <!-- OVERLAY BOX ENDS -->
                                <div class="rw-tp wt-left-tab align-self-center">
                                    <div class="co-rvnu">
                                        <div class="lft-prf d-flex justify-content-between">
                                            <span>Collectable Revenue:</span>
                                            <span><?php echo!empty($res->user_revenue) ? $res->user_revenue . '%' : ''; ?></span>
                                        </div>
                                        <div class="lft-prf d-flex justify-content-between">
                                            <span>Product Type:</span>
        <?php if ($res->product_type == 'variant') { ?>
                                                <span>
                                                    <a href="javascript:;" class="productvariation" data-product-id="{{$res->product_id}}" data-toggle="modal" >Variable</a>
                                                </span>
                                            <?php } else { ?>
                                                <span>Simple</span>
        <?php } ?>
                                        </div>                                                     

                                    </div>
                                </div> 
                                <div class="rw-tp wt-rgt-tab align-self-center">
                                    <div class="co-rvnu ">
                                        <div class="rght-prf d-flex justify-content-between">
                                            <span>Current Stock:</span>
        <?php if ($res->product_type == 'variant') {
            ?>
            <?php if ($variableData['current_stock'] > 0) { ?>
                                                    <span><?php echo isset($variableData['current_stock']) ? $variableData['current_stock'] : 0; ?></span>
                                                <?php } else { ?>
                                                    <span class="error">Out of stock</span>
                                                <?php } ?>
                                            <?php } else { ?>
                                                <?php if ($res->current_stock > 0) { ?>
                                                    <span><?php echo isset($res->current_stock) ? $res->current_stock : 0; ?></span>
                                                <?php } else { ?>
                                                    <span class="error">Out of stock</span>
                                                <?php } ?>
                                            <?php } ?>
                                        </div> 
                                        <div class="rght-prf d-flex justify-content-between">
                                            <span>Alltime sales:</span>

        <?php if ($res->product_type == 'variant') { ?>
                                                <span><?php echo isset($variableData['total_sales']) ? $variableData['total_sales'] : 0; ?></span>

                                            <?php } else { ?>
                                                <span><?php echo isset($res->sales) ? $res->sales : 0; ?></span>

                                            <?php } ?>
                                        </div>                                                     
                                    </div>
                                </div>
                            </div>
                        </div> 

                        <!-- <div class="row pr-edit-strip">
                             <div class="col-sm-12">
                                 <div class="d-flex justify-content-between w100p">
                                     <div class="edit-delete-box align-self-center">
                                         <p > 
        <?php
        $status_text = 'Deactivate';

        if ($res->status == 0) {
            $status_text = 'Activate';
        }
        ?>
                                             <a href="<?php //echo URL::to('/edit-product') . "/" . $res->product_id;               ?>">Edit</a> <span class="sprtr-lne"> |  </span><a class="alert-message-mbt" href="<?php //echo URL::to('/store-products-delete') . "/" . $res->product_id;               ?>">Delete</a> <span class="sprtr-lne"> |  </span> <a class="product-deactive" href="<?php //echo URL::to('change-status/' . $res->product_id . '/' . $status_text);               ?>"><?php //echo $status_text;               ?></a>                                                           
                                         </p>                                                    
                                     </div>
                        <?php
                        if (isset($_GET['store_campaign']) && $_GET['store_campaign'] != '') {
                            $campaignId = $_GET['store_campaign'];
                        }
                        if (!empty($campaignId)) {
                            $flashSale = checkFlashSaleProduct($campaignId, $res->product_id);
                            ?>
                                                                                      
                                                                                                                         </p>
                                                                                                                     </div>
                        <?php } ?>
                                 </div>   
                             </div>
                         </div> -->

                    </div>

    <?php } ?>
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

                echo $results->appends($q)->links('pagination.defaultpagination');
                ?>
                    </div>
                </div>
            </div>
                    <?php } else { ?>
            <div class="alert alert-danger alert-success-mbt" role="alert">  No record found</div>                     
<?php } ?>
    </div>
    <div id="variable-popup" class="modal fade vaiable-popup-edit" role="dialog">
        <div class="modal-dialog">

            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Product Variable</h4>  
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body" id="showvariation">

                </div>

            </div>

        </div>
    </div>  


    <!-- Modal -->
    <script>
        jQuery(document).on('change', '#store_campaign', function () {
            //var storeCampaign=$(this).attr('data-camp-status');

            jQuery('#vendor-filters').trigger('submit');
        });
        $(document).on('click', '.changeflash', function () {
            var data = {};
            data['product_id'] = $(this).attr('data-product-id');
            data['store_campaign_id'] = $(this).attr('data-camp-id');
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
                swal("Success!", "", "success");
                window.location.reload();

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

        $(function () {
            var url = "<?php echo URL::to('product-search'); ?>";
            $("#search_products").autocomplete({
                source: url,
                minLength: 3,
                select: function () {
                    jQuery('#product_search_form').trigger('submit');
                }
            });
        });
        $(document).on('click', ".change-flash-sale", function () {
            $(".container-brall").removeClass('hidden');
            $(".search-bar-top").removeClass('hidden');
            //return false;
        });
        $(document).on('click', '.flash-alert', function () {
            swal("You only have one product for sale at the moment. Please add another one, to choose it for flash sale.");
        });
    </script>

</main>
<!-- Content Ends-->
@endsection