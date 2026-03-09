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
                            <?php 
                                foreach($productDetails as $key => $value) {
                                    if (is_array($value)) {
                                        $product_variation_image = !empty($value['product_variation_image']) ? $value['product_variation_image'] : '';
                                        $product_type = $value['product_type'];
                                        $image = $value['feature_image'];
                        
                                        $image_url = '';
                                        if (!empty($image)) {
                                            if ($product_type == 'simple' || $product_type == 'virtual') {
                                                $image_url = getAttachmentUrlByIdEmailTemplete((int) $image, 'medium');
                                            } else {
                                                $image_url = getAttachmentUrlByIdEmailTemplete((int) $product_variation_image, 'medium');
                                            }
                                            $image_url = str_replace([' ', '(', ')'], ['%20', '%28', '%29'], $image_url);
                                        }
                                        $url = env('base_url');
                                        ?>
                                        
                                    <?php 
                                    }
                                }
                            ?>
                           
 

    <!--  New Email Template Section  -->

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
                                            <div>
                                            <img src={{URL::to('/assets/images/Mall-Shark-Logo.png')}} alt="Mallshark logo"
                                            title="Mallshark logo" width="200px" style="border:none;display:inline-block;font-size:14px;margin-bottom:10px;font-weight:bold;height:auto;outline:none;text-decoration:none;text-transform:capitalize;vertical-align:middle;max-width:100%;margin-left:0;margin-right:0" border="0">
                                            </div>
                                            <table border="0" cellpadding="0" cellspacing="0" width="100%">
                                                <tbody>
                                                    <tr>
                                                        <td>
                                                            <table border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color:#3c98cc;color:#fff;border-bottom:0;font-weight:bold;line-height:100%;vertical-align:middle;font-family:'Helvetica Neue',Helvetica,Roboto,Arial,sans-serif;border-radius:3px 3px 0 0;" bgcolor="#3c98cc">
                                                                <tbody>
                                                                    <tr>
                                                                        <td style="padding:36px 48px;display:block;">
                                                                            <h1 style="font-family:Helvetica Neue,Helvetica,Roboto,Arial,sans-serif;font-size:30px;font-weight:300;line-height:150%;margin:0;text-align:left;color:#fff;">Order Received</h1>
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
                                                                                                <p style="margin:0 0 16px;">Hi {{$storeName}},</p>
                                                                                                <p style="margin:0 0 16px;">You’ve received an order from a customer on the MallShark platform. Below are the details for the order and customer:</p>
                                                                                                <h2 style="color:#3c98cc;font-size:18px;font-weight:bold;line-height:130%;margin:0 0 18px;text-align:left;">[Order #{{$order_id}}] ({{ \Carbon\Carbon::now()->format('m/d/Y') }})</h2>
                                                                                                <div style="margin-bottom:20px;">
                                                                                                    <?php
                                                                                                        // Check if any product has customizations
                                                                                                        $hasCustomizations = false;
                                                                                                        foreach($productDetails->orderItems as $item) {
                                                                                                                $customizations = $item['customizations'];
                                                                                                                if (is_string($customizations)) {
                                                                                                                    $customizations = json_decode($customizations, true);
                                                                                                                }
                                                                                                                if(!empty($customizations) && is_array($customizations) && isset($customizations['price']) && $customizations['price'] > 0) {
                                                                                                                    $hasCustomizations = true;
                                                                                                                    break;
                                                                                                                }
                                                                                                            }
                                                                                                    ?>
                                                                                                    <table cellspacing="0" cellpadding="6" border="1" style="color:#636363;border:1px solid #e5e5e5;vertical-align:middle;width:100%;font-family:'Helvetica Neue',Helvetica,Roboto,Arial,sans-serif;">
                                                                                                        <thead>
                                                                                                            <tr>
                                                                                                                <th style="padding:12px;text-align:left;border:1px solid #e5e5e5;">Product</th>
                                                                                                                <th style="padding:12px;text-align:left;border:1px solid #e5e5e5;">Quantity</th>
                                                                                                                <th style="padding:12px;text-align:left;border:1px solid #e5e5e5;">Unit Price</th>
                                                                                                                @if($hasCustomizations)
                                                                                                                <th style="padding:12px;text-align:left;border:1px solid #e5e5e5;">Personalisation Price</th>
                                                                                                                @endif
                                                                                                            </tr>
                                                                                                        </thead>
                                                                                                        <tbody>
                                                                                                        <?php 
                                                                                                            $grandTotal = 0;
                                                                                                            $shippingPriceTotal = 0;
                                                                                                            ?>
                                                                                                            <?php 
                                                                                                                 foreach($productDetails->orderItems as $key => $value) {
                                                                                                                    // Decode customizations if it's a JSON string
                                                                                                                    $customizations = $value['customizations'];
                                                                                                                    if (is_string($customizations)) {
                                                                                                                        $customizations = json_decode($customizations, true);
                                                                                                                    }
                                                                                                                    if(!empty($customizations) && is_array($customizations))
                                                                                                                    {
                                                                                                                        $type = $customizations['type'] ?? '';
                                                                                                                        $instructions = $customizations['instructions'] ?? '';
                                                                                                                        $style = $customizations['styles'] ?? '';
                                                                                                                        $customText = $customizations['custom_text'] ?? '';
                                                                                                                        $imageUrl = $customizations['image_url'] ?? '';
                                                                                                                        $customPrice = $customizations['price'] ?? '';
                                                                                                                        $customQuantity = $customizations['product_quantity'] ?? '';
                                                                                                                        // Ensure all values are strings
                                                                                                                        $type = is_array($type) ? json_encode($type) : (string)$type;
                                                                                                                        $instructions = is_array($instructions) ? implode(', ', $instructions) : (string)$instructions;
                                                                                                                        $style = is_array($style) ? implode(', ', $style) : (string)$style;
                                                                                                                        $customText = is_array($customText) ? implode(', ', $customText) : (string)$customText;
                                                                                                                        $imageUrl = is_array($imageUrl) ? ($imageUrl[0] ?? '') : (string)$imageUrl;
                                                                                                                    }else{
                                                                                                                        $type = '';
                                                                                                                        $instructions = '';
                                                                                                                        $style = '';
                                                                                                                        $customText = '';
                                                                                                                        $imageUrl = '';
                                                                                                                        $customPrice = 0;
                                                                                                                        $customQuantity = 1;
                                                                                                                    }
                                                                                                                        $itemImage = !empty($value['item_image']) ? $value['item_image'] : '';
                                                                                                                        if(!is_null($value['variation_info']) && $value['variation_id'] > 0){
                                                                                                                            $product_type = 'variant';
                                                                                                                        }else{
                                                                                                                            $product_type = 'simple';
                                                                                                                        }
                                                                                                                        $image =  $value['feature_image'];
                                                                                                                        $variations = array();
                                                                                                                        if ($product_type == 'variant' && !empty($value['variation_id'])) {
                                                                                                                            $variation = getvariableByProduct_id($value['variation_id']);
                                                                                                                                if(isset($variation->option1) && !is_null($variation->option1)){
                                                                                                                                    $variation1 = ['attribute' => $variation->attribute1,'option'=>$variation->option1];
                                                                                                                                    $variations[]=$variation1;  
                                                                                                                                }if(isset($variation->option2) && !is_null($variation->option2)){
                                                                                                                                    $variation2 = ['attribute' => $variation->attribute2,'option'=>$variation->option2];
                                                                                                                                    $variations[]=$variation2; 
                                                                                                                                }if(isset($variation->option3) && !is_null($variation->option3)){
                                                                                                                                    $variation3 = ['attribute' => $variation->attribute3,'option'=>$variation->option3];
                                                                                                                                    $variations[]=$variation3; 
                                                                                                                                }
                                                                                                                                
                                                                                                                        }
                                                                                                                        $image_url = '';
                                                                                                                        if (!empty($image)) {
                                                                                                                            
                                                                                                                                $image_url = getAttachmentUrlByIdEmailTemplete($image, 'medium');
                                                                                                                           
                                                                                                                            $image_url = str_replace([' ', '(', ')'], ['%20', '%28', '%29'], $image_url);
                                                                                                                        } 
                                                                                                                        $url = env('base_url');
                                                                                                                ?>
                                                                                                                <tr>
                                                                                                                    <td style="padding:12px;text-align:left;border:1px solid #e5e5e5;">{{ isset($value['product_name']) ? $value['product_name']: "" }}
                                                                                                                    <br>  
                                                                                                                       @if($product_type == 'variant')
                                                                                                                            <small>{{ is_array($value['variation_info']) ? implode(', ', $value['variation_info']) : $value['variation_info'] }}</small>
                                                                                                                            <br>
                                                                                                                        @endif
                                                                                                                        @if(!empty($type) && $type == 'text')
                                                                                                                            <strong>Customization Details:</strong><br>
                                                                                                                            Style: {{$style}}<br>
                                                                                                                            Custom Text: {{$customText}}<br>
                                                                                                                        @endif
                                                                                                                        @if(!empty($type) && $type == 'image')
                                                                                                                        @php 
                                                                                                                                $imageUrl = getOrderImage($imageUrl);
                                                                                                                            @endphp
                                                                                                                            <img src="{{ $imageUrl }}" alt="Customization Image" width="100" height="100">
                                                                                                                        @endif
                                                                                                                        @if(!empty($instructions))
                                                                                                                            Instructions: {{$instructions}}<br>
                                                                                                                        @endif
                                                                                                                    </td>
                                                                                                                    <td style="padding:12px;text-align:left;border:1px solid #e5e5e5;">
                                                                                                                        {{ isset($value['quantity']) ? $value['quantity']: "" }}
                                                                                                                    </td>
                                                                                                                    <td style="padding:12px;text-align:left;border:1px solid #e5e5e5;">
                                                                                                                        @php
                                                                                                                            if ($product_type != 'virtual') {
                                                                                                                                if ($value['sold_price'] > 0) {
                                                                                                                                    $item_price = $value['sold_price'];
                                                                                                                                } else {
                                                                                                                                    $item_price = $value['price'];
                                                                                                                                }
                                                                                                                            }else {
                                                                                                                                $item_price = isset($value['price']) ? $value['price'] : $value['price'];
                                                                                                                            }

                                                                                                                            $quantity = isset($value['quantity']) ? $value['quantity'] : 1;
                                                                                                                            $shippingPrice = isset($value['shipping_amount']) ? $value['shipping_amount'] : 0;
                                                                                                                            $total_item_price = $item_price * $quantity;
                                                                                                                            $custom_total_price = $customPrice * $customQuantity;
                                                                                                                            $grandTotal += $total_item_price + $custom_total_price;
                                                                                                                            $shippingPriceTotal += $shippingPrice;

                                                                                                                        @endphp
                                                                                                                        ${{ $item_price }}
                                                                                                                    </td>
                                                                                                                    @if($hasCustomizations)
                                                                                                                     <td style="padding:12px;text-align:left;border:1px solid #e5e5e5;">
                                                                                                                        ${{ number_format($customPrice, 2) }} x {{ $customQuantity }} = ${{ number_format($customPrice * $customQuantity, 2) }}
                                                                                                                    </td>
                                                                                                                    @endif
                                                                                                                </tr>
                                                                                                            <?php }  ?>
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
                                                                                                            <!-- <tr>
                                                                                                                <th scope="row" colspan="2" style="color:#636363;border:1px solid #e5e5e5;vertical-align:middle;padding:12px;text-align:left" align="left">Payment method:</th>
                                                                                                                <td style="color:#636363;border:1px solid #e5e5e5;vertical-align:middle;padding:12px;text-align:left" align="left">Check payments</td>
                                                                                                            </tr> -->
                                                                                                            <tr>
                                                                                                                <th scope="row" colspan="2" style="color:#636363;border:1px solid #e5e5e5;vertical-align:middle;padding:12px;text-align:left" align="left">Total:</th>
                                                                                                                <td style="color:#636363;border:1px solid #e5e5e5;vertical-align:middle;padding:12px;text-align:left" align="left"><span><span>$</span>{{ number_format($grandTotal +  $shippingPriceTotal, 2) }}</span></td>
                                                                                                            </tr>
                                                                                                        </tfoot>
                                                                                                    </table>
                                                                                                </div>
                                                                                                <table width="100%" cellspacing="0" style="margin-bottom:40px;">
                                                                                                    <tbody>
                                                                                                        <tr style="vertical-align:baseline;">
                                                                                                            <td style="text-align:left;">
                                                                                                                <h2 style="color:#3c98cc;font-size:18px;font-weight:bold;margin-bottom:20px;">Shipping address</h2>
                                                                                                                <address style="padding:12px;color:#636363;border:1px solid #e5e5e5;">
                                                                                                                    {{$name}}<br>
                                                                                                                    {{$apartmentNo.' '.$street. ' '.$city.' '.$state}}<br>
                                                                                                                    {{$country}}, {{$postalcode}}
                                                                                                                </address>
                                                                                                            </td>
                                                                                                        </tr>
                                                                                                    </tbody>
                                                                                                </table>
                                                                                                <p style="margin:0;">Thanks for using <a href="https://www.mallshark.com" style="color:#3c98cc;">www.mallshark.com</a>!</p>
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
                                                                                                <div style="margin-bottom:10px"><img src={{URL::to('/assets/images/Mall-Shark-Logo.png')}} alt="Mallshark logo" width="150" title="Mallshark logo" style="border:none;display:inline-block;font-size:14px;font-weight:bold;height:auto;outline:none;text-decoration:none;text-transform:capitalize;vertical-align:middle;margin-right:10px;max-width:100%" border="0" class="CToWUd" data-bit="iit"></div>
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
                    </td>
                    <td></td>
                </tr>
            </tbody>
        </table>
    </div>


</body>

</html>