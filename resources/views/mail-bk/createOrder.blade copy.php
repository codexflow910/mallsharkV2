<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet">
    <title>Order Placed</title>
    <style>
        * {
            padding: 0;
            margin: 0;
            box-sizing: border-box;
            font-family: 'Roboto', sans-serif;
        }

        .mail-container {
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
        }

        .mail-body {
            background: #f4f4f4;
            min-height: 500px;
            margin-top: 30px;
            padding: 40px 15px;
        }

        .logo-section img {
            max-width: 150px;
        }

        .products a {
            font-size: 20px;
            font-weight: bold;
            color: #183B56;
        }

        p,
        h1,
        h2,
        span,
        strong {
            color: #000;
            opacity: 80%;
        }

        p {
            line-height: 25px !important;
        }

        @media(min-width:768px) {
            .set-min-width {
                min-width: 120px;
            }
        }

        @media(max-width:768px) {
            .set-min-width {
                padding-right: .5rem;
            }
        }

        @media(max-width:576px) {
            .mail-container p {
                font-size: 14px !important;
            }

            .mail-container h1,
            .mail-container strong {
                font-size: 14px !important;
            }

            .mail-container span {
                font-size: 14px !important;
            }
        }
    </style>
</head>

<body>
    <div class="mail-container" style="padding-top:1.5rem;">
        <div dir="ltr" style="padding: 0 0rem; line-height: 25px; opacity: 80%;">
            <b>From:</b> Mallshark &lt;<a href="mailto:noreply@support.mallshark.net"
                target="_blank">noreply@support.mallshark.net</a>&gt;<br><b>Date:</b> 10 June 2023 at 11:15:01 PM
            GMT+5<br><b>To:</b> <a href="mailto:sajidoon@mindblazetech.com"
                target="_blank">sajidoon@mindblazetech.com</a><br><b>Subject:</b> <b>Your order has been
                placed!</b><br><br>
        </div>
        <div class="mail-body">
            <div style="box-shadow:  0px 0px 2px 2px #e2e2e2;">
                <div class="logo-section" style="background: #fff; padding: 26px 22px; margin-bottom: 10px;">
                    <a href="#" style="text-align: center;display: block;">
                        <img src="{{asset('assets/images/MallShark-Beta-new.svg')}}"
                            alt="Mallshark logo" title="Mallshark logo">
                    </a>
                    <!-- <div style="padding-top: 30px;" class="products">
                        <ul
                            style="list-style: none; display: flex; align-items: center; justify-content: space-around;">
                            <li>
                                <a href="#" style="text-decoration: none;">Mart</a>
                            </li>
                            <li>
                                <a href="#" style="text-decoration: none;">Fashion</a>
                            </li>
                            <li>
                                <a href="#" style="text-decoration: none;">Beauty</a>
                            </li>
                        </ul>
                    </div> -->
                    <div style="margin: 50px 0 30px 0;" class="thanks-msg">
                        <h1 style="text-align: center; font-size:16px;">Thank you for your order!</h1>
                    </div>
                    <div class="mail-to">
                        <strong style="font-size: 17px;">Hi Sajidoon Nadeem,</strong>
                        <p style="margin: 30px 0 25px 0; font-size: 16px; line-height: 30px;">
                            Your order # <strong>161818429323900</strong> has been placed successfully and we will let
                            you know once your package is on its way. Check the status of your order using the tracking
                            link below and enable push notifications on your Daraz App to receive real-time updates of
                            your order.
                        </p>
                        <button style="border: unset; display: block; margin: 0 auto;">
                            <a href="#"
                                style="text-decoration:none;color:#fffffe;display:block!important;font-size:16px;line-height:16px;font-family:'Roboto','Arial',sans-serif;text-align:center;word-break:keep-all;font-weight:700;padding:15px;background-color:#0510fa;border-radius:5px"
                                target="_blank">
                                <span style="text-decoration:none!important;color:#fffffe">TRACK MY ORDER</span>
                            </a>
                        </button>
                    </div>
                </div>

                <div style="background: #fff; padding: 20px 22px; margin-bottom: 10px;">
                    <div>
                        <h1 style="font-size:16px;">Delivery Details!</h1>
                    </div>
                    <div style="font-size:16px; margin-top: 25px;">
                        <span class="set-min-width" style="display: inline-block;">Name:</span><span>Sajidoon
                            Nadeem</span>
                    </div>
                    <div style="font-size:16px; margin-top: 20px;">
                        <span class="set-min-width" style="display: inline-block;">Address:</span><span
                        style="max-width: 400px;display: inline-block;
                        vertical-align: text-top;">169-P block
                            model  town extension lahore, Lahore - Model Town, Punjab</span>
                    </div>
                    <div style="font-size:16px; margin-top: 20px;">
                        <span class="set-min-width"
                            style="display: inline-block;">Email:</span><span>sajidoon@mindblazetech.com</span>
                    </div>
                </div>

                <div class="order-details" style="background: #fff; padding: 20px 22px 50px 22px; margin-bottom: 10px;">
                    <div>
                        <h1 style="font-size:16px;">Order Details</h1>
                    </div>
                    <div style="font-size:16px; margin-top: 25px;">
                        <p style="text-transform: uppercase;">ITEM 1</p>
                    </div>
                    <div style="font-size:16px; margin-top: 20px;">
                        <p>Sold by Al-Mustafa Trader</p>
                    </div>
                    <div style="margin-top: 55px;display: flex;">
                        <a href="#" style="width: 150px;">
                            <img src="https://dev.mallshark.net/uploads/medium/images2_2537.jpg" alt="product imag"
                                style="width: 100%; display: block;">
                        </a>
                        <div style="padding-left: 40px;font-size:16px;">
                            <p>Estimated delivery between 14 June-16 June</p>
                            <p style="padding-top: 2rem;">Rs. 99 x 1</p>
                        </div>
                    </div>
                </div>

                <div style="background: #fff; padding: 20px 22px; margin-bottom: 10px;">
                    <div style="font-size:16px; margin-top: 25px;">
                        <span> Order Total:</span>
                        <span style="width: 110px; float: right; position: relative;">
                            <span>Rs</span>
                            <span style="float: right;">99</span>
                        </span>
                    </div>

                    <div style="font-size:16px; margin-top:15px;">
                        <span> Shipping Fee:</span>
                        <div style="width: 110px; float: right; position: relative;">
                            <span>Rs</span>
                            <span style="float: right;">119</span>
                        </div>
                    </div>

                    <div style="font-size:16px; margin-top:25px; position: relative;">
                        <span>Delivery Method:</span><span style="text-transform: uppercase; float: right;">STANDARD</span>
                    </div>
                    <div style="font-size:16px; margin-top:15px; position: relative;">
                        <span>Paid By:</span><span style="text-transform: uppercase; float: right;">COD</span>
                    </div>

                </div>

                <div style="background: #fff; padding: 20px 22px;">
                    <div>
                        <div>
                            <h1 style="font-size:16px;">Need Help?</h1>
                        </div>
                        <div style="margin: 25px 0 5px 0">
                            <strong style="font-size:16px;">When will I receive my order?</strong>
                        </div>
                        <p style="font-size:16px;">
                            To track your order, go to <strong>Account</strong> > <strong>My Orders</strong> > Select
                            the order you want to track > <strong>Track Package</strong> to check the status and
                            estimated delivery date.
                        </p>
                    </div>

                    <div>
                        <div style="margin: 25px 0 5px 0">
                            <h1 style="font-size:16px;">How can I cancel my order?</h1>
                        </div>
                        <p style="font-size:16px;">
                            You may cancel your order before it is shipped by going to<strong>Account</strong> >
                            <strong>View All</strong> > select >
                            <strong>Order </strong> > <strong>Cancel</strong>
                        <p>If your order is already shipped by the seller, <span style="color: #0510fa;">chat with
                                us</span> for further assistance.</p>
                        </p>
                    </div>

                    <div style="margin-top:25px">
                        <div>
                            <h1 style="font-size:16px;">Will I be contacted before the delivery of my package?</h1>
                        </div>
                        <p style="font-size:16px; margin-top: 5px;">
                            You may be contacted by our courier partner to confirm your address.
                        </p>
                    </div>

                    <div style="margin-top: 25px; line-height: 25px;">
                        <div>
                            <h1 style="font-size:16px;">I placed my order via Cash on Delivery, can I change it to
                                prepayment?</h1>
                        </div>
                        <p style="font-size:16px; margin-top: 5px;">
                            You cannot change your payment method once your order has been placed, however, you can
                            conveniently pay at the doorstep using your bank debit or credit card through tap & pay on
                            DEX rider's phone. <span>Learn more about Tap & Pay service.</span>
                        </p>
                    </div>

                    <div style="margin: 60px 0 25px 0">
                        <button style="border: unset; display: block; margin: 0 auto;">
                            <a href="#"
                                style="text-decoration:none;color:#fffffe;display:block!important;font-size:16px;line-height:16px;font-family:'Roboto','Arial',sans-serif;text-align:center;word-break:keep-all;font-weight:700;padding:15px;background-color:#0510fa;border-radius:5px"
                                target="_blank">
                                <span style="text-decoration:none!important;color:#fffffe">Still have questions? Contact
                                    us</span>
                            </a>
                        </button>
                        <div style="margin:25px 0">
                            <h1 style="font-size:16px;">Notes</h1>
                        </div>
                        <div style="line-height: 25px; font-size:16px;">
                            <p>For more information, visit our <span style="color: #0510fa;">Help Center</span> or check
                                our <span style="color: #0510fa;">Return Policy</span>.</p>
                            <p>Please note that any transactions made off Daraz violate our Terms of Service. If a
                                seller asks you to pay off-site or through an alternative channel, please do not send
                                them money and report the matter to us.</p>
                        </div>
                    </div>

                    <div class="mail-footer" style="max-width: 500px;  margin: 60px auto 0">
                        <center>
                            <div style="margin-bottom: 30px;">
                                <strong style="font-size: 18px;"> <a href="#" style="color: #183B56; text-decoration: none;">FAQs</a> | <a href="#" style="color: #183B56; text-decoration: none;">Contact Us</a></strong>
                            </div>
                            <div class="logo-section">
                                <a href="#" style="text-align: center;display: block;">
                                    <img src="{{asset('assets/images/MallShark-Beta-new.png')}}"
                                        alt="Mallshark logo" title="Mallshark logo">
                                </a>
                            </div>

                            <div style="margin-top:30px; width:100%; display: none;">

                                <a class="icon fb-icon" style="
                                width: 42px;
                                height: 42px;
                                background: #3b5998;
                                display: inline-block;
                                line-height: 42px;
                                border-radius: 50%;                               
                                color: #fff;
                                text-decoration: none;
                                font-size: 22px;
                                margin-right: 1rem;
                                " target="_blank" href="https://www.facebook.com/ShopMallShark"><i
                                        class="fa fa-facebook"></i></a>
                                <a target="_blank"
                                style="
                                width: 42px;
                                height: 42px;
                                background: #00aced;
                                display: inline-block;
                                line-height: 42px;
                                border-radius: 50%;                               
                                color: #fff;
                                text-decoration: none;
                                font-size: 22px;
                                margin-right: 1rem;
                                " 
                                 href="https://twitter.com/ShopMallShark" class="icon t-icon"><i
                                        class="fa fa-twitter"></i></a>
                                        <img src="" alt="">
                                <a target="_blank"
                                style="
                                width: 42px;
                                height: 42px;
                                background: red;
                                display: inline-block;
                                line-height: 42px;
                                border-radius: 50%;                               
                                color: #fff;
                                text-decoration: none;
                                font-size: 22px;
                                margin-right: 1rem;
                                " 
                                
                                href="https://www.instagram.com/shopmallshark/"
                                    class="icon ins-icon">
                                    <i class="fa fa fa-instagram"></i>
                                </a>
                                <a target="_blank"
                                style="
                                width: 42px;
                                height: 42px;
                                border-radius: 50%;
                                text-decoration: none;
                                display: inline-block;
                                vertical-align: bottom;
                                " 
                                href="https://discord.gg/hW9DZapKyH" class="icon dis-icon">
                                    <img src="{{asset('assets/images/MallShark-Beta-new.svg')}}" style="width: 100%;"></a>
                            </div>

                            <div style="font-size:12px; margin: 10px 0"> Copyright © 2023 
                                <a href="https://dev.mallshark.net" style="color: #0510fa; text-decoration: none;">Mallshark.net,</a> Inc. All Rights Reserved.<p></p>
                            </div>
                        </center>
                    </div>
                </div>
            </div>

        </div>
    </div>
    </div>
</body>

</html>