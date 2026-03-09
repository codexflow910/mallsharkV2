<?php

namespace Packages\Order\Contracts;

use App;
use Request;
use Session;
use Exception;
use App\BaseAccess;
use App\Constants;
use Packages\Auth\Models\User;
use Mailgun\Mailgun;
use Packages\Product\Models\{ProductVariation,Product,Attachments};
use Packages\Auth\Models\DeviceToken;
use Packages\Order\Models\{StoreOrder,OrderItems};
use App\Http\Controllers\StripeController;
use Illuminate\Support\Facades\Auth;
use Packages\Core\Contracts\CoreHelpers;
use Illuminate\Support\Facades\DB;
use Packages\Store\Models\Store;
use App\Services\FCMService;
use App\Services\SellerFcmService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\URL;
use App\Http\Controllers\MailgunController;
use Packages\Import\Http\Controllers\ShopifyController;

trait OrderHelpers
{
    use CoreHelpers;
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
        // $orderItems = $this->getOrderItemsByOrderId($order_id);
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

        $this->pushNotification($buyerId, $message, 'buyer', '/new-buyer-orders-detail/' . $orderId);

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
                $length = $this->convertToInches($product['productSize_length'] ?? 0, $product['productSize_unit'] ?? 'in');
                $width = $this->convertToInches($product['productSize_width'] ?? 0, $product['productSize_unit'] ?? 'in');
                $height = $this->convertToInches($product['productSize_height'] ?? 0, $product['productSize_unit'] ?? 'in');

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
    
}
