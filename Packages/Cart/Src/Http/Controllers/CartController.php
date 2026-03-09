<?php

namespace Packages\Cart\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Http\Controllers\Controller;
use Packages\Cart\Services\CartService;
use Illuminate\Support\Facades\Validator;
use Packages\Cart\Contracts\BaseApiResponse;
use Illuminate\Support\ItemNotFoundException;
use Packages\Cart\Http\Requests\StoreRequest;
use Packages\Cart\Http\Requests\QuantityRequest;
use Packages\Cart\Http\Requests\CheckQuantityRequest;

class CartController extends Controller
{
    use BaseApiResponse;

    protected $cartService;
    
    public function __construct(CartService $cartService)
    {
        $this->cartService = $cartService;
    }
    
    public function addToCart(StoreRequest $request)
    {
        try {
            $data = $request->all();
            $response = $this->cartService->addToCart($data);

            return $this->successResponse($response, 'Cart added successfully');
        } catch (ItemNotFoundException $e) {
            return $this->notFoundResponse($e->getMessage());
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    public function checkQuantity(CheckQuantityRequest $request)
    {
        try {
            $data = $request->all();
            $response = $this->cartService->checkQuantity($data);

            return $this->successResponse($response, 'Product quantity retreived successfully');
        } catch (ItemNotFoundException $e) {
            return $this->notFoundResponse($e->getMessage());
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    public function increaseQuantity(QuantityRequest $request)
    {
        try {
            $data = $request->all();
            $response = $this->cartService->updateCartQuantity($data);
            
            if (!$response) {
                return $this->errorResponse('Cannot increase quantity.');
            }

            return $this->successResponse($response, 'Product quantity updated successfully');
        } catch (ItemNotFoundException $e) {
            return $this->notFoundResponse($e->getMessage());
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    public function decreaseQuantity(QuantityRequest $request)
    {
        try {
            $data = $request->all();
            $response = $this->cartService->updateCartQuantity($data);

            if (!$response) {
                return $this->errorResponse('Cannot decrease quantity.');
            }

            return $this->successResponse($response, 'Product quantity update successfully');
        } catch (ItemNotFoundException $e) {
            return $this->notFoundResponse($e->getMessage());
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }
    
    public function deleteCart(Request $request)
    {
        try {
            $data = $request->input();

            // Validate the request data
            $validation = Validator::make($data, [
                'cart_id' => 'required|integer|exists:products_cart,cart_id',
            ]);

            if ($validation->fails()) {
                return $this->validationErrorResponse('Validation error', $validation->errors());
            }

            $response = $this->cartService->deleteCart($data['cart_id']);

            return $this->deletedResponse('Cart deleted successfully');
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    public function toggleCart(Request $request)
    {
        try {
            $data = $request->input();

            // Validate the request data
            $validation = Validator::make($data, [
                'cart_id' => 'required|integer|exists:products_cart,cart_id',
                'is_check' => 'required|integer|in:0,1',
            ]);

            if ($validation->fails()) {
                return $this->validationErrorResponse('Validation error', $validation->errors());
            }

            $response = $this->cartService->toggleCart($data['cart_id'], $data['is_check']);

            return $this->successResponse($response, 'Cart toggled successfully');
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    public function getCart(Request $request)
    {
        try {
            $data = $request->input();

            // Validate the request data
            $validation = Validator::make($data, [
                 'id' => 'required',
                'guest' => ['required', Rule::in([true, false])],
            ]);

            if ($validation->fails()) {
                return $this->validationErrorResponse('Validation error', $validation->errors());
            }

            $response = $this->cartService->getCart($data);

            return $this->successResponse($response, 'Cart retrieved successfully');
        } catch (ItemNotFoundException  $e) {
            return $this->notFoundResponse($e->getMessage());
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

}