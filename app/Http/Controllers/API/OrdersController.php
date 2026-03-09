<?php

namespace App\Http\Controllers\API;

use PDF;
use Zendesk;
use Carbon\Carbon;
// use Barryvdh\DomPDF\PDF;
use App\Models\{StoreOrder, StoreEscrow, Product, ProductRefund,DeviceToken};
use App\Models\{DigitalFiles, User};
use App\Services\FCMService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Repositories\User\Useraccess;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\MailgunController;
use App\Repositories\Orders\StoreOrdersAccess;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\API\StripeController;
use Illuminate\Support\Facades\URL;
use App\Constants;


class OrdersController extends Controller
{
    //private $pdf;
    private $access_obj;
    private $access_obj_users;
    private $storeOrder;
    private $mailgun;
    private $digtialFiles;
    private $_request;
    private $stripeObj;
    public function __construct(Request $request)
    {
        $this->access_obj = new StoreOrdersAccess();
        $this->access_obj_users = new Useraccess();
        $this->storeOrder = new StoreOrder();
        $this->mailgun = new MailgunController();
        $this->stripeObj = new StripeController($request);
        $this->digtialFiles = new DigitalFiles();
        $this->_request = $request;
    }

    // public function get_brand_orders()
    // {
    //     if (Auth::user()) {
    //         try {
    //             $userId = Auth::user()->user_id;
    //             $stores = $this->access_obj_users->get_store_info_by_user_id($userId);
    //             if (count($stores) > 0 && $stores != 0) {
    //                 $stores_array = array();
    //                 foreach ($stores as $val) {
    //                     $stores_array[] = $val['store_id'];
    //                 }
    //                 $orders_all = $this->access_obj->get_orders_with_items_allApi($stores_array);
    //                 $completeOrdersArray = [];

    //                 foreach ($orders_all as $store_id => $order_details) {
    //                     foreach ($order_details as $order) {
    //                         $productDetail = $order['order_items_list'][0]['products'];
    //                         $productsArray = [];

    //                         foreach ($productDetail as $product) {
    //                             $products = (array) $product; // Convert the object to an array
    //                             $products['feature_image'] = getAttachmentUrlById($product['feature_image'], 'media');

    //                             $serializedData = $product['gallery'];
    //                             $galleryArray = [];

    //                             if ($serializedData && !empty($serializedData)) {
    //                                 $unserializedArray = unserialize($serializedData);

    //                                 foreach ($unserializedArray as $value) {
    //                                     $galleryImageInfo = [];
    //                                     $galleryImageInfo = getAttachmentUrlById((int) $value, 'media');
    //                                     $galleryArray[] = $galleryImageInfo;
    //                                 }
    //                             }

    //                             $products['gallery'] = $galleryArray;
    //                             $productsArray[] = $products;
    //                         }

    //                         // Add the productsArray to the current order
    //                         $order['order_items_list'][0]['products'] = $productsArray;

    //                         // Add the modified order to the completeOrdersArray
    //                         $completeOrdersArray[] = $order;
    //                     }
    //                 }

    //                 if (count($orders_all) > 0) {

    //                     return response()->json([
    //                         'status' => true,
    //                         'message' => "You successfully get brand orders",
    //                         'orders' => $completeOrdersArray,
    //                     ], 200);
    //                 } else {
    //                     return response()->json([
    //                         'status' => false,
    //                         'message' => "You dont have any Order",
    //                     ], 401);
    //                 }
    //             } else {
    //                 return response()->json([
    //                     'status' => false,
    //                     'message' => "You dont have any store",
    //                 ], 401);
    //             }
    //         } catch (\Throwable $th) {
    //             return response()->json([
    //                 'status' => false,
    //                 'message' => $th->getMessage()
    //             ], 500);
    //         }
    //     }
    // }

  public function getBrandOrdersPaginate(Request $request)
    {
        // dd(12);
        $validateUser = Validator::make(
            $this->_request->all(),
            [
                'status' => 'in:pending,completed,shipped,refunded,dispute,cancelled',
                'order_id' => 'exists:store_orders,order_id',
                'From_Date' => ['date_format:m/d/Y'],
                'To_Date' => ['date_format:m/d/Y', 'after_or_equal:From_Date'],
            ]
        );
        if ($validateUser->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'validation error',
                'errors' => $validateUser->errors()
            ], 404);
        }
        $order_status = $request->input('order_status');
        $order_id =  $request->input('order_id');
        $keyword = $request->input('keyword');
        $From_Date = $request->input('From_Date');
        $To_Date =  $request->input('To_Date');

        $userId = Auth::user()->user_id;
        $store = $this->access_obj_users->getStoreInfoUserId($userId);
        $currentDate = Carbon::today();
        $getTodayNewStoreOrders = StoreOrder::where('store_id', $store['store_id'])->where('charge_status','paid')->whereDate('created_at', $currentDate)->count();
        $getTodayPendingStoreOrders = StoreOrder::where('store_id', $store['store_id'])->where('charge_status','paid')->where('order_status', 'pending')->whereDate('created_at', $currentDate)->count();
        $getTodayRefundReqStoreOrders = StoreOrder::where('store_id', $store['store_id'])->where('charge_status','paid')->where('order_status', 'refunded')->whereDate('created_at', $currentDate)->count();
        $getTodayDisputeStoreOrders = StoreOrder::where('store_id', $store['store_id'])->where('charge_status','paid')->where('order_status', 'dispute')->whereDate('created_at', $currentDate)->count();
        $postedData = $request->all();
        if ($store > 0 && $store != 0) {
            $orders_all = $this->access_obj->get_orders_with_items_all_new_Api($store['store_id'], $order_id, $order_status, $keyword,$From_Date,$To_Date);
        } else {

            return response()->json([
                'status' => false,
                'message' => "Store not exist",
            ], 404);
        }
        if (Auth::user()->user_id) {
            try {
                if (!empty($orders_all)) {
                    // dd($orders_all);
                    $selectedOrders = [];
                    // dd($orders_all);
                    foreach ($orders_all['data'] as $orders) {
                        // return 12;
                        if ($orders['order_status'] == 'cancelled') {
                            $refundProduct = ProductRefund::where('order_id', $orders['order_id'])->first();
                            if (isset($refundProduct)) {
                                $orders['refund_reason_type'] = $refundProduct->reason_type;
                                $orders['refund_reason'] = $refundProduct->reason;
                            }
                        }
                        // return 12;
                        //                      
                        if (isset($orders['user']['user_avatar'])) {
                        $userImageId = $orders['user']['user_avatar'];
                        $userImage = getAttachmentUrlSmallById((int) $userImageId);
                        $orders['user']['user_avatar'] = $userImage;
                        }
                        if ($orders['store_tax'] === null) {
                            $orders['store_tax'] = 0;
                        }
                        // return 12;
                        // dd($orders);
                        $order_items_list = [];
                        // $orders = $orders_all['data'][1];
                        if (isset($orders['order_items_list']) && !empty($orders['order_items_list'])) {
                             $path =  url('downloads/labels/');
                            if(!empty($orders['order_items_list']->tracking_code) && $orders->order_status != 'completed' ){
                                $orders['order_items_list']->label_url = $path.'/'.$orders['order_items_list']->tracking_code.'.png';
                                $orders['order_items_list']->PDF_label_url = $path.'/'.$orders['order_items_list']->tracking_code.'.png';
                            }
                            foreach ($orders['order_items_list'] as $products) {
                                $modifiedProducts = [];
                                if($products['variation_id'] == 0){
                                    foreach ($products['products'] as $product) {
                                        $product['feature_image'] = getAttachmentUrlById(intval($product['feature_image']), 'media');
                                        $modifiedProducts[] = $product;
                                    }
                                    
                                }else if($products['variation_id'] != 0){
                                    $variation_image = DB::table('product_variation')->where('variation_id',$products['variation_id'])->first();
                                    if(isset($variation_image) && !is_null($variation_image)){
                                        foreach ($products['products'] as $product) {
                                            $product['feature_image'] = getAttachmentUrlById(intval($variation_image->product_variation_image), 'media');
                                            $modifiedProducts[] = $product;
                                        }
                                    }else{
                                        foreach ($products['products'] as $product) {
                                            $product['feature_image'] = getAttachmentUrlById(intval($product['feature_image']), 'media');
                                            $modifiedProducts[] = $product;
                                        }
                                    }
                                        
                                }
                                
                                $products['products'] = $modifiedProducts;
                                $order_items_list[] = $products;
                            }
                            $orders['order_items_list'] = $order_items_list;

                            $selectedOrders[] = $orders;
                        }
                        // return 12;

                    }
                    // return 12;
// dd($orders_all['data']);
                    if (!empty($orders_all['data'])) {
                        $orders_all['data'] = $selectedOrders;
                        return response()->json([
                            'status' => true,
                            'message' => "You get all orders",
                            'orderProducts' => $orders_all,
                            'NewOrders' => $getTodayNewStoreOrders,
                            'PendingOrders' => $getTodayPendingStoreOrders,
                            'RefundRequestOrders' => $getTodayRefundReqStoreOrders,
                            'DisputeOrders' => $getTodayDisputeStoreOrders,
                        ]);
                    } else {
                        return response()->json([
                            'status' => false,
                            'message' => "No order found",
                        ], 404);
                    }
                } else {
                    return response()->json([
                        'status' => false,
                        'message' => "No Order Found",
                    ], 404);
                }
            } catch (\Throwable $th) {
                echo $th->getMessage();
            }
            // try {
            //     if (!empty($orders_all['data'])) {
            //         $selectedOrders = [];
            
            //         foreach ($orders_all['data'] as $orders) {
            //             if (isset($orders['order_status']) && $orders['order_status'] == 'cancelled') {
            //                 $refundProduct = ProductRefund::where('order_id', $orders['order_id'])->first();
            
            //                 if (isset($refundProduct)) {
            //                     $orders['refund_reason_type'] = $refundProduct->reason_type;
            //                     $orders['refund_reason'] = $refundProduct->reason;
            //                 }
            //             }
            
            //             if (isset($orders['user']['user_avatar'])) {
            //                 $userImageId = $orders['user']['user_avatar'];
            //                 $userImage = getAttachmentUrlSmallById((int) $userImageId);
            //                 $orders['user']['user_avatar'] = $userImage;
            //             }
            
            //             if ($orders['store_tax'] === null) {
            //                 $orders['store_tax'] = 0;
            //             }
            
            //             $order_items_list = [];
            
            //             if (isset($orders['order_items_list']) && !empty($orders['order_items_list'])) {
            //                 foreach ($orders['order_items_list'] as $products) {
            //                     $modifiedProducts = [];
            
            //                     if (isset($products['products']) && !empty($products['products'])) {
            //                         foreach ($products['products'] as $product) {
            //                             $product['feature_image'] = getAttachmentUrlSmallById((int) $product['feature_image'], 'small');
            //                             $modifiedProducts[] = $product;
            //                         }
            //                         $products['products'] = $modifiedProducts;
            //                         $order_items_list[] = $products;
            //                     }
            //                 }
            //                 $orders['order_items_list'] = $order_items_list;
            //                 $selectedOrders[] = $orders;
            //             }
            //         }
            
            //         if (!empty($selectedOrders)) {
            //             $orders_all['data'] = $selectedOrders;
            //             return response()->json([
            //                 'status' => true,
            //                 'message' => "You get all orders",
            //                 'orderProducts' => $orders_all,
            //                 'NewOrders' => $getTodayNewStoreOrders,
            //                 'PendingOrders' => $getTodayPendingStoreOrders,
            //                 'RefundRequestOrders' => $getTodayRefundReqStoreOrders,
            //                 'DisputeOrders' => $getTodayDisputeStoreOrders,
            //             ]);
            //         } else {
            //             return response()->json([
            //                 'status' => false,
            //                 'message' => "No order found",
            //             ], 401);
            //         }
            //     } else {
            //         return response()->json([
            //             'status' => false,
            //             'message' => "No Order Found",
            //         ], 500);
            //     }
            // } catch (\Throwable $th) {
            //     // Log or handle the exception appropriately
            //     return response()->json([
            //         'status' => false,
            //         'message' => "An error occurred",
            //     ], 500);
            // }
            
        }
    }
    public function getBrandOrders(Request $request)
    {
        // dd(12);
        $validateUser = Validator::make(
            $this->_request->all(),
            [
                'status' => 'in:pending,completed,shipped,refunded,dispute,cancelled',
                'order_id' => 'exists:store_orders,order_id',
            ]
        );
        if ($validateUser->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'validation error',
                'errors' => $validateUser->errors()
            ], 401);
        }
        $order_status = $request->input('status');
        $order_id =  $request->input('order_id');
        $userId = Auth::user()->user_id;
        $store = $this->access_obj_users->getStoreInfoUserId($userId);
        $currentDate = Carbon::today();
        $getTodayNewStoreOrders = StoreOrder::where('store_id', $store['store_id'])->where('charge_status','paid')->whereDate('created_at', $currentDate)->count();
        $getTodayPendingStoreOrders = StoreOrder::where('store_id', $store['store_id'])->where('charge_status','paid')->where('order_status', 'pending')->whereDate('created_at', $currentDate)->count();
        $getTodayRefundReqStoreOrders = StoreOrder::where('store_id', $store['store_id'])->where('charge_status','paid')->where('order_status', 'refunded')->whereDate('created_at', $currentDate)->count();
        $getTodayDisputeStoreOrders = StoreOrder::where('store_id', $store['store_id'])->where('charge_status','paid')->where('order_status', 'dispute')->whereDate('created_at', $currentDate)->count();
        $postedData = $request->all();
        if ($store > 0 && $store != 0) {
            $orders_all = $this->access_obj->get_orders_with_items_all_new_Api_no_pagination($store['store_id'], $order_id, $order_status);
        } else {

            return response()->json([
                'status' => false,
                'message' => "Store not exist",
            ], 401);
        }
        if (Auth::user()->user_id) {
            try {
                if (!empty($orders_all)) {
                    // dd($orders_all);
                    $selectedOrders = [];
                    // dd($orders_all);
                    foreach ($orders_all as $orders) {
                        // return 12;
                        if ($orders['order_status'] == 'cancelled') {
                            $refundProduct = ProductRefund::where('order_id', $orders['order_id'])->first();
                            if (isset($refundProduct)) {
                                $orders['refund_reason_type'] = $refundProduct->reason_type;
                                $orders['refund_reason'] = $refundProduct->reason;
                            }
                        }
                        // return 12;
                        //                      
                        if (isset($orders['user']['user_avatar'])) {
                        $userImageId = $orders['user']['user_avatar'];
                        $userImage = getAttachmentUrlSmallById((int) $userImageId);
                        $orders['user']['user_avatar'] = $userImage;
                        }
                        if ($orders['store_tax'] === null) {
                            $orders['store_tax'] = 0;
                        }
                        // return 12;
                        // dd($orders);
                        $order_items_list = [];
                        // $orders = $orders_all['data'][1];
                        if (isset($orders['order_items_list']) && !empty($orders['order_items_list'])) {
                             $path =  url('downloads/labels/');
                            if(!empty($orders['order_items_list']->tracking_code) && $orders->order_status != 'completed' ){
                                $orders['order_items_list']->label_url = $path.'/'.$orders['order_items_list']->tracking_code.'.png';
                                $orders['order_items_list']->PDF_label_url = $path.'/'.$orders['order_items_list']->tracking_code.'.png';
                            }
                            foreach ($orders['order_items_list'] as $products) {
                                $modifiedProducts = [];
                                if($products['variation_id'] == 0){
                                    foreach ($products['products'] as $product) {
                                        $product['feature_image'] = getAttachmentUrlById(intval($product['feature_image']), 'media');
                                        $modifiedProducts[] = $product;
                                    }
                                    
                                }else if($products['variation_id'] != 0){
                                    $variation_image = DB::table('product_variation')->where('variation_id',$products['variation_id'])->first();
                                    if(isset($variation_image) && !is_null($variation_image)){
                                        foreach ($products['products'] as $product) {
                                            $product['feature_image'] = getAttachmentUrlById(intval($variation_image->product_variation_image), 'media');
                                            $modifiedProducts[] = $product;
                                        }
                                    }else{
                                        foreach ($products['products'] as $product) {
                                            $product['feature_image'] = getAttachmentUrlById(intval($product['feature_image']), 'media');
                                            $modifiedProducts[] = $product;
                                        }
                                    }
                                        
                                }
                                
                                $products['products'] = $modifiedProducts;
                                $order_items_list[] = $products;
                            }
                            $orders['order_items_list'] = $order_items_list;
                            $selectedOrders[] = $orders;
                        }
                        // return 12;

                    }
                    // return 12;

                    if (!empty($orders_all)) {
                        $orders_all = $selectedOrders;
                        return response()->json([
                            'status' => true,
                            'message' => "You get all orders",
                            'orderProducts' => $orders_all,
                            'NewOrders' => $getTodayNewStoreOrders,
                            'PendingOrders' => $getTodayPendingStoreOrders,
                            'RefundRequestOrders' => $getTodayRefundReqStoreOrders,
                            'DisputeOrders' => $getTodayDisputeStoreOrders,
                        ]);
                    } else {
                        return response()->json([
                            'status' => false,
                            'message' => "No order found",
                        ], 401);
                    }
                } else {
                    return response()->json([
                        'status' => false,
                        'message' => "No Order Found",
                    ], 500);
                }
            } catch (\Throwable $th) {
                echo $th->getMessage();
            }
            // try {
            //     if (!empty($orders_all['data'])) {
            //         $selectedOrders = [];
            
            //         foreach ($orders_all['data'] as $orders) {
            //             if (isset($orders['order_status']) && $orders['order_status'] == 'cancelled') {
            //                 $refundProduct = ProductRefund::where('order_id', $orders['order_id'])->first();
            
            //                 if (isset($refundProduct)) {
            //                     $orders['refund_reason_type'] = $refundProduct->reason_type;
            //                     $orders['refund_reason'] = $refundProduct->reason;
            //                 }
            //             }
            
            //             if (isset($orders['user']['user_avatar'])) {
            //                 $userImageId = $orders['user']['user_avatar'];
            //                 $userImage = getAttachmentUrlSmallById((int) $userImageId);
            //                 $orders['user']['user_avatar'] = $userImage;
            //             }
            
            //             if ($orders['store_tax'] === null) {
            //                 $orders['store_tax'] = 0;
            //             }
            
            //             $order_items_list = [];
            
            //             if (isset($orders['order_items_list']) && !empty($orders['order_items_list'])) {
            //                 foreach ($orders['order_items_list'] as $products) {
            //                     $modifiedProducts = [];
            
            //                     if (isset($products['products']) && !empty($products['products'])) {
            //                         foreach ($products['products'] as $product) {
            //                             $product['feature_image'] = getAttachmentUrlSmallById((int) $product['feature_image'], 'small');
            //                             $modifiedProducts[] = $product;
            //                         }
            //                         $products['products'] = $modifiedProducts;
            //                         $order_items_list[] = $products;
            //                     }
            //                 }
            //                 $orders['order_items_list'] = $order_items_list;
            //                 $selectedOrders[] = $orders;
            //             }
            //         }
            
            //         if (!empty($selectedOrders)) {
            //             $orders_all['data'] = $selectedOrders;
            //             return response()->json([
            //                 'status' => true,
            //                 'message' => "You get all orders",
            //                 'orderProducts' => $orders_all,
            //                 'NewOrders' => $getTodayNewStoreOrders,
            //                 'PendingOrders' => $getTodayPendingStoreOrders,
            //                 'RefundRequestOrders' => $getTodayRefundReqStoreOrders,
            //                 'DisputeOrders' => $getTodayDisputeStoreOrders,
            //             ]);
            //         } else {
            //             return response()->json([
            //                 'status' => false,
            //                 'message' => "No order found",
            //             ], 401);
            //         }
            //     } else {
            //         return response()->json([
            //             'status' => false,
            //             'message' => "No Order Found",
            //         ], 500);
            //     }
            // } catch (\Throwable $th) {
            //     // Log or handle the exception appropriately
            //     return response()->json([
            //         'status' => false,
            //         'message' => "An error occurred",
            //     ], 500);
            // }
            
        }
    }
    public function brandOrderdetails($order_id)
    {

        if (!$order_id) {
            return response()->json([
                'status' => false,
                'message' => 'order_id parameter is missing',
            ], 400);
        }

        try {

            $orderDetails = $this->access_obj->getOrderDetailByOrderIdApi($order_id);
            $selectedOrders = [];
            if (isset($orderDetails) && !empty($orderDetails)) {
                foreach ($orderDetails as $orders) {
                    $order_items_list = [];
                    foreach ($orders['order_items_list'] as $products) {
                        $modifiedProducts = [];
                        foreach ($products['products'] as $product) {
                            $product['feature_image'] = getAttachmentUrlById((int) $product['feature_image'], 'media');
                            $modifiedProducts[] = $product;
                        }
                        $products['products'] = $modifiedProducts;
                        $order_items_list[] = $products;
                    }

                    $orders['order_items_list'] = $order_items_list;

                    $orders['totolAmount'] = $orders['total_amount'];
                    $orders['shippingAmount'] = $orders['shipping_amount'];
                    $orders['orderPrice'] = $orders['total_amount'] - $orders['shipping_amount'];
                    $selectedOrders[] = $orders;
                }
            }


            if ($orderDetails) {
                return response()->json([
                    'status' => true,
                    'message' => "You get orders",
                    'orderProducts' => $selectedOrders,
                ]);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => "No Order Found",
                ], 500);
            }
        } catch (\Throwable $th) {

            return response()->json([
                'error' => $th->getMessage(),
            ], 500);
        }
    }

    public function get_store_order(Request $request)
    {

        if (Auth::user()) {
            $mesage = '';
            $active = '#home';
            $input = $request->all();
            $order_status = $request->input('status');
            // echo '$input: <pre>' .print_r($input,true). '</pre>';

            // die();

            if (isset($input['purpose']) && $input['purpose'] == 'refund' && isset($input['order_id']) && $input['order_id'] != '') {
                $active = '#' . $input['active_tab'];
                $mesage = 'Refund is not successfull please contact to super admin';
            }
            try {
                $userId = Auth::user()->user_id;

                $stores = $this->access_obj_users->get_store_info_by_user_id($userId);

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
                    // echo '$orders_all: <pre>' . print_r($orders_all, true) . '</pre>';
                    // die();
                    return response()->json([
                        'status' => true,
                        'message' => "User Data",
                        'orders_all' => $orders_all,
                        'orders_all_refund_cancel' => $orders_all_refund_cancel,
                        'mesage' => $mesage,
                        'active' => $active,
                    ], 200);
                } else {
                    return response()->json([
                        'status' => false,
                        'message' => "You dont have any store",

                    ], 401);
                }
            } catch (\Throwable $th) {
                return response()->json([
                    'status' => false,
                    'message' => $th->getMessage()
                ], 500);
            }
        }
    }

    public function updateOrderStatus()
    {

        $validateUser = Validator::make(
            $this->_request->all(),
            [
                'status' => 'required|in:refunded,cancelled,shipped',
                'order_id' => 'required|exists:store_orders,order_id',
            ]
        );
        if ($validateUser->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'validation error',
                'errors' => $validateUser->errors()
            ], 401);
        }

        $input = $this->_request->all();
        if (isset($input['status']) && $input['status'] != '' && isset($input['order_id']) && $input['order_id'] != '') {
            try {
                $store_orders = StoreOrder::with('orderItems')->where('order_id', $input['order_id'])->first();
                $ship_id = [];
                if ($input['status'] == 'cancelled' && $store_orders->payment_method == 'stripe') {
                    $input['payment_method'] = $store_orders->method;
                    $input['is_refund'] = 'vandor';
                }
                if ($input['status'] == 'cancelled' && $store_orders->payment_method == 'crypto') {
                    $input['payment_method'] = $store_orders->method;
                    $input['is_refund'] = 'vandor';
                }
                if ($input['status'] == 'shipped') {


                    foreach ($store_orders->orderItems as $order_items) {
                        $obj = [];
                        $obj[$order_items->payment_service] = [
                            'shipping_id' => $order_items->shipment_id,
                            'order_item_id' => $order_items->order_item_id,
                            'payment_method' => $order_items->method,
                        ];
                        $ship_id_json = json_encode($obj);
                        $ship_id[] = $ship_id_json;
                    }
                    $ship_id = implode(", ", $ship_id);
                    $input['ship_id'] = $ship_id;
                }
                $result = $this->access_obj->updateOrderApi($input);
                if ($result) {
                    $orderNotification = [];
                    if ($input['status'] == 'cancelled' && $input['shipped_cancel'] == 0) {
                        $orderNotification['status'] = 'cancelled';
                    } elseif ($input['status'] == 'cancelled' && $input['shipped_cancel'] == 1) {
                        $orderNotification['status'] = 'refunded';
                    } else {
                        $orderNotification['status'] = 'shipped';
                    }
                    $orderNotification['buyer_id'] = $store_orders->buyer_id;
                    $orderNotification['order_id'] = $input['order_id'];
                    $this->orderStatusNotification($orderNotification);
                    return response()->json([
                        'status' => true,
                        'message' => "Status updated successfully",

                    ], 200);
                } else {
                    return response()->json([
                        'status' => false,
                        'message' => "Status is not changed please contact to super admin",

                    ], 401);
                }
            } catch (\Throwable $th) {
                return response()->json([
                    'status' => false,
                    'message' => $th->getMessage()
                ], 500);
            }
        }
    }
    public function getStoreOrders()
    {
        // if($this->_request->days == 7){
        //     $days = now()->subDays(7);
        // }elseif ($this->_request->days == 30) {
        //     $days = now()->subDays(30);
        // }elseif ($this->_request->days == 90) {
        //     $days = now()->subDays(90);

        // }else{
        //     $days = today();
        // }

        $user = auth()->user();
        $store = getStoreInfoByUserid($user->user_id);
        $todayNewOrders = StoreOrder::where('store_id', $store->store_id)->where('charge_status','paid')->whereDate('created_at', today())->count();
        $todayOrders['todayNewOrders'] = $todayNewOrders;
        $todayUnitsSold = StoreOrder::where('order_status', 'completed')->where('store_id', $store->store_id)->whereDate('created_at', today())->select('total_items', 'total_amount')->get();
        $todayOrders['units'] = 0;
        $todayOrders['totalRevenue'] = 0;
        foreach ($todayUnitsSold as $unit) {
            $todayOrders['units'] += $unit->total_items;
            $amount = number_format((float)$unit->total_amount, 2, '.', '');
            $todayOrders['totalRevenue'] += $amount;
        }
        $todayOrders['totalRevenue'] = '$' . number_format((float)$todayOrders['totalRevenue'], 2, '.', '');
        $sevenDaysorders = StoreOrder::where('order_status', 'completed')->where('store_id', $store->store_id)->whereDate('created_at', '>=', now()->subDays(7))->select('total_items', 'total_amount')->get();
        $thirtyDaysOrders = StoreOrder::where('order_status', 'completed')->where('store_id', $store->store_id)->whereDate('created_at', '>=', now()->subDays(30))->select('total_items', 'total_amount')->get();
        $nintyDaysOrders = StoreOrder::where('order_status', 'completed')->where('store_id', $store->store_id)->whereDate('created_at', '>=', now()->subDays(90))->select('total_items', 'total_amount')->get();
        // seven days order
        $sevenDaysOrder = [];
        $sevenDaysOrder['totalRevenue'] = 0;
        $sevenDaysOrder['total_items'] = 0;
        $sevenDaysOrder['ordersCount'] = count($sevenDaysorders);
        foreach ($sevenDaysorders as $order) {
            $sevenDaysOrder['total_items'] += $order->total_items;
            $amount = number_format((float)$order->total_amount, 2, '.', '');
            $sevenDaysOrder['totalRevenue'] += $amount;
        }
        $sevenDaysOrder['totalRevenue'] = number_format((float)$sevenDaysOrder['totalRevenue'], 2, '.', '');
        $sevenDaysOrder['averageRevenuePerDay'] = $sevenDaysOrder['totalRevenue'] / 7;
        $sevenDaysOrder['averageRevenuePerDay'] = '$' . number_format((float)$sevenDaysOrder['averageRevenuePerDay'], 2, '.', '');
        $sevenDaysOrder['totalRevenue'] = '$' . $sevenDaysOrder['totalRevenue'];
        //thirty Days Order
        $thirtyDaysOrder = [];
        $thirtyDaysOrder['totalRevenue'] = 0;
        $thirtyDaysOrder['total_items'] = 0;
        $thirtyDaysOrder['ordersCount'] = count($thirtyDaysOrders);
        foreach ($thirtyDaysOrders as $order) {
            $thirtyDaysOrder['total_items'] += $order->total_items;
            $amount = number_format((float)$order->total_amount, 2, '.', '');
            $thirtyDaysOrder['totalRevenue'] += $amount;
        }
        $thirtyDaysOrder['totalRevenue'] = number_format((float)$thirtyDaysOrder['totalRevenue'], 2, '.', '');
        $thirtyDaysOrder['averageRevenuePerDay'] = $thirtyDaysOrder['totalRevenue'] / 30;
        $thirtyDaysOrder['averageRevenuePerDay'] = '$' . number_format((float)$thirtyDaysOrder['averageRevenuePerDay'], 2, '.', '');
        $thirtyDaysOrder['totalRevenue'] = '$' . $thirtyDaysOrder['totalRevenue'];
        // 90 days orders
        $nintyDaysOrder = [];
        $nintyDaysOrder['totalRevenue'] = 0;
        $nintyDaysOrder['total_items'] = 0;
        $nintyDaysOrder['ordersCount'] = count($nintyDaysOrders);
        foreach ($nintyDaysOrders as $order) {
            $nintyDaysOrder['total_items'] += $order->total_items;
            $amount = number_format((float)$order->total_amount, 2, '.', '');
            $nintyDaysOrder['totalRevenue'] += $amount;
        }
        $nintyDaysOrder['totalRevenue'] = number_format((float)$nintyDaysOrder['totalRevenue'], 2, '.', '');
        $nintyDaysOrder['averageRevenuePerDay'] = $nintyDaysOrder['totalRevenue'] / 90;
        $nintyDaysOrder['averageRevenuePerDay'] = '$' . number_format((float)$nintyDaysOrder['averageRevenuePerDay'], 2, '.', '');
        $nintyDaysOrder['totalRevenue'] = '$' . $nintyDaysOrder['totalRevenue'];
        return response()->json([
            'status' => 200,
            'success' => true,
            'todayOrders' => $todayOrders,
            'Data' => ['seven_days' => $sevenDaysOrder, 'thirty_days' => $thirtyDaysOrder, 'ninty_days' => $nintyDaysOrder],

        ]);
    }
    // public function searchOrders()
    // {   $user = auth()->user();
    //     $store = getStoreInfoByUserid($user->user_id);
    //     $search = $this->_request->input('search');
    //     $search_order = StoreOrder::with('user','orderItemsList','shippingAddress')
    //         ->where(function ($query) use ($search) {
    //             $query->whereHas('user', function ($userQuery) use ($search) {
    //                 $userQuery->where('name', 'like', "%{$search}%");
    //             })->orWhere('order_id', 'like', "%{$search}%");
    //         })->paginate(1);
    //         dd($search_order);
    //         if ($search_order === null && json_last_error() !== JSON_ERROR_NONE) {
    //             // Handle decoding error
    //             echo "JSON decoding error: " . json_last_error_msg();
    //         } else {
    //             // Access the 'data' key
    //             $data = $search_order['data'];
    //             dd($data);
    //         }
    //         if (!empty($orders_all)) {
    //             $selectedOrders = [];
    //             foreach ($orders_all['data'] as $orders) {
    //                 dd($orders);
    //                 $userImageId = $orders['user']['user_avatar'];
    //                 $userImage = getAttachmentUrlById((int) $userImageId);
    //                 $orders['user']['user_avatar'] = $userImage;
    //                 if ($orders['store_tax'] == null) {
    //                     $orders['store_tax'] = 0;
    //                 }
    //                 $order_items_list = [];
    //                 foreach ($orders['orderItemsList'] as $products) {
    //                     $modifiedProducts = [];
    //                     foreach ($products['products'] as $product) {
    //                         $product['feature_image'] = getAttachmentUrlById((int) $product['feature_image'], 'media');
    //                         $modifiedProducts[] = $product;
    //                     }
    //                     $products['products'] = $modifiedProducts;
    //                     $order_items_list[] = $products;
    //                 }
    //                 $orders['order_items_list'] = $order_items_list;
    //                 $selectedOrders[] = $orders;
    //             }
    //             $orders_all['data'] = $selectedOrders;
    //             if (!empty($orders_all['data'])) {
    //                 return response()->json([
    //                     'status' => true,
    //                     'message' => "You get all orders",
    //                     'orderProducts' => $orders_all,
    //                 ]);
    //             }
    //         }else{
    //             return response()->json([
    //                 'status' => false,
    //                 'message' => "you have no order",
    //             ], 401);
    //         }
    // }
    public function searchOrders()
    {
        $user = auth()->user();
        $store = getStoreInfoByUserid($user->user_id);
        $search = $this->_request->input('search');

        $search_orders = StoreOrder::with('user', 'orderItemsList', 'shippingAddress')
            ->where('store_id', $store->store_id)
            ->where(function ($query) use ($search) {
                $query->whereHas('user', function ($userQuery) use ($search) {
                    $userQuery->where('name', 'like', "%{$search}%");
                })->orWhere('order_id', 'like', "%{$search}%");
            })->paginate(10);
        if (!is_null($search_orders)) {
            $selectedOrders = [];
            foreach ($search_orders->items() as $orders) {
                $userImageId = $orders['user']['user_avatar'];
                $userImage = getAttachmentUrlById((int) $userImageId);
                $orders['user']['user_avatar'] = $userImage;

                if ($orders['store_tax'] === null) {
                    $orders['store_tax'] = 0;
                }

                $order_items_list = [];
                foreach ($orders['orderItemsList'] as $products) {
                    $modifiedProducts = [];
                    foreach ($products['products'] as $product) {
                        $product['feature_image'] = getAttachmentUrlById((int) $product['feature_image'], 'media');
                        $modifiedProducts[] = $product;
                    }
                    $products['products'] = $modifiedProducts;
                    $order_items_list[] = $products;
                }

                $orders['order_items_list'] = $order_items_list;
                $selectedOrders = $orders;
            }
            return response()->json([
                'status' => true,
                'message' => "You get all orders",
                'orderProducts' => $search_orders,

            ]);
        } else {
            return response()->json([
                'status' => false,
                'message' => "No matching orders found",
            ], 404);
        }
    }
    public function return_refund_stripe(Request $request)
    {

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
                  
                    $response =   $this->stripeObj->refundBack($request['order_id']);
                     
                    if ($response) {
                        
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

                            $variableData = getvariableByProduct_id($product_id);
                           
                        
                            $productArray[] = [
                               'title' => $productData->title,
                                'slug' => $productData->slug,
                                'feature_image' => $productData->feature_image,
                                'product_type' => $productData->product_type,
                                'price_total' => $value->total,
                                'price' => $value->price,
                                'quantity' => $value->quantity,
                                'sold_price'=> $value->sold_price,
                                'shipping_amount'=>$value->shipping_amount,
                            ];  
                        }
                      
                  
                        $html = view('mail.order-update-client-refunded', ['userData' => $userData, "shippingAddress" => $usershipping, 'orderItems' => $productArray,'orderId'=>$request['order_id']]);

                        $subject = 'Your MallShark Order Has Been Refunded';
                        $toEmail = $email;
                        $toCompletename = $name;
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
                        // if(isset($storeUser_id) && $storeUser_id != ''){
                        //     $userData = User::with('device_token')->findOrFail($storeUser_id);
                        //     if ($userData) {
                        //         $tokens = [];
                        //         if(isset($userData->device_token) && !is_null($userData->device_token)){
                        //             foreach ($userData->device_token as $device_token_obj) {
                        //                 // dd($device_token_obj->fcm_token);
                                    
                                    
                        //             $subject = $subjectStore;
                        //             $description =  $storeDescription;
                        //             // foreach($tokens as $token){
                        //                 $notification = FCMService::send(
                        //                     $device_token_obj->fcm_token,
                        //                     [
                        //                         'title' => $subject,
                        //                         'body' => $description,
                        //                     ]
                        //                 );
                
                        //                 // addNotificationsApi($get_user_id, $senderId = '', $subject, $description, $accunt_url = '');
                        //             }
                        //         }
                                
                        //         addNotifications($storeUser_id, $buyer_id, $subject . $request['order_id'], $description, URL::to('/brand-order-details/'.$request['order_id']));
            
                        //     }
                        // }
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
            
                                    // addNotificationsApi($get_user_id, $senderId = '', $subject, $description, $accunt_url = '');
                                }
                            }
            
                            }
                            addNotifications($buyer_id,$storeUser_id, $subjectStore, '', URL::to('new-buyer-orders-detail/'.$request['order_id']),'buyer_order_details',$request['order_id'],'buyer');
                        }
                        // addNotifications($buyer_id, $storeUser_id, $subjectStore . $request['order_id'], $description, URL::to('new-buyer-orders-detail/' . $request['order_id']));
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
            $orderNotification = [];
            $orderNotification['buyer_id'] = $StoreOrder->buyer_id;
            $orderNotification['order_id'] = $StoreOrder->order_id;
            $orderNotification['status'] = $StoreOrder->order_status;
            $this->orderStatusNotification($orderNotification);
            return response()->json([
                'status' => true,
                'message' => 'Order has been refunded',
            ]);
            // DB::commit();
        } catch (\Throwable $e) {
            // DB::rollback();
            echo 'Caught exception: ', $e->getMessage(), "\n";
        }
    }

    public function rejectOrderStoreOwner(Request $request)
    {
        $val = false;
        $buyer_id = $request['buyer_id'];
        $order_id = $request['order_id'];
        $reason_for_reject = $request['reject_order_reason'] ? $request['reject_order_reason'] : "";
        $StoreOrder = StoreOrder::find($order_id);
        $storeData = $StoreOrder->store;
        $status = $request['order_status'];

        if ($request) {
            try {
                if (!empty($request->refund_product_id) && is_array($request->refund_product_id)) {
                foreach ($request->refund_product_id as $product) {
                    if ($product === null) {
                        return response()->json([
                            'status' => true,
                            'message' => 'you have no product in this order'
                        ]);
                    } else {
                        $productRefund = ProductRefund::where('product_id', $product)->where('order_id',$order_id)->first(); //shold check where order_id  as well
                        $productRefund->is_reject = $request['is_reject'] ? $request['is_reject'] : "";
                        $productRefund->is_refund = $request['is_refund'] ? $request['is_refund'] : "";
                        $productRefund->reject_order_reason = $request['reject_order_reason'] ? $request['reject_order_reason'] : "";
                        $productRefund->save();
                    }
                }}

                $StoreOrder = StoreOrder::find($request['order_id']);
                $StoreOrder->order_status = $request['order_status'] ? $request['order_status'] : "";
                $StoreOrder->reject_date = $request['reject_date'] ? $request['reject_date'] : "";
                $StoreOrder->is_refund = $request['is_refund'] ? $request['is_refund'] : "";
                $StoreOrder->is_reject = $request['is_reject'] ? $request['is_reject'] : "";
                $StoreOrder->save();

                $val = true;
                $buyerData = getUserByIdchat($buyer_id);
                $userid = auth()->user()->user_id;
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
                $html = view('mail.order_refund_request_rejected_client', ['buyerName' => $buyerName, 'order_id' => $order_id, 'mallsharkstorename' => $mallsharkstorename, 'mallsharkstoreslug' => $mallsharkstoreslug, 'storeName' => $storeName, 'productInfo' => $request->refund_product_title, 'status' => $status, 'storeData' => $storeData, 'customerDetail' => $StoreOrder->user]);
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
    
                            // addNotificationsApi($get_user_id, $senderId = '', $subject, $description, $accunt_url = '');
                        }
                    }
                addNotifications($buyer_id, getCurrentUserId(), $subject, $description, URL::to('new-buyer-orders-detail/' . $order_id),'buyer_order_details',$order_id,'buyer');
    
                    }
                }
                // create ticket zendesk start
                if (!is_null($userid)) {
                    // $userid = Session::get('userData')['user_id'];

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
                    $newTicket = Zendesk::tickets()->create([
                        'subject' => 'Order Dispute with ID' . $order_id . '',
                        'comment' => [
                            'body' => 'The order for ' . $userData->name . ' Vendor has been disputed, Email ' . $userData->email . ' ' . $reason_for_reject . ' ',
                        ],
                        'priority' => 'normal',
                        'requester_id' => $zendeskId,
                        'type' => 'problem',
                        'priority' => 'high',
                    ]);
                }

                // create ticket zendesk end
                // return Redirect::back()->withSuccess(['msg' => 'Your Order is Disputed. Superadmin check this order']);
                // $responseData = ['msg' => 'Your Order is Disputed. Superadmin check this order'];
                $orderNotification = [];
                $orderNotification['buyer_id'] = $StoreOrder->buyer_id;
                $orderNotification['order_id'] = $StoreOrder->order_id;
                $orderNotification['status'] = $StoreOrder->order_status;
                // $this->orderStatusNotification($orderNotification);
                return response()->json([
                    'status' => true,
                    'message' => 'Your Order is Disputed. Superadmin will check this order'
                ]);
            } catch (Throwable $e) {
                return response()->json([
                    'status' => false,
                    'message' => $e->getMessage()
                ]);
                // echo 'Caught exception: ', $e->getMessage(), "\n";
            }
        }
    }
    public function orderStatusNotification($orderNotification)
    {
        $user = User::with('device_token')->findOrFail($orderNotification['buyer_id']);
        if ($user) {
            $tokens = [];
            foreach ($user->device_token as $device_token_obj) {
                array_push($tokens, $device_token_obj->fcm_token);
            }
            $notification = FCMService::send(
                $tokens,
                [
                    'title' => 'Order Update',
                    'body' => 'Order' . $orderNotification['order_id'] . 'status changed to' . $orderNotification['status'],
                ]
            );
            if ($notification) {
            }
        }
    }
    public function getOrderPdf($orderId)
    {
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
        // $pdf = PDF::loadView('orders.invoice', array('ordersdata' => $data));
        return response()->json([
            'status' => true,
            'data' => $data
        ]);
    }
}
