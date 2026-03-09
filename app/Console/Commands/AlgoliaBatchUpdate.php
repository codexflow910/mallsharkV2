<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Packages\Product\Models\V2\Product;
use Illuminate\Support\Facades\Log;
class AlgoliaBatchUpdate extends Command
{
    protected $signature = 'algolia:batch-update';
    protected $description = 'Update products with algolia_batch = 1 in Algolia and reset the flag';

    public function handle()
    {
        $products = Product::where(['algolia_batch' => 1])->get();

        if ($products->isNotEmpty()) {
            $productIds = $products->pluck('product_id')->toArray();

            // Update flag BEFORE pushing to Algolia to prevent race condition
            Product::whereIn('product_id', $productIds)->update(['algolia_batch' => 0]);

            // Now push to Algolia
            $products->searchable();

            $message = "Algolia batch update completed for " . count($productIds) . " products.";
            $this->info($message);
            Log::info($message); // Add this line
        } else {
            $message = "No products found for Algolia batch update.";
            $this->info($message);
            Log::info($message); // Add this line
        }
    }
}
