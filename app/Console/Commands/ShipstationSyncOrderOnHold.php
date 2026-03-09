<?php

namespace App\Console\Commands;

use GuzzleHttp\Client;
use Illuminate\Console\Command;
use Packages\Core\Config\CoreConstants;
use Packages\Order\Models\V2\StoreOrder;

class ShipstationSyncOrderOnHold extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'shipstation:change-order-status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Change the status of Shipstation orders';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    
    public function handle()
    {
        $apiKey    = env('SHIPSTATION_API_KEY');
        $apiSecret = env('SHIPSTATION_API_SECRET');
        $baseUrl   = env('SHIPSTATION_BASE_URL');

        try {
            $client = new Client();

            $response = $client->get($baseUrl . '/orders', [
                'auth'  => [$apiKey, $apiSecret],
                'query' => [
                    'sortDir'     => 'DESC',
                    'orderStatus' => 'on_hold',
                ],
            ]);

            $data = json_decode($response->getBody(), true);

            $orderIds = [];

            if (!empty($data['orders'])) {
                foreach ($data['orders'] as $order) {
                    if (($order['orderStatus'] ?? null) === CoreConstants::ORDER_ON_HOLD) {
                        $orderIds[] = (int) $order['orderNumber'];
                    }
                }
            }

            if (empty($orderIds)) {
                $this->warn('No ShipStation orders with status ON_HOLD found.');
                return;
            }

            $updatedCount = StoreOrder::whereIn('order_id', $orderIds)
                ->update([
                    'shipstation_status' => CoreConstants::ORDER_ON_HOLD,
                    'updated_at'         => now(),
                ]);

            $this->info("ShipStation orders statuses sync completed. Updated {$updatedCount} orders. IDs: " . implode(', ', $orderIds));

        } catch (\GuzzleHttp\Exception\RequestException $e) {
            // Handles API request errors
            $this->error('ShipStation API request failed: ' . $e->getMessage());

            $shipstationLog = new \Monolog\Logger('shipstation');
            $shipstationLog->pushHandler(new \Monolog\Handler\StreamHandler(
                storage_path('logs/shipStation/statusChange.log'),
                \Monolog\Logger::ERROR
            ));

            $shipstationLog->error('ShipStation API request failed', [
                'Message'   => $e->getMessage(),
                'OrderIds'  => $orderIds ?? [],
            ]);

        } catch (\Exception $e) {
            // Handles any other exceptions (DB, JSON parsing, etc.)
            $this->error('Error syncing ShipStation orders: ' . $e->getMessage());

            $shipstationLog = new \Monolog\Logger('shipstation');
            $shipstationLog->pushHandler(new \Monolog\Handler\StreamHandler(
                storage_path('logs/shipStation/statusChange.log'),
                \Monolog\Logger::ERROR
            ));

            $shipstationLog->error('General error during ShipStation sync', [
                'Message'   => $e->getMessage(),
                'OrderIds'  => $orderIds ?? [],
            ]);
        }
    }
}
