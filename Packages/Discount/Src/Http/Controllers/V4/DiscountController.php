<?php

namespace Packages\Discount\Http\Controllers\V4;


use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use InvalidArgumentException;
use App\Http\Controllers\Controller;
use Illuminate\Support\ItemNotFoundException;
use Packages\Discount\Services\V4\CouponService;
use Illuminate\Validation\ValidationException;
use Packages\Discount\Services\V4\DiscountService;
use Packages\Discount\Contracts\V4\BaseApiResponse;
use Packages\Discount\Http\Requests\V4\ApplyCouponRequest;
use Packages\Discount\Http\Requests\V4\SaveDiscountRequest;

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
            ini_set('max_execution_time', 300);
            $discount = $this->discountService->updateDiscount($request->validated(), $id);
            if ($discount && $discount['discount']->type == 'sale' && $discount['discount']->status == 'active' && $discount['discount']->state == 'live' && $discount['discount']->archive == false) {
                return $this->updatedResponse($discount, 'changes will be applied after 5 minutes of discount update');
            }else if($discount){
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
                'discount_type' => 'sometimes|in:coupon,sale',
            ]);
            $status = $validated['status'];
            $discountType = $validated['discount_type'];

            $response = $this->discountService->updateDiscountStatus($id, $status,$discountType);

            return $this->successResponse(null , $response['message'], Response::HTTP_OK);
        } catch (\Exception $e) {
            if ($e instanceof ItemNotFoundException) {
                return $this->notFoundResponse($e->getMessage());
            }
            return $this->errorResponse($e->getMessage());
        }
    }

    public function confirmUpdateStatus(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'discount_type' => 'sometimes|in:coupon,sale',
            ]);
            $discountType = $validated['discount_type'];
            $response = $this->discountService->confirmUpdateStatus($id, $discountType);

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
    public function getCoupons(Request $request)
    {
        try {
            $data=$request->all();
            $discount = $this->discountService->getCoupons($data);
            if(!empty($discount)){
                return $this->successResponse($discount, "Discount get Successfully.");
            }
            return $this->errorResponse("No discounts found.", 404);

        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }


    public function couponsProducts($id)
    {
        try {
            $discount = $this->discountService->couponProduct($id);

            if(!empty($discount)){
                return $this->successResponse($discount, "Products Related this coupons get Successfully.");
            }
            return $this->errorResponse("No coupons product found.", 404);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }
    public function Customercoupons(Request $request)
    {
        try {
            $data = $request->validate([
                'coupon_id' => 'required',
            ]);
            $discount = $this->discountService->customerCoupons($data);
            if (!empty($discount)) {
                return $this->successResponse($discount, "Customer coupon saved successfully.");
            }
            return $this->errorResponse("Coupon not saved.", 404);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }
    public function customerCouponsDashboard()
    {
        try {
            $user = auth()->user();
            $discount = $this->discountService->customerCouponsDashboard($user);
            if (!empty($discount)) {
                return $this->successResponse($discount, "Customer coupons fetched successfully.");
            }
            return $this->errorResponse("No coupons found for the customer.", 404);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }
}
