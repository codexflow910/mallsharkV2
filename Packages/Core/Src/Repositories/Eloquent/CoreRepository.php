<?php

namespace Packages\Core\Repositories\Eloquent;

use Exception;
use Carbon\Carbon;
use Stripe\Stripe;
use Stripe\Coupon;
use Monolog\Logger;
use Stripe\Webhook;
use Stripe\Customer;
use Packages\Auth\Models\Userrole;
use Packages\Order\Models\OrderItems;
use Packages\Store\Models\StoreEscrow;
use Stripe\Checkout\Session;
use Packages\Auth\Models\User;
use Packages\Home\Models\ShippingAddress;
use Illuminate\Support\Facades\DB;
use Monolog\Handler\StreamHandler;
use Packages\Product\Models\Product;
use Packages\Cart\Models\ProductCart;
use Packages\Order\Models\StoreOrder;
use Packages\Discount\Models\Discount;
use Packages\Discount\Models\DiscountUsage;
use Illuminate\Support\ItemNotFoundException;
use Packages\Core\Contracts\CoreRepositoryInterface;
use Packages\Product\Models\ProductVariation;
use Packages\Store\Models\Store;

class CoreRepository implements CoreRepositoryInterface
{

    protected $model;
    protected $cartModel;
    protected $productVariationModel;
    public function __construct(ProductCart $cartModel, ProductVariation $productVariationModel)
    {
        $this->cartModel = $cartModel;
        Stripe::setApiKey(env('STRIPE_API_KEY'));
        $this->productVariationModel = $productVariationModel;
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
            $user->customer_id = $customer->id;
            $user->save();
        } else {
            $customer = Customer::retrieve($user->customer_id);
        }

        return $customer;
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

    public function processOrder($checkoutSession, $cartData, $request)
    {
        try {
            DB::transaction(function () use ($checkoutSession, $cartData) {
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

    public function addOrderItems($item_array = array())
    {
        OrderItems::insert($item_array);
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
            $user->name = $session['customer_details']['name'];
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
            $now = Carbon::now();
            $user->forget_key_date = $now;
            $user->forget_key = $loginKey;
            $user->save();
        }

        return $user;
    }

    public function createShippingAddress($fullName, $countryId, $stateId, $street, $postCode, $city, $userId)
    {
        $shippingAddress = new ShippingAddress();
        $shippingAddress->full_name = $fullName;
        $shippingAddress->country = $countryId;
        $shippingAddress->state = $stateId;
        $shippingAddress->street = $street;
        $shippingAddress->post_code = $postCode;
        $shippingAddress->city = $city;
        $shippingAddress->user_id = $userId;
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
    }

    function deleteCartByIntentMetadata($metadata)
    {
        if (!isset($metadata->user_id)) {
            return false;
        }

        if (isset($metadata->guest) && $metadata->guest === 'true') {
            return ProductCart::where('random_key', $metadata->user_id)->delete();
        }

        return ProductCart::where('user_id', $metadata->user_id)->delete();
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

            $user = getUserById($userId);
            $customerId = null;

            if ($user) {
                $customer = $this->findOrCreateStripeCustomer($user);
                $customerId = $customer->id;
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
                        $regularPrice = isset($product['variation_sale_price']) ? $product['variation_sale_price'] : 0;
                        $imageUrl = getAttachmentUrlById((int)$product['product_variation_image']);

                        if (!$imageUrl) {
                            $imageUrl = getAttachmentUrlById($product['feature_image']);
                        }
                        $shippingCost = isset($product['shipping_price']) ? $product['shipping_price'] : 0;

                        // Calculate discount per product
                        if ($product['on_sale']) {
                            $salePrice = isset($product['product_variation_sale_price']) ? $product['product_variation_sale_price'] : 0;
                            $discountAmount = max(0, $regularPrice - $salePrice);
                        }
                    } else {
                        $regularPrice = isset($product['price']) ? $product['price'] : 0;
                        $imageUrl = getAttachmentUrlById($product['feature_image']);

                        $shippingCost = isset($product['shipping_price']) ? $product['shipping_price'] : 0;

                        // Calculate discount per product
                        if ($product['on_sale']) {
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

            // Process Order
            $response = $this->processOrder($checkoutSession, $checkoutData, $newCartItem);
            return $response;
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }
}