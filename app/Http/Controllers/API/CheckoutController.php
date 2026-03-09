<?php

namespace App\Http\Controllers\API;

use DB;
use PayPal;
use Session;
use Redirect;
use Stripe\Stripe;
use Stripe\Customer;
use Illuminate\Support\Carbon;
use App\Models\User;
use App\Models\Store;
use App\Models\Userrole;
use Illuminate\Http\Request;
use App\Http\Controllers\UspsT;
use App\Models\{ShippingAddress,Product,StoreOrder,OrderItems,Discount,DiscountUsage};
use Illuminate\Support\Facades\URL;
use Illuminate\Database\Eloquent\Model;
use App\Repositories\Checkout\Checkoutaccess;
use App\Repositories\Location\LocationAccess;
use App\Repositories\Orders\StoreOrdersAccess;
use App\Http\Controllers\Controller;
class CheckoutController extends Controller
{

    private $_checkoutobj;
    private $_esearch;

    public function __construct(Request $request)
    {
        $this->_checkoutobj = new Checkoutaccess();
        $this->_esearch = new ElasticSearchController();

    }

    public function getCheckout(Request $request)
    {   
        \Stripe\Stripe::setApiKey(env('STRIPE_API_KEY'));
        $postedData = $request->all();
        $result = $this->_checkoutobj->cartDetailApi($postedData);
        if(!$postedData['guest']){
            $userId = $postedData['id'];
        }else{
            $userId = null;
        }
        if(isset($postedData['cart_id'])){
            $cartId = $postedData['cart_id'];
        }else{
            $cartId = '';
        }
        $user = User::select('user_id','name','email','customer_id')->where('user_id',$userId)->first();
        if(!is_null($user)){
            if (!$user->customer_id) {
        
                // Create a new Stripe Customer
                $customer = Customer::create([
                    'email' => $user->email,
                    'name' => $user->name ?? '',
                ]);
            
                // Store the Stripe customer ID in the database
                $user->customer_id = $customer->id;
                $user->save();
                $customerId = $customer->id;
            } else {
                // Retrieve the existing customer ID
                $customer = Customer::retrieve($user->customer_id);
                $customerId = $customer->id;
            }
        }else{
            $customerId = null;
        }
        
        $storeIds = [];
        $productIds = [];
        $lineItems = [];
        $totalDiscount = 0;
        $totalShipping = 0; // Total shipping calculation
        $subtotal = 0; // Subtotal without discount
        $grandTotal = 0; // Total with discount and shipping
        $totalDue = 0; // Subtotal - Discount
        
       
        foreach ($result as $products) {
            foreach ($products as $product) {
                $discountAmount = 0;
                $salePrice = 0;
                if($product['product_type'] == 'variant'){
                    $regularPrice = isset($product['variation_sale_price']) ? $product['variation_sale_price'] : 0;
                    $ImageUrl = getAttachmentUrlById((int)$product['product_variation_image']);
                    
                   if(!$ImageUrl){
                    $ImageUrl = getAttachmentUrlById($product['feature_image']);
                   }
                    $shippingCost = isset($product['shipping_price']) ? $product['shipping_price'] : 0;
            
                    // Calculate discount per product
                    if($product['on_sale']){
                        $salePrice = isset($product['product_variation_sale_price']) ? $product['product_variation_sale_price'] : 0;
                        $discountAmount = max(0, $regularPrice - $salePrice);
                    }
                }else{
                    $regularPrice = isset($product['price']) ? $product['price'] : 0;
                    $ImageUrl = getAttachmentUrlById($product['feature_image']);
                    
                    $shippingCost = isset($product['shipping_price']) ? $product['shipping_price'] : 0;
            
                    // Calculate discount per product
                    if($product['on_sale']){
                        $salePrice = isset($product['sale_price']) ? $product['sale_price'] : 0;
                        $discountAmount = max(0, $regularPrice - $salePrice);
                    }
                }
                
                $totalDiscount += $discountAmount * $product['product_quantity'];
                 // Total discount
                $totalShipping += $shippingCost; // Total shipping
        
                // Convert values to cents
                $unitAmount = intval($regularPrice * 100);
                $shippingAmount = intval($shippingCost * 100);
        
                // Calculate subtotal
                $subtotal += $unitAmount * $product['product_quantity'];
                $ImageUrl = str_replace(' ', '%20', $ImageUrl);
                // Line Item for the Product
                $lineItems[] = [
                    'price_data' => [
                        'currency' => 'usd',
                        'product_data' => [
                            'name' => $salePrice != 0 ? "{$product['title']} (Was: \${$regularPrice}, Now: \${$salePrice})" : "{$product['title']}",
                            'images' => [$ImageUrl],
                            'description' => $product['on_sale'] ? "Discount(Coupon): \${$discountAmount}\nShipping: \${$shippingCost}" : "Shipping: \${$shippingCost}",
                        
                        ],
                        
                        'unit_amount' => $unitAmount,
                    ],
                    'quantity' => $product['product_quantity'],
                ];
                $storeIds[] = $product['store_id']; // Collect store IDs
                $productIds[] = $product['product_id'];
            }
        }
        // dd($lineItems);
        // Calculate the total amount due (Subtotal - Discount)
        $totalDue = max(0, $subtotal - $totalDiscount); // Ensure it's not negative
        
        // Grand Total (Including Shipping)
        $grandTotal = $totalDue + $totalShipping;
        // Add Shipping as a separate line item
        if ($totalShipping > 0) {
            $lineItems[] = [
                'price_data' => [
                    'currency' => 'usd',
                    'product_data' => ['name' => 'Shipping'],
                    'unit_amount' => intval($totalShipping * 100),
                ],
                'quantity' => 1,
            ];
        }
        
        // Add Discount as a separate line item (Negative Amount for Discount)
        if ($totalDiscount > 0) {
            $coupon = \Stripe\Coupon::create([
                'amount_off' => intval($totalDiscount * 100), // Convert to cents
                'currency' => 'usd',
                'duration' => 'once' // Apply only once per checkout
            ]);
        
            $discounts = [['coupon' => $coupon->id]];
        } else {
            $discounts = [];
        }
        // Create Stripe Checkout Session
        // dd($lineItems,implode(',', array_unique($storeIds)));
        if(isset($request['platform'])){
            if($request['platform'] == 'web'){
                
                $successUrl = env('STRIPE_SUCCESS_URL').'?id={CHECKOUT_SESSION_ID}';
                $cancelUrl = env('STRIPE_CANCEL_URL');
            }elseif($request['platform'] == 'mobile'){
               
                $successUrl = env('APP_URL') . '/stripe/mobile-success?id={CHECKOUT_SESSION_ID}';
                $cancelUrl = env('APP_URL') . '/stripe/mobile-cancel';
            }
        }
        $checkoutSession = \Stripe\Checkout\Session::create([
            'payment_method_types' => ['card','crypto'],
            'line_items' => $lineItems,
            'customer' => $customerId,
            'mode' => 'payment',
            'success_url' =>  $successUrl,
            'cancel_url' => $cancelUrl,
            'metadata' => [
                'total_discount' => number_format($totalDiscount, 2),
                'total_shipping' => number_format($totalShipping, 2),
                'subtotal' => number_format($subtotal, 2),
                'total_due' => number_format($subtotal - $totalDiscount, 2), // Final amount due
                'store_ids' => json_encode(array_values(array_unique($storeIds))), // ✅ Convert to JSON string
                'product_ids' => json_encode(array_values(array_unique($productIds))),
                'guest' => $postedData['guest'],
                'user_id' => $postedData['id'],
                'cart_id' => $cartId,
            ],
            'custom_text' => [
                'submit' => [
                    'message' => 'Note: Only USDC and USDP accepted for crypto payments.',
                ],
                'shipping_address' => [
                    'message' => '⚠️ IMPORTANT:Don’t forget to add your apartment/unit number in address line 1 if applicable.',
                ],
            ],
            
            'shipping_address_collection' => ['allowed_countries' => ['US']],
            'billing_address_collection' => 'required',
            'discounts' => $discounts, // ✅ Apply discount here
        ]);
           
            try{
                DB::transaction(function () use ($checkoutSession, $result) {
                    foreach ($result as $key => $storeData) {
                        try{

                   
                            $orderAmount = 0;
                            $shipping_amount = 0;
                            foreach ($storeData as $product) {
                                $sale_price = '';
                                    $quantity = $product['product_quantity'];
                                    if ($product['product_type'] == 'variant') {
                                        if (isset($product['on_sale']) && $product['on_sale'] == 1 ){
                                            $sale_price = isset($product['product_variation_sale_price']) ? $product['product_variation_sale_price'] : $product['sale_price'];
                                            $justVirtualProducts = false;
                                        }
                                            $item_price = isset($product['variation_sale_price']) ? $product['variation_sale_price'] : $product['price'];
                                            $justVirtualProducts = false;
                                    
                                    } else if ($product['product_type'] == 'simple') {
                                        $justVirtualProducts = false;
                                        if (isset($product['on_sale']) && $product['on_sale'] == 1 )
                                        {
                                            $sale_price = $product['sale_price'];
                                        }
                                        $item_price = isset($product['price']) ? $product['price'] : $product['price'];
                                    
                                    } else if ($product['product_type'] == 'virtual') {
                                        if (isset($product['on_sale']) && $product['on_sale'] == 1 )
                                        {
                                            $sale_price = $product['sale_price'];
                                        }
                                            $item_price = isset($product['price']) ? $product['price'] : $product['price'];
                                    
                                     }

                                    if (!is_numeric($quantity)) {
                                        $quantity = 1;
                                    }
                                    if(!empty($sale_price)){
                                        $item_price_condition = is_numeric($sale_price) ? $sale_price : 0; // Check if $item_price is numeric, if not, set it to 0
                                        $product_order_price = floatval($quantity * $item_price_condition);
                                    }else{
                                        $item_price_condition = is_numeric($item_price) ? $item_price : 0; // Check if $item_price is numeric, if not, set it to 0
                                        $product_order_price = floatval($quantity * $item_price_condition);
                                    }

                                    $shipping_method = isset($product['method']) ? $product['method'] : "";
                                    $store_id_pr = $product['store_id'];
                                    $store_p = getStoreInfoById((int) $store_id_pr);
                                    $shipping_charges = isset($product['shipping_price']) ? $product['shipping_price'] : 0;
                                    if ($shipping_charges == '') {
                                        $shipping_charges = 0;
                                    }
                                    $orderAmount += $shipping_charges + $product_order_price;
                                    $shipping_amount += $shipping_charges;
                            }
                            $stripe_fee = ($orderAmount * (2.9 / 100) + 0.33);
                            $platform_fee = $orderAmount * 0.045;
                            $total_fee = $stripe_fee +  $platform_fee;
                            $payable_amount = $orderAmount - $total_fee;
                            $platform_fee = $platform_fee;
                            $charge_status = 'unpaid';
                            $payable_amount = $payable_amount;
                            $paymentID = '';
                            $balanceTransaction = '';
                            $payerID = '';
                            $payment_status = 'pending';
                            $default_order_status = 'pending';
                            $justVirtualProducts = true; // using this to check if order as all virtual products then set order status directly
                            if ($key != 'products_price' && $key != 'shipping_price' && $key != 'total_price' && $key != 'total_tax') {
                                $this->store_order = new StoreOrder;
                                $this->discount_usage = new DiscountUsage;
                
                                $store_id = $key;
                                $storeInfo = getStoreStatusById((int)$store_id);
                                $this->store_order->buyer_id = '';
                                $this->store_order->payment_method = 'stripe';
                                $this->store_order->payment_status = $payment_status;
                                $this->store_order->charge_status = $charge_status;
                                $this->store_order->platform_fee = $platform_fee;
                                $this->store_order->payable_amount = $payable_amount;
                                $this->store_order->order_status = $default_order_status;
                                $this->store_order->total_amount = $orderAmount;
                
                                $this->store_order->shipping_address = '';
                                $this->store_order->shipping_method = '';
                                $this->store_order->total_items = $quantity;
                                $this->store_order->transaction_id = $paymentID;
                                $this->store_order->session_id = $checkoutSession->id;
                                $this->store_order->balance_transaction = $balanceTransaction;
                                $this->store_order->payerID = $payerID;
                                $this->store_order->shipping_amount = $shipping_amount;
                
                                $this->store_order->store_id = $store_id;
                
                                $order_id = $this->store_order->save();
                                $store_order_id = $this->store_order->order_id;
                                $onSaleProductFound = false;
                                $usedCouponCode = '';
                
                                if (isset($coupon_code) && !empty($coupon_code)) {
                                    $usedCouponCode = $coupon_code; 
                                // if (isset($val['coupon_code']) && !empty($val['coupon_code'])) {
                                //     $usedCouponCode = $val['coupon_code']; 
                                    // break; 
                                }else{
                                    foreach ($storeData as $product) {
                                        if ($onSaleProductFound == false) {
                                            if (isset($product['on_sale']) && $product['on_sale'] == 1 && $product['store_id'] == $store_id) {
                                                $onSaleProductFound = true;
                                                $sale_id = $product['sale_id'];
                                                break;
                                            }
                                        }
                                    }
                                }
        
                                if (isset($usedCouponCode) && !empty($usedCouponCode)) {
                                    // Track the coupon usage count
                                    $coupon = Discount::where('code', $usedCouponCode)
                                                                ->where('store_id', $store_id) 
                                                                ->first();
                                    
                                    if ($coupon) {
                                        $discount_id = $coupon->id; 
                                        $coupon->usage_count += 1; 
                                        $coupon->save();
                                        
                                        $this->discount_usage->discount_id = $discount_id; 
                                        $this->discount_usage->user_id = ''; 
                                        $this->discount_usage->order_id = $store_order_id;
                                        $this->discount_usage->store_id = $store_id;
                                        $this->discount_usage->used_at = Carbon::now();
                                        $this->discount_usage->save();
                                    }
                                }else{
                                
                    
                                    if (isset($onSaleProductFound) && $onSaleProductFound == true) {
                                        $this->discount_usage->discount_id = $sale_id;
                                        $this->discount_usage->user_id = ''; // Make sure to set discount_id in order_info
                                        $this->discount_usage->order_id = $store_order_id;
                                        $this->discount_usage->store_id = $store_id;
                                        $this->discount_usage->used_at = Carbon::now();
                                        $this->discount_usage->save();
                    
                                        $discount = Discount::where('id', $sale_id)->first();
                                        $discount->usage_count += 1;
                                        $discount->save();
                                    }
                                }
        
        
                                $order_id = $this->store_order->order_id;
                                $items_Array = array();
                                $sipping_amount = 0;
                            if ($order_id) {
                                    $store_data = getStoreInfoById((int)$store_id);
                                    $currentDate = date('Y-m-d');
                                    // $paymentDate = $currentDate;
                                    $paymentDate = date('Y-m-d', strtotime($currentDate . ' + 10 days'));
                                    $amountWithOutShip = $orderAmount;
                                    $paymentProfit = $amountWithOutShip * 0.045;
                                    $payableAmount = $orderAmount - $paymentProfit;
                                    $storeWallet = !empty($store_data[0]->wallet_address) ? $store_data[0]->wallet_address : '';
                                if (count($storeData) > 0) {
                                    $total_amount = 0;
                                    foreach ($storeData as $key => $product) {
                                        $sale_price = '';
                                        if (isset($product['product_id']) && $product['product_id'] != '') {
                                            $quantity = $product['product_quantity'];
                                            if ($product['product_type'] == 'variant') {
                                                if (isset($product['on_sale']) && $product['on_sale'] == 1 ){
                                                    $sale_price = isset($product['product_variation_sale_price']) ? $product['product_variation_sale_price'] : $product['sale_price'];
                                                    $justVirtualProducts = false;
                                                }
                                                    $item_price = isset($product['variation_sale_price']) ? $product['variation_sale_price'] : $product['price'];
                                                    $justVirtualProducts = false;
                                                
                                            } else if ($product['product_type'] == 'simple') {
                                                $justVirtualProducts = false;
                                                if (isset($product['on_sale']) && $product['on_sale'] == 1 )
                                                {
                                                    $sale_price = $product['sale_price'];
                                                }
                                                $item_price = isset($product['price']) ? $product['price'] : $product['price'];
                                                
                                            } else if ($product['product_type'] == 'virtual') {
                                                if (isset($product['on_sale']) && $product['on_sale'] == 1 )
                                                {
                                                    $sale_price = $product['sale_price'];
                                                }
                                                    $item_price = isset($product['price']) ? $product['price'] : $product['price'];
                                                
                                            }

                                            if (!is_numeric($quantity)) {
                                                $quantity = 1;
                                            }
                                            if(!empty($sale_price)){
                                                $item_price_condition = is_numeric($sale_price) ? $sale_price : 0; // Check if $item_price is numeric, if not, set it to 0
                                                $product_order_price = floatval($quantity * $item_price_condition);
                                            }else{
                                                $item_price_condition = is_numeric($item_price) ? $item_price : 0; // Check if $item_price is numeric, if not, set it to 0
                                                $product_order_price = floatval($quantity * $item_price_condition);
                                            }
                                            $shipping_method = isset($product['method']) ? $product['method'] : "";
                                            $store_id_pr = $product['store_id'];
                                            $store_p = getStoreInfoById((int) $store_id_pr);
                                            $shipping_charges = isset($product['shipping_price']) ? $product['shipping_price'] : 0;
                                            if ($shipping_charges == '') {
                                                $shipping_charges = 0;
                                            }
                                            $order_per_total = $shipping_charges + $product_order_price;
                                            $product_variation_id = isset($product['variation_id']) ? $product['variation_id'] : "";
                                            $shipping_price = isset($product['shipping_price']) ? $product['shipping_price'] : "";
                                            $p_name = $product['title'] ?? 'Not Found';
                                            $variation_info = '';
                                            if ($product_variation_id != '') {
                                                $variation_info = getVariationNameById($product_variation_id);
                                            }

                                            $image = isset($product['feature_image']) ? $product['feature_image'] : '';
                                            $image = (int) $image;
                                            $image_url = '';
                                            $newImagePath = '';
                                            if (!empty($image)) {
                                                if($product['product_type'] == 'simple' ){
                                                    $image_url = getAttachmentUrlById((int) $image, 'small');
                                                }elseif($product['product_type'] == 'variant'){
                                                    $image_url = getAttachmentUrlById((int) (isset($product['product_variation_image']) && $product['product_variation_image'] != "" ? $product['product_variation_image'] : $image) , 'medium');
                                                    if(empty($image_url)){
                                                        $image_url = getAttachmentUrlById((int) $image, 'small');
                                                    }
                                                }else{ 
                                                    $image_url = getAttachmentUrlById((int) $image, 'small');
                                                }
                                            }
                                            if($image_url != ''){
                                                $newImagePath = copyImageFromUrlToNewFolder($image_url);
                                            }
                                            if ($shipping_method == 'calculated' && isset($product['ship_id']) && $product['ship_id'] != '') {
                                                $created_date = date("Y-m-d H:i:s");
                                                $items_Array[] = array(
                                                    'order_id' => $order_id, 'store_id' => $store_id, 'item_id' => $product['product_id'],
                                                'sold_price' => (!empty($sale_price)) ? $sale_price : 0,
                                                    'sale_id' => $product['sale_id'],
                                                    'variation_id' => $product_variation_id, 'quantity' => $product['product_quantity'], 
                                                    'price' => $item_price,
                                                    'shipping_amount' => $product['shipping_price'], 'order_note' => '', 'item_tax' => '',
                                                    'method' => $shipping_method, 'total' => $order_per_total, 'shipment_id' => $product['ship_id'], 'payment_service' => $product['shipping_method'], 'shipment_id' => '', 'rate_id' => '', 'label_url' => '', 'lable_pdf_url' => '', 'tracking_id' => '', 'tracking_code' => '', 'shipment_object' => '', 'created_at' => $created_date,
                                                    'product_name' => $p_name, 'variation_info' => $variation_info, 'item_image' => $newImagePath
                                                );
                                            } else {
                                                $created_date = date("Y-m-d H:i:s");
                                                $items_Array[] = array(
                                                    'order_id' => $order_id, 'store_id' => $store_id, 'item_id' => $product['product_id'],
                                                    'sold_price' => (!empty($sale_price)) ? $sale_price : 0,
                                                    'sale_id' => $product['sale_id'],
                                                    'variation_id' => $product_variation_id, 'quantity' => $product['product_quantity'], 
                                                    'price' => $item_price,
                                                    'shipping_amount' => $shipping_price, 'order_note' => '', 'item_tax' => '',
                                                    'shipment_id' => '', 'rate_id' => '', 'label_url' => '',
                                                    'lable_pdf_url' => '', 'tracking_id' => '', 'tracking_code' => '', 'payment_service' => '', 'shipment_object' => '',
                                                    'method' => $shipping_method, 'total' => $order_per_total, 'created_at' => $created_date,
                                                    'product_name' => $p_name, 'variation_info' => $variation_info, 'item_image' => $newImagePath
                                                );
                                            }
                                            $storeInfo = getStoreStatusById((int)$store_id);
                                        }
                                    }

                                    if (count($items_Array) > 0) {
                                        $this->add_order_items($items_Array);
                                    }
                                }
            
                            }
        
                        }
                    }catch (Exception $e) {
                        DB::rollBack();
                        $paymentLog = new Logger('');
                        $paymentLog->pushHandler(new StreamHandler(storage_path('logs/orders-exception.log')), Logger::ERROR);
                        $paymentLog->error('Failed', ['Order_id' =>$order_id,'store_id' => $store_id, 'order_date' => Carbon::now(), 'Message' => $e->getMessage()]);
                    }
                } 
            });
            if(isset($request['platform'])){
                if($request['platform'] == 'web'){
                    return response()->json(['id' => $checkoutSession->id]);
                }elseif($request['platform'] == 'mobile'){
            return response()->json(['id' => $checkoutSession->url]);

                }
            }
        } catch (Exception $e) {
            DB::rollBack();
            $paymentLog = new Logger('');
            $paymentLog->pushHandler(new StreamHandler(storage_path('logs/orders-exception.log')), Logger::ERROR);
            $paymentLog->error('Failed', ['order_date' => Carbon::now(), 'Message' => $e->getMessage()]);
        }
    }
    public function add_order_items($item_array = array())
    {
        OrderItems::insert($item_array);
    }
    public function OrderConfirmation()
    {
        $id = request()->query('id');
        $sessionExists = StoreOrder::where('session_id', $id)->exists();
        if (!$sessionExists) {
            return response()->json([
                'status' => false,
                'success_url' =>  'myapp://payment-cancel',
            ]);
        }
        
        $storeOrders = StoreOrder::where('session_id',$id)->where('charge_status','unpaid')->get();
       
        if($storeOrders->isEmpty()) {
           
            return response()->json([
                'status' => true,
                'success_url' =>  'myapp://payment-success',
            ]);
        }else{
            return response()->json([
                'status' => false,
                'success_url' =>  'myapp://payment-cancel',
            ]);
        }
        
        
    }
}
