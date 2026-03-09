<?php

namespace Packages\Store\Services\V3;

use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ItemNotFoundException;
use Illuminate\Validation\UnauthorizedException;
use Packages\Store\Contracts\V3\BaseApiResponse;
use Packages\Store\Models\V3\Store;

class PayPalOnboardingService
{
    use BaseApiResponse;

    protected Store $store;

    public function __construct()
    {
        $this->store = new Store();
    }

    /**
     * Get PayPal access token (cached for performance)
     *
     * @return string
     * @throws Exception
     */
    protected function getAccessToken(): string
    {
        return Cache::remember('paypal_access_token', 3000, function () {
            $mode = config('paypal.mode', 'sandbox');
            $baseUrl = config("paypal.base_url.{$mode}");
            $clientId = config('paypal.client_id');
            $clientSecret = config('paypal.client_secret');

            $response = Http::withBasicAuth($clientId, $clientSecret)
                ->asForm()
                ->post("{$baseUrl}/v1/oauth2/token", [
                    'grant_type' => 'client_credentials',
                ]);

            if (!$response->successful()) {
                Log::error('PayPal Auth Error', ['response' => $response->json()]);
                throw new Exception('Failed to obtain PayPal access token');
            }

            return $response->json()['access_token'];
        });
    }

    /**
     * Make authenticated API request to PayPal
     *
     * @param string $method
     * @param string $endpoint
     * @param array $data
     * @param array $headers
     * @return array
     * @throws Exception
     */
    protected function apiRequest(string $method, string $endpoint, array $data = [], array $headers = []): array
    {
        $token = $this->getAccessToken();
        $mode = config('paypal.mode', 'sandbox');
        $baseUrl = config("paypal.base_url.{$mode}");

        $defaultHeaders = [
            'Content-Type' => 'application/json',
            'PayPal-Request-Id' => uniqid('paypal_partner_', true),
        ];

        $response = Http::withToken($token)
            ->withHeaders(array_merge($defaultHeaders, $headers))
            ->{$method}("{$baseUrl}{$endpoint}", $data);

        if (!$response->successful()) {
            Log::error('PayPal Partner API Error', [
                'endpoint' => $endpoint,
                'status' => $response->status(),
                'response' => $response->json(),
            ]);
            throw new Exception($response->json()['message'] ?? 'PayPal API request failed');
        }

        return $response->json();
    }

    /**
     * Create a Partner Referral link for seller onboarding
     *
     * @return array Contains 'action_url' (onboarding link) and 'tracking_id'
     * @throws Exception
     */
    public function createPartnerReferral(): array
    {
        $user = auth()->user();
        $userId = $user->user_id;
        $userEmail = $user->email;

        if (empty($userId)) {
            throw new UnauthorizedException('User not authenticated');
        }

        $store = $this->store->with('user')
            ->select('store_id', 'store_name', 'paypal_connected', 'paypal_merchant_id')
            ->where('user_id', $userId)
            ->first();

        if (!$store) {
            throw new ItemNotFoundException('Store not found for this user');
        }

        // Check if already connected
        if ($store->paypal_connected === '1' && !empty($store->paypal_merchant_id)) {
            throw new Exception('PayPal account is already connected');
        }

        // Generate unique tracking ID for this onboarding
        $trackingId = 'MALLSHARK_' . $store->store_id . '_' . uniqid();

        // Build Partner Referral payload
        $payload = [
            'tracking_id' => $trackingId,
            'partner_config_override' => [
                'return_url' => config('paypal.seller_return_url') . "?tracking_id={$trackingId}",
                'return_url_description' => 'Return to Mallshark after completing PayPal setup',
                'action_renewal_url' => config('paypal.seller_return_url') . "?tracking_id={$trackingId}&action=renew",
            ],
            'operations' => [
                [
                    'operation' => 'API_INTEGRATION',
                    'api_integration_preference' => [
                        'rest_api_integration' => [
                            'integration_method' => 'PAYPAL',
                            'integration_type' => 'THIRD_PARTY',
                            'third_party_details' => [
                                'features' => config('paypal.seller_features', [
                                    'PAYMENT',
                                    'REFUND',
                                    'PARTNER_FEE',
                                    'DELAY_FUNDS_DISBURSEMENT',
                                ]),
                            ],
                        ],
                    ],
                ],
            ],
            'products' => config('paypal.seller_products', ['EXPRESS_CHECKOUT']),
            'legal_consents' => [
                [
                    'type' => 'SHARE_DATA_CONSENT',
                    'granted' => true,
                ],
            ],
        ];

        // Add business info if available
        if (!empty($store->store_email)) {
            $payload['email'] = $store->store_email;
        }

        try {
            $response = $this->apiRequest('post', '/v2/customer/partner-referrals', $payload);

            // Extract the action URL from HATEOAS links
            $actionUrl = null;
            foreach ($response['links'] ?? [] as $link) {
                if ($link['rel'] === 'action_url') {
                    $actionUrl = $link['href'];
                    break;
                }
            }

            if (!$actionUrl) {
                throw new Exception('No action URL returned from PayPal');
            }

            // Save tracking ID and onboarding link to store
            $this->store->where('store_id', $store->store_id)->update([
                'paypal_tracking_id' => $trackingId,
                'paypal_onboarding_link' => $actionUrl,
            ]);

            Log::info('PayPal Partner Referral Created', [
                'store_id' => $store->store_id,
                'tracking_id' => $trackingId,
            ]);

            return [
                'action_url' => $actionUrl,
                'tracking_id' => $trackingId,
            ];
        } catch (Exception $e) {
            Log::error('PayPal Partner Referral Error', [
                'store_id' => $store->store_id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }
     public function handleSellerReturnWithAuth(array $data): array
    {
        $userId = auth()->user()->user_id;

        if (empty($userId)) {
            throw new UnauthorizedException('User not authenticated');
        }

        // Get store for authenticated user
        $store = $this->store
            ->where('user_id', $userId)
            ->first();

        if (!$store) {
            throw new ItemNotFoundException('Store not found for this user');
        }

        $merchantIdInPayPal = $data['merchantIdInPayPal'] ?? $data['merchantId'] ?? null;
        $permissionsGranted = $data['permissionsGranted'] === 'true';
        $isEmailConfirmed = $data['isEmailConfirmed'] === 'true';
        $accountStatus = $data['accountStatus'] ?? null;
        // Mark as connected if permissions granted (email can be verified later)
        $paymentsReceivable = $permissionsGranted;

        $updates = [
            'paypal_connected' => $paymentsReceivable ? '1' : '0',
            'paypal_account_status' => $accountStatus,
            'paypal_payments_receivable' => $paymentsReceivable,
            'paypal_primary_email_confirmed' => $isEmailConfirmed,
            'paypal_status_checked_at' => now(),
        ];

        // Fetch PayPal email from API if merchant ID available
        if (!empty($merchantIdInPayPal)) {
            $updates['paypal_merchant_id'] = $merchantIdInPayPal;

            $paypalEmail = $this->getPayPalEmailFromAPI($merchantIdInPayPal);
            if ($paypalEmail) {
                $updates['paypal_email'] = $paypalEmail;
            }
        }

        $this->store->where('store_id', $store->store_id)->update($updates);

        Log::info('PayPal Account Connected (Bearer Auth)', [
            'store_id' => $store->store_id,
            'user_id' => $userId,
            'merchant_id' => $merchantIdInPayPal,
            'email' => $updates['paypal_email'] ?? 'not_retrieved',
        ]);

        return [
            'store_id' => $store->store_id,
            'merchant_id' => $merchantIdInPayPal,
            'account_status' => $accountStatus,
            'permissions_granted' => $permissionsGranted,
            'email_confirmed' => $isEmailConfirmed,
            'paypal_email' => $updates['paypal_email'] ?? null,
            'onboarding_complete' => $paymentsReceivable,
        ];
    }

    /**
     * Handle seller return from PayPal onboarding (legacy - using tracking ID)
     * Fetches merchant email from PayPal API and updates store record
     * Handle seller return from PayPal onboarding
     *
     * @param array $data Contains 'tracking_id' and optionally 'merchantIdInPayPal'
     * @return array
     * @throws Exception
     */
    public function handleSellerReturn(array $data): array
    {
        $trackingId = $data['tracking_id'] ?? null;
        $merchantIdInPayPal = $data['merchantIdInPayPal'] ?? $data['merchantId'] ?? null;

        if (empty($trackingId)) {
            throw new Exception('Tracking ID is required');
        }

        // Find store by tracking ID
        $store = $this->store
            ->where('paypal_tracking_id', $trackingId)
            ->first();

        if (!$store) {
            throw new ItemNotFoundException('Store not found for this tracking ID');
        }

        // If merchant ID provided in return URL, save it
        if (!empty($merchantIdInPayPal)) {
            $this->store->where('store_id', $store->store_id)->update([
                'paypal_merchant_id' => $merchantIdInPayPal,
            ]);
        }

        // Fetch merchant status from PayPal
        $merchantStatus = $this->getMerchantIntegrationStatus($store->store_id);

        return [
            'store_id' => $store->store_id,
            'merchant_status' => $merchantStatus,
            'onboarding_complete' => $merchantStatus['payments_receivable'] ?? false,
        ];
    }

    /**
     * Get merchant integration status from PayPal
     *
     * @param int $storeId
     * @return array
     * @throws Exception
     */
    public function getMerchantIntegrationStatus(int $storeId): array
    {
        $store = $this->store
            ->select('store_id', 'paypal_merchant_id', 'paypal_tracking_id')
            ->where('store_id', $storeId)
            ->first();

        if (!$store) {
            throw new ItemNotFoundException('Store not found');
        }

        // Try to get merchant ID via tracking ID if not stored
        if (empty($store->paypal_merchant_id) && !empty($store->paypal_tracking_id)) {
            $merchantId = $this->getMerchantIdByTrackingId($store->paypal_tracking_id);
            if ($merchantId) {
                $this->store->where('store_id', $storeId)->update([
                    'paypal_merchant_id' => $merchantId,
                ]);
                $store->paypal_merchant_id = $merchantId;
            }
        }

        if (empty($store->paypal_merchant_id)) {
            return [
                'connected' => false,
                'payments_receivable' => false,
                'primary_email_confirmed' => false,
                'message' => 'Merchant ID not found. Please complete onboarding.',
            ];
        }

        $partnerId = config('paypal.partner_id');
        $merchantId = $store->paypal_merchant_id;

        try {
            $response = $this->apiRequest(
                'get',
                "/v1/customer/partners/{$partnerId}/merchant-integrations/{$merchantId}"
            );

            $paymentsReceivable = $response['payments_receivable'] ?? false;
            $primaryEmailConfirmed = $response['primary_email_confirmed'] ?? false;
            $oauthIntegrations = $response['oauth_integrations'] ?? [];
            $accountStatus = $response['merchant_status'] ?? 'UNKNOWN';

            // Extract granted permissions
            $permissions = [];
            foreach ($oauthIntegrations as $integration) {
                if (isset($integration['oauth_third_party'])) {
                    $permissions = array_merge(
                        $permissions,
                        $integration['oauth_third_party']['scopes'] ?? []
                    );
                }
            }

            // Update store with latest status
            $this->store->where('store_id', $storeId)->update([
                'paypal_connected' => $paymentsReceivable ? '1' : '0',
                'paypal_account_status' => $accountStatus,
                'paypal_payments_receivable' => $paymentsReceivable,
                'paypal_primary_email_confirmed' => $primaryEmailConfirmed,
                'paypal_permissions_granted' => json_encode($permissions),
                'paypal_status_checked_at' => now(),
            ]);

            Log::info('PayPal Merchant Status Retrieved', [
                'store_id' => $storeId,
                'merchant_id' => $merchantId,
                'payments_receivable' => $paymentsReceivable,
            ]);

            return [
                'connected' => $paymentsReceivable,
                'merchant_id' => $merchantId,
                'payments_receivable' => $paymentsReceivable,
                'primary_email_confirmed' => $primaryEmailConfirmed,
                'account_status' => $accountStatus,
                'permissions' => $permissions,
            ];
        } catch (Exception $e) {
            Log::error('PayPal Get Merchant Status Error', [
                'store_id' => $storeId,
                'merchant_id' => $merchantId,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Get merchant ID using tracking ID (partner referral lookup)
     *
     * @param string $trackingId
     * @return string|null
     */
    protected function getMerchantIdByTrackingId(string $trackingId): ?string
    {
        $partnerId = config('paypal.partner_id');

        try {
            $response = $this->apiRequest(
                'get',
                "/v1/customer/partners/{$partnerId}/merchant-integrations?tracking_id={$trackingId}"
            );

            return $response['merchant_id'] ?? null;
        } catch (Exception $e) {
            Log::warning('Could not retrieve merchant ID by tracking ID', [
                'tracking_id' => $trackingId,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }
     /**
     * Check both Stripe and PayPal wallet status
     * Updates store data if needed and returns combined status
     *
     * @return array
     * @throws Exception
     */
    public function checkWalletStatus(): array
    {
        $userId = auth()->user()->user_id;

        if (empty($userId)) {
            throw new UnauthorizedException('User not authenticated');
        }

        $store = $this->store
            ->where('user_id', $userId)
            ->first();

        if (!$store) {
            throw new ItemNotFoundException('Store not found');
        }

        // Check Stripe status
        $stripeStatus = $this->checkStripeStatus($store);

        // Check PayPal status
        $paypalStatus = $this->getSellerStatus();

        return [
            'stripe' => $stripeStatus,
            'paypal' => $paypalStatus,
        ];
    }

    /**
     * Check Stripe account status
     * Retrieves account details from Stripe and updates store if fully set up
     *
     * @param Store $store
     * @return array
     * @throws Exception
     */
    protected function checkStripeStatus(Store $store): array
    {
        $stripeKey = config('stripe.secret');
        $accountId = $store->stripe_account_id ?? null;

        if (!$stripeKey) {
            return [
                'connected' => false,
                'account_id' => null,
                'details_submitted' => false,
                'message' => 'Stripe API key not configured',
            ];
        }

        if (!$accountId) {
            return [
                'connected' => $store->stripe_connected == 1,
                'account_id' => null,
                'details_submitted' => false,
                'message' => 'No Stripe account linked',
            ];
        }

        try {
            // Check if account is already marked as connected
            if ($store->stripe == 1 && $store->stripe_connected == 1) {
                return [
                    'connected' => true,
                    'account_id' => $accountId,
                    'details_submitted' => true,
                    'message' => 'Stripe account fully connected',
                ];
            }

            // Retrieve account details from Stripe
            $stripe = new \Stripe\StripeClient($stripeKey);
            $response = $stripe->accounts->retrieve($accountId);

            $detailsSubmitted = $response->details_submitted ?? false;
            $chargesEnabled = $response->charges_enabled ?? false;
            $payoutsEnabled = $response->payouts_enabled ?? false;

            // Update store if details are now submitted
            if ($detailsSubmitted && ($store->stripe == 0 || $store->stripe_connected == 0)) {
                $this->store->where('user_id', $store->user_id)->update([
                    'stripe' => 1,
                    'stripe_connected' => 1,
                ]);

                Log::info('Stripe Account Status Updated', [
                    'store_id' => $store->store_id,
                    'account_id' => $accountId,
                    'details_submitted' => true,
                ]);
            }

            return [
                'connected' => $chargesEnabled && $payoutsEnabled,
                'account_id' => $accountId,
                'details_submitted' => $detailsSubmitted,
                'charges_enabled' => $chargesEnabled,
                'payouts_enabled' => $payoutsEnabled,
            ];
        } catch (Exception $e) {
            Log::warning('Stripe Status Check Failed', [
                'store_id' => $store->store_id,
                'account_id' => $accountId,
                'error' => $e->getMessage(),
            ]);

            return [
                'connected' => false,
                'account_id' => $accountId,
                'details_submitted' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Fetch PayPal merchant email from API using merchant integration endpoint
     *
     * @param string $merchantId The PayPal merchant ID
     * @return string|null The merchant's PayPal email or null if not found
     */
    protected function getPayPalEmailFromAPI(string $merchantId): ?string
    {
        try {
            $partnerId = config('paypal.partner_id');

            $response = $this->apiRequest(
                'get',
                "/v1/customer/partners/{$partnerId}/merchant-integrations/{$merchantId}"
            );

            // Try multiple possible field names for email
            $email = $response['email']
                ?? $response['merchant_email']
                ?? $response['primary_email']
                ?? null;

            if ($email) {
                Log::info('PayPal Email Retrieved', [
                    'merchant_id' => $merchantId,
                    'email' => $email,
                ]);
            }

            return $email;
        } catch (Exception $e) {
            Log::warning('Could not retrieve PayPal email from API', [
                'merchant_id' => $merchantId,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }
    /**
     * Get seller's current PayPal connection status
     *
     * @return array
     * @throws Exception
     */
    public function getSellerStatus(): array
    {
        $userId = auth()->user()->user_id;

        if (empty($userId)) {
            throw new UnauthorizedException('User not authenticated');
        }

        $store = $this->store
            ->where('user_id', $userId)
            ->first();

        if (!$store) {
            throw new ItemNotFoundException('Store not found');
        }

        // If connected, refresh status from PayPal
        if ($store->paypal_connected === '1' && !empty($store->paypal_merchant_id)) {
            // Only refresh if last check was more than 1 hour ago
            $lastCheck = $store->paypal_status_checked_at;
            if (!$lastCheck || now()->diffInHours($lastCheck) >= 1) {
                return $this->getMerchantIntegrationStatus($store->store_id);
            }
        }

        return [
            'connected' => $store->paypal_connected === '1',
            'merchant_id' => $store->paypal_merchant_id,
            'payments_receivable' => $store->paypal_payments_receivable ?? false,
            'primary_email_confirmed' => $store->paypal_primary_email_confirmed ?? false,
            'account_status' => $store->paypal_account_status,
            'permissions' => json_decode($store->paypal_permissions_granted ?? '[]', true),
            'onboarding_link' => $store->paypal_onboarding_link,
        ];
    }

    /**
     * Disconnect PayPal account from store
     *
     * @return array
     * @throws Exception
     */
    public function disconnectPayPal(): array
    {
        $userId = auth()->user()->user_id;

        if (empty($userId)) {
            throw new UnauthorizedException('User not authenticated');
        }

        $store = $this->store
            ->where('user_id', $userId)
            ->first();

        if (!$store) {
            throw new ItemNotFoundException('Store not found');
        }

        if ($store->paypal_connected !== '1') {
            throw new Exception('PayPal account is not connected');
        }

        // Reset PayPal fields
        $this->store->where('store_id', $store->store_id)->update([
            'paypal_merchant_id' => null,
            'paypal_tracking_id' => null,
            'paypal_connected' => '0',
            'paypal_account_status' => null,
            'paypal_permissions_granted' => null,
            'paypal_onboarding_link' => null,
            'paypal_payments_receivable' => false,
            'paypal_primary_email_confirmed' => false,
            'paypal_status_checked_at' => null,
        ]);

        Log::info('PayPal Account Disconnected', [
            'store_id' => $store->store_id,
            'user_id' => $userId,
        ]);

        return [
            'success' => true,
            'message' => 'PayPal account disconnected successfully',
        ];
    }

    /**
     * Generate a new onboarding link for incomplete onboarding
     *
     * @return array
     * @throws Exception
     */
    public function regenerateOnboardingLink(): array
    {
        $userId = auth()->user()->user_id;

        if (empty($userId)) {
            throw new UnauthorizedException('User not authenticated');
        }

        $store = $this->store
            ->where('user_id', $userId)
            ->first();

        if (!$store) {
            throw new ItemNotFoundException('Store not found');
        }

        if ($store->paypal_connected === '1') {
            throw new Exception('PayPal account is already fully connected');
        }

        // Create new partner referral
        return $this->createPartnerReferral();
    }
}
