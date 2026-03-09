<?php

namespace Packages\Core\Http\Controllers\V2;

use Exception;
use Throwable;
use Packages\Order\Models\V2\StoreOrder;
use App\Http\Controllers\Controller;
use Packages\Core\Services\V2\CoreService;
use Packages\Core\Contracts\V2\BaseApiResponse;
use Illuminate\Support\ItemNotFoundException;
use Packages\Core\Http\Requests\V2\CheckoutRequest;
use Packages\Core\Http\Requests\V2\ShippingRateRequest;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Packages\Core\Contracts\V2\CoreHelpers;
use Packages\Shipping\Models\V2\{FreeShipping,ShippingProfile};
use Packages\Product\Models\V2\Product;
use Packages\Store\Models\V2\Store;
class CheckoutController extends Controller
{
    use BaseApiResponse;
    use CoreHelpers;
    protected $coreService;

    public function __construct(CoreService $coreService)
    {
        $this->coreService = $coreService;
    }

    public function getCheckout(CheckoutRequest $request)
    {
        try {
            $data = $request->validated();

            $response = $this->coreService->getCheckout($data);

            return $response;
        } catch (ItemNotFoundException $e) {
            return $this->notFoundResponse($e->getMessage());
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    public function paymentReceived(Request $request)
    {
        try {
            $response = $this->coreService->paymentReceived($request);

            return $response;
        } catch (ItemNotFoundException $e) {
            return $this->notFoundResponse($e->getMessage());
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }
    public function paymentReceivedV2(Request $request)
    {
        try {
            $response = $this->coreService->paymentReceivedV2($request);

            return $response;
        } catch (ItemNotFoundException $e) {
            return $this->notFoundResponse($e->getMessage());
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }
    public function buyNow(CheckoutRequest $request){
        try{
            $data = $request->all();
            $response = $this->coreService->makeCheckoutData($data);
            return $response;
        } catch (ItemNotFoundException $e) {
            return $this->notFoundResponse($e->getMessage());
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }
    public function OrderConfirmation()
    {
        $id = request()->query('id');

        if (empty($id)) {
            return response()->json([
                'status' => false,
                'success_url' => 'myapp://payment-cancel',
            ]);
        }

        if (is_array($id)) {
            $storeOrders = StoreOrder::whereIn('order_id', $id)->where('charge_status', 'unpaid')->get();
        } else {
            $storeOrders = StoreOrder::where('session_id', $id)->where('charge_status', 'unpaid')->get();
        }

        if ($storeOrders->isEmpty()) {
            return response()->json([
                'status' => true,
                'success_url' => 'myapp://payment-success',
            ]);
        } else {
            return response()->json([
                'status' => false,
                'success_url' => 'myapp://payment-cancel',
            ]);
        }
    }

    public function getRatesOnCheckout(ShippingRateRequest $request)
    {
        try {
            $data = $request->validated();
            $result = $this->coreService->getRatesOnCheckout($data);
            
            return $this->successResponse($result, 'Shipping rates fetched successfully');
        } catch (ItemNotFoundException $e) {
            return $this->notFoundResponse($e->getMessage());
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }
    public function sendLowStockEmail($orderId)
    {
        try {
            $response = $this->coreService->sendLowStockEmail($orderId);
            return $response;
        } catch (ItemNotFoundException $e) {
            return $this->notFoundResponse($e->getMessage());
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }
    public function applyCoupon(Request $request): Response
    {
        try {
            $couponData = $this->coreService->applyCoupon($request->all());
            return $this->successResponse($couponData, 'Coupon applied', Response::HTTP_CREATED);
        } catch (Throwable $e) {
            Log::error('Apply Coupon Error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return $this->errorResponse($e->getMessage());
        }
    }
    public function applyCouponV3(Request $request): Response
    {
        try {
            $couponData = $this->coreService->applyCouponV3($request->all());
            return $this->successResponse($couponData, 'Coupon applied', Response::HTTP_CREATED);
        } catch (Throwable $e) {
            Log::error('Apply Coupon Error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return $this->errorResponse($e->getMessage());
        }
    }
    public function newCheckout(Request $request)
    {
        try {
            $requestData = $request->all();
            $shippingData = $request->shipping_data ?? null;
            $userEmail = $shippingData['email'] ?? null;
            $requestPlatform = $request->platform ?? null;
            $checkoutUserId = $request->id ?? 'guest_' . uniqid();
            $customer = null;
            $logData = [
                'event_type' => 'stripe_request_data',
                'user_id' => $checkoutUserId,
                'data' => json_encode(['request_data' => $requestData]),
            ];
            $log = CoreHelpers::checkoutLog($logData);
            if ($shippingData) {
                $customer = $this->coreService->findOrCreateStripeCustomerV3($shippingData, $requestPlatform, $checkoutUserId);
            }
            $session = $this->coreService->makeCheckoutDataV3($requestData, $customer, $checkoutUserId);

            return $this->successResponse($session, 'Order Created', Response::HTTP_CREATED);
        } catch (\Exception $e) {
            // Log error per customer (fallback to general log if userEmail not found)
            $error = [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ];
            $logData = [
                'event_type' => 'checkout_error',
                'user_id' => $checkoutUserId,
                'data' => json_encode(['exception' => $error]),
            ];
            $log = CoreHelpers::checkoutLogV3($logData);
            return $this->errorResponse('Order could not be created. Please try again.');
        }
    }

    public function cryptoCheckout(Request $request)
    {
        try {
            $requestData = $request->all();
            $shippingData = $request->shipping_data ?? null;
            $userEmail = $shippingData['email'] ?? null;
            $requestPlatform = $request->platform ?? null;
            $checkoutUserId = $request->id ?? 'guest_' . uniqid();
            $customer = null;
            $logData = [
                'event_type' => 'stripe_request_data',
                'user_id' => $checkoutUserId,
                'data' => json_encode(['request_data' => $requestData]),
            ];
            $log = CoreHelpers::checkoutLog($logData);
            if($shippingData){
                $customer = $this->coreService->createCryptoUser($shippingData,$requestPlatform,$checkoutUserId);
            }
            $session = $this->coreService->cryptoCheckout($requestData, $customer,$checkoutUserId);

            return $this->successResponse($session,'Order Created',Response::HTTP_CREATED);

        } catch (\Exception $e) {
            // Log error per customer (fallback to general log if userEmail not found)
           $logData = [
                'event_type' => 'Checkout Error',
                'session_id' => $checkoutUserId,
                'data' => json_encode(['Exception' => $e->getMessage()]),
            ];
            $log = CoreHelpers::checkoutLog($logData);
            return $this->errorResponse('Order could not be created. Please try again.');
        }
    }
    private function getCustomerLogger($identifier)
    {
        $logFile = storage_path('logs/orders/customer_' . preg_replace('/[^a-zA-Z0-9]/', '_', $identifier) . '.log');
        $logger = new \Monolog\Logger('customer');
        $logger->pushHandler(new \Monolog\Handler\StreamHandler($logFile, \Monolog\Logger::INFO));
        return $logger;
    }

    public function getOrderSummary()
    {
        try {
            $id = request()->input('id');
            if (!$id) {
                return $this->validationErrorResponse('Session ID is required.');
            }

            $response = $this->coreService->getOrderSummary($id);

            if ($response->isEmpty()) {
                return $this->notFoundResponse('No orders found for this session.');
            }

            return $this->successResponse($response, 'Order summary retrieved successfully.');
        } catch (ItemNotFoundException $e) {
            return $this->notFoundResponse($e->getMessage());
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }
    public function profiles()
    {
        try 
        {
                $stores = Store::all();

                foreach ($stores as $store) {
                    $generalShipping = ShippingProfile::where('store_id', $store->store_id)
                    ->where('is_general', 1)
                    ->first();
                    if ($generalShipping) {
                        $products = Product::where('store_id', $store->store_id)->get();

                        foreach ($products as $product) {
                            $product->shippingProfiles()->sync($generalShipping->id);
                        }
                    }
                }
                return 'done';
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }
    public function cryptoWebhook(Request $request)
    {
        try {
            $response = $this->coreService->cryptoWebhook($request);
            return $response;
        } catch (ItemNotFoundException $e) {
            return $this->notFoundResponse($e->getMessage());
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }
    public function applyCollectiveCoupon(Request $request){
        try {
            $couponData = $this->coreService->applyCollectiveCoupon($request->all());
            return $this->successResponse($couponData, 'Collective Coupon applied', Response::HTTP_CREATED);
        } catch (Throwable $e) {
            Log::error('Apply Collective Coupon Error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return $this->errorResponse($e->getMessage());
        }
    }

    // ==================== PAYPAL CHECKOUT ENDPOINTS ====================

    /**
     * Get PayPal client ID for frontend SDK initialization
     */
    public function getPayPalClientId()
    {
        try {
            $clientId = $this->coreService->getPayPalClientId();
            return $this->successResponse(['client_id' => $clientId], 'PayPal client ID retrieved');
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    /**
     * Create a PayPal order for checkout
     */
    public function createPayPalOrder(Request $request)
    {
        try {
            $requestData = $request->all();
            $shippingData = $request->shipping_data ?? null;
            $requestPlatform = $request->platform ?? null;
            $checkoutUserId = $request->id ?? 'guest_' . uniqid();
            $user = null;

            $logData = [
                'event_type' => 'paypal_checkout_request',
                'user_id' => $checkoutUserId,
                'data' => json_encode(['request_data' => $requestData]),
            ];
            CoreHelpers::checkoutLogV3($logData);

            // Find or create user from shipping data
            if ($shippingData) {
                $user = $this->coreService->findOrCreateStripeCustomerV3($shippingData, $requestPlatform, $checkoutUserId);
            }

            $result = $this->coreService->createPayPalOrder($requestData, $user, $checkoutUserId);

            return $this->successResponse($result, 'PayPal order created', Response::HTTP_CREATED);

        } catch (Exception $e) {
            $logData = [
                'event_type' => 'paypal_checkout_error',
                'user_id' => $checkoutUserId ?? 'unknown',
                'data' => json_encode([
                    'error' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                ]),
            ];
            CoreHelpers::checkoutLogV3($logData);

            Log::error('PayPal Create Order Error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return $this->errorResponse('PayPal order could not be created. Please try again.');
        }
    }

    /**
     * Capture an approved PayPal order after user approval
     */
    public function capturePayPalOrder(Request $request)
    {
        try {
            $paypalOrderId = $request->input('paypal_order_id');
            $checkoutUserId = $request->input('id') ?? 'guest_' . uniqid();

            if (!$paypalOrderId) {
                return $this->validationErrorResponse('PayPal order ID is required');
            }

            $result = $this->coreService->capturePayPalOrder($paypalOrderId, $checkoutUserId);

            if ($result['success']) {
                return $this->successResponse($result, 'Payment captured successfully');
            }

            return $this->errorResponse($result['message'] ?? 'Payment capture failed');

        } catch (Exception $e) {
            Log::error('PayPal Capture Error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return $this->errorResponse('Payment capture failed. Please try again.');
        }
    }

    /**
     * Handle PayPal webhook events
     */
    public function paypalWebhook(Request $request)
    {
        try {
            $result = $this->coreService->handlePayPalWebhook($request);

            return response()->json($result, Response::HTTP_OK);

        } catch (Exception $e) {
            Log::error('PayPal Webhook Error', [
                'error' => $e->getMessage(),
                'payload' => $request->all(),
            ]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Get PayPal order details
     */
    public function getPayPalOrderDetails(Request $request)
    {
        try {
            $paypalOrderId = $request->input('paypal_order_id');

            if (!$paypalOrderId) {
                return $this->validationErrorResponse('PayPal order ID is required');
            }

            $result = $this->coreService->getPayPalOrderDetails($paypalOrderId);

            return $this->successResponse($result, 'PayPal order details retrieved');

        } catch (Exception $e) {
            Log::error('PayPal Get Order Details Error', [
                'error' => $e->getMessage(),
            ]);

            return $this->errorResponse('Could not retrieve order details.');
        }
    }

    /**
     * Confirm PayPal order status (similar to Stripe OrderConfirmation)
     */
    public function paypalOrderConfirmation(Request $request)
    {
        $paypalOrderId = $request->query('id');

        if (empty($paypalOrderId)) {
            return response()->json([
                'status' => false,
                'success_url' => 'myapp://payment-cancel',
            ]);
        }

        $storeOrders = StoreOrder::where('session_id', $paypalOrderId)
            ->where('payment_method', 'paypal')
            ->where('charge_status', 'unpaid')
            ->get();

        if ($storeOrders->isEmpty()) {
            return response()->json([
                'status' => true,
                'success_url' => 'myapp://payment-success',
            ]);
        } else {
            return response()->json([
                'status' => false,
                'success_url' => 'myapp://payment-cancel',
            ]);
        }
    }

}
