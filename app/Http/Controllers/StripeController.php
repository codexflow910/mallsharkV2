<?php

namespace App\Http\Controllers;


use Stripe\Plan;
use Stripe\Token;
use Stripe\Charge;
use Stripe\Refund;
use Stripe\Stripe;
use Stripe\Account;
use Stripe\Invoice;
use Stripe\Product;
use Stripe\Customer;
use Stripe\Transfer;
use App\Models\Store;
use Stripe\BankAccount;
use App\Models\BankInfo;
use Stripe\Subscription;
use App\Models\DigitalFiles;
use Illuminate\Http\Request;
use App\Models\CampaignStore;
use App\Services\DiscountsService;
//use App\Repositories\Home\Homeaccess;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Redirect;
use App\Repositories\Credit\Creditaccess;
use App\Repositories\stripe\StripeAccess;
use App\Repositories\Vendor\Vendoraccess;
use App\Repositories\Orders\StoreOrdersAccess;
use App\Repositories\Discounts\Discountsaccess;

class StripeController extends Controller {

    private $clientId;
    private $redirectUrl;
    private $stripeObj;
    private $stripeProductId;
    private $stripePlanId;
    private $accessObj;
    private $apiKey;
    private $bankinfo;
    private $store;
    private $mailgun;
    private $_request;
    protected $discountsService;

    public function __construct() {
        $this->clientId = env('STRIPE_CLIENT_ID');
      //  $this->redirectUrl = route('striperedirect');
        $this->stripeProductId = env('STRIPE_PRODUCT_ID');
        $this->stripePlanId = env('STRIPE_PLAN_ID');
        $this->apiKey = env('STRIPE_API_KEY');
        $this->apiSec = env('STRIPE_API_KEY');
        $this->stripeObj = new StripeAccess();
        $this->digtialFiles = new DigitalFiles();
        $this->bankinfo = new BankInfo();
        $this->store = new Store();
        $this->mailgun = new MailgunController();
        $discountsAccess = new Discountsaccess();
        $this->discountsService = new DiscountsService($discountsAccess);
        //$this->accessObj = new StoreOrdersAccess();
       // $this->_homeobj = new Homeaccess();
    }

    // public function connectAccount() {
    //     //$this->retrieveAccountDetail('acct_1ChxsdEMI3NOER1G');

    //     return $call = 'https://connect.stripe.com/oauth/authorize?response_type=code&client_id=' . $this->clientId . '&scope=read_write&redirect_uri=' . route('stripe-dashboard');
    // }
    public function handleMobileSuccess(Request $request)
    {
        $sessionId = $request->get('id');
    
        // You can validate the session here if you want using Stripe API...
    
        // Redirect to deep link
        return redirect()->to("mallshark://payment-success?id={$sessionId}");
    }
    public function handleMobileCancel(Request $request)
    {
        $sessionId = $request->get('id');
    
        // You can validate the session here if you want using Stripe API...
    
        // Redirect to deep link
        return redirect()->to("mallshark://payment-cancel");
    }
    public function storebankdetail(Request $request)
    {
        if ($request->isMethod('post')) {

            $stripe = new \Stripe\StripeClient(env('STRIPE_API_KEY'));
              $userinfo = getUserById(getCurrentUserId());
    
              $userEmail = $userinfo->email;
              $userName = $userinfo->name;
         
              $userId = Session::get('userData')['user_id'];
              $storeData = getStoreDataByUserId($userId); 
              $store_id = $storeData->store_id;
    
              $customer =  $stripe->customers->create([
                'email' => $userEmail,
                'name' =>  $userName,
              
              ]);
              $banks = $stripe->tokens->create([
                'bank_account' => [
                  'country' => $request->country,
                  'currency' => $request->currency,
                  'account_holder_name' => $request->account_holder_name,
                  'account_holder_type' => $request->account_holder_type,
                  'routing_number' => $request->routing_number,
                  'account_number' => $request->account_number,
                ],
              ]);
              $cust = $stripe->customers->retrieve(
                $customer->id,
                []
              );
              $stripe->customers->createSource(
                $customer->id,
                ['source' => $banks->id]
              );

            if(isset($customer->id)){
            $updateData = array();
            $updateData_stripe = array();
            $userId = Session::get('userData')['user_id'];
            $storeData = getStoreDataByUserId($userId); 
            $storeID =  !empty($storeData->store_id) ? $storeData->store_id :'';
           
            $updateData['customer_id'] = $customer->id;
            $updateData['bank_id'] = $banks->bank_account->id;
            $updateData['country'] =  $request->country;
            $updateData['currency'] = $request->currency;
            $updateData['account_holder_name'] =  $request->account_holder_name;
            $updateData['account_holder_type'] =  $request->account_holder_type;
            $updateData['routing_number'] =  $request->routing_number;
            $updateData['account_number'] =  $request->account_number;
               
            $this->store->where('store_id', '=', $storeID)->update($updateData);
            $updateData_stripe['stripe'] = 1;
            $this->store->where('store_id', '=', $store_id)->update($updateData_stripe);
                $request->session()->flash('updatesuccessmessage', 'Bank Address has been updated successfully');
                return redirect()->back();
            }
            
        }
        $bankinfo = $this->stripeObj->getstoreData();

        // if ($this->isMobileDevice()) {
        //     return view('Store/mobile/bankdetails', ['bankinfo' => $bankinfo]);
        // } else {
            return view('Store/bankdetails' , ['bankinfo' => $bankinfo]);
        // }

    }


    public function activateStipe(Request $request)
    {
        
        if ($request->ajax()) {
            $posedData = $request->input();
            $data = $this->stripeObj->genrateStripeOnBoardLink();
            // $data = $this->stripeObj->changeStripeStatus($posedData);
            return $data;
            //  echo 'Data: <pre>' .print_r($data,true). '</pre>'; die();
        }
    }

    public function createLoginLinkStripeAccount(Request $request){
        $posedData = $request->input();
        $data = $this->stripeObj->changeStripeStatus($posedData);
       
     return $data;
    }

    public function onLoadConnectStripeAccount(Request $request){
        $posedData = $request->input();
        $data = $this->stripeObj->changeStripeStatusOnLoad($posedData);
       
     return $data;
    }
    public function updateStripeIddB(Request $request){
        $posedData = $request->input();
        $data = $this->stripeObj->UpdateStripIdStore($posedData);
       
     return $data;
    }

    public function editStripeAccount(Request $request)
    {
        $posedData = $request->input();
        // echo '$posedData: <pre>' .print_r($posedData,true). '</pre>'; die();
        $data = $this->stripeObj->editStripeUser($posedData);
       
        return $data;
    }

    public function transferAmount(Request $request){
       // dd($request->useremail);
       $stripe = new \Stripe\StripeClient(env('STRIPE_API_KEY'));
       // $stripe = new \Stripe\StripeClient('sk_test_51IxVIOKJU8arBnJ0bCPopA9l58fEd3NTTSFZa2loWsQlRWSK7lhD1Wu5Y03PqFF81RvKktb4cBtEEWWXQRE3uikZ00RHe3AM8q');
       $stripe->transfers->create([
           'amount' => 202000,
           'currency' => 'usd',
           'destination' => 'acct_1MvF0YQSO6zopFya',
       ]);
       die('noman');


// echo '$stripe: <pre>' .print_r($stripe,true). '</pre>'; die();
//         die;
        // Set your secret key. Remember to switch to your live secret key in production.
// See your keys here: https://dashboard.stripe.com/apikeys
    

        $response = $stripe->accounts->retrieve(
            'acct_1MuChVQPlJhR7ao1',
            []
        );
        $link = $stripe->accountLinks->create([
            'account' =>'acct_1MuChVQPlJhR7ao1',
            'refresh_url' => 'https://example.com/reauth',
            'return_url' => 'https://example.com/return',
            'type' => 'account_onboarding',
        ]);
        echo 'Data: <pre>' .print_r($link,true). '</pre>'; die();
// $data = $stripe->accounts->createLoginLink(
//     'acct_1MtrkxQKCOz2BqGy',
//     []
//   );
echo 'Data: <pre>' .print_r($data,true). '</pre>'; die();
// echo 'Data: <pre>' .print_r($data,true). '</pre>'; die();
    // $stripe = new \Stripe\StripeClient(env('STRIPE_API_KEY'));
    // \Stripe\Stripe::setApiKey(env('STRIPE_API_KEY'));
    //  $acc = $stripe->accounts->all(['limit' => 50]);
    //  echo 'Data: <pre>' .print_r($acc,true). '</pre>'; die();
    // die;




       $account = $stripe->accounts->create([
            'type' => 'express',
            'country' => 'US',
            'email' => $request->useremail,
            
        ]);
       $link =  $stripe->accountLinks->create([
            'account' => $account->id,
            'refresh_url' => 'https://example.com/reauth',
            'return_url' => 'https://example.com/return',
            'type' => 'account_onboarding',
          ]);
          return $link->url;
        echo '$link: <pre>' .print_r($link->url,true). '</pre>'; die();


       
        // $customer_id = "gcus_1MlwWeQO334dZap2gXYC6xLy";
        // $payment_method = \Stripe\PaymentMethod::create(array(
        // "type" => "card",
        // "card" => array(
        //     "number" => "4000056655665556",
        //     "exp_month" => 12,
        //     "exp_year" => 2026,
        //     "cvc" => "123"
        // )
        // ));
echo 'Data: <pre>' .print_r($transfer,true). '</pre>'; die();
// \Stripe\Customer::update($customer_id, array(
//   "invoice_settings" => array(
//     "default_payment_method" => $payment_method->id
//   )
// ));
                
               
                // if ($transfer->status == 'paid') {
                //     echo 'Transfer successful!';
                // } else {
                //     echo 'Transfer failed!';
                // }
                // echo 'Data: <pre>' .print_r($transfer,true). '</pre>'; die();
    }

    public function metamaskCreatOrder(Request $request){
     //   echo '$request: <pre>' .print_r($request,true). '</pre>'; die();
        //   dd($request->all());
        if(empty($request->shipping_data)){
            $response = "Please Fill the shipping form";
            return redirect()->back()->withErrors($response);
            die;
        }
        if(empty($request->orderData)){
            $response = "Please update your cart";
            return redirect()->back()->withErrors($response);
            die;
        }
        $shipping_data = $request->shipping_data;
        $buyer_address = $request->buyer_address;
        $email_address = $request->email_address;
        $data =  json_decode($request->orderData,true);
        parse_str($shipping_data, $output);
        $output["paymentID"] = "";
        $output["balance_transaction"] = "";
        $output["payerID"] = 111111111;
        $output["orderData"] = $data;
        $output["buyer_address"] = $buyer_address;
        $output["useremail"] = $email_address;
        
        //exit;
        //  echo '$output: <pre>' .print_r($output,true). '</pre>'; die();
        $this->accessObj = new StoreOrdersAccess();
        $result = $this->accessObj->create_order_crypto($output);
        // echo '$result: <pre>' .print_r($result,true). '</pre>'; die();
        Session::flash('success', 'Thankyou for purchasing with Mallshark! Please check your email for the receipt(s) of this order.');
        // return Redirect::to('/');
        return Redirect::back();

    }
    public function stripeDashboard(Request $request) {
        $response_array = array();
        if (isset($_GET['scope']) && $_GET['scope'] != '') {
            $Authorization_code = $_GET["code"];
            $token_request_body = array(
                'grant_type' => 'authorization_code',
                'client_id' => $this->clientId,
                'code' => $Authorization_code,
                'client_secret' => $this->apiSec
            );
            $req = curl_init("https://connect.stripe.com/oauth/token");
            curl_setopt($req, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($req, CURLOPT_POST, true);
            curl_setopt($req, CURLOPT_POSTFIELDS, http_build_query($token_request_body));

     // TODO: Additional error handling
            $respCode = curl_getinfo($req, CURLINFO_HTTP_CODE);

            $resp = json_decode(curl_exec($req), true);

            curl_close($req);
            if (isset($resp["error"])) {
                $response_array["error"] = $resp["error"];
                $response_array["error_desc"] = $resp["error_description"];
            } else {
                $this->stripeObj->addStripeConnectedAccount($resp);
                $response_array["success"] = ' Your account is added successfully';
            }
        } else {
            if (isset($_GET['error']) && $_GET['error'] != '') {

                $response_array["error"] = $_GET['error'];
            } else {
                $response_array["error"] = " Your account is not added please contact to support";
            }
        }
        Session::flash('response_array', $response_array);
        return redirect('/store-dashboard');
    }

    public function index() {
        //return View::make("");
        return View('stripe.checkout')->render();
    }

    public function refundBack($order_id = '', $amount = '') {
        // Stripe::setApiKey($this->apiKey);
        // $re = Refund::create(array(
        //     "charge" => 1410,
        //     'amount' => 155 *100, 
        //     'transfer'=> 'tr_3NRwOsKJU8arBnJ00zMzgpg4', 
        // ));
        try {
           
       
        if ($order_id != '') {
            $orders = DB::table('store_orders')
                    ->select('transaction_id','session_id', 'total_amount','shipping_amount')
                    ->where('order_id', '=', $order_id)
                    ->get()
                    ->toArray();
            $session_id = isset($orders[0]->session_id) ? $orders[0]->session_id : '';
            $transaction_id = isset($orders[0]->transaction_id) ? $orders[0]->transaction_id : '';

            $amount = isset($orders[0]->total_amount) ? $orders[0]->total_amount : 0;
            $shipping = isset($orders[0]->shipping_amount) ? $orders[0]->shipping_amount : 0;
            

            $percent = 4;
            $withouShipping = $amount-$shipping;
            $finalAmount = $withouShipping - ($withouShipping * (2.9/100) + 0.3);
            $finalAmount =   round($finalAmount * 100);
            
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
                return false;
            }
        } else {
            return false;
        }
    } catch (\Throwable $th) {
        // dd($th);
        return false;
        // echo $th->getMessage();
    }
    }
    public function disperse(){
        
        
        $this->stripeObj->transferVendorPayments();

    }
    public function stripeResponse(Request $request) {
        // $couponCode = '10DollarCoupon';
        // $storeId = 179;
        // $cartTotal = 10000;
        // $cartQuantity = 4;
        // $result = $this->discountsService->applyCoupon($couponCode, $storeId, $cartTotal, $cartQuantity);
        // dd($result);
        
              $input = $request->shipping_data_stripe;
                parse_str($input, $outputshipping);
                if(empty($outputshipping['states_countries'])){
                    $response = "Please Select Country and states";
                    return redirect()->back()->withErrors($response);
                     die;
                 }
             if(empty($request->shipping_data_stripe)){
                $response = "Please Fill the shipping form";
                return redirect()->back()->withErrors($response);
                 die;
             }
             if(empty($request->stripe_email)){
                $response = "Please Enter Your Email ";
                return redirect()->back()->withErrors($response);
                 die;
             }
             if(empty($request->orderdata)){
                $response = "Please update your cart";
                return redirect()->back()->withErrors($response);
                 die;
             }
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
          $Stripe=  Stripe::setApiKey($this->apiKey);         
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
              $OrderData =  json_decode($request->orderdata,true);
                // calucalte total chargables
                $totalOrdersAmount = 0;
                $totalOrder = 0;
            
                foreach ($OrderData as $storeId => $store) {
                    $totalShipping = 0;
                    $orderAmount = 0;
                    $productsTotal = 0;
                    $couponCode = '';
                    $totalProductsQuantity = 0;
                    $discount = 0;
                    $couponKey = array_key_last($store); 
                    if (isset($store[$couponKey]['coupon_code'])) {
                        $couponCode = $store[$couponKey]['coupon_code'];
                        array_pop($store); 
                    }

                    foreach($store as $product){
                        
                        $product_type = $product['product_type'] ? $product['product_type'] : null;
                        $quantity = $product['product_quantity'];
                        $shippingPrice = $product['shipping_price'];
                        if(isset($product_type) && $product_type && $product_type == 'variant'){
                            if (isset($product['variation_id']) && $product['variation_id'] != '' && $product['variation_id'] != 0) {
                                $variable_product = getvariableByProduct_id($product['variation_id']);
                                if ($product['on_sale'] == 1){
                                    $productPrice = $variable_product->sale_price * $quantity;
                                }else{
                                    $productPrice = $variable_product->price * $quantity;
                                }
                            }
                        }else{
                            if ($product['on_sale'] == 1){
                                $productPrice = $product['sale_price'] * $quantity;
                            }else{
                                $productPrice = $product['price'] * $quantity;
                            }
                        }
                        $productsTotal += $productPrice;
                        $orderAmount += $productPrice+$shippingPrice;
                        // for gtm start
                        $totalOrder += $productPrice+$shippingPrice;
                        $totalShipping +=  $shippingPrice;
                        $storeData =  getStoreInfoByIdCartApi($product['store_id']);
                        $category_id = getCategoriesByProductID($product['product_id']);
                        if(isset($category_id[0])){
                            $productCategory = getCategoryByCateId($category_id[0]);
                        }else{
                            $productCategory = null;
                        }
                   
                        $category = isset($productCategory) ? $productCategory['categoryTitle']: '';
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
                        $totalProductsQuantity += $product['product_quantity'];
                    
                    }
                    if(isset($couponCode) && !empty($couponCode)){
                        $discount = $this->discountsService->applyCoupon($couponCode, $storeId, $productsTotal, $totalProductsQuantity);
                        $discount['cartTotal'] = $discount['cartTotal'] + $totalShipping;
                    }
                    // strip fee is 2.9% + $3 
                    if (isset($orderAmount) && $orderAmount > 0 && !isset($discount['cartTotal'])){
                        $stripe_fee = ($orderAmount*(2.9/100)+0.33);
                    } else {
                        $stripe_fee = ($discount['cartTotal']*(2.9/100)+0.33);
                    }
                    // we need platform fee % on   ( orignal Order amount - stripe fee)
                    // $temp_amount = $orderAmount - $stripe_fee;

                    $storeCampaign = getCampaignsByStoreId($storeId);
                   
                    $totalActiveProducts = getTotalActiveProductsByStore($storeId);
                   
                    
                    if(isset($storeCampaign) && $totalActiveProducts >= 10){
                        $benifitsdata = json_decode($storeCampaign->benefits, true);
                        $platformfeeCheck = isset($benifitsdata['platform_fee']) ? $benifitsdata['platform_fee'] : false;

                        if(!$platformfeeCheck ){
                            $platform_fee = $benifitsdata['percentage'];
                        }
                    }else{
                       
                        if (isset($orderAmount) && $orderAmount > 0 && !isset($discount['cartTotal'])){
                        $platform_fee = $orderAmount * 0.045;
                        } else {
                            $platform_fee = $discount['cartTotal'] * 0.045;
                        }
                    }
                    
                    $total_fee = $stripe_fee +  $platform_fee;

                    if (isset($orderAmount) && $orderAmount > 0 && !isset($discount['cartTotal'])){
                        $payable_amount = $orderAmount - $total_fee;
                    } else {
                        $payable_amount = $discount['cartTotal'] - $total_fee;
                    }
                    
                    $paymentInfo = array(
                        "platform_fee" => $platform_fee,
                        "payable_amount" => $payable_amount,
                    );
                    $charges[$storeId] = $paymentInfo;
                    if (isset($orderAmount) && $orderAmount > 0 && !isset($discount['cartTotal'])){
                        $totalOrdersAmount += $orderAmount;
                    } else {
                        $totalOrdersAmount += $discount['cartTotal'];
                    }
                }
                // dd('end');
                $charge = Charge::create(array(
                    // "application_fee" => round($chargeable * 100),
                    "customer" => $customer->id,
                    "description" => $descreption,
                       "amount" => round($totalOrdersAmount * 100),
                       "currency" => "usd",
                       "source" => $card->id,
                       "transfer_group" => 'order002'
                   ));
                  if($charge->paid && $charge->status == 'succeeded'){
                    // charged successfully
                    
                    foreach($OrderData as $storeId => $store){
                        $charges[$storeId]['paymentId'] =  $charge->id;
                        $charges[$storeId]['balance_transaction'] =  $charge->balance_transaction;
                        $charges[$storeId]['payerID'] =  $charge->source->payerID;
                        $charges[$storeId]['charge_status'] =  'paid';
                        
                    }
                    $data =  json_decode($request->orderdata,true);

                    $output["useremail"] = $c_email;
                    $output["charges"] = $charges; 
                    $output["orderData"] = $data;
                    // dd($data);
                    $this->accessObj = new StoreOrdersAccess();
                    $result  = $this->accessObj->create_order($output);
                    if($result){
                        return response()->json([
                            'status' => true,
                            'message' => 'Thankyou for purchasing with Mallshark! Please check your email for the receipt(s) of this order.',
                            'data' => $products,
                            'transactionId' => $charge->id,
                            'total_amount' => round($totalOrder, 2),
                            'total_shipping' => $totalShipping,
                        ]);
                    }else{
                        return response()->json([
                            'status' => false,
                            'message' => 'Place order request not submitted successfully',
                            'data' => $products,
                            'transactionId' => $charge->id,
                            'total_amount' => round($totalOrder, 2),
                            'total_shipping' => $totalShipping,
                        ]);
                    }
                    

                  }else if(!$charge->paid && $charge->status = 'failed'){

                    return response()->json([
                        'status' => false,
                        'message' => 'Your payment was not successfull please try again ',
                    ]);
                
                  }

           
        } catch (\Throwable $th) {
                
               if($th->getMessage() && !empty( $th->getMessage())){
                return response()->json(['status'=>false,'message'=> $th->getMessage()]);
            }
            return response()->json(['status'=>false,'message'=> "Failed"]);
        }
        } else {
            echo 'Your token is empty';
        }
        die();
    
    }

    public function transfer($stripe_account, $amount) {
        Stripe::setApiKey($this->apiKey);

        $transfer = Transfer::create(array(
                    "amount" => $amount * 100,
                    "currency" => "cad",
                    "destination" => $stripe_account,
                        //"transfer_group" => "123fsddfs",
        ));
    }

    public function createCustomerView() {
        return View('stripe.create-customer')->render();
    }

    public function createCustomer(request $request) {
        Stripe::setApiKey($this->apiKey);
        $allData = $request->all();
        // print_rr($allData);
        // die();
//        $token = Token::create(array(
//                    "card" => array(
//                        "number" => "4242424242424242",
//                        "exp_month" => 4,
//                        "exp_year" => 2019,
//                        "cvc" => "314"
//                    )
//        ));
        // $token = $token->id;
        $token = $allData['stripeToken'];
        $planId = $allData['plan_id'];
        if ($token != '') {
            $userinfo = getUserById(getCurrentUserId());

            $userEmail = $userinfo[0]->email;

            $customer = Customer::create([
                        'email' => $userEmail,
                        'source' => $token,
            ]);
            $customer_id = $customer->id;
            $this->stripeObj->addStripeCustomerId($customer_id);
            $this->subscribeCustomerPlan($planId, $customer_id);
            Session::flash('msg', "Your subscription was successful!");
            return Redirect::back();
        }
    }

    public function subscribePlanToCustomer(Request $request) {
        $dataAll = $request->all();
        if (isset($dataAll['plan_id']) && isset($dataAll['customer_id'])) {
            $this->subscribeCustomerPlan($dataAll['plan_id'], $dataAll['customer_id']);
            Session::flash('msg', "Your subscription was successful!");
            return Redirect::back();
        }
    }

    public function createStripeProduct() {
        Stripe::setApiKey($this->apiKey);
        $product = Product::create([
                    'name' => 'Ability Aids subscription product',
                    'type' => 'service',
        ]);
        print_rr($product);
        die();
    }

    public function createStripePlan() {
        Stripe::setApiKey($this->apiKey);
        $plan = Plan::create([
                    'product' => $this->stripeProductId,
                    'nickname' => 'Monthally Plan for customers subscription',
                    'interval' => 'month',
                    'currency' => 'cad',
                    'amount' => 10000,
        ]);
        print_rr($plan);
        die();
    }

    public function retrieveAccountDetail($accountid) {
        if ($accountid != '') {
            Stripe::setApiKey($this->apiKey);
            $account_info = Account::retrieve($accountid);
            print_rr($account_info);
            die();
        }
    }

    public function getPlansByProductId($productId = '') {
        if ($productId == '') {
            $productId = $this->stripeProductId;
        }
        Stripe::setApiKey($this->apiKey);
        $planList = Plan::all([
                    'limit' => 10,
                    'product' => $productId,
        ]);
        $users = DB::table('stripe_accounts')->where('user_id', getCurrentUserId())->first();
        $customerId = '';
        if (isset($users->customer_id) && $users->customer_id != '') {
            $customerId = $users->customer_id;
        }
        $user_id = '';
        if (isset($users->user_id) && $users->user_id != '') {
            $user_id = $users->user_id;
        }
        //$this->getSubscriptionsByUserId();
        //die();
        return view('credits.store-subscription', array('data' => $planList->data, 'customer_id' => $customerId, 'user_id' => $user_id));
        //print_rr($planList);
    }

    public function subscribeCustomerPlan($planId = '', $customerID) {
        if ($planId == '') {
            $planId = $this->stripePlanId;
        }
        if ($customerID == '') {
            $customerID = 'cus_CfKbf2ytCeNs0r';
        }
        Stripe::setApiKey($this->apiKey);
        $subscription = Subscription::create([
                    'customer' => $customerID,
                    'items' => [['plan' => $planId]],
                    'billing' => 'send_invoice',
                    'days_until_due' => 3,
        ]);
        //print_rr($subscription);
        //die();
    }

    public function stripeEvents(Request $request) {
        // Stripe::setApiKey($this->apiKey);
        $eventType = $request->input('type');
        $data = $request->input('data');
        $allData = $request->all();
        $customers = array();
        //$customer_id = $data['object']['customer'];
        switch ($eventType) {
            case 'customer.subscription.deleted':
                $customers = array();
                foreach ($data['object'] as $key => $val) {
                    if ($key == 'customer') {
                        $customers[] = $val;
                    }
                }
                if (count($customers) > 0) {
                    foreach ($customers as $cus) {
                        //$user = DB::table('tripe_accounts')->where('customer_id', $cus)->select('user_id')->get()->toArray();
                        $user = DB::table('stripe_accounts')->select('user_id')->where('customer_id', $cus)->get()->toArray();
                        if (isset($user[0]->user_id)) {
                            $user_id = $user[0]->user_id;
                            $this->_storeobj->changeStoreStatus($user_id, '0');
                        }
                    }
                }
                break;
            case 'invoice.payment_failed':
                $customers = array();
                foreach ($data['object'] as $key => $val) {
                    if ($key == 'customer') {
                        $customers[] = $val;
                    }
                }
                break;
            case 'customer.subscription.trial_will_end':
                $customers = array();
                foreach ($data['object'] as $key => $val) {
                    if ($key == 'customer') {
                        $customers[] = $val;
                    }
                }
                break;
            case 'customer.subscription.updated':
                $customers = array();
                foreach ($data['object'] as $key => $val) {
                    if ($key == 'customer') {
                        $customers[] = $val;
                    }
                }
                break;

            default:
        }
        echo $eventType . ' list is given below';
        print_rr($customers);

        $input = @file_get_contents("php://input");
        $event_json = json_decode($input);

        http_response_code(200);
        die();
    }

    /*
     * Get user subscriptions by id
     */

    public function getSubscriptionsByUserId($userId = '') {
        if ($userId == '') {
            $userId = getCurrentUserId();
        }
        Stripe::setApiKey($this->apiKey);
        $users = DB::table('stripe_accounts')->where('user_id', $userId)->first();
        $customerId = '';
        if (isset($users->customer_id) && $users->customer_id != '') {
            $customerId = $users->customer_id;
        }
        if ($customerId == '') {
            return false;
        } else {

            $subscriptions = Subscription::all([
                        'customer' => $customerId,
                        'limit' => 1
            ]);
            //print_rr($subscriptions);
            $subscription_info = isset($subscriptions->data) ? $subscriptions->data : '';

            $info = array();
            if ($subscription_info != '') {
                foreach ($subscription_info as $val) {
                    $info['id'] = $subscriptions->id;
                    $info['status'] = $val->status;
                    $info['current_period_start'] = date("d M Y", $val->current_period_start);
                    $info['current_period_end'] = date("d M Y", $val->current_period_end);
                    $info['amount'] = date("d M Y", $val->plan->amount);
                    $info['name'] = $val->plan->nickname;
                }
                return $info;
            } else {
                return false;
            }
        }
    }

    public function getConnectedAccountByUserId($userId = '') {
        if ($userId == '') {
            $userId = getCurrentUserId();
        }

        $users = DB::table('stripe_accounts')->where('user_id', $userId)->first();

        $customerId = '';
        if (isset($users->user_id) && $users->user_id != '') {
            return $customerId = $users->user_id;
        }
        if ($customerId == '') {
            return false;
        }
    }

    public function getInvoicesCustomer() {
        $customer_id = '';
        if (Session::has('userData')) {
            $userId = Session::get('userData')['user_id'];
            if ($userId != '') {
                $query = DB::table('stripe_accounts')->select('customer_id')->where('user_id', $userId)->get()->toArray();
                $customer_id = isset($query[0]->customer_id) ? $query[0]->customer_id : 'cus_CntkkPeRaJ2Hxp';

                Stripe::setApiKey($this->apiKey);
                $invoices = Invoice::all([
                            'customer' => $customer_id,
                ]);
                $results = isset($invoices->data) ? $invoices->data : '';
                if (count($results) > 0) {
                    foreach ($results as $res) {
                        print_rr($res);
                        echo $res->amount_due / 100;
                        echo '<br />';
                    }
                }
            }
        }
    }

    public function stripeCall(){
        return view('checkout_dummy');
    }

    public function chargeManully(){
        try {
            Stripe::setApiKey(env('STRIPE_API_KEY'));
            // $stripe = new \Stripe\StripeClient(env('STRIPE_API_KEY'));
            $charge = Charge::create(array(
                    "description" => "Manully charge amount",
                    "amount" => 50 * 100,
                    "currency" => "usd",
                    "source" => 'tok_amex',
                    "transfer_group" => 'order002'
                ));
                echo 'Data: <pre>' .print_r($charge,true). '</pre>'; die();
        } catch (\Throwable $th) {
            echo  $th->getMessage();
        }
          
    }
}
