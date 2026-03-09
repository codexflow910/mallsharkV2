<?php

namespace Packages\Import\Contracts\V2;

interface ShopifyRepositoryInterface
{
    public function saveShopDetails($userId, $shopData, $accessToken, $shopUrl, $permissions);
    public function getShopByUserId($userId);
    public function getByShopDomain(string $shopDomain);
    public function getShopByAccessToken($accessToken);
    public function findShopByAccessTokenOrUser($accessToken = null, $userId = null);
    public function disconnectShop($shop);
    public function getShopifyStoreByUserId($userId);
    public function getStoreByUserId($userId);
    public function checkExistingShopifyProduct($productId,$storeId);
    public function checkProductImportInProgress($productId, $userId);
    public function markProductsAsImporting($productIds, $userId);
    public function markProductImportComplete($productId, $userId);
    public function markProductImportFailed($productId, $userId);
    public function checkSkuExists($storeId, $handle);
    public function getParentWithChildCategories();
    public function getShippingProfiles();
}