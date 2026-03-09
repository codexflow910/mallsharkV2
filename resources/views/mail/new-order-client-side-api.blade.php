<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet">
    <style>
        * {
            padding: 0;
            margin: 0;
            box-sizing: border-box;
            font-family: 'Roboto', sans-serif;
            line-height: 25px;
            font-size: 16px;
        }
        .subject {
            text-align: center;
            font-size: 20px;
            font-weight: bold;
            margin-top: 20px;
            text-decoration: underline;

        }
        .mail-container {
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
        }

        .mail-footer .logo-section {
            display: none;
        }

        p{
          font-size: 16px;
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
                min-width: 80px;
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




<body style="background:#F9FBFE;">
    <div marginwidth="0" marginheight="0" style="background-color:#f7f7f7;padding:0;text-align:center;" bgcolor="#f7f7f7">
        <table width="100%" style="background-color:#f7f7f7;">
            <tbody>
                <tr>
                    <td></td>
                    <td width="600">
                        <div style="margin:0 auto;padding:70px 0;width:100%;max-width:600px;">
                            <table border="0" cellpadding="0" cellspacing="0" height="100%" width="100%">
                                <tbody>
                                    <tr>
                                        <td align="center" valign="top">
                                           
                                            <table border="0" cellpadding="0" cellspacing="0" width="100%">
                                                <tbody>
                                                    <tr>
                                                        <td>
                                                            <table border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color:#3c98cc;color:#fff;border-bottom:0;font-weight:bold;line-height:100%;vertical-align:middle;font-family:'Helvetica Neue',Helvetica,Roboto,Arial,sans-serif;border-radius:3px 3px 0 0;" bgcolor="#3c98cc">
                                                                <tbody>
                                                                    <tr>
                                                                        <td style="padding:36px 48px;display:block;">
                                                                            <h1 style="font-family:Helvetica Neue,Helvetica,Roboto,Arial,sans-serif;font-size:30px;font-weight:300;line-height:150%;margin:0;text-align:left;color:#fff;">Thank you for your order</h1>
                                                                        </td>
                                                                    </tr>
                                                                </tbody>
                                                            </table>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td align="center" valign="top">
                                                            <table border="0" cellpadding="0" cellspacing="0" width="100%">
                                                                <tbody>
                                                                    <tr>
                                                                        <td valign="top" style="background-color:#fff;padding:48px;" bgcolor="#fff">
                                                                            <table border="0" cellpadding="20" cellspacing="0" width="100%">
                                                                                <tbody>
                                                                                    <tr>
                                                                                        <td valign="top">
                                                                                            <div style="color:#636363;font-family:'Helvetica Neue',Helvetica,Roboto,Arial,sans-serif;font-size:14px;line-height:150%;text-align:left;">
                                                                                                <p style="margin:0 0 16px;">Hi {{$customerDetail->name }},</p>
                                                                                                <p style="margin:0 0 16px;">Just to let you know — we've received your order [# {{$order_id}}], and it is now being processed:</p>
                                                                                                <h2 style="color:#3c98cc;font-size:18px;font-weight:bold;line-height:130%;margin:0 0 18px;text-align:left;">[Order # {{$order_id}}] ({{ \Carbon\Carbon::now()->format('m/d/Y') }})</h2>
                                                                                                <div style="margin-bottom:20px;">
                                                                                                    <table cellspacing="0" cellpadding="6" border="1" style="color:#636363;border:1px solid #e5e5e5;vertical-align:middle;width:100%;font-family:'Helvetica Neue',Helvetica,Roboto,Arial,sans-serif;">
                                                                                                        <thead>
                                                                                                            <tr>
                                                                                                                <th style="padding:12px;text-align:left;border:1px solid #e5e5e5;">Product</th>
                                                                                                                <th style="padding:12px;text-align:left;border:1px solid #e5e5e5;">Quantity</th>
                                                                                                                <th style="padding:12px;text-align:left;border:1px solid #e5e5e5;">Price</th>
                                                                                                            </tr>
                                                                                                        </thead>
                                                                                                        <tbody>
                                                                                                        <?php 
                                                                                                         $grandTotal = 0;
                                                                                                         $shippingPriceTotal = 0;
                                                                                                            foreach($productDetails as $key =>  $value) {     

                                                                                                                if ($value) {
                                                                                                                    $product_variation_image = !empty($value->product_variation_image) ? $value->product_variation_image : '';
                                                                                                                    $product_type =  $value->product_type;
                                                                                                                    $image =  $value->feature_image;
                                                                                                                

                                                                                                                    $image_url = '';
                                                                                                                    if(!empty($image)){
                                                                                                                        if($product_type == 'simple' ){
                                                                                                                            $image_url = getAttachmentUrlByIdEmailTemplete( $image, 'medium');
                                                                                                                            }elseif($product_type == 'virtual'){
                                                                                                                                $image_url = getAttachmentUrlByIdEmailTemplete((int) $image, 'medium');
                                                                                                                            }else{ 
                                                                                                                                $image_url = getAttachmentUrlByIdEmailTemplete((int) $product_variation_image, 'medium');

                                                                                                                            }
                                                                                                                            $image_url = str_replace([' ', '(', ')'], ['%20', '%28', '%29'], $image_url);
                                                                                                                        } 
                                                                                                                        $url = env('base_url');
                                                                                                                
                                                                                                                        ?>
                                                                                                                        <tr>
                                                                                                                                <td style="padding:12px;text-align:left;border:1px solid #e5e5e5;">{{ isset($value->title) ? $value->title: "" }}</td>
                                                                                                                                <td style="padding:12px;text-align:left;border:1px solid #e5e5e5;">{{ isset($value->quantity) ? $value->quantity: "" }}</td>
                                                                                                                                <td style="padding:12px;text-align:left;border:1px solid #e5e5e5;">${{ isset($value->sold_price) && $value->sold_price > 0 ? $value->sold_price : $value->price }}</td>
                                                                                                                
                                                                                                                            </tr>
                                                                                                                    <?php
                                                                                                                    
                                                                                                                    $quantity =  isset($value->quantity) ? $value->quantity : 1;
                                                                                                                    $item_price =  isset($value->sold_price) && $value->sold_price > 0 ? $value->sold_price : $value->price;
                                                                                                                    $shippingPrice = isset($value->shipping_amount) ? $value->shipping_amount : 0;
                                                                                                                    $total_item_price = $item_price * $quantity;
                                                                                                                    $grandTotal += $total_item_price;
                                                                                                                    $shippingPriceTotal += $shippingPrice;}
                                                                                                                }
                                                                                                            

                                                                                                            ?>
                                                                                                        </tbody>
                                                                                                        <tfoot>
                                                                                                            <tr>
                                                                                                                <th scope="row" colspan="2" style="color:#636363;border:1px solid #e5e5e5;vertical-align:middle;padding:12px;text-align:left;border-top-width:4px" align="left">Subtotal:</th>
                                                                                                                <td style="color:#636363;border:1px solid #e5e5e5;vertical-align:middle;padding:12px;text-align:left;border-top-width:4px" align="left">${{ number_format($grandTotal, 2) }}</td>
                                                                                                            </tr>
                                                                                                            <tr>
                                                                                                                <th scope="row" colspan="2" style="color:#636363;border:1px solid #e5e5e5;vertical-align:middle;padding:12px;text-align:left" align="left">Shipping Price:</th>
                                                                                                                <td style="color:#636363;border:1px solid #e5e5e5;vertical-align:middle;padding:12px;text-align:left" align="left">${{$shippingPriceTotal}}</td>
                                                                                                            </tr>
                                                                                                            <tr>
                                                                                                                <th scope="row" colspan="2" style="color:#636363;border:1px solid #e5e5e5;vertical-align:middle;padding:12px;text-align:left" align="left">Total:</th>
                                                                                                                <td style="color:#636363;border:1px solid #e5e5e5;vertical-align:middle;padding:12px;text-align:left" align="left"><span><span>$</span>{{ number_format($grandTotal + $shippingPriceTotal, 2) }}</span></td>
                                                                                                            </tr>
                                                                                                        </tfoot>
                                                                                                    </table>
                                                                                                </div>
                                                                                                <table width="100%" cellspacing="0" style="margin-bottom:40px;">
                                                                                                    <tbody>
                                                                                                        <tr style="vertical-align:baseline;">
                                                                                                            <td style="text-align:left;">
                                                                                                                <h2 style="color:#3c98cc;font-size:18px;font-weight:bold;margin-bottom:20px;">Billing address</h2>
                                                                                                                <address style="padding:12px;color:#636363;border:1px solid #e5e5e5;">
                                                                                                                    {{$name}}<br>
                                                                                                                    {{$street. ' '.$city.' '.$state}}<br>
                                                                                                                    {{$country}}, {{$postalcode}}<br>
                                                                                                                    <a href="tel:9999999999" style="color:#3c98cc;">999999999</a><br>
                                                                                                                    <a href="mailto:abdullah@mindblazetech.com">{{$email}}</a>
                                                                                                                </address>
                                                                                                            </td>
                                                                                                            <td style="text-align:left;">
                                                                                                                <h2 style="color:#3c98cc;font-size:18px;font-weight:bold;margin-bottom:20px;">Shipping address</h2>
                                                                                                                <address style="padding:12px;color:#636363;border:1px solid #e5e5e5;">
                                                                                                                    {{$name}}<br>
                                                                                                                    {{$street. ' '.$city.' '.$state}}<br>
                                                                                                                    {{$country}}, {{$postalcode}}
                                                                                                                </address>
                                                                                                            </td>
                                                                                                        </tr>
                                                                                                    </tbody>
                                                                                                </table>
                                                                                                
                                                                                                <p style="margin:0;">Thanks for using <a href="https://mallshark.com" style="color:#3c98cc;">www.mallshark.com</a>!</p>
                                                                                            </div>
                                                                                        </td>
                                                                                    </tr>
                                                                                </tbody>
                                                                            </table>
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td align="center" valign="top">
                                                                            <table border="0" cellpadding="10" cellspacing="0" width="100%">
                                                                                <tbody>
                                                                                    <tr>
                                                                                        <td colspan="2" style="padding:24px;color:#3c3c3c;text-align:center;">
                                                                                            <p style="margin:0;font-size:12px;line-height:35px;">MallShark</p>
                                                                                            <div>
                                                                                            </div>
                                                                                            <a href="https://mallshark.com/privacy-policy" style="color:#3c3c3c;text-decoration:underline;margin-right:10px;">Privacy Policy</a> | 
                                                                                            <a href="https://mallshark.com/terms" style="color:#3c3c3c;text-decoration:underline;">Terms of Use</a>
                                                                                        </td>
                                                                                    </tr>
                                                                                </tbody>
                                                                            </table>
                                                                        </td>
                                                                    </tr>
                                                                </tbody>
                                                            </table>
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div style="margin-top: 30px;">
                            <p style="font-size: 16px;">We thank you for participating in our community and look forward to more successful orders like this one.</p>
                        </div>
                        <div style="margin-top: 30px;">
                            <p style="font-size: 16px;">To check the status of your orders at any time, log into your account and go to the <a href="{{ env('FRONT_END_URL') }}/orders" target="_blank" > orders section.</a></p>
                        </div>
                        
                        <div style="margin-top: 30px; font-size:16px;">
                            <p>Best regards,</p>
                            <p>The MallShark Team</p>
                        </div>
                    
                        <div class="mail-footer" style="max-width: 500px;  margin: 60px auto 0">
                            <center>
                            <div style="margin-bottom: 30px;">
                                <strong style="font-size: 18px;"> <a href="{{ env('FRONT_END_URL') }}/faqs/general"
                                        style="color: #183B56; text-decoration: none;">FAQs</a> | <a href="{{ env('FRONT_END_URL') }}/contact-us"
                                        style="color: #183B56; text-decoration: none;">Contact Us</a> | <a href="{{ env('FRONT_END_URL') }}/return-policy"
                                            style="color: #183B56; text-decoration: none;">Return Policy</a></strong>
                            </div>
                            <div class="logo-section">
                                <a href="{{ env('FRONT_END_URL') }}" style="text-align: center;display: block;">
                                    <img src="https://mallshark.com/assets/images/MallShark-Beta-new.png" alt="Mallshark logo"
                                        title="Mallshark logo">
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
                                    <a target="_blank" style="
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
                                    " href="https://twitter.com/ShopMallShark" class="icon t-icon"><i
                                            class="fa fa-twitter"></i></a>
                                    <img src="" alt="">
                                    <a target="_blank" style="
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
                                    " href="https://www.instagram.com/shopmallshark/" class="icon ins-icon">
                                        <i class="fa fa fa-instagram"></i>
                                    </a>
                                    <a target="_blank" style="
                                    width: 42px;
                                    height: 42px;
                                    border-radius: 50%;
                                    text-decoration: none;
                                    display: inline-block;
                                    vertical-align: bottom;
                                    " href="https://discord.gg/hW9DZapKyH" class="icon dis-icon">
                                        <img src="https://mallshark.com/assets/images/discord.svg"
                                            style="width: 100%;"></a>
                                </div>

                                <div style="font-size:12px; margin: 10px 0"> Copyright © {{date('Y')}}
                                    <a href="https://mallshark.com"
                                        style="color: #073ba5; text-decoration: none;">Mallshark.com,</a> Inc. All
                                    Rights Reserved.<p></p>
                                </div>
                            </center>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>


<?php  //dd('called');  ?>
</html>