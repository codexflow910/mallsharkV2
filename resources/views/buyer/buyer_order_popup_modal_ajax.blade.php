<?php
foreach ($orderDetails as $orders) {
    $sum = 0;
    $refundAmount = 0;
   
?>

    <?php foreach ($orders['orderItems'] as $detail) {
        
        //    dump($variable_product);
        $order_id = $detail->order_id;
        $store_id = $detail->store_id;
        $payment_method = $detail->payment_method;

    ?>  
        <div class="popup-wrapper">
            <form  id="order_refunded">
                @csrf
                <div class="container" style="padding: 0px;">
                    <!-- <input type="hidden" name="order_id" id="order_id" value="{{$detail->order_id}}">
                    <input type="hidden" name="buyer_id" id="buyer_id" value="{{$orders->buyer_id}}">
                    <input type="hidden" name="store_id" id="store_id" value="{{$detail->store_id}}">
                    <input type="hidden" name="order_status" id="order_status" value="dispute"> -->

                    <input type="hidden" name="order_id" id="order_id" class="order_id" value="{{$order_id}}">
                    <input type="hidden" name="buyer_id" id="buyer_id" class="buyer_id" value="{{$orders->buyer_id}}">
                    <input type="hidden" name="store_id" id="store_id" class="store_id" value="{{$detail->store_id}}">
                    <input type="hidden" name="order_status" id="order_status" class="order_status" value="cancelled">
                    <input type="hidden" name="payment_method" id="payment_method" class="payment_method" value="{{$payment_method}}">

                    <?php

                    $product = getProductId($detail->item_id);
                // dump($product);
                    $product_title = isset($detail->product_name) ? $detail->product_name : '';
                    $product_slug = isset($product->slug) ? $product->slug : '';
                    $product_id = isset($detail->item_id) ? $detail->item_id : '';
                    $product_type = !empty($product->product_type) ? $product->product_type : '';
                    $payment_method = isset($orders->payment_method) ? $orders->payment_method : '';
                    // dump("payment method" , $payment_method);
                    $image_url = isset($detail->item_image) ? $detail->item_image : '';
                    $product_quantity = isset($detail->quantity) ? $detail->quantity : "";
                    $shipping_amount = isset($detail->shipping_amount) ? $detail->shipping_amount : "";
                    $price = isset($detail->price) ? $detail->price : "";
                    $sale_price = isset($detail->sold_price) ? $detail->sold_price : null;
                    $sale_id = isset($detail->sale_id) ? $detail->sale_id : null;
                    $variation_id = isset($detail->variation_id) ? $detail->variation_id : 0;
                    if (isset($sale_id) && !is_null($sale_id)) {
                        $finalPrice = $orders->total_amount ?? $shipping_amount + ($sale_price * $product_quantity);
                    } else {
                        $finalPrice = $orders->total_amount ?? $shipping_amount + ($price * $product_quantity);
                    }

                    
                    $finalAmount = $finalPrice - ($finalPrice * (2.9 / 100) + 0.3);
                    $refundAmount += $finalAmount;
                    $sum += $finalPrice;
                  
                    ?>
                    <input type="hidden" id="cancel_order_accept" data-price="{{$finalPrice}}" wallet-address="{{ $orders->wallet_address }}" data-quantity="{{$product_quantity}}" data-order-id="{{$detail->order_id}}" data-product-id="{{$product_id}}" data-payment_method="{{$payment_method}}" value="{{$sum}}" data-image="{{$image_url}}" class="form-check-input cancel_order_accept_{{$detail->order_id}}" />
                    <input type="hidden" name="refund_product_id[]" id="refund_product_id" value="{{$product_id}}">
                    <input type="hidden" name="refund_product_title[]" id="refund_product_title" value="{{$product_title}}">
                    <div class="row mb-2  form-group" style="display: flex; align-items: center;margin:0px 0px;">
                        <div class="col-md-12" style="display: flex;align-items: flex-start;margin-bottom:16px;padding:0px;">
                            <input type="checkbox" name="product_id[]" id="select_price_refund" data-variation-id="{{$variation_id}}" data-price="{{ !empty($sale_id) && $sale_id > 0 ? $sale_price : $price }}" data-shipment="{{$shipping_amount}}" data-quantity="{{$product_quantity}}" data-order-id="{{$order_id}}" data-product-title="{{$product_title}}" data-product-slug="{{$product_slug}}" value="{{$product_id}}" data-image="{{$image_url}}" class="form-check-input select_price_cancel_order" />
                            <div class="media">
                                <img class="mr-2" src="{{url('uploads/orders/').'/'.$image_url}}" onerror="this.src='{{url('/assets/images/placeholder.jpg')}}'"  alt="Generic placeholder image" width="85px">
                            </div>
                                <div class="media-body col-md-4 text-center">
                                    <span class="mt-0"><a href="/product/<?php echo $product_slug; ?>"> {{$product_title}} </a></span>
                                    <p></p>
                                </div>
                                <div class="col-md-4 text-center">
                                    <p class=" "><b>Quantity: </b> {{$product_quantity}}</p>
                                </div>
                                <div class="col-md-4 text-center">
                                    <p class="order_shipping_amount"><strong>Price: ${{ !empty($sale_id) && $sale_id > 0 ? $sale_price : $price }} </strong>(Inc. Shippment ${{$shipping_amount}}) </p>
                                </div>
    
                            
                        </div>
                       
                       
                        
                    </div>
                </div>
                <?php } ?>
                <div class="return-prolicy">
                    <div class="row" style="padding:0px 6px;">
                        <div class="col-12">
                            <h4 style="margin-bottom:16px;">Return Policy</h4>
                            <p>If your product is damaged, defective, incorrect or incomplete at the time of delivery, please raise a return request on Mallshark app or website.
                                Return request must be raised within 14 days for Mallshark items and within 7 days for non-Mallshark items from the date of delivery.
                                All Mallshark items are 100% Authentic by Trusted Brands and are covered under 2x Money Back Guarantee.
                                Note: Groceries and Digital Goods are excluded from 2x Money Back Guarantee.
                            </p>
                        </div>
                    </div>
                </div>


                <div class="cancle-order-reason-fields">
                    <div class="form-group">
                        <h4 style="margin-bottom:1rem;">Reason Of Return</h4>
                        <select class="form-control refund_reason_type cancel_reason_type_{{$order_id}}" id="refund_reason_type" name="refund_reason_type">
                            <option value="">select Reason</option>
                            <option value="The customer ordered the wrong product or size">
                                I ordered the wrong product or size</option>
                            <option value="The merchant shipped the wrong product or size">
                                The merchant shipped the wrong product or size</option>
                            <option value="The product was damaged or defective">The
                                product was damaged or defective</option>
                            <option value="The product arrived too late">The product
                                arrived too late</option>
                            <option value="The customer no longer needed the product">
                                I no longer needed the product</option>
                            <option value="The product did not match the description">
                                The product did not match the description</option>
                            <option value="The product did not meet the customer’s expectations">
                                The product did not meet my expectations
                            </option>
                        </select>
                    </div>

                    <div class="form-group">
                        <textarea type="text" id="reason" class="form-control cancel_reason_{{$order_id}} some_other_reason" name="reason" placeholder="Any additional information you'd like to share?"></textarea>
                    </div>
                    <?php $now = date("Y-m-d"); ?>
                    <input type="hidden" name="is_refund" id="cancle" value="cancelled">
                    <input type="hidden" name="refund_date" id="refund_date" value="{{$now}}">

                    <div class="row " >
                        <div class="col-md-6">
                            <p class="text-primary total1_{{$order_id}}" id="total1_{{$order_id}}"></p>
                            <!-- <input type="text" id="total1" readonly> -->
                        </div>
                        <div class="col-md-6 text-right">
                        <button id="cancel_order_submit" class="btn btn-primary cancel_order_submit" style="margin-right: 6px;">Request Refund</button>

                        </div>

                    </div>
                </div>
            </form>
        </div>
  
<?php } ?>