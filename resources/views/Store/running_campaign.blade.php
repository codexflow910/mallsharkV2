@extends('layouts.new-main')
@section('content')
@section('title', 'Store Prodcuts')
<!-- Content Starts -->
<main>
    @include('Store.sidebar')
    <!-- Dashboard Ends -->
    <link rel="stylesheet" href="{{asset('assets/css/bootstrap2-toggle.min.css',env('HTTPS_STATUS'))  }}" type="text/css">
    <script src="{{ URL::asset('assets/js/bootstrap2-toggle.min.js',env('HTTPS_STATUS')) }}"></script>
    <div class="dshbrdin dashprdlst storeproducts-page">
        <div class="container">
            <div class="row">
                <div class="col-md-12"> 
                    <div class="dashprdlst dddd">
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
<!--                        <input id="toggle-one" checked type="checkbox">-->
                        <!-- Product Sorting Ends--> 
                        <!-- Tab sections start -->
                        <ul class=" prd nav nav-pills nav-fill">
                            <li class="nav-item">
                                <a class="nav-link" href="{{URL::to('/store/campaign')}}">Collecting Pledges</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link active" href="{{URL::to('/store/oncampaign')}}">Active Campaign</a>
                            </li>
                            <li class="nav-item">
                                <a href="<?php echo URL::to('store/next-campaign'); ?>" class="nav-link">Next Campaigns</a>
                            </li>
                        </ul>
                        <br>
                        <?php
                        //  exit;
                        if (!empty($results)) {
                            $constants = config('constants.shopzit_gamification');
                            $flashCashback = $constants['STANDARD_CASHBACK'];
                            $flashsaleDuration = $constants['CHOOSEN_CASHBACK_LIMIT'];
                            $campDateEnd = campaignEndDate(isset($results->store_id) ? $results->store_id : '');
                            $campData = getCampaignIdByStoreID(isset($results->store_id) ? $results->store_id : '');
                            $storeProducts = getProductsByStoreId($results->store_id, $flashSaleStatus = "");
                            $totalProdcuts = count($storeProducts);
                            $campId = isset($campData->store_campaign_id) ? $campData->store_campaign_id : '';
                            $flashsaleProductId = isset($campData->product_id) ? $campData->product_id : "";
                            $exprireTime = expireFlashsaleTime($flashsaleProductId);
                            $saleQuantity = getGAveBackStats($campId, $results->product_id);
                            if ($results->product_type == 'variant') {
                                $variableData = getvariableProduct($results->product_id);
                                $price = $variableData['min_price'];
                                $price = number_format($price, 2);
                                $currentStock = $variableData['current_stock'];
                                $maxSale = maxYouCanSell($price, $campData->pledges - $campData->toal_gave_back);
                            } else {
                                $price = $results->price;
                                $price = number_format($price, 2);
                                $currentStock = $results->current_stock;
                                $maxSale = maxYouCanSell($price, $campData->pledges - $campData->toal_gave_back);
                            }
                            $flashDate = getFlashSaleDate($results->product_id);
                            ?>
                            <div class="row">
                                <div class="col-sm-12 campaign-bar-top nopad">  
                                    <div class="d-flex justify-content-between  w100p ">
                                        <div class="countdown-hours">
                                            <div class=" col-12">Active Campaign <span style="color:#195dfa">(Started from <?php echo!empty($campData->camp_end) ? date_convert($campData->camp_end) : "" ?>)</span></div>
                                            <p class="ends-heading"><b>Time left before your campaign ends</b></p>
                                            <div class="col-md-12"><div id="count-running-campaign"></div></div>
                                            <div class="col-md-12">
                                                <p class="text-center" style="color:#767676; max-width:450px; margin:auto; margin-top:20px">All products will be sold at 20% off until the time period for this campaign ends or the amount raised in pledges has been consumed.</p>
                                            </div>
                                            <div class="col-sm-12">    
                                                <div class="d-flex justify-content-between mt-30" style="max-width:700px; margin:auto">
                                                    <div class="bd-highlight col-sm-4 text-center ">
                                                        <span class="d-block mb-5">Pledges Received: USDC <?php echo!empty($campData->pledges) ? $campData->pledges : 0; ?></span>
                                                    </div>
                                                    <div class="bd-highlight col-sm-4 text-center">
                                                        <span class="d-block ">Total Products: <?php echo!empty($totalProdcuts) ? $totalProdcuts : 0; ?></span>                                            
                                                    </div> 
                                                    <div class="bd-highlight col-sm-4 text-center">
                                                        <span class="d-block ">Total Given Back: USDC <?php echo!empty($campData->toal_gave_back) ? $campData->toal_gave_back : 0; ?></span>                                            
                                                    </div>                                    
                                                </div> 
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div> 
                            <?php
                            if ($exprireTime == "expire") {
                                //exit;
                                ?>
                                <div class="row mt-40 actvflsh">

                                    <div class="col-sm-12 campaign-bar-top">                                
                                        <div class="d-flex justify-content-between w100p ">
                                            <div class="countdown-hours">
                                                <h1 class="ends-heading" style="font-size:32px; font-weight:600">Your flash sale period has been expired!</h1>
                                                <p style="max-width:500px; margin:auto; font-size:18px;" class="text-center"><b>You have sold <?php echo!empty($saleQuantity->sold_qty) ? $saleQuantity->sold_qty : 0; ?> products at 75% off.</b></p>
                                                <p style="max-width:500px; margin:auto; margin-bottom:10px" class="text-center"><?php echo!empty($campData->toal_gave_back) ? number_format($campData->toal_gave_back, 2) : 0; ?> USDC consumed out of <?php echo!empty($campData->pledges) ? number_format($campData->pledges, 2) : 0; ?> Available USDC</p>
                                                <p style="max-width:500px; margin:auto" class="text-center">Untill campaign lasts or pledges consumed all of your products 
                                                    are available at minimum 20% off, supported by Mallshark</p> 

                                            </div>

                                        </div>

                                    </div>


                                    <!-- Flashin Product Start -->
                                    <div class="dshprdbx border inflash inflashoff">
                                        <div class="row">     
                                            <div class="col-sm-6 lp-bar">
                                                <div class="row">
                                                    <div class="col-md-3"> 
                                                        <div class="img-div-mbt">
                                                            <img class="img-responsive" src="<?php echo getAttachmentUrlById((int) $results->feature_image, $size = 'medium') ?>" alt="#">

                                                        </div>
                                                    </div>   

                                                    <div class=" col-md-9">
                                                        <div class="body-content-mbt">
                                                            <a href="<?php echo URL::to('product/' . $results->slug); ?>"><h4 class="card-title"><?php echo!empty($results->title) ? $results->title : ""; ?></h4></a>
                                                            <?php
                                                            $timestamp = strtotime(!empty($results->created_at) ? $results->created_at : "");
                                                            $date = date('d-M-Y', $timestamp);
                                                            ?>
                                                            <p class="small lgt-gry" style="margin-bottom:5px"><?php echo $date; ?></p>
                                                            <p class="card-text" style="margin-bottom:5px;display:none;"></p>
                                                            <p class="inline-block-ele" style="margin-bottom:5px">     

                                                                <span class="zit-price">Price: USDC <?php echo!empty($price) ? $price : ""; ?></span> 
                                                                <span style="color:grey">&nbsp;&nbsp;|&nbsp;&nbsp;</span> <span>In stock: <?php echo!empty($currentStock) ? $currentStock : 0; ?></span>                                                                                </p></div> 
                                                    </div>
                                                </div>
                                            </div> 
                                            <div class="col-sm-6 lrt-bar  arrow_box flshinf "  > 
                                                <div class="row" style="margin-top:15px">
                                                    <div class="col-md-12 mt-10">This product is on flashsale</div> 
                                                    <div class="col-md-12 text-center" style="font-size:22px"><b>Falsh Sales:  <?php echo!empty($saleQuantity->sold_qty) ? $saleQuantity->sold_qty : 0; ?></b></div>
                                                </div>
                                            </div>

                                        </div>  
                                    </div>
                                    <!-- Flashin Product Ends --> 
                                    <!-- Stats Starts -->
                                    <div class="row col-md-12 flshsts">
                                        <div class="rw-tp wt-left-tab align-self-center col-md-6 nopad">
                                            <div class="co-rvnu">
                                                <div class="lft-prf d-flex justify-content-between">
                                                    <span>Collectable Revenue:</span>
                                                    <span><?php echo!empty($results->user_revenue) ? $results->user_revenue : 0; ?>%</span>
                                                </div>
                                                <div class="lft-prf d-flex justify-content-between bggry  ">
                                                    <span>Product Type:</span>
                                                    <span>
                                                        <?php if ($results->product_type == 'variant') { ?>
                                                            <span>
                                                                <a href="javascript:;" class="productvariation" data-product-id="{{$results->product_id}}" data-toggle="modal" >Variables</a>
                                                            </span>
                                                        <?php } else { ?>
                                                            <span>Simple</span>
                                                        <?php } ?>
                                                    </span>
                                                </div>  



                                            </div>
                                        </div> 
                                        <div class="rw-tp wt-rgt-tab align-self-center col-md-6 nopad">
                                            <div class="co-rvnu ">
                                                <div class="lft-prf d-flex justify-content-between  ">
                                                    <?php if($currentStock>0){ ?>
                                                    <span>Current Stock:</span>
                                                    <span><?php echo!empty($currentStock) ? $currentStock : 0; ?></span>
                                                    <?php }else{ ?>
                                                    <span class="red">Out of stock</span>
                                                    <?php } ?>
                                                </div>
                                                <div class="rght-prf d-flex justify-content-between bggry ">
                                                    <span>Max You Can Sell:</span>
                                                    <span><?php echo!empty($maxSale) ? $maxSale : ""; ?></span>
                                                </div>
                                            </div>
                                        </div>    

                                    </div>
                                    <!-- Stats Ends -->
                                </div>
                                <!-- Flash Product Ends -->
                                <?php
                            } else {

                                $class = "btn-primary";
                                $flashsaleclass = "inflashoff";
                                $flashsaleText = "Click “Activate” to start flash sale";
                                $text = "Activate";
                                $successResult = "Flash sale activated.";
                                $alertText = "This will activate the flash sale and the product will be sold with 75% zit back. ";
                                if ($results->flashsale_status == "choosen") {
                                    $class = "btn-success";
                                    $text = "Deactivate";
                                    $flashsaleclass = "";
                                    $flashsaleText = "Flash Sale Ends In";
                                    $alertText = "Are you sure you want to stop flash sale ?";
                                    $successResult = "Flash sale deactivated.";
                                }
                                ?>
                                <div class="row mt-40 actvflsh">

                                    <div class="col-sm-12 campaign-bar-top">                                
                                        <div class="d-flex justify-content-between w100p ">
                                            <div class="countdown-hours">
                                                <p class="ends-heading"><?php echo $flashsaleText; ?></p>
                                                <?php if ($flashsaleclass == "") { ?>
                                                    <div id="count-flash-sale" style="max-width:350px; margin: auto;"></div>
                                                <?php } ?>
                                            </div>

                                        </div>
                                        <div class="col-md-12 text-center mb-40">

                                            <p><button style="padding-left:50px; padding-right: 50px;" class="btn <?php echo $class; ?> flash-sale-update" data-camp-id="<?php echo $campId; ?>" data-product-id="<?php echo $results->product_id; ?>"><?php echo $text; ?></button></p>
                                            <?php if ($flashsaleclass == "") { ?>
                                                <p class="text-center" style=" max-width: 400px; margin-top:30px; margin:auto">Flash sale activated. Buyers will receive a 75% zit back.</p>
                                                <p class="small text-center" style="margin-top:15px">Note: make sure you have enough stock before activating flash sale.</p> 

                                            <?php } else {
                                                ?>
                                                <p class="text-center" style=" max-width: 400px; margin-top:30px; margin:auto">Once activated, the flash sale will run for  <?php echo!empty($flashsaleDuration) ? $flashsaleDuration : "" ?> hours. <br>
                                                    During this time the product will be available at 75% zit back. </p>
                                                <p class="small text-center danger" style="margin-top:15px">Note: Please ensure that you have have enough items in stock to run this flash.</p> 
                                                <p><a href="<?php echo URL::to('edit-product/' . $results->product_id."?stock=update"); ?>">Add more to stock</a></p>
                                            <?php } ?>

                                        </div>
                                    </div>


                                    <!-- Flashin Product Start -->
                                    <div class="dshprdbx border inflash <?php echo $flashsaleclass; ?>">
                                        <div class="row">     
                                            <div class="col-sm-6 lp-bar">
                                                <div class="row">
                                                    <div class="col-md-3"> 
                                                        <div class="img-div-mbt">
                                                            <img class="img-responsive" src="<?php echo getAttachmentUrlById((int) $results->feature_image, $size = 'medium') ?>" alt="#">

                                                        </div>
                                                    </div>   

                                                    <div class=" col-md-9">
                                                        <div class="body-content-mbt">
                                                            <a href="<?php echo URL::to('product/' . $results->slug); ?>"><h4 class="card-title"><?php echo!empty($results->title) ? $results->title : ""; ?></h4></a>
                                                            <?php
                                                            $timestamp = strtotime(!empty($results->created_at) ? $results->created_at : "");
                                                            $date = date('d-M-Y', $timestamp);
                                                            ?>
                                                            <p class="small lgt-gry" style="margin-bottom:5px"><?php echo $date; ?></p>
                                                            <p class="card-text" style="margin-bottom:5px;display:none;"></p>
                                                            <p class="inline-block-ele" style="margin-bottom:5px">                                                          
                                                                <span class="zit-price">Price: Z: <?php echo!empty($price) ? $price : ""; ?></span> <span style="color:grey">&nbsp;&nbsp;|&nbsp;&nbsp;</span> <span>In stock: <?php echo!empty($currentStock) ? $currentStock : 0; ?></span>                                                                                </p></div> 
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-sm-6 lrt-bar  arrow_box flshinf "  > 
                                                <div class="row" style="margin-top:15px">
                                                    <div class="col-md-12 mt-10">This product is on flashsale</div> 
                                                    <div class="col-md-12 text-center" style="font-size:22px"><b>Flash Sales: <?php echo!empty($saleQuantity->sold_qty) ? $saleQuantity->sold_qty : 0; ?></b></div>
                                                </div>
                                            </div>

                                        </div>  
                                    </div>
                                    <!-- Flashin Product Ends --> 
                                    <!-- Stats Starts -->
                                    <div class="row col-md-12 flshsts">
                                        <div class="rw-tp wt-left-tab align-self-center col-md-6 nopad">
                                            <div class="co-rvnu">
                                                <div class="lft-prf d-flex justify-content-between">
                                                    <span>Collectable Revenue:</span>
                                                    <span><?php echo!empty($results->user_revenue) ? $results->user_revenue : 0; ?>%</span>
                                                </div>
                                                <div class="lft-prf d-flex justify-content-between bggry  ">
                                                    <span>Product Type:</span>
                                                    <span>
                                                        <?php if ($results->product_type == 'variant') { ?>
                                                            <span>
                                                                <a href="javascript:;" class="productvariation" data-product-id="{{$results->product_id}}" data-toggle="modal" >Variables</a>
                                                            </span>
                                                        <?php } else { ?>
                                                            <span>Simple</span>
                                                        <?php } ?>
                                                    </span>
                                                </div>  
                                            </div>
                                        </div> 
                                        <div class="rw-tp wt-rgt-tab align-self-center col-md-6 nopad">
                                            <div class="co-rvnu ">
                                                <div class="rght-prf d-flex justify-content-between ">
                                                    <?php if($currentStock>0){ ?>
                                                    <span>Current Stock:</span>
                                                    <span><?php echo!empty($currentStock) ? $currentStock : 0; ?></span>
                                                    <?php }else{ ?>
                                                    <span>Current Stock:</span>
                                                    <span class="red">Out of stock</span>
                                                    <?php } ?>
                                                </div> 
                                                
                                                <div class="rght-prf d-flex justify-content-between bggry ">
                                                    <span>Max You Can Sell:</span>
                                                    <span><?php echo!empty($maxSale) ? $maxSale : ""; ?></span>
                                                </div>   
                                            </div>
                                        </div>    
                                    </div>
                                </div>
                                <?php
                            }
                        } else {
                            ?>
                            <div class="alert alert-danger alert-success-mbt" role="alert">  No record found</div>                     
                        <?php } ?>
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
                    <h4 class="modal-title">Product Variables</h4>  
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
$(document).on("click", ".productvariation", function (e) {
    var data = {};
    data['product_id'] = $(this).attr('data-product-id');
    sendreq(data, 'product-variation', 'show', 'callbackVar');
});
function callbackVar(data) {
    $("#variable-popup").modal('show');
    $("#showvariation").html(data);
    //$("#variable-popup").modal('show');
}
    </script>
    <?php if ($results && $campDateEnd) { ?>
        <script>
            $(function () {
                var austDay = new Date("<?php echo $campDateEnd; ?>");
                console.log(austDay);
                $('#count-running-campaign').countdown({until: austDay, format: 'DHM'});
            });
        </script>
    <?php } ?>
    <?php
    if (!empty($flashDate)) {
        ?>
        <script>
            $(function () {
                var austDay = new Date("<?php echo $flashDate; ?>");
                $('#count-flash-sale').countdown({until: austDay, format: 'HMS'});
            });
        </script>
    <?php } ?>
    <script>
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
        $(".flash-sale-update").on("click", function () {
            var data = {};
            swal({
                title: "Are you sure?",
                text: "<?php echo!empty($alertText) ? $alertText : ""; ?>",
                type: "warning",
                showCancelButton: true,
                confirmButtonClass: "btn-success",
                confirmButtonText: "Yes, Do it!",
                closeOnConfirm: false,
                showLoaderOnConfirm: true,
            },
                    function () {
                        data['campaign_id'] = "<?php echo isset($campId) ? $campId : ""; ?>";
                        sendreq(data, 'flashsale', 'on', 'callback');
                    });
        });
        function callback(data) {
            if (data == "success") {
                swal("", "<?php echo!empty($successResult) ? $successResult : ""; ?>", "success");
                window.location.reload();
            }else if (data=='outofstock'){
                swal("", "This product is out of stock please update your stock and try again", "error");
            }else{
                swal("", "something went wrong.", "error");
            }
        }
        //$('#toggle-demo').bootstrapToggle('toggle');
    </script>
<!--    <script>
        $(function () {
        //    $('#toggle-one').bootstrapToggle();
        })
    </script>-->
</main>
<!-- Content Ends-->
@endsection