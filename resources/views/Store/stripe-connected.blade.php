<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Centered Logo and Text</title>
    <style>
        body {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100vh;
            margin: 0;
            font-family: Arial, sans-serif;
            background-color: #f0f0f0;
            text-align: center;
        }

        .logo {
            width: 150px;
            height: auto;
            margin-bottom: 20px;
        }

        .content {
            max-width: 600px;
            padding: 0 20px;
        }
        @media (max-width: 600px) {
    .content {
        margin: 15px;
        font-size: 20px;
        padding-top: 20px;
        padding-bottom: 20px;
    }
}
    </style>
</head>
<body style="background-color: #FFFFFF;">
    <img src="{{ asset('assets/images/MallShark-Beta-new.svg', env('HTTPS_STATUS')) }}" alt="Logo" class="logo">
    <div class="content" style="background-color: #dbecfd;
    color: #3131d0;
    border-radius: 10px;
    border: 1px solid #c6e2ff;">
        <p style="font-size: 18px;"><b style="font-size: 20px;">Congratulations!</b> <br/><br/>Your bank account is now successfully linked with Stripe, enabling you to receive payments for your online store. To proceed, click the cross icon at the top left to return to the app. Please remember to click the "CONNECT" button once you're back in the app to confirm the connection with Stripe.</p>
    </div>
</body>
</html>
