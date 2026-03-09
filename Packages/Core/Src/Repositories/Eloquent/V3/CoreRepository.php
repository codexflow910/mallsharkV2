<?php

namespace Packages\Core\Repositories\Eloquent\V3;

use App\Http\Controllers\MailgunController;
use Exception;
use Carbon\Carbon;
use Stripe\Stripe;
use Stripe\Coupon;
use Monolog\Logger;
use Stripe\Webhook;
use Stripe\Customer;
use InvalidArgumentException;
use OutOfBoundsException;
use LogicException;
use RuntimeException;
use Stripe\PaymentIntent;
use Illuminate\Support\Facades\Log;
use Packages\Auth\Models\V3\Userrole;
use Packages\Order\Models\V3\OrderItems;
use Packages\Store\Models\V3\StoreEscrow;
use Stripe\Checkout\Session;
use Packages\Auth\Models\V3\User;
use Packages\Home\Models\V3\ShippingAddress;
use Illuminate\Support\Facades\DB;
use Monolog\Handler\StreamHandler;
use Packages\Product\Models\V3\Product;
use Packages\Cart\Models\V3\ProductCart;
use Packages\Order\Models\V3\{StoreOrder,OrderShipping, OrderTracking};
use Packages\Discount\Models\V3\Discount;
use Packages\Discount\Models\V3\{DiscountUsage,DiscountApplicable,CustomerCoupon};
use Packages\Admin\Models\V3\StoreDeduction;
use Illuminate\Support\ItemNotFoundException;
use Packages\Core\Config\CoreConstants;
use Packages\Core\Contracts\V3\CoreRepositoryInterface;
use Packages\Core\Models\V3\MallsharkPurchasedPackage;
use Packages\Product\Models\V3\ProductVariation;
use Packages\Shipping\Models\V3\ShippingProfile;
use Packages\Store\Models\V3\Store;
use Packages\Shipping\Models\V3\ShippingOption;
use Packages\Core\Contracts\V3\CoreHelpers;
use Packages\Cart\Contracts\V3\CartHelpers;
use Packages\Order\Config\OrderConstants;
use Packages\Core\Services\V3\CryptoService;
use Packages\Core\Services\V3\CoreService;
class CoreRepository implements CoreRepositoryInterface
{
    use CoreHelpers;
    protected $model;
    protected $cartModel;
    protected $productVariationModel;
    protected $cryptoService;
    public function __construct(ProductCart $cartModel, ProductVariation $productVariationModel, CryptoService $cryptoService)
    {
        $this->cartModel = $cartModel;
        Stripe::setApiKey(env('STRIPE_API_KEY'));
        $this->productVariationModel = $productVariationModel;
        $this->CryptoService = $cryptoService;
    }

    public function getCart($data)
    {
        try {
            $query = $this->cartModel->with(['product', 'variation', 'store', 'product.store', 'product.shippingOption']);

            if ($data['guest'] && !empty($data['id'])) {
                $query->where('random_key', $data['id']);
            } elseif (!$data['guest'] && !empty($data['id'])) {
                $query->where('user_id', $data['id']);
            }
            $query->where('is_check','1');
            $cart = $query->get();

            if ($cart->isEmpty()) {
                throw new ItemNotFoundException('Cart not found');
            }

            return $cart->toArray();
        } catch (ItemNotFoundException $e) {
            throw new ItemNotFoundException($e->getMessage());
        } catch (Exception $e) {
            throw new Exception('An error occurred while retrieving the cart', $e->getMessage());
        }
    }

    public function findOrCreateStripeCustomer($user)
    {
        if (!$user->customer_id) {
            $customer = Customer::create([
                'email' => $user->email,
                'name' => $user->name ?? '',
            ]);
            $user = User::where('user_id',$user->user_id)->first();
            $user->customer_id = $customer->id;
            $user->save();
        } else {
            $customer = Customer::retrieve($user->customer_id);
        }

        return $customer;
    }
    public function findOrCreateStripeCustomerV2($data,$requestPlatform)
    {
        $user = User::where('email',$data['email'])->first();
        $customerName = $data['full_name'];
        $customerEmail = $data['email'];
        $customerPhone = $data['phone_no'];
        $shippingAddress = [
            'line1'       => $data['address_1'],
            'line2'       => $data['address_2'],
            'city'        => $data['city'],
            'state'       => $data['state'],
            'postal_code' => $data['state'],
            'country'     => 'US',
        ];
        if($user){
            if (!$user->customer_id) {
                $customer = Customer::create([
                    'email' => $customerEmail,
                    'shipping' => [
                        'name' => $customerName,
                        'address' => $shippingAddress
                    ],
                ]);
                $user = User::where('user_id',$user->user_id)->first();
                $user->customer_id = $customer->id;
                $user->save();
            } else {
                $customer = Customer::update($user->customer_id, [
                   'email' => $customerEmail,
                    'shipping' => [
                        'name' => $customerName,
                        'address' => $shippingAddress
                    ],
                ]);
            }
        }else{
            $loginKey = str_random(25);
            $customer = Customer::create([
                 'email' => $customerEmail,
                    'shipping' => [
                        'name' => $customerName,
                        'address' => $shippingAddress
                    ],
            ]);

            $user = new User();
            $user->name = $customerName;
            $user->email = $customerEmail;
            $user->customer_id = $customer->id;
            $user->password = md5('mallshark123456');
            $now = Carbon::now();
            $user->forget_key_date = $now;
            $user->login_key = $loginKey;
            $user->register_source = $requestPlatform;
            $user->guest_user = 1;
            $user->save();
            $userRole = new Userrole();
            $userRole->user_id = $user->user_id;
            $userRole->role_id = 4;
            $userRole->save();
        }
        return $user;
    }

    public function findOrCreateStripeCustomerV3($data,$requestPlatform,$checkoutUserId)
    {
        try{
            $logData = [
                'event_type' => 'customer_creation_request',
                'user_id' => $checkoutUserId,
                'data' => json_encode(['customer_data' => $data]),
            ];
            $log = CoreHelpers::checkoutLogV3($logData);
        
            $user = User::where('email',$data['email'])->first();
            $customerName = $data['full_name'];
            $customerEmail = $data['email'];
            $customerPhone = $data['phone_no'];
            $shippingAddress = [
                'line1'       => $data['address_1'],
                'line2'       => $data['address_2'],
                'city'        => $data['city'],
                'state'       => $data['state'],
                'postal_code' => $data['state'],
                'country'     => 'US',
            ];
            if($user){
                if (!$user->customer_id) {
                    $customer = Customer::create([
                        'email' => $customerEmail,
                        'shipping' => [
                            'name' => $customerName,
                            'address' => $shippingAddress
                        ],
                    ]);
                    $user = User::where('user_id',$user->user_id)->first();
                    $user->customer_id = $customer->id;
                    $user->save();
                } else {
                    $customer = Customer::update($user->customer_id, [
                    'email' => $customerEmail,
                        'shipping' => [
                            'name' => $customerName,
                            'address' => $shippingAddress
                        ],
                    ]);
                }
            }else{
                $loginKey = str_random(25);
                $customer = Customer::create([
                    'email' => $customerEmail,
                        'shipping' => [
                            'name' => $customerName,
                            'address' => $shippingAddress
                        ],
                ]);

                $user = new User();
                $user->name = $customerName;
                $user->email = $customerEmail;
                $user->customer_id = $customer->id;
                $user->password = md5('mallshark123456');
                $now = Carbon::now();
                $user->forget_key_date = $now;
                $user->login_key = $loginKey;
                $user->register_source = $requestPlatform;
                $user->guest_user = 1;
                $user->save();
                $userRole = new Userrole();
                $userRole->user_id = $user->user_id;
                $userRole->role_id = 4;
                $userRole->save();
            }
            $logData = [
                'event_type' => 'customer_creation_completed',
                'user_id' => $checkoutUserId,
                'data' => json_encode(['customer_data' => $data]),
            ];
            $log = CoreHelpers::checkoutLogV3($logData);
            return $user;
        }catch(Exception $e){
            $error = [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ];
            $logData = [
                'event_type' => 'customer_creation_failed',
                'user_id' => $checkoutUserId,
                'data' => json_encode(['customer_request_data' => $error]),
            ];
            $log = CoreHelpers::checkoutLogV3($logData);
            throw new Exception('An error occurred while retrieving or creating the Stripe customer: ' . $e->getMessage());
        }
    }
    public function findOrCreatePaypalCustomerV3($data,$requestPlatform,$checkoutUserId)
    {
        try{
            $logData = [
                'event_type' => 'customer_creation_request',
                'user_id' => $checkoutUserId,
                'data' => json_encode(['customer_data' => $data]),
            ];
            $log = CoreHelpers::checkoutLogV3($logData);
        
            $user = User::where('email',$data['email'])->first();
            $customerName = $data['full_name'];
            $customerEmail = $data['email'];
            $customerPhone = $data['phone_no'];
            $shippingAddress = [
                'line1'       => $data['address_1'],
                'line2'       => $data['address_2'],
                'city'        => $data['city'],
                'state'       => $data['state'],
                'postal_code' => $data['state'],
                'country'     => 'US',
            ];
            if($user){
                return $user;
            }else{
                $loginKey = str_random(25);
                $user = new User();
                $user->name = $customerName;
                $user->email = $customerEmail;
                $user->customer_id = null;
                $user->password = md5('mallshark123456');
                $now = Carbon::now();
                $user->forget_key_date = $now;
                $user->login_key = $loginKey;
                $user->register_source = $requestPlatform;
                $user->guest_user = 1;
                $user->save();
                $userRole = new Userrole();
                $userRole->user_id = $user->user_id;
                $userRole->role_id = 4;
                $userRole->save();
            }
            $logData = [
                'event_type' => 'customer_creation_completed',
                'user_id' => $checkoutUserId,
                'data' => json_encode(['customer_data' => $data]),
            ];
            $log = CoreHelpers::checkoutLogV3($logData);
            return $user;
        }catch(Exception $e){
            $error = [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ];
            $logData = [
                'event_type' => 'customer_creation_failed',
                'user_id' => $checkoutUserId,
                'data' => json_encode(['customer_request_data' => $error]),
            ];
            $log = CoreHelpers::checkoutLogV3($logData);
            throw new Exception('An error occurred while retrieving or creating the Stripe customer: ' . $e->getMessage());
        }
    }
    public function createCryptoUser($data,$requestPlatform,$checkoutUserId)
    {
        try{
            $logData = [
                    'event_type' => 'customer_creation_request',
                    'user_id' => $checkoutUserId,
                    'data' => json_encode(['customer_data' => $data]),
                ];
                $log = CoreHelpers::checkoutLog($logData);
            $user = User::where('email',$data['email'])->first();
            $customerName = $data['full_name'];
            $customerEmail = $data['email'];
            if(!$user){
                $loginKey = str_random(25);
                $user = new User();
                $user->name = $customerName;
                $user->email = $customerEmail;
                $user->customer_id = null;
                $user->password = md5('mallshark123456');
                $now = Carbon::now();
                $user->forget_key_date = $now;
                $user->login_key = $loginKey;
                $user->register_source = $requestPlatform;
                $user->guest_user = 1;
                $user->save();
                $userRole = new Userrole();
                $userRole->user_id = $user->user_id;
                $userRole->role_id = 4;
                $userRole->save();
            }
            $logData = [
                'event_type' => 'customer_creation_completed',
                'user_id' => $checkoutUserId,
                'data' => json_encode(['customer_created_successfully']),
            ];
            $log = CoreHelpers::checkoutLog($logData);
            return $user;
        }catch(Exception $e){
            $error = [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ];
            $logData = [
                'event_type' => 'customer_creation_failed',
                'user_id' => $checkoutUserId,
                'data' => json_encode(['customer_request_data' => $error]),
            ];
            $log = CoreHelpers::checkoutLog($logData);
            throw new Exception('An error occurred while retrieving or creating the Stripe customer: ' . $e->getMessage());
        }
    }
    public function createCoupon($totalDiscount)
    {
        return Coupon::create([
            'amount_off' => intval($totalDiscount * 100), // Convert to cents
            'currency' => 'usd',
            'duration' => 'once'
        ]);
    }

    public function checkoutSession(
        $lineItems,
        $customerId,
        $successUrl,
        $cancelUrl,
        $totalDiscount,
        $totalShipping,
        $subTotal,
        $storeIds,
        $productIds,
        $guest,
        $userId,
        $cartId,
        $discounts
    ) {
        return Session::create([
            'payment_method_types' => ['card', 'crypto'],
            'line_items' => $lineItems,
            'customer' => $customerId,
            'mode' => 'payment',
            'success_url' =>  $successUrl,
            'cancel_url' => $cancelUrl,
            'metadata' => [
                'total_discount' => number_format($totalDiscount, 2),
                'total_shipping' => number_format($totalShipping, 2),
                'subtotal' => number_format($subTotal, 2),
                'total_due' => number_format($subTotal - $totalDiscount, 2), // Final amount due
                'store_ids' => json_encode(array_values(array_unique($storeIds))), // ✅ Convert to JSON string
                'product_ids' => json_encode(array_values(array_unique($productIds))),
                'guest' => $guest,
                'user_id' => $userId,
                'cart_id' => $cartId,
                'checkout' => 'old',
            ],
            'custom_text' => [
                'submit' => [
                    'message' => 'Note: Only USDC and USDP accepted for crypto payments.',
                ],
                'shipping_address' => [
                    'message' => '⚠️❗ APARTMENT # to be added in address line 2 (if applicable). ❗',
                ],
            ],

            'shipping_address_collection' => ['allowed_countries' => ['US']],
            // 'billing_address_collection' => 'required',
            'discounts' => $discounts, // ✅ Apply discount here
        ]);
    }
    public function createPaymentIntent($amount, $currency = 'usd', $customerId = null, $metadata = [])
    {
        return PaymentIntent::create([
            'amount' => intval($amount), // in cents
            'currency' => $currency,
            'customer' => $customerId,
            'automatic_payment_methods' => [
                'enabled' => true, // enables card, Apple Pay, Google Pay automatically
            ],
            'metadata' => $metadata,
        ]);
    }

    public function createPaymentIntentV3($amount, $currency = 'usd', $customerId = null, $metadata = [])
    {
        return PaymentIntent::create([
            'amount' => intval($amount), // in cents
            'currency' => $currency,
            'customer' => $customerId,
            'automatic_payment_methods' => [
                'enabled' => true, // enables card, Apple Pay, Google Pay automatically
            ],
            'metadata' => $metadata,
        ]);
    }

    public function processOrder($checkoutSession, $cartData, $request)
    {
        try {
            
            DB::transaction(function () use ($checkoutSession, $cartData) {
                $logData = [
                    'event_type' => 'order_creation',
                    'session_id' => $checkoutSession->id,
                    'data' => json_encode(['orderData' => $cartData]),
                ];
                $log = CoreHelpers::checkoutLog($logData);
                foreach ($cartData as $key => $storeData) {
                    $orderId = null;
                    try {

                        $orderAmount = 0;
                        $shippingAmount = 0;
                        foreach ($storeData as $product) {
                            $salePrice = '';
                            $quantity = $product['product_quantity'];
                            if ($product['product_type'] == 'variant') {
                                if (isset($product['on_sale']) && $product['on_sale'] == 1) {
                                    $salePrice = isset($product['product_variation_sale_price']) ? round((float)$product['product_variation_sale_price'],2) : round((float)$product['sale_price'],2);
                                }
                                $itemPrice = isset($product['variation_sale_price']) ? round((float)$product['variation_sale_price'],2) : round((float)$product['price'],2);
                            } else if ($product['product_type'] == 'simple') {

                                if (isset($product['on_sale']) && $product['on_sale'] == 1) {
                                    $salePrice = round((float)$product['sale_price'],2);
                                }
                                $itemPrice = isset($product['price']) ? round((float)$product['price'],2) : round((float)$product['price'],2);
                            } else if ($product['product_type'] == 'virtual') {
                                if (isset($product['on_sale']) && $product['on_sale'] == 1) {
                                    $salePrice = round((float)$product['sale_price'],2);
                                }
                                $itemPrice = isset($product['price']) ? round((float)$product['price'],2) : round((float)$product['price'],2);
                            }

                            if (!is_numeric($quantity)) {
                                $quantity = 1;
                            }
                            if (!empty($salePrice)) {
                                $salePrice = is_numeric($salePrice) ? $salePrice : 0;
                                $productOrderPrice = floatval($quantity * $salePrice);
                            } else {
                                $itemPrice = is_numeric($itemPrice) ? $itemPrice : 0;
                                $productOrderPrice = floatval($quantity * $itemPrice);
                            }

                            $shippingMethod = isset($product['method']) ? $product['method'] : "";
                            $storeId = $product['store_id'];
                            $shippingCharges = isset($product['shipping_price']) ? $product['shipping_price'] : 0;
                            if ($shippingCharges == '') {
                                $shippingCharges = 0;
                            }
                            $orderAmount += $shippingCharges + $productOrderPrice;
                            $shippingAmount += $shippingCharges;
                        }

                        $stripeFee = ($orderAmount * (2.9 / 100) + 0.33);
                        $platformFee = $orderAmount * 0.045;
                        $totalFee = $stripeFee +  $platformFee;
                        $payableAmount = $orderAmount - $totalFee;
                        $platformFee = $platformFee;
                        $chargeStatus = 'unpaid';
                        $payableAmount = $payableAmount;
                        $paymentID = '';
                        $balanceTransaction = '';
                        $payerID = '';
                        $paymentStatus = 'pending';
                        $defaultOrderStatus = 'pending';

                        if ($key != 'products_price' && $key != 'shipping_price' && $key != 'total_price' && $key != 'total_tax') {
                            $storeOrder = new StoreOrder();
                            $discountUsage = new DiscountUsage();

                            $storeId = $key;
                            $storeOrder->buyer_id = '';
                            $storeOrder->payment_method = 'stripe';
                            $storeOrder->payment_status = $paymentStatus;
                            $storeOrder->charge_status = $chargeStatus;
                            $storeOrder->platform_fee = $platformFee;
                            $storeOrder->payable_amount = $payableAmount;
                            $storeOrder->order_status = $defaultOrderStatus;
                            $storeOrder->total_amount = $orderAmount;

                            $storeOrder->shipping_address = '';
                            $storeOrder->shipping_method = '';
                            $storeOrder->total_items = $quantity;
                            $storeOrder->transaction_id = $paymentID;
                            $storeOrder->session_id = $checkoutSession->id;
                            $storeOrder->balance_transaction = $balanceTransaction;
                            $storeOrder->payerID = $payerID;
                            $storeOrder->shipping_amount = $shippingAmount;
                            $storeOrder->store_id = $storeId;
                            $storeOrder->ip_address = request()->ip();
                            $storeOrder->platform = isset($request['platform']) ? $request['platform'] : 'web';
                            $orderId = $storeOrder->save();

                            $storeOrderId = $storeOrder->order_id;
                            $onSaleProductFound = false;
                            $usedCouponCode = '';
                            if (isset($coupon_code) && !empty($coupon_code)) {
                                $usedCouponCode = $coupon_code;
                            } else {
                                foreach ($storeData as $product) {
                                    if ($onSaleProductFound == false) {
                                        if (isset($product['on_sale']) && $product['on_sale'] == 1 && $product['store_id'] == $storeId) {
                                            $onSaleProductFound = true;
                                            $saleId = $product['sale_id'];
                                            break;
                                        }
                                    }
                                }
                            }

                            if (isset($usedCouponCode) && !empty($usedCouponCode)) {
                                // Track the coupon usage count
                                $coupon = Discount::where('code', $usedCouponCode)
                                    ->where('store_id', $storeId)
                                    ->first();

                                if ($coupon) {
                                    $discountId = $coupon->id;
                                    $coupon->usage_count += 1;
                                    $coupon->save();

                                    $discountUsage->discount_id = $discountId;
                                    $discountUsage->user_id = '';
                                    $discountUsage->order_id = $storeOrderId;
                                    $discountUsage->store_id = $storeId;
                                    $discountUsage->used_at = Carbon::now();
                                    $discountUsage->save();
                                }
                            } else {

                                if (isset($onSaleProductFound) && $onSaleProductFound == true) {
                                    $discountUsage->discount_id = $saleId;
                                    $discountUsage->user_id = ''; // Make sure to set discount_id in order_info
                                    $discountUsage->order_id = $storeOrderId;
                                    $discountUsage->store_id = $storeId;
                                    $discountUsage->used_at = Carbon::now();
                                    $discountUsage->save();

                                    $discount = Discount::where('id', $saleId)->first();
                                    $discount->usage_count += 1;
                                    $orderId = $discount->save();
                                }
                            }

                            $orderId = $storeOrder->order_id;
                            $itemsArray = array();

                            if ($orderId) {

                                $amountWithoutShip = $orderAmount;
                                $paymentProfit = $amountWithoutShip * 0.045;
                                $payableAmount = $orderAmount - $paymentProfit;

                                if (count($storeData) > 0) {

                                    foreach ($storeData as $key => $product) {
                                        $salePrice = '';
                                        if (isset($product['product_id']) && $product['product_id'] != '') {
                                            $quantity = $product['product_quantity'];
                                            if ($product['product_type'] == 'variant') {
                                                if (isset($product['on_sale']) && $product['on_sale'] == 1) {
                                                    $salePrice = isset($product['product_variation_sale_price']) ? $product['product_variation_sale_price'] : $product['sale_price'];
                                                }
                                                $itemPrice = isset($product['variation_sale_price']) ? $product['variation_sale_price'] : $product['price'];
                                            } else if ($product['product_type'] == 'simple') {

                                                if (isset($product['on_sale']) && $product['on_sale'] == 1) {
                                                    $salePrice = $product['sale_price'];
                                                }
                                                $itemPrice = isset($product['price']) ? $product['price'] : $product['price'];
                                            } else if ($product['product_type'] == 'virtual') {
                                                if (isset($product['on_sale']) && $product['on_sale'] == 1) {
                                                    $salePrice = $product['sale_price'];
                                                }
                                                $itemPrice = isset($product['price']) ? $product['price'] : $product['price'];
                                            }

                                            if (!is_numeric($quantity)) {
                                                $quantity = 1;
                                            }
                                            if (!empty($salePrice)) {
                                                $salePrice = is_numeric($salePrice) ? $salePrice : 0; // Check if $itemPrice is numeric, if not, set it to 0
                                                $productOrderPrice = floatval($quantity * $salePrice);
                                            } else {
                                                $itemPrice = is_numeric($itemPrice) ? $itemPrice : 0; // Check if $itemPrice is numeric, if not, set it to 0
                                                $productOrderPrice = floatval($quantity * $itemPrice);
                                            }
                                            $shippingMethod = isset($product['method']) ? $product['method'] : "";
                                            $storeId = $product['store_id'];
                                            $shippingCharges = isset($product['shipping_price']) ? $product['shipping_price'] : 0;
                                            if ($shippingCharges == '') {
                                                $shippingCharges = 0;
                                            }
                                            $orderPerTotal = $shippingCharges + $productOrderPrice;
                                            $productVariationId = isset($product['variation_id']) ? $product['variation_id'] : "";
                                            $shippingPrice = isset($product['shipping_price']) ? $product['shipping_price'] : "";
                                            $productName = $product['title'] ?? 'Not Found';
                                            $variationInfo = '';
                                            if ($productVariationId != '') {
                                                $variationInfo = getVariationNameById($productVariationId);
                                            }

                                            $image = isset($product['feature_image']) ? $product['feature_image'] : '';
                                            $image = (int) $image;
                                            $imageUrl = '';
                                            $newImagePath = '';
                                            if (!empty($image)) {
                                                if ($product['product_type'] == 'simple') {
                                                    $imageUrl = getAttachmentUrlById((int) $image, 'small');
                                                } elseif ($product['product_type'] == 'variant') {
                                                    $imageUrl = getAttachmentUrlById((int) (isset($product['product_variation_image']) && $product['product_variation_image'] != "" ? $product['product_variation_image'] : $image), 'medium');
                                                    if (empty($imageUrl)) {
                                                        $imageUrl = getAttachmentUrlById((int) $image, 'small');
                                                    }
                                                } else {
                                                    $imageUrl = getAttachmentUrlById((int) $image, 'small');
                                                }
                                            }

                                            if ($imageUrl != '') {
                                                $newImagePath = copyImageFromUrlToNewFolder($imageUrl);
                                            }

                                            if ($shippingMethod == 'calculated' && isset($product['ship_id']) && $product['ship_id'] != '') {
                                                $createdDate = date("Y-m-d H:i:s");
                                                $itemsArray[] = array(
                                                    'order_id' => $orderId,
                                                    'store_id' => $storeId,
                                                    'item_id' => $product['product_id'],
                                                    'sold_price' => (!empty($salePrice)) ? $salePrice : 0,
                                                    'sale_id' => $product['sale_id'],
                                                    'variation_id' => $productVariationId,
                                                    'quantity' => $product['product_quantity'],
                                                    'price' => $itemPrice,
                                                    'shipping_amount' => $product['shipping_price'],
                                                    'order_note' => '',
                                                    'item_tax' => '',
                                                    'method' => $shippingMethod,
                                                    'total' => $orderPerTotal,
                                                    'shipment_id' => $product['ship_id'],
                                                    'payment_service' => $product['shipping_method'],
                                                    'shipment_id' => '',
                                                    'rate_id' => '',
                                                    'label_url' => '',
                                                    'lable_pdf_url' => '',
                                                    'tracking_id' => '',
                                                    'tracking_code' => '',
                                                    'shipment_object' => '',
                                                    'created_at' => $createdDate,
                                                    'product_name' => $productName,
                                                    'variation_info' => $variationInfo,
                                                    'item_image' => $newImagePath
                                                );
                                            } else {
                                                $createdDate = date("Y-m-d H:i:s");
                                                $itemsArray[] = array(
                                                    'order_id' => $orderId,
                                                    'store_id' => $storeId,
                                                    'item_id' => $product['product_id'],
                                                    'sold_price' => (!empty($salePrice)) ? $salePrice : 0,
                                                    'sale_id' => $product['sale_id'],
                                                    'variation_id' => $productVariationId,
                                                    'quantity' => $product['product_quantity'],
                                                    'price' => $itemPrice,
                                                    'shipping_amount' => $shippingPrice,
                                                    'order_note' => '',
                                                    'item_tax' => '',
                                                    'shipment_id' => '',
                                                    'rate_id' => '',
                                                    'label_url' => '',
                                                    'lable_pdf_url' => '',
                                                    'tracking_id' => '',
                                                    'tracking_code' => '',
                                                    'payment_service' => '',
                                                    'shipment_object' => '',
                                                    'method' => $shippingMethod,
                                                    'total' => $orderPerTotal,
                                                    'created_at' => $createdDate,
                                                    'product_name' => $productName,
                                                    'variation_info' => $variationInfo,
                                                    'item_image' => $newImagePath
                                                );
                                            }
                                        }
                                    }

                                    if (count($itemsArray) > 0) {
                                        $this->addOrderItems($itemsArray);
                                    }
                                }
                            }
                        }
                    } catch (Exception $e) {
                        DB::rollBack();
                        $paymentLog = new Logger('');
                        $paymentLog->pushHandler(new StreamHandler(storage_path('logs/orders-exception.log')), Logger::ERROR);
                        $paymentLog->error('Failed', ['Order_id' => $orderId, 'store_id' => $storeId, 'order_date' => Carbon::now(), 'Message' => $e->getMessage()]);
                    }
                }
            });

            if (isset($request['platform'])) {
                if ($request['platform'] == 'web') {
                    return response()->json(['id' => $checkoutSession->id]);
                } elseif ($request['platform'] == 'mobile') {
                    return response()->json(['id' => $checkoutSession->url]);
                }
            }
        } catch (Exception $e) {
            throw new Exception('An error occurred while processing the order: ' . $e->getMessage());
        }
    }

    public function processOrderV2($checkoutSession, $productData, $request, $userId)
    {
        try {
            DB::transaction(function () use ($checkoutSession, $productData, $userId, $request) {
                $customerShipping = $request['shipping_data'];
                $country = CoreHelpers::getCountryByCode($customerShipping['country']);
                $state = CoreHelpers::getStatebyCode($customerShipping['state']);

                $shippingAddress = $this->createShippingAddressV2(
                    $customerShipping['full_name'],
                    $country['id'],
                    $state['state_id'],
                    $customerShipping['address_1'],
                    $customerShipping['zip_code'],
                    $customerShipping['city'],
                    $userId,
                    $customerShipping['address_2'],
                    $customerShipping['phone_no']
                )->sh_id;

                foreach ($productData as $storeId => $storeData) {
                    try {
                        $orderAmount = 0;
                        $orderShippingAmount = 0;
                        $coupon_code = $storeData['coupon_code'] ?? null;
                        $totalOrderItems = 0;
                        foreach ($storeData['products'] as $product) {
                             $totalOrderItems++;
                            $quantity = is_numeric($product['quantity']) ? (int)$product['quantity'] : 1;
                            $salePrice = $product['on_sale'] == 1 ? ($product['sale_price'] ?? 0) : null;
                            $itemPrice = $product['price'] ?? 0;

                            $productOrderPrice = $quantity * ($salePrice ?? $itemPrice);
                            $shippingCharges = $product['shipping_detail']['shipping_amount'] ?? 0;

                            $orderAmount += $productOrderPrice + $shippingCharges;
                            $orderShippingAmount += $shippingCharges;
                        }

                        $stripeFee = ($orderAmount * 0.029) + 0.33;
                        $platformFeePercent = optional(StoreDeduction::where('store_id', $storeId)->first())->mall_shark_amount ?? env('MALLSHARK_DEDUCTION');
                        $platformFee = ($platformFeePercent / 100) * $orderAmount;
                        $payableAmount = $orderAmount - ($stripeFee + $platformFee);

                        $storeOrder = new StoreOrder([
                            'buyer_id' => $userId,
                            'payment_method' => 'stripe',
                            'payment_status' => 'pending',
                            'charge_status' => 'unpaid',
                            'platform_fee' => $platformFee,
                            'payable_amount' => $payableAmount,
                            'order_status' => 'pending',
                            'total_amount' => $orderAmount,
                            'total_items' => $totalOrderItems,
                            'shipping_address' => $shippingAddress,
                            'shipping_method' => '',
                            'transaction_id' => '',
                            'session_id' => $checkoutSession->id,
                            'balance_transaction' => '',
                            'payerID' => '',
                            'shipping_amount' => $orderShippingAmount,
                            'store_id' => $storeId
                        ]);
                        $storeOrder->save();

                        $orderId = $storeOrder->order_id;
                        $itemsArray = [];
                        $itemShippingArray = [];

                        foreach ($storeData['products'] as $product) {
                            $quantity = is_numeric($product['quantity']) ? (int)$product['quantity'] : 1;
                            $salePrice = $product['on_sale'] == 1 ? ($product['sale_price'] ?? 0) : null;
                            $itemPrice = $product['price'] ?? 0;
                            $orderItemPrice = $quantity * ($salePrice ?? $itemPrice);
                            $shippingCharges = $product['shipping_detail']['shipping_amount'] ?? 0;
                            $totalPerItem = $orderItemPrice + $shippingCharges;

                            $variationInfo = '';
                            if (!empty($product['variation_id'])) {
                                $variationInfo = getVariationNameById($product['variation_id']);
                            }

                            $imageId = (int)($product['image_id'] ?? 0);
                            $imageUrl = getAttachmentUrlById($imageId, 'medium') ?? getAttachmentUrlById($imageId, 'small');
                            $newImagePath = $imageUrl ? copyImageFromUrlToNewFolder($imageUrl) : '';

                            $itemsArray[] = [
                                'order_id' => $orderId,
                                'store_id' => $product['store_id'],
                                'item_id' => $product['product_id'],
                                'sold_price' => $salePrice ?? 0,
                                'sale_price' => $salePrice ?? 0,
                                'sale_id' => $product['discount_id'] ?? null,
                                'variation_id' => $product['variation_id'] ?? null,
                                'quantity' => $quantity,
                                'price' => $itemPrice,
                                'shipping_amount' => $shippingCharges,
                                'total' => $totalPerItem,
                                'product_name' => $product['title'] ?? 'N/A',
                                'variation_info' => $variationInfo,
                                'item_image' => $newImagePath,
                                'created_at' => now(),
                            ];

                            $itemShippingArray[] = [
                                'shipping_profile_id' => $product['shipping_detail']['shipping_profile_id'],
                                'order_id' => $orderId,
                                'product_id' => $product['product_id'],
                                'shipping_amount' => $shippingCharges,
                                'base_price' => $product['shipping_detail']['base_price'] ?? 0,
                                'handlilng_fee' => $product['shipping_detail']['handling_fee'] ?? 0,
                                'delivery_time' => $product['shipping_detail']['delivery_time'],
                                'shipping_method' => $product['shipping_detail']['shipping_method'],
                                'service_id' => $product['shipping_detail']['service_id'],
                                'service_slug' => $product['shipping_detail']['service_slug'],
                                'service_name' => $product['shipping_detail']['service_name'],
                            ];
                        }

                        $this->addOrderItems($itemsArray);
                        $this->addItemShipping($itemShippingArray);

                        // Handle discount usage
                        $usedCouponCode = $coupon_code ?? collect($storeData['products'])->firstWhere('on_sale', 1)['discount_id'] ?? null;

                        if ($usedCouponCode) {
                            $discount = Discount::where('code', $usedCouponCode)->orWhere('id', $usedCouponCode)->first();
                            if ($discount) {
                                $discount->usage_count += 1;
                                $discount->save();

                                DiscountUsage::create([
                                    'discount_id' => $discount->id,
                                    'user_id' => $userId,
                                    'order_id' => $orderId,
                                    'store_id' => $storeId,
                                    'used_at' => now(),
                                    'usage_count' => 1
                                ]);
                            }
                        }

                       $logData = [
                            'event_type' => 'Order Created Successfully',
                            'session_id' => $checkoutSession->id,
                            'data' => json_encode(['Order Created Successfully' => $orderId]),
                        ];
                        $log = CoreHelpers::checkoutLog($logData);

                    } catch (Exception $e) {
                        DB::rollBack();
                        $logData = [
                            'event_type' => 'Order creation failed',
                            'session_id' => $checkoutSession->id,
                            'data' => json_encode(['store_id' => $storeId, 'error' => $e->getMessage()]),
                        ];
                        $log = CoreHelpers::checkoutLog($logData);
                        throw new \Exception('Order failed: ' . $e->getMessage());
                    }
                }
            });

            if (isset($request['platform'])) {
                if ($request['platform'] == 'web') {
                    $response = ['id' => $checkoutSession];
                } elseif ($request['platform'] == 'mobile') {
                    $response = $checkoutSession;
                }
                return $response;
            }

        } catch (Exception $e) {
            $logData = [
                'event_type' => 'Order creation failed',
                'session_id' => $checkoutSession->id,
                'data' => json_encode(['message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]),
            ];
            $log = CoreHelpers::checkoutLog($logData);
            throw new \Exception('Order processing failed: ' . $e->getMessage());
        }
    }

        public function processOrderV3($checkoutSession, $productData, $request, $userId)
    {
        try {
            DB::transaction(function () use ($checkoutSession, $productData, $userId, $request) {
                $customerShipping = $request['shipping_data'];
                $country = CoreHelpers::getCountryByCode($customerShipping['country']);
                $state = CoreHelpers::getStatebyCode($customerShipping['state']);

                $shippingAddress = $this->createShippingAddressV3(
                    $customerShipping['full_name'],
                    $country['id'],
                    $state['state_id'],
                    $customerShipping['address_1'],
                    $customerShipping['zip_code'],
                    $customerShipping['city'],
                    $userId,
                    $customerShipping['address_2'],
                    $customerShipping['phone_no']
                )->sh_id;

                foreach ($productData as $storeId => $storeData) {
                    try {
                        $orderAmount = 0;
                        $orderShippingAmount = 0;
                        $totalOrderItems = 0;
                        foreach ($storeData['products'] as $product) {
                             $totalOrderItems++;
                             $couponCode = $product['coupon_code'] ?? null;
                            $quantity = is_numeric($product['quantity']) ? (int)$product['quantity'] : 1;
                            if(isset($product['whole_sale']) && !$product['whole_sale']){
                                $originalPrice = $product['price'];
                                $salePrice = $product['sale_price'] ?? 0;
                                
                                // if coupon exists then calculate price according to coupon value
                                $productOrderPrice = $quantity * ($salePrice > 0 ? $salePrice : $originalPrice);
                            }else{
                                // wholesale product tier price calculation
                                if($product['product_type'] === 'variant'){
                                    $tierDetails = CartHelpers::calculateTierDetailsV3($product['product_id'], $product['variation_id'], $quantity);
                                    if ($tierDetails['total_price'] > 0) {
                                        $productOrderPrice = $tierDetails['total_price'];
                                    }
                                }else{
                                    $tierDetails = CartHelpers::calculateTierDetailsV3($product['product_id'], null, $quantity);

                                    if ($tierDetails['total_price'] > 0) {
                                        $productOrderPrice = $tierDetails['total_price'];
                                    }
                                }
                            }
                            $shippingCharges = $product['shipping_detail']['shipping_amount'] ?? 0;
                                // include any customizations price (only for custom products)
                                if (!empty($product['is_custom']) && $product['is_custom'] == 1) {
                                    $totalCustomPrice = 0;

                                    if (!empty($product['customizations'])) {
                                        $custom = $product['customizations'];

                                        if (isset($custom['price'])) {
                                            $customPrice = (float) ($custom['price'] ?? 0);
                                            $customQuantity = (int) ($custom['product_quantity'] ?? $quantity);
                                            $totalCustomPrice = $customPrice * $customQuantity;
                                        }
                                    }

                                    if ($totalCustomPrice > 0) {
                                        $productOrderPrice += $totalCustomPrice;
                                    }
                                }
                            $orderAmount += $productOrderPrice + $shippingCharges;
                            $orderShippingAmount += $shippingCharges;
                        }
                        // Calculate stripe fees and platform fees
                        $stripeFee = ($orderAmount * 0.029) + 0.33;
                        $platformFeePercent = optional(StoreDeduction::where('store_id', $storeId)->first())->mall_shark_amount ?? env('MALLSHARK_DEDUCTION');
                        $platformFee = ($platformFeePercent / 100) * $orderAmount;
                        $payableAmount = $orderAmount - ($stripeFee + $platformFee);
                        // Order creation for every single store
                        $storeOrder = new StoreOrder([
                            'buyer_id' => $userId,
                            'payment_method' => 'stripe',
                            'payment_status' => 'pending',
                            'charge_status' => 'unpaid',
                            'platform_fee' => $platformFee,
                            'payable_amount' => $payableAmount,
                            'order_status' => 'pending',
                            'total_amount' => $orderAmount,
                            'total_items' => $totalOrderItems,
                            'shipping_address' => $shippingAddress,
                            'shipping_method' => '',
                            'transaction_id' => '',
                            'session_id' => $checkoutSession->id,
                            'balance_transaction' => '',
                            'payerID' => '',
                            'shipping_amount' => $orderShippingAmount,
                            'store_id' => $storeId
                        ]);
                        $storeOrder->save();

                        $orderId = $storeOrder->order_id;
                        $itemsArray = [];
                        $itemShippingArray = [];

                        foreach ($storeData['products'] as $product) {
                            $salePrice = 0;
                            $orderItemPrice = 0;
                            $couponCode = $product['coupon_code'] ?? null;
                            $quantity = is_numeric($product['quantity']) ? (int)$product['quantity'] : 1;
                            if(isset($product['whole_sale']) && !$product['whole_sale']){
                                $originalPrice = $product['price'];
                                $salePrice = $product['sale_price'] ?? 0;
                                
                                $orderItemPrice = $quantity * ($salePrice > 0 ? $salePrice : $originalPrice);
                            }else{
                                if($product['product_type'] === 'variant'){
                                    $tierDetails = CartHelpers::calculateTierDetailsV3($product['product_id'], $product['variation_id'], $quantity);
                                    if ($tierDetails['total_price'] > 0) {
                                        $orderItemPrice = $tierDetails['total_price'];
                                        $originalPrice = $tierDetails['unit_price'];
                                    }
                                }else{
                                    $tierDetails = CartHelpers::calculateTierDetailsV3($product['product_id'], null, $quantity);

                                    if ($tierDetails['total_price'] > 0) {
                                        $orderItemPrice = $tierDetails['total_price'];
                                        $originalPrice = $tierDetails['unit_price'];
                                    }
                                }
                                $product['discount_id'] = null;
                            }
                            // include any customizations/custom images price (only for custom products)
                            if (!empty($product['is_custom']) && $product['is_custom'] == 1) {
                                $totalCustomPrice = 0;
                                if (!empty($product['customizations'])) {
                                    $custom = $product['customizations'];
                                    if (isset($custom['price'])) {
                                        $customPrice = ($custom['price'] ?? 0);
                                        $customQuantity = ($custom['product_quantity'] ?? $quantity);
                                        $totalCustomPrice = $customPrice * $customQuantity;
                                    }
                                }
                                if ($totalCustomPrice > 0) {
                                    $orderItemPrice += $totalCustomPrice;
                                }
                            }
                            $shippingCharges = $product['shipping_detail']['shipping_amount'] ?? 0;
                            $totalPerItem = $orderItemPrice + $shippingCharges;

                            $variationInfo = '';
                            if (!empty($product['variation_id'])) {
                                $variationInfo = getVariationNameById($product['variation_id']);
                            }

                            $imageId = (int)($product['image_id'] ?? 0);
                            $imageUrl = getAttachmentUrlById($imageId, 'medium') ?? getAttachmentUrlById($imageId, 'small');
                            $newImagePath = $imageUrl ? copyImageFromUrlToNewFolder($imageUrl) : '';
                            if (!empty($product['is_custom']) && $product['is_custom'] == 1 && !empty($product['customizations'])) { 
                            $customImage = $product['customizations']['file_name'] ?? '';
                            $customImageUrl = getAttachmentUrlByName($customImage);
                            $newcustomImagePath = $customImageUrl ? copyImageFromUrlToNewFolder($customImageUrl) : '';
                            // Remove old image_url if exists
                            if (isset($product['customizations']['image_url'])) {
                                unset($product['customizations']['image_url']);
                            }    
                            $product['customizations']['image_url'] = $newcustomImagePath;
                            }

                            $itemDimensions = CoreHelpers::getProductDimensions($product['product_id'], $product['variation_id'] ?? null);

                            $itemsArray[] = [
                                'order_id' => $orderId,
                                'store_id' => $product['store_id'],
                                'item_id' => $product['product_id'],
                                'sold_price' => $salePrice ?? 0,
                                'sale_price' => $salePrice ?? 0,
                                'sale_id' => $product['discount_id'] ?? null,
                                'variation_id' => $product['variation_id'] ?? null,
                                'quantity' => $quantity,
                                'price' => $originalPrice,
                                'shipping_amount' => $shippingCharges,
                                'total' => $totalPerItem,
                                'product_name' => $product['title'] ?? 'N/A',
                                'variation_info' => $variationInfo,
                                'item_image' => $newImagePath,
                                'created_at' => now(),
                                'customizations' => (!empty($product['is_custom']) && $product['is_custom'] == 1 && !empty($product['customizations'])) 
                                    ? json_encode($product['customizations']) 
                                    : null,
                                'weight'          => $itemDimensions['weight']['value'] ?? 0,
                                'weight_type'     => $itemDimensions['weight']['units'] ?? null,
                                'length'          => $itemDimensions['dimensions']['length'] ?? 0,
                                'width'           => $itemDimensions['dimensions']['width'] ?? 0,
                                'height'          => $itemDimensions['dimensions']['height'] ?? 0,
                                'dimension_type'  => $itemDimensions['dimensions']['units'] ?? null,
                            ];
                            if(isset($product['whole_sale']) && !$product['whole_sale']){
                                $itemShippingArray[] = [
                                    'shipping_profile_id' => $product['shipping_detail']['shipping_profile_id'],
                                    'order_id' => $orderId,
                                    'product_id' => $product['product_id'],
                                    'shipping_amount' => $shippingCharges,
                                    'base_price' => $product['shipping_detail']['base_price'] ?? 0,
                                    'handlilng_fee' => $product['shipping_detail']['handling_fee'] ?? 0,
                                    'delivery_time' => $product['shipping_detail']['delivery_time'],
                                    'shipping_method' => $product['shipping_detail']['shipping_method'],
                                    'service_id' => $product['shipping_detail']['service_id'],
                                    'service_slug' => $product['shipping_detail']['service_slug'],
                                    'service_name' => $product['shipping_detail']['service_name'],
                                    'created_at' => now(),
                                    'updated_at' => now(),
                                ];
                            }
                        }

                        $this->addOrderItems($itemsArray);
                        if(isset($product['whole_sale']) && !$product['whole_sale']){
                            $this->addItemShipping($itemShippingArray);
                        }
                        // Handle discount usage
                        // $usedCouponCode = $couponCode ?? collect($storeData['products'])->firstWhere('on_sale', 1)['discount_id'] ?? null;

                        if ($couponCode) {
                            $coupon = Discount::where(['code' => $couponCode,'store_id' => $storeId,'status' => 'active','state' => 'live'])->first();
                            if ($coupon) {
                                DiscountUsage::create([
                                    'discount_id' => $coupon->id,
                                    'user_id' => $userId,
                                    'order_id' => $orderId,
                                    'store_id' => $storeId,
                                    'used_at' => now(),
                                    'usage_count' => 0
                                ]);
                            }
                        }

                       $logData = [
                            'event_type' => 'Order Created Successfully',
                            'session_id' => $checkoutSession->id,
                            'data' => json_encode(['Order Created Successfully' => $orderId]),
                        ];
                        $log = CoreHelpers::checkoutLogV3($logData);

                    } catch (Exception $e) {
                        DB::rollBack();
                        $logData = [
                            'event_type' => 'Order creation failed',
                            'session_id' => $checkoutSession->id,
                            'data' => json_encode(['store_id' => $storeId, 'error' => $e->getMessage()]),
                        ];
                        $log = CoreHelpers::checkoutLog($logData);
                        throw new \Exception('Order failed: ' . $e->getMessage());
                    }
                }
            });
            if (isset($request['platform'])) {
                if ($request['platform'] == 'web') {
                    $response = ['id' => $checkoutSession];
                } elseif ($request['platform'] == 'mobile') {
                    $response = $checkoutSession;
                }
                return $response;
            }

        } catch (Exception $e) {
            $logData = [
                'event_type' => 'Order creation failed',
                'session_id' => $checkoutSession->id,
                'data' => json_encode(['message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]),
            ];
            $log = CoreHelpers::checkoutLogV3($logData);
            throw new \Exception('Order processing failed: ' . $e->getMessage());
        }
    }

    public function processOrderCrypto($productData, $request, $userId, $checkoutUserId, $grandTotal)
    {
        try {
            return DB::transaction(function () use ($productData, $request, $userId, $checkoutUserId, $grandTotal) {

                $customerShipping = $request['shipping_data'];
                $country = CoreHelpers::getCountryByCode($customerShipping['country']);
                $state = CoreHelpers::getStatebyCode($customerShipping['state']);
                
                $shippingAddress = $this->createShippingAddressV2(
                    $customerShipping['full_name'],
                    $country['id'],
                    $state['state_id'],
                    $customerShipping['address_1'],
                    $customerShipping['zip_code'],
                    $customerShipping['city'],
                    $userId,
                    $customerShipping['address_2'],
                    $customerShipping['phone_no']
                )->sh_id;

                $orderIds = [];

                foreach ($productData as $storeId => $storeData) {
                    try {
                        $orderAmount = 0;
                        $orderShippingAmount = 0;
                        $couponCode = $storeData['coupon_code'] ?? null;
                        $totalOrderItems = 0;

                        foreach ($storeData['products'] as $product) {
                            
                            $totalOrderItems++;
                            $quantity = is_numeric($product['quantity']) ? (int)$product['quantity'] : 1;
                            if(isset($product['whole_sale']) && !$product['whole_sale']){
                                 $originalPrice = $product['price'];
                                $salePrice = $product['sale_price'] ?? 0;
                                $productOrderPrice = $quantity * ($salePrice > 0 ? $salePrice : $originalPrice);

                            }else{
                                 // wholesale product tier price calculation
                                if($product['product_type'] === 'variant'){
                                    $tierDetails = CartHelpers::calculateTierDetails($product['product_id'], $product['variation_id'], $quantity);
                                    if ($tierDetails['total_price'] > 0) {
                                        $productOrderPrice = $tierDetails['total_price'];
                                    }
                                }else{
                                    $tierDetails = CartHelpers::calculateTierDetails($product['product_id'], null, $quantity);

                                    if ($tierDetails['total_price'] > 0) {
                                        $productOrderPrice = $tierDetails['total_price'];
                                    }
                                }
                            }
                            $shippingCharges = $product['shipping_detail']['shipping_amount'] ?? 0;
                                if (!empty($product['is_custom']) && $product['is_custom'] == 1) {
                                    $totalCustomPrice = 0;
                                    if (!empty($product['customizations'])) {
                                        $custom = $product['customizations'];
                                        if (isset($custom['price'])) {
                                            $customPrice = ($custom['price'] ?? 0);
                                            $customQuantity = ($custom['product_quantity'] ?? $quantity);
                                            $totalCustomPrice = $customPrice * $customQuantity;
                                        }
                                    }
                                    if ($totalCustomPrice > 0) {
                                        // totalCustomPrice already accounts for per-customization counts; add as-is (dollars)
                                        $productOrderPrice += $totalCustomPrice;
                                    }
                                }
                            $orderAmount += $productOrderPrice + $shippingCharges;
                            $orderShippingAmount += $shippingCharges;
                        }
                        $platformFeePercent = optional(StoreDeduction::where('store_id', $storeId)->first())->mall_shark_amount ?? env('MALLSHARK_DEDUCTION');
                        $platformFee = ($platformFeePercent / 100) * $orderAmount;
                        $payableAmount = $orderAmount - $platformFee;

                        $storeOrder = new StoreOrder([
                            'buyer_id' => $userId,
                            'payment_method' => 'crypto',
                            'payment_status' => 'pending',
                            'charge_status' => 'unpaid',
                            'platform_fee' => $platformFee,
                            'payable_amount' => $payableAmount,
                            'order_status' => 'pending',
                            'total_amount' => $orderAmount,
                            'total_items' => $totalOrderItems,
                            'shipping_address' => $shippingAddress,
                            'shipping_method' => '',
                            'transaction_id' => '',
                            'session_id' => null,
                            'balance_transaction' => '',
                            'payerID' => '',
                            'shipping_amount' => $orderShippingAmount,
                            'store_id' => $storeId,
                        ]);
                        $storeOrder->save();

                        $orderId = $storeOrder->order_id;
                        $orderIds[] = $orderId;

                        // create items and shipping data
                        $itemsArray = [];
                        $itemShippingArray = [];

                        foreach ($storeData['products'] as $product) {
                            $salePrice = 0;
                            $orderItemPrice = 0;
                            $quantity = is_numeric($product['quantity']) ? (int)$product['quantity'] : 1;
                            if(isset($product['whole_sale']) && !$product['whole_sale']){
                                $originalPrice = $product['price'];
                                $salePrice = $product['sale_price'] ?? 0;
                                $orderItemPrice = $quantity * ($salePrice > 0 ? $salePrice : $originalPrice);
                            }else{
                                if($product['product_type'] === 'variant'){
                                    $tierDetails = CartHelpers::calculateTierDetails($product['product_id'], $product['variation_id'], $quantity);
                                    if ($tierDetails['total_price'] > 0) {
                                        $orderItemPrice = $tierDetails['total_price'];
                                        $originalPrice = $tierDetails['unit_price'];
                                    }
                                }else{
                                    $tierDetails = CartHelpers::calculateTierDetails($product['product_id'], null, $quantity);

                                    if ($tierDetails['total_price'] > 0) {
                                        $orderItemPrice = $tierDetails['total_price'];
                                        $originalPrice = $tierDetails['unit_price'];
                                    }
                                }
                                $product['discount_id'] = null;
                            }
                             // include any customizations/custom images price (only for custom products)
                            if (!empty($product['is_custom']) && $product['is_custom'] == 1) {
                                $totalCustomPrice = 0;
                                if (!empty($product['customizations'])) {
                                    $custom = $product['customizations'];
                                    if (isset($custom['price'])) {
                                        $customPrice = ($custom['price'] ?? 0);
                                        $customQuantity = ($custom['product_quantity'] ?? $quantity);
                                        $totalCustomPrice = $customPrice * $customQuantity;
                                    }
                                }
                                if ($totalCustomPrice > 0) {
                                    // totalCustomPrice already accounts for per-customization counts; add as-is (dollars)
                                    $orderItemPrice += $totalCustomPrice;
                                }
                            }
                            $shippingCharges = $product['shipping_detail']['shipping_amount'] ?? 0;
                            $totalPerItem = $orderItemPrice + $shippingCharges;

                            $variationInfo = '';
                            if (!empty($product['variation_id'])) {
                                $variationInfo = getVariationNameById($product['variation_id']);
                            }

                            $imageId = (int)($product['image_id'] ?? 0);
                            $imageUrl = getAttachmentUrlById($imageId, 'medium') ?? getAttachmentUrlById($imageId, 'small');
                            $newImagePath = $imageUrl ? copyImageFromUrlToNewFolder($imageUrl) : '';
                            if (!empty($product['is_custom']) && $product['is_custom'] == 1 && !empty($product['customizations'])) { 
                            $customImage = $product['customizations']['file_name'] ?? '';
                            $customImageUrl = getAttachmentUrlByName($customImage);
                            $newcustomImagePath = $customImageUrl ? copyImageFromUrlToNewFolder($customImageUrl) : '';
                            // Remove old image_url if exists
                            if (isset($product['customizations']['image_url'])) {
                                unset($product['customizations']['image_url']);
                            }    
                            $product['customizations']['image_url'] = $newcustomImagePath;
                            }
                            $itemsArray[] = [
                                'order_id' => $orderId,
                                'store_id' => $product['store_id'],
                                'item_id' => $product['product_id'],
                                'sold_price' => $salePrice ?? 0,
                                'sale_price' => $salePrice ?? 0,
                                'sale_id' => $product['discount_id'] ?? null,
                                'variation_id' => $product['variation_id'] ?? null,
                                'quantity' => $quantity,
                                'price' => $originalPrice,
                                'shipping_amount' => $shippingCharges,
                                'total' => $totalPerItem,
                                'product_name' => $product['title'] ?? 'N/A',
                                'variation_info' => $variationInfo,
                                'item_image' => $newImagePath,
                                'customizations' => (!empty($product['is_custom']) && $product['is_custom'] == 1 && !empty($product['customizations'])) 
                                    ? json_encode($product['customizations']) 
                                    : null,
                                'created_at' => now(),
                            ];
                            if(isset($product['whole_sale']) && !$product['whole_sale']){
                                if (isset($product['shipping_detail']['shipping_profile_id'])) {
                                    $itemShippingArray[] = [
                                        'shipping_profile_id' => $product['shipping_detail']['shipping_profile_id'] ?? null,
                                        'order_id' => $orderId,
                                        'product_id' => $product['product_id'],
                                        'shipping_amount' => $shippingCharges,
                                        'base_price' => $product['shipping_detail']['base_price'] ?? 0,
                                        'handlilng_fee' => $product['shipping_detail']['handling_fee'] ?? 0,
                                        'delivery_time' => $product['shipping_detail']['delivery_time'],
                                        'shipping_method' => $product['shipping_detail']['shipping_method'],
                                        'service_id' => $product['shipping_detail']['service_id'],
                                        'service_slug' => $product['shipping_detail']['service_slug'],
                                        'service_name' => $product['shipping_detail']['service_name'],
                                        'created_at' => now(),
                                        'updated_at' => now(),
                                    ];
                                    $this->addItemShipping($itemShippingArray);
                                }
                            }
                        }

                        $this->addOrderItems($itemsArray);

                        // $usedCouponCode = $couponCode ?? collect($storeData['products'])->firstWhere('on_sale', 1)['discount_id'] ?? null;

                        if ($couponCode) {
                            $coupon = Discount::where(['code' => $couponCode,'store_id' => $storeId,'status' => 'active','state' => 'live'])->first();
                            if ($coupon) {
                                DiscountUsage::create([
                                    'discount_id' => $coupon->id,
                                    'user_id' => $userId,
                                    'order_id' => $orderId,
                                    'store_id' => $storeId,
                                    'used_at' => now(),
                                    'usage_count' => 0
                                ]);
                            }
                        }
                        $logData = [
                            'event_type' => 'Order Created Successfully',
                            'session_id' => $checkoutUserId,
                            'data' => json_encode(['Order Created Successfully' => $orderId]),
                        ];
                        $log = CoreHelpers::checkoutLog($logData);
                    } catch (Exception $e) {
                        DB::rollBack();
                        $logData = [
                            'event_type' => 'Order creation failed',
                            'session_id' => $checkoutUserId,
                            'data' => json_encode(['store_id' => $storeId, 'error' => $e->getMessage()]),
                        ];
                        $log = CoreHelpers::checkoutLog($logData);
                        throw new \Exception('Order failed: ' . $e->getMessage());
                    }
                }

                $invoice = $this->CryptoService->createInvoice($grandTotal, $orderIds);
                if($request['buy_now'] == false){
                    if (isset($request['guest']) && $request['guest'] == 'true') {
                        $updateCart = ProductCart::where('random_key', $request['id'])->update(['crypto_random' => $invoice['id']]);
                    }else{
                        $updateCart = ProductCart::where('user_id', $request['id'])->update(['crypto_random' => $invoice['id']]);
                    }
                }
                StoreOrder::whereIn('order_id', $orderIds)->update([
                    'session_id' => $invoice['id'] ?? null,
                ]);
                $logData = [
                    'event_type' => 'crypto Invoice Created Successfully',
                    'session_id' => $checkoutUserId,
                    'data' => json_encode(['crypto_invoice_created' => $orderIds]),
                ];
                $log = CoreHelpers::checkoutLog($logData);
                return $invoice;
            });

        } catch (Exception $e) {
            $logData = [
                'event_type' => 'Order creation failed',
                'session_id' => $checkoutUserId,
                'data' => json_encode(['message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]),
            ];
            $log = CoreHelpers::checkoutLog($logData);
            throw new \Exception('Order processing failed: ' . $e->getMessage());
        }
    }

    public function addOrderItems($item_array = array())
    {
        OrderItems::insert($item_array);
    }

    public function addItemShipping($item_array = array())
    {
        OrderShipping::insert($item_array);
    }

    public function constructEvent($payload, $sigHeader)
    {
        try {
            $secret = env('STRIPE_WEBHOOK_SECRET');

            return Webhook::constructEvent(
                $payload,
                $sigHeader,
                $secret
            );

        } catch (\UnexpectedValueException $e) {
            $paymentLog = new Logger('');
            $paymentLog->pushHandler(new StreamHandler(storage_path('logs/checkout_payment.log')), Logger::ERROR);
            $paymentLog->error('Failed', ['Message' => $e->getMessage()]);

            throw new \UnexpectedValueException('Invalid payload: '. $e->getMessage());
            // return response()->json(['error' => 'Invalid payload'], 400);
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            $paymentLog = new Logger('');
            $paymentLog->pushHandler(new StreamHandler(storage_path('logs/checkout_payment.log')), Logger::ERROR);
            $paymentLog->error('Failed', ['Message' => $e->getMessage()]);
            // return response()->json(['error' => 'Invalid signature'], 400);
            throw new \Stripe\Exception\SignatureVerificationException('Invalid signature: '. $e->getMessage());
        }
    }
    public function constructEventV2($payload, $sigHeader)
    {
        try {
            $secret = env('STRIPE_WEBHOOK_SECRET_NEW');

            return Webhook::constructEvent(
                $payload,
                $sigHeader,
                $secret
            );

        } catch (\UnexpectedValueException $e) {
            $paymentLog = new Logger('');
            $paymentLog->pushHandler(new StreamHandler(storage_path('logs/checkout_payment.log')), Logger::ERROR);
            $paymentLog->error('Failed', ['Message' => $e->getMessage()]);

            throw new \UnexpectedValueException('Invalid payload: '. $e->getMessage());
            // return response()->json(['error' => 'Invalid payload'], 400);
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            $paymentLog = new Logger('');
            $paymentLog->pushHandler(new StreamHandler(storage_path('logs/checkout_payment.log')), Logger::ERROR);
            $paymentLog->error('Failed', ['Message' => $e->getMessage()]);
            // return response()->json(['error' => 'Invalid signature'], 400);
            throw new \Stripe\Exception\SignatureVerificationException('Invalid signature: '. $e->getMessage());
        }
    }
    public function createOrUpdateUserWithSession($user, $session)
    {
        $customerEmail = $session['customer_details']['email'];
        $customerName = $session['customer_details']['name'];
        $loginKey = str_random(25);

        if (!$user) {
            $customer = Customer::create([
                'email' => $customerEmail,
                'name' => $customerName,
            ]);

            $user = new User();
            $user->name = $session['customer_details']['name'] ?? $session['collected_information']['shipping_details']['name'];
            $user->email = $customerEmail;
            $user->customer_id = $customer->id;
            $user->password = md5('mallshark123456');
            $now = Carbon::now();
            $user->forget_key_date = $now;
            $user->forget_key = $loginKey;
            $user->register_source = 'MallShark';
            $user->contact_no = $session['customer_details']['phone'];
            $user->guest_user = 1;
            $user->save();
            $userRole = new Userrole();
            $userRole->user_id = $user->user_id;
            $userRole->role_id = 4;
            $userRole->save();
        } else {
            $customer = $this->findOrCreateStripeCustomer($user);
            $now = Carbon::now();
            $user->forget_key_date = $now;
            $user->forget_key = $loginKey;
            $user->save();
        }

        return $user;
    }

    public function createShippingAddress($fullName, $countryId, $stateId, $street, $postCode, $city, $userId,$apartment)
    {
        $shippingAddress = new ShippingAddress();
        $shippingAddress->full_name = $fullName;
        $shippingAddress->country = $countryId;
        $shippingAddress->state = $stateId;
        $shippingAddress->street = $street;
        $shippingAddress->post_code = $postCode;
        $shippingAddress->city = $city;
        $shippingAddress->user_id = $userId;
        $shippingAddress->apartment_no = $apartment;
        $shippingAddress->save();
        
        return $shippingAddress;
    }
    public function createShippingAddressV2($fullName, $countryId, $stateId, $street, $postCode, $city, $userId, $appartmentNumber = null,$customerPhone)
    {
        $shippingAddress = new ShippingAddress();
        $shippingAddress->full_name = $fullName;
        $shippingAddress->country = $countryId;
        $shippingAddress->state = $stateId;
        $shippingAddress->street = $street;
        $shippingAddress->post_code = $postCode;
        $shippingAddress->city = $city;
        $shippingAddress->user_id = $userId;
        $shippingAddress->apartment_no = $appartmentNumber;
        $shippingAddress->contact_no = $customerPhone;
        $shippingAddress->save();
        
        return $shippingAddress;
    }

    public function createShippingAddressV3($fullName, $countryId, $stateId, $street, $postCode, $city, $userId, $appartmentNumber = null,$customerPhone)
    {
        $shippingAddress = new ShippingAddress();
        $shippingAddress->full_name = $fullName;
        $shippingAddress->country = $countryId;
        $shippingAddress->state = $stateId;
        $shippingAddress->street = $street;
        $shippingAddress->post_code = $postCode;
        $shippingAddress->city = $city;
        $shippingAddress->user_id = $userId;
        $shippingAddress->apartment_no = $appartmentNumber;
        $shippingAddress->contact_no = $customerPhone;
        $shippingAddress->save();
        
        return $shippingAddress;
    }

    public function getOrdersBySessionId($sessionId)
    {
        return StoreOrder::with('orderItems')->where('session_id',$sessionId)->get();
    }

    public function getDiscountUsageByOrderId($orderId)
    {
        return DiscountUsage::where('order_id', $orderId)->first();
    }

    public function createStoreEscrow($orderId, $storeId, $storeWallet, $totalAmount, $shippingAmount, $amountWithOutShip, $payableAmount, $paymentProfit)
    {
        $storeEscrew = new StoreEscrow();
        $storeEscrew->order_id = $orderId;
        $storeEscrew->store_id = $storeId;
        $storeEscrew->store_wallet_address = $storeWallet;
        $storeEscrew->order_price = $totalAmount;
        $storeEscrew->ship_price =  $shippingAmount;
        $storeEscrew->order_price_with_out_shipping =  $amountWithOutShip;
        $storeEscrew->payable_amount =  $payableAmount;
        $storeEscrew->profit =  $paymentProfit;
        $storeEscrew->status =  'pending';
        $storeEscrew->profit_status =  'pending';
        $storeEscrew->payment_date = date('Y-m-d');
        $storeEscrew->save();
        return $storeEscrew;
    }

    function deleteCartByIntentMetadata($metadata)
    {
        if (!isset($metadata['user_id'])) {
        return false;
        }

        if (isset($metadata['guest']) && $metadata['guest'] === 'true') {
            return ProductCart::where('random_key', $metadata['user_id'])->delete();
        }

        return ProductCart::where('user_id', $metadata['user_id'])->delete();
        return false;
    }
    function deleteCartByIntentMetadataV2($metadata)
    {
        if($metadata['buy_now'] == 'false'){
            if (!isset($metadata['user_id'])) {
            return false;
            }

            if (isset($metadata['guest']) && $metadata['guest'] == 'true') {
                return ProductCart::where('random_key', $metadata['user_id'])->delete();
            }

            return ProductCart::where('user_id', $metadata['user_id'])->delete();
        }
        return false;
        
    }
    function deleteCartByCryptoKey($key)
    {
        $cart = ProductCart::where('crypto_random', $key)->exists();
        if ($cart) {
            return ProductCart::where('crypto_random', $key)->delete();
        }
        return false;
    }
    function getVariationsInfo(array $options, int $productId)
    {
        $query = $this->productVariationModel->where('product_id', $productId);

        // Dynamically apply filters
        if (!empty($options[0])) {
            $query->where('option1', $options[0]);
        }

        if (!empty($options[1])) {
            $query->where('option2', $options[1]);
        }

        if (!empty($options[2])) {
            $query->where('option3', $options[2]);
        }

        return $query->first();
    }
    function structureBuyNowProduct($data){
        $product = Product::with(['store', 'shippingOption'])->findOrFail($data['product_id']);
            $variation = $data['variation_id'] 
                ? ProductVariation::find($data['variation_id']) 
                : null;
            $store = Store::findOrFail($data['store_id']);
            $allData = [];

            $array = [];
            $array['usps_services'] = ['First', 'Priority', 'Express'];
            $array['product_id'] = $product->product_id;
            $array['store_id'] = $data['store_id'];
            $array['origin_zip'] = $store->zip_code;
            $array['user_id'] = $data['user_id'];
            $array['title'] = $product->title;
            $array['price'] = $product->price;
            $array['on_sale'] = $product->on_sale;
            $array['sale_id'] = $product->discount_id;
            $array['sale_price'] = $product->sale_price;
            $array['slug'] = $product->slug;
            $array['feature_image'] = $product->feature_image;
            $array['cart_id'] = null;
            $array['product_quantity'] = $data['product_quantity'];
            $array['variation_sale_price'] = $variation?->price ?? "";
            $array['product_variation_sale_price'] = $variation?->sale_price ?? $array['variation_sale_price'];
            $array['variation_discount_percentage'] = $variation?->discount_percentage ?? 0;
            $array['variation_id'] = $variation?->variation_id ?? "";
            $array['product_variation_image'] = $variation?->product_variation_image ?? "";
            $array['option1'] = $variation?->option1 ?? "";
            $array['attribute1'] = $variation?->attribute1 ?? "";
            $array['attribute2'] = $variation?->attribute2 ?? "";
            $array['attribute3'] = $variation?->attribute3 ?? "";
            $array['unit1'] = $variation?->unit1 ?? "";
            $array['option2'] = $variation?->option2 ?? "";
            $array['option3'] = $variation?->option3 ?? "";
            $array['unit2'] = $variation?->unit2 ?? "";
            $array['unit3'] = $variation?->unit3 ?? "";
            $array['product_variation_quantity'] = $variation?->product_variation_quantity ?? "";

            $array['productSize_length'] = $product->productSize_length ?? "";
            $array['productSize_width'] = $product->productSize_width ?? "";
            $array['productSize_height'] = $product->productSize_height ?? "";
            $array['weight'] = $product->weight ? $product->weight * $data['product_quantity'] : "";
            $array['shipping_option_id'] = $product->shipping_option_id ?? "";
            $array['domestic_handling_fee'] = $product->shippingOption->domestic_handling_fee ?? "";
            $array['flat_shipping_rate'] = $product->shippingOption->flat_shipping_rate ?? "";
            $array['is_domestic_free'] = $product->shippingOption->is_domesticfree ?? "";
            $array['profile_name'] = $product->shippingOption->profile_name ?? "";
            $array['origin_zipcode'] = $product->shippingOption->origin_zipcode ?? "";
            $all_services = isset($product->shippingOption->shipping_service) ? explode(",", $product->shippingOption->shipping_service) : [];
            $array['shipping_service'] = $all_services;

            $array['product_type'] = $product->product_type;
            $array['dimention_type'] = $product->dimention_type;
            $array['weight_type'] = $product->weight_type;
            $array['store_country'] = $store->store_country;
            $array['store_state'] = $store->store_state;
            $array['store_city'] = $store->store_city;
            $array['store_wallet_address'] = $store->wallet_address;
            $array['stripe_account_id'] = $store->account_id;
            $array['stripe'] = $store->stripe;
            $array['store_zip_code'] = $product->shippingOption->origin_zipcode ?? "";
            $array['store_contact_info'] = $store->contact_info;
            $array['ship_id'] = "";
            $array['rate_id'] = "";
            $array['store_data'] = [];
            $array['user_info'] = [];
            $array['store_total'] = "";
            $array['product_tax'] = 0;

            // Handle Shipping Logic
            if (!empty($array['shipping_option_id'])) {
                $query = getShippingOptionById($array['shipping_option_id']);
                if ($query && $query['is_domesticfree'] == 0) {
                    if ($query['flat_shipping_rate'] > 0) {
                        $array['shipping_method'] = 'flat';
                        $array['method'] = 'flat';
                        $array['shipping_price'] = (float) $query['flat_shipping_rate'];
                    } else {
                        $array['shipping_method'] = 'Priority';
                        $array['method'] = 'calculated';
                        $array['shipping_price'] = 0;
                    }
                } else {
                    $array['shipping_method'] = 'free';
                    $array['method'] = 'free';
                    $array['shipping_price'] = 0;
                }
            } else {
                if ($array['product_type'] === 'virtual') {
                    $array['shipping_method'] = '';
                    $array['method'] = 'free';
                    $array['shipping_price'] = 0;
                }
            }

            $array['expected-delivery-date'] = 0;

            $allData[] = $array;
            $finalData = [];
            foreach ($allData as $item) {
                $finalData[$item['store_id']][] = $item;
            }
            return $finalData;
    }
    function makeCheckout($checkoutData,$newCartItem){
       try{
            $userId = $newCartItem['user_id'];
            $cartId = null;
            $guest = $newCartItem['guest'] ?? false;
            $user = null;
            $customerId = null;
            $customer = null;
            if($guest == false){
                $user = $this->getUserById($userId);
                if ($user) {
                    $customer = $this->findOrCreateStripeCustomer($user);
                    $customerId = $customer->id;
                }
            }
            $storeIds = [];
            $productIds = [];
            $lineItems = [];
            $totalDiscount = 0;
            $totalShipping = 0; // Total shipping calculation
            $subTotal = 0; // Subtotal without discount
            $grandTotal = 0; // Total with discount and shipping
            $totalDue = 0; // Subtotal - Discount
            
            foreach ($checkoutData as $products) {
                foreach ($products as $product) {
                    $discountAmount = 0;
                    $salePrice = 0;
                    if ($product['product_type'] == 'variant') {
                        $regularPrice = isset($product['variation_sale_price']) ? round((float)$product['variation_sale_price'],2) : 0;
                        $imageUrl = getAttachmentUrlById((int)$product['product_variation_image']);

                        if (!$imageUrl) {
                            $imageUrl = getAttachmentUrlById($product['feature_image']);
                        }
                        $shippingCost = isset($product['shipping_price']) ? round((float)$product['shipping_price'],2) : 0;

                        // Calculate discount per product
                        if ($product['on_sale']) {
                            $salePrice = isset($product['product_variation_sale_price']) ? round((float)$product['product_variation_sale_price'],2) : 0;
                            $discountAmount = max(0, $regularPrice - $salePrice);
                        }
                    } else {
                        $regularPrice = isset($product['price']) ? round((float)$product['price'],2) : 0;
                        $imageUrl = getAttachmentUrlById($product['feature_image']);

                        $shippingCost = isset($product['shipping_price']) ? round((float)$product['shipping_price'],2) : 0;

                        // Calculate discount per product
                        if ($product['on_sale']) {
                            $salePrice = isset($product['sale_price']) ? round((float)$product['sale_price'],2) : 0;
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
                    $subTotal += $unitAmount * $product['product_quantity'];
                    $imageUrl = str_replace(' ', '%20', $imageUrl);
                    // Line Item for the Product
                    $lineItems[] = [
                        'price_data' => [
                            'currency' => 'usd',
                            'product_data' => [
                                'name' => $salePrice != 0 ? "{$product['title']} (Was: \${$regularPrice}, Now: \${$salePrice})" : "{$product['title']}",
                                'images' => [$imageUrl],
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

            // Calculate the total amount due (Subtotal - Discount)
            $totalDue = max(0, $subTotal - $totalDiscount);

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
               $coupon = $this->createCoupon($totalDiscount);
                $discounts = [['coupon' => $coupon->id]];
            } else {
                $discounts = [];
            }

            // Create Stripe Checkout Session
            if (isset($newCartItem['platform'])) {
                if ($newCartItem['platform'] == 'web') {

                    $successUrl = env('STRIPE_SUCCESS_URL') . '?id={CHECKOUT_SESSION_ID}';
                    $cancelUrl = env('STRIPE_CANCEL_URL');
                } elseif ($newCartItem['platform'] == 'mobile') {

                    $successUrl = env('APP_URL') . '/stripe/mobile-success?id={CHECKOUT_SESSION_ID}';
                    $cancelUrl = env('APP_URL') . '/stripe/mobile-cancel';
                }
            }
            $logData = [
                'event_type' => 'buyNow Request',
                'session_id' => null,
                'data' => json_encode(['user' => $user , 'customer'=> $customer , 'checkoutData' => $checkoutData,'newCartItem' => $newCartItem]),
                ];
            $log = CoreHelpers::checkoutLog($logData);
            $checkoutSession = $this->checkoutSession(
                $lineItems,
                $customerId,
                $successUrl,
                $cancelUrl,
                $totalDiscount,
                $totalShipping,
                $subTotal,
                $storeIds,
                $productIds,
                $guest,
                $userId,
                $cartId,
                $discounts
            );
            if($log){
                CoreHelpers::findAndUpdateLogWithLogId($log->id,$checkoutSession);
            }
            $logData = [
                'event_type' => 'stripe_session_created',
                'session_id' => $checkoutSession->id,
                'data' => json_encode(['session' => $checkoutSession]),
                ];
            $log = CoreHelpers::checkoutLog($logData);
            // Process Order
            $response = $this->processOrder($checkoutSession, $checkoutData, $newCartItem);
            return $response;
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }
    public function syncShipstationOrdersStatuses(array $orderIds, $status)
    {
        try {
            $updatedOrderIds = [];

            DB::transaction(function () use ($orderIds, $status, &$updatedOrderIds) {
                if (count($orderIds) > 0) {
                    $ordersToUpdate = StoreOrder::whereIn('order_id', $orderIds)
                        ->where(function ($query) {
                            $query->where('order_status', CoreConstants::ORDER_PENDING)
                                ->orWhereNull('order_status');
                        })
                        ->pluck('order_id')
                        ->toArray();

                    if (!empty($ordersToUpdate)) {
                        StoreOrder::whereIn('order_id', $ordersToUpdate)
                            ->update([
                                'order_status' => $status,
                                'updated_at' => Carbon::now(),
                            ]);
                    }

                    $updatedOrderIds = $ordersToUpdate;
                }
            });

            return $updatedOrderIds;
        } catch (Exception $e) {
            throw new Exception('Failed to sync ShipStation orders statuses: ' . $e->getMessage());
        }
    }

    public function getOrderSummary($id)
    {
        try {
            if(is_array($id)){
                $storeOrders = StoreOrder::with(['orderItems.variation', 'user'])
                ->whereIn('order_id', $id)
                ->get();
            }else{
                $storeOrders = StoreOrder::with(['orderItems.variation', 'user'])
                ->where('session_id', $id)
                ->get();
            }
            if (!$storeOrders || $storeOrders->isEmpty()) {
                throw new ItemNotFoundException('No orders found for this session.');
            }

            return $storeOrders;
        } catch (ItemNotFoundException $e) {
            throw new ItemNotFoundException($e->getMessage());
        } catch (Exception $e) {
            throw new Exception('Failed to retrieve order summary: ' . $e->getMessage());
        }
    }
    public function storeMallsharkPurchasedPackage($data,$sessionId)
    {
        $packageData = [
            'store_id'     => $data['store_id'],
            'package_name' => $data['package_name'],
            'amount'       => $data['amount'],
            'started_at'   => $data['started_at'],
            'expired_at'   => $data['expired_at'],
            'session_id' => $sessionId,
            'status'       => $data['status'] ?? 'inactive', 
            'charge_status'=> $data['charge_status'] ?? 'unpaid',  
            'created_at'   => now(),
            'updated_at'   => now(),
        ];

        return MallsharkPurchasedPackage::insert($packageData);
    }
    public function PackageConfirmation($id)
    {
        try{
            $sessionExists = MallsharkPurchasedPackage::where('session_id', $id)->exists();
            if (!$sessionExists) {
                return response()->json([
                    'status' => false,
                    'success_url' =>  'myapp://payment-cancel',
                    'reason' => 'Session does not exist',

                ]);
            }
            $package = MallsharkPurchasedPackage::where('session_id',$id)->where('charge_status','paid')->get();
            return [
                'status' => $package->isEmpty(),
                'success_url' => $package->isEmpty()
                    ? 'myapp://payment-success'
                    : 'myapp://payment-cancel',
            ];
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }
  

   
    
    function getProductShippingProfiles($productId)
    {
        $profiles = DB::table('product_shipping')->where('product_id', $productId)->get();

        if (!$profiles) return null;

        $productProfiles = null;
        foreach ($profiles as $profile) {
            $productProfiles[] = $this->getShippingProfile($profile->shipping_id)->first();
        }

        return $productProfiles;
    }

    function getShippingProfile($prodfileId) 
    {
        $result = ShippingProfile::with([
            'fixedShipping',
            'carrierShipping.carrier',
            'freeShipping',
            'zones.fixedShipping',
            'zones.carrierShipping.carrier',
            'zones.freeShipping',
            'zones.states',
        ])->where('id', $prodfileId)->get();

        if (!$result) {
            throw new ItemNotFoundException('No shipping profiles found.');
        }

        return $result->map(function ($profile) {
            if ($profile->is_general) {
                foreach ($profile->zones as $zone) {
                    unset($zone->fixedShipping, $zone->carrierShipping);
                }
            } else {
                unset($profile->fixedShipping, $profile->carrierShipping);
            }

            return $profile;
        });
    }

    function getShippingOption($id) {
        return ShippingOption::where('id', $id)->first();
    }
    public function applyCoupon(array $data): array
    {
        try {
            $code = $data['coupon_code'] ?? null;
            $variationId = $data['variation_id'] ?? null;
            $productId = $data['product_id'] ?? null;
            $userEmail = $data['email'] ?? null;

            if (!$code) {
                throw new InvalidArgumentException('Coupon code is required.');
            }

            $coupon = Discount::where(['code' => $code, 'type' => 'coupon'])->first();

            if (!$coupon || $coupon->status !== 'active' || $coupon->state !== 'live') {
                throw new OutOfBoundsException('The coupon is either inactive or unavailable.');
            }

            if ($coupon->max_usage_limit && $coupon->usage_count >= $coupon->max_usage_limit) {
                throw new RuntimeException('Coupon usage limit has been reached.');
            }

            if ($coupon->one_use_per_customer && $userEmail) {
                $user = User::where('email', $userEmail)->select('user_id')->first();

                if ($user) {
                    $alreadyUsed = DiscountUsage::where([
                        'discount_id' => $coupon->id,
                        'user_id' => $user->user_id
                    ])->exists();

                    if ($alreadyUsed) {
                        throw new LogicException('This user has already used the coupon.');
                    }
                }
            }

            $discountApplicable = null;

            if ($variationId) {
                $discountApplicable = DiscountApplicable::where([
                    'applicable_type' => 'variation',
                    'applicable_id' => $variationId,
                    'discount_id' => $coupon->id
                ])->first();
            } elseif ($productId) {
                $discountApplicable = DiscountApplicable::where([
                    'applicable_type' => 'product',
                    'applicable_id' => $productId,
                    'discount_id' => $coupon->id
                ])->first();
            }

            if (!$discountApplicable) {
                throw new OutOfBoundsException('The coupon does not apply to this product or variation.');
            }

            return [
                'type' => $discountApplicable->custom_discount_type,
                'value' => $discountApplicable->custom_discount_value,
                'applicable_id' => $discountApplicable->applicable_id,
                'applicable_type' => $discountApplicable->applicable_type,
                'discount_id' => $coupon->id,
            ];

        } catch (\Throwable $e) {
            Log::error('Coupon application error', [
                'message' => $e->getMessage(),
                'input' => $data,
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }

    public function applyCouponV3(array $data): array
    {
        try {
            $code = $data['coupon_code'] ?? null;
            $variationId = $data['variation_id'] ?? null;
            $productId = $data['product_id'] ?? null;
            $userEmail = $data['email'] ?? null;

            if (!$code) {
                throw new InvalidArgumentException('Coupon code is required.');
            }
            $storeId = Product::where('product_id', $productId)->value('store_id');
            $coupon = Discount::where(['code' => $code, 'type' => 'coupon','store_id' => $storeId,'status' => 'active','state' => 'live'])->first();

            if (!$coupon || $coupon->status !== 'active' || $coupon->state !== 'live') {
                throw new OutOfBoundsException('The coupon is either inactive or unavailable.');
            }

            if ($coupon->max_usage_limit && $coupon->usage_count >= $coupon->max_usage_limit) {
                throw new RuntimeException('Coupon usage limit has been reached.');
            }

            if ($coupon->one_use_per_customer && $userEmail) {
                $user = User::where('email', $userEmail)->select('user_id')->first();

                if ($user) {
                    $alreadyUsed = DiscountUsage::where([
                        'discount_id' => $coupon->id,
                        'user_id' => $user->user_id,
                        'usage_count' => 1
                    ])->exists();

                    if ($alreadyUsed) {
                        throw new LogicException('This user has already used the coupon.');
                    }
                }
            }

            $discountApplicable = null;

            if ($variationId) {
                $discountApplicable = DiscountApplicable::where([
                    'applicable_type' => 'variation',
                    'applicable_id' => $variationId,
                    'discount_id' => $coupon->id
                ])->first();
            } elseif ($productId) {
                $discountApplicable = DiscountApplicable::where([
                    'applicable_type' => 'product',
                    'applicable_id' => $productId,
                    'discount_id' => $coupon->id
                ])->first();
            }

            if (!$discountApplicable) {
                throw new OutOfBoundsException('The coupon does not apply to this product or variation.');
            }

            return [
                'type' => $discountApplicable->custom_discount_type,
                'value' => $discountApplicable->custom_discount_value,
                'applicable_id' => $discountApplicable->applicable_id,
                'applicable_type' => $discountApplicable->applicable_type,
                'discount_id' => $coupon->id,
            ];

        } catch (\Throwable $e) {
            Log::error('Coupon application error', [
                'message' => $e->getMessage(),
                'input' => $data,
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }
    public function applyCollectiveCoupon(array $data)
    {
        $userId = $data['user_id'] ?? null;
        $products = $data['products'] ?? [];

        if (!$userId || empty($products)) {
            return $data;
        }

        foreach ($products as $key => $product) {

            // Initialize
            $couponApplied = false;
            $finalTotal = null;
            $couponInfo = [
                'coupon_id' => null,
                'coupon_discount_type' => null,
                'coupon_discount_value' => null,
            ];

            // ----------------------------
            // Resolve type (product/variant)
            // ----------------------------
            $isVariant = ($product['type'] === 'variant');
            $itemType = $isVariant ? 'variation' : 'product';
            $itemId   = $isVariant ? $product['variation_id'] : $product['product_id'];
            $unitPrice = $product['price'];
            $quantity  = $product['quantity'] ?? 1;

            $storeId = $product['store_id'];
            $couponId = $product['coupon_id'] ?? null;

            // If product does not have coupon_id, skip it
            if (!$couponId) {
                $products[$key] = CoreHelpers::attachCouponResult($product, $couponApplied, $couponInfo, $finalTotal);
                continue;
            }

            // ----------------------------
            // Check if user collected this coupon
            // ----------------------------
            $hasCollected = CustomerCoupon::where('user_id', $userId)
                ->where('status', 1)
                ->where('coupon_id', $couponId)
                ->exists();

            if (!$hasCollected) {
                $products[$key] = CoreHelpers::attachCouponResult($product, $couponApplied, $couponInfo, $finalTotal);
                continue;
            }

            // ----------------------------
            // Fetch active coupon
            // ----------------------------
            $coupon = Discount::where('id', $couponId)
                ->where('store_id', $storeId)
                ->where('status', 'active')
                ->where('state', 'live')
                ->where('type', 'coupon')
                ->first();

            if (!$coupon) {
                $products[$key] = CoreHelpers::attachCouponResult($product, $couponApplied, $couponInfo, $finalTotal);
                continue;
            }

            // ----------------------------
            // Check usage limits
            // ----------------------------
            if (CoreHelpers::isCouponUsageExceeded($coupon, $userId)) {
                $products[$key] = CoreHelpers::attachCouponResult($product, $couponApplied, $couponInfo, $finalTotal);
                continue;
            }

            // ----------------------------
            // Check applicability (product/variation)
            // ----------------------------
            $applicable = DiscountApplicable::where([
                'applicable_type' => $itemType,
                'discount_id' => $coupon->id,
                'applicable_id' => $itemId
            ])->exists();

            if (!$applicable) {
                $products[$key] = CoreHelpers::attachCouponResult($product, $couponApplied, $couponInfo, $finalTotal);
                continue;
            }

            // ----------------------------
            // Apply coupon
            // ----------------------------
            $total = $unitPrice * $quantity;

            $finalTotal = ($coupon->discount_value_type === 'percent')
                ? $total - ($total * ($coupon->discount_value / 100))
                : $total - $coupon->discount_value;

            // Mark coupon as applied
            $couponApplied = true;
            $couponInfo = [
                'coupon_id' => $coupon->id,
                'coupon_discount_type' => $coupon->discount_value_type,
                'coupon_discount_value' => $coupon->discount_value,
            ];

            // Attach data back into product array
            $products[$key] = CoreHelpers::attachCouponResult($product, $couponApplied, $couponInfo, $finalTotal);
        }

        $data['products'] = $products;
        return $data;
    }

}