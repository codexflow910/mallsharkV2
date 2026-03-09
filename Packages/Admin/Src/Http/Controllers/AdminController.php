<?php

namespace Packages\Admin\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\ItemNotFoundException;
use Illuminate\Validation\ValidationException;
use App\Http\Controllers\Controller;
use Packages\Admin\Services\AdminService;



use Packages\Admin\Contracts\BaseApiResponse;

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
    public function gettransferVendorPayment(Request $requests){
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
            } 
            elseif($amount == 0){
              
                return $this->successResponse(0, 'Orders Amount');
            }
            else {
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
}
