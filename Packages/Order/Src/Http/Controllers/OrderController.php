<?php

namespace Packages\Order\Http\Controllers;

use PDF;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use Dotenv\Exception\ValidationException;
use Illuminate\Support\Facades\Validator;
use Packages\Order\Services\OrderService;
use Packages\Order\Contracts\OrderHelpers;
use Illuminate\Support\ItemNotFoundException;
use Packages\Order\Contracts\BaseApiResponse;
use Packages\Order\Http\Requests\GetOrdersRequest;
use Packages\Order\Http\Requests\UpdateOrderRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Auth\Access\AuthorizationException;
use GuzzleHttp\Client;
use Packages\Order\Http\Requests\OrderCancellationRequest;

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
            $input = $request->validated();
            $input = $request->all();
            $result = $this->orderService->updateOrderStatus($input);
            return $this->successResponse(null, "Order {$input['status']} successfully.");
        } catch (ItemNotFoundException $e) {
            return $this->notFoundResponse($e->getMessage());
        } catch (ValidationException $e) {
            return $this->validationErrorResponse($e->getMessage());
        } catch (Exception $e) {
            return $this->errorResponse('An unexpected error occurred: ' . $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
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
      public function return_refund_stripe(Request $request)
    {
   try{
 
     $validatedData = $request->validate([
        'order_id' => 'required|numeric|exists:store_orders,order_id',
    ]);
      $input = $request->all();
      $input =$request->all();
      $result = $this->orderService->return_refund_stripe($input);
         return $this->successResponse($result, "Success");
        }catch (ItemNotFoundException $e) {
            return $this->notFoundResponse($e->getMessage());
        } catch (ValidationException $e) {
            return $this->validationErrorResponse($e->getMessage());
        } catch (Exception $e) {
            return $this->errorResponse('An unexpected error occurred: ' . $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
     
    }
      public function rejectOrderStoreOwner(Request $request)
    {
   try{
     $validatedData = $request->validate([
        'order_id' => 'required|numeric|exists:store_orders,order_id',
    ]);
      $input = $request->all();
      $input =$request->all();
      
      $result = $this->orderService->rejectOrderStoreOwner($input);
         return $this->successResponse($result, "Success");
        }catch (ItemNotFoundException $e) {
            return $this->notFoundResponse($e->getMessage());
        } catch (ValidationException $e) {
            return $this->validationErrorResponse($e->getMessage());
        } catch (Exception $e) {
            return $this->errorResponse('An unexpected error occurred: ' . $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
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
                'post_code' => 'nullable|integer',
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
}