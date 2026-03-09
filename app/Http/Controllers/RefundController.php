<?php

namespace App\Http\Controllers;

use App\Constants;
use Session;
use Zendesk;
use Redirect;
use App\Models\Product;
use App\Models\StoreOrder;
use App\Models\StoreEscrow;
use Illuminate\Http\Request;
use App\Models\{DeviceToken, ProductRefund,ProductVariation,User};
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\MailgunController;
use App\Models\OrderItems;
use Illuminate\Support\Facades\URL;
use App\Services\FCMService;
use App\Services\SellerFcmService;

class RefundController extends Controller
{
    private $_productrefund;
    private $mailgun;

    public function __construct()
    {
        $this->_productrefund = new ProductRefund();
        $this->mailgun = new MailgunController();
    }

    private function current_user_can_update_order($order_id)
    {
        $val = false;
        if ($order_id != '') {
            $current_user_id = getCurrentUserId();
            $store_id = DB::table('store_orders')
                ->where('order_id', '=', $order_id)
                ->select('store_id', 'buyer_id')
                ->get()
                ->toArray();

            if (count($store_id) > 0) {
                $store_idd = $store_id[0]->store_id;
                if ($store_idd != '') {
                    $user = DB::table('stores')
                        ->where('store_id', '=', $store_idd)
                        ->select('user_id')
                        ->get()
                        ->toArray();

                    if (count($user) > 0) {
                        $user_id = $user[0]->user_id;
                        $buyer_id = $store_id[0]->buyer_id;
                        if ($user_id == $current_user_id) {
                            $val = true;
                        }
                        if ($buyer_id == $current_user_id) {
                            $val = true;
                        }
                    }
                }
            }
        }
        return $val;
    }

    public function cancel_order(Request $request)
    {
        try {

            if ($request) {
                // dd($request->input());
                // dd($request->all());
                $StoreOrder = StoreOrder::find($request['order_id']);
                $storeData = $StoreOrder->store;
                
                // echo '$storeData: <pre>' .print_r($storeData->user->email,true). '</pre>'; 
                // echo '$StoreOrder->user->email: <pre>' .print_r($StoreOrder->user->email,true). '</pre>'; die();
                $StoreOrder->order_status = $request['order_status'] ? $request['order_status'] : "";
                $StoreOrder->refund_date = $request['refund_date'] ? $request['refund_date'] : "";
                $StoreOrder->is_refund = $request['is_refund'] ? $request['is_refund'] : "";
                $StoreOrder->save();
            }
            if (is_array($request->cancel_product_id)) {
                foreach ($request->cancel_product_id as $cancel_product) {
                    try {
                        $productCancel = new ProductRefund();
                        $productCancel->product_id = $cancel_product['product_id'] ? $cancel_product['product_id'] : "";
                        $productCancel->order_id = $cancel_product['order_id'] ? $cancel_product['order_id'] : "";
                        $productCancel->buyer_id = $cancel_product['buyer_id'] ? $cancel_product['buyer_id'] : "";
                        $productCancel->store_id = $cancel_product['store_id'] ? $cancel_product['store_id'] : "";
                        $productCancel->quantity = $cancel_product['quantity'] ? $cancel_product['quantity'] : "";
                        $productCancel->price = $cancel_product['price'] ? $cancel_product['price'] : "";
                        $productCancel->shipping_amount = $cancel_product['shipment'] ? $cancel_product['shipment'] : "";
                        $productCancel->reason_type = $request['refund_reason_type'] ? $request['refund_reason_type'] : "";
                        $productCancel->reason = $request['message'] ? $request['message'] : "";
                        $productCancel->is_refund = $request['cancelled'] ? $request['cancelled'] : "";
                        $productCancel->refund_date = $request['refund_date'] ? $request['refund_date'] : "";
                        $productCancel->payment_method = $request['payment_method'] ? $request['payment_method'] : "";
                        $productCancel->variation_id = $cancel_product['variation_id'] ? $cancel_product['variation_id'] : null;
                        $productCancel->save();
                    } catch (Exception $e) {
                        echo 'Caught exception: ', $e->getMessage(), "\n";
                    }
                }
            } else {
                try {
                    $productCancel = new ProductRefund();
                    $productCancel->product_id = $request->product_id[0] ? $request->product_id[0] : null;
                    $productCancel->order_id = $request['order_id'] ? $request['order_id'] : "";
                    $productCancel->buyer_id = $request['buyer_id'] ? $request['buyer_id'] : "";
                    $productCancel->store_id = $request['store_id'] ? $request['store_id'] : "";
                    $productCancel->quantity = $request['quantity'] ? $request['quantity'] : "";
                    $productCancel->price = $request['price'] ? $request['price'] : "";
                    $productCancel->reason_type = $request['refund_reason_type'] ? $request['refund_reason_type'] : "";
                    $productCancel->reason = $request['reason'] ? $request['reason'] : "";
                    $productCancel->is_refund = $request['is_refund'] ? $request['is_refund'] : "";
                    $productCancel->refund_date = $request['refund_date'] ? $request['refund_date'] : "";
                    $productCancel->payment_method = $request['payment_method'] ? $request['payment_method'] : "";
                    $productCancel->save();
                } catch (Exception $e) {
                    echo 'Caught exception: ', $e->getMessage(), "\n";
                }
            }
            $usershipping = getShippingAddressbyUserId($request['buyer_id']);
            $userData = getUserById($request['buyer_id']);
            if (!empty($userData)) {
                $email = $userData->email;
            } else {
                $email = "noman@mindblazetech.com";
            }

            // $user_description = Constants::REQUEST_CANCEL_ORDER_BUYER_DESCRIPTION;
            $subjectuser = Constants::REQUEST_CANCEL_ORDER_BUYER_SUBJECT.$request['order_id'];
            $subjectStore = $userData->name.Constants::REQUEST_CANCEL_ORDER_SELLER_SUBJECT.$request['order_id'];
            // $store_description = $userData->name.Constants::REQUEST_CANCEL_ORDER_SELLER_DESCRIPTION;
            // email send to vendor
            $html = view('mail.canceled-order-from-client' , ['userData' => $userData,'orderData' => $request->input(), "shippingAddress" => $usershipping, 'storeData' => $storeData, 'customerDetail' => $StoreOrder->user]);
            $subject = 'Order NO ' . $request['order_id'] . ' Cancellation';
            $toEmail = $storeData->user->email;
            $params = [
                'from'    => 'MallShark  ' . env('SUPPORT_EMAIL'),
                'to' => $toEmail,
                'subject' => $subject,
                'html' => "$html",
            ];
            $this->mailgun->SendMailgunMail($params);
            if(isset($storeData->user->user_id) && !is_null($storeData->user->user_id)){
                $userData = User::with('device_token')->findOrFail($storeData->user->user_id);
                $device_tokens = DeviceToken::where('user_id',$userData->user_id)->where('app_flag','seller')->get();
                if ($device_tokens) {
                    $tokens = [];
                    if(isset($device_tokens)&& count($device_tokens) > 0){

                    foreach ($device_tokens as $device_token_obj) {
                        // dd($device_token_obj->fcm_token);
                    
                    
                    $subject = $subjectStore;
                    // foreach($tokens as $token){
                        $notification = SellerFcmService::send(
                            $device_token_obj->fcm_token,
                            [
                                'title' => $subject,
                            ]
                        );
                    }
                }
                    addNotifications($storeData->user->user_id, $userData->user_id, $subjectStore, '', URL::to('/brand-order-details/'.$request['order_id']),'brand_order_details',$request['order_id']);

                }
            }
         
            // email send to buyer
            $userData = getUserById($request['buyer_id']);
            $html = view('mail.order-update-client-cancelled', ['order_id' => $request['order_id'], 'userData' => $userData, 'orderData' => $request->input(), "shippingAddress" => $usershipping, 'storeData' => $storeData, 'customerDetail' => $StoreOrder->user]);
            $subject = 'Order NO ' . $request['order_id'] . ' Cancellation';
            $toEmail_buyer = $email;
            $params = [
                'from'    => 'MallShark  ' . env('SUPPORT_EMAIL'),
                'to' => $toEmail_buyer,
                'subject' => $subject,
                'html' => "$html",
            ];
            $this->mailgun->SendMailgunMail($params);
            // if(isset($request['buyer_id']) && !is_null($request['buyer_id'])){
            //     $userData = User::with('device_token')->findOrFail($request['buyer_id']);
            //     $device_tokens = DeviceToken::where('user_id',$userData->user_id)->where('app_flag','buyer')->get();
            //     if ($device_tokens) {
            //         $tokens = [];
            //         if(isset($device_tokens)&& count($device_tokens) > 0 ){
            //             foreach ($device_tokens as $device_token_obj) {
            //                 // dd($device_token_obj->fcm_token);
                        
                        
            //             $subject = $subjectuser;
            //             // foreach($tokens as $token){
            //                 $notification = FCMService::send(
            //                     $device_token_obj->fcm_token,
            //                     [
            //                         'title' => $subject,
            //                     ]
            //                 );
            //             }
    
            //         }
              

            //         addNotifications($request['buyer_id'] , $storeData->user->user_id, $subjectuser, '', URL::to('new-buyer-orders-detail/'.$request['order_id']),'buyer_order_details',$request['order_id'],'buyer');

                    
            //     }
            // }

            return response()->json(['success' => 'Your Order Cancel is Processing']);
        } catch (\Throwable $th) {
            echo $th;
        }
    }

    public function refund_order(Request $request)
    {
        // dd($request);
        if ($request) {
            $StoreOrder = StoreOrder::find($request['order_id']);
            $StoreOrder->order_status = $request['order_status'] ? $request['order_status'] : "";
            $StoreOrder->refund_date = $request['refund_date'] ? $request['refund_date'] : "";
            $StoreOrder->is_refund = $request['is_refund'] ? $request['is_refund'] : "";
            $StoreOrder->save();
        }
        if (is_array($request->product_id)) {

            foreach ($request->product_id as $product) {
                try {
                    $productRefund = new ProductRefund();
                    $productRefund->product_id = $product['product_id'] ? $product['product_id'] : null;
                    $productRefund->order_id = $product['order_id'] ? $product['order_id'] : "";
                    $productRefund->buyer_id = $product['buyer_id'] ? $product['buyer_id'] : "";
                    $productRefund->store_id = $product['store_id'] ? $product['store_id'] : "";
                    $productRefund->quantity = $product['quantity'] ? $product['quantity'] : "";
                    $productRefund->price = $product['price'] ? $product['price'] : "";
                    $productRefund->reason_type = $request['refund_reason_type'] ? $request['refund_reason_type'] : "";
                    $productRefund->reason = $request['message'] ? $request['message'] : "";
                    $productRefund->is_refund = $request['cancelled'] ? $request['cancelled'] : "";
                    $productRefund->refund_date = $request['refund_date'] ? $request['refund_date'] : "";
                    $productRefund->save();
                } catch (Exception $e) {
                    echo 'Caught exception: ', $e->getMessage(), "\n";
                }
            }
        } else {
            try {
                $productRefund = new ProductRefund();
                $productRefund->product_id = $request['product_id'] ? $request['product_id'] : null;
                $productRefund->order_id = $request['order_id'] ? $request['order_id'] : "";
                $productRefund->buyer_id = $request['buyer_id'] ? $request['buyer_id'] : "";
                $productRefund->store_id = $request['store_id'] ? $request['store_id'] : "";
                $productRefund->quantity = $request['quantity'] ? $request['quantity'] : "";
                $productRefund->price = $request['price'] ? $request['price'] : "";
                $productRefund->reason_type = $request['refund_reason_type'] ? $request['refund_reason_type'] : "";
                $productRefund->reason = $request['reason'] ? $request['reason'] : "";
                $productRefund->is_refund = $request['is_refund'] ? $request['is_refund'] : "";
                $productRefund->refund_date = $request['refund_date'] ? $request['refund_date'] : "";
                $productRefund->save();
            } catch (Exception $e) {
                echo 'Caught exception: ', $e->getMessage(), "\n";
            }
        }
        return response()->json(['success' => 'Your Order Refunded is Processing']);
    }

    // Order complete through Admin end when  order dispute
    public function admin_complete_order(Request $request)
    {

        // dd($request);

        try {
            $StoreOrder = StoreOrder::find($request['order_id']);
            $StoreOrder->order_status = $request['order_status'] ? $request['order_status'] : "";
            $StoreOrder->refund_date = $request['complete_date'] ? $request['complete_date'] : "";
            $StoreOrder->is_refund = 'complete';
            $StoreOrder->is_reject = '0';
            $StoreOrder->save();
        } catch (Exception $e) {
            echo 'Caught exception: ', $e->getMessage(), "\n";
        }

        return response()->json(['success' => 'Your Order Refunded is Completed']);
    }

    public function rejectOrderStoreOwner(Request $request)
    {
        // dd($request->input());
        $val = false;
        $buyer_id = $request['buyer_id'];
        $order_id = $request['order_id'];
        $StoreOrder = StoreOrder::find($order_id);
        $storeData = $StoreOrder->store;
        $status = $request['order_status'];

        if ($request) {
            try {
               
                foreach ($request->refund_product_id as $product) {
                    if ($product === null) {
                        return Redirect::back()->withError(['msg' => 'Please check your order something is missing']);
                    } else {
                        $productRefund = ProductRefund::where('product_id', $product)->first();
                        $productRefund->is_reject = $request['is_reject'] ? $request['is_reject'] : "";
                        $productRefund->is_refund = $request['is_refund'] ? $request['is_refund'] : "";
                        $productRefund->reject_order_reason = $request['reject_order_reson'] ? $request['reject_order_reson'] : "";
                        $productRefund->save();
                    }
                }

                $StoreOrder = StoreOrder::find($request['order_id']);
                $StoreOrder->order_status = $request['order_status'] ? $request['order_status'] : "";
                $StoreOrder->reject_date = $request['reject_date'] ? $request['reject_date'] : "";
                $StoreOrder->is_refund = $request['is_refund'] ? $request['is_refund'] : "";
                $StoreOrder->is_reject = $request['is_reject'] ? $request['is_reject'] : "";
                $StoreOrder->save();

                $val = true;
                $buyerData = getUserByIdchat($buyer_id);
                $userid = Session::get('userData')['user_id'];
                // dump("user_id", $userid);
                // dump("buyerData", $buyerData);
                // die;
                $storeName = $storeData->user->name;
                $mallsharkstorename = $storeData->store_name;
                $mallsharkstoreslug = $storeData->store_slug;
                $description = '';

                // Vendor Email
                $html = view('mail.order_refund_request_rejected_vendor', ['storeName' => $storeName, 'order_id' => $order_id, 'productInfo' => $request->refund_product_title, 'status' => $status, 'storeData' => $storeData, 'customerDetail' => $StoreOrder->user]);
                $subject = Constants::DISPUTED_ORDER_BUYER_SUBJECT.$order_id;
                // $toEmail = $userData->email;
                $toEmail = $storeData->user->email;

                $params = [
                    'from'    => 'MallShark  ' . env('SUPPORT_EMAIL'),
                    'to' => $toEmail,
                    'subject' => $subject,
                    'html' => "$html",
                ];
                $results = $this->mailgun->SendMailgunMail($params);

                // Buyer Email
                $buyerName = $StoreOrder->user->name;
                $html = view('mail.order_refund_request_rejected_client', ['buyerName' => $buyerName, 'order_id' => $order_id, 'mallsharkstorename' => $mallsharkstorename,'mallsharkstoreslug' => $mallsharkstoreslug, 'storeName' => $storeName, 'productInfo' => $request->refund_product_title, 'status' => $status, 'storeData' => $storeData, 'customerDetail' => $StoreOrder->user]);
                // $subject = Constants::DISPUTED_ORDER_BUYER_SUBJECT;
                // $toEmail = $userData->email;
                $toEmail_buyer = $StoreOrder->user->email;

                $params = [
                    'from'    => 'MallShark  ' . env('SUPPORT_EMAIL'),
                    'to' => $toEmail_buyer,
                    'subject' => $subject,
                    'html' => "$html",
                ];
                $results = $this->mailgun->SendMailgunMail($params);
                if(isset($buyer_id) && $buyer_id != ''){
                    $userData = User::with('device_token')->findOrFail($buyer_id);
                    $device_tokens = DeviceToken::where('user_id',$userData->user_id)->where('app_flag','buyer')->get();
                    
                    if ($device_tokens) {
                        $tokens = [];
                        if(isset($device_tokens)&& count($device_tokens) > 0){
                        foreach ($device_tokens as $device_token_obj) {
                            // dd($device_token_obj->fcm_token);
                        
                        
                        $subject = $subject;
                        // foreach($tokens as $token){
                            $notification = FCMService::send(
                                $device_token_obj->fcm_token,
                                [
                                    'title' => $subject,
                                   
                                ]
                            );
    
                            
                        }
                    }
                addNotifications($buyer_id, getCurrentUserId(), $subject, $description, URL::to('new-buyer-orders-detail/' . $order_id),'buyer_order_details',$order_id,'buyer');
                    
                    }
                }
                
                // create ticket zendesk start
                if (Session::has('userData')) {
                    $userid = Session::get('userData')['user_id'];

                    $userData = getUserById($userid);
                    $userZendeskId = $buyerData->zendesk_user_id;

                    $zendeskId = $userData->zendesk_user_id;
                    if (empty($zendeskId)) {
                        // die('vendor');
                        try {
                            $zendeskResult = Zendesk::users()->create(
                                [
                                    'name' => $userData->name,
                                    'email' => $userData->email,
                                    'phone' => $userData->contact_no,
                                    'role' => 'end-user',
                                    'details' => 'This user has been created with the API.',
                                ]
                            );
                            // dump("zendesk", $zendeskResult);
                        } catch (\Zendesk\API\Exceptions\ApiResponseException $e) {
                            // echo 'Please check your credentials. Make sure to change the $subdomain, $username, and $token variables in this file.';
                        }
                        if (isset($zendeskResult)) {
                            $zendeskResult = $zendeskResult->user;
                            $zendId = $zendeskResult->id;
                            $zendIdd = strval($zendId);
                            $zendeskId = $zendIdd;
                            $q = DB::table('users')->where('user_id', '=', $userid)->update(['zendesk_user_id' => $zendIdd]);
                        }
                    }

                    // if (empty($userZendeskId)) {
                     

                    //     try {
                    //         $userzendeskResult = Zendesk::users()->create(
                    //             [
                    //                 'name' => $userData->name,
                    //                 'email' => $userData->email,
                    //                 'phone' => $userData->contact_no,
                    //                 'role' => 'end-user',
                    //                 'details' => 'This user has been created with the API.',
                    //             ]
                    //         );
                    //         dump("userzendeskResult",$userzendeskResult);
                          
                    //     } catch (\Zendesk\API\Exceptions\ApiResponseException $e) {
                    //         echo $e->getMessage(). ' Please check your credentials. Make sure to change the $subdomain, $username, and $token variables in this file.';
                    //     }
                       
                    //     if (isset($userzendeskResult)) {
                    //         $userzendeskResult = $userzendeskResult->user;
                    //         $UserZendId = $userzendeskResult->id;
                    //         $userZendIdd = strval($UserZendId);
                    //         $userZendeskId = $userZendIdd;
                    //         $q = DB::table('users')->where('user_id', '=', $buyer_id)->update(['zendesk_user_id' => $userZendIdd]);
                    //     }
                    // }
                    // die('end');
                    $newTicket = Zendesk::tickets()->create([
                        'subject' => 'Order Dispute with ID' . $order_id . '',
                        'comment' => [
                            'body' => 'The order for ' . $userData->name . ' Vendor has been disputed, Email ' . $userData->email . ' ',
                        ],
                        'priority' => 'normal',
                        'requester_id' => $zendeskId,
                        'type' => 'problem',
                        'priority' => 'high',
                    ]);
                }

                // create ticket zendesk end
                // return Redirect::back()->withSuccess(['msg' => 'Your Order is Disputed. Superadmin check this order']);
                $responseData = ['msg' => 'Your Order is Disputed. Superadmin check this order'];

                return response()->json($responseData);

            } catch (Throwable $e) {
                echo 'Caught exception: ', $e->getMessage(), "\n";
            }
        }
    }

    public function return_refund(Request $request)
    {

        $status = "refunded";
        $StoreOrder = StoreOrder::find($request['order_id']);
        $StoreOrder->order_status = 'refunded';
        $StoreOrder->is_refund = 'refund';
        $StoreOrder->refund_amount = $request['amount'];
        $StoreOrder->payment_status = $status;
        $StoreOrder->update();

        $StroreEscrow = StoreEscrow::find($request['order_id']);
        $StroreEscrow->status = $status;
        $StroreEscrow->profit_status = $status;
        $StroreEscrow->update();

        if (is_array($request->refund_process)) {
            foreach ($request->refund_process as $product) {
                try {
                    $productRefundComplete = Product::find($product['product_id']);
                    $productRefundComplete->current_stock += $product['product_quantity'] ? $product['product_quantity'] : null;
                    $productRefundComplete->update();
                } catch (Exception $e) {
                    echo 'Caught exception: ', $e->getMessage(), "\n";
                }
            }
        }

        return response()->json(['success' => 'Your Order Refunded is Complete']);
    }

    public function return_refund_stripe(Request $request)
    {
        // dd($request->input());
        try {
            // DB::beginTransaction();
            $status = "refunded";
            $StoreOrder = StoreOrder::find($request['order_id']);
            $StoreOrder->order_status = 'refunded';
            $StoreOrder->is_refund = 'refund';
            $StoreOrder->refund_amount = $request['amount'];
            $StoreOrder->payment_status = $status;
            $StoreOrder->update();
            if ($request->payment_method == 'stripe') {
            try {
                $order_items_array = OrderItems::where('order_id',$request['order_id'])->get();
                // dd($order_items_array);
                foreach($order_items_array as $single_order_item){
                    if(!is_null($single_order_item)){
                        if($single_order_item->variation_id == 0 || $single_order_item->variation_id == null){
                            $product_id = $single_order_item->item_id;
                            $product_quantity = $single_order_item->quantity;
                            // dd($product_quantity);
                            $product_stock_update = Product::find($product_id);
                            $old_Stock = $product_stock_update->current_stock;
                            // dd($old_Stock);
                            $updated_stock = $old_Stock+$product_quantity;
                            // dd($updated_stock);
                            $product_stock_update->current_stock = $updated_stock;
                            
                            $product_stock_update->save();
                        }else{
                            $variation_id = $single_order_item->variation_id;
                            $product_quantity = $single_order_item->quantity;
                            // dd($product_quantity);
                            $product_stock_update = ProductVariation::find($variation_id);
                            $old_Stock = $product_stock_update->current_stock;
                            // dd($old_Stock);
                            $updated_stock = $old_Stock+$product_quantity;
                            // dd($updated_stock);
                            $product_stock_update->current_stock = $updated_stock;
                            
                            $product_stock_update->save();
                        }
                        
                    }
                }     
                $Stripeobj = new StripeController();
                 $response =   $Stripeobj->refundBack($request['order_id'], $request['amount']);
                //  dd($response);
                if($response === true) {
                    // dd('asdsadsadasd');
                    $orderItems = getOrderItemsByOrderId($request['order_id']);
                    $order_information = DB::table('store_orders')->where('order_id', $request['order_id'])->select('buyer_id')->first();
                    $order_store_info = DB::table('store_orders')->where('order_id', $request['order_id'])->select('store_id')->first();
                    $buyer_id = $order_information->buyer_id;
                    $store_id = $order_store_info->store_id;
                    $store = getStoreInfoById((int) $store_id);
                    $storeUser_id = isset($store[0]->user_id) ? $store[0]->user_id : '';
    
                    $userData = getUserById($buyer_id);
                    $usershipping = getShippingAddressbyUserId($buyer_id);
                    if (!empty($userData)) {
                        $name  = $userData->name;
                        $email = $userData->email;
                    } else {
                        $name  = "";
                        $email = "noman@mindblazetech.com";
                    }

                    $productArray = [];
                    foreach ($orderItems as $key => $value) {
                        $product_id = $value->item_id;
                        $productData = getProductIdSpecificColums($product_id);
                        $variableData = getvariableByProduct_id($value->variation_id);
                        $productArray[] = [
                            'title' => $productData->title,
                            'slug' => $productData->slug,
                            'feature_image' => $productData->feature_image,
                            'product_type' => $productData->product_type,
                            'price' => $value->price,
                           'variation_price' => is_array($variableData) ? (isset($variableData['sale_price']) && $variableData['sale_price'] ? $variableData['sale_price'] : $variableData['price']) : null,
                            'price_total' => $value->total,
                            'quantity' => $value->quantity ?? '',
                            'variable_image' => isset($variableData) ? $variableData->product_variation_image : null,
                            'variation_id' => isset($variableData) ? $variableData->variation_id : null,
        
                        ];
                    }
                    
                    $html = view('mail.order-update-client-refunded', ['userData' => $userData, "shippingAddress" => $usershipping, 'orderItems' => $productArray]);
    
                    $subject = 'Your MallShark Order Has Been Refunded';
                    $toEmail = $email;
                    $params = [
                        'from'    => 'MallShark  ' . env('SUPPORT_EMAIL'),
                        'to'      => $toEmail,
                        'subject' => $subject,
                        'html'    => "$html",
                    ];
                    try {
                       
                   
                    $results = $this->mailgun->SendMailgunMail($params);
                    } catch (\Throwable $th) {
                         dd($th);
                    }
                    $subjectStore = Constants::ACCEPT_REFUND_REQUEST_SELLER_SUBJECT.$request['order_id'];
                    if(isset($buyer_id) && !is_null($buyer_id)){
                        $userData = User::with('device_token')->findOrFail($buyer_id);
                        $device_tokens = DeviceToken::where('user_id',$userData->user_id)->where('app_flag','buyer')->get();

                        if ($device_tokens) {
                            $tokens = [];
                            if(isset($device_tokens)&& count($device_tokens) > 0){
                            foreach ($device_tokens as $device_token_obj) {
                                // dd($device_token_obj->fcm_token);
                            
                            
                            $subject = $subjectStore;
                            // foreach($tokens as $token){
                                $notification = FCMService::send(
                                    $device_token_obj->fcm_token,
                                    [
                                        'title' => $subject,
                                    ]
                                );
        
                               
                            }
                        }
                            addNotifications($buyer_id,$storeUser_id, $subjectStore , '', URL::to('new-buyer-orders-detail/'.$request['order_id']),'buyer_order_details',$request['order_id'],'buyer');
        
                        }
                    }
                    $mesage = 'Refund was successfull';
                } else {
                    $mesage = 'Refund is not successfull please contact to super admin';
                }
                } catch (\Throwable $th) {
                    echo $th->getMessage();
                }
            }
            // $StroreEscrow = StoreEscrow::find($request['order_id']);
            // $StroreEscrow->status = $status;
            // $StroreEscrow->profit_status = $status;
            // $StroreEscrow->update();

            if (is_array($request->refund_process)) {
                foreach ($request->refund_process as $product) {

                    $productRefundComplete = Product::find($product['product_id']);
                    $productRefundComplete->current_stock += $product['product_quantity'] ? $product['product_quantity'] : null;
                    $productRefundComplete->update();
                }
            }

            return response()->json(['success' => 'Your Order Refunded is Complete']);
            // DB::commit();
        } catch (\Throwable $e) {
            // DB::rollback();
            echo 'Caught exception: ', $e->getMessage(), "\n";
        }
    }
}
