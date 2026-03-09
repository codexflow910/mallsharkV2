<?php

namespace Packages\Import\Contracts;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Auth;
use Packages\Import\Repositories\Eloquent\ShopifyRepository;
use Packages\Import\Config\ShopifyConstants;
use Illuminate\Support\Facades\Http;

trait ShopifyHelpers
{
    public static function getShopDetails($shopUrl, $accessToken)
    {
        try {
            $client = new Client([
                'base_uri' => $shopUrl,
                'headers' => [
                    'Content-Type' => 'application/graphql',
                    'X-Shopify-Access-Token' => $accessToken,
                    'X-GraphQL-Cost-Include-Fields' => true
                ]
            ]);

            $query = '{
              shop {id name currencyCode email myshopifyDomain primaryDomain { url  }
              }
            }';

            $response = $client->request('POST', ShopifyConstants::API_ENDPOINT, [
                'body' => $query,
            ]);

            $body = json_decode($response->getBody(), true);

            if (isset($body['data']['shop'])) {
                return $body['data']['shop'];
            }
        } catch (\Exception $e) {
            return null;
        }
        return null;
    }

    public static function getShopifyClient($shopUrl, $accessToken)
    {
        return new Client([
            'base_uri' => $shopUrl,
            'headers' => [
                'Content-Type' => 'application/graphql',
                'X-Shopify-Access-Token' => $accessToken,
                'X-GraphQL-Cost-Include-Fields' => true
            ]
        ]);
    }

    public static function convertToPounds($weight, $unit)
    {
        switch ($unit) {
            case 'GRAMS':
                return $weight * 0.00220462;
            case 'KILOGRAMS':
                return $weight * 2.20462;
            case 'OUNCES':
                return $weight * 0.0625;
            default:
                return $weight;
        }
    }

    public static function fetchProducts($client)
    {
        ini_set('max_execution_time', 0);
        $products = [];
        $cursor = null;
        $counter = 0;

        do {
            $args = ['first: 100'];
            if (!empty($cursor)) {
                $args[] = 'after:"' . $cursor . '"';
            }
            $args = implode(', ', $args);

            $PQ = '{
            products(query: "status:active",' . $args . ') {
                    edges {
                        cursor
                        node {
                            id
                            title
                            handle
                            status
                            totalInventory
                            totalVariants
                            priceRangeV2 {
                                maxVariantPrice { amount currencyCode }
                                minVariantPrice { amount currencyCode }
                            }
                            images(first: 10) { nodes { url } }
                            productCategory {
                                productTaxonomyNode {
                                    id fullName isRoot name isLeaf
                                }
                            }
                            variants(first: 50) {
                                edges {
                                    node {
                                        title
                                        sku
                                        price
                                        weight
                                        weightUnit
                                        inventoryQuantity
                                        requiresShipping
                                        image { url }
                                        inventoryItem { id }
                                    }
                                }
                            }
                        }
                    }
                    pageInfo { endCursor hasNextPage }
                }
            }';

            try {
                $response = $client->request('POST', ShopifyConstants::API_ENDPOINT, [
                    'body' => $PQ,
                ]);

                if ($response->getStatusCode() !== 200) {
                    return ['status' => false, 'data' => []];
                }

                $body = json_decode($response->getBody());
                $edges = $body->data->products->edges ?? [];

                foreach ($edges as $edge) {
                    $product = $edge->node;

                    $productImages = array_map(fn($img) => $img->url, $product->images->nodes ?? []);

                    $variants = [];
                    foreach ($product->variants->edges as $variantEdge) {
                        $variant = $variantEdge->node;
                        $variants[] = [
                            'title' => $variant->title,
                            'sku' => $variant->sku,
                            'price' => $variant->price,
                            'weight' => $variant->weight,
                            'weightUnit' => $variant->weightUnit,
                            'inventoryQuantity' => $variant->inventoryQuantity,
                            'requiresShipping' => $variant->requiresShipping,
                            'image' => $variant->image->url ?? null,
                            'inventoryItemId' => $variant->inventoryItem->id ?? null,
                        ];
                    }

                    $products[] = [
                        'id' => $product->id,
                        'title' => $product->title,
                        'handle' => $product->handle,
                        'status' => $product->status,
                        'totalInventory' => $product->totalInventory,
                        'totalVariants' => $product->totalVariants,
                        'priceRangeV2' => $product->priceRangeV2,
                        'productImages' => $productImages,
                        'productCategory' => $product->productCategory,
                        'variants' => $variants,
                    ];

                    $cursor = $edge->cursor;
                }

                $counter++;
            } catch (\Exception $e) {
                return ['status' => false, 'data' => []];
            }
        } while (!empty($cursor) && ($body->data->products->pageInfo->hasNextPage ?? false));

        return ['status' => true, 'data' => $products];
    }

    public static function updateShopifyInventory($inventoryItemId, $locationId, $availableAdjustment, $shopifyCredentials)
    {
        $shopUrl = rtrim($shopifyCredentials->shop_url, '/') . ShopifyConstants::SHOPIFY_INVENTORY_UPDATE_URL;
        $accessToken = $shopifyCredentials->access_token;
        $client = new Client([
            'base_uri' => $shopUrl,
            'headers' => [
                'Content-Type' => 'application/json',
                'X-Shopify-Access-Token' => $accessToken
            ]
        ]);

        $query = <<<GRAPHQL
        {
          inventoryItem(id: "gid://shopify/InventoryItem/$inventoryItemId") {
            inventoryLevels(first: 10) {
              edges {
                node {
                  location {
                    id
                    name
                  }
                  quantities(names: ["available", "on_hand"]) {
                    name
                    quantity
                  }
                }
              }
            }
          }
        }
        GRAPHQL;

        try {
            $response = $client->request('POST', '', [
                'json' => ['query' => $query]
            ]);
            $body = json_decode($response->getBody()->getContents(), true);

            $locations = $body['data']['inventoryItem']['inventoryLevels']['edges'] ?? [];
            if (empty($locations)) {
                \Log::error('No Shopify locations found.');
                return false;
            }

            $selectedLocation = null;
            foreach ($locations as $location) {
                $quantities = $location['node']['quantities'] ?? [];

                foreach ($quantities as $quantity) {
                    if ($quantity['name'] === 'available' && $quantity['quantity'] > 0) {
                        $selectedLocation = $location['node']['location']['id'];
                        break 2;
                    }
                }
            }

            if (!$selectedLocation) {
                \Log::warning('No inventory available in any location.');
                return false;
            }

            $mutation = <<<GRAPHQL
            mutation {
              inventoryAdjustQuantities(input: {
                reason: "correction",
                name: "available",
                changes: [
                  {
                    inventoryItemId: "gid://shopify/InventoryItem/$inventoryItemId",
                    locationId: "$selectedLocation",
                    delta: $availableAdjustment
                  }
                ]
              }) {
                inventoryAdjustmentGroup {
                  createdAt
                  changes {
                    name
                    delta
                  }
                }
                userErrors {
                  field
                  message
                }
              }
            }
            GRAPHQL;

            $updateResponse = $client->request('POST', '', [
                'json' => ['query' => $mutation]
            ]);
            $updateBody = json_decode($updateResponse->getBody()->getContents(), true);

            if (!empty($updateBody['data']['inventoryAdjustQuantity']['userErrors'])) {
                \Log::error('Shopify Inventory Update Error:', $updateBody['data']['inventoryAdjustQuantity']['userErrors']);
                return false;
            }

            return $updateBody['data']['inventoryAdjustQuantity']['inventoryLevel']['available'] ?? null;
        } catch (\Exception $e) {
            \Log::error("Shopify GraphQL Error: " . $e->getMessage());
            return false;
        }
    }

    public static function getInventoryItemIdFromVariantId($variantId, $shopifyCredentials)
    {
        $shopUrl = rtrim($shopifyCredentials->shop_url, '/') . '/admin/api/2023-10/graphql.json';
        $accessToken = $shopifyCredentials->access_token;

        $client = new \GuzzleHttp\Client([
            'base_uri' => $shopUrl,
            'headers' => [
                'Content-Type' => 'application/json',
                'X-Shopify-Access-Token' => $accessToken,
            ],
        ]);

        $variantGID = "gid://shopify/ProductVariant/$variantId";

        $query = <<<GRAPHQL
        {
          productVariant(id: "$variantGID") {
            inventoryItem {
              id
            }
          }
        }
        GRAPHQL;

        try {
            $response = $client->request('POST', '', [
                'json' => ['query' => $query],
            ]);
            $body = json_decode($response->getBody()->getContents(), true);

            return $body['data']['productVariant']['inventoryItem']['id'] ?? null;
        } catch (\Exception $e) {
            \Log::error("Error fetching inventory item ID: " . $e->getMessage());
            return null;
        }
    }

    public static function createShopifyWebhook($shop, $accessToken, $topic, $callbackUrl)
    {
        if (str_starts_with($shop, 'https://')) {
            $shop = preg_replace('#^https?://#', '', $shop);
        }

        $response = Http::withHeaders([
            'X-Shopify-Access-Token' => $accessToken,
            'Content-Type' => 'application/json',
        ])->post("https://{$shop}/admin/api/2025-04/webhooks.json", [
            'webhook' => [
                'topic' => $topic,
                'address' => $callbackUrl,
                'format' => 'json',
            ],
        ]);

        if ($response->successful()) {
            return $response->json();
        } else {
            throw new \Exception('Error creating webhook: ' . $response->body());
        }
    }

    public static function webhooksList($shop, $accessToken)
    {
        if (str_starts_with($shop, 'https://')) {
            $shop = preg_replace('#^https?://#', '', $shop);
        }

        $response = Http::withHeaders([
            'X-Shopify-Access-Token' => $accessToken,
        ])->get("https://{$shop}/admin/api/2025-04/webhooks.json");

        $webhooks = $response->json('webhooks');
        return $webhooks;
    }

    public static function deleteWebhook($shop, $accessToken, $id)
    {
        if (str_starts_with($shop, 'https://')) {
            $shop = preg_replace('#^https?://#', '', $shop);
        }

        $response = Http::withHeaders([
            'X-Shopify-Access-Token' => $accessToken,
        ])->delete("https://{$shop}/admin/api/2025-04/webhooks/{$id}.json");

        return ($response->json());
    }

    public static function getAllInventoryItemIds($shopifyCredentials)
    {
        $query = <<<GRAPHQL
        {
        inventoryItems(first: 100) {
            edges {
            node {
                id
            }
            }
        }
        }
        GRAPHQL;

        $client = new \GuzzleHttp\Client([
            'base_uri' => rtrim($shopifyCredentials->shop_url, '/') . '/admin/api/2023-10/graphql.json',
            'headers' => [
                'Content-Type' => 'application/json',
                'X-Shopify-Access-Token' => $shopifyCredentials->access_token,
            ],
        ]);

        try {
            $response = $client->request('POST', '', [
                'json' => ['query' => $query]
            ]);

            $body = json_decode($response->getBody()->getContents(), true);

            $edges = $body['data']['inventoryItems']['edges'] ?? [];

            return collect($edges)->pluck('node.id')->map(function ($gid) {
                return preg_replace('/[^0-9]/', '', $gid); // Extract numeric ID
            })->toArray();
        } catch (\Exception $e) {
            \Log::error("❌ Failed to fetch inventory items: " . $e->getMessage());
            return [];
        }
    }

    public static function getAvailableInventoryFromShopify($inventoryItemId, $shopifyCredentials)
    {
        $shopUrl = rtrim($shopifyCredentials->shop_url, '/') . '/admin/api/2023-10/graphql.json';
        $accessToken = $shopifyCredentials->access_token;

        $client = new \GuzzleHttp\Client([
            'base_uri' => $shopUrl,
            'headers' => [
                'Content-Type' => 'application/json',
                'X-Shopify-Access-Token' => $accessToken,
            ],
        ]);

        $inventoryGID = "gid://shopify/InventoryItem/$inventoryItemId";

        $query = <<<GRAPHQL
        {
        inventoryItem(id: "$inventoryGID") {
            inventoryLevels(first: 5) {
            edges {
                node {
                location {
                    id
                    name
                }
                quantities(names: ["available"]) {
                    name
                    quantity
                }
                }
            }
            }
        }
        }
        GRAPHQL;

        try {
            $response = $client->request('POST', '', [
                'json' => ['query' => $query],
            ]);
            $body = json_decode($response->getBody()->getContents(), true);

            $levels = $body['data']['inventoryItem']['inventoryLevels']['edges'] ?? [];

            $availableQty = 0;

            foreach ($levels as $level) {
                $quantities = $level['node']['quantities'] ?? [];
                foreach ($quantities as $quantity) {
                    if ($quantity['name'] === 'available') {
                        $availableQty += $quantity['quantity'] ?? 0;
                    }
                }
            }

            return $availableQty;
        } catch (\Exception $e) {
            \Log::error("Error fetching Shopify inventory: " . $e->getMessage());
            return null;
        }
    }

    public static function getAvailableQuantityForInventoryItem($inventoryItemId, $shopifyCredentials)
    {
        $shopUrl = rtrim($shopifyCredentials->shop_url, '/') . '/admin/api/2023-10/graphql.json';
        $accessToken = $shopifyCredentials->access_token;

        $client = new \GuzzleHttp\Client([
            'base_uri' => $shopUrl,
            'headers' => [
                'Content-Type' => 'application/json',
                'X-Shopify-Access-Token' => $accessToken,
            ],
        ]);

        $inventoryGID = "gid://shopify/InventoryItem/$inventoryItemId";

        $query = <<<GRAPHQL
        {
            inventoryItem(id: "$inventoryGID") {
                inventoryLevels(first: 1) {
                    edges {
                        node {
                            quantities(names: ["available"]) {
                                name
                                quantity
                            }
                        }
                    }
                }
            }
        }
        GRAPHQL;

        try {
            $response = $client->post('', ['json' => ['query' => $query]]);
            $body = json_decode($response->getBody()->getContents(), true);

            return $body['data']['inventoryItem']['inventoryLevels']['edges'][0]['node']['quantities'][0]['quantity'] ?? null;
        } catch (\Exception $e) {
            \Log::error("❌ Shopify inventory fetch failed: " . $e->getMessage());
            return null;
        }
    }

}
