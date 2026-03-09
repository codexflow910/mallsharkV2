<?php
foreach ($orderDetails as $orders) {
    // dump("orders", $orders);
    // dump("orders", $orders);
    $sum = 0;
    $refundAmount = 0;
    $order_id = $orders->order_id;
    $store_id = $orders->store_id;
    $total_amount = $orders->total_amount;
?>

    <?php 

    foreach ($orders['refundItems'] as $detail) {
        
        
    ?>
        <div class="popup-wrapper">
            <form action="{{URL::to('/reject_order')}}" method="post" id="reject_order">
                @csrf
                <div class="">
                    <input type="hidden" name="order_id" id="order_id" value="{{$detail->order_id}}">
                    <input type="hidden" name="buyer_id" id="buyer_id" value="{{$orders->buyer_id}}">
                    <input type="hidden" name="store_id" id="store_id" value="{{$detail->store_id}}">
                    <input type="hidden" name="order_status" id="order_status" value="dispute">

                    <?php

                    $product = getProductId($detail->product_id);
                    $product_type = !empty($product->product_type) ? $product->product_type : '';
                    if(!empty($product_type) && $product_type == 'variant'){
                        $variation_id = $detail->variation_id ? $detail->variation_id : null;
                        if($variation_id){

                            $variable_product = getvariableByProduct_id($detail->variation_id);
                        }
                    if (!empty($variable_product)) {
                        $product_variation_image = isset($variable_product->product_variation_image) ? $variable_product->product_variation_image : '';  
                        
                        // $variation_image_url = getAttachmentUrlById((int) $product_variation_image, 'medium');
                        
                    }
                    }
                    $product_title = isset($product->title) ? $product->title : '';
                    $product_id = isset($detail->product_id) ? $detail->product_id : '';
                    $reason_type = isset($detail->reason_type) ? $detail->reason_type : '';
                    $reason = isset($detail->reason) ? $detail->reason : '';

                    $payment_method = isset($orders->payment_method) ? $orders->payment_method : '';
                    // dump("payment method" , $payment_method);
                    $p_id = isset($detail->product_id) ? $detail->product_id : '';
                    $product_quantity = isset($detail->quantity) ? $detail->quantity : "";
                    $shipping_amount = isset($detail->shipping_amount) ? $detail->shipping_amount : "";
                    $price = isset($detail->price) ? $detail->price : "";
                    // $finalPrice = $price;
                    $finalPrice = $total_amount;
                    // $finalAmount = $finalPrice - ($finalPrice * (2.9 / 100) + 0.3);
                    $refundAmount += $finalPrice;
                    
                    $sum += $finalPrice;
                    // echo '$detail: <pre>' .print_r($detail,true). '</pre>';
                    // die;
                    $pslug = isset($product->slug) ? $product->slug : '';

                    $image = isset($product->feature_image) ? $product->feature_image : '';
                    // dump($product);
                    $image = (int) $image;
                    $image_url = '';
                    if($product_type == 'simple' ){
                        $image_url = getAttachmentUrlById((int) $image, 'medium');
                    }elseif($product_type == 'variant'){
                        if(!empty($product_variation_image)){
                            
                            $image_url = getAttachmentUrlById((int) $product_variation_image, 'medium');
                        }
                        if(empty($image_url)){
                            // $image_url = getAttachmentUrlById((int) $image, 'small');
                        }
                    }else{ 
                        $image_url = getAttachmentUrlById((int) $image, 'medium');
                    }

                    // if ($image != '') {
                    //     $image_url = getAttachmentUrlById($image, 'small');
                    // }
                    $dateCreated = isset($product->created_at) ? $product->created_at : '';
                    ?>
                    <input type="hidden" id="cancel_order_accept" data-price="{{$price}}" wallet-address="{{ $orders->wallet_address }}" data-quantity="{{$product_quantity}}" data-order-id="{{$detail->order_id}}" data-product-id="{{$p_id}}" data-payment_method="{{$payment_method}}" value="{{$sum}}" class="form-check-input cancel_order_accept_{{$detail->order_id}}" />
                    <input type="hidden" name="refund_product_id[]" id="refund_product_id" value="{{$product_id}}">
                    <input type="hidden" name="refund_product_title[]" id="refund_product_title" value="{{$product_title}}">
                    <div class="row mb-2 p-2 form-group align-items-top" style="margin-bottom: 16px!important;background-color:#EAECEF;padding:10px 0px!important;margin-top:-20px;border-bottom:5px solid #fff;">
                        <div class="col-md-5 popup-col-5 text-center">
                            <div class="media align-items-top">

                                <img class="" src="<?php echo $image_url; ?>" onerror="this.src='{{url('/assets/images/placeholder.jpg')}}'" alt="Generic placeholder image" width="85px">
                                <div class="media-body">
                                    <span class="mt-0">{{$product_title}}</span>
                                    <p></p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 popup-col-3 text-center">
                            <p class=""><b>Quantity: </b> {{$product_quantity}}</p>
                        </div>

                        <div class="col-md-4 popup-col-4 text-center">
                            <p class="order_shipping_amount"><strong>Unit Price: ${{$price+$shipping_amount}} </strong>(Inc. Shippment ${{$shipping_amount}}) </p>
                                {{-- (Inc. Shippment ${{$shipping_amount}}) --}}
                             </p>
                        </div>
                    </div>

                </div>
            <?php } ?>
            <div class="return-prolicy">
                <div class="row">
                    <div class="col-12">
                        <h3 style="margin-bottom: 16px!important;font-size:14px;color:#2f2f2f!important;"><b>Reason Of Return: </b>  <span style="font-size: 14px!important;color:#2f2f2f!important;">
                            <?php
                            if (isset($reason_type)) {
                                echo $reason_type;
                            } ?>,


                            <?php if (isset($reason) && !empty($reason)) { ?>
                        {{-- <h3 style="margin-bottom: 16px!important;">Additional Return Reason</h3> --}}
                        <span style="font-size: 14px!important;color:2f2f2f!important;"><?php echo $reason;
                        } else {
                            echo "";
                        }
                ?></span>
                    
                    </span></h3>
                       
                    </div>
                </div>
            </div>


            <div class="cancle-order-reason-fields">
                <div class="form-group" style="margin-bottom: 0px;position:relative;">
                  <div class="vendorRefundReq">
                    {{-- <h3 style="margin-bottom: 0px!important;margin-left:-5px;">Accept Refund Request</h3> --}}
                    <div class="row align-items-center">
                        <div class="col-sm-1"></div>
                        <div class="col-sm-5" style="padding-right: 5px!important;">
                            <h3 class=" mb-0" style="font-size: 14px;color:#2f2f2f!important;"><b>Maximum Refund Amount will be: </b> 
                                {{-- <span style="color: #0510fa; font-weight:bold;"> ${{number_format($refundAmount, 2, '.', '')}} </span> --}}
                            </h3>
                        </div>
                        <div class="col-sm-5 col-6 d-flex acceptFundRequestCont">
                            <div class="form-group mx-sm-3 mb-2" style="margin-left:0px!important;margin-right:2px!important;">
                                <input type="text" pattern="/^-?\d+\.?\d*$/" onKeyPress="if(this.value.length==8) return false;" id="cancel_order_price" data-price='{{$total_amount}}' class="form-control product-price" value="" placeholder="{{number_format($total_amount, 2, '.', '')}}" style="width: 150px; position:relative; top:5px; border-radius:32px; margin-right:5px" />
                            </div>
                            <?php
                            if ($payment_method == 'stripe') { ?>
                                <button type="button" data-product_id="{{$detail->product_id}}" data-product_quantity="" data-payment_method="{{$payment_method}}" data-store_id="{{$store_id}}" data-order_id="{{$order_id}}" class="btn btn-success accept_amount_stripe" style="height: 36px; position: relative;top: 5px;background-color:#0510fa!important;border-color:#0510fa!important;"> Accept Refund </button>

                            <?php  } else { ?>
                                <button type="button" data-product_id="{{$detail->product_id}}" data-product_quantity="" data-payment_method="{{$payment_method}}" data-store_id="{{$store_id}}" data-order_id="{{$order_id}}" class="btn btn-success accept_amount" style="height: 40px; position: relative;top: 5px;"> Refund Accepted </button>

                            <?php      }  ?>
                        </div>
                        <div class="col-sm-1"></div>

                    </div>
            </div>
                    <hr>
                    <div class="form-group mt-5 row" style="margin-bottom: 0px;padding-left:10px;display:flex;justify-content:center;">

                        {{-- <h4 style="margin-bottom: 16px;">Reject Refund Request</h4> --}}
                        <span class="col-md-2 refundTypeReason" style="margin-left:-81px;font-weight:bold;text-align:end;align-self:center;color:#2f2f2f;font-size:14px;">Type Reason: </span>
                        <input style="border-radius:32px; max-width:366px;" type="text" name="reject_order_reson" class="form-control col-md-8" id="reject_order_reson" placeholder="Share your reason for rejecting this request" />
                        <div class="col-md-2 text-right" style="padding-right: 5px;">
                            <button type="submit" id="reject_submit" class="btn btn-danger">Reject Refund</button>
                            <!-- <input type="submit" id="return_submit"  class="btn btn-primary" value="Request Return"> -->
                        </div>
                    </div>
                    <div class="error rectingReqError" id="reasonError"></div>

                    <?php $now = date("Y-m-d"); ?>

                    <input type="hidden" name="is_reject" id="is_reject" value="1">
                    <input type="hidden" name="is_refund" id="is_refund" value="dispute">
                    <input type="hidden" name="order_status" id="order_status" value="dispute">
                    <input type="hidden" name="reject_date" id="reject_date" value="{{$now}}">
                    <input type="hidden" name="order_id" id="order_id" value="{{$order_id}}">

                    <div class="row">
                        <div class="col-md-6">
                            <p class="text-primary reject_order_{{$order_id}}" id="reject_order_{{$order_id}}"></p>
                            <!-- <input type="text" id="total1" readonly> -->
                        </div>
                        {{-- <div class="col-md-6 text-right">
                            <button type="submit" id="reject_submit" class="btn btn-danger">Reject Refund Request</button>
                            <!-- <input type="submit" id="return_submit"  class="btn btn-primary" value="Request Return"> -->
                        </div> --}}

                    </div>

                </div>
            </div>
            </form>
        </div>

    <?php } ?>

    <script>
        $(document).on('keypress', '#cancel_order_price', function(event) {
    var key = event.which || event.keyCode;
    enableAcceptRefundButton();
    // Allow numbers, backspace, and decimal point
    if (!(key >= 48 && key <= 57) && // Numbers
        key != 8 && // Backspace
        key != 46 && // Decimal point
        key != 37 && // Left arrow
        key != 39) // Right arrow
    {
        event.preventDefault();
    }
});

$(document).on('keyup', '#cancel_order_price', function(event) {
    // Get the price and quantity from data attributes
    var price = parseFloat($('#cancel_order_price').data('price'));
    // var quantity = parseInt($('#cancel_order_accept').data('quantity'));
    
    // Calculate the total refund amount based on the price and quantity
    // var totalRefundAmount = price * quantity;
    var totalRefundAmount = price;
    // Get the order input value
    var orderInputValue = parseFloat($(this).val());

    // Check if the order input value is greater than the total refund amount
    if (orderInputValue > totalRefundAmount) {
        // Display an error message
        $('#reasonError').text('Order input value cannot be greater than the total refund amount.');
        disableAcceptRefundButton();
    } else {
        $('#reasonError').text(''); // Clear error message if valid
        enableAcceptRefundButton();
    }
});
function disableAcceptRefundButton() {
    $('.accept_amount, .accept_amount_stripe').prop('disabled', true);
}

function enableAcceptRefundButton() {
    $('.accept_amount, .accept_amount_stripe').prop('disabled', false);
}

        $('#reject_order').submit(function(e) {
            e.preventDefault();
            var reject_order_reson = $('#reject_order_reson').val();
            if (reject_order_reson === '') {
                $('#reasonError').text('Please enter your reason.');
                $('#reject_order_reson').css('border', '1px solid red');
                return;
            } else {
                $('#reasonError').text(''); // Clear error message if valid
            }
            
            let loader = jQuery('#loading').show();

            $.ajax({
                url: '{{ route('reject_order') }}',
                type: 'POST',
                data: $('#reject_order').serialize(), // Serialize form data
                success: function(response) {
                    jQuery('#loading').hide();
                    $("#orderModal").modal('hide');
                    
                    if(response){
                    

                        toastr.options = {
                            closeButton: true, // Add close button
                            disableTimeOut: true,
                            tapToDismiss: true,
                            positionClass: 'toast-top-right',
                            toastClass: 'toast-icon custom-toast-success'
                            };
                        toastr.success(response.msg);
                        setTimeout(() => {
                            location.reload();
                        }, 2000);
                    }else{
                        console.log(response);

                    }
                    
                    // Handle the server response here
                    console.log(response);
                }
            });

        });
    </script>