<?php
namespace Packages\Import\Services\V4;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Packages\Import\Config\ShopifyConstants;
use Packages\Import\Contracts\V4\ShopifyHelpers;
use Packages\Import\Repositories\Eloquent\V4\ShopifyRepository;
use Packages\Product\Models\V4\Product;
use Packages\Product\Models\V4\ProductVariation;

class ShopifyService
{
    protected $shopifyRepository;
    use ShopifyHelpers;

    public function __construct(ShopifyRepository $shopifyRepository)
    {
        $this->shopifyRepository = $shopifyRepository;
    }
    public function disconnectShop($accessToken = null, $userId = null)
    {
        $shop = $this->shopifyRepository->findShopByAccessTokenOrUser($accessToken, $userId);

        if (!$shop) {
            return false;
        }

        $shopUrl = $shop->shop_url;
        $accessToken = $shop->access_token;

        $webhooks = ShopifyHelpers::webhooksList($shopUrl, $accessToken);
        if (str_starts_with($shopUrl, 'https://')) {
            $shopUrl = preg_replace('#^https?://#', '', $shopUrl);
        }
        if ($webhooks && count($webhooks) > 0) {
            foreach ($webhooks as $webhook) {
                ShopifyHelpers::deleteWebhook($shopUrl, $accessToken, $webhook['id']);
            }
        }

        $this->shopifyRepository->disconnectShop($shop);
        return true;
    }

    public function importAllProducts($userId,$request)
    {
        $shopifyStore = $this->shopifyRepository->getShopifyStoreByUserId($userId);
        if (!$shopifyStore) {
            return ['status' => false, 'message' => 'Shopify not connected'];
        }
        $storeID = $this->shopifyRepository->getStoreByUserId($userId);

        $client = ShopifyHelpers::getShopifyClient($shopifyStore->shop_url, $shopifyStore->access_token);
        $response = ShopifyHelpers::fetchProducts($client,$request);

        if (!$response['status']) {
            return ['status' => false, 'message' => 'Failed to fetch products'];
        }

        $products = $response['data'];
        $categories = $this->shopifyRepository->getParentWithChildCategories();
        $filteredProducts = [];
        $shopifyCategories = [];
        foreach ($products as $product) {
            if ($this->isValidProduct($product, $storeID['store_id'],$request)) {
                
                $filteredProducts[] = $product;
            }
            if (isset($product['productCategory']) && is_object($product['productCategory']) && isset($product['productCategory']->productTaxonomyNode)) {
                $shopifyCategories[] = $product['productCategory']->productTaxonomyNode;
            }
        }

        $uniqueShopifyCategories = array_values(array_map(
            fn($item) => json_decode($item),
            array_unique(array_map('json_encode', $shopifyCategories))
        ));
        if($filteredProducts === [] && $response['pageInfo']['secondCursor'] != null && $response['pageInfo']['hasNextPage'] == true){
            $request->merge(['cursor' => $response['pageInfo']['secondCursor']]);
           return $this->importAllProducts($userId,$request);
        }
        return [
            'status' => true,
            'count' => count($filteredProducts),
            'products' => $filteredProducts,
            'categories' => $categories,
            'page_info' => $response['pageInfo']
        ];
    }
    public function importAllCategories($userId,$request)
    {
        $shopifyStore = $this->shopifyRepository->getShopifyStoreByUserId($userId);
        if (!$shopifyStore) {
            return ['status' => false, 'message' => 'Shopify not connected'];
        }
        $storeID = $this->shopifyRepository->getStoreByUserId($userId);

        $client = ShopifyHelpers::getShopifyClient($shopifyStore->shop_url, $shopifyStore->access_token);
        $categories = ShopifyHelpers::fetchCollectionsWithProducts($client);
        return [
            'status' => true,
            'categories' => $categories,
        ];
    }
    private function isValidProduct($product, $storeId, $request)
    {

        if (empty($product['title'])) {
            return false;
        }
        // if ($request->has('category_id') && isset($request->category_id)) {
        //     if (
        //         !isset($product['productCategory']) ||
        //         !is_object($product['productCategory']) ||
        //         !isset($product['productCategory']->productTaxonomyNode) ||
        //         !isset($product['productCategory']->productTaxonomyNode->id) ||
        //         $product['productCategory']->productTaxonomyNode->id !== 'gid://shopify/ProductTaxonomyNode/' . $request->category_id
        //     ) {
        //         return false;
        //     }
        // }
        if (empty($product['productImages']) || count($product['productImages']) === 0) {
            return false;
        }

        if (!isset($product['variants']) || count($product['variants']) === 0) {
            return false;
        }

        foreach ($product['variants'] as $variant) {
            if (empty($variant['price']) || $variant['price'] < 1 || $variant['price'] > 9999) {
                return false;
            }
            if ($variant['requiresShipping'] === false) {
                return false;
            }
        }
        $productId = preg_replace('/[^0-9]/', '', $product['id']);

        // Check if product already exists in database
        if ($this->shopifyRepository->checkExistingShopifyProduct($productId,$storeId)) {
            return false;
        }

        // Check if product is currently being imported
        if ($this->shopifyRepository->checkProductImportInProgress($productId, \Auth::id())) {
            return false;
        }

        return true;
    }

    public function reduceInventoryForMallsharkOrder($productId, $quantity) {

        $product = Product::find($productId);

        if (!$product || !$product->shopify_inventory_item_id) {
            Log::error("Product not found or missing Shopify inventory item ID", ['product_id' => $productId]);
            return false;
        }

        $shopifyInventoryItemId = $product->shopify_inventory_item_id;
        $shopifyLocationId = config('services.shopify.location_id');

        $shopifyCredentials = $this->shopifyRepository->getShopByUserId(Auth::id());
        if (!$shopifyCredentials) {
            Log::error("Shopify credentials not found in database.");
            return false;
        }

        $response = ShopifyHelpers::updateShopifyInventory($shopifyInventoryItemId, $shopifyLocationId, -$quantity,$shopifyCredentials);
        if (isset($response['errors'])) {
            Log::error("Shopify inventory update failed", ['response' => $response]);
            return false;
        }

        return true;
    }
    public function reduceMallsharkInventoryFromShopifyOrder(array $data,$shopDomain)
    {
        $shopifyCredentials = $this->shopifyRepository->getByShopDomain($shopDomain);
        if (!$shopifyCredentials) {
            Log::error("Shopify credentials not found in database.");
            return false;
        }
        foreach ($data['line_items'] as $item) {
            $quantity = $item['quantity'];
            $variantId = $item['variant_id'];

            $inventoryItemGid = ShopifyHelpers::getInventoryItemIdFromVariantId($variantId, $shopifyCredentials);
            if (!$inventoryItemGid) {
                \Log::warning("Could not find inventory item for variant ID $variantId");
                continue;
            }

            $shopifyInventoryId = isset($inventoryItemGid) ? preg_replace('/[^0-9]/', '', $inventoryItemGid) : null;
            if (!$shopifyInventoryId) {
                \Log::warning("No inventory_item_id found related to variation id", $item);
                continue;
            }

            $variation = DB::table('product_variation')
                ->where('shopify_inventory_item_id', $shopifyInventoryId)
                ->first();
            if ($variation) {
                DB::table('product_variation')
                    ->where('variation_id', $variation->variation_id)
                    ->decrement('current_stock', $quantity);

                \Log::info("Reduced inventory for product variation ID {$variation->variation_id} by $quantity");
                continue;
            }

            $product = DB::table('products')
                ->where('shopify_inventory_item_id', $shopifyInventoryId)
                ->first();

            if ($product) {
                DB::table('products')
                    ->where('product_id', $product->product_id)
                    ->decrement('current_stock', $quantity);

                \Log::info("Reduced inventory for product ID {$product->product_id} by $quantity");
            } else {
                \Log::warning("No product or variation found for inventory_item_id: $shopifyInventoryId");
            }
        }
    }

    public function restoreInventoryForCancelledMallsharkOrder($productId, $variationId, $quantity)
    {
        $shopifyInventoryItemId = null;

        if ($variationId) {
            // Restore for variation
            $variation = ProductVariation::where('variation_id', $variationId)
                ->where('product_id', $productId)
                ->first();

            if (!$variation || !$variation->shopify_inventory_item_id) {
                Log::error("Variation not found or missing Shopify inventory item ID", [
                    'product_id' => $productId,
                    'variation_id' => $variationId,
                ]);
                return false;
            }

            $userId = Product::where('product_id', $variation->product_id)->value('user_id');

            $shopifyInventoryItemId = $variation->shopify_inventory_item_id;
        } else {
            // Restore for simple product
            $product = Product::find($productId);

            if (!$product || !$product->shopify_inventory_item_id) {
                Log::error("Product not found or missing Shopify inventory item ID", [
                    'product_id' => $productId,
                ]);
                return false;
            }

            $userId = $product->user_id;

            $shopifyInventoryItemId = $product->shopify_inventory_item_id;
        }

        $shopifyCredentials = $this->shopifyRepository->getShopByUserId($userId);
        if (!$shopifyCredentials) {
            Log::error("Shopify credentials not found in database.");
            return false;
        }

        // Positive quantity because we are restoring inventory
        $response = ShopifyHelpers::updateShopifyInventory($shopifyInventoryItemId, null, $quantity, $shopifyCredentials);

        if (isset($response['errors'])) {
            Log::error("Shopify inventory restoration failed", [
                'inventory_item_id' => $shopifyInventoryItemId,
                'response' => $response,
            ]);
            return false;
        }

        return true;
    }
}