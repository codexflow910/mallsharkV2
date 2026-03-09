<?php

namespace Packages\Admin\Repositories\Eloquent\V3;

use Exception;
use Illuminate\Support\ItemNotFoundException;
use Packages\Admin\Contracts\V3\AdminRepositoryInterface;
use Packages\Admin\Models\V3\CategoryTag;
use Stripe\Transfer;
use Stripe\Stripe as StripeLib;
use Monolog\Logger;
use Illuminate\Support\Facades\DB;
use Packages\Admin\Models\V3\StoreDeduction;
use Monolog\Handler\StreamHandler;
use Carbon\Carbon;
use App\Models\StoreOrder;
use App\Http\Controllers\MailgunController;

class AdminRepository implements AdminRepositoryInterface
{
    
    protected $model;
    private $mailgun;

    public function __construct(CategoryTag $categoryTag)
    {
        $this->model = $categoryTag;
        $this->mailgun = new MailgunController();
    }

    public function getCategoryTags($id) 
    { 
        $tags = $this->model->where('category_id', $id)->get();
        $count = $tags->count();
    
        return response()->json([
            'tags' => $tags,
            'count' => $count,
        ]);
    }
    
    public function getTag($name, $categoryId) 
    {
        return $this->model->where('category_id', $categoryId)
            ->whereRaw('LOWER(name) = ?', [strtolower($name)])
            ->first();
    }

    public function createTag(array $data) 
    {
        return $this->model->create($data);
    }

    public function deleteTag($id) 
    {
        $tag = $this->model->find($id);
        
        if (!$tag) {
            throw new ItemNotFoundException('Tag not found');
        }

        return $tag->delete();
    }
    public function getByStoreId($storeId)
    {
        try{
        return StoreDeduction::where('store_id', $storeId)->first();
      }  catch (Exception $e) {
            throw $e;
        }
    }

    public function create(array $data)
    {try{
        return StoreDeduction::create($data);
     } catch (Exception $e) {
            throw $e;
        }
    }

    public function update(StoreDeduction $deduction, array $data)
    {
        try{
        $deduction->update($data);
        return $deduction;
     } catch (Exception $e) {
            throw $e;
        }
    }
    public function getorderstransferVendorPayment($store_id)
    {
        try {
           
            $paymentLog = new Logger('');
            $paymentLog->pushHandler(new StreamHandler(storage_path('logs/sitemap.log')), Logger::INFO);
            $paymentLog->info('transfer', ['created_at' => Carbon::now()]);

            $escrowDays = env('ESCROW_DAYS', 15);
            $escrowMins = env('ESCROW_MINS', 5);
            $escrowTest = env('ESCROW_TEST', false);
            // admin operation mode
            // 1. Calculate escrowDate
            if(env('APP_ENV') == 'development'){
                $escrowDate = Carbon::now()->subMinutes($escrowMins)->format('Y-m-d H:i:s');
            }else{
                $escrowDate = Carbon::now()->subDays($escrowDays)->format('Y-m-d H:i:s');
            }
            // 2. Fetch all shipped orders for a specific store within the escrowDate window
            $shippedOrders = StoreOrder::where('store_id', $store_id)
                ->where('order_status', 'shipped')
                ->where('charge_status', 'paid')
                ->where('shipped_date', '<=', $escrowDate)
                ->where(function ($query) {
                    $query->where('payment_status', 'pending')
                        ->orWhere('payment_status', 'failed');
                })
                ->get();

            if ($shippedOrders->count() < 1) {
                return 0;
            }
            if ($shippedOrders->count() > 0) {

                $store = $shippedOrders->first()->store;
                $s_id = $store->store_id;
                $deduction = DB::table('store_deduction')->where('store_id', $s_id)->first();

                $totalPayable = 0;
                $processedOrders = [];
                //  Calculate  deduction payments
                foreach ($shippedOrders as $order) {
                    $orderAmount = $order->total_amount;

                    // Apply store-specific deduction if exists
                    if ($deduction) {
                        $mallSharkAmount = $deduction->mall_shark_amount;
                        $shippingCheck = $deduction->shipping_check;
                        $adsCheck = $deduction->ads_check;
                        $adAmount = $deduction->ad_amount;

                        $stripeFee = ($orderAmount * 0.029) + 0.33;
                        $platformFee = $orderAmount * ($mallSharkAmount / 100);
                        // Deduct shipping only if shippingCheck is true (1)
                        $shippingFee = $shippingCheck ? ($order->shipping_amount) : 0;


                        $platformFee += $stripeFee;
                        
                        $platformFee += $shippingFee;
                        // Optionally add ad fee if needed
                        // if ($adsCheck && $adAmount > 0) {
                        //     $platformFee += $adAmount;
                        // }
                    } else {
                        // Default deductions
                        $stripeFee = ($orderAmount * 0.029) + 0.33;
                        $platformFee = $orderAmount * env('MALLSHARK_DEDUCTION');   //0.045;
                        $platformFee += $stripeFee;
                    }

                    // Final payable OF THAT ORDER
                    $payableAmount = round($orderAmount - $platformFee, 2);

                    $processedOrders[] = [
                        'order_id'        => $order['order_id'],
                        'buyer_id'        => $order['buyer_id'],
                        'store_id'        => $order['store_id'],
                        'payment_method'  => $order['payment_method'],
                        'total_amount'    => $order['total_amount'],
                        'payable_amount'  => $payableAmount,
                        'charge_status'   => $order['charge_status'],
                        'payment_status'  => $order['payment_status'],
                        'shipped_date'    => $order['shipped_date'],
                        'order_status'    => $order['order_status'],
                        'shipping_amount' => $order['shipping_amount'],
                        'tracking_id'     => $order['tracking_id'],
                        'created_at'      => $order['created_at'],
                    ];



                    $totalPayable += $payableAmount;
                }


                return [
                    'totalPayable'    => $totalPayable,
                    'processedOrders' => $processedOrders,
                ];
            }

            return 0;
            // 4. Send payment to vendor’s Stripe account (once for the whole store)
        } catch (Exception $e) {
            throw $e;
        }
    }
    public function transferOrderVendorPayment($store_id, $amount, $orderIds)
    {
      
        $paymentLog = new Logger('');
        $paymentLog->pushHandler(new StreamHandler(storage_path('logs/sitemap.log')), Logger::INFO);
        $paymentLog->info('transfer', ['created_at' => Carbon::now()]);

        try {
            $escrowDays = env('ESCROW_DAYS', 15);
            $escrowMins = env('ESCROW_MINS', 5);
            $escrowTest = env('ESCROW_TEST', false);

            if ($escrowTest) {

                // admin operation mode
                // 1. Calculate escrowDate
                if(env('APP_ENV') == 'development'){
                    $escrowDate = Carbon::now()->subMinutes($escrowMins)->format('Y-m-d H:i:s');
                }else{
                    $escrowDate = Carbon::now()->subDays($escrowDays)->format('Y-m-d H:i:s');
                }
                // 2. Fetch all shipped orders for a specific store within the escrowDate window
                $shippedOrders = StoreOrder::where('store_id', $store_id)
                    ->where('order_status', 'shipped')
                    ->where('charge_status', 'paid')
                    ->where('shipped_date', '<=', $escrowDate)
                    ->where(function ($query) {
                        $query->where('payment_status', 'pending')
                            ->orWhere('payment_status', 'failed');
                    })
                    ->whereIn('order_id', $orderIds) // <-- Only include provided orderIds
                    ->get();



                if ($shippedOrders->count() < 1) {
                    return false;
                }


                // 4. Send payment to vendor’s Stripe account (once for the whole store)
                StripeLib::setApiKey(env('STRIPE_API_KEY'));

                if ($shippedOrders->count() > 0) {
                    $store = $shippedOrders->first()->store;

                    if ($store && $store->user && !empty($store->account_id)) {

                        $vendor_stripe_acc = $store->account_id;
                        $vendor_email = $store->user->email;
                        $store_name = $store->user->name ?? 'Sir';
                        $payable_amount = $amount;
                        $group = 'store_' . $store->store_id . '_bulk_' . time(); // Group identifier for Stripe transfer  
                        try {
                            $charge = Transfer::create([
                                'amount' => $amount * 100, // Stripe uses cents
                                'currency' => 'usd',
                                'destination' => $vendor_stripe_acc,
                                'transfer_group' => $group,
                            ]);

                            if ($charge) {

                                // 2. Get and update all matching shipped orders for a specific store
                                $affectedRows = StoreOrder::where('store_id', $store_id)
                                    ->where('order_status', 'shipped')
                                    ->where('charge_status', 'paid')
                                    ->where('shipped_date', '<=', $escrowDate)
                                    ->where(function ($query) {
                                        $query->where('payment_status', 'pending')
                                            ->orWhere('payment_status', 'failed');
                                    })
                                    ->whereIn('order_id', $orderIds) // Filter by provided orderIds
                                    ->update([
                                        'payment_status' => 'paid',
                                        'order_status' => 'completed',
                                    ]);

                                // Log the payment
                                $paymentLog = new Logger('vendor_payment');
                                $paymentLog->pushHandler(new StreamHandler(storage_path('logs/test_vendor_payments.log')), Logger::INFO);
                                $paymentLog->info('Bulk Order Paid', [
                                    'store_id' => $store->store_id,
                                    'payable_amount' => $amount,
                                    'payment_date' => Carbon::now(),
                                    'order_ids' => $orderIds, // Add this line
                                ]);

                                // Send payment confirmation email
                                if ($vendor_email) {
                                    try {

                                        $subject = 'Payments Recevied from MallShark';
                                        $mail_template = view('mail.vender-order-payment', compact('store_name', 'payable_amount', 'orderIds'));
                                        $params = [
                                            'from'    => 'MallShark  ' . env('SUPPORT_EMAIL'),
                                            'to'      => $vendor_email,
                                            'subject' => $subject,
                                            'html'    => "$mail_template",
                                        ];

                                        $this->mailgun->SendMailgunMail($params);


                                        return $amount;
                                    } catch (Exception $e) {
                                        throw $e;
                                    }
                                }
                            }
                        } catch (Exception $e) {
                            $affectedRows = StoreOrder::where('store_id', $store_id)
                                ->where('order_status', 'shipped')
                                ->where('charge_status', 'paid')
                                ->where('shipped_date', '<=', $escrowDate)
                                ->where(function ($query) {
                                    $query->where('payment_status', 'pending')
                                        ->orWhere('payment_status', 'failed');
                                })
                                ->whereIn('order_id', $orderIds) // Only update specified orderIds
                                ->update([
                                    'payment_status' => 'failed',
                                ]);

                            $paymentLog = new Logger('');
                            $paymentLog->pushHandler(new StreamHandler(storage_path('logs/test_vendor_payments.log')), Logger::ERROR);
                            $paymentLog->error('Failed', [
                                'payable_amount' => $payable_amount,
                                'payment_date' => Carbon::now(),
                                'order_ids' => $orderIds,
                                'message' => $e->getMessage(),
                            ]);

                            $subject = 'Payments Failed from MallShark';
                            $mail_template = view('mail.vender-order-failure-payment', compact('store_name', 'payable_amount', 'orderIds'));
                            $params = [
                                'from'    => 'MallShark  ' . env('SUPPORT_EMAIL'),
                                'to'      => "abdullah.jutt@mindblaze.biz",
                                'cc'      => 'sajidoon@mindblazetech.com',
                                'subject' => $subject,
                                'html'    => "$mail_template",
                            ];

                            $this->mailgun->SendMailgunMail($params);
                        }
                    }
                }
            } else {
              
                // admin operation mode
                // 1. Calculate escrowDate
                if(env('APP_ENV') == 'development'){
                    $escrowDate = Carbon::now()->subMinutes($escrowMins)->format('Y-m-d H:i:s');
                }else{
                    $escrowDate = Carbon::now()->subMinutes($escrowDays)->format('Y-m-d H:i:s');
                }
                // 2. Fetch all shipped orders for a specific store within the escrowDate window
                $shippedOrders = StoreOrder::where('store_id', $store_id)
                    ->where('order_status', 'shipped')
                    ->where('charge_status', 'paid')
                    ->where('shipped_date', '<=', $escrowDate)
                    ->where(function ($query) {
                        $query->where('payment_status', 'pending')
                            ->orWhere('payment_status', 'failed');
                    })
                    ->whereIn('order_id', $orderIds) // <-- Only include provided orderIds
                    ->get();



                if ($shippedOrders->count() < 1) {
                    return false;
                }


                // 4. Send payment to vendor’s Stripe account (once for the whole store)
                StripeLib::setApiKey(env('STRIPE_API_KEY'));

                if ($shippedOrders->count() > 0) {
                    $store = $shippedOrders->first()->store;

                    if ($store && $store->user && !empty($store->account_id)) {

                        $vendor_stripe_acc = $store->account_id;
                        $vendor_email = $store->user->email;
                        $store_name = $store->user->name ?? 'Sir';
                       
                        $payable_amount = $amount;
                        $group = 'store_' . $store->store_id . '_bulk_' . time(); // Group identifier for Stripe transfer  
                        try {
                            $charge = Transfer::create([
                                'amount' => $amount * 100, // Stripe uses cents
                                'currency' => 'usd',
                                'destination' => $vendor_stripe_acc,
                                'transfer_group' => $group,
                            ]);

                            if ($charge) {

                                // 2. Get and update all matching shipped orders for a specific store
                                $affectedRows = StoreOrder::where('store_id', $store_id)
                                    ->where('order_status', 'shipped')
                                    ->where('charge_status', 'paid')
                                    ->where('shipped_date', '<=', $escrowDate)
                                    ->where(function ($query) {
                                        $query->where('payment_status', 'pending')
                                            ->orWhere('payment_status', 'failed');
                                    })
                                    ->whereIn('order_id', $orderIds) // Filter by provided orderIds
                                    ->update([
                                        'payment_status' => 'paid',
                                        'order_status' => 'completed',
                                    ]);

                                // Log the payment
                                $paymentLog = new Logger('vendor_payment');
                                $paymentLog->pushHandler(new StreamHandler(storage_path('logs/test_vendor_payments.log')), Logger::INFO);
                                $paymentLog->info('Bulk Order Paid', [
                                    'store_id' => $store->store_id,
                                    'payable_amount' => $amount,
                                    'payment_date' => Carbon::now(),
                                    'order_ids' => $orderIds, // Add this line
                                ]);

                                // Send payment confirmation email
                                if ($vendor_email) {
                                    try {

                                        $subject = 'Payments Recevied from MallShark';
                                        $mail_template = view('mail.vender-order-payment', compact('store_name', 'payable_amount', 'orderIds'));
                                        $params = [
                                            'from'    => 'MallShark  ' . env('SUPPORT_EMAIL'),
                                            'to'      => $vendor_email,
                                            'subject' => $subject,
                                            'html'    => "$mail_template",
                                        ];
                                      
                                        $this->mailgun->SendMailgunMail($params);
                                       

                                        return $amount;
                                    } catch (Exception $e) {

                                        throw $e;
                                    }
                                }
                            }
                        } catch (Exception $e) {
                            $affectedRows = StoreOrder::where('store_id', $store_id)
                                ->where('order_status', 'shipped')
                                ->where('charge_status', 'paid')
                                ->where('shipped_date', '<=', $escrowDate)
                                ->where(function ($query) {
                                    $query->where('payment_status', 'pending')
                                        ->orWhere('payment_status', 'failed');
                                })
                                ->whereIn('order_id', $orderIds) // Only update specified orderIds
                                ->update([
                                    'payment_status' => 'failed',
                                ]);

                            $paymentLog = new Logger('');
                            $paymentLog->pushHandler(new StreamHandler(storage_path('logs/test_vendor_payments.log')), Logger::ERROR);
                            $paymentLog->error('Failed', [
                                'payable_amount' => $payable_amount,
                                'payment_date' => Carbon::now(),
                                'order_ids' => $orderIds,
                                'message' => $e->getMessage(),
                            ]);

                            $subject = 'Payments Failed from MallShark';
                            $mail_template = view('mail.vender-order-failure-payment', compact('store_name', 'payable_amount','orderIds'));
                            $params = [
                                'from'    => 'MallShark  ' . env('SUPPORT_EMAIL'),
                                'to'      => "abdullah.jutt@mindblaze.biz",
                                'cc'      => 'sajidoon@mindblazetech.com', 
                                'subject' => $subject,
                                'html'    => "$mail_template",
                            ];

                            $this->mailgun->SendMailgunMail($params);
                        }
                    }
                }
            }
        } catch (Exception $e) {
            throw $e;
        }
    }



    public function gettransferVendorPayment($store_id)
    {
        try {
            $paymentLog = new Logger('');
            $paymentLog->pushHandler(new StreamHandler(storage_path('logs/sitemap.log')), Logger::INFO);
            $paymentLog->info('transfer', ['created_at' => Carbon::now()]);

            $escrowDays = env('ESCROW_DAYS', 15);
            $escrowMins = env('ESCROW_MINS', 5);
            $escrowTest = env('ESCROW_TEST', false);
            // admin operation mode
            // 1. Calculate escrowDate
           if(env('APP_ENV') == 'development'){
                $escrowDate = Carbon::now()->subMinutes($escrowMins)->format('Y-m-d H:i:s');
            }else{
                $escrowDate = Carbon::now()->subMinutes($escrowDays)->format('Y-m-d H:i:s');
            }
            // 2. Fetch all shipped orders for a specific store within the escrowDate window
            $shippedOrders = StoreOrder::where('store_id', $store_id)
                ->where('order_status', 'shipped')
                ->where('charge_status', 'paid')
                ->where('shipped_date', '<=', $escrowDate)
                ->where(function ($query) {
                    $query->where('payment_status', 'pending')
                        ->orWhere('payment_status', 'failed');
                })
                ->get();

            if ($shippedOrders->count() < 1) {
                return 0;
            }

            if ($shippedOrders->count() > 0) {

                $store = $shippedOrders->first()->store;
                $s_id = $store->store_id;
                $deduction = DB::table('store_deduction')->where('store_id', $s_id)->first();

                $totalPayable = 0;
                //  Calculate  deduction payments
                foreach ($shippedOrders as $order) {

                    $orderAmount = $order->total_amount;

                    // Apply store-specific deduction if exists
                    if ($deduction) {
                        $mallSharkAmount = $deduction->mall_shark_amount;
                        $shippingCheck = $deduction->shipping_check;
                        $adsCheck = $deduction->ads_check;
                        $adAmount = $deduction->ad_amount;

                        $stripeFee = ($orderAmount * 0.029) + 0.33;
                        $platformFee = $orderAmount * ($mallSharkAmount / 100);
                        // Deduct shipping only if shippingCheck is true (1)
                        $shippingFee = $shippingCheck ? ($order->shipping_amount) : 0;


                        $platformFee += $stripeFee;
                        $platformFee += $shippingFee;
                        // Optionally add ad fee if needed
                        // if ($adsCheck && $adAmount > 0) {
                        //     $platformFee += $adAmount;
                        // }
                    } else {
                        // Default deductions
                        $stripeFee = ($orderAmount * 0.029) + 0.33;
                        $platformFee = $orderAmount * 0.045;
                        $platformFee += $stripeFee;
                    }

                    // Final payable OF THAT ORDER
                    $payableAmount = round($orderAmount - $platformFee, 2);

                    $totalPayable += $payableAmount;
                }
                return $totalPayable;
            }

            return 0;
            // 4. Send payment to vendor’s Stripe account (once for the whole store)
        } catch (Exception $e) {
            throw $e;
        }
    }
    public function transferVendorPayment($store_id, $amount)
    {
        $paymentLog = new Logger('');
        $paymentLog->pushHandler(new StreamHandler(storage_path('logs/sitemap.log')), Logger::INFO);
        $paymentLog->info('transfer', ['created_at' => Carbon::now()]);

        try {
            $escrowDays = env('ESCROW_DAYS', 15);
            $escrowMins = env('ESCROW_MINS', 5);
            $escrowTest = env('ESCROW_TEST', false);
            $flag = 0;
            if ($flag == 1) {

                // admin operation mode
                // 1. Calculate escrowDate
                if(env('APP_ENV') == 'development'){
                    $escrowDate = Carbon::now()->subMinutes($escrowMins)->format('Y-m-d H:i:s');
                }else{
                    $escrowDate = Carbon::now()->subMinutes($escrowDays)->format('Y-m-d H:i:s');
                }
                // 2. Fetch all shipped orders for a specific store within the escrowDate window
                $shippedOrders = StoreOrder::where('store_id', $store_id)
                    ->where('order_status', 'shipped')
                    ->where('charge_status', 'paid')
                    ->where('shipped_date', '<=', $escrowDate)
                    ->where(function ($query) {
                        $query->where('payment_status', 'pending')
                            ->orWhere('payment_status', 'failed');
                    })
                    ->get();

                if ($shippedOrders->count() < 1) {
                    return false;
                }


                // 4. Send payment to vendor’s Stripe account (once for the whole store)
                StripeLib::setApiKey(env('STRIPE_API_KEY'));

                if ($shippedOrders->count() > 0) {
                    $store = $shippedOrders->first()->store;

                    if ($store && $store->user && !empty($store->account_id)) {

                        $vendor_stripe_acc = $store->account_id;
                        $vendor_email = $store->user->email;
                        $store_name = $store->user->name ?? '';
                        $payable_amount = $amount;
                        $group = 'store_' . $store->store_id . '_bulk_' . time(); // Group identifier for Stripe transfer  
                        try {
                            $charge = Transfer::create([
                                'amount' => $amount * 100, // Stripe uses cents
                                'currency' => 'usd',
                                'destination' => $vendor_stripe_acc,
                                'transfer_group' => $group,
                            ]);

                            if ($charge) {

                                // 2. Get and update all matching shipped orders for a specific store
                                $affectedRows = StoreOrder::where('store_id', $store_id) // Add store filter
                                    ->where('order_status', 'shipped')
                                    ->where('charge_status', 'paid')
                                    ->where('shipped_date', '<=', $escrowDate)
                                    ->where(function ($query) {
                                        $query->where('payment_status', 'pending')
                                            ->orWhere('payment_status', 'failed');
                                    })
                                    ->update([
                                        'payment_status' => 'paid',
                                        'order_status' => 'completed',
                                    ]);
                                // Log the payment
                                $paymentLog = new Logger('vendor_payment');
                                $paymentLog->pushHandler(new StreamHandler(storage_path('logs/test_vendor_payments.log')), Logger::INFO);
                                $paymentLog->info('Bulk Paid', [
                                    'store_id' => $store->store_id,
                                    'payable_amount' => $amount,
                                    'payment_date' => Carbon::now(),
                                ]);

                                // Send payment confirmation email
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


                                        return $amount;
                                    } catch (Exception $e) {
                                        throw $e;
                                    }
                                }
                            }
                        } catch (Exception $e) {
                            $affectedRows = StoreOrder::where('store_id', $store_id) // Add store filter
                                ->where('order_status', 'shipped')
                                ->where('charge_status', 'paid')
                                ->where('shipped_date', '<=', $escrowDate)
                                ->where(function ($query) {
                                    $query->where('payment_status', 'pending')
                                        ->orWhere('payment_status', 'failed');
                                })
                                ->update([
                                    'payment_status' => 'failed',
                                ]);
                            $paymentLog = new Logger('');
                            $paymentLog->pushHandler(new StreamHandler(storage_path('logs/test_vendor_payments.log')), Logger::ERROR);
                            $paymentLog->error('Failed', ['payable_amount' => $payable_amount, 'payment_date' => Carbon::now(), 'Message' => $e->getMessage()]);

                            $subject = 'Payments Failed from MallShark';
                            $mail_template = view('mail.vendor_payment', compact('store_name', 'payable_amount'));
                            $params = [
                                'from'    => 'MallShark  ' . env('SUPPORT_EMAIL'),
                                'to'      => "abdullah.jutt@mindblaze.biz",
                                'subject' => $subject,
                                'html'    => "$mail_template",
                            ];

                            $this->mailgun->SendMailgunMail($params);
                        }
                    }
                }
            } else {
                // admin operation mode
                // 1. Calculate escrowDate
                if(env('APP_ENV') == 'development'){
                    $escrowDate = Carbon::now()->subMinutes($escrowMins)->format('Y-m-d H:i:s');
                }else{
                    $escrowDate = Carbon::now()->subMinutes($escrowDays)->format('Y-m-d H:i:s');
                }
                // 2. Fetch all shipped orders for a specific store within the escrowDate window
                $shippedOrders = StoreOrder::where('store_id', $store_id)
                    ->where('order_status', 'shipped')
                    ->where('charge_status', 'paid')
                    ->where('shipped_date', '<=', $escrowDate)
                    ->where(function ($query) {
                        $query->where('payment_status', 'pending')
                            ->orWhere('payment_status', 'failed');
                    })
                    ->get();

                if ($shippedOrders->count() < 1) {
                    return false;
                }
                StripeLib::setApiKey(env('STRIPE_API_KEY'));

                if ($shippedOrders->count() > 0) {
                    $store = $shippedOrders->first()->store;

                    if ($store && $store->user && !empty($store->account_id)) {

                        $vendor_stripe_acc = $store->account_id;
                        $vendor_email = $store->user->email;
                        $store_name = $store->user->name ?? '';
                        $payable_amount = $amount;
                        $group = 'store_' . $store->store_id . '_bulk_' . time(); // Group identifier for Stripe transfer  
                        try {
                            $charge = Transfer::create([
                                'amount' => $amount * 100, // Stripe uses cents
                                'currency' => 'usd',
                                'destination' => $vendor_stripe_acc,
                                'transfer_group' => $group,
                            ]);

                            if ($charge) {

                                // 2. Get and update all matching shipped orders for a specific store
                                $affectedRows = StoreOrder::where('store_id', $store_id) // Add store filter
                                    ->where('order_status', 'shipped')
                                    ->where('charge_status', 'paid')
                                    ->where('shipped_date', '<=', $escrowDate)
                                    ->where(function ($query) {
                                        $query->where('payment_status', 'pending')
                                            ->orWhere('payment_status', 'failed');
                                    })
                                    ->update([
                                        'payment_status' => 'paid',
                                        'order_status' => 'completed',
                                    ]);
                                // Log the payment
                                $paymentLog = new Logger('vendor_payment');
                                $paymentLog->pushHandler(new StreamHandler(storage_path('logs/test_vendor_payments.log')), Logger::INFO);
                                $paymentLog->info('Bulk Paid', [
                                    'store_id' => $store->store_id,
                                    'payable_amount' => $amount,
                                    'payment_date' => Carbon::now(),
                                ]);

                                // Send payment confirmation email
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

                                        // $this->mailgun->SendMailgunMail($params);


                                        return $amount;
                                    } catch (Exception $e) {
                                        throw $e;
                                    }
                                }
                            }
                        } catch (Exception $e) {
                            $affectedRows = StoreOrder::where('store_id', $store_id) // Add store filter
                                ->where('order_status', 'shipped')
                                ->where('charge_status', 'paid')
                                ->where('shipped_date', '<=', $escrowDate)
                                ->where(function ($query) {
                                    $query->where('payment_status', 'pending')
                                        ->orWhere('payment_status', 'failed');
                                })
                                ->update([
                                    'payment_status' => 'failed',
                                ]);
                            $paymentLog = new Logger('');
                            $paymentLog->pushHandler(new StreamHandler(storage_path('logs/test_vendor_payments.log')), Logger::ERROR);
                            $paymentLog->error('Failed', ['payable_amount' => $payable_amount, 'payment_date' => Carbon::now(), 'Message' => $e->getMessage()]);

                            $subject = 'Payments Failed from MallShark';
                            $mail_template = view('mail.vendor_payment', compact('store_name', 'payable_amount'));
                            $params = [
                                'from'    => 'MallShark  ' . env('SUPPORT_EMAIL'),
                                'to'      => "abdullah.jutt@mindblaze.biz",
                                'subject' => $subject,
                                'html'    => "$mail_template",
                            ];

                            // $this->mailgun->SendMailgunMail($params);
                        }
                    }
                }
            }
        } catch (Exception $e) {
            throw $e;
        }
    }
}