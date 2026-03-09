<?php

namespace Packages\Product\Http\Controllers;

use Exception;
use App\Http\Controllers\Controller;
use Illuminate\Http\Response;
use Illuminate\Support\ItemNotFoundException;
use Illuminate\Validation\UnauthorizedException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;
use Packages\Product\Http\Requests\StoreRequest;
use Packages\Product\Models\V2\Product;
use Packages\Product\Http\Requests\UpdateRequest;
use Packages\Product\Contracts\BaseApiResponse;
use Packages\Product\Repositories\Eloquent\ProductRepository;
use Packages\Product\Services\ProductTypes\SimpleProductService;
use Packages\Product\Services\ProductTypes\VariableProductService;
use Packages\Product\Services\ProductService;
use App\Http\Controllers\ElasticSearchController;
use Illuminate\Http\Request;
use Packages\Core\Contracts\CoreHelpers;

class ProductController extends Controller
{
    use BaseApiResponse;
    use CoreHelpers;
    protected $simpleProductService;
    protected $variableProductService;
    protected $productService;
    private $_esearch;
    protected $productRepository;

    public function __construct(ProductRepository $productRepository,SimpleProductService $simpleProductService, VariableProductService $variableProductService,ProductService $productService)
    {
        $this->simpleProductService = $simpleProductService;
        $this->variableProductService = $variableProductService;
        $this->productRepository = $productRepository;
        $this->_esearch = new ElasticSearchController();
        $this->productService = $productService;
        // $this->middleware('guest')->except('logout');
    }

    public function uploadImage(Request $request){
        
        if($request->hasFile('image')){
            $image = $request->file('image');
            $response =  $this->simpleProductService->uploadProcessProductImage($image);
            return $this->successResponse($response, 'Image uploaded successfully', Response::HTTP_OK);
        }else{
            return $this->validationErrorResponse(['image' => 'Image is required']);
        }
        
    }
    public function update(StoreRequest $request,$product_id)
    {
        
        try {
            $data = $request->validated();

            $data['product_id'] = $product_id;
            
            $product = match ($data['product_type']) {
                'simple' => $this->simpleProductService->update($data),
                'variant' => $this->variableProductService->update($data),
                default => throw new InvalidArgumentException('Invalid product type'),
            };

            if ($product) {
                $this->_esearch->esMApping($product->product_id);
                $product->searchable();
                return $this->successResponse($product, 'Product updated successfully', Response::HTTP_OK);
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
    }

    public function store(StoreRequest $request)
    {

        try {
// dd($request->all());
            $data = $request->validated();
            $product = match ($data['product_type']) {
                'simple' => $this->simpleProductService->store($data),
                'variant' => $this->variableProductService->store($data),

                default => throw new InvalidArgumentException('Invalid product type'),
            };

            if ($product) {
                $this->_esearch->esMApping($product->product_id);
                $product->searchable();
                return $this->successResponse($product, 'Product created successfully', Response::HTTP_CREATED);
            }
        } catch (Exception $e) {
            
            //check exception type and return reponse based on that using errorResponse method

            if ($e instanceof ItemNotFoundException) {
                return $this->notFoundResponse($e->getMessage());
            }
            if ($e instanceof ValidationException) {
                return $this->validationErrorResponse($e->errors());
            }
            return $this->errorResponse($e->getMessage());
        }
        return $this->successResponse($product, 'Product created successfully', Response::HTTP_CREATED);
    }

    public function show($productId)
    {
        try {
            $product = $this->simpleProductService->get($productId) ?? $this->variableProductService->get($productId);

            if ($product) {
                return $this->successResponse($product, 'Product retrieved successfully', Response::HTTP_OK);
            }
        } catch (Exception $e) {
            if ($e instanceof ItemNotFoundException) {
                return $this->notFoundResponse($e->getMessage());
            }
            return $this->errorResponse($e->getMessage());
        }
    }
     public function changestatus(UpdateRequest  $request)
    { 
        try{
            $data = $request->validated();
            $store =  $this->simpleProductService->getStore();
            $store_id=$store->store_id;
            $productId=  $data['product_id'];
            $productStatus=$data['product_status'];
            $notUpdatedProducts = []; // Array to store product IDs that were not updated
            $UpdatedProducts = []; 
            foreach ($productId as $id) {
            $product = $this->simpleProductService->get($id);
                $p_status=$product->status;
                if ($p_status == 5) {
                    if ($productStatus != "Delete") {
                        $notUpdatedProducts[] = $id;  // Draft Product can status can not be change except delete
                        continue;        
                    }
                }
            if (!empty($product)) {
                // Check if product status is 2,3, 4, or 5
                if ($productStatus !==  "Delete" && in_array($product->status, [2, 3, 4])) {
                    $notUpdatedProducts[] = $id;
                    continue; // Skip to next iteration without updating the status
                }
                elseif ($product->product_type == 'simple') {
                    $UpdatedProducts[] = $id;
                    $this->simpleProductService->changestatus($id, $productStatus, $store_id);
                } elseif ($product->product_type == 'variable' || $product->product_type == 'variant') {
                  
                        $UpdatedProducts[] = $id;
                    $this->variableProductService->changestatus($id, $productStatus, $store_id,$p_status);
                }
                $product = Product::find($id);
                $product->searchable();

                    // Send notification only if product is updated
                    if (in_array($id, $UpdatedProducts)) {
                        $subject = "Your Product <b>{$product->title}</b> status changed to {$productStatus}.";
                        $url = "/product/detail/{$product->slug}";

                        $this->pushNotification($product->user_id, $subject, 'seller', $url);
                    }
            }
                }
                $responseData = [
                    'updated_products' =>   $UpdatedProducts,
                    'not_updated_products' => $notUpdatedProducts
                ];
                return $this->successResponse($responseData, 'Product status update processed', Response::HTTP_OK);
                        }
        catch (Exception $e) {
            if ($e instanceof ItemNotFoundException) {
                return $this->notFoundResponse($e->getMessage());
            }
            if ($e instanceof ValidationException) {
                return $this->validationErrorResponse($e->errors());
            }
            return $this->errorResponse($e->getMessage());
        }
    }

    public function productDetail($slug, Request $request)
    {
        try {

            $userId = $request->input('user_id') ?? null;
            $productDetailArray = $this->simpleProductService->getProductDetail($slug, $userId);

            return $this->successResponse($productDetailArray, 'Product detail retrieved successfully', Response::HTTP_OK);
        } catch (Exception $e) {
            if ($e instanceof ItemNotFoundException) {
                return $this->notFoundResponse($e->getMessage());
            }
            return $this->errorResponse($e->getMessage());
        }
    }

    public function productDetailSeller($slug, Request $request)
    {
        try {
            $userId = $request->input('user_id') ?? null;
            $productDetailArray = $this->simpleProductService->getSellerProductDetail($slug, $userId);

            return $this->successResponse($productDetailArray, 'Product detail retrieved successfully', Response::HTTP_OK);
        } catch (Exception $e) {
            if ($e instanceof ItemNotFoundException) {
                return $this->notFoundResponse($e->getMessage());
            }
            return $this->errorResponse($e->getMessage());
        }
    }

    public function addToFavorite(Request $request)
    {
        try {
            
            $productId = $request->input('product_id');
            if (empty($productId)) {
                throw new InvalidArgumentException('Product ID is required');   
            }

            $response = $this->simpleProductService->toggleFavorite($productId);

            if($response == false){
                return $this->successResponse(null, 'Product removed from favorites successfully', Response::HTTP_OK);
            }
            return $this->successResponse(null, 'Product added to favorites successfully', Response::HTTP_OK);

        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    public function getBuyerFavouriteProducts(Request $request)
    {
        try {
         
            $favorites = $this->simpleProductService->getBuyerFavorites($request->all());

            return $this->successResponse($favorites, 'Favourite products retrieved successfully', Response::HTTP_OK);
        } catch (Exception $e) {
            if ($e instanceof ItemNotFoundException) {
                return $this->notFoundResponse($e->getMessage());
            }
            return $this->errorResponse($e->getMessage());
        }
    }
     public function getProducts()
    {
        try {
         
            $products = $this->simpleProductService->getProducts();

            return $this->successResponse($products, 'Products retrieved successfully', Response::HTTP_OK);
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
    
    public function uploadProductImage(Request $request)
    {
        try {

            if ($request->hasFile('productImage')) {
                $imageObj = $request->file('productImage');
            } else {
                throw new InvalidArgumentException('Product image is required');
            }

            $response = $this->simpleProductService->uploadProductImage($imageObj);
            return $this->successResponse($response, 'Product image uploaded successfully', Response::HTTP_OK);
        } catch (Exception $e) {
            if ($e instanceof ItemNotFoundException) {
                return $this->notFoundResponse($e->getMessage());
            }
            return $this->errorResponse($e->getMessage());
        }
    }
    public function deleteProductImage(Request $request)
    {
        try {
            $validation = Validator::make(
                $request->all(),
                [
                    'edit_product_id' => 'required',
                    'imageName' =>  'required',
                ],
            );
            if ($validation->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validation error',
                    'errors' => $validation->errors()
                ], 422);
            }
            $data = $request->all();

            $response = $this->simpleProductService->deleteProductImage($data);
            if ($response) {
                return $this->successResponse($response, 'Product image deleted successfully', Response::HTTP_OK);
            } else {
                return $this->errorResponse("Image not deleted");
            }
        } catch (ItemNotFoundException $e) {
            return $this->notFoundResponse($e->getMessage());
        } catch (ValidationException $e) {
            return $this->validationErrorResponse($e->getMessage());
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }
    public function getLoveProducts()
    {
        try {

            $response = $this->simpleProductService->getLoveProducts();
            return $this->successResponse($response, 'Love products retrieved successfully', Response::HTTP_OK);
        } catch (Exception $e) {
            if ($e instanceof ItemNotFoundException) {
                return $this->notFoundResponse($e->getMessage());
            }
            return $this->errorResponse($e->getMessage());
        }
    }
    public function getVariationsInfo(Request $request)
    {
        try {

            $data = $request->all();

            $response = $this->variableProductService->getVariationsInfo($data);
            if ($response) {
                return $this->successResponse($response, 'Product get successfully', Response::HTTP_OK);
            } else {
                throw new ItemNotFoundException('Product not found');
            }
        } catch (ItemNotFoundException $e) {
            return $this->notFoundResponse($e->getMessage());
        } catch (ValidationException $e) {
            return $this->validationErrorResponse($e->getMessage());
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    public function updateProductShipping(Request $request)
    {
        try {
            $validation = Validator::make(
                $request->all(),
                [
                    'product_id' => 'required|integer|exists:products,product_id',
                    'shipping_option_id' =>  'required|integer|exists:shipping_options,id',
                ],
            );
            if ($validation->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validation error',
                    'errors' => $validation->errors()
                ], 422);
            }
            $data = $request->all();

            $response = $this->simpleProductService->updateProductShipping($data);

            return $this->successResponse($response, 'Product shipping updated successfully');
        } catch (ItemNotFoundException $e) {
            return $this->notFoundResponse($e->getMessage());
        } catch (ValidationException $e) {
            return $this->validationErrorResponse($e->getMessage());
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    public function getProductsSlug() {
        try {

            return $this->simpleProductService->getProductsSlug();
        } catch (ItemNotFoundException $e) {
            return $this->notFoundResponse($e->getMessage());
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    public function disassociateFromShopify($id)
    {
        try {
            $authUser = auth()->id();
            $product = $this->productRepository->getProduct($id);
            if ($product != null){

                if (!$product->is_shopify_import) {
                    return $this->errorResponse('Product is not associated with Shopify.', Response::HTTP_BAD_REQUEST);
                }

                if ($product->user_id !== $authUser) {
                    return $this->errorResponse('You are not authorized to perform this action.', Response::HTTP_FORBIDDEN);
                }

                $product->is_shopify_import = false;
                $product->save();

                return $this->successResponse($product, 'Product disassociated from Shopify successfully.', Response::HTTP_OK);
            }else{
                return $this->errorResponse('Product not found.', Response::HTTP_BAD_REQUEST);
            }

        } catch (\Throwable $e) {
            return $this->errorResponse('An unexpected error occurred: ' . $e->getMessage());
        }
    }

    public function getProductsTitle() {
        try {
            
            return $this->simpleProductService->getProductsTitle();
        } catch (ItemNotFoundException $e) {
            return $this->notFoundResponse($e->getMessage());
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }
    public function productHeadingSeo($slug){
        try {
            if(!empty($slug)){
                $data = $this->productService->productHeadingSeo($slug);
                if($data){
                    return $this->successResponse($data, 'data Found', Response::HTTP_OK);
                }else{
                    throw new ItemNotFoundException('data not found.');
                }
            }
        } catch (ItemNotFoundException $e) {
            return $this->notFoundResponse($e->getMessage());
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }
}