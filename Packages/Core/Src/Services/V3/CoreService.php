<?php

namespace Packages\Core\Services\V3;
use Monolog\Logger;
use Stripe\Webhook;
use Packages\Import\Http\Controllers\V3\ShopifyController;
use Monolog\Handler\StreamHandler;
use App\Constants;
use App\Http\Controllers\MailgunController;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\URL;
use Stripe\Stripe;
use Stripe\Checkout\Session;
use Packages\Core\Contracts\V3\CoreHelpers;
use Packages\Cart\Contracts\V3\CartHelpers;
use Packages\Core\Repositories\Eloquent\V3\CoreRepository;
use Illuminate\Support\ItemNotFoundException;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Huddle\Zendesk\Facades\Zendesk;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Packages\Discount\Models\V3\Discount;
use Packages\Discount\Models\V3\{DiscountUsage,DiscountApplicable};
use Packages\Product\Models\V3\{ProductVariation,Product};
use Packages\Shipping\Models\V3\FixedShipping;
use InvalidArgumentException;
use OutOfBoundsException;
use RuntimeException;
use LogicException;
use Packages\Core\Config\CoreConstants;
use Packages\Core\Models\V3\MallsharkPurchasedPackage;
use Packages\Product\Config\ProductConstants;
use App\Jobs\CreateShipstationOrderJob;
use Defuse\Crypto\Core;
use App\Jobs\SendOrderEmailsJob;
use App\Jobs\UspsValidateAddressJob;
use Illuminate\Auth\Access\AuthorizationException;
use Packages\Klavio\Services\V3\KlavioService;
use Packages\Order\Config\OrderConstants;  
use Packages\Core\Models\V3\BaseRate;
use Packages\Core\Models\V3\StateCategoryTax;
use Packages\Store\Models\V3\States;
use Packages\Core\Models\V3\TaxCategory;
use Packages\Core\Models\V3\category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Packages\Order\Models\V3\{StoreOrder};
use Packages\Order\Models\V3\{OrderItems,OrderShipping};
use Packages\Admin\Models\V3\StoreDeduction;
use Packages\Cart\Models\V3\ProductCart;
class CoreService
{
    use CoreHelpers;
    use CartHelpers;
    private $mailgun;
    protected $coreRepository;
    protected $shopifyController;
    protected $klaviyoService;
    protected $cryptoService;
    
    public function __construct(CoreRepository $coreRepository, ShopifyController $shopifyController, KlavioService $klaviyo)
    {
        Stripe::setApiKey(env('STRIPE_API_KEY'));
        $this->coreRepository = $coreRepository;
        $this->mailgun = new MailgunController();
        $this->shopifyController = $shopifyController;
        $this->klaviyoService = $klaviyo;
    }

    public function getCheckout($data)
    {
        try {
            $cartItems = $this->coreRepository->getCart($data);
            $newItems = [];
            foreach ($cartItems as $item) {
                // dd($item);
                $product = $item['product'];
                $item['is_corrupt'] = false;
                $item['corrupt_reason'] = null;

                if (!$product || $product['status'] != ProductConstants::STATUS_ACTIVE) {
                    continue;
                }

                if (!empty($item['variation_id'])) {
                    $variation = CoreHelpers::getvariableByProduct_id($item['variation_id']);

                    if (!$variation) {
                        continue;
                    } elseif ($variation->current_stock < $item['product_quantity']) {
                        continue;
                    }
                } else {
                    if ($product['current_stock'] < $item['product_quantity']) {
                        continue;
                    }
                }
            $newItems[] = $item;
            }
            $cartData = $newItems;
            $cartData = $this->structureCartData($cartData);

            $userId = $data['id'];
            $cartId = $data['cart_id'] ?? '';
            $guest = $data['guest'] ?? false;
            $user = null;
            $customerId = null;
            $customer = null;
            $log = null;
            if($guest == false){
                $user = $this->getUserById($userId);
                if ($user) {
                    $customer = $this->coreRepository->findOrCreateStripeCustomer($user);
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

            foreach ($cartData as $products) {
                foreach ($products as $product) {
                    $discountAmount = 0;
                    $salePrice = 0;
                    if ($product['product_type'] == 'variant') {
                        $regularPrice = isset($product['variation_sale_price']) ? round((float)$product['variation_sale_price'],2) : 0;
                        $imageUrl = $this->getAttachmentUrlById((int)$product['product_variation_image']);

                        if (!$imageUrl) {
                            $imageUrl = $this->getAttachmentUrlById($product['feature_image']);
                        }
                        $shippingCost = isset($product['shipping_price']) ? round((float)$product['shipping_price'],2) : 0;

                        // Calculate discount per product
                        if ($product['on_sale']) {
                            $salePrice = isset($product['product_variation_sale_price']) ? round((float)$product['product_variation_sale_price'],2) : 0;
                            $discountAmount = max(0, $regularPrice - $salePrice);
                        }
                    } else {
                        $regularPrice = isset($product['price']) ? round((float)$product['price'],2) : 0;
                        $imageUrl = $this->getAttachmentUrlById($product['feature_image']);

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
                    $unitAmount = (int) round($regularPrice * 100);
                    $shippingAmount = (int) round($shippingCost * 100);

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
            $logData = [
                'event_type' => 'stripe request data',
                'session_id' => null,
                'data' => json_encode(['user' => $user, 'customer' => $customer, 'cart_data' => $cartData,'request' => $data]),
            ];
            $log = CoreHelpers::checkoutLog($logData);
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
                $discounts,
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
            $response = $this->coreRepository->processOrder($checkoutSession, $cartData, $data);
            $email = $user ? $user->email : ($data['email'] ?? null);
            if (!empty($email)) {
                $eventData = [
                    'email'        => $email,
                    'checkout_id'  => $checkoutSession->id ?? null,
                    'cart_items'   => $lineItems,
                    'total_price'  => $grandTotal / 100,
                    'currency'     => 'USD',
                ];
                Log::info('Sending Klaviyo event', $eventData);
                $this->klaviyoService->trackStartedCheckout($eventData);
            }
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
            
            $payload = $request->getContent();
            $sigHeader = $request->header('Stripe-Signature');

            $event = $this->coreRepository->constructEvent($payload, $sigHeader);

            $intentData =  $event->data->object;
            if($intentData->metadata['checkout'] == 'old'){
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
                    $loginKey = $user->forget_key;

                    $country = getCountryByCode($session['collected_information']['shipping_details']['address']['country']);
                    $state = getStatebyCode($session['collected_information']['shipping_details']['address']['state']);
                    $userId = $user->user_id;
                    $countryId = $country['id'];
                    $stateId = $state['state_id'] ?? null;
                    $fullName = $session['collected_information']['shipping_details']['name'];
                    $street = $session['collected_information']['shipping_details']['address']['line1'];
                    $apartment = $session['collected_information']['shipping_details']['address']['line2'];
                    $postCode = $session['collected_information']['shipping_details']['address']['postal_code'];
                    $city = $session['collected_information']['shipping_details']['address']['city'];

                    $shippingAddress = $this->coreRepository->createShippingAddress($fullName, $countryId, $stateId, $street, $postCode, $city, $userId,$apartment);

                    $sessionId = $session['id'];
                    $orders = $this->coreRepository->getOrdersBySessionId($sessionId);
                    $orderIds = [];
                    foreach ($orders as $order) {

                        $orderId = $order->order_id;
                        $orderIds[] = $orderId;
                        $storeId = $order->store_id;
                        $store = CoreHelpers::getStoreInfoById((int)$storeId);
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
                            if (isset($item['variation_id']) && $item['variation_id'] != '' && $item['variation_id'] != 0) {
                                CoreHelpers::variableUpdateCurrentStock($item['variation_id'], $item['item_id'], $item['quantity']);
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
                                CoreHelpers::simpleUpdateCurrentStock($item['item_id'], $item['quantity']);
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

                            //create order on shipstation
                            if (in_array($storeId, CoreConstants::SHIPSTATION_STORE_IDS)) {
                                // CreateShipstationOrderJob::dispatch($orderId);
                                UspsValidateAddressJob::dispatch($orderId);
                            }
                            
                            $buyerId = $order->buyer_id;
                            $shippingAddressId = $order->shipping_address;
                            $buyerData = $this->getUserById($buyerId);

                            $orderId = $order->order_id;
                            $shippingAddress = CoreHelpers::getShippingAddressById($shippingAddressId);
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
                            $stateName = $state ? $state['state_name'] : null;
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
                                'apartment_no' => $shippingAddress->apartment_no,
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

                        //create order on shipstation
                        if (in_array($storeId, CoreConstants::SHIPSTATION_STORE_IDS)) {
                            CreateShipstationOrderJob::dispatch($orderId);
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
                        $stateName = $state['state_name'] ?? null;
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
                            'apartmentNo' => $shippingAddress->apartment_no,
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
                        if (app()->environment('production')) {
                            $params['bcc'] = $bccEmails;
                        }
                        $results = $this->mailgun->SendMailgunMail($params);
                    }
                    $logData = [
                        'event_type' => 'payment_success',
                        'session_id' => $session['id'],
                        'data' => json_encode(['session' => $session,'user' => $user,'orders'=>$orders]),
                    ];
                    $log = CoreHelpers::checkoutLog($logData);
                

                    $stateId = $shippingAddress->state;
                    $countryId = $shippingAddress->country;
                    $state = getStatebyId($stateId);
                    $stateName = $state['state_name'] ?? null;
                    $country = getCountryById($countryId);
                    $countryName = $country['code'];
                    
                    $html = view('mail.new-order-client-side', [
                        'customerDetail' => $buyerData,
                        'productDetails' => $orders,
                        'email' => $customerEmail,
                        'name' => $shippingAddress->full_name,
                        'city' => $shippingAddress->city,
                        'street' => $shippingAddress->street,
                        'apartmentNo' => $shippingAddress->apartment_no,
                        'postalcode' => $shippingAddress->post_code,
                        'state' => $stateName,
                        'country' => $countryName,
                        'orderIds' => $orderIds,
                    ]);

                    $subject = 'Order Placed! Order #' . implode(', ', $orderIds);
                    $toEmail = $buyerData->email;
                    $bccEmails = explode(',', env('CREATE_ORDER_BCC'));
                    $params = [
                        'from'    => 'MallShark  ' . env('SUPPORT_EMAIL'),
                        'to'      => $toEmail,
                        'subject' => $subject,
                        'html'    => "$html",
                    ];
                    if (app()->environment('production')) {
                        $params['bcc'] = $bccEmails;
                    }
                    $results = $this->mailgun->SendMailgunMail($params);
                    $user = getGuestUserById($userId);

                    if (!empty($user)) {
                        $url = URL::to('/change-password/' . $loginKey);
                        $html = view('mail.activateUser_checkout', ['activate_url' => $url]);
                        $subject = 'Welcome to MallShark - Get Ready for an Unforgettable Shopping Experience!';

                        $toEmail = $user->email;
                        $params = [
                            'from'    => 'MallShark  ' . env('SUPPORT_EMAIL'),
                            'to'      => $toEmail,
                            // 'to' => 'deyap29215@firain.com',
                            'subject' => $subject,
                            'html'    => "$html",
                        ];
                        $results = $this->mailgun->SendMailgunMail($params);
                    }

                    // Delete cart items after successful payment
                    $this->coreRepository->deleteCartByIntentMetadata($intentData->metadata);
                }
            }
        }
            return response()->json(['status' => 'success']);
    }
    
   public function paymentReceivedV2($request)
    {
        try {
            
            $payload = $request->getContent();
            
            $sigHeader = $request->header('Stripe-Signature');

            $event = $this->coreRepository->constructEventV2($payload, $sigHeader);
            $intentData =  $event->data->object;
            if ($event->type === 'checkout.session.completed') {
                if (isset($intentData->metadata->type) && $intentData->metadata->type === 'package_purchase') {

                    MallsharkPurchasedPackage::where('session_id', $intentData->id)->update([
                        'status' => 'active',
                        'charge_status' => 'paid',
                        'updated_at' => now()
                    ]);
                }
                return response()->json(['status' => 'success']);
            }
            if($intentData->metadata->checkout == 'new'){
                if ($event->type === 'payment_intent.succeeded') {
                        $intentId = $intentData->id;
                        $orders = $this->coreRepository->getOrdersBySessionId($intentId);
                        // Decode customizations for each order item (moved from legacy handler)
                        foreach ($orders as $order) {
                            if (isset($order->orderItems) && is_iterable($order->orderItems)) {
                                foreach ($order->orderItems as &$item) {
                                    if (!empty($item['customizations']) && is_string($item['customizations'])) {
                                        $decoded = json_decode($item['customizations'], true);
                                        if (json_last_error() === JSON_ERROR_NONE) {
                                            $item['customizations'] = $decoded;
                                        }
                                    }
                                }
                                unset($item);
                            }
                        }

                        $results = $this->updateStoreOrderViaCrypto($orders,$intentId);
                        
                        // Delete cart items after successful payment
                        $cartData = $this->coreRepository->deleteCartByIntentMetadataV2($intentData->metadata);
                        
                        $logData = [
                            'event_type' => 'webhook success',
                            'session_id' => $intentId,
                            'data' => json_encode(['date_time' => now(),'Success' => true]),
                        ];
                        $log = CoreHelpers::checkoutLog($logData);
                }
                return response()->json(['status' => 'success']);
            } 
        }catch (Exception $e) {
            $log = [
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'error' => $e->getMessage(),
            ];
            $logData = [
                'event_type' => 'webhook fail',
                'session_id' => $intentId,
                'data' => json_encode($log),
            ];
            $log = CoreHelpers::checkoutLog($logData);
            $html = view('mail.payment-receive-failed', [
                    'payload' => $request->all(),
                    'errorMessage' =>  $e->getMessage(),
                ]);

                $subject = "⚠️ Stripe Webhook Failed";
                $toEmail = 'abdullah.jutt@mindblaze.biz';
                $params = [
                    'from'    => 'MallShark  ' . env('SUPPORT_EMAIL'),
                    'to'      => $toEmail,
                    'subject' => $subject,
                    'html'    => "$html",
                ];
               if(app()->environment('production')){
                    SendOrderEmailsJob::dispatch($params);
                }

             return response()->json(['status' => true], 200);

        }
    }
    public function makeCheckoutData($data){
        try{
            foreach ($data['products'] as $product) {
                // 1. Validate Store
                $store = CoreHelpers::getStoreInfoById($product['store_id']);
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
        
    public function createShipstationOrder($orderId)
    {
        try {
            $orderData = $this->getCompleteOrderData($orderId);

            if (!$orderData) {
                throw new Exception('Order not found or invalid order ID: ' . $orderId);
            }

            $order = $orderData[0];

            $shippingAddress = $order['shipping_address'];
            $store = $order['store'];
            $buyer = $order['user'];
            $orderItems = $order['order_items'];
            $items = [];
            
            foreach ($orderItems as $orderItem) {
                $product = $orderItem['product'];
                $variation = $orderItem['variation'] ?? null;
                $itemImageId = $variation['product_variation_image'] ?? $product['feature_image'] ?? null;

                $items[] = [
                    'lineItemKey' => (string) $orderItem['order_item_id'],
                    'name' => $orderItem['product_name'] . ($orderItem['variation_info'] ?? ''),
                    'quantity' => $orderItem['quantity'],
                    'unitPrice' => $orderItem['sold_price'] > 0 ? $orderItem['sold_price'] : $orderItem['price'],
                    'productId' => $product['product_id'],
                    'shippingAmount' => $orderItem['shipping_amount'],
                    'imageUrl' => $this->getAttachmentUrlById((int)$itemImageId, 'small'),
                    'weight' => [
                        'value' => (float)($product['weight'] ?? 0),
                        'units' => $product['weight_type'] ?? 'lb',
                    ],
                    "createDate" => Carbon::parse($order['created_at'])->toIso8601String(),
                    "modifyDate" => Carbon::parse($order['created_at'])->toIso8601String(),

                ];
            }


            $payload = [
                'orderNumber' => $order['order_id'],
                'orderDate' => Carbon::parse($order['created_at'])->toIso8601String(),
                'createDate' => Carbon::parse($order['created_at'])->toIso8601String(),
                'modifyDate' => Carbon::parse($order['created_at'])->toIso8601String(),
                'paymentDate' => Carbon::parse($order['created_at'])->toIso8601String(),
                'orderStatus' => CoreConstants::SHIPSTATION_ORDER_CREATE,
                'customerEmail' => $buyer['email'],
                'billTo' => [
                    'name' => $buyer['name'],
                    'street1' => $shippingAddress['street'] ?? '',
                    'city' => $shippingAddress['city'] ?? '',
                    'state' => $shippingAddress['state']['code'] ?? '',
                    'postalCode' => $shippingAddress['post_code'] ?? '',
                    'country' => $shippingAddress['country']['code'] ?? 'US',
                    'phone' => $buyer['contact_no'] ?? '',
                ],
                'shipTo' => [
                    'name' => $shippingAddress['full_name'],
                    'street1' => $shippingAddress['street'],
                    'street2' => $shippingAddress['apartment_no'] ?? '',
                    'city' => $shippingAddress['city'],
                    'state' => $shippingAddress['state']['code'],
                    'postalCode' => $shippingAddress['post_code'],
                    'country' => $shippingAddress['country']['code'],
                    'phone' => $buyer['contact_no'] ?? '',
                ],
                'amountPaid' => $order['total_amount'],
                'shippingAmount' => $order['shipping_amount'],
                'paymentMethod' => $order['payment_method'],
                'items' => $items,
            ];

            $response = $this->createOrderOnShipstation($payload);
            return $response;
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    public function createOrderOnShipstation($payload)
    {
        try {
            $apiKey = env('SHIPSTATION_API_KEY');
            $apiSecret = env('SHIPSTATION_API_SECRET');
            $baseUrl = env('SHIPSTATION_BASE_URL');

            // Build the Basic Auth token manually
            $authToken = base64_encode($apiKey . ':' . $apiSecret);
            CoreHelpers::notifyShipstationFailure($payload['orderNumber'], $payload);
            $paymentLog = new Logger('');
            $paymentLog->pushHandler(new StreamHandler(storage_path('logs/shipStationError.log')), Logger::ERROR);
            $paymentLog->error('Request', ['Data' => $payload]);
            $client = new Client();
            $response = $client->post($baseUrl . '/orders/createorder', [
                'headers' => [
                    'Authorization' => 'Basic ' . $authToken,
                    'Content-Type'  => 'application/json',
                ],
                'json' => $payload,
            ]);

            $responseData = json_decode($response->getBody(), true);

            if (isset($responseData['orderId'])) {
                return $responseData;
            } else {
                throw new Exception('Failed to create order on ShipStation: ' . json_encode($responseData));
            }
        } catch (RequestException $e) {
            $rawBody = $e->hasResponse() ? $e->getResponse()->getBody()->getContents() : null;
            $parsedError = json_decode($rawBody, true);
            $paymentLog = new Logger('');
            $paymentLog->pushHandler(new StreamHandler(storage_path('logs/shipStationError.log')), Logger::ERROR);
            $paymentLog->error('Failed', ['Message' => $e->getMessage(),'Data' => $payload, 'Response' => $parsedError]);
            CoreHelpers::notifyShipstationFailure($payload['orderNumber'], $payload);
            throw new Exception('RequestException: ' . $e->getMessage());
        } catch (Exception $e) {
            $shipstationLog = new Logger('shipstation');
            $shipstationLog->pushHandler(new StreamHandler(storage_path('logs/shipStation/shipStationCreateOrder.log')), Logger::ERROR);
            $shipstationLog->error('Failed', ['Message' => $e->getMessage()]);
            CoreHelpers::notifyShipstationFailure($payload['orderNumber'], $payload);
            throw new Exception('Failed to create order on ShipStation: ' . $e->getMessage());
        }
    }

    public function syncShipstationOrdersStatuses()
    {
        try {
            $apiKey = env('SHIPSTATION_API_KEY');
            $apiSecret = env('SHIPSTATION_API_SECRET');
            $baseUrl = env('SHIPSTATION_BASE_URL');

            $client = new Client();

            $response = $client->get($baseUrl . '/orders', [
                'auth' => [$apiKey, $apiSecret],
                'query' => [
                    'orderStatus' => CoreConstants::ORDER_SHIPPED,
                ],
            ]);

            $data = json_decode($response->getBody(), true);

            $orderIds = [];
            if (isset($data['orders']) && !empty($data['orders'])) {
                foreach ($data['orders'] as $order) {
                    if (isset($order['orderStatus']) && $order['orderStatus'] == CoreConstants::ORDER_SHIPPED) {
                        $orderId = $order['orderNumber'];
                        $orderIds[] = (int)$orderId;               
                    } else {
                        continue;
                    }
                }
            }

            if (empty($orderIds)) {
                return [];
            }

            $response = $this->coreRepository->syncShipstationOrdersStatuses($orderIds, CoreConstants::ORDER_SHIPPED);
            return $response;
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }
    public function getRatesOnCheckout($data)
    {
        try {
            $buyerStateId = $data['buyer_state_id'];
            $buyerPostalCode = $data['buyer_postal_code'];
            $mailClass = $data['mail_class'] ?? null;
            $buyerCountry = $data['buyer_country_code'];
            $results = [];
            $notFound = [];
            $rawTotals = [];
            $totalTax = 0;

            foreach ($data['products'] as $product) {
                $productId = $product['product_id'];
                $storeId = $product['store_id'];
                if(isset($product['whole_sale']) && !$product['whole_sale']){
                    $matchedZone = null;
                    $customProfile = null;
                    $generalProfile = null;

                    if (!$product['new_shipping']) {
                        $shippingOption = CoreHelpers::getShippingOptionById($product['shipping_option_id']);
                        
                        if ($shippingOption) {
                            $results[$productId] = $shippingOption;
                        } else {
                            $notFound[] = [
                                'product_id' => $productId,
                                'message' => "Shipping Option not found."
                            ];
                        }
                        continue;
                    }

                    $shippingProfiles = $this->coreRepository->getProductShippingProfiles($productId);
                    if($shippingProfiles){
                        foreach ($shippingProfiles as $shippingProfile) {
                            if (!$shippingProfile->is_general) {
                                // Check if buyer's state matches any zone in custom profile
                                foreach ($shippingProfile->zones as $zone) {
                                    $stateIds = collect($zone->states)->pluck('state_id')->toArray();
                                    if (in_array($buyerStateId, $stateIds)) {
                                        $matchedZone = $zone;
                                        $customProfile = $shippingProfile;
                                        break 2;
                                    }
                                }
                            } else {
                                $generalProfile = $shippingProfile;
                            }
                        }
                    }
                    

                    $productShippingOptions = [];

                    // ✅ CASE 1: Use matched custom profile zone
                    if ($customProfile && $matchedZone) {
                        if (!empty($matchedZone->fixedShipping)) {
                            $price = CoreHelpers::calculateFixedShipping($product, $matchedZone->fixedShipping);
                            $productShippingOptions[] = [
                                'type' => 'fixed_shipping',
                                'zone_id' => $matchedZone->id,
                                'profile_id' => $customProfile->id,
                                'shipping' => $matchedZone->fixedShipping,
                                'price' => round($price, 2),
                            ];

                            // track raw totals by product id
                            $rawTotals[$productId]['fixed'][] = $price;
                            $rawTotals[$productId]['quantity'][] = $product['quantity'];
                            $rawTotals[$productId]['weight'][] = $product['weight'];
                            $rawTotals[$productId]['weight_unit'] = $product['weight_unit'];
                            $rawTotals[$productId]['shipping'] = $matchedZone->fixedShipping;
                        }

                        if (!empty($matchedZone->carrierShipping)) {
                            $carrierSlug = $matchedZone->carrierShipping->carrier->slug ?? null;
                            $carrierRates = $this->getCarrierRates(
                                $carrierSlug,
                                $product,
                                $storeId,
                                $buyerPostalCode,
                                $matchedZone->carrierShipping,
                                $buyerCountry,
                                $mailClass
                            );

                            if ($carrierRates) {
                                $productShippingOptions[] = [
                                    'type' => 'carrier_shipping',
                                    'carrier' => $carrierSlug,
                                    'zone_id' => $matchedZone->id,
                                    'profile_id' => $customProfile->id,
                                    'shipping' => $matchedZone->carrierShipping,
                                    'carrier_rates' => $carrierRates,
                                ];
                            } else {
                                $notFound[] = [
                                    'product_id' => $productId,
                                    'message' => "Carrier not found or unsupported in zone"
                                ];
                            }
                        }

                        if (!empty($matchedZone->freeShipping)) {
                            $productShippingOptions[] = [
                                'type' => 'free_shipping',
                                'zone_id' => $matchedZone->id,
                                'profile_id' => $customProfile->id,
                                'shipping' => $matchedZone->freeShipping,
                            ];
                        }

                    // ✅ CASE 2: Fall back to general profile
                    } elseif ($generalProfile) {
                        if (!empty($generalProfile->fixedShipping)) {
                            $price = CoreHelpers::calculateFixedShipping($product, $generalProfile->fixedShipping);
                            $productShippingOptions[] = [
                                'type' => 'fixed_shipping',
                                'profile_id' => $generalProfile->id,
                                'shipping' => $generalProfile->fixedShipping,
                                'price' => round($price, 2),
                            ];

                            $rawTotals[$productId]['fixed'][] = $price;
                            $rawTotals[$productId]['weight'][] = $product['weight'];
                            $rawTotals[$productId]['quantity'][] = $product['quantity'];
                            $rawTotals[$productId]['weight_unit'] = $product['weight_unit'];
                            $rawTotals[$productId]['shipping'] = $generalProfile->fixedShipping;
                        }

                        if (!empty($generalProfile->carrierShipping)) {
                            $carrierSlug = $generalProfile->carrierShipping->carrier->slug ?? null;
                            $carrierRates = $this->getCarrierRates(
                                $carrierSlug,
                                $product,
                                $storeId,
                                $buyerPostalCode,
                                $generalProfile->carrierShipping,
                                $buyerCountry,
                                $mailClass
                            );

                            if (is_array($carrierRates) && isset($carrierRates[0])) {
                                $productShippingOptions[] = [
                                    'type' => 'carrier_shipping',
                                    'carrier' => $carrierSlug,
                                    'profile_id' => $generalProfile->id,
                                    'shipping' => $generalProfile->carrierShipping,
                                    'carrier_rates' => $carrierRates,
                                ];
                            } else {
                                $notFound[] = [
                                    'product_id' => $productId,
                                    'message' => isset($carrierRates['message'])
                                        ? $carrierRates['message'] . (isset($carrierRates['code']) ? " ({$carrierRates['code']})" : '')
                                        : "Carrier not found or unsupported in general profile",

                                ];
                            }
                        }

                        if (!empty($generalProfile->freeShipping)) {
                            $productShippingOptions[] = [
                                'type' => 'free_shipping',
                                'profile_id' => $generalProfile->id,
                                'shipping' => $generalProfile->freeShipping,
                            ];
                        }

                    } else {
                        // ❌ CASE 3: Nothing matched
                        $notFound[] = [
                            'product_id' => $productId,
                            'message' => 'No applicable shipping profile found (custom or general).'
                        ];
                    }

                    // Assign shipping options to $results as requested
                    
                    if ($product['product_type'] == 'variant') {
                        $variationId = $product['variation_id'];
                        $results[$productId][$variationId] = $productShippingOptions;
                    } else {
                        $results[$productId] = $productShippingOptions;
                    }
                }else{
                    $matchedZone = null;
                    $customProfile = null;
                    $generalProfile = null;

                    if (!$product['new_shipping']) {
                        $shippingOption = CoreHelpers::getShippingOptionById($product['shipping_option_id']);
                        
                        if ($shippingOption) {
                            $results[$productId] = $shippingOption;
                        } else {
                            $notFound[] = [
                                'product_id' => $productId,
                                'message' => "Shipping Option not found."
                            ];
                        }
                        continue;
                    }

                    $shippingProfiles = $this->coreRepository->getProductShippingProfiles($productId);
                    if($shippingProfiles){
                        foreach ($shippingProfiles as $shippingProfile) {
                            if (!$shippingProfile->is_general) {
                                // Check if buyer's state matches any zone in custom profile
                                foreach ($shippingProfile->zones as $zone) {
                                    $stateIds = collect($zone->states)->pluck('state_id')->toArray();
                                    if (in_array($buyerStateId, $stateIds)) {
                                        $matchedZone = $zone;
                                        $customProfile = $shippingProfile;
                                        break 2;
                                    }
                                }
                            } else {
                                $generalProfile = $shippingProfile;
                            }
                        }
                    }
                    

                    $productShippingOptions = [];

                    // ✅ CASE 1: Use matched custom profile zone
                    if ($customProfile && $matchedZone) {
                        if (!empty($matchedZone->fixedShipping)) {
                            $price = CoreHelpers::calculateFixedShipping($product, $matchedZone->fixedShipping);
                            $productShippingOptions[] = [
                                'type' => 'fixed_shipping',
                                'zone_id' => $matchedZone->id,
                                'profile_id' => $customProfile->id,
                                'shipping' => $matchedZone->fixedShipping,
                                'price' => round($price, 2),
                            ];

                            // track raw totals by product id
                            $rawTotals[$productId]['fixed'][] = $price;
                            $rawTotals[$productId]['quantity'][] = $product['quantity'];
                            $rawTotals[$productId]['weight'][] = $product['weight'];
                            $rawTotals[$productId]['weight_unit'] = $product['weight_unit'];
                            $rawTotals[$productId]['shipping'] = $matchedZone->fixedShipping;
                        }

                        if (!empty($matchedZone->carrierShipping)) {
                            $carrierSlug = $matchedZone->carrierShipping->carrier->slug ?? null;
                            $carrierRates = $this->getCarrierRates(
                                $carrierSlug,
                                $product,
                                $storeId,
                                $buyerPostalCode,
                                $matchedZone->carrierShipping,
                                $buyerCountry,
                                $mailClass
                            );

                            if ($carrierRates) {
                                $productShippingOptions[] = [
                                    'type' => 'carrier_shipping',
                                    'carrier' => $carrierSlug,
                                    'zone_id' => $matchedZone->id,
                                    'profile_id' => $customProfile->id,
                                    'shipping' => $matchedZone->carrierShipping,
                                    'carrier_rates' => $carrierRates,
                                ];
                            } else {
                                $notFound[] = [
                                    'product_id' => $productId,
                                    'message' => "Carrier not found or unsupported in zone"
                                ];
                            }
                        }

                        if (!empty($matchedZone->freeShipping)) {
                            $productShippingOptions[] = [
                                'type' => 'free_shipping',
                                'zone_id' => $matchedZone->id,
                                'profile_id' => $customProfile->id,
                                'shipping' => $matchedZone->freeShipping,
                            ];
                        }

                    // ✅ CASE 2: Fall back to general profile
                    } elseif ($generalProfile) {
                        if (!empty($generalProfile->fixedShipping)) {
                            $price = CoreHelpers::calculateFixedShipping($product, $generalProfile->fixedShipping);
                            $productShippingOptions[] = [
                                'type' => 'fixed_shipping',
                                'profile_id' => $generalProfile->id,
                                'shipping' => $generalProfile->fixedShipping,
                                'price' => round($price, 2),
                            ];

                            $rawTotals[$productId]['fixed'][] = $price;
                            $rawTotals[$productId]['weight'][] = $product['weight'];
                            $rawTotals[$productId]['quantity'][] = $product['quantity'];
                            $rawTotals[$productId]['weight_unit'] = $product['weight_unit'];
                            $rawTotals[$productId]['shipping'] = $generalProfile->fixedShipping;
                        }

                        if (!empty($generalProfile->carrierShipping)) {
                            $carrierSlug = $generalProfile->carrierShipping->carrier->slug ?? null;
                            $carrierRates = $this->getCarrierRates(
                                $carrierSlug,
                                $product,
                                $storeId,
                                $buyerPostalCode,
                                $generalProfile->carrierShipping,
                                $buyerCountry,
                                $mailClass
                            );

                            if (is_array($carrierRates) && isset($carrierRates[0])) {
                                $productShippingOptions[] = [
                                    'type' => 'carrier_shipping',
                                    'carrier' => $carrierSlug,
                                    'profile_id' => $generalProfile->id,
                                    'shipping' => $generalProfile->carrierShipping,
                                    'carrier_rates' => $carrierRates,
                                ];
                            } else {
                                $notFound[] = [
                                    'product_id' => $productId,
                                    'message' => isset($carrierRates['message'])
                                        ? $carrierRates['message'] . (isset($carrierRates['code']) ? " ({$carrierRates['code']})" : '')
                                        : "Carrier not found or unsupported in general profile",

                                ];
                            }
                        }

                        if (!empty($generalProfile->freeShipping)) {
                            $productShippingOptions[] = [
                                'type' => 'free_shipping',
                                'profile_id' => $generalProfile->id,
                                'shipping' => $generalProfile->freeShipping,
                            ];
                        }

                    } else {
                        // ❌ CASE 3: Nothing matched
                        $notFound[] = [
                            'product_id' => $productId,
                            'message' => 'No applicable shipping profile found (custom or general).'
                        ];
                    }

                    // Assign shipping options to $results as requested
                    
                    if ($product['product_type'] == 'variant') {
                        $variationId = $product['variation_id'];
                        $results[$productId][$variationId] = $productShippingOptions;
                    } else {
                        $results[$productId] = $productShippingOptions;
                    }
                }
                $results[$productId]['product_type'] = $product['product_type'];
                // // Tax calculation code start
                $categoryModel = CoreHelpers::getChildCategoryByProductID($product['product_id']);
                $categoryID = $categoryModel ? $categoryModel->id : null;

                $tax = $this->calculateTax($buyerStateId, $categoryID, $product['price'], $product['quantity'],$buyerPostalCode );
                
                $totalTax += $tax['tax_amount'] ?? 0;
                $results[$productId]['tax'] = [
                    'tax_amount'   => $tax['tax_amount'] ?? 0,
                ];
            }

            // ✅ Step 2: Merge products by product_id for discount
            $shippingDiscount = 0;
            foreach ($rawTotals as $pid => $dataArr) {
                if (!isset($dataArr['fixed'], $dataArr['shipping'])) {
                    continue;
                }
                $sumOfVariants = array_sum($dataArr['fixed']);
                $mergedProduct = [
                    'product_id' => $pid,
                    'quantity'   => array_sum($dataArr['quantity']),
                    'weight_unit' => $dataArr['weight_unit'],
                    'weight'     => array_sum($dataArr['weight']),
                ];
                $discounted = CoreHelpers::calculateFixedShipping($mergedProduct, $dataArr['shipping']);
                $shippingDiscount += ($sumOfVariants - $discounted);
            }
            return [
                'products_shipping' => $results,
                'missing_profiles' => $notFound,
                'shipping_discount' => round($shippingDiscount, 2),
                'total_tax' => round($totalTax, 2),
            ];

        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    public function createShipStationWebhook()
    {
        try {
            $apiKey = env('SHIPSTATION_API_KEY');
            $baseUrl = env('SHIPSTATION_BASE_URL');
            $apiSecret = env('SHIPSTATION_API_SECRET');

            $client = new Client();

            $response = $client->post( $baseUrl . '/webhooks/subscribe', [
                'auth' => [$apiKey, $apiSecret],
                'headers' => [
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'target_url' => 'https://stageapi.mallshark.net/api/shipstation/order-webhook',
                    'event' => 'ORDER_NOTIFY',
                    'store_id' => null,
                    'friendly_name' => 'Mallshark Order Status Webhook',
                ],
            ]);

            $data = json_decode($response->getBody(), true);
            return $data;
        } catch (Exception $e) {
            throw new Exception('ShipStation Webhook Creation Failed: ' . $e->getMessage());
        }
    }

    public function fetchShipmentDetails($resourceUrl)
    {
        try {
            $apiKey = env('SHIPSTATION_API_KEY');
            $apiSecret = env('SHIPSTATION_API_SECRET');


            $client = new Client([
                'auth' => [$apiKey, $apiSecret],
            ]);

            $response = $client->get($resourceUrl);
            return $response;
        } catch (Exception $e) {
            throw new Exception('Failed to fetch shipment details: ' . $e->getMessage());
        }
    }
    public function createZendeskTicketForPackagePurshased($data)
    {
        try {
            $buyer = auth()->user();
            $buyerName = $buyer->name;
            $buyerEmail = $buyer->email;

            // Create or update Zendesk user
            $zendeskUser = Zendesk::users()->createOrUpdate([
                'name'  => $buyerName,
                'email' => $buyerEmail,
                'role'  => 'seller',
            ]);

            $zendeskId = $zendeskUser->user->id ?? null;

            if (!$zendeskId) {
                throw new ItemNotFoundException("Zendesk ID not found");
            }

            // Extract package info
            $packageName = $data['package_name'];
            $amount      = number_format($data['amount'], 2);
            $startedAt   = Carbon::parse($data['started_at'])->toFormattedDateString();
            $expiresAt   = Carbon::parse($data['expires_at'])->toFormattedDateString();

            // Build Zendesk ticket content
            $subject = "You’ve Successfully Purchased a Mallshark Package";
            $message = <<<EOD
                Dear $buyerName,

                Thank you for purchasing the **$packageName** package on Mallshark.

                **Package Details:**
                - Amount: \$$amount
                - Start Date: $startedAt
                - Expiry Date: $expiresAt

                If you have any questions or need help with your package benefits, feel free to reply to this email or contact us at [care@mallshark.com](mailto:care@mallshark.com).

                We appreciate your business and look forward to supporting your store's success.

                Best regards,  
                Team Mallshark
                EOD;

            // Create Zendesk ticket
            $newTicket = Zendesk::tickets()->create([
                'subject'      => $subject,
                'comment'      => ['body' => $message],
                'priority'     => 'normal',
                'requester_id' => $zendeskId,
                'type'         => 'question',
            ]);
            
            // Send Mailgun email confirmation
            $from = env('ZENDESK_EMAIL');
            $to = $buyerEmail;
            $htmlContent = view('mail.mallshark-package-purchase', ['buyerName' => $buyerName, 'packageInfo' => $data])->render();

            $params = [
                'from'    => $from,
                'to'      => $to,
                'subject' => $subject,
                'html'    => $htmlContent,
            ];

            $mailgun = new MailgunController();
            $mailgun->SendMailgunMail($params);

            $adminSubject = "A seller has successfully purchased a package on <strong>Mallshark</strong>";

            $params = [
                $from = $from,
                'to' => env(''),
                'subject' => $adminSubject,
                'html' => $htmlContent
            ];

            $mailgun->SendMailgunMail($params);
            return true;
        } catch (ItemNotFoundException $e) {
            throw new ItemNotFoundException($e->getMessage());
        }
    }
    public function getCarrierRates($carrierSlug, $product, $storeId, $buyerPostalCode, $carrierShipping, $buyerCountry, $mailClass)
    {
        $rates = null;
        $additionalFeeCheck = $carrierShipping->additional_fee;
        $feeType = $carrierShipping->fee_type;
        $feeValue = $carrierShipping->fee_value;

        switch ($carrierSlug) {
            case 'fedex':
                $ratesResponse = $this->getFedexShippingRates([$product], $storeId, $buyerPostalCode, $buyerCountry);
                $rates = $ratesResponse instanceof \Illuminate\Http\JsonResponse
                    ? $ratesResponse->getData(true)
                    : $ratesResponse;

                if (isset($rates['status']) && $rates['status'] === false) {
                    break;
                }
                
                $rates = $this->filterFedexResponse($rates);
                break;
            case 'usps':
                $rates = $this->getUspsShippingRates([$product], $storeId, $buyerPostalCode, $mailClass);
                $rates = CoreHelpers::filterUspsResponse($rates);
                break;
        }

        return $rates ? $this->addAdditionalFee($rates, $feeType, $feeValue, $additionalFeeCheck) : null;
    }


    public function getFedexShippingRates($product, $storeId, $recipientPostalCode, $recipientCountryCode = 'US')
    {
        $store = $this->getStoreById($storeId);

        // Extract shipping details (default to US data)
        $shipperPostalCode = $store['zip_code'] ?? null;
        $shipperCountryCode = $store['country']['code'] ?? 'US';

        $packageItems = $this->convertPackageFormat($product);

        if (empty($packageItems)) {
            throw new Exception('No valid package details found');
        }

        // ✅ Get FedEx Token
        $client = new Client();
        $baseUrl = env('FEDEX_BASE_URL');

        try {
            $response = $client->post($baseUrl . '/oauth/token', [
                'form_params' => [
                    'grant_type' => 'client_credentials',
                    'client_id' => env('FEDEX_API_KEY'),
                    'client_secret' => env('FEDEX_SECRET_KEY'),
                ],
            ]);
            $data = json_decode($response->getBody(), true);
            $token = $data['access_token'];
        } catch (Exception $e) {
            throw new Exception('Failed to authenticate FedEx. ' . $e->getMessage());
        }

        // ✅ API Headers
        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'Content-Type'  => 'application/json',
        ];

        // ✅ API Request Body
        $body = [
            'accountNumber' => [
                'value' => env('ACCOUNT_NUMBER')
            ],
            "rateRequestControlParameters" => [
                "returnTransitTimes" => true,
                "servicesNeededOnRateFailure" => true,
                "variableOptions" => "FREIGHT_GUARANTEE",
                "rateSortOrder" => "COMMITDESCENDING"
            ],
            'requestedShipment' => [
                'shipper' => [
                    'address' => [
                        'postalCode' => $shipperPostalCode,
                        'countryCode' => $shipperCountryCode,
                    ]
                ],
                'recipient' => [
                    'address' => [
                        'postalCode' => $recipientPostalCode,
                        'countryCode' => $recipientCountryCode,
                    ]
                ],
                'pickupType' => 'DROPOFF_AT_FEDEX_LOCATION',
                'rateRequestType' => ["ACCOUNT"],
                "shipDateStamp" => now()->toDateString(),
                'requestedPackageLineItems' => [$packageItems],
            ],
            "labelResponseOptions" => "URL_ONLY"
        ];

        try {
            $response = $client->post($baseUrl . '/rate/v1/rates/quotes', [
                'headers' => $headers,
                'json' => $body,
            ]);

            $response = json_decode($response->getBody(), true);
            return $response;
        } catch (RequestException $e) {
            $statusCode = $e->hasResponse() ? $e->getResponse()->getStatusCode() : 500;
            $rawBody = $e->hasResponse() ? $e->getResponse()->getBody()->getContents() : null;
            $parsedError = json_decode($rawBody, true);

            $errorMessage = 'Unknown FedEx error';
            $errorCode = 'UNKNOWN';

            if (isset($parsedError['errors'][0])) {
                $errorCode = $parsedError['errors'][0]['code'] ?? 'UNKNOWN_CODE';
                $errorMessage = $parsedError['errors'][0]['message'] ?? 'No message provided';
            }

            return response()->json([
                'status' => false,
                'message' => "FedEx Error: {$errorMessage}",
                'code' => $errorCode,
                'status_code' => $statusCode,
            ], $statusCode);
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    public function PackageConfirmation($id)
    {
        try {
        return $this->coreRepository->PackageConfirmation($id);
        } catch (\Exception $e) {
             throw new Exception($e->getMessage());
        }
    }
    public function getUspsAccessToken()
    {
        try {

            $baseUrl = env('USPS_BASE_URL');

            $response = Http::retry(3, 200)->asForm()->post($baseUrl . '/oauth2/v3/token', [
                'grant_type'    => 'client_credentials',
                'client_id'     => env('USPS_CONSUMER_KEY'),
                'client_secret' => env('USPS_CONSUMER_SECRET'),
            ]);

            $data = json_decode($response->getBody(), true);
            if (isset($data['access_token'])) {
                return $data['access_token'];
            }

            throw new Exception('Access token not found in USPS response.');
        } catch (Exception $e) {
            throw new Exception('Failed to authenticate USPS. ' . $e->getMessage());
        }
    }
    public function applyCoupon($data){

        $coupon = $this->coreRepository->applyCoupon($data);

        $discountType = $coupon['type'] ?? null;
        $discountValue = $coupon['value'] ?? null;

        if (!$discountType || !$discountValue) {
            $discount = Discount::findOrFail($coupon['discount_id']);
            $discountType = $discount->discount_value_type;
            $discountValue = $discount->discount_value;
        }

        $product = match ($coupon['applicable_type']) {
            'variation' => ProductVariation::where('variation_id', $coupon['applicable_id'])->firstOrFail(),
            'product' => Product::where('product_id', $coupon['applicable_id'])->firstOrFail(),
            default => throw new InvalidArgumentException('Invalid applicable type')
        };

        $salePrice = match ($discountType) {
            'fixed' => round(max(0, $product->price - $discountValue), 2),
            'percent' => round($product->price - ($product->price * ($discountValue / 100)), 2),
            default => throw new InvalidArgumentException('Invalid discount type')
        };

        return [
            'applicable_id' => $coupon['applicable_id'],
            'price' => $product->price,
            'sale_price' => $salePrice,
            'discount_id' => $coupon['discount_id']
        ];
    }

    public function applyCouponV3($data){

        $coupon = $this->coreRepository->applyCouponV3($data);

        $discountType = $coupon['type'] ?? null;
        $discountValue = $coupon['value'] ?? null;

        if (!$discountType || !$discountValue) {
            $discount = Discount::findOrFail($coupon['discount_id']);
            $discountType = $discount->discount_value_type;
            $discountValue = $discount->discount_value;
        }

        // $product = match ($coupon['applicable_type']) {
        //     'variation' => ProductVariation::where('variation_id', $coupon['applicable_id'])->firstOrFail(),
        //     'product' => Product::where('product_id', $coupon['applicable_id'])->firstOrFail(),
        //     default => throw new InvalidArgumentException('Invalid applicable type')
        // };

        $salePrice = match ($discountType) {
            'fixed' => round(max(0, ($data['price'] * $data['quantity']) - $discountValue), 2),
            'percent' => round(($data['price'] * $data['quantity']) - (($data['price'] * $data['quantity']) * ($discountValue / 100)), 2),
            default => throw new InvalidArgumentException('Invalid discount type')
        };

        return [
            'applicable_id' => $coupon['applicable_id'],
            'price' => $data['price'],
            'coupon_id' => $discount->id,
            'coupon' => 1,
            'coupon_collected_code' => $discount->code,
            'coupon_collected' => 1,
            'coupon_unit_price' => $salePrice / $data['quantity'],
            'total_actual_price' => round($data['price'] * $data['quantity'], 2),
            'total_coupon_price' => $salePrice
        ];
    }
    public function applyCollectiveCoupon($data){

        $coupon = $this->coreRepository->applyCollectiveCoupon($data);

        return $coupon;

    }
    public function findOrCreateStripeCustomerV2($data,$requestPlatform){
         $customer = $this->coreRepository->findOrCreateStripeCustomerV2($data,$requestPlatform);
        return $customer;
    }

    public function findOrCreateStripeCustomerV3($data,$requestPlatform,$checkoutUserId){
         $customer = $this->coreRepository->findOrCreateStripeCustomerV3($data,$requestPlatform,$checkoutUserId);
        return $customer;
    }
    public function findOrCreatePaypalCustomerV3($data,$requestPlatform,$checkoutUserId){
         $customer = $this->coreRepository->findOrCreatePaypalCustomerV3($data,$requestPlatform,$checkoutUserId);
        return $customer;
    }
    public function createCryptoUser($data,$requestPlatform,$checkoutUserId){
         $customer = $this->coreRepository->createCryptoUser($data,$requestPlatform,$checkoutUserId);
        return $customer;
    }
    public function makeCheckoutDataV2($request, $user,$guestSessionId)
    {
        try {
            $storeIds = [];
            $productIds = [];
            $lineItems = [];
            $totalDiscount = 0;
            $totalShipping = 0;
            $subTotal = 0;
            $grandTotal = 0;
            $totalDue = 0;

            $customerId = $user->customer_id ?? null;
            $guest = $request['guest'] ?? false;
            $userId = $user->user_id ?? null;
            $checkoutUserId = $request['id'] ?? null;
            $buyNow = $request['buy_now'] ?? null;
            $productData = $request['product_data'];

            foreach ($productData as $storeId => $storeData) {
                $couponCode = $storeData['coupon_code'] ?? null;
                $updatedProducts = [];

                foreach ($storeData['products'] as $product) {
                    $salePrice = 0;
                    $discountAmount = 0;
                    $regularPrice = 0;
                    $imageUrl = '';

                    $qty = $product['quantity'];
                    $sale = Discount::where([
                        'id' => $product['discount_id'],
                        'store_id' => $product['store_id'],
                        'status' => 'active',
                        'state' => 'live'
                    ])->first();

                    if ($product['product_type'] === 'variant') {
                        $dbProduct = ProductVariation::where([
                            'variation_id' => $product['variation_id'],
                            'product_id' => $product['product_id']
                        ])->first();

                        if (!$dbProduct) continue;

                        $regularPrice = $dbProduct->price ?? $product['price'];
                        $imageId = $dbProduct->product_variation_image ?? $product['image_id'];
                    } else {
                        $dbProduct = Product::where('product_id', $product['product_id'])->first();

                        if (!$dbProduct) continue;

                        $regularPrice = $dbProduct->price ?? $product['price'];
                        $imageId = $dbProduct->feature_image ?? $product['image_id'];
                    }

                    // Discount Logic
                    if ($sale) {
                        $applicableType = $product['product_type'] === 'variant' ? 'variation' : 'product';
                        $applicableId = $product['product_type'] === 'variant' ? $product['variation_id'] : $product['product_id'];

                        $discountApplicable = DiscountApplicable::where([
                            'applicable_type' => $applicableType,
                            'applicable_id' => $applicableId,
                            'discount_id' => $sale->id
                        ])->first();

                        if ($discountApplicable) {
                            $discountType = $discountApplicable->custom_discount_type ?? $sale->discount_value_type;
                            $discountValue = $discountApplicable->custom_discount_value ?? $sale->discount_value;

                            $salePrice = match ($discountType) {
                                'fixed' => max(0, $regularPrice - $discountValue),
                                'percent' => $regularPrice - ($regularPrice * ($discountValue / 100)),
                                default => throw new InvalidArgumentException('Invalid discount type')
                            };

                            $product['sale_price'] = $salePrice;
                        }
                    }
                    $product['price'] = $regularPrice;
                    $discountAmount = $product['on_sale'] ? max(0, $regularPrice - $salePrice) : 0;

                    // Shipping Calculation
                    $shippingId = $product['shipping_detail']['service_id'] ?? null;
                    $shippingCost = 0;

                    if ($shippingId && $product['shipping_detail']['shipping_method'] === 'free') {
                        $product['shipping_detail']['shipping_amount'] = 0;
                    } elseif ($shippingId && $product['shipping_detail']['shipping_method'] === 'fixed') {
                        $shipping = FixedShipping::find($shippingId);

                        if ($shipping && $shipping->shipping_per_item) {
                            $perItemPrice = (float) $shipping->per_item_price;
                            $maxQty = $shipping->max_items_allowed_for_price;
                            $additional = (float) $shipping->additional_price;

                            if ($maxQty && $qty > $maxQty) {
                                $baseShipping = $perItemPrice * $maxQty;
                                $extraShipping = $additional * ($qty - $maxQty);
                                $product['shipping_detail']['base_price'] = $baseShipping;
                                $product['shipping_detail']['handling_fee'] = $extraShipping;
                                $product['shipping_detail']['shipping_amount'] = round($baseShipping + $extraShipping, 2);
                            } else {
                                $baseShipping = $perItemPrice * $qty;
                                $product['shipping_detail']['base_price'] = $baseShipping;
                                $product['shipping_detail']['handling_fee'] = 0;
                                $product['shipping_detail']['shipping_amount'] = round($baseShipping, 2);
                            }
                        }else if($shipping && $shipping->shipping_by_weight){
                            $perWeightPrice = (float) $shipping->base_price;
                            $maxWeightAllowed = $shipping->max_weight_allowed_for_price;
                            $additional = (float) $shipping->additional_price_weight;
                            $profileUnit = $shipping->weight_unit ?? 'lb';

                            $totalWeight = (float) $product['weight'] * (int) $qty;
                            $convertedWeight = CoreHelpers::convertWeightToTargetUnit(
                                $totalWeight,
                                $product['weight_unit'],
                                $profileUnit
                            );
                            $weight = ceil($convertedWeight);

                            if($maxWeightAllowed && $maxWeightAllowed > 0 && $weight > $maxWeightAllowed) {
                                $baseShipping = $perWeightPrice * $weight;
                                $extraShipping = $additional * ($weight - $maxWeightAllowed);
                                $product['shipping_detail']['base_price'] = $baseShipping;
                                $product['shipping_detail']['handling_fee'] = $extraShipping;
                                $product['shipping_detail']['shipping_amount'] = round($baseShipping + $extraShipping, 2);
                            } else {
                                $baseShipping = $perWeightPrice * $weight;
                                $product['shipping_detail']['base_price'] = $baseShipping;
                                $product['shipping_detail']['handling_fee'] = 0;
                                $product['shipping_detail']['shipping_amount'] = round($baseShipping, 2);
                            }
                        } else {
                            $product['shipping_detail']['shipping_amount'] = 0;
                        }
                    }else{
                        $product['shipping_detail']['shipping_amount'] = 0;
                    }
                    $shippingCost = $product['shipping_detail']['shipping_amount'] ?? 0;
                    $totalDiscount += $discountAmount * $qty;
                    $totalShipping += $shippingCost;
                    $subTotal += (int) round($regularPrice * 100) * $qty;

                    $productIds[] = $product['product_id'];
                    $updatedProducts[] = $product;
                }

                $storeData['products'] = $updatedProducts;
                $productData[$storeId] = $storeData;
                $storeIds[] = $storeId;
            }
            
            $totalDue = max(0, $subTotal - ($totalDiscount * 100));
            $grandTotal = $totalDue + ($totalShipping * 100);
            $logData = [
                'event_type' => 'Building checkout data',
                'session_id' => $guestSessionId,
                'data' => json_encode(['Building checkout data' => $productData]),
            ];
            $log = CoreHelpers::checkoutLog($logData);

            $paymentIntent = $this->coreRepository->createPaymentIntent(
                $grandTotal, // already in cents
                'usd',
                $customerId,
                [
                    'store_ids' => json_encode(array_values(array_unique($storeIds))),
                    'product_ids' => json_encode(array_values(array_unique($productIds))),
                    'guest' => $guest,
                    'user_id' => $checkoutUserId,
                    'buy_now' => $buyNow,
                    'checkout' => 'new',
                ]
            );

            $response = ['id' => $paymentIntent->client_secret];
           CoreHelpers::findAndUpdateLogWithSessionId($guestSessionId,$paymentIntent->id);
            $logData = [
                'event_type' => 'Checkout session created',
                'session_id' => $paymentIntent->id,
                'data' => json_encode(['Checkout session created' => $paymentIntent->id]),
            ];
            $log = CoreHelpers::checkoutLog($logData);
            if($user){
                $response = $this->coreRepository->processOrderV2(
                    $paymentIntent,
                    $productData,
                    $request,
                    $user->user_id
                );
                $logData = [
                    'event_type' => 'Order Processed',
                    'session_id' => $paymentIntent->id,
                    'data' => json_encode(['Order Processed' => $paymentIntent->id]),
                ];
                $log = CoreHelpers::checkoutLog($logData);
                }
            return $response;

        } catch (\Exception $e) {
            $logData = [
                'event_type' => 'Order Processing Failed: ',
                'session_id' => $guestSessionId,
                'data' => json_encode(['Exception' => $e->getMessage()]),
            ];
            $log = CoreHelpers::checkoutLog($logData);
            throw $e;
        }
    }

    public function makeCheckoutDataV3($request, $user,$checkoutUserId)
    {
        try {
            $logData = [
                'event_type' => 'started_stripe_intent_creation',
                'user_id' => $checkoutUserId,
                'data' => json_encode(['creating_stripe_intent' =>$request['product_data']]),
            ];
            $log = CoreHelpers::checkoutLogV3($logData);
            $storeIds = [];
            $productIds = [];
            $lineItems = [];
            $totalDiscount = 0;
            $totalShipping = 0;
            $subTotal = 0;
            $customizationTotal = 0;
            $grandTotal = 0;
            $totalDue = 0;

            $customerId = $user->customer_id ?? null;
            $guest = $request['guest'] ?? false;
            $userId = $user->user_id ?? null;
            $buyNow = $request['buy_now'] ?? null;
            $productData = $request['product_data'];

            foreach ($productData as $storeId => $storeData) {
                $updatedProducts = [];

                foreach ($storeData['products'] as $product) {
                    $salePrice = 0;
                    $discountAmount = 0;
                    $regularPrice = 0;
                    $imageUrl = '';
                    $couponCode = $product['coupon_code'] ?? null;
                    $qty = $product['quantity'];

                    if(isset($product['whole_sale']) && !$product['whole_sale']){
                        $originalPrice = $product['price'];
                        $salePrice = $product['sale_price'] ?? 0;
                        $discountAmount = $salePrice > 0 ? $originalPrice - $salePrice : 0;
                        $totalDiscount += $discountAmount * $qty;
                        // include customization price for custom products
                        if (isset($product['is_custom'])) {
                            if (!empty($product['customizations'])) {
                                    $custom = $product['customizations'];                                    
                                    $p = isset($custom['price']) ? (float)$custom['price'] : 0;
                                    $pq = isset($custom['product_quantity']) ? (int)$custom['product_quantity'] : 1;
                                    $customizationTotal += $p * $pq * 100;
                            }
                        }
                        $subTotal += (int) round($originalPrice * 100) * $qty;

                        // Shipping Calculation
                        $shippingId = $product['shipping_detail']['service_id'] ?? null;
                        $shippingCost = 0;

                        if ($shippingId && $product['shipping_detail']['shipping_method'] === 'free') {
                            $product['shipping_detail']['shipping_amount'] = 0;
                        } elseif ($shippingId && $product['shipping_detail']['shipping_method'] === 'fixed') {
                            $shipping = FixedShipping::find($shippingId);

                            if ($shipping && $shipping->shipping_per_item) {
                                $perItemPrice = (float) $shipping->per_item_price;
                                $maxQty = $shipping->max_items_allowed_for_price;
                                $additional = (float) $shipping->additional_price;

                                if ($maxQty && $qty > $maxQty) {
                                    $baseShipping = $perItemPrice * $maxQty;
                                    $extraShipping = $additional * ($qty - $maxQty);
                                    $product['shipping_detail']['base_price'] = $baseShipping;
                                    $product['shipping_detail']['handling_fee'] = $extraShipping;
                                    $product['shipping_detail']['shipping_amount'] = round($baseShipping + $extraShipping, 2);
                                } else {
                                    $baseShipping = $perItemPrice * $qty;
                                    $product['shipping_detail']['base_price'] = $baseShipping;
                                    $product['shipping_detail']['handling_fee'] = 0;
                                    $product['shipping_detail']['shipping_amount'] = round($baseShipping, 2);
                                }
                            }else if($shipping && $shipping->shipping_by_weight){
                                $perWeightPrice = (float) $shipping->base_price;
                                $maxWeightAllowed = $shipping->max_weight_allowed_for_price;
                                $additional = (float) $shipping->additional_price_weight;
                                $profileUnit = $shipping->weight_unit ?? 'lb';

                                $totalWeight = (float) $product['weight'] * (int) $qty;
                                $convertedWeight = CoreHelpers::convertWeightToTargetUnit(
                                    $totalWeight,
                                    $product['weight_unit'],
                                    $profileUnit
                                );
                                $weight = ceil($convertedWeight);
                                if($maxWeightAllowed){
                                    $maxTotalWeight = $maxWeightAllowed * $shipping->base_price_per_weight;
                                }else{
                                    $maxTotalWeight = $maxWeightAllowed;
                                }
                                if($maxTotalWeight && $maxTotalWeight > 0 && $weight > $maxTotalWeight) {
                                    $baseShipping = $perWeightPrice;
                                    $extraShipping = $additional * ($weight - $maxTotalWeight);
                                    $product['shipping_detail']['base_price'] = $baseShipping;
                                    $product['shipping_detail']['handling_fee'] = $extraShipping;
                                    $product['shipping_detail']['shipping_amount'] = round($baseShipping + $extraShipping, 2);
                                } else {
                                    $baseShipping = $perWeightPrice;
                                    $product['shipping_detail']['base_price'] = $baseShipping;
                                    $product['shipping_detail']['handling_fee'] = 0;
                                    $product['shipping_detail']['shipping_amount'] = round($baseShipping, 2);
                                }
                            } else {
                                $product['shipping_detail']['shipping_amount'] = 0;
                            }
                        }else{
                            $product['shipping_detail']['shipping_amount'] = 0;
                        }
                        $shippingCost = $product['shipping_detail']['shipping_amount'] ?? 0;
                        
                    }else{
                        if($product['product_type'] === 'variant'){
                            $tierDetails = CartHelpers::calculateTierDetailsV3($product['product_id'], $product['variation_id'], $qty);
                            if ($tierDetails['total_price'] > 0) {
                                $regularPrice = $tierDetails['total_price'];
                                $shippingCost = $tierDetails['total_shipping'] ?? 0;
                            }
                        }else{
                            $tierDetails = CartHelpers::calculateTierDetailsV3($product['product_id'], null, $qty);

                            if ($tierDetails['total_price'] > 0) {
                                $regularPrice = $tierDetails['total_price'];
                                $shippingCost = $tierDetails['total_shipping'] ?? 0;
                            }
                        }
                        $subTotal += (int) round($regularPrice * 100);
                    }
                    $totalShipping += $shippingCost;
                    $productIds[] = $product['product_id'];
                    $updatedProducts[] = $product;
                }

                $storeData['products'] = $updatedProducts;
                $productData[$storeId] = $storeData;
                $storeIds[] = $storeId;
            }
            
            $discountedBase  = max(0, $subTotal - ($totalDiscount * 100));
            $totalDue = $discountedBase + $customizationTotal;
            $grandTotal = $totalDue + ($totalShipping * 100);
            $paymentIntent = $this->coreRepository->createPaymentIntentV3(
                $grandTotal, // already in cents
                'usd',
                $customerId,
                [
                    'store_ids' => json_encode(array_values(array_unique($storeIds))),
                    'product_ids' => json_encode(array_values(array_unique($productIds))),
                    'guest' => $guest,
                    'user_id' => $checkoutUserId,
                    'buy_now' => $buyNow,
                    'checkout' => 'new',
                ]
            );

            if (isset($request['platform'])) {
                if ($request['platform'] == 'web') {
                    $response = ['id' => $paymentIntent];
                } elseif ($request['platform'] == 'mobile') {
                    $response = ['id' => $paymentIntent->url];
                }
            }
            $logData = [
                'event_type' => 'stripe_intent_created',
                'user_id' => $checkoutUserId,
                'data' => json_encode(['stripe_intent_created' => $response]),
            ];
            $log = CoreHelpers::checkoutLogV3($logData);
            CoreHelpers::findAndUpdateLogWithSessionIdV3($checkoutUserId,$paymentIntent->id);
            if($user){
                $response = $this->coreRepository->processOrderV3(
                    $paymentIntent,
                    $productData,
                    $request,
                    $user->user_id,
                    $checkoutUserId
                );
                $logData = [
                    'event_type' => 'complete_order_processed',
                    'user_id' => $checkoutUserId,
                    'session_id' => $paymentIntent->id,
                    'data' => json_encode(['complete_order_processed' => $paymentIntent->id]),
                ];
                $log = CoreHelpers::checkoutLogV3($logData);
            }
            return $response;

        } catch (\Exception $e) {
            $error = [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ];
            $logData = [
                'event_type' => 'order_processing_failed',
                'user_id' => $checkoutUserId,
                'data' => json_encode(['exception' => $error]),
            ];
            $log = CoreHelpers::checkoutLog($logData);
            throw new \Exception('Order processing failed: ' . $e->getMessage());
        }
    }

     public function cryptoCheckout($request, $user,$checkoutUserId)
    {
        try {
            $logData = [
                'event_type' => 'started_order_creation',
                'user_id' => $checkoutUserId,
                'data' => json_encode(['creating_stripe_intent' =>$request['product_data']]),
            ];
            $log = CoreHelpers::checkoutLog($logData);
            $storeIds = [];
            $productIds = [];
            $lineItems = [];
            $totalDiscount = 0;
            $totalShipping = 0;
            $subTotal = 0;
            $customizationTotal = 0;
            $grandTotal = 0;
            $totalDue = 0;
            $guest = $request['guest'] ?? false;
            $userId = $user->user_id ?? null;
            $checkoutUserId = $request['id'] ?? null;
            $buyNow = $request['buy_now'] ?? null;
            $productData = $request['product_data'];

            foreach ($productData as $storeId => $storeData) {
                $updatedProducts = [];

                foreach ($storeData['products'] as $product) {
                    $salePrice = 0;
                    $discountAmount = 0;
                    $regularPrice = 0;
                    $imageUrl = '';
                    $couponCode = $product['coupon_code'] ?? null;
                    $qty = $product['quantity'];
                    if(isset($product['whole_sale']) && !$product['whole_sale']){
                        
                        $originalPrice = $product['price'];
                        $salePrice = $product['sale_price'] ?? 0;
                        $discountAmount = $salePrice > 0 ? $originalPrice - $salePrice : 0;
                        $totalDiscount += $discountAmount * $qty;
                         // include customization price for custom products
                        if (isset($product['is_custom'])) {
                            if (!empty($product['customizations'])) {
                                    $custom = $product['customizations'];                                    
                                    $customPrice = isset($custom['price']) ? (float)$custom['price'] : 0;
                                    $customQuantity = isset($custom['product_quantity']) ? (int)$custom['product_quantity'] : 1;
                                    $customizationTotal += $customPrice * $customQuantity;
                            }
                        }
                        $subTotal += (int) round($originalPrice) * $qty;

                        // Shipping Calculation
                        $shippingId = $product['shipping_detail']['service_id'] ?? null;
                        $shippingCost = 0;

                        if ($shippingId && $product['shipping_detail']['shipping_method'] === 'free') {
                            $product['shipping_detail']['shipping_amount'] = 0;
                        } elseif ($shippingId && $product['shipping_detail']['shipping_method'] === 'fixed') {
                            $shipping = FixedShipping::find($shippingId);

                            if ($shipping && $shipping->shipping_per_item) {
                                $perItemPrice = (float) $shipping->per_item_price;
                                $maxQty = $shipping->max_items_allowed_for_price;
                                $additional = (float) $shipping->additional_price;

                                if ($maxQty && $qty > $maxQty) {
                                    $baseShipping = $perItemPrice * $maxQty;
                                    $extraShipping = $additional * ($qty - $maxQty);
                                    $product['shipping_detail']['base_price'] = $baseShipping;
                                    $product['shipping_detail']['handling_fee'] = $extraShipping;
                                    $product['shipping_detail']['shipping_amount'] = round($baseShipping + $extraShipping, 2);
                                } else {
                                    $baseShipping = $perItemPrice * $qty;
                                    $product['shipping_detail']['base_price'] = $baseShipping;
                                    $product['shipping_detail']['handling_fee'] = 0;
                                    $product['shipping_detail']['shipping_amount'] = round($baseShipping, 2);
                                }
                            }else if($shipping && $shipping->shipping_by_weight){
                                $perWeightPrice = (float) $shipping->base_price;
                                $maxWeightAllowed = $shipping->max_weight_allowed_for_price;
                                $additional = (float) $shipping->additional_price_weight;
                                $profileUnit = $shipping->weight_unit ?? 'lb';

                                $totalWeight = (float) $product['weight'] * (int) $qty;
                                $convertedWeight = CoreHelpers::convertWeightToTargetUnit(
                                    $totalWeight,
                                    $product['weight_unit'],
                                    $profileUnit
                                );
                                $weight = ceil($convertedWeight);
                                if($maxWeightAllowed){
                                    $maxTotalWeight = $maxWeightAllowed * $shipping->base_price_per_weight;
                                }else{
                                    $maxTotalWeight = $maxWeightAllowed;
                                }
                                if($maxTotalWeight && $maxTotalWeight > 0 && $weight > $maxTotalWeight) {
                                    $baseShipping = $perWeightPrice;
                                    $extraShipping = $additional * ($weight - $maxTotalWeight);
                                    $product['shipping_detail']['base_price'] = $baseShipping;
                                    $product['shipping_detail']['handling_fee'] = $extraShipping;
                                    $product['shipping_detail']['shipping_amount'] = round($baseShipping + $extraShipping, 2);
                                } else {
                                    $baseShipping = $perWeightPrice;
                                    $product['shipping_detail']['base_price'] = $baseShipping;
                                    $product['shipping_detail']['handling_fee'] = 0;
                                    $product['shipping_detail']['shipping_amount'] = round($baseShipping, 2);
                                }
                            } else {
                                $product['shipping_detail']['shipping_amount'] = 0;
                            }
                        }else{
                            $product['shipping_detail']['shipping_amount'] = 0;
                        }
                        $shippingCost = $product['shipping_detail']['shipping_amount'] ?? 0;
                    }else{
                        if($product['product_type'] === 'variant'){
                                $tierDetails = CartHelpers::calculateTierDetails($product['product_id'], $product['variation_id'], $qty);
                                if ($tierDetails['total_price'] > 0) {
                                    $regularPrice = $tierDetails['total_price'];
                                    $shippingCost = $tierDetails['total_shipping'] ?? 0;
                                }
                        }else{
                            $tierDetails = CartHelpers::calculateTierDetails($product['product_id'], null, $qty);

                            if ($tierDetails['total_price'] > 0) {
                                $regularPrice = $tierDetails['total_price'];
                                $shippingCost = $tierDetails['total_shipping'] ?? 0;
                            }
                        }

                        $subTotal += (int) round($regularPrice);
                    }
                    $totalShipping += $shippingCost;

                    $productIds[] = $product['product_id'];
                    $updatedProducts[] = $product;
                }

                $storeData['products'] = $updatedProducts;
                $productData[$storeId] = $storeData;
                $storeIds[] = $storeId;
            }
            
            $discountedBase  = max(0, $subTotal - ($totalDiscount));
            $totalDue = $discountedBase + $customizationTotal;            
            $grandTotal = $totalDue + ($totalShipping);
            $logData = [
                'event_type' => 'Building checkout data',
                'user_id' => $checkoutUserId,
                'data' => json_encode(['Building checkout data' => $productData]),
            ];
            $log = CoreHelpers::checkoutLog($logData);
            if($user){
                $response = $this->coreRepository->processOrderCrypto(
                    $productData,
                    $request,
                    $user->user_id,$checkoutUserId,
                    $grandTotal
                );
                CoreHelpers::findAndUpdateLogWithSessionId($checkoutUserId,$response['id']);
                $logData = [
                    'event_type' => 'complete_order_processed',
                    'user_id' => $checkoutUserId,
                    'session_id' => $response['id'],
                    'data' => json_encode(['complete_order_processed' => $response['id']]),
                ];
                $log = CoreHelpers::checkoutLog($logData);
                return $response;
            }

        } catch (\Exception $e) {
            $error = [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ];
            $logData = [
                'event_type' => 'order_processing_failed',
                'user_id' => $checkoutUserId,
                'data' => json_encode(['exception' => $error]),
            ];
            $log = CoreHelpers::checkoutLog($logData);
            throw new \Exception('Order processing failed: ' . $e->getMessage());
        }
    }
    public function getOrderSummary($sessionId)
    {
        $orders = $this->coreRepository->getOrderSummary($sessionId);

        foreach ($orders as $order) {
            foreach ($order->orderItems as $item) {
                $item->customizations = json_decode($item->customizations, true);
               $item->image_url = url('uploads/orders').'/'.$item->item_image;
            }
        }

        return $orders;
    }
    public function getPackageCheckout($data)
    {
        try {
            $user = auth()->user();

            if (!$user) {
                throw new Exception('User not authenticated.');
            }

            $customer = $this->coreRepository->findOrCreateStripeCustomer($user);
            $customerId = $customer->id;

            $storeId = $data['store_id'];
            $amount = (float) $data['amount'];
            $unitAmountCents = intval($amount * 100);

            $lineItems = [[
                'price_data' => [
                    'currency' => 'usd',
                    'product_data' => [
                        'name' => $data['package_name'],
                    ],
                    'unit_amount' => $unitAmountCents,
                ],
                'quantity' => 1,
            ]];

            // Common Checkout payload
            $payload = [
                'payment_method_types' => ['card', 'crypto'],
                'line_items' => $lineItems,
                'customer' => $customerId,
                'mode' => 'payment',
                'metadata' => [
                    'subtotal' => $amount,
                    'total_due' => $amount,
                    'store_id' => $storeId,
                    'type'=>'package_purchase',
                ],
                'custom_text' => [
                    'submit' => [
                        'message' => 'Note: Only USDC and USDP accepted for crypto payments.',
                    ],
                ],
            ];

            // Add platform-specific fields
            if (isset($data['platform']) && $data['platform'] === 'mobile') {
                $payload['success_url'] = env('APP_URL') . '/stripe/mobile-success?id={CHECKOUT_SESSION_ID}';
                $payload['cancel_url'] = env('APP_URL') . '/stripe/mobile-cancel';
            } elseif (isset($data['platform']) && $data['platform'] === 'web') {
                $payload['ui_mode'] = 'embedded';
                $payload['return_url'] = env('PACKAGE_RETURN_URL') . '?id={CHECKOUT_SESSION_ID}';
            }

            $checkoutSession = Session::create($payload);

            $this->coreRepository->storeMallsharkPurchasedPackage($data,$checkoutSession->id);

            if ($data['platform'] === 'web') {
                return response()->json(['id' => $checkoutSession->client_secret]); // for embedded checkout
            } elseif ($data['platform'] === 'mobile') {
                return response()->json(['id' => $checkoutSession->url]); // for redirect URL
            } else {
                return response()->json(['message' => 'Unknown platform.'], 400);
            }

        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
    public function getUspsRates($orderId, $processItems)
    {
        try {
            $order = $this->getCompleteOrderData($orderId);
            $order = $order[0] ?? null;

            if (!$order) {
                throw new Exception('Order not found.');
            }

            $orderItems = $order['order_items'] ?? [];
            if (empty($orderItems)) {
                throw new Exception('No items in the order.');
            }

            $filteredItems = collect($orderItems)
                ->whereIn('order_item_id', $processItems)
                ->where('status', '==', OrderConstants::ORDER_PENDING);

            if ($filteredItems->isEmpty()) {
                throw new Exception('No valid items found to process — all items may already be shipped.');
            }

            $products = [];

            foreach ($filteredItems as $item) {
                $products[] = [
                    "product_id"     => $item['product']['product_id'] ?? null,
                    "store_id"       => $item['store_id'] ?? null,
                    "quantity"       => $item['quantity'] ?? 0,
                    "weight"         => $item['product']['weight'] ?: 1,
                    "weight_unit"    => $item['product']['weight_type'] ?? null,
                    "length" => $item['product']['productSize_length'] ?: 1,
                    "width"  => $item['product']['productSize_width'] ?: 1,
                    "height" => $item['product']['productSize_height'] ?: 1,
                    "new_shipping"   => !empty($item['product']['new_shipping']) ? true : false,
                    "dimension_unit" => $item['product']['dimention_type'] ?? null,
                    "product_type"   => $item['product']['product_type'] ?? null,
                    "variation_id"   => $item['variation_id'] ?? null,
                ];
            }
            $storeId = $order['store_id'];
            $recipientPostalCode = $order['shipping_address']['post_code'] ?? null;
            $uspsRates = $this->getUspsShippingRates($products, $storeId, $recipientPostalCode);
            $rates = CoreHelpers::filterUspsResponse($uspsRates);

            return $rates;
        } catch (ItemNotFoundException $e) {
            throw new Exception($e->getMessage());
        } catch (Exception $e) {
            throw new Exception('Failed: ' . $e->getMessage());
        }
    }

    public function getUspsShippingRates($product, $storeId, $recipientPostalCode)
    {
        try {
            $store = $this->getStoreById($storeId);
            $shipperPostalCode = $store['zip_code'] ?? null;

            $packageFormat = $this->convertPackageFormat($product);
            
            $baseUrl = env('USPS_BASE_URL');
            $accessToken = $this->getUspsAccessToken();

            if (empty($shipperPostalCode) || empty($recipientPostalCode)) {
                throw new Exception('Missing postal code: origin (' . ($shipperPostalCode ?: 'null') . ') or destination (' . ($recipientPostalCode ?: 'null') . ').');
            }
            $payload = [
                "originZIPCode" => $shipperPostalCode,
                "destinationZIPCode" => $recipientPostalCode,
                "weight" => max($packageFormat['weight']['value'], 0.01),
                "length" => max($packageFormat['dimensions']['length'], 1),
                "width" => max($packageFormat['dimensions']['width'], 1),
                "height" => max($packageFormat['dimensions']['height'], 1),
                "mailClasses" => CoreConstants::USPS_MAIL_CLASSES,
                "priceType" => "COMMERCIAL",
                "mailingDate" => now()->toDateString(),
                "accountType" => "EPS",
                "accountNumber" => env('USPS_EPS_NUMBER'),
                "hasNonstandardCharacteristics" => false,
                "itemValue" => 0,
                "extraServices" => [986],
            ];

             $response = Http::retry(3, 200, function ($exception) {
                    // Only retry on connection errors, not on 4xx client errors
                    return $exception instanceof \Illuminate\Http\Client\ConnectionException;
                })
                ->withToken($accessToken)
                ->acceptJson()
                ->post($baseUrl . '/prices/v3/total-rates/search', $payload);

            if ($response->failed()) {
                $formattedError = CoreHelpers::filterUspsError($response->json());
                throw new Exception($formattedError['message']);
            }

            return $response->json();
        } catch (\Illuminate\Http\Client\RequestException $e) {
            $responseBody = $e->response ? $e->response->json() : null;
            if ($responseBody) {
                $formattedError = CoreHelpers::filterUspsError($responseBody);
                throw new Exception($formattedError['message']);
            }
            throw new Exception($e->getMessage());
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    public function uspsValidateAddress($orderId)
    {
        try {
            $order = CoreHelpers::getOrderById($orderId);
            $shipId = $order->shipping_address;
            $shipping = CoreHelpers::getShippingAddressById($shipId);
            $state = CoreHelpers::getStatebyId($shipping->state);

            $accessToken = $this->getUspsAccessToken();
            $baseUrl = env('USPS_BASE_URL');

            $payload = [
                "streetAddress" => $shipping->street ?? '',
                "city"          => $shipping->city ?? '',
                "state"         => $state['code'] ?? '',
                "ZIPCode"       => $shipping->post_code ?? '',
            ];

            if (
                empty($payload['streetAddress']) ||
                empty($payload['city']) ||
                empty($payload['ZIPCode']) ||
                empty($payload['state'])
            ) {
                $shipping->update(['is_valid' => 'invalid']);

                return response()->json([
                    'status'  => false,
                    'message' => 'Incomplete shipping address: street, city, state, and postal code are required for USPS validation.',
                    'data'    => null,
                ], 400);
            }

            $response = Http::retry(3, 200)
                ->withToken($accessToken)
                ->acceptJson()
                ->get($baseUrl . '/addresses/v3/address', $payload);

            if ($response->failed()) {
                $json = $response->json();
                $errorMsg = $json['error']['detail'] ?? $json['error']['message'] ?? 'Invalid USPS address request.';
                $errorCode = $json['error']['code'] ?? $response->status();

                $shipping->update(['is_valid' => 'invalid']);

                return response()->json([
                    'status'  => false,
                    'message' => "USPS validation failed: {$errorMsg}",
                    'data'    => null,
                ], $response->status());
            }

            $data = $response->json();
            $dpv = $data['additionalInfo']['DPVConfirmation'] ?? 'N';
            $isValid = ($dpv === 'Y');

            $shipping->update(['is_valid' => $isValid ? 'valid' : 'invalid']);

            return response()->json([
                'status'  => $isValid,
                'message' => $isValid
                    ? 'Address is valid and standardized by USPS.'
                    : 'Address is invalid or not confirmed by USPS.',
                'data'    => $data,
            ], 200);

        } catch (Exception $e) {
            $shipping = isset($shipId) ? CoreHelpers::getShippingAddressById($shipId) : null;
            if ($shipping) {
                $shipping->update(['is_valid' => 'invalid']);
            }
            Log::error('USPS Validate Address Exception: ' . $e->getMessage());
            throw new Exception('Address validation failed: invalid or incomplete address.');
        }
    }

    public function updateShippingDetails($orderId, array $data)
    {
        $storeOrder = $this->coreRepository->getOrderById($orderId);

        if (!$storeOrder) {
            throw new Exception("Store order not found.");
        }

        $storeOrder->tracking_id = $data['tracking_id'] ?? null;
        $storeOrder->ship_label_url = $data['ship_label_url'] ?? null;
        $storeOrder->fedex_ship_amount = $data['ship_amount'] ?? null;
        $storeOrder->ship_service = $data['ship_service'] ?? null;
        $storeOrder->delivery_date = $data['delivery_date'] ?? null;
        $storeOrder->order_status = $data['status'] ?? null;
        $storeOrder->shipped_date = now();

        $trackingUrl = $data['tracking_id'] ?? null;

        $isSaved = $storeOrder->save();

        if ($isSaved) {
            $this->sendShippedMail($orderId, $trackingUrl);
            return true;
        } else {
            return false;
        }
    }

    public function sendShippedMail($orderId, $trackingUrl)
    {
        $message = Constants::SHIPPED_ORDER_BUYER_SUBJECT . $orderId;
        $this->mailgun = new MailgunController();

        $orderData = $this->getCompleteOrderData($orderId);
        $order = $orderData[0] ?? null;

        $shippingAddress = $order['shipping_address'] ?? null;
        $buyer = $order['user'] ?? null;
        $buyerEmail = $buyer['email'];
        $buyerId = $buyer['user_id'];

        $productArray = [];

        foreach ($order['order_items'] as $item) {
            $product = $item['product'] ?? [];

            $productArray[] = [
                'title' => $item['product_name'] ?? $product['title'] ?? '',
                'slug' => $product['slug'] ?? '',
                'feature_image' => $item['item_image'] ?? $product['feature_image'] ?? '',
                'product_type' => $product['product_type'] ?? '',
                'price_total' => $item['total'],
                'price' => $item['price'],
                'quantity' => $item['quantity'],
                'sold_price' => $item['sold_price'],
                'shipping_amount' => $item['shipping_amount'],
                'variable_image' => $item['item_image'] ?? $product['feature_image'] ?? '',
            ];
        }

        // $this->pushNotification($buyerId, $message, 'buyer', '/new-buyer-orders-detail/' . $orderId);

        $html = view('mail.fedex-shipped-client', [
            'userData' => $buyer,
            'shippingAddress' => $shippingAddress,
            'trackingUrl' => $trackingUrl,
            'orderItems' => $productArray,
            'orderId' => $orderId
        ])->render();

        $subject = 'Your MallShark Order Has Been Shipped!';

        $bccEmails = ['care@mallshark.com'];

        $params = [
            'from'    => 'MallShark  ' . env('SUPPORT_EMAIL'),
            'to'      => $buyerEmail,
            'subject' => $subject,
            'html'    => $html,
        ];
        if (app()->environment('production')) {
            $params['bcc'] = $bccEmails;
        }
        return $this->mailgun->SendMailgunMail($params);
    }

    public function calculateTax($stateId, $CategoryId, $amount, $quantity, $postalCode = null)
{

    $state = States::where('state_id', $stateId)->first();
    if (!$state) {
        return ['error' => 'Invalid state'];
    }
                
    $subtotal = $amount * $quantity;

    // 1️⃣ Map product category → category_slug → tax_category_id
    $category = Category::find($CategoryId);
    if (!$category) {
        return ['error' => 'Invalid product category'];
    }
                
    $categorySlug = $category->category_slug;            
    $taxCategory = TaxCategory::where('name', $categorySlug)->first();
    if (!$taxCategory) {
        // fallback tax category
        $taxCategory = TaxCategory::where('name', 'uncategorized')->first();
    }
               
    $taxCategoryId = $taxCategory->id;
    $stripeTaxCode = $taxCategory->stripe_tax_id ?? 'txcd_99999999';
                
    // 2️⃣ Check if rate exists in DB
    // $stateTax = StateCategoryTax::where('state_id', $stateId)
    //     ->where('tax_category_id', $taxCategoryId)->where('postal_code',$postalCode)
    //     ->first();
               
    // if ($stateTax) {
    //     $rate = (float) $stateTax->rate;
    //     $taxAmount = round($subtotal * $rate, 2);
                
    //     return [
    //         'source' => 'database',
    //         'state_id' => $stateId,
    //         'tax_category_id' => $taxCategoryId,
    //         'product_category_id' => $CategoryId,
    //         'rate' => $rate,
    //         'subtotal' => $subtotal,
    //         'tax_amount' => $taxAmount,
    //         'total' => round($subtotal + $taxAmount, 2),
    //         ];
    // }
    // 3️⃣ Fallback → Stripe API
    try {
        $stripe = new \Stripe\StripeClient(env('STRIPE_API_KEY'));
               
        $calculation = $stripe->tax->calculations->create([
            'currency' => 'usd',
            'line_items' => [[
                'reference' => 'item_1',
                'amount' => (int) round($amount * 100),
                'quantity' => $quantity,
                'tax_code' => $stripeTaxCode,
            ]],
            'customer_details' => [
                'address_source' => 'shipping',
                'address' => [
                    'country' => 'US',
                    'state' => $state->code,
                    'postal_code' => $postalCode,
                ],
            ],
        ]);
        $taxAmountCents = $calculation->tax_amount_exclusive;
        $taxableCents   = $calculation->tax_breakdown[0]->taxable_amount ?? 0;

        $rate = $taxableCents > 0
            ? round($taxAmountCents / $taxableCents, 6)
            : 0;

        $taxAmount = round($taxAmountCents / 100, 2);

        // 4️⃣ Save discovered rate
        // $result = StateCategoryTax::updateOrCreate(
        //     ['state_id' => $stateId, 'tax_category_id' => $taxCategoryId,'mallshark_categories'=>$CategoryId,'postal_code' => $postalCode],
        //     ['rate' => $rate, 'expires_at' => Carbon::now()->addMonths(6)]
        // );
                
        return [
            'source' => 'stripe',
            'state_id' => $stateId,
            'product_category_id' => $CategoryId,
            'tax_category_id' => $taxCategoryId,
            'rate' => $rate,
            'subtotal' => $subtotal,
            'tax_amount' => $taxAmount,
            'total' => round($subtotal + $taxAmount, 2),
        ];

    } catch (\Stripe\Exception\ApiErrorException $e) {
        return [
            'error' => $e->getMessage(),
            'type' => $e->getError()->type ?? null,
            'code' => $e->getError()->code ?? null,
            'param' => $e->getError()->param ?? null,
        ];
    }
}
    public function cryptoWebhook(Request $request)
    {
        try {
            // Raw payload is required for signature verification
            $payloadRaw = $request->getContent();
            $secret = env('NOWPAYMENTS_IPN_SECRET');
            $logData = [
                'event_type' => 'webhook success',
                'session_id' => '123',
                'data' => '1234',
            ];
            $log = CoreHelpers::checkoutLog($logData);
            if (empty($secret)) {
                Log::error('NOWPayments Webhook: NOWPAYMENTS_IPN_SECRET not configured');
                return response()->json(['error' => 'Server misconfigured'], 500);
            }

            $signature = (string) $request->header('x-nowpayments-sig', '');
            $expected = hash_hmac('sha512', $payloadRaw, $secret);

            if (!hash_equals($expected, $signature)) {
                Log::warning('NOWPayments Webhook: Invalid signature', ['expected' => $expected, 'signature' => $signature]);
                return response()->json(['error' => 'Invalid signature'], 403);
            }

            $payload = json_decode($payloadRaw, true);
            if (!is_array($payload)) {
                // fallback to parsed input
                $payload = $request->all();
            }

            $orderRef = $payload['order_id'] ?? null;
            $statusRaw = $payload['payment_status'] ?? $payload['status'] ?? null;
            $invoiceId = $payload['invoice_id'] ?? null;
            $paymentId = $payload['payment_id'] ?? null;
            $logData = [
                'event_type' => 'webhook success',
                'session_id' => $invoiceId,
                'data' => json_encode($payloadRaw),
            ];
            $log = CoreHelpers::checkoutLog($logData);
            if (empty($orderRef) && empty($invoiceId) && empty($paymentId)) {
                Log::error('NOWPayments Webhook: Missing identifiers', $payload);
                return response()->json(['error' => 'Missing identifiers'], 400);
            }

            // Normalize order IDs: accept array, JSON string, comma-separated or single value
            $orderIds = [];
            // First urldecode the string
            $decodedRef = urldecode($orderRef);
            // Remove the array wrapper if present
            $decodedRef = is_array($orderRef) ? $orderRef[0] : $decodedRef;
            // Try JSON decode
            $decoded = json_decode($decodedRef, true);
            
            if (is_array($decoded)) {
                $orderIds = $decoded;
            } elseif (strpos((string)$decodedRef, ',') !== false) {
                $orderIds = array_map('trim', explode(',', (string)$decodedRef));
            } else {
                $orderIds = [(string)$decodedRef];
            }
            // Update matching orders inside transaction
            DB::beginTransaction();
            if($statusRaw == 'finished'){
                $orders = $this->coreRepository->getOrdersBySessionId($invoiceId);
            
                if($orders){
                    $updated = $this->updateStoreOrderViaCrypto($orders, $paymentId);
                }
                // Pass an array of orders to the helper and capture the return value
                $this->coreRepository->deleteCartByCryptoKey($invoiceId);
                $logData = [
                    'event_type' => 'webhook success',
                    'session_id' => $invoiceId,
                    'data' => json_encode(['date_time' => now(),'Success' => true]),
                ];
                $log = CoreHelpers::checkoutLog($logData);
            }
            
            DB::commit();

            return response()->json(['status' => true], 200);
        } catch (\Exception $e) {
            try { DB::rollBack(); } catch (\Exception $_) {}
            $logData = [
                'event_type' => 'webhook fail',
                'session_id' => $invoiceId,
                'data' => json_encode(['date_time' => now(),'Error' => $e->getMessage()]),
            ];
            $log = CoreHelpers::checkoutLog($logData);
            $html = view('mail.payment-receive-failed', [
                    'payload' => $request->all(),
                    'errorMessage' =>  $e->getMessage(),
                ]);

                $subject = "⚠️ Crypto Webhook Failed";
                $toEmail = 'abdullah.jutt@mindblaze.biz';
                $params = [
                    'from'    => 'MallShark  ' . env('SUPPORT_EMAIL'),
                    'to'      => $toEmail,
                    'subject' => $subject,
                    'html'    => "$html",
                ];
                if(app()->environment('production')){
                    $this->mailgun->SendMailgunMail($params);
                }
            return response()->json(['status' => true], 200);
        }
    }
    public function updateStoreOrderViaCrypto($orders,$intentId)
    {
        try {
            $sessionId = $orders[0]->session_id;
            $buyerId = $orders[0]->buyer_id;
            $paymentMethod = $orders[0]->payment_method;
            $user = CoreHelpers::getUserById($buyerId);
            $loginKey = $user->login_key;
            $userId = $user->user_id;
            $guestUser = $user->guest_user;
            $orderIds = [];
            foreach ($orders as $order) {
                $orderId = $order->order_id;
                $orderIds[] = $orderId;
                $storeId = $order->store_id;
                $response = CoreHelpers::getStoreInfoById((int)$storeId);
                $store = $response[0];
                $storeWallet = $store->wallet_address ?? '';
                $totalAmount = $order->total_amount;
                $payableAmount = $order->payable_amount;
                $shippingAmount = $order->shipping_amount;
                $amountWithOutShip = $totalAmount - $shippingAmount;
                
                $paymentProfit = $order->platform_fee;
                $order->charge_status = 'paid';
                $order->transaction_id = $intentId;
                $order->store_tax = null;
                $order->save();
                $couponUsage = DiscountUsage::where('order_id', $orderId)->where('user_id',$userId)->first();
                if ($couponUsage) {
                    $couponUsage->usage_count = 1;
                    $couponUsage->save();
                    $discount = Discount::find($couponUsage->discount_id);
                    if ($discount) {
                        $discount->usage_count += 1;
                        $discount->save();
                    }
                }
                foreach ($order->orderItems as $item) {
                    $remainStock = "";
                    $productId = $item['item_id'];
                    
                    $unitSold = CoreHelpers::getProductOrderCount($productId);
                    if ($unitSold >= ProductConstants::BEST_SELLER_COUNT) {
                        CoreHelpers::assignLabelToProduct($productId, ProductConstants::BEST_SELLER_LABEL);
                    }
                    
                    $variationId = $item['variation_id'];
                    $quantity = $item['quantity'];
                    $product = $this->getProductById($item['item_id']);
                    // ----------------------------------------------------------------
                    // decrease product stock after order placed
                    // ----------------------------------------------------------------
                    if($product->unique_product ==  true){
                         CoreHelpers::simpleUpdateCurrentStock($item['item_id'], $item['quantity']);
                    }else{
                        if (isset($item['variation_id']) && $item['variation_id'] != '' && $item['variation_id'] != 0) {
                            CoreHelpers::variableUpdateCurrentStock($item['variation_id'], $item['item_id'], $item['quantity']);
                            // change status of flash sale
                            $productFlashsaleStatus = $product->flashsale_status;
                            
                            if($product->is_shopify_import){
                                $this->shopifyController->orderPlacedInventorySyncToShopify($productId,$quantity,$variationId);
                            }

                        } else {
                            CoreHelpers::simpleUpdateCurrentStock($item['item_id'], $item['quantity']);
                            // change flash sale status
                            $remainStock = $product->current_stock;
                            if ($product->flashsale_status == 'choosen' && $remainStock <= 0) {
                                $product->update(['flashsale_status' => 'standard']);
                            }
                            if($product->is_shopify_import){
                                $this->shopifyController->orderPlacedInventorySyncToShopify($productId,$quantity);
                            }
                        }
                    }
                }

                $buyerId = $user->user_id;
                $buyerData = $user;
                $customerEmail = $buyerData->email;
                //create order on shipstation
                if (in_array($storeId, CoreConstants::SHIPSTATION_STORE_IDS)) {
                    // CreateShipstationOrderJob::dispatch($orderId);
                    UspsValidateAddressJob::dispatch($orderId);
                }

                $orderId = $order->order_id;
                $sellerId = $store->user_id ?? '';

                //prepare notification for buyer
                $subjectBuyer = Constants::ORDER_CREATE_SUBJECT_BUYER . $orderId;
                addNotifications($buyerId, $sellerId, $subjectBuyer, '', '/new-buyer-orders-detail/' . $orderId, 'buyer_order_details', $orderId, 'buyer');
                $this->pushNotification($buyerId, $subjectBuyer, 'buyer');

                // prepare notification for seller 
                $subjectSeller = Constants::ORDER_CREATE_SUBJECT_SELLER . $orderId;
                addNotifications($sellerId, $buyerId, $subjectSeller, '', '/order-detail/' . $orderId, 'brand_order_details', $orderId, 'seller');
                $this->pushNotification($sellerId, $subjectSeller, 'seller');

                $storeName = $store->store_name ?? '';
                $shippingAddress = CoreHelpers::getShippingAddressById($order->shipping_address);
                $sellerUser = CoreHelpers::getUserById((int) $sellerId);
                $toEmailStore = $sellerUser->email;
                $stateObj = CoreHelpers::getStatebyId($shippingAddress->state);
                $countryObj = CoreHelpers::getCountryById($shippingAddress->country);
                $state = $stateObj ? $stateObj['state_name'] : '';
                $country = $countryObj ? $countryObj['name'] : 'USA';
                $html = view('mail.storecreateOrder', [
                    'email' => $customerEmail,
                    'apartmentNo' => $shippingAddress->apartment_no,
                    'name' =>  $shippingAddress->full_name,
                    'city' => $shippingAddress->city,
                    'street' => $shippingAddress->street,
                    'postalcode' => $shippingAddress->post_code,
                    'state' => $state,
                    'country' => $country,
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
                
                SendOrderEmailsJob::dispatch($params);
                $this->sendLowStockEmail($orderId);
                // update discountusage table if discount or coupon applied
            }
            $logData = [
                'event_type' => 'payment_success',
                'session_id' => $sessionId,
                'data' => json_encode(['session' => $sessionId,'user' => $user,'orders'=>$orders]),
            ];
            $log = CoreHelpers::checkoutLog($logData);
            
            $url = env('FRONT_END_URL').'/verification/password-change?token='.$loginKey;
            $html = view('mail.new-order-client-side', [
                'customerDetail' => $buyerData,
                'productDetails' => $orders,
                'email' => $customerEmail,
                'name' => $shippingAddress->full_name,
                'city' => $shippingAddress->city,
                'apartmentNo' => $shippingAddress->apartment_no,
                'street' => $shippingAddress->street,
                'postalcode' => $shippingAddress->post_code,
                'state' => $state,
                'country' => $country,
                'orderIds' => $orderIds,
                'guestUser' => $guestUser,
                'activate_url' => $url

            ]);

            $subject = 'Order Placed! Order #' . implode(', ', $orderIds);
            $toEmail = $buyerData->email;
            $bccEmails = explode(',', env('CREATE_ORDER_BCC'));
            
            $params = [
                'from'    => 'MallShark  ' . env('SUPPORT_EMAIL'),
                'to'      => $toEmail,
                'subject' => $subject,
                'html'    => "$html",
            ];
            if (app()->environment('production')) {
                $params['bcc'] = $bccEmails;
            }
            SendOrderEmailsJob::dispatch($params);

            if($paymentMethod == 'crypto'){
                if($guestUser) {
                    $url = env('FRONT_END_URL').'/verification/password-change?token='.$loginKey.'&wallet=true';
                } else {
                    $url = env('FRONT_END_URL').'/login?'.'wallet=true';
                }
                $html = view('mail.add-wallet-activate-account', [
                    'activate_url' => $url

                ]);

                $subject = 'Account Setup!';
                $toEmail = $buyerData->email;
                $params = [
                    'from'    => 'MallShark  ' . env('SUPPORT_EMAIL'),
                    'to'      => $toEmail,
                    'subject' => $subject,
                    'html'    => "$html",
                ];
                
                SendOrderEmailsJob::dispatch($params);
            }
        }catch (\Exception $e) {
            $log = [
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString(),
            ];
            $logData = [
                'event_type' => 'webhook_fail',
                'session_id' => $intentId,
                'data' => json_encode($log),
            ];
            $log = CoreHelpers::checkoutLog($logData);
            throw new Exception($e->getMessage());
        }
    }
     // =====================================================
    // PAYPAL PAYMENT INTEGRATION METHODS
    // =====================================================

    /**
     * Get PayPal access token for API requests
     */
    protected function getPayPalAccessToken(): string
    {
        return Cache::remember('paypal_access_token', 3000, function () {
            $mode = config('paypal.mode', 'sandbox');
            $baseUrl = config("paypal.base_url.{$mode}");
            $clientId = config('paypal.client_id');
            $clientSecret = config('paypal.client_secret');

            $response = Http::withBasicAuth($clientId, $clientSecret)
                ->asForm()
                ->post("{$baseUrl}/v1/oauth2/token", [
                    'grant_type' => 'client_credentials',
                ]);

            if (!$response->successful()) {
                Log::error('PayPal Auth Error', ['response' => $response->json()]);
                throw new Exception('Failed to obtain PayPal access token');
            }

            return $response->json()['access_token'];
        });
    }

    /**
     * Make a request to the PayPal API
     */
    protected function paypalApiRequest(string $method, string $endpoint, array $data = []): array
    {
        $token = $this->getPayPalAccessToken();
        $mode = config('paypal.mode', 'sandbox');
        $baseUrl = config("paypal.base_url.{$mode}");

        $request = Http::withToken($token)
            ->withHeaders([
                'Content-Type' => 'application/json',
                'PayPal-Request-Id' => uniqid('paypal_', true),
            ]);

        // For empty data, send empty object {} (required for PayPal capture endpoint)
        if (empty($data)) {
            $response = $request->withBody('{}', 'application/json')->{$method}("{$baseUrl}{$endpoint}");
        } else {
            $response = $request->{$method}("{$baseUrl}{$endpoint}", $data);
        }

        if (!$response->successful()) {
            Log::error('PayPal API Error', [
                'endpoint' => $endpoint,
                'status' => $response->status(),
                'response' => $response->json(),
            ]);
            throw new Exception($response->json()['message'] ?? 'PayPal API request failed');
        }

        return $response->json();
    }

    /**
     * Create a PayPal order from checkout data
     */
    public function createPayPalOrder(array $request, $user, string $checkoutUserId): array
    {
        try {
            $productData = $request['product_data'];
            $shippingData = $request['shipping_data'] ?? null;
            $guest = $request['guest'] ?? false;
            $buyNow = $request['buy_now'] ?? false;

            // Calculate totals from product data
            $calculatedData = $this->calculatePayPalCheckoutTotals($productData);

            $logData = [
                'event_type' => 'paypal_order_creation_started',
                'user_id' => $checkoutUserId,
                'data' => json_encode([
                    'calculated_totals' => $calculatedData,
                ]),
            ];
            CoreHelpers::checkoutLogV3($logData);

            // Build PayPal order payload with user info for webhook identification
            $userId = $user->user_id ?? null;
            $userInfo = [
                'user_id' => $userId,
                'checkout_user_id' => $checkoutUserId,
                'is_guest' => $guest,
            ];
            $orderPayload = $this->buildPayPalOrderPayload($calculatedData, $shippingData, $userInfo);

            // Create PayPal order
            $paypalOrder = $this->paypalApiRequest('post', '/v2/checkout/orders', $orderPayload);

            $logData = [
                'event_type' => 'paypal_order_created',
                'user_id' => $checkoutUserId,
                'data' => json_encode([
                    'paypal_order_id' => $paypalOrder['id'],
                    'status' => $paypalOrder['status'],
                ]),
            ];
            CoreHelpers::checkoutLogV3($logData);

            // Create pending store orders in database
            $userId = $user->user_id ?? null;
            $orderIds = $this->createPayPalPendingOrders(
                $paypalOrder['id'],
                $calculatedData,
                $productData,
                $request,
                $userId,
                $checkoutUserId
            );

            return [
                'paypal_order_id' => $paypalOrder['id'],
                'status' => $paypalOrder['status'],
                'store_order_ids' => $orderIds,
            ];

        } catch (Exception $e) {
            Log::error('PayPal Create Order Error', [
                'error' => $e->getMessage(),
                'user_id' => $checkoutUserId,
            ]);
            throw $e;
        }
    }

    /**
     * Capture an approved PayPal order
     */
    public function capturePayPalOrder(string $paypalOrderId, string $checkoutUserId): array
    {
        try {
            $logData = [
                'event_type' => 'paypal_capture_started',
                'user_id' => $checkoutUserId,
                'data' => json_encode(['paypal_order_id' => $paypalOrderId]),
            ];
            CoreHelpers::checkoutLogV3($logData);

            // Capture the payment
            $captureResponse = $this->paypalApiRequest('post', "/v2/checkout/orders/{$paypalOrderId}/capture", []);

            $captureStatus = $captureResponse['status'] ?? 'UNKNOWN';
            $captureId = $captureResponse['purchase_units'][0]['payments']['captures'][0]['id'] ?? null;
            $payerId = $captureResponse['payer']['payer_id'] ?? null;

            $logData = [
                'event_type' => 'paypal_capture_completed',
                'user_id' => $checkoutUserId,
                'data' => json_encode([
                    'paypal_order_id' => $paypalOrderId,
                    'capture_id' => $captureId,
                    'status' => $captureStatus,
                ]),
            ];
            CoreHelpers::checkoutLogV3($logData);

            if ($captureStatus === 'COMPLETED') {
                // Update store orders to paid
                $this->updatePayPalOrdersToPaid($paypalOrderId, $captureId, $payerId);

                return [
                    'success' => true,
                    'paypal_order_id' => $paypalOrderId,
                    'capture_id' => $captureId,
                    'payer_id' => $payerId,
                    'status' => 'COMPLETED',
                ];
            }

            return [
                'success' => false,
                'paypal_order_id' => $paypalOrderId,
                'status' => $captureStatus,
                'message' => 'Payment capture was not completed',
            ];

        } catch (Exception $e) {
            Log::error('PayPal Capture Error', [
                'error' => $e->getMessage(),
                'paypal_order_id' => $paypalOrderId,
            ]);
            throw $e;
        }
    }

    /**
     * Handle PayPal webhook events
     */
    public function handlePayPalWebhook(Request $request): array
    {
        try {
            $payload = $request->all();
            $headers = $request->headers->all();
            $eventType = $payload['event_type'] ?? null;
            $resource = $payload['resource'] ?? [];

            Log::info('PayPal Webhook Received', [
                'event_type' => $eventType,
                'resource_id' => $payload ?? null,
            ]);

            // Verify webhook signature
            if (!$this->verifyPayPalWebhookSignature($payload, $headers)) {
                Log::warning('PayPal Webhook Signature Verification Failed');
                throw new Exception('Invalid webhook signature');
            }

            switch ($eventType) {
                case 'PAYMENT.CAPTURE.COMPLETED':
                    return $this->handlePayPalCaptureCompleted($resource);

                case 'PAYMENT.CAPTURE.DENIED':
                case 'PAYMENT.CAPTURE.REFUNDED':
                    return $this->handlePayPalCaptureFailed($resource, $eventType);

                case 'CHECKOUT.ORDER.APPROVED':
                    return $this->handlePayPalOrderApproved($resource);

                default:
                    Log::info('Unhandled PayPal Webhook Event', ['event_type' => $eventType]);
                    return ['success' => true, 'message' => 'Event acknowledged'];
            }

        } catch (Exception $e) {
            Log::error('PayPal Webhook Error', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Get PayPal order details
     */
    public function getPayPalOrderDetails(string $paypalOrderId): array
    {
        return $this->paypalApiRequest('get', "/v2/checkout/orders/{$paypalOrderId}");
    }

    /**
     * Get PayPal client ID for frontend SDK
     */
    public function getPayPalClientId(): string
    {
        return config('paypal.client_id');
    }

    /**
     * Calculate checkout totals from product data for PayPal
     * Implementation mirrors makeCheckoutDataV3 for Stripe to ensure consistency
     */
    protected function calculatePayPalCheckoutTotals(array $productData): array
    {
        $storeIds = [];
        $productIds = [];
        $totalDiscount = 0;
        $totalShipping = 0;
        $subTotal = 0;
        $customizationTotal = 0;
        $processedData = [];

        foreach ($productData as $storeId => $storeData) {
            $storeIds[] = $storeId;
            $updatedProducts = [];

            foreach ($storeData['products'] as $product) {
                $salePrice = 0;
                $discountAmount = 0;
                $regularPrice = 0;
                $couponCode = $product['coupon_code'] ?? null;
                $qty = $product['quantity'];
                $shippingCost = 0;

                if (isset($product['whole_sale']) && !$product['whole_sale']) {
                    // Regular retail product (not wholesale)
                    $originalPrice = $product['price'];
                    $salePrice = $product['sale_price'] ?? 0;
                    $discountAmount = $salePrice > 0 ? $originalPrice - $salePrice : 0;
                    $totalDiscount += $discountAmount * $qty;

                    // Include customization price for custom products
                    if (isset($product['is_custom'])) {
                        if (!empty($product['customizations'])) {
                            $custom = $product['customizations'];
                            $p = isset($custom['price']) ? (float)$custom['price'] : 0;
                            $pq = isset($custom['product_quantity']) ? (int)$custom['product_quantity'] : 1;
                            $customizationTotal += $p * $pq;
                        }
                    }
                    $subTotal += $originalPrice * $qty;

                    // Shipping Calculation
                    $shippingId = $product['shipping_detail']['service_id'] ?? null;

                    if ($shippingId && $product['shipping_detail']['shipping_method'] === 'free') {
                        $product['shipping_detail']['shipping_amount'] = 0;
                    } elseif ($shippingId && $product['shipping_detail']['shipping_method'] === 'fixed') {
                        $shipping = FixedShipping::find($shippingId);

                        if ($shipping && $shipping->shipping_per_item) {
                            $perItemPrice = (float) $shipping->per_item_price;
                            $maxQty = $shipping->max_items_allowed_for_price;
                            $additional = (float) $shipping->additional_price;

                            if ($maxQty && $qty > $maxQty) {
                                $baseShipping = $perItemPrice * $maxQty;
                                $extraShipping = $additional * ($qty - $maxQty);
                                $product['shipping_detail']['base_price'] = $baseShipping;
                                $product['shipping_detail']['handling_fee'] = $extraShipping;
                                $product['shipping_detail']['shipping_amount'] = round($baseShipping + $extraShipping, 2);
                            } else {
                                $baseShipping = $perItemPrice * $qty;
                                $product['shipping_detail']['base_price'] = $baseShipping;
                                $product['shipping_detail']['handling_fee'] = 0;
                                $product['shipping_detail']['shipping_amount'] = round($baseShipping, 2);
                            }
                        } else if ($shipping && $shipping->shipping_by_weight) {
                            $perWeightPrice = (float) $shipping->base_price;
                            $maxWeightAllowed = $shipping->max_weight_allowed_for_price;
                            $additional = (float) $shipping->additional_price_weight;
                            $profileUnit = $shipping->weight_unit ?? 'lb';

                            $totalWeight = (float) $product['weight'] * (int) $qty;
                            $convertedWeight = CoreHelpers::convertWeightToTargetUnit(
                                $totalWeight,
                                $product['weight_unit'],
                                $profileUnit
                            );
                            $weight = ceil($convertedWeight);
                            if ($maxWeightAllowed) {
                                $maxTotalWeight = $maxWeightAllowed * $shipping->base_price_per_weight;
                            } else {
                                $maxTotalWeight = $maxWeightAllowed;
                            }
                            if ($maxTotalWeight && $maxTotalWeight > 0 && $weight > $maxTotalWeight) {
                                $baseShipping = $perWeightPrice;
                                $extraShipping = $additional * ($weight - $maxTotalWeight);
                                $product['shipping_detail']['base_price'] = $baseShipping;
                                $product['shipping_detail']['handling_fee'] = $extraShipping;
                                $product['shipping_detail']['shipping_amount'] = round($baseShipping + $extraShipping, 2);
                            } else {
                                $baseShipping = $perWeightPrice;
                                $product['shipping_detail']['base_price'] = $baseShipping;
                                $product['shipping_detail']['handling_fee'] = 0;
                                $product['shipping_detail']['shipping_amount'] = round($baseShipping, 2);
                            }
                        } else {
                            $product['shipping_detail']['shipping_amount'] = 0;
                        }
                    } else {
                        $product['shipping_detail']['shipping_amount'] = 0;
                    }
                    $shippingCost = $product['shipping_detail']['shipping_amount'] ?? 0;

                } else {
                    // Wholesale product - use tier pricing
                    if ($product['product_type'] === 'variant') {
                        $tierDetails = CartHelpers::calculateTierDetailsV3($product['product_id'], $product['variation_id'], $qty);
                        if ($tierDetails['total_price'] > 0) {
                            $regularPrice = $tierDetails['total_price'];
                            $shippingCost = $tierDetails['total_shipping'] ?? 0;
                        }
                    } else {
                        $tierDetails = CartHelpers::calculateTierDetailsV3($product['product_id'], null, $qty);

                        if ($tierDetails['total_price'] > 0) {
                            $regularPrice = $tierDetails['total_price'];
                            $shippingCost = $tierDetails['total_shipping'] ?? 0;
                        }
                    }
                    $subTotal += $regularPrice;
                }
                $totalShipping += $shippingCost;
                $productIds[] = $product['product_id'];
                $updatedProducts[] = $product;
            }

            $storeData['products'] = $updatedProducts;
            $processedData[$storeId] = $storeData;
            $storeIds[] = $storeId;
        }

        $discountedBase = max(0, $subTotal - $totalDiscount);
        $totalDue = $discountedBase + $customizationTotal;
        $grandTotal = $totalDue + $totalShipping;

        return [
            'store_ids' => array_values(array_unique($storeIds)),
            'product_ids' => array_values(array_unique($productIds)),
            'subtotal' => round($subTotal, 2),
            'shipping' => round($totalShipping, 2),
            'discount' => round($totalDiscount, 2),
            'customization' => round($customizationTotal, 2),
            'grand_total' => round($grandTotal, 2),
            'store_data' => $processedData,
        ];
    }

    /**
     * Build PayPal order payload
     * Mirrors the structure used in makeCheckoutDataV3 for Stripe
     */
    protected function buildPayPalOrderPayload(array $calculatedData, ?array $shippingData, array $userInfo = []): array
    {
        $currency = config('paypal.currency', 'USD');
        $items = [];

        foreach ($calculatedData['store_data'] as $storeId => $storeData) {
            foreach ($storeData['products'] as $product) {
                $qty = $product['quantity'];
                $unitPrice = 0;

                // Calculate unit price based on wholesale or retail
                if (isset($product['whole_sale']) && !$product['whole_sale']) {
                    // Retail product - use original price (discount is shown separately in breakdown)
                    $unitPrice = $product['price'];
                } else {
                    // Wholesale product - tier price is total, calculate unit price
                    if ($product['product_type'] === 'variant') {
                        $tierDetails = CartHelpers::calculateTierDetailsV3($product['product_id'], $product['variation_id'], $qty);
                    } else {
                        $tierDetails = CartHelpers::calculateTierDetailsV3($product['product_id'], null, $qty);
                    }
                    $totalPrice = $tierDetails['total_price'] ?? 0;
                    $unitPrice = $qty > 0 ? $totalPrice / $qty : 0;
                }

                $items[] = [
                    'name' => substr($product['name'] ?? "Product #{$product['product_id']}", 0, 127),
                    'quantity' => (string) $qty,
                    'unit_amount' => [
                        'currency_code' => $currency,
                        'value' => number_format($unitPrice, 2, '.', ''),
                    ],
                    'sku' => $product['product_type'] === 'variant'
                        ? "VAR-{$product['variation_id']}"
                        : "PROD-{$product['product_id']}",
                ];
            }
        }

        // Build amount breakdown
        $breakdown = [
            'item_total' => [
                'currency_code' => $currency,
                'value' => number_format($calculatedData['subtotal'], 2, '.', ''),
            ],
            'shipping' => [
                'currency_code' => $currency,
                'value' => number_format($calculatedData['shipping'], 2, '.', ''),
            ],
            'discount' => [
                'currency_code' => $currency,
                'value' => number_format($calculatedData['discount'], 2, '.', ''),
            ],
        ];

        // Add handling (customization) fee if present
        $customizationTotal = $calculatedData['customization'] ?? 0;
        if ($customizationTotal > 0) {
            $breakdown['handling'] = [
                'currency_code' => $currency,
                'value' => number_format($customizationTotal, 2, '.', ''),
            ];
        }

        $purchaseUnit = [
            'reference_id' => 'default',
            'amount' => [
                'currency_code' => $currency,
                'value' => number_format($calculatedData['grand_total'], 2, '.', ''),
                'breakdown' => $breakdown,
            ],
            'items' => $items,
        ];

        // Add custom_id with user info for webhook identification (max 127 chars)
        if (!empty($userInfo)) {
            $purchaseUnit['custom_id'] = json_encode($userInfo);
        }

        // Add shipping address if provided
        if ($shippingData) {
            $postalCode = $shippingData['postal_code'] ?? $shippingData['zip_code'] ?? $shippingData['post_code'] ?? '';
            $fullName = $shippingData['full_name'] ?? trim(($shippingData['first_name'] ?? '') . ' ' . ($shippingData['last_name'] ?? ''));

            $purchaseUnit['shipping'] = [
                'name' => [
                    'full_name' => $fullName,
                ],
                'address' => [
                    'address_line_1' => $shippingData['address_1'] ?? $shippingData['address'] ?? '',
                    'address_line_2' => $shippingData['address_2'] ?? '',
                    'admin_area_2' => $shippingData['city'] ?? '',
                    'admin_area_1' => $shippingData['state'] ?? '',
                    'postal_code' => $postalCode,
                    'country_code' => $shippingData['country_code'] ?? $shippingData['country'] ?? 'US',
                ],
            ];
        }

        return [
            'intent' => 'CAPTURE',
            'purchase_units' => [$purchaseUnit],
            'payment_source' => [
                'paypal' => [
                    'experience_context' => [
                        'payment_method_preference' => 'IMMEDIATE_PAYMENT_REQUIRED',
                        'brand_name' => config('app.name', 'Mallshark'),
                        'locale' => 'en-US',
                        'landing_page' => 'LOGIN',
                        'shipping_preference' => $shippingData ? 'SET_PROVIDED_ADDRESS' : 'GET_FROM_FILE',
                        'user_action' => 'PAY_NOW',
                        'return_url' => config('paypal.return_url'),
                        'cancel_url' => config('paypal.cancel_url'),
                    ],
                ],
            ],
        ];
    }

    /**
     * Update PayPal orders to paid status
     */
    protected function updatePayPalOrdersToPaid(string $paypalOrderId, string $captureId, ?string $payerId, ?array $userInfo = null): void
    {
        // Get buyer_id before updating to clear cart
        $orders = StoreOrder::where('session_id', $paypalOrderId)
            ->where('payment_method', 'paypal')
            ->get();

        $buyerId = $orders[0]->buyer_id ?? null;
        $isGuest = $userInfo['is_guest'] ?? false;
        $checkoutUserId = $userInfo['checkout_user_id'] ?? null;
        // Update order status
        StoreOrder::where('session_id', $paypalOrderId)
            ->where('payment_method', 'paypal')
            ->update([
                'charge_status' => 'paid',
                'payment_status' => 'succeeded',
                'transaction_id' => $captureId,
                'payerID' => $payerId,
            ]);

        // Process order completion (emails, notifications, stock)
        $this->processPayPalOrderComplete($orders->all(), $captureId);

        // Clear the cart after successful payment
        if ($isGuest && $checkoutUserId) {
            // Guest user - clear by checkout_user_id (random_key or crypto_random)
            ProductCart::where('random_key', $checkoutUserId)
                ->orWhere('crypto_random', $checkoutUserId)
                ->delete();
            Log::info('Guest cart cleared after PayPal payment', ['checkout_user_id' => $checkoutUserId, 'is_guest' => true]);
        }elseif ($buyerId) {
            // Logged-in user - clear by user_id
            ProductCart::where('user_id', $buyerId)->delete();
            Log::info('Cart cleared after PayPal payment', ['buyer_id' => $buyerId, 'is_guest' => false]);
        } 

        Log::info('PayPal Orders Updated to Paid', [
            'paypal_order_id' => $paypalOrderId,
            'capture_id' => $captureId,
            'is_guest' => $isGuest,
        ]);
    }
    /**
     * Process PayPal order completion (emails, notifications, stock updates)
     * Mirrors updateStoreOrderViaCrypto for PayPal orders
     */
    protected function processPayPalOrderComplete($orders, string $captureId): void
    {
        try {
            $buyerId = $orders[0]->buyer_id ?? null;
            $user = CoreHelpers::getUserById($buyerId);
            $orderIds = [];
            $shippingAddress = null;
            $state = '';
            $country = '';
                    
            foreach ($orders as $order) {
                $orderId = $order->order_id;
                $orderIds[] = $orderId;
                $storeId = $order->store_id;

                // Update coupon usage
                $couponUsage = DiscountUsage::where('order_id', $orderId)->where('user_id', $buyerId)->first();
                if ($couponUsage) {
                    $couponUsage->usage_count = 1;
                    $couponUsage->save();
                    $discount = Discount::find($couponUsage->discount_id);
                    if ($discount) {
                        $discount->usage_count += 1;
                        $discount->save();
                    }
                }

                // Update product stock
                foreach ($order->orderItems as $item) {
                    $productId = $item['item_id'];
                    $variationId = $item['variation_id'] ?? null;
                    $quantity = $item['quantity'];
                    $product = $this->getProductById($productId);

                    // Assign best seller label
                    $unitSold = CoreHelpers::getProductOrderCount($productId);
                    if ($unitSold >= ProductConstants::BEST_SELLER_COUNT) {
                        CoreHelpers::assignLabelToProduct($productId, ProductConstants::BEST_SELLER_LABEL);
                    }

                    // Decrease stock
                    if ($product->unique_product == true) {
                        CoreHelpers::simpleUpdateCurrentStock($productId, $quantity);
                    } else {
                        if ($variationId) {
                            CoreHelpers::variableUpdateCurrentStock($variationId, $productId, $quantity);
                            if ($product->is_shopify_import) {
                                $this->shopifyController->orderPlacedInventorySyncToShopify($productId, $quantity, $variationId);
                            }
                        } else {
                            CoreHelpers::simpleUpdateCurrentStock($productId, $quantity);
                            if ($product->flashsale_status == 'choosen' && $product->current_stock <= 0) {
                                $product->update(['flashsale_status' => 'standard']);
                            }
                            if ($product->is_shopify_import) {
                                $this->shopifyController->orderPlacedInventorySyncToShopify($productId, $quantity);
                            }
                        }
                    }
                }

                // Get store and shipping info
                $response = CoreHelpers::getStoreInfoById((int)$storeId);
                $store = $response[0];
                $sellerId = $store->user_id ?? '';
                $storeName = $store->store_name ?? '';
                $shippingAddress = CoreHelpers::getShippingAddressById($order->shipping_address);
                $stateObj = CoreHelpers::getStatebyId($shippingAddress->state);
                $countryObj = CoreHelpers::getCountryById($shippingAddress->country);
                $state = $stateObj ? $stateObj['state_name'] : '';
                $country = $countryObj ? $countryObj['name'] : 'USA';

                // Create ShipStation order
                if (in_array($storeId, CoreConstants::SHIPSTATION_STORE_IDS)) {
                    UspsValidateAddressJob::dispatch($orderId);
                }

                // Send notifications
                $subjectBuyer = Constants::ORDER_CREATE_SUBJECT_BUYER . $orderId;
                addNotifications($buyerId, $sellerId, $subjectBuyer, '', '/new-buyer-orders-detail/' . $orderId, 'buyer_order_details', $orderId, 'buyer');
                // $this->pushNotification($buyerId, $subjectBuyer, 'buyer');

                $subjectSeller = Constants::ORDER_CREATE_SUBJECT_SELLER . $orderId;
                addNotifications($sellerId, $buyerId, $subjectSeller, '', '/order-detail/' . $orderId, 'brand_order_details', $orderId, 'seller');
                // $this->pushNotification($sellerId, $subjectSeller, 'seller');
                    
                // Send seller email
                $sellerUser = CoreHelpers::getUserById((int)$sellerId);
                $html = view('mail.storecreateOrder', [
                    'email' => $user->email,
                    'apartmentNo' => $shippingAddress->apartment_no,
                    'name' => $shippingAddress->full_name,
                    'city' => $shippingAddress->city,
                    'street' => $shippingAddress->street,
                    'postalcode' => $shippingAddress->post_code,
                    'state' => $state,
                    'country' => $country,
                    'storeName' => $storeName,
                    'order_id' => $orderId,
                    'productDetails' => $order,
                    'coupon_code' => ''
                ]);
                SendOrderEmailsJob::dispatch([
                    'from' => 'MallShark  ' . env('SUPPORT_EMAIL'),
                    'to' => $sellerUser->email,
                    'subject' => 'You\'ve Received an Order!',
                    'html' => "$html",
                ]);
                    
                $this->sendLowStockEmail($orderId);
            }

            // Send buyer email
            if ($user) {
                $html = view('mail.new-order-client-side', [
                    'customerDetail' => $user,
                    'productDetails' => $orders,
                    'email' => $user->email,
                    'name' => $shippingAddress->full_name ?? '',
                    'city' => $shippingAddress->city ?? '',
                    'apartmentNo' => $shippingAddress->apartment_no ?? '',
                    'street' => $shippingAddress->street ?? '',
                    'postalcode' => $shippingAddress->post_code ?? '',
                    'state' => $state,
                    'country' => $country,
                    'orderIds' => $orderIds,
                    'guestUser' => $user->guest_user,
                    'activate_url' => env('FRONT_END_URL') . '/verification/password-change?token=' . $user->login_key
                ]);
                $params = [
                    'from' => 'MallShark  ' . env('SUPPORT_EMAIL'),
                    'to' => $user->email,
                    'subject' => 'Order Placed! Order #' . implode(', ', $orderIds),
                    'html' => "$html",
                ];
                if (app()->environment('production')) {
                    $params['bcc'] = explode(',', env('CREATE_ORDER_BCC'));
                }
                SendOrderEmailsJob::dispatch($params);
            }
        } catch (\Exception $e) {
            Log::error('PayPal Order Processing Error', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Verify PayPal webhook signature
     */
    protected function verifyPayPalWebhookSignature(array $payload, array $headers): bool
    {
        $webhookId = config('paypal.webhook_id');
        $mode = config('paypal.mode', 'sandbox');

        if (!$webhookId) {
            Log::warning('PayPal Webhook ID not configured, skipping verification');
            return true;
        }

        try {
            // Flatten headers array (Laravel returns arrays for each header)
            $flatHeaders = [];
            foreach ($headers as $key => $value) {
                $flatHeaders[strtolower($key)] = is_array($value) ? $value[0] : $value;
            }

            $verifyPayload = [
                'auth_algo' => $flatHeaders['paypal-auth-algo'] ?? '',
                'cert_url' => $flatHeaders['paypal-cert-url'] ?? '',
                'transmission_id' => $flatHeaders['paypal-transmission-id'] ?? '',
                'transmission_sig' => $flatHeaders['paypal-transmission-sig'] ?? '',
                'transmission_time' => $flatHeaders['paypal-transmission-time'] ?? '',
                'webhook_id' => $webhookId,
                'webhook_event' => $payload,
            ];

            Log::info('PayPal Webhook Verification Request', [
                'auth_algo' => $verifyPayload['auth_algo'],
                'transmission_id' => $verifyPayload['transmission_id'],
                'webhook_id' => $webhookId,
            ]);

            $response = $this->paypalApiRequest('post', '/v1/notifications/verify-webhook-signature', $verifyPayload);
            $status = $response['verification_status'] ?? '';

            Log::info('PayPal Webhook Verification Response', ['status' => $status]);

            if ($status === 'SUCCESS') {
                return true;
            }

            // In sandbox mode, allow processing even if verification fails (for testing)
            if ($mode === 'sandbox') {
                Log::warning('PayPal Webhook Verification failed in sandbox mode, proceeding anyway', [
                    'status' => $status,
                ]);
                return true;
            }

            return false;

        } catch (Exception $e) {
            Log::error('PayPal Webhook Verification Error', ['error' => $e->getMessage()]);

            // In sandbox mode, allow processing even if verification throws error
            if ($mode === 'sandbox') {
                Log::warning('PayPal Webhook Verification error in sandbox mode, proceeding anyway');
                return true;
            }

            return false;
        }
    }

    /**
     * Handle PayPal PAYMENT.CAPTURE.COMPLETED webhook
     */
    protected function handlePayPalCaptureCompleted(array $resource): array
    {
        $captureId = $resource['id'] ?? null;
        $paypalOrderId = $resource['supplementary_data']['related_ids']['order_id'] ?? null;

        // Extract custom_id which contains user info (user_id, checkout_user_id, is_guest)
        $customId = $resource['custom_id'] ?? null;
        $userInfo = $customId ? json_decode($customId, true) : null;

        Log::info('PayPal Capture - User Info', [
            'capture_id' => $captureId,
            'custom_id' => $customId,
            'user_info' => $userInfo,
            'is_guest' => $userInfo['is_guest'] ?? null,
        ]);

        if (!$paypalOrderId) {
            $order = StoreOrder::where('transaction_id', $captureId)->first();
            if (!$order) {
                Log::warning('PayPal Capture Completed - Order not found', ['capture_id' => $captureId]);
                return ['success' => false, 'message' => 'Order not found'];
            }
            $paypalOrderId = $order->session_id;
        }

        $this->updatePayPalOrdersToPaid($paypalOrderId, $captureId, null, $userInfo);

        return [
            'success' => true,
            'message' => 'Payment capture processed',
            'capture_id' => $captureId,
            'is_guest' => $userInfo['is_guest'] ?? null,
        ];
    }

    /**
     * Handle PayPal failed capture webhooks
     */
    protected function handlePayPalCaptureFailed(array $resource, string $eventType): array
    {
        $captureId = $resource['id'] ?? null;
        $status = $eventType === 'PAYMENT.CAPTURE.REFUNDED' ? 'refunded' : 'failed';

        StoreOrder::where('transaction_id', $captureId)
            ->update([
                'charge_status' => $status,
                'payment_status' => $status,
            ]);

        Log::info("PayPal Capture {$status}", ['capture_id' => $captureId]);

        return [
            'success' => true,
            'message' => "Capture {$status} processed",
            'capture_id' => $captureId,
        ];
    }

    /**
     * Handle PayPal CHECKOUT.ORDER.APPROVED webhook
     */
    protected function handlePayPalOrderApproved(array $resource): array
    {
        $paypalOrderId = $resource['id'] ?? null;

        Log::info('PayPal Order Approved (Webhook)', ['paypal_order_id' => $paypalOrderId]);

        return [
            'success' => true,
            'message' => 'Order approval acknowledged',
            'paypal_order_id' => $paypalOrderId,
        ];
    }

    /**
     * Create pending store orders for PayPal
     * Implementation mirrors processOrderV3 in CoreRepository for Stripe
     */
    protected function createPayPalPendingOrders(
        string $paypalOrderId,
        array $calculatedData,
        array $productData,
        array $request,
        ?int $userId,
        string $checkoutUserId
    ): array {
        $orderIds = [];
        $shippingData = $request['shipping_data'] ?? null;
        $shippingAddressId = $shippingData['shipping_address_id'] ?? null;

        // Create shipping address if data provided
        if ($shippingData && $userId) {
            $country = CoreHelpers::getCountryByCode($shippingData['country'] ?? $shippingData['country_code']);
            $state = CoreHelpers::getStatebyCode($shippingData['state']);

            if ($country && $state) {
                $shippingAddress = $this->coreRepository->createShippingAddressV3(
                    $shippingData['full_name'] ?? ($shippingData['first_name'] . ' ' . $shippingData['last_name']),
                    $country['id'],
                    $state['state_id'],
                    $shippingData['address_1'],
                    $shippingData['zip_code'] ?? $shippingData['postal_code'],
                    $shippingData['city'],
                    $userId,
                    $shippingData['address_2'] ?? '',
                    $shippingData['phone_no'] ?? $shippingData['phone'] ?? ''
                );
                $shippingAddressId = $shippingAddress->sh_id;
            }
        }

        foreach ($calculatedData['store_data'] as $storeId => $storeData) {
            $orderAmount = 0;
            $orderShippingAmount = 0;
            $totalOrderItems = 0;

            // Calculate order totals (matching processOrderV3 logic)
            foreach ($storeData['products'] as $product) {
                $totalOrderItems++;
                $quantity = is_numeric($product['quantity']) ? (int)$product['quantity'] : 1;
                $productOrderPrice = 0;

                if (isset($product['whole_sale']) && !$product['whole_sale']) {
                    // Regular retail product
                    $originalPrice = $product['price'];
                    $salePrice = $product['sale_price'] ?? 0;
                    $productOrderPrice = $quantity * ($salePrice > 0 ? $salePrice : $originalPrice);
                } else {
                    // Wholesale product - tier price calculation
                    if ($product['product_type'] === 'variant') {
                        $tierDetails = CartHelpers::calculateTierDetailsV3($product['product_id'], $product['variation_id'], $quantity);
                        if ($tierDetails['total_price'] > 0) {
                            $productOrderPrice = $tierDetails['total_price'];
                        }
                    } else {
                        $tierDetails = CartHelpers::calculateTierDetailsV3($product['product_id'], null, $quantity);
                        if ($tierDetails['total_price'] > 0) {
                            $productOrderPrice = $tierDetails['total_price'];
                        }
                    }
                }

                $shippingCharges = $product['shipping_detail']['shipping_amount'] ?? 0;

                // Include customization price for custom products
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

            // Calculate PayPal fees and platform fees
            $paypalFee = ($orderAmount * config('paypal.transaction_fee.percentage', 0.0349))
                + config('paypal.transaction_fee.fixed', 0.49);
            $platformFeePercent = optional(StoreDeduction::where('store_id', $storeId)->first())->mall_shark_amount ?? env('MALLSHARK_DEDUCTION');
            $platformFee = ($platformFeePercent / 100) * $orderAmount;
            $payableAmount = $orderAmount - ($paypalFee + $platformFee);

            // Create store order
            $storeOrder = new StoreOrder([
                'buyer_id' => $userId,
                'payment_method' => 'paypal',
                'payment_status' => 'pending',
                'charge_status' => 'unpaid',
                'platform_fee' => $platformFee,
                'payable_amount' => $payableAmount,
                'order_status' => 'pending',
                'total_amount' => $orderAmount,
                'total_items' => $totalOrderItems,
                'shipping_address' => $shippingAddressId,
                'shipping_method' => '',
                'transaction_id' => '',
                'session_id' => $paypalOrderId,
                'balance_transaction' => '',
                'payerID' => '',
                'shipping_amount' => $orderShippingAmount,
                'store_id' => $storeId
            ]);
            $storeOrder->save();

            $orderId = $storeOrder->order_id;
            $orderIds[] = $orderId;
            $itemsArray = [];
            $itemShippingArray = [];

            // Create order items (matching processOrderV3 logic)
            foreach ($storeData['products'] as $product) {
                $salePrice = 0;
                $orderItemPrice = 0;
                $originalPrice = 0;
                $quantity = is_numeric($product['quantity']) ? (int)$product['quantity'] : 1;

                if (isset($product['whole_sale']) && !$product['whole_sale']) {
                    // Retail product
                    $originalPrice = $product['price'];
                    $salePrice = $product['sale_price'] ?? 0;
                    $orderItemPrice = $quantity * ($salePrice > 0 ? $salePrice : $originalPrice);
                } else {
                    // Wholesale product
                    if ($product['product_type'] === 'variant') {
                        $tierDetails = CartHelpers::calculateTierDetailsV3($product['product_id'], $product['variation_id'], $quantity);
                        if ($tierDetails['total_price'] > 0) {
                            $orderItemPrice = $tierDetails['total_price'];
                            $originalPrice = $tierDetails['unit_price'];
                        }
                    } else {
                        $tierDetails = CartHelpers::calculateTierDetailsV3($product['product_id'], null, $quantity);
                        if ($tierDetails['total_price'] > 0) {
                            $orderItemPrice = $tierDetails['total_price'];
                            $originalPrice = $tierDetails['unit_price'];
                        }
                    }
                    $product['discount_id'] = null;
                }

                // Include customization price for custom products
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

                // Handle custom product image
                if (!empty($product['is_custom']) && $product['is_custom'] == 1 && !empty($product['customizations'])) {
                    $customImage = $product['customizations']['file_name'] ?? '';
                    $customImageUrl = getAttachmentUrlByName($customImage);
                    $newcustomImagePath = $customImageUrl ? copyImageFromUrlToNewFolder($customImageUrl) : '';
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
                    'product_name' => $product['title'] ?? $product['name'] ?? 'N/A',
                    'variation_info' => $variationInfo,
                    'item_image' => $newImagePath,
                    'created_at' => now(),
                    'customizations' => (!empty($product['is_custom']) && $product['is_custom'] == 1 && !empty($product['customizations']))
                        ? json_encode($product['customizations'])
                        : null,
                    'weight' => $itemDimensions['weight']['value'] ?? 0,
                    'weight_type' => $itemDimensions['weight']['units'] ?? null,
                    'length' => $itemDimensions['dimensions']['length'] ?? 0,
                    'width' => $itemDimensions['dimensions']['width'] ?? 0,
                    'height' => $itemDimensions['dimensions']['height'] ?? 0,
                    'dimension_type' => $itemDimensions['dimensions']['units'] ?? null,
                ];

                // Store shipping details for retail products
                if (isset($product['whole_sale']) && !$product['whole_sale']) {
                    $itemShippingArray[] = [
                        'shipping_profile_id' => $product['shipping_detail']['shipping_profile_id'] ?? null,
                        'order_id' => $orderId,
                        'product_id' => $product['product_id'],
                        'shipping_amount' => $shippingCharges,
                        'base_price' => $product['shipping_detail']['base_price'] ?? 0,
                        'handlilng_fee' => $product['shipping_detail']['handling_fee'] ?? 0,
                        'delivery_time' => $product['shipping_detail']['delivery_time'] ?? null,
                        'shipping_method' => $product['shipping_detail']['shipping_method'] ?? null,
                        'service_id' => $product['shipping_detail']['service_id'] ?? null,
                        'service_slug' => $product['shipping_detail']['service_slug'] ?? null,
                        'service_name' => $product['shipping_detail']['service_name'] ?? null,
                    ];
                }
            }

            // Insert order items
            if (!empty($itemsArray)) {
                OrderItems::insert($itemsArray);
            }

            // Insert shipping details
            if (!empty($itemShippingArray)) {
                OrderShipping::insert($itemShippingArray);
            }

            $logData = [
                'event_type' => 'paypal_store_order_created',
                'user_id' => $checkoutUserId,
                'data' => json_encode([
                    'order_id' => $orderId,
                    'store_id' => $storeId,
                    'paypal_order_id' => $paypalOrderId,
                    'total' => $orderAmount,
                ]),
            ];
            CoreHelpers::checkoutLogV3($logData);
        }

        return $orderIds;
    }
    
}
