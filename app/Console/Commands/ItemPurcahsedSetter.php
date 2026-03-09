<?php

namespace App\Console\Commands;

use App\Models\StoreOrder;
use Illuminate\Console\Command;

class ItemPurcahsedSetter extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ItemPurchased:reset';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
        $storeOrders = StoreOrder::with('orderItems')->get();

        foreach ($storeOrders as $order) {
            $totalQuantity = $order->orderItems->sum('quantity');
            if ($order->total_items != $totalQuantity) {
                $order->total_items = $totalQuantity;
                $order->save();  // Save the changes to the database
            }
        }
        return 0;
    }
}
