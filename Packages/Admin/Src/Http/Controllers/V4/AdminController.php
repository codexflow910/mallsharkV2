<?php

namespace Packages\Admin\Http\Controllers\V4;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\ItemNotFoundException;
use Illuminate\Validation\ValidationException;
use App\Http\Controllers\Controller;
use Packages\Admin\Contracts\V4\BaseApiResponse;
use Packages\Admin\Services\V4\AdminService;
use Packages\Store\Models\Store;
class AdminController extends Controller
{
    use BaseApiResponse;


    protected $adminService;

    public function __construct(AdminService $adminService)
    {
        $this->adminService = $adminService;
    }
    public function  getorderstransferVendorPayment(Request $requests)
    {
        try {
            $validation = Validator::make(
                $requests->all(),
                [
                    'store_id' => 'required|exists:stores,store_id',
                ],
            );
            if ($validation->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validation error',
                    'errors' => $validation->errors()
                ], 422);
            }
            $store_id = $requests['store_id'];

            $amount = $this->adminService->getorderstransferVendorPayment($store_id);

            if ($amount) {
                return $this->successResponse($amount, 'Orders Amount');
            } elseif ($amount == 0) {
                throw new ItemNotFoundException('No Orders Found');
                return $this->successResponse(0, 'Orders Amount');
            } else {
                throw new ItemNotFoundException('No Orders Found');
            }
        } catch (ItemNotFoundException $e) {
            return $this->notFoundResponse($e->getMessage());
        } catch (ValidationException $e) {
            return $this->validationErrorResponse($e->getMessage());
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }
    public function getByStoreId($storeId)
    {
      try{
        $deduction = $this->adminService->getDeductionByStoreId($storeId);
        return response()->json([
            'status' => true,
            'data' => $deduction
        ]);
     } catch (ItemNotFoundException $e) {
            return $this->notFoundResponse($e->getMessage());
        } catch (ValidationException $e) {
            return $this->validationErrorResponse($e->getMessage());
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    public function createOrUpdate(Request $request)
    {
       
        $data = $request->validate([
            'store_id' => 'required|integer',
            'mall_shark_amount' => 'required|numeric',
            'shipping_check' => 'nullable|boolean',
            'ads_check' => 'nullable|boolean',
            'ad_amount' => 'nullable|numeric',
        ]);

        $deduction = $this->adminService->createOrUpdate($data);

        return response()->json([
            'status' => true,
            'message' => 'Store deduction saved successfully.',
            'data' => $deduction
        ]);
    }
    public function transferOrderVendorPayment(Request $requests)
    {
        try {
            $validation = Validator::make(
                $requests->all(),
                [
                    'store_id' => 'required|exists:stores,store_id',
                    'amount' => 'required',
                    'orderIds' => 'required|array|min:1',
                    'orderIds.*' => 'integer', //|exists:orders,order_id
                ],
            );
            if ($validation->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validation error',
                    'errors' => $validation->errors()
                ], 422);
            }
            $store_id = $requests['store_id'];
            $amount = $requests['amount'];
            $orderIds = $requests['orderIds'];

            $admin = $this->adminService->transferOrderVendorPayment($store_id, $amount, $orderIds);
            if ($admin) {
                return $this->successResponse($admin, 'Send Amount to store successfully');
            } else {
                throw new ItemNotFoundException('No Orders Found');
            }
        } catch (ItemNotFoundException $e) {
            return $this->notFoundResponse($e->getMessage());
        } catch (ValidationException $e) {
            return $this->validationErrorResponse($e->getMessage());
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }
    public function gettransferVendorPayment(Request $requests)
    {
        try {
            $validation = Validator::make(
                $requests->all(),
                [
                    'store_id' => 'required|exists:stores,store_id',
                ],
            );
            if ($validation->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validation error',
                    'errors' => $validation->errors()
                ], 422);
            }
            $store_id = $requests['store_id'];

            $amount = $this->adminService->gettransferVendorPayment($store_id);

            if ($amount) {
                return $this->successResponse($amount, 'Orders Amount');
            } elseif ($amount == 0) {

                return $this->successResponse(0, 'Orders Amount');
            } else {
                throw new ItemNotFoundException('No Orders Found');
            }
        } catch (ItemNotFoundException $e) {
            return $this->notFoundResponse($e->getMessage());
        } catch (ValidationException $e) {
            return $this->validationErrorResponse($e->getMessage());
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    public function transferVendorPayment(Request $requests)
    {
        try {
            $validation = Validator::make(
                $requests->all(),
                [
                    'store_id' => 'required|exists:stores,store_id',
                    'amount' => 'required',
                ],
            );
            if ($validation->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validation error',
                    'errors' => $validation->errors()
                ], 422);
            }
            $store_id = $requests['store_id'];
            $amount = $requests['amount'];

            $admin = $this->adminService->transferVendorPayment($store_id, $amount);
            if ($admin) {
                return $this->successResponse($admin, 'Send Amount to store successfully');
            } else {
                throw new ItemNotFoundException('No Orders Found');
            }
        } catch (ItemNotFoundException $e) {
            return $this->notFoundResponse($e->getMessage());
        } catch (ValidationException $e) {
            return $this->validationErrorResponse($e->getMessage());
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }
    public function toggleAiFeature($storeId)
    {
        $store = Store::findOrFail($storeId);

        // Toggle the current value
        $store->ai_enabled = !$store->ai_enabled;
        $store->save();

        return response()->json([
            'message' => 'AI feature toggled successfully.',
            'ai_enabled' => $store->ai_enabled,
        ]);
    }
    public function resetAiToken(Request $request){
        try{
            $storeId = $request['store_id'];
            $store = Store::find($storeId);
            $tokens = env('AI_TOKENS');
            if($store && $store['ai_enabled']){
                $store['ai_tokens_remaining'] = $tokens;
                $store['ai_tokens_used'] = 0;
                $store['ai_quota_reset_at'] = now();
                $store->save();
                return $this->successResponse($store, 'Token quota reset successfuly');
            }else{
                return $this->notFoundResponse('Ai disabled');
            }
        } catch (ItemNotFoundException $e) {
            return $this->errorResponse($e->getMessage());
        }
    }
 
}
