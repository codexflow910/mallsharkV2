<?php

namespace Packages\Core\Http\Controllers\V3;


use Exception;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Dotenv\Exception\ValidationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\ItemNotFoundException;
use Packages\Core\Services\V3\CoreService;
use Packages\Core\Contracts\V3\BaseApiResponse;
use Packages\Core\Http\Requests\V3\PackageBuyRequest;
use Packages\Product\Models\V3\Product;

class CoreController extends Controller
{

    protected $coreService;
    use BaseApiResponse;

    public function __construct(CoreService $coreService)
    {
        $this->coreService = $coreService;
    }

 
    public function createShipstationOrder(Request $request)
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
            $response = $this->coreService->createShipstationOrder($orderId);

            return $this->createdResponse($response, 'ShipStation Order created successfully');
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    public function syncShipstationOrdersStatuses(Request $request)
    {
        try {
            $response = $this->coreService->syncShipstationOrdersStatuses();

            if (empty($response)) {
                return $this->successResponse(null, 'No ShipStation orders to sync');
            }

            return $this->successResponse($response, 'ShipStation Orders statuses synced successfully');
        } catch (Exception $e) {
            Log::error('ShipStation Order Sync Failed', ['error' => $e->getMessage()]);
            return $this->errorResponse($e->getMessage());
        }
    }


    public function createShipStationWebhook()
    {
        try {
            $response = $this->coreService->createShipStationWebhook();

            return $this->createdResponse($response, 'ShipStation Webhook created successfully');
        } catch (\Exception $e) {
            Log::error('ShipStation Webhook Creation Failed', ['error' => $e->getMessage()]);
            return $this->errorResponse($e->getMessage(), 500);
        }
    }


    public function handleTrackEvent(Request $request)
    {
        
        try {
            $data = $request->all();
            $shipmentDetails = $this->coreService->fetchShipmentDetails($data['resource_url']);

            return $this->successResponse($shipmentDetails, 'Shipment details fetched successfully');
        } catch (\Exception $e) {
            Log::error('Error fetching shipment details: ' . $e->getMessage());
            return $this->errorResponse('Failed to process webhook', 500);
        }
    }

    public function getPackageCheckout(PackageBuyRequest $request)
    {
        try {
            $data = $request->all();
            $response = $this->coreService->getPackageCheckout($data);

            return $this->successResponse($response, "Mallshark package purchased successfully.");
        } catch (Exception $e) {
            Log::error('Package buying process Failed: ', ['error' => $e->getMessage()]);
            return $this->errorResponse($e->getMessage(), 500);
        }
    }
    public function getCoupons(Product $product)
    {
        try {
           
            $response = $this->coreService->getCoupons($product);
            return $this->successResponse($response, "Mallshark package purchased successfully.");
        } catch (Exception $e) {
            Log::error('Package buying process Failed: ', ['error' => $e->getMessage()]);
            return $this->errorResponse($e->getMessage(), 500);
        }
    }
    public function PackageConfirmation()
    {
        try {
            $id = request()->query('sessionId');
            if (!$id) {
                return $this->successResponse([
                    'status' => false,
                    'success_url' => 'myapp://payment-cancel'
                ], 'Package canceled.');
            }
            $response = $this->coreService->PackageConfirmation($id);
            return $this->successResponse($response, "Package purchased successfully.");
        } catch (Exception $e) {
            Log::error('Package buying process Failed: ', ['error' => $e->getMessage()]);
            return $this->errorResponse($e->getMessage(), 500);
        }  
    }

    public function getUspsRates(Request $request)
    {
        try {
            $validation = Validator::make(
                $request->all(), [
                'order_id' => 'required|integer|exists:store_orders,order_id',
                'process_items' => 'required|array',
            ]);

            if ($validation->fails()) {
                return $this->validationErrorResponse('Validation error', $validation->errors());
            }
            
            $orderId = $request->input('order_id');
            $processItems = $request->input('process_items');

            $response = $this->coreService->getUspsRates($orderId, $processItems);

            return $this->successResponse($response, 'USPS Rates fetched successfully');
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }
    public function calculateTax(Request $request)
    {
        $request->validate([
            'state_id' => 'required|integer|min:1',
            'category_id' => 'required|integer',
            'amount' => 'required|numeric|min:0',
            'quantity' => 'required|integer|min:1',
            'postal_code' => 'required|string|min:5|max:10',
        ]);

        try {
            $tax = $this->coreService->calculateTax(
                $request->state_id,
                $request->category_id,
                $request->amount,
                $request->quantity,
                $request->postal_code
            );

            if (!empty($tax['error'])) {
                return response()->json([
                    'success' => false,
                    'message' => $tax['error'],
                    'details' => $tax['details'] ?? null,
                ], 400);
            }
            $response = [   
                'state' => $tax['state_id'],
                'category_id' => $tax['category_id'],
                'amount' => $request->amount,
                'quantity' => $request->quantity,
                'subtotal' => $tax['subtotal'],
                'rate' => $tax['rate'],
                'tax_amount' => $tax['tax_amount'],
                'total' => $tax['total'],
            ];
            return $this->successResponse($response, 'Tax calculated successfully');

        } catch (ValidationException $e) {
            return $this->validationErrorResponse($e->getMessage());
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }
    public function uspsValidateAddress(Request $request)
    {
        try {
            $validation = Validator::make($request->all(), [
                'order_id' => 'required|integer|exists:store_orders,order_id',
            ]);

            if ($validation->fails()) {
                return $this->validationErrorResponse('Validation error', $validation->errors());
            }

            $orderId = $request->input('order_id');
            return $this->coreService->uspsValidateAddress($orderId);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }
}
