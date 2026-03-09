<?php

namespace Packages\Order\Contracts\V3;

use App;
use Request;
use Session;
use Exception;
use App\BaseAccess;
use App\Constants;
use App\Events\NewNotificationEvent;
use Packages\Auth\Models\V3\User;
use Mailgun\Mailgun;
use Packages\Product\Models\V3\{ProductVariation,Product,Attachments};
use Packages\Auth\Models\V3\DeviceToken;
use Packages\Order\Models\V3\{StoreOrder,OrderItems};
use App\Http\Controllers\StripeController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Packages\Store\Models\V3\Store;
use App\Services\FCMService;
use App\Services\SellerFcmService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\URL;
use App\Http\Controllers\MailgunController;
use Illuminate\Support\Facades\Log;
use Packages\Import\Http\Controllers\V3\ShopifyController;
use Packages\Order\Config\OrderConstants;

trait OrderHelpers
{
    protected function getAttachmentUrlByIdAvatar($id, $size = 'avatar')
    {
        $attachement = new Attachments();

        if ($id != '' && is_int($id)) {
            $attachement->id = $id;
            $attachement->size = $size;

            return $attachement->getAttachmentUrlByIdAvatar();
        } else {
            return 'Please add a valid id';
        }
    }

   
    protected function getVariationsById($variationID)
    {
        $variationDetails = DB::table('product_variation')
            ->where('variation_id', $variationID)
            ->first();

        return $variationDetails;
    }

    function getAttachmentUrlSmallById($id, $size = 'small') {
        $global_obj = new Attachments();
        if ($id != '' && is_int($id)) {
            $global_obj->id = $id;
            $global_obj->size = $size;
    
            return $global_obj->getAttachmentUrlById();
        } else {
            return 'Please add a valid id';
        }
    }
    protected function getOrderDetailByOrderIdApi($order_id)
    {
  
        $orderdetails = StoreOrder::with([
            'orderItemsList' => function ($itemlist) {
                $itemlist->select('order_item_id','order_id', 'item_id', 'store_id', 'quantity', 'price','sold_price', 'shipping_amount', 'shipment_id', 'label_url','lable_pdf_url','tracking_id','tracking_code','method', 'total','payment_service', 'created_at');
            }, 
            'shippingAddress' => function ($shipping) {
                $shipping->select('sh_id', 'country', 'full_name', 'state', 'city', 'street', 'post_code', 'flat_other');
            },
            // 'shippingAddress.country',
            'store' => function ($store) {
                $store->select('store_id', 'store_country', 'store_state', 'store_city', 'street_address', 'store_name', 'store_slug', 'store_logo', 'contact_info', 'store_status' );
            },
            'user' => function ($user) {
                $user->select('user_id', 'name', 'slug', 'email', 'status', 'user_country_id', 'user_state_id','user_zip_code', 'contact_no', 'physical_address' );
            }
        ])->select('order_id', 'buyer_id', 'store_id', 'payment_method', 'total_amount', 'payable_amount', 'payment_status', 'shipped_date','order_status','shipping_amount','shipping_address')
        ->where('order_id', $order_id)->get()->toArray();
       
        if (count($orderdetails) > 0) {
            return $orderdetails;
        } else {
            return false;
        }
      
    }
     public function getOrderPdfItems($order_id)
    {
        $orderdetails =  StoreOrder::with('orderItems', 'shippingAddress', 'store', 'user')->where('order_id', $order_id)->get();
       
        $orders = array();
        if ($order_id != '') {
            $orders = StoreOrder::where('store_orders.order_id', '=', $order_id)
                ->join('order_items', 'store_orders.order_id', '=', 'order_items.order_id')
                ->join('stores', 'store_orders.store_id', '=', 'stores.store_id')
                ->join('shipping_addresses', 'store_orders.shipping_address', '=', 'shipping_addresses.sh_id')
                ->select(
                    'store_orders.order_id',
                    'store_orders.store_tax',
                    'stores.store_id',
                    'stores.store_logo',
                    'store_orders.buyer_id',
                    'store_orders.order_status',
                    'store_orders.shipping_amount',
                    'store_orders.shipping_address',
                    'store_orders.payment_method',
                    'store_orders.total_amount',
                    'store_orders.created_at',
                    'order_items.item_id',
                    'order_items.quantity',
                    'order_items.variation_id',
                    'order_items.order_note',
                    'order_items.price as order_per_price',
                    'order_items.shipping_amount as order_per_shipping',
                    'shipping_addresses.street',
                    'shipping_addresses.country',
                    'shipping_addresses.flat_other',
                    'order_items.total as order_total',
                    'shipping_addresses.city',
                    'shipping_addresses.state',
                    'shipping_addresses.full_name',
                    'shipping_addresses.user_id',
                    'shipping_addresses.post_code'
                )
                ->get()
                ->toArray();
               
        }
        if ($orderdetails) {
            $storeLogo = $orderdetails->first()->store->store_logo ?? null;

            // Convert store_logo ID to URL
            $url = $storeLogo ? $this->getAttachmentUrlById($storeLogo) : null;

            // Update store_logo field in the order details collection
            $orderdetails->first()->store->store_logo = $url;

                return $orderdetails;
            } else {
                return;
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
    private function current_user_can_update_order_api($order_id)
    {
        
        $val = false;
        if ($order_id != '') {
        
            $current_user_id = Auth::user()->user_id;

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
       public function getStoreInfoUserId($userId)
    {
        $store = Store::where('user_id', $userId)->first();
    
        if ($store) {
            return $store->toArray();
        } else {
            return 0;
        }
    }
     public function getOrdersWithItemsAllNewApiNoPagination($store_id, $order_id = null, $order_status = null)
    {
        $query = StoreOrder::with('orderItemsList','user','shippingAddress')->where('store_id', $store_id);
    
        if ($order_id !== null && $order_status !== null) {
            
            $storeOrder = $query->where('order_id', $order_id)->get();
    
            if ($storeOrder) {
                return $storeOrder->toArray();
            } else {
                return false;
            }
        } elseif ($order_id !== null) {
          
            $storeOrder = $query->where('order_id', $order_id)->get();
    
            if ($storeOrder) {
                return $storeOrder->toArray();
            } else {
                return false;
            }
        } elseif ($order_status !== null) {
        
            $storeOrders = $query->where('order_status', $order_status)->orderBy('created_at', 'desc')->get();
    
            if (count($storeOrders) > 0) {
                return $storeOrders;
            } else {
                return false;
            }
        } else {
            
            $storeOrders = $query->orderBy('created_at', 'desc')->get();
            if (count($storeOrders) > 0) {
                return $storeOrders->toArray();
            } else {
                return false;
            }
        }
    }
    
    public function getOrderDataForShipping($id) {
        $data = StoreOrder::with([
            'orderItems.product',
            'shippingAddress',
            'store.country', 
            'store.state',  
            'store.user',
            'user.state',
            'user.country',
        ])
        ->where('order_id', $id)
        ->get()
        ->toArray();

        return $data;
    }

    public function sendFedExShippedMail($orderId, $trackingUrl)
    {
        $message = Constants::SHIPPED_ORDER_BUYER_SUBJECT . $orderId;
        $this->mailgun = new MailgunController();
        
        $orderData = $this->getOrderDataForShipping($orderId);
        $order = $orderData[0] ?? null;

        $shippingAddress = $order['shipping_address'] ?? null;
        $buyer = $order['user'] ?? null;
        $buyerEmail = $buyer['email'];
        $buyerId = $buyer['user_id'];

        $productArray = [];

        foreach ($order['order_items'] as $item) {
            $product = $item['product'] ?? [];

            $productArray[] = [
                'title' => $item['product_name'] ?? $product['title'] ?? '',
                'slug' => $product['slug'] ?? '',
                'feature_image' => $item['item_image'] ?? $product['feature_image'] ?? '',
                'product_type' => $product['product_type'] ?? '',
                'price_total' => $item['total'],
                'price' => $item['price'],
                'quantity' => $item['quantity'],
                'sold_price' => $item['sold_price'],
                'shipping_amount' => $item['shipping_amount'],
                'variable_image' => $item['item_image'] ?? $product['feature_image'] ?? '',
            ];
        }

        self::pushNotification($buyerId, $message, 'buyer', '/new-buyer-orders-detail/' . $orderId);

        $html = view('mail.fedex-shipped-client', [
            'userData' => $buyer,
            'shippingAddress' => $shippingAddress,
            'trackingUrl' => $trackingUrl,
            'orderItems' => $productArray,
            'orderId' => $orderId
        ])->render();

        $subject = 'Your MallShark Order Has Been Shipped!';

        $bccEmails = ['care@mallshark.com'];

        $params = [
            'from'    => 'MallShark  ' . env('SUPPORT_EMAIL'),
            'to'      => $buyerEmail,
            'bcc'     => implode(',', $bccEmails),
            'subject' => $subject,
            'html'    => $html,
        ];

        return $this->mailgun->SendMailgunMail($params);
    }


    function convertToFedExPackageFormat(array $orderItems): array
    {
        $packageWeight = 0;
        $maxLength = $maxWidth = 0;
        $totalHeight = 0;

        foreach ($orderItems as $item) {
            if (!empty($item['product'])) {
                $product = $item['product'];
                $quantity = (int) ($item['quantity'] ?? 1);

                // Weight conversion
                $weight = (float) $product['weight'];
                $weightType = strtolower($product['weight_type'] ?? 'lb');

                switch ($weightType) {
                    case 'kg':
                    case 'kilogram':
                        $weight *= 2.20462;
                        break;
                    case 'g':
                    case 'gram':
                        $weight *= 0.00220462;
                        break;
                    case 'oz':
                    case 'ounce':
                        $weight *= 0.0625;
                        break;
                    case 'lb':
                    case 'pound':
                    default:
                        // already in pounds
                        break;
                }

                $packageWeight += ($weight * $quantity);

                // Dimension conversion
                $length = self::convertToInches($product['productSize_length'] ?? 0, $product['productSize_unit'] ?? 'in');
                $width = self::convertToInches($product['productSize_width'] ?? 0, $product['productSize_unit'] ?? 'in');
                $height = self::convertToInches($product['productSize_height'] ?? 0, $product['productSize_unit'] ?? 'in');

                $maxLength = max($maxLength, $length);
                $maxWidth = max($maxWidth, $width);
                $totalHeight += ($height * $quantity);
            }
        }

        return [
            'weight' => [
                'units' => 'LB',
                'value' => round($packageWeight, 2)
            ],
            'dimensions' => [
                'length' => round($maxLength, 2),
                'width' => round($maxWidth, 2),
                'height' => round($totalHeight, 2),
                'units' => 'IN'
            ]
        ];
    }



    public function getStoreOrderByOrderId($orderId) {
        $data = StoreOrder::with([
            'orderItems.product',
            'shippingAddress',
            'store', 
            'store.user',
            'user',
        ])
        ->where('order_id', $orderId)
        ->get();

        return $data;
    }
    
    public static function generateTrackingLink($carrier, $transaction, $customUrl = null)
    {
        $urls = [
            'ups'            => "https://www.ups.com/track?loc=en_US&tracknum=",
            'usps'           => "https://tools.usps.com/go/TrackConfirmAction?tLabels=",
            'fedx'           => "https://www.fedex.com/fedextrack/?tracknumbers=",
            'dhl'            => "https://www.dhl.com/global-en/home/tracking.html?tracking-id=",
            'canada_post'    => "https://www.canadapost-postescanada.ca/track-reperage/en#/search?searchFor=",
            'royal_mail'     => "https://www.royalmail.com/track-your-item#/tracking-results/",
            'australia_post' => "https://auspost.com.au/mypost/track/#/details/",
            'china_post'     => "http://track-chinapost.com/?tr=",
            'japan_post'     => "https://trackings.post.japanpost.jp/services/srv/search/?requestNo1=",
            'hermes'         => "https://www.myhermes.co.uk/track#/parcel/",
            'dpd'            => "https://www.dpd.com/tracking?parcelNumber=",
            'gls'            => "https://gls-group.com/track?trackingNumber=",
            'aramex'         => "https://www.aramex.com/track/shipments/",
            'tnt'            => "https://www.tnt.com/express/en_us/site/tracking.html?searchType=con&cons=",
            'amazon'         => "https://track.amazon.com/tracking/"
        ];

        // Match known carrier first
        if (isset($urls[$carrier])) {
            return $urls[$carrier] . $transaction;
        }

        if ($customUrl) {
            return $customUrl;
        }

        return $transaction;
    }

    public static function resolveOrderStatus(array $itemStatuses)
    {
        $statusCounts = array_count_values($itemStatuses);
        // 1. Mixed: pending + shipped → Partial Shipped
        if (isset($statusCounts['pending']) && isset($statusCounts['shipped'])) {
            return OrderConstants::ORDER_PARTIAL_SHIPPED;
        }

        // 2. Mixed: shipped + refunded or shipped + cancelled → Shipped
        if (
            isset($statusCounts['shipped']) &&
            (isset($statusCounts['refunded']) || isset($statusCounts['cancelled']) || isset($statusCounts['disputed'])) &&
            !isset($statusCounts['pending'])
        ) {
            return OrderConstants::ORDER_SHIPPED;
        }

        // 3. Mixed: refunded + cancelled (no shipped/pending) → Refunded
        if (
            isset($statusCounts['refunded']) &&
            isset($statusCounts['cancelled']) &&
            count($statusCounts) === 2
        ) {
            return OrderConstants::ORDER_REFUNDED;
        }

        // 4. Only pending
        if (isset($statusCounts['pending']) && count($statusCounts) === 1) {
            return OrderConstants::ORDER_PENDING;
        }

        // 5. Only shipped
        if (isset($statusCounts['shipped']) && count($statusCounts) === 1) {
            return OrderConstants::ORDER_SHIPPED;
        }

        // 6. Only refunded
        if (isset($statusCounts['refunded']) && count($statusCounts) === 1) {
            return OrderConstants::ORDER_REFUNDED;
        }

        // 7. Only cancelled
        if (isset($statusCounts['cancelled']) && count($statusCounts) === 1) {
            return OrderConstants::ORDER_CANCELLED;
        }

        // 8. Only disputed
        if (isset($statusCounts['disputed']) && count($statusCounts) === 1) {
            return OrderConstants::ORDER_DISPUTED;
        }

        //9 . Mixed refunded/cancelled + pending → Pending
        if (
            (isset($statusCounts['refunded']) || isset($statusCounts['cancelled']) || isset($statusCounts['disputed'])) &&
            isset($statusCounts['pending'])
        ) {
            return OrderConstants::ORDER_PENDING;
        }

        //10 . Mixed refunded/cancelled (without shipped or pending) → Refunded
        if (isset($statusCounts['refunded']) || isset($statusCounts['cancelled'])) {
            return OrderConstants::ORDER_REFUNDED;
        }

        return OrderConstants::ORDER_PENDING;
    }
    public static function getBuyerByOrderId($orderId)
    {
        $order = StoreOrder::with('user')->where('order_id', $orderId)->first();
    }

    public static function getAccessibleStore($userId)
    {
        $store = Store::where('user_id', $userId)->first();

        if (!$store) {
            $pivot = DB::table('store_user')->where('user_id', $userId)->first();

            if ($pivot) {
                $store = Store::find($pivot->store_id);
            }
        }

        return $store;
    }

    function getAttachmentUrlById($id, $size = 'media')
    {

        $attachement = new Attachments();
        if (isset($id))
            $id = json_decode($id);

        if ($id != '') {
            if (is_int($id)) {
                $attachement->id = $id;
                $attachement->size = $size;

                return $attachement->getAttachmentUrlById();
            } else {
                return false;
            }
        } else {
            return 'Please add a valid id';
        }
    }

    public static function getOrderById($orderId)
    {
        return StoreOrder::where('order_id', $orderId)->first();
    }

    /**
    * Push notifications
    */
    public static function pushNotification($userId, $message, $appFlag = null, $url = null)
    {
        $deviceTokens = DeviceToken::where('user_id', $userId)
            ->pluck('fcm_token');
    
        if ($deviceTokens->isEmpty()) {
            return response()->json(['status' => false, 'message' => 'No device tokens found.'], 404);
        }
    
        $service = ($appFlag == 'seller') ? SellerFcmService::class : FCMService::class;
        $failedTokens = [];
        $successTokens = [];
    
        $deviceTokens->each(function ($token) use ($service, $message, $url, &$failedTokens, &$successTokens) {
            try {
                $notificationData = [
                    'title' => $message,
                ];

                if ($url) {
                    $notificationData['url'] = $url;
                }

                $service::send($token, $notificationData);
                $successTokens[] = $token; // Track successful tokens
            } catch (\Exception $e) {
                Log::error("FCM Notification Failed for Token: $token | Error: " . $e->getMessage());
                $failedTokens[] = $token; // Track failed tokens
            }
        });
    
        return response()->json([
            'status' => true,
            'message' => 'Push notifications processed.',
            'success_tokens' => $successTokens,
            'failed_tokens' => $failedTokens,
        ]);
    }

    public static function convertPackageFormat(array $products)
	{
		$packageWeight = 0;
		$maxLength = $maxWidth = 0;
		$totalHeight = 0;

		foreach ($products as $product) {
			$quantity = (int) ($product['quantity'] ?? 1);

			// Weight conversion
			$weight = self::convertToPounds((float) $product['weight'], $product['weight_unit'] ?? 'lb');
			$packageWeight += ($weight * $quantity);

			// Dimension conversion
			$length = self::convertToInches($product['length'] ?? 0, $product['dimension_unit'] ?? 'in');
			$width  = self::convertToInches($product['width'] ?? 0, $product['dimension_unit'] ?? 'in');
			$height = self::convertToInches($product['height'] ?? 0, $product['dimension_unit'] ?? 'in');

			$maxLength = max($maxLength, $length);
			$maxWidth = max($maxWidth, $width);
			$totalHeight += ($height * $quantity);
		}

		return [
			'weight' => [
				'units' => 'lb',
				'value' => round($packageWeight, 2)
			],
			'dimensions' => [
				'length' => round($maxLength, 2),
				'width' => round($maxWidth, 2),
				'height' => round($totalHeight, 2),
				'units' => 'in'
			]
		];
	}

    public static function convertToPounds(float $weight, string $unit): float
	{
		return match (strtolower($unit)) {
			'kg', 'kilogram' => $weight * 2.20462,
			'g', 'gram'      => $weight * 0.00220462,
			'oz', 'ounce'    => $weight * 0.0625,
			default          => $weight,
		};
	}

    public static function convertToInches($value, $unit)
    {
        $value = (float) $value;
        switch (strtolower($unit)) {
            case 'cm':
                return $value * 0.393701;
            case 'm':
                return $value * 39.3701;
            case 'mm':
                return $value * 0.0393701;
            case 'ft':
            case 'feet':
                return $value * 12;
            case 'in':
            case 'inch':
            default:
                return $value;
        }
    }

    public static function splitName($name)
    {
        $nameParts = explode(' ', trim($name));
        $firstName = array_shift($nameParts);
        $lastName = implode(' ', $nameParts);
        return [$firstName, $lastName];
    }

    /**
     * Add a new notification and dispatch event.
    **/
    public static function addNotifications(
        $recipientId,
        $senderId,
        $subject,
        $description,
        $urlLink,
        $returnTab = null,
        $notificationActionId = null,
        $appFlag = null
    ) {
        $data = [
            'recipient_id'           => $recipientId ?? '',
            'sender_id'              => $senderId ?? '',
            'subject'                => $subject ?? '',
            'description'            => $description ?? '',
            'url_link'               => $urlLink ?? '',
            'created_at'             => now(), // Laravel helper for date
            'app_flag'               => $appFlag,
            'return_tab'             => $returnTab,
            'notification_action_id' => $notificationActionId,
        ];

        // Insert and get ID of the new notification
        $notificationId = DB::table('notifications')->insertGetId($data);

        if (!$notificationId) {
            return false; // Insertion failed
        }

        // Update has_notification flag for both users
        DB::table('users')
            ->whereIn('user_id', [$recipientId, $senderId])
            ->update(['has_notification' => 1]);

        // Retrieve newly inserted notification
        $notification = DB::table('notifications')
            ->where('notification_id', $notificationId)
            ->first();

        // Fire event if successful
        if ($notification) {
            event(new NewNotificationEvent($recipientId, $notification));
            return true;
        }

        return false;
    }

    public static function getOrderItems($orderId)
    {
        return OrderItems::with('product')->where("order_id", $orderId)->get();
    }

    public static function getStoreById($storeId)
	{
		return Store::where('store_id', $storeId)->first();
	}

    public static function getUserById($userId)
    {
        return User::where('user_id', $userId)->first();
    }

    public static function getShippingAddressbyUserId($userId)
    {
        $query = DB::table('shipping_addresses')->where('user_id', $userId)->latest('sh_id')->first();
        if (!empty($query)) {
            return objToArray($query);
        } else {
            return false;
        }
    }

    public static function getProductIdSpecificColums($productId)
    {
        return Product::where('product_id', '=', $productId)->select('product_id', 'title', 'slug', 'feature_image', 'product_type')->first();
    }
    
    public static function getvariableByProduct_id($variationId)
    {
        $query = DB::table('product_variation')->select('variation_id', 'product_variation_image', 'price', 'sale_price', 'option1', 'option2', 'option3', 'unit1', 'unit2', 'unit3', 'attribute1', 'attribute2', 'attribute3', 'current_stock')->where('variation_id', $variationId)->first();
        if (!empty($query)) {
            return $query;
        } else {
            return false;
        }
    }

    public static function filterUspsError($response)
    {
        if (is_string($response)) {
            $response = json_decode($response, true);
        }

        $error = $response['error'] ?? [];
        $message = $error['message'] ?? $response['message'] ?? 'USPS request failed.';
        $code = $error['code'] ?? $response['code'] ?? null;
        $errors = $error['errors'] ?? $response['errors'] ?? [];

        $formattedMessage = $message;

        // 🧩 USPS-style error handling (array of errors)
        if (!empty($errors)) {
            $details = [];

            foreach ($errors as $err) {
                $param = $err['source']['parameter'] ?? null;
                $detail = $err['detail'] ?? 'Unknown issue';
                $codePart = isset($err['code']) ? "(Code: {$err['code']})" : '';

                if ($param) {
                    $details[] = ucfirst($param) . ": " . $detail . " {$codePart}";
                } else {
                    $details[] = "{$detail} {$codePart}";
                }
            }

            // Append all details to message
            $formattedMessage .= ' — ' . implode(', ', array_unique($details));
        }

        // 🧠 Fallback for OpenAPI validation errors
        if (str_contains($message, 'OpenAPI-Spec-Validation')) {
            $issues = [];

            preg_match_all("/Path '\/([^']+)'/", $message, $matches);
            if (!empty($matches[1])) {
                foreach ($matches[1] as $path) {
                    $issues[] = "Invalid field: {$path}";
                }
            }

            preg_match_all("/'([^']+)' does not match input string ([^:\]]+)/", $message, $invalids);
            if (!empty($invalids[1])) {
                foreach ($invalids[1] as $index => $field) {
                    $value = trim($invalids[2][$index] ?? '');
                    $issues[] = "Field '{$field}' has invalid value: {$value}";
                }
            }

            if (empty($issues)) {
                $issues[] = str_contains($message, 'required')
                    ? 'Some required fields are missing or invalid.'
                    : 'Please verify the address and package details.';
            }

            $formattedMessage = implode(', ', array_unique($issues));
        }

        return [
            'status'  => false,
            'message' => trim($formattedMessage),
            'code'    => $code,
        ];
    }
}
