<?php
if (count($ordersdata) > 0) {
    $counter = 0;
    echo '<div class="container">';
// echo '$ordersdata: <pre>' .print_r($ordersdata,true). '</pre>'; die();
    foreach ($ordersdata as $order) {
        // echo '$order: <pre>' .print_r($order,true). '</pre>'; die;
        if ($counter == 0) {
            $order_id = $order->order_id;
            $user_id = $order->buyer_id;
            $order_status = $order->order_status;
            $date = $order->created_at;
            $shipping_address = $order->shipping_address;
            $total_amount = $order->total_amount;
            $user_info = getUserById($user_id);
            $product = getProductById($order->item_id);
            $payment_method = $order->payment_method;
            

            //$country_name = $country_name[0]->country_name;
             $country_code = $order->country;
                                    if ($country_code == 2) {
                                        $country_name = 'CANADA';
                                    } else {
                                        $country_name = 'USA';
                                    }
           
            $state_name = DB::table('states')->where('state_id', '=', $order->state)->where('country_id', '=', $order->country)->select('state_name')->get();
                
            $state_name = $state_name[0]->state_name;
           $name = $order->full_name;
            $shipping_amount = $order->shipping_amount;
            $shipping_address = $order->street . ' ' .$order->flat_other . ' '.$order->city . ' ' . $state_name . ' ' . $country_name.' '.$order->post_code;
            $storeLogoId = $order->store_logo;
            $imgSource='';
            if (!empty($storeLogoId)) {
                $imageUrl = getAttachmentUrlById((int) $storeLogoId, 'avatar');
                if(isset($imageUrl) && !empty($imageUrl)){
                $parts = explode("/", $imageUrl);
                $imageName = end($parts);
                $imgSource = public_path() . '/uploads/avatar/' . $imageName;
                 }else{
                    $imageName = 'logo.png';
                     $imgSource = public_path() . '/assets/images/' . $imageName;
                 }
                if (file_exists($imgSource)) {
                    
                }
            }
            ?>
            <div class="row">
                <div class="col-xs-12">
                    <div class="invoice-title">
                        <img style="width:150px; margin-left:12%;" src="<?php echo $imgSource; ?>" alt="invoice">
                        <h2 style="float:left;width:30%; font-family: 'Roboto', sans-serif;
                            color: #2f2f2f;">Invoice</h2><h3 class="pull-right" style="float:right">Order # {{$order_id}}</h3>
                    </div>
                    <div class="clear" style="clear:both"></div>
                    <hr>
                    <div class="row">

                        <div class="col-xs-12 text-right">
                            <address style="font-family: 'Roboto', sans-serif;
                                     color: #2f2f2f;">
                                <strong>Shipped To:</strong>
                                <br />
                                {{$name}}
                                <br />
                                {{$shipping_address}}
                            </address>
                        </div>
                    </div>
                    <div class="row">
                        <br />
                        <div class="col-xs-6">
                            <address style="font-family: 'Roboto', sans-serif;
                                     color: #2f2f2f;">
                                <strong>Payment Method:</strong><br>
                                {{$payment_method}}
                            </address>
                        </div>
                        <div class="col-xs-6 text-right">
                            <br />
                            <address style="font-family: 'Roboto', sans-serif;
                                     color: #2f2f2f;">
                                <strong>Date Ordered:</strong><br>
                                {{ date('F d, Y', strtotime($date)) }}<br><br>
                            </address>
                        </div>

                        <div class="col-xs-6 text-right">
                            <address style="font-family: 'Roboto', sans-serif;
                                     color: #2f2f2f;">
                                <strong style="font-family: 'Roboto', sans-serif;
                                        color: #2f2f2f;"> Cost of shipping: </strong><br>
                                        <span class="zit-price">USDC {{ number_format($shipping_amount,2) }}</span><br><br>
                            </address>
                        </div>
                        <div class="col-xs-6 text-right">
                            <address style="font-family: 'Roboto', sans-serif;
                                     color: #2f2f2f;">
                                <strong style="font-family: 'Roboto', sans-serif;
                                        color: #2f2f2f;"> Tax: </strong><br>
                                        <span class="zit-price">USDC {{ number_format($order->store_tax,2) }}</span><br><br>
                            </address>
                        </div>
                        <div class="col-xs-6 text-right">
                            <address style="font-family: 'Roboto', sans-serif;
                                     color: #2f2f2f;">
                                <strong>Grand total:</strong><br>
                                <span class="zit-price">USDC {{ number_format($total_amount,2) }}</span><br><br>
                            </address>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <h3 class="panel-title" style="font-family: 'Roboto', sans-serif;
                                color: #2f2f2f;"><strong>Order summary</strong></h3>
                        </div>
                        <div class="panel-body">
                            <div class="table-responsive" style="width:100%;border: 2px solid #000;padding-bottom:0px;">
                                <table class="table table-condensed" style="width:100%;">
                                    <thead style="font-family: 'Roboto', sans-serif;
                                           color: #2f2f2f;">
                                        <tr>
                                            <td style="padding:10px;"><strong>Item</strong></td>
                                            <td style="padding:10px;" class="text-center"><strong>Price</strong></td>
                                            <td style="padding:10px;" class="text-center"><strong>Quantity</strong></td>
                                            <td style="padding:10px;" class="text-right"><strong>Note</strong></td>
                                        </tr>
                                    </thead>
                                    <?php
                                }
                                $product = getProductById($order->item_id);
                                
                                $product_title = isset($product[0]->title) ? $product[0]->title:'';
                                $product_quantity = $order->quantity;
                                // $price = $order->price;
                                $order_total_price = isset($order->order_total) ? $order->order_total : "";
                                $image = isset($product[0]->feature_image)? $product[0]->feature_image:'';
                                $order_note = $order->order_note;
                                $image_url = getAttachmentUrlById($image, 'small');
                                ?>
                                <tr style="width:100%; font-family: 'Roboto', sans-serif;
                                    color: #2f2f2f;">
                                    <td style="width:45%;">{{$product_title}}</td>
                                    <td style="width:20%;" class="text-center"> <span class="zit-price">USDC {{$order_total_price}}</span></td>
                                    <td style="width:15%;"class="text-center">{{$product_quantity}}</td>
                                    <td style="width:20%;" class="text-right">{{$order_note}}</td>
                                </tr>


                                <?php
                                $counter++;
                            }
                           
                            ?>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>   

    <?php
} else {
    echo '<h5>No Records Found</h5>';
}
?>
<style>
    table{border-collapse: collapse;}
    .invoice-title h2, .invoice-title h3 {
        display: inline-block;
    }

    .table > tbody > tr > .no-line {
        /* border-top: none; */
    }

    .table > thead > tr > .no-line {
        /* border-bottom: none; */
    }

    .table > tbody > tr > .thick-line {
        border-top: 2px solid;
    }
    table {
        border-collapse: collapse;
    }

    th, td {
        border: 1px solid #000;
    }
    
     table td, table th,table tbody td {
        padding: .75rem;
        vertical-align: top;
        border-bottom: 1px solid #000;
        }
    table thead th{
        border-bottom: 2px solid #000 !important;
    }
</style>