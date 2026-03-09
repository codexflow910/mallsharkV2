<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CleanUnpaidOrders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'orders:cleanup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete unpaid orders older than 7 days and related records';

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
        DB::beginTransaction();

        try {
            // Get unpaid order IDs older than 7 days
            $orders = DB::table('store_orders')
                ->where('charge_status', 'unpaid')
                ->where('order_status', 'pending')
                ->where('created_at', '<', now()->subDays(7))
                ->get(['order_id', 'session_id','shipping_address']);

            if ($orders->isEmpty()) {
                $this->info('No unpaid orders older than 7 days found.');
                return;
            }

            $orderIds = $orders->pluck('order_id')->toArray();
            $sessionIds = $orders->pluck('session_id')
                ->filter()
                ->map(fn($s) => trim($s))
                ->toArray();
            $shippingAddresses = $orders->pluck('shipping_address')->filter()->toArray();

            // 1. Delete from order_items
            DB::table('order_items')
                ->whereIn('order_id', $orderIds)
                ->delete();

            // 2. Delete from shipping_addresses
            DB::table('shipping_addresses')
                ->whereIn('sh_id', $shippingAddresses)
                ->delete();

            // 3. Delete from checkout_logs
           foreach ($sessionIds as $sessionId) {
             DB::table('checkout_logs')
                ->where('session_id', 'like', $sessionId . '%')
                ->delete();
            }
            // 4. Delete from discount_usages
            DB::table('discount_usages')
                ->whereIn('order_id', $orderIds)
                ->delete();

            // 5. Delete from store_orders
            DB::table('store_orders')
                ->whereIn('order_id', $orderIds)
                ->delete();

            DB::commit();

            $this->info('✅ Successfully deleted unpaid orders older than 7 days and related records.');
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('❌ Error during cleanup: ' . $e->getMessage());
        }
    }
}
