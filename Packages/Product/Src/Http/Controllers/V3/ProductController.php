<?php

namespace Packages\Product\Http\Controllers\V3;

use Exception;
use App\Http\Controllers\Controller;
use Illuminate\Http\Response;
use Illuminate\Support\ItemNotFoundException;
use Illuminate\Validation\UnauthorizedException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;
use Packages\Product\Http\Requests\V3\StoreRequest;
use Packages\Product\Models\V3\Product;
use Packages\Product\Http\Requests\V3\UpdateRequest;
use Packages\Product\Contracts\V3\BaseApiResponse;
use Packages\Product\Repositories\V3\ProductRepository;
use Packages\Product\Services\V3\ProductService;
use Packages\Product\Services\V3\ProductTypes\CustomProductService;
use Packages\Product\Http\Requests\V3\ProductTierRequest;
use Packages\Product\Services\V3\ProductTypes\SimpleProductService;
use Packages\Product\Services\V3\ProductTypes\VariableProductService;
use App\Http\Controllers\ElasticSearchController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Packages\Product\Http\Requests\V3\SyncVariationRequest;
use Packages\Product\Contracts\V3\ProductHelpers;

class ProductController extends Controller
{
    use BaseApiResponse;
    use ProductHelpers;
    protected $simpleProductService;
    protected $variableProductService;
    protected $customProductService;
    private $_esearch;
    protected $productRepository;
    protected $productService;

    public function __construct(ProductRepository $productRepository, SimpleProductService $simpleProductService, VariableProductService $variableProductService, ProductService $productService, CustomProductService $customProductService)
    {
        $this->simpleProductService = $simpleProductService;
        $this->variableProductService = $variableProductService;
        $this->productRepository = $productRepository;
        $this->productService = $productService;
        $this->customProductService = $customProductService;
        $this->_esearch = new ElasticSearchController();
        // $this->middleware('guest')->except('logout');
    }

    public function uploadImage(Request $request)
    {
        if ($request->hasFile('images')) {
            $images = $request->file('images');
            $responses = [];

            foreach ($images as $image) {
                $responses[] = $this->simpleProductService->uploadProcessProductImage($image);
                
            }

            return $this->successResponse($responses, 'Images uploaded successfully', Response::HTTP_OK);
        } else {
            return $this->validationErrorResponse(['images' => 'At least one image is required']);
        }
    }
    public function uploadDescImage(Request $request)
    {
        if ($request->hasFile('images')) {
            $images = $request->file('images');
            $responses = [];

            foreach ($images as $image) {
                $responses[] = $this->simpleProductService->uploadProcessDescImage($image);
            }

            return $this->successResponse($responses, 'Description Images uploaded successfully', Response::HTTP_OK);
        } else {
            return $this->validationErrorResponse(['images' => 'At least one image is required']);
        }
    }


    public function update(StoreRequest $request, $product_id)
    {

        try {
            $data = $request->validated();

            $data['product_id'] = $product_id;
            $productType = ProductHelpers::getProductTypeById($product_id);
            $isCustom = isset($data['is_custom']) ? $data['is_custom'] : false;
            if($productType){
                if($productType != $data['product_type']){
                    if($productType == 'variant'){
                        // Delete existing variations of this product
                        ProductHelpers::deleteVariationsData($product_id);
                    }else{
                        // Delete existing simple product data if changing from simple to variant
                        ProductHelpers::deleteSimpleProductData($product_id);
                    }
                }
            }
            if ($isCustom) {
                $product = match ($data['product_type']) {
                    'simple' => $this->customProductService->updateSimple($data),
                    'variant' => $this->customProductService->updateVariant($data),
                    default => throw new InvalidArgumentException('Invalid product type'),
                };
            } else {
                $product = match ($data['product_type']) {
                    'simple' => $this->simpleProductService->update($data),
                    'variant' => $this->variableProductService->update($data),
                    default => throw new InvalidArgumentException('Invalid product type'),
                };
            }

            if ($product) {
                 $updatedProduct = Product::find($product->product_id);
                $updatedProduct->searchable();
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
            $data = $request->validated();
            $product = null;
            $isCustom = isset($data['is_custom']) ? $data['is_custom'] : false;
            if ($isCustom) {
                 $product = match ($data['product_type']) {
                    'simple' => $this->customProductService->storeSimple($data),
                    'variant' => $this->customProductService->storeVariant($data),
                };
            } else {
                $product = match ($data['product_type']) {
                    'simple' => $this->simpleProductService->store($data),
                    'variant' => $this->variableProductService->store($data),
                    default => throw new InvalidArgumentException('Invalid product type'),
                };
            }

            if ($product) {
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
        try {
            $data = $request->validated();

            $userId = auth()->id();
            $store = ProductHelpers::getAccessibleStore($userId);
            if (!$store) {
                throw new ItemNotFoundException('User does not belong or have access to any store.');
            }

            $store_id = $store->store_id;
            $productId =  $data['product_id'];
            $productStatus = $data['product_status'];
            $notUpdatedProducts = []; // Array to store product IDs that were not updated
            $UpdatedProducts = [];
            foreach ($productId as $id) {
                $product = $this->simpleProductService->get($id);
                $p_status = $product->status;
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
                    } elseif ($product->product_type == 'simple') {
                        $UpdatedProducts[] = $id;
                        $this->simpleProductService->changestatus($id, $productStatus, $store_id);
                    } elseif ($product->product_type == 'variable' || $product->product_type == 'variant') {

                        $UpdatedProducts[] = $id;
                        $this->variableProductService->changestatus($id, $productStatus, $store_id, $p_status);
                    }
                    $product = Product::find($id);
                    if($productStatus == "Active"){
                        $product->searchable();
                    }else{
                        $product->unsearchable();
                    }
                    

                    // Send notification only if product is updated
                    if (in_array($id, $UpdatedProducts)) {
                        $subject = "Your Product <b>{$product->title}</b> status changed to {$productStatus}.";
                        $url = "/product/detail/{$product->slug}";

                        ProductHelpers::pushNotification($product->user_id, $subject, 'seller', $url);
                    }
                }
            }
            $responseData = [
                'updated_products' =>   $UpdatedProducts,
                'not_updated_products' => $notUpdatedProducts
            ];
            return $this->successResponse($responseData, 'Product status update processed', Response::HTTP_OK);
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

    public function productDetail($slug, Request $request)
    {
        try {
            $userId = $request->input('user_id') ?? null;
            $guestUuid = $request->input('guest_user_id') ?? null;

            $productDetailArray = $this->productService->getProductDetail($slug, $userId, $guestUuid);
            
            return $this->successResponse($productDetailArray, 'Product detail retrieved successfully', Response::HTTP_OK);
        } catch (Exception $e) {
            if ($e instanceof ItemNotFoundException) {
                return $this->notFoundResponse($e->getMessage());
            }
            return $this->errorResponse($e->getMessage());
        }
    }
    public function productPreview($slug, Request $request)
    {
        try {
            $userId = $request->input('user_id') ?? null;
            
            $productDetailArray = $this->productService->getProductPreview($slug, $userId);
            
            return $this->successResponse($productDetailArray, 'Product preview retrieved successfully', Response::HTTP_OK);
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

            if ($response == false) {
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

    public function getProductsSlug()
    {
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
            if ($product != null) {

                if (!$product->is_shopify_import) {
                    return $this->errorResponse('Product is not associated with Shopify.', Response::HTTP_BAD_REQUEST);
                }

                if ($product->user_id !== $authUser) {
                    return $this->errorResponse('You are not authorized to perform this action.', Response::HTTP_FORBIDDEN);
                }

                $product->is_shopify_import = false;
                $product->save();

                return $this->successResponse($product, 'Product disassociated from Shopify successfully.', Response::HTTP_OK);
            } else {
                return $this->errorResponse('Product not found.', Response::HTTP_BAD_REQUEST);
            }
        } catch (\Throwable $e) {
            return $this->errorResponse('An unexpected error occurred: ' . $e->getMessage());
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
    //update store products slug
    public function updateProductSlug($storeId) {
        try {
            $response = $this->productservice->updateProductSlug($storeId);

            return $this->successResponse($response, 'Slug updated successfully');
        } catch (Exception $e) {
            return $this->errorResponse('Error updating product slug: ' . $e->getMessage());
        }
    }

    public function assignLabels(Request $request)
    {
        try {
            $validation = Validator::make($request->all(), 
                [
                    'product_id' => 'required|integer|exists:products,product_id',
                ],
            );

            if ($validation->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validation error',
                    'errors' => $validation->errors()
                ], 422);
            }

            $productId = $request->product_id;

            $response = $this->productService->assignLabels($productId);

            return $this->successResponse($response, 'Label assigned successfully');
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }


    public function assignLabelsToStoreProducts(Request $request)
    {
        try {
            $validation = Validator::make($request->all(), [
                'store_id' => 'required|integer|exists:stores,store_id',
            ]);

            if ($validation->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validation error',
                    'errors' => $validation->errors()
                ], 422);
            }

            $storeId = $request->store_id;
            $ids = $this->productService->getAllProductsIdByStoreId($storeId);


            if (empty($ids)) {
                throw new ItemNotFoundException('No products found for this store');
            }

            DB::beginTransaction();

            foreach ($ids as $id) {
                $this->productService->assignLabels($id);
            }

            DB::commit();

            return $this->successResponse($ids, 'Label assigned successfully to all products');
        } catch (ItemNotFoundException $e) {
            DB::rollBack();
            return $this->notFoundResponse($e->getMessage());
        } catch (Exception $e) {
            DB::rollBack();
            return $this->errorResponse($e->getMessage());
        }
    }

    public function assignLabelsToRecentProducts(Request $request)
    {
        try {
            set_time_limit(0);

            $validation = Validator::make($request->all(), [
                'months' => 'nullable|integer|min:1|max:12',
            ]);

            if ($validation->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validation error',
                    'errors' => $validation->errors()
                ], 422);
            }

            $months = $request->input('months', 6);

            $response = $this->productService->assignLabelsToRecentProducts($months);

            return $this->successResponse($response, 'Labels assigned successfully to products with recent orders');
        } catch (ItemNotFoundException $e) {
            return $this->notFoundResponse($e->getMessage());
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
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
    public function rephrase(Request $request)
    {
        $field = $request->input('field');
        $text = $request->input('text');
        $promptCheck = $request->input('prompt');
        $provider = $request->input('provider', 'openai'); 
        
        $rephrased = $this->productService->rephrase($text, $field, auth()->user()->store->store_id, $provider,$promptCheck);
        $result = [
            'ai_data' => $rephrased,
            'text' => $text,
            'field' => $field
        ];
        return $this->successResponse($result, 'Rephrased successfully');
    }
    public function tags(Request $request)
    {
        $desc = $request->input('description');
        $tags = $this->productService->suggestTags($desc, auth()->user()->vendor_id);
        return response()->json(['tags' => $tags]);
    }

    public function storeProductTiers(ProductTierRequest $request) {
        try {
            $data = $request->all();
            $response = $this->productService->storeProductTiers($data);

            return $this->successResponse($response, 'Tiers assigned successfully');
        } catch (Exception $e) {
            return $this->errorResponse('Error assigning tiers: ' . $e->getMessage());
        }
    }

    public function getProductTiers($productId) {
        try {
            $response = $this->productService->getProductTiers($productId);

            if (empty($response)) {
                return $this->notFoundResponse('No tiers found for this productId: ' . $productId);
            }

            return $this->successResponse($response, 'Tiers retrieved successfully');
        } catch (Exception $e) {
            return $this->errorResponse('Error retrieving tiers: ' . $e->getMessage());
        }
    }

    public function syncVariationTiers(SyncVariationRequest $request)
    {
        try {
            $data = $request->all();
            $response = $this->variableProductService->syncVariationTiers($data);
            return $this->successResponse($response, 'Variation tiers synchronized successfully');
        } catch (Exception $e) {
            return $this->errorResponse('Error: ' . $e->getMessage());
        }
    }
}
