<?php

namespace App\Http\Controllers;

use DB;
use PDF;
use URL;
use Zendesk;
use Mailgun\Mailgun;
use App\Models\StoreOrder;
use App\Models\DigitalFiles;
use Illuminate\Http\Request;
use App\Repositories\User\Useraccess;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Redirect;
use App\Http\Controllers\MailgunController;
use App\Repositories\Orders\StoreOrdersAccess;

class OrdersController extends Controller
{

    //private $pdf;
    private $access_obj;
    private $access_obj_users;
    private $storeOrder;
    private $mailgun;
    private $digtialFiles;

    public function __construct()
    {
        $this->access_obj = new StoreOrdersAccess();
        $this->access_obj_users = new Useraccess();
        $this->storeOrder = new StoreOrder();
        $this->mailgun = new MailgunController();
        $this->digtialFiles = new DigitalFiles();

        //$this->pdf = new PDF();
    }

    public function create_order($order_info = array())
    {
        print_rr($order_info);
        die();
        $this->access_obj->create_order();
    }

    public function get_store_order(Request $request)
    {
        

        if (Session::has('userData')) {
            $mesage = '';
            $active = '#home';
            $input = $request->all();
            $order_status = $request->input('status');
            // echo '$input: <pre>' .print_r($input,true). '</pre>';

            // die();
            if (isset($input['status']) && $input['status'] != '' && isset($input['order_id']) && $input['order_id'] != '') {
                $active = '#' . $input['active_tab'];
                if ($this->access_obj->update_order_status($input)) {
                    $mesage = 'Status updated successfully';
                } else {
                    $mesage = 'Status is not changed please contact to super admin';
                }
            }
            if (isset($input['purpose']) && $input['purpose'] == 'refund' && isset($input['order_id']) && $input['order_id'] != '') {
                $active = '#' . $input['active_tab'];
                $mesage = 'Refund is not successfull please contact to super admin';
                /*
             *  $Stripeobj = new StripeController();
            if ($Stripeobj->refundBack($input['order_id'])) {
            $mesage = 'Refund was successfull';
            } else {
            $mesage = 'Refund is not successfull please contact to super admin';
            }
             */
            }

            $user_data = Session::get('userData');

            $user_id = $user_data['user_id'];

            $stores = $this->access_obj_users->get_store_info_by_user_id($user_id);

            if (count($stores) > 0 && $stores != 0) {
                $stores_array = array();
                foreach ($stores as $val) {
                    $stores_array[] = $val['store_id'];
                }

                $orders_all_refund_cancel = "";
                if ($order_status != '' && $request->input('current_tab') != '') {

                    $orders_all = $this->access_obj->get_orders_with_items_all($stores_array, $input);

                    $orders_all_refund_cancel = $this->access_obj->get_refund_cancel_orders_with_items_status($stores_array, $input);
                } else if ($order_status != '' && $request->input('current_tab') == '' && $request->input('order_id') != '') {
                    $orders_all_refund_cancel = $this->access_obj->get_refund_cancel_orders_with_items_status($stores_array, $input);
                    $orders_all = $this->access_obj->get_orders_with_items_all($stores_array, $input);
                } else if ($order_status != '') {
                    $orders_all = $this->access_obj->get_orders_with_items_all($stores_array, $input);

                    $orders_all_refund_cancel = $this->access_obj->get_refund_cancel_orders_with_items_status($stores_array, $input);
                } else if ($request->input('current_tab') == '') {

                    $orders_all_refund_cancel = $this->access_obj->get_refund_cancel_orders_with_items_status($stores_array, $input);

                    $orders_all = $this->access_obj->get_orders_with_items_all($stores_array, $input);
                } else {
                    $orders_all = $this->access_obj->get_orders_with_items_all($stores_array, $input);
                    $orders_all_refund_cancel = $this->access_obj->get_refund_cancel_orders_with_items_status($stores_array, $input);
                }
                if (isset($input['current_tab']) && $input['current_tab'] != '') {
                    $active = 'dd';
                }

                // if ($this->isMobileDevice()) {
                //     return View('orders.mobile.store_orders', array(
                //         'orders_all' => $orders_all,
                //         'orders_all_refund_cancel' => $orders_all_refund_cancel,

                //         'mesage' => $mesage,
                //         'active' => $active,
                //     ));
                // } else {

                $page_number = 1;
                // echo 'Data: <pre>' .print_r($orders_all,true). '</pre>'; die();
                return View('orders.store_orders', array(
                    'orders_all' => $orders_all,
                    'orders_all_refund_cancel' => $orders_all_refund_cancel,

                    'mesage' => $mesage,
                    'active' => $active,
                    "page_number" => $page_number
                ));
                // }
            } else {
                return '<h2>You dont have any store</h2>';
            }
        }
    }

    public function getBrandOrders(Request $request)
    {
        if (Session::has('userData')) {

            $postedData = $request->all();
            $order_status = $request->input('status');
            $order_id =  $request->input('order_id');
            try {
                if (isset($order_status) && $order_status != '' && isset($order_id) && $order_id != '') {
                    $active = '#' . $postedData['active_tab'];
                    // _____GTM Event Array Start_____
                    $orderArray = array();
                    $orderItemdetail = getOrderItemsByOrderId($order_id);
                    // echo 'Data: <pre>' .print_r($orderItemdetail,true). '</pre>'; die();
                    $orderdetail = getStoreOrder($order_id);
                    $transaction_id = $orderdetail->transaction_id;
                    $shipping_amount = $orderdetail->shipping_amount;
                    $total_amount = $orderdetail->total_amount;

                    foreach ($orderItemdetail as $key => $order) {
                      $product_id = $order->item_id;
                      $category_id = getCategoriesByProductID($product_id);
                      $productCategory = getCategoryByCateId($category_id[0]);
                        
                      $category = isset($productCategory['categoryTitle']) ? $productCategory['categoryTitle'] : '';
                      $productDetial= getProductIdSpecificColums($product_id);

                      $productStore = getStoreInfoByIdCartApi($order->store_id);
                      $product_store = $productStore['store_name'];
                      $product_title = $productDetial->title;
                      $product_quanity = $order->quantity;
                      $product_price = $order->price;
                      
                      $orderInfo = array(
                        "item_id" => $product_id,
                        "item_name" => $product_title,
                        "item_category" => $category,
                        "item_brand" => $product_store,
                        "item_list_id" => "refund",
                        "item_list_name" => "Refund Order",
                        "price" => $product_price,
                        "quantity" => $product_quanity,
                    );
                    $orderArray[] = $orderInfo;
                }
                    $response = $this->access_obj->update_order_status($postedData);
                    // echo 'Data: <pre>' .print_r($response,true). '</pre>'; die();

                    if ($response) {
                        return Redirect::back()->withSuccess([
                            'msg' => $response['message'],
                            'redund_status' => $response['redund_status'],
                            'transactionId' => $transaction_id,
                            'shipping_amount' => $shipping_amount,
                            'total_amount' => $total_amount,
                            'orderData' => $orderArray,
                        ]);
                    } else {
                        return Redirect::back()->withError([
                            'msg' => 'Status is not changed please contact to super admin',
                           
                        ]);
                        // $mesage = 'Status is not changed please contact to super admin';
                    }
                }
            } catch (\Throwable $th) {
                echo $th->getMessage();
            }
            $userId = Session::get('userData')['user_id'];

            $store = $this->access_obj_users->getStoreInfoUserId($userId);
            if ($store > 0 && $store != 0) {
                $orders_all = $this->access_obj->get_orders_with_items_all_new($store['store_id'], $postedData);
                //  echo 'Data: <pre>' .print_r($orders_all,true). '</pre>'; die();

            }
        }
        $status_list = [
            "All",
            "Pending",
            "Shipped",
            "Refunded",
            "Cancelled",
            "Disputed",
            "Completed",
        ];
        $data = [1, 2, 3, 4];
        return view("orders.newOrderPage", ["status_list" => $status_list, "data" => $data, "allOrders" => $orders_all]);
    }

    public function brandOrderdetails($order_id)
    {
        try {
            $userId = getUserIdbyOrderId($order_id);
            $currentid = Session::get('userData')['user_id'];

            $orderDetails = $this->access_obj->getOrderDetailByOrderId($order_id);
            // dd($orderDetails);
            if ($orderDetails &&  $orderDetails->count() > 0) { 
                if($currentid == $userId['user_id']){
                    return view('orders.orderdetail-ajax', ['orderDetails' => $orderDetails]);
                }else{
                    return view('error.404page');
                } 
            } else {
                return view('error.no-found-page');
                
            }
        } catch (\Throwable $th) {
           
            return response()->json([
                'error' => $th->getMessage(),
            ], 500);
        }
    }

    public function brandOrderdetailsStatus(Request $request)
    {
        $postedData = $request->all();
        
        
            $order_status = $request->input('status');
            $order_id =  $request->input('order_id');
            try {
                if (isset($order_status) && $order_status != '' && isset($order_id) && $order_id != '') {

                    // _____GTM Event Array Start_____
                    $orderArray = array();
                    $orderItemdetail = getOrderItemsByOrderId($order_id);
                    $orderdetail = getStoreOrder($order_id);
                    $transaction_id = $orderdetail->transaction_id;
                    $shipping_amount = $orderdetail->shipping_amount;
                    $total_amount = $orderdetail->total_amount;
                    
                    // dd($orderItemdetail);
                    foreach ($orderItemdetail as $key => $orderdata) {

                        // dump($orderdata);
                        $product_id = $orderdata->item_id;
                        // echo '$product_id: <pre>' .print_r($product_id,true). '</pre>';
                        $category_id = getCategoriesByProductID($product_id);
                        $productCategory = getCategoryByCateId($category_id[0]);

                        $category = isset($productCategory['categoryTitle']) ? $productCategory['categoryTitle'] : '';
                      $productDetial= getProductIdSpecificColums($product_id);
                    //   echo 'Data: <pre>' .print_r($productDetial,true). '</pre>'; die;

                      $productStore = getStoreInfoByIdCartApi($orderdata->store_id);
                      $product_store = $productStore['store_name'];
                      $product_title = $productDetial->title;
                      $product_quanity = $orderdata->quantity;
                      $product_price = $orderdata->price;
                      
                      $orderInfo = array(
                        "item_id" => $product_id,
                        "item_name" => $product_title,
                        "item_category" => $category,
                        "item_brand" => $product_store,
                        "item_list_id" => "refund",
                        "item_list_name" => "Refund Order",
                        "price" => $product_price,
                        "quantity" => $product_quanity,
                    );
                    $orderArray[] = $orderInfo;
                         
                    }
                    // die;
                    // _____GTM Event Array End_____
                   
                    $response = $this->access_obj->update_orderdetail_status($postedData);
                    // echo '$response: <pre>' .print_r($response,true). '</pre>'; die;
                    if ($response) {
                        return response()->json([
                            'status' => $response['val'] ,
                            'message' => $response['message'],
                            'transactionId' => $transaction_id,
                            'shipping_amount' => $shipping_amount,
                            'total_amount' => $total_amount,
                            'orderData' => $orderArray,
                        ]);

                    } else {
                        $message = 'Status is not changed please contact to super admin';
                        return response()->json([ 'status' => $response['val'], 'message' => $message], 400);
                    }
                }
            } catch (\Throwable $th) {
                dd($th);
                echo $th->getMessage();
            }

    }

    public function brandOrderdetailsModal(Request $request)
    {
        try {
            $order_id = $request->input('order_id');
            $orderDetails = $this->access_obj->getOrderDetailByOrderId($order_id);
            // dd($orderDetails);
            if ($orderDetails) {
                return view('orders.orderdetail-items-modal-ajax', ['orderDetails' => $orderDetails]);
            }
        } catch (\Throwable $th) {

            return response()->json([
                'error' => $th->getMessage(),
            ], 500);
        }
    }


    /*
     * function to get pdf of againstorder_id
     */

    public function getOrderPdf($orderId)
    {
        set_time_limit(0);
        $data = $this->access_obj->getOrderItemsByOrderId($orderId);
        
        // echo 'Data: <pre>' .print_r($data,true). '</pre>'; die();

        //        $data = '<table style="width:100%" id="t01">
        //  <caption>Monthly savings</caption>
        //  <tr>
        //    <th>Month</th>
        //    <th>Savings</th>
        //  </tr>
        //  <tr>
        //    <td>January</td>
        //    <td>$100</td>
        //  </tr>
        //  <tr>
        //    <td>February</td>
        //    <td>$50</td>
        //  </tr>
        //</table>';
        //        $pdf = App::make('dompdf.wrapper');
        //        $pdf->loadHTML($data);
        //        return $pdf->download('kamran.pdf');
        $pdf = PDF::loadView('orders.invoice', array('ordersdata' => $data));
        return $pdf->download('invoice-' . $orderId . '.pdf');
    }

    public function ordersDtail()
    {
        return view('orders.orders-detail');
    }

    /*
     * function to add and update order tracking info
     */

    public function updateTrackingId(Request $request)
    {
        $orderId = $request->input('order_id');
        $trackingId = $request->input('tracking_id');
        if ($orderId != '') {
            $order = $this->storeOrder->find($orderId);
            $order->tracking_id = $trackingId;
            $order->save();
            echo $order;
        }
    }

    /*
     * GET yearly orders
     */

    public function getYearlyOrdersOfStore()
    {
        $data = $this->access_obj->getYearlyOrdersOfStore();
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

    /*
     * GET Dispute Order
     */
    public function dispute_order(Request $request)
    {
        // dd($request);
        $val = false;
        $paid = false;

        $order_id = $request->order_id;
        $status = $request->status;
        $dispute_date = $request->dispute_date;
        $is_refund = $request->is_refund;
        if ($this->current_user_can_update_order($order_id)) {
            $order_information = DB::table('store_orders')->where('order_id', $order_id)->select('total_amount', 'is_refund', 'payment_status', 'store_id', 'buyer_id', 'is_refund')->get()->toArray();
            $buyer_id = $order_information[0]->buyer_id;
            $is_refund = $order_information[0]->is_refund;

            $query_update = DB::table('store_orders')
                ->where('order_id', $order_id)
                ->update(['order_status' => $status, 'refunded_date' => $dispute_date, 'is_refund' => $is_refund]);

            $val = true;
            $userData = getUserByIdchat($buyer_id);

            $description = '';
            $html = view('mail.statusProduct', ['status' => $status]);
            $subject = 'Dispute Your Order NO ' . $order_id;
            $toEmail = $userData->email;
            $toCompletename = $userData->name;
            $params = [
                'from'    => 'MallShark  ' . env('SUPPORT_EMAIL'),
                'to' => $toEmail,
                'subject' => $subject,
                'html' => "$html",
            ];
            $results = $this->mailgun->SendMailgunMail($params);
            addNotifications($buyer_id, getCurrentUserId(), $subject, $description, URL::to('/dispute_order?status=' . $status) . '&selector=' . $order_id);
        }

        if (Session::has('userData')) {
            $userid = Session::get('userData')['user_id'];

            $userData = getUserById($userid);
            $zendeskId = $userData->zendesk_user_id;
            if (empty($zendeskId)) {

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
                } catch (\Zendesk\API\Exceptions\ApiResponseException $e) {
                    echo 'Please check your credentials. Make sure to change the $subdomain, $username, and $token variables in this file.';
                }
                if (isset($zendeskResult)) {
                    $zendeskResult = $zendeskResult->user;
                    $zendId = $zendeskResult->id;
                    $zendIdd = strval($zendId);
                    $zendeskId = $zendIdd;
                    $q = DB::table('users')->where('user_id', '=', $userid)->update(['zendesk_user_id' => $zendIdd]);
                }
            }

            $newTicket = Zendesk::tickets()->create([
                'subject' => 'Order Dispute with ID' . $order_id . '',
                'comment' => [
                    'body' => 'Order Disputed of ' . $userData->name . ' Vendor , Email ' . $userData->email . ' ',
                ],
                'priority' => 'normal',
                'requester_id' => $zendeskId,
                'type' => 'problem',
                'priority' => 'high',
            ]);
        }

        return $val;
    }
    public function dispute_order_cron()
    {
        // $orderData = DB::table('store_orders')->select('refunded_date','is_refund','order_status', 'order_id', 'store_id', 'total_amount')->orderBy('order_id', 'DESC')->get();
        // foreach($orderData as $data){

        //     if(!empty($data->refunded_date)){
        //         //echo $data->refunded_date .'</br>';
        //         // continue;
        //         // die;
        //         $now = date("Y-m-d");
        //         $date = $data->refunded_date;
        //          $startTimeStamp = strtotime($date);
        //          $endTimeStamp = strtotime($now);
        //         $timeDiff = abs($endTimeStamp - $startTimeStamp);
        //         $numberDays = $timeDiff/86400;
        //          $numberDays = intval($numberDays);
        //        // echo $numberDays .'</br>';
        //         //die;
        //         $order_id = $data->order_id;
        //         $status = 'dispute';
        //         // die($numberDays);
        //          if($numberDays == 10 && $data->order_status == 'returned' ){
        //             // die('called');
        //             if ($this->current_user_can_update_order($order_id)) {
        //                 $order_information = DB::table('store_orders')->where('order_id', $order_id)->select('total_amount','is_refund', 'payment_status', 'store_id', 'buyer_id', 'is_refund')->get()->toArray();
        //                 $buyer_id = $order_information[0]->buyer_id;

        //                 $query_update = DB::table('store_orders')
        //                         ->where('order_id', $order_id)
        //                         ->update(['order_status' => $status]);

        //                 $val = true;
        //                 $userData = getUserByIdchat($buyer_id);

        //                 $description = '';
        //                 $html=view('mail.statusProduct', ['status'=>$status]);
        //                 $subject = 'Dispute Your Order NO ' . $order_id;
        //                 $toEmail = $userData->email;
        //                 $toCompletename = $userData->name;
        //                 $params = [ 'from'    => 'sales@mindblaze.net',
        //                 'to'      => $toEmail,
        //                 'subject' => $subject,
        //                 'html'    => "$html",
        //              ];
        //                 $results = $this->mailgun->SendMailgunMail($params);
        //                 addNotifications($buyer_id, getCurrentUserId(), $subject, $description, URL::to('/dispute_order?status=' . $status) . '&selector=' . $order_id);
        //             }
        //             if (Session::has('userData')) {
        //                 $userid = Session::get('userData')['user_id'];

        //                 $userDataByTicket = getUserById($userid);
        //                 $zendeskId = $userDataByTicket->zendesk_user_id;
        //                 if (empty($zendeskId)) {

        //                     try {
        //                         $zendeskResult = Zendesk::users()->create(
        //                             [
        //                             'name' => $userDataByTicket->name,
        //                             'email' => $userDataByTicket->email,
        //                             'phone' => $userDataByTicket->contact_no,
        //                             'role'  => 'end-user',
        //                             'details' => 'This user has been created with the API.'
        //                             ]
        //                         );

        //                     } catch (\Zendesk\API\Exceptions\ApiResponseException $e) {
        //                         echo 'Please check your credentials. Make sure to change the $subdomain, $username, and $token variables in this file.';
        //                     }
        //                     if (isset($zendeskResult)) {
        //                         $zendeskResult = $zendeskResult->user;
        //                         $zendId = $zendeskResult->id;
        //                         $zendIdd = strval($zendId);
        //                         $zendeskId = $zendIdd;
        //                         $q = DB::table('users')->where('user_id', '=', $userid)->update(['zendesk_user_id' => $zendIdd]);
        //                     }
        //                 }

        //                 $newTicket = Zendesk::tickets()->create([
        //                     'subject' => 'Order Dispute with ID'.$order_id.'',
        //                     'comment' => [
        //                         'body' => 'Order Disputed of (Vendor) '.$userDataByTicket->name.' and Email '.$userDataByTicket->email.' ',
        //                     ],
        //                     'priority' => 'normal',
        //                     'requester_id' => $zendeskId,
        //                     'type' => 'problem',
        //                     'priority' => 'high',
        //                 ]);
        //             }

        //          }

        //     }

        // }

        $orderData = DB::table('store_orders')->select('refunded_date', 'is_refund', 'order_status', 'order_id', 'store_id', 'total_amount', 'buyer_id')->orderBy('order_id', 'DESC')->get();
        $store_id_array = array();
        foreach ($orderData as $data) {
            if (!empty($data->refunded_date)) {

                $now = date("Y-m-d");
                $date = $data->refunded_date;
                $startTimeStamp = strtotime($date);
                $endTimeStamp = strtotime($now);
                $timeDiff = abs($endTimeStamp - $startTimeStamp);
                $numberDays = $timeDiff / 86400;
                $numberDays = intval($numberDays);
                $order_id = $data->order_id;
                $userid = $data->buyer_id;
                $status = 'dispute';
                //     // die($numberDays);
                if ($numberDays == 10 && $data->order_status == 'returned') {
                    $store_id_array[] = $data->store_id;

                    $query_update = DB::table('store_orders')
                        ->where('order_id', $order_id)
                        ->update(['order_status' => $status]);

                    $val = true;
                    $userData = getUserByIdchat($userid);

                    $description = '';
                    $html = view('mail.statusProduct', ['status' => $status]);
                    $subject = 'Dispute Your Order NO ' . $order_id;
                    $toEmail = $userData->email;
                    $toCompletename = $userData->name;
                    $params = [
                        'from'    => 'MallShark  ' . env('SUPPORT_EMAIL'),
                        'to' => $toEmail,
                        'subject' => $subject,
                        'html' => "$html",
                    ];
                    $results = $this->mailgun->SendMailgunMail($params);
                    addNotifications($userid, getCurrentUserId(), $subject, $description, URL::to('/dispute_order?status=' . $status) . '&selector=' . $order_id);
                }
            }
        }

        $arrayfinal = array_values(array_filter(array_unique($store_id_array)));
        // print_r($arrayfinal);
        // die;
        if (!empty($arrayfinal)) {

            foreach ($arrayfinal as $arry) {
                $store_id = $arry;

                $userDataByTicket = getUserById($store_id);
                $zendeskId = $userDataByTicket->zendesk_user_id;
                //  echo '$zendeskId: <pre>' .print_r($zendeskId,true). '</pre>'; continue; die;
                if (empty($zendeskId)) {

                    try {
                        $zendeskResult = Zendesk::users()->create(
                            [
                                'name' => $userDataByTicket->name,
                                'email' => $userDataByTicket->email,
                                'phone' => $userDataByTicket->contact_no,
                                'role' => 'end-user',
                                'details' => 'This user has been created with the API.',
                            ]
                        );
                    } catch (\Zendesk\API\Exceptions\ApiResponseException $e) {
                        echo 'Please check your credentials. Make sure to change the $subdomain, $username, and $token variables in this file.';
                    }

                    if (isset($zendeskResult)) {
                        $zendeskResult = $zendeskResult->user;
                        // echo '$zendeskResult: <pre>' .print_r($zendeskResult,true). '</pre>'; continue; die;
                        $zendId = $zendeskResult->id;
                        $zendIdd = strval($zendId);
                        $zendeskId = $zendIdd;
                        $q = DB::table('users')->where('user_id', '=', $store_id)->update(['zendesk_user_id' => $zendIdd]);
                    }
                }

                $newTicket = Zendesk::tickets()->create([
                    'subject' => 'Order Dispute with ID' . $order_id . '',
                    'comment' => [
                        'body' => 'Order Disputed of (Vendor) ' . $userDataByTicket->name . ' and Email ' . $userDataByTicket->email . ' ',
                    ],
                    'priority' => 'normal',
                    'requester_id' => $zendeskId,
                    'type' => 'problem',
                    'priority' => 'high',
                ]);
            }
        }
    }
    public function downLoadFiles($producId)
    {
        $digtialFiles = $this->digtialFiles->where('product_id', $producId)->get();
        if (!empty($digtialFiles)) {
            $url = '';
            $zip_file = time() . '_' . date('Y-m-d') . ".zip";
            $zip = new \ZipArchive();
            $zip->open($zip_file, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);

            $path = public_path('uploads/digital');
            // echo $path;
            $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path));

            foreach ($digtialFiles as $name => $file) {
                $filePath = $path . '/' . $file->file_name;
                $relativePath = substr($filePath, strlen($path) + 1);
                $zip->addFile($filePath, $relativePath);
            }
            $zip->close();
            return response()->download($zip_file)->deleteFileAfterSend(true);
        } else {
            return false;
        }
    }
}
