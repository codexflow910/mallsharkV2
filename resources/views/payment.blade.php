<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Apple Pay Detection</title>
    <script src="https://js.stripe.com/v3/"></script>
    <style>
        #payment-request-button {
            display: none; /* Initially hidden */
            width: 100%;
            max-width: 400px;
            margin: 20px auto;
        }
    </style>
</head>
<body>
    <h1>Check Apple Pay Availability</h1>

    <!-- Apple Pay Button Container -->
    <div id="payment-request-button"></div>

    <script>
        // Initialize Stripe with your public key
        const stripe = Stripe('pk_test_51Q7vzzBqRNMTX90UQtz7onHv5aZArZo2GnzdIWyRb69MZZfW8pbdHoijiavMd0vXlItcVdskGicLSyJRq7vZEJry00Zg1rrFn7');

        // Create a Payment Request object
        const paymentRequest = stripe.paymentRequest({
            country: 'US',
            currency: 'usd',
            total: {
                label: 'Demo total',
                amount: 1000,
            },
            requestPayerName: true,
            requestPayerEmail: true,
        });

        const elements = stripe.elements();

        // Create the paymentRequestButton element
        const prButton = elements.create('paymentRequestButton', {
            paymentRequest: paymentRequest
        });

        // Check if the device can make payments with Apple Pay and display the button
        paymentRequest.canMakePayment().then(function(result) {
            if (result) {
                // Display the Apple Pay button if available
                document.getElementById('payment-request-button').style.display = 'block';
                prButton.mount('#payment-request-button');
            } else {
                alert("Apple Pay is not available on this device.");
            }
        });
    </script>
</body>
</html>
