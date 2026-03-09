<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
</head>
<style>
    .dnone {
        display: none;
    }

    .ml0 {
        margin-left: 0px
    }

    .ml67 {
        margin-left: 85px;
    }
</style>

<body data-rsssl=1 style="font-family:'Helvetica Neue',Helvetica,Arial,sans-serif;
                text-align:center;">
    <?php
    if (count($ordersdata) > 0) {
        // dd($ordersdata);
        foreach ($ordersdata as $orders) {
            $createdTimestamp = strtotime($orders->created_at);
            $formattedCreatedAt = date('d M Y', $createdTimestamp);

            // Store Logo
            $storeLogoId = $orders['store']->store_logo;
            $imgSource = '';
            if (!empty($storeLogoId)) {
                $imageUrl = getAttachmentUrlById((int) $storeLogoId, 'small');
                $imgSource = $imageUrl ?: '';
            }

            // State
            $state_name = '';
            if (isset($orders['shippingAddress']->state)) {
                $state = getStatebyId($orders['shippingAddress']->state);
                $state_name = $state['state_name'];
            }
            // Country
            $country_name = '';
            if (isset($orders['shippingAddress']->country)) {
                $country = getCountrybyId($orders['shippingAddress']->country);
                $country_name = $country['name'];
            }
    ?>
            <!-- Header -->
            <h1 style="font-family:'Helvetica Neue',Helvetica,Arial,sans-serif;
                text-align:center;font-weight:bold;">Invoice</h1>

            <!-- Store + MallShark Info -->
            <table style="width:100%;border-top:2px solid #000;margin-top:10px;">
                <tr>
                    <td style="vertical-align:middle;">
                        <div style="display:flex;align-items:center;gap:10px;">
                            <?php if (!empty($imgSource)) { ?>
                                <img style="width:60px;margin-top:20px;" src="<?php echo $imgSource; ?>" alt="Store Logo">
                                <span style="font-size:22px;font-weight:bold;vertical-align:middle;">
                                    <?php echo $orders['store']->store_name; ?>
                                </span>
                            <?php } ?>
                        </div>
                        <p style="margin:5px 0 0 0;">MallShark</p>
                        <a href="mailto:care@mallshark.com" style="text-decoration:none;">
                            care@mallshark.com
                        </a>
                    </td>
                    <td style="text-align:right;vertical-align:top;padding:10px;white-space:nowrap;">
                        <div style="display:flex;flex-direction:column;gap:5px;">
                            <p style="margin:0;text-align:right;"><strong>Order #:</strong> <?php echo $orders->order_id; ?></p>
                            <p style="margin:0;text-align:right;"><strong>Order Date:</strong> <?php echo $formattedCreatedAt; ?></p>
                        </div>
                    </td>
                </tr>
            </table>

            <!-- Shipping Address -->
            <table style="width:100%;margin-top:0px;">
                <tr>
                    <td style="vertical-align:start;">
                        <div style="display:flex;align-items:center;">
                            <p class="mb-0" style="margin-bottom:0px;">
                                <strong>Name:</strong><br>
                                <?php echo $orders['shippingAddress']->full_name ?? ''; ?>
                            </p>
                        </div>
                    </td>
                    <td style="text-align:right;vertical-align:top;padding:10px;white-space:nowrap;">
                        <div style="display:flex;flex-direction:column;gap:5px;">
                            <div style="margin:0;text-align:right;">
                                <p style="margin-bottom:0px;">
                                    <strong>Shipping Address:</strong><br>
                                    <?php echo $orders['shippingAddress']->apartment_no ?? ''; ?><br>
                                    <?php echo $orders['shippingAddress']->street ?? ''; ?><br>
                                    <?php echo ($orders['shippingAddress']->city ?? '') . ', ' . ($state_name ?? '') . ' ' . ($orders['shippingAddress']->post_code ?? ''); ?><br>
                                    <?php echo ($country_name ?? ''); ?><br>
                                </p>
                            </div>
                        </div>
                    </td>
                </tr>
            </table>

            <!-- Products -->
            <table style="width:100%;border-collapse:collapse;border:1px solid #9b9a9aff;">
                <thead>
                    <tr style="background:#00A499;color:#fff;">
                        <th style="padding:8px;border:1px solid #9b9a9aff;">#</th>
                        <th style="padding:8px;border:1px solid #9b9a9aff;">Product</th>
                        <th style="padding:8px;border:1px solid #9b9a9aff;">Variation</th>
                        <th style="padding:8px;text-align:center;border:1px solid #9b9a9aff;">Unit Price</th>
                        <th style="padding:8px;text-align:center;border:1px solid #9b9a9aff;">Qty</th>
                        <th style="padding:8px;text-align:center;border:1px solid #9b9a9aff;">Custom Price</th>
                        <th style="padding:8px;text-align:center;border:1px solid #9b9a9aff;">Shipping</th>
                        <th style="padding:8px;text-align:center;border:1px solid #9b9a9aff;">Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $counter = 0;
                    $total_shipping = 0;
                    $total_amount = 0;

                    foreach ($orders['orderItems'] as $detail) {
                        $counter++;
                        $order_price = $detail->sold_price > 0 ? $detail->sold_price : $detail->price;
                        $shipping_amount = $detail->shipping_amount ?? 0;
                        $product_total = $order_price * $detail->quantity;
                        $customization_price = !empty($detail->customizations) ? json_decode($detail->customizations, true)['price'] ?? 0 : 0;
                        $product_total += $customization_price * $detail->quantity;
                        $total_shipping += $shipping_amount;
                        $total_amount += $product_total;
                    ?>
                        <tr>
                            <td style="padding:8px;border:1px solid #9b9a9aff;font-size:16px;"><?php echo $counter; ?></td>
                            <td style="padding:8px;border:1px solid #9b9a9aff;font-size:16px;">
                                <?php echo $detail->product_name ?? ''; ?>
                            </td>
                            <td style="padding:8px;border:1px solid #9b9a9aff;font-size:16px;">
                                <?php if (!empty($detail->variation_info)) : ?>
                                <?php echo $detail->variation_info; ?>
                                <?php endif; ?>
                            </td>
                            @php
                                $custom = !empty($detail->customizations) ? json_decode($detail->customizations, true) : [];
                            @endphp
                            <td style="padding:8px;text-align:center;border:1px solid #9b9a9aff;font-size:16px;">$<?php echo number_format($order_price, 2); ?></td>
                            <td style="padding:8px;text-align:center;border:1px solid #9b9a9aff;font-size:16px;"><?php echo $detail->quantity; ?></td>                         
                            <td style="padding:8px;text-align:center;border:1px solid #9b9a9aff;font-size:16px;">{{ $custom['price'] ?? ''}}</td>
                            <td style="padding:8px;text-align:center;border:1px solid #9b9a9aff;font-size:16px;">$<?php echo number_format($shipping_amount, 2); ?></td>
                            <td style="padding:8px;text-align:center;border:1px solid #9b9a9aff;font-size:16px;">$<?php echo number_format($product_total + $shipping_amount, 2); ?></td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>


            <!-- Totals -->
            <div style="text-align:right;margin-top:15px;">
                <p>Shipping Fee: $<?php echo number_format($total_shipping, 2); ?></p>
                <p><strong>Price: $<?php echo number_format($total_amount, 2); ?></strong></p>
                <p><strong>Total: $<?php echo number_format($total_amount + $total_shipping, 2); ?></strong></p>
            </div>

            <!-- Footer -->
            <p style="font-size:10pt;text-align:left;font-style:italic;margin-top:20px;vertical-align:bottom;">
                Note: Non-perishable items can be refunded within 15 days from the date of shipping.
            </p>

    <?php
        } // end foreach
    } else {
        echo '<h5>No Records Found</h5>';
    }
    ?>
</body>

</html>