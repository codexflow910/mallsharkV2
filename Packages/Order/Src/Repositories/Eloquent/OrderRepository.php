<?php

namespace Packages\Order\Repositories\Eloquent;
use Zendesk;
use Exception;
use App\Constants;
use Carbon\Carbon;
use Packages\Store\Models\{Store,StoreEscrow};
use Packages\Auth\Models\{DeviceToken,User};
use Packages\Home\Models\{ShippingAddress};
use Packages\Product\Models\{Product,DigitalFiles};
use App\Services\FCMService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Auth;
use Packages\Order\Models\{StoreOrder,ProductRefund};
use Packages\Core\Contracts\CoreHelpers;
use Packages\Order\Contracts\OrderHelpers;
use App\Http\Controllers\MailgunController;
use Illuminate\Support\Arr;
use Illuminate\Support\ItemNotFoundException;
use Illuminate\Validation\UnauthorizedException;
use Packages\Order\Contracts\BaseApiResponse;
use App\Http\Controllers\API\StripeController;
use Packages\Order\Contracts\OrderRepositoryInterface;

class OrderRepository implements OrderRepositoryInterface
{
    use BaseApiResponse;
    use OrderHelpers;
    use CoreHelpers;
    private $stripeObj;
    private $mailgun;
    public function __construct(StoreOrder $model, Request $request)
    {
        $this->model = $model;
        $this->stripeObj = new StripeController($request);
        $this->mailgun = new MailgunController();
    }
    public function getBuyerOrders($orderId = null, $orderStatus = null)
    {
        try {
            $userId = Auth::user()->user_id;
            $query = StoreOrder::with('orderItemsList.review', 'user', 'shippingAddress')
                ->where('charge_status', 'paid')
                ->where('buyer_id', $userId);

            // Apply filters based on orderId and order_status
            if ($orderId) {
                $query->where('order_id', $orderId);
            }

            if ($orderStatus) {
                $query->where('order_status', $orderStatus);
            }

            $orders = $query->get();
      
            return $orders->toArray();
        } catch (\Exception $e) {
            throw new ItemNotFoundException("No orders found matching your criteria.");
        }
    }

    public function getStoreInfoByUserId($userId)
    {
        try {
            $store = Store::where('user_id', $userId)->first();

            if ($store) {
                return $store->toArray();
            } else {
                return 0;
            }
        } catch (\Exception $e) {
            throw new ItemNotFoundException("Store not found.");
        }
    }

    public function getTodayNewOrders($storeId, $currentDate)
    {
        return StoreOrder::where('store_id', $storeId)
            ->where('charge_status', 'paid')
            ->whereDate('created_at', $currentDate)
            ->count();
    }

    public function getTodayPendingOrders($storeId, $currentDate)
    {
        return StoreOrder::where('store_id', $storeId)
            ->where('charge_status', 'paid')
            ->where('order_status', 'pending')
            ->whereDate('created_at', $currentDate)
            ->count();
    }

    public function getTodayRefundedOrders($storeId, $currentDate)
    {
        return StoreOrder::where('store_id', $storeId)
            ->where('charge_status', 'paid')
            ->where('order_status', 'refunded')
            ->whereDate('created_at', $currentDate)
            ->count();
    }

    public function getTodayDisputeOrders($storeId, $currentDate)
    {
        return StoreOrder::where('store_id', $storeId)
            ->where('charge_status', 'paid')
            ->where('order_status', 'dispute')
            ->whereDate('created_at', $currentDate)
            ->count();
    }

    public function getOrdersWithItems($store_id, $order_id = null, $order_status = null, $keyword = null, $zipcode = null, $From_Date = null, $To_Date = null)
    {
        try {
            $query = StoreOrder::with('orderItemsList', 'user', 'shippingAddress')
                ->where('charge_status', 'paid')
                ->where('store_id', $store_id);

            // Handle date filtering
            if ($From_Date && $To_Date) {
                $From_Date = \Carbon\Carbon::createFromFormat('m/d/Y', $From_Date)->format('Y-m-d');
                $To_Date = \Carbon\Carbon::createFromFormat('m/d/Y', $To_Date)->format('Y-m-d');
                $query->whereBetween('created_at', [$From_Date . ' 00:00:00', $To_Date . ' 23:59:59']);
            }

            // Handle keyword filtering
            if ($keyword !== null) {
                if (is_numeric($keyword) && (int)$keyword == $keyword) {
                    $orderId = (int)$keyword;

                    // Check if any orders match the given order_id
                    $storeOrder = $query->where('order_id', $orderId)->paginate(10);

                    // If matching orders are found, return them
                    if ($storeOrder->total() > 0) {
                        return $storeOrder->toArray();
                    }
                }

                $query->where(function ($q) use ($keyword) {
                    $q->whereHas('user', function ($userQuery) use ($keyword) {
                        $userQuery->where('email', 'LIKE', "%{$keyword}%")
                            ->orWhere('name', 'LIKE', "%{$keyword}%");
                    })->orWhereHas('orderItemsList', function ($orderItemQuery) use ($keyword) {
                        $orderItemQuery->whereHas('products', function ($productQuery) use ($keyword) {
                            $productQuery->where('title', 'LIKE', "%{$keyword}%");
                        });
                    });
                });
            }

            // Handle order_id filtering
            if (!empty($order_id)) {
                $query->where('order_id', $order_id);
            }

            // Handle zipcode filtering
            if (!empty($zipcode)) {
                $query->whereHas('shippingAddress', function ($addressQuery) use ($zipcode) {
                    $addressQuery->where('post_code', $zipcode);
                });
            }

            // Handle order_status filtering
            if ($order_status !== null) {
                $query->where('order_status', $order_status);
            }

            $storeOrders = $query->orderBy('created_at', 'desc')->paginate(10);

            // Modify the response to format price values
            $storeOrdersArray = $storeOrders->toArray();

            foreach ($storeOrdersArray['data'] as &$order) {
                // Format order-level prices dynamically
                $order['total_amount'] = is_numeric($order['total_amount'])
                    ? (fmod($order['total_amount'], 1) == 0 ? intval($order['total_amount']) : number_format($order['total_amount'], 2, '.', ''))
                    : '0';


                $order['shipping_amount'] = is_numeric($order['shipping_amount'])
                    ? (fmod($order['shipping_amount'], 1) == 0 ? intval($order['shipping_amount']) : number_format($order['shipping_amount'], 2, '.', ''))
                    : '0';

                $order['store_tax'] = is_numeric($order['store_tax'])
                    ? (fmod($order['store_tax'], 1) == 0 ? intval($order['store_tax']) : number_format($order['store_tax'], 2, '.', ''))
                    : '0';


                // Iterate over order items safely
                if (isset($order['order_items_list']) && is_array($order['order_items_list'])) {
                    foreach ($order['order_items_list'] as &$item) {
                        $item['price'] = is_numeric($item['price'])
                            ? (fmod($item['price'], 1) == 0 ? intval($item['price']) : number_format($item['price'], 2, '.', ''))
                            : '0';

                        $item['sold_price'] = is_numeric($item['sold_price'])
                            ? (fmod($item['sold_price'], 1) == 0 ? intval($item['sold_price']) : number_format($item['sold_price'], 2, '.', ''))
                            : '0';
                        $item['shipping_amount'] = is_numeric($item['shipping_amount'])
                            ? (fmod($item['shipping_amount'], 1) == 0 ? intval($item['shipping_amount']) : number_format($item['shipping_amount'], 2, '.', ''))
                            : '0';


                        $item['total'] = is_numeric($item['total'])
                            ? (fmod($item['total'], 1) == 0 ? intval($item['total']) : number_format($item['total'], 2, '.', ''))
                            : '0';
                    }
                }
            }


            return $storeOrdersArray;
        } catch (\Exception $e) {
            throw new ItemNotFoundException("No orders found matching your criteria.");
        }
    }
    public function getOrderByOrderId($orderId)
    {

        $StoreOrder = StoreOrder::find($orderId);

        return $StoreOrder;
    }
    public function getRefundProductByOrderId($orderId)
    {

        return ProductRefund::where('order_id', $orderId)->first();
    }

    public function getVariationImage($variation_id)
    {
        return DB::table('product_variation')->where('variation_id', $variation_id)->first();
    }
    public function  getbrandOrderdetails($order_id)
    {

        $orderDetails = $this->getOrderDetailByOrderIdApi($order_id);
        return $orderDetails;
    }

    public function getOrderData($orderId)
    {
        try {
            $data =  StoreOrder::with('orderItems', 'shippingAddress', 'store', 'user')->where('order_id', $orderId)->get();

            if (empty($data)) {
                throw new ItemNotFoundException('Order data not found.');
            }

            return $data;
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    public function  getOrderPdf($order_id)
    {
        $orderDetails = $this->getOrderPdfItems($order_id);

        if ($orderDetails) {
            return $orderDetails;
        } else {
            throw new ItemNotFoundException("No Order Found.");
        }
    }

    public function  getStoreOrders($store)
    {
        $todayNewOrders = StoreOrder::where('store_id', $store['store_id'])->where('charge_status', 'paid')->whereDate('created_at', today())->count();
        $todayOrders['todayNewOrders'] = $todayNewOrders;
        $todayUnitsSold = StoreOrder::where('order_status', 'completed')->where('store_id', $store['store_id'])->whereDate('created_at', today())->select('total_items', 'total_amount')->get();
        $todayOrders['units'] = 0;
        $todayOrders['totalRevenue'] = 0;
        foreach ($todayUnitsSold as $unit) {
            $todayOrders['units'] += $unit->total_items;
            $amount = number_format((float)$unit->total_amount, 2, '.', '');
            $todayOrders['totalRevenue'] += $amount;
        }
        $todayOrders['totalRevenue'] = '$' . number_format((float)$todayOrders['totalRevenue'], 2, '.', '');
        $sevenDaysorders = StoreOrder::where('order_status', 'completed')->where('store_id', $store['store_id'])->whereDate('created_at', '>=', now()->subDays(7))->select('total_items', 'total_amount')->get();
        $thirtyDaysOrders = StoreOrder::where('order_status', 'completed')->where('store_id', $store['store_id'])->whereDate('created_at', '>=', now()->subDays(30))->select('total_items', 'total_amount')->get();
        $nintyDaysOrders = StoreOrder::where('order_status', 'completed')->where('store_id', $store['store_id'])->whereDate('created_at', '>=', now()->subDays(90))->select('total_items', 'total_amount')->get();
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

        if (!empty($todayOrders) && !empty($sevenDaysOrder) && !empty($thirtyDaysOrder) && !empty($nintyDaysOrder)) {
            return ([
                'todayOrders' => $todayOrders,
                'seven_days' => $sevenDaysOrder,
                'thirty_days' => $thirtyDaysOrder,
                'ninty_days' => $nintyDaysOrder,
            ]);
        } else {
            throw new ItemNotFoundException("No Data Found.");
        }
    }
    public function updateOrderStatus($input)
    {

        try {
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

                    $result = $this->updateOrder($input);

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

                        $subjectBuyer = "Your Order #<b>{$input['order_id']}</b> has been {$orderNotification['status']}.";

                        $this->pushNotification(
                            $store_orders->buyer_id,
                            $subjectBuyer,
                            'buyer',
                            "/new-buyer-orders-detail/{$input['order_id']}"
                        );

                        $subjectSeller = "Order #<b>{$input['order_id']}</b> has been {$orderNotification['status']}.";
                        $sellerId = auth()->user()->user_id;

                        $this->pushNotification(
                            $sellerId,
                            $subjectSeller,
                            'seller',
                            "/orders/brand-order-details/{$input['order_id']}"
                        );
                        return response()->json([
                            'status' => true,
                            'message' => "Status updated successfully",

                        ], 200);
                    } else {
                        throw new Exception("Status is not changed please contact to super admin");
                    }
                } catch (\Throwable $th) {
                    throw new Exception($th->getMessage());
                }
            }
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    public function getOrderById($orderId)
    {
        return DB::table('store_orders')
            ->select('transaction_id', 'total_amount')
            ->where('order_id', $orderId)
            ->first();
    }

    public function changeOrderStatus($orderId, $status)
    {
        return DB::table('store_orders')
            ->where('order_id', $orderId)
            ->update(['order_status' => $status]);
    }

    public function  return_refund_stripe($StoreOrder, $input)
    {
        try {

            if (isset($input['payment_method']) && $input['payment_method'] === 'stripe') {
                try {
                    $response =   $this->stripeObj->refundBack($input['order_id']);

                    if ($response) {

                        $orderItems = $this->getOrderItems($input['order_id']);

                        $order_information = StoreOrder::where('order_id', $input['order_id'])->select('buyer_id')->first();
                        $order_store_info = StoreOrder::where('order_id', $input['order_id'])->select('store_id')->first();
                        $query_update = StoreOrder::where('order_id', $input['order_id'])->update([ 'cancelled_by' => "user"]);

                        $buyer_id = $order_information->buyer_id;
                        $store_id = $order_store_info->store_id;

                        $store = $this->getStoreInfoById((int) $store_id);

                        $storeUser_id = isset($store[0]->user_id) ? $store[0]->user_id : '';

                        $userData = $this->getUserById($buyer_id);
                        $usershipping = $this->getShippingAddressbyUserId($buyer_id);
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
                            $productData = $this->getProductIdSpecificColums($product_id);
                            $variableData = $this->getvariableByProduct_id($product_id);

                            $productArray[] = [
                                'title' => $productData->title,
                                'slug' => $productData->slug,
                                'feature_image' => $productData->feature_image,
                                'product_type' => $productData->product_type,
                                'price_total' => $value->total,
                                'quantity' => $value->quantity,
                                'variable_image' => $variableData->product_variation_image,
                            ];
                        }
                        $html = view('mail.order-update-client-refunded', ['userData' => $userData, "shippingAddress" => $usershipping, 'orderItems' => $productArray, 'orderId' => $input['order_id']]);
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
                        } catch (Exception $e) {
                            throw new Exception($e->getMessage());
                        }
                        $subjectStore = Constants::ACCEPT_REFUND_REQUEST_SELLER_SUBJECT . $input['order_id'];

                        if (isset($buyer_id) && !is_null($buyer_id)) {
                            $userData = User::with('device_token')->findOrFail($buyer_id);
                            $device_tokens = DeviceToken::where('user_id', $userData->user_id)->where('app_flag', 'buyer')->get();
                            if ($device_tokens) {
                                $tokens = [];
                                if (isset($device_tokens) && count($device_tokens) > 0) {

                                    foreach ($device_tokens as $device_token_obj) {

                                        $subject = $subjectStore;

                                        $notification = FCMService::send(
                                            $device_token_obj->fcm_token,
                                            [
                                                'title' => $subject,
                                            ]
                                        );
                                    }
                                }
                            }
                            addNotifications($buyer_id, $storeUser_id, $subjectStore, '', URL::to('new-buyer-orders-detail/' . $input['order_id']), 'buyer_order_details', $input['order_id'], 'buyer');
                        }
                        $mesage = 'Refund was successfull';
                    } else {
                        $mesage = 'Refund is not successfull please contact to super admin';
                    }
                } catch (Exception $e) {
                    throw new Exception($e->getMessage());
                }
            }


            if (isset($input['refund_process']) && is_array($input['refund_process'])) {
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
            $message = 'Refund was successfull';
            return $message;
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }
    public function rejectOrderStoreOwner($storeOrder, $input)
    {
        try {
            $status = "refunded";
            if ($input) {
                try {
                    $refundProductIds = is_array($input['refund_product_id']) ? $input['refund_product_id'] : explode(',', $input['refund_product_id']);
                    foreach ($refundProductIds as $product) {

                        if ($product === null) {
                            throw new ItemNotFoundException('you have no product in this order');
                        } else {
                            $productRefund = ProductRefund::where('product_id', $product)->where('order_id', $input['order_id'])->first(); //shold check where order_id  as well
                            $productRefund->is_reject = $input['is_reject'] ? $input['is_reject'] : "";
                            $productRefund->is_refund = $input['is_refund'] ? $input['is_refund'] : "";
                            $productRefund->reject_order_reason = $input['reject_order_reason'] ? $input['reject_order_reason'] : "";
                            $productRefund->save();
                        }
                    }

                    $StoreOrder = StoreOrder::find($input['order_id']);
                    $storeData = $StoreOrder->store;
                    $StoreOrder->order_status = $input['order_status'] ? $input['order_status'] : "";
                    $StoreOrder->reject_date = $input['reject_date'] ? $input['reject_date'] : "";
                    $StoreOrder->is_refund = $input['is_refund'] ? $input['is_refund'] : "";
                    $StoreOrder->is_reject = $input['is_reject'] ? $input['is_reject'] : "";
                    $StoreOrder->save();
                    $val = true;
                    $buyerData = $this->getUserByIdchat($input['buyer_id']);
                    $userid = auth()->user()->user_id;
                    $storeName = $storeData->user->name;
                    $mallsharkstorename = $storeData->store_name;
                    $mallsharkstoreslug = $storeData->store_slug;
                    $description = '';

                    // Vendor Email
                    $html = view('mail.order_refund_request_rejected_vendor', ['storeName' => $storeName, 'order_id' => $input['order_id'], 'productInfo' => $input['refund_product_title'], 'status' => $status, 'storeData' => $storeData, 'customerDetail' => $StoreOrder->user]);
                    $subject = Constants::DISPUTED_ORDER_BUYER_SUBJECT . $input['order_id'];

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
                    $html = view('mail.order_refund_request_rejected_client', ['buyerName' => $buyerName, 'order_id' =>  $input['order_id'], 'mallsharkstorename' => $mallsharkstorename, 'mallsharkstoreslug' => $mallsharkstoreslug, 'storeName' => $storeName, 'productInfo' => $input['refund_product_title'], 'status' => $status, 'storeData' => $storeData, 'customerDetail' => $StoreOrder->user]);

                    $toEmail_buyer = $StoreOrder->user->email;

                    $params = [
                        'from'    => 'MallShark  ' . env('SUPPORT_EMAIL'),
                        'to' => $toEmail_buyer,
                        'subject' => $subject,
                        'html' => "$html",
                    ];
                    $results = $this->mailgun->SendMailgunMail($params);

                    if (isset($input['buyer_id']) && $input['buyer_id'] != '') {
                        $userData = User::with('device_token')->findOrFail($input['buyer_id']);
                        $device_tokens = DeviceToken::where('user_id', $userData->user_id)->where('app_flag', 'buyer')->get();
                        if ($device_tokens) {
                            $tokens = [];
                            if (isset($device_tokens) && count($device_tokens) > 0) {
                                foreach ($device_tokens as $device_token_obj) {

                                    $subject = $subject;

                                    $notification = FCMService::send(
                                        $device_token_obj->fcm_token,
                                        [
                                            'title' => $subject,
                                        ]
                                    );
                                }
                            }
                            $this->addNotifications($input['buyer_id'], $this->getCurrentUserId(), $subject, $description, URL::to('new-buyer-orders-detail/' . $input['order_id']), 'buyer_order_details', $input['order_id'], 'buyer');
                        }
                    }
                    // create ticket zendesk start
                    if (!is_null($userid)) {
                        $userData = $this->getUserById($userid);
                        $userZendeskId = $buyerData->zendesk_user_id;

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
                            } catch (Throwable $e) {
                                throw new Exception($e->getMessage());
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
                            'subject' => 'Order Dispute with ID' . $input['order_id'] . '',
                            'comment' => [
                                'body' => 'The order for ' . $userData->name . ' Vendor has been disputed, Email ' . $userData->email . ' ',
                            ],
                            'priority' => 'normal',
                            'requester_id' => $zendeskId,
                            'type' => 'problem',
                            'priority' => 'high',
                        ]);
                    }

                    $orderNotification = [];
                    $orderNotification['buyer_id'] = $StoreOrder->buyer_id;
                    $orderNotification['order_id'] = $StoreOrder->order_id;
                    $orderNotification['status'] = $StoreOrder->order_status;
                    $message = 'Your Order is Disputed. Superadmin will check this order';
                    return  $message;
                } catch (Throwable $e) {
                    throw new Exception($e->getMessage());
                }
            }
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }
    public function getBrandOrders($order_status, $order_id, $postedData)
    {
        try {
            $userId = Auth::user()->user_id;
            if (!$userId) {
                throw new UnauthorizedException('No User found');
            }
            $store = $this->getStoreInfoUserId($userId);

            $currentDate = Carbon::today();
            $getTodayNewStoreOrders = StoreOrder::where('store_id', $store['store_id'])->whereBetween('created_at', [now()->subDays(6)->startOfDay(), now()->endOfDay()])->count();
            $getTodayPendingStoreOrders = StoreOrder::where('store_id', $store['store_id'])->where('charge_status', 'paid')->where('order_status', 'pending')->whereBetween('created_at', [now()->subDays(6)->startOfDay(), now()->endOfDay()])->count();
            $getTodayRefundReqStoreOrders = StoreOrder::where('store_id', $store['store_id'])->where('charge_status', 'paid')->where('order_status', 'refunded')->whereBetween('created_at', [now()->subDays(6)->startOfDay(), now()->endOfDay()])->count();
            $getTodayDisputeStoreOrders = StoreOrder::where('store_id', $store['store_id'])->where('charge_status', 'paid')->where('order_status', 'dispute')->whereBetween('created_at', [now()->subDays(6)->startOfDay(), now()->endOfDay()])->count();
            if ($store > 0 && $store != 0) {
                $orders_all = $this->getOrdersWithItemsAllNewApiNoPagination($store['store_id'], $order_id, $order_status);
            } else {
                throw new ItemNotFoundException('Store not exists');
            }
            if (Auth::user()->user_id) {

                if (!empty($orders_all)) {

                    $selectedOrders = [];

                    foreach ($orders_all as $orders) {

                        if ($orders['order_status'] == 'cancelled') {
                            $refundProduct = ProductRefund::where('order_id', $orders['order_id'])->first();
                            if (isset($refundProduct)) {
                                $orders['refund_reason_type'] = $refundProduct->reason_type;
                                $orders['refund_reason'] = $refundProduct->reason;
                            }
                        }

                        if (isset($orders['user']['user_avatar'])) {
                            $userImageId = $orders['user']['user_avatar'];
                            $userImage = $this->getAttachmentUrlSmallById((int) $userImageId);
                            $orders['user']['user_avatar'] = $userImage;
                        }
                        if ($orders['store_tax'] === null) {
                            $orders['store_tax'] = 0;
                        }

                        $order_items_list = [];

                        if (isset($orders['order_items_list']) && !empty($orders['order_items_list'])) {
                            $path =  url('downloads/labels/');
                            if (!empty($orders['order_items_list']->tracking_code) && $orders->order_status != 'completed') {
                                $orders['order_items_list']->label_url = $path . '/' . $orders['order_items_list']->tracking_code . '.png';
                                $orders['order_items_list']->PDF_label_url = $path . '/' . $orders['order_items_list']->tracking_code . '.png';
                            }
                            foreach ($orders['order_items_list'] as $products) {
                                $modifiedProducts = [];
                                if ($products['variation_id'] == 0) {
                                    foreach ($products['products'] as $product) {
                                        $product['feature_image'] = $this->getAttachmentUrlById(intval($product['feature_image']), 'media');
                                        $modifiedProducts[] = $product;
                                    }
                                } else if ($products['variation_id'] != 0) {
                                    $variation_image = DB::table('product_variation')->where('variation_id', $products['variation_id'])->first();
                                    if (isset($variation_image) && !is_null($variation_image)) {
                                        foreach ($products['products'] as $product) {
                                            $product['feature_image'] = $this->getAttachmentUrlById(intval($variation_image->product_variation_image), 'media');
                                            $modifiedProducts[] = $product;
                                        }
                                    } else {
                                        foreach ($products['products'] as $product) {
                                            $product['feature_image'] = $this->getAttachmentUrlById(intval($product['feature_image']), 'media');
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
                    }

                    if (!empty($orders_all)) {
                        $orders_all = $selectedOrders;
                        return [
                            'orderProducts' => $orders_all,
                            'NewOrders' => $getTodayNewStoreOrders,
                            'PendingOrders' => $getTodayPendingStoreOrders,
                            'RefundRequestOrders' => $getTodayRefundReqStoreOrders,
                            'DisputeOrders' => $getTodayDisputeStoreOrders,
                        ];
                    } else {
                        throw new ItemNotFoundException('No Order Found');
                    }
                } else {
                    throw new ItemNotFoundException('No Order Found');
                }
            }
        } catch (\Exception $e) {
            throw $e;
        }
	} 

    public function getBrandOrdersWithItems($store_id)
    {
        $orders = StoreOrder::with(['orderItems.products.productImages'])
            ->where('store_id', $store_id)
            ->where('created_at', '>=', Carbon::now()->subDays(30))
            ->get();

        return $orders;
    }

    public function updateFedExShippingDetails($orderId, array $data)
    {
        $storeOrder = StoreOrder::where('order_id', $orderId)->first();

        if (!$storeOrder) {
            throw new Exception("Store order not found.");
        }

        $storeOrder->ship_label_url = $data['ship_label_url'] ?? null;
        $storeOrder->fedex_ship_amount = $data['fedex_ship_amount'] ?? null;
        $storeOrder->ship_service = $data['ship_service'] ?? null;
        $storeOrder->delivery_date = $data['delivery_date'] ?? null;
        $storeOrder->order_status = $data['status'] ?? null;
        $storeOrder->shipped_date = now();

        $trackingNumber = $data['tracking_id'] ?? null;
        $trackingUrl = "https://www.fedex.com/fedextrack/?trknbr={$trackingNumber}";
        $storeOrder->tracking_id =  $trackingUrl; 
        $storeOrder->tracking_number =  $trackingNumber;
        $isSaved = $storeOrder->save();

        if ($isSaved) {
            $this->sendFedExShippedMail($orderId, $trackingUrl);
            return true;
        } else {
            return false;
        }
    }

    public function getOrdersPaginate($orderStatus = null)
    {
        try {
            $userId = Auth::user()->user_id;
            $query = StoreOrder::with('orderItemsList.review', 'user', 'shippingAddress')
                ->where('charge_status', 'paid')
                ->where('buyer_id', $userId);

            if ($orderStatus) {
                $query->where('order_status', $orderStatus);
            }
            $orders =  $query->orderBy('created_at', 'desc')->paginate(10);
            // $orders = $query->paginate(10); // 10 is the number of items per page
            $storeOrdersArray = $orders->toArray();

            return $storeOrdersArray;
        } catch (\Exception $e) {
            throw new ItemNotFoundException("No orders found matching your criteria.");
        }
    }
    public function editOrderShippingAddress($data)
    {
        try {
            $orderData = $this->getOrderDataForShipping($data['order_id']);
            if (!$orderData) {
                throw new ItemNotFoundException("Order not found.");
            }
            $order = $orderData[0];

            $shippId = $order['shipping_address']['sh_id'];
            if (!$shippId) {
                throw new ItemNotFoundException("Shipping address id not found.");
            }

            $shippingAddress = ShippingAddress::where('sh_id', $shippId)->first();

            if (!$shippingAddress) {
                throw new ItemNotFoundException("Shipping address not found.");
            }

            $updateData = Arr::only($data, ['street', 'city', 'state', 'post_code']);
            $shippingAddress->update($updateData);

            return $orderData;
        } catch (ItemNotFoundException $e) {
            throw new ItemNotFoundException($e->getMessage());
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        } 
    }

    
    public function productRefund(array $data): ProductRefund
    {
        try {
            $refund = new ProductRefund();
            $refund->product_id = $data['product_id'] ?? null;
            $refund->order_id = $data['order_id'] ?? null;
            $refund->buyer_id = $data['buyer_id'] ?? null;
            $refund->store_id = $data['store_id'] ?? null;
            $refund->quantity = $data['quantity'] ?? null;
            $refund->price = $data['price'] ?? null;
            $refund->shipping_amount = $data['shipment'] ?? null;
            $refund->reason_type = $data['refund_reason_type'] ?? null;
            $refund->reason = $data['message'] ?? $data['reason'] ?? null;
            $refund->is_refund = $data['cancelled'] ?? $data['is_refund'] ?? null;
            $refund->refund_date = $data['refund_date'] ?? null;
            $refund->payment_method = $data['payment_method'] ?? null;
            $refund->save();

            return $refund;
        } catch (\Exception $e) {
            throw new Exception($e->getMessage());
        }
    }
}
