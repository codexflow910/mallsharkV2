<?php

namespace Packages\Order\Http\Controllers\V4;

use PDF;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use Dotenv\Exception\ValidationException;
use Illuminate\Support\Facades\Validator;
use Packages\Order\Services\V4\OrderService;
use Packages\Order\Contracts\V4\OrderHelpers;
use Illuminate\Support\ItemNotFoundException;
use Packages\Order\Contracts\V4\BaseApiResponse;
use Packages\Order\Http\Requests\V4\GetOrdersRequest;
use Packages\Order\Http\Requests\V4\UpdateOrderRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Auth\Access\AuthorizationException;
use GuzzleHttp\Client;
use Packages\Order\Http\Requests\V4\OrderCancellationRequest;
use Carbon\Carbon;
use Illuminate\Contracts\Validation\Rule;
use Packages\Order\Models\V4\StoreOrder;
use Packages\Order\Http\Requests\V4\RejectRefundRequest;

class OrderController extends Controller
{
    use BaseApiResponse;
    use OrderHelpers;

    protected $orderService;
    protected $client;
    protected $baseUrl;

    public function __construct(OrderService $orderService, Client $client)
    {
        $this->orderService = $orderService;
        $this->client = $client;
    }

    public function buyerOrders(GetOrdersRequest $request)
    {
        try {
            $ordersAll = $this->orderService->getBuyerOrders($request);

            if (empty($ordersAll)) {
                throw new ItemNotFoundException("No orders found for the provided filters.");
            }

            // $selectedOrders = $this->orderService->addOrdersItems($ordersAll);

            if (empty($ordersAll)) {
                throw new ItemNotFoundException("No orders found for the provided filters.");
            }

            return $this->successResponse($ordersAll, "You get all orders");
        } catch (ItemNotFoundException $e) {
            return $this->notFoundResponse($e->getMessage());
        } catch (ValidationException $e) {
            return $this->validationErrorResponse($e->getMessage());
        } catch (Exception $e) {
            return $this->errorResponse('An unexpected error occurred: ' . $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function getBrandOrdersPaginate(GetOrdersRequest $request)
    {
        try {
            $orders = $this->orderService->getBrandOrdersPaginate($request);

            if (empty($orders)) {
                throw new ItemNotFoundException("No orders found.");
            }
            return $this->successResponse($orders, "You get all orders");
        } catch (ItemNotFoundException $e) {
            return $this->notFoundResponse($e->getMessage());
        } catch (ValidationException $e) {
            return $this->validationErrorResponse($e->getMessage());
        } catch (Exception $e) {
            return $this->notFoundResponse($e->getMessage());
            // return $this->errorResponse('An unexpected error occurred: ' . $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    public function brandOrderdetails($order_id)
    {
        try {
            $orders = $this->orderService->brandOrderdetails($order_id);
            if (empty($orders)) {
                throw new ItemNotFoundException("No orders found.");
            }
            return $this->successResponse($orders, "You get all orders");
        } catch (ItemNotFoundException $e) {
            return $this->notFoundResponse($e->getMessage());
        } catch (ValidationException $e) {
            return $this->validationErrorResponse($e->getMessage());
        } catch (Exception $e) {
            return $this->errorResponse('An unexpected error occurred: ' . $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function orderPdf($orderId)
    {
        try {
            set_time_limit(0);
            $data = $this->orderService->getOrderData($orderId);

            if (empty($data)) {
                throw new ItemNotFoundException('Order data not found');
            }

            // Generate and download PDF
            $pdf = PDF::loadView('orders.invoice', ['ordersdata' => $data]);
            return $pdf->download('invoice-' . $orderId . '.pdf');
        } catch (ItemNotFoundException $e) {
            return $this->notFoundResponse($e->getMessage());
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    public function getOrderPdf(GetOrdersRequest $request)
    {
        try {
            $data = $request->validated();
            $order_id = $data['order_id'];
            $order = $this->orderService->getOrderPdf($order_id);
            if (empty($order)) {
                throw new ItemNotFoundException("No orders found.");
            }
            return $this->successResponse($order, "You get order");
        } catch (ItemNotFoundException $e) {
            return $this->notFoundResponse($e->getMessage());
        } catch (ValidationException $e) {
            return $this->validationErrorResponse($e->getMessage());
        } catch (Exception $e) {
            return $this->errorResponse('An unexpected error occurred: ' . $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    public function getStoreOrders()
    {
        try {
            $result = $this->orderService->getStoreOrders();
            if (empty($result)) {
                throw new ItemNotFoundException("No Data found");
            }
            return $this->successResponse($result, "Success");
        } catch (ItemNotFoundException $e) {
            return $this->notFoundResponse($e->getMessage());
        } catch (ValidationException $e) {
            return $this->validationErrorResponse($e->getMessage());
        } catch (Exception $e) {
            return $this->errorResponse('An unexpected error occurred: ' . $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    public function updateOrderStatus(UpdateOrderRequest $request)
    {
        try {
            $input = $request->all();

            $result = $this->orderService->updateOrderStatus($input);
            return $this->successResponse($result, "Status updated successfully: " . strtoupper($input['status']));
        } catch (AuthorizationException $e) {
            return $this->authorizationResponse($e->getMessage());
        } catch (ValidationException $e) {
            return $this->validationErrorResponse($e->getMessage());
        } catch (Exception $e) {
            return $this->errorResponse('Failed: ' . $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function refundOrder(Request $request)
    {
        try {
            $validation = Validator::make(
                $request->all(),
                [
                    'order_id' => 'required',
                ]
            );

            if ($validation->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'validation error',
                    'errors' => $validation->errors()
                ], 422);
            }
    
            $response = $this->orderService->processRefund($request['order_id']);
    
            return $this->successResponse($response, 'Refund processed successfully.');
        } catch (ValidationException $e) {
            return $this->validationErrorResponse($e->getMessage());
        } catch (ItemNotFoundException $e) {
            return $this->notFoundResponse($e->getMessage());
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    public function rejectRefund(RejectRefundRequest $request)
    {
        try {
            $input = $request->all();

            $result = $this->orderService->rejectRefund($input);
            
            return $this->successResponse($result, "Order rejected successfully.");
        } catch (ItemNotFoundException $e) {
            return $this->notFoundResponse($e->getMessage());
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

     public function getBrandOrders(Request $request)
    {
      
        try {
            $validateUser = Validator::make(
			$request->all(),
			[
				'status' => 'in:pending,completed,shipped,refunded,dispute,cancelled',
                'order_id' => 'exists:store_orders,order_id',
			]
		);

		if ($validateUser->fails()) {
		   throw new ValidationException("Validation errors",$validateUser->errors());
		}
          $order_status = $request->input('status');
          $order_id =  $request->input('order_id');
          $postedData = $request->all();
            $order = $this->orderService->getBrandOrders($order_status,$order_id,$postedData );
            return $this->successResponse($order, 'Store retrieved successfully');
        } catch (Exception $e) {
            if ($e instanceof ItemNotFoundException) {
                return $this->notFoundResponse($e->getMessage());
            }
             if ($e instanceof ValidationException) {
                return $this->validationErrorResponse($e->errors());
            }
            if ($e instanceof UnauthorizedException) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }
            if ($e instanceof AuthorizationException) {
                return response()->json(['error' => 'Unauthorized access'], 403);
            }
            return $this->errorResponse($e->getMessage());
        }
    }
    public function buyerOrdersPaginate(GetOrdersRequest $request)
    {
        try {
            $ordersAll = $this->orderService->getOrdersPaginate($request);

            if (empty($ordersAll)) {
                throw new ItemNotFoundException("No orders found for the provided filters.");
            }
            // dd($ordersAll);
            return $this->successResponse($ordersAll,"You get all orders");         
        } catch (ItemNotFoundException $e) {
            return $this->notFoundResponse($e->getMessage());
        } catch (ValidationException $e) {
            return $this->validationErrorResponse($e->getMessage());
        } catch (Exception $e) {
            return $this->errorResponse('An unexpected error occurred: ' . $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    public function authenticate(Request $request){
        $response = Http::asForm()->post('https://apis-sandbox.fedex.com/oauth/token', [
            'grant_type'    => $request->grant_type,
            'client_id'     => $request->client_id,
            'client_secret' => $request->client_secret,
        ]);
        return response()->json($response->json());
    }

    public function exportOrders($store_id)
    {
        try {
            $orders = $this->orderService->exportOrders($store_id);
            return $orders; 
        } catch (Exception $e) {
            if ($e instanceof ValidationException) {
                return $this->validationErrorResponse($e->getMessage());
            }
            return $this->errorResponse($e->getMessage());
        }
    }

    public function cancelBuyerOrder(OrderCancellationRequest $request)
    {
        try {

            $result = $this->orderService->cancelBuyerOrder($request->validated());
            return $this->successResponse($result, "You order has been cancelled successfully.");
            
        } catch (Exception $e) {
            if ($e instanceof ItemNotFoundException) {
                return $this->notFoundResponse($e->getMessage());
            }
            return $this->errorResponse($e->getMessage());
        }
    }

    public function fetchFedExOrderRate(Request $request)
    {
        try {
            $validation = Validator::make(
                $request->all(), [
                'order_id' => 'required|integer|exists:store_orders,order_id',
            ]);

            if ($validation->fails()) {
                return $this->validationErrorResponse('Validation error', $validation->errors());
            }

            $orderId = $request->input('order_id');

            return $this->orderService->fetchFedExOrderRate($orderId);
        } catch (ItemNotFoundException $e) {
            return $this->notFoundResponse($e->getMessage());
        } catch (ValidationException $e) {
            return $this->validationErrorResponse($e->getMessage());
        } catch (Exception $e) {
            return $this->errorResponse('An unexpected error occurred: ' . $e->getMessage());
        }
    }
    public function shipFedExOrder(Request $request)
    {
        try {
            $validation = Validator::make(
                $request->all(), [
                'order_id' => 'required|integer|exists:store_orders,order_id',
                'service_type' => 'required|string',
            ]);

            if ($validation->fails()) {
                return $this->validationErrorResponse('Validation error', $validation->errors());
            }

            $orderId = $request->input('order_id');
            $serviceType = $request->input('service_type');

            return $this->orderService->shipFedExOrder($orderId, $serviceType);

        } catch (ItemNotFoundException $e) {
            return $this->notFoundResponse($e->getMessage());
        } catch (AuthorizationException $e) {
            return $this->authorizationResponse($e->getMessage());
        } catch (ValidationException $e) {
            return $this->validationErrorResponse($e->getMessage());
        } catch (Exception $e) {
            return $this->errorResponse('An unexpected error occurred: ' . $e->getMessage());
        }
    }

    public function printLabel(Request $request)
    {
        try {
            $validation = Validator::make($request->all(), [
                'label_url' => 'required|url'
            ]);
    
            if ($validation->fails()) {
                return $this->validationErrorResponse('Validation error', $validation->errors());
            }
    
            $labelUrl = $request->input('label_url');
    
            // This will return an image response from service directly
            return $this->orderService->printLabel($labelUrl);
    
        } catch (ItemNotFoundException $e) {
            return $this->notFoundResponse($e->getMessage());
        } catch (ValidationException $e) {
            return $this->validationErrorResponse($e->getMessage());
        } catch (Exception $e) {
            return $this->errorResponse('An unexpected error occurred: ' . $e->getMessage());
        }
    }
    
    public function trackFedExOrder(Request $request)
    {
        try {
            $validation = Validator::make($request->all(), [
                'tracking_id' => 'required'
            ]);
    
            if ($validation->fails()) {
                return $this->validationErrorResponse('Validation error', $validation->errors());
            }
    
            $trackingId = $request->input('tracking_id');
    
            // This will return an image response from service directly
            return $this->orderService->trackFedExOrder($trackingId);
            
        } catch (ItemNotFoundException $e) {
            return $this->notFoundResponse($e->getMessage());
        } catch (ValidationException $e) {
            return $this->validationErrorResponse($e->getMessage());
        } catch (Exception $e) {
            return $this->errorResponse('An unexpected error occurred: ' . $e->getMessage());
        }
    }

    public function editOrderShippingAddress(Request $request)
    {
        try {
            $validation = Validator::make(
                $request->all(), [
                'order_id' => 'required|integer|exists:store_orders,order_id',
                'street' => 'nullable|string',
                'city' => 'nullable|string',
                'state' => 'nullable|integer',
                'post_code' => 'nullable',
            ]);

            if ($validation->fails()) {
                return $this->validationErrorResponse('Validation error', $validation->errors());
            }

            $data = $request->all();
            $response = $this->orderService->editOrderShippingAddress($data);
            return $this->successResponse($response, "Shipping address updated successfully.");
        } catch (ItemNotFoundException $e) {
            return $this->notFoundResponse($e->getMessage());
        } catch (ValidationException $e) {
            return $this->validationErrorResponse($e->getMessage());
        } catch (Exception $e) {
            return $this->errorResponse('An unexpected error occurred: ' . $e->getMessage());
        }
    }

    public function createZendeskTicketForAddress(Request $request)
    {
        try {
            $validation = Validator::make(
                $request->all(), [
                'order_id' => 'required|integer|exists:store_orders,order_id',
            ]);

            if ($validation->fails()) {
                return $this->validationErrorResponse('Validation error', $validation->errors());
            }

            $data = $request->all();
            $response = $this->orderService->createZendeskTicketForAddress($data);
            return $this->successResponse($response, "Zendesk ticket created successfully.");
        } catch (ItemNotFoundException $e) {
            return $this->notFoundResponse($e->getMessage());
        } catch (ValidationException $e) {
            return $this->validationErrorResponse($e->getMessage());
        } catch (Exception $e) {
            return $this->errorResponse('An unexpected error occurred: ' . $e->getMessage());
        }
    }
    
    public function updateOrdersStatus(Request $request){
        $From_Date = \Carbon\Carbon::createFromFormat('m/d/Y', '10/28/2024')->format('Y-m-d');
        $To_Date = \Carbon\Carbon::createFromFormat('m/d/Y', '07/06/2025')->format('Y-m-d');
        StoreOrder::where('store_id', '228')->where('order_status', 'shipped')->where('charge_status','paid')
        ->whereBetween('order_id', ['14011', '14344'])
        ->update(['order_status' => 'completed']);
    }

    public function editOrderNote(Request $request)
    {
        try {
            $validation = Validator::make(
                $request->all(), [
                'order_id' => 'required|integer|exists:store_orders,order_id',
                'order_note' => 'required|string|max:255',
            ]);

            if ($validation->fails()) {
                return $this->validationErrorResponse('Validation error', $validation->errors());
            }

            $orderNote = $request->input('order_note');
            $orderId = $request->input('order_id');

            $response = $this->orderService->editOrderNote($orderId, $orderNote);
            return $this->successResponse($response, "Order Note update successfully.");
        } catch (Exception $e) {
            return $this->errorResponse("Order Note can't udated: " . $e->getMessage());
        }
    }


    public function editItemTracking(Request $request)
    {
        try {
            $validation = Validator::make(
                $request->all(), [
                    'order_id'     => 'required|integer|exists:store_orders,order_id',
                    'transaction'  => 'nullable|string|alpha_num|min:1',
                    'tracking_id'     => 'required|integer|exists:order_tracking,id',
                    'carrier'      => 'required|string|in:ups,usps,fedx,dhl,others,amazon',
                    'custom_url'   => 'nullable|url|required_if:carrier,others',
                ]
            );

            if ($validation->fails()) {
                return $this->validationErrorResponse('Validation error', $validation->errors());
            }

            $orderId = $request->input('order_id');
            $carrier = $request->input('carrier');
            $trackingId = $request->input('tracking_id');
            $transaction = $request->input('transaction');
            $customUrl = $request->input('custom_url');
            $response = $this->orderService->editItemTracking($orderId, $carrier, $transaction, $customUrl, $trackingId);

            return $this->successResponse($response, "Order tracking updated successfully.");
        } catch (\Exception $e) {
            return $this->errorResponse("Order tracking can't be updated: " . $e->getMessage());
        }
    }

    public function shipUSPSOrder(Request $request)
    {
        try {
            $validation = Validator::make(
                $request->all(),
                [
                    'sku' => 'required|string',
                    'mail_class' => 'required|string',
                    'process_items' => 'required|array',
                    'rate_indicator' => 'required|string',
                    'processing_category' => 'required|string',
                    'destination_entry_facility_type' => 'required|string',
                    'order_id' => 'required|integer|exists:store_orders,order_id',
                ]
            );

            if ($validation->fails()) {
                return $this->validationErrorResponse('Validation error', $validation->errors());
            }

            $sku = $request->input('sku');
            $orderId = $request->input('order_id');
            $mailClass = $request->input('mail_class');
            $processItems = $request->input('process_items');
            $rateIndicator = $request->input('rate_indicator');
            $processingCategory = $request->input('processing_category');
            $destinationEntryFacilityType = $request->input('destination_entry_facility_type');
            
            $data = [
                'sku' => $sku,
                'mail_class' => $mailClass,
                'rate_indicator' => $rateIndicator,
                'processing_category' => $processingCategory,
                'destination_entry_facility_type' => $destinationEntryFacilityType,
            ];

            return $this->orderService->shipUspsOrder($orderId, $processItems, $data);

        } catch (ItemNotFoundException $e) {
            return $this->notFoundResponse($e->getMessage());
        } catch (AuthorizationException $e) {
            return $this->authorizationResponse($e->getMessage());
        } catch (ValidationException $e) {
            return $this->validationErrorResponse($e->getMessage());
        } catch (Exception $e) {
            return $this->errorResponse('An unexpected error occurred: ' . $e->getMessage());
        }
    }

    public function sendWalletAddressEmail(Request $request)
    {
        try {
            $validation = Validator::make(
                $request->all(),
                [
                    'user_id' => 'required|integer|exists:users,user_id',
                ]
            );

            if ($validation->fails()) {
                return $this->validationErrorResponse('Validation error', $validation->errors());
            }

            $userId = $request->input('user_id');

            $response = $this->orderService->sendWalletAddressEmail($userId);

            return $this->successResponse($response, "Wallet address email sent successfully.");
        } catch (ItemNotFoundException $e) {
            return $this->notFoundResponse($e->getMessage());
        } catch (ValidationException $e) {
            return $this->validationErrorResponse($e->getMessage());
        } catch (Exception $e) {
            return $this->errorResponse('An unexpected error occurred: ' . $e->getMessage());
        }
    }

    public function markItemsAsRefunded(Request $request)
    {
        try {
            $validation = Validator::make(
                $request->all(),
                [
                    'order_id' => 'required|integer|exists:store_orders,order_id',
                    'refund_items' => 'required|array|min:1',
                    'refund_items.*' => 'required|integer|exists:product_refund,id',
                ]
            );

            if ($validation->fails()) {
                return $this->validationErrorResponse('Validation error', $validation->errors());
            }

            $orderId = $request->input('order_id');
            $refundItems = $request->input('refund_items');

            $response = $this->orderService->markItemsAsRefunded($orderId, $refundItems);

            return $this->successResponse($response, "Items marked as refunded successfully.");
        } catch (ItemNotFoundException $e) {
            return $this->notFoundResponse($e->getMessage());
        } catch (ValidationException $e) {
            return $this->validationErrorResponse($e->getMessage());
        } catch (Exception $e) {
            return $this->errorResponse('An unexpected error occurred: ' . $e->getMessage());
        }
    }
}