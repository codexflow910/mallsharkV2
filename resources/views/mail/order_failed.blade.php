<!-- resources/views/emails/order_failed.blade.php -->

<!DOCTYPE html>
<html>
<head>
    <title>Payment Failure Notification</title>
</head>
<body>
    <p>Dear {{ $user->name }},</p>

    <p>We regret to inform you that your recent order with {{ config('app.name') }} could not be processed successfully due to a payment failure.</p>

    <h3>Order Details:</h3>
    <ul>
        <li><strong>Order Number:</strong> {{ $order }}</li>
    </ul>

    <h3>Reason for Failure:</h3>
    <p>The payment for your order was declined by Stripe, our payment processor. This could be due to several reasons, such as:</p>
    <ul>
        <li>Insufficient funds in your account</li>
        <li>Incorrect card details</li>
        <li>Expired card</li>
        <li>Your bank rejecting the transaction</li>
    </ul>

    <p>We recommend checking your payment details and ensuring they are up to date. You may also want to contact your bank for further information.</p>

    {{-- <h3>Next Steps:</h3>
    <p>To complete your purchase, please follow these steps:</p>
    <ol>
        <li>Visit our website and log into your account.</li>
        <li>Navigate to your order history and select the failed order.</li>
        <li>Re-enter your payment details and try again.</li>
    </ol>

    <p>If you need any assistance or have any questions, please do not hesitate to contact our customer support team at {{ config('app.support_email') }} . We are here to help you and ensure your shopping experience is smooth and satisfactory.</p> --}}

    <p>We apologize for any inconvenience this may have caused and thank you for your understanding.</p>

    <p>Best regards,</p>
    <p>{{ config('app.name') }}</p>
</body>
</html>
