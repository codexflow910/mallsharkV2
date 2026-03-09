<?php

namespace Packages\Shipping\Http\Controllers\V2;

use Exception;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\ItemNotFoundException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Validation\ValidationException;
use Packages\Shipping\Services\V2\ShippingService;
use Illuminate\Validation\UnauthorizedException;
use Packages\Shipping\Contracts\V2\BaseApiResponse;
use Packages\Shipping\Contracts\V2\ShippingHelpers;
use Packages\Shipping\Http\Requests\V2\CarrierRequest;
use Packages\Shipping\Http\Requests\V2\ShippingProfileRequest;
use Packages\Product\Services\V2\ProductService;
use Packages\Shipping\Http\Requests\V2\CarrierConfigRequest;

class ShippingController extends Controller
{
    use BaseApiResponse;
    use ShippingHelpers;

    protected $shippingService;
    protected $productService;

    public function __construct(ShippingService $shippingService, ProductService $productService)
    {
        $this->shippingService = $shippingService;
        $this->productService = $productService;
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

    public function storeNewCarrier(Request $request)
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

            $result = $this->shippingService->storeNewCarrier($data);
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

            if ($result) {
                $productIds = ShippingHelpers::getProductsIdsForShippingId($id);
                if (!empty($productIds)) {
                    foreach ($productIds as $productId) {
                        $this->productService->assignLabels($productId);
                    }
                }
            }
            
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
            $userId = auth()->id();
            $store = ShippingHelpers::getAccessibleStore($userId);
            if (!$store) {
                throw new ItemNotFoundException('User does not belong or have access to any store.');
            }
            
            $data['store_id'] = $store->store_id;
            $result = $this->shippingService->assignOrUpdateCarrierToStore($data);

            return $this->successResponse($result, 'Carrier assigned to store successfully');
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    public function getStoreCarrier(Request $request)
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

            $userId = auth()->id();
            $store = ShippingHelpers::getAccessibleStore($userId);
            if (!$store) {
                throw new ItemNotFoundException('User does not belong or have access to any store.');
            }

            $storeId = $store->store_id;
            $storeId = $request->input('store_id') ?? $storeId;
            $carrierId = $request->input('carrier_id') ?? null;

            $result = $this->shippingService->getCarrier($storeId, $carrierId);
            return $this->successResponse($result, 'Store Carrier retrieved successfully');
        } catch (ItemNotFoundException $e) {
            return $this->notFoundResponse($e->getMessage());
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    public function storeCarrierConfig(CarrierConfigRequest $request)
    {
        try {
            $data = $request->validated();
            
            $userId = auth()->id();
            $store = ShippingHelpers::getAccessibleStore($userId);
            if (!$store) {
                throw new ItemNotFoundException('User does not belong or have access to any store.');
            }

            $carrierSlug = $data['carrier'];
            $carrier = ShippingHelpers::getShippingCarrierBySlug($carrierSlug);
            if (!$carrier) {
                throw new ItemNotFoundException($carrierSlug . " carrier not found");
            }

            $carrierId = $carrier->id;
            $newCarrier['store_id'] = $store->store_id;
            $newCarrier['shipping_carrier_id'] = $carrierId;
            
            $result = $this->shippingService->assignOrUpdateCarrierToStore($newCarrier);

            $data['store_id'] = $store->store_id;
            $result = $this->shippingService->storeCarrierConfig($data);
            return $this->successResponse($result, $data['carrier'] . " configuration saved successfully.");
        } catch (ItemNotFoundException $e) {
            return $this->notFoundResponse($e->getMessage());
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    public function deleteStoreCarrier(Request $request)
    {
        try {
            $validation = Validator::make(
                $request->all(),
                [
                    'carrier_id' => 'required|integer|exists:shipping_carriers,id',
                ],
            );

            if ($validation->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validation error',
                    'errors' => $validation->errors()
                ], 422);
            }

            $carrierId = $request->input('carrier_id');
            
            $userId = auth()->id();
            $store = ShippingHelpers::getAccessibleStore($userId);
            if (!$store) {
                throw new ItemNotFoundException('User does not belong or have access to any store.');
            }
            
            $storeId = $store->store_id;
            $result = $this->shippingService->deleteStoreCarrier($storeId, $carrierId);
            return $this->deletedResponse($result);
        } catch (Exception $e) {
            return $this->errorResponse('Failed to save carrier configuration: ' . $e->getMessage());
        }
    }
}
