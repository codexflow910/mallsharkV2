<?php

namespace App\Http\Controllers;

use App\Constants;
use Stripe\Token;
use Stripe\Charge;
use Stripe\Refund;
use Stripe\Stripe;
use Stripe\EphemeralKey;
use Stripe\PaymentIntent;
use Stripe\Customer;
use App\Models\User;
use App\Services\FCMService;
use Illuminate\Http\Request;
use Stripe\OAuth;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Stripe\Exception\CardException;
use App\Http\Controllers\Controller;
use App\Models\DeviceToken;
use App\Models\StoreOrder;
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
use App\Models\OrderItems;
use App\Models\ShippingAddress;
use Illuminate\Support\Facades\Log;

use Session;
use App\Models\Cart;
//use App\models\ShAddress;
use Mailgun\Mailgun;
use App\Models\Product;
use App\Models\Userrole;
use App\Models\StoreEscrow;
use App\Models\DiscountUsage;
use App\Repositories\Formulas;
use Illuminate\Support\Carbon;
use App\Services\DiscountsService;
use App\Http\Controllers\MandrillT;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use function PHPUnit\Framework\isNull;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Redirect;
use App\Http\Controllers\PoolTController;
use App\Http\Controllers\StripeController;
use App\Http\Controllers\EasypostController;
use App\Repositories\Transactions\PoolTAccess;

use App\Repositories\Discounts\Discountsaccess;
use App\Repositories\Transactions\WalletAccess;
use App\Http\Controllers\ElasticSearchController;


class PaymentController extends Controller
{
    private $accessObj;
    private $apiKey;
    private $_request;
    private $stripeObj;
    private $_userobj;
    private $_buyerobj;
    private $mailgun;

    private $store_order;
    private $order_items;
    private $shipping_address;
    private $mandrill;
    private $walletTransactions;
    private $_esearch;
    private $storeEscrew;
    private $_userRole;
    private $_user;
    private $_cart;
    protected $discountsService;

    public function __construct(Request $_request)
    {
        $this->_userobj = new Useraccess();
        $this->_buyerobj = new Buyeraccess();
        $this->apiKey = env('STRIPE_API_KEY');
        // $this->stripeObj = new StripeAccess();
        $this->_request = $_request;
        $this->stripeObj = new StripeAccess();
        $this->mailgun = new MailgunController();
        $this->shipping_address = new ShippingAddress();
        $this->store_order = new StoreOrder;
        $this->order_items = new OrderItems;
        $this->stripeObj = new StripeController();
        $this->mandrill = new MandrillT();
        $this->PoolTAccess = new PoolTAccess();
        $this->walletTransactions = new WalletAccess();
        $this->mailgun = new MailgunController();
        $this->_esearch = new ElasticSearchController();
        $this->storeEscrew = new StoreEscrow();
        $this->_user = new User();
        $this->_userRole = new Userrole();
        $this->_cart = new Cart();
        $discountsAccess = new Discountsaccess();
        $this->discountsService = new DiscountsService($discountsAccess);
    }
    public function createPaymentIntent(Request $request)
    {
        // dd($this->_request->all());
        $validateUser = Validator::make(
            $this->_request->all(),
            [
                'first_name' => 'required',
                'last_name' => 'required',
                'country' => 'required',
                'user_email' => 'required',
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
                        $productPrice = 0;
                        
                        $quantity = $product['product_quantity'];
                        $shippingPrice = $product['shipping_price'];
                        if ($product['on_sale'] == 0) {
                            if(isset($product['variation_id']) && !empty($product['variation_id'])){
                            
                                $productPrice = $product['variation_sale_price'] * $quantity;
                            }else{
                                $productPrice = $product['price'] * $quantity;
                                
                            }
                        } elseif ($product['on_sale'] == 1) {
                            if(isset($product['variation_id']) && !empty($product['variation_id'])){
                            
                                $productPrice = $product['product_variation_sale_price'] * $quantity;
                            }else{

                                $productPrice = $product['sale_price'] * $quantity;
                            }
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
                $orderIds = $this->create_orderApiv2($output);
                
                $thankyouOutput["orderId"] = $orderIds;
                // return response()->json([
                //     'status' => true,
                //     'message' => 'Order Stored',
                //     'data'=>$orderId
                // ]);\
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
                $totalOrdersAmount=number_format($totalOrdersAmount, 2, '.', '')*100;
                $totalOrdersAmount=number_format($totalOrdersAmount, 2, '.', '')*100;
                $paymentIntent = PaymentIntent::create([
                    'amount' => $totalOrdersAmount, // Amount in cents
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
    public function handleWebhook(Request $request)
    {
        $event = $request->all();

        // Log the event to verify the received data
        Log::info('Received Stripe webhook: ', $event);

        // Handle specific event types if needed
        switch ($event['type']) {
            case 'payment_method.attached':
                // Handle when a payment method is attached
                break;
            // Add more cases as needed for other events
        }

        return response()->json(['status' => 'success']);
    }

    public function checkPaymentMethodAvailability(Request $request)
    {
        // Log the availability status
        Log::info('Payment method availability: ', ['available' => $request->input('available')]);

        // You can perform additional logic here if needed

        return response()->json(['status' => 'success']);
    }
    public function create_orderApiv2($order_info = array())
    {
        try{

        
        $client = new \EasyPost\EasyPostClient(env('EASYPOST_API_KEY'));
        $sess = array();
        $insertedData = array();
        
        $order_ids = [];
        $shipping_address_id = '';
        $get_user_id = '';
        $item_price = '';


        $user_email = isset($order_info['useremail']) ? $order_info['useremail'] : "";

        $user = User::where('email', $user_email)->first();

        $login_key = str_random(25);
        // check if user doesn't exists create new one
        if ($user) {
            $get_user_id = $user->user_id;
            $this->shipping_address->user_id = $user->user_id;

            $this->shipping_address->full_name = $order_info['full_name'];
            $this->shipping_address->country = $order_info['country'];
            $this->shipping_address->state = isset($order_info['states']) ? $order_info['states'] : "46";
            $this->shipping_address->city = $order_info['city'];
            $this->shipping_address->street = $order_info['address'];
            $this->shipping_address->post_code = $order_info['post_code'];
            $this->shipping_address->save();
            $shipping_address_id = $this->shipping_address->sh_id;
        } else {
            // User does not exist, insert new record
            $this->_user->name = $order_info['full_name'];
            $this->_user->email = $user_email;
            $this->_user->password = md5('mallshark123456');
            $this->_user->forget_key = $login_key;
            $this->_user->register_source = 'MallShark';

            $this->_user->contact_no = isset($order_info['phone_number']) ? $order_info['phone_number'] : "";

            $this->_user->save();

            $lastInsertid = $this->_user->user_id;
            $get_user_id = $this->_user->user_id;



            $this->shipping_address->full_name = $order_info['full_name'];
            $this->shipping_address->country = $order_info['country'];
            $this->shipping_address->state = $order_info['states'];
            $this->shipping_address->street = $order_info['address'];
            $this->shipping_address->post_code = $order_info['post_code'];
            $this->shipping_address->city = $order_info['city'];
            $this->shipping_address->user_id = $lastInsertid;
            $this->shipping_address->save();
            $shipping_address_id = $this->shipping_address->sh_id;

            $this->_userRole->user_id = $lastInsertid;
            $this->_userRole->role_id = 4;
            $this->_userRole->save();
            // $url = URL::to('/change-password/' . $login_key);
            // $html = view('mail.activateUser_checkout', ['activate_url' => $url]);
            // $subject = 'Welcome to MallShark - Get Ready for an Unforgettable Shopping Experience!';

            // $toEmail = $order_info['useremail'];
            $toCompletename = $order_info['full_name'];
            // $params = [
            //     'from'    => 'MallShark  ' . env('SUPPORT_EMAIL'),
            //     'to'      => $toEmail,
            //     'subject' => $subject,
            //     'html'    => "$html",
            // ];
            // $results = $this->mailgun->SendMailgunMail($params);
            $this->_esearch->userMapping($lastInsertid);
        }
        //  _____________________USER CHECK ENDS________________

        $counter_g = 0;

        $shippingPriceArray = array();
        $productPriceArray = array();
        foreach ($order_info['orderData'] as $key1 => $val) {



            foreach ($val as $pval) {

                $totalOrderShimpentPrice = 0;
                $totalOrderPrice = 0;
                if ($key1 == $pval['store_id']) {

                    $product = Product::find($pval['product_id']);
                    if($product){
                        $product->sales = $pval['product_quantity'];
                        $product->save();
                    }
                    if ($pval['product_type'] == 'variant') {
                        // if($pval['on_sale'] == 1){
                        //     $vaiablePrice = isset($pval['sale_price']) ? $pval['sale_price'] : $pval['price'];
                        // }else{
                        //     $vaiablePrice = $pval['price'];
                        // }
                        

                        $vaiablePrice =  $pval['on_sale'] == 1 ? $pval['product_variation_sale_price'] : $pval['variation_sale_price'];
                        if (is_numeric($vaiablePrice) && is_numeric($pval['product_quantity'])) {
                            $totalOrderPrice = $totalOrderPrice + ($vaiablePrice * $pval['product_quantity']);
                        }
                    } else if ($pval['product_type'] == 'simple') {
                        if($pval['on_sale'] == 1){
                            $simplePrice = isset($pval['sale_price']) ? $pval['sale_price'] : $pval['price'];
                        }else{
                            $simplePrice = $pval['price'];
                        }
                        $quantity = $pval['product_quantity'];
                        if (!is_numeric($quantity)) {
                            $quantity = 1;
                        }

                        $totalOrderPrice = $totalOrderPrice + ($simplePrice * $quantity);
                    } else if ($pval['product_type'] == 'virtual') {
                        $virtualPrice = $pval['price'];
                        $totalOrderPrice = $totalOrderPrice +  $virtualPrice;
                    }
                    $shipping_price = isset($pval['shipping_price']) ? $pval['shipping_price'] : 0;
                    $totalOrderShimpentPrice = $totalOrderShimpentPrice + $shipping_price;

                    $str[$key1]['ship_price'] = $totalOrderShimpentPrice;
                    if (isset($shippingPriceArray[$key1])) {
                        $shippingPriceArray[$key1] = $totalOrderShimpentPrice + $shippingPriceArray[$key1];
                    } else {
                        $shippingPriceArray[$key1] = $totalOrderShimpentPrice;
                    }

                    if (isset($productPriceArray[$key1])) {
                        $productPriceArray[$key1] = $totalOrderPrice + $productPriceArray[$key1];
                    } else {
                        $productPriceArray[$key1] = $totalOrderPrice;
                    }
                }
            }
        }
        



        foreach ($order_info['orderData'] as $key => $val) {
            $chargeInfo = $order_info['charges'][$key];
            $platform_fee = $chargeInfo['platform_fee'];
            $payable_amount = $chargeInfo['payable_amount'];
            $paymentID = isset($chargeInfo['paymentId']) ? $chargeInfo['paymentId'] : '';
            $balanceTransaction = isset($chargeInfo['balance_transaction']) ? $chargeInfo['balance_transaction'] : '';
            $payerID = isset( $chargeInfo['payerID']) ?  $chargeInfo['payerID'] : '';
            $payment_status = $order_info['payment_status'] ? $order_info['payment_status'] : 'pending';
            $charge_status = $order_info['charge_status'] ? $order_info['charge_status'] : 'unpaid';
            if ($key != 'products_price' && $key != 'shipping_price' && $key != 'total_price' && $key != 'total_tax') {
                $this->store_order = new StoreOrder;
                
                $store_id = $key;
                $storeInfo = getStoreStatusById((int)$store_id);

                $google_analytics_arr[$counter_g] = array('transaction_id' => rand(258, 9854), 'affiliation' => 'Google online store', 'value' => "", 'currency' => 'USDC', 'tax' => 0, 'shipping' => $shippingPriceArray[$key]);
                $val['total_amount'] = 350;
                $val['shiping_amount'] = 30;
                $this->store_order->buyer_id = $get_user_id;
                // uncomment for crypto,further handle statuses 
                // if ($paymentID == "" &&  $order_info['balance_transaction'] == "") {
                //     $this->store_order->payment_method = 'crypto';
                //     $this->store_order->buyer_address = $order_info['buyer_address'];
                // } else {
                    $this->store_order->payment_method = 'stripe';
                // }
                $this->store_order->payment_status = $payment_status;
                $this->store_order->charge_status = $charge_status;
                $this->store_order->platform_fee = $platform_fee;
                $this->store_order->payable_amount = $payable_amount;
                $this->store_order->order_status = 'pending';
                $this->store_order->total_amount = $productPriceArray[$key] + $shippingPriceArray[$key];

                $this->store_order->shipping_address = $shipping_address_id;
                $this->store_order->shipping_method = !empty($val['shipping_method']) ? $val['shipping_method'] : '';
                $this->store_order->total_items = $val[0]['product_quantity'];
                $this->store_order->transaction_id = $paymentID;
                $this->store_order->balance_transaction = $balanceTransaction;
                $this->store_order->payerID = $payerID;
                $this->store_order->shipping_amount = $shippingPriceArray[$key];

                $this->store_order->store_id = $store_id;

                $order_id = $this->store_order->save();

                $lastInsertedId = $this->store_order->order_id;
                $items_Array = array();
                $sipping_amount = 0;
                if ($order_id) {
                    $store_data = getStoreInfoById((int)$store_id);
                    $currentDate = date('Y-m-d');
                    // $paymentDate = $currentDate;
                    $paymentDate = date('Y-m-d', strtotime($currentDate . ' + 10 days'));
                    $amountWithOutShip = $productPriceArray[$key] - $shippingPriceArray[$key];
                    $paymentProfit = $amountWithOutShip * 0.045;
                    $payableAmount = $productPriceArray[$key] - $paymentProfit;
                    $storeWallet = !empty($store_data[0]->wallet_address) ? $store_data[0]->wallet_address : '';

                    if ($paymentID == "" &&  $balanceTransaction == "") {
                        // $storeEscrew = new StoreEscrow();
                        // $storeEscrew->order_id = $lastInsertedId;
                        // $storeEscrew->store_id = $store_id;
                        // $storeEscrew->store_wallet_address = $storeWallet;
                        // $storeEscrew->order_price = $productPriceArray[$key];
                        // $storeEscrew->ship_price =  $shippingPriceArray[$key];
                        // $storeEscrew->order_price_with_out_shipping =  $amountWithOutShip;
                        // $storeEscrew->payable_amount =  $payableAmount;
                        // $storeEscrew->profit =  $paymentProfit;
                        // $storeEscrew->status =  'pending';
                        // $storeEscrew->profit_status =  'pending';
                        // $storeEscrew->payment_date =  $paymentDate;
                        // $storeEscrew->save();
                    }
                    
                    if (count($val) > 0) {
                        $total_amount = 0;
                        foreach ($val as $key => $product) {
                            if (isset($product['product_id']) && $product['product_id'] != '') {

                                if ($product['product_type'] == 'variant') {
                                    if($product['on_sale'] == 1){
                                    //  // $item_price = isset($product['sale_price']) ? $product['sale_price'] : $product['price'];
                                    //  $item_price = isset($product['variation_sale_price']) ? $product['variation_sale_price'] : $product['price'];
                                    $item_price = isset($product['variation_sale_price']) ? $product['variation_sale_price'] : $product['price'];
                                    }else{
                                      //  $item_price = $product['price'];
                                      $item_price = isset($product['variation_sale_price']) ? $product['variation_sale_price'] : $product['price'];
                                    }
                                    $quantity = $product['product_quantity'];
                                } else if ($product['product_type'] == 'simple') {
                                    if($product['on_sale'] == 1){
                                        $item_price = isset($product['sale_price']) ? $product['sale_price'] : $product['price'];
                                    }else{
                                        $item_price = $product['price'];
                                    }
                                    $quantity = $product['product_quantity'];
                                } else if ($product['product_type'] == 'virtual') {
                                  //  $item_price = isset($product['price']) ? $product['price'] : $product['price'];
                                   // $quantity = $product['product_quantity'];
                                   if (isset($product['on_sale']) && $product['on_sale'] == 1 )
                                    {
                                        $item_price = $product['sale_price'];
                                    }else{
                                        $item_price = isset($product['price']) ? $product['price'] : $product['price'];
                                    }
                                }

                                if (!is_numeric($quantity)) {
                                    $quantity = 1;
                                }
                                
                                $item_price_condition = is_numeric($item_price) ? $item_price : 0; // Check if $item_price is numeric, if not, set it to 0
                                $item_price = floatval($quantity * $item_price_condition);
                                //$item_price = floatval($item_price_condition);
                                $shipping_method = isset($product['method']) ? $product['method'] : "";
                                $store_id_pr = $product['store_id'];
                                $store_p = getStoreInfoById((int) $store_id_pr);
                                $shipping_charges = isset($product['shipping_price']) ? $product['shipping_price'] : 0;
                                if ($shipping_charges == '') {
                                    $shipping_charges = 0;
                                }
                                
                                $order_per_total = $shipping_charges + $item_price;
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
                                        
                                        // dd($image);
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
                                    $shipment = $client->shipment->retrieve($product['ship_id']);

                                    $created_date = date("Y-m-d H:i:s");
                                    $items_Array[] = array(
                                        'order_id' => $lastInsertedId, 'store_id' => $store_id, 'item_id' => $product['product_id'],
                                        //'sold_price' => $product['sale_price'],
                                        'sold_price' => (!empty($product['product_variation_sale_price'])) ? $product['product_variation_sale_price'] : $product['sale_price'],
                                        'sale_id' => $product['sale_id'],
                                        'variation_id' => $product_variation_id, 'quantity' => $product['product_quantity'], 
                                        //'price' => $item_price,
                                        'price' => (!empty($product['variation_sale_price'])) ? $product['variation_sale_price'] : $product['price'],
                                        'shipping_amount' => $product['shipping_price'], 'order_note' => '', 'item_tax' => '',
                                        'method' => $shipping_method, 'total' => $order_per_total, 'shipment_id' => $product['ship_id'], 'payment_service' => $product['shipping_method'], 'shipment_id' => $shipment->id, 'rate_id' => '', 'label_url' => '', 'lable_pdf_url' => '', 'tracking_id' => '', 'tracking_code' => '', 'shipment_object' => '', 'created_at' => $created_date,
                                        'product_name' => $p_name, 'variation_info' => $variation_info, 'item_image' => $newImagePath
                                    );
                                
                                } else {
                                    $created_date = date("Y-m-d H:i:s");
                                    $items_Array[] = array(
                                        'order_id' => $lastInsertedId, 'store_id' => $store_id, 'item_id' => $product['product_id'],
                                        //'sold_price' => $product['sale_price'],
                                        
                                        'sold_price' => (!empty($product['product_variation_sale_price'])) ? $product['product_variation_sale_price'] : $product['sale_price'],
                                        'sale_id' => $product['sale_id'],
                                        'variation_id' => $product_variation_id, 'quantity' => $product['product_quantity'], 
                                        //'price' => $item_price,
                                         
                                        'price' => (!empty($product['variation_sale_price'])) ? $product['variation_sale_price'] : $product['price'],
                                        'shipping_amount' => $shipping_price, 'order_note' => '', 'item_tax' => '',
                                        'shipment_id' => '', 'rate_id' => '', 'label_url' => '',
                                        'lable_pdf_url' => '', 'tracking_id' => '', 'tracking_code' => '', 'payment_service' => '', 'shipment_object' => '',
                                        'method' => $shipping_method, 'total' => $order_per_total, 'created_at' => $created_date,
                                        'product_name' => $p_name, 'variation_info' => $variation_info, 'item_image' => $newImagePath
                                    );
                                }
                                

                                $storeInfo = getStoreStatusById((int)$store_id);
                                

                                // echo '$storeInfo: <pre>' .print_r($storeInfo,true). '</pre>'; die();
                                if (isset($storeInfo->stripe) && $storeInfo->stripe == 1) {
                                    
                                    if (isset($_COOKIE['cartCookie'])) {
                                        unset($_COOKIE['cartCookie']);
                                        setcookie('cartCookie', null, -1, '/');
                                    }
                                    if (isset($_COOKIE['cartCookieCrypto'])) {
                                        unset($_COOKIE['cartCookieCrypto']);
                                        setcookie('cartCookieCrypto', null, -1, '/');
                                    }
                                }


                                $remainStock = "";
                                if (isset($product['variation_id']) && $product['variation_id'] != '') {
                                   
                                    variableUpdateCurrentStock($product['variation_id'], $product['product_id'], $product['product_quantity']);
                                    // change status of flash sale
                                    
                                    $productFlashsaleStatus = DB::table('products')->where('product_id', $product['product_id'])->value('flashsale_status');
                                    if ($productFlashsaleStatus == 'choosen') {
                                        
                                        $productData = getvariableProduct($product['product_id']);
                                        $remainStock = $productData['current_stock'];
                                        if ($remainStock <= 0) {
                                            
                                            DB::table('products')->where('product_id', $product['product_id'])->update(['flashsale_status' => 'standard']);
                                        }
                                        
                                    }
                                    
                                } else {
                                    
                                    simpleUpdateCurrentStock($product['product_id'], $product['product_quantity']);
                                    // change flash sale status
                                    $productquery = DB::table('products')->where('product_id', $product['product_id'])->select('flashsale_status', 'current_stock')->first();
                                    $remainStock = $productquery->current_stock;
                                    if ($productquery->flashsale_status == 'choosen' && $remainStock <= 0) {
                                        DB::table('products')->where('product_id', $product['product_id'])->update(['flashsale_status' => 'standard']);
                                    }
                                }
                                
                            }
                        }

                        if (count($items_Array) > 0) {
                            
                            $this->add_order_items($items_Array);
                        }
                        
                    }

                    //die();
                }
                
                $userData = getUserByIdchat($get_user_id);

                $subject = 'Your Order is Submitted successfully';
                $subjectStore = Constants::ORDER_CREATE_SUBJECT_SELLER.$lastInsertedId;
                $subjectBuyer = Constants::ORDER_CREATE_SUBJECT_BUYER.$lastInsertedId;
                $toEmail = $userData->email;

                $toCompletename = $userData->name;
                $store = getStoreInfoById((int) $store_id);

                $user_id = isset($store[0]->user_id) ? $store[0]->user_id : '';
                $userDataStore = getUserByIdchat((int) $user_id);

                if ($userDataStore != '' && isset($userDataStore->email)) {
                    $toEmailStore = $userDataStore->email;
                }
                if ($userDataStore != '' && isset($userDataStore->name)) {
                    $store_name = $userDataStore->name;
                }
                $country_name = $order_info['country'];
                if(isset($order_info['states'])){
                    $state_id = $order_info['states'];
                    $state = getStatebyId($state_id);
                    $state_name = $state['state_name'];
                }
                // $country = getCountryById($country_id);
                // $country_name = $country['code'];
                
                $html = view('mail.storecreateOrder', [
                    'email' => $order_info['useremail'], 'name' => $order_info['full_name'],
                    'city' => $order_info['city'], 'street' => $order_info['address'], 'postalcode' => $order_info['post_code'],
                    'state' =>isset($state_name) ? $state_name : null, 'country' => $country_name, 'storeName' => $store_name, 'order_id' => $order_id, 'productDetails' => $val
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
            $counter_g++;
            $order_ids[]=$lastInsertedId;
        }
          
        Session::put('google_analytics', $google_analytics_arr);
        DB::table('products_cart')->where('user_id', '=', getCurrentUserId())->delete();
        // die();
        return $order_ids;
        return $lastInsertedId;
    }catch(Exception $e){
        dd($e->getMessage());
    }
    }
    public function add_order_items($item_array = array())
    {
        $this->order_items->insert($item_array);
    }
}
