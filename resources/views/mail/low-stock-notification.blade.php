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
    <div class="mail-container" style="padding:1.5rem 0;">
        <div class="mail-body">
            <div style="border: 1px solid rgb(179, 179, 179); 
            border-radius: 10px;
            overflow: hidden;">
                <div class="logo-section" style="background: #fff; padding: 26px 22px;">
                    <a href="{{ env('FRONT_END_URL') }}" target="_blank" style="text-align: center;display: block;">
                        <img src="{{ URL::to('/assets/images/Mall-Shark-Logo.png') }}"
                            alt="MallShark logo"
                            title="Mallshark logo" width="130px">
                    </a>

                    <div class="subject" style="font-size:20px; margin-top: 20px; font-weight:bold;">
                        Low Stock Alert – Please Restock Soon
                    </div>

                    <div style="margin-top: 30px;">
                        <strong style="font-size: 17px;">Dear {{ $storeOwner['name'] }},</strong>
                        @if(!empty($storeData['store_name']))
                        <p style="margin: 8px 0; font-size: 16px; color:#555;">
                            Store: <strong>{{ $storeData['store_name'] }}</strong>
                        </p>
                        @endif
                        <p style="margin: 20px 0; font-size: 16px;">
                            We’ve detected that some of your products are running low on stock.
                            To avoid missing out on potential sales, please consider restocking them promptly.
                        </p>
                    </div>

                    <div class="low-stock-detail">
                        <div>
                            <strong style="font-size:16px;">Low Stock Products:</strong>
                        </div>
                        <table style="border-spacing: 0; width: 100%; border-collapse: collapse; margin-top:10px;">
                            <thead>
                                <tr style="background-color: #f5f5f5; border: 1px solid #e0e0e0;">
                                    <th style="padding: 10px; text-align: left; font-size: 16px; border-bottom: 2px solid #e0e0e0;">Product Name</th>
                                    <th style="padding: 10px; text-align: left; font-size: 16px; border-bottom: 2px solid #e0e0e0;">Image</th>
                                    <th style="padding: 10px; text-align: center; font-size: 16px; border-bottom: 2px solid #e0e0e0;">Current Stock</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($lowStockItems as $item)
                                @php
                                $variationParts = [];
                                if (!empty($item['variation']['option1'])) $variationParts[] = $item['variation']['attribute1'] . ': ' . $item['variation']['option1'];
                                if (!empty($item['variation']['option2'])) $variationParts[] = $item['variation']['attribute2'] . ': ' . $item['variation']['option2'];
                                if (!empty($item['variation']['option3'])) $variationParts[] = $item['variation']['attribute3'] . ': ' . $item['variation']['option3'];
                                
                                $imageUrl = $item['variation']['product_variation_image'] ?? $item['product']['feature_image'];
                                @endphp

                                <tr style="border-bottom: 1px solid #e0e0e0;">
                                    <td style="padding: 10px; text-align: left; font-size: 16px;">
                                        <a href="{{ env('FRONT_END_URL') }}/product/{{ $item['product']['slug'] }}" style="color: #073ba5; text-decoration: none;">
                                            {{ $item['product']['title'] }}
                                        </a>
                                        @if(count($variationParts))
                                        <br>
                                        <small style="color: #555; display: block;">
                                            {!! implode('<br>', $variationParts) !!}
                                        </small>
                                        @endif
                                    </td>

                                    <td style="text-align:left; padding: 10px;">
                                        <img src="{{ $imageUrl }}" 
                                            alt="{{ $item['product']['title'] }}" 
                                            style="width: 80px; height: 80px; object-fit: contain; border: 1px solid #ddd; border-radius: 6px;">
                                    </td>
                                    {{-- Stock --}}
                                    <td style="padding: 10px; text-align: center; font-size: 16px;">
                                        {{ $item['variation']['current_stock'] ?? $item['product']['current_stock'] ?? '-' }}
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div style="margin-top: 30px; font-size:16px;">
                        <p>Thank you,</p>
                        <p>The MallShark Team</p>
                    </div>

                    <div class="mail-footer" style="max-width: 500px;  margin: 60px auto 0">
                        <center>
                            <div style="margin-bottom: 30px;">
                                <strong style="font-size: 18px;">
                                    <a href="{{ env('FRONT_END_URL') }}/faqs/general" style="color: #183B56; text-decoration: none;">FAQs</a> |
                                    <a href="{{ env('FRONT_END_URL') }}/contact-us" style="color: #183B56; text-decoration: none;">Contact Us</a>
                                </strong>
                            </div>
                            <div class="logo-section">
                                <a href="{{ env('FRONT_END_URL') }}" style="text-align: center;display: block;">
                                    <img src="https://mallshark.com/assets/images/MallShark-Beta-new.png" alt="Mallshark logo" title="Mallshark logo">
                                </a>
                            </div>
                            <div style="font-size:12px; margin: 10px 0">
                                Copyright © {{date('Y')}}
                                <a href="https://mallshark.com" style="color: #073ba5; text-decoration: none;">Mallshark.com</a>, Inc. All Rights Reserved.
                            </div>
                        </center>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>


</html>