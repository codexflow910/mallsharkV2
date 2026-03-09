<?php

namespace App\Http\Controllers\API;

use App\Constants;
use Stripe\Token;
use Stripe\Charge;
use Stripe\Refund;
use Stripe\Stripe;
use Stripe\EphemeralKey;
use Stripe\PaymentIntent;
use Stripe\BalanceTransaction;
use Stripe\Customer;
use App\Models\{User,DiscountUsage,StoreOrder,StoreEscrow,Userrole};
use App\Services\FCMService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Stripe\OAuth;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Stripe\Exception\CardException;
use App\Http\Controllers\Controller;
use App\Models\DeviceToken;
use App\Repositories\Buyer\Buyeraccess;
use App\Repositories\stripe\StripeAccess;
use Illuminate\Support\Facades\Validator;
use App\Repositories\Orders\StoreOrdersAccess;
use App\Repositories\Credit\Creditaccess;
use App\Repositories\User\Useraccess;
use App\Services\SellerFcmService;
use Exception;
use Illuminate\Support\Facades\URL;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Stripe\Webhook;
use Illuminate\Support\Str;
use App\Http\Controllers\MailgunController;
use App\Models\{OrderItems,ProductCart};
use App\Models\ShippingAddress;
use App\Http\Controllers\ElasticSearchController;

class StripeController extends Controller
{

    private $accessObj;
    private $apiKey;
    private $_request;
    private $stripeObj;
    private $_userobj;
    private $_buyerobj;
    private $mailgun;

    public function __construct(Request $_request)
    {
        $this->_userobj = new Useraccess();
        $this->_buyerobj = new Buyeraccess();
        $this->apiKey = env('STRIPE_API_KEY');
        // $this->stripeObj = new StripeAccess();
        $this->_request = $_request;
        $this->stripeObj = new StripeAccess();
        $this->mailgun = new MailgunController();
    }

    public function refundBack($order_id = '', $amount = '')
    {
        // $validateUser = Validator::make(
        // $this->_request->all(),
        //     [
        //         'order_id' => 'required|exists:store_orders,order_id',
        //     ]
        // );
        // if ($validateUser->fails()) {
        //     return response()->json([
        //         'status' => false,
        //         'message' => 'validation error',
        //         'errors' => $validateUser->errors()
        //     ], 401);
        // }

        $input = $this->_request->input();
        // dd($input);
        try {
            $orders = DB::table('store_orders')
                ->select('transaction_id','session_id', 'total_amount')
                ->where('order_id', '=', $input['order_id'])
                ->get()
                ->toArray();
            $session_id = isset($orders[0]->session_id) ? $orders[0]->session_id : '';
            $transaction_id = isset($orders[0]->transaction_id) ? $orders[0]->transaction_id : '';
            $amount = isset($orders[0]->total_amount) ? $orders[0]->total_amount : '';
            $price =   round($amount * 100);
            //    echo 'Data: <pre>' .print_r($orders,true). '</pre>'; die();
            if ($session_id != '') {
                Stripe::setApiKey($this->apiKey);
                $session = \Stripe\Checkout\Session::retrieve($session_id);
                $paymentIntentId = $session->payment_intent;
                $re = Refund::create([
                    'payment_intent' => $paymentIntentId, // ID from previous step
                ]);
                if (isset($re->status) && $re->status == 'succeeded') {
                    
                    $query_update = DB::table('store_orders')
                            ->where('order_id', $order_id)
                            ->update(['order_status' => 'refunded']);
                    return true;
                }
            } else {
                return response()->json([
                    'status' => false,
                    'message' => "Tranection id not exist",
                ], 401);
            }
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    public function confirmPaymentIntent(Request $request)
    {
        Stripe::setApiKey($this->apiKey);

        $validateUser = Validator::make(
            $this->_request->all(),
            [
                'payment_intent_id' => 'required',
            ]
        );
        if ($validateUser->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation error',
                'errors' => $validateUser->errors()
            ], 401);
        }
        $paymentIntentId = $request->input('payment_intent_id');

        try {
            $paymentIntent = \Stripe\PaymentIntent::retrieve($paymentIntentId);
            $paymentIntent->confirm();

            return response()->json(['message' => 'PaymentIntent successfully confirmed']);
        } catch (CardException $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    public function paymentIntentGooglePay()
    {
        // dd($this->_request->all());
        $validateUser = Validator::make(
            $this->_request->all(),
            [
                'first_name' => 'required',
                'last_name' => 'required',
                'country' => 'required',
                'user_email' => 'required',
                'states' => 'required',
                'address' => 'required',
                'city' => 'required',
                'post_code' => 'required',
                'total_amount' => 'required',
                'orderdata' => 'required|array',
                'amount' => 'required',
                'isChecked' => 'required|boolean',
                'fcm_token' => 'required',
                'app_flag' => 'required',
            ]
        );
        if ($validateUser->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation error',
                'errors' => $validateUser->errors()
            ], 401);
        }
        try {
            $guest_user = false;

            $userInfo = $this->_request->input('userInfo');
            $userEmail = $this->_request->input('user_email');
            $user = User::where('email', $userEmail)->first();
            if ($user) {
                if ($this->_request->input('isChecked', false)) {
                    $shippingData = [
                        'city' => $this->_request->input('city'),
                        'user_country' => $this->_request->input('country'),
                        'user_state' => $this->_request->input('states'),
                        'zip_code' => $this->_request->input('post_code'),
                        'street' => $this->_request->input('address'),
                    ];
                    $shippingData['user_id'] = $user->user_id;
                    $this->_buyerobj->updateShippingAddressApi($shippingData);
                }
            } else {

                $postedData = [
                    'txtname' => $this->_request->input('first_name') . ' ' . $this->_request->input('last_name'),
                    'fcm_token' => $this->_request->input('fcm_token'),
                    'app_flag' => $this->_request->input('app_flag'),
                    'return_source' => 'true',
                    'guest_user' => true,
                    'address' => $this->_request->input('address'),
                    'city' => $this->_request->input('city'),
                    'contactNumber' => $this->_request->input('city'),
                    'email' => $userEmail
                    // verify if more data needed to create user

                ];

                $registeredUser = $this->_userobj->userRegister($postedData, true);
                if ($registeredUser) {
                    $guest_user = true;
                    $shippingData = [
                        'city' => $this->_request->input('city'),
                        'user_country' => $this->_request->input('country'),
                        'user_state' => $this->_request->input('states'),
                        'zip_code' => $this->_request->input('post_code'),
                        'street' => $this->_request->input('address'),
                    ];
                    $userId = isset($registeredUser['userobj']->user_id) && !empty($registeredUser['userobj']->user_id) ? $registeredUser['userobj']->user_id : null;
                    $shippingData['user_id'] = $userId;
                    if ($this->_request->input('isChecked', false)) {
                        $this->_buyerobj->updateShippingAddressApi($shippingData);
                    }
                } else {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Order data is not in the expected format'
                    ]);
                }
                //after this add shipping shipping address if isChecked param is true
                // check postman
            }



            $orderIds = [];
            $token = $this->_request->input('stripeToken');
            $user_email = $this->_request->input('user_email');
            $first_name = $this->_request->input('first_name');
            $last_name = $this->_request->input('last_name');
            $country = $this->_request->input('country');
            $states = $this->_request->input('states');
            $address = $this->_request->input('address');
            $city = $this->_request->input('city');
            $post_code = $this->_request->input('post_code');
            $total_amount = $this->_request->input('total_amount');
            $OrderData = $this->_request->input('orderdata');
            $thankyouOutput = [];
            // calucalte total chargables
            $charges = array();
            $totalOrdersAmount = 0;
            $totalOrder = 0;
            $totalShipping = 0;
            if (is_array($OrderData)) {
                foreach ($OrderData as $storeId => $store) {
                    // dd($store);
                    $orderAmount = 0;
                    foreach ($store as $product) {

                        $quantity = $product['product_quantity'];
                        $shippingPrice = $product['shipping_price'];
                        if ($product['on_sale'] == 0) {
                            $productPrice = $product['price'] * $quantity;
                        } elseif ($product['on_sale'] == 1) {
                            $productPrice = $product['sale_price'] * $quantity;
                        }
                        $orderAmount += $productPrice + $shippingPrice;

                        // for gtm start
                        $totalOrder += $productPrice + $shippingPrice;
                        $totalShipping +=  $shippingPrice;
                        $storeData =  getStoreInfoByIdCartApi($product['store_id']);
                        $category_id = getCategoriesByProductID($product['product_id']);
                        if (isset($category_id[0])) {
                            $productCategory = getCategoryByCateId($category_id[0]);
                        } else {
                            $productCategory = null;
                        }

                        $category = isset($productCategory) ? $productCategory['categoryTitle'] : '';
                        $productInfo = array(
                            "item_id" => $product['product_id'],
                            "item_name" => $product['title'],
                            "item_category" => $category,
                            "item_brand" => $storeData['store_name'],
                            "item_list_id" => "purchase",
                            "item_list_name" => "Place Order",
                            "price" => $product['price'],
                            "quantity" => $product['product_quantity'],
                        );
                        $products[] = $productInfo;
                        // for gtm end

                    }
                    $stripe_fee = ($orderAmount * (2.9 / 100) + 0.33);
                    // we need platform fee % on   ( orignal Order amount - stripe fee)
                    // $temp_amount = $orderAmount - $stripe_fee;

                    $storeCampaign = getCampaignsByStoreId($storeId);
                    $totalActiveProducts = getTotalActiveProductsByStore($storeId);
                    if (isset($storeCampaign) && $totalActiveProducts >= 10) {
                        $benifitsdata = json_decode($storeCampaign->benefits, true);
                        $platformfeeCheck = isset($benifitsdata['platform_fee']) ? $benifitsdata['platform_fee'] : false;

                        if (!$platformfeeCheck) {
                            $platform_fee = $benifitsdata['percentage'];
                        }
                    } else {

                        $platform_fee = $orderAmount * 0.045;
                    }

                    $total_fee = $stripe_fee +  $platform_fee;
                    $payable_amount = $orderAmount - $total_fee;

                    $paymentInfo = array(
                        "platform_fee" => $platform_fee,
                        "payable_amount" => $payable_amount,
                    );
                    $charges[$storeId] = $paymentInfo;
                    $totalOrdersAmount += $orderAmount;
                }

                // Add Order 
                $output["useremail"] = $user_email;
                $output["full_name"] = $first_name . ' ' . $last_name;
                $output["country"] = $country;
                $output["states"] = $states;
                $output["city"] = $city;
                $output["post_code"] = $post_code;
                $output["address"] = $address;
                $output["total_amount"] = $total_amount;
                $output["charges"] = $charges;
                $output["payment_status"] = 'pending';
                $output["charge_status"] = 'unpaid';
                $output["orderData"] = $OrderData = $this->_request->input('orderdata');

                // thankyou data
                $thankyouOutput["useremail"] = $user_email;
                $thankyouOutput["full_name"] = $first_name . ' ' . $last_name;
                $thankyouOutput["country"] = $country;
                $thankyouOutput["states"] = $states;
                $thankyouOutput["city"] = $city;
                $thankyouOutput["post_code"] = $post_code;
                $thankyouOutput["address"] = $address;
                $thankyouOutput["total_amount"] = $total_amount;
                $thankyouOutput["orderData"] = $OrderData = $this->_request->input('orderdata');
                $this->accessObj = new StoreOrdersAccess();
                $orderIds = $this->accessObj->create_orderApi($output);
                $thankyouOutput["orderId"] = $orderIds;
                // return response()->json([
                //     'status' => true,
                //     'message' => 'Order Stored',
                //     'data'=>$orderId
                // ]);
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Order data is not in the expected format'
                ]);
            }
            if (count($orderIds) > 0) {

                // Create Intent
                $amount = $this->_request->input('amount');
                // Set your Stripe API key
                Stripe::setApiKey(env('STRIPE_API_KEY'));

                $customer = Customer::create();
                $ephemeralKey = EphemeralKey::create(
                    ['customer' => $customer->id],
                    ['stripe_version' => env('STRIPE_API_VERSION')]
                );
                $paymentIntent = PaymentIntent::create([
                    'amount' => round($totalOrdersAmount * 100), // Amount in cents
                    'currency' => 'usd',
                    'customer' => $customer->id,
                    'metadata' => ['order_ids' => json_encode($orderIds)],
                    'payment_method' => $this->_request->paymentMethod, // The paymentMethodId sent from frontend
                    'confirmation_method' => 'automatic',
                    // 'usage' => 'off_session',
                ]);

                return response()->json([
                    'paymentIntent' => $paymentIntent->client_secret,
                    'ephemeralKey' => $ephemeralKey->secret,
                    'customer' => $customer->id,
                    'guest_user' => $guest_user,
                    'orderId' => $orderIds,
                    "thankyouData" => $thankyouOutput
                ]);
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Order data is not in the expected format'
                ]);
            }
        } catch (\Exception $e) {
            $paymentLog = new Logger('');
            $paymentLog->pushHandler(new StreamHandler(storage_path('logs/checkout_payment.log')), Logger::ERROR);
            $paymentLog->error('Store Order', ['Message' => $e->getMessage()]);
        }
        // Store Order

    }
    public function paymentIntent()
    {
        // dd($this->_request->all());
        $validateUser = Validator::make(
            $this->_request->all(),
            [
                'first_name' => 'required',
                'last_name' => 'required',
                'country' => 'required',
                'user_email' => 'required',
                'states' => 'required',
                'address' => 'required',
                'city' => 'required',
                'post_code' => 'required',
                'total_amount' => 'required',
                'orderdata' => 'required|array',
                'amount' => 'required',
                'isChecked' => 'required|boolean',
                'fcm_token' => 'required',
                'app_flag' => 'required',
            ]
        );
        if ($validateUser->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation error',
                'errors' => $validateUser->errors()
            ], 401);
        }
        try {
            $guest_user = false;

            $userInfo = $this->_request->input('userInfo');
            $userEmail = $this->_request->input('user_email');
            $user = User::where('email', $userEmail)->first();
            if ($user) {



                if ($this->_request->input('isChecked', false)) {
                    $shippingData = [
                        'city' => $this->_request->input('city'),
                        'user_country' => $this->_request->input('country'),
                        'user_state' => $this->_request->input('states'),
                        'zip_code' => $this->_request->input('post_code'),
                        'street' => $this->_request->input('address'),
                    ];
                    $shippingData['user_id'] = $user->user_id;
                    $this->_buyerobj->updateShippingAddressApi($shippingData);
                }
            } else {

                $postedData = [
                    'txtname' => $this->_request->input('first_name') . ' ' . $this->_request->input('last_name'),
                    'fcm_token' => $this->_request->input('fcm_token'),
                    'app_flag' => $this->_request->input('app_flag'),
                    'return_source' => 'true',
                    'guest_user' => true,
                    'address' => $this->_request->input('address'),
                    'city' => $this->_request->input('city'),
                    'contactNumber' => $this->_request->input('city'),
                    'email' => $userEmail
                    // verify if more data needed to create user

                ];

                $registeredUser = $this->_userobj->userRegister($postedData, true);
                if ($registeredUser) {
                    $guest_user = true;
                    $shippingData = [
                        'city' => $this->_request->input('city'),
                        'user_country' => $this->_request->input('country'),
                        'user_state' => $this->_request->input('states'),
                        'zip_code' => $this->_request->input('post_code'),
                        'street' => $this->_request->input('address'),
                    ];
                    $userId = isset($registeredUser['userobj']->user_id) && !empty($registeredUser['userobj']->user_id) ? $registeredUser['userobj']->user_id : null;
                    $shippingData['user_id'] = $userId;
                    if ($this->_request->input('isChecked', false)) {
                        $this->_buyerobj->updateShippingAddressApi($shippingData);
                    }
                } else {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Order data is not in the expected format'
                    ]);
                }
                //after this add shipping shipping address if isChecked param is true
                // check postman
            }



            $orderIds = [];
            $token = $this->_request->input('stripeToken');
            $user_email = $this->_request->input('user_email');
            $first_name = $this->_request->input('first_name');
            $last_name = $this->_request->input('last_name');
            $country = $this->_request->input('country');
            $states = $this->_request->input('states');
            $address = $this->_request->input('address');
            $city = $this->_request->input('city');
            $post_code = $this->_request->input('post_code');
            $total_amount = $this->_request->input('total_amount');
            $OrderData = $this->_request->input('orderdata');
            $thankyouOutput = [];
            // calucalte total chargables
            $charges = array();
            $totalOrdersAmount = 0;
            $totalOrder = 0;
            $totalShipping = 0;
            if (is_array($OrderData)) {
                foreach ($OrderData as $storeId => $store) {
                    // dd($store);
                    $orderAmount = 0;
                    foreach ($store as $product) {

                        $quantity = $product['product_quantity'];
                        $shippingPrice = $product['shipping_price'];
                        if ($product['on_sale'] == 0) {
                            $productPrice = $product['price'] * $quantity;
                        } elseif ($product['on_sale'] == 1) {
                            $productPrice = $product['sale_price'] * $quantity;
                        }
                        $orderAmount += $productPrice + $shippingPrice;

                        // for gtm start
                        $totalOrder += $productPrice + $shippingPrice;
                        $totalShipping +=  $shippingPrice;
                        $storeData =  getStoreInfoByIdCartApi($product['store_id']);
                        $category_id = getCategoriesByProductID($product['product_id']);
                        if (isset($category_id[0])) {
                            $productCategory = getCategoryByCateId($category_id[0]);
                        } else {
                            $productCategory = null;
                        }

                        $category = isset($productCategory) ? $productCategory['categoryTitle'] : '';
                        $productInfo = array(
                            "item_id" => $product['product_id'],
                            "item_name" => $product['title'],
                            "item_category" => $category,
                            "item_brand" => $storeData['store_name'],
                            "item_list_id" => "purchase",
                            "item_list_name" => "Place Order",
                            "price" => $product['price'],
                            "quantity" => $product['product_quantity'],
                        );
                        $products[] = $productInfo;
                        // for gtm end

                    }
                    $stripe_fee = ($orderAmount * (2.9 / 100) + 0.33);
                    // we need platform fee % on   ( orignal Order amount - stripe fee)
                    // $temp_amount = $orderAmount - $stripe_fee;

                    $storeCampaign = getCampaignsByStoreId($storeId);
                    $totalActiveProducts = getTotalActiveProductsByStore($storeId);
                    if (isset($storeCampaign) && $totalActiveProducts >= 10) {
                        $benifitsdata = json_decode($storeCampaign->benefits, true);
                        $platformfeeCheck = isset($benifitsdata['platform_fee']) ? $benifitsdata['platform_fee'] : false;

                        if (!$platformfeeCheck) {
                            $platform_fee = $benifitsdata['percentage'];
                        }
                    } else {

                        $platform_fee = $orderAmount * 0.045;
                    }

                    $total_fee = $stripe_fee +  $platform_fee;
                    $payable_amount = $orderAmount - $total_fee;

                    $paymentInfo = array(
                        "platform_fee" => $platform_fee,
                        "payable_amount" => $payable_amount,
                    );
                    $charges[$storeId] = $paymentInfo;
                    $totalOrdersAmount += $orderAmount;
                }

                // Add Order 
                $output["useremail"] = $user_email;
                $output["full_name"] = $first_name . ' ' . $last_name;
                $output["country"] = $country;
                $output["states"] = $states;
                $output["city"] = $city;
                $output["post_code"] = $post_code;
                $output["address"] = $address;
                $output["total_amount"] = $total_amount;
                $output["charges"] = $charges;
                $output["payment_status"] = 'pending';
                $output["charge_status"] = 'unpaid';
                $output["orderData"] = $OrderData = $this->_request->input('orderdata');

                // thankyou data
                $thankyouOutput["useremail"] = $user_email;
                $thankyouOutput["full_name"] = $first_name . ' ' . $last_name;
                $thankyouOutput["country"] = $country;
                $thankyouOutput["states"] = $states;
                $thankyouOutput["city"] = $city;
                $thankyouOutput["post_code"] = $post_code;
                $thankyouOutput["address"] = $address;
                $thankyouOutput["total_amount"] = $total_amount;
                $thankyouOutput["orderData"] = $OrderData = $this->_request->input('orderdata');
                $this->accessObj = new StoreOrdersAccess();
                $orderIds = $this->accessObj->create_orderApi($output);
                $thankyouOutput["orderId"] = $orderIds;
                // return response()->json([
                //     'status' => true,
                //     'message' => 'Order Stored',
                //     'data'=>$orderId
                // ]);
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Order data is not in the expected format'
                ]);
            }
            if (count($orderIds) > 0) {

                // Create Intent
                $amount = $this->_request->input('amount');
                // Set your Stripe API key
                Stripe::setApiKey(env('STRIPE_API_KEY'));

                $customer = Customer::create();
                $ephemeralKey = EphemeralKey::create(
                    ['customer' => $customer->id],
                    ['stripe_version' => env('STRIPE_API_VERSION')]
                );
                $paymentIntent = PaymentIntent::create([
                    'amount' => round($totalOrdersAmount * 100,2), // Amount in cents
                    'currency' => 'usd',
                    'customer' => $customer->id,
                    'metadata' => ['order_ids' => json_encode($orderIds)],
                    // 'usage' => 'off_session',
                ]);

                return response()->json([
                    'paymentIntent' => $paymentIntent->client_secret,
                    'ephemeralKey' => $ephemeralKey->secret,
                    'customer' => $customer->id,
                    'guest_user' => $guest_user,
                    'orderId' => $orderIds,
                    "thankyouData" => $thankyouOutput
                ]);
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Order data is not in the expected format'
                ]);
            }
        } catch (\Exception $e) {
            $paymentLog = new Logger('');
            $paymentLog->pushHandler(new StreamHandler(storage_path('logs/checkout_payment.log')), Logger::ERROR);
            $paymentLog->error('Store Order', ['Message' => $e->getMessage()]);
        }
        // Store Order

    }
    public function paymentReceived(Request $request)
    {
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        $endpointSecret = env('STRIPE_WEBHOOK_SECRET');
        try {
            $event = Webhook::constructEvent(
                $payload,
                $sigHeader,
                $endpointSecret
            );
        } catch (\UnexpectedValueException $e) {
            $paymentLog = new Logger('');
            $paymentLog->pushHandler(new StreamHandler(storage_path('logs/checkout_payment.log')), Logger::ERROR);
            $paymentLog->error('Failed', ['Message' => $e->getMessage()]);

            return response()->json(['error' => 'Invalid payload'], 400);
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            $paymentLog = new Logger('');
            $paymentLog->pushHandler(new StreamHandler(storage_path('logs/checkout_payment.log')), Logger::ERROR);
            $paymentLog->error('Failed', ['Message' => $e->getMessage()]);
            return response()->json(['error' => 'Invalid signature'], 400);
        }

        // if ($event->type === 'payment_intent.succeeded') {
        //     $paymentLog = new Logger('');
        //     $paymentLog->pushHandler(new StreamHandler(storage_path('logs/checkout_payment.log')), Logger::INFO);
        //     $intentData =  $event->data->object;
        //     $paymentIntentMetaData = $event->data->object->metadata;

        //     $paymentLog->info('payment_intent.succeeded', ['Message' =>'Success','data'=>$intentData]);
        //     $orderPaid = $this->updatePaymentStatus($paymentIntentMetaData->order_id);
        //     if($orderPaid){

        //         $paymentLog->info('Order Stored', ['Message' =>'Success','data'=>$paymentIntentMetaData->order_id]);
        //     }


        //     // $this->handlePaymentSuccess($paymentIntent);
        // }
        $intentData =  $event->data->object;
        if ($event->type === 'checkout.session.completed') {

            Stripe::setApiKey(env('STRIPE_API_KEY'));
            // $paymentIntentId = $intentData['payment_intent'];
            // $paymentIntent = PaymentIntent::retrieve($paymentIntentId);

            // // Get Balance Transaction ID
            // $balanceTransactionId = $paymentIntent->charges;
            // $balanceTransaction = BalanceTransaction::retrieve($balanceTransactionId);

            $stripe = new \Stripe\StripeClient(env('STRIPE_API_KEY'));

            // Retrieve session with expanded line items
            $session = $stripe->checkout->sessions->retrieve(
                $intentData['id'],
                ['expand' => ['line_items']]
            );
            if($session['payment_status'] == 'paid'){
                $customerEmail = $session['customer_details']['email'];
                $customerName = $session['customer_details']['name'];
                $customerId = $session['customer'];
                $user = User::where('email',$customerEmail)->first();
                $login_key = str_random(25);
                if(!$user){
                    $customer = Customer::create([
                        'email' => $customerEmail,
                        'name' => $customerName,
                    ]);
                
                    // Store the Stripe customer ID in the database
                   
                    $user = new User();
                    $user->name = $session['customer_details']['name'];
                    $user->email = $customerEmail;
                    $user->customer_id = $customer->id;
                    $user->password = md5('mallshark123456');
                    $now = Carbon::now();
                    $user->forget_key_date = $now;
                    $user->forget_key = $login_key;
                    $user->register_source = 'MallShark';
                    $user->contact_no = $session['customer_details']['phone'];
                    $user->guest_user = 1;
                    $user->save();
                    $userRole = new Userrole();
                    $userRole->user_id = $user->user_id;
                    $userRole->role_id = 4;
                    $userRole->save();
                }else{
                    $now = Carbon::now();
                    $user->forget_key_date = $now;
                    $user->forget_key = $login_key;
                    $user->save();
                }
                $country = getCountryByCode($session['customer_details']['address']['country']);
                $state = getStatebyCode($session['customer_details']['address']['state']);
                $userId = $user->user_id;
                $shippingAddress = new ShippingAddress();
                $shippingAddress->full_name = $session['collected_information']['shipping_details']['name'];
                $shippingAddress->country = $country['id'];
                $shippingAddress->state = $state['state_id'];
                $shippingAddress->street = $session['customer_details']['address']['line1'];
                $shippingAddress->post_code = $session['customer_details']['address']['postal_code'];
                $shippingAddress->city = $session['customer_details']['address']['city'];
                $shippingAddress->user_id = $userId;
                $shippingAddress->save();


                
                $sessionId = $session['id'];
                $storeOrders = StoreOrder::with('orderItems')->where('session_id',$sessionId)->get();
                $totalSubtotal = $storeOrders->sum('total_amount');
                $totalShipping = $storeOrders->sum('shipping_amount');
                $total_amount_without_ship = $totalSubtotal - $totalShipping;
                // $totalTax = $session['total_details']['amount_tax'] / 100;
                $orderIds = [];
                foreach($storeOrders as $order){
                    // $taxForOrder = round(($order->total_amount / $total_amount_without_ship) * $totalTax, 2);
                    $order_id = $order->order_id;
                    $orderIds[] = $order_id;
                    $store_id = $order->store_id;
                    $store_data = getStoreInfoById((int)$store_id);
                    $storeWallet = !empty($store_data[0]->wallet_address) ? $store_data[0]->wallet_address : '';
                    $total_amount = $order->total_amount;
                    $payableAmount = $order->payable_amount;
                    $shippingAmount = $order->shipping_amount;
                    $amountWithOutShip = $total_amount - $shippingAmount;
                    $paymentProfit = $total_amount * 0.045;
                    $order->buyer_id = $userId;
                    $order->charge_status = 'paid';
                    $order->transaction_id = $session['payment_intent'];
                    $order->store_tax = null;
                    $order->shipping_address = $shippingAddress->sh_id;
                    $order->save();
                    $discountUsage = DiscountUsage::where('order_id',$order->order_id)->first();
                    if($discountUsage){
                        $discountUsage->user_id = $userId;
                        $discountUsage->save();
                    }
                    
                    //escrow
                    $storeEscrew = new StoreEscrow();
                    $storeEscrew->order_id = $order->order_id;
                    $storeEscrew->store_id = $store_id;
                    $storeEscrew->store_wallet_address = $storeWallet;
                    $storeEscrew->order_price = $total_amount;
                    $storeEscrew->ship_price =  $shippingAmount;
                    $storeEscrew->order_price_with_out_shipping =  $amountWithOutShip;
                    $storeEscrew->payable_amount =  $payableAmount;
                    $storeEscrew->profit =  $paymentProfit;
                    $storeEscrew->status =  'pending';
                    $storeEscrew->profit_status =  'pending';
                    $storeEscrew->payment_date = date('Y-m-d');
                    $storeEscrew->save();
                    foreach($order->orderItems as $item){
                        $remainStock = "";
                        if (isset($item['variation_id']) && $item['variation_id'] != '') {
                            variableUpdateCurrentStock($item['variation_id'], $item['item_id'], $item['quantity']);
                            // change status of flash sale
                            $productFlashsaleStatus = DB::table('products')->where('product_id', $item['item_id'])->value('flashsale_status');
                            if ($productFlashsaleStatus == 'choosen') {
                                $productData = getvariableProduct($item['item_id']);
                                $remainStock = $productData['current_stock'];
                                if ($remainStock <= 0) {
                                    DB::table('products')->where('product_id', $item['item_id'])->update(['flashsale_status' => 'standard']);
                                }
                            }
                        } else {
                            simpleUpdateCurrentStock($item['item_id'], $item['quantity']);
                            // change flash sale status
                            $productquery = DB::table('products')->where('product_id', $item['item_id'])->select('flashsale_status', 'current_stock')->first();
                            $remainStock = $productquery->current_stock;
                            if ($productquery->flashsale_status == 'choosen' && $remainStock <= 0) {
                                DB::table('products')->where('product_id', $item['item_id'])->update(['flashsale_status' => 'standard']);
                            }
                        }
                    }
                    try {

                        //prepare notification for buyer

                        $storeId = $order->store_id;
                        $buyerId = $order->buyer_id;
                        $BuyerData = User::with('device_token')->findOrFail($buyerId);
                        $orderId = $order->order_id;
                        $shippingAddress = ShippingAddress::where('sh_id', $order->shipping_address)->first();
                        $store = getStoreInfoById((int) $storeId);
                        $sellerId = isset($store[0]->user_id) ? $store[0]->user_id : '';
                        $subjectBuyer = Constants::ORDER_CREATE_SUBJECT_BUYER . $orderId;
                        // add buyer notification
                        addNotifications($buyerId, $sellerId, $subjectBuyer, '', '/new-buyer-orders-detail/' . $order_id, 'buyer_order_details', $orderId, 'buyer');
                        //send  notification to buyer
                        $device_token_obj = DeviceToken::where('user_id', $buyerId)->where('app_flag', 'buyer')->first();
                        if ($device_token_obj) {
                            if (isset($device_token_obj)) {

                                FCMService::send(
                                    $device_token_obj->fcm_token,
                                    [
                                        'title' => $subjectBuyer,
                                    ]
                                );
                        }
                        }
                        // prepare notification for seller 
                        $subjectSeller = Constants::ORDER_CREATE_SUBJECT_SELLER . $orderId;
                        // add seller notification
                        addNotifications($sellerId, $buyerId, $subjectSeller, '', '/order-detail/' . $orderId, 'brand_order_details', $orderId, 'seller');
                        //send  notification to seller
                        $device_token_obj = DeviceToken::where('user_id', $sellerId)->where('app_flag', 'seller')->first();
                        if ($device_token_obj) {
                            if (isset($device_token_obj)) {
                                SellerFcmService::send(
                                        $device_token_obj->fcm_token,
                                        [
                                            'title' => $subjectSeller,
                                        ]
                                    );
                                }

                            // Send Emails here


                        }
                    } catch (\Exception $e) {
                        $paymentLog = new Logger('');
                        $paymentLog->pushHandler(new StreamHandler(storage_path('logs/notifactions_failed.log')), Logger::ERROR);
                        $paymentLog->error('Notification failed', ['error' => $e->getMessage()]);
                    }
                    $shippingAddress = ShippingAddress::where('sh_id', $order->shipping_address)->first();
                    $state_id = $shippingAddress->state;
                    $country_id = $shippingAddress->country;
                    $state = getStatebyId($state_id);
                    $country = getCountryById($country_id);
                    $state_name = $state['state_name'];
                    $country_name = $country['code'];
                    $store_name = isset($store[0]->store_name) ? $store[0]->store_name : '';
                    $sellerId = isset($store[0]->user_id) ? $store[0]->user_id : '';
                    $userDataStore = getUserByIdchat((int) $sellerId);
                    $toEmailStore = $userDataStore->email;
                    $html = view('mail.storecreateOrder', [
                        'email' => $customerEmail, 'name' =>  $shippingAddress->full_name,
                        'city' => $shippingAddress->city, 'street' => $shippingAddress->street, 'postalcode' => $shippingAddress->post_code,
                        'state' => $state_name, 'country' => $country_name, 'storeName' => $store_name, 'order_id' => $order_id, 'productDetails' => $order, 'coupon_code' => ''
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
            
            $state_id = $shippingAddress->state;
            $country_id = $shippingAddress->country;
            $state = getStatebyId($state_id);
            $state_name = $state['state_name'];
            $country = getCountryById($country_id);
            $country_name = $country['code'];
            $html = view('mail.new-order-client-side', [
                'customerDetail' => $BuyerData,
                'productDetails' => $storeOrders, 'email' => $customerEmail, 'name' => $shippingAddress->full_name,
                'city' => $shippingAddress->city, 'street' => $shippingAddress->street, 'postalcode' => $shippingAddress->post_code,
                'state' => $state_name, 'country' => $country_name,'orderIds' => $orderIds,
            ]);
            $subject = 'Order Placed! Order #'.implode(', ', $orderIds);
            $toEmail = $BuyerData->email;
            $bccEmails = ['sajidoon@mindblazetech.com','abdullah.jutt@mindblaze.biz','care@mallshark.com','fugatesklcc@yahoo.com','aatika@mallshark.com','chadfugate85@gmail.com','chad@mallshark.com'];
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
                $url = URL::to('/change-password/'.$login_key);
                $html = view('mail.activateUser_checkout', ['activate_url' => $url]);
                $subject = 'Welcome to MallShark - Get Ready for an Unforgettable Shopping Experience!';
    
                $toEmail = $user->email;
                $toCompletename = $user->name;
                $params = [
                    'from'    => 'MallShark  ' . env('SUPPORT_EMAIL'),
                    'to'      => $toEmail,
                    'subject' => $subject,
                    'html'    => "$html",
                ];
                $results = $this->mailgun->SendMailgunMail($params);
            }
            if($intentData->metadata->cart_id != ''){
                $cart = ProductCart::where('cart_id',$intentData->metadata->cart_id)->delete();
            }else{
                if($intentData->metadata->guest == 'true'){
                    $cart = ProductCart::where('random_key',$intentData->metadata->user_id)->delete();
                }else{
                    $cart = ProductCart::where('user_id',$intentData->metadata->user_id)->delete();
                }
            }
           
        }
        return response()->json(['status' => 'success']);
    }
    public function testNotifSeller()
    {
        $token = $this->_request->input('token');

        return SellerFcmService::send(
            $token,
            [
                'title' =>  Constants::ORDER_CREATE_SUBJECT_SELLER . '1798',
            ]
        );
    }
    public function testNotifBuyer()
    {
        $token = $this->_request->input('token');

        return FCMService::send(
            $token,
            [
                'title' =>  Constants::ORDER_CREATE_SUBJECT_BUYER . '1798',
            ]
        );
    }
    public function updatePaymentStatus($orderIds, $transaction_id, $balance_transaction)
    {
        try {
            $orderIds = json_decode($orderIds);
            $storeOrder =  StoreOrder::where('order_id', $orderIds[0])->first();
            $buyerId = $storeOrder->buyer_id;
            $randomString = Str::random(8);
            $user = getGuestUserById($buyerId);

            if (!empty($user)) {
                $guesUser = User::findorfail($buyerId);
                $guesUser->password =  md5($randomString);
                $guesUser->save();
                $subject = 'Welcome to MallShark - Get Ready for an Unforgettable Shopping Experience!';
                $html = view('mail.guestuser-password', ['password' => $randomString]);
                $params = [
                    'from'    => 'Verify User  ' . env('SUPPORT_EMAIL'),
                    'to'      => $user->email,
                    'subject' => $subject,
                    'html'    => "$html",
                ];
                $this->mailgun->SendMailgunMail($params);
                $guesUser->guest_user = 0;
                $guesUser->save();
            } else {
                $user = getUserByIdchat($buyerId);
            }
            $products = collect();
            foreach ($orderIds as $key => $orderId) {
                $store_products = DB::table('order_items as oi')
                    ->join('products as p', 'oi.item_id', '=', 'p.product_id')
                    ->leftJoin('product_variation as pv', 'oi.variation_id', '=', 'pv.variation_id')
                    ->where('oi.order_id', $orderId)
                    ->select(
                        'oi.price',
                        'oi.sold_price',
                        'oi.quantity',
                        'oi.shipping_amount',
                        'p.slug',
                        'p.title',
                        'p.feature_image',
                        'p.product_type',
                        'pv.product_variation_image'
                    )
                    ->get();
                $products = $products->merge($store_products);
                $storeOrder =  StoreOrder::where('order_id', $orderId)->first();

                if ($storeOrder) {
                    $storeOrder->charge_status = 'paid';
                    $storeOrder->transaction_id = $transaction_id;
                    $storeOrder->balance_transaction = $balance_transaction;
                    $storeOrder->save();
                    if ($storeOrder->wasChanged('charge_status')) {
                        try {

                            //prepare notification for buyer

                            $storeId = $storeOrder->store_id;
                            $buyerId = $storeOrder->buyer_id;
                            $shippingAddress = ShippingAddress::where('sh_id', $storeOrder->shipping_address)->first();
                            $store = getStoreInfoById((int) $storeId);
                            $sellerId = isset($store[0]->user_id) ? $store[0]->user_id : '';
                            $subjectBuyer = Constants::ORDER_CREATE_SUBJECT_BUYER . $orderId;
                            // add buyer notification
                            addNotifications($buyerId, $sellerId, $subjectBuyer, '', URL::to('new-buyer-orders-detail/' . $orderId), 'buyer_order_details', $orderId, 'buyer');
                            //send  notification to buyer
                            $device_tokens = DeviceToken::where('user_id', $buyerId)->where('app_flag', 'buyer')->get();
                            if ($device_tokens) {
                                if (isset($device_tokens) && count($device_tokens) > 0) {

                                    foreach ($device_tokens as $device_token_obj) {


                                        FCMService::send(
                                            $device_token_obj->fcm_token,
                                            [
                                                'title' => $subjectBuyer,
                                            ]
                                        );
                                    }
                                }
                            }
                            // prepare notification for seller 
                            $subjectSeller = Constants::ORDER_CREATE_SUBJECT_SELLER . $orderId;
                            // add seller notification
                            addNotifications($sellerId, $buyerId, $subjectSeller, '', URL::to('/brand-order-details/' . $orderId), 'brand_order_details', $orderId, 'seller');
                            //send  notification to seller
                            $seller_device_tokens = DeviceToken::where('user_id', $sellerId)->where('app_flag', 'seller')->get();
                            if ($seller_device_tokens) {
                                if (isset($seller_device_tokens) && count($seller_device_tokens) > 0) {
                                    foreach ($seller_device_tokens as $device_token_obj) {


                                        SellerFcmService::send(
                                            $device_token_obj->fcm_token,
                                            [
                                                'title' => $subjectSeller,
                                            ]
                                        );
                                    }
                                }

                                // Send Emails here


                            }
                        } catch (\Exception $e) {
                            $paymentLog = new Logger('');
                            $paymentLog->pushHandler(new StreamHandler(storage_path('logs/notifactions_failed.log')), Logger::ERROR);
                            $paymentLog->error('Notification failed', ['error' => $e->getMessage()]);
                        }
                    }
                }
            }
            $products = $products->toArray();
            $state_id = $shippingAddress->state;
            $country_id = $shippingAddress->country;
            $state = getStatebyId($state_id);
            $state_name = $state['state_name'];
            $country = getCountryById($country_id);
            $country_name = $country['code'];
            $html = view('mail.new-order-client-side-api', [
                'customerDetail' => $user,
                'productDetails' => $products,
                'email' => $user->email,
                'name' => $shippingAddress['full_name'],
                'city' => $shippingAddress->city,
                'street' => $shippingAddress->street,
                'postalcode' => $shippingAddress->post_code,
                'state' => $state_name,
                'country' => $country_name,
                'order_id' => implode(',', $orderIds)
            ]);
            $subject = 'Order Placed! Order #'.$orderId;
            $toEmail = $user->email;
            $bccEmails = ['sajidoon@mindblazetech.com','robocoder42@gmail.com','care@mallshark.com','fugatesklcc@yahoo.com','aatika@mallshark.com','chadfugate85@gmail.com','chad@mallshark.com'];
            $params = [
                'from'    => 'MallShark  ' . env('SUPPORT_EMAIL'),
                'to'      => $toEmail,
                'bcc'     => implode(',', $bccEmails),
                'subject' => $subject,
                'html'    => "$html",
            ];
            $results = $this->mailgun->SendMailgunMail($params);
            return true;
        } catch (\Exception $e) {
            $paymentLog = new Logger('');
            $paymentLog->pushHandler(new StreamHandler(storage_path('logs/checkout_payment.log')), Logger::ERROR);
            $paymentLog->error('Store Order', ['Message' => $e->getMessage()]);
        }
    }
    public function getOrderStatus()
    {
        if ($this->_request->has('order_id') && $this->_request->filled('order_id')) {

            $storeOrder = StoreOrder::where('order_id', $this->_request->input('order_id'))->first();
            if ($storeOrder) {
                return response()->json([
                    'status' => true,
                    'order_id' => $storeOrder->order_id,
                    'charge_status' => $storeOrder->charge_status
                ]);
            }
        }
        return response()->json(['status' => false, 'message' => 'Order not found'], 404);
    }
    public function deleteFailedOrder($orderId)
    {
        $storeOrder = StoreOrder::where('order_id', $orderId)->first();
        if ($storeOrder) {
            $storeOrder->delete();
            return true;
        } else {
            false;
        }
    }


    public function placeOrder()
    {

        $validateUser = Validator::make(
            $this->_request->all(),
            [
                'first_name' => 'required',
                'last_name' => 'required',
                'country' => 'required',
                'user_email' => 'required',
                'states' => 'required',
                'address' => 'required',
                'city' => 'required',
                'post_code' => 'required',
                'stripeToken' => 'required',
                'total_amount' => 'required',
                'orderdata' => 'required|array',
            ]
        );
        if ($validateUser->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'validation error',
                'errors' => $validateUser->errors()
            ], 401);
        }
        Stripe::setApiKey($this->apiKey);
        try {

            $token = $this->_request->input('stripeToken');
            $user_email = $this->_request->input('user_email');
            $first_name = $this->_request->input('first_name');
            $last_name = $this->_request->input('last_name');
            $country = $this->_request->input('country');
            $states = $this->_request->input('states');
            $address = $this->_request->input('address');
            $city = $this->_request->input('city');
            $post_code = $this->_request->input('post_code');
            $total_amount = $this->_request->input('total_amount');
            $OrderData = $this->_request->input('orderdata');

            if ($token != '') {
                $Stripe =  Stripe::setApiKey($this->apiKey);
                $customer =  Customer::create([
                    'email' => $user_email,
                    'name' =>  "",
                ]);

                $card = \Stripe\Customer::createSource(
                    $customer->id,
                    array('source' => $token)
                );

                $charges = array();


                // calucalte total chargables
                $totalOrdersAmount = 0;
                $totalOrder = 0;
                $totalShipping = 0;
                if (is_array($OrderData)) {
                    // dd($OrderData);
                    foreach ($OrderData as $storeId => $store) {
                        // dd($store);
                        $orderAmount = 0;
                        foreach ($store as $product) {

                            $quantity = $product['product_quantity'];
                            $shippingPrice = $product['shipping_price'];
                            $productPrice = $product['price'] * $quantity;
                            $orderAmount += $productPrice + $shippingPrice;

                            // for gtm start
                            $totalOrder += $productPrice + $shippingPrice;
                            $totalShipping +=  $shippingPrice;
                            $storeData =  getStoreInfoByIdCartApi($product['store_id']);
                            $category_id = getCategoriesByProductID($product['product_id']);
                            if (isset($category_id[0])) {
                                $productCategory = getCategoryByCateId($category_id[0]);
                            } else {
                                $productCategory = null;
                            }

                            $category = isset($productCategory) ? $productCategory['categoryTitle'] : '';
                            $productInfo = array(
                                "item_id" => $product['product_id'],
                                "item_name" => $product['title'],
                                "item_category" => $category,
                                "item_brand" => $storeData['store_name'],
                                "item_list_id" => "purchase",
                                "item_list_name" => "Place Order",
                                "price" => $product['price'],
                                "quantity" => $product['product_quantity'],
                            );
                            $products[] = $productInfo;
                            // for gtm end

                        }
                        $stripe_fee = ($orderAmount * (2.9 / 100) + 0.33);
                        $temp_amount = $orderAmount - $stripe_fee;
                        $storeCampaign = getCampaignsByStoreId($storeId);
                        $totalActiveProducts = getTotalActiveProductsByStore($storeId);
                        if (isset($storeCampaign) && $totalActiveProducts >= 10) {
                            $benifitsdata = json_decode($storeCampaign->benefits, true);
                            $platformfeeCheck = isset($benifitsdata['platform_fee']) ? $benifitsdata['platform_fee'] : false;

                            if (!$platformfeeCheck) {
                                $platform_fee = $benifitsdata['percentage'];
                            }
                        } else {

                            $platform_fee = $temp_amount * 0.045;
                        }

                        $total_fee = $stripe_fee +  $platform_fee;
                        $payable_amount = $orderAmount - $total_fee;

                        $paymentInfo = array(
                            "platform_fee" => $platform_fee,
                            "payable_amount" => $payable_amount,
                        );
                        $charges[$storeId] = $paymentInfo;
                        $totalOrdersAmount += $orderAmount;
                    }
                } else {
                    // Handle the case where $OrderData is not an array
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Order data is not in the expected format'
                    ]);
                }

                // return response()->json([
                //     'status' => false,
                //     'pos' => 'beforecharge',
                //     'message' => $customer->id
                // ]);
                $charge = Charge::create(array(
                    "customer" => $customer->id,
                    "description" => "Products Purchased",
                    "amount" => round($totalOrdersAmount * 100),
                    "currency" => "usd",
                    "source" => $card->id,
                    "transfer_group" => 'order002'
                ));
                if ($charge->paid && $charge->status == 'succeeded') {
                    // charged successfully

                    foreach ($OrderData as $storeId => $store) {
                        $charges[$storeId]['paymentId'] =  $charge->id;
                        $charges[$storeId]['balance_transaction'] =  $charge->balance_transaction;
                        $charges[$storeId]['payerID'] =  $charge->source->payerID;
                    }

                    $output["useremail"] = $user_email;
                    $output["full_name"] = $first_name . ' ' . $last_name;
                    $output["country"] = $country;
                    $output["states"] = $states;
                    $output["city"] = $city;
                    $output["post_code"] = $post_code;
                    $output["address"] = $address;
                    $output["total_amount"] = $total_amount;
                    $output["charges"] = $charges;
                    $output["orderData"] = $OrderData = $this->_request->input('orderdata');
                    // dd($total_amount);
                    $thankyouOutput["useremail"] = $user_email;
                    $thankyouOutput["full_name"] = $first_name . ' ' . $last_name;
                    $thankyouOutput["country"] = $country;
                    $thankyouOutput["states"] = $states;
                    $thankyouOutput["city"] = $city;
                    $thankyouOutput["post_code"] = $post_code;
                    $thankyouOutput["address"] = $address;
                    $thankyouOutput["total_amount"] = $total_amount;
                    $thankyouOutput["orderData"] = $OrderData = $this->_request->input('orderdata');

                    $this->accessObj = new StoreOrdersAccess();
                    $orderId = $this->accessObj->create_orderApi($output);
                    // $this->placeOrderNotification($orderId);
                    $thankyouOutput["orderId"] = $orderId;

                    return response()->json([
                        'status' => true,
                        'message' => 'Thankyou for purchasing with Mallshark! Please check your email for the receipt(s) of this order.',
                        'thankyouData' => $thankyouOutput,
                    ]);
                } else if (!$charge->paid && $charge->status = 'failed') {

                    return response()->json([
                        'status' => false,
                        'message' => 'Your payment was not successfull please try again'
                    ]);
                }
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'Token is not available'
                ]);
            }
        } catch (\Exception $th) {
            $paymentLog = new Logger('');
            $paymentLog->pushHandler(new StreamHandler(storage_path('logs/checkout_payment.log')), Logger::INFO);
            $paymentLog->error('Failed', ['Message' => $th->getMessage()]);
            return response()->json([
                'status' => false,
                'message' =>  $th->getMessage()
            ]);
        }
    }

    public function placeOrderOld(Request $request)
    {

        // echo '$request->all(): <pre>' .print_r($request->all(),true). '</pre>'; die;
        //   $input = $request->shipping_data_stripe;
        //     parse_str($input, $outputshipping);
        //     if(empty($outputshipping['states_countries'])){
        //         $response = "Please Select Country and states";
        //         return redirect()->back()->withErrors($response);

        //          die;
        //      }
        //  if(empty($request->shipping_data_stripe)){
        //     $response = "Please Fill the shipping form";
        //     return redirect()->back()->withErrors($response);

        //      die;
        //  }
        //  if(empty($request->stripe_email)){
        //     $response = "Please Enter Your Email ";
        //     return redirect()->back()->withErrors($response);

        //      die;
        //  }
        //  if(empty($request->orderdata)){
        //     $response = "Please update your cart";
        //     return redirect()->back()->withErrors($response);
        //      die;
        //  }
        $shipping_data = $request->input('shipping_data_stripe');

        $creditPlanId = $request->input('credit_pan_id');
        $creditPurchase = $request->input('credit_purchase');
        $creditPostPurchase = $request->input('credit_post_purchase');
        $creditUserPurchase = $request->input('credit_user_purchase');
        $creditStoreUserPurchase = $request->input('credit_store_user_purchase');
        $stipe_email = $request->input('stripe_email');

        $descreption = "";
        if (isset($creditPurchase)) {
            $descreption = "Credit Purchase";
        }
        if (isset($creditPostPurchase)) {
            $descreption = "Credit Post Purchase";
        }
        if (isset($creditUserPurchase)) {
            $descreption = "User Credit Purchase";
        }
        if (isset($creditStoreUserPurchase)) {
            $descreption = "Store User Credit Purchase";
        } else {
            $descreption = "Products Purchased";
        }



        parse_str($shipping_data, $output);
        $orderData = $request->session()->get('orderData');
        $total_price = round($request->total_amount_ship);

        if (isset($creditPlanId)) {
            $creditPlanObj = new Creditaccess();
            $planData = $creditPlanObj->getCreditPackageById($creditPlanId);
        }
        $token = isset($_POST['stripeToken']) ? $_POST['stripeToken'] : '';


        $user_info = getUserById(getCurrentUserId());
        $array = json_decode(json_encode($user_info), true);


        if ($token != '') {
            $Stripe =  Stripe::setApiKey($this->apiKey);
            //Destination Charges
            $c_email = $stipe_email;
            try {


                // Create a new customer in Stripe
                $customer =  Customer::create([
                    'email' => $c_email,
                    'name' =>  "",

                ]);

                $card = \Stripe\Customer::createSource(
                    $customer->id,
                    array('source' => $token)
                );

                $stripe = new \Stripe\StripeClient(env('STRIPE_API_KEY'));


                $totals = [];
                $deductions = [];
                $charges = array();
                $products = array();
                $OrderData =  json_decode($request->orderdata, true);
                // calucalte total chargables
                $totalOrdersAmount = 0;
                $totalOrder = 0;
                $totalShipping = 0;

                foreach ($OrderData as $storeId => $store) {

                    $orderAmount = 0;
                    foreach ($store as $product) {
                        $quantity = $product['product_quantity'];
                        $shippingPrice = $product['shipping_price'];
                        $productPrice = $product['price'] * $quantity;
                        $orderAmount += $productPrice + $shippingPrice;

                        // for gtm start
                        $totalOrder += $productPrice + $shippingPrice;
                        $totalShipping +=  $shippingPrice;
                        $storeData =  getStoreInfoByIdCartApi($product['store_id']);
                        $category_id = getCategoriesByProductID($product['product_id']);
                        if (isset($category_id[0])) {
                            $productCategory = getCategoryByCateId($category_id[0]);
                        } else {
                            $productCategory = null;
                        }

                        $category = isset($productCategory) ? $productCategory['categoryTitle'] : '';
                        $productInfo = array(
                            "item_id" => $product['product_id'],
                            "item_name" => $product['title'],
                            "item_category" => $category,
                            "item_brand" => $storeData['store_name'],
                            "item_list_id" => "purchase",
                            "item_list_name" => "Place Order",
                            "price" => $product['price'],
                            "quantity" => $product['product_quantity'],
                        );
                        $products[] = $productInfo;
                        // for gtm end

                    }
                    $stripe_fee = ($orderAmount * (2.9 / 100) + 0.33);
                    $temp_amount = $orderAmount - $stripe_fee;

                    $storeCampaign = getCampaignsByStoreId($storeId);

                    $totalActiveProducts = getTotalActiveProductsByStore($storeId);


                    if (isset($storeCampaign) && $totalActiveProducts >= 10) {
                        $benifitsdata = json_decode($storeCampaign->benefits, true);
                        $platformfeeCheck = isset($benifitsdata['platform_fee']) ? $benifitsdata['platform_fee'] : false;

                        if (!$platformfeeCheck) {
                            $platform_fee = $benifitsdata['percentage'];
                        }
                    } else {

                        $platform_fee = $temp_amount * 0.045;
                    }

                    $total_fee = $stripe_fee +  $platform_fee;
                    $payable_amount = $orderAmount - $total_fee;

                    $paymentInfo = array(
                        "platform_fee" => $platform_fee,
                        "payable_amount" => $payable_amount,
                    );
                    $charges[$storeId] = $paymentInfo;
                    $totalOrdersAmount += $orderAmount;
                }


                $charge = Charge::create(array(
                    // "application_fee" => round($chargeable * 100),
                    "customer" => $customer->id,
                    "description" => $descreption,
                    "amount" => round($totalOrdersAmount * 100),
                    "currency" => "usd",
                    "source" => $card->id,
                    "transfer_group" => 'order002'
                ));
                if ($charge->paid && $charge->status == 'succeeded') {
                    // charged successfully

                    foreach ($OrderData as $storeId => $store) {
                        $charges[$storeId]['paymentId'] =  $charge->id;
                        $charges[$storeId]['balance_transaction'] =  $charge->balance_transaction;
                        $charges[$storeId]['payerID'] =  $charge->source->payerID;
                    }

                    $data =  json_decode($request->orderdata, true);

                    $output["useremail"] = $c_email;
                    $output["charges"] = $charges;
                    $output["orderData"] = $data;
                    // dd($data);
                    $this->accessObj = new StoreOrdersAccess();

                    $result  = $this->accessObj->create_order($output);
                    // dd($result);
                    return response()->json([
                        'status' => true,
                        'message' => 'Thankyou for purchasing with Mallshark! Please check your email for the receipt(s) of this order.',
                        'data' => $products,
                        'transactionId' => $charge->id,
                        'total_amount' => round($totalOrder, 2),
                        'total_shipping' => $totalShipping,
                    ]);
                } else if (!$charge->paid && $charge->status = 'failed') {

                    return response()->json([
                        'status' => false,
                        'message' => 'Your payment was not successfull please try again ',
                    ]);
                }




                //   count total platform_fee, chargeable,
                foreach ($OrderData as $storeId => $store) {
                    $totalPrice = 0;
                    $acount_id = $store[0]['stripe_account_id'];
                    foreach ($store as $product) {
                        $quantity = $product['product_quantity'];
                        $shippingPrice = $product['shipping_price'];

                        $Price = $product['price'] * $quantity;
                        $productprice = $Price + $shippingPrice;
                        $totalPrice += $productprice;
                    }
                    // dd($totalPrice);
                    $chargeable = $totalPrice * 0.04;
                    //  $charge = Charge::create(array(
                    //         "amount" => round($totalPrice * 100),
                    //         "currency" => "usd",
                    //         "customer" => $customer->id,
                    //         "source" => $card->id,
                    //         "description" => $descreption,
                    //         "application_fee" => round($chargeable * 100),
                    //         "transfer_data" => [
                    //             "destination" => $acount_id,
                    //         ],
                    //     ));

                    //  $charge = Charge::create(array(
                    //      // "application_fee" => round($chargeable * 100),
                    //      "customer" => $customer->id,
                    //      "description" => $descreption,
                    //         "amount" => round($totalPrice * 100),
                    //         "currency" => "usd",
                    //         "source" => $card->id,
                    //         "transfer_group" => 'order002'
                    //     ));
                    //  $chargeInfo = array(
                    //     "paymentID" => $charge->id,
                    //     "balance_transaction" => $charge->balance_transaction,
                    //     "payerID" => $charge->source->payerID,
                    //     "userEmail" => $c_email,
                    //     "accountID" => $acount_id,
                    // );

                    // $charges[$storeId] = $chargeInfo;
                }


                //     if ($charge->status == 'succeeded') {
                //         if (isset($creditPurchase)) {
                //             $productId = Session::get('featur_product_id');
                //             $creditObj = new Creditaccess();
                //             $creditObj->makeProductFeature($productId, $paystripe = true);
                //     $request->session()->flash('SuccessMessage', 'Product has been featured successfully.');
                //     return redirect('store-products');
                //      }





                //     $data =  json_decode($request->orderdata,true);

                //     $output["useremail"] = $c_email;

                //     $output["charges"] = $charges; 
                //     $output["orderData"] = $data;
                //     $this->accessObj = new StoreOrdersAccess();

                //    $resul  = $this->accessObj->create_order($output);

                // }

            } catch (\Throwable $th) {
                if ($th->getMessage() && !empty($th->getMessage())) {
                    return response()->json(['status' => false, 'message' => $th->getMessage()]);
                }
                return response()->json(['status' => false, 'message' => "Failed"]);
            }
        } else {
            echo 'Your token is empty';
        }
        die();
    }
    public function placeOrderNotification($order_id)
    {
        $user = User::with('device_token')->findOrFail(auth()->user()->user_id);
        if ($user) {
            $tokens = [];
            foreach ($user->device_token as $device_token_obj) {
                array_push($tokens, $device_token_obj->fcm_token);
            }
            $notification = FCMService::send(
                $tokens,
                [
                    'title' => 'Order placed',
                    'body' => 'Order' . $order_id . 'Placed successfully',
                ]
            );
            if ($notification) {
            }
        }
    }
    public function handleCallback(Request $request)
    {
        // $code = $request->input('code');
        // $state = $request->input('state');

        // Stripe::setApiKey(env('STRIPE_SECRET'));

        try {
            // $response = OAuth::token([
            //     'grant_type' => 'authorization_code',
            //     'code' => $code,
            // ]);

            // $connectedAccountId = $response->stripe_user_id;
            // Save the connected account ID to your database
            // Assuming you have a User model with a stripe_account_id field
            // $user = Auth::user();
            // $user->stripe_account_id = $connectedAccountId;
            // $user->save();

            return redirect('mallsharkseller://stripe-auth-success'); // Redirect back to your app
        } catch (\Exception $e) {
            // Log::error('Stripe OAuth error: ' . $e->getMessage());
            return redirect('mallsharkseller://stripe-auth-failure'); // Redirect back to your app
        }
    }
    public function activateStipe(Request $request)
    {

        // if ($request->ajax()) {
        $posedData = $request->input();
        $data = $this->stripeObj->genrateStripeOnBoardLinkApi();
        // $data = $this->stripeObj->changeStripeStatus($posedData);
        return response()->json([
            'status' => true,
            'data' => $data
        ]);
        //  echo 'Data: <pre>' .print_r($data,true). '</pre>'; die();
        // }
    }
    public function editStripeAccount(Request $request)
    {
        $userId = auth()->user()->user_id;
        $store = getStoreDataByUserId($userId);
        $posedData = $store->account_id;
        // echo '$posedData: <pre>' .print_r($posedData,true). '</pre>'; die();
        $data = $this->stripeObj->editStripeUserApi($posedData);

        return response()->json([
            'status' => true,
            'data' => $data
        ]);
    }
    public function createLoginLinkStripeAccount(Request $request)
    {
        $posedData = $request->input();
        $data = $this->stripeObj->changeStripeStatusApi($posedData);

        return response()->json([
            'status' => true,
            'data' => $data
        ]);
    }
    public function onLoadConnectStripeAccount(Request $request){
        $posedData = $request->input();
        $data = $this->stripeObj->changeStripeStatusOnLoadApi($posedData);
       
     return $data;
    }
}
