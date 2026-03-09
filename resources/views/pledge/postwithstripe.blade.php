<?php
$user_info = getUserById(getCurrentUserId());
$user_email = '';
if (isset($user_info->email)) {
    $user_email = $user_info->email;
}
$totalPrice = $pledge_amount * 100;
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
        data-email="<?php echo $user_email; ?>"
        data-currency="USDC"

        data-locale="auto">
    </script>
    <input type="hidden" id="pledge_for_support" name="pledge_for_support" value="yes">
    <input type="hidden" id="pledge_amount" name="pledge_amount" value="{{$totalPrice}}">
    <input type="hidden" id="product_id" name="product_id" value="<?php echo $product_id ?>">
    <input type="hidden" id="user_id" name="user_id" value="<?php echo getCurrentUserId() ?>">
    <input type="hidden" id="product_slug" name="product_slug" value="<?php echo $product_slug ?>">

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
