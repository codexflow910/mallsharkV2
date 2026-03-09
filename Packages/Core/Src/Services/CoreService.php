<?php

namespace Packages\Core\Services;

use Stripe\Webhook;
use Packages\Import\Http\Controllers\ShopifyController;
use App\Constants;
use App\Http\Controllers\MailgunController;
use Exception;
use Illuminate\Support\Facades\URL;
use Stripe\Stripe;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Packages\Core\Contracts\CoreHelpers;
use Packages\Core\Repositories\Eloquent\CoreRepository;
use Illuminate\Support\ItemNotFoundException;
class CoreService
{
    use CoreHelpers;
    private $mailgun;
    protected $coreRepository;
    protected $shopifyController;
    public function __construct(CoreRepository $coreRepository,ShopifyController $shopifyController)
    {
        Stripe::setApiKey(env('STRIPE_API_KEY'));
        $this->coreRepository = $coreRepository;
        $this->mailgun = new MailgunController();
        $this->shopifyController = $shopifyController;
    }

    public function getCheckout($data)
    {
        try {
            $cartData = $this->coreRepository->getCart($data);
            $cartData = $this->structureCartData($cartData);

            $userId = $data['id'];
            $cartId = $data['cart_id'] ?? '';
            $guest = $data['guest'] ?? false;

            $user = $this->getUserById($userId);
            $customerId = null;

            if ($user) {
                $customer = $this->coreRepository->findOrCreateStripeCustomer($user);
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

            foreach ($cartData as $products) {
                foreach ($products as $product) {
                    $discountAmount = 0;
                    $salePrice = 0;
                    if ($product['product_type'] == 'variant') {
                        $regularPrice = isset($product['variation_sale_price']) ? $product['variation_sale_price'] : 0;
                        $imageUrl = $this->getAttachmentUrlById((int)$product['product_variation_image']);

                        if (!$imageUrl) {
                            $imageUrl = $this->getAttachmentUrlById($product['feature_image']);
                        }
                        $shippingCost = isset($product['shipping_price']) ? $product['shipping_price'] : 0;

                        // Calculate discount per product
                        if ($product['on_sale']) {
                            $salePrice = isset($product['product_variation_sale_price']) ? $product['product_variation_sale_price'] : 0;
                            $discountAmount = max(0, $regularPrice - $salePrice);
                        }
                    } else {
                        $regularPrice = isset($product['price']) ? $product['price'] : 0;
                        $imageUrl = $this->getAttachmentUrlById($product['feature_image']);

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
                    $cartId = $product['cart_id'] ?? '';
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
                $coupon = $this->coreRepository->createCoupon($totalDiscount);
                $discounts = [['coupon' => $coupon->id]];
            } else {
                $discounts = [];
            }

            // Create Stripe Checkout Session
            if (isset($data['platform'])) {
                if ($data['platform'] == 'web') {

                    $successUrl = env('STRIPE_SUCCESS_URL') . '?id={CHECKOUT_SESSION_ID}';
                    $cancelUrl = env('STRIPE_CANCEL_URL');
                } elseif ($data['platform'] == 'mobile') {

                    $successUrl = env('APP_URL') . '/stripe/mobile-success?id={CHECKOUT_SESSION_ID}';
                    $cancelUrl = env('APP_URL') . '/stripe/mobile-cancel';
                }
            }

            $checkoutSession = $this->coreRepository->checkoutSession(
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
            $response = $this->coreRepository->processOrder($checkoutSession, $cartData, $data);
            return $response;
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    public function structureCartData($cartData)
    {
        $allData = [];
        foreach ($cartData as $key => $product) {
            $array = [];
            $array['usps_services'] = ['First', 'Priority', 'Express'];
            $array['product_id'] = $product['product_id'];
            $array['store_id'] = $product['store_id'];
            $array['origin_zip'] = $product['store']['zip_code'];
            $array['user_id'] = $product['user_id'];
            $array['title'] = $product['product']['title'];
            $array['price'] = $product['product']['price'];
            $array['on_sale'] = $product['product']['on_sale'];
            $array['sale_id'] = $product['product']['discount_id'];
            $array['sale_price'] = $product['product']['sale_price'];
            $array['slug'] = $product['product']['slug'];
            $array['feature_image'] = $product['product']['feature_image'];
            $array['cart_id'] = $product['cart_id'];
            $array['product_quantity'] = $product['product_quantity'];
            $array['variation_sale_price'] = isset($product['variation']['price']) ? $product['variation']['price'] : "";
            $array['product_variation_sale_price'] = isset($product['variation']['sale_price']) ? $product['variation']['sale_price'] : $array['variation_sale_price'];
            $array['variation_id'] = isset($product['variation']['variation_id']) ? $product['variation']['variation_id'] : "";
            $array['product_variation_image'] = isset($product['variation']['product_variation_image']) ? $product['variation']['product_variation_image'] : "";
            $array['option1'] = isset($product['variation']['option1']) ? $product['variation']['option1'] : "";
            $array['attribute1'] = isset($product['variation']['attribute1']) ? $product['variation']['attribute1'] : "";
            $array['attribute2'] = isset($product['variation']['attribute2']) ? $product['variation']['attribute2'] : "";
            $array['attribute3'] = isset($product['variation']['attribute3']) ? $product['variation']['attribute3'] : "";
            $array['unit1'] = isset($product['variation']['unit1']) ? $product['variation']['unit1'] : "";
            $array['option2'] = isset($product['variation']['option2']) ? $product['variation']['option2'] : "";
            $array['option3'] = isset($product['variation']['option3']) ? $product['variation']['option3'] : "";
            $array['unit2'] = isset($product['variation']['unit2']) ? $product['variation']['unit2'] : "";
            $array['unit3'] = isset($product['variation']['unit3']) ? $product['variation']['unit3'] : "";
            $array['product_variation_quantity'] = isset($product['variation']['product_variation_quantity']) ? $product['variation']['product_variation_quantity'] : "";
            $array['productSize_length'] = isset($product['product']['productSize_length']) ?  $product['product']['productSize_length'] : "";
            $array['productSize_width'] = isset($product['product']['productSize_width']) ? $product['product']['productSize_width'] : "";
            $array['productSize_height'] = isset($product['product']['productSize_height']) ? $product['product']['productSize_height'] : "";
            $array['weight'] = isset($product['product']['weight']) ? $product['product']['weight'] * $product['product_quantity'] : "";
            $array['shipping_option_id'] = isset($product['product']['shipping_option_id']) ? $product['product']['shipping_option_id'] : "";
            $array['domestic_handling_fee'] = isset($product['product']['shipping_option']['domestic_handling_fee']) ? $product['product']['shipping_option']['domestic_handling_fee'] : "";
            $array['flat_shipping_rate'] = isset($product['product']['shipping_option']['flat_shipping_rate']) ? $product['product']['shipping_option']['flat_shipping_rate'] : "";
            $array['is_domestic_free'] = isset($product['product']['shipping_option']['is_domesticfree']) ? $product['product']['shipping_option']['is_domesticfree'] : "";
            $array['profile_name'] = isset($product['product']['shipping_option']['profile_name']) ? $product['product']['shipping_option']['profile_name'] : "";
            $array['origin_zipcode'] = isset($product['product']['shipping_option']['origin_zipcode']) ? $product['product']['shipping_option']['origin_zipcode'] : "";
            $service_expload = isset($product['product']['shipping_option']['shipping_service']) ? $product['product']['shipping_option']['shipping_service'] : "";
            $all_services = explode(",", $service_expload);
            $array['shipping_service'] = $all_services;
            $array['product_type'] = $product['product']['product_type'];
            $array['dimention_type'] = $product['product']['dimention_type'];
            $array['weight_type'] = $product['product']['weight_type'];
            $array['store_country'] = $product['store']['store_country'];
            $array['store_state'] = $product['store']['store_state'];
            $array['store_city'] = $product['store']['store_city'];
            $array['store_state'] = $product['store']['store_state'];
            $array['store_wallet_address'] = $product['store']['wallet_address'];
            $array['stripe_account_id'] = $product['store']['account_id'];
            $array['stripe'] = $product['store']['stripe'];
            $array['stripe_account_id'] = $product['store']['account_id'];
            $array['store_zip_code'] = isset($product['product']['shipping_option']['origin_zipcode']) ? $product['product']['shipping_option']['origin_zipcode'] : "";
            $array['store_contact_info'] = $product['store']['contact_info'];
            $array['ship_id'] = "";
            $array['rate_id'] = "";
            $array['store_data'] = [];
            $array['user_info'] = [];
            $array['store_total'] = "";
            $array['product_tax'] = 0;

            if (isset($array['shipping_option_id']) && $array['shipping_option_id'] != '0') {
                $query = getShippingOptionById($array['shipping_option_id']);
                if (isset($query['is_domesticfree']) && $query['is_domesticfree'] == 0) {

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

                    $array['shipping_price'] = 0;
                    $array['shipping_method'] = 'free';
                    $array['method'] = 'free';
                }
            } else {

                if ($array['product_type'] == 'virtual') {

                    $array['shipping_method'] = '';
                    $array['method'] = 'free';
                    $array['shipping_price'] = 0;
                }
            }

            $array['expected-delivery-date'] = 0;
            array_push($allData, $array);
        }

        foreach ($allData as $key => $value) {
            $finalData[$value['store_id']][] = $value;
        }
        return $finalData;
    }

    public function paymentReceived($request)
    {
        try {
            
            $payload = $request->getContent();
            $sigHeader = $request->header('Stripe-Signature');

            $event = $this->coreRepository->constructEvent($payload, $sigHeader);

            $intentData =  $event->data->object;

            if ($event->type === 'checkout.session.completed') {

                $stripe = new \Stripe\StripeClient(env('STRIPE_API_KEY'));

                // Retrieve session with expanded line items
                $session = $stripe->checkout->sessions->retrieve(
                    $intentData['id'],
                    ['expand' => ['line_items']]
                );
                if ($session['payment_status'] == 'paid') {
                    $customerEmail = $session['customer_details']['email'];

                    $user = $this->getUserByEmail($customerEmail);
                    $user = $this->coreRepository->createOrUpdateUserWithSession($user, $session);
                    $loginKey = $user->login_key;

                    $country = getCountryByCode($session['collected_information']['shipping_details']['address']['country']);
                    $state = getStatebyCode($session['collected_information']['shipping_details']['address']['state']);

                    $userId = $user->user_id;
                    $countryId = $country['id'];
                    $stateId = $state['state_id'];
                    $fullName = $session['collected_information']['shipping_details']['name'];
                    $street = $session['collected_information']['shipping_details']['address']['line1'];
                    $postCode = $session['collected_information']['shipping_details']['address']['postal_code'];
                    $city = $session['collected_information']['shipping_details']['address']['city'];

                    $shippingAddress = $this->coreRepository->createShippingAddress($fullName, $countryId, $stateId, $street, $postCode, $city, $userId);

                    $sessionId = $session['id'];
                    $orders = $this->coreRepository->getOrdersBySessionId($sessionId);

                    $orderIds = [];
                    foreach ($orders as $order) {

                        $orderId = $order->order_id;
                        $orderIds[] = $orderId;
                        $storeId = $order->store_id;
                        $store = getStoreInfoById((int)$storeId);
                        $storeWallet = !empty($store[0]->wallet_address) ? $store[0]->wallet_address : '';
                        $totalAmount = $order->total_amount;
                        $payableAmount = $order->payable_amount;
                        $shippingAmount = $order->shipping_amount;
                        $amountWithOutShip = $totalAmount - $shippingAmount;
                        $paymentProfit = $totalAmount * 0.045;
                        $order->buyer_id = $userId;
                        $order->charge_status = 'paid';
                        $order->transaction_id = $session['payment_intent'];
                        $order->store_tax = null;
                        $order->shipping_address = $shippingAddress->sh_id;
                        $order->save();


                        $discountUsage = $this->coreRepository->getDiscountUsageByOrderId($orderId);
                        if ($discountUsage) {
                            $discountUsage->user_id = $userId;
                            $discountUsage->save();
                        }

                        //escrow
                        $this->coreRepository->createStoreEscrow($orderId, $storeId, $storeWallet, $totalAmount, $shippingAmount, $amountWithOutShip, $payableAmount, $paymentProfit);


                        foreach ($order->orderItems as $item) {
                            $remainStock = "";
                             $productId = $item['item_id'];
                            $variationId = $item['variation_id'];
                            $quantity = $item['quantity'];
                            if (isset($item['variation_id']) && $item['variation_id'] != '') {
                                variableUpdateCurrentStock($item['variation_id'], $item['item_id'], $item['quantity']);
                                // change status of flash sale
                                $product = $this->getProductById($item['item_id']);
                                $productFlashsaleStatus = $product->flashsale_status;

                                if ($productFlashsaleStatus == 'choosen') {
                                    $productData = getvariableProduct($item['item_id']);
                                    $remainStock = $productData['current_stock'];
                                    if ($remainStock <= 0) {
                                        $product->update(['flashsale_status' => 'standard']);
                                    }
                                }
                                if($product->is_shopify_import){
                                    $this->shopifyController->orderPlacedInventorySyncToShopify($productId,$quantity,$variationId);
                                }
                            } else {
                                simpleUpdateCurrentStock($item['item_id'], $item['quantity']);
                                // change flash sale status
                                $product = $this->getProductById($item['item_id']);
                                $remainStock = $product->current_stock;
                                if ($product->flashsale_status == 'choosen' && $remainStock <= 0) {
                                    $product->update(['flashsale_status' => 'standard']);
                                }
                                if($product->is_shopify_import){
                                    $this->shopifyController->orderPlacedInventorySyncToShopify($productId,$quantity);
                                }
                            }
                        }

                        $buyerId = $order->buyer_id;
                        $shippingAddressId = $order->shipping_address;
                        $buyerData = $this->getUserById($buyerId);

                        $orderId = $order->order_id;
                        $shippingAddress = $this->getShippingAddressById($shippingAddressId);
                        $sellerId = isset($store[0]->user_id) ? $store[0]->user_id : '';

                        // prepare notification for buyer
                        $subjectBuyer = Constants::ORDER_CREATE_SUBJECT_BUYER . $orderId;
                        addNotifications($buyerId, $sellerId, $subjectBuyer, '', '/new-buyer-orders-detail/' . $orderId, 'buyer_order_details', $orderId, 'buyer');
                        $this->pushNotification($buyerId, $subjectBuyer, 'buyer');

                        // prepare notification for seller 
                        $subjectSeller = Constants::ORDER_CREATE_SUBJECT_SELLER . $orderId;
                        addNotifications($sellerId, $buyerId, $subjectSeller, '', '/order-detail/' . $orderId, 'brand_order_details', $orderId, 'seller');
                        $this->pushNotification($sellerId, $subjectSeller, 'seller');

                        $stateId = $shippingAddress->state;
                        $countryId = $shippingAddress->country;
                        $state = getStatebyId($stateId);
                        $country = getCountryById($countryId);
                        $stateName = $state['state_name'];
                        $countryName = $country['code'];
                        $storeName = isset($store[0]->store_name) ? $store[0]->store_name : '';
                        $sellerId = isset($store[0]->user_id) ? $store[0]->user_id : '';
                        $userDataStore = getUserByIdchat((int) $sellerId);
                        $toEmailStore = $userDataStore->email;
                        $html = view('mail.storecreateOrder', [
                            'email' => $customerEmail,
                            'name' =>  $shippingAddress->full_name,
                            'city' => $shippingAddress->city,
                            'street' => $shippingAddress->street,
                            'postalcode' => $shippingAddress->post_code,
                            'state' => $stateName,
                            'country' => $countryName,
                            'storeName' => $storeName,
                            'order_id' => $orderId,
                            'productDetails' => $order,
                            'coupon_code' => ''
                        ]);
                        $subjectVendor = 'You’ve Received an Order! ';

                        $params = [
                            'from'    => 'MallShark  ' . env('SUPPORT_EMAIL'),
                            'to'      => $toEmailStore,
                            'subject' => $subjectVendor,
                            'html'    => "$html",
                        ];
                        $results = $this->mailgun->SendMailgunMail($params);
                    }
                }

                $stateId = $shippingAddress->state;
                $countryId = $shippingAddress->country;
                $state = getStatebyId($stateId);
                $stateName = $state['state_name'];
                $country = getCountryById($countryId);
                $countryName = $country['code'];
                $html = view('mail.new-order-client-side', [
                    'customerDetail' => $buyerData,
                    'productDetails' => $orders,
                    'email' => $customerEmail,
                    'name' => $shippingAddress->full_name,
                    'city' => $shippingAddress->city,
                    'street' => $shippingAddress->street,
                    'postalcode' => $shippingAddress->post_code,
                    'state' => $stateName,
                    'country' => $countryName,
                    'orderIds' => $orderIds,
                ]);

                $subject = 'Order Placed! Order #' . implode(', ', $orderIds);
                $toEmail = $buyerData->email;
                $bccEmails = ['sajidoon@mindblazetech.com', 'abdullah.jutt@mindblaze.biz', 'care@mallshark.com', 'fugatesklcc@yahoo.com', 'aatika@mallshark.com', 'chadfugate85@gmail.com', 'chad@mallshark.com'];
                $params = [
                    'from'    => 'MallShark  ' . env('SUPPORT_EMAIL'),
                     'to'      => $toEmail,
                    'bcc'     => implode(',', $bccEmails),
                    'subject' => $subject,
                    'html'    => "$html",
                ];
                $results = $this->mailgun->SendMailgunMail($params);
                $user = getGuestUserById($userId);

                if (!empty($user)) {
                    $url = URL::to('/change-password/' . $loginKey);
                    $html = view('mail.activateUser_checkout', ['activate_url' => $url]);
                    $subject = 'Welcome to MallShark - Get Ready for an Unforgettable Shopping Experience!';

                    $toEmail = $user->email;
                    $params = [
                        'from'    => 'MallShark  ' . env('SUPPORT_EMAIL'),
                        // 'to'      => $toEmail,
                        'to' => 'deyap29215@firain.com',
                        'subject' => $subject,
                        'html'    => "$html",
                    ];
                    $results = $this->mailgun->SendMailgunMail($params);
                }

                // Delete cart items after successful payment
                $this->coreRepository->deleteCartByIntentMetadata($intentData->metadata);
            }

            return response()->json(['status' => 'success']);
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }
    public function makeCheckoutData($data){
        try{
            foreach ($data['products'] as $product) {
                // 1. Validate Store
                $store = $this->getStoreInfoById($product['store_id']);
                if (empty($store) || !isset($store[0])) {
                    throw new ItemNotFoundException('Store not found. Store ID: ' . $product['store_id']);
                }

                if ($store[0]->stripe != 1) {
                    throw new Exception('Store does not support purchases. Stripe not connected. Store ID: ' . $product['store_id']);
                }

                // 2. Validate Product
                $productData = $this->getProductById($product['product_id']);
                if (!$productData) {
                    throw new ItemNotFoundException('Product not found. Product ID: ' . $product['product_id']);
                }

                if ($product['store_id'] != $productData->store_id) {
                    throw new Exception('Product does not belong to the given store. Product ID: ' . $product['product_id']);
                }

                // 3. Check if product has variation
                $isVariation = !empty($product['variation_options']);

                $variationId = null;
                $currentStock = $productData->current_stock;

                if ($isVariation) {
                    $variation = $this->coreRepository->getVariationsInfo($product['variation_options'], $product['product_id']);
                    if (!$variation) {
                        throw new ItemNotFoundException('Variation not found. Product ID: ' . $product['product_id']);
                    }
                    $variationId = $variation->variation_id;
                    $currentStock = $variation->current_stock;
                }
                $newCartItem = [
                    'product_id' => $product['product_id'],
                    'variation_id' => $variationId,
                    'store_id' => $product['store_id'],
                    'product_quantity' => $product['product_quantity'],
                    'user_id' => $data['id'],
                    'guest' => $data['guest'],
                    'platform' => $data['platform']
                ];
            }
            $checkoutData = $this->coreRepository->structureBuyNowProduct($newCartItem);
            $response = $this->coreRepository->makeCheckout($checkoutData,$newCartItem);
            return $response;
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }

    }
}
