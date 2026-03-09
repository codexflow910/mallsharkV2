<?php

namespace Packages\Discount\Http\Controllers;


use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use InvalidArgumentException;
use App\Http\Controllers\Controller;
use Illuminate\Support\ItemNotFoundException;
use Packages\Discount\Services\CouponService;
use Illuminate\Validation\ValidationException;
use Packages\Discount\Services\DiscountService;
use Packages\Discount\Contracts\BaseApiResponse;
use Packages\Discount\Http\Requests\ApplyCouponRequest;
use Packages\Discount\Http\Requests\SaveDiscountRequest;

class DiscountController extends Controller
{
    use BaseApiResponse;

    protected $discountService;
    protected $couponService;

    public function __construct(DiscountService $discountService, CouponService $couponService)
    {
        $this->discountService = $discountService;
        $this->couponService = $couponService;
    }

    public function store(SaveDiscountRequest $request)
    {
        try {

            $discount = $this->discountService->saveDiscount($request->validated());

            if ($discount) {
                return $this->successResponse($discount, 'Discount created successfully', Response::HTTP_CREATED);
            }

        } catch (Exception $e) {

            if ($e instanceof ItemNotFoundException) {
                return $this->notFoundResponse($e->getMessage());
            }
            if ($e instanceof ValidationException) {
                return $this->validationErrorResponse($e->errors());
            }
            return $this->errorResponse($e->getMessage());
        }
        return $this->successResponse($discount, 'Discount created successfully', Response::HTTP_CREATED);
    }

    public function update(SaveDiscountRequest $request, $id)
    {
        try {
            $discount = $this->discountService->updateDiscount($request->validated(), $id);

            if ($discount) {
                return $this->updatedResponse($discount, 'Discount updated successfully');
            }
        } catch (\Exception $e) {
            if ($e instanceof ItemNotFoundException) {
                return $this->notFoundResponse($e->getMessage());
            }
            if ($e instanceof ValidationException) {
                return $this->validationErrorResponse($e->errors());
            }
            return $this->errorResponse($e->getMessage());
        }
    }

    public function getAll()
    {
        try {
            $discounts = $this->discountService->getAllDiscounts();
            return $this->successResponse($discounts, 'Discounts fetched successfully');
        } catch (\Exception $e) {
            if ($e instanceof ItemNotFoundException) {
                return $this->notFoundResponse($e->getMessage());
            }
            return $this->errorResponse($e->getMessage());
        }
    }

    public function get($id)
    {
        try {
            $discount = $this->discountService->getDiscount($id);
            return $this->successResponse($discount, 'Discount fetched successfully');
        } catch (\Exception $e) {
            if ($e instanceof ItemNotFoundException) {
                return $this->notFoundResponse($e->getMessage());
            }
            return $this->errorResponse($e->getMessage());
        }
    }

    public function updateStatus(Request $request, $id)
    {
        try {         
            $validated = $request->validate([
                'status' => 'required|in:active,inActive', 
            ]);
            $status = $validated['status'];

            $response = $this->discountService->updateDiscountStatus($id, $status);

            return $this->successResponse(null , $response['message'], Response::HTTP_OK);
        } catch (\Exception $e) {
            if ($e instanceof ItemNotFoundException) {
                return $this->notFoundResponse($e->getMessage());
            }
            return $this->errorResponse($e->getMessage());
        }
    }

    public function confirmUpdateStatus($id)
    {
        try {
            $response = $this->discountService->confirmUpdateStatus($id);

            // return response()->json($response, $response['status'] ? 200 : 400);
            return $this->successResponse(null , $response['message'], Response::HTTP_OK);
        } catch (\Exception $e) {
            if ($e instanceof ItemNotFoundException) {
                return $this->notFoundResponse($e->getMessage());
            }
            return $this->errorResponse($e->getMessage());
        }
    }

    public function archive($id)
    {
        try {
            $response = $this->discountService->archiveDiscount($id);            

            // return response()->json($response, $response['status'] ? 200 : ($response['status'] === false && isset($response['message']) ? 404 : 500));
            return $this->successResponse(null , $response['message'], Response::HTTP_OK);
        } catch (\Exception $e) {
            if ($e instanceof ItemNotFoundException) {
                return $this->notFoundResponse($e->getMessage());
            }
            return $this->errorResponse($e->getMessage());
        }
    }

    public function unArchive($id)
    {
        try {
            $response = $this->discountService->unArchiveDiscount($id);
            return $this->successResponse(null , $response['message'], Response::HTTP_OK);
        } catch (\Exception $e) {
            if ($e instanceof ItemNotFoundException) {
                return $this->notFoundResponse($e->getMessage());
            }
            return $this->errorResponse($e->getMessage());
        }
    }

    public function getArchives()
    {
        try {
            $discounts = $this->discountService->getArchivedDiscounts();

            return $this->successResponse($discounts, 'Archived discounts fetched successfully');
        } catch (\Exception $e) {
            if ($e instanceof ItemNotFoundException) {
                return $this->notFoundResponse($e->getMessage());
            }
            return $this->errorResponse($e->getMessage());
        }
    }


    public function applyCoupon(ApplyCouponRequest $request)
    {
        try {
            $data = $request->validated();
            $result = $this->couponService->applyCoupon($data);

            return $this->successResponse([
                'discountAmount' => $result['discountAmount'],
                'cartTotalAfterDiscount' => $result['cartTotalAfterDiscount'],
            ], 'Coupon applied successfully!');
        } catch (\Exception $e) {
            if ($e instanceof ItemNotFoundException) {
                return $this->notFoundResponse($e->getMessage());
            }
            if ($e instanceof ValidationException) {
                return $this->validationErrorResponse($e->errors());
            }
            return $this->errorResponse($e->getMessage());
        }
    }


}
