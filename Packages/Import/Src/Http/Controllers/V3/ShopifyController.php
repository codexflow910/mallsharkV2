<?php

namespace Packages\Import\Http\Controllers\V3;

use App\Http\Controllers\Controller;

use File;
use Image;
use Packages\Import\Config\ShopifyConstants;
use Packages\Product\Config\ProductConstants;
use Packages\Product\Contracts\V3\ProductHelpers;
use Packages\Core\Models\category;
use Packages\Discount\Models\Discount;
use Packages\Product\Models\V3\{Product,
    Attachments,
    AttachmentSizes,
    CategoryProduct,
    ProductTags,
    ProductVariation};
use Packages\Home\Models\Store;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\ElasticSearchController;
use Packages\Import\Contracts\V3\BaseApiResponse;
use Packages\Import\Contracts\V3\ShopifyHelpers;
use Packages\Import\Http\Requests\V3\ConnectShopifyRequest;
use Packages\Import\Repositories\Eloquent\V3\ShopifyRepository;
use Packages\Import\Services\V3\ShopifyService;
use App\Jobs\ImportProductsJob;
use Illuminate\Support\ItemNotFoundException;
use Illuminate\Validation\ValidationException;
use Packages\Import\Http\Requests\WebhooksValidationRequest;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Packages\Shipping\Models\V3\{ShippingProfile};
use Packages\Import\Services\V3\ShopifyOAuthService;
use Packages\Import\Models\V3\PendingShopifyOAuth;
use Illuminate\Support\Str;
use Packages\Import\Models\V3\ShopifyStore;

class ShopifyController extends Controller
{

    use BaseApiResponse;
    use ShopifyHelpers;
    use ProductHelpers;

    protected $shopifyService;
    protected $shopifyRepository;
    private $_esearch;
    public function __construct(ShopifyService $shopifyService, ShopifyRepository $shopifyRepository)
    {
        $this->shopifyService = $shopifyService;
        $this->shopifyRepository = $shopifyRepository;
        $this->_esearch = new ElasticSearchController();
    }

    public function connectShopify(ConnectShopifyRequest $request)
    {
        $shopUrl = $request->shopUrl;
        $accessToken = $request->accessToken;
        $permissions = $request->permissions;
        $shopData = ShopifyHelpers::getShopDetails($shopUrl, $accessToken);

        if ($shopData) {

            $result = $this->shopifyRepository->saveShopDetails(Auth::id(), $shopData, $accessToken, $shopUrl, $permissions);

            if(!empty($result)) {
                $createOrder = ShopifyHelpers::createShopifyWebhook($shopUrl, $accessToken, 'orders/create', env('base_url').'/api/v2/shopify/update-inventory-on-shopify-order');
                $cancelOrder = ShopifyHelpers::createShopifyWebhook($shopUrl, $accessToken, 'orders/cancelled', env('base_url').'/api/v2/shopify/update-inventory-on-shopify-order-cancel');
                $updateProduct = ShopifyHelpers::createShopifyWebhook($shopUrl, $accessToken, 'products/update', env('base_url').'/api/v2/shopify/update-product-on-shopify-update');
                $updateProductOnDelete = ShopifyHelpers::createShopifyWebhook($shopUrl, $accessToken, 'products/delete', env('base_url').'/api/v2/shopify/update-product-on-shopify-delete');
            }

            return $this->successResponse([
                'shop_id' => $shopData['id'],
                'name' => $shopData['name'],
                'currency' => $shopData['currencyCode'],
                'email' => $shopData['email'],
                'domain' => $shopData['primaryDomain']['url'],
                'Order create webhook' => $createOrder,
                'Order cancel webhook' => $cancelOrder
            ], 'Connected successfully');
        } else{
            return $this->errorResponse('Unable to connect, No store found', Response::HTTP_BAD_REQUEST);
        }

    }

    public function webhooksList(WebhooksValidationRequest $request)
    {
        try {

            $validated = $request->validated();

            $shop = $validated['shop'];
            $accessToken = $validated['accessToken'];

            $webhooks = ShopifyHelpers::webhooksList($shop, $accessToken);

            if ($webhooks && count($webhooks) > 0) {
                return $this->successResponse($webhooks, 'Webhooks fetched successfully');
            } else {
                throw new ItemNotFoundException('No webhooks found');
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

    public function deleteWebhook(WebhooksValidationRequest $request, $id)
    {
        try {
            $validated = $request->validated();

            $shop = $validated['shop'];
            $accessToken = $validated['accessToken'];

            ShopifyHelpers::deleteWebhook($shop, $accessToken, $id);

            return $this->successResponse(true, 'Webhook deleted successfully');

        } catch (\Exception $e) {
            if ($e instanceof ValidationException) {
                return $this->validationErrorResponse($e->errors());
            }
            return $this->errorResponse($e->getMessage());
        }
    }

    public function disconnectShopify(Request $request)
    {
        $accessToken = $request->accessToken;
        $userId = Auth::id();

        $isDeleted = $this->shopifyService->disconnectShop($accessToken, $userId);

        if ($isDeleted) {
            return $this->deletedResponse('Shop disconnected successfully');
        }

        return $this->notFoundResponse('Shop not found');
    }

    public function getShopDetails()
    {
        $shop = $this->shopifyRepository->getShopByUserId(Auth::id());

        if ($shop) {
            return $this->successResponse($shop, 'Shop details retrieved successfully');
        }

        return $this->notFoundResponse('No Shopify store found');
    }

    public function validateAccessToken(Request $request)
    {
        $request->validate([
            'accessToken' => 'required|string',
        ]);

        $shop = $this->shopifyRepository->getShopByAccessToken($request->accessToken);

        if ($shop) {
            return $this->successResponse($shop, 'Valid access token');
        }

        return $this->errorResponse('Invalid access token', Response::HTTP_UNAUTHORIZED);
    }

    public function importAllProducts(Request $request)
    {
        try {
            $user = Auth::user();
            if (!$user) {
                return $this->errorResponse('Unauthorized', Response::HTTP_UNAUTHORIZED);
            }

            $response = $this->shopifyService->importAllProducts($user->user_id, $request);

            if ($response['status']) {
                return $this->successResponse($response, 'Products fetched successfully.');
            }

            return $this->errorResponse($response['message'], Response::HTTP_BAD_REQUEST);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }
    // fetch categories from shopify
    public function importAllCategories(Request $request)
    {
        try {
            $user = Auth::user();
            if (!$user) {
                return $this->errorResponse('Unauthorized', Response::HTTP_UNAUTHORIZED);
            }

            $response = $this->shopifyService->importAllCategories($user->user_id, $request);

            if ($response['status']) {
                return $this->successResponse($response, 'Products fetched successfully.');
            }

            return $this->errorResponse($response['message'], Response::HTTP_BAD_REQUEST);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }
    public function saveImportedProducts(Request $request)
    {
        set_time_limit(2000);
        $shop = $this->shopifyRepository->getShopByUserId(auth()->id());
        if (!isset($shop)) {
            return $this->errorResponse('Shopify not connect, try to connect it again', Response::HTTP_BAD_REQUEST);
        }

        $accessToken = $shop->access_token;
        $shopUrl = $shop->shop_url;
        if (empty($accessToken) || empty($shopUrl)) {
            return $this->errorResponse('Invalid user credentials', Response::HTTP_BAD_REQUEST);
        }

        $client = $this->getShopifyClient($shopUrl, $accessToken);

        if (!isset($request->productsData)) {
            return $this->errorResponse('No product data found', Response::HTTP_BAD_REQUEST);
        }

        $products = json_decode(json_encode($request->productsData));
        if (empty($products)) {
            return $this->errorResponse('Empty product data', Response::HTTP_BAD_REQUEST);
        }
        $userEmail = auth()->user()->email;
        $userId = auth()->id();
        if (sizeof($products) > 5) {
            // Extract product IDs and mark them as importing BEFORE dispatching job
            $productIds = array_map(function($product) {
                return preg_replace('/[^0-9]/', '', $product->product_id);
            }, $products);

            $this->shopifyRepository->markProductsAsImporting($productIds, $userId);

            ImportProductsJob::dispatch($shopUrl, $accessToken, $products, $userEmail,$userId);
            return $this->successResponse202(null, 'Product import started, you will receive the email once its completed');
        } else {
            // Mark products as importing for synchronous processing too
            $productIds = array_map(function($product) {
                return preg_replace('/[^0-9]/', '', $product->product_id);
            }, $products);
            $this->shopifyRepository->markProductsAsImporting($productIds, $userId);

            foreach ($products as $product) {
                if (!empty($product->categoryId)) {
                    $productId = preg_replace('/[^0-9]/', '', $product->product_id);
                    try {
                        DB::beginTransaction();
                        $this->processSingleProduct($client, $product);
                        DB::commit();
                        // Mark as complete after successful import
                        $this->shopifyRepository->markProductImportComplete($productId, $userId);
                    } catch (\Exception $e) {
                        DB::rollBack();
                        // Mark as failed so it can be retried
                        $this->shopifyRepository->markProductImportFailed($productId, $userId);
                        return $this->errorResponse($e->getMessage(), Response::HTTP_BAD_REQUEST);
                    }
                } else {
                    return $this->errorResponse('Category required for adding product', Response::HTTP_BAD_REQUEST);
                }
            }
        }
        return $this->successResponse('', 'Products imported successfully');
    }

    public function processSingleProduct($client, $data, $userId = null)
    {
        // Extract shopify_product_id for early duplicate check
        $shopifyProductId = preg_replace('/[^0-9]/', '', $data->product_id);
        $userID = $userId ?? auth()->id();
        $store = Store::where('user_id', $userID)->first();
        $storeId = $store->store_id ?? 1;

        // Early duplicate check before making API call
        $existingProduct = Product::where('shopify_product_id', $shopifyProductId)
            ->where('store_id', $storeId)->where(function ($query) {
                $query->whereNull('is_deleted')
                    ->orWhere('is_deleted', '=', '0');
                })
            ->where('status', '!=', ProductConstants::STATUS_DELETED)
            ->first();

        if ($existingProduct) {
            \Log::info('Product already exists (early check), skipping import', [
                'shopify_product_id' => $shopifyProductId,
                'existing_product_id' => $existingProduct->product_id
            ]);
            return $existingProduct->product_id;
        }

        $query = $this->buildProductQuery($data->product_id);
        $response = $client->request('POST', ShopifyConstants::API_ENDPOINT, [
            'body' => $query,
        ]);
        $body = json_decode($response->getBody());
        if (!isset($body->data->product)) {
            return false;
        }
        return $this->storeProduct($data, $body->data->product, $userId);
    }

    private function buildProductQuery($productId)
    {
        return '{
            product(id: "' . $productId . '") {
                    id title tags handle status productType customProductType totalVariants
                    description descriptionHtml updatedAt createdAt
                    options(first: 3) { id name values }
                    priceRangeV2 {
                        maxVariantPrice { amount currencyCode }
                        minVariantPrice { amount currencyCode }
                    }
                    images(first: 30) { nodes { url originalSrc transformedSrc(maxWidth:800,maxHeight:800) } }
                    productCategory { productTaxonomyNode { id fullName isRoot name isLeaf } }
                    collections(first:2) { nodes { id title } }
                    variants(first:50) {
                        edges {
                            node {
                            id
                            title
                            sku
                            price
                            displayName
                            availableForSale
                            inventoryQuantity
                            selectedOptions { name value }
                            image {
                                originalSrc
                                transformedSrc(maxWidth:800, maxHeight:800)
                            }
                            inventoryItem {
                                id
                                measurement {
                                weight {
                                    unit
                                    value
                                }
                                }
                                requiresShipping
                            }
                            }
                        }
                        }
            }
        }';
    }

    public function storeProduct($data, $product, $userId = null)
    {
        try{
        $store = new Store();
        $globalProductId = null;
        $userID = isset($userId)  || $userId != null  ? $userId : auth()->id();
        $storeID = $store->select('store_id')->where('user_id', '=', $userID)->first();

        // Extract shopify_product_id to check for duplicates
        $shopifyProductId = preg_replace('/[^0-9]/', '', $product->id);

        // Check if product already exists with this shopify_product_id for this store
        $existingProduct = Product::where('shopify_product_id', $shopifyProductId)
            ->where('store_id', $storeID)->where(function ($query) {
                $query->whereNull('is_deleted')
                    ->orWhere('is_deleted', '=', '0');
                })
            ->where('status', '!=', ProductConstants::STATUS_DELETED)
            ->first();

        if ($existingProduct) {
            \Log::info('Product already exists, skipping import', [
                'shopify_product_id' => $shopifyProductId,
                'existing_product_id' => $existingProduct->product_id
            ]);
            return $existingProduct->product_id;
        }

        if ($product->totalVariants > 0) {
            $variant1 = $product->variants->edges[0]->node;
            $productTitle = $product->title;
            $productPrice = isset($variant1->price) ? $variant1->price : 0;
            $productStatus = $product->status == 'ACTIVE' ? 1 : 0;
            $productSKU = isset($variant1->sku) ? $variant1->sku : null;
            $productDescription = $product->descriptionHtml;
            $productWeightUnit = $variant1->weightUnit ?? null;
            $shopifyInventoryItemId = preg_replace('/[^0-9]/', '', $variant1->inventoryItem->id);
            $shopifyProductId = preg_replace('/[^0-9]/', '', $product->id);

            $tempWeight = isset($variant1->weight) ? round($variant1->weight) : 0;
            $productWeight = $this->convertToPounds($tempWeight, $productWeightUnit);

            $productSizeLength = null;
            $productSizeHeight = null;
            $productSizeWidth = null;
            $productCurrentStock = isset($variant1->inventoryQuantity) ? $variant1->inventoryQuantity : 0;
            $weightType = 'pound';
            $dimentionType = 'inch';
            $productType = $product->totalVariants > 1 ? 'variant' : 'simple';
            $generalShipping = ShippingProfile::where('store_id', $storeID->store_id)
            ->where('is_general', 1)
            ->first();
            $shippingOptionId = $generalShipping->id;

            if (isset($storeID->store_id) && $storeID->store_id != '') {
                $storeId = $storeID->store_id;
                $storeStatus = getStoreInfoById($storeId);
                $store_super_status = $storeStatus[0]->super_status;
                $storeStatus = $storeStatus[0]->store_status;
            } else {
                $storeId = 1;
            }

            $randomKey = rand(1000, 9999);
//            $productSlug = $slug . '-' . $storeId;
            $productSlug = $this->createSlug($userID,$data->categoryId,$randomKey);
            if (!$userID) {
                $userID = 1;
            }
            if ($store_super_status != 1 || $storeStatus != 2) {
                $status = ProductConstants::STATUS_DEACTIVATED;
            } else {
                $status = ProductConstants::STATUS_ACTIVE;
            }
            $productImages = $product->images->nodes;
            $hasImages = count($productImages) > 0;

            // Set status to pending if no images - will be updated by ProcessProductImagesJob
            if (!$hasImages) {
                $status = ProductConstants::STATUS_PENDING;
            }
            $newProduct = new Product();
            $newProduct->user_id = $userID;
            $newProduct->store_id = $storeId;
            $newProduct->title = $productTitle;
            $newProduct->slug = $productSlug;
            $newProduct->description = $productDescription;
            $newProduct->price = $productPrice;
            $newProduct->current_stock = $productCurrentStock;
            $newProduct->sku = $productSKU;
            $newProduct->status = $status;
            $newProduct->weight = $productWeight;
            $newProduct->productSize_length = $productSizeLength;
            $newProduct->productSize_width = $productSizeWidth;
            $newProduct->productSize_height = $productSizeHeight;
            $newProduct->shipping_option_id = $shippingOptionId;
            $newProduct->weight_type = $weightType;
            $newProduct->dimention_type = $dimentionType;
            $newProduct->product_type = $productType;
            $newProduct->product_status = $productStatus;
            $newProduct->maxquantitylimit = 10;

            $newProduct->is_shopify_import = true;
            $newProduct->shopify_inventory_item_id = $shopifyInventoryItemId;
            $newProduct->shopify_product_id = $shopifyProductId;
            $newProduct->algolia_batch = 1;
            // Images will be processed asynchronously by ProcessProductImagesJob
            // Product is saved without images first for faster processing
            $newProduct->save();
            $newProduct->slug = $this->createSlug($userID,$data->categoryId,$newProduct->product_id);

            $generalShippingProfile = $this->getGeneralShippingProfile($storeId);

            if ($generalShippingProfile) {
                $newProduct->shippingProfiles()->sync([$generalShippingProfile->id]);
            }

            
            $newProduct->save();
            if ($newProduct) {
                $newProduct->shippingProfiles()->sync($generalShipping->id);
                $productId = $newProduct->product_id;
                $globalProductId =$productId;
                if (isset($data->categoryId)) {
                    $category = category::find($data->categoryId);
                    $productCategory = new CategoryProduct();
                    $productCategory->product_id = $productId;
                    $productCategory->category_id = $category->id;
                    $productCategory->save();

                    if ($category->parentCategory()->exists()) {
                        $productParentCategory = new CategoryProduct();
                        $productParentCategory->product_id = $productId;
                        $productParentCategory->category_id = $category->parentCategory->id;
                        $productParentCategory->save();
                    }
                }
                if (isset($product->tags) && count($product->tags) > 0) {
                    $tagCounter = 1;
                    foreach ($product->tags as $tag) {
                        if ($tagCounter < 11) {
                            $productTags = new ProductTags();
                            $productTags->product_id = $productId;
                            $productTags->tag_title = $tag;
                            $productTags->save();
                        }
                        $tagCounter++;
                    }
                }
                if ($product->totalVariants > 1) {
                    $vars = [];
                    $options = [];
                    $variantImageMap = []; // Map variant SKU to image URL for ProcessProductImagesJob
                    if (isset($product->options)) {
                        $counter = 1;
                        foreach ($product->options as $item) {
                            $attributeKey = 'attribute' . $counter;
                            $vars[$attributeKey][] = $item->name;

                            $optionKey = 'option' . $counter;
                            $options[] = $optionKey;
                            $counter++;
                        }
                        $counter = 1;
                        $variationImages = array();
                        $productInfo = array();
                        foreach ($product->variants->edges as $variant) {
                            $node = $variant->node;
                            $optionKey = 'option' . $counter;
                            $priceKey = 'price';
                            $skuKey = 'sku';
                            $currentStockKey = 'current_stock';
                            $visibilityKey = 'visibility';
                            $variationImageKey = 'product_variation_image';
                            $inventoryItemId = 'shopify_inventory_item_id';
                            $vars[$priceKey][] = $node->price;
                            $vars[$skuKey][] = $node->sku;
                            $vars[$currentStockKey][] = $node->inventoryQuantity;
                            $vars[$visibilityKey][] = $counter;
                            $vars[$variationImageKey][] = null; // Will be updated by ProcessProductImagesJob
                            $vars[$inventoryItemId][] = isset($node->inventoryItem->id) ? preg_replace('/[^0-9]/', '', $node->inventoryItem->id) : null;
                            foreach ($options as $key => $opt) {
                                $vars[$opt][] = $node->selectedOptions[$key]->value;
                            }

                            // Capture variant-specific image URL from Shopify
                            if (isset($node->image->transformedSrc)) {
                                $variantImageMap[$node->sku] = $node->image->transformedSrc;
                            }

                            // Variation images will be set by ProcessProductImagesJob
                            // For now, set to null and they will be updated asynchronously
                            $productInfo['$product_variation_image'][] = null;
                            $counter++;
                        }
                        $productInfo['productID'] = $productId;
                        $productInfo['store_id'] = $storeId;
                    }
                    if (count($vars['option1']) > 0) {
                        $this->addProductVariation($vars, $productInfo, 'add');
                    }
                }

                // Dispatch image processing job AFTER product is saved
                if ($hasImages) {
                    // Pass variant image mapping to ProcessProductImagesJob
                    $variantImageMap = isset($variantImageMap) ? $variantImageMap : [];
                    \App\Jobs\ProcessProductImagesJob::dispatch($productId, $productImages, $variantImageMap);
                    $paymentLog = new \Monolog\Logger('');
                    $paymentLog->pushHandler(new \Monolog\Handler\StreamHandler(storage_path('logs/shopify.log')), \Monolog\Logger::ERROR);
                    $paymentLog->info('Image processing job dispatched', [
                        'product_id' => $productId,
                        'images_count' => count($productImages),
                        'variant_images_count' => count($variantImageMap)
                    ]);
                }
            }
        }
        }catch(Exception $e){
            dd($e->getMessage());
        }
    }

    public function getAllAttachmentSizes()
    {
        $AllSize = new AttachmentSizes();
        $getDirectorywithSizes = $AllSize->all()->toArray();
        if (!empty($getDirectorywithSizes)) {
            foreach ($getDirectorywithSizes as $key => $value) {
                $directoryName = $value['name'];
                $path = public_path() . '/uploads/' . $directoryName;
                if (!File::exists($path)) {
                    File::makeDirectory($path, 0775, true);
                }
            }
        }
        return $getDirectorywithSizes;
    }

    public function generateRandomString($length)
    {
        $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxy';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    public function getAttachmentId($file, $type = 'digital', $remove = false, $crop = true)
    {
        if ($file) {
            $arrayItems = explode("--_--", $file);
            if (is_array($arrayItems)) {
                $getItem = $arrayItems[1];
                $fileAttr = pathinfo($getItem);

                $nameItemOnly = $fileAttr['filename'];
                $fileExtension = $fileAttr['extension'];
                $attachments = new Attachments();
                $attachments->fileName = $nameItemOnly;
                $attachments->fileExtension = $fileExtension;
                $attachments->save();
                $attachmentId = $attachments->attachment_id;
                if ($attachmentId) {
                    $getRealPath = public_path() . '/uploads/temp/' . $file;
                    $fileName = $nameItemOnly . '_' . $attachmentId . '.' . $fileExtension;

                    switch ($type) {
                        case 'digital':
                            $destinationPath = public_path() . '/uploads/digital';
                            if (!\Illuminate\Support\Facades\File::exists($destinationPath)) {
                                \Illuminate\Support\Facades\File::makeDirectory($destinationPath, 0775, true);
                            }
                            $readFile = file_get_contents($getRealPath);
                            file_put_contents($destinationPath . '/' . $fileName, $readFile);

                            break;
                        case 'image':
                            $getDirectorywithSizes = $this->getAllAttachmentSizes();
                            foreach ($getDirectorywithSizes as $key => $value) {
                                $directoryName = $value['name'];
                                $width = $value['width'];
                                $height = $value['height'];
                                $destinationPath = public_path() . '/uploads/' . $directoryName;
                                if ($width == 0 && $height == 0) {
                                    $readImage = file_get_contents($getRealPath);
                                    file_put_contents($destinationPath . '/' . $fileName, $readImage);
                                } else {
                                    if ($crop) {
                                        if ($value['name'] == 'product') {
                                            $readImage = file_get_contents($getRealPath);

                                            file_put_contents($destinationPath . '/' . $fileName, $readImage);
                                        } else {
                                            $img = \Intervention\Image\Facades\Image::make($getRealPath);
                                            $img->fit($width, $height, function ($constraint) {
                                                $constraint->upsize();
                                            });
                                            $img->save($destinationPath . '/' . $fileName);
                                        }
                                    } else {
                                        $img = \Intervention\Image\Facades\Image::make($getRealPath);
                                        $img->resize($width, null, function ($constraint) {
                                            $constraint->aspectRatio();
                                        })->save($destinationPath . '/' . $fileName);
                                    }
                                }
                            }
                            break;

                        default:
                            break;
                    }
                    if ($remove) {
                        unlink($getRealPath);
                    }
                    return $attachmentId;
                }
            }
        }
    }

    public function addProductVariation($data, $ProductInfo, $Query)
    {
        if ($data) {
            $counterCheck = 1;
            foreach ($data['option1'] as $key => $value) {
                $variationProduct = new ProductVariation();
                $variationProduct->product_id = $ProductInfo['productID'];
                $variationProduct->maxquantitylimit = isset($ProductInfo['maxquantitylimit']) ? $ProductInfo['maxquantitylimit'] : 10;
                if (isset($data['attribute1'])) {
                    $variationProduct->attribute1 = $data['attribute1'][0];
                }
                if (isset($data['attribute2'])) {
                    $variationProduct->attribute2 = $data['attribute2'][0];
                }
                if (isset($data['attribute3'])) {
                    $variationProduct->attribute3 = $data['attribute3'][0];
                }
                if (isset($data['unit1'])) {
                    $variationProduct->unit1 = $data['unit1'][0];
                }
                if (isset($data['unit2'])) {
                    $variationProduct->unit2 = $data['unit2'][0];
                }
                if (isset($data['unit3'])) {
                    $variationProduct->unit3 = $data['unit3'][0];
                }
                if (isset($ProductInfo['$product_variation_image']) && !empty($ProductInfo['$product_variation_image'])) {
                    $variationProduct->product_variation_image = isset($ProductInfo['$product_variation_image'][$key])
                        ? $ProductInfo['$product_variation_image'][$key]
                        : (isset($ProductInfo['feature_image']) ? $ProductInfo['feature_image'] : null);
                }
                if (isset($data['option1'])) {
                    $variationProduct->option1 = $data['option1'][$key];
                }
                if (isset($data['option2'])) {
                    $variationProduct->option2 = $data['option2'][$key];
                }
                if (isset($data['option3'])) {
                    $variationProduct->option3 = $data['option3'][$key];
                }
                $sales = isset($data['sales'][$key]) ? $data['sales'][$key] : 0;
                if (isset($data['variationID'][$key])) {
                    $variationProduct->variation_id = $data['variationID'][$key];
                }
                $variationProduct->check_box_price1 = 1;
                $variationProduct->check_box_price2 = 1;
                $variationProduct->check_box_quantity1 = 1;
                $variationProduct->check_box_quantity2 = 1;
                $variationProduct->check_box_sku1 = 1;
                $variationProduct->check_box_sku2 = 1;

                $price = isset($data['price'][$key]) ? $data['price'][$key] : 0;

                $sku = isset($data['sku'][$key]) ? $data['sku'][$key] : '';
                $currentStock = isset($data['current_stock'][$key]) ? $data['current_stock'][$key] : 0;
                $shopifyInventoryItemId = isset($data['shopify_inventory_item_id'][$key]) ? $data['shopify_inventory_item_id'][$key] : 0;
                $variationProduct->price = $price;
                $variationProduct->sku = $sku;
                $variationProduct->current_stock = $currentStock;
                $variationProduct->sales = $sales;
                $variationProduct->shopify_inventory_item_id =$shopifyInventoryItemId;
                if (isset($data['visibility'])) {
                    if (in_array($counterCheck, $data['visibility'])) {
                        $variationProduct->visibility = 1;
                    } else {
                        $variationProduct->visibility = 0;
                    }
                    $counterCheck++;
                } else {
                    $variationProduct->visibility = 1;
                }
                $discount = Discount::where(['store_id' => $ProductInfo['store_id'], 'status' => 'active', 'state' => 'live'])->first();
                // if (isset($discount) && !empty($discount)) {
                //     if ($discount->discount_value_type == 'percent') {
                //         $discount_value = $data['price'][$key] * (1 - ($discount->discount_value / 100));
                //         if ($discount_value > 0) {
                //             $variationProduct->sale_price = $discount_value;
                //             $variationProduct->discount_percentage = $discount->discount_value;
                //         } else {
                //             $variationProduct->sale_price = $variationProduct->price;
                //             $variationProduct->save();
                //         }
                //     } elseif ($discount->discount_value_type == 'fixed') {
                //         $discount_value = max($data['price'][$key] - $discount->discount_value, 0);
                //         if ($discount_value > 0) {
                //             if ($data['price'][$key] > $discount->discount_value) {
                //                 $discount_percentage = round(floatval(($discount->discount_value / $data['price'][$key]) * 100));
                //             } else {
                //                 $discount_percentage = 100;
                //             }
                //             $variationProduct->sale_price = $discount_value;
                //             $variationProduct->discount_percentage = $discount_percentage;
                //         } else {
                //             $variationProduct->sale_price = $variationProduct->price;
                //             $variationProduct->save();
                //         }
                //     }
                // }
                $variationProduct->save();
            }
            $totalStock = ProductVariation::where('product_id',  $ProductInfo['productID'])->sum('current_stock');
            Product::where('product_id',  $ProductInfo['productID'])->update(['current_stock' => $totalStock]);
        }
    }

    public function orderPlacedInventorySyncToShopify($product_id,$productQuantity,$variation_id = null)
    {
        $productId = $product_id;
        $quantity = $productQuantity;
        $variationId = $variation_id;

        $success = $this->shopifyService->reduceInventoryForMallsharkOrder($productId, $quantity,$variationId);

        if ($success) {
            return $this->successResponse('', 'Inventory updated successfully in Shopify');
        } else {
            return $this->errorResponse('Failed to update inventory in Shopify');
        }
    }

    public function orderPlacedInventorySyncToMallshark(Request $request)
    {
        \Log::debug('🛒 Shopify order webhook received', $request->all());
        $shopDomain = $request->header('X-Shopify-Shop-Domain');

        if (!$shopDomain) {
            \Log::error('Missing X-Shopify-Shop-Domain in webhook headers.');
        }
        $data = $request->all();
        try {
            $this->shopifyService->reduceMallsharkInventoryFromShopifyOrder($data,$shopDomain);
            return $this->successResponse('', 'Inventory updated successfully in mallShark');
        } catch (\Throwable $e) {
            \Log::error('❌ Shopify Order Sync Failed', [
                'error' => $e->getMessage(),
                'order' => $data
            ]);
            return $this->errorResponse('Inventory sync failed.');
        }
    }

    public function orderCancelledInventorySyncToMallshark(Request $request)
    {
        \Log::debug('🚫 Shopify order cancellation webhook received', $request->all());
        $shopDomain = $request->header('X-Shopify-Shop-Domain');

        if (!$shopDomain) {
            \Log::error('Missing X-Shopify-Shop-Domain in webhook headers.');
        }

        $data = $request->all();

        try {
            $this->shopifyService->restoreMallsharkInventoryFromShopifyOrderCancellation($data, $shopDomain);
            return $this->successResponse('', 'Inventory restored successfully in Mallshark');
        } catch (\Throwable $e) {
            \Log::error('❌ Shopify Order Cancellation Sync Failed', [
                'error' => $e->getMessage(),
                'order' => $data
            ]);
            return $this->errorResponse('Inventory restore failed.');
        }
    }

    public function restoreInventoryToShopifyOnOrderCancel($productId,$quantity,$variationId = '')
    {

        $success = $this->shopifyService->restoreInventoryForCancelledMallsharkOrder($productId, $variationId, $quantity);

        if ($success) {
            return $this->successResponse('', 'Inventory restored successfully in Shopify');
        } else {
            return $this->errorResponse('Failed to restore inventory in Shopify');
        }
    }

    public function updateImportedProducts(Request $request)
    {
        \Log::debug('🛒 Shopify product update webhook received',$request->all());
        $shopDomain = $request->header('X-Shopify-Shop-Domain');
        $shop = $this->shopifyRepository->getByShopDomain($shopDomain);

        if (!isset($shop)) {
            \Log::debug('🛒 This Shopify store not connected with any MallShark store');
            return $this->errorResponse('This Shopify store not connected with any MallShark store', Response::HTTP_BAD_REQUEST);
        }
        $accessToken = $shop->access_token;
        $shopUrl = $shop->shop_url;
        if (empty($accessToken) || empty($shopUrl)) {
            return $this->errorResponse('Access Token or shop url not found', Response::HTTP_BAD_REQUEST);
        }

        $client = $this->getShopifyClient($shopUrl, $accessToken);

        try {
            $this->processProductUpdate($client, $request->all());
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), Response::HTTP_BAD_REQUEST);
        }

        return $this->successResponse('', 'Products imported successfully');
    }

    public function processProductUpdate($client, $data)
    {
        $query = $this->buildProductQuery($data['admin_graphql_api_id']);
        $response = $client->request('POST', ShopifyConstants::API_ENDPOINT, [
            'body' => $query,
        ]);
        $body = json_decode($response->getBody());

        if (!isset($body->data->product)) {
            return false;
        }
        $productData=$body->data->product;
        $this->updateShopifyProduct($productData);
    }

    public function updateShopifyProduct($data)
    {
        $variant1 = $data->variants->edges[0]->node;
        $productImages = $data->images->nodes;
        $shopifyInventoryItemId = preg_replace('/[^0-9]/', '', $variant1->inventoryItem->id);
        try {
            $product = Product::where('shopify_inventory_item_id', $shopifyInventoryItemId)->where(function ($query) {
                $query->whereNull('is_deleted')
                    ->orWhere('is_deleted', '=', '0');
            })
//                ->where('status', ProductConstants::STATUS_ACTIVE)
                ->first();

            if (!$product) {
                foreach ($data->variants->edges as $variantEdge) {
                    $variantNode = $variantEdge->node;
                    $inventoryItemId = preg_replace('/[^0-9]/', '', $variantNode->inventoryItem->id);

                    $variation = ProductVariation::where('shopify_inventory_item_id', $inventoryItemId)->first();
                    if ($variation) {
                        $product = Product::where('product_id', $variation->product_id)->where(function($query) {
                            $query->whereNull('is_deleted')
                                ->orWhere('is_deleted', '=', 0);
                        })
//                            ->where('status',ProductConstants::STATUS_ACTIVE)
                            ->first();
                        break;
                    }
                }
            }

            if (!$product) {
                \Log::warning("⚠️ Product not found in Mallshark for Shopify inventory item ID: " . $shopifyInventoryItemId);
                return $this->errorResponse("Product not found");
            }
            DB::beginTransaction();
            $this->updateProductDetails($product, $data,$variant1);

            $variationGallery = $this->updateProductImages($product, $productImages);

            if (isset($data) && $data->totalVariants > 1){
                \Log::info('✅ Updating product variants');
                $this->updateProductVariants($product, $data->variants->edges, $data->options, $variationGallery );
            }

            if ($product){
                $product = Product::find($product->product_id);
                $product->searchable();
            }

            DB::commit();
            \Log::info('✅ Product updated successfully', ['product_id' => $product->product_id]);
            return $this->successResponse('', 'Product updated successfully in MallShark');
        } catch (\Throwable $e) {
            DB::rollBack();
            \Log::error('❌ Product Update Failed', [
                'error' => $e->getMessage(),
                'payload' => $data
            ]);
            return $this->errorResponse('Product Update Failed: ' . $e->getMessage());
        }
    }

    private function updateProductDetails($product, $data,$variant1)
    {
        $storeId = $product->store_id;
        $userId = $product->user_id;
        $firstVariant = $variant1;
        $slug = $product->slug; // Keep existing slug to avoid breaking links - slug will only be updated on product title change, handled in a separate function

        $storeStatus = getStoreInfoById($storeId);
        $store_super_status = $storeStatus[0]->super_status;
        $storeStatus = $storeStatus[0]->store_status;
        $status = ($store_super_status == 1 && $storeStatus == 2) ? '1' : '0';
        $totalQuantity = array_sum(array_map(function ($variant) {
            return $variant->node->inventoryQuantity;
        }, $data->variants->edges));

        $product->slug = $slug;
        $product->title = $data->title;
        $product->description = $data->descriptionHtml;
        $product->price = $firstVariant->price ?? 0;
        $product->current_stock = $totalQuantity;
        $product->sku = $firstVariant->sku ?? null;
        \Log::debug('📸 Product Status', [
            'status' => $data->status
        ]);
        if ($data->status == 'ARCHIVED'){
            $product->status = ProductConstants::STATUS_DEACTIVATED;
        }
        if ($data->status == 'DRAFT'){
            $product->status = ProductConstants::STATUS_DEACTIVATED;
        }
        if ($data->status == 'ACTIVE'){
            $product->status = ProductConstants::STATUS_ACTIVE;
        }
//        $product->status = count($data->images->nodes) > 0 ? $status : '3';
        $product->weight = $this->convertToPounds(round($firstVariant->weight ?? 0), $firstVariant->weightUnit ?? 'KILOGRAMS');
        $product->product_type = $data->totalVariants > 1 ? 'variant' : 'simple';
        $product->updated_at = now();

        $discount = Discount::where(['store_id' => $storeId, 'type' => 'sale', 'status' => 'active', 'state' => 'live'])->first();
        if ($discount) {
            if ($discount->discount_value_type == 'percent') {
                $discount_value = $firstVariant->price * ($discount->discount_value / 100);
                $sale_price = $firstVariant->price - $discount_value;
                if ($sale_price > 0) {
                    $product->on_sale = true;
                    $product->sale_price = $sale_price;
                    $product->discount_id = $discount->id;
                    $product->discount_percentage = $discount->discount_value;
                } else {
                    $product->sale_price = $firstVariant->price;
                    $product->on_sale = false;
                }
            } elseif ($discount->discount_value_type == 'fixed') {
                $discount_value = $firstVariant->price - $discount->discount_value;
                if ($discount_value > 0) {
                    $discount_percentage = $firstVariant->price > 0 ? round(($discount->discount_value / $firstVariant->price) * 100) : 0;
                    $product->on_sale = true;
                    $product->sale_price = $discount_value;
                    $product->discount_percentage = $discount_percentage;
                    $product->discount_id = $discount->id;
                } else {
                    $product->sale_price = $firstVariant->price;
                    $product->on_sale = false;
                }
            }
            \Log::debug('📸 Discount applied on updated product', [
                'discount Id' => $discount->id,
                'Store Id' => $storeId,
                'Product Id' => $product->product_id
            ]);
        }

        ProductTags::where('product_id', $product->product_id)->delete();
        if (isset($data->tags) && count($data->tags) > 0) {
            $tagCounter = 1;
            foreach ($data->tags as $tag) {
                if ($tagCounter < 11) {
                    $productTags = new ProductTags();
                    $productTags->product_id = $product->product_id;
                    $productTags->tag_title = $tag;
                    $productTags->save();
                }
                $tagCounter++;
            }
        }

        $product->save();
    }

    private function updateProductVariants($product, array $variants, array $options, array $variationGallery)
    {
        $existingVariants = ProductVariation::where('product_id', $product->product_id)
            ->pluck('shopify_inventory_item_id')
            ->toArray();
        $incomingVariantIds = array_map(function ($variant) {
            return preg_replace('/[^0-9]/', '', $variant->node->inventoryItem->id)  ?? null;
        }, $variants);

        $incomingVariantIds = array_filter($incomingVariantIds);

        $variantsToDelete = array_diff($existingVariants, $incomingVariantIds);
        ProductVariation::where('product_id', $product->product_id)
            ->whereIn('shopify_inventory_item_id', $variantsToDelete)
            ->delete();

        $vars = [
            'price' => [],
            'sku' => [],
            'current_stock' => [],
            'visibility' => [],
            'product_variation_image' => [],
            'shopify_inventory_item_id' => [],
        ];
        $optionKeys = [];
        $productInfo = [
            'productID' => $product->product_id,
            'store_id' => $product->store_id,
            'maxquantitylimit' => 10,
        ];

        // Map options
        $counter = 1;
        $option = [];
        foreach ($options as $item) {
            $attributeKey = 'attribute' . $counter;
            $vars[$attributeKey][] = $item->name;
            $optionKey = 'option' . $counter;
            $option[] = $optionKey;
            $counter++;
        }
        // Map variants
        $variationImages = [];
        foreach ($variants as $index => $variant) {
            $node = $variant->node;
            $vars['price'][] = $node->price;
            $vars['sku'][] = $node->sku ?? null;
            $vars['current_stock'][] = $node->inventoryQuantity ?? 0;
            $vars['visibility'][] = $index + 1;
            $vars['shopify_inventory_item_id'][] = isset($node->inventoryItem->id) ? preg_replace('/[^0-9]/', '', $node->inventoryItem->id) : null;;
            foreach ($option as $key => $opt) {
                $vars[$opt][] = $node->selectedOptions[$key]->value;
            }
            $src = isset($node->image->transformedSrc) ? $node->image->transformedSrc : null;
            $value = null;
            if ($src) {
                $imageName = pathinfo($src, PATHINFO_FILENAME);
                \Log::debug('📸 Variant image lookup', [
                    'src' => $src,
                    'imageName' => $imageName,
                    'variationGallery' => $variationGallery
                ]);
                $value = $variationGallery[$imageName] ?? null;
            }
            $vars['product_variation_image'][] = $value ?: $product->feature_image;
        }
        // Update or add variants
        if (!empty($vars['option1'])) {
            $this->updateProductVariation($vars, $productInfo, 'update');
        }
    }

    private function updateProductVariation($data, $productInfo, $query)
    {
        if ($data) {
            $counterCheck = 1;
            foreach ($data['option1'] as $key => $value) {
                $variation = ProductVariation::where('product_id', $productInfo['productID'])
                    ->where('shopify_inventory_item_id', $data['shopify_inventory_item_id'][$key])
                    ->first();

                if (!$variation) {
                    $variation = new ProductVariation();
                    $variation->product_id = $productInfo['productID'];
                }

                $variation->maxquantitylimit = $productInfo['maxquantitylimit'] ?? 10;
                if (isset($data['attribute1'])) {
                    $variation->attribute1 = $data['attribute1'][0];
                }
                if (isset($data['attribute2'])) {
                    $variation->attribute2 = $data['attribute2'][0];
                }
                if (isset($data['attribute3'])) {
                    $variation->attribute3 = $data['attribute3'][0];
                }
                $variation->option1 = $data['option1'][$key] ?? null;
                $variation->option2 = $data['option2'][$key] ?? null;
                $variation->option3 = $data['option3'][$key] ?? null;
                $variation->price = $data['price'][$key] ?? 0;
                $variation->sku = $data['sku'][$key] ?? '';
                $variation->current_stock = $data['current_stock'][$key] ?? 0;
                $variation->shopify_inventory_item_id = $data['shopify_inventory_item_id'][$key] ?? null;
                $variation->visibility = in_array($counterCheck, $data['visibility']) ? 1 : 0;
                $variation->product_variation_image = $data['product_variation_image'][$key] ?? $productInfo['feature_image'] ?? null;

                $discount = Discount::where(['store_id' => $productInfo['store_id'], 'status' => 'active', 'state' => 'live'])->first();
                if ($discount) {
                    if ($discount->discount_value_type == 'percent') {
                        $discount_value = $variation->price * (1 - ($discount->discount_value / 100));
                        $variation->sale_price = $discount_value > 0 ? $discount_value : $variation->price;
                        $variation->discount_percentage = $discount->discount_value;
                    } elseif ($discount->discount_value_type == 'fixed') {
                        $discount_value = max($variation->price - $discount->discount_value, 0);
                        if ($discount_value > 0) {
                            $variation->sale_price = $discount_value;
                            $variation->discount_percentage = $variation->price > 0 ? round(($discount->discount_value / $variation->price) * 100) : 0;
                        } else {
                            $variation->sale_price = $variation->price;
                        }
                    }
                }

                $variation->save();
                $counterCheck++;
            }

            // Update total stock
            $totalStock = ProductVariation::where('product_id', $productInfo['productID'])->sum('current_stock');
            Product::where('product_id', $productInfo['productID'])->update(['current_stock' => $totalStock]);
        }
    }

    private function updateProductImages($product, array $images)
    {
        $destinationPath = public_path() . '/uploads/';
        $tempDestinationPath = public_path() . '/uploads/temp/';
        $getDirectorywithSizes = $this->getAllAttachmentSizes();
        $newImages = [];
        $variationGallery = [];

        if (!File::exists($destinationPath)) {
            File::makeDirectory($destinationPath, 0775, true);
        }

        // Retrieve and delete existing images
//        $existingGallery = $product->gallery ? unserialize($product->gallery) : [];
//        $existingFeatureImage = $product->feature_image;
//        $existingAttachments = array_unique(array_merge($existingGallery, $existingFeatureImage ? [$existingFeatureImage] : []));
//
//        foreach ($existingAttachments as $attachmentId) {
//            // Assume getAttachmentId returns the filename for an attachment ID
//            $fileName = $this->getFileNameFromAttachmentId($attachmentId); // Implement this based on your getAttachmentId logic
//            if ($fileName) {
//                foreach ($getDirectorywithSizes as $value) {
//                    $directoryName = $value['name'];
//                    $filePath = $destinationPath . $directoryName . '/' . $fileName;
//                    if (File::exists($filePath)) {
//                        File::delete($filePath);
//                        Log::info('🗑️ Deleted old image', ['file' => $filePath]);
//                    }
//                }
//            }
//        }
//
//        // Clear existing image references in the database
//        $product->feature_image = null;
//        $product->gallery = null;
//        $product->save();

        foreach ($images as $image) {
            $src =  $image->transformedSrc;
            if (!filter_var($src, FILTER_VALIDATE_URL)) {
                \Log::warning('⚠️ Invalid image URL skipped', ['url' => $src]);
                continue;
            }
            $originalFileName = pathinfo($src, PATHINFO_FILENAME);
            $nameOnly = preg_replace('/\..+$/', '', $originalFileName);
            $extension = explode('?', pathinfo($src, PATHINFO_EXTENSION))[0];
            $fileName = $this->generateRandomString(5) . '--_--' . $nameOnly . '.' . $extension;

            try {
                $client = new Client();
                $response = $client->get($src);
                $tempPath = $tempDestinationPath . $fileName;
                file_put_contents($tempPath, $response->getBody());
                \Log::info('📥 Saved image to temp', ['path' => $tempPath]);

                foreach ($getDirectorywithSizes as $value) {
                    $directoryName = $value['name'];
                    $resizeDir = $destinationPath . $directoryName . '/';

                    $path = $resizeDir . $fileName;
                    $width = $value['width'];
                    $height = $value['height'];

                    if ($width == 0 && $height == 0) {
                        file_put_contents($path, file_get_contents($tempPath));
                    } else {
                        Image::make($tempPath)
                            ->resize($width, null, function ($constraint) {
                                $constraint->aspectRatio();
                            })
                            ->save($path);
                    }
                }

                $newImages[$originalFileName] = $fileName;
                if (file_exists($tempPath)) {
                    $variationGallery[$originalFileName] = $this->getAttachmentId($fileName, 'image', false);
                } else {
                    \Log::error('❌ Temp file missing before getAttachmentId', [
                        'tempPath' => $tempPath,
                        'fileName' => $fileName
                    ]);
                }
                if (File::exists($tempPath)) {
                    File::delete($tempPath);
                }
            } catch (\Exception $e) {
                \Log::error('❌ Failed to process image', ['fileName' => $fileName,'url' => $src, 'error' => $e->getMessage()]);
            }
        }

        $productGallery = [];
        $productFeatureImage = null;
        $counter = 0;

        foreach ($newImages as $key => $image) {
            $attachment = $variationGallery[$key] ?? null;
            if ($attachment) {
                if ($counter === 0) {
                    $productFeatureImage = $attachment;
                }
                $productGallery[] = $attachment;
                $counter++;
            }
        }

        if ($productFeatureImage) {
            $product->feature_image = $productFeatureImage;
        }
        if (!empty($productGallery)) {
            $product->gallery = serialize($productGallery);
        }

        $product->save();

        \Log::info('🖼️ Updated product images', ['product_id' => $product->product_id, 'gallery' => $productGallery]);

        return $variationGallery;
    }

    public function updateDeletedProducts(Request $request)
    {
        \Log::debug('🛒 Shopify product delete webhook received',$request->all());
        $shopDomain = $request->header('X-Shopify-Shop-Domain');
        $shop = $this->shopifyRepository->getByShopDomain($shopDomain);

        if (!isset($shop)) {
            \Log::debug('🛒 This Shopify store not connected with any MallShark store');
            return $this->errorResponse('This Shopify store not connected with any MallShark store', Response::HTTP_BAD_REQUEST);
        }
        $accessToken = $shop->access_token;
        $shopUrl = $shop->shop_url;
        if (empty($accessToken) || empty($shopUrl)) {
            return $this->errorResponse('Access Token or shop url not found', Response::HTTP_BAD_REQUEST);
        }

        $client = $this->getShopifyClient($shopUrl, $accessToken);

        try {
            $this->updateShopifyDeletedProduct($request->all());
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), Response::HTTP_BAD_REQUEST);
        }

        return $this->successResponse('', 'Products imported successfully');
    }

    public function updateShopifyDeletedProduct($data)
    {
        try {
            $product = Product::where('shopify_product_id', $data['id'])->where(function ($query) {
                $query->whereNull('is_deleted')
                    ->orWhere('is_deleted', '=', '0');
            })->first();

            if (!$product) {
                \Log::warning("⚠️ Product not found in Mallshark for Shopify product ID: " . $data['id']);
                return $this->errorResponse("Product not found");
            }
            DB::beginTransaction();
            $product->status = ProductConstants::STATUS_DEACTIVATED;
            $product->save();

            if (isset($product) && isset($product->product_id) && $product->product_id != null){
                $product->searchable();
                $this->_esearch->esMApping($product->product_id);
            }

            DB::commit();
            \Log::info('✅ Product updated successfully', ['product_id' => $product->product_id]);
            return $this->successResponse('', 'Product updated successfully in MallShark');
        } catch (\Throwable $e) {
            DB::rollBack();
            \Log::error('❌ Product Update Failed', [
                'error' => $e->getMessage(),
                'payload' => $data
            ]);
            return $this->errorResponse('Product Update Failed: ' . $e->getMessage());
        }
    }

    /**
     * Initialize OAuth flow for new Shopify apps (post-January 2026)
     * Step 1: Generate authorization URL and store pending OAuth state
     */
    public function oauthInit(Request $request)
    {
        try {
            $request->validate([
                'shopUrl' => 'required|string',
                'client_id' => 'required|string',
                'client_secret' => 'required|string',
                'permissions' => 'sometimes|array',
            ]);

            $userId = Auth::id();
            if (!$userId) {
                return $this->errorResponse('Unauthorized', Response::HTTP_UNAUTHORIZED);
            }

            // Generate unique state token for CSRF protection
            $state = Str::random(40);

            // Build callback URL
            $redirectUri = env('ADMIN_URL', 'https://admin.mallshark.com') . '/connect-shopify';
            // Store pending OAuth data (expires in 15 minutes)
            $this->shopifyRepository->storePendingOAuth($userId, [
                'shop_url' => $request->shopUrl,
                'client_id' => $request->client_id,
                'client_secret' => $request->client_secret,
                'state' => $state,
                'permissions' => $request->permissions,
            ]);

            // Generate authorization URL
            $oauthService = new ShopifyOAuthService();
            $authUrl = $oauthService->getAuthorizationUrl(
                $request->shopUrl,
                $request->client_id,
                $redirectUri,
                $state
            );

            return $this->successResponse([
                'auth_url' => $authUrl,
                'state' => $state,
            ], 'Redirecting auth_url to authorize the app in Shopify');

        } catch (ValidationException $e) {
            return $this->validationErrorResponse($e->errors());
        } catch (\Exception $e) {
            \Log::error('OAuth init failed', ['error' => $e->getMessage()]);
            return $this->errorResponse('Failed to initialize OAuth: ' . $e->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Handle OAuth callback from Shopify
     * Step 2: Exchange authorization code for access token
     */
    public function oauthCallback(Request $request)
    {
        $code = $request->query('code');
        $state = $request->query('state');
        $shop = $request->query('shop');

        // Validate required parameters
        if (!$code || !$state) {
            \Log::error('OAuth callback missing parameters', ['code' => $code, 'state' => $state]);
            return $this->errorResponse('Missing required parameters', Response::HTTP_BAD_REQUEST);
        }

        // Retrieve pending OAuth data using state token
        $pending = $this->shopifyRepository->getPendingOAuthByState($state);
        if (!$pending) {
            \Log::error('OAuth callback invalid or expired state', ['state' => $state]);
            return $this->errorResponse('Invalid or expired OAuth state', Response::HTTP_BAD_REQUEST);
        }
        // Exchange code for access token
        $oauthService = new ShopifyOAuthService();
        $tokenData = $oauthService->exchangeCodeForToken(
            $pending->shop_url,
            $pending->client_id,
            $pending->client_secret,
            $code
        );
        if (!$tokenData || !isset($tokenData['access_token'])) {
            \Log::error('OAuth token exchange failed', ['shop' => $pending->shop_url]);
            return $this->errorResponse('Failed to obtain access token from Shopify', Response::HTTP_BAD_REQUEST);
        }

        $accessToken = $tokenData['access_token'];
        // Get shop details to verify the token works
        $shopData = ShopifyHelpers::getShopDetails($pending->shop_url, $accessToken);

        if (!$shopData) {
            \Log::error('OAuth could not fetch shop details', ['shop' => $pending->shop_url]);
            return $this->errorResponse('Failed to verify shop details with access token', Response::HTTP_BAD_REQUEST);
        }

        // Save shop with OAuth credentials
        $this->shopifyRepository->saveShopDetailsWithOAuth(
            $pending->user_id,
            $shopData,
            $accessToken,
            $pending->shop_url,
            $pending->permissions ?? [],
            $pending->client_id,
            $pending->client_secret
        );

        // Create webhooks for order and product sync
        try {
            $baseUrl = env('APP_URL', 'https://devapi.mallshark.net');
            ShopifyHelpers::createShopifyWebhook($pending->shop_url, $accessToken, 'orders/create', $baseUrl . '/api/v3/shopify/update-inventory-on-shopify-order');
            ShopifyHelpers::createShopifyWebhook($pending->shop_url, $accessToken, 'orders/cancelled', $baseUrl . '/api/v3/shopify/update-inventory-on-shopify-order-cancel');
            ShopifyHelpers::createShopifyWebhook($pending->shop_url, $accessToken, 'products/update', $baseUrl . '/api/v3/shopify/update-product-on-shopify-update');
            ShopifyHelpers::createShopifyWebhook($pending->shop_url, $accessToken, 'products/delete', $baseUrl . '/api/v3/shopify/update-product-on-shopify-delete');
        } catch (\Exception $e) {
            \Log::warning('OAuth webhooks creation failed (non-critical)', ['error' => $e->getMessage()]);
            return $this->errorResponse($e->getMessage(), Response::HTTP_BAD_REQUEST);
            // Continue anyway - webhooks can be created later
        }
        ShopifyStore::where('shop_url', $pending->shop_url)->update(['status' => 1]);
        // Clean up pending OAuth record
        $this->shopifyRepository->deletePendingOAuth($state);

        \Log::info('OAuth flow completed successfully', [
            'user_id' => $pending->user_id,
            'shop' => $shopData['name'] ?? $pending->shop_url,
        ]);

        return $this->successResponse([
            'token' => $accessToken,
        ], 'Shopify store connected successfully');
    }
}