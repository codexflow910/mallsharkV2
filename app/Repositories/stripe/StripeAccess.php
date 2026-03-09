<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Repositories\stripe;

use DB;
use Session;
use App\BaseAccess;
use App\Models\Store;
use App\Models\Stripe;
use Illuminate\Support\Facades\URL;
use App\Http\Controllers\ElasticSearchController;
use App\Http\Controllers\MailgunController;
use App\Models\StoreOrder;
use Exception;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Illuminate\Support\Carbon;
use Stripe\Transfer;
use Stripe\Stripe as StripeLib;

/**
 * Description of Homeaccess
 *
 * @author Noman
 */
class StripeAccess extends BaseAccess
{

    private $stripeObj;
    private $store;
    private $_esearch;
    private $mailgun;
    public function __construct()
    {
        $this->stripeObj = new Stripe();
        $this->store = new Store();
        $this->_esearch = new ElasticSearchController();
        $this->mailgun = new MailgunController();
    }

    public function addStripeConnectedAccount($accountInfo = array())
    {
        if (count($accountInfo) > 0) {
            $this->stripeObj->user_id = getCurrentUserId();
            $this->stripeObj->access_token = $accountInfo['access_token'];
            $this->stripeObj->refresh_token = $accountInfo['refresh_token'];
            $this->stripeObj->stripe_publishable_key = $accountInfo['stripe_publishable_key'];
            $this->stripeObj->stripe_user_id = $accountInfo['stripe_user_id'];
            $this->stripeObj->save();
        }
    }

    public function addStripeCustomerId($customerID)
    {
        if ($customerID != '') {
            $this->stripeObj->where('user_id', '=', getCurrentUserId())->update(array('customer_id' => $customerID));
        }
    }



    public function getstoreData()
    {
        $userId = Session::get('userData')['user_id'];
        $storeData = getStoreDataByUserId($userId);
        $storeID =  !empty($storeData->store_id) ? $storeData->store_id : '';
        if (empty($storeID)) {
            $storeID = Request::segment(2);
        }
        if ($storeID) {
            $userData = $this->store->where('store_id', '=', $storeID)->first()->toArray();
            if (!empty($userData)) {
                return $userData;
            } else {
                return false;
            }
        }
    }

    // Connect Stripe Vendor Account 
    public function genrateStripeOnBoardLink()
    {

        $userId = Session::get('userData')['user_id'];
        $storeStatus = $this->store->select('stripe', 'account_id')->where('user_id', $userId)->first();
        if ($storeStatus->stripe == '0' || $storeStatus->stripe == 0) {
            // if($storeStatus->account_id == ''){ 
            $stripe = new \Stripe\StripeClient(env('STRIPE_API_KEY'));
            $account = $stripe->accounts->create([
                'type' => 'express',
                'country' => 'US',
            ]);
            $link = $stripe->accountLinks->create([
                'account' => $account->id,
                'refresh_url' => URL::to('/store-dashboard'),
                'return_url' => URL::to('/store/payments'),
                'type' => 'account_onboarding',
            ]);


            $this->store->where('user_id', $userId)->update(['account_id' => "$account->id", 'account_link' => "$link->url"]);
            return $link->url;
            // }
        }
    }

    public function genrateStripeOnBoardLinkApi()
    {

        $userId = auth()->user()->user_id;
        $storeStatus = $this->store->select('stripe', 'account_id')->where('user_id', $userId)->first();
        if ($storeStatus->stripe == '0' || $storeStatus->stripe == 0) {
            // if($storeStatus->account_id == ''){ 
            $stripe = new \Stripe\StripeClient(env('STRIPE_API_KEY'));
            $account = $stripe->accounts->create([
                'type' => 'express',
                'country' => 'US',
            ]);
            $link = $stripe->accountLinks->create([
                'account' => $account->id,
                'refresh_url' => env('STRIPE_ONBOARDING_REDIRECT'),
                'return_url' => env('STRIPE_ONBOARDING_REDIRECT'),
                'type' => 'account_onboarding',
            ]);
            $this->store->where('user_id', $userId)->update(['account_id' => "$account->id", 'account_link' => "$link->url"]);
            return $link->url;
            // }
        }
    }
    // Edit/Update Stripe Vendor Account 
    public function UpdateStripIdStore($account)
    {
        $userId = Session::get('userData')['user_id'];
        $stripe = new \Stripe\StripeClient(env('STRIPE_API_KEY'));
        $response = $stripe->accounts->retrieve(
            $account['account_id'],
            []
        );

        // echo '$link: <pre>' .print_r($link,true). '</pre>'; die();
        $details = $response->details_submitted;
        if ($details == '1') {

            $response =  $this->store->where('user_id', $userId)->update(['stripe' => "1"]);
            return $response;
        }
    }

    // Edit/Update Stripe Vendor Account 
    public function editStripeUser($account)
    {
        $userId = Session::get('userData')['user_id'];
        // dd(env('STRIPE_API_KEY'));
        $stripe = new \Stripe\StripeClient(env('STRIPE_API_KEY'));
        $response = $stripe->accounts->retrieve(
            $account['account_id'],
            []
        );
        // dd($response);
        $link = $stripe->accountLinks->create([
            'account' => $response->id,
            'refresh_url' => URL::to('/store/payments'),
            'return_url' => URL::to('/store/payments'),
            'type' => 'account_onboarding',
        ]);
        // echo '$link: <pre>' .print_r($link,true). '</pre>'; die();
        $details = $response->details_submitted;
        if ($details == '1') {

            $this->store->where('user_id', $userId)->update(['stripe' => "1"]);
        }
        return $link->url;
    }
    public function editStripeUserApi($account)
    {
        // $userId = Session::get('userData')['user_id'];
        $userId = auth()->user()->user_id;
        $stripe = new \Stripe\StripeClient(env('STRIPE_API_KEY'));
        // return response()->json($stripe);
        $response = $stripe->accounts->retrieve(
            $account,
            []
        );
      

        $link = $stripe->accountLinks->create([
            'account' => $response->id,
            'refresh_url' => env('STRIPE_ONBOARDING_REDIRECT'),
            'return_url' => env('STRIPE_ONBOARDING_REDIRECT'),
            'type' => 'account_onboarding',
        ]);
        // echo '$link: <pre>' .print_r($link,true). '</pre>'; die();
        $details = $response->details_submitted;

        if ($details == '1') {

            $this->store->where('user_id', $userId)->update(['stripe' => "1"]);
        }
        return $link->url;
    }
    public function changeStripeStatus($status)
    {
        $userId = Session::get('userData')['user_id'];
        $stripe_status =  $status['stripe_status'];
        $account_id =  $status['account_id'];
        $storeStatus = $this->store->select('stripe', 'store_id', 'store_status')->where('user_id', $userId)->first();
        // dd($storeStatus->stripe);
        if ($storeStatus->stripe == '0' || $storeStatus->stripe == 0) {

            $stripe = new \Stripe\StripeClient(env('STRIPE_API_KEY'));
            $response = $stripe->accounts->retrieve(
                $account_id,
                []
            );
            $details = $response->details_submitted;
         
            if ($details) {
                $stripe_status =  $status['stripe_status'];
                $this->store->where('user_id', $userId)->update(['stripe' => "$stripe_status"]);
            }
            return $details;
        } elseif ($storeStatus->stripe == '1' || $storeStatus->stripe == 1) {

            $status = '0';
            $store_status = $storeStatus->store_status; 
            if ($storeStatus) {
                $storeId = $storeStatus->store_id;
            }
            // dd($store_status);
            DB::table('products')->where('user_id', $userId)->whereNotIn('status', ['2', '3', '5'])->update(['status' => '0']);
            $products = DB::table('products')->select('product_id')->where('user_id', $userId)->get()->toArray();

            $this->store->where('user_id', $userId)->update(['stripe' => "$status", 'store_status' => $status]);
        }
    }
    public function changeStripeStatusApi($status)
    {
        // $userId = Session::get('userData')['user_id'];
        $userId = auth()->user()->user_id;
        $stripe_status =  $status['stripe_status'];
        $account_id =  $status['account_id'];
        $storeStatus = $this->store->select('stripe', 'store_id', 'store_status')->where('user_id', $userId)->first();
        // dd($storeStatus->stripe);
        if ($storeStatus->stripe == '0' || $storeStatus->stripe == 0) {

            $stripe = new \Stripe\StripeClient(env('STRIPE_API_KEY'));
            $response = $stripe->accounts->retrieve(
                $account_id,
                []
            );
            $details = $response->details_submitted;
         
            if ($details) {
                $stripe_status =  $status['stripe_status'];
                $this->store->where('user_id', $userId)->update(['stripe' => "$stripe_status"]);
            }
            $store = $this->store->where('account_id',$account_id)->first();
            return $store;
        } elseif ($storeStatus->stripe == '1' || $storeStatus->stripe == 1) {

            $status = '0';
            $store_status = $storeStatus->store_status; 
            if ($storeStatus) {
                $storeId = $storeStatus->store_id;
            }
            // dd($store_status);
            DB::table('products')->where('user_id', $userId)->whereNotIn('status', ['2', '3', '5'])->update(['status' => '0']);
            $products = DB::table('products')->select('product_id')->where('user_id', $userId)->get()->toArray();

            $this->store->where('user_id', $userId)->update(['stripe' => "$status", 'store_status' => $status]);
            $store = $this->store->where('account_id',$account_id)->first();
            return $store;
        }
    }
    public function changeStripeStatusOnLoad($status)
    {
        $userId = Session::get('userData')['user_id'];
        $stripe_status =  1;
    
        $account_id =  $status['accountId'];
        $storeStatus = $this->store->select('stripe', 'store_id', 'stripe_connected')->where('user_id', $userId)->first();
        if ( $storeStatus->stripe == 0 && $storeStatus->stripe_connected == 0) {

            $stripe = new \Stripe\StripeClient(env('STRIPE_API_KEY'));
            $response = $stripe->accounts->retrieve(
                $account_id,
                []
            );
            $details = $response->details_submitted;
         
            if ($details) {
     
                $this->store->where('user_id', $userId)->update(['stripe' => "$stripe_status", "stripe_connected" => "1"]);
            }
            return $details;
        } 
    }

    public function changeStripeStatusOnLoadApi($status)
    {
        $userId = auth()->user()->user_id;
        $stripe_status =  1;
    
        $account_id =  $status['account_id'];
        $storeStatus = $this->store->select('stripe', 'store_id', 'stripe_connected')->where('user_id', $userId)->first();
        if ( $storeStatus->stripe == 0 && $storeStatus->stripe_connected == 0) {

            $stripe = new \Stripe\StripeClient(env('STRIPE_API_KEY'));
            
            $response = $stripe->accounts->retrieve(
                $account_id,
                []
            );
            $details = $response->details_submitted;
           
            if ($details) {
     
                $this->store->where('user_id', $userId)->update(['stripe' => "$stripe_status", "stripe_connected" => "1"]);
                $storeStatus = $this->store->select('stripe', 'store_id', 'stripe_connected')->where('user_id', $userId)->first();
            }
        } 
        return $storeStatus;
    }
    public function transferVendorPayments()
    {
        $paymentLog = new Logger('');
        $paymentLog->pushHandler(new StreamHandler(storage_path('logs/sitemap.log')), Logger::INFO);
        $paymentLog->info('transfer', ['created_at' => Carbon::now()]);

        try {
            $escrowDays = env('ESCROW_DAYS', 15);
            $escrowMins = env('ESCROW_MINS', 5);
            $escrowTest = env('ESCROW_TEST', false);
            if (!$escrowTest) {

                // prod mode
                $escrowDate = Carbon::now()->subDay($escrowDays)->format('Y-m-d H:i:s');
                $shippedOrders = StoreOrder::where(function ($query) use ($escrowDate) {
                    // First condition: order_status is 'shipped', shipped_date is less than or equal to $escrowDate, and payment_status is either 'pending' or 'failed'
                    $query->where('order_status', 'shipped')
                          ->where('charge_status','paid')
                          ->where('shipped_date', '<=', $escrowDate)
                          ->where(function ($query) {
                              $query->where('payment_status', 'pending')
                                    ->orWhere('payment_status', 'failed');
                          });
                    
                    // Second condition: order_status is 'completed', virtual is true, and payment_status is either 'pending' or 'failed'
                    $query->orWhere(function ($query) {
                        $query->where('order_status', 'completed')
                              ->where('charge_status','paid')
                              ->where('virtual', true)
                              ->where(function ($query) {
                                  $query->where('payment_status', 'pending')
                                        ->orWhere('payment_status', 'failed');
                              });
                    });
                })->get();

                // // --------------------Above code replaced this because added check for whole virtual orders (directly completed)-----------------------------------------------
                // $shippedOrders = StoreOrder::where('order_status', 'shipped')
                //     ->where('shipped_date', '<=', $escrowDate)
                //     ->where(function ($query) {
                //         $query->where('payment_status', 'pending')->orWhere('payment_status', 'failed');
                //     })->get();


                StripeLib::setApiKey(env('STRIPE_API_KEY'));

                if (count($shippedOrders) > 0) {
                    $shippedOrders->each(function ($order) {
                        if ($order->store()->exists()) {
                            $vendor_stripe_acc = $order->store->account_id;
                            $vendor_email = null;
                            $store_name = !empty($order->store->store_name) ? $order->store->store_name : '';
                            $user_name = !empty($order->user->name) ? $order->user->name : '';
                            if ($order->store->user()->exists()) {
                                $vendor_email = !empty($order->store->user->email) ? $order->store->user->email : null;
                            }
                            if (!empty(trim($vendor_stripe_acc)) && $vendor_stripe_acc) {

                                $payable_amount = $order->payable_amount;
                                // $payable_amount = 99999999;
                                $group = $order->order_id;
                                try {

                                    $charge = Transfer::create(array(

                                        'amount' => $payable_amount * 100,
                                        'currency' => 'usd',
                                        'destination' => $vendor_stripe_acc,
                                        'transfer_group' => $group,

                                    ));
                                    if ($charge) {
                                        $order->payment_status = 'paid';
                                        $order->order_status = 'completed';
                                        $order->save();
                                        $paymentLog = new Logger('');
                                        $paymentLog->pushHandler(new StreamHandler(storage_path('logs/vendor_payments.log')), Logger::INFO);
                                        $paymentLog->info('Paid', ['payable_amount' => $payable_amount, 'payment_date' => Carbon::now()]);
                                    }else{

                                        $order->payment_status = 'failed';
                                        $order->save();
                                    }
                                } catch (Exception $e) {
                                    // $order->payment_status = 'failed';
                                    // $order->save();
                                    $paymentLog = new Logger('');
                                    $paymentLog->pushHandler(new StreamHandler(storage_path('logs/vendor_payments.log')), Logger::ERROR);
                                    $paymentLog->error('Failed', ['payable_amount' => $payable_amount, 'payment_date' => Carbon::now(), 'Message' => $e->getMessage()]);
                                }

                                try{

                                    if ($vendor_email) {
                                        $subject = 'Payments Recevied from MallShark';
                                        $mail_template = view('mail.vendor_payment', compact('user_name', 'payable_amount'));
                                        $params = [
                                            'from'    => 'MallShark  ' . env('SUPPORT_EMAIL'),
                                            'to'      => $vendor_email,
                                            'subject' => $subject,
                                            'html'    => "$mail_template",
                                        ];

                                        $results = $this->mailgun->SendMailgunMail($params);
                                    }
                                }
                                catch(Exception $e){
                                    
                                }
                            }
                        }
                    });
                }
            } else {
                // test mode

                $escrowDate = Carbon::now()->subMinutes($escrowMins)->format('Y-m-d H:i:s');
                $shippedOrders = StoreOrder::where(function ($query) use ($escrowDate) {
                    // First condition: order_status is 'shipped', shipped_date is less than or equal to $escrowDate, and payment_status is either 'pending' or 'failed'
                    $query->where('order_status', 'shipped')
                          ->where('charge_status','paid')
                          ->where('shipped_date', '<=', $escrowDate)
                          ->where(function ($query) {
                              $query->where('payment_status', 'pending')
                                    ->orWhere('payment_status', 'failed');
                          });
                    
                    // Second condition: order_status is 'completed', virtual is true, and payment_status is either 'pending' or 'failed'
                    $query->orWhere(function ($query) {
                        $query->where('order_status', 'completed')
                              ->where('charge_status','paid')
                              ->where('virtual', true)
                              ->where(function ($query) {
                                  $query->where('payment_status', 'pending')
                                        ->orWhere('payment_status', 'failed');
                              });
                    });
                })->get();
                
                // // --------------------Above code replaced this because added check for whole virtual orders (directly completed)-----------------------------------------------
                // $shippedOrders = StoreOrder::where('order_status', 'shipped')
                //     ->where('shipped_date', '<=', $escrowDate)
                //     ->where(function ($query) {
                //         $query->where('payment_status', 'pending')->orWhere('payment_status', 'failed');
                //     })->get();

                StripeLib::setApiKey(env('STRIPE_API_KEY'));

                if (count($shippedOrders) > 0) {
                    $shippedOrders->each(function ($order) {
                        if ($order->store()->exists()) {
                            $vendor_stripe_acc = $order->store->account_id;
                            $vendor_email = null;
                            $store_name = !empty($order->store->user->name) ? $order->store->user->name : '';
                            if ($order->store->user()->exists()) {
                                $vendor_email = !empty($order->store->user->email) ? $order->store->user->email : null;
                                // echo 'Data: <pre>' .print_r($vendor_email,true). '</pre>'; die();
                            }
                            if (!empty(trim($vendor_stripe_acc)) && $vendor_stripe_acc) {

                                $payable_amount = $order->payable_amount;
                                // $payable_amount = 99999999;
                                $group = $order->order_id;
                                try {

                                    $charge = Transfer::create(array(

                                        'amount' => $payable_amount * 100,
                                        'currency' => 'usd',
                                        'destination' => $vendor_stripe_acc,
                                        'transfer_group' => $group,

                                    ));
                                    if ($charge) {
                                        $order->payment_status = 'paid';
                                        $order->order_status = 'completed';
                                        $order->save();
                                        $paymentLog = new Logger('');
                                        $paymentLog->pushHandler(new StreamHandler(storage_path('logs/test_vendor_payments.log')), Logger::INFO);
                                        $paymentLog->info('Paid', ['payable_amount' => $payable_amount, 'payment_date' => Carbon::now()]);
                                        if ($vendor_email) {
                                            try {

                                                $subject = 'Payments Recevied from MallShark';
                                                $mail_template = view('mail.vendor_payment', compact('store_name', 'payable_amount'));
                                                $params = [
                                                    'from'    => 'MallShark  ' . env('SUPPORT_EMAIL'),
                                                    'to'      => $vendor_email,
                                                    'subject' => $subject,
                                                    'html'    => "$mail_template",
                                                ];

                                                $this->mailgun->SendMailgunMail($params);
                                            } catch (\Throwable $th) {
                                                echo $th->getMessage();
                                            }
                                        }
                                    }
                                } catch (Exception $e) {
                                    $order->payment_status = 'failed';
                                    $order->save();
                                    $paymentLog = new Logger('');
                                    $paymentLog->pushHandler(new StreamHandler(storage_path('logs/test_vendor_payments.log')), Logger::ERROR);
                                    $paymentLog->error('Failed', ['payable_amount' => $payable_amount, 'payment_date' => Carbon::now(), 'Message' => $e->getMessage()]);
                                }
                            }
                        }
                    });
                }
            }
        } catch (Exception $e) {
            // dd($e);
        }
    }
}
