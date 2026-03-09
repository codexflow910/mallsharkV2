<?php

namespace Packages\Core\Services\V4;

use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CryptoService
{
    /**
     * Initialize the crypto service
     */
    public function __construct()
    {
        $this->apiKey = env('NOWPAYMENTS_API_KEY');
        $this->baseUrl = env('NOWPAYMENTS_API_URL');
    }

    /**
     * Process a crypto payment
     *
     * @param array $paymentData
     * @return array
     */
     /**
     * Create a hosted invoice for crypto payment
     */
    public function createInvoice($grandTotal, array $orderIds, $currency = 'usd')
    {
        // JSON-encode order IDs so the API receives a string representation
        $orderIds = is_array($orderIds) ? urlencode(json_encode(array_values($orderIds))) : urlencode($orderIds);
        $response = Http::withHeaders([
            'x-api-key' => $this->apiKey,
        ])->post("{$this->baseUrl}/invoice", [
            'price_amount' => $grandTotal,
            'price_currency' => $currency,
            'order_id' => $orderIds,
            'order_description' => "MallShark Order #{$orderIds}",
            'ipn_callback_url' => env('WEBHOOK_URL'),
            'success_url' => env('STRIPE_SUCCESS_URL').'?id=' . $orderIds,
            'cancel_url' => env('STRIPE_CANCEL_URL').'?id=' . $orderIds,
        ]);
        $data = $response->json();
        if ($response->successful()) {
            return $data;
        } else {
            throw new Exception('Failed to create invoice: ' . $data['message']);
        }
    }

   

    /**
     * Verify a crypto transaction
     *
     * @param string $transactionId
     * @return bool
     */
    public function verifyTransaction(string $transactionId)
    {
        // Implement verification logic
        return true;
    }

    /**
     * Get supported crypto currencies
     *
     * @return array
     */
    public function getSupportedCurrencies()
    {
        return ['USDC', 'USDP'];
    }
}