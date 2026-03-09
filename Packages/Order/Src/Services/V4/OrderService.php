<?php

namespace Packages\Order\Services\V4;

use Exception;
use Carbon\Carbon;
use Stripe\Refund;
use Stripe\Stripe;
use App\Constants;
use Illuminate\Support\Facades\Auth;
use Packages\Order\Models\V4\StoreOrder;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Http;
use Packages\Order\Contracts\V4\OrderHelpers;
use App\Http\Controllers\MailgunController;
use Illuminate\Support\ItemNotFoundException;
use Illuminate\Validation\UnauthorizedException;
use GuzzleHttp\Exception\RequestException;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Packages\Auth\Models\V4\{User,DeviceToken};
use App\Services\SellerFcmService;
use EasyPost\Order;
use Illuminate\Support\Facades\URL;
use Packages\Order\Config\OrderConstants;
use Packages\Order\Contracts\V4\BaseApiResponse;
use Huddle\Zendesk\Facades\Zendesk;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Packages\Order\Models\V4\OrderItems;
use Packages\Order\Repositories\Eloquent\V4\OrderRepository;

class OrderService
{
    use OrderHelpers;
    protected $orderRepository;
    protected $apiKey;
    protected $client;
    protected $baseUrl;
    private $mailgun;

    public function __construct(OrderRepository $orderRepository)
    {
        $this->mailgun = new MailgunController();
        $this->orderRepository = $orderRepository;
        $this->apiKey = env('STRIPE_CHECKOUT_KEY');

    }

    public function getBuyerOrders($request)
    {
        try {
            $orderStatus = $request->input('status');
            $orderId =  $request->input('order_id');

            $orders = $this->orderRepository->getBuyerOrders($orderId, $orderStatus);
            if (empty($orders)) {
                throw new ItemNotFoundException("No orders found for the provided filters.");
            }
            foreach ($orders as $order) {

                // Add status and user avatar processing as needed
                $order['order_status'] = ucfirst($order['order_status']);
                $userImageId = $order['user']['user_avatar'];
                if (isset($userImageId) && !is_null($userImageId)) {
                    $userImage = $this->getAttachmentUrlByIdAvatar($userImageId);
                    $order['user']['user_avatar'] = $userImage;
                }

                $order_items_list = [];
                foreach ($order['order_items_list'] as $orderItem) {
                    $orderItem['item_image'] = url('/uploads') .'/'. 'orders/'.($orderItem['item_image']);
                    $orderItem['customizations'] = json_decode($orderItem['customizations'], true);
                    if(!is_null($orderItem['customizations'])){
                        $customizations = $orderItem['customizations'];
                        if(!empty($customizations->image_url)){
                            $orderItem['customizations']['image_url'] = url('/uploads') .'/'. 'orders/'.($customizations->image_url ?? '');
                        }
                    }
                    foreach ($orderItem['products'] as $key => $product) {
                        $product['feature_image_url'] = $this->getAttachmentUrlById(intval($product['feature_image']), 'media');
                        $orderItem['products'][$key] = $product;
                    }
                    $order_items_list[] = $orderItem;
                }

                $order['order_items_list'] = $order_items_list;
                $order['totolAmount'] = $order['total_amount'];
                $order['shippingAmount'] = $order['shipping_amount'];
                $order['orderPrice'] = $order['total_amount'] - $order['shipping_amount'];
                $selectedOrders[] = $order;
                // Add status and user avatar processing as needed
                
            }
            return $selectedOrders;
        } catch (ItemNotFoundException $e) {
            throw new ItemNotFoundException($e->getMessage());
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    public function addOrdersItems($allOrders)
    {
        $selectedOrders = [];

        try {
            foreach ($allOrders as $order) {

                // Add status and user avatar processing as needed
                $order['order_status'] = ucfirst($order['order_status']);
                $userImageId = $order['user']['user_avatar'];
                if (isset($userImageId) && !is_null($userImageId)) {
                    $userImage = $this->getAttachmentUrlByIdAvatar($userImageId);
                   
                    $order['user']['user_avatar'] = $userImage;
                }

                // Ensure that 'order_items_list' is an array before proceeding
                $orderItemsList = [];
                if (isset($order['order_items_list']) && is_array($order['order_items_list'])) {
                    $orderItemsList = $this->addOrderItemsData($order['order_items_list']);
                } else {
                    // Handle the case where 'order_items_list' is null or not an array
                    $order['order_items_list'] = [];
                }

                // Attach the processed order items to the order
                $order['order_items_list'] = $orderItemsList;

                // Add the processed order to the list
                $selectedOrders[] = $order;
            }
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }

        return $selectedOrders;
    }

    public function addOrderItemsData($orderItems)
    {
        $orderItemsList = [];
        $userId = Auth::user()->user_id;
        try {
            foreach ($orderItems as $products) {
                // Process each product and variation logic
                $modifiedProducts = [];
                if ($products['variation_id'] == 0) {
                    foreach ($products['products'] as $product) {
                        $product['feature_image'] = $this->getAttachmentUrlById(intval($product['feature_image']), 'media');
                        $modifiedProducts[] = $product;
                    }
                } else if ($products['variation_id'] != 0) {
                    // Handle variation logic
                    $modifiedProducts[] = $this->addVariationsData($products);
                }

                // Add review processing
                if ($products['review']) {
                    foreach ($products['review'] as $review) {
                        $products['isReview'] = $review['user_id'] == $userId && $review['product_id'] == $products['item_id'];
                    }
                }

                $products['products'] = $modifiedProducts;
                $orderItemsList[] = $products;
            }
        } catch (Exception $e) {
            throw new Exception('Processing Order Items ' . $e->getMessage());
        }
        return $orderItemsList;
    }

    public function addVariationsData($products)
    {
        $modifiedProduct = [];

        try {
            $variationDetails = $this->getVariationsById($products['variation_id']);

            if ($variationDetails) {

                if (isset($variationDetails->product_variation_image)) {
                    foreach ($products['products'] as $product) {
                        $product['feature_image'] = $this->getAttachmentUrlById(intval($variationDetails->product_variation_image), 'media');
                        $modifiedProduct[] = $product;
                    }
                } else {
                    // If no variation image, process the default feature image
                    foreach ($products['products'] as $product) {
                        $product['feature_image'] = $this->getAttachmentUrlById(intval($product['feature_image']), 'media');
                        $modifiedProduct[] = $product;
                    }
                }
            } else {
                // If no variation is found, handle the case by returning the default products
                foreach ($products['products'] as $product) {
                    $product['feature_image'] = $this->getAttachmentUrlById(intval($product['feature_image']), 'media');
                    $modifiedProduct[] = $product;
                }
            }
        } catch (Exception $e) {
            throw new Exception('Processing Variations' . $e->getMessage());
        }
        return $modifiedProduct;
    }


    //Store Orders

    public function getBrandOrdersPaginate($request)
    {
        $order_status = $request->input('order_status');
        $order_id =  $request->input('order_id');
        $keyword = $request->input('keyword');
        $zipcode = $request->input('zipcode');
        $From_Date = $request->input('From_Date');
        $To_Date =  $request->input('To_Date');
        $userId = Auth::user()->user_id;

        $store = OrderHelpers::getAccessibleStore($userId);
        if (!$store) {
            throw new ItemNotFoundException('User does not belong or have access to any store.');
        }
        $storeId = $store->store_id;
        $currentDate = Carbon::today();

        // Get today's new, pending, refunded, and dispute orders
        $newOrders = $this->orderRepository->getTodayNewOrders($storeId, $currentDate);
        $pendingOrders = $this->orderRepository->getTodayPendingOrders($storeId, $currentDate);
        $refundedOrders = $this->orderRepository->getTodayRefundedOrders($storeId, $currentDate);
        $disputeOrders = $this->orderRepository->getTodayDisputeOrders($storeId, $currentDate);

        $orders_all = $this->orderRepository->getOrdersWithItems($storeId, $order_id, $order_status, $keyword, $zipcode, $From_Date,$To_Date);
      
        if (Auth::user()->user_id) {
            try {
                if (!empty($orders_all)) {
                    // dd($orders_all);
                    $selectedOrders = [];
                    // dd($orders_all);
                    foreach ($orders_all['data'] as $orders) {
                        // return 12;
                        if ($orders['order_status'] == 'cancelled') {
                            $refundProduct = $this->orderRepository->getRefundProductByOrderId($orders['order_id']);
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
                            foreach ($orders['order_items_list'] as $orderItem) {
                                $orderItem['item_image'] = url('/uploads') .'/'. 'orders/'.($orderItem['item_image']);
                                $orderItem['customizations'] = !is_null($orderItem['customizations']) ? json_decode($orderItem['customizations'], true) : null;
                                if(!is_null($orderItem['customizations'])){
                                    if(!empty($orderItem['customizations']['image_url'])){
                                        $orderItem['customizations']['image_url'] = url('/uploads') .'/'. 'orders/'.($orderItem['customizations']['image_url'] ?? '');
                                    }
                                }
                                foreach ($orderItem['products'] as $key => $product) {
                                    $product['feature_image'] = $this->getAttachmentUrlById(intval($product['feature_image']), 'media');
                                    $orderItem['products'][$key] = $product;
                                }
                                $order_items_list[] = $orderItem;
                            }
                            $orders['order_items_list'] = $order_items_list;
                            $selectedOrders[] = $orders;
                        }

                    }

                    if (!empty($orders_all['data'])) {
                        $orders_all['data'] = $selectedOrders;
                        return [
                            'orderProducts' => $orders_all,
                            'NewOrders' => $newOrders,
                            'PendingOrders' => $pendingOrders,
                            'RefundRequestOrders' => $refundedOrders,
                            'DisputeOrders' => $disputeOrders,
                        ];
                    } else {
                        throw new ItemNotFoundException("No Order Found.");
                    }
                } else {
                    throw new ItemNotFoundException("No Order Found.");
                }
            } catch (Exception $e) {
                throw new Exception($e->getMessage());
            }
            
        }
    }

    // Get order with id
    public function brandOrderdetails($order_id)
    {
        try {
            $orderDetails = $this->orderRepository->getbrandOrderdetails($order_id);
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
                return [
                    'orderProducts' => $selectedOrders,
                ];
            } else {
                 throw new ItemNotFoundException("No Order Found.");
            }
        } catch (\Throwable $th) {

         throw new ItemNotFoundException("No Order Found.");
        }
    }

    public function getOrderData($orderId) {
        try {
            $data = $this->orderRepository->getOrderData($orderId);
            return $data;
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }
     public function getOrderPdf($order_id)
    {
        try {
         
            $orderDetails = $this->orderRepository->getOrderPdf($order_id);
           
            if ($orderDetails) {
                return $orderDetails;
            } else {
                 throw new ItemNotFoundException("No Order Found.");
            }
        }
         catch (Exception $e) {
               throw new ItemNotFoundException("No Order Found.");
            }
    }
    public function getStoreOrders()
    {
        try {
        $user = auth()->user();
        $store =  $this->orderRepository->getStoreInfoByUserid($user->user_id);
        $result =  $this->orderRepository->getStoreOrders( $store );

        if ( $result ) {
            return  $result ;
        } else {
                throw new ItemNotFoundException("No Data Found.");
        }
        }
         catch (Exception $e) {
               throw new ItemNotFoundException("No Data Found.");
            }
    }
    
    public function updateOrderStatus($data)
    {
        try {
            $orderId = $data['order_id'];
            $orderStatus = $data['status'];
            $order = OrderHelpers::getOrderById($orderId);
            $buyerId = $order->buyer_id;
            if ($orderStatus == OrderConstants::ORDER_CANCELLED) {
                $data['is_refund'] = 'vendor';
            } elseif ($orderStatus == OrderConstants::ORDER_REFUNDED) {
                $data['is_refund'] = 'refund';
            }

            $isShippedCancel = $data['shipped_cancel'] ?? '';
            $data['shipped_cancel'] = $isShippedCancel;
            $isRefund = $data['is_refund'] ?? '';

            $result = null;
            switch ($orderStatus) {
                
                case OrderConstants::ORDER_SHIPPED:
                    $result = $this->processShipOrder($data);
                    break;

                case OrderConstants::ORDER_REFUNDED:
                    $result = $this->processRefundOrder($data);
                    break;

                case OrderConstants::ORDER_CANCELLED:
                    $result = $this->processCancelOrder($data);
                    break;

                default:
                    throw new Exception('Invalid status.');
            }
            
            if ($result) {
                $status = ($orderStatus == OrderConstants::ORDER_CANCELLED && $data['shipped_cancel'] == 1)
                    ? OrderConstants::ORDER_REFUNDED
                    : $orderStatus;

                $orderNotification = [
                    'status' => $status,
                    'buyer_id' => $buyerId,
                    'order_id' => $orderId,
                ];

                $subjectBuyer = "Your Order #<b>{$orderId}</b> has been {$orderNotification['status']}.";
                OrderHelpers::pushNotification(
                    $buyerId,
                    $subjectBuyer,
                    'buyer',
                    "/orders/{$orderId}"
                );

                $subjectSeller = "Order #<b>{$orderId}</b> has been {$orderNotification['status']}.";
                $sellerId = auth()->user()->user_id;
                OrderHelpers::pushNotification(
                    $sellerId,
                    $subjectSeller,
                    'seller',
                    "/order-detail/{$orderId}"
                );

                return true;
            }

            throw new Exception("Status is not changed, please contact the super admin.");
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    public function processRefundOrder($data)
    {
        try {
            $orderId = $data['order_id'];
            $orderStatus = $data['status'];
            $isRefund = $data['is_refund'];
            $processItems = $data['process_items'];
            $processItemIds = array_column($processItems, 'order_item_id');

            if (empty($processItemIds)) {
                throw new Exception('No items to process for refund.');
            }

            $orderDetails = $this->getOrderDataForShipping($orderId);
            $order = $orderDetails[0];

            $paymentMethod = $order['payment_method'];
            $orderItems = $order['order_items'];
            $buyer = $order['user'];
            $shippingAddress = $order['shipping_address'];

            // Derive processed item IDs from original $orderItems
            $allItemIds = array_column($orderItems, 'order_item_id');
            $missingItemIds = array_diff($allItemIds, $processItemIds);

            // Check if any of the missing items are NOT already refunded
            $missingItemsNotRefund = array_filter($orderItems, function ($item) use ($missingItemIds) {
                return in_array($item['order_item_id'], $missingItemIds);
            });

            // Set isAll = true only if all remaining items are already refunded
            $isAll = empty($missingItemsNotRefund);

            // Now filter accordingly
             $filteredItems = array_values(array_filter($orderItems, function ($item) use ($processItemIds) {
                return in_array($item['order_item_id'], $processItemIds);
            }));

            $productArray = $this->buildProductArray($filteredItems);

            // Create a map of refund amounts by order_item_id
            $refundMap = collect($processItems)->keyBy('order_item_id');

            // Assign item_refund_amount to each item in $productArray
            foreach ($productArray as &$item) {
                $item['item_refund_amount'] = $refundMap[$item['order_item_id']]['item_refund_amount'] ?? 0;
                $item['item_refund_quantity'] = $refundMap[$item['order_item_id']]['product_quantity'] ?? 0;
            }
            unset($item);


            if ($this->orderRepository->processCancelOrder($orderId, $isRefund, $productArray, $paymentMethod,$buyer['user_id'])) {
                $this->sendEmail($orderId, $orderStatus, $buyer, $shippingAddress, $productArray, $paymentMethod, 'mail.order-update-client-refunded');
            } else {
                throw new Exception('Refund failed. Please contact the super admin.');
            }

            return true;
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    public function processRefund($orderId)
    {
        try {
            $order = $this->orderRepository->getOrderById($orderId);

            if (!$order || empty($order->transaction_id)) {
                throw new ItemNotFoundException("Order or Transaction ID not found.");
            }

            Stripe::setApiKey($this->apiKey);

            $refund = Refund::create([
                "charge" => $order->transaction_id,
                "amount" => round($order->total_amount * 100)
            ]);

            if (isset($refund->status) && $refund->status === 'succeeded') {
                $this->orderRepository->changeOrderStatus($orderId, 'refunded');
                return true;
            }

            throw new Exception("Refund failed.");
        } catch (ItemNotFoundException $e) {
            throw new ItemNotFoundException($e->getMessage());
        }catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }
    
    public function rejectRefund($input)
    {
        try {
            $response =  $this->orderRepository->rejectRefund($input);

            if ($response) {
                $this->sendOrderDisputeEmail($input);
            } else {
                throw new Exception("Failed to reject refund request.");
            }
            return $response;
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

     public function getBrandOrders($order_status,$order_id,$postedData )
    {
	try{
        $userId = auth()->user()->user_id;
        if (!$userId) {
			throw new UnauthorizedException('No User found');
		}
        $order = $this->orderRepository->getBrandOrders($order_status,$order_id,$postedData);
			if (isset($order) && !empty($order)) {
			return $order;
		} else {
			throw new ItemNotFoundException("Store Data Not Avaliable");
		}
      }
        catch (\Exception $e) {
         throw $e;
        }
    }

    public function cancelBuyerOrder($data)
    {
        try {
            $orderData = $this->getStoreOrderByOrderId($data['order_id']);
            $order = $orderData[0];

            if (!$order) {
                throw new ItemNotFoundException('Order not found');
            }

            $orderItems = $order['orderItems'];
            if (!$orderItems) {
                throw new ItemNotFoundException('Order Items not found.');
            }

            
            $storeData = $order['store'];
            if (!$storeData) {
                throw new ItemNotFoundException('Store data not found');
            }
            
            $shippingAddress = $order['shippingAddress'];
            if (!$shippingAddress) {
                throw new ItemNotFoundException('Shipping address not found');
            }
            
            $buyer = $order['user'];
            if (!$buyer) {
                throw new ItemNotFoundException('Buyer data not found');
            }
            
            $data['buyer_id'] = $buyer['user_id'] ?? null;
            $data['store_id'] = $storeData['store_id'] ?? null;
            $data['payment_method'] = $order['payment_method'] ?? null;
            $data['refund_date'] = now()->format('Y-m-d H:i:s');
            $data['is_refund'] = "cancelled";
            
            if (is_array($data['cancel_item_id'])) {
                $refunds = [];
                
                foreach ($data['cancel_item_id'] as $itemId) {
                    $matchedItem = $order['orderItems']->firstWhere('order_item_id', $itemId);
                    if (!$matchedItem) {
                        throw new ItemNotFoundException("Order item with ID {$itemId} not found in order.");
                    }
                    $matchedItem->status = OrderConstants::ORDER_CANCELLED;
                    $matchedItem->save();

                    if ($matchedItem) {
                        $refundData = array_merge($data, [
                            'product_id'     => $matchedItem->item_id,
                            'variation_id'   => $matchedItem->variation_id,
                            'quantity'       => $matchedItem->quantity,
                            'price'          => $matchedItem->sold_price > 0 ? $matchedItem->sold_price : $matchedItem->price,
                            'shipment'       => $matchedItem->shipping_amount ?? 0,
                            'product_image'  => $matchedItem->item_image,
                            'product_title'  => $matchedItem->product_name ?? 'Product',
                            'product_slug'   => optional($matchedItem->product)->slug ?? '',
                            'customizations' => $matchedItem->customizations ?? null,
                        ]);
                        
                        $this->orderRepository->productRefund($refundData);
                        
                        $refunds[] = $refundData;
                    }
                }
                
                $itemStatuses = OrderItems::where('order_id', $data['order_id'])
                    ->pluck('status')
                    ->toArray();
    
                $order['order_status'] = OrderHelpers::resolveOrderStatus($itemStatuses);
                $order['refund_date'] = now()->format('Y-m-d H:i:s');
                $order['is_refund'] = "cancelled";
                $order->save();

                // Merge base data with cancel_item_id list
                $finalData = array_merge($data, [
                    'cancel_item_id' => $refunds
                ]);
            }
            $subjectStore = $shippingAddress['full_name'] . OrderConstants::REQUEST_CANCEL_ORDER_SELLER_SUBJECT . $data['order_id'];

            // email send to vendor
            $html = view('mail.canceled-order-from-client', ['userData' => $buyer, 'orderData' => $finalData, "shippingAddress" => $shippingAddress, 'storeData' => $storeData])->render();
            $subject = 'Order NO #' . $data['order_id'] . ' Cancellation';
            $toEmail = $storeData->user->email;
            $params = [
                'from'    => 'MallShark  ' . env('SUPPORT_EMAIL'),
                'to' => $toEmail,
                'subject' => $subject,
                'html' => "$html",
            ];
            $this->mailgun->SendMailgunMail($params);
            OrderHelpers::pushNotification($storeData['user_id'], $subjectStore, 'seller');
            OrderHelpers::addNotifications($storeData['user_id'], $buyer['user_id'], $subjectStore, '', '/orders/' . $data['order_id'], 'brand_order_details', $data['order_id'], 'seller');

            // email send to buyer
            $html = view('mail.order-update-client-cancelled', ['order_id' => $data['order_id'], 'userData' => $buyer, 'orderData' => $finalData, 'shippingAddress' => $shippingAddress])->render();
            $subject = 'Order #' . $data['order_id'] . ' Cancellation Request';
            $subjectBuyer = OrderConstants::REQUEST_CANCEL_ORDER_BUYER_SUBJECT . $data['order_id'];
            $buyerEmail = $buyer['email'];
            $params = [
                'from'    => 'MallShark  ' . env('SUPPORT_EMAIL'),
                'to' => $buyerEmail,
                'subject' => $subjectBuyer,
                'html' => $html,
            ];
            $this->mailgun->SendMailgunMail($params);

            return $order;
        } catch (ItemNotFoundException $e) {
            throw new ItemNotFoundException($e->getMessage());
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }
    public function getOrdersPaginate($request)
    {
        try {
            $orderStatus = $request->input('status');
            $orders = $this->orderRepository->getOrdersPaginate($orderStatus);
        
            if (empty($orders)) {
                throw new ItemNotFoundException("No orders found for the provided filters.");
            }
            foreach ($orders['data'] as &$order) {
                if (!empty($order['order_items_list'])) {
                    foreach ($order['order_items_list'] as &$item) {
                       $item['item_image'] = url('/uploads') .'/'. 'orders/'.($item['item_image']);
                       $item['customizations'] = !is_null($item['customizations']) ? json_decode($item['customizations'], true) : null;
                        if(!is_null($item['customizations'])){
                            if(!empty($item['customizations']['image_url'])){
                                $item['customizations']['image_url'] = url('/uploads') .'/'. 'orders/'.($item['customizations']['image_url'] ?? '');
                            }
                        }
                    }
                }
            }
            return $orders;
        } catch (ItemNotFoundException $e) {
        throw new ItemNotFoundException($e->getMessage());
        } catch (Exception $e) {
        throw new Exception($e->getMessage());
        }
    }

    public function editOrderShippingAddress($data) {
        try {
            $response = $this->orderRepository->editOrderShippingAddress($data);

            $order = $response[0];
            $shippingAddress = $order['shipping_address'];

            return $shippingAddress;
        } catch (ItemNotFoundException $e) {
            throw new ItemNotFoundException($e->getMessage());
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }
    public function createZendeskTicketForAddress($data) {
        try {
            $orderData = $this->getOrderDataForShipping($data['order_id']);
            $order = $orderData[0];
            $buyer = $order['user'];
            $shippingAddress = $order['shipping_address'];

            $buyerName = $shippingAddress['full_name'] ?? $buyer['name'];
            $buyerEmail = $buyer['email'];
            $orderId = $data['order_id'];

            $zendeskUser = Zendesk::users()->createOrUpdate([
                'name' => $buyerName,
                'email' => $buyerEmail,
                'role' => 'end-user',
            ]);

            $zendeskId = $zendeskUser->user->id;

            if(!$zendeskId) {
                throw new ItemNotFoundException("Zendesk id not found");
            }

            // Create the Zendesk ticket
            $subject = 'Address Update Required';
            $message = <<<EOD
            Dear $buyerName,

            We hope this finds you well.

            We are reaching out regarding your recent order #$orderId . It appears that the shipping address provided is incomplete, which may cause delays in the delivery of your order.

            To ensure a smooth and timely dispatch, we kindly request you to update your address at your earliest convenience. You may reply to this email with the complete address or update it directly through your account.

            Please let us know if you need any assistance at care@mallshark.com.
            We appreciate your prompt attention to this matter.

            Best regards,
            Team Mallshark.
            EOD;

            $newTicket = Zendesk::tickets()->create([
                'subject' => 'Address Update Required',
                'comment' => [
                    'body' => $message,
                ],
                'priority' => 'high',
                'requester_id' => $zendeskId,
                'type' => 'problem',
            ]);

            $from = env('ZENDESK_EMAIL');
            $to = $buyerEmail;
            $htmlContent = view('mail.address-update', compact('buyerName', 'orderId', 'shippingAddress'))->render();

            $params = [
                'from'    => $from,
                'to'      => $to,
                'subject' => $subject,
                'html'    => $htmlContent, 
            ];
            $mailgun = new MailgunController();
            $mailgun->SendMailgunMail($params);

            return $shippingAddress;
        } catch (ItemNotFoundException $e) {
            throw new ItemNotFoundException($e->getMessage());
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    public function fetchFedExOrderRate($orderId)
    {
        $orderData = $this->getOrderDataForShipping($orderId);
        $order = $orderData[0];

        $store = $order['store'] ?? null;
        $shippingAddress = $order['shipping_address'] ?? null;

        // Extract shipping details (default to US data)
        $shipperPostalCode = $store['zip_code'] ?? null;
        $shipperCountryCode = $store['country']['code'] ?? 'US';

        $recipientPostalCode = $shippingAddress['post_code'] ?? null;
        $recipientCountryCode = $shippingAddress['country']['code'] ?? 'US';

        $packageItems = $this->convertToFedExPackageFormat($order['order_items']);

        // Ensure packageItems has at least one entry
        if (empty($packageItems)) {
            return response()->json(['error' => 'No valid package details found'], 422);
        }

        // ✅ Get FedEx Token
        $this->client = new Client();
        $this->baseUrl = env('FEDEX_BASE_URL');

        try {
            $response = $this->client->post($this->baseUrl . '/oauth/token', [
                'form_params' => [
                    'grant_type' => 'client_credentials',
                    'client_id' => env('FEDEX_API_KEY'),
                    'client_secret' => env('FEDEX_SECRET_KEY'),
                ],
            ]);
            $data = json_decode($response->getBody(), true);
            $token = $data['access_token'];
        } catch (\Exception $e) {
            return response()->json(['error' => 'FedEx Authentication Failed: ' . $e->getMessage()], 500);
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
            $response = $this->client->post($this->baseUrl . '/rate/v1/rates/quotes', [
                'headers' => $headers,
                'json' => $body,
            ]);

            $response = json_decode($response->getBody(), true);
            return response()->json([
                'status' => true,
                'message' => 'FedEx rates retrieved successfully.',
                'data'  => $response
            ], 200);
        }  catch (RequestException $e) {
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

    public function shipFedExOrder($orderId, $serviceType)
    {
        try {
            $orderData = $this->getOrderDataForShipping($orderId);
            $order = $orderData[0] ?? null;

            if (!$order) {
                throw new ItemNotFoundException("Order not found.");
            }

            $store = $order['store'] ?? null;
            $shippingAddress = $order['shipping_address'] ?? null;
            $buyer = $order['user'] ?? null;

            if (!$store) throw new ItemNotFoundException("Store details not found.");
            if (!$buyer) throw new ItemNotFoundException("Buyer details not found.");
            if (!$shippingAddress) throw new ItemNotFoundException("Shipping address not found.");
            if (!$store['fulfillment_by_mallshark']) throw new AuthorizationException("You don't have access to this resource.");

            // Extract shipper & recipient details
            $shipper = [
                "postalCode" => $store['zip_code'],
                "street" => $store['street_address'],
                "city" => $store['store_city'],
                "stateCode" => $store['state']['code'],
                "countryCode" => $store['country']['code'],
                "name" => $store['user']['name'],
                "email" => $store['user']['email'],
                "phone" => $store['user']['contact_no'],
                "phoneExtension" => $store['country']['dialing_code'],
                "companyName" => $store['store_name'] ?? 'Mallshark'
            ];

            $recipient = [
                "name" => $buyer['name'],
                "email" => $buyer['email'],
                "phone" => $buyer['contact_no'],
                "city" => $shippingAddress['city'],
                "street" => $shippingAddress['street'],
                "postalCode" => $shippingAddress['post_code'],
                "stateCode" => $shippingAddress['state']['code'],
                "countryCode" => $shippingAddress['country']['code'],
                "phoneExtension" => $shippingAddress['country']['dialing_code'],
                "companyName" => 'Mallshark'
            ];

            // Package Details Calculation
            $package = $this->convertToFedExPackageFormat($order['order_items']);

            // Get FedEx OAuth Token
            $this->client = new Client();
            $this->baseUrl = env('FEDEX_BASE_URL');

            $authResponse = $this->client->post($this->baseUrl . '/oauth/token', [
                'form_params' => [
                    'grant_type' => 'client_credentials',
                    'client_id' => env('FEDEX_API_KEY'),
                    'client_secret' => env('FEDEX_SECRET_KEY'),
                ],
            ]);

            $authData = json_decode($authResponse->getBody(), true);
            if (!isset($authData['access_token'])) {
                throw new Exception("Failed to fetch FedEx token.");
            }

            $token = $authData['access_token'];

            // Make FedEx API Request
            $response = Http::withToken($token)->post($this->baseUrl . '/ship/v1/shipments', [
                "accountNumber" => ["value" => env('ACCOUNT_NUMBER')],
                "requestedShipment" => [
                    "shipper" => [
                        "address" => [
                            "streetLines" => [$shipper["street"]],
                            "city" => $shipper["city"],
                            "stateOrProvinceCode" => $shipper["stateCode"],
                            "postalCode" => $shipper["postalCode"],
                            "countryCode" => $shipper["countryCode"]
                        ],
                        "contact" => [
                            "personName" => $shipper["name"],
                            "emailAddress" => $shipper["email"],
                            "phoneExtension" => $shipper["phoneExtension"],
                            "phoneNumber" => $shipper["phone"],
                            "companyName" => $shipper["companyName"]
                        ],
                    ],
                    "recipients" => [
                        [
                            "address" => [
                                "streetLines" => [$recipient["street"]],
                                "city" => $recipient["city"],
                                "stateOrProvinceCode" => $recipient["stateCode"],
                                "postalCode" => $recipient["postalCode"],
                                "countryCode" => $recipient["countryCode"]
                            ],
                            "contact" => [
                                "personName" => $recipient["name"],
                                "emailAddress" => $recipient["email"],
                                "phoneExtension" =>$recipient["phoneExtension"],
                                "phoneNumber" => $recipient["phone"],
                                "companyName" => $recipient["companyName"]
                            ],
                        ]
                    ],
                    "pickupType" => "DROPOFF_AT_FEDEX_LOCATION",
                    "shippingChargesPayment" => [
                        "paymentType" => "SENDER",
                        "payor" => [ //mendatory when type is recipient or other
                            "responsibleParty" => [
                                "accountNumber" => [
                                    "value" => env('ACCOUNT_NUMBER')
                                ]
                            ]
                        ]
                    ],
                    "labelSpecification" => [
                        "labelStockType" => "STOCK_4X6",
                        "imageType" => "ZPLII",
                        "labelRotation"=> "UPSIDE_DOWN",
                    ],

                    "serviceType" => $serviceType,
                    "packagingType" => "YOUR_PACKAGING",
                    "requestedPackageLineItems" => [$package],
                ],
                "labelResponseOptions" => "URL_ONLY"
            ]);

            if ($response->failed()) {            
                return response()->json([
                    'status' => false,
                    'message' => 'Request failed.',
                    'error' => $response->json(),
                ], $response->status());
            }

            $responseData = $response->json();
            $shipment = $responseData['output']['transactionShipments'][0] ?? null;
            $piece = $shipment['pieceResponses'][0] ?? null;

            if (!$shipment || !$piece) {
                throw new Exception("Invalid FedEx response: Missing shipment data.");
            }

            $formattedData = [
                'ship_label_url' => $piece['packageDocuments'][0]['url'] ?? null,
                'tracking_id' => $shipment['masterTrackingNumber'] ?? null,
                'fedex_ship_amount' => $piece['baseRateAmount'] ?? null,
                'ship_service' => $shipment['serviceName'] ?? null,
                'delivery_date' => $piece['deliveryDatestamp'] ?? null,
                'status'    => 'shipped'
            ];

            // Update Database
            $shipDetails = $this->orderRepository->updateFedExShippingDetails($orderId, $formattedData);
            if (!$shipDetails) {
                throw new Exception("Failed to update FedEx details in database.");
            }

            return response()->json([
                'status' => true,
                'message' => 'Order shiped successfully',
                'original_response' => $responseData,
                'extracted_data' => $formattedData
            ], 200);
        } catch (ItemNotFoundException $e) {
            throw new ItemNotFoundException($e->getMessage());
        } catch (AuthorizationException $e) {
            throw new AuthorizationException($e->getMessage());
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }
    
    public function printLabel($labelUrl)
    {
        try {
            $client = new Client();
            $baseUrl = env('FEDEX_BASE_URL');
    
            // Get FedEx OAuth token
            $response = $client->post($baseUrl . '/oauth/token', [
                'form_params' => [
                    'grant_type' => 'client_credentials',
                    'client_id' => env('FEDEX_API_KEY'),
                    'client_secret' => env('FEDEX_SECRET_KEY'),
                ],
            ]);
    
            $data = json_decode($response->getBody(), true);
            $token = $data['access_token'] ?? null;
    
            if (!$token) {
                throw new Exception("FedEx access token not retrieved.");
            }
    
            // Fetch the label image
            $labelResponse = Http::withToken($token)->get($labelUrl);
    
            if ($labelResponse->successful()) {
                $zplContent = $labelResponse->body();

                // Step 3: Return ZPL file as a download
                return response($zplContent, 200)
                    ->header('Content-Type', 'application/zpl')
                    ->header('Content-Disposition', 'attachment; filename="fedex-label.txt"');
            }
    
            throw new Exception("Failed to retrieve label. FedEx responded with status: " . $labelResponse->status());
    
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }
    
    public function trackFedExOrder($trackingNumber)
    {
        try {
            $client = new Client();
            $baseUrl = env('FEDEX_BASE_URL');

            // Step 1: Get FedEx OAuth token
            $authResponse = $client->post($baseUrl . '/oauth/token', [
                'form_params' => [
                    'grant_type' => 'client_credentials',
                    'client_id' => env('FEDEX_TRACK_API_KEY'),
                    'client_secret' => env('FEDEX_TRACK_SECRET_KEY'),
                ],
            ]);

            $authData = json_decode($authResponse->getBody(), true);
            $token = $authData['access_token'] ?? null;

            if (!$token) {
                throw new Exception("FedEx access token not retrieved.");
            }

            // Step 2: Prepare Tracking Request
            $payload = [
                'includeDetailedScans' => true,
                'trackingInfo' => [
                    [
                        'trackingNumberInfo' => [
                            'trackingNumber' => $trackingNumber
                        ]
                    ]
                ]
            ];

            // Step 3: Make the FedEx tracking request
            $trackResponse = Http::withHeaders([
                'Authorization' => 'Bearer ' . $token,
                'Content-Type' => 'application/json',
                'X-locale' => 'en_US'
            ])->post($baseUrl . '/track/v1/trackingnumbers', $payload);
            
            if ($trackResponse->failed()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Tracking request failed.',
                    'error' => $trackResponse->json(),
                ], $trackResponse->status());
            }

            return $trackResponse->json();

        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    public function processCancelOrder($data)
    {
        try {
            $orderId = $data['order_id'];
            $orderStatus = $data['status'];
            $isShippedCancel = $data['shipped_cancel'];
            $isRefund = $data['is_refund'];
            $processItems = $data['process_items'];
            $processItemIds = array_column($processItems, 'order_item_id');

            if (empty($processItemIds)) {
                throw new Exception('No items to process for cancellation.');
            }

            $orderDetails = $this->getOrderDataForShipping($orderId);
            $order = $orderDetails[0];

            $paymentMethod = $order['payment_method'];
            $orderItems = $order['order_items'];
            $buyer = $order['user'];
            $shippingAddress = $order['shipping_address'];

            // Derive processed item IDs from original $orderItems
            $allItemIds = array_column($orderItems, 'order_item_id');
            $missingItemIds = array_diff($allItemIds, $processItemIds);

            // Check if any of the missing items are NOT already cancelled
            $missingItemsNotCancelled = array_filter($orderItems, function ($item) use ($missingItemIds) {
                return in_array($item['order_item_id'], $missingItemIds) && $item['status'] !== 'cancelled'; 
            });

            // Set isAll = true only if all remaining items are already cancelled
            $isAll = empty($missingItemsNotCancelled);

            // Now filter accordingly
            $filteredItems = array_values(array_filter($orderItems, function ($item) use ($processItemIds) {
                return in_array($item['order_item_id'], $processItemIds);
            }));

            $productArray = $this->buildProductArray($filteredItems);

            // Create a map of refund amounts by order_item_id
            $refundMap = collect($processItems)->keyBy('order_item_id');

            // Assign item_refund_amount to each item in $productArray
            foreach ($productArray as &$item) {
                $item['item_refund_amount'] = $refundMap[$item['order_item_id']]['item_refund_amount'] ?? 0;
            }
            unset($item);

            if ($orderStatus == OrderConstants::ORDER_CANCELLED) {
                // foreach ($orderItems as $item) {
                // //    $this->orderRepository->restoreStock($item);
                // }

                if ($this->orderRepository->processCancelOrder($orderId, $isRefund, $productArray, $paymentMethod, $buyer['user_id'])) {
                    $bladeFile = $isShippedCancel == '1'
                        ? 'mail.order-update-vendor-shipped-to-cancelled'
                        : 'mail.order-update-vendor-cancel-to-client';

                    $this->sendEmail($orderId, $orderStatus, $buyer, $shippingAddress, $productArray, $paymentMethod, $bladeFile);
                } else {
                    throw new Exception('Refund failed. Please contact the super admin.');
                }
            }

            return true;
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }
    public function editOrderNote($orderId, $orderNote)
    {
        try {
            
            return $this->orderRepository->editOrderNote($orderId, $orderNote);

        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    public function processShipOrder($data)
    {
        try {
            $orderId = $data['order_id'];
            $orderStatus = $data['status'];
            $processItems = $data['process_items'];
            $processItemIds = array_column($processItems, 'order_item_id');
            
            $orderData = $this->getOrderDataForShipping($orderId);
            $order = $orderData[0] ?? null;
            $buyer = $order['user'] ?? null;
            $buyerEmail = $buyer['email'];
            $orderItems = $order['order_items'];
            $shippingAddress = $order['shipping_address'] ?? null;
            
           // Derive processed item IDs from original $orderItems
            $allItemIds = array_column($orderItems, 'order_item_id');
            $missingItemIds = array_diff($allItemIds, $processItemIds);

            // Check if any of the missing items are NOT already shipped
            $missingItemsNotShipped = array_filter($orderItems, function ($item) use ($missingItemIds) {
                return in_array($item['order_item_id'], $missingItemIds) && $item['status'] !== 'shipped';
            });

            // Set isAll = true only if all remaining items are already shipped
            $isAll = empty($missingItemsNotShipped);

            // Now filter accordingly
            $filteredItems = array_values(array_filter($orderItems, function ($item) use ($processItemIds,$isAll) {
                return $isAll || in_array($item['order_item_id'], $processItemIds);
            }));

            $productArray = $this->buildProductArray($filteredItems);

            $userId = auth()->id();
            $store = OrderHelpers::getAccessibleStore($userId);
            if (!$store) {
                throw new ItemNotFoundException('User does not belong or have access to any store.');
            }

            if ($store->store_id == $order['store_id']) {
                foreach ($productArray as &$product) {
                    foreach ($processItems as $item) {
                        if ($product['order_item_id'] == $item['order_item_id']) {
                            $carrier = $item['carrier'] ?? null;
                            $transaction = $item['transaction'] ?? null;
                            $customUrl = $item['custom_url'] ?? null;

                            $product['tracking_id'] = $this->generateTrackingLink($carrier, $transaction, $customUrl);
                            break;
                        }
                    }
                }
                unset($product);

                $this->orderRepository->updateOrderAndItems($orderId, $orderStatus, $isAll, $processItems);

                    $html = view('mail.shipped-update-client', [
                        'shippingAddress' => $shippingAddress,
                        'status' => $orderStatus,
                        'orderItems' => $productArray,
                        'orderId' => $orderId,
                    ])->render();

                    $subject = 'Your MallShark Order Has Been Shipped!';
                    $params = [
                        'from'    => 'MallShark  ' . env('SUPPORT_EMAIL'),
                        'to'      => $buyerEmail,
                        'bcc'     => 'care@mallshark.com',
                        'subject' => $subject,
                        'html'    => "$html",
                    ];

                    $this->mailgun->SendMailgunMail($params);
                
                return true;
            } else {
                throw new AuthorizationException('Not authorized to process this order');
            }
        } catch (AuthorizationException $e) {
            throw new AuthorizationException($e->getMessage());
        }
    }

    public function editItemTracking($orderId, $carrier, $transaction, $customUrl, $trackingId)
    {
        try {
            $trackingUrl = OrderHelpers::generateTrackingLink($carrier, $transaction, $customUrl);
            $response = $this->orderRepository->editItemTracking($trackingId, $trackingUrl, $carrier);

            if ($response) {
               $buyer = OrderHelpers::getBuyerByOrderId($orderId);
               if ($buyer) {
                    $buyerName = $buyer->name;
                    $buyerEmail = $buyer->email;
                  
                    $from = env('CARE_EMAIL');
                    $subject = "Updated Tracking Information for Your Order #" . $orderId;

                    $tracking = $trackingUrl;
                    $htmlContent = view('mail.order-tracking-update', compact('buyerName', 'orderId', 'tracking'))->render();

                    $params = [
                        'from'    => $from,
                        'to'      => $buyerEmail,
                        'subject' => $subject,
                        'html'    => $htmlContent, 
                    ];
                    if (app()->environment('production')) {
                        $params['bcc'] = 'care@mallshark.com';
                    }
                    $mailgun = new MailgunController();
                    $mailgun->SendMailgunMail($params);
                    return true;
               }
            }

            return true;
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    public function buildProductArray($orderItems)
    {
        $products = [];
        foreach ($orderItems as $item) {
            $product = $item['product'];
            $products[] = [
                'order_item_id' => $item['order_item_id'],
                'title' => $item['product_name'] ?? $product['title'],
                'slug' => $product['slug'],
                'status' => $item['status'],
                'feature_image' => $item['item_image'],
                'product_type' => $product['product_type'],
                'price_total' => $item['total'],
                'price' => $item['price'],
                'quantity' => $item['quantity'],
                'sold_price' => $item['sold_price'],
                'shipping_amount' => $item['shipping_amount'],
                'customizations' => $item['customizations'] ?? null,
                'variable_image' => $item['item_image'],
                'variation_id' => $item['variation_id'] ?? null,
                'variation_info' => $item['variation_info'] ?? null
            ];
        }
        return $products;
    }

    public function sendEmail($orderId, $status, $buyer, $shippingAddress, $products, $paymentMethod, $bladeFile)
    {
        if (!$buyer) return;

        $userData = (object)[
            'name'  => $buyer['name'],
            'email' => $buyer['email'],
        ];

        $html = view($bladeFile, [
            'userData' => $userData,
            'shippingAddress' => $shippingAddress,
            'status' => $status,
            'orderItems' => $products,
            'orderId' => $orderId,
            'paymentMethod' => $paymentMethod,
        ])->render();

        $params = [
            'from' => 'MallShark ' . env('SUPPORT_EMAIL'),
            'to' => $buyer['email'],
            'subject' => Constants::CANCEL_ORDER_BUYER_SUBJECT . $orderId,
            'html' => $html,
        ];

        $this->mailgun = new MailgunController();
        $response = $this->mailgun->SendMailgunMail($params);
    }

    public function sendOrderDisputeEmail($data)
    {
        try {
            $orderId = $data['order_id'];
            $status = $data['status'];
            $processItems = $data['process_items'];
            $processItemIds = array_column($processItems, 'refund_product_id');

            $orderData = $this->getOrderDataForShipping($orderId);
            $order = $orderData[0] ?? null;
            $buyer = $order['user'] ?? null;
            $storeData = $order['store'] ?? null;
            $storeName = $storeData['store_name'] ?? 'MallShark';
            $storeSlug = $storeData['store_slug'] ?? '';
            $buyerId = $buyer['user_id'] ?? null;
            $buyerEmail = $buyer['email'];
            $orderItems = $order['order_items'];

            $userData = (object)[
                'name'  => $buyer['name'],
                'email' => $buyer['email'],
                'contact_no' => $buyer['contact_no'] ?? null,
                'zendesk_user_id' => $buyer['zendesk_user_id'] ?? null,
            ];

            // Identify disputed products
            $filteredItems = array_filter($orderItems, function ($item) use ($processItemIds) {
                return in_array($item['order_item_id'], $processItemIds);
            });

            $productArray = $this->buildProductArray($filteredItems);

            // ========== Vendor Email ==========
            $html = view('mail.order_refund_request_rejected_vendor', [
                'storeName' => $storeName,
                'order_id' => $orderId,
                'status' => $status,
                'storeData' => $storeData,
                'customerDetail' => $userData,
                'orderItems' => $productArray
            ])->render();

            $subject = Constants::DISPUTED_ORDER_BUYER_SUBJECT . $orderId;
            $toEmail = $storeData['user']['email'] ?? null;
            $sellerId = $storeData['user']['user_id'] ?? null;

            if ($toEmail) {
                $this->mailgun->SendMailgunMail([
                    'from' => 'MallShark ' . env('SUPPORT_EMAIL'),
                    'to' => $toEmail,
                    'subject' => $subject,
                    'html' => $html,
                ]);
            }

            // ========== Push Notification ==========
            OrderHelpers::pushNotification($sellerId, $subject, 'seller');

            // ========== Buyer Email ==========
            $html = view('mail.order_refund_request_rejected_client', [
                'buyerName' => $buyer['name'],
                'order_id' => $orderId,
                'mallsharkstorename' => $storeName,
                'mallsharkstoreslug' => $storeSlug,
                'storeName' => $storeName,
                'status' => $status,
                'storeData' => $storeData,
                'customerDetail' => $userData,
                'orderItems' => $productArray
            ])->render();
            
            if ($buyerEmail) {
                $this->mailgun->SendMailgunMail([
                    'from' => 'MallShark ' . env('SUPPORT_EMAIL'),
                    'to' => $buyerEmail,
                    'subject' => $subject,
                    'html' => $html,
                ]);
            }
            
            // ========== Push Notification ==========
            OrderHelpers::pushNotification($buyerId, $subject, 'buyer');
            OrderHelpers::pushNotification($sellerId, $subject, 'seller');

            // ========== Zendesk Ticket ==========
            $zendeskId = $userData->zendesk_user_id;
            if (empty($zendeskId)) {
                $zendeskResult = Zendesk::users()->createOrUpdate([
                    'name' => $userData->name,
                    'email' => $userData->email,
                    'phone' => $userData->contact_no,
                    'role' => 'end-user',
                    'details' => 'This user has been created with the API.',
                ]);
                $zendeskId = (string)$zendeskResult->user->id;

                DB::table('users')
                    ->where('user_id', $buyerId)
                    ->update(['zendesk_user_id' => $zendeskId]);
            }

            Zendesk::tickets()->create([
                'subject' => 'Order Dispute with ID ' . $orderId,
                'comment' => [
                    'body' => 'The order for ' . $userData->name . ' (Email: ' . $userData->email . ') has been disputed by the vendor.',
                ],
                'priority' => 'high',
                'requester_id' => $zendeskId,
                'type' => 'problem',
            ]);

            return true;
        } catch (Exception $e) {
            Log::error('Dispute Order Failed', ['error' => $e->getMessage()]);
            throw new Exception( $e->getMessage());
        }
    }

    public function shipUspsOrder($orderId, $processItemIds, $data)
    {
        try {
            $orderData = $this->getOrderDataForShipping($orderId);
            $order = $orderData[0] ?? null;

            if (!$order) {
                throw new ItemNotFoundException("Order not found.");
            }

            $store = $order['store'] ?? null;
            $shippingAddress = $order['shipping_address'] ?? null;
            $buyer = $order['user'] ?? null;

            if (!$store) throw new ItemNotFoundException("Store details not found.");
            if (!$buyer) throw new ItemNotFoundException("Buyer details not found.");
            if (!$shippingAddress) throw new ItemNotFoundException("Shipping address not found.");
            if (!$store['fulfillment_by_mallshark']) throw new AuthorizationException('Store not authorized to use this service');

            $userId = auth()->id();
            $store = OrderHelpers::getAccessibleStore($userId);
            if (!$store) {
                throw new ItemNotFoundException('User does not belong or have access to any store.');
            }

            if ($store['store_id'] != $order['store_id']) {
                throw new AuthorizationException('User not authorized to process this order');
            }

            list($firstName, $lastName) = OrderHelpers::splitName($store['user']['name']);
            $fromAddress = [
                "firstName"         => $firstName,
                "lastName"          => $lastName ?: 'Seller',
                "streetAddress"     => $store['street_address'],
                "city"              => $store['store_city'],
                "state"             => $store['state']['code'],
                "ZIPCode"           => $store['zip_code'],
            ];

            list($firstName, $lastName) = OrderHelpers::splitName($shippingAddress['full_name']);
            $toAddress = [
                "firstName" => $firstName,
                "lastName" => $lastName ?: 'Buyer',
                "streetAddress" => $shippingAddress['street'],
                "secondaryAddress" => $shippingAddress['apartment_no'] ?? '',
                "city" => $shippingAddress['city'],
                "state" => $shippingAddress['state']['code'],
                "ZIPCode" => $shippingAddress['post_code'],
            ];

            $products = [];
            $orderItems = $order['order_items'] ?? [];
            if (empty($orderItems)) {
                throw new ItemNotFoundException('No items in the order.');
            }

            $filteredItems = collect($orderItems)
                ->whereIn('order_item_id', $processItemIds)
                ->where('status', '==', OrderConstants::ORDER_PENDING);

            if ($filteredItems->isEmpty()) {
                throw new Exception('No valid items found to process — all items may already be shipped.');
            }

            foreach ($filteredItems as $item) {
                $products[] = [
                    "product_id"     => $item['item_id'],
                    "store_id"       => $item['store_id'] ?? null,
                    "quantity"       => $item['quantity'] ?? 0,
                    "weight"         => $item['product']['weight'] ?: 1,
                    "weight_unit"    => $item['product']['weight_type'] ?? null,
                    "length"         => $item['product']['productSize_length'] ?: 1,
                    "width"          => $item['product']['productSize_width'] ?: 1,
                    "height"         => $item['product']['productSize_height'] ?: 1,
                    "new_shipping"   => !empty($item['product']['new_shipping']) ? true : false,
                    "dimension_unit" => $item['product']['dimention_type'] ?? null,
                    "product_type"   => $item['product']['product_type'] ?? null,
                    "variation_id"   => $item['variation_id'] ?? null,
                ];
            }
            // Package Details Calculation
            $package = OrderHelpers::convertPackageFormat($products);

            $packageDescription = [
                "SKU" => $data['sku'],
                "mailClass"     => $data['mail_class'],
                "rateIndicator" => $data['rate_indicator'],
                "weightUOM"     => $package['weight']['units'],
                "weight"        => max($package['weight']['value'], 0.01),
                "dimensionsUOM" => $package['dimensions']['units'],
                "length"        => $package['dimensions']['length'],
                "height"        => $package['dimensions']['height'],
                "width"         => $package['dimensions']['width'],
                "processingCategory" => $data['processing_category'],
                "mailingDate"   => now()->toDateString(),
                "postagePriceType" => "CUSTOMER_SELECTED",
                "destinationEntryFacilityType" => $data['destination_entry_facility_type'],
            ];

            $imageInfo = [
                "imageType"       => "PDF",
                "labelType"       => "4X6LABEL",
                "receiptOption"   => "NONE",
                "suppressPostage" => false,
                "suppressMailDate" => true
            ];

            $token = $this->getUspsAccessToken();
            $paymentToken = $this->getUspsPaymentToken();
            $baseUrl = env('USPS_BASE_URL');

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $token,
                'X-Payment-Authorization-Token' => $paymentToken,
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ])->post($baseUrl . '/labels/v3/label', [
                "imageInfo"          => $imageInfo,
                "toAddress"          => $toAddress,
                "fromAddress"        => $fromAddress,
                "packageDescription" => $packageDescription
            ]);

            if ($response->failed()) {
                $formattedError = OrderHelpers::filterUspsError($response->json());
                return response()->json($formattedError, $response->status());
            }

            $responseBody = $response->body();
            // --- Extract JSON (labelMetadata) ---
            preg_match('/\{(?:[^{}]|(?R))*\}/s', $responseBody, $jsonMatch);
            $labelMetadata = isset($jsonMatch[0]) ? json_decode($jsonMatch[0], true) : null;
            // --- Extract PDF binary safely ---
            $pdfData = null;
            if (preg_match(
                '/Content-Disposition:\s*form-data;\s*filename="labelImage\.pdf".*?\r\n\r\n(.*?)\r\n--/s',
                $responseBody,
                $pdfMatch
            )) {
                $pdfData = trim($pdfMatch[1]);
            }

            // --- Save the PDF if found ---
            $labelUrl = null;
            if ($pdfData) {
                $labelDir = public_path('storage/usps_labels');
                if (!file_exists($labelDir)) {
                    mkdir($labelDir, 0775, true);
                }

                $random = str_random(5);
                $fileName = "label_{$orderId}_{$random}.pdf";
                $filePath = "{$labelDir}/{$fileName}";

                // Write raw binary (not base64 decode)
                file_put_contents($filePath, base64_decode($pdfData));

                $labelUrl = asset("storage/usps_labels/{$fileName}");
            }

            $trackingUrl = null;

            if (
                !empty($labelMetadata['links'][0]['rel']) &&
                in_array('Tracking URL', $labelMetadata['links'][0]['rel']) &&
                !empty($labelMetadata['links'][0]['href'])
            ) {

                $trackingUrl = $labelMetadata['links'][0]['href'];
            }

            $shipAmount = $labelMetadata['postage'] ?? null;
            $deliveryDate = $labelMetadata['commitment']['scheduleDeliveryDate'] ?? null;
            $shipService = 'usps';
            $shippedManually = false;

            $this->orderRepository->updateOrderItemsShipped($orderId, $trackingUrl, $labelUrl, $shipAmount, $shipService, $deliveryDate, $shippedManually, $processItemIds);

            $subject = Constants::SHIPPED_ORDER_BUYER_SUBJECT . $orderId;
            OrderHelpers::pushNotification($buyer['user_id'], $subject, 'buyer', '/orders/' . $orderId);

            $buyerEmail = $buyer['email'];

            $productArray = $this->buildProductArray($filteredItems);
            foreach ($productArray as &$product) {
                $product['tracking_id'] = $trackingUrl;
            }
            unset($product);

            $html = view('mail.shipped-update-client', [
                'shippingAddress' => $shippingAddress,
                'status' => OrderConstants::ORDER_SHIPPED,
                'orderItems' => $productArray,
                'orderId' => $orderId,
            ])->render();

            $params = [
                'from'    => 'MallShark  ' . env('SUPPORT_EMAIL'),
                'to'      => $buyerEmail,
                'subject' => $subject,
                'html'    => "$html",
            ];

            $bccEmails = ['care@mallshark.com'];

            if (app()->environment('production')) {
                $params['bcc'] = $bccEmails;
            }

            $this->mailgun->SendMailgunMail($params);

            return response()->json([
                'status' => true,
                'message' => 'Order shipped successfully',
                'data' => [
                    'labelMetadata' => $labelMetadata,
                    'labelUrl' => $labelUrl,
                ]
            ]);
        } catch (ItemNotFoundException $e) {
            throw new ItemNotFoundException($e->getMessage());
        } catch (AuthorizationException $e) {
            throw new AuthorizationException($e->getMessage());
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    public function getUspsAccessToken()
    {
        try {

            $baseUrl = env('USPS_BASE_URL');
            $client = new Client();

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

    public function getUspsPaymentToken()
    {
        try {
            $accessToken = $this->getUspsAccessToken();
            $baseUrl = env('USPS_BASE_URL');

            $payload = [
                "roles" => [
                    [
                        "roleName"     => "PAYER",
                        "CRID"         => env('USPS_CRID'),
                        "MID"          => env('USPS_MAILER_ID'),
                        "manifestMID"  => env('USPS_MAILER_ID'),
                        "accountType"  => "EPS",
                        "accountNumber"=> env('USPS_EPS_NUMBER'),
                    ],
                    [
                        "roleName"     => "LABEL_OWNER",
                        "CRID"         => env('USPS_CRID'),
                        "MID"          => env('USPS_MAILER_ID'),
                        "manifestMID"  => env('USPS_MAILER_ID'),
                    ]
                ]
            ];

            $response = Http::retry(3, 200)->withToken($accessToken)
                ->acceptJson()
                ->post($baseUrl . '/payments/v3/payment-authorization', $payload);

            if ($response->failed()) {
                throw new Exception("Failed to fetch USPS Payment Authorization Token: " . $response->body());
            }

            $json = $response->json();
            return $json['paymentAuthorizationToken'] ?? null;

        } catch (Exception $e) {
            throw new Exception("USPS Payment Token Error: " . $e->getMessage());
        }
    }

    public function sendWalletAddressEmail($userId) 
    {
        try {
            $user = OrderHelpers::getUserById($userId);
            if (!$user) {
                throw new ItemNotFoundException("User not found.");
            }

            $userName = $user->name;
            $userEmail = $user->email;

            $html = view('mail.add-wallet-address-request', [
                'activate_url' => env('FRONT_END_URL').'/login?'.'wallet=true',
                'userName' => $userName,
            ])->render();

            $params = [
                'from'    => 'MallShark  ' . env('SUPPORT_EMAIL'),
                'to'      => $userEmail,
                'subject' => 'Complete Your Setup: Add Wallet Address to Receive Refunds',
                'html'    => "$html",
            ];

            $this->mailgun = new MailgunController();
            $this->mailgun->SendMailgunMail($params);

            return true;

        } catch (ItemNotFoundException $e) {
            throw new ItemNotFoundException($e->getMessage());
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    public function markItemsAsRefunded($orderId, $refundItemIds)
    {
        try {
            $refundProducts = $this->orderRepository->markItemsAsRefunded($orderId, $refundItemIds);

            if ($refundProducts->isNotEmpty()) {

                $orderItems = OrderHelpers::getOrderItems($orderId);
                $refundedOrderItemIds = $refundProducts->pluck('product_id')->toArray();

                $filteredItems = $orderItems->whereIn('order_item_id', $refundedOrderItemIds);
                
                // Build lookup for refund details
                $refundLookup = $refundProducts->keyBy('product_id');

                // Build email product array
                $productArray = $this->buildProductArray($filteredItems);

                // Add refund info to each item
                foreach ($productArray as &$item) {
                    $refundData = $refundLookup[$item['order_item_id']] ?? null;
                    $item['item_refund_amount']   = $refundData->price + $refundData->shipping_amount ?? 0;
                }
                unset($item);
                
                // Prepare and send email
                $buyer = OrderHelpers::getBuyerByOrderId($orderId);
                $shippingAddress = null;
                $paymentMethod = null;

                $this->sendEmail($orderId, 'Refunded', $buyer, $shippingAddress, $productArray, $paymentMethod, 'mail.order-update-client-refunded');
            }

            return true;
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }
}
