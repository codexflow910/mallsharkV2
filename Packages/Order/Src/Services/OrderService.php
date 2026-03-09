<?php

namespace Packages\Order\Services;

use Exception;
use Carbon\Carbon;
use Stripe\Refund;
use Stripe\Stripe;
use Illuminate\Support\Facades\Auth;
use Packages\Order\Models\StoreOrder;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Http;
use Packages\Order\Contracts\OrderHelpers;
use App\Http\Controllers\MailgunController;
use Illuminate\Support\ItemNotFoundException;
use Illuminate\Validation\UnauthorizedException;
use GuzzleHttp\Exception\RequestException;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Packages\Auth\Models\{User,DeviceToken};
use App\Services\SellerFcmService;
use Illuminate\Support\Facades\URL;
use Packages\Order\Config\OrderConstants;
use Packages\Order\Contracts\BaseApiResponse;
use Huddle\Zendesk\Facades\Zendesk;
use Illuminate\Auth\Access\AuthorizationException;
use Packages\Core\Contracts\CoreHelpers;
use Packages\Order\Repositories\Eloquent\OrderRepository;

class OrderService
{
    use OrderHelpers;
    use CoreHelpers;
    protected $orderRepository;
    protected $apiKey;
    protected $client;
    protected $baseUrl;
    private $mailgun;

    public function __construct(OrderRepository $orderRepository)
    {
        $this->mailgun = new MailgunController();
        $this->orderRepository = $orderRepository;
        $this->apiKey = env('STRIPE_API_KEY');

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
                foreach ($order['order_items_list'] as $products) {
                    $modifiedProducts = [];
                    foreach ($products['products'] as $product) {
                        $product['feature_image'] = getAttachmentUrlById($product['feature_image']);
                       
                        $modifiedProducts[] = $product;
                    }
                    $products['products'] = $modifiedProducts;
                    $order_items_list[] = $products;
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

        $store = $this->orderRepository->getStoreInfoByUserId($userId);
        $currentDate = Carbon::today();

        // Get today's new, pending, refunded, and dispute orders
        $newOrders = $this->orderRepository->getTodayNewOrders($store['store_id'], $currentDate);
        $pendingOrders = $this->orderRepository->getTodayPendingOrders($store['store_id'], $currentDate);
        $refundedOrders = $this->orderRepository->getTodayRefundedOrders($store['store_id'], $currentDate);
        $disputeOrders = $this->orderRepository->getTodayDisputeOrders($store['store_id'], $currentDate);

        if (!$store) {
            throw new ItemNotFoundException("Store not found.");
        }

        $orders_all = $this->orderRepository->getOrdersWithItems($store['store_id'], $order_id, $order_status, $keyword, $zipcode, $From_Date,$To_Date);
      
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
                            foreach ($orders['order_items_list'] as $products) {
                                $modifiedProducts = [];
                                if($products['variation_id'] == 0){
                                    foreach ($products['products'] as $product) {
                                        $product['feature_image'] = getAttachmentUrlById(intval($product['feature_image']), 'media');
                                        $modifiedProducts[] = $product;
                                    }
                                    
                                }else if($products['variation_id'] != 0){
                                    $variation_image = $this->orderRepository->getVariationImage($products['variation_id']);
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
    
     public function updateOrderStatus($input)
    {
        try {
         $store =  $this->orderRepository->updateOrderStatus($input);
        
        return $store;}
          catch (Exception $e) {
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
    public function return_refund_stripe($input)
    {
        try {
           
         $status = "refunded";
          
              $storeOrder =  $this->orderRepository->getOrderByOrderId($input['order_id']);
             $storeOrder->order_status = 'refunded';
             $storeOrder->is_refund = 'refund';
             $storeOrder->refund_amount = $input['amount'];
             $storeOrder->payment_status = $status;
            $storeOrder->update();      
            $store =  $this->orderRepository->return_refund_stripe($storeOrder,$input);
            return $store;
    }
          catch (Exception $e) {
               throw new Exception($e->getMessage());
            }
    }
      public function rejectOrderStoreOwner($input)
    {
   try {
            $storeOrder =  $this->orderRepository->getOrderByOrderId($input['order_id']);
            $storeOrder->order_status = 'refunded';
            $buyer_id = $input['buyer_id'];
            $order_id = $input['order_id'];
            $storeData = $storeOrder->store;
            $status = $input['order_status'];
            $message =  $this->orderRepository->rejectOrderStoreOwner($storeOrder,$input);
            return $message;
    }
          catch (Exception $e) {
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

            $order['order_status'] = "cancelled";
            $order['refund_date'] = now()->format('Y-m-d H:i:s');
            $order['is_refund'] = "cancelled";
            $order->save();

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

            if (is_array($data['cancel_product_id'])) {
                $refunds = [];
            
                foreach ($data['cancel_product_id'] as $productId) {
                    $matchedItem = $order['orderItems']->firstWhere('item_id', $productId);
            
                    if ($matchedItem) {
                        $refundData = array_merge($data, [
                            'product_id'     => $productId,
                            'quantity'       => $matchedItem->quantity,
                            'price'          => $matchedItem->sold_price > 0 ? $matchedItem->sold_price : $matchedItem->price,
                            'shipment'       => $matchedItem->shipping_amount ?? 0,
                            'product_image'  => $matchedItem->item_image,
                            'product_title'  => $matchedItem->product_name ?? 'Product',
                            'product_slug'   => optional($matchedItem->product)->slug ?? '',
                        ]);
            
                        $this->orderRepository->productRefund($refundData);
            
                        $refunds[] = $refundData;
                    }
                }
            
                // Merge base data with cancel_product_id list
                $finalData = array_merge($data, [
                    'cancel_product_id' => $refunds
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
            $this->pushNotification($storeData['user_id'], $subjectStore, 'seller');
            $this->addNotifications($storeData['user_id'], $buyer['user_id'], $subjectStore, '', '/order-detail/' . $data['order_id'], 'brand_order_details', $data['order_id'], 'seller');

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
                        if (!empty($item['products'])) {
                            foreach ($item['products'] as &$product) {
                                if (!empty($product['feature_image'])) {
                                    $product['feature_image_url'] = $this->getAttachmentUrlById($product['feature_image']);
                                }
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
                        "labelStockType" => "PAPER_7X475",
                        "imageType" => "PNG",
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
}
