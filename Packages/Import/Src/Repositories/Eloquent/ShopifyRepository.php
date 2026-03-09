<?php

namespace Packages\Import\Repositories\Eloquent;

use Packages\Core\Models\category;
use Packages\Import\Models\ProcessTime;
use Packages\Import\Models\ShopifyStore;
use Packages\Import\Contracts\ShopifyRepositoryInterface;
use Packages\Product\Config\ProductConstants;
use Packages\Product\Models\Product;
use Packages\Shipping\Models\ShippingOption;
use Packages\Store\Models\Store;

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
                'shop_url' => $shopUrl,
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

    public function deleteShop($shop)
    {
        return $shop->delete();
    }

    public function getShopifyStoreByUserId($userId)
    {
        return ShopifyStore::where('user_id', $userId)->first();
    }

    public function getStoreByUserId($userId)
    {
        return Store::select('store_id')->where('user_id', '=', $userId)->first()->toArray();
    }

    public function checkExistingShopifyProduct($productId)
    {
        return Product::where('shopify_product_id', $productId)->where(function ($query) {
            $query->whereNull('is_deleted')
                ->orWhere('is_deleted', '=', '0');
        })->where('status', '!=', ProductConstants::STATUS_DELETED)
        ->exists();
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
        })->where('delete_status', '!=', 0)->orderBy('user_id', 'asc')->get();

        $processTime = ProcessTime::whereNull('user_id')->orWhere('user_id', $userId)->get();

        return [
            'shipping_profile' => $shippingProfiles,
            'process_time' => $processTime
        ];
    }

}