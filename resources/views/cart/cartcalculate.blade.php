<!-- <div class="cart-total-box card mt-0 after-crt">
    <h3 class="mb-20 pt-15">Cart Total</h3>
    <p class="clearfix mb-10">
        <span class="lft-prombt">Total Price</span>
        <span class="right-prombt pull-right"><b>USDC &nbsp;<?php //  echo number_format($cartdata['totalProduct_amount'],2); ?></b></span>
    </p> -->
    <!-- <p class="clearfix mb-10">
        <span class="lft-prombt">Total Shipping</span>
        <span class="right-prombt pull-right"><b>USDC &nbsp;<?php //echo number_format($cartdata['totalshiphing_amount'],2); ?></b></span>
    </p> -->
    <!-- <hr> -->
    <!-- <h4 class="text-blue mb-10 m-pull-left lne-hgt"><span class="pull-left"> Grand Total: </span><span class="pull-right"><b>USDC &nbsp;<?php
                // $grandTotal = 0;

                // $grandTotal = (float)$cartdata['totalProduct_amount'] + (float)$cartdata['totalshiphing_amount'] + (float)$cartdata['product_tax'];
                // $grandTotal = (float)$cartdata['totalProduct_amount']  + (float)$cartdata['product_tax'];
                // echo number_format($grandTotal,2);
                ?></b></span></h4>
    <a href="<?php // echo URL::to('/checkout'); ?>" class="primary-btn btn btn-primary text-white">Checkout</a> -->
<!-- </div> -->
<!-- cart-->

                <div class=" col-md-12 total-container">
                    <p class=" mb-10 m-pull-left lne-hgt prcbxin">
                    <span class="grand-total-text">
                    Cart Total: </span>
                        <span class=" ">
                            <span class="show-mobile767"> Total:</span> <b class="total-price-cart">$&nbsp;<?php
                            // $grandTotal = (float) $totalproductPrice + (float) $totalShippingAmount;
                            $grandTotal = isset($cartdata['totalProduct_amount'])? (float) $cartdata['totalProduct_amount'] : 0 ;
                            echo number_format($grandTotal, 2);
                            ?></b></span>
                    </p>
                </div>

         


