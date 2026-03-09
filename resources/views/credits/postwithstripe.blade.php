<?php
if (!empty($creditPlan)) {
     $user_info=getUserById(getCurrentUserId());
                            $user_email='';
                            if(isset($user_info[0]->email)){
                               $user_email=$user_info[0]->email;
                                
                            }
    $totalPrice = $creditPlan['plan_price'] * 100;
    ?>
    <script src="https://code.jquery.com/jquery-1.12.4.js"></script>
    <form action="/stripe-response" method="POST">
        <script
            src="https://checkout.stripe.com/checkout.js" class="stripe-button"
            data-key="{{env('STRIPE_API_KEY_PUBLISHABLE')}}"
            data-amount="<?php echo $totalPrice; ?>"
            data-name="Mallshark"
            data-description="Checkout Charge"
            data-image="https://stripe.com/img/documentation/checkout/marketplace.png"
            data-email="<?php echo $user_email;?>"
            data-currency="USDC"

            data-locale="auto">
        </script>
        <input type="hidden" id="credit_purchase" name="credit_post_purchase" value="yes">
        <input type="hidden" id="credit_pan_id" name="credit_pan_id" value="<?php echo $creditPlan['credit_plan_id'] ?>">

        {{ csrf_field() }}

    </form>
    <script>
        $(document).ready(function () {
            setTimeout(function () {
                $(".stripe-button-el").click();
            }, 1000);

        });

    </script>
    <style>

        .stripe-button-el{
            display:none;
        }
    </style>
<?php } ?>