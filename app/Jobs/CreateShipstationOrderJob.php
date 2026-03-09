<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Packages\Core\Services\V2\CoreService; // your existing service

class CreateShipstationOrderJob implements ShouldQueue
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
            $coreService->createShipstationOrder($this->orderId);

        } catch (\Throwable $e) {

            throw $e; // let Laravel retry
        }
    }
}
