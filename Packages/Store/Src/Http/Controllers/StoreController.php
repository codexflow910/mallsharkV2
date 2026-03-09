<?php

namespace Packages\Store\Http\Controllers;


use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\ItemNotFoundException;
use Illuminate\Validation\ValidationException;
use Packages\Store\Http\Requests\StoreRequest;
use Packages\Store\Http\Requests\StoreUpdateAccountRequest;
use Packages\Store\Http\Requests\CreateStoreRequest;
use Packages\Store\Http\Requests\StoreAddressRequest;
use Packages\Store\Http\Requests\ContactRequest;
use Packages\Store\Http\Requests\StoreStatusRequest;
use Illuminate\Validation\UnauthorizedException;
use Packages\Store\Contracts\BaseApiResponse;
use Packages\Store\Services\StoreService;

class StoreController extends Controller
{
    use BaseApiResponse;


    protected $storeService;

    public function __construct(StoreService $storeService)

    {
        $this->storeService = $storeService;
        // $this->middleware('guest')->except('logout');
    }


    public function store(StoreRequest $request)
    {
        dd('store request');
        try {
        } catch (Exception $e) {

            //check exception type and return reponse based on that using errorResponse method

            if ($e instanceof ItemNotFoundException) {
                return $this->notFoundResponse($e->getMessage());
            }
            return $this->errorResponse($e->getMessage());
        }
    }

    public function getStoreProducts(Request $request)
    {
        try {
            $products = $this->storeService->getStoreProducts($request);

            return $this->successResponse($products, 'Store products retrieved successfully');
        } catch (ItemNotFoundException $e) {
            return $this->notFoundResponse($e->getMessage());
        } catch (ValidationException $e) {
            return $this->validationErrorResponse($e->getMessage());
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }
    public function addStore(CreateStoreRequest $request)
    {
        try {
            $data = $request->validated();
            $store = $this->storeService->addStore($data);
            if ($store) {
                return $this->successResponse($store, 'Company profile has been created');
            }
        } catch (ItemNotFoundException $e) {
            return $this->notFoundResponse($e->getMessage());
        } catch (ValidationException $e) {
            return $this->validationErrorResponse($e->getMessage());
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }
    public function editStore()
    {
        try {
            $store = $this->storeService->editStore();
            if ($store) {
                return $this->successResponse($store, 'Store Information');
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
            return $this->errorResponse($e->getMessage());
        }
    }
    public function updateStore(CreateStoreRequest $request)
    {
        try {
            $data = $request->validated();
            $store = $this->storeService->updateStore($data);
            return $this->successResponse($store, 'Store Updated Successfully');
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
    public function addStoreAddress(StoreAddressRequest $request)
    {
        try {
            $data = $request->validated();
            $data = $request->all();
            $store = $this->storeService->addStoreAddress($data);
            return $this->successResponse($store, 'Store Updated Successfully');
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
    public function StoreStatusChange(StoreStatusRequest $request)
    {
        try {
            $data = $request->validated();
            $data = $request->all();
            $store = $this->storeService->StoreStatusChange($data);
            return $this->successResponse($store, $store['message']);
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
    public function updateAccountSetting(StoreUpdateAccountRequest $request)
    {
        try {
            $data = $request->validated();
            $data = $request->all();
            $store = $this->storeService->updateAccountSetting($data);
            if ($store) {
                return $this->successResponse($store, "User Updated Successfully");
            } else {
                throw new ItemNotFoundException("Failed to Update");
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
            return $this->errorResponse($e->getMessage());
        }
    }


    public function getAllCountries()
    {
        try {
            $countries = $this->storeService->getAllCountries();

            return $this->successResponse($countries, 'All countries retrieved successfully');
        } catch (ItemNotFoundException $e) {
            return $this->notFoundResponse($e->getMessage());
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    public function getStatesByCountryId($countryId)
    {
        try {
            $states = $this->storeService->getStatesByCountryId($countryId);

            return $this->successResponse($states, 'States retrieved successfully');
        } catch (ItemNotFoundException $e) {
            return $this->notFoundResponse($e->getMessage());
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    public function deleteBanner(Request $request)
    {
        try {
            $validation = Validator::make(
                $request->all(),
                [
                    'store_id' => 'required',
                    'filename' => 'required',
                    'image_id' => 'required'
                ],
            );

            if ($validation->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validation error',
                    'errors' => $validation->errors()
                ], 422);
            }

            $deleted = $this->storeService->deleteBanner($request->all());

            if ($deleted) {
                return $this->deletedResponse('Banner deleted successfully.');
            }

            return $this->notFoundResponse('Banner not found or already deleted.');
        } catch (ItemNotFoundException $e) {
            return $this->notFoundResponse($e->getMessage());
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }
    public function contactStore(ContactRequest $request)
    {
        try {
            $data = $request->validated();
            $data = $request->all();
            $result = $this->storeService->contactStore($data);
            if ($result) {
                return $this->successResponse($result, 'Thank you! Your support request has been submitted successfully.');
            } else {
                throw new ItemNotFoundException("Failed to Request");
            }
        } catch (ItemNotFoundException $e) {
            return $this->notFoundResponse($e->getMessage());
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    /**
	 * Get Current User's Store Products.
	 */
    public function allProducts()
    {
        try {
            $response = $this->storeService->getAllProducts();

            return $this->successResponse($response, 'Products retrieved successfully.');
        } catch (ItemNotFoundException $e) {
            return $this->notFoundResponse($e->getMessage());
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    public function getAllBrands(Request $request)
    {
        try {
            $brand = $request->input('keyword');
            $response = $this->storeService->getAllBrands($brand);

            return $this->successResponse($response, 'Data retrieved successfully.');
        } catch (ItemNotFoundException $e) {
            return $this->notFoundResponse($e->getMessage());    
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }
     public function getStoreAbout()
    {
        try {
            $store = $this->storeService->getStoreAbout();
            return $this->successResponse($store, 'Store retrieved successfully');
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
     public function updateStoreAbout(Request $request)
    {
        try {
          
            $validateUser = Validator::make(
			$request->all(),
			[
				'about_store' => 'required',
			]
		);

		if ($validateUser->fails()) {
		   throw new ValidationException("Validation errors",$validateUser->errors());
		}
            $about=$request->input('about_store');
           
            $store = $this->storeService->updateStoreAbout($about);
            return $this->successResponse($store, 'Store updated successfully');
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
    public function storeHome(Request $request)
    {
        try {

            $validateUser = Validator::make(
                $request->all(),
                [
            'store_slug' => ['required', 'exists:stores,store_slug'],
            'name' => 'nullable|string',
            'category' => 'nullable|string',
            'min_price' => 'nullable|numeric|min:0',
            'max_price' => 'nullable|numeric|min:0',
            'page' => 'nullable',
            'on_sale' => 'nullable',
            'mallshark_selected' => 'nullable|numeric',
            'shipping_option_id' => 'nullable|numeric',
            'per_page' => 'nullable',
            'sort_by' => 'nullable',
            'order' => 'nullable|string'
                ]
            );

            if ($validateUser->fails()) {
                throw new ValidationException("Validation errors", $validateUser->errors());
            }
            $slug = $request->input('store_slug');
            $data = $request->all();
            $store = $this->storeService->storeHome($slug, $data);
            if (!empty($store['Product']) && $store['Product']->isNotEmpty()) {
                return $this->successResponse($store, 'Product successfully');
            } else {
                throw new ItemNotFoundException("No Product Found");
            }
            return $this->successResponse($store, 'Store updated successfully');
        } catch (ItemNotFoundException $e) {
            return $this->notFoundResponse($e->getMessage());
        } catch (ValidationException $e) {
            return $this->validationErrorResponse($e->getMessage());
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }
    public function storeinfo(Request $request)
    {
        try {

            $validateUser = Validator::make(
                $request->all(),
                [
                    'store_slug' => ['required', 'exists:stores,store_slug'],
                ]
            );

            if ($validateUser->fails()) {
                throw new ValidationException("Validation errors", $validateUser->errors());
            }
            $slug = $request->input('store_slug');

            $store = $this->storeService->storeinfo($slug);
            if ($store) {
                return $this->successResponse($store, 'Store data found successfully');
            } else {
                throw new ItemNotFoundException("Failed to found data");
            }
            return $this->successResponse($store, 'Store data found successfully');
        } catch (ItemNotFoundException $e) {
            return $this->notFoundResponse($e->getMessage());
        } catch (ValidationException $e) {
            return $this->validationErrorResponse($e->getMessage());
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    public function followStore(Request $request)
    {
        try {
            $validation = Validator::make(
                $request->all(),
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
    
            $storeId = $request->input('store_id');
            $followed = $this->storeService->followStore($storeId);
    
            $message = $followed ? 'Store followed successfully' : 'Store unfollowed successfully';
    
            return $this->successResponse(null, $message);
        } catch (ItemNotFoundException $e) {
            return $this->notFoundResponse($e->getMessage());
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }
    
    public function redistributeProducts()
    {
        try {
            $response = $this->storeService->redistributeProducts();

            return $this->successResponse($response, 'Products distributed successfully.');
        } catch (ItemNotFoundException $e) {
            return $this->notFoundResponse($e->getMessage());    
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    public function updateStoreUserEmails()
    {
        try {
            $response = $this->storeService->updateStoreUserEmails();

            return $this->successResponse($response, 'Store User Emails changed successfully.');
        } catch (ItemNotFoundException $e) {
            return $this->notFoundResponse($e->getMessage());    
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }
}
