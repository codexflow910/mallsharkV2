<?php

namespace Packages\Shipping\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\ItemNotFoundException;
use Illuminate\Validation\ValidationException;
use Packages\Shipping\Services\ShippingService;
use Illuminate\Validation\UnauthorizedException;
use Packages\Shipping\Contracts\BaseApiResponse;
use Packages\Shipping\Contracts\ShippingHelpers;
use Illuminate\Auth\Access\AuthorizationException;
use Packages\Shipping\Http\Requests\CarrierRequest;
use Packages\Shipping\Http\Requests\ShippingProfileRequest;


class ShippingController extends Controller
{
    use BaseApiResponse;
    use ShippingHelpers;

    protected $shippingService;

    public function __construct(ShippingService $shippingService)
    {
        $this->shippingService = $shippingService;
    }
    public function updateShippingProfile(Request $request)
    {
        try {

            $data = $request->all();
            $result = $this->shippingService->updateShippingProfile($data);
            if ($result === "Profile is already exist with this name") {
                // Handle the case when the profile already exists
                return $this->successResponse($result, 'Profile is already exist with this name');
            }
            if ($result) {
                return $this->successResponse($result, 'shipping profile created');
            }
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
            if ($e->getMessage() === 'Profile is already exist with this name') {
                return response()->json(['error' => $e->getMessage()], 409); // 409 Conflict
            }
            return $this->errorResponse($e->getMessage());
        }
    }
    public function getShippingOptions()
    {
        try {
            $result = $this->shippingService->getShippingOptions();
            return $this->successResponse($result, 'shipping profile Found');
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

    public function deleteShippingProfile(Request $request)
    {

        try {
            $validation = Validator::make(
                $request->all(),
                [
                    'shipping_id' => 'required|integer'
                ],
            );

            if ($validation->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validation error',
                    'errors' => $validation->errors()
                ], 422);
            }

            $shippingId = $request->input('shipping_id');

            $this->shippingService->deleteShippingProfile($shippingId);

            return $this->deletedResponse('Shipping Profile deleted successfully');
        } catch (ItemNotFoundException $e) {
            return $this->notFoundResponse($e->getMessage());
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    public function storeCarrier(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|unique:shipping_carriers,name',
                'logo' => 'nullable|string',
                'is_active' => 'nullable|boolean',
            ]);

            $data = [
                'name' => $validated['name'],
                'logo' => null,
                'is_active' => $validated['is_active']
            ];

            $result = $this->shippingService->storeCarrier($data);
            return $this->successResponse($result, 'Carrier created successfully');
        } catch (Exception $e) {
            if ($e instanceof ValidationException) {
                return $this->validationErrorResponse($e->errors());
            }
            return $this->errorResponse($e->getMessage());
        }
    }

    public function getCarriers()
    {
        try {
            $result = $this->shippingService->getCarriers();
            return $this->successResponse($result, 'Carriers fetched successfully');
        } catch (Exception $e) {
            if ($e instanceof ItemNotFoundException) {
                return $this->notFoundResponse($e->getMessage());
            }
            return $this->errorResponse($e->getMessage());
        }
    }

    public function storeProfile(ShippingProfileRequest $request)
    {
        try {
            $result = $this->shippingService->storeProfile($request->validated());
            return $this->successResponse($result, 'Profile created successfully');
        } catch (Exception $e) {
            if ($e instanceof ValidationException) {
                return $this->validationErrorResponse($e->errors());
            }
            return $this->errorResponse($e->getMessage());
        }
    }

    public function updateProfile(ShippingProfileRequest $request, $id)
    {
        try {
            $result = $this->shippingService->updateProfile($request->validated(), $id);
            return $this->successResponse($result, 'Profile updated successfully');
        } catch (Exception $e) {
            if ($e instanceof ValidationException) {
                return $this->validationErrorResponse($e->errors());
            }
            if ($e instanceof ItemNotFoundException) {
                return $this->notFoundResponse($e->getMessage());
            }
            return $this->errorResponse($e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            $result = $this->shippingService->deleteProfile($id);
            return $this->successResponse($result, 'Profile deleted successfully');
        } catch (\Exception $e) {
            if ($e instanceof ItemNotFoundException) {
                return $this->notFoundResponse($e->getMessage());
            }
            return $this->errorResponse($e->getMessage());
        }
    }

    public function getProfiles(Request $request)
    {
        try {
            $validation = Validator::make(
                $request->all(),
                [
                    'profile_id' => 'nullable|integer',
                    'store_id' => 'nullable|integer|exists:stores,store_id'
                ],
            );

            if ($validation->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validation error',
                    'errors' => $validation->errors()
                ], 422);
            }

            $profileId = $request->input('profile_id');
            $storeId = $request->input('store_id') ?? auth()->user()->store->store_id;

            $result = $this->shippingService->getProfiles($storeId, $profileId);

            return $this->successResponse($result, 'Store shipping profiles retrieved successfully');
        } catch (\Exception $e) {
            if ($e instanceof ItemNotFoundException) {
                return $this->notFoundResponse($e->getMessage());
            }
            return $this->errorResponse($e->getMessage());
        }
    }

    public function assignCarrierToStore(CarrierRequest $request)
    {
        try {

            $data = $request->validated();

            $result = $this->shippingService->assignOrUpdateCarrierToStore($data);

            return $this->successResponse($result, 'Carrier assigned to store successfully');
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    public function getCarrier(Request $request)
    {
        try {
            $validation = Validator::make(
                $request->all(),
                [
                    'store_id' => 'nullable|integer|exists:stores,store_id',
                    'carrier_id' => 'nullable|integer|exists:shipping_carriers,id',
                ],
            );

            if ($validation->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validation error',
                    'errors' => $validation->errors()
                ], 422);
            }

            $storeId = $request->input('store_id') ?? auth()->user()->store->store_id;
            $carrierId = $request->input('carrier_id') ?? null;

            $result = $this->shippingService->getCarrier($storeId, $carrierId);

            return $this->successResponse($result, 'Store Carrier retrieved successfully');
        } catch (ItemNotFoundException $e) {
            return $this->notFoundResponse($e->getMessage());
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }
}
