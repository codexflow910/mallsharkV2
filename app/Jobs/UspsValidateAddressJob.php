<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Packages\Core\Services\V2\CoreService;

class UspsValidateAddressJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $orderId;

    public function __construct($orderId)
    {
        $this->orderId = $orderId;
    }

    public function handle(CoreService $coreService)
    {
        try {
            $coreService->uspsValidateAddress($this->orderId);

        } catch (\Throwable $e) {
            Log::error('USPS Validate Address Exception: ' . $e->getMessage());
            throw $e;
        }
    }
}
