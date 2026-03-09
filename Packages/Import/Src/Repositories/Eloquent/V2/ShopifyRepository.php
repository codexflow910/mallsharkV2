<?php

namespace Packages\Import\Repositories\Eloquent\V2;

use Packages\Core\Models\V2\category;
use Packages\Import\Models\V2\ProcessTime;
use Packages\Import\Models\V2\ShopifyStore;
use Packages\Import\Models\V2\PendingShopifyOAuth;
use Packages\Import\Contracts\V2\ShopifyRepositoryInterface;
use Packages\Product\Config\ProductConstants;
use Packages\Product\Models\V2\Product;
use Packages\Shipping\Models\V2\ShippingOption;
use Packages\Store\Models\V2\Store;

class ShopifyRepository implements ShopifyRepositoryInterface
{
    public function saveShopDetails($userId, $shopData, $accessToken, $shopUrl, $permissions)
    {
        return ShopifyStore::updateOrCreate(
            ['user_id' => $userId],
            [
                'name' => $shopData['name'],
                'shop_id' => $shopData['id'],
                'currency_code' => $shopData['currencyCode'],
                'email' => $shopData['email'],
                'domain' => $shopData['primaryDomain']['url'],
                'access_token' => $accessToken,
                'status' => true,
                'shop_url' => $shopUrl,
                'auth_type' => 'access_token',
                'product_management' => isset($permissions['product_management']) && $permissions['product_management'] == 1 ? true : false,
                'inventory_management' => isset($permissions['inventory_management']) && $permissions['inventory_management'] == 1 ? true : false,
                'order_synchronization' => isset($permissions['order_synchronization']) && $permissions['order_synchronization'] == 1 ? true : false,
                'discount_promotion_management' => isset($permissions['discount_promotion_management']) && $permissions['discount_promotion_management'] == 1 ? true : false,
                'shipping_fulfillment' => isset($permissions['shipping_fulfillment']) && $permissions['shipping_fulfillment'] == 1 ? true : false,
            ]
        );
    }

    public function getShopByUserId($userId)
    {
        return ShopifyStore::where('user_id', $userId)->first();
    }

    public function getByShopDomain(string $shopDomain)
    {
        return ShopifyStore::where('domain', 'like', "%$shopDomain%")->first();
    }

    public function getShopByAccessToken($accessToken)
    {
        return ShopifyStore::where('access_token', $accessToken)->first();
    }

    public function findShopByAccessTokenOrUser($accessToken = null, $userId = null)
    {
        $shopQuery = ShopifyStore::query();

        if ($accessToken) {
            $shopQuery->where('access_token', $accessToken);
        } elseif ($userId) {
            $shopQuery->where('user_id', $userId);
        }

        return $shopQuery->first();
    }

    public function disconnectShop($shop)
    {
         $shop->status = false;
         $shop->save();
         return true;
    }

    public function getShopifyStoreByUserId($userId)
    {
        return ShopifyStore::where('user_id', $userId)->first();
    }

    public function getStoreByUserId($userId)
    {
        return Store::select('store_id')->where('user_id', '=', $userId)->first()->toArray();
    }

    public function checkExistingShopifyProduct($productId, $storeId)
    {
        return Product::where('shopify_product_id', $productId)->where(function ($query) {
            $query->whereNull('is_deleted')
                ->orWhere('is_deleted', '=', '0');
        })->where('status', '!=', ProductConstants::STATUS_DELETED)
            ->exists();
    }

    public function checkProductImportInProgress($productId, $userId)
    {
        $cacheKey = "shopify_import_processing_{$userId}_{$productId}";
        return \Cache::has($cacheKey);
    }

    public function markProductsAsImporting($productIds, $userId)
    {
        foreach ($productIds as $productId) {
            $cacheKey = "shopify_import_processing_{$userId}_{$productId}";
            // Cache for 1 hour (enough time for import to complete)
            \Cache::put($cacheKey, true, now()->addHours(1));
        }
    }

    public function markProductImportComplete($productId, $userId)
    {
        $cacheKey = "shopify_import_processing_{$userId}_{$productId}";
        \Cache::forget($cacheKey);
    }

    public function markProductImportFailed($productId, $userId)
    {
        $cacheKey = "shopify_import_processing_{$userId}_{$productId}";
        \Cache::forget($cacheKey);
    }

    public function checkSkuExists($storeId, $handle)
    {
        $slug = $this->generateSlug($handle, $storeId);
        return Product::where('store_id', $storeId)
            ->where('slug', $slug)
            ->where('status', '!=', '2')
            ->exists();
    }

    private function generateSlug($str, $storeId)
    {
        $str = strtolower(trim($str));
        $str = preg_replace('/[^a-z0-9-]/', '-', $str);
        $str = preg_replace('/-+/', "-", $str);
        return rtrim($str, '-') . '-' . $storeId;
    }

    public function getParentWithChildCategories()
    {
        return category::with('childCategories')
            ->where('parentCategoryID', 0)
            ->get();
    }

    public function getShippingProfiles()
    {
        $userId = auth()->id();
        $shippingProfiles = ShippingOption::where(function ($query) use ($userId) {
            $query->where('user_id', $userId)->orWhereNull('user_id');
        })->where('delete_status','!=',0)->orderBy('user_id', 'asc')->get();

        $processTime = ProcessTime::whereNull('user_id')->orWhere('user_id', $userId)->get();

        return [
            'shipping_profile' => $shippingProfiles,
            'process_time' => $processTime
        ];
    }

    // ==========================================
    // OAuth Related Methods (for new Shopify apps)
    // ==========================================

    /**
     * Store pending OAuth data during OAuth initialization
     * This data is used when Shopify redirects back with the authorization code
     */
    public function storePendingOAuth($userId, array $data)
    {
        // Delete any existing pending OAuth for this user
        PendingShopifyOAuth::where('user_id', $userId)->delete();

        return PendingShopifyOAuth::create([
            'user_id' => $userId,
            'shop_url' => $data['shop_url'],
            'client_id' => $data['client_id'],
            'client_secret' => $data['client_secret'],
            'state' => $data['state'],
            'permissions' => $data['permissions'] ?? [],
            'expires_at' => now()->addMinutes(15), // OAuth state expires in 15 minutes
        ]);
    }

    /**
     * Retrieve pending OAuth data by state token
     * Only returns if the state hasn't expired
     */
    public function getPendingOAuthByState($state)
    {
        return PendingShopifyOAuth::validState($state)->first();
    }

    /**
     * Delete pending OAuth record after successful completion or on cleanup
     */
    public function deletePendingOAuth($state)
    {
        return PendingShopifyOAuth::where('state', $state)->delete();
    }

    /**
     * Clean up expired pending OAuth records
     * Can be called from a scheduled job
     */
    public function cleanupExpiredPendingOAuth()
    {
        return PendingShopifyOAuth::where('expires_at', '<', now())->delete();
    }

    /**
     * Save shop details with OAuth credentials (for new apps)
     */
    public function saveShopDetailsWithOAuth($userId, $shopData, $accessToken, $shopUrl, $permissions, $clientId, $clientSecret)
    {
        return ShopifyStore::updateOrCreate(
            ['user_id' => $userId],
            [
                'name' => $shopData['name'],
                'shop_id' => $shopData['id'],
                'currency_code' => $shopData['currencyCode'],
                'email' => $shopData['email'],
                'domain' => $shopData['primaryDomain']['url'],
                'access_token' => $accessToken,
                'shop_url' => $shopUrl,
                'client_id' => $clientId,
                'client_secret' => $clientSecret,
                'auth_type' => 'oauth',
                'product_management' => isset($permissions['product_management']) && $permissions['product_management'] == 1 ? true : false,
                'inventory_management' => isset($permissions['inventory_management']) && $permissions['inventory_management'] == 1 ? true : false,
                'order_synchronization' => isset($permissions['order_synchronization']) && $permissions['order_synchronization'] == 1 ? true : false,
                'discount_promotion_management' => isset($permissions['discount_promotion_management']) && $permissions['discount_promotion_management'] == 1 ? true : false,
                'shipping_fulfillment' => isset($permissions['shipping_fulfillment']) && $permissions['shipping_fulfillment'] == 1 ? true : false,
            ]
        );
    }

}