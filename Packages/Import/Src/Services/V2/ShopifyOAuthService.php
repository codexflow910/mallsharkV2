<?php

namespace Packages\Import\Services\V2;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ShopifyOAuthService
{
    /**
     * Scopes required for full Shopify integration
     * - read_products, write_products: Access product catalog
     * - read_inventory, write_inventory: Manage inventory levels
     * - read_orders: Access order information for webhooks
     * - write_webhooks: Create webhooks for order/product sync
     */
    const SCOPES = 'read_products,write_products,read_inventory,write_inventory,read_orders';

    /**
     * Generate the OAuth authorization URL for Shopify
     *
     * @param string $shopUrl The shop URL (e.g., https://myshop.myshopify.com)
     * @param string $clientId The app's client ID
     * @param string $redirectUri The callback URL after authorization
     * @param string $state CSRF protection token
     * @return string The authorization URL to redirect the user to
     */
    public function getAuthorizationUrl(string $shopUrl, string $clientId, string $redirectUri, string $state): string
    {
        $shop = $this->normalizeShopDomain($shopUrl);

        $params = [
            'client_id' => $clientId,
            'scope' => self::SCOPES,
            'redirect_uri' => $redirectUri,
            'state' => $state,
        ];

        return "https://{$shop}/admin/oauth/authorize?" . http_build_query($params);
    }

    /**
     * Exchange authorization code for access token
     *
     * @param string $shopUrl The shop URL
     * @param string $clientId The app's client ID
     * @param string $clientSecret The app's client secret
     * @param string $code The authorization code from Shopify callback
     * @return array|null Returns ['access_token' => '...', 'scope' => '...'] or null on failure
     */
    public function exchangeCodeForToken(string $shopUrl, string $clientId, string $clientSecret, string $code): ?array
    {
        $shop = $this->normalizeShopDomain($shopUrl);

        try {
            $response = Http::post("https://{$shop}/admin/oauth/access_token", [
                'client_id' => $clientId,
                'client_secret' => $clientSecret,
                'code' => $code,
            ]);

            if ($response->successful()) {
                $data = $response->json();

                Log::info('Shopify OAuth token exchange successful', [
                    'shop' => $shop,
                    'scope' => $data['scope'] ?? 'unknown',
                ]);

                return [
                    'access_token' => $data['access_token'] ?? null,
                    'scope' => $data['scope'] ?? null,
                ];
            }

            Log::error('Shopify OAuth token exchange failed', [
                'shop' => $shop,
                'status' => $response->status(),
                'error' => $response->json(),
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('Shopify OAuth token exchange exception', [
                'shop' => $shop,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Validate HMAC signature from Shopify callback
     * This verifies the callback is genuinely from Shopify
     *
     * @param array $queryParams All query parameters from the callback
     * @param string $clientSecret The app's client secret
     * @return bool True if signature is valid
     */
    public function validateHmac(array $queryParams, string $clientSecret): bool
    {
        if (!isset($queryParams['hmac'])) {
            return false;
        }

        $hmac = $queryParams['hmac'];
        unset($queryParams['hmac']);

        // Sort parameters and build query string
        ksort($queryParams);
        $message = http_build_query($queryParams);

        // Calculate expected HMAC
        $calculatedHmac = hash_hmac('sha256', $message, $clientSecret);

        return hash_equals($calculatedHmac, $hmac);
    }

    /**
     * Normalize shop domain by removing protocol and trailing slashes
     *
     * @param string $shopUrl
     * @return string Normalized domain (e.g., "myshop.myshopify.com")
     */
    private function normalizeShopDomain(string $shopUrl): string
    {
        $shop = preg_replace('#^https?://#', '', $shopUrl);
        return rtrim($shop, '/');
    }
}
