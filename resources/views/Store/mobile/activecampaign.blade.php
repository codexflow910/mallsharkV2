@extends('layouts.mobile-main')
<!-- Main Banner Starts -->
@section('content')
@section('title', 'Buyer info')
@include('buyer.mobile.sidebar')
<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
<?php
?>
<main>
    @include('Store.sidebar')
    <!-- Dashboard Ends -->
    <div class="dshbrdin dashprdlst storeproducts-page   compaing-page-container">
        <div class="container">
            <div class="row">
                <div class="col-md-12"> 
                    <div class="dashprdlst">
                        <!-- Product Sorting Starts -->
                        <!--<div class="row srtbx">
                            <div class="col-md-12">
                                <div class="d-flex justify-content-between">
                                    <div class="heading-pr"><h3 class=" ">Products</h3></div>
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

                        </div>   -->
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

                        <!-- Product Sorting Ends--> 
                        <!-- Tab sections start -->
                        <ul class=" prd nav nav-pills nav-fill">
                            <li class="nav-item">
                                <a class="nav-link active" href="{{URL::to('/store/campaign')}}">Collecting Pledges</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link " href="{{URL::to('/store/oncampaign')}}">Active Campaign</a>
                            </li>
                            <li class="nav-item">
                                <a href="<?php echo URL::to('store/next-campaign'); ?>" class="nav-link">Next Campaigns</a>
                            </li>
                        </ul>
                        <br>
                        <?php if (!empty($results)) {
                            ?>
                            <div class="row">
                                <div class="col-sm-12 campaign-bar-top">                                

                                </div>
                                <?php
                                $targetPercent = 0;
                                $requiredZit = 0;
                                if (!empty($supportCampaigns)) {
                                    $constants = config('constants.shopzit_gamification');
                                    $flasSaleTarget = $constants['FLASH_SALE_TARGET'];
                                    $collectedAmount = $supportCampaigns->pledges;
                                    $targetPercent = $collectedAmount / $flasSaleTarget * 100;
//                                    if ($targetPercent <100) {
//                                        //$targetPercent = 100;
//                                        $requiredZit = (float) $flasSaleTarget - (float) $collectedAmount;
//                                    }
                                    //echo $css;
                                    ?>

                                    <div class="col-sm-12"  id="camp_progress">                                  
                                        <div class="zit-container">
                                            <div class="zit-bar">
                                                <br>
                                                <div class="progress">
                                                    <div class="progress-bar" style="width:{{round($targetPercent)}}%"></div>
                                                </div>
                                                <div class="d-flex justify-content-between w100p">
                                                    <div class="zero-zit">
                                                        <span>0 USDC</span>
                                                    </div>
                                                    <div class="half-zit">

                                                        <?php
                                                        $collectedAmount = 0;
                                                        if (!empty($supportCampaigns)) {
                                                            $collectedAmount = $supportCampaigns->pledges;
                                                            ?>
                                                        <?php } ?>
                                                        <span class="blue-text"><?php echo!empty($collectedAmount) ? number_format($collectedAmount, 2) : 0; ?> USDC</span>
                                                    </div>
                                                    <div class="end-zit">
                                                        <span>100,000 USDC</span>
                                                    </div>


                                                </div>
                                                <div>
                                                    <?php
                                                    $amount = 0;
                                                    if ($targetPercent > 100) {
                                                        $requiredZit = $collectedAmount;
                                                        $amount = $collectedAmount - $flasSaleTarget
                                                        ?>
                                                        <p class="text-center mt-20">WHOA !....Well done. You have been over funded by <?php echo!empty($amount) ? number_format($amount, 2) : 0; ?> USDC. </p>

                                                        <?php
                                                    } else {
                                                        $requiredZit = (float) $flasSaleTarget - (float) $collectedAmount;
                                                        ?>
                                                        <p class="text-center mt-20"><?php echo!empty($targetPercent) ? round($targetPercent) : 0; ?>% Target Achieved, <?php echo!empty($requiredZit) ? number_format($requiredZit, 2) : 0; ?> USDC Required in</p>
                                                    <?php } ?>
                                                </div>
                                                <div class="row"> 
                                                    <div class="col-sm-12 campaign-bar-top">                                
                                                        <div class="d-flex justify-content-between  w100p ">
                                                            <div class="countdown-hourst">
                    <!--                                            <p class="ends-heading">Campaign Ends In</p>-->
                                                                <div id="count-running-campaignt"></div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                            </div>
                                            <?php
                                            $campEndDate = !empty($supportCampaigns->camp_end) ? $supportCampaigns->camp_end : "";

                                            $date = strtotime($campEndDate);
                                            $dat = date('d M Y', $date);
                                            if ($supportCampaigns) {
                                                if (Session::has('userData')) {
                                                    $userId = Session::get('userData')['user_id'];
                                                } else {
                                                    $userId = Request::segment(2);
                                                }

                                                $flashProduct = getFlashSaleProduct($supportCampaigns->store_campaign_id,$userId);
                                            }
                                            if (!empty($flashProduct) && $flashProduct->product_type == 'variant') {
                                                $variableData = getvariableProduct($flashProduct->product_id);
                                            }
                                            ?>
                                            <div><p class="text-center mt-20" style="font-size:18px;">Campaign starts on <b><?php echo!empty($dat) ? $dat : ""; ?></b> if target is met.</p></div>
                                            <?php if (!empty($supportCampaigns) && !empty($flashProduct)) { ?>
                                                <div class="dshprdbx border inflash">
                                                    <div class="row">     
                                                        <div class="col-sm-6 lp-bar">
                                                            <div class="row">
                                                                <div class="col-md-3"> 
                                                                    <div class="img-div-mbt">
                                                                        <img class="img-responsive" src="<?php echo getAttachmentUrlById((int) !empty($flashProduct->feature_Image) ? $flashProduct->feature_Image : "", $size = 'medium') ?>" alt="{{$flashProduct->title}}" title="{{$flashProduct->title}}">

                                                                    </div>
                                                                </div>   

                                                                <div class=" col-md-9">
                                                                    <div class="body-content-mbt ss">
                                                                        <a href="<?php echo URL::to('product/' . $flashProduct->slug); ?>"><h4 class="card-title"><?php echo!empty($flashProduct->title) ? $flashProduct->title : ""; ?></h4></a>
                                                                        <?php
                                                                        $timestamp = strtotime($flashProduct->created_at);
                                                                        $date = date('d-M-Y', $timestamp);
                                                                        ?>
                                                                        <p class="small lgt-gry" style="margin-bottom:5px"><?php echo $date; ?></p>
                                                                        <p class="card-text" style="margin-bottom:5px;display:none;"></p>
                                                                        <p class="inline-block-ele" style="margin-bottom:5px">
                                                                            <?php
                                                                            if ($flashProduct->product_type == 'variant') {
                                                                                ?>
                                                                                <span class="zit-price">Price: USDC {{$variableData['min_price']}}</span> 
                                                                            <?php } else { ?>
                                                                                <span class="zit-price">Price: USDC {{$flashProduct->price}}</span> 
                                                                            <?php } ?>
                                                                            <span style="color:grey">&nbsp;&nbsp;|&nbsp;&nbsp;</span> 
                                                                            <?php
                                                                            if ($flashProduct->product_type == 'variant') {
                                                                                if ($variableData['current_stock'] > 0) {
                                                                                    ?>
                                                                                    <span>In stock: <?php echo isset($variableData['current_stock']) ? $variableData['current_stock'] : 0; ?></span>
                                                                                <?php } else { ?>
                                                                                    <span class="error">out of stock</span>
                                                                                <?php } ?>
                                                                            <?php } else { ?>
                                                                                <?php if ($flashProduct->current_stock > 0) { ?>
                                                                                    <span>In stock: <?php echo isset($flashProduct->current_stock) ? $flashProduct->current_stock : 0; ?></span>
                                                                                <?php } else { ?>
                                                                                    <span class="red">out of stock</span>
                                                                                <?php } ?>
                                                                            <?php } ?>
                                                                        </p></div> 
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-sm-6 lrt-bar  d-flex justify-content-between arrow_box flshinf "> 
                                                            <div class="row">
                                                                <div class="col-md-12 mt-10">Selected for campaign starting from <b><?php echo!empty($dat) ? $dat : ""; ?></b></div> 
                                                                <div class="col-12 text-left"><b></b></div>
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
                                        </div>
                                        <!-- Flash Product -->

                                        <!-- Flash Product Ends-->
                                    </div>
                                </div>
                            <?php } ?>
                            <div class="col-sm-12 search-bar-top hidden">                                
                                <div class="d-flex justify-content-between w100p br-top-thin mt-20">
                                    <div class="align-self-center">
                                        <span class="d-block">Select your Product for flash sale on  <?php echo!empty($dat) ? $dat : ""; ?></span>
                                    </div>
                                    <form action="{{URL::to('/store/campaign')}}" method="get" id="product_search_form"> 
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

                        </div>      
                        <?php
                        $hidden = isset($_GET['search_products']) ? "" : "hidden";
                        ?>
                        <div class="container-brall <?php echo $hidden; ?>">
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
                                                <a class="btn btn-primary changeflash" data-camp-id="<?php echo!empty($campaignId) ? $campaignId : ""; ?>" data-product-id="<?php echo!empty($res->product_id) ? $res->product_id : ""; ?>">Choose For Flashsale</a>
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
                                                            <span><?php echo isset($variableData['current_stock']) ? $variableData['current_stock'] : 0; ?></span>
                                                        <?php } else { ?>
                                                            <span><?php echo isset($res->current_stock) ? $res->current_stock : 0; ?></span>
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
                                                         <a href="<?php //echo URL::to('/edit-product') . "/" . $res->product_id;         ?>">Edit</a> <span class="sprtr-lne"> |  </span><a class="alert-message-mbt" href="<?php //echo URL::to('/store-products-delete') . "/" . $res->product_id;         ?>">Delete</a> <span class="sprtr-lne"> |  </span> <a class="product-deactive" href="<?php //echo URL::to('change-status/' . $res->product_id . '/' . $status_text);         ?>"><?php //echo $status_text;         ?></a>                                                           
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
                <div class="strtcamp" >

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
                <a href="javascript:;" class="btn btn-default btn-primary pull-right" data-dismiss="modal">Save</a>
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
<script type="text/javascript" src="{{ URL::asset('assets/js/jquery.plugin.min.js') }}"></script>
<script type="text/javascript" src="{{ URL::asset('assets/js/jquery.countdown.new.js') }}"></script>
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
<?php
$campEndDate = !empty($supportCampaigns->camp_end) ? date_convert($supportCampaigns->camp_end) : "";
?>
<?php if (!empty($campEndDate)) { ?>
    <script>
        $(function () {
            var austDay = new Date("<?php echo $campEndDate; ?>");
            console.log(austDay);
            $('#count-running-campaignt').countdown({until: austDay, format: 'DHM'});
        });
    </script>
<?php } ?>
</main>
@endsection