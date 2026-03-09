<?php

namespace Packages\Order\Repositories\Eloquent\V2;
use Zendesk;
use Exception;
use App\Constants;
use Carbon\Carbon;
use Stripe\Refund;
use Stripe\Stripe;
use Packages\Store\Models\V2\{Store,StoreEscrow};
use Packages\Auth\Models\V2\{DeviceToken,User};
use Packages\Home\Models\V2\{ShippingAddress};
use Packages\Product\Models\V2\{Product,DigitalFiles, ProductVariation};
use App\Services\FCMService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Auth;
use Packages\Order\Models\V2\{OrderItems, OrderTracking, StoreOrder,ProductRefund};
use Packages\Order\Contracts\V2\OrderHelpers;
use App\Http\Controllers\MailgunController;
use Illuminate\Support\Arr;
use Illuminate\Support\ItemNotFoundException;
use Illuminate\Validation\UnauthorizedException;
use Packages\Order\Contracts\V2\BaseApiResponse;
use App\Http\Controllers\API\StripeController;
use Packages\Import\Http\Controllers\V2\ShopifyController;
use Packages\Order\Config\OrderConstants;
use Packages\Order\Contracts\V2\OrderRepositoryInterface;
use Illuminate\Support\Str;
class OrderRepository implements OrderRepositoryInterface
{
    use BaseApiResponse;
    use OrderHelpers;
    private $stripeObj;
    private $mailgun;
    private $model;
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
            $query = StoreOrder::with('orderItemsList.review', 'orderItemsList.trackingUrl', 'user', 'shippingAddress')
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
            $query = StoreOrder::with('orderItemsList.trackingUrl', 'user', 'shippingAddress')
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

            if ($storeOrdersArray && $order_id) {
                $prevOrder = StoreOrder::where('store_id', $store_id)
                    ->where('order_id', '<', $order_id)
                    ->where('charge_status', 'paid')
                    ->orderByDesc('order_id')
                    ->first();

                $nextOrder = StoreOrder::where('store_id', $store_id)
                    ->where('order_id', '>', $order_id)
                    ->where('charge_status', 'paid')
                    ->orderBy('order_id')
                    ->first();

                // Attach previous and next order IDs
                $storeOrdersArray['previous_order_id'] = $prevOrder?->order_id;
                $storeOrdersArray['next_order_id'] = $nextOrder?->order_id;
            }

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

    public function rejectRefund(array $data) 
    {
        try {
            $orderId = $data['order_id'];
            $reason = $data['reject_order_reason'] ?? null;
            $isReject = $data['is_reject'] ?? true;
            $isRefund = $data['is_refund'] ?? false;

            $processItems = collect($data['process_items'] ?? []);
            if ($processItems->isEmpty()) {
                throw new ItemNotFoundException('You have no item in this order to refund.');
            }

            DB::beginTransaction();

            foreach ($processItems as $item) {
                $itemId = $item['refund_item_id'] ?? null;
                $variationId = $item['variation_id'] ?? null;

                if (!$itemId) {
                    continue;
                }

                $orderItemQuery = OrderItems::where('order_id', $orderId)
                    ->where('order_item_id', $itemId);

                if ($variationId) {
                    $orderItemQuery->where('variation_id', $variationId);
                }

                $orderItem = $orderItemQuery->first();
                
                if (!$orderItem) {
                    throw new ItemNotFoundException("Order item not found for Item ID {$itemId}" . ($variationId ? " and Variation ID {$variationId}" : ""));
                }

                $productRefundQuery = ProductRefund::where('product_id', $orderItem->item_id)
                    ->where('order_id', $orderId);

                if ($variationId) {
                    $productRefundQuery->where('variation_id', $variationId);
                }

                $productRefund = $productRefundQuery->first();
                if (!$productRefund) {
                    throw new ItemNotFoundException("Refund entry not found for Product ID {$orderItem->product_id}" . ($variationId ? " and Variation ID {$variationId}" : ""));
                }

                $productRefund->is_reject = $isReject;
                $productRefund->is_refund = $isRefund;
                $productRefund->reject_order_reason = $reason;
                $productRefund->save();

                $orderItem->status = OrderConstants::ORDER_DISPUTED;
                $orderItem->save();
            }

            $itemStatuses = OrderItems::where('order_id', $orderId)->pluck('status')->toArray();
            $finalStatus = OrderHelpers::resolveOrderStatus($itemStatuses);

            $storeOrder = StoreOrder::findOrFail($orderId);
            $storeOrder->order_status = $finalStatus;
            $storeOrder->reject_date = now();
            $storeOrder->is_reject = $isReject;
            $storeOrder->is_refund = $isRefund;
            $storeOrder->save();

            DB::commit();
            return true;
        } catch (Exception $e) {
            DB::rollBack();
            throw new Exception($e->getMessage(), $e->getCode(), $e);
        }
    }

    public function getBrandOrders($order_status, $order_id, $postedData)
    {
        try {
            $userId = Auth::user()->user_id;
            if (!$userId) {
                throw new UnauthorizedException('No User found');
            }
            $store = OrderHelpers::getAccessibleStore($userId);
            if (!$store) {
                throw new ItemNotFoundException('User does not belong or have access to store.');
            }
            $storeId = $store->store_id;

            $currentDate = Carbon::today();
            $getTodayNewStoreOrders = StoreOrder::where('store_id', $storeId)->whereBetween('created_at', [now()->subDays(6)->startOfDay(), now()->endOfDay()])->count();
            $getTodayPendingStoreOrders = StoreOrder::where('store_id', $storeId)->where('charge_status', 'paid')->where('order_status', 'pending')->whereBetween('created_at', [now()->subDays(6)->startOfDay(), now()->endOfDay()])->count();
            $getTodayRefundReqStoreOrders = StoreOrder::where('store_id', $storeId)->where('charge_status', 'paid')->where('order_status', 'refunded')->whereBetween('created_at', [now()->subDays(6)->startOfDay(), now()->endOfDay()])->count();
            $getTodayDisputeStoreOrders = StoreOrder::where('store_id', $storeId)->where('charge_status', 'paid')->where('order_status', 'dispute')->whereBetween('created_at', [now()->subDays(6)->startOfDay(), now()->endOfDay()])->count();
            
            $orders_all = $this->getOrdersWithItemsAllNewApiNoPagination($storeId, $order_id, $order_status);

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

        $storeOrder->tracking_id = $data['tracking_id'] ?? null;
        $storeOrder->ship_label_url = $data['ship_label_url'] ?? null;
        $storeOrder->fedex_ship_amount = $data['fedex_ship_amount'] ?? null;
        $storeOrder->ship_service = $data['ship_service'] ?? null;
        $storeOrder->delivery_date = $data['delivery_date'] ?? null;
        $storeOrder->order_status = $data['status'] ?? null;
        $storeOrder->shipped_date = now();

        $trackingNumber = $data['tracking_id'] ?? null;
        $trackingUrl = "https://www.fedex.com/fedextrack/?trknbr={$trackingNumber}";
        
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

            $updateData = Arr::only($data, ['apartment_no','street', 'city', 'state', 'post_code']);
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
            $refund->variation_id = $data['variation_id'] ?? null;
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
    public function restoreStock($item)
    {
        if (!$item) return;

        $quantity = $item['quantity'];
        $productId = $item['item_id'];
        $variationId = $item['variation_id'];

        if (empty($variationId)) {
            $product = Product::find($productId);
            if ($product) {
                $product->increment('current_stock', $quantity);

                if ($product->is_shopify_import) {
                    (new ShopifyController())->restoreInventoryToShopifyOnOrderCancel($productId, $quantity);
                }
            }
        } else {
            $variation = ProductVariation::find($variationId);
            if ($variation) {
                $variation->increment('current_stock', $quantity);

                if ($variation->is_shopify_import) {
                    (new ShopifyController())->restoreInventoryToShopifyOnOrderCancel($productId, $quantity, $variationId);
                }
            }
        }
    }

    public function processRefund($orderId, $isRefund)
    {
        $stripe = $this->stripeObj;
        if ($stripe->refundBack($orderId)) {
            DB::table('store_orders')->where('order_id', $orderId)->update([
                'order_status' => OrderConstants::ORDER_REFUNDED,
                'is_refund' => $isRefund
            ]);
            return true;
        }
        return false;
    }

    public function processCancelOrder($orderId, $isRefund, $processedItems, $paymentMethod,$userId)
    {
        try {
            DB::beginTransaction();

            $totalRefundAmount = 0;
            $isCrypto = strtolower($paymentMethod ?? '') === OrderConstants::PAYMENT_METHOD_CRYPTO;

            // Step 1: Update order_items with refund amounts and status
            foreach ($processedItems as $item) {
                $refundAmount = $item['item_refund_amount'] ?? 0;
                if ($refundAmount <= 0) {
                    continue;
                }

                $totalRefundAmount += $refundAmount;

                OrderItems::where('order_item_id', $item['order_item_id'])
                    ->update([
                        'refund_amount' => DB::raw("COALESCE(refund_amount, 0) + {$refundAmount}"),
                        'status' => OrderConstants::ORDER_REFUNDED,
                    ]);
                $productRefund = ProductRefund::where('product_id', $item['order_item_id'])->first();

                if (!$productRefund) {
                    // Create a new refund record using data from order item
                    $orderItem = OrderItems::where('order_item_id', $item['order_item_id'])->first();
                    
                    if ($orderItem) {
                        ProductRefund::create([
                            'product_id'       => $orderItem->order_item_id,
                            'variation_id'     => $orderItem->variation_id ?? null,
                            'order_id'         => $orderItem->order_id,
                            'buyer_id'         => $userId ?? null,
                            'store_id'         => $orderItem->store_id,
                            'quantity'         => $orderItem->quantity,
                            'price'            => min($refundAmount, $orderItem->price),
                            'shipping_amount'  => max($refundAmount - $orderItem->price, 0),
                            'reason_type'      => null,
                            'reason'           => null,
                            'reject_order_reason' => null,
                            'is_complete'      => $isCrypto ? 0 : 1,
                            'is_reject'        => 0,
                            'is_refund'        => $isCrypto ? 0 : 1,
                            'refund_status'    => $isCrypto ? 0 : 1,
                            'refund_date'      => now(),
                        ]);
                    }
                } else {
                    // Refund record exists → update values
                    $price = $productRefund->price ?? 0;
                    $shippingAmount = 0;

                    if ($refundAmount >= $price) {
                        $shippingAmount = $refundAmount - $price;
                    } else {
                        $price = $refundAmount;
                    }

                    $productRefund->update([
                        'is_reject'       => 1,
                        'refund_status'   => 1,
                        'price'           => $price,
                        'shipping_amount' => $shippingAmount,
                    ]);
                }
            }

            // Step 2: Refund via Stripe
            if ($totalRefundAmount > 0 && $paymentMethod === OrderConstants::PAYMENT_METHOD_STRIPE) {
                $refunded = $this->refundBack($orderId, $totalRefundAmount);
                if (!$refunded) {
                    DB::rollBack();
                    throw new Exception("Stripe refund failed.");
                }

                // Step 3: Update store_orders with refund flag and total refund amount
                StoreOrder::where('order_id', $orderId)->update([
                    'is_refund' => $isRefund,
                    'refund_amount' => DB::raw("COALESCE(refund_amount, 0) + {$totalRefundAmount}")
                ]);
            } elseif ($totalRefundAmount > 0 && $paymentMethod === OrderConstants::PAYMENT_METHOD_CRYPTO) {
                // For crypto payment methods, just update the refund amount
                StoreOrder::where('order_id', $orderId)->update([
                    'is_refund' => $isRefund,
                    'refund_amount' => DB::raw("COALESCE(refund_amount, 0) + {$totalRefundAmount}")
                ]);
            } else {
                throw new Exception("Payment method not supported for refunds or refund amount is less than equal to zero.");
            }

            // Step 4: Recalculate order status
            $itemStatuses = OrderItems::where('order_id', $orderId)
                ->pluck('status')
                ->toArray();

            $finalStatus = OrderHelpers::resolveOrderStatus($itemStatuses);

            StoreOrder::where('order_id', $orderId)->update([
                'order_status' => $finalStatus
            ]);

            DB::commit();
            return true;

        } catch (Exception $e) {
            DB::rollBack();
            throw new Exception($e->getMessage());
        }
    }

    public function refundBack($orderId, $amount = null)
    {
        try {
            $order = StoreOrder::select('transaction_id', 'session_id', 'total_amount', 'payment_method','buyer_id')
                ->where('order_id', $orderId)
                ->first();

            if (!$order || !$order->session_id) {
                throw new Exception("Transaction/session ID not found for order ID: " . $orderId);
            }

            
            Stripe::setApiKey(env('STRIPE_API_KEY'));
            $paymentIntentId = $order->session_id;
            if (Str::startsWith($paymentIntentId, 'cs_')) {
                $checkoutSession =  \Stripe\Checkout\Session::retrieve($paymentIntentId);
                $paymentIntentId = $checkoutSession->payment_intent;
            }
            $refundParams = ['payment_intent' => $paymentIntentId];
                if ($amount !== null) {
                    // Ensure the amount is within the order's total range
                    $refundAmountInCents = intval(round($amount * 100));
                    if ($refundAmountInCents <= 0) {
                        throw new Exception("Refund amount must be greater than 0.");
                    }
                    $refundParams['amount'] = $refundAmountInCents;
                }

                $refund = Refund::create($refundParams);
            return $refund->status === 'succeeded';

        } catch (\Throwable $th) {
            throw new Exception($th->getMessage());
        }
    }

    public function updateOrderAndItems($orderId, $itemStatus, $isAll, $processItems)
    {
        $orderStatus = $isAll ? OrderConstants::ORDER_SHIPPED : OrderConstants::ORDER_PARTIAL_SHIPPED;

        StoreOrder::where('order_id', $orderId)->update([
            'order_status' => $orderStatus,
            'shipped_date' => Carbon::now(),
        ]);

        foreach ($processItems as $item) {
            $carrier = $item['carrier'] ?? null;
            $transaction = $item['transaction'] ?? null;
            $customUrl = $item['custom_url'] ?? null;

            $trackingUrl = $this->generateTrackingLink($carrier, $transaction, $customUrl);

            $tracking = OrderTracking::create([
                'url' => $trackingUrl,
                'shipped_date' => now(),
                'ship_service' => $carrier
            ]);

            OrderItems::where('order_id', $orderId)
                ->where('order_item_id', $item['order_item_id'])
                ->update([
                    'status' => $itemStatus,
                    'tracking_id' => $tracking->id,
                ]);
        }
    }
    public function editOrderNote($orderId, $orderNote) 
    {
        try {
            return StoreOrder::where('order_id', $orderId)
                ->update(['order_note' => $orderNote]);

        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public function editItemTracking($trackingId, $trackingUrl, $carrier)
    {
        try {
            $updated = OrderTracking::where('id', $trackingId)
                ->update([
                    'url' => $trackingUrl,
                    'ship_service' => $carrier,
                ]);

            if (!$updated) {
                throw new \Exception('Tracking record not found or not updated.');
            }

            return $updated;
        } catch (\Throwable $e) {
            throw new \Exception('Failed to update tracking: ' . $e->getMessage());
        }
    }

    public function updateOrderItemsShipped(
        $orderId,
        $trackingUrl,
        $labelUrl,
        $shipAmount,
        $shipService,
        $deliveryDate,
        $shippedManually,
        $processItemIds
    ) {
        DB::beginTransaction();

        try {
            $status = OrderConstants::ORDER_SHIPPED;

            // Create order tracking record
            $tracking = OrderTracking::create([
                'url' => $trackingUrl,
                'ship_label_url' => $labelUrl,
                'ship_amount' => $shipAmount,
                'ship_service' => $shipService,
                'delivery_date' => $deliveryDate,
                'shipped_manually' => $shippedManually,
                'shipped_date' => now(),
            ]);

            // Update specific order items as shipped
            OrderItems::where('order_id', $orderId)
                ->whereIn('order_item_id', $processItemIds)
                ->update([
                    'status' => $status,
                    'tracking_id' => $tracking->id,
                ]);

            // Recalculate order status based on item statuses
            $itemStatuses = OrderItems::where('order_id', $orderId)->pluck('status')->toArray();
            $finalStatus = OrderHelpers::resolveOrderStatus($itemStatuses);

            // Update store order
            StoreOrder::where('order_id', $orderId)->update([
                'order_status' => $finalStatus,
            ]);

            DB::commit();
            return true;
        } catch (Exception $e) {
            DB::rollBack();
            throw new Exception('Failed to update shipped items: ' . $e->getMessage());
        }
    }

    public function markItemsAsRefunded($orderId, $refundItems)
    {
        try {
            DB::beginTransaction();

            $updatedRefunds = ProductRefund::where('order_id', $orderId)
                ->whereIn('id', $refundItems)
                ->update([
                    'is_complete'   => 1,
                    'refund_status' => 1,
                    'is_refund'     => 1,
                    'refund_date'   => now(),
                ]);

            $refundProducts = ProductRefund::where('order_id', $orderId)
                ->whereIn('id', $refundItems)
                ->get();

            DB::commit();
            return $refundProducts;
        } catch (Exception $e) {
            DB::rollBack();
            throw new Exception($e->getMessage(), $e->getCode(), $e);
        }
    }
}
