<?php

namespace Packages\Store\Services\V2;

use Exception;
use Packages\Store\Contracts\V2\StoreHelpers;
use Packages\Core\Contracts\V2\CoreHelpers;
use Packages\Store\Contracts\BaseApiResponse;
use Packages\Store\Repositories\Eloquent\V2\StoreRepository;
use App\Http\Controllers\MailgunController;
use App\Mail\StoreSetupStepMail;
class StoreSetupService
{
	use StoreHelpers;
	use CoreHelpers;
	use BaseApiResponse;

	protected $storeRepository;
    protected $mailgunController;
    
    // Define the setup steps
    protected $allSteps = [
        'account_created' => 'Store Account Created',
        'logo_uploaded' => 'Store Logo',
        'product_added' => 'First Product',
        'stripe_connected' => 'Stripe Account'
    ];

	public function __construct(StoreRepository $storeRepository, MailgunController $mailgunController)
	{
		$this->storeRepository = $storeRepository;
        $this->mailgunController = $mailgunController;
	}
  public $supportEmail = 'abdullah.jutt@mindblaze.biz'; // change to real support email

    /**
     * Generic method to send setup step emails with progress tracking
     * Uses MailgunController to send via Mailgun, falls back to Laravel Mail.
     */
    protected function sendStepMail(string $step, string $message, $store = null, string $currentStepKey = null)
    {
        $details = [
            'step' => $step,
            'message' => $message,
        ];

        if ($store && $currentStepKey) {
            $progress = $this->calculateProgress($store, $currentStepKey);
            if(is_array($store)){
                $storeName = $store['store_name'] ?? 'Unknown Store';
                $storeId = $store['store_id'] ?? 'N/A';
            } else {
                $storeName = $store->store_name ?? 'Unknown Store';
                $storeId = $store->store_id ?? 'N/A';
            }
            // Get vendor email for support notification
            $vendorEmail = null;
            $vendorName = 'Store Owner';
            if (isset($store->user)) {
                $vendorEmail = $store->user->email;
                $vendorName = $store->user->name ?? 'Store Owner';
            } elseif (isset($store['user_id'])) {
                $user = CoreHelpers::getUserById($store['user_id']);
                $vendorEmail = $user['email'] ?? null;
                $vendorName = $user['name'] ?? 'Store Owner';
            }
            
            $details = array_merge($details, [
                'storeName' => $storeName,
                'storeId' => $storeId,
                'vendorEmail' => $vendorEmail,
                'vendorName' => $vendorName,
                'completedSteps' => $progress['completed'],
                'remainingSteps' => $progress['remaining'],
            ]);
        }

        $supportEmail =  $this->supportEmail;
        try {
            $html = view('mail.store-setup-step', ['details' => $details])->render();
        } catch (Exception $e) {
            $html = $message;
        }

        $from = 'MallShark  ' . env('SUPPORT_EMAIL');
        
        $subject = $step;
        if ($store && $currentStepKey) {
            $progress = $this->calculateProgress($store, $currentStepKey);
            $completedCount = count($progress['completed']);
            $remainingCount = count($progress['remaining']);
            
            if ($remainingCount > 0) {
                $completedStepNames = implode(', ', $progress['completed']);
                $remainingStepNames = implode(', ', $progress['remaining']);
                $subject = strtoupper($storeName) . ": {$step} | Remaining: {$remainingStepNames}";
            } else {
                $subject = strtoupper($storeName).": {$step} - All Steps Complete!";
            }
        }
        
        $params = [
            'from' => $from,
            'to' => $supportEmail,
            'subject' => $subject,
            'html' => $html,
        ];
        
        $mailgun = $this->mailgunController;
        $response = $mailgun->SendMailgunMail($params);
        return $response;
    }

    /**
     * Generic method to send setup step emails to vendor with progress tracking
     * Uses same store-setup-step template and includes progress details.
     */
    protected function sendVendorMail(string $step, string $message, $store, string $currentStepKey = null)
    {
        // Get vendor email from store relationship or user
        $toEmail = null;
        $vendorName = null;
        if (isset($store->user)) {
            $toEmail = $store->user->email;
            $vendorName = $store->user->name;
        } elseif (isset($store['user_id'])) {
            $user = CoreHelpers::getUserById($store['user_id']);
            $toEmail = $user['email'] ?? null;
            $vendorName = $user['name'] ?? null;
        }
        
        if (!$toEmail) {
            return false; 
        }

        if (is_array($store)) {
            $storeName = $store['store_name'] ?? 'Unknown Store';
            $storeId = $store['store_id'] ?? 'N/A';
        } else {
            $storeName = $store->store_name ?? 'Unknown Store';
            $storeId = $store->store_id ?? 'N/A';
        }

        $details = [
            'step' => $step,
            'message' => $message,
            'vendorName' => $vendorName,
            'vendorEmail' => $toEmail,
        ];

        if ($currentStepKey) {
            $progress = $this->calculateProgress($store, $currentStepKey);
            
            $details = array_merge($details, [
                'storeName' => $storeName,
                'storeId' => $storeId,
                'completedSteps' => $progress['completed'],
                'remainingSteps' => $progress['remaining'],
                'progressPercentage' => $progress['percentage'],
            ]);
        }

        try {
            $html = view('mail.store-setup-step', ['details' => $details])->render();
        } catch (Exception $e) {
            $html = $message;
        }

        $from = 'MallShark  ' . env('SUPPORT_EMAIL');
        
        $subject = $step;
        if ($currentStepKey) {
            $progress = $this->calculateProgress($store, $currentStepKey);
            $completedCount = count($progress['completed']);
            $remainingCount = count($progress['remaining']);
            
            if ($remainingCount > 0) {
                $completedStepNames = implode(', ', $progress['completed']);
                $remainingStepNames = implode(', ', $progress['remaining']);
                $subject = strtoupper($storeName) . ": {$step} | Remaining: {$remainingStepNames}";
            } else {
                $subject = strtoupper($storeName) . ": {$step} - All Steps Complete!";
            }
        }
        
        $params = [
            'from' => $from,
            'to' => $toEmail,
            'subject' => $subject,
            'html' => $html,
        ];

        try {
            $mailgun = $this->mailgunController;
            $response = $mailgun->SendMailgunMail($params);
            return $response;
        } catch (Exception $e) {
            throw new Exception($e->getMessage());

        }
    }

    /**
     * Calculate the setup progress for a store
     */
    protected function calculateProgress($store, $currentStepKey = null)
    {
        $completed = [];
        $remaining = [];
        
        $completed[] = $this->allSteps['account_created'];
        
        if (!empty($store->store_logo)) {
            $completed[] = $this->allSteps['logo_uploaded'];
        } else {
            $remaining[] = $this->allSteps['logo_uploaded'];
        }
        
        $hasProducts = false;
        try {
         if (is_object($store) && method_exists($store, 'products')) {
                $hasProducts = $store->products()->count() > 0;
            } else {
                $storeId = is_array($store) ? ($store['store_id'] ?? $store['id'] ?? null) : ($store->store_id ?? $store->id ?? null);
                if ($storeId) {
                    $hasProducts = StoreHelpers::getStoreProductsCount($storeId) > 0;
                } else {
                    $hasProducts = false;
                }
            }
        } catch (Exception $e) {
            $hasProducts = false;
        }
        if ($hasProducts) {
            $completed[] = $this->allSteps['product_added'];
        } else {
            $remaining[] = $this->allSteps['product_added'];
        }
        if (!empty($store->stripe) && $store->stripe == '1') {
            $completed[] = $this->allSteps['stripe_connected'];
        } else {
            $remaining[] = $this->allSteps['stripe_connected'];
        }
        
        if ($currentStepKey && isset($this->allSteps[$currentStepKey])) {
            $currentStepName = $this->allSteps[$currentStepKey];
            if (!in_array($currentStepName, $completed)) {
                $completed[] = $currentStepName;
                $remaining = array_filter($remaining, function($step) use ($currentStepName) {
                    return $step !== $currentStepName;
                });
            }
        }
        
        $totalSteps = count($this->allSteps);
        $completedCount = count($completed);
        $percentage = round(($completedCount / $totalSteps) * 100);
        
        return [
            'completed' => array_values($completed),
            'remaining' => array_values($remaining),
            'percentage' => $percentage
        ];
    }

    /**
     * Called after a new store account is created.
     */
    public function accountCreated($store)
    {
        $storeName = $store['store_name'] ?? 'Unknown Store';
        $storeId = $store['store_id'] ?? 'N/A';

        $message = "A new store account '{$storeName}' (ID: {$storeId}) has been created.";

        $supportEmail = $this->sendStepMail('Store Account Created', $message, $store, 'account_created');
        $vendorEmail = $this->sendVendorMail('Store Account Created', $message, $store, 'account_created');
    }

    /**
     * Called when a store uploads or updates its logo
     */
    public function LogoUploaded($store)
    {
        $storeName = $store->store_name ?? 'Unknown Store';
        $storeId = $store->store_id ?? 'N/A';

        $message = "The store '{$storeName}' (ID: {$storeId}) uploaded a logo.";
        $this->sendStepMail('Logo Uploaded', $message, $store, 'logo_uploaded');
        }

    /**
     * Called when a product is added for a store.
     */
    public function productAdded($product)
    {
        $store = $product->store ?? null;
        $storeName = $store->store_name ?? ($product->store_name ?? 'Unknown Store');
        $storeId = $store->store_id ?? ($product->store_id ?? 'N/A');

        $message = "A product was added to store '{$storeName}' (ID: {$storeId}).";
        $this->sendStepMail('First Product Added', $message, $store, 'product_added');
    }

    /**
     * Called when a Stripe account is connected to a store.
     */
    public function stripeAccountConnected($store)
    {
        $storeName = $store->store_name ?? 'Unknown Store';
        $storeId = $store->store_id ?? 'N/A';

        $message = "Stripe payment account has been successfully connected to store '{$storeName}' (ID: {$storeId}).";
        $this->sendStepMail('Stripe Account Connected', $message, $store, 'stripe_connected');
    }
}
